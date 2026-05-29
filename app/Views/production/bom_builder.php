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
// AMBIL DATA LIST TAHAPAN OPERASI YANG ADA DI DATABASE SECARA UNIK
$db = \Config\Database::connect();
$distinctOps = $db->table('bom_operations')
    ->select('operation_name')
    ->distinct()
    ->orderBy('operation_name', 'ASC')
    ->get()->getResultArray();
?>

<style>
    /* =======================================================================
       PREMIUM ERP UI / UX DESIGN SYSTEM (BOM BUILDER)
       ======================================================================= */
    :root {
        --primary: #6366f1; --primary-light: #e0e7ff; --primary-dark: #4f46e5;
        --secondary: #f59e0b; --success: #10b981; --success-dark: #059669; --danger: #ef4444; 
        --info: #3b82f6; --info-dark: #2563eb; --pink: #ec4899;
        
        --bg-body: #f8fafc;
        --bg-surface: #ffffff;
        --bg-input: #f1f5f9;
        
        --text-main: #0f172a;
        --text-muted: #64748b;
        --border-subtle: #e2e8f0;
        
        --shadow-sm: 0 2px 4px rgba(0,0,0,0.02);
        --shadow-md: 0 10px 25px -5px rgba(0,0,0,0.05);
        --shadow-lg: 0 20px 40px -10px rgba(0,0,0,0.1);
        --shadow-hover: 0 20px 40px -10px rgba(99, 102, 241, 0.15);
        
        --radius-sm: 8px;
        --radius-md: 12px;
        --radius-lg: 16px;
        --radius-xl: 24px;
        
        --transition-smooth: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        --glass-bg: rgba(255, 255, 255, 0.85);
    }
    
    html.dark { 
        --bg-body: #0f172a; --bg-surface: #1e293b; --bg-input: #0f172a;
        --text-main: #f8fafc; --text-muted: #94a3b8; --border-subtle: #334155;
        --glass-bg: rgba(30, 41, 59, 0.85);
    }

    body { font-family: 'Plus Jakarta Sans', sans-serif; background: var(--bg-body); color: var(--text-main); }

    /* CUSTOM SCROLLBARS */
    ::-webkit-scrollbar { width: 8px; height: 8px; }
    ::-webkit-scrollbar-track { background: transparent; }
    ::-webkit-scrollbar-thumb { background: var(--border-subtle); border-radius: 10px; }
    ::-webkit-scrollbar-thumb:hover { background: var(--text-muted); }

    @keyframes slideInUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
    @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }

    /* HEADER & TITLE SECTION */
    .page-header { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 35px; width: 100%; flex-wrap: wrap; gap: 20px;} 
    .page-title { display: flex; align-items: center; gap: 18px; animation: slideInUp 0.5s ease-out forwards; flex-wrap: wrap;}
    .title-icon { width: 64px; height: 64px; border-radius: 20px; background: linear-gradient(135deg, var(--primary), var(--primary-dark)); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 32px; box-shadow: 0 15px 30px -5px rgba(99, 102, 241, 0.4); flex-shrink: 0; border: 1px solid rgba(255,255,255,0.2);}
    .page-title h1 { font-size: 32px; font-weight: 900; color: var(--text-main); margin: 0; letter-spacing: -1px; line-height: 1.2;}
    .page-title p { font-size: 14px; color: var(--text-muted); font-weight: 600; margin: 4px 0 0 0; line-height: 1.5;}

    .header-actions { display: flex; gap: 12px; flex-wrap: wrap; align-items: center; }
    .btn-back { background: var(--bg-surface); color: var(--text-main); border: 1px solid var(--border-subtle); padding: 14px 20px; border-radius: var(--radius-md); font-size: 14px; font-weight: 800; text-decoration: none; transition: var(--transition-smooth); display: inline-flex; align-items: center; justify-content: center; gap: 8px; box-shadow: var(--shadow-sm);}
    .btn-back:hover { border-color: var(--primary); color: var(--primary); transform: translateY(-2px); box-shadow: var(--shadow-md);}
    
    .btn-print-master { background: linear-gradient(135deg, var(--success), var(--success-dark)); color: #fff; border: none; padding: 14px 24px; border-radius: var(--radius-md); font-size: 14px; font-weight: 800; cursor: pointer; transition: var(--transition-smooth); display: inline-flex; align-items: center; justify-content: center; gap: 8px; box-shadow: 0 10px 25px -5px rgba(16, 185, 129, 0.4); text-decoration: none;}
    .btn-print-master:hover { transform: translateY(-3px); box-shadow: 0 15px 30px -5px rgba(16, 185, 129, 0.5); }

    /* TAB NAVIGATION */
    .tab-nav { display: inline-flex; background: var(--bg-surface); padding: 8px; border-radius: var(--radius-lg); border: 1px solid var(--border-subtle); margin-bottom: 30px; box-shadow: var(--shadow-sm); flex-wrap: wrap; position: relative; z-index: 1; gap: 6px;}
    .tab-btn { padding: 12px 24px; font-size: 14px; font-weight: 800; border-radius: var(--radius-md); border: none; background: transparent; color: var(--text-muted); cursor: pointer; transition: var(--transition-smooth); display: flex; align-items: center; gap: 8px;}
    .tab-btn:hover { color: var(--text-main); }
    .tab-btn.active { background: var(--text-main); color: var(--bg-surface); box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
    html.dark .tab-btn.active { background: var(--primary); color: #fff;}
    
    .tab-content { display: none; animation: fadeIn 0.4s ease-out; }
    .tab-content.active { display: block; }

    /* BUILDER CARD & FORMS */
    .builder-card { background: var(--glass-bg); backdrop-filter: blur(12px); border: 1px solid var(--border-subtle); border-radius: var(--radius-xl); padding: 40px; box-shadow: var(--shadow-md); width: 100%; box-sizing: border-box; transition: var(--transition-smooth); border-top: 8px solid var(--primary);}
    @media (max-width: 768px) { .builder-card { padding: 20px; border-radius: 20px; } }
    
    .target-box { background: var(--bg-surface); border: 1px solid var(--border-subtle); padding: 25px; border-radius: var(--radius-lg); margin-bottom: 35px; position: relative; overflow: hidden; box-shadow: var(--shadow-sm);}
    .target-box::before { content: ''; position: absolute; top: 0; left: 0; width: 6px; height: 100%; background: var(--primary); border-radius: 6px 0 0 6px;}

    .form-group { margin-bottom: 20px; width: 100%;}
    .form-group label { display: block; font-size: 12px; font-weight: 900; color: var(--text-muted); margin-bottom: 10px; text-transform: uppercase; letter-spacing: 0.5px;}
    .form-control { width: 100%; background: var(--bg-input); border: 2px solid var(--border-subtle); padding: 14px 18px; border-radius: var(--radius-md); font-size: 14px; font-weight: 700; outline: none; color: var(--text-main); transition: var(--transition-smooth); box-sizing: border-box;}
    .form-control:focus { border-color: var(--primary); background: var(--bg-surface); box-shadow: 0 0 0 4px var(--primary-light);}
    
    /* SELECT2 OVERRIDES */
    .select2-container--default .select2-selection--single { background: var(--bg-input); border: 2px solid var(--border-subtle); height: auto; min-height: 50px; border-radius: var(--radius-md); display: flex; align-items: center; padding: 0 12px; transition: var(--transition-smooth);}
    .select2-container--default.select2-container--focus .select2-selection--single,
    .select2-container--default.select2-container--open .select2-selection--single { border-color: var(--primary); background: var(--bg-surface); box-shadow: 0 0 0 4px var(--primary-light);}
    .select2-container--default .select2-selection--single .select2-selection__rendered { font-weight: 800; color: var(--text-main); font-size: 14px; line-height: 1.5; padding: 10px 0;}
    .select2-container--default .select2-selection--single .select2-selection__arrow { height: 48px; right: 15px;}
    .select2-dropdown { border-radius: var(--radius-md); border: 1px solid var(--border-subtle); box-shadow: var(--shadow-lg); padding: 10px; background: var(--bg-surface); margin-top: 5px; z-index: 10001;}
    .select2-search__field { border-radius: 8px !important; padding: 12px 16px !important; border: 2px solid var(--border-subtle) !important; outline: none; font-family: inherit; font-weight: 700; background: var(--bg-input); color: var(--text-main);}
    .select2-results__option { border-radius: 8px; margin-bottom: 2px; font-weight: 700; font-size: 13px; padding: 12px 16px; color: var(--text-main); transition: 0.2s;}
    .select2-container--default .select2-results__option--highlighted[aria-selected] { background: var(--primary-light) !important; color: var(--primary) !important; border: 1px solid rgba(99, 102, 241, 0.2);}
    .select2-container--default .select2-results__option[aria-selected=true] { background-color: rgba(0,0,0,0.03); color: var(--text-main); }
    html.dark .select2-container--default .select2-results__option[aria-selected=true] { background-color: rgba(255,255,255,0.05); }

    /* SECTION & ROW STYLES */
    .section-title { font-size: 18px; font-weight: 900; color: var(--text-main); margin-top: 45px; margin-bottom: 25px; display: flex; align-items: center; gap: 12px; padding-bottom: 15px; border-bottom: 2px dashed var(--border-subtle);}
    .section-title i { background: var(--primary-light); color: var(--primary); padding: 10px; border-radius: 12px; font-size: 22px;}

    .dynamic-row { background: var(--bg-surface); padding: 18px 24px; border-radius: var(--radius-lg); border: 1px solid var(--border-subtle); margin-bottom: 15px; transition: var(--transition-smooth); box-shadow: var(--shadow-sm);}
    .dynamic-row:hover { border-color: var(--border-focus); box-shadow: var(--shadow-md); transform: translateY(-2px);}
    
    .op-row { display: flex; flex-direction: column; gap: 10px; align-items: stretch; border-left: 5px solid var(--secondary); }
    .op-grid { display: grid; grid-template-columns: 40px 2.5fr 1.5fr 1.2fr 1.5fr 45px; gap: 15px; align-items: center; width: 100%;}
    
    .rm-row { display: grid; grid-template-columns: 2.5fr 1.2fr 1.2fr 1.5fr 45px; gap: 15px; align-items: center; border-left: 5px solid var(--success); } 
    .oh-row { display: grid; grid-template-columns: 3.5fr 1.5fr 45px; gap: 15px; align-items: center; border-left: 5px solid var(--pink); } 
    
    /* ALIGNMENT FIX UNTUK JUDUL TABEL (LABELS) */
    .desktop-labels { display: grid; gap: 15px; padding: 0 24px 12px 29px; font-size: 11px; font-weight: 900; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; }
    .rm-labels { grid-template-columns: 2.5fr 1.2fr 1.2fr 1.5fr 45px; }
    .oh-labels { grid-template-columns: 3.5fr 1.5fr 45px; }
    
    @media (max-width: 1024px) { 
        .op-grid, .rm-row, .oh-row { grid-template-columns: 1fr; } 
        .step-number { display:none; } 
        .desktop-labels { display: none; }
    }

    .step-number { font-size: 15px; font-weight: 900; color: var(--text-muted); text-align: center; background: var(--bg-input); width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; border-radius: 10px;}
    .btn-remove { background: rgba(239, 68, 68, 0.08); color: var(--danger); border: 1px solid transparent; width: 45px; height: 45px; border-radius: var(--radius-md); font-size: 22px; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: var(--transition-smooth);}
    .btn-remove:hover { background: var(--danger); color: #fff; transform: scale(1.1) rotate(5deg); box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);}
    
    .btn-add { background: var(--bg-surface); border: 2px dashed; padding: 18px; border-radius: var(--radius-lg); font-weight: 900; font-size: 15px; cursor: pointer; margin-bottom: 10px; transition: var(--transition-smooth); display: flex; align-items: center; justify-content: center; gap: 10px;}
    .btn-add:hover { transform: translateY(-2px); box-shadow: var(--shadow-sm);}
    
    .btn-save { flex: 1; background: linear-gradient(135deg, var(--primary), var(--primary-dark)); color: #fff; border: none; padding: 22px; border-radius: var(--radius-lg); font-size: 16px; font-weight: 900; cursor: pointer; box-shadow: 0 10px 25px -5px rgba(99, 102, 241, 0.5); transition: var(--transition-smooth); display: flex; align-items: center; justify-content: center; gap: 10px; text-transform: uppercase; letter-spacing: 1px;}
    .btn-save:hover { transform: translateY(-3px); box-shadow: 0 15px 30px -5px rgba(99, 102, 241, 0.6);}
    
    .input-wrapper { display: flex; align-items: stretch; background: var(--bg-input); border: 2px solid var(--border-subtle); border-radius: var(--radius-md); overflow: hidden; transition: var(--transition-smooth); width: 100%;}
    .input-wrapper:focus-within { background: var(--bg-surface); }
    .input-wrapper input { flex: 1; background: transparent; border: none; color: var(--text-main); padding: 14px 16px; font-size: 15px; font-weight: 900; font-family: 'Space Mono', monospace; outline: none; width: 100%; min-width: 50px;}
    .input-wrapper input::placeholder { color: var(--text-muted); opacity: 0.7; font-weight: 600; font-family: 'Plus Jakarta Sans', sans-serif;}
    .prefix { background: var(--bg-input); font-size: 14px; font-weight: 900; padding: 0 16px; display: flex; align-items: center; border-right: 2px solid var(--border-subtle); transition: 0.3s; color: var(--text-muted); }

    /* TABLE STYLES (DAFTAR & COPY MASSAL) */
    .table-responsive { width: 100%; border-radius: var(--radius-lg); border: 1px solid var(--border-subtle); background: var(--bg-surface); box-shadow: var(--shadow-sm);}
    table.prod-table { width: 100%; border-collapse: collapse; white-space: nowrap; }
    table.prod-table thead th { text-align: left; padding: 18px 24px; font-size: 11px; font-weight: 900; text-transform: uppercase; color: var(--text-muted); background: var(--bg-input); border-bottom: 2px solid var(--border-subtle); letter-spacing: 0.5px;}
    table.prod-table td { padding: 18px 24px; border-bottom: 1px dashed var(--border-subtle); color: var(--text-main); font-size: 14px; font-weight: 700; vertical-align: middle; transition: var(--transition-smooth);}
    table.prod-table tr:last-child td { border-bottom: none; }
    table.prod-table tr:hover td { background: var(--bg-input); }
    
    .sku-badge { font-family: 'Space Mono', monospace; font-size: 12px; color: var(--primary); background: var(--primary-light); padding: 6px 12px; border-radius: 8px; font-weight: 900; border: 1px dashed rgba(99, 102, 241, 0.4);}
    
    .btn-action-sm { padding: 10px 14px; border-radius: 10px; font-size: 13px; font-weight: 900; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; text-decoration: none; border: none; transition: var(--transition-smooth);}
    .btn-action-sm:hover { transform: translateY(-2px);}
    .btn-edit { background: rgba(59, 130, 246, 0.1); color: var(--info); }
    .btn-edit:hover { background: var(--info); color: #fff;}
    .btn-del { background: rgba(239, 68, 68, 0.1); color: var(--danger); }
    .btn-del:hover { background: var(--danger); color: #fff;}
    .btn-copy { background: rgba(245, 158, 11, 0.1); color: var(--secondary); }
    .btn-copy:hover { background: var(--secondary); color: #fff;}
    .btn-print-tbl { background: rgba(16, 185, 129, 0.1); color: var(--success); }
    .btn-print-tbl:hover { background: var(--success); color: #fff;}

    .section-card { background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: var(--radius-xl); padding: 35px; margin-bottom: 30px; position: relative; overflow: hidden; box-shadow: var(--shadow-sm); }
    .section-card::before { content: ''; position: absolute; top: 0; left: 0; width: 8px; height: 100%; background: linear-gradient(180deg, var(--primary), var(--primary-dark)); border-radius: 8px 0 0 8px; }
    .section-head { display: flex; justify-content: space-between; align-items: center; gap: 15px; margin-bottom: 25px; flex-wrap: wrap; border-bottom: 1px solid var(--border-subtle); padding-bottom: 15px;}
    .section-head h4 { margin: 0; font-size: 18px; font-weight: 900; color: var(--primary); display: flex; align-items: center; gap: 10px; }
    .section-mini-badge { background: var(--primary-light); color: var(--primary-dark); border: 1px dashed rgba(99, 102, 241, 0.3); padding: 6px 12px; border-radius: 8px; font-size: 11px; font-weight: 900; text-transform: uppercase; letter-spacing: .6px; }
    
    .btn-add-section { background: linear-gradient(135deg, var(--primary), var(--primary-dark)); color: #fff; border: none; width: 100%; padding: 20px; border-radius: var(--radius-xl); font-weight: 900; font-size: 16px; cursor: pointer; box-shadow: 0 10px 25px -5px rgba(139, 92, 246, 0.4); transition: var(--transition-smooth); display: flex; align-items: center; justify-content: center; gap: 10px; margin-bottom: 35px; }
    .btn-add-section:hover { transform: translateY(-3px); box-shadow: 0 15px 30px -5px rgba(139, 92, 246, 0.5);}

    .custom-checkbox { width: 20px; height: 20px; cursor: pointer; accent-color: var(--secondary); border-radius: 6px;}
    .highlight-row td { background-color: rgba(245, 158, 11, 0.05) !important; }
    .highlight-row td:first-child { border-left: 4px solid var(--secondary) !important; }
    
    .search-container-tbl { position: relative; width: 100%; max-width: 350px; margin: 0;}
    .search-container-tbl i { position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: var(--text-muted); font-size: 20px; pointer-events: none;}
    .search-container-tbl input { width: 100%; background: var(--bg-input); border: 2px solid var(--border-subtle); padding: 14px 16px 14px 44px; border-radius: var(--radius-md); font-size: 14px; font-weight: 700; color: var(--text-main); outline: none; transition: var(--transition-smooth); box-sizing: border-box;}
    .search-container-tbl input:focus { border-color: var(--info); background: var(--bg-surface); box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.15); }

    /* =======================================================
       PREMIUM SWEETALERT CUSTOMIZATION 
       ======================================================= */
    .swal2-container.swal2-backdrop-show {
        background: rgba(15, 23, 42, 0.75) !important;
        backdrop-filter: blur(5px) !important;
    }
    
    .swal2-popup.swal2-custom-radius {
        border-radius: 24px !important;
        padding: 2.5em 2em 2em 2em !important;
        box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25) !important;
        border: 1px solid var(--border-subtle) !important;
        background: var(--bg-surface) !important;
        color: var(--text-main) !important;
    }

    .swal2-title { 
        font-family: 'Plus Jakarta Sans', sans-serif !important; 
        font-weight: 900 !important; 
        color: var(--text-main) !important;
        font-size: 24px !important;
        margin-bottom: 15px !important;
    }
    
    .swal2-html-container { 
        font-family: 'Plus Jakarta Sans', sans-serif !important; 
        font-weight: 600 !important; 
        color: var(--text-muted) !important; 
        font-size: 15px !important;
        line-height: 1.6 !important;
        margin-bottom: 25px !important;
    }
    
    .swal2-actions {
        gap: 12px !important;
        margin-top: 15px !important;
    }

    .swal2-confirm, .swal2-cancel { 
        border-radius: 12px !important; 
        font-weight: 800 !important; 
        padding: 14px 28px !important; 
        font-family: 'Plus Jakarta Sans', sans-serif !important;
        font-size: 15px !important;
        letter-spacing: 0.5px !important;
        transition: all 0.3s ease !important;
    }
    
    .swal2-confirm {
        box-shadow: 0 8px 20px -6px rgba(0,0,0,0.3) !important;
    }
    
    .swal2-confirm:hover {
        transform: translateY(-2px) !important;
        box-shadow: 0 12px 25px -6px rgba(0,0,0,0.4) !important;
    }

    .swal2-cancel {
        background: var(--bg-input) !important;
        color: var(--text-main) !important;
        border: 1px solid var(--border-subtle) !important;
    }
    
    .swal2-cancel:hover {
        background: var(--border-subtle) !important;
        transform: translateY(-2px) !important;
    }
    
    .swal2-icon {
        border-width: 4px !important;
        margin-bottom: 25px !important;
    }
</style>

<div class="page-wrapper">
    <div class="page-header">
        <div class="page-title">
            <div class="title-icon"><i class="ph-fill ph-flask"></i></div>
            <div>
                <h1>BoM & Routing Studio</h1>
                <p>Arsitektur kebutuhan bahan mentah, biaya overhead, dan susunan alur kerja pabrikasi.</p>
            </div>
        </div>
        <div class="header-actions">
            <a href="<?= base_url('/production/print_bom_batch') ?>" target="_blank" class="btn-print-master">
                <i class="ph-bold ph-printer" style="font-size: 18px;"></i> Cetak Semua Master (Katalog)
            </a>
            <a href="<?= base_url('/production') ?>" class="btn-back">
                <i class="ph-bold ph-arrow-left" style="font-size: 18px;"></i> Kembali ke Pabrik
            </a>
        </div>
    </div>

    <div class="tab-nav">
        <button class="tab-btn active" onclick="switchTab('buat')" id="btnTabBuat"><i class="ph-bold ph-plus-circle" style="font-size: 18px;"></i> Rancang Resep Baru</button>
        <button class="tab-btn" onclick="switchTab('daftar')"><i class="ph-bold ph-database" style="font-size: 18px;"></i> Database Master BoM</button>
        <button class="tab-btn" onclick="switchTab('copy_massal')"><i class="ph-bold ph-copy" style="font-size: 18px;"></i> Copy Massal Spesifik</button>
        <button class="tab-btn" onclick="switchTab('upah_massal')"><i class="ph-bold ph-users" style="font-size: 18px;"></i> Inject Upah Karyawan</button>
    </div>

    <div id="tab-buat" class="tab-content active">
        <div class="builder-card" id="formCard">
            <h3 id="formTitle" style="margin: 0 0 25px 0; font-weight: 900; color: var(--info); display: none; align-items: center; gap: 10px; padding: 18px 20px; background: rgba(59, 130, 246, 0.1); border-radius: var(--radius-md); border: 1px dashed rgba(59, 130, 246, 0.3);">
                <i class="ph-bold ph-pencil-simple" style="font-size: 24px;"></i> Mode Edit Resep
            </h3>

            <form action="<?= base_url('/production/store_bom') ?>" method="post" id="formBOM">
                <?= csrf_field() ?>

                <div class="target-box">
                    <div class="form-group" style="margin-bottom: 20px;">
                        <label style="color: var(--primary);"><i class="ph-fill ph-target"></i> Target Produksi (Barang Jadi)</label>
                        <select name="fg_sku" class="form-control select2-target" required>
                            <option value="">-- Cari & Pilih Produk Jualan --</option>
                            <?php foreach($finishedGoods as $fg): ?>
                                <?php 
                                    $typeUpper = strtoupper($fg['item_type'] ?? '');
                                    $prefix = '';
                                    if (strpos($typeUpper, 'SILENCER') !== false || strpos($typeUpper, 'SLIP-ON') !== false) {
                                        $prefix = '[SILENCER] ';
                                    } elseif (strpos($typeUpper, 'LEHERAN') !== false || strpos($typeUpper, 'HEADER') !== false) {
                                        $prefix = '[LEHERAN] ';
                                    } elseif (strpos($typeUpper, 'FULL') !== false) {
                                        $prefix = '[FULLSYSTEM] ';
                                    } else {
                                        $prefix = '[' . strtoupper($fg['item_type'] ?? 'UMUM') . '] ';
                                    }
                                ?>
                                <option value="<?= esc($fg['sku']) ?>">
                                    <?= $prefix ?><?= esc($fg['item_name']) ?> (Stok: <?= floatval($fg['physical_stock']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group" style="margin-bottom: 0;">
                        <label>Nama Resep / SOP Pembuatan</label>
                        <input type="text" name="recipe_name" class="form-control" placeholder="Cth: Standar Perakitan Leheran Paten FU" required autocomplete="off">
                    </div>
                </div>

                <div class="section-title">
                    <i class="ph-fill ph-stack-simple"></i> Struktur Bagian / Sub-Assembly Produk
                </div>

                <div style="background: var(--primary-light); border: 1px dashed rgba(99, 102, 241, 0.4); padding: 16px 20px; border-radius: var(--radius-md); margin-bottom: 30px; font-size: 13px; color: var(--primary-dark); font-weight: 700; line-height: 1.5;">
                    <i class="ph-fill ph-info" style="font-size: 18px; vertical-align: bottom; margin-right: 4px;"></i>
                    Gunakan <b>Bagian</b> untuk memisahkan komponen produksi seperti <b>Letter S</b>, <b>Monel</b>, <b>Tabung Silincer</b>.
                    <br><b>Tips:</b> Kalau produk sederhana, cukup buat <b>1 Bagian</b> saja.
                </div>

                <div id="sections-container"></div>

                <button type="button" class="btn-add-section" onclick="addSection()">
                    <i class="ph-bold ph-plus-circle" style="font-size: 22px;"></i> Tambah Bagian / Section Baru
                </button>

                <div style="display: flex; gap: 15px; margin-top: 10px; flex-wrap: wrap;">
                    <button type="submit" id="btnSubmitBOM" class="btn-save" style="margin: 0;">
                        <i class="ph-bold ph-check-square" style="font-size: 22px;"></i> <span>Simpan Blueprint Produksi</span>
                    </button>
                    
                    <button type="button" id="btnCancelEdit" class="btn-back" style="display: none; justify-content: center; flex: 0.3; min-width: 200px; border-color: var(--danger); color: var(--danger); padding: 22px; font-size: 16px;" onclick="resetForm()">
                        <i class="ph-bold ph-x" style="font-size: 20px;"></i> Batalkan Edit
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div id="tab-daftar" class="tab-content">
        <div class="builder-card" style="border-top-color: var(--info);">
            
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; flex-wrap: wrap; gap: 15px;">
                <h3 style="margin: 0; font-weight: 900; display: flex; align-items: center; gap: 12px; color: var(--text-main); font-size: 20px;">
                    <i class="ph-fill ph-database" style="color: var(--info); font-size: 28px;"></i> Daftar Resep Master
                </h3>
                
                <div class="search-container-tbl">
                    <i class="ph-bold ph-magnifying-glass"></i>
                    <input type="text" id="searchBom" placeholder="Cari nama atau resep..." onkeyup="filterTable('searchBom', 'bomTable')" style="border-radius: var(--radius-md);">
                </div>
            </div>
            
            <div class="table-responsive" style="max-height: 550px; overflow-y: auto; border: 1px solid var(--border-subtle); border-radius: var(--radius-md); box-shadow: inset 0 2px 4px rgba(0,0,0,0.02);">
                <table id="bomTable" class="prod-table" style="min-width: 100%;">
                    <thead style="position: sticky; top: 0; z-index: 20; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                        <tr>
                            <th style="width: 5%; text-align: center; padding: 16px;">No</th>
                            <th style="width: 15%; padding: 16px;">SKU Produk</th>
                            <th style="width: 30%; padding: 16px;">Nama Produk Jualan</th>
                            <th style="width: 30%; padding: 16px;">Judul Resep / SOP</th>
                            <th style="width: 20%; text-align: center; padding: 16px;">Tindakan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($existingBoms)): ?>
                            <tr id="emptyRow">
                                <td colspan="5">
                                    <div class="empty-state" style="padding: 40px 20px; border: none; background: transparent; box-shadow: none;">
                                        <i class="ph-duotone ph-flask"></i>
                                        <h3>Belum Ada Resep Master</h3>
                                        <p>Buat blueprint pertama Anda melalui tab Rancang Resep Baru.</p>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php $n=1; foreach($existingBoms as $b): ?>
                            <tr class="data-row spk-row">
                                <td style="color: var(--text-muted); font-weight: 900; text-align: center; padding: 16px;"><?= $n++ ?>.</td>
                                <td class="searchable" style="padding: 16px;">
                                    <span class="sku-badge" style="background: var(--bg-input); color: var(--text-muted); border: 1px solid var(--border-subtle);"><?= esc($b['fg_sku']) ?></span>
                                </td>
                                <td class="searchable" style="font-weight: 900; color: var(--text-main); padding: 16px;">
                                    <?php 
                                        $typeUpper = strtoupper($b['item_type'] ?? '');
                                        if (strpos($typeUpper, 'SILENCER') !== false || strpos($typeUpper, 'SLIP-ON') !== false) {
                                            echo '<span style="color:var(--primary); font-size:11px; background: var(--primary-light); padding: 4px 8px; border-radius: 6px; margin-right: 6px; display: inline-block;">[SILENCER]</span> ';
                                        } elseif (strpos($typeUpper, 'LEHERAN') !== false || strpos($typeUpper, 'HEADER') !== false) {
                                            echo '<span style="color:var(--secondary); font-size:11px; background: rgba(245, 158, 11, 0.1); padding: 4px 8px; border-radius: 6px; margin-right: 6px; display: inline-block;">[LEHERAN]</span> ';
                                        } elseif (strpos($typeUpper, 'FULL') !== false) {
                                            echo '<span style="color:var(--success); font-size:11px; background: rgba(16, 185, 129, 0.1); padding: 4px 8px; border-radius: 6px; margin-right: 6px; display: inline-block;">[FULLSYSTEM]</span> ';
                                        } else {
                                            echo '<span style="color:var(--text-muted); font-size:11px; background: var(--border-subtle); padding: 4px 8px; border-radius: 6px; margin-right: 6px; display: inline-block;">[' . esc(strtoupper($b['item_type'] ?? 'UMUM')) . ']</span> ';
                                        }
                                    ?>
                                    <br>
                                    <span style="display:inline-block; margin-top: 4px;"><?= esc($b['item_name'] ?? 'Produk Tidak Diketahui') ?></span>
                                </td>
                                <td class="searchable" style="color: var(--text-muted); font-weight: 700; padding: 16px; white-space: normal; line-height: 1.4;">
                                    <?= esc($b['recipe_name']) ?>
                                </td>
                                <td style="text-align: center; padding: 16px;">
                                    <div style="display: flex; justify-content: center; gap: 6px; flex-wrap: wrap;">
                                        <a href="#" onclick="confirmAction(event, '<?= base_url('production/duplicate_bom/'.$b['id']) ?>', 'Duplikat Resep?', 'Salinan resep ini akan dibuat sama persis. Lanjutkan?', 'var(--info-dark)', '<i class=\'ph-bold ph-copy\'></i> Ya, Duplikat')" class="btn-action-sm btn-copy" title="Duplikat Resep">
                                            <i class="ph-bold ph-copy"></i>
                                        </a>
                                        <a href="<?= base_url('production/print_bom/'.$b['id']) ?>" target="_blank" class="btn-action-sm btn-print-tbl" title="Cetak Satuan (A4)">
                                            <i class="ph-bold ph-printer"></i>
                                        </a>
                                        <button type="button" class="btn-action-sm btn-edit" onclick="editBom(<?= $b['id'] ?>)" title="Edit Resep">
                                            <i class="ph-bold ph-pencil-simple"></i> Edit
                                        </button>
                                        <a href="#" onclick="confirmAction(event, '<?= base_url('production/delete_bom/'.$b['id']) ?>', 'Hapus Permanen?', 'Resep ini dan seluruh tahapan operasinya akan dihapus dari sistem.', 'var(--danger)', '<i class=\'ph-bold ph-trash\'></i> Ya, Hapus')" class="btn-action-sm btn-del" title="Hapus Resep">
                                            <i class="ph-bold ph-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            
                            <tr id="noResultRow" style="display: none;">
                                <td colspan="5">
                                    <div class="empty-state" style="padding: 40px 20px; border: none; background: transparent; box-shadow: none;">
                                        <i class="ph-duotone ph-warning-circle" style="color: var(--danger); font-size: 40px; margin-bottom: 10px;"></i>
                                        <h3 style="margin:0; font-size: 16px;">Produk tidak ditemukan.</h3>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div id="tab-copy_massal" class="tab-content">
        <div class="builder-card" style="border-top-color: var(--secondary);">
            <h3 style="margin: 0 0 25px 0; font-weight: 900; color: var(--text-main); display: flex; align-items: center; gap: 12px; font-size: 20px;">
                <i class="ph-fill ph-copy" style="color: var(--secondary); font-size: 28px;"></i> Copy-Paste Massal Formula Resep
            </h3>
            
            <div style="background: rgba(245, 158, 11, 0.05); border: 1px dashed rgba(245, 158, 11, 0.3); padding: 18px 24px; border-radius: var(--radius-md); margin-bottom: 30px; font-size: 14px; color: var(--text-main); font-weight: 600; line-height: 1.6;">
                <i class="ph-fill ph-info" style="color: var(--secondary); font-size: 20px; vertical-align: bottom; margin-right: 6px;"></i>
                Pilih elemen apa saja yang mau Anda Copy-Paste dari Resep Master ke Resep Target. <br><b style="color:var(--danger); margin-left: 30px;">Peringatan:</b> Data pada resep target akan <b style="color:var(--danger);">dihapus dan ditimpa (Replace)</b> secara permanen mengikuti master.
            </div>

            <form action="<?= base_url('/production/mass_copy_bom') ?>" method="post" id="formMassCopy">
                <?= csrf_field() ?>
                
                <div class="target-box" style="border-color: rgba(245, 158, 11, 0.3); margin-bottom: 25px;">
                    <label style="display: block; font-size: 12px; font-weight: 900; color: var(--text-muted); margin-bottom: 10px; text-transform: uppercase;">1. Pilih Resep Sumber (Master Copy)</label>
                    <select name="source_bom_id" id="sourceBomSelect" class="form-control" required style="width: 100%;">
                        <option value="">-- Cari Resep Master yang datanya mau di-copy --</option>
                        <?php foreach($existingBoms as $b): ?>
                            <option value="<?= $b['id'] ?>"><?= esc($b['recipe_name']) ?> [<?= esc($b['fg_sku']) ?>]</option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="target-box" style="border-color: rgba(59, 130, 246, 0.3); margin-bottom: 25px; background: rgba(59, 130, 246, 0.03);">
                    <label style="display: block; font-size: 12px; font-weight: 900; color: var(--info); margin-bottom: 15px; text-transform: uppercase;">2. Elemen Apa Saja Yang Ingin Ditimpa/Copy?</label>
                    
                    <div style="display: flex; gap: 30px; flex-wrap: wrap;">
                        <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; text-transform: none; font-size: 15px; font-weight: 800; color: var(--text-main);">
                            <input type="checkbox" name="copy_items" value="yes" class="custom-checkbox">
                            📦 Bahan Baku & Overhead (bom_items)
                        </label>
                        <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; text-transform: none; font-size: 15px; font-weight: 800; color: var(--text-main);">
                            <input type="checkbox" name="copy_ops" value="yes" class="custom-checkbox" checked>
                            👷 Tahapan Pekerja & Upah (bom_operations)
                        </label>
                    </div>
                </div>

                <div class="target-box" style="border-color: var(--border-subtle); margin-bottom: 25px; padding: 25px;">
                    <label style="display: block; font-size: 13px; font-weight: 900; color: var(--text-main); margin-bottom: 20px; text-transform: uppercase; letter-spacing: 0.5px;">
                        <i class="ph-fill ph-check-square-offset" style="color: var(--secondary); font-size: 20px; vertical-align: bottom; margin-right: 6px;"></i> 
                        3. Centang Resep Target (Yang Akan Di-timpa)
                    </label>

                    <div class="search-container-tbl" style="margin-bottom: 20px; max-width: 100%;">
                        <i class="ph-bold ph-magnifying-glass"></i>
                        <input type="text" id="searchCopy" placeholder="Cari resep target untuk dicentang..." onkeyup="filterTable('searchCopy', 'copyTable')" style="border-radius: var(--radius-md);">
                    </div>

                    <div class="table-responsive" style="max-height: 400px; overflow-y: auto; border: 1px solid var(--border-subtle); border-radius: var(--radius-md); box-shadow: inset 0 2px 4px rgba(0,0,0,0.02);">
                        <table id="copyTable" class="prod-table" style="min-width: 100%;"> 
                            <thead style="position: sticky; top: 0; z-index: 10; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                                <tr>
                                    <th style="width: 5%; text-align: center; padding: 14px;"><input type="checkbox" id="selectAllCheckbox" class="custom-checkbox"></th>
                                    <th style="width: 25%; padding: 14px;">SKU Produk</th>
                                    <th style="width: 70%; padding: 14px;">Judul Resep Target</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($existingBoms as $b): ?>
                                <tr class="data-row checkbox-row spk-row">
                                    <td style="text-align: center; padding: 12px;"><input type="checkbox" name="target_bom_ids[]" value="<?= $b['id'] ?>" class="target-checkbox custom-checkbox"></td>
                                    <td class="searchable" style="padding: 12px;"><span class="sku-badge" style="background: var(--bg-input); color: var(--text-muted); border: 1px solid var(--border-subtle);"><?= esc($b['fg_sku']) ?></span></td>
                                    <td class="searchable" style="font-weight: 900; color: var(--text-main); padding: 12px;"><?= esc($b['recipe_name']) ?></td>
                                </tr>
                                <?php endforeach; ?>
                                
                                <tr id="noResultRowCopy" style="display: none;">
                                    <td colspan="3">
                                        <div class="empty-state" style="padding: 40px 20px; border: none; box-shadow: none; background: transparent;">
                                            <i class="ph-duotone ph-warning-circle" style="color: var(--danger); font-size: 40px; margin-bottom: 10px;"></i>
                                            <h3 style="margin:0; font-size: 16px;">Resep target tidak ditemukan.</h3>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <button type="submit" class="btn-save" style="margin-top: 25px; background: linear-gradient(135deg, var(--secondary), #d97706); box-shadow: 0 15px 35px -5px rgba(245, 158, 11, 0.4);" onclick="return confirmAction(event, null, 'Peringatan Keras!', 'Data pada Resep Target yang dicentang akan DIHAPUS PERMANEN dan diganti dengan data dari Resep Master.', 'var(--secondary)', '<i class=\'ph-bold ph-files\'></i> Ya, Timpa Data!', this.closest('form'))">
                    <i class="ph-bold ph-files" style="font-size: 22px;"></i> Timpa (Paste) Sekarang
                </button>
            </form>
        </div>
    </div>

    <div id="tab-upah_massal" class="tab-content">
        <div class="builder-card" style="border-top-color: var(--success);">
            <h3 style="margin: 0 0 25px 0; font-weight: 900; color: var(--text-main); display: flex; align-items: center; gap: 12px; font-size: 20px;">
                <i class="ph-fill ph-users" style="color: var(--success); font-size: 28px;"></i> Inject Harga Khusus Karyawan (Massal)
            </h3>
            
            <div style="background: rgba(16, 185, 129, 0.05); border: 1px dashed rgba(16, 185, 129, 0.3); padding: 18px 24px; border-radius: var(--radius-md); margin-bottom: 30px; font-size: 14px; color: var(--text-main); font-weight: 600; line-height: 1.6;">
                <i class="ph-fill ph-info" style="color: var(--success-dark); font-size: 20px; vertical-align: bottom; margin-right: 6px;"></i>
                Gunakan alat ini jika Anda punya pekerja dengan upah berbeda (Misal: Senior/Junior). <br>Pilih kata kunci tahapan (contoh: <b>LAS CANTUM</b>) dan pilih spesifik motor (opsional), maka sistem akan otomatis menempelkan harga khusus ini ke <b>SEMUA RESEP</b> yang sesuai.
            </div>

            <form action="<?= base_url('/production/mass_update_custom_wage') ?>" method="post" id="formInjectUpah">
                <?= csrf_field() ?>
                
                <div class="target-box" style="border-color: rgba(16, 185, 129, 0.3); margin-bottom: 25px;">
                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px;">
                        <div class="form-group" style="margin-bottom: 0;">
                            <label>1. Pilih Pekerja</label>
                            <select name="employee_id" class="form-control select2-emp-massal" required style="width: 100%;">
                                <option value="">-- Cari Nama Karyawan --</option>
                                <?php foreach($workers as $w): 
                                    $isBorong = (stripos($w['status'] ?? '', 'Borong') !== false) ? 'Borong' : 'Tetap';
                                ?>
                                    <option value="<?= esc($w['employee_id']) ?>"><?= esc($w['name']) ?> (<?= $isBorong ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group" style="margin-bottom: 0;">
                            <label>2. Filter Jenis Motor (Opsional)</label>
                            <select name="product_keyword" class="form-control select2-recipe-massal" style="width: 100%;">
                                <option value="">-- Terapkan ke Semua Motor --</option>
                                <?php foreach($existingBoms as $b): ?>
                                    <option value="<?= esc($b['recipe_name']) ?>"><?= esc($b['recipe_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group" style="margin-bottom: 0;">
                            <label>3. Nama Tahapan Kerja</label>
                            <select name="operation_keyword" class="form-control select2-op-massal" required style="width: 100%;">
                                <option value="">-- Pilih Tahapan --</option>
                                <?php foreach($distinctOps as $op): ?>
                                    <option value="<?= esc($op['operation_name']) ?>"><?= esc($op['operation_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="target-box" style="border-color: var(--success-dark); margin-bottom: 25px; background: var(--success-soft);">
                    <div class="form-group" style="margin-bottom: 0; max-width: 400px; margin: 0 auto;">
                        <label style="color: var(--success-dark); text-align: center;">4. Harga Upah Khusus (Rp / Pcs)</label>
                        <div class="input-wrapper" style="border-color: var(--success-dark);">
                            <div class="prefix" style="background:var(--success-dark); color:#fff; border-right:none;">Rp</div>
                            <input type="text" name="custom_wage" placeholder="Masukan nominal..." required onkeyup="formatRupiah(this)" autocomplete="off" style="color: var(--success-dark); font-size: 24px; text-align: center;">
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn-save" style="margin-top: 25px; background: linear-gradient(135deg, var(--success), var(--success-dark)); box-shadow: 0 15px 35px -5px rgba(16, 185, 129, 0.4);" onclick="return confirmAction(event, null, 'Inject Upah Khusus?', 'Sistem akan mencari semua tahapan kerja yang sesuai kriteria di Master Resep, lalu menyisipkan harga khusus ini secara massal.', 'var(--success-dark)', '<i class=\'ph-bold ph-magic-wand\'></i> Ya, Lanjutkan!', this.closest('form'))">
                    <i class="ph-bold ph-magic-wand" style="font-size: 22px;"></i> Inject Upah Khusus Sekarang
                </button>
            </form>
        </div>
    </div>
</div>

<script>
    const rmData = <?= json_encode($rawMaterials, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) ?>;
    const fgData = <?= json_encode($finishedGoods, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) ?>;
    const uomData = <?= json_encode($uomMasters, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) ?>;
    const workersData = <?= json_encode($workers ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) ?>;
    let sectionCounter = 0;

    document.addEventListener("DOMContentLoaded", function() {
        <?php if(session()->getFlashdata('success')): ?>
            Swal.fire({ icon: 'success', title: 'Berhasil!', html: <?= json_encode(session()->getFlashdata('success'), JSON_HEX_APOS|JSON_HEX_QUOT) ?>, confirmButtonColor: '#10b981', customClass: { popup: 'swal2-custom-radius' } });
        <?php endif; ?>
        <?php if(session()->getFlashdata('error')): ?>
            Swal.fire({ icon: 'error', title: 'Gagal!', html: <?= json_encode(session()->getFlashdata('error'), JSON_HEX_APOS|JSON_HEX_QUOT) ?>, confirmButtonColor: '#ef4444', customClass: { popup: 'swal2-custom-radius' } });
        <?php endif; ?>
    });

    /* =======================================================
       SWEETALERT 2 CONFIRMATIONS (PREMIUM UI REPLACEMENT)
       ======================================================= */
    function confirmAction(e, url, title, text, confirmColor, confirmText, formElement = null) {
        e.preventDefault();
        Swal.fire({
            title: title,
            html: text,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: confirmColor,
            cancelButtonColor: '#64748b',
            confirmButtonText: confirmText,
            cancelButtonText: 'Batal',
            customClass: { popup: 'swal2-custom-radius' }
        }).then((result) => {
            if (result.isConfirmed) {
                if (url) {
                    window.location.href = url;
                } else if (formElement) {
                    Swal.fire({ title: 'Memproses...', allowOutsideClick: false, didOpen: () => { Swal.showLoading() } });
                    formElement.submit();
                }
            }
        });
    }
    /* =======================================================
       END SWEETALERT
       ======================================================= */

    document.getElementById('selectAllCheckbox').addEventListener('change', function() {
        let checkboxes = document.querySelectorAll('.target-checkbox');
        let isChecked = this.checked;
        checkboxes.forEach(cb => {
            if (cb.closest('tr').style.display !== 'none' && !cb.disabled) {
                cb.checked = isChecked;
                highlightRow(cb);
            }
        });
    });

    document.querySelectorAll('.target-checkbox').forEach(cb => {
        cb.addEventListener('change', function() {
            highlightRow(this);
        });
    });

    function highlightRow(checkbox) {
        let row = checkbox.closest('tr');
        if(checkbox.checked) {
            row.classList.add('highlight-row');
        } else {
            row.classList.remove('highlight-row');
        }
    }

    function buildUomOptions(selectedUom) {
        let options = '';
        let sUom = String(selectedUom || 'PCS').toUpperCase();
        
        if (uomData && Array.isArray(uomData)) {
            uomData.forEach(u => {
                let code = String(u.uom_code).toUpperCase();
                let isSelected = (code === sUom) ? 'selected' : '';
                options += `<option value="${code}" ${isSelected}>${code}</option>`;
            });
        }
        return options;
    }

    function buildWorkerOptions(selectedId = '') {
        let opts = '<option value="">-- Pilih Pekerja --</option>';
        workersData.forEach(w => {
            let isBorong = w.status && w.status.toLowerCase().includes('borong') ? 'Borong' : 'Tetap';
            let sel = (w.employee_id === selectedId) ? 'selected' : '';
            opts += `<option value="${w.employee_id}" ${sel}>${w.name} (${isBorong})</option>`;
        });
        return opts;
    }

    function filterTable(inputId = "searchBom", tableId = "bomTable") {
        let input = document.getElementById(inputId);
        let filter = input.value.toUpperCase();
        let table = document.getElementById(tableId);
        let tr = table.getElementsByClassName("data-row");
        let hasResult = false;

        for (let i = 0; i < tr.length; i++) {
            let searchableCells = tr[i].getElementsByClassName("searchable");
            let rowContainsText = false;
            for (let j = 0; j < searchableCells.length; j++) {
                if (searchableCells[j]) {
                    let txtValue = searchableCells[j].textContent || searchableCells[j].innerText;
                    if (txtValue.toUpperCase().indexOf(filter) > -1) {
                        rowContainsText = true; break;
                    }
                }
            }
            if (rowContainsText) { tr[i].style.display = ""; hasResult = true; } 
            else { tr[i].style.display = "none"; }
        }

        let noResultRow = (tableId === 'bomTable') ? document.getElementById("noResultRow") : document.getElementById("noResultRowCopy");
        if (noResultRow) noResultRow.style.display = hasResult ? "none" : "";
    }

    $(document).ready(function() {
        $('.select2-target').select2({ 
            width: '100%', placeholder: "-- Cari & Pilih Target Produksi --",
        });

        // INIT SOURCE BOM (MASTER COPY)
        $('#sourceBomSelect').select2({
            width: '100%', placeholder: "-- Cari Resep Master --"
        }).on('change', function() {
            // FITUR PROTEKSI: Disable resep target yang sama dengan resep master (Cegah Human Error)
            let selectedSourceId = $(this).val();
            
            // 1. Reset semua checkbox target agar bisa diklik dan terang kembali
            $('.target-checkbox').prop('disabled', false);
            $('.target-checkbox').closest('tr').css('opacity', '1').css('pointer-events', 'auto');
            
            // 2. Cari yang ID-nya sama dengan master, lalu matikan dan buramkan
            if(selectedSourceId) {
                let selfCheckbox = document.querySelector(`.target-checkbox[value="${selectedSourceId}"]`);
                if(selfCheckbox) {
                    selfCheckbox.checked = false;
                    selfCheckbox.disabled = true;
                    highlightRow(selfCheckbox);
                    selfCheckbox.closest('tr').style.opacity = '0.4';
                    selfCheckbox.closest('tr').style.pointerEvents = 'none';
                }
            }
        });

        // INIT SELECT2 UNTUK TAB INJECT UPAH MASSAL
        $('.select2-emp-massal').select2({ 
            placeholder: "-- Cari Nama Karyawan --", width: '100%' 
        });
        $('.select2-recipe-massal').select2({ 
            placeholder: "-- Terapkan ke Semua Motor --", width: '100%', allowClear: true 
        });
        $('.select2-op-massal').select2({ 
            placeholder: "-- Pilih Tahapan --", width: '100%' 
        });
        
        $('.select2-target').on('change', function() {
            let selectedTargetSku = $(this).val();
            $('.select2-component option').prop('disabled', false);
            if(selectedTargetSku) {
                $('.select2-component option[value="' + selectedTargetSku + '"]').prop('disabled', true);
            }
            $('.select2-component').each(function() { initializeSelect2Component($(this)); });
        });

        resetForm();
    });

    function switchTab(tabId) {
        document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
        event.currentTarget.classList.add('active');
        document.getElementById('tab-' + tabId).classList.add('active');
    }

    function initializeSelect2Component($element) {
        $element.select2({ 
            width: '100%', placeholder: "-- Cari Komponen / Bahan / Overhead --"
        });
    }

    function buildMaterialOptions() {
        let optgroupRM = '<optgroup label="Bahan Baku Mentah & Penolong (Material / Overhead)">';
        rmData.forEach(rm => { 
            let baseUom = rm.base_uom || rm.unit || 'PCS';
            optgroupRM += `<option value="${rm.sku_material}" data-base="${baseUom}">[${rm.sku_material}] ${rm.material_name} (Sisa: ${parseFloat(rm.physical_stock)} ${baseUom})</option>`; 
        });
        optgroupRM += '</optgroup>';
        
        let optgroupFG = '<optgroup label="Produk Sub-Assembly (Slincer, dll)">';
        fgData.forEach(fg => { 
            optgroupFG += `<option value="${fg.sku}" data-base="PCS">[${fg.sku}] ${fg.item_name} (Sisa: ${parseFloat(fg.physical_stock)} Pcs)</option>`; 
        });
        optgroupFG += '</optgroup>';

        return optgroupRM + optgroupFG;
    }

    function calcMatTotal(element) {
        if(!element) return;
        let row = element.closest('.rm-row');
        if(!row) return;

        let sizeInput = row.querySelector('.mat-size');
        let pcsInput = row.querySelector('.mat-pcs');
        let uomSizeSelect = row.querySelector('.mat-uom');
        let uomQtySelect = row.querySelector('.mat-qty-uom');
        
        if(!sizeInput || !pcsInput) return;

        let sizeValStr = sizeInput.value.replace(/,/g, '.').replace(/[^0-9.]/g, '');
        let pcsValStr = pcsInput.value.replace(/,/g, '.').replace(/[^0-9.]/g, '');
        
        if((sizeValStr.match(/\./g) || []).length > 1) sizeValStr = sizeValStr.replace(/\.$/, "");
        if((pcsValStr.match(/\./g) || []).length > 1) pcsValStr = pcsValStr.replace(/\.$/, "");
        
        sizeInput.value = sizeValStr;
        pcsInput.value = pcsValStr;

        let size = parseFloat(sizeValStr) || 1; 
        let pcs = parseFloat(pcsValStr) || 0;
        
        let uomSize = uomSizeSelect ? uomSizeSelect.value : 'PCS';
        let uomQty = uomQtySelect ? uomQtySelect.value : 'PCS';
        
        let total = size * pcs;
        let finalUom = (size !== 1) ? uomSize : uomQty;

        let totalInput = row.querySelector('.qty-real');
        if(totalInput) {
            totalInput.value = isNaN(total) ? '0' : total.toFixed(4).replace(/\.?0+$/, '');
        }
        
        let lblUom = row.querySelector('.base-uom-lbl');
        if(lblUom) lblUom.innerText = finalUom;
    }

    function updateMatRow(selElement) {
        let selectedOption = selElement.options[selElement.selectedIndex];
        if (!selectedOption || !selectedOption.value) return;
        
        let baseUom = String(selectedOption.getAttribute('data-base') || 'PCS').toUpperCase();
        let row = selElement.closest('.rm-row');
        
        let uomSizeSelect = row.querySelector('.mat-uom');
        if(uomSizeSelect && row.dataset.editing !== 'true') {
            if ($(uomSizeSelect).find("option[value='" + baseUom + "']").length) {
                uomSizeSelect.value = baseUom;
            }
        }

        calcMatTotal(selElement); 
    }

    function updateOhRow(selElement) {
        let selectedOption = selElement.options[selElement.selectedIndex];
        if (!selectedOption || !selectedOption.value) return;
        
        let baseUom = String(selectedOption.getAttribute('data-base') || 'PCS').toUpperCase();
        let row = selElement.closest('.oh-row');
        let lbl = row.querySelector('.oh-uom-lbl');
        if(lbl) lbl.innerText = baseUom;
    }

    function toggleWageType(selectElement) {
        let row = $(selectElement).closest('.op-row');
        let wageInput = row.find('.wage-input');
        let prefix = row.find('.prefix');
        let btnException = row.find('.btn-exception');
        let cwContainer = row.find('.op-custom-wages-container');
        
        if (selectElement.value === 'Tetap') {
            wageInput.val('0');
            wageInput.prop('readonly', true);
            prefix.css('opacity', '0.4');
            wageInput.css('opacity', '0.4');
            btnException.hide();
            cwContainer.hide();
        } else {
            wageInput.prop('readonly', false);
            prefix.css('opacity', '1');
            wageInput.css('opacity', '1');
            btnException.show();
            cwContainer.show();
        }
    }

    function renumberOperations(container = null) {
        let rows = container ? container.querySelectorAll('.op-row') : document.querySelectorAll('.op-row');
        rows.forEach((row, index) => {
            let numBox = row.querySelector('.step-number');
            if(numBox) numBox.innerText = index + 1;
            row.style.transition = 'all 0.4s ease';
            if(index === rows.length - 1) {
                row.style.borderColor = 'var(--info)'; 
                row.style.boxShadow = 'inset 0 0 0 2px rgba(59, 130, 246, 0.1)';
                if(numBox){ numBox.style.background = 'var(--info)'; numBox.style.color = '#fff'; }
            } else {
                row.style.borderColor = 'var(--border-subtle)'; 
                row.style.boxShadow = 'var(--shadow-sm)';
                if(numBox){ numBox.style.background = 'var(--bg-input)'; numBox.style.color = 'var(--text-muted)'; }
            }
        });
    }

    function addSection(name = '', code = '') {
        sectionCounter++;
        let sectionId = sectionCounter;

        let sectionHtml = `
            <div class="section-card" data-section-id="${sectionId}">
                <div class="section-head">
                    <h4><i class="ph-fill ph-stack"></i> Bagian / Section #${sectionId}</h4>
                    <div style="display:flex; gap:10px; align-items:center;">
                        <span class="section-mini-badge">Sub Assembly</span>
                        <button type="button" class="btn-remove" onclick="removeSection(this)" title="Hapus Bagian" style="width: 38px; height: 38px;"><i class="ph-bold ph-trash" style="font-size: 18px;"></i></button>
                    </div>
                </div>

                <div style="display:grid; grid-template-columns: 2fr 1fr; gap:20px; margin-bottom:25px;">
                    <div class="form-group" style="margin-bottom:0;">
                        <label>Nama Bagian</label>
                        <input type="text" name="section_name[]" class="form-control" placeholder="Contoh: Letter S / Monel / Tabung Luar / Isi Dalam" value="${name}" required autocomplete="off">
                    </div>
                    <div class="form-group" style="margin-bottom:0;">
                        <label>Kode Bagian (Opsional)</label>
                        <input type="text" name="section_code[]" class="form-control section-code" placeholder="Contoh: LS / MNL / TBG" value="${code}">
                    </div>
                </div>

                <div class="section-title" style="margin-top: 15px; color: var(--success);">
                    <i class="ph-fill ph-cube" style="color: var(--success); background: rgba(16, 185, 129, 0.1);"></i> Kebutuhan Material Fisik & Komponen
                </div>

                <div class="desktop-labels rm-labels">
                    <div>Pilih Material Fisik</div>
                    <div>Ukuran per Item</div>
                    <div>Jml Item / Pcs</div>
                    <div>Total Kebutuhan</div>
                    <div style="width: 45px;"></div>
                </div>

                <div class="rm-container"></div>

                <button type="button" class="btn-add" onclick="addRmRow(this.closest('.section-card').querySelector('.rm-container'), 'rm')" style="color: var(--success); border-color: rgba(16, 185, 129, 0.4); background: rgba(16, 185, 129, 0.05);">
                    <i class="ph-bold ph-plus-circle" style="font-size: 18px;"></i> Tambah Kebutuhan Material Fisik
                </button>

                <div class="section-title" style="margin-top: 40px; color: var(--pink);">
                    <i class="ph-fill ph-lightning" style="color: var(--pink); background: rgba(236, 72, 153, 0.1);"></i> Kebutuhan Overhead Pabrik
                </div>

                <div class="desktop-labels oh-labels">
                    <div>Pilih Overhead / Biaya Tambahan</div>
                    <div>Total Kebutuhan</div>
                    <div style="width: 45px;"></div>
                </div>

                <div class="oh-container"></div>

                <button type="button" class="btn-add" onclick="addRmRow(this.closest('.section-card').querySelector('.oh-container'), 'oh')" style="color: var(--pink); border-color: rgba(236, 72, 153, 0.4); background: rgba(236, 72, 153, 0.05);">
                    <i class="ph-bold ph-plus-circle" style="font-size: 18px;"></i> Tambah Biaya Overhead
                </button>

                <div class="section-title" style="margin-top: 45px; color: var(--secondary);">
                    <i class="ph-fill ph-kanban" style="color: var(--secondary); background: rgba(245, 158, 11, 0.1);"></i> Tahapan Operasi & Upah Pekerja
                </div>

                <div class="op-container"></div>

                <button type="button" class="btn-add" onclick="addOpRow(this.closest('.section-card').querySelector('.op-container'))" style="color: var(--secondary); border-color: rgba(245, 158, 11, 0.4); background: rgba(245, 158, 11, 0.05);">
                    <i class="ph-bold ph-plus-circle" style="font-size: 18px;"></i> Tambah Tahapan Kerja Baru
                </button>
            </div>
        `;

        $('#sections-container').append(sectionHtml);
    }

    function removeSection(btn) {
        let total = document.querySelectorAll('.section-card').length;
        if (total <= 1) {
            Swal.fire('Minimal 1 Bagian', 'Satu produk harus punya minimal 1 bagian / section.', 'warning');
            return;
        }
        btn.closest('.section-card').remove();
    }

    function addCustomWageRow(container, empId = '', wage = '') {
        let div = document.createElement('div');
        div.className = 'custom-wage-row';
        div.style.cssText = "display:grid; grid-template-columns: auto 1fr auto auto; gap:12px; margin-top:10px; align-items:center; background: var(--bg-input); padding: 12px 16px; border-radius: var(--radius-md); border: 1px dashed var(--border-subtle); box-shadow: inset 0 2px 4px rgba(0,0,0,0.02);";
        div.innerHTML = `
            <i class="ph-bold ph-arrow-elbow-down-right" style="color:var(--text-muted); font-size: 20px;"></i>
            <div style="min-width: 200px;"><select class="form-control cw-emp select2-emp-exception" style="width:100%;">${buildWorkerOptions(empId)}</select></div>
            <div class="input-wrapper" style="border-color: var(--info); width:180px; background: var(--bg-surface);">
                <div class="prefix" style="background:rgba(59, 130, 246, 0.1); color:var(--info); border-right:none;">Rp</div>
                <input type="text" class="cw-wage" value="${wage}" onkeyup="formatRupiah(this)" placeholder="Harga Khusus" style="padding:10px 14px; font-size:14px; color:var(--info);">
            </div>
            <button type="button" class="btn-remove" style="width:40px; height:40px; font-size:18px;" onclick="this.parentElement.remove()"><i class="ph-bold ph-x"></i></button>
        `;
        container.appendChild(div);
        $(div).find('.select2-emp-exception').select2({ placeholder: "-- Pilih Pekerja --", width: '100%' });
    }

    function addOpRow(containerEl, opName = '', opWage = '', workerType = 'Borongan', specialty = '', customWages = []) {
        let row = document.createElement('div');
        row.className = 'dynamic-row op-row';
        
        let isReadonly = (workerType === 'Tetap') ? 'readonly' : '';
        let opacity = (workerType === 'Tetap') ? 'opacity: 0.4;' : '';
        let exceptionDisplay = (workerType === 'Tetap') ? 'display: none;' : '';

        const specialtiesList = ['Bending', 'Monel', 'Las Cacing', 'Las Cantum', 'Poles / Amril', 'Perakitan', 'Quality Control', 'Packing', 'Gudang / Logistik'];
        let specialtyOptions = '<option value="">-- Bebas (Semua Pekerja) --</option>';
        specialtiesList.forEach(sp => {
            let isSel = (specialty === sp) ? 'selected' : '';
            specialtyOptions += `<option value="${sp}" ${isSel}>${sp}</option>`;
        });

        row.innerHTML = `
            <div class="op-grid">
                <div class="step-number"></div>
                <input type="text" name="op_name[]" class="form-control op-name" placeholder="Cth: Cetak Monel / Bending" required autocomplete="off" style="font-weight: 900; font-size: 15px;" value="${opName}">
                
                <select name="op_specialty[]" class="form-control op-specialty" style="font-size: 13px; font-weight: 800; color: var(--primary);">
                    ${specialtyOptions}
                </select>

                <select name="op_worker_type[]" class="form-control op-worker-type" onchange="toggleWageType(this)" style="font-size: 13px; font-weight: 800;">
                    <option value="Borongan" ${workerType === 'Borongan' ? 'selected' : ''}>👤 Borong</option>
                    <option value="Tetap" ${workerType === 'Tetap' ? 'selected' : ''}>🏢 Tetap</option>
                </select>

                <div class="input-wrapper" style="border-color: rgba(245, 158, 11, 0.4);">
                    <div class="prefix" style="color: var(--secondary); background: rgba(245, 158, 11, 0.1); border-color: rgba(245, 158, 11, 0.2); ${opacity}">Rp</div>
                    <input type="text" name="op_wage[]" class="wage-input op-wage" placeholder="Upah Standar" required onkeyup="formatRupiah(this)" autocomplete="off" style="color: var(--secondary); ${opacity}" value="${opWage}" ${isReadonly}>
                </div>
                
                <button type="button" class="btn-remove" onclick="removeOpRow(this)" title="Hapus Tahap"><i class="ph-bold ph-trash"></i></button>
            </div>
            
            <div class="op-custom-wages-container" style="padding-left: 55px; display:flex; flex-direction:column; gap:5px; margin-top:5px; ${exceptionDisplay}"></div>
            <div style="padding-left: 55px; margin-top:10px; ${exceptionDisplay}">
                <button type="button" class="btn-exception" onclick="addCustomWageRow(this.parentElement.previousElementSibling)" style="font-family: 'Plus Jakarta Sans', sans-serif; font-size:12px; font-weight:900; color:var(--info); background:rgba(59,130,246,0.1); border:1px dashed rgba(59,130,246,0.4); padding:10px 16px; border-radius:10px; cursor:pointer; transition:0.3s; display: inline-flex; align-items: center; gap: 6px;">
                    <i class="ph-bold ph-user-plus" style="font-size: 16px; vertical-align: middle; margin-right: 4px;"></i> Atur Harga Khusus Karyawan
                </button>
            </div>
        `;
        row.style.opacity = 0; row.style.transform = "translateY(20px)";
        containerEl.appendChild(row);
        
        let cwContainer = row.querySelector('.op-custom-wages-container');
        if (customWages && customWages.length > 0) {
            customWages.forEach(cw => addCustomWageRow(cwContainer, cw.employee_id, parseFloat(cw.wage).toLocaleString('id-ID')));
        }
        
        setTimeout(() => { row.style.opacity = 1; row.style.transform = "translateY(0)"; renumberOperations(containerEl); }, 10);
    }

    function removeOpRow(btn) {
        let container = btn.closest('.op-container');
        if(container.children.length <= 1) { 
            btn.parentElement.parentElement.animate([{ transform: 'translateX(-5px)' }, { transform: 'translateX(5px)' }, { transform: 'translateX(0)' }], { duration: 400 }); return; 
        }
        let row = btn.parentElement.parentElement;
        row.style.opacity = 0; row.style.transform = "scale(0.95)";
        setTimeout(() => { row.remove(); renumberOperations(container); }, 200);
    }

    function addRmRow(containerEl, type = 'rm', skuVal = '', sizeVal = 1, sizeUomVal = 'PCS', qtyVal = 1, qtyUomVal = 'PCS', totalVal = '') {
        let isRm = (type === 'rm');
        let row = document.createElement('div');
        row.className = 'dynamic-row ' + (isRm ? 'rm-row' : 'oh-row');
        row.dataset.editing = (skuVal !== '') ? 'true' : 'false'; 

        if (isRm) {
            row.innerHTML = `
                <select class="form-control select2-component item-sku" required onchange="updateMatRow(this)">
                    <option value=""></option> ${buildMaterialOptions()}
                </select>
                
                <div class="input-wrapper" style="border-color: rgba(59, 130, 246, 0.3);">
                    <input type="text" class="mat-size" placeholder="Ukuran" value="${sizeVal}" onkeyup="calcMatTotal(this)" onchange="calcMatTotal(this)">
                    <select class="prefix mat-uom" style="background:rgba(59, 130, 246, 0.1); color:var(--info); border:none; outline:none; font-weight:900; cursor:pointer; padding: 0 14px; border-left: 1px solid var(--border-subtle); width: auto;" onchange="calcMatTotal(this)">
                        ${buildUomOptions(sizeUomVal)}
                    </select>
                </div>

                <div class="input-wrapper" style="border-color: rgba(245, 158, 11, 0.3);">
                    <input type="text" class="mat-pcs" placeholder="Jml Item" value="${qtyVal}" onkeyup="calcMatTotal(this)" onchange="calcMatTotal(this)" required>
                    <select class="prefix mat-qty-uom" style="background:rgba(245, 158, 11, 0.1); color:var(--secondary); border:none; outline:none; font-weight:900; cursor:pointer; padding: 0 14px; border-left: 1px solid var(--border-subtle); width: auto;" onchange="calcMatTotal(this)">
                        ${buildUomOptions(qtyUomVal)}
                    </select>
                </div>

                <div class="input-wrapper" style="border-color: var(--success); background: rgba(16, 185, 129, 0.05);">
                    <div class="prefix" style="background:transparent; color:var(--success); border-right:none; font-size: 16px;">=</div>
                    <input type="text" class="qty-real item-qty" readonly value="${totalVal}" style="color:var(--success); font-weight:900; background:transparent;">
                    <div class="prefix base-uom-lbl" style="background:transparent; color:var(--success); border-left: 1px dashed rgba(16, 185, 129, 0.4);">PCS</div>
                </div>
                <button type="button" class="btn-remove" style="width: 45px; height: 45px;" onclick="this.parentElement.remove()" title="Hapus Baris"><i class="ph-bold ph-trash"></i></button>
            `;
        } else {
            row.innerHTML = `
                <select class="form-control select2-component item-sku" required onchange="updateOhRow(this)">
                    <option value=""></option> ${buildMaterialOptions()}
                </select>
                <div class="input-wrapper" style="border-color: rgba(236, 72, 153, 0.3);">
                    <input type="text" class="qty-real item-qty oh-qty qty-tampil" placeholder="Total Kebutuhan" value="${totalVal}" required style="color: var(--pink); font-weight:900;" onkeyup="this.value = this.value.replace(/,/g, '.').replace(/[^0-9.]/g, '')">
                    <div class="prefix oh-uom-lbl" style="background:rgba(236, 72, 153, 0.1); color:var(--pink); border-left: 1px solid var(--border-subtle);">Unit</div>
                </div>
                <button type="button" class="btn-remove" style="width: 45px; height: 45px;" onclick="this.parentElement.remove()" title="Hapus Baris"><i class="ph-bold ph-trash"></i></button>
            `;
        }
        
        row.style.opacity = 0; row.style.transform = "translateY(20px)";
        containerEl.appendChild(row);
        
        let $select = $(row).find('.select2-component');
        initializeSelect2Component($select);
        
        if(skuVal !== '') {
            $select.val(skuVal).trigger('change'); 
            if(isRm) {
                calcMatTotal(row.querySelector('.mat-size'));
            } else {
                updateOhRow($select[0]);
            }
        } else {
            if(isRm) calcMatTotal(row.querySelector('.mat-size'));
        }
        
        setTimeout(() => { 
            row.style.opacity = 1; 
            row.style.transform = "translateY(0)"; 
            row.dataset.editing = 'false';
        }, 10);
    }

    function buildSectionsPayload() {
        let payload = [];

        document.querySelectorAll('.section-card').forEach((sectionCard, index) => {
            let sectionName = sectionCard.querySelector('input[name="section_name[]"]').value.trim();
            let sectionCode = sectionCard.querySelector('input[name="section_code[]"]').value.trim();

            let materials = [];
            sectionCard.querySelectorAll('.rm-container .rm-row').forEach(row => {
                let sku       = row.querySelector('.item-sku')?.value || '';
                let size      = row.querySelector('.mat-size')?.value || 1;
                let size_uom  = row.querySelector('.mat-uom')?.value || 'PCS';
                let qty       = row.querySelector('.mat-pcs')?.value || 1;
                let qty_uom   = row.querySelector('.mat-qty-uom')?.value || 'PCS';
                let total     = row.querySelector('.item-qty')?.value || '';
                let total_uom = row.querySelector('.base-uom-lbl')?.innerText.trim() || 'PCS'; 
                
                if (sku && total && parseFloat(total) > 0) {
                    materials.push({ sku, size, size_uom, qty, qty_uom, total, total_uom });
                }
            });

            let overheads = [];
            sectionCard.querySelectorAll('.oh-container .oh-row').forEach(row => {
                let sku = row.querySelector('.item-sku')?.value || '';
                let total = row.querySelector('.item-qty')?.value || '';
                if (sku && total && parseFloat(total) > 0) {
                    overheads.push({ sku, total });
                }
            });

            let operations = [];
            sectionCard.querySelectorAll('.op-container .op-row').forEach((row, opIndex) => {
                let opName = row.querySelector('.op-name')?.value || '';
                let workerType = row.querySelector('.op-worker-type')?.value || 'Borongan';
                let specialty = row.querySelector('.op-specialty')?.value || ''; 
                let wage = row.querySelector('.op-wage')?.value || '0';
                
                let custom_wages = [];
                row.querySelectorAll('.custom-wage-row').forEach(cwRow => {
                    let eId = cwRow.querySelector('.cw-emp').value;
                    let w = cwRow.querySelector('.cw-wage').value.replace(/\./g, '');
                    if(eId && w) custom_wages.push({ employee_id: eId, wage: w });
                });

                if (opName.trim() !== '') {
                    operations.push({ step_order: opIndex + 1, name: opName, worker_type: workerType, specialty: specialty, wage: wage, custom_wages: custom_wages });
                }
            });

            payload.push({ section_order: index + 1, section_name: sectionName, section_code: sectionCode, materials, overheads, operations });
        });

        return payload;
    }

    function injectHiddenSectionsPayload() {
        document.querySelectorAll('.sections-json-input').forEach(el => el.remove());

        let input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'sections_json';
        input.value = JSON.stringify(buildSectionsPayload());
        input.className = 'sections-json-input';
        document.getElementById('formBOM').appendChild(input);
    }

    function editBom(id) {
        Swal.fire({ title: 'Memuat Resep...', allowOutsideClick: false, didOpen: () => { Swal.showLoading() } });

        fetch("<?= base_url('/production/get_bom/') ?>" + id, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(res => res.json())
        .then(res => {
            if(res.status === 'success') {
                Swal.close(); 
                document.body.classList.remove('swal2-height-auto');
                document.body.style.overflow = '';
                
                document.getElementById('btnTabBuat').click();
                document.getElementById('formTitle').style.display = 'flex';
                document.getElementById('formCard').style.borderTopColor = 'var(--info)';
                document.getElementById('formBOM').action = "<?= base_url('/production/update_bom/') ?>" + id;
                
                $('.select2-target').val(res.header.fg_sku).trigger('change');
                document.querySelector('input[name="recipe_name"]').value = res.header.recipe_name;

                document.getElementById('sections-container').innerHTML = '';
                sectionCounter = 0;

                if (res.sections && res.sections.length > 0) {
                    res.sections.forEach(sec => {
                        addSection(sec.section_name || '', sec.section_code || '');
                        let currentSection = document.querySelectorAll('.section-card')[document.querySelectorAll('.section-card').length - 1];
                        currentSection.querySelector('.rm-container').innerHTML = '';
                        currentSection.querySelector('.oh-container').innerHTML = '';
                        currentSection.querySelector('.op-container').innerHTML = '';

                        if (sec.materials && sec.materials.length > 0) {
                            sec.materials.forEach(item => {
                                addRmRow(
                                    currentSection.querySelector('.rm-container'), 'rm', 
                                    item.rm_sku, item.size_per_item, item.size_uom, item.qty_per_item, item.qty_uom, item.qty_required
                                );
                            });
                        } else {
                            addRmRow(currentSection.querySelector('.rm-container'), 'rm');
                        }

                        if (sec.overheads && sec.overheads.length > 0) {
                            sec.overheads.forEach(item => addRmRow(currentSection.querySelector('.oh-container'), 'oh', item.rm_sku, 1, 'PCS', 1, 'PCS', item.qty_required));
                        } else {
                            addRmRow(currentSection.querySelector('.oh-container'), 'oh');
                        }

                        if (sec.operations && sec.operations.length > 0) {
                            sec.operations.forEach(op => {
                                let formattedWage = parseFloat(op.wage_per_piece || 0).toLocaleString('id-ID');
                                addOpRow(currentSection.querySelector('.op-container'), op.operation_name, formattedWage, op.worker_type || 'Borongan', op.specialty_required || '', op.custom_wages || []);
                            });
                        } else {
                            addOpRow(currentSection.querySelector('.op-container'), '', '', 'Borongan', '', []);
                        }
                        renumberOperations(currentSection.querySelector('.op-container'));
                    });
                } else {
                    addSection('BAGIAN UTAMA', 'MAIN');
                    let currentSection = document.querySelector('.section-card');

                    currentSection.querySelector('.rm-container').innerHTML = '';
                    currentSection.querySelector('.oh-container').innerHTML = '';
                    currentSection.querySelector('.op-container').innerHTML = '';

                    let hasRM = false;
                    let hasOH = false;

                    if (res.items && res.items.length > 0) {
                        res.items.forEach(item => {
                            let rawQty = parseFloat(item.qty_required);
                            if (item.material_category === 'Overhead' || item.material_category === 'Consumable') {
                                addRmRow(currentSection.querySelector('.oh-container'), 'oh', item.rm_sku, 1, 'PCS', 1, 'PCS', rawQty);
                                hasOH = true;
                            } else {
                                addRmRow(currentSection.querySelector('.rm-container'), 'rm', item.rm_sku, item.size_per_item, item.size_uom, item.qty_per_item, item.qty_uom, rawQty);
                                hasRM = true;
                            }
                        });
                    }

                    if (!hasRM) addRmRow(currentSection.querySelector('.rm-container'), 'rm');
                    if (!hasOH) addRmRow(currentSection.querySelector('.oh-container'), 'oh');

                    if (res.ops && res.ops.length > 0) {
                        res.ops.forEach(op => {
                            let formattedWage = parseFloat(op.wage_per_piece || 0).toLocaleString('id-ID');
                            addOpRow(currentSection.querySelector('.op-container'), op.operation_name, formattedWage, op.worker_type || 'Borongan', op.specialty_required || '', op.custom_wages || []);
                        });
                    } else {
                        addOpRow(currentSection.querySelector('.op-container'), '', '', 'Borongan', '', []);
                    }

                    renumberOperations(currentSection.querySelector('.op-container'));
                }
                
                let btnSave = document.querySelector('#btnSubmitBOM');
                btnSave.querySelector('span').innerText = 'Perbarui Blueprint (Update)';
                btnSave.style.background = 'linear-gradient(135deg, var(--info), #2563eb)';
                document.getElementById('btnCancelEdit').style.display = 'inline-flex';
                window.scrollTo({ top: 0, behavior: 'smooth' });
            } else {
                Swal.fire('Error', res.message, 'error');
            }
        }).catch(err => {
            Swal.close(); 
            document.body.classList.remove('swal2-height-auto');
            document.body.style.overflow = '';
            console.error("Detail Error:", err);
            Swal.fire('Koneksi / Skrip Error', 'Terjadi kesalahan sistem: ' + err.message, 'error');
        });
    }

    function resetForm() {
        document.getElementById('formBOM').reset();
        document.getElementById('formBOM').action = "<?= base_url('/production/store_bom') ?>";
        
        $('.select2-target').val('').trigger('change');
        
        document.getElementById('formTitle').style.display = 'none';
        document.getElementById('formCard').style.borderTopColor = 'var(--primary)';
        document.getElementById('sections-container').innerHTML = '';
        sectionCounter = 0;

        addSection('BAGIAN UTAMA', 'MAIN');
        addRmRow(document.querySelector('.rm-container'), 'rm');
        addRmRow(document.querySelector('.oh-container'), 'oh');
        addOpRow(document.querySelector('.op-container'));

        let btnSave = document.querySelector('#btnSubmitBOM');
        btnSave.querySelector('span').innerText = 'Simpan Blueprint Produksi';
        btnSave.style.background = 'linear-gradient(135deg, var(--primary), var(--primary-dark))';
        document.getElementById('btnCancelEdit').style.display = 'none';
    }

    document.getElementById('formBOM').addEventListener('submit', function(e) {
        let isValid = true;

        if (document.querySelectorAll('.section-card').length < 1) {
            e.preventDefault();
            Swal.fire({
                title: 'Peringatan', 
                text: 'Minimal harus ada 1 bagian / section.', 
                icon: 'warning',
                customClass: { popup: 'swal2-custom-radius' }
            });
            return;
        }

        document.querySelectorAll('.section-card').forEach(section => {
            let sectionName = section.querySelector('input[name="section_name[]"]').value.trim();
            if (!sectionName) isValid = false;
        });

        $('.item-qty').each(function() {
            let realValue = $(this).val();
            let skuValue = $(this).closest('.dynamic-row').find('.item-sku').val();
            if(skuValue && (!realValue || parseFloat(realValue) <= 0 || isNaN(realValue))) {
                isValid = false;
                $(this).closest('.dynamic-row').css('border', '1px solid var(--danger)');
            } else {
                $(this).closest('.dynamic-row').css('border', '1px solid var(--border-subtle)');
            }
        });

        if(!isValid) {
            e.preventDefault();
            Swal.fire({
                title: 'Peringatan', 
                text: 'Pastikan nama bagian dan kuantitas material/overhead sudah valid.', 
                icon: 'warning',
                customClass: { popup: 'swal2-custom-radius' }
            });
            return;
        }

        injectHiddenSectionsPayload();

        const btn = document.getElementById('btnSubmitBOM');
        btn.style.transform = 'scale(0.98)';
        btn.style.opacity = '0.9'; 
        btn.style.pointerEvents = 'none';
        btn.querySelector('span').innerText = 'Menyimpan Resep...';
        btn.querySelector('i').className = 'ph-bold ph-spinner-gap ph-spin';
    });
    
    function formatRupiah(input) {
        let val = input.value.replace(/[^,\d]/g, '');
        if(!val) { input.value = ''; return; }
        let sisa = val.length % 3;
        let rupiah = val.substr(0, sisa);
        let ribuan = val.substr(sisa).match(/\d{3}/gi);
        if (ribuan) { let separator = sisa ? '.' : ''; rupiah += separator + ribuan.join('.'); }
        input.value = rupiah;
    }
</script>

<?= $this->endSection() ?>