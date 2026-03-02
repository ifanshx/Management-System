<?php

namespace App\Controllers;
use App\Libraries\ShopeeApi;

class ShopeeReturn extends BaseController
{
    private $db;
    private $shopeeApi;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->shopeeApi = new ShopeeApi();
    }

    // --- HALAMAN PUSAT RETUR ---
    public function index($shopId)
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/portal');

        $shop = $this->db->table('shopee_integrations')->where('shop_id', $shopId)->get()->getRowArray();
        if (!$shop) return redirect()->to('/shopee')->with('error', 'Toko tidak ditemukan.');

        $returns = [];
        try {
            $resp = $this->shopeeApi->getReturnList($shopId);
            if (isset($resp['response']['return_list'])) {
                $returns = $resp['response']['return_list'];
            }
        } catch (\Exception $e) {
            session()->setFlashdata('error', 'Gagal menarik data retur: ' . $e->getMessage());
        }

        $data = [
            'title'   => 'Pusat Manajemen Retur & Sengketa',
            'shop'    => $shop,
            'returns' => $returns
        ];

        return view('shopee/return_center', $data);
    }

    // --- PROSES SETUJUI RETUR ---
    public function confirm($shopId, $returnSn)
    {
        try {
            $resp = $this->shopeeApi->confirmReturn($shopId, $returnSn);
            if (isset($resp['error']) && $resp['error'] !== '') {
                throw new \Exception($resp['message'] ?? $resp['error']);
            }
            return redirect()->back()->with('success', "Retur disetujui. Saldo akan dikembalikan ke pembeli.");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menyetujui retur: ' . $e->getMessage());
        }
    }

    // --- PROSES AJUKAN SENGKETA (DISPUTE) VIA AJAX ---
    public function dispute($shopId)
    {
        if (!$this->request->isAJAX()) return $this->response->setStatusCode(403);

        $returnSn = $this->request->getPost('return_sn');
        $reasonText = $this->request->getPost('dispute_text');
        
        // Default kode alasan sengketa Shopee: "OTHERS" (Alasan lain-lain yang dideskripsikan di teks)
        $reasonCode = 'OTHERS'; 

        if (empty($reasonText)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Alasan sengketa wajib diisi.']);
        }

        try {
            $resp = $this->shopeeApi->disputeReturn($shopId, $returnSn, $reasonCode, $reasonText);

            if (isset($resp['error']) && $resp['error'] !== '') {
                throw new \Exception($resp['message'] ?? $resp['error']);
            }

            return $this->response->setJSON(['success' => true, 'message' => 'Sengketa berhasil diajukan ke Hakim Shopee!']);

        } catch (\Exception $e) {
            return $this->response->setJSON(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}