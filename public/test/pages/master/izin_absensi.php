<?php 
require_once '../../config/database.php';
// Set Timezone
date_default_timezone_set('Asia/Jakarta');

cek_login(); 

// SECURITY: Pastikan hanya admin yang akses
if ($_SESSION['role'] !== 'admin') {
    echo "<script>window.location='../../dashboard.php';</script>";
    exit;
}

// --- 1. PROSES: TERIMA / TOLAK / HAPUS ---
if (isset($_POST['update_status'])) {
    $id_izin = $_POST['id'];
    $status_baru = $_POST['status_baru']; // 'Approved' atau 'Rejected'
    
    $q = mysqli_query($conn, "UPDATE perizinan SET status='$status_baru' WHERE id='$id_izin'");
    if($q) {
        $msg_type = "success";
        $msg_content = "Status izin berhasil diubah menjadi: $status_baru";
    }
}

if (isset($_GET['hapus'])) {
    $id_hapus = $_GET['hapus'];
    mysqli_query($conn, "DELETE FROM perizinan WHERE id='$id_hapus'");
    echo "<script>alert('Data berhasil dihapus'); window.location='izin_absensi.php';</script>";
}

// --- 2. PROSES: TAMBAH IZIN MANUAL OLEH ADMIN ---
if (isset($_POST['tambah_manual'])) {
    $user_id = $_POST['user_id'];
    $jenis = $_POST['jenis_izin'];
    $tanggal_waktu = $_POST['tanggal']; // Format dari HTML: 2026-01-26T10:29
    $ket = mysqli_real_escape_string($conn, $_POST['keterangan']);

    // Admin input statusnya langsung Approved
    // Kita ubah format T menjadi spasi agar standar MySQL DATETIME
    $tanggal_sql = str_replace('T', ' ', $tanggal_waktu);

    $sql = "INSERT INTO perizinan (user_id, jenis_izin, tanggal, keterangan, status) 
            VALUES ('$user_id', '$jenis', '$tanggal_sql', '$ket', 'Approved')";

    if(mysqli_query($conn, $sql)) {
        $msg_type = "success";
        $msg_content = "Izin berhasil ditambahkan manual.";
    } else {
        $msg_type = "error";
        $msg_content = "Gagal: " . mysqli_error($conn);
    }
}

// --- 3. AMBIL DATA (JOIN dengan Users) ---
// Filter Bulan (Berdasarkan tanggal saja, jam diabaikan untuk filter list)
$bln_ini = date('Y-m');
if(isset($_GET['bulan'])) $bln_ini = $_GET['bulan'];

$sql_data = "SELECT p.*, u.fullname, u.pin 
             FROM perizinan p 
             JOIN users u ON p.user_id = u.id 
             WHERE p.tanggal LIKE '$bln_ini%'
             ORDER BY p.tanggal DESC, p.created_at DESC";
$q_data = mysqli_query($conn, $sql_data);

