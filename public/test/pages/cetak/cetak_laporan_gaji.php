<?php 
require_once '../../config/database.php';
// session_start() ditaruh paling atas
session_start();

if (!isset($_SESSION['status']) || $_SESSION['status'] != "login") {
    echo "<script>window.close();</script>";
    exit;
}

date_default_timezone_set('Asia/Jakarta');

// =========================================================================
// 1. DATA PROCESSING (LOGIKA HITUNG GAJI)
// =========================================================================
$tgl_awal  = isset($_GET['tgl_awal']) ? $_GET['tgl_awal'] : date('Y-m-01');
$tgl_akhir = isset($_GET['tgl_akhir']) ? $_GET['tgl_akhir'] : date('Y-m-t');

$q_set = mysqli_query($conn, "SELECT * FROM settings_jam_kerja WHERE id=1");
$aturan = mysqli_fetch_assoc($q_set);

$JAM_MASUK       = $aturan['jam_masuk'];              
$JAM_IST_OUT     = $aturan['jam_istirahat_keluar']; 
$JAM_IST_IN      = $aturan['jam_istirahat_masuk'];  
$JAM_PULANG      = $aturan['jam_pulang'];            
$TARGET_FULL     = (int)$aturan['target_menit_full']; 
$TARGET_HALF     = (int)$aturan['target_menit_half']; 
$MIN_MAKAN       = isset($aturan['min_menit_makan']) ? (int)$aturan['min_menit_makan'] : 330;
$LEM_MIN         = (int)$aturan['lembur_min'];
$LEM_MAX         = (int)$aturan['lembur_max'];
$LEM_POT         = (int)$aturan['lembur_pengurang'];

$r_masuk_start = date('H:i:s', strtotime("$JAM_MASUK - {$aturan['durasi_sebelum_masuk']} minutes"));
$r_masuk_end   = date('H:i:s', strtotime("$JAM_MASUK + {$aturan['durasi_setelah_masuk']} minutes"));
$ts_out = strtotime($JAM_IST_OUT); $ts_in = strtotime($JAM_IST_IN); $ts_mid = $ts_out + (($ts_in - $ts_out) / 2); 
$r_ist_out_start = date('H:i:s', strtotime("$JAM_IST_OUT - 60 minutes")); $r_ist_out_end = date('H:i:s', $ts_mid); 
$r_ist_in_start  = date('H:i:s', $ts_mid + 1); $r_ist_in_end    = date('H:i:s', strtotime("$JAM_IST_IN + 60 minutes"));
$r_plg_start   = date('H:i:s', strtotime("$JAM_PULANG - {$aturan['durasi_sebelum_pulang']} minutes"));
$r_plg_end     = date('H:i:s', strtotime("$JAM_PULANG + {$aturan['durasi_setelah_pulang']} minutes"));

$period_obj = new DatePeriod(new DateTime($tgl_awal), new DateInterval('P1D'), new DateTime($tgl_akhir . ' +1 day'));
$jumlah_sabtu = 0; foreach ($period_obj as $dt) { if ($dt->format('N') == 6) $jumlah_sabtu++; }

// --- FETCH DATA ---
$absen_map = [];
$tgl_akhir_scan = date('Y-m-d H:i:s', strtotime($tgl_akhir . ' +1 day 07:00:00'));
$q_raw = mysqli_query($conn, "SELECT pin, scan_date FROM absensi WHERE scan_date BETWEEN '$tgl_awal 00:00:00' AND '$tgl_akhir_scan' ORDER BY scan_date ASC");
while($r = mysqli_fetch_assoc($q_raw)) {
    $scan_ts = strtotime($r['scan_date']); $tgl_aktual = date('Y-m-d', $scan_ts); $jam = date('H:i:s', $scan_ts);
    $tgl_idx = ($jam >= "00:00:00" && $jam <= "07:00:00") ? date('Y-m-d', strtotime($tgl_aktual . ' -1 day')) : $tgl_aktual;
    $absen_map[$r['pin']][$tgl_idx][] = $jam;
}

