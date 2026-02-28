<?php

namespace App\Controllers;
use App\Libraries\ShopeeApi;

class ShopeeVoucher extends BaseController
{
    private $db;
    private $shopeeApi;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->shopeeApi = new ShopeeApi();
    }

    // --- HALAMAN PUSAT VOUCHER ---
    public function index($shopId)
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/portal');

        $shop = $this->db->table('shopee_integrations')->where('shop_id', $shopId)->get()->getRowArray();
        if (!$shop) return redirect()->to('/shopee')->with('error', 'Toko tidak ditemukan.');

        try {
            // Ambil daftar voucher yang sedang berjalan (Aktif)
            $ongoingResp = $this->shopeeApi->getVoucherList($shopId, 'ongoing');
            $ongoingVouchers = $ongoingResp['response']['voucher_list'] ?? [];

            // Ambil daftar voucher yang akan datang (Terjadwal)
            $upcomingResp = $this->shopeeApi->getVoucherList($shopId, 'upcoming');
            $upcomingVouchers = $upcomingResp['response']['voucher_list'] ?? [];

            $data = [
                'title'            => 'Mesin Voucher Toko',
                'shop'             => $shop,
                'ongoingVouchers'  => $ongoingVouchers,
                'upcomingVouchers' => $upcomingVouchers
            ];

            return view('shopee/voucher_center', $data);

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal memuat data Voucher: ' . $e->getMessage());
        }
    }

    // --- PROSES PEMBUATAN VOUCHER KE SHOPEE ---
    public function create_voucher($shopId)
    {
        try {
            // Validasi Input Tanggal
            $startTime = strtotime($this->request->getPost('start_time'));
            $endTime   = strtotime($this->request->getPost('end_time'));

            if ($startTime < time()) {
                throw new \Exception("Waktu mulai voucher harus lebih besar dari waktu saat ini.");
            }
            if ($endTime <= $startTime) {
                throw new \Exception("Waktu berakhir harus lebih lama dari waktu mulai.");
            }

            // Aturan Shopee: Awalan kode voucher wajib berupa 4 huruf pertama dari nama toko/username yang diizinkan,
            // Namun untuk API, Shopee menerima kombinasi alfanumerik.
            $voucherCode = strtoupper($this->request->getPost('voucher_code'));

            // Format Payload sesuai dokumentasi Shopee V2
            $payload = [
                'voucher_name'     => $this->request->getPost('voucher_name'),
                'voucher_code'     => $voucherCode,
                'start_time'       => $startTime,
                'end_time'         => $endTime,
                'target_type'      => 1, // 1 = Voucher Berlaku untuk Semua Produk di Toko
                'discount_type'    => 1, // 1 = Potongan Nominal Fix (Bukan Persen)
                'min_basket_price' => (float)$this->request->getPost('min_basket_price'),
                'usage_quantity'   => (int)$this->request->getPost('usage_quantity'),
                'display_setting_type' => 1 // 1 = Tampilkan di semua halaman (Beranda Toko, Keranjang, dll)
            ];

            // Masukkan nominal potongan
            $payload['fix_amt_discount_info'] = [
                'discount_amount' => (float)$this->request->getPost('discount_amount')
            ];

            // Tembak ke API
            $resp = $this->shopeeApi->addVoucher($shopId, $payload);

            if (isset($resp['error']) && $resp['error'] !== '') {
                throw new \Exception("Gagal merilis Voucher: " . ($resp['message'] ?? $resp['error']));
            }

            return redirect()->back()->with('success', "Hebat! Voucher <b>{$voucherCode}</b> berhasil diterbitkan dan akan tampil di Shopee.");

        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    // --- HENTIKAN VOUCHER AKTIF ---
    public function end_voucher($shopId, $voucherId)
    {
        try {
            $resp = $this->shopeeApi->endVoucher($shopId, $voucherId);
            
            if (isset($resp['error']) && $resp['error'] !== '') {
                throw new \Exception($resp['message'] ?? $resp['error']);
            }
            
            return redirect()->back()->with('success', 'Voucher berhasil dihentikan (Nonaktif).');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghentikan voucher: ' . $e->getMessage());
        }
    }
}