<?php 
// --- 1. CONFIG & SESSION ---
error_reporting(E_ALL);
ini_set('display_errors', 0); 

require_once '../../config/database.php';
date_default_timezone_set('Asia/Jakarta');
cek_login(); 

// Helper Functions
function get_greeting() {
    $h = date('H');
    if ($h < 11) return "Selamat Pagi";
    if ($h < 15) return "Selamat Siang";
    if ($h < 19) return "Selamat Sore";
    return "Selamat Malam";
}

// --- 2. ATURAN JAM KERJA (DARI DB) ---
$q_set = mysqli_query($conn, "SELECT * FROM settings_jam_kerja WHERE id=1");
$aturan = mysqli_fetch_assoc($q_set);

if (!$aturan) {
    die("Pengaturan jam kerja belum disetting oleh Admin.");
}

$JAM_MASUK_NORMAL   = $aturan['jam_masuk'];             
$JAM_IST_OUT        = $aturan['jam_istirahat_keluar']; 
$JAM_IST_IN         = $aturan['jam_istirahat_masuk'];  
$JAM_PULANG_NORMAL  = $aturan['jam_pulang'];            

$TARGET_FULL        = (int)$aturan['target_menit_full'];  
$TARGET_HALF        = (int)$aturan['target_menit_half'];  
$TOL_TELAT          = (int)$aturan['toleransi_telat'];    
$TOL_PLG_AWAL       = (int)$aturan['toleransi_pulang_awal']; 
$LEM_MIN            = (int)$aturan['lembur_min'];         
$LEM_MAX            = (int)$aturan['lembur_max'];         
$LEM_POT            = (int)$aturan['lembur_pengurang'];   

// --- 3. HITUNG RANGE WAKTU SCAN ---
$r_masuk_start = date('H:i:s', strtotime("$JAM_MASUK_NORMAL - {$aturan['durasi_sebelum_masuk']} minutes"));
$r_masuk_end   = date('H:i:s', strtotime("$JAM_MASUK_NORMAL + {$aturan['durasi_setelah_masuk']} minutes"));

// Range Istirahat
$ts_out = strtotime($JAM_IST_OUT);
$ts_in  = strtotime($JAM_IST_IN);
$ts_mid = $ts_out + (($ts_in - $ts_out) / 2); 

$r_ist_out_start = date('H:i:s', strtotime("$JAM_IST_OUT - 60 minutes"));
$r_ist_out_end   = date('H:i:s', $ts_mid); 

$r_ist_in_start  = date('H:i:s', $ts_mid + 1); 
$r_ist_in_end    = date('H:i:s', strtotime("$JAM_IST_IN + 60 minutes"));

// Range Pulang
$r_plg_start   = date('H:i:s', strtotime("$JAM_PULANG_NORMAL - {$aturan['durasi_sebelum_pulang']} minutes"));
$r_plg_end     = date('H:i:s', strtotime("$JAM_PULANG_NORMAL + {$aturan['durasi_setelah_pulang']} minutes"));


// --- 4. DATA USER & PERIODE ---
$my_id = $_SESSION['user_id'];
$u_res = mysqli_query($conn, "SELECT fullname, pin FROM users WHERE id='$my_id'");
$me = mysqli_fetch_assoc($u_res);
$fullname = explode(' ', $me['fullname'])[0]; 
$full_name_db = $me['fullname'];
$my_pin = $me['pin'];

// Periode Minggu Ini
$dt = new DateTime();
$dt->setISODate((int)date('o'), (int)date('W'));
$tgl_awal  = $dt->format('Y-m-d'); 
$dt->modify('+6 days');
$tgl_akhir = $dt->format('Y-m-d'); 
$label_periode = date('d M Y', strtotime($tgl_awal)) . " - " . date('d M Y', strtotime($tgl_akhir));

// --- 5. QUERY SCAN LOGS ---
$tgl_akhir_plus = date('Y-m-d H:i:s', strtotime($tgl_akhir . ' +1 day 07:00:00'));
$absen_raw = [];
$sql = "SELECT scan_date FROM absensi 
        WHERE pin = '$my_pin' 
        AND scan_date BETWEEN '$tgl_awal 00:00:00' AND '$tgl_akhir_plus' 
        ORDER BY scan_date ASC";
$q_absen = mysqli_query($conn, $sql);

