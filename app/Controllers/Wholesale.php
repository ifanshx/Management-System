<?php

namespace App\Controllers;

class Wholesale extends BaseController
{
    private $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    public function index()
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/portal');

        $salesOrders = $this->db->table('b2b_sales_orders')
            ->select('b2b_sales_orders.*, b2b_customers.company_name')
            ->join('b2b_customers', 'b2b_customers.id = b2b_sales_orders.customer_id')
            ->orderBy('b2b_sales_orders.id', 'DESC')
            ->get()->getResultArray();

        $customers = $this->db->table('b2b_customers')->get()->getResultArray();
        $products  = $this->db->table('warehouse_inventory')->like('sku', 'FG-', 'after')->get()->getResultArray();

        $data = [
            'title'       => 'B2B Wholesale & Piutang',
            'salesOrders' => $salesOrders,
            'customers'   => $customers,
            'products'    => $products
        ];

        return view('wholesale/index', $data);
    }

    public function store_so()
    {
        try {
            $this->db->transStart();

            $customerId = $this->request->getPost('customer_id');
            $fgSku      = $this->request->getPost('fg_sku');
            $qty        = (int)$this->request->getPost('qty');
            $unitPrice  = (float)$this->request->getPost('unit_price');
            $dpAmount   = (float)$this->request->getPost('dp_amount');
            $dueDate    = $this->request->getPost('due_date');

            $totalAmount = $qty * $unitPrice;
            $status = ($dpAmount >= $totalAmount) ? 'PAID' : ($dpAmount > 0 ? 'PARTIAL' : 'PENDING');

            // 1. Cek & Potong Stok Gudang
            $stock = $this->db->table('warehouse_inventory')->where('sku', $fgSku)->get()->getRowArray();
            if(!$stock || $stock['physical_stock'] < $qty) throw new \Exception("Stok {$fgSku} tidak mencukupi!");
            $this->db->query("UPDATE warehouse_inventory SET physical_stock = physical_stock - ? WHERE sku = ?", [$qty, $fgSku]);

            // 2. Generate SO Number & Insert
            $soNumber = "SO-" . date('Ymd') . "-" . rand(100,999);
            $this->db->table('b2b_sales_orders')->insert([
                'so_number'    => $soNumber,
                'customer_id'  => $customerId,
                'order_date'   => date('Y-m-d'),
                'due_date'     => $dueDate,
                'total_amount' => $totalAmount,
                'paid_amount'  => $dpAmount,
                'status'       => $status
            ]);

            // 3. PERBAIKAN INTEGRASI AKUNTANSI (Jika ada DP)
            if ($dpAmount > 0) {
                $this->db->table('journals')->insert([
                    'journal_number'   => 'JRN-B2B-'.time(),
                    'transaction_date' => date('Y-m-d'),
                    'description'      => "Uang Muka (DP) SO B2B: $soNumber",
                    'total_amount'     => $dpAmount,
                    'created_by'       => 'Sistem Auto-B2B'
                ]);
                $journalId = $this->db->insertID();

                // Ambil ID Akun: 1-1000 (Kas) dan 4-1000 (Pendapatan Jualan)
                $kas = $this->db->table('chart_of_accounts')->where('account_code', '1-1000')->get()->getRowArray();
                $rev = $this->db->table('chart_of_accounts')->where('account_code', '4-1000')->get()->getRowArray();

                if($kas && $rev) {
                    // Masukkan ke Detail Jurnal
                    $this->db->table('journal_items')->insert(['journal_id' => $journalId, 'account_id' => $kas['id'], 'debit' => $dpAmount, 'credit' => 0]);
                    $this->db->table('journal_items')->insert(['journal_id' => $journalId, 'account_id' => $rev['id'], 'debit' => 0, 'credit' => $dpAmount]);
                    
                    // Tambah Saldo Buku Besar
                    $this->db->query("UPDATE chart_of_accounts SET balance = balance + ? WHERE id = ?", [$dpAmount, $kas['id']]);
                    $this->db->query("UPDATE chart_of_accounts SET balance = balance + ? WHERE id = ?", [$dpAmount, $rev['id']]);
                }
            }

            $this->db->transComplete();
            if ($this->db->transStatus() === false) throw new \Exception("Gagal menerbitkan Sales Order.");

            return redirect()->back()->with('success', "Sales Order berhasil dibuat. Stok terpotong dan Jurnal otomatis dicatat!");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function pay_installment($id)
    {
        try {
            $amount = (float)$this->request->getPost('amount');
            if ($amount <= 0) throw new \Exception("Nominal pelunasan tidak valid.");

            $this->db->transStart();

            // 1. Update Status Piutang
            $this->db->query("UPDATE b2b_sales_orders SET paid_amount = paid_amount + ?, status = CASE WHEN paid_amount >= total_amount THEN 'PAID' ELSE 'PARTIAL' END WHERE id = ?", [$amount, $id]);
            
            $so = $this->db->table('b2b_sales_orders')->where('id', $id)->get()->getRowArray();

            // 2. PERBAIKAN: Catat Pelunasan ke Jurnal Akuntansi
            $this->db->table('journals')->insert([
                'journal_number'   => 'JRN-PAY-'.time(),
                'transaction_date' => date('Y-m-d'),
                'description'      => "Pelunasan Piutang SO: " . $so['so_number'],
                'total_amount'     => $amount,
                'created_by'       => 'Sistem Auto-B2B'
            ]);
            $journalId = $this->db->insertID();

            $kas = $this->db->table('chart_of_accounts')->where('account_code', '1-1000')->get()->getRowArray();
            $rev = $this->db->table('chart_of_accounts')->where('account_code', '4-1000')->get()->getRowArray();

            if($kas && $rev) {
                $this->db->table('journal_items')->insert(['journal_id' => $journalId, 'account_id' => $kas['id'], 'debit' => $amount, 'credit' => 0]);
                $this->db->table('journal_items')->insert(['journal_id' => $journalId, 'account_id' => $rev['id'], 'debit' => 0, 'credit' => $amount]);
                $this->db->query("UPDATE chart_of_accounts SET balance = balance + ? WHERE id = ?", [$amount, $kas['id']]);
                $this->db->query("UPDATE chart_of_accounts SET balance = balance + ? WHERE id = ?", [$amount, $rev['id']]);
            }

            $this->db->transComplete();
            if ($this->db->transStatus() === false) throw new \Exception("Gagal mencatat pembayaran.");

            return redirect()->back()->with('success', 'Pembayaran piutang berhasil dicatat ke dalam Lejar Akuntansi!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}