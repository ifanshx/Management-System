<?php
// 1. PENGATURAN ERROR
ini_set('display_errors', 0); // Matikan display error di production
error_reporting(E_ALL);

// 2. KONEKSI DAN KONFIGURASI
require_once __DIR__ . '/../config/database.php';

// 3. CEK LOGIN
if (!isset($_SESSION['status']) || $_SESSION['status'] !== "login") {
    header("Location: " . BASE_URL . "login.php?pesan=belum_login");
    exit;
}

// 4. DATA USER
$my_id    = $_SESSION['user_id'];
$role     = $_SESSION['role'] ?? 'user';
$fullname = $_SESSION['fullname'] ?? 'User'; 

// 5. HELPER FUNCTIONS
function get_safe_single_val($conn, $query) {
    $result = mysqli_query($conn, $query);
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        return $row['val'] ?? 0;
    }
    return 0;
}

function get_user_photo($url, $name) {
    if (!empty($url)) {
        return htmlspecialchars($url);
    }
    return "https://ui-avatars.com/api/?name=".urlencode($name)."&background=e0e7ff&color=4f46e5&bold=true&size=128";
}

// 6. AMBIL SETTING JAM KERJA
$q_set = mysqli_query($conn, "SELECT * FROM settings_jam_kerja WHERE id=1");
$aturan = ($q_set && mysqli_num_rows($q_set) > 0) ? mysqli_fetch_assoc($q_set) : [];

$jam_masuk = $aturan['jam_masuk'] ?? "08:00";
$jam_pulang = $aturan['jam_pulang'] ?? "16:00";
$target_full = $aturan['target_menit_full'] ?? 420;
$toleransi = $aturan['toleransi_telat'] ?? 5;

// Hitung Range Scan
$durasi_seb_masuk = $aturan['durasi_sebelum_masuk'] ?? 120;
$durasi_set_masuk = $aturan['durasi_setelah_masuk'] ?? 120;
$durasi_seb_plg   = $aturan['durasi_sebelum_pulang'] ?? 60;
$durasi_set_plg   = $aturan['durasi_setelah_pulang'] ?? 240;

$range_masuk_start = date('H:i:s', strtotime("-$durasi_seb_masuk minutes", strtotime($jam_masuk)));
$range_masuk_end   = date('H:i:s', strtotime("+$durasi_set_masuk minutes", strtotime($jam_masuk)));

// Jam Istirahat
$jam_ist_out = $aturan['jam_istirahat_keluar'] ?? "11:30";
$jam_ist_in  = $aturan['jam_istirahat_masuk'] ?? "12:30";

// Hitung Titik Tengah Istirahat
$ts_out = strtotime($jam_ist_out);
$ts_in  = strtotime($jam_ist_in);
$ts_mid = $ts_out + (($ts_in - $ts_out) / 2); 

$range_ist_out_start = date('H:i:s', strtotime("$jam_ist_out - 60 minutes"));
$range_ist_out_end   = date('H:i:s', $ts_mid); 

$range_ist_in_start  = date('H:i:s', $ts_mid + 1); 
$range_ist_in_end    = date('H:i:s', strtotime("$jam_ist_in + 60 minutes"));

$range_pulang_start = date('H:i:s', strtotime("-$durasi_seb_plg minutes", strtotime($jam_pulang)));
$range_pulang_end   = date('H:i:s', strtotime("+$durasi_set_plg minutes", strtotime($jam_pulang)));

// Default JS Variables
$js_jam_masuk = 0;
$js_status_kerja = "belum"; 
$js_break_start_ts = 0; 
$js_break_end_ts   = 0;
$status_half_day_flag = false;

// 7. AMBIL DATA USER UTAMA & LOGIKA TANGGAL SHIFT AKTIF
$user_pin = '';
$q_user_info = mysqli_query($conn, "SELECT pin FROM users WHERE id='$my_id'");
if ($q_user_info && mysqli_num_rows($q_user_info) > 0) {
    $d_user_info = mysqli_fetch_assoc($q_user_info);
    $user_pin = $d_user_info['pin'] ?? '';
}