while($row = mysqli_fetch_assoc($q_absen)) {
    $scan_ts = strtotime($row['scan_date']);
    $tgl_aktual = date('Y-m-d', $scan_ts);
    $jam = date('H:i:s', $scan_ts);
    
    // Logika Cross-Day
    if ($jam >= "00:00:00" && $jam <= "07:00:00") {
        $tgl_idx = date('Y-m-d', strtotime($tgl_aktual . ' -1 day'));
    } else {
        $tgl_idx = $tgl_aktual;
    }
    $absen_raw[$tgl_idx][] = $jam;
}

// --- 6. QUERY IZIN ---
$izin_map = [];
$sql_izin = "SELECT jenis_izin, tanggal, keterangan FROM perizinan 
             WHERE user_id='$my_id' 
             AND status='Approved' 
             AND tanggal BETWEEN '$tgl_awal 00:00:00' AND '$tgl_akhir 23:59:59'";
$q_izin = mysqli_query($conn, $sql_izin);

while($row = mysqli_fetch_assoc($q_izin)) {
    $tgl_only = date('Y-m-d', strtotime($row['tanggal']));
    $izin_map[$tgl_only] = $row;
}

// --- 7. LOGIKA CALCULATOR ---
$report_data = [];
$stats = ['hadir'=>0, 'telat'=>0, 'pulang_awal'=>0, 'lembur'=>0, 'izin'=>0];

$period = new DatePeriod(new DateTime($tgl_awal), new DateInterval('P1D'), new DateTime($tgl_akhir.' +1 day'));

