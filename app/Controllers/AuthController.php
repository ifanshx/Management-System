<?php

namespace App\Controllers;
use App\Models\UserModel;

class AuthController extends BaseController
{
    public function index()
    {
        // Jika sudah login, langsung arahkan ke dashboard
        if (session()->get('isLoggedIn')) {
            return redirect()->to('/dashboard');
        }
        return view('auth/login');
    }

 public function process()
    {
        $session = session();
        $userModel = new \App\Models\UserModel();

        $username = $this->request->getPost('username');
        $password = $this->request->getPost('password');

        $user = $userModel->where('username', $username)->first();

        if ($user) {
            if (password_verify($password, $user['password'])) {
                
                $sessionData = [
                    'id'          => $user['id'],
                    'employee_id' => $user['employee_id'] ?? 'SUPER-ADMIN', // <-- LANGSUNG AMBIL DARI USERS
                    'username'    => $user['username'],
                    'name'        => $user['name'],
                    'role'        => $user['role'],
                    'isLoggedIn'  => TRUE
                ];

                // Hanya ambil Divisi & Jabatan untuk keperluan Sidebar Dinamis
                if ($user['role'] === 'karyawan') {
                    $employeeModel = new \App\Models\EmployeeModel();
                    $empData = $employeeModel->where('employee_id', $user['employee_id'])->first();
                    
                    $sessionData['department'] = $empData ? $empData['department'] : 'Umum';
                    $sessionData['position']   = $empData ? $empData['position'] : 'Staf';
                } else {
                    $sessionData['department'] = 'Manajemen Pusat';
                    $sessionData['position']   = 'System Administrator';
                }

                $session->set($sessionData);

                return redirect()->to($user['role'] === 'admin' ? '/dashboard' : '/portal');
                
            } else {
                $session->setFlashdata('error', 'Password salah.');
                return redirect()->to('/login');
            }
        } else {
            $session->setFlashdata('error', 'Username tidak ditemukan.');
            return redirect()->to('/login');
        }
    }
    public function logout()
    {
        session()->destroy();
        return redirect()->to('/login');
    }
}