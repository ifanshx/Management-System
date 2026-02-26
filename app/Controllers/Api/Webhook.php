<?php

namespace App\Controllers\Api;
use CodeIgniter\RESTful\ResourceController;
use App\Models\AttendanceModel;
use App\Models\EmployeeModel;
use App\Models\WorkShiftModel;

class Webhook extends ResourceController
{
    // ====================================================================
    // ENDPOINT UTAMA PENERIMA WEBHOOK DARI MESIN FINGERSPOT
    // ====================================================================
    public function fingerspot()
    {
        $requestData = $this->request->getJSON(true); // Tangkap payload JSON
        $cloudIdEnv  = getenv('FINGERSPOT_CLOUD_ID');

        // 1. Keamanan Dasar: Validasi Payload
        if (!$requestData || !isset($requestData['type']) || !isset($requestData['cloud_id'])) {
            return $this->fail('Invalid payload format', 400);
        }

        // Pastikan mesin yang mengirim data adalah mesin pabrik kita
        if ($requestData['cloud_id'] !== $cloudIdEnv) {
            return $this->failUnauthorized('Unrecognized Cloud ID');
        }

        $type = $requestData['type'];
        $db = \Config\Database::connect();

        // 2. Simpan Data Mentah ke Log (Untuk keperluan Audit IT)
        $db->table('fingerspot_logs')->insert([
            'cloud_id'      => $requestData['cloud_id'],
            'type'          => $type,
            'original_data' => json_encode($requestData),
            'created_at'    => date('Y-m-d H:i:s')
        ]);

        // 3. ROUTER TUGAS BERDASARKAN TIPE WEBHOOK
        if ($type === 'attlog') {
            return $this->processRealtimeAttendance($requestData['data']);
        } 
        elseif ($type === 'get_userinfo') {
            return $this->processUserInfoSync($requestData['data']);
        }
        elseif ($type === 'set_userinfo') {
            return $this->processSetUserInfoStatus($requestData);
        }
        elseif ($type === 'delete_userinfo') {
            return $this->processDeleteUserInfoStatus($requestData);
        }
        elseif ($type === 'get_userid_list') {
            return $this->processGetAllPinStatus($requestData);
        }

        elseif ($type === 'set_time') {
            return $this->processSetTimeStatus($requestData);
        }

        elseif ($type === 'register_online') {
            return $this->processRegisterOnlineStatus($requestData);
        }

        return $this->respond(['status' => 'OK', 'message' => "Log for type '{$type}' saved successfully."]);
    }

    // ====================================================================
    // FUNGSI 1: LOGIKA SINKRONISASI DATA USER/KARYAWAN
    // ====================================================================
    private function processUserInfoSync($data)
    {
        $empModel = new EmployeeModel();
        
        $pin = $data['pin'] ?? null;
        if (!$pin) return $this->fail('PIN is missing in payload data');

        $emp = $empModel->where('pin', $pin)->first();
        
        if ($emp) {
            $updateData = [
                'rfid'              => $data['rfid'] ?? null,
                'finger_count'      => $data['finger'] ?? 0,
                'face_count'        => $data['face'] ?? 0,
                'machine_privilege' => $data['privilege'] ?? 1
            ];
            
            $empModel->update($emp['id'], $updateData);
            
            return $this->respond([
                'status'  => 'OK', 
                'message' => "Biometric data for employee {$emp['name']} synced successfully."
            ]);
        } else {
            return $this->failNotFound("Employee with PIN {$pin} not found in ERP Database.");
        }
    }

