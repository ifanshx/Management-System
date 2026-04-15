<?php 
// --- 1. AKTIFKAN REPORTING ERROR (Agar ketahuan jika ada masalah) ---
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// --- 2. INCLUDE FILE PENTING ---
// Pastikan urutan dan path-nya benar
require_once '../../config/database.php';

// Cek apakah file function ada sebelum di-require (Menghindari blank putih)
if (file_exists('../../config/function.php')) {
    require_once '../../config/function.php';
}

// --- 3. CEK LOGIN ---
// Pastikan fungsi cek_login() sudah didefinisikan di function.php
if (function_exists('cek_login')) {
    cek_login();
} else {
    // Fallback manual jika fungsi tidak ada
    session_start();
    if (!isset($_SESSION['role'])) {
        header("Location: ../../login.php");
        exit;
    }
}

// Validasi Role Admin
if($_SESSION['role'] != 'admin') {
    echo "<script>window.location='../dashboard.php';</script>"; exit;
}

$swal_script = "";

// --- 4. LOGIKA PROSES DATA ---
if(isset($_POST['aksi'])) {
    $id = (int)$_POST['id'];
    $aksi = $_POST['aksi'];
    
    // --- AKSI TERIMA (APPROVE) ---
    if($aksi == 'terima') {
        $jml_baru = (int)$_POST['jumlah_revisi'];
        
        // Ambil ID Pekerjaan
        $q_log = mysqli_query($conn, "SELECT pekerjaan_id FROM hasil_produksi_borongan WHERE id='$id'");
        if($q_log && mysqli_num_rows($q_log) > 0){
            $d_log = mysqli_fetch_assoc($q_log);
            $pid   = $d_log['pekerjaan_id'];

            // Ambil Harga dari Master
            $q_hrg = mysqli_query($conn, "SELECT harga FROM master_pekerjaan_borongan WHERE id='$pid'");
            $d_hrg = mysqli_fetch_assoc($q_hrg);
            
            $harga = $d_hrg ? $d_hrg['harga'] : 0; 
            $total = $jml_baru * $harga;

            $update = mysqli_query($conn, "UPDATE hasil_produksi_borongan SET jumlah='$jml_baru', total_upah='$total', status='Approved' WHERE id='$id'");
            
            if($update) $swal_script = "Swal.fire({icon: 'success', title: 'Disetujui', text: 'Data berhasil diverifikasi.', timer: 1500, showConfirmButton: false});";
        }
    
    // --- AKSI TOLAK (REJECT) ---
    } elseif ($aksi == 'tolak') {
        $update = mysqli_query($conn, "UPDATE hasil_produksi_borongan SET status='Rejected' WHERE id='$id'");
        if($update) $swal_script = "Swal.fire({icon: 'error', title: 'Ditolak', text: 'Laporan ditolak.', timer: 1500, showConfirmButton: false});";
    
    // --- AKSI HAPUS (DELETE) ---
    } elseif ($aksi == 'hapus') {
        $del = mysqli_query($conn, "DELETE FROM hasil_produksi_borongan WHERE id='$id'");
        if($del) $swal_script = "Swal.fire({icon: 'success', title: 'Terhapus', text: 'Data dihapus permanen.', timer: 1500, showConfirmButton: false});";

    // --- AKSI EDIT ---
    } elseif ($aksi == 'edit') {
        $jml_edit = (int)$_POST['jumlah_edit'];
        
        $q_log = mysqli_query($conn, "SELECT pekerjaan_id FROM hasil_produksi_borongan WHERE id='$id'");
        if($q_log && mysqli_num_rows($q_log) > 0){
            $d_log = mysqli_fetch_assoc($q_log);
            $pid   = $d_log['pekerjaan_id'];

            $q_hrg = mysqli_query($conn, "SELECT harga FROM master_pekerjaan_borongan WHERE id='$pid'");
            $d_hrg = mysqli_fetch_assoc($q_hrg);
            
            $harga = $d_hrg ? $d_hrg['harga'] : 0; 
            $total = $jml_edit * $harga;

            $update = mysqli_query($conn, "UPDATE hasil_produksi_borongan SET jumlah='$jml_edit', total_upah='$total' WHERE id='$id'");
            if($update) $swal_script = "Swal.fire({icon: 'success', title: 'Diperbarui', text: 'Data berhasil diedit.', timer: 1500, showConfirmButton: false});";
        }
    }
}

// --- 5. QUERY DATA ---
$tab = isset($_GET['tab']) ? $_GET['tab'] : 'pending';
$search_val = isset($_GET['q']) ? mysqli_real_escape_string($conn, $_GET['q']) : "";

