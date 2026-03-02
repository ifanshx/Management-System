<?php

namespace App\Libraries;

class ShopeeApi
{
    private $partnerId;
    private $partnerKey;
    private $host;

    public function __construct()
    {
        $this->partnerId  = getenv('SHOPEE_PARTNER_ID');
        $this->partnerKey = getenv('SHOPEE_PARTNER_KEY');
        
        // Cek Environment berdasarkan dokumentasi resmi
        if (getenv('SHOPEE_ENV') === 'production') {
            $this->host = "https://partner.shopeemobile.com";
        } else {
            $this->host = "https://openplatform.sandbox.test-stable.shopee.sg";
        }
    }

    /**
     * 1. GENERATE AUTHORIZATION LINK
     * (Sesuai dokumentasi: Generating the authorization link)
     */
    public function getAuthUrl()
    {
        $path = "/api/v2/shop/auth_partner";
        $redirectUrl = getenv('SHOPEE_REDIRECT_URL');
        $timestamp = time();
        
        // Base String untuk otorisasi: partner_id + api path + timestamp
        $baseString = sprintf("%s%s%s", $this->partnerId, $path, $timestamp);
        $sign = hash_hmac('sha256', $baseString, $this->partnerKey);
        
        return sprintf("%s%s?partner_id=%s&timestamp=%s&sign=%s&redirect=%s", 
            $this->host, $path, $this->partnerId, $timestamp, $sign, urlencode($redirectUrl)
        );
    }

    /**
     * 2. CORE METHOD UNTUK REQUEST API
     * Menangani GET / POST dengan Signature HMAC-SHA256
     */
    public function callApi($path, $bodyParams = [], $method = 'POST', $accessToken = '', $shopId = '')
    {
        $timestamp = time();
        
        // Beda API, beda susunan Base String (Sesuai dokumentasi)
        // Untuk Shop API: partner_id, api path, timestamp, access_token, shop_id
        if ($accessToken && $shopId) {
            $baseString = sprintf("%s%s%s%s%s", $this->partnerId, $path, $timestamp, $accessToken, $shopId);
        } else {
            // Untuk Public API
            $baseString = sprintf("%s%s%s", $this->partnerId, $path, $timestamp);
        }

        $sign = hash_hmac('sha256', $baseString, $this->partnerKey);

        // Parameter URL wajib
        $urlParams = [
            'partner_id' => $this->partnerId,
            'timestamp'  => $timestamp,
            'sign'       => $sign
        ];
        
        if ($accessToken) $urlParams['access_token'] = $accessToken;
        if ($shopId)      $urlParams['shop_id'] = $shopId;

        $url = $this->host . $path . '?' . http_build_query($urlParams);

        // cURL Setup
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        if ($method === 'POST') {
            // Shopee mengharuskan POST body digabung dengan parameter partner_id
            $bodyParams['partner_id'] = (int)$this->partnerId;
            if ($shopId) $bodyParams['shop_id'] = (int)$shopId;

            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($bodyParams));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        }

