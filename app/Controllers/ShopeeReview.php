<?php

namespace App\Controllers;
use App\Libraries\ShopeeApi;

class ShopeeReview extends BaseController
{
    private $db;
    private $shopeeApi;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->shopeeApi = new ShopeeApi();
    }

    // --- HALAMAN PUSAT ULASAN ---
    public function index($shopId)
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/portal');

        $shop = $this->db->table('shopee_integrations')->where('shop_id', $shopId)->get()->getRowArray();
        if (!$shop) return redirect()->to('/shopee')->with('error', 'Toko tidak ditemukan.');

        $reviews = [];
        try {
            $resp = $this->shopeeApi->getComment($shopId, 50); // Tarik 50 ulasan terakhir
            
            if (isset($resp['response']['item_comment_list'])) {
                $reviews = $resp['response']['item_comment_list'];
            }
        } catch (\Exception $e) {
            session()->setFlashdata('error', 'Gagal menarik ulasan: ' . $e->getMessage());
        }

        $data = [
            'title'   => 'Pusat Manajemen Ulasan (Review)',
            'shop'    => $shop,
            'reviews' => $reviews
        ];

        return view('shopee/review_center', $data);
    }

    // --- PROSES BALAS ULASAN VIA AJAX ---
    public function reply($shopId)
    {
        if (!$this->request->isAJAX()) return $this->response->setStatusCode(403);

        $commentId = $this->request->getPost('comment_id');
        $replyText = $this->request->getPost('reply_text');

        if (empty($replyText)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Teks balasan tidak boleh kosong.']);
        }

        try {
            $resp = $this->shopeeApi->replyComment($shopId, $commentId, $replyText);

            if (isset($resp['error']) && $resp['error'] !== '') {
                // Shopee mengembalikan detail error dalam result_list
                $failMsg = $resp['response']['result_list'][0]['fail_reason'] ?? ($resp['message'] ?? 'Gagal membalas ulasan.');
                throw new \Exception($failMsg);
            }

            return $this->response->setJSON(['success' => true, 'message' => 'Balasan berhasil dikirim!']);

        } catch (\Exception $e) {
            return $this->response->setJSON(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}