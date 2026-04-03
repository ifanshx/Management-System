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
            icon: 'success',
            title: 'Berhasil!',
            html: '<?= session()->getFlashdata('success') ?>',
            confirmButtonColor: '#10b981',
            background: bgColor,
            color: textColor,
            customClass: { popup: 'swal2-custom-radius' }
        }).then((result) => {
            let msg = '<?= strtolower(session()->getFlashdata('success')) ?>';
            if(msg.includes('sinkronisasi') || msg.includes('terkirim')) {
                Swal.fire({
                    title: 'Memuat Data...',
                    text: 'Sedang memuat ulang tabel dari database. Mohon tunggu.',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    background: bgColor, color: textColor,
                    didOpen: () => { Swal.showLoading(); }
                });
                setTimeout(() => { window.location.reload(); }, 1000);
            }
        });
    <?php endif; ?>

    <?php if(session()->getFlashdata('error')): ?>
        Swal.fire({
            icon: 'error',
            title: 'Gagal!',
            html: '<?= session()->getFlashdata('error') ?>',
            confirmButtonColor: '#ef4444',
            background: bgColor,
            color: textColor,
            customClass: { popup: 'swal2-custom-radius' }
        });
    <?php endif; ?>
});

function confirmDelete(id, name) {
    const isDark = document.documentElement.classList.contains('dark');
    Swal.fire({
        title: 'Hapus Data?',
        html: `Data <b>${name}</b> dan akun login terkait akan dihapus permanen.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: isDark ? '#3f3f46' : '#cbd5e1',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal',
        background: isDark ? '#18181b' : '#ffffff',
        color: isDark ? '#f4f4f5' : '#09090b',
        customClass: { popup: 'swal2-custom-radius' }
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('delete-form-' + id).submit();
        }
    });
}

function filterTable() {
    let input = document.getElementById("searchInput");
    let filter = input.value.toLowerCase();
    let rows = document.querySelectorAll("#employeeTable tbody tr.emp-row");

    rows.forEach(row => {
        let text = row.innerText.toLowerCase();
        row.style.display = text.includes(filter) ? "" : "none";
    });
}

function confirmDeactivate(id, name) {
    const isDark = document.documentElement.classList.contains('dark');
    Swal.fire({
        title: 'Proses Karyawan Keluar?', 
        html: `Status <b>${name}</b> akan menjadi <b style="color:#f59e0b;">NON-AKTIF</b>. Hak akses portal dicabut.`,
        icon: 'warning', showCancelButton: true, confirmButtonColor: '#f59e0b', cancelButtonColor: isDark ? '#3f3f46' : '#cbd5e1', confirmButtonText: 'Ya, Resign!', cancelButtonText: 'Batal', background: isDark ? '#18181b' : '#ffffff', color: isDark ? '#f4f4f5' : '#09090b', customClass: { popup: 'swal2-custom-radius' }
    }).then((result) => { if (result.isConfirmed) document.getElementById('deact-form-' + id).submit(); });
}

function confirmPush(url, name) {
    const isDark = document.documentElement.classList.contains('dark');
    Swal.fire({
        title: 'Daftarkan ke Mesin?', text: `Sistem akan mengirim identitas ${name} ke mesin pabrik.`,
        icon: 'info', showCancelButton: true, confirmButtonColor: '#10b981', cancelButtonColor: isDark ? '#3f3f46' : '#cbd5e1', confirmButtonText: 'Ya, Kirim', cancelButtonText: 'Batal', background: isDark ? '#18181b' : '#ffffff', color: isDark ? '#f4f4f5' : '#09090b', customClass: { popup: 'swal2-custom-radius' }
    }).then((result) => { if (result.isConfirmed) window.location.href = url; });
}

function confirmPull(url) {
    const isDark = document.documentElement.classList.contains('dark');
    Swal.fire({
        title: 'Tarik Data dari Mesin?', text: 'Ekstrak daftar karyawan dari mesin fisik ke Web.',
        icon: 'question', showCancelButton: true, confirmButtonColor: '#10b981', cancelButtonColor: isDark ? '#3f3f46' : '#cbd5e1', confirmButtonText: 'Ya, Tarik', cancelButtonText: 'Batal', background: isDark ? '#18181b' : '#ffffff', color: isDark ? '#f4f4f5' : '#09090b', customClass: { popup: 'swal2-custom-radius' }
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({ title: 'Sinkronisasi IoT...', html: 'Mengirim perintah ke mesin fisik.', allowOutsideClick: false, showConfirmButton: false, background: isDark ? '#18181b' : '#ffffff', color: isDark ? '#f4f4f5' : '#09090b', customClass: { popup: 'swal2-custom-radius' }, didOpen: () => { Swal.showLoading(); setTimeout(() => { window.location.href = url; }, 500); } });
        }
    });
}
</script>

<style>
    .swal2-custom-radius {
        border-radius: 20px !important;
        font-family: 'Plus Jakarta Sans', sans-serif;
    }

    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        flex-wrap: wrap;
        gap: 18px;
        margin-bottom: 28px;
    }

    .page-title-wrap {
        display: flex;
        align-items: center;
        gap: 16px;
    }

    .page-icon {
        width: 56px;
        height: 56px;
        border-radius: 20px;
        background: linear-gradient(135deg, rgba(37,99,235,.15), rgba(59,130,246,.05));
        color: #2563eb;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 25px;
        border: 1px solid rgba(37,99,235,.15);
        box-shadow: 0 14px 35px -18px rgba(37,99,235,.45);
    }

    .page-title h1 {
        font-size: 28px;
        font-weight: 900;
        margin: 0 0 5px 0;
        color: var(--text-main);
        letter-spacing: -0.7px;
    }

    .page-title p {
        margin: 0;
        font-size: 12px;
        color: var(--text-muted);
        font-weight: 600;
    }

    .btn-primary {
        background: linear-gradient(135deg, #2563eb, #1d4ed8);
        color: #fff;
        border: none;
        padding: 13px 18px;
        border-radius: 14px;
        font-weight: 800;
        font-size: 12px;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: .25s ease;
        box-shadow: 0 12px 25px -14px rgba(37,99,235,.7);
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        filter: brightness(1.05);
    }

    .btn-iot { background: linear-gradient(135deg, #10b981, #059669); box-shadow: 0 12px 25px -14px rgba(16, 185, 129, 0.7); }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 16px;
        margin-bottom: 24px;
    }

    @media (max-width: 1100px) {
        .stats-grid { grid-template-columns: repeat(2, 1fr); }
    }

    @media (max-width: 640px) {
        .stats-grid { grid-template-columns: 1fr; }
    }

    .stat-card {
        background: var(--bg-surface);
        border: 1px solid var(--border-subtle);
        border-radius: 22px;
        padding: 20px;
        box-shadow: 0 18px 35px -28px rgba(0,0,0,.18);
        position: relative;
        overflow: hidden;
        transition: .25s ease;
    }

    .stat-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 24px 45px -30px rgba(0,0,0,.22);
    }

    .stat-card::after {
        content: '';
        position: absolute;
        right: -22px;
        top: -22px;
        width: 90px;
        height: 90px;
        border-radius: 50%;
        background: rgba(37,99,235,.05);
    }

    .stat-label {
        font-size: 11px;
        font-weight: 800;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: .7px;
        margin-bottom: 10px;
    }

    .stat-value {
        font-size: 30px;
        font-weight: 900;
        color: var(--text-main);
        letter-spacing: -1px;
        line-height: 1;
    }

    .table-card {
        background: var(--bg-surface);
        border: 1px solid var(--border-subtle);
        border-radius: 24px;
        box-shadow: 0 25px 60px -35px rgba(0,0,0,.25);
        overflow: hidden;
    }

    .table-controls {
        padding: 18px 22px;
        border-bottom: 1px dashed var(--border-subtle);
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 14px;
        flex-wrap: wrap;
        background: linear-gradient(to right, rgba(37,99,235,.03), transparent);
    }

    .search-box {
        position: relative;
        width: 100%;
        max-width: 390px;
    }

    .search-box i {
        position: absolute;
        left: 14px;
        top: 50%;
        transform: translateY(-50%);
        color: #2563eb;
        font-size: 17px;
    }

    .search-box input {
        width: 100%;
        background: var(--bg-base);
        border: 1px solid var(--border-subtle);
        padding: 12px 14px 12px 42px;
        border-radius: 14px;
        color: var(--text-main);
        outline: none;
        font-size: 12px;
        font-weight: 700;
        transition: .25s ease;
    }

    .search-box input:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 4px rgba(37,99,235,.12);
    }

    .table-responsive {
        overflow-x: auto;
    }

    table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        white-space: nowrap;
    }

    th {
        text-align: left;
        padding: 16px 18px;
        font-size: 10px;
        font-weight: 900;
        text-transform: uppercase;
        color: var(--text-muted);
        background: linear-gradient(to bottom, rgba(0,0,0,.02), transparent);
        letter-spacing: .7px;
        border-bottom: 1px solid var(--border-subtle);
    }

    td {
        padding: 18px;
        font-size: 12px;
        font-weight: 600;
        color: var(--text-main);
        border-bottom: 1px dashed var(--border-subtle);
        vertical-align: middle;
    }

    tr:last-child td {
        border-bottom: none;
    }

    tr:hover td {
        background: rgba(37,99,235,.025);
    }

    .emp-profile {
        display: flex;
        align-items: center;
        gap: 14px;
    }

    .emp-avatar {
        width: 46px;
        height: 46px;
        border-radius: 16px;
        background: linear-gradient(135deg, #2563eb, #1e40af);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 900;
        font-size: 15px;
        box-shadow: 0 12px 20px -12px rgba(37,99,235,.6);
        flex-shrink: 0;
    }
    .emp-avatar-inactive { background: var(--bg-base); border: 2px dashed var(--border-subtle); color: var(--text-muted); box-shadow: none;}

    .emp-name {
        font-size: 13px;
        font-weight: 900;
        color: var(--text-main);
        margin-bottom: 4px;
    }

    .emp-nik {
        font-family: 'Space Mono', monospace;
        font-size: 10px;
        font-weight: 800;
        color: #2563eb;
        background: rgba(37,99,235,.08);
        display: inline-block;
        padding: 4px 8px;
        border-radius: 8px;
        margin-bottom: 5px;
    }

    .emp-sub {
        font-size: 10px;
        color: var(--text-muted);
        font-weight: 700;
    }

    .cell-title {
        font-weight: 900;
        font-size: 13px;
        color: var(--text-main);
        margin-bottom: 5px;
    }

    .cell-sub {
        font-size: 10px;
        color: var(--text-muted);
        font-weight: 700;
        margin-bottom: 8px;
    }

    .badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 11px;
        border-radius: 999px;
        font-size: 10px;
        font-weight: 900;
        border: 1px solid transparent;
        margin-bottom: 6px;
        margin-right: 6px;
    }

    .iot-capsule {
        display: inline-flex; align-items: center; background: var(--bg-surface);
        border: 1px solid var(--border-subtle); border-radius: 10px; overflow: hidden;
    }
    .iot-section {
        padding: 6px 8px; display: flex; align-items: center; gap: 4px; font-size: 11px;
        font-family: 'Space Mono', monospace; font-weight: 800; border-right: 1px solid var(--border-subtle);
    }
    .iot-section:last-child { border-right: none; }

    .action-group {
        display: inline-flex;
        gap: 6px;
        background: var(--bg-base);
        border: 1px solid var(--border-subtle);
        padding: 6px;
        border-radius: 14px;
    }

    .btn-icon {
        width: 36px;
        height: 36px;
        border-radius: 12px;
        border: none;
        background: transparent;
        color: var(--text-muted);
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        transition: all .2s ease;
        text-decoration: none;
    }

    .btn-icon:hover {
        background: var(--bg-surface);
        transform: translateY(-2px);
        box-shadow: 0 10px 20px -12px rgba(0,0,0,.3);
    }

    .btn-edit:hover { color: #2563eb; }
    .btn-delete:hover { color: #ef4444; }

    .empty-state {
        text-align: center;
        padding: 80px 20px;
        color: var(--text-muted);
    }

    .empty-state i {
        font-size: 46px;
        margin-bottom: 10px;
        opacity: .4;
    }

    .empty-state-title {
        font-weight: 900;
        color: var(--text-main);
        font-size: 15px;
        margin-bottom: 6px;
    }

    .empty-state-sub {
        font-size: 12px;
        color: var(--text-muted);
    }

    @media (max-width: 768px) {
        .page-title h1 { font-size: 22px; }
        .page-icon { width: 50px; height: 50px; font-size: 22px; }
        th, td { padding: 14px 14px; }
    }
</style>

<?php
    $totalEmployees = count($employees);
    $activeEmployees = count(array_filter($employees, fn($e) => (int)($e['is_active'] ?? 1) === 1));
    $inactiveEmployees = count(array_filter($employees, fn($e) => (int)($e['is_active'] ?? 1) === 0));
    $transferEmployees = count(array_filter($employees, fn($e) => ($e['payment_method'] ?? '') === 'Transfer'));
?>

<div class="page-header">
    <div class="page-title-wrap">
        <div class="page-icon">
            <i class="ph-fill ph-users-three"></i>
        </div>
        <div class="page-title">
            <h1>Master Employee</h1>
            <p>Manajemen karyawan, akun login, payroll, integrasi IoT, dan data operasional.</p>
        </div>
    </div>

    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
        <button type="button" onclick="confirmPull('<?= base_url('/employee/pull_from_machine') ?>')" class="btn-primary btn-iot">
            <i class="ph-bold ph-download-simple" style="font-size: 16px;"></i> Tarik Data Mesin
        </button>
        <a href="<?= base_url('/employee/create') ?>" class="btn-primary">
            <i class="ph-bold ph-user-plus" style="font-size: 16px;"></i> Tambah Karyawan
        </a>
    </div>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-label">Total Karyawan</div>
        <div class="stat-value"><?= $totalEmployees ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Karyawan Aktif</div>
        <div class="stat-value"><?= $activeEmployees ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Non Aktif</div>
        <div class="stat-value"><?= $inactiveEmployees ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Metode Transfer</div>
        <div class="stat-value"><?= $transferEmployees ?></div>
    </div>
</div>

<div class="table-card">
    <div class="table-controls">
        <div class="search-box">
            <i class="ph-bold ph-magnifying-glass"></i>
            <input type="text" id="searchInput" placeholder="Cari NIK, nama, jabatan..." onkeyup="filterTable()">
        </div>

        <div style="font-size: 11px; font-weight: 800; color: var(--text-muted);">
            Total data:
            <span style="color: var(--text-main); font-family: 'Space Mono', monospace; font-size: 14px;">
                <?= count($employees) ?>
            </span>
        </div>
    </div>

    <div class="table-responsive">
        <table id="employeeTable">
            <thead>
                <tr>
                    <th>Karyawan</th>
                    <th>Jabatan & Status</th>
                    <th style="text-align: center;">Integrasi IoT</th>
                    <th style="text-align:right;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($employees)): ?>
                    <tr>
                        <td colspan="4">
                            <div class="empty-state">
                                <i class="ph-fill ph-users-slash"></i>
                                <div class="empty-state-title">Belum Ada Data Karyawan</div>
                                <div class="empty-state-sub">Silakan tambahkan data employee baru untuk memulai.</div>
                            </div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach($employees as $emp): ?>
                        <?php
                            $status = $emp['status'] ?? 'Tetap';
                            $isActive = (int)($emp['is_active'] ?? 1);
                        ?>
                        <tr class="emp-row" style="<?= $isActive == 0 ? 'opacity:.68;' : '' ?>">
                            <td>
                                <div class="emp-profile">
                                    <div class="emp-avatar <?= $isActive == 0 ? 'emp-avatar-inactive' : '' ?>"><?= strtoupper(substr($emp['name'], 0, 1)) ?></div>
                                    <div>
                                        <div class="emp-name <?= $isActive == 0 ? 'text-decoration: line-through;' : '' ?>"><?= esc($emp['name']) ?></div>
                                        <div class="emp-nik"><?= esc($emp['employee_id']) ?></div>
                                        <div class="emp-sub"><?= esc($emp['phone'] ?? '-') ?></div>
                                    </div>
                                </div>
                            </td>

                            <td>
                                <div class="cell-title"><?= esc($emp['position']) ?></div>
                                <div class="cell-sub"><?= esc($emp['department']) ?></div>

                                <span class="badge" style="background: <?= $status == 'Borongan' ? 'rgba(245,158,11,.10)' : 'rgba(16,185,129,.10)' ?>; color: <?= $status == 'Borongan' ? '#f59e0b' : '#10b981' ?>; border-color: <?= $status == 'Borongan' ? 'rgba(245,158,11,.18)' : 'rgba(16,185,129,.18)' ?>;">
                                    <i class="ph-bold <?= $status == 'Borongan' ? 'ph-hammer' : 'ph-briefcase' ?>"></i>
                                    <?= esc($status) ?>
                                </span>

                                <span class="badge" style="background: <?= $isActive ? 'rgba(59,130,246,.10)' : 'rgba(239,68,68,.10)' ?>; color: <?= $isActive ? '#3b82f6' : '#ef4444' ?>; border-color: <?= $isActive ? 'rgba(59,130,246,.18)' : 'rgba(239,68,68,.18)' ?>;">
                                    <i class="ph-bold <?= $isActive ? 'ph-check-circle' : 'ph-x-circle' ?>"></i>
                                    <?= $isActive ? 'Aktif' : 'Non Aktif' ?>
                                </span>
                            </td>

                            <td style="text-align: center;">
                                <?php if($isActive == 1): ?>
                                    <div class="iot-capsule">
                                        <div class="iot-section" style="<?= $emp['pin'] ? 'color: #10b981; background: rgba(16,185,129,0.05);' : 'color: var(--text-muted);' ?>" title="ID Mesin Fingerspot"><i class="ph-bold ph-hash"></i> <?= $emp['pin'] ? esc($emp['pin']) : 'N/A' ?></div>
                                        <div class="iot-section" style="<?= ($emp['finger_count'] ?? 0) > 0 ? 'color: #3b82f6; background: rgba(59,130,246,0.05);' : 'color: var(--text-muted);' ?>" title="<?= $emp['finger_count'] ?? 0 ?> Finger"><i class="ph-bold ph-fingerprint"></i> <?= $emp['finger_count'] ?? 0 ?></div>
                                        <div class="iot-section" style="<?= ($emp['face_count'] ?? 0) > 0 ? 'color: #8b5cf6; background: rgba(139,92,246,0.05);' : 'color: var(--text-muted);' ?>" title="<?= $emp['face_count'] ?? 0 ?> Face"><i class="ph-bold ph-bounding-box"></i> <?= $emp['face_count'] ?? 0 ?></div>
                                    </div>
                                    <div style="margin-top: 6px; display: flex; justify-content: center; gap: 4px;">
                                        <button type="button" class="badge" style="cursor:pointer; background:rgba(16,185,129,.1); color:#10b981; border:none; margin:0;" title="Daftarkan ke Mesin Fisik" onclick="confirmPush('<?= base_url('/employee/push_to_machine/' . $emp['id']) ?>', '<?= esc($emp['name']) ?>')"><i class="ph-bold ph-cloud-arrow-up"></i> Push IoT</button>
                                        <?php if($emp['pin']): ?><a href="<?= base_url('/employee/sync_biometric/' . $emp['pin']) ?>" class="badge" style="margin:0; text-decoration:none; background:rgba(59,130,246,.1); color:#3b82f6; border:none;" title="Tarik Update Biometrik"><i class="ph-bold ph-arrows-clockwise"></i> Sync</a><?php endif; ?>
                                    </div>
                                <?php else: ?>
                                    <span style="font-size: 10px; font-weight: 800; color: var(--text-muted); font-style: italic; background: var(--bg-base); padding: 4px 8px; border-radius: 6px; border: 1px solid var(--border-subtle);"><i class="ph-fill ph-plug-x"></i> Akses Dicabut</span>
                                <?php endif; ?>
                            </td>

                            <td style="text-align:right;">
                                <div class="action-group">
                                    <?php if($isActive == 1): ?>
                                        <a href="<?= base_url('/employee/edit/' . $emp['id']) ?>" class="btn-icon btn-edit" title="Edit">
                                            <i class="ph-bold ph-pencil-simple"></i>
                                        </a>
                                        <form action="<?= base_url('/employee/deactivate/' . $emp['id']) ?>" method="post" style="display:inline;" id="deact-form-<?= $emp['id'] ?>">
                                            <?= csrf_field() ?>
                                            <button type="button" class="btn-icon" style="color: #71717a;" title="Proses Resign" onclick="confirmDeactivate(<?= $emp['id'] ?>, '<?= esc($emp['name']) ?>')"><i class="ph-bold ph-user-minus"></i></button>
                                        </form>
                                    <?php endif; ?>

                                    <button type="button" onclick="confirmDelete('<?= $emp['id'] ?>', '<?= esc($emp['name']) ?>')" class="btn-icon btn-delete" title="Hapus">
                                        <i class="ph-bold ph-trash"></i>
                                    </button>

                                    <form id="delete-form-<?= $emp['id'] ?>" action="<?= base_url('/employee/delete/' . $emp['id']) ?>" method="post" style="display:none;">
                                        <?= csrf_field() ?>
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