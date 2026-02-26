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
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'admin') return redirect()->to('/portal');

        $payrollModel = new PayrollModel();
        
        $data = [
            'title'    => 'Kelola Penggajian | Noric Workspace',
            'payrolls' => $payrollModel->orderBy('created_at', 'DESC')->findAll()
        ];

        return view('payroll/index', $data);
    }

    // --- MESIN UTAMA PENGHITUNG GAJI (GENERATE) ---
    public function generate()
    {
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'admin') return redirect()->to('/portal');

        $db = \Config\Database::connect();
        $payrollModel       = new PayrollModel();
        $detailModel        = new PayrollDetailModel();
        $empModel           = new EmployeeModel();
        $attModel           = new AttendanceModel();
        $shiftModel         = new WorkShiftModel();

        $salaryType = $this->request->getPost('salary_type');
        $periodStart = $this->request->getPost('period_start');
        $periodEnd   = $this->request->getPost('period_end');

        // Cari karyawan yang aktif dan sesuai tipe gaji (Harian/Mingguan/Bulanan)
        $employees = $empModel->where('is_active', 1)->where('salary_type', $salaryType)->findAll();

        if (empty($employees)) {
            return redirect()->back()->with('error', "Tidak ada karyawan aktif dengan tipe gaji {$salaryType}.");
        }

        $db->transStart();
        try {
            // 1. Buat Header Payroll
            $payrollCode = 'PAY-' . date('Ymd') . '-' . strtoupper(substr($salaryType, 0, 1));
            $payrollId = $payrollModel->insert([
                'payroll_code'    => $payrollCode,
                'salary_type'     => $salaryType,
                'period_start'    => $periodStart,
                'period_end'      => $periodEnd,
                'status'          => 'Draft',
                'total_employees' => count($employees),
                'total_amount'    => 0 // Akan diupdate nanti
            ]);

            $grandTotal = 0;

            // 2. Looping Perhitungan Tiap Karyawan
            foreach ($employees as $emp) {
                // Ambil data shift untuk denda
                $shift = $shiftModel->find($emp['shift_id']);
                $penaltyRate = $shift ? $shift['late_penalty_rate'] : 0;

                // Ambil Absensi di rentang tanggal
                $attendances = $attModel->where('employee_id', $emp['employee_id'])
                                        ->where('date >=', $periodStart)
                                        ->where('date <=', $periodEnd)
                                        ->whereIn('status', ['Hadir', 'Terlambat'])
                                        ->findAll();

                $totalPresent = count($attendances);
                $totalLate    = 0;
                $totalOvertime= 0;

                foreach ($attendances as $att) {
                    $totalLate += $att['late_minutes'];
                    $totalOvertime += $att['overtime_minutes'];
                }

                // --- KALKULASI PEMASUKAN ---
                // Jika Harian, gaji pokok dikali jumlah hadir. Jika bukan, full rate.
                $basicSalary = ($salaryType === 'Harian') ? ($emp['basic_salary'] * $totalPresent) : $emp['basic_salary'];
                $position    = $emp['position_allowance'];
                $meal        = $emp['meal_allowance'] * $totalPresent; // Uang makan berdasarkan kehadiran
                $transport   = $emp['transport_allowance'] * $totalPresent;
                
                // Lembur (Tarif per jam. Total menit / 60)
                $overtimePay = $emp['overtime_rate'] * floor($totalOvertime / 60);

                $grossSalary = $basicSalary + $position + $meal + $transport + $overtimePay;

                // --- KALKULASI POTONGAN ---
                $latePenalty = $totalLate * $penaltyRate;
                $bpjs = 0;
                if ($emp['bpjs_kesehatan'] == 1) $bpjs += ($basicSalary * 0.01);
                if ($emp['bpjs_ketenagakerjaan'] == 1) $bpjs += ($basicSalary * 0.02);

                $totalDeduction = $latePenalty + $bpjs;

                // --- GAJI BERSIH ---
                $netSalary = $grossSalary - $totalDeduction;
                if ($netSalary < 0) $netSalary = 0; // Cegah gaji minus

                // Simpan Detail Slip
                $detailModel->insert([
                    'payroll_id'            => $payrollId,
                    'employee_id'           => $emp['employee_id'],
                    'total_present'         => $totalPresent,
                    'total_late_minutes'    => $totalLate,
                    'total_overtime_minutes'=> $totalOvertime,
                    'basic_salary'          => $basicSalary,
                    'position_allowance'    => $position,
                    'meal_allowance'        => $meal,
                    'transport_allowance'   => $transport,
                    'overtime_pay'          => $overtimePay,
                    'late_penalty'          => $latePenalty,
                    'bpjs_deduction'        => $bpjs,
                    'net_salary'            => $netSalary
                ]);

                $grandTotal += $netSalary;
            }

            // 3. Update Total di Header
            $payrollModel->update($payrollId, ['total_amount' => $grandTotal]);

            $db->transComplete();

            if ($db->transStatus() === false) {
                return redirect()->back()->with('error', 'Gagal memproses penggajian.');
            }

            return redirect()->to('/payroll/detail/' . $payrollId)->with('success', 'Payroll berhasil di-generate secara otomatis!');

        } catch (\Exception $e) {
            $db->transRollback();
            return redirect()->back()->with('error', 'Terjadi kesalahan sistem: ' . $e->getMessage());
        }
    }

    // --- LIHAT DETAIL KARYAWAN PER PERIODE ---
    public function detail($id)
    {
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'admin') return redirect()->to('/portal');

        $payrollModel = new PayrollModel();
        $db = \Config\Database::connect();

        $payroll = $payrollModel->find($id);
        if (!$payroll) return redirect()->to('/payroll');

        // Join untuk ambil nama karyawan
        $details = $db->table('payroll_details')
                      ->select('payroll_details.*, employees.name, employees.department, employees.bank_name, employees.bank_account')
                      ->join('employees', 'employees.employee_id = payroll_details.employee_id')
                      ->where('payroll_id', $id)
                      ->get()->getResultArray();

        $data = [
            'title'   => 'Detail Payroll | Noric Workspace',
            'payroll' => $payroll,
            'details' => $details
        ];

        return view('payroll/detail', $data);
    }

    // --- CETAK SLIP GAJI PDF/PRINT ---
    public function print_slip($detail_id)
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/portal');

        $db = \Config\Database::connect();
        
        $slip = $db->table('payroll_details')
                   ->select('payroll_details.*, payrolls.period_start, payrolls.period_end, payrolls.payroll_code, employees.name, employees.position, employees.department, employees.salary_type')
                   ->join('payrolls', 'payrolls.id = payroll_details.payroll_id')
                   ->join('employees', 'employees.employee_id = payroll_details.employee_id')
                   ->where('payroll_details.id', $detail_id)
                   ->get()->getRowArray();

        if (!$slip) return redirect()->back()->with('error', 'Slip Gaji tidak ditemukan.');

        $data = ['slip' => $slip];
        return view('payroll/payslip', $data);
    }
}