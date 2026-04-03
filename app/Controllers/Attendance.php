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
        $empModel = new EmployeeModel(); 
        
        $startDate = $this->request->getGet('start_date') ?? date('Y-m-d');
        $endDate   = $this->request->getGet('end_date') ?? date('Y-m-d');
        
        $attendances = $attModel->select('attendances.*, employees.name, employees.status as emp_status')
                                ->join('employees', 'employees.employee_id = attendances.employee_id', 'left')
                                ->where('attendances.date >=', $startDate)
                                ->where('attendances.date <=', $endDate)
                                ->orderBy('employees.name', 'ASC')
                                ->orderBy('attendances.date', 'ASC')
                                ->findAll();

        $data = [
            'title'       => 'Log Kehadiran',
            'startDate'   => $startDate,
            'endDate'     => $endDate,
            'attendances' => $attendances,
            'employees'   => $empModel->where('is_active', 1)->orderBy('name', 'ASC')->findAll() 
        ];

        return view('attendance/index', $data);
    }

    public function syncData()
    {
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'admin') return redirect()->to('/portal');

        $startDate = $this->request->getGet('start_date') ?? date('Y-m-d');
        $endDate   = $this->request->getGet('end_date') ?? date('Y-m-d');
        
        $fingerspot = new \App\Libraries\Fingerspot();
        $attModel = new AttendanceModel();
        $empModel = new EmployeeModel();
        $shiftModel = new WorkShiftModel();

        $response = $fingerspot->getAttlog($startDate, $endDate);

        if (isset($response['success']) && $response['success'] && !empty($response['data'])) {
            $syncedCount = 0;

            foreach ($response['data'] as $log) {
                $pin = $log['pin']; 
                $scanTime = $log['scan_date'] ?? $log['scan']; 
                
                $statusScan   = isset($log['status_scan']) ? (string)$log['status_scan'] : null;
                $photoUrl     = isset($log['photo_url']) ? (string)$log['photo_url'] : null; 
                $verifyMethod = isset($log['verify']) ? (string)$log['verify'] : null; 

                $datePart = date('Y-m-d', strtotime($scanTime));
                $timePart = date('H:i:s', strtotime($scanTime));

                $emp = $empModel->where('pin', $pin)->first();
                if (!$emp) continue; 

                $existingLog = $attModel->where(['employee_id' => $emp['employee_id'], 'date' => $datePart])->first();
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
                        'employee_id'   => $emp['employee_id'], 
                        'date'          => $datePart,
                        'status'        => $status,
                        'late_minutes'  => $lateMinutes,
                        'photo_url'     => $photoUrl,
                        'verify_method' => $verifyMethod
                    ];

                    if ($statusScan === "0") $insertData['time_in'] = $timePart;
                    elseif ($statusScan === "1") $insertData['time_out'] = $timePart;
                    elseif ($statusScan === "2") $insertData['break_in'] = $timePart;
                    elseif ($statusScan === "3") $insertData['break_out'] = $timePart;
                    else $insertData['time_in'] = $timePart;

                    $attModel->insert($insertData);
                    $syncedCount++;
                } 
                else {
                    $updateData = [];
                    if (!empty($photoUrl)) $updateData['photo_url'] = $photoUrl;
                    if (!empty($verifyMethod)) $updateData['verify_method'] = $verifyMethod; 
                    
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
                        
                        if ($shift) {
                            $actualIn  = strtotime($datePart . ' ' . $existingLog['time_in']);
                            $actualOut = strtotime($datePart . ' ' . $timePart);
                            $shiftEnd  = strtotime($datePart . ' ' . $shift['time_out']);
                            
                            if ($shift['time_out'] < $shift['time_in']) $shiftEnd += 86400; 
                            if ($actualOut < $actualIn) $actualOut += 86400;

                            $overtimeMinutes = 0;
                            if ($actualOut > $shiftEnd) {
                                $overtimeMinutes = round(($actualOut - $shiftEnd) / 60);
                                $minOv = $shift['min_overtime'] ?? 0;
                                $deduct = $shift['overtime_deduction'] ?? 0;
                                
                                if ($overtimeMinutes < $minOv) $overtimeMinutes = 0;
                                if ($overtimeMinutes > 0 && $deduct > 0) $overtimeMinutes -= $deduct;
                            }
                            $updateData['overtime_minutes'] = $overtimeMinutes;

                            $totalSeconds = $actualOut - $actualIn;
                            $bOut = $existingLog['break_out'] ? strtotime($datePart . ' ' . $existingLog['break_out']) : 0;
                            $bIn  = $existingLog['break_in'] ? strtotime($datePart . ' ' . $existingLog['break_in']) : 0;
                            if ($bOut > 0 && $bIn > 0 && $bIn > $bOut) {
                                $totalSeconds -= ($bIn - $bOut);
                            }
                            $updateData['work_duration_minutes'] = round($totalSeconds / 60);
                        }
                    }

                    if (!empty($updateData)) {
                        $attModel->update($existingLog['id'], $updateData);
                        $syncedCount++;
                    }
                }
            }
            return redirect()->back()->with('success', "Sinkronisasi selesai! Berhasil menarik & mengkalkulasi <b>{$syncedCount}</b> log data menggunakan status tombol mesin.");
        }
        return redirect()->back()->with('info', 'Tidak ada data baru di mesin untuk rentang tanggal tersebut, atau mesin sedang offline.');
    }

    public function syncMachineTime()
    {
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'admin') return redirect()->to('/portal');

        $fingerspot = new \App\Libraries\Fingerspot();
        $response = $fingerspot->setTime('Asia/Jakarta');

        if (isset($response['success']) && $response['success']) {
            return redirect()->back()->with('success', 'Perintah sinkronisasi waktu berhasil dikirim! Jam mesin disesuaikan dengan WIB.');
        } else {
            return redirect()->back()->with('error', 'Gagal mengirim perintah. Pastikan mesin online.');
        }
    }

    public function manual() { 
        if (!session()->get('isLoggedIn') || (session()->get('role') !== 'admin' && session()->get('department') !== 'Manajemen & HRD')) return redirect()->to('/portal');
        $empModel = new EmployeeModel();
        return view('attendance/manual', ['title' => 'Koreksi Absensi Manual', 'employees' => $empModel->where('is_active', 1)->orderBy('name', 'ASC')->findAll()]);
    }

    public function store_manual()
    {
        if (!session()->get('isLoggedIn') || (session()->get('role') !== 'admin' && session()->get('department') !== 'Manajemen & HRD')) return redirect()->to('/portal');

        $attModel   = new AttendanceModel();
        $empModel   = new EmployeeModel();
        $shiftModel = new WorkShiftModel();

        $employee_id = $this->request->getPost('employee_id');
        $date        = $this->request->getPost('date');
        $status      = $this->request->getPost('status'); 

        $emp = $empModel->where('employee_id', $employee_id)->first();
        if (!$emp) return redirect()->back()->with('error', 'Karyawan tidak valid.');

        $shift = $shiftModel->find($emp['shift_id']);
        $existingLog = $attModel->where(['employee_id' => $employee_id, 'date' => $date])->first();

        $time_in   = $this->request->getPost('time_in')   ?: ($existingLog ? $existingLog['time_in'] : null);
        $time_out  = $this->request->getPost('time_out')  ?: ($existingLog ? $existingLog['time_out'] : null);
        $break_out = $this->request->getPost('break_out') ?: ($existingLog ? $existingLog['break_out'] : null);
        $break_in  = $this->request->getPost('break_in')  ?: ($existingLog ? $existingLog['break_in'] : null);

        $lateMinutes = 0;
        $overtimeMinutes = 0;
        $workDurationMinutes = 0;

        if (in_array($status, ['Hadir', 'Terlambat']) && $shift && !empty($time_in)) {
            
            $shiftStart = strtotime($date . ' ' . $shift['time_in']);
            $actualIn   = strtotime($date . ' ' . $time_in);
            
            if ($actualIn > ($shiftStart + (($shift['late_tolerance'] ?? 0) * 60))) {
                $status = 'Terlambat';
                $lateMinutes = round(($actualIn - $shiftStart) / 60);
            } else {
                $status = 'Hadir'; 
            }

            if (!empty($time_out)) {
                $shiftEnd = strtotime($date . ' ' . $shift['time_out']);
                $actualOut = strtotime($date . ' ' . $time_out);
                
                if ($shift['time_out'] < $shift['time_in']) $shiftEnd += 86400;
                if ($actualOut < $actualIn) $actualOut += 86400;

                if ($actualOut > $shiftEnd) {
                    $overtimeMinutes = round(($actualOut - $shiftEnd) / 60);
                    $minOv = $shift['min_overtime'] ?? 0;
                    $deduct = $shift['overtime_deduction'] ?? 0;
                    
                    if ($overtimeMinutes < $minOv) $overtimeMinutes = 0;
                    if ($overtimeMinutes > 0 && $deduct > 0) $overtimeMinutes -= $deduct;
                }

                $totalSeconds = $actualOut - $actualIn;
                if (!empty($break_out) && !empty($break_in)) {
                    $bOut = strtotime($date . ' ' . $break_out);
                    $bIn  = strtotime($date . ' ' . $break_in);
                    if ($bIn > $bOut) $totalSeconds -= ($bIn - $bOut);
                }
                $workDurationMinutes = round($totalSeconds / 60);
            }
        }

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

        if ($existingLog) {
            $attModel->update($existingLog['id'], $dataToSave);
            $msg = 'Data absensi berhasil dikoreksi dan dikalkulasi ulang.';
        } else {
            $attModel->insert($dataToSave);
            $msg = 'Data absensi manual berhasil ditambahkan.';
        }

        return redirect()->to('/attendance')->with('success', $msg);
    }

    public function delete($id) { 
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'admin') return redirect()->to('/portal');
        $attModel = new AttendanceModel(); $attModel->delete($id);
        return redirect()->back()->with('success', 'Catatan absensi berhasil dihapus.');
    }
    
    public function get_existing_log()
    {
        $employee_id = $this->request->getGet('employee_id');
        $date = $this->request->getGet('date');

        $attModel = new AttendanceModel();
        $log = $attModel->where(['employee_id' => $employee_id, 'date' => $date])->first();

        if ($log) {
            return $this->response->setJSON([
                'success' => true,
                'data' => [
                    'time_in'   => $log['time_in'] ? date('H:i', strtotime($log['time_in'])) : '',
                    'time_out'  => $log['time_out'] ? date('H:i', strtotime($log['time_out'])) : '',
                    'break_out' => $log['break_out'] ? date('H:i', strtotime($log['break_out'])) : '',
                    'break_in'  => $log['break_in'] ? date('H:i', strtotime($log['break_in'])) : '',
                    'status'    => $log['status']
                ]
            ]);
        }
        return $this->response->setJSON(['success' => false]);
    }
    
    // ====================================================================
    // FUNGSI: TANDAI UANG MAKAN & OTOMATIS POTONG KAS LACI (ERP LINKED)
    // ====================================================================
    public function toggle_meal($id)
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/portal');

        $attModel = new \App\Models\AttendanceModel();
        $empModel = new \App\Models\EmployeeModel();
        $cashModel = new \App\Models\OperationalCashModel();
        $db = \Config\Database::connect();

        $log = $attModel->find($id);
        if (!$log) return redirect()->back()->with('error', 'Log absensi tidak ditemukan.');

        $emp = $empModel->where('employee_id', $log['employee_id'])->first();
        if (!$emp) return redirect()->back()->with('error', 'Data karyawan tidak ditemukan.');

        $mealRate = (float) $emp['meal_allowance'];
        if ($mealRate <= 0) {
            return redirect()->back()->with('error', 'Karyawan ini tidak memiliki jatah uang makan di master data (Rp 0).');
        }

        $akunKas = $db->table('chart_of_accounts')->where('account_code', '1-1000')->get()->getRowArray();
        $akunBeban = $db->table('chart_of_accounts')->where('account_code', '5-2000')->get()->getRowArray(); 

        if (!$akunKas || !$akunBeban) {
            return redirect()->back()->with('error', 'Gagal: Akun Kas (1-1000) atau Beban (5-2000) tidak ditemukan di Buku Besar Akuntansi.');
        }

        $db->transBegin();
        try {
            $newStatus = ($log['is_meal_taken'] == 1) ? 0 : 1;
            
            $attModel->update($id, ['is_meal_taken' => $newStatus]);

            $absenDate = $log['date']; 
            $dateCode  = date('Ymd', strtotime($absenDate));

            $lastTrx = $cashModel->like('transaction_code', "TRX-$dateCode-", 'after')->orderBy('id', 'DESC')->first();
            $newNumber = $lastTrx ? str_pad((int) substr($lastTrx['transaction_code'], -3) + 1, 3, '0', STR_PAD_LEFT) : '001';
            $trxCode = "TRX-$dateCode-$newNumber";

            // Fallback nama admin, cegah error jika session expired tiba-tiba
            $picName = session()->get('name') ?? 'Sistem Otomatis';

            // =========================================================
            // JIKA UANG MAKAN DIAMBIL
            // =========================================================
            if ($newStatus == 1) {
                // 1. Catat Jurnal
                $db->table('journals')->insert([
                    'journal_number'   => 'JRN-MKN-'.time(),
                    'transaction_date' => $absenDate,
                    'description'      => "Uang Makan: " . $emp['name'] . " (Tgl: " . date('d/m/Y', strtotime($absenDate)) . ")",
                    'total_amount'     => $mealRate,
                    'status'           => 'POSTED',
                    'created_by'       => 'Sistem Otomatis (HRD)'
                ]);
                $jrnId = $db->insertID();

                $db->table('journal_items')->insert(['journal_id' => $jrnId, 'account_id' => $akunBeban['id'], 'line_description' => 'Beban Makan', 'debit' => $mealRate, 'credit' => 0]);
                $db->table('journal_items')->insert(['journal_id' => $jrnId, 'account_id' => $akunKas['id'], 'line_description' => 'Kas Keluar', 'debit' => 0, 'credit' => $mealRate]);
                
                // 2. Catat Kas Operasional
                $cashModel->insert([
                    'transaction_code' => $trxCode, 'transaction_date' => $absenDate, 'type' => 'Cash Out', 'metode' => 'Cash',
                    'category' => 'Uang Makan', 'amount' => $mealRate, 'description' => "Uang Makan: " . $emp['name'] . " (Tgl Absen: " . date('d/m/Y', strtotime($absenDate)) . ")",
                    'pic_name' => $picName, 'journal_id' => $jrnId, 'status' => 'POSTED'
                ]);
                
                $msg = "Berhasil! Uang makan Rp " . number_format($mealRate, 0, ',', '.') . " terpotong otomatis dari Kas Laci.";
            } 
            // =========================================================
            // JIKA UANG MAKAN DIBATALKAN
            // =========================================================
            else {
                $db->table('journals')->insert([
                    'journal_number'   => 'JRN-RTN-'.time(),
                    'transaction_date' => $absenDate,
                    'description'      => "Batal Uang Makan: " . $emp['name'] . " (Tgl: " . date('d/m/Y', strtotime($absenDate)) . ")",
                    'total_amount'     => $mealRate,
                    'status'           => 'POSTED',
                    'created_by'       => 'Sistem Otomatis (HRD)'
                ]);
                $jrnId = $db->insertID();

                $db->table('journal_items')->insert(['journal_id' => $jrnId, 'account_id' => $akunKas['id'], 'line_description' => 'Kas Masuk', 'debit' => $mealRate, 'credit' => 0]);
                $db->table('journal_items')->insert(['journal_id' => $jrnId, 'account_id' => $akunBeban['id'], 'line_description' => 'Koreksi Beban', 'debit' => 0, 'credit' => $mealRate]);
                
                $cashModel->insert([
                    'transaction_code' => $trxCode, 'transaction_date' => $absenDate, 'type' => 'Cash In', 'metode' => 'Cash',
                    'category' => 'Pembatalan Kasbon Makan', 'amount' => $mealRate, 'description' => "Batal Uang Makan: " . $emp['name'],
                    'pic_name' => $picName, 'journal_id' => $jrnId, 'status' => 'POSTED'
                ]);

                $msg = "Dibatalkan! Uang Rp " . number_format($mealRate, 0, ',', '.') . " telah dikembalikan ke Saldo Kas.";
            }

            if ($db->transStatus() === false) {
                $errorMsg = $db->error();
                throw new \Exception("DB Error: " . ($errorMsg['message'] ?? 'Tidak diketahui'));
            }
            
            $db->transCommit();
            return redirect()->back()->with('success', $msg);

        } catch (\Exception $e) {
            $db->transRollback();
            return redirect()->back()->with('error', 'Gagal memproses kasbon makan: ' . $e->getMessage());
        }
    }
    
    // ====================================================================
    // FUNGSI: INPUT KASBON CEPAT (BORONGAN)
    // ====================================================================
    public function store_quick_kasbon()
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/portal');

        $empId   = $this->request->getPost('employee_id');
        $date    = $this->request->getPost('date');
        $amount  = (float) $this->request->getPost('amount');
        
        if ($amount <= 0) return redirect()->back()->with('error', 'Nominal kasbon harus lebih dari 0.');

        $empModel = new \App\Models\EmployeeModel();
        $cashModel = new \App\Models\OperationalCashModel();
        $db = \Config\Database::connect();

        $emp = $empModel->where('employee_id', $empId)->first();
        if (!$emp) return redirect()->back()->with('error', 'Karyawan tidak ditemukan.');

        $akunKas = $db->table('chart_of_accounts')->where('account_code', '1-1000')->get()->getRowArray();
        $akunPiutang = $db->table('chart_of_accounts')->where('account_code', '1-4000')->get()->getRowArray(); 

        if (!$akunKas || !$akunPiutang) {
            return redirect()->back()->with('error', 'Gagal: Akun Kas (1-1000) atau Piutang (1-4000) tidak ditemukan di Buku Besar Akuntansi.');
        }

        $picName = session()->get('name') ?? 'Sistem Otomatis';

        $db->transBegin();
        try {
            // 1. Simpan ke Tabel Kasbon
            $db->table('cash_advances')->insert([
                'employee_id' => $empId, 'date' => $date, 'amount' => $amount,
                'tempo_date'  => date('Y-m-t', strtotime($date)),
                'description' => "Kasbon Harian (Borongan) via Menu Absensi",
                'status'      => 'Belum Lunas',
                'created_at'  => date('Y-m-d H:i:s'), 'updated_at'  => date('Y-m-d H:i:s')
            ]);

            $dateCode  = date('Ymd', strtotime($date));
            $lastTrx = $cashModel->like('transaction_code', "TRX-$dateCode-", 'after')->orderBy('id', 'DESC')->first();
            $newNumber = $lastTrx ? str_pad((int) substr($lastTrx['transaction_code'], -3) + 1, 3, '0', STR_PAD_LEFT) : '001';
            $trxCode = "TRX-$dateCode-$newNumber";

            // 2. Jurnal & Kas
            $db->table('journals')->insert([
                'journal_number'   => 'JRN-KSB-'.time(), 'transaction_date' => $date,
                'description'      => "Kasbon Borongan: " . $emp['name'] . " (Tgl: " . date('d/m/Y', strtotime($date)) . ")",
                'total_amount'     => $amount, 'status' => 'POSTED', 'created_by' => $picName
            ]);
            $jrnId = $db->insertID();

            $db->table('journal_items')->insert(['journal_id' => $jrnId, 'account_id' => $akunPiutang['id'], 'line_description' => 'Piutang Karyawan', 'debit' => $amount, 'credit' => 0]);
            $db->table('journal_items')->insert(['journal_id' => $jrnId, 'account_id' => $akunKas['id'], 'line_description' => 'Kas Keluar', 'debit' => 0, 'credit' => $amount]);
            
            $cashModel->insert([
                'transaction_code' => $trxCode, 'transaction_date' => $date, 'type' => 'Cash Out', 'metode' => 'Cash',
                'category' => 'Kasbon Karyawan', 'amount' => $amount, 'description' => "Kasbon Borongan: " . $emp['name'],
                'pic_name' => $picName, 'journal_id' => $jrnId, 'status' => 'POSTED'
            ]);

            if ($db->transStatus() === false) {
                $errorMsg = $db->error();
                throw new \Exception("DB Error: " . ($errorMsg['message'] ?? 'Tidak diketahui'));
            }
            
            $db->transCommit();

            return redirect()->back()->with('success', "Kasbon Rp " . number_format($amount, 0, ',', '.') . " untuk " . $emp['name'] . " dicatat & memotong Kas.");

        } catch (\Exception $e) {
            $db->transRollback();
            return redirect()->back()->with('error', 'Gagal memproses kasbon: ' . $e->getMessage());
        }
    }
}