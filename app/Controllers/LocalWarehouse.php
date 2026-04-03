<?php

namespace App\Controllers;

class LocalWarehouse extends BaseController
{
    private $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    // --- HELPER: AUTO GENERATE SMART SKU (BERDASARKAN KATEGORI & NAMA) ---
    private function generateSKU($type, $context) 
    {
        if ($type === 'PRD') {
            $table  = 'warehouse_inventory';
            $column = 'sku';
            
            // Mapping Singkatan Kategori Produk
            $map = [
                'Full System'           => 'FSY',
                'Silencer / Slip-on'    => 'SLC',
                'Header / Leheran'      => 'LHR',
                'Aksesoris / Sparepart' => 'ACC'
            ];
            $midCode = $map[$context] ?? 'GEN'; 
            $prefix = "PRD-{$midCode}-";
            
        } else {
            $table  = 'raw_materials';
            $column = 'sku_material';
            
            // Ambil 3 huruf alfabet pertama dari Nama Material
            $cleanName = preg_replace('/[^A-Za-z]/', '', $context);
            $midCode = strtoupper(substr($cleanName, 0, 3));
            
            // Jika kurang dari 3 huruf, tambahkan 'X'
            if (strlen($midCode) < 3) {
                $midCode = str_pad($midCode, 3, 'X'); 
            }
            $prefix = "MAT-{$midCode}-";
        }

        // Cari nomor urut terakhir berdasarkan prefix spesifik ini
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

    // --- 1. HALAMAN MASTER DATA GUDANG ---
    public function index()
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/portal');

        $finishedGoods = $this->db->table('warehouse_inventory')->orderBy('id', 'DESC')->get()->getResultArray();
        $rawMaterials = $this->db->table('raw_materials')->orderBy('id', 'DESC')->get()->getResultArray();
        
        $adjustments = $this->db->table('stock_adjustments')->orderBy('id', 'DESC')->get()->getResultArray();

        $totalValueFG = 0; foreach($finishedGoods as $f) { $totalValueFG += ($f['physical_stock'] * $f['hpp']); }
        $totalValueRM = 0; foreach($rawMaterials as $r) { $totalValueRM += ($r['physical_stock'] * $r['hpp']); }

        $data = [
            'title'         => 'Master Inventaris Terpusat',
            'finishedGoods' => $finishedGoods,
            'rawMaterials'  => $rawMaterials,
            'adjustments'   => $adjustments,
            'totalValueFG'  => $totalValueFG,
            'totalValueRM'  => $totalValueRM
        ];

        return view('warehouse/local_inventory', $data);
    }

