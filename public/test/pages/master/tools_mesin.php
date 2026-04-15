<?php 
require_once '../../config/database.php';
require_once '../../config/fingerspot.php';  // Menggunakan file config fingerspot.php, bukan fingerspot_api.php
cek_login(); 

// PROTEKSI: Hanya Admin
if ($_SESSION['role'] !== 'admin') {
    header("Location: ../dashboard.php"); exit;
}

$msg = "";
$device_info = null;

// Fungsi untuk memanggil API Fingerspot yang lebih baik
function call_fingerspot_api($endpoint, $data = []) {
    $url = FINGERSPOT_API_URL . '/' . $endpoint;
    
    // Tambahkan timestamp jika belum ada
    if (!isset($data['trans_id'])) {
        $data['trans_id'] = (string)time();
    }
    
    // Pastikan cloud_id ada
    if (!isset($data['cloud_id'])) {
        $data['cloud_id'] = FINGERSPOT_CLOUD_ID;
    }
    
    $headers = [
        'Content-Type: application/json',
        'Authorization: Bearer ' . FINGERSPOT_API_TOKEN,
        'User-Agent: Noric-Management/1.0'
    ];

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($data, JSON_UNESCAPED_SLASHES),
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_SSL_VERIFYPEER => false, // Untuk development, set false
        CURLOPT_SSL_VERIFYHOST => 0,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_FAILONERROR => false
    ]);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error) {
        error_log("Fingerspot API Error ($endpoint): " . $error);
        return ['success' => false, 'message' => 'CURL Error: ' . $error];
    }

    $decoded = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log("JSON Decode Error: " . json_last_error_msg());
        return ['success' => false, 'message' => 'Invalid JSON response'];
    }

    return $decoded;
}

// --- 1. LOGIC: INPUT MANUAL ---
if (isset($_POST['input_manual'])) {
    $pin = mysqli_real_escape_string($conn, $_POST['pin_karyawan']);
    $scan_date = date('Y-m-d H:i:s', strtotime($_POST['tgl_waktu']));
    $status = (int)$_POST['status_scan'];
    
    // Cek apakah user ada
    $cek_user = mysqli_query($conn, "SELECT id FROM users WHERE pin='$pin'");
    if (mysqli_num_rows($cek_user) == 0) {
        $msg = "<div class='alert-custom warning'><i class='fa fa-exclamation-triangle'></i> PIN karyawan tidak ditemukan.</div>";
    } else {
        $cek = mysqli_query($conn, "SELECT id FROM absensi WHERE pin='$pin' AND scan_date='$scan_date'");
        if (mysqli_num_rows($cek) > 0) {
            $msg = "<div class='alert-custom warning'><i class='fa fa-exclamation-triangle'></i> Data absensi sudah ada.</div>";
        } else {
            $stmt = mysqli_prepare($conn, "INSERT INTO absensi (pin, scan_date, status_scan, verify_mode) VALUES (?, ?, ?, 1)");
            mysqli_stmt_bind_param($stmt, "ssi", $pin, $scan_date, $status);
            if (mysqli_stmt_execute($stmt)) {
                $msg = "<div class='alert-custom success'><i class='fa fa-check-circle'></i> Absensi manual tersimpan.</div>";
            } else {
                $msg = "<div class='alert-custom danger'>Gagal simpan database: " . mysqli_error($conn) . "</div>";
            }
            mysqli_stmt_close($stmt);
        }
    }
}

