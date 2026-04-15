<?php

namespace App\Controllers;

use App\Services\InventoryService;
use App\Services\AccountingService;

class Procurement extends BaseController
{
    private $db;

    // ACCOUNT CODES (Sesuaikan dengan Master Data CoA)
    private string $accCash        = '1-1000';
    private string $accBank        = '1-2000';
    private string $accInventory   = '1-3000'; // Persediaan Bahan Baku & Penolong
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

    private function normalizePhone(?string $phone): string
    {
        $phone = trim((string)$phone);
        if ($phone === '') return '';
        $phone = preg_replace('/[^0-9]/', '', $phone);
        if ($phone === '') return '';

        if (substr($phone, 0, 1) === '0') {
            $phone = '62' . substr($phone, 1);
        } elseif (substr($phone, 0, 2) !== '62') {
            if (substr($phone, 0, 1) === '8') {
                $phone = '62' . $phone;
            }
        }

        if (!preg_match('/^62[0-9]{8,15}$/', $phone)) return '';
        return $phone;
    }

    public function index()
    {
        if ($redirect = $this->ensureLogin()) return $redirect;

        $purchaseOrders = $this->db->table('purchase_orders')
            ->select('purchase_orders.*, suppliers.supplier_name, suppliers.phone as supplier_phone')
            ->join('suppliers', 'suppliers.id = purchase_orders.supplier_id', 'left')
            ->orderBy('purchase_orders.id', 'DESC')
            ->get()->getResultArray();

        $poItems = $this->db->table('purchase_order_items')
            ->select('purchase_order_items.*, raw_materials.material_name, purchase_order_items.purchase_uom as unit')
            ->join('raw_materials', 'raw_materials.sku_material = purchase_order_items.rm_sku', 'left')
            ->get()->getResultArray();

        $groupedItems = [];
        foreach ($poItems as $item) {
            $groupedItems[$item['po_id']][] = $item;
        }

        $suppliers = $this->db->table('suppliers')
            ->orderBy('supplier_name', 'ASC')
            ->get()->getResultArray();
            
        $company = $this->db->tableExists('company_settings') ? $this->db->table('company_settings')->get()->getRowArray() : [];

        return view('procurement/index', [
            'title'          => 'Manajemen Pembelian (Procurement)',
            'purchaseOrders' => $purchaseOrders,
            'groupedItems'   => $groupedItems,
            'suppliers'      => $suppliers,
            'company'        => $company
        ]);
    }

