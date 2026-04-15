<?php
// 1. KONEKSI & SESSION
require_once '../../config/database.php';
require_once '../../config/function.php';
date_default_timezone_set('Asia/Jakarta');
session_start();

if (!isset($_SESSION['status'])) { header("Location: ../../login.php"); exit; }
$user_id = $_SESSION['user_id'] ?? 1;

// --- AJAX: GET DETAIL & RIWAYAT ---
if (isset($_POST['act']) && $_POST['act'] == 'get_detail') {
    $id = intval($_POST['id']);
    
    // Ambil Data Utama
    $q_main = $conn->query("SELECT * FROM utang_piutang WHERE id='$id'");
    $main = $q_main->fetch_assoc();
    
    // Ambil Riwayat
    $q_hist = $conn->query("SELECT * FROM riwayat_utang_piutang WHERE utang_piutang_id='$id' ORDER BY tanggal DESC");
    $hist = [];
    while($r = $q_hist->fetch_assoc()) {
        $hist[] = $r;
    }

    echo json_encode(['main' => $main, 'hist' => $hist]);
    exit;
}

// --- LOGIKA 1: TAMBAH DATA BARU (LENGKAP DENGAN FOTO & KONTAK) ---
if (isset($_POST['simpan_baru'])) {
    $jenis      = $_POST['jenis'];
    $nama       = $conn->real_escape_string($_POST['nama_pihak']);
    $hp         = $conn->real_escape_string($_POST['no_telp']); // Baru
    $nominal    = floatval(str_replace('.', '', $_POST['nominal']));
    $ket        = $conn->real_escape_string($_POST['keterangan']);
    $metode     = $_POST['metode'];
    $tempo      = !empty($_POST['jatuh_tempo']) ? $_POST['jatuh_tempo'] : NULL;
    $val_tempo  = $tempo ? "'$tempo'" : "NULL";
    $tgl_skrg   = date('Y-m-d');

    // -- LOGIKA UPLOAD FOTO --
    $foto_nama = NULL;
    if (!empty($_FILES['foto_bukti']['name'])) {
        $ext = pathinfo($_FILES['foto_bukti']['name'], PATHINFO_EXTENSION);
        $foto_nama = 'UP_' . time() . '.' . $ext;
        $tmp = $_FILES['foto_bukti']['tmp_name'];
        // Pastikan folder uploads ada (buat folder 'uploads' sejajar dengan file ini jika belum ada)
        move_uploaded_file($tmp, 'uploads/' . $foto_nama);
    }
    $val_foto = $foto_nama ? "'$foto_nama'" : "NULL";

    if ($nominal > 0) {
        // Insert Master
        $q = "INSERT INTO utang_piutang (jenis, nama_pihak, no_telp, nominal_awal, nominal_sisa, keterangan, foto_bukti, jatuh_tempo, status) 
              VALUES ('$jenis', '$nama', '$hp', '$nominal', '$nominal', '$ket', $val_foto, $val_tempo, 'Belum Lunas')";
        
        if ($conn->query($q)) {
            // Auto Catat ke Kas
            $kas_jenis = ($jenis == 'Utang') ? 'Masuk' : 'Keluar';
            $kas_ket   = "TRX " . strtoupper($jenis) . " BARU - " . $nama;
            
            $q_kas = "INSERT INTO transaksi_kas (user_id, tanggal, jenis, keterangan, nominal, metode) 
                      VALUES ('$user_id', '$tgl_skrg', '$kas_jenis', '$kas_ket', '$nominal', '$metode')";
            $conn->query($q_kas);

            $_SESSION['success'] = "Data berhasil disimpan lengkap!";
        } else {
            $_SESSION['error'] = "Gagal: " . $conn->error;
        }
    }
    header("Location: utang_piutang.php"); exit;
}

