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
            'title'    => 'Kelola Penggajian', // Hapus hardcode Noric
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

        $salaryType  = $this->request->getPost('salary_type');
        $periodStart = $this->request->getPost('period_start');
        $periodEnd   = $this->request->getPost('period_end');

        // SECURITY PATCH 1: Blokir Duplikasi Dokumen Penggajian
        $existingPayroll = $payrollModel->where([
            'salary_type'  => $salaryType,
            'period_start' => $periodStart,
            'period_end'   => $periodEnd
        ])->first();

        if ($existingPayroll) {
            return redirect()->back()->with('error', 'Dokumen penggajian untuk Tipe dan Periode Tanggal tersebut sudah pernah dibuat! Silakan hapus dokumen sebelumnya jika ingin mengkalkulasi ulang.');
        }

        // Cari karyawan yang aktif
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
                'total_amount'    => 0 
            ]);

            $grandTotal = 0;

            // 2. Looping Perhitungan
            foreach ($employees as $emp) {
                $shift = $shiftModel->find($emp['shift_id']);
                $penaltyRate = $shift ? $shift['late_penalty_rate'] : 0;

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

                // Kalkulasi
                $basicSalary = ($salaryType === 'Harian') ? ($emp['basic_salary'] * $totalPresent) : $emp['basic_salary'];
                $position    = $emp['position_allowance'];
                $meal        = $emp['meal_allowance'] * $totalPresent; 
                $transport   = $emp['transport_allowance'] * $totalPresent;
                $overtimePay = $emp['overtime_rate'] * floor($totalOvertime / 60);

                $grossSalary = $basicSalary + $position + $meal + $transport + $overtimePay;

                // Potongan
                $latePenalty = $totalLate * $penaltyRate;
                $bpjs = 0;
                if ($emp['bpjs_kesehatan'] == 1) $bpjs += ($basicSalary * 0.01);
                if ($emp['bpjs_ketenagakerjaan'] == 1) $bpjs += ($basicSalary * 0.02);

                $totalDeduction = $latePenalty + $bpjs;
                $netSalary = $grossSalary - $totalDeduction;
                if ($netSalary < 0) $netSalary = 0; 

                // Simpan Rincian
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

            // 3. Update Total Akhir
            $payrollModel->update($payrollId, ['total_amount' => $grandTotal]);

            $db->transComplete();

            if ($db->transStatus() === false) {
                return redirect()->back()->with('error', 'Gagal memproses penggajian.');
            }

            return redirect()->to('/payroll/detail/' . $payrollId)->with('success', 'Payroll berhasil di-kalkulasi secara otomatis!');

        } catch (\Exception $e) {
            $db->transRollback();
            return redirect()->back()->with('error', 'Terjadi kesalahan sistem: ' . $e->getMessage());
        }
    }

    // --- LIHAT DETAIL KARYAWAN ---
    public function detail($id)
    {
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'admin') return redirect()->to('/portal');

        $payrollModel = new PayrollModel();
        $db = \Config\Database::connect();

        $payroll = $payrollModel->find($id);
        if (!$payroll) return redirect()->to('/payroll');

        $details = $db->table('payroll_details')
                      ->select('payroll_details.*, employees.name, employees.department, employees.bank_name, employees.bank_account')
                      ->join('employees', 'employees.employee_id = payroll_details.employee_id')
                      ->where('payroll_id', $id)
                      ->get()->getResultArray();

        $data = [
            'title'   => 'Rincian Penggajian',
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

    // --- FITUR HAPUS DOKUMEN PAYROLL (JIKA SALAH TANGGAL) ---
    public function delete($id)
    {
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'admin') return redirect()->to('/portal');

        $payrollModel = new PayrollModel();
        $detailModel  = new PayrollDetailModel();

        // Hapus rincian gajinya dulu (Foreign Key)
        $detailModel->where('payroll_id', $id)->delete();
        // Baru hapus dokumen headernya
        $payrollModel->delete($id);

        return redirect()->to('/payroll')->with('success', 'Dokumen penggajian beserta seluruh rinciannya berhasil dihapus.');
    }

    // --- FITUR BARU: PENCAIRAN KE BUKU KAS (FINANCE) ---
    public function push_to_finance()
    {
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'admin') return redirect()->to('/portal');

        $db = \Config\Database::connect();
        $payrollModel = new PayrollModel();
        
        // Load model Finance kita
        $cashModel = new \App\Models\OperationalCashModel();

        $payroll_id  = $this->request->getPost('payroll_id');
        $metode      = $this->request->getPost('metode'); // Cash / ATM
        $total_dana  = $this->request->getPost('total_amount');
        $payrollCode = $this->request->getPost('payroll_code');
        $pic_name    = session()->get('name');

        $payroll = $payrollModel->find($payroll_id);
        
        // Keamanan: Cegah pencairan ganda
        if (!$payroll || $payroll['status'] === 'Paid (Dicairkan)') {
            return redirect()->back()->with('error', 'Dokumen ini tidak valid atau sudah pernah dicairkan sebelumnya!');
        }

        // Helper untuk Auto-Generate Kode TRX Keuangan
        $dateCode = date('Ymd');
        $lastTrx = $cashModel->like('transaction_code', "TRX-$dateCode-", 'after')->orderBy('id', 'DESC')->first();
        $newNumber = $lastTrx ? str_pad((int) substr($lastTrx['transaction_code'], -3) + 1, 3, '0', STR_PAD_LEFT) : '001';
        $trxCode = "TRX-$dateCode-$newNumber";

        $db->transStart();
        try {
            // 1. Potong saldo di Buku Kas Operasional
            $cashModel->insert([
                'transaction_code' => $trxCode,
                'transaction_date' => date('Y-m-d'),
                'type'             => 'Cash Out',
                'metode'           => $metode,
                'category'         => 'Pembayaran Gaji',
                'amount'           => $total_dana,
                'description'      => "PENCAIRAN GAJI KARYAWAN (Ref: $payrollCode)",
                'pic_name'         => $pic_name
            ]);

            // 2. Kunci dokumen Payroll menjadi 'Paid'
            $payrollModel->update($payroll_id, ['status' => 'Paid (Dicairkan)']);

            $db->transComplete();

            if ($db->transStatus() === false) {
                return redirect()->back()->with('error', 'Gagal memproses pengiriman dana ke buku kas.');
            }

            return redirect()->back()->with('success', 'Dana berhasil dicairkan! Saldo Buku Kas (Finance) telah otomatis terpotong.');

        } catch (\Exception $e) {
            $db->transRollback();
            return redirect()->back()->with('error', 'Sistem Error: ' . $e->getMessage());
        }
    }
}