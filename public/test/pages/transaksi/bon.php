<?php 
require_once '../../config/database.php';
date_default_timezone_set('Asia/Jakarta');
cek_login(); 

$uid = $_SESSION['user_id'];
$today = date('Y-m-d');
$now_datetime = date('Y-m-d H:i:s');
$jam_sekarang = (int)date('H'); 

// --- 1. AMBIL DATA USER & GAJI ---
$q_user = mysqli_query($conn, "SELECT status_karyawan FROM users WHERE id='$uid'");
$d_user = mysqli_fetch_assoc($q_user);
$status_karyawan = $d_user['status_karyawan']; 

// Ambil nominal JATAH DEFAULT dari database
$q_gaji = mysqli_query($conn, "SELECT uang_makan FROM gaji_karyawan WHERE user_id='$uid'");
$d_gaji = mysqli_fetch_assoc($q_gaji);
$jatah_makan_db = $d_gaji['uang_makan'] ?? 0; 

// --- 2. CEK STATUS PENGAJUAN HARI INI (Hanya Cek UMH) ---
$data_harian = null;

$q_cek = mysqli_query($conn, "SELECT * FROM uang_makan WHERE user_id='$uid' AND DATE(tanggal)='$today'");
while($row = mysqli_fetch_assoc($q_cek)) {
    if($row['kode_pengajuan'] == 'UMH') { $data_harian = $row; } 
}

$sudah_harian = ($data_harian != null);

// --- 3. PROSES PENGAJUAN ---
$swal_script = "";

if(isset($_POST['ajukan_makan'])) {
    $tipe = $_POST['tipe_makan']; 
    
    // Default error handling
    $is_error = false;
    $msg_error = "";
    $nominal_final = 0;

    // A. LOGIKA HARIAN
    if ($tipe == 'harian') {
        $kode_db = 'UMH';
        $ket_db  = 'Uang Makan Harian';
        
        if($sudah_harian) { 
            $is_error = true; $msg_error = "Jatah harian sudah diambil!"; 
        } else {
            // Jika Borongan: Ambil dari Input. Jika Tetap: Ambil dari DB
            if ($status_karyawan == 'Borongan') {
                $nominal_final = str_replace('.', '', $_POST['nominal']);
            } else {
                $nominal_final = $jatah_makan_db;
            }
        }
    } 

    // EKSEKUSI DATABASE
    if($is_error) {
        $swal_script = "Swal.fire({icon: 'error', title: 'Gagal', text: '$msg_error'});";
    } else {
        if ($nominal_final > 0) {
            $sql = "INSERT INTO uang_makan (user_id, kode_pengajuan, tanggal, nominal, keterangan, status) 
                    VALUES ('$uid', '$kode_db', '$now_datetime', '$nominal_final', '$ket_db', 'Pending')";
            
            if(mysqli_query($conn, $sql)) {
                $swal_script = "Swal.fire({icon: 'success', title: 'Berhasil', text: 'Pengajuan $ket_db Sebesar Rp ".number_format($nominal_final)." Berhasil!', timer: 2000, showConfirmButton: false}).then(() => { window.location='bon.php'; });";
            } else {
                $swal_script = "Swal.fire({icon: 'error', title: 'Error', text: 'Database Error'});";
            }
        } else {
            $swal_script = "Swal.fire({icon: 'error', title: 'Error', text: 'Nominal Jatah Makan di Database masih 0. Hubungi Admin!'});";
        }
    }
}