// --- 2. LOGIC: TARIK LOG (RECOVERY) ---
if (isset($_POST['tarik_log'])) {
    $tgl_awal = $_POST['tgl_awal'];
    $tgl_akhir = $_POST['tgl_akhir'];
    
    // Format tanggal untuk API
    $start_date = $tgl_awal . " 00:00:00";
    $end_date = $tgl_akhir . " 23:59:59";
    
    $data = [
        'trans_id' => (string)time(),
        'cloud_id' => FINGERSPOT_CLOUD_ID,
        'start_date' => $start_date,
        'end_date' => $end_date
    ];
    
    $result = call_fingerspot_api('get_attlog', $data);
    
    if (isset($result['success']) && $result['success'] === true && isset($result['data'])) {
        $logs = $result['data'];
        $count_success = 0;
        $count_skip = 0;
        
        // Siapkan Statement untuk Cek User (Optimasi Performance)
        $stmt_check_user = mysqli_prepare($conn, "SELECT pin FROM users WHERE pin = ? LIMIT 1");
        
        // Siapkan Statement untuk Cek Duplikat Absensi
        $stmt_check_dup = mysqli_prepare($conn, "SELECT id FROM absensi WHERE pin = ? AND scan_date = ? LIMIT 1");
        
        // Siapkan Statement Insert
        $stmt_insert = mysqli_prepare($conn, "INSERT INTO absensi (pin, scan_date, status_scan, verify_mode, photo_url) VALUES (?, ?, ?, ?, ?)");

        foreach ($logs as $log) {
            // 1. Sanitasi Data Dasar
            $pin = trim($log['pin']);
            $scan = date('Y-m-d H:i:s', strtotime($log['scan'])); // Format datetime mysql

            // 2. Mapping Data (API -> Database)
            // API kadang mereturn 'verify' atau 'ver', database butuh 'verify_mode'
            $verify_mode = isset($log['verify']) ? (int)$log['verify'] : 1; 
            
            // API kadang mereturn 'status' atau 'status_scan'
            $status_scan = isset($log['status']) ? (int)$log['status'] : (isset($log['status_scan']) ? (int)$log['status_scan'] : 0);
            
            // Cek URL Foto (API bisa 'img', 'image', atau 'photo_url')
            $photo_url = null;
            if (!empty($log['img']) && $log['img'] !== '-') {
                $photo_url = $log['img'];
            } elseif (!empty($log['photo_url']) && $log['photo_url'] !== '-') {
                $photo_url = $log['photo_url'];
            }

            // 3. LOGIC VALIDASI DATABASE
            
            // Cek apakah User/Karyawan ada di database (Wajib karena Foreign Key)
            mysqli_stmt_bind_param($stmt_check_user, "s", $pin);
            mysqli_stmt_execute($stmt_check_user);
            mysqli_stmt_store_result($stmt_check_user);
            
            if (mysqli_stmt_num_rows($stmt_check_user) > 0) {
                // User Ditemukan, Lanjut Cek Duplikat Absensi
                mysqli_stmt_bind_param($stmt_check_dup, "ss", $pin, $scan);
                mysqli_stmt_execute($stmt_check_dup);
                mysqli_stmt_store_result($stmt_check_dup);

                if (mysqli_stmt_num_rows($stmt_check_dup) == 0) {
                    // Belum ada, Lakukan Insert
                    mysqli_stmt_bind_param($stmt_insert, "ssiis", $pin, $scan, $status_scan, $verify_mode, $photo_url);
                    
                    if (mysqli_stmt_execute($stmt_insert)) {
                        $count_success++;
                    }
                } else {
                    $count_skip++; // Data sudah ada
                }
            } else {
                // User tidak ditemukan di tabel users, skip agar tidak error FK constraint
                // (Opsional: Anda bisa melog ini ke file text jika perlu debugging)
            }
        }
        
        // Tutup Statements
        mysqli_stmt_close($stmt_check_user);
        mysqli_stmt_close($stmt_check_dup);
        mysqli_stmt_close($stmt_insert);

        $msg = "<div class='alert-custom success'>
                    <i class='fa fa-check-circle'></i> 
                    <b>Selesai!</b> $count_success data baru disimpan. ($count_skip data duplikat dilewati).
                </div>";
    } else {
        $error_msg = $result['message'] ?? 'Tidak ada data log dari Cloud';
        $msg = "<div class='alert-custom warning'><i class='fa fa-exclamation-circle'></i> $error_msg</div>";
    }
}

