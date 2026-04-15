<?php 
// 1. CONFIG & SECURITY
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '../../config/database.php';
require_once '../../config/function.php';
require_once '../../config/fingerspot_api.php'; 

session_start();
// Cek Login
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo "<script>window.location='../../login.php';</script>";
    exit;
}

$my_user_id = $_SESSION['user_id'];
$swal_script = "";

// --- 2. LOGIKA TAMBAH/HAPUS MASTER DATA (DEPARTEMEN & JABATAN) ---

// A. Tambah Departemen
if (isset($_POST['add_departemen'])) {
    $nama = mysqli_real_escape_string($conn, strtoupper($_POST['nama_dept']));
    if(mysqli_query($conn, "INSERT INTO master_departemen (nama_departemen) VALUES ('$nama')")) {
        $swal_script = "Swal.fire({icon: 'success', title: 'Berhasil', text: 'Departemen $nama ditambahkan!', timer: 1500, showConfirmButton: false});";
    }
}

// B. Hapus Departemen
if (isset($_GET['del_dept'])) {
    $id = (int)$_GET['del_dept'];
    // Cek apakah dipakai user? (Opsional, saat ini langsung hapus)
    mysqli_query($conn, "DELETE FROM master_departemen WHERE id='$id'");
    echo "<script>window.location='admin_system.php';</script>";
}

// C. Tambah Jabatan
if (isset($_POST['add_jabatan'])) {
    $nama = mysqli_real_escape_string($conn, ucwords($_POST['nama_jab']));
    if(mysqli_query($conn, "INSERT INTO master_jabatan (nama_jabatan) VALUES ('$nama')")) {
        $swal_script = "Swal.fire({icon: 'success', title: 'Berhasil', text: 'Jabatan $nama ditambahkan!', timer: 1500, showConfirmButton: false});";
    }
}

// D. Hapus Jabatan
if (isset($_GET['del_jab'])) {
    $id = (int)$_GET['del_jab'];
    mysqli_query($conn, "DELETE FROM master_jabatan WHERE id='$id'");
    echo "<script>window.location='admin_system.php';</script>";
}


// --- 3. LOGIKA UPDATE DATA KARYAWAN ---
if (isset($_POST['update_profile'])) {
    $id_user = (int)$_POST['edit_id'];
    $fullname = mysqli_real_escape_string($conn, trim($_POST['edit_fullname']));
    $dept_id  = (int)$_POST['edit_departemen'];
    $jab_id   = (int)$_POST['edit_jabatan'];
    $status   = mysqli_real_escape_string($conn, $_POST['edit_status']);
    
    $tim_id_update = NULL;
    $q_j = mysqli_query($conn, "SELECT nama_jabatan FROM master_jabatan WHERE id='$jab_id'");
    $d_j = mysqli_fetch_assoc($q_j);
    $nama_jab_baru = $d_j['nama_jabatan'];

    if ($status == 'Borongan') {
        if (stripos($nama_jab_baru, 'Leader') !== false) {
            $cek_tim = mysqli_query($conn, "SELECT id FROM master_tim WHERE leader_id='$id_user'");
            if(mysqli_num_rows($cek_tim) > 0) {
                $d_tim = mysqli_fetch_assoc($cek_tim);
                $tim_id_update = $d_tim['id'];
            } else {
                $nama_tim_baru = "Tim " . $fullname;
                mysqli_query($conn, "INSERT INTO master_tim (nama_tim, leader_id) VALUES ('$nama_tim_baru', '$id_user')");
                $tim_id_update = mysqli_insert_id($conn);
            }
        } elseif (stripos($nama_jab_baru, 'Operator') !== false) {
            $tipe_kerja = $_POST['edit_tipe_kerja'] ?? 'perorangan';
            if ($tipe_kerja == 'team') {
                $tim_id_update = !empty($_POST['edit_tim_id']) ? (int)$_POST['edit_tim_id'] : NULL;
            }
        }
    }

    $q_upd = "UPDATE users SET fullname='$fullname', departemen_id='$dept_id', jabatan_id='$jab_id', status_karyawan='$status', tim_id = " . ($tim_id_update ? "'$tim_id_update'" : "NULL") . " WHERE id='$id_user'";

    if (mysqli_query($conn, $q_upd)) {
        $swal_script = "Swal.fire({icon: 'success', title: 'Updated', text: 'Data karyawan diperbarui!', timer: 1500, showConfirmButton: false});";
    }
}