// LOGIKA KASBON (Tidak berubah)
if(isset($_POST['ajukan_kasbon'])) {
    $nom = str_replace('.', '', $_POST['nominal']); 
    $ket = mysqli_real_escape_string($conn, $_POST['keterangan']);
    $tenor = (int)$_POST['tenor']; 

    if($nom > 0 && !empty($ket)) {
        $cek_pending = mysqli_query($conn, "SELECT id FROM kasbon WHERE user_id='$uid' AND status='Pending'");
        if(mysqli_num_rows($cek_pending) > 0) {
            $swal_script = "Swal.fire({icon: 'warning', title: 'Tahan Dulu', text: 'Selesaikan pengajuan kasbon sebelumnya.'});";
        } else {
            $sql = "INSERT INTO kasbon (user_id, tanggal, nominal, keterangan, status, tenor, terbayar, status_lunas) 
                    VALUES ('$uid', '$today', '$nom', '$ket', 'Pending', '$tenor', 0, 'Belum')";
            if(mysqli_query($conn, $sql)) {
                $swal_script = "Swal.fire({icon: 'success', title: 'Berhasil', text: 'Pengajuan kasbon terkirim!', timer: 1500, showConfirmButton: false}).then(() => { window.location='bon.php'; });";
            }
        }
    } else {
        $swal_script = "Swal.fire({icon: 'error', title: 'Error', text: 'Isi semua data kasbon!'});";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <?php include '../../layout/header.php'; ?>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root { --primary: #4f46e5; --bg-body: #f8fafc; --border-color: #e2e8f0; }
        body { background-color: var(--bg-body); font-family: 'Inter', sans-serif; color: #1e293b; }
        .content-wrapper { padding: 30px; }
        .modern-card { background: #fff; border-radius: 16px; border: 1px solid var(--border-color); box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); overflow: hidden; height: 100%; }
        .card-header { padding: 20px 25px; border-bottom: 1px solid var(--border-color); background: #fff; }
        .card-body { padding: 25px; }
        
        .tabs-container { display: flex; background: #f1f5f9; padding: 5px; border-radius: 12px; margin-bottom: 25px; }
        .tab-btn { flex: 1; padding: 10px; border: none; background: transparent; border-radius: 8px; font-weight: 600; color: #64748b; cursor: pointer; transition: 0.2s; font-size: 14px; }
        .tab-btn.active { background: #fff; color: var(--primary); box-shadow: 0 2px 4px rgba(0,0,0,0.05); }

        .form-label { font-size: 12px; font-weight: 600; color: #64748b; text-transform: uppercase; margin-bottom: 6px; display: block; }
        /* Input Readonly lebih gelap sedikit */
        .form-control-lg { height: 48px; border-radius: 10px; font-size: 15px; border: 1px solid var(--border-color); padding-left: 15px; width: 100%; }
        .form-control-lg:read-only { background-color: #f1f5f9; color: #64748b; cursor: not-allowed; font-weight: 700; }
        .form-control-lg:focus { border-color: var(--primary); outline: none; box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1); }
        
        .status-card { padding: 15px; border-radius: 10px; border: 1px solid #e2e8f0; margin-bottom: 15px; background: #fff; position: relative; overflow: hidden; }
        .status-card.done { background: #f0fdf4; border-color: #bbf7d0; }
        
        .code-badge { position: absolute; top: 15px; right: 15px; font-size: 10px; background: #e2e8f0; color: #64748b; padding: 3px 8px; border-radius: 4px; font-weight: 800; }
        .done .code-badge { background: #dcfce7; color: #166534; }
        
        .btn-submit { width: 100%; padding: 12px; background: var(--primary); color: #fff; border: none; border-radius: 8px; font-weight: 600; font-size: 14px; cursor: pointer; margin-top:10px; }
        .btn-submit:hover { background: #4338ca; }
        .btn-dark { background: #0f172a; }
        .badge { padding: 4px 10px; border-radius: 6px; font-size: 10px; font-weight: 700; text-transform: uppercase; }
        .text-success { color: #15803d; }
        .text-muted { color: #94a3b8; font-size: 12px; }
    </style>
</head>
<body>
    <?php include '../../layout/sidebar.php'; ?>
    
    <div class="content-wrapper">
        <h2 style="font-weight:800; color:#1e293b; margin-bottom: 20px;">Pengajuan Dana</h2>

        <div class="row">
            <div class="col-lg-5 col-md-12 mb-4">
                <div class="modern-card">
                    <div class="card-header">
                        <h4 style="margin:0; font-size:16px; font-weight:700;">Buat Pengajuan</h4>
                    </div>
                    <div class="card-body">
                        
                        <div class="tabs-container">
                            <button type="button" class="tab-btn active" onclick="switchTab('kasbon')" id="btn-kasbon">Kasbon</button>
                            <button type="button" class="tab-btn" onclick="switchTab('makan')" id="btn-makan">Uang Makan</button>
                        </div>

                        <div id="view-kasbon">
                            <form method="POST">
                                <div class="form-group mb-3">
                                    <label class="form-label">Nominal Pinjaman</label>
                                    <input type="text" name="nominal" id="nominal_kasbon" class="form-control-lg rupiah" placeholder="Rp..." required autocomplete="off">
                                </div>
                                <div class="form-group mb-3">
                                    <label class="form-label">Tenor</label>
                                    <select name="tenor" class="form-control-lg">
                                        <option value="1">1 Minggu</option>
                                        <option value="2">2 Minggu</option>
                                        <option value="3">3 Minggu</option>
                                        <option value="4">4 Minggu</option>
                                    </select>
                                </div>
                                <div class="form-group mb-3">
                                    <label class="form-label">Keperluan</label>
                                    <textarea name="keterangan" class="form-control-lg" style="height:80px; padding-top:10px;" placeholder="Alasan..." required></textarea>
                                </div>
                                <button type="submit" name="ajukan_kasbon" class="btn-submit">AJUKAN KASBON</button>
                            </form>
                        </div>

                        <div id="view-makan" style="display:none;">
                            
                            <div class="status-card <?= $sudah_harian ? 'done' : '' ?>">
                                <span class="code-badge">UMH</span>
                                <h6><i class="fa fa-sun text-warning"></i> Uang Makan Harian</h6>
                                <?php if($sudah_harian): ?>
                                    <p class="text-success" style="font-weight:700; font-size:18px; margin:5px 0;">Rp <?= number_format($data_harian['nominal']) ?></p>
                                    <small class="text-muted"><i class="fa fa-check"></i> Sudah diambil</small>
                                <?php else: ?>
                                    <form method="POST">
                                        <input type="hidden" name="tipe_makan" value="harian">
                                        <?php if($status_karyawan == 'Borongan'): ?>
                                            <div class="form-group mt-2">
                                                <input type="text" name="nominal" class="form-control-lg rupiah" placeholder="Nominal Harian..." required autocomplete="off">
                                            </div>
                                        <?php else: ?>
                                            <input type="hidden" name="nominal" value="<?= $jatah_makan_db ?>">
                                            <p style="margin:5px 0 10px;">Jatah: <b>Rp <?= number_format($jatah_makan_db) ?></b></p>
                                        <?php endif; ?>
                                        <button type="submit" name="ajukan_makan" class="btn-submit">AMBIL UMH</button>
                                    </form>
                                <?php endif; ?>
                            </div>

                            </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-7 col-md-12">
                <div class="modern-card">
                    <div class="card-header">
                        <h4 style="margin:0; font-size:16px; font-weight:700;">Riwayat Hari Ini</h4>
                    </div>
                    <div class="card-body">
                        <ul style="list-style:none; padding:0; margin:0;">
                            <?php
                            $q_hist = mysqli_query($conn, "SELECT * FROM uang_makan WHERE user_id='$uid' AND DATE(tanggal)='$today' ORDER BY tanggal DESC");
                            
                            if(mysqli_num_rows($q_hist) == 0) echo "<p class='text-muted text-center'>Belum ada data hari ini.</p>";

                            while($h = mysqli_fetch_assoc($q_hist)) {
                                $bg = ($h['status']=='Approved') ? '#dcfce7' : '#fff7ed';
                                $col = ($h['status']=='Approved') ? '#15803d' : '#c2410c';
                                $kd = isset($h['kode_pengajuan']) ? $h['kode_pengajuan'] : 'UMH';
                            ?>
                            <li style="border-bottom:1px solid #f1f5f9; padding:12px 0; display:flex; justify-content:space-between; align-items:center;">
                                <div>
                                    <div style="display:flex; align-items:center; gap:8px;">
                                        <span style="font-size:10px; font-weight:800; background:#f1f5f9; padding:2px 6px; border-radius:4px; color:#64748b;"><?= $kd ?></span>
                                        <h5 style="margin:0; font-size:14px;"><?= $h['keterangan'] ?></h5>
                                    </div>
                                    <small class="text-muted">Jam <?= date('H:i', strtotime($h['tanggal'])) ?></small>
                                </div>
                                <div style="text-align:right;">
                                    <span style="display:block; font-weight:700;">Rp <?= number_format($h['nominal']) ?></span>
                                    <span class="badge" style="background:<?=$bg?>; color:<?=$col?>;"><?= $h['status'] ?></span>
                                </div>
                            </li>
                            <?php } ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php include '../../layout/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        <?php if(!empty($swal_script)) echo $swal_script; ?>

        function switchTab(type) {
            if(type === 'kasbon') {
                document.getElementById('btn-kasbon').classList.add('active');
                document.getElementById('btn-makan').classList.remove('active');
                document.getElementById('view-kasbon').style.display = 'block';
                document.getElementById('view-makan').style.display = 'none';
            } else {
                document.getElementById('btn-makan').classList.add('active');
                document.getElementById('btn-kasbon').classList.remove('active');
                document.getElementById('view-kasbon').style.display = 'none';
                document.getElementById('view-makan').style.display = 'block';
            }
        }

        document.querySelectorAll('.rupiah').forEach(item => {
            item.addEventListener('keyup', function(e) {
                let val = this.value.replace(/[^0-9]/g, '');
                this.value = new Intl.NumberFormat('id-ID').format(val);
            });
        });
    </script>
</body>
</html>