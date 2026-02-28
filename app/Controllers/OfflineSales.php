<?php

namespace App\Controllers;
use App\Controllers\Shopee; // Wajib dipanggil untuk auto-sync stok

class OfflineSales extends BaseController
{
    private $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    // --- 1. HALAMAN POINT OF SALE (KASIR PABRIK) ---
    public function index()
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/portal');

        // Hanya tampilkan Barang Jadi (Finished Goods) yang stoknya > 0
        $products = $this->db->table('warehouse_inventory')
                             ->where('physical_stock >', 0)
                             ->orderBy('item_name', 'ASC')
                             ->get()->getResultArray();

        $data = [
            'title'    => 'Mesin Kasir Offline (POS)',
            'products' => $products
        ];

        return view('sales/offline_pos', $data);
    }

    // --- 2. PROSES CHECKOUT & OMNICHANNEL SYNC ---
    public function process_checkout()
    {
        if (!$this->request->isAJAX()) return $this->response->setStatusCode(403);

        $customerName = $this->request->getPost('customer_name') ?: 'Pelanggan Umum';
        $paymentMethod= $this->request->getPost('payment_method');
        $cart         = json_decode($this->request->getPost('cart'), true);

        if (empty($cart)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Keranjang kosong!']);
        }

        try {
            $this->db->transStart();

            // Generate Nomor Invoice: INV-20260228-0001
            $datePrefix = date('Ymd');
            $lastInvoice = $this->db->table('offline_sales')->like('invoice_no', "INV-$datePrefix", 'after')->orderBy('id', 'DESC')->get()->getRowArray();
            $seq = 1;
            if ($lastInvoice) {
                $parts = explode('-', $lastInvoice['invoice_no']);
                $seq = intval(end($parts)) + 1;
            }
            $invoiceNo = "INV-" . $datePrefix . "-" . str_pad($seq, 4, '0', STR_PAD_LEFT);

            $totalAmount = 0;
            $shopeeSyncLog = [];

            // Proses setiap barang di keranjang
            foreach ($cart as $item) {
                $sku = $item['sku'];
                $qty = (int)$item['qty'];
                $price = (float)$item['price'];
                $subtotal = $qty * $price;
                $totalAmount += $subtotal;

                // A. Validasi & Kurangi Stok Gudang Fisik
                $currentStockQuery = $this->db->table('warehouse_inventory')->where('sku', $sku)->get()->getRowArray();
                if (!$currentStockQuery || $currentStockQuery['physical_stock'] < $qty) {
                    throw new \Exception("Stok {$sku} tidak mencukupi di gudang!");
                }

                $newTotalStock = $currentStockQuery['physical_stock'] - $qty;

                // Update tabel Gudang Lokal
                $this->db->table('warehouse_inventory')->where('sku', $sku)->update(['physical_stock' => $newTotalStock]);

                // B. Simpan ke Detail Penjualan
                $this->db->table('offline_sale_items')->insert([
                    'invoice_no' => $invoiceNo,
                    'sku'        => $sku,
                    'item_name'  => $item['name'],
                    'qty'        => $qty,
                    'price'      => $price,
                    'subtotal'   => $subtotal
                ]);

                // Siapkan data untuk trigger ke Shopee setelah DB lokal aman
                $shopeeSyncLog[] = ['sku' => $sku, 'new_stock' => $newTotalStock];
            }

            // Simpan Header Penjualan
            $this->db->table('offline_sales')->insert([
                'invoice_no'     => $invoiceNo,
                'customer_name'  => $customerName,
                'total_amount'   => $totalAmount,
                'payment_method' => $paymentMethod,
                'cashier_name'   => session()->get('name') ?? 'Admin Kasir'
            ]);

            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                throw new \Exception("Gagal mengunci transaksi ke database.");
            }

            // C. THE OMNICHANNEL MAGIC (UPDATE KE SHOPEE SECARA BACKGROUND)
            $shopeeController = new Shopee();
            $syncCount = 0;
            foreach ($shopeeSyncLog as $sync) {
                // Tembak API Shopee untuk setiap barang yang berkurang!
                $synced = $shopeeController->push_stock_to_shopee($sync['sku'], $sync['new_stock']);
                if ($synced > 0) $syncCount++;
            }

            return $this->response->setJSON([
                'success' => true, 
                'message' => "Pembayaran Berhasil! Invoice: $invoiceNo. (Sync ke $syncCount etalase Shopee sukses)."
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}