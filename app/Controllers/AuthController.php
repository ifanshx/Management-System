<?php

namespace App\Controllers;
use App\Models\UserModel;

class AuthController extends BaseController
{
    public function index()
    {
        // Jika sudah login, langsung arahkan ke dashboard/portal
        if (session()->get('isLoggedIn')) {
            return session()->get('role') === 'admin' ? redirect()->to('/dashboard') : redirect()->to('/portal');
        }
        
        // Panggil BaseController otomatis mendistribusikan data $company ke view
        return view('auth/login');
    }

    public function process()
    {
        $session = session();
        $userModel = new UserModel();
        $db = \Config\Database::connect(); // Akses DB untuk Query Builder JOIN

        $username = $this->request->getPost('username');
        $password = $this->request->getPost('password');

        $user = $userModel->where('username', $username)->first();

        if ($user) {
            if (password_verify((string)$password, $user['password'])) {
                
                $sessionData = [
                    'id'          => $user['id'],
                    'employee_id' => $user['employee_id'] ?? 'SUPER-ADMIN',
                    'username'    => $user['username'],
                    'name'        => $user['name'],
                    'role'        => $user['role'],
                    'isLoggedIn'  => TRUE
                ];

                // Mengambil Data Divisi & Jabatan dari Database yang sudah dinormalisasi
                if ($user['role'] === 'karyawan' && !empty($user['employee_id'])) {
                    
                    $empRecord = $db->table('employees')
                                    ->select('employees.is_active, departments.name as dept_name, positions.name as pos_name')
                                    ->join('departments', 'departments.id = employees.department_id', 'left')
                                    ->join('positions', 'positions.id = employees.position_id', 'left')
                                    ->where('employees.employee_id', $user['employee_id'])
                                    ->get()->getRowArray();
                    
                    if ($empRecord) {
                        // BLOKIR AKSES JIKA KARYAWAN SUDAH RESIGN / NON-AKTIF
                        if ($empRecord['is_active'] == 0) {
                            $session->setFlashdata('error', 'Akses Ditolak: Akun Anda telah dinonaktifkan.');
                            return redirect()->to('/login');
                        }

                        // Simpan nama departemen dan posisi aktual ke Session
                        $sessionData['department_name'] = $empRecord['dept_name'] ?? 'Belum Diatur';
                        $sessionData['position_name']   = $empRecord['pos_name'] ?? 'Staf';
                        
                        // Fallback untuk variabel lama (mencegah error di view versi sebelumnya)
                        $sessionData['department'] = $empRecord['dept_name'] ?? 'Belum Diatur';
                        $sessionData['position']   = $empRecord['pos_name'] ?? 'Staf';
                    } else {
                        $sessionData['department_name'] = 'Umum';
                        $sessionData['position_name']   = 'Staf';
                    }

                } else {
                    // Jika yang login adalah Admin
                    $sessionData['department_name'] = 'Manajemen Pusat';
                    $sessionData['position_name']   = 'System Administrator';
                    $sessionData['department']      = 'Manajemen Pusat'; // Fallback
                    $sessionData['position']        = 'System Administrator'; // Fallback
                }

                $session->set($sessionData);

                // Redirect cerdas berdasarkan Role
                return redirect()->to($user['role'] === 'admin' ? '/dashboard' : '/portal');
                
            } else {
                $session->setFlashdata('error', 'Password yang Anda masukkan salah.');
                return redirect()->to('/login');
            }
        } else {
            $session->setFlashdata('error', 'Username tidak ditemukan di dalam sistem.');
            return redirect()->to('/login');
        }
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to('/login')->with('info', 'Sesi Anda telah diakhiri dengan aman.');
    }
}