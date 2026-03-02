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

            if ($qty <= 0 || $unitPrice <= 0) {
                throw new \Exception("Kuantiti dan Harga mesti melebihi 0.");
            }

            $totalAmount = $qty * $unitPrice;

            // PENYEMPURNAAN KESELAMATAN: DP tidak boleh melebihi Total
            if ($dpAmount > $totalAmount) {
                throw new \Exception("Ralat! Wang Pendahuluan (DP) sebesar Rp " . number_format($dpAmount,0,',','.') . " tidak boleh melebihi Total Pesanan (Rp " . number_format($totalAmount,0,',','.') . ").");
            }

            $status = ($dpAmount >= $totalAmount) ? 'PAID' : ($dpAmount > 0 ? 'PARTIAL' : 'PENDING');

            // 1. Semak & Potong Stok Gudang
            $stock = $this->db->table('warehouse_inventory')->where('sku', $fgSku)->get()->getRowArray();
            if(!$stock || $stock['physical_stock'] < $qty) throw new \Exception("Stok {$fgSku} tidak mencukupi untuk pesanan ini!");
            
            $this->db->query("UPDATE warehouse_inventory SET physical_stock = physical_stock - ? WHERE sku = ?", [$qty, $fgSku]);

            // 2. Jana Nombor SO & Masukkan Data
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

            // 3. Catatan Akuntansi Automatik
            if ($dpAmount > 0) {
                $this->db->table('journals')->insert([
                    'journal_number'   => 'JRN-B2B-'.time(),
                    'transaction_date' => date('Y-m-d'),
                    'description'      => "Wang Pendahuluan (DP) SO B2B: $soNumber",
                    'total_amount'     => $dpAmount,
                    'created_by'       => session()->get('name') ?? 'Sistem Auto-B2B'
                ]);
                $journalId = $this->db->insertID();

                $kas = $this->db->table('chart_of_accounts')->where('account_code', '1-1000')->get()->getRowArray();
                $rev = $this->db->table('chart_of_accounts')->where('account_code', '4-1000')->get()->getRowArray();

                if($kas && $rev) {
                    $this->db->table('journal_items')->insert(['journal_id' => $journalId, 'account_id' => $kas['id'], 'debit' => $dpAmount, 'credit' => 0]);
                    $this->db->table('journal_items')->insert(['journal_id' => $journalId, 'account_id' => $rev['id'], 'debit' => 0, 'credit' => $dpAmount]);
                    
                    $this->db->query("UPDATE chart_of_accounts SET balance = balance + ? WHERE id = ?", [$dpAmount, $kas['id']]);
                    $this->db->query("UPDATE chart_of_accounts SET balance = balance + ? WHERE id = ?", [$dpAmount, $rev['id']]);
                }
            }

            $this->db->transComplete();
            if ($this->db->transStatus() === false) throw new \Exception("Gagal menerbitkan Sales Order.");

            return redirect()->back()->with('success', "Pesanan Borong berjaya direkodkan. Stok telah dipotong dan Jurnal dikemas kini!");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function pay_installment($id)
    {
        try {
            $amount = (float)$this->request->getPost('amount');
            if ($amount <= 0) throw new \Exception("Nilai pembayaran tidak sah.");

            $this->db->transStart();

            $so = $this->db->table('b2b_sales_orders')->where('id', $id)->get()->getRowArray();
            if (!$so) throw new \Exception("Pesanan tidak dijumpai.");

            // PENYEMPURNAAN KESELAMATAN: Pembayaran tidak boleh melebihi baki hutang
            $sisaPiutang = $so['total_amount'] - $so['paid_amount'];
            if ($amount > $sisaPiutang) {
                throw new \Exception("DITOLAK! Nilai bayaran (Rp " . number_format($amount,0,',','.') . ") melebihi baki hutang (Rp " . number_format($sisaPiutang,0,',','.') . ").");
            }

            // 1. Kemas Kini Status Hutang
            $this->db->query("UPDATE b2b_sales_orders SET paid_amount = paid_amount + ?, status = CASE WHEN paid_amount >= total_amount THEN 'PAID' ELSE 'PARTIAL' END WHERE id = ?", [$amount, $id]);
            
            // 2. Catat Pelunasan ke Jurnal
            $this->db->table('journals')->insert([
                'journal_number'   => 'JRN-PAY-'.time(),
                'transaction_date' => date('Y-m-d'),
                'description'      => "Bayaran Ansuran SO: " . $so['so_number'],
                'total_amount'     => $amount,
                'created_by'       => session()->get('name') ?? 'Sistem Auto-B2B'
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
            if ($this->db->transStatus() === false) throw new \Exception("Gagal merekodkan pembayaran.");

            return redirect()->back()->with('success', 'Pembayaran hutang berjaya direkodkan ke dalam Lejar Akuntansi!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}