<?php

namespace App\Controllers;
use App\Models\LeaveModel;
use App\Models\EmployeeModel;

class Leave extends BaseController
{
    // ==================================================
    // AREA KARYAWAN (ESS) - Mengajukan Cuti
    // ==================================================
    public function index()
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/login');

        $leaveModel = new LeaveModel();
        $empModel = new EmployeeModel();
        
        $employeeId = session()->get('employee_id');
        
        // Ambil data sisa cuti karyawan
        $employee = $empModel->where('employee_id', $employeeId)->first();
        // Ambil riwayat pengajuannya sendiri
        $myLeaves = $leaveModel->where('employee_id', $employeeId)->orderBy('created_at', 'DESC')->findAll();

        $data = [
            'title'    => 'Pengajuan Cuti & Izin',
            'employee' => $employee,
            'myLeaves' => $myLeaves
        ];

        return view('leave/index', $data);
    }

    public function store()
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/login');

        $leaveModel = new LeaveModel();
        
        // 1. Hitung Durasi (Selisih Hari)
        $start = strtotime($this->request->getPost('start_date'));
        $end = strtotime($this->request->getPost('end_date'));
        $duration = round(($end - $start) / (60 * 60 * 24)) + 1;

        // 2. Upload Lampiran Surat Sakit (Jika Ada)
        $fileName = null;
        $file = $this->request->getFile('attachment');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $fileName = $file->getRandomName();
            $file->move(FCPATH . 'uploads/leaves/', $fileName); // Simpan ke folder public/uploads/leaves/
        }

        $leaveModel->insert([
            'employee_id' => session()->get('employee_id'),
            'leave_type'  => $this->request->getPost('leave_type'),
            'start_date'  => $this->request->getPost('start_date'),
            'end_date'    => $this->request->getPost('end_date'),
            'duration'    => $duration,
            'reason'      => $this->request->getPost('reason'),
            'attachment'  => $fileName,
            'status'      => 'Pending'
        ]);

        return redirect()->to('/leave')->with('success', 'Pengajuan berhasil dikirim dan menunggu persetujuan HRD.');
    }

    // ==================================================
    // AREA ADMIN/HRD - Persetujuan Cuti
    // ==================================================
    public function approval()
    {
        // Hanya Admin / HRD yang boleh masuk
        if (!session()->get('isLoggedIn') || (session()->get('role') !== 'admin' && session()->get('department') !== 'Manajemen & HRD')) {
            return redirect()->to('/portal')->with('error', 'Akses ditolak.');
        }

        $leaveModel = new LeaveModel();
        $data = [
            'title'   => 'Approval Cuti & Izin',
            'leaves'  => $leaveModel->getAllWithEmployee()
        ];

        return view('leave/approval', $data);
    }

    public function process_action($id, $action)
    {
        $leaveModel = new LeaveModel();
        $empModel = new EmployeeModel();
        $db = \Config\Database::connect();

        $request = $leaveModel->find($id);
        if (!$request || $request['status'] !== 'Pending') return redirect()->back()->with('error', 'Data tidak valid.');

        $db->transStart();
        try {
            // 1. Update Status Pengajuan
            $status = ($action === 'approve') ? 'Approved' : 'Rejected';
            $leaveModel->update($id, [
                'status'      => $status,
                'reviewed_by' => session()->get('name')
            ]);

            // 2. POTONG SALDO CUTI JIKA DISETUJUI & JENISNYA CUTI TAHUNAN
            if ($action === 'approve' && $request['leave_type'] === 'Cuti Tahunan') {
                $emp = $empModel->where('employee_id', $request['employee_id'])->first();
                if ($emp) {
                    $newBalance = $emp['leave_balance'] - $request['duration'];
                    // Cegah saldo minus
                    $newBalance = ($newBalance < 0) ? 0 : $newBalance; 
                    $empModel->update($emp['id'], ['leave_balance' => $newBalance]);
                }
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                return redirect()->back()->with('error', 'Gagal memproses data.');
            }

            return redirect()->back()->with('success', 'Pengajuan berhasil di-' . strtolower($status) . '.');

        } catch (\Exception $e) {
            $db->transRollback();
            return redirect()->back()->with('error', 'Terjadi kesalahan sistem.');
        }
    }
}