    public function store_supplier()
    {
        if ($redirect = $this->ensureLogin()) return $redirect;

        try {
            $name    = trim($this->request->getPost('supplier_name'));
            $contact = trim($this->request->getPost('contact_person'));
            $phone   = trim($this->request->getPost('phone'));
            $address = trim($this->request->getPost('address'));

            if ($name === '') throw new \Exception("Nama vendor wajib diisi.");

            $normalizedPhone = $this->normalizePhone($phone);
            if ($phone !== '' && $normalizedPhone === '') {
                throw new \Exception("Nomor WhatsApp vendor tidak valid. Gunakan format seperti <b>08123456789</b>.");
            }

            $exists = $this->db->table('suppliers')->where('supplier_name', $name)->countAllResults();
            if ($exists > 0) throw new \Exception("Vendor dengan nama <b>$name</b> sudah terdaftar.");

            $this->db->table('suppliers')->insert([
                'supplier_name'  => $name,
                'contact_person' => $contact,
                'phone'          => $normalizedPhone, 
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

            $normalizedPhone = $this->normalizePhone($phone);
            if ($phone !== '' && $normalizedPhone === '') {
                throw new \Exception("Nomor WhatsApp vendor tidak valid.");
            }

            $exists = $this->db->table('suppliers')->where('supplier_name', $name)->where('id !=', $id)->countAllResults();
            if ($exists > 0) throw new \Exception("Vendor dengan nama <b>$name</b> sudah terdaftar di sistem.");

            $this->db->table('suppliers')->where('id', $id)->update([
                'supplier_name'  => $name,
                'contact_person' => $contact,
                'phone'          => $normalizedPhone, 
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

    public function create_po()
    {
        if ($redirect = $this->ensureLogin()) return $redirect;

        $suppliers = $this->db->table('suppliers')->orderBy('supplier_name', 'ASC')->get()->getResultArray();
        $rawMaterials = $this->db->table('raw_materials')->like('sku_material', 'MAT-', 'after')->orderBy('material_name', 'ASC')->get()->getResultArray();

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
            $taxAmount    = (float) str_replace('.', '', $this->request->getPost('tax_amount') ?? 0);
            $shippingCost = (float) str_replace('.', '', $this->request->getPost('shipping_cost') ?? 0);
            $paymentTerm  = trim($this->request->getPost('payment_term'));

            if ($supplierId <= 0) throw new \Exception("Vendor supplier wajib dipilih.");
            if (empty($poDate)) throw new \Exception("Tanggal PO wajib diisi.");
            if (empty($rmSkus) || count($rmSkus) === 0) throw new \Exception("Anda harus memilih minimal 1 material.");

            $supplier = $this->db->table('suppliers')->where('id', $supplierId)->get()->getRowArray();
            if (!$supplier) throw new \Exception("Data Vendor tidak valid.");

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
                'subtotal'       => 0,
                'tax_amount'     => 0,
                'shipping_cost'  => 0,
                'total_amount'   => 0,
                'paid_amount'    => 0
            ]);

            $poId = $this->db->insertID();

            $subTotal   = 0;
            $validItems = 0;
            $waItemList = "";

            for ($i = 0; $i < count($rmSkus); $i++) {
                $sku   = trim($rmSkus[$i] ?? '');
                $qty   = (float) ($qtys[$i] ?? 0);
                $price = (float) str_replace('.', '', ($prices[$i] ?? 0));

                if ($sku === '' || $qty <= 0 || $price < 0) continue;

                $material = $this->db->table('raw_materials')->where('sku_material', $sku)->get()->getRowArray();
                if (!$material) continue;

                $purchaseUom = $material['purchase_uom'] ?? $material['unit'] ?? 'PCS';
                $baseUom     = $material['base_uom'] ?? $material['unit'] ?? 'PCS';
                $conversion  = (float)($material['conversion_factor'] ?? 1);
                if ($conversion <= 0) $conversion = 1;

                $qtyBase        = $qty * $conversion;
                $unitPriceBase  = $price / $conversion;
                $itemSubtotal   = $qty * $price;

                $subTotal += $itemSubtotal;

                $this->db->table('purchase_order_items')->insert([
                    'po_id'             => $poId,
                    'rm_sku'            => $sku,
                    'purchase_uom'      => $purchaseUom,
                    'base_uom'          => $baseUom,
                    'conversion_factor' => $conversion,
                    'qty'               => $qty,
                    'qty_base'          => $qtyBase,
                    'unit_price'        => $price,
                    'unit_price_base'   => $unitPriceBase,
                    'subtotal'          => $itemSubtotal
                ]);

                $matName = $material['material_name'] ?? $sku;
                $waItemList .= "- {$qty} {$purchaseUom} {$matName}";
                if ($purchaseUom !== $baseUom) {
                    $waItemList .= " (≈ " . number_format($qtyBase, 2, ',', '.') . " {$baseUom})";
                }
                $waItemList .= "\n";
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

            $waLink = null;
            $supPhone = $this->normalizePhone($supplier['phone'] ?? '');

            if (!empty($supPhone)) {
                $companyInfo = $this->db->tableExists('company_settings') ? $this->db->table('company_settings')->get()->getRowArray() : [];
                $companyName = $companyInfo ? ($companyInfo['company_name'] ?? 'Pabrik Knalpot') : 'Pabrik Knalpot';

                $waText = "Halo *{$supplier['supplier_name']}*,\n\n";
                $waText .= "Kami dari *{$companyName}* ingin melakukan pemesanan barang.\n";
                $waText .= "*(No. PO: {$poNumber})*\n\n";
                $waText .= "Berikut rincian pesanan kami:\n";
                $waText .= $waItemList . "\n";
                $waText .= "Mohon informasi ketersediaan barang dan total tagihannya (Termin: {$paymentTerm}).\n\n";
                $waText .= "Terima kasih.";

                $waLink = "https://wa.me/{$supPhone}?text=" . urlencode($waText);
            }

            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'status'  => 'success',
                    'message' => "Purchase Order $poNumber berhasil diterbitkan.",
                    'wa_link' => $waLink
                ]);
            }
            return redirect()->to('/procurement')->with('success', "Purchase Order <b>$poNumber</b> berhasil diterbitkan.");
        } catch (\Exception $e) {
            if ($this->request->isAJAX()) return $this->response->setJSON(['status' => 'error', 'message' => $e->getMessage()]);
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    // ====================================================================
    // FUNGSI BARU: AMBIL DATA ITEM PO UNTUK MODAL CHECKLIST
    // ====================================================================
    public function get_po_items($id)
    {
        if ($this->request->isAJAX()) {
            $items = $this->db->table('purchase_order_items')
                ->select('purchase_order_items.*, raw_materials.material_name, raw_materials.unit')
                ->join('raw_materials', 'raw_materials.sku_material = purchase_order_items.rm_sku', 'left')
                ->where('po_id', $id)
                ->get()->getResultArray();
            
            // Hitung sisa yang belum diterima agar UI bisa membatasi Input
            foreach ($items as &$item) {
                $remBase = (float)$item['qty_base'] - (float)$item['qty_received'];
                $item['remaining_qty'] = $remBase / (float)$item['conversion_factor'];
            }
            
            return $this->response->setJSON($items);
        }
    }

    // ====================================================================
    // FUNGSI BARU: PENERIMAAN BARANG PARSIAL (DYNAMIC QTY)
    // ====================================================================
    public function receive_goods($poId)
    {
        if ($redirect = $this->ensureLogin()) return $redirect;

        try {
            $this->db->transStart();

            $po = $this->db->table('purchase_orders')->where('id', $poId)->get()->getRowArray();
            if (!$po) throw new \Exception("Purchase Order tidak ditemukan.");
            if ($po['status'] === 'RECEIVED') throw new \Exception("Barang dari PO ini sudah diterima sepenuhnya.");

            $poItems = $this->db->table('purchase_order_items')->where('po_id', $poId)->get()->getResultArray();
            if (empty($poItems)) throw new \Exception("Item PO tidak ditemukan.");

            // Ambil array input jumlah dari User & Catatan Penerimaan
            $inputQtys = $this->request->getPost('qty_received'); 
            $receiveNotes = trim((string)$this->request->getPost('receive_notes'));

            $inventoryService = new \App\Services\InventoryService();
            
            $totalReceivedValue = 0;
            $hasReceiving = false;
            $allFullyReceived = true;

            foreach ($poItems as $item) {
                $itemId = $item['id'];
                $inputQty = isset($inputQtys[$itemId]) ? (float)$inputQtys[$itemId] : 0;
                
                $remBase = (float)$item['qty_base'] - (float)$item['qty_received'];
                $remPurchase = $remBase / (float)$item['conversion_factor'];

                if ($inputQty > 0) {
                    if ($inputQty > $remPurchase) {
                        throw new \Exception("Kuantitas terima untuk {$item['rm_sku']} melebihi sisa pesanan (Maks: {$remPurchase} {$item['purchase_uom']}).");
                    }

                    $hasReceiving = true;
                    // Konversi input user (purchase uom) ke base uom gudang
                    $receivedBase = $inputQty * (float)$item['conversion_factor'];
                    // Hitung nilai rupiah barang yang masuk saat ini
                    $receivedValue = $inputQty * (float)$item['unit_price'];
                    $totalReceivedValue += $receivedValue;

                    // 1. UPDATE STOK GUDANG + HPP RATA-RATA
                    $inventoryService->receiveRawMaterial(
                        $item['rm_sku'],
                        $receivedBase,
                        $receivedValue,
                        $po['po_number'],
                        $poId,
                        "Penerimaan PO " . ($receiveNotes ? "($receiveNotes)" : "")
                    );

                    // 2. UPDATE PROGRESS ITEM DI TABEL PO
                    $newQtyReceived = (float)$item['qty_received'] + $receivedBase;
                    // Beri sedikit toleransi desimal
                    $isFully = ($newQtyReceived >= ((float)$item['qty_base'] - 0.01)) ? 1 : 0;

                    $this->db->table('purchase_order_items')->where('id', $itemId)->update([
                        'qty_received'      => $newQtyReceived,
                        'is_fully_received' => $isFully
                    ]);

                    if (!$isFully) {
                        $allFullyReceived = false;
                    }
                } else {
                    // Cek jika item ini sebelumnya belum lunas diterima, maka keseluruhan PO belum lunas diterima.
                    if ($item['is_fully_received'] == 0) {
                        $allFullyReceived = false;
                    }
                }
            }

            if (!$hasReceiving) {
                throw new \Exception("Ditolak! Anda belum mengisi kuantitas penerimaan barang sama sekali.");
            }

            // 3. UPDATE STATUS PO
            $newStatus = $allFullyReceived ? 'RECEIVED' : 'ORDERED';
            $newReceiptStatus = $allFullyReceived ? 'FULLY_RECEIVED' : 'PARTIAL';

            $this->db->table('purchase_orders')->where('id', $poId)->update([
                'status'         => $newStatus,
                'receipt_status' => $newReceiptStatus,
                'received_at'    => date('Y-m-d H:i:s'),
                'received_by'    => session()->get('name') ?? 'SYSTEM'
            ]);

            // 4. JURNAL AKUNTANSI OTOMATIS: PERSEDIAAN VS HUTANG USAHA (Sesuai nilai barang yang datang saja)
            $journalItems = [
                ['account_id' => $this->getAccountByCode($this->accInventory)['id'], 'debit' => $totalReceivedValue, 'credit' => 0, 'memo' => 'Persediaan Bahan Baku Masuk Gudang'],
                ['account_id' => $this->getAccountByCode($this->accAP)['id'], 'debit' => 0, 'credit' => $totalReceivedValue, 'memo' => 'Hutang Pembelian Vendor']
            ];

            $accService = new \App\Services\AccountingService();
            $accService->createJournal(
                date('Y-m-d'), 
                "Penerimaan Barang PO: {$po['po_number']}" . ($receiveNotes ? " - $receiveNotes" : ""), 
                'PROCUREMENT', 
                $po['po_number'], 
                $totalReceivedValue, 
                $journalItems, 
                session()->get('name') ?? 'System',
                $poId
            );

            $this->db->transComplete();
            if ($this->db->transStatus() === false) throw new \Exception("Gagal memproses sistem penerimaan barang.");

            $msg = $allFullyReceived 
                ? "Semua barang untuk <b>{$po['po_number']}</b> telah diterima lunas. Tagihan dimasukkan ke Hutang." 
                : "Penerimaan parsial (sebagian) untuk <b>{$po['po_number']}</b> berhasil dicatat. Sisa pesanan masih berstatus OTW.";

            return redirect()->back()->with('success', $msg);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    // ====================================================================
    // FUNGSI BARU: BATALKAN PENERIMAAN BARANG (VOID PO TERPUSAT)
    // ====================================================================
    public function void_po($poId)
    {
        if ($redirect = $this->ensureLogin()) return $redirect;

        try {
            $this->db->transStart();

            $po = $this->db->table('purchase_orders')->where('id', $poId)->get()->getRowArray();
            if (!$po) throw new \Exception("Purchase Order tidak ditemukan.");
            if ((float)$po['paid_amount'] > 0) throw new \Exception("PO ini sudah dibayar! Anda harus Membatalkan Pembayaran (Void) di modul Finance terlebih dahulu.");

            $poItems = $this->db->table('purchase_order_items')->where('po_id', $poId)->get()->getResultArray();
            $inventoryService = new \App\Services\InventoryService();

            $hasReceivedItems = false;
            foreach ($poItems as $item) {
                if ((float)$item['qty_received'] > 0) {
                    $hasReceivedItems = true;
                    // Hitung nilai uang yang harus di-void (HPP)
                    $receivedPurchaseQty = (float)$item['qty_received'] / (float)$item['conversion_factor'];
                    $receivedValue = $receivedPurchaseQty * (float)$item['unit_price'];

                    // 1. Tarik Kembali Stok Gudang & Revert HPP
                    $inventoryService->voidReceipt(
                        $item['rm_sku'],
                        $item['qty_received'], // base uom
                        $receivedValue,
                        $po['po_number'],
                        $poId,
                        'Batal Penerimaan PO (VOID)'
                    );

                    // Reset Qty Received di Item
                    $this->db->table('purchase_order_items')->where('id', $item['id'])->update([
                        'qty_received' => 0,
                        'is_fully_received' => 0
                    ]);
                }
            }

            if (!$hasReceivedItems) throw new \Exception("Sistem Gagal: Belum ada barang yang diterima dari PO ini.");

            // 2. Kembalikan Status PO ke titik awal (ORDERED)
            $this->db->table('purchase_orders')->where('id', $poId)->update([
                'status'         => 'ORDERED',
                'receipt_status' => 'PENDING',
                'received_at'    => null,
                'received_by'    => null
            ]);

            // 3. Batalkan SEMUA Jurnal Hutang terkait PO ini
            $accService = new \App\Services\AccountingService();
            $journals = $this->db->table('journals')
                ->where('source_module', 'PROCUREMENT')
                ->where('source_id', $poId)
                ->where('status !=', 'VOID')
                ->get()->getResultArray();

            foreach ($journals as $journal) {
                $accService->voidJournal($journal['id'], "Batal Terima PO (Void) - {$po['po_number']}", session()->get('name'));
            }

            $this->db->transComplete();
            if ($this->db->transStatus() === false) throw new \Exception("Gagal memproses Void PO.");

            return redirect()->back()->with('success', "Seluruh penerimaan PO <b>{$po['po_number']}</b> berhasil DIBATALKAN. Stok ditarik dari gudang & Hutang dilenyapkan.");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function pay_po($id)
    {
        if ($redirect = $this->ensureLogin()) return $redirect;

        try {
            $this->db->transStart();

            $po = $this->db->table('purchase_orders')->where('id', $id)->get()->getRowArray();
            if (!$po) throw new \Exception("Purchase Order tidak ditemukan.");
            if ($po['status'] === 'ORDERED' && $po['receipt_status'] === 'PENDING') throw new \Exception("PO belum bisa dibayar karena barang belum diterima gudang sama sekali.");
            if (($po['payment_status'] ?? 'UNPAID') === 'PAID') throw new \Exception("PO ini sudah berstatus LUNAS.");

            $paymentMethod = trim($this->request->getPost('payment_method')); 
            $payAmountRaw  = $this->request->getPost('payment_amount') ?? $this->request->getPost('pay_amount'); 
            $referenceNo   = trim($this->request->getPost('reference_number'));
            $notes         = trim($this->request->getPost('notes'));
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

            $saldoSumber = $this->db->query("SELECT calculated_balance FROM v_account_balances WHERE id = ?", [$sourceAccount['id']])->getRowArray()['calculated_balance'] ?? 0;
            if ($saldoSumber < $payAmount) throw new \Exception("Saldo akun <b>{$sourceAccount['account_name']}</b> tidak mencukupi.");

            $journalItems = [
                ['account_id' => $apAccount['id'], 'debit' => $payAmount, 'credit' => 0, 'memo' => 'Pembayaran Hutang'],
                ['account_id' => $sourceAccount['id'], 'debit' => 0, 'credit' => $payAmount, 'memo' => 'Kas Keluar']
            ];

            $accService = new AccountingService();
            $journalId = $accService->createJournal(
                $paymentDate, 
                "Pembayaran Hutang PO: {$po['po_number']}", 
                'PAYMENT', 
                $po['po_number'], 
                $payAmount, 
                $journalItems, 
                session()->get('name') ?? 'System',
                $id
            );

            $this->recordPoPaymentHistory(
                $id, $paymentDate,
                $paymentMethod === $this->accCash ? 'Cash' : 'Bank Transfer',
                $paymentMethod, $payAmount, $referenceNo ?: $po['po_number'], $notes
            );

            $this->recordFinanceOut(
                $paymentDate, $paymentMethod, $payAmount,
                "Pembayaran Hutang PO: {$po['po_number']}", $po['po_number'], $journalId
            );

            $newPaidAmount = $currentPaid + $payAmount;
            $newStatus = ($newPaidAmount >= $totalAmount) ? 'PAID' : 'PARTIAL';

            $this->db->table('purchase_orders')->where('id', $id)->update([
                'paid_amount'    => $newPaidAmount,
                'payment_status' => $newStatus
            ]);

            $this->db->transComplete();
            if ($this->db->transStatus() === false) throw new \Exception("Gagal memproses pembayaran PO.");

            $statusText = ($newStatus === 'PAID') ? "LUNAS" : "DICICIL";
            if ($this->request->isAJAX()) return $this->response->setJSON(['status' => 'success', 'message' => "Pembayaran Rp " . number_format($payAmount, 0, ',', '.') . " berhasil. Status: $statusText."]);
            return redirect()->back()->with('success', "Pembayaran PO berhasil diproses.");

        } catch (\Exception $e) {
            if ($this->request->isAJAX()) return $this->response->setJSON(['status' => 'error', 'message' => $e->getMessage()]);
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function detail($id)
    {
        if ($redirect = $this->ensureLogin()) return $redirect;

        $po = $this->db->table('purchase_orders')
            ->select('purchase_orders.*, suppliers.supplier_name, suppliers.contact_person, suppliers.phone AS supplier_phone, suppliers.address AS supplier_address')
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

    public function delete_po($id)
    {
        if ($redirect = $this->ensureLogin()) return $redirect;

        try {
            $po = $this->db->table('purchase_orders')->where('id', $id)->get()->getRowArray();
            if (!$po) throw new \Exception("Dokumen PO tidak ditemukan.");
            if ($po['status'] === 'RECEIVED' || $po['receipt_status'] !== 'PENDING') throw new \Exception("DITOLAK! PO ini sudah diterima (meski sebagian) dan masuk ke stok / buku besar. Gunakan fitur Batal/Void terlebih dahulu.");

            $this->db->transStart();
            $this->db->table('purchase_order_items')->where('po_id', $id)->delete();
            if ($this->db->tableExists('purchase_order_payments')) {
                $this->db->table('purchase_order_payments')->where('po_id', $id)->delete();
            }
            $this->db->table('purchase_orders')->where('id', $id)->delete();
            $this->db->transComplete();

            if ($this->db->transStatus() === false) throw new \Exception("Gagal menghapus dokumen PO.");

            return redirect()->to('/procurement')->with('success', "Dokumen {$po['po_number']} berhasil dibatalkan dan dihapus secara permanen.");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    private function getAccountByCode(string $accountCode): ?array
    {
        return $this->db->table('chart_of_accounts')->where('account_code', $accountCode)->get()->getRowArray();
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
        
        $newNumber = 0;
        if ($lastTrx) {
            $parts = explode('-', $lastTrx['transaction_code']);
            $newNumber = (int) end($parts);
        }

        $methodText = $accountCode === $this->accCash ? 'Cash' : 'ATM';
        $trxCode = "TRX-$dateCode-" . str_pad($newNumber + 1, 3, '0', STR_PAD_LEFT);

        $this->db->table('operational_cash')->insert([
            'transaction_code' => $trxCode, 'transaction_date' => $transactionDate,
            'type' => 'Cash Out', 'metode' => $methodText, 'category' => 'Pembayaran PO',
            'amount' => $amount, 'description' => $description, 'pic_name' => session()->get('name') ?? 'System',
            'journal_id' => $journalId, 'status' => 'POSTED'
        ]);
        
        $cashId = $this->db->insertID();
        $this->db->table('journals')->where('id', $journalId)->update(['source_id' => $cashId]);
    }
}