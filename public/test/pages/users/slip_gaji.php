<?php 
require_once '../../config/database.php';
require_once '../../config/function.php'; 
date_default_timezone_set('Asia/Jakarta');
cek_login(); 

// =================================================================================
// FUNGSI BANTUAN (LOGIKA JADWAL KASBON)
// =================================================================================
function hitung_potongan_berdasarkan_jadwal($filter_awal, $filter_akhir, $tgl_pinjam, $tenor) {
    $start_loan = new DateTime($tgl_pinjam);
    $end_loan   = clone $start_loan;
    $end_loan->modify("+" . $tenor . " weeks"); 
    $end_loan->modify("+1 day"); 

    $f_start = new DateTime($filter_awal);
    $f_end   = new DateTime($filter_akhir);

    $window_start = ($f_start > $start_loan) ? $f_start : $start_loan;
    $window_end   = ($f_end < $end_loan) ? $f_end : $end_loan;

    if ($window_start > $window_end) return 0;

    $count = 0;
    $period = new DatePeriod($window_start, new DateInterval('P1D'), $window_end->modify('+1 second'));
    foreach($period as $dt) {
        if($dt->format('N') == 6) $count++; 
    }
    return $count;
}

// --- 1. CONFIG JAM KERJA ---
$q_set = mysqli_query($conn, "SELECT * FROM settings_jam_kerja WHERE id=1");
$aturan = mysqli_fetch_assoc($q_set);

if (!$aturan) {
    die("<div style='padding:20px; color:red; font-weight:bold;'>Error: Konfigurasi Jam Kerja tidak ditemukan.</div>");
}

// Parameter Waktu & Gaji
$JAM_MASUK      = $aturan['jam_masuk'];              
$JAM_IST_OUT    = $aturan['jam_istirahat_keluar']; 
$JAM_IST_IN     = $aturan['jam_istirahat_masuk'];  
$JAM_PULANG     = $aturan['jam_pulang'];            
$TARGET_FULL    = (int)$aturan['target_menit_full'];  
$TARGET_HALF    = (int)$aturan['target_menit_half'];  
$MIN_MAKAN      = isset($aturan['min_menit_makan']) ? (int)$aturan['min_menit_makan'] : 330;
$LEM_MIN        = (int)$aturan['lembur_min'];         
$LEM_MAX        = (int)$aturan['lembur_max'];         
$LEM_POT        = (int)$aturan['lembur_pengurang'];     

$r_masuk_start = date('H:i:s', strtotime("$JAM_MASUK - {$aturan['durasi_sebelum_masuk']} minutes"));
$r_masuk_end   = date('H:i:s', strtotime("$JAM_MASUK + {$aturan['durasi_setelah_masuk']} minutes"));
$ts_out = strtotime($JAM_IST_OUT); $ts_in  = strtotime($JAM_IST_IN); $ts_mid = $ts_out + (($ts_in - $ts_out) / 2); 
$r_ist_out_start = date('H:i:s', strtotime("$JAM_IST_OUT - 60 minutes")); $r_ist_out_end   = date('H:i:s', $ts_mid); 
$r_ist_in_start  = date('H:i:s', $ts_mid + 1); $r_ist_in_end    = date('H:i:s', strtotime("$JAM_IST_IN + 60 minutes"));
$r_plg_start   = date('H:i:s', strtotime("$JAM_PULANG - {$aturan['durasi_sebelum_pulang']} minutes"));
$r_plg_end     = date('H:i:s', strtotime("$JAM_PULANG + {$aturan['durasi_setelah_pulang']} minutes"));

// --- 2. DATA USER ---
$my_uid = $_SESSION['user_id'];
$q_user = mysqli_query($conn, "SELECT u.*, g.gaji_pokok, g.uang_makan, g.gaji_lembur, mj.nama_jabatan FROM users u LEFT JOIN gaji_karyawan g ON u.id = g.user_id LEFT JOIN master_jabatan mj ON u.jabatan_id = mj.id WHERE u.id = '$my_uid'");
$user_data = mysqli_fetch_assoc($q_user);

$is_mandor_role = (stripos($user_data['nama_jabatan'], 'Leader') !== false) ? 1 : 0;
$is_anggota_borongan = ($user_data['status_karyawan'] == 'Borongan' && !empty($user_data['tim_id']) && $is_mandor_role == 0);
$is_mandor_borongan  = ($user_data['status_karyawan'] == 'Borongan' && $is_mandor_role == 1);

