<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\EmployeeModel;
use App\Models\DepartmentModel;
use App\Models\PositionModel;
use App\Models\UserModel;
use App\Models\WorkShiftModel;
use CodeIgniter\HTTP\RedirectResponse;

class Employee extends BaseController
{
    private $factorySpecialties = [
        'Bending',
        'Monel',
        'Las Cacing',
        'Las Cantum',
        'Poles / Amril',
        'Perakitan',
        'Quality Control',
        'Packing',
        'Gudang / Logistik'
    ];

    public function index()
    {
        if (!session()->get('isLoggedIn') || (session()->get('role') !== 'admin' && !str_contains(strtolower(session()->get('department_name') ?? session()->get('department')), 'hrd'))) {
            return redirect()->to('/portal');
        }

        $employeeModel = new EmployeeModel();
        $employees = $employeeModel->select('employees.*, departments.name as department_name, positions.name as position_name')
                                   ->join('departments', 'departments.id = employees.department_id', 'left')
                                   ->join('positions', 'positions.id = employees.position_id', 'left')
                                   ->orderBy('employees.employee_id', 'DESC')
                                   ->findAll();

        return view('employee/index', [
            'title'             => 'Manajemen Karyawan',
            'employees'         => $employees, 
            'activeEmployees'   => array_filter($employees, fn($e) => (int)($e['is_active'] ?? 1) === 1),
            'inactiveEmployees' => array_filter($employees, fn($e) => (int)($e['is_active'] ?? 1) === 0),
        ]);
    }

    public function create(): string|RedirectResponse
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/login');

        $employeeModel   = new EmployeeModel();
        $shiftModel      = new WorkShiftModel();
        $departmentModel = new DepartmentModel();
        $positionModel   = new PositionModel();

