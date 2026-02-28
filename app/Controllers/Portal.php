<?php

namespace App\Controllers;

use App\Models\EmployeeModel;
use App\Models\AttendanceModel;
use App\Models\PayrollDetailModel;

class Portal extends BaseController
{
    // --- 1. DASHBOARD KARYAWAN ---
    public function index()
    {
        // Pastikan hanya karyawan yang bisa masuk
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'karyawan') {
            return redirect()->to('/login');
        }

        $empModel = new EmployeeModel();
        $attModel = new AttendanceModel();

        $employeeId = session()->get('employee_id');
        $employee = $empModel->where('employee_id', $employeeId)->first();

        // Ambil riwayat absen 5 hari terakhir
        $recentAttendances = $attModel->where('employee_id', $employeeId)
                                      ->orderBy('date', 'DESC')
                                      ->limit(5)
                                      ->findAll();

        // Hitung total telat bulan ini
        $currentMonth = date('m');
        $currentYear = date('Y');
        $monthlyAttendances = $attModel->where('employee_id', $employeeId)
                                       ->where('MONTH(date)', $currentMonth)
                                       ->where('YEAR(date)', $currentYear)
                                       ->findAll();
        
        $totalLateMinutes = 0;
        $totalPresent = 0;
        foreach ($monthlyAttendances as $att) {
            if (in_array($att['status'], ['Hadir', 'Terlambat'])) $totalPresent++;
            $totalLateMinutes += $att['late_minutes'];
        }

        $data = [
            'title'             => 'Portal Karyawan',
            'employee'          => $employee,
            'recentAttendances' => $recentAttendances,
            'totalPresent'      => $totalPresent,
            'totalLateMinutes'  => $totalLateMinutes
        ];

        return view('portal/index', $data);
    }

    // --- 2. HALAMAN DAFTAR SLIP GAJI ---
    public function slip_gaji()
    {
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'karyawan') return redirect()->to('/login');

        $db = \Config\Database::connect();
        $employeeId = session()->get('employee_id');

        // Tarik riwayat gaji khusus untuk NIK yang sedang login
        $mySlips = $db->table('payroll_details')
                      ->select('payroll_details.*, payrolls.payroll_code, payrolls.period_start, payrolls.period_end, payrolls.status')
                      ->join('payrolls', 'payrolls.id = payroll_details.payroll_id')
                      ->where('payroll_details.employee_id', $employeeId)
                      ->where('payrolls.status !=', 'Draft') // Karyawan tidak boleh lihat slip yang masih Draft HRD
                      ->orderBy('payrolls.period_start', 'DESC')
                      ->get()->getResultArray();

        $data = [
            'title'   => 'Slip Gaji Saya',
            'mySlips' => $mySlips
        ];

        return view('portal/slip_gaji', $data);
    }

    // --- 3. CETAK SLIP GAJI PRIBADI (PDF/PRINT) ---
    public function print_my_slip($detail_id)
    {
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'karyawan') return redirect()->to('/login');

        $db = \Config\Database::connect();
        $employeeId = session()->get('employee_id');
        
        // SECURITY CHECK: Pastikan ID Slip Gaji ini benar-benar milik karyawan yang sedang login
        $slip = $db->table('payroll_details')
                   ->select('payroll_details.*, payrolls.period_start, payrolls.period_end, payrolls.payroll_code, employees.name, employees.position, employees.department, employees.salary_type')
                   ->join('payrolls', 'payrolls.id = payroll_details.payroll_id')
                   ->join('employees', 'employees.employee_id = payroll_details.employee_id')
                   ->where('payroll_details.id', $detail_id)
                   ->where('payroll_details.employee_id', $employeeId) // KUNCI KEAMANAN
                   ->get()->getRowArray();

        if (!$slip) {
            // Jika dia mencoba meretas URL slip gaji orang lain
            return redirect()->to('/portal/slip_gaji')->with('error', 'Akses Ditolak! Dokumen tidak ditemukan atau bukan milik Anda.');
        }

        $data = ['slip' => $slip];
        
        // Kita gunakan view payslip yang sama dengan yang dipakai HRD
        return view('payroll/payslip', $data);
    }
}