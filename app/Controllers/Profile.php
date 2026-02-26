<?php

namespace App\Controllers;
use App\Models\UserModel;
use App\Models\EmployeeModel;

class Profile extends BaseController
{
    public function index()
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/login');

        $userModel = new UserModel();
        // Ambil data user dari tabel users
        $user = $userModel->find(session()->get('id'));

        $employeeData = null;
        // Jika dia karyawan, ambil detail lengkapnya dari tabel employees
        if ($user['role'] === 'karyawan') {
            $empModel = new EmployeeModel();
            $employeeData = $empModel->where('employee_id', $user['employee_id'])->first();
        }

        $data = [
            'title'    => 'Akun & Pengaturan | Noric Workspace',
            'user'     => $user,
            'employee' => $employeeData
        ];

        return view('profile/index', $data);
    }

    public function update_password()
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/login');

        $userModel = new UserModel();
        $userId = session()->get('id');
        $user = $userModel->find($userId);

        $oldPassword = $this->request->getPost('old_password');
        $newPassword = $this->request->getPost('new_password');

        // Verifikasi password lama
        if (!password_verify($oldPassword, $user['password'])) {
            return redirect()->to('/profile')->with('error', 'Password lama tidak sesuai!');
        }

        // Simpan password baru
        $userModel->update($userId, [
            'password' => password_hash($newPassword, PASSWORD_DEFAULT)
        ]);

        return redirect()->to('/profile')->with('success', 'Kata sandi akun Anda berhasil diperbarui. Silakan gunakan kata sandi baru pada login berikutnya.');
    }
}