<?php 
require_once '../../config/database.php';
session_start();

if (!isset($_SESSION['status']) || $_SESSION['status'] != "login") {
    header("Location: ../../login.php");
    exit;
}

date_default_timezone_set('Asia/Jakarta');

// =========================================================================
// 1. CONFIG & SETTINGS
// =========================================================================
$q_set = mysqli_query($conn, "SELECT * FROM settings_jam_kerja WHERE id=1");
$aturan = mysqli_fetch_assoc($q_set);

if (!$aturan) { die("Pengaturan jam kerja belum diset."); }

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

$r_masuk_start = date('H:i:s', strtotime("$JAM_MASUK - {$aturan['durasi_sebelum_masuk']} minutes"));
$r_masuk_end   = date('H:i:s', strtotime("$JAM_MASUK + {$aturan['durasi_setelah_masuk']} minutes"));
$ts_out = strtotime($JAM_IST_OUT); $ts_in  = strtotime($JAM_IST_IN); $ts_mid = $ts_out + (($ts_in - $ts_out) / 2); 
$r_ist_out_start = date('H:i:s', strtotime("$JAM_IST_OUT - 60 minutes")); $r_ist_out_end   = date('H:i:s', $ts_mid); 
$r_ist_in_start  = date('H:i:s', $ts_mid + 1); $r_ist_in_end    = date('H:i:s', strtotime("$JAM_IST_IN + 60 minutes"));
$r_plg_start   = date('H:i:s', strtotime("$JAM_PULANG - {$aturan['durasi_sebelum_pulang']} minutes"));
$r_plg_end     = date('H:i:s', strtotime("$JAM_PULANG + {$aturan['durasi_setelah_pulang']} minutes"));

// =========================================================================
// 2. DATA FETCHING
// =========================================================================
$tgl_awal  = isset($_GET['tgl_awal']) ? $_GET['tgl_awal'] : date('Y-m-d');
$tgl_akhir = isset($_GET['tgl_akhir']) ? $_GET['tgl_akhir'] : date('Y-m-d');
$filter_id = isset($_GET['user_id']) ? $_GET['user_id'] : '';

$sql_user = "SELECT id, fullname, pin, jabatan_id FROM users WHERE role IN ('user', 'kepala_toko', 'kepala_bengkel')";
if(!empty($filter_id)) { $sql_user .= " AND id='$filter_id'"; }
$sql_user .= " ORDER BY fullname ASC";
$q_users = mysqli_query($conn, $sql_user);
$list_users = []; while($r = mysqli_fetch_assoc($q_users)) $list_users[] = $r;

$absen_map = [];
$tgl_akhir_scan = date('Y-m-d H:i:s', strtotime($tgl_akhir . ' +1 day 07:00:00'));
$sql_absen = "SELECT pin, scan_date FROM absensi WHERE scan_date BETWEEN '$tgl_awal 00:00:00' AND '$tgl_akhir_scan' ORDER BY scan_date ASC";
$q_raw = mysqli_query($conn, $sql_absen);
while($r = mysqli_fetch_assoc($q_raw)) {
    $scan_ts = strtotime($r['scan_date']);
    $tgl_aktual = date('Y-m-d', $scan_ts);
    $jam = date('H:i:s', $scan_ts);
    $tgl_idx = ($jam >= "00:00:00" && $jam <= "07:00:00") ? date('Y-m-d', strtotime($tgl_aktual . ' -1 day')) : $tgl_aktual;
    $absen_map[$r['pin']][$tgl_idx][] = $jam;
}

$izin_map = [];
$sql_izin = "SELECT user_id, jenis_izin, tanggal, keterangan FROM perizinan WHERE status='Approved' AND tanggal BETWEEN '$tgl_awal 00:00:00' AND '$tgl_akhir 23:59:59'";
$q_izin = mysqli_query($conn, $sql_izin);
while($row = mysqli_fetch_assoc($q_izin)) {
    $tgl_only = date('Y-m-d', strtotime($row['tanggal']));
    $izin_map[$row['user_id']][$tgl_only] = $row;
}

