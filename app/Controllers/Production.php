<?php

namespace App\Controllers;

class Production extends BaseController
{
    private $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    // --- 1. DASBOR PABRIK & SPK ---
    public function index()
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/portal');

        // Ambil daftar SPK
        $workOrders = $this->db->table('work_orders')
            ->select('work_orders.*, bom_headers.recipe_name, bom_headers.fg_sku')
            ->join('bom_headers', 'bom_headers.id = work_orders.bom_id')
            ->orderBy('work_orders.id', 'DESC')
            ->get()->getResultArray();

        // Ambil daftar Resep (BoM)
        $boms = $this->db->table('bom_headers')->get()->getResultArray();

        $data = [
            'title'      => 'Command Center Produksi',
            'workOrders' => $workOrders,
            'boms'       => $boms
        ];

        return view('production/index', $data);
    }

    // --- 2. HALAMAN BOM BUILDER (RESEP) ---
    public function bom_builder()
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/portal');

        // 1. Ambil Barang Jadi (Knalpot) -> Dari tabel warehouse_inventory
        $finishedGoods = $this->db->table('warehouse_inventory')
                                  ->like('sku', 'PRD-', 'after')
                                  ->orderBy('item_name', 'ASC')
                                  ->get()->getResultArray();
                                  
        // 2. PERBAIKAN: Ambil Bahan Baku Mentah -> Dari tabel raw_materials (BUKAN warehouse_inventory)
        $rawMaterials  = $this->db->table('raw_materials')
                                  ->like('sku_material', 'MAT-', 'after')
                                  ->orderBy('material_name', 'ASC')
                                  ->get()->getResultArray();

        $data = [
            'title'         => 'Bill of Materials Builder',
            'finishedGoods' => $finishedGoods,
            'rawMaterials'  => $rawMaterials
        ];

        return view('production/bom_builder', $data);
    }
    // --- 3. SIMPAN RESEP (BOM) ---
    public function store_bom()
    {
        try {
            $this->db->transStart();

            $fgSku = $this->request->getPost('fg_sku');
            $recipeName = $this->request->getPost('recipe_name');
            $rmSkus = $this->request->getPost('rm_sku'); // Array
            $qtys = $this->request->getPost('qty'); // Array

            if(empty($rmSkus)) throw new \Exception("Pilih minimal 1 material dasar untuk resep ini.");

            // Insert Header
            $this->db->table('bom_headers')->insert([
                'fg_sku'      => $fgSku,
                'recipe_name' => $recipeName
            ]);
            $bomId = $this->db->insertID();

            // Insert Items (Material)
            for ($i = 0; $i < count($rmSkus); $i++) {
                if(!empty($rmSkus[$i]) && !empty($qtys[$i])) {
                    $this->db->table('bom_items')->insert([
                        'bom_id'       => $bomId,
                        'rm_sku'       => $rmSkus[$i],
                        'qty_required' => (float)$qtys[$i]
                    ]);
                }
            }

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                throw new \Exception("Gagal menyimpan resep ke database.");
            }

            return redirect()->to('/production')->with('success', 'Formulasi Resep (BoM) berhasil diciptakan!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    // --- 4. BUAT SURAT PERINTAH KERJA (SPK) ---
    public function create_spk()
    {
        try {
            // Auto Generate SPK Number
            $dateStr = date('Ymd');
            $lastSpk = $this->db->table('work_orders')->like('spk_number', "SPK-$dateStr", 'after')->orderBy('id', 'DESC')->get()->getRowArray();
            $seq = 1;
            if ($lastSpk) {
                $parts = explode('-', $lastSpk['spk_number']);
                $seq = intval(end($parts)) + 1;
            }
            $spkNumber = "SPK-" . $dateStr . "-" . str_pad($seq, 3, '0', STR_PAD_LEFT);

            $this->db->table('work_orders')->insert([
                'spk_number'  => $spkNumber,
                'bom_id'      => $this->request->getPost('bom_id'),
                'planned_qty' => (int)$this->request->getPost('planned_qty'),
                'status'      => 'IN_PROGRESS',
                'start_date'  => date('Y-m-d')
            ]);

            return redirect()->back()->with('success', "Surat Perintah Kerja $spkNumber berhasil diterbitkan untuk pabrik!");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal membuat SPK: ' . $e->getMessage());
        }
    }

   // --- 5. EKSEKUSI JANTUNG PABRIK: SELESAIKAN SPK & POTONG STOK ---
    public function complete_spk($spkId)
    {
        try {
            $this->db->transStart();

            // 1. Ambil data SPK
            $spk = $this->db->table('work_orders')->where('id', $spkId)->get()->getRowArray();
            if (!$spk || $spk['status'] === 'COMPLETED') {
                throw new \Exception("SPK tidak valid atau sudah selesai.");
            }

            // 2. Ambil Header Resep
            $bom = $this->db->table('bom_headers')->where('id', $spk['bom_id'])->get()->getRowArray();
            $fgSku = $bom['fg_sku'];
            $targetQty = $spk['planned_qty'];

            // 3. Ambil Rincian Material Dasar
            $bomItems = $this->db->table('bom_items')->where('bom_id', $bom['id'])->get()->getResultArray();

            // 4. CEK KETERSEDIAAN SEMUA MATERIAL DULU!
            foreach ($bomItems as $item) {
                $totalRmNeeded = $item['qty_required'] * $targetQty;
                $rmStock = $this->db->table('warehouse_inventory')->where('sku', $item['rm_sku'])->get()->getRowArray();
                
                // Jika tidak ketemu di warehouse_inventory, cari di raw_materials (tergantung arsitektur database Anda)
                if (!$rmStock) {
                    $rmStock = $this->db->table('raw_materials')->where('sku_material', $item['rm_sku'])->get()->getRowArray();
                }
                
                $physStock = $rmStock['physical_stock'] ?? 0;
                
                if (!$rmStock || $physStock < $totalRmNeeded) {
                    throw new \Exception("GAGAL! Stok Material <b>{$item['rm_sku']}</b> tidak cukup. Dibutuhkan: $totalRmNeeded, Sisa: $physStock.");
                }
            }

            // 5. PROSES PEMOTONGAN MATERIAL JIKA STOK AMAN
            foreach ($bomItems as $item) {
                $totalRmNeeded = $item['qty_required'] * $targetQty;
                // Update di tabel yang relevan (sesuaikan jika raw_materials pisah tabel)
                $this->db->query("UPDATE warehouse_inventory SET physical_stock = physical_stock - ? WHERE sku = ?", [$totalRmNeeded, $item['rm_sku']]);
                $this->db->query("UPDATE raw_materials SET physical_stock = physical_stock - ? WHERE sku_material = ?", [$totalRmNeeded, $item['rm_sku']]);
            }

            // 6. PROSES PENAMBAHAN PRODUK JADI (PRD)
            $this->db->query("UPDATE warehouse_inventory SET physical_stock = physical_stock + ? WHERE sku = ?", [$targetQty, $fgSku]);

            // 7. Update Status SPK
            $this->db->table('work_orders')->where('id', $spkId)->update([
                'status' => 'COMPLETED',
                'completed_at' => date('Y-m-d H:i:s')
            ]);

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                throw new \Exception("Terjadi kegagalan koneksi saat menyelaraskan stok pabrik.");
            }

            return redirect()->back()->with('success', "🔥 SPK Selesai! Stok Produk bertambah <b>+$targetQty</b> dan Material otomatis terpotong.");

        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}