// --- 3. FILTER PERIODE ---
$default_awal  = date('Y-m-d', strtotime('monday this week'));
$default_akhir = date('Y-m-d', strtotime('sunday this week'));
$tgl_awal  = isset($_GET['tgl_awal']) ? $_GET['tgl_awal'] : $default_awal;
$tgl_akhir = isset($_GET['tgl_akhir']) ? $_GET['tgl_akhir'] : $default_akhir;

// --- 4. ENGINE HITUNG GAJI ---
$gaji = [
    'gapok_total' => 0, 'makan_hak' => 0, 'lembur_total_rp' => 0, 'borongan_total' => 0,
    'kasbon_total' => 0, 'um_diambil' => 0, 'pot_um_anggota' => 0,
    'uang_makan_lembur' => 0, 'pot_pro_rata' => 0, 
    'bonus_lain' => 0, 'potongan_lain' => 0, 
    'thp' => 0, 'lembur_menit_total' => 0, 
    'jml_hari_kerja' => 0, 'jml_sakit_cuti' => 0, 
    'detail_log' => [], 'detail_produksi' => [], 'detail_um_anggota' => [], 'detail_lain' => [],
    'detail_kasbon_list' => []
];

// A. LOGIKA PENDAPATAN (KARYAWAN TETAP)
if ($user_data['status_karyawan'] === 'Tetap' || $user_data['role'] === 'kepala_bengkel') {
    $pin = $user_data['pin'];
    $absen_raw = [];
    $tgl_akhir_plus = date('Y-m-d H:i:s', strtotime($tgl_akhir . ' +1 day 07:00:00'));
    $q_absen = mysqli_query($conn, "SELECT scan_date FROM absensi WHERE pin = '$pin' AND scan_date BETWEEN '$tgl_awal 00:00:00' AND '$tgl_akhir_plus' ORDER BY scan_date ASC");
    while($row = mysqli_fetch_assoc($q_absen)) {
        $scan_ts = strtotime($row['scan_date']); $tgl_aktual = date('Y-m-d', $scan_ts); $jam = date('H:i:s', $scan_ts);
        $tgl_idx = ($jam >= "00:00:00" && $jam <= "07:00:00") ? date('Y-m-d', strtotime($tgl_aktual . ' -1 day')) : $tgl_aktual;
        $absen_raw[$tgl_idx][] = $jam;
    }

    $izin_map = [];
    $q_izin = mysqli_query($conn, "SELECT jenis_izin, tanggal, keterangan FROM perizinan WHERE user_id='$my_uid' AND status='Approved' AND tanggal BETWEEN '$tgl_awal 00:00:00' AND '$tgl_akhir 23:59:59'");
    while($row = mysqli_fetch_assoc($q_izin)) { $izin_map[date('Y-m-d', strtotime($row['tanggal']))] = $row; }

    $period = new DatePeriod(new DateTime($tgl_awal), new DateInterval('P1D'), new DateTime($tgl_akhir . ' +1 day'));
    foreach ($period as $dt_obj) {
        $curr = $dt_obj->format('Y-m-d'); if ($dt_obj->format('N') == 7) continue; 
        $log = ['tgl' => $curr, 'status' => 'Alpha', 'ket' => '-'];
        $data_izin = isset($izin_map[$curr]) ? $izin_map[$curr] : null;

        if (isset($absen_raw[$curr])) {
            $scans = $absen_raw[$curr];
            $in = null; $out = null; $ist_out = null; $ist_in = null; $is_shift_siang = false; $is_half_day = false;
            foreach($scans as $jam) {
                if ($jam >= $r_masuk_start && $jam <= $r_masuk_end) { if(!$in) $in = $jam; }
                elseif ($jam >= $r_ist_out_start && $jam <= $r_ist_out_end) { $ist_out = $jam; }
                elseif ($jam >= $r_ist_in_start && $jam <= $r_ist_in_end) { if(!$in) { $in = $jam; $is_shift_siang = true; } else { $ist_in = $jam; } }
                elseif (($jam >= $r_plg_start && $jam <= $r_plg_end) || ($jam >= "00:00:00" && $jam <= "07:00:00") || $jam > $r_plg_end) { $out = $jam; }
            }
            if ($in && empty($out) && $data_izin && $data_izin['jenis_izin'] == 'Pulang Cepat') { $out = date('H:i:s', strtotime($data_izin['tanggal'])); $log['ket'] = "Auto-Out (Izin)"; }
            if ($in && empty($out) && !empty($ist_out) && empty($ist_in)) {
                  if (floor((strtotime("$curr $ist_out") - strtotime("$curr $in")) / 60) >= $TARGET_HALF) { $out = $ist_out; $is_half_day = true; }
            }
            if ($in) {
                $log['status'] = "Hadir";
                if ($out) {
                    $ts_in = strtotime("$curr $in"); $ts_out = strtotime("$curr $out"); if ($ts_out < $ts_in) $ts_out += 86400; 
                    $durasi_menit = floor(($ts_out - $ts_in) / 60);
                    if (!$is_shift_siang) {
                        $ts_ist_out_real = strtotime("$curr $JAM_IST_OUT"); $ts_ist_in_real  = strtotime("$curr $JAM_IST_IN");
                        if ($ts_in < $ts_ist_out_real && $ts_out > $ts_ist_in_real && !$is_half_day && !($data_izin && $data_izin['jenis_izin']=='Pulang Cepat')) { $durasi_menit -= 60; }
                    }
                    $durasi_bersih = max(0, $durasi_menit);
                    if ($durasi_bersih >= $MIN_MAKAN) { $gaji['makan_hak'] += $user_data['uang_makan']; }
                    if ($durasi_bersih >= $TARGET_FULL) {
                        $gaji['gapok_total'] += $user_data['gaji_pokok']; $gaji['jml_hari_kerja'] += 1; $log['status'] = "Full Day";
                        $menit_lembur = $durasi_bersih - $TARGET_FULL;
                        if ($ts_out > strtotime("$curr 18:00:00")) $menit_lembur -= $LEM_POT;
                        if ($menit_lembur >= $LEM_MIN) {
                            $menit_lembur = min($menit_lembur, $LEM_MAX); $gaji['lembur_menit_total'] += $menit_lembur;
                            $gaji['lembur_total_rp'] += ($menit_lembur / 60) * $user_data['gaji_lembur']; $log['status'] = "Lembur";
                        }
                    } else {
                        $rasio = $durasi_bersih / $TARGET_FULL; if($rasio > 1) $rasio = 1;
                        $upah_pro_rata = $user_data['gaji_pokok'] * $rasio; $gaji['gapok_total'] += $upah_pro_rata;
                        $gaji['pot_pro_rata'] += ($user_data['gaji_pokok'] - $upah_pro_rata);
                        $gaji['jml_hari_kerja'] += $rasio; $log['status'] = ($durasi_bersih >= $TARGET_HALF) ? "Half Day" : "Low Hour";
                    }
                    if ($data_izin && $data_izin['jenis_izin'] == 'Pulang Cepat') { $log['status'] = "Izin Plg Cepat"; }
                    $jam_show = floor($durasi_bersih/60); $menit_show = $durasi_bersih%60; $log['ket'] = "Durasi: {$jam_show}j {$menit_show}m";
                } else { $log['status'] = "Invalid"; $log['ket'] = "Lupa Pulang"; }
            }
        } else {
            if ($data_izin) {
                $jenis = $data_izin['jenis_izin']; $log['status'] = $jenis; $log['ket'] = $data_izin['keterangan'];
                if ($jenis == 'Sakit' || $jenis == 'Cuti') { $gaji['gapok_total'] += $user_data['gaji_pokok']; $gaji['jml_sakit_cuti']++; $gaji['jml_hari_kerja']++; }
            }
        }
        $gaji['detail_log'][] = $log;
    }
}

