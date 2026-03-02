<?php

namespace App\Controllers;
use App\Libraries\ShopeeApi;

class ShopeeBundle extends BaseController
{
    private $db;
    private $shopeeApi;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->shopeeApi = new ShopeeApi();
    }

    public function index($shopId)
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/portal');

        $shop = $this->db->table('shopee_integrations')->where('shop_id', $shopId)->get()->getRowArray();
        if (!$shop) return redirect()->to('/shopee')->with('error', 'Toko tidak ditemukan.');

        // Ambil daftar produk untuk dimasukkan ke dalam Bundle
        $products = $this->db->table('shopee_products')
                             ->where('shop_id', $shopId)
                             ->where('status', 'NORMAL')
                             ->get()->getResultArray();

        $data = [
            'title'    => 'Mesin Kombo Hemat (Bundle Deals)',
            'shop'     => $shop,
            'products' => $products
        ];

        return view('shopee/bundle_center', $data);
    }

    public function create_bundle($shopId)
    {
        try {
            $ruleType = (int)$this->request->getPost('rule_type');
            $discountValue = (float)$this->request->getPost('discount_value');
            $itemCount = (int)$this->request->getPost('item_count');
            $itemIds = $this->request->getPost('item_ids'); // Array produk yang dipilih

            if (empty($itemIds) || count($itemIds) < 2) {
                throw new \Exception("Pilih minimal 2 produk untuk membuat Kombo Hemat.");
            }

            $startTime = strtotime($this->request->getPost('start_time'));
            $endTime   = strtotime($this->request->getPost('end_time'));

            // TAHAP 1: Buat Kerangka Bundle
            $payload = [
                'title'                  => $this->request->getPost('bundle_title'),
                'start_time'             => $startTime,
                'end_time'               => $endTime,
                'bundle_deal_item_count' => $itemCount,
                'bundle_deal_rule_type'  => $ruleType, // 1: Harga Fix, 2: Diskon Persen, 3: Diskon Nominal
                'purchase_limit'         => (int)$this->request->getPost('purchase_limit')
            ];

            if ($ruleType === 1) {
                $payload['fix_price'] = $discountValue;
            } else {
                $payload['discount_value'] = $discountValue;
            }

            $bundleResp = $this->shopeeApi->addBundleDeal($shopId, $payload);

            if (isset($bundleResp['error']) && $bundleResp['error'] !== '') {
                throw new \Exception("Gagal membuat kerangka Bundle: " . ($bundleResp['message'] ?? $bundleResp['error']));
            }

            $bundleId = $bundleResp['response']['bundle_deal_id'];

            // TAHAP 2: Masukkan Produk ke dalam Bundle tersebut
            $itemResp = $this->shopeeApi->addBundleDealItem($shopId, $bundleId, $itemIds);

            if (isset($itemResp['error']) && $itemResp['error'] !== '') {
                throw new \Exception("Berhasil membuat kombo, tapi gagal memasukkan item: " . ($itemResp['message'] ?? $itemResp['error']));
            }

            return redirect()->back()->with('success', 'Kombo Hemat berhasil diterbitkan! Pembeli akan melihat label khusus di produk Anda.');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}