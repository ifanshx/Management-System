<?php 
require_once '../../config/database.php';
require_once '../../config/function.php';

// Cek Login & Role
cek_login(); 
if ($_SESSION['role'] !== 'admin') {
    header("Location: ../dashboard.php");
    exit;
}

// --- 1. PROSES SIMPAN DATA ---
if (isset($_POST['simpan_aturan'])) {
    
    // Validasi CSRF
    verify_csrf_token($_POST['csrf_token']);

    // A. Ambil Input Waktu Utama
    $jam_masuk    = $_POST['jam_masuk']; 
    $jam_ist_out = $_POST['jam_ist_out'];
    $jam_ist_in  = $_POST['jam_ist_in'];
    $jam_pulang  = $_POST['jam_pulang']; 

    // B. Ambil Input Durasi (Menit)
    $d_seb_masuk = abs((int)$_POST['d_seb_masuk']);
    $d_set_masuk = abs((int)$_POST['d_set_masuk']);
    $d_seb_plg   = abs((int)$_POST['d_seb_plg']);
    $d_set_plg   = abs((int)$_POST['d_set_plg']);

    // C. Parameter Lain
    $tol_telat    = abs((int)$_POST['toleransi_telat']);
    $tol_plg_awal = abs((int)$_POST['toleransi_pulang_awal']);
    $min_half     = abs((int)$_POST['target_menit_half']);
    $min_full     = abs((int)$_POST['target_menit_full']);
    
    // [BARU] Ambil Input Min Makan
    $min_makan    = abs((int)$_POST['min_menit_makan']);
    
    $lembur_min   = abs((int)$_POST['lembur_min']);
    $lembur_max   = abs((int)$_POST['lembur_max']);
    $lembur_pot   = abs((int)$_POST['lembur_pengurang']);

    // D. UPDATE QUERY
    $sql = "UPDATE settings_jam_kerja SET 
            jam_masuk = '$jam_masuk',
            jam_istirahat_keluar = '$jam_ist_out',
            jam_istirahat_masuk = '$jam_ist_in',
            jam_pulang = '$jam_pulang',

            durasi_sebelum_masuk = '$d_seb_masuk',
            durasi_setelah_masuk = '$d_set_masuk',
            durasi_sebelum_pulang = '$d_seb_plg',
            durasi_setelah_pulang = '$d_set_plg',

            toleransi_telat = '$tol_telat',
            toleransi_pulang_awal = '$tol_plg_awal',
            target_menit_half = '$min_half',
            target_menit_full = '$min_full',
            min_menit_makan = '$min_makan', 
            
            lembur_min = '$lembur_min',
            lembur_max = '$lembur_max',
            lembur_pengurang = '$lembur_pot'
            WHERE id=1";

    if(mysqli_query($conn, $sql)) {
        $msg_type = "success";
        $msg_content = "Pengaturan Jam Kerja & Durasi Berhasil Diperbarui!";
    } else {
        $msg_type = "error";
        $msg_content = "Gagal menyimpan: " . mysqli_error($conn);
    }
}

// --- 2. AMBIL DATA ---
$d = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM settings_jam_kerja WHERE id=1"));

// Helper view time
function v_time($t) { return date('H:i', strtotime($t)); }

