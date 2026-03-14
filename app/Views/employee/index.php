<?= $this->extend('layout/template') ?>
<?= $this->section('content') ?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // --- FUNGSI KONFIRMASI CUSTOM SWEETALERT2 ---
    function confirmDelete(id, name) {
        const isDark = document.documentElement.classList.contains('dark');
        Swal.fire({
            title: 'Hapus Data Permanen?', 
            html: `Apakah Anda yakin ingin menghapus data <b>${name}</b>?<br><br><span style="color:#ef4444; font-size:12px; font-weight:bold; background: rgba(239, 68, 68, 0.1); padding: 4px 8px; border-radius: 6px;">Akses login dan seluruh riwayat absensinya akan terhapus.</span>`,
            icon: 'warning', 
            showCancelButton: true, 
            confirmButtonColor: '#ef4444', 
            cancelButtonColor: isDark ? '#3f3f46' : '#cbd5e1', 
            confirmButtonText: 'Ya, Hapus!', 
            cancelButtonText: 'Batal',
            background: isDark ? '#18181b' : '#ffffff', 
            color: isDark ? '#f4f4f5' : '#09090b',
        }).then((result) => {
            if (result.isConfirmed) document.getElementById('delete-form-' + id).submit();
        });
    }

    function confirmDeactivate(id, name) {
        const isDark = document.documentElement.classList.contains('dark');
        Swal.fire({
            title: 'Proses Karyawan Keluar?', 
            html: `Anda akan menonaktifkan <b>${name}</b>.<br><br>Status karyawan akan menjadi <b>NON-AKTIF</b> dan hak akses portal dicabut, riwayat penggajian akan diarsipkan.`,
            icon: 'warning', 
            showCancelButton: true, 
            confirmButtonColor: '#f59e0b', 
            cancelButtonColor: isDark ? '#3f3f46' : '#cbd5e1', 
            confirmButtonText: 'Ya, Proses Resign!', 
            cancelButtonText: 'Batal',
            background: isDark ? '#18181b' : '#ffffff', 
            color: isDark ? '#f4f4f5' : '#09090b',
        }).then((result) => {
            if (result.isConfirmed) document.getElementById('deact-form-' + id).submit();
        });
    }

    function confirmPush(url, name) {
        const isDark = document.documentElement.classList.contains('dark');
        Swal.fire({
            title: 'Daftarkan ke Mesin Absen?', 
            text: `Sistem akan mengirimkan identitas ${name} ke mesin fisik pabrik. Lanjutkan?`,
            icon: 'info', 
            showCancelButton: true, 
            confirmButtonColor: '#10b981', 
            cancelButtonColor: isDark ? '#3f3f46' : '#cbd5e1', 
            confirmButtonText: 'Ya, Kirim Data', 
            cancelButtonText: 'Batal',
            background: isDark ? '#18181b' : '#ffffff', 
            color: isDark ? '#f4f4f5' : '#09090b',
        }).then((result) => {
            if (result.isConfirmed) window.location.href = url;
        });
    }
</script>