// Ambil list user untuk form tambah
$q_users = mysqli_query($conn, "SELECT id, fullname FROM users ORDER BY fullname ASC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <?php include '../../layout/header.php'; ?>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        body { background-color: #f1f5f9; font-family: 'Inter', sans-serif; }
        
        .card-custom { background: #fff; border-radius: 12px; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); margin-bottom: 25px; }
        .card-header-c { padding: 15px 20px; background: #f8fafc; border-bottom: 1px solid #e2e8f0; font-weight: 700; color: #334155; display:flex; justify-content:space-between; align-items:center; }
        
        /* Table Styles */
        .table-custom { width: 100%; border-collapse: collapse; }
        .table-custom th { text-align: left; padding: 12px 15px; background: #f8fafc; color: #64748b; font-size: 11px; text-transform: uppercase; border-bottom: 1px solid #e2e8f0; font-weight: 700; }
        .table-custom td { padding: 12px 15px; border-bottom: 1px solid #f1f5f9; font-size: 13px; color: #334155; vertical-align: middle; }
        .table-custom tr:hover td { background: #f8fafc; }

        /* Badges & Buttons */
        .badge { padding: 4px 10px; border-radius: 6px; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; }
        .bg-pending { background: #fff7ed; color: #c2410c; border: 1px solid #ffedd5; }
        .bg-approved { background: #f0fdf4; color: #15803d; border: 1px solid #dcfce7; }
        .bg-rejected { background: #fef2f2; color: #b91c1c; border: 1px solid #fee2e2; }

        .btn-act { border: none; padding: 6px 12px; border-radius: 6px; font-size: 11px; font-weight: 600; cursor: pointer; transition: 0.2s; display: inline-flex; align-items: center; gap: 5px; }
        .btn-acc { background: #22c55e; color: white; } .btn-acc:hover { background: #16a34a; }
        .btn-rej { background: #ef4444; color: white; } .btn-rej:hover { background: #dc2626; }
        .btn-del { background: #fff; color: #ef4444; border: 1px solid #ef4444; } .btn-del:hover { background: #fef2f2; }

        /* Form Styles */
        .form-control-sm { padding: 8px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px; width: 100%; box-sizing: border-box; }
        .modal-wrap { display: none; position: fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:999; justify-content:center; align-items:center; }
        .modal-box { background:white; width:400px; padding:25px; border-radius:12px; box-shadow:0 10px 25px rgba(0,0,0,0.2); }
        
        /* Jam Text Style */
        .jam-text { display:block; font-size:11px; color:#2563eb; margin-top:2px; font-weight:bold; }
    </style>
</head>
<body>
    <?php include '../../layout/sidebar.php'; ?>
    
    <div class="content-wrapper" style="padding: 30px;">
        
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
            <div>
                <h2 style="font-weight:800; color:#1e293b; margin:0;">Kelola Perizinan</h2>
                <p style="color:#64748b; font-size:13px; margin-top:5px;">Approve, Reject, atau Input Izin Karyawan.</p>
            </div>
            <button onclick="document.getElementById('modalTambah').style.display='flex'" class="btn-act" style="background:#2563eb; color:white; padding:10px 20px;">
                <i class="fa fa-plus"></i> Input Manual
            </button>
        </div>

        <div class="card-custom">
            <div class="card-header-c">
                <span>Data Pengajuan (Bulan: <?= date('F Y', strtotime($bln_ini)) ?>)</span>
                <form method="GET" style="display:flex; gap:10px;">
                    <input type="month" name="bulan" value="<?= $bln_ini ?>" class="form-control-sm" style="width:auto;" onchange="this.form.submit()">
                </form>
            </div>
            
            <div style="overflow-x: auto;">
                <table class="table-custom">
                    <thead>
                        <tr>
                            <th width="140">Waktu Izin</th>
                            <th>Nama Karyawan</th>
                            <th>Jenis Izin</th>
                            <th>Keterangan</th>
                            <th width="100">Status</th>
                            <th width="180" style="text-align:center;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(mysqli_num_rows($q_data) > 0): ?>
                            <?php while($row = mysqli_fetch_assoc($q_data)): ?>
                            <tr>
                                <td style="font-family:'Courier New'; font-weight:600;">
                                    <?= date('d/m/Y', strtotime($row['tanggal'])) ?>
                                    <span class="jam-text">
                                        Pukul <?= date('H:i', strtotime($row['tanggal'])) ?>
                                    </span>
                                </td>
                                <td>
                                    <div style="font-weight:600;"><?= $row['fullname'] ?></div>
                                    <div style="font-size:10px; color:#94a3b8;">PIN: <?= $row['pin'] ?></div>
                                </td>
                                <td><?= $row['jenis_izin'] ?></td>
                                <td style="color:#64748b; font-style:italic;">"<?= $row['keterangan'] ?>"</td>
                                <td>
                                    <?php 
                                    $st = $row['status'];
                                    $cls = ($st=='Approved') ? 'bg-approved' : (($st=='Rejected') ? 'bg-rejected' : 'bg-pending');
                                    ?>
                                    <span class="badge <?= $cls ?>"><?= $st ?></span>
                                </td>
                                <td style="text-align:center;">
                                    <?php if($st == 'Pending'): ?>
                                        <form method="POST" style="display:inline-block;">
                                            <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                            <button type="submit" name="update_status" value="Approved" onclick="document.getElementsByName('status_baru')[0].value='Approved'" class="btn-act btn-acc" title="Setuju">
                                                <i class="fa fa-check"></i>
                                            </button>
                                            <button type="submit" name="update_status" value="Rejected" onclick="document.getElementsByName('status_baru')[0].value='Rejected'" class="btn-act btn-rej" title="Tolak">
                                                <i class="fa fa-times"></i>
                                            </button>
                                            <input type="hidden" name="status_baru" value="">
                                        </form>
                                    <?php else: ?>
                                        <a href="?hapus=<?= $row['id'] ?>" onclick="return confirm('Hapus data izin ini permanen?')" class="btn-act btn-del">
                                            <i class="fa fa-trash"></i>
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="6" style="text-align:center; padding:30px; color:#cbd5e1;">Tidak ada data izin bulan ini.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div id="modalTambah" class="modal-wrap">
        <div class="modal-box">
            <h3 style="margin-top:0; color:#1e293b;">Input Izin Manual</h3>
            <p style="font-size:12px; color:#64748b; margin-bottom:20px;">Masukkan tanggal DAN jam izin berlaku.</p>
            
            <form method="POST">
                <div class="form-group mb-3" style="margin-bottom:15px;">
                    <label class="form-label">Nama Karyawan</label>
                    <select name="user_id" class="form-control-sm" required>
                        <option value="">-- Pilih --</option>
                        <?php foreach($q_users as $u): ?>
                            <option value="<?= $u['id'] ?>"><?= $u['fullname'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group mb-3" style="margin-bottom:15px;">
                    <label class="form-label">Tanggal & Jam</label>
                    <input type="datetime-local" name="tanggal" class="form-control-sm" required value="<?= date('Y-m-d\TH:i') ?>">
                </div>

                <div class="form-group mb-3" style="margin-bottom:15px;">
                    <label class="form-label">Jenis Izin</label>
                    <select name="jenis_izin" class="form-control-sm" required>
                        <option value="Pulang Cepat">Pulang Cepat</option>
                        <option value="Sakit">Sakit</option>
                        <option value="Izin">Izin</option>
                        <option value="Cuti">Cuti</option>
                    </select>
                </div>
                <div class="form-group mb-3" style="margin-bottom:20px;">
                    <label class="form-label">Keterangan</label>
                    <textarea name="keterangan" class="form-control-sm" rows="2" placeholder="Cth: Sakit perut mendadak jam 10..." required></textarea>
                </div>
                <div style="display:flex; gap:10px;">
                    <button type="button" onclick="document.getElementById('modalTambah').style.display='none'" class="btn-act" style="background:#e2e8f0; color:#475569; padding:10px; flex:1; justify-content:center;">Batal</button>
                    <button type="submit" name="tambah_manual" class="btn-act" style="background:#2563eb; color:white; padding:10px; flex:1; justify-content:center;">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <?php include '../../layout/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        <?php if(isset($msg_type)): ?>
            Swal.fire({
                icon: '<?= $msg_type ?>',
                title: '<?= ($msg_type == 'success') ? 'Berhasil' : 'Gagal' ?>',
                text: '<?= $msg_content ?>',
                timer: 2000,
                showConfirmButton: false
            });
        <?php endif; ?>

        window.onclick = function(event) {
            let modal = document.getElementById('modalTambah');
            if (event.target == modal) { modal.style.display = "none"; }
        }
    </script>
</body>
</html>