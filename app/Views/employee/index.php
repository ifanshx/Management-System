<?= $this->extend('layout/template') ?>
<?= $this->section('content') ?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const isDark = document.documentElement.classList.contains('dark');
        const bgColor = isDark ? '#18181b' : '#ffffff';
        const textColor = isDark ? '#f4f4f5' : '#09090b';

        <?php if(session()->getFlashdata('success')): ?>
            Swal.fire({
                icon: 'success', title: 'Berhasil!', html: '<?= session()->getFlashdata('success') ?>',
                confirmButtonColor: '#38bdf8', background: bgColor, color: textColor,
            });
        <?php endif; ?>

        <?php if(session()->getFlashdata('error')): ?>
            Swal.fire({
                icon: 'error', title: 'Sistem Terkendala', text: '<?= session()->getFlashdata('error') ?>',
                confirmButtonColor: '#ef4444', background: bgColor, color: textColor,
            });
        <?php endif; ?>
    });

    function confirmDelete(id, name) {
        Swal.fire({
            title: 'Hapus Data Permanen?', 
            html: `Apakah Anda yakin ingin menghapus data <b>${name}</b> dari database?<br><br><span style="color:#ef4444; font-size:12px;">Peringatan: Tindakan ini juga akan menghapus akses login portal dan seluruh riwayat absensinya secara permanen.</span>`,
            icon: 'warning', showCancelButton: true, confirmButtonColor: '#ef4444', cancelButtonColor: '#71717a', confirmButtonText: 'Ya, Hapus!', cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) document.getElementById('delete-form-' + id).submit();
        });
    }

    function confirmDeactivate(id, name) {
        Swal.fire({
            title: 'Proses Karyawan Keluar?', 
            html: `Anda akan menonaktifkan <b>${name}</b>.<br><br>Status karyawan akan menjadi <b>NON-AKTIF</b> dan hak akses login portalnya akan dicabut, namun riwayat slip gaji akan tetap tersimpan.`,
            icon: 'warning', showCancelButton: true, confirmButtonColor: '#f59e0b', cancelButtonColor: '#71717a', confirmButtonText: 'Ya, Proses Resign!', cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) document.getElementById('deact-form-' + id).submit();
        });
    }

    function confirmPush(url, name) {
        Swal.fire({
            title: 'Daftarkan ke Mesin Absen?', 
            text: `Sistem akan mengirimkan identitas ${name} ke mesin fisik pabrik. Lanjutkan?`,
            icon: 'info', showCancelButton: true, confirmButtonColor: '#10b981', cancelButtonColor: '#71717a', confirmButtonText: 'Ya, Kirim Data', cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) window.location.href = url;
        });
    }
</script>

