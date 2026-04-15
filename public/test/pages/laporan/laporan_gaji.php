<?php 
require_once '../../config/database.php';
require_once '../../config/function.php';
date_default_timezone_set('Asia/Jakarta');
session_start();

if (!isset($_SESSION['status'])) { header("Location: ../../login.php"); exit; }
if ($_SESSION['role'] !== 'admin') { header("Location: ../dashboard.php"); exit; }

$swal_script = "";

// =================================================================================
// FUNGSI BANTUAN LOGIKA
// =================================================================================

// 1. UPDATE DATABASE (Tetap pakai logika sisa hutang agar akurat)
function proses_potong_kasbon_db($conn, $kategori_karyawan, $tgl_awal, $tgl_akhir) {
    // Hitung Sabtu (Trigger Gajian)
    $period_obj = new DatePeriod(new DateTime($tgl_awal), new DateInterval('P1D'), new DateTime($tgl_akhir . ' +1 day'));
    $jumlah_sabtu = 0; 
    foreach ($period_obj as $dt) { if ($dt->format('N') == 6) $jumlah_sabtu++; }

    if ($jumlah_sabtu == 0) return 0;

    // Cari kasbon yang BELUM LUNAS untuk dipotong
    $sql = "SELECT k.id as kasbon_id, k.nominal, k.tenor, k.terbayar 
            FROM kasbon k 
            JOIN users u ON k.user_id = u.id 
            WHERE u.status_karyawan = '$kategori_karyawan' 
            AND k.status = 'Approved' 
            AND (k.nominal - k.terbayar) > 0"; // Kunci update: Hanya yang punya sisa
    
    $query = mysqli_query($conn, $sql);
    $total_updated = 0;

    while($row = mysqli_fetch_assoc($query)) {
        $tenor = (int)$row['tenor']; if($tenor < 1) $tenor = 1;
        $sisa_hutang = $row['nominal'] - $row['terbayar'];
        $cicilan_per_minggu = ceil($row['nominal'] / $tenor);
        
        // Berapa kali potong periode ini?
        $potongan_saat_ini  = $cicilan_per_minggu * $jumlah_sabtu;
        $potongan_fix = min($potongan_saat_ini, $sisa_hutang);

        if($potongan_fix > 0) {
            $id_bon = $row['kasbon_id'];
            $sql_update = "UPDATE kasbon SET 
                           terbayar = terbayar + $potongan_fix,
                           status_lunas = CASE WHEN (nominal - terbayar) <= 0 THEN 'Lunas' ELSE 'Belum' END
                           WHERE id = '$id_bon'";
            mysqli_query($conn, $sql_update);
            $total_updated++;
        }
    }
    return $total_updated;
}

// 2. TAMPILAN LAPORAN (Pakai logika JADWAL, agar history tidak hilang meski lunas)
function hitung_potongan_berdasarkan_jadwal($filter_awal, $filter_akhir, $tgl_pinjam, $tenor) {
    // Tentukan Tanggal Mulai & Selesai Pinjaman
    $start_loan = new DateTime($tgl_pinjam);
    $end_loan   = clone $start_loan;
    $end_loan->modify("+" . $tenor . " weeks"); // Pinjaman berlaku selama tenor minggu
    // Tambah toleransi 1 hari untuk covering
    $end_loan->modify("+1 day"); 

    $f_start = new DateTime($filter_awal);
    $f_end   = new DateTime($filter_akhir);

    // Cari Irisan Tanggal (Intersection) antara Filter Laporan dengan Masa Pinjaman
    $window_start = ($f_start > $start_loan) ? $f_start : $start_loan;
    $window_end   = ($f_end < $end_loan) ? $f_end : $end_loan;

    // Jika tidak beririsan (Laporan bulan depan, pinjaman bulan lalu), return 0
    if ($window_start > $window_end) return 0;

    // Hitung ada berapa Sabtu di dalam irisan tersebut
    $count = 0;
    // Fix: DatePeriod tidak include end date secara default, jadi tambah 1 detik/hari
    $period = new DatePeriod($window_start, new DateInterval('P1D'), $window_end->modify('+1 second'));
    foreach($period as $dt) {
        if($dt->format('N') == 6) $count++; // 6 = Sabtu
    }
    return $count;
}

// =================================================================================
// PROSES POST FORM
// =================================================================================

