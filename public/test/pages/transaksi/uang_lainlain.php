<?php 
// 1. CONFIG
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '../../config/database.php';
require_once '../../config/function.php'; 

date_default_timezone_set('Asia/Jakarta');
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

$swal_script = "";

// --- 2. PROSES SIMPAN (SINKRON KE KAS) ---
if (isset($_POST['simpan'])) {
    $user_id    = (int)$_POST['user_id'];
    $tanggal    = $_POST['tanggal'];
    $nominal    = (int)str_replace('.', '', $_POST['nominal']);
    $keterangan = mysqli_real_escape_string($conn, $_POST['keterangan']);
    
    $pilihan_jenis = $_POST['jenis_select'];

    // Ambil nama karyawan
    $q_nama = mysqli_fetch_assoc(mysqli_query($conn, "SELECT fullname FROM users WHERE id='$user_id'"));
    $nama_karyawan = $q_nama['fullname'] ?? 'Karyawan';

    // Logika Kategori
    if ($pilihan_jenis === 'Lainnya') {
        $jenis    = mysqli_real_escape_string($conn, ucwords($_POST['jenis_manual']));
        $kategori = $_POST['kategori_manual']; 
    } else {
        $jenis = mysqli_real_escape_string($conn, $pilihan_jenis);
        $list_pendapatan = ['Bonus', 'THR', 'Insentif', 'Tunjangan', 'Uang Saku'];
        $kategori = in_array($jenis, $list_pendapatan) ? 'Pendapatan' : 'Potongan';
    }

    if ($nominal > 0) {
        // A. Insert ke uang_lainlain
        $q = "INSERT INTO uang_lainlain (user_id, tanggal, jenis, kategori, nominal, keterangan) 
              VALUES ('$user_id', '$tanggal', '$jenis', '$kategori', '$nominal', '$keterangan')";
        
        if (mysqli_query($conn, $q)) {
            
            // B. Sinkronisasi ke transaksi_kas
            // LOGIKA PENTING:
            // - Jika Kategori 'Pendapatan' (Karyawan dpt uang) -> Kas KELUAR
            // - Jika Kategori 'Potongan' (Karyawan bayar denda) -> Kas MASUK
            
            $jenis_kas = ($kategori == 'Pendapatan') ? 'Keluar' : 'Masuk';
            
            // Format Keterangan Kas: "JENIS TRANSAKSI - NAMA KARYAWAN"
            $ket_kas = strtoupper($jenis) . " - " . strtoupper($nama_karyawan);
            if(!empty($keterangan)) $ket_kas .= " (" . strtoupper($keterangan) . ")";
            
            $admin_id = $_SESSION['user_id'];

            $q_kas = "INSERT INTO transaksi_kas (user_id, tanggal, jenis, keterangan, nominal, metode) 
                      VALUES ('$admin_id', '$tanggal', '$jenis_kas', '$ket_kas', '$nominal', 'Cash')";
            
            mysqli_query($conn, $q_kas);

            $swal_script = "Swal.fire({icon: 'success', title: 'Berhasil', text: 'Data & Kas berhasil disimpan!', timer: 1500, showConfirmButton: false});";
        } else {
            $swal_script = "Swal.fire({icon: 'error', title: 'Gagal', text: 'Error: ".mysqli_error($conn)."'});";
        }
    } else {
        $swal_script = "Swal.fire({icon: 'warning', title: 'Invalid', text: 'Nominal harus lebih dari 0'});";
    }
}

