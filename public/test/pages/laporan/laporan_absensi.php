<?php 
require_once '../../config/database.php';
// Set Timezone agar sinkron
date_default_timezone_set('Asia/Jakarta');

if (!isset($_SESSION['status'])) { header("Location: ../../login.php"); exit; }

if ($_SESSION['role'] !== 'admin') {
    header("Location: ../dashboard.php");
    exit;
}

// =========================================================================
// 1. CONFIG & SETTINGS
// =========================================================================
$q_set = mysqli_query($conn, "SELECT * FROM settings_jam_kerja WHERE id=1");
$aturan = mysqli_fetch_assoc($q_set);

if (!$aturan) {
    echo "<script>alert('Harap isi pengaturan jam kerja dahulu!'); window.location='../dashboard.php';</script>";
    exit;
}

$JAM_MASUK      = $aturan['jam_masuk'];              
$JAM_IST_OUT    = $aturan['jam_istirahat_keluar']; 
$JAM_IST_IN     = $aturan['jam_istirahat_masuk'];  
$JAM_PULANG     = $aturan['jam_pulang'];            
$TARGET_FULL    = (int)$aturan['target_menit_full']; 
$TARGET_HALF    = (int)$aturan['target_menit_half']; 
$TOL_TELAT      = (int)$aturan['toleransi_telat'];
$TOL_PLG_AWAL   = 0; 
$LEM_MIN        = (int)$aturan['lembur_min'];
$LEM_MAX        = (int)$aturan['lembur_max'];
$LEM_POT        = (int)$aturan['lembur_pengurang'];

// HITUNG RANGE SCAN
$r_masuk_start = date('H:i:s', strtotime("$JAM_MASUK - {$aturan['durasi_sebelum_masuk']} minutes"));
$r_masuk_end   = date('H:i:s', strtotime("$JAM_MASUK + {$aturan['durasi_setelah_masuk']} minutes"));
$ts_out = strtotime($JAM_IST_OUT); $ts_in  = strtotime($JAM_IST_IN); $ts_mid = $ts_out + (($ts_in - $ts_out) / 2); 
$r_ist_out_start = date('H:i:s', strtotime("$JAM_IST_OUT - 60 minutes")); $r_ist_out_end   = date('H:i:s', $ts_mid); 
$r_ist_in_start  = date('H:i:s', $ts_mid + 1); $r_ist_in_end    = date('H:i:s', strtotime("$JAM_IST_IN + 60 minutes"));
$r_plg_start   = date('H:i:s', strtotime("$JAM_PULANG - {$aturan['durasi_sebelum_pulang']} minutes"));
$r_plg_end     = date('H:i:s', strtotime("$JAM_PULANG + {$aturan['durasi_setelah_pulang']} minutes"));

// =========================================================================
// 2. FILTER & DATA FETCHING
// =========================================================================
$default_awal  = date('Y-m-d', strtotime('monday this week'));
$default_akhir = date('Y-m-d', strtotime('sunday this week'));

$tgl_awal  = isset($_GET['tgl_awal']) ? $_GET['tgl_awal'] : $default_awal;
$tgl_akhir = isset($_GET['tgl_akhir']) ? $_GET['tgl_akhir'] : $default_akhir;
$filter_id = isset($_GET['user_id']) ? $_GET['user_id'] : '';

// 1. Ambil User (Sesuai Filter)
$sql_user = "SELECT id, fullname, pin FROM users WHERE role IN ('user', 'kepala_toko', 'kepala_bengkel')";
if(!empty($filter_id)) { $sql_user .= " AND id='$filter_id'"; }
$sql_user .= " ORDER BY fullname ASC";
$q_users = mysqli_query($conn, $sql_user);
$list_users = []; while($r = mysqli_fetch_assoc($q_users)) $list_users[] = $r;

// 2. Ambil Absensi (Bulk Range)
$absen_map = [];
$tgl_akhir_scan = date('Y-m-d H:i:s', strtotime($tgl_akhir . ' +1 day 07:00:00'));
$sql_absen = "SELECT pin, scan_date FROM absensi WHERE scan_date BETWEEN '$tgl_awal 00:00:00' AND '$tgl_akhir_scan' ORDER BY scan_date ASC";
$q_raw = mysqli_query($conn, $sql_absen);
while($r = mysqli_fetch_assoc($q_raw)) {
    $scan_ts = strtotime($r['scan_date']);
    $tgl_aktual = date('Y-m-d', $scan_ts);
    $jam = date('H:i:s', $scan_ts);
    // Logika Shift Malam (Scan jam 00:00-07:00 masuk ke hari sebelumnya)
    $tgl_idx = ($jam >= "00:00:00" && $jam <= "07:00:00") ? date('Y-m-d', strtotime($tgl_aktual . ' -1 day')) : $tgl_aktual;
    $absen_map[$r['pin']][$tgl_idx][] = $jam;
}

