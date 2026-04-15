<?= $this->extend('layout/template') ?>
<?= $this->section('content') ?>

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    /* VARIABLES & ANIMATIONS */
    :root {
        --primary: #8b5cf6; --primary-light: #ddd6fe;
        --secondary: #f59e0b; --success: #10b981; --danger: #ef4444; --info: #3b82f6; --pink: #ec4899;
        --glass-bg: rgba(255, 255, 255, 0.95);
        --shadow-soft: 0 10px 40px -10px rgba(0,0,0,0.05);
        --bg-input: #f8fafc; --bg-surface: #ffffff;
        --border-subtle: #e2e8f0; --text-main: #0f172a; --text-muted: #64748b;
    }
    html.dark {
        --bg-input: #0f172a; --bg-surface: #1e293b;
        --border-subtle: #334155; --text-main: #f8fafc; --text-muted: #94a3b8;
        --glass-bg: rgba(30, 41, 59, 0.95);
    }
    
    @keyframes slideIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
    @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }

    .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; width: 100%; flex-wrap: wrap; gap: 15px; } 
    .page-title { display: flex; align-items: center; gap: 20px; animation: slideIn 0.5s ease-out forwards;}
    .title-icon { width: 54px; height: 54px; border-radius: 16px; background: linear-gradient(135deg, var(--primary), #6d28d9); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 28px; box-shadow: 0 10px 25px -5px rgba(139, 92, 246, 0.5);}
    .page-title h1 { font-size: 30px; font-weight: 900; color: var(--text-main); margin: 0; letter-spacing: -1px;}
    .page-title p { font-size: 13px; color: var(--text-muted); font-weight: 600; margin: 4px 0 0 0; line-height: 1.5;}
    
    .btn-back { background: var(--glass-bg); color: var(--text-muted); border: 1px solid var(--border-subtle); padding: 12px 24px; border-radius: 14px; font-size: 13px; font-weight: 800; display: inline-flex; align-items: center; gap: 8px; text-decoration: none; transition: all 0.3s; box-shadow: var(--shadow-soft); margin: 0; cursor: pointer;}
    .btn-back:hover { color: var(--primary); border-color: var(--primary); transform: translateX(-5px); box-shadow: 0 8px 25px rgba(139, 92, 246, 0.15);}
    
    .btn-print-master { background: linear-gradient(135deg, #10b981, #059669); color: #fff; border: none; padding: 12px 24px; border-radius: 14px; font-size: 13px; font-weight: 800; display: inline-flex; align-items: center; gap: 8px; text-decoration: none; transition: all 0.3s; box-shadow: 0 10px 25px -5px rgba(16, 185, 129, 0.4);}
    .btn-print-master:hover { transform: translateY(-3px); box-shadow: 0 15px 30px -5px rgba(16, 185, 129, 0.5);}

    .tab-nav { display: inline-flex; background: rgba(0,0,0,0.03); padding: 6px; border-radius: 16px; border: 1px solid var(--border-subtle); margin-bottom: 25px; box-shadow: inset 0 2px 4px rgba(0,0,0,0.02); flex-wrap: wrap;}
    .tab-btn { padding: 12px 24px; font-size: 13px; font-weight: 800; border-radius: 12px; border: none; background: transparent; color: var(--text-muted); cursor: pointer; transition: 0.3s; display: flex; align-items: center; gap: 8px;}
    .tab-btn.active { background: var(--bg-surface); color: var(--primary); box-shadow: 0 4px 15px rgba(0,0,0,0.05); border: 1px solid var(--border-subtle); }
    .tab-content { display: none; animation: fadeIn 0.4s ease-out; }
    .tab-content.active { display: block; }

    .builder-card { background: var(--glass-bg); backdrop-filter: blur(10px); border: 1px solid var(--border-subtle); border-radius: 24px; padding: 40px; box-shadow: var(--shadow-soft); width: 100%; box-sizing: border-box; transition: all 0.4s; border-top: 8px solid var(--primary);}
    @media (max-width: 768px) { .builder-card { padding: 25px; border-radius: 20px; } }
    .target-box { background: var(--bg-surface); border: 1px solid var(--border-subtle); padding: 25px; border-radius: 20px; margin-bottom: 30px; position: relative; overflow: hidden;}
    .target-box::before { content: ''; position: absolute; top: 0; left: 0; width: 6px; height: 100%; background: var(--primary); border-radius: 6px 0 0 6px;}

    .form-group { margin-bottom: 20px; }
    .form-group label { display: block; font-size: 11px; font-weight: 900; color: var(--text-muted); margin-bottom: 8px; text-transform: uppercase; letter-spacing: 1px;}
    .form-control { width: 100%; background: var(--bg-input); border: 2px solid var(--border-subtle); padding: 14px 18px; border-radius: 12px; font-size: 14px; font-weight: 700; outline: none; color: var(--text-main); transition: all 0.3s; box-sizing: border-box;}
    .form-control:focus { border-color: var(--primary); background: var(--bg-surface); box-shadow: 0 0 0 4px rgba(139, 92, 246, 0.15);}
    
    .select2-container--default .select2-selection--single { background: var(--bg-input); border: 2px solid var(--border-subtle); height: 50px; border-radius: 12px; display: flex; align-items: center; padding: 0 12px; transition: all 0.3s;}
    .select2-container--default.select2-container--focus .select2-selection--single { border-color: var(--primary); background: var(--bg-surface); box-shadow: 0 0 0 4px rgba(139, 92, 246, 0.15);}
    .select2-container--default .select2-selection--single .select2-selection__rendered { font-weight: 800; color: var(--text-main); font-size: 14px;}
    .select2-container--default .select2-selection--single .select2-selection__arrow { height: 48px; right: 15px;}
    .select2-dropdown { border-radius: 16px; border: 1px solid var(--border-subtle); box-shadow: 0 20px 50px rgba(0,0,0,0.15); padding: 12px; background: var(--bg-surface);}
    .select2-search__field { border-radius: 10px !important; padding: 10px 14px !important; border: 2px solid var(--border-subtle) !important; outline: none; font-family: inherit; font-weight: 700; background: var(--bg-input); color: var(--text-main);}
    .select2-results__option { border-radius: 10px; margin-bottom: 4px; font-weight: 700; font-size: 13px; padding: 10px 15px; color: var(--text-main);}
    .select2-container--default .select2-results__option--highlighted[aria-selected] { background: var(--primary) !important; color: white !important; }
    .stok-badge { float: right; font-size: 10px; background: rgba(0,0,0,0.08); padding: 2px 8px; border-radius: 6px; font-family: 'Space Mono', monospace; font-weight: 900;}

    .section-title { font-size: 16px; font-weight: 900; color: var(--text-main); margin-top: 35px; margin-bottom: 20px; display: flex; align-items: center; gap: 12px; padding-bottom: 12px; border-bottom: 2px dashed var(--border-subtle);}
    .section-title i { background: rgba(139, 92, 246, 0.1); color: var(--primary); padding: 8px; border-radius: 10px; font-size: 20px;}

    .dynamic-row { display: grid; gap: 15px; align-items: center; background: var(--bg-surface); padding: 15px 20px; border-radius: 16px; border: 1px solid var(--border-subtle); margin-bottom: 12px; transition: all 0.3s; box-shadow: inset 0 2px 4px rgba(0,0,0,0.02);}
    .dynamic-row:hover { border-color: var(--primary-light); box-shadow: 0 10px 25px -5px rgba(139, 92, 246, 0.15); transform: translateY(-2px);}
    
    .op-row { grid-template-columns: 35px 2fr 1.5fr 1fr 1.5fr auto; }
    .rm-row { grid-template-columns: 2.5fr 1.2fr 1.2fr 1.5fr auto; } 
    .oh-row { grid-template-columns: 3.5fr 1.5fr auto; } 
    @media (max-width: 900px) { .op-row, .rm-row, .oh-row { grid-template-columns: 1fr; } .step-number{ display:none; } }

    .step-number { font-size: 14px; font-weight: 900; color: var(--text-muted); text-align: center; background: var(--bg-input); width: 35px; height: 35px; display: flex; align-items: center; justify-content: center; border-radius: 10px;}
    .btn-remove { background: rgba(239, 68, 68, 0.05); color: var(--danger); border: 2px solid transparent; width: 45px; height: 45px; border-radius: 12px; font-size: 20px; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.3s;}
    .btn-remove:hover { background: var(--danger); color: #fff; transform: scale(1.1) rotate(5deg); }
    .btn-add { background: transparent; color: var(--primary); border: 2px dashed var(--primary-light); width: 100%; padding: 15px; border-radius: 16px; font-weight: 900; font-size: 14px; cursor: pointer; margin-bottom: 10px; transition: all 0.3s; display: flex; align-items: center; justify-content: center; gap: 8px; background: rgba(139, 92, 246, 0.02);}
    
    .btn-save { flex: 1; background: linear-gradient(135deg, var(--primary), #6d28d9); color: #fff; border: none; padding: 20px; border-radius: 16px; font-size: 16px; font-weight: 900; cursor: pointer; box-shadow: 0 15px 35px -5px rgba(139, 92, 246, 0.5); transition: all 0.3s; display: flex; align-items: center; justify-content: center; gap: 10px; text-transform: uppercase; letter-spacing: 1px;}
    
    .input-wrapper { display: flex; align-items: stretch; background: var(--bg-input); border: 2px solid var(--border-subtle); border-radius: 12px; overflow: hidden; transition: all 0.3s ease; }
    .input-wrapper:focus-within { border-color: var(--secondary); background: var(--bg-surface); box-shadow: 0 0 0 4px rgba(245, 158, 11, 0.15);}
    .input-wrapper input { flex: 1; background: transparent; border: none; color: var(--text-main); padding: 12px 14px; font-size: 14px; font-weight: 900; font-family: 'Space Mono', monospace; outline: none; width: 100%; min-width: 50px;}
    .input-wrapper input::placeholder { color: var(--text-muted); opacity: 0.7; font-weight: 600; font-family: 'Plus Jakarta Sans', sans-serif;}
    .prefix { background: rgba(245, 158, 11, 0.1); color: var(--secondary); font-size: 13px; font-weight: 900; padding: 0 14px; display: flex; align-items: center; border-right: 2px solid var(--border-subtle); transition: 0.3s; }

    .desktop-labels { display: grid; gap: 15px; padding: 0 20px 10px 20px; font-size: 11px; font-weight: 900; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; }
    .rm-labels { grid-template-columns: 2.5fr 1.2fr 1.2fr 1.5fr auto; }
    .oh-labels { grid-template-columns: 3.5fr 1.5fr auto; }
    
    table { width: 100%; border-collapse: collapse; white-space: nowrap; }
    th { text-align: left; padding: 15px 20px; font-size: 11px; font-weight: 900; text-transform: uppercase; color: var(--text-muted); background: var(--bg-input); border-bottom: 2px solid var(--border-subtle);}
    td { padding: 16px 20px; border-bottom: 1px dashed var(--border-subtle); color: var(--text-main); font-size: 13px; font-weight: 700; vertical-align: middle;}
    .sku-badge { font-family: 'Space Mono', monospace; font-size: 11px; color: var(--primary); background: rgba(139, 92, 246, 0.1); padding: 4px 8px; border-radius: 6px; font-weight: 900; border: 1px dashed rgba(139, 92, 246, 0.4);}
    .btn-action-sm { padding: 8px 12px; border-radius: 8px; font-size: 12px; font-weight: 900; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; text-decoration: none; border: none; transition: 0.3s;}
    .btn-edit { background: rgba(59, 130, 246, 0.1); color: var(--info); }
    .btn-del { background: rgba(239, 68, 68, 0.1); color: var(--danger); }
    .btn-copy { background: rgba(245, 158, 11, 0.1); color: var(--secondary); }

    .section-card { background: var(--bg-surface); border: 2px dashed rgba(139, 92, 246, 0.25); border-radius: 22px; padding: 22px; margin-bottom: 28px; position: relative; overflow: hidden; }
    .section-card::before { content: ''; position: absolute; top: 0; left: 0; width: 6px; height: 100%; background: linear-gradient(180deg, var(--primary), #6d28d9); border-radius: 6px 0 0 6px; }
    .section-head { display: flex; justify-content: space-between; align-items: center; gap: 15px; margin-bottom: 18px; flex-wrap: wrap; }
    .section-head h4 { margin: 0; font-size: 16px; font-weight: 900; color: var(--primary); display: flex; align-items: center; gap: 10px; }
    .section-mini-badge { background: rgba(139, 92, 246, 0.08); color: var(--primary); border: 1px dashed rgba(139, 92, 246, 0.25); padding: 6px 12px; border-radius: 999px; font-size: 11px; font-weight: 900; text-transform: uppercase; letter-spacing: .6px; }
    .btn-add-section { background: linear-gradient(135deg, var(--primary), #6d28d9); color: #fff; border: none; width: 100%; padding: 18px; border-radius: 18px; font-weight: 900; font-size: 15px; cursor: pointer; box-shadow: 0 12px 30px -8px rgba(139, 92, 246, 0.45); transition: .3s; display: flex; align-items: center; justify-content: center; gap: 10px; margin-bottom: 28px; }
    .btn-add-section:hover { transform: translateY(-2px); }

    /* CSS UNTUK CHECKBOX MASSAL */
    .custom-checkbox { width: 18px; height: 18px; cursor: pointer; accent-color: var(--secondary); }
    .highlight-row { background-color: rgba(245, 158, 11, 0.05); border-left: 4px solid var(--secondary); }
</style>

<?php
// AUTO-FETCH DATA UNTUK FORM BOM BUILDER
$db = \Config\Database::connect();
$finishedGoods = $db->table('warehouse_inventory')->like('sku', 'PRD-', 'after')->orderBy('item_type', 'ASC')->orderBy('item_name', 'ASC')->get()->getResultArray();
$rawMaterials  = $db->table('raw_materials')->orderBy('material_name', 'ASC')->get()->getResultArray();
$uomMasters    = $db->table('uom_master')->orderBy('uom_name', 'ASC')->get()->getResultArray();
$existingBoms  = $db->table('bom_headers')->select('bom_headers.*, warehouse_inventory.item_name, warehouse_inventory.item_type')->join('warehouse_inventory', 'warehouse_inventory.sku = bom_headers.fg_sku', 'left')->orderBy('warehouse_inventory.item_type', 'ASC')->orderBy('bom_headers.id', 'DESC')->get()->getResultArray();
$rmList = $rawMaterials;
?>

<div class="page-wrapper">
    <div class="page-header">
        <div class="page-title">
            <div class="title-icon"><i class="ph-fill ph-flask"></i></div>
            <div>
                <h1>BoM & Routing Studio</h1>
                <p>Arsitektur kebutuhan bahan mentah, biaya overhead, dan susunan alur kerja pabrikasi.</p>
            </div>
        </div>
        <div style="display: flex; gap: 10px;">
            <a href="<?= base_url('/production/print_bom_batch') ?>" target="_blank" class="btn-print-master">
                <i class="ph-bold ph-printer"></i> Cetak Semua Master (Katalog)
            </a>
            <a href="<?= base_url('/production') ?>" class="btn-back">
                <i class="ph-bold ph-arrow-left"></i> Kembali ke Pabrik
            </a>
        </div>
    </div>

    <div class="tab-nav">
        <button class="tab-btn active" onclick="switchTab('buat')" id="btnTabBuat"><i class="ph-bold ph-plus-circle"></i> Rancang Resep Baru</button>
        <button class="tab-btn" onclick="switchTab('daftar')"><i class="ph-bold ph-database"></i> Database Master BoM</button>
        <button class="tab-btn" onclick="switchTab('copy_massal')"><i class="ph-bold ph-copy"></i> Copy Massal Spesifik</button>
    </div>

    <div id="tab-buat" class="tab-content active">
        <div class="builder-card" id="formCard">
            <h3 id="formTitle" style="margin: 0 0 20px 0; font-weight: 900; color: var(--primary); display: none; align-items: center; gap: 10px; padding: 15px; background: rgba(59, 130, 246, 0.1); border-radius: 12px;">
                <i class="ph-bold ph-pencil-simple"></i> Mode Edit Resep
            </h3>

            <form action="<?= base_url('/production/store_bom') ?>" method="post" id="formBOM">
                <?= csrf_field() ?>

                <div class="target-box">
                    <div class="form-group" style="margin-bottom: 15px;">
                        <label style="color: var(--primary);"><i class="ph-fill ph-target"></i> Target Produksi (Barang Jadi)</label>
                        <select name="fg_sku" class="form-control select2-target" required>
                            <option value="">-- Cari & Pilih Produk Jualan --</option>
                            <?php foreach($finishedGoods as $fg): ?>
                                <option value="<?= esc($fg['sku']) ?>">
                                    [<?= esc($fg['sku']) ?>] <?= esc($fg['item_name']) ?> (Sisa Stok: <?= floatval($fg['physical_stock']) ?> Pcs)
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

                <div style="background: rgba(139, 92, 246, 0.05); border: 1px dashed rgba(139, 92, 246, 0.25); padding: 14px 20px; border-radius: 14px; margin-bottom: 24px; font-size: 12px; color: var(--text-muted); font-weight: 600;">
                    <i class="ph-fill ph-info" style="color: var(--primary);"></i>
                    Gunakan <b>Bagian</b> untuk memisahkan komponen produksi seperti <b>Letter S</b>, <b>Monel</b>, <b>Tabung Silincer</b>.
                    <br><br>
                    <b>Tips:</b> Kalau produk sederhana, cukup buat <b>1 Bagian</b> saja.
                </div>

                <div id="sections-container"></div>

                <button type="button" class="btn-add-section" onclick="addSection()">
                    <i class="ph-bold ph-plus-circle"></i> Tambah Bagian / Section Baru
                </button>

                <div style="display: flex; gap: 15px; margin-top: 10px;">
                    <button type="submit" id="btnSubmitBOM" class="btn-save" style="margin: 0;">
                        <i class="ph-bold ph-check-square"></i> <span>Simpan Blueprint Produksi</span>
                    </button>
                    
                    <button type="button" id="btnCancelEdit" class="btn-back" style="display: none; justify-content: center; width: auto; min-width: 180px; border-color: var(--danger); color: var(--danger);" onclick="resetForm()">
                        <i class="ph-bold ph-x"></i> Batalkan Edit
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div id="tab-daftar" class="tab-content">
        <div class="builder-card" style="border-top-color: var(--info);">
            
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 15px;">
                <h3 style="margin: 0; font-weight: 900; display: flex; align-items: center; gap: 10px;">
                    <i class="ph-fill ph-database" style="color: var(--info); font-size: 24px;"></i> Daftar Resep Master
                </h3>
                
                <div style="display: flex; gap: 8px; flex: 1; justify-content: flex-end; flex-wrap: wrap;">
                    <div class="search-container" style="margin: 0; max-width: 350px; position: relative;">
                        <i class="ph-bold ph-magnifying-glass search-icon" style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: var(--text-muted); font-size: 18px;"></i>
                        <input type="text" id="searchBom" class="form-control" placeholder="Cari nama atau resep..." onkeyup="filterTable('searchBom', 'bomTable')" style="padding-left: 42px;">
                    </div>
                </div>
            </div>
            
            <div class="table-responsive" style="border-radius: 16px; border: 1px solid var(--border-subtle); background: var(--bg-surface); overflow-x: auto;">
                <table id="bomTable">
                    <thead>
                        <tr>
                            <th style="width: 5%;">No</th>
                            <th style="width: 15%;">SKU Produk</th>
                            <th style="width: 35%;">Nama Produk Jualan</th>
                            <th style="width: 25%;">Judul Resep / SOP</th>
                            <th style="width: 20%; text-align: center;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($existingBoms)): ?>
                            <tr id="emptyRow"><td colspan="5" style="text-align: center; padding: 40px; color: var(--text-muted); font-style: italic;">Belum ada resep yang dibuat.</td></tr>
                        <?php else: ?>
                            <?php $n=1; foreach($existingBoms as $b): ?>
                            <tr class="data-row">
                                <td style="color: var(--text-muted); font-weight: 800;"><?= $n++ ?>.</td>
                                <td class="searchable"><span class="sku-badge"><?= esc($b['fg_sku']) ?></span></td>
                                <td class="searchable" style="font-weight: 800;"><?= esc($b['item_name'] ?? 'Produk Tidak Diketahui') ?></td>
                                <td class="searchable" style="color: var(--text-muted);"><?= esc($b['recipe_name']) ?></td>
                                <td style="text-align: center;">
                                    <div style="display: flex; gap: 6px; justify-content: center;">
                                        <a href="<?= base_url('production/duplicate_bom/'.$b['id']) ?>" onclick="return confirm('Menduplikasi resep ini akan membuat salinan yang sama persis. Anda yakin?');" class="btn-action-sm btn-copy" title="Duplikat Resep">
                                            <i class="ph-bold ph-copy"></i> Duplikat
                                        </a>
                                        <a href="<?= base_url('production/print_bom/'.$b['id']) ?>" target="_blank" class="btn-action-sm btn-print" title="Cetak Satuan (A4)">
                                            <i class="ph-bold ph-printer"></i>
                                        </a>
                                        <button type="button" class="btn-action-sm btn-edit" onclick="editBom(<?= $b['id'] ?>)" title="Edit Resep">
                                            <i class="ph-bold ph-pencil-simple"></i> Edit
                                        </button>
                                        <a href="<?= base_url('production/delete_bom/'.$b['id']) ?>" class="btn-action-sm btn-del" onclick="return confirm('HAPUS PERMANEN?\n\nResep ini dan seluruh tahapan operasinya akan dihapus dari sistem. Lanjutkan?');" title="Hapus Resep">
                                            <i class="ph-bold ph-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <tr id="noResultRow" style="display: none;"><td colspan="5" style="text-align: center; padding: 40px; color: var(--danger); font-style: italic; font-weight: bold;"><i class="ph-bold ph-warning-circle"></i> Produk tidak ditemukan.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div id="tab-copy_massal" class="tab-content">
        <div class="builder-card" style="border-top-color: var(--secondary);">
            <h3 style="margin: 0 0 20px 0; font-weight: 900; color: var(--secondary); display: flex; align-items: center; gap: 10px;">
                <i class="ph-fill ph-copy" style="font-size: 24px;"></i> Copy-Paste Massal Formula Resep
            </h3>
            
            <div style="background: rgba(245, 158, 11, 0.05); border: 1px dashed rgba(245, 158, 11, 0.3); padding: 14px 20px; border-radius: 14px; margin-bottom: 24px; font-size: 13px; color: var(--text-muted); font-weight: 600; line-height: 1.5;">
                <i class="ph-fill ph-info" style="color: var(--secondary);"></i>
                Pilih elemen apa saja yang mau Anda Copy-Paste dari Resep Master ke Resep Target. <b style="color:var(--danger);">Peringatan:</b> Data pada resep target akan <b>dihapus dan ditimpa (Replace)</b> secara permanen mengikuti master.
            </div>

            <form action="<?= base_url('/production/mass_copy_bom') ?>" method="post" id="formMassCopy">
                <?= csrf_field() ?>
                
                <div class="target-box" style="border-color: var(--secondary); margin-bottom: 20px;">
                    <label style="display: block; font-size: 11px; font-weight: 900; color: var(--text-muted); margin-bottom: 8px; text-transform: uppercase;">1. Pilih Resep Sumber (Master Copy)</label>
                    <select name="source_bom_id" id="sourceBomSelect" class="form-control" required style="width: 100%;">
                        <option value="">-- Cari Resep Master yang datanya mau di-copy --</option>
                        <?php foreach($existingBoms as $b): ?>
                            <option value="<?= $b['id'] ?>"><?= esc($b['recipe_name']) ?> [<?= esc($b['fg_sku']) ?>]</option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="target-box" style="border-color: var(--info); margin-bottom: 20px; background: rgba(59, 130, 246, 0.02);">
                    <label style="display: block; font-size: 11px; font-weight: 900; color: var(--text-muted); margin-bottom: 12px; text-transform: uppercase;">2. Elemen Apa Saja Yang Ingin Ditimpa/Copy?</label>
                    
                    <div style="display: flex; gap: 25px; flex-wrap: wrap;">
                        <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; text-transform: none; font-size: 14px; font-weight: 800; color: var(--text-main);">
                            <input type="checkbox" name="copy_items" value="yes" class="custom-checkbox">
                            📦 Bahan Baku & Overhead (bom_items)
                        </label>
                        <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; text-transform: none; font-size: 14px; font-weight: 800; color: var(--text-main);">
                            <input type="checkbox" name="copy_ops" value="yes" class="custom-checkbox" checked>
                            👷 Tahapan Pekerja & Upah (bom_operations)
                        </label>
                    </div>
                </div>

                <div class="section-title" style="margin-top: 0; color: var(--secondary);">
                    <i class="ph-fill ph-check-square-offset" style="color: var(--secondary); background: rgba(245, 158, 11, 0.1);"></i> 3. Centang Resep Target (Yang Akan Di-timpa)
                </div>

                <div style="margin-bottom: 15px; position: relative; max-width: 400px;">
                    <i class="ph-bold ph-magnifying-glass search-icon" style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: var(--text-muted); font-size: 18px;"></i>
                    <input type="text" id="searchCopy" class="form-control" placeholder="Cari resep untuk dicentang..." onkeyup="filterTable('searchCopy', 'copyTable')" style="padding-left: 42px;">
                </div>

                <div class="table-responsive" style="max-height: 400px; overflow-y: auto; border-radius: 12px; border: 1px solid var(--border-subtle);">
                    <table id="copyTable" style="width: 100%; border-collapse: collapse;">
                        <thead style="position: sticky; top: 0; z-index: 10;">
                            <tr>
                                <th style="width: 5%; text-align: center; padding: 12px;"><input type="checkbox" id="selectAllCheckbox" class="custom-checkbox"></th>
                                <th style="width: 25%; padding: 12px;">SKU Produk</th>
                                <th style="width: 70%; padding: 12px;">Judul Resep Target</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($existingBoms as $b): ?>
                            <tr class="data-row checkbox-row">
                                <td style="text-align: center;"><input type="checkbox" name="target_bom_ids[]" value="<?= $b['id'] ?>" class="target-checkbox custom-checkbox"></td>
                                <td class="searchable" style="font-weight: 800; color: var(--text-muted); font-family: monospace;"><?= esc($b['fg_sku']) ?></td>
                                <td class="searchable" style="font-weight: 800; color: var(--text-main);"><?= esc($b['recipe_name']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <button type="submit" class="btn-save" style="margin-top: 20px; background: linear-gradient(135deg, var(--secondary), #d97706); box-shadow: 0 15px 35px -5px rgba(245, 158, 11, 0.4);" onclick="return confirm('Peringatan Keras!\nData pada Resep Target yang Anda centang akan DIHAPUS PERMANEN dan diganti dengan data dari Resep Master.\n\nApakah Anda Yakin?');">
                    <i class="ph-bold ph-files"></i> Timpa (Paste) Sekarang
                </button>
            </form>
        </div>
    </div>
</div>

<script>
    const rmData = <?= json_encode($rawMaterials) ?>;
    const fgData = <?= json_encode($finishedGoods) ?>;
    const uomData = <?= json_encode($uomMasters) ?>;
    let sectionCounter = 0;

    // SCRIPT BARU UNTUK FITUR CHECKBOX & SEARCH COPY MASSAL
    document.getElementById('selectAllCheckbox').addEventListener('change', function() {
        let checkboxes = document.querySelectorAll('.target-checkbox');
        let isChecked = this.checked;
        checkboxes.forEach(cb => {
            // Hanya centang row yang tidak di-hide oleh filter pencarian
            if (cb.closest('tr').style.display !== 'none') {
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

        let noResultRow = document.getElementById("noResultRow");
        if (tableId === 'bomTable' && noResultRow) noResultRow.style.display = hasResult ? "none" : "";
    }

    $(document).ready(function() {
        $('.select2-target').select2({ 
            width: '100%', placeholder: "-- Cari & Pilih Target Produksi --",
            templateResult: formatSelect2Options, templateSelection: formatSelect2Options
        });

        $('#sourceBomSelect').select2({
            width: '100%', placeholder: "-- Cari Resep Master --"
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

    function formatSelect2Options(item) {
        if (!item.id) return item.text;
        let text = item.text;
        let badgeMatch = text.match(/\(Sisa(:| Stok:).*?\)/); 
        if (badgeMatch) {
            let pureText = text.replace(badgeMatch[0], '').trim();
            return $(`<span>${pureText} <span class="stok-badge"><i class="ph-bold ph-package"></i> ${badgeMatch[0].replace('(', '').replace(')', '')}</span></span>`);
        }
        return text;
    }

    function initializeSelect2Component($element) {
        $element.select2({ 
            width: '100%', placeholder: "-- Cari Komponen / Bahan / Overhead --",
            templateResult: formatSelect2Options, templateSelection: formatSelect2Options
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
        
        if (selectElement.value === 'Tetap') {
            wageInput.val('0');
            wageInput.prop('readonly', true);
            prefix.css('opacity', '0.4');
            wageInput.css('opacity', '0.4');
        } else {
            wageInput.prop('readonly', false);
            prefix.css('opacity', '1');
            wageInput.css('opacity', '1');
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
                row.style.boxShadow = 'inset 0 2px 4px rgba(0,0,0,0.02)';
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
                        <button type="button" class="btn-remove" onclick="removeSection(this)" title="Hapus Bagian"><i class="ph-bold ph-trash"></i></button>
                    </div>
                </div>

                <div style="display:grid; grid-template-columns: 2fr 1fr; gap:16px; margin-bottom:20px;">
                    <div class="form-group" style="margin-bottom:0;">
                        <label>Nama Bagian</label>
                        <input type="text" name="section_name[]" class="form-control" placeholder="Contoh: Letter S / Monel / Tabung Luar / Isi Dalam" value="${name}" required autocomplete="off">
                    </div>
                    <div class="form-group" style="margin-bottom:0;">
                        <label>Kode Bagian (Opsional)</label>
                        <input type="text" name="section_code[]" class="form-control section-code" placeholder="Contoh: LS / MNL / TBG">
                    </div>
                </div>

                <div class="section-title">
                    <i class="ph-fill ph-cube" style="color: var(--success); background: rgba(16, 185, 129, 0.1);"></i> Kebutuhan Material Fisik & Komponen
                </div>
                <div style="background: rgba(16, 185, 129, 0.05); border: 1px dashed rgba(16, 185, 129, 0.3); padding: 12px 20px; border-radius: 12px; margin-bottom: 20px; font-size: 12px; color: var(--text-muted); font-weight: 600;">
                    <i class="ph-fill ph-info" style="color: var(--success);"></i> Material fisik yang terpakai langsung untuk bagian ini.
                </div>

                <div class="desktop-labels rm-labels">
                    <div>Pilih Material Fisik</div>
                    <div>Ukuran per Item</div>
                    <div>Jml Item / Pcs</div>
                    <div>Total Kebutuhan</div>
                    <div style="width: 45px;"></div>
                </div>

                <div class="rm-container"></div>

                <button type="button" class="btn-add" onclick="addRmRow(this.closest('.section-card').querySelector('.rm-container'), 'rm')" style="color: var(--success); border-color: rgba(16, 185, 129, 0.3); background: rgba(16, 185, 129, 0.02);">
                    <i class="ph-bold ph-plus-circle"></i> Tambah Kebutuhan Material Fisik
                </button>

                <div class="section-title" style="margin-top: 40px;">
                    <i class="ph-fill ph-lightning" style="color: var(--pink); background: rgba(236, 72, 153, 0.1);"></i> Kebutuhan Overhead Pabrik
                </div>
                <div style="background: rgba(236, 72, 153, 0.05); border: 1px dashed rgba(236, 72, 153, 0.3); padding: 12px 20px; border-radius: 12px; margin-bottom: 20px; font-size: 12px; color: var(--text-muted); font-weight: 600;">
                    <i class="ph-fill ph-magic-wand" style="color: var(--pink);"></i> Overhead untuk bagian ini seperti gas, listrik, kawat las, dll. (Langsung isi total).
                </div>

                <div class="desktop-labels oh-labels">
                    <div>Pilih Overhead / Biaya Tambahan</div>
                    <div>Total Kebutuhan</div>
                    <div style="width: 45px;"></div>
                </div>

                <div class="oh-container"></div>

                <button type="button" class="btn-add" onclick="addRmRow(this.closest('.section-card').querySelector('.oh-container'), 'oh')" style="color: var(--pink); border-color: rgba(236, 72, 153, 0.3); background: rgba(236, 72, 153, 0.02);">
                    <i class="ph-bold ph-plus-circle"></i> Tambah Biaya Overhead
                </button>

                <div class="section-title" style="margin-top: 45px;">
                    <i class="ph-fill ph-kanban" style="color: var(--secondary); background: rgba(245, 158, 11, 0.1);"></i> Tahapan Operasi & Upah Pekerja
                </div>
                <div style="background: rgba(245, 158, 11, 0.05); border: 1px solid rgba(245, 158, 11, 0.2); padding: 15px 20px; border-radius: 14px; margin-bottom: 20px; font-size: 12px; color: var(--text-muted); font-weight: 600; display: flex; gap: 12px; align-items: flex-start;">
                    <i class="ph-fill ph-warning-circle" style="color: var(--secondary); font-size: 20px;"></i>
                    <div><b>ATURAN ROUTING:</b> Susun urutan kerja untuk bagian ini dari awal sampai akhir. Anda juga bisa mengatur tahapan ini butuh tukang spesialisasi apa.</div>
                </div>

                <div class="op-container"></div>

                <button type="button" class="btn-add" onclick="addOpRow(this.closest('.section-card').querySelector('.op-container'))" style="color: var(--secondary); border-color: rgba(245, 158, 11, 0.3); background: rgba(245, 158, 11, 0.02);">
                    <i class="ph-bold ph-plus-circle"></i> Tambah Tahapan Kerja Baru
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

    function addOpRow(containerEl, opName = '', opWage = '', workerType = 'Borongan', specialty = '') {
        let row = document.createElement('div');
        row.className = 'dynamic-row op-row';
        
        let isReadonly = (workerType === 'Tetap') ? 'readonly' : '';
        let opacity = (workerType === 'Tetap') ? 'opacity: 0.4;' : '';

        const specialtiesList = ['Bending', 'Monel', 'Las Cacing', 'Las Cantum', 'Poles / Amril', 'Perakitan', 'Quality Control', 'Packing', 'Gudang / Logistik'];
        let specialtyOptions = '<option value="">-- Bebas (Semua Tukang) --</option>';
        specialtiesList.forEach(sp => {
            let isSel = (specialty === sp) ? 'selected' : '';
            specialtyOptions += `<option value="${sp}" ${isSel}>${sp}</option>`;
        });

        row.innerHTML = `
            <div class="step-number"></div>
            <input type="text" name="op_name[]" class="form-control op-name" placeholder="Cth: Cetak Monel / Bending" required autocomplete="off" style="font-weight: 900;" value="${opName}">
            
            <select name="op_specialty[]" class="form-control op-specialty" style="font-size: 12px; font-weight: 800; color: var(--prod-accent);">
                ${specialtyOptions}
            </select>

            <select name="op_worker_type[]" class="form-control op-worker-type" onchange="toggleWageType(this)" style="font-size: 12px; font-weight: 800;">
                <option value="Borongan" ${workerType === 'Borongan' ? 'selected' : ''}>👤 Borong</option>
                <option value="Tetap" ${workerType === 'Tetap' ? 'selected' : ''}>🏢 Tetap</option>
            </select>

            <div class="input-wrapper" style="border-color: rgba(245, 158, 11, 0.3);">
                <div class="prefix" style="${opacity}">Rp</div>
                <input type="text" name="op_wage[]" class="wage-input op-wage" placeholder="Upah Borongan" required onkeyup="formatRupiah(this)" autocomplete="off" style="color: var(--secondary); ${opacity}" value="${opWage}" ${isReadonly}>
            </div>
            
            <button type="button" class="btn-remove" onclick="removeOpRow(this)" title="Hapus Tahap"><i class="ph-bold ph-trash"></i></button>
        `;
        row.style.opacity = 0; row.style.transform = "translateY(20px)";
        containerEl.appendChild(row);
        setTimeout(() => { row.style.opacity = 1; row.style.transform = "translateY(0)"; renumberOperations(containerEl); }, 10);
    }

    function removeOpRow(btn) {
        let container = btn.closest('.op-container');
        if(container.children.length <= 1) { 
            btn.parentElement.animate([{ transform: 'translateX(-5px)' }, { transform: 'translateX(5px)' }, { transform: 'translateX(0)' }], { duration: 400 }); return; 
        }
        let row = btn.parentElement;
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
                
                <div class="input-wrapper" style="border-color: var(--info);">
                    <input type="text" class="mat-size" placeholder="Ukuran" value="${sizeVal}" onkeyup="calcMatTotal(this)" onchange="calcMatTotal(this)">
                    <select class="prefix mat-uom" style="background:rgba(59, 130, 246, 0.1); color:var(--info); border:none; outline:none; font-weight:900; cursor:pointer; padding: 0 14px; border-left: 2px solid var(--border-subtle); width: auto;" onchange="calcMatTotal(this)">
                        ${buildUomOptions(sizeUomVal)}
                    </select>
                </div>

                <div class="input-wrapper" style="border-color: var(--warning);">
                    <input type="text" class="mat-pcs" placeholder="Jml Item" value="${qtyVal}" onkeyup="calcMatTotal(this)" onchange="calcMatTotal(this)" required>
                    <select class="prefix mat-qty-uom" style="background:rgba(245, 158, 11, 0.1); color:var(--warning); border:none; outline:none; font-weight:900; cursor:pointer; padding: 0 14px; border-left: 2px solid var(--border-subtle); width: auto;" onchange="calcMatTotal(this)">
                        ${buildUomOptions(qtyUomVal)}
                    </select>
                </div>

                <div class="input-wrapper" style="border-color: var(--success); background: rgba(16, 185, 129, 0.05);">
                    <div class="prefix" style="background:transparent; color:var(--success); border-right:none;">=</div>
                    <input type="text" class="qty-real item-qty" readonly value="${totalVal}" style="color:var(--success); font-weight:900; background:transparent;">
                    <div class="prefix base-uom-lbl" style="background:transparent; color:var(--success); border-left: 1px solid rgba(16, 185, 129, 0.2);">PCS</div>
                </div>
                <button type="button" class="btn-remove" onclick="this.parentElement.remove()" title="Hapus Baris"><i class="ph-bold ph-trash"></i></button>
            `;
        } else {
            row.innerHTML = `
                <select class="form-control select2-component item-sku" required onchange="updateOhRow(this)">
                    <option value=""></option> ${buildMaterialOptions()}
                </select>
                <div class="input-wrapper" style="border-color: var(--pink);">
                    <input type="text" class="qty-real item-qty oh-qty qty-tampil" placeholder="Total Kebutuhan" value="${totalVal}" required style="color: var(--pink); font-weight:900;" onkeyup="this.value = this.value.replace(/,/g, '.').replace(/[^0-9.]/g, '')">
                    <div class="prefix oh-uom-lbl" style="background:rgba(236, 72, 153, 0.1); color:var(--pink);">Unit</div>
                </div>
                <button type="button" class="btn-remove" onclick="this.parentElement.remove()" title="Hapus Baris"><i class="ph-bold ph-trash"></i></button>
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
                if (opName.trim() !== '') {
                    operations.push({ step_order: opIndex + 1, name: opName, worker_type: workerType, specialty: specialty, wage: wage });
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
            Swal.close();
            if(res.status === 'success') {
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
                                addOpRow(currentSection.querySelector('.op-container'), op.operation_name, formattedWage, op.worker_type || 'Borongan', op.specialty_required || '');
                            });
                        } else {
                            addOpRow(currentSection.querySelector('.op-container'), '', '', 'Borongan', '');
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
                            addOpRow(currentSection.querySelector('.op-container'), op.operation_name, formattedWage, op.worker_type || 'Borongan', op.specialty_required || '');
                        });
                    } else {
                        addOpRow(currentSection.querySelector('.op-container'), '', '', 'Borongan', '');
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
        btnSave.style.background = 'linear-gradient(135deg, var(--primary), #6d28d9)';
        document.getElementById('btnCancelEdit').style.display = 'none';
    }

    document.getElementById('formBOM').addEventListener('submit', function(e) {
        let isValid = true;

        if (document.querySelectorAll('.section-card').length < 1) {
            e.preventDefault();
            Swal.fire('Peringatan', 'Minimal harus ada 1 bagian / section.', 'warning');
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
            Swal.fire('Peringatan', 'Pastikan nama bagian dan kuantitas material/overhead sudah valid.', 'warning');
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
</script>

<?= $this->endSection() ?>