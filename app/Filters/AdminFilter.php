<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AdminFilter implements FilterInterface
{
   public function before(RequestInterface $request, $arguments = null)
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login')->with('error', 'Silakan otorisasi akses Anda terlebih dahulu.');
        }
        
        $role = session()->get('role');
        $dept = session()->get('department');

        // Bolehkan lewat jika dia Admin ATAU Karyawan Divisi HRD
        $isAuthorized = ($role === 'admin') || ($role === 'karyawan' && $dept === 'Manajemen & HRD');

        if (!$isAuthorized) {
            return redirect()->to('/portal')->with('error', 'Akses ditolak! Halaman ini khusus Manajemen & HRD Noric.');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null) {}
}