$izin_map = [];
$q_izin = mysqli_query($conn, "SELECT user_id, jenis_izin, tanggal FROM perizinan WHERE status='Approved' AND tanggal BETWEEN '$tgl_awal 00:00:00' AND '$tgl_akhir 23:59:59'");
while($row = mysqli_fetch_assoc($q_izin)) { $izin_map[$row['user_id']][date('Y-m-d', strtotime($row['tanggal']))] = $row; }

$lembur_tambahan_map = [];
$q_lt = mysqli_query($conn, "SELECT user_id, SUM(nominal) as total FROM uang_lembur_tambahan WHERE tanggal BETWEEN '$tgl_awal' AND '$tgl_akhir' GROUP BY user_id");
while($lt = mysqli_fetch_assoc($q_lt)) { $lembur_tambahan_map[$lt['user_id']] = $lt['total']; }

// --- PROCESSING ---
$laporan_tetap = []; $laporan_borongan = [];
$stats_tetap = ['gapok'=>0, 'makan'=>0, 'lembur'=>0, 'bonus'=>0, 'umh'=>0, 'pot_lain'=>0, 'kasbon'=>0, 'thp'=>0];
$stats_borongan = ['borongan'=>0, 'bonus'=>0, 'umh'=>0, 'uang_makan_lembur'=>0, 'pot_lain'=>0, 'kasbon'=>0, 'thp'=>0];

$q_users = mysqli_query($conn, "SELECT u.id, u.pin, u.fullname, u.status_karyawan, u.tim_id, u.role, mj.nama_jabatan, COALESCE(g.gaji_pokok, 0) as gaji_pokok, COALESCE(g.uang_makan, 0) as uang_makan, COALESCE(g.gaji_lembur, 0) as gaji_lembur FROM users u LEFT JOIN gaji_karyawan g ON u.id = g.user_id LEFT JOIN master_jabatan mj ON u.jabatan_id = mj.id WHERE u.role IN ('user', 'kepala_bengkel') ORDER BY u.fullname ASC");

