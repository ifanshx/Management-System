<?php

namespace App\Controllers;
use App\Libraries\ShopeeApi;

class ShopeeVariation extends BaseController
{
    private $db;
    private $shopeeApi;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->shopeeApi = new ShopeeApi();
    }

    // --- HALAMAN VARIATION BUILDER ---
    public function build($shopId, $itemId)
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/portal');

        $shop = $this->db->table('shopee_integrations')->where('shop_id', $shopId)->get()->getRowArray();
        if (!$shop) return redirect()->back()->with('error', 'Toko tidak ditemukan.');

        // Ambil info produk lokal
        $product = $this->db->table('shopee_products')->where('item_id', $itemId)->get()->getRowArray();
        if (!$product) return redirect()->back()->with('error', 'Produk tidak ditemukan di database.');

        $data = [
            'title'   => 'Builder Varian Produk',
            'shop'    => $shop,
            'product' => $product
        ];

        return view('shopee/variation_builder', $data);
    }

    // --- PROSES SIMPAN VARIAN KE SHOPEE ---
    public function save($shopId, $itemId)
    {
        try {
            // Menerima Payload JSON utuh yang dirakit oleh Javascript di View
            $payloadJson = $this->request->getPost('variation_payload');
            $payload = json_decode($payloadJson, true);

            if (!$payload || !isset($payload['tier_variation']) || !isset($payload['model'])) {
                throw new \Exception("Data varian tidak valid atau kosong.");
            }

            $resp = $this->shopeeApi->initTierVariation(
                $shopId, 
                $itemId, 
                $payload['tier_variation'], 
                $payload['model']
            );

            if (isset($resp['error']) && $resp['error'] !== '') {
                throw new \Exception("Gagal mengatur varian di Shopee: " . ($resp['message'] ?? $resp['error']));
            }

            // Update status lokal bahwa produk ini sudah memiliki varian
            $this->db->table('shopee_products')->where('item_id', $itemId)->update(['has_variation' => 1]);

            return redirect()->to('/shopee/products/'.$shopId)->with('success', 'Luar Biasa! Varian produk berhasil di-publish ke Shopee.');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}