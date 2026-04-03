<?php

namespace App\Controllers\Api;
use CodeIgniter\RESTful\ResourceController;
use App\Models\AttendanceModel;
use App\Models\EmployeeModel;
use App\Models\WorkShiftModel;
use App\Models\UserModel; 
use App\Libraries\Fingerspot; 

class Webhook extends ResourceController
{
    public function fingerspot()
    {
        $requestData = $this->request->getJSON(true); 
        $cloudIdEnv  = getenv('FINGERSPOT_CLOUD_ID');

        if (!$requestData || !isset($requestData['type']) || !isset($requestData['cloud_id'])) {
            return $this->fail('Invalid payload format', 400);
        }

        if ($requestData['cloud_id'] !== $cloudIdEnv) {
            return $this->failUnauthorized('Unrecognized Cloud ID');
        }

        $type = $requestData['type'];
        $db = \Config\Database::connect();

        $db->table('fingerspot_logs')->insert([
            'cloud_id'      => $requestData['cloud_id'],
            'type'          => $type,
            'original_data' => json_encode($requestData),
            'created_at'    => date('Y-m-d H:i:s')
        ]);

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

    private function processUserInfoSync($data)
    {
        $empModel = new EmployeeModel();
        $userModel = new UserModel();
        
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
        } 
        else {
            $tmpNik = "NIK-" . $pin . "-" . time();
            $name   = (!empty($data['name'])) ? $data['name'] : "USER MESIN ID " . $pin;

            $empData = [
                'pin'               => $pin,
                'employee_id'       => $tmpNik,
                'name'              => $name,
                'shift_id'          => 1, 
                'status'            => 'Tetap',
                'payment_method'    => 'Cash',
                'join_date'         => date('Y-m-d'),
                'is_active'         => 1,
                'basic_salary'      => 0,
                'machine_privilege' => $data['privilege'] ?? "0",
                'rfid'              => $data['rfid'] ?? null,
                'finger_count'      => $data['finger'] ?? 0,
                'face_count'        => $data['face'] ?? 0,
            ];

            $empModel->insert($empData);

            $userModel->insert([
                'employee_id' => $tmpNik,
                'username'    => "user" . $pin,
                'password'    => password_hash("123456", PASSWORD_DEFAULT),
                'name'        => $name,
                'role'        => 'karyawan'
            ]);

            return $this->respond([
                'status'  => 'OK', 
                'message' => "DRAFT Employee created for PIN {$pin} from Machine."
            ]);
        }
    }

    // ====================================================================
    // FUNGSI 2: LOGIKA ABSENSI REAL-TIME DENGAN STATUS_SCAN, FOTO & VERIFY
    // ====================================================================
    private function processRealtimeAttendance($data)
    {
        $attModel = new AttendanceModel();
        $empModel = new EmployeeModel();
        $shiftModel = new WorkShiftModel();

        $pin = $data['pin']; 
        $scanTime = $data['scan'];
        
        // TANGKAP 3 DATA PENTING DARI MESIN IOT
        $statusScan   = isset($data['status_scan']) ? (string)$data['status_scan'] : null;
        $photoUrl     = isset($data['photo_url']) ? (string)$data['photo_url'] : null; 
        $verifyMethod = isset($data['verify']) ? (string)$data['verify'] : null; // <--- TANGKAP METODE VERIFIKASI

        $datePart = date('Y-m-d', strtotime($scanTime));
        $timePart = date('H:i:s', strtotime($scanTime));

        $emp = $empModel->where('pin', $pin)->first();
        if (!$emp) return $this->failNotFound("Employee PIN $pin not found.");

        $nikWeb = $emp['employee_id'];
        $existingLog = $attModel->where(['employee_id' => $nikWeb, 'date' => $datePart])->first();
        $shift = $shiftModel->find($emp['shift_id']);

        if (!$existingLog) {
            $status = 'Hadir';
            $lateMinutes = 0;

            if ($shift && !empty($shift['time_in']) && ($statusScan === "0" || $statusScan === null)) {
                $shiftStart = strtotime($datePart . ' ' . $shift['time_in']);
                $actualIn = strtotime($scanTime);
                if ($actualIn > ($shiftStart + (($shift['late_tolerance'] ?? 0) * 60))) {
                    $status = 'Terlambat';
                    $lateMinutes = round(($actualIn - $shiftStart) / 60);
                }
            }

            $insertData = [
                'employee_id'   => $nikWeb,
                'date'          => $datePart,
                'status'        => $status,
                'late_minutes'  => $lateMinutes,
                'photo_url'     => $photoUrl, 
                'verify_method' => $verifyMethod // <--- SIMPAN VERIFIKASI
            ];

            if ($statusScan === "0") $insertData['time_in'] = $timePart;
            elseif ($statusScan === "1") $insertData['time_out'] = $timePart;
            elseif ($statusScan === "2") $insertData['break_in'] = $timePart; 
            elseif ($statusScan === "3") $insertData['break_out'] = $timePart;
            else $insertData['time_in'] = $timePart; 

            $attModel->insert($insertData);
            return $this->respond(['status' => 'OK', 'message' => 'Check-IN Recorded via Status']);
        } 
        else {
            $updateData = [];
            
            if (!empty($photoUrl)) $updateData['photo_url'] = $photoUrl;
            if (!empty($verifyMethod)) $updateData['verify_method'] = $verifyMethod; // <--- UPDATE VERIFIKASI TERBARU

            if ($statusScan === "0" && empty($existingLog['time_in'])) {
                $updateData['time_in'] = $timePart;
            } 
            elseif ($statusScan === "2") {
                $updateData['break_in'] = $timePart;
            } 
            elseif ($statusScan === "3") {
                $updateData['break_out'] = $timePart;
            } 
            elseif ($statusScan === "1") {
                $updateData['time_out'] = $timePart;

                $timeInStr = strtotime($existingLog['date'] . ' ' . $existingLog['time_in']);
                $currentScanStr = strtotime($scanTime);

                if ($shift && !empty($shift['time_out'])) {
                    $shiftEnd = strtotime($datePart . ' ' . $shift['time_out']);
                    if ($shift['time_out'] < $shift['time_in']) $shiftEnd += 86400; 

                    if ($currentScanStr > $shiftEnd) {
                        $overtime = round(($currentScanStr - $shiftEnd) / 60);
                        $minOv = $shift['min_overtime'] ?? 0;
                        $deduct = $shift['overtime_deduction'] ?? 0;
                        
                        if ($overtime < $minOv) $overtime = 0;
                        if ($overtime > 0 && $deduct > 0) $overtime -= $deduct;
                        $updateData['overtime_minutes'] = $overtime;
                    } else {
                        $updateData['overtime_minutes'] = 0;
                    }
                }

                if (!empty($existingLog['time_in'])) {
                    $totalWorkSeconds = $currentScanStr - $timeInStr;
                    $bOut = $existingLog['break_out'] ? strtotime($existingLog['date'] . ' ' . $existingLog['break_out']) : 0;
                    $bIn = $existingLog['break_in'] ? strtotime($existingLog['date'] . ' ' . $existingLog['break_in']) : 0;
                    if ($bOut > 0 && $bIn > 0 && $bIn > $bOut) {
                        $totalWorkSeconds -= ($bIn - $bOut);
                    }
                    $updateData['work_duration_minutes'] = round($totalWorkSeconds / 60);
                }
            }

            if (!empty($updateData)) {
                $attModel->update($existingLog['id'], $updateData);
                return $this->respond(['status' => 'OK', 'message' => 'Status Updated by Machine Status']);
            }
            return $this->respond(['status' => 'OK', 'message' => 'Scan ignored']);
        }
    }