<style>
    /* --- HEADER UTAMA --- */
    .page-header { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 30px; flex-wrap: wrap; gap: 20px; }
    .page-title h1 { font-size: 26px; font-weight: 800; color: var(--text-main); margin-bottom: 6px; letter-spacing: -0.5px; }
    .page-title p { color: var(--text-muted); font-size: 13px; margin: 0; }
    
    .btn-primary { background: var(--accent-main); color: #fff; border: none; padding: 12px 24px; border-radius: 12px; font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 8px; transition: 0.3s; box-shadow: 0 4px 12px var(--accent-light); text-decoration: none; font-size: 14px;}
    .btn-primary:hover { transform: translateY(-2px); filter: brightness(1.1); box-shadow: 0 6px 15px var(--accent-light);}
    
    /* --- BENTO CARD & CONTROLS --- */
    .table-card { background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: 20px; box-shadow: var(--shadow-card); overflow: hidden; }
    .table-controls { padding: 20px 25px; border-bottom: 1px dashed var(--border-subtle); display: flex; justify-content: space-between; align-items: center; gap: 15px; flex-wrap: wrap; background: var(--bg-base); }
    
    /* --- SEARCH BOX --- */
    .search-box { position: relative; width: 100%; max-width: 380px; }
    .search-box i { position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: var(--text-muted); font-size: 18px;}
    .search-box input { width: 100%; background: var(--bg-surface); border: 1px solid var(--border-subtle); padding: 12px 16px 12px 44px; border-radius: 12px; color: var(--text-main); outline: none; font-family: inherit; font-size: 13px; transition: 0.2s; font-weight: 500;}
    .search-box input:focus { border-color: var(--accent-main); box-shadow: 0 0 0 3px var(--accent-light); }
    
    /* --- TABEL DATA --- */
    .table-responsive { width: 100%; overflow-x: auto; min-height: 400px;}
    table { width: 100%; border-collapse: collapse; white-space: nowrap; }
    th { text-align: left; padding: 16px 25px; font-size: 11px; font-weight: 800; text-transform: uppercase; color: var(--text-muted); border-bottom: 1px solid var(--border-subtle); background: var(--bg-surface); letter-spacing: 0.5px;}
    td { padding: 16px 25px; border-bottom: 1px solid var(--border-subtle); color: var(--text-main); font-size: 13px; font-weight: 500; vertical-align: middle;}
    tr:hover td { background: var(--bg-base); }

    /* --- PROFIL KARYAWAN --- */
    .emp-profile { display: flex; align-items: center; gap: 16px; }
    .emp-avatar { width: 44px; height: 44px; border-radius: 12px; background: linear-gradient(135deg, var(--accent-main) 0%, #1e40af 100%); color: #ffffff; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 18px; box-shadow: 0 4px 10px var(--accent-light); flex-shrink: 0;}
    .emp-avatar-inactive { background: var(--bg-base); border: 1px dashed var(--border-subtle); color: var(--text-muted); box-shadow: none;}
    
    .emp-name { font-size: 14px; font-weight: 800; margin: 0 0 4px 0; color: var(--text-main); }
    .emp-nik { font-family: 'Space Mono', monospace; font-weight: 700; color: var(--accent-main); font-size: 12px; margin-bottom: 4px; }
    .emp-meta { font-size: 11px; color: var(--text-muted); font-weight: 600; display: flex; align-items: center; gap: 4px;}

    /* --- BADGE & DEPT --- */
    .emp-position { font-weight: 800; font-size: 13px; color: var(--text-main); margin-bottom: 4px;}
    .emp-dept { font-size: 11px; font-weight: 600; color: var(--text-muted); display: flex; align-items: center; gap: 4px; margin-bottom: 8px;}
    .badge { padding: 4px 10px; border-radius: 6px; font-size: 10px; font-weight: 800; display: inline-block; border: 1px solid transparent; text-transform: uppercase; letter-spacing: 0.5px;}
    
    /* --- IOT CAPSULE --- */
    .iot-capsule { display: inline-flex; align-items: center; background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: 8px; overflow: hidden;}
    .iot-section { padding: 6px 12px; display: flex; align-items: center; gap: 6px; font-size: 12px; font-family: 'Space Mono', monospace; font-weight: 700; position: relative;}
    .iot-section:not(:last-child)::after { content: ''; position: absolute; right: 0; top: 20%; bottom: 20%; width: 1px; background: var(--border-subtle);}

    /* --- AKSI MANAJEMEN --- */
    .action-group { display: inline-flex; gap: 6px; background: var(--bg-base); border: 1px solid var(--border-subtle); padding: 5px; border-radius: 10px;}
    .btn-icon { width: 32px; height: 32px; border-radius: 8px; border: none; background: transparent; color: var(--text-muted); cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 16px; transition: 0.2s; text-decoration: none;}
    .btn-icon:hover { background: var(--bg-surface); color: var(--text-main); box-shadow: 0 2px 6px rgba(0,0,0,0.08); }
    html.dark .btn-icon:hover { filter: brightness(1.2); }
</style>

<div class="page-header">
    <div class="page-title">
        <h1>Data Induk Karyawan</h1>
        <p>Manajemen database SDM, tipe penggajian, dan sinkronisasi biometrik (IoT).</p>
    </div>
    
    <div>
        <a href="<?= base_url('/employee/create') ?>" class="btn-primary">
            <i class="ph ph-user-plus" style="font-size: 18px;"></i> Pekerja Baru
        </a>
    </div>
</div>

<div class="table-card">
    
    <div class="table-controls">
        <div class="search-box">
            <i class="ph ph-magnifying-glass"></i>
            <input type="text" id="searchInput" placeholder="Cari NIK, Nama, atau Departemen..." onkeyup="filterTable()">
        </div>
    </div>

    <div class="table-responsive">
        <table id="employeeTable">
            <thead>
                <tr>
                    <th>Profil Karyawan</th>
                    <th>Posisi & Penugasan</th>
                    <th style="text-align: center;">Integrasi Mesin (IoT)</th>
                    <th style="text-align: right;">Aksi Manajemen</th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($employees)): ?>
                    <tr>
                        <td colspan="4" style="text-align:center; padding: 100px 20px;">
                            <div style="background: var(--bg-base); width: 80px; height: 80px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 15px;">
                                <i class="ph ph-users-slash" style="font-size: 40px; color: var(--text-muted);"></i>
                            </div>
                            <div style="color: var(--text-main); font-weight: 800; font-size: 16px;">Belum ada data karyawan</div>
                            <p style="color: var(--text-muted); font-size: 13px; margin-top: 5px;">Mulai bangun database SDM Anda dengan mendaftarkan pekerja baru.</p>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach($employees as $emp): ?>
                    <tr class="emp-row" style="<?= ($emp['is_active'] == 0) ? 'background: rgba(0,0,0,0.02);' : '' ?>">
                        
                        <td>
                            <div class="emp-profile">
                                <div class="emp-avatar <?= ($emp['is_active'] == 0) ? 'emp-avatar-inactive' : '' ?>">
                                    <?= strtoupper(substr($emp['name'], 0, 1)) ?>
                                </div>
                                <div style="<?= ($emp['is_active'] == 0) ? 'opacity: 0.5;' : '' ?>">
                                    <h4 class="emp-name <?= ($emp['is_active'] == 0) ? 'text-decoration: line-through;' : '' ?>">
                                        <?= esc($emp['name']) ?>
                                    </h4>
                                    <div class="emp-nik">
                                        <?= esc($emp['employee_id']) ?>
                                    </div>
                                    
                                    <?php 
                                        $joinTime = strtotime($emp['join_date']);
                                        $displayDate = ($joinTime && $joinTime > 0 && $emp['join_date'] !== '0000-00-00') ? date('d M Y', $joinTime) : 'Belum diatur';
                                    ?>
                                    <div class="emp-meta">
                                        <i class="ph ph-calendar-blank"></i> Masuk: <?= $displayDate ?>
                                    </div>
                                </div>
                            </div>
                        </td>
                        
                        <td>
                            <div style="<?= ($emp['is_active'] == 0) ? 'opacity: 0.5;' : '' ?>">
                                <div class="emp-position">
                                    <?= esc($emp['position']) ?>
                                </div>
                                <div class="emp-dept">
                                    <i class="ph ph-briefcase"></i> <?= esc($emp['department']) ?>
                                </div>
                                
                                <?php if($emp['is_active'] == 1): ?>
                                    <?php 
                                        $badgeColor = '#10b981'; $badgeBg = 'rgba(16, 185, 129, 0.1)'; $badgeBorder = 'rgba(16, 185, 129, 0.2)';
                                        if(isset($emp['salary_type']) && $emp['salary_type'] == 'Mingguan') { $badgeColor = '#38bdf8'; $badgeBg = 'rgba(56, 189, 248, 0.1)'; $badgeBorder = 'rgba(56, 189, 248, 0.2)';}
                                        if(isset($emp['salary_type']) && $emp['salary_type'] == 'Harian') { $badgeColor = '#f59e0b'; $badgeBg = 'rgba(245, 158, 11, 0.1)'; $badgeBorder = 'rgba(245, 158, 11, 0.2)';}
                                    ?>
                                    <span class="badge" style="background: <?= $badgeBg ?>; color: <?= $badgeColor ?>; border-color: <?= $badgeBorder ?>;">
                                        <?= esc($emp['salary_type'] ?? 'TETAP') ?>
                                    </span>
                                <?php else: ?>
                                    <span class="badge" style="background: rgba(239, 68, 68, 0.1); color: #ef4444; border-color: rgba(239, 68, 68, 0.2);">
                                        <i class="ph ph-user-minus"></i> RESIGN
                                    </span>
                                <?php endif; ?>
                            </div>
                        </td>

                        <td style="text-align: center;">
                            <?php if($emp['is_active'] == 1): ?>
                                <div class="iot-capsule">
                                    <div class="iot-section" style="<?= $emp['pin'] ? 'color: #10b981; background: rgba(16,185,129,0.05);' : 'color: var(--text-muted);' ?>" title="ID Mesin">
                                        <i class="ph ph-hash"></i> <?= $emp['pin'] ? esc($emp['pin']) : 'N/A' ?>
                                    </div>
                                    <div class="iot-section" style="<?= ($emp['finger_count'] ?? 0) > 0 ? 'color: #38bdf8; background: rgba(56,189,248,0.05);' : 'color: var(--text-muted);' ?>" title="<?= $emp['finger_count'] ?? 0 ?> Sidik Jari">
                                        <i class="ph ph-fingerprint"></i> <?= $emp['finger_count'] ?? 0 ?>
                                    </div>
                                    <div class="iot-section" style="<?= ($emp['face_count'] ?? 0) > 0 ? 'color: #a855f7; background: rgba(168,85,247,0.05);' : 'color: var(--text-muted);' ?>" title="<?= $emp['face_count'] ?? 0 ?> Wajah">
                                        <i class="ph ph-bounding-box"></i> <?= $emp['face_count'] ?? 0 ?>
                                    </div>
                                </div>
                            <?php else: ?>
                                <span style="font-size: 11px; color: var(--text-muted); font-style: italic;">Akses Dicabut</span>
                            <?php endif; ?>
                        </td>

                        <td style="text-align: right;">
                            <div style="display: flex; gap: 10px; justify-content: flex-end;">
                                
                                <?php if($emp['is_active'] == 1): ?>
                                <div class="action-group">
                                    <button type="button" class="btn-icon" style="color: #10b981;" title="Kirim ke Mesin" onclick="confirmPush('<?= base_url('/employee/push_to_machine/' . $emp['id']) ?>', '<?= esc($emp['name']) ?>')">
                                        <i class="ph ph-cloud-arrow-up"></i>
                                    </button>
                                    <?php if($emp['pin']): ?>
                                        <a href="<?= base_url('/employee/sync_biometric/' . $emp['pin']) ?>" class="btn-icon" style="color: #38bdf8;" title="Tarik Data Biometrik">
                                            <i class="ph ph-arrows-clockwise"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                                <?php endif; ?>

                                <div class="action-group">
                                    <?php if($emp['is_active'] == 1): ?>
                                        <a href="<?= base_url('/employee/edit/' . $emp['id']) ?>" class="btn-icon" style="color: var(--accent-main);" title="Edit Profil">
                                            <i class="ph ph-pencil-simple"></i>
                                        </a>
                                        <form action="<?= base_url('/employee/deactivate/' . $emp['id']) ?>" method="post" style="display:inline;">
                                            <?= csrf_field() ?>
                                            <button type="button" class="btn-icon" style="color: #f59e0b;" title="Proses Resign" onclick="confirmDeactivate(<?= $emp['id'] ?>, '<?= esc($emp['name']) ?>')">
                                                <i class="ph ph-user-minus"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                    
                                    <form action="<?= base_url('/employee/delete/' . $emp['id']) ?>" method="post" style="display:inline;">
                                        <?= csrf_field() ?>
                                        <button type="button" class="btn-icon" style="color: #ef4444;" title="Hapus Permanen" onclick="confirmDelete(<?= $emp['id'] ?>, '<?= esc($emp['name']) ?>')">
                                            <i class="ph ph-trash"></i>
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
        let dept = tr[i].querySelector(".emp-dept").textContent || tr[i].querySelector(".emp-dept").innerText;
        
        if (name.toLowerCase().indexOf(filter) > -1 || nik.toLowerCase().indexOf(filter) > -1 || dept.toLowerCase().indexOf(filter) > -1) {
            tr[i].style.display = "";
        } else {
            tr[i].style.display = "none";
        }
    }
}
</script>

<?= $this->endSection() ?>