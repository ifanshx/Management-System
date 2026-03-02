<?= $this->extend('layout/template') ?>
<?= $this->section('content') ?>

<style>
    /* =========================================================
       1. PAGE HEADER
       ========================================================= */
    .page-header { margin-bottom: 25px; max-width: 800px; margin-left: auto; margin-right: auto;}
    .page-title h1 { font-size: 26px; font-weight: 900; color: var(--text-main); margin-bottom: 5px; display: flex; align-items: center; gap: 10px; letter-spacing: -0.5px;}
    .page-title p { font-size: 13px; color: var(--text-muted); margin: 0; font-weight: 500;}
    
    .btn-back { color: var(--text-muted); text-decoration: none; font-size: 12px; font-weight: 800; display: inline-flex; align-items: center; gap: 6px; margin-bottom: 15px; padding: 8px 16px; background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: 100px; transition: all 0.3s;}
    .btn-back:hover { color: #8b5cf6; border-color: #8b5cf6; transform: translateX(-4px);}

    /* =========================================================
       2. MAIN BUILDER CARD (COMPACT)
       ========================================================= */
    .builder-card { background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: 20px; padding: 30px 35px; box-shadow: 0 10px 30px -10px rgba(0,0,0,0.05); max-width: 800px; margin: 0 auto; border-top: 5px solid #8b5cf6;}
    @media (max-width: 768px) { .builder-card { padding: 20px; } }
    
    .target-box { background: linear-gradient(135deg, rgba(139, 92, 246, 0.05), rgba(37, 99, 235, 0.05)); border: 1px solid rgba(139, 92, 246, 0.2); padding: 20px; border-radius: 16px; margin-bottom: 25px;}
    
    .form-group { margin-bottom: 15px; }
    .form-group label { display: block; font-size: 11px; font-weight: 800; color: var(--text-muted); margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.5px;}
    .form-control { width: 100%; background: var(--bg-base); border: 1px solid var(--border-subtle); padding: 12px 16px; border-radius: 10px; font-size: 13px; font-weight: 600; outline: none; color: var(--text-main); transition: all 0.2s;}
    .form-control:focus { border-color: #8b5cf6; background: var(--bg-surface); box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.15);}

    /* =========================================================
       3. DYNAMIC ROWS (RESEP BAHAN)
       ========================================================= */
    .section-title { font-size: 14px; font-weight: 900; color: var(--text-main); margin-bottom: 15px; border-bottom: 2px dashed var(--border-subtle); padding-bottom: 12px; display: flex; align-items: center; gap: 8px;}

    .rm-row { display: grid; grid-template-columns: 2fr 1fr auto; gap: 12px; align-items: center; background: var(--bg-base); padding: 12px 16px; border-radius: 12px; border: 1px dashed var(--border-subtle); margin-bottom: 10px; transition: all 0.2s;}
    .rm-row:hover { border-color: #8b5cf6; box-shadow: 0 4px 10px rgba(139, 92, 246, 0.08); background: var(--bg-surface);}
    @media (max-width: 640px) { .rm-row { grid-template-columns: 1fr; } }
    
    .btn-remove { background: rgba(239, 68, 68, 0.08); color: #ef4444; border: 1px solid transparent; width: 42px; height: 42px; border-radius: 10px; font-size: 20px; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.2s;}
    .btn-remove:hover { background: #ef4444; color: #fff; transform: scale(1.05) rotate(5deg);}

    .btn-add { background: var(--bg-surface); color: #8b5cf6; border: 2px dashed #a78bfa; width: 100%; padding: 14px; border-radius: 12px; font-weight: 800; font-size: 13px; cursor: pointer; margin-bottom: 25px; transition: all 0.2s; display: flex; align-items: center; justify-content: center; gap: 8px;}
    .btn-add:hover { background: rgba(139, 92, 246, 0.05); transform: translateY(-1px); border-color: #8b5cf6;}

    .btn-save { width: 100%; background: #8b5cf6; color: #fff; border: none; padding: 18px; border-radius: 14px; font-size: 15px; font-weight: 900; cursor: pointer; box-shadow: 0 8px 20px -5px rgba(139, 92, 246, 0.5); transition: all 0.2s; display: flex; align-items: center; justify-content: center; gap: 8px;}
    .btn-save:hover { background: #7c3aed; transform: translateY(-2px); box-shadow: 0 10px 25px -5px rgba(139, 92, 246, 0.6);}
    .btn-save:disabled { background: var(--border-subtle); color: var(--text-muted); box-shadow: none; cursor: not-allowed; transform: none;}
</style>

<div class="page-header">
    <a href="<?= base_url('/production') ?>" class="btn-back"><i class="ph-bold ph-arrow-left"></i> Kembali ke Pusat Produksi</a>
    <div class="page-title">
        <h1>
            <div style="background: rgba(139, 92, 246, 0.1); color: #8b5cf6; padding: 6px; border-radius: 10px; display: flex;">
                <i class="ph-fill ph-flask"></i>
            </div>
            Bill of Materials (BoM) Builder
        </h1>
        <p>Rakit komposisi resep standar yang akurat untuk 1 Unit Produk (PRD).</p>
    </div>
</div>

<div class="builder-card">
    <form action="<?= base_url('/production/store_bom') ?>" method="post">
        <?= csrf_field() ?>

        <div class="target-box">
            <div class="form-group" style="margin-bottom: 12px;">
                <label style="color: #8b5cf6;"><i class="ph-fill ph-target"></i> Target Produksi (Kode PRD-)</label>
                <select name="fg_sku" class="form-control" required style="border: 2px solid rgba(139, 92, 246, 0.3); font-weight: 800;">
                    <option value="">-- Pilih Produk Jadi (PRD) --</option>
                    <?php foreach($finishedGoods as $fg): ?>
                        <option value="<?= esc($fg['sku']) ?>">[<?= esc($fg['sku']) ?>] <?= esc($fg['item_name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <?php if(empty($finishedGoods)): ?>
                    <div style="font-size: 11px; color: #ef4444; font-weight: 700; margin-top: 6px;">*Anda belum memiliki Produk Jadi (PRD) di Master Gudang.</div>
                <?php endif; ?>
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label>Nama Resep / Formulasi</label>
                <input type="text" name="recipe_name" class="form-control" placeholder="Cth: Formulasi Standar Knalpot WR155" required autocomplete="off">
            </div>
        </div>

        <div class="section-title">
            <i class="ph-fill ph-puzzle-piece" style="color: #8b5cf6;"></i> Komponen Material Dasar (Kode MAT-)
        </div>

        <div id="rm-container">
            <div class="rm-row">
                <select name="rm_sku[]" class="form-control" required>
                    <option value="">-- Pilih Material (MAT) --</option>
                    <?php foreach($rawMaterials as $rm): ?>
                        <option value="<?= esc($rm['sku_material'] ?? $rm['sku']) ?>">[<?= esc($rm['sku_material'] ?? $rm['sku']) ?>] <?= esc($rm['material_name'] ?? $rm['item_name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <div style="position: relative;">
                    <input type="number" name="qty[]" class="form-control" placeholder="Kebutuhan" step="0.01" required style="padding-right: 45px;">
                    <span style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); font-size: 11px; font-weight: 800; color: var(--text-muted);">Pcs/Kg</span>
                </div>
                <button type="button" class="btn-remove" onclick="this.parentElement.remove()" title="Hapus Baris"><i class="ph-bold ph-x"></i></button>
            </div>
        </div>
        
        <?php if(empty($rawMaterials)): ?>
            <div style="background: rgba(239, 68, 68, 0.1); border: 1px dashed rgba(239, 68, 68, 0.3); color: #ef4444; font-size: 11px; padding: 10px 15px; border-radius: 10px; margin-bottom: 15px; font-weight: 700; display: flex; gap: 8px; align-items: center;">
                <i class="ph-fill ph-warning-circle" style="font-size: 18px;"></i> 
                Sistem belum menemukan Material berawalan "MAT-" di gudang Anda.
            </div>
        <?php endif; ?>

        <button type="button" class="btn-add" onclick="addRmRow()" <?= empty($rawMaterials) ? 'disabled style="opacity:0.5; cursor:not-allowed;"' : '' ?>>
            <i class="ph-bold ph-plus-circle" style="font-size: 16px;"></i> Tambah Komponen Material
        </button>

        <button type="submit" class="btn-save" <?= (empty($finishedGoods) || empty($rawMaterials)) ? 'disabled' : '' ?>>
            <i class="ph-bold ph-floppy-disk" style="font-size: 18px;"></i> Simpan Resep Produksi
        </button>
    </form>
</div>

<script>
    const rmData = <?= json_encode($rawMaterials) ?>;

    function addRmRow() {
        if(rmData.length === 0) return;
        
        let container = document.getElementById('rm-container');
        let options = '<option value="">-- Pilih Material (MAT) --</option>';
        rmData.forEach(rm => {
            let sku = rm.sku_material ? rm.sku_material : rm.sku;
            let name = rm.material_name ? rm.material_name : rm.item_name;
            options += `<option value="${sku}">[${sku}] ${name}</option>`;
        });

        let row = document.createElement('div');
        row.className = 'rm-row';
        row.innerHTML = `
            <select name="rm_sku[]" class="form-control" required>${options}</select>
            <div style="position: relative;">
                <input type="number" name="qty[]" class="form-control" placeholder="Kebutuhan" step="0.01" required style="padding-right: 45px;">
                <span style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); font-size: 11px; font-weight: 800; color: var(--text-muted);">Pcs/Kg</span>
            </div>
            <button type="button" class="btn-remove" onclick="this.parentElement.remove()" title="Hapus Baris"><i class="ph-bold ph-x"></i></button>
        `;
        
        row.style.opacity = 0;
        row.style.transform = "translateY(15px)";
        container.appendChild(row);
        
        setTimeout(() => {
            row.style.opacity = 1;
            row.style.transform = "translateY(0)";
        }, 10);
    }
</script>

<?= $this->endSection() ?>