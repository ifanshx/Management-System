<?php

namespace App\Controllers;

use App\Models\PayrollModel;
use App\Models\PayrollDetailModel;
use App\Models\EmployeeModel;
use App\Models\AttendanceModel;
use App\Models\WorkShiftModel;

class Payroll extends BaseController
{
    public function index()
    {
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'admin') {
            return redirect()->to('/portal');
        }

        $payrollModel = new PayrollModel();

        $data = [
            'title'    => 'Kelola Penggajian (Payroll)',
            'payrolls' => $payrollModel->orderBy('created_at', 'DESC')->findAll()
        ];

        return view('payroll/index', $data);
    }

    public function generate()
    {
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'admin') {
            return redirect()->to('/portal');
        }

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
            'employee_status' => $employeeStatus,
            'salary_type'     => $salaryType,
            'period_start'    => $periodStart,
            'period_end'      => $periodEnd
        ])->get()->getRowArray();

        if ($existingPayroll) {
            return redirect()->back()->with('error', "Dokumen payroll {$employeeStatus} ({$salaryType}) untuk periode tersebut sudah ada.");
        }

        $employees = $empModel->where('is_active', 1)
                              ->where('status', $employeeStatus)
                              ->where('salary_type', $salaryType)
                              ->findAll();

        if (empty($employees)) {
            return redirect()->back()->with('error', "Tidak ada Karyawan {$employeeStatus} dengan siklus gaji {$salaryType} di sistem.");
        }

        $db->transBegin();

        try {
            $codePrefix = match ($employeeStatus) {
                'Tetap'    => 'TTP',
                'Borongan' => 'BRG',
                'Magang'   => 'MAG',
                default    => 'PAY'
            };

            $payrollCode = 'PAY-' . date('Ymd-His') . '-' . $codePrefix . '-' . strtoupper(substr($salaryType, 0, 3));

            $db->table('payrolls')->insert([
                'payroll_code'    => $payrollCode,
                'employee_status' => $employeeStatus,
                'salary_type'     => $salaryType,
                'period_start'    => $periodStart,
                'period_end'      => $periodEnd,
                'status'          => 'Draft',
                'total_employees' => count($employees),
                'total_amount'    => 0,
                'created_at'      => date('Y-m-d H:i:s'),
                'updated_at'      => date('Y-m-d H:i:s')
            ]);
            
            $payrollId = $db->insertID();
            $grandTotal = 0;

            foreach ($employees as $emp) {
                $shift = !empty($emp['shift_id']) ? $shiftModel->find($emp['shift_id']) : null;
                $penaltyRate = (float) ($shift['late_penalty_rate'] ?? 0);

                $attendances = $attModel->where('employee_id', $emp['employee_id'])
                                        ->where('date >=', $periodStart)
                                        ->where('date <=', $periodEnd)
                                        ->whereIn('status', ['Hadir', 'Terlambat', 'Cuti', 'Izin'])
                                        ->findAll();

                $totalPresent   = count($attendances);
                $totalLate      = 0;
                $totalOvertime  = 0;
                $mealTakenCount = 0;

                foreach ($attendances as $att) {
                    $totalLate     += (int) ($att['late_minutes'] ?? 0);
                    $totalOvertime += (int) ($att['overtime_minutes'] ?? 0);

                    if (!empty($att['is_meal_taken']) && $att['is_meal_taken'] == 1) {
                        $mealTakenCount++;
                    }
                }

                $kasbonData = $db->table('cash_advances')
                                 ->where('employee_id', $emp['employee_id'])
                                 ->where('status', 'Belum Lunas')
                                 ->where('tempo_date <=', $periodEnd)
                                 ->get()->getResultArray();

                $totalKasbon = 0;
                $kasbonIds   = [];

                foreach ($kasbonData as $kb) {
                    $totalKasbon += (float) ($kb['amount'] ?? 0);
                    $kasbonIds[] = $kb['id'];
                }

                $basicSalary = 0;
                $boronganPay = 0;
                $position    = 0;
                $meal        = 0;
                $transport   = 0;
                $overtimePay = 0;
                $latePenalty = 0;
                $bpjs        = 0;

                if ($employeeStatus === 'Borongan') {
                    if ($db->tableExists('production_logs')) {
                        $boronganData = $db->table('production_logs')
                                           ->selectSum('total_wage')
                                           ->where('employee_id', $emp['employee_id'])
                                           ->where('DATE(production_date) >=', $periodStart)
                                           ->where('DATE(production_date) <=', $periodEnd)
                                           ->get()->getRowArray();
                        $boronganPay = (float) ($boronganData['total_wage'] ?? 0);
                    }

                    $meal        = (float) ($emp['meal_allowance'] ?? 0) * max(0, ($totalPresent - $mealTakenCount));
                    $transport   = (float) ($emp['transport_allowance'] ?? 0) * $totalPresent;
                    $overtimePay = round(((float) ($emp['overtime_rate'] ?? 0) / 60) * $totalOvertime);
                    $latePenalty = (float) $totalLate * $penaltyRate;
                } 
                else {
                    if ($salaryType === 'Bulanan') {
                        $basicSalary = (float) ($emp['basic_salary'] ?? 0);
                    } else {
                        $basicSalary = (float) ($emp['basic_salary'] ?? 0) * $totalPresent;
                    }

                    $position    = (float) ($emp['position_allowance'] ?? 0);
                    $meal        = (float) ($emp['meal_allowance'] ?? 0) * max(0, ($totalPresent - $mealTakenCount));
                    $transport   = (float) ($emp['transport_allowance'] ?? 0) * $totalPresent;
                    $overtimePay = round(((float) ($emp['overtime_rate'] ?? 0) / 60) * $totalOvertime);
                    $latePenalty = (float) $totalLate * $penaltyRate;

                    if (!empty($emp['bpjs_kesehatan']) && $emp['bpjs_kesehatan'] == 1) {
                        $bpjs += ($basicSalary * 0.01);
                    }
                    if (!empty($emp['bpjs_ketenagakerjaan']) && $emp['bpjs_ketenagakerjaan'] == 1) {
                        $bpjs += ($basicSalary * 0.02);
                    }
                }

                $grossSalary    = $basicSalary + $boronganPay + $position + $meal + $transport + $overtimePay;
                $totalDeduction = $latePenalty + $bpjs + $totalKasbon;
                $netSalary      = $grossSalary - $totalDeduction;

                if ($netSalary < 0) $netSalary = 0;

                $db->table('payroll_details')->insert([
                    'payroll_id'             => $payrollId,
                    'employee_id'            => $emp['employee_id'],
                    'total_present'          => $totalPresent,
                    'total_late_minutes'     => $totalLate,
                    'total_overtime_minutes' => $totalOvertime,
                    'basic_salary'           => $basicSalary,
                    'borongan_pay'           => $boronganPay,
                    'position_allowance'     => $position,
                    'meal_allowance'         => $meal,
                    'transport_allowance'    => $transport,
                    'overtime_pay'           => $overtimePay,
                    'late_penalty'           => $latePenalty,
                    'cash_advance'           => $totalKasbon,
                    'bpjs_deduction'         => $bpjs,
                    'net_salary'             => $netSalary
                ]);

                if (!empty($kasbonIds)) {
                    $db->table('cash_advances')
                       ->whereIn('id', $kasbonIds)
                       ->update([
                           'payroll_id' => $payrollId,
                           'updated_at' => date('Y-m-d H:i:s')
                       ]);
                }

                $grandTotal += $netSalary;
            }

            $db->table('payrolls')->where('id', $payrollId)->update([
                'total_amount' => $grandTotal,
                'updated_at'   => date('Y-m-d H:i:s')
            ]);

            if ($db->transStatus() === false) {
                $dbError = $db->error(); 
                throw new \Exception("Database Error: " . ($dbError['message'] ?? 'Constraint MySQL gagal.'));
            }

            $db->transCommit();

            return redirect()->to('/payroll/detail/' . $payrollId)->with('success', 'Payroll berhasil diproses. Silakan Otorisasi Pembayaran untuk memotong Saldo Kas.');
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
                      ->select('payroll_details.*, employees.name, employees.department, employees.bank_name, employees.bank_account, employees.payment_method, employees.meal_allowance as master_meal, employees.transport_allowance as master_transport, employees.basic_salary as master_basic, employees.overtime_rate as master_overtime')
                      ->join('employees', 'employees.employee_id = payroll_details.employee_id')
                      ->where('payroll_id', $id)
                      ->get()->getResultArray();

        return view('payroll/detail', [
            'title'   => 'Rincian Penggajian',
            'payroll' => $payroll,
            'details' => $details
        ]);
    }

    // =========================================================
    // PUSH TO FINANCE (OTORISASI & POTONG SALDO)
    // =========================================================
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
        
        if (!$payroll || $payroll['status'] !== 'Draft') {
            return redirect()->back()->with('error', 'Dokumen tidak valid atau dana sudah dicairkan sebelumnya.');
        }

        // Cek Ketersediaan Akun Buku Besar
        $akunKas = $db->table('chart_of_accounts')->where('account_code', '1-1000')->get()->getRowArray();
        $akunBank = $db->table('chart_of_accounts')->where('account_code', '1-2000')->get()->getRowArray();
        $akunBeban = $db->table('chart_of_accounts')->where('account_code', '5-2000')->get()->getRowArray();

        if (!$akunKas || !$akunBank || !$akunBeban) {
            return redirect()->back()->with('error', 'Gagal: Akun Kas (1-1000), Bank (1-2000), atau Beban Gaji (5-2000) tidak ditemukan di sistem Akuntansi.');
        }

        // Cek Saldo untuk memastikan uangnya cukup
        $saldoKas = $db->query("SELECT calculated_balance FROM v_account_balances WHERE id = ?", [$akunKas['id']])->getRowArray()['calculated_balance'] ?? 0;
        $saldoBank = $db->query("SELECT calculated_balance FROM v_account_balances WHERE id = ?", [$akunBank['id']])->getRowArray()['calculated_balance'] ?? 0;

        if ($totalCash > 0 && $saldoKas < $totalCash) return redirect()->back()->with('error', 'Pencairan Ditolak: Saldo Laci Tunai tidak mencukupi.');
        if ($totalTransfer > 0 && $saldoBank < $totalTransfer) return redirect()->back()->with('error', 'Pencairan Ditolak: Saldo Rekening Bank tidak mencukupi.');

        $db->transBegin();
        try {
            // 1. Kunci Dokumen Payroll
            $db->table('payrolls')->where('id', $payrollId)->update([
                'status'     => 'Paid',
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            $db->table('cash_advances')->where('payroll_id', $payrollId)->update([
                'status'     => 'Lunas',
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            // 2. Buat Jurnal Akuntansi
            $db->table('journals')->insert([
                'journal_number'   => 'JRN-PAY-' . time(),
                'transaction_date' => date('Y-m-d'),
                'description'      => "Pencairan Gaji: {$payrollCode} ({$payroll['employee_status']})",
                'total_amount'     => $totalCash + $totalTransfer,
                'status'           => 'POSTED',
                'created_by'       => $picName
            ]);
            $jrnId = $db->insertID();

            // Debit Beban Gaji Keseluruhan
            $db->table('journal_items')->insert(['journal_id' => $jrnId, 'account_id' => $akunBeban['id'], 'line_description' => 'Beban Gaji Karyawan', 'debit' => $totalCash + $totalTransfer, 'credit' => 0]);

            $dateCode = date('Ymd');
            
            // 3. Potong Kas Tunai
            if ($totalCash > 0) {
                $db->table('journal_items')->insert(['journal_id' => $jrnId, 'account_id' => $akunKas['id'], 'line_description' => 'Gaji via Tunai', 'debit' => 0, 'credit' => $totalCash]);
                
                $lastTrxCash = $db->table('operational_cash')->like('transaction_code', "TRX-$dateCode-", 'after')->orderBy('id', 'DESC')->get()->getRowArray();
                $newNumberCash = $lastTrxCash ? str_pad((int) substr($lastTrxCash['transaction_code'], -3) + 1, 3, '0', STR_PAD_LEFT) : '001';
                
                $db->table('operational_cash')->insert([
                    'transaction_code' => "TRX-$dateCode-$newNumberCash", 'transaction_date' => date('Y-m-d'),
                    'type' => 'Cash Out', 'metode' => 'Cash', 'category' => 'Payroll Gaji',
                    'amount' => $totalCash, 'description' => "Pencairan Gaji (Tunai): {$payrollCode}",
                    'pic_name' => $picName, 'journal_id' => $jrnId, 'status' => 'POSTED'
                ]);
            }

            // 4. Potong Rekening Bank
            if ($totalTransfer > 0) {
                $db->table('journal_items')->insert(['journal_id' => $jrnId, 'account_id' => $akunBank['id'], 'line_description' => 'Gaji via Transfer', 'debit' => 0, 'credit' => $totalTransfer]);
                
                $lastTrxBank = $db->table('operational_cash')->like('transaction_code', "TRX-$dateCode-", 'after')->orderBy('id', 'DESC')->get()->getRowArray();
                $newNumberBank = $lastTrxBank ? str_pad((int) substr($lastTrxBank['transaction_code'], -3) + 1, 3, '0', STR_PAD_LEFT) : '001';
                
                $db->table('operational_cash')->insert([
                    'transaction_code' => "TRX-$dateCode-$newNumberBank", 'transaction_date' => date('Y-m-d'),
                    'type' => 'Cash Out', 'metode' => 'ATM', 'category' => 'Payroll Gaji',
                    'amount' => $totalTransfer, 'description' => "Pencairan Gaji (Transfer ATM): {$payrollCode}",
                    'pic_name' => $picName, 'journal_id' => $jrnId, 'status' => 'POSTED'
                ]);
            }

            if ($db->transStatus() === false) {
                $dbError = $db->error();
                throw new \Exception('Database Error saat otorisasi: ' . ($dbError['message'] ?? 'Gagal memotong Kas/Jurnal.'));
            }

            $db->transCommit();
            
            $msg = "Pencairan berhasil dan telah dipotong dari Kas! Dokumen <b>#{$payrollCode}</b> terkunci.<br>";
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
                   ->select('payroll_details.*, payrolls.period_start, payrolls.period_end, payrolls.payroll_code, payrolls.employee_status, employees.name, employees.position, employees.department, employees.salary_type, employees.payment_method, employees.employee_id, employees.meal_allowance as master_meal, employees.transport_allowance as master_transport, employees.basic_salary as master_basic, employees.overtime_rate as master_overtime')
                   ->join('payrolls', 'payrolls.id = payroll_details.payroll_id')
                   ->join('employees', 'employees.employee_id = payroll_details.employee_id')
                   ->where('payroll_details.id', $detail_id)
                   ->get()->getRowArray();

        if (!$slip) return redirect()->back()->with('error', 'Slip Gaji tidak ditemukan.');

        $companyModel = new \App\Models\CompanyModel();
        $company = $companyModel->first();

        return view('payroll/payslip', ['slip' => $slip, 'company' => $company]);
    }

    public function delete($id)
    {
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'admin') return redirect()->to('/portal');

        $db = \Config\Database::connect();
        $payroll = $db->table('payrolls')->where('id', $id)->get()->getRowArray();
        
        if (!$payroll) return redirect()->back()->with('error', 'Dokumen payroll tidak ditemukan.');

        if ($payroll['status'] !== 'Draft') {
            return redirect()->back()->with('error', 'Dokumen yang sudah dicairkan ke Keuangan tidak dapat dihapus demi integritas akuntansi!');
        }

        $db->transBegin();

        try {
            $db->table('cash_advances')
               ->where('payroll_id', $id)
               ->update([
                   'status'     => 'Belum Lunas',
                   'payroll_id' => null,
                   'updated_at' => date('Y-m-d H:i:s')
               ]);

            $db->table('payroll_details')->where('payroll_id', $id)->delete();
            $db->table('payrolls')->where('id', $id)->delete();

            if ($db->transStatus() === false) {
                $dbError = $db->error();
                throw new \Exception('Database Error saat hapus: ' . ($dbError['message'] ?? 'Gagal hapus'));
            }

            $db->transCommit();
            return redirect()->to('/payroll')->with('success', 'Dokumen payroll DRAFT berhasil dibatalkan dan dihapus.');
        } catch (\Throwable $e) {
            $db->transRollback();
            return redirect()->back()->with('error', 'Error Sistem: ' . $e->getMessage());
        }
    }
}