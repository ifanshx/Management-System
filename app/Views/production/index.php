<?= $this->extend('layout/template') ?>
<?= $this->section('content') ?>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&family=Space+Mono:ital,wght@0,400;0,700;1,400;1,700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<?php
$db = \Config\Database::connect();
$rawMaterials  = $db->table('raw_materials')->orderBy('material_name', 'ASC')->get()->getResultArray();
$rmList = $rawMaterials;

$spkAktif = 0; $spkSelesai = 0; $totalKnalpotHariIni = 0;
if(isset($workOrders)) {
    foreach($workOrders as $wo) {
        if($wo['status'] == 'IN_PROGRESS') $spkAktif++;
        if($wo['status'] == 'COMPLETED') $spkSelesai++;
    }
}
if(isset($logs)) {
    foreach($logs as $log) {
        if(date('Y-m-d', strtotime($log['production_date'])) == date('Y-m-d') && $log['is_final_step'] == 1 && $log['status'] == 'Approved') {
            $totalKnalpotHariIni += $log['qty_produced'];
        }
    }
}

$groupedLogs = [];
if (!empty($pendingLogs)) {
    foreach ($pendingLogs as $log) {
        $spk = $log['spk_number'];
        if (!isset($groupedLogs[$spk])) {
            $groupedLogs[$spk] = [];
        }
        $groupedLogs[$spk][] = $log;
    }
}

$isMandorView = false;
if (session()->get('role') === 'admin') {
    $isMandorView = true;
} else {
    if (isset($workers) && isset($userEmpId)) {
        foreach($workers as $w) {
            if(($w['leader_id'] ?? '') === $userEmpId) {
                $isMandorView = true; break;
            }
        }
    }
}
?>