// 3. Ambil Izin
$izin_map = [];
$sql_izin = "SELECT user_id, jenis_izin, tanggal, keterangan FROM perizinan WHERE status='Approved' AND tanggal BETWEEN '$tgl_awal 00:00:00' AND '$tgl_akhir 23:59:59'";
$q_izin = mysqli_query($conn, $sql_izin);
while($row = mysqli_fetch_assoc($q_izin)) {
    $tgl_only = date('Y-m-d', strtotime($row['tanggal']));
    $izin_map[$row['user_id']][$tgl_only] = $row;
}

// =========================================================================
// 3. PROCESSING (GROUP BY USER)
// =========================================================================
$final_report = []; // Array penampung hasil akhir

foreach($list_users as $u) {
    $uid = $u['id'];
    $pin = $u['pin'];
    
    // Struktur Data Per Karyawan
    $user_data = [
        'info' => $u,
        'stats' => ['hadir' => 0, 'telat' => 0, 'plg_awal' => 0, 'lembur' => 0, 'izin' => 0, 'alpha' => 0],
        'logs' => []
    ];

    $period_obj = new DatePeriod(new DateTime($tgl_awal), new DateInterval('P1D'), new DateTime($tgl_akhir . ' +1 day'));

    foreach($period_obj as $dt) {
        $curr_date = $dt->format('Y-m-d');
        $is_libur  = ($dt->format('N') == 7); 
        
        $d = [
            'tanggal' => $curr_date, 
            'in' => null, 'ist_out' => null, 'ist_in' => null, 'out' => null,
            'telat' => 0, 'plg_awal' => 0, 'durasi' => 0, 'lembur' => 0,
            'status' => $is_libur ? 'Libur' : 'Alpha',
            'badge' => $is_libur ? 'badge-libur' : 'badge-alpha',
            'is_shift_siang' => false, 'is_maghrib' => false, 'ket' => [] 
        ];

        $data_izin = isset($izin_map[$uid][$curr_date]) ? $izin_map[$uid][$curr_date] : null;

        if(isset($absen_map[$pin][$curr_date])) {
            $scans = $absen_map[$pin][$curr_date];
            foreach($scans as $jam) {
                if ($jam >= $r_masuk_start && $jam <= $r_masuk_end) { if(!$d['in']) $d['in'] = $jam; }
                elseif ($jam >= $r_ist_out_start && $jam <= $r_ist_out_end) { $d['ist_out'] = $jam; }
                elseif ($jam >= $r_ist_in_start && $jam <= $r_ist_in_end) { 
                    if(!$d['in']) { $d['in'] = $jam; $d['is_shift_siang'] = true; } else { $d['ist_in'] = $jam; }
                }
                elseif (($jam >= $r_plg_start && $jam <= $r_plg_end) || ($jam >= "00:00:00" && $jam <= "07:00:00")) { $d['out'] = $jam; }
                elseif ($jam > $r_plg_end) { $d['out'] = $jam; }
            }

            if ($d['in'] && empty($d['out']) && $data_izin && $data_izin['jenis_izin'] == 'Pulang Cepat') {
                $d['out'] = date('H:i:s', strtotime($data_izin['tanggal']));
                $d['ket'][] = "Auto-Out (Izin)";
            }

            if ($d['in'] && empty($d['out']) && !empty($d['ist_out']) && empty($d['ist_in'])) {
                $t_in = strtotime("$curr_date ".$d['in']); 
                $t_pot = strtotime("$curr_date ".$d['ist_out']);
                if (floor(($t_pot - $t_in) / 60) >= $TARGET_HALF) {
                    $jam_sekarang = date('H:i:s');
                    $batas_wajar  = date('H:i:s', strtotime("$JAM_IST_IN + 30 minutes")); 
                    if ($curr_date != date('Y-m-d') || ($curr_date == date('Y-m-d') && $jam_sekarang > $batas_wajar)) {
                        $d['out'] = $d['ist_out']; $d['ket'][] = "Half Day"; 
                    }
                }
            }

            if($d['in']) {
                $d['status'] = 'HADIR'; $d['badge'] = 'badge-full';
                $user_data['stats']['hadir']++;

                $ts_in = strtotime("$curr_date ".$d['in']);
                $jam_target = $d['is_shift_siang'] ? $JAM_IST_IN : $JAM_MASUK;
                $ts_target = strtotime("$curr_date $jam_target");
                if($ts_in > ($ts_target + ($TOL_TELAT * 60))) {
                    $d['telat'] = floor(($ts_in - $ts_target) / 60);
                    $user_data['stats']['telat']++;
                }

                if ($d['out']) {
                    $ts_out = strtotime("$curr_date ".$d['out']);
                    if($ts_out < $ts_in) $ts_out += 86400;

                    if (!$d['is_shift_siang'] && !in_array("Half Day", $d['ket'])) {
                        $ts_target_plg = strtotime("$curr_date $JAM_PULANG");
                        if ($ts_out < $ts_target_plg) {
                            $selisih = floor(($ts_target_plg - $ts_out) / 60);
                            if ($selisih > $TOL_PLG_AWAL) {
                                $is_valid = ($data_izin && $data_izin['jenis_izin'] == 'Pulang Cepat');
                                if($is_valid) {
                                    $d['badge'] = 'badge-info'; $d['status'] = 'IZIN PLG CEPAT';
                                    $d['ket'][] = "Izin: ".date('H:i', strtotime($data_izin['tanggal']));
                                } else {
                                    $d['plg_awal'] = $selisih;
                                    $user_data['stats']['plg_awal']++;
                                }
                            }
                        }
                    }

                    $dur_kotor = floor(($ts_out - $ts_in)/60);
                    $potongan = 0;
                    if (!$d['is_shift_siang']) {
                        $ts_ist_out_real = strtotime("$curr_date $JAM_IST_OUT");
                        $ts_ist_in_real  = strtotime("$curr_date $JAM_IST_IN");
                        if ($ts_in < $ts_ist_out_real && $ts_out > $ts_ist_in_real && !in_array("Half Day", $d['ket'])) {
                            $potongan = 60; 
                        }
                    }
                    $d['durasi'] = max(0, $dur_kotor - $potongan);

                    if ($d['durasi'] > $TARGET_FULL) {
                        $raw_lembur = $d['durasi'] - $TARGET_FULL;
                        if ($ts_out > strtotime("$curr_date 18:00:00")) { 
                            $raw_lembur -= $LEM_POT; $d['is_maghrib'] = true; 
                        }
                        if ($raw_lembur >= $LEM_MIN) {
                            $d['lembur'] = min($raw_lembur, $LEM_MAX);
                            $user_data['stats']['lembur'] += $d['lembur'];
                        }
                    }
                    
                    if ($d['status'] == 'HADIR') {
                        if ($d['durasi'] < $TARGET_HALF) $d['badge'] = 'badge-low';
                        elseif ($d['durasi'] < $TARGET_FULL) $d['badge'] = 'badge-half';
                    }
                } else {
                    $d['badge'] = 'badge-low'; $d['ket'][] = "Lupa Pulang";
                }
            }
        } else {
            if (!$is_libur) {
                if ($data_izin) {
                    $d['status'] = strtoupper($data_izin['jenis_izin']);
                    $d['badge'] = 'badge-info';
                    $user_data['stats']['izin']++;
                } else {
                    $user_data['stats']['alpha']++;
                }
            }
        }
        $user_data['logs'][] = $d;
    }
    // Masukkan ke report akhir
    $final_report[] = $user_data;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <?php include '../../layout/header.php'; ?>
    <link href="https://fonts.googleapis.com/css2?family=Roboto+Mono:wght@500&family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    
    <style>
        /* --- STYLE MODERN & CLEAN --- */
        body { background-color: #f1f5f9; font-family: 'Inter', sans-serif; }
        
        .card-custom { 
            background: #fff; border-radius: 12px; border: 1px solid #e2e8f0; 
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); margin-bottom: 30px; 
            overflow: hidden;
        }
        
        .user-header {
            background: #f8fafc; padding: 15px 20px; border-bottom: 1px solid #e2e8f0;
            display: flex; justify-content: space-between; align-items: center;
        }
        .user-name { font-weight: 800; font-size: 16px; color: #1e293b; }
        .user-pin { font-family: 'Roboto Mono', monospace; font-size: 12px; color: #64748b; background: #e2e8f0; padding: 2px 8px; border-radius: 4px; }

        /* Mini Stats Grid inside Card */
        .mini-stats {
            display: flex; gap: 10px; padding: 15px 20px; background: #fff; border-bottom: 1px solid #f1f5f9; flex-wrap: wrap;
        }
        .ms-item { flex: 1; min-width: 100px; text-align: center; border-right: 1px solid #f1f5f9; }
        .ms-item:last-child { border-right: none; }
        .ms-val { font-weight: 800; font-size: 18px; display: block; }
        .ms-lbl { font-size: 10px; text-transform: uppercase; color: #94a3b8; font-weight: 600; }
        
        /* Table */
        .table-responsive { width: 100%; overflow-x: auto; }
        .table-custom { width: 100%; border-collapse: collapse; white-space: nowrap; }
        .table-custom th { 
            text-align: center; padding: 10px; background: #f8fafc; color: #64748b; 
            font-size: 11px; text-transform: uppercase; border-bottom: 1px solid #e2e8f0; font-weight: 700; 
        }
        .table-custom td { 
            padding: 10px; border-bottom: 1px solid #f1f5f9; font-size: 13px; 
            color: #334155; vertical-align: middle; text-align: center;
        }
        .table-custom tr:hover td { background: #f8fafc; }
        .text-left { text-align: left !important; }

        /* Badges & Colors */
        .badge { padding: 4px 10px; border-radius: 50px; font-size: 10px; font-weight: 700; display: inline-flex; align-items: center; justify-content: center; min-width: 60px; }
        .badge-full { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
        .badge-half { background: #fef9c3; color: #854d0e; border: 1px solid #fde047; }
        .badge-low { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
        .badge-alpha { background: #f1f5f9; color: #64748b; border: 1px solid #e2e8f0; }
        .badge-libur { background: #fff; color: #94a3b8; border: 1px dashed #cbd5e1; }
        .badge-info { background: #cffafe; color: #155e75; border: 1px solid #a5f3fc; }

        .mono { font-family: 'Roboto Mono', monospace; font-size: 12px; }
        .text-danger { color: #dc2626; font-weight: 700; }
        .text-primary { color: #2563eb; font-weight: 700; }
        .note { font-size: 10px; color: #dc2626; display: block; margin-top: 2px; }
        .tag-info { font-size: 9px; background: #e0f2fe; color: #0369a1; padding: 2px 5px; border-radius: 4px; font-weight: 600; margin-left: 5px; }

        /* Filter Form */
        .filter-container {
            display: flex; gap: 15px; align-items: flex-end; padding: 20px;
            background: #fff; border-radius: 12px; border: 1px solid #e2e8f0;
            margin-bottom: 25px; flex-wrap: wrap;
        }
        .form-group-filter { flex: 1; min-width: 150px; }
        .form-label { font-size: 11px; font-weight: 600; color: #64748b; margin-bottom: 5px; display: block; text-transform: uppercase; }
        .form-input { width: 100%; padding: 10px 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 13px; color: #334155; }
        
        .btn-act { border: none; padding: 10px 20px; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; }
        .btn-dark { background: #1e293b; color: white; }
        .btn-green { background: #10b981; color: white; }

        @media print {
            .no-print, .main-sidebar, .content-header { display: none !important; }
            .content-wrapper { padding: 0 !important; margin: 0 !important; }
            .card-custom { box-shadow: none; border: 1px solid #000; page-break-inside: avoid; }
        }
    </style>
</head>
<body>
    <?php include '../../layout/sidebar.php'; ?>
    
    <div class="content-wrapper" style="padding: 30px;">
        
        <div style="display:flex; justify-content:space-between; align-items:end; margin-bottom:25px;" class="no-print">
            <div>
                <h2 style="font-weight:800; color:#1e293b; margin:0; font-size: 24px;">Laporan Absensi</h2>
                <p style="color:#64748b; font-size:14px; margin-top:5px;">Rekapitulasi kehadiran per karyawan</p>
            </div>
            
            <a href="../cetak/cetak_laporan_absensi.php?tgl_awal=<?= $tgl_awal ?>&tgl_akhir=<?= $tgl_akhir ?>&user_id=<?= $filter_id ?>" 
               target="_blank" class="btn-act btn-green">
               <i class="fa fa-print"></i> Cetak PDF
            </a>
        </div>

        <form method="GET" class="filter-container no-print">
            <div class="form-group-filter">
                <span class="form-label">Tanggal Awal</span>
                <input type="date" name="tgl_awal" value="<?= $tgl_awal ?>" class="form-input">
            </div>
            <div class="form-group-filter">
                <span class="form-label">Tanggal Akhir</span>
                <input type="date" name="tgl_akhir" value="<?= $tgl_akhir ?>" class="form-input">
            </div>
            <div class="form-group-filter" style="flex: 2;">
                <span class="form-label">Karyawan</span>
                <select name="user_id" class="form-input">
                    <option value="">-- Semua Karyawan --</option>
                    <?php foreach($list_users as $u): ?>
                        <option value="<?= $u['id'] ?>" <?= ($filter_id==$u['id'])?'selected':'' ?>><?= $u['fullname'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn-act btn-dark">
                <i class="fa fa-filter"></i> Tampilkan
            </button>
        </form>

        <?php if(empty($final_report)): ?>
            <div class="card-custom" style="padding:40px; text-align:center; color:#94a3b8;">
                <i class="fa fa-folder-open fa-3x" style="margin-bottom:15px;"></i>
                <p>Tidak ada data absensi pada periode ini.</p>
            </div>
        <?php else: ?>
            
            <?php foreach($final_report as $user): ?>
            <div class="card-custom">
                <div class="user-header">
                    <div class="user-name"><?= $user['info']['fullname'] ?></div>
                    <div class="user-pin">PIN: <?= $user['info']['pin'] ?></div>
                </div>

                <div class="mini-stats">
                    <div class="ms-item">
                        <span class="ms-val" style="color:#2563eb;"><?= $user['stats']['hadir'] ?></span>
                        <span class="ms-lbl">Hadir</span>
                    </div>
                    <div class="ms-item">
                        <span class="ms-val" style="color:#dc2626;"><?= $user['stats']['telat'] ?></span>
                        <span class="ms-lbl">Telat</span>
                    </div>
                    <div class="ms-item">
                        <span class="ms-val" style="color:#ca8a04;"><?= $user['stats']['plg_awal'] ?></span>
                        <span class="ms-lbl">Plg Awal</span>
                    </div>
                    <div class="ms-item">
                        <span class="ms-val" style="color:#06b6d4;"><?= $user['stats']['izin'] ?></span>
                        <span class="ms-lbl">Izin</span>
                    </div>
                    <div class="ms-item">
                        <span class="ms-val" style="color:#64748b;"><?= $user['stats']['alpha'] ?></span>
                        <span class="ms-lbl">Alpha</span>
                    </div>
                    <div class="ms-item">
                        <span class="ms-val" style="color:#16a34a;"><?= number_format($user['stats']['lembur']) ?></span>
                        <span class="ms-lbl">Lembur (Mnt)</span>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table-custom">
                        <thead>
                            <tr>
                                <th width="100">Tanggal</th>
                                <th>Masuk</th>
                                <th>Ist.Keluar</th>
                                <th>Ist.Masuk</th>
                                <th>Pulang</th>
                                <th>Telat</th>
                                <th>Durasi</th>
                                <th>Lembur</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($user['logs'] as $r): ?>
                            <tr>
                                <td class="mono"><?= date('d/m/Y', strtotime($r['tanggal'])) ?></td>
                                
                                <td class="mono">
                                    <?= $r['in'] ? date('H:i', strtotime($r['in'])) : '-' ?>
                                    <?= $r['is_shift_siang'] ? '<span class="tag-info">SIANG</span>' : '' ?>
                                </td>
                                
                                <td class="mono" style="color:#64748b;"><?= $r['ist_out'] ? date('H:i', strtotime($r['ist_out'])) : '-' ?></td>
                                <td class="mono" style="color:#64748b;"><?= $r['ist_in'] ? date('H:i', strtotime($r['ist_in'])) : '-' ?></td>
                                
                                <td class="mono" style="font-weight:700;">
                                    <?= $r['out'] ? date('H:i', strtotime($r['out'])) : '-' ?>
                                </td>
                                
                                <td>
                                    <?= $r['telat'] > 0 ? '<span class="text-danger">'.$r['telat'].'m</span>' : '-' ?>
                                </td>
                                
                                <td>
                                    <?php if($r['durasi'] > 0): 
                                        $jam = floor($r['durasi']/60); $mnt = $r['durasi']%60;
                                        echo "<b>{$jam}</b>j <b>{$mnt}</b>m";
                                    else: echo "-"; endif; ?>
                                </td>
                                
                                <td>
                                    <?php 
                                    if($r['lembur'] > 0) {
                                        $lj = floor($r['lembur']/60); $lm = $r['lembur']%60;
                                        echo "<span class='text-primary'>+{$lj}j {$lm}m</span>";
                                        if($r['is_maghrib']) echo "<span class='note'>Pot. Maghrib</span>";
                                    } else { echo "-"; }
                                    ?>
                                </td>
                                
                                <td>
                                    <span class="badge <?= $r['badge'] ?>"><?= $r['status'] ?></span>
                                    <?php foreach($r['ket'] as $k) echo "<span class='note'>$k</span>"; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endforeach; ?>

        <?php endif; ?>
    </div>

    <?php include '../../layout/footer.php'; ?>
</body>
</html>