// B. LOGIKA PENDAPATAN (BORONGAN) - REVISI PENGELOMPOKAN
if ($user_data['status_karyawan'] == 'Borongan') {
    $q_prod = mysqli_query($conn, "SELECT * FROM hasil_produksi_borongan WHERE user_id = '$my_uid' AND status = 'Approved' AND tanggal BETWEEN '$tgl_awal' AND '$tgl_akhir' ORDER BY jenis_pekerjaan ASC, tanggal ASC");
    
    $grouped_prod = [];

    while($prod = mysqli_fetch_assoc($q_prod)) {
        $nm_job = $prod['jenis_pekerjaan'];

        // Jika belum ada di array, inisialisasi
        if(!isset($grouped_prod[$nm_job])) {
            $grouped_prod[$nm_job] = [
                'jenis' => $nm_job,
                'total_qty' => 0,
                'total_rp' => 0,
                'dates' => []
            ];
        }

        // Akumulasi data
        $grouped_prod[$nm_job]['total_qty'] += $prod['jumlah'];
        $grouped_prod[$nm_job]['total_rp'] += $prod['total_upah'];
        // Kumpulkan tanggal (format d/m)
        $grouped_prod[$nm_job]['dates'][] = date('d/m', strtotime($prod['tanggal']));

        // Hitung total global
        $gaji['borongan_total'] += $prod['total_upah'];
    }

    // Simpan data yang sudah dikelompokkan ke array utama
    $gaji['detail_produksi'] = $grouped_prod;

    if ($is_anggota_borongan) { $gaji['borongan_total'] = 0; }
}