// Helper Estimasi Range
function hitung_range($jam_pusat, $menit_kurang, $menit_tambah) {
    if(empty($jam_pusat)) return "-";
    $start = date('H:i', strtotime("$jam_pusat - $menit_kurang minutes"));
    $end   = date('H:i', strtotime("$jam_pusat + $menit_tambah minutes"));
    return "$start s/d $end";
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <?php include '../../layout/header.php'; ?>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    
    <style>
        body { background-color: #f1f5f9; font-family: 'Inter', sans-serif; }
        .content-wrapper { padding: 30px; }
        
        .card-custom {
            background: #fff; border-radius: 12px; border: 1px solid #e2e8f0;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); margin-bottom: 25px;
        }
        .card-header-custom {
            padding: 20px 25px; border-bottom: 1px solid #f1f5f9;
            font-weight: 700; color: #334155; font-size: 16px;
            display: flex; align-items: center; gap: 10px;
        }
        .card-body-custom { padding: 25px; }

        .form-label { font-size: 12px; font-weight: 600; text-transform: uppercase; color: #64748b; margin-bottom: 8px; display: block; }
        .form-control-custom {
            width: 100%; height: 42px; border: 1px solid #cbd5e1; border-radius: 8px;
            padding: 0 15px; font-size: 14px; color: #1e293b; transition: 0.2s;
        }
        .form-control-custom:focus { border-color: #6366f1; outline: none; box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1); }
        
        .input-group { display: flex; align-items: stretch; }
        .input-group-text {
            background: #f8fafc; border: 1px solid #cbd5e1; border-left: none;
            padding: 0 15px; display: flex; align-items: center; font-size: 13px; color: #64748b;
            border-top-right-radius: 8px; border-bottom-right-radius: 8px;
        }
        .has-addon { border-top-right-radius: 0; border-bottom-right-radius: 0; }

        .btn-save {
            width: 100%; background: #4f46e5; color: white; border: none; padding: 15px;
            border-radius: 10px; font-weight: 700; font-size: 15px; cursor: pointer;
            transition: 0.2s; box-shadow: 0 4px 10px rgba(79, 70, 229, 0.2);
        }
        .btn-save:hover { background: #4338ca; transform: translateY(-2px); }

        .info-box {
            background: #eff6ff; border: 1px solid #bfdbfe; color: #1d4ed8; padding: 15px;
            border-radius: 8px; font-size: 13px; margin-bottom: 20px;
        }
        .divider { border-top: 1px dashed #cbd5e1; margin: 25px 0; position: relative; }
        .divider span {
            position: absolute; top: -10px; left: 50%; transform: translateX(-50%);
            background: #fff; padding: 0 10px; color: #94a3b8; font-size: 11px; font-weight: 700;
        }
        .estimasi-text { margin-top:10px; font-size:11px; color:#64748b; font-style:italic; }
    </style>
</head>
<body>
    <?php include '../../layout/sidebar.php'; ?>
    
    <div class="content-wrapper">
        <div style="margin-bottom: 20px;">
            <h2 style="font-weight:800; color:#1e293b; margin:0;">Konfigurasi Absensi</h2>
            <p style="color:#64748b; margin-top:5px;">Pengaturan jam operasional & durasi toleransi scan.</p>
        </div>

        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">

            <div class="row">
                <div class="col-lg-6">
                    <div class="card-custom">
                        <div class="card-header-custom"><i class="fa fa-clock text-primary"></i> Setting Jam & Durasi Scan</div>
                        <div class="card-body-custom">
                            
                            <div class="info-box">
                                <i class="fa fa-info-circle"></i> 
                                Sistem menghitung range scan secara dinamis: <b>Jam Utama ± Durasi Menit</b>.
                            </div>

                            <div style="background:#f8fafc; padding:15px; border-radius:10px; border:1px solid #e2e8f0; margin-bottom:15px;">
                                <div class="form-group">
                                    <label class="form-label text-primary">JAM MASUK UTAMA</label>
                                    <input type="time" name="jam_masuk" value="<?=v_time($d['jam_masuk'])?>" class="form-control-custom" style="font-weight:bold;">
                                </div>
                                <div class="row mt-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Durasi Sebelum (Menit)</label>
                                        <div class="input-group">
                                            <input type="number" name="d_seb_masuk" value="<?=$d['durasi_sebelum_masuk'] ?? 120?>" class="form-control-custom has-addon">
                                            <span class="input-group-text">Min</span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Durasi Setelah (Menit)</label>
                                        <div class="input-group">
                                            <input type="number" name="d_set_masuk" value="<?=$d['durasi_setelah_masuk'] ?? 120?>" class="form-control-custom has-addon">
                                            <span class="input-group-text">Min</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="estimasi-text">
                                    Estimasi Scan Masuk: 
                                    <b><?= hitung_range($d['jam_masuk'], $d['durasi_sebelum_masuk'], $d['durasi_setelah_masuk']) ?></b>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Jam Istirahat (Keluar)</label>
                                        <input type="time" name="jam_ist_out" value="<?=v_time($d['jam_istirahat_keluar'])?>" class="form-control-custom">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Jam Istirahat (Masuk)</label>
                                        <input type="time" name="jam_ist_in" value="<?=v_time($d['jam_istirahat_masuk'])?>" class="form-control-custom">
                                    </div>
                                </div>
                            </div>

                            <div class="divider"><span>JAM PULANG</span></div>

                            <div style="background:#fff1f2; padding:15px; border-radius:10px; border:1px solid #fda4af; margin-bottom:15px;">
                                <div class="form-group">
                                    <label class="form-label text-danger">JAM PULANG UTAMA</label>
                                    <input type="time" name="jam_pulang" value="<?=v_time($d['jam_pulang'])?>" class="form-control-custom" style="font-weight:bold;">
                                </div>
                                <div class="row mt-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Durasi Sebelum (Menit)</label>
                                        <div class="input-group">
                                            <input type="number" name="d_seb_plg" value="<?=$d['durasi_sebelum_pulang'] ?? 60?>" class="form-control-custom has-addon">
                                            <span class="input-group-text">Min</span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Durasi Setelah (Menit)</label>
                                        <div class="input-group">
                                            <input type="number" name="d_set_plg" value="<?=$d['durasi_setelah_pulang'] ?? 240?>" class="form-control-custom has-addon">
                                            <span class="input-group-text">Min</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="estimasi-text" style="color:#9f1239;">
                                    Estimasi Scan Pulang: 
                                    <b><?= hitung_range($d['jam_pulang'], $d['durasi_sebelum_pulang'], $d['durasi_setelah_pulang']) ?></b>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    
                    <div class="card-custom">
                        <div class="card-header-custom"><i class="fa fa-gavel text-warning"></i> Toleransi & Target</div>
                        <div class="card-body-custom">
                            <div class="row">
                                <div class="col-md-6">
                                    <label class="form-label">Toleransi Terlambat</label>
                                    <div class="input-group mb-3">
                                        <input type="number" name="toleransi_telat" value="<?=$d['toleransi_telat']?>" class="form-control-custom has-addon">
                                        <span class="input-group-text">Min</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Toleransi Plg Awal</label>
                                    <div class="input-group mb-3">
                                        <input type="number" name="toleransi_pulang_awal" value="<?=$d['toleransi_pulang_awal']?>" class="form-control-custom has-addon">
                                        <span class="input-group-text">Min</span>
                                    </div>
                                </div>
                            </div>
                            <div class="divider"><span>MINIMAL DURASI KERJA</span></div>
                            <div class="row">
                                <div class="col-md-6">
                                    <label class="form-label">Dihitung 1/2 Hari</label>
                                    <div class="input-group mb-3">
                                        <input type="number" name="target_menit_half" value="<?=$d['target_menit_half']?>" class="form-control-custom has-addon">
                                        <span class="input-group-text">Min</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Dihitung Penuh (Full)</label>
                                    <div class="input-group mb-3">
                                        <input type="number" name="target_menit_full" value="<?=$d['target_menit_full']?>" class="form-control-custom has-addon">
                                        <span class="input-group-text">Min</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-12">
                                    <label class="form-label text-success">Syarat Dapat Uang Makan</label>
                                    <div class="input-group mb-3">
                                        <input type="number" name="min_menit_makan" value="<?=$d['min_menit_makan'] ?? 330?>" class="form-control-custom has-addon" style="border-color:#10b981;">
                                        <span class="input-group-text" style="background:#ecfdf5; color:#047857;">Menit Kerja (Min)</span>
                                    </div>
                                    <small style="color:#64748b; font-size:11px; margin-top:-10px; display:block;">
                                         Contoh: 330 menit = 5.5 Jam Kerja Bersih.
                                    </small>
                                </div>
                            </div>

                        </div>
                    </div>

                    <div class="card-custom">
                        <div class="card-header-custom"><i class="fa fa-bolt text-success"></i> Konfigurasi Lembur</div>
                        <div class="card-body-custom">
                            <div class="form-group mb-3">
                                <label class="form-label">Minimal Lembur (Menit)</label>
                                <input type="number" name="lembur_min" value="<?=$d['lembur_min']?>" class="form-control-custom">
                                <small style="font-size:10px; color:#94a3b8;">Lembur baru dihitung jika kelebihan jam kerja > angka ini.</small>
                            </div>
                            <div class="form-group mb-3">
                                <label class="form-label">Maksimal Lembur (Menit)</label>
                                <input type="number" name="lembur_max" value="<?=$d['lembur_max']?>" class="form-control-custom">
                            </div>
                            <div class="form-group mb-3">
                                <label class="form-label">Pengurangan Lembur (Pot. Maghrib)</label>
                                <input type="number" name="lembur_pengurang" value="<?=$d['lembur_pengurang']?>" class="form-control-custom">
                                <small style="font-size:10px; color:#94a3b8;">Jumlah menit yang dipotong otomatis jika pulang lewat jam 18:00.</small>
                            </div>
                        </div>
                    </div>

                    <button type="submit" name="simpan_aturan" class="btn-save">
                        <i class="fa fa-save"></i> SIMPAN SEMUA PENGATURAN
                    </button>

                </div>
            </div>
        </form>
    </div>

    <?php include '../../layout/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    <?php if(isset($msg_type)): ?>
        Swal.fire({
            icon: '<?= $msg_type ?>',
            title: '<?= ($msg_type == 'success') ? 'Berhasil' : 'Gagal' ?>',
            text: '<?= $msg_content ?>',
            confirmButtonColor: '#4f46e5'
        });
    <?php endif; ?>
    </script>
</body>
</html>