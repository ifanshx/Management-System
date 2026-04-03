<?php

namespace App\Controllers;

class Procurement extends BaseController
{
    private $db;

    // ACCOUNT CODES (Sesuaikan dengan Master Data CoA)
    private string $accCash        = '1-1000';
    private string $accBank        = '1-2000';
    private string $accInventory   = '1-3000'; // Persediaan Bahan Baku
    private string $accAP          = '2-1000'; // Hutang Usaha / Supplier

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    private function ensureLogin()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/portal');
        }
        return null;
    }

    // =========================================================
    // INDEX DASBOR PROCUREMENT
    // =========================================================
    public function index()
    {
        if ($redirect = $this->ensureLogin()) return $redirect;

        $purchaseOrders = $this->db->table('purchase_orders')
            ->select('purchase_orders.*, suppliers.supplier_name')
            ->join('suppliers', 'suppliers.id = purchase_orders.supplier_id', 'left')
            ->orderBy('purchase_orders.id', 'DESC')
            ->get()->getResultArray();

        $suppliers = $this->db->table('suppliers')
            ->orderBy('supplier_name', 'ASC')
            ->get()->getResultArray();

        return view('procurement/index', [
            'title'          => 'Manajemen Pembelian (Procurement)',
            'purchaseOrders' => $purchaseOrders,
            'suppliers'      => $suppliers
        ]);
    }

    // =========================================================
    // MANAJEMEN VENDOR / SUPPLIER
    // =========================================================
    public function store_supplier()
    {
        if ($redirect = $this->ensureLogin()) return $redirect;

        try {
            $name    = trim($this->request->getPost('supplier_name'));
            $contact = trim($this->request->getPost('contact_person'));
            $phone   = trim($this->request->getPost('phone'));
            $address = trim($this->request->getPost('address'));

            if ($name === '') throw new \Exception("Nama vendor wajib diisi.");

            $exists = $this->db->table('suppliers')->where('supplier_name', $name)->countAllResults();
            if ($exists > 0) throw new \Exception("Vendor dengan nama <b>$name</b> sudah terdaftar.");

            $this->db->table('suppliers')->insert([
                'supplier_name'  => $name,
                'contact_person' => $contact,
                'phone'          => $phone,
                'address'        => $address
            ]);

            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['status' => 'success', 'message' => "Vendor $name berhasil didaftarkan!"]);
            }
            return redirect()->back()->with('success', "Vendor/Supplier <b>$name</b> berhasil didaftarkan!");
        } catch (\Exception $e) {
            if ($this->request->isAJAX()) return $this->response->setJSON(['status' => 'error', 'message' => $e->getMessage()]);
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function get_supplier($id)
    {
        if ($redirect = $this->ensureLogin()) return $this->response->setStatusCode(401);
        $supplier = $this->db->table('suppliers')->where('id', $id)->get()->getRowArray();
        return $this->response->setJSON($supplier);
    }

    public function update_supplier($id)
    {
        if ($redirect = $this->ensureLogin()) return $redirect;

        try {
            $name    = trim($this->request->getPost('supplier_name'));
            $contact = trim($this->request->getPost('contact_person'));
            $phone   = trim($this->request->getPost('phone'));
            $address = trim($this->request->getPost('address'));

            if ($name === '') throw new \Exception("Nama vendor wajib diisi.");

            $exists = $this->db->table('suppliers')->where('supplier_name', $name)->where('id !=', $id)->countAllResults();
            if ($exists > 0) throw new \Exception("Vendor dengan nama <b>$name</b> sudah terdaftar di sistem.");

            $this->db->table('suppliers')->where('id', $id)->update([
                'supplier_name'  => $name,
                'contact_person' => $contact,
                'phone'          => $phone,
                'address'        => $address
            ]);

            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['status' => 'success', 'message' => "Data Vendor $name berhasil diperbarui!"]);
            }
            return redirect()->back()->with('success', "Data Vendor <b>$name</b> berhasil diperbarui!");
        } catch (\Exception $e) {
            if ($this->request->isAJAX()) return $this->response->setJSON(['status' => 'error', 'message' => $e->getMessage()]);
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function delete_supplier($id)
    {
        if ($redirect = $this->ensureLogin()) return $redirect;
        try {
            $used = $this->db->table('purchase_orders')->where('supplier_id', $id)->countAllResults();
            if ($used > 0) throw new \Exception("DITOLAK! Vendor ini tidak bisa dihapus karena sudah memiliki riwayat transaksi PO.");

            $this->db->table('suppliers')->where('id', $id)->delete();
            return redirect()->back()->with('success', 'Data Vendor berhasil dihapus dari sistem.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    // =========================================================
    // PURCHASE ORDER (BUAT & SIMPAN)
    // =========================================================
    public function create_po()
    {
        if ($redirect = $this->ensureLogin()) return $redirect;

        $suppliers = $this->db->table('suppliers')->orderBy('supplier_name', 'ASC')->get()->getResultArray();
        $rawMaterials = $this->db->table('raw_materials')
            ->like('sku_material', 'MAT-', 'after')
            ->orderBy('material_name', 'ASC')
            ->get()->getResultArray();

        return view('procurement/create_po', [
            'title'        => 'Buat Purchase Order Baru',
            'suppliers'    => $suppliers,
            'rawMaterials' => $rawMaterials
        ]);
    }

    public function store_po()
    {
        if ($redirect = $this->ensureLogin()) return $redirect;

        try {
            $this->db->transStart();

            $supplierId   = (int) $this->request->getPost('supplier_id');
            $poDate       = $this->request->getPost('po_date');
            $rmSkus       = $this->request->getPost('rm_sku');
            $qtys         = $this->request->getPost('qty');
            $prices       = $this->request->getPost('unit_price'); 
            $taxAmount    = (float) $this->request->getPost('tax_amount'); 
            $shippingCost = (float) $this->request->getPost('shipping_cost');
            $paymentTerm  = trim($this->request->getPost('payment_term'));

            if ($supplierId <= 0) throw new \Exception("Vendor supplier wajib dipilih.");
            if (empty($poDate)) throw new \Exception("Tanggal PO wajib diisi.");
            if (empty($rmSkus) || count($rmSkus) === 0) throw new \Exception("Anda harus memilih minimal 1 material.");

            // Generate PO Number
            $dateStr = date('Ym', strtotime($poDate));
            $lastPo = $this->db->table('purchase_orders')->like('po_number', "PO-$dateStr", 'after')->orderBy('id', 'DESC')->get()->getRowArray();
            $seq = 1;
            if ($lastPo) {
                $parts = explode('-', $lastPo['po_number']);
                $seq = intval(end($parts)) + 1;
            }
            $poNumber = "PO-" . $dateStr . "-" . str_pad($seq, 4, '0', STR_PAD_LEFT);

            $this->db->table('purchase_orders')->insert([
                'po_number'      => $poNumber,
                'supplier_id'    => $supplierId,
                'po_date'        => $poDate,
                'payment_term'   => $paymentTerm ?: 'Tempo 7 Hari',
                'status'         => 'ORDERED',
                'payment_status' => 'UNPAID',
                'subtotal'       => 0, 'tax_amount' => 0, 'shipping_cost' => 0, 'total_amount' => 0, 'paid_amount' => 0
            ]);
            $poId = $this->db->insertID();

            $subTotal   = 0;
            $validItems = 0;

            for ($i = 0; $i < count($rmSkus); $i++) {
                $sku   = trim($rmSkus[$i] ?? '');
                $qty   = (float) ($qtys[$i] ?? 0);
                $price = (float) ($prices[$i] ?? 0);

                if ($sku === '' || $qty <= 0 || $price < 0) continue;

                $itemSubtotal = $qty * $price;
                $subTotal += $itemSubtotal;

                $this->db->table('purchase_order_items')->insert([
                    'po_id'      => $poId,
                    'rm_sku'     => $sku,
                    'qty'        => $qty,
                    'unit_price' => $price,
                    'subtotal'   => $itemSubtotal
                ]);
                $validItems++;
            }

            if ($validItems === 0) throw new \Exception("Item PO tidak valid. Purchase Order dibatalkan.");

            $grandTotal = $subTotal + $taxAmount + $shippingCost;

            $this->db->table('purchase_orders')->where('id', $poId)->update([
                'subtotal'      => $subTotal,
                'tax_amount'    => $taxAmount,
                'shipping_cost' => $shippingCost,
                'total_amount'  => $grandTotal
            ]);

            $this->db->transComplete();

            if ($this->db->transStatus() === false) throw new \Exception("Gagal menyimpan Purchase Order.");

            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['status' => 'success', 'message' => "Purchase Order $poNumber berhasil diterbitkan."]);
            }
            return redirect()->to('/procurement')->with('success', "Purchase Order <b>$poNumber</b> berhasil diterbitkan.");
        } catch (\Exception $e) {
            if ($this->request->isAJAX()) return $this->response->setJSON(['status' => 'error', 'message' => $e->getMessage()]);
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    // =========================================================
    // TERIMA BARANG & MASUKKAN KE GUDANG + JURNAL
    // =========================================================
    public function receive_goods($poId)
    {
        if ($redirect = $this->ensureLogin()) return $redirect;

        try {
            $this->db->transStart();

            $po = $this->db->table('purchase_orders')->where('id', $poId)->get()->getRowArray();
            if (!$po) throw new \Exception("Purchase Order tidak ditemukan.");
            if ($po['status'] === 'RECEIVED') throw new \Exception("Barang dari PO ini sudah pernah diterima sebelumnya.");

            $poItems = $this->db->table('purchase_order_items')->where('po_id', $poId)->get()->getResultArray();
            if (empty($poItems)) throw new \Exception("Item PO tidak ditemukan.");

            // 1. UPDATE STOCK + HPP (AVERAGE COSTING)
            foreach ($poItems as $item) {
                $rmStock = $this->db->table('raw_materials')->where('sku_material', $item['rm_sku'])->get()->getRowArray();
                if (!$rmStock) continue;

                $oldQty   = (float) ($rmStock['physical_stock'] ?? 0);
                $oldHpp   = (float) ($rmStock['hpp'] ?? 0);
                $newQty   = (float) ($item['qty'] ?? 0);
                $newPrice = (float) ($item['unit_price'] ?? 0);

                $totalQty = $oldQty + $newQty;
                $newHpp   = 0;
                if ($totalQty > 0) {
                    $newHpp = (($oldQty * $oldHpp) + ($newQty * $newPrice)) / $totalQty;
                }

                $this->db->table('raw_materials')->where('sku_material', $item['rm_sku'])->update([
                    'physical_stock' => $totalQty,
                    'hpp'            => $newHpp
                ]);
            }

            // 2. DETEKSI METODE PEMBAYARAN
            $paymentTerm = strtolower($po['payment_term'] ?? '');
            $isPaidUpfront = false;
            $creditAccCode = $this->accAP; // Default: Masuk ke Hutang

            if (strpos($paymentTerm, 'cash') !== false || strpos($paymentTerm, 'tunai') !== false || strpos($paymentTerm, 'c.o.d') !== false) {
                $creditAccCode = $this->accCash;
                $isPaidUpfront = true;
            } elseif (strpos($paymentTerm, 'transfer') !== false) {
                $creditAccCode = $this->accBank;
                $isPaidUpfront = true;
            }

            $paidAmount = $isPaidUpfront ? (float)$po['total_amount'] : 0;
            $paymentStatus = $isPaidUpfront ? 'PAID' : 'UNPAID';

            // 3. UPDATE STATUS PO
            $this->db->table('purchase_orders')->where('id', $poId)->update([
                'status'         => 'RECEIVED',
                'paid_amount'    => $paidAmount,
                'payment_status' => $paymentStatus
            ]);

            // 4. MENCATAT JURNAL AKUNTANSI (Gunakan Tanggal PO, bukan hari ini)
            $inventoryAccount = $this->getAccountByCode($this->accInventory);
            $creditAccount    = $this->getAccountByCode($creditAccCode);

            if (!$inventoryAccount || !$creditAccount) throw new \Exception("Sistem Gagal: Akun akuntansi Inventori atau Hutang tidak ditemukan.");

            $journalId = $this->createJournal(
                $po['po_date'], // PERBAIKAN: Jurnal direkam sesuai tanggal PO
                "Penerimaan Material PO: {$po['po_number']} ({$po['payment_term']})",
                (float)$po['total_amount']
            );

            // Jurnal Items
            $this->insertJournalItem($journalId, $inventoryAccount['id'], (float)$po['total_amount'], 0, 'Persediaan Bahan Baku'); // Debit Persediaan
            $this->insertJournalItem($journalId, $creditAccount['id'], 0, (float)$po['total_amount'], 'Hutang / Kas Keluar'); // Kredit Hutang/Kas

            // 5. JIKA DIBAYAR LUNAS DIAWAL -> CATAT KE KAS OPERASIONAL
            if ($isPaidUpfront) {
                $paymentId = $this->recordPoPaymentHistory(
                    $poId, $po['po_date'], // PERBAIKAN: Record payment juga pakai tanggal PO
                    $creditAccCode === $this->accCash ? 'Cash' : 'Bank Transfer',
                    $creditAccCode, (float)$po['total_amount'], $po['po_number'], "Pembayaran langsung saat penerimaan barang."
                );

                $this->recordFinanceOut(
                    $po['po_date'], // PERBAIKAN: Kas berkurang sesuai tanggal PO
                    $creditAccCode, (float)$po['total_amount'],
                    "Pembayaran Langsung PO: {$po['po_number']}", $po['po_number'], $journalId
                );
            }

            $this->db->transComplete();

            if ($this->db->transStatus() === false) throw new \Exception("Gagal memproses penerimaan barang.");

            $msg = $isPaidUpfront ? "Barang diterima dan pembayaran langsung tercatat pada " . date('d M Y', strtotime($po['po_date'])) : "Barang diterima di Gudang. Tagihan masuk ke Buku Hutang Usaha.";
            return redirect()->back()->with('success', $msg);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    // =========================================================
    // BAYAR HUTANG PO (MENDUKUNG CICILAN)
    // =========================================================
    public function pay_po($id)
    {
        if ($redirect = $this->ensureLogin()) return $redirect;

        try {
            $this->db->transStart();

            $po = $this->db->table('purchase_orders')->where('id', $id)->get()->getRowArray();
            if (!$po) throw new \Exception("Purchase Order tidak ditemukan.");
            if ($po['status'] !== 'RECEIVED') throw new \Exception("PO belum bisa dibayar karena barang belum diterima gudang.");
            if (($po['payment_status'] ?? 'UNPAID') === 'PAID') throw new \Exception("PO ini sudah berstatus LUNAS.");

            $paymentMethod = trim($this->request->getPost('payment_method')); 
            $payAmountRaw  = $this->request->getPost('payment_amount') ?? $this->request->getPost('pay_amount'); 
            $referenceNo   = trim($this->request->getPost('reference_number'));
            $notes         = trim($this->request->getPost('notes'));
            
            // PERBAIKAN: Ambil input tanggal pembayaran
            $paymentDate   = $this->request->getPost('payment_date') ?: date('Y-m-d'); 

            if ($paymentMethod === '') throw new \Exception("Metode pembayaran wajib dipilih.");

            $payAmount = (float) str_replace(['Rp', '.', ' '], '', $payAmountRaw);

            $currentPaid      = (float) ($po['paid_amount'] ?? 0);
            $totalAmount      = (float) ($po['total_amount'] ?? 0);
            $remainingBalance = $totalAmount - $currentPaid;

            if ($payAmount <= 0) throw new \Exception("Nominal pembayaran harus lebih dari Rp 0.");
            if ($payAmount > $remainingBalance) throw new \Exception("Nominal pembayaran melebihi sisa tagihan (Rp " . number_format($remainingBalance, 0, ',', '.') . ").");

            $apAccount     = $this->getAccountByCode($this->accAP);
            $sourceAccount = $this->getAccountByCode($paymentMethod);

            if (!$apAccount || !$sourceAccount) throw new \Exception("Akun akuntansi pembayaran tidak ditemukan.");

            // Cek saldo Kas jika Cash Out
            $saldoSumber = $this->db->query("SELECT calculated_balance FROM v_account_balances WHERE id = ?", [$sourceAccount['id']])->getRowArray()['calculated_balance'] ?? 0;
            if ($saldoSumber < $payAmount) throw new \Exception("Saldo akun <b>{$sourceAccount['account_name']}</b> tidak mencukupi.");

            // 1. Buat Jurnal (Mencatat berdasarkan Tanggal Bayar Inputan Kasir)
            $journalId = $this->createJournal(
                $paymentDate, "Pembayaran Hutang PO: {$po['po_number']}", $payAmount
            );

            $this->insertJournalItem($journalId, $apAccount['id'], $payAmount, 0, 'Pembayaran Hutang'); // Debit Hutang
            $this->insertJournalItem($journalId, $sourceAccount['id'], 0, $payAmount, 'Kas Keluar'); // Kredit Kas

            // 2. Catat Riwayat Bayar
            $paymentId = $this->recordPoPaymentHistory(
                $id, $paymentDate,
                $paymentMethod === $this->accCash ? 'Cash' : 'Bank Transfer',
                $paymentMethod, $payAmount, $referenceNo ?: $po['po_number'], $notes
            );

            // 3. Catat di Kas Operasional
            $this->recordFinanceOut(
                $paymentDate, $paymentMethod, $payAmount,
                "Pembayaran Hutang PO: {$po['po_number']}", $po['po_number'], $journalId
            );

            // 4. Update Status PO
            $newPaidAmount = $currentPaid + $payAmount;
            $newStatus = ($newPaidAmount >= $totalAmount) ? 'PAID' : 'PARTIAL';

            $this->db->table('purchase_orders')->where('id', $id)->update([
                'paid_amount'    => $newPaidAmount,
                'payment_status' => $newStatus
            ]);

            $this->db->transComplete();

            if ($this->db->transStatus() === false) throw new \Exception("Gagal memproses pembayaran PO.");

            $statusText = ($newStatus === 'PAID') ? "LUNAS" : "DICICIL";
            if ($this->request->isAJAX()) return $this->response->setJSON(['status' => 'success', 'message' => "Pembayaran Rp " . number_format($payAmount, 0, ',', '.') . " berhasil di tanggal " . date('d M Y', strtotime($paymentDate)) . ". Status: $statusText."]);
            return redirect()->back()->with('success', "Pembayaran PO berhasil diproses.");

        } catch (\Exception $e) {
            if ($this->request->isAJAX()) return $this->response->setJSON(['status' => 'error', 'message' => $e->getMessage()]);
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    // =========================================================
    // DETAIL
    // =========================================================
    public function detail($id)
    {
        if ($redirect = $this->ensureLogin()) return $redirect;

        $po = $this->db->table('purchase_orders')
            ->select('purchase_orders.*, suppliers.supplier_name, suppliers.contact_person, suppliers.phone, suppliers.address')
            ->join('suppliers', 'suppliers.id = purchase_orders.supplier_id')
            ->where('purchase_orders.id', $id)
            ->get()->getRowArray();

        if (!$po) return redirect()->to('/procurement')->with('error', 'Dokumen Purchase Order tidak ditemukan.');

        $items = $this->db->table('purchase_order_items')
            ->select('purchase_order_items.*, raw_materials.material_name, raw_materials.unit')
            ->join('raw_materials', 'raw_materials.sku_material = purchase_order_items.rm_sku', 'left')
            ->where('po_id', $id)
            ->get()->getResultArray();

        $payments = [];
        if ($this->db->tableExists('purchase_order_payments')) {
            $payments = $this->db->table('purchase_order_payments')->where('po_id', $id)->orderBy('id', 'DESC')->get()->getResultArray();
        }

        $companyModel = new \App\Models\CompanyModel();
        $company = $companyModel->first();

        return view('procurement/detail', [
            'title'    => 'Purchase Order: ' . $po['po_number'],
            'po'       => $po,
            'items'    => $items,
            'payments' => $payments,
            'company'  => $company
        ]);
    }

    // =========================================================
    // DELETE PO
    // =========================================================
    public function delete_po($id)
    {
        if ($redirect = $this->ensureLogin()) return $redirect;

        try {
            $po = $this->db->table('purchase_orders')->where('id', $id)->get()->getRowArray();
            if (!$po) throw new \Exception("Dokumen PO tidak ditemukan.");
            if ($po['status'] === 'RECEIVED') throw new \Exception("DITOLAK! PO ini sudah diterima dan masuk ke stok / buku besar. Gunakan mekanisme retur, bukan hapus.");

            $this->db->transStart();
            $this->db->table('purchase_order_items')->where('po_id', $id)->delete();
            if ($this->db->tableExists('purchase_order_payments')) {
                $this->db->table('purchase_order_payments')->where('po_id', $id)->delete();
            }
            $this->db->table('purchase_orders')->where('id', $id)->delete();
            $this->db->transComplete();

            if ($this->db->transStatus() === false) throw new \Exception("Gagal menghapus dokumen PO.");

            return redirect()->to('/procurement')->with('success', "Dokumen {$po['po_number']} berhasil dibatalkan dan dihapus.");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    // =========================================================
    // PRIVATE HELPERS
    // =========================================================
    private function getAccountByCode(string $accountCode): ?array
    {
        return $this->db->table('chart_of_accounts')->where('account_code', $accountCode)->get()->getRowArray();
    }

    private function createJournal(string $date, string $description, float $amount): int
    {
        $this->db->table('journals')->insert([
            'journal_number'   => 'JRN-' . date('Ym', strtotime($date)) . '-' . rand(1000, 9999),
            'transaction_date' => $date,
            'description'      => $description,
            'total_amount'     => $amount,
            'status'           => 'POSTED',
            'created_by'       => session()->get('name') ?? 'System Procurement'
        ]);

        return (int) $this->db->insertID();
    }

    private function insertJournalItem(int $journalId, int $accountId, float $debit, float $credit, string $desc = null): void
    {
        $this->db->table('journal_items')->insert([
            'journal_id'       => $journalId,
            'account_id'       => $accountId,
            'line_description' => $desc,
            'debit'            => $debit,
            'credit'           => $credit
        ]);
    }

    private function recordPoPaymentHistory(int $poId, string $paymentDate, string $paymentMethod, string $financeAccountCode, float $amount, ?string $referenceNumber = null, ?string $notes = null): int {
        if (!$this->db->tableExists('purchase_order_payments')) return 0;
        $this->db->table('purchase_order_payments')->insert([
            'po_id' => $poId, 'payment_date' => $paymentDate, 'payment_method' => $paymentMethod, 'finance_account_code' => $financeAccountCode,
            'amount' => $amount, 'reference_number' => $referenceNumber, 'notes' => $notes, 'created_by' => session()->get('name') ?? 'System'
        ]);
        return (int) $this->db->insertID();
    }

    private function recordFinanceOut(string $transactionDate, string $accountCode, float $amount, string $description, string $referenceNumber, int $journalId): void {
        if (!$this->db->tableExists('operational_cash')) return;

        $dateCode = date('Ymd', strtotime($transactionDate));
        $lastTrx = $this->db->table('operational_cash')->like('transaction_code', "TRX-$dateCode-", 'after')->orderBy('id', 'DESC')->get()->getRowArray();
        $newNumber = $lastTrx ? str_pad((int) substr($lastTrx['transaction_code'], -3) + 1, 3, '0', STR_PAD_LEFT) : '001';

        $methodText = $accountCode === $this->accCash ? 'Cash' : 'ATM';

        $this->db->table('operational_cash')->insert([
            'transaction_code' => "TRX-$dateCode-$newNumber", 'transaction_date' => $transactionDate,
            'type' => 'Cash Out', 'metode' => $methodText, 'category' => 'Pembayaran PO',
            'amount' => $amount, 'description' => $description, 'pic_name' => session()->get('name') ?? 'System',
            'journal_id' => $journalId, 'status' => 'POSTED'
        ]);
    }
}