<?php

namespace App\Controllers;

use App\Services\InventoryService;
use App\Services\AccountingService;

class Production extends BaseController
{
    private $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    public function index()
    {
        // Cek apakah belum login, ATAU (bukan admin DAN bukan orang produksi)
        if (!session()->get('isLoggedIn') || (session()->get('role') !== 'admin' && !str_contains(strtolower(session()->get('department_name') ?? session()->get('department')), 'produksi'))) {
            return redirect()->to('/portal');
        }

        $workOrders = $this->db->table('work_orders')
            ->select('work_orders.*, bom_headers.recipe_name, bom_headers.fg_sku, b2b_sales_orders.so_number, b2b_customers.company_name')
            ->join('bom_headers', 'bom_headers.id = work_orders.bom_id')
            ->join('b2b_sales_orders', 'b2b_sales_orders.id = work_orders.so_id', 'left')
            ->join('b2b_customers', 'b2b_customers.id = b2b_sales_orders.customer_id', 'left')
            ->orderBy('work_orders.id', 'DESC')->get()->getResultArray();

        $spkManual = []; 
        $spkPreorder = []; 
        $spkPreorderGrouped = [];

        // FILTER: Jangan tampilkan SPK yang sudah COMPLETED ke tabel antrean
        foreach ($workOrders as $wo) {
            if (($wo['source'] ?? 'MANUAL') === 'PREORDER') {
                if ($wo['status'] !== 'COMPLETED') { 
                    $soId = $wo['so_id'] ?: '0'; 
                    $groupName = !empty($wo['company_name']) ? "{$wo['company_name']} ({$wo['so_number']})" : "Pre-Order Belum Teridentifikasi";
                    if(!isset($spkPreorderGrouped[$soId])) $spkPreorderGrouped[$soId] = ['so_id' => $soId, 'group_name' => $groupName, 'spks' => []];
                    $spkPreorderGrouped[$soId]['spks'][] = $wo;
                    $spkPreorder[] = $wo;
                }
            } else {
                if ($wo['status'] !== 'COMPLETED') {
                    $spkManual[] = $wo;
                }
            }
        }

        $boms = $this->db->table('bom_headers')->get()->getResultArray();
        
        $workers = $this->db->table('employees')
            ->select('employees.*, positions.name as position_name, departments.name as department_name')
            ->join('positions', 'positions.id = employees.position_id', 'left')
            ->join('departments', 'departments.id = employees.department_id', 'left')
            ->where('employees.is_active', 1)
            ->orderBy('employees.name', 'ASC')
            ->get()->getResultArray();

        $productionLogs = $this->db->table('production_logs')
            ->select('production_logs.*, employees.name as employee_name, employees.status as emp_status, warehouse_inventory.item_name')
            ->join('employees', 'employees.employee_id = production_logs.employee_id')
            ->join('warehouse_inventory', 'warehouse_inventory.sku = production_logs.sku', 'left')
            ->orderBy('production_logs.id', 'DESC')
            ->limit(100)
            ->get()->getResultArray();

        $pendingLogs = $this->db->table('production_logs')
            ->select('production_logs.*, employees.name as employee_name, warehouse_inventory.item_name')
            ->join('employees', 'employees.employee_id = production_logs.employee_id')
            ->join('warehouse_inventory', 'warehouse_inventory.sku = production_logs.sku', 'left')
            ->where('production_logs.status', 'Pending')
            ->orderBy('production_logs.id', 'ASC')
            ->get()->getResultArray();

        // AMBIL RIWAYAT SETORAN PEKERJA YANG SEDANG LOGIN
        $myLogs = $this->db->table('production_logs')
            ->select('production_logs.*, warehouse_inventory.item_name')
            ->join('warehouse_inventory', 'warehouse_inventory.sku = production_logs.sku', 'left')
            ->where('production_logs.employee_id', session()->get('employee_id'))
            ->orderBy('production_logs.id', 'DESC')
            ->limit(50)
            ->get()->getResultArray();

        $currentUser = $this->db->table('employees')->where('employee_id', session()->get('employee_id'))->get()->getRowArray();
        $userSpecialty = $currentUser['specialty'] ?? '';
        $userEmpId = $currentUser['employee_id'] ?? '';

        return view('production/index', [
            'title' => 'Command Center Produksi', 
            'workOrders' => $workOrders, 
            'spkManual' => $spkManual, 
            'spkPreorder' => $spkPreorder, 
            'spkPreorderGrouped' => $spkPreorderGrouped, 
            'boms' => $boms, 
            'workers' => $workers, 
            'logs' => $productionLogs,
            'pendingLogs' => $pendingLogs,
            'myLogs' => $myLogs,
            'userSpecialty' => $userSpecialty,
            'userEmpId' => $userEmpId,
            'userRole' => session()->get('role')
        ]);
    }

    public function delete_log(string $id)
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/portal');

        $log = $this->db->table('production_logs')->where('id', $id)->get()->getRowArray();
        
        if (!$log) return redirect()->back()->with('error', 'Data setoran tidak ditemukan.');

        // PROTEKSI AKUNTANSI: Jika sudah disetujui, dilarang dihapus karena stok sudah terpotong otomatis
        if ($log['status'] === 'Approved') {
            return redirect()->back()->with('error', 'DITOLAK: Setoran sudah diverifikasi (Approved) dan memotong stok bahan baku. Hubungi Admin jika terjadi kesalahan.');
        }

        // PROTEKSI AKSES: Hanya pemilik data (atau Admin) yang boleh menghapus
        if (session()->get('role') !== 'admin' && $log['employee_id'] !== session()->get('employee_id')) {
            return redirect()->back()->with('error', 'DITOLAK: Anda tidak memiliki akses untuk menghapus data pekerja lain.');
        }

