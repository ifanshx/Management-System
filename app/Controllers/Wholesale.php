<?php

namespace App\Controllers;

class Wholesale extends BaseController
{
    private $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        
        // AUTO-PATCH SCHEMA DATABASE
        try { $this->db->query("ALTER TABLE b2b_sales_orders MODIFY COLUMN status ENUM('PENDING','PARTIAL','PAID','RETURNED') DEFAULT 'PENDING'"); } catch (\Exception $e) {}
        try { $this->db->query("ALTER TABLE b2b_sales_orders ADD COLUMN discount DECIMAL(15,2) DEFAULT 0 AFTER total_amount"); } catch (\Exception $e) {}
        try { $this->db->query("ALTER TABLE b2b_sales_orders ADD COLUMN discount_percent INT DEFAULT 0 AFTER discount"); } catch (\Exception $e) {}
        try { $this->db->query("ALTER TABLE b2b_sales_orders ADD COLUMN bonus_notes TEXT NULL AFTER discount_percent"); } catch (\Exception $e) {} // KOLOM BONUS BARU
        try { $this->db->query("ALTER TABLE b2b_sales_order_items ADD COLUMN additional_fee DECIMAL(15,2) DEFAULT 0 AFTER price"); } catch (\Exception $e) {}
        try { $this->db->query("ALTER TABLE b2b_sales_order_items ADD COLUMN additional_note VARCHAR(255) NULL AFTER additional_fee"); } catch (\Exception $e) {}
        try { $this->db->query("ALTER TABLE b2b_sales_order_items ADD COLUMN shipped_qty INT DEFAULT 0 AFTER qty"); } catch (\Exception $e) {}
        try { $this->db->query("ALTER TABLE b2b_sales_returns MODIFY COLUMN refund_type ENUM('REDUCE_RECEIVABLE','CASH_REFUND','CUSTOMER_CREDIT','REPAIR_REPLACE') DEFAULT 'REDUCE_RECEIVABLE'"); } catch (\Exception $e) {}
        
        // Auto Patch Master Data jika modul Warehouse belum sempat diakses
        try { $this->db->query("ALTER TABLE warehouse_inventory ADD COLUMN motor_category VARCHAR(50) DEFAULT 'Universal' AFTER item_type"); } catch (\Exception $e) {}
        
        // Fix null values
        try { $this->db->query("UPDATE b2b_sales_order_items SET shipped_qty = 0 WHERE shipped_qty IS NULL"); } catch (\Exception $e) {}
        try { $this->db->query("UPDATE b2b_sales_orders SET shipping_status = 'PENDING' WHERE shipping_status = 'PRE-ORDER'"); } catch (\Exception $e) {}
        
        // AUTO PATCH UNTUK PRODUKSI & POIN LOYALTY
        try { $this->db->query("ALTER TABLE work_orders ADD COLUMN so_id INT NULL AFTER id"); } catch (\Exception $e) {}
        try { $this->db->query("ALTER TABLE work_orders ADD COLUMN production_notes VARCHAR(255) NULL AFTER planned_qty"); } catch (\Exception $e) {}
        try { $this->db->query("ALTER TABLE b2b_customers ADD COLUMN reward_points INT DEFAULT 0 AFTER address"); } catch (\Exception $e) {}
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

    // Fungsi Helper Dinamis untuk melengkapi data produk PRD (Barang Jadi) maupun MAT (Bahan Baku/Bonus)
    private function enrichItemsWithNames(array &$items) {
        foreach ($items as &$itm) {
            $sku = $itm['fg_sku'];
            if (strpos($sku, 'MAT-') === 0) {
                $inv = $this->db->table('raw_materials')->where('sku_material', $sku)->get()->getRowArray();
                $itm['item_name'] = $inv['material_name'] ?? $sku;
                $itm['motor_category'] = $inv['material_category'] ?? 'Material/Sparepart';
                $itm['item_type'] = $inv['unit'] ?? 'PCS';
                $itm['hpp'] = $inv['hpp'] ?? 0;
            } else {
                $inv = $this->db->table('warehouse_inventory')->where('sku', $sku)->get()->getRowArray();
                $itm['item_name'] = $inv['item_name'] ?? $sku;
                $itm['motor_category'] = $inv['motor_category'] ?? 'Universal';
                $itm['item_type'] = $inv['item_type'] ?? 'Produk';
                $itm['hpp'] = $inv['hpp'] ?? 0;
            }
        }
    }

    public function index(): string
    {
        if (!session()->get('isLoggedIn')) { header("Location: " . base_url('/portal')); exit; }

        $salesOrders = $this->db->table('b2b_sales_orders')
            ->select('b2b_sales_orders.*, b2b_customers.company_name')
            ->join('b2b_customers', 'b2b_customers.id = b2b_sales_orders.customer_id')
            ->orderBy('b2b_sales_orders.id', 'DESC')
            ->get()->getResultArray();

        foreach ($salesOrders as &$so) {
            $items = $this->db->table('b2b_sales_order_items')->where('so_id', $so['id'])->get()->getResultArray();
            $this->enrichItemsWithNames($items);

            foreach ($items as &$item) {
                $returned = $this->db->table('b2b_sales_return_items')->selectSum('qty_return')->where('so_item_id', $item['id'])->get()->getRowArray();
                $item['shipped_qty']    = (int)($item['shipped_qty'] ?? 0);
                $item['unshipped_qty']  = max(0, (int)$item['qty'] - $item['shipped_qty']);
                $item['returned_qty']   = (int)($returned['qty_return'] ?? 0);
                $item['returnable_qty'] = max(0, (int)$item['qty'] - $item['returned_qty']);
            }
            $so['items']   = $items;
            $so['returns'] = $this->db->table('b2b_sales_returns')->where('so_id', $so['id'])->orderBy('id', 'DESC')->get()->getResultArray();
        }

        $customers = $this->db->table('b2b_customers')->orderBy('company_name', 'ASC')->get()->getResultArray();
        $products  = $this->db->table('warehouse_inventory')
                            ->select('sku, item_name, physical_stock, hpp, wholesale_price, item_type, motor_category')
                            ->like('sku', 'PRD-', 'after')->orderBy('motor_category', 'ASC')->orderBy('item_name', 'ASC')->get()->getResultArray();
        
        $rawMaterials = $this->db->table('raw_materials')
                            ->select('sku_material as sku, material_name as item_name, physical_stock, hpp, material_category as motor_category, unit as item_type')
                            ->orderBy('material_name', 'ASC')->get()->getResultArray();

        $company = $this->db->tableExists('company_settings') ? $this->db->table('company_settings')->get()->getRowArray() : [];

        return view('wholesale/index', [
            'title' => 'B2B Wholesale & Piutang', 'salesOrders' => $salesOrders, 
            'customers' => $customers, 'products' => $products, 'rawMaterials' => $rawMaterials, 'company' => $company
        ]);
    }

    private function cleanRupiah(?string $string): float
    {
        if (empty($string)) return 0.0;
        $cleanString = str_replace('.', '', $string);
        $cleanString = str_replace(',', '.', $cleanString);
        return (float) $cleanString;
    }

    private function getReturnedQtyBySoItem(int $soItemId): int
    {
        $row = $this->db->table('b2b_sales_return_items')->selectSum('qty_return')->where('so_item_id', $soItemId)->get()->getRowArray();
        return (int)($row['qty_return'] ?? 0);
    }