    private function processSetUserInfoStatus($payload) {
        $status = $payload['data']['status'] ?? null; 
        $transId = $payload['trans_id'] ?? 'unknown';
        if ($status == "1") log_message('info', "[Fingerspot] Karyawan sukses ditambahkan. Trans ID: {$transId}");
        elseif ($status == "2") log_message('error', "[Fingerspot] GAGAL mendaftarkan karyawan. Trans ID: {$transId}");
        return $this->respond(['status' => 'OK']);
    }

    private function processDeleteUserInfoStatus($payload) {
        $status = $payload['data']['status'] ?? null; 
        $transId = $payload['trans_id'] ?? 'unknown';
        if ($status == "1") log_message('info', "[Fingerspot] Akses mesin BERHASIL dicabut. Trans ID: {$transId}");
        elseif ($status == "2") log_message('warning', "[Fingerspot] GAGAL mencabut akses mesin. Trans ID: {$transId}");
        return $this->respond(['status' => 'OK']);
    }

    private function processGetAllPinStatus($payload)
    {
        $total = $payload['data']['total'] ?? 0;
        $pinArr = $payload['data']['pin_arr'] ?? [];

        ob_start();
        $responseJson = json_encode(['status' => 'OK', 'message' => 'Processing in background.']);
        header('Content-Type: application/json');
        header('Connection: close');
        header('Content-Length: ' . strlen($responseJson));
        echo $responseJson;
        ob_end_flush();
        ob_flush();
        flush();
        
        if (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        }

        ignore_user_abort(true);
        set_time_limit(0);

        if (is_array($pinArr) && count($pinArr) > 0) {
            $fingerspot = new \App\Libraries\Fingerspot();
            
            $empModel = new \App\Models\EmployeeModel();
            $existingData = $empModel->select('pin')->where('pin !=', null)->where('pin !=', '')->findAll();
            $existingPins = array_column($existingData, 'pin');
            
            $countNew = 0;

            foreach ($pinArr as $pin) {
                if (in_array($pin, $existingPins)) {
                    continue; 
                }

                $fingerspot->getUserInfo($pin);
                $countNew++;
                usleep(100000); 
            }

            log_message('info', "[Fingerspot] Berhasil menarik {$countNew} karyawan baru dari sisa total {$total} PIN.");
        }

        exit(); 
    }

    public function ping() 
    {
        return $this->respond([
            'status'  => 'OK', 
            'message' => 'Noric Webhook Endpoint is Ready. Waiting for POST request from IoT Machine.'
        ], 200);
    }
}