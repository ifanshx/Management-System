<?php

namespace App\Controllers;

class LocalWarehouse extends BaseController
{
    private $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        
        // AUTO-PATCH SCHEMA: Menambahkan kolom motor_category secara otomatis jika belum ada
        try {
            $this->db->query("ALTER TABLE warehouse_inventory ADD COLUMN motor_category VARCHAR(50) DEFAULT 'Universal' AFTER item_type");
        } catch (\Exception $e) {
            // Abaikan jika kolom sudah ada
        }
    }

    private function generateSKU($type, $context, $motorCategory = 'Universal') 
    {
        if ($type === 'PRD') {
            $table  = 'warehouse_inventory';
            $column = 'sku';
            
            // Pemetaan Tipe Fisik Knalpot
            $mapType = [
                'Full System'           => 'FSY',
                'Silencer / Slip-on'    => 'SLC',
                'Header / Leheran'      => 'LHR',
                'Aksesoris / Sparepart' => 'ACC'
            ];
            
            // Pemetaan Kategori Motor
            $mapMotor = [
                'Matic'     => 'MTC',
                'Sport'     => 'SPT',
                'Bebek'     => 'BBK',
                'Universal' => 'UNV'
            ];

            $typeCode  = $mapType[$context] ?? 'GEN'; 
            $motorCode = $mapMotor[$motorCategory] ?? 'UNV';
            
            // Format Standar ERP: PRD-{TIPE}-{MOTOR}-{URUTAN} -> Cth: PRD-LHR-MTC-001
            $prefix = "PRD-{$typeCode}-{$motorCode}-";
            
        } else {
            $table  = 'raw_materials';
            $column = 'sku_material';
            
            $cleanName = preg_replace('/[^A-Za-z]/', '', $context);
            $midCode = strtoupper(substr($cleanName, 0, 3));
            
            if (strlen($midCode) < 3) {
                $midCode = str_pad($midCode, 3, 'X'); 
            }
            $prefix = "MAT-{$midCode}-";
        }

        $lastItem = $this->db->table($table)
                             ->like($column, $prefix, 'after')
                             ->orderBy('id', 'DESC')
                             ->get()
                             ->getRowArray();
        
        if (!$lastItem) return $prefix . '001';

        $lastSku = $lastItem[$column];
        $parts = explode('-', $lastSku);
        $lastNumber = intval(end($parts)); 
        $newNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
        
        return $prefix . $newNumber;
    }

    public function index()
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/portal');

        $finishedGoods = $this->db->table('warehouse_inventory')->orderBy('id', 'DESC')->get()->getResultArray();
        $rawMaterials = $this->db->table('raw_materials')->orderBy('id', 'DESC')->get()->getResultArray();
        $adjustments = $this->db->table('stock_adjustments')->orderBy('id', 'DESC')->get()->getResultArray();
        
        $uomMaster = $this->db->table('uom_master')->where('is_active', 1)->orderBy('uom_name', 'ASC')->get()->getResultArray();

        $totalValueFG = 0; foreach($finishedGoods as $f) { $totalValueFG += ($f['physical_stock'] * $f['hpp']); }
        $totalValueRM = 0; foreach($rawMaterials as $r) { $totalValueRM += ($r['physical_stock'] * $r['hpp']); }

        $data = [
            'title'         => 'Master Inventaris Terpusat',
            'finishedGoods' => $finishedGoods,
            'rawMaterials'  => $rawMaterials,
            'adjustments'   => $adjustments,
            'uomMaster'     => $uomMaster, 
            'totalValueFG'  => $totalValueFG,
            'totalValueRM'  => $totalValueRM
        ];

        return view('warehouse/local_inventory', $data);
    }

    public function store_fg()
    {
        try {
            $this->db->transStart(); 

            $itemType = $this->request->getPost('item_type');
            $motorCategory = $this->request->getPost('motor_category') ?? 'Universal';
            $itemName = $this->request->getPost('item_name');
            
            $autoSku = $this->generateSKU('PRD', $itemType, $motorCategory);
            
            $data = [
                'sku'             => $autoSku,
                'item_name'       => $itemName,
                'item_type'       => $itemType,
                'motor_category'  => $motorCategory,
                'hpp'             => (float) str_replace(['Rp', '.', ' '], '', $this->request->getPost('hpp')),
                'retail_price'    => (float) str_replace(['Rp', '.', ' '], '', $this->request->getPost('retail_price')),
                'wholesale_price' => (float) str_replace(['Rp', '.', ' '], '', $this->request->getPost('wholesale_price')),
                'physical_stock'  => (int)$this->request->getPost('initial_stock'),
                'min_stock'       => (int)$this->request->getPost('min_stock')
            ];

            $this->db->table('warehouse_inventory')->insert($data);

            // ========================================================
            // AUTO-DRAFT BOM MENGIKUTI DB BARU
            // ========================================================
            $this->db->table('bom_headers')->insert([
                'fg_sku'      => $autoSku,
                'recipe_name' => 'Resep Dasar: ' . $itemName
            ]);
            $newBomId = $this->db->insertID();

            $this->db->table('bom_operations')->insert([
                'bom_id'         => $newBomId,
                'step_order'     => 1,
                'section_code'   => 'PRK',
                'operation_name' => '[PERAKITAN] PERAKITAN PACKING',
                'worker_type'    => 'Tetap',
                'wage_per_piece' => 0,
                'is_final_step'  => 1
            ]);
            // ========================================================

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                throw new \Exception("Gagal menyimpan Produk dan Draft Resep ke database.");
            }

            $pesanSukses = "Produk <b>{$autoSku}</b> berhasil didaftarkan! <br><span style='font-size:11px; color:#10b981;'><i class='ph-fill ph-magic-wand'></i> Draft Resep (BoM) telah otomatis dibuat di menu Produksi.</span>";

            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['status' => 'success', 'message' => $pesanSukses]);
            }
            return redirect()->back()->with('success', $pesanSukses);

        } catch (\Exception $e) {
            if ($this->request->isAJAX()) return $this->response->setJSON(['status' => 'error', 'message' => $e->getMessage()]);
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function store_rm()
    {
        try {
            $materialName = $this->request->getPost('material_name');
            $autoSku = $this->generateSKU('MAT', $materialName);
            
            $purchaseUom = strtoupper(trim($this->request->getPost('purchase_uom')));
            $baseUom     = strtoupper(trim($this->request->getPost('base_uom')));
            $conversion  = (float)$this->request->getPost('conversion_factor');

            if ($conversion <= 0) $conversion = 1;
            if ($purchaseUom === $baseUom) $conversion = 1;

            if (!$purchaseUom) $purchaseUom = strtoupper(trim($this->request->getPost('unit') ?? 'PCS'));
            if (!$baseUom) $baseUom = $purchaseUom;

            $data = [
                'sku_material'      => $autoSku,
                'material_name'     => $materialName,
                'material_category' => $this->request->getPost('material_category') ?? 'General',
                'unit'              => $baseUom,
                'purchase_uom'      => $purchaseUom,
                'base_uom'          => $baseUom,
                'conversion_factor' => $conversion,
                'hpp'               => (float) str_replace(['Rp', '.', ' '], '', $this->request->getPost('hpp')),
                'physical_stock'    => (float)$this->request->getPost('initial_stock'),
                'min_stock'         => (float)$this->request->getPost('min_stock')
            ];

            $this->db->table('raw_materials')->insert($data);

            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['status' => 'success', 'message' => "Material Mentah berhasil disimpan dengan SKU: $autoSku"]);
            }
            return redirect()->back()->with('success', "Material Bahan Baku (MAT) berhasil disimpan dengan SKU: <b>{$autoSku}</b>");
        } catch (\Exception $e) {
            if ($this->request->isAJAX()) return $this->response->setJSON(['status' => 'error', 'message' => $e->getMessage()]);
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function get_fg($id)
    {
        if ($this->request->isAJAX()) {
            $item = $this->db->table('warehouse_inventory')->where('id', $id)->get()->getRowArray();
            return $this->response->setJSON($item);
        }
    }

    public function get_rm($id)
    {
        if ($this->request->isAJAX()) {
            $item = $this->db->table('raw_materials')->where('id', $id)->get()->getRowArray();
            return $this->response->setJSON($item);
        }
    }

   public function update_fg($id)
    {
        try {
            // Mulai transaksi database agar jika salah satu gagal, semuanya dibatalkan
            $this->db->transStart();

            // 1. Ambil data lama untuk mendapatkan SKU-nya
            $oldItem = $this->db->table('warehouse_inventory')->where('id', $id)->get()->getRowArray();
            if (!$oldItem) throw new \Exception("Data produk tidak ditemukan.");

            $itemName = $this->request->getPost('item_name');

            // 2. Siapkan data update untuk gudang
            $data = [
                'item_name'       => $itemName,
                'item_type'       => $this->request->getPost('item_type'),
                'motor_category'  => $this->request->getPost('motor_category') ?? 'Universal',
                'hpp'             => (float) str_replace(['Rp', '.', ' '], '', $this->request->getPost('hpp')),
                'retail_price'    => (float) str_replace(['Rp', '.', ' '], '', $this->request->getPost('retail_price')),
                'wholesale_price' => (float) str_replace(['Rp', '.', ' '], '', $this->request->getPost('wholesale_price')),
                'min_stock'       => (int)$this->request->getPost('min_stock')
            ];

            // 3. Eksekusi Update di Master Gudang
            $this->db->table('warehouse_inventory')->where('id', $id)->update($data);

            // 4. SINKRONISASI OTOMATIS KE PRODUKSI (BOM HEADERS)
            // Mengubah nama resep di modul produksi agar sama dengan nama barang terbaru
            $this->db->table('bom_headers')->where('fg_sku', $oldItem['sku'])->update([
                'recipe_name' => $itemName
            ]);

            // Selesaikan transaksi
            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                throw new \Exception("Sistem gagal menyinkronkan data Gudang dan Produksi.");
            }

            if ($this->request->isAJAX()) return $this->response->setJSON(['status' => 'success', 'message' => "Data Produk Gudang & Resep Produksi berhasil disinkronkan!"]);
            return redirect()->back()->with('success', "Data Produk Gudang & Resep Produksi berhasil disinkronkan!");
            
        } catch (\Exception $e) {
            if ($this->request->isAJAX()) return $this->response->setJSON(['status' => 'error', 'message' => $e->getMessage()]);
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function update_rm($id)
    {
        try {
            $purchaseUom = strtoupper(trim($this->request->getPost('purchase_uom')));
            $baseUom     = strtoupper(trim($this->request->getPost('base_uom')));
            $conversion  = (float)$this->request->getPost('conversion_factor');

            if ($conversion <= 0) $conversion = 1;
            if ($purchaseUom === $baseUom) $conversion = 1;

            if (!$purchaseUom) $purchaseUom = strtoupper(trim($this->request->getPost('unit') ?? 'PCS'));
            if (!$baseUom) $baseUom = $purchaseUom;

            $data = [
                'material_name'     => $this->request->getPost('material_name'),
                'material_category' => $this->request->getPost('material_category') ?? 'General',
                'unit'              => $baseUom,
                'purchase_uom'      => $purchaseUom,
                'base_uom'          => $baseUom,
                'conversion_factor' => $conversion,
                'hpp'               => (float) str_replace(['Rp', '.', ' '], '', $this->request->getPost('hpp')),
                'min_stock'         => (float)$this->request->getPost('min_stock')
            ];

            $this->db->table('raw_materials')->where('id', $id)->update($data);

            if ($this->request->isAJAX()) return $this->response->setJSON(['status' => 'success', 'message' => "Data Material berhasil diperbarui."]);
            return redirect()->back()->with('success', "Data Material berhasil diperbarui.");
        } catch (\Exception $e) {
            if ($this->request->isAJAX()) return $this->response->setJSON(['status' => 'error', 'message' => $e->getMessage()]);
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function store_adjustment()
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/portal');

        try {
            $this->db->transStart();

            $sku     = $this->request->getPost('sku');
            $adjType = $this->request->getPost('adjustment_type'); 
            $qty     = (float) $this->request->getPost('qty');
            $reason  = $this->request->getPost('reason');
            $picName = session()->get('name') ?? 'Admin Gudang';

            if ($qty <= 0) throw new \Exception("Kuantitas penyesuaian harus lebih dari 0.");

            $type = (substr($sku, 0, 3) === 'PRD') ? 'PRD' : 'MAT';
            
            if ($type === 'PRD') {
                $item = $this->db->table('warehouse_inventory')->where('sku', $sku)->get()->getRowArray();
                $tableName = 'warehouse_inventory';
                $skuCol = 'sku';
                $itemName = $item['item_name'] ?? 'Unknown';
            } else {
                $item = $this->db->table('raw_materials')->where('sku_material', $sku)->get()->getRowArray();
                $tableName = 'raw_materials';
                $skuCol = 'sku_material';
                $itemName = $item['material_name'] ?? 'Unknown';
            }

            if (!$item) throw new \Exception("Barang dengan SKU {$sku} tidak ditemukan.");

            if ($adjType === 'MINUS' && $item['physical_stock'] < $qty) {
                throw new \Exception("GAGAL! Stok fisik saat ini ({$item['physical_stock']}) tidak mencukupi untuk dikurangi {$qty}.");
            }

            $financialValue = $qty * $item['hpp'];

            if ($adjType === 'PLUS') {
                $this->db->query("UPDATE {$tableName} SET physical_stock = physical_stock + ? WHERE {$skuCol} = ?", [$qty, $sku]);
            } else {
                $this->db->query("UPDATE {$tableName} SET physical_stock = physical_stock - ? WHERE {$skuCol} = ?", [$qty, $sku]);
                
                $invAccount = $this->db->table('chart_of_accounts')->where('account_code', '1-3000')->get()->getRowArray();
                $lossAccount = $this->db->table('chart_of_accounts')->where('account_code', '5-9000')->get()->getRowArray(); 
                
                if ($invAccount && $lossAccount) {
                    $this->db->table('journals')->insert([
                        'journal_number'   => 'JRN-ADJ-'.time(),
                        'transaction_date' => date('Y-m-d'),
                        'description'      => "Kerugian Penyesuaian Stok (Scrap/Defect): " . $sku,
                        'total_amount'     => $financialValue,
                        'created_by'       => $picName
                    ]);
                    $journalId = $this->db->insertID();

                    $this->db->table('journal_items')->insert(['journal_id' => $journalId, 'account_id' => $lossAccount['id'], 'line_description' => 'Beban Kerugian', 'debit' => $financialValue, 'credit' => 0]);
                    $this->db->table('journal_items')->insert(['journal_id' => $journalId, 'account_id' => $invAccount['id'], 'line_description' => 'Persediaan Berkurang', 'debit' => 0, 'credit' => $financialValue]);
                }
            }

            $this->db->table('stock_adjustments')->insert([
                'sku'             => $sku,
                'item_name'       => $itemName,
                'item_type'       => $type,
                'adjustment_type' => $adjType,
                'qty'             => $qty,
                'reason'          => $reason,
                'financial_value' => $financialValue,
                'pic_name'        => $picName
            ]);
            
            $adjustmentId = $this->db->insertID();

            // Panggil fungsi log mutasi (Bypass logging untuk adjustment di controller ini langsung)
            $this->db->table('inventory_movements')->insert([
                'movement_date'   => date('Y-m-d H:i:s'),
                'item_type'       => $type === 'PRD' ? 'FG' : 'RAW',
                'item_sku'        => $sku,
                'item_name'       => $itemName,
                'uom'             => $item['unit'] ?? 'PCS',
                'movement_type'   => $adjType === 'PLUS' ? 'ADJUSTMENT_IN' : 'ADJUSTMENT_OUT',
                'qty_in'          => $adjType === 'PLUS' ? $qty : 0,
                'qty_out'         => $adjType === 'MINUS' ? $qty : 0,
                'balance_after'   => $adjType === 'PLUS' ? ($item['physical_stock'] + $qty) : ($item['physical_stock'] - $qty),
                'unit_cost'       => $item['hpp'],
                'total_value'     => $financialValue,
                'reference_no'    => 'ADJ-' . date('YmdHis'),
                'reference_table' => 'stock_adjustments',
                'reference_id'    => $adjustmentId,
                'notes'           => $reason,
                'created_by'      => $picName
            ]);

            $this->db->transComplete();

            if ($this->db->transStatus() === false) throw new \Exception("Gagal memproses penyesuaian stok.");

            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['status' => 'success', 'message' => "Stok berhasil disesuaikan! Dokumen tercatat oleh: {$picName}."]);
            }
            return redirect()->to('/warehouse/local-inventory?tab=adj')->with('success', 'Stok berhasil disesuaikan.');

        } catch (\Exception $e) {
            if ($this->request->isAJAX()) return $this->response->setJSON(['status' => 'error', 'message' => $e->getMessage()]);
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function delete_fg($id) {
        $item = $this->db->table('warehouse_inventory')->where('id', $id)->get()->getRowArray();
        if (!$item) return redirect()->back()->with('error', 'Data tidak ditemukan.');

        $sku = $item['sku'];

        $boms = $this->db->table('bom_headers')->where('fg_sku', $sku)->get()->getResultArray();
        
        foreach ($boms as $bom) {
            $cekSpk = $this->db->table('work_orders')->where('bom_id', $bom['id'])->countAllResults();
            if ($cekSpk > 0) {
                return redirect()->back()->with('error', "<b>Ditolak (Data Terkunci)!</b> Produk <b>{$sku}</b> tidak bisa dihapus karena sudah memiliki riwayat Surat Perintah Kerja (SPK) di Pabrik.");
            }
        }

        try {
            $this->db->transStart();

            foreach ($boms as $bom) {
                $this->db->table('bom_items')->where('bom_id', $bom['id'])->delete();
                $this->db->table('bom_operations')->where('bom_id', $bom['id'])->delete();
                $this->db->table('bom_headers')->where('id', $bom['id'])->delete();
            }

            $this->db->table('warehouse_inventory')->where('id', $id)->delete();

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                throw new \Exception("Gagal menghapus data dari database secara permanen.");
            }

            return redirect()->back()->with('success', "Produk <b>{$sku}</b> beserta Draft Resep-nya berhasil dihapus bersih dari sistem.");

        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function delete_rm($id) {
        $item = $this->db->table('raw_materials')->where('id', $id)->get()->getRowArray();
        if (!$item) return redirect()->back()->with('error', 'Data tidak ditemukan.');

        $cekBomItem = $this->db->table('bom_items')->where('rm_sku', $item['sku_material'])->countAllResults();
        if ($cekBomItem > 0) {
            return redirect()->to(base_url('/warehouse/local-inventory?tab=rm'))->with('error', "<b>Ditolak!</b> Material <b>{$item['sku_material']}</b> sedang menjadi komponen wajib di {$cekBomItem} Resep Produksi (BoM).");
        }

        $this->db->table('raw_materials')->where('id', $id)->delete();
        return redirect()->to(base_url('/warehouse/local-inventory?tab=rm'))->with('success', "Bahan Baku {$item['sku_material']} berhasil dihapus.");
    }
}