    // ====================================================================
    // FUNGSI 2: LOGIKA ABSENSI REAL-TIME (SMART HEURISTIC SCAN)
    // ====================================================================
    private function processRealtimeAttendance($data)
    {
        $attModel = new AttendanceModel();
        $empModel = new EmployeeModel();
        $shiftModel = new WorkShiftModel();

        $pin = $data['pin']; 
        $scanTime = $data['scan'];

        $datePart = date('Y-m-d', strtotime($scanTime));
        $timePart = date('H:i:s', strtotime($scanTime));

        $emp = $empModel->where('pin', $pin)->first();
        if (!$emp) return $this->failNotFound("Employee PIN $pin not found.");

        $nikWeb = $emp['employee_id'];
        $existingLog = $attModel->where(['employee_id' => $nikWeb, 'date' => $datePart])->first();
        $shift = $shiftModel->find($emp['shift_id']);

        // -----------------------------------------------------------
        // SKENARIO 1: SCAN PERTAMA KALI (MASUK)
        // -----------------------------------------------------------
        if (!$existingLog) {
            $status = 'Hadir';
            $lateMinutes = 0;

            if ($shift) {
                $shiftStart = strtotime($datePart . ' ' . $shift['start_time']);
                $actualIn = strtotime($scanTime);
                if ($actualIn > ($shiftStart + ($shift['tolerance_minutes'] * 60))) {
                    $status = 'Terlambat';
                    $lateMinutes = round(($actualIn - $shiftStart) / 60);
                }
            }

            $attModel->insert([
                'employee_id'  => $nikWeb,
                'date'         => $datePart,
                'time_in'      => $timePart,
                'status'       => $status,
                'late_minutes' => $lateMinutes
            ]);

            return $this->respond(['status' => 'OK', 'message' => 'Check-IN Recorded']);
        } 
        
        // -----------------------------------------------------------
        // SKENARIO 2: SCAN LANJUTAN (SMART DETECTION)
        // -----------------------------------------------------------
        else {
            $timeInStr = strtotime($existingLog['date'] . ' ' . $existingLog['time_in']);
            $currentScanStr = strtotime($scanTime);
            $hoursElapsed = ($currentScanStr - $timeInStr) / 3600;

            // Abaikan jika jarak scan terlalu cepat (dibawah 1 jam) -> Pencegahan Double Tap
            if ($hoursElapsed < 1) {
                return $this->respond(['status' => 'OK', 'message' => 'Duplicate Scan Ignored']);
            }

            $updateData = [];

            // A. ISTIRAHAT KELUAR (Terjadi antara 2.5 hingga 5 jam setelah absen masuk)
            if ($hoursElapsed >= 2.5 && $hoursElapsed <= 5 && empty($existingLog['break_out'])) {
                $updateData['break_out'] = $timePart;
            } 
            // B. ISTIRAHAT MASUK (Terjadi antara 3.5 hingga 6 jam setelah masuk)
            elseif ($hoursElapsed >= 3.5 && $hoursElapsed <= 6 && !empty($existingLog['break_out']) && empty($existingLog['break_in'])) {
                $updateData['break_in'] = $timePart;
            } 
            // C. PULANG (Lebih dari 6 jam, atau menimpa absen pulang sebelumnya)
            elseif ($hoursElapsed > 6) {
                $updateData['time_out'] = $timePart;

                if ($shift) {
                    // PERBAIKAN: Gunakan time_out sesuai database Anda
                    $shiftEnd = strtotime($datePart . ' ' . $shift['time_out']);
                    
                    // Jika shift malam (melewati tengah malam)
                    if ($shift['time_out'] < $shift['time_in']) $shiftEnd += 86400; 

                    if ($currentScanStr > $shiftEnd) {
                        $updateData['overtime_minutes'] = round(($currentScanStr - $shiftEnd) / 60);
                    } else {
                        $updateData['overtime_minutes'] = 0;
                    }
                }

                // Hitung Total Durasi Kerja Bersih (Masuk s/d Pulang, dikurangi waktu istirahat jika ada)
                $totalWorkSeconds = $currentScanStr - $timeInStr;
                
                $bOut = $existingLog['break_out'] ? strtotime($existingLog['date'] . ' ' . $existingLog['break_out']) : 0;
                $bIn = $existingLog['break_in'] ? strtotime($existingLog['date'] . ' ' . $existingLog['break_in']) : 0;
                
                if ($bOut > 0 && $bIn > 0) {
                    $breakDuration = $bIn - $bOut;
                    $totalWorkSeconds -= $breakDuration;
                }

                $updateData['work_duration_minutes'] = round($totalWorkSeconds / 60);
            }

            if (!empty($updateData)) {
                $attModel->update($existingLog['id'], $updateData);
                return $this->respond(['status' => 'OK', 'message' => 'Status Updated Automatically']);
            }

            return $this->respond(['status' => 'OK', 'message' => 'Scan ignored (Does not fit logical timeframe)']);
        }
    }

