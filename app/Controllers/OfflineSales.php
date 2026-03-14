<?php

namespace App\Controllers;
use App\Controllers\Shopee;
use App\Models\OperationalCashModel;

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

        $products = $this->db->table('warehouse_inventory')
                             ->like('sku', 'PRD-', 'after')
                             ->where('physical_stock >', 0)
                             ->orderBy('item_name', 'ASC')
                             ->get()->getResultArray();

        $data = [
            'title'    => 'Mesin Kasir Offline (POS)',
            'products' => $products
        ];

        return view('sales/offline_pos', $data);
    }

    // --- 2. HALAMAN RIWAYAT TRANSAKSI KASIR ---
    public function history()
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/portal');

        // Ambil data penjualan dari yang terbaru
        $sales = $this->db->table('offline_sales')
                          ->orderBy('created_at', 'DESC')
                          ->limit(100) // Batasi 100 terakhir agar cepat
                          ->get()->getResultArray();

        $data = [
            'title' => 'Riwayat Transaksi POS',
            'sales' => $sales
        ];

        return view('sales/offline_history', $data);
    }

    // --- API: AMBIL DETAIL STRUK TRANSAKSI ---
    public function get_detail($invoice_no)
    {
        if (!$this->request->isAJAX()) return;
        $items = $this->db->table('offline_sale_items')->where('invoice_no', $invoice_no)->get()->getResultArray();
        return $this->response->setJSON($items);
    }

    // --- 3. PROSES CHECKOUT (AJAX) ---
    public function process_offline()
    {
        if (!$this->request->isAJAX()) return $this->response->setStatusCode(403)->setJSON(['success' => false, 'message' => 'Akses Ditolak.']);

        $customerName = $this->request->getPost('customer_name') ?: 'Pelanggan Umum';
        $paymentMethod= $this->request->getPost('payment_method');
        
        // Ambil uang diterima dan kembalian dari frontend (hanya untuk log jika ingin dikembangkan, namun yang disimpan tetap total_amount)
        $amountPaid   = str_replace(['Rp', '.', ' '], '', $this->request->getPost('amount_paid') ?? 0);
        $changeAmount = $this->request->getPost('change_amount') ?? 0;
        
        $cart         = json_decode($this->request->getPost('cart'), true);
        $pic_name     = session()->get('name') ?? 'Admin Kasir';

        if (empty($cart)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Keranjang kosong!']);
        }

        try {
            $this->db->transException(true)->transStart();

            $datePrefix = date('Ymd');
            $lastInvoice = $this->db->table('offline_sales')->like('invoice_no', "INV-$datePrefix", 'after')->orderBy('id', 'DESC')->get()->getRowArray();
            $seq = $lastInvoice ? intval(substr($lastInvoice['invoice_no'], -4)) + 1 : 1;
            $invoiceNo = "INV-" . $datePrefix . "-" . str_pad($seq, 4, '0', STR_PAD_LEFT);

            $totalAmount = 0;
            $shopeeSyncLog = [];

            foreach ($cart as $item) {
                $sku = $item['sku'];
                $qty = (int)$item['qty'];
                $inputPrice = (float)$item['price']; 

                $stockDb = $this->db->table('warehouse_inventory')->where('sku', $sku)->get()->getRowArray();
                if (!$stockDb || $stockDb['physical_stock'] < $qty) {
                    throw new \Exception("Stok {$sku} tidak mencukupi!");
                }

                $subtotal = $qty * $inputPrice;
                $totalAmount += $subtotal;
                $newTotalStock = $stockDb['physical_stock'] - $qty;

                $this->db->table('warehouse_inventory')->where('sku', $sku)->update(['physical_stock' => $newTotalStock]);

                $this->db->table('offline_sale_items')->insert([
                    'invoice_no' => $invoiceNo,
                    'sku'        => $sku,
                    'item_name'  => $item['name'],
                    'qty'        => $qty,
                    'price'      => $inputPrice,
                    'subtotal'   => $subtotal
                ]);

                $shopeeSyncLog[] = ['sku' => $sku, 'new_stock' => $newTotalStock];
            }

            // Simpan Header Penjualan
            $this->db->table('offline_sales')->insert([
                'invoice_no'     => $invoiceNo,
                'customer_name'  => $customerName,
                'total_amount'   => $totalAmount,
                'payment_method' => $paymentMethod,
                'cashier_name'   => $pic_name
            ]);

            // PENCATATAN KAS
            $cashModel = new OperationalCashModel();
            $lastTrx = $cashModel->like('transaction_code', "TRX-$datePrefix-", 'after')->orderBy('id', 'DESC')->first();
            $trxSeq = $lastTrx ? str_pad((int) substr($lastTrx['transaction_code'], -3) + 1, 3, '0', STR_PAD_LEFT) : '001';
            
            $cashModel->insert([
                'transaction_code' => "TRX-$datePrefix-$trxSeq",
                'transaction_date' => date('Y-m-d'),
                'type'             => 'Cash In',
                'metode'           => (strpos(strtolower($paymentMethod), 'cash') !== false || strpos(strtolower($paymentMethod), 'tunai') !== false) ? 'Cash' : 'ATM',
                'category'         => 'Penjualan Offline',
                'amount'           => $totalAmount,
                'description'      => "Pendapatan POS: $invoiceNo",
                'pic_name'         => $pic_name
            ]);

            // AUTO JURNAL
            $accCode = (strpos(strtolower($paymentMethod), 'cash') !== false || strpos(strtolower($paymentMethod), 'tunai') !== false) ? '1-1000' : '1-2000';
            $assetAcc = $this->db->table('chart_of_accounts')->where('account_code', $accCode)->get()->getRowArray();
            $lawanAcc = $this->db->table('chart_of_accounts')->where('account_code', '4-1000')->get()->getRowArray(); 

            if($assetAcc && $lawanAcc) {
                $this->db->table('journals')->insert([
                    'journal_number'   => 'JRN-POS-'.time(),
                    'transaction_date' => date('Y-m-d'),
                    'description'      => "Penjualan Kasir: $invoiceNo",
                    'total_amount'     => $totalAmount,
                    'created_by'       => $pic_name
                ]);
                $jrnId = $this->db->insertID();

                $this->db->table('journal_items')->insert(['journal_id' => $jrnId, 'account_id' => $assetAcc['id'], 'debit' => $totalAmount, 'credit' => 0]);
                $this->db->table('journal_items')->insert(['journal_id' => $jrnId, 'account_id' => $lawanAcc['id'], 'debit' => 0, 'credit' => $totalAmount]);
                $this->db->query("UPDATE chart_of_accounts SET balance = balance + ? WHERE id = ?", [$totalAmount, $assetAcc['id']]);
                $this->db->query("UPDATE chart_of_accounts SET balance = balance + ? WHERE id = ?", [$totalAmount, $lawanAcc['id']]);
            }

            $this->db->transComplete();

            // SINKRONISASI SHOPEE
            $shopeeController = new Shopee();
            $syncCount = 0;
            foreach ($shopeeSyncLog as $sync) {
                $synced = $shopeeController->push_stock_to_shopee($sync['sku'], $sync['new_stock']);
                if ($synced > 0) $syncCount++;
            }

            return $this->response->setJSON([
                'success' => true, 
                'change_amount' => $changeAmount,
                'message' => "Pembayaran Sukses! (Stok lokal, Buku Kas, & Jurnal telah diperbarui. $syncCount Shopee disinkronkan)."
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}