// --- LOGIKA 2: BAYAR CICILAN ---
if (isset($_POST['bayar_cicilan'])) {
    $id_up      = $_POST['id_up'];
    $nominal    = floatval(str_replace('.', '', $_POST['nominal_bayar']));
    $tgl        = $_POST['tanggal_bayar'];
    $ket_bayar  = $conn->real_escape_string($_POST['ket_bayar']);
    $metode_byr = $_POST['metode_bayar'];

    $cek = $conn->query("SELECT * FROM utang_piutang WHERE id='$id_up'")->fetch_assoc();
    
    if ($nominal > 0 && $nominal <= $cek['nominal_sisa']) {
        // Insert History
        $stmt = $conn->prepare("INSERT INTO riwayat_utang_piutang (utang_piutang_id, user_id, tanggal, nominal, keterangan) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("iisds", $id_up, $user_id, $tgl, $nominal, $ket_bayar);
        $stmt->execute();

        // Update Master
        $sisa_baru = $cek['nominal_sisa'] - $nominal;
        $status = ($sisa_baru <= 0) ? 'Lunas' : 'Belum Lunas';
        $conn->query("UPDATE utang_piutang SET nominal_sisa='$sisa_baru', status='$status' WHERE id='$id_up'");

        // Catat Kas
        $tipe_kas = ($cek['jenis'] == 'Utang') ? 'Keluar' : 'Masuk';
        $ket_kas = "CICILAN " . strtoupper($cek['jenis']) . " - " . $cek['nama_pihak'];
        $conn->query("INSERT INTO transaksi_kas (user_id, tanggal, jenis, keterangan, nominal, metode) VALUES ('$user_id', '$tgl', '$tipe_kas', '$ket_kas', '$nominal', '$metode_byr')");

        $_SESSION['success'] = "Pembayaran berhasil dicatat!";
    } else {
        $_SESSION['error'] = "Nominal tidak valid.";
    }
    header("Location: utang_piutang.php"); exit;
}

// --- LOGIKA 3: HAPUS ---
if (isset($_GET['hapus'])) {
    $id = intval($_GET['hapus']);
    // Hapus foto jika ada
    $cek_foto = $conn->query("SELECT foto_bukti FROM utang_piutang WHERE id='$id'")->fetch_assoc();
    if($cek_foto['foto_bukti'] && file_exists('uploads/'.$cek_foto['foto_bukti'])){
        unlink('uploads/'.$cek_foto['foto_bukti']);
    }

    $conn->query("DELETE FROM riwayat_utang_piutang WHERE utang_piutang_id='$id'");
    $conn->query("DELETE FROM utang_piutang WHERE id='$id'");
    $_SESSION['success'] = "Data dihapus.";
    header("Location: utang_piutang.php"); exit;
}

// --- DATA & STATS ---
$q_utang = $conn->query("SELECT * FROM utang_piutang WHERE jenis='Utang' AND status='Belum Lunas' ORDER BY created_at DESC");
$q_piutang = $conn->query("SELECT * FROM utang_piutang WHERE jenis='Piutang' AND status='Belum Lunas' ORDER BY created_at DESC");
$stat_utang = $conn->query("SELECT SUM(nominal_sisa) as t FROM utang_piutang WHERE jenis='Utang' AND status='Belum Lunas'")->fetch_assoc()['t'];
$stat_piutang = $conn->query("SELECT SUM(nominal_sisa) as t FROM utang_piutang WHERE jenis='Piutang' AND status='Belum Lunas'")->fetch_assoc()['t'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include '../../layout/header.php'; ?>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    
    <style>
        /* Styles Dasar */
        body { background-color: #f1f5f9; font-family: 'Inter', sans-serif; color: #334155; }
        .content-wrapper { padding: 20px 30px !important; }
        .card-pro { background: #fff; border-radius: 12px; border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); margin-bottom: 25px; overflow: hidden; }
        .card-header-pro { padding: 15px 20px; border-bottom: 1px solid #f1f5f9; background: #fff; display: flex; align-items: center; justify-content: space-between; }
        .card-header-pro h3 { margin: 0; font-size: 15px; font-weight: 700; color: #0f172a; text-transform: uppercase; letter-spacing: 0.5px; }
        .card-body-pro { padding: 25px; }

        /* Form Layout */
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 25px; }
        .form-section-title { grid-column: 1 / -1; font-size: 12px; font-weight: 800; color: #94a3b8; text-transform: uppercase; margin-bottom: 10px; border-bottom: 2px solid #f1f5f9; padding-bottom: 5px; }
        
        .form-group { margin-bottom: 15px; }
        .form-label { display: block; font-size: 11px; font-weight: 700; color: #475569; margin-bottom: 6px; text-transform: uppercase; }
        .form-control { width: 100%; padding: 10px 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 13px; outline: none; transition: 0.2s; background: #f8fafc; }
        .form-control:focus { border-color: #2563eb; background: #fff; box-shadow: 0 0 0 3px rgba(37,99,235,0.1); }
        
        /* Special Inputs */
        .input-rupiah { font-family: monospace; font-weight: 700; color: #2563eb; font-size: 15px; letter-spacing: 1px; }
        
        /* Upload Area */
        .upload-area { border: 2px dashed #cbd5e1; border-radius: 8px; padding: 20px; text-align: center; cursor: pointer; transition: 0.2s; background: #f8fafc; position: relative; }
        .upload-area:hover { border-color: #2563eb; background: #eff6ff; }
        .upload-icon { font-size: 24px; color: #94a3b8; margin-bottom: 5px; }
        .upload-text { font-size: 11px; color: #64748b; }
        #previewImg { max-width: 100%; max-height: 150px; border-radius: 6px; display: none; margin-top: 10px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); }

        .btn-submit { grid-column: 1 / -1; width: 100%; padding: 14px; background: #2563eb; color: #fff; border: none; border-radius: 8px; font-weight: 700; cursor: pointer; font-size: 14px; transition: 0.2s; display: flex; align-items: center; justify-content: center; gap: 10px; }
        .btn-submit:hover { background: #1d4ed8; transform: translateY(-1px); }

        /* Responsive */
        @media (max-width: 768px) { .form-grid { grid-template-columns: 1fr; } }
        
        /* Table & Stats (Sama seperti sebelumnya) */
        .stats-mini { display: flex; gap: 15px; margin-bottom: 20px; }
        .stat-card { flex: 1; background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 15px; display: flex; flex-direction: column; justify-content: center; height: 80px; border-left-width: 4px; box-shadow: 0 2px 4px rgba(0,0,0,0.02); }
        .stat-lbl { font-size: 10px; font-weight: 700; text-transform: uppercase; color: #64748b; margin-bottom: 4px; }
        .stat-val { font-size: 18px; font-weight: 800; font-family: monospace; }
        
        .table-history { width: 100%; border-collapse: collapse; }
        .table-history th { text-align: left; padding: 12px 15px; background: #f8fafc; color: #64748b; font-size: 10px; font-weight: 700; text-transform: uppercase; }
        .table-history td { padding: 12px 15px; border-bottom: 1px solid #f1f5f9; font-size: 13px; vertical-align: middle; }
        .badge-status { padding: 3px 8px; border-radius: 4px; font-size: 10px; font-weight: 700; display: inline-block; text-transform: uppercase; }
        .bs-utang { background: #fee2e2; color: #991b1b; } .bs-piutang { background: #dcfce7; color: #166534; }
        
        /* Modal */
        .modal-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.5); z-index: 9999; backdrop-filter: blur(2px); align-items: center; justify-content: center; }
        .modal-box { background: #fff; width: 90%; max-width: 500px; border-radius: 12px; overflow: hidden; animation: slideUp 0.3s; }
        @keyframes slideUp { from {transform: translateY(20px); opacity:0;} to {transform: translateY(0); opacity:1;} }
    </style>
</head>
<body>
    <?php include '../../layout/sidebar.php'; ?>

    <div class="content-wrapper">
        <div style="margin-bottom: 25px;">
            <h2 style="font-weight:800; color:#0f172a; margin:0; font-size: 22px;">Manajemen Utang Piutang</h2>
            <p style="color:#64748b; margin-top:4px; font-size:13px;">Catat kewajiban dan aset piutang perusahaan secara rapi.</p>
        </div>

        <div class="stats-mini">
            <div class="stat-card" style="border-left-color: #ef4444;">
                <span class="stat-lbl">Sisa Utang (Kewajiban)</span>
                <span class="stat-val" style="color:#ef4444;">Rp <?= number_format($stat_utang, 0, ',', '.') ?></span>
            </div>
            <div class="stat-card" style="border-left-color: #10b981;">
                <span class="stat-lbl">Sisa Piutang (Aset)</span>
                <span class="stat-val" style="color:#10b981;">Rp <?= number_format($stat_piutang, 0, ',', '.') ?></span>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12"> <form method="POST" enctype="multipart/form-data">
                    <div class="card-pro">
                        <div class="card-header-pro">
                            <h3><i class="fa fa-edit" style="margin-right:8px; color:#2563eb;"></i> Form Transaksi Baru</h3>
                            <div style="font-size:11px; color:#64748b;">Isi data dengan lengkap</div>
                        </div>
                        <div class="card-body-pro">
                            <div class="form-grid">
                                
                                <div>
                                    <div class="form-section-title">1. Rincian Transaksi</div>
                                    <div class="form-group">
                                        <label class="form-label">Jenis Transaksi</label>
                                        <select name="jenis" class="form-control select2" required>
                                            <option value="Utang">🔴 UTANG (Kita Pinjam Uang)</option>
                                            <option value="Piutang">🟢 PIUTANG (Kita Pinjamkan Uang)</option>
                                        </select>
                                    </div>
                                    <div class="form-grid" style="grid-template-columns: 2fr 1fr; gap:15px; margin-bottom:0;">
                                        <div class="form-group">
                                            <label class="form-label">Nominal (Rp)</label>
                                            <input type="text" name="nominal" class="form-control input-rupiah" placeholder="0" onkeyup="formatRupiah(this)" required>
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">Sumber Dana</label>
                                            <select name="metode" class="form-control" required>
                                                <option value="Cash">Tunai</option>
                                                <option value="ATM">Transfer/ATM</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Jatuh Tempo (Opsional)</label>
                                        <input type="date" name="jatuh_tempo" class="form-control">
                                    </div>
                                </div>

                                <div>
                                    <div class="form-section-title">2. Pihak & Bukti</div>
                                    <div class="form-grid" style="gap:15px; margin-bottom:0;">
                                        <div class="form-group">
                                            <label class="form-label">Nama Pihak (Orang/Toko)</label>
                                            <input type="text" name="nama_pihak" class="form-control" placeholder="Cth: Bpk Budi" required>
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">No. WhatsApp/HP</label>
                                            <input type="text" name="no_telp" class="form-control" placeholder="08xxxx">
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label class="form-label">Bukti Foto / Nota (Klik untuk upload)</label>
                                        <label class="upload-area">
                                            <input type="file" name="foto_bukti" accept="image/*" style="display:none;" onchange="previewImage(this)">
                                            <div class="upload-icon"><i class="fa fa-cloud-upload-alt"></i></div>
                                            <div class="upload-text">Format JPG/PNG. Maks 2MB</div>
                                            <img id="previewImg" src="#" alt="Preview">
                                        </label>
                                    </div>

                                    <div class="form-group">
                                        <label class="form-label">Keterangan Tambahan</label>
                                        <textarea name="keterangan" class="form-control" rows="1" placeholder="Catatan singkat..."></textarea>
                                    </div>
                                </div>

                                <button type="submit" name="simpan_baru" class="btn-submit">
                                    <i class="fa fa-save"></i> SIMPAN DATA & CATAT KE KAS
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <div class="col-lg-12">
                <div class="card-pro">
                    <div class="card-header-pro">
                        <h3><i class="fa fa-list-ul" style="margin-right:8px; color:#64748b;"></i> Daftar Tanggungan Aktif</h3>
                    </div>
                    <div style="max-height: 500px; overflow-y: auto;">
                        <table class="table-history">
                            <thead>
                                <tr>
                                    <th>Pihak Terkait</th>
                                    <th>Kontak</th>
                                    <th>Sisa Tagihan</th>
                                    <th>Tempo</th>
                                    <th style="text-align:right;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $data_all = [];
                                while($u = $q_utang->fetch_assoc()) $data_all[] = $u;
                                while($p = $q_piutang->fetch_assoc()) $data_all[] = $p;
                                usort($data_all, function($a, $b) { return $b['id'] - $a['id']; });
                                ?>

                                <?php foreach($data_all as $r): ?>
                                <?php 
                                    $badge = ($r['jenis'] == 'Utang') ? 'bs-utang' : 'bs-piutang';
                                    $color = ($r['jenis'] == 'Utang') ? 'text-danger' : 'text-success';
                                    $icon  = ($r['jenis'] == 'Utang') ? 'fa-arrow-down' : 'fa-arrow-up';
                                    $wa_link = $r['no_telp'] ? "https://wa.me/".preg_replace('/^0/', '62', $r['no_telp']) : "#";
                                ?>
                                <tr>
                                    <td>
                                        <div style="font-weight:700; color:#334155; font-size:13px;"><?= $r['nama_pihak'] ?></div>
                                        <span class="badge-status <?= $badge ?>"><i class="fa <?= $icon ?>"></i> <?= strtoupper($r['jenis']) ?></span>
                                        <?php if($r['foto_bukti']): ?>
                                            <a href="uploads/<?= $r['foto_bukti'] ?>" target="_blank" style="font-size:10px; color:#2563eb; margin-left:5px; text-decoration:none;"><i class="fa fa-paperclip"></i> Bukti</a>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if($r['no_telp']): ?>
                                            <a href="<?= $wa_link ?>" target="_blank" style="color:#25d366; font-weight:600; text-decoration:none; font-size:12px;">
                                                <i class="fab fa-whatsapp"></i> <?= $r['no_telp'] ?>
                                            </a>
                                        <?php else: ?>
                                            <span style="color:#94a3b8;">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="<?= $color ?>" style="font-weight:700; font-family:monospace;">Rp <?= number_format($r['nominal_sisa'], 0, ',', '.') ?></div>
                                        <div style="font-size:10px; color:#64748b;">Total: <?= number_format($r['nominal_awal'], 0, ',', '.') ?></div>
                                    </td>
                                    <td>
                                        <?php if($r['jatuh_tempo']): ?>
                                            <span style="color:#ef4444; font-weight:600; font-size:11px;"><?= date('d M Y', strtotime($r['jatuh_tempo'])) ?></span>
                                        <?php else: ?>
                                            <span style="color:#94a3b8;">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="text-align:right;">
                                        <button onclick="openModal(<?= $r['id'] ?>)" class="btn-sm btn-primary" style="border:none; border-radius:4px; padding:5px 10px; background:#eff6ff; color:#2563eb; cursor:pointer;">
                                            <i class="fa fa-wallet"></i> Bayar
                                        </button>
                                        <a href="?hapus=<?= $r['id'] ?>" onclick="return confirm('Hapus permanen?')" style="color:#ef4444; margin-left:10px;"><i class="fa fa-trash"></i></a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="modalAction" class="modal-overlay">
        <div class="modal-box">
            <div style="padding: 15px 20px; border-bottom: 1px solid #e2e8f0; display:flex; justify-content:space-between; align-items:center; background:#f8fafc;">
                <h3 style="margin:0; font-size:15px; color:#1e293b;">Input Pembayaran / Cicilan</h3>
                <span onclick="closeModal()" style="cursor:pointer; font-size:20px; color:#94a3b8;">&times;</span>
            </div>
            <div style="padding:20px; background:#fff;">
                <form method="POST">
                    <input type="hidden" name="id_up" id="inpIdUp">
                    <div style="text-align:center; margin-bottom:15px;">
                        <div style="font-size:11px; color:#64748b;">PIHAK TERKAIT</div>
                        <div style="font-weight:700; color:#1e293b; font-size:14px;" id="modNamaPihak">-</div>
                        <div style="font-size:24px; font-weight:800; color:#2563eb; margin-top:5px; font-family:monospace;" id="txtSisa">Rp 0</div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Nominal Bayar</label>
                        <input type="text" name="nominal_bayar" class="form-control input-rupiah" placeholder="0" onkeyup="formatRupiah(this)" required>
                    </div>
                    <div class="form-grid" style="grid-template-columns:1fr 1fr; gap:15px; margin-bottom:15px;">
                        <div>
                            <label class="form-label">Tanggal</label>
                            <input type="date" name="tanggal_bayar" class="form-control" value="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div>
                            <label class="form-label">Metode</label>
                            <select name="metode_bayar" class="form-control">
                                <option value="ATM">Transfer/ATM</option>
                                <option value="Cash">Cash</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Catatan</label>
                        <input type="text" name="ket_bayar" class="form-control" placeholder="Cth: Cicilan ke-2">
                    </div>

                    <button type="submit" name="bayar_cicilan" class="btn-submit" style="background:#10b981;">
                        <i class="fa fa-check-circle"></i> SIMPAN PEMBAYARAN
                    </button>
                </form>
                
                <div style="margin-top:20px; border-top:1px solid #f1f5f9; padding-top:10px;">
                    <div style="font-size:10px; font-weight:700; color:#94a3b8; margin-bottom:10px;">RIWAYAT TERAKHIR</div>
                    <table class="table-history" id="tblRiwayat"></table>
                </div>
            </div>
        </div>
    </div>

    <?php include '../../layout/footer.php'; ?>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        $(document).ready(function() {
            $('.select2').select2();
        });

        function formatRupiah(el) {
            let val = el.value.replace(/[^0-9]/g, '');
            el.value = new Intl.NumberFormat('id-ID').format(val);
        }

        function previewImage(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    $('#previewImg').attr('src', e.target.result).show();
                    $('.upload-icon, .upload-text').hide();
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        function openModal(id) {
            document.getElementById('modalAction').style.display = 'flex';
            $.post('', { act: 'get_detail', id: id }, function(res) {
                let data = JSON.parse(res);
                document.getElementById('inpIdUp').value = id;
                document.getElementById('modNamaPihak').innerText = data.main.nama_pihak;
                document.getElementById('txtSisa').innerText = 'Rp ' + new Intl.NumberFormat('id-ID').format(data.main.nominal_sisa);
                
                let h = '';
                data.hist.forEach(item => {
                    h += `<tr>
                        <td style="color:#64748b;">${item.tanggal}</td>
                        <td>${item.keterangan}</td>
                        <td style="text-align:right; font-weight:700;">Rp ${new Intl.NumberFormat('id-ID').format(item.nominal)}</td>
                    </tr>`;
                });
                document.getElementById('tblRiwayat').innerHTML = h || '<tr><td colspan="3" style="text-align:center; color:#cbd5e1;">Belum ada history</td></tr>';
            });
        }

        function closeModal() {
            document.getElementById('modalAction').style.display = 'none';
        }

        <?php if(isset($_SESSION['success'])): ?>
            Swal.fire({icon:'success', title:'Sukses', text:'<?= $_SESSION['success'] ?>', timer:1500, showConfirmButton:false});
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>
    </script>
</body>
</html>