// =========================================================================
// 3. PROCESSING
// =========================================================================
$final_report = [];

foreach($list_users as $u) {
    $uid = $u['id']; $pin = $u['pin'];
    $user_data = [
        'info' => $u,
        'rekap' => ['hadir'=>0, 'telat'=>0, 'plg_awal'=>0, 'lembur'=>0, 'izin'=>0, 'alpha'=>0, 'libur'=>0],
        'logs' => []
    ];

    $period_obj = new DatePeriod(new DateTime($tgl_awal), new DateInterval('P1D'), new DateTime($tgl_akhir . ' +1 day'));

    foreach($period_obj as $dt) {
        $curr_date = $dt->format('Y-m-d');
        $is_libur  = ($dt->format('N') == 7); 
        
        $d = [
            'tanggal' => $curr_date, 
            'in' => null, 'ist_out' => null, 'ist_in' => null, 'out' => null,
            'telat' => 0, 'durasi' => 0, 'lembur' => 0,
            'status' => $is_libur ? 'Libur' : 'Alpha',
            'is_shift_siang' => false,
            'ket' => [] 
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
                $d['out'] = date('H:i:s', strtotime($data_izin['tanggal'])); $d['ket'][] = "Izin Plg Cepat";
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
                $d['status'] = 'HADIR'; $user_data['rekap']['hadir']++;
                $ts_in = strtotime("$curr_date ".$d['in']);
                $jam_target = $d['is_shift_siang'] ? $JAM_IST_IN : $JAM_MASUK;
                $ts_target = strtotime("$curr_date $jam_target");
                if($ts_in > ($ts_target + ($TOL_TELAT * 60))) {
                    $d['telat'] = floor(($ts_in - $ts_target) / 60);
                    $user_data['rekap']['telat']++;
                }

                if ($d['out']) {
                    $ts_out = strtotime("$curr_date ".$d['out']);
                    if($ts_out < $ts_in) $ts_out += 86400;

                    if (!$d['is_shift_siang'] && !in_array("Half Day", $d['ket'])) {
                        $ts_target_plg = strtotime("$curr_date $JAM_PULANG");
                        if ($ts_out < $ts_target_plg) {
                            $selisih = floor(($ts_target_plg - $ts_out) / 60);
                            if ($selisih > $TOL_PLG_AWAL && !($data_izin && $data_izin['jenis_izin'] == 'Pulang Cepat')) {
                                $user_data['rekap']['plg_awal']++;
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
                        if ($ts_out > strtotime("$curr_date 18:00:00")) { $raw_lembur -= $LEM_POT; }
                        if ($raw_lembur >= $LEM_MIN) {
                            $d['lembur'] = min($raw_lembur, $LEM_MAX);
                            $user_data['rekap']['lembur'] += $d['lembur'];
                        }
                    }
                } else { $d['ket'][] = "Lupa Pulang"; }
            }
        } else {
            if ($is_libur) { $user_data['rekap']['libur']++; } 
            else {
                if ($data_izin) {
                    $d['status'] = strtoupper($data_izin['jenis_izin']);
                    $user_data['rekap']['izin']++;
                } else {
                    $user_data['rekap']['alpha']++;
                }
            }
        }
        $user_data['logs'][] = $d;
    }
    $final_report[] = $user_data;
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
    <title>Laporan Absensi Per Karyawan</title>
    <link href="https://fonts.googleapis.com/css2?family=Arial:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        /* CSS RESET & PRINT SETTINGS */
        * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; box-sizing: border-box; }
        
        @media print {
            @page {
                size: A4 portrait;
                margin: 0; /* HILANGKAN HEADER BROWSER */
            }
            body { 
                margin: 0; 
                padding: 0;
            }
            .sheet {
                /* MARGIN HALAMAN MANUAL */
                padding: 1.5cm 2cm; 
                width: 100%;
                height: 100%;
                position: relative; /* Container Watermark */
                page-break-after: always;
            }
            .print-btn { display: none; }
        }

        body { font-family: 'Arial', sans-serif; font-size: 10pt; color: #000; background: #fff; }
        
        @media screen {
            body { background: #f0f0f0; padding: 20px; }
            .sheet { 
                background: white; width: 210mm; min-height: 297mm; 
                margin: 0 auto 20px auto; padding: 1.5cm 2cm; 
                box-shadow: 0 0 10px rgba(0,0,0,0.1); 
                position: relative; /* Container Watermark */
            }
        }

        /* WATERMARK LOGO */
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
            opacity: 0.08; /* Transparansi Halus */
            z-index: 0;
            pointer-events: none;
        }
        
        /* ISI AGAR DI ATAS WATERMARK */
        .content-layer { position: relative; z-index: 1; }

        /* KOP SURAT */
        .kop-surat { border-bottom: 2px solid #000; padding-bottom: 8px; margin-bottom: 15px; display: flex; align-items: center; }
        .kop-logo { width: 70px; height: auto; margin-right: 15px; }
        .kop-text { flex: 1; text-align: center; }
        .kop-nama { font-size: 18pt; font-weight: 800; text-transform: uppercase; margin-bottom: 2px; }
        .kop-alamat { font-size: 9pt; margin-bottom: 2px; }
        .kop-kontak { font-size: 9pt; font-style: italic; font-weight: bold; }

        .report-title { text-align: center; font-weight: bold; font-size: 12pt; text-decoration: underline; margin-bottom: 5px; text-transform: uppercase; }
        .report-period { text-align: center; font-size: 10pt; margin-bottom: 20px; }

        .emp-info { width: 100%; margin-bottom: 10px; font-size: 10pt; }
        .emp-info td { padding: 2px 0; }
        .emp-label { font-weight: bold; width: 100px; }

        .table-data { width: 100%; border-collapse: collapse; margin-bottom: 15px; font-size: 9pt; background: transparent; }
        .table-data th, .table-data td { border: 1px solid #000; padding: 4px; text-align: center; }
        .table-data th { background-color: #e0e0e0; font-weight: bold; }
        .bg-libur { background-color: rgba(249, 249, 249, 0.7); color: #888; }
        .bg-alpha { background-color: rgba(255, 240, 240, 0.7); }
        .text-red { color: red; font-weight: bold; }

        .summary-row { display: flex; justify-content: space-between; border: 1px solid #000; padding: 8px; font-size: 9pt; margin-bottom: 30px; background: rgba(253, 253, 253, 0.8); }
        .sum-box { text-align: center; }
        .sum-val { font-weight: bold; font-size: 11pt; display: block; }
        .sum-lbl { font-size: 8pt; text-transform: uppercase; color: #555; }

        .print-btn { position: fixed; bottom: 30px; right: 30px; background: #1e293b; color: white; padding: 15px 25px; border-radius: 50px; cursor: pointer; border: none; font-weight: bold; box-shadow: 0 4px 15px rgba(0,0,0,0.3); z-index: 9999;}
    </style>
</head>
<body>

<button class="print-btn" onclick="window.print()">🖨️ Cetak Laporan</button>

<?php foreach($final_report as $user): ?>
    <div class="sheet">
        <div class="content-layer">
            <div class="kop-surat">
                <img src="../../assets/image/logo-noric.png" alt="Logo" class="kop-logo">
                <div class="kop-text">
                    <div class="kop-nama">NORIC RACING EXHAUST</div>
                    <div class="kop-alamat">JL. Ketuhu, Wirasana, Kec. Purbalingga, Kabupaten Purbalingga, Jawa Tengah 53318</div>
                    <div class="kop-kontak">Telp: +62 821-1358-2244 | Email: admin@noric-management.my.id</div>
                </div>
            </div>

            <div class="report-title">LAPORAN ABSENSI PERORANGAN</div>
            <div class="report-period">Periode: <?= tgl_indo($tgl_awal) ?> s/d <?= tgl_indo($tgl_akhir) ?></div>

            <table class="emp-info">
                <tr>
                    <td class="emp-label">Nama</td>
                    <td>: <?= strtoupper($user['info']['fullname']) ?></td>
                    <td class="emp-label" style="text-align:right;">PIN ID</td>
                    <td style="width:50px;">: <?= $user['info']['pin'] ?></td>
                </tr>
            </table>

            <table class="table-data">
                <thead>
                    <tr>
                        <th width="12%">Tanggal</th>
                        <th width="10%">Masuk</th>
                        <th width="10%">Istirahat</th>
                        <th width="10%">Pulang</th>
                        <th width="8%">Telat</th>
                        <th width="10%">Durasi</th>
                        <th width="10%">Lembur</th>
                        <th width="15%">Ket</th>
                        <th width="15%">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($user['logs'] as $log): ?>
                        <?php 
                            $row_class = '';
                            if($log['status'] == 'Libur') $row_class = 'bg-libur';
                            elseif($log['status'] == 'Alpha') $row_class = 'bg-alpha';
                        ?>
                        <tr class="<?= $row_class ?>">
                            <td><?= date('d/m/Y', strtotime($log['tanggal'])) ?></td>
                            <td><?= $log['in'] ? date('H:i', strtotime($log['in'])) : '-' ?></td>
                            <td style="font-size:8pt;">
                                <?php 
                                    if($log['ist_out']) echo date('H:i', strtotime($log['ist_out']));
                                    if($log['ist_in']) echo '<br>'.date('H:i', strtotime($log['ist_in']));
                                    if(!$log['ist_out'] && !$log['ist_in']) echo '-';
                                ?>
                            </td>
                            <td style="font-weight:bold;"><?= $log['out'] ? date('H:i', strtotime($log['out'])) : '-' ?></td>
                            <td class="<?= $log['telat']>0 ? 'text-red' : '' ?>"><?= $log['telat']>0 ? $log['telat'].'m' : '' ?></td>
                            <td>
                                <?php if($log['durasi'] > 0) {
                                        $j = floor($log['durasi']/60); $m = $log['durasi']%60; echo "{$j}j {$m}m";
                                    } else { echo "-"; } ?>
                            </td>
                            <td>
                                <?php if($log['lembur'] > 0): $lj = floor($log['lembur']/60); $lm = $log['lembur']%60; echo "+{$lj}j {$lm}m";
                                else: echo "-"; endif; ?>
                            </td>
                            <td style="font-size:8pt; text-align:left;"><?= !empty($log['ket']) ? implode(", ", $log['ket']) : '' ?></td>
                            <td style="font-size:8pt;"><?= $log['status'] == 'HADIR' ? '<b>HADIR</b>' : $log['status'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="summary-row">
                <div class="sum-box"><span class="sum-val"><?= $user['rekap']['hadir'] ?></span><span class="sum-lbl">Hadir</span></div>
                <div class="sum-box"><span class="sum-val text-red"><?= $user['rekap']['telat'] ?></span><span class="sum-lbl">Terlambat</span></div>
                <div class="sum-box"><span class="sum-val text-red"><?= $user['rekap']['plg_awal'] ?></span><span class="sum-lbl">Plg Awal</span></div>
                <div class="sum-box"><span class="sum-val"><?= $user['rekap']['izin'] ?></span><span class="sum-lbl">Izin/Sakit</span></div>
                <div class="sum-box"><span class="sum-val"><?= $user['rekap']['alpha'] ?></span><span class="sum-lbl">Alpha</span></div>
                <div class="sum-box"><span class="sum-val"><?= number_format($user['rekap']['lembur']) ?> mnt</span><span class="sum-lbl">Total Lembur</span></div>
            </div>

            <div style="display:flex; justify-content:space-between; margin-top:10px; page-break-inside: avoid;">
                <div style="text-align:center; width:30%;">
                    <div style="margin-bottom:60px;">Karyawan,</div>
                    <div style="border-bottom:1px solid #000; padding-bottom:5px; font-weight:bold;"><?= $user['info']['fullname'] ?></div>
                </div>
                <div style="text-align:center; width:30%;">
                    <div style="margin-bottom:60px;">Admin HRD,</div>
                    <div style="border-bottom:1px solid #000; padding-bottom:5px; font-weight:bold;">( .............................. )</div>
                </div>
            </div>
        </div> </div> <?php endforeach; ?>

</body>
</html>