// C. LOGIKA POTONGAN

// 1. KASBON (LOGIKA JADWAL)
$q_bon = mysqli_query($conn, "SELECT nominal, tenor, tanggal, terbayar FROM kasbon WHERE user_id = '$my_uid' AND status = 'Approved' AND tanggal <= '$tgl_akhir'");

while ($b = mysqli_fetch_assoc($q_bon)) {
    $tenor_minggu = (int)$b['tenor']; if($tenor_minggu < 1) $tenor_minggu = 1;
    $jml_potong = hitung_potongan_berdasarkan_jadwal($tgl_awal, $tgl_akhir, $b['tanggal'], $tenor_minggu);

    if ($jml_potong > 0) {
        $cicilan_per_minggu = ceil($b['nominal'] / $tenor_minggu);
        $total_potong_item = $cicilan_per_minggu * $jml_potong;
        $gaji['kasbon_total'] += $total_potong_item;
        $gaji['detail_kasbon_list'][] = ['nominal' => $total_potong_item, 'tenor' => $tenor_minggu, 'cicilan' => $cicilan_per_minggu];
    }
}

// 2. Lain-lain
$q_uml = mysqli_query($conn, "SELECT SUM(nominal) as total FROM uang_lembur_tambahan WHERE user_id = '$my_uid' AND tanggal BETWEEN '$tgl_awal' AND '$tgl_akhir'");
$d_uml = mysqli_fetch_assoc($q_uml); $gaji['uang_makan_lembur'] = $d_uml['total'] ?? 0;

$q_um = mysqli_query($conn, "SELECT SUM(nominal) as total FROM uang_makan WHERE user_id = '$my_uid' AND status = 'Approved' AND tanggal BETWEEN '$tgl_awal' AND '$tgl_akhir'");
$d_um = mysqli_fetch_assoc($q_um); $um_total_diambil = $d_um['total'] ?? 0;
$gaji['um_diambil'] = $is_anggota_borongan ? 0 : $um_total_diambil;

if ($is_mandor_borongan) {
    $tim_id = $user_data['tim_id']; $q_anggota = mysqli_query($conn, "SELECT id FROM users WHERE tim_id='$tim_id' AND id != '$my_uid'");
    $ids_anggota = []; while($agm = mysqli_fetch_assoc($q_anggota)) { $ids_anggota[] = $agm['id']; }
    if(!empty($ids_anggota)) {
        $ids_str = implode(',', $ids_anggota);
        $q_um_agg = mysqli_query($conn, "SELECT SUM(nominal) as total FROM uang_makan WHERE user_id IN ($ids_str) AND status = 'Approved' AND tanggal BETWEEN '$tgl_awal' AND '$tgl_akhir'");
        $d_agg = mysqli_fetch_assoc($q_um_agg); $gaji['pot_um_anggota'] = $d_agg['total'] ?? 0;
        if ($gaji['pot_um_anggota'] > 0) {
            $q_dt_um = mysqli_query($conn, "SELECT u.fullname, um.nominal, um.tanggal FROM uang_makan um JOIN users u ON um.user_id = u.id WHERE um.user_id IN ($ids_str) AND um.status = 'Approved' AND um.tanggal BETWEEN '$tgl_awal' AND '$tgl_akhir' ORDER BY um.tanggal ASC");
            while($row_um = mysqli_fetch_assoc($q_dt_um)) { $gaji['detail_um_anggota'][] = $row_um; }
        }
    }
}

$q_lain = mysqli_query($conn, "SELECT * FROM uang_lainlain WHERE user_id='$my_uid' AND tanggal BETWEEN '$tgl_awal' AND '$tgl_akhir'");
while($l = mysqli_fetch_assoc($q_lain)) {
    if ($l['kategori'] == 'Pendapatan') { $gaji['bonus_lain'] += $l['nominal']; } else { $gaji['potongan_lain'] += $l['nominal']; }
    $gaji['detail_lain'][] = $l;
}

