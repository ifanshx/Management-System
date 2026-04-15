<?php

namespace App\Controllers;
use App\Models\AttendanceModel;
use App\Models\EmployeeModel;
use App\Models\WorkShiftModel;
use App\Services\AccountingService;

class Attendance extends BaseController
{
    public function __construct()
    {
        $db = \Config\Database::connect();
        try { $db->query("ALTER TABLE attendances ADD COLUMN overtime_meal_amount DECIMAL(15,2) DEFAULT 0 AFTER is_overtime_taken"); } catch (\Exception $e) {}
    }

   public function index()
    {
        if (!session()->get('isLoggedIn') || (session()->get('role') !== 'admin' && session()->get('department') !== 'Manajemen & HRD')) return redirect()->to('/portal');

        $attModel = new AttendanceModel();
        $empModel = new EmployeeModel(); 
        
        $startDate = $this->request->getGet('start_date') ?? date('Y-m-d');
        $endDate   = $this->request->getGet('end_date') ?? date('Y-m-d');
        
        $attendances = $attModel->select('attendances.*, employees.name, employees.status as emp_status, departments.name as department_name')
                                ->join('employees', 'employees.employee_id = attendances.employee_id', 'left')
                                ->join('departments', 'departments.id = employees.department_id', 'left')
                                ->where('attendances.date >=', $startDate)
                                ->where('attendances.date <=', $endDate)
                                ->orderBy('employees.name', 'ASC')
                                ->orderBy('attendances.date', 'ASC')
                                ->findAll();

        return view('attendance/index', [
            'title'       => 'Log Kehadiran',
            'startDate'   => $startDate,
            'endDate'     => $endDate,
            'attendances' => $attendances,
            'employees'   => $empModel->where('is_active', 1)->orderBy('name', 'ASC')->findAll() 
        ]);
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
                
                $datePart = date('Y-m-d', strtotime($scanTime));
                $timePart = date('H:i:s', strtotime($scanTime));

                $emp = $empModel->where('pin', $pin)->first();
                if (!$emp) continue; 

                $existingLog = $attModel->where(['employee_id' => $emp['employee_id'], 'date' => $datePart])->first();
                $shift = $shiftModel->find($emp['shift_id']);

                if (!$existingLog) {
                    $status = 'Hadir'; $lateMinutes = 0;
                    if ($shift && !empty($shift['time_in']) && ($statusScan === "0" || $statusScan === null)) {
                        $shiftStart = strtotime($datePart . ' ' . $shift['time_in']);
                        $actualIn = strtotime($scanTime);
                        if ($actualIn > ($shiftStart + (($shift['late_tolerance'] ?? 0) * 60))) {
                            $status = 'Terlambat'; $lateMinutes = round(($actualIn - $shiftStart) / 60);
                        }
                    }

                    $insertData = [
                        'employee_id' => $emp['employee_id'], 'date' => $datePart, 'status' => $status,
                        'late_minutes' => $lateMinutes, 'photo_url' => $log['photo_url'] ?? null, 'verify_method' => $log['verify'] ?? null
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
                    if (!empty($log['photo_url'])) $updateData['photo_url'] = $log['photo_url'];
                    if (!empty($log['verify'])) $updateData['verify_method'] = $log['verify']; 
                    
                    if ($statusScan === "0" && empty($existingLog['time_in'])) $updateData['time_in'] = $timePart;
                    elseif ($statusScan === "2") $updateData['break_in'] = $timePart;
                    elseif ($statusScan === "3") $updateData['break_out'] = $timePart;
                    elseif ($statusScan === "1") {
                        $updateData['time_out'] = $timePart;
                        if ($shift) {
                            $actualIn  = strtotime($datePart . ' ' . $existingLog['time_in']);
                            $actualOut = strtotime($datePart . ' ' . $timePart);
                            $shiftEnd  = strtotime($datePart . ' ' . $shift['time_out']);
                            $shiftStart = strtotime($datePart . ' ' . $shift['time_in']);
                            
                            if ($shift['time_out'] < $shift['time_in']) $shiftEnd += 86400; 
                            if ($actualOut < $actualIn) $actualOut += 86400;

                            // Hitung ulang lateMinutes dari data pagi (jika ada) untuk memotong lembur
                            $lateMinutes = 0;
                            if ($actualIn > ($shiftStart + (($shift['late_tolerance'] ?? 0) * 60))) {
                                $lateMinutes = round(($actualIn - $shiftStart) / 60);
                            }

                            $overtimeMinutes = 0;
                            if ($actualOut > $shiftEnd) {
                                // Menghitung total waktu kelebihan di sore hari
                                $rawOvertime = round(($actualOut - $shiftEnd) / 60);
                                
                                // LOGIKA BARU: Potong lembur dengan waktu keterlambatan pagi (Mengepaskan Jam)
                                $netOvertime = $rawOvertime - $lateMinutes;

                                if ($netOvertime > 0) {
                                    $minOv = $shift['min_overtime'] ?? 0; 
                                    $deduct = $shift['overtime_deduction'] ?? 0;
                                    
                                    // Cek apakah lembur bersih masih memenuhi syarat minimal lembur
                                    if ($netOvertime >= $minOv) {
                                        $overtimeMinutes = $netOvertime;
                                        if ($deduct > 0) $overtimeMinutes -= $deduct;
                                    }
                                }
                            }
                            $updateData['overtime_minutes'] = $overtimeMinutes;

                            $totalSeconds = $actualOut - $actualIn;
                            $bOut = $existingLog['break_out'] ? strtotime($datePart . ' ' . $existingLog['break_out']) : 0;
                            $bIn  = $existingLog['break_in'] ? strtotime($datePart . ' ' . $existingLog['break_in']) : 0;
                            if ($bOut > 0 && $bIn > 0 && $bIn > $bOut) $totalSeconds -= ($bIn - $bOut);
                            
                            $updateData['work_duration_minutes'] = round($totalSeconds / 60);
                        }
                    }

                    if (!empty($updateData)) { $attModel->update($existingLog['id'], $updateData); $syncedCount++; }
                }
            }
            return redirect()->back()->with('success', "Sinkronisasi selesai! Berhasil menarik & mengkalkulasi <b>{$syncedCount}</b> log data.");
        }
        return redirect()->back()->with('info', 'Tidak ada data baru di mesin untuk rentang tanggal tersebut.');
    }

    public function syncMachineTime()
    {
        $fingerspot = new \App\Libraries\Fingerspot();
        $response = $fingerspot->setTime('Asia/Jakarta');
        if (isset($response['success']) && $response['success']) return redirect()->back()->with('success', 'Jam mesin disesuaikan dengan WIB.');
        return redirect()->back()->with('error', 'Gagal mengirim perintah. Pastikan mesin online.');
    }

    public function manual() 
    { 
        $db = \Config\Database::connect();
        
        $employees = $db->table('employees')
            ->select('employees.*, positions.name as position')
            ->join('positions', 'positions.id = employees.position_id', 'left')
            ->where('employees.is_active', 1)
            ->orderBy('employees.name', 'ASC')
            ->get()->getResultArray();

        return view('attendance/manual', [
            'title'     => 'Koreksi Absensi Manual', 
            'employees' => $employees
        ]);
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
                    $rawOvertime = round(($actualOut - $shiftEnd) / 60);
                    
                    // LOGIKA BARU: Mengepaskan Jam (Potong Lembur Manual dengan Telat)
                    $netOvertime = $rawOvertime - $lateMinutes;

                    if ($netOvertime > 0) {
                        $minOv = $shift['min_overtime'] ?? 0;
                        $deduct = $shift['overtime_deduction'] ?? 0;
                        
                        if ($netOvertime >= $minOv) {
                            $overtimeMinutes = $netOvertime;
                            if ($deduct > 0) $overtimeMinutes -= $deduct;
                        }
                    }
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
    
    public function toggle_meal($id)
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/portal');

        $attModel = new \App\Models\AttendanceModel();
        $empModel = new \App\Models\EmployeeModel();
        $cashModel = new \App\Models\OperationalCashModel();
        $db = \Config\Database::connect();

        $log = $attModel->find($id);
        $emp = $empModel->where('employee_id', $log['employee_id'])->first();

        $mealRate = (float) $emp['meal_allowance'];
        if ($mealRate <= 0) return redirect()->back()->with('error', 'Karyawan ini tidak memiliki jatah uang makan (Rp 0).');

        $akunKas = $db->table('chart_of_accounts')->where('account_code', '1-1000')->get()->getRowArray();
        $akunBeban = $db->table('chart_of_accounts')->where('account_code', '5-2000')->get()->getRowArray(); 

        $db->transBegin();
        try {
            $newStatus = ($log['is_meal_taken'] == 1) ? 0 : 1;
            $attModel->update($id, ['is_meal_taken' => $newStatus]);

            $absenDate = $log['date']; 
            $dateCode  = date('Ymd', strtotime($absenDate));

            $lastTrx = $cashModel->like('transaction_code', "TRX-$dateCode-", 'after')->orderBy('id', 'DESC')->get()->getRowArray();
            $newNumber = $lastTrx ? (int) explode('-', $lastTrx['transaction_code'])[2] + 1 : 1;
            $trxCode = "TRX-$dateCode-" . str_pad($newNumber, 3, '0', STR_PAD_LEFT);
            $picName = session()->get('name') ?? 'Sistem Otomatis';

            $accService = new AccountingService();

            if ($newStatus == 1) {
                // KAS KELUAR (AMBIL MAKAN)
                $journalItems = [
                    ['account_id' => $akunBeban['id'], 'debit' => $mealRate, 'credit' => 0, 'memo' => 'Beban Makan'],
                    ['account_id' => $akunKas['id'], 'debit' => 0, 'credit' => $mealRate, 'memo' => 'Kas Keluar']
                ];
                $jrnId = $accService->createJournal($absenDate, "Uang Makan: " . $emp['name'], 'ATTENDANCE', "MKN-{$emp['employee_id']}-{$dateCode}", $mealRate, $journalItems, 'Sistem HRD', $id);
                
                $cashModel->insert(['transaction_code' => $trxCode, 'transaction_date' => $absenDate, 'type' => 'Cash Out', 'metode' => 'Cash', 'category' => 'Uang Makan', 'amount' => $mealRate, 'description' => "Uang Makan: " . $emp['name'] . " (Tgl Absen: ".date('d/m/Y', strtotime($absenDate)).")", 'pic_name' => $picName, 'journal_id' => $jrnId, 'status' => 'POSTED']);
                $msg = "Uang makan Rp " . number_format($mealRate, 0, ',', '.') . " terpotong dari Kas Laci.";
            } else {
                // KAS MASUK (BATAL AMBIL MAKAN - REVERSAL)
                $journalItems = [
                    ['account_id' => $akunKas['id'], 'debit' => $mealRate, 'credit' => 0, 'memo' => 'Kas Masuk'],
                    ['account_id' => $akunBeban['id'], 'debit' => 0, 'credit' => $mealRate, 'memo' => 'Koreksi Beban']
                ];
                $jrnId = $accService->createJournal($absenDate, "Batal Uang Makan: " . $emp['name'], 'ATTENDANCE', "RTN-{$emp['employee_id']}-{$dateCode}", $mealRate, $journalItems, 'Sistem HRD', $id);
                
                $cashModel->insert(['transaction_code' => $trxCode, 'transaction_date' => $absenDate, 'type' => 'Cash In', 'metode' => 'Cash', 'category' => 'Pembatalan Kasbon', 'amount' => $mealRate, 'description' => "Batal Uang Makan: " . $emp['name'] . " (Tgl Absen: ".date('d/m/Y', strtotime($absenDate)).")", 'pic_name' => $picName, 'journal_id' => $jrnId, 'status' => 'POSTED']);
                $msg = "Dibatalkan! Saldo Kas Laci telah dikembalikan.";
            }

            if ($db->transStatus() === false) throw new \Exception("DB Error");
            $db->transCommit();
            return redirect()->back()->with('success', $msg);

        } catch (\Exception $e) {
            $db->transRollback();
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function toggle_overtime_meal()
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/portal');

        $logId = $this->request->getPost('log_id');
        $amount = (float) str_replace(['Rp', '.', ' '], '', $this->request->getPost('amount') ?? '0');
        
        $attModel = new \App\Models\AttendanceModel();
        $empModel = new \App\Models\EmployeeModel();
        $cashModel = new \App\Models\OperationalCashModel();
        $db = \Config\Database::connect();

        $log = $attModel->find($logId);
        $emp = $empModel->where('employee_id', $log['employee_id'])->first();
        $isBorong = (stripos($emp['status'], 'Borong') !== false);

        $db->transBegin();
        try {
            $absenDate = $log['date']; 
            $dateCode  = date('Ymd', strtotime($absenDate));
            $picName = session()->get('name') ?? 'Sistem Otomatis';

            $akunKas = $db->table('chart_of_accounts')->where('account_code', '1-1000')->get()->getRowArray();
            $akunPiutang = $db->table('chart_of_accounts')->where('account_code', '1-4001')->get()->getRowArray();
            $akunBeban = $db->table('chart_of_accounts')->where('account_code', '5-2000')->get()->getRowArray();

            $accService = new AccountingService();

            if ($log['is_overtime_taken'] == 1) {
                $savedAmount = $log['overtime_meal_amount'];

                if ($isBorong) {
                    $kb = $db->table('cash_advances')->where('employee_id', $emp['employee_id'])->where('date', $absenDate)->like('description', 'Kasbon Lembur')->get()->getRowArray();
                    if ($kb) {
                        $db->table('cash_advances')->where('id', $kb['id'])->delete();
                    }
                    $descReverse = "Batal Lembur Borongan: " . $emp['name'];
                    $accKredit = $akunPiutang['id'];
                    $lineDesc = 'Koreksi Kasbon';
                } else {
                    $descReverse = "Batal Lembur Tetap: " . $emp['name'];
                    $accKredit = $akunBeban['id'];
                    $lineDesc = 'Koreksi Beban Lembur';
                }

                $lastTrx = $cashModel->like('transaction_code', "TRX-$dateCode-", 'after')->orderBy('id', 'DESC')->get()->getRowArray();
                $newNumber = $lastTrx ? (int) explode('-', $lastTrx['transaction_code'])[2] + 1 : 1;
                $trxCode = "TRX-$dateCode-" . str_pad($newNumber, 3, '0', STR_PAD_LEFT);

                $journalItems = [
                    ['account_id' => $akunKas['id'], 'debit' => $savedAmount, 'credit' => 0, 'memo' => 'Kas Masuk'],
                    ['account_id' => $accKredit, 'debit' => 0, 'credit' => $savedAmount, 'memo' => $lineDesc]
                ];
                $jrnId = $accService->createJournal($absenDate, $descReverse, 'ATTENDANCE', "RTNOT-{$emp['employee_id']}-{$dateCode}", $savedAmount, $journalItems, $picName, $logId);
                
                $cashModel->insert(['transaction_code' => $trxCode, 'transaction_date' => $absenDate, 'type' => 'Cash In', 'metode' => 'Cash', 'category' => 'Pembatalan Lembur', 'amount' => $savedAmount, 'description' => $descReverse, 'pic_name' => $picName, 'journal_id' => $jrnId, 'status' => 'POSTED']);
                
                $attModel->update($logId, ['is_overtime_taken' => 0, 'overtime_meal_amount' => 0]);
                $msg = "Pencatatan Uang Lembur dibatalkan dan Kas Laci telah dikembalikan.";
                
            } else {
                if ($amount <= 0) throw new \Exception("Nominal harus diisi lebih dari 0.");
                
                $saldoKas = $db->query("SELECT calculated_balance FROM v_account_balances WHERE id = ?", [$akunKas['id']])->getRowArray()['calculated_balance'] ?? 0;
                if ($saldoKas < $amount) throw new \Exception("Saldo Kas Laci tidak cukup (Sisa: Rp " . number_format($saldoKas, 0, ',', '.') . ")");

                $lastTrx = $cashModel->like('transaction_code', "TRX-$dateCode-", 'after')->orderBy('id', 'DESC')->get()->getRowArray();
                $newNumber = $lastTrx ? (int) explode('-', $lastTrx['transaction_code'])[2] + 1 : 1;
                $trxCode = "TRX-$dateCode-" . str_pad($newNumber, 3, '0', STR_PAD_LEFT);

                if ($isBorong) {
                    $db->table('cash_advances')->insert([
                        'employee_id' => $emp['employee_id'], 'date' => $absenDate, 'amount' => $amount,
                        'tempo_date'  => date('Y-m-t', strtotime($absenDate)),
                        'description' => "Kasbon Lembur: " . $emp['name'],
                        'status'      => 'Belum Lunas', 'created_at'  => date('Y-m-d H:i:s'), 'updated_at'  => date('Y-m-d H:i:s')
                    ]);
                    $accDebit = $akunPiutang['id'];
                    $descLine = 'Piutang Karyawan';
                    $descJurnal = "Kasbon Lembur: " . $emp['name'];
                    $msg = "Pekerja Borongan: Uang Lembur Rp " . number_format($amount, 0, ',', '.') . " memotong Kas Laci & dicatat sebagai Kasbon.";
                } else {
                    $accDebit = $akunBeban['id'];
                    $descLine = 'Beban Lembur';
                    $descJurnal = "Uang Lembur (Tetap): " . $emp['name'];
                    $msg = "Pekerja Tetap: Uang Lembur Rp " . number_format($amount, 0, ',', '.') . " memotong Kas Laci saat ini (Akan tercatat sbg Info di Slip Gaji).";
                }

                $journalItems = [
                    ['account_id' => $accDebit, 'debit' => $amount, 'credit' => 0, 'memo' => $descLine],
                    ['account_id' => $akunKas['id'], 'debit' => 0, 'credit' => $amount, 'memo' => 'Kas Keluar']
                ];
                $jrnId = $accService->createJournal($absenDate, $descJurnal, 'ATTENDANCE', "OT-{$emp['employee_id']}-{$dateCode}", $amount, $journalItems, $picName, $logId);
                
                $cashModel->insert(['transaction_code' => $trxCode, 'transaction_date' => $absenDate, 'type' => 'Cash Out', 'metode' => 'Cash', 'category' => 'Uang Lembur', 'amount' => $amount, 'description' => "Cair Lembur: " . $emp['name'], 'pic_name' => $picName, 'journal_id' => $jrnId, 'status' => 'POSTED']);
                
                $attModel->update($logId, ['is_overtime_taken' => 1, 'overtime_meal_amount' => $amount]);
            }

            if ($db->transStatus() === false) throw new \Exception("Database Error saat memproses.");
            $db->transCommit();
            return redirect()->back()->with('success', $msg);

        } catch (\Exception $e) {
            $db->transRollback();
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
    
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
        $akunPiutang = $db->table('chart_of_accounts')->where('account_code', '1-4001')->get()->getRowArray(); 

        if (!$akunKas || !$akunPiutang) {
            return redirect()->back()->with('error', 'Gagal: Akun Kas (1-1000) atau Piutang Karyawan (1-4001) tidak ditemukan di Buku Besar Akuntansi.');
        }

        $picName = session()->get('name') ?? 'Sistem Otomatis';

        $db->transBegin();
        try {
            $db->table('cash_advances')->insert([
                'employee_id' => $empId, 'date' => $date, 'amount' => $amount,
                'tempo_date'  => date('Y-m-t', strtotime($date)),
                'description' => "Kasbon Harian (Borongan) via Menu Absensi",
                'status'      => 'Belum Lunas',
                'created_at'  => date('Y-m-d H:i:s'), 'updated_at'  => date('Y-m-d H:i:s')
            ]);

            $dateCode  = date('Ymd', strtotime($date));
            $lastTrx = $cashModel->like('transaction_code', "TRX-$dateCode-", 'after')->orderBy('id', 'DESC')->get()->getRowArray();
            $newNumber = $lastTrx ? (int) explode('-', $lastTrx['transaction_code'])[2] + 1 : 1;
            $trxCode = "TRX-$dateCode-" . str_pad($newNumber, 3, '0', STR_PAD_LEFT);

            $accService = new AccountingService();
            $journalItems = [
                ['account_id' => $akunPiutang['id'], 'debit' => $amount, 'credit' => 0, 'memo' => 'Piutang Karyawan'],
                ['account_id' => $akunKas['id'], 'debit' => 0, 'credit' => $amount, 'memo' => 'Kas Keluar']
            ];
            $jrnId = $accService->createJournal($date, "Kasbon Borongan: " . $emp['name'] . " (Tgl: " . date('d/m/Y', strtotime($date)) . ")", 'ATTENDANCE', "KSB-{$empId}-{$dateCode}", $amount, $journalItems, $picName);
            
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