// A. SIMPAN GAJI KARYAWAN TETAP (CASH)
if (isset($_POST['save_tetap_cash'])) {
    $nominal    = (int)$_POST['nominal']; 
    $admin_id   = $_SESSION['user_id'];
    $tgl        = date('Y-m-d');
    $p_awal     = $_POST['hidden_tgl_awal'];
    $p_akhir    = $_POST['hidden_tgl_akhir'];
    $ket        = "PENGGAJIAN KARYAWAN TETAP ($p_awal s/d $p_akhir)"; 
    
    if ($nominal > 0) {
        $q = "INSERT INTO transaksi_kas (user_id, tanggal, jenis, keterangan, nominal, metode) 
              VALUES ('$admin_id', '$tgl', 'Keluar', '$ket', '$nominal', 'Cash')";
        if (mysqli_query($conn, $q)) {
            proses_potong_kasbon_db($conn, 'Tetap', $p_awal, $p_akhir);
            $swal_script = "Swal.fire({icon: 'success', title: 'Berhasil!', text: 'Gaji Tetap tercatat & Saldo Kasbon diperbarui.', timer: 2000, showConfirmButton: false});";
        } else {
            $swal_script = "Swal.fire({icon: 'error', title: 'Gagal', text: 'Database Error'});";
        }
    }
}

// B. SIMPAN GAJI KARYAWAN BORONGAN (ATM)
if (isset($_POST['save_borongan_atm'])) {
    $nominal    = (int)$_POST['nominal']; 
    $admin_id   = $_SESSION['user_id'];
    $tgl        = date('Y-m-d');
    $p_awal     = $_POST['hidden_tgl_awal'];
    $p_akhir    = $_POST['hidden_tgl_akhir'];
    $ket        = "PENGGAJIAN KARYAWAN BORONGAN ($p_awal s/d $p_akhir)"; 
    $metode     = "ATM"; 
    
    if ($nominal > 0) {
        $q = "INSERT INTO transaksi_kas (user_id, tanggal, jenis, keterangan, nominal, metode) 
              VALUES ('$admin_id', '$tgl', 'Keluar', '$ket', '$nominal', '$metode')";
        if (mysqli_query($conn, $q)) {
            proses_potong_kasbon_db($conn, 'Borongan', $p_awal, $p_akhir);
            $swal_script = "Swal.fire({icon: 'success', title: 'Berhasil!', text: 'Gaji Borongan tercatat & Saldo Kasbon diperbarui.', timer: 2000, showConfirmButton: false});";
        } else {
            $swal_script = "Swal.fire({icon: 'error', title: 'Gagal', text: 'Database Error'});";
        }
    }
}

// =================================================================================
// CONFIG & SETTINGS
// =================================================================================
$q_set = mysqli_query($conn, "SELECT * FROM settings_jam_kerja WHERE id=1");
$aturan = mysqli_fetch_assoc($q_set);
if (!$aturan) { die("<script>alert('Harap atur jam kerja dahulu!'); window.location='../dashboard.php';</script>"); }

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
$ts_out = strtotime($JAM_IST_OUT); $ts_in = strtotime($JAM_IST_IN);
$ts_mid = $ts_out + (($ts_in - $ts_out) / 2); 
$r_ist_out_start = date('H:i:s', strtotime("$JAM_IST_OUT - 60 minutes")); $r_ist_out_end = date('H:i:s', $ts_mid); 
$r_ist_in_start  = date('H:i:s', $ts_mid + 1); $r_ist_in_end = date('H:i:s', strtotime("$JAM_IST_IN + 60 minutes"));
$r_plg_start   = date('H:i:s', strtotime("$JAM_PULANG - {$aturan['durasi_sebelum_pulang']} minutes"));
$r_plg_end     = date('H:i:s', strtotime("$JAM_PULANG + {$aturan['durasi_setelah_pulang']} minutes"));

// =================================================================================
// FILTER PERIODE
// =================================================================================
$default_awal  = date('Y-m-d', strtotime('monday this week'));
$default_akhir = date('Y-m-d', strtotime('sunday this week'));
$tgl_awal  = isset($_GET['tgl_awal']) ? $_GET['tgl_awal'] : $default_awal;
$tgl_akhir = isset($_GET['tgl_akhir']) ? $_GET['tgl_akhir'] : $default_akhir;

$period_obj = new DatePeriod(new DateTime($tgl_awal), new DateInterval('P1D'), new DateTime($tgl_akhir . ' +1 day'));

// =================================================================================
// FETCH DATA
// =================================================================================
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
while($lt = mysqli_fetch_assoc($q_lt)) {
    $lembur_tambahan_map[$lt['user_id']] = $lt['total'];
}

// =================================================================================
// PROCESSING
// =================================================================================
$laporan_tetap = []; $laporan_borongan = [];
$stats_tetap = ['gapok'=>0, 'makan'=>0, 'lembur'=>0, 'denda'=>0, 'kasbon'=>0, 'umh'=>0, 'uang_makan_lembur'=>0, 'bonus'=>0, 'pot_lain'=>0, 'thp'=>0];
$stats_borongan = ['borongan'=>0, 'kasbon'=>0, 'umh'=>0, 'uang_makan_lembur'=>0, 'bonus'=>0, 'pot_lain'=>0, 'thp'=>0];

