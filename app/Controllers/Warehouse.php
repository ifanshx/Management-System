<?php

namespace App\Controllers;

class Warehouse extends BaseController
{
    private $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    public function orders()
    {
        // Pastikan hanya user yang login yang bisa akses
        if (!session()->get('isLoggedIn')) return redirect()->to('/login');

        // Ambil semua pesanan dari tabel lokal, urutkan dari yang paling lama masuk (First In First Out)
        $builder = $this->db->table('sales_orders');
        $builder->where('order_status', 'READY_TO_SHIP'); // Hanya tampilkan yang siap dipacking
        $builder->orderBy('order_date', 'ASC'); 
        $orders = $builder->get()->getResultArray();

        // Looping untuk memasukkan rincian item ke masing-masing pesanan
        foreach ($orders as &$order) {
            $order['items'] = $this->db->table('sales_order_items')
                                       ->where('order_sn', $order['order_sn'])
                                       ->get()->getResultArray();
        }

        $data = [
            'title'  => 'Manajemen Pesanan Gudang',
            'orders' => $orders
        ];

        return view('warehouse/orders', $data);
    }

    // --- FITUR: ARRANGE SHIPMENT (PICKUP KURIR SINGLE) ---
    public function ship_shopee_order($orderSn)
    {
        $shopeeApi = new \App\Libraries\ShopeeApi();
        
        $order = $this->db->table('sales_orders')->where('order_sn', $orderSn)->get()->getRowArray();
        if (!$order) return redirect()->back()->with('error', 'Pesanan tidak ditemukan di database lokal.');

        try {
            $shopId = $order['shop_id'];

            // 1. TANYA SHOPEE: PARAMETER APA YANG DIBUTUHKAN?
            $shippingParams = $shopeeApi->getShippingParameter($orderSn, $shopId);

            if (isset($shippingParams['error']) && $shippingParams['error'] !== '') {
                throw new \Exception("Gagal mengambil parameter logistik: " . ($shippingParams['message'] ?? 'Error API'));
            }

            $pickupData = [];
            $responseParam = $shippingParams['response'] ?? [];

            // 2. CEK APAKAH MENDUKUNG PICKUP (JEMPUT KURIR)
            if (isset($responseParam['info_needed']['pickup'])) {
                
                $addressList = $responseParam['pickup']['address_list'] ?? [];
                
                if (empty($addressList)) {
                    throw new \Exception("Tidak ada alamat Gudang (Pickup Address) yang disetting di Shopee Seller Center Anda.");
                }

                $pickupData['address_id'] = $addressList[0]['address_id'];
                $timeSlots = $addressList[0]['time_slot_list'] ?? [];
                if (!empty($timeSlots)) {
                    $pickupData['pickup_time_id'] = $timeSlots[0]['pickup_time_id'];
                }

            } else {
                throw new \Exception("Kurir pesanan ini tidak mendukung penjemputan (Pickup). Anda harus melakukan Drop-off manual.");
            }

            $this->db->transStart();

            // 3. EKSEKUSI PENGIRIMAN (SHIP ORDER) KE SHOPEE
            $shipResponse = $shopeeApi->shipOrder($orderSn, $shopId, $pickupData, []);

            if (isset($shipResponse['error']) && $shipResponse['error'] !== '') {
                throw new \Exception("Gagal memanggil kurir: " . ($shipResponse['message'] ?? $shipResponse['error']));
            }

            // 4. UPDATE DATABASE LOKAL (Tandai pesanan selesai dipacking)
            $this->db->table('sales_orders')
                     ->where('order_sn', $orderSn)
                     ->update(['order_status' => 'PACKED']);

            // ========================================================
            // 5. KUNCI OMNICHANNEL: POTONG STOK MASTER GUDANG LOKAL!
            // ========================================================
            $orderItems = $this->db->table('sales_order_items')->where('order_sn', $orderSn)->get()->getResultArray();
            foreach ($orderItems as $item) {
                $sku = $item['item_sku']; 
                $qty = $item['model_qty'];
                
                // Kurangi stok fisik di Gudang Lokal (Karena barangnya masuk ke kardus dan diserahkan ke kurir)
                $this->db->query("UPDATE warehouse_inventory SET physical_stock = physical_stock - ? WHERE sku = ?", [$qty, $sku]);
            }
            // ========================================================

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                 // Rollback jika terjadi masalah DB lokal
                throw new \Exception("Terjadi kesalahan saat mengunci transaksi database lokal.");
            }

            return redirect()->back()->with('success', "Sukses! Kurir dipanggil dan Stok Gudang Lokal terpotong untuk resi: {$orderSn}");

        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    // --- FITUR: CETAK RESI THERMAL (AIRWAY BILL SINGLE) ---
    public function print_shopee_awb($orderSn)
    {
        $shopeeApi = new \App\Libraries\ShopeeApi();
        $order = $this->db->table('sales_orders')->where('order_sn', $orderSn)->get()->getRowArray();
        
        if (!$order) return redirect()->back()->with('error', 'Pesanan tidak ditemukan.');
        
        $shopId = $order['shop_id'];

        try {
            $createResp = $shopeeApi->createShippingDocument($orderSn, $shopId);
            if (isset($createResp['error']) && $createResp['error'] !== '') {
                throw new \Exception("Gagal Create Dokumen: " . ($createResp['message'] ?? $createResp['error']));
            }

            $isReady = false;
            $maxRetries = 3; 
            $attempts = 0;

            while (!$isReady && $attempts < $maxRetries) {
                usleep(1500000); 
                $resultResp = $shopeeApi->getShippingDocumentResult($orderSn, $shopId);
                
                if (isset($resultResp['response']['result_list'][0]['status'])) {
                    $status = $resultResp['response']['result_list'][0]['status'];
                    if ($status === 'READY') {
                        $isReady = true;
                    } elseif ($status === 'FAILED') {
                        $failMsg = $resultResp['response']['result_list'][0]['fail_message'] ?? 'Render dokumen gagal di sisi server Shopee.';
                        throw new \Exception("Shopee gagal memproses resi: " . $failMsg);
                    }
                }
                $attempts++;
            }

            if (!$isReady) {
                throw new \Exception("Dokumen resi sedang diproses oleh Shopee. Silakan coba klik tombol cetak beberapa detik lagi.");
            }

            $fileContent = $shopeeApi->downloadShippingDocument($orderSn, $shopId);

            if (is_string($fileContent) && strpos(trim($fileContent), '{') === 0) {
                $errDecode = json_decode($fileContent, true);
                if (isset($errDecode['error']) && $errDecode['error'] !== '') {
                    throw new \Exception("Download Gagal: " . ($errDecode['message'] ?? $errDecode['error']));
                }
            }

            return $this->response
                        ->setHeader('Content-Type', 'application/pdf')
                        ->setHeader('Content-Disposition', 'inline; filename="THERMAL_AWB_'.$orderSn.'.pdf"')
                        ->setBody($fileContent);

        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    // --- FITUR: HALAMAN MASS FULFILLMENT ---
    public function mass_fulfillment()
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/portal');

        $orders = $this->db->table('sales_orders')
                           ->where('order_status', 'READY_TO_SHIP')
                           ->orderBy('shipping_carrier', 'ASC') 
                           ->orderBy('order_date', 'ASC')
                           ->get()->getResultArray();

        $data = [
            'title'  => 'Pusat Pemrosesan Massal',
            'orders' => $orders
        ];

        return view('warehouse/mass_fulfillment', $data);
    }

    // --- FITUR: PROSES PICKUP MASSAL & CETAK RESI MASSAL ---
    public function process_mass_action()
    {
        $action = $this->request->getPost('action_type'); // 'ship' atau 'print'
        $orderSns = $this->request->getPost('selected_orders');

        if (empty($orderSns)) {
            return redirect()->back()->with('error', 'Pilih minimal 1 pesanan terlebih dahulu.');
        }

        $shopeeApi = new \App\Libraries\ShopeeApi();
        
        $firstOrder = $this->db->table('sales_orders')->where('order_sn', $orderSns[0])->get()->getRowArray();
        $shopId = $firstOrder['shop_id'];

        try {
            if ($action === 'ship') {
                $successCount = 0;
                
                $this->db->transStart(); // Mulai Transaksi Massal Lokal

                foreach ($orderSns as $sn) {
                    $paramResp = $shopeeApi->getShippingParameter($sn, $shopId);
                    $pickupData = [];
                    if (isset($paramResp['response']['info_needed']['pickup']['address_list'][0])) {
                        $pickupData['address_id'] = $paramResp['response']['info_needed']['pickup']['address_list'][0]['address_id'];
                    }

                    $shipResp = $shopeeApi->shipOrder($sn, $shopId, $pickupData, []);
                    
                    if (!isset($shipResp['error']) || $shipResp['error'] === '') {
                        
                        // 1. Ubah status lokal
                        $this->db->table('sales_orders')->where('order_sn', $sn)->update(['order_status' => 'PACKED']);
                        
                        // ========================================================
                        // 2. KUNCI OMNICHANNEL MASSAL: POTONG STOK MASTER LOKAL
                        // ========================================================
                        $orderItems = $this->db->table('sales_order_items')->where('order_sn', $sn)->get()->getResultArray();
                        foreach ($orderItems as $item) {
                            $sku = $item['item_sku']; 
                            $qty = $item['model_qty'];
                            
                            $this->db->query("UPDATE warehouse_inventory SET physical_stock = physical_stock - ? WHERE sku = ?", [$qty, $sku]);
                        }
                        // ========================================================

                        $successCount++;
                    }
                }

                $this->db->transComplete();

                if ($this->db->transStatus() === false) {
                    throw new \Exception("Gagal mengunci transaksi ke database.");
                }

                return redirect()->back()->with('success', "Sukses! Kurir telah dipanggil & Stok Gudang Terpotong untuk <b>{$successCount} pesanan</b>.");
            }

            if ($action === 'print') {
                $createResp = $shopeeApi->createMassShippingDocument($orderSns, $shopId);
                
                $isReady = false;
                $attempts = 0;
                while (!$isReady && $attempts < 4) {
                    usleep(2000000); 
                    $resResp = $shopeeApi->getMassShippingDocumentResult($orderSns, $shopId);
                    
                    if (isset($resResp['response']['result_list'][0]['status']) && $resResp['response']['result_list'][0]['status'] === 'READY') {
                        $isReady = true;
                    }
                    $attempts++;
                }

                $pdfContent = $shopeeApi->downloadMassShippingDocument($orderSns, $shopId);

                return $this->response
                            ->setHeader('Content-Type', 'application/pdf')
                            ->setHeader('Content-Disposition', 'inline; filename="MASS_AWB_'.date('YmdHis').'.pdf"')
                            ->setBody($pdfContent);
            }

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan sistem: ' . $e->getMessage());
        }
    }

    // ===========================================================================
    // FITUR: PUSAT RESOLUSI PEMBATALAN PEMBELI (CANCELLATION HUB)
    // ===========================================================================
    public function cancellation_hub()
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/portal');

        $shopeeApi = new \App\Libraries\ShopeeApi();
        // Ambil toko utama yang sedang aktif
        $shop = $this->db->table('shopee_integrations')->where('status', 'Active')->get()->getRowArray();
        
        if (!$shop) return redirect()->to('/shopee')->with('error', 'Tidak ada toko Shopee yang aktif.');

        $shopId = $shop['shop_id'];
        $cancelOrders = [];

        try {
            // 1. Tarik pesanan berstatus IN_CANCEL secara Real-Time dari Server Shopee
            $resp = $shopeeApi->getInCancelOrders($shopId);
            $orderSns = [];
            
            if (isset($resp['response']['order_list'])) {
                foreach ($resp['response']['order_list'] as $o) {
                    $orderSns[] = $o['order_sn'];
                }
            }

            // 2. Jika ada pesanan yang minta dibatalkan, tarik detail alasannya
            if (!empty($orderSns)) {
                $detailResp = $shopeeApi->getCancelOrderDetails($shopId, $orderSns);
                if (isset($detailResp['response']['order_list'])) {
                    $cancelOrders = $detailResp['response']['order_list'];
                }
            }

        } catch (\Exception $e) {
            session()->setFlashdata('error', 'Gagal menarik data batal dari Shopee: ' . $e->getMessage());
        }

        $data = [
            'title'        => 'Pusat Resolusi Pembatalan',
            'shop'         => $shop,
            'cancelOrders' => $cancelOrders
        ];

        return view('warehouse/cancellation_hub', $data);
    }

    // --- FITUR: PROSES TERIMA / TOLAK PEMBATALAN ---
    public function process_cancellation()
    {
        $shopId    = $this->request->getPost('shop_id');
        $orderSn   = $this->request->getPost('order_sn');
        $operation = $this->request->getPost('operation'); // ACCEPT / REJECT

        $shopeeApi = new \App\Libraries\ShopeeApi();
        
       try {
            // PERBAIKAN: Nama fungsi dan urutan parameter yang benar
            $resp = $shopeeApi->handleBuyerCancellation($shopId, $orderSn, $operation);
            
            if (isset($resp['error']) && $resp['error'] !== '') {
                throw new \Exception($resp['message'] ?? $resp['error']);
            }

            // Jika operasi disetujui (ACCEPT), ubah status di DB Lokal menjadi CANCELLED
            if ($operation === 'ACCEPT') {
                $exists = $this->db->table('sales_orders')->where('order_sn', $orderSn)->countAllResults();
                if ($exists > 0) {
                    $this->db->table('sales_orders')->where('order_sn', $orderSn)->update(['order_status' => 'CANCELLED']);
                }
            }

            $msg = ($operation === 'ACCEPT') 
                 ? "Pembatalan DISETUJUI. Dana akan dikembalikan ke pembeli." 
                 : "Pembatalan DITOLAK! Silakan lanjutkan packing dan serahkan paket ke kurir.";

            return redirect()->back()->with('success', "Aksi Berhasil! " . $msg);

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal merespon pembatalan: ' . $e->getMessage());
        }
    }
}