<style>
    /* =========================================================
       1. PAGE HEADER & BENTO CARD
       ========================================================= */
    .page-header { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 30px; flex-wrap: wrap; gap: 20px; }
    .page-title { display: flex; align-items: center; gap: 15px;}
    .title-icon { width: 50px; height: 50px; border-radius: 14px; background: linear-gradient(135deg, rgba(37, 99, 235, 0.15), rgba(29, 78, 216, 0.05)); color: #2563eb; display: flex; align-items: center; justify-content: center; font-size: 24px; border: 1px solid rgba(37, 99, 235, 0.2);}
    .page-title h1 { font-size: 26px; font-weight: 900; color: var(--text-main); margin: 0 0 4px 0; letter-spacing: -0.5px; }
    .page-title p { color: var(--text-muted); font-size: 13px; margin: 0; font-weight: 500;}
    
    .btn-primary { background: linear-gradient(135deg, #2563eb, #1d4ed8); color: #fff; border: none; padding: 14px 24px; border-radius: 14px; font-weight: 900; font-size: 14px; cursor: pointer; display: flex; align-items: center; gap: 8px; transition: 0.3s; box-shadow: 0 8px 20px -6px rgba(37, 99, 235, 0.5); text-decoration: none;}
    .btn-primary:hover { transform: translateY(-3px); box-shadow: 0 12px 25px -6px rgba(37, 99, 235, 0.6);}
    
    .table-card { background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: 24px; box-shadow: 0 10px 30px -10px rgba(0,0,0,0.05); overflow: hidden; }
    .table-controls { padding: 25px 30px; border-bottom: 2px dashed var(--border-subtle); display: flex; justify-content: space-between; align-items: center; gap: 15px; flex-wrap: wrap; background: rgba(0,0,0,0.01); }
    html.dark .table-controls { background: rgba(255,255,255,0.01); }

    /* =========================================================
       2. SEARCH BOX
       ========================================================= */
    .search-box { position: relative; width: 100%; max-width: 450px; }
    .search-box i { position: absolute; left: 20px; top: 50%; transform: translateY(-50%); color: #2563eb; font-size: 20px;}
    .search-box input { width: 100%; background: var(--bg-base); border: 2px solid transparent; padding: 14px 20px 14px 50px; border-radius: 14px; color: var(--text-main); outline: none; font-size: 14px; font-weight: 700; transition: 0.3s; box-shadow: inset 0 2px 4px rgba(0,0,0,0.02);}
    .search-box input:focus { border-color: #2563eb; background: var(--bg-surface); box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.15); }
    .search-box input::placeholder { font-weight: 600; color: var(--text-muted); }
    
    /* =========================================================
       3. TABEL DATA (ANALYTICAL STYLE)
       ========================================================= */
    .table-responsive { width: 100%; overflow-x: auto; min-height: 400px;}
    table { width: 100%; border-collapse: collapse; white-space: nowrap; }
    th { text-align: center; padding: 16px 20px; font-size: 11px; font-weight: 900; text-transform: uppercase; color: var(--text-muted); border-bottom: 2px solid var(--border-subtle); border-right: 1px solid var(--border-subtle); background: var(--bg-base); letter-spacing: 0.5px;}
    td { text-align: center; padding: 16px 20px; border-bottom: 1px dashed var(--border-subtle); border-right: 1px solid var(--border-subtle); color: var(--text-main); font-size: 14px; font-weight: 600; vertical-align: middle; transition: 0.2s;}
    
    th:first-child, td:first-child { text-align: left; }
    th:nth-child(2), td:nth-child(2) { text-align: left; }
    th:last-child, td:last-child { border-right: none; text-align: right;}
    tr:last-child td { border-bottom: none; }
    tr:hover td { background: rgba(37, 99, 235, 0.02); }
    html.dark tr:hover td { background: rgba(37, 99, 235, 0.05); }

    /* --- PROFIL KARYAWAN --- */
    .emp-profile { display: flex; align-items: center; gap: 15px; }
    .emp-avatar { width: 46px; height: 46px; border-radius: 12px; background: linear-gradient(135deg, #2563eb, #1e40af); color: #ffffff; display: flex; align-items: center; justify-content: center; font-weight: 900; font-size: 18px; box-shadow: 0 4px 10px rgba(37, 99, 235, 0.3); flex-shrink: 0; border: 2px solid rgba(255,255,255,0.2);}
    .emp-avatar-inactive { background: var(--bg-base); border: 2px dashed var(--border-subtle); color: var(--text-muted); box-shadow: none;}
    
    .emp-name { font-size: 15px; font-weight: 900; margin: 0 0 4px 0; color: var(--text-main); letter-spacing: -0.5px;}
    .emp-nik { font-family: 'Space Mono', monospace; font-weight: 800; color: #2563eb; font-size: 12px; margin-bottom: 6px; background: rgba(37, 99, 235, 0.08); padding: 2px 6px; border-radius: 6px; display: inline-block;}
    .emp-meta { font-size: 11px; color: var(--text-muted); font-weight: 700; display: flex; align-items: center; gap: 4px;}

    /* --- BADGE & DEPT --- */
    .emp-position { font-weight: 900; font-size: 14px; color: var(--text-main); margin-bottom: 6px;}
    .emp-dept { font-size: 12px; font-weight: 700; color: var(--text-muted); display: flex; align-items: center; gap: 6px; margin-bottom: 10px;}
    .badge { padding: 4px 10px; border-radius: 6px; font-size: 10px; font-weight: 900; display: inline-flex; align-items: center; gap: 4px; border: 1px solid transparent; text-transform: uppercase; letter-spacing: 0.5px;}
    
    /* --- IOT CAPSULE --- */
    .iot-capsule { display: inline-flex; align-items: center; background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: 10px; overflow: hidden; box-shadow: 0 2px 5px rgba(0,0,0,0.02);}
    .iot-section { padding: 8px 12px; display: flex; align-items: center; justify-content: center; gap: 6px; font-size: 12px; font-family: 'Space Mono', monospace; font-weight: 800; position: relative;}
    .iot-section:not(:last-child)::after { content: ''; position: absolute; right: 0; top: 20%; bottom: 20%; width: 1px; background: var(--border-subtle);}

    /* --- AKSI MANAJEMEN --- */
    .action-group { display: inline-flex; gap: 6px; background: var(--bg-base); border: 1px solid var(--border-subtle); padding: 4px; border-radius: 12px;}
    .btn-icon { width: 34px; height: 34px; border-radius: 8px; border: none; background: transparent; color: var(--text-muted); cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 18px; transition: 0.3s; text-decoration: none;}
    .btn-icon:hover { background: var(--bg-surface); color: var(--text-main); box-shadow: 0 4px 10px rgba(0,0,0,0.08); transform: translateY(-2px);}
</style>

<div class="page-header">
    <div class="page-title">
        <div class="title-icon"><i class="ph-fill ph-users-three"></i></div>
        <div>
            <h1>Data Induk Karyawan</h1>
            <p>Manajemen database SDM, tipe penggajian, dan sinkronisasi biometrik (IoT).</p>
        </div>
    </div>
    
    <div>
        <a href="<?= base_url('/employee/create') ?>" class="btn-primary">
            <i class="ph-bold ph-user-plus" style="font-size: 20px;"></i> Registrasi Pekerja Baru
        </a>
    </div>
</div>

<div class="table-card">
    
    <div class="table-controls">
        <div class="search-box">
            <i class="ph-bold ph-magnifying-glass"></i>
            <input type="text" id="searchInput" placeholder="Cari NIK, Nama, atau Posisi Jabatan..." onkeyup="filterTable()">
        </div>
        <div style="font-size: 13px; font-weight: 800; color: var(--text-muted);">
            Total: <span style="color: var(--text-main); font-family: 'Space Mono', monospace; font-size: 16px;"><?= count($employees) ?></span> Karyawan
        </div>
    </div>

    <div class="table-responsive">
        <table id="employeeTable">
            <thead>
                <tr>
                    <th>Profil Karyawan</th>
                    <th>Posisi & Departemen</th>
                    <th style="text-align: center;">Integrasi Mesin Absen (IoT)</th>
                    <th style="text-align: right;">Aksi Manajemen</th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($employees)): ?>
                    <tr>
                        <td colspan="4" style="text-align:center; padding: 80px 20px;">
                            <div style="background: var(--bg-base); width: 88px; height: 88px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; border: 2px dashed var(--border-subtle);">
                                <i class="ph-fill ph-users-slash" style="font-size: 44px; color: var(--text-muted);"></i>
                            </div>
                            <div style="color: var(--text-main); font-weight: 900; font-size: 18px; margin-bottom: 5px;">Belum Ada Data Karyawan</div>
                            <p style="color: var(--text-muted); font-size: 14px; font-weight: 500;">Mulai bangun database SDM Anda dengan mendaftarkan pekerja baru.</p>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach($employees as $emp): ?>
                    <tr class="emp-row" style="<?= ($emp['is_active'] == 0) ? 'background: rgba(0,0,0,0.02); opacity: 0.8;' : '' ?>">
                        
                        <td>
                            <div class="emp-profile">
                                <div class="emp-avatar <?= ($emp['is_active'] == 0) ? 'emp-avatar-inactive' : '' ?>">
                                    <?= strtoupper(substr($emp['name'], 0, 1)) ?>
                                </div>
                                <div>
                                    <h4 class="emp-name <?= ($emp['is_active'] == 0) ? 'text-decoration: line-through;' : '' ?>">
                                        <?= esc($emp['name']) ?>
                                    </h4>
                                    <div class="emp-nik">
                                        <i class="ph-bold ph-identification-card"></i> <?= esc($emp['employee_id']) ?>
                                    </div>
                                    
                                    <?php 
                                        $joinTime = strtotime($emp['join_date']);
                                        $displayDate = ($joinTime && $joinTime > 0 && $emp['join_date'] !== '0000-00-00') ? date('d M Y', $joinTime) : 'Belum diatur';
                                    ?>
                                    <div class="emp-meta">
                                        <i class="ph-bold ph-calendar-plus"></i> Masuk: <?= $displayDate ?>
                                    </div>
                                </div>
                            </div>
                        </td>
                        
                        <td>
                            <div>
                                <div class="emp-position">
                                    <?= esc($emp['position']) ?>
                                </div>
                                <div class="emp-dept">
                                    <i class="ph-fill ph-briefcase" style="color: var(--border-subtle); font-size: 16px;"></i> <?= esc($emp['department']) ?>
                                </div>
                                
                                <?php if($emp['is_active'] == 1): ?>
                                    <?php 
                                        $badgeColor = '#10b981'; $badgeBg = 'rgba(16, 185, 129, 0.1)'; $badgeBorder = 'rgba(16, 185, 129, 0.2)'; $icon='ph-money';
                                        if(isset($emp['salary_type']) && $emp['salary_type'] == 'Mingguan') { $badgeColor = '#3b82f6'; $badgeBg = 'rgba(59, 130, 246, 0.1)'; $badgeBorder = 'rgba(59, 130, 246, 0.2)'; $icon='ph-calendar-check';}
                                        if(isset($emp['salary_type']) && $emp['salary_type'] == 'Harian') { $badgeColor = '#f59e0b'; $badgeBg = 'rgba(245, 158, 11, 0.1)'; $badgeBorder = 'rgba(245, 158, 11, 0.2)'; $icon='ph-clock';}
                                    ?>
                                    <span class="badge" style="background: <?= $badgeBg ?>; color: <?= $badgeColor ?>; border-color: <?= $badgeBorder ?>;">
                                        <i class="ph-bold <?= $icon ?>"></i> Gaji <?= esc($emp['salary_type'] ?? 'TETAP') ?>
                                    </span>
                                <?php else: ?>
                                    <span class="badge" style="background: rgba(239, 68, 68, 0.1); color: #ef4444; border-color: rgba(239, 68, 68, 0.2);">
                                        <i class="ph-bold ph-user-minus"></i> NON-AKTIF / RESIGN
                                    </span>
                                <?php endif; ?>
                            </div>
                        </td>

                        <td style="text-align: center;">
                            <?php if($emp['is_active'] == 1): ?>
                                <div class="iot-capsule">
                                    <div class="iot-section" style="<?= $emp['pin'] ? 'color: #10b981; background: rgba(16,185,129,0.05);' : 'color: var(--text-muted);' ?>" title="ID Mesin Fingerspot">
                                        <i class="ph-bold ph-hash"></i> <?= $emp['pin'] ? esc($emp['pin']) : 'N/A' ?>
                                    </div>
                                    <div class="iot-section" style="<?= ($emp['finger_count'] ?? 0) > 0 ? 'color: #3b82f6; background: rgba(59,130,246,0.05);' : 'color: var(--text-muted);' ?>" title="<?= $emp['finger_count'] ?? 0 ?> Data Sidik Jari">
                                        <i class="ph-bold ph-fingerprint"></i> <?= $emp['finger_count'] ?? 0 ?>
                                    </div>
                                    <div class="iot-section" style="<?= ($emp['face_count'] ?? 0) > 0 ? 'color: #8b5cf6; background: rgba(139,92,246,0.05);' : 'color: var(--text-muted);' ?>" title="<?= $emp['face_count'] ?? 0 ?> Data Wajah">
                                        <i class="ph-bold ph-bounding-box"></i> <?= $emp['face_count'] ?? 0 ?>
                                    </div>
                                </div>
                            <?php else: ?>
                                <span style="font-size: 12px; font-weight: 800; color: var(--text-muted); font-style: italic; background: var(--bg-base); padding: 6px 12px; border-radius: 8px; border: 1px solid var(--border-subtle);">
                                    <i class="ph-fill ph-plug-x"></i> Akses Dicabut
                                </span>
                            <?php endif; ?>
                        </td>

                        <td style="text-align: right;">
                            <div style="display: flex; gap: 12px; justify-content: flex-end;">
                                
                                <?php if($emp['is_active'] == 1): ?>
                                <div class="action-group">
                                    <button type="button" class="btn-icon" style="color: #10b981;" title="Daftarkan ke Mesin Fisik" onclick="confirmPush('<?= base_url('/employee/push_to_machine/' . $emp['id']) ?>', '<?= esc($emp['name']) ?>')">
                                        <i class="ph-bold ph-cloud-arrow-up"></i>
                                    </button>
                                    <?php if($emp['pin']): ?>
                                        <a href="<?= base_url('/employee/sync_biometric/' . $emp['pin']) ?>" class="btn-icon" style="color: #3b82f6;" title="Tarik Update Biometrik">
                                            <i class="ph-bold ph-arrows-clockwise"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                                <?php endif; ?>

                                <div class="action-group">
                                    <?php if($emp['is_active'] == 1): ?>
                                        <a href="<?= base_url('/employee/edit/' . $emp['id']) ?>" class="btn-icon" style="color: #f59e0b;" title="Edit Profil Karyawan">
                                            <i class="ph-bold ph-pencil-simple"></i>
                                        </a>
                                        <form action="<?= base_url('/employee/deactivate/' . $emp['id']) ?>" method="post" style="display:inline;" id="deact-form-<?= $emp['id'] ?>">
                                            <?= csrf_field() ?>
                                            <button type="button" class="btn-icon" style="color: #71717a;" title="Proses Resign / Non-Aktif" onclick="confirmDeactivate(<?= $emp['id'] ?>, '<?= esc($emp['name']) ?>')">
                                                <i class="ph-bold ph-user-minus"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                    
                                    <form action="<?= base_url('/employee/delete/' . $emp['id']) ?>" method="post" style="display:inline;" id="delete-form-<?= $emp['id'] ?>">
                                        <?= csrf_field() ?>
                                        <button type="button" class="btn-icon" style="color: #ef4444;" title="Hapus Permanen" onclick="confirmDelete(<?= $emp['id'] ?>, '<?= esc($emp['name']) ?>')">
                                            <i class="ph-bold ph-trash"></i>
                                        </button>
                                    </form>
                                </div>

                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function filterTable() {
    let input = document.getElementById("searchInput");
    let filter = input.value.toLowerCase();
    let table = document.getElementById("employeeTable");
    let tr = table.getElementsByClassName("emp-row");

    for (let i = 0; i < tr.length; i++) {
        let name = tr[i].querySelector(".emp-name").textContent || tr[i].querySelector(".emp-name").innerText;
        let nik = tr[i].querySelector(".emp-nik").textContent || tr[i].querySelector(".emp-nik").innerText;
        let position = tr[i].querySelector(".emp-position").textContent || tr[i].querySelector(".emp-position").innerText;
        
        if (name.toLowerCase().indexOf(filter) > -1 || nik.toLowerCase().indexOf(filter) > -1 || position.toLowerCase().indexOf(filter) > -1) {
            tr[i].style.display = "";
        } else {
            tr[i].style.display = "none";
        }
    }
}
</script>

<?= $this->endSection() ?>