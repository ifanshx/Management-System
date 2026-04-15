<?php 
require_once '../../config/database.php';
cek_login(); 

// Validasi Role: Hanya Admin yang boleh akses
if ($_SESSION['role'] !== 'admin') {
    echo "<script>window.location='../dashboard.php';</script>";
    exit;
}

$swal_script = "";

// --- LOGIKA SIMPAN GAJI TETAP ---
if (isset($_POST['simpan_gaji_individu'])) {
    $stmt = mysqli_prepare($conn, "INSERT INTO gaji_karyawan (user_id, gaji_pokok, uang_makan, gaji_lembur) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE gaji_pokok = VALUES(gaji_pokok), uang_makan = VALUES(uang_makan), gaji_lembur = VALUES(gaji_lembur)");
    
    if ($stmt) {
        $success_count = 0;
        foreach ($_POST['user'] as $uid => $data) {
            $uid = (int)$uid;
            // Bersihkan format titik sebelum simpan ke DB
            $gapok  = (int)str_replace('.', '', $data['gapok'] ?? '0');
            $makan  = (int)str_replace('.', '', $data['makan'] ?? '0');
            $lembur = (int)str_replace('.', '', $data['lembur'] ?? '0');
            
            mysqli_stmt_bind_param($stmt, "iiii", $uid, $gapok, $makan, $lembur);
            if(mysqli_stmt_execute($stmt)) {
                $success_count++;
            }
        }
        mysqli_stmt_close($stmt);
        
        if($success_count > 0) {
            $swal_script = "Swal.fire({
                icon: 'success', 
                title: 'Berhasil Disimpan', 
                text: 'Data gaji karyawan berhasil diperbarui!',
                timer: 1500, 
                showConfirmButton: false
            });";
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
        /* --- PAGE SPECIFIC STYLE --- */
        :root { 
            --primary: #3b82f6; /* Sesuai Sidebar */
            --primary-dark: #2563eb;
            --bg-body: #f1f5f9; 
            --text-dark: #1e293b;
            --text-muted: #64748b;
            --border-color: #e2e8f0;
            --card-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
        }
        
        body { background-color: var(--bg-body); font-family: 'Inter', sans-serif; color: var(--text-dark); }
        
        /* Container disesuaikan dengan Sidebar width (260px) */
        .content-wrapper { 
            padding: 30px; 
            margin-left: 260px; /* Sesuai lebar sidebar */
            transition: margin-left 0.3s ease;
        }
        
        @media (max-width: 768px) {
            .content-wrapper { margin-left: 0; padding: 20px; padding-bottom: 100px; }
        }

        /* Header Section */
        .page-header { margin-bottom: 25px; display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 15px; }
        .page-title h2 { margin: 0; font-weight: 700; color: var(--text-dark); font-size: 24px; letter-spacing: -0.5px; }
        .page-title p { margin: 5px 0 0; color: var(--text-muted); font-size: 14px; }

        /* Modern Card */
        .modern-card {
            background: #fff;
            border-radius: 16px;
            box-shadow: var(--card-shadow);
            overflow: hidden;
            border: 1px solid var(--border-color);
        }

        /* Toolbar (Search & Info) */
        .toolbar {
            padding: 20px 25px;
            background: #fff;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }
        .search-wrapper {
            position: relative;
            width: 350px;
            max-width: 100%;
        }
        .search-wrapper i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
        }
        .search-input {
            width: 100%;
            padding: 12px 15px 12px 45px;
            border-radius: 10px;
            border: 1px solid var(--border-color);
            background: #f8fafc;
            font-size: 14px;
            transition: all 0.3s;
        }
        .search-input:focus {
            background: #fff;
            border-color: var(--primary);
            outline: none;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        /* Table Styling */
        .table-responsive { overflow-x: auto; -webkit-overflow-scrolling: touch; }
        .table-custom { width: 100%; border-collapse: separate; border-spacing: 0; }
        .table-custom th {
            background: #f8fafc;
            color: #475569;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 16px 24px;
            border-bottom: 1px solid var(--border-color);
            white-space: nowrap;
        }
        .table-custom td {
            padding: 16px 24px;
            vertical-align: middle;
            border-bottom: 1px solid var(--border-color);
            background: #fff;
            transition: background 0.2s;
        }
        .table-custom tr:hover td { background: #f8fafc; }
        .table-custom tr:last-child td { border-bottom: none; }

        /* User Profile in Table */
        .user-info { display: flex; align-items: center; gap: 15px; }
        .avatar-circle {
            width: 42px; height: 42px;
            /* Gradient Biru Sesuai Sidebar */
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: #fff;
            border-radius: 12px; /* Rounded square looks more modern */
            display: flex; align-items: center; justify-content: center;
            font-weight: 700; font-size: 15px;
            flex-shrink: 0;
            box-shadow: 0 4px 6px -1px rgba(59, 130, 246, 0.3);
        }
        .user-details h6 { margin: 0; font-size: 14px; font-weight: 600; color: var(--text-dark); }
        .user-details span { font-size: 12px; color: var(--text-muted); display: block; margin-top: 2px; }

        /* Input Fields inside Table */
        .input-group-custom {
            display: flex; align-items: center;
            background: #fff;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            overflow: hidden;
            transition: all 0.2s;
        }
        .input-group-custom:focus-within {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        .input-prefix {
            background: #f8fafc;
            color: #64748b;
            font-size: 12px;
            font-weight: 600;
            padding: 8px 12px;
            border-right: 1px solid var(--border-color);
        }
        .input-currency {
            border: none;
            padding: 8px 12px;
            width: 100%;
            min-width: 100px;
            font-family: 'Inter', sans-serif;
            font-weight: 600;
            color: var(--text-dark);
            text-align: right;
            outline: none;
            font-size: 13px;
        }

        /* Badges for Roles */
        .badge-role {
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: inline-block;
        }
        .bg-head { background: #e0f2fe; color: #0284c7; border: 1px solid #bae6fd; } /* Kepala Bengkel */
        .bg-staff { background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; } /* Staff */
        .bg-admin { background: #f0fdf4; color: #16a34a; border: 1px solid #bbf7d0; } /* Admin */

        /* Floating/Sticky Footer Action Bar */
        .card-footer-action {
            background: #fff;
            padding: 15px 25px;
            border-top: 1px solid var(--border-color);
            display: flex;
            justify-content: flex-end;
            align-items: center;
            position: sticky;
            bottom: 0;
            z-index: 10;
            box-shadow: 0 -4px 10px rgba(0,0,0,0.03);
        }

        .btn-save {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: #fff;
            border: none;
            padding: 12px 30px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            box-shadow: 0 4px 10px rgba(37, 99, 235, 0.3);
            transition: all 0.2s;
            display: flex; align-items: center; gap: 8px;
        }
        .btn-save:hover { transform: translateY(-2px); box-shadow: 0 6px 15px rgba(37, 99, 235, 0.4); }
        .btn-save:active { transform: translateY(0); }

    </style>
</head>
<body>
    <?php include '../../layout/sidebar.php'; ?>
    
    <div class="content-wrapper">
        <div class="page-header">
            <div class="page-title">
                <h2>Gaji Karyawan Tetap</h2>
                <p>Atur gaji pokok, uang makan, dan lembur untuk staff bulanan & manajemen.</p>
            </div>
        </div>

        <form method="POST">
            <div class="modern-card">
                <div class="toolbar">
                    <div class="search-wrapper">
                        <i class="fa fa-magnifying-glass"></i>
                        <input type="text" id="searchEmployee" class="search-input" placeholder="Cari nama atau jabatan...">
                    </div>
                    
                    <div style="display:flex; gap:10px; font-size:12px;">
                        <div style="display:flex; align-items:center; gap:5px;">
                            <span style="width:10px; height:10px; background:#e0f2fe; border:1px solid #bae6fd; border-radius:3px;"></span> Kepala
                        </div>
                        <div style="display:flex; align-items:center; gap:5px;">
                            <span style="width:10px; height:10px; background:#f1f5f9; border:1px solid #e2e8f0; border-radius:3px;"></span> Staff
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table-custom" id="salaryTable">
                        <thead>
                            <tr>
                                <th width="35%">Profil Karyawan</th>
                                <th width="20%">Gaji Pokok <small style="color:#94a3b8; font-weight:400; text-transform:none;">/ Hari</small></th>
                                <th width="20%">Uang Makan <small style="color:#94a3b8; font-weight:400; text-transform:none;">/ Hari</small></th>
                                <th width="20%">Lembur <small style="color:#94a3b8; font-weight:400; text-transform:none;">/ Jam</small></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Query: Ambil User Tetap, Admin, & Kepala Bengkel
                            $query = "
                                SELECT u.id, u.fullname, u.role, mj.nama_jabatan,
                                COALESCE(g.gaji_pokok, 0) AS gaji_pokok, 
                                COALESCE(g.uang_makan, 0) AS uang_makan, 
                                COALESCE(g.gaji_lembur, 0) AS gaji_lembur 
                                FROM users u 
                                LEFT JOIN gaji_karyawan g ON u.id = g.user_id 
                                LEFT JOIN master_jabatan mj ON u.jabatan_id = mj.id
                                WHERE (u.status_karyawan = 'Tetap' AND u.role != 'admin') 
                                   OR u.role = 'kepala_bengkel'
                                   OR mj.nama_jabatan IN ('Administrator', 'Staff Admin')
                                ORDER BY 
                                    CASE WHEN u.role = 'kepala_bengkel' THEN 1 ELSE 2 END,
                                    u.fullname ASC
                            ";
                            
                            $result = mysqli_query($conn, $query);
                            
                            if(mysqli_num_rows($result) > 0) {
                                while ($row = mysqli_fetch_assoc($result)) { 
                                    // Visual Logic
                                    $initial = strtoupper(substr($row['fullname'], 0, 1));
                                    $jabatan = !empty($row['nama_jabatan']) ? $row['nama_jabatan'] : 'Staff Tetap';
                                    
                                    // Tentukan Warna Badge
                                    if($row['role'] == 'kepala_bengkel' || stripos($jabatan, 'Kepala') !== false) {
                                        $badge_cls = 'bg-head';
                                        $role_txt = 'Management';
                                    } elseif($row['role'] == 'admin') {
                                        $badge_cls = 'bg-admin';
                                        $role_txt = 'Admin System';
                                    } else {
                                        $badge_cls = 'bg-staff';
                                        $role_txt = 'Karyawan Tetap';
                                    }
                                    ?>
                                    <tr>
                                        <td>
                                            <div class="user-info">
                                                <div class="avatar-circle"><?php echo $initial; ?></div>
                                                <div class="user-details">
                                                    <h6><?php echo $row['fullname']; ?></h6>
                                                    <span class="badge-role <?php echo $badge_cls; ?>"><?php echo $jabatan; ?></span>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="input-group-custom">
                                                <span class="input-prefix">Rp</span>
                                                <input type="text" name="user[<?php echo $row['id']; ?>][gapok]" 
                                                       class="input-currency input-rupiah" 
                                                       value="<?php echo number_format($row['gaji_pokok'], 0, ',', '.'); ?>" 
                                                       placeholder="0">
                                            </div>
                                        </td>
                                        <td>
                                            <div class="input-group-custom">
                                                <span class="input-prefix">Rp</span>
                                                <input type="text" name="user[<?php echo $row['id']; ?>][makan]" 
                                                       class="input-currency input-rupiah" 
                                                       value="<?php echo number_format($row['uang_makan'], 0, ',', '.'); ?>" 
                                                       placeholder="0">
                                            </div>
                                        </td>
                                        <td>
                                            <div class="input-group-custom">
                                                <span class="input-prefix">Rp</span>
                                                <input type="text" name="user[<?php echo $row['id']; ?>][lembur]" 
                                                       class="input-currency input-rupiah" 
                                                       value="<?php echo number_format($row['gaji_lembur'], 0, ',', '.'); ?>" 
                                                       placeholder="0">
                                            </div>
                                        </td>
                                    </tr>
                                <?php } 
                            } else { 
                                echo '<tr><td colspan="4" class="text-center" style="padding:50px; color:#94a3b8;">
                                    <i class="fa fa-users-slash" style="font-size:30px; margin-bottom:10px;"></i><br>
                                    Tidak ada data karyawan tetap.
                                </td></tr>'; 
                            } ?>
                        </tbody>
                    </table>
                </div>

                <div class="card-footer-action">
                    <button type="submit" name="simpan_gaji_individu" class="btn-save">
                        <i class="fa fa-floppy-disk"></i> Simpan Perubahan
                    </button>
                </div>
            </div>
        </form>
    </div>
    
    <?php include '../../layout/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        <?php if (!empty($swal_script)) echo $swal_script; ?>
        
        // 1. Fitur Pencarian Real-time
        document.getElementById('searchEmployee').addEventListener('keyup', function() {
            let filter = this.value.toLowerCase();
            let rows = document.querySelectorAll('#salaryTable tbody tr');
            
            rows.forEach(row => {
                let name = row.querySelector('.user-details h6').textContent.toLowerCase();
                let role = row.querySelector('.badge-role').textContent.toLowerCase();
                
                if (name.includes(filter) || role.includes(filter)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
        
        // 2. Format Rupiah Otomatis
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
                
                rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
                this.value = rupiah;
            });
        });
    </script>
</body>
</html>