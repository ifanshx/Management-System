<?php 
require_once '../../config/database.php';
cek_login(); 

if ($_SESSION['role'] !== 'admin') {
    echo "<script>window.location='../dashboard.php';</script>";
    exit;
}

$swal_script = "";

// 1. Tambah Tarif Baru
if (isset($_POST['tambah_pekerjaan'])) {
    $jenis  = trim(mysqli_real_escape_string($conn, $_POST['jenis_baru']));
    $motor  = trim(mysqli_real_escape_string($conn, $_POST['motor_baru']));
    $kat    = mysqli_real_escape_string($conn, $_POST['kategori_baru']);
    $harga  = str_replace('.', '', $_POST['harga_baru']);
    
    // LOGIKA PENENTUAN ID (Leader atau Pekerja Lepas)
    if ($kat == 'Team') {
        $assigned_id = !empty($_POST['leader_team']) ? (int)$_POST['leader_team'] : 'NULL';
    } else {
        $assigned_id = !empty($_POST['pekerja_lepas']) ? (int)$_POST['pekerja_lepas'] : 'NULL';
    }
    
    $val_id = ($assigned_id === 'NULL') ? "NULL" : "'$assigned_id'";

    if(!empty($jenis) && !empty($motor)) {
        // UPDATE NAMA TABEL: master_pekerjaan -> master_pekerjaan_borongan
        $q_ins = "INSERT INTO master_pekerjaan_borongan (jenis_pekerjaan, nama_motor, kategori, leader_id, harga) 
                  VALUES ('$jenis', '$motor', '$kat', $val_id, '$harga')";
        
        if (mysqli_query($conn, $q_ins)) {
            $swal_script = "Swal.fire({icon: 'success', title: 'Berhasil', text: 'Tarif baru ditambahkan!', timer: 1500, showConfirmButton: false});";
        } else {
            if(mysqli_errno($conn) == 1062){
                $swal_script = "Swal.fire({icon: 'error', title: 'Duplikat', text: 'Data pekerjaan ini sudah ada.'});";
            } else {
                $swal_script = "Swal.fire({icon: 'error', title: 'Error', text: 'Gagal menyimpan data.'});";
            }
        }
    }
}

// 2. Update Massal Tarif
if (isset($_POST['update_harga_borongan'])) {
    foreach ($_POST['harga_pekerjaan'] as $id => $nilai) {
        $nilai_fix = str_replace('.', '', $nilai);
        // UPDATE NAMA TABEL
        mysqli_query($conn, "UPDATE master_pekerjaan_borongan SET harga='$nilai_fix' WHERE id='$id'");
    }
    $swal_script = "Swal.fire({icon: 'success', title: 'Tersimpan', text: 'Semua harga berhasil diperbarui!', timer: 1500, showConfirmButton: false});";
}

