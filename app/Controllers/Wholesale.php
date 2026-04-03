<?php

namespace App\Controllers;
use App\Controllers\Shopee;

class Wholesale extends BaseController
{
    private $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        
        // AUTO-PATCH: Mencegah error ENUM saat update status ke RETURNED
        try {
            $this->db->query("ALTER TABLE b2b_sales_orders MODIFY COLUMN status ENUM('PENDING','PARTIAL','PAID','RETURNED') DEFAULT 'PENDING'");
        } catch (\Exception $e) {
            // Abaikan jika sudah pernah dialter
        }
    }

    public function index()
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/portal');

        $salesOrders = $this->db->table('b2b_sales_orders')
            ->select('b2b_sales_orders.*, b2b_customers.company_name')
            ->join('b2b_customers', 'b2b_customers.id = b2b_sales_orders.customer_id')
            ->orderBy('b2b_sales_orders.id', 'DESC')
            ->get()->getResultArray();

        foreach ($salesOrders as &$so) {
            $items = $this->db->table('b2b_sales_order_items')
                ->select('b2b_sales_order_items.*, warehouse_inventory.item_name, warehouse_inventory.hpp')
                ->join('warehouse_inventory', 'warehouse_inventory.sku = b2b_sales_order_items.fg_sku', 'left')
                ->where('so_id', $so['id'])
                ->get()->getResultArray();

            foreach ($items as &$item) {
                $returned = $this->db->table('b2b_sales_return_items')
                    ->selectSum('qty_return')
                    ->where('so_item_id', $item['id'])
                    ->get()->getRowArray();

                $item['returned_qty']   = (int)($returned['qty_return'] ?? 0);
                $item['returnable_qty'] = max(0, (int)$item['qty'] - $item['returned_qty']);
            }

            $returns = $this->db->table('b2b_sales_returns')
                ->where('so_id', $so['id'])
                ->orderBy('id', 'DESC')
                ->get()->getResultArray();

            $so['items']   = $items;
            $so['returns'] = $returns; 
        }

        $customers = $this->db->table('b2b_customers')->orderBy('company_name', 'ASC')->get()->getResultArray();
        
        $products  = $this->db->table('warehouse_inventory')
                              ->select('sku, item_name, physical_stock, hpp, wholesale_price')
                              ->like('sku', 'PRD-', 'after')
                              ->get()->getResultArray();

        $data = [
            'title'       => 'B2B Wholesale & Piutang',
            'salesOrders' => $salesOrders,
            'customers'   => $customers,
            'products'    => $products
        ];

        return view('wholesale/index', $data);
    }

    private function cleanRupiah($string)
    {
        if(empty($string)) return 0;
        $cleanString = str_replace('.', '', $string);
        $cleanString = str_replace(',', '.', $cleanString);
        return (float) $cleanString;
    }

    private function getReturnedQtyBySoItem($soItemId)
    {
        $row = $this->db->table('b2b_sales_return_items')
            ->selectSum('qty_return')
            ->where('so_item_id', $soItemId)
            ->get()->getRowArray();

        return (int)($row['qty_return'] ?? 0);
    }

    public function store_so()
    {
        try {
            $this->db->transStart();

            $customerId = $this->request->getPost('customer_id');
            $orderType  = $this->request->getPost('order_type') ?? 'READY'; 
            
            $fgSkus     = $this->request->getPost('fg_sku'); 
            $qtys       = $this->request->getPost('qty');     
            $prices     = $this->request->getPost('unit_price'); 
            
            $dpRaw      = $this->request->getPost('dp_amount');
            $dpAmount   = $this->cleanRupiah($dpRaw);
            $dueDate    = $this->request->getPost('due_date');

            if(empty($fgSkus) || count($fgSkus) === 0) {
                throw new \Exception("Anda harus memilih minimal 1 produk.");
            }

            $totalAmount = 0;
            $totalHppCost = 0; 
            $validItems = [];

            for ($i = 0; $i < count($fgSkus); $i++) {
                if(!empty($fgSkus[$i])) {
                    $sku = $fgSkus[$i];
                    $qty = (int)$qtys[$i];
                    $price = $this->cleanRupiah($prices[$i]);

                    if ($qty <= 0 || $price <= 0) continue;

                    $stock = $this->db->table('warehouse_inventory')->where('sku', $sku)->get()->getRowArray();
                    
                    if ($orderType === 'READY') {
                        if(!$stock || $stock['physical_stock'] < $qty) {
                            throw new \Exception("GAGAL! Pesanan ini bersifat Ready Stock, tapi sisa {$sku} di gudang tidak cukup. Pilih tipe Pre-Order.");
                        }
                    }

                    $subtotal = $qty * $price;
                    $totalAmount += $subtotal;
                    
                    if ($orderType === 'READY') {
                        $totalHppCost += ($qty * ($stock['hpp'] ?? 0));
                    }

                    $validItems[] = [
                        'sku' => $sku,
                        'qty' => $qty,
                        'price' => $price,
                        'subtotal' => $subtotal
                    ];
                }
            }

            if (count($validItems) === 0) throw new \Exception("Data produk tidak valid.");
            if ($dpAmount > $totalAmount) throw new \Exception("DP (Rp " . number_format($dpAmount,0,',','.') . ") melebihi Grand Total.");

            $status = ($dpAmount >= $totalAmount) ? 'PAID' : ($dpAmount > 0 ? 'PARTIAL' : 'PENDING');
            $soNumber = "SO-" . date('Ymd') . "-" . rand(1000,9999);
            $orderStatus = ($orderType === 'PREORDER') ? 'PRE-ORDER' : 'SHIPPED'; 

            $this->db->table('b2b_sales_orders')->insert([
                'so_number'    => $soNumber,
                'customer_id'  => $customerId,
                'order_date'   => date('Y-m-d'),
                'due_date'     => $dueDate,
                'total_amount' => $totalAmount,
                'paid_amount'  => $dpAmount,
                'status'       => $status,
                'shipping_status' => $orderStatus
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
                
                if ($orderType === 'READY') {
                    $this->db->query("UPDATE warehouse_inventory SET physical_stock = physical_stock - ? WHERE sku = ?", [$item['qty'], $item['sku']]);
                }
            }

            // AUTO JURNAL PIUTANG
            $piutangAcc = $this->db->table('chart_of_accounts')->where('account_code', '1-4000')->get()->getRowArray();
            if(!$piutangAcc) {
                $this->db->table('chart_of_accounts')->insert(['account_code'=>'1-4000', 'account_name'=>'Piutang Usaha (B2B)', 'account_type'=>'ASET', 'balance'=>0]);
                $piutangAcc = $this->db->table('chart_of_accounts')->where('account_code', '1-4000')->get()->getRowArray();
            }

            $kas = $this->db->table('chart_of_accounts')->where('account_code', '1-2000')->get()->getRowArray(); 
            $rev = $this->db->table('chart_of_accounts')->where('account_code', '4-1000')->get()->getRowArray();
            $invAcc = $this->db->table('chart_of_accounts')->where('account_code', '1-3000')->get()->getRowArray(); 
            $hppAcc = $this->db->table('chart_of_accounts')->where('account_code', '5-1000')->get()->getRowArray();

            $this->db->table('journals')->insert([
                'journal_number'   => 'JRN-B2B-'.time(),
                'transaction_date' => date('Y-m-d'),
                'description'      => "Penjualan Grosir B2B: $soNumber" . ($orderType === 'PREORDER' ? " [Pre-Order]" : " [Ready Stock]"),
                'total_amount'     => $totalAmount,
                'created_by'       => session()->get('name') ?? 'System'
            ]);
            $journalId = $this->db->insertID();

            if ($rev && $kas && $piutangAcc) {
                $sisaPiutang = $totalAmount - $dpAmount;
                $this->db->table('journal_items')->insert(['journal_id' => $journalId, 'account_id' => $rev['id'], 'debit' => 0, 'credit' => $totalAmount, 'line_description' => 'Pendapatan B2B']);

                if ($dpAmount > 0) {
                    $this->db->table('journal_items')->insert(['journal_id' => $journalId, 'account_id' => $kas['id'], 'debit' => $dpAmount, 'credit' => 0, 'line_description' => 'DP Kas B2B']);
                    
                    $dateCode = date('Ymd');
                    $lastTrx = $this->db->table('operational_cash')->like('transaction_code', "TRX-$dateCode-", 'after')->orderBy('id', 'DESC')->get()->getRowArray();
                    $newNumber = $lastTrx ? str_pad((int) substr($lastTrx['transaction_code'], -3) + 1, 3, '0', STR_PAD_LEFT) : '001';
                    $this->db->table('operational_cash')->insert([
                        'transaction_code' => "TRX-$dateCode-$newNumber", 'transaction_date' => date('Y-m-d'),
                        'type' => 'Cash In', 'metode' => 'ATM', 'category' => 'Uang Muka B2B',
                        'amount' => $dpAmount, 'description' => "Uang Muka (DP) SO: $soNumber",
                        'pic_name' => session()->get('name') ?? 'Sistem'
                    ]);
                }

                if ($sisaPiutang > 0) {
                    $this->db->table('journal_items')->insert(['journal_id' => $journalId, 'account_id' => $piutangAcc['id'], 'debit' => $sisaPiutang, 'credit' => 0, 'line_description' => 'Piutang B2B']);
                }
            }

            if ($orderType === 'READY' && $totalHppCost > 0 && $invAcc && $hppAcc) {
                $this->db->table('journal_items')->insert(['journal_id' => $journalId, 'account_id' => $hppAcc['id'], 'debit' => $totalHppCost, 'credit' => 0, 'line_description' => 'HPP B2B']);
                $this->db->table('journal_items')->insert(['journal_id' => $journalId, 'account_id' => $invAcc['id'], 'debit' => 0, 'credit' => $totalHppCost, 'line_description' => 'Persediaan Keluar']);
            }

            $this->db->transComplete();
            if ($this->db->transStatus() === false) throw new \Exception("Gagal menyimpan ke database.");

            return redirect()->back()->with('success', "Pesanan Grosir berhasil diterbitkan. Tipe: " . ($orderType === 'PREORDER' ? "Pre-Order" : "Ready Stock"));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function pay_installment($id)
    {
        try {
            $amountRaw = $this->request->getPost('amount');
            $amount = $this->cleanRupiah($amountRaw);
            
            if ($amount <= 0) throw new \Exception("Nominal bayaran cicilan tidak sah atau Kosong.");

            $this->db->transStart();

            $so = $this->db->table('b2b_sales_orders')->where('id', $id)->get()->getRowArray();
            if (!$so) throw new \Exception("Pesanan tidak dijumpai.");

            $sisaPiutang = $so['total_amount'] - $so['paid_amount'];
            if ($amount > $sisaPiutang) {
                throw new \Exception("Bayaran (Rp ".number_format($amount,0,',','.').") melebihi sisa piutang (Rp ".number_format($sisaPiutang,0,',','.').").");
            }

            $this->db->query("UPDATE b2b_sales_orders SET paid_amount = paid_amount + ?, status = CASE WHEN paid_amount >= total_amount THEN 'PAID' ELSE 'PARTIAL' END WHERE id = ?", [$amount, $id]);
            
            $this->db->table('journals')->insert([
                'journal_number'   => 'JRN-PAY-'.time(),
                'transaction_date' => date('Y-m-d'),
                'description'      => "Pelunasan Piutang B2B SO: " . $so['so_number'],
                'total_amount'     => $amount,
                'created_by'       => session()->get('name') ?? 'System'
            ]);
            $journalId = $this->db->insertID();

            $kas = $this->db->table('chart_of_accounts')->where('account_code', '1-2000')->get()->getRowArray(); 
            $piutangAcc = $this->db->table('chart_of_accounts')->where('account_code', '1-4000')->get()->getRowArray(); 

            if($kas && $piutangAcc) {
                $this->db->table('journal_items')->insert(['journal_id' => $journalId, 'account_id' => $kas['id'], 'debit' => $amount, 'credit' => 0, 'line_description' => 'Kas Masuk']); 
                $this->db->table('journal_items')->insert(['journal_id' => $journalId, 'account_id' => $piutangAcc['id'], 'debit' => 0, 'credit' => $amount, 'line_description' => 'Pembayaran Piutang']); 
            }

            $dateCode = date('Ymd');
            $lastTrx = $this->db->table('operational_cash')->like('transaction_code', "TRX-$dateCode-", 'after')->orderBy('id', 'DESC')->get()->getRowArray();
            $newNumber = $lastTrx ? str_pad((int) substr($lastTrx['transaction_code'], -3) + 1, 3, '0', STR_PAD_LEFT) : '001';
            
            $this->db->table('operational_cash')->insert([
                'transaction_code' => "TRX-$dateCode-$newNumber", 'transaction_date' => date('Y-m-d'),
                'type' => 'Cash In', 'metode' => 'ATM', 'category' => 'Angsuran / Pelunasan B2B',
                'amount' => $amount, 'description' => "Pelunasan SO: " . $so['so_number'],
                'pic_name' => session()->get('name') ?? 'Sistem'
            ]);

            $this->db->transComplete();
            if ($this->db->transStatus() === false) throw new \Exception("Gagal mencatat pembayaran.");

            return redirect()->back()->with('success', 'Pembayaran angsuran berhasil! Uang masuk ke Bank dan catatan Piutang pelanggan telah dikurangi.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function ship_preorder($id)
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/portal');

        try {
            $this->db->transStart();

            $so = $this->db->table('b2b_sales_orders')->where('id', $id)->get()->getRowArray();
            if (!$so || $so['shipping_status'] !== 'PRE-ORDER') throw new \Exception("Pesanan tidak valid atau sudah dikirim.");

            $items = $this->db->table('b2b_sales_order_items')->where('so_id', $id)->get()->getResultArray();
            $totalHppCost = 0;

            foreach ($items as $item) {
                $stock = $this->db->table('warehouse_inventory')->where('sku', $item['fg_sku'])->get()->getRowArray();
                if (!$stock || $stock['physical_stock'] < $item['qty']) throw new \Exception("Gagal Kirim! Knalpot {$item['fg_sku']} belum selesai diproduksi pabrik.");
                $totalHppCost += ($item['qty'] * ($stock['hpp'] ?? 0));
            }

            foreach ($items as $item) {
                $this->db->query("UPDATE warehouse_inventory SET physical_stock = physical_stock - ? WHERE sku = ?", [$item['qty'], $item['fg_sku']]);
            }

            $this->db->table('b2b_sales_orders')->where('id', $id)->update([
                'shipping_status' => 'SHIPPED'
            ]);

            if ($totalHppCost > 0) {
                $this->db->table('journals')->insert([
                    'journal_number'   => 'JRN-SHP-'.time(),
                    'transaction_date' => date('Y-m-d'),
                    'description'      => "Pengiriman & Pengakuan HPP PO: " . $so['so_number'],
                    'total_amount'     => $totalHppCost,
                    'created_by'       => session()->get('name') ?? 'System'
                ]);
                $journalId = $this->db->insertID();

                $invAcc = $this->db->table('chart_of_accounts')->where('account_code', '1-3000')->get()->getRowArray(); 
                $hppAcc = $this->db->table('chart_of_accounts')->where('account_code', '5-1000')->get()->getRowArray();

                if ($invAcc && $hppAcc) {
                    $this->db->table('journal_items')->insert(['journal_id' => $journalId, 'account_id' => $hppAcc['id'], 'debit' => $totalHppCost, 'credit' => 0, 'line_description' => 'HPP Pre-Order']);
                    $this->db->table('journal_items')->insert(['journal_id' => $journalId, 'account_id' => $invAcc['id'], 'debit' => 0, 'credit' => $totalHppCost, 'line_description' => 'Persediaan Keluar']);
                }
            }

            $this->db->transComplete();
            if ($this->db->transStatus() === false) throw new \Exception("Gagal memproses pengiriman ke database.");

            return redirect()->back()->with('success', 'Barang Pre-Order berhasil dikirim! Stok dipotong dari gudang dan Jurnal HPP telah diakui.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    // =========================================================================
    // FITUR BARU: RETUR PARSIAL (B2B GROSIR)
    // =========================================================================
    public function return_so($id)
    {
        try {
            if (!session()->get('isLoggedIn')) {
                return redirect()->to('/portal');
            }

            $this->db->transStart();

            $so = $this->db->table('b2b_sales_orders')->where('id', $id)->get()->getRowArray();
            if (!$so) throw new \Exception("Sales Order tidak ditemukan.");

            $returnDate = $this->request->getPost('return_date') ?: date('Y-m-d');
            $reason     = trim($this->request->getPost('reason') ?? '');
            $refundType = $this->request->getPost('refund_type') ?? 'REDUCE_RECEIVABLE';

            $soItemIds  = $this->request->getPost('so_item_id');
            $qtyReturns = $this->request->getPost('qty_return');

            if (empty($soItemIds) || empty($qtyReturns)) {
                throw new \Exception("Tidak ada item retur yang dikirim.");
            }

            $returnItems = [];
            $totalReturn = 0;
            $totalHppReturn = 0;

            foreach ($soItemIds as $index => $soItemId) {
                $qtyReturn = (int)($qtyReturns[$index] ?? 0);
                if ($qtyReturn <= 0) continue;

                $soItem = $this->db->table('b2b_sales_order_items')->where('id', $soItemId)->get()->getRowArray();
                if (!$soItem) continue;

                if ((int)$soItem['so_id'] !== (int)$id) {
                    throw new \Exception("Item retur tidak valid.");
                }

                $alreadyReturned = $this->getReturnedQtyBySoItem($soItemId);
                $maxReturnable   = (int)$soItem['qty'] - $alreadyReturned;

                if ($qtyReturn > $maxReturnable) {
                    throw new \Exception("Qty retur untuk SKU {$soItem['fg_sku']} melebihi batas. Maksimal: {$maxReturnable}");
                }

                $subtotal = $qtyReturn * (float)$soItem['price'];
                $totalReturn += $subtotal;

                $stock = $this->db->table('warehouse_inventory')->where('sku', $soItem['fg_sku'])->get()->getRowArray();
                $hpp = (float)($stock['hpp'] ?? 0);
                
                // Jika pesanan sudah dikirim, kita harus tarik HPP nya kembali
                if ($so['shipping_status'] === 'SHIPPED') {
                    $totalHppReturn += ($qtyReturn * $hpp);
                }

                $returnItems[] = [
                    'so_item_id'  => $soItem['id'],
                    'fg_sku'      => $soItem['fg_sku'],
                    'qty_return'  => $qtyReturn,
                    'price'       => (float)$soItem['price'],
                    'subtotal'    => $subtotal,
                    'hpp'         => $hpp
                ];
            }

            if (count($returnItems) === 0) {
                throw new \Exception("Kuantitas retur belum diisi.");
            }

            // Generate Return Number
            $dateCode = date('Ymd');
            $lastReturn = $this->db->table('b2b_sales_returns')
                ->like('return_number', "RET-$dateCode-", 'after')
                ->orderBy('id', 'DESC')
                ->get()->getRowArray();

            $newNumber = $lastReturn ? str_pad((int)substr($lastReturn['return_number'], -3) + 1, 3, '0', STR_PAD_LEFT) : '001';
            $returnNumber = "RET-$dateCode-$newNumber";

            // Simpan header retur
            $this->db->table('b2b_sales_returns')->insert([
                'return_number' => $returnNumber,
                'so_id'         => $id,
                'return_date'   => $returnDate,
                'reason'        => $reason,
                'total_return'  => $totalReturn,
                'refund_type'   => $refundType,
                'status'        => 'POSTED',
                'created_by'    => session()->get('name') ?? 'System'
            ]);
            $salesReturnId = $this->db->insertID();

            // Simpan detail retur + Kembalikan stok ke gudang (Jika pesanan berstatus SHIPPED)
            foreach ($returnItems as $item) {
                $this->db->table('b2b_sales_return_items')->insert([
                    'sales_return_id' => $salesReturnId,
                    'so_item_id'      => $item['so_item_id'],
                    'fg_sku'          => $item['fg_sku'],
                    'qty_return'      => $item['qty_return'],
                    'price'           => $item['price'],
                    'subtotal'        => $item['subtotal']
                ]);

                if ($so['shipping_status'] === 'SHIPPED') {
                    $this->db->query(
                        "UPDATE warehouse_inventory SET physical_stock = physical_stock + ? WHERE sku = ?",
                        [$item['qty_return'], $item['fg_sku']]
                    );
                }
            }

            // UPDATE B2B SALES ORDER TOTAL
            $newTotalAmount = max(0, (float)$so['total_amount'] - $totalReturn);
            $paidAmount     = (float)$so['paid_amount'];

            if ($refundType === 'REDUCE_RECEIVABLE') {
                $newPaidAmount = min($paidAmount, $newTotalAmount);
            } elseif ($refundType === 'CUSTOMER_CREDIT') {
                $newPaidAmount = $paidAmount;
            } else { 
                $newPaidAmount = max(0, $paidAmount - $totalReturn);
            }

            $newStatus = 'PENDING';
            if ($newTotalAmount <= 0) {
                $newStatus = 'RETURNED';
            } elseif ($newPaidAmount >= $newTotalAmount) {
                $newStatus = 'PAID';
            } elseif ($newPaidAmount > 0) {
                $newStatus = 'PARTIAL';
            }

            $this->db->table('b2b_sales_orders')->where('id', $id)->update([
                'total_amount' => $newTotalAmount,
                'paid_amount'  => $newPaidAmount,
                'status'       => $newStatus
            ]);

            // JURNAL AKUNTANSI RETUR (AUTO-REVERSAL)
            $returAcc   = $this->db->table('chart_of_accounts')->where('account_code', '4-1100')->get()->getRowArray();
            $piutangAcc = $this->db->table('chart_of_accounts')->where('account_code', '1-4000')->get()->getRowArray();
            $bankAcc    = $this->db->table('chart_of_accounts')->where('account_code', '1-2000')->get()->getRowArray();
            $invAcc     = $this->db->table('chart_of_accounts')->where('account_code', '1-3000')->get()->getRowArray();
            $hppAcc     = $this->db->table('chart_of_accounts')->where('account_code', '5-1000')->get()->getRowArray();

            $this->db->table('journals')->insert([
                'journal_number'   => 'JRN-RET-'.time(),
                'transaction_date' => $returnDate,
                'description'      => "Retur Penjualan SO: {$so['so_number']} / {$returnNumber}",
                'total_amount'     => $totalReturn,
                'created_by'       => session()->get('name') ?? 'System'
            ]);
            $journalId = $this->db->insertID();

            if ($returAcc) {
                $this->db->table('journal_items')->insert(['journal_id' => $journalId, 'account_id' => $returAcc['id'], 'line_description' => 'Retur Penjualan (Contra)', 'debit' => $totalReturn, 'credit' => 0]);

                if ($refundType === 'CASH_REFUND' && $bankAcc) {
                    $this->db->table('journal_items')->insert(['journal_id' => $journalId, 'account_id' => $bankAcc['id'], 'line_description' => 'Refund Kas Customer', 'debit' => 0, 'credit' => $totalReturn]);

                    // Catat Kas Keluar
                    $trxDateCode = date('Ymd');
                    $lastTrx = $this->db->table('operational_cash')->like('transaction_code', "TRX-$trxDateCode-", 'after')->orderBy('id', 'DESC')->get()->getRowArray();
                    $trxNum = $lastTrx ? str_pad((int)substr($lastTrx['transaction_code'], -3) + 1, 3, '0', STR_PAD_LEFT) : '001';

                    $this->db->table('operational_cash')->insert([
                        'transaction_code' => "TRX-$trxDateCode-$trxNum",
                        'transaction_date' => $returnDate,
                        'type'             => 'Cash Out',
                        'metode'           => 'ATM',
                        'category'         => 'Refund Retur Penjualan',
                        'amount'           => $totalReturn,
                        'description'      => "Refund retur penjualan SO: {$so['so_number']}",
                        'pic_name'         => session()->get('name') ?? 'System'
                    ]);
                } else {
                    if ($piutangAcc) {
                        $this->db->table('journal_items')->insert(['journal_id' => $journalId, 'account_id' => $piutangAcc['id'], 'line_description' => 'Pengurang Piutang Customer', 'debit' => 0, 'credit' => $totalReturn]);
                    }
                }
            }

            // Balik HPP (Hanya jika barang masuk gudang kembali)
            if ($so['shipping_status'] === 'SHIPPED' && $invAcc && $hppAcc && $totalHppReturn > 0) {
                $this->db->table('journal_items')->insert(['journal_id' => $journalId, 'account_id' => $invAcc['id'], 'line_description' => 'Persediaan Masuk (Retur)', 'debit' => $totalHppReturn, 'credit' => 0]);
                $this->db->table('journal_items')->insert(['journal_id' => $journalId, 'account_id' => $hppAcc['id'], 'line_description' => 'Pembalik Beban HPP', 'debit' => 0, 'credit' => $totalHppReturn]);
            }

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                throw new \Exception("Gagal memproses retur penjualan.");
            }

            return redirect()->back()->with('success', "Retur parsial berhasil diproses. Dokumen: <b>{$returnNumber}</b>.");

        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function store_customer() { /* Sama */ }
    public function delete_customer($id) { /* Sama */ }
    public function surat_jalan($id) { /* Sama */ }
}