$q_users = mysqli_query($conn, "SELECT u.id, u.pin, u.fullname, u.status_karyawan, u.tim_id, u.role, mj.nama_jabatan, COALESCE(g.gaji_pokok, 0) as gaji_pokok, COALESCE(g.uang_makan, 0) as uang_makan, COALESCE(g.gaji_lembur, 0) as gaji_lembur FROM users u LEFT JOIN gaji_karyawan g ON u.id = g.user_id LEFT JOIN master_jabatan mj ON u.jabatan_id = mj.id WHERE u.role IN ('user', 'kepala_bengkel') ORDER BY u.fullname ASC");

while ($u = mysqli_fetch_assoc($q_users)) {
    $uid = $u['id']; $pin = $u['pin'];
    $is_leader = (stripos($u['nama_jabatan'], 'Leader') !== false) ? 1 : 0;
    if ($u['status_karyawan'] == 'Borongan' && !empty($u['tim_id']) && $is_leader == 0) continue; 

    $row = [
        'nama' => $u['fullname'], 'jenis' => $u['status_karyawan'],
        'gapok' => 0, 'makan_hak' => 0, 'lembur_duit' => 0, 'borongan' => 0,
        'lembur_menit_total' => 0, 'kasbon_duit' => 0, 'thp' => 0, 'hari_kerja' => 0, 'info_extra' => '', 
        'pot_pro_rata' => 0, 'umh_diambil' => 0, 'uang_makan_lembur' => 0,
        'bonus_lain' => 0, 'potongan_lain' => 0,
        'list_kasbon_detail' => [] 
    ];

    if($u['role'] == 'kepala_bengkel') $row['info_extra'] = 'Kepala Bengkel';
    elseif($u['status_karyawan'] == 'Borongan') $row['info_extra'] = $is_leader ? 'Mandor' : 'Perorangan';

    // A. HITUNG JAM KERJA
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
                        $row['gapok'] += $u['gaji_pokok']; $row['hari_kerja'] += 1;
                        $menit_lembur = $durasi_bersih - $TARGET_FULL;
                        if ($ts_out > strtotime("$curr 18:00:00")) $menit_lembur -= $LEM_POT; 
                        if ($menit_lembur >= $LEM_MIN) $row['lembur_menit_total'] += min($menit_lembur, $LEM_MAX);
                    } else {
                        $rasio = min(1, $durasi_bersih / $TARGET_FULL);
                        $gaji_dapat = $u['gaji_pokok'] * $rasio;
                        $row['gapok'] += $gaji_dapat;
                        $row['pot_pro_rata'] += ($u['gaji_pokok'] - $gaji_dapat);
                        $row['hari_kerja'] += $rasio;
                    }
                }
            } else {
                if ($data_izin && ($data_izin['jenis_izin'] == 'Sakit' || $data_izin['jenis_izin'] == 'Cuti')) {
                    $row['gapok'] += $u['gaji_pokok']; $row['hari_kerja'] += 1;
                }
            }
        }
        $row['lembur_duit'] = ($row['lembur_menit_total'] / 60) * $u['gaji_lembur'];
    } else {
        $res_prod = mysqli_query($conn, "SELECT SUM(total_upah) as upah FROM hasil_produksi_borongan WHERE user_id = '$uid' AND status = 'Approved' AND tanggal BETWEEN '$tgl_awal' AND '$tgl_akhir'");
        $row['borongan'] = mysqli_fetch_assoc($res_prod)['upah'] ?? 0;
    }

    // =================================================================
    // [LOGIKA KASBON - TAMPILAN] (FIXED: Pakai Jadwal, Bukan Sisa Saldo)
    // =================================================================
    $kasbon_murni = 0;
    
    // Ambil SEMUA kasbon approved (Termasuk yang sudah lunas, asalkan tanggalnya <= filter)
    // Agar kita bisa cek apakah minggu lalu (saat belum lunas) ada jadwal bayar
    $res_bon = mysqli_query($conn, "SELECT nominal, tenor, tanggal, terbayar FROM kasbon WHERE user_id = '$uid' AND status = 'Approved' AND tanggal <= '$tgl_akhir'");
    
    while ($b = mysqli_fetch_assoc($res_bon)) {
        $tenor_minggu = (int)$b['tenor']; if($tenor_minggu < 1) $tenor_minggu = 1; 

        // Hitung berapa kali potong untuk periode laporan yang sedang dilihat
        $jml_potong_kali_ini = hitung_potongan_berdasarkan_jadwal($tgl_awal, $tgl_akhir, $b['tanggal'], $tenor_minggu);
        
        if ($jml_potong_kali_ini > 0) {
            $cicilan_per_minggu = ceil($b['nominal'] / $tenor_minggu);
            $total_potong_row = $cicilan_per_minggu * $jml_potong_kali_ini;

            // Tampilkan di UI
            $kasbon_murni += $total_potong_row;
            $info_k = number_format($total_potong_row/1000) . "k (Tnr:" . $tenor_minggu . ")";
            $row['list_kasbon_detail'][] = $info_k;
        }
    }
    $row['kasbon_duit'] = $kasbon_murni; 

    // --- Uang Makan Anggota & Lain-lain ---
    $res_um = mysqli_query($conn, "SELECT kode_pengajuan, SUM(nominal) as total FROM uang_makan WHERE user_id = '$uid' AND status = 'Approved' AND tanggal BETWEEN '$tgl_awal' AND '$tgl_akhir' GROUP BY kode_pengajuan");
    while($m = mysqli_fetch_assoc($res_um)) {
        $kode = $m['kode_pengajuan'] ? $m['kode_pengajuan'] : 'UMH';
        if($kode == 'UMH') {
            $row['umh_diambil'] += $m['total'];
        } 
    }

    if ($is_leader == 1 && !empty($u['tim_id'])) {
        $rincian_anggota = [];
        $total_um_anggota = 0;
        $q_um_agg = mysqli_query($conn, "SELECT u.fullname, SUM(um.nominal) as total FROM uang_makan um JOIN users u ON um.user_id = u.id WHERE u.tim_id = '{$u['tim_id']}' AND u.id != '$uid' AND um.status = 'Approved' AND um.tanggal BETWEEN '$tgl_awal' AND '$tgl_akhir' GROUP BY um.user_id");
        while($member = mysqli_fetch_assoc($q_um_agg)) {
            $total_um_anggota += $member['total'];
            $rincian_anggota[] = explode(' ', $member['fullname'])[0] . " (" . number_format($member['total']/1000) . "k)";
        }
        $row['umh_diambil'] += $total_um_anggota;
        if(!empty($rincian_anggota)) {
            $row['list_um_anggota'] = implode(", ", $rincian_anggota);
        }
    }

    $row['uang_makan_lembur'] = isset($lembur_tambahan_map[$uid]) ? $lembur_tambahan_map[$uid] : 0;

    $q_lain = mysqli_query($conn, "SELECT kategori, SUM(nominal) as total FROM uang_lainlain WHERE user_id='$uid' AND tanggal BETWEEN '$tgl_awal' AND '$tgl_akhir' GROUP BY kategori");
    while($l = mysqli_fetch_assoc($q_lain)) { if ($l['kategori'] == 'Pendapatan') $row['bonus_lain'] = $l['total']; else $row['potongan_lain'] = $l['total']; }

    // ROUNDING
    $row['gapok']             = round($row['gapok'], -3);
    $row['makan_hak']         = round($row['makan_hak'], -3);
    $row['lembur_duit']       = round($row['lembur_duit'], -3);
    $row['borongan']          = round($row['borongan'], -3);
    $row['kasbon_duit']       = round($row['kasbon_duit'], -3);
    $row['umh_diambil']       = round($row['umh_diambil'], -3);
    $row['uang_makan_lembur'] = round($row['uang_makan_lembur'], -3);
    $row['bonus_lain']        = round($row['bonus_lain'], -3);
    $row['potongan_lain']     = round($row['potongan_lain'], -3);
    $row['pot_pro_rata']      = round($row['pot_pro_rata'], -3);

    // THP Final
    if ($u['status_karyawan'] === 'Tetap' || $u['role'] === 'kepala_bengkel') {
        $pendapatan = $row['gapok'] + $row['makan_hak'] + $row['lembur_duit'] + $row['bonus_lain'];
        $total_potongan = $row['kasbon_duit'] + $row['potongan_lain'] + $row['umh_diambil'];
    } else {
        $pendapatan = $row['borongan'] + $row['bonus_lain'];
        $total_potongan = $row['kasbon_duit'] + $row['potongan_lain'] + $row['umh_diambil'] + $row['uang_makan_lembur'];
    }
    
    $row['thp'] = max(0, $pendapatan - $total_potongan);

    // Array Output
    if ($u['status_karyawan'] === 'Tetap' || $u['role'] === 'kepala_bengkel') {
        $laporan_tetap[] = $row;
        $stats_tetap['gapok'] += $row['gapok']; $stats_tetap['makan'] += $row['makan_hak'];
        $stats_tetap['lembur'] += $row['lembur_duit']; $stats_tetap['denda'] += $row['pot_pro_rata'];
        $stats_tetap['kasbon'] += $row['kasbon_duit']; 
        $stats_tetap['umh'] += $row['umh_diambil'];
        $stats_tetap['uang_makan_lembur'] += $row['uang_makan_lembur'];
        $stats_tetap['bonus'] += $row['bonus_lain']; $stats_tetap['pot_lain'] += $row['potongan_lain']; 
        $stats_tetap['thp'] += $row['thp']; 
    } else {
        $laporan_borongan[] = $row;
        $stats_borongan['borongan'] += $row['borongan']; $stats_borongan['kasbon'] += $row['kasbon_duit'];
        $stats_borongan['umh'] += $row['umh_diambil'];
        $stats_borongan['uang_makan_lembur'] += $row['uang_makan_lembur'];
        $stats_borongan['bonus'] += $row['bonus_lain']; $stats_borongan['pot_lain'] += $row['potongan_lain']; 
        $stats_borongan['thp'] += $row['thp']; 
    }
}