// 3. Hapus Tarif
if (isset($_GET['hapus_job'])) {
    $id = (int)$_GET['hapus_job'];
    
    // UPDATE NAMA TABEL: produksi_borongan -> hasil_produksi_borongan
    $cek_pakai = mysqli_query($conn, "SELECT id FROM hasil_produksi_borongan WHERE pekerjaan_id='$id' LIMIT 1");
    
    if(mysqli_num_rows($cek_pakai) > 0){
        $swal_script = "Swal.fire({icon: 'warning', title: 'Ditolak', text: 'Tarif sedang digunakan dalam riwayat produksi.'});";
    } else {
        // UPDATE NAMA TABEL
        mysqli_query($conn, "DELETE FROM master_pekerjaan_borongan WHERE id='$id'");
        $swal_script = "Swal.fire({icon: 'success', title: 'Terhapus', text: 'Item berhasil dihapus.', timer: 1000, showConfirmButton: false}).then(() => { window.location='setting_tarif_borongan.php'; });";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <?php include '../../layout/header.php'; ?>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root { 
            --primary: #10b981; 
            --primary-dark: #059669;
            --bg-body: #f1f5f9; 
            --text-dark: #1e293b;
            --text-muted: #64748b;
            --border-color: #e2e8f0;
            --card-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
        }
        
        body { background-color: var(--bg-body); font-family: 'Inter', sans-serif; color: var(--text-dark); }
        
        .content-wrapper { 
            padding: 30px; 
            margin-left: 260px; 
            transition: margin-left 0.3s ease;
        }
        
        @media (max-width: 768px) {
            .content-wrapper { margin-left: 0; padding: 20px; padding-bottom: 100px; }
        }

        .page-header { margin-bottom: 25px; display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 15px; }
        .page-title h2 { margin: 0; font-weight: 700; color: var(--text-dark); font-size: 24px; letter-spacing: -0.5px; }
        .page-title p { margin: 5px 0 0; color: var(--text-muted); font-size: 14px; }

        .modern-card {
            background: #fff;
            border-radius: 16px;
            box-shadow: var(--card-shadow);
            overflow: hidden;
            border: 1px solid var(--border-color);
            margin-bottom: 25px;
        }
        
        .card-header-clean {
            padding: 20px 25px;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #fff;
        }
        
        .card-header-clean h5 { margin: 0; font-size: 16px; font-weight: 600; color: var(--text-dark); display: flex; align-items: center; gap: 10px; }

        .form-grid {
            display: grid;
            grid-template-columns: 2fr 1.5fr 1fr 1.5fr 1.5fr auto;
            gap: 15px;
            align-items: end;
            padding: 25px;
            background: #f8fafc;
            border-bottom: 1px solid var(--border-color);
        }

        .form-group label { display: block; font-size: 12px; font-weight: 600; color: var(--text-muted); margin-bottom: 6px; text-transform: uppercase; }
        .input-modern { width: 100%; border: 1px solid var(--border-color); border-radius: 8px; padding: 10px 15px; font-size: 13px; background: #fff; transition: all 0.2s; height: 42px; }
        .input-modern:focus { border-color: var(--primary); outline: none; box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1); }

        .table-responsive { overflow-x: auto; -webkit-overflow-scrolling: touch; }
        .table-custom { width: 100%; border-collapse: separate; border-spacing: 0; }
        .table-custom th {
            background: #fff;
            color: #475569;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 16px 24px;
            border-bottom: 2px solid var(--border-color);
            white-space: nowrap;
        }
        .table-custom td {
            padding: 16px 24px;
            vertical-align: middle;
            border-bottom: 1px solid var(--border-color);
            background: #fff;
            font-size: 13px;
        }
        .table-custom tr:hover td { background: #f8fafc; }

        .badge-pill { padding: 4px 10px; border-radius: 6px; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; display: inline-block; }
        .badge-team { background: #ffedd5; color: #c2410c; border: 1px solid #fed7aa; }
        .badge-pero { background: #e0e7ff; color: #4338ca; border: 1px solid #c7d2fe; }

        .btn-add { background: var(--primary); color: #fff; border: none; width: 42px; height: 42px; border-radius: 8px; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: 0.2s; box-shadow: 0 4px 6px rgba(16, 185, 129, 0.2); }
        .btn-add:hover { background: var(--primary-dark); transform: translateY(-2px); }

        .btn-icon-soft { width: 32px; height: 32px; border-radius: 8px; display: inline-flex; align-items: center; justify-content: center; background: #fee2e2; color: #ef4444; transition: 0.2s; }
        .btn-icon-soft:hover { background: #fecaca; }

        .input-currency-table { border: 1px solid transparent; background: transparent; text-align: right; font-weight: 600; color: var(--text-dark); width: 100%; padding: 5px; border-radius: 5px; transition: 0.2s; }
        .input-currency-table:focus { background: #fff; border-color: var(--primary); outline: none; }

        .card-footer-action {
            background: #fff;
            padding: 15px 25px;
            border-top: 1px solid var(--border-color);
            display: flex;
            justify-content: flex-end;
            position: sticky;
            bottom: 0;
            z-index: 10;
            box-shadow: 0 -4px 10px rgba(0,0,0,0.03);
        }
        .btn-save-all {
            background: var(--primary); color: #fff; border: none; padding: 12px 25px; border-radius: 8px; font-weight: 600; font-size: 13px; cursor: pointer; display: flex; align-items: center; gap: 8px; box-shadow: 0 4px 6px rgba(16, 185, 129, 0.2);
        }
        .btn-save-all:hover { background: var(--primary-dark); }

        @media (max-width: 992px) {
            .form-grid { grid-template-columns: 1fr 1fr; }
            .col-full-mobile { grid-column: span 2; }
            .btn-add-wrapper { grid-column: span 2; display: flex; justify-content: flex-end; }
        }
        @media (max-width: 576px) {
            .form-grid { display: flex; flex-direction: column; align-items: stretch; gap: 15px; }
            .card-header-clean { flex-direction: column; gap: 15px; align-items: stretch; }
            .search-box { width: 100%; }
        }
    </style>
</head>
<body>
    <?php include '../../layout/sidebar.php'; ?>
    
    <div class="content-wrapper">
        <div class="page-header">
            <div class="page-title">
                <h2>Master Tarif Borongan</h2>
                <p>Kelola jenis pekerjaan, harga, dan penanggung jawab produksi.</p>
            </div>
        </div>

        <div class="modern-card">
            <div class="card-header-clean">
                <h5><i class="fa fa-plus-circle text-success"></i> Tambah Tarif Baru</h5>
            </div>
            
            <form method="POST">
                <div class="form-grid">
                    <div class="form-group col-full-mobile">
                        <label>Jenis Pekerjaan</label>
                        <input type="text" name="jenis_baru" class="input-modern" placeholder="Contoh: LAS CACING" required style="text-transform:uppercase;">
                    </div>
                    
                    <div class="form-group">
                        <label>Tipe Motor</label>
                        <input type="text" name="motor_baru" class="input-modern" placeholder="Contoh: TIGER" required style="text-transform:uppercase;">
                    </div>

                    <div class="form-group">
                        <label>Kategori</label>
                        <select name="kategori_baru" id="kategoriSelect" class="input-modern" onchange="toggleForm()">
                            <option value="Perorangan">Perorangan</option>
                            <option value="Team">Team</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Penanggung Jawab</label>
                        
                        <div id="divLeader" style="display:none;">
                            <select name="leader_team" id="inputLeader" class="input-modern">
                                <option value="">- Pilih Ketua Tim -</option>
                                <?php 
                                $q_lead = mysqli_query($conn, "SELECT u.id, u.fullname FROM users u JOIN master_jabatan mj ON u.jabatan_id = mj.id WHERE mj.nama_jabatan = 'Leader' ORDER BY u.fullname ASC");
                                while($l = mysqli_fetch_assoc($q_lead)){ echo '<option value="'.$l['id'].'">'.$l['fullname'].'</option>'; }
                                ?>
                            </select>
                        </div>

                        <div id="divPekerja">
                            <select name="pekerja_lepas" id="inputPekerja" class="input-modern">
                                <option value="">- Pilih Pekerja -</option>
                                <?php 
                                $q_users = mysqli_query($conn, "SELECT id, fullname FROM users WHERE status_karyawan = 'Borongan' AND (tim_id IS NULL OR tim_id = 0) ORDER BY fullname ASC");
                                while($u = mysqli_fetch_assoc($q_users)){ echo '<option value="'.$u['id'].'">'.$u['fullname'].'</option>'; }
                                ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Harga Upah</label>
                        <div style="position: relative;">
                            <span style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #94a3b8; font-weight: 600; font-size: 12px;">Rp</span>
                            <input type="text" name="harga_baru" class="input-modern input-rupiah" style="padding-left: 35px; font-weight: 600; color: var(--primary);" placeholder="0" required>
                        </div>
                    </div>

                    <div class="btn-add-wrapper">
                        <button type="submit" name="tambah_pekerjaan" class="btn-add" title="Tambah Data">
                            <i class="fa fa-arrow-right"></i>
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <div class="modern-card">
            <div class="card-header-clean">
                <h5><i class="fa fa-list-ul text-primary"></i> Daftar Tarif Aktif</h5>
                <div class="search-box" style="position:relative; width: 250px;">
                    <i class="fa fa-search" style="position:absolute; left:12px; top:50%; transform:translateY(-50%); color:#94a3b8;"></i>
                    <input type="text" id="cariTarif" class="input-modern" style="padding-left:35px;" placeholder="Cari data...">
                </div>
            </div>
            
            <form method="POST">
                <div class="table-responsive" style="max-height: 600px;">
                    <table class="table-custom" id="tabelTarif">
                        <thead>
                            <tr>
                                <th width="30%">Jenis Pekerjaan</th>
                                <th width="20%">Motor</th>
                                <th width="20%">Penanggung Jawab</th>
                                <th width="10%">Kategori</th>
                                <th width="15%" class="text-right">Harga (Rp)</th>
                                <th width="5%" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            // UPDATE QUERY NAMA TABEL
                            $q_job = mysqli_query($conn, "
                                SELECT mp.*, u.fullname AS nama_leader 
                                FROM master_pekerjaan_borongan mp 
                                LEFT JOIN users u ON mp.leader_id = u.id 
                                ORDER BY mp.jenis_pekerjaan ASC, mp.nama_motor ASC
                            ");

                            if(mysqli_num_rows($q_job) > 0) {
                                while ($job = mysqli_fetch_assoc($q_job)):
                                    $badge_html = ($job['kategori'] == 'Team') 
                                        ? '<span class="badge-pill badge-team">TEAM</span>' 
                                        : '<span class="badge-pill badge-pero">MANDIRI</span>';
                                ?>
                                <tr>
                                    <td>
                                        <div style="font-weight:600; color:#1e293b;"><?php echo $job['jenis_pekerjaan']; ?></div>
                                    </td>
                                    <td>
                                        <span style="color:#64748b; font-weight:500;"><?php echo $job['nama_motor']; ?></span>
                                    </td>
                                    <td>
                                        <?php if($job['nama_leader']): ?>
                                            <div style="display:flex; align-items:center; gap:8px;">
                                                <div style="width:24px; height:24px; background:#f1f5f9; border-radius:50%; display:flex; align-items:center; justify-content:center; color:#64748b; font-size:10px;">
                                                    <i class="fa fa-user"></i>
                                                </div>
                                                <span style="font-weight:600; font-size:12px; color:#334155;"><?php echo $job['nama_leader']; ?></span>
                                            </div>
                                        <?php else: ?>
                                            <span style="color:#cbd5e1; font-style:italic; font-size:12px;">- Umum -</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo $badge_html; ?></td>
                                    <td>
                                        <input type="text" name="harga_pekerjaan[<?php echo $job['id']; ?>]" class="input-currency-table input-rupiah" value="<?php echo number_format($job['harga'], 0, ',', '.'); ?>">
                                    </td>
                                    <td class="text-center">
                                        <a href="setting_tarif_borongan.php?hapus_job=<?php echo $job['id']; ?>" onclick="return confirm('Yakin ingin menghapus item ini?')" class="btn-icon-soft">
                                            <i class="fa fa-trash-can"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endwhile; 
                            } else { 
                                echo '<tr><td colspan="6" class="text-center" style="padding:40px; color:#94a3b8;">Belum ada data tarif. Tambahkan di form atas.</td></tr>'; 
                            }?>
                        </tbody>
                    </table>
                </div>

                <div class="card-footer-action">
                    <button type="submit" name="update_harga_borongan" class="btn-save-all">
                        <i class="fa fa-check-circle"></i> Simpan Perubahan Harga
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <?php include '../../layout/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        <?php if (!empty($swal_script)) echo $swal_script; ?>
        
        // --- LOGIKA TOGGLE FORM (Team vs Perorangan) ---
        function toggleForm() {
            var kategori = document.getElementById("kategoriSelect").value;
            var divLeader = document.getElementById("divLeader");
            var inputLeader = document.getElementById("inputLeader");
            var divPekerja = document.getElementById("divPekerja");
            var inputPekerja = document.getElementById("inputPekerja");

            if (kategori === "Team") {
                divLeader.style.display = "block";
                inputLeader.setAttribute("required", "required");
                divPekerja.style.display = "none";
                inputPekerja.removeAttribute("required");
                inputPekerja.value = ""; 
            } else {
                divPekerja.style.display = "block";
                inputPekerja.setAttribute("required", "required");
                divLeader.style.display = "none";
                inputLeader.removeAttribute("required");
                inputLeader.value = ""; 
            }
        }

        document.addEventListener("DOMContentLoaded", function() { toggleForm(); });

        // --- SEARCH REALTIME ---
        document.getElementById('cariTarif').addEventListener('keyup', function() {
            let filter = this.value.toLowerCase();
            document.querySelectorAll('#tabelTarif tbody tr').forEach(row => {
                let text = row.textContent.toLowerCase();
                row.style.display = text.includes(filter) ? '' : 'none';
            });
        });
        
        // --- FORMAT RUPIAH ---
        document.querySelectorAll('.input-rupiah').forEach(input => {
            input.addEventListener('keyup', function(e) {
                let val = this.value.replace(/[^,\d]/g, '').toString();
                let split = val.split(',');
                let sisa = split[0].length % 3;
                let rupiah = split[0].substr(0, sisa);
                let ribuan = split[0].substr(sisa).match(/\d{3}/gi);
                
                if (ribuan) {
                    let separator = sisa ? '.' : '';
                    rupiah += separator + ribuan.join('.');
                }
                this.value = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
            });
        });
    </script>
</body>
</html>