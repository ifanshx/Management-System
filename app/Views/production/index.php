<?= $this->extend('layout/template') ?>
<?= $this->section('content') ?>

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
?>

<style>
    /* VARIABLES & ANIMATIONS */
    :root {
        --prod-primary: #2563eb; --prod-primary-dark: #1d4ed8; --prod-primary-soft: rgba(37, 99, 235, 0.1);
        --prod-accent: #8b5cf6; --prod-accent-dark: #7c3aed; --prod-accent-soft: rgba(139, 92, 246, 0.1);
        --prod-success: #10b981; --prod-success-soft: rgba(16, 185, 129, 0.1);
        --prod-warning: #f59e0b; --prod-warning-dark: #d97706; --prod-warning-soft: rgba(245, 158, 11, 0.1);
        --prod-danger: #ef4444; --prod-danger-soft: rgba(239, 68, 68, 0.1);
        --prod-info: #0ea5e9; --prod-info-soft: rgba(14, 165, 233, 0.1);
        --prod-glass-bg: rgba(255, 255, 255, 0.95);
        --prod-shadow-soft: 0 10px 40px -10px rgba(0,0,0,0.05);
        --prod-shadow-hover: 0 15px 35px -5px rgba(0,0,0,0.1);
        --transition-smooth: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    }
    
    html.dark { --prod-glass-bg: rgba(30, 41, 59, 0.95); }
    @keyframes slideInUp { from { opacity: 0; transform: translateY(15px); } to { opacity: 1; transform: translateY(0); } }
    @keyframes pulseSoft { 0% { box-shadow: 0 0 0 0 rgba(245, 158, 11, 0.4); } 70% { box-shadow: 0 0 0 8px rgba(245, 158, 11, 0); } 100% { box-shadow: 0 0 0 0 rgba(245, 158, 11, 0); } }

    /* HEADER & TITLE SECTION */
    .prod-page-header { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 30px; width: 100%; flex-wrap: wrap; gap: 20px;} 
    .prod-page-title { display: flex; align-items: center; gap: 15px; animation: slideInUp 0.5s ease-out forwards; flex-wrap: wrap;}
    .prod-title-icon { width: 56px; height: 56px; border-radius: 16px; background: linear-gradient(135deg, var(--prod-primary), var(--prod-primary-dark)); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 28px; box-shadow: 0 10px 25px -5px rgba(37, 99, 235, 0.5); flex-shrink: 0;}
    .prod-title-text { display: flex; flex-direction: column; gap: 4px; }
    .prod-title-text h1 { font-size: 28px; font-weight: 900; color: var(--text-main); margin: 0; letter-spacing: -0.5px; line-height: 1.2;}
    .prod-title-text p { font-size: 13px; color: var(--text-muted); font-weight: 600; margin: 0; line-height: 1.5;}

    .prod-actions { display: flex; gap: 10px; flex-wrap: wrap; align-items: center; }
    .btn-create-bom { background: var(--prod-glass-bg); color: var(--prod-accent); border: 1px solid var(--border-subtle); padding: 12px 20px; border-radius: 14px; font-size: 13px; font-weight: 800; text-decoration: none; transition: var(--transition-smooth); display: inline-flex; align-items: center; justify-content: center; gap: 8px; box-shadow: var(--prod-shadow-soft);}
    .btn-create-bom:hover { border-color: var(--prod-accent); background: var(--prod-accent-soft); transform: translateY(-2px);}
    
    .btn-open-spk { background: linear-gradient(135deg, var(--prod-primary), var(--prod-primary-dark)); color: #fff; border: none; padding: 12px 20px; border-radius: 14px; font-size: 13px; font-weight: 800; cursor: pointer; transition: var(--transition-smooth); display: inline-flex; align-items: center; justify-content: center; gap: 8px; box-shadow: 0 10px 25px -5px rgba(37, 99, 235, 0.4);}
    .btn-open-spk:hover { transform: translateY(-3px); box-shadow: 0 15px 30px -5px rgba(37, 99, 235, 0.5); }

    /* STATS GRID & BENTO CARDS */
    .prod-stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; margin-bottom: 30px; animation: slideInUp 0.6s ease-out forwards;}
    .prod-stat-card { background: var(--prod-glass-bg); backdrop-filter: blur(10px); border: 1px solid var(--border-subtle); padding: 22px; border-radius: 20px; display: flex; align-items: center; gap: 18px; box-shadow: var(--prod-shadow-soft); transition: var(--transition-smooth); }
    .prod-stat-card:hover { transform: translateY(-4px); box-shadow: var(--prod-shadow-hover); border-color: var(--border-subtle);}
    .prod-stat-icon { width: 56px; height: 56px; border-radius: 16px; display: flex; align-items: center; justify-content: center; font-size: 28px; flex-shrink: 0;}
    .prod-stat-info { display: flex; flex-direction: column; gap: 4px; }
    .prod-stat-info h4 { margin: 0; font-size: 11px; color: var(--text-muted); text-transform: uppercase; font-weight: 800; letter-spacing: 0.5px;}
    .prod-stat-info h2 { margin: 0; font-size: 24px; color: var(--text-main); font-weight: 900; font-family: 'Space Mono', monospace; line-height: 1;}

    .prod-bento-card { background: var(--prod-glass-bg); backdrop-filter: blur(10px); border: 1px solid var(--border-subtle); border-radius: 24px; padding: 25px; margin-bottom: 30px; box-shadow: var(--prod-shadow-soft); animation: slideInUp 0.7s ease-out forwards; overflow: hidden;}
    .prod-bento-card.accent-purple { border-top: 6px solid var(--prod-accent); }
    .prod-bento-card.accent-blue { border-top: 6px solid var(--prod-primary); }

    .prod-card-title { font-size: 16px; font-weight: 900; color: var(--text-main); margin-bottom: 20px; display: flex; align-items: center; justify-content: space-between; gap: 12px; border-bottom: 2px dashed var(--border-subtle); padding-bottom: 15px; flex-wrap: wrap;}
    .prod-card-title i { padding: 8px; border-radius: 10px; font-size: 20px;}

    /* TABLES & VISUAL PROGRESS BAR */
    .prod-table-responsive { width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch; border-radius: 14px; border: 1px solid var(--border-subtle); background: var(--bg-surface); margin-bottom: 10px;}
    .prod-table { width: 100%; border-collapse: collapse; white-space: nowrap; min-width: 600px; }
    .prod-table th { text-align: left; padding: 14px 18px; font-size: 11px; font-weight: 900; text-transform: uppercase; color: var(--text-muted); background: var(--bg-input); border-bottom: 2px solid var(--border-subtle); letter-spacing: 0.5px;}
    .prod-table td { padding: 14px 18px; border-bottom: 1px dashed var(--border-subtle); color: var(--text-main); font-size: 13px; font-weight: 700; vertical-align: middle; transition: 0.2s;}
    .prod-table tr:last-child td { border-bottom: none; }
    .prod-table tr:hover td { background: var(--bg-soft); }
    
    .spk-badge-po { font-family: 'Space Mono', monospace; font-size: 11px; color: var(--prod-accent); background: var(--prod-accent-soft); padding: 4px 8px; border-radius: 6px; font-weight: 900; border: 1px dashed rgba(139, 92, 246, 0.3); display: inline-block;}
    .spk-badge { font-family: 'Space Mono', monospace; font-size: 11px; color: var(--prod-primary); background: var(--prod-primary-soft); padding: 4px 8px; border-radius: 6px; font-weight: 900; border: 1px dashed rgba(37, 99, 235, 0.3); display: inline-block;}
    .note-badge { font-family: 'Plus Jakarta Sans', sans-serif; font-size: 10px; color: var(--pink); background: rgba(236, 72, 153, 0.1); padding: 4px 8px; border-radius: 6px; font-weight: 900; border: 1px dashed rgba(236, 72, 153, 0.3); display: inline-block;}
    
    .prod-status-badge { padding: 6px 10px; border-radius: 8px; font-size: 10px; font-weight: 900; text-transform: uppercase; display: inline-flex; align-items: center; justify-content: center; gap: 4px; border: 1px solid transparent; width: 100%;}
    .s-progress { background: var(--prod-warning-soft); color: var(--prod-warning-dark); border-color: rgba(245, 158, 11, 0.2);}
    .s-completed { background: var(--prod-success-soft); color: var(--prod-success); border-color: rgba(16, 185, 129, 0.2);}

    /* SIMPLIFIED PROGRESS DISPLAY */
    .progress-container { width: 100%; display: flex; align-items: center; justify-content: center;}
    .progress-stats { font-family: 'Space Mono', monospace; font-size: 18px; font-weight: 900; color: var(--text-main); display: flex; align-items: center; gap: 8px;}
    .progress-stats .done { color: var(--prod-success); }
    .progress-stats .divider { color: var(--border-subtle); font-weight: 400; }
    .progress-stats .target { color: var(--text-muted); font-size: 15px;}

    .prod-action-group { display: flex; gap: 6px; justify-content: center; flex-wrap: wrap;}
    .btn-action-sm { padding: 8px 12px; border-radius: 8px; font-size: 12px; font-weight: 900; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; gap: 6px; text-decoration: none; border: 1px solid transparent; transition: var(--transition-smooth);}
    .btn-action-sm:hover { transform: translateY(-2px);}
    .btn-complete { background: linear-gradient(135deg, var(--prod-warning), var(--prod-warning-dark)); color: #fff; box-shadow: 0 4px 10px rgba(245,158,11,0.3); border: none; }
    .btn-complete:hover { transform: translateY(-3px); box-shadow: 0 6px 15px rgba(245,158,11,0.4);}
    .btn-print { background: var(--prod-accent-soft); color: var(--prod-accent); border-color: rgba(139, 92, 246, 0.2); }
    .btn-print:hover { background: var(--prod-accent); color: #fff;}
    .btn-edit { background: var(--prod-info-soft); color: var(--prod-info); border-color: rgba(14, 165, 233, 0.2); }
    .btn-edit:hover { background: var(--prod-info); color: #fff;}
    .btn-del { background: var(--prod-danger-soft); color: var(--prod-danger); border-color: rgba(239, 68, 68, 0.2); }
    .btn-del:hover { background: var(--prod-danger); color: #fff;}

    /* MODALS & FORMS - REDESIGN UX PEKERJA */
    .prod-modal-overlay { position: fixed; inset: 0; background: rgba(15, 23, 42, 0.8); backdrop-filter: blur(8px); z-index: 1000; display: none; justify-content: center; align-items: center; opacity: 0; transition: opacity 0.3s ease; padding: 20px;}
    .prod-modal-overlay.active { display: flex; opacity: 1; }
    
    .prod-modal-box { background: #f8fafc; border-radius: 24px; width: 100%; max-width: 560px; padding: 0; transform: scale(0.95) translateY(20px); transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1); box-shadow: 0 30px 60px -15px rgba(0,0,0,0.5); margin: auto; border: 1px solid var(--border-subtle); display: flex; flex-direction: column; max-height: calc(100vh - 40px); overflow: hidden; }
    .prod-modal-overlay.active .prod-modal-box { transform: scale(1) translateY(0); }
    
    .prod-modal-header { background: #fff; padding: 20px 25px; border-bottom: 1px solid var(--border-subtle); display: flex; flex-direction: column; position: sticky; top: 0; z-index: 10; border-radius: 24px 24px 0 0;}
    .prod-btn-close { background: var(--bg-input); border: 1px solid var(--border-subtle); width: 36px; height: 36px; border-radius: 50%; font-size: 16px; color: var(--text-muted); cursor: pointer; display: flex; align-items: center; justify-content: center; transition: 0.2s; flex-shrink: 0;}
    .prod-btn-close:hover { background: var(--prod-danger); color: #fff; transform: rotate(90deg); border-color: var(--prod-danger);}
    
    .prod-modal-body { padding: 20px; overflow-y: auto; flex: 1;}
    
    /* ZONA CARD STYLE UX */
    .modal-zone { background: #ffffff; border: 1px solid var(--border-subtle); border-radius: 16px; padding: 20px; margin-bottom: 16px; box-shadow: 0 4px 15px rgba(0,0,0,0.02); }
    .modal-zone-title { font-size: 12px; font-weight: 900; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 15px; display: flex; align-items: center; gap: 8px; border-bottom: 1px dashed var(--border-subtle); padding-bottom: 10px; }
    
    .prod-form-group { margin-bottom: 15px; position: relative; width: 100%;}
    .prod-form-group label { display: flex; justify-content: space-between; font-size: 12px; font-weight: 800; color: var(--text-main); margin-bottom: 8px; flex-wrap: wrap; gap: 5px;}
    
    /* CUSTOM STEPPER FOR QUANTITY */
    .stepper-container { display: flex; align-items: center; justify-content: center; gap: 15px; margin-top: 10px; }
    .btn-stepper { background: #fff; border: 2px solid var(--border-subtle); color: var(--prod-warning-dark); width: 56px; height: 56px; border-radius: 16px; font-size: 28px; font-weight: 900; cursor: pointer; transition: 0.2s; display: flex; align-items: center; justify-content: center; user-select: none; box-shadow: 0 4px 10px rgba(0,0,0,0.02); }
    .btn-stepper:hover { background: var(--prod-warning-soft); border-color: var(--prod-warning); color: var(--prod-warning-dark); }
    .btn-stepper:active { transform: scale(0.9); }
    .qty-display { flex: 1; border: 2px solid var(--prod-warning); background: #fff; height: 60px; border-radius: 16px; font-size: 28px; font-weight: 900; color: var(--prod-warning-dark); text-align: center; outline: none; transition: 0.3s; box-shadow: 0 4px 15px rgba(245, 158, 11, 0.15); font-family: 'Space Mono', monospace; }
    .qty-display:focus { box-shadow: 0 0 0 4px var(--prod-warning-soft); border-color: var(--prod-warning-dark); }

    /* SELECT2 CUSTOMIZATION */
    .select2-container--default .select2-selection--single { background: var(--bg-input); border: 1px solid var(--border-subtle); height: auto; min-height: 52px; border-radius: 12px; display: flex; align-items: center; transition: 0.3s; width: 100%;}
    .select2-container--default.select2-container--focus .select2-selection--single { border-color: var(--prod-primary); background: #fff; box-shadow: 0 0 0 4px var(--prod-primary-soft); }
    .select2-container--default .select2-selection--single .select2-selection__rendered { font-weight: 800; font-size: 13px; color: var(--text-main); padding: 10px 16px; width: 100%; white-space: normal; line-height: 1.4;}
    .select2-container--default .select2-selection--single .select2-selection__arrow { height: 50px; right: 12px;}
    .select2-dropdown { border-radius: 16px; border: 1px solid var(--border-subtle); box-shadow: 0 20px 40px rgba(0,0,0,0.15); padding: 10px; background: #fff;}
    .select2-search__field { border-radius: 8px !important; padding: 10px 14px !important; border: 1px solid var(--border-subtle) !important; outline: none; font-family: inherit; font-weight: 700; background: var(--bg-input); color: var(--text-main);}
    .select2-results__option { border-radius: 10px; margin-bottom: 4px; font-weight: 700; font-size: 12px; padding: 12px 14px; color: var(--text-main); word-wrap: break-word; border: 1px solid transparent; transition: 0.2s;}
    .select2-container--default .select2-results__option--highlighted[aria-selected] { background: var(--prod-primary-soft) !important; color: var(--prod-primary-dark) !important; border: 1px solid rgba(37, 99, 235, 0.2);}
    
    .btn-submit-log { width: 100%; background: linear-gradient(135deg, var(--prod-primary), var(--prod-primary-dark)); color: white; border: none; padding: 18px; border-radius: 16px; font-weight: 900; font-size: 16px; display: flex; align-items: center; justify-content: center; gap: 10px; cursor: pointer; transition: 0.3s; margin-top: 10px; box-shadow: 0 8px 25px -5px rgba(37, 99, 235, 0.4); text-transform: uppercase; letter-spacing: 0.5px; position: sticky; bottom: 0; z-index: 20;}
    .btn-submit-log:hover { transform: translateY(-3px); box-shadow: 0 15px 30px -5px rgba(37, 99, 235, 0.5);}
    .btn-submit-log.orange { background: linear-gradient(135deg, var(--prod-warning), var(--prod-warning-dark)); box-shadow: 0 8px 25px -5px rgba(245, 158, 11, 0.4); }
    .btn-submit-log.orange:hover { box-shadow: 0 15px 30px -5px rgba(245, 158, 11, 0.5); }

    .btn-toggle-nego { background: #fff; border: 1px dashed var(--border-subtle); color: var(--text-main); padding: 16px; border-radius: 14px; font-weight: 800; font-size: 13px; cursor: pointer; display: flex; align-items: center; justify-content: space-between; transition: 0.3s; margin-bottom: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.02);}
    .btn-toggle-nego:hover { background: var(--prod-primary-soft); border-color: rgba(37, 99, 235, 0.3); color: var(--prod-primary-dark); }
    
    .prod-input-group { display: flex; align-items: stretch; background: var(--bg-input); border: 1px solid var(--border-subtle); border-radius: 12px; overflow: hidden; transition: 0.3s; width: 100%;}
    .prod-input-group:focus-within { border-color: var(--prod-warning); background: var(--bg-surface); box-shadow: 0 0 0 4px var(--prod-warning-soft);}
    .prod-input-group input { flex: 1; border: none; background: transparent; padding: 12px 16px; font-size: 16px; font-weight: 900; outline: none; width: 100%; color: var(--text-main); min-width: 0;}
    .prod-input-group span { padding: 0 16px; background: var(--bg-soft); font-size: 12px; font-weight: 900; color: var(--text-muted); display: flex; align-items: center; border-left: 1px solid var(--border-subtle); white-space: nowrap;}

    .extra-item-grid { display: grid; grid-template-columns: 1fr auto; gap: 8px; margin-bottom: 10px; align-items: center; background: #f8fafc; border: 1px solid var(--border-subtle); padding: 12px; border-radius: 14px;}
    .extra-item-inputs { display: grid; grid-template-columns: 2fr 1fr; gap: 10px; width: 100%; }
    .btn-del-extra { background: var(--prod-danger-soft); color: var(--prod-danger); border: 1px solid transparent; width: 48px; height: 48px; border-radius: 12px; font-size: 18px; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: 0.3s; flex-shrink: 0;}
    .btn-del-extra:hover { background: var(--prod-danger); color: #fff; transform: scale(1.05); }
    .btn-add-extra { background: var(--prod-success-soft); color: var(--prod-success); border: 1px dashed rgba(16, 185, 129, 0.3); width: 100%; padding: 14px; border-radius: 12px; font-size: 12px; font-weight: 900; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: 0.3s; margin-top: 10px; }
    .btn-add-extra:hover { background: var(--prod-success); color: #fff; border-style: solid;}

    /* SEARCH BAR STYLE */
    .search-bar-wrapper { margin-bottom: 25px; width: 100%; display: flex; justify-content: flex-end;}
    .search-input-group { background: var(--prod-glass-bg); border: 1px solid var(--border-subtle); border-radius: 14px; display: flex; align-items: center; padding: 0 15px; width: 100%; max-width: 400px; box-shadow: var(--prod-shadow-soft); transition: 0.3s;}
    .search-input-group:focus-within { border-color: var(--prod-primary); box-shadow: 0 0 0 4px var(--prod-primary-soft); }
    .search-input-group i { color: var(--prod-primary); font-size: 18px; }
    .search-input-group input { border: none; background: transparent; padding: 14px; width: 100%; outline: none; color: var(--text-main); font-weight: 700; font-size: 14px;}
    
    @media (max-width: 768px) {
        .prod-page-header { flex-direction: column; align-items: flex-start; gap: 15px; margin-bottom: 20px;}
        .prod-actions { width: 100%; display: flex; flex-direction: column; gap: 10px; }
        .btn-create-bom, .btn-open-spk { width: 100%; justify-content: center; }
        .prod-stats-grid { grid-template-columns: 1fr; gap: 15px; margin-bottom: 20px; } 
        .prod-table { min-width: 500px; }
        .prod-table th, .prod-table td { padding: 12px 14px; font-size: 12px;}
        .prod-modal-box { border-radius: 20px 20px 0 0; max-height: 98vh; position: absolute; bottom: 0; width: 100%; }
        .prod-modal-header { padding: 18px 20px; border-radius: 20px 20px 0 0; }
        .prod-modal-body { padding: 15px; }
        .extra-item-inputs { grid-template-columns: 1fr; }
        .btn-del-extra { height: 100%; min-height: 48px; }
        .search-bar-wrapper { justify-content: flex-start; }
        .search-input-group { max-width: 100%; }
    }
</style>

<div class="prod-page-header">
    <div class="prod-page-title">
        <div class="prod-title-icon"><i class="ph-fill ph-factory"></i></div>
        <div class="prod-title-text">
            <h1>Pusat Eksekusi Manufaktur</h1>
            <p>Terbitkan SPK, atur jalur Borongan & Tetap, lalu pantau target.</p>
        </div>
    </div>
    
    <div class="prod-actions">
        <button onclick="openModal('modalRiwayat')" class="btn-create-bom" style="border-color: var(--prod-info); color: var(--prod-info); background: var(--prod-info-soft);">
            <i class="ph-bold ph-clock-counter-clockwise"></i> Riwayat Input Saya
        </button>

        <?php if($userRole === 'admin'): ?>
            <a href="<?= base_url('/production/confirm_logs') ?>" class="btn-create-bom" style="border-color: rgba(245, 158, 11, 0.3); color: var(--prod-warning-dark); background: rgba(245, 158, 11, 0.05); <?= !empty($pendingLogs) ? 'animation: pulseSoft 2s infinite;' : '' ?>">
                <i class="ph-bold ph-bell-ringing"></i> Konfirmasi Setoran
                <?php if(!empty($pendingLogs)): ?>
                    <span style="background: var(--prod-danger); color: #fff; padding: 2px 8px; border-radius: 12px; font-size: 11px; font-weight: 900; margin-left: 5px;"><?= count($pendingLogs) ?></span>
                <?php endif; ?>
            </a>
            <a href="<?= base_url('/production/bom_builder') ?>" class="btn-create-bom"><i class="ph-bold ph-flask"></i> BoM & Routing Studio</a>
            <button onclick="openModal('modalSPK')" class="btn-open-spk"><i class="ph-bold ph-plus-circle"></i> Terbitkan SPK Reguler</button>
        <?php endif; ?>
    </div>
</div>

<div class="prod-stats-grid">
    <div class="prod-stat-card">
        <div class="prod-stat-icon" style="background: var(--prod-primary-soft); color: var(--prod-primary);"><i class="ph-fill ph-clipboard-text"></i></div>
        <div class="prod-stat-info"><h4>Total SPK Berjalan</h4><h2><?= $spkAktif ?> SPK</h2></div>
    </div>
    <div class="prod-stat-card">
        <div class="prod-stat-icon" style="background: var(--prod-success-soft); color: var(--prod-success);"><i class="ph-fill ph-check-circle"></i></div>
        <div class="prod-stat-info"><h4>SPK Selesai (All Time)</h4><h2><?= $spkSelesai ?> SPK</h2></div>
    </div>
    <div class="prod-stat-card">
        <div class="prod-stat-icon" style="background: var(--prod-warning-soft); color: var(--prod-warning);"><i class="ph-fill ph-package"></i></div>
        <div class="prod-stat-info"><h4>Knalpot Jadi (Hari Ini)</h4><h2><?= $totalKnalpotHariIni ?> Pcs</h2></div>
    </div>
</div>

<div class="search-bar-wrapper">
    <div class="search-input-group">
        <i class="ph-bold ph-magnifying-glass"></i>
        <input type="text" id="searchSPK" placeholder="Cari Pemesan, SPK, atau Knalpot..." onkeyup="filterSPK()">
    </div>
</div>

<div class="prod-bento-card accent-purple">
    <div class="prod-card-title">
        <div><i class="ph-fill ph-clock-countdown" style="background: var(--prod-accent-soft); color: var(--prod-accent);"></i> Antrean SPK Pre-Order (Prioritas B2B)</div>
    </div>
    <div class="prod-table-responsive">
        <table class="prod-table" id="tableSPK-PO">
            <thead>
                <tr>
                    <th style="width: 45%;">Target Produk & SPK</th><th style="text-align: center; width: 20%;">Progress Produksi</th>
                    <th style="text-align: center;">Status</th><th style="text-align: center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($spkPreorderGrouped)): ?>
                    <tr class="empty-state"><td colspan="4" style="text-align:center; padding:40px 20px; color:var(--text-muted); font-size:13px; font-weight: 600;"><i class="ph-duotone ph-check-circle" style="font-size: 40px; display: block; margin-bottom:10px; color: var(--prod-success); opacity: 0.5;"></i>Tidak ada antrean pesanan Pre-Order B2B.</td></tr>
                <?php else: ?>
                    <?php foreach($spkPreorderGrouped as $group): ?>
                        <tr class="group-header" style="background: var(--prod-accent-soft); border-top: 1px solid rgba(139, 92, 246, 0.2);">
                            <td colspan="3" style="font-weight: 900; font-size: 13px; color: var(--prod-accent); padding: 12px 20px;"><i class="ph-fill ph-storefront"></i> <?= esc($group['group_name']) ?></td>
                            <td style="text-align: center; padding: 10px;">
                                <div class="prod-action-group">
                                    <?php if($group['so_id'] != 0): ?>
                                        <a href="<?= base_url('production/print_rekap_produksi/'.$group['so_id']) ?>" target="_blank" class="btn-action-sm" style="background: var(--prod-warning-dark); color: #fff; box-shadow: 0 4px 10px rgba(245,158,11,0.3);" title="Cetak Ringkasan Target A4"><i class="ph-bold ph-list-numbers"></i> Rekap</a>
                                        <a href="<?= base_url('production/print_spk_batch/'.$group['so_id']) ?>" target="_blank" class="btn-action-sm" style="background: var(--prod-accent); color: #fff; box-shadow: 0 4px 10px rgba(139,92,246,0.3);" title="Cetak Semua Kertas SPK"><i class="ph-bold ph-printer"></i> Cetak SPK</a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php foreach($group['spks'] as $wo): ?>
                        <?php 
                            $selesai = $db->table('production_logs')->where('spk_number', $wo['spk_number'])->where('is_final_step', 1)->whereIn('status', ['Approved','Pending'])->selectSum('qty_produced')->get()->getRowArray()['qty_produced'] ?? 0; 
                            $isComplete = $wo['status'] == 'COMPLETED';
                        ?>
                        <tr class="spk-row">
                            <td>
                                <div style="font-size: 13px; font-weight: 900; margin-bottom: 6px; color: var(--text-main); white-space: normal; line-height: 1.4;"><?= esc($wo['recipe_name']) ?></div>
                                <div style="display:flex; gap:6px; align-items:center; flex-wrap:wrap;">
                                    <span style="font-size: 10px; background: var(--bg-input); padding: 4px 8px; border-radius: 6px; border: 1px solid var(--border-subtle); font-family: 'Space Mono', monospace; font-weight: 800; color: var(--text-muted); display: inline-block;">Target: <?= esc($wo['fg_sku']) ?></span>
                                    <span class="spk-badge-po"><?= esc($wo['spk_number']) ?></span>
                                    <?php if(!empty($wo['production_notes'])): ?>
                                        <span class="note-badge"><i class="ph-fill ph-tag"></i> <?= esc($wo['production_notes']) ?></span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <div class="progress-container">
                                    <div class="progress-stats">
                                        <span class="done"><?= $selesai ?></span>
                                        <span class="divider">|</span>
                                        <span class="target"><?= $wo['planned_qty'] ?></span>
                                    </div>
                                </div>
                            </td>
                            <td style="text-align: center;">
                                <?= ($wo['status'] == 'IN_PROGRESS') ? '<span class="prod-status-badge s-progress"><i class="ph-bold ph-spinner-gap"></i> Dikerjakan</span>' : '<span class="prod-status-badge s-completed"><i class="ph-bold ph-check-circle"></i> Selesai</span>' ?>
                            </td>
                            <td style="text-align: center;">
                                <div class="prod-action-group">
                                    <?php if($wo['status'] == 'IN_PROGRESS'): ?><a href="#" onclick="openDailyLogModal(event, <?= $wo['id'] ?>, '<?= esc($wo['spk_number']) ?>', '<?= esc(addslashes($wo['recipe_name']), 'js') ?>')" class="btn-action-sm btn-complete"><i class="ph-bold ph-hammer"></i> Setor</a><?php endif; ?>
                                    <a href="<?= base_url('production/print_spk/'.$wo['id']) ?>" target="_blank" class="btn-action-sm btn-print" title="Cetak SPK Satuan (A4)"><i class="ph-bold ph-printer"></i></a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="prod-bento-card accent-blue">
    <div class="prod-card-title">
        <div><i class="ph-fill ph-kanban" style="background: var(--prod-primary-soft); color: var(--prod-primary);"></i> SPK Produksi Reguler (Stok Gudang)</div>
    </div>
    <div class="prod-table-responsive">
        <table class="prod-table" id="tableSPK-Reguler">
            <thead>
                <tr>
                    <th style="width: 45%;">Target Produk & SPK</th><th style="text-align: center; width: 20%;">Progress Produksi</th>
                    <th style="text-align: center;">Status</th><th style="text-align: center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($spkManual)): ?>
                    <tr class="empty-state"><td colspan="4" style="text-align:center; padding:40px 20px; color:var(--text-muted); font-size:13px; font-weight: 600;"><i class="ph-duotone ph-clipboard-text" style="font-size: 40px; display: block; margin-bottom:10px; opacity:0.3;"></i>Belum ada SPK stok gudang yang aktif.</td></tr>
                <?php else: ?>
                    <?php foreach($spkManual as $wo): ?>
                        <?php 
                            $selesai = $db->table('production_logs')->where('spk_number', $wo['spk_number'])->where('is_final_step', 1)->whereIn('status', ['Approved','Pending'])->selectSum('qty_produced')->get()->getRowArray()['qty_produced'] ?? 0; 
                            $isComplete = $wo['status'] == 'COMPLETED';
                        ?>
                        <tr class="spk-row">
                            <td>
                                <div style="font-size: 13px; font-weight: 900; margin-bottom: 6px; color: var(--text-main); white-space: normal; line-height: 1.4;"><?= esc($wo['recipe_name']) ?></div>
                                <div style="display:flex; gap:6px; align-items:center; flex-wrap:wrap;">
                                    <span style="font-size: 10px; background: var(--bg-input); padding: 4px 8px; border-radius: 6px; border: 1px solid var(--border-subtle); font-family: 'Space Mono', monospace; font-weight: 800; color: var(--text-muted); display: inline-block;">Target: <?= esc($wo['fg_sku']) ?></span>
                                    <span class="spk-badge"><?= esc($wo['spk_number']) ?></span>
                                    <?php if(!empty($wo['production_notes'])): ?>
                                        <span class="note-badge"><i class="ph-fill ph-tag"></i> <?= esc($wo['production_notes']) ?></span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <div class="progress-container">
                                    <div class="progress-stats">
                                        <span class="done"><?= $selesai ?></span>
                                        <span class="divider">|</span>
                                        <span class="target"><?= $wo['planned_qty'] ?></span>
                                    </div>
                                </div>
                            </td>
                            <td style="text-align: center;">
                                <?= ($wo['status'] == 'IN_PROGRESS') ? '<span class="prod-status-badge s-progress"><i class="ph-bold ph-spinner-gap"></i> Dikerjakan</span>' : '<span class="prod-status-badge s-completed"><i class="ph-bold ph-check-circle"></i> Selesai</span>' ?>
                            </td>
                            <td style="text-align: center;">
                                <div class="prod-action-group">
                                    <?php if($wo['status'] == 'IN_PROGRESS'): ?><a href="#" onclick="openDailyLogModal(event, <?= $wo['id'] ?>, '<?= esc($wo['spk_number']) ?>', '<?= esc(addslashes($wo['recipe_name']), 'js') ?>')" class="btn-action-sm btn-complete"><i class="ph-bold ph-hammer"></i> Setor</a><?php endif; ?>
                                    <a href="<?= base_url('production/print_spk/'.$wo['id']) ?>" target="_blank" class="btn-action-sm btn-print" title="Cetak Surat Perintah Kerja (A4)"><i class="ph-bold ph-printer"></i></a>
                                    
                                    <?php if($userRole === 'admin'): ?>
                                    <button type="button" onclick="editSPK(<?= $wo['id'] ?>)" class="btn-action-sm btn-edit" title="Revisi SPK"><i class="ph-bold ph-pencil-simple"></i></button>
                                    <?php if($selesai == 0): ?>
                                        <a href="<?= base_url('production/delete_spk/'.$wo['id']) ?>" onclick="return confirm('Hapus SPK <?= esc($wo['spk_number']) ?> secara permanen?')" class="btn-action-sm btn-del" title="Hapus SPK"><i class="ph-bold ph-trash"></i></a>
                                    <?php endif; endif; ?>
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
    function filterSPK() {
        let input = document.getElementById("searchSPK").value.toLowerCase();
        
        // 1. Filter Tabel Reguler
        let regRows = document.querySelectorAll("#tableSPK-Reguler tbody tr:not(.empty-state)");
        regRows.forEach(row => {
            row.style.display = row.textContent.toLowerCase().includes(input) ? "" : "none";
        });

        // 2. Filter Tabel Pre-Order (dengan logika Grup Toko)
        let poTbody = document.querySelector("#tableSPK-PO tbody");
        if(poTbody) {
            let poRows = poTbody.querySelectorAll("tr:not(.empty-state)");
            
            // Evaluasi awal setiap baris
            poRows.forEach(row => {
                if(row.classList.contains('group-header')) {
                    row.dataset.matches = row.textContent.toLowerCase().includes(input) ? "true" : "false";
                    row.dataset.hasVisibleChild = "false";
                } else {
                    let matches = row.textContent.toLowerCase().includes(input);
                    row.style.display = matches ? "" : "none";
                    
                    let prevHeader = row.previousElementSibling;
                    while(prevHeader && !prevHeader.classList.contains('group-header')) {
                        prevHeader = prevHeader.previousElementSibling;
                    }
                    if(prevHeader && matches) {
                        prevHeader.dataset.hasVisibleChild = "true";
                    }
                }
            });
            
            // Terapkan visibility pada Header dan Anak-anaknya
            poRows.forEach(row => {
                if(row.classList.contains('group-header')) {
                    if(row.dataset.matches === "true" || row.dataset.hasVisibleChild === "true") {
                        row.style.display = "";
                    } else {
                        row.style.display = "none";
                    }
                } else {
                    let prevHeader = row.previousElementSibling;
                    while(prevHeader && !prevHeader.classList.contains('group-header')) {
                        prevHeader = prevHeader.previousElementSibling;
                    }
                    if(prevHeader && prevHeader.dataset.matches === "true") {
                        row.style.display = "";
                    }
                }
            });
        }
    }
</script>

<div class="prod-modal-overlay" id="modalRiwayat">
    <div class="prod-modal-box" style="border-top: 6px solid var(--prod-info); max-width: 800px;">
        <div class="prod-modal-header">
            <h3 style="margin:0; font-size: 16px; display: flex; align-items: center; gap: 10px;">
                <div style="background: rgba(14, 165, 233, 0.1); color: var(--prod-info); width: 34px; height: 34px; border-radius: 8px; display: flex; align-items: center; justify-content: center;"><i class="ph-bold ph-clock-counter-clockwise"></i></div> 
                Riwayat Setoran Pekerjaan Saya
            </h3>
            <button class="prod-btn-close" onclick="closeModal('modalRiwayat')" type="button"><i class="ph-bold ph-x"></i></button>
        </div>
        <div class="prod-modal-body">
            <div class="prod-table-responsive">
                <table class="prod-table">
                    <thead>
                        <tr>
                            <th style="width: 15%;">Waktu</th>
                            <th style="width: 45%;">SPK & Tahapan Kerja</th>
                            <th style="text-align: center; width: 10%;">Qty</th>
                            <th style="text-align: center; width: 15%;">Status</th>
                            <th style="text-align: center; width: 15%;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($myLogs)): ?>
                            <tr><td colspan="5" style="text-align:center; padding:30px; color:var(--text-muted); font-size:12px; font-weight:600;"><i class="ph-fill ph-empty" style="font-size:30px; display:block; margin-bottom:10px; opacity:0.3;"></i>Belum ada riwayat setoran pekerjaan.</td></tr>
                        <?php else: ?>
                            <?php foreach($myLogs as $log): ?>
                                <tr>
                                    <td style="font-size:11px; color:var(--text-muted); font-weight:800;"><?= date('d/m/Y H:i', strtotime($log['production_date'])) ?></td>
                                    <td>
                                        <div style="font-size:12px; font-weight:900; color:var(--text-main); margin-bottom:4px;"><?= esc($log['spk_number']) ?></div>
                                        <div style="font-size:11px; color:var(--prod-primary); font-weight:800;"><i class="ph-bold ph-caret-right"></i> <?= esc($log['operation_name']) ?></div>
                                    </td>
                                    <td style="text-align: center; font-family:'Space Mono', monospace; font-weight:900; color:var(--prod-warning-dark); font-size:14px;"><?= $log['qty_produced'] ?></td>
                                    <td style="text-align: center;">
                                        <?php if($log['status'] === 'Approved'): ?>
                                            <span style="background:var(--prod-success-soft); color:var(--prod-success); padding:4px 8px; border-radius:6px; font-size:10px; font-weight:900; display:inline-block;">DISETUJUI</span>
                                        <?php elseif($log['status'] === 'Rejected'): ?>
                                            <span style="background:var(--prod-danger-soft); color:var(--prod-danger); padding:4px 8px; border-radius:6px; font-size:10px; font-weight:900; display:inline-block;">DITOLAK</span>
                                        <?php else: ?>
                                            <span style="background:var(--bg-input); color:var(--text-muted); padding:4px 8px; border-radius:6px; font-size:10px; font-weight:900; display:inline-block; border: 1px solid var(--border-subtle);">MENUNGGU</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="text-align: center;">
                                        <?php if($log['status'] !== 'Approved'): ?>
                                            <a href="<?= base_url('production/delete_log/'.$log['id']) ?>" onclick="return confirm('Batalkan dan hapus setoran ini?')" class="btn-action-sm btn-del" title="Hapus Setoran"><i class="ph-bold ph-trash"></i></a>
                                        <?php else: ?>
                                            <i class="ph-fill ph-lock-key" style="color:var(--border-subtle); font-size:18px;" title="Data Terkunci (Sudah Masuk Jurnal Akuntansi)"></i>
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

<div class="prod-modal-overlay" id="modalEditSPK">
    <div class="prod-modal-box" style="border-top: 6px solid var(--prod-info);">
        <div class="prod-modal-header">
            <h3 style="margin:0; font-size: 16px; display: flex; align-items: center; gap: 10px;">
                <div style="background: rgba(14, 165, 233, 0.1); color: var(--prod-info); width: 34px; height: 34px; border-radius: 8px; display: flex; align-items: center; justify-content: center;"><i class="ph-bold ph-pencil-simple"></i></div> 
                Revisi SPK Reguler
            </h3>
            <button class="prod-btn-close" onclick="closeModal('modalEditSPK')" type="button"><i class="ph-bold ph-x"></i></button>
        </div>
        <div class="prod-modal-body">
            <form action="<?= base_url('/production/update_spk') ?>" method="post">
                <?= csrf_field() ?>
                <input type="hidden" name="spk_id" id="edit_spk_id">
                
                <div class="modal-zone">
                    <div class="prod-form-group">
                        <label>Resep / SOP Pembuatan</label>
                        <select name="bom_id" id="edit_bom_id" class="prod-form-control select2-edit-modal" required style="width: 100%;">
                            <option value=""></option>
                            <?php foreach($boms as $b): ?>
                                <option value="<?= $b['id'] ?>"><?= esc($b['recipe_name']) ?> [Target: <?= esc($b['fg_sku']) ?>]</option>
                            <?php endforeach; ?>
                        </select>
                        <div id="editBomWarning" style="font-size:10.5px; color:var(--prod-danger); margin-top:6px; display:none; background: rgba(239, 68, 68, 0.05); padding: 8px; border-radius: 8px; border: 1px dashed rgba(239, 68, 68, 0.3);">
                            <i class="ph-bold ph-lock-key"></i> Resep dikunci karena SPK sudah memiliki riwayat setoran.
                        </div>
                    </div>
                    
                    <div class="prod-form-group" style="margin-bottom: 0;">
                        <label>Target Kuantitas Baru (Pcs)</label>
                        <div class="prod-input-group" style="border-color: var(--prod-info);">
                            <input type="number" name="planned_qty" id="edit_planned_qty" placeholder="10" required min="1" autocomplete="off" style="color: var(--prod-info); font-size: 20px; text-align: center; font-family: 'Space Mono'; padding: 12px;">
                            <span style="border-left-color: rgba(14, 165, 233, 0.2); background: rgba(14, 165, 233, 0.05); color: var(--prod-info);">Pcs</span>
                        </div>
                    </div>
                </div>
                
                <button type="submit" class="btn-submit-log info" style="background: linear-gradient(135deg, var(--prod-info), #0284c7); box-shadow: 0 8px 25px -5px rgba(14, 165, 233, 0.4);"><i class="ph-bold ph-floppy-disk" style="font-size: 18px;"></i> Simpan Perubahan</button>
            </form>
        </div>
    </div>
</div>

<div class="prod-modal-overlay" id="modalDailyLog">
    <div class="prod-modal-box" style="border-top: 6px solid var(--prod-warning);">
        
        <div class="prod-modal-header" style="flex-direction: column; align-items: flex-start; gap: 12px; padding-bottom: 15px;">
            <div style="display: flex; justify-content: space-between; width: 100%; align-items: center;">
                 <h3 style="margin:0; font-size: 16px; display: flex; align-items: center; gap: 10px;">
                    <div style="background: var(--prod-warning-soft); color: var(--prod-warning-dark); width: 34px; height: 34px; border-radius: 8px; display: flex; align-items: center; justify-content: center;"><i class="ph-bold ph-hammer"></i></div> 
                    Setoran Pekerjaan Anda
                </h3>
                <button class="prod-btn-close" onclick="closeModal('modalDailyLog')" type="button"><i class="ph-bold ph-x"></i></button>
            </div>
            
            <div style="display: flex; gap: 8px; align-items: center; background: var(--bg-input); padding: 10px 14px; border-radius: 10px; width: 100%; border: 1px dashed var(--border-subtle); margin:0;">
                <span id="displaySpkNumber" style="font-family: 'Space Mono', monospace; font-size: 12px; font-weight: 900; color: var(--prod-warning-dark);"></span>
                <span style="color: var(--text-muted);">|</span>
                <span id="displayProductName" style="font-size: 12px; font-weight: 800; color: var(--text-main); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"></span>
            </div>
        </div>

        <div class="prod-modal-body">
            <form id="formDailyLog" action="<?= base_url('production/add_production_log') ?>" method="post">
                <?= csrf_field() ?>
                <input type="hidden" name="spk_id" id="logSpkId">
                
                <div class="modal-zone">
                    <div class="modal-zone-title"><i class="ph-bold ph-list-numbers" style="color:var(--prod-primary); font-size: 16px;"></i> LANGKAH 1: Pilih Tahapan Kerja</div>
                    <select name="operation_id" id="operationSelect" class="prod-form-control" required style="width: 100%;">
                        <option value="">-- Sedang Memuat Data... --</option>
                    </select>
                </div>

                <div class="modal-zone">
                    <div class="modal-zone-title"><i class="ph-bold ph-user-circle" style="color:var(--prod-info); font-size: 16px;"></i> LANGKAH 2: Konfirmasi Data Diri & Hasil</div>
                    
                    <div class="prod-form-group" style="margin-bottom: 20px;">
                        <label style="color: var(--text-muted); font-size: 10px;">PEKERJA YANG MENYETOR</label>
                        <select name="employee_id" class="prod-form-control select2-employee" required style="width: 100%;">
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

                    <div class="prod-form-group" style="margin-bottom: 0;">
                        <label style="justify-content: center; font-size: 11px; text-transform: uppercase; color: var(--text-muted); margin-bottom: 10px;">Jumlah yang Diselesaikan <span id="maxQtyLabel" style="color: var(--prod-danger); display:none; background: rgba(239,68,68,0.1); padding: 2px 6px; border-radius: 4px;"></span></label>
                        <div class="stepper-container">
                            <button type="button" class="btn-stepper" onclick="decrementQty()">-</button>
                            <input type="number" name="qty_produced" id="qtyInput" class="qty-display" placeholder="0" required min="1" autocomplete="off">
                            <button type="button" class="btn-stepper" onclick="incrementQty()">+</button>
                        </div>
                    </div>
                </div>

                <div class="btn-toggle-nego" onclick="$('#customWageDiv').slideToggle(300, function() { document.querySelector('#modalDailyLog .prod-modal-body').scrollTo({ top: 1000, behavior: 'smooth' }); });">
                    <span style="display:flex; align-items:center; gap:8px; color: var(--text-muted);">
                        <i class="ph-bold ph-dots-three-circle" style="font-size: 18px; color: var(--prod-primary);"></i> 
                        <span>Klik untuk opsi Tarif Tambahan / Emblem</span>
                    </span>
                    <i class="ph-bold ph-caret-down"></i>
                </div>
                
                <div id="customWageDiv" style="display: none; background: #fff; padding: 20px; border-radius: 16px; border: 1px solid var(--border-subtle); margin-bottom: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.02);">
                    
                    <div style="background: var(--prod-primary-soft); padding: 12px; border-radius: 10px; margin-bottom: 15px; font-size: 11px; color: var(--prod-primary-dark); font-weight: 700; border: 1px solid rgba(37,99,235,0.2); line-height: 1.4;">
                        <i class="ph-fill ph-lightbulb"></i> <b>Memori Cerdas:</b> Jika tarif tukang ini beda dari standar resep motor ini (karena Junior/Senior), silakan diisi. Sistem akan mengingatnya saat dia setor lagi!
                    </div>

                    <div class="prod-form-group" style="margin-bottom: 15px;">
                        <label>Tarif Khusus per Pcs <span id="standarTarifLabel" style="color: var(--prod-danger); font-size: 10px; font-weight: 900; background: rgba(239, 68, 68, 0.1); padding: 2px 6px; border-radius: 4px;"></span></label>
                        <div class="prod-input-group"><span style="color:var(--prod-danger); border-right:1px solid var(--border-subtle); border-left:none;">Rp</span><input type="text" name="custom_wage" placeholder="Kosongkan jika standar" onkeyup="validateWageInput(this)" style="color: var(--prod-danger); font-family: monospace;"></div>
                        <div id="wageValidator" style="font-size: 10px; font-weight: 800; margin-top: 6px;"></div> 
                    </div>

                    <div class="prod-form-group" style="margin-bottom: 20px;">
                        <label>Biaya Overhead Pabrik Tambahan</label>
                        <div class="prod-input-group"><span style="color:var(--prod-accent); border-right:1px solid var(--border-subtle); border-left:none;">Rp</span><input type="text" name="overhead_cost" placeholder="0" onkeyup="formatRupiah(this)" style="color: var(--prod-accent); font-family: monospace;"></div>
                    </div>

                    <div style="border-top: 1px dashed var(--border-subtle); padding-top: 15px;">
                        <label style="font-size: 11px; font-weight: 900; color: var(--prod-success); margin-bottom: 10px; display: block; text-transform: uppercase;">
                            <i class="ph-fill ph-plus-circle"></i> Tambahan Bahan Fisik (Emblem/Stiker)
                        </label>
                        
                        <div id="extraMaterialContainer"></div>
                        
                        <button type="button" class="btn-add-extra" onclick="addExtraMaterialRow()">
                            + Tambah Emblem/Stiker
                        </button>
                    </div>
                </div>

                <button type="submit" id="btnSubmitLog" class="btn-submit-log orange"><i class="ph-bold ph-paper-plane-tilt" style="font-size: 20px;"></i> KIRIM SETORAN SEKARANG</button>
            </form>
        </div>
    </div>
</div>

<script>
    let userEmpId = '<?= $userEmpId ?>';
    let userRole = '<?= $userRole ?>';
    let userSpecialty = '<?= strtolower($userSpecialty) ?>';

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

    function addExtraMaterialRow() {
        let container = document.getElementById('extraMaterialContainer');
        let row = document.createElement('div');
        row.className = 'extra-item-grid';
        
        let options = '<option value="">-- Pilih Emblem/Material --</option>';
        <?php foreach($rmList as $rm): ?>
            options += `<option value="<?= $rm['sku_material'] ?>"><?= esc($rm['material_name']) ?> (Stok: <?= floatval($rm['physical_stock']) ?>)</option>`;
        <?php endforeach; ?>

        row.innerHTML = `
            <div class="extra-item-inputs">
                <select name="extra_rm_sku[]" class="prod-form-control select2-modal-extra" style="width: 100%;">${options}</select>
                <input type="number" name="extra_rm_qty[]" class="prod-form-control" placeholder="Qty" min="1" style="text-align: center; font-family: 'Space Mono'; font-size: 16px; font-weight: bold;">
            </div>
            <button type="button" class="btn-del-extra" onclick="this.parentElement.remove()"><i class="ph-bold ph-trash"></i></button>
        `;
        
        container.appendChild(row);
        
        $(row).find('.select2-modal-extra').select2({
            placeholder: "-- Pilih Emblem/Material --", allowClear: true, width: '100%', dropdownParent: $('#modalDailyLog')
        });
        
        document.querySelector('#modalDailyLog .prod-modal-body').scrollTo({ top: 1500, behavior: 'smooth' });
    }

    $(document).ready(function() {
        $('.select2-employee').select2({ width: '100%', placeholder: "-- Pilih Nama Anda --", dropdownParent: $('#modalDailyLog') });
        $('.select2-modal').select2({ width: '100%', placeholder: "-- Silakan Pilih Resep / SOP --", dropdownParent: $('#modalSPK') });
        $('.select2-edit-modal').select2({ width: '100%', placeholder: "-- Silakan Pilih Resep / SOP --", dropdownParent: $('#modalEditSPK') });

        $('#operationSelect').select2({
            width: '100%', placeholder: "-- Pilih Tahapan Yang Dikerjakan --", dropdownParent: $('#modalDailyLog'),
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
            } else {
                labelStandar.text('');
                labelStandar.hide();
            }
            
            validateWageInput(document.querySelector('input[name="custom_wage"]'));

            if (sisa !== undefined && sisa > 0) {
                qtyInput.val(sisa); 
                qtyInput.attr('max', sisa); 
                labelMaks.text('Maks: ' + sisa + ' Pcs').show();
            } else {
                qtyInput.val(''); 
                qtyInput.removeAttr('max'); 
                labelMaks.hide();
            }

            if (workerType) {
                $('.select2-employee option').each(function() {
                    if ($(this).val() === "") return; 
                    let empType = $(this).data('type');
                    if (empType === workerType) {
                        $(this).prop('disabled', false); 
                    } else {
                        $(this).prop('disabled', true);  
                    }
                });
                
                $('.select2-employee').select2({ 
                    width: '100%', 
                    placeholder: "-- Pilih Nama Anda --", 
                    dropdownParent: $('#modalDailyLog') 
                });
                
                if ($('.select2-employee').find(':selected').prop('disabled')) {
                    $('.select2-employee').val(null).trigger('change');
                }
            }
        });

        // AJAX CSRF AUTO SYNC
        $('.select2-employee, #operationSelect').on('change', function() {
            let empId = $('.select2-employee').val();
            let opId = $('#operationSelect').val();
            let spkId = $('#logSpkId').val(); 
            let csrfName = '<?= csrf_token() ?>';
            let csrfHash = $('input[name="<?= csrf_token() ?>"]').last().val();

            if (empId && opId && spkId) {
                let data = { employee_id: empId, operation_id: opId, spk_id: spkId };
                data[csrfName] = csrfHash;

                $.ajax({
                    url: '<?= base_url("production/check_last_wage") ?>',
                    type: 'POST',
                    dataType: 'json',
                    data: data,
                    success: function(res) {
                        if(res.csrf_token) { 
                            $('input[name="<?= csrf_token() ?>"]').val(res.csrf_token); 
                        }

                        if (res.status === 'found') {
                            $('#customWageDiv').slideDown(300, function() {
                                document.querySelector('#modalDailyLog .prod-modal-body').scrollTo({ top: 1000, behavior: 'smooth' });
                            });
                            
                            let inputWage = document.querySelector('input[name="custom_wage"]');
                            inputWage.value = res.custom_wage;
                            validateWageInput(inputWage); 
                            
                            Swal.fire({
                                toast: true, position: 'top-end', icon: 'info',
                                title: 'Tarif Khusus Diaktifkan!',
                                text: 'Sistem mengingat tarif tukang ini.',
                                showConfirmButton: false, timer: 4500
                            });
                        } else if (res.status === 'not_found') {
                            let inputWage = document.querySelector('input[name="custom_wage"]');
                            inputWage.value = '';
                            $('#wageValidator').html('');
                            $('#customWageDiv').slideUp();
                        }
                    },
                    error: function(xhr) {
                        if (xhr.status === 403 || (xhr.responseText && xhr.responseText.includes('not allowed'))) {
                            Swal.fire({
                                icon: 'info',
                                title: 'Sinkronisasi Keamanan',
                                text: 'Sistem menyelaraskan ulang sesi keamanan Anda. Mohon tunggu...',
                                showConfirmButton: false,
                                timer: 2000,
                                customClass: { popup: 'swal2-custom-radius' }
                            }).then(() => {
                                window.location.reload();
                            });
                        }
                    }
                });
            }
        });
    });

    function validateWageInput(input) {
        if(!input) return;
        formatRupiah(input); 
        
        let customVal = parseInt(input.value.replace(/\./g, '')) || 0;
        let baseWage = parseInt($('#operationSelect').find(':selected').data('wage')) || 0;
        let validator = $('#wageValidator');
        
        if(customVal === 0 || customVal === baseWage) {
            validator.html('');
            return;
        }

        let diff = customVal - baseWage;
        if(diff < 0) {
            validator.html(`<span style="color:var(--prod-success);"><i class="ph-bold ph-trend-down"></i> Lebih murah Rp ${Math.abs(diff).toLocaleString('id-ID')} dari standar.</span>`);
        } else {
            validator.html(`<span style="color:var(--prod-danger);"><i class="ph-bold ph-warning"></i> AWAS OVERBUDGET! Lebih mahal Rp ${diff.toLocaleString('id-ID')} dari standar!</span>`);
        }
    }

    function openModal(modalId) { document.getElementById(modalId).classList.add('active'); document.body.style.overflow = 'hidden'; }
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
        }
    }
    
    function formatRupiah(angka) {
        if (!angka) return;
        let number_string = angka.value.replace(/[^,\d]/g, '').toString(), split = number_string.split(','), sisa = split[0].length % 3, rupiah = split[0].substr(0, sisa), ribuan = split[0].substr(sisa).match(/\d{3}/gi);
        if (ribuan) { let separator = sisa ? '.' : ''; rupiah += separator + ribuan.join('.'); }
        rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah; angka.value = rupiah;
    }

    function editSPK(id) {
        fetch("<?= base_url('/production/get_spk/') ?>" + id, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(res => res.json())
        .then(res => {
            if(res.status === 'success') {
                document.getElementById('edit_spk_id').value = res.data.id;
                document.getElementById('edit_planned_qty').value = res.data.planned_qty;
                
                document.getElementById('edit_planned_qty').min = res.data.completed_qty > 0 ? res.data.completed_qty : 1; 
                
                $('#edit_bom_id').val(res.data.bom_id).trigger('change');

                if(parseInt(res.data.completed_qty) > 0) {
                    $('#edit_bom_id').prop('disabled', true);
                    document.getElementById('editBomWarning').style.display = 'block';
                } else {
                    $('#edit_bom_id').prop('disabled', false);
                    document.getElementById('editBomWarning').style.display = 'none';
                }

                openModal('modalEditSPK');
            } else {
                Swal.fire('Error', res.message || 'SPK tidak ditemukan', 'error');
            }
        })
        .catch(err => {
            Swal.fire('Error', 'Gagal memanggil data SPK dari server.', 'error');
        });
    }

    document.querySelector('#modalEditSPK form').addEventListener('submit', function(e) {
        $('#edit_bom_id').prop('disabled', false); 
    });

    function openDailyLogModal(event, spkId, spkNumber, productName) {
        event.preventDefault();
        document.getElementById('logSpkId').value = spkId;
        
        document.getElementById('displaySpkNumber').innerText = spkNumber;
        document.getElementById('displayProductName').innerText = productName;
        
        document.getElementById('formDailyLog').reset();
        
        $('.select2-employee').val(null).trigger('change'); 
        $('#operationSelect').empty().append('<option value="">-- Sedang memuat tahapan... --</option>');
        $('#qtyInput').val('').removeAttr('max');
        $('#maxQtyLabel').hide();
        $('#customWageDiv').hide();
        $('#wageValidator').html('');
        $('#standarTarifLabel').text('').hide(); 
        $('#hiddenEmpId').remove();
        $('.select2-employee').prop('disabled', false);
        
        let ajaxUrl = '<?= base_url('production/get_operations') ?>' + '/' + spkId;
        
        fetch(ajaxUrl)
            .then(response => {
                if(!response.ok) throw new Error("Koneksi ke Controller Gagal (Error " + response.status + ")");
                return response.json();
            })
            .then(res => {
                let opSelect = $('#operationSelect');
                opSelect.empty().append('<option value="">-- Pilih Tahapan Yang Dikerjakan --</option>');
                
                if(res.status === 'success') {
                    let optionsData = [];
                    let autoSelectId = null;

                    res.data.forEach(op => {
                        let isDone = op.qty_done >= op.qty_target;
                        let sisa = Math.max(0, op.qty_target - op.qty_done);
                        let color = isDone ? 'var(--prod-success)' : 'var(--prod-primary)';
                        let bg = isDone ? 'var(--prod-success-soft)' : 'var(--prod-primary-soft)';
                        let icon = isDone ? 'ph-check-circle' : 'ph-clock-countdown';
                        let statusText = isDone ? 'SELESAI 100%' : `SISA: ${sisa} Pcs`;
                        
                        let finalBadge = (op.is_final_step == 1) ? `<span style="background:var(--prod-warning-soft); color:var(--prod-warning-dark); padding:2px 6px; border-radius:4px; font-size:9px; font-weight:900; margin-left:6px; border:1px solid rgba(245,158,11,0.2);"><i class="ph-bold ph-package"></i> TAHAP FINAL</span>` : '';
                        
                        let workerTypeBadge = (op.worker_type === 'Tetap') ? 
                            `<span style="background:rgba(59, 130, 246, 0.1); color:#3b82f6; padding:2px 6px; border-radius:4px; font-size:9px; font-weight:900; margin-left:6px;"><i class="ph-bold ph-buildings"></i> TETAP</span>` : 
                            `<span style="background:rgba(139, 92, 246, 0.1); color:#8b5cf6; padding:2px 6px; border-radius:4px; font-size:9px; font-weight:900; margin-left:6px;"><i class="ph-bold ph-user"></i> BORONGAN</span>`;

                        let opNameLower = op.operation_name.toLowerCase();
                        let isSpecialtyMatch = false;

                        if (op.specialty_required && op.specialty_required !== '') {
                            if (userSpecialty.toLowerCase().includes(op.specialty_required.toLowerCase())) {
                                isSpecialtyMatch = true;
                            }
                        } else if (!op.specialty_required || op.specialty_required === '') {
                            isSpecialtyMatch = true;
                        }

                        if (isSpecialtyMatch && !isDone && userRole === 'karyawan' && !autoSelectId) {
                            autoSelectId = op.id;
                        }

                        let specialtyBadge = isSpecialtyMatch 
                            ? `<span style="background:var(--prod-success-soft); color:var(--prod-success); padding:2px 6px; border-radius:4px; font-size:9px; font-weight:900; margin-left:6px; border:1px solid rgba(16,185,129,0.3);"><i class="ph-fill ph-star"></i> KEAHLIAN ANDA</span>` 
                            : `<span style="background:var(--prod-danger-soft); color:var(--prod-danger); padding:2px 6px; border-radius:4px; font-size:9px; font-weight:900; margin-left:6px; border:1px solid rgba(239,68,68,0.3);"><i class="ph-bold ph-warning"></i> BUKAN KEAHLIAN ANDA</span>`;

                        let htmlMarkup = `
                            <div style="display:flex; justify-content:space-between; align-items:center; padding:10px 0; border-bottom: 1px dashed var(--border-subtle); flex-wrap:wrap; gap:8px; ${!isSpecialtyMatch && userRole === 'karyawan' ? 'opacity: 0.5;' : ''}">
                                <div style="flex:1; min-width: 150px;">
                                    <div style="font-weight:900; font-size:13px; color:var(--text-main); margin-bottom:6px; white-space:normal; line-height:1.4;">${op.step_order}. ${op.operation_name} ${finalBadge}</div>
                                    <div style="display:flex; gap:4px; flex-wrap:wrap; margin-bottom:4px;">${workerTypeBadge} ${userRole === 'karyawan' ? specialtyBadge : ''}</div>
                                    <div style="font-size:11px; color:var(--text-muted); font-family:'Space Mono', monospace; font-weight:700;"><i class="ph-bold ph-coins"></i> Rp ${parseInt(op.wage_per_piece).toLocaleString('id-ID')} / Pcs</div>
                                </div>
                                <div style="text-align:right; flex-shrink:0;">
                                    <div style="font-size:10px; font-weight:900; color:var(--text-muted); margin-bottom:4px; letter-spacing:0.5px;">PROGRES: <span style="color:var(--text-main); font-size:12px;">${op.qty_done} / ${op.qty_target}</span></div>
                                    <div style="font-size:10px; font-weight:900; color:${color}; background:${bg}; padding:4px 10px; border-radius:6px; display:inline-flex; align-items:center; gap:4px; border:1px solid ${bg};"><i class="ph-bold ${icon}"></i> ${statusText}</div>
                                </div>
                            </div>
                        `;
                        
                        let optionText = `${op.step_order}. ${op.operation_name} ${isDone ? '(SELESAI)' : `(Sisa: ${sisa} Pcs)`}`;
                        
                        optionsData.push({
                            id: op.id, text: optionText, html: htmlMarkup, isDone: isDone, sisa: sisa, wage: op.wage_per_piece, workerType: op.worker_type || 'Borongan', isSpecialtyMatch: isSpecialtyMatch
                        });
                    });

                    if (userRole === 'karyawan') {
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
                    
                    if (autoSelectId) {
                        opSelect.val(autoSelectId).trigger('change');
                    }
                    
                    if (userRole === 'karyawan' && userEmpId) {
                        $('.select2-employee').val(userEmpId).trigger('change');
                        $('.select2-employee').prop('disabled', true);
                        if ($('#hiddenEmpId').length === 0) {
                            $('#formDailyLog').append('<input type="hidden" name="employee_id" id="hiddenEmpId" value="'+userEmpId+'">');
                        }
                    }

                } else {
                    opSelect.append(`<option value="" disabled>❌ ${res.message}</option>`);
                    Swal.fire('Terjadi Kesalahan', res.message, 'error');
                }
            })
            .catch(error => {
                console.error("AJAX Error:", error);
                $('#operationSelect').empty().append('<option value="" disabled>🚨 Error AJAX. Lihat Console.</option>');
                Swal.fire('Koneksi Error', 'Terjadi kesalahan saat menarik data tahapan dari server.', 'error');
            });

        openModal('modalDailyLog');
    }
    
    document.getElementById('formDailyLog').addEventListener('submit', function() {
        const btn = document.getElementById('btnSubmitLog');
        let customWage = document.querySelector('input[name="custom_wage"]');
        if(customWage) customWage.value = customWage.value.replace(/\./g, '');
        let overhead = document.querySelector('input[name="overhead_cost"]');
        if(overhead) overhead.value = overhead.value.replace(/\./g, '');
        btn.style.opacity = '0.8'; btn.style.pointerEvents = 'none';
        btn.innerHTML = '<i class="ph-bold ph-spinner-gap ph-spin" style="font-size: 20px;"></i> Mengirim Setoran...';
    });

</script>

<?= $this->endSection() ?>