$base_query = "
    SELECT 
        hp.*, 
        u.fullname, 
        mp.jenis_pekerjaan, 
        mp.nama_motor 
    FROM hasil_produksi_borongan hp 
    LEFT JOIN users u ON hp.user_id = u.id
    LEFT JOIN master_pekerjaan_borongan mp ON hp.pekerjaan_id = mp.id
";

if ($tab == 'pending') {
    $where = "WHERE hp.status = 'Pending'";
} else {
    $where = "WHERE hp.status != 'Pending'";
}

if (!empty($search_val)) {
    $where .= " AND (u.fullname LIKE '%$search_val%' OR mp.jenis_pekerjaan LIKE '%$search_val%' OR mp.nama_motor LIKE '%$search_val%')";
}

$query_final = "$base_query $where ORDER BY hp.tanggal DESC, hp.created_at DESC LIMIT 50";
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <?php include '../../layout/header.php'; ?>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />

    <style>
        body { background-color: #f8fafc; font-family: 'Plus Jakarta Sans', sans-serif; color: #334155; }
        .content-wrapper { padding: 30px 20px; }
        .page-header { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 30px; flex-wrap: wrap; gap: 15px; }
        .page-title h3 { margin: 0; font-weight: 800; color: #1e293b; font-size: 24px; letter-spacing: -0.5px; }
        .page-title p { margin: 5px 0 0; color: #64748b; font-size: 14px; }
        .nav-tabs-custom { display: flex; gap: 10px; border-bottom: 1px solid #e2e8f0; margin-bottom: 20px; }
        .nav-link { padding: 12px 20px; font-weight: 600; color: #64748b; text-decoration: none; border-bottom: 3px solid transparent; transition: all 0.3s; display: flex; align-items: center; gap: 8px; font-size: 14px; }
        .nav-link:hover, .nav-link.active { color: #4f46e5; border-bottom-color: #4f46e5; background: rgba(79, 70, 229, 0.05); border-radius: 8px 8px 0 0; }
        .modern-card { background: #fff; border-radius: 16px; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); overflow: hidden; }
        .search-box { position: relative; max-width: 300px; }
        .search-input { width: 100%; padding: 10px 15px 10px 40px; border-radius: 50px; border: 1px solid #cbd5e1; font-size: 14px; transition: all 0.3s; background: #fff; }
        .search-input:focus { border-color: #4f46e5; box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1); outline: none; }
        .search-icon { position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: #94a3b8; }
        .table-responsive { width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch; }
        .table-approval { width: 100%; border-collapse: collapse; min-width: 900px; }
        .table-approval th { background: #f8fafc; color: #64748b; font-size: 11px; text-transform: uppercase; font-weight: 700; padding: 15px 20px; border-bottom: 1px solid #e2e8f0; letter-spacing: 0.5px; white-space: nowrap; }
        .table-approval td { padding: 15px 20px; border-bottom: 1px solid #f1f5f9; font-size: 14px; vertical-align: middle; color: #334155; }
        .input-koreksi-wrapper { display: flex; align-items: center; border: 1px solid #cbd5e1; border-radius: 8px; overflow: hidden; width: 100px; }
        .input-koreksi { border: none; text-align: center; font-weight: 700; width: 60px; padding: 8px; font-size: 14px; color: #1e293b; outline: none; }
        .input-unit { background: #f1f5f9; padding: 8px 10px; font-size: 11px; font-weight: 600; color: #64748b; border-left: 1px solid #cbd5e1; }
        .badge-job { background: #eff6ff; color: #4f46e5; padding: 4px 10px; border-radius: 6px; font-weight: 700; font-size: 12px; display: inline-block; margin-bottom: 4px; }
        .badge-motor { background: #f1f5f9; color: #475569; padding: 2px 8px; border-radius: 4px; font-size: 11px; font-weight: 600; border: 1px solid #e2e8f0; display: inline-block; }
        .st-Approved { background: #dcfce7; color: #166534; padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; text-transform: uppercase; }
        .st-Rejected { background: #fee2e2; color: #991b1b; padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; text-transform: uppercase; }
        .avatar-circle { width: 40px; height: 40px; border-radius: 10px; background: #e0f2fe; color: #0284c7; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 16px; margin-right: 12px; border: 1px solid #bae6fd; }
        .btn-action { width: 36px; height: 36px; border-radius: 10px; display: inline-flex; align-items: center; justify-content: center; border: none; transition: 0.2s; cursor: pointer; }
        .btn-accept { background: #dcfce7; color: #16a34a; }
        .btn-accept:hover { background: #16a34a; color: #fff; transform: translateY(-2px); }
        .btn-reject { background: #fee2e2; color: #dc2626; }
        .btn-reject:hover { background: #dc2626; color: #fff; transform: translateY(-2px); }
        .btn-edit { background: #fff; border: 1px solid #d1d5db; color: #4f46e5; }
        .btn-edit:hover { background: #eff6ff; border-color: #4f46e5; }
        .btn-del { background: #fff; border: 1px solid #d1d5db; color: #ef4444; }
        .btn-del:hover { background: #fee2e2; border-color: #ef4444; }
        .empty-state { padding: 50px 20px; text-align: center; color: #94a3b8; }
        .empty-icon { font-size: 48px; margin-bottom: 15px; opacity: 0.5; display: block; }
        @media (max-width: 768px) {
            .page-header { flex-direction: column; align-items: flex-start; }
            .search-box { max-width: 100%; width: 100%; }
            .nav-tabs-custom { overflow-x: auto; white-space: nowrap; padding-bottom: 5px; }
        }
    </style>
</head>
<body>
    <?php include '../../layout/sidebar.php'; ?>
    
    <div class="content-wrapper">
        <div class="page-header">
            <div class="page-title">
                <h3>Verifikasi Produksi</h3>
                <p>Validasi laporan kerja harian karyawan borongan.</p>
            </div>
            
            <form method="GET" class="search-box">
                <input type="hidden" name="tab" value="<?= $tab ?>">
                <i class="fa fa-search search-icon"></i>
                <input type="text" name="q" class="search-input" placeholder="Cari karyawan / pekerjaan..." value="<?= htmlspecialchars($search_val) ?>" onchange="this.form.submit()">
            </form>
        </div>

        <div class="nav-tabs-custom">
            <a href="?tab=pending" class="nav-link <?= ($tab=='pending')?'active':'' ?>">
                <i class="fa fa-clock"></i> Menunggu
                <?php 
                    // Pastikan tabelnya benar (hasil_produksi_borongan)
                    $res_count = mysqli_query($conn, "SELECT id FROM hasil_produksi_borongan WHERE status='Pending'");
                    $count_pending = ($res_count) ? mysqli_num_rows($res_count) : 0;
                    if($count_pending > 0) echo "<span style='background:#ef4444; color:#fff; padding:2px 6px; border-radius:4px; font-size:10px; margin-left:5px;'>$count_pending</span>";
                ?>
            </a>
            <a href="?tab=history" class="nav-link <?= ($tab=='history')?'active':'' ?>">
                <i class="fa fa-history"></i> Riwayat
            </a>
        </div>

        <div class="modern-card">
            <div class="table-responsive">
                <table class="table-approval">
                    <thead>
                        <tr>
                            <th width="25%">Karyawan</th>
                            <th width="30%">Detail Pekerjaan</th>
                            <?php if($tab == 'pending'): ?>
                                <th width="15%" class="text-center">Koreksi Jumlah</th>
                                <th width="15%" class="text-right">Est. Upah</th>
                                <th width="15%" class="text-center">Aksi</th>
                            <?php else: ?>
                                <th width="10%" class="text-center">Jumlah</th>
                                <th width="15%" class="text-right">Total Upah</th>
                                <th width="10%" class="text-center">Status</th>
                                <th width="10%" class="text-center">Aksi</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $q = mysqli_query($conn, $query_final);
                        
                        // Cek error query jika ada
                        if (!$q) {
                            echo "<tr><td colspan='6' class='text-center text-danger'>Error Query: ".mysqli_error($conn)."</td></tr>";
                        } elseif(mysqli_num_rows($q) > 0) {
                            while($d = mysqli_fetch_assoc($q)) {
                                $fid = "form_verif_".$d['id'];
                                $job = $d['jenis_pekerjaan'];
                                $mtr = $d['nama_motor'];
                                $fullname = isset($d['fullname']) ? $d['fullname'] : 'Unknown';
                                $initials = strtoupper(substr($fullname, 0, 1));
                        ?>
                        <tr>
                            <td>
                                <div style="display:flex; align-items:center;">
                                    <div class="avatar-circle"><?php echo $initials; ?></div>
                                    <div>
                                        <div style="font-weight:700; color:#1e293b; font-size:14px;"><?php echo $fullname; ?></div>
                                        <div style="font-size:12px; color:#64748b; margin-top:2px;">
                                            <i class="fa fa-calendar-alt"></i> <?php echo date('d M Y', strtotime($d['tanggal'])); ?>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge-job"><?php echo $job; ?></span><br>
                                <?php if($mtr): ?>
                                    <span class="badge-motor"><i class="fa fa-wrench"></i> <?php echo $mtr; ?></span>
                                <?php endif; ?>
                            </td>
                            
                            <?php if($tab == 'pending'): ?>
                                <td class="text-center">
                                    <div style="display:flex; justify-content:center;">
                                        <div class="input-koreksi-wrapper">
                                            <input type="number" name="jumlah_revisi" value="<?php echo $d['jumlah']; ?>" form="<?php echo $fid; ?>" class="input-koreksi" min="1">
                                            <div class="input-unit">Pcs</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-right">
                                    <span style="font-weight:700; color:#10b981; font-size:14px;">Rp <?php echo number_format($d['total_upah']); ?></span>
                                </td>
                                <td class="text-center">
                                    <form id="<?php echo $fid; ?>" method="POST" style="display:inline-flex; gap:8px;">
                                        <input type="hidden" name="id" value="<?php echo $d['id']; ?>">
                                        
                                        <button type="button" onclick="confirmAction('terima', '<?php echo $fid; ?>')" class="btn-action btn-accept" title="Setujui">
                                            <i class="fa fa-check"></i>
                                        </button>
                                        
                                        <button type="button" onclick="confirmAction('tolak', '<?php echo $fid; ?>')" class="btn-action btn-reject" title="Tolak">
                                            <i class="fa fa-times"></i>
                                        </button>

                                        <input type="hidden" name="aksi" id="aksi_<?php echo $fid; ?>" value="">
                                    </form>
                                </td>
                            <?php else: ?>
                                <td class="text-center">
                                    <span style="font-weight:700; color:#334155;"><?php echo $d['jumlah']; ?> Pcs</span>
                                </td>
                                <td class="text-right">
                                    <span style="font-weight:700; color:#10b981;">Rp <?php echo number_format($d['total_upah']); ?></span>
                                </td>
                                <td class="text-center">
                                    <span class="st-<?php echo $d['status']; ?>"><?php echo $d['status']; ?></span>
                                </td>
                                <td class="text-center">
                                    <form id="<?php echo $fid; ?>" method="POST" style="display:inline-flex; gap:8px;">
                                        <input type="hidden" name="id" value="<?php echo $d['id']; ?>">
                                        <input type="hidden" name="jumlah_edit" id="jumlah_edit_<?php echo $fid; ?>" value="<?php echo $d['jumlah']; ?>">
                                        <input type="hidden" name="aksi" id="aksi_<?php echo $fid; ?>" value="">

                                        <button type="button" onclick="editJumlah('<?php echo $fid; ?>', '<?php echo $d['jumlah']; ?>')" class="btn-action btn-edit" title="Edit Jumlah">
                                            <i class="fa fa-pencil"></i>
                                        </button>

                                        <button type="button" onclick="confirmAction('hapus', '<?php echo $fid; ?>')" class="btn-action btn-del" title="Hapus Permanen">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            <?php endif; ?>
                        </tr>
                        <?php 
                            } 
                        } else {
                        ?>
                            <tr>
                                <td colspan="<?php echo ($tab=='pending')?5:6; ?>">
                                    <div class="empty-state">
                                        <i class="fa fa-folder-open empty-icon"></i>
                                        <h5 style="font-weight:700; margin:0;">Tidak Ada Data</h5>
                                        <p style="font-size:13px; margin-top:5px;">Belum ada laporan produksi pada tab ini.</p>
                                    </div>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <?php include '../../layout/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
        <?php if(!empty($swal_script)) echo $swal_script; ?>

        function editJumlah(formId, currentVal) {
            Swal.fire({
                title: 'Edit Jumlah',
                input: 'number',
                inputValue: currentVal,
                inputAttributes: { min: 1 },
                showCancelButton: true,
                confirmButtonText: 'Simpan',
                confirmButtonColor: '#4f46e5',
                preConfirm: (val) => {
                    if (!val || val < 1) Swal.showValidationMessage('Jumlah minimal 1');
                    return val;
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('jumlah_edit_' + formId).value = result.value;
                    document.getElementById('aksi_' + formId).value = 'edit';
                    document.getElementById(formId).submit();
                }
            });
        }

        function confirmAction(type, formId) {
            let config = {};
            if (type === 'terima') {
                config = { title: 'Setujui?', text: 'Pastikan data sudah benar.', icon: 'question', confirmBtnColor: '#16a34a' };
            } else if (type === 'tolak') {
                config = { title: 'Tolak?', text: 'Laporan akan ditolak.', icon: 'warning', confirmBtnColor: '#ef4444' };
            } else if (type === 'hapus') {
                config = { title: 'Hapus?', text: 'Data akan hilang permanen.', icon: 'warning', confirmBtnColor: '#ef4444' };
            }

            Swal.fire({
                title: config.title,
                text: config.text,
                icon: config.icon,
                showCancelButton: true,
                confirmButtonColor: config.confirmBtnColor,
                cancelButtonColor: '#cbd5e1',
                confirmButtonText: 'Ya',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('aksi_' + formId).value = type;
                    document.getElementById(formId).submit();
                }
            })
        }
    </script>
</body>
</html>