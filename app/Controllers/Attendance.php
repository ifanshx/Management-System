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
            'title'       => 'Log Kehadiran',
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

    // ========================================================================
    // 1. TAMPILKAN FORM KOREKSI MANUAL
    // ========================================================================
    public function manual()
    {
        if (!session()->get('isLoggedIn') || (session()->get('role') !== 'admin' && session()->get('department') !== 'Manajemen & HRD')) {
            return redirect()->to('/portal');
        }

        $empModel = new EmployeeModel();
        
        $data = [
            'title'     => 'Koreksi Absensi Manual',
            'employees' => $empModel->where('is_active', 1)->orderBy('name', 'ASC')->findAll()
        ];

        return view('attendance/manual', $data);
    }

   // ========================================================================
    // 2. SIMPAN & KALKULASI ULANG ABSENSI MANUAL (UPDATE CERDAS)
    // ========================================================================
    public function store_manual()
    {
        if (!session()->get('isLoggedIn') || (session()->get('role') !== 'admin' && session()->get('department') !== 'Manajemen & HRD')) {
            return redirect()->to('/portal');
        }

        $attModel   = new AttendanceModel();
        $empModel   = new EmployeeModel();
        $shiftModel = new WorkShiftModel();

        $employee_id = $this->request->getPost('employee_id');
        $date        = $this->request->getPost('date');
        $status      = $this->request->getPost('status'); // Hadir / Terlambat dll

        $emp = $empModel->where('employee_id', $employee_id)->first();
        if (!$emp) return redirect()->back()->with('error', 'Karyawan tidak valid.');

        $shift = $shiftModel->find($emp['shift_id']);
        
        // 1. Cek apakah karyawan sudah punya data absensi di tanggal tersebut
        $existingLog = $attModel->where(['employee_id' => $employee_id, 'date' => $date])->first();

        // 2. SMART MERGE (PENGGABUNGAN DATA)
        // Jika HRD mengosongkan form, pertahankan data yang sudah ada di Database!
        $time_in   = $this->request->getPost('time_in')   ?: ($existingLog ? $existingLog['time_in'] : null);
        $time_out  = $this->request->getPost('time_out')  ?: ($existingLog ? $existingLog['time_out'] : null);
        $break_out = $this->request->getPost('break_out') ?: ($existingLog ? $existingLog['break_out'] : null);
        $break_in  = $this->request->getPost('break_in')  ?: ($existingLog ? $existingLog['break_in'] : null);

        $lateMinutes = 0;
        $overtimeMinutes = 0;
        $workDurationMinutes = 0;

        // 3. KALKULASI ULANG menggunakan data yang sudah digabung
        if (in_array($status, ['Hadir', 'Terlambat']) && $shift && !empty($time_in)) {
            
            // Hitung Keterlambatan
            $shiftStart = strtotime($date . ' ' . $shift['time_in']);
            $actualIn   = strtotime($date . ' ' . $time_in);
            
            if ($actualIn > ($shiftStart + ($shift['late_tolerance'] * 60))) {
                $status = 'Terlambat';
                $lateMinutes = round(($actualIn - $shiftStart) / 60);
            } else {
                $status = 'Hadir'; // Koreksi otomatis jika ternyata tidak telat
            }

            // Hitung Lembur & Durasi (Jika Jam Pulang sudah ada / baru saja diisi)
            if (!empty($time_out)) {
                $shiftEnd = strtotime($date . ' ' . $shift['time_out']);
                $actualOut = strtotime($date . ' ' . $time_out);
                
                // Antisipasi Shift Malam
                if ($shift['time_out'] < $shift['time_in']) $shiftEnd += 86400;
                if ($actualOut < $actualIn) $actualOut += 86400;

                // Hitung Lembur
                if ($actualOut > $shiftEnd) {
                    $overtimeMinutes = round(($actualOut - $shiftEnd) / 60);
                    if ($overtimeMinutes < $shift['min_overtime']) $overtimeMinutes = 0;
                    if ($overtimeMinutes > 0 && $shift['overtime_deduction'] > 0) {
                        $overtimeMinutes -= $shift['overtime_deduction'];
                    }
                }

                // Hitung Durasi Kerja Bersih
                $totalSeconds = $actualOut - $actualIn;
                
                // Kurangi waktu istirahat
                if (!empty($break_out) && !empty($break_in)) {
                    $bOut = strtotime($date . ' ' . $break_out);
                    $bIn  = strtotime($date . ' ' . $break_in);
                    if ($bIn > $bOut) {
                        $totalSeconds -= ($bIn - $bOut);
                    }
                }
                $workDurationMinutes = round($totalSeconds / 60);
            }
        }

        // 4. Siapkan Data Final
        $dataToSave = [
            'employee_id'           => $employee_id,
            'date'                  => $date,
            'time_in'               => $time_in,
            'time_out'              => $time_out,
            'break_out'             => $break_out,
            'break_in'              => $break_in,
            'status'                => $status,
            'late_minutes'          => $lateMinutes,
            'overtime_minutes'      => $overtimeMinutes,
            'work_duration_minutes' => $workDurationMinutes
        ];

        // 5. Simpan ke Database
        if ($existingLog) {
            $attModel->update($existingLog['id'], $dataToSave);
            $msg = 'Data absensi berhasil dikoreksi dan dikalkulasi ulang tanpa menghilangkan data sebelumnya.';
        } else {
            $attModel->insert($dataToSave);
            $msg = 'Data absensi manual berhasil ditambahkan.';
        }

        return redirect()->to('/attendance')->with('success', $msg);
    }

    // ========================================================================
    // 3. HAPUS DATA ABSENSI
    // ========================================================================
    public function delete($id)
    {
        if (!session()->get('isLoggedIn') || (session()->get('role') !== 'admin' && session()->get('department') !== 'Manajemen & HRD')) {
            return redirect()->to('/portal');
        }

        $attModel = new AttendanceModel();
        $attModel->delete($id);

        return redirect()->back()->with('success', 'Catatan absensi berhasil dihapus.');
    }
}