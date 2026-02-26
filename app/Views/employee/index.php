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
            html: `Apakah Anda yakin ingin menghapus data <b>${name}</b> dari database?<br><br><span style="color:#ef4444; font-size:12px;">Peringatan: Tindakan ini juga akan menghapus akses login ESS dan seluruh riwayat absensinya.</span>`,
            icon: 'warning', showCancelButton: true, confirmButtonColor: '#ef4444', cancelButtonColor: '#71717a', confirmButtonText: 'Ya, Hapus!', cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) document.getElementById('delete-form-' + id).submit();
        });
    }

    function confirmDeactivate(id, name) {
        Swal.fire({
            title: 'Proses Karyawan Keluar?', 
            html: `Anda akan menonaktifkan <b>${name}</b>.<br><br>Status karyawan akan menjadi <b>NON-AKTIF</b> dan hak akses loginnya akan dicabut, namun riwayat slip gaji akan tetap tersimpan aman.`,
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

    // FUNGSI BARU: Konfirmasi Rekam Jari
    function confirmRegister(url, name) {
        Swal.fire({
            title: 'Aktifkan Mode Rekam Jari?', 
            html: `Mesin absensi fisik akan otomatis menyala dan masuk ke mode perekaman sidik jari untuk <b>${name}</b>.<br><br><span style="color:var(--text-muted); font-size:12px;">Pastikan karyawan sudah berada di depan mesin sebelum Anda melanjutkan.</span>`,
            icon: 'info', showCancelButton: true, confirmButtonColor: '#a855f7', cancelButtonColor: '#71717a', confirmButtonText: 'Ya, Aktifkan Mesin', cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) window.location.href = url;
        });
    }
</script>

<style>
    /* UI Structure */
    .module-header { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 30px; flex-wrap: wrap; gap: 20px; }
    .module-title h1 { font-size: 28px; font-weight: 800; color: var(--text-main); margin-bottom: 5px; letter-spacing: -1px; }
    .module-title p { color: var(--text-muted); font-size: 14px; }
    
    .btn-primary { background: var(--accent-main); color: #fff; border: none; padding: 12px 24px; border-radius: 12px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 8px; transition: 0.3s; box-shadow: 0 4px 12px var(--accent-light); }
    .btn-primary:hover { transform: translateY(-2px); filter: brightness(1.1); }
    
    .table-card { background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: 20px; box-shadow: var(--shadow-card); overflow: hidden; }
    .table-controls { padding: 20px 24px; border-bottom: 1px solid var(--border-subtle); display: flex; justify-content: space-between; align-items: center; gap: 15px; flex-wrap: wrap; }
    
    .search-box { position: relative; width: 100%; max-width: 350px; }
    .search-box i { position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: var(--text-muted); }
    .search-box input { width: 100%; background: var(--bg-base); border: 1px solid var(--border-subtle); padding: 10px 15px 10px 40px; border-radius: 10px; color: var(--text-main); outline: none; font-family: inherit; font-size: 13px; }
    .search-box input:focus { border-color: var(--accent-main); }
    
    .table-responsive { width: 100%; overflow-x: auto; }
    table { width: 100%; border-collapse: collapse; white-space: nowrap; }
    th { text-align: left; padding: 15px 24px; font-size: 11px; font-weight: 700; text-transform: uppercase; color: var(--text-muted); border-bottom: 1px solid var(--border-subtle); background: var(--bg-base); }
    td { padding: 16px 24px; border-bottom: 1px solid var(--border-subtle); color: var(--text-main); font-size: 14px; }
    tr:hover td { background: rgba(0,0,0,0.01); }
    html.dark tr:hover td { background: rgba(255,255,255,0.02); }

    .emp-profile { display: flex; align-items: center; gap: 12px; }
    .emp-avatar { width: 36px; height: 36px; border-radius: 10px; background: var(--border-subtle); display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 14px; }
    .badge { padding: 4px 10px; border-radius: 100px; font-size: 11px; font-weight: 700; display: inline-flex; align-items: center; gap: 4px; }
    
    .action-btns { display: flex; gap: 8px; justify-content: flex-end; }
    .btn-icon { width: 34px; height: 34px; border-radius: 8px; border: none; background: transparent; color: var(--text-muted); cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 18px; transition: 0.2s; text-decoration: none; }
    .btn-icon:hover { background: var(--border-subtle); color: var(--text-main); }
</style>

<div class="module-header">
    <div class="module-title">
        <h1>Data Karyawan & IoT</h1>
        <p>Kelola data induk SDM dan sinkronisasikan akses biometrik dengan mesin absen pabrik.</p>
    </div>
    
    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
        <button class="btn-primary" onclick="window.location.href='<?= base_url('/employee/create') ?>'">
            <i class="ph ph-plus-circle"></i> Tambah Karyawan
        </button>
    </div>
</div>

<div class="table-card">
    <div class="table-controls">
        <div class="search-box">
            <i class="ph ph-magnifying-glass"></i>
            <input type="text" placeholder="Cari NIK, Nama, atau Departemen...">
        </div>
        
        <div style="display: flex; gap: 15px; font-size: 12px; color: var(--text-muted); font-weight: 600;">
            <div style="display: flex; align-items: center; gap: 5px;"><i class="ph ph-cloud-arrow-up" style="color: #10b981; font-size: 16px;"></i> Push Mesin</div>
            <div style="display: flex; align-items: center; gap: 5px;"><i class="ph ph-fingerprint" style="color: #a855f7; font-size: 16px;"></i> Rekam Jari</div>
            <div style="display: flex; align-items: center; gap: 5px;"><i class="ph ph-arrows-clockwise" style="color: #38bdf8; font-size: 16px;"></i> Sync Data</div>
        </div>
    </div>

    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Informasi Karyawan</th>
                    <th>Identitas (NIK & PIN)</th>
                    <th>Divisi & Jabatan</th>
                    <th>Status Pekerja</th>
                    <th>Status Biometrik</th>
                    <th style="text-align: right;">Aksi Manajemen</th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($employees)): ?>
                    <tr>
                        <td colspan="6" style="text-align:center; padding: 60px 20px;">
                            <i class="ph ph-users-slash" style="font-size: 48px; color: var(--border-subtle); margin-bottom: 10px; display: block;"></i>
                            <div style="color: var(--text-muted); font-weight: 600;">Belum ada data karyawan. Mulai dengan meregistrasi pekerja baru.</div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach($employees as $emp): ?>
                    <tr style="<?= ($emp['is_active'] == 0) ? 'opacity: 0.5; background: rgba(0,0,0,0.02);' : '' ?>">
                        
                        <td>
                            <div class="emp-profile">
                                <div class="emp-avatar" style="<?= ($emp['is_active'] == 0) ? 'background: transparent; border: 1px solid var(--border-subtle);' : '' ?>">
                                    <?= strtoupper(substr($emp['name'], 0, 1)) ?>
                                </div>
                                <div>
                                    <h4 style="font-size:14px; font-weight:700; margin:0; <?= ($emp['is_active'] == 0) ? 'text-decoration: line-through;' : '' ?>">
                                        <?= esc($emp['name']) ?>
                                    </h4>
                                    <p style="font-size:11px; margin:0; color:var(--text-muted); margin-top:2px;">
                                        <i class="ph ph-calendar-blank"></i> Bergabung: <?= date('d M Y', strtotime($emp['join_date'])) ?>
                                    </p>
                                </div>
                            </div>
                        </td>
                        
                        <td>
                            <div style="font-family: monospace; font-weight: 800; color: var(--text-main); font-size: 13px;"><?= esc($emp['employee_id']) ?></div>
                            <div style="font-size: 11px; font-weight: 600; color: var(--text-muted); margin-top: 4px; display: flex; align-items: center; gap: 5px;">
                                ID Mesin: 
                                <?php if($emp['pin']): ?>
                                    <span style="background: rgba(16, 185, 129, 0.1); color: #10b981; padding: 2px 6px; border-radius: 4px; font-family: monospace; font-weight: 800;">#<?= esc($emp['pin']) ?></span>
                                <?php else: ?>
                                    <span style="background: var(--border-subtle); padding: 2px 6px; border-radius: 4px; color: var(--text-main);">Belum Di-Push</span>
                                <?php endif; ?>
                            </div>
                        </td>

                        <td>
                            <div style="font-weight:700; font-size:13px;"><?= esc($emp['position']) ?></div>
                            <div style="font-size:11px; color:var(--text-muted); margin-top: 2px;"><?= esc($emp['department']) ?></div>
                        </td>

                        <td>
                            <?php if($emp['is_active'] == 1): ?>
                                <?php 
                                    $badgeColor = '#10b981'; $badgeBg = 'rgba(16, 185, 129, 0.1)';
                                    if($emp['status'] == 'Kontrak') { $badgeColor = '#38bdf8'; $badgeBg = 'rgba(56, 189, 248, 0.1)'; }
                                    if($emp['status'] == 'Magang') { $badgeColor = '#f59e0b'; $badgeBg = 'rgba(245, 158, 11, 0.1)'; }
                                ?>
                                <span class="badge" style="background: <?= $badgeBg ?>; color: <?= $badgeColor ?>;"><?= esc($emp['status']) ?></span>
                            <?php else: ?>
                                <span class="badge" style="background: rgba(239, 68, 68, 0.1); color: #ef4444;"><i class="ph ph-user-minus"></i> RESIGN</span>
                            <?php endif; ?>
                        </td>

                        <td>
                            <div style="display: flex; gap: 8px;">
                                <div title="<?= $emp['finger_count'] ?> Jari Terdaftar" style="width: 30px; height: 30px; border-radius: 8px; display: flex; align-items: center; justify-content: center; <?= $emp['finger_count'] > 0 ? 'background: rgba(16, 185, 129, 0.1); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.2);' : 'background: var(--bg-base); color: var(--border-subtle); border: 1px dashed var(--border-subtle);' ?>">
                                    <i class="ph ph-fingerprint"></i>
                                </div>
                                <div title="<?= $emp['face_count'] ?> Wajah Terdaftar" style="width: 30px; height: 30px; border-radius: 8px; display: flex; align-items: center; justify-content: center; <?= $emp['face_count'] > 0 ? 'background: rgba(56, 189, 248, 0.1); color: #38bdf8; border: 1px solid rgba(56, 189, 248, 0.2);' : 'background: var(--bg-base); color: var(--border-subtle); border: 1px dashed var(--border-subtle);' ?>">
                                    <i class="ph ph-bounding-box"></i>
                                </div>
                            </div>
                        </td>

                        <td>
                            <div class="action-btns">
                                <?php if($emp['is_active'] == 1): ?>
                                    
                                    <button type="button" class="btn-icon" style="color: #10b981; background: rgba(16, 185, 129, 0.1);" title="Daftarkan ke Mesin Absen (IoT)" onclick="confirmPush('<?= base_url('/employee/push_to_machine/' . $emp['id']) ?>', '<?= esc($emp['name']) ?>')">
                                        <i class="ph ph-cloud-arrow-up"></i>
                                    </button>

                                    <?php if($emp['pin']): ?>
                                        <button type="button" class="btn-icon" style="color: #a855f7; background: rgba(168, 85, 247, 0.1);" title="Aktifkan Mode Rekam Jari di Mesin" onclick="confirmRegister('<?= base_url('/employee/trigger_register_online/' . $emp['pin']) ?>', '<?= esc($emp['name']) ?>')">
                                            <i class="ph ph-fingerprint"></i>
                                        </button>
                                        <a href="<?= base_url('/employee/sync_biometric/' . $emp['pin']) ?>" class="btn-icon" style="color: #38bdf8; background: rgba(56, 189, 248, 0.1);" title="Tarik Data Jari dari Mesin (Sync)">
                                            <i class="ph ph-arrows-clockwise"></i>
                                        </a>
                                    <?php endif; ?>

                                    <a href="<?= base_url('/employee/edit/' . $emp['id']) ?>" class="btn-icon" title="Edit Biodata" style="background: var(--bg-base); border: 1px solid var(--border-subtle);">
                                        <i class="ph ph-pencil-simple"></i>
                                    </a>
                                    
                                    <form action="<?= base_url('/employee/deactivate/' . $emp['id']) ?>" method="post" id="deact-form-<?= $emp['id'] ?>" style="display:inline;">
                                        <?= csrf_field() ?>
                                        <button type="button" class="btn-icon" style="color: #f59e0b; background: rgba(245, 158, 11, 0.1);" title="Proses Resign Karyawan" onclick="confirmDeactivate(<?= $emp['id'] ?>, '<?= esc($emp['name']) ?>')">
                                            <i class="ph ph-user-minus"></i>
                                        </button>
                                    </form>
                                <?php endif; ?>

                                <form action="<?= base_url('/employee/delete/' . $emp['id']) ?>" method="post" id="delete-form-<?= $emp['id'] ?>" style="display:inline;">
                                    <?= csrf_field() ?>
                                    <button type="button" class="btn-icon" style="color: #ef4444; background: rgba(239, 68, 68, 0.1);" title="Hapus Data Fisik dari Server" onclick="confirmDelete(<?= $emp['id'] ?>, '<?= esc($emp['name']) ?>')">
                                        <i class="ph ph-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?= $this->endSection() ?>