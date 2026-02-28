<?php

namespace App\Controllers;
use App\Libraries\ShopeeApi;

class Shopee extends BaseController
{
    private $shopeeApi;
    private $db;

    public function __construct()
    {
        $this->shopeeApi = new ShopeeApi();
        $this->db = \Config\Database::connect();
    }

    // --- 1. HALAMAN DASHBOARD INTEGRASI SHOPEE ---
    public function index()
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/portal');

        $shops = $this->db->table('shopee_integrations')->get()->getResultArray();
        
        $data = [
            'title'    => 'Integrasi Shopee',
            'shops'    => $shops,
            'auth_url' => $this->shopeeApi->getAuthUrl() // URL untuk tombol Otorisasi
        ];

        return view('shopee/index', $data); // (File View akan kita buat terpisah nanti)
    }

    // --- 2. CALLBACK DARI SHOPEE SETELAH OTORISASI ---
    public function callback()
    {
        // Shopee melempar '?code=xxx&shop_id=xxx'
        $code   = $this->request->getGet('code');
        $shopId = $this->request->getGet('shop_id');

        if (!$code || !$shopId) {
            return redirect()->to('/shopee')->with('error', 'Otorisasi ditolak atau dibatalkan oleh penjual.');
        }

        try {
            // Tukar Code dengan Access Token & Refresh Token
            $response = $this->shopeeApi->getAccessToken($code, $shopId);

            if (isset($response['error']) && $response['error'] !== '') {
                throw new \Exception($response['message']);
            }

            // Siapkan data untuk disimpan (Sesuai skema tabel sebelumnya)
            // Access token berlaku 4 jam (expire_in biasanya 14400 detik)
            $data = [
                'shop_id'       => $shopId,
                'access_token'  => $response['access_token'],
                'refresh_token' => $response['refresh_token'],
                'expire_at'     => time() + $response['expire_in'], 
                'status'        => 'Active'
            ];

            // Cek apakah toko ini sudah ada di database
            $existing = $this->db->table('shopee_integrations')->where('shop_id', $shopId)->get()->getRowArray();

            if ($existing) {
                // Update token jika toko sudah pernah di-authorize
                $this->db->table('shopee_integrations')->where('shop_id', $shopId)->update($data);
                $msg = 'Otorisasi ulang toko berhasil. Token diperbarui.';
            } else {
                // Insert baru jika toko pertama kali di-authorize
                $data['shop_name'] = 'Toko Shopee #' . $shopId; // Bisa diupdate namanya nanti pakai API get_shop_info
                $this->db->table('shopee_integrations')->insert($data);
                $msg = 'Toko baru berhasil dihubungkan ke sistem!';
            }

            return redirect()->to('/shopee')->with('success', $msg);

        } catch (\Exception $e) {
            return redirect()->to('/shopee')->with('error', 'Gagal memproses otorisasi API: ' . $e->getMessage());
        }
    }

    // --- 3. HELPER: OTOMATIS REFRESH TOKEN ---
    // (Bisa dipanggil via CronJob atau sebelum hit API lain)
    public function auto_refresh_token($shopId)
    {
        $shop = $this->db->table('shopee_integrations')->where('shop_id', $shopId)->get()->getRowArray();
        if (!$shop) return false;

        // Cek apakah token sudah mau habis (kurang dari 30 menit / 1800 detik sebelum expire)
        if (time() > ($shop['expire_at'] - 1800)) {
            try {
                $response = $this->shopeeApi->refreshAccessToken($shop['refresh_token'], $shop['shop_id']);

                if (isset($response['error']) && $response['error'] !== '') {
                    // Jika gagal (misal refresh token 30 harinya expired), set status Disconnected
                    $this->db->table('shopee_integrations')->where('shop_id', $shopId)->update(['status' => 'Disconnected']);
                    return false;
                }

                // Update token baru ke database
                $updateData = [
                    'access_token'  => $response['access_token'],
                    'refresh_token' => $response['refresh_token'],
                    'expire_at'     => time() + $response['expire_in'],
                    'status'        => 'Active'
                ];
                $this->db->table('shopee_integrations')->where('shop_id', $shopId)->update($updateData);

                return $response['access_token'];

            } catch (\Exception $e) {
                return false;
            }
        }

        // Jika belum expired, gunakan token yang ada
        return $shop['access_token'];
    }

    // --- 3. FITUR: TARIK PESANAN (SYNC ORDERS) - ENTERPRISE LEVEL ---
    public function sync_orders($shopId)
    {
        $shop = $this->db->table('shopee_integrations')->where('shop_id', $shopId)->get()->getRowArray();
        
        if (!$shop) return redirect()->back()->with('error', 'Toko tidak ditemukan.');
        if ($shop['expire_at'] < time()) {
            return redirect()->back()->with('error', 'Token kadaluarsa. Silakan otorisasi ulang toko ini.');
        }

        $db = \Config\Database::connect();

        try {
            $orderSns = [];
            $hasMore = true;
            $cursor = "";
            
            // =========================================================================
            // TAHAP A: LOOPING GET ORDER LIST (Menangani Pesanan Lebih dari 100)
            // Sesuai Dokumentasi API Shopee: v2.order.get_order_list
            // =========================================================================
            while ($hasMore) {
                $pathList = '/api/v2/order/get_order_list';
                $paramsList = [
                    'time_range_field' => 'create_time',
                    'time_from'        => time() - (86400 * 14), // Ambil 14 Hari ke belakang (Batas max Shopee 15 hari)
                    'time_to'          => time(),
                    'page_size'        => 50,                    // Ambil 50 pesanan per halaman
                    'order_status'     => 'READY_TO_SHIP',       // Fokus pesanan yang siap dipacking
                    'cursor'           => $cursor                // Parameter Paginasi
                ];

                $respList = $this->shopeeApi->callApi($pathList, $paramsList, 'GET', $shop['access_token'], $shop['shop_id']);

                if (isset($respList['error']) && $respList['error'] !== '') {
                    throw new \Exception($respList['message'] ?? 'Unknown Error from Shopee API');
                }

                // Ekstrak Order SN dan masukkan ke Array Utama
                if (!empty($respList['response']['order_list'])) {
                    foreach ($respList['response']['order_list'] as $order) {
                        $orderSns[] = $order['order_sn'];
                    }
                }

                // Cek apakah masih ada halaman berikutnya?
                $hasMore = $respList['response']['more'] ?? false;
                $cursor  = $respList['response']['next_cursor'] ?? "";
                
                // Safety Break: Cegah Infinite Loop jika pesanan lebih dari 5000 
                if (count($orderSns) > 5000) break; 
            }

            // Jika tidak ada pesanan baru, hentikan proses dengan elegan
            if (empty($orderSns)) {
                return redirect()->back()->with('success', 'Sinkronisasi selesai. Tidak ada pesanan "READY_TO_SHIP" yang belum diproses.');
            }

            // =========================================================================
            // TAHAP B: GET ORDER DETAIL & SIMPAN KE DATABASE LOKAL
            // =========================================================================
            $builderOrder = $db->table('sales_orders');
            $builderItems = $db->table('sales_order_items');
            $syncedCount = 0;

            $db->transStart(); // Mulai mode Transaksi Keamanan Database

            // API Detail maksimal menerima 50 order_sn sekaligus. Kita pecah (Chunk) array-nya!
            $chunks = array_chunk($orderSns, 50); 
            
            foreach ($chunks as $chunk) {
                $pathDetail = '/api/v2/order/get_order_detail';
                $paramsDetail = [
                    'order_sn_list' => implode(',', $chunk), // Gabungkan 50 array menjadi string dipisah koma
                    'response_optional_fields' => 'item_list,buyer_user_name,payment_method,shipping_carrier,total_amount'
                ];

                $respDetail = $this->shopeeApi->callApi($pathDetail, $paramsDetail, 'GET', $shop['access_token'], $shop['shop_id']);
                $ordersDetail = $respDetail['response']['order_list'] ?? [];

                // Simpan ke Database Lokal
                foreach ($ordersDetail as $o) {
                    $orderSn = $o['order_sn'];
                    
                    // Cek apakah pesanan ini sudah pernah ditarik sebelumnya (mencegah duplikasi)
                    $exist = $builderOrder->where('order_sn', $orderSn)->countAllResults();
                    
                    if ($exist == 0) {
                        // 1. Simpan Header Pesanan (Keuangan & Logistik)
                        $builderOrder->insert([
                            'order_sn'         => $orderSn,
                            'shop_id'          => $shopId,
                            'buyer_username'   => $o['buyer_user_name'] ?? 'Unknown Buyer', // Sesuai JSON Shopee API v2
                            'total_amount'     => $o['total_amount'] ?? 0,
                            'payment_method'   => $o['payment_method'] ?? '',
                            'order_status'     => $o['order_status'],
                            'shipping_carrier' => $o['shipping_carrier'] ?? '',
                            'order_date'       => date('Y-m-d H:i:s', $o['create_time'])
                        ]);

                        // 2. Simpan Rincian Knalpot yang dibeli (Gudang & Produksi)
                        if (!empty($o['item_list'])) {
                            foreach ($o['item_list'] as $item) {
                                $builderItems->insert([
                                    'order_sn'               => $orderSn,
                                    'item_id'                => $item['item_id'],
                                    'item_name'              => $item['item_name'],
                                    'variation_name'         => $item['model_name'] ?? '',
                                    'model_qty'              => $item['model_quantity_purchased'],
                                    'model_discounted_price' => $item['model_discounted_price']
                                ]);
                            }
                        }
                        $syncedCount++;
                    }
                }
            }
            
            $db->transComplete(); // Kunci data ke Database

            if ($db->transStatus() === false) {
                throw new \Exception("Gagal melakukan penulisan ke database lokal.");
            }

            // =========================================================================
            // TAHAP C: UPDATE WAKTU TERAKHIR SINKRONISASI
            // =========================================================================
            $this->db->table('shopee_integrations')->where('shop_id', $shopId)->update(['updated_at' => date('Y-m-d H:i:s')]);

            return redirect()->back()->with('success', "Hebat! <b>{$syncedCount} Pesanan Baru</b> berhasil masuk ke sistem Gudang Anda.");

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menarik pesanan: ' . $e->getMessage());
        }
    }

   // --- 4. FITUR: SINKRONISASI KATALOG PRODUK (BATCH PROCESSING) ---
    public function sync_products($shopId)
    {
        $shop = $this->db->table('shopee_integrations')->where('shop_id', $shopId)->get()->getRowArray();
        
        if (!$shop) return redirect()->back()->with('error', 'Toko tidak ditemukan.');
        if ($shop['expire_at'] < time()) return redirect()->back()->with('error', 'Token kadaluarsa. Harap otorisasi ulang.');

        $db = \Config\Database::connect();
        $builder = $db->table('shopee_products');

        try {
            $hasMore = true;
            $offset = 0;
            $totalSynced = 0;

            // Membuka transaksi database agar data aman jika di tengah jalan internet putus
            $db->transStart();

            // TAHAP A: LOOPING GET ITEM LIST (Max 100 per halaman)
            while ($hasMore) {
                // Tarik daftar ID
                $listResp = $this->shopeeApi->getItemList($shopId, $offset, 100);
                
                if (isset($listResp['error']) && $listResp['error'] !== '') {
                    throw new \Exception($listResp['message'] ?? 'Gagal mengambil daftar produk.');
                }

                $items = $listResp['response']['item'] ?? [];
                if (empty($items)) break;

                // Ambil ID-nya saja dan kumpulkan ke dalam array
                $itemIds = array_column($items, 'item_id');

                // TAHAP B: PECAH ARRAY JADI 50 (Sesuai limit Get Item Base Info Shopee)
                $chunks = array_chunk($itemIds, 50);

                foreach ($chunks as $chunkIds) {
                    
                    // Tarik informasi lengkap (Nama, Stok, Harga) untuk 50 produk ini
                    $infoResp = $this->shopeeApi->getItemBaseInfo($chunkIds, $shopId);
                    $itemDetails = $infoResp['response']['item_list'] ?? [];

                    foreach ($itemDetails as $detail) {
                        $itemId = $detail['item_id'];
                        
                        // Cara mengambil Harga dan Stok terbaru dari struktur JSON Shopee yang kompleks
                        $price = $detail['price_info'][0]['original_price'] ?? 0;
                        
                        // Menangani struktur stok versi terbaru (stock_info_v2)
                        $stock = 0;
                        if (isset($detail['stock_info_v2']['summary_info']['total_available_stock'])) {
                            $stock = $detail['stock_info_v2']['summary_info']['total_available_stock'];
                        }

                        // Cek apakah item ini sudah pernah disinkronkan sebelumnya ke database ERP
                        $exists = $builder->where('item_id', $itemId)->countAllResults();

                        $data = [
                            'shop_id'   => $shopId,
                            'item_id'   => $itemId,
                            'item_name' => $detail['item_name'],
                            'item_sku'  => $detail['item_sku'] ?? '', // Sangat penting untuk mapping offline nanti
                            'price'     => $price,
                            'stock'     => $stock,
                            'status'    => $detail['item_status']
                        ];

                        if ($exists > 0) {
                            $builder->where('item_id', $itemId)->update($data); // Update data yang ada
                        } else {
                            $builder->insert($data); // Masukkan barang baru
                        }
                        $totalSynced++;
                    }
                }

                // Cek Paginasi: Apakah masih ada barang di halaman berikutnya?
                $hasMore = $listResp['response']['has_next_page'] ?? false;
                $offset = $listResp['response']['next_offset'] ?? ($offset + 100);
            }

            // Kunci semua perubahan ke Database
            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \Exception("Database gagal menyimpan Katalog Shopee.");
            }

            // Update waktu sinkronisasi di tabel toko
            $this->db->table('shopee_integrations')->where('shop_id', $shopId)->update(['updated_at' => date('Y-m-d H:i:s')]);

            return redirect()->back()->with('success', "Katalog Induk Tersinkronisasi! <b>{$totalSynced} Produk Aktif</b> berhasil ditarik dari Shopee ke database ERP Pabrik.");

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal sinkronisasi katalog: ' . $e->getMessage());
        }
    }

    // --- 5. HALAMAN KATALOG PRODUK SHOPEE LOKAL ---
    public function products($shopId)
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/portal');

        $db = \Config\Database::connect();
        
        // Ambil info toko
        $shop = $db->table('shopee_integrations')->where('shop_id', $shopId)->get()->getRowArray();
        if (!$shop) return redirect()->to('/shopee')->with('error', 'Toko tidak ditemukan.');

        // Ambil daftar produk yang sudah ditarik
        $products = $db->table('shopee_products')
                       ->where('shop_id', $shopId)
                       ->orderBy('updated_at', 'DESC')
                       ->get()->getResultArray();

        $data = [
            'title'    => 'Katalog Produk Shopee',
            'shop'     => $shop,
            'products' => $products
        ];

        return view('shopee/products', $data);
    }

    // --- 6. ENDPOINT AJAX: SIMPAN PEMETAAN SKU ---
    public function map_sku()
    {
        // Pastikan request datang dari AJAX
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403)->setBody('Akses Ditolak');
        }

        $itemId = $this->request->getPost('item_id');
        $sku    = $this->request->getPost('sku');

        if (empty($itemId) || empty($sku)) {
            return $this->response->setJSON(['success' => false, 'message' => 'SKU tidak boleh kosong.']);
        }

        try {
            $db = \Config\Database::connect();
            
            // Simpan SKU Gudang ke tabel produk Shopee lokal kita
            $db->table('shopee_products')
               ->where('item_id', $itemId)
               ->update(['item_sku' => strtoupper($sku)]); // Jadikan huruf besar agar seragam

            return $this->response->setJSON(['success' => true, 'message' => 'Produk berhasil diikat dengan SKU: ' . strtoupper($sku)]);

        } catch (\Exception $e) {
            return $this->response->setJSON(['success' => false, 'message' => 'Terjadi kesalahan database.']);
        }
    }

    // --- 7. GLOBAL HELPER: AUTO-PUSH STOK KE SHOPEE ---
    /**
     * Panggil fungsi ini dari Modul Produksi/Kasir Offline Anda.
     * Contoh penggunaan di controller lain: 
     * $shopeeController = new \App\Controllers\Shopee();
     * $shopeeController->push_stock_to_shopee('NRC-WR155-KIDAL', 15);
     */
    public function push_stock_to_shopee($localSku, $latestTotalStock)
    {
        $db = \Config\Database::connect();
        
        // 1. Cari apakah SKU pabrik ini punya "kembaran" di etalase Shopee?
        // (Bisa jadi 1 SKU pabrik dipasang di 2 toko Shopee cabang yang berbeda)
        $shopeeItems = $db->table('shopee_products')
                          ->where('item_sku', strtoupper($localSku))
                          ->get()->getResultArray();

        if (empty($shopeeItems)) {
            return false; // Barang ini tidak dijual di Shopee, abaikan.
        }

        $successCount = 0;

        // 2. Jika ketemu, tembak API Update Stock ke semua toko Shopee yang terkait
        foreach ($shopeeItems as $item) {
            try {
                // Memanggil Mesin API yang sudah kita buat sebelumnya
                $response = $this->shopeeApi->updateStock($item['item_id'], $latestTotalStock, $item['shop_id'], 0);
                
                if (!isset($response['error']) || $response['error'] === '') {
                    // Jika API Shopee sukses, update juga tabel lokal kita agar sinkron
                    $db->table('shopee_products')
                       ->where('item_id', $item['item_id'])
                       ->update(['stock' => $latestTotalStock, 'updated_at' => date('Y-m-d H:i:s')]);
                       
                    $successCount++;
                }
            } catch (\Exception $e) {
                // Catat log error jika gagal ke satu toko agar toko lain tetap diproses
                log_message('error', "Gagal push stok Shopee Item {$item['item_id']}: " . $e->getMessage());
                continue; 
            }
        }

        return $successCount;
    }

    // --- 8. HALAMAN ADD PRODUCT (STYLE BIGSELLER) ---
    public function create_product($shopId)
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/portal');

        $shop = $this->db->table('shopee_integrations')->where('shop_id', $shopId)->get()->getRowArray();
        if (!$shop) return redirect()->to('/shopee');

        $data = [
            'title' => 'Tambah Produk (Publish ke Shopee)',
            'shop'  => $shop
        ];

        return view('shopee/add_product', $data);
    }

    // --- 9. PROSES PUBLISH KE SHOPEE (ENTERPRISE STANDARD) ---
    public function store_product($shopId)
    {
        try {
            $shopeeApi = new \App\Libraries\ShopeeApi();

            // 1. TANGKAP DAN VALIDASI FILE GAMBAR
            $file = $this->request->getFile('product_image');
            if (!$file || !$file->isValid() || $file->hasMoved()) {
                throw new \Exception("Gambar produk wajib diunggah dan tidak boleh rusak!");
            }

            // Simpan file sementara di server ERP (Wajib untuk dikirim via cURL)
            $tempPath = ROOTPATH . 'public/uploads/temp/';
            if (!is_dir($tempPath)) mkdir($tempPath, 0777, true);
            
            $newName = $file->getRandomName();
            $file->move($tempPath, $newName);
            $fullFilePath = $tempPath . $newName;

            // 2. UPLOAD GAMBAR KE SHOPEE MEDIA SPACE
            $uploadResp = $shopeeApi->uploadImage($fullFilePath, $shopId);
            
            // Hapus file temp lokal agar hardisk server ERP Anda tidak penuh
            if (file_exists($fullFilePath)) unlink($fullFilePath);

            // Cek apakah Shopee menolak gambar tersebut (misal ukuran file > 2MB)
            if (isset($uploadResp['error']) && $uploadResp['error'] !== '') {
                throw new \Exception("Gagal upload gambar ke Shopee: " . ($uploadResp['message'] ?? $uploadResp['error']));
            }

            // Ekstrak Image ID resmi dari Shopee
            $shopeeImageId = $uploadResp['response']['image_info']['image_id'];

            if (empty($shopeeImageId)) {
                throw new \Exception("Image ID tidak ditemukan dari respon Shopee.");
            }

            // 3. RAKIT PAYLOAD JSON (Sesuai Dokumentasi Terbaru)
            $payload = [
                'item_name'      => $this->request->getPost('item_name'),
                'description'    => $this->request->getPost('description'),
                'item_sku'       => strtoupper($this->request->getPost('item_sku')),
                'category_id'    => (int)$this->request->getPost('category_id'), 
                'original_price' => (float)$this->request->getPost('price'),
                'weight'         => (float)$this->request->getPost('weight'),
                'item_status'    => 'NORMAL', 
                
                // MENGGUNAKAN SELLER_STOCK (Bukan normal_stock)
                'seller_stock'   => [
                    [
                        'location_id' => '', // Kosongkan agar masuk ke gudang default
                        'stock'       => (int)$this->request->getPost('stock')
                    ]
                ],
                
                'dimension'      => [
                    'package_length' => (int)$this->request->getPost('length'),
                    'package_width'  => (int)$this->request->getPost('width'),
                    'package_height' => (int)$this->request->getPost('height')
                ],
                
                'image' => [
                    'image_id_list' => [$shopeeImageId]
                ],

                // PARAMETER WAJIB: BRAND
                'brand' => [
                    'brand_id'            => 0,
                    'original_brand_name' => 'No Brand'
                ],
                
                // PENGATURAN KURIR PENGIRIMAN
                // Pastikan logistic_id (Contoh 80014 = J&T Express) benar-benar aktif di toko Anda
                'logistic_info' => [
                    [
                        'logistic_id' => 80014, 
                        'enabled'     => true,
                        'is_free'     => false
                    ]
                ]
            ];

            // 4. TEMBAK API ADD ITEM
            $addResp = $shopeeApi->addItem($payload, $shopId);

            if (isset($addResp['error']) && $addResp['error'] !== '') {
                throw new \Exception("Gagal Publish Produk: " . ($addResp['message'] ?? $addResp['error']));
            }

            return redirect()->to('/shopee/products/'.$shopId)->with('success', 'Hebat! Knalpot berhasil dirilis secara Live ke Shopee.');

        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    // --- 10. FITUR: REKONSILIASI KEUANGAN OTOMATIS (TARIK DATA PENCAIRAN) ---
    public function sync_finance($shopId)
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/portal');

        $shop = $this->db->table('shopee_integrations')->where('shop_id', $shopId)->get()->getRowArray();
        if (!$shop) return redirect()->back()->with('error', 'Toko tidak ditemukan.');

        $db = \Config\Database::connect();
        
        try {
            // 1. Cari pesanan berstatus COMPLETED yang BELUM dicatat di buku kas
            $builder = $db->table('sales_orders');
            $builder->select('order_sn, buyer_username, order_date');
            $builder->where('shop_id', $shopId);
            $builder->where('order_status', 'COMPLETED');
            $builder->where('order_sn NOT IN (SELECT order_sn FROM shopee_finances)', null, false);
            $builder->limit(50); // Maksimal proses 50 pesanan sekali klik
            $completedOrders = $builder->get()->getResultArray();

            if (empty($completedOrders)) {
                return redirect()->back()->with('success', 'Buku Kas Anda sudah Up-to-Date. Tidak ada pencairan dana baru dari Shopee.');
            }

            $syncedCount = 0;
            $totalNetIncome = 0;

            $db->transStart();

            // 2. Tanya rincian Escrow ke Shopee untuk masing-masing pesanan
            foreach ($completedOrders as $order) {
                $orderSn = $order['order_sn'];
                $resp = $this->shopeeApi->getEscrowDetail($orderSn, $shopId);

                if (isset($resp['response']['order_income'])) {
                    $income = $resp['response']['order_income'];
                    
                    // Ekstraksi Nilai berdasarkan Dokumentasi Resmi
                    $grossSales   = $income['original_cost_of_goods_sold'] ?? 0;
                    $escrowAmount = $income['escrow_amount'] ?? 0; // NET INCOME (Uang masuk rekening)
                    
                    // Rincian Potongan Shopee
                    $commissionFee  = $income['commission_fee'] ?? 0;
                    $serviceFee     = $income['service_fee'] ?? 0;
                    $transactionFee = $income['seller_transaction_fee'] ?? 0;
                    
                    // Total Admin adalah gabungan Komisi + Layanan + Biaya Transaksi Kartu/Transfer
                    $totalAdminFee  = $commissionFee + $serviceFee + $transactionFee;
                    
                    // Ongkir yang ditanggung penjual (Jika ada penalti atau selisih berat)
                    $shippingPaidBySeller = $income['final_shipping_fee'] ?? 0; 
                    if ($shippingPaidBySeller < 0) $shippingPaidBySeller = abs($shippingPaidBySeller);

                    // 3. Masukkan ke Buku Kas ERP
                    $db->table('shopee_finances')->insert([
                        'order_sn'                    => $orderSn,
                        'shop_id'                     => $shopId,
                        'buyer_username'              => $order['buyer_username'],
                        'original_price'              => $grossSales,
                        'admin_fee'                   => $totalAdminFee,
                        'service_fee'                 => $serviceFee, // Disimpan terpisah untuk rekapan detil
                        'shipping_fee_paid_by_seller' => $shippingPaidBySeller,
                        'escrow_amount'               => $escrowAmount,
                        'payout_time'                 => date('Y-m-d H:i:s'), // Waktu uang dicatat
                    ]);

                    $syncedCount++;
                    $totalNetIncome += $escrowAmount;
                }
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \Exception("Database gagal mengunci catatan kas keuangan.");
            }

            $formattedMoney = 'Rp ' . number_format($totalNetIncome, 0, ',', '.');
            return redirect()->back()->with('success', "Audit Selesai! <b>{$syncedCount} Pesanan</b> telah cair dengan total Uang Bersih <b>{$formattedMoney}</b>.");

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal audit keuangan: ' . $e->getMessage());
        }
    }

    // --- 11. HALAMAN DASHBOARD KEUANGAN SHOPEE ---
    public function finances($shopId)
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/portal');

        $db = \Config\Database::connect();
        $shop = $db->table('shopee_integrations')->where('shop_id', $shopId)->get()->getRowArray();
        
        if (!$shop) return redirect()->to('/shopee');

        // Ambil riwayat pencairan dana, urutkan dari yang terbaru
        $finances = $db->table('shopee_finances')
                       ->where('shop_id', $shopId)
                       ->orderBy('payout_time', 'DESC')
                       ->get()->getResultArray();

        // Hitung akumulasi statistik
        $statGross = 0;
        $statNet   = 0;
        $statFees  = 0;

        foreach ($finances as $f) {
            $statGross += $f['original_price'];
            $statNet   += $f['escrow_amount'];
            $statFees  += $f['admin_fee'];
        }

        $data = [
            'title'     => 'Buku Kas Shopee',
            'shop'      => $shop,
            'finances'  => $finances,
            'statGross' => $statGross,
            'statNet'   => $statNet,
            'statFees'  => $statFees
        ];

        return view('shopee/finances', $data);
    }

    // --- 12. FITUR: SINKRONISASI RETUR BARANG (RMA) ---
    public function sync_returns($shopId)
    {
        $shop = $this->db->table('shopee_integrations')->where('shop_id', $shopId)->get()->getRowArray();
        if (!$shop) return redirect()->back()->with('error', 'Toko tidak ditemukan.');

        $db = \Config\Database::connect();
        
        try {
            $resp = $this->shopeeApi->getReturnList($shopId);

            if (isset($resp['error']) && $resp['error'] !== '') {
                throw new \Exception($resp['message'] ?? 'Gagal mengambil data retur.');
            }

            $returns = $resp['response']['return'] ?? [];
            if (empty($returns)) {
                return redirect()->back()->with('success', 'Tidak ada data pengajuan Retur/Refund baru.');
            }

            $syncedCount = 0;
            $builder = $db->table('shopee_rma');

            foreach ($returns as $ret) {
                $returnSn = $ret['return_sn'];
                
                // Cek apakah sudah pernah ditarik
                $exists = $builder->where('return_sn', $returnSn)->countAllResults();
                
                if ($exists == 0) {
                    $builder->insert([
                        'return_sn'     => $returnSn,
                        'order_sn'      => $ret['order_sn'],
                        'shop_id'       => $shopId,
                        'reason'        => $ret['reason'] ?? 'Unknown Reason',
                        'status'        => $ret['status'],
                        'refund_amount' => $ret['refund_amount'] ?? 0
                    ]);

                    // Mengubah status pesanan di tabel lokal agar Gudang tidak bingung
                    $db->table('sales_orders')->where('order_sn', $ret['order_sn'])->update(['order_status' => 'IN_CANCEL / RETURNED']);
                    
                    $syncedCount++;
                }
            }

            return redirect()->back()->with('success', "Audit Retur Selesai! <b>{$syncedCount} Transaksi Batal/Retur</b> berhasil dicatat ke sistem.");

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menarik data retur: ' . $e->getMessage());
        }
    }
}