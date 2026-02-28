<?php

namespace App\Controllers;
use App\Libraries\ShopeeApi;

class Marketing extends BaseController
{
    private $db;
    private $shopeeApi;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->shopeeApi = new ShopeeApi();
    }

    // --- HALAMAN PUSAT PROMOSI ---
    public function shopee_discount($shopId)
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/portal');

        $shop = $this->db->table('shopee_integrations')->where('shop_id', $shopId)->get()->getRowArray();
        if (!$shop) return redirect()->back()->with('error', 'Toko tidak ditemukan.');

        // Ambil daftar produk lokal untuk dipilih di dropdown form
        $products = $this->db->table('shopee_products')->where('shop_id', $shopId)->get()->getResultArray();

        $data = [
            'title'    => 'Pusat Promosi & Harga Coret',
            'shop'     => $shop,
            'products' => $products
        ];

        return view('marketing/shopee_discount', $data);
    }

    // --- PROSES PEMBUATAN PROMO KE SHOPEE ---
    public function create_discount($shopId)
    {
        try {
            $discountName = $this->request->getPost('discount_name');
            $itemId       = $this->request->getPost('item_id');
            $promoPrice   = $this->request->getPost('promo_price');
            
            // Konversi format tanggal HTML (Y-m-d\TH:i) ke UNIX Timestamp
            $startTime = strtotime($this->request->getPost('start_time'));
            $endTime   = strtotime($this->request->getPost('end_time'));

            // Validasi Shopee: Promo tidak boleh dimulai di masa lalu
            if ($startTime < time()) {
                throw new \Exception("Waktu mulai promo harus lebih besar dari waktu sekarang.");
            }

            // TAHAP 1: Buat Wadah Campaign Diskon
            $campaignResp = $this->shopeeApi->addDiscount($shopId, $discountName, $startTime, $endTime);
            
            if (isset($campaignResp['error']) && $campaignResp['error'] !== '') {
                throw new \Exception("Gagal buat Campaign: " . ($campaignResp['message'] ?? $campaignResp['error']));
            }

            $discountId = $campaignResp['response']['discount_id'];

            // TAHAP 2: Masukkan Produk & Harga Coretnya ke dalam Campaign tersebut
            $itemResp = $this->shopeeApi->addDiscountItem($shopId, $discountId, $itemId, 0, $promoPrice, 0);

            // Jika item gagal dimasukkan (misal harga promo > harga asli)
            if (isset($itemResp['error']) && $itemResp['error'] !== '') {
                throw new \Exception("Gagal set harga coret: " . ($itemResp['message'] ?? $itemResp['error']));
            }

            return redirect()->back()->with('success', 'Luar Biasa! Promo <b>' . esc($discountName) . '</b> berhasil dirilis (Live) di Shopee.');

        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }
}