    // ====================================================================
    // FUNGSI 3: LOGIKA BALASAN SET USERINFO
    // ====================================================================
    private function processSetUserInfoStatus($payload)
    {
        $status = $payload['data']['status'] ?? null; 
        $transId = $payload['trans_id'] ?? 'unknown';

        if ($status == "1") {
            log_message('info', "[Fingerspot] Karyawan sukses ditambahkan ke mesin. Trans ID: {$transId}");
        } elseif ($status == "2") {
            log_message('error', "[Fingerspot] GAGAL mendaftarkan karyawan ke mesin. Trans ID: {$transId}");
        }

        return $this->respond(['status' => 'OK', 'message' => 'Set Userinfo status processed']);
    }

    // ====================================================================
    // FUNGSI 4: LOGIKA BALASAN DELETE USERINFO
    // ====================================================================
    private function processDeleteUserInfoStatus($payload)
    {
        $status = $payload['data']['status'] ?? null; 
        $transId = $payload['trans_id'] ?? 'unknown';

        if ($status == "1") {
            log_message('info', "[Fingerspot Security] Akses biometrik mesin BERHASIL dicabut. Trans ID: {$transId}");
        } elseif ($status == "2") {
            log_message('warning', "[Fingerspot Security] GAGAL mencabut akses biometrik mesin (mungkin PIN tidak ditemukan). Trans ID: {$transId}");
        }

        return $this->respond(['status' => 'OK', 'message' => 'Delete Userinfo status processed']);
    }

    // ====================================================================
    // FUNGSI 5: LOGIKA BALASAN AUDIT GET ALL PIN
    // ====================================================================
    private function processGetAllPinStatus($payload)
    {
        $total = $payload['data']['total'] ?? 0;
        $pinArr = $payload['data']['pin_arr'] ?? [];
        $transId = $payload['trans_id'] ?? 'unknown';

        if (is_array($pinArr) && count($pinArr) > 0) {
            $pinListString = implode(', ', $pinArr);
            log_message('info', "[Fingerspot Audit] Berhasil menarik {$total} PIN dari mesin. Daftar PIN: [{$pinListString}]. Trans ID: {$transId}");
        } else {
            log_message('warning', "[Fingerspot Audit] Mesin merespons, tetapi tidak ada PIN yang terdaftar di mesin (Kosong).");
        }

        return $this->respond(['status' => 'OK', 'message' => 'Get All PIN list processed']);
    }

    // ====================================================================
    // FUNGSI 6: LOGIKA BALASAN SINKRONISASI WAKTU MESIN
    // ====================================================================
    private function processSetTimeStatus($payload)
    {
        // Status 1: Aksi berhasil, 2: Aksi gagal
        $status = $payload['data']['status'] ?? null; 
        $transId = $payload['trans_id'] ?? 'unknown';

        if ($status == "1") {
            log_message('info', "[Fingerspot IoT] Waktu mesin BERHASIL disinkronkan dengan server. Trans ID: {$transId}");
        } elseif ($status == "2") {
            log_message('error', "[Fingerspot IoT] GAGAL melakukan sinkronisasi waktu mesin. Trans ID: {$transId}");
        }

        return $this->respond(['status' => 'OK', 'message' => 'Set Time status processed']);
    }

    // ====================================================================
    // FUNGSI 7: LOGIKA BALASAN REGISTER ONLINE (REKAM JARI/WAJAH LANGSUNG)
    // ====================================================================
    private function processRegisterOnlineStatus($payload)
    {
        // Status 1: berhasil direkam, 2: gagal direkam / timeout
        $status = $payload['data']['status'] ?? null; 
        $transId = $payload['trans_id'] ?? 'unknown';

        if ($status == "1") {
            log_message('info', "[Fingerspot IoT] Perekaman biometrik di mesin BERHASIL diselesaikan oleh karyawan. Trans ID: {$transId}");
            
            // Catatan: Biasanya setelah ini HRD akan menekan tombol "Sync Data Jari" 
            // untuk menarik update jumlah jari ke database web.
        } elseif ($status == "2") {
            log_message('warning', "[Fingerspot IoT] Perekaman biometrik GAGAL (mungkin karyawan tidak menempelkan jari hingga batas waktu habis). Trans ID: {$transId}");
        }

        return $this->respond(['status' => 'OK', 'message' => 'Register Online status processed']);
    }

    // ====================================================================
    // FUNGSI : PING TEST DARI BROWSER (GET METHOD)
    // ====================================================================
    public function ping()
    {
        return $this->respond([
            'status'  => 'OK',
            'message' => 'Noric Webhook Endpoint is Ready. Please send POST request with Fingerspot JSON payload.'
        ]);
    }
}