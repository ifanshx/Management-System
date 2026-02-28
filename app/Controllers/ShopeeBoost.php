<?php

namespace App\Controllers;
use App\Libraries\ShopeeApi;

class ShopeeBoost extends BaseController
{
    private $db;
    private $shopeeApi;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->shopeeApi = new ShopeeApi();
    }

    // --- HALAMAN PUSAT KENDALI BOOST ---
    public function index($shopId)
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/portal');

        $shop = $this->db->table('shopee_integrations')->where('shop_id', $shopId)->get()->getRowArray();
        if (!$shop) return redirect()->to('/shopee')->with('error', 'Toko tidak ditemukan.');

        try {
            $boostResp = $this->shopeeApi->getBoostedList($shopId);
            $boostedItems = $boostResp['response']['item_list'] ?? [];

            $allProducts = $this->db->table('shopee_products')
                                    ->where('shop_id', $shopId)
                                    ->where('status', 'NORMAL')
                                    ->orderBy('item_name', 'ASC')
                                    ->get()->getResultArray();

            $data = [
                'title'        => 'Pusat Trafik & Naikkan Produk',
                'shop'         => $shop,
                'boostedItems' => $boostedItems,
                'allProducts'  => $allProducts
            ];

            return view('shopee/boost_center', $data);

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal memuat data Boost: ' . $e->getMessage());
        }
    }

    // --- PROSES SUBMIT BOOST KE API ---
    public function push_boost($shopId)
    {
        $itemIds = $this->request->getPost('item_ids');

        if (empty($itemIds)) {
            return redirect()->back()->with('error', 'Pilih minimal 1 produk untuk dinaikkan.');
        }

        if (count($itemIds) > 5) {
            return redirect()->back()->with('error', 'Maksimal hanya 5 produk yang bisa dinaikkan secara bersamaan.');
        }

        try {
            $resp = $this->shopeeApi->boostItem($itemIds, $shopId);

            if (isset($resp['error']) && $resp['error'] !== '') {
                if (isset($resp['response']['failure_list']) && !empty($resp['response']['failure_list'])) {
                    $failReason = $resp['response']['failure_list'][0]['failed_reason'];
                    throw new \Exception("Gagal menaikkan produk: " . $failReason);
                }
                throw new \Exception("Gagal menghubungi API Shopee: " . ($resp['message'] ?? $resp['error']));
            }

            return redirect()->back()->with('success', '🚀 Luar biasa! ' . count($itemIds) . ' Produk berhasil dinaikkan ke puncak pencarian Shopee.');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}