// --- 3. LOGIC: GET USER INFO ---
if (isset($_POST['trigger_get_userinfo'])) {
    $result = call_fingerspot_api('get_userinfo', [
        'cloud_id' => FINGERSPOT_CLOUD_ID
    ]);
    
    if (isset($result['success']) && $result['success'] == true) {
        $msg = "<div class='alert-custom success'><i class='fa fa-paper-plane'></i> Perintah <b>Get Userinfo</b> terkirim. Tunggu data masuk.</div>";
    } else {
        $err = $result['message'] ?? 'Unknown Error';
        $msg = "<div class='alert-custom danger'>Gagal kirim perintah: $err</div>";
    }
}

// --- 4. LOGIC: GET DEVICE INFO ---
if (isset($_POST['get_device_info'])) {
    $result = call_fingerspot_api('get_device', [
        'cloud_id' => FINGERSPOT_CLOUD_ID
    ]);
    
    if (isset($result['success']) && $result['success'] == true && isset($result['data'])) {
        $device_info = $result['data'];
        $msg = "<div class='alert-custom success'><i class='fa fa-check-circle'></i> Data Perangkat Diterima.</div>";
    } else {
        $err = $result['message'] ?? 'Unknown Error';
        $msg = "<div class='alert-custom danger'>Gagal: $err</div>";
    }
}

// --- 5. LOGIC: RESTART DEVICE ---
if (isset($_POST['restart_mesin'])) {
    $result = call_fingerspot_api('restart_device', [
        'cloud_id' => FINGERSPOT_CLOUD_ID
    ]);
    
    if (isset($result['success']) && $result['success'] == true) {
        $msg = "<div class='alert-custom success'>Perintah Restart terkirim.</div>";
    } else {
        $msg = "<div class='alert-custom danger'>Gagal mengirim perintah restart.</div>";
    }
}

// --- 6. LOGIC: PUSH ALL USERS ---
if (isset($_POST['push_all_users'])) {
    // Ambil semua user dari database
    $query = mysqli_query($conn, "SELECT pin, fullname FROM users WHERE role IN ('user','kepala_bengkel')");
    $success = 0;
    $failed = 0;
    
    while ($row = mysqli_fetch_assoc($query)) {
        $data = [
            'cloud_id' => FINGERSPOT_CLOUD_ID,
            'data' => [
                'pin' => $row['pin'],
                'name' => substr(trim($row['fullname']), 0, 18),
                'privilege' => '1',
                'password' => '',
                'rfid' => '',
                'template' => ''
            ]
        ];
        
        $result = call_fingerspot_api('set_userinfo', $data);
        if (isset($result['success']) && $result['success'] == true) {
            $success++;
        } else {
            $failed++;
        }
    }
    
    $msg = "<div class='alert-custom info'>Perintah Push User selesai. Berhasil: $success, Gagal: $failed</div>";
}

