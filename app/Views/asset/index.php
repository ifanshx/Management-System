<?= $this->extend('layout/template') ?>
<?= $this->section('content') ?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    function confirmDelete(id, name) {
        const isDark = document.documentElement.classList.contains('dark');

        Swal.fire({
            title: 'Hapus Aset Permanen?',
            html: `
                Data aset <b>${name}</b> akan dihapus dari inventaris sistem.<br><br>
                <span style="color:#ef4444; font-size:12px; font-weight:800; background: rgba(239, 68, 68, 0.08); padding: 6px 10px; border-radius: 8px; display:inline-block;">
                    Catatan: histori jurnal akuntansi tidak ikut dibatalkan otomatis.
                </span>
            `,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: isDark ? '#3f3f46' : '#cbd5e1',
            confirmButtonText: '<i class="ph-bold ph-trash"></i> Ya, Hapus',
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

    // Auto Tampilkan SweetAlert Jika ada session success/error dari Controller
    document.addEventListener("DOMContentLoaded", function() {
        const isDark = document.documentElement.classList.contains('dark');
        <?php if(session()->getFlashdata('success')): ?>
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                html: '<?= session()->getFlashdata('success') ?>',
                confirmButtonColor: '#10b981',
                background: isDark ? '#18181b' : '#ffffff',
                color: isDark ? '#f4f4f5' : '#09090b',
                customClass: { popup: 'swal2-custom-radius' }
            });
        <?php endif; ?>

        <?php if(session()->getFlashdata('error')): ?>
            Swal.fire({
                icon: 'error',
                title: 'Ditolak / Gagal!',
                html: '<?= session()->getFlashdata('error') ?>',
                confirmButtonColor: '#ef4444',
                background: isDark ? '#18181b' : '#ffffff',
                color: isDark ? '#f4f4f5' : '#09090b',
                customClass: { popup: 'swal2-custom-radius' }
            });
        <?php endif; ?>
    });
</script>

<style>
    .swal2-custom-radius {
        border-radius: 24px !important;
        font-family: 'Plus Jakarta Sans', sans-serif;
    }

    /* =========================================================
       1. PAGE HEADER
       ========================================================= */
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        flex-wrap: wrap;
        gap: 18px;
        margin-bottom: 28px;
    }

    .page-title-wrap {
        display: flex;
        align-items: center;
        gap: 16px;
    }

    .title-icon {
        width: 62px;
        height: 62px;
        border-radius: 20px;
        background: linear-gradient(135deg, rgba(245, 158, 11, 0.16), rgba(217, 119, 6, 0.06));
        color: #f59e0b;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 30px;
        border: 1px solid rgba(245, 158, 11, 0.22);
        box-shadow: 0 16px 30px -12px rgba(245, 158, 11, 0.35);
        flex-shrink: 0;
    }

    .page-title h1 {
        font-size: 28px;
        font-weight: 900;
        color: var(--text-main);
        margin: 0 0 6px;
        letter-spacing: -.6px;
        line-height: 1.1;
    }

    .page-title p {
        font-size: 14px;
        color: var(--text-muted);
        font-weight: 600;
        margin: 0;
        max-width: 780px;
        line-height: 1.7;
    }

    .header-actions {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
    }

    .btn-create-asset {
        background: linear-gradient(135deg, #f59e0b, #d97706);
        color: #fff;
        border: none;
        padding: 14px 22px;
        border-radius: 16px;
        font-size: 14px;
        font-weight: 900;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 10px;
        text-decoration: none;
        box-shadow: 0 14px 26px -10px rgba(245, 158, 11, 0.45);
        transition: all .3s cubic-bezier(.16,1,.3,1);
    }

    .btn-create-asset:hover {
        transform: translateY(-3px);
        box-shadow: 0 18px 34px -10px rgba(245, 158, 11, 0.52);
    }

    /* =========================================================
       2. SUMMARY CARDS
       ========================================================= */
    .summary-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 18px;
        margin-bottom: 28px;
    }

    .summary-card {
        background: var(--bg-surface);
        border: 1px solid var(--border-subtle);
        border-radius: 24px;
        padding: 22px;
        box-shadow: var(--shadow-sm);
        position: relative;
        overflow: hidden;
        transition: all .3s cubic-bezier(.16,1,.3,1);
    }

    .summary-card:hover {
        transform: translateY(-4px);
        box-shadow: var(--shadow-md);
    }

    .summary-card::before {
        content: '';
        position: absolute;
        top: -40px;
        right: -30px;
        width: 140px;
        height: 140px;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(245,158,11,.12), transparent 70%);
        pointer-events: none;
    }

    .summary-label {
        font-size: 11px;
        font-weight: 900;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: .7px;
        margin-bottom: 12px;
    }

    .summary-value {
        font-size: 26px;
        font-weight: 900;
        color: var(--text-main);
        letter-spacing: -.8px;
        line-height: 1.1;
        margin-bottom: 10px;
    }

    .summary-sub {
        font-size: 12px;
        color: var(--text-muted);
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .summary-icon {
        width: 48px;
        height: 48px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
        margin-bottom: 14px;
    }

    .ic-amber { background: rgba(245,158,11,.10); color: #f59e0b; }
    .ic-green { background: rgba(16,185,129,.10); color: #10b981; }
    .ic-blue  { background: rgba(14,165,233,.10); color: #0ea5e9; }
    .ic-red   { background: rgba(239,68,68,.10); color: #ef4444; }

    /* =========================================================
       3. SECTION WRAP
       ========================================================= */
    .section-shell {
        background: var(--bg-surface);
        border: 1px solid var(--border-subtle);
        border-radius: 28px;
        padding: 26px;
        box-shadow: var(--shadow-sm);
    }

    .section-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        flex-wrap: wrap;
        margin-bottom: 22px;
    }

    .section-head h2 {
        font-size: 20px;
        font-weight: 900;
        color: var(--text-main);
        letter-spacing: -.4px;
        margin: 0 0 4px;
    }

    .section-head p {
        font-size: 13px;
        color: var(--text-muted);
        font-weight: 600;
        margin: 0;
    }

    .section-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: rgba(245,158,11,.08);
        color: #f59e0b;
        border: 1px solid rgba(245,158,11,.16);
        padding: 10px 14px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 900;
    }

    /* =========================================================
       4. ASSET GRID
       ========================================================= */
    .asset-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(330px, 1fr));
        gap: 22px;
    }

    .asset-card {
        background: linear-gradient(180deg, var(--bg-surface), rgba(255,255,255,.78));
        border: 1px solid var(--border-subtle);
        border-radius: 26px;
        padding: 24px;
        box-shadow: var(--shadow-sm);
        position: relative;
        transition: all .35s cubic-bezier(.16,1,.3,1);
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }

    html.dark .asset-card {
        background: linear-gradient(180deg, rgba(18,18,20,.88), rgba(18,18,20,.72));
    }

    .asset-card::before {
        content: '';
        position: absolute;
        top: -70px;
        right: -70px;
        width: 180px;
        height: 180px;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(245,158,11,.10), transparent 72%);
        pointer-events: none;
    }

    .asset-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 22px 44px -18px rgba(245, 158, 11, 0.18);
        border-color: rgba(245, 158, 11, 0.26);
    }

    .card-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 16px;
        gap: 12px;
        position: relative;
        z-index: 1;
    }

    .a-code {
        font-size: 12px;
        font-family: 'Space Mono', monospace;
        background: rgba(245, 158, 11, 0.06);
        border: 1px solid rgba(245, 158, 11, 0.18);
        padding: 8px 12px;
        border-radius: 10px;
        font-weight: 900;
        color: #f59e0b;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .btn-delete-card {
        background: rgba(239, 68, 68, 0.06);
        color: #ef4444;
        border: 1px solid transparent;
        width: 38px;
        height: 38px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        cursor: pointer;
        transition: all .3s;
        text-decoration: none;
        flex-shrink: 0;
    }

    .btn-delete-card:hover {
        background: #ef4444;
        color: #fff;
        box-shadow: 0 10px 18px rgba(239, 68, 68, 0.28);
        transform: scale(1.05) rotate(4deg);
    }

    .a-name {
        font-size: 19px;
        font-weight: 900;
        color: var(--text-main);
        margin-bottom: 6px;
        line-height: 1.35;
        letter-spacing: -.5px;
        position: relative;
        z-index: 1;
    }

    .a-category {
        font-size: 12px;
        font-weight: 800;
        color: var(--text-muted);
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 14px;
        position: relative;
        z-index: 1;
    }

    .asset-date {
        font-size: 12px;
        color: var(--text-muted);
        margin-bottom: 18px;
        display: flex;
        align-items: center;
        gap: 8px;
        font-weight: 700;
        position: relative;
        z-index: 1;
    }

    .asset-date span {
        color: var(--text-main);
        font-weight: 900;
    }

    .fin-box {
        background: var(--bg-soft);
        padding: 18px;
        border-radius: 18px;
        margin-bottom: 18px;
        border: 1px solid var(--border-subtle);
        flex-grow: 1;
        position: relative;
        z-index: 1;
    }

    .fin-row {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 14px;
        margin-bottom: 12px;
        font-size: 12px;
    }

    .fin-row:last-child {
        margin-bottom: 0;
        padding-top: 14px;
        border-top: 1px dashed var(--border-subtle);
    }

    .fin-label {
        color: var(--text-muted);
        font-weight: 800;
        display: flex;
        align-items: center;
        gap: 8px;
        flex: 1;
    }

    .fin-value {
        font-weight: 900;
        font-family: 'Space Mono', monospace;
        color: var(--text-main);
        text-align: right;
    }

    .status-wrap {
        margin-bottom: 18px;
        position: relative;
        z-index: 1;
    }

    .status-indicator {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-size: 11px;
        font-weight: 900;
        padding: 8px 12px;
        border-radius: 10px;
        text-transform: uppercase;
        letter-spacing: .45px;
    }

    .st-ACTIVE {
        background: rgba(16, 185, 129, 0.10);
        color: #10b981;
        border: 1px solid rgba(16, 185, 129, 0.2);
    }

    .st-MAINTENANCE {
        background: rgba(245, 158, 11, 0.10);
        color: #d97706;
        border: 1px solid rgba(245, 158, 11, 0.2);
    }

    .st-BROKEN {
        background: rgba(239, 68, 68, 0.10);
        color: #ef4444;
        border: 1px solid rgba(239, 68, 68, 0.2);
    }

    .asset-action-form {
        border-top: 1px dashed var(--border-subtle);
        padding-top: 16px;
        margin-top: auto;
        position: relative;
        z-index: 1;
    }

    .btn-update {
        width: 100%;
        background: var(--bg-soft);
        color: var(--text-main);
        border: 1px solid var(--border-subtle);
        padding: 12px;
        border-radius: 14px;
        font-weight: 900;
        font-size: 13px;
        cursor: pointer;
        margin-top: 10px;
        transition: .3s;
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 8px;
    }

    .btn-update:hover {
        background: rgba(245, 158, 11, 0.08);
        color: #f59e0b;
        border-color: rgba(245, 158, 11, 0.25);
        transform: translateY(-2px);
    }

    .empty-state {
        text-align: center;
        padding: 90px 24px;
        background: var(--bg-soft);
        border-radius: 28px;
        border: 2px dashed var(--border-subtle);
        color: var(--text-muted);
    }

    .empty-state i {
        font-size: 72px;
        opacity: .35;
        margin-bottom: 18px;
        display: block;
        color: #f59e0b;
    }

    .empty-state h3 {
        color: var(--text-main);
        font-weight: 900;
        margin-bottom: 8px;
        font-size: 20px;
        letter-spacing: -.3px;
    }

    .empty-state p {
        font-size: 14px;
        max-width: 500px;
        margin: 0 auto;
        line-height: 1.8;
    }

    /* =========================================================
       5. MODAL PREMIUM
       ========================================================= */
    .modal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,0.58);
        backdrop-filter: blur(8px);
        z-index: 1000;
        display: none;
        justify-content: center;
        align-items: center;
        opacity: 0;
        transition: opacity .3s;
        padding: 20px;
    }

    .modal-overlay.active {
        display: flex;
        opacity: 1;
    }

    .modal-box {
        background: var(--bg-surface);
        border-radius: 30px;
        width: 100%;
        max-width: 620px;
        padding: 34px;
        transform: scale(.96) translateY(18px);
        transition: all .4s cubic-bezier(.16,1,.3,1);
        box-shadow: 0 34px 70px -18px rgba(0,0,0,.45);
        border: 1px solid var(--border-subtle);
        max-height: 92vh;
        overflow-y: auto;
        position: relative;
        overflow-x: hidden;
    }

    .modal-box::before {
        content: '';
        position: absolute;
        top: -90px;
        right: -70px;
        width: 220px;
        height: 220px;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(245,158,11,.10), transparent 70%);
        pointer-events: none;
    }

    .modal-overlay.active .modal-box {
        transform: scale(1) translateY(0);
    }

    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 16px;
        margin-bottom: 28px;
        position: relative;
        z-index: 1;
    }

    .modal-title {
        font-size: 24px;
        font-weight: 900;
        color: var(--text-main);
        display: flex;
        align-items: center;
        gap: 14px;
        letter-spacing: -.5px;
    }

    .modal-title-icon {
        background: rgba(245, 158, 11, 0.10);
        width: 48px;
        height: 48px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #f59e0b;
        border: 1px solid rgba(245,158,11,.18);
        flex-shrink: 0;
    }

    .btn-close {
        background: var(--bg-soft);
        border: 1px solid var(--border-subtle);
        width: 40px;
        height: 40px;
        border-radius: 50%;
        font-size: 18px;
        color: var(--text-muted);
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all .25s;
        flex-shrink: 0;
    }

    .btn-close:hover {
        background: #ef4444;
        color: #fff;
        border-color: #ef4444;
        transform: rotate(90deg);
    }

    .modal-subtitle {
        font-size: 13px;
        color: var(--text-muted);
        line-height: 1.8;
        font-weight: 600;
        margin-top: 8px;
    }

    /* =========================================================
       6. FORM
       ========================================================= */
    .form-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
    }

    .form-group {
        margin-bottom: 20px;
        text-align: left;
        position: relative;
        z-index: 1;
    }

    .form-group.full {
        grid-column: 1 / -1;
    }

    .form-group label {
        display: block;
        font-size: 11px;
        font-weight: 900;
        color: var(--text-muted);
        margin-bottom: 9px;
        text-transform: uppercase;
        letter-spacing: .6px;
    }

    .form-control {
        width: 100%;
        background: var(--bg-soft);
        border: 1px solid var(--border-subtle);
        padding: 15px 18px;
        border-radius: 15px;
        font-size: 14px;
        color: var(--text-main);
        font-weight: 700;
        outline: none;
        transition: .25s;
    }

    .form-control:focus {
        border-color: #f59e0b;
        box-shadow: 0 0 0 4px rgba(245, 158, 11, 0.12);
        background: var(--bg-surface);
    }

    select.form-control {
        cursor: pointer;
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%2371717a' viewBox='0 0 256 256'%3E%3Cpath d='M213.66,101.66l-80,80a8,8,0,0,1-11.32,0l-80-80A8,8,0,0,1,53.66,90.34L128,164.69l74.34-74.35a8,8,0,0,1,11.32,11.32Z'%3E%3C/path%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 16px center;
        padding-right: 42px;
    }

    .input-group {
        display: flex;
        align-items: stretch;
        background: var(--bg-soft);
        border: 1px solid var(--border-subtle);
        border-radius: 15px;
        overflow: hidden;
        transition: all .25s ease;
    }

    .input-group:focus-within {
        border-color: #f59e0b;
        box-shadow: 0 0 0 4px rgba(245, 158, 11, 0.12);
        background: var(--bg-surface);
    }

    .input-group .prefix {
        background: rgba(0,0,0,.02);
        color: var(--text-muted);
        font-size: 13px;
        font-weight: 900;
        padding: 0 16px;
        display: flex;
        align-items: center;
        border-right: 1px solid var(--border-subtle);
        white-space: nowrap;
    }

    .input-group input {
        flex: 1;
        border: none;
        background: transparent;
        padding: 15px 16px;
        outline: none;
        color: var(--text-main);
        font-weight: 800;
    }

    .input-group input:disabled {
        background: rgba(0,0,0,.04);
        color: var(--text-muted);
        cursor: not-allowed;
    }

    .hint-box {
        font-size: 12px;
        color: #10b981;
        font-weight: 800;
        margin-top: -6px;
        margin-bottom: 18px;
        background: rgba(16, 185, 129, 0.08);
        padding: 12px 14px;
        border-radius: 14px;
        border: 1px dashed rgba(16, 185, 129, 0.28);
        display: none;
        line-height: 1.7;
    }

    .btn-submit {
        width: 100%;
        background: linear-gradient(135deg, #f59e0b, #d97706);
        color: #fff;
        border: none;
        padding: 18px;
        border-radius: 18px;
        font-size: 15px;
        font-weight: 900;
        cursor: pointer;
        transition: all .3s ease;
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 10px;
        margin-top: 10px;
        box-shadow: 0 14px 28px -10px rgba(245, 158, 11, 0.5);
    }

    .btn-submit:hover {
        transform: translateY(-3px);
        box-shadow: 0 18px 34px -10px rgba(245, 158, 11, 0.58);
    }

    @media (max-width: 1100px) {
        .summary-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 768px) {
        .summary-grid {
            grid-template-columns: 1fr;
        }

        .asset-grid {
            grid-template-columns: 1fr;
        }

        .form-grid {
            grid-template-columns: 1fr;
        }

        .modal-box {
            padding: 26px 22px;
            border-radius: 24px;
        }

        .page-title h1 {
            font-size: 24px;
        }

        .section-shell {
            padding: 20px;
            border-radius: 24px;
        }
    }
</style>

<div class="page-header">
    <div class="page-title-wrap">
        <div class="title-icon">
            <i class="ph-fill ph-buildings"></i>
        </div>
        <div class="page-title">
            <h1>Manajemen Aset Tetap & Depresiasi</h1>
            <p>Kelola inventaris properti, kendaraan, mesin produksi, serta nilai ekonomis aset perusahaan secara profesional dan terintegrasi dengan jurnal akuntansi.</p>
        </div>
    </div>

    <div class="header-actions">
        <button onclick="openModal('modalCreateAsset')" class="btn-create-asset">
            <i class="ph-bold ph-plus-circle" style="font-size:20px;"></i>
            Registrasi Aset Baru
        </button>
    </div>
</div>

<div class="summary-grid">
    <div class="summary-card">
        <div class="summary-icon ic-amber"><i class="ph-fill ph-buildings"></i></div>
        <div class="summary-label">Total Aset Terdaftar</div>
        <div class="summary-value"><?= number_format($totalAssets ?? 0, 0, ',', '.') ?></div>
        <div class="summary-sub"><i class="ph-bold ph-stack"></i> Seluruh aset inventaris perusahaan</div>
    </div>

    <div class="summary-card">
        <div class="summary-icon ic-blue"><i class="ph-fill ph-currency-circle-dollar"></i></div>
        <div class="summary-label">Total Nilai Perolehan</div>
        <div class="summary-value" style="font-size:22px;">Rp <?= number_format($totalValue ?? 0, 0, ',', '.') ?></div>
        <div class="summary-sub"><i class="ph-bold ph-bank"></i> Akumulasi nilai pembelian aset</div>
    </div>

    <div class="summary-card">
        <div class="summary-icon ic-green"><i class="ph-fill ph-check-circle"></i></div>
        <div class="summary-label">Aset Aktif</div>
        <div class="summary-value"><?= number_format($activeCount ?? 0, 0, ',', '.') ?></div>
        <div class="summary-sub"><i class="ph-bold ph-activity"></i> Siap digunakan operasional</div>
    </div>

    <div class="summary-card">
        <div class="summary-icon ic-red"><i class="ph-fill ph-trend-down"></i></div>
        <div class="summary-label">Penyusutan / Bulan</div>
        <div class="summary-value" style="font-size:22px;">Rp <?= number_format($totalDep ?? 0, 0, ',', '.') ?></div>
        <div class="summary-sub"><i class="ph-bold ph-chart-line-down"></i> Estimasi beban depresiasi bulanan</div>
    </div>
</div>

<div class="section-shell">
    <div class="section-head">
        <div>
            <h2>Inventaris Aset Perusahaan</h2>
            <p>Daftar lengkap aset tetap berikut kondisi fisik, nilai buku awal, dan parameter depresiasinya.</p>
        </div>

        <div class="section-badge">
            <i class="ph-bold ph-archive-box"></i>
            <?= number_format(count($assets), 0, ',', '.') ?> data aset
        </div>
    </div>

    <div class="asset-grid">
        <?php if (empty($assets)): ?>
            <div class="empty-state" style="grid-column:1/-1;">
                <i class="ph-fill ph-buildings"></i>
                <h3>Belum Ada Aset Terdaftar</h3>
                <p>Klik tombol <b>Registrasi Aset Baru</b> untuk mulai mencatat properti, mesin, kendaraan, atau aset tetap lain milik perusahaan.</p>
            </div>
        <?php else: ?>
            <?php foreach($assets as $a): ?>
                <div class="asset-card">
                    <div class="card-header">
                        <div class="a-code">
                            <i class="ph-bold ph-barcode"></i>
                            <?= esc($a['asset_code']) ?>
                        </div>

                        <form action="<?= base_url('/asset/delete/'.$a['id']) ?>" method="post" id="delete-form-<?= $a['id'] ?>">
                            <?= csrf_field() ?>
                            <button type="button" onclick="confirmDelete(<?= $a['id'] ?>, '<?= esc($a['asset_name']) ?>')" class="btn-delete-card" title="Hapus Aset Permanen">
                                <i class="ph-bold ph-trash"></i>
                            </button>
                        </form>
                    </div>

                    <div class="a-name"><?= esc($a['asset_name']) ?></div>

                    <div class="a-category">
                        <?php
                            $catIcon = 'ph-wrench';
                            if (($a['asset_category'] ?? '') == 'Bangunan / Gedung') $catIcon = 'ph-buildings';
                            if (($a['asset_category'] ?? '') == 'Tanah / Lahan') $catIcon = 'ph-mountains';
                            if (($a['asset_category'] ?? '') == 'Kendaraan Operasional') $catIcon = 'ph-truck';
                        ?>
                        <i class="ph-fill <?= $catIcon ?>" style="font-size:16px;"></i>
                        <?= esc($a['asset_category'] ?? 'Mesin & Peralatan') ?>
                    </div>

                    <div class="asset-date">
                        <i class="ph-bold ph-calendar-blank"></i>
                        Akuisisi:
                        <span><?= date('d M Y', strtotime($a['purchase_date'])) ?></span>
                    </div>

                    <div class="fin-box">
                        <div class="fin-row">
                            <span class="fin-label">
                                <i class="ph-bold ph-money"></i> Nilai Perolehan
                            </span>
                            <span class="fin-value">Rp <?= number_format($a['purchase_price'] ?? 0, 0, ',', '.') ?></span>
                        </div>

                        <div class="fin-row">
                            <span class="fin-label">
                                <i class="ph-bold ph-hourglass"></i> Umur Ekonomis
                            </span>
                            <span class="fin-value">
                                <?= (($a['asset_category'] ?? '') == 'Tanah / Lahan') ? 'Tidak Terbatas' : esc($a['useful_life_months'] ?? 0) . ' Bulan' ?>
                            </span>
                        </div>

                        <div class="fin-row">
                            <span class="fin-label" style="color: <?= (($a['asset_category'] ?? '') == 'Tanah / Lahan') ? 'var(--text-muted)' : '#ef4444' ?>;">
                                <i class="ph-bold ph-trend-down"></i> Penyusutan
                            </span>
                            <span class="fin-value" style="color: <?= (($a['asset_category'] ?? '') == 'Tanah / Lahan') ? 'var(--text-muted)' : '#ef4444' ?>;">
                                <?= (($a['asset_category'] ?? '') == 'Tanah / Lahan') ? 'Rp 0' : '- Rp ' . number_format($a['monthly_depreciation'] ?? 0, 0, ',', '.') . ' /bln' ?>
                            </span>
                        </div>
                    </div>

                    <div class="status-wrap">
                        <div class="status-indicator st-<?= esc($a['status']) ?>">
                            <?php
                                if ($a['status'] == 'ACTIVE') {
                                    echo '<i class="ph-fill ph-check-circle"></i> Beroperasi Normal';
                                } elseif ($a['status'] == 'MAINTENANCE') {
                                    echo '<i class="ph-fill ph-warning-circle"></i> Dalam Perawatan';
                                } else {
                                    echo '<i class="ph-fill ph-x-circle"></i> Tidak Layak Pakai';
                                }
                            ?>
                        </div>
                    </div>

                    <form action="<?= base_url('/asset/update_status/'.$a['id']) ?>" method="post" class="asset-action-form">
                        <?= csrf_field() ?>
                        <select name="status" class="form-control" style="padding: 12px 14px; font-size: 12px; margin-bottom: 10px; cursor: pointer; font-weight: 800;">
                            <option value="ACTIVE" <?= $a['status']=='ACTIVE'?'selected':'' ?>>Kondisi Prima</option>
                            <option value="MAINTENANCE" <?= $a['status']=='MAINTENANCE'?'selected':'' ?>>Perawatan Berkala</option>
                            <option value="BROKEN" <?= $a['status']=='BROKEN'?'selected':'' ?>>Tidak Layak Pakai</option>
                        </select>

                        <button type="submit" class="btn-update">
                            <i class="ph-bold ph-arrows-clockwise"></i>
                            Perbarui Kondisi Fisik
                        </button>
                    </form>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<div class="modal-overlay" id="modalCreateAsset">
    <div class="modal-box" style="border-top: 8px solid #f59e0b;">
        <div class="modal-header">
            <div>
                <div class="modal-title">
                    <div class="modal-title-icon">
                        <i class="ph-fill ph-buildings" style="font-size:24px;"></i>
                    </div>
                    Registrasi Aset Tetap
                </div>
                <div class="modal-subtitle">
                    Tambahkan aset perusahaan baru dan catat nilai perolehannya langsung ke sistem inventaris & jurnal akuntansi.
                </div>
            </div>

            <button class="btn-close" onclick="closeModal('modalCreateAsset')" type="button">
                <i class="ph-bold ph-x"></i>
            </button>
        </div>

        <form action="<?= base_url('/asset/store') ?>" method="post">
            <?= csrf_field() ?>

            <div class="form-group">
                <label>Kode Inventaris <span style="color:var(--text-muted); text-transform:none; font-weight:600;">(Generate Otomatis)</span></label>
                <input type="text" name="asset_code" class="form-control" value="<?= esc($autoAssetCode) ?>" readonly style="background: rgba(0,0,0,0.03); font-family: 'Space Mono', monospace; font-size: 15px; color: var(--text-muted); cursor: not-allowed; border-color: transparent;">
            </div>

            <div class="form-grid">
                <div class="form-group full">
                    <label>Nama Aset / Spesifikasi Singkat</label>
                    <input type="text" name="asset_name" class="form-control" placeholder="Contoh: Mesin Bubut CNC / Mobil Box L300 / Gedung Workshop" required minlength="3" autocomplete="off">
                </div>

                <div class="form-group">
                    <label>Kategori Aset</label>
                    <select name="asset_category" id="assetCategory" class="form-control" onchange="toggleDepreciation()" required>
                        <option value="Mesin & Peralatan">Mesin & Peralatan</option>
                        <option value="Bangunan / Gedung">Bangunan / Gedung Pabrik</option>
                        <option value="Kendaraan Operasional">Kendaraan Operasional</option>
                        <option value="Tanah / Lahan">Tanah / Lahan</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Tanggal Akuisisi</label>
                    <input type="date" name="purchase_date" class="form-control" value="<?= date('Y-m-d') ?>" required style="font-family: 'Space Mono', monospace;">
                </div>

                <div class="form-group">
                    <label>Nilai Perolehan</label>
                    <div class="input-group">
                        <span class="prefix">Rp</span>
                        <input type="text" name="purchase_price" placeholder="0" onkeyup="formatRupiah(this)" required autocomplete="off" style="font-family: 'Space Mono', monospace; font-size: 15px; font-weight: 900; color: #f59e0b;">
                    </div>
                </div>

                <div class="form-group">
                    <label>Umur Ekonomis</label>
                    <div class="input-group" id="usefulLifeWrapper">
                        <input type="number" id="usefulLifeInput" name="useful_life_months" value="60" required min="1" autocomplete="off" style="font-family: 'Space Mono', monospace; font-size: 15px; font-weight: 900; text-align: center;">
                        <span class="prefix" style="border-right:none; border-left:1px solid var(--border-subtle);">Bulan</span>
                    </div>
                </div>
            </div>

            <div id="landNotice" class="hint-box">
                <i class="ph-bold ph-info"></i>
                Aset kategori <b>Tanah / Lahan</b> secara umum tidak mengalami penyusutan (depresiasi) dalam praktik akuntansi standar.
            </div>

            <button type="submit" class="btn-submit">
                <i class="ph-bold ph-floppy-disk" style="font-size:22px;"></i>
                Simpan ke Inventaris & Akuntansi
            </button>
        </form>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        toggleDepreciation();
    });

    function toggleDepreciation() {
        const category = document.getElementById('assetCategory').value;
        const lifeInput = document.getElementById('usefulLifeInput');
        const landNotice = document.getElementById('landNotice');

        if (category === 'Tanah / Lahan') {
            lifeInput.value = 0;
            lifeInput.disabled = true;
            lifeInput.removeAttribute('required');
            landNotice.style.display = 'block';
        } else {
            lifeInput.disabled = false;
            lifeInput.setAttribute('required', 'required');
            if (lifeInput.value == 0) lifeInput.value = 60;
            landNotice.style.display = 'none';
        }
    }

    function openModal(modalId) {
        document.getElementById(modalId).classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeModal(modalId) {
        document.getElementById(modalId).classList.remove('active');
        document.body.style.overflow = '';
    }

    window.onclick = function(event) {
        if (event.target.classList.contains('modal-overlay')) {
            event.target.classList.remove('active');
            document.body.style.overflow = '';
        }
    }

    function formatRupiah(angka) {
        if (!angka) return;

        let number_string = angka.value.replace(/[^,\d]/g, '').toString(),
            split   = number_string.split(','),
            sisa    = split[0].length % 3,
            rupiah  = split[0].substr(0, sisa),
            ribuan  = split[0].substr(sisa).match(/\d{3}/gi);

        if (ribuan) {
            let separator = sisa ? '.' : '';
            rupiah += separator + ribuan.join('.');
        }

        rupiah = split[1] !== undefined ? rupiah + ',' + split[1] : rupiah;
        angka.value = rupiah;
    }
</script>

<?= $this->endSection() ?>