    // --- 2. TAMBAH BARANG JADI (PRD) ---
    public function store_fg()
    {
        try {
            $itemType = $this->request->getPost('item_type');
            // Generate SKU berdasarkan Kategori (item_type)
            $autoSku = $this->generateSKU('PRD', $itemType);
            
            $data = [
                'sku'             => $autoSku,
                'item_name'       => $this->request->getPost('item_name'),
                'item_type'       => $itemType,
                'hpp'             => (float) str_replace(['Rp', '.', ' '], '', $this->request->getPost('hpp')),
                'retail_price'    => (float) str_replace(['Rp', '.', ' '], '', $this->request->getPost('retail_price')),
                'wholesale_price' => (float) str_replace(['Rp', '.', ' '], '', $this->request->getPost('wholesale_price')),
                'physical_stock'  => (int)$this->request->getPost('initial_stock'),
                'min_stock'       => (int)$this->request->getPost('min_stock')
            ];

            $this->db->table('warehouse_inventory')->insert($data);

            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['status' => 'success', 'message' => "Produk Jadi berhasil disimpan dengan SKU: $autoSku"]);
            }
            return redirect()->back()->with('success', "Barang Produksi (PRD) berhasil disimpan dengan SKU: <b>{$autoSku}</b>");
        } catch (\Exception $e) {
            if ($this->request->isAJAX()) return $this->response->setJSON(['status' => 'error', 'message' => $e->getMessage()]);
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    // --- 3. TAMBAH BAHAN BAKU (MAT) ---
    public function store_rm()
    {
        try {
            $materialName = $this->request->getPost('material_name');
            // Generate SKU berdasarkan Nama Material
            $autoSku = $this->generateSKU('MAT', $materialName);
            
            $data = [
                'sku_material'   => $autoSku,
                'material_name'  => $materialName,
                'unit'           => $this->request->getPost('unit'),
                'hpp'            => (float) str_replace(['Rp', '.', ' '], '', $this->request->getPost('hpp')),
                'physical_stock' => (float)$this->request->getPost('initial_stock'),
                'min_stock'      => (float)$this->request->getPost('min_stock')
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

    // --- FITUR: AMBIL DATA UNTUK FORM EDIT (AJAX) ---
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

    // --- FITUR: UPDATE PERUBAHAN DATA KE DATABASE ---
    public function update_fg($id)
    {
        try {
            $data = [
                'item_name'       => $this->request->getPost('item_name'),
                'item_type'       => $this->request->getPost('item_type'),
                'hpp'             => (float) str_replace(['Rp', '.', ' '], '', $this->request->getPost('hpp')),
                'retail_price'    => (float) str_replace(['Rp', '.', ' '], '', $this->request->getPost('retail_price')),
                'wholesale_price' => (float) str_replace(['Rp', '.', ' '], '', $this->request->getPost('wholesale_price')),
                'min_stock'       => (int)$this->request->getPost('min_stock')
            ];

            $this->db->table('warehouse_inventory')->where('id', $id)->update($data);

            if ($this->request->isAJAX()) return $this->response->setJSON(['status' => 'success', 'message' => "Data Produk berhasil diperbarui."]);
            return redirect()->back()->with('success', "Data Produk berhasil diperbarui.");
        } catch (\Exception $e) {
            if ($this->request->isAJAX()) return $this->response->setJSON(['status' => 'error', 'message' => $e->getMessage()]);
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function update_rm($id)
    {
        try {
            $data = [
                'material_name'  => $this->request->getPost('material_name'),
                'unit'           => $this->request->getPost('unit'),
                'hpp'            => (float) str_replace(['Rp', '.', ' '], '', $this->request->getPost('hpp')),
                'min_stock'      => (float)$this->request->getPost('min_stock')
            ];

            $this->db->table('raw_materials')->where('id', $id)->update($data);

            if ($this->request->isAJAX()) return $this->response->setJSON(['status' => 'success', 'message' => "Data Material berhasil diperbarui."]);
            return redirect()->back()->with('success', "Data Material berhasil diperbarui.");
        } catch (\Exception $e) {
            if ($this->request->isAJAX()) return $this->response->setJSON(['status' => 'error', 'message' => $e->getMessage()]);
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    // --- 4. EKSEKUSI STOCK OPNAME (PENYESUAIAN STOK) ---
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
                
                // Murni catat Jurnal Saja (View v_account_balances akan otomatis menyesuaikan)
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

    // --- 5. HAPUS DATA DENGAN PROTEKSI INTEGRITAS ---
    public function delete_fg($id) {
        $item = $this->db->table('warehouse_inventory')->where('id', $id)->get()->getRowArray();
        if (!$item) return redirect()->back()->with('error', 'Data tidak ditemukan.');

        $cekBom = $this->db->table('bom_headers')->where('fg_sku', $item['sku'])->countAllResults();
        if ($cekBom > 0) {
            return redirect()->back()->with('error', "<b>Ditolak!</b> Produk <b>{$item['sku']}</b> sedang digunakan dalam {$cekBom} Resep Produksi (BoM). Hapus resep terkait terlebih dahulu.");
        }

        $this->db->table('warehouse_inventory')->where('id', $id)->delete();
        return redirect()->back()->with('success', "Produk Jadi {$item['sku']} berhasil dihapus.");
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