// --- 7. LOGIC: SYNC TIME ---
if (isset($_POST['sync_time'])) {
    $result = call_fingerspot_api('set_time', [
        'cloud_id' => FINGERSPOT_CLOUD_ID,
        'timezone' => 'Asia/Jakarta'
    ]);
    
    if (isset($result['success']) && $result['success'] == true) {
        $msg = "<div class='alert-custom success'>Waktu mesin disinkronkan.</div>";
    } else {
        $msg = "<div class='alert-custom danger'>Gagal sinkron waktu.</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <?php include '../../layout/header.php'; ?>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body { background-color: #f8fafc; font-family: 'Plus Jakarta Sans', sans-serif; color:#334155; }
        .content-wrapper { padding: 30px; position: relative; }
        
        /* HEADER */
        .page-header h2 { font-weight: 800; color: #1e293b; margin: 0; font-size: 24px; letter-spacing: -0.5px; }
        .page-header p { color: #64748b; margin-top: 5px; font-size: 14px; }

        /* ALERTS */
        .alert-custom { padding: 15px 20px; border-radius: 12px; margin-bottom: 25px; font-size: 14px; display: flex; align-items: center; gap: 10px; font-weight: 500; animation: slideDown 0.3s ease-out; }
        .alert-custom.success { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
        .alert-custom.danger { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
        .alert-custom.warning { background: #fef3c7; color: #92400e; border: 1px solid #fde68a; }
        .alert-custom.info { background: #e0f2fe; color: #075985; border: 1px solid #bae6fd; }

        /* TABS NAVIGATION */
        .nav-tabs-wrapper { overflow-x: auto; white-space: nowrap; margin-bottom: 25px; padding-bottom: 5px; }
        .nav-tabs-custom { display: inline-flex; border-bottom: 2px solid #e2e8f0; width: 100%; min-width: max-content; }
        .nav-link-custom {
            padding: 12px 20px; font-weight: 600; color: #64748b; background: none; border: none; cursor: pointer;
            border-bottom: 3px solid transparent; transition: 0.3s; font-size: 14px; display: inline-flex; align-items: center; gap: 8px;
        }
        .nav-link-custom:hover { color: #4f46e5; background: rgba(79, 70, 229, 0.05); }
        .nav-link-custom.active { color: #4f46e5; border-bottom-color: #4f46e5; background: rgba(79, 70, 229, 0.05); }
        
        /* CARDS */
        .modern-card { background: #fff; border-radius: 16px; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02); overflow: hidden; margin-bottom: 25px; height: 100%; display: flex; flex-direction: column; }
        .card-header-gradient { padding: 20px 25px; color: #fff; display: flex; align-items: center; justify-content: space-between; }
        
        /* Gradient Variants */
        .bg-blue { background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); }
        .bg-green { background: linear-gradient(135deg, #10b981 0%, #059669 100%); }
        .bg-purple { background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%); }
        .bg-orange { background: linear-gradient(135deg, #f97316 0%, #ea580c 100%); }
        .bg-dark { background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); }
        .bg-red { background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); }
        
        .card-title { margin: 0; font-weight: 700; font-size: 16px; display: flex; align-items: center; gap: 10px; }
        .card-body { padding: 25px; flex: 1; }
        
        /* FORMS */
        .form-group { margin-bottom: 20px; }
        .form-label { font-size: 11px; font-weight: 700; text-transform: uppercase; color: #64748b; display: block; margin-bottom: 8px; letter-spacing: 0.5px; }
        .form-control-custom { width: 100%; height: 48px; padding: 0 15px; border-radius: 10px; border: 1px solid #cbd5e1; font-size: 14px; background: #f8fafc; transition: 0.3s; }
        .form-control-custom:focus { background: #fff; border-color: #4f46e5; outline: none; box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1); }
        
        /* BUTTONS */
        .btn-action { 
            width: 100%; padding: 14px; border-radius: 12px; font-weight: 700; color: #fff; border: none; cursor: pointer; transition: all 0.2s; display: flex; align-items: center; justify-content: center; gap: 10px; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px; 
        }
        .btn-action:hover { transform: translateY(-2px); filter: brightness(110%); box-shadow: 0 4px 12px rgba(0,0,0,0.15); }
        .btn-action:disabled { opacity: 0.7; cursor: not-allowed; transform: none; }
        
        .btn-blue { background: #3b82f6; } .btn-green { background: #10b981; } .btn-red { background: #ef4444; } .btn-purple { background: #8b5cf6; } .btn-orange { background: #f97316; } .btn-dark { background: #334155; }

        /* DEVICE INFO GRID */
        .device-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); gap: 15px; margin-top: 20px; }
        .device-item { background: #fff7ed; border: 1px solid #ffedd5; padding: 15px; border-radius: 12px; text-align: center; transition: 0.2s; }
        .device-item:hover { transform: translateY(-2px); border-color: #fdba74; }
        .device-label { font-size: 11px; font-weight: 600; color: #9a3412; text-transform: uppercase; margin-bottom: 5px; }
        .device-value { font-size: 16px; font-weight: 800; color: #c2410c; word-break: break-word; }
        
        .device-header { background: #fff7ed; padding: 20px; border-radius: 12px; border: 1px solid #ffedd5; margin-top: 20px; display: flex; align-items: center; gap: 15px; }
        .device-icon-large { font-size: 30px; color: #ea580c; background: #fff; width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 6px rgba(234, 88, 12, 0.1); }

        /* UTILITY BOX */
        .util-box { background: #f8fafc; border: 1px dashed #cbd5e1; padding: 20px; border-radius: 12px; text-align: center; margin-bottom: 20px; }
        .util-icon { font-size: 32px; margin-bottom: 10px; color: #64748b; }
        
        /* LOADING OVERLAY */
        #loadingOverlay {
            display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(255, 255, 255, 0.8); z-index: 9999; backdrop-filter: blur(2px);
            justify-content: center; align-items: center; flex-direction: column;
        }
        .spinner { width: 50px; height: 50px; border: 5px solid #e2e8f0; border-top: 5px solid #4f46e5; border-radius: 50%; animation: spin 1s linear infinite; margin-bottom: 15px; }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
        
        /* ANIMATIONS & UTILS */
        .tab-content { display: none; animation: fadeIn 0.4s; }
        .tab-content.active { display: block; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes slideDown { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }

        @media (max-width: 768px) {
            .content-wrapper { padding: 20px 15px; }
            .nav-link-custom { padding: 10px 15px; font-size: 13px; }
            .device-header { flex-direction: column; text-align: center; }
        }
    </style>
</head>
<body>
    <?php include '../../layout/sidebar.php'; ?>
    
    <div id="loadingOverlay">
        <div class="spinner"></div>
        <h5 style="color:#334155; font-weight:700;">Sedang Memproses...</h5>
        <p style="color:#64748b; font-size:13px;">Mohon tunggu respon mesin</p>
    </div>

    <div class="content-wrapper">
        <div class="page-header" style="margin-bottom: 25px;">
            <h2 style="font-weight: 800; color: #1e293b; margin: 0; font-size: 24px;">Maintenance Mesin</h2>
            <p style="color: #64748b; margin-top: 5px; font-size: 14px;">Pusat kontrol perangkat absensi, pemulihan data, dan informasi device.</p>
        </div>

        <?php echo $msg; ?>

        <div class="nav-tabs-wrapper">
            <div class="nav-tabs-custom">
                <button class="nav-link-custom active" onclick="switchTab('tab1')"><i class="fa fa-database"></i> Input & Recovery</button>
                <button class="nav-link-custom" onclick="switchTab('tab2')"><i class="fa fa-users-cog"></i> Manajemen User</button>
                <button class="nav-link-custom" onclick="switchTab('tab3')"><i class="fa fa-microchip"></i> Device & Utilities</button>
            </div>
        </div>

        <div id="tab1" class="tab-content active">
            <div class="row">
                <div class="col-md-6">
                    <div class="modern-card">
                        <div class="card-header-gradient bg-green">
                            <h4 class="card-title"><i class="fa fa-keyboard"></i> Input Absen Manual</h4>
                        </div>
                        <div class="card-body">
                            <form method="POST" onsubmit="showLoading()">
                                <div class="form-group">
                                    <label class="form-label">Karyawan</label>
                                    <select name="pin_karyawan" class="form-control-custom" required>
                                        <option value="">-- Pilih --</option>
                                        <?php 
                                        $q = mysqli_query($conn, "SELECT pin, fullname FROM users WHERE role IN ('user','kepala_bengkel') ORDER BY fullname ASC");
                                        while($r=mysqli_fetch_assoc($q)) echo "<option value='{$r['pin']}'>{$r['fullname']}</option>";
                                        ?>
                                    </select>
                                </div>
                                <div class="row">
                                    <div class="col-6">
                                        <div class="form-group">
                                            <label class="form-label">Waktu</label>
                                            <input type="datetime-local" name="tgl_waktu" class="form-control-custom" value="<?= date('Y-m-d\TH:i') ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="form-group">
                                            <label class="form-label">Status</label>
                                            <select name="status_scan" class="form-control-custom">
                                                <option value="0">Masuk</option>
                                                <option value="1">Pulang</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <button type="submit" name="input_manual" class="btn-action btn-green">
                                    <i class="fa fa-save"></i> SIMPAN DATA
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="modern-card">
                        <div class="card-header-gradient bg-blue">
                            <h4 class="card-title"><i class="fa fa-cloud-download-alt"></i> Tarik Log Cloud</h4>
                        </div>
                        <div class="card-body">
                            <div class="util-box" style="margin-bottom:15px; padding:15px; text-align:left;">
                                <small style="color:#64748b;">
                                    <i class="fa fa-info-circle"></i> Gunakan jika data absensi macet. Data akan ditarik paksa dari Cloud Server.
                                </small>
                            </div>
                            <form method="POST" onsubmit="showLoading()">
                                <div class="row">
                                    <div class="col-6">
                                        <div class="form-group">
                                            <label class="form-label">Dari</label>
                                            <input type="date" name="tgl_awal" class="form-control-custom" value="<?= date('Y-m-d') ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="form-group">
                                            <label class="form-label">Sampai</label>
                                            <input type="date" name="tgl_akhir" class="form-control-custom" value="<?= date('Y-m-d') ?>" required>
                                        </div>
                                    </div>
                                </div>
                                <button type="submit" name="tarik_log" class="btn-action btn-blue">
                                    <i class="fa fa-download"></i> TARIK LOG SEKARANG
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="tab2" class="tab-content">
            <div class="row">
                <div class="col-md-6">
                    <div class="modern-card">
                        <div class="card-header-gradient bg-purple">
                            <h4 class="card-title"><i class="fa fa-download"></i> Get User Info (Backup)</h4>
                        </div>
                        <div class="card-body">
                            <p style="font-size:13px; color:#64748b; line-height:1.6; margin-bottom:20px;">
                                Perintah ini akan meminta mesin untuk mengirim <b>semua data user (PIN, Nama, Wajah, Jari)</b> ke Server Database Webhook. Gunakan untuk backup data dari mesin.
                            </p>
                            <form method="POST" onsubmit="showLoading()">
                                <button type="submit" name="trigger_get_userinfo" class="btn-action btn-purple" onclick="return confirm('Proses ini memakan waktu tergantung jumlah user di mesin. Lanjutkan?')">
                                    <i class="fa fa-satellite-dish"></i> TRIGGER GET USERINFO
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="modern-card">
                        <div class="card-header-gradient bg-dark">
                            <h4 class="card-title"><i class="fa fa-upload"></i> Push User (Restore)</h4>
                        </div>
                        <div class="card-body">
                            <p style="font-size:13px; color:#64748b; line-height:1.6; margin-bottom:20px;">
                                Mengirim <b>Nama Karyawan</b> dari Database Web ke Mesin Absensi. Berguna saat ganti mesin baru agar nama muncul di layar saat scan.
                            </p>
                            <form method="POST" onsubmit="showLoading()">
                                <button type="submit" name="push_all_users" class="btn-action btn-dark" onclick="return confirm('Kirim semua nama user dari database ke mesin?')">
                                    <i class="fa fa-cloud-upload-alt"></i> PUSH NAMA KE MESIN
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="tab3" class="tab-content">
            <div class="row">
                <div class="col-md-12">
                    <div class="modern-card">
                        <div class="card-header-gradient bg-orange">
                            <h4 class="card-title"><i class="fa fa-server"></i> Informasi Perangkat</h4>
                        </div>
                        <div class="card-body">
                            <form method="POST" onsubmit="showLoading()">
                                <button type="submit" name="get_device_info" class="btn-action btn-orange" style="max-width: 300px; margin: 0 auto;">
                                    <i class="fa fa-search"></i> SCAN STATUS TERBARU
                                </button>
                            </form>

                            <?php if ($device_info): ?>
                                <div class="device-header">
                                    <div class="device-icon-large">
                                        <i class="fa fa-fingerprint"></i>
                                    </div>
                                    <div style="text-align: left;">
                                        <h4 style="margin:0; font-weight:800; color:#c2410c;"><?= $device_info['dev_name'] ?? 'Unknown Device' ?></h4>
                                        <span style="font-size:13px; color:#9a3412;">Cloud ID: <?= $device_info['cloud_id'] ?? '-' ?></span>
                                    </div>
                                </div>

                                <div class="device-grid">
                                    <div class="device-item">
                                        <div class="device-label">Firmware</div>
                                        <div class="device-value"><?= $device_info['fw_ver'] ?? '-' ?></div>
                                    </div>
                                    <div class="device-item">
                                        <div class="device-label">Algoritma</div>
                                        <div class="device-value"><?= $device_info['alg_ver'] ?? '-' ?></div>
                                    </div>
                                    <div class="device-item">
                                        <div class="device-label">Total User</div>
                                        <div class="device-value"><?= $device_info['user_cnt'] ?? '0' ?></div>
                                    </div>
                                    <div class="device-item">
                                        <div class="device-label">Total Jari</div>
                                        <div class="device-value"><?= $device_info['fp_cnt'] ?? '0' ?></div>
                                    </div>
                                    <div class="device-item">
                                        <div class="device-label">Total Wajah</div>
                                        <div class="device-value"><?= $device_info['face_cnt'] ?? '0' ?></div>
                                    </div>
                                    <div class="device-item">
                                        <div class="device-label">Total Log</div>
                                        <div class="device-value"><?= $device_info['log_cnt'] ?? '0' ?></div>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div style="margin-top:30px; text-align:center; opacity:0.5;">
                                    <i class="fa fa-search" style="font-size:40px; color:#cbd5e1;"></i>
                                    <p style="margin-top:10px; font-size:14px;">Belum ada data perangkat.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="modern-card">
                        <div class="card-body text-center">
                            <div class="util-box">
                                <i class="fa fa-power-off util-icon" style="color:#ef4444;"></i>
                                <h5 style="font-weight:700; margin-bottom:5px;">Reboot Mesin</h5>
                                <p style="font-size:13px; color:#64748b;">Restart jika mesin hang.</p>
                                <form method="POST" onsubmit="showLoading()">
                                    <button type="submit" name="restart_mesin" class="btn-action btn-red" onclick="return confirm('Yakin restart mesin?')">
                                        RESTART
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="modern-card">
                        <div class="card-body text-center">
                            <div class="util-box">
                                <i class="fa fa-clock util-icon" style="color:#3b82f6;"></i>
                                <h5 style="font-weight:700; margin-bottom:5px;">Sync Waktu</h5>
                                <p style="font-size:13px; color:#64748b;">Samakan jam mesin & server.</p>
                                <form method="POST" onsubmit="showLoading()">
                                    <button type="submit" name="sync_time" class="btn-action btn-blue">
                                        SYNC CLOCK
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <?php include '../../layout/footer.php'; ?>
    <script>
        function switchTab(tabId) {
            // Hide all
            document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
            document.querySelectorAll('.nav-link-custom').forEach(el => el.classList.remove('active'));
            
            // Show selected
            document.getElementById(tabId).classList.add('active');
            event.target.classList.add('active');
        }

        function showLoading() {
            document.getElementById('loadingOverlay').style.display = 'flex';
            setTimeout(() => {
                document.getElementById('loadingOverlay').style.display = 'none';
            }, 3000); // Auto hide after 3 seconds
        }
    </script>
</body>
</html>