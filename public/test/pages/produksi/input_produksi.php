<?php 
// 1. SETTING AWAL & ERROR REPORTING
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../../config/database.php';

// Cek file function
if (file_exists('../../config/function.php')) {
    require_once '../../config/function.php';
}

date_default_timezone_set('Asia/Jakarta');

// Cek Login Session
session_start();
if (!isset($_SESSION['role'])) {
    header("Location: ../../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// --- 1.1 LOGIKA SHIFT LINTAS HARI (FIX AKSES DITUTUP) ---
$jam_akses  = date('H:i:s');
$tgl_aktual = date('Y-m-d');

// Jika akses web dilakukan antara jam 00:00 - 07:00, sistem akan menganggap 
// Anda masih berada di "shift kemarin"
if ($jam_akses >= "00:00:00" && $jam_akses <= "07:00:00") {
    $today = date('Y-m-d', strtotime('-1 day'));
} else {
    $today = $tgl_aktual;
}
// Variabel $today sekarang merepresentasikan TANGGAL SHIFT AKTIF, bukan kalender biasa.

// --- 2. AMBIL DATA USER & CEK JABATAN ---
// Ambil data user, jabatan, dan info tim
$q_user = mysqli_query($conn, "
    SELECT u.pin, u.fullname, u.tim_id, u.status_karyawan, 
           mj.nama_jabatan, mt.leader_id as team_leader_id 
    FROM users u 
    LEFT JOIN master_jabatan mj ON u.jabatan_id = mj.id 
    LEFT JOIN master_tim mt ON u.tim_id = mt.id
    WHERE u.id='$user_id'
");

if (!$q_user) {
    die("Error Database: " . mysqli_error($conn));
}

$d_user = mysqli_fetch_assoc($q_user);
$my_pin = trim($d_user['pin']);
$nama_full = $d_user['fullname'];
$nama_parts = explode(' ', $nama_full);
$nama_user = $nama_parts[0];

// Deteksi Role Leader
// Dianggap Leader jika nama jabatan mengandung kata "Leader"
$is_leader = (isset($d_user['nama_jabatan']) && stripos($d_user['nama_jabatan'], 'Leader') !== false) ? 1 : 0;

// Tentukan Kategori User (Untuk Filter Pekerjaan & Label UI)
$kategori_filter = "";
$label_mode      = "";
$style_badge     = "";
$target_leader_id = "NULL"; 

if ($d_user['status_karyawan'] == 'Borongan') {
    if ($is_leader == 1) {
        // Leader: Lihat job milik dia sendiri ATAU Umum
        $kategori_filter  = "Team";
        $label_mode       = "MODE MANDOR";
        $style_badge      = "background: #dbeafe; color: #1e40af; border: 1px solid #93c5fd;";
        $target_leader_id = "'$user_id'"; 
    } elseif (!empty($d_user['tim_id'])) {
        // Anggota Tim: (Nanti akan diblokir di Section 4)
        $kategori_filter  = "Team"; 
        $label_mode       = "ANGGOTA TIM";
        $style_badge      = "background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5;"; // Merah (karena akses ditutup)
        $leader_id_tim    = !empty($d_user['team_leader_id']) ? $d_user['team_leader_id'] : "NULL";
        $target_leader_id = "'$leader_id_tim'";
    } else {
        // Perorangan: Lihat job milik dia sendiri ATAU Umum
        $kategori_filter  = "Perorangan"; 
        $label_mode       = "PERORANGAN";
        $style_badge      = "background: #fce7f3; color: #9d174d; border: 1px solid #f9a8d4;";
        $target_leader_id = "'$user_id'";
    }
} else {
    $label_mode = "KARYAWAN TETAP";
    $style_badge = "background: #f3f4f6; color: #374151;";
}

// --- 3. CEK ABSENSI (DENGAN LOGIKA SHIFT) ---
$sudah_masuk  = false;
$sudah_pulang = false;

// Cari data absensi dalam rentang 1 hari penuh + 7 jam keesokan harinya
$batas_awal  = $today . " 00:00:00";
$batas_akhir = date('Y-m-d', strtotime($today . ' +1 day')) . " 07:00:00";

$q_absen = mysqli_query($conn, "
    SELECT scan_date, status_scan 
    FROM absensi 
    WHERE trim(pin)='$my_pin' 
    AND scan_date BETWEEN '$batas_awal' AND '$batas_akhir'
    ORDER BY scan_date ASC
");

if ($q_absen) {
    while($row = mysqli_fetch_assoc($q_absen)) {
        $scan_jam = date('H:i:s', strtotime($row['scan_date']));
        $scan_tgl = date('Y-m-d', strtotime($row['scan_date']));
        
        // Logika Indexing: Jika scan masuk di area jam 00:00 - 07:00, kembalikan ke tanggal kemarin
        $tgl_idx = ($scan_jam >= "00:00:00" && $scan_jam <= "07:00:00") 
                   ? date('Y-m-d', strtotime($scan_tgl . ' -1 day')) 
                   : $scan_tgl;
        
        // Cek status khusus HANYA untuk tanggal shift aktif ($today)
        if ($tgl_idx == $today) {
            if(in_array($row['status_scan'], [0, 4, 8])) { $sudah_masuk = true; } 
            if(in_array($row['status_scan'], [1, 5, 9])) { $sudah_pulang = true; } 
        }
    }
}

// --- 4. LOGIKA BLOKIR AKSES (UPDATED) ---
$bisa_input   = false;
$pesan_blokir = "";
$warna_alert  = "grad-warning";
$icon_blokir  = "fa-exclamation-triangle";

// Cek apakah dia Anggota Tim Biasa (Punya Tim ID TAPI Bukan Leader)
$is_anggota_biasa = (!empty($d_user['tim_id']) && $is_leader == 0);

if ($d_user['status_karyawan'] != 'Borongan') {
    // 1. Blokir jika bukan borongan
    $pesan_blokir = "Halaman ini khusus untuk karyawan <b>BORONGAN</b>.";
    $warna_alert  = "grad-danger";
    $icon_blokir  = "fa-ban";

} elseif ($is_anggota_biasa) {
    // 2. BLOKIR KHUSUS ANGGOTA TIM (SESUAI REQUEST)
    $pesan_blokir = "Inputan Produksi Hanya di lakukan oleh MANDOR";
    $warna_alert  = "grad-danger"; // Merah
    $icon_blokir  = "fa-user-lock";
    $bisa_input   = false; // Pastikan false

} elseif (!$sudah_masuk) {
    // 3. Blokir jika belum absen masuk
    $pesan_blokir = "Anda belum melakukan <b>SCAN MASUK</b> hari ini.";
    $warna_alert  = "grad-warning";
    $icon_blokir  = "fa-fingerprint";

} elseif ($sudah_pulang) {
    // 4. Blokir jika sudah pulang
    $pesan_blokir = "Anda sudah melakukan <b>ABSEN PULANG</b>. Akses ditutup.";
    $warna_alert  = "grad-danger";
    $icon_blokir  = "fa-lock";

} else {
    // Lolos semua validasi (Hanya Leader atau Perorangan yang bisa masuk sini)
    $bisa_input = true;
}

// --- 5. PROSES SIMPAN (SUBMIT FORM) ---
$swal_script = "";
if(isset($_POST['simpan_semua']) && $bisa_input) {
    $tgl_input = $_POST['tanggal'];
    $job_ids   = isset($_POST['job_id']) ? $_POST['job_id'] : [];
    $jumlahs   = isset($_POST['jumlah']) ? $_POST['jumlah'] : [];
    
    $sukses = 0;

    if(!empty($job_ids)) {
        foreach($job_ids as $index => $raw_id) {
            $id_pekerjaan = (int)$raw_id;
            $jumlah       = (int)$jumlahs[$index];

            if($jumlah > 0) {
                // Ambil Harga
                $q_cek = mysqli_query($conn, "SELECT jenis_pekerjaan, nama_motor, harga FROM master_pekerjaan_borongan WHERE id='$id_pekerjaan'");
                $d_cek = mysqli_fetch_assoc($q_cek);
                
                if($d_cek) {
                    $upah_satuan = $d_cek['harga'];
                    $total_upah  = $jumlah * $upah_satuan;
                    $nama_lengkap_job = $d_cek['jenis_pekerjaan'] . " - " . $d_cek['nama_motor'];

                    // Insert
                    $stmt = mysqli_prepare($conn, "INSERT INTO hasil_produksi_borongan (user_id, tanggal, jenis_pekerjaan, pekerjaan_id, jumlah, total_upah, status) VALUES (?, ?, ?, ?, ?, ?, 'Pending')");
                    
                    if ($stmt) {
                        mysqli_stmt_bind_param($stmt, "issiid", $user_id, $tgl_input, $nama_lengkap_job, $id_pekerjaan, $jumlah, $total_upah);
                        if(mysqli_stmt_execute($stmt)) {
                            $sukses++;
                        }
                    }
                }
            }
        }
    }

    if($sukses > 0) {
        $swal_script = "Swal.fire({icon: 'success', title: 'Berhasil', text: '$sukses data produksi berhasil disimpan.', timer: 2000, showConfirmButton: false});";
    } else {
        $swal_script = "Swal.fire({icon: 'error', title: 'Gagal', text: 'Tidak ada data yang tersimpan.'});";
    }
}

// --- 6. SIAPKAN DATA JSON UNTUK DROPDOWN JS (FILTERED) ---
$list_jenis = [];
$json_motor = [];

if($bisa_input) {
    $sql_master = "
        SELECT * FROM master_pekerjaan_borongan 
        WHERE kategori = '$kategori_filter' 
        AND (leader_id = $target_leader_id OR leader_id IS NULL)
        ORDER BY jenis_pekerjaan ASC, nama_motor ASC
    ";

    $q_master = mysqli_query($conn, $sql_master);
    
    if($q_master) {
        while($r = mysqli_fetch_assoc($q_master)) {
            $key = md5(strtolower(trim($r['jenis_pekerjaan'])));
            $list_jenis[$key] = $r['jenis_pekerjaan'];
            
            $json_motor[$key][] = [
                'id'    => $r['id'],
                'motor' => $r['nama_motor'],
                'harga' => (int)$r['harga']
            ];
        }
    }
}

// --- 7. STATISTIK HARIAN ---
$stat_qty  = 0;
$stat_upah = 0;
// Tetap tampilkan statistik (read-only) meskipun dia diblokir inputnya, agar tetap transparan
$q_st = mysqli_query($conn, "SELECT SUM(jumlah) as j, SUM(total_upah) as u FROM hasil_produksi_borongan WHERE user_id='$user_id' AND tanggal='$today'");
if($q_st && $d_st = mysqli_fetch_assoc($q_st)) {
    $stat_qty  = $d_st['j'] ?? 0;
    $stat_upah = $d_st['u'] ?? 0;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <?php 
    if(file_exists('../../layout/header.php')) {
        include '../../layout/header.php';
    } else {
        echo '<meta name="viewport" content="width=device-width, initial-scale=1">';
        echo "<title>Input Produksi</title>";
    }
    ?>
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root { 
            --primary: #4f46e5; 
            --primary-dark: #4338ca;
            --bg: #f8fafc; 
            --text-main: #334155;
            --card-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --sidebar-width: 250px; 
        }
        
        body { 
            background-color: var(--bg); 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            color: var(--text-main); 
            margin: 0;
            overflow-x: hidden;
        }

        /* Layout */
        .content-wrapper { 
            padding: 30px 20px; 
            margin-left: var(--sidebar-width); 
            transition: margin-left 0.3s ease;
            min-height: 100vh;
        }
        body.sidebar-collapse .content-wrapper { margin-left: 0; }
        @media (max-width: 768px) {
            .content-wrapper { margin-left: 0; padding: 20px 15px; padding-bottom: 80px; }
        }

        /* Header */
        .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; flex-wrap: wrap; gap: 15px; }
        .page-title h2 { font-weight: 800; color: #1e293b; font-size: 26px; margin: 0; letter-spacing: -0.5px; }
        .page-title p { color: #64748b; font-size: 14px; margin: 5px 0 0; }
        .date-badge { 
            font-size: 13px; font-weight: 600; color: var(--primary); 
            background: #e0e7ff; padding: 8px 16px; border-radius: 50px; 
            display: inline-flex; align-items: center; gap: 8px;
        }

        /* Components */
        .alert-box { 
            border-radius: 16px; padding: 20px; color: #fff; 
            display: flex; align-items: flex-start; gap: 15px; margin-bottom: 25px; 
            box-shadow: var(--card-shadow);
        }
        .grad-warning { background: linear-gradient(135deg, #f59e0b, #d97706); }
        .grad-danger { background: linear-gradient(135deg, #ef4444, #b91c1c); }
        .grad-info { background: linear-gradient(135deg, #3b82f6, #1d4ed8); }

        .glass-card { 
            background: #fff; border-radius: 24px; border: 1px solid #e2e8f0; 
            box-shadow: var(--card-shadow); overflow: hidden; height: 100%; 
            display: flex; flex-direction: column; transition: transform 0.2s;
        }
        .form-header { 
            padding: 25px; border-bottom: 1px solid #f1f5f9; 
            display: flex; justify-content: space-between; align-items: center; 
            background: linear-gradient(to bottom, #fff, #fcfcfc);
        }
        .mode-label { 
            font-size: 11px; font-weight: 800; padding: 6px 12px; 
            border-radius: 30px; letter-spacing: 0.5px; text-transform: uppercase; 
        }
        .form-body { padding: 25px; flex-grow: 1; }
        
        .input-group-m { margin-bottom: 20px; }
        .input-label { 
            display: block; font-size: 12px; font-weight: 700; 
            color: #64748b; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px;
        }
        .form-control-m { 
            width: 100%; height: 50px; padding: 0 15px; border-radius: 12px; 
            border: 2px solid #f1f5f9; background: #f8fafc; 
            font-size: 14px; font-weight: 600; color: #334155; 
            transition: all 0.2s; 
        }
        .form-control-m:focus { 
            border-color: var(--primary); background: #fff; outline: none; 
            box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1); 
        }
        .form-control-m:disabled { background: #e2e8f0; cursor: not-allowed; opacity: 0.7; }

        .btn-add-queue { 
            width: 100%; height: 50px; background: #eff6ff; 
            border: 2px dashed #818cf8; border-radius: 14px; 
            color: var(--primary); font-weight: 700; cursor: pointer; 
            transition: 0.2s; display: flex; align-items: center; justify-content: center; gap: 8px;
            font-size: 14px;
        }
        .btn-add-queue:hover { background: #e0e7ff; border-color: var(--primary); transform: translateY(-1px); }
        .btn-add-queue:active { transform: translateY(0); }

        .btn-submit-all { 
            width: 100%; height: 55px; 
            background: linear-gradient(135deg, #4f46e5 0%, #4338ca 100%); 
            border: none; border-radius: 16px; color: #fff; 
            font-size: 16px; font-weight: 700; cursor: pointer; 
            margin-top: 15px; box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);
            display: flex; align-items: center; justify-content: center; gap: 10px;
            transition: 0.2s;
        }
        .btn-submit-all:hover { transform: translateY(-2px); box-shadow: 0 6px 15px rgba(79, 70, 229, 0.4); }
        
        .queue-box { margin-top: 25px; display: none; border-top: 2px dashed #e2e8f0; padding-top: 20px; }
        .queue-title { font-size: 13px; font-weight: 800; color: #1e293b; margin-bottom: 15px; display: flex; justify-content: space-between; align-items: center; }
        .queue-item { background: #fff; border: 1px solid #e2e8f0; border-radius: 14px; padding: 15px; display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; animation: slideDown 0.3s ease; box-shadow: 0 2px 4px rgba(0,0,0,0.02); }
        .btn-rem { width: 32px; height: 32px; background: #fee2e2; color: #ef4444; border: none; border-radius: 10px; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: 0.2s; }
        .btn-rem:hover { background: #fecaca; }
        
        @keyframes slideDown { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }

        .stat-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 25px; }
        .stat-card { background: #fff; padding: 20px; border-radius: 20px; border: 1px solid #e2e8f0; display: flex; align-items: center; gap: 15px; box-shadow: 0 2px 8px rgba(0,0,0,0.02); }
        .stat-icon { width: 48px; height: 48px; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 20px; flex-shrink: 0; }
        .sc-blue { background: #eff6ff; color: #2563eb; }
        .sc-green { background: #f0fdf4; color: #16a34a; }

        .hist-item { background: #fff; padding: 18px; border-radius: 16px; border: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; transition: 0.2s; }
        .hist-item:hover { transform: translateX(3px); border-color: #e2e8f0; }
        
        .st-Pending { background: #fff7ed; color: #c2410c; padding: 4px 10px; border-radius: 8px; font-size: 11px; font-weight: 700; text-transform: uppercase; }
        .st-Approved { background: #dcfce7; color: #166534; padding: 4px 10px; border-radius: 8px; font-size: 11px; font-weight: 700; text-transform: uppercase; }
        .st-Rejected { background: #fee2e2; color: #991b1b; padding: 4px 10px; border-radius: 8px; font-size: 11px; font-weight: 700; text-transform: uppercase; }
        .empty-state { text-align: center; padding: 60px 20px; color: #94a3b8; }

        @media (max-width: 992px) {
            .row { display: flex; flex-direction: column; }
            .col-md-5, .col-md-7 { width: 100%; margin-bottom: 25px; padding: 0; }
            .page-title h2 { font-size: 22px; }
            .stat-card { padding: 15px; }
            .stat-icon { width: 40px; height: 40px; font-size: 16px; }
        }
    </style>
</head>
<body>
    <?php if(file_exists('../../layout/sidebar.php')) include '../../layout/sidebar.php'; ?>
    
    <div class="content-wrapper">
        <div class="page-header">
            <div>
                <h2 class="page-title">Input Produksi</h2>
                <p style="margin:5px 0 0; color:#64748b;">Halo <b><?= htmlspecialchars($nama_user) ?></b>, input hasil kerjamu hari ini.</p>
            </div>
            <div class="date-badge">
                <i class="fa fa-calendar-alt"></i> <?= date('l, d M Y', strtotime($today)) ?>
            </div>
        </div>

        <?php if(!$bisa_input): ?>
            <div class="alert-box <?php echo $warna_alert; ?>">
                <div style="background: rgba(255,255,255,0.2); width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <i class="fa <?php echo $icon_blokir; ?> fa-lg"></i>
                </div>
                <div>
                    <h4 style="margin: 0 0 4px; font-weight: 800; font-size: 16px;">AKSES DITUTUP</h4>
                    <p style="margin: 0; opacity: 0.95; font-size: 13px; line-height: 1.5;"><?php echo $pesan_blokir; ?></p>
                </div>
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-5">
                <div class="glass-card" style="<?php if(!$bisa_input) echo 'opacity: 0.6; pointer-events: none; filter: grayscale(100%);'; ?>">
                    <div class="form-header">
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <div style="width: 42px; height: 42px; background: #f1f5f9; border-radius: 12px; display: flex; align-items: center; justify-content: center; color: var(--primary);">
                                <i class="fa fa-edit fa-lg"></i>
                            </div>
                            <div>
                                <h4 style="margin: 0; font-weight: 800; font-size: 16px; color: #1e293b;">Form Laporan</h4>
                                <span style="font-size: 11px; color: #64748b;">Isi data dengan teliti</span>
                            </div>
                        </div>
                        <span class="mode-label" style="<?= $style_badge ?>"><?= $label_mode ?></span>
                    </div>
                    
                    <div class="form-body">
                        <div class="input-group-m">
                            <label class="input-label"><i class="fa fa-tags"></i> 1. Jenis Pekerjaan</label>
                            <select id="pilih_jenis" class="form-control-m">
                                <option value="">-- Pilih Jenis --</option>
                                <?php foreach($list_jenis as $key => $val): ?>
                                    <option value="<?= $key ?>"><?= htmlspecialchars($val) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="row" style="display:flex; gap:10px; margin:0;">
                            <div style="flex:2;">
                                <div class="input-group-m">
                                    <label class="input-label"><i class="fa fa-motorcycle"></i> 2. Motor / Item</label>
                                    <select id="pilih_motor" class="form-control-m" disabled>
                                        <option value="">-- Pilih --</option>
                                    </select>
                                </div>
                            </div>
                            <div style="flex:1;">
                                <div class="input-group-m">
                                    <label class="input-label"><i class="fa fa-cubes"></i> 3. Qty</label>
                                    <input type="number" id="input_jumlah" class="form-control-m" placeholder="0" min="1">
                                </div>
                            </div>
                        </div>

                        <button type="button" id="btn_tambah" class="btn-add-queue">
                            <i class="fa fa-plus-circle"></i> Tambah ke Daftar
                        </button>

                        <form method="POST" id="form_utama">
                            <input type="hidden" name="tanggal" value="<?= $today ?>">
                            
                            <div class="queue-box" id="queue_container">
                                <div class="queue-title">
                                    <span>DAFTAR ANTRIAN</span>
                                    <span id="queue_count" style="background: #e0e7ff; color: var(--primary); padding: 2px 8px; border-radius: 4px; font-size: 11px;">0 Item</span>
                                </div>
                                <div id="queue_list">
                                    </div>
                                <button type="submit" name="simpan_semua" class="btn-submit-all">
                                    <span>KIRIM SEMUA DATA</span> <i class="fa fa-paper-plane"></i> 
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-7">
                <div class="stat-grid">
                    <div class="stat-card">
                        <div class="stat-icon sc-blue"><i class="fa fa-box-open"></i></div>
                        <div>
                            <div style="font-size: 20px; font-weight: 800; color: #1e293b; line-height:1;"><?= number_format($stat_qty) ?></div>
                            <div style="font-size: 11px; font-weight: 700; color: #64748b; margin-top:5px;">UNIT HARI INI</div>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon sc-green"><i class="fa fa-money-bill-wave"></i></div>
                        <div>
                            <div style="font-size: 20px; font-weight: 800; color: #1e293b; line-height:1;">Rp <?= number_format($stat_upah) ?></div>
                            <div style="font-size: 11px; font-weight: 700; color: #64748b; margin-top:5px;">ESTIMASI UPAH</div>
                        </div>
                    </div>
                </div>

                <div class="glass-card">
                    <div style="padding: 20px; background: #fff; border-bottom: 1px solid #f1f5f9; display:flex; justify-content:space-between; align-items:center;">
                        <h5 style="margin:0; font-size:15px; font-weight:800; color:#1e293b;">Riwayat Hari Ini</h5>
                        <i class="fa fa-history text-muted"></i>
                    </div>
                    <div style="padding: 20px; background: #f8fafc; flex-grow: 1; overflow-y: auto; max-height: 500px;">
                        <?php 
                        $q_hist = mysqli_query($conn, "
                            SELECT hp.*, mp.jenis_pekerjaan, mp.nama_motor 
                            FROM hasil_produksi_borongan hp
                            LEFT JOIN master_pekerjaan_borongan mp ON hp.pekerjaan_id = mp.id
                            WHERE hp.user_id='$user_id' AND hp.tanggal='$today'
                            ORDER BY hp.id DESC
                        ");

                        if($q_hist && mysqli_num_rows($q_hist) > 0) {
                            while($h = mysqli_fetch_assoc($q_hist)) {
                        ?>
                            <div class="hist-item">
                                <div>
                                    <div style="font-weight: 700; color: #334155; font-size: 14px;"><?= $h['jenis_pekerjaan'] ?></div>
                                    <div style="font-size: 12px; color: #64748b; margin-top: 4px; display:flex; align-items:center; gap:5px;">
                                        <i class="fa fa-wrench" style="font-size: 10px;"></i> <?= $h['nama_motor'] ?>
                                    </div>
                                </div>
                                <div style="text-align: right;">
                                    <div style="font-weight: 800; color: #1e293b; font-size: 15px;"><?= $h['jumlah'] ?> <span style="font-size:11px; font-weight:600; color:#64748b;">Pcs</span></div>
                                    <div style="margin-top:4px;"><span class="st-<?= $h['status'] ?>"><?= $h['status'] ?></span></div>
                                </div>
                            </div>
                        <?php 
                            }
                        } else {
                            echo '
                            <div class="empty-state">
                                <div style="width:60px; height:60px; background:#e2e8f0; border-radius:50%; display:inline-flex; align-items:center; justify-content:center; margin-bottom:15px; color:#94a3b8;">
                                    <i class="fa fa-clipboard-list fa-2x"></i>
                                </div>
                                <p style="font-weight:600; margin:0;">Belum ada data</p>
                                <p style="font-size:12px; margin:5px 0 0;">Input pekerjaanmu sekarang!</p>
                            </div>';
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if(file_exists('../../layout/footer.php')) include '../../layout/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
        var dbMotor = <?php echo json_encode($json_motor); ?>;
        <?php if($swal_script) echo $swal_script; ?>

        $(document).ready(function() {
            $('#pilih_jenis').change(function() {
                var key = $(this).val();
                var motorSelect = $('#pilih_motor');
                
                motorSelect.css('opacity', '0.5'); 
                
                setTimeout(function(){
                    motorSelect.empty().append('<option value="">-- Pilih Motor / Item --</option>');

                    if(key && dbMotor[key]) {
                        motorSelect.prop('disabled', false).css('background', '#fff');
                        $.each(dbMotor[key], function(i, item){
                            var rp = new Intl.NumberFormat('id-ID').format(item.harga);
                            motorSelect.append(`<option value="${item.id}" data-nama="${item.motor}">${item.motor} (Rp ${rp})</option>`);
                        });
                    } else {
                        motorSelect.prop('disabled', true).css('background', '#f8fafc');
                    }
                    motorSelect.css('opacity', '1');
                }, 200);
            });

            $('#btn_tambah').click(function() {
                var jenisTxt = $('#pilih_jenis option:selected').text();
                var motorVal = $('#pilih_motor').val();
                var motorTxt = $('#pilih_motor option:selected').data('nama');
                var jumlah   = $('#input_jumlah').val();

                if(!motorVal || !jumlah || jumlah <= 0) {
                    Swal.fire({
                        icon: 'warning', 
                        title: 'Data Belum Lengkap', 
                        text: 'Silakan pilih Motor dan isi Jumlah dengan benar.', 
                        timer: 1500, 
                        showConfirmButton: false
                    });
                    return;
                }

                var itemHtml = `
                <div class="queue-item">
                    <div>
                        <div style="font-weight:700; font-size:13px; color:#334155; margin-bottom:2px;">${jenisTxt}</div>
                        <div style="font-size:12px; color:#64748b;">${motorTxt} &bull; <b style="color:var(--primary);">${jumlah} Pcs</b></div>
                        <input type="hidden" name="job_id[]" value="${motorVal}">
                        <input type="hidden" name="jumlah[]" value="${jumlah}">
                    </div>
                    <button type="button" class="btn-rem" onclick="$(this).parent().remove(); updateQueue();">
                        <i class="fa fa-times"></i>
                    </button>
                </div>`;

                $('#queue_list').prepend(itemHtml); 
                $('#input_jumlah').val('');
                $('#pilih_motor').focus();
                
                updateQueue();
            });
        });

        function updateQueue() {
            var count = $('#queue_list .queue-item').length;
            $('#queue_count').text(count + " Item");
            
            if(count > 0) {
                $('#queue_container').slideDown();
            } else {
                $('#queue_container').slideUp();
            }
        }
    </script>
</body>
</html>