while ($u = mysqli_fetch_assoc($q_users)) {
    $uid = $u['id']; $pin = $u['pin'];
    $is_leader = (stripos($u['nama_jabatan'], 'Leader') !== false) ? 1 : 0;
    if ($u['status_karyawan'] == 'Borongan' && !empty($u['tim_id']) && $is_leader == 0) continue; 

    $row = [
        'nama' => $u['fullname'], 'jenis' => $u['status_karyawan'],
        'gapok' => 0, 'makan_hak' => 0, 'lembur_duit' => 0, 'borongan' => 0,
        'kasbon_duit' => 0, 'thp' => 0, 'umh_diambil' => 0, 'uang_makan_lembur' => 0,
        'bonus_lain' => 0, 'potongan_lain' => 0, 'lembur_menit_total' => 0
    ];

    if ($u['status_karyawan'] === 'Tetap' || $u['role'] === 'kepala_bengkel') {
        foreach ($period_obj as $dt) {
            $curr = $dt->format('Y-m-d'); if ($dt->format('N') == 7) continue; 
            $data_izin = isset($izin_map[$uid][$curr]) ? $izin_map[$uid][$curr] : null;

            if (isset($absen_map[$pin][$curr])) {
                $scans = $absen_map[$pin][$curr];
                $in = null; $out = null; $ist_out = null; $ist_in = null; $is_shift_siang = false; $is_half_day = false;
                foreach($scans as $jam) {
                    if ($jam >= $r_masuk_start && $jam <= $r_masuk_end) { if(!$in) $in = $jam; }
                    elseif ($jam >= $r_ist_out_start && $jam <= $r_ist_out_end) { $ist_out = $jam; }
                    elseif ($jam >= $r_ist_in_start && $jam <= $r_ist_in_end) { if(!$in) { $in = $jam; $is_shift_siang = true; } else { $ist_in = $jam; } }
                    elseif (($jam >= $r_plg_start && $jam <= $r_plg_end) || ($jam >= "00:00:00" && $jam <= "07:00:00") || $jam > $r_plg_end) { $out = $jam; }
                }
                if ($in && empty($out) && $data_izin && $data_izin['jenis_izin'] == 'Pulang Cepat') $out = date('H:i:s', strtotime($data_izin['tanggal']));
                if ($in && empty($out) && !empty($ist_out) && empty($ist_in)) {
                     if (floor((strtotime("$curr $ist_out") - strtotime("$curr $in")) / 60) >= $TARGET_HALF) { $out = $ist_out; $is_half_day = true; }
                }
                if ($in && $out) {
                    $ts_in = strtotime("$curr $in"); $ts_out = strtotime("$curr $out"); if($ts_out < $ts_in) $ts_out += 86400;
                    $durasi_menit = floor(($ts_out - $ts_in) / 60);
                    if (!$is_shift_siang) {
                        $ts_ist_out_real = strtotime("$curr $JAM_IST_OUT"); $ts_ist_in_real = strtotime("$curr $JAM_IST_IN");
                        if ($ts_in < $ts_ist_out_real && $ts_out > $ts_ist_in_real && !$is_half_day && !($data_izin && $data_izin['jenis_izin']=='Pulang Cepat')) $durasi_menit -= 60;
                    }
                    $durasi_bersih = max(0, $durasi_menit);
                    if ($durasi_bersih >= $MIN_MAKAN) $row['makan_hak'] += $u['uang_makan'];
                    if ($durasi_bersih >= $TARGET_FULL) {
                        $row['gapok'] += $u['gaji_pokok']; 
                        $menit_lembur = $durasi_bersih - $TARGET_FULL;
                        if ($ts_out > strtotime("$curr 18:00:00")) $menit_lembur -= $LEM_POT; 
                        if ($menit_lembur >= $LEM_MIN) $row['lembur_menit_total'] += min($menit_lembur, $LEM_MAX);
                    } else {
                        $rasio = min(1, $durasi_bersih / $TARGET_FULL);
                        $row['gapok'] += ($u['gaji_pokok'] * $rasio);
                    }
                }
            } else {
                if ($data_izin && ($data_izin['jenis_izin'] == 'Sakit' || $data_izin['jenis_izin'] == 'Cuti')) {
                    $row['gapok'] += $u['gaji_pokok']; 
                }
            }
        }
        $row['lembur_duit'] = ($row['lembur_menit_total'] / 60) * $u['gaji_lembur'];
    } else {
        $res_prod = mysqli_query($conn, "SELECT SUM(total_upah) as upah FROM hasil_produksi_borongan WHERE user_id = '$uid' AND status = 'Approved' AND tanggal BETWEEN '$tgl_awal' AND '$tgl_akhir'");
        $row['borongan'] = mysqli_fetch_assoc($res_prod)['upah'] ?? 0;
    }

    if ($jumlah_sabtu > 0) {
        $res_bon = mysqli_query($conn, "SELECT nominal, tenor, terbayar FROM kasbon WHERE user_id = '$uid' AND status = 'Approved' AND (nominal - terbayar) > 0");
        while ($b = mysqli_fetch_assoc($res_bon)) {
            $cicilan = ceil($b['nominal'] / max(1, $b['tenor']));
            $potongan = min($cicilan * $jumlah_sabtu, ($b['nominal'] - $b['terbayar']));
            $row['kasbon_duit'] += $potongan;
        }
    }

    $res_um = mysqli_query($conn, "SELECT kode_pengajuan, SUM(nominal) as total FROM uang_makan WHERE user_id = '$uid' AND status = 'Approved' AND tanggal BETWEEN '$tgl_awal' AND '$tgl_akhir' GROUP BY kode_pengajuan");
    while($m = mysqli_fetch_assoc($res_um)) {
        if(($m['kode_pengajuan'] ?? 'UMH') == 'UMH') $row['umh_diambil'] += $m['total'];
    }

    if ($is_leader == 1 && !empty($u['tim_id'])) {
        $q_um_agg = mysqli_query($conn, "SELECT SUM(um.nominal) as total FROM uang_makan um JOIN users u ON um.user_id = u.id WHERE u.tim_id = '{$u['tim_id']}' AND u.id != '$uid' AND um.status = 'Approved' AND um.tanggal BETWEEN '$tgl_awal' AND '$tgl_akhir'");
        $row['umh_diambil'] += mysqli_fetch_assoc($q_um_agg)['total'];
    }

    $row['uang_makan_lembur'] = isset($lembur_tambahan_map[$uid]) ? $lembur_tambahan_map[$uid] : 0;

    $q_lain = mysqli_query($conn, "SELECT kategori, SUM(nominal) as total FROM uang_lainlain WHERE user_id='$uid' AND tanggal BETWEEN '$tgl_awal' AND '$tgl_akhir' GROUP BY kategori");
    while($l = mysqli_fetch_assoc($q_lain)) { if ($l['kategori'] == 'Pendapatan') $row['bonus_lain'] = $l['total']; else $row['potongan_lain'] = $l['total']; }

    $row['gapok'] = round($row['gapok'], -3);
    $row['makan_hak'] = round($row['makan_hak'], -3);
    $row['lembur_duit'] = round($row['lembur_duit'], -3);
    $row['borongan'] = round($row['borongan'], -3);
    $row['kasbon_duit'] = round($row['kasbon_duit'], -3);
    $row['umh_diambil'] = round($row['umh_diambil'], -3);
    $row['uang_makan_lembur'] = round($row['uang_makan_lembur'], -3);
    $row['bonus_lain'] = round($row['bonus_lain'], -3);
    $row['potongan_lain'] = round($row['potongan_lain'], -3);

    if ($u['status_karyawan'] === 'Tetap' || $u['role'] === 'kepala_bengkel') {
        $pendapatan = $row['gapok'] + $row['makan_hak'] + $row['lembur_duit'] + $row['bonus_lain'];
        $total_potongan = $row['kasbon_duit'] + $row['potongan_lain'] + $row['umh_diambil'];
        $row['thp'] = max(0, $pendapatan - $total_potongan);
        
        $laporan_tetap[] = $row;
        $stats_tetap['gapok'] += $row['gapok']; $stats_tetap['makan'] += $row['makan_hak'];
        $stats_tetap['lembur'] += $row['lembur_duit']; $stats_tetap['bonus'] += $row['bonus_lain'];
        $stats_tetap['umh'] += $row['umh_diambil']; $stats_tetap['pot_lain'] += $row['potongan_lain'];
        $stats_tetap['kasbon'] += $row['kasbon_duit']; $stats_tetap['thp'] += $row['thp'];
    } else {
        $pendapatan = $row['borongan'] + $row['bonus_lain'];
        $total_potongan = $row['kasbon_duit'] + $row['potongan_lain'] + $row['umh_diambil'] + $row['uang_makan_lembur'];
        $row['thp'] = max(0, $pendapatan - $total_potongan);
        
        $laporan_borongan[] = $row;
        $stats_borongan['borongan'] += $row['borongan']; $stats_borongan['bonus'] += $row['bonus_lain'];
        $stats_borongan['umh'] += $row['umh_diambil']; $stats_borongan['uang_makan_lembur'] += $row['uang_makan_lembur'];
        $stats_borongan['pot_lain'] += $row['potongan_lain']; $stats_borongan['kasbon'] += $row['kasbon_duit'];
        $stats_borongan['thp'] += $row['thp'];
    }
}