   public function store_so()
    {
        try {
            $this->db->transStart();

            $customerId = $this->request->getPost('customer_id');
            $orderType  = $this->request->getPost('order_type') ?? 'READY'; 
            
            // Produk Utama
            $fgSkus     = $this->request->getPost('fg_sku') ?? []; 
            $qtys       = $this->request->getPost('qty') ?? [];     
            $prices     = $this->request->getPost('unit_price') ?? []; 
            $addFees    = $this->request->getPost('additional_fee') ?? [];
            $addNotes   = $this->request->getPost('additional_note') ?? [];
            
            // Produk Bonus (Gratis)
            $bonusSkus  = $this->request->getPost('bonus_sku') ?? [];
            $bonusQtys  = $this->request->getPost('bonus_qty') ?? [];

            // Titipan Produksi / Buffer Stok (TIDAK MASUK INVOICE, LANGSUNG KE SPK)
            $bufferSkus = $this->request->getPost('buffer_sku') ?? [];
            $bufferQtys = $this->request->getPost('buffer_qty') ?? [];
            
            // Menggabungkan Bonus ke dalam pesanan sebagai item dengan harga 0
            for ($i = 0; $i < count($bonusSkus); $i++) {
                if (!empty($bonusSkus[$i]) && (int)$bonusQtys[$i] > 0) {
                    $fgSkus[]   = $bonusSkus[$i];
                    $qtys[]     = $bonusQtys[$i];
                    $prices[]   = 0;
                    $addFees[]  = 0;
                    $addNotes[] = 'BONUS GRATIS';
                }
            }

            $dpAmount    = $this->cleanRupiah($this->request->getPost('dp_amount'));
            $discPercent = (int)$this->request->getPost('discount_percent');
            $dueDate     = $this->request->getPost('due_date');

            if ($discPercent < 0) $discPercent = 0; if ($discPercent > 100) $discPercent = 100;
            if (empty($fgSkus) || count($fgSkus) === 0) throw new \Exception("Anda harus memilih minimal 1 produk utama.");

            $totalAmount  = 0; $totalHppCost = 0; $validItems   = [];

            for ($i = 0; $i < count($fgSkus); $i++) {
                if (!empty($fgSkus[$i])) {
                    $sku     = $fgSkus[$i]; $qty = (int)$qtys[$i]; 
                    $price   = isset($prices[$i]) ? $this->cleanRupiah((string)$prices[$i]) : 0;
                    $addFee  = isset($addFees[$i]) ? $this->cleanRupiah((string)$addFees[$i]) : 0;
                    $addNote = $addNotes[$i] ?? '';

                    if ($qty <= 0) continue;

                    $isMat = (strpos($sku, 'MAT-') === 0);
                    $table = $isMat ? 'raw_materials' : 'warehouse_inventory';
                    $skuCol = $isMat ? 'sku_material' : 'sku';

                    $stock = $this->db->table($table)->where($skuCol, $sku)->get()->getRowArray();
                    
                    if ($orderType === 'READY') {
                        if (!$stock || $stock['physical_stock'] < $qty) throw new \Exception("GAGAL! Pesanan Ready Stock, sisa {$sku} di gudang tidak mencukupi.");
                        $totalHppCost += ($qty * ($stock['hpp'] ?? 0));
                    }

                    $subtotal = $qty * ($price + $addFee);
                    $totalAmount += $subtotal;
                    
                    $validItems[] = [
                        'sku' => $sku, 'qty' => $qty, 'price' => $price, 'is_mat' => $isMat, 'table' => $table, 'sku_col' => $skuCol,
                        'additional_fee' => $addFee, 'additional_note' => $addNote, 'subtotal' => $subtotal
                    ];
                }
            }

            if (count($validItems) === 0) throw new \Exception("Data produk tidak valid.");
            
            $discAmount = $totalAmount * ($discPercent / 100);
            $grandTotal = $totalAmount - $discAmount;
            if ($grandTotal < 0) $grandTotal = 0;
            if ($dpAmount > $grandTotal) throw new \Exception("DP melebihi Grand Total (setelah diskon).");

            $status      = ($dpAmount >= $grandTotal) ? 'PAID' : ($dpAmount > 0 ? 'PARTIAL' : 'PENDING');
            $soNumber    = "SO-" . date('Ymd') . "-" . rand(1000, 9999);
            $orderStatus = ($orderType === 'READY') ? 'SHIPPED' : 'PENDING'; 

            $this->db->table('b2b_sales_orders')->insert([
                'so_number' => $soNumber, 'customer_id' => $customerId, 'order_date' => date('Y-m-d'),
                'due_date' => $dueDate, 'total_amount' => $grandTotal, 'discount' => $discAmount,
                'discount_percent' => $discPercent, 'paid_amount' => $dpAmount, 'status' => $status,
                'shipping_status' => $orderStatus
            ]);
            $soId = $this->db->insertID();

            $pointsEarned = floor($grandTotal / 100000);
            if ($pointsEarned > 0) {
                $this->db->query("UPDATE b2b_customers SET reward_points = reward_points + ? WHERE id = ?", [$pointsEarned, $customerId]);
            }

            $autoSpkCreated = 0; $missingBomSkus = [];

            foreach ($validItems as $item) {
                $shippedQty = ($orderType === 'READY') ? $item['qty'] : 0;
                
                $this->db->table('b2b_sales_order_items')->insert([
                    'so_id' => $soId, 'fg_sku' => $item['sku'], 'qty' => $item['qty'],
                    'shipped_qty' => $shippedQty, 'price' => $item['price'], 'additional_fee' => $item['additional_fee'],
                    'additional_note' => $item['additional_note'], 'subtotal' => $item['subtotal']
                ]);
                
                if ($orderType === 'READY') {
                    $this->db->query("UPDATE {$item['table']} SET physical_stock = physical_stock - ? WHERE {$item['sku_col']} = ?", [$item['qty'], $item['sku']]);
                } 
                else if ($orderType === 'PREORDER' && !$item['is_mat']) {
                    // SPK hanya dicetak untuk Produk PRD, bukan Material Bonus
                    $bom = $this->db->table('bom_headers')->where('fg_sku', $item['sku'])->orderBy('id', 'DESC')->get()->getRowArray();
                    if ($bom) {
                        $dateStr = date('Ymd');
                        $lastSpk = $this->db->table('work_orders')->like('spk_number', "SPK-$dateStr", 'after')->orderBy('id', 'DESC')->get()->getRowArray();
                        
                        $seq = 1;
                        if ($lastSpk) {
                            $spkParts = explode('-', $lastSpk['spk_number']);
                            $seq = intval(end($spkParts)) + 1;
                        }
                        
                        $spkNumber = "SPK-" . $dateStr . "-" . str_pad($seq, 3, '0', STR_PAD_LEFT);

                        $this->db->table('work_orders')->insert([
                            'so_id'            => $soId,
                            'spk_number'       => $spkNumber,
                            'bom_id'           => $bom['id'],
                            'planned_qty'      => $item['qty'], 
                            'production_notes' => $item['additional_note'],
                            'status'           => 'IN_PROGRESS',
                            'start_date'       => date('Y-m-d'),
                            'source'           => 'PREORDER'
                        ]);
                        $autoSpkCreated++;
                    } else {
                        $missingBomSkus[] = $item['sku'];
                    }
                }
            }

            // PROSES TITIPAN PRODUKSI / BUFFER STOK
            $bufferSpkCreated = 0;
            for ($i = 0; $i < count($bufferSkus); $i++) {
                if (!empty($bufferSkus[$i]) && (int)$bufferQtys[$i] > 0) {
                    $sku = $bufferSkus[$i];
                    $qty = (int)$bufferQtys[$i];
                    
                    $bom = $this->db->table('bom_headers')->where('fg_sku', $sku)->orderBy('id', 'DESC')->get()->getRowArray();
                    if ($bom) {
                        $dateStr = date('Ymd');
                        $lastSpk = $this->db->table('work_orders')->like('spk_number', "SPK-$dateStr", 'after')->orderBy('id', 'DESC')->get()->getRowArray();
                        $seq = 1;
                        if ($lastSpk) {
                            $spkParts = explode('-', $lastSpk['spk_number']);
                            $seq = intval(end($spkParts)) + 1;
                        }
                        $spkNumber = "SPK-" . $dateStr . "-" . str_pad($seq, 3, '0', STR_PAD_LEFT);

                        $this->db->table('work_orders')->insert([
                            'so_id'            => null, // Null agar tidak nyangkut di riwayat SO
                            'spk_number'       => $spkNumber,
                            'bom_id'           => $bom['id'],
                            'planned_qty'      => $qty, 
                            'production_notes' => "BUFFER STOK (Dititipkan via $soNumber)",
                            'status'           => 'IN_PROGRESS',
                            'start_date'       => date('Y-m-d'),
                            'source'           => 'MANUAL' // Treat as manual SPK (Reguler)
                        ]);
                        $bufferSpkCreated++;
                    }
                }
            }

            // Jurnal B2B
            $piutangAcc = $this->db->table('chart_of_accounts')->where('account_code', '1-4000')->get()->getRowArray();
            if (!$piutangAcc) {
                $this->db->table('chart_of_accounts')->insert(['account_code'=>'1-4000', 'account_name'=>'Piutang Usaha (B2B)', 'account_type'=>'ASET', 'balance'=>0]);
                $piutangAcc = $this->db->table('chart_of_accounts')->where('account_code', '1-4000')->get()->getRowArray();
            }
            $kas    = $this->db->table('chart_of_accounts')->where('account_code', '1-2000')->get()->getRowArray(); 
            $rev    = $this->db->table('chart_of_accounts')->where('account_code', '4-1000')->get()->getRowArray();
            $invAcc = $this->db->table('chart_of_accounts')->where('account_code', '1-3000')->get()->getRowArray(); 
            $hppAcc = $this->db->table('chart_of_accounts')->where('account_code', '5-1000')->get()->getRowArray();

            $this->db->table('journals')->insert([
                'journal_number'   => 'JRN-B2B-'.time(), 'transaction_date' => date('Y-m-d'),
                'description'      => "Penjualan Grosir B2B: $soNumber",
                'total_amount'     => $grandTotal, 'created_by' => session()->get('name') ?? 'System'
            ]);
            $journalId = $this->db->insertID();

            if ($rev && $kas && $piutangAcc) {
                $sisaPiutang = $grandTotal - $dpAmount;
                $this->db->table('journal_items')->insert(['journal_id' => $journalId, 'account_id' => $rev['id'], 'debit' => 0, 'credit' => $grandTotal, 'line_description' => 'Pendapatan B2B']);
                if ($dpAmount > 0) {
                    $this->db->table('journal_items')->insert(['journal_id' => $journalId, 'account_id' => $kas['id'], 'debit' => $dpAmount, 'credit' => 0, 'line_description' => 'DP Kas B2B']);
                    $dateCode  = date('Ymd');
                    $lastTrx   = $this->db->table('operational_cash')->like('transaction_code', "TRX-$dateCode-", 'after')->orderBy('id', 'DESC')->get()->getRowArray();
                    $newNumber = $lastTrx ? str_pad((int) substr($lastTrx['transaction_code'], -3) + 1, 3, '0', STR_PAD_LEFT) : '001';
                    $this->db->table('operational_cash')->insert([
                        'transaction_code' => "TRX-$dateCode-$newNumber", 'transaction_date' => date('Y-m-d'),
                        'type' => 'Cash In', 'metode' => 'ATM', 'category' => 'Uang Muka B2B',
                        'amount' => $dpAmount, 'description' => "Uang Muka (DP) SO: $soNumber", 'pic_name' => session()->get('name') ?? 'Sistem'
                    ]);
                }
                if ($sisaPiutang > 0) $this->db->table('journal_items')->insert(['journal_id' => $journalId, 'account_id' => $piutangAcc['id'], 'debit' => $sisaPiutang, 'credit' => 0, 'line_description' => 'Piutang B2B']);
            }

            if ($orderType === 'READY' && $totalHppCost > 0 && $invAcc && $hppAcc) {
                $this->db->table('journal_items')->insert(['journal_id' => $journalId, 'account_id' => $hppAcc['id'], 'debit' => $totalHppCost, 'credit' => 0, 'line_description' => 'HPP B2B']);
                $this->db->table('journal_items')->insert(['journal_id' => $journalId, 'account_id' => $invAcc['id'], 'debit' => 0, 'credit' => $totalHppCost, 'line_description' => 'Persediaan Keluar']);
            }

            $this->db->transComplete();
            if ($this->db->transStatus() === false) throw new \Exception("Gagal menyimpan ke database.");

            $pesan = "Pesanan Grosir berhasil diterbitkan. <br>🎁 <b>Dapat $pointsEarned Poin Hadiah/THR.</b>";
            if ($orderType === 'PREORDER') {
                if ($autoSpkCreated > 0) $pesan .= "<br><br><span style='color:#10b981;'><i class='ph-fill ph-check-circle'></i> <b>Berhasil membuat $autoSpkCreated SPK Pabrik otomatis.</b></span>";
                if ($bufferSpkCreated > 0) $pesan .= "<br><span style='color:#3b82f6;'><i class='ph-fill ph-factory'></i> <b>Ditambah $bufferSpkCreated SPK Titipan (Stok Gudang).</b></span>";
                if (count($missingBomSkus) > 0) {
                    $skus = implode(', ', $missingBomSkus);
                    $pesan .= "<br><br><span style='color:#ef4444;'><i class='ph-fill ph-warning-circle'></i> <b>Peringatan:</b> Produk [$skus] belum memiliki Resep BOM. Harap buat SPK manual.</span>";
                }
            }
            return redirect()->back()->with('success', $pesan);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

public function export_excel(string $id)
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/portal');
        
        $so = $this->db->table('b2b_sales_orders')
            ->select('b2b_sales_orders.*, b2b_customers.company_name, b2b_customers.address, b2b_customers.phone')
            ->join('b2b_customers', 'b2b_customers.id = b2b_sales_orders.customer_id')
            ->where('b2b_sales_orders.id', $id)
            ->get()->getRowArray();

        if (!$so) return redirect()->back()->with('error', 'SO tidak ditemukan.');

        $items = $this->db->table('b2b_sales_order_items')->where('so_id', $id)->get()->getResultArray();
        $this->enrichItemsWithNames($items);

        $fileName = "Invoice_Noric_B2B_" . $so['so_number'] . ".xls";
        header("Content-Type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=\"$fileName\"");
        header("Pragma: no-cache");
        header("Expires: 0");

        echo "<table border='1' style='font-family: sans-serif; border-collapse: collapse;'>";
        echo "<tr><th colspan='9' style='background-color:#10b981; color:#ffffff; font-size:16px; padding:10px;'>INVOICE GROSIR B2B - {$so['so_number']}</th></tr>";
        echo "<tr><td colspan='9'><strong>Toko/Mitra:</strong> {$so['company_name']}</td></tr>";
        echo "<tr><td colspan='9'><strong>Telepon:</strong> {$so['phone']}</td></tr>";
        echo "<tr><td colspan='9'><strong>Tanggal Transaksi:</strong> " . date('d F Y', strtotime($so['order_date'])) . "</td></tr>";
        
        echo "<tr style='background-color:#e2e8f0;'>
                <th>No</th>
                <th>Kode SKU</th>
                <th>Kategori/Sifat</th>
                <th>Tipe Fisik</th>
                <th>Nama Produk / Barang</th>
                <th>Catatan Kustom</th>
                <th>Qty</th>
                <th>Harga Satuan (Rp)</th>
                <th>Subtotal (Rp)</th>
              </tr>";
        
       // PENGELOMPOKAN BARANG UNTUK EXCEL BERDASARKAN DATABASE (item_type)
        $catSilencer = [];
        $catLeheran  = [];
        $catFullsystem = [];
        $catBonus    = [];
        $catLainnya  = [];

        foreach ($items as $itm) {
            $p = $itm['price'] + $itm['additional_fee'];
            $note = strtoupper($itm['additional_note'] ?? '');
            $itemType = strtoupper($itm['item_type'] ?? '');
            $name = strtoupper($itm['item_name']);
            $sku = strtoupper($itm['fg_sku']);

            if ($p == 0 && strpos($note, 'BONUS') !== false) {
                $catBonus[] = $itm;
            // LOGIKA BARU: Pastikan nama SLINCER/LEHER ditarik duluan agar tidak nyangkut di -FSY-
            } elseif (strpos($itemType, 'SILENCER') !== false || strpos($itemType, 'SLIP-ON') !== false || strpos($name, 'SLINCER') !== false || strpos($sku, '-SLC-') !== false) {
                $catSilencer[] = $itm;
            } elseif (strpos($itemType, 'LEHERAN') !== false || strpos($itemType, 'HEADER') !== false || strpos($name, 'LEHER') !== false || strpos($sku, '-LHR-') !== false) {
                $catLeheran[] = $itm;
            } elseif (strpos($itemType, 'FULLSYSTEM') !== false || strpos($sku, '-FSY-') !== false) {
                $catFullsystem[] = $itm;
            } else {
                $catLainnya[] = $itm;
            }
        }
        
        $no = 1;

        // Fungsi Bantuan Render Baris Excel
        $renderRows = function($title, $arrayItems, $bgColor, &$no) {
            if (empty($arrayItems)) return;
            echo "<tr><td colspan='9' style='background-color:{$bgColor}; font-weight:900; font-size:13px; text-align:center;'>--- {$title} ---</td></tr>";
            foreach ($arrayItems as $itm) {
                $p = $itm['price'] + $itm['additional_fee'];
                echo "<tr>";
                echo "<td align='center'>".$no++."</td>";
                echo "<td>{$itm['fg_sku']}</td>";
                echo "<td align='center'>{$itm['motor_category']}</td>";
                echo "<td align='center'>{$itm['item_type']}</td>";
                echo "<td>{$itm['item_name']}</td>";
                echo "<td>{$itm['additional_note']}</td>";
                echo "<td align='center'>{$itm['qty']}</td>";
                echo "<td align='right'>".number_format($p, 0, ',', '.')."</td>";
                echo "<td align='right'>".number_format($itm['subtotal'], 0, ',', '.')."</td>";
                echo "</tr>";
            }
        };

        // Cetak Grup secara Berurutan
        $renderRows("KATEGORI: FULLSYSTEM / PAKET LENGKAP", $catFullsystem, '#fef08a', $no);
        $renderRows("KATEGORI: SILENCER", $catSilencer, '#dbeafe', $no);
        $renderRows("KATEGORI: LEHERAN", $catLeheran, '#fce7f3', $no);
        $renderRows("KATEGORI: LAIN-LAIN / SPAREPART", $catLainnya, '#f3f4f6', $no);
        $renderRows("BARANG BONUS / GRATIS", $catBonus, '#fef3c7', $no);
        
        // FOOTER TOTALAN
        echo "<tr><td colspan='8' align='right'><strong>Total Kotor</strong></td><td align='right'><strong>".number_format($so['total_amount'] + $so['discount'], 0, ',', '.')."</strong></td></tr>";
        echo "<tr><td colspan='8' align='right'><strong>Diskon Transaksi ({$so['discount_percent']}%)</strong></td><td align='right'><strong>-".number_format($so['discount'], 0, ',', '.')."</strong></td></tr>";
        echo "<tr><td colspan='8' align='right'><strong>GRAND TOTAL TAGIHAN</strong></td><td align='right' style='background-color:#fef08a;'><strong>".number_format($so['total_amount'], 0, ',', '.')."</strong></td></tr>";
        echo "<tr><td colspan='8' align='right'><strong>Telah Dibayar (Deposit)</strong></td><td align='right' style='background-color:#bbf7d0;'><strong>".number_format($so['paid_amount'], 0, ',', '.')."</strong></td></tr>";
        echo "<tr><td colspan='8' align='right'><strong>Sisa Piutang Berjalan</strong></td><td align='right' style='background-color:#fecaca; color:#ef4444;'><strong>".number_format($so['total_amount'] - $so['paid_amount'], 0, ',', '.')."</strong></td></tr>";
        echo "</table>";
        exit;
    }

    public function pay_installment(string $id)
    {
        try {
            $amount = $this->cleanRupiah($this->request->getPost('amount'));
            if ($amount <= 0) throw new \Exception("Nominal bayaran cicilan tidak sah atau Kosong.");

            $this->db->transStart();

            $so = $this->db->table('b2b_sales_orders')->where('id', $id)->get()->getRowArray();
            if (!$so) throw new \Exception("Pesanan tidak dijumpai.");

            $sisaPiutang = $so['total_amount'] - $so['paid_amount'];
            if ($amount > $sisaPiutang) {
                throw new \Exception("Bayaran (Rp ".number_format($amount,0,',','.').") melebihi sisa piutang.");
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

            $kas        = $this->db->table('chart_of_accounts')->where('account_code', '1-2000')->get()->getRowArray(); 
            $piutangAcc = $this->db->table('chart_of_accounts')->where('account_code', '1-4000')->get()->getRowArray(); 

            if ($kas && $piutangAcc) {
                $this->db->table('journal_items')->insert(['journal_id' => $journalId, 'account_id' => $kas['id'], 'debit' => $amount, 'credit' => 0, 'line_description' => 'Kas Masuk']); 
                $this->db->table('journal_items')->insert(['journal_id' => $journalId, 'account_id' => $piutangAcc['id'], 'debit' => 0, 'credit' => $amount, 'line_description' => 'Pembayaran Piutang']); 
            }

            $dateCode  = date('Ymd');
            $lastTrx   = $this->db->table('operational_cash')->like('transaction_code', "TRX-$dateCode-", 'after')->orderBy('id', 'DESC')->get()->getRowArray();
            $newNumber = $lastTrx ? str_pad((int) substr($lastTrx['transaction_code'], -3) + 1, 3, '0', STR_PAD_LEFT) : '001';
            
            $this->db->table('operational_cash')->insert([
                'transaction_code' => "TRX-$dateCode-$newNumber", 'transaction_date' => date('Y-m-d'),
                'type' => 'Cash In', 'metode' => 'ATM', 'category' => 'Angsuran / Pelunasan B2B',
                'amount' => $amount, 'description' => "Pelunasan SO: " . $so['so_number'],
                'pic_name' => session()->get('name') ?? 'Sistem'
            ]);

            $this->db->transComplete();
            if ($this->db->transStatus() === false) throw new \Exception("Gagal mencatat pembayaran.");

            return redirect()->back()->with('success', 'Pembayaran angsuran berhasil dicatat!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function process_shipment(string $id)
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/portal');

        try {
            $this->db->transStart();
            
            $so = $this->db->table('b2b_sales_orders')->where('id', $id)->get()->getRowArray();
            if (!$so || $so['shipping_status'] === 'SHIPPED') throw new \Exception("Pesanan tidak valid atau semua barang sudah terkirim 100%.");

            $soItemIds = $this->request->getPost('so_item_id');
            $shipQtys  = $this->request->getPost('ship_qty');

            if (empty($soItemIds) || empty($shipQtys)) throw new \Exception("Tidak ada item yang dipilih untuk dikirim.");

            $totalHppCost = 0;
            $hasShippedAnything = false;

            foreach ($soItemIds as $index => $itemId) {
                $shipQty = (int)($shipQtys[$index] ?? 0);
                if ($shipQty <= 0) continue;

                $hasShippedAnything = true;

                $item = $this->db->table('b2b_sales_order_items')->where('id', $itemId)->get()->getRowArray();
                if (!$item || (int)$item['so_id'] !== (int)$id) continue;

                $unshipped = (int)$item['qty'] - (int)$item['shipped_qty'];
                if ($shipQty > $unshipped) throw new \Exception("Kuantitas kirim melebihi batas sisa barang yang belum dikirim.");

                $isMat = (strpos($item['fg_sku'], 'MAT-') === 0);
                $table = $isMat ? 'raw_materials' : 'warehouse_inventory';
                $skuCol = $isMat ? 'sku_material' : 'sku';

                $stock = $this->db->table($table)->where($skuCol, $item['fg_sku'])->get()->getRowArray();
                if (!$stock || $stock['physical_stock'] < $shipQty) {
                    throw new \Exception("Stok Gudang fisik untuk barang {$item['fg_sku']} kurang (Tersedia: " . ($stock['physical_stock'] ?? 0) . "). Tidak bisa mengirim!");
                }

                $this->db->query("UPDATE {$table} SET physical_stock = physical_stock - ? WHERE {$skuCol} = ?", [$shipQty, $item['fg_sku']]);
                $this->db->query("UPDATE b2b_sales_order_items SET shipped_qty = shipped_qty + ? WHERE id = ?", [$shipQty, $itemId]);

                $totalHppCost += ($shipQty * ($stock['hpp'] ?? 0));
            }

            if (!$hasShippedAnything) throw new \Exception("Anda belum memasukkan kuantitas pengiriman apa pun.");

            $allItems = $this->db->table('b2b_sales_order_items')->where('so_id', $id)->get()->getResultArray();
            $allShipped = true;
            foreach ($allItems as $i) {
                if ((int)$i['shipped_qty'] < (int)$i['qty']) {
                    $allShipped = false;
                    break;
                }
            }

            $newShipStatus = $allShipped ? 'SHIPPED' : 'PARTIAL-SHIPPED';
            $this->db->table('b2b_sales_orders')->where('id', $id)->update(['shipping_status' => $newShipStatus]);

            if ($totalHppCost > 0) {
                $this->db->table('journals')->insert([
                    'journal_number'   => 'JRN-SHP-'.time(),
                    'transaction_date' => date('Y-m-d'),
                    'description'      => "Pengiriman Parsial/Full PO: " . $so['so_number'],
                    'total_amount'     => $totalHppCost,
                    'created_by'       => session()->get('name') ?? 'System'
                ]);
                $journalId = $this->db->insertID();
                
                $invAcc = $this->db->table('chart_of_accounts')->where('account_code', '1-3000')->get()->getRowArray(); 
                $hppAcc = $this->db->table('chart_of_accounts')->where('account_code', '5-1000')->get()->getRowArray();

                if ($invAcc && $hppAcc) {
                    $this->db->table('journal_items')->insert(['journal_id' => $journalId, 'account_id' => $hppAcc['id'], 'debit' => $totalHppCost, 'credit' => 0, 'line_description' => 'HPP Barang Keluar']);
                    $this->db->table('journal_items')->insert(['journal_id' => $journalId, 'account_id' => $invAcc['id'], 'debit' => 0, 'credit' => $totalHppCost, 'line_description' => 'Persediaan Keluar']);
                }
            }
            
            $this->db->transComplete();
            if ($this->db->transStatus() === false) throw new \Exception("Gagal memproses pengiriman ke database.");
            
            $msg = $allShipped ? "Semua barang untuk pesanan ini telah selesai dikirim 100%!" : "Pengiriman Parsial berhasil dicatat dan stok dipotong otomatis.";
            return redirect()->back()->with('success', $msg);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function return_so(string $id)
    {
        try {
            if (!session()->get('isLoggedIn')) return redirect()->to('/portal');
            $this->db->transStart();

            $so = $this->db->table('b2b_sales_orders')->where('id', $id)->get()->getRowArray();
            if (!$so) throw new \Exception("Sales Order tidak ditemukan.");

            $returnDate = $this->request->getPost('return_date') ?: date('Y-m-d');
            $reason     = trim($this->request->getPost('reason') ?? '');
            $refundType = $this->request->getPost('refund_type') ?? 'REDUCE_RECEIVABLE';
            $soItemIds  = $this->request->getPost('so_item_id');
            $qtyReturns = $this->request->getPost('qty_return');

            if (empty($soItemIds) || empty($qtyReturns)) throw new \Exception("Tidak ada item retur yang dikirim.");

            $returnItems    = [];
            $totalReturn    = 0;
            $totalHppReturn = 0;

            foreach ($soItemIds as $index => $soItemId) {
                $qtyReturn = (int)($qtyReturns[$index] ?? 0);
                if ($qtyReturn <= 0) continue;

                $soItem = $this->db->table('b2b_sales_order_items')->where('id', $soItemId)->get()->getRowArray();
                if (!$soItem || (int)$soItem['so_id'] !== (int)$id) continue;

                $alreadyReturned = $this->getReturnedQtyBySoItem($soItemId);
                $maxReturnable   = (int)$soItem['qty'] - $alreadyReturned;

                if ($qtyReturn > $maxReturnable) throw new \Exception("Qty retur melebihi batas yang diizinkan.");

                $addFee   = (float)($soItem['additional_fee'] ?? 0);
                $subtotal = $qtyReturn * ((float)$soItem['price'] + $addFee);
                
                if ((float)$so['discount_percent'] > 0) {
                    $subtotal = $subtotal - ($subtotal * ((float)$so['discount_percent'] / 100));
                }

                $totalReturn += $subtotal;

                $isMat = (strpos($soItem['fg_sku'], 'MAT-') === 0);
                $table = $isMat ? 'raw_materials' : 'warehouse_inventory';
                $skuCol = $isMat ? 'sku_material' : 'sku';

                $stock = $this->db->table($table)->where($skuCol, $soItem['fg_sku'])->get()->getRowArray();
                $hpp   = (float)($stock['hpp'] ?? 0);
                
                $shippedQty = (int)$soItem['shipped_qty'];
                $hppToReverse = min($qtyReturn, max(0, $shippedQty - $alreadyReturned));
                
                if ($hppToReverse > 0) {
                    $totalHppReturn += ($hppToReverse * $hpp);
                    $this->db->query("UPDATE {$table} SET physical_stock = physical_stock + ? WHERE {$skuCol} = ?", [$hppToReverse, $soItem['fg_sku']]);
                    
                    if ($refundType === 'REPAIR_REPLACE') {
                        $this->db->query("UPDATE b2b_sales_order_items SET shipped_qty = shipped_qty - ? WHERE id = ?", [$hppToReverse, $soItemId]);
                    }
                }

                $returnItems[] = [
                    'so_item_id' => $soItem['id'],
                    'fg_sku'     => $soItem['fg_sku'],
                    'qty_return' => $qtyReturn,
                    'price'      => ((float)$soItem['price'] + $addFee),
                    'subtotal'   => $subtotal,
                    'hpp'        => $hpp
                ];
            }

            if (count($returnItems) === 0) throw new \Exception("Kuantitas retur belum diisi valid.");

            $dateCode     = date('Ymd');
            $lastReturn   = $this->db->table('b2b_sales_returns')->like('return_number', "RET-$dateCode-", 'after')->orderBy('id', 'DESC')->get()->getRowArray();
            $newNumber    = $lastReturn ? str_pad((int)substr($lastReturn['return_number'], -3) + 1, 3, '0', STR_PAD_LEFT) : '001';
            $returnNumber = "RET-$dateCode-$newNumber";

            $this->db->table('b2b_sales_returns')->insert([
                'return_number' => $returnNumber, 'so_id' => $id, 'return_date' => $returnDate,
                'reason' => $reason, 'total_return' => ($refundType === 'REPAIR_REPLACE' ? 0 : $totalReturn),
                'refund_type' => $refundType,
                'status' => 'POSTED', 'created_by' => session()->get('name') ?? 'System'
            ]);
            $salesReturnId = $this->db->insertID();

            foreach ($returnItems as $item) {
                $this->db->table('b2b_sales_return_items')->insert([
                    'sales_return_id' => $salesReturnId, 'so_item_id' => $item['so_item_id'],
                    'fg_sku' => $item['fg_sku'], 'qty_return' => $item['qty_return'],
                    'price' => $item['price'], 'subtotal' => ($refundType === 'REPAIR_REPLACE' ? 0 : $item['subtotal'])
                ]);
            }

            // LOGIKA TARIK POIN KARENA RETUR (Batal Beli)
            if ($refundType !== 'REPAIR_REPLACE' && $totalReturn > 0) {
                $pointsDeducted = floor($totalReturn / 100000);
                if ($pointsDeducted > 0) {
                    $this->db->query("UPDATE b2b_customers SET reward_points = GREATEST(0, reward_points - ?) WHERE id = ?", [$pointsDeducted, $so['customer_id']]);
                }
            }

            $invAcc     = $this->db->table('chart_of_accounts')->where('account_code', '1-3000')->get()->getRowArray();
            $hppAcc     = $this->db->table('chart_of_accounts')->where('account_code', '5-1000')->get()->getRowArray();

            if ($refundType === 'REPAIR_REPLACE') {
                $this->db->table('b2b_sales_orders')->where('id', $id)->update(['shipping_status' => 'PARTIAL-SHIPPED']);

                if ($totalHppReturn > 0 && $invAcc && $hppAcc) {
                    $this->db->table('journals')->insert(['journal_number' => 'JRN-RMA-'.time(), 'transaction_date' => $returnDate, 'description' => "Retur Perbaikan Garansi SO: {$so['so_number']}", 'total_amount' => $totalHppReturn, 'created_by' => session()->get('name') ?? 'System']);
                    $journalId = $this->db->insertID();
                    $this->db->table('journal_items')->insert(['journal_id' => $journalId, 'account_id' => $invAcc['id'], 'line_description' => 'Persediaan Masuk (Perbaikan)', 'debit' => $totalHppReturn, 'credit' => 0]);
                    $this->db->table('journal_items')->insert(['journal_id' => $journalId, 'account_id' => $hppAcc['id'], 'line_description' => 'Pembalik HPP', 'debit' => 0, 'credit' => $totalHppReturn]);
                }

            } else {
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
                if ($newTotalAmount <= 0) $newStatus = 'RETURNED';
                elseif ($newPaidAmount >= $newTotalAmount) $newStatus = 'PAID';
                elseif ($newPaidAmount > 0) $newStatus = 'PARTIAL';

                $this->db->table('b2b_sales_orders')->where('id', $id)->update(['total_amount' => $newTotalAmount, 'paid_amount' => $newPaidAmount, 'status' => $newStatus]);

                $returAcc   = $this->db->table('chart_of_accounts')->where('account_code', '4-1100')->get()->getRowArray();
                $piutangAcc = $this->db->table('chart_of_accounts')->where('account_code', '1-4000')->get()->getRowArray();
                $bankAcc    = $this->db->table('chart_of_accounts')->where('account_code', '1-2000')->get()->getRowArray();

                $this->db->table('journals')->insert(['journal_number' => 'JRN-RET-'.time(), 'transaction_date' => $returnDate, 'description' => "Retur Uang SO: {$so['so_number']} / {$returnNumber}", 'total_amount' => $totalReturn, 'created_by' => session()->get('name') ?? 'System']);
                $journalId = $this->db->insertID();

                if ($returAcc) {
                    $this->db->table('journal_items')->insert(['journal_id' => $journalId, 'account_id' => $returAcc['id'], 'line_description' => 'Retur Penjualan', 'debit' => $totalReturn, 'credit' => 0]);
                    if ($refundType === 'CASH_REFUND' && $bankAcc) {
                        $this->db->table('journal_items')->insert(['journal_id' => $journalId, 'account_id' => $bankAcc['id'], 'line_description' => 'Refund Kas', 'debit' => 0, 'credit' => $totalReturn]);
                    } elseif ($piutangAcc) {
                        $this->db->table('journal_items')->insert(['journal_id' => $journalId, 'account_id' => $piutangAcc['id'], 'line_description' => 'Pengurang Piutang', 'debit' => 0, 'credit' => $totalReturn]);
                    }
                }

                if ($totalHppReturn > 0 && $invAcc && $hppAcc) {
                    $this->db->table('journal_items')->insert(['journal_id' => $journalId, 'account_id' => $invAcc['id'], 'line_description' => 'Persediaan Masuk (Retur)', 'debit' => $totalHppReturn, 'credit' => 0]);
                    $this->db->table('journal_items')->insert(['journal_id' => $journalId, 'account_id' => $hppAcc['id'], 'line_description' => 'Pembalik HPP', 'debit' => 0, 'credit' => $totalHppReturn]);
                }
            }

            $this->db->transComplete();
            if ($this->db->transStatus() === false) throw new \Exception("Gagal memproses retur penjualan.");
            
            $msg = ($refundType === 'REPAIR_REPLACE') ? "Retur Garansi/Perbaikan berhasil! Silakan kirim ulang barangnya nanti via tombol Kirim." : "Retur parsial & Refund berhasil diproses. Poin mitra dipotong.";
            return redirect()->back()->with('success', $msg);

        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function get_customer(string $id)
    {
        if ($this->request->isAJAX()) {
            $customer = $this->db->table('b2b_customers')->where('id', $id)->get()->getRowArray();
            return $this->response->setJSON($customer);
        }
    }

    public function store_customer()
    {
        try {
            $phone = trim($this->request->getPost('phone'));
            $normalizedPhone = $this->normalizePhone($phone);

            if ($phone !== '' && $normalizedPhone === '') {
                throw new \Exception("Nomor WhatsApp tidak valid. Gunakan format seperti <b>08123456789</b>.");
            }

            $data = [
                'company_name' => $this->request->getPost('company_name'),
                'contact_name' => $this->request->getPost('contact_name'),
                'phone'        => $normalizedPhone,
                'address'      => $this->request->getPost('address'),
            ];

            $this->db->table('b2b_customers')->insert($data);
            return redirect()->back()->with('success', 'Data Mitra Reseller berhasil ditambahkan.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menambah mitra: ' . $e->getMessage());
        }
    }

    public function update_customer(string $id)
    {
        try {
            $phone = trim($this->request->getPost('phone'));
            $normalizedPhone = $this->normalizePhone($phone);

            if ($phone !== '' && $normalizedPhone === '') {
                throw new \Exception("Nomor WhatsApp tidak valid. Gunakan format seperti <b>08123456789</b>.");
            }

            $data = [
                'company_name' => $this->request->getPost('company_name'),
                'contact_name' => $this->request->getPost('contact_name'),
                'phone'        => $normalizedPhone,
                'address'      => $this->request->getPost('address'),
            ];
            $this->db->table('b2b_customers')->where('id', $id)->update($data);
            return redirect()->back()->with('success', 'Data Mitra Reseller berhasil diperbarui.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal mengedit mitra: ' . $e->getMessage());
        }
    }

    public function delete_customer(string $id)
    {
        try {
            $check = $this->db->table('b2b_sales_orders')->where('customer_id', $id)->countAllResults();
            if ($check > 0) {
                return redirect()->back()->with('error', 'Gagal Dihapus! Mitra ini memiliki riwayat transaksi/pesanan.');
            }

            $this->db->table('b2b_customers')->where('id', $id)->delete();
            return redirect()->back()->with('success', 'Data Mitra berhasil dihapus dari sistem.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus mitra: ' . $e->getMessage());
        }
    }

    public function redeem_points(string $id)
    {
        try {
            $pointsToRedeem = (int) $this->request->getPost('points');
            if ($pointsToRedeem <= 0) throw new \Exception("Jumlah poin yang ditukar tidak valid.");

            $customer = $this->db->table('b2b_customers')->where('id', $id)->get()->getRowArray();
            if (!$customer) throw new \Exception("Mitra tidak ditemukan.");

            if ($pointsToRedeem > (int)$customer['reward_points']) {
                throw new \Exception("Poin tidak cukup. Sisa poin mitra: {$customer['reward_points']} Pts.");
            }

            $this->db->query("UPDATE b2b_customers SET reward_points = reward_points - ? WHERE id = ?", [$pointsToRedeem, $id]);
            
            return redirect()->back()->with('success', "Berhasil menukar <b>{$pointsToRedeem} Poin</b> dari toko {$customer['company_name']} untuk pencairan THR/Hadiah.");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function surat_jalan(string $id)
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/portal');

        $so = $this->db->table('b2b_sales_orders')
            ->select('b2b_sales_orders.*, b2b_customers.company_name, b2b_customers.address, b2b_customers.phone, b2b_customers.contact_name')
            ->join('b2b_customers', 'b2b_customers.id = b2b_sales_orders.customer_id')
            ->where('b2b_sales_orders.id', $id)
            ->get()->getRowArray();

        if (!$so) return redirect()->back()->with('error', 'Dokumen Sales Order tidak ditemukan.');

        $items = $this->db->table('b2b_sales_order_items')->where('so_id', $id)->get()->getResultArray();
        $this->enrichItemsWithNames($items);

        $company = [];
        if($this->db->tableExists('company_settings')) {
            $company = $this->db->table('company_settings')->get()->getRowArray() ?? [];
        }

        $data = [
            'title'   => 'Surat Jalan - ' . $so['so_number'],
            'so'      => $so,
            'items'   => $items,
            'company' => $company
        ];

        return view('wholesale/surat_jalan', $data);
    }

    public function delete_so(string $id)
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/portal');

        try {
            $this->db->transStart();

            $so = $this->db->table('b2b_sales_orders')->where('id', $id)->get()->getRowArray();
            if (!$so) throw new \Exception("Dokumen Sales Order tidak ditemukan.");

            $soNumber = $so['so_number'];

            $items = $this->db->table('b2b_sales_order_items')->where('so_id', $id)->get()->getResultArray();
            foreach ($items as $item) {
                if ((int)$item['shipped_qty'] > 0) {
                    $isMat = (strpos($item['fg_sku'], 'MAT-') === 0);
                    $table = $isMat ? 'raw_materials' : 'warehouse_inventory';
                    $skuCol = $isMat ? 'sku_material' : 'sku';
                    $this->db->query("UPDATE {$table} SET physical_stock = physical_stock + ? WHERE {$skuCol} = ?", [$item['shipped_qty'], $item['fg_sku']]);
                }
            }

            $pointsToDeduct = floor($so['total_amount'] / 100000);
            if ($pointsToDeduct > 0) {
                $this->db->query("UPDATE b2b_customers SET reward_points = GREATEST(0, reward_points - ?) WHERE id = ?", [$pointsToDeduct, $so['customer_id']]);
            }

            $journals = $this->db->table('journals')->like('description', $soNumber)->get()->getResultArray();
            foreach ($journals as $j) {
                $this->db->table('journal_items')->where('journal_id', $j['id'])->delete();
                $this->db->table('journals')->where('id', $j['id'])->delete();
            }

            $this->db->table('operational_cash')->like('description', $soNumber)->delete();

            $workOrders = $this->db->table('work_orders')->where('so_id', $id)->get()->getResultArray();
            foreach ($workOrders as $wo) {
                if ($wo['status'] !== 'COMPLETED') {
                    $this->db->table('production_logs')->where('spk_number', $wo['spk_number'])->delete();
                    $this->db->table('work_orders')->where('id', $wo['id'])->delete();
                } else {
                    $this->db->table('work_orders')->where('id', $wo['id'])->update(['so_id' => null, 'source' => 'MANUAL']);
                }
            }

            $this->db->table('b2b_sales_order_items')->where('so_id', $id)->delete();
            $this->db->table('b2b_sales_orders')->where('id', $id)->delete();

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                throw new \Exception("Gagal membatalkan transaksi dan membalikkan jurnal.");
            }

            return redirect()->back()->with('success', "Dokumen SO <b>{$soNumber}</b> berhasil dihapus. Stok Gudang, Poin Mitra, dan SPK Pabrik ditarik kembali.");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
    
    public function get_pending_by_customer($customerId = null)
    {
        if (empty($customerId)) $customerId = $this->request->getUri()->getSegment(3);
        if (empty($customerId)) return $this->response->setJSON(['status' => 'error', 'message' => 'Parameter Toko tidak valid.']);

        $items = $this->db->query("
            SELECT i.id, i.so_id, i.fg_sku, i.qty, i.shipped_qty, o.so_number, o.order_date, o.shipping_status
            FROM b2b_sales_order_items i
            JOIN b2b_sales_orders o ON o.id = i.so_id
            WHERE o.customer_id = ? ORDER BY o.order_date ASC
        ", [$customerId])->getResultArray();
        
        $this->enrichItemsWithNames($items);
        
        $pendingItems = [];
        foreach ($items as $itm) {
            if ($itm['shipping_status'] === 'SHIPPED') continue;
            if ((int)$itm['qty'] > (int)$itm['shipped_qty']) {
                $pendingItems[] = $itm;
            }
        }
        return $this->response->setJSON(['status' => 'success', 'data' => $pendingItems]);
    }

    public function process_shipment_gabungan()
    {
        $this->db->transStart();
        
        $itemIds = $this->request->getPost('so_item_id') ?? [];
        $shipQtys = $this->request->getPost('ship_qty') ?? [];
        $soIdsToUpdate = [];
        $totalHppCost = 0;

        foreach ($itemIds as $index => $itemId) {
            $shipQty = (int)$shipQtys[$index];
            if ($shipQty <= 0) continue;

            $item = $this->db->table('b2b_sales_order_items')->where('id', $itemId)->get()->getRowArray();
            if (!$item) continue;
            
            $so = $this->db->table('b2b_sales_orders')->where('id', $item['so_id'])->get()->getRowArray();
            if (!$so) continue;
            
            $soIdsToUpdate[] = $so['id'];

            $isMat = (strpos($item['fg_sku'], 'MAT-') === 0);
            $table = $isMat ? 'raw_materials' : 'warehouse_inventory';
            $skuCol = $isMat ? 'sku_material' : 'sku';
            
            $stock = $this->db->table($table)->where($skuCol, $item['fg_sku'])->get()->getRowArray();
            if (!$stock || (int)$stock['physical_stock'] < $shipQty) {
                throw new \Exception("Gagal: Stok Gudang untuk {$item['fg_sku']} kurang dari {$shipQty}!");
            }

            $this->db->query("UPDATE {$table} SET physical_stock = physical_stock - ? WHERE {$skuCol} = ?", [$shipQty, $item['fg_sku']]);
            $totalHppCost += ($shipQty * (float)$stock['hpp']);
            
            $this->db->query("UPDATE b2b_sales_order_items SET shipped_qty = shipped_qty + ? WHERE id = ?", [$shipQty, $itemId]);
        }

        $soIdsToUpdate = array_unique($soIdsToUpdate);
        foreach ($soIdsToUpdate as $soId) {
            $allItems = $this->db->table('b2b_sales_order_items')->where('so_id', $soId)->get()->getResultArray();
            $allShipped = true;
            $anyShipped = false;
            foreach ($allItems as $it) {
                if ((int)$it['shipped_qty'] < (int)$it['qty']) $allShipped = false;
                if ((int)$it['shipped_qty'] > 0) $anyShipped = true;
            }
            
            $shipStatus = 'PENDING';
            if ($allShipped) $shipStatus = 'SHIPPED';
            elseif ($anyShipped) $shipStatus = 'PARTIAL-SHIPPED';

            $this->db->table('b2b_sales_orders')->where('id', $soId)->update(['shipping_status' => $shipStatus]);
        }
        
        if ($totalHppCost > 0) {
            $this->db->table('journals')->insert([
                'journal_number'   => 'JRN-SHP-GBG-'.time(),
                'transaction_date' => date('Y-m-d'),
                'description'      => "Pengiriman Barang Gabungan B2B",
                'total_amount'     => $totalHppCost,
                'created_by'       => session()->get('name') ?? 'System'
            ]);
            $journalId = $this->db->insertID();
            
            $invAcc = $this->db->table('chart_of_accounts')->where('account_code', '1-3000')->get()->getRowArray(); 
            $hppAcc = $this->db->table('chart_of_accounts')->where('account_code', '5-1000')->get()->getRowArray();

            if ($invAcc && $hppAcc) {
                $this->db->table('journal_items')->insert(['journal_id' => $journalId, 'account_id' => $hppAcc['id'], 'debit' => $totalHppCost, 'credit' => 0, 'line_description' => 'HPP Barang Keluar (Gabungan)']);
                $this->db->table('journal_items')->insert(['journal_id' => $journalId, 'account_id' => $invAcc['id'], 'debit' => 0, 'credit' => $totalHppCost, 'line_description' => 'Persediaan Keluar']);
            }
        }

        $this->db->transComplete();
        if ($this->db->transStatus() === false) return redirect()->back()->with('error', 'Gagal memproses pengiriman gabungan.');

        $soIdsString = implode('-', $soIdsToUpdate);
        return redirect()->back()->with('success', 'Pengiriman gabungan berhasil diproses dan stok telah dipotong!')->with('print_gabungan', $soIdsString);
    }

    public function print_sj_gabungan($soIdsStr)
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/portal');
        
        $soIds = explode('-', $soIdsStr);
        if (empty($soIds)) return redirect()->to('/wholesale');
        
        $sos = $this->db->table('b2b_sales_orders')->whereIn('id', $soIds)->get()->getResultArray();
        $customer = $this->db->table('b2b_customers')->where('id', $sos[0]['customer_id'])->get()->getRowArray();
        
        $items = $this->db->table('b2b_sales_order_items')
            ->select('b2b_sales_order_items.*, b2b_sales_orders.so_number')
            ->join('b2b_sales_orders', 'b2b_sales_orders.id = b2b_sales_order_items.so_id')
            ->whereIn('b2b_sales_order_items.so_id', $soIds)
            ->get()->getResultArray();
        
        $this->enrichItemsWithNames($items);
            
        $company = $this->db->tableExists('company_settings') ? $this->db->table('company_settings')->get()->getRowArray() : [];
        
        return view('wholesale/print_sj_gabungan', ['sos' => $sos, 'customer' => $customer, 'items' => $items, 'company' => $company]);
    }
    
    public function get_so(string $id)
    {
        if (!$this->request->isAJAX()) return;

        $so = $this->db->table('b2b_sales_orders')->where('id', $id)->get()->getRowArray();
        if (!$so) return $this->response->setJSON(['status' => 'error', 'message' => 'SO tidak ditemukan']);

        $items = $this->db->table('b2b_sales_order_items')->where('so_id', $id)->get()->getResultArray();
        $this->enrichItemsWithNames($items);
        
        $so['items'] = $items;
        return $this->response->setJSON(['status' => 'success', 'data' => $so]);
    }

    public function add_item_to_so()
    {
        try {
            $this->db->transStart();

            $soId = $this->request->getPost('so_id');
            $fgSkus = $this->request->getPost('fg_sku') ?? []; 
            $qtys = $this->request->getPost('qty') ?? [];     
            $prices = $this->request->getPost('unit_price') ?? []; 
            $addFees = $this->request->getPost('additional_fee') ?? [];
            $addNotes = $this->request->getPost('additional_note') ?? [];

            // TANGKAP DATA BONUS DARI MODAL EDIT ITEM
            $bonusSkus = $this->request->getPost('bonus_sku') ?? [];
            $bonusQtys = $this->request->getPost('bonus_qty') ?? [];
            
            for ($i = 0; $i < count($bonusSkus); $i++) {
                if (!empty($bonusSkus[$i]) && (int)$bonusQtys[$i] > 0) {
                    $fgSkus[]   = $bonusSkus[$i];
                    $qtys[]     = $bonusQtys[$i];
                    $prices[]   = 0;
                    $addFees[]  = 0;
                    $addNotes[] = 'BONUS GRATIS';
                }
            }

            if (empty($soId)) throw new \Exception("ID SO tidak valid.");
            if (empty($fgSkus) || count($fgSkus) === 0) throw new \Exception("Anda harus memilih minimal 1 produk.");

            $so = $this->db->table('b2b_sales_orders')->where('id', $soId)->get()->getRowArray();
            if (!$so) throw new \Exception("Pesanan (SO) tidak ditemukan.");
            if ($so['shipping_status'] === 'SHIPPED') throw new \Exception("Gagal: Pesanan ini telah terkirim 100%.");
            if ($so['status'] === 'RETURNED') throw new \Exception("Gagal: Pesanan ini sudah berstatus Diretur.");

            $orderType = 'PREORDER'; // Default edit behavior

            $totalAdditionalAmount = 0;
            $totalHppCost = 0;
            $autoSpkCreated = 0;

            for ($i = 0; $i < count($fgSkus); $i++) {
                if (!empty($fgSkus[$i])) {
                    $sku     = $fgSkus[$i]; 
                    $qty     = (int)$qtys[$i]; 
                    $price   = isset($prices[$i]) ? $this->cleanRupiah((string)$prices[$i]) : 0;
                    $addFee  = isset($addFees[$i]) ? $this->cleanRupiah((string)$addFees[$i]) : 0;
                    $addNote = $addNotes[$i] ?? '';

                    if ($qty <= 0) continue;

                    $isMat = (strpos($sku, 'MAT-') === 0);
                    $table = $isMat ? 'raw_materials' : 'warehouse_inventory';
                    $skuCol = $isMat ? 'sku_material' : 'sku';

                    $stock = $this->db->table($table)->where($skuCol, $sku)->get()->getRowArray();
                    
                    $subtotal = $qty * ($price + $addFee);
                    $totalAdditionalAmount += $subtotal;
                    
                    $shippedQty = ($orderType === 'READY') ? $qty : 0;
                    
                    $this->db->table('b2b_sales_order_items')->insert([
                        'so_id' => $soId, 'fg_sku' => $sku, 'qty' => $qty, 'shipped_qty' => $shippedQty, 
                        'price' => $price, 'additional_fee' => $addFee, 'additional_note' => $addNote, 'subtotal' => $subtotal
                    ]);

                    if ($orderType === 'PREORDER' && !$isMat) {
                        $bom = $this->db->table('bom_headers')->where('fg_sku', $sku)->orderBy('id', 'DESC')->get()->getRowArray();
                        if ($bom) {
                            $dateStr = date('Ymd');
                            $lastSpk = $this->db->table('work_orders')->like('spk_number', "SPK-$dateStr", 'after')->orderBy('id', 'DESC')->get()->getRowArray();
                            
                            // PERBAIKAN PADA ADD ITEM SO (MENCEGAH ERROR "ONLY VARIABLES SHOULD BE PASSED BY REFERENCE")
                            $seq = 1;
                            if ($lastSpk) {
                                $spkParts = explode('-', $lastSpk['spk_number']);
                                $seq = intval(end($spkParts)) + 1;
                            }
                            
                            $spkNumber = "SPK-" . $dateStr . "-" . str_pad($seq, 3, '0', STR_PAD_LEFT);

                            $this->db->table('work_orders')->insert([
                                'so_id' => $soId, 'spk_number' => $spkNumber, 'bom_id' => $bom['id'], 'planned_qty' => $qty, 
                                'production_notes' => $addNote, 'status' => 'IN_PROGRESS', 'start_date' => date('Y-m-d'), 'source' => 'PREORDER'
                            ]);
                            $autoSpkCreated++;
                        }
                    }
                }
            }

            if ($totalAdditionalAmount == 0 && count($fgSkus) == 0) throw new \Exception("Tidak ada produk valid yang ditambahkan.");

            $discPercent = (float)$so['discount_percent'];
            $oldTotalRaw = (float)$so['total_amount'] + (float)$so['discount'];
            $newTotalRaw = $oldTotalRaw + $totalAdditionalAmount;
            
            $newDiscAmount = $newTotalRaw * ($discPercent / 100);
            $newGrandTotal = $newTotalRaw - $newDiscAmount;
            
            $paidAmount = (float)$so['paid_amount'];
            $newStatus = ($paidAmount >= $newGrandTotal) ? 'PAID' : ($paidAmount > 0 ? 'PARTIAL' : 'PENDING');

            $this->db->table('b2b_sales_orders')->where('id', $soId)->update([
                'total_amount' => $newGrandTotal, 'discount' => $newDiscAmount, 'status' => $newStatus,
                'shipping_status' => ($so['shipping_status'] === 'SHIPPED' && $orderType === 'PREORDER') ? 'PARTIAL-SHIPPED' : $so['shipping_status'] 
            ]);

            $netAdditionalRevenue = $newGrandTotal - (float)$so['total_amount'];

            $piutangAcc = $this->db->table('chart_of_accounts')->where('account_code', '1-4000')->get()->getRowArray();
            $revAcc = $this->db->table('chart_of_accounts')->where('account_code', '4-1000')->get()->getRowArray();

            if ($netAdditionalRevenue > 0) {
                $this->db->table('journals')->insert([
                    'journal_number' => 'JRN-ADD-'.time(), 'transaction_date' => date('Y-m-d'), 'description' => "Penambahan Item SO: {$so['so_number']}",
                    'total_amount' => $netAdditionalRevenue, 'created_by' => session()->get('name') ?? 'System'
                ]);
                $journalId = $this->db->insertID();

                if ($revAcc && $piutangAcc) {
                    $this->db->table('journal_items')->insert(['journal_id' => $journalId, 'account_id' => $revAcc['id'], 'debit' => 0, 'credit' => $netAdditionalRevenue, 'line_description' => 'Pendapatan B2B Tambahan']);
                    $this->db->table('journal_items')->insert(['journal_id' => $journalId, 'account_id' => $piutangAcc['id'], 'debit' => $netAdditionalRevenue, 'credit' => 0, 'line_description' => 'Piutang B2B Tambahan']);
                }

                $pointsEarned = floor($netAdditionalRevenue / 100000);
                if ($pointsEarned > 0) {
                    $this->db->query("UPDATE b2b_customers SET reward_points = reward_points + ? WHERE id = ?", [$pointsEarned, $so['customer_id']]);
                }
            }

            $this->db->transComplete();
            if ($this->db->transStatus() === false) throw new \Exception("Sistem gagal menyimpan perubahan database.");

            $pesan = "Berhasil menambahkan item ke Pesanan <b>{$so['so_number']}</b>.";
            if ($orderType === 'PREORDER' && $autoSpkCreated > 0) $pesan .= "<br>Diterbitkan <b>$autoSpkCreated SPK Pabrik</b> otomatis untuk item baru.";

            return redirect()->back()->with('success', $pesan);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function delete_item_from_so($itemId)
    {
        try {
            if (!session()->get('isLoggedIn')) return redirect()->to('/portal');
            $this->db->transStart();

            $item = $this->db->table('b2b_sales_order_items')->where('id', $itemId)->get()->getRowArray();
            if (!$item) throw new \Exception("Item tidak ditemukan.");

            $so = $this->db->table('b2b_sales_orders')->where('id', $item['so_id'])->get()->getRowArray();
            if ($so['shipping_status'] === 'SHIPPED') throw new \Exception("Gagal: Orderan sudah terkirim Full (SHIPPED). Tidak bisa hapus barang.");
            if ($so['status'] === 'RETURNED') throw new \Exception("Gagal: Orderan sudah Diretur.");

            $qtyToReturn = (int)$item['shipped_qty'];
            if ($qtyToReturn > 0) {
                 $isMat = (strpos($item['fg_sku'], 'MAT-') === 0);
                 $table = $isMat ? 'raw_materials' : 'warehouse_inventory';
                 $skuCol = $isMat ? 'sku_material' : 'sku';
                 $this->db->query("UPDATE {$table} SET physical_stock = physical_stock + ? WHERE {$skuCol} = ?", [$qtyToReturn, $item['fg_sku']]);
            }

            $pointsToDeduct = floor($item['subtotal'] / 100000);
            if ($pointsToDeduct > 0) {
                $this->db->query("UPDATE b2b_customers SET reward_points = GREATEST(0, reward_points - ?) WHERE id = ?", [$pointsToDeduct, $so['customer_id']]);
            }

            // PERBAIKAN LOGIKA PENGHAPUSAN SPK PRODUKSI
            // Hapus SPK terlepas dari status PENDING atau bukan, selama SPK-nya belum COMPLETED
            $bom = $this->db->table('bom_headers')->where('fg_sku', $item['fg_sku'])->orderBy('id', 'DESC')->get()->getRowArray();
            if($bom) {
                $spks = $this->db->table('work_orders')
                    ->where('so_id', $so['id'])
                    ->where('bom_id', $bom['id'])
                    ->where('production_notes', $item['additional_note']) // Pencocokan spesifik dengan catatannya (emblem dll)
                    ->where('status !=', 'COMPLETED')
                    ->get()->getResultArray();
                    
                foreach($spks as $wo) { 
                    // Bersihkan log setoran jika ada, lalu hapus SPK-nya
                    $this->db->table('production_logs')->where('spk_number', $wo['spk_number'])->delete();
                    $this->db->table('work_orders')->where('id', $wo['id'])->delete(); 
                }
            }

            $discPercent = (float)$so['discount_percent'];
            $oldTotalRaw = (float)$so['total_amount'] + (float)$so['discount'];
            $newTotalRaw = max(0, $oldTotalRaw - (float)$item['subtotal']);
            
            $newDiscAmount = $newTotalRaw * ($discPercent / 100);
            $newGrandTotal = $newTotalRaw - $newDiscAmount;
            
            $paidAmount = (float)$so['paid_amount'];
            $newStatus = ($paidAmount >= $newGrandTotal) ? 'PAID' : ($paidAmount > 0 ? 'PARTIAL' : 'PENDING');
            if ($newGrandTotal <= 0) $newStatus = 'RETURNED';

            $this->db->table('b2b_sales_orders')->where('id', $so['id'])->update([
                'total_amount' => $newGrandTotal, 'discount' => $newDiscAmount, 'status' => $newStatus
            ]);

            $this->db->table('b2b_sales_order_items')->where('id', $itemId)->delete();

            $netReduction = (float)$so['total_amount'] - $newGrandTotal;
            if ($netReduction > 0) {
                $piutangAcc = $this->db->table('chart_of_accounts')->where('account_code', '1-4000')->get()->getRowArray();
                $revAcc = $this->db->table('chart_of_accounts')->where('account_code', '4-1000')->get()->getRowArray();
                
                $this->db->table('journals')->insert([
                    'journal_number' => 'JRN-DEL-'.time(), 'transaction_date' => date('Y-m-d'), 'description' => "Revisi/Hapus Item SO: {$so['so_number']}",
                    'total_amount' => $netReduction, 'created_by' => session()->get('name') ?? 'System'
                ]);
                $journalId = $this->db->insertID();

                if ($revAcc && $piutangAcc) {
                    $this->db->table('journal_items')->insert(['journal_id' => $journalId, 'account_id' => $revAcc['id'], 'debit' => $netReduction, 'credit' => 0, 'line_description' => 'Pembatalan Pendapatan']);
                    $this->db->table('journal_items')->insert(['journal_id' => $journalId, 'account_id' => $piutangAcc['id'], 'debit' => 0, 'credit' => $netReduction, 'line_description' => 'Pengurangan Piutang']);
                }
            }

            $this->db->transComplete();
            if ($this->db->transStatus() === false) throw new \Exception("Sistem gagal memproses penghapusan item.");

            return redirect()->back()->with('success', 'Item berhasil dihapus dari orderan. Total tagihan dan antrean produksi telah disesuaikan.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function update_item_qty($itemId)
    {
        try {
            if (!session()->get('isLoggedIn')) { return $this->response->setJSON(['status' => 'error', 'message' => 'Sesi habis', 'csrf_token' => csrf_hash()]); }
            $this->db->transStart();

            $newQty = (int)$this->request->getPost('new_qty');
            if ($newQty <= 0) throw new \Exception("Kuantitas minimal adalah 1.");

            $item = $this->db->table('b2b_sales_order_items')->where('id', $itemId)->get()->getRowArray();
            if (!$item) throw new \Exception("Item tidak ditemukan.");

            $so = $this->db->table('b2b_sales_orders')->where('id', $item['so_id'])->get()->getRowArray();
            if ($so['shipping_status'] === 'SHIPPED') throw new \Exception("Gagal: Orderan sudah terkirim Full (SHIPPED).");
            if ($so['status'] === 'RETURNED') throw new \Exception("Gagal: Orderan sudah Diretur.");

            $oldQty = (int)$item['qty'];
            if ($newQty == $oldQty) return $this->response->setJSON(['status' => 'success', 'message' => 'Tidak ada perubahan.', 'csrf_token' => csrf_hash()]);

            $diffQty = $newQty - $oldQty;
            $shippedQty = (int)$item['shipped_qty'];

            if ($newQty < $shippedQty) throw new \Exception("Gagal: Qty baru tidak boleh lebih kecil dari yang sudah dikirim ($shippedQty).");

            $isMat = (strpos($item['fg_sku'], 'MAT-') === 0);
            $table = $isMat ? 'raw_materials' : 'warehouse_inventory';
            $skuCol = $isMat ? 'sku_material' : 'sku';
            
            $stock = $this->db->table($table)->where($skuCol, $item['fg_sku'])->get()->getRowArray();
            $hpp = (float)($stock['hpp'] ?? 0);

            $orderType = 'PREORDER';

            if ($orderType === 'READY') {
                if ($diffQty > 0) {
                    if (!$stock || $stock['physical_stock'] < $diffQty) throw new \Exception("Stok Gudang fisik kurang.");
                    $this->db->query("UPDATE {$table} SET physical_stock = physical_stock - ? WHERE {$skuCol} = ?", [$diffQty, $item['fg_sku']]);
                } else {
                    $absDiff = abs($diffQty);
                    $this->db->query("UPDATE {$table} SET physical_stock = physical_stock + ? WHERE {$skuCol} = ?", [$absDiff, $item['fg_sku']]);
                }
                $this->db->query("UPDATE b2b_sales_order_items SET shipped_qty = shipped_qty + ? WHERE id = ?", [$diffQty, $itemId]);
            } else {
                if (!$isMat) {
                    $bom = $this->db->table('bom_headers')->where('fg_sku', $item['fg_sku'])->orderBy('id', 'DESC')->get()->getRowArray();
                    if ($bom) {
                        // SINKRONISASI UPDATE QTY DENGAN SPK (MATCH DENGAN CATATAN PABRIK)
                        $spk = $this->db->table('work_orders')
                            ->where('so_id', $so['id'])
                            ->where('bom_id', $bom['id'])
                            ->where('production_notes', $item['additional_note'])
                            ->where('status !=', 'COMPLETED')
                            ->get()->getRowArray();
                            
                        if ($spk) {
                            $newSpkQty = (int)$spk['planned_qty'] + $diffQty;
                            if ($newSpkQty < (int)$spk['completed_qty']) throw new \Exception("Gagal: Target SPK tidak bisa lebih kecil dari barang yang disetor.");
                            $this->db->table('work_orders')->where('id', $spk['id'])->update(['planned_qty' => $newSpkQty]);
                        }
                    }
                }
            }

            $itemPrice = (float)$item['price'] + (float)$item['additional_fee'];
            $newSubtotal = $newQty * $itemPrice;
            $diffAmount = $newSubtotal - (float)$item['subtotal'];

            $this->db->table('b2b_sales_order_items')->where('id', $itemId)->update(['qty' => $newQty, 'subtotal' => $newSubtotal]);

            $discPercent = (float)$so['discount_percent'];
            $oldTotalRaw = (float)$so['total_amount'] + (float)$so['discount'];
            $newTotalRaw = max(0, $oldTotalRaw + $diffAmount);

            $newDiscAmount = $newTotalRaw * ($discPercent / 100);
            $newGrandTotal = $newTotalRaw - $newDiscAmount;

            $paidAmount = (float)$so['paid_amount'];
            $newStatus = ($paidAmount >= $newGrandTotal) ? 'PAID' : ($paidAmount > 0 ? 'PARTIAL' : 'PENDING');

            $this->db->table('b2b_sales_orders')->where('id', $so['id'])->update(['total_amount' => $newGrandTotal, 'discount' => $newDiscAmount, 'status' => $newStatus]);

            $netDiff = $newGrandTotal - (float)$so['total_amount'];
            if ($netDiff != 0) {
                $pointsDiff = floor(abs($netDiff) / 100000);
                if ($pointsDiff > 0) {
                    if ($netDiff > 0) $this->db->query("UPDATE b2b_customers SET reward_points = reward_points + ? WHERE id = ?", [$pointsDiff, $so['customer_id']]);
                    else $this->db->query("UPDATE b2b_customers SET reward_points = GREATEST(0, reward_points - ?) WHERE id = ?", [$pointsDiff, $so['customer_id']]);
                }

                $piutangAcc = $this->db->table('chart_of_accounts')->where('account_code', '1-4000')->get()->getRowArray();
                $revAcc = $this->db->table('chart_of_accounts')->where('account_code', '4-1000')->get()->getRowArray();
                $invAcc = $this->db->table('chart_of_accounts')->where('account_code', '1-3000')->get()->getRowArray();
                $hppAcc = $this->db->table('chart_of_accounts')->where('account_code', '5-1000')->get()->getRowArray();

                $jurnalDesc = ($netDiff > 0) ? "Penambahan Qty Item SO: {$so['so_number']}" : "Pengurangan Qty Item SO: {$so['so_number']}";

                $this->db->table('journals')->insert([
                    'journal_number' => 'JRN-QTY-'.time(), 'transaction_date' => date('Y-m-d'), 'description' => $jurnalDesc,
                    'total_amount' => abs($netDiff), 'created_by' => session()->get('name') ?? 'System'
                ]);
                $journalId = $this->db->insertID();

                if ($revAcc && $piutangAcc) {
                    if ($netDiff > 0) {
                        $this->db->table('journal_items')->insert(['journal_id' => $journalId, 'account_id' => $revAcc['id'], 'debit' => 0, 'credit' => $netDiff, 'line_description' => 'Revisi Qty (Pendapatan Naik)']);
                        $this->db->table('journal_items')->insert(['journal_id' => $journalId, 'account_id' => $piutangAcc['id'], 'debit' => $netDiff, 'credit' => 0, 'line_description' => 'Revisi Qty (Piutang Naik)']);
                    } else {
                        $this->db->table('journal_items')->insert(['journal_id' => $journalId, 'account_id' => $revAcc['id'], 'debit' => abs($netDiff), 'credit' => 0, 'line_description' => 'Revisi Qty (Pendapatan Turun)']);
                        $this->db->table('journal_items')->insert(['journal_id' => $journalId, 'account_id' => $piutangAcc['id'], 'debit' => 0, 'credit' => abs($netDiff), 'line_description' => 'Revisi Qty (Piutang Turun)']);
                    }
                }

                if ($orderType === 'READY' && $hpp > 0 && $invAcc && $hppAcc) {
                    $hppDiff = abs($diffQty) * $hpp;
                    if ($diffQty > 0) { 
                        $this->db->table('journal_items')->insert(['journal_id' => $journalId, 'account_id' => $hppAcc['id'], 'debit' => $hppDiff, 'credit' => 0, 'line_description' => 'Revisi Qty (HPP Bertambah)']);
                        $this->db->table('journal_items')->insert(['journal_id' => $journalId, 'account_id' => $invAcc['id'], 'debit' => 0, 'credit' => $hppDiff, 'line_description' => 'Revisi Qty (Persediaan Keluar)']);
                    } else { 
                        $this->db->table('journal_items')->insert(['journal_id' => $journalId, 'account_id' => $invAcc['id'], 'debit' => $hppDiff, 'credit' => 0, 'line_description' => 'Revisi Qty (Persediaan Masuk)']);
                        $this->db->table('journal_items')->insert(['journal_id' => $journalId, 'account_id' => $hppAcc['id'], 'debit' => 0, 'credit' => $hppDiff, 'line_description' => 'Revisi Qty (Pembalik HPP)']);
                    }
                }
            }

            $this->db->transComplete();
            if ($this->db->transStatus() === false) throw new \Exception("Gagal menyimpan ke database.");

            return $this->response->setJSON(['status' => 'success', 'message' => 'Qty berhasil diperbarui.', 'csrf_token' => csrf_hash()]);
        } catch (\Exception $e) {
            return $this->response->setJSON(['status' => 'error', 'message' => $e->getMessage(), 'csrf_token' => csrf_hash()]);
        }
    }
}
?>