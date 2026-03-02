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

        // Tarik rincian item untuk setiap SO agar tampil di tabel / modal
        foreach ($salesOrders as &$so) {
            $items = $this->db->table('b2b_sales_order_items')
                ->select('b2b_sales_order_items.qty, warehouse_inventory.item_name, b2b_sales_order_items.fg_sku')
                ->join('warehouse_inventory', 'warehouse_inventory.sku = b2b_sales_order_items.fg_sku', 'left')
                ->where('so_id', $so['id'])
                ->get()->getResultArray();
            $so['items'] = $items; 
        }

        $customers = $this->db->table('b2b_customers')->orderBy('company_name', 'ASC')->get()->getResultArray();
        
        // PERBAIKAN: Gunakan awalan 'PRD-' untuk Barang Jadi yang siap jual
        $products  = $this->db->table('warehouse_inventory')->like('sku', 'PRD-', 'after')->get()->getResultArray();

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
            $fgSkus     = $this->request->getPost('fg_sku'); 
            $qtys       = $this->request->getPost('qty');     
            $prices     = $this->request->getPost('unit_price'); 
            $dpAmount   = (float)$this->request->getPost('dp_amount');
            $dueDate    = $this->request->getPost('due_date');

            if(empty($fgSkus) || count($fgSkus) === 0) {
                throw new \Exception("Anda harus memilih minimal 1 produk.");
            }

            $totalAmount = 0;
            $validItems = [];

            for ($i = 0; $i < count($fgSkus); $i++) {
                if(!empty($fgSkus[$i])) {
                    $sku = $fgSkus[$i];
                    $qty = (int)$qtys[$i];
                    $price = (float)$prices[$i];

                    if ($qty <= 0 || $price <= 0) continue;

                    $stock = $this->db->table('warehouse_inventory')->where('sku', $sku)->get()->getRowArray();
                    if(!$stock || $stock['physical_stock'] < $qty) {
                        throw new \Exception("Stok {$sku} tidak cukup! Sisa: " . ($stock['physical_stock'] ?? 0));
                    }

                    $subtotal = $qty * $price;
                    $totalAmount += $subtotal;

                    $validItems[] = [
                        'sku' => $sku,
                        'qty' => $qty,
                        'price' => $price,
                        'subtotal' => $subtotal
                    ];
                }
            }

            if (count($validItems) === 0) throw new \Exception("Data produk tidak valid.");

            if ($dpAmount > $totalAmount) {
                throw new \Exception("DP (Rp " . number_format($dpAmount,0,',','.') . ") melebihi Grand Total.");
            }

            $status = ($dpAmount >= $totalAmount) ? 'PAID' : ($dpAmount > 0 ? 'PARTIAL' : 'PENDING');

            $soNumber = "SO-" . date('Ymd') . "-" . rand(1000,9999);
            $this->db->table('b2b_sales_orders')->insert([
                'so_number'    => $soNumber,
                'customer_id'  => $customerId,
                'order_date'   => date('Y-m-d'),
                'due_date'     => $dueDate,
                'total_amount' => $totalAmount,
                'paid_amount'  => $dpAmount,
                'status'       => $status
            ]);
            $soId = $this->db->insertID();

            foreach($validItems as $item) {
                $this->db->table('b2b_sales_order_items')->insert([
                    'so_id'    => $soId,
                    'fg_sku'   => $item['sku'],
                    'qty'      => $item['qty'],
                    'price'    => $item['price'],
                    'subtotal' => $item['subtotal']
                ]);
                $this->db->query("UPDATE warehouse_inventory SET physical_stock = physical_stock - ? WHERE sku = ?", [$item['qty'], $item['sku']]);
            }

            if ($dpAmount > 0) {
                $this->db->table('journals')->insert([
                    'journal_number'   => 'JRN-B2B-'.time(),
                    'transaction_date' => date('Y-m-d'),
                    'description'      => "Uang Muka (DP) SO: $soNumber",
                    'total_amount'     => $dpAmount,
                    'created_by'       => session()->get('name') ?? 'System'
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
            if ($this->db->transStatus() === false) throw new \Exception("Gagal menyimpan ke database.");

            return redirect()->back()->with('success', "Pesanan Grosir berhasil diterbitkan.");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function pay_installment($id)
    {
        try {
            $amount = (float)$this->request->getPost('amount');
            if ($amount <= 0) throw new \Exception("Nominal tidak sah.");

            $this->db->transStart();

            $so = $this->db->table('b2b_sales_orders')->where('id', $id)->get()->getRowArray();
            if (!$so) throw new \Exception("Pesanan tidak dijumpai.");

            $sisaPiutang = $so['total_amount'] - $so['paid_amount'];
            if ($amount > $sisaPiutang) {
                throw new \Exception("Bayaran melebihi sisa piutang.");
            }

            $this->db->query("UPDATE b2b_sales_orders SET paid_amount = paid_amount + ?, status = CASE WHEN paid_amount >= total_amount THEN 'PAID' ELSE 'PARTIAL' END WHERE id = ?", [$amount, $id]);
            
            $this->db->table('journals')->insert([
                'journal_number'   => 'JRN-PAY-'.time(),
                'transaction_date' => date('Y-m-d'),
                'description'      => "Bayar Angsuran SO: " . $so['so_number'],
                'total_amount'     => $amount,
                'created_by'       => session()->get('name') ?? 'System'
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

            return redirect()->back()->with('success', 'Pembayaran berhasil masuk ke Buku Besar!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function store_customer()
    {
        try {
            $companyName = $this->request->getPost('company_name');
            $this->db->table('b2b_customers')->insert([
                'company_name' => $companyName,
                'contact_name' => $this->request->getPost('contact_name'),
                'phone'        => $this->request->getPost('phone'),
                'address'      => $this->request->getPost('address')
            ]);
            return redirect()->back()->with('success', "Mitra Reseller berhasil ditambahkan!");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function delete_customer($id)
    {
        try {
            $used = $this->db->table('b2b_sales_orders')->where('customer_id', $id)->countAllResults();
            if ($used > 0) throw new \Exception("Pelanggan ini memiliki riwayat transaksi aktif.");
            $this->db->table('b2b_customers')->where('id', $id)->delete();
            return redirect()->back()->with('success', 'Data Pelanggan berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function surat_jalan($id)
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/portal');

        $so = $this->db->table('b2b_sales_orders')
            ->select('b2b_sales_orders.*, b2b_customers.company_name, b2b_customers.contact_name, b2b_customers.phone, b2b_customers.address')
            ->join('b2b_customers', 'b2b_customers.id = b2b_sales_orders.customer_id')
            ->where('b2b_sales_orders.id', $id)
            ->get()->getRowArray();

        if (!$so) return redirect()->to('/wholesale')->with('error', 'Dokumen SO tidak ditemukan.');

        $items = $this->db->table('b2b_sales_order_items')
            ->select('b2b_sales_order_items.*, warehouse_inventory.item_name')
            ->join('warehouse_inventory', 'warehouse_inventory.sku = b2b_sales_order_items.fg_sku', 'left')
            ->where('so_id', $id)
            ->get()->getResultArray();

        $data = [
            'title' => 'Surat Jalan Pengiriman: ' . $so['so_number'],
            'so'    => $so,
            'items' => $items
        ];

        return view('wholesale/surat_jalan', $data);
    }
}