<style>
    :root {
        --prod-primary: #2563eb; --prod-primary-dark: #1e40af; --prod-primary-soft: #eff6ff;
        --prod-accent: #8b5cf6; --prod-accent-dark: #6d28d9; --prod-accent-soft: #f5f3ff;
        --prod-success: #10b981; --prod-success-dark: #047857; --prod-success-soft: #ecfdf5;
        --prod-warning: #f59e0b; --prod-warning-dark: #b45309; --prod-warning-soft: #fffbeb;
        --prod-danger: #ef4444; --prod-danger-dark: #b91c1c; --prod-danger-soft: #fef2f2;
        --prod-info: #0ea5e9; --prod-info-dark: #0369a1; --prod-info-soft: #f0f9ff;
        
        --bg-body: #f8fafc; --bg-surface: #ffffff; --bg-input: #f1f5f9;
        --text-main: #0f172a; --text-muted: #64748b; --border-subtle: #e2e8f0;
        
        --shadow-sm: 0 2px 4px rgba(15, 23, 42, 0.04);
        --shadow-md: 0 8px 20px -4px rgba(15, 23, 42, 0.08);
        --shadow-lg: 0 20px 40px -10px rgba(15, 23, 42, 0.12);
        
        --radius-sm: 10px; --radius-md: 16px; --radius-lg: 20px; --radius-xl: 28px;
        --transition-smooth: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    html.dark { 
        --bg-body: #0f172a; --bg-surface: #1e293b; --bg-input: #0f172a;
        --text-main: #f8fafc; --text-muted: #94a3b8; --border-subtle: #334155;
    }

    body { font-family: 'Plus Jakarta Sans', sans-serif; background: var(--bg-body); color: var(--text-main); }
    ::-webkit-scrollbar { width: 6px; height: 6px; }
    ::-webkit-scrollbar-track { background: transparent; }
    ::-webkit-scrollbar-thumb { background: var(--border-subtle); border-radius: 10px; }
    ::-webkit-scrollbar-thumb:hover { background: var(--text-muted); }

    @keyframes slideInUp { from { opacity: 0; transform: translateY(15px); } to { opacity: 1; transform: translateY(0); } }
    @keyframes pulseSoft { 0% { box-shadow: 0 0 0 0 rgba(245, 158, 11, 0.4); } 70% { box-shadow: 0 0 0 10px rgba(245, 158, 11, 0); } 100% { box-shadow: 0 0 0 0 rgba(245, 158, 11, 0); } }

    .prod-page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 40px; flex-wrap: wrap; gap: 20px; animation: slideInUp 0.4s ease-out;} 
    .prod-page-title { display: flex; align-items: center; gap: 20px;}
    .prod-title-icon { width: 64px; height: 64px; border-radius: 22px; background: linear-gradient(135deg, var(--prod-primary), var(--prod-info)); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 30px; box-shadow: 0 15px 30px -5px rgba(37, 99, 235, 0.4);}
    .prod-title-text h1 { font-size: 28px; font-weight: 900; margin: 0; letter-spacing: -0.5px; color: var(--text-main);}
    .prod-title-text p { font-size: 14px; color: var(--text-muted); font-weight: 600; margin: 4px 0 0 0;}

    .prod-actions { display: flex; gap: 12px; flex-wrap: wrap;}
    .btn-custom { padding: 12px 24px; border-radius: 99px; font-size: 14px; font-weight: 800; display: inline-flex; align-items: center; gap: 8px; cursor: pointer; transition: var(--transition-smooth); border: 1px solid transparent; text-decoration: none;}
    .btn-custom:hover { transform: translateY(-2px); box-shadow: var(--shadow-md);}
    
    .btn-outline { background: var(--bg-surface); color: var(--text-main); border-color: var(--border-subtle); box-shadow: var(--shadow-sm);}
    .btn-outline:hover { border-color: var(--prod-primary); color: var(--prod-primary);}
    .btn-gradient-primary { background: linear-gradient(135deg, var(--prod-primary), var(--prod-primary-dark)); color: #fff; box-shadow: 0 8px 20px -6px rgba(37, 99, 235, 0.5);}
    .btn-gradient-warning { background: linear-gradient(135deg, var(--prod-warning), var(--prod-warning-dark)); color: #fff; box-shadow: 0 8px 20px -6px rgba(245, 158, 11, 0.5);}
    
    .btn-action-sm { padding: 8px 16px; border-radius: 12px; font-size: 12px; font-weight: 800; display: inline-flex; align-items: center; gap: 6px; text-decoration: none; border: 1px solid transparent; transition: var(--transition-smooth); cursor: pointer;}
    .btn-action-sm:hover { transform: translateY(-2px);}
    .btn-action-sm.setor { background: var(--prod-warning-soft); color: var(--prod-warning-dark); border-color: rgba(245,158,11,0.2); }
    .btn-action-sm.setor:hover { background: var(--prod-warning); color: #fff; }
    .btn-action-sm.print { background: var(--bg-input); color: var(--text-muted); border-color: var(--border-subtle); }
    .btn-action-sm.print:hover { background: var(--text-main); color: #fff; }
    .btn-action-sm.edit { background: var(--prod-info-soft); color: var(--prod-info-dark); border-color: rgba(14,165,233,0.2); }
    .btn-action-sm.del { background: var(--prod-danger-soft); color: var(--prod-danger-dark); border-color: rgba(239,68,68,0.2); }

    .prod-stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 24px; margin-bottom: 40px; animation: slideInUp 0.5s ease-out forwards;}
    .prod-stat-card { background: var(--bg-surface); border: 1px solid var(--border-subtle); padding: 24px; border-radius: var(--radius-xl); display: flex; align-items: center; gap: 20px; box-shadow: var(--shadow-sm); transition: var(--transition-smooth);}
    .prod-stat-card:hover { transform: translateY(-4px); box-shadow: var(--shadow-md); border-color: var(--prod-primary-soft);}
    .prod-stat-icon { width: 56px; height: 56px; border-radius: 16px; display: flex; align-items: center; justify-content: center; font-size: 28px; flex-shrink: 0;}
    .prod-stat-info h4 { margin: 0 0 6px 0; font-size: 12px; color: var(--text-muted); text-transform: uppercase; font-weight: 800; letter-spacing: 0.5px;}
    .prod-stat-info h2 { margin: 0; font-size: 28px; color: var(--text-main); font-weight: 900; font-family: 'Space Mono', monospace;}

    .prod-bento-card { background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: var(--radius-xl); padding: 30px; margin-bottom: 40px; box-shadow: var(--shadow-sm); animation: slideInUp 0.6s ease-out forwards; position: relative;}
    .prod-bento-card.accent-purple { border-top: 6px solid var(--prod-accent); }
    .prod-bento-card.accent-blue { border-top: 6px solid var(--prod-primary); }
    
    .prod-card-title { font-size: 18px; font-weight: 900; color: var(--text-main); margin-bottom: 24px; display: flex; justify-content: space-between; align-items: center; gap: 15px; flex-wrap: wrap; border-bottom: 2px dashed var(--border-subtle); padding-bottom: 18px;}
    .title-left { display: flex; align-items: center; gap: 10px; }
    .title-left i { padding: 8px; border-radius: 10px; font-size: 20px; }

    .prod-table-responsive { width: 100%; overflow-y: auto; max-height: 600px; border-radius: var(--radius-lg); border: 1px solid var(--border-subtle); }
    .prod-table { width: 100%; border-collapse: collapse; white-space: nowrap; min-width: 900px; background: var(--bg-surface); }
    .prod-table thead th { position: sticky; top: 0; z-index: 10; text-align: left; padding: 18px 24px; font-size: 11px; font-weight: 900; text-transform: uppercase; color: var(--text-muted); background: #f8fafc; border-bottom: 1px solid var(--border-subtle); letter-spacing: 1px;}
    html.dark .prod-table thead th { background: #0f172a; }
    .prod-table tbody td { padding: 20px 24px; border-bottom: 1px solid var(--border-subtle); vertical-align: middle; transition: var(--transition-smooth);}
    .prod-table tbody tr:hover td { background: var(--bg-input); }
    .prod-table tbody tr:last-child td { border-bottom: none; }
    
    .product-name { font-size: 15px; font-weight: 900; color: var(--text-main); margin-bottom: 6px; white-space: normal; line-height: 1.4;}
    .product-sku { font-family: 'Space Mono', monospace; font-size: 11px; color: var(--text-muted); font-weight: 700;}
    
    .spk-badge-po, .spk-badge, .note-badge { padding: 4px 12px; border-radius: 99px; font-weight: 800; font-size: 11px; display: inline-flex; align-items: center; gap: 6px; border: 1px solid transparent;}
    .spk-badge-po { font-family: 'Space Mono', monospace; color: var(--prod-accent-dark); background: var(--prod-accent-soft); border-color: rgba(139,92,246,0.2);}
    .spk-badge { font-family: 'Space Mono', monospace; color: var(--prod-primary-dark); background: var(--prod-primary-soft); border-color: rgba(37,99,235,0.2);}
    .note-badge { color: var(--prod-warning-dark); background: var(--prod-warning-soft); border-color: rgba(245,158,11,0.2);}

    .status-pill { padding: 6px 14px; border-radius: 99px; font-size: 11px; font-weight: 900; text-transform: uppercase; display: inline-flex; align-items: center; justify-content: center; gap: 6px; width: max-content; letter-spacing: 0.5px;}
    .status-pill.antrean { background: var(--bg-input); color: var(--text-muted); }
    .status-pill.proses { background: var(--prod-warning-soft); color: var(--prod-warning-dark); }
    .status-pill.selesai { background: var(--prod-success-soft); color: var(--prod-success-dark); }

    .progress-stats { font-family: 'Space Mono', monospace; display: flex; align-items: center; justify-content: center; gap: 8px; margin-bottom: 8px;}
    .progress-stats .done { font-size: 22px; font-weight: 900; color: var(--text-main); }
    .progress-stats .divider { font-size: 16px; color: var(--border-subtle); font-weight: 400; }
    .progress-stats .target { font-size: 14px; color: var(--text-muted); font-weight: 700;}

    .custom-checkbox { width: 18px; height: 18px; cursor: pointer; border-radius: 4px; border: 2px solid var(--border-subtle); appearance: none; -webkit-appearance: none; outline: none; transition: 0.2s; position: relative;}
    .custom-checkbox:checked { background-color: var(--prod-primary); border-color: var(--prod-primary); }
    .custom-checkbox:checked::after { content: '✔'; position: absolute; color: white; font-size: 12px; top: 50%; left: 50%; transform: translate(-50%, -50%); font-weight: 900;}
    .highlight-row td { background-color: var(--prod-info-soft) !important; }

    .search-input-group { background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: 99px; display: flex; align-items: center; padding: 0 24px; width: 100%; max-width: 400px; box-shadow: var(--shadow-sm); transition: var(--transition-smooth); margin-bottom: 30px; margin-left: auto;}
    .search-input-group:focus-within { border-color: var(--prod-primary); box-shadow: 0 0 0 4px var(--prod-primary-soft); }
    .search-input-group i { color: var(--text-muted); font-size: 18px;}
    .search-input-group input { border: none; background: transparent; padding: 14px 16px; width: 100%; outline: none; font-weight: 600; font-size: 14px; color: var(--text-main);}

    .group-header td { padding: 0 !important; }
    .group-header-content { padding: 16px 24px; cursor: pointer; display: flex; justify-content: space-between; align-items: center; font-weight: 800; font-size: 14px;}
    .collapsed-accordion { display: none !important; }
    .rotate-icon { transform: rotate(-180deg) !important; }

    .prod-modal-overlay { position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(8px); z-index: 1000; display: none; justify-content: center; align-items: center; opacity: 0; transition: opacity 0.3s ease; padding: 20px;}
    .prod-modal-overlay.active { display: flex; opacity: 1; }
    .prod-modal-box { background: var(--bg-surface); border-radius: var(--radius-xl); width: 100%; max-width: 600px; display: flex; flex-direction: column; max-height: calc(100vh - 40px); overflow: hidden; transform: scale(0.95); transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25);}
    .prod-modal-overlay.active .prod-modal-box { transform: scale(1); }
    
    .prod-modal-header { padding: 24px 30px; border-bottom: 1px solid var(--border-subtle); display: flex; justify-content: space-between; align-items: center;}
    .prod-btn-close { width: 36px; height: 36px; border-radius: 50%; font-size: 16px; cursor: pointer; border: none; background: var(--bg-input); color: var(--text-muted); display: flex; align-items: center; justify-content: center; transition: 0.2s;}
    .prod-btn-close:hover { background: var(--prod-danger-soft); color: var(--prod-danger-dark); transform: rotate(90deg);}
    
    .prod-modal-body { padding: 30px; overflow-y: auto; background: var(--bg-body);}
    .modal-zone { background: var(--bg-surface); border: 1px solid var(--border-subtle); padding: 24px; margin-bottom: 20px; border-radius: var(--radius-lg); box-shadow: var(--shadow-sm); }
    .modal-zone-title { font-size: 12px; font-weight: 900; color: var(--text-muted); text-transform: uppercase; margin-bottom: 20px; letter-spacing: 0.5px;}
    
    .prod-form-group { margin-bottom: 20px; width: 100%;}
    .prod-form-group label { font-size: 12px; font-weight: 800; color: var(--text-muted); display: block; margin-bottom: 8px;}
    
    .stepper-container { display: flex; align-items: center; justify-content: center; gap: 15px; margin-top: 10px; }
    .btn-stepper { background: var(--bg-surface); border: 2px solid var(--border-subtle); color: var(--prod-warning-dark); width: 64px; height: 64px; border-radius: var(--radius-lg); font-size: 32px; font-weight: 900; cursor: pointer; display: flex; align-items: center; justify-content: center; user-select: none; transition: var(--transition-smooth);}
    .btn-stepper:hover { background: var(--prod-warning-soft); border-color: var(--prod-warning); color: var(--prod-warning-dark); transform: translateY(-2px);}
    .qty-display { flex: 1; border: 2px solid var(--border-subtle); background: var(--bg-surface); height: 64px; border-radius: var(--radius-lg); font-size: 32px; font-weight: 900; color: var(--text-main); text-align: center; outline: none; font-family: 'Space Mono', monospace; transition: var(--transition-smooth); }
    .qty-display:focus { border-color: var(--prod-warning); box-shadow: 0 0 0 4px var(--prod-warning-soft); color: var(--prod-warning-dark);}

    .btn-submit-block { width: 100%; border: none; padding: 18px; border-radius: var(--radius-md); font-weight: 900; font-size: 15px; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 10px; margin-top: 10px; transition: var(--transition-smooth);}
    .btn-submit-block.orange { background: linear-gradient(135deg, var(--prod-warning), var(--prod-warning-dark)); color:white; box-shadow: 0 10px 25px -5px rgba(245, 158, 11, 0.4); }

    .prod-input-group { display: flex; align-items: stretch; background: var(--bg-input); border: 2px solid var(--border-subtle); border-radius: var(--radius-md); overflow: hidden; width: 100%;}
    .prod-input-group input { flex: 1; border: none; background: transparent; padding: 14px 18px; font-size: 16px; font-weight: 900; outline: none; width: 100%; color: var(--text-main);}
    .prod-input-group span { padding: 0 18px; background: var(--bg-surface); font-size: 13px; font-weight: 900; color: var(--text-muted); display: flex; align-items: center; border-left: 2px solid var(--border-subtle);}

    .select2-container--default .select2-selection--single { background: var(--bg-input); border: 1px solid var(--border-subtle); height: 52px; border-radius: 12px; display: flex; align-items: center; width: 100%; outline:none;}
    .select2-container--default.select2-container--focus .select2-selection--single { border-color: var(--prod-primary); box-shadow: 0 0 0 4px var(--prod-primary-soft); background: var(--bg-surface);}
    
    .select2-container--default .select2-selection--single .select2-selection__rendered { 
        font-weight: 800; font-size: 13px; padding: 0 16px; color: var(--text-main); 
        white-space: nowrap !important; overflow: hidden !important; text-overflow: ellipsis !important; width: 100%; display: block;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow { height: 50px; right: 10px;}
    
    .select2-result-custom { padding: 4px; }
    .select2-container--default .select2-results__option--highlighted[aria-selected] { background-color: var(--prod-primary-soft); color: var(--text-main); }

    .spk-item-row { display: flex; gap: 12px; margin-bottom: 16px; align-items: flex-end; width: 100%; }
    .spk-item-row .col-select { flex: 1; min-width: 0; margin-bottom: 0; } 
    .spk-item-row .col-qty { width: 110px; margin-bottom: 0; flex-shrink: 0; }
    .spk-item-row .col-action { width: 52px; flex-shrink: 0; margin-bottom: 0; }

    .empty-state { text-align: center; padding: 60px 20px; color: var(--text-muted); width: 100%; display: flex; flex-direction: column; align-items: center;}
    .empty-state i { font-size: 64px; color: var(--border-subtle); margin-bottom: 20px; }
    .empty-state h3 { font-size: 18px; font-weight: 900; color: var(--text-main); margin-bottom: 8px; letter-spacing: -0.5px;}

    @media (max-width: 768px) {
        .prod-page-header { flex-direction: column; align-items: flex-start;}
        .prod-actions { width: 100%; }
        .btn-custom { width: 100%; justify-content: center; }
        .search-input-group { max-width: 100%; margin-right: 0;}
        .prod-modal-box { border-radius: 24px 24px 0 0; max-height: 90vh; position: absolute; bottom: 0; width: 100%; }
        
        .spk-item-row { flex-wrap: wrap; align-items: flex-start; }
        .spk-item-row .col-select { width: 100%; flex: unset; margin-bottom: 10px; }
        .spk-item-row .col-qty { width: calc(100% - 64px); }
        .spk-item-row .col-action { width: 52px; }
    }
</style>

<form id="csrf-form" style="display:none;">
    <?= csrf_field() ?>
</form>

<div class="prod-page-header">
    <div class="prod-page-title">
        <div class="prod-title-icon"><i class="ph-fill ph-factory"></i></div>
        <div class="prod-title-text">
            <h1>Command Center Produksi</h1>
            <p>Terbitkan SPK, atur jalur Borongan & Tetap, lalu pantau target hari ini.</p>
        </div>
    </div>
    
    <div class="prod-actions">
        <button onclick="openModal('modalRiwayat')" class="btn-custom btn-outline">
            <i class="ph-bold ph-clock-counter-clockwise"></i> Riwayat <?= $isMandorView ? 'Tim Saya' : 'Saya' ?>
        </button>

        <?php if($userRole === 'admin'): ?>
            <a href="<?= base_url('/production/confirm_logs') ?>" class="btn-custom btn-gradient-warning" style="<?= !empty($pendingLogs) ? 'animation: pulseSoft 2s infinite;' : '' ?>">
                <i class="ph-bold ph-bell-ringing"></i> Konfirmasi Setoran
                <?php if(!empty($pendingLogs)): ?>
                    <span style="background: rgba(255,255,255,0.3); color: #fff; padding: 2px 8px; border-radius: 12px; font-size: 12px; font-weight: 900; margin-left: 4px;"><?= count($pendingLogs) ?></span>
                <?php endif; ?>
            </a>
            
            <a href="<?= base_url('/production/bom_builder') ?>" class="btn-custom btn-outline">
                <i class="ph-bold ph-flask" style="color: var(--prod-accent-dark);"></i> BoM & Routing Studio
            </a>
            
            <button onclick="openModal('modalSPK')" class="btn-custom btn-gradient-primary">
                <i class="ph-bold ph-plus-circle"></i> Terbitkan SPK Reguler
            </button>
        <?php endif; ?>
    </div>
</div>

<div class="prod-stats-grid">
    <div class="prod-stat-card">
        <div class="prod-stat-icon" style="background: var(--prod-info-soft); color: var(--prod-info);"><i class="ph-fill ph-clipboard-text"></i></div>
        <div class="prod-stat-info"><h4>SPK Berjalan</h4><h2><?= $spkAktif ?></h2></div>
    </div>
    <div class="prod-stat-card">
        <div class="prod-stat-icon" style="background: var(--prod-success-soft); color: var(--prod-success-dark);"><i class="ph-fill ph-check-circle"></i></div>
        <div class="prod-stat-info"><h4>SPK Selesai (All Time)</h4><h2><?= $spkSelesai ?></h2></div>
    </div>
    <div class="prod-stat-card">
        <div class="prod-stat-icon" style="background: var(--prod-warning-soft); color: var(--prod-warning-dark);"><i class="ph-fill ph-package"></i></div>
        <div class="prod-stat-info"><h4>Knalpot Jadi (Hari Ini)</h4><h2><?= $totalKnalpotHariIni ?> <span style="font-size:14px; color:var(--text-muted);">Pcs</span></h2></div>
    </div>
</div>

<div class="search-input-group">
    <i class="ph-bold ph-magnifying-glass"></i>
    <input type="text" id="searchSPK" placeholder="Cari SPK atau Nama Barang..." onkeyup="filterSPK()">
</div>

<form id="formBatchSPK-PO" onsubmit="submitBatchSPK_PO(event)">
    <div class="prod-bento-card accent-purple">
        <div class="prod-card-title">
            <div class="title-left"><i class="ph-fill ph-clock-countdown" style="background: var(--prod-accent-soft); color: var(--prod-accent-dark);"></i> Antrean Pre-Order (B2B)</div>
            <?php if($userRole === 'admin'): ?>
                <button type="submit" class="btn-custom" style="background: var(--prod-accent); color: white; padding: 10px 18px; font-size:13px;">
                    <i class="ph-bold ph-check-square-offset"></i> Terbitkan Terpilih
                </button>
            <?php endif; ?>
        </div>
        
        <div class="prod-table-responsive">
            <table class="prod-table" id="tableSPK-PO">
                <thead>
                    <tr>
                        <?php if($userRole === 'admin'): ?>
                            <th style="width: 5%; text-align: center;"><input type="checkbox" id="checkAllPO" class="custom-checkbox" onchange="toggleCheckAll_PO(this)"></th>
                        <?php endif; ?>
                        <th style="width: 40%;">Produk & Referensi SPK</th>
                        <th style="text-align: center; width: 25%;">Progress Tahapan</th>
                        <th style="text-align: center; width: 30%;">Tindakan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($spkPreorderGrouped)): ?>
                        <tr><td colspan="<?= $userRole==='admin'?'4':'3' ?>" style="text-align:center; padding:40px; color:var(--text-muted); font-weight:600;">🎉 Tidak ada antrean pesanan Pre-Order saat ini.</td></tr>
                    <?php else: ?>
                        <?php $gIndex = 0; foreach($spkPreorderGrouped as $group): $gIndex++; $groupId = 'po_group_' . $gIndex; $isFirst = ($gIndex === 1); 
                            $isAllGroupCompleted = true;
                            foreach($group['spks'] as $wocheck) { if($wocheck['status'] != 'COMPLETED') { $isAllGroupCompleted = false; break; } }
                        ?>
                            <tr class="group-header" style="background: <?= $isAllGroupCompleted ? 'var(--prod-success-soft)' : 'var(--prod-accent-soft)' ?>;">
                                <td colspan="<?= $userRole === 'admin' ? '4' : '3' ?>">
                                    <div class="group-header-content" onclick="toggleAccordion('<?= $groupId ?>')" style="color: <?= $isAllGroupCompleted ? 'var(--prod-success-dark)' : 'var(--prod-accent-dark)' ?>;">
                                        <div style="display: flex; align-items: center; gap: 12px;">
                                            <?php if($userRole === 'admin'): ?>
                                                <div onclick="event.stopPropagation();"><input type="checkbox" class="custom-checkbox group-checkbox-<?= $groupId ?>" onchange="toggleCheckGroup(this, '<?= $groupId ?>', 'po')"></div>
                                            <?php endif; ?>
                                            <span><i class="ph-fill <?= $isAllGroupCompleted ? 'ph-check-circle' : 'ph-storefront' ?>" style="font-size:18px; margin-right:4px;"></i> <?= esc($group['group_name']) ?></span>
                                        </div>
                                        <div style="display:flex; align-items:center; gap:8px;">
                                            <span style="background:var(--bg-surface); padding:2px 8px; border-radius:8px; font-size:12px; color:var(--text-main); font-weight:800;"><?= count($group['spks']) ?> SPK</span>
                                            <i class="ph-bold ph-caret-down toggle-icon-<?= $groupId ?> <?= $isFirst ? '' : 'rotate-icon' ?>"></i>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            
                            <?php 
                            $cats = ['SILENCER' => [], 'LEHERAN' => [], 'FULLSYSTEM / PAKET LENGKAP' => [], 'LAIN-LAIN / STOK GUDANG' => []];
                            foreach($group['spks'] as $wo) {
                                $type = strtoupper($wo['item_type'] ?? ''); $name = strtoupper($wo['recipe_name']); $sku = strtoupper($wo['fg_sku']);
                                if (strpos($type, 'SILENCER') !== false || strpos($type, 'SLIP-ON') !== false || strpos($name, 'SLINCER') !== false || strpos($sku, '-SLC-') !== false) { $cats['SILENCER'][] = $wo; } 
                                elseif (strpos($type, 'LEHERAN') !== false || strpos($type, 'HEADER') !== false || strpos($name, 'LEHER') !== false || strpos($sku, '-LHR-') !== false) { $cats['LEHERAN'][] = $wo; } 
                                elseif (strpos($type, 'FULLSYSTEM') !== false || strpos($sku, '-FSY-') !== false) { $cats['FULLSYSTEM / PAKET LENGKAP'][] = $wo; } 
                                else { $cats['LAIN-LAIN / STOK GUDANG'][] = $wo; }
                            }
                            ?>

                            <?php foreach($cats as $catName => $cSpks): if(empty($cSpks)) continue; ?>
                                <tr class="category-header child-row-<?= $groupId ?> <?= $isFirst ? '' : 'collapsed-accordion' ?>" style="background: #fafafa;">
                                    <td colspan="<?= $userRole === 'admin' ? '4' : '3' ?>" style="font-weight: 900; font-size: 11px; color: var(--text-muted); padding: 12px 24px; text-transform: uppercase;"><i class="ph-bold ph-tag" style="color: var(--prod-accent); margin-right: 6px;"></i> <?= $catName ?></td>
                                </tr>
                                
                                <?php foreach($cSpks as $wo): ?>
                                <?php 
                                    $selesai = $db->table('production_logs')->where('spk_number', $wo['spk_number'])->where('is_final_step', 1)->whereIn('status', ['Approved','Pending'])->selectSum('qty_produced')->get()->getRowArray()['qty_produced'] ?? 0; 
                                    $isComplete = $wo['status'] == 'COMPLETED';
                                    $ops = $db->table('bom_operations')->where('bom_id', $wo['bom_id'])->orderBy('step_order', 'ASC')->get()->getResultArray();
                                    $logCounts = $db->table('production_logs')->select('operation_name, SUM(qty_produced) as total_qty')->where('spk_number', $wo['spk_number'])->whereIn('status', ['Approved', 'Pending'])->groupBy('operation_name')->get()->getResultArray();
                                    $logMap = []; foreach($logCounts as $lc) { $logMap[$lc['operation_name']] = (int)$lc['total_qty']; }
                                ?>
                                <tr class="spk-row child-row-<?= $groupId ?> <?= $isFirst ? '' : 'collapsed-accordion' ?>" <?= $isComplete ? 'style="opacity: 0.6;"' : '' ?>>
                                    <?php if($userRole === 'admin'): ?>
                                        <td style="text-align: center;">
                                            <?php if(!$isComplete): ?>
                                                <input type="checkbox" name="spk_ids[]" value="<?= $wo['id'] ?>" class="custom-checkbox spk-checkbox-po" onchange="highlightRow(this)">
                                            <?php else: ?>
                                                <i class="ph-fill ph-check-circle" style="color: var(--prod-success); font-size: 20px;"></i>
                                            <?php endif; ?>
                                        </td>
                                    <?php endif; ?>
                                    <td>
                                        <div class="product-name <?= $isComplete ? 'line-through' : '' ?>"><?= esc($wo['recipe_name']) ?></div>
                                        <div style="display:flex; gap:6px; align-items:center; flex-wrap:wrap; margin-bottom:8px;">
                                            <span class="product-sku">TGT: <?= esc($wo['fg_sku']) ?></span>
                                            <span class="spk-badge-po"><?= esc($wo['spk_number']) ?></span>
                                            <?php if(!empty($wo['production_notes'])): ?><span class="note-badge"><i class="ph-fill ph-push-pin"></i> <?= esc($wo['production_notes']) ?></span><?php endif; ?>
                                        </div>
                                        
                                        <div style="display: flex; gap: 4px; flex-wrap: wrap;">
                                            <?php foreach($ops as $op):
                                                $done = $logMap[$op['operation_name']] ?? 0;
                                                $target = (int)$wo['planned_qty'];
                                                if ($done >= $target) { $bg = 'var(--prod-success-soft)'; $col = 'var(--prod-success-dark)'; $icon = 'ph-check-circle'; } 
                                                elseif ($done > 0) { $bg = 'var(--prod-warning-soft)'; $col = 'var(--prod-warning-dark)'; $icon = 'ph-spinner-gap ph-spin'; } 
                                                else { $bg = 'var(--bg-input)'; $col = 'var(--text-muted)'; $icon = 'ph-circle'; }
                                            ?>
                                                <span style="background: <?= $bg ?>; color: <?= $col ?>; padding: 4px 8px; border-radius: 6px; font-size: 10px; font-weight: 800; display: inline-flex; align-items: center; gap: 4px;" title="<?= esc($op['operation_name']) ?>">
                                                    <i class="ph-bold <?= $icon ?>"></i> <?= esc($op['operation_name']) ?> (<?= $done ?>/<?= $target ?>)
                                                </span>
                                            <?php endforeach; ?>
                                        </div>
                                    </td>
                                    
                                    <td style="text-align: center;">
                                        <div style="display: flex; flex-direction: column; align-items: center; justify-content: center;">
                                            <div class="progress-stats">
                                                <span class="done"><?= $selesai ?></span><span class="divider">/</span><span class="target"><?= $wo['planned_qty'] ?></span>
                                            </div>
                                            <?php if($isComplete): ?>
                                                <span class="status-pill selesai"><i class="ph-bold ph-check-circle"></i> Selesai</span>
                                            <?php elseif($wo['status'] == 'DRAFT' || empty($wo['start_date'])): ?>
                                                <span class="status-pill antrean"><i class="ph-bold ph-hourglass-medium"></i> Antrean</span>
                                            <?php else: ?>
                                                <span class="status-pill proses"><i class="ph-bold ph-spinner-gap"></i> Dikerjakan</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>

                                    <td style="text-align: center;">
                                        <div style="display: flex; gap: 6px; justify-content: center; flex-wrap: wrap;">
                                            <?php if(!$isComplete): ?>
                                                <?php if($wo['status'] == 'IN_PROGRESS' && !empty($wo['start_date'])): ?>
                                                    <?php $buyerNameStr = !empty($group['group_name']) ? $group['group_name'] : 'Pre-Order'; ?>
                                                    <a href="#" onclick='openDailyLogModal(event, <?= $wo['id'] ?>, <?= json_encode($wo['spk_number']) ?>, <?= json_encode($wo['recipe_name'], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) ?>, <?= json_encode($buyerNameStr) ?>)' class="btn-action-sm setor"><i class="ph-bold ph-hammer"></i> Setor</a>
                                                <?php endif; ?>
                                                <a href="<?= base_url('production/print_spk/'.$wo['id']) ?>" target="_blank" class="btn-action-sm print" title="Cetak SPK"><i class="ph-bold ph-printer"></i></a>
                                                
                                                <?php if($userRole === 'admin'): ?>
                                                    <button type="button" onclick="editSPK(<?= $wo['id'] ?>)" class="btn-action-sm edit" title="Revisi Qty"><i class="ph-bold ph-pencil-simple"></i></button>
                                                    <?php if($selesai == 0): ?>
                                                        <a href="#" onclick="confirmAction(event, '<?= base_url('production/delete_spk/'.$wo['id']) ?>', 'Hapus SPK?', 'Yakin ingin menghapus SPK ini?')" class="btn-action-sm del" title="Hapus"><i class="ph-bold ph-trash"></i></a>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <a href="<?= base_url('production/print_spk/'.$wo['id']) ?>" target="_blank" class="btn-action-sm print" style="background: var(--prod-success-soft); color: var(--prod-success-dark);"><i class="ph-bold ph-printer"></i> Cetak Ulang</a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endforeach; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</form>

<form id="formBatchSPK-Reguler" onsubmit="submitBatchSPK_Reguler(event)">
    <div class="prod-bento-card accent-blue">
        <div class="prod-card-title">
            <div class="title-left"><i class="ph-fill ph-kanban" style="background: var(--prod-primary-soft); color: var(--prod-primary-dark);"></i> SPK Reguler (Stok Gudang)</div>
            <?php if($userRole === 'admin'): ?>
            <div>
                <button type="submit" class="btn-custom" style="background: var(--prod-primary); color: white; padding: 10px 18px; font-size:13px;">
                    <i class="ph-bold ph-check-square-offset"></i> Terbitkan Terpilih
                </button>
            </div>
            <?php endif; ?>
        </div>
        <div class="prod-table-responsive">
            <table class="prod-table" id="tableSPK-Reguler">
                <thead>
                    <tr>
                        <?php if($userRole === 'admin'): ?>
                            <th style="width: 5%; text-align: center;"><input type="checkbox" id="checkAllReguler" class="custom-checkbox" onchange="toggleCheckAll_Reguler(this)"></th>
                        <?php endif; ?>
                        <th style="width: 45%;">Produk & Referensi SPK</th>
                        <th style="text-align: center; width: 25%;">Progress Tahapan</th>
                        <th style="text-align: center; width: 25%;">Tindakan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($spkManual)): ?>
                        <tr><td colspan="<?= $userRole==='admin'?'4':'3' ?>" style="text-align:center; padding:40px; color:var(--text-muted); font-weight:600;">📁 Belum ada SPK Reguler yang aktif.</td></tr>
                    <?php else: ?>
                        <?php foreach($spkManual as $wo): ?>
                            <?php 
                                $selesai = $db->table('production_logs')->where('spk_number', $wo['spk_number'])->where('is_final_step', 1)->whereIn('status', ['Approved','Pending'])->selectSum('qty_produced')->get()->getRowArray()['qty_produced'] ?? 0; 
                                $isComplete = $wo['status'] == 'COMPLETED';
                                $ops = $db->table('bom_operations')->where('bom_id', $wo['bom_id'])->orderBy('step_order', 'ASC')->get()->getResultArray();
                                $logCounts = $db->table('production_logs')->select('operation_name, SUM(qty_produced) as total_qty')->where('spk_number', $wo['spk_number'])->whereIn('status', ['Approved', 'Pending'])->groupBy('operation_name')->get()->getResultArray();
                                $logMap = []; foreach($logCounts as $lc) { $logMap[$lc['operation_name']] = (int)$lc['total_qty']; }
                            ?>
                            <tr class="spk-row" <?= $isComplete ? 'style="opacity: 0.6;"' : '' ?>>
                                <?php if($userRole === 'admin'): ?>
                                    <td style="text-align: center;">
                                        <?php if(!$isComplete): ?>
                                            <input type="checkbox" name="spk_ids[]" value="<?= $wo['id'] ?>" class="custom-checkbox spk-checkbox-reguler" onchange="highlightRow(this)">
                                        <?php else: ?>
                                            <i class="ph-fill ph-check-circle" style="color: var(--prod-success); font-size: 20px;"></i>
                                        <?php endif; ?>
                                    </td>
                                <?php endif; ?>
                                <td>
                                    <div class="product-name <?= $isComplete ? 'line-through' : '' ?>"><?= esc($wo['recipe_name']) ?></div>
                                    <div style="display:flex; gap:6px; align-items:center; flex-wrap:wrap; margin-bottom:8px;">
                                        <span class="product-sku">TGT: <?= esc($wo['fg_sku']) ?></span>
                                        <span class="spk-badge"><?= esc($wo['spk_number']) ?></span>
                                        <?php if(!empty($wo['production_notes'])): ?><span class="note-badge"><i class="ph-fill ph-push-pin"></i> <?= esc($wo['production_notes']) ?></span><?php endif; ?>
                                    </div>

                                    <div style="display: flex; gap: 4px; flex-wrap: wrap;">
                                        <?php foreach($ops as $op):
                                            $done = $logMap[$op['operation_name']] ?? 0;
                                            $target = (int)$wo['planned_qty'];
                                            if ($done >= $target) { $bg = 'var(--prod-success-soft)'; $col = 'var(--prod-success-dark)'; $icon = 'ph-check-circle'; } 
                                            elseif ($done > 0) { $bg = 'var(--prod-warning-soft)'; $col = 'var(--prod-warning-dark)'; $icon = 'ph-spinner-gap ph-spin'; } 
                                            else { $bg = 'var(--bg-input)'; $col = 'var(--text-muted)'; $icon = 'ph-circle'; }
                                        ?>
                                            <span style="background: <?= $bg ?>; color: <?= $col ?>; padding: 4px 8px; border-radius: 6px; font-size: 10px; font-weight: 800; display: inline-flex; align-items: center; gap: 4px;" title="<?= esc($op['operation_name']) ?>">
                                                <i class="ph-bold <?= $icon ?>"></i> <?= esc($op['operation_name']) ?> (<?= $done ?>/<?= $target ?>)
                                            </span>
                                        <?php endforeach; ?>
                                    </div>
                                </td>
                                
                                <td style="text-align: center;">
                                    <div style="display: flex; flex-direction: column; align-items: center; justify-content: center;">
                                        <div class="progress-stats">
                                            <span class="done"><?= $selesai ?></span><span class="divider">/</span><span class="target"><?= $wo['planned_qty'] ?></span>
                                        </div>
                                        <?php if($isComplete): ?>
                                            <span class="status-pill selesai"><i class="ph-bold ph-check-circle"></i> Selesai</span>
                                        <?php elseif($wo['status'] == 'DRAFT' || empty($wo['start_date'])): ?>
                                            <span class="status-pill antrean"><i class="ph-bold ph-hourglass-medium"></i> Antrean</span>
                                        <?php else: ?>
                                            <span class="status-pill proses"><i class="ph-bold ph-spinner-gap"></i> Dikerjakan</span>
                                        <?php endif; ?>
                                    </div>
                                </td>

                                <td style="text-align: center;">
                                    <div style="display: flex; gap: 6px; justify-content: center; flex-wrap: wrap;">
                                        <?php if(!$isComplete): ?>
                                            <?php if($wo['status'] == 'IN_PROGRESS' && !empty($wo['start_date'])): ?>
                                                <a href="#" onclick='openDailyLogModal(event, <?= $wo['id'] ?>, <?= json_encode($wo['spk_number']) ?>, <?= json_encode($wo['recipe_name'], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) ?>, "Stok Gudang (Reguler)")' class="btn-action-sm setor"><i class="ph-bold ph-hammer"></i> Setor</a>
                                            <?php endif; ?>
                                            <a href="<?= base_url('production/print_spk/'.$wo['id']) ?>" target="_blank" class="btn-action-sm print" title="Cetak SPK"><i class="ph-bold ph-printer"></i></a>
                                            <?php if($userRole === 'admin'): ?>
                                                <button type="button" onclick="editSPK(<?= $wo['id'] ?>)" class="btn-action-sm edit" title="Revisi SPK"><i class="ph-bold ph-pencil-simple"></i></button>
                                                <?php if($selesai == 0): ?>
                                                    <a href="#" onclick="confirmAction(event, '<?= base_url('production/delete_spk/'.$wo['id']) ?>', 'Hapus SPK?', 'Yakin ingin menghapus permanen?')" class="btn-action-sm del" title="Hapus"><i class="ph-bold ph-trash"></i></a>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <a href="<?= base_url('production/print_spk/'.$wo['id']) ?>" target="_blank" class="btn-action-sm print" style="background: var(--prod-success-soft); color: var(--prod-success-dark);"><i class="ph-bold ph-printer"></i> Cetak Ulang</a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</form>

<div class="prod-modal-overlay" id="modalSPK">
    <div class="prod-modal-box">
        <div class="prod-modal-header">
            <h3 style="margin:0; font-size: 18px; display: flex; align-items: center; gap: 12px; font-weight: 900;">
                <div style="background: var(--prod-primary-soft); color: var(--prod-primary-dark); width: 42px; height: 42px; border-radius: 12px; display: flex; align-items: center; justify-content: center;"><i class="ph-bold ph-plus-circle" style="font-size: 22px;"></i></div> 
                Terbitkan SPK Reguler
            </h3>
            <button class="prod-btn-close" onclick="closeModal('modalSPK')" type="button"><i class="ph-bold ph-x"></i></button>
        </div>
        <div class="prod-modal-body">
            <form action="<?= base_url('/production/create_spk') ?>" method="post">
                <?= csrf_field() ?>
                
                <div class="modal-zone" id="spkItemsContainer">
                    <div class="spk-item-row">
                        <div class="prod-form-group col-select">
                            <label>Resep / SOP Pembuatan</label>
                            <select name="bom_id[]" class="select2-modal" required>
                                <option value=""></option>
                                <?php foreach($boms as $b): ?>
                                    <option value="<?= $b['id'] ?>"><?= esc($b['recipe_name']) ?> [Target: <?= esc($b['fg_sku']) ?>]</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="prod-form-group col-qty">
                            <label>Qty (Pcs)</label>
                            <input type="number" name="planned_qty[]" placeholder="10" required min="1" autocomplete="off" style="width:100%; border: 1px solid var(--border-subtle); border-radius: 12px; background: var(--bg-input); color: var(--text-main); font-size: 15px; font-weight: 800; padding: 15px 10px; text-align: center; outline: none; transition: 0.2s;">
                        </div>
                        <div class="prod-form-group col-action">
                            <button type="button" style="height:52px; width:100%; border-radius:12px; border:none; background:var(--bg-input); color:var(--text-muted); cursor:not-allowed;" disabled><i class="ph-bold ph-trash"></i></button>
                        </div>
                    </div>
                </div>
                
                <button type="button" onclick="addSpkRow()" style="background:transparent; border:1px dashed var(--border-subtle); color:var(--text-muted); width:100%; padding:14px; border-radius:12px; font-weight:800; cursor:pointer; margin-bottom:24px; display:flex; align-items:center; justify-content:center; gap:8px; transition: 0.2s;" onmouseover="this.style.borderColor='var(--prod-primary)'; this.style.color='var(--prod-primary)';" onmouseout="this.style.borderColor='var(--border-subtle)'; this.style.color='var(--text-muted)';">
                    <i class="ph-bold ph-plus-circle" style="font-size:18px;"></i> Tambah Baris Barang
                </button>
                
                <button type="submit" class="btn-submit-block btn-gradient-primary"><i class="ph-bold ph-paper-plane-tilt" style="font-size: 20px;"></i> TERBITKAN SEMUA SPK</button>
            </form>
        </div>
    </div>
</div>

<div class="prod-modal-overlay" id="modalEditSPK">
    <div class="prod-modal-box" style="border-top: 8px solid var(--prod-info);">
        <div class="prod-modal-header">
            <h3 style="margin:0; font-size: 18px; display: flex; align-items: center; gap: 12px; font-weight: 900;">
                <div style="background: var(--prod-info-soft); color: var(--prod-info-dark); width: 42px; height: 42px; border-radius: 12px; display: flex; align-items: center; justify-content: center;"><i class="ph-bold ph-pencil-simple" style="font-size: 22px;"></i></div> 
                Revisi SPK Reguler
            </h3>
            <button class="prod-btn-close" onclick="closeModal('modalEditSPK')" type="button"><i class="ph-bold ph-x"></i></button>
        </div>
        <div class="prod-modal-body">
            <form action="<?= base_url('/production/update_spk') ?>" method="post">
                <?= csrf_field() ?>
                <input type="hidden" name="spk_id" id="edit_spk_id">
                <div class="modal-zone">
                    <div class="spk-item-row" style="margin-bottom:0;">
                        <div class="prod-form-group col-select" style="flex:1;">
                            <label>Resep / SOP Pembuatan</label>
                            <select name="bom_id" id="edit_bom_id" class="select2-edit-modal" required>
                                <option value=""></option>
                                <?php foreach($boms as $b): ?>
                                    <option value="<?= $b['id'] ?>"><?= esc($b['recipe_name']) ?> [Target: <?= esc($b['fg_sku']) ?>]</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="prod-form-group col-qty" style="width: 140px;">
                            <label>Qty Baru (Pcs)</label>
                            <input type="number" name="planned_qty" id="edit_planned_qty" required min="1" autocomplete="off" style="width:100%; border: 1px solid var(--prod-info); border-radius: 12px; background: var(--prod-info-soft); color: var(--prod-info-dark); font-size: 18px; font-weight: 900; padding: 14px 10px; text-align: center; outline: none; height: 52px;">
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn-submit-block" style="background: linear-gradient(135deg, var(--prod-info), var(--prod-info-dark)); color:white; box-shadow: 0 8px 20px -6px rgba(14, 165, 233, 0.5);"><i class="ph-bold ph-floppy-disk" style="font-size: 22px;"></i> Simpan Perubahan</button>
            </form>
        </div>
    </div>
</div>

<div class="prod-modal-overlay" id="modalDailyLog">
    <div class="prod-modal-box" style="border-top: 8px solid var(--prod-warning);">
        <div class="prod-modal-header" style="flex-direction: row; justify-content: space-between; align-items: center; padding-bottom: 20px;">
            <h3 style="margin:0; font-size: 18px; display: flex; align-items: center; gap: 12px; font-weight: 900;">
                <div style="background: var(--prod-warning-soft); color: var(--prod-warning-dark); width: 42px; height: 42px; border-radius: 12px; display: flex; align-items: center; justify-content: center;"><i class="ph-bold ph-hammer" style="font-size: 22px;"></i></div> 
                Setoran Pekerja
            </h3>
            <button class="prod-btn-close" onclick="closeModal('modalDailyLog')" type="button"><i class="ph-bold ph-x"></i></button>
        </div>

        <div class="prod-modal-body" style="padding-top:0;">
            <div style="display: flex; flex-direction: column; gap: 10px; justify-content: center; background: var(--prod-warning-soft); padding: 14px 18px; border-radius: 12px; border: 1px dashed rgba(245,158,11,0.3); margin-bottom:24px;">
                <span id="displaySpkNumber" style="font-family: 'Space Mono', monospace; font-size: 13px; font-weight: 900; color: var(--prod-warning-dark);"></span>
                <div id="displayProductName" style="font-size: 13px; font-weight: 800; color: var(--prod-warning-dark); display: flex; align-items: center; flex-wrap: wrap; gap: 6px;"></div>
            </div>

            <form id="formDailyLog" action="<?= base_url('production/add_production_log') ?>" method="post">
                <?= csrf_field() ?>
                <input type="hidden" name="spk_id" id="logSpkId">
                
                <div class="modal-zone">
                    <div class="modal-zone-title"><i class="ph-bold ph-list-numbers" style="color:var(--prod-primary); font-size: 16px;"></i> 1. Pilih Tahapan Kerja</div>
                    <select name="operation_id" id="operationSelect" class="prod-form-control" required style="width: 100%;">
                        <option value="">-- Sedang Memuat Data... --</option>
                    </select>
                </div>
                
                <div class="modal-zone">
                    <div class="modal-zone-title"><i class="ph-bold ph-user-circle" style="color:var(--prod-info-dark); font-size: 16px;"></i> 2. Data Setoran</div>
                    <div class="prod-form-group">
                        <label>PEKERJA</label>
                        <select name="employee_id" class="select2-employee" required style="width: 100%;">
                            <option value=""></option> 
                            <?php if(!empty($workers)): ?>
                                <optgroup label="Tukang Borongan (Dihitung per Pcs)">
                                    <?php foreach($workers as $w): 
                                        $isBorong = (stripos($w['status'] ?? '', 'Borong') !== false);
                                        if($isBorong): 
                                    ?>
                                        <option value="<?= esc($w['employee_id']) ?>" data-type="Borongan">🛠️ <?= esc($w['name']) ?> <?= !empty($w['specialty']) ? '('.esc($w['specialty']).')' : '' ?> - [<?= esc($w['position_name'] ?? 'Staff') ?>]</option>
                                    <?php endif; endforeach; ?>
                                </optgroup>
                                
                                <optgroup label="Pegawai Tetap (Gaji Bulanan)">
                                    <?php foreach($workers as $w): 
                                        $isTetap = (stripos($w['status'] ?? '', 'Tetap') !== false);
                                        if($isTetap): 
                                    ?>
                                        <option value="<?= esc($w['employee_id']) ?>" data-type="Tetap">🏢 <?= esc($w['name']) ?> <?= !empty($w['specialty']) ? '('.esc($w['specialty']).')' : '' ?> - [<?= esc($w['position_name'] ?? 'Staff') ?>]</option>
                                    <?php endif; endforeach; ?>
                                </optgroup>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="prod-form-group" style="margin-bottom: 0; text-align:center;">
                        <label style="justify-content: center;">JUMLAH SELESAI <span id="maxQtyLabel" style="color: var(--prod-danger-dark); display:none; background: var(--prod-danger-soft); padding: 2px 8px; border-radius: 6px; margin-left:8px;"></span></label>
                        <div class="stepper-container">
                            <button type="button" class="btn-stepper" onclick="decrementQty()">-</button>
                            <input type="number" name="qty_produced" id="qtyInput" class="qty-display" placeholder="0" required min="1" autocomplete="off">
                            <button type="button" class="btn-stepper" onclick="incrementQty()">+</button>
                        </div>
                    </div>
                </div>
                
                <div onclick="$('#customWageDiv').slideToggle(300);" style="background: var(--bg-input); padding: 16px; border-radius: 12px; border: 1px solid var(--border-subtle); display: flex; justify-content: space-between; align-items: center; cursor: pointer; margin-bottom: 20px; font-weight: 800; font-size:13px; color: var(--text-muted); transition:0.2s;" onmouseover="this.style.color='var(--text-main)'; this.style.borderColor='var(--text-muted)'" onmouseout="this.style.color='var(--text-muted)'; this.style.borderColor='var(--border-subtle)'">
                    <span style="display:flex; align-items:center; gap:10px;"><i class="ph-bold ph-sliders" style="font-size: 18px;"></i> Opsi Tarif Khusus / Emblem</span>
                    <i class="ph-bold ph-caret-down"></i>
                </div>
                
                <div id="customWageDiv" style="display: none; background: var(--bg-surface); padding: 20px; border-radius: 12px; border: 1px solid var(--border-subtle); margin-bottom: 20px;">
                    <div class="prod-form-group">
                        <label>Tarif Khusus per Pcs <span id="standarTarifLabel" style="color: var(--prod-danger-dark); font-size: 10px; background: var(--prod-danger-soft); padding: 2px 6px; border-radius: 4px;"></span></label>
                        <div class="prod-input-group" style="border-color: var(--border-subtle);">
                            <span style="color:var(--text-muted); background: transparent;">Rp</span>
                            <input type="text" name="custom_wage" placeholder="Sesuai standar" onkeyup="validateWageInput(this)" style="font-family: 'Space Mono', monospace; font-size: 16px;">
                        </div>
                        <div id="wageValidator" style="font-size: 11px; font-weight: 800; margin-top: 6px;"></div> 
                    </div>
                    <div class="prod-form-group" style="margin-bottom: 25px;">
                        <label>Overhead Tambahan</label>
                        <div class="prod-input-group" style="border-color: var(--border-subtle);">
                            <span style="color:var(--text-muted); background: transparent;">Rp</span>
                            <input type="text" name="overhead_cost" placeholder="0" onkeyup="formatRupiah(this)" style="font-family: 'Space Mono', monospace; font-size: 16px;">
                        </div>
                    </div>
                    <div style="border-top: 1px dashed var(--border-subtle); padding-top: 15px;">
                        <label style="font-size: 11px; color: var(--prod-success-dark); margin-bottom: 12px; display: flex; align-items: center; gap: 6px;"><i class="ph-fill ph-plus-circle" style="font-size: 16px;"></i> Tambahan Emblem/Stiker</label>
                        <div id="extraMaterialContainer"></div>
                        <button type="button" onclick="addExtraMaterialRow()" style="background:var(--prod-success-soft); color:var(--prod-success-dark); border:none; padding:8px 12px; border-radius:8px; font-size:11px; font-weight:800; cursor:pointer;"><i class="ph-bold ph-plus"></i> Baris Material</button>
                    </div>
                </div>

                <button type="submit" id="btnSubmitLog" class="btn-submit-block btn-gradient-warning"><i class="ph-bold ph-paper-plane-tilt" style="font-size: 20px;"></i> KIRIM SETORAN SEKARANG</button>
            </form>
        </div>
    </div>
</div>

<div class="prod-modal-overlay" id="modalRiwayat">
    <div class="prod-modal-box" style="border-top: 8px solid var(--prod-info); max-width: 950px;">
        <div class="prod-modal-header" style="flex-direction: row; justify-content: space-between; align-items: center;">
            <h3 style="margin:0; font-size: 18px; display: flex; align-items: center; gap: 12px; font-weight: 900;">
                <div style="background: var(--prod-info-soft); color: var(--prod-info-dark); width: 42px; height: 42px; border-radius: 12px; display: flex; align-items: center; justify-content: center;"><i class="ph-bold ph-clock-counter-clockwise" style="font-size: 22px;"></i></div> 
                Riwayat Setoran
            </h3>
            <button class="prod-btn-close" onclick="closeModal('modalRiwayat')" type="button"><i class="ph-bold ph-x"></i></button>
        </div>
        <div class="prod-modal-body" style="padding: 20px;">
            <div class="prod-table-responsive" style="max-height: 65vh;">
                <table class="prod-table">
                    <thead>
                        <tr>
                            <th style="width: 20%;">Waktu & Pekerja</th>
                            <th style="width: 40%;">Produk, SPK & Tahapan</th>
                            <th style="text-align: right; width: 15%;">Qty</th>
                            <th style="text-align: center; width: 15%;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($myLogs)): ?>
                            <tr class="empty-state"><td colspan="4" style="text-align:center;">Belum Ada Riwayat</td></tr>
                        <?php else: ?>
                            <?php foreach($myLogs as $log): ?>
                                <tr>
                                    <td>
                                        <div style="font-size:12px; color:var(--text-muted); font-weight:800;"><?= date('d/m/Y H:i', strtotime($log['production_date'])) ?></div>
                                        <div style="font-weight:900; font-size:13px;"><i class="ph-fill ph-user-circle" style="color: var(--prod-primary);"></i> <?= esc($log['emp_name'] ?? 'Unknown') ?></div>
                                    </td>
                                    <td>
                                        <div style="font-size:14px; font-weight:900; margin-bottom:6px;"><?= esc($log['item_name'] ?? 'Produk Tidak Diketahui') ?></div>
                                        <div>
                                            <span class="spk-badge" style="font-size:10px;"><?= esc($log['spk_number']) ?></span> 
                                            <?php if(!empty($log['buyer_name'])): ?>
                                                <span style="font-size:10px; background:var(--prod-accent-soft); color:var(--prod-accent-dark); padding:2px 6px; border-radius:4px; margin-left:4px;"><i class="ph-fill ph-storefront"></i> <?= esc($log['buyer_name']) ?></span>
                                            <?php else: ?>
                                                <span style="font-size:10px; background:var(--prod-info-soft); color:var(--prod-info-dark); padding:2px 6px; border-radius:4px; margin-left:4px;"><i class="ph-fill ph-warehouse"></i> Reguler</span>
                                            <?php endif; ?>
                                            <div style="font-size:11px; font-weight:800; display:block; margin-top:4px;"><i class="ph-bold ph-caret-right"></i> <?= esc($log['operation_name']) ?></div>
                                        </div>
                                    </td>
                                    <td style="text-align: right;">
                                        <div style="font-family:'Space Mono', monospace; font-weight:900; color:var(--prod-warning-dark); font-size:16px;"><?= $log['qty_produced'] ?> Pcs</div>
                                    </td>
                                    <td style="text-align: center;">
                                        <?php if($log['status'] === 'Approved'): ?>
                                            <span style="background:var(--prod-success-soft); color:var(--prod-success-dark); padding:6px 10px; border-radius:8px; font-size:10px; font-weight:900;"><i class="ph-bold ph-check-circle"></i> ACC</span>
                                        <?php elseif($log['status'] === 'Rejected'): ?>
                                            <span style="background:var(--prod-danger-soft); color:var(--prod-danger-dark); padding:6px 10px; border-radius:8px; font-size:10px; font-weight:900;"><i class="ph-bold ph-x-circle"></i> DITOLAK</span>
                                        <?php else: ?>
                                            <span style="background:var(--bg-input); color:var(--text-muted); padding:6px 10px; border-radius:8px; font-size:10px; font-weight:900;"><i class="ph-bold ph-hourglass"></i> PENDING</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const isDark = document.documentElement.classList.contains('dark');
        const bgColor = isDark ? '#1e293b' : '#ffffff';
        const textColor = isDark ? '#f8fafc' : '#0f172a';

        <?php if(session()->getFlashdata('success')): ?>
            Swal.fire({ icon: 'success', title: 'Berhasil!', html: <?= json_encode(session()->getFlashdata('success')) ?>, confirmButtonColor: '#10b981', background: bgColor, color: textColor, customClass: {popup: 'swal2-custom-radius'} });
        <?php endif; ?>
        <?php if(session()->getFlashdata('error')): ?>
            Swal.fire({ icon: 'error', title: 'Gagal!', html: <?= json_encode(session()->getFlashdata('error')) ?>, confirmButtonColor: '#ef4444', background: bgColor, color: textColor, customClass: {popup: 'swal2-custom-radius'} });
        <?php endif; ?>
    });

    function toggleAccordion(groupId) {
        let childRows = document.querySelectorAll('.child-row-' + groupId);
        if (!childRows || childRows.length === 0) return;
        let icon = document.querySelector('.toggle-icon-' + groupId);
        let isCollapsed = childRows[0].classList.contains('collapsed-accordion');
        childRows.forEach(row => isCollapsed ? row.classList.remove('collapsed-accordion') : row.classList.add('collapsed-accordion'));
        if (icon) isCollapsed ? icon.classList.remove('rotate-icon') : icon.classList.add('rotate-icon');
    }

    function toggleCheckGroup(source, groupId, type) {
        let cls = type === 'po' ? '.spk-checkbox-po' : '.spk-checkbox-reguler';
        document.querySelectorAll('.child-row-' + groupId + ' ' + cls).forEach(cb => {
            if (cb.closest('tr').style.display !== 'none' && !cb.disabled) { cb.checked = source.checked; highlightRow(cb); }
        });
    }

    function toggleCheckAll_PO(source) {
        document.querySelectorAll('.spk-checkbox-po').forEach(cb => {
            if (cb.closest('tr').style.display !== 'none') { cb.checked = source.checked; highlightRow(cb); }
        });
    }

    function toggleCheckAll_Reguler(source) {
        document.querySelectorAll('.spk-checkbox-reguler').forEach(cb => {
            if (cb.closest('tr').style.display !== 'none') { cb.checked = source.checked; highlightRow(cb); }
        });
    }

    function highlightRow(checkbox) {
        let row = checkbox.closest('tr');
        checkbox.checked ? row.classList.add('highlight-row') : row.classList.remove('highlight-row');
    }

    function submitBatchSPK_PO(e) {
        e.preventDefault();
        let ids = [];
        document.querySelectorAll('.spk-checkbox-po:checked').forEach(cb => {
            if(cb.closest('tr').style.display !== 'none') { ids.push(cb.value); }
        });

        if (ids.length === 0) {
            Swal.fire({ title: 'Peringatan', text: 'Silakan centang minimal 1 SPK Pre-Order terlebih dahulu.', icon: 'warning', customClass: {popup: 'swal2-custom-radius'} });
            return;
        }

        let formData = new FormData();
        formData.append('<?= csrf_token() ?>', $('input[name="<?= csrf_token() ?>"]').last().val());
        ids.forEach(id => formData.append('spk_ids[]', id));

        Swal.fire({
            title: 'Terbitkan & Cetak?', text: `Sistem akan meresmikan ${ids.length} SPK Pre-Order. Lanjutkan?`, icon: 'question', showCancelButton: true, confirmButtonColor: '#8b5cf6', confirmButtonText: 'Ya, Lanjutkan!', customClass: {popup: 'swal2-custom-radius'}
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({ title: 'Memproses...', allowOutsideClick: false, didOpen: () => { Swal.showLoading() } });
                fetch('<?= base_url("production/batch_create_spk") ?>', { method: 'POST', body: formData, headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(res => res.json())
                .then(res => {
                    if (res.status === 'success') {
                        if(res.print_url) window.open(res.print_url, '_blank');
                        Swal.fire({title: 'Berhasil!', text: res.message, icon: 'success', customClass: {popup: 'swal2-custom-radius'}}).then(() => window.location.reload());
                    } else { Swal.fire({title: 'Error!', text: res.message, icon: 'error', customClass: {popup: 'swal2-custom-radius'}}); }
                });
            }
        });
    }

    function submitBatchSPK_Reguler(e) {
        e.preventDefault();
        let ids = [];
        document.querySelectorAll('.spk-checkbox-reguler:checked').forEach(cb => {
            if(cb.closest('tr').style.display !== 'none') { ids.push(cb.value); }
        });

        if (ids.length === 0) {
            Swal.fire({ title: 'Peringatan', text: 'Silakan centang minimal 1 SPK Reguler Gudang terlebih dahulu.', icon: 'warning', customClass: {popup: 'swal2-custom-radius'} });
            return;
        }

        let formData = new FormData();
        formData.append('<?= csrf_token() ?>', $('input[name="<?= csrf_token() ?>"]').last().val());
        ids.forEach(id => formData.append('spk_ids[]', id));

        Swal.fire({
            title: 'Terbitkan & Cetak?', text: `Sistem akan meresmikan ${ids.length} SPK Reguler. Lanjutkan?`, icon: 'question', showCancelButton: true, confirmButtonColor: '#2563eb', confirmButtonText: 'Ya, Lanjutkan!', customClass: {popup: 'swal2-custom-radius'}
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({ title: 'Memproses...', allowOutsideClick: false, didOpen: () => { Swal.showLoading() } });
                fetch('<?= base_url("production/batch_create_spk") ?>', { method: 'POST', body: formData, headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(res => res.json())
                .then(res => {
                    if (res.status === 'success') {
                        if(res.print_url) window.open(res.print_url, '_blank');
                        Swal.fire({title: 'Berhasil!', text: res.message, icon: 'success', customClass: {popup: 'swal2-custom-radius'}}).then(() => window.location.reload());
                    } else { Swal.fire({title: 'Error!', text: res.message, icon: 'error', customClass: {popup: 'swal2-custom-radius'}}); }
                });
            }
        });
    }

    function filterSPK() {
        let input = document.getElementById("searchSPK").value.toLowerCase();
        if (input !== "") {
            document.querySelectorAll('.collapsed-accordion').forEach(row => row.classList.remove('collapsed-accordion'));
            document.querySelectorAll('.rotate-icon').forEach(icon => icon.classList.remove('rotate-icon'));
        }
        document.querySelectorAll("#tableSPK-Reguler tbody tr:not(.empty-state)").forEach(row => {
            row.style.display = row.textContent.toLowerCase().includes(input) ? "" : "none";
        });
        let poTbody = document.querySelector("#tableSPK-PO tbody");
        if(poTbody) {
            let poRows = poTbody.querySelectorAll("tr:not(.empty-state)");
            poRows.forEach(row => {
                if(row.classList.contains('group-header') || row.classList.contains('category-header')) {
                    row.dataset.matches = row.textContent.toLowerCase().includes(input) ? "true" : "false";
                    row.dataset.hasVisibleChild = "false";
                } else {
                    let matches = row.textContent.toLowerCase().includes(input);
                    row.style.display = matches ? "" : "none";
                    let prevHeader = row.previousElementSibling;
                    while(prevHeader && (!prevHeader.classList.contains('group-header') && !prevHeader.classList.contains('category-header'))) {
                        prevHeader = prevHeader.previousElementSibling;
                    }
                    if(prevHeader && matches) {
                        prevHeader.dataset.hasVisibleChild = "true";
                        if (prevHeader.classList.contains('category-header')) {
                            let groupHeader = prevHeader.previousElementSibling;
                            while(groupHeader && !groupHeader.classList.contains('group-header')) {
                                groupHeader = groupHeader.previousElementSibling;
                            }
                            if (groupHeader) groupHeader.dataset.hasVisibleChild = "true";
                        }
                    }
                }
            });
            poRows.forEach(row => {
                if(row.classList.contains('group-header') || row.classList.contains('category-header')) {
                    row.style.display = (row.dataset.matches === "true" || row.dataset.hasVisibleChild === "true") ? "" : "none";
                } else {
                    let prevHeader = row.previousElementSibling;
                    let parentHeaderMatches = false;
                    while(prevHeader && !prevHeader.classList.contains('group-header') && !prevHeader.classList.contains('category-header')) {
                        prevHeader = prevHeader.previousElementSibling;
                    }
                    if(prevHeader && prevHeader.dataset.matches === "true") parentHeaderMatches = true;
                    if(parentHeaderMatches) row.style.display = "";
                }
            });
        }
    }

    function addSpkRow() {
        const container = document.getElementById('spkItemsContainer');
        const row = document.createElement('div');
        row.className = 'spk-item-row';
        
        let options = '<option value=""></option>';
        <?php foreach($boms as $b): ?>
            options += `<option value="<?= $b['id'] ?>"><?= esc(addslashes($b['recipe_name'])) ?> [TGT: <?= esc(addslashes($b['fg_sku'])) ?>]</option>`;
        <?php endforeach; ?>
        
        row.innerHTML = `
            <div class="prod-form-group col-select">
                <select name="bom_id[]" class="select2-new" required>${options}</select>
            </div>
            <div class="prod-form-group col-qty">
                <input type="number" name="planned_qty[]" placeholder="Qty" required min="1" autocomplete="off" style="width:100%; border: 1px solid var(--border-subtle); border-radius: 12px; background: var(--bg-input); color: var(--text-main); font-size: 15px; font-weight: 800; padding: 15px 10px; text-align: center; outline: none; transition: 0.2s;">
            </div>
            <div class="prod-form-group col-action">
                <button type="button" style="height:52px; width:100%; border-radius:12px; border:none; background:var(--prod-danger-soft); color:var(--prod-danger-dark); cursor:pointer; transition:0.2s;" onclick="this.parentElement.parentElement.remove()" onmouseover="this.style.background='var(--prod-danger)'; this.style.color='#fff'" onmouseout="this.style.background='var(--prod-danger-soft)'; this.style.color='var(--prod-danger-dark)'"><i class="ph-bold ph-trash"></i></button>
            </div>
        `;
        container.appendChild(row);
        
        $(row).find('.select2-new').select2({ 
            width: '100%', 
            placeholder: "-- Pilih SOP --", 
            dropdownParent: $('#modalSPK'),
            templateSelection: function(data) {
                if (!data.id) return data.text;
                let text = data.text;
                if(text.length > 40) return text.substring(0, 40) + '...';
                return text;
            }
        });
        document.querySelector('#modalSPK .prod-modal-body').scrollTo({ top: 1500, behavior: 'smooth' });
    }

    function openModal(modalId) { 
        document.getElementById(modalId).classList.add('active'); 
        document.body.style.overflow = 'hidden'; 
        
        if (modalId === 'modalSPK') {
            $('.select2-modal').select2({ 
                width: '100%', 
                placeholder: "-- Pilih SOP --", 
                dropdownParent: $('#modalSPK'),
                templateSelection: function(data) {
                    if (!data.id) return data.text;
                    let text = data.text;
                    if(text.length > 40) return text.substring(0, 40) + '...';
                    return text;
                }
            });
        } else if (modalId === 'modalEditSPK') {
            $('.select2-edit-modal').select2({ 
                width: '100%', 
                placeholder: "-- Pilih SOP --", 
                dropdownParent: $('#modalEditSPK'),
                templateSelection: function(data) {
                    if (!data.id) return data.text;
                    let text = data.text;
                    if(text.length > 40) return text.substring(0, 40) + '...';
                    return text;
                }
            });
        }
    }
    
    function closeModal(modalId) { 
        document.getElementById(modalId).classList.remove('active'); 
        document.body.style.overflow = '';
        if(modalId === 'modalDailyLog') {
            $('.select2-employee').val(null).trigger('change'); 
            $('#operationSelect').val(null).trigger('change'); 
            $('#qtyInput').val('').removeAttr('max');
            $('#maxQtyLabel').hide();
            $('#customWageDiv').hide();
            $('#wageValidator').html('');
            $('#standarTarifLabel').text('').hide(); 
            $('#hiddenEmpId').remove();
            $('.select2-employee').prop('disabled', false);
            $('#extraMaterialContainer').empty();
        }
    }

    function incrementQty() {
        let input = document.getElementById('qtyInput');
        let max = parseInt(input.getAttribute('max')) || 999999;
        let val = parseInt(input.value) || 0;
        if(val < max) input.value = val + 1;
    }
    
    function decrementQty() {
        let input = document.getElementById('qtyInput');
        let val = parseInt(input.value) || 0;
        if(val > 1) input.value = val - 1;
    }

    function confirmAction(e, url, title, text) {
        e.preventDefault();
        const isDark = document.documentElement.classList.contains('dark');
        Swal.fire({
            title: title, text: text, icon: 'warning', showCancelButton: true, confirmButtonColor: '#ef4444', cancelButtonColor: '#64748b', confirmButtonText: 'Ya, Hapus!', cancelButtonText: 'Batal', background: isDark ? '#1e293b' : '#ffffff', color: isDark ? '#f8fafc' : '#0f172a', customClass: {popup: 'swal2-custom-radius'}
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({ title: 'Memproses...', allowOutsideClick: false, didOpen: () => { Swal.showLoading() } });
                window.location.href = url;
            }
        });
    }

    function editSPK(id) {
        Swal.fire({ title: 'Memuat data...', allowOutsideClick: false, didOpen: () => { Swal.showLoading() } });
        fetch("<?= base_url('/production/get_spk/') ?>" + id, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(res => res.json())
        .then(res => {
            Swal.close();
            if(res.status === 'success') {
                document.getElementById('edit_spk_id').value = res.data.id;
                document.getElementById('edit_planned_qty').value = res.data.planned_qty;
                $('#edit_bom_id').val(res.data.bom_id).trigger('change');
                openModal('modalEditSPK');
            }
        });
    }

    let userEmpId = '<?= $userEmpId ?>';
    let userRole = '<?= $userRole ?>';
    let userSpecialty = '<?= strtolower($userSpecialty) ?>';

    // PERBAIKAN: Fungsi openDailyLogModal sekarang menerima parameter buyerName
    function openDailyLogModal(event, spkId, spkNumber, productName, buyerName = 'Stok Gudang (Reguler)') {
        event.preventDefault();
        document.getElementById('logSpkId').value = spkId;
        document.getElementById('displaySpkNumber').innerText = spkNumber;
        
        // Buat badge identitas Pemesan agar Karyawan tidak bingung lagi
        let badgeHtml = buyerName !== 'Stok Gudang (Reguler)' 
            ? `<span style="background:var(--prod-accent-soft); color:var(--prod-accent-dark); padding:4px 8px; border-radius:6px; font-size:10px; margin-left:8px; border: 1px dashed rgba(139,92,246,0.3); vertical-align:middle;"><i class="ph-fill ph-storefront"></i> Pemesan: ${buyerName}</span>` 
            : `<span style="background:var(--prod-info-soft); color:var(--prod-info-dark); padding:4px 8px; border-radius:6px; font-size:10px; margin-left:8px; border: 1px dashed rgba(14,165,233,0.3); vertical-align:middle;"><i class="ph-fill ph-warehouse"></i> Stok Gudang</span>`;

        document.getElementById('displayProductName').innerHTML = productName + badgeHtml;
        document.getElementById('formDailyLog').reset();
        
        let ajaxUrl = '<?= base_url('production/get_operations') ?>' + '/' + spkId;
        fetch(ajaxUrl)
            .then(response => response.json())
            .then(res => {
                let opSelect = $('#operationSelect');
                opSelect.empty().append('<option value="">-- Silakan Pilih Tahapan --</option>');
                if(res.status === 'success') {
                    let autoSelectId = null;
                    let optionsData = [];

                    res.data.forEach(op => {
                        let isDone = op.qty_done >= op.qty_target;
                        let sisa = Math.max(0, op.qty_target - op.qty_done);
                        let isSpecialtyMatch = false;

                        if (op.specialty_required && op.specialty_required !== '') {
                            if (userSpecialty.toLowerCase().includes(op.specialty_required.toLowerCase())) isSpecialtyMatch = true;
                        } else {
                            isSpecialtyMatch = true;
                        }

                        if (isSpecialtyMatch && !isDone && (userRole === 'karyawan' || userRole === 'mandor') && !autoSelectId) {
                            autoSelectId = op.id;
                        }

                        let color = isDone ? 'var(--prod-success-dark)' : 'var(--prod-primary-dark)';
                        let bg = isDone ? 'var(--prod-success-soft)' : 'var(--prod-primary-soft)';
                        let icon = isDone ? 'ph-check-circle' : 'ph-clock-countdown';
                        let statusText = isDone ? 'SELESAI 100%' : `SISA: ${sisa} Pcs`;
                        let finalBadge = (op.is_final_step == 1) ? `<span style="background:var(--prod-warning-soft); color:var(--prod-warning-dark); padding:4px 8px; border-radius:6px; font-size:10px; font-weight:900; margin-left:8px; border:1px solid rgba(245,158,11,0.2);"><i class="ph-bold ph-flag-checkered"></i> FINAL</span>` : '';
                        let workerTypeBadge = (op.worker_type === 'Tetap') ? 
                            `<span style="background:var(--bg-surface); border: 1px solid var(--border-subtle); color:var(--text-muted); padding:4px 8px; border-radius:6px; font-size:10px; font-weight:900; margin-left:8px;"><i class="ph-bold ph-buildings"></i> TETAP</span>` : 
                            `<span style="background:var(--prod-accent-soft); color:var(--prod-accent-dark); border: 1px solid rgba(139,92,246,0.2); padding:4px 8px; border-radius:6px; font-size:10px; font-weight:900; margin-left:8px;"><i class="ph-bold ph-user"></i> BORONGAN</span>`;
                        let specialtyBadge = isSpecialtyMatch 
                            ? `<span style="background:var(--prod-success-soft); color:var(--prod-success-dark); padding:4px 8px; border-radius:6px; font-size:10px; font-weight:900; margin-left:8px; border:1px solid rgba(16,185,129,0.3);"><i class="ph-fill ph-star"></i> REKOMENDASI</span>` 
                            : `<span style="background:var(--bg-surface); color:var(--text-muted); padding:4px 8px; border-radius:6px; font-size:10px; font-weight:900; margin-left:8px; border:1px dashed var(--border-subtle);"><i class="ph-bold ph-warning"></i> BUKAN KEAHLIAN</span>`;

                        let htmlMarkup = `
                            <div style="display:flex; justify-content:space-between; align-items:center; padding:12px 10px; border-bottom: 1px dashed var(--border-subtle); flex-wrap:wrap; gap:12px; ${!isSpecialtyMatch && userRole === 'karyawan' ? 'opacity: 0.6;' : ''}">
                                <div style="flex:1; min-width: 200px;">
                                    <div style="font-weight:900; font-size:14px; color:var(--text-main); margin-bottom:8px; white-space:normal; line-height:1.4;">${op.step_order}. ${op.operation_name} ${finalBadge}</div>
                                    <div style="display:flex; gap:6px; flex-wrap:wrap; margin-bottom:6px;">${workerTypeBadge} ${userRole === 'karyawan' ? specialtyBadge : ''}</div>
                                    <div style="font-size:13px; color:var(--text-muted); font-family:'Space Mono', monospace; font-weight:700;"><i class="ph-bold ph-coins" style="color:var(--prod-warning-dark);"></i> Rp ${parseInt(op.wage_per_piece).toLocaleString('id-ID')} / Pcs</div>
                                </div>
                                <div style="text-align:right; flex-shrink:0; background: var(--bg-surface); padding: 10px 15px; border-radius: 12px; border: 1px solid var(--border-subtle); box-shadow: var(--shadow-sm);">
                                    <div style="font-size:11px; font-weight:900; color:var(--text-muted); margin-bottom:6px; letter-spacing:0.5px;">TARGET TERCAPAI</div>
                                    <div style="font-family: 'Space Mono'; font-size: 16px; font-weight: 900; color: var(--text-main); margin-bottom: 6px;">${op.qty_done} / ${op.qty_target}</div>
                                    <div style="font-size:10px; font-weight:900; color:${color}; background:${bg}; padding:4px 10px; border-radius:6px; display:inline-flex; align-items:center; gap:6px; border:1px solid ${bg};"><i class="ph-bold ${icon}"></i> ${statusText}</div>
                                </div>
                            </div>
                        `;
                        let optionText = `${op.step_order}. ${op.operation_name} ${isDone ? '(SELESAI)' : `(Sisa: ${sisa} Pcs)`}`;
                        
                        optionsData.push({ id: op.id, text: optionText, html: htmlMarkup, isDone: isDone, sisa: sisa, wage: op.wage_per_piece, workerType: op.worker_type || 'Borongan', isSpecialtyMatch: isSpecialtyMatch });
                    });

                    if (userRole === 'karyawan' || userRole === 'mandor') {
                        optionsData.sort((a, b) => {
                            if (a.isSpecialtyMatch && !b.isSpecialtyMatch) return -1;
                            if (!a.isSpecialtyMatch && b.isSpecialtyMatch) return 1;
                            return 0; 
                        });
                    }

                    optionsData.forEach(opt => {
                        let option = new Option(opt.text, opt.id, false, false);
                        option.disabled = opt.isDone; 
                        $(option).attr('data-html', opt.html);
                        $(option).attr('data-sisa', opt.sisa);
                        $(option).attr('data-wage', opt.wage);
                        $(option).attr('data-worker-type', opt.workerType); 
                        opSelect.append(option);
                    });
                    
                    if (autoSelectId) { opSelect.val(autoSelectId).trigger('change'); }
                    
                    if (userEmpId) {
                        $('.select2-employee').val(userEmpId).trigger('change');
                        if (userRole === 'karyawan') {
                            $('.select2-employee').prop('disabled', true);
                            if ($('#hiddenEmpId').length === 0) {
                                $('#formDailyLog').append('<input type="hidden" name="employee_id" id="hiddenEmpId" value="'+userEmpId+'">');
                            }
                        }
                    }

                }
            });
        
        $(document).ready(function() {
            $('.select2-employee').select2({ width: '100%', placeholder: "-- Cari Pekerja --", dropdownParent: $('#modalDailyLog') });
            
            $('#operationSelect').select2({ 
                width: '100%', 
                placeholder: "-- Cari Tahapan --", 
                dropdownParent: $('#modalDailyLog'),
                escapeMarkup: function (markup) { return markup; },
                templateResult: function (data) {
                    if (!data.id) return data.text;
                    let htmlMarkup = $(data.element).attr('data-html');
                    return htmlMarkup ? htmlMarkup : data.text;
                },
                templateSelection: function (data) { return data.text; }
            });
            
            $('#operationSelect').on('change', function() {
                let selectedOption = $(this).find(':selected');
                let sisa = selectedOption.data('sisa');
                let workerType = selectedOption.data('worker-type'); 
                let wage = selectedOption.data('wage') || 0; 
                
                let qtyInput = $('#qtyInput');
                let labelMaks = $('#maxQtyLabel');
                let labelStandar = $('#standarTarifLabel'); 

                if(wage > 0) {
                    labelStandar.text('Standar: Rp ' + parseInt(wage).toLocaleString('id-ID'));
                    labelStandar.show();
                } else { labelStandar.text(''); labelStandar.hide(); }
                
                if (sisa !== undefined && sisa > 0) {
                    qtyInput.val(sisa); qtyInput.attr('max', sisa); labelMaks.text('Maks: ' + sisa + ' Pcs').show();
                } else {
                    qtyInput.val(''); qtyInput.removeAttr('max'); labelMaks.hide();
                }

                if (workerType) {
                    $('.select2-employee option').each(function() {
                        if ($(this).val() === "") return; 
                        $(this).prop('disabled', $(this).data('type') !== workerType);
                    });
                    $('.select2-employee').select2({ width: '100%', placeholder: "-- Pilih Nama Pekerja --", dropdownParent: $('#modalDailyLog') });
                    if ($('.select2-employee').find(':selected').prop('disabled')) $('.select2-employee').val(null).trigger('change');
                }
            });

            $('.select2-employee, #operationSelect').on('change', function() {
                let empId = $('.select2-employee').val(); let opId = $('#operationSelect').val(); let spkId = $('#logSpkId').val(); 
                if (empId && opId && spkId) {
                    let data = { employee_id: empId, operation_id: opId, spk_id: spkId };
                    data['<?= csrf_token() ?>'] = $('input[name="<?= csrf_token() ?>"]').last().val();
                    $.ajax({
                        url: '<?= base_url("production/check_last_wage") ?>', type: 'POST', dataType: 'json', data: data,
                        success: function(res) {
                            if(res.csrf_token) $('input[name="<?= csrf_token() ?>"]').val(res.csrf_token); 
                            if (res.status === 'found') {
                                $('#customWageDiv').slideDown(300);
                                let inputWage = document.querySelector('input[name="custom_wage"]');
                                inputWage.value = res.custom_wage;
                                formatRupiah(inputWage);
                            } else {
                                document.querySelector('input[name="custom_wage"]').value = '';
                                $('#customWageDiv').slideUp();
                            }
                        }
                    });
                }
            });
        });
        openModal('modalDailyLog');
    }

    function addExtraMaterialRow() {
        let container = document.getElementById('extraMaterialContainer');
        let row = document.createElement('div');
        row.className = 'extra-item-grid';
        let options = '<option value="">-- Pilih Emblem/Material --</option>';
        <?php foreach($rmList as $rm): ?>
            options += `<option value="<?= $rm['sku_material'] ?>"><?= esc($rm['material_name']) ?> (Stok: <?= floatval($rm['physical_stock']) ?>)</option>`;
        <?php endforeach; ?>
        row.innerHTML = `
            <div style="display:flex; gap:10px; margin-bottom:10px;">
                <select name="extra_rm_sku[]" class="select2-modal-extra" style="width: 100%;">${options}</select>
                <input type="number" name="extra_rm_qty[]" placeholder="Qty" min="1" style="width:80px; text-align: center; border: 1px solid var(--border-subtle); border-radius: 8px; font-weight: bold; padding: 8px;">
                <button type="button" style="background:var(--prod-danger-soft); color:var(--prod-danger-dark); border:none; border-radius:8px; width:40px; cursor:pointer;" onclick="this.parentElement.remove()"><i class="ph-bold ph-trash"></i></button>
            </div>
        `;
        container.appendChild(row);
        $(row).find('.select2-modal-extra').select2({ width: '100%', dropdownParent: $('#modalDailyLog') });
    }

    function validateWageInput(input) { formatRupiah(input); }
    function formatRupiah(angka) {
        if (!angka) return;
        let number_string = angka.value.replace(/[^,\d]/g, '').toString(), split = number_string.split(','), sisa = split[0].length % 3, rupiah = split[0].substr(0, sisa), ribuan = split[0].substr(sisa).match(/\d{3}/gi);
        if (ribuan) { let separator = sisa ? '.' : ''; rupiah += separator + ribuan.join('.'); }
        rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah; angka.value = rupiah;
    }
</script>

<?= $this->endSection() ?>