        $response = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);

        if ($err) throw new \Exception("cURL Error: " . $err);

        return json_decode($response, true);
    }

    /**
     * 3. MENDAPATKAN ACCESS TOKEN PERTAMA KALI
     * (Sesuai dokumentasi: GetAccesstoken)
     */
    public function getAccessToken($code, $shopId)
    {
        $path = "/api/v2/auth/token/get";
        $body = [
            'code' => $code
        ];
        // Shopee requires shop_id di body untuk request ini
        return $this->callApi($path, $body, 'POST', '', $shopId);
    }

    /**
     * 4. REFRESH ACCESS TOKEN (Berlaku 30 Hari)
     * (Sesuai dokumentasi: RefreshAccessToken)
     */
    public function refreshAccessToken($refreshToken, $shopId)
    {
        $path = "/api/v2/auth/access_token/get";
        $body = [
            'refresh_token' => $refreshToken
        ];
        // Request ini menggunakan skema Public API (tanpa access_token)
        return $this->callApi($path, $body, 'POST', '', $shopId);
    }

    /**
     * 5A. GET SHIPPING PARAMETER
     * Wajib dipanggil sebelum shipOrder untuk mendapatkan address_id dan time_slot
     */
    public function getShippingParameter($orderSn, $shopId)
    {
        $path = "/api/v2/logistics/get_shipping_parameter";
        $params = ['order_sn' => $orderSn];
        
        return $this->callApi($path, $params, 'GET', $this->getValidToken($shopId), $shopId);
    }

    /**
     * 5B. ARRANGE SHIPMENT (Kirim Pesanan)
     * Menggunakan parameter dinamis dari dokumentasi resmi
     */
    public function shipOrder($orderSn, $shopId, $pickupData = [], $dropoffData = [])
    {
        $path = "/api/v2/logistics/ship_order";
        
        $body = [
            'order_sn' => $orderSn
        ];

        // Sesuai dokumentasi, masukkan object pickup JIKA ada isinya
        if (!empty($pickupData)) {
            $body['pickup'] = $pickupData;
        } 
        // Atau masukkan object dropoff
        elseif (!empty($dropoffData)) {
            $body['dropoff'] = $dropoffData;
        }

        return $this->callApi($path, $body, 'POST', $this->getValidToken($shopId), $shopId);
    }

    /**
     * TAHAP 1: CREATE SHIPPING DOCUMENT
     * Meminta Shopee untuk mulai membuat/merender file resi pengiriman.
     */
    public function createShippingDocument($orderSn, $shopId)
    {
        $path = "/api/v2/logistics/create_shipping_document";
        $body = [
            'order_list' => [
                ['order_sn' => $orderSn]
            ]
        ];
        
        return $this->callApi($path, $body, 'POST', $this->getValidToken($shopId), $shopId);
    }

    /**
     * TAHAP 2: GET SHIPPING DOCUMENT RESULT
     * Mengecek apakah render file resi dari Tahap 1 sudah selesai (READY).
     */
    public function getShippingDocumentResult($orderSn, $shopId)
    {
        $path = "/api/v2/logistics/get_shipping_document_result";
        $body = [
            'order_list' => [
                ['order_sn' => $orderSn]
            ],
            // Kita minta format Thermal sesuai standar pergudangan modern
            'shipping_document_type' => 'THERMAL_AIR_WAYBILL'
        ];
        
        return $this->callApi($path, $body, 'POST', $this->getValidToken($shopId), $shopId);
    }

    /**
     * TAHAP 3: DOWNLOAD SHIPPING DOCUMENT
     * Mengunduh file waybill (resi) yang mengembalikan file PDF murni.
     */
    public function downloadShippingDocument($orderSn, $shopId)
    {
        $path = "/api/v2/logistics/download_shipping_document";
        $body = [
            'shipping_document_type' => 'THERMAL_AIR_WAYBILL',
            'order_list' => [
                ['order_sn' => $orderSn]
            ]
        ];

        // Hasil dari fungsi ini bisa berupa File (PDF Thermal) ATAU JSON jika ada error.
        return $this->callApi($path, $body, 'POST', $this->getValidToken($shopId), $shopId);
    }

    /**
     * HELPER: GET VALID TOKEN (SELF-HEALING)
     * Mengambil token dari DB. Jika sisa waktu < 15 menit, otomatis perpanjang (Refresh) dulu!
     */
    private function getValidToken($shopId)
    {
        $db = \Config\Database::connect();
        $shop = $db->table('shopee_integrations')->where('shop_id', $shopId)->get()->getRowArray();
        
        if (!$shop) return '';

        // Jika token akan kedaluwarsa dalam waktu kurang dari 15 menit (900 detik)
        if (time() > ($shop['expire_at'] - 900)) {
            // Panggil API Refresh Token
            $path = "/api/v2/auth/access_token/get";
            $body = ['refresh_token' => $shop['refresh_token']];
            
            // Kita bypass callApi biasa agar tidak terjadi infinite loop
            $timestamp = time();
            $baseString = sprintf("%s%s%s", $this->partnerId, $path, $timestamp);
            $sign = hash_hmac('sha256', $baseString, $this->partnerKey);
            
            $url = $this->host . $path . '?partner_id=' . $this->partnerId . '&timestamp=' . $timestamp . '&sign=' . $sign;
            
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            $body['partner_id'] = (int)$this->partnerId;
            $body['shop_id'] = (int)$shopId;
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            
            $response = curl_exec($ch);
            curl_close($ch);
            $result = json_decode($response, true);

            // Jika berhasil di-refresh, update database dan gunakan token baru
            if (isset($result['access_token'])) {
                $db->table('shopee_integrations')->where('shop_id', $shopId)->update([
                    'access_token'  => $result['access_token'],
                    'refresh_token' => $result['refresh_token'],
                    'expire_at'     => time() + $result['expire_in'],
                    'status'        => 'Active'
                ]);
                return $result['access_token'];
            } else {
                // Jika gagal (misal refresh token mati setelah 30 hari), set Disconnected
                $db->table('shopee_integrations')->where('shop_id', $shopId)->update(['status' => 'Disconnected']);
                return ''; // Memaksa admin login ulang
            }
        }

        // Jika masih valid, kembalikan token yang ada di database
        return $shop['access_token'];
    }
    /**
     * ===========================================================================
     * MODUL PRODUK & INVENTORY (ENTERPRISE LEVEL)
     * ===========================================================================
     */

    /**
     * 7. GET ITEM LIST
     * Menarik daftar ID Produk dari etalase Shopee (Maksimal 100 per halaman).
     */
    public function getItemList($shopId, $offset = 0, $pageSize = 100)
    {
        $path = "/api/v2/product/get_item_list";
        $params = [
            'offset'      => $offset,
            'page_size'   => $pageSize,
            'item_status' => 'NORMAL' // Hanya ambil produk yang aktif (tidak dibanned/dihapus)
        ];
        
        return $this->callApi($path, $params, 'GET', $this->getValidToken($shopId), $shopId);
    }

    /**
     * 8. GET ITEM BASE INFO
     * Menerjemahkan ID Produk menjadi Nama, Harga, SKU, dan Stok.
     */
    public function getItemBaseInfo($itemIdList, $shopId)
    {
        $path = "/api/v2/product/get_item_base_info";
        
        // Shopee API v2 mensyaratkan list ID digabung menjadi string dipisah koma
        // Pastikan array tidak melebihi 50 ID sebelum dipanggil (akan diatur di Controller)
        $params = [
            'item_id_list' => implode(',', $itemIdList) 
        ];
        
        return $this->callApi($path, $params, 'GET', $this->getValidToken($shopId), $shopId);
    }

    /**
     * 9. UPDATE STOCK (Versi Terbaru Sesuai Aturan 2022)
     * Menggunakan struktur 'seller_stock' alih-alih 'normal_stock'.
     */
    public function updateStock($itemId, $newStock, $shopId, $modelId = 0)
    {
        $path = "/api/v2/product/update_stock";
        
        $body = [
            'item_id' => (int)$itemId,
            'stock_list' => [
                [
                    'model_id' => (int)$modelId, // 0 jika tidak ada variasi ukuran/warna
                    'seller_stock' => [
                        [
                            'location_id' => '', // Dikosongkan agar Shopee memakai gudang default
                            'stock' => (int)$newStock
                        ]
                    ]
                ]
            ]
        ];
        
        return $this->callApi($path, $body, 'POST', $this->getValidToken($shopId), $shopId);
    }
    
    /**
     * 10. UPLOAD IMAGE KE SHOPEE MEDIA SPACE
     * Shopee mewajibkan kita mengunggah foto ke server mereka dulu sebelum memposting barang.
     */
    public function uploadImage($filePath, $shopId)
    {
        $path = "/api/v2/media_space/upload_image";
        
        // Membaca file fisik untuk dikirim via cURL Multipart
        $cfile = new \CURLFile($filePath, mime_content_type($filePath), basename($filePath));
        
        $body = [
            'image' => $cfile
        ];

        // Karena ini Multipart/form-data, kita gunakan custom cURL khusus di dalam fungsi ini
        $timestamp = time();
        $accessToken = $this->getValidToken($shopId);
        $baseString = sprintf("%s%s%s%s%s", $this->partnerId, $path, $timestamp, $accessToken, $shopId);
        $sign = hash_hmac('sha256', $baseString, $this->partnerKey);

        $url = $this->host . $path . '?partner_id=' . $this->partnerId . '&timestamp=' . $timestamp . '&sign=' . $sign . '&access_token=' . $accessToken . '&shop_id=' . $shopId;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response, true);
    }

    /**
     * 11. ADD NEW ITEM (PUBLISH KE SHOPEE)
     */
    public function addItem($payload, $shopId)
    {
        $path = "/api/v2/product/add_item";
        return $this->callApi($path, $payload, 'POST', $this->getValidToken($shopId), $shopId);
    }

    /**
     * ===========================================================================
     * MODUL KEUANGAN & PEMBAYARAN (FINANCE & ESCROW)
     * ===========================================================================
     */

    /**
     * 12. GET ESCROW DETAIL
     * Menarik rincian uang masuk (Payout), potongan admin, dan biaya layanan per pesanan.
     */
    public function getEscrowDetail($orderSn, $shopId)
    {
        $path = "/api/v2/payment/get_escrow_detail";
        $params = [
            'order_sn' => $orderSn
        ];
        
        // Sesuai dokumentasi Shopee, Get Escrow Detail menggunakan method GET
        return $this->callApi($path, $params, 'GET', $this->getValidToken($shopId), $shopId);
    }

    /**
     * ===========================================================================
     * MODUL CUSTOMER SERVICE (SELLER CHAT)
     * ===========================================================================
     */

    /**
     * 13. GET CONVERSATION LIST
     * Mengambil daftar orang yang sedang chat dengan toko kita.
     */
    public function getConversationList($shopId, $offset = 0, $pageSize = 20)
    {
        $path = "/api/v2/sellerchat/get_conversation_list";
        $params = [
            'direction' => 'latest',
            'type'      => 'all', // Ambil semua (sudah dibaca maupun belum)
            'page_size' => $pageSize
        ];

        // Parameter offset tidak boleh dikirim jika nilainya 0 (aturan khusus API Chat Shopee)
        if ($offset > 0) {
            $params['next_timestamp_offset'] = $offset;
        }

        return $this->callApi($path, $params, 'GET', $this->getValidToken($shopId), $shopId);
    }

    /**
     * 14. GET MESSAGE (Riwayat Chat)
     * Mengambil isi percakapan dengan satu pembeli tertentu.
     */
    public function getMessages($conversationId, $shopId, $offset = 0, $pageSize = 50)
    {
        $path = "/api/v2/sellerchat/get_message";
        $params = [
            'conversation_id' => $conversationId,
            'page_size'       => $pageSize
        ];

        if ($offset > 0) {
            $params['offset'] = $offset;
        }

        return $this->callApi($path, $params, 'GET', $this->getValidToken($shopId), $shopId);
    }

    /**
     * 15. SEND MESSAGE (Balas Chat)
     * Mengirim pesan teks ke pembeli.
     */
    public function sendMessage($toId, $messageText, $shopId)
    {
        $path = "/api/v2/sellerchat/send_message";
        $body = [
            'to_id'        => (int)$toId,
            'message_type' => 'text',
            'content'      => [
                'text' => $messageText
            ]
        ];

        return $this->callApi($path, $body, 'POST', $this->getValidToken($shopId), $shopId);
    }

    /**
     * 16. GET RETURN LIST
     * Mengambil daftar pengembalian barang atau dana dari pembeli
     */
    public function getReturnList($shopId)
    {
        $path = "/api/v2/returns/get_return_list";
        $params = [
            'page_size' => 50,
            'create_time_from' => time() - (86400 * 14), // 14 Hari terakhir
            'create_time_to'   => time()
        ];

        return $this->callApi($path, $params, 'GET', $this->getValidToken($shopId), $shopId);
    }
    
    /**
     * 17. HANDLE BUYER CANCELLATION
     * Menyetujui atau menolak permintaan batal dari pembeli
     */
    public function handleCancellation($orderSn, $operation, $shopId)
    {
        $path = "/api/v2/order/handle_buyer_cancellation";
        $body = [
            'order_sn'  => $orderSn,
            'operation' => $operation // 'ACCEPT' atau 'REJECT'
        ];

        return $this->callApi($path, $body, 'POST', $this->getValidToken($shopId), $shopId);
    }

    /**
     * ===========================================================================
     * MODUL MARKETING & PROMOSI (DISCOUNT ENGINE)
     * ===========================================================================
     */

    /**
     * 18. ADD DISCOUNT (Buat Campaign / Nama Promo)
     * Shopee mewajibkan kita membuat "Wadah Promo" nya dulu beserta tanggal aktifnya.
     */
    public function addDiscount($shopId, $discountName, $startTime, $endTime)
    {
        $path = "/api/v2/discount/add_discount";
        $body = [
            'discount_name' => $discountName,
            'start_time'    => $startTime, // Format: UNIX Timestamp
            'end_time'      => $endTime    // Format: UNIX Timestamp
        ];

        return $this->callApi($path, $body, 'POST', $this->getValidToken($shopId), $shopId);
    }

    /**
     * 19. ADD DISCOUNT ITEM (Masukkan Knalpot ke dalam Promo & Set Harga Baru)
     */
    public function addDiscountItem($shopId, $discountId, $itemId, $modelId = 0, $promoPrice, $purchaseLimit = 0)
    {
        $path = "/api/v2/discount/add_discount_item";
        $body = [
            'discount_id' => (int)$discountId,
            'item_list'   => [
                [
                    'item_id' => (int)$itemId,
                    'model_list' => [
                        [
                            'model_id' => (int)$modelId, // 0 jika tidak ada varian ukuran/warna
                            'model_promotion_price' => (float)$promoPrice
                        ]
                    ],
                    'purchase_limit' => (int)$purchaseLimit // 0 berarti tanpa batas pembelian per akun
                ]
            ]
        ];

        return $this->callApi($path, $body, 'POST', $this->getValidToken($shopId), $shopId);
    }

    /**
     * ===========================================================================
     * MODUL MASS FULFILLMENT (SKALA PABRIK BESAR)
     * ===========================================================================
     */

    public function createMassShippingDocument($orderSns, $shopId)
    {
        $path = "/api/v2/logistics/create_shipping_document";
        
        // Membentuk array sesuai standar Shopee: [{order_sn: '123'}, {order_sn: '456'}]
        $orderList = [];
        foreach ($orderSns as $sn) {
            $orderList[] = ['order_sn' => $sn];
        }

        $body = ['order_list' => $orderList];
        return $this->callApi($path, $body, 'POST', $this->getValidToken($shopId), $shopId);
    }

    public function getMassShippingDocumentResult($orderSns, $shopId)
    {
        $path = "/api/v2/logistics/get_shipping_document_result";
        $orderList = [];
        foreach ($orderSns as $sn) {
            $orderList[] = ['order_sn' => $sn];
        }

        $body = [
            'order_list' => $orderList,
            'shipping_document_type' => 'THERMAL_AIR_WAYBILL'
        ];
        return $this->callApi($path, $body, 'POST', $this->getValidToken($shopId), $shopId);
    }

    public function downloadMassShippingDocument($orderSns, $shopId)
    {
        $path = "/api/v2/logistics/download_shipping_document";
        $orderList = [];
        foreach ($orderSns as $sn) {
            $orderList[] = ['order_sn' => $sn];
        }

        $body = [
            'shipping_document_type' => 'THERMAL_AIR_WAYBILL',
            'order_list' => $orderList
        ];
        // Akan mengembalikan 1 file PDF panjang berisi puluhan/ratusan halaman resi
        return $this->callApi($path, $body, 'POST', $this->getValidToken($shopId), $shopId);
    }

    /**
     * ===========================================================================
     * MODUL OPTIMASI & TRAFIK (BOOST ENGINE)
     * ===========================================================================
     */

    /**
     * 20. GET BOOSTED LIST
     * Mengecek produk apa saja yang sedang dalam masa Boost (Maksimal 5 slot).
     */
    public function getBoostedList($shopId)
    {
        $path = "/api/v2/product/get_boosted_list";
        return $this->callApi($path, [], 'GET', $this->getValidToken($shopId), $shopId);
    }

    /**
     * 21. BOOST ITEM
     * Menaikkan produk ke pencarian teratas (Bisa menerima array berisi maksimal 5 ID Produk).
     */
    public function boostItem($itemIds, $shopId)
    {
        $path = "/api/v2/product/boost_item";
        
        // Memastikan item_id berupa integer
        $formattedIds = array_map('intval', $itemIds);
        
        $body = [
            'item_id_list' => $formattedIds
        ];

        return $this->callApi($path, $body, 'POST', $this->getValidToken($shopId), $shopId);
    }

    /**
     * ===========================================================================
     * MODUL MARKETING LANJUTAN (VOUCHER ENGINE)
     * ===========================================================================
     */

    /**
     * 22. GET VOUCHER LIST
     * Mengambil daftar voucher berdasarkan status (upcoming, ongoing, expired)
     */
    public function getVoucherList($shopId, $status = 'ongoing')
    {
        $path = "/api/v2/voucher/get_voucher_list";
        $params = [
            'status' => $status,
            'page_size' => 50
        ];
        return $this->callApi($path, $params, 'GET', $this->getValidToken($shopId), $shopId);
    }

    /**
     * 23. ADD VOUCHER (BUAT KUPON BARU)
     */
    public function addVoucher($shopId, $payload)
    {
        $path = "/api/v2/voucher/add_voucher";
        return $this->callApi($path, $payload, 'POST', $this->getValidToken($shopId), $shopId);
    }

    /**
     * 24. END VOUCHER (HENTIKAN KUPON)
     * Memaksa kupon berhenti sebelum waktu berakhirnya habis
     */
    public function endVoucher($shopId, $voucherId)
    {
        $path = "/api/v2/voucher/end_voucher";
        $body = [
            'voucher_id' => (int)$voucherId
        ];
        return $this->callApi($path, $body, 'POST', $this->getValidToken($shopId), $shopId);
    }

    /**
     * ===========================================================================
     * MODUL KOMBO HEMAT (BUNDLE DEALS)
     * ===========================================================================
     */

    /**
     * 25. ADD BUNDLE DEAL
     * Membuat kerangka promosi Kombo Hemat (Contoh: Beli 2 Diskon 10%)
     */
    public function addBundleDeal($shopId, $payload)
    {
        $path = "/api/v2/bundle_deal/add_bundle_deal";
        return $this->callApi($path, $payload, 'POST', $this->getValidToken($shopId), $shopId);
    }

    /**
     * 26. ADD BUNDLE DEAL ITEM
     * Memasukkan produk-produk ke dalam kerangka promosi Kombo Hemat
     */
    public function addBundleDealItem($shopId, $bundleId, $itemIds)
    {
        $path = "/api/v2/bundle_deal/add_bundle_deal_item";
        
        $itemList = [];
        foreach ($itemIds as $id) {
            $itemList[] = [
                'item_id' => (int)$id,
                'status'  => 1 // 1 = Enable (Aktif di bundle ini)
            ];
        }

        $body = [
            'bundle_deal_id' => (int)$bundleId,
            'item_list'      => $itemList
        ];

        return $this->callApi($path, $body, 'POST', $this->getValidToken($shopId), $shopId);
    }

    /**
     * ===========================================================================
     * MODUL RESOLUSI & PEMBATALAN PESANAN (CANCELLATION HUB)
     * ===========================================================================
     */

    /**
     * 27. GET IN_CANCEL ORDERS
     * Menarik pesanan yang saat ini sedang diajukan pembatalan oleh pembeli.
     */
    public function getInCancelOrders($shopId, $days = 15)
    {
        $path = "/api/v2/order/get_order_list";
        $params = [
            'time_range_field' => 'create_time',
            'time_from'        => time() - ($days * 86400),
            'time_to'          => time(),
            'page_size'        => 50,
            'order_status'     => 'IN_CANCEL'
        ];
        return $this->callApi($path, $params, 'GET', $this->getValidToken($shopId), $shopId);
    }

    /**
     * 28. GET CANCEL ORDER DETAILS
     * Menarik alasan kenapa pembeli membatalkan pesanan.
     */
    public function getCancelOrderDetails($shopId, $orderSnList)
    {
        $path = "/api/v2/order/get_order_detail";
        $params = [
            'order_sn_list'            => implode(',', $orderSnList),
            'response_optional_fields' => 'buyer_username,item_list,cancel_reason,cancel_by,total_amount'
        ];
        return $this->callApi($path, $params, 'GET', $this->getValidToken($shopId), $shopId);
    }

    /**
     * 29. HANDLE BUYER CANCELLATION
     * Menjawab permintaan batal (Operation: ACCEPT atau REJECT).
     */
    public function handleBuyerCancellation($shopId, $orderSn, $operation)
    {
        $path = "/api/v2/order/handle_buyer_cancellation";
        $body = [
            'order_sn'  => $orderSn,
            'operation' => strtoupper($operation) // Harus 'ACCEPT' atau 'REJECT'
        ];
        return $this->callApi($path, $body, 'POST', $this->getValidToken($shopId), $shopId);
    }

    /**
     * ===========================================================================
     * MODUL KATALOG & VARIAN (PRODUCT VARIATIONS)
     * ===========================================================================
     */

    /**
     * 30. INIT TIER VARIATION
     * Mengatur Varian Produk (Contoh: Warna & Ukuran) beserta harga dan stoknya.
     */
    public function initTierVariation($shopId, $itemId, $tierVariation, $modelList)
    {
        $path = "/api/v2/product/init_tier_variation";
        
        $body = [
            'item_id'        => (int)$itemId,
            'tier_variation' => $tierVariation,
            'model'          => $modelList
        ];

        return $this->callApi($path, $body, 'POST', $this->getValidToken($shopId), $shopId);
    }

    /**
     * ===========================================================================
     * MODUL PAKET DISKON (ADD-ON DEALS)
     * ===========================================================================
     */

    /**
     * 31. ADD ADD-ON DEAL (Buat Kerangka Promosi)
     */
    public function addAddOnDeal($shopId, $payload)
    {
        $path = "/api/v2/add_on_deal/add_add_on_deal";
        return $this->callApi($path, $payload, 'POST', $this->getValidToken($shopId), $shopId);
    }

    /**
     * 32. ADD MAIN ITEM (Masukkan Produk Utama)
     */
    public function addAddOnDealMainItem($shopId, $addOnDealId, $itemIds)
    {
        $path = "/api/v2/add_on_deal/add_add_on_deal_main_item";
        
        $itemList = [];
        foreach ($itemIds as $id) {
            $itemList[] = ['item_id' => (int)$id, 'status' => 1];
        }

        $body = [
            'add_on_deal_id' => (int)$addOnDealId,
            'main_item_list' => $itemList
        ];

        return $this->callApi($path, $body, 'POST', $this->getValidToken($shopId), $shopId);
    }

    /**
     * 33. ADD SUB ITEM (Masukkan Produk Tambahan/Aksesoris beserta Harga Khususnya)
     */
    public function addAddOnDealSubItem($shopId, $addOnDealId, $subItemList)
    {
        $path = "/api/v2/add_on_deal/add_add_on_deal_sub_item";
        $body = [
            'add_on_deal_id' => (int)$addOnDealId,
            'sub_item_list'  => $subItemList
        ];

        return $this->callApi($path, $body, 'POST', $this->getValidToken($shopId), $shopId);
    }

    /**
     * ===========================================================================
     * MODUL REPUTASI & ULASAN PEMBELI (REVIEW MANAGEMENT)
     * ===========================================================================
     */

    /**
     * 34. GET COMMENT (Tarik Ulasan)
     * Menarik daftar ulasan pembeli beserta rating bintangnya.
     */
    public function getComment($shopId, $pageSize = 50, $cursor = '')
    {
        $path = "/api/v2/product/get_comment";
        $params = [
            'page_size' => $pageSize
        ];
        if (!empty($cursor)) {
            $params['cursor'] = $cursor;
        }

        return $this->callApi($path, $params, 'GET', $this->getValidToken($shopId), $shopId);
    }

    /**
     * 35. REPLY COMMENT (Balas Ulasan)
     * Mengirimkan teks balasan dari penjual (Seller Reply) ke ulasan pembeli.
     */
    public function replyComment($shopId, $commentId, $replyText)
    {
        $path = "/api/v2/product/reply_comment";
        $body = [
            'comment_list' => [
                [
                    'comment_id' => (int)$commentId,
                    'comment'    => $replyText
                ]
            ]
        ];

        return $this->callApi($path, $body, 'POST', $this->getValidToken($shopId), $shopId);
    }

    /**
     * ===========================================================================
     * MODUL MANAJEMEN HARGA MASSAL (MASS PRICE UPDATER)
     * ===========================================================================
     */

    /**
     * 36. UPDATE PRICE
     * Mengubah harga asli produk di Shopee.
     */
    public function updatePrice($shopId, $itemId, $originalPrice)
    {
        $path = "/api/v2/product/update_price";
        
        // Aturan Shopee V2: Harga diupdate melalui array price_list
        // model_id = 0 berarti produk utama (bukan varian).
        $body = [
            'item_id'    => (int)$itemId,
            'price_list' => [
                [
                    'model_id'       => 0, 
                    'original_price' => (float)$originalPrice
                ]
            ]
        ];

        return $this->callApi($path, $body, 'POST', $this->getValidToken($shopId), $shopId);
    }

    /**
     * ===========================================================================
     * MODUL RETUR & SENGKETA PEMBELI (DISPUTE MANAGEMENT)
     * ===========================================================================
     */


    /**
     * 38. CONFIRM RETURN
     * Menyetujui pengembalian dana pembeli (Jika memang murni kesalahan pabrik).
     */
    public function confirmReturn($shopId, $returnSn)
    {
        $path = "/api/v2/returns/confirm";
        $body = [
            'return_sn' => $returnSn
        ];
        return $this->callApi($path, $body, 'POST', $this->getValidToken($shopId), $shopId);
    }

    /**
     * 39. DISPUTE RETURN
     * Mengajukan sengketa/menolak retur karena pembeli curang atau alasan tidak valid.
     */
    public function disputeReturn($shopId, $returnSn, $disputeReason, $disputeText)
    {
        $path = "/api/v2/returns/dispute";
        $body = [
            'return_sn'           => $returnSn,
            'email'               => 'cs@noric-exhaust.com', // Email resmi toko untuk korespondensi tim Shopee
            'dispute_reason'      => $disputeReason, // Pilih dari GET_RETURN_DISPUTE_REASON (Biasanya dikirim dari form)
            'dispute_text_reason' => $disputeText
        ];
        return $this->callApi($path, $body, 'POST', $this->getValidToken($shopId), $shopId);
    }
}