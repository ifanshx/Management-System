<?php

namespace App\Controllers;

use App\Models\PayrollModel;
use App\Models\PayrollDetailModel;
use App\Models\EmployeeModel;
use App\Models\AttendanceModel;
use App\Models\WorkShiftModel;
use App\Services\AccountingService;

class Payroll extends BaseController
{
    public function __construct()
    {
        $db = \Config\Database::connect();
        try { $db->query("ALTER TABLE payroll_details ADD COLUMN overtime_meal_allowance DECIMAL(15,2) DEFAULT 0 AFTER overtime_pay"); } catch (\Exception $e) {}
    }

    public function index()
    {
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'admin') return redirect()->to('/portal');

        $payrollModel = new PayrollModel();
        return view('payroll/index', [
            'title'    => 'Kelola Penggajian (Payroll)',
            'payrolls' => $payrollModel->orderBy('created_at', 'DESC')->findAll()
        ]);
    }

    public function generate()
    {
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'admin') return redirect()->to('/portal');

        $db = \Config\Database::connect();
        $empModel   = new EmployeeModel();
        $attModel   = new AttendanceModel();
        $shiftModel = new WorkShiftModel();

        $employeeStatus = trim($this->request->getPost('employee_status')); 
        $salaryType     = trim($this->request->getPost('salary_type'));     
        $periodStart    = $this->request->getPost('period_start');
        $periodEnd      = $this->request->getPost('period_end');

        if (empty($employeeStatus) || empty($salaryType) || empty($periodStart) || empty($periodEnd)) {
            return redirect()->back()->with('error', 'Semua field wajib diisi!');
        }

        if ($periodStart > $periodEnd) {
            return redirect()->back()->with('error', 'Tanggal mulai tidak boleh lebih besar dari tanggal selesai!');
        }

        $existingPayroll = $db->table('payrolls')->where([
            'employee_status' => $employeeStatus, 'salary_type' => $salaryType,
            'period_start'    => $periodStart, 'period_end' => $periodEnd
        ])->get()->getRowArray();

        if ($existingPayroll) return redirect()->back()->with('error', "Dokumen payroll {$employeeStatus} ({$salaryType}) untuk periode tersebut sudah ada.");

        $employees = $empModel->groupStart()->where('is_active', 1)->orWhere('resign_date >=', $periodStart)->groupEnd()->where('status', $employeeStatus)->where('salary_type', $salaryType)->findAll();
        if (empty($employees)) return redirect()->back()->with('error', "Tidak ada Karyawan {$employeeStatus} dengan siklus gaji {$salaryType} di rentang tanggal tersebut.");

        $db->transBegin();

        try {
            $codePrefix = match ($employeeStatus) { 'Tetap' => 'TTP', 'Borongan' => 'BRG', 'Magang' => 'MAG', default => 'PAY' };
            $payrollCode = 'PAY-' . date('Ymd-His') . '-' . $codePrefix . '-' . strtoupper(substr($salaryType, 0, 3));

            $db->table('payrolls')->insert([
                'payroll_code' => $payrollCode, 'employee_status' => $employeeStatus, 'salary_type' => $salaryType, 'period_start' => $periodStart,
                'period_end' => $periodEnd, 'status' => 'Draft', 'total_employees' => count($employees), 'total_amount' => 0,
                'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')
            ]);
            $payrollId = $db->insertID();

            $payrollDetails = [];

            // 1. LAKUKAN KALKULASI AWAL UNTUK SEMUA ORANG
            foreach ($employees as $emp) {
                $shift = !empty($emp['shift_id']) ? $shiftModel->find($emp['shift_id']) : null;
                $penaltyRate = (float) ($shift['late_penalty_rate'] ?? 0);
                $empOvertimeRate = (float) ($emp['overtime_rate'] ?? 0);
                $empMealRate = (float) ($emp['meal_allowance'] ?? 0);

                $attendances = $attModel->where('employee_id', $emp['employee_id'])->where('date >=', $periodStart)->where('date <=', $periodEnd)->whereIn('status', ['Hadir', 'Terlambat', 'Cuti', 'Izin'])->findAll();

                $totalPresent   = count($attendances);
                $totalLate      = 0;
                $totalOvertime  = 0;
                $mealTakenCount = 0;
                $earnedMealCount = 0; 
                $overtimeMealAmount = 0; 

                $accumulatedBasicSalary = 0;
                $dailyBasicRate = (float) ($emp['basic_salary'] ?? 0);
                $fullDayMin = $shift ? (int)($shift['full_day_duration'] ?? 480) : 480;

                foreach ($attendances as $att) {
                    $totalLate      += (int) ($att['late_minutes'] ?? 0);
                    $totalOvertime += (int) ($att['overtime_minutes'] ?? 0);
                    $workDuration   = (int) ($att['work_duration_minutes'] ?? 0);

                    if (in_array($att['status'], ['Hadir', 'Terlambat'])) {
                        $halfDayLimit = $shift ? (int)($shift['half_day_duration'] ?? 240) : 240;
                        
                        // LOGIKA BARU: Uang Makan & Gaji strictly mengikuti jam kerja
                        // Karyawan HANYA dapat uang makan jika durasinya >= batas setengah hari
                        if ($workDuration >= $halfDayLimit) {
                            $earnedMealCount++; 
                        }

                        // --- MENGHITUNG GAJI POKOK PROPORSIONAL ---
                        if ($fullDayMin > 0) {
                            // Batasi rasio maksimal 1 (jadi tidak dobel gaji jika dia kerja 12 jam)
                            $ratio = min(1, $workDuration / $fullDayMin); 
                            $accumulatedBasicSalary += ($dailyBasicRate * $ratio);
                        }
                    } else if (in_array($att['status'], ['Cuti', 'Izin'])) {
                        // Jika Cuti/Izin berbayar, dia dianggap mendapat gaji full hari itu
                        $accumulatedBasicSalary += $dailyBasicRate;
                    }

                    if (!empty($att['is_meal_taken']) && $att['is_meal_taken'] == 1) $mealTakenCount++;
                    if (!empty($att['is_overtime_taken']) && $att['is_overtime_taken'] == 1) {
                        $overtimeMealAmount += (float) ($att['overtime_meal_amount'] ?? 0);
                    }
                }

                $kasbonData = $db->table('cash_advances')
                    ->where('employee_id', $emp['employee_id'])
                    ->where('status', 'Belum Lunas')
                    ->groupStart()
                        ->groupStart()->like('description', 'Cicilan')->where('tempo_date <=', $periodEnd)->groupEnd()
                        ->orGroupStart()->notLike('description', 'Cicilan')->where('date <=', $periodEnd)->groupEnd()
                    ->groupEnd()->get()->getResultArray();

                $totalKasbon = 0; 
                $kasbonIds = [];
                foreach ($kasbonData as $kb) { 
                    $totalKasbon += (float) ($kb['amount'] ?? 0); 
                    $kasbonIds[] = $kb['id']; 
                }

                $grossMealEarned = $empMealRate * $earnedMealCount;
                $mealTakenValue  = $empMealRate * $mealTakenCount;
                $mealAllowanceToGive = max(0, $grossMealEarned - $mealTakenValue); 

                $basicSalary = 0; $boronganPay = 0; $position = 0; $transport = 0; $overtimePay = 0; $latePenalty = 0; $bpjs = 0;

                if ($db->tableExists('production_logs')) {
                    $sumWage = $db->table('production_logs')
                        ->where('employee_id', $emp['employee_id'])
                        ->where('DATE(production_date) >=', $periodStart)
                        ->where('DATE(production_date) <=', $periodEnd)
                        ->selectSum('total_wage')->get()->getRowArray();
                    $boronganPay = (float)($sumWage['total_wage'] ?? 0);
                }

                if ($employeeStatus === 'Borongan') {
                    $transport = (float) ($emp['transport_allowance'] ?? 0);
                    $overtimeMealAmount = 0;
                } else {
                    if ($salaryType === 'Bulanan') {
                        $basicSalary = (float) ($emp['basic_salary'] ?? 0);
                    } else {
                        $basicSalary = round($accumulatedBasicSalary);
                    }

                    $position    = (float) ($emp['position_allowance'] ?? 0);
                    $transport   = (float) ($emp['transport_allowance'] ?? 0);
                    $overtimePay = round(($totalOvertime / 60) * $empOvertimeRate); 
                    $latePenalty = (float) $totalLate * $penaltyRate;
                    if (!empty($emp['bpjs_kesehatan']) && $emp['bpjs_kesehatan'] == 1) $bpjs += ($basicSalary * 0.01);
                    if (!empty($emp['bpjs_ketenagakerjaan']) && $emp['bpjs_ketenagakerjaan'] == 1) $bpjs += ($basicSalary * 0.02);
                }

                // Masukkan data mentah ke Array untuk dilebur nanti
                $payrollDetails[$emp['employee_id']] = [
                    'payroll_id'             => $payrollId, 
                    'employee_id'            => $emp['employee_id'],
                    'leader_id'              => $emp['leader_id'] ?? null, 
                    'total_present'          => $totalPresent, 
                    'total_late_minutes'     => $totalLate,
                    'total_overtime_minutes' => $totalOvertime, 
                    'basic_salary'           => $basicSalary,
                    'borongan_pay'           => $boronganPay, 
                    'position_allowance'     => $position,
                    'meal_allowance'         => $mealAllowanceToGive, 
                    'transport_allowance'    => $transport,
                    'overtime_pay'           => $overtimePay, 
                    'overtime_meal_allowance'=> $overtimeMealAmount,
                    'late_penalty'           => $latePenalty, 
                    'cash_advance'           => $totalKasbon, 
                    'bpjs_deduction'         => $bpjs, 
                    'net_salary'             => 0,
                    'kasbon_ids'             => $kasbonIds
                ];
            }

            // =================================================================
            // 2. SISTEM MANDOR (GROUP LEADER) - PENGGABUNGAN PENDAPATAN & HUTANG
            // =================================================================
            foreach ($payrollDetails as $empId => $data) {
                if (!empty($data['leader_id']) && isset($payrollDetails[$data['leader_id']])) {
                    $leaderId = $data['leader_id'];
                    
                    $payrollDetails[$leaderId]['basic_salary'] += $data['basic_salary'];
                    $payrollDetails[$leaderId]['borongan_pay'] += $data['borongan_pay'];
                    $payrollDetails[$leaderId]['position_allowance'] += $data['position_allowance'];
                    $payrollDetails[$leaderId]['meal_allowance'] += $data['meal_allowance'];
                    $payrollDetails[$leaderId]['transport_allowance'] += $data['transport_allowance'];
                    $payrollDetails[$leaderId]['overtime_pay'] += $data['overtime_pay'];
                    $payrollDetails[$leaderId]['overtime_meal_allowance'] += $data['overtime_meal_allowance'];
                    
                    $payrollDetails[$leaderId]['late_penalty'] += $data['late_penalty'];
                    $payrollDetails[$leaderId]['cash_advance'] += $data['cash_advance'];
                    $payrollDetails[$leaderId]['bpjs_deduction'] += $data['bpjs_deduction'];
                    
                    $payrollDetails[$leaderId]['kasbon_ids'] = array_merge($payrollDetails[$leaderId]['kasbon_ids'], $data['kasbon_ids']);

                    $payrollDetails[$empId]['basic_salary'] = 0;
                    $payrollDetails[$empId]['borongan_pay'] = 0;
                    $payrollDetails[$empId]['position_allowance'] = 0;
                    $payrollDetails[$empId]['meal_allowance'] = 0;
                    $payrollDetails[$empId]['transport_allowance'] = 0;
                    $payrollDetails[$empId]['overtime_pay'] = 0;
                    $payrollDetails[$empId]['overtime_meal_allowance'] = 0;
                    $payrollDetails[$empId]['late_penalty'] = 0;
                    $payrollDetails[$empId]['cash_advance'] = 0;
                    $payrollDetails[$empId]['bpjs_deduction'] = 0;
                    $payrollDetails[$empId]['kasbon_ids'] = [];
                }
            }

            // 3. FINALISASI PENYIMPANAN KE DATABASE
            $grandTotal = 0;
            foreach ($payrollDetails as $empId => $data) {
                $grossSalary    = $data['basic_salary'] + $data['borongan_pay'] + $data['position_allowance'] + $data['meal_allowance'] + $data['transport_allowance'] + $data['overtime_pay'];
                $totalDeduction = $data['late_penalty'] + $data['bpjs_deduction'] + $data['cash_advance'];
                $netSalary      = max(0, $grossSalary - $totalDeduction);

                $db->table('payroll_details')->insert([
                    'payroll_id'             => $data['payroll_id'], 
                    'employee_id'            => $data['employee_id'],
                    'total_present'          => $data['total_present'], 
                    'total_late_minutes'     => $data['total_late_minutes'],
                    'total_overtime_minutes' => $data['total_overtime_minutes'], 
                    'basic_salary'           => $data['basic_salary'],
                    'borongan_pay'           => $data['borongan_pay'], 
                    'position_allowance'     => $data['position_allowance'],
                    'meal_allowance'         => $data['meal_allowance'], 
                    'transport_allowance'    => $data['transport_allowance'],
                    'overtime_pay'           => $data['overtime_pay'], 
                    'overtime_meal_allowance'=> $data['overtime_meal_allowance'],
                    'late_penalty'           => $data['late_penalty'], 
                    'cash_advance'           => $data['cash_advance'], 
                    'bpjs_deduction'         => $data['bpjs_deduction'], 
                    'net_salary'             => $netSalary
                ]);

                if (!empty($data['kasbon_ids'])) { 
                    $db->table('cash_advances')->whereIn('id', $data['kasbon_ids'])->update(['payroll_id' => $payrollId, 'updated_at' => date('Y-m-d H:i:s')]); 
                }
                
                $grandTotal += $netSalary;
            }

            $db->table('payrolls')->where('id', $payrollId)->update(['total_amount' => $grandTotal, 'updated_at' => date('Y-m-d H:i:s')]);
            if ($db->transStatus() === false) throw new \Exception("Database Error: Gagal mencatat perhitungan gaji.");

            $db->transCommit();
            return redirect()->to('/payroll/detail/' . $payrollId)->with('success', 'Payroll berhasil dikalkulasi! Gaji & Uang Makan kini akurat sesuai jam kerja.');
        } catch (\Throwable $e) {
            $db->transRollback();
            return redirect()->back()->with('error', 'Error Sistem: ' . $e->getMessage());
        }
    }

    public function detail($id) 
    { 
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'admin') return redirect()->to('/portal');
        $db = \Config\Database::connect();
        
        $payroll = $db->table('payrolls')->where('id', $id)->get()->getRowArray();
        if (!$payroll) return redirect()->to('/payroll');
        
        $details = $db->table('payroll_details')
            ->select('payroll_details.*, employees.name, employees.leader_id, departments.name as department, positions.name as position, employees.bank_name, employees.bank_account, employees.payment_method, employees.meal_allowance as master_meal, employees.transport_allowance as master_transport, employees.basic_salary as master_basic, employees.overtime_rate as master_overtime')
            ->join('employees', 'employees.employee_id = payroll_details.employee_id')
            ->join('departments', 'departments.id = employees.department_id', 'left')
            ->join('positions', 'positions.id = employees.position_id', 'left')
            ->where('payroll_id', $id)
            ->get()->getResultArray();
            
        return view('payroll/detail', ['title' => 'Rincian Penggajian', 'payroll' => $payroll, 'details' => $details]);
    }

    public function push_to_finance() 
    { 
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'admin') return redirect()->to('/portal');
        
        $payrollId = $this->request->getPost('payroll_id'); 
        $totalCash = (float) $this->request->getPost('total_cash'); 
        $totalTransfer = (float) $this->request->getPost('total_transfer'); 
        $payrollCode = $this->request->getPost('payroll_code'); 
        $picName = session()->get('name') ?? 'Sistem HRD';
        
        $db = \Config\Database::connect(); 
        $payroll = $db->table('payrolls')->where('id', $payrollId)->get()->getRowArray();
        
        if (!$payroll || $payroll['status'] !== 'Draft') return redirect()->back()->with('error', 'Dokumen tidak valid atau dana sudah dicairkan sebelumnya.');
        
        $akunKas = $db->table('chart_of_accounts')->where('account_code', '1-1000')->get()->getRowArray(); 
        $akunBank = $db->table('chart_of_accounts')->where('account_code', '1-2000')->get()->getRowArray(); 
        $akunBeban = $db->table('chart_of_accounts')->where('account_code', '5-2000')->get()->getRowArray();
        $akunPiutang = $db->table('chart_of_accounts')->where('account_code', '1-4001')->get()->getRowArray();
        
        if (!$akunKas || !$akunBank || !$akunBeban || !$akunPiutang) return redirect()->back()->with('error', 'Gagal: Akun Akuntansi (Kas/Bank/Beban/Piutang) tidak ditemukan.');
        
        $saldoKas = $db->query("SELECT calculated_balance FROM v_account_balances WHERE id = ?", [$akunKas['id']])->getRowArray()['calculated_balance'] ?? 0; 
        $saldoBank = $db->query("SELECT calculated_balance FROM v_account_balances WHERE id = ?", [$akunBank['id']])->getRowArray()['calculated_balance'] ?? 0;
        
        if ($totalCash > 0 && $saldoKas < $totalCash) return redirect()->back()->with('error', 'Pencairan Ditolak: Saldo Laci Tunai tidak mencukupi.'); 
        if ($totalTransfer > 0 && $saldoBank < $totalTransfer) return redirect()->back()->with('error', 'Pencairan Ditolak: Saldo Rekening Bank tidak mencukupi.');
        
        $db->transBegin();
        try {
            $db->table('payrolls')->where('id', $payrollId)->update(['status' => 'Paid', 'updated_at' => date('Y-m-d H:i:s')]);
            $db->table('cash_advances')->where('payroll_id', $payrollId)->update(['status' => 'Lunas', 'updated_at' => date('Y-m-d H:i:s')]);
            
            $details = $db->table('payroll_details')->where('payroll_id', $payrollId)->get()->getResultArray();
            $totalGross = 0;
            $totalKasbon = 0;
            $totalPenaltyAndBpjs = 0;
            
            foreach ($details as $d) {
                $gross = $d['basic_salary'] + $d['borongan_pay'] + $d['position_allowance'] + $d['meal_allowance'] + $d['transport_allowance'] + $d['overtime_pay'];
                $totalGross += $gross;
                $totalKasbon += $d['cash_advance'];
                $totalPenaltyAndBpjs += ($d['late_penalty'] + $d['bpjs_deduction']);
            }

            $accService = new AccountingService();
            $journalItems = [
                ['account_id' => $akunBeban['id'], 'debit' => $totalGross, 'credit' => 0, 'memo' => 'Beban Gaji Karyawan (Kotor)']
            ];
            
            if ($totalKasbon > 0) {
                $journalItems[] = ['account_id' => $akunPiutang['id'], 'debit' => 0, 'credit' => $totalKasbon, 'memo' => 'Pemotongan Piutang Kasbon'];
            }
            if ($totalPenaltyAndBpjs > 0) {
                $journalItems[] = ['account_id' => $akunBeban['id'], 'debit' => 0, 'credit' => $totalPenaltyAndBpjs, 'memo' => 'Koreksi Potongan Denda & BPJS'];
            }
            if ($totalCash > 0) {
                $journalItems[] = ['account_id' => $akunKas['id'], 'debit' => 0, 'credit' => $totalCash, 'memo' => 'Pencairan THP via Tunai'];
            }
            if ($totalTransfer > 0) {
                $journalItems[] = ['account_id' => $akunBank['id'], 'debit' => 0, 'credit' => $totalTransfer, 'memo' => 'Pencairan THP via Transfer'];
            }

            $jrnId = $accService->createJournal(
                date('Y-m-d'), 
                "Pencairan Gaji: {$payrollCode} ({$payroll['employee_status']})", 
                'PAYROLL', 
                $payrollCode, 
                $totalGross, 
                $journalItems, 
                $picName,
                $payrollId
            );
            
            $dateCode = date('Ymd');
            
            if ($totalCash > 0) {
                $lastTrxCash = $db->table('operational_cash')->like('transaction_code', "TRX-$dateCode-", 'after')->orderBy('id', 'DESC')->get()->getRowArray();
                $newNumberCash = $lastTrxCash ? (int) explode('-', $lastTrxCash['transaction_code'])[2] + 1 : 1;
                $trxCodeCash = "TRX-$dateCode-" . str_pad($newNumberCash, 3, '0', STR_PAD_LEFT);
                
                $db->table('operational_cash')->insert([
                    'transaction_code' => $trxCodeCash, 'transaction_date' => date('Y-m-d'), 'type' => 'Cash Out', 'metode' => 'Cash', 
                    'category' => 'Payroll Gaji', 'amount' => $totalCash, 'description' => "Pencairan Gaji (Tunai): {$payrollCode}", 
                    'pic_name' => $picName, 'journal_id' => $jrnId, 'status' => 'POSTED'
                ]);
            }
            
            if ($totalTransfer > 0) {
                $lastTrxBank = $db->table('operational_cash')->like('transaction_code', "TRX-$dateCode-", 'after')->orderBy('id', 'DESC')->get()->getRowArray();
                $newNumberBank = $lastTrxBank ? (int) explode('-', $lastTrxBank['transaction_code'])[2] + 1 : 1;
                $trxCodeBank = "TRX-$dateCode-" . str_pad($newNumberBank, 3, '0', STR_PAD_LEFT);
                
                $db->table('operational_cash')->insert([
                    'transaction_code' => $trxCodeBank, 'transaction_date' => date('Y-m-d'), 'type' => 'Cash Out', 'metode' => 'ATM', 
                    'category' => 'Payroll Gaji', 'amount' => $totalTransfer, 'description' => "Pencairan Gaji (Transfer ATM): {$payrollCode}", 
                    'pic_name' => $picName, 'journal_id' => $jrnId, 'status' => 'POSTED'
                ]);
            }
            
            if ($db->transStatus() === false) throw new \Exception('Database Error saat mengunci data Akuntansi.');
            $db->transCommit();
            
            $msg = "Pencairan berhasil dan Akuntansi seimbang (Balanced)! Dokumen <b>#{$payrollCode}</b> terkunci.<br>";
            if ($totalCash > 0) $msg .= "<br>• Brankas Laci berkurang: <b>Rp " . number_format($totalCash, 0, ',', '.') . "</b>";
            if ($totalTransfer > 0) $msg .= "<br>• Rekening Bank berkurang: <b>Rp " . number_format($totalTransfer, 0, ',', '.') . "</b>";
            return redirect()->back()->with('success', $msg);
        } catch (\Exception $e) { 
            $db->transRollback(); 
            return redirect()->back()->with('error', $e->getMessage()); 
        }
    }

    public function print_slip($detail_id) 
    { 
        if (!session()->get('isLoggedIn')) return redirect()->to('/portal');
        $db = \Config\Database::connect();
        
        $slip = $db->table('payroll_details')
            ->select('payroll_details.*, payrolls.period_start, payrolls.period_end, payrolls.payroll_code, payrolls.employee_status, employees.name, departments.name as department, positions.name as position, employees.salary_type, employees.payment_method, employees.employee_id, employees.meal_allowance as master_meal, employees.transport_allowance as master_transport, employees.basic_salary as master_basic, employees.overtime_rate as master_overtime')
            ->join('payrolls', 'payrolls.id = payroll_details.payroll_id')
            ->join('employees', 'employees.employee_id = payroll_details.employee_id')
            ->join('departments', 'departments.id = employees.department_id', 'left')
            ->join('positions', 'positions.id = employees.position_id', 'left')
            ->where('payroll_details.id', $detail_id)
            ->get()->getRowArray();
            
        if (!$slip) return redirect()->back()->with('error', 'Slip Gaji tidak ditemukan.');

        $teamIds = [$slip['employee_id']];
        $anakBuahs = $db->table('employees')->where('leader_id', $slip['employee_id'])->get()->getResultArray();
        foreach($anakBuahs as $ab) {
            $teamIds[] = $ab['employee_id'];
        }

        $boronganDetails = [];
        if ($slip['borongan_pay'] > 0) {
            $boronganDetails = $db->table('production_logs')
                ->select('
                    warehouse_inventory.item_name, 
                    warehouse_inventory.item_type, 
                    production_logs.operation_name, 
                    SUM(production_logs.qty_produced) as total_qty, 
                    production_logs.wage_per_piece, 
                    SUM(production_logs.total_wage) as total_wage
                ')
                ->join('warehouse_inventory', 'warehouse_inventory.sku = production_logs.sku', 'left')
                ->whereIn('production_logs.employee_id', $teamIds)
                ->where('DATE(production_logs.production_date) >=', $slip['period_start'])
                ->where('DATE(production_logs.production_date) <=', $slip['period_end'])
                ->groupBy('warehouse_inventory.item_name, warehouse_inventory.item_type, production_logs.operation_name, production_logs.wage_per_piece')
                ->get()->getResultArray();
        }
        
        $kasbonDetails = $db->table('cash_advances')
            ->select('cash_advances.*, employees.name as emp_name')
            ->join('employees', 'employees.employee_id = cash_advances.employee_id', 'left')
            ->whereIn('cash_advances.employee_id', $teamIds)
            ->where('cash_advances.payroll_id', $slip['payroll_id'])
            ->get()->getResultArray();
            
        $companyModel = new \App\Models\CompanyModel();
        return view('payroll/payslip', [
            'slip' => $slip, 
            'company' => $companyModel->first(),
            'boronganDetails' => $boronganDetails,
            'kasbonDetails' => $kasbonDetails
        ]);
    }
    
    public function delete($id) 
    { 
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'admin') return redirect()->to('/portal');
        $db = \Config\Database::connect(); $payroll = $db->table('payrolls')->where('id', $id)->get()->getRowArray();
        if (!$payroll) return redirect()->back()->with('error', 'Dokumen payroll tidak ditemukan.');
        if ($payroll['status'] !== 'Draft') return redirect()->back()->with('error', 'Dokumen yang sudah dicairkan ke Keuangan tidak dapat dihapus demi integritas akuntansi!');
        
        $db->transBegin();
        try {
            $db->table('cash_advances')->where('payroll_id', $id)->update(['status' => 'Belum Lunas', 'payroll_id' => null, 'updated_at' => date('Y-m-d H:i:s')]);
            $db->table('payroll_details')->where('payroll_id', $id)->delete();
            $db->table('payrolls')->where('id', $id)->delete();
            if ($db->transStatus() === false) throw new \Exception('Database Error saat hapus.');
            $db->transCommit();
            return redirect()->to('/payroll')->with('success', 'Dokumen payroll DRAFT berhasil dibatalkan dan dihapus.');
        } catch (\Throwable $e) { 
            $db->transRollback(); 
            return redirect()->back()->with('error', 'Error Sistem: ' . $e->getMessage()); 
        }
    }
}