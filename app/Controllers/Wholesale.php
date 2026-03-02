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

            // Cek Stok Gudang
            $stock = $this->db->table('warehouse_inventory')->where('sku', $fgSku)->get()->getRowArray();
            if(!$stock || $stock['physical_stock'] < $qty) throw new \Exception("Stok {$fgSku} tidak mencukupi untuk pesanan grosir ini!");

            // Potong Stok
            $this->db->query("UPDATE warehouse_inventory SET physical_stock = physical_stock - ? WHERE sku = ?", [$qty, $fgSku]);

            // Generate SO Number
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

            // Jika ada DP, masukkan ke Jurnal Akuntansi secara otomatis
            if ($dpAmount > 0) {
                $this->db->table('journals')->insert([
                    'journal_number'   => 'JRN-B2B-'.time(),
                    'transaction_date' => date('Y-m-d'),
                    'description'      => "Pembayaran DP/Lunas SO $soNumber",
                    'total_amount'     => $dpAmount,
                    'created_by'       => 'Sistem B2B'
                ]);
            }

            $this->db->transComplete();
            if ($this->db->transStatus() === false) throw new \Exception("Gagal membuat Sales Order.");

            return redirect()->back()->with('success', "Sales Order B2B berhasil diterbitkan dan stok telah dipotong!");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function pay_installment($id)
    {
        // Fungsi pelunasan piutang
        $amount = (float)$this->request->getPost('amount');
        $this->db->query("UPDATE b2b_sales_orders SET paid_amount = paid_amount + ?, status = CASE WHEN paid_amount >= total_amount THEN 'PAID' ELSE 'PARTIAL' END WHERE id = ?", [$amount, $id]);
        return redirect()->back()->with('success', 'Pembayaran piutang berhasil dicatat.');
    }
}