        return view('employee/create', [
            'title'       => 'Tambah Karyawan Baru',
            'shifts'      => $shiftModel->findAll(),
            'departments' => $departmentModel->where('status', 'active')->findAll(),
            'positions'   => $positionModel->where('status', 'active')->findAll(),
            'specialties' => $this->factorySpecialties,
            'leaders'     => $employeeModel->where('is_active', 1)->orderBy('name', 'ASC')->findAll(), 
            'autoNIK'     => $employeeModel->generateEmployeeId() 
        ]);
    }

    public function store(): RedirectResponse
    {
        $rules = [
            'department_id' => 'required|is_not_unique[departments.id]',
            'position_id'   => 'required|is_not_unique[positions.id]',
            'username'      => 'required|is_unique[users.username]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Validasi Gagal: Pastikan username unik dan departemen valid dipilih.');
        }

        $employeeModel = new EmployeeModel();
        $userModel     = new UserModel();
        $db            = \Config\Database::connect();

        $db->transStart();
        try {
            $salaryType = $this->request->getPost('salary_type');
            $status     = $this->request->getPost('status');
            $paymentMethod = $this->request->getPost('payment_method');
            $employeeId = $employeeModel->generateEmployeeId();
            $empName    = strtoupper(trim((string)$this->request->getPost('name')));

            $leaderId = $this->request->getPost('leader_id');
            if (empty($leaderId)) $leaderId = null;

            // KITA SIMPAN PIN DAN PRIVILEGE KE VARIABEL DULU AGAR BISA DIPAKAI KE FINGERSPOT
            $newPin = $employeeModel->generateNewPin();
            $machinePrivilege = $this->request->getPost('machine_privilege') ?? "0";

            $empData = [
                'pin'                     => $newPin,
                'employee_id'             => $employeeId,
                'department_id'           => $this->request->getPost('department_id'),
                'position_id'             => $this->request->getPost('position_id'),
                'specialty'               => $this->request->getPost('specialty'),
                'leader_id'               => $leaderId, 
                'shift_id'                => $this->request->getPost('shift_id'),
                'name'                    => $empName,
                'nik_ktp'                 => $this->request->getPost('nik_ktp'),
                'phone'                   => $this->request->getPost('phone'),
                'address'                 => $this->request->getPost('address'),
                'marital_status'          => $this->request->getPost('marital_status'),
                'emergency_contact_name'  => $this->request->getPost('emergency_contact_name'), 
                'emergency_contact_phone' => $this->request->getPost('emergency_contact_phone'), 
                'grade_level'             => $this->request->getPost('grade_level'), 
                'machine_privilege'       => $machinePrivilege,
                'status'                  => $status,
                'payment_method'          => $paymentMethod,
                'join_date'               => $this->request->getPost('join_date'),
                'is_active'               => $this->request->getPost('is_active') ?? 1,
                'bank_name'               => $paymentMethod === 'Transfer' ? $this->request->getPost('bank_name') : '',
                'bank_account'            => $paymentMethod === 'Transfer' ? $this->request->getPost('bank_account') : '',
                'salary_type'             => $salaryType,
                'basic_salary'            => str_replace(['Rp', '.', ' '], '', $this->request->getPost('basic_salary') ?? 0),
                'position_allowance'      => str_replace(['Rp', '.', ' '], '', $this->request->getPost('position_allowance') ?? 0),
                'meal_allowance'          => str_replace(['Rp', '.', ' '], '', $this->request->getPost('meal_allowance') ?? 0),
                'transport_allowance'     => str_replace(['Rp', '.', ' '], '', $this->request->getPost('transport_allowance') ?? 0),
                'overtime_rate'           => str_replace(['Rp', '.', ' '], '', $this->request->getPost('overtime_rate') ?? 0),
                'bpjs_kesehatan'          => $this->request->getPost('bpjs_kesehatan') ? 1 : 0,
                'bpjs_ks_number'          => $this->request->getPost('bpjs_ks_number'), 
                'bpjs_ketenagakerjaan'    => $this->request->getPost('bpjs_ketenagakerjaan') ? 1 : 0,
                'bpjs_tk_number'          => $this->request->getPost('bpjs_tk_number') 
            ];
            
            $employeeModel->insert($empData);

            $userModel->insert([
                'employee_id' => $employeeId, 
                'username'    => $this->request->getPost('username'),
                'password'    => password_hash((string)$this->request->getPost('password'), PASSWORD_DEFAULT),
                'name'        => $empName,
                'role'        => $this->request->getPost('role') ?? 'karyawan'
            ]);

            $db->transComplete();
            if ($db->transStatus() === false) throw new \Exception("Kegagalan penulisan database utama.");

            // =========================================================================
            // TAMBAHAN FULL FIX: KIRIM KE MESIN FINGERSPOT SECARA OTOMATIS
            // =========================================================================
            $fingerspot = new \App\Libraries\Fingerspot();
            // Parameter API: pin, nama, privilege mesin, password mesin (kosong), RFID (kosong)
            $apiResponse = $fingerspot->setUserInfo($newPin, $empName, $machinePrivilege, "", "");

            if (isset($apiResponse['success']) && $apiResponse['success']) {
                $msg = "Karyawan <b>{$empName}</b> berhasil didaftarkan dan <b>sukses terkirim otomatis</b> ke Mesin Absensi.";
            } else {
                $msg = "Karyawan <b>{$empName}</b> tersimpan di Web, namun <b>GAGAL</b> dikirim ke mesin. Mesin mungkin sedang offline. Gunakan tombol 'Push IoT' di tabel nanti.";
            }
            // =========================================================================

            return redirect()->to('/employee')->with('success', $msg);

        } catch (\Exception $e) {
            $db->transRollback();
            return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan sistem: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/portal');

        $employeeModel   = new EmployeeModel();
        $departmentModel = new DepartmentModel();
        $positionModel   = new PositionModel();
        $shiftModel      = new WorkShiftModel();
        $db              = \Config\Database::connect();

        $employee = $employeeModel->find($id);
        if (!$employee) return redirect()->to('/employee')->with('error', 'Data karyawan tidak ditemukan.');

        $user = $db->table('users')->where('employee_id', $employee['employee_id'])->get()->getRowArray();
        
        return view('employee/edit', [
            'title'       => 'Edit Karyawan',
            'employee'    => $employee,
            'user'        => $user,
            'shifts'      => $shiftModel->findAll(),
            'departments' => $departmentModel->where('status', 'active')->findAll(),
            'positions'   => $positionModel->where('status', 'active')->findAll(),
            'specialties' => $this->factorySpecialties,
            'leaders'     => $employeeModel->where('is_active', 1)->where('employee_id !=', $employee['employee_id'])->orderBy('name', 'ASC')->findAll(), 
        ]);
    }

    public function update($id)
    {
        $employeeModel = new EmployeeModel();
        $userModel     = new UserModel();
        $db            = \Config\Database::connect();

        $employee = $employeeModel->find($id);
        if (!$employee) return redirect()->to('/employee');

        $db->transStart();
        try {
            $salaryType    = $this->request->getPost('salary_type');
            $status        = $this->request->getPost('status');
            $paymentMethod = $this->request->getPost('payment_method');
            $newName       = strtoupper(trim($this->request->getPost('name')));
            
            $leaderId = $this->request->getPost('leader_id');
            if (empty($leaderId)) $leaderId = null;

            // Catat privilege lama untuk pengecekan perubahan
            $oldPrivilege = $employee['machine_privilege'] ?? "0";
            $newPrivilege = $this->request->getPost('machine_privilege');

            $empData = [
                'department_id'           => $this->request->getPost('department_id'), 
                'position_id'             => $this->request->getPost('position_id'),   
                'specialty'               => $this->request->getPost('specialty'),
                'leader_id'               => $leaderId,
                'shift_id'                => $this->request->getPost('shift_id'),
                'name'                    => $newName,
                'nik_ktp'                 => $this->request->getPost('nik_ktp'),
                'phone'                   => $this->request->getPost('phone'),
                'address'                 => $this->request->getPost('address'),
                'marital_status'          => $this->request->getPost('marital_status'),
                'emergency_contact_name'  => $this->request->getPost('emergency_contact_name'), 
                'emergency_contact_phone' => $this->request->getPost('emergency_contact_phone'), 
                'grade_level'             => $this->request->getPost('grade_level'), 
                'machine_privilege'       => $newPrivilege,
                'status'                  => $status,
                'payment_method'          => $paymentMethod,
                'join_date'               => $this->request->getPost('join_date'),
                'is_active'               => $this->request->getPost('is_active'),
                'resign_date'             => $this->request->getPost('resign_date'),
                'bank_name'               => $paymentMethod == 'Transfer' ? $this->request->getPost('bank_name') : '',
                'bank_account'            => $paymentMethod == 'Transfer' ? $this->request->getPost('bank_account') : '',
                'salary_type'             => $salaryType,
                'basic_salary'            => str_replace(['Rp', '.', ' '], '', $this->request->getPost('basic_salary') ?? 0),
                'position_allowance'      => str_replace(['Rp', '.', ' '], '', $this->request->getPost('position_allowance') ?? 0),
                'meal_allowance'          => str_replace(['Rp', '.', ' '], '', $this->request->getPost('meal_allowance') ?? 0),
                'transport_allowance'     => str_replace(['Rp', '.', ' '], '', $this->request->getPost('transport_allowance') ?? 0),
                'overtime_rate'           => str_replace(['Rp', '.', ' '], '', $this->request->getPost('overtime_rate') ?? 0),
                'bpjs_kesehatan'          => $this->request->getPost('bpjs_kesehatan') ? 1 : 0,
                'bpjs_ks_number'          => $this->request->getPost('bpjs_ks_number'), 
                'bpjs_ketenagakerjaan'    => $this->request->getPost('bpjs_ketenagakerjaan') ? 1 : 0,
                'bpjs_tk_number'          => $this->request->getPost('bpjs_tk_number')
            ];
            
            $employeeModel->update($id, $empData);

            $userAccount   = $userModel->where('employee_id', $employee['employee_id'])->first();
            $inputUsername = trim($this->request->getPost('username'));
            $inputPassword = trim($this->request->getPost('password'));
            $inputRole     = $this->request->getPost('role') ?? 'karyawan';

            if ($inputUsername) {
                $cekDuplikat = $userModel->where('username', $inputUsername)->where('employee_id !=', $employee['employee_id'])->first();
                if ($cekDuplikat) throw new \Exception("Username '{$inputUsername}' sudah digunakan.");
            }

            if ($userAccount) {
                $userDataToUpdate = ['name' => $newName, 'username' => $inputUsername, 'role' => $inputRole];
                if (!empty($inputPassword)) $userDataToUpdate['password'] = password_hash($inputPassword, PASSWORD_DEFAULT);
                $userModel->update($userAccount['id'], $userDataToUpdate);
            } else {
                if (!empty($inputUsername)) {
                    $newUserData = ['employee_id' => $employee['employee_id'], 'name' => $newName, 'username' => $inputUsername, 'role' => $inputRole];
                    $newUserData['password'] = !empty($inputPassword) ? password_hash($inputPassword, PASSWORD_DEFAULT) : password_hash('123456', PASSWORD_DEFAULT); 
                    $userModel->insert($newUserData);
                }
            }

            $db->transComplete();
            if ($db->transStatus() === false) throw new \Exception("Gagal menyimpan update ke database.");

            // =========================================================================
            // Opsi: Sync update nama/privilege ke mesin juga jika ada perubahan
            // =========================================================================
            $msg = "Data karyawan dan Akun Login <b>{$newName}</b> berhasil diperbarui.";
            if ($employee['name'] !== $newName || $oldPrivilege !== $newPrivilege) {
                $fingerspot = new \App\Libraries\Fingerspot();
                $apiResponse = $fingerspot->setUserInfo($employee['pin'], $newName, $newPrivilege, "", $employee['rfid']);
                if (isset($apiResponse['success']) && $apiResponse['success']) {
                    $msg .= " Perubahan nama/hak akses juga telah <b>di-update ke mesin IoT</b>.";
                }
            }

            return redirect()->to('/employee')->with('success', $msg);
        } catch (\Exception $e) {
            $db->transRollback();
            return redirect()->back()->withInput()->with('error', 'Peringatan: ' . $e->getMessage());
        }
    }

    public function delete($id)
    {
        $employeeModel = new EmployeeModel();
        $userModel = new \App\Models\UserModel();
        $db = \Config\Database::connect();

        $employee = $employeeModel->find($id);
        if (!$employee) return redirect()->to('/employee')->with('error', 'Data karyawan tidak ditemukan.');

        $cekGaji = $db->table('payroll_details')
                      ->where('employee_id', $employee['employee_id'])
                      ->countAllResults();
        
        if ($cekGaji > 0) {
            return redirect()->to('/employee')->with('error', '<b>AKSES DITOLAK: Risiko Integritas Laporan!</b><br>Karyawan ini sudah memiliki riwayat slip gaji. Menghapus data ini akan merusak laporan kas operasional. Silakan gunakan tombol <b>Proses Resign</b> untuk menonaktifkan.');
        }

        $db->transStart();
        try {
            if ($employee['pin']) {
                $fingerspot = new \App\Libraries\Fingerspot();
                $fingerspot->deleteUserInfo($employee['pin']); 
            }

            $db->table('employees')->where('leader_id', $employee['employee_id'])->update(['leader_id' => null]);
            $userModel->where('employee_id', $employee['employee_id'])->delete();
            $employeeModel->delete($id);

            if ($db->transStatus() === false) {
                throw new \Exception("Gagal menghapus dari database.");
            }
            
            $db->transCommit();
            return redirect()->to('/employee')->with('success', 'Data karyawan dihapus permanen beserta koneksi IoT-nya.');

        } catch (\Exception $e) {
            $db->transRollback();
            return redirect()->to('/employee')->with('error', 'Terjadi kesalahan sistem saat menghapus data.');
        }
    }

    public function deactivate($id)
    {
        $employeeModel = new EmployeeModel();
        $userModel = new \App\Models\UserModel();
        $db = \Config\Database::connect();

        $employee = $employeeModel->find($id);
        if (!$employee) return redirect()->to('/employee')->with('error', 'Data karyawan tidak ditemukan.');

        $db->transStart();
        try {
            if ($employee['pin']) {
                $fingerspot = new \App\Libraries\Fingerspot();
                $fingerspot->deleteUserInfo($employee['pin']); 
            }

            $empData = [
                'is_active'   => 0,
                'resign_date' => date('Y-m-d'),
                'leader_id'   => null 
            ];
            $employeeModel->update($id, $empData);

            $db->table('employees')->where('leader_id', $employee['employee_id'])->update(['leader_id' => null]);
            $userModel->where('employee_id', $employee['employee_id'])->delete();

            if ($db->transStatus() === false) {
                throw new \Exception("Gagal memproses resign karyawan.");
            }
            
            $db->transCommit();
            return redirect()->to('/employee')->with('success', "Karyawan <b>{$employee['name']}</b> berhasil diproses resign dan dinonaktifkan.");

        } catch (\Exception $e) {
            $db->transRollback();
            return redirect()->to('/employee')->with('error', 'Terjadi kesalahan sistem memproses dektivasi.');
        }
    }

    // ==========================================
    // FUNGSI IOT FINGERSPOT 
    // ==========================================
    
    public function sync_biometric($pin)
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/portal');

        $fingerspot = new \App\Libraries\Fingerspot();
        $response = $fingerspot->getUserInfo($pin);

        if (isset($response['success']) && $response['success']) {
            return redirect()->back()->with('success', "Perintah tarik data biometrik untuk PIN Mesin: {$pin} terkirim. Refresh halaman ini dalam beberapa detik.");
        } else {
            return redirect()->back()->with('error', 'Gagal terhubung ke Cloud Fingerspot. Pastikan mesin IoT dalam keadaan online.');
        }
    }

    public function push_to_machine($id)
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/portal');

        $empModel = new \App\Models\EmployeeModel();
        $employee = $empModel->find($id);

        if (!$employee) return redirect()->back()->with('error', 'Data karyawan tidak ditemukan.');

        $fingerspot = new \App\Libraries\Fingerspot();
        $privilege = $employee['machine_privilege'] ?? "1"; 
        
        $response = $fingerspot->setUserInfo($employee['pin'], $employee['name'], $privilege, "", $employee['rfid']);

        if (isset($response['success']) && $response['success']) {
            $roleName = ($privilege == "2") ? "ADMIN MESIN" : "USER BIASA";
            return redirect()->back()->with('success', "Perintah pendaftaran <b>{$employee['name']}</b> terkirim ke mesin sebagai <b>{$roleName}</b>.");
        } else {
            return redirect()->back()->with('error', 'Gagal mengirim perintah ke mesin IoT. Pastikan mesin terhubung ke internet.');
        }
    }

    public function pull_from_machine()
    {
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'admin') return redirect()->to('/portal');

        $fingerspot = new \App\Libraries\Fingerspot();
        $response = $fingerspot->getAllPin();

        if (isset($response['success']) && $response['success'] === true) {
            return redirect()->back()->with('success', "<b>Perintah Sinkronisasi Terkirim!</b><br>Mesin sedang mengumpulkan data. Silakan <b>Refresh (F5)</b> dalam 10 detik.");
        }

        $apiError = "Tidak ada kejelasan dari server.";
        if (isset($response['message'])) $apiError = $response['message'];
        elseif (isset($response['msg'])) $apiError = $response['msg'];

        $debugInfo = is_array($response) ? json_encode($response) : $response;
        return redirect()->back()->with('error', "<b>GAGAL MENGIRIM PERINTAH:</b><br>{$apiError}<br><br><span style='font-size:11px; color:#ef4444; word-break: break-all;'>Debug Data: {$debugInfo}</span>");
    }
}