        $this->db->table('production_logs')->where('id', $id)->delete();
        return redirect()->back()->with('success', 'Riwayat setoran berhasil dibatalkan dan dihapus.');
    }

    public function get_operations($spkId = null)
    {
        if (!$spkId) return $this->response->setJSON(['status' => 'error', 'message' => 'ID SPK kosong.']);
        $spk = $this->db->table('work_orders')->where('id', $spkId)->get()->getRowArray();
        if(!$spk) return $this->response->setJSON(['status' => 'error', 'message' => 'SPK tidak ditemukan.']);

        $operations = $this->db->table('bom_operations')->where('bom_id', $spk['bom_id'])->orderBy('step_order', 'ASC')->get()->getResultArray();
        if (empty($operations)) return $this->response->setJSON(['status' => 'error', 'message' => '❌ SPK LAMA: Resep ini tidak punya tahapan kerja. Terbitkan SPK Baru dengan Resep yang sudah diupdate!']);

        $logs = $this->db->table('production_logs')
            ->select('operation_name, SUM(qty_produced) as total_done')
            ->where('spk_number', $spk['spk_number'])
            ->whereIn('status', ['Approved', 'Pending'])
            ->groupBy('operation_name')->get()->getResultArray();
            
        $doneMap = []; foreach($logs as $l) $doneMap[$l['operation_name']] = (int)$l['total_done'];

        $plannedQty = (int)$spk['planned_qty'];
        foreach ($operations as &$op) { $op['qty_done'] = $doneMap[$op['operation_name']] ?? 0; $op['qty_target'] = $plannedQty; }

        return $this->response->setJSON(['status' => 'success', 'data' => $operations]);
    }

    public function create_spk()
    {
        try {
            $dateStr = date('Ymd');
            $lastSpk = $this->db->table('work_orders')
                ->like('spk_number', "SPK-$dateStr", 'after')
                ->orderBy('id', 'DESC')
                ->get()
                ->getRowArray();
            
            if ($lastSpk) {
                $spkParts = explode('-', $lastSpk['spk_number']);
                $seq = intval(end($spkParts)) + 1;
            } else {
                $seq = 1;
            }
            
            $spkNumber = "SPK-" . $dateStr . "-" . str_pad($seq, 3, '0', STR_PAD_LEFT);

            $this->db->table('work_orders')->insert([
                'spk_number'  => $spkNumber, 
                'bom_id'      => $this->request->getPost('bom_id'),
                'planned_qty' => (int)$this->request->getPost('planned_qty'), 
                'status'      => 'IN_PROGRESS',
                'start_date'  => date('Y-m-d'), 
                'source'      => 'MANUAL'
            ]);

            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['status' => 'success', 'message' => "Surat Perintah Kerja $spkNumber berhasil diterbitkan!"]);
            }
            return redirect()->back()->with('success', "Surat Perintah Kerja $spkNumber berhasil diterbitkan!");
        } catch (\Exception $e) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['status' => 'error', 'message' => $e->getMessage()]);
            }
            return redirect()->back()->with('error', 'Gagal membuat SPK: ' . $e->getMessage());
        }
    }

    public function get_spk(string $id)
    {
        if (!$this->request->isAJAX()) return;
        $spk = $this->db->table('work_orders')->where('id', $id)->get()->getRowArray();
        if ($spk) return $this->response->setJSON(['status' => 'success', 'data' => $spk]);
        return $this->response->setJSON(['status' => 'error', 'message' => 'SPK tidak ditemukan.']);
    }

    public function update_spk()
    {
        try {
            $id = $this->request->getPost('spk_id');
            $bomId = $this->request->getPost('bom_id');
            $plannedQty = (int)$this->request->getPost('planned_qty');

            $spk = $this->db->table('work_orders')->where('id', $id)->get()->getRowArray();
            if (!$spk) throw new \Exception("SPK tidak ditemukan.");

            if ($plannedQty < $spk['completed_qty']) {
                throw new \Exception("Gagal: Target Qty ({$plannedQty}) lebih kecil dari barang yang sudah disetor pekerja ({$spk['completed_qty']}).");
            }

            $updateData = ['planned_qty' => $plannedQty];
            
            if ($spk['completed_qty'] == 0 && !empty($bomId)) {
                $updateData['bom_id'] = $bomId;
            }

            $newStatus = ($spk['completed_qty'] >= $plannedQty) ? 'COMPLETED' : 'IN_PROGRESS';
            $updateData['status'] = $newStatus;
            
            if ($newStatus === 'COMPLETED' && $spk['status'] !== 'COMPLETED') {
                $updateData['completed_date'] = date('Y-m-d');
                $updateData['completed_at'] = date('Y-m-d H:i:s');
            }

            $this->db->table('work_orders')->where('id', $id)->update($updateData);

            return redirect()->back()->with('success', "SPK {$spk['spk_number']} tersinkronisasi!");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function delete_spk(string $id)
    {
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'admin') return redirect()->to('/portal');
        
        $spk = $this->db->table('work_orders')->where('id', $id)->get()->getRowArray();
        if (!$spk) return redirect()->back()->with('error', 'SPK tidak ditemukan.');

        $logsCount = $this->db->table('production_logs')->where('spk_number', $spk['spk_number'])->countAllResults();
        
        if ($logsCount > 0) {
            return redirect()->back()->with('error', "DITOLAK (Proteksi Data): SPK {$spk['spk_number']} memiliki riwayat setoran pekerja ($logsCount log). SPK tidak dapat dihapus!");
        }

        $this->db->table('work_orders')->where('id', $id)->delete();
        return redirect()->back()->with('success', "SPK {$spk['spk_number']} berhasil dibatalkan dan dihapus.");
    }

    public function bom_builder()
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/portal');

        $finishedGoods = $this->db->table('warehouse_inventory')
            ->like('sku', 'PRD-', 'after')
            ->orderBy('item_type', 'ASC')
            ->orderBy('item_name', 'ASC')
            ->get()->getResultArray();

        $rawMaterials  = $this->db->table('raw_materials')
            ->orderBy('material_name', 'ASC')
            ->get()->getResultArray();

        $uomMasters = $this->db->table('uom_master')
            ->orderBy('uom_name', 'ASC')
            ->get()->getResultArray();

        $existingBoms = $this->db->table('bom_headers')
            ->select('bom_headers.*, warehouse_inventory.item_name, warehouse_inventory.item_type')
            ->join('warehouse_inventory', 'warehouse_inventory.sku = bom_headers.fg_sku', 'left')
            ->orderBy('warehouse_inventory.item_type', 'ASC')
            ->orderBy('bom_headers.id', 'DESC')
            ->get()->getResultArray();

        return view('production/bom_builder', [
            'title' => 'Master Data BoM & Routing',
            'finishedGoods' => $finishedGoods,
            'rawMaterials' => $rawMaterials,
            'uomMasters' => $uomMasters,
            'existingBoms' => $existingBoms
        ]);
    }

    public function store_bom()
    {
        try {
            $fgSku = $this->request->getPost('fg_sku');
            $recipeName = $this->request->getPost('recipe_name');
            $sectionsJson = $this->request->getPost('sections_json');

            if (empty($fgSku) || empty($recipeName)) throw new \Exception("Produk Target dan Nama Resep wajib diisi.");

            $checkExisting = $this->db->table('bom_headers')->where('fg_sku', $fgSku)->get()->getRowArray();
            if ($checkExisting) throw new \Exception("Resep untuk SKU {$fgSku} sudah ada! Silakan gunakan tombol Edit pada tabel Daftar Resep.");

            $this->db->transStart();

            $this->db->table('bom_headers')->insert(['fg_sku' => $fgSku, 'recipe_name' => $recipeName]);
            $bomId = $this->db->insertID();

            $globalStepOrder = 1;
            $allOperations = [];

            if (!empty($sectionsJson)) {
                $sections = json_decode($sectionsJson, true);
                if (!is_array($sections)) throw new \Exception("Format sections_json tidak valid.");

                foreach ($sections as $sec) {
                    $sectionName = !empty($sec['section_name']) ? strtoupper(trim($sec['section_name'])) : 'BAGIAN UTAMA';
                    $sectionCode = !empty($sec['section_code']) ? strtoupper(trim($sec['section_code'])) : null;

                    if (!empty($sec['materials']) && is_array($sec['materials'])) {
                        foreach ($sec['materials'] as $mat) {
                            $rmSku = $mat['sku'] ?? $mat['rm_sku'] ?? '';
                            if (empty($rmSku)) continue;

                            $sizePerItem = (float)($mat['size'] ?? $mat['size_per_item'] ?? 1);
                            $sizeUom     = strtoupper(trim($mat['size_uom'] ?? 'PCS'));
                            $qtyPerItem  = (float)($mat['qty'] ?? $mat['qty_per_item'] ?? 1);
                            $qtyUom      = strtoupper(trim($mat['qty_uom'] ?? 'PCS'));
                            $qtyRequired = (float)($mat['total'] ?? $mat['qty_required'] ?? 0);
                            $unit        = strtoupper(trim($mat['total_uom'] ?? $mat['unit'] ?? (($sizePerItem != 1) ? $sizeUom : $qtyUom)));

                            if ($qtyRequired <= 0) continue;

                            $this->db->table('bom_items')->insert([
                                'bom_id'        => $bomId,
                                'section_name'  => $sectionName,
                                'section_code'  => $sectionCode,
                                'rm_sku'        => $rmSku,
                                'size_per_item' => $sizePerItem,
                                'size_uom'      => $sizeUom,
                                'qty_per_item'  => $qtyPerItem,
                                'qty_uom'       => $qtyUom,
                                'qty_required'  => $qtyRequired,
                                'unit'          => $unit
                            ]);
                        }
                    }

                    if (!empty($sec['overheads']) && is_array($sec['overheads'])) {
                        foreach ($sec['overheads'] as $oh) {
                            $rmSku = $oh['sku'] ?? $oh['rm_sku'] ?? '';
                            if (empty($rmSku)) continue;
                            $ohQty = (float)($oh['total'] ?? $oh['qty'] ?? $oh['qty_required'] ?? 0);
                            if ($ohQty <= 0) continue;

                            $this->db->table('bom_items')->insert([
                                'bom_id'        => $bomId,
                                'section_name'  => $sectionName,
                                'section_code'  => $sectionCode,
                                'rm_sku'        => $rmSku,
                                'size_per_item' => 1,
                                'size_uom'      => 'PCS',
                                'qty_per_item'  => $ohQty,
                                'qty_uom'       => 'PCS',
                                'qty_required'  => $ohQty,
                                'unit'          => 'PCS'
                            ]);
                        }
                    }

                    if (!empty($sec['operations']) && is_array($sec['operations'])) {
                        foreach ($sec['operations'] as $op) {
                            $allOperations[] = ['section_name' => $sectionName, 'section_code' => $sectionCode, 'op_data' => $op];
                        }
                    }
                }

                $totalOps = count($allOperations);

                foreach ($allOperations as $idx => $opMap) {
                    $op = $opMap['op_data'];
                    $opName = trim($op['name'] ?? $op['operation_name'] ?? '');
                    if ($opName === '') continue;

                    $workerType = $op['worker_type'] ?? 'Borongan';
                    $wage = ($workerType === 'Tetap') ? 0 : (float)str_replace(['Rp', '.', ' '], '', $op['wage'] ?? $op['wage_per_piece'] ?? '0');
                    $isFinal = ($idx === ($totalOps - 1)) ? 1 : 0;
                    $opNameFormatted = (count($sections) > 1) ? "[{$opMap['section_name']}] {$opName}" : $opName;

                    $this->db->table('bom_operations')->insert([
                        'bom_id'             => $bomId,
                        'step_order'         => $globalStepOrder++,
                        'section_code'       => $opMap['section_code'],
                        'operation_name'     => strtoupper($opNameFormatted),
                        'worker_type'        => $workerType,
                        'specialty_required' => $op['specialty'] ?? null, 
                        'wage_per_piece'     => $wage,
                        'is_final_step'      => $isFinal
                    ]);
                }
            }

            $this->db->transComplete();
            if ($this->db->transStatus() === false) throw new \Exception("Database gagal memproses formula resep.");

            return redirect()->to('/production/bom_builder')->with('success', 'Formulasi Resep (BoM) berhasil diciptakan!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function update_bom(string $id)
    {
        try {
            $fgSku = $this->request->getPost('fg_sku');
            $recipeName = $this->request->getPost('recipe_name');
            $sectionsJson = $this->request->getPost('sections_json');

            if (empty($fgSku) || empty($recipeName)) throw new \Exception("Produk Target dan Nama Resep wajib diisi.");

            $existingBom = $this->db->table('bom_headers')->where('id', $id)->get()->getRowArray();
            if (!$existingBom) throw new \Exception("Data BOM tidak ditemukan.");

            $duplicate = $this->db->table('bom_headers')->where('fg_sku', $fgSku)->where('id !=', $id)->get()->getRowArray();
            if ($duplicate) throw new \Exception("SKU {$fgSku} sudah digunakan oleh resep lain.");

            $this->db->transStart();

            $this->db->table('bom_headers')->where('id', $id)->update(['fg_sku' => $fgSku, 'recipe_name' => $recipeName]);
            $this->db->table('bom_items')->where('bom_id', $id)->delete();
            $this->db->table('bom_operations')->where('bom_id', $id)->delete();

            $sections = json_decode($sectionsJson, true);
            if (!is_array($sections)) throw new \Exception("Format sections_json tidak valid.");

            $globalStep = 1;
            $allOperations = [];

            foreach ($sections as $sec) {
                $secName = !empty($sec['section_name']) ? strtoupper(trim($sec['section_name'])) : 'BAGIAN UTAMA';
                $sectionCode = !empty($sec['section_code']) ? strtoupper(trim($sec['section_code'])) : null;

                if (!empty($sec['materials']) && is_array($sec['materials'])) {
                    foreach ($sec['materials'] as $m) {
                        $rmSku = $m['sku'] ?? $m['rm_sku'] ?? '';
                        if (empty($rmSku)) continue;

                        $sizePerItem = (float)($m['size'] ?? $m['size_per_item'] ?? 1);
                        $sizeUom     = strtoupper(trim($m['size_uom'] ?? 'PCS'));
                        $qtyPerItem  = (float)($m['qty'] ?? $m['qty_per_item'] ?? 1);
                        $qtyUom      = strtoupper(trim($m['qty_uom'] ?? 'PCS'));
                        $qtyRequired = (float)($m['total'] ?? $m['qty_required'] ?? 0);
                        $unit        = strtoupper(trim($m['total_uom'] ?? $m['unit'] ?? (($sizePerItem != 1) ? $sizeUom : $qtyUom)));

                        if ($qtyRequired <= 0) continue;

                        $this->db->table('bom_items')->insert([
                            'bom_id'        => $id,
                            'section_name'  => $secName,
                            'section_code'  => $sectionCode,
                            'rm_sku'        => $rmSku,
                            'size_per_item' => $sizePerItem,
                            'size_uom'      => $sizeUom,
                            'qty_per_item'  => $qtyPerItem,
                            'qty_uom'       => $qtyUom,
                            'qty_required'  => $qtyRequired,
                            'unit'          => $unit
                        ]);
                    }
                }

                if (!empty($sec['overheads']) && is_array($sec['overheads'])) {
                    foreach ($sec['overheads'] as $oh) {
                        $rmSku = $oh['sku'] ?? $oh['rm_sku'] ?? '';
                        if (empty($rmSku)) continue;
                        $ohQty = (float)($oh['total'] ?? $oh['qty'] ?? $oh['qty_required'] ?? 0);
                        if ($ohQty <= 0) continue;

                        $this->db->table('bom_items')->insert([
                            'bom_id'        => $id,
                            'section_name'  => $secName,
                            'section_code'  => $sectionCode,
                            'rm_sku'        => $rmSku,
                            'size_per_item' => 1,
                            'size_uom'      => 'PCS',
                            'qty_per_item'  => $ohQty,
                            'qty_uom'       => 'PCS',
                            'qty_required'  => $ohQty,
                            'unit'          => 'PCS'
                        ]);
                    }
                }

                if (!empty($sec['operations']) && is_array($sec['operations'])) {
                    foreach ($sec['operations'] as $op) {
                        $allOperations[] = ['section_name' => $secName, 'section_code' => $sectionCode, 'op_data' => $op];
                    }
                }
            }

            $totalOps = count($allOperations);

            foreach ($allOperations as $idx => $opMap) {
                $op = $opMap['op_data'];
                $opName = trim($op['name'] ?? $op['operation_name'] ?? '');
                if ($opName === '') continue;

                $workerType = $op['worker_type'] ?? 'Borongan';
                $wage = ($workerType === 'Tetap') ? 0 : (float)str_replace(['Rp', '.', ' '], '', $op['wage'] ?? $op['wage_per_piece'] ?? '0');
                $isFinal = ($idx === ($totalOps - 1)) ? 1 : 0;
                $opNameFormatted = (count($sections) > 1) ? "[{$opMap['section_name']}] {$opName}" : $opName;

                $this->db->table('bom_operations')->insert([
                    'bom_id'             => $id,
                    'step_order'         => $globalStep++,
                    'section_code'       => $opMap['section_code'],
                    'operation_name'     => strtoupper($opNameFormatted),
                    'worker_type'        => $workerType,
                    'specialty_required' => $op['specialty'] ?? null, 
                    'wage_per_piece'     => $wage,
                    'is_final_step'      => $isFinal
                ]);
            }

            $this->db->transComplete();
            if ($this->db->transStatus() === false) throw new \Exception("Gagal memperbarui resep.");

            return redirect()->to('/production/bom_builder')->with('success', 'Resep diperbarui!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function duplicate_bom(string $id)
    {
        if (session()->get('role') !== 'admin') return redirect()->to('/portal');

        try {
            $this->db->transStart();

            $originalBom = $this->db->table('bom_headers')->where('id', $id)->get()->getRowArray();
            if (!$originalBom) throw new \Exception("Resep sumber tidak ditemukan.");

            $newFgSku = $originalBom['fg_sku'] . '-COPY-' . rand(10, 99);
            $newRecipeName = $originalBom['recipe_name'] . ' (SALINAN)';

            $this->db->table('bom_headers')->insert([
                'fg_sku'      => $newFgSku,
                'recipe_name' => $newRecipeName,
                'version_no'  => $originalBom['version_no'],
                'notes'       => 'Hasil duplikasi dari resep: ' . $originalBom['recipe_name'],
                'is_active'   => $originalBom['is_active']
            ]);
            $newBomId = $this->db->insertID();

            $originalItems = $this->db->table('bom_items')->where('bom_id', $id)->get()->getResultArray();
            foreach ($originalItems as $item) {
                $this->db->table('bom_items')->insert([
                    'bom_id'        => $newBomId,
                    'section_name'  => $item['section_name'],
                    'section_code'  => $item['section_code'],
                    'rm_sku'        => $item['rm_sku'],
                    'size_per_item' => $item['size_per_item'],
                    'size_uom'      => $item['size_uom'],
                    'qty_per_item'  => $item['qty_per_item'],
                    'qty_uom'       => $item['qty_uom'],
                    'qty_required'  => $item['qty_required'],
                    'unit'          => $item['unit']
                ]);
            }

            $originalOps = $this->db->table('bom_operations')->where('bom_id', $id)->get()->getResultArray();
            foreach ($originalOps as $op) {
                $this->db->table('bom_operations')->insert([
                    'bom_id'             => $newBomId,
                    'step_order'         => $op['step_order'],
                    'section_code'       => $op['section_code'],
                    'operation_name'     => $op['operation_name'],
                    'worker_type'        => $op['worker_type'],
                    'specialty_required' => $op['specialty_required'],
                    'wage_per_piece'     => $op['wage_per_piece'],
                    'is_final_step'      => $op['is_final_step']
                ]);
            }

            $this->db->transComplete();
            if ($this->db->transStatus() === false) throw new \Exception("Sistem gagal menduplikasi resep.");

            return redirect()->back()->with('success', "Berhasil menduplikasi resep! Silakan edit data salinan untuk menyesuaikan SKU Produk.");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function get_bom(string $id)
    {
        if ($this->request->isAJAX()) {
            try {
                $header = $this->db->table('bom_headers')->where('id', $id)->get()->getRowArray();
                if (!$header) return $this->response->setJSON(['status' => 'error', 'message' => 'Data Resep tidak ditemukan.']);

                $itemsRaw = $this->db->table('bom_items')
                    ->select('bom_items.*, raw_materials.material_category')
                    ->join('raw_materials', 'raw_materials.sku_material = bom_items.rm_sku', 'left')
                    ->where('bom_id', $id)->get()->getResultArray();

                $opsRaw = $this->db->table('bom_operations')->where('bom_id', $id)->orderBy('step_order', 'ASC')->get()->getResultArray();

                $sectionsMap = [];

                foreach ($itemsRaw as $item) {
                    $secName = !empty($item['section_name']) ? strtoupper($item['section_name']) : 'BAGIAN UTAMA';
                    $secCode = !empty($item['section_code']) ? strtoupper($item['section_code']) : '';

                    if (!isset($sectionsMap[$secName])) {
                        $sectionsMap[$secName] = ['section_name' => $secName, 'section_code' => $secCode, 'materials' => [], 'overheads' => [], 'operations' => []];
                    }

                    $cat = strtolower($item['material_category'] ?? '');
                    $isOh = ($cat === 'consumable' || $cat === 'overhead');

                    if ($isOh) {
                        $sectionsMap[$secName]['overheads'][] = ['sku' => $item['rm_sku'], 'rm_sku' => $item['rm_sku'], 'total' => (float)$item['qty_required'], 'qty_required' => (float)$item['qty_required']];
                    } else {
                        $sectionsMap[$secName]['materials'][] = [
                            'sku' => $item['rm_sku'], 'rm_sku' => $item['rm_sku'], 'size' => (float)($item['size_per_item'] ?? 1),
                            'size_per_item' => (float)($item['size_per_item'] ?? 1), 'size_uom' => $item['size_uom'] ?? 'PCS',
                            'qty' => (float)($item['qty_per_item'] ?? 1), 'qty_per_item' => (float)($item['qty_per_item'] ?? 1),
                            'qty_uom' => $item['qty_uom'] ?? 'PCS', 'total' => (float)($item['qty_required'] ?? 0),
                            'qty_required' => (float)($item['qty_required'] ?? 0), 'total_uom' => $item['unit'] ?? 'PCS', 'unit' => $item['unit'] ?? 'PCS'
                        ];
                    }
                }

                foreach ($opsRaw as $op) {
                    $secName = 'BAGIAN UTAMA';
                    $secCode = !empty($op['section_code']) ? strtoupper($op['section_code']) : '';
                    $opName = $op['operation_name'] ?? '';

                    if (preg_match('/^\[(.*?)\]\s*(.*)$/', $opName, $matches)) {
                        $secName = strtoupper(trim($matches[1]));
                        $opName = trim($matches[2]);
                    }

                    if (!isset($sectionsMap[$secName])) {
                        $sectionsMap[$secName] = ['section_name' => $secName, 'section_code' => $secCode, 'materials' => [], 'overheads' => [], 'operations' => []];
                    }

                    if (empty($sectionsMap[$secName]['section_code']) && !empty($secCode)) {
                        $sectionsMap[$secName]['section_code'] = $secCode;
                    }

                    $sectionsMap[$secName]['operations'][] = [
                        'name' => $opName, 'operation_name' => $opName, 'worker_type' => $op['worker_type'] ?? 'Borongan',
                        'specialty_required' => $op['specialty_required'] ?? '', 'wage' => (float)($op['wage_per_piece'] ?? 0), 'wage_per_piece' => (float)($op['wage_per_piece'] ?? 0)
                    ];
                }

                return $this->response->setJSON(['status' => 'success', 'header' => $header, 'sections' => array_values($sectionsMap), 'items' => $itemsRaw, 'ops' => $opsRaw]);
            } catch (\Exception $e) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'System Error: ' . $e->getMessage()]);
            }
        }
    }

    public function delete_bom(string $id)
    {
        $this->db->table('bom_items')->where('bom_id', $id)->delete();
        $this->db->table('bom_operations')->where('bom_id', $id)->delete();
        $this->db->table('bom_headers')->where('id', $id)->delete();
        return redirect()->back()->with('success', 'Resep (BOM) berhasil dihapus permanen.');
    }

    public function add_production_log()
    {
        try {
            $this->db->transStart();

            $spkId        = (int)$this->request->getPost('spk_id');
            $employeeId   = $this->request->getPost('employee_id');
            if (empty($employeeId)) {
                 $employeeId = session()->get('employee_id');
            }
            $operationId  = (int)$this->request->getPost('operation_id');
            $qtyProduced  = (int)$this->request->getPost('qty_produced');

            $overheadHarian = (float) str_replace(',', '.', str_replace('.', '', $this->request->getPost('overhead_cost') ?? '0'));
            $customWage     = (float) str_replace(['Rp', '.', ' '], '', $this->request->getPost('custom_wage') ?? '0');

            $extraSkus = $this->request->getPost('extra_rm_sku') ?? [];
            $extraQtys = $this->request->getPost('extra_rm_qty') ?? [];

            if ($qtyProduced <= 0) throw new \Exception("Jumlah barang diselesaikan tidak valid.");

            $spk       = $this->db->table('work_orders')->where('id', $spkId)->get()->getRowArray();
            $bom       = $this->db->table('bom_headers')->where('id', $spk['bom_id'])->get()->getRowArray();
            $operation = $this->db->table('bom_operations')->where('id', $operationId)->get()->getRowArray();
            $employee  = $this->db->table('employees')->where('employee_id', $employeeId)->get()->getRowArray();
            
            if (!$spk || !$bom || !$operation || !$employee) throw new \Exception("Data Referensi tidak valid.");

            $logSelesai = $this->db->table('production_logs')
                ->where('spk_number', $spk['spk_number'])
                ->where('operation_name', $operation['operation_name'])
                ->whereIn('status', ['Approved', 'Pending'])
                ->selectSum('qty_produced')
                ->get()
                ->getRowArray();

            $alreadyDone = (int)($logSelesai['qty_produced'] ?? 0);
            $remaining   = ((int)$spk['planned_qty'] - $alreadyDone);

            if ($qtyProduced > $remaining) throw new \Exception("DITOLAK! Kuantitas setoran melebihi sisa target SPK. Sisa target: {$remaining}");

            $isTetapOp = (stripos($operation['worker_type'] ?? '', 'Tetap') !== false);
            $laborRate = $isTetapOp ? 0.00 : (($customWage > 0) ? $customWage : (float)$operation['wage_per_piece']);
            $totalWage = $qtyProduced * $laborRate;

            $extraMaterials = [];
            for ($i = 0; $i < count($extraSkus); $i++) {
                if (!empty($extraSkus[$i]) && (float)$extraQtys[$i] > 0) {
                    $extraMaterials[] = ['sku' => $extraSkus[$i], 'qty' => (float)$extraQtys[$i]];
                }
            }

            $logStatus = session()->get('role') === 'admin' ? 'Approved' : 'Pending';

            $this->db->table('production_logs')->insert([
                'sku'             => $bom['fg_sku'],
                'spk_number'      => $spk['spk_number'],
                'employee_id'     => $employeeId,
                'operation_name'  => $operation['operation_name'],
                'is_final_step'   => $operation['is_final_step'],
                'qty_produced'    => $qtyProduced,
                'wage_per_piece'  => $laborRate,
                'total_wage'      => $totalWage,
                'production_date' => date('Y-m-d H:i:s'),
                'status'          => $logStatus,
                'extra_materials' => json_encode($extraMaterials),
                'notes'           => $overheadHarian > 0 ? "Overhead: $overheadHarian" : null
            ]);
            $productionLogId = $this->db->insertID();

            if ($logStatus === 'Approved') {
                $this->process_log_approval($productionLogId, $overheadHarian);
            }

            $this->db->transComplete();
            if ($this->db->transStatus() === false) throw new \Exception("Gagal menyimpan setoran.");

            $msg = ($logStatus === 'Approved') 
                ? "Setoran berhasil dicatat dan disetujui otomatis."
                : "Setoran berhasil dikirim! Menunggu konfirmasi Mandor/Admin.";

            if ($this->request->isAJAX()) return $this->response->setJSON(['status' => 'success', 'message' => $msg]);
            return redirect()->back()->with('success', $msg);

        } catch (\Exception $e) {
            if ($this->db->transStatus()) $this->db->transRollback();
            if ($this->request->isAJAX()) return $this->response->setJSON(['status' => 'error', 'message' => $e->getMessage()]);
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function approve_log($id)
    {
        if (session()->get('role') !== 'admin') return redirect()->to('/portal');

        try {
            $this->db->transStart();

            $log = $this->db->table('production_logs')->where('id', $id)->get()->getRowArray();
            if (!$log || $log['status'] !== 'Pending') {
                throw new \Exception("Setoran tidak valid atau sudah diproses.");
            }

            $overheadHarian = 0;
            if (strpos($log['notes'] ?? '', 'Overhead:') !== false) {
                $overheadHarian = (float) trim(str_replace('Overhead:', '', $log['notes']));
            }

            $this->db->table('production_logs')->where('id', $id)->update([
                'status' => 'Approved',
                'approved_by' => session()->get('name'),
                'approved_at' => date('Y-m-d H:i:s')
            ]);

            $this->process_log_approval($id, $overheadHarian);

            $this->db->transComplete();
            if ($this->db->transStatus() === false) throw new \Exception("Gagal memproses persetujuan.");

            return redirect()->back()->with('success', 'Setoran berhasil dikonfirmasi! Stok bahan terpotong dan Jurnal terbentuk otomatis.');
        } catch (\Exception $e) {
            $this->db->transRollback();
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function reject_log($id)
    {
        if (session()->get('role') !== 'admin') return redirect()->to('/portal');

        $this->db->table('production_logs')->where('id', $id)->update([
            'status' => 'Rejected',
            'approved_by' => session()->get('name'),
            'approved_at' => date('Y-m-d H:i:s')
        ]);

        return redirect()->back()->with('success', 'Setoran telah ditolak dan tidak akan diproses.');
    }

    private function process_log_approval(int $logId, float $overheadHarian): void
    {
        $log = $this->db->table('production_logs')->where('id', $logId)->get()->getRowArray();
        $spk = $this->db->table('work_orders')->where('spk_number', $log['spk_number'])->get()->getRowArray();
        $bom = $this->db->table('bom_headers')->where('fg_sku', $log['sku'])->get()->getRowArray();

        $totalMaterialCost = 0;
        $invService = new InventoryService();

        // 1. Eksekusi Pemotongan Extra Material
        $extraMaterials = json_decode($log['extra_materials'] ?? '[]', true);
        if (is_array($extraMaterials) && !empty($extraMaterials)) {
            foreach ($extraMaterials as $ext) {
                $consumedExtra = $invService->consumeMaterialOrFG($ext['sku'], $ext['qty'], $spk['spk_number'], $spk['id'], 'Pemakaian Extra Material SPK');
                $totalMaterialCost += $consumedExtra['cost'];
            }
        }

        // 2. Eksekusi Pemotongan Bahan Baku BOM
        if ((int)$log['is_final_step'] === 1) {
            $bomItems = $this->db->table('bom_items')->where('bom_id', $bom['id'])->get()->getResultArray();
            foreach ($bomItems as $item) {
                $totalRmNeeded = (float)$item['qty_required'] * $log['qty_produced'];
                $consumed = $invService->consumeMaterialOrFG($item['rm_sku'], $totalRmNeeded, $spk['spk_number'], $spk['id'], 'Pemotongan BOM Otomatis SPK');
                $totalMaterialCost += $consumed['cost'];
            }
            
            // Kalkulasi HPP dan Input Barang Jadi FG
            $routingOps = $this->db->table('bom_operations')->where('bom_id', $bom['id'])->get()->getResultArray();
            $routingWagePerPcs = 0;
            foreach ($routingOps as $op) {
                if (strtolower($op['worker_type'] ?? 'borongan') === 'borongan') {
                    $routingWagePerPcs += (float)$op['wage_per_piece'];
                }
            }

            $unitMaterialCost = ($log['qty_produced'] > 0) ? ($totalMaterialCost / $log['qty_produced']) : 0;
            $unitOverheadCost = ($log['qty_produced'] > 0) ? ($overheadHarian / $log['qty_produced']) : 0;
            $unitHppBaru = $unitMaterialCost + $routingWagePerPcs + $unitOverheadCost;
            $totalFgValue = $log['qty_produced'] * $unitHppBaru;

            // Tambah Stok Jadi
            $invService->addFinishedGood($bom['fg_sku'], $log['qty_produced'], $totalFgValue, $spk['spk_number'], $spk['id'], 'Hasil Produksi SPK');

            // Sinkronisasi SPK Status
            $finalDone = $this->db->table('production_logs')
                ->where('spk_number', $spk['spk_number'])
                ->where('is_final_step', 1)
                ->where('status', 'Approved')
                ->selectSum('qty_produced')->get()->getRowArray();
                
            $completedQty = (int)($finalDone['qty_produced'] ?? 0);
            $statusSpk = ($completedQty >= (int)$spk['planned_qty']) ? 'COMPLETED' : 'IN_PROGRESS';

            $this->db->table('work_orders')->where('id', $spk['id'])->update([
                'completed_qty'        => $completedQty,
                'actual_material_cost' => ((float)($spk['actual_material_cost'] ?? 0)) + $totalMaterialCost,
                'actual_labor_cost'    => ((float)($spk['actual_labor_cost'] ?? 0)) + $log['total_wage'],
                'actual_total_cost'    => ((float)($spk['actual_total_cost'] ?? 0)) + $totalFgValue,
                'status'               => $statusSpk,
                'completed_at'         => ($statusSpk === 'COMPLETED') ? date('Y-m-d H:i:s') : null
            ]);
        } else {
            // Update Partial Cost (Labor only)
            $this->db->table('work_orders')->where('id', $spk['id'])->update([
                'actual_labor_cost'    => ((float)($spk['actual_labor_cost'] ?? 0)) + $log['total_wage'],
                'actual_material_cost' => ((float)($spk['actual_material_cost'] ?? 0)) + $totalMaterialCost,
            ]);
        }
    }

    public function check_last_wage()
    {
        if (!$this->request->isAJAX()) return;

        $employeeId = $this->request->getPost('employee_id');
        $operationId = $this->request->getPost('operation_id');
        $spkId = $this->request->getPost('spk_id');

        $op = $this->db->table('bom_operations')->where('id', $operationId)->get()->getRowArray();
        $spk = $this->db->table('work_orders')->where('id', $spkId)->get()->getRowArray();
        if (!$op || !$spk) return $this->response->setJSON(['status' => 'error']);
        
        $bom = $this->db->table('bom_headers')->where('id', $spk['bom_id'])->get()->getRowArray();

        $lastLog = $this->db->table('production_logs')
            ->where('employee_id', $employeeId)
            ->where('operation_name', $op['operation_name'])
            ->where('sku', $bom['fg_sku']) 
            ->orderBy('id', 'DESC')
            ->get()->getRowArray();

        if ($lastLog) {
            $lastWage = (float)$lastLog['wage_per_piece'];
            $baseWage = (float)$op['wage_per_piece'];
            
            if ($lastWage != $baseWage) {
                return $this->response->setJSON([
                    'status'      => 'found',
                    'custom_wage' => $lastWage,
                    'csrf_token'  => csrf_hash() 
                ]);
            }
        }

        return $this->response->setJSON([
            'status' => 'not_found',
            'csrf_token'  => csrf_hash()
        ]);
    }

    public function print_spk(string $id)
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/portal');
        $spk = $this->db->table('work_orders')->where('id', $id)->get()->getRowArray();
        $bom = $this->db->table('bom_headers')->where('id', $spk['bom_id'])->get()->getRowArray();
        $targetProduct = $this->db->table('warehouse_inventory')->where('sku', $bom['fg_sku'])->get()->getRowArray();
        $items = $this->db->table('bom_items')->where('bom_id', $bom['id'])->get()->getResultArray();
        
        foreach ($items as &$item) {
            $rm = $this->db->table('raw_materials')->where('sku_material', $item['rm_sku'])->get()->getRowArray();
            if ($rm) { $item['name'] = $rm['material_name']; $item['unit'] = $rm['unit']; } 
            else {
                $fg = $this->db->table('warehouse_inventory')->where('sku', $item['rm_sku'])->get()->getRowArray();
                $item['name'] = $fg['item_name'] ?? 'Unknown Item'; $item['unit'] = 'Pcs';
            }
            $item['total_needed'] = $item['qty_required'] * $spk['planned_qty'];
        }
        $operations = $this->db->table('bom_operations')->where('bom_id', $bom['id'])->orderBy('step_order', 'ASC')->get()->getResultArray();
        $company = $this->db->tableExists('company_settings') ? $this->db->table('company_settings')->get()->getRowArray() : [];
        return view('production/print_spk', ['title' => 'Cetak SPK', 'spk' => $spk, 'bom' => $bom, 'targetProduct' => $targetProduct, 'items' => $items, 'operations' => $operations, 'company' => $company]);
    }
    
    public function print_spk_batch(string $soId)
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/portal');
        
        $so = $this->db->table('b2b_sales_orders')
            ->select('b2b_sales_orders.*, b2b_customers.company_name')
            ->join('b2b_customers', 'b2b_customers.id = b2b_sales_orders.customer_id', 'left')
            ->where('b2b_sales_orders.id', $soId)->get()->getRowArray();
            
        $workOrders = $this->db->table('work_orders')->where('so_id', $soId)->get()->getResultArray();
        
        $targetProducts = [];
        $aggregatedMaterials = [];
        
        foreach($workOrders as $spk) {
            $plannedQty = (int)$spk['planned_qty'];
            $bom = $this->db->table('bom_headers')->where('id', $spk['bom_id'])->get()->getRowArray();
            $fg = $this->db->table('warehouse_inventory')->where('sku', $bom['fg_sku'])->get()->getRowArray();
            
            $targetProducts[] = [
                'sku'        => $fg['sku'] ?? $bom['fg_sku'],
                'name'       => $fg['item_name'] ?? 'Unknown',
                'qty'        => $plannedQty,
                'spk_number' => $spk['spk_number']
            ];
            
            $items = $this->db->table('bom_items')->where('bom_id', $bom['id'])->get()->getResultArray();
            foreach ($items as $item) {
                $rmSku      = $item['rm_sku'];
                $size       = (float)($item['size_per_item'] ?? 1);
                $sizeUom    = strtoupper($item['size_uom'] ?? 'PCS');
                $qtyPerItem = (float)($item['qty_per_item'] ?? 1);
                $qtyUom     = strtoupper($item['qty_uom'] ?? 'PCS');
                $unitAkhir  = strtoupper($item['unit'] ?? 'PCS');
                
                $groupKey = $rmSku . '_' . $size . '_' . $sizeUom;
                
                if (!isset($aggregatedMaterials[$groupKey])) {
                    $rm = $this->db->table('raw_materials')->where('sku_material', $rmSku)->get()->getRowArray();
                    if (!$rm) {
                        $rmFg = $this->db->table('warehouse_inventory')->where('sku', $rmSku)->get()->getRowArray();
                        $name = $rmFg['item_name'] ?? 'Unknown';
                    } else {
                        $name = $rm['material_name'];
                    }
                    
                    $aggregatedMaterials[$groupKey] = [
                        'sku'                    => $rmSku,
                        'name'                   => $name,
                        'size_per_item'          => $size,
                        'size_uom'               => $sizeUom,
                        'qty_uom'                => $qtyUom,
                        'unit_akhir'             => $unitAkhir,
                        'total_pcs'              => 0,
                        'total_kebutuhan_gudang' => 0
                    ];
                }
                
                $aggregatedMaterials[$groupKey]['total_pcs'] += ($qtyPerItem * $plannedQty);
                $aggregatedMaterials[$groupKey]['total_kebutuhan_gudang'] += ((float)$item['qty_required'] * $plannedQty);
            }
        }
        
        usort($aggregatedMaterials, function($a, $b) {
            if ($a['sku'] == $b['sku']) return $b['size_per_item'] <=> $a['size_per_item'];
            return strcmp($a['sku'], $b['sku']);
        });

        $company = $this->db->tableExists('company_settings') ? $this->db->table('company_settings')->get()->getRowArray() : [];
        
        return view('production/print_spk_batch', [
            'title'          => 'Rekap Total Kebutuhan Order', 
            'so'             => $so,
            'targetProducts' => $targetProducts,
            'materials'      => $aggregatedMaterials,
            'company'        => $company
        ]);
    }

    public function print_rekap_produksi(string $soId)
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/portal');
        $so = $this->db->table('b2b_sales_orders')->select('b2b_sales_orders.*, b2b_customers.company_name')->join('b2b_customers', 'b2b_customers.id = b2b_sales_orders.customer_id', 'left')->where('b2b_sales_orders.id', $soId)->get()->getRowArray();
        $workOrders = $this->db->table('work_orders')->select('work_orders.*, bom_headers.recipe_name, bom_headers.fg_sku, warehouse_inventory.item_name')->join('bom_headers', 'bom_headers.id = work_orders.bom_id')->join('warehouse_inventory', 'warehouse_inventory.sku = bom_headers.fg_sku', 'left')->where('work_orders.so_id', $soId)->get()->getResultArray();
        $company = $this->db->tableExists('company_settings') ? $this->db->table('company_settings')->get()->getRowArray() : [];
        return view('production/print_rekap_produksi', ['title' => 'Rekap Target Produksi', 'so' => $so, 'workOrders' => $workOrders, 'company' => $company]);
    }
    
    public function print_form_setoran()
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/portal');
        $company = $this->db->tableExists('company_settings') ? $this->db->table('company_settings')->get()->getRowArray() : [];
        return view('production/print_form_setoran', ['title' => 'Form Setoran Harian', 'company' => $company]);
    }
    
    public function print_bom(string $id)
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/portal');
        $bom = $this->db->table('bom_headers')->select('bom_headers.*, warehouse_inventory.item_name')->join('warehouse_inventory', 'warehouse_inventory.sku = bom_headers.fg_sku', 'left')->where('bom_headers.id', $id)->get()->getRowArray();
        $items = $this->db->table('bom_items')->select('bom_items.*, raw_materials.material_name, raw_materials.unit as rm_unit, warehouse_inventory.item_name as fg_name')->join('raw_materials', 'raw_materials.sku_material = bom_items.rm_sku', 'left')->join('warehouse_inventory', 'warehouse_inventory.sku = bom_items.rm_sku', 'left')->where('bom_id', $id)->get()->getResultArray();
        $operations = $this->db->table('bom_operations')->where('bom_id', $id)->orderBy('step_order', 'ASC')->get()->getResultArray();
        $company = $this->db->tableExists('company_settings') ? $this->db->table('company_settings')->get()->getRowArray() : [];
        return view('production/print_bom', ['title' => 'Cetak Resep', 'bom' => $bom, 'items' => $items, 'operations' => $operations, 'company' => $company]);
    }

    public function print_bom_batch()
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/portal');
        $boms = $this->db->table('bom_headers')->select('bom_headers.*, warehouse_inventory.item_name, warehouse_inventory.item_type')->join('warehouse_inventory', 'warehouse_inventory.sku = bom_headers.fg_sku', 'left')->orderBy('warehouse_inventory.item_type', 'ASC')->orderBy('bom_headers.id', 'ASC')->get()->getResultArray();
        $bomDataBatch = [];
        foreach ($boms as $bom) {
            $items = $this->db->table('bom_items')->select('bom_items.*, raw_materials.material_name, raw_materials.unit as rm_unit, warehouse_inventory.item_name as fg_name')->join('raw_materials', 'raw_materials.sku_material = bom_items.rm_sku', 'left')->join('warehouse_inventory', 'warehouse_inventory.sku = bom_items.rm_sku', 'left')->where('bom_id', $bom['id'])->get()->getResultArray();
            $operations = $this->db->table('bom_operations')->where('bom_id', $bom['id'])->orderBy('step_order', 'ASC')->get()->getResultArray();
            $bomDataBatch[] = ['bom' => $bom, 'items' => $items, 'operations' => $operations];
        }
        $company = $this->db->tableExists('company_settings') ? $this->db->table('company_settings')->get()->getRowArray() : [];
        return view('production/print_bom_batch', ['title' => 'Cetak Massal Resep', 'bomDataBatch' => $bomDataBatch, 'company' => $company]);
    }

    public function print_blank_bom()
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/portal');
        $company = $this->db->tableExists('company_settings') ? $this->db->table('company_settings')->get()->getRowArray() : [];
        return view('production/print_blank_bom', ['title' => 'Formulir Resep Universal (Blank)', 'company' => $company]);
    }
    
    public function print_blank_bom_batch()
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/portal');
        $products = $this->db->query("SELECT sku, item_name, item_type FROM warehouse_inventory WHERE sku LIKE 'PRD-%' ORDER BY item_type ASC, item_name ASC")->getResultArray();
        $company = $this->db->tableExists('company_settings') ? $this->db->table('company_settings')->get()->getRowArray() : [];
        return view('production/print_blank_bom_batch', ['title' => 'Formulir Resep (Blank)', 'products' => $products, 'company' => $company]);
    }

    public function sync_old_po()
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/portal');

        $this->db->transStart();

        $this->db->query("
            DELETE FROM work_orders 
            WHERE source = 'PREORDER'
              AND status != 'COMPLETED'
              AND spk_number NOT IN (
                  SELECT DISTINCT spk_number 
                  FROM production_logs 
                  WHERE spk_number IS NOT NULL AND spk_number != ''
              )
        ");

        $productsWithoutBom = $this->db->query("
            SELECT w.sku, w.item_name, w.item_type 
            FROM warehouse_inventory w
            LEFT JOIN bom_headers b ON w.sku = b.fg_sku
            WHERE w.sku LIKE 'PRD-%' 
              AND b.id IS NULL
        ")->getResultArray();

        foreach ($productsWithoutBom as $prd) {
            $kategori = !empty($prd['item_type']) ? $prd['item_type'] : 'Produk Umum';

            $this->db->table('bom_headers')->insert([
                'fg_sku'      => $prd['sku'],
                'recipe_name' => "Resep $kategori: " . $prd['item_name']
            ]);

            $newBomId = $this->db->insertID();

            $this->db->table('bom_operations')->insert([
                'bom_id'         => $newBomId,
                'step_order'     => 1,
                'section_code'   => 'FINAL',
                'operation_name' => 'PERAKITAN & QC AKHIR',
                'worker_type'    => 'Tetap',
                'wage_per_piece' => 0,
                'is_final_step'  => 1
            ]);
        }

        $pendingItems = $this->db->query("
            SELECT 
                so.id AS so_id,
                so.so_number,
                so.order_date,
                so.customer_id,
                c.company_name,
                soi.fg_sku,
                COALESCE(soi.additional_note, '') AS additional_note,
                SUM(soi.qty) AS total_qty,
                SUM(soi.shipped_qty) AS total_shipped,
                SUM(soi.qty - soi.shipped_qty) AS pending_qty
            FROM b2b_sales_order_items soi
            JOIN b2b_sales_orders so ON so.id = soi.so_id
            JOIN b2b_customers c ON c.id = so.customer_id
            WHERE so.shipping_status = 'PRE-ORDER'
              AND (soi.qty - soi.shipped_qty) > 0
            GROUP BY 
                so.id,
                so.so_number,
                so.order_date,
                so.customer_id,
                c.company_name,
                soi.fg_sku,
                soi.additional_note
            HAVING pending_qty > 0
            ORDER BY so.order_date ASC, so.so_number ASC, soi.fg_sku ASC
        ")->getResultArray();

        $spkCreated = 0;
        $spkUpdated = 0;
        $dateStr = date('Ymd');

        $lastSpk = $this->db->table('work_orders')
            ->like('spk_number', "SPK-$dateStr", 'after')
            ->orderBy('id', 'DESC')
            ->get()
            ->getRowArray();

        if ($lastSpk) {
            $spkParts = explode('-', $lastSpk['spk_number']);
            $seq = intval(end($spkParts));
        } else {
            $seq = 0;
        }

        foreach ($pendingItems as $item) {
            $bom = $this->db->table('bom_headers')
                ->where('fg_sku', $item['fg_sku'])
                ->orderBy('id', 'DESC')
                ->get()
                ->getRowArray();

            if (!$bom) {
                continue;
            }

            $existingSpk = $this->db->table('work_orders')
                ->where('source', 'PREORDER')
                ->where('so_id', $item['so_id'])
                ->where('bom_id', $bom['id'])
                ->where('production_notes', $item['additional_note'])
                ->where('status !=', 'COMPLETED')
                ->get()
                ->getRowArray();

            if ($existingSpk) {
                $newPlannedQty = (int)$item['pending_qty'];
                $currentCompleted = (int)($existingSpk['completed_qty'] ?? 0);

                if ($newPlannedQty < $currentCompleted) {
                    $newPlannedQty = $currentCompleted;
                }

                if ((int)$existingSpk['planned_qty'] !== $newPlannedQty) {
                    $newStatus = ($currentCompleted >= $newPlannedQty) ? 'COMPLETED' : 'IN_PROGRESS';

                    $this->db->table('work_orders')
                        ->where('id', $existingSpk['id'])
                        ->update([
                            'planned_qty' => $newPlannedQty,
                            'status'      => $newStatus,
                            'end_date'    => ($newStatus === 'COMPLETED') ? date('Y-m-d') : null
                        ]);

                    $spkUpdated++;
                }
            } else {
                $seq++;
                $spkNumber = "SPK-" . $dateStr . "-" . str_pad($seq, 3, '0', STR_PAD_LEFT);

                $this->db->table('work_orders')->insert([
                    'so_id'            => $item['so_id'],
                    'spk_number'       => $spkNumber,
                    'bom_id'           => $bom['id'],
                    'planned_qty'      => (int)$item['pending_qty'],
                    'completed_qty'    => 0,
                    'production_notes' => $item['additional_note'],
                    'status'           => 'IN_PROGRESS',
                    'start_date'       => date('Y-m-d'),
                    'source'           => 'PREORDER'
                ]);

                $spkCreated++;
            }
        }

        $activePreorderSpks = $this->db->table('work_orders')
            ->where('source', 'PREORDER')
            ->where('status !=', 'COMPLETED')
            ->get()
            ->getResultArray();

        foreach ($activePreorderSpks as $spk) {
            $bom = $this->db->table('bom_headers')
                ->where('id', $spk['bom_id'])
                ->get()
                ->getRowArray();

            if (!$bom) continue;

            $cekMasihAda = $this->db->query("
                SELECT 
                    SUM(soi.qty - soi.shipped_qty) AS pending_qty
                FROM b2b_sales_order_items soi
                JOIN b2b_sales_orders so ON so.id = soi.so_id
                WHERE so.id = ?
                  AND so.shipping_status = 'PRE-ORDER'
                  AND soi.fg_sku = ?
                  AND COALESCE(soi.additional_note, '') = ?
                  AND (soi.qty - soi.shipped_qty) > 0
            ", [
                $spk['so_id'],
                $bom['fg_sku'],
                $spk['production_notes'] ?? ''
            ])->getRowArray();

            $pendingQty = (int)($cekMasihAda['pending_qty'] ?? 0);

            $hasLog = $this->db->table('production_logs')
                ->where('spk_number', $spk['spk_number'])
                ->countAllResults();

            if ($pendingQty <= 0 && $hasLog == 0) {
                $this->db->table('work_orders')->where('id', $spk['id'])->delete();
            }
        }

        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            return redirect()->back()->with('error', 'Sinkronisasi gagal diproses.');
        }

        return redirect()->to('/production')->with(
            'success',
            "Sinkronisasi selesai! SPK baru: {$spkCreated}, SPK diperbarui: {$spkUpdated}."
        );
    }
    
   public function confirm_logs()
    {
        if (session()->get('role') !== 'admin') return redirect()->to('/portal');

        $pendingLogs = $this->db->table('production_logs')
            ->select('production_logs.*, employees.name as employee_name, warehouse_inventory.item_name')
            ->join('employees', 'employees.employee_id = production_logs.employee_id')
            ->join('warehouse_inventory', 'warehouse_inventory.sku = production_logs.sku', 'left')
            ->where('production_logs.status', 'Pending')
            ->orderBy('production_logs.id', 'ASC')
            ->get()->getResultArray();

        return view('production/confirm_logs', [
            'title' => 'Konfirmasi Setoran Pekerja',
            'pendingLogs' => $pendingLogs
        ]);
    }

    public function mass_copy_bom()
    {
        if (session()->get('role') !== 'admin') return redirect()->to('/portal');

        try {
            $sourceBomId = $this->request->getPost('source_bom_id');
            $targetBomIds = $this->request->getPost('target_bom_ids'); 
            $copyItems = $this->request->getPost('copy_items');
            $copyOps = $this->request->getPost('copy_ops'); 

            if (empty($sourceBomId) || empty($targetBomIds) || !is_array($targetBomIds)) {
                throw new \Exception("Pilih 1 Resep Sumber dan minimal 1 Resep Target.");
            }

            if (!$copyItems && !$copyOps) {
                throw new \Exception("Anda harus memilih minimal satu elemen yang akan di-copy (Bahan Baku atau Tahapan Operasi).");
            }

            $this->db->transStart();

            $sourceItems = [];
            $sourceOps = [];

            if ($copyItems) {
                $sourceItems = $this->db->table('bom_items')->where('bom_id', $sourceBomId)->get()->getResultArray();
                if (empty($sourceItems)) throw new \Exception("Resep Sumber tidak memiliki Bahan Baku untuk di-copy.");
            }

            if ($copyOps) {
                $sourceOps = $this->db->table('bom_operations')->where('bom_id', $sourceBomId)->orderBy('step_order', 'ASC')->get()->getResultArray();
                if (empty($sourceOps)) throw new \Exception("Resep Sumber tidak memiliki Tahapan Operasi untuk di-copy.");
            }

            foreach ($targetBomIds as $targetId) {
                if ($copyItems) {
                    $this->db->table('bom_items')->where('bom_id', $targetId)->delete();
                    foreach ($sourceItems as $item) {
                        $this->db->table('bom_items')->insert([
                            'bom_id'        => $targetId,
                            'section_name'  => $item['section_name'],
                            'section_code'  => $item['section_code'],
                            'rm_sku'        => $item['rm_sku'],
                            'size_per_item' => $item['size_per_item'],
                            'size_uom'      => $item['size_uom'],
                            'qty_per_item'  => $item['qty_per_item'],
                            'qty_uom'       => $item['qty_uom'],
                            'qty_required'  => $item['qty_required'],
                            'unit'          => $item['unit']
                        ]);
                    }
                }

                if ($copyOps) {
                    $this->db->table('bom_operations')->where('bom_id', $targetId)->delete();
                    foreach ($sourceOps as $op) {
                        $this->db->table('bom_operations')->insert([
                            'bom_id'             => $targetId,
                            'step_order'         => $op['step_order'],
                            'section_code'       => $op['section_code'],
                            'operation_name'     => $op['operation_name'],
                            'worker_type'        => $op['worker_type'],
                            'specialty_required' => $op['specialty_required'],
                            'wage_per_piece'     => $op['wage_per_piece'],
                            'is_final_step'      => $op['is_final_step']
                        ]);
                    }
                }
            }

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                throw new \Exception("Sistem gagal menerapkan Copy Massal.");
            }

            $count = count($targetBomIds);
            return redirect()->back()->with('success', "Berhasil menimpa formulasi resep ke <b>{$count} Resep Target</b>.");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}