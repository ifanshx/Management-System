<?php

namespace App\Controllers;
use App\Libraries\Fingerspot;
use App\Models\EmployeeModel;

class Device extends BaseController
{
    public function index()
    {
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'admin') {
            return redirect()->to('/portal');
        }

        $fingerspot = new Fingerspot();
        
        // Panggil API Get Device untuk mengecek status mesin Live
        $deviceInfo = $fingerspot->getDeviceInfo();

        // Hitung Karyawan Aktif di Database untuk dikomparasi dengan kapasitas mesin
        $empModel = new EmployeeModel();
        $totalDbUsers = $empModel->where('is_active', 1)->where('pin IS NOT NULL')->countAllResults();

        // --- AMBIL CLOUD ID DARI DATABASE ---
        $db = \Config\Database::connect();
        $fsConfig = $db->table('fingerspot_api')->where('id', 1)->get()->getRowArray();
        $cloudId = $fsConfig['cloud_id'] ?? '-';

        $data = [
            'title'        => 'Control Panel Mesin',
            'cloud_id'     => $cloudId, // <-- Melempar Cloud ID dari Database ke View
            'device'       => (isset($deviceInfo['success']) && $deviceInfo['success']) ? $deviceInfo['data'] : null,
            'totalDbUsers' => $totalDbUsers
        ];

        return view('device/index', $data);
    }

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

    // =======================================================================
    // FITUR BARU: SINKRONISASI MASSAL (DATABASE -> MESIN)
    // =======================================================================
    public function push_all_users()
    {
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'admin') return redirect()->to('/portal');
        
        $empModel = new EmployeeModel();
        // Ambil semua karyawan yang masih aktif dan punya PIN
        $employees = $empModel->where('is_active', 1)->where('pin IS NOT NULL')->findAll();

        if (empty($employees)) {
            return redirect()->back()->with('error', 'Tidak ada karyawan aktif untuk dikirim ke mesin.');
        }

        $fingerspot = new Fingerspot();
        $successCount = 0;

        // Looping Push Data ke Mesin Fisik
        foreach ($employees as $emp) {
            $privilege = $emp['machine_privilege'] ?? "1"; 
            $response = $fingerspot->setUserInfo($emp['pin'], $emp['name'], $privilege, "", $emp['rfid']);
            
            if (isset($response['success']) && $response['success']) {
                $successCount++;
            }
        }

        if ($successCount > 0) {
            return redirect()->back()->with('success', "Sinkronisasi Selesai! $successCount Data Karyawan Web berhasil di-push ke mesin absen.");
        }
        return redirect()->back()->with('error', 'Gagal mengirim data massal. Pastikan mesin IoT sedang online.');
    }

    // =======================================================================
    // FITUR AUDIT (MESIN -> DATABASE)
    // =======================================================================
    public function audit_pins()
    {
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'admin') return redirect()->to('/portal');
        
        $fingerspot = new Fingerspot();
        $response = $fingerspot->getAllPin();

        if (isset($response['success']) && $response['success']) {
            return redirect()->back()->with('success', 'Perintah Tarik Data terkirim. Daftar PIN di mesin akan disesuaikan dengan Database Web di latar belakang.');
        }
        return redirect()->back()->with('error', 'Gagal mengirim perintah audit ke mesin.');
    }
    
    // =======================================================================
    // FITUR PENGATURAN API FINGERSPOT (TAMPILAN & UPDATE)
    // =======================================================================
    public function settings()
    {
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'admin') return redirect()->to('/portal');
        
        $db = \Config\Database::connect();
        $fsConfig = $db->table('fingerspot_api')->where('id', 1)->get()->getRowArray();

        $data = [
            'title'    => 'Pengaturan Mesin (API)',
            'fsConfig' => $fsConfig
        ];

        return view('device/settings', $data);
    }

    public function update_settings()
    {
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'admin') return redirect()->to('/portal');
        
        $db = \Config\Database::connect();
        
        $updateData = [
            'api_url'   => $this->request->getPost('api_url'),
            'api_token' => $this->request->getPost('api_token'),
            'cloud_id'  => $this->request->getPost('cloud_id'),
        ];

        // Update data di database (ID 1 karena kita hanya punya 1 konfigurasi)
        $db->table('fingerspot_api')->where('id', 1)->update($updateData);

        return redirect()->back()->with('success', 'Konfigurasi Mesin Fingerspot berhasil diperbarui!');
    }
}