foreach($period as $dt_obj) {
    $curr_date = $dt_obj->format('Y-m-d');
    $is_libur = ($dt_obj->format('N') == 7); 

    $d = [
        'tanggal' => $curr_date,
        'nama' => $full_name_db,
        'in' => null, 'ist_out' => null, 'ist_in' => null, 'out' => null,
        'telat' => 0, 'plg_awal' => 0, 'durasi' => 0, 'lembur' => 0,
        'status' => $is_libur ? 'Libur' : 'Alpha',
        'badge' => $is_libur ? 'bg-gray-100 text-gray-500' : 'bg-red-100 text-red-600',
        'is_shift_siang' => false,
        'is_maghrib' => false,
        'warning_list' => [] 
    ];

    // Cek Izin Hari Ini
    $data_izin = isset($izin_map[$curr_date]) ? $izin_map[$curr_date] : null;

    if(isset($absen_raw[$curr_date])) {
        $scans = $absen_raw[$curr_date];
        
        // A. DETEKSI POSISI SCAN
        foreach($scans as $jam) {
            if ($jam >= $r_masuk_start && $jam <= $r_masuk_end) { if(!$d['in']) $d['in'] = $jam; }
            elseif ($jam >= $r_ist_out_start && $jam <= $r_ist_out_end) { $d['ist_out'] = $jam; }
            elseif ($jam >= $r_ist_in_start && $jam <= $r_ist_in_end) { 
                if(!$d['in']) { $d['in'] = $jam; $d['is_shift_siang'] = true; } else { $d['ist_in'] = $jam; }
            }
            elseif (($jam >= $r_plg_start && $jam <= $r_plg_end) || ($jam >= "00:00:00" && $jam <= "07:00:00")) { $d['out'] = $jam; }
            elseif ($jam > $r_plg_end) { $d['out'] = $jam; }
        }

        // --- UPDATE 1: JIKA IZIN PULANG CEPAT TAPI LUPA SCAN PULANG ---
        if ($d['in'] && empty($d['out']) && $data_izin && $data_izin['jenis_izin'] == 'Pulang Cepat') {
            $d['out'] = date('H:i:s', strtotime($data_izin['tanggal']));
            $d['warning_list'][] = "Auto-Out (Izin)";
        }

        // --- UPDATE 2: LOGIKA HALF DAY (FIXED) ---
        // Syarat: Masuk OK, Out Kosong, Ada Ist_Out, TAPI TIDAK ADA Ist_In
        if ($d['in'] && empty($d['out']) && !empty($d['ist_out']) && empty($d['ist_in'])) {
            
            $t_in = strtotime("$curr_date ".$d['in']); 
            $t_pot = strtotime("$curr_date ".$d['ist_out']);
            $durasi_sementara = floor(($t_pot - $t_in) / 60);

            // Cek target menit Half Day
            if ($durasi_sementara >= $TARGET_HALF) {
                // Buffer Waktu (Realtime Fix)
                $jam_sekarang = date('H:i:s');
                $batas_wajar  = date('H:i:s', strtotime("$JAM_IST_IN + 30 minutes"));
                
                // Hanya vonis Half Day jika bukan hari ini, atau hari ini tapi jam sudah lewat batas wajar
                if ($curr_date != date('Y-m-d') || ($curr_date == date('Y-m-d') && $jam_sekarang > $batas_wajar)) {
                    $d['out'] = $d['ist_out'];
                    $d['warning_list'][] = "Half Day"; 
                }
            }
        }

        // C. HITUNG STATISTIK FINAL
        if ($d['in']) {
            $d['status'] = 'HADIR'; 
            $d['badge'] = 'bg-emerald-100 text-emerald-700';
            $stats['hadir']++;
            
            $ts_in = strtotime("$curr_date ".$d['in']);
            
            // Cek Telat
            $jam_target = $d['is_shift_siang'] ? $JAM_IST_IN : $JAM_MASUK_NORMAL;
            $ts_target_masuk = strtotime("$curr_date $jam_target");

            if ($ts_in > ($ts_target_masuk + ($TOL_TELAT * 60))) {
                $d['telat'] = floor(($ts_in - $ts_target_masuk) / 60);
                $stats['telat']++;
            }

            if ($d['out']) {
                $ts_out = strtotime("$curr_date ".$d['out']);
                if ($ts_out < $ts_in) $ts_out += 86400; 

                // --- UPDATE 3: CEK PULANG AWAL DENGAN LOGIKA IZIN ---
                if (!$d['is_shift_siang'] && !in_array("Half Day", $d['warning_list'])) {
                    $ts_target_pulang = strtotime("$curr_date $JAM_PULANG_NORMAL");
                    if ($ts_out < $ts_target_pulang) {
                        $selisih = floor(($ts_target_pulang - $ts_out) / 60);
                        if($selisih > $TOL_PLG_AWAL) {
                            // Cek Izin Override
                            if ($data_izin && $data_izin['jenis_izin'] == 'Pulang Cepat') {
                                $d['status'] = 'IZIN PLG CEPAT';
                                $d['badge'] = 'bg-cyan-100 text-cyan-700'; // Warna Info
                                $jam_izin_show = date('H:i', strtotime($data_izin['tanggal']));
                                $d['warning_list'][] = "Approved: $jam_izin_show";
                            } else {
                                // Jika tidak ada izin, baru dianggap pelanggaran
                                $d['plg_awal'] = $selisih;
                                $stats['pulang_awal']++;
                            }
                        }
                    }
                }

                $durasi_menit = floor(($ts_out - $ts_in) / 60);

                // Potongan Istirahat
                if (!$d['is_shift_siang']) {
                    $ts_ist_out = strtotime("$curr_date $JAM_IST_OUT");
                    $ts_ist_in  = strtotime("$curr_date $JAM_IST_IN");
                    if ($ts_in < $ts_ist_out && $ts_out > $ts_ist_in && !in_array("Half Day", $d['warning_list'])) {
                        $durasi_menit -= 60; 
                    }
                }

                $d['durasi'] = max(0, $durasi_menit);

                // Kalkulasi Lembur
                if ($d['durasi'] > $TARGET_FULL) {
                    $menit_lembur = $d['durasi'] - $TARGET_FULL;
                    if ($ts_out > strtotime("$curr_date 18:00:00")) {
                        $menit_lembur -= $LEM_POT;
                        $d['is_maghrib'] = true;
                    }
                    if ($menit_lembur >= $LEM_MIN) {
                        $d['lembur'] = min($menit_lembur, $LEM_MAX);
                        $stats['lembur'] += $d['lembur'];
                    }
                }
                
                // Badge Durasi (Hanya jika Status masih HADIR, bukan Izin)
                if ($d['status'] == 'HADIR') {
                    if ($d['durasi'] < $TARGET_HALF) $d['badge'] = 'bg-rose-100 text-rose-700';
                    elseif ($d['durasi'] < $TARGET_FULL) $d['badge'] = 'bg-amber-100 text-amber-700';
                }

            } else {
                $d['badge'] = 'bg-rose-100 text-rose-700';
                $d['warning_list'][] = "Lupa Absen Pulang";
            }
        } else {
            // --- UPDATE 4: LOGIKA ALPHA vs IZIN FULL DAY ---
            if (!$is_libur) {
                if ($data_izin) {
                    $d['status'] = strtoupper($data_izin['jenis_izin']);
                    $d['badge'] = 'bg-cyan-100 text-cyan-700';
                    $stats['izin']++;
                }
            }
        }
    }
    $report_data[] = $d;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <?php include '../../layout/header.php'; ?>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    <style>
        :root {
            --primary: #4f46e5; --success: #10b981; --danger: #ef4444; 
            --warning: #f59e0b; --info: #06b6d4; --gray-200: #e5e7eb; --gray-800: #1f2937;
        }
        body { background-color: #f3f4f6; font-family: 'Inter', sans-serif; color: var(--gray-800); }
        .content-wrapper { padding: 30px; }
        .card { background: #fff; border-radius: 16px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); border: 1px solid var(--gray-200); overflow: hidden; }
        .welcome-banner { background: linear-gradient(135deg, #4f46e5 0%, #3b82f6 100%); border-radius: 16px; padding: 30px; color: #fff; margin-bottom: 30px; display: flex; justify-content: space-between; align-items: center; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: #fff; border-radius: 16px; padding: 20px; display: flex; align-items: center; gap: 15px; border: 1px solid var(--gray-200); }
        .stat-icon { width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 20px; }
        
        .icon-blue { background: #eff6ff; color: #3b82f6; }
        .icon-orange { background: #fff7ed; color: #f97316; }
        .icon-red { background: #fef2f2; color: #ef4444; }
        .icon-green { background: #ecfdf5; color: #10b981; }
        .icon-cyan { background: #cffafe; color: #06b6d4; }

        .custom-table { width: 100%; border-collapse: separate; border-spacing: 0; }
        .custom-table th { background: #f9fafb; padding: 16px; text-align: left; font-size: 12px; font-weight: 600; color: #6b7280; text-transform: uppercase; border-bottom: 1px solid var(--gray-200); }
        .custom-table td { padding: 16px; background: #fff; border-bottom: 1px solid var(--gray-200); font-size: 14px; }
        
        /* Badges */
        .badge { display: inline-flex; align-items: center; padding: 4px 10px; border-radius: 9999px; font-size: 11px; font-weight: 600; }
        .bg-emerald-100 { background-color: #d1fae5; } .text-emerald-700 { color: #047857; }
        .bg-rose-100 { background-color: #ffe4e6; } .text-rose-700 { color: #be123c; }
        .bg-amber-100 { background-color: #fef3c7; } .text-amber-700 { color: #b45309; }
        .bg-cyan-100 { background-color: #cffafe; } .text-cyan-700 { color: #0e7490; }
        
        .shift-badge { font-size: 10px; background: #eff6ff; color: #1d4ed8; padding: 2px 6px; border-radius: 4px; font-weight: 600; margin-left: 6px; border: 1px solid #dbeafe; }
        .warning-text { font-size: 11px; color: #dc2626; font-weight: 600; display: block; }
        
        /* Mobile */
        @media (max-width: 768px) {
            .welcome-banner { flex-direction: column; text-align: center; gap: 20px; }
            .text-right { text-align: center; }
            .table-responsive { overflow-x: auto; }
        }
    </style>
</head>
<body>
    <?php include '../../layout/sidebar.php'; ?>
    
    <div class="content-wrapper">
        <div class="welcome-banner">
            <div>
                <h1 style="margin:0; font-size: 24px; font-weight: 700;"><?=get_greeting()?>, <?=$fullname?>!</h1>
                <p style="margin:5px 0 0; opacity:0.9; font-size:14px;">Ringkasan aktivitas absensi Anda minggu ini.</p>
            </div>
            <div class="text-right">
                <div id="clock" style="font-family:monospace; font-size:32px; font-weight:700;">00:00:00</div>
                <div style="font-size:13px; opacity:0.9;"><?=date('l, d F Y')?></div>
            </div>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon icon-blue"><i class="fa-solid fa-calendar-check"></i></div>
                <div><h4 style="margin:0; font-size:24px;"><?=$stats['hadir']?></h4><span style="font-size:11px; color:#6b7280; font-weight:700;">HADIR</span></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon icon-orange"><i class="fa-solid fa-clock"></i></div>
                <div><h4 style="margin:0; font-size:24px;"><?=$stats['telat']?></h4><span style="font-size:11px; color:#6b7280; font-weight:700;">TELAT</span></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon icon-red"><i class="fa-solid fa-sign-out"></i></div>
                <div><h4 style="margin:0; font-size:24px;"><?=$stats['pulang_awal']?></h4><span style="font-size:11px; color:#6b7280; font-weight:700;">PLG AWAL</span></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon icon-cyan"><i class="fa-solid fa-file-medical"></i></div>
                <div><h4 style="margin:0; font-size:24px;"><?=$stats['izin']?></h4><span style="font-size:11px; color:#6b7280; font-weight:700;">IZIN/SAKIT</span></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon icon-green"><i class="fa-solid fa-bolt"></i></div>
                <div><h4 style="margin:0; font-size:24px;"><?=number_format($stats['lembur'])?></h4><span style="font-size:11px; color:#6b7280; font-weight:700;">LEMBUR (M)</span></div>
            </div>
        </div>

        <div class="card">
            <div style="padding: 20px 25px; border-bottom: 1px solid #e5e7eb; display:flex; justify-content:space-between;">
                <h3 style="margin:0; font-size:16px;">Riwayat Absensi Mingguan</h3>
                <span style="color:#6b7280; font-size:13px;"><?= $label_periode ?></span>
            </div>
            <div class="table-responsive">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Masuk</th>
                            <th>Istirahat</th>
                            <th>Pulang</th>
                            <th class="text-center">Telat</th>
                            <th>Durasi</th>
                            <th>Lembur</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($report_data as $row): ?>
                        <tr>
                            <td>
                                <div style="font-weight:700; color:#374151;"><?=date('d M', strtotime($row['tanggal']))?></div>
                                <div style="font-size:11px; color:#9ca3af;"><?=date('l', strtotime($row['tanggal']))?></div>
                            </td>
                            <td>
                                <?php if($row['in']): ?>
                                    <span style="font-family:monospace; color:#4f46e5; font-weight:700;"><?=date('H:i', strtotime($row['in']))?></span>
                                    <?= $row['is_shift_siang'] ? '<span class="shift-badge">SIANG</span>' : '' ?>
                                <?php else: ?> - <?php endif; ?>
                            </td>
                            <td style="font-size:11px; color:#6b7280;">
                                <div>Out: <?= $row['ist_out'] ? date('H:i', strtotime($row['ist_out'])) : '-' ?></div>
                                <div>In: <?= $row['ist_in'] ? date('H:i', strtotime($row['ist_in'])) : '-' ?></div>
                            </td>
                            <td>
                                <?php if($row['out']): ?>
                                    <span style="font-family:monospace; color:#ef4444; font-weight:700;"><?=date('H:i', strtotime($row['out']))?></span>
                                <?php else: ?> - <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <?= $row['telat'] > 0 ? '<span style="color:#ef4444; font-weight:700;">'.$row['telat'].'m</span>' : '-' ?>
                            </td>
                            <td>
                                <?php if($row['durasi'] > 0): ?>
                                    <div style="font-weight:700;"><?= floor($row['durasi']/60) ?>j <?= $row['durasi']%60 ?>m</div>
                                    <?= $row['plg_awal'] > 0 ? '<span class="warning-text">Awal '.$row['plg_awal'].'m</span>' : '' ?>
                                <?php else: ?> - <?php endif; ?>
                            </td>
                            <td>
                                <?php if($row['lembur'] > 0): ?>
                                    <span style="color:#059669; font-weight:700;">+<?= floor($row['lembur']/60) ?>j <?= $row['lembur']%60 ?>m</span>
                                    <?= $row['is_maghrib'] ? '<div class="warning-text" style="font-size:10px;">(Pot. Maghrib)</div>' : '' ?>
                                <?php else: ?> - <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge <?= $row['badge'] ?>"><?= $row['status'] ?></span>
                                <?php foreach($row['warning_list'] as $w) echo "<span class='warning-text' style='font-size:10px; margin-top:2px;'>• $w</span>"; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <?php include '../../layout/footer.php'; ?>
    <script>
        function updateClock() {
            const now = new Date();
            document.getElementById('clock').innerText = now.toLocaleTimeString('id-ID', {hour:'2-digit', minute:'2-digit', second:'2-digit'});
        }
        setInterval(updateClock, 1000); updateClock();
    </script>
</body>
</html>