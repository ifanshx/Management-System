<?php

namespace App\Controllers;
use App\Libraries\ShopeeApi;

class ShopeeAddon extends BaseController
{
    private $db;
    private $shopeeApi;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->shopeeApi = new ShopeeApi();
    }

    // --- HALAMAN SETUP PAKET DISKON ---
    public function index($shopId)
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/portal');

        $shop = $this->db->table('shopee_integrations')->where('shop_id', $shopId)->get()->getRowArray();
        if (!$shop) return redirect()->to('/shopee')->with('error', 'Toko tidak ditemukan.');

        // Ambil produk untuk dipilih sebagai Utama / Tambahan
        $products = $this->db->table('shopee_products')
                             ->where('shop_id', $shopId)
                             ->where('status', 'NORMAL')
                             ->get()->getResultArray();

        $data = [
            'title'    => 'Mesin Paket Diskon (Add-on)',
            'shop'     => $shop,
            'products' => $products
        ];

        return view('shopee/addon_center', $data);
    }

    // --- PROSES SUBMIT KE SHOPEE ---
    public function create_addon($shopId)
    {
        try {
            $addonName = $this->request->getPost('addon_name');
            $startTime = strtotime($this->request->getPost('start_time'));
            $endTime   = strtotime($this->request->getPost('end_time'));
            $purchaseLimit = (int)$this->request->getPost('purchase_limit');

            // Data Produk Utama & Tambahan (Dari JSON yang digenerate Javascript)
            $mainItems = json_decode($this->request->getPost('main_items'), true);
            $subItems  = json_decode($this->request->getPost('sub_items'), true);

            if (empty($mainItems) || empty($subItems)) {
                throw new \Exception("Pilih minimal 1 Produk Utama dan 1 Produk Tambahan.");
            }

            // TAHAP 1: Buat Kerangka Add-on Deal
            $payload = [
                'add_on_deal_name' => $addonName,
                'start_time'       => $startTime,
                'end_time'         => $endTime,
                'promotion_type'   => 1, // 1 = Add-on Discount
                'purchase_min_spend' => 0, 
                'per_order_max_sub_item_count' => $purchaseLimit
            ];

            $addonResp = $this->shopeeApi->addAddOnDeal($shopId, $payload);
            if (isset($addonResp['error']) && $addonResp['error'] !== '') {
                throw new \Exception("Gagal membuat kerangka Promo: " . ($addonResp['message'] ?? $addonResp['error']));
            }

            $addOnDealId = $addonResp['response']['add_on_deal_id'];

            // TAHAP 2: Masukkan Produk Utama (Cth: Knalpot)
            $mainResp = $this->shopeeApi->addAddOnDealMainItem($shopId, $addOnDealId, $mainItems);
            if (isset($mainResp['error']) && $mainResp['error'] !== '') {
                throw new \Exception("Gagal memasukkan Produk Utama: " . ($mainResp['message'] ?? $mainResp['error']));
            }

            // TAHAP 3: Masukkan Produk Tambahan beserta Harga Diskonnya (Cth: DB Killer)
            $subPayloadList = [];
            foreach ($subItems as $sub) {
                $subPayloadList[] = [
                    'item_id'      => (int)$sub['id'],
                    'status'       => 1,
                    'add_on_price' => (float)$sub['addon_price'],
                    'add_on_limit' => (int)$sub['addon_limit'] // Batas beli per user untuk item ini
                ];
            }

            $subResp = $this->shopeeApi->addAddOnDealSubItem($shopId, $addOnDealId, $subPayloadList);
            if (isset($subResp['error']) && $subResp['error'] !== '') {
                throw new \Exception("Gagal memasukkan Produk Tambahan: " . ($subResp['message'] ?? $subResp['error']));
            }

            return redirect()->back()->with('success', 'Paket Diskon (Add-on) berhasil diterbitkan! Pembeli akan mendapatkan penawaran spesial saat checkout.');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}