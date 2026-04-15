<?= $this->extend('layout/template') ?>
<?= $this->section('content') ?>

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    :root {
        --brand: #3b82f6; --brand-dark: #2563eb; --brand-soft: rgba(59, 130, 246, 0.1);
        --success: #10b981; --success-soft: rgba(16, 185, 129, 0.1);
        --warning: #f59e0b; --warning-soft: rgba(245, 158, 11, 0.1);
        --danger: #ef4444; --danger-soft: rgba(239, 68, 68, 0.1);
        
        --bg-main: #f8fafc; --card-bg: #ffffff; --border-color: #e2e8f0;
        --text-main: #0f172a; --text-muted: #64748b;
        
        --shadow-sm: 0 1px 3px rgba(0,0,0,0.05);
        --shadow-md: 0 4px 15px -5px rgba(0,0,0,0.05);
        --shadow-hover: 0 15px 35px -10px rgba(0,0,0,0.08);
        --transition-smooth: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    }

    .swal2-container { z-index: 10000 !important; }
    .swal2-custom-radius { border-radius: 20px !important; font-family: 'Plus Jakarta Sans', sans-serif; }

    .ambient-glow { position: absolute; top: -150px; left: 50%; transform: translateX(-50%); width: 800px; height: 500px; background: radial-gradient(circle, rgba(59, 130, 246, 0.08) 0%, transparent 70%); z-index: 0; pointer-events: none;}
    html.dark .ambient-glow { background: radial-gradient(circle, rgba(59, 130, 246, 0.15) 0%, transparent 70%); }

    .page-header { margin-bottom: 25px; display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 15px; position: relative; z-index: 1;}
    .page-title { display: flex; align-items: center; gap: 16px; }
    .title-icon { width: 56px; height: 56px; border-radius: 18px; background: linear-gradient(135deg, var(--brand), var(--brand-dark)); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 28px; box-shadow: 0 10px 25px -5px rgba(59, 130, 246, 0.4); }
    .title-text h1 { font-size: 30px; font-weight: 900; color: var(--text-main); margin: 0; letter-spacing: -1px; line-height: 1.1;}
    .title-text p { font-size: 13px; color: var(--text-muted); font-weight: 600; margin: 4px 0 0 0; letter-spacing: -0.2px;}
    
    .tab-nav { display: inline-flex; background: var(--card-bg); padding: 8px; border-radius: 16px; border: 1px solid var(--border-color); margin-bottom: 25px; box-shadow: var(--shadow-sm); position: relative; z-index: 1;}
    .tab-btn { padding: 12px 24px; font-size: 14px; font-weight: 800; border-radius: 12px; border: none; background: transparent; color: var(--text-muted); cursor: pointer; transition: var(--transition-smooth); display: flex; align-items: center; gap: 8px;}
    .tab-btn:hover { color: var(--text-main); }
    .tab-btn.active { background: var(--text-main); color: var(--card-bg); box-shadow: 0 4px 15px rgba(0,0,0,0.15); }
    html.dark .tab-btn.active { background: var(--brand); color: #fff;}

    .tab-content { display: none; animation: slideFadeUp 0.4s cubic-bezier(0.16, 1, 0.3, 1); position: relative; z-index: 1;}
    .tab-content.active { display: block; }
    @keyframes slideFadeUp { from { opacity: 0; transform: translateY(15px); } to { opacity: 1; transform: translateY(0); } }

    .table-toolbar { padding: 20px; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center; gap: 15px; flex-wrap: wrap; background: rgba(248, 250, 252, 0.5); }
    .toolbar-title { font-size: 16px; font-weight: 900; color: var(--text-main); display: flex; align-items: center; gap: 8px; margin: 0; }
    .search-container { position: relative; width: 100%; max-width: 320px; }
    .search-container i { position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: var(--text-muted); font-size: 18px; pointer-events: none;}
    .search-input { width: 100%; background: var(--bg-main); border: 1px solid var(--border-color); padding: 12px 16px 12px 42px; border-radius: 14px; font-size: 13px; font-weight: 700; color: var(--text-main); outline: none; transition: var(--transition-smooth); }
    .search-input:focus { border-color: var(--brand); background: var(--card-bg); box-shadow: 0 0 0 4px var(--brand-soft); }

    .btn-primary { background: linear-gradient(135deg, #3b82f6, #2563eb); color: #fff; border: none; padding: 12px 24px; border-radius: 14px; font-weight: 900; font-size: 13px; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 8px 20px -6px rgba(59, 130, 246, 0.5); transition: 0.3s;}
    .btn-primary:hover { transform: translateY(-3px); box-shadow: 0 12px 25px -6px rgba(59, 130, 246, 0.6);}
    
    .btn-warning { background: linear-gradient(135deg, #f59e0b, #d97706); color: #fff; border: none; padding: 12px 24px; border-radius: 14px; font-weight: 900; font-size: 13px; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 8px 20px -6px rgba(245, 158, 11, 0.5); transition: 0.3s;}
    .btn-warning:hover { transform: translateY(-3px); box-shadow: 0 12px 25px -6px rgba(245, 158, 11, 0.6);}

    .btn-danger { background: linear-gradient(135deg, #ef4444, #dc2626); color: #fff; border: none; padding: 12px 24px; border-radius: 14px; font-weight: 900; font-size: 13px; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 8px 20px -6px rgba(239, 68, 68, 0.5); transition: 0.3s;}
    .btn-danger:hover { transform: translateY(-3px); box-shadow: 0 12px 25px -6px rgba(239, 68, 68, 0.6);}

    .asset-summary { font-size: 13px; color: var(--text-muted); font-weight: 800; background: var(--card-bg); padding: 14px 24px; border-radius: 16px; border: 1px solid var(--border-color); display: inline-flex; align-items: center; gap: 10px; box-shadow: var(--shadow-sm);}
    .asset-val { color: var(--text-main); font-size: 18px; font-weight: 900; font-family: 'Space Mono', monospace; letter-spacing: -0.5px;}

    .table-card { background: var(--card-bg); border: 1px solid var(--border-color); border-radius: 24px; box-shadow: var(--shadow-md); overflow: hidden; margin-top: 20px; transition: var(--transition-smooth);}
    .table-card:hover { box-shadow: var(--shadow-hover); }
    table { width: 100%; border-collapse: collapse; white-space: nowrap; }
    
    th { text-align: center; padding: 16px 20px; font-size: 11px; font-weight: 900; text-transform: uppercase; color: var(--text-muted); border-bottom: 2px solid var(--border-color); background: rgba(248, 250, 252, 0.5); letter-spacing: 0.5px;}
    td { text-align: center; padding: 16px 20px; border-bottom: 1px dashed var(--border-color); font-size: 13px; font-weight: 600; color: var(--text-main); vertical-align: middle; transition: 0.2s;}
    
    th:first-child, td:first-child { text-align: left; }
    th:nth-child(2), td:nth-child(2) { text-align: left; } 
    tr:last-child td { border-bottom: none; }
    tr:hover td { background: var(--brand-soft); }

    .sku-badge { padding: 6px 12px; border-radius: 8px; font-family: 'Space Mono', monospace; font-weight: 900; font-size: 12px; display: inline-block; border: 1px dashed transparent; letter-spacing: -0.5px;}
    .sku-prd { background: rgba(59, 130, 246, 0.1); color: #3b82f6; border-color: rgba(59, 130, 246, 0.3);}
    .sku-mat { background: rgba(245, 158, 11, 0.1); color: #f59e0b; border-color: rgba(245, 158, 11, 0.3);}

    .stock-box { display: inline-flex; align-items: center; justify-content: center; padding: 6px 14px; border-radius: 8px; font-weight: 900; font-family: 'Space Mono', monospace; background: rgba(16, 185, 129, 0.1); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.2);}
    .stock-danger { background: rgba(239, 68, 68, 0.1); color: #ef4444; border-color: rgba(239, 68, 68, 0.3); animation: pulseDanger 2s infinite;}
    @keyframes pulseDanger { 0% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.4); } 70% { box-shadow: 0 0 0 6px rgba(239, 68, 68, 0); } 100% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); } }

    .action-group { display: flex; justify-content: center; gap: 8px; }
    .btn-edit { color: #3b82f6; background: rgba(59, 130, 246, 0.1); font-size: 18px; transition: 0.3s; width: 36px; height: 36px; display: inline-flex; align-items: center; justify-content: center; border-radius: 10px; border: 1px solid transparent; text-decoration: none;}
    .btn-edit:hover { color: #fff; background: #3b82f6; box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3); transform: translateY(-2px);}
    
    .btn-delete { color: #ef4444; background: rgba(239, 68, 68, 0.1); font-size: 18px; transition: 0.3s; width: 36px; height: 36px; display: inline-flex; align-items: center; justify-content: center; border-radius: 10px; border: 1px solid transparent; text-decoration: none;}
    .btn-delete:hover { color: #fff; background: #ef4444; box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3); transform: translateY(-2px);}

    .empty-state { text-align: center; padding: 60px 20px; color: var(--text-muted); }
    .empty-state i { font-size: 56px; color: var(--border-color); margin-bottom: 15px; display: block; }
    .empty-state p { font-size: 14px; font-weight: 500; margin: 0;}

    .modal-overlay { position: fixed; inset: 0; background: rgba(15,23,42,0.65); backdrop-filter: blur(8px); z-index: 1000; display: none; justify-content: center; align-items: center; opacity: 0; transition: opacity 0.3s ease; padding: 20px; }
    .modal-overlay.active { display: flex; opacity: 1; }
    
    .modal-box { background: var(--card-bg); border-radius: 28px; width: 100%; max-width: 700px; padding: 40px; transform: scale(0.95) translateY(20px); transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1); box-shadow: 0 30px 60px -15px rgba(0,0,0,0.5); border: 1px solid var(--border-color); max-height: 95vh; overflow-y: auto;}
    .modal-overlay.active .modal-box { transform: scale(1) translateY(0); }
    
    .modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
    .modal-title { font-size: 20px; font-weight: 900; color: var(--text-main); display: flex; align-items: center; gap: 12px;}
    
    .btn-close { background: var(--bg-main); border: 1px solid var(--border-color); width: 36px; height: 36px; border-radius: 50%; font-size: 16px; color: var(--text-muted); cursor: pointer; display: flex; align-items: center; justify-content: center; transition: 0.2s;}
    .btn-close:hover { background: var(--danger); color: #fff; border-color: var(--danger); transform: rotate(90deg);}
    
    .form-group { margin-bottom: 20px; }
    .form-group label { display: flex; justify-content: space-between; font-size: 11px; font-weight: 900; color: var(--text-muted); margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px;}
    
    .form-control { width: 100%; background: var(--bg-main); border: 1px solid var(--border-color); padding: 14px 18px; border-radius: 14px; font-size: 14px; color: var(--text-main); font-weight: 700; outline: none; transition: 0.3s; box-sizing: border-box;}
    .form-control:focus { border-color: var(--brand); background: var(--card-bg); box-shadow: 0 0 0 4px var(--brand-soft);}
    
    .select2-container--default .select2-selection--single { background: var(--bg-main); border: 1px solid var(--border-color); height: auto; min-height: 54px; border-radius: 14px; display: flex; align-items: center; transition: 0.3s;}
    .select2-container--default.select2-container--focus .select2-selection--single { border-color: var(--warning); background: var(--card-bg); box-shadow: 0 0 0 4px rgba(245, 158, 11, 0.15); }
    .select2-container--default .select2-selection--single .select2-selection__rendered { font-weight: 800; font-size: 14px; color: var(--text-main); padding: 8px 16px; width: 100%;}
    .select2-container--default .select2-selection--single .select2-selection__arrow { height: 52px; right: 15px;}
    .select2-dropdown { border-radius: 16px; border: 1px solid var(--border-color); box-shadow: 0 20px 50px rgba(0,0,0,0.15); padding: 12px; background: var(--card-bg);}
    .select2-search__field { border-radius: 10px !important; padding: 10px 14px !important; border: 1px solid var(--border-color) !important; outline: none; font-family: inherit; font-weight: 700; background: var(--bg-main); color: var(--text-main);}
    .select2-results__option { border-radius: 10px; margin-bottom: 4px; font-weight: 700; font-size: 13px; padding: 6px 12px; color: var(--text-main);}
    .select2-container--default .select2-results__option--highlighted[aria-selected] { background: var(--bg-main) !important; color: var(--text-main) !important; border: 1px solid var(--border-color);}
    .select2-results__option[aria-disabled="true"] { opacity: 0.5; background: transparent !important; cursor: not-allowed; }

    .btn-submit-modal { width: 100%; color: #fff; border: none; padding: 18px; border-radius: 16px; font-size: 15px; font-weight: 900; cursor: pointer; transition: all 0.3s ease; display: flex; justify-content: center; align-items: center; gap: 10px; margin-top: 10px; }
    .btn-submit-modal.btn-blue { background: linear-gradient(135deg, var(--brand), var(--brand-dark)); box-shadow: 0 8px 20px -5px rgba(59, 130, 246, 0.5); }
    .btn-submit-modal.btn-orange { background: linear-gradient(135deg, var(--warning), #d97706); box-shadow: 0 8px 20px -5px rgba(245, 158, 11, 0.5); }
    .btn-submit-modal.btn-red { background: linear-gradient(135deg, var(--danger), #dc2626); box-shadow: 0 8px 20px -5px rgba(239, 68, 68, 0.5); }
    .btn-submit-modal:hover { transform: translateY(-3px); filter: brightness(1.1); }
    
    .cat-selector { appearance: none; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%2364748b' viewBox='0 0 256 256'%3E%3Cpath d='M213.66,101.66l-80,80a8,8,0,0,1-11.32,0l-80-80A8,8,0,0,1,53.66,90.34L128,164.69l74.34-74.35a8,8,0,0,1,11.32,11.32Z'%3E%3C/path%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 16px center; padding-right: 40px; cursor: pointer; }

    .modal-group-box { padding: 20px; border-radius: 16px; margin-bottom: 20px; border: 1px solid var(--border-color); background: var(--bg-main); }
    .modal-group-title { font-size: 11px; font-weight: 900; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 16px; display: flex; align-items: center; gap: 8px; border-bottom: 1px dashed var(--border-color); padding-bottom: 10px; }
    
    .reason-chips { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 10px;}
    .chip { font-size: 11px; font-weight: 800; color: var(--danger); background: var(--danger-soft); border: 1px dashed rgba(239, 68, 68, 0.3); padding: 6px 12px; border-radius: 100px; cursor: pointer; transition: 0.2s;}
    .chip:hover { background: var(--danger); color: #fff;}

    .mini-note { display: block; font-size: 11px; color: var(--text-muted); font-weight: 700; margin-top: 6px; line-height: 1.5; }

    .pill-info { display: inline-flex; align-items: center; gap: 6px; padding: 5px 10px; border-radius: 999px; font-size: 10px; font-weight: 900; background: rgba(59, 130, 246, 0.08); color: var(--brand); border: 1px dashed rgba(59, 130, 246, 0.25); text-transform: uppercase; letter-spacing: .4px; }

    @media (max-width: 768px) {
        .grid-2, .grid-3 { grid-template-columns: 1fr; }
        .modal-box { padding: 24px; }
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
        <button class="tab-btn active" onclick="switchTab('fg', event)"><i class="ph-fill ph-motorcycle"></i> Produk & Komponen (PRD)</button>
        <button class="tab-btn" onclick="switchTab('rm', event)"><i class="ph-fill ph-nut"></i> Material & Overhead (MAT)</button>
        <button class="tab-btn" onclick="switchTab('adj', event)"><i class="ph-fill ph-scales"></i> Penyesuaian / Opname</button>
    </div>

    <div id="tab-fg" class="tab-content active">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
            <div class="asset-summary">
                <div style="background: var(--brand-soft); padding: 6px; border-radius: 8px; display: flex;"><i class="ph-fill ph-vault" style="color: var(--brand);"></i></div>
                Aset Produk (PRD): <span class="asset-val">Rp <?= number_format($totalValueFG, 0, ',', '.') ?></span>
            </div>
            <button class="btn-primary" onclick="openCreateModalFG()">
                <i class="ph-bold ph-plus-circle" style="font-size: 18px;"></i> Registrasi Produk Baru
            </button>
        </div>

        <div class="table-card">
            <div class="table-toolbar">
                <h3 class="toolbar-title"><i class="ph-fill ph-list-dashes" style="color: var(--brand);"></i> Daftar Produk Jadi</h3>
                <div class="search-container">
                    <i class="ph-bold ph-magnifying-glass"></i>
                    <input type="text" class="search-input" id="searchDataFG" onkeyup="filterTable('searchDataFG', 'tbodyFG')" placeholder="Cari Nama, SKU, atau Harga..." autocomplete="off">
                </div>
            </div>

            <div style="overflow-x: auto;">
               <table>
                    <thead>
                        <tr>
                            <th>Kode SKU Produk</th>
                            <th>Nama Produk (Tipe)</th>
                            <th style="text-align: right;">HPP (Modal)</th>
                            <th style="text-align: right;">Harga Retail</th>
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
                                        <h3 style="margin: 0 0 5px 0; color: var(--text-main); font-weight: 800; font-size: 16px;">Belum ada Produk (PRD)</h3>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                        <?php foreach($finishedGoods as $fg): ?>
                        <tr class="data-row">
                            <td><span class="sku-badge sku-prd"><?= esc($fg['sku']) ?></span></td>
                            <td>
                                <div style="font-weight: 800; margin-bottom: 4px; font-size: 14px;"><?= esc($fg['item_name']) ?></div>
                                <span style="font-size: 10px; background: rgba(0,0,0,0.05); padding: 2px 6px; border-radius: 4px; font-weight: 800; color: var(--text-muted); text-transform: uppercase;"><?= esc($fg['item_type']) ?></span>
                            </td>
                            
                            <td style="text-align: right; font-family: 'Space Mono', monospace; font-weight: 900; color: var(--danger); font-size: 14px;">
                                Rp <?= number_format($fg['hpp'], 0, ',', '.') ?>
                            </td>
                            
                            <td style="text-align: right;">
                                <div style="font-family: 'Space Mono', monospace; font-weight: 900; color: var(--success); font-size: 14px;">
                                    Rp <?= number_format($fg['retail_price'] ?? 0, 0, ',', '.') ?>
                                </div>
                                <?php $marginRetail = ($fg['retail_price'] ?? 0) - $fg['hpp']; ?>
                                <div style="font-size: 10px; color: var(--text-muted); font-weight: 800; margin-top: 4px;">
                                    Laba: <span style="color: var(--success);">+Rp <?= number_format($marginRetail, 0, ',', '.') ?></span>
                                </div>
                            </td>

                            <td style="text-align: right;">
                                <div style="font-family: 'Space Mono', monospace; font-weight: 900; color: var(--brand); font-size: 14px;">
                                    Rp <?= number_format($fg['wholesale_price'] ?? 0, 0, ',', '.') ?>
                                </div>
                                <?php $marginGrosir = ($fg['wholesale_price'] ?? 0) - $fg['hpp']; ?>
                                <div style="font-size: 10px; color: var(--text-muted); font-weight: 800; margin-top: 4px;">
                                    Laba: <span style="color: var(--brand);">+Rp <?= number_format($marginGrosir, 0, ',', '.') ?></span>
                                </div>
                            </td>

                            <td>
                                <div class="stock-box <?= ($fg['physical_stock'] <= $fg['min_stock']) ? 'stock-danger' : '' ?>" title="Minimum Stok: <?= $fg['min_stock'] ?>">
                                    <?= $fg['physical_stock'] ?>
                                </div>
                            </td>
                            <td>
                                <div class="action-group">
                                    <a href="#" onclick="openEditModalFG(<?= $fg['id'] ?>)" class="btn-edit" title="Edit Data">
                                        <i class="ph-bold ph-pencil-simple"></i>
                                    </a>
                                    <a href="#" onclick="confirmDelete(event, '<?= base_url('/warehouse/delete_fg/'.$fg['id']) ?>')" class="btn-delete" title="Hapus Data">
                                        <i class="ph-bold ph-trash"></i>
                                    </a>
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
                <div style="background: var(--warning-soft); padding: 6px; border-radius: 8px; display: flex;"><i class="ph-fill ph-vault" style="color: var(--warning);"></i></div>
                Aset Material (MAT): <span class="asset-val">Rp <?= number_format($totalValueRM, 0, ',', '.') ?></span>
            </div>
            <button class="btn-warning" onclick="openCreateModalRM()">
                <i class="ph-bold ph-plus-circle" style="font-size: 18px;"></i> Registrasi Material Baru
            </button>
        </div>

        <div class="table-card">
            <div class="table-toolbar">
                <h3 class="toolbar-title"><i class="ph-fill ph-list-dashes" style="color: var(--warning);"></i> Daftar Material & Overhead</h3>
                <div class="search-container">
                    <i class="ph-bold ph-magnifying-glass"></i>
                    <input type="text" class="search-input" id="searchDataRM" onkeyup="filterTable('searchDataRM', 'tbodyRM')" placeholder="Cari Nama, SKU, Satuan, atau Harga..." autocomplete="off">
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
                                        <h3 style="margin: 0 0 5px 0; color: var(--text-main); font-weight: 800; font-size: 16px;">Belum ada Bahan Baku</h3>
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
                                <div style="font-weight: 800; font-size: 14px;"><?= esc($rm['material_name']) ?></div>
                                <div style="margin-top: 6px;">
                                    <span class="pill-info"><i class="ph-fill ph-cube"></i> <?= esc($materialCategory) ?></span>
                                </div>
                            </td>
                            <td><?= esc($materialCategory) ?></td>
                            <td><span class="sku-badge sku-mat"><?= esc($baseUom) ?></span></td>
                            <td><span class="sku-badge sku-prd"><?= esc($purchaseUom) ?></span></td>
                            <td style="font-family: 'Space Mono', monospace; font-weight: 900;">
                                <?= number_format($conversionFactor, 2, ',', '.') ?>
                            </td>
                            <td style="text-align: right;">
                                <div style="font-family: 'Space Mono', monospace; font-weight: 900; color: var(--warning); font-size: 14px;">
                                    Rp <?= number_format($rm['hpp'], 0, ',', '.') ?>
                                </div>
                                <div style="font-size:10px; color:var(--text-muted); font-weight:800; margin-top:4px;">
                                    / <?= esc($baseUom) ?>
                                </div>
                            </td>
                            <td>
                                <div class="stock-box <?= ($rm['physical_stock'] <= $rm['min_stock']) ? 'stock-danger' : '' ?>" title="Minimum Stok: <?= $rm['min_stock'] ?>">
                                    <?= number_format((float)$rm['physical_stock'], 2, ',', '.') ?>
                                    <span style="font-size:10px; margin-left:6px; font-family:'Plus Jakarta Sans', sans-serif;"><?= esc($baseUom) ?></span>
                                </div>
                            </td>
                            <td>
                                <div class="action-group">
                                    <a href="#" onclick="openEditModalRM(<?= $rm['id'] ?>)" class="btn-edit" title="Edit Data">
                                        <i class="ph-bold ph-pencil-simple"></i>
                                    </a>
                                    <a href="#" onclick="confirmDelete(event, '<?= base_url('/warehouse/delete_rm/'.$rm['id']) ?>')" class="btn-delete" title="Hapus Data">
                                        <i class="ph-bold ph-trash"></i>
                                    </a>
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
            <div style="font-size: 13px; color: var(--text-muted); font-weight: 600; background: var(--card-bg); padding: 14px 20px; border-radius: 16px; border: 1px solid var(--border-color);">
                <i class="ph-fill ph-info" style="color: var(--danger); font-size: 16px; vertical-align: middle; margin-right: 6px;"></i> Catat penyesuaian jika ada barang rusak (Scrap), cacat produksi, atau selisih fisik gudang.
            </div>
            <button class="btn-danger" onclick="openModal('modalAdj')">
                <i class="ph-bold ph-scales" style="font-size: 18px;"></i> Lakukan Penyesuaian Stok
            </button>
        </div>

        <div class="table-card">
            <div class="table-toolbar">
                <h3 class="toolbar-title"><i class="ph-fill ph-list-dashes" style="color: var(--danger);"></i> Riwayat Penyesuaian Stok</h3>
                <div class="search-container">
                    <i class="ph-bold ph-magnifying-glass"></i>
                    <input type="text" class="search-input" id="searchDataAdj" onkeyup="filterTable('searchDataAdj', 'tbodyAdj')" placeholder="Cari Riwayat..." autocomplete="off">
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
                                        <h3 style="margin: 0 0 5px 0; color: var(--text-main); font-weight: 800; font-size: 16px;">Belum ada Riwayat Penyesuaian</h3>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                        <?php foreach($adjustments as $adj): ?>
                        <tr class="data-row">
                            <td>
                                <div style="font-size: 11px; font-weight: 800; color: var(--text-muted);"><i class="ph-bold ph-calendar-blank"></i> <?= date('d M Y, H:i', strtotime($adj['created_at'])) ?></div>
                                <div style="font-size: 13px; font-weight: 700; margin-top: 4px; color: var(--text-main);"><i class="ph-bold ph-user"></i> <?= esc($adj['pic_name']) ?></div>
                            </td>
                            <td>
                                <div style="font-weight: 800; font-size: 14px; margin-bottom: 6px;"><?= esc($adj['item_name']) ?></div>
                                <span class="sku-badge <?= $adj['item_type'] == 'PRD' ? 'sku-prd' : 'sku-mat' ?>"><?= esc($adj['sku']) ?></span>
                            </td>
                            <td>
                                <?php if($adj['adjustment_type'] == 'PLUS'): ?>
                                    <span style="background: var(--success-soft); color: var(--success); padding: 4px 10px; border-radius: 6px; font-weight: 900; font-family: monospace; font-size: 14px;">+<?= floatval($adj['qty']) ?></span>
                                <?php else: ?>
                                    <span style="background: var(--danger-soft); color: var(--danger); padding: 4px 10px; border-radius: 6px; font-weight: 900; font-family: monospace; font-size: 14px;">-<?= floatval($adj['qty']) ?></span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: right; font-family: 'Space Mono', monospace; font-weight: 800; font-size: 14px; <?= $adj['adjustment_type'] == 'PLUS' ? 'color: var(--success);' : 'color: var(--danger);' ?>">
                                Rp <?= number_format($adj['financial_value'], 0, ',', '.') ?>
                            </td>
                            <td style="white-space: normal; line-height: 1.4; max-width: 250px; font-size: 12px; color: var(--text-muted); text-align: left;">
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
    <div class="modal-box" style="border-top: 8px solid var(--brand);">
        <div class="modal-header">
            <div class="modal-title" id="titleFG">
                <div style="background: var(--brand-soft); width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; color: var(--brand);"><i class="ph-fill ph-motorcycle"></i></div>
                Input Produk (PRD) Baru
            </div>
            <button class="btn-close" onclick="closeModal('modalFG')" type="button"><i class="ph-bold ph-x"></i></button>
        </div>
        <form id="formFG" action="<?= base_url('/warehouse/store_fg') ?>" method="post">
            <?= csrf_field() ?>
            
            <div class="modal-group-box" style="background: rgba(59, 130, 246, 0.02); border-color: rgba(59, 130, 246, 0.15);">
                <div class="modal-group-title" style="color: var(--brand);"><i class="ph-fill ph-tag"></i> 1. Identitas & Kategori</div>
                <div class="form-group">
                    <label>Nama Lengkap Produk / Komponen</label>
                    <input type="text" name="item_name" class="form-control focus-blue" placeholder="Cth: Knalpot WR155 Full" required autocomplete="off">
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label>Kategori / Tipe Produk (Penting untuk BoM)</label>
                    <select name="item_type" class="form-control focus-blue cat-selector" required>
                        <option value="Full System">Full System (Utuh)</option>
                        <option value="Silencer / Slip-on">Silencer / Slip-on</option>
                        <option value="Header / Leheran">Header / Leheran</option>
                        <option value="Aksesoris / Sparepart">Aksesoris / Sparepart Lainnya</option>
                    </select>
                </div>
            </div>

            <div class="modal-group-box">
                <div class="modal-group-title" style="color: var(--text-muted);"><i class="ph-fill ph-currency-circle-dollar"></i> 2. Pengaturan Nilai & Harga</div>
                <div class="form-group">
                    <label>HPP / Nilai Modal Pokok</label>
                    <div style="display: flex; align-items: center; background: var(--bg-main); border: 1px solid var(--border-color); border-radius: 12px; overflow: hidden; transition: 0.3s;" class="im-blue" id="wrap_fg_hpp">
                        <span style="padding: 14px 16px; background: rgba(0,0,0,0.03); font-size: 13px; font-weight: 900; color: var(--text-muted); border-right: 1px solid var(--border-color);">Rp</span>
                        <input type="text" name="hpp" id="fg_hpp" placeholder="0" required onkeyup="formatRupiah(this)" autocomplete="off" style="border: none; padding: 14px 16px; font-size: 15px; font-weight: 800; width: 100%; outline: none; background: transparent; color: var(--text-main); font-family: 'Space Mono', monospace;" onfocus="document.getElementById('wrap_fg_hpp').style.borderColor='var(--brand)'; document.getElementById('wrap_fg_hpp').style.boxShadow='0 0 0 4px var(--brand-soft)'" onblur="document.getElementById('wrap_fg_hpp').style.borderColor='var(--border-color)'; document.getElementById('wrap_fg_hpp').style.boxShadow='none'">
                    </div>
                </div>
                <div class="grid-2" style="margin-bottom: 0;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label>Harga Retail (Ecer)</label>
                        <div style="display: flex; align-items: center; background: var(--bg-main); border: 1px solid rgba(16, 185, 129, 0.4); border-radius: 12px; overflow: hidden; transition: 0.3s;" id="wrap_fg_retail">
                            <span style="padding: 14px 16px; font-size: 13px; font-weight: 900; border-right: 1px solid rgba(16, 185, 129, 0.2); color: #10b981; background: rgba(16, 185, 129, 0.05);">Rp</span>
                            <input type="text" name="retail_price" id="fg_retail" placeholder="0" required onkeyup="formatRupiah(this)" autocomplete="off" style="border: none; padding: 14px 16px; font-size: 15px; font-weight: 800; width: 100%; outline: none; background: transparent; color: #10b981; font-family: 'Space Mono', monospace;" onfocus="document.getElementById('wrap_fg_retail').style.borderColor='var(--brand)'; document.getElementById('wrap_fg_retail').style.boxShadow='0 0 0 4px var(--brand-soft)'" onblur="document.getElementById('wrap_fg_retail').style.borderColor='rgba(16, 185, 129, 0.4)'; document.getElementById('wrap_fg_retail').style.boxShadow='none'">
                        </div>
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label>Harga Grosir (B2B)</label>
                        <div style="display: flex; align-items: center; background: var(--bg-main); border: 1px solid rgba(59, 130, 246, 0.4); border-radius: 12px; overflow: hidden; transition: 0.3s;" id="wrap_fg_wholesale">
                            <span style="padding: 14px 16px; font-size: 13px; font-weight: 900; border-right: 1px solid rgba(59, 130, 246, 0.2); color: #3b82f6; background: rgba(59, 130, 246, 0.05);">Rp</span>
                            <input type="text" name="wholesale_price" id="fg_wholesale" placeholder="0" required onkeyup="formatRupiah(this)" autocomplete="off" style="border: none; padding: 14px 16px; font-size: 15px; font-weight: 800; width: 100%; outline: none; background: transparent; color: #3b82f6; font-family: 'Space Mono', monospace;" onfocus="document.getElementById('wrap_fg_wholesale').style.borderColor='var(--brand)'; document.getElementById('wrap_fg_wholesale').style.boxShadow='0 0 0 4px var(--brand-soft)'" onblur="document.getElementById('wrap_fg_wholesale').style.borderColor='rgba(59, 130, 246, 0.4)'; document.getElementById('wrap_fg_wholesale').style.boxShadow='none'">
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-group-box" style="margin-bottom: 0;">
                <div class="modal-group-title" style="color: var(--text-muted);"><i class="ph-fill ph-stack"></i> 3. Parameter Stok Fisik</div>
                <div class="grid-2" style="margin-bottom: 0;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label>Stok Fisik Saat Ini</label>
                        <input type="number" name="initial_stock" id="initial_stock_fg" class="form-control focus-blue" value="0" required min="0" style="font-family: 'Space Mono', monospace; font-size: 16px;">
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label>Batas Peringatan Stok (Min)</label>
                        <input type="number" name="min_stock" class="form-control focus-blue" value="5" required min="1" style="font-family: 'Space Mono', monospace; font-size: 16px; color: var(--danger);">
                    </div>
                </div>
            </div>

            <button type="submit" id="btnSubmitFG" class="btn-submit-modal btn-blue" style="margin-top: 25px;">
                <i class="ph-bold ph-floppy-disk" style="font-size: 20px;"></i> <span>Simpan Data Produk (PRD)</span>
            </button>
        </form>
    </div>
</div>

<div class="modal-overlay" id="modalRM">
    <div class="modal-box" style="border-top: 8px solid var(--warning);">
        <div class="modal-header">
            <div class="modal-title" id="titleRM">
                <div style="background: var(--warning-soft); width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; color: var(--warning);"><i class="ph-fill ph-nut"></i></div>
                Input Material & Overhead
            </div>
            <button class="btn-close" onclick="closeModal('modalRM')" type="button"><i class="ph-bold ph-x"></i></button>
        </div>

        <form id="formRM" action="<?= base_url('/warehouse/store_rm') ?>" method="post">
            <?= csrf_field() ?>
            
            <div class="modal-group-box" style="background: rgba(245, 158, 11, 0.03); border-color: rgba(245, 158, 11, 0.15);">
                <div class="modal-group-title" style="color: var(--warning);"><i class="ph-fill ph-tag"></i> 1. Identitas & Klasifikasi</div>

                <div class="form-group">
                    <label>Nama Material (Contoh: Pipa SS 304, Paku Rivet)</label>
                    <input type="text" name="material_name" class="form-control focus-orange" placeholder="Cth: Pipa Stainless 2 Inch SS304" required autocomplete="off">
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label>Kategori Material</label>
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
                        <label>Satuan Pembelian (PO ke Supplier)</label>
                        <select name="purchase_uom" id="rm_purchase_uom" class="form-control focus-orange select2-uom" required>
                            <option value="">-- Pilih Satuan --</option>
                            <?php foreach($uomMaster as $uom): ?>
                                <option value="<?= esc($uom['uom_code']) ?>"><?= esc($uom['uom_name']) ?> (<?= esc($uom['uom_code']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                        <small class="mini-note">Satuannya saat beli grosir.</small>
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label>Satuan Pemakaian Gudang (Resep/BOM)</label>
                        <select name="base_uom" id="rm_base_uom" class="form-control focus-orange select2-uom" required>
                            <option value="">-- Pilih Satuan --</option>
                            <?php foreach($uomMaster as $uom): ?>
                                <option value="<?= esc($uom['uom_code']) ?>"><?= esc($uom['uom_name']) ?> (<?= esc($uom['uom_code']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                        <small class="mini-note">Satuannya saat dipotong oleh tukang.</small>
                    </div>
                </div>

                <div id="conversion_box" style="display: none; margin-top: 15px; padding: 15px; background: rgba(59, 130, 246, 0.05); border: 1px dashed rgba(59, 130, 246, 0.3); border-radius: 12px;">
                    <label style="font-size: 11px; font-weight: 900; color: var(--brand); margin-bottom: 8px; display: block; text-transform: uppercase;">
                        <i class="ph-fill ph-arrows-left-right"></i> Konversi Otomatis (Wajib Diisi)
                    </label>
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <div style="flex: 1; text-align: center; font-size: 14px; font-weight: 900; background: #fff; padding: 10px; border: 1px solid var(--border-color); border-radius: 10px;">
                            1 <span id="lbl_purch_uom" style="color: var(--brand);">BATANG</span>
                        </div>
                        <div style="font-weight: 900; color: var(--text-muted);">SAMA DENGAN (=)</div>
                        <div style="flex: 1; display: flex; align-items: center;">
                            <input type="number" step="0.0001" min="0.0001" name="conversion_factor" id="rm_conv_factor" class="form-control focus-blue" style="border-radius: 10px 0 0 10px; border-right: none; font-family: 'Space Mono', monospace; text-align: center; font-size: 16px;" placeholder="Cth: 600">
                            <div id="lbl_base_uom" style="background: var(--bg-main); border: 1px solid var(--border-color); padding: 13.5px 15px; border-radius: 0 10px 10px 0; font-size: 13px; font-weight: 900; color: var(--brand);">CM</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-group-box" style="margin-bottom: 0;">
                <div class="modal-group-title" style="color: var(--text-muted);"><i class="ph-fill ph-currency-circle-dollar"></i> 3. Harga Pokok & Stok</div>
                <div class="form-group">
                    <label>Harga Beli Pokok per <span id="lbl_hpp_uom" style="color: var(--danger); background: rgba(239, 68, 68, 0.1); padding: 2px 6px; border-radius: 4px;">SATUAN GUDANG</span></label>
                    <div style="display: flex; align-items: center; background: var(--bg-main); border: 1px solid var(--border-color); border-radius: 12px; overflow: hidden; transition: 0.3s;" class="im-orange" id="wrap_rm_hpp">
                        <span style="padding: 14px 16px; background: rgba(0,0,0,0.03); font-size: 13px; font-weight: 900; color: var(--text-muted); border-right: 1px solid var(--border-color);">Rp</span>
                        <input type="text" name="hpp" id="rm_hpp" placeholder="0" required onkeyup="formatRupiah(this)" autocomplete="off" style="border: none; padding: 14px 16px; font-size: 15px; font-weight: 800; width: 100%; outline: none; background: transparent; color: var(--text-main); font-family: 'Space Mono', monospace;" onfocus="document.getElementById('wrap_rm_hpp').style.borderColor='var(--warning)'; document.getElementById('wrap_rm_hpp').style.boxShadow='0 0 0 4px var(--warning-soft)'" onblur="document.getElementById('wrap_rm_hpp').style.borderColor='var(--border-color)'; document.getElementById('wrap_rm_hpp').style.boxShadow='none'">
                    </div>
                    <small class="mini-note" id="note_hpp" style="color: var(--danger); font-weight: 800;"></small>
                </div>

                <div class="grid-2" style="margin-bottom: 0;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label>Stok Fisik Gudang Saat Ini</label>
                        <div style="display: flex; align-items: center;">
                            <input type="number" step="0.01" name="initial_stock" id="initial_stock_rm" class="form-control focus-orange" value="0" required min="0" style="font-family: 'Space Mono', monospace; font-size: 16px; border-radius: 12px 0 0 12px; border-right: none;">
                            <div id="lbl_stok_uom" style="background: var(--bg-main); border: 1px solid var(--border-color); padding: 13.5px 15px; border-radius: 0 12px 12px 0; font-size: 13px; font-weight: 900; color: var(--text-muted);">UNIT</div>
                        </div>
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label>Peringatan Stok Minimum</label>
                        <input type="number" step="0.01" name="min_stock" id="rm_min_stock" class="form-control focus-orange" value="10" required min="0" style="font-family: 'Space Mono', monospace; font-size: 16px; color: var(--danger);">
                    </div>
                </div>
            </div>

            <button type="submit" id="btnSubmitRM" class="btn-submit-modal btn-orange" style="margin-top: 25px;">
                <i class="ph-bold ph-floppy-disk" style="font-size: 20px;"></i> <span>Simpan Data Material (MAT)</span>
            </button>
        </form>
    </div>
</div>

<div class="modal-overlay" id="modalAdj">
    <div class="modal-box" style="border-top: 8px solid var(--danger);">
        <div class="modal-header">
            <div class="modal-title">
                <div style="background: var(--danger-soft); width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; color: var(--danger);"><i class="ph-fill ph-scales"></i></div>
                Penyesuaian Stok (Opname)
            </div>
            <button class="btn-close" onclick="closeModal('modalAdj')" type="button"><i class="ph-bold ph-x"></i></button>
        </div>

        <form id="formAdj" action="<?= base_url('/warehouse/store_adjustment') ?>" method="post">
            <?= csrf_field() ?>
            
            <div class="modal-group-box" style="background: rgba(239, 68, 68, 0.03); border-color: rgba(239, 68, 68, 0.15);">
                <div class="modal-group-title" style="color: var(--danger);"><i class="ph-fill ph-target"></i> 1. Target Barang & Jenis Opname</div>
                <div class="form-group">
                    <label>Pilih Barang yang Ingin Disesuaikan</label>
                    <select name="sku" id="adj_sku_select" class="form-control select2-search" required style="width: 100%;">
                        <option value=""></option>
                        <optgroup label="📦 Produk Jadi (Kategori PRD)">
                            <?php foreach($finishedGoods as $fg): ?>
                                <option value="<?= esc($fg['sku']) ?>">[<?= esc($fg['sku']) ?>] <?= esc($fg['item_name']) ?> (Sisa: <?= $fg['physical_stock'] ?>)</option>
                            <?php endforeach; ?>
                        </select>
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
                        <label>Jenis Arah Penyesuaian</label>
                        <select name="adjustment_type" class="form-control focus-red cat-selector" required>
                            <option value="MINUS">📉 Kurangi Stok (Hilang/Diambil)</option>
                            <option value="PLUS">📈 Tambah Stok (Kelebihan Fisik)</option>
                        </select>
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label>Kuantitas Perubahan</label>
                        <input type="number" step="0.01" name="qty" class="form-control focus-red" placeholder="Cth: 2" required min="0.01" style="font-family: 'Space Mono', monospace; font-size: 16px;">
                    </div>
                </div>
            </div>

            <div class="modal-group-box" style="margin-bottom: 0;">
                <div class="modal-group-title" style="color: var(--text-muted);"><i class="ph-fill ph-file-text"></i> 2. Keterangan Laporan</div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label>Alasan Penyesuaian (Wajib Diisi)</label>
                    <textarea name="reason" id="adjReasonInput" class="form-control focus-red" rows="2" placeholder="Ketik alasan atau klik tombol cepat di bawah..." required></textarea>
                    
                    <div class="reason-chips" style="margin-top: 10px;">
                        <span class="chip" onclick="setAdjReason('Barang diambil tim produksi ke bengkel')">Diambil Produksi</span>
                        <span class="chip" onclick="setAdjReason('Barang rusak / cacat produksi (Scrap)')">Cacat / Rusak</span>
                        <span class="chip" onclick="setAdjReason('Penyesuaian selisih hitung fisik gudang')">Selisih Opname</span>
                    </div>
                </div>
            </div>
            
            <button type="submit" id="btnSubmitAdj" class="btn-submit-modal btn-red" style="margin-top: 25px;">
                <i class="ph-bold ph-paper-plane-tilt" style="font-size: 20px;"></i> <span>Eksekusi & Jurnal Otomatis</span>
            </button>
        </form>
    </div>
</div>

<script>
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

        // =========================================================
        // JAVASCRIPT CERDAS UNTUK FORM MATERIAL
        // =========================================================
        $('#rm_purchase_uom, #rm_base_uom').on('change', function() {
            let purchUom = $('#rm_purchase_uom').val() || 'SATUAN_BELI';
            let baseUom = $('#rm_base_uom').val() || 'SATUAN_GUDANG';
            let convBox = $('#conversion_box');
            let convInput = $('#rm_conv_factor');
            let noteHpp = $('#note_hpp');
            
            // Update Label-label statis di form
            $('#lbl_purch_uom').text(purchUom);
            $('#lbl_base_uom').text(baseUom);
            $('#lbl_hpp_uom').text(baseUom);
            $('#lbl_stok_uom').text(baseUom);

            // Jika satuan belinya BEDA dengan satuan gudang
            if(purchUom !== baseUom && purchUom !== 'SATUAN_BELI' && baseUom !== 'SATUAN_GUDANG') {
                convBox.slideDown(300);
                if(convInput.val() == 1 || convInput.val() == "") {
                    convInput.val(''); // Kosongkan agar user sadar harus diisi
                }
                noteHpp.html(`* PERHATIAN: Karena ada konversi, masukkan harga beli untuk 1 ${baseUom} (bukan harga 1 ${purchUom}).<br>Atau Anda bisa mengosongkan harga ini jika bingung.`);
            } else {
                // Jika satuan sama, sembunyikan kotak konversi dan set otomatis ke 1
                convBox.slideUp(300);
                convInput.val(1);
                noteHpp.html(`* Harga untuk pembelian 1 ${baseUom}.`);
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
        if (urlParams.get('tab') === 'rm') {
            switchTab('rm');
        } else if (urlParams.get('tab') === 'adj') {
            switchTab('adj');
        } else {
            switchTab('fg');
        }
    });

    function openModal(modalId) { 
        document.getElementById(modalId).classList.add('active'); 
    }

    function closeModal(modalId) { 
        document.getElementById(modalId).classList.remove('active'); 
    }

    function openCreateModalFG() {
        document.getElementById('formFG').reset();
        document.getElementById('titleFG').innerHTML = '<div style="background: rgba(59, 130, 246, 0.1); width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; color: #3b82f6;"><i class="ph-fill ph-motorcycle"></i></div> Input Produk (PRD) Baru';
        document.getElementById('formFG').action = "<?= base_url('/warehouse/store_fg') ?>";

        let stockInput = document.getElementById('initial_stock_fg');
        stockInput.readOnly = false;
        stockInput.style.backgroundColor = "";
        stockInput.title = "";
        openModal('modalFG');
    }

    function openCreateModalRM() {
        document.getElementById('formRM').reset();
        document.getElementById('titleRM').innerHTML = '<div style="background: rgba(245, 158, 11, 0.1); width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; color: #f59e0b;"><i class="ph-fill ph-nut"></i></div> Input Material & Overhead';
        document.getElementById('formRM').action = "<?= base_url('/warehouse/store_rm') ?>";

        let stockInput = document.getElementById('initial_stock_rm');
        stockInput.readOnly = false;
        stockInput.style.backgroundColor = "";
        stockInput.title = "";

        document.querySelector('#modalRM select[name="material_category"]').value = 'General';
        document.querySelector('#modalRM input[name="conversion_factor"]').value = 1;

        // Reset nilai select2 dan trigger kalkulasi UI
        $('#modalRM select[name="base_uom"]').val('').trigger('change');
        $('#modalRM select[name="purchase_uom"]').val('').trigger('change');
        
        let hpp = document.getElementById('rm_hpp');
        hpp.value = '';

        openModal('modalRM');
    }

    function openEditModalFG(id) {
        document.getElementById('formFG').reset();
        document.getElementById('titleFG').innerHTML = '<div style="background: rgba(59, 130, 246, 0.1); width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; color: #3b82f6;"><i class="ph-fill ph-pencil-simple"></i></div> Edit Data Produk (PRD)';
        document.getElementById('formFG').action = "<?= base_url('/warehouse/update_fg/') ?>" + id;
        
        let stockInput = document.getElementById('initial_stock_fg');
        stockInput.readOnly = true;
        stockInput.style.backgroundColor = "rgba(0,0,0,0.05)";
        stockInput.title = "Gunakan fitur Stock Opname di Tab 3 untuk mengubah stok fisik.";

        if(typeof window.showGlobalToast === 'function') window.showGlobalToast("Mengambil data...");

        fetch("<?= base_url('/warehouse/get_fg/') ?>" + id, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(res => res.json())
        .then(data => {
            document.querySelector('#modalFG input[name="item_name"]').value = data.item_name;
            document.querySelector('#modalFG select[name="item_type"]').value = data.item_type;
            document.querySelector('#modalFG input[name="min_stock"]').value = data.min_stock;
            stockInput.value = data.physical_stock; 
            
            let hpp = document.getElementById('fg_hpp');
            let retail = document.getElementById('fg_retail');
            let wholesale = document.getElementById('fg_wholesale');
            
            hpp.value = Math.round(parseFloat(data.hpp || 0)); 
            retail.value = Math.round(parseFloat(data.retail_price || 0)); 
            wholesale.value = Math.round(parseFloat(data.wholesale_price || 0));
            
            formatRupiah(hpp); 
            formatRupiah(retail); 
            formatRupiah(wholesale);
            
            openModal('modalFG');
        });
    }

    function openEditModalRM(id) {
        document.getElementById('formRM').reset();
        document.getElementById('titleRM').innerHTML = '<div style="background: rgba(245, 158, 11, 0.1); width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; color: #f59e0b;"><i class="ph-fill ph-pencil-simple"></i></div> Edit Data Material';
        document.getElementById('formRM').action = "<?= base_url('/warehouse/update_rm/') ?>" + id;
        
        let stockInput = document.getElementById('initial_stock_rm');
        stockInput.readOnly = true;
        stockInput.style.backgroundColor = "rgba(0,0,0,0.05)";
        stockInput.title = "Gunakan fitur Stock Opname di Tab 3 untuk mengubah stok fisik.";

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

    function handleAjaxForm(formId, btnId, redirectTab) {
        document.getElementById(formId).addEventListener('submit', function(e) {
            e.preventDefault(); 
            
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
                    setTimeout(() => {
                        window.location.replace("<?= base_url('/warehouse/local-inventory') ?>" + redirectTab);
                    }, 1200);
                } else {
                    if(typeof window.showGlobalToast === 'function') window.showGlobalToast(data.message, true);
                    btn.disabled = false;
                    btnText.innerText = originalText;
                    btnIcon.className = originalIcon;
                }
            })
            .catch(err => {
                if(typeof window.showGlobalToast === 'function') window.showGlobalToast("Koneksi Server Gagal", true);
                btn.disabled = false;
                btnText.innerText = originalText;
                btnIcon.className = originalIcon;
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
            text: "Aksi ini tidak bisa dikembalikan.",
            icon: 'warning', 
            showCancelButton: true, 
            confirmButtonColor: '#ef4444', 
            cancelButtonColor: isDark ? '#3f3f46' : '#cbd5e1', 
            confirmButtonText: 'Ya, Hapus',
            background: isDark ? '#18181b' : '#ffffff', 
            color: isDark ? '#f4f4f5' : '#09090b',
        }).then((result) => {
            if (result.isConfirmed) {
                if(url.includes('delete_rm')) {
                    window.location.href = url + '?tab=rm'; 
                } else {
                    window.location.href = url;
                }
            }
        })
    }

    function setAdjReason(text) {
        document.getElementById('adjReasonInput').value = text;
    }
</script>

<?= $this->endSection() ?>