$grand_thp = $stats_tetap['thp'] + $stats_borongan['thp'];
$gross_tetap    = $stats_tetap['gapok'] + $stats_tetap['makan'] + $stats_tetap['lembur'] + $stats_tetap['bonus'];
$gross_borongan = $stats_borongan['borongan'] + $stats_borongan['bonus'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <?php include '../../layout/header.php'; ?>
    <link href="https://fonts.googleapis.com/css2?family=Roboto+Mono:wght@500&family=Inter:wght@400;500;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    
    <style>
        :root { --blue: #2563eb; --red: #dc2626; --green: #16a34a; --orange: #ca8a04; --dark: #1e293b; --sidebar-w: 260px; }
        body { background-color: #f8fafc; font-family: 'Inter', sans-serif; color: #334155; }
        
        .main-content { margin-left: var(--sidebar-w); padding: 30px; padding-top: 100px; min-height: 100vh; transition: margin-left 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
        body.sidebar-collapsed .main-content { margin-left: 0; }
        @media (max-width: 992px) { .main-content { margin-left: 0; padding-top: 90px; } }

        .page-header-custom { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 25px; flex-wrap:wrap; gap:15px; }
        .page-title { font-size: 24px; font-weight: 800; color: #0f172a; margin: 0; line-height: 1.2; }
        .page-subtitle { color: #64748b; font-size: 14px; margin-top: 5px; font-weight: 500; }
        
        .filter-container { display: flex; gap: 15px; align-items: flex-end; padding: 20px; background: #fff; border-radius: 12px; border: 1px solid #e2e8f0; margin-bottom: 25px; flex-wrap: wrap; }
        .form-group-filter { flex: 1; min-width: 150px; }
        .form-label { font-size: 11px; font-weight: 600; color: #64748b; margin-bottom: 5px; display: block; text-transform: uppercase; }
        .form-input { width: 100%; padding: 10px 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 13px; color: #334155; outline: none; box-sizing: border-box; }
        .btn-act { border: none; padding: 10px 20px; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; transition: 0.2s; display: inline-flex; align-items: center; gap: 6px; text-decoration: none; height: 42px; }
        .btn-dark { background: var(--dark); color: white; } .btn-dark:hover { background: #0f172a; }
        .btn-green { background: var(--green); color: white; } .btn-green:hover { background: #059669; }

        .section-divider { background: linear-gradient(to right, #f1f5f9, #fff); border-left: 5px solid var(--dark); padding: 10px 15px; margin: 25px 0 15px 0; font-size: 14px; font-weight: 800; color: var(--dark); border-radius: 4px; text-transform: uppercase; letter-spacing: 0.5px; }
        
        .card-table { background: white; border-radius: 12px; border: 1px solid #e2e8f0; overflow: hidden; margin-bottom: 20px; }
        .table-responsive { width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch; }
        .table-custom { width: 100%; border-collapse: collapse; min-width: 900px; }
        .table-custom th { background: #f8fafc; padding: 10px 8px; font-size: 10px; text-transform: uppercase; color: #475569; border-bottom: 1px solid #e2e8f0; font-weight: 800; text-align: right; }
        .table-custom th:first-child, .table-custom td:first-child { text-align: left; position: sticky; left: 0; background: inherit; z-index: 2; }
        .table-custom td { padding: 8px; border-bottom: 1px solid #f3f4f6; font-size: 12px; text-align: right; vertical-align: middle; white-space: nowrap; }
        .table-custom tr:hover td { background: #f8fafc; }
        .table-custom tfoot td { background: #f1f5f9; font-weight: 800; border-top: 2px solid #e2e8f0; color: #334155; padding: 12px 8px; text-align: right; font-size: 12px; }

        .font-mono { font-family: 'Roboto Mono', monospace; }
        .text-plus { color: var(--green); } 
        .text-min { color: var(--red); } 
        .text-info { color: #d97706; font-weight: 600; } 
        .text-thp { color: var(--blue); font-weight: 800; font-size: 13px; }
        
        .stat-box { 
            padding: 15px 20px; 
            border-radius: 10px; 
            color: #fff; 
            height: 100%; 
            display: flex; 
            flex-direction: column; 
            justify-content: space-between; 
            position: relative; 
            overflow: hidden; 
            box-shadow: 0 2px 4px rgba(0,0,0,0.1); 
        }
        
        .box-blue { background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%); }
        .box-emerald { background: linear-gradient(135deg, #10b981 0%, #059669 100%); }
        .box-violet { background: linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%); }

        .stat-label { font-size: 11px; margin-bottom: 5px; opacity: 0.8; font-weight: 700; letter-spacing: 0.5px; text-transform: uppercase; }
        .stat-val { font-family: 'Roboto Mono', monospace; font-size: 20px; font-weight: 700; margin-bottom: 2px; }
        .stat-sub { font-size: 10px; opacity: 0.7; margin-bottom: 10px; font-style: italic; }

        .btn-box-action {
            background: rgba(255,255,255,0.2); 
            border: 1px solid rgba(255,255,255,0.3);
            color: #fff; width: 100%; padding: 6px 0; 
            border-radius: 6px; font-size: 11px; font-weight: 600; cursor: pointer;
            transition: 0.2s; display: flex; align-items: center; justify-content: center; gap: 6px;
            text-decoration: none; margin-top: auto;
        }
        .btn-box-action:hover { background: rgba(255,255,255,0.35); transform: translateY(-1px); }

    </style>
</head>
<body>
    <?php include '../../layout/sidebar.php'; ?>
    
    <div class="main-content">
        
        <div class="page-header-custom">
            <div>
                <h1 class="page-title">Laporan Gaji</h1>
                <p class="page-subtitle">Periode: <?= date('d M Y', strtotime($tgl_awal)) ?> s/d <?= date('d M Y', strtotime($tgl_akhir)) ?></p>
            </div>
            
            <a href="../cetak/cetak_laporan_gaji.php?tgl_awal=<?= $tgl_awal ?>&tgl_akhir=<?= $tgl_akhir ?>" target="_blank" class="btn-act btn-green">
                <i class="fa fa-print"></i> Cetak / PDF
            </a>
        </div>

        <form method="GET" class="filter-container">
            <div class="form-group-filter">
                <span class="form-label">Tanggal Awal</span>
                <input type="date" name="tgl_awal" value="<?= $tgl_awal ?>" class="form-input">
            </div>
            <div class="form-group-filter">
                <span class="form-label">Tanggal Akhir</span>
                <input type="date" name="tgl_akhir" value="<?= $tgl_akhir ?>" class="form-input">
            </div>
            <button type="submit" class="btn-act btn-dark" style="margin-bottom: 2px;">
                <i class="fa fa-filter"></i> Filter Data
            </button>
        </form>

        <div class="section-divider">KARYAWAN TETAP & HARIAN</div>
        <div class="card-table">
            <div class="table-responsive">
                <table class="table-custom">
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>Status</th>
                            <th>Gapok</th>
                            <th>U.Makan</th>
                            <th>Lembur</th>
                            <th>Lain² (+)</th>
                            <th>Bon UM</th>
                            <th>Lain² (-)</th> <th>Kasbon</th>
                            <th>THP</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($laporan_tetap as $r): ?>
                        <tr>
                            <td style="text-align:left;">
                                <div style="font-weight:700; color:#1e293b;"><?= $r['nama'] ?></div>
                                <div style="font-size:10px; color:#64748b;">HK: <?= number_format($r['hari_kerja'],1) ?></div>
                            </td>
                            <td style="text-align:left; color:#64748b; font-size:10px; line-height:1.2;"><?= $r['jenis'] ?><br><?= $r['info_extra'] ?></td>
                            <td class="font-mono">Rp <?= number_format($r['gapok']) ?></td>
                            <td class="font-mono">Rp <?= number_format($r['makan_hak']) ?></td>
                            <td class="font-mono text-plus">+<?= number_format($r['lembur_duit']) ?></td>
                            <td class="font-mono text-plus">+<?= number_format($r['bonus_lain']) ?></td>
                            
                            <td class="font-mono text-min">-<?= number_format($r['umh_diambil']) ?></td>
                            <td class="font-mono text-min">-<?= number_format($r['potongan_lain']) ?></td> 
                            <td class="font-mono text-min">
                                -<?= number_format($r['kasbon_duit']) ?>
                                <?php if(!empty($r['list_kasbon_detail'])): ?>
                                    <div style="font-size:9px; color:#dc2626; font-style:italic; line-height:1;">
                                        <?= implode(", ", $r['list_kasbon_detail']) ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td class="font-mono text-thp">Rp <?= number_format($r['thp']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if(empty($laporan_tetap)): ?>
                            <tr><td colspan="10" style="text-align:center; padding:30px; color:#94a3b8;">Tidak ada data.</td></tr>
                        <?php endif; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="2">TOTAL</td>
                            <td class="font-mono"><?= number_format($stats_tetap['gapok']) ?></td>
                            <td class="font-mono"><?= number_format($stats_tetap['makan']) ?></td>
                            <td class="font-mono"><?= number_format($stats_tetap['lembur']) ?></td>
                            <td class="font-mono"><?= number_format($stats_tetap['bonus']) ?></td>
                            <td class="font-mono"><?= number_format($stats_tetap['umh']) ?></td>
                            <td class="font-mono"><?= number_format($stats_tetap['pot_lain']) ?></td> <td class="font-mono"><?= number_format($stats_tetap['kasbon']) ?></td>
                            <td class="font-mono text-thp">Rp <?= number_format($stats_tetap['thp']) ?></td>
                        </tr>
                        <tr style="background-color: #eef2ff;">
                            <td colspan="2" style="color: #2563eb;">TOTAL TANPA POTONGAN</td>
                            <td class="font-mono"><?= number_format($stats_tetap['gapok']) ?></td>
                            <td class="font-mono"><?= number_format($stats_tetap['makan']) ?></td>
                            <td class="font-mono"><?= number_format($stats_tetap['lembur']) ?></td>
                            <td class="font-mono"><?= number_format($stats_tetap['bonus']) ?></td>
                            <td colspan="3" style="text-align: center; color: #94a3b8; font-size: 10px; font-weight: normal;">(Tanpa Potongan)</td>
                            <td class="font-mono text-thp" style="color: #0f172a;">Rp <?= number_format($gross_tetap) ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <div class="section-divider">KARYAWAN BORONGAN</div>
        <div class="card-table">
            <div class="table-responsive">
                <table class="table-custom">
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>Status</th>
                            <th>Borongan</th>
                            <th>Lain² (+)</th>
                            <th>Bon UM</th>
                            <th style="color:#dc2626;">UM Lembur</th> 
                            <th>Lain² (-)</th>
                            <th>Kasbon</th>
                            <th>THP</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($laporan_borongan as $r): ?>
                        <tr>
                            <td style="text-align:left;">
                                <div style="font-weight:700; color:#1e293b;"><?= $r['nama'] ?></div>
                            </td>
                            <td style="text-align:left; color:#64748b; font-size:10px; line-height:1.2;"><?= $r['jenis'] ?><br><?= $r['info_extra'] ?></td>
                            <td class="font-mono text-plus">Rp <?= number_format($r['borongan']) ?></td>
                            <td class="font-mono text-plus">+<?= number_format($r['bonus_lain']) ?></td>
                            
                            <td class="font-mono text-min">
                                -<?= number_format($r['umh_diambil']) ?>
                                <?php if(!empty($r['list_um_anggota'])): ?>
                                    <div style="font-size:9px; color:#64748b; margin-top:2px; line-height:1.1; font-style:italic;">
                                        (<?= $r['list_um_anggota'] ?>)
                                    </div>
                                <?php endif; ?>
                            </td>

                            <td class="font-mono text-min">-<?= number_format($r['uang_makan_lembur']) ?></td>

                            <td class="font-mono text-min">-<?= number_format($r['potongan_lain']) ?></td>
                            <td class="font-mono text-min">
                                -<?= number_format($r['kasbon_duit']) ?>
                                <?php if(!empty($r['list_kasbon_detail'])): ?>
                                    <div style="font-size:9px; color:#dc2626; font-style:italic; line-height:1;">
                                        <?= implode(", ", $r['list_kasbon_detail']) ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td class="font-mono text-thp">Rp <?= number_format($r['thp']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if(empty($laporan_borongan)): ?>
                            <tr><td colspan="9" style="text-align:center; padding:30px; color:#94a3b8;">Tidak ada data.</td></tr>
                        <?php endif; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="2">TOTAL</td>
                            <td class="font-mono"><?= number_format($stats_borongan['borongan']) ?></td>
                            <td class="font-mono"><?= number_format($stats_borongan['bonus']) ?></td>
                            <td class="font-mono"><?= number_format($stats_borongan['umh']) ?></td>
                            <td class="font-mono"><?= number_format($stats_borongan['uang_makan_lembur']) ?></td>
                            <td class="font-mono"><?= number_format($stats_borongan['pot_lain']) ?></td>
                            <td class="font-mono"><?= number_format($stats_borongan['kasbon']) ?></td>
                            <td class="font-mono text-thp">Rp <?= number_format($stats_borongan['thp']) ?></td>
                        </tr>
                        <tr style="background-color: #eef2ff;">
                            <td colspan="2" style="color: #2563eb;">TOTAL TANPA POTONGAN</td>
                            <td class="font-mono"><?= number_format($stats_borongan['borongan']) ?></td>
                            <td class="font-mono"><?= number_format($stats_borongan['bonus']) ?></td>
                            <td colspan="4" style="text-align: center; color: #94a3b8; font-size: 10px; font-weight: normal;">(Tanpa Potongan)</td>
                            <td class="font-mono text-thp" style="color: #0f172a;">Rp <?= number_format($gross_borongan) ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        
        <div class="row align-items-stretch" style="margin-top: 20px;">
            
            <div class="col-md-4 col-sm-12 mb-3">
                <div class="stat-box box-blue">
                    <div>
                        <div class="stat-label">Total THP (Bersih)</div>
                        <div class="stat-val">Rp <?= number_format($grand_thp) ?></div>
                        <div class="stat-sub">Semua Karyawan (Tetap + Borongan)</div>
                    </div>
                </div>
            </div>

            <div class="col-md-4 col-sm-12 mb-3">
                <div class="stat-box box-emerald">
                    <div>
                        <div class="stat-label">Total Kotor Tetap</div>
                        <div class="stat-val">Rp <?= number_format($gross_tetap) ?></div>
                        <div class="stat-sub">(Tanpa Potongan)</div>
                    </div>
                    <form method="POST" id="formTetap">
                        <input type="hidden" name="nominal" value="<?= $gross_tetap ?>">
                        <input type="hidden" name="save_tetap_cash" value="1">
                        <input type="hidden" name="hidden_tgl_awal" value="<?= $tgl_awal ?>">
                        <input type="hidden" name="hidden_tgl_akhir" value="<?= $tgl_akhir ?>">

                        <button type="button" onclick="confirmTetap()" class="btn-box-action">
                            <i class="fa fa-wallet"></i> Kirim ke Kas (CASH)
                        </button>
                    </form>
                </div>
            </div>

            <div class="col-md-4 col-sm-12 mb-3">
                <div class="stat-box box-violet">
                    <div>
                        <div class="stat-label">Total Kotor Borongan</div>
                        <div class="stat-val">Rp <?= number_format($gross_borongan) ?></div>
                        <div class="stat-sub">(Tanpa Potongan)</div>
                    </div>
                    <form method="POST" id="formBorongan">
                        <input type="hidden" name="nominal" value="<?= $gross_borongan ?>">
                        <input type="hidden" name="save_borongan_atm" value="1">
                        <input type="hidden" name="hidden_tgl_awal" value="<?= $tgl_awal ?>">
                        <input type="hidden" name="hidden_tgl_akhir" value="<?= $tgl_akhir ?>">

                        <button type="button" onclick="confirmBorongan()" class="btn-box-action">
                            <i class="fa fa-credit-card"></i> Kirim ke Kas (ATM)
                        </button>
                    </form>
                </div>
            </div>

        </div>

    </div>
    <?php include '../../layout/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        <?php if($swal_script) echo $swal_script; ?>

        function confirmTetap() {
            let total = "<?= number_format($gross_tetap, 0, ',', '.') ?>";
            Swal.fire({
                title: 'Konfirmasi Gaji Tetap',
                html: `Kirim pengeluaran <b>Rp ${total}</b> ke Kas?<br><small>Metode: CASH (Gaji Kotor Tetap - Bulat)</small>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#10b981',
                confirmButtonText: 'Ya, Proses!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) { document.getElementById('formTetap').submit(); }
            });
        }

        function confirmBorongan() {
            let total = "<?= number_format($gross_borongan, 0, ',', '.') ?>";
            Swal.fire({
                title: 'Konfirmasi Gaji Borongan',
                html: `Kirim pengeluaran <b>Rp ${total}</b> ke Kas?<br><small>Metode: ATM (Gaji Kotor Borongan - Bulat)</small>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#8b5cf6',
                confirmButtonText: 'Ya, Proses!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) { document.getElementById('formBorongan').submit(); }
            });
        }
    </script>
</body>
</html>