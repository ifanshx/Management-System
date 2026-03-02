<?php

namespace App\Controllers;
use App\Libraries\ShopeeApi;

class ShopeeMassUpdate extends BaseController
{
    private $db;
    private $shopeeApi;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->shopeeApi = new ShopeeApi();
    }

    // --- HALAMAN SPREADSHEET UPDATE HARGA ---
    public function price($shopId)
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/portal');

        $shop = $this->db->table('shopee_integrations')->where('shop_id', $shopId)->get()->getRowArray();
        if (!$shop) return redirect()->to('/shopee')->with('error', 'Toko tidak ditemukan.');

        // Tarik semua produk yang berstatus NORMAL untuk diubah harganya
        $products = $this->db->table('shopee_products')
                             ->where('shop_id', $shopId)
                             ->where('status', 'NORMAL')
                             ->orderBy('item_name', 'ASC')
                             ->get()->getResultArray();

        $data = [
            'title'    => 'Mesin Update Harga Massal',
            'shop'     => $shop,
            'products' => $products
        ];

        return view('shopee/mass_price', $data);
    }

    // --- PROSES SIMPAN HARGA KE SHOPEE & DATABASE LOKAL ---
    public function update_price_action($shopId)
    {
        // Menerima array harga baru: name="new_prices[item_id]"
        $newPrices = $this->request->getPost('new_prices');

        if (empty($newPrices)) {
            return redirect()->back()->with('error', 'Tidak ada data harga yang dikirim.');
        }

        $successCount = 0;
        $errorList = [];

        try {
            $this->db->transStart();

            // Looping dan tembak API Shopee satu per satu untuk setiap barang yang harganya berubah
            foreach ($newPrices as $itemId => $price) {
                $newPriceFloat = (float)$price;

                // Tembak ke API Shopee
                $resp = $this->shopeeApi->updatePrice($shopId, $itemId, $newPriceFloat);

                if (isset($resp['error']) && $resp['error'] !== '') {
                    $errorList[] = "Item ID $itemId: " . ($resp['message'] ?? $resp['error']);
                    continue; // Lanjut ke produk berikutnya meski error
                }

                // Jika sukses di Shopee, update database ERP lokal
                $this->db->table('shopee_products')
                         ->where('item_id', $itemId)
                         ->update(['price' => $newPriceFloat]);
                
                $successCount++;
            }

            $this->db->transComplete();

            if (!empty($errorList)) {
                $errStr = implode("<br>", $errorList);
                return redirect()->back()->with('error', "Beberapa produk gagal diupdate:<br>$errStr");
            }

            return redirect()->back()->with('success', "<i class='ph ph-check-circle'></i> Luar biasa! Harga <b>$successCount produk</b> berhasil diubah di Shopee secara massal.");

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan sistem: ' . $e->getMessage());
        }
    }
}