// --- 3. PROSES HAPUS (SINKRON HAPUS KAS) ---
if (isset($_GET['hapus_id'])) {
    $id = (int)$_GET['hapus_id'];

    // Ambil data dulu sebelum dihapus
    $cek = mysqli_query($conn, "SELECT ul.*, u.fullname FROM uang_lainlain ul JOIN users u ON ul.user_id = u.id WHERE ul.id='$id'");
    $d = mysqli_fetch_assoc($cek);

    if($d) {
        $nominal_hapus = $d['nominal'];
        $tgl_hapus     = $d['tanggal'];
        $kategori_hapus = $d['kategori'];
        
        // Cek Logika Jenis Kas yang mau dihapus
        $jenis_kas_hapus = ($kategori_hapus == 'Pendapatan') ? 'Keluar' : 'Masuk';
        
        $ket_kas_hapus = strtoupper($d['jenis']) . " - " . strtoupper($d['fullname']);
        if(!empty($d['keterangan'])) $ket_kas_hapus .= " (" . strtoupper($d['keterangan']) . ")";

        if(mysqli_query($conn, "DELETE FROM uang_lainlain WHERE id=$id")){
            
            // Hapus Transaksi Kas yang cocok
            $q_hapus_kas = "DELETE FROM transaksi_kas 
                            WHERE tanggal='$tgl_hapus' 
                              AND nominal='$nominal_hapus' 
                              AND jenis='$jenis_kas_hapus' 
                              AND keterangan='$ket_kas_hapus'
                            LIMIT 1";
            mysqli_query($conn, $q_hapus_kas);

            $swal_script = "Swal.fire({icon: 'success', title: 'Terhapus', text: 'Data & Kas berhasil dihapus.', timer: 1500, showConfirmButton: false}).then(() => { window.location='uang_lainlain.php'; });";
        } else {
            $swal_script = "Swal.fire({icon: 'error', title: 'Gagal', text: 'Gagal menghapus data.'});";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <?php include '../../layout/header.php'; ?>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root { 
            --primary: #4f46e5; 
            --primary-dark: #4338ca;
            --bg: #f8fafc; 
            --text-main: #334155;
            --text-light: #64748b;
            --sidebar-width: 250px; /* Sesuaikan dengan sidebar Anda */
        }
        
        body { background-color: var(--bg); font-family: 'Plus Jakarta Sans', sans-serif; color: var(--text-main); }
        
        .content-wrapper { padding: 30px 20px; margin-left: var(--sidebar-width); transition: margin-left 0.3s ease; min-height: 100vh; }
        @media (max-width: 768px) { .content-wrapper { margin-left: 0; padding: 20px 15px; padding-bottom:80px; } }

        /* Header */
        .page-header { margin-bottom: 30px; }
        .page-title { font-size: 24px; font-weight: 800; color: #1e293b; margin: 0; letter-spacing: -0.5px; }
        .page-subtitle { font-size: 14px; color: var(--text-light); margin-top: 5px; }

        /* Modern Card */
        .glass-card { 
            background: #fff; border-radius: 20px; border: 1px solid #e2e8f0; 
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05), 0 2px 4px -1px rgba(0,0,0,0.03); 
            overflow: hidden; margin-bottom: 25px; transition: transform 0.2s;
        }
        
        .card-header { 
            padding: 20px 25px; border-bottom: 1px solid #f1f5f9; 
            display: flex; justify-content: space-between; align-items: center; 
            background: linear-gradient(to bottom, #fff, #fcfcfc);
        }
        .card-title { font-size: 15px; font-weight: 700; color: #1e293b; display: flex; align-items: center; gap: 10px; margin: 0; }
        .card-body { padding: 25px; }

        /* Form Elements */
        .form-group { margin-bottom: 20px; }
        .form-label { display: block; font-size: 12px; font-weight: 700; color: var(--text-light); margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px; }
        
        .form-control-custom { 
            width: 100%; height: 48px; padding: 0 15px; border-radius: 12px; 
            border: 2px solid #f1f5f9; background: #f8fafc; color: #334155;
            font-size: 14px; font-weight: 500; transition: all 0.2s; box-sizing: border-box;
        }
        .form-control-custom:focus { 
            border-color: var(--primary); background: #fff; outline: none; 
            box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1); 
        }

        .btn-save { 
            width: 100%; height: 50px; background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%); 
            color: #fff; border: none; border-radius: 14px; font-weight: 700; font-size: 15px;
            cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; 
            transition: all 0.2s; box-shadow: 0 4px 10px rgba(79, 70, 229, 0.3);
        }
        .btn-save:hover { transform: translateY(-2px); box-shadow: 0 6px 15px rgba(79, 70, 229, 0.4); }
        .btn-save:active { transform: translateY(0); }

        /* Custom Input Box */
        .custom-input-box {
            display: none; background: #eff6ff; padding: 20px; border-radius: 14px; 
            border: 1px dashed #bfdbfe; margin-top: 15px;
        }
        .radio-group { display: flex; gap: 15px; margin-top: 10px; }
        .radio-item { 
            display: flex; align-items: center; gap: 8px; cursor: pointer; 
            font-size: 13px; font-weight: 600; padding: 10px 15px; background: #fff; 
            border-radius: 10px; border: 1px solid #e2e8f0; transition: 0.2s; flex: 1; justify-content: center;
        }
        .radio-item:hover { border-color: var(--primary); }
        .radio-item input { accent-color: var(--primary); width: 16px; height: 16px; }

        /* Table Styles */
        .table-container { overflow-x: auto; border-radius: 12px; border: 1px solid #e2e8f0; }
        .table-custom { width: 100%; border-collapse: separate; border-spacing: 0; min-width: 600px; }
        .table-custom th { 
            background: #f8fafc; padding: 16px 20px; font-size: 11px; font-weight: 700; 
            text-transform: uppercase; color: var(--text-light); border-bottom: 1px solid #e2e8f0; text-align: left;
        }
        .table-custom td { padding: 16px 20px; border-bottom: 1px solid #f1f5f9; font-size: 13px; vertical-align: middle; color: #334155; }
        .table-custom tr:last-child td { border-bottom: none; }
        .table-custom tr:hover td { background: #fdfdfd; }

        /* Status Badges */
        .badge-pill { padding: 6px 12px; border-radius: 30px; font-weight: 700; font-size: 11px; display: inline-flex; align-items: center; gap: 5px; }
        .badge-plus { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
        .badge-minus { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
        
        .val-plus { color: #166534; font-weight: 700; font-family: monospace; font-size: 14px; }
        .val-minus { color: #991b1b; font-weight: 700; font-family: monospace; font-size: 14px; }

        /* Action Buttons */
        .btn-icon { 
            width: 34px; height: 34px; border-radius: 10px; display: inline-flex; 
            align-items: center; justify-content: center; border: none; cursor: pointer; transition: 0.2s;
        }
        .btn-delete { background: #fef2f2; color: #ef4444; border: 1px solid #fecaca; }
        .btn-delete:hover { background: #ef4444; color: #fff; transform: scale(1.05); }

        /* Search Box */
        .search-box { position: relative; width: 250px; }
        .search-box input { 
            width: 100%; padding: 10px 15px 10px 35px; border-radius: 10px; 
            border: 1px solid #e2e8f0; font-size: 13px; transition: 0.2s; box-sizing: border-box;
        }
        .search-box input:focus { border-color: var(--primary); outline: none; }
        .search-box i { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 14px; }
        
        /* Grid Layout (Bootstrap-like logic) */
        .row { display: flex; flex-wrap: wrap; margin-right: -15px; margin-left: -15px; }
        .col-lg-4 { flex: 0 0 33.333333%; max-width: 33.333333%; padding: 0 15px; box-sizing: border-box; }
        .col-lg-8 { flex: 0 0 66.666667%; max-width: 66.666667%; padding: 0 15px; box-sizing: border-box; }

        @media (max-width: 992px) {
            .col-lg-4, .col-lg-8 { flex: 0 0 100%; max-width: 100%; margin-bottom: 20px; }
            .search-box { width: 100%; margin-top: 10px; }
            .card-header { flex-direction: column; align-items: flex-start; gap: 10px; }
        }
    </style>
</head>
<body>
    <?php include '../../layout/sidebar.php'; ?>
    
    <div class="content-wrapper">
        <div class="page-header">
            <h1 class="page-title">Keuangan Lain-lain</h1>
            <p class="page-subtitle">Input bonus, THR, denda, atau transaksi manual lainnya (Otomatis Kas).</p>
        </div>

        <div class="row">
            <div class="col-lg-4">
                <div class="glass-card">
                    <div class="card-header">
                        <h5 class="card-title"><i class="fa fa-pen-to-square text-primary"></i> Input Baru</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="form-group">
                                <label class="form-label">Pilih Karyawan</label>
                                <select name="user_id" class="form-control-custom" required>
                                    <option value="">- Pilih Nama -</option>
                                    <?php 
                                    $q_u = mysqli_query($conn, "SELECT id, fullname, status_karyawan FROM users WHERE role!='admin' ORDER BY fullname ASC");
                                    while($u = mysqli_fetch_assoc($q_u)) {
                                        echo "<option value='{$u['id']}'>{$u['fullname']} ({$u['status_karyawan']})</option>";
                                    }
                                    ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Tanggal Transaksi</label>
                                <input type="date" name="tanggal" class="form-control-custom" value="<?= date('Y-m-d') ?>" required>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Jenis Transaksi</label>
                                <select name="jenis_select" id="jenis_select" class="form-control-custom" required onchange="toggleCustomInput()">
                                    <optgroup label="PENAMBAH GAJI (+)">
                                        <option value="Bonus">Bonus Kinerja</option>
                                        <option value="THR">THR / Tunjangan Hari Raya</option>
                                        <option value="Insentif">Insentif Khusus</option>
                                        <option value="Tunjangan">Tunjangan Lainnya</option>
                                        <option value="Uang Saku">Uang Saku / Dinas</option>
                                    </optgroup>
                                    <optgroup label="PENGURANG GAJI (-)">
                                        <option value="Denda">Denda Keterlambatan/Kerusakan</option>
                                        <option value="Potongan Lain">Potongan Lainnya</option>
                                        <option value="Koperasi">Potongan Koperasi</option>
                                        <option value="Ganti Rugi">Ganti Rugi Barang</option>
                                    </optgroup>
                                    <option value="Lainnya" style="font-weight:700; color:var(--primary);">+ Input Manual (Custom)</option>
                                </select>
                                
                                <div id="custom_input_box" class="custom-input-box">
                                    <div style="margin-bottom:15px;">
                                        <label class="form-label" style="color:#1e40af;">Nama Transaksi Baru</label>
                                        <input type="text" name="jenis_manual" id="jenis_manual" class="form-control-custom" style="background:#fff;" placeholder="Contoh: Bonus Project X">
                                    </div>
                                    <div>
                                        <label class="form-label" style="color:#1e40af;">Kategori Keuangan</label>
                                        <div class="radio-group">
                                            <label class="radio-item">
                                                <input type="radio" name="kategori_manual" value="Pendapatan" checked> 
                                                <span style="color:#166534;">Pendapatan (+)</span>
                                            </label>
                                            <label class="radio-item">
                                                <input type="radio" name="kategori_manual" value="Potongan"> 
                                                <span style="color:#991b1b;">Potongan (-)</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <small id="info_kategori" style="margin-top:12px; font-weight:600; color:#166534; font-size:12px; display:flex; align-items:center; gap:5px;">
                                    <i class="fa fa-circle-check"></i> Transaksi ini akan MENAMBAH Total Gaji
                                </small>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Nominal (Rp)</label>
                                <input type="text" name="nominal" class="form-control-custom input-rupiah" placeholder="0" required>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Keterangan (Opsional)</label>
                                <textarea name="keterangan" class="form-control-custom" style="height:80px; padding-top:12px; resize:none;" placeholder="Catatan tambahan..."></textarea>
                            </div>

                            <button type="submit" name="simpan" class="btn-save">
                                <i class="fa fa-floppy-disk"></i> Simpan Data
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="glass-card">
                    <div class="card-header">
                        <h5 class="card-title"><i class="fa fa-clock-rotate-left text-primary"></i> Riwayat Bulan Ini</h5>
                        <div class="search-box">
                            <i class="fa fa-search"></i>
                            <input type="text" id="searchTable" placeholder="Cari nama atau jenis...">
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-container">
                            <table class="table-custom" id="dataTable">
                                <thead>
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>Karyawan</th>
                                        <th>Jenis & Ket</th>
                                        <th style="text-align:right;">Nominal</th>
                                        <th style="text-align:center;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $bulan_ini = date('Y-m');
                                    $q_data = mysqli_query($conn, "
                                        SELECT ul.*, u.fullname 
                                        FROM uang_lainlain ul
                                        LEFT JOIN users u ON ul.user_id = u.id
                                        WHERE ul.tanggal LIKE '$bulan_ini%'
                                        ORDER BY ul.id DESC
                                    ");
                                    
                                    if(mysqli_num_rows($q_data) > 0) {
                                        while($row = mysqli_fetch_assoc($q_data)) {
                                            $is_plus = ($row['kategori'] == 'Pendapatan');
                                            $badge_cls = $is_plus ? 'badge-plus' : 'badge-minus';
                                            $val_cls   = $is_plus ? 'val-plus' : 'val-minus';
                                            $sign      = $is_plus ? '+' : '-';
                                            $icon      = $is_plus ? 'fa-arrow-up' : 'fa-arrow-down';
                                            $fmt_uang  = number_format($row['nominal'], 0, ',', '.');
                                    ?>
                                    <tr>
                                        <td style="color:#64748b; font-weight:600;"><?= date('d/m/Y', strtotime($row['tanggal'])) ?></td>
                                        <td style="font-weight:700; color:#1e293b;"><?= $row['fullname'] ?></td>
                                        <td>
                                            <span class="badge-pill <?= $badge_cls ?>"><i class="fa <?= $icon ?>"></i> <?= strtoupper($row['jenis']) ?></span>
                                            <?php if(!empty($row['keterangan'])): ?>
                                                <div style="font-size:11px; color:#64748b; margin-top:5px; font-style:italic;">"<?= $row['keterangan'] ?>"</div>
                                            <?php endif; ?>
                                        </td>
                                        <td style="text-align:right;" class="<?= $val_cls ?>">
                                            <?= $sign ?> Rp <?= $fmt_uang ?>
                                        </td>
                                        <td style="text-align:center;">
                                            <button onclick="hapusData(<?= $row['id'] ?>)" class="btn-icon btn-delete" title="Hapus Data">
                                                <i class="fa fa-trash-can"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php 
                                        }
                                    } else {
                                        echo "<tr><td colspan='5' style='text-align:center; padding:40px; color:#94a3b8;'><i class='fa fa-folder-open fa-2x' style='margin-bottom:10px; display:block;'></i>Belum ada data bulan ini.</td></tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include '../../layout/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        <?php if($swal_script) echo $swal_script; ?>

        // Format Rupiah Live
        document.querySelectorAll('.input-rupiah').forEach(i => {
            i.addEventListener('keyup', function() {
                let val = this.value.replace(/\D/g, '');
                this.value = val.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
            });
        });

        // Search Table
        document.getElementById('searchTable').addEventListener('keyup', function() {
            let filter = this.value.toLowerCase();
            document.querySelectorAll('#dataTable tbody tr').forEach(r => {
                let text = r.innerText.toLowerCase();
                r.style.display = text.includes(filter) ? '' : 'none';
            });
        });

        // Toggle Custom Input Logic
        function toggleCustomInput() {
            const select = document.getElementById('jenis_select');
            const customBox = document.getElementById('custom_input_box');
            const inputManual = document.getElementById('jenis_manual');
            const info = document.getElementById('info_kategori');

            if (select.value === 'Lainnya') {
                customBox.style.display = 'block';
                inputManual.setAttribute('required', 'required');
                info.style.display = 'none';
            } else {
                customBox.style.display = 'none';
                inputManual.removeAttribute('required');
                info.style.display = 'flex';
                
                // Update hint text & color
                const selectedOpt = select.options[select.selectedIndex];
                const groupLabel = selectedOpt.parentNode.label || "";
                
                if (groupLabel.includes('PENAMBAH')) {
                    info.innerHTML = '<i class="fa fa-circle-check"></i> Transaksi ini akan MENAMBAH Total Gaji';
                    info.style.color = '#166534';
                } else {
                    info.innerHTML = '<i class="fa fa-circle-minus"></i> Transaksi ini akan MENGURANGI Total Gaji';
                    info.style.color = '#991b1b';
                }
            }
        }

        // Fungsi Popup Hapus
        function hapusData(id) {
            Swal.fire({
                title: 'Hapus & Batalkan Kas?',
                text: "Data akan dihapus dan saldo kas akan disesuaikan kembali!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal',
                background: '#fff',
                borderRadius: '16px'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = `uang_lainlain.php?hapus_id=${id}`;
                }
            });
        }
        
        // Init state
        toggleCustomInput();
    </script>
</body>
</html>