// --- FIX CROSS-DAY: Tentukan "Hari Shift" Saat Ini ---
$jam_akses  = date('H:i:s');
$tgl_aktual = date('Y-m-d');

// Jika diakses jam 00:00 - 07:00, "Today" masih dianggap hari kemarin
if ($jam_akses >= "00:00:00" && $jam_akses <= "07:00:00") {
    $today = date('Y-m-d', strtotime('-1 day'));
} else {
    $today = $tgl_aktual;
}
$besok = date('Y-m-d', strtotime($today . ' +1 day'));

// 8. LOGIKA DASHBOARD BERDASARKAN ROLE
if ($role === 'admin') {
    // --- ADMIN LOGIC ---
    $cnt_hadir = get_safe_single_val($conn, "SELECT COUNT(DISTINCT pin) as val FROM absensi WHERE scan_date BETWEEN '$today 00:00:00' AND '$besok 07:00:00'");
    $cnt_pending = get_safe_single_val($conn, "SELECT COUNT(*) as val FROM orderan WHERE status='Pending'");
    $cnt_proses = get_safe_single_val($conn, "SELECT COUNT(*) as val FROM orderan WHERE status='Proses'");
    $prod_today = get_safe_single_val($conn, "SELECT SUM(jumlah) as val FROM hasil_produksi_borongan WHERE tanggal='$today' AND status='Approved'");

    $kpi_list = [
        ['val' => $cnt_hadir, 'label' => 'Hadir Hari Ini', 'icon' => 'fa-users', 'color' => 'blue'],
        ['val' => $cnt_pending, 'label' => 'Order Pending', 'icon' => 'fa-clock', 'color' => 'orange'],
        ['val' => $cnt_proses, 'label' => 'Order Proses', 'icon' => 'fa-spinner', 'color' => 'teal'],
        ['val' => number_format($prod_today), 'label' => 'Output Produksi', 'icon' => 'fa-box-open', 'color' => 'green']
    ];

    // Ambil log terbaru dari semuanya
    $q_list_query = "SELECT a.*, u.fullname FROM absensi a LEFT JOIN users u ON a.pin = u.pin ORDER BY a.scan_date DESC LIMIT 8";

} else {
    // --- USER LOGIC ---
    // Tarik data scan dari jam 00:00 hari shift sampai besok paginya jam 07:00
    $q_absen = mysqli_query($conn, "
        SELECT scan_date, status_scan, photo_url 
        FROM absensi 
        WHERE pin='$user_pin' 
        AND scan_date >= '$today 00:00:00' 
        AND scan_date <= '$besok 07:00:00' 
        ORDER BY scan_date ASC
    ");
      
    $jam_in_db = null; 
    $jam_ist_out_db = null; 
    $jam_ist_in_db = null; 
    $jam_out_db = null;

    $photo_in_db = null;
    $photo_out_db = null;
    $photo_ist_out_db = null;

    if ($q_absen) {
        while ($r = mysqli_fetch_assoc($q_absen)) {
            $ts_scan = strtotime($r['scan_date']);
            $j = date('H:i:s', $ts_scan);
            $scan_tgl = date('Y-m-d', $ts_scan);
            
            // Pengelompokan: Scan dini hari (00-07) masuk ke index shift kemarin
            $tgl_idx = ($j >= "00:00:00" && $j <= "07:00:00") ? date('Y-m-d', strtotime($scan_tgl . ' -1 day')) : $scan_tgl;
            
            // Hanya proses scan yang masuk dalam $today (Shift Aktif)
            if ($tgl_idx == $today) {
                if ($j >= $range_masuk_start && $j <= $range_masuk_end) { 
                    if(!$jam_in_db) {
                        $jam_in_db = $r['scan_date']; 
                        $photo_in_db = $r['photo_url'];
                    }
                }
                elseif ($j >= $range_ist_out_start && $j <= $range_ist_out_end) { 
                    $jam_ist_out_db = $r['scan_date']; 
                    $photo_ist_out_db = $r['photo_url'];
                }
                elseif ($j >= $range_ist_in_start && $j <= $range_ist_in_end) { 
                    if(!$jam_in_db) { 
                        $jam_in_db = $r['scan_date']; 
                        $photo_in_db = $r['photo_url'];
                    } else { 
                        $jam_ist_in_db = $r['scan_date']; 
                    }
                }
                // FIX: Menangkap pulang di jam normal ATAU dini hari (00:00 - 07:00)
                elseif ($j >= $range_pulang_start || ($j >= "00:00:00" && $j <= "07:00:00")) { 
                    $jam_out_db = $r['scan_date']; 
                    $photo_out_db = $r['photo_url']; 
                }
                
                // Prioritas Override jika status scan explicitly '1' (Pulang) dari mesin
                if ($r['status_scan'] == 1) {
                    $jam_out_db = $r['scan_date'];
                    $photo_out_db = $r['photo_url'];
                }
            }
        }
    }

    // --- LOGIKA HALF DAY REALTIME ---
    if ($jam_in_db && empty($jam_out_db) && !empty($jam_ist_out_db) && empty($jam_ist_in_db)) {
        $jam_sekarang = date('H:i:s');
        $batas_wajar  = date('H:i:s', strtotime("$jam_ist_in + 30 minutes"));

        if ($jam_sekarang > $batas_wajar) {
            $jam_out_db = $jam_ist_out_db; 
            $photo_out_db = $photo_ist_out_db; 
            $status_half_day_flag = true;
        }
    }

    $durasi_display = "Belum Absen Masuk";
    
    if ($jam_in_db) {
        $ts_in = strtotime($jam_in_db);
        $js_jam_masuk = $ts_in * 1000;

        $ts_break_start = strtotime(date('Y-m-d', $ts_in) . ' ' . $jam_ist_out);
        $ts_break_end   = strtotime(date('Y-m-d', $ts_in) . ' ' . $jam_ist_in);
        
        $js_break_start_ts = $ts_break_start * 1000;
        $js_break_end_ts   = $ts_break_end * 1000;

        // strtotime akan secara otomatis kalkulasi detik secara akurat bahkan meski lintas hari (+24jam)
        $ts_end = $jam_out_db ? strtotime($jam_out_db) : time();
        $total_seconds = $ts_end - $ts_in;
        
        if ($ts_in < $ts_break_start && $ts_end > $ts_break_end) {
            if (!$status_half_day_flag) {
                $break_duration = $ts_break_end - $ts_break_start;
                $total_seconds -= $break_duration;
            }
        } 
        elseif ($ts_in < $ts_break_start && $ts_end >= $ts_break_start && $ts_end <= $ts_break_end) {
            $total_seconds = $ts_break_start - $ts_in;
        }

        $total_seconds = max(0, $total_seconds);

        $h = floor($total_seconds / 3600);
        $m = floor(($total_seconds % 3600) / 60);
        $durasi_display = sprintf("%02d Jam %02d Menit", $h, $m);

        if ($jam_out_db) {
            $js_status_kerja = "selesai";
        } else {
            $js_status_kerja = "kerja";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <?php include __DIR__ . '/../layout/header.php'; ?>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary: #4f46e5; 
            --dark: #0f172a;
            --light: #f8fafc;
            --gray-border: #e2e8f0;
            --card-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
        }
        body { 
            background-color: var(--light); 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            color: #334155; 
        }
        .content-wrapper { 
            padding: 30px 20px; 
        }
        
        /* Welcome Banner */
        .welcome-card {
            background: linear-gradient(135deg, #4f46e5 0%, #3730a3 100%);
            border-radius: 24px; 
            padding: 30px 40px; 
            color: #fff; 
            margin-bottom: 30px;
            display: flex; 
            justify-content: space-between; 
            align-items: center;
            box-shadow: 0 10px 30px -5px rgba(79, 70, 229, 0.4); 
            position: relative; 
            overflow: hidden;
        }
        .welcome-card::before {
            content: ''; 
            position: absolute; 
            top: -60px; 
            left: -60px;
            width: 250px; 
            height: 250px; 
            background: radial-gradient(circle, rgba(255,255,255,0.15) 0%, rgba(255,255,255,0) 70%);
            border-radius: 50%; 
            z-index: 0;
        }
        .welcome-profile-group { 
            display: flex; 
            align-items: center; 
            gap: 20px; 
            position: relative; 
            z-index: 2; 
            flex: 1; 
        }
        .profile-frame {
            width: 75px; 
            height: 75px; 
            border-radius: 50%; 
            padding: 3px;
            background: rgba(255, 255, 255, 0.3); 
            backdrop-filter: blur(4px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.2); 
            flex-shrink: 0;
        }
        .profile-img { 
            width: 100%; 
            height: 100%; 
            object-fit: cover; 
            border-radius: 50%; 
            border: 2px solid #fff; 
            background-color: #fff; 
        }
        .welcome-text h1 { 
            margin: 0; 
            font-size: 26px; 
            font-weight: 800; 
            letter-spacing: -0.5px; 
            text-shadow: 0 2px 4px rgba(0,0,0,0.1); 
        }
        .welcome-text p { 
            margin: 4px 0 0; 
            opacity: 0.9; 
            font-size: 14px; 
            font-weight: 500; 
        }
        .dashboard-logo-container { 
            flex: 0 0 auto; 
            margin: 0 40px; 
            display: flex; 
            justify-content: center; 
            align-items: center; 
            z-index: 2; 
        }
        .dashboard-logo { 
            height: 65px; 
            width: auto; 
            filter: drop-shadow(0 4px 6px rgba(0,0,0,0.3)); 
            transition: transform 0.3s ease; 
        }
        .dashboard-logo:hover { 
            transform: scale(1.1) rotate(2deg); 
        }
        .clock-container { 
            text-align: right; 
            position: relative; 
            z-index: 2; 
            flex: 1; 
        }
        .live-clock { 
            font-family: 'JetBrains Mono', monospace; 
            font-size: 36px; 
            font-weight: 700; 
            letter-spacing: -1.5px; 
            text-shadow: 0 2px 4px rgba(0,0,0,0.1); 
        }
        .live-date { 
            font-size: 13px; 
            font-weight: 600; 
            opacity: 0.8; 
            margin-top: 2px; 
            text-transform: uppercase; 
            letter-spacing: 1px; 
        }

        /* KPI Grid */
        .stats-grid { 
            display: grid; 
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); 
            gap: 20px; 
            margin-bottom: 30px; 
        }
        .stat-card {
            background: #fff; 
            border-radius: 16px; 
            padding: 25px; 
            border: 1px solid var(--gray-border);
            display: flex; 
            align-items: center; 
            gap: 20px; 
            transition: transform 0.2s, box-shadow 0.2s; 
            box-shadow: var(--card-shadow);
        }
        .stat-card:hover { 
            transform: translateY(-3px); 
            box-shadow: 0 10px 20px -5px rgba(0,0,0,0.05); 
        }
        .icon-box { 
            width: 55px; 
            height: 55px; 
            border-radius: 14px; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            font-size: 22px; 
            flex-shrink: 0; 
        }
        .bg-blue { background: #eff6ff; color: #2563eb; }
        .bg-green { background: #f0fdf4; color: #16a34a; }
        .bg-orange { background: #fff7ed; color: #ea580c; }
        .bg-teal { background: #ecfeff; color: #06b6d4; }
        .stat-val { 
            font-size: 26px; 
            font-weight: 800; 
            color: var(--dark); 
            line-height: 1.1; 
            margin-bottom: 2px; 
        }
        .stat-label { 
            font-size: 12px; 
            font-weight: 600; 
            text-transform: uppercase; 
            color: #64748b; 
            letter-spacing: 0.5px; 
        }

        /* Info Grid */
        .info-grid { 
            display: grid; 
            grid-template-columns: 2fr 1fr; 
            gap: 25px; 
        }
        .info-card { 
            background: #fff; 
            border-radius: 16px; 
            border: 1px solid var(--gray-border); 
            overflow: hidden; 
            height: 100%; 
            box-shadow: var(--card-shadow); 
        }
        .card-header { 
            padding: 20px 25px; 
            border-bottom: 1px solid var(--gray-border); 
            background: #fff; 
            display: flex; 
            align-items: center; 
            gap: 12px; 
        }
        .card-header h3 { 
            margin: 0; 
            font-size: 16px; 
            font-weight: 700; 
            color: var(--dark); 
        }
        .card-body { 
            padding: 25px; 
        }

        /* Rules */
        .rule-item { 
            display: flex; 
            align-items: flex-start; 
            gap: 15px; 
            margin-bottom: 20px; 
        }
        .rule-item:last-child { 
            margin-bottom: 0; 
        }
        .rule-icon { 
            width: 40px; 
            height: 40px; 
            border-radius: 12px; 
            background: #f1f5f9; 
            color: #475569; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            font-size: 16px; 
            flex-shrink: 0; 
        }
        .rule-content h4 { 
            margin: 0 0 4px; 
            font-size: 14px; 
            font-weight: 700; 
            color: var(--dark); 
        }
        .rule-content p { 
            margin: 0; 
            font-size: 13px; 
            color: #64748b; 
            line-height: 1.5; 
        }

        /* Live Status */
        .status-box { 
            text-align: center; 
            padding: 15px 0; 
        }
        .timer-display {
            font-family: 'JetBrains Mono', monospace; 
            font-size: 32px; 
            font-weight: 700;
            color: var(--primary); 
            margin: 15px 0; 
            background: #eef2ff; 
            padding: 10px; 
            border-radius: 12px;
            border: 1px dashed #c7d2fe;
        }
        .status-badge { 
            display: inline-flex; 
            align-items: center; 
            gap: 8px; 
            padding: 8px 16px; 
            border-radius: 30px; 
            font-size: 12px; 
            font-weight: 700; 
            text-transform: uppercase; 
        }
        .badge-working { background: #dbeafe; color: #1e40af; }
        .badge-idle { background: #f1f5f9; color: #64748b; border: 1px solid #cbd5e1; }
        
        /* Table Styling */
        .table {
            width: 100%; 
            border-collapse: collapse; 
            margin: 0;
        }
        .table thead {
            background: #f8fafc; 
            border-bottom: 1px solid #e2e8f0;
        }
        .table th {
            padding: 15px 25px; 
            font-size: 12px; 
            color: #64748b; 
            text-transform: uppercase;
            text-align: left;
            font-weight: 600;
        }
        .table td {
            padding: 15px 25px; 
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }
        .table tr:last-child td {
            border-bottom: none;
        }

        /* Mobile */
        @media (max-width: 768px) {
            .welcome-card { 
                flex-direction: column; 
                text-align: center; 
                gap: 20px; 
                padding: 30px 20px; 
            }
            .welcome-profile-group { 
                flex-direction: column; 
                gap: 10px; 
            }
            .dashboard-logo-container { 
                margin: 5px 0; 
                order: -1; 
            }
            .dashboard-logo { 
                height: 60px; 
            }
            .clock-container { 
                text-align: center; 
            }
            
            /* --- MODIFIKASI LAYOUT MOBILE --- */
            .info-grid { 
                /* Ubah dari grid ke flex agar bisa pakai order */
                display: flex;
                flex-direction: column;
                gap: 20px;
            }
            /* Balik urutan: Status dulu (1), baru Informasi (2) */
            .card-status { order: 1; }
            .card-info { order: 2; }
            /* -------------------------------- */

            .welcome-card::before { 
                width: 150px; 
                height: 150px; 
                top: -30px; 
                right: -30px; 
            }
            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/../layout/sidebar.php'; ?>
    
    <div class="content-wrapper">
        <div class="welcome-card">
            <div class="welcome-profile-group">
                <div class="welcome-text">
                    <h1>Halo, <?=$fullname?>!</h1>
                    <p>Selamat datang di Dashboard Sistem Manajemen NORIC.</p>
                </div>
            </div>
            <div class="dashboard-logo-container">
                <img src="<?php echo BASE_URL; ?>assets/image/logo-noric.png" alt="NORIC Racing" class="dashboard-logo">
            </div>
            <div class="clock-container">
                <div class="live-clock" id="rt-clock">00:00:00</div>
                <div class="live-date" id="rt-date">...</div>
            </div>
        </div>

        <?php if($role === 'admin'): ?>
            <div class="stats-grid">
                <?php foreach($kpi_list as $k): ?>
                <div class="stat-card">
                    <div class="icon-box bg-<?=$k['color']?>"><i class="fa <?=$k['icon']?>"></i></div>
                    <div>
                        <div class="stat-val"><?=$k['val']?></div>
                        <div class="stat-label"><?=$k['label']?></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="info-card">
                <div class="card-header">
                    <i class="fa fa-list-check text-primary"></i> 
                    <h3>Aktivitas Absensi Terbaru</h3>
                </div>
                <div class="card-body p-0">
                    <div style="overflow-x:auto;">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Karyawan</th>
                                    <th>Waktu</th>
                                    <th>Status</th>
                                    <th>Verifikasi</th>
                                </tr>
                            </thead>
                            <tbody>
    <?php 
    $res = mysqli_query($conn, $q_list_query);
    if(mysqli_num_rows($res) > 0):
        while($row = mysqli_fetch_assoc($res)): 
            $j = date('H:i', strtotime($row['scan_date']));
            $scan_time = date('H:i:s', strtotime($row['scan_date']));
            
            // FIX CROSS-DAY: Status label Admin kini mendeteksi jam dini hari sebagai "PULANG"
            if ($scan_time >= $range_masuk_start && $scan_time <= $range_masuk_end) {
                $s = 'MASUK';
                $bg_badge = 'background:#dcfce7; color:#166534; border:1px solid #bbf7d0;';
            } elseif ($scan_time >= $range_ist_out_start && $scan_time <= $range_ist_out_end) {
                $s = 'ISTIRAHAT KELUAR';
                $bg_badge = 'background:#ffedd5; color:#9a3412; border:1px solid #fed7aa;';
            } elseif ($scan_time >= $range_ist_in_start && $scan_time <= $range_ist_in_end) {
                $s = 'ISTIRAHAT MASUK';
                $bg_badge = 'background:#dbeafe; color:#1e40af; border:1px solid #bfdbfe;';
            } elseif ($scan_time >= $range_pulang_start || ($scan_time >= "00:00:00" && $scan_time <= "07:00:00")) {
                $s = 'PULANG';
                $bg_badge = 'background:#fee2e2; color:#991b1b; border:1px solid #fecaca;';
            } else {
                $s = 'LOG';
                $bg_badge = 'background:#f1f5f9; color:#64748b; border:1px solid #e2e8f0;';
            }
            
            $verify_mode = $row['verify_mode'];
            $verify_text = ($verify_mode == 1) ? 'Finger' : (($verify_mode == 4) ? 'Face' : 'Other');

            // --- MODIFIKASI: Ambil Foto Absen atau Generate Avatar ---
            $user_pic = get_user_photo($row['photo_url'], $row['fullname']);
        ?>
        <tr>
            <td>
                <div style="display:flex; align-items:center; gap:12px;">
                    <img src="<?=$user_pic?>" style="width:40px; height:40px; border-radius:50%; object-fit:cover; border:1px solid #e2e8f0;" alt="Foto">
                    <span style="font-weight:600; color:#334155;"><?=$row['fullname']?></span>
                </div>
            </td>
            
            <td style="font-family:'JetBrains Mono', monospace; font-size:13px; font-weight:600;">
                <?=$j?> WIB
            </td>
            
            <td>
                <span style="padding:5px 12px; border-radius:30px; font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:0.5px; <?=$bg_badge?>">
                    <?=$s?>
                </span>
            </td>
            
            <td>
                <span style="font-size:11px; color:#475569; background:#f8fafc; padding:3px 8px; border-radius:4px; border:1px solid #e2e8f0;">
                    <?=$verify_text?>
                </span>
            </td>
        </tr>
        <?php endwhile; else: ?>
        <tr>
            <td colspan="4" style="padding:40px; text-align:center;">
                <div style="margin-bottom:10px; color:#cbd5e1; font-size:40px;"><i class="fa fa-fingerprint"></i></div>
                <div style="color:#94a3b8; font-weight:500;">Belum ada aktivitas absensi hari ini.</div>
            </td>
        </tr>
        <?php endif; ?>
</tbody>
                        </table>
                    </div>
                </div>
            </div>

        <?php else: ?>
            <div class="info-grid">
                <div class="info-card card-info">
                    <div class="card-header">
                        <i class="fa fa-circle-info text-primary"></i> 
                        <h3>Informasi & Kebijakan</h3>
                    </div>
                    <div class="card-body">
                        <div class="rule-item">
                            <div class="rule-icon"><i class="fa fa-clock"></i></div>
                            <div class="rule-content">
                                <h4>Jam Kerja & Keterlambatan</h4>
                                <p>Jam Masuk: <b><?=$jam_masuk?></b>. Toleransi terlambat <b><?=$toleransi?> menit</b>. Keterlambatan akan mengurangi durasi kerja riil.</p>
                            </div>
                        </div>
                        <div class="rule-item">
                            <div class="rule-icon"><i class="fa fa-money-bill-wave"></i></div>
                            <div class="rule-content">
                                <h4>Gaji Pokok (Karyawan Tetap)</h4>
                                <p>Target Full Day adalah <b><?=$target_full?> menit</b> (<?=floor($target_full/60)?> jam). Jika kurang, gaji pokok dihitung <b>Pro-Rata</b>. Uang makan cair jika kerja minimal <b>5 Jam</b>.</p>
                            </div>
                        </div>
                        <div class="rule-item">
                            <div class="rule-icon"><i class="fa fa-bolt"></i></div>
                            <div class="rule-content">
                                <h4>Lembur & Shift</h4>
                                <p>Lembur dihitung dari kelebihan menit kerja setelah mencapai target. Pulang di atas <b>18:30</b> dikenakan potongan istirahat Maghrib 30 menit.</p>
                            </div>
                        </div>
                        <div class="rule-item">
                            <div class="rule-icon"><i class="fa fa-cubes"></i></div>
                            <div class="rule-content">
                                <h4>Kebijakan Karyawan Borongan</h4>
                                <p>Gaji dihitung berdasarkan <b>Total Output Produksi</b> yang disetujui (Approved). Absensi tetap wajib dilakukan sebagai bukti kehadiran, namun tidak berlaku aturan pro-rata menit.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="info-card card-status">
                    <div class="card-header">
                        <i class="fa fa-stopwatch text-danger"></i> 
                        <h3>Status Kehadiran</h3>
                    </div>
                    <div class="card-body">
                        <div class="status-box">
                            <div style="font-size:12px; color:#64748b; font-weight:600; text-transform:uppercase;">
                                Durasi Kerja Real-Time
                            </div>
                            <div id="live-timer" class="timer-display">
                                <?php echo ($js_status_kerja == 'kerja') ? '00:00:00' : $durasi_display; ?>
                            </div>
                            <span class="status-badge <?= ($js_status_kerja == 'kerja') ? 'badge-working' : 'badge-idle' ?>">
                                <?php if($js_status_kerja == 'kerja'): ?>
                                    <i class="fa fa-circle" style="font-size:8px;"></i> SEDANG BEKERJA
                                <?php elseif($js_status_kerja == 'selesai'): ?>
                                    <i class="fa fa-check-circle" style="font-size:8px;"></i> 
                                    <?= $status_half_day_flag ? "SELESAI BEKERJA (HALF DAY)" : "SELESAI BEKERJA" ?>
                                <?php else: ?>
                                    <i class="fa fa-circle-stop" style="font-size:8px;"></i> <?= strtoupper($durasi_display) ?>
                                <?php endif; ?>
                            </span>
                            
                            <div style="margin-top:25px; border-top:1px dashed #e2e8f0; padding-top:15px;">
                                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
                                    <div style="text-align:left;">
                                        <span style="color:#64748b; font-size:11px; display:block; margin-bottom:5px;">MASUK</span>
                                        <div style="width:60px; height:60px; background:#f1f5f9; border-radius:8px; display:flex; align-items:center; justify-content:center; color:#cbd5e1; margin:0 auto; overflow:hidden;">
                                            <?php if(!empty($photo_in_db)): ?>
                                                <img src="<?= $photo_in_db ?>" style="width:100%; height:100%; object-fit:cover;">
                                            <?php else: ?>
                                                <i class="fa fa-sign-in-alt"></i>
                                            <?php endif; ?>
                                        </div>
                                        <div style="font-weight:700; color:#1e293b; font-family:'JetBrains Mono'; margin-top:5px; font-size:12px; text-align:center;">
                                            <?= $jam_in_db ? date('H:i', strtotime($jam_in_db)) : '--:--' ?>
                                        </div>
                                    </div>

                                    <div style="text-align:center;">
                                        <span style="color:#64748b; font-size:11px; display:block; margin-bottom:5px;">ISTIRAHAT</span>
                                        <div style="width:60px; height:60px; background:#fef3c7; border-radius:8px; display:flex; align-items:center; justify-content:center; color:#d97706; margin:0 auto;">
                                            <i class="fa fa-coffee"></i>
                                        </div>
                                        <div style="font-weight:700; color:#1e293b; font-family:'JetBrains Mono'; margin-top:5px; font-size:12px; text-align:center;">
                                            <?= $jam_ist_out ?> - <?= $jam_ist_in ?>
                                        </div>
                                    </div>

                                    <div style="text-align:right;">
                                        <span style="color:#64748b; font-size:11px; display:block; margin-bottom:5px;">PULANG</span>
                                        <div style="width:60px; height:60px; background:#f1f5f9; border-radius:8px; display:flex; align-items:center; justify-content:center; color:#cbd5e1; margin-left:auto; overflow:hidden;">
                                            <?php if(!empty($photo_out_db)): ?>
                                                <img src="<?= $photo_out_db ?>" style="width:100%; height:100%; object-fit:cover;">
                                            <?php else: ?>
                                                <i class="fa fa-sign-out-alt"></i>
                                            <?php endif; ?>
                                        </div>
                                        <div style="font-weight:700; color:#1e293b; font-family:'JetBrains Mono'; margin-top:5px; font-size:12px; text-align:center;">
                                            <?= $jam_out_db ? date('H:i', strtotime($jam_out_db)) : '--:--' ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <?php include __DIR__ . '/../layout/footer.php'; ?>
    
    <script>
        // Live Clock
        function updateClock() {
            const now = new Date();
            document.getElementById('rt-clock').textContent = now.toLocaleTimeString('id-ID', {hour12:false});
            document.getElementById('rt-date').textContent = now.toLocaleDateString('id-ID', {
                weekday: 'long', 
                day: 'numeric', 
                month: 'long', 
                year: 'numeric'
            });
        }
        setInterval(updateClock, 1000);
        updateClock();

        // Live Timer untuk User
        <?php if($role != 'admin' && $js_status_kerja == 'kerja'): ?>
            const jamMasuk    = <?=$js_jam_masuk?>; 
            const breakStart  = <?=$js_break_start_ts?>;
            const breakEnd    = <?=$js_break_end_ts?>;
            const breakDur    = breakEnd - breakStart; // durasi istirahat (ms)

            function updateTimer() {
                const now = new Date().getTime();
                
                // Hitung durasi kotor
                let diff = now - jamMasuk;

                // Logika Net Duration (Sama seperti di PHP)
                if (jamMasuk < breakStart && now > breakEnd) {
                    diff -= breakDur; // Potong full istirahat
                }
                else if (jamMasuk < breakStart && now >= breakStart && now <= breakEnd) {
                    diff = breakStart - jamMasuk; // Freeze timer saat istirahat
                }

                if(diff > 0) {
                    const h = Math.floor(diff / (1000 * 60 * 60));
                    const m = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
                    const s = Math.floor((diff % (1000 * 60)) / 1000);
                    const str = [h, m, s].map(v => v < 10 ? "0" + v : v).join(":");
                    document.getElementById('live-timer').innerText = str;
                }
            }
            setInterval(updateTimer, 1000);
            updateTimer();
        <?php endif; ?>
    </script>
</body>
</html>