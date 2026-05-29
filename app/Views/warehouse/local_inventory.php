<?= $this->extend('layout/template') ?>
<?= $this->section('content') ?>

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    /* ========================================================
       CORE VARIABLES & RESET
       ======================================================== */
    :root {
        --brand-blue: #3b82f6; --brand-blue-dark: #2563eb; --brand-blue-soft: rgba(59, 130, 246, 0.1);
        --brand-green: #10b981; --brand-green-dark: #059669; --brand-green-soft: rgba(16, 185, 129, 0.1);
        --brand-orange: #f59e0b; --brand-orange-dark: #d97706; --brand-orange-soft: rgba(245, 158, 11, 0.1);
        --brand-red: #ef4444; --brand-red-dark: #dc2626; --brand-red-soft: rgba(239, 68, 68, 0.1);
        
        --bg-body: #f8fafc; 
        --bg-surface: #ffffff; 
        --bg-input: #f1f5f9;
        
        --border-light: #e2e8f0;
        --border-focus: #cbd5e1;
        
        --text-dark: #0f172a; 
        --text-muted: #64748b;
        
        --shadow-sm: 0 2px 4px rgba(0,0,0,0.02);
        --shadow-md: 0 10px 25px -5px rgba(0,0,0,0.05);
        --shadow-lg: 0 20px 40px -10px rgba(0,0,0,0.1);
        --shadow-modal: 0 25px 50px -12px rgba(0,0,0,0.25);
        
        --radius-sm: 8px;
        --radius-md: 12px;
        --radius-lg: 16px;
        --radius-xl: 24px;
        
        --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    html.dark {
        --bg-body: #0f172a; --bg-surface: #1e293b; --bg-input: #0f172a;
        --border-light: #334155; --border-focus: #475569;
        --text-dark: #f8fafc; --text-muted: #94a3b8;
    }

    .ambient-glow { position: absolute; top: -150px; left: 50%; transform: translateX(-50%); width: 800px; height: 500px; background: radial-gradient(circle, var(--brand-blue-soft) 0%, transparent 70%); z-index: 0; pointer-events: none;}

    /* ========================================================
       PAGE HEADER & TABS
       ======================================================== */
    .page-header { margin-bottom: 25px; display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 15px; position: relative; z-index: 1;}
    .page-title { display: flex; align-items: center; gap: 16px; }
    .title-icon { width: 56px; height: 56px; border-radius: var(--radius-lg); background: linear-gradient(135deg, var(--brand-blue), var(--brand-blue-dark)); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 28px; box-shadow: 0 10px 25px -5px rgba(59, 130, 246, 0.4); }
    .title-text h1 { font-size: 28px; font-weight: 900; color: var(--text-dark); margin: 0; letter-spacing: -0.5px; line-height: 1.2;}
    .title-text p { font-size: 13px; color: var(--text-muted); font-weight: 600; margin: 4px 0 0 0;}
    
    .tab-nav { display: inline-flex; background: var(--bg-surface); padding: 8px; border-radius: var(--radius-lg); border: 1px solid var(--border-light); margin-bottom: 25px; box-shadow: var(--shadow-sm); position: relative; z-index: 1;}
    .tab-btn { padding: 12px 24px; font-size: 13px; font-weight: 800; border-radius: var(--radius-md); border: none; background: transparent; color: var(--text-muted); cursor: pointer; transition: var(--transition); display: flex; align-items: center; gap: 8px;}
    .tab-btn:hover { color: var(--text-dark); }
    .tab-btn.active { background: var(--text-dark); color: var(--bg-surface); box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
    html.dark .tab-btn.active { background: var(--brand-blue); color: #fff;}

    .tab-content { display: none; animation: slideFadeUp 0.4s ease; position: relative; z-index: 1;}
    .tab-content.active { display: block; }
    @keyframes slideFadeUp { from { opacity: 0; transform: translateY(15px); } to { opacity: 1; transform: translateY(0); } }

    /* ========================================================
       BUTTONS & UTILITIES
       ======================================================== */
    .btn-custom { color: #fff; border: none; padding: 12px 24px; border-radius: var(--radius-md); font-weight: 800; font-size: 13px; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; transition: var(--transition);}
    .btn-custom:hover { transform: translateY(-2px); filter: brightness(1.1);}
    
    .btn-blue { background: linear-gradient(135deg, var(--brand-blue), var(--brand-blue-dark)); box-shadow: 0 8px 20px -6px rgba(59, 130, 246, 0.5);}
    .btn-orange { background: linear-gradient(135deg, var(--brand-orange), var(--brand-orange-dark)); box-shadow: 0 8px 20px -6px rgba(245, 158, 11, 0.5);}
    .btn-red { background: linear-gradient(135deg, var(--brand-red), var(--brand-red-dark)); box-shadow: 0 8px 20px -6px rgba(239, 68, 68, 0.5);}

    .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
    .asset-summary { font-size: 13px; color: var(--text-muted); font-weight: 800; background: var(--bg-surface); padding: 14px 24px; border-radius: var(--radius-lg); border: 1px solid var(--border-light); display: inline-flex; align-items: center; gap: 12px; box-shadow: var(--shadow-sm);}
    .asset-val { color: var(--text-dark); font-size: 18px; font-weight: 900; font-family: 'Space Mono', monospace; letter-spacing: -0.5px;}

    /* ========================================================
       TABLE STYLES
       ======================================================== */
    .table-card { background: var(--bg-surface); border: 1px solid var(--border-light); border-radius: var(--radius-xl); box-shadow: var(--shadow-md); overflow: hidden; margin-top: 15px; transition: var(--transition);}
    .table-toolbar { padding: 20px 24px; border-bottom: 1px solid var(--border-light); display: flex; justify-content: space-between; align-items: center; gap: 15px; flex-wrap: wrap; background: rgba(0,0,0,0.01); }
    .toolbar-title { font-size: 15px; font-weight: 900; color: var(--text-dark); display: flex; align-items: center; gap: 8px; margin: 0; }
    
    .search-container { position: relative; width: 100%; max-width: 320px; }
    .search-container i { position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: var(--text-muted); font-size: 18px; pointer-events: none;}
    .search-input { width: 100%; background: var(--bg-input); border: 1px solid var(--border-light); padding: 12px 16px 12px 42px; border-radius: var(--radius-md); font-size: 13px; font-weight: 700; color: var(--text-dark); outline: none; transition: var(--transition); }
    .search-input:focus { border-color: var(--brand-blue); background: var(--bg-surface); box-shadow: 0 0 0 4px var(--brand-blue-soft); }

    table { width: 100%; border-collapse: collapse; white-space: nowrap; }
    th { text-align: center; padding: 16px 24px; font-size: 11px; font-weight: 900; text-transform: uppercase; color: var(--text-muted); border-bottom: 2px solid var(--border-light); background: rgba(0,0,0,0.01); letter-spacing: 0.5px;}
    td { text-align: center; padding: 16px 24px; border-bottom: 1px dashed var(--border-light); font-size: 13px; font-weight: 600; color: var(--text-dark); vertical-align: middle;}
    th:first-child, td:first-child, th:nth-child(2), td:nth-child(2) { text-align: left; } 
    tr:last-child td { border-bottom: none; }
    tr:hover td { background: var(--bg-input); }

    .sku-badge { padding: 4px 10px; border-radius: 6px; font-family: 'Space Mono', monospace; font-weight: 900; font-size: 11px; display: inline-block; border: 1px dashed transparent;}
    .sku-prd { background: var(--brand-blue-soft); color: var(--brand-blue); border-color: rgba(59,130,246,0.3);}
    .sku-mat { background: var(--brand-orange-soft); color: var(--brand-orange); border-color: rgba(245,158,11,0.3);}

    .stock-box { display: inline-flex; align-items: center; justify-content: center; padding: 6px 12px; border-radius: 8px; font-weight: 900; font-family: 'Space Mono', monospace; background: var(--brand-green-soft); color: var(--brand-green); border: 1px solid rgba(16,185,129,0.2);}
    .stock-danger { background: var(--brand-red-soft); color: var(--brand-red); border-color: rgba(239,68,68,0.3); animation: pulseDanger 2s infinite;}
    @keyframes pulseDanger { 0% { box-shadow: 0 0 0 0 rgba(239,68,68,0.4); } 70% { box-shadow: 0 0 0 6px rgba(239,68,68,0); } 100% { box-shadow: 0 0 0 0 rgba(239,68,68,0); } }

    .action-group { display: flex; justify-content: center; gap: 8px; }
    .btn-icon { width: 34px; height: 34px; display: inline-flex; align-items: center; justify-content: center; border-radius: 10px; font-size: 16px; transition: var(--transition); border: 1px solid transparent; cursor: pointer; text-decoration: none;}
    .btn-icon.edit { background: var(--brand-blue-soft); color: var(--brand-blue); }
    .btn-icon.edit:hover { background: var(--brand-blue); color: #fff; transform: translateY(-2px); box-shadow: 0 4px 10px rgba(59,130,246,0.3);}
    .btn-icon.del { background: var(--brand-red-soft); color: var(--brand-red); }
    .btn-icon.del:hover { background: var(--brand-red); color: #fff; transform: translateY(-2px); box-shadow: 0 4px 10px rgba(239,68,68,0.3);}

    .empty-state { text-align: center; padding: 50px 20px; color: var(--text-muted); }
    .empty-state i { font-size: 48px; color: var(--border-focus); margin-bottom: 15px; display: block; }

    /* ========================================================
       BEAUTIFUL MODALS & FORMS
       ======================================================== */
    .modal-overlay { position: fixed; inset: 0; background: rgba(15,23,42,0.7); backdrop-filter: blur(8px); z-index: 1000; display: none; justify-content: center; align-items: center; opacity: 0; transition: opacity 0.3s ease; padding: 20px; }
    .modal-overlay.active { display: flex; opacity: 1; }
    
    .modal-box { background: var(--bg-surface); border-radius: var(--radius-xl); width: 100%; max-width: 650px; padding: 35px; transform: scale(0.95) translateY(20px); transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1); box-shadow: var(--shadow-modal); max-height: 90vh; overflow-y: auto; border: 1px solid var(--border-light);}
    .modal-overlay.active .modal-box { transform: scale(1) translateY(0); }
    
    .modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; padding-bottom: 15px; border-bottom: 1px solid var(--border-light);}
    .modal-title { font-size: 18px; font-weight: 900; color: var(--text-dark); display: flex; align-items: center; gap: 12px;}
    
    .btn-close { background: var(--bg-input); border: 1px solid var(--border-light); width: 34px; height: 34px; border-radius: 50%; font-size: 16px; color: var(--text-muted); cursor: pointer; display: flex; align-items: center; justify-content: center; transition: 0.2s;}
    .btn-close:hover { background: var(--brand-red); color: #fff; border-color: var(--brand-red); transform: rotate(90deg);}
    
    .modal-group-box { background: var(--bg-surface); border: 1px solid var(--border-light); border-radius: var(--radius-lg); padding: 20px; margin-bottom: 20px; box-shadow: var(--shadow-sm);}
    .modal-group-title { font-size: 12px; font-weight: 900; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 18px; display: flex; align-items: center; gap: 8px; }
    
    .form-group { margin-bottom: 18px; }
    .form-group label { display: block; font-size: 11px; font-weight: 800; color: var(--text-muted); margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.5px;}
    
    .form-control { width: 100%; background: var(--bg-input); border: 1px solid var(--border-light); padding: 12px 16px; border-radius: var(--radius-md); font-size: 13px; font-weight: 700; color: var(--text-dark); outline: none; transition: var(--transition); box-sizing: border-box;}
    .form-control::placeholder { color: #94a3b8; font-weight: 500; }
    
    /* Focus States berdasarkan warna */
    .focus-blue:focus { border-color: var(--brand-blue); background: var(--bg-surface); box-shadow: 0 0 0 3px var(--brand-blue-soft);}
    .focus-orange:focus { border-color: var(--brand-orange); background: var(--bg-surface); box-shadow: 0 0 0 3px var(--brand-orange-soft);}
    .focus-red:focus { border-color: var(--brand-red); background: var(--bg-surface); box-shadow: 0 0 0 3px var(--brand-red-soft);}

    /* Input Group (Rp & Unit) */
    .input-group { display: flex; align-items: stretch; background: var(--bg-input); border: 1px solid var(--border-light); border-radius: var(--radius-md); overflow: hidden; transition: var(--transition); }
    .input-group:focus-within { background: var(--bg-surface); }
    .input-group.ig-blue:focus-within { border-color: var(--brand-blue); box-shadow: 0 0 0 3px var(--brand-blue-soft); }
    .input-group.ig-orange:focus-within { border-color: var(--brand-orange); box-shadow: 0 0 0 3px var(--brand-orange-soft); }
    
    .input-group-addon { padding: 12px 16px; font-size: 13px; font-weight: 900; background: rgba(0,0,0,0.02); display: flex; align-items: center; justify-content: center; border-right: 1px solid var(--border-light); color: var(--text-muted); }
    .input-group-addon.suffix { border-right: none; border-left: 1px solid var(--border-light); }
    .input-group input { flex: 1; border: none; background: transparent; padding: 12px 16px; font-size: 14px; font-weight: 800; color: var(--text-dark); outline: none; font-family: 'Space Mono', monospace; width: 100%;}
    
    /* Select2 Overrides agar senada dengan Form Control */
    .select2-container--default .select2-selection--single { background: var(--bg-input); border: 1px solid var(--border-light); height: auto; min-height: 46px; border-radius: var(--radius-md); display: flex; align-items: center; transition: var(--transition);}
    .select2-container--default.select2-container--focus .select2-selection--single,
    .select2-container--default.select2-container--open .select2-selection--single { border-color: var(--brand-blue); background: var(--bg-surface); box-shadow: 0 0 0 3px var(--brand-blue-soft); }
    .select2-container--default .select2-selection--single .select2-selection__rendered { font-weight: 700; font-size: 13px; color: var(--text-dark); padding: 8px 16px; line-height: 1.5;}
    .select2-container--default .select2-selection--single .select2-selection__arrow { height: 44px; right: 12px;}
    .select2-dropdown { border-radius: var(--radius-md); border: 1px solid var(--border-light); box-shadow: var(--shadow-lg); padding: 8px; background: var(--bg-surface); margin-top: 5px; z-index: 10001;}
    .select2-search__field { border-radius: var(--radius-sm) !important; padding: 8px 12px !important; border: 1px solid var(--border-light) !important; outline: none; font-family: inherit; font-weight: 600; background: var(--bg-input); color: var(--text-dark);}
    .select2-results__option { border-radius: var(--radius-sm); margin-bottom: 2px; font-weight: 600; font-size: 12px; padding: 8px 12px; color: var(--text-dark); transition: 0.2s;}
    .select2-container--default .select2-results__option--highlighted[aria-selected] { background: var(--brand-blue-soft) !important; color: var(--brand-blue) !important; border: 1px solid rgba(59,130,246,0.2);}

    .btn-submit-modal { width: 100%; color: #fff; border: none; padding: 16px; border-radius: var(--radius-lg); font-size: 14px; font-weight: 900; cursor: pointer; transition: var(--transition); display: flex; justify-content: center; align-items: center; gap: 8px; margin-top: 10px; letter-spacing: 0.5px; text-transform: uppercase;}
    .btn-submit-modal:hover { transform: translateY(-3px); filter: brightness(1.1); }
    
    .cat-selector { appearance: none; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%2364748b' viewBox='0 0 256 256'%3E%3Cpath d='M213.66,101.66l-80,80a8,8,0,0,1-11.32,0l-80-80A8,8,0,0,1,53.66,90.34L128,164.69l74.34-74.35a8,8,0,0,1,11.32,11.32Z'%3E%3C/path%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 14px center; padding-right: 35px; cursor: pointer; }

    .mini-note { display: block; font-size: 10.5px; color: var(--text-muted); font-weight: 600; margin-top: 6px; line-height: 1.4; }

    @media (max-width: 768px) {
        .grid-2 { grid-template-columns: 1fr; }
        .modal-box { padding: 25px; margin: 15px; width: calc(100% - 30px);}
    }
</style>

<div class="ambient-glow"></div>

<div class="page-wrapper">
    <div class="page-header">
        <div class="page-title">
            <div class="title-icon"><i class="ph-fill ph-database"></i></div>
            <div class="title-text">
                <h1>Master Inventaris Pabrik</h1>
                <p>Kelola data induk Produk Jadi (PRD), Material Dasar (MAT), dan Penyesuaian Stok.</p>
            </div>
        </div>
    </div>

    <div class="tab-nav">
        <button class="tab-btn active" onclick="switchTab('fg', event)"><i class="ph-fill ph-motorcycle"></i> Produk & Komponen</button>
        <button class="tab-btn" onclick="switchTab('rm', event)"><i class="ph-fill ph-nut"></i> Material Mentah</button>
        <button class="tab-btn" onclick="switchTab('adj', event)"><i class="ph-fill ph-scales"></i> Stok Opname</button>
    </div>

    <div id="tab-fg" class="tab-content active">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
            <div class="asset-summary">
                <div style="background: var(--brand-blue-soft); padding: 8px; border-radius: 10px; display: flex;"><i class="ph-fill ph-vault" style="color: var(--brand-blue); font-size: 18px;"></i></div>
                <div>Aset Produk (PRD): <br><span class="asset-val" style="color: var(--brand-blue);">Rp <?= number_format($totalValueFG, 0, ',', '.') ?></span></div>
            </div>
            <button class="btn-custom btn-blue" onclick="openCreateModalFG()">
                <i class="ph-bold ph-plus-circle" style="font-size: 18px;"></i> Registrasi Produk Baru
            </button>
        </div>

        <div class="table-card">
            <div class="table-toolbar">
                <h3 class="toolbar-title"><i class="ph-fill ph-list-dashes" style="color: var(--brand-blue);"></i> Daftar Produk Jadi</h3>
                <div class="search-container">
                    <i class="ph-bold ph-magnifying-glass"></i>
                    <input type="text" class="search-input focus-blue" id="searchDataFG" onkeyup="filterTable('searchDataFG', 'tbodyFG')" placeholder="Cari Nama, SKU..." autocomplete="off">
                </div>
            </div>

            <div style="overflow-x: auto;">
               <table>
                    <thead>
                        <tr>
                            <th>Kode SKU Produk</th>
                            <th>Nama Produk (Tipe & Kategori)</th>
                            <th style="text-align: right;">HPP (Modal)</th>
                            <th style="text-align: right;">Harga Ecer</th>
                            <th style="text-align: right;">Harga Grosir</th>
                            <th>Stok Tersedia</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="tbodyFG">
                        <?php if(empty($finishedGoods)): ?>
                            <tr>
                                <td colspan="7">
                                    <div class="empty-state">
                                        <i class="ph-fill ph-package"></i>
                                        <h3 style="margin: 0 0 5px 0; color: var(--text-dark); font-weight: 800; font-size: 16px;">Belum ada Produk (PRD)</h3>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                        <?php foreach($finishedGoods as $fg): ?>
                        <tr class="data-row">
                            <td><span class="sku-badge sku-prd"><?= esc($fg['sku']) ?></span></td>
                            <td>
                                <div style="font-weight: 800; margin-bottom: 6px; font-size: 13px;"><?= esc($fg['item_name']) ?></div>
                                <div style="display: flex; gap: 6px; align-items: center;">
                                    <span style="font-size: 9px; background: rgba(0,0,0,0.05); padding: 4px 8px; border-radius: 6px; font-weight: 800; color: var(--text-muted); text-transform: uppercase;"><i class="ph-fill ph-tag"></i> <?= esc($fg['item_type']) ?></span>
                                    
                                    <?php 
                                        $mc = $fg['motor_category'] ?? 'Universal';
                                        $mcColor = match($mc) { 'Matic' => '#3b82f6', 'Sport' => '#ef4444', 'Bebek' => '#10b981', default => '#64748b' };
                                        $mcBg = match($mc) { 'Matic' => 'rgba(59,130,246,0.1)', 'Sport' => 'rgba(239,68,68,0.1)', 'Bebek' => 'rgba(16,185,129,0.1)', default => 'rgba(100,116,139,0.1)' };
                                    ?>
                                    <span style="font-size: 9px; background: <?= $mcBg ?>; color: <?= $mcColor ?>; padding: 4px 8px; border-radius: 6px; font-weight: 900; text-transform: uppercase;">
                                        <i class="ph-fill ph-motorcycle"></i> <?= esc($mc) ?>
                                    </span>
                                </div>
                            </td>
                            
                            <td style="text-align: right; font-family: 'Space Mono', monospace; font-weight: 900; color: var(--brand-red); font-size: 13px;">
                                Rp <?= number_format($fg['hpp'], 0, ',', '.') ?>
                            </td>
                            
                            <td style="text-align: right;">
                                <div style="font-family: 'Space Mono', monospace; font-weight: 900; color: var(--brand-green); font-size: 13px;">
                                    Rp <?= number_format($fg['retail_price'] ?? 0, 0, ',', '.') ?>
                                </div>
                                <?php $marginRetail = ($fg['retail_price'] ?? 0) - $fg['hpp']; ?>
                                <div style="font-size: 10px; color: var(--text-muted); font-weight: 800; margin-top: 4px;">
                                    Laba: <span style="color: var(--brand-green);">+Rp <?= number_format($marginRetail, 0, ',', '.') ?></span>
                                </div>
                            </td>

                            <td style="text-align: right;">
                                <div style="font-family: 'Space Mono', monospace; font-weight: 900; color: var(--brand-blue); font-size: 13px;">
                                    Rp <?= number_format($fg['wholesale_price'] ?? 0, 0, ',', '.') ?>
                                </div>
                                <?php $marginGrosir = ($fg['wholesale_price'] ?? 0) - $fg['hpp']; ?>
                                <div style="font-size: 10px; color: var(--text-muted); font-weight: 800; margin-top: 4px;">
                                    Laba: <span style="color: var(--brand-blue);">+Rp <?= number_format($marginGrosir, 0, ',', '.') ?></span>
                                </div>
                            </td>

                            <td>
                                <div class="stock-box <?= ($fg['physical_stock'] <= $fg['min_stock']) ? 'stock-danger' : '' ?>" title="Minimum Stok: <?= $fg['min_stock'] ?>">
                                    <?= $fg['physical_stock'] ?>
                                </div>
                            </td>
                            <td>
                                <div class="action-group">
                                    <a href="#" onclick="openEditModalFG(<?= $fg['id'] ?>)" class="btn-icon edit" title="Edit Data"><i class="ph-bold ph-pencil-simple"></i></a>
                                    <a href="#" onclick="confirmDelete(event, '<?= base_url('/warehouse/delete_fg/'.$fg['id']) ?>')" class="btn-icon del" title="Hapus Data"><i class="ph-bold ph-trash"></i></a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div id="tab-rm" class="tab-content">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
            <div class="asset-summary">
                <div style="background: var(--brand-orange-soft); padding: 8px; border-radius: 10px; display: flex;"><i class="ph-fill ph-vault" style="color: var(--brand-orange); font-size: 18px;"></i></div>
                <div>Aset Material (MAT): <br><span class="asset-val" style="color: var(--brand-orange);">Rp <?= number_format($totalValueRM, 0, ',', '.') ?></span></div>
            </div>
            <button class="btn-custom btn-orange" onclick="openCreateModalRM()">
                <i class="ph-bold ph-plus-circle" style="font-size: 18px;"></i> Registrasi Material Baru
            </button>
        </div>

        <div class="table-card">
            <div class="table-toolbar">
                <h3 class="toolbar-title"><i class="ph-fill ph-list-dashes" style="color: var(--brand-orange);"></i> Daftar Material & Overhead</h3>
                <div class="search-container">
                    <i class="ph-bold ph-magnifying-glass"></i>
                    <input type="text" class="search-input focus-orange" id="searchDataRM" onkeyup="filterTable('searchDataRM', 'tbodyRM')" placeholder="Cari Nama, SKU..." autocomplete="off">
                </div>
            </div>

            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>Kode SKU Material</th>
                            <th>Nama Material / Overhead</th>
                            <th>Kategori</th>
                            <th>Satuan Gudang</th>
                            <th>Satuan Beli PO</th>
                            <th>Konversi Beli</th>
                            <th style="text-align: right;">Harga Modal per Gudang</th>
                            <th>Stok Gudang</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="tbodyRM">
                        <?php if(empty($rawMaterials)): ?>
                            <tr>
                                <td colspan="9">
                                    <div class="empty-state">
                                        <i class="ph-fill ph-nut"></i>
                                        <h3 style="margin: 0 0 5px 0; color: var(--text-dark); font-weight: 800; font-size: 16px;">Belum ada Bahan Baku</h3>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                        <?php foreach($rawMaterials as $rm): ?>
                        <?php
                            $baseUom = $rm['base_uom'] ?? $rm['unit'] ?? '-';
                            $purchaseUom = $rm['purchase_uom'] ?? $rm['unit'] ?? '-';
                            $conversionFactor = (float)($rm['conversion_factor'] ?? 1);
                            $materialCategory = $rm['material_category'] ?? 'General';
                        ?>
                        <tr class="data-row">
                            <td><span class="sku-badge sku-mat"><?= esc($rm['sku_material']) ?></span></td>
                            <td>
                                <div style="font-weight: 800; font-size: 13px;"><?= esc($rm['material_name']) ?></div>
                                <div style="margin-top: 6px;">
                                    <span style="font-size: 9px; background: rgba(0,0,0,0.05); padding: 4px 8px; border-radius: 6px; font-weight: 800; color: var(--text-muted); text-transform: uppercase;"><i class="ph-fill ph-cube"></i> <?= esc($materialCategory) ?></span>
                                </div>
                            </td>
                            <td><?= esc($materialCategory) ?></td>
                            <td><span class="sku-badge sku-mat" style="background: rgba(0,0,0,0.05); color:var(--text-muted); border:none;"><?= esc($baseUom) ?></span></td>
                            <td><span class="sku-badge sku-prd" style="background: rgba(0,0,0,0.05); color:var(--text-muted); border:none;"><?= esc($purchaseUom) ?></span></td>
                            <td style="font-family: 'Space Mono', monospace; font-weight: 900; font-size: 13px;">
                                <?= number_format($conversionFactor, 2, ',', '.') ?>
                            </td>
                            <td style="text-align: right;">
                                <div style="font-family: 'Space Mono', monospace; font-weight: 900; color: var(--brand-orange); font-size: 13px;">
                                    Rp <?= number_format($rm['hpp'], 0, ',', '.') ?>
                                </div>
                                <div style="font-size:10px; color:var(--text-muted); font-weight:700; margin-top:4px;">
                                    per 1 <?= esc($baseUom) ?>
                                </div>
                            </td>
                            <td>
                                <div class="stock-box <?= ($rm['physical_stock'] <= $rm['min_stock']) ? 'stock-danger' : '' ?>" title="Minimum Stok: <?= $rm['min_stock'] ?>" style="color:var(--brand-orange); background:var(--brand-orange-soft); border-color:rgba(245,158,11,0.2);">
                                    <?= number_format((float)$rm['physical_stock'], 2, ',', '.') ?>
                                    <span style="font-size:10px; margin-left:6px; font-family:'Plus Jakarta Sans', sans-serif;"><?= esc($baseUom) ?></span>
                                </div>
                            </td>
                            <td>
                                <div class="action-group">
                                    <a href="#" onclick="openEditModalRM(<?= $rm['id'] ?>)" class="btn-icon edit" style="color:var(--brand-orange); background:var(--brand-orange-soft);" title="Edit Data"><i class="ph-bold ph-pencil-simple"></i></a>
                                    <a href="#" onclick="confirmDelete(event, '<?= base_url('/warehouse/delete_rm/'.$rm['id']) ?>')" class="btn-icon del" title="Hapus Data"><i class="ph-bold ph-trash"></i></a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div id="tab-adj" class="tab-content">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
            <div style="font-size: 12px; color: var(--text-muted); font-weight: 600; background: var(--bg-surface); padding: 14px 20px; border-radius: var(--radius-md); border: 1px solid var(--border-light); display:flex; align-items:center; gap:10px; box-shadow: var(--shadow-sm);">
                <div style="background:var(--brand-red-soft); padding:6px; border-radius:8px; color:var(--brand-red);"><i class="ph-fill ph-info" style="font-size: 18px;"></i></div> 
                Catat penyesuaian jika ada barang rusak (Scrap), cacat produksi, atau selisih hitung fisik gudang.
            </div>
            <button class="btn-custom btn-red" onclick="openModal('modalAdj')">
                <i class="ph-bold ph-scales" style="font-size: 18px;"></i> Lakukan Penyesuaian Stok
            </button>
        </div>

        <div class="table-card">
            <div class="table-toolbar">
                <h3 class="toolbar-title"><i class="ph-fill ph-list-dashes" style="color: var(--brand-red);"></i> Riwayat Penyesuaian Stok</h3>
                <div class="search-container">
                    <i class="ph-bold ph-magnifying-glass"></i>
                    <input type="text" class="search-input focus-red" id="searchDataAdj" onkeyup="filterTable('searchDataAdj', 'tbodyAdj')" placeholder="Cari Riwayat..." autocomplete="off">
                </div>
            </div>

            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>Waktu & PIC</th>
                            <th>Barang yang Disesuaikan</th>
                            <th>Perubahan</th>
                            <th style="text-align: right;">Valuasi Finansial</th>
                            <th>Keterangan / Alasan</th>
                        </tr>
                    </thead>
                    <tbody id="tbodyAdj">
                        <?php if(empty($adjustments)): ?>
                            <tr>
                                <td colspan="5">
                                    <div class="empty-state">
                                        <i class="ph-fill ph-clipboard-text"></i>
                                        <h3 style="margin: 0 0 5px 0; color: var(--text-dark); font-weight: 800; font-size: 16px;">Belum ada Riwayat Penyesuaian</h3>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                        <?php foreach($adjustments as $adj): ?>
                        <tr class="data-row">
                            <td>
                                <div style="font-size: 11px; font-weight: 800; color: var(--text-muted);"><i class="ph-bold ph-calendar-blank"></i> <?= date('d M Y, H:i', strtotime($adj['created_at'])) ?></div>
                                <div style="font-size: 13px; font-weight: 700; margin-top: 4px; color: var(--text-dark);"><i class="ph-bold ph-user"></i> <?= esc($adj['pic_name']) ?></div>
                            </td>
                            <td>
                                <div style="font-weight: 800; font-size: 13px; margin-bottom: 6px;"><?= esc($adj['item_name']) ?></div>
                                <span class="sku-badge <?= $adj['item_type'] == 'PRD' ? 'sku-prd' : 'sku-mat' ?>"><?= esc($adj['sku']) ?></span>
                            </td>
                            <td>
                                <?php if($adj['adjustment_type'] == 'PLUS'): ?>
                                    <span style="background: var(--brand-green-soft); color: var(--brand-green); padding: 4px 10px; border-radius: 6px; font-weight: 900; font-family: monospace; font-size: 13px;">+<?= floatval($adj['qty']) ?></span>
                                <?php else: ?>
                                    <span style="background: var(--brand-red-soft); color: var(--brand-red); padding: 4px 10px; border-radius: 6px; font-weight: 900; font-family: monospace; font-size: 13px;">-<?= floatval($adj['qty']) ?></span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: right; font-family: 'Space Mono', monospace; font-weight: 800; font-size: 13px; <?= $adj['adjustment_type'] == 'PLUS' ? 'color: var(--brand-green);' : 'color: var(--brand-red);' ?>">
                                Rp <?= number_format($adj['financial_value'], 0, ',', '.') ?>
                            </td>
                            <td style="white-space: normal; line-height: 1.5; max-width: 250px; font-size: 12px; color: var(--text-muted); text-align: left; font-weight: 600;">
                                <?= esc($adj['reason']) ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal-overlay" id="modalFG">
    <div class="modal-box" style="border-top: 6px solid var(--brand-blue);">
        <div class="modal-header">
            <div class="modal-title" id="titleFG">
                <div style="background: var(--brand-blue-soft); width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; color: var(--brand-blue);"><i class="ph-fill ph-motorcycle"></i></div>
                Input Produk Baru
            </div>
            <button class="btn-close" onclick="closeModal('modalFG')" type="button"><i class="ph-bold ph-x"></i></button>
        </div>
        <form id="formFG" action="<?= base_url('/warehouse/store_fg') ?>" method="post">
            <?= csrf_field() ?>
            
            <div class="modal-group-box" style="background: rgba(59, 130, 246, 0.02); border-color: rgba(59, 130, 246, 0.15);">
                <div class="modal-group-title" style="color: var(--brand-blue);"><i class="ph-fill ph-tag"></i> 1. Identitas & Kategori Produk</div>
                
                <div class="form-group">
                    <label>Nama Lengkap Produk (Cth: BEAT KOLONG INLET 38)</label>
                    <input type="text" name="item_name" class="form-control focus-blue" placeholder="Cth: Knalpot WR155 Full" required autocomplete="off">
                </div>
                
                <div class="grid-2" style="margin-bottom: 0;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label>Kategori Motor</label>
                        <select name="motor_category" class="form-control focus-blue cat-selector" required>
                            <option value="Matic">Matic (Beat, Vario, dll)</option>
                            <option value="Sport">Sport (CBR, Ninja, dll)</option>
                            <option value="Bebek">Bebek (Supra, MX, dll)</option>
                            <option value="Universal">Universal / Aksesoris</option>
                        </select>
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label>Tipe Produk (Sifat Fisik)</label>
                        <select name="item_type" class="form-control focus-blue cat-selector" required>
                            <option value="Full System">Full System (Utuh)</option>
                            <option value="Silencer / Slip-on">Silencer / Slip-on</option>
                            <option value="Header / Leheran">Header / Leheran</option>
                            <option value="Aksesoris / Sparepart">Aksesoris / Sparepart Lainnya</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="modal-group-box">
                <div class="modal-group-title" style="color: var(--text-muted);"><i class="ph-fill ph-currency-circle-dollar"></i> 2. Pengaturan Harga & Modal</div>
                
                <div class="form-group">
                    <label>Harga Pokok Penjualan (HPP Modal)</label>
                    <div class="input-group ig-blue">
                        <span class="input-group-addon">Rp</span>
                        <input type="text" name="hpp" id="fg_hpp" placeholder="0" required onkeyup="formatRupiah(this)" autocomplete="off">
                    </div>
                </div>
                
                <div class="grid-2" style="margin-bottom: 0;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label style="color: var(--brand-green);">Harga Retail (Eceran)</label>
                        <div class="input-group ig-blue">
                            <span class="input-group-addon" style="color: var(--brand-green); background: var(--brand-green-soft);">Rp</span>
                            <input type="text" name="retail_price" id="fg_retail" placeholder="0" required onkeyup="formatRupiah(this)" autocomplete="off" style="color: var(--brand-green);">
                        </div>
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label style="color: var(--brand-blue);">Harga Grosir (B2B)</label>
                        <div class="input-group ig-blue">
                            <span class="input-group-addon" style="color: var(--brand-blue); background: var(--brand-blue-soft);">Rp</span>
                            <input type="text" name="wholesale_price" id="fg_wholesale" placeholder="0" required onkeyup="formatRupiah(this)" autocomplete="off" style="color: var(--brand-blue);">
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-group-box" style="margin-bottom: 0;">
                <div class="modal-group-title" style="color: var(--text-muted);"><i class="ph-fill ph-stack"></i> 3. Parameter Stok Gudang</div>
                <div class="grid-2" style="margin-bottom: 0;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label>Stok Fisik Saat Ini</label>
                        <div class="input-group ig-blue">
                            <input type="number" name="initial_stock" id="initial_stock_fg" value="0" required min="0" style="text-align: center;">
                            <span class="input-group-addon suffix">PCS</span>
                        </div>
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label style="color: var(--brand-red);">Peringatan Stok Minimum</label>
                        <div class="input-group ig-blue">
                            <input type="number" name="min_stock" value="5" required min="1" style="text-align: center; color: var(--brand-red);">
                            <span class="input-group-addon suffix" style="color: var(--brand-red); background: var(--brand-red-soft);">PCS</span>
                        </div>
                    </div>
                </div>
            </div>

            <button type="submit" id="btnSubmitFG" class="btn-submit-modal btn-blue">
                <i class="ph-bold ph-floppy-disk" style="font-size: 20px;"></i> <span>Simpan Data Produk</span>
            </button>
        </form>
    </div>
</div>

<div class="modal-overlay" id="modalRM">
    <div class="modal-box" style="border-top: 6px solid var(--brand-orange);">
        <div class="modal-header">
            <div class="modal-title" id="titleRM">
                <div style="background: var(--brand-orange-soft); width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; color: var(--brand-orange);"><i class="ph-fill ph-nut"></i></div>
                Input Material & Overhead
            </div>
            <button class="btn-close" onclick="closeModal('modalRM')" type="button"><i class="ph-bold ph-x"></i></button>
        </div>

        <form id="formRM" action="<?= base_url('/warehouse/store_rm') ?>" method="post">
            <?= csrf_field() ?>
            
            <div class="modal-group-box" style="background: rgba(245, 158, 11, 0.03); border-color: rgba(245, 158, 11, 0.15);">
                <div class="modal-group-title" style="color: var(--brand-orange);"><i class="ph-fill ph-tag"></i> 1. Identitas & Klasifikasi</div>

                <div class="form-group">
                    <label>Nama Material (Cth: Pipa SS 304, Paku Rivet)</label>
                    <input type="text" name="material_name" class="form-control focus-orange" placeholder="Cth: Pipa Stainless 2 Inch SS304" required autocomplete="off">
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label>Kategori Material Gudang</label>
                    <select name="material_category" class="form-control focus-orange cat-selector" required>
                        <option value="General">General</option>
                        <option value="Pipa">Pipa / Tube (Barang Potongan)</option>
                        <option value="Plat">Plat / Sheet (Barang Potongan)</option>
                        <option value="Batang">Batang / Rod (Barang Potongan)</option>
                        <option value="Fastener">Fastener / Baut / Mur (Barang Utuh)</option>
                        <option value="Welding">Welding / Las</option>
                        <option value="Finishing">Finishing / Polish / Coating</option>
                        <option value="Packing">Packing</option>
                        <option value="Sub Assembly">Sub Assembly</option>
                        <option value="Consumable">Consumable (Barang Habis Pakai)</option>
                    </select>
                </div>
            </div>

            <div class="modal-group-box">
                <div class="modal-group-title" style="color: var(--text-muted);"><i class="ph-fill ph-scales"></i> 2. Pengaturan Satuan & Konversi</div>
                <div class="grid-2">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label>Satuan Beli (Ke Supplier)</label>
                        <select name="purchase_uom" id="rm_purchase_uom" class="form-control select2-uom" required>
                            <option value="">-- Pilih Satuan --</option>
                            <?php foreach($uomMaster as $uom): ?>
                                <option value="<?= esc($uom['uom_code']) ?>"><?= esc($uom['uom_name']) ?> (<?= esc($uom['uom_code']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                        <small class="mini-note">Satuan saat beli partai besar.</small>
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label>Satuan Pemakaian (Pabrik)</label>
                        <select name="base_uom" id="rm_base_uom" class="form-control select2-uom" required>
                            <option value="">-- Pilih Satuan --</option>
                            <?php foreach($uomMaster as $uom): ?>
                                <option value="<?= esc($uom['uom_code']) ?>"><?= esc($uom['uom_name']) ?> (<?= esc($uom['uom_code']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                        <small class="mini-note">Satuan saat dipotong tukang.</small>
                    </div>
                </div>

                <div id="conversion_box" style="display: none; margin-top: 15px; padding: 15px; background: rgba(59, 130, 246, 0.05); border: 1px dashed rgba(59, 130, 246, 0.3); border-radius: 12px;">
                    <label style="font-size: 11px; font-weight: 900; color: var(--brand-blue); margin-bottom: 10px; display: block; text-transform: uppercase;">
                        <i class="ph-fill ph-arrows-left-right"></i> Rumus Konversi Otomatis
                    </label>
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <div style="flex: 1; text-align: center; font-size: 13px; font-weight: 800; background: var(--bg-surface); padding: 12px; border: 1px solid var(--border-light); border-radius: 10px;">
                            1 <span id="lbl_purch_uom" style="color: var(--brand-blue); font-weight: 900;">BATANG</span>
                        </div>
                        <div style="font-weight: 900; color: var(--text-muted); font-size: 16px;">=</div>
                        <div class="input-group ig-blue" style="flex: 1; border-radius: 10px;">
                            <input type="number" step="0.0001" min="0.0001" name="conversion_factor" id="rm_conv_factor" placeholder="Cth: 600" style="text-align: center; font-size: 15px;">
                            <span class="input-group-addon suffix" id="lbl_base_uom" style="color: var(--brand-blue); background: var(--brand-blue-soft);">CM</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-group-box" style="margin-bottom: 0;">
                <div class="modal-group-title" style="color: var(--text-muted);"><i class="ph-fill ph-currency-circle-dollar"></i> 3. Harga Pokok & Stok Fisik</div>
                
                <div class="form-group">
                    <label>Harga Beli Pokok per <span id="lbl_hpp_uom" style="color: var(--brand-orange); font-size: 12px;">SATUAN PABRIK</span></label>
                    <div class="input-group ig-orange">
                        <span class="input-group-addon">Rp</span>
                        <input type="text" name="hpp" id="rm_hpp" placeholder="0" required onkeyup="formatRupiah(this)" autocomplete="off">
                    </div>
                    <small class="mini-note" id="note_hpp" style="color: var(--brand-red); font-weight: 700;"></small>
                </div>

                <div class="grid-2" style="margin-bottom: 0;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label>Stok Fisik Saat Ini</label>
                        <div class="input-group ig-orange">
                            <input type="number" step="0.01" name="initial_stock" id="initial_stock_rm" value="0" required min="0" style="text-align: center;">
                            <span class="input-group-addon suffix" id="lbl_stok_uom">UNIT</span>
                        </div>
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label style="color: var(--brand-red);">Peringatan Stok Minimum</label>
                        <div class="input-group ig-orange">
                            <input type="number" step="0.01" name="min_stock" id="rm_min_stock" value="10" required min="0" style="text-align: center; color: var(--brand-red);">
                            <span class="input-group-addon suffix" style="color: var(--brand-red); background: var(--brand-red-soft); border-left-color: rgba(239,68,68,0.2);">UNIT</span>
                        </div>
                    </div>
                </div>
            </div>

            <button type="submit" id="btnSubmitRM" class="btn-submit-modal btn-orange">
                <i class="ph-bold ph-floppy-disk" style="font-size: 20px;"></i> <span>Simpan Data Material</span>
            </button>
        </form>
    </div>
</div>

<div class="modal-overlay" id="modalAdj">
    <div class="modal-box" style="border-top: 6px solid var(--brand-red);">
        <div class="modal-header">
            <div class="modal-title">
                <div style="background: var(--brand-red-soft); width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; color: var(--brand-red);"><i class="ph-fill ph-scales"></i></div>
                Penyesuaian Stok (Opname)
            </div>
            <button class="btn-close" onclick="closeModal('modalAdj')" type="button"><i class="ph-bold ph-x"></i></button>
        </div>

        <form id="formAdj" action="<?= base_url('/warehouse/store_adjustment') ?>" method="post">
            <?= csrf_field() ?>
            
            <div class="modal-group-box" style="background: rgba(239, 68, 68, 0.02); border-color: rgba(239, 68, 68, 0.15);">
                <div class="modal-group-title" style="color: var(--brand-red);"><i class="ph-fill ph-target"></i> 1. Target Barang & Sifat Opname</div>
                
                <div class="form-group">
                    <label>Pilih Barang yang Ingin Disesuaikan</label>
                    <select name="sku" id="adj_sku_select" class="form-control" required style="width: 100%;">
                        <option value=""></option>
                        <optgroup label="📦 Produk Jadi (Kategori PRD)">
                            <?php foreach($finishedGoods as $fg): ?>
                                <option value="<?= esc($fg['sku']) ?>">[<?= esc($fg['sku']) ?>] <?= esc($fg['item_name']) ?> (Sisa: <?= $fg['physical_stock'] ?>)</option>
                            <?php endforeach; ?>
                        </optgroup>
                        <optgroup label="⚙️ Material Mentah (Kategori MAT)">
                            <?php foreach($rawMaterials as $rm): ?>
                                <?php $baseUomAdj = $rm['base_uom'] ?? $rm['unit'] ?? ''; ?>
                                <option value="<?= esc($rm['sku_material']) ?>">[<?= esc($rm['sku_material']) ?>] <?= esc($rm['material_name']) ?> (Sisa: <?= number_format((float)$rm['physical_stock'], 2, ',', '.') ?> <?= esc($baseUomAdj) ?>)</option>
                            <?php endforeach; ?>
                        </optgroup>
                    </select>
                </div>

                <div class="grid-2" style="margin-bottom: 0;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label>Jenis Perubahan Stok</label>
                        <select name="adjustment_type" class="form-control focus-red cat-selector" required style="font-weight: 800;">
                            <option value="MINUS">📉 Kurangi Stok (Hilang/Scrap)</option>
                            <option value="PLUS">📈 Tambah Stok (Kelebihan Fisik)</option>
                        </select>
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label>Kuantitas (Qty) Penyesuaian</label>
                        <div class="input-group ig-red" style="border-color: rgba(239,68,68,0.3);">
                            <input type="number" step="0.01" name="qty" placeholder="Cth: 2" required min="0.01" style="text-align: center; color: var(--brand-red);">
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-group-box" style="margin-bottom: 0;">
                <div class="modal-group-title" style="color: var(--text-muted);"><i class="ph-fill ph-file-text"></i> 2. Keterangan & Laporan</div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label>Alasan / Berita Acara Penyesuaian (Wajib)</label>
                    <textarea name="reason" id="adjReasonInput" class="form-control focus-red" rows="2" placeholder="Ketik alasan atau klik tombol cepat di bawah..." required style="resize: none;"></textarea>
                    
                    <div style="display: flex; gap: 8px; flex-wrap: wrap; margin-top: 10px;">
                        <span class="sku-badge" onclick="setAdjReason('Barang diambil tim produksi ke bengkel')" style="cursor: pointer; background: var(--bg-input); color: var(--text-muted); border: 1px dashed var(--border-light);">Diambil Produksi</span>
                        <span class="sku-badge" onclick="setAdjReason('Barang rusak / cacat produksi (Scrap)')" style="cursor: pointer; background: var(--brand-red-soft); color: var(--brand-red); border: 1px dashed rgba(239,68,68,0.3);">Cacat / Rusak</span>
                        <span class="sku-badge" onclick="setAdjReason('Penyesuaian selisih hitung fisik gudang')" style="cursor: pointer; background: var(--brand-orange-soft); color: var(--brand-orange); border: 1px dashed rgba(245,158,11,0.3);">Selisih Opname</span>
                    </div>
                </div>
            </div>
            
            <button type="submit" id="btnSubmitAdj" class="btn-submit-modal btn-red">
                <i class="ph-bold ph-paper-plane-tilt" style="font-size: 20px;"></i> <span>Eksekusi & Jurnal Otomatis</span>
            </button>
        </form>
    </div>
</div>

<script>
    // ========================================================
    // INITIALIZATION & TAB LOGIC
    // ========================================================
    $(document).ready(function() {
        $('#adj_sku_select').select2({
            placeholder: "-- Pilih SKU Barang / Material --",
            allowClear: true,
            dropdownParent: $('#modalAdj')
        });
        
        $('.select2-uom').select2({
            dropdownParent: $('#modalRM'),
            width: '100%'
        });

        // Logika Pintar Pergantian UOM
        $('#rm_purchase_uom, #rm_base_uom').on('change', function() {
            let purchUom = $('#rm_purchase_uom').val() || 'SATUAN_BELI';
            let baseUom = $('#rm_base_uom').val() || 'SATUAN_PABRIK';
            let convBox = $('#conversion_box');
            let convInput = $('#rm_conv_factor');
            let noteHpp = $('#note_hpp');
            
            $('#lbl_purch_uom').text(purchUom);
            $('#lbl_base_uom').text(baseUom);
            $('#lbl_hpp_uom').text(baseUom);
            $('#lbl_stok_uom').text(baseUom);

            if(purchUom !== baseUom && purchUom !== 'SATUAN_BELI' && baseUom !== 'SATUAN_PABRIK') {
                convBox.slideDown(300);
                if(convInput.val() == 1 || convInput.val() == "") {
                    convInput.val('');
                }
                noteHpp.html(`<i class="ph-fill ph-warning-circle"></i> PERHATIAN: Masukkan harga modal untuk 1 ${baseUom} (bukan 1 ${purchUom}).`);
            } else {
                convBox.slideUp(300);
                convInput.val(1);
                noteHpp.html(`* Masukkan harga modal untuk 1 ${baseUom}.`);
            }
        });
    });

    function switchTab(tabName, event = null) {
        document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));

        if (event && event.currentTarget) {
            event.currentTarget.classList.add('active');
        } else {
            const tabMap = { fg: 0, rm: 1, adj: 2 };
            const btns = document.querySelectorAll('.tab-btn');
            if (btns[tabMap[tabName]]) btns[tabMap[tabName]].classList.add('active');
        }

        document.getElementById('tab-' + tabName).classList.add('active');
    }

    document.addEventListener("DOMContentLoaded", function() {
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('tab') === 'rm') switchTab('rm');
        else if (urlParams.get('tab') === 'adj') switchTab('adj');
        else switchTab('fg');
    });

    // ========================================================
    // MODAL HANDLERS
    // ========================================================
    function openModal(modalId) { 
        document.getElementById(modalId).classList.add('active'); 
        document.body.style.overflow = 'hidden'; 
    }

    function closeModal(modalId) { 
        document.getElementById(modalId).classList.remove('active'); 
        document.body.style.overflow = ''; 
    }

    function openCreateModalFG() {
        document.getElementById('formFG').reset();
        document.getElementById('titleFG').innerHTML = '<div style="background: var(--brand-blue-soft); width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; color: var(--brand-blue);"><i class="ph-fill ph-motorcycle"></i></div> Input Produk (PRD) Baru';
        document.getElementById('formFG').action = "<?= base_url('/warehouse/store_fg') ?>";
        
        document.querySelector('#modalFG select[name="motor_category"]').value = 'Universal';
        document.querySelector('#modalFG select[name="item_type"]').value = 'Full System';
        
        let stockInput = document.getElementById('initial_stock_fg');
        stockInput.readOnly = false;
        stockInput.style.backgroundColor = "transparent";
        stockInput.title = "";
        
        // HAPUS ATAU COMMENT BARIS DI BAWAH INI (Karena ID wrap_fg_hpp sudah diganti di desain baru)
        // document.getElementById('wrap_fg_hpp').classList.remove('im-readonly');
        
        openModal('modalFG');
    }

    function openCreateModalRM() {
        document.getElementById('formRM').reset();
        document.getElementById('titleRM').innerHTML = '<div style="background: var(--brand-orange-soft); width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; color: var(--brand-orange);"><i class="ph-fill ph-nut"></i></div> Input Material & Overhead';
        document.getElementById('formRM').action = "<?= base_url('/warehouse/store_rm') ?>";

        let stockInput = document.getElementById('initial_stock_rm');
        stockInput.readOnly = false;
        stockInput.style.backgroundColor = "transparent";
        stockInput.title = "";

        document.querySelector('#modalRM select[name="material_category"]').value = 'General';
        document.querySelector('#modalRM input[name="conversion_factor"]').value = 1;

        $('#modalRM select[name="base_uom"]').val('').trigger('change');
        $('#modalRM select[name="purchase_uom"]').val('').trigger('change');
        
        openModal('modalRM');
    }

    function openEditModalFG(id) {
        document.getElementById('formFG').reset();
        document.getElementById('titleFG').innerHTML = '<div style="background: var(--brand-blue-soft); width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; color: var(--brand-blue);"><i class="ph-fill ph-pencil-simple"></i></div> Edit Data Produk (PRD)';
        document.getElementById('formFG').action = "<?= base_url('/warehouse/update_fg/') ?>" + id;
        
        let stockInput = document.getElementById('initial_stock_fg');
        stockInput.readOnly = true;
        stockInput.style.backgroundColor = "var(--bg-input)";
        stockInput.title = "Gunakan fitur Stok Opname untuk mengubah kuantitas fisik gudang.";

        if(typeof window.showGlobalToast === 'function') window.showGlobalToast("Mengambil data...");

        fetch("<?= base_url('/warehouse/get_fg/') ?>" + id, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(res => res.json())
        .then(data => {
            document.querySelector('#modalFG input[name="item_name"]').value = data.item_name;
            document.querySelector('#modalFG select[name="item_type"]').value = data.item_type;
            document.querySelector('#modalFG select[name="motor_category"]').value = data.motor_category || 'Universal';
            document.querySelector('#modalFG input[name="min_stock"]').value = data.min_stock;
            stockInput.value = data.physical_stock; 
            
            let hpp = document.getElementById('fg_hpp');
            let retail = document.getElementById('fg_retail');
            let wholesale = document.getElementById('fg_wholesale');
            
            hpp.value = Math.round(parseFloat(data.hpp || 0)); 
            retail.value = Math.round(parseFloat(data.retail_price || 0)); 
            wholesale.value = Math.round(parseFloat(data.wholesale_price || 0));
            
            formatRupiah(hpp); formatRupiah(retail); formatRupiah(wholesale);
            
            openModal('modalFG');
        });
    }

    function openEditModalRM(id) {
        document.getElementById('formRM').reset();
        document.getElementById('titleRM').innerHTML = '<div style="background: var(--brand-orange-soft); width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; color: var(--brand-orange);"><i class="ph-fill ph-pencil-simple"></i></div> Edit Data Material';
        document.getElementById('formRM').action = "<?= base_url('/warehouse/update_rm/') ?>" + id;
        
        let stockInput = document.getElementById('initial_stock_rm');
        stockInput.readOnly = true;
        stockInput.style.backgroundColor = "var(--bg-input)";
        stockInput.title = "Gunakan fitur Stok Opname untuk mengubah kuantitas fisik gudang.";

        if(typeof window.showGlobalToast === 'function') window.showGlobalToast("Mengambil data...");

        fetch("<?= base_url('/warehouse/get_rm/') ?>" + id, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(res => res.json())
        .then(data => {
            document.querySelector('#modalRM input[name="material_name"]').value = data.material_name;
            document.querySelector('#modalRM select[name="material_category"]').value = data.material_category || 'General';
            
            let baseUomVal = data.base_uom || data.unit || '';
            let purchaseUomVal = data.purchase_uom || data.unit || '';
            
            $('#modalRM select[name="base_uom"]').val(baseUomVal).trigger('change');
            $('#modalRM select[name="purchase_uom"]').val(purchaseUomVal).trigger('change');
            
            document.querySelector('#modalRM input[name="conversion_factor"]').value = data.conversion_factor || 1;
            document.querySelector('#modalRM input[name="min_stock"]').value = data.min_stock;
            stockInput.value = data.physical_stock; 
            
            let hpp = document.getElementById('rm_hpp');
            hpp.value = Math.round(parseFloat(data.hpp || 0));
            formatRupiah(hpp);

            openModal('modalRM');
        });
    }

    // ========================================================
    // UTILITIES & SUBMIT LOGIC
    // ========================================================
    function formatRupiah(angka) {
        if (!angka || !angka.value) return;
        let number_string = angka.value.replace(/[^,\d]/g, '').toString(),
            split   = number_string.split(','),
            sisa    = split[0].length % 3,
            rupiah  = split[0].substr(0, sisa),
            ribuan  = split[0].substr(sisa).match(/\d{3}/gi);
            
        if (ribuan) {
            let separator = sisa ? '.' : '';
            rupiah += separator + ribuan.join('.');
        }
        angka.value = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
    }

    function filterTable(inputId, tbodyId) {
        let input = document.getElementById(inputId);
        let filter = input.value.toLowerCase();
        let tbody = document.getElementById(tbodyId);
        let rows = tbody.querySelectorAll('tr.data-row');

        rows.forEach(row => {
            let textData = row.textContent.toLowerCase();
            row.style.display = textData.indexOf(filter) > -1 ? "" : "none";
        });
    }

    function setAdjReason(text) {
        document.getElementById('adjReasonInput').value = text;
    }

    function handleAjaxForm(formId, btnId, redirectTab) {
        document.getElementById(formId).addEventListener('submit', function(e) {
            e.preventDefault(); 
            
            // Clean up currency inputs before submitting
            this.querySelectorAll('input[type="text"]').forEach(input => {
                if(input.id.includes('hpp') || input.id.includes('retail') || input.id.includes('wholesale')) {
                    input.value = input.value.replace(/\./g, '');
                }
            });

            const btn = document.getElementById(btnId);
            const btnText = btn.querySelector('span');
            const btnIcon = btn.querySelector('i');
            const originalText = btnText.innerText;
            const originalIcon = btnIcon.className;
            
            btn.disabled = true;
            btnText.innerText = "Memproses...";
            btnIcon.className = "ph-bold ph-spinner-gap ph-spin";

            fetch(this.action, {
                method: 'POST',
                body: new FormData(this),
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(res => res.json())
            .then(data => {
                if(data.status === 'success') {
                    if(typeof window.showGlobalToast === 'function') window.showGlobalToast(data.message);
                    this.reset();
                    if($(this).find('.select2-search').length > 0) {
                        $(this).find('.select2-search').val(null).trigger('change');
                    }
                    document.querySelectorAll('.modal-overlay').forEach(m => m.classList.remove('active'));
                    document.body.style.overflow = '';
                    
                    setTimeout(() => { window.location.replace("<?= base_url('/warehouse/local-inventory') ?>" + redirectTab); }, 1200);
                } else {
                    if(typeof window.showGlobalToast === 'function') window.showGlobalToast(data.message, true);
                    btn.disabled = false; btnText.innerText = originalText; btnIcon.className = originalIcon;
                }
            })
            .catch(err => {
                if(typeof window.showGlobalToast === 'function') window.showGlobalToast("Koneksi Server Gagal", true);
                btn.disabled = false; btnText.innerText = originalText; btnIcon.className = originalIcon;
            });
        });
    }

    handleAjaxForm('formFG', 'btnSubmitFG', '?tab=fg');
    handleAjaxForm('formRM', 'btnSubmitRM', '?tab=rm');
    handleAjaxForm('formAdj', 'btnSubmitAdj', '?tab=adj');

    function confirmDelete(e, url) {
        e.preventDefault();
        const isDark = document.documentElement.classList.contains('dark');
        Swal.fire({
            title: 'Hapus Data?', 
            text: "Aksi ini akan menghapus data secara permanen dari sistem.",
            icon: 'warning', 
            showCancelButton: true, 
            confirmButtonColor: '#ef4444', 
            cancelButtonColor: isDark ? '#334155' : '#cbd5e1', 
            confirmButtonText: 'Ya, Hapus Saja',
            cancelButtonText: 'Batal',
            background: isDark ? '#1e293b' : '#ffffff', 
            color: isDark ? '#f8fafc' : '#0f172a',
            customClass: { popup: 'swal2-custom-radius' }
        }).then((result) => {
            if (result.isConfirmed) {
                if(url.includes('delete_rm')) window.location.href = url + '?tab=rm'; 
                else window.location.href = url;
            }
        })
    }
</script>

<?= $this->endSection() ?>