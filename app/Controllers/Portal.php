<?php

namespace App\Controllers;

use App\Models\EmployeeModel;
use App\Models\AttendanceModel;
use App\Models\CompanyModel;

class Portal extends BaseController
{
    private function checkAuth()
    {
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'karyawan') {
            return redirect()->to('/login');
        }
        return null;
    }

    // --- 1. DASHBOARD KARYAWAN ---
    public function index()
    {
        if ($redirect = $this->checkAuth()) return $redirect;

        $empModel = new EmployeeModel();
        $attModel = new AttendanceModel();

        $employeeId = session()->get('employee_id');
        
        $employee = $empModel->select('employees.*, departments.name as department_name, positions.name as position_name')
                             ->join('departments', 'departments.id = employees.department_id', 'left')
                             ->join('positions', 'positions.id = employees.position_id', 'left')
                             ->where('employees.employee_id', $employeeId)
                             ->first();
                             
        if (is_object($employee)) $employee = (array) $employee;

        // Ambil riwayat absen 5 hari terakhir
        $recentAttendances = $attModel->where('employee_id', $employeeId)
                                      ->orderBy('date', 'DESC')
                                      ->limit(5)
                                      ->findAll();

        // Ambil data absen hari ini untuk Live Timer
        $today = date('Y-m-d');
        $todayAttendance = $attModel->where('employee_id', $employeeId)
                                    ->where('date', $today)
                                    ->first();

        // Hitung total telat & hadir bulan ini
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
            $totalLateMinutes += (int) $att['late_minutes'];
        }

        return view('portal/index', [
            'title'             => 'Portal Karyawan',
            'employee'          => $employee,
            'recentAttendances' => $recentAttendances,
            'todayAttendance'   => $todayAttendance,
            'totalPresent'      => $totalPresent,
            'totalLateMinutes'  => $totalLateMinutes
        ]);
    }

    // --- 2. HALAMAN DAFTAR SLIP GAJI ---
    public function slip_gaji()
    {
        if ($redirect = $this->checkAuth()) return $redirect;

        $db = \Config\Database::connect();
        $employeeId = session()->get('employee_id');

        try {
            $mySlips = $db->table('payroll_details')
                          ->select('payroll_details.*, payrolls.payroll_code, payrolls.period_start, payrolls.period_end, payrolls.status')
                          ->join('payrolls', 'payrolls.id = payroll_details.payroll_id')
                          ->where('payroll_details.employee_id', $employeeId)
                          ->where('payrolls.status !=', 'Draft') 
                          ->orderBy('payrolls.period_start', 'DESC')
                          ->get()->getResultArray();
                          
        } catch (\Exception $e) {
            $mySlips = [];
            session()->setFlashdata('error', 'Gagal memuat data penggajian.');
        }

        return view('portal/slip_gaji', [
            'title'   => 'Riwayat Slip Gaji',
            'mySlips' => $mySlips
        ]);
    }

    // --- 3. CETAK SLIP GAJI KARYAWAN (PROTECTED) ---
    public function print_my_slip($detail_id) 
    { 
        if ($redirect = $this->checkAuth()) return $redirect;

        $db = \Config\Database::connect();
        $employeeId = session()->get('employee_id');

        // PROTEKSI: Karyawan HANYA bisa menarik data slip gajinya sendiri
        $slip = $db->table('payroll_details')
            ->select('payroll_details.*, payrolls.period_start, payrolls.period_end, payrolls.payroll_code, payrolls.employee_status, employees.name, departments.name as department, positions.name as position, employees.salary_type, employees.payment_method, employees.employee_id, employees.meal_allowance as master_meal, employees.transport_allowance as master_transport, employees.basic_salary as master_basic, employees.overtime_rate as master_overtime')
            ->join('payrolls', 'payrolls.id = payroll_details.payroll_id')
            ->join('employees', 'employees.employee_id = payroll_details.employee_id')
            ->join('departments', 'departments.id = employees.department_id', 'left')
            ->join('positions', 'positions.id = employees.position_id', 'left')
            ->where('payroll_details.id', $detail_id)
            ->where('payroll_details.employee_id', $employeeId) // KUNCI KEAMANAN
            ->where('payrolls.status !=', 'Draft') // TIDAK BOLEH CETAK JIKA MASIH DRAFT
            ->get()->getRowArray();

        if (!$slip) {
            return redirect()->to('/portal/slip_gaji')->with('error', 'Akses Ditolak: Slip Gaji tidak ditemukan atau Anda tidak memiliki izin.');
        }

        $companyModel = new CompanyModel();
        return view('portal/print_my_slip', [
            'slip'    => $slip, 
            'company' => $companyModel->first()
        ]);
    }
}