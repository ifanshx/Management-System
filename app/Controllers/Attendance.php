<?php

namespace App\Controllers;
use App\Models\AttendanceModel;
use App\Models\EmployeeModel;
use App\Models\WorkShiftModel;

class Attendance extends BaseController
{
    public function index()
    {
        if (!session()->get('isLoggedIn') || (session()->get('role') !== 'admin' && session()->get('department') !== 'Manajemen & HRD')) {
            return redirect()->to('/portal');
        }

        $attModel = new AttendanceModel();
        
        // Ambil filter tanggal dari URL (Default: Hari ini)
        $dateFilter = $this->request->getGet('date') ?? date('Y-m-d');
        
        $attendances = $attModel->getDailyAttendance($dateFilter);

        $data = [
            'title'       => 'Log Kehadiran | Noric HR',
            'dateFilter'  => $dateFilter,
            'attendances' => $attendances
        ];

        return view('attendance/index', $data);
    }

    // --- FUNGSI BARU: TARIK DATA ABSENSI TERSANGKUT (GET ATTLOG) ---
    public function syncData()
    {
        if (!session()->get('isLoggedIn') || (session()->get('role') !== 'admin')) return redirect()->to('/portal');

        $dateFilter = $this->request->getGet('date') ?? date('Y-m-d');
        
        $fingerspot = new \App\Libraries\Fingerspot();
        $attModel = new AttendanceModel();
        $empModel = new EmployeeModel();
        $shiftModel = new WorkShiftModel();

        // Tembak API Get Attlog (Ambil data hari ini saja sesuai filter)
        $response = $fingerspot->getAttlog($dateFilter, $dateFilter);

        if (isset($response['success']) && $response['success'] && !empty($response['data'])) {
            $syncedCount = 0;

            foreach ($response['data'] as $log) {
                // Catatan: Di Webhook namanya 'scan', tapi di Get Attlog namanya 'scan_date'
                $pin = $log['pin']; 
                $scanTime = $log['scan_date'] ?? $log['scan']; 

                $datePart = date('Y-m-d', strtotime($scanTime));
                $timePart = date('H:i:s', strtotime($scanTime));

                // 1. Cari NIK berdasarkan PIN Mesin
                $emp = $empModel->where('pin', $pin)->first();
                if (!$emp) continue; // Lewati jika PIN tidak dikenal sistem web

                // 2. Cek apakah log ini sudah masuk ke database
                $existingLog = $attModel->where(['employee_id' => $emp['employee_id'], 'date' => $datePart])->first();

                // 3. LOGIKA MASUK
                if (!$existingLog) {
                    $shift = $shiftModel->find($emp['shift_id']);
                    $status = 'Hadir';
                    $lateMinutes = 0;

                    if ($shift) {
                        $shiftStart = strtotime($datePart . ' ' . $shift['start_time']);
                        $actualIn = strtotime($scanTime);

                        // Hitung telat + toleransi
                        if ($actualIn > ($shiftStart + ($shift['tolerance_minutes'] * 60))) {
                            $status = 'Terlambat';
                            $lateMinutes = round(($actualIn - $shiftStart) / 60);
                        }
                    }

                    $attModel->insert([
                        'employee_id'  => $emp['employee_id'], // Simpan NIK, bukan PIN
                        'date'         => $datePart,
                        'time_in'      => $timePart,
                        'status'       => $status,
                        'late_minutes' => $lateMinutes
                    ]);
                    $syncedCount++;
                } 
                // 4. LOGIKA PULANG (Jika sudah ada absen masuk, tapi time_out masih kosong)
                elseif ($existingLog && empty($existingLog['time_out']) && $timePart > $existingLog['time_in']) {
                    $attModel->update($existingLog['id'], ['time_out' => $timePart]);
                    $syncedCount++;
                }
            }

            return redirect()->back()->with('success', "Sinkronisasi selesai! Berhasil menarik <b>{$syncedCount}</b> log data baru dari mesin absen.");
        }

        return redirect()->back()->with('info', 'Tidak ada data baru di mesin untuk tanggal tersebut, atau mesin sedang offline.');
    }

    // --- FUNGSI BARU: SINKRONISASI JAM MESIN IOT ---
    public function syncMachineTime()
    {
        // Hanya Admin / HRD yang boleh mengeksekusi
        if (!session()->get('isLoggedIn') || (session()->get('role') !== 'admin')) {
            return redirect()->to('/portal');
        }

        $fingerspot = new \App\Libraries\Fingerspot();
        
        // Tembak API Set Time dengan zona waktu WIB (Asia/Jakarta)
        $response = $fingerspot->setTime('Asia/Jakarta');

        if (isset($response['success']) && $response['success']) {
            return redirect()->back()->with('success', 'Perintah sinkronisasi waktu berhasil dikirim! Jam pada mesin fisik pabrik akan disesuaikan dengan zona waktu server (Asia/Jakarta).');
        } else {
            return redirect()->back()->with('error', 'Gagal mengirim perintah. Pastikan mesin dalam keadaan online.');
        }
    }
}