<?php

namespace App\Controllers;
use App\Libraries\ShopeeApi;

class CustomerService extends BaseController
{
    private $shopeeApi;
    private $db;

    public function __construct()
    {
        $this->shopeeApi = new ShopeeApi();
        $this->db = \Config\Database::connect();
    }

    // --- 1. HALAMAN DAFTAR OBROLAN (INBOX) ---
    public function inbox($shopId)
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/portal');

        $shop = $this->db->table('shopee_integrations')->where('shop_id', $shopId)->get()->getRowArray();
        if (!$shop) return redirect()->to('/shopee');

        try {
            // Tarik 20 chat terakhir dari Shopee
            $chatResp = $this->shopeeApi->getConversationList($shopId);
            
            if (isset($chatResp['error']) && $chatResp['error'] !== '') {
                throw new \Exception($chatResp['message']);
            }

            $conversations = $chatResp['response']['conversations'] ?? [];

            $data = [
                'title' => 'Customer Service Hub',
                'shop'  => $shop,
                'chats' => $conversations
            ];

            return view('shopee/chat_inbox', $data);

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal memuat pesan: ' . $e->getMessage());
        }
    }

    // --- 2. AJAX ENDPOINT: BALAS PESAN ---
    public function reply_chat()
    {
        if (!$this->request->isAJAX()) return $this->response->setStatusCode(403);

        $shopId = $this->request->getPost('shop_id');
        $toId   = $this->request->getPost('to_id'); // User ID Pembeli
        $text   = $this->request->getPost('message');

        if (empty($text)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Pesan tidak boleh kosong.']);
        }

        try {
            $resp = $this->shopeeApi->sendMessage($toId, $text, $shopId);

            if (isset($resp['error']) && $resp['error'] !== '') {
                throw new \Exception($resp['message']);
            }

            return $this->response->setJSON(['success' => true, 'message' => 'Pesan terkirim.']);

        } catch (\Exception $e) {
            return $this->response->setJSON(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}