function tgl_indo($tanggal){
    $bulan = array (1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember');
    $pecahkan = explode('-', $tanggal);
    return $pecahkan[2] . ' ' . $bulan[ (int)$pecahkan[1] ] . ' ' . $pecahkan[0];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Gaji Karyawan - NORIC RACING</title>
    <link href="https://fonts.googleapis.com/css2?family=Times+New+Roman:wght@400;700&family=Arial:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        /* RESET PRINT */
        @media print {
            @page {
                size: A4 portrait;
                margin: 0; /* HILANGKAN HEADER BROWSER */
            }
            body { margin: 0; }
            .sheet {
                padding: 1.5cm 2cm; 
                width: 100%; height: 100%;
                position: relative; 
                page-break-after: always;
            }
            .print-btn { display: none; }
        }

        body { font-family: 'Arial', sans-serif; font-size: 10px; color: #000; background: #fff; }
        
        @media screen {
            body { background: #f0f0f0; padding: 20px; } 
            .sheet { 
                background: white; width: 21cm; min-height: 29.7cm; 
                margin: 0 auto; padding: 1.5cm 2cm; 
                box-shadow: 0 0 10px rgba(0,0,0,0.1); 
                position: relative;
            } 
        }

        /* WATERMARK */
        .sheet::before {
            content: "";
            position: absolute;
            top: 50%; left: 50%;
            transform: translate(-50%, -50%);
            width: 400px; height: 400px;
            background-image: url('../../assets/image/logo-noric.png');
            background-repeat: no-repeat;
            background-position: center;
            background-size: contain;
            opacity: 0.08; /* Transparansi */
            z-index: 0;
            pointer-events: none;
        }
        .content-layer { position: relative; z-index: 1; }

        /* KOP SURAT */
        .kop-surat { border-bottom: 3px double #000; padding-bottom: 8px; margin-bottom: 15px; display: flex; align-items: center; justify-content: center; position: relative; }
        .kop-logo { width: 70px; height: auto; position: absolute; left: 0; top: 0; }
        .kop-text { text-align: center; width: 100%; padding: 0 80px; }
        .kop-nama { font-family: 'Times New Roman', serif; font-size: 20px; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 3px; }
        .kop-alamat { font-size: 10px; margin-bottom: 2px; }
        .kop-kontak { font-size: 9px; font-style: italic; font-weight: bold; }

        .report-title { text-align: center; font-weight: bold; font-size: 14px; text-decoration: underline; margin-bottom: 3px; text-transform: uppercase; }
        .report-period { text-align: center; font-size: 11px; margin-bottom: 15px; }

        .table-data { width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 9px; background: transparent; }
        .table-data th, .table-data td { border: 1px solid #000; padding: 4px; text-align: right; }
        .table-data th { background-color: #eee; font-weight: 700; text-align: center; text-transform: uppercase; }
        .text-left { text-align: left !important; }
        .text-center { text-align: center !important; }
        .bg-total { background-color: #f0f0f0; font-weight: bold; }
        
        .section-title { font-weight: 700; font-size: 11px; margin-bottom: 5px; text-transform: uppercase; border-bottom: 1px solid #000; display: inline-block; }

        .footer-sign { margin-top: 30px; display: flex; justify-content: space-between; page-break-inside: avoid; }
        .sign-box { width: 200px; text-align: center; }
        .sign-name { margin-top: 60px; font-weight: 700; text-decoration: underline; }

        .print-btn { position: fixed; bottom: 30px; right: 30px; background: #1e293b; color: white; padding: 12px 20px; border-radius: 50px; cursor: pointer; border: none; font-weight: bold; box-shadow: 0 4px 10px rgba(0,0,0,0.3); z-index:9999; }
    </style>
</head>
<body>

<div class="sheet">
    <div class="content-layer">
        <div class="kop-surat">
            <img src="../../assets/image/logo-noric.png" alt="Logo" class="kop-logo">
            <div class="kop-text">
                <div class="kop-nama">NORIC RACING EXHAUST</div>
                <div class="kop-alamat">JL. Ketuhu, Wirasana, Kec. Purbalingga, Jawa Tengah 53318</div>
                <div class="kop-kontak">Telp: +62 821-1358-2244 | Email: admin@noric-management.my.id</div>
            </div>
        </div>

        <div class="report-title">REKAPITULASI GAJI KARYAWAN</div>
        <div class="report-period">Periode: <?= tgl_indo($tgl_awal) ?> s/d <?= tgl_indo($tgl_akhir) ?></div>

        <div class="section-title">I. KARYAWAN TETAP</div>
        <table class="table-data">
            <thead>
                <tr>
                    <th width="3%">No</th>
                    <th width="20%">Nama Karyawan</th>
                    <th>Gaji Pokok</th>
                    <th>U. Makan</th>
                    <th>Lembur</th>
                    <th>Bonus</th>
                    <th>Pot. UM</th>
                    <th>Pot. Lain</th>
                    <th>Kasbon</th>
                    <th>THP</th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($laporan_tetap)): ?>
                    <tr><td colspan="10" class="text-center">Tidak ada data.</td></tr>
                <?php else: $no=1; foreach($laporan_tetap as $r): ?>
                    <tr>
                        <td class="text-center"><?= $no++ ?></td>
                        <td class="text-left"><?= $r['nama'] ?></td>
                        <td><?= number_format($r['gapok']) ?></td>
                        <td><?= number_format($r['makan_hak']) ?></td>
                        <td><?= number_format($r['lembur_duit']) ?></td>
                        <td><?= number_format($r['bonus_lain']) ?></td>
                        <td><?= number_format($r['umh_diambil']) ?></td>
                        <td><?= number_format($r['potongan_lain']) ?></td>
                        <td><?= number_format($r['kasbon_duit']) ?></td>
                        <td style="font-weight:bold;"><?= number_format($r['thp']) ?></td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
            <tfoot>
                <tr class="bg-total">
                    <td colspan="2" class="text-center">TOTAL</td>
                    <td><?= number_format($stats_tetap['gapok']) ?></td>
                    <td><?= number_format($stats_tetap['makan']) ?></td>
                    <td><?= number_format($stats_tetap['lembur']) ?></td>
                    <td><?= number_format($stats_tetap['bonus']) ?></td>
                    <td><?= number_format($stats_tetap['umh']) ?></td>
                    <td><?= number_format($stats_tetap['pot_lain']) ?></td>
                    <td><?= number_format($stats_tetap['kasbon']) ?></td>
                    <td><?= number_format($stats_tetap['thp']) ?></td>
                </tr>
            </tfoot>
        </table>

        <div class="section-title" style="margin-top: 15px;">II. KARYAWAN BORONGAN</div>
        <table class="table-data">
            <thead>
                <tr>
                    <th width="3%">No</th>
                    <th width="20%">Nama Karyawan</th>
                    <th>Borongan</th>
                    <th>Bonus</th>
                    <th>Pot. UM</th>
                    <th>UM Lembur</th>
                    <th>Pot. Lain</th>
                    <th>Kasbon</th>
                    <th>THP</th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($laporan_borongan)): ?>
                    <tr><td colspan="9" class="text-center">Tidak ada data.</td></tr>
                <?php else: $no=1; foreach($laporan_borongan as $r): ?>
                    <tr>
                        <td class="text-center"><?= $no++ ?></td>
                        <td class="text-left"><?= $r['nama'] ?></td>
                        <td><?= number_format($r['borongan']) ?></td>
                        <td><?= number_format($r['bonus_lain']) ?></td>
                        <td><?= number_format($r['umh_diambil']) ?></td>
                        <td><?= number_format($r['uang_makan_lembur']) ?></td>
                        <td><?= number_format($r['potongan_lain']) ?></td>
                        <td><?= number_format($r['kasbon_duit']) ?></td>
                        <td style="font-weight:bold;"><?= number_format($r['thp']) ?></td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
            <tfoot>
                <tr class="bg-total">
                    <td colspan="2" class="text-center">TOTAL</td>
                    <td><?= number_format($stats_borongan['borongan']) ?></td>
                    <td><?= number_format($stats_borongan['bonus']) ?></td>
                    <td><?= number_format($stats_borongan['umh']) ?></td>
                    <td><?= number_format($stats_borongan['uang_makan_lembur']) ?></td>
                    <td><?= number_format($stats_borongan['pot_lain']) ?></td>
                    <td><?= number_format($stats_borongan['kasbon']) ?></td>
                    <td><?= number_format($stats_borongan['thp']) ?></td>
                </tr>
            </tfoot>
        </table>

        <div style="text-align: right; margin-top: 10px; font-size: 12px; font-weight: bold; border-top: 1px dashed #000; padding-top: 5px;">
            GRAND TOTAL PENGELUARAN GAJI: Rp <?= number_format($stats_tetap['thp'] + $stats_borongan['thp']) ?>
        </div>

        <div class="footer-sign">
            <div class="sign-box">
                <div>Purbalingga, <?= tgl_indo(date('Y-m-d')) ?></div>
                <div style="margin-top:5px;">Dibuat Oleh,</div>
                <div class="sign-name">Admin Keuangan</div>
            </div>
            <div class="sign-box">
                <div>Diketahui Oleh,</div>
                <div style="margin-top:5px;">Pimpinan</div>
                <div class="sign-name">......................................</div>
            </div>
        </div>
    </div>
</div>

<button class="print-btn" onclick="window.print()">🖨️ Cetak</button>

</body>
</html>