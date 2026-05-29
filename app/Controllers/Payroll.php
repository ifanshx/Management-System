<?php

namespace App\Controllers;

use App\Models\PayrollModel;
use App\Models\PayrollDetailModel;
use App\Models\EmployeeModel;
use App\Models\AttendanceModel;
use App\Models\WorkShiftModel;
use App\Services\AccountingService;
use CodeIgniter\HTTP\RedirectResponse;

class Payroll extends BaseController
{
    public function __construct()
    {
        $db = \Config\Database::connect();
        try { $db->query("ALTER TABLE payroll_details ADD COLUMN overtime_meal_allowance DECIMAL(15,2) DEFAULT 0 AFTER overtime_pay"); } catch (\Exception $e) {}
        try { $db->query("ALTER TABLE payroll_details ADD COLUMN meal_taken_deduction DECIMAL(15,2) DEFAULT 0 AFTER meal_allowance"); } catch (\Exception $e) {}
    }

    public function index(): string|RedirectResponse
    {
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'admin') return redirect()->to('/portal');

        $payrollModel = new PayrollModel();
        return view('payroll/index', [
            'title'    => 'Kelola Penggajian (Payroll)',
            'payrolls' => $payrollModel->orderBy('created_at', 'DESC')->findAll()
        ]);
    }

    public function generate(): RedirectResponse
    {
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'admin') return redirect()->to('/portal');

        $db = \Config\Database::connect();
        $empModel   = new EmployeeModel();
        $attModel   = new AttendanceModel();
        $shiftModel = new WorkShiftModel();

        $employeeStatus = trim((string)$this->request->getPost('employee_status')); 
        $salaryType     = trim((string)$this->request->getPost('salary_type'));     
        $periodStart    = (string)$this->request->getPost('period_start');
        $periodEnd      = (string)$this->request->getPost('period_end');

        if (empty($employeeStatus) || empty($salaryType) || empty($periodStart) || empty($periodEnd)) return redirect()->back()->with('error', 'Semua field wajib diisi!');
        if ($periodStart > $periodEnd) return redirect()->back()->with('error', 'Tanggal mulai tidak boleh lebih besar dari tanggal selesai!');

        $existingPayroll = $db->table('payrolls')->where(['employee_status' => $employeeStatus, 'salary_type' => $salaryType, 'period_start' => $periodStart, 'period_end' => $periodEnd])->get()->getRowArray();
        if ($existingPayroll) return redirect()->back()->with('error', "Dokumen payroll {$employeeStatus} ({$salaryType}) untuk periode tersebut sudah ada.");

        $builder = $empModel->groupStart()->where('is_active', 1)->orWhere('resign_date >=', $periodStart)->groupEnd()->where('salary_type', $salaryType);

        if ($employeeStatus === 'Borongan') {
            $leaderIds = $db->table('employees')->select('leader_id')->where('leader_id IS NOT NULL')->where('leader_id !=', '')->get()->getResultArray();
            $lIds = array_filter(array_column($leaderIds, 'leader_id'));
            
            if (!empty($lIds)) {
                $builder->groupStart()->where('status', 'Borongan')->orWhereIn('employee_id', $lIds)->groupEnd();
            } else {
                $builder->where('status', 'Borongan');
            }
        } else {
            $builder->where('status', $employeeStatus);
        }
        
        $employees = $builder->findAll();
        if (empty($employees)) return redirect()->back()->with('error', "Tidak ada Karyawan untuk siklus {$employeeStatus} di rentang tanggal tersebut.");

        $db->transBegin();

        try {
            $codePrefix = match ($employeeStatus) { 'Tetap' => 'TTP', 'Borongan' => 'BRG', 'Magang' => 'MAG', default => 'PAY' };
            $payrollCode = 'PAY-' . date('Ymd-His') . '-' . $codePrefix . '-' . strtoupper(substr($salaryType, 0, 3));

            $db->table('payrolls')->insert([
                'payroll_code' => $payrollCode, 'employee_status' => $employeeStatus, 'salary_type' => $salaryType, 'period_start' => $periodStart,
                'period_end' => $periodEnd, 'status' => 'Draft', 'total_employees' => 0, 'total_amount' => 0,
                'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')
            ]);
            $payrollId = $db->insertID();

            $grandTotal = 0;
            $processedCount = 0;

            foreach ($employees as $emp) {
                $shift = !empty($emp['shift_id']) ? $shiftModel->find($emp['shift_id']) : null;
                $penaltyRate = (float) ($shift['late_penalty_rate'] ?? 0);
                $empOvertimeRate = (float) ($emp['overtime_rate'] ?? 0);
                $empMealRate = (float) ($emp['meal_allowance'] ?? 0);

                $attendances = $attModel->where('employee_id', $emp['employee_id'])->where('date >=', $periodStart)->where('date <=', $periodEnd)->whereIn('status', ['Hadir', 'Terlambat', 'Cuti', 'Izin'])->findAll();

                $totalPresent = count($attendances); $totalLate = 0; $totalOvertime = 0;
                $mealTakenCount = 0; $earnedMealCount = 0; $overtimeMealAmount = 0; 
                $accumulatedBasicSalary = 0; $dailyBasicRate = (float) ($emp['basic_salary'] ?? 0);
                $fullDayMin = $shift ? (int)($shift['full_day_duration'] ?? 480) : 480;
                $weeksWorked = ($totalPresent > 0) ? ceil($totalPresent / 6) : 0;

                foreach ($attendances as $att) {
                    $totalLate += (int) ($att['late_minutes'] ?? 0);
                    $totalOvertime += (int) ($att['overtime_minutes'] ?? 0);
                    $workDuration = (int) ($att['work_duration_minutes'] ?? 0);

                    if (in_array($att['status'], ['Hadir', 'Terlambat'])) {
                        if ($workDuration >= ($shift ? (int)($shift['half_day_duration'] ?? 240) : 240)) $earnedMealCount++; 
                        if ($fullDayMin > 0) $accumulatedBasicSalary += ($dailyBasicRate * min(1, $workDuration / $fullDayMin));
                    } else if (in_array($att['status'], ['Cuti', 'Izin'])) {
                        $accumulatedBasicSalary += $dailyBasicRate;
                    }

                    if (!empty($att['is_meal_taken']) && $att['is_meal_taken'] == 1) $mealTakenCount++;
                    if (!empty($att['is_overtime_taken']) && $att['is_overtime_taken'] == 1) $overtimeMealAmount += (float) ($att['overtime_meal_amount'] ?? 0);
                }

                $boronganPay = 0; $basicSalary = 0; $position = 0; $transport = 0; $overtimePay = 0; 
                $latePenalty = 0; $bpjs = 0; $grossMealEarned = 0; $mealTakenValue = 0; 
                $totalKasbon = 0; $kasbonIds = [];

                $isAnakBuah = !empty($emp['leader_id']);
                $teamIds = [$emp['employee_id']];
                $anakBuahs = $db->table('employees')->where('leader_id', $emp['employee_id'])->get()->getResultArray();
                foreach($anakBuahs as $ab) { $teamIds[] = $ab['employee_id']; }

                // 1. LOGIKA SIKLUS BORONGAN
                if ($employeeStatus === 'Borongan') {
                    if (!$isAnakBuah) {
                        if ($db->tableExists('production_logs')) {
                            $sumWage = $db->table('production_logs')->whereIn('employee_id', $teamIds)->where('status', 'Approved') ->where('DATE(production_date) >=', $periodStart)->where('DATE(production_date) <=', $periodEnd)->selectSum('total_wage')->get()->getRowArray();
                            $boronganPay = (float)($sumWage['total_wage'] ?? 0);
                        }

                        $allKasbon = $db->table('cash_advances')->whereIn('employee_id', $teamIds)->where('status', 'Belum Lunas')->get()->getResultArray();
                        foreach ($allKasbon as $kb) { 
                            $isCicilan = (stripos($kb['description'], 'Cicilan') !== false);
                            if (($isCicilan && $kb['tempo_date'] <= $periodEnd) || (!$isCicilan && $kb['date'] <= $periodEnd)) {
                                $totalKasbon += (float) ($kb['amount'] ?? 0); $kasbonIds[] = $kb['id']; 
                            }
                        }
                    }
                    if ($emp['status'] !== 'Borongan' && $boronganPay <= 0 && $totalKasbon <= 0) continue;
                }

                // 2. LOGIKA SIKLUS TETAP
                if ($employeeStatus === 'Tetap' || $employeeStatus === 'Magang') {
                    $grossMealEarned = $empMealRate * $earnedMealCount;
                    $mealTakenValue  = $empMealRate * $mealTakenCount;

                    $basicSalary = round($accumulatedBasicSalary);
                    $position    = (float) ($emp['position_allowance'] ?? 0);
                    $transport   = (float) ($emp['transport_allowance'] ?? 0) * $weeksWorked;
                    $overtimePay = round(($totalOvertime / 60) * $empOvertimeRate); 
                    
                    if ($employeeStatus === 'Tetap') {
                        $latePenalty = (float) $totalLate * $penaltyRate;
                        if (!empty($emp['bpjs_kesehatan']) && $emp['bpjs_kesehatan'] == 1) $bpjs += ($basicSalary * 0.01);
                        if (!empty($emp['bpjs_ketenagakerjaan']) && $emp['bpjs_ketenagakerjaan'] == 1) $bpjs += ($basicSalary * 0.02);
                    }

                    $allKasbon = $db->table('cash_advances')->where('employee_id', $emp['employee_id'])->where('status', 'Belum Lunas')->get()->getResultArray();
                    foreach ($allKasbon as $kb) { 
                        $isCicilan = (stripos($kb['description'], 'Cicilan') !== false);
                        if (($isCicilan && $kb['tempo_date'] <= $periodEnd) || (!$isCicilan && $kb['date'] <= $periodEnd)) {
                            $totalKasbon += (float) ($kb['amount'] ?? 0); $kasbonIds[] = $kb['id']; 
                        }
                    }
                }

                $grossSalary    = $basicSalary + $boronganPay + $position + $grossMealEarned + $transport + $overtimePay;
                $totalDeduction = $latePenalty + $bpjs + $totalKasbon + $mealTakenValue;

                // PROTEKSI UNBALANCED JOURNAL: Jika Potongan melebihi Gross, Kasbon dibatasi
                if ($totalDeduction > $grossSalary) {
                    $maxKasbonAllowed = max(0, $grossSalary - ($latePenalty + $bpjs + $mealTakenValue));
                    $totalKasbon = $maxKasbonAllowed; // Sisa kasbon akan tetap "Belum Lunas" otomatis
                    $totalDeduction = $latePenalty + $bpjs + $totalKasbon + $mealTakenValue;
                }

                $netSalary = max(0, $grossSalary - $totalDeduction);

                $db->table('payroll_details')->insert([
                    'payroll_id'             => $payrollId, 
                    'employee_id'            => $emp['employee_id'],
                    'total_present'          => $totalPresent, 
                    'total_late_minutes'     => $totalLate,
                    'total_overtime_minutes' => $totalOvertime, 
                    'basic_salary'           => $basicSalary,
                    'borongan_pay'           => $boronganPay, 
                    'position_allowance'     => $position,
                    'meal_allowance'         => $grossMealEarned, 
                    'meal_taken_deduction'   => $mealTakenValue,  
                    'transport_allowance'    => $transport,
                    'overtime_pay'           => $overtimePay, 
                    'overtime_meal_allowance'=> $overtimeMealAmount,
                    'late_penalty'           => $latePenalty, 
                    'cash_advance'           => $totalKasbon, 
                    'bpjs_deduction'         => $bpjs, 
                    'net_salary'             => $netSalary
                ]);

                if (!empty($kasbonIds) && $totalKasbon > 0) { 
                    $db->table('cash_advances')->whereIn('id', $kasbonIds)->update(['payroll_id' => $payrollId, 'updated_at' => date('Y-m-d H:i:s')]); 
                }
                
                $grandTotal += $netSalary;
                $processedCount++;
            }

            $db->table('payrolls')->where('id', $payrollId)->update(['total_amount' => $grandTotal, 'total_employees' => $processedCount, 'updated_at' => date('Y-m-d H:i:s')]);
            if ($db->transStatus() === false) throw new \Exception("Database Error: Gagal mencatat perhitungan gaji.");

            $db->transCommit();
            return redirect()->to('/payroll/detail/' . $payrollId)->with('success', 'Payroll berhasil dikalkulasi secara terpisah sesuai siklus!');
        } catch (\Throwable $e) {
            $db->transRollback();
            return redirect()->back()->with('error', 'Error Sistem: ' . $e->getMessage());
        }
    }

    public function detail($id): string|RedirectResponse 
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

   public function push_to_finance(): RedirectResponse 
    { 
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'admin') {
            return redirect()->to('/portal');
        }
        
        $payrollId   = (int) $this->request->getPost('payroll_id'); 
        $payrollCode = (string) $this->request->getPost('payroll_code'); 
        $picName     = session()->get('name') ?? 'Sistem HRD';
        
        $db = \Config\Database::connect(); 
        $payroll = $db->table('payrolls')->where('id', $payrollId)->get()->getRowArray();
        
        if (!$payroll || $payroll['status'] !== 'Draft') {
            return redirect()->back()->with('error', 'Dokumen tidak valid atau dana sudah dicairkan.');
        }
        
        $akunKas     = $db->table('chart_of_accounts')->where('account_code', '1-1000')->get()->getRowArray(); 
        $akunBank    = $db->table('chart_of_accounts')->where('account_code', '1-2000')->get()->getRowArray(); 
        $akunBeban   = $db->table('chart_of_accounts')->where('account_code', '5-2000')->get()->getRowArray();
        $akunPiutang = $db->table('chart_of_accounts')->where('account_code', '1-4001')->get()->getRowArray();
        
        if (!$akunKas || !$akunBank || !$akunBeban || !$akunPiutang) {
            return redirect()->back()->with('error', 'Gagal: Akun Akuntansi (Kas/Bank/Beban/Piutang) tidak ditemukan.');
        }
        
        $db->transBegin();
        try {
            $details = $db->table('payroll_details')
                ->select('payroll_details.*, employees.leader_id, employees.payment_method')
                ->join('employees', 'employees.employee_id = payroll_details.employee_id', 'left')
                ->where('payroll_id', $payrollId)
                ->get()->getResultArray();
            
            $totalGross          = 0;
            $totalKasbon         = 0;
            $totalPenaltyAndBpjs = 0;
            $totalCash           = 0;
            $totalTransfer       = 0;
            
            foreach ($details as $d) {
                if (!empty($d['leader_id'])) continue; 

                $gross       = (float)$d['basic_salary'] + (float)$d['borongan_pay'] + (float)$d['position_allowance'] + (float)$d['meal_allowance'] + (float)$d['transport_allowance'] + (float)$d['overtime_pay'];
                $kasbon      = (float)$d['cash_advance'];
                $penaltyBpjs = (float)$d['late_penalty'] + (float)$d['bpjs_deduction'] + (float)($d['meal_taken_deduction'] ?? 0);
                $netSalary   = (float)$d['net_salary'];

                // HACK SAFETY AKUNTANSI: Mencegah Unbalanced Journal karena data lama/corrupt
                if (round($gross, 2) !== round($netSalary + $kasbon + $penaltyBpjs, 2)) {
                    $gross = $netSalary + $kasbon + $penaltyBpjs;
                }

                $totalGross          += $gross;
                $totalKasbon         += $kasbon;
                $totalPenaltyAndBpjs += $penaltyBpjs;

                // SOLUSI: Mengambil data asli karyawan. Jika khusus Borongan, baru paksa Transfer
                $paymentMethod = $d['payment_method'];
                if ($payroll['employee_status'] === 'Borongan') {
                    $paymentMethod = 'Transfer';
                }
                
                if ($paymentMethod === 'Transfer') {
                    $totalTransfer += $netSalary;
                } else {
                    $totalCash += $netSalary;
                }
            }

            $saldoKas  = (float) ($db->query("SELECT calculated_balance FROM v_account_balances WHERE id = ?", [$akunKas['id']])->getRowArray()['calculated_balance'] ?? 0); 
            $saldoBank = (float) ($db->query("SELECT calculated_balance FROM v_account_balances WHERE id = ?", [$akunBank['id']])->getRowArray()['calculated_balance'] ?? 0);
            
            if ($totalCash > 0 && $saldoKas < $totalCash) throw new \Exception('Pencairan Ditolak: Saldo Laci Tunai tidak mencukupi.'); 
            if ($totalTransfer > 0 && $saldoBank < $totalTransfer) throw new \Exception('Pencairan Ditolak: Saldo Rekening Bank tidak mencukupi.');

            $db->table('payrolls')->where('id', $payrollId)->update(['status' => 'Paid', 'updated_at' => date('Y-m-d H:i:s')]);
            $db->table('cash_advances')->where('payroll_id', $payrollId)->update(['status' => 'Lunas', 'updated_at' => date('Y-m-d H:i:s')]);

            $accService = new AccountingService();
            $journalItems = [
                ['account_id' => $akunBeban['id'], 'debit' => $totalGross, 'credit' => 0, 'memo' => 'Beban Gaji Karyawan (Kotor)']
            ];
            
            if ($totalKasbon > 0) $journalItems[] = ['account_id' => $akunPiutang['id'], 'debit' => 0, 'credit' => $totalKasbon, 'memo' => 'Pemotongan Piutang Kasbon'];
            if ($totalPenaltyAndBpjs > 0) $journalItems[] = ['account_id' => $akunBeban['id'], 'debit' => 0, 'credit' => $totalPenaltyAndBpjs, 'memo' => 'Koreksi Potongan (Denda, BPJS, Makan)'];
            if ($totalCash > 0) $journalItems[] = ['account_id' => $akunKas['id'], 'debit' => 0, 'credit' => $totalCash, 'memo' => 'Pencairan THP via Tunai'];
            if ($totalTransfer > 0) $journalItems[] = ['account_id' => $akunBank['id'], 'debit' => 0, 'credit' => $totalTransfer, 'memo' => 'Pencairan THP via Transfer'];

            $jrnId = $accService->createJournal(date('Y-m-d'), "Pencairan Gaji: {$payrollCode} ({$payroll['employee_status']})", 'PAYROLL', $payrollCode, $totalGross, $journalItems, $picName, $payrollId);
            
            $dateCode = date('Ymd');
            if ($totalCash > 0) {
                $lastTrxCash = $db->table('operational_cash')->like('transaction_code', "TRX-$dateCode-", 'after')->orderBy('id', 'DESC')->get()->getRowArray();
                $newNumberCash = $lastTrxCash ? (int) explode('-', $lastTrxCash['transaction_code'])[2] + 1 : 1;
                $db->table('operational_cash')->insert(['transaction_code' => "TRX-$dateCode-" . str_pad($newNumberCash, 3, '0', STR_PAD_LEFT), 'transaction_date' => date('Y-m-d'), 'type' => 'Cash Out', 'metode' => 'Cash', 'category' => 'Payroll Gaji', 'amount' => $totalCash, 'description' => "Pencairan Gaji (Tunai): {$payrollCode}", 'pic_name' => $picName, 'journal_id' => $jrnId, 'status' => 'POSTED']);
            }
            if ($totalTransfer > 0) {
                $lastTrxBank = $db->table('operational_cash')->like('transaction_code', "TRX-$dateCode-", 'after')->orderBy('id', 'DESC')->get()->getRowArray();
                $newNumberBank = $lastTrxBank ? (int) explode('-', $lastTrxBank['transaction_code'])[2] + 1 : 1;
                $db->table('operational_cash')->insert(['transaction_code' => "TRX-$dateCode-" . str_pad($newNumberBank, 3, '0', STR_PAD_LEFT), 'transaction_date' => date('Y-m-d'), 'type' => 'Cash Out', 'metode' => 'ATM', 'category' => 'Payroll Gaji', 'amount' => $totalTransfer, 'description' => "Pencairan Gaji (Transfer ATM): {$payrollCode}", 'pic_name' => $picName, 'journal_id' => $jrnId, 'status' => 'POSTED']);
            }
            
            if ($db->transStatus() === false) throw new \Exception('Database Error saat mengunci data Akuntansi.');
            $db->transCommit();
            
            return redirect()->back()->with('success', "Pencairan berhasil dan Akuntansi seimbang (Balanced)!");
        } catch (\Exception $e) { 
            $db->transRollback(); 
            return redirect()->back()->with('error', $e->getMessage()); 
        }
    }

    public function print_slip($detail_id): string|RedirectResponse 
    { 
        if (!session()->get('isLoggedIn')) return redirect()->to('/portal');
        $db = \Config\Database::connect();
        
        $slip = $db->table('payroll_details')
            ->select('payroll_details.*, payrolls.period_start, payrolls.period_end, payrolls.payroll_code, payrolls.employee_status as payroll_type, employees.name, departments.name as department, positions.name as position, employees.salary_type, employees.payment_method, employees.employee_id')
            ->join('payrolls', 'payrolls.id = payroll_details.payroll_id')
            ->join('employees', 'employees.employee_id = payroll_details.employee_id')
            ->join('departments', 'departments.id = employees.department_id', 'left')
            ->join('positions', 'positions.id = employees.position_id', 'left')
            ->where('payroll_details.id', $detail_id)
            ->get()->getRowArray();
            
        if (!$slip) return redirect()->back()->with('error', 'Slip Gaji tidak ditemukan.');

        // SOLUSI SLIP: Cetakan Slip Otomatis Berubah Jadi ATM Hanya Saat Gaji Borongan
        if ($slip['payroll_type'] === 'Borongan') {
            $slip['payment_method'] = 'Transfer';
        }

        $teamIds = [$slip['employee_id']];
        $isBoronganPayroll = ($slip['payroll_type'] === 'Borongan');

        if ($isBoronganPayroll) {
            $anakBuahs = $db->table('employees')->where('leader_id', $slip['employee_id'])->get()->getResultArray();
            foreach($anakBuahs as $ab) { $teamIds[] = $ab['employee_id']; }
        }

        $boronganDetails = [];
        if ($slip['borongan_pay'] > 0 && $isBoronganPayroll) {
            $boronganDetails = $db->table('production_logs')
                ->select('warehouse_inventory.item_name, warehouse_inventory.item_type, production_logs.operation_name, SUM(production_logs.qty_produced) as total_qty, production_logs.wage_per_piece, SUM(production_logs.total_wage) as total_wage')
                ->join('warehouse_inventory', 'warehouse_inventory.sku = production_logs.sku', 'left')
                ->whereIn('production_logs.employee_id', $teamIds)->where('production_logs.status', 'Approved') 
                ->where('DATE(production_logs.production_date) >=', $slip['period_start'])->where('DATE(production_logs.production_date) <=', $slip['period_end'])
                ->groupBy('warehouse_inventory.item_name, warehouse_inventory.item_type, production_logs.operation_name, production_logs.wage_per_piece')
                ->get()->getResultArray();
        }
        
        $kasbonDetails = $db->table('cash_advances')->select('cash_advances.*, employees.name as emp_name')->join('employees', 'employees.employee_id = cash_advances.employee_id', 'left')
            ->whereIn('cash_advances.employee_id', $teamIds)->where('cash_advances.payroll_id', $slip['payroll_id'])->get()->getResultArray();
            
        $companyModel = new \App\Models\CompanyModel();
        return view('payroll/payslip', ['slip' => $slip, 'company' => $companyModel->first(), 'boronganDetails' => $boronganDetails, 'kasbonDetails' => $kasbonDetails]);
    }
    
    public function delete($id): RedirectResponse 
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
            return redirect()->to('/payroll')->with('success', 'Dokumen payroll DRAFT berhasil dibatalkan dan dihapus. Data Kasbon telah dikembalikan.');
        } catch (\Throwable $e) { 
            $db->transRollback(); return redirect()->back()->with('error', 'Error Sistem: ' . $e->getMessage()); 
        }
    }
}