<?php

namespace App\Controllers;
use App\Libraries\Fingerspot;

class Device extends BaseController
{
    public function index()
    {
        // Kunci akses: Hanya Super Admin / IT / Manajer HRD
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'admin') {
            return redirect()->to('/portal');
        }

        $fingerspot = new Fingerspot();
        
        // Panggil API Get Device untuk mengecek status mesin Live
        $deviceInfo = $fingerspot->getDeviceInfo();

        $data = [
            'title'      => 'Control Panel Mesin IoT | Noric System',
            'cloud_id'   => getenv('FINGERSPOT_CLOUD_ID'),
            // Jika sukses, kirim datanya. Jika gagal (offline), kirim null
            'device'     => (isset($deviceInfo['success']) && $deviceInfo['success']) ? $deviceInfo['data'] : null
        ];

        return view('device/index', $data);
    }

    // --- FUNGSI 1: SINKRONISASI JAM ---
    public function sync_time()
    {
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'admin') return redirect()->to('/portal');
        
        $fingerspot = new Fingerspot();
        $response = $fingerspot->setTime('Asia/Jakarta');

        if (isset($response['success']) && $response['success']) {
            return redirect()->back()->with('success', 'Waktu mesin berhasil disinkronkan dengan server (Asia/Jakarta).');
        }
        return redirect()->back()->with('error', 'Gagal mengirim perintah. Mesin offline.');
    }

    // --- FUNGSI 2: RESTART MESIN ---
    public function restart()
    {
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'admin') return redirect()->to('/portal');
        
        $fingerspot = new Fingerspot();
        $response = $fingerspot->restartDevice();

        if (isset($response['success']) && $response['success']) {
            return redirect()->back()->with('success', 'Perintah Soft-Reboot terkirim. Mesin akan mati dan menyala kembali dalam 1 menit.');
        }
        return redirect()->back()->with('error', 'Gagal merestart mesin IoT.');
    }

    // --- FUNGSI 3: AUDIT PIN (Tarik semua ID) ---
    public function audit_pins()
    {
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'admin') return redirect()->to('/portal');
        
        $fingerspot = new Fingerspot();
        $response = $fingerspot->getAllPin();

        if (isset($response['success']) && $response['success']) {
            return redirect()->back()->with('success', 'Perintah Audit terkirim. Daftar PIN akan dicatat di Log Server latar belakang.');
        }
        return redirect()->back()->with('error', 'Gagal mengirim perintah audit ke mesin.');
    }
}