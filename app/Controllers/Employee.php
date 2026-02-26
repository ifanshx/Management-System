<?php

namespace App\Controllers;
use App\Models\EmployeeModel;

class Employee extends BaseController
{
    public function index()
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/login');

        $employeeModel = new EmployeeModel();
        
        $data = [
            'title'     => 'Manajemen Karyawan | Noric Workspace',
            'employees' => $employeeModel->orderBy('employee_id', 'DESC')->findAll()
        ];

        return view('employee/index', $data);
    }

    public function create()
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/login');

        $shiftModel = new \App\Models\WorkShiftModel(); 

        $data = [
            'title'  => 'Tambah Karyawan | Noric Workspace',
            'shifts' => $shiftModel->findAll() 
        ];

        return view('employee/create', $data);
    }

    public function store()
    {
        $employeeModel = new EmployeeModel();
        $userModel = new \App\Models\UserModel();
        $db = \Config\Database::connect();

        $inputUsername = $this->request->getPost('username');
        $inputPassword = $this->request->getPost('password');
        $empName       = $this->request->getPost('name');

        if ($userModel->where('username', $inputUsername)->first()) {
            return redirect()->back()->withInput()->with('error', 'Username tersebut sudah digunakan! Silakan pilih username lain.');
        }

        $db->transStart();

        try {
           // --- 1. AUTO-GENERATE NIK (WEB) & PIN (MESIN) ---
            $year = date('Y');
            $lastEmployee = $employeeModel->like('employee_id', "NRC-$year-", 'after')
                                          ->orderBy('employee_id', 'DESC')
                                          ->first();
                                          
            if ($lastEmployee) {
                $lastNumber = (int) substr($lastEmployee['employee_id'], -3);
                $newNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
            } else {
                $newNumber = '001';
            }
            $employeeId = "NRC-$year-$newNumber";

            $lastPinUser = $employeeModel->orderBy('pin', 'DESC')->first();
            $newMachinePin = $lastPinUser && $lastPinUser['pin'] ? $lastPinUser['pin'] + 1 : 1;

            // --- 2. SIMPAN BIODATA KARYAWAN ---
           $empData = [
                'pin'                  => $newMachinePin,
                'employee_id'          => $employeeId,    
                'shift_id'             => $this->request->getPost('shift_id'),
                'name'                 => $this->request->getPost('name'),
                'phone'                => $this->request->getPost('phone'),
                'address'              => $this->request->getPost('address'),
                'marital_status'       => $this->request->getPost('marital_status'),
                'department'           => $this->request->getPost('department'),
                'position'             => $this->request->getPost('position'),
                'machine_privilege'    => $this->request->getPost('machine_privilege'),
                'status'               => $this->request->getPost('status'),
                'join_date'            => $this->request->getPost('join_date'),
                
                // --- PENGGAJIAN & TUNJANGAN MULTI-TIPE ---
                'bank_name'            => $this->request->getPost('bank_name'),
                'bank_account'         => $this->request->getPost('bank_account'),
                'salary_type'          => $this->request->getPost('salary_type'), 
                'basic_salary'         => str_replace(['Rp', '.', ' '], '', $this->request->getPost('basic_salary')),
                'position_allowance'   => str_replace(['Rp', '.', ' '], '', $this->request->getPost('position_allowance')),
                'meal_allowance'       => str_replace(['Rp', '.', ' '], '', $this->request->getPost('meal_allowance')),
                'transport_allowance'  => str_replace(['Rp', '.', ' '], '', $this->request->getPost('transport_allowance')),
                'overtime_rate'        => str_replace(['Rp', '.', ' '], '', $this->request->getPost('overtime_rate')),
                'late_penalty_rate'    => str_replace(['Rp', '.', ' '], '', $this->request->getPost('late_penalty_rate')),
                'bpjs_kesehatan'       => $this->request->getPost('bpjs_kesehatan') ? 1 : 0,
                'bpjs_ketenagakerjaan' => $this->request->getPost('bpjs_ketenagakerjaan') ? 1 : 0
            ];
            $employeeModel->insert($empData);

            // --- 3. BUAT AKUN LOGIN SESUAI INPUT HRD ---
            $userData = [
                'employee_id' => $employeeId, 
                'username'    => $inputUsername,
                'password'    => password_hash($inputPassword, PASSWORD_DEFAULT),
                'name'        => $empName,
                'role'        => 'karyawan'
            ];
            $userModel->insert($userData);

            $db->transComplete();

            if ($db->transStatus() === false) {
                return redirect()->back()->withInput()->with('error', 'Gagal menyimpan data ke database. Silakan coba lagi.');
            }

            $successMsg = "Karyawan <b>{$empName}</b> berhasil didaftarkan.<br><br><b>Akses Portal:</b><br>Username: <code>{$inputUsername}</code><br>Password: <code>{$inputPassword}</code>";
            return redirect()->to('/employee')->with('success', $successMsg);

        } catch (\Exception $e) {
            $db->transRollback();
            return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan sistem: ' . $e->getMessage());
        }
    }

   public function edit($id)
    {
        $employeeModel = new EmployeeModel();
        $shiftModel = new \App\Models\WorkShiftModel();

        $employee = $employeeModel->find($id);
        if (!$employee) return redirect()->to('/employee');

        $data = [
            'title'    => 'Edit Karyawan | Noric Workspace',
            'employee' => $employee,
            'shifts'   => $shiftModel->findAll() 
        ];

        return view('employee/edit', $data);
    }

    public function update($id)
    {
        $employeeModel = new EmployeeModel();
        $userModel = new \App\Models\UserModel();
        $db = \Config\Database::connect();

        $employee = $employeeModel->find($id);
        if (!$employee) return redirect()->to('/employee');

        $db->transStart();
        try {
            $newName = $this->request->getPost('name');
            
           $empData = [
                'shift_id'             => $this->request->getPost('shift_id'),
                'name'                 => $newName,
                'phone'                => $this->request->getPost('phone'),
                'address'              => $this->request->getPost('address'),
                'marital_status'       => $this->request->getPost('marital_status'),
                'department'           => $this->request->getPost('department'),
                'position'             => $this->request->getPost('position'),
                'machine_privilege'    => $this->request->getPost('machine_privilege'),
                'status'               => $this->request->getPost('status'),
                'join_date'            => $this->request->getPost('join_date'),
                
                // --- PENGGAJIAN & TUNJANGAN MULTI-TIPE ---
                'bank_name'            => $this->request->getPost('bank_name'),
                'bank_account'         => $this->request->getPost('bank_account'),
                'salary_type'          => $this->request->getPost('salary_type'), 
                'basic_salary'         => str_replace(['Rp', '.', ' '], '', $this->request->getPost('basic_salary')),
                'position_allowance'   => str_replace(['Rp', '.', ' '], '', $this->request->getPost('position_allowance')),
                'meal_allowance'       => str_replace(['Rp', '.', ' '], '', $this->request->getPost('meal_allowance')),
                'transport_allowance'  => str_replace(['Rp', '.', ' '], '', $this->request->getPost('transport_allowance')),
                'overtime_rate'        => str_replace(['Rp', '.', ' '], '', $this->request->getPost('overtime_rate')),
                'bpjs_kesehatan'       => $this->request->getPost('bpjs_kesehatan') ? 1 : 0,
                'bpjs_ketenagakerjaan' => $this->request->getPost('bpjs_ketenagakerjaan') ? 1 : 0
            ];
            $employeeModel->update($id, $empData);

            $userAccount = $userModel->where('username', $employee['employee_id'])->first();
            if ($userAccount) {
                $userModel->update($userAccount['id'], ['name' => $newName]);
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                return redirect()->back()->withInput()->with('error', 'Gagal memperbarui data.');
            }

            return redirect()->to('/employee')->with('success', "Data karyawan <b>{$newName}</b> berhasil diperbarui.");

        } catch (\Exception $e) {
            $db->transRollback();
            return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function delete($id)
    {
        $employeeModel = new EmployeeModel();
        $userModel = new \App\Models\UserModel();
        $db = \Config\Database::connect();

        $employee = $employeeModel->find($id);
        if (!$employee) return redirect()->to('/employee')->with('error', 'Data tidak ditemukan.');

        $db->transStart();
        try {
            if ($employee['pin']) {
                $fingerspot = new \App\Libraries\Fingerspot();
                $fingerspot->deleteUserInfo($employee['pin']); 
            }

            $userModel->where('username', $employee['employee_id'])->delete();
            $employeeModel->delete($id);

            $db->transComplete();

            if ($db->transStatus() === false) {
                return redirect()->to('/employee')->with('error', 'Gagal menghapus data.');
            }

            return redirect()->to('/employee')->with('success', 'Data karyawan dihapus, akses ESS dicabut, dan perintah penghapusan biometrik mesin telah dikirim.');

        } catch (\Exception $e) {
            $db->transRollback();
            return redirect()->to('/employee')->with('error', 'Terjadi kesalahan sistem.');
        }
    }

    public function deactivate($id)
    {
        $employeeModel = new EmployeeModel();
        $userModel = new \App\Models\UserModel();
        $db = \Config\Database::connect();

        $employee = $employeeModel->find($id);
        if (!$employee) return redirect()->to('/employee')->with('error', 'Data tidak ditemukan.');

        $db->transStart();
        try {
            if ($employee['pin']) {
                $fingerspot = new \App\Libraries\Fingerspot();
                $fingerspot->deleteUserInfo($employee['pin']); 
            }

            $empData = [
                'is_active'   => 0,
                'resign_date' => date('Y-m-d')
            ];
            $employeeModel->update($id, $empData);

            $userModel->where('username', $employee['employee_id'])->delete();

            $db->transComplete();

            if ($db->transStatus() === false) {
                return redirect()->to('/employee')->with('error', 'Gagal menonaktifkan karyawan.');
            }

            return redirect()->to('/employee')->with('success', "Karyawan <b>{$employee['name']}</b> dinonaktifkan. Akses Web & Mesin Fisiknya telah dicabut permanen.");

        } catch (\Exception $e) {
            $db->transRollback();
            return redirect()->to('/employee')->with('error', 'Terjadi kesalahan sistem.');
        }
    }

    // --- FUNGSI IOT: GET USERINFO ---
    public function sync_biometric($employee_id)
    {
        if (!session()->get('isLoggedIn') || (session()->get('role') !== 'admin' && session()->get('department') !== 'Manajemen & HRD')) {
            return redirect()->to('/portal');
        }

        $fingerspot = new \App\Libraries\Fingerspot();
        $response = $fingerspot->getUserInfo($employee_id);

        if (isset($response['success']) && $response['success']) {
            return redirect()->back()->with('success', "Perintah sinkronisasi untuk NIK {$employee_id} terkirim. Refresh halaman ini dalam beberapa detik untuk melihat pembaruan data biometrik.");
        } else {
            return redirect()->back()->with('error', 'Gagal terhubung ke Cloud Fingerspot. Pastikan mesin dalam keadaan online.');
        }
    }

    // --- FUNGSI IOT: SET USERINFO ---
    public function push_to_machine($id)
    {
        if (!session()->get('isLoggedIn') || (session()->get('role') !== 'admin' && session()->get('department') !== 'Manajemen & HRD')) {
            return redirect()->to('/portal');
        }

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

    // --- FUNGSI IOT: REG ONLINE (REKAM JARI) ---
    public function trigger_register_online($pin)
    {
        if (!session()->get('isLoggedIn') || (session()->get('role') !== 'admin' && session()->get('department') !== 'Manajemen & HRD')) {
            return redirect()->to('/portal');
        }

        $fingerspot = new \App\Libraries\Fingerspot();
        $response = $fingerspot->registerOnline($pin, "0"); 

        if (isset($response['success']) && $response['success']) {
            return redirect()->back()->with('success', "Perintah rekam jari terkirim! Silakan minta karyawan untuk segera menempelkan jarinya ke mesin absensi.");
        } else {
            return redirect()->back()->with('error', 'Gagal mengirim perintah. Pastikan mesin dalam keadaan online dan standby.');
        }
    }
}