// ROUNDING
$gaji['gapok_total'] = round($gaji['gapok_total'], -3); $gaji['makan_hak'] = round($gaji['makan_hak'], -3);
$gaji['lembur_total_rp'] = round($gaji['lembur_total_rp'], -3); $gaji['borongan_total'] = round($gaji['borongan_total'], -3);
$gaji['kasbon_total'] = round($gaji['kasbon_total'], -3); $gaji['um_diambil'] = round($gaji['um_diambil'], -3);
$gaji['pot_um_anggota'] = round($gaji['pot_um_anggota'], -3); $gaji['uang_makan_lembur'] = round($gaji['uang_makan_lembur'], -3);
$gaji['bonus_lain'] = round($gaji['bonus_lain'], -3); $gaji['potongan_lain'] = round($gaji['potongan_lain'], -3);

// THP
if ($user_data['status_karyawan'] === 'Tetap' || $user_data['role'] === 'kepala_bengkel') {
    $pendapatan = $gaji['gapok_total'] + $gaji['makan_hak'] + $gaji['lembur_total_rp'] + $gaji['bonus_lain'];
    $potongan   = $gaji['kasbon_total'] + $gaji['um_diambil'] + $gaji['potongan_lain'];
} else {
    $pendapatan = $gaji['borongan_total'] + $gaji['bonus_lain'];
    $potongan   = $gaji['kasbon_total'] + $gaji['um_diambil'] + $gaji['pot_um_anggota'] + $gaji['uang_makan_lembur'] + $gaji['potongan_lain'];
}
$gaji['thp'] = max(0, $pendapatan - $potongan);
$label_periode = date('d M Y', strtotime($tgl_awal)) . " - " . date('d M Y', strtotime($tgl_akhir));
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <?php include '../../layout/header.php'; ?>
    <link href="https://fonts.googleapis.com/css2?family=Courier+Prime:wght@400;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    <style>
        :root { --primary: #1e293b; --accent: #4f46e5; --success: #10b981; --danger: #ef4444; --bg-body: #f8fafc; }
        body { background-color: var(--bg-body); font-family: 'Plus Jakarta Sans', sans-serif; color: #334155; }
        .content-wrapper { padding: 30px 20px; }
        .slip-card { max-width: 600px; margin: 0 auto; background: #fff; border-radius: 16px; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); overflow: hidden; position: relative; border-top: 5px solid var(--accent); }
        .slip-header { text-align: center; padding: 30px 20px 20px; border-bottom: 2px dashed #e2e8f0; background: #fff; }
        .slip-title { font-weight: 800; font-size: 20px; text-transform: uppercase; letter-spacing: 0.5px; color: var(--primary); }
        .slip-subtitle { font-size: 13px; color: #64748b; margin-top: 5px; font-weight: 500; }
        .slip-body { padding: 30px; }
        .section-header { font-size: 11px; text-transform: uppercase; color: #94a3b8; font-weight: 700; margin: 20px 0 10px; letter-spacing: 0.5px; }
        .row-item { display: flex; justify-content: space-between; align-items: center; padding: 10px 0; border-bottom: 1px dashed #f1f5f9; font-size: 14px; }
        .row-item:last-child { border-bottom: none; }
        .row-label { color: #475569; font-weight: 500; }
        .row-val { font-family: 'Courier Prime', monospace; font-weight: 700; color: #1e293b; }
        .val-plus { color: var(--success); } .val-min { color: var(--danger); }
        .total-box { background: #f8fafc; padding: 20px; border-radius: 12px; margin-top: 25px; display: flex; justify-content: space-between; align-items: center; border: 1px solid #e2e8f0; }
        .total-label { font-weight: 800; color: var(--primary); font-size: 14px; }
        .total-val { font-family: 'Courier Prime', monospace; font-weight: 700; color: var(--accent); font-size: 22px; }
        .filter-bar { display: flex; gap: 10px; margin-bottom: 25px; justify-content: center; flex-wrap: wrap; }
        .date-input { padding: 10px 15px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 14px; font-weight: 600; color: #334155; }
        .btn-filter { background: var(--accent); color: white; border: none; padding: 10px 25px; border-radius: 8px; font-size: 14px; font-weight: 700; cursor: pointer; box-shadow: 0 4px 6px rgba(79, 70, 229, 0.2); transition: 0.2s; }
        .btn-filter:hover { transform: translateY(-2px); }
        .log-table { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 13px; }
        .log-table th { text-align: left; padding: 10px; background: #f8fafc; color: #64748b; font-weight: 700; border-radius: 6px; }
        .log-table td { padding: 10px; border-bottom: 1px solid #f1f5f9; color: #334155; }
        .badge { padding: 4px 10px; border-radius: 6px; font-size: 10px; font-weight: 700; text-transform: uppercase; }
        .bg-green { background: #dcfce7; color: #166534; } .bg-red { background: #fee2e2; color: #991b1b; } .bg-blue { background: #dbeafe; color: #1e40af; } .bg-yellow { background: #fef9c3; color: #a16207; }
        .btn-print { display: block; width: 100%; margin-top: 30px; padding: 16px; background: #1e293b; color: white; text-align: center; border-radius: 12px; font-weight: 700; border: none; cursor: pointer; transition: 0.2s; font-size: 14px; }
        .btn-print:hover { background: #0f172a; transform: translateY(-2px); }
        .signature-box { display: none; margin-top: 50px; padding: 0 20px; justify-content: space-between; }
        .sign-col { text-align: center; width: 35%; }
        .sign-title { font-weight: 600; margin-bottom: 60px; font-size: 12px; text-transform: uppercase; color: #64748b; }
        .sign-line { border-top: 1px solid #1e293b; font-weight: 700; padding-top: 5px; font-size: 13px; }
        
        @media print {
            @page { margin: 0; size: auto; }
            body { background: white; -webkit-print-color-adjust: exact; margin: 0; padding: 0; }
            .no-print, .main-sidebar, .content-header, .navbar, .main-footer { display: none !important; }
            .content-wrapper { padding: 20px; margin: 0; }
            .slip-card { box-shadow: none; border: 1px solid #000; border-top: 1px solid #000; max-width: 100%; border-radius: 0; margin: 0; }
            .slip-header { border-bottom: 2px solid #000; background: none !important; }
            .slip-title, .slip-subtitle { color: #000 !important; }
            .btn-print { display: none; }
            .val-plus, .val-min, .total-val { color: #000 !important; }
            .total-box { border: 1px solid #000; background: none; }
            .signature-box { display: flex; }
        }
        @media (max-width: 600px) { .filter-bar { flex-direction: column; } .date-input, .btn-filter { width: 100%; } }
    </style>
</head>
<body>
    <?php include '../../layout/sidebar.php'; ?>
    <div class="content-wrapper">
        <div class="filter-bar no-print">
            <form method="GET" style="display:flex; gap:10px; flex-wrap:wrap; width:100%; justify-content:center;">
                <input type="date" name="tgl_awal" value="<?= $tgl_awal ?>" class="date-input">
                <input type="date" name="tgl_akhir" value="<?= $tgl_akhir ?>" class="date-input">
                <button type="submit" class="btn-filter"><i class="fa fa-search"></i> Tampilkan Slip</button>
            </form>
        </div>

        <div class="slip-card">
            <div class="slip-header">
                <div style="font-weight: 800; font-size: 24px; letter-spacing: 2px; margin-bottom: 5px;">NORIC RACING</div>
                <div class="slip-title">SLIP GAJI KARYAWAN</div>
                <div class="slip-subtitle" style="margin-top:10px; font-weight:700; color:#1e293b;"><?= strtoupper($user_data['fullname']) ?></div>
                <div class="slip-subtitle"><?= $label_periode ?></div>
            </div>
            
            <div class="slip-body">
                <?php if ($is_anggota_borongan): ?>
                    <div style="text-align:center; padding:50px 20px; color:#64748b; background:#f8fafc; border-radius:12px; border:1px dashed #cbd5e1;">
                        <i class="fa fa-users fa-3x" style="margin-bottom:15px; color:#94a3b8;"></i>
                        <h4 style="margin:0 0 5px; color:#1e293b;">ANGGOTA TIM</h4>
                        <p style="margin:0; font-size:13px;">Slip gaji dan pembagian upah dikelola langsung oleh <b>Ketua Tim (Mandor)</b>.</p>
                    </div>
                <?php else: ?>
                    
                    <div class="section-header">PENERIMAAN (INCOME)</div>
                    <?php if ($user_data['status_karyawan'] == 'Tetap' || $user_data['role'] == 'kepala_bengkel'): ?>
                        <div class="row-item">
                            <span class="row-label">Gaji Pokok (<?= number_format($gaji['jml_hari_kerja'], 1) ?> Hari)</span>
                            <span class="row-val val-plus">Rp <?=number_format($gaji['gapok_total'])?></span>
                        </div>
                        <div class="row-item">
                            <span class="row-label">Uang Makan</span>
                            <span class="row-val val-plus">Rp <?=number_format($gaji['makan_hak'])?></span>
                        </div>
                        <div class="row-item">
                            <span class="row-label">Lembur (<?= floor($gaji['lembur_menit_total']/60) ?> Jam)</span>
                            <span class="row-val val-plus">Rp <?=number_format($gaji['lembur_total_rp'])?></span>
                        </div>
                    <?php else: ?>
                        <div class="row-item">
                            <span class="row-label">Hasil Borongan</span>
                            <span class="row-val val-plus">Rp <?=number_format($gaji['borongan_total'])?></span>
                        </div>
                        <?php if(!empty($gaji['detail_produksi'])): ?>
                            <div style="margin:5px 0 15px 15px; font-size:11px; color:#64748b; border-left:2px solid #e2e8f0; padding:0 10px 10px;">
                                <table style="width:100%; border-collapse:collapse; margin-top:5px;">
                                    <thead style="border-bottom:1px solid #cbd5e1; font-weight:700;">
                                        <tr>
                                            <td style="padding:5px 0;">Pekerjaan & Tgl</td>
                                            <td style="text-align:center;">Qty</td>
                                            <td style="text-align:right;">Total</td>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($gaji['detail_produksi'] as $p): ?>
                                            <tr>
                                                <td style="padding:6px 0; vertical-align:top;">
                                                    <span style="font-weight:600; color:#334155;"><?= $p['jenis'] ?></span><br>
                                                    <span style="font-size:10px; color:#94a3b8; line-height:1.2; display:block; margin-top:2px;">
                                                        Tgl: <?= implode(', ', array_unique($p['dates'])) ?>
                                                    </span>
                                                </td>
                                                <td style="text-align:center; padding:6px 0; vertical-align:top; font-weight:600;">
                                                    <?= number_format($p['total_qty']) ?>
                                                </td>
                                                <td style="text-align:right; padding:6px 0; vertical-align:top; font-weight:600; color:#1e293b;">
                                                    <?= number_format($p['total_rp']) ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                        <tr style="border-top:1px dashed #94a3b8; font-weight:700; color:#1e293b;">
                                            <td colspan="2" style="padding-top:8px;">TOTAL HASIL AKHIR</td>
                                            <td style="text-align:right; padding-top:8px;"><?= number_format($gaji['borongan_total']) ?></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>

                    <?php if($gaji['bonus_lain'] > 0): ?>
                        <div class="row-item">
                            <span class="row-label">Bonus & Pendapatan Lain</span>
                            <span class="row-val val-plus">Rp <?=number_format($gaji['bonus_lain'])?></span>
                        </div>
                        <div style="margin:5px 0 15px 15px; font-size:11px; color:#64748b; border-left:2px solid #10b981; padding-left:10px;">
                            <?php foreach($gaji['detail_lain'] as $l): if($l['kategori'] == 'Pendapatan'): ?>
                                <div style="display:flex; justify-content:space-between; margin-bottom:2px;">
                                    <span><?= $l['jenis'] ?> (<?= date('d/m', strtotime($l['tanggal'])) ?>)</span>
                                    <span><?= number_format($l['nominal']) ?></span>
                                </div>
                            <?php endif; endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <div class="section-header">POTONGAN (DEDUCTION)</div>
                    
                    <?php if($gaji['um_diambil'] > 0): ?>
                        <div class="row-item">
                            <span class="row-label">Ambilan Uang Makan</span>
                            <span class="row-val val-min">Rp <?=number_format($gaji['um_diambil'])?></span>
                        </div>
                    <?php endif; ?>
                    
                    <?php if($gaji['pot_um_anggota'] > 0): ?>
                        <div class="row-item">
                            <span class="row-label">Uang Makan Anggota (Mandor)</span>
                            <span class="row-val val-min">Rp <?=number_format($gaji['pot_um_anggota'])?></span>
                        </div>
                        <?php if(!empty($gaji['detail_um_anggota'])): ?>
                            <div style="margin:5px 0 15px 15px; font-size:11px; color:#64748b; border-left:2px solid #ef4444; padding-left:10px;">
                                <?php foreach($gaji['detail_um_anggota'] as $da): ?>
                                    <div style="display:flex; justify-content:space-between; margin-bottom:2px;">
                                        <span>- <?= $da['fullname'] ?> (<?= date('d/m', strtotime($da['tanggal'])) ?>)</span>
                                        <span><?= number_format($da['nominal']) ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>

                    <?php if($user_data['status_karyawan'] == 'Borongan' && $gaji['uang_makan_lembur'] > 0): ?>
                         <div class="row-item">
                            <span class="row-label">UM Lembur (Pengurang)</span>
                            <span class="row-val val-min">Rp <?=number_format($gaji['uang_makan_lembur'])?></span>
                        </div>
                    <?php endif; ?>

                    <?php if($gaji['kasbon_total'] > 0): ?>
                        <div class="row-item">
                            <span class="row-label">Cicilan Kasbon</span>
                            <span class="row-val val-min">Rp <?=number_format($gaji['kasbon_total'])?></span>
                        </div>
                        <?php if(!empty($gaji['detail_kasbon_list'])): ?>
                            <div style="margin:5px 0 15px 15px; font-size:11px; color:#64748b; border-left:2px solid #ef4444; padding-left:10px;">
                                <?php foreach($gaji['detail_kasbon_list'] as $bon): ?>
                                    <div style="display:flex; justify-content:space-between; margin-bottom:2px;">
                                        <span>- Cicilan (Tnr: <?= $bon['tenor'] ?> Mg)</span>
                                        <span><?= number_format($bon['nominal']) ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>

                    <?php if($gaji['potongan_lain'] > 0): ?>
                        <div class="row-item">
                            <span class="row-label">Potongan Lain-Lain (-)</span>
                            <span class="row-val val-min">Rp <?=number_format($gaji['potongan_lain'])?></span>
                        </div>
                        <div style="margin:5px 0 15px 15px; font-size:11px; color:#64748b; border-left:2px solid #ef4444; padding-left:10px;">
                            <?php foreach($gaji['detail_lain'] as $l): if($l['kategori'] == 'Potongan'): ?>
                                <div style="display:flex; justify-content:space-between; margin-bottom:2px;">
                                    <span><?= $l['jenis'] ?> (<?= date('d/m', strtotime($l['tanggal'])) ?>)</span>
                                    <span><?= number_format($l['nominal']) ?></span>
                                </div>
                            <?php endif; endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <?php if($gaji['um_diambil'] == 0 && $gaji['kasbon_total'] == 0 && $gaji['pot_um_anggota'] == 0 && $gaji['potongan_lain'] == 0 && $gaji['uang_makan_lembur'] == 0): ?>
                        <div class="row-item" style="color:#94a3b8; font-style:italic;">
                            <span class="row-label">- Tidak ada potongan -</span>
                            <span class="row-val">0</span>
                        </div>
                    <?php endif; ?>

                    <div class="total-box">
                        <span class="total-label">TAKE HOME PAY</span>
                        <span class="total-val">Rp <?=number_format($gaji['thp'])?></span>
                    </div>

                    <?php if ($user_data['status_karyawan'] == 'Tetap' || $user_data['role'] == 'kepala_bengkel'): ?>
                        <div class="section-header no-print" style="margin-top:25px;">RINGKASAN KEHADIRAN</div>
                        <div class="no-print" style="max-height: 200px; overflow-y:auto; border:1px solid #e2e8f0; border-radius:10px;">
                            <table class="log-table">
                                <thead><tr><th>Tanggal</th><th>Status</th><th>Keterangan</th></tr></thead>
                                <tbody>
                                    <?php foreach($gaji['detail_log'] as $l): ?>
                                        <tr>
                                            <td style="font-weight:600;"><?= date('d/m', strtotime($l['tgl'])) ?></td>
                                            <td>
                                                <?php 
                                                    $b = 'bg-red'; 
                                                    if(in_array($l['status'], ['Hadir','Full Day','Lembur'])) $b = 'bg-green';
                                                    elseif(in_array($l['status'], ['Sakit','Cuti','Izin Plg Cepat'])) $b = 'bg-blue';
                                                    elseif(in_array($l['status'], ['Half Day','Low Hour'])) $b = 'bg-yellow';
                                                ?>
                                                <span class="badge <?= $b ?>"><?= $l['status'] ?></span>
                                            </td>
                                            <td><?= $l['ket'] ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>

                    <div class="signature-box">
                        <div class="sign-col">
                            <div class="sign-title">Penerima</div>
                            <div class="sign-line"><?= $user_data['fullname'] ?></div>
                        </div>
                        <div class="sign-col">
                            <div class="sign-title">Admin Keuangan</div>
                            <div class="sign-line">...........................</div>
                        </div>
                    </div>

                    <button onclick="window.print()" class="btn-print no-print">
                        <i class="fa fa-print"></i> CETAK SLIP GAJI
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>