<?php 
require_once '../../config/database.php';
require_once '../../config/function.php'; 
require_once '../../config/fingerspot_api.php'; 

date_default_timezone_set('Asia/Jakarta');
cek_login(); 

if ($_SESSION['role'] !== 'admin') {
    header("Location: ../dashboard.php");
    exit;
}

$swal_script = "";

// --- 1. AUTO PIN ---
$q_max = mysqli_query($conn, "SELECT MAX(CAST(pin AS UNSIGNED)) as max_pin FROM users");
$d_max = mysqli_fetch_assoc($q_max);
$next_pin = ($d_max['max_pin'] && $d_max['max_pin'] >= 1000) ? $d_max['max_pin'] + 1 : 1001;

// --- 2. EDIT PASSWORD ---
if (isset($_POST['update_password'])) {
    $uid = (int)$_POST['user_id'];
    $new_pass = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
    $stmt = mysqli_prepare($conn, "UPDATE users SET password = ? WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "si", $new_pass, $uid);
    if (mysqli_stmt_execute($stmt)) {
        $swal_script = "Swal.fire({icon: 'success', title: 'Berhasil', text: 'Password diperbarui!', timer: 1500, showConfirmButton: false});";
    }
}

// --- 3. SIMPAN KARYAWAN ---
if (isset($_POST['simpan_karyawan'])) {
    verify_csrf_token($_POST['csrf_token']);

    $fullname = mysqli_real_escape_string($conn, trim($_POST['fullname']));
    $username = mysqli_real_escape_string($conn, trim($_POST['username']));
    $pin      = mysqli_real_escape_string($conn, trim($_POST['pin']));
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $no_hp    = !empty($_POST['no_hp']) ? mysqli_real_escape_string($conn, $_POST['no_hp']) : NULL;
    $tgl_masuk = !empty($_POST['tgl_masuk']) ? $_POST['tgl_masuk'] : date('Y-m-d');
    
    $jabatan_id    = (int)$_POST['jabatan_id'];
    $departemen_id = (int)$_POST['departemen_id'];
    $status_karyawan = mysqli_real_escape_string($conn, $_POST['status_karyawan_input']); 

    // Ambil Info Jabatan
    $d_jab = mysqli_fetch_assoc(mysqli_query($conn, "SELECT nama_jabatan FROM master_jabatan WHERE id='$jabatan_id'"));
    $nama_jab = $d_jab['nama_jabatan']; 
    
    // Role System
    $role = 'user';
    if(stripos($nama_jab, 'Admin') !== false || stripos($nama_jab, 'Owner') !== false) $role = 'admin';
    if(stripos($nama_jab, 'Kepala') !== false) $role = 'kepala_bengkel';

    // --- LOGIKA TIM ---
    $tim_id = NULL;

    if ($status_karyawan == 'Borongan') {
        if (stripos($nama_jab, 'Leader') !== false) {
            // Leader: Nanti dibuatkan tim baru
        } else {
            // Selain Leader (Operator/Helper/dll), cek pilihan user
            $tipe_kerja = $_POST['tipe_kerja_produksi'] ?? 'perorangan'; 
            if ($tipe_kerja == 'team') {
                $tim_id = !empty($_POST['tim_id']) ? (int)$_POST['tim_id'] : NULL;
            }
        }
    }

    // Cek Duplikat
    $stmt = mysqli_prepare($conn, "SELECT id FROM users WHERE pin = ? OR username = ?");
    mysqli_stmt_bind_param($stmt, "ss", $pin, $username);
    mysqli_stmt_execute($stmt);
    
    if (mysqli_num_rows(mysqli_stmt_get_result($stmt)) > 0) {
        $swal_script = "Swal.fire({icon: 'error', title: 'Gagal', text: 'PIN atau Username sudah terpakai!'});";
    } else {
        mysqli_begin_transaction($conn);
        try {
            $privilege = ($role == 'admin') ? '3' : '1'; 
            $sync_status = 'pending';

            // Insert User
            $stmt_ins = mysqli_prepare($conn, "INSERT INTO users (pin, fullname, username, privilege, password, role, status_karyawan, no_hp, tgl_masuk, sync_status, jabatan_id, departemen_id, tim_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt_ins, "ssssssssssiii", $pin, $fullname, $username, $privilege, $password, $role, $status_karyawan, $no_hp, $tgl_masuk, $sync_status, $jabatan_id, $departemen_id, $tim_id);
            mysqli_stmt_execute($stmt_ins);
            $user_id = mysqli_insert_id($conn);

            // Jika LEADER -> Buat Tim Baru
            if ($status_karyawan == 'Borongan' && stripos($nama_jab, 'Leader') !== false) {
                $nama_tim_baru = "Tim " . $fullname;
                mysqli_query($conn, "INSERT INTO master_tim (nama_tim, leader_id) VALUES ('$nama_tim_baru', '$user_id')");
                $new_tim_id = mysqli_insert_id($conn);
                mysqli_query($conn, "UPDATE users SET tim_id = '$new_tim_id' WHERE id = '$user_id'");
            }

            // Jika TETAP -> Simpan Gaji
            if ($status_karyawan === 'Tetap') {
                $gapok  = preg_replace('/[^0-9]/', '', $_POST['gaji_pokok'] ?? '0');
                $makan  = preg_replace('/[^0-9]/', '', $_POST['uang_makan'] ?? '0');
                $lembur = preg_replace('/[^0-9]/', '', $_POST['gaji_lembur'] ?? '0'); 
                
                $stmt_gaji = mysqli_prepare($conn, "INSERT INTO gaji_karyawan (user_id, gaji_pokok, uang_makan, gaji_lembur) VALUES (?, ?, ?, ?)");
                mysqli_stmt_bind_param($stmt_gaji, "iiii", $user_id, $gapok, $makan, $lembur);
                mysqli_stmt_execute($stmt_gaji);
            }

            // Sync Fingerspot
            if(function_exists('fingerspot_sync_user')) {
                $sync_success = fingerspot_sync_user($pin, $fullname, $privilege);
                if ($sync_success) {
                    mysqli_query($conn, "UPDATE users SET sync_status = 'synced' WHERE id = $user_id");
                }
            }

            mysqli_commit($conn);
            $swal_script = "Swal.fire({ icon: 'success', title: 'Berhasil', text: 'Karyawan berhasil ditambahkan', timer: 1500, showConfirmButton: false }).then(() => { window.location='karyawan.php'; });";

        } catch (Exception $e) {
            mysqli_rollback($conn);
            $swal_script = "Swal.fire({icon: 'error', title: 'Error', text: 'Err: " . $e->getMessage() . "'});";
        }
    }
}

// --- 4. HAPUS KARYAWAN ---
if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    $stmt = mysqli_prepare($conn, "SELECT pin FROM users WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    
    if ($user) {
        if (!empty($user['pin']) && function_exists('fingerspot_delete_user')) {
            fingerspot_delete_user($user['pin']);
        }
        
        $cek_tim = mysqli_query($conn, "SELECT id FROM master_tim WHERE leader_id = '$id'");
        if (mysqli_num_rows($cek_tim) > 0) {
            $data_tim = mysqli_fetch_assoc($cek_tim);
            $tim_id_hapus = $data_tim['id'];
            mysqli_query($conn, "UPDATE users SET tim_id = NULL WHERE tim_id = '$tim_id_hapus'");
            mysqli_query($conn, "DELETE FROM master_tim WHERE id = '$tim_id_hapus'");
        }

        if(mysqli_query($conn, "DELETE FROM users WHERE id = $id")) {
            $swal_script = "Swal.fire({icon: 'success', title: 'Dihapus', text: 'Data karyawan berhasil dihapus.', timer: 1500, showConfirmButton: false}).then(() => { window.location='karyawan.php'; });";
        }
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
        :root { --primary: #3b82f6; --bg-body: #f1f5f9; --text-dark: #1e293b; --text-muted: #64748b; --border-color: #e2e8f0; }
        body { background-color: var(--bg-body); font-family: 'Inter', sans-serif; color: var(--text-dark); }
        .content-wrapper { padding: 30px; margin-left: 260px; transition: margin-left 0.3s ease; }
        @media (max-width: 768px) { .content-wrapper { margin-left: 0; padding: 20px; padding-bottom: 100px; } }
        
        .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; gap: 15px; }
        .page-title h3 { margin: 0; font-weight: 700; font-size: 24px; letter-spacing: -0.5px; }
        .page-title p { margin: 5px 0 0; color: var(--text-muted); font-size: 13px; }
        .modern-card { background: #fff; border-radius: 16px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); overflow: hidden; border: 1px solid var(--border-color); margin-bottom: 25px; }
        .card-header-clean { padding: 20px 25px; border-bottom: 1px solid #f1f5f9; background: #fff; display: flex; align-items: center; justify-content: space-between; }
        .card-body { padding: 25px; }
        
        .form-group { margin-bottom: 15px; }
        .form-label { font-size: 12px; font-weight: 600; color: var(--text-muted); margin-bottom: 6px; display: block; text-transform: uppercase; }
        .form-control-custom { height: 42px; border-radius: 8px; border: 1px solid var(--border-color); font-size: 13px; padding: 0 15px; width: 100%; transition: all 0.2s; background: #fff; }
        .form-control-custom:focus { border-color: var(--primary); outline: none; box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1); }
        
        .section-box { padding: 15px; border-radius: 10px; margin-bottom: 15px; animation: fadeIn 0.3s ease; }
        .section-blue { background: #eff6ff; border: 1px solid #bfdbfe; }
        .section-green { background: #f0fdf4; border: 1px solid #bbf7d0; }
        
        .table-responsive { overflow-x: auto; }
        .table-custom { width: 100%; border-collapse: separate; border-spacing: 0; }
        .table-custom th { background: #f8fafc; color: #475569; font-size: 11px; text-transform: uppercase; font-weight: 700; padding: 15px 20px; border-bottom: 1px solid var(--border-color); white-space: nowrap; }
        .table-custom td { padding: 15px 20px; border-bottom: 1px solid var(--border-color); font-size: 13px; vertical-align: middle; }
        
        .btn-save { width: 100%; padding: 12px; border-radius: 8px; font-weight: 600; font-size: 14px; background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); color: #fff; border: none; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; box-shadow: 0 4px 6px rgba(59, 130, 246, 0.2); }
        .avatar-initial { width: 36px; height: 36px; border-radius: 10px; background: #e2e8f0; color: #475569; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 13px; margin-right: 12px; }
        .badge-soft { padding: 4px 8px; border-radius: 6px; font-size: 10px; font-weight: 700; text-transform: uppercase; display: inline-block; }
        .bg-green { background: #dcfce7; color: #166534; }
        .bg-orange { background: #ffedd5; color: #9a3412; }
        @keyframes fadeIn { from { opacity:0; transform:translateY(-5px); } to { opacity:1; transform:translateY(0); } }
        @media (max-width: 992px) { .row-grid { display: flex; flex-direction: column; } }
    </style>
</head>
<body>
    <?php include '../../layout/sidebar.php'; ?>
    
    <div class="content-wrapper">
        <div class="page-header">
            <div class="page-title">
                <h3>Data Karyawan</h3>
                <p>Manajemen personel, akses sistem, dan pengaturan produksi.</p>
            </div>
        </div>

        <div class="row row-grid">
            <div class="col-lg-4 mb-4">
                <div class="modern-card">
                    <div class="card-header-clean">
                        <h5><i class="fa fa-user-plus text-primary"></i> Form Registrasi</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                            
                            <div class="form-group">
                                <label class="form-label text-primary">Status Karyawan</label>
                                <select name="status_karyawan_input" id="status_karyawan_input" class="form-control-custom" required onchange="toggleForm()">
                                    <option value="">- Pilih Status -</option>
                                    <option value="Tetap">Karyawan Tetap</option>
                                    <option value="Borongan">Karyawan Borongan</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Nama Lengkap</label>
                                <input type="text" name="fullname" class="form-control-custom" placeholder="Sesuai KTP" required>
                            </div>
                            
                            <div class="row" style="margin-left:-5px; margin-right:-5px;">
                                <div class="col-6" style="padding:0 5px;">
                                    <div class="form-group">
                                        <label class="form-label">No. HP</label>
                                        <input type="text" name="no_hp" class="form-control-custom" placeholder="08...">
                                    </div>
                                </div>
                                <div class="col-6" style="padding:0 5px;">
                                    <div class="form-group">
                                        <label class="form-label">Tgl Masuk</label>
                                        <input type="date" name="tgl_masuk" class="form-control-custom" value="<?php echo date('Y-m-d'); ?>">
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Username & PIN</label>
                                <div style="display:flex; gap:10px;">
                                    <input type="text" name="username" class="form-control-custom" placeholder="Login App" required style="flex:1;">
                                    <input type="text" name="pin" class="form-control-custom" value="<?php echo $next_pin; ?>" readonly style="width:80px; text-align:center;">
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Departemen</label>
                                <select name="departemen_id" id="departemen_id" class="form-control-custom" required>
                                    <option value="">- Pilih Departemen -</option>
                                    <?php 
                                    $q_d = mysqli_query($conn, "SELECT * FROM master_departemen ORDER BY nama_departemen ASC");
                                    while($d = mysqli_fetch_assoc($q_d)) echo "<option value='{$d['id']}'>{$d['nama_departemen']}</option>";
                                    ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Jabatan</label>
                                <select name="jabatan_id" id="jabatan_id" class="form-control-custom" required onchange="toggleForm()">
                                    <option value="">- Pilih Jabatan -</option>
                                    <?php 
                                    $q_j = mysqli_query($conn, "SELECT * FROM master_jabatan ORDER BY nama_jabatan ASC");
                                    while($j = mysqli_fetch_assoc($q_j)) echo "<option value='{$j['id']}' data-nama='{$j['nama_jabatan']}'>{$j['nama_jabatan']}</option>";
                                    ?>
                                </select>
                            </div>

                            <div id="section_produksi" class="section-box section-blue">
                                <div class="form-group">
                                    <label class="form-label text-primary">Tipe Kerja Borongan</label>
                                    <select name="tipe_kerja_produksi" id="tipe_kerja_produksi" class="form-control-custom" onchange="toggleTim()">
                                        <option value="perorangan">Perorangan (Mandiri)</option>
                                        <option value="team">Ikut Team (Anggota)</option>
                                    </select>
                                </div>

                                <div id="box_pilih_tim" class="form-group" style="display:none; margin-bottom:0;">
                                    <label class="form-label text-primary">Pilih Ketua Tim (Leader)</label>
                                    <select name="tim_id" id="tim_id_select" class="form-control-custom">
                                        <option value="">- Pilih Leader -</option>
                                        <?php 
                                        $q_t = mysqli_query($conn, "SELECT * FROM master_tim ORDER BY nama_tim ASC");
                                        while($t = mysqli_fetch_assoc($q_t)) echo "<option value='{$t['id']}'>{$t['nama_tim']}</option>";
                                        ?>
                                    </select>
                                </div>
                            </div>

                            <div id="section_gaji" class="section-box section-green">
                                <label class="form-label text-success">Setting Gaji Awal</label>
                                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:10px;">
                                    <input type="text" name="gaji_pokok" class="form-control-custom input-rupiah" placeholder="Gapok">
                                    <input type="text" name="uang_makan" class="form-control-custom input-rupiah" placeholder="Makan">
                                    <input type="text" name="gaji_lembur" class="form-control-custom input-rupiah" placeholder="Lembur" style="grid-column: span 2;">
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Password Login</label>
                                <input type="password" name="password" class="form-control-custom" placeholder="Min. 6 Karakter" required>
                            </div>

                            <button type="submit" name="simpan_karyawan" class="btn-save">
                                <i class="fa fa-floppy-disk"></i> Simpan Data
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="modern-card">
                    <div class="card-header-clean">
                        <h5><i class="fa fa-users text-success"></i> Direktori Karyawan</h5>
                        <input type="text" id="searchTable" class="form-control-custom" style="width:200px; height:36px;" placeholder="Cari...">
                    </div>
                    <div class="table-responsive">
                        <table class="table-custom" id="dataTable">
                            <thead>
                                <tr>
                                    <th>Nama & PIN</th>
                                    <th>Posisi</th>
                                    <th>Status Kerja</th>
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
                                    WHERE u.role != 'system'
                                    ORDER BY u.fullname ASC
                                ");
                                while ($k = mysqli_fetch_assoc($q)) {
                                    
                                    $bg_badge = ($k['nama_departemen'] == 'Produksi') ? 'bg-orange' : 'bg-green';
                                    $status_tim = "Staff Tetap";
                                    
                                    if ($k['status_karyawan'] == 'Borongan') {
                                        if (stripos($k['nama_jabatan'], 'Leader') !== false) {
                                            $status_tim = "Leader (Ketua)";
                                        } elseif ($k['tim_id']) {
                                            $status_tim = "Anggota: " . $k['nama_tim'];
                                        } else {
                                            $status_tim = "Mandiri (Perorangan)";
                                        }
                                    }
                                ?>
                                <tr>
                                    <td>
                                        <div style="display:flex; align-items:center;">
                                            <div class="avatar-initial"><?php echo strtoupper(substr($k['fullname'],0,1)); ?></div>
                                            <div>
                                                <div style="font-weight:700; color:#1e293b; font-size:13px;"><?php echo $k['fullname']; ?></div>
                                                <div style="font-size:11px; color:#64748b;">PIN: <b>#<?php echo $k['pin']; ?></b></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge-soft <?php echo $bg_badge; ?>"><?php echo $k['nama_departemen']; ?></span>
                                        <div style="font-size:12px; font-weight:600; margin-top:4px;"><?php echo $k['nama_jabatan']; ?></div>
                                    </td>
                                    <td>
                                        <div style="font-size:12px; color:#475569; font-weight:500;">
                                            <?php echo $status_tim; ?>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <button onclick="editPass(<?php echo $k['id']; ?>)" class="btn-save" style="width:30px; height:30px; padding:0; background:#fff; border:1px solid #e2e8f0; color:#3b82f6;"><i class="fa fa-key"></i></button>
                                        <a href="karyawan.php?hapus=<?php echo $k['id']; ?>" onclick="return confirm('Hapus?')" class="btn-save" style="width:30px; height:30px; padding:0; background:#fff; border:1px solid #e2e8f0; color:#ef4444;"><i class="fa fa-trash"></i></a>
                                    </td>
                                </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <form id="formPass" method="POST" style="display:none;">
        <input type="hidden" name="user_id" id="pass_id">
        <input type="hidden" name="new_password" id="pass_val">
        <input type="hidden" name="update_password" value="1">
    </form>

    <?php include '../../layout/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    <?php if ($swal_script) echo $swal_script; ?>

    // --- LOGIKA FORM JS ---
    function toggleForm() {
        const statusSelect = document.getElementById('status_karyawan_input');
        const statusVal = statusSelect.value;
        
        const jabSelect = document.getElementById('jabatan_id');
        const selectedOpt = jabSelect.options[jabSelect.selectedIndex];
        const jabName = selectedOpt ? selectedOpt.text : ''; // Ambil Text Jabatan

        const secProduksi = document.getElementById('section_produksi');
        const secGaji = document.getElementById('section_gaji');

        // Reset
        secProduksi.style.display = 'none';
        secGaji.style.display = 'none';

        if (statusVal === 'Tetap') {
            secGaji.style.display = 'block';
        } else if (statusVal === 'Borongan') {
            // JIKA BUKAN LEADER, TAMPILKAN PILIHAN TIM
            if (!jabName.includes('Leader')) {
                secProduksi.style.display = 'block';
                toggleTim(); 
            }
        }
    }

    function toggleTim() {
        const tipe = document.getElementById('tipe_kerja_produksi').value;
        const boxTim = document.getElementById('box_pilih_tim');
        const inputTim = document.getElementById('tim_id_select');

        if (tipe === 'team') {
            boxTim.style.display = 'block';
            inputTim.setAttribute('required', 'required');
        } else {
            boxTim.style.display = 'none';
            inputTim.removeAttribute('required');
            inputTim.value = "";
        }
    }

    function editPass(id) {
        Swal.fire({
            title: 'Password Baru',
            input: 'password',
            showCancelButton: true,
            confirmButtonText: 'Ubah'
        }).then((result) => {
            if (result.value) {
                document.getElementById('pass_id').value = id;
                document.getElementById('pass_val').value = result.value;
                document.getElementById('formPass').submit();
            }
        });
    }

    document.querySelectorAll('.input-rupiah').forEach(i => {
        i.addEventListener('keyup', function() {
            this.value = this.value.replace(/\D/g,'').replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        });
    });

    document.getElementById('searchTable').addEventListener('keyup', function() {
        let filter = this.value.toLowerCase();
        document.querySelectorAll('#dataTable tbody tr').forEach(r => {
            r.style.display = r.innerText.toLowerCase().includes(filter) ? '' : 'none';
        });
    });
    
    // Init state saat load
    toggleForm();
    </script>
</body>
</html>