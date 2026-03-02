<?php

namespace App\Controllers;

class LocalWarehouse extends BaseController
{
    private $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    // --- HELPER: AUTO GENERATE SKU (ENTERPRISE STANDARD) ---
    private function generateSKU($type = 'PRD') 
    {
        // PRD = Product (Barang Jadi), MAT = Material (Bahan Baku Mentah)
        $prefix = ($type === 'PRD') ? 'PRD-' : 'MAT-';
        $table  = ($type === 'PRD') ? 'warehouse_inventory' : 'raw_materials';
        $column = ($type === 'PRD') ? 'sku' : 'sku_material';

        // Ambil SKU terakhir berdasarkan prefix tersebut
        $lastItem = $this->db->table($table)
                             ->like($column, $prefix, 'after')
                             ->orderBy('id', 'DESC')
                             ->get()
                             ->getRowArray();
        
        if (!$lastItem) {
            return $prefix . '0001';
        }

        // Pecah SKU terakhir berdasarkan tanda strip "-"
        $lastSku = $lastItem[$column];
        $parts = explode('-', $lastSku);
        
        // Ambil bagian paling belakang (angkanya)
        $lastNumber = intval(end($parts)); 
        
        // Tambah 1 dan format kembali menjadi 4 digit (0046)
        $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        
        return $prefix . $newNumber;
    }

    // --- 1. HALAMAN MASTER DATA GUDANG (TABBED) ---
    public function index()
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/portal');

        // Tarik Data Barang Jadi (Finished Goods -> PRD)
        $finishedGoods = $this->db->table('warehouse_inventory')->orderBy('id', 'DESC')->get()->getResultArray();
        
        // Tarik Data Bahan Baku (Raw Materials -> MAT)
        $rawMaterials = $this->db->table('raw_materials')->orderBy('id', 'DESC')->get()->getResultArray();

        // Kalkulasi Aset
        $totalValueFG = 0; foreach($finishedGoods as $f) { $totalValueFG += ($f['physical_stock'] * $f['hpp']); }
        $totalValueRM = 0; foreach($rawMaterials as $r) { $totalValueRM += ($r['physical_stock'] * $r['hpp']); }

        $data = [
            'title'         => 'Master Inventaris Terpusat',
            'finishedGoods' => $finishedGoods,
            'rawMaterials'  => $rawMaterials,
            'totalValueFG'  => $totalValueFG,
            'totalValueRM'  => $totalValueRM
        ];

        return view('warehouse/local_inventory', $data);
    }

    // --- 2. TAMBAH BARANG JADI (KNALPOT -> PRD) ---
    public function store_fg()
    {
        try {
            $autoSku = $this->generateSKU('PRD');
            
            $data = [
                'sku'            => $autoSku,
                'item_name'      => $this->request->getPost('item_name'),
                'item_type'      => 'Product',
                'hpp'            => (float) str_replace(['Rp', '.', ' '], '', $this->request->getPost('hpp')),
                'physical_stock' => (int)$this->request->getPost('initial_stock'),
                'min_stock'      => (int)$this->request->getPost('min_stock')
            ];

            $this->db->table('warehouse_inventory')->insert($data);
            return redirect()->back()->with('success', "Barang Produksi (PRD) berhasil disimpan dengan SKU: <b>{$autoSku}</b>");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    // --- 3. TAMBAH BAHAN BAKU (PIPA/PLAT -> MAT) ---
    public function store_rm()
    {
        try {
            $autoSku = $this->generateSKU('MAT');
            
            $data = [
                'sku_material'   => $autoSku,
                'material_name'  => $this->request->getPost('material_name'),
                'unit'           => $this->request->getPost('unit'),
                'hpp'            => (float) str_replace(['Rp', '.', ' '], '', $this->request->getPost('hpp')),
                'physical_stock' => (float)$this->request->getPost('initial_stock'),
                'min_stock'      => (float)$this->request->getPost('min_stock')
            ];

            $this->db->table('raw_materials')->insert($data);
            return redirect()->back()->with('success', "Material Bahan Baku (MAT) berhasil disimpan dengan SKU: <b>{$autoSku}</b>");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    // --- 4. HAPUS DATA ---
    public function delete_fg($id) {
        $this->db->table('warehouse_inventory')->where('id', $id)->delete();
        return redirect()->back()->with('success', 'Barang Jadi (PRD) dihapus.');
    }
    public function delete_rm($id) {
        $this->db->table('raw_materials')->where('id', $id)->delete();
        return redirect()->back()->with('success', 'Bahan Baku (MAT) dihapus.');
    }
}