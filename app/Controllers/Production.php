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

        $workOrders = $this->db->table('work_orders')
            ->select('work_orders.*, bom_headers.recipe_name, bom_headers.fg_sku')
            ->join('bom_headers', 'bom_headers.id = work_orders.bom_id')
            ->orderBy('work_orders.id', 'DESC')
            ->get()->getResultArray();

        $boms = $this->db->table('bom_headers')->get()->getResultArray();
        
        $workers = $this->db->table('employees')
            ->where('is_active', 1)
            ->orderBy('name', 'ASC')
            ->get()->getResultArray();

        $productionLogs = $this->db->table('production_logs')
            ->select('production_logs.*, employees.name as employee_name, employees.status as emp_status, warehouse_inventory.item_name')
            ->join('employees', 'employees.employee_id = production_logs.employee_id')
            ->join('warehouse_inventory', 'warehouse_inventory.sku = production_logs.sku', 'left')
            ->orderBy('production_logs.id', 'DESC')
            ->limit(100)
            ->get()->getResultArray();

        $data = [
            'title'      => 'Command Center Produksi',
            'workOrders' => $workOrders,
            'boms'       => $boms,
            'workers'    => $workers,
            'logs'       => $productionLogs
        ];

        return view('production/index', $data);
    }

    // --- FITUR AJAX: Ambil Daftar Operasi ---
    public function get_operations($spkId = null)
    {
        if (!$spkId) return $this->response->setJSON(['status' => 'error', 'message' => 'ID SPK kosong. Coba muat ulang halaman.']);

        $spk = $this->db->table('work_orders')->where('id', $spkId)->get()->getRowArray();
        if(!$spk) return $this->response->setJSON(['status' => 'error', 'message' => 'Dokumen SPK tidak ditemukan di database.']);

        $operations = $this->db->table('bom_operations')
            ->where('bom_id', $spk['bom_id'])
            ->orderBy('step_order', 'ASC')
            ->get()->getResultArray();

        if (empty($operations)) {
            return $this->response->setJSON(['status' => 'error', 'message' => '❌ SPK LAMA: Resep ini tidak punya tahapan kerja. Terbitkan SPK Baru dengan Resep yang sudah diupdate!']);
        }

        return $this->response->setJSON(['status' => 'success', 'data' => $operations]);
    }

    // --- 2. HALAMAN BOM BUILDER ---
    public function bom_builder()
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/portal');

        $finishedGoods = $this->db->table('warehouse_inventory')->orderBy('item_name', 'ASC')->get()->getResultArray();
        $rawMaterials  = $this->db->table('raw_materials')->orderBy('material_name', 'ASC')->get()->getResultArray();

        $data = [
            'title'         => 'Formulasi Resep Produksi (BoM)',
            'finishedGoods' => $finishedGoods,
            'rawMaterials'  => $rawMaterials
        ];

        return view('production/bom_builder', $data);
    }

    // --- 3. SIMPAN RESEP (BOM & OPERATIONS) ---
    public function store_bom()
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/portal');

        try {
            $this->db->transStart();

            $fgSku = $this->request->getPost('fg_sku');
            $recipeName = $this->request->getPost('recipe_name');
            $rmSkus = $this->request->getPost('rm_sku'); 
            $qtys = $this->request->getPost('qty'); 
            $opNames = $this->request->getPost('op_name');
            $opWages = $this->request->getPost('op_wage');

            if(empty($rmSkus)) throw new \Exception("Pilih minimal 1 komponen material untuk resep ini.");
            if(empty($opNames)) throw new \Exception("Buat minimal 1 tahapan kerja untuk resep ini.");

            $this->db->table('bom_headers')->insert([
                'fg_sku'      => $fgSku,
                'recipe_name' => $recipeName
            ]);
            $bomId = $this->db->insertID();

            for ($i = 0; $i < count($rmSkus); $i++) {
                if(!empty($rmSkus[$i]) && !empty($qtys[$i])) {
                    $this->db->table('bom_items')->insert([
                        'bom_id'       => $bomId,
                        'rm_sku'       => $rmSkus[$i],
                        'qty_required' => (float)$qtys[$i]
                    ]);
                }
            }

            $totalOps = count($opNames);
            for ($j = 0; $j < $totalOps; $j++) {
                if(!empty($opNames[$j])) {
                    $wage = (float) str_replace(['Rp', '.', ' '], '', $opWages[$j] ?? '0');
                    $isFinal = ($j == ($totalOps - 1)) ? 1 : 0; 
                    
                    $this->db->table('bom_operations')->insert([
                        'bom_id'         => $bomId,
                        'step_order'     => $j + 1,
                        'operation_name' => $opNames[$j],
                        'wage_per_piece' => $wage,
                        'is_final_step'  => $isFinal
                    ]);
                }
            }

            $this->db->transComplete();

            if ($this->db->transStatus() === false) throw new \Exception("Gagal menyimpan resep ke database.");

            return redirect()->to('/production')->with('success', 'Formulasi Resep (BoM) beserta tahapan kerjanya berhasil diciptakan!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    // --- 4. BUAT SURAT PERINTAH KERJA (SPK) ---
    public function create_spk()
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/portal');

        try {
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

            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['status' => 'success', 'message' => "Surat Perintah Kerja $spkNumber berhasil diterbitkan!"]);
            }
            return redirect()->back()->with('success', "Surat Perintah Kerja $spkNumber berhasil diterbitkan!");
        } catch (\Exception $e) {
            if ($this->request->isAJAX()) return $this->response->setJSON(['status' => 'error', 'message' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Gagal membuat SPK: ' . $e->getMessage());
        }
    }

    // --- 5. INPUT HASIL PRODUKSI (MULTI-STAGE) ---
    public function add_production_log()
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/portal');

        try {
            $this->db->transStart();

            $spkId = $this->request->getPost('spk_id');
            $employeeId = $this->request->getPost('employee_id'); 
            $operationId = $this->request->getPost('operation_id'); 
            $qtyProduced = (int)$this->request->getPost('qty_produced');
            
            $overheadRaw = str_replace('.', '', $this->request->getPost('overhead_cost') ?? '0');
            $overheadHarian = (float) str_replace(',', '.', $overheadRaw);

            $customWageRaw = $this->request->getPost('custom_wage');
            $customWage = (float) str_replace(['Rp', '.', ' '], '', $customWageRaw ?? '0');

            if ($qtyProduced <= 0) throw new \Exception("Jumlah barang diselesaikan tidak valid.");

            $spk = $this->db->table('work_orders')->where('id', $spkId)->get()->getRowArray();
            if (!$spk || $spk['status'] === 'COMPLETED') throw new \Exception("SPK tidak valid atau sudah selesai.");

            $bom = $this->db->table('bom_headers')->where('id', $spk['bom_id'])->get()->getRowArray();
            $fgSku = $bom['fg_sku'];
            
            $operation = $this->db->table('bom_operations')->where('id', $operationId)->get()->getRowArray();
            if (!$operation || $operation['bom_id'] != $bom['id']) throw new \Exception("Tahapan operasi tidak valid.");

            $employee = $this->db->table('employees')->where('employee_id', $employeeId)->get()->getRowArray();
            if (!$employee) throw new \Exception("Data Karyawan tidak ditemukan.");

            // PENTING: DETEKSI PEKERJA TETAP ATAU BORONGAN
            $isTetap = (stripos($employee['status'], 'Tetap') !== false || stripos($employee['salary_type'], 'Bulan') !== false);
            
            // Karyawan tetap = Upah Borongan 0
            $laborRate = $isTetap ? 0.00 : (($customWage > 0) ? $customWage : (float)$operation['wage_per_piece']);

            $totalMaterialCost = 0;

            // POTONG MATERIAL JIKA TAHAP FINAL (MASUK GUDANG)
            if ($operation['is_final_step'] == 1) {
                $bomItems = $this->db->table('bom_items')->where('bom_id', $bom['id'])->get()->getResultArray();
                
                foreach ($bomItems as $item) {
                    $totalRmNeeded = $item['qty_required'] * $qtyProduced; 
                    $skuKomponen = $item['rm_sku'];

                    $rmStock = $this->db->table('raw_materials')->where('sku_material', $skuKomponen)->get()->getRowArray();
                    
                    if ($rmStock) {
                        if ($rmStock['physical_stock'] < $totalRmNeeded) {
                            throw new \Exception("GAGAL! Stok Bahan Mentah <b>{$skuKomponen}</b> tidak cukup.");
                        }
                        $totalMaterialCost += ($totalRmNeeded * $rmStock['hpp']);
                        $this->db->query("UPDATE raw_materials SET physical_stock = physical_stock - ? WHERE sku_material = ?", [$totalRmNeeded, $skuKomponen]);
                    } 
                    else {
                        $fgStock = $this->db->table('warehouse_inventory')->where('sku', $skuKomponen)->get()->getRowArray();
                        if ($fgStock) {
                            if ($fgStock['physical_stock'] < $totalRmNeeded) {
                                throw new \Exception("GAGAL! Stok Sub-Komponen <b>{$skuKomponen}</b> tidak cukup untuk dirakit.");
                            }
                            $totalMaterialCost += ($totalRmNeeded * $fgStock['hpp']);
                            $this->db->query("UPDATE warehouse_inventory SET physical_stock = physical_stock - ? WHERE sku = ?", [$totalRmNeeded, $skuKomponen]);
                        } else {
                            throw new \Exception("GAGAL CRITICAL! Komponen <b>{$skuKomponen}</b> tidak ditemukan di gudang.");
                        }
                    }
                }
            }

            // CATAT LOG PRODUKSI
            $totalWage = $qtyProduced * $laborRate;
            $this->db->table('production_logs')->insert([
                'sku'             => $fgSku,
                'spk_number'      => $spk['spk_number'],
                'employee_id'     => $employeeId,
                'operation_name'  => $operation['operation_name'], 
                'is_final_step'   => $operation['is_final_step'],
                'qty_produced'    => $qtyProduced,
                'wage_per_piece'  => $laborRate,
                'total_wage'      => $totalWage,
                'production_date' => date('Y-m-d H:i:s')
            ]);

            // KALKULASI HPP BARU (MOVING AVERAGE) SAAT FINAL STEP
            if ($operation['is_final_step'] == 1) {
                $sumWages = $this->db->table('bom_operations')->where('bom_id', $bom['id'])->selectSum('wage_per_piece')->get()->getRowArray();
                $totalWagePerPcsAllSteps = (float)$sumWages['wage_per_piece'];
                
                $materialUnitCost = ($qtyProduced > 0) ? ($totalMaterialCost / $qtyProduced) : 0;
                $overheadUnitCost = ($qtyProduced > 0) ? ($overheadHarian / $qtyProduced) : 0;
                
                $unitHppBaru = $materialUnitCost + $totalWagePerPcsAllSteps + $overheadUnitCost;
                
                $newAverageHpp = 0;
                $fgStockInfo = $this->db->table('warehouse_inventory')->where('sku', $fgSku)->get()->getRowArray();
                if ($fgStockInfo) {
                    $oldQty = $fgStockInfo['physical_stock'];
                    $oldHpp = $fgStockInfo['hpp'];
                    $newQty = $qtyProduced;
                    $totalQty = $oldQty + $newQty;
                    
                    if ($totalQty > 0) {
                        $newAverageHpp = (($oldQty * $oldHpp) + ($newQty * $unitHppBaru)) / $totalQty;
                    }
                    $this->db->query("UPDATE warehouse_inventory SET physical_stock = ?, hpp = ? WHERE sku = ?", [$totalQty, $newAverageHpp, $fgSku]);
                }
            }

            // UPDATE PROGRESS BIAYA SPK
            $this->db->query("
                UPDATE work_orders 
                SET material_cost = material_cost + ?, labor_cost = labor_cost + ?, overhead_cost = overhead_cost + ?
                WHERE id = ?
            ", [$totalMaterialCost, $totalWage, $overheadHarian, $spkId]);

            // CEK STATUS SPK (SELESAI JIKA FINAL STEP MENCAPAI TARGET)
            $totalSetoranSpkFinal = $this->db->table('production_logs')
                ->where('spk_number', $spk['spk_number'])
                ->where('is_final_step', 1)
                ->selectSum('qty_produced')
                ->get()->getRowArray()['qty_produced'];
            
            if ($totalSetoranSpkFinal >= $spk['planned_qty']) {
                $latestSpk = $this->db->table('work_orders')->where('id', $spkId)->get()->getRowArray();
                $finalTotalCost = $latestSpk['material_cost'] + $latestSpk['labor_cost'] + $latestSpk['overhead_cost'];
                $actualHppFinal = ($spk['planned_qty'] > 0) ? ($finalTotalCost / $spk['planned_qty']) : 0;

                $this->db->table('work_orders')->where('id', $spkId)->update([
                    'status'       => 'COMPLETED',
                    'completed_at' => date('Y-m-d H:i:s'),
                    'actual_hpp'   => $actualHppFinal
                ]);
            }

            // JURNAL AKUNTANSI
            $invPRD = $this->db->table('chart_of_accounts')->where('account_code', '1-3000')->get()->getRowArray(); 
            $bebanGaji = $this->db->table('chart_of_accounts')->where('account_code', '5-2000')->get()->getRowArray(); 
            $hutangGaji = $this->db->table('chart_of_accounts')->where('account_code', '2-1000')->get()->getRowArray(); 

            if ($invPRD && ($totalMaterialCost > 0 || $totalWage > 0)) {
                $desc = ($operation['is_final_step'] == 1) ? "Barang Masuk Gudang SPK: {$spk['spk_number']}" : "Biaya Upah ({$operation['operation_name']}) SPK: {$spk['spk_number']}";
                
                $this->db->table('journals')->insert([
                    'journal_number'   => 'JRN-MFG-'.time(),
                    'transaction_date' => date('Y-m-d'),
                    'description'      => $desc . " - " . $employee['name'],
                    'total_amount'     => $totalMaterialCost + $totalWage,
                    'created_by'       => session()->get('name') ?? 'System'
                ]);
                $journalId = $this->db->insertID();

                if ($operation['is_final_step'] == 1 && $totalMaterialCost > 0) {
                    $hppTotal = $totalMaterialCost + $totalWage;
                    $this->db->table('journal_items')->insert(['journal_id' => $journalId, 'account_id' => $invPRD['id'], 'line_description' => 'Produk Jadi (Masuk)', 'debit' => $hppTotal, 'credit' => 0]);
                    $this->db->table('journal_items')->insert(['journal_id' => $journalId, 'account_id' => $invPRD['id'], 'line_description' => 'Material (Keluar)', 'debit' => 0, 'credit' => $totalMaterialCost]);
                }

                if ($totalWage > 0 && $bebanGaji && $hutangGaji) {
                    $this->db->table('journal_items')->insert(['journal_id' => $journalId, 'account_id' => $bebanGaji['id'], 'line_description' => 'Beban Upah Pabrik', 'debit' => $totalWage, 'credit' => 0]);
                    $this->db->table('journal_items')->insert(['journal_id' => $journalId, 'account_id' => $hutangGaji['id'], 'line_description' => 'Hutang Upah', 'debit' => 0, 'credit' => $totalWage]);
                }
            }

            $this->db->transComplete();

            if ($this->db->transStatus() === false) throw new \Exception("Kegagalan Database saat mencatat aktivitas produksi.");

            $pesan = "Setoran tahap <b>{$operation['operation_name']}</b> ({$qtyProduced} Pcs) berhasil direkam.";
            if($isTetap) $pesan .= "<br><span style='font-size:11px; color:#f59e0b;'><i class='ph-fill ph-info'></i> Karyawan Tetap: Beban upah borongan = Rp 0.</span>";
            if($operation['is_final_step'] == 1) $pesan .= "<br><span style='font-size:11px; color:#10b981;'><i class='ph-fill ph-check-circle'></i> Material dipotong, Stok {$fgSku} bertambah ke Gudang.</span>";
            
            return redirect()->back()->with('success', $pesan);

        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}