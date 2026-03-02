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

        // Pisahkan Barang Jadi (FG) dan Bahan Baku (RM) berdasarkan awalan SKU (Asumsi standar: FG- dan RM-)
        // Atau ambil semua dan biarkan user memilih
        $finishedGoods = $this->db->table('warehouse_inventory')->like('sku', 'FG-', 'after')->get()->getResultArray();
        $rawMaterials  = $this->db->table('warehouse_inventory')->like('sku', 'RM-', 'after')->get()->getResultArray();

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

            if(empty($rmSkus)) throw new \Exception("Pilih minimal 1 bahan baku untuk resep ini.");

            // Insert Header
            $this->db->table('bom_headers')->insert([
                'fg_sku'      => $fgSku,
                'recipe_name' => $recipeName
            ]);
            $bomId = $this->db->insertID();

            // Insert Items (Bahan Baku)
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

            return redirect()->to('/production')->with('success', 'Resep (BoM) berhasil diciptakan!');
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

            return redirect()->back()->with('success', "Surat Perintah Kerja $spkNumber berhasil diterbitkan untuk bengkel!");
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

            // 2. Ambil Header Resep (Mengetahui Knalpot apa yang dibuat)
            $bom = $this->db->table('bom_headers')->where('id', $spk['bom_id'])->get()->getRowArray();
            $fgSku = $bom['fg_sku'];
            $targetQty = $spk['planned_qty'];

            // 3. Ambil Rincian Bahan Baku dari Resep
            $bomItems = $this->db->table('bom_items')->where('bom_id', $bom['id'])->get()->getResultArray();

            // 4. PROSES PEMOTONGAN BAHAN BAKU (RAW MATERIAL DEDUCTION)
            foreach ($bomItems as $item) {
                $totalRmNeeded = $item['qty_required'] * $targetQty;
                
                // Kurangi stok di gudang lokal
                $this->db->query("UPDATE warehouse_inventory SET physical_stock = physical_stock - ? WHERE sku = ?", [$totalRmNeeded, $item['rm_sku']]);
            }

            // 5. PROSES PENAMBAHAN BARANG JADI (FINISHED GOODS ADDITION)
            $this->db->query("UPDATE warehouse_inventory SET physical_stock = physical_stock + ? WHERE sku = ?", [$targetQty, $fgSku]);

            // 6. Update Status SPK
            $this->db->table('work_orders')->where('id', $spkId)->update([
                'status' => 'COMPLETED',
                'completed_at' => date('Y-m-d H:i:s')
            ]);

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                throw new \Exception("Terjadi kegagalan saat menyelaraskan stok pabrik.");
            }

            return redirect()->back()->with('success', "🔥 SPK Selesai! Stok Barang Jadi bertambah <b>+$targetQty</b> dan Bahan Baku otomatis terpotong.");

        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}