<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use App\Controllers\Shopee; // Menggunakan controller Shopee yang sudah ada

class ShopeeWebhook extends ResourceController
{
    public function receive()
    {
        // 1. Tangkap Data Mentah dari Shopee
        $payload = $this->request->getBody();
        $authHeader = $this->request->getHeaderLine('Authorization');
        
        // 2. VERIFIKASI KEAMANAN (MUTLAK DIPERLUKAN)
        // Rumus Shopee Webhook V2: HMAC-SHA256(Webhook_URL | Payload_Body, Partner_Key)
        $partnerKey = getenv('SHOPEE_PARTNER_KEY');
        $webhookUrl = current_url(); // Mendapatkan full URL endpoint ini
        
        $baseString = $webhookUrl . '|' . $payload;
        $calculatedSign = hash_hmac('sha256', $baseString, $partnerKey);

        // Jika tanda tangan tidak cocok, tolak mentah-mentah (Anti-Hacking)
        if ($authHeader !== $calculatedSign) {
            log_message('critical', 'Shopee Webhook Security Breach Attempt!');
            return $this->respond(['status' => 'error', 'message' => 'Invalid Signature'], 403);
        }

        // 3. PARSING DATA
        $data = json_decode($payload, true);
        
        if (!$data) {
            return $this->respond(['status' => 'error', 'message' => 'Invalid JSON'], 400);
        }

        $shopId = $data['shop_id'] ?? 0;
        $code   = $data['code'] ?? 0; // Kode Event dari Shopee

        // 4. ROUTING LOGIKA BISNIS BERDASARKAN EVENT
        try {
            switch ($code) {
                case 3: // Event Code 3 = ORDER STATUS UPDATE
                    $orderSn = $data['data']['ordersn'] ?? '';
                    $status  = $data['data']['status'] ?? '';

                    // Jika pesanan baru masuk dan siap dikemas
                    if ($status === 'READY_TO_SHIP') {
                        // Secara otomatis jalankan fungsi Tarik Pesanan yang sudah kita buat!
                        $shopeeController = new Shopee();
                        $shopeeController->sync_orders($shopId);
                        
                        log_message('info', "WEBHOOK AUTO-SYNC: Pesanan baru $orderSn otomatis masuk ke Gudang.");
                    }
                    break;

                case 4: // Event Code 4 = TRACKING NUMBER READY
                    // Anda bisa memicu pencetakan otomatis di sini
                    break;
                    
                case 10: // Event Code 10 = SHOP DEAUTHORIZED
                    // Jika Anda atau admin membatalkan integrasi dari Seller Centre
                    $db = \Config\Database::connect();
                    $db->table('shopee_integrations')->where('shop_id', $shopId)->update(['status' => 'Disconnected']);
                    break;
            }

            // Wajib merespon 200 OK agar Shopee tidak mengirim ulang data (Retry)
            return $this->respond(['status' => 'success'], 200);

        } catch (\Exception $e) {
            log_message('error', 'Webhook Error: ' . $e->getMessage());
            return $this->respond(['status' => 'error'], 500);
        }
    }
}