// --- 4. LOGIKA ACTION BUTTONS LAINNYA ---
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = (int)$_GET['id']; $action = $_GET['action'];
    if ($id != $my_user_id) {
        if ($action === 'make_admin') { $r='admin'; $p='2'; } elseif ($action === 'make_user') { $r='user'; $p='1'; }
        if (isset($r)) {
            mysqli_query($conn, "UPDATE users SET role='$r', privilege='$p' WHERE id='$id'");
            echo "<script>window.location='admin_system.php';</script>";
        }
    }
}

if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    if ($id != $my_user_id) {
        $u = mysqli_fetch_assoc(mysqli_query($conn, "SELECT pin FROM users WHERE id='$id'"));
        if ($u) {
            if (!empty($u['pin']) && function_exists('fingerspot_delete_user')) fingerspot_delete_user($u['pin']);
            $cek_tim = mysqli_query($conn, "SELECT id FROM master_tim WHERE leader_id='$id'");
            if(mysqli_num_rows($cek_tim) > 0){
                $dt = mysqli_fetch_assoc($cek_tim);
                mysqli_query($conn, "UPDATE users SET tim_id=NULL WHERE tim_id='{$dt['id']}'");
                mysqli_query($conn, "DELETE FROM master_tim WHERE id='{$dt['id']}'");
            }
            mysqli_query($conn, "DELETE FROM users WHERE id='$id'");
            echo "<script>window.location='admin_system.php';</script>";
        }
    }
}

// --- DATA MASTER ---
$master_dept = []; $q_d = mysqli_query($conn, "SELECT * FROM master_departemen"); while($r=mysqli_fetch_assoc($q_d)) $master_dept[]=$r;
$master_jab  = []; $q_j = mysqli_query($conn, "SELECT * FROM master_jabatan"); while($r=mysqli_fetch_assoc($q_j)) $master_jab[]=$r;
$master_tim  = []; $q_t = mysqli_query($conn, "SELECT * FROM master_tim"); while($r=mysqli_fetch_assoc($q_t)) $master_tim[]=$r;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <?php include '../../layout/header.php'; ?>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root { --primary: #4f46e5; --bg-body: #f1f5f9; --text-main: #334155; --sidebar-width: 250px; }
        body { background-color: var(--bg-body); font-family: 'Inter', sans-serif; color: var(--text-main); margin: 0; }
        .content-wrapper { padding: 30px; margin-left: var(--sidebar-width); transition: margin-left 0.3s ease; min-height: 100vh; }
        @media (max-width: 768px) { .content-wrapper { margin-left: 0; padding: 20px 15px; padding-bottom: 80px; } }

        /* General Styles */
        .modern-card { background: #fff; border-radius: 16px; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); overflow: hidden; margin-bottom: 25px; }
        .card-header-gradient { background: linear-gradient(135deg, #1e293b 0%, #334155 100%); padding: 20px 25px; color: #fff; display: flex; justify-content: space-between; align-items: center; }
        .card-header-white { padding: 15px 25px; border-bottom: 1px solid #f1f5f9; font-weight: 700; color: #1e293b; display: flex; justify-content: space-between; align-items: center; }
        .card-body { padding: 25px; }

        /* Lists */
        .list-group-custom { list-style: none; padding: 0; margin: 0; }
        .list-group-item { display: flex; justify-content: space-between; align-items: center; padding: 10px 15px; border-bottom: 1px solid #f1f5f9; font-size: 13px; transition: 0.2s; }
        .list-group-item:last-child { border-bottom: none; }
        .list-group-item:hover { background: #f8fafc; }
        
        .btn-mini { padding: 4px 8px; border-radius: 6px; font-size: 10px; cursor: pointer; border: none; }
        .btn-mini-del { background: #fee2e2; color: #b91c1c; }
        .btn-mini-add { background: #dbeafe; color: #1e40af; width: 100%; padding: 10px; font-weight: 600; margin-top: 10px; }

        /* Search & Table */
        .search-box input { padding: 8px 15px 8px 35px; border-radius: 20px; border: 1px solid rgba(255,255,255,0.2); background: rgba(255,255,255,0.1); color: #fff; width: 250px; font-size: 13px; }
        .search-box i { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: rgba(255,255,255,0.7); }
        
        .table-custom th { background: #f8fafc; font-size: 12px; text-transform: uppercase; padding: 15px; border-bottom: 1px solid #e2e8f0; color: #64748b; }
        .table-custom td { padding: 15px; border-bottom: 1px solid #f1f5f9; font-size: 13px; vertical-align: middle; }
        
        .badge-role { padding: 5px 10px; border-radius: 20px; font-size: 10px; font-weight: 700; text-transform: uppercase; }
        .bg-admin { background: #fee2e2; color: #b91c1c; } .bg-user { background: #dcfce7; color: #15803d; }
        
        .btn-icon { width: 32px; height: 32px; border-radius: 8px; display: inline-flex; align-items: center; justify-content: center; border: 1px solid #e2e8f0; background: #fff; cursor: pointer; transition: 0.2s; }
        .btn-edit { color: #3b82f6; } .btn-edit:hover { background: #eff6ff; border-color: #3b82f6; }
        .btn-delete { color: #ef4444; } .btn-delete:hover { background: #fef2f2; border-color: #ef4444; }
        
        .swal-label { display: block; text-align: left; font-size: 12px; font-weight: 600; color: #64748b; margin-bottom: 5px; }
        .swal-input { width: 100%; padding: 10px; border: 1px solid #e2e8f0; border-radius: 8px; margin-bottom: 15px; box-sizing: border-box; }
    </style>
</head>
<body>
    <?php include '../../layout/sidebar.php'; ?>

    <div class="content-wrapper">
        <div style="margin-bottom: 20px;">
            <h3 style="margin:0; font-weight:800; color:#1e293b;">System & Master Data</h3>
            <p style="margin:5px 0 0; color:#64748b; font-size:14px;">Kelola departemen, jabatan, dan akses pengguna.</p>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="modern-card">
                    <div class="card-header-white">
                        <span><i class="fa fa-building text-primary"></i> Master Departemen</span>
                    </div>
                    <div class="card-body p-0">
                        <ul class="list-group-custom" style="max-height: 200px; overflow-y: auto;">
                            <?php foreach($master_dept as $d): ?>
                            <li class="list-group-item">
                                <b><?= $d['nama_departemen'] ?></b>
                                <a href="admin_system.php?del_dept=<?= $d['id'] ?>" onclick="return confirm('Hapus departemen ini?')" class="btn-mini btn-mini-del"><i class="fa fa-trash"></i></a>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                        <div style="padding: 10px;">
                            <button onclick="addDept()" class="btn-mini btn-mini-add"><i class="fa fa-plus"></i> Tambah Departemen Baru</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="modern-card">
                    <div class="card-header-white">
                        <span><i class="fa fa-briefcase text-success"></i> Master Jabatan</span>
                    </div>
                    <div class="card-body p-0">
                        <ul class="list-group-custom" style="max-height: 200px; overflow-y: auto;">
                            <?php foreach($master_jab as $j): ?>
                            <li class="list-group-item">
                                <b><?= $j['nama_jabatan'] ?></b>
                                <a href="admin_system.php?del_jab=<?= $j['id'] ?>" onclick="return confirm('Hapus jabatan ini?')" class="btn-mini btn-mini-del"><i class="fa fa-trash"></i></a>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                        <div style="padding: 10px;">
                            <button onclick="addJab()" class="btn-mini btn-mini-add" style="background: #dcfce7; color: #166534;"><i class="fa fa-plus"></i> Tambah Jabatan Baru</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modern-card">
            <div class="card-header-gradient">
                <h4 style="margin:0;"><i class="fa fa-users-cog"></i> Hak Akses & Profil</h4>
                <div class="search-box">
                    <i class="fa fa-search"></i>
                    <input type="text" id="searchInput" placeholder="Cari user...">
                </div>
            </div>
            
            <div class="table-responsive">
                <table class="table-custom" id="userTable" style="width:100%;">
                    <thead>
                        <tr>
                            <th>User Profile</th>
                            <th>Detail Pekerjaan</th>
                            <th>Role System</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $q = mysqli_query($conn, "
                            SELECT u.*, mj.nama_jabatan, md.nama_departemen, mt.nama_tim 
                            FROM users u 
                            LEFT JOIN master_jabatan mj ON u.jabatan_id = mj.id
                            LEFT JOIN master_departemen md ON u.departemen_id = md.id
                            LEFT JOIN master_tim mt ON u.tim_id = mt.id
                            ORDER BY u.role ASC, u.fullname ASC
                        ");
                        while ($u = mysqli_fetch_assoc($q)) {
                            $is_me = ($u['id'] == $my_user_id);
                            $badge_cls = ($u['role'] === 'admin') ? 'bg-admin' : 'bg-user';
                            $json = htmlspecialchars(json_encode($u), ENT_QUOTES, 'UTF-8');
                        ?>
                        <tr>
                            <td>
                                <div style="font-weight:700; color:#1e293b;"><?= htmlspecialchars($u['fullname']) ?></div>
                                <div style="font-size:11px; color:#64748b;">@<?= $u['username'] ?></div>
                            </td>
                            <td>
                                <div style="font-weight:600;"><?= $u['nama_jabatan'] ?? '-' ?> <span style="font-weight:400; color:#94a3b8;">(<?= $u['nama_departemen'] ?? '-' ?>)</span></div>
                                <div style="font-size:11px; color:#64748b;"><?= $u['status_karyawan'] ?> <?= $u['tim_id'] ? "&bull; " . $u['nama_tim'] : '' ?></div>
                            </td>
                            <td>
                                <span class="badge-role <?= $badge_cls ?>"><?= strtoupper($u['role']) ?></span>
                                <?php if (!$is_me): ?>
                                    <a href="admin_system.php?action=<?= $u['role']=='user'?'make_admin':'make_user' ?>&id=<?= $u['id'] ?>" class="btn-mini" style="background:#f1f5f9; color:#334155; text-decoration:none; margin-left:5px;">Ubah</a>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <button onclick="openEditModal(<?= $json ?>)" class="btn-icon btn-edit" title="Edit Profil"><i class="fa fa-pencil"></i></button>
                                <?php if (!$is_me): ?>
                                <a href="admin_system.php?hapus=<?= $u['id'] ?>" onclick="return confirm('Hapus permanen?')" class="btn-icon btn-delete" title="Hapus User"><i class="fa fa-trash"></i></a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <form id="formAddDept" method="POST"><input type="hidden" name="add_departemen" value="1"><input type="hidden" name="nama_dept" id="inp_dept"></form>
    <form id="formAddJab" method="POST"><input type="hidden" name="add_jabatan" value="1"><input type="hidden" name="nama_jab" id="inp_jab"></form>

    <?php include '../../layout/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        <?php if(!empty($swal_script)) echo $swal_script; ?>

        // 1. TAMBAH DEPARTEMEN
        function addDept() {
            Swal.fire({
                title: 'Tambah Departemen',
                input: 'text',
                inputPlaceholder: 'Contoh: Produksi, Gudang, HRD',
                showCancelButton: true,
                confirmButtonText: 'Simpan'
            }).then((res) => {
                if(res.value) {
                    document.getElementById('inp_dept').value = res.value;
                    document.getElementById('formAddDept').submit();
                }
            });
        }

        // 2. TAMBAH JABATAN
        function addJab() {
            Swal.fire({
                title: 'Tambah Jabatan',
                input: 'text',
                inputPlaceholder: 'Contoh: Operator, Helper, Manager',
                showCancelButton: true,
                confirmButtonText: 'Simpan'
            }).then((res) => {
                if(res.value) {
                    document.getElementById('inp_jab').value = res.value;
                    document.getElementById('formAddJab').submit();
                }
            });
        }

        // 3. EDIT PROFILE MODAL
        const masterDept = <?php echo json_encode($master_dept); ?>;
        const masterJab = <?php echo json_encode($master_jab); ?>;
        const masterTim = <?php echo json_encode($master_tim); ?>;

        function openEditModal(user) {
            let optDept = '<option value="">- Pilih -</option>';
            masterDept.forEach(d => { optDept += `<option value="${d.id}" ${d.id==user.departemen_id?'selected':''}>${d.nama_departemen}</option>`; });

            let optJab = '<option value="">- Pilih -</option>';
            masterJab.forEach(j => { optJab += `<option value="${j.id}" data-nama="${j.nama_jabatan}" ${j.id==user.jabatan_id?'selected':''}>${j.nama_jabatan}</option>`; });

            let optStatus = `
                <option value="Tetap" ${user.status_karyawan=='Tetap'?'selected':''}>Tetap</option>
                <option value="Borongan" ${user.status_karyawan=='Borongan'?'selected':''}>Borongan</option>
            `;

            let optTim = '<option value="">- Pilih Tim -</option>';
            masterTim.forEach(t => { optTim += `<option value="${t.id}" ${t.id==user.tim_id?'selected':''}>${t.nama_tim}</option>`; });

            Swal.fire({
                title: 'Edit Data Karyawan',
                html: `
                    <form id="editForm">
                        <input type="hidden" name="update_profile" value="1">
                        <input type="hidden" name="edit_id" value="${user.id}">
                        <div class="swal-form-group">
                            <label class="swal-label">Nama Lengkap</label>
                            <input type="text" name="edit_fullname" class="swal-input" value="${user.fullname}" required>
                        </div>
                        <div class="swal-form-group">
                            <label class="swal-label">Departemen</label>
                            <select name="edit_departemen" id="edit_dept" class="swal-input" required>${optDept}</select>
                        </div>
                        <div class="swal-form-group">
                            <label class="swal-label">Jabatan</label>
                            <select name="edit_jabatan" id="edit_jab" class="swal-input" required onchange="checkEditLogic()">${optJab}</select>
                        </div>
                        <div class="swal-form-group">
                            <label class="swal-label">Status</label>
                            <select name="edit_status" id="edit_status" class="swal-input" required onchange="checkEditLogic()">${optStatus}</select>
                        </div>
                        <div id="box_edit_produksi" style="display:none; text-align:left; background:#eff6ff; padding:10px; border-radius:8px;">
                             <label class="swal-label text-primary">Tipe Kerja Borongan</label>
                             <select name="edit_tipe_kerja" id="edit_tipe_kerja" class="swal-input" onchange="toggleEditTim()">
                                <option value="perorangan">Perorangan</option>
                                <option value="team" ${user.tim_id ? 'selected' : ''}>Ikut Team</option>
                             </select>
                             <div id="box_edit_tim" style="display:none;">
                                <label class="swal-label text-primary">Pilih Tim</label>
                                <select name="edit_tim_id" class="swal-input">${optTim}</select>
                            </div>
                        </div>
                    </form>
                `,
                showCancelButton: true,
                confirmButtonText: 'Simpan',
                didOpen: () => { checkEditLogic(); }
            }).then((r) => {
                if(r.isConfirmed) {
                    const form = document.getElementById('editForm');
                    const formData = new FormData(form);
                    fetch('admin_system.php', { method: 'POST', body: formData }).then(() => location.reload());
                }
            });
        }

        window.checkEditLogic = function() {
            const stat = document.getElementById('edit_status').value;
            const jabSel = document.getElementById('edit_jab');
            const jabName = jabSel.options[jabSel.selectedIndex].getAttribute('data-nama') || '';
            const boxProd = document.getElementById('box_edit_produksi');

            if (stat === 'Borongan' && jabName.includes('Operator')) {
                boxProd.style.display = 'block';
                toggleEditTim();
            } else {
                boxProd.style.display = 'none';
            }
        }

        window.toggleEditTim = function() {
            const tipe = document.getElementById('edit_tipe_kerja').value;
            document.getElementById('box_edit_tim').style.display = (tipe === 'team') ? 'block' : 'none';
        }

        // Search
        document.getElementById('searchInput').addEventListener('keyup', function() {
            let filter = this.value.toLowerCase();
            document.querySelectorAll('#userTable tbody tr').forEach(row => {
                let text = row.innerText.toLowerCase();
                row.style.display = text.includes(filter) ? '' : 'none';
            });
        });
    </script>
</body>
</html>