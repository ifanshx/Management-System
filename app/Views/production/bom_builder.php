<?= $this->extend('layout/template') ?>
<?= $this->section('content') ?>

<style>
    /* =========================================================
       1. CORE LAYOUT & HEADER (RATA KIRI)
       ========================================================= */
    .page-header { margin-bottom: 30px; max-width: 1000px;} /* Dihapus margin auto agar rata kiri */
    
    .btn-back { background: var(--bg-surface); color: var(--text-muted); border: 1px solid var(--border-subtle); padding: 8px 16px; border-radius: 100px; font-size: 12px; font-weight: 800; display: inline-flex; align-items: center; gap: 6px; text-decoration: none; transition: all 0.3s; box-shadow: 0 2px 8px rgba(0,0,0,0.02); margin-bottom: 20px;}
    .btn-back:hover { color: #8b5cf6; border-color: #8b5cf6; transform: translateX(-4px); box-shadow: 0 4px 12px rgba(139, 92, 246, 0.15);}

    .page-title { display: flex; align-items: center; gap: 15px;}
    .title-icon { width: 48px; height: 48px; border-radius: 14px; background: linear-gradient(135deg, rgba(139, 92, 246, 0.15), rgba(139, 92, 246, 0.05)); color: #8b5cf6; display: flex; align-items: center; justify-content: center; font-size: 24px; border: 1px solid rgba(139, 92, 246, 0.2);}
    .page-title h1 { font-size: 28px; font-weight: 900; color: var(--text-main); margin: 0; letter-spacing: -0.5px;}
    .page-title p { font-size: 13px; color: var(--text-muted); font-weight: 500; margin: 4px 0 0 0;}

    /* =========================================================
       2. BENTO BUILDER CARD (RATA KIRI)
       ========================================================= */
    /* Dihapus margin: 0 auto agar box form rata kiri, lebar disesuaikan */
    .builder-card { background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: 24px; padding: 40px; box-shadow: 0 15px 40px -15px rgba(0,0,0,0.08); max-width: 1000px; transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1); border-top: 6px solid #8b5cf6;}
    .builder-card:hover { box-shadow: 0 20px 50px -15px rgba(139, 92, 246, 0.15); border-color: rgba(139, 92, 246, 0.3);}
    @media (max-width: 768px) { .builder-card { padding: 25px; } }
    
    .target-box { background: linear-gradient(135deg, rgba(139, 92, 246, 0.05), rgba(59, 130, 246, 0.05)); border: 1px solid rgba(139, 92, 246, 0.2); padding: 25px; border-radius: 16px; margin-bottom: 30px;}
    
    .form-group { margin-bottom: 18px; }
    .form-group label { display: block; font-size: 11px; font-weight: 900; color: var(--text-muted); margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px;}
    .form-control { width: 100%; background: var(--bg-base); border: 1px solid var(--border-subtle); padding: 14px 18px; border-radius: 12px; font-size: 14px; font-weight: 600; outline: none; color: var(--text-main); transition: all 0.3s;}
    .form-control:focus { border-color: #8b5cf6; background: var(--bg-surface); box-shadow: 0 0 0 4px rgba(139, 92, 246, 0.15);}

    /* =========================================================
       3. DYNAMIC ROWS
       ========================================================= */
    .section-title { font-size: 16px; font-weight: 900; color: var(--text-main); margin-bottom: 20px; display: flex; align-items: center; gap: 10px; padding-bottom: 15px; border-bottom: 2px dashed var(--border-subtle);}
    .section-title i { background: rgba(139, 92, 246, 0.1); color: #8b5cf6; padding: 6px; border-radius: 8px; font-size: 18px;}

    .rm-row { display: grid; grid-template-columns: 2fr 1fr auto; gap: 15px; align-items: center; background: var(--bg-base); padding: 15px 20px; border-radius: 16px; border: 1px solid var(--border-subtle); margin-bottom: 12px; transition: all 0.3s;}
    .rm-row:hover { border-color: #8b5cf6; box-shadow: 0 8px 20px -5px rgba(139, 92, 246, 0.15); background: var(--bg-surface); transform: translateY(-2px);}
    @media (max-width: 640px) { .rm-row { grid-template-columns: 1fr; } }
    
    .btn-remove { background: rgba(239, 68, 68, 0.05); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.2); width: 46px; height: 46px; border-radius: 12px; font-size: 22px; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.3s;}
    .btn-remove:hover { background: #ef4444; color: #fff; transform: scale(1.05) rotate(5deg); box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);}

    .btn-add { background: var(--bg-base); color: #8b5cf6; border: 2px dashed rgba(139, 92, 246, 0.5); width: 100%; padding: 16px; border-radius: 14px; font-weight: 800; font-size: 14px; cursor: pointer; margin-bottom: 30px; transition: all 0.3s; display: flex; align-items: center; justify-content: center; gap: 8px;}
    .btn-add:hover { background: rgba(139, 92, 246, 0.05); border-color: #8b5cf6; transform: translateY(-2px); box-shadow: 0 4px 15px rgba(139, 92, 246, 0.1);}

    .btn-save { width: 100%; background: linear-gradient(135deg, #8b5cf6, #7c3aed); color: #fff; border: none; padding: 20px; border-radius: 16px; font-size: 16px; font-weight: 900; cursor: pointer; box-shadow: 0 10px 25px -5px rgba(139, 92, 246, 0.5); transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1); display: flex; align-items: center; justify-content: center; gap: 10px;}
    .btn-save:hover { transform: translateY(-3px); box-shadow: 0 15px 30px -5px rgba(139, 92, 246, 0.6);}
    .btn-save:disabled { background: var(--bg-base); color: var(--text-muted); border: 2px dashed var(--border-subtle); box-shadow: none; cursor: not-allowed; transform: none;}
</style>

<div class="page-header">
    <a href="<?= base_url('/production') ?>" class="btn-back"><i class="ph-bold ph-arrow-left"></i> Kembali ke Dashboard Produksi</a>
    <div class="page-title">
        <div class="title-icon"><i class="ph-fill ph-flask"></i></div>
        <div>
            <h1>Bill of Materials (BoM)</h1>
            <p>Rakit komposisi resep standar yang akurat untuk 1 Unit Produk (PRD).</p>
        </div>
    </div>
</div>

<div class="builder-card">
    <form action="<?= base_url('/production/store_bom') ?>" method="post">
        <?= csrf_field() ?>

        <div class="target-box">
            <div class="form-group" style="margin-bottom: 18px;">
                <label style="color: #8b5cf6;"><i class="ph-fill ph-target"></i> Target Produksi (Kode PRD-)</label>
                <select name="fg_sku" class="form-control" required style="border: 2px solid rgba(139, 92, 246, 0.3); font-weight: 800; background: var(--bg-surface);">
                    <option value="">-- Pilih Produk Jadi (PRD) --</option>
                    <?php foreach($finishedGoods as $fg): ?>
                        <option value="<?= esc($fg['sku']) ?>">[<?= esc($fg['sku']) ?>] <?= esc($fg['item_name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <?php if(empty($finishedGoods)): ?>
                    <div style="font-size: 11px; color: #ef4444; font-weight: 700; margin-top: 8px; display:flex; align-items:center; gap:4px;"><i class="ph-fill ph-warning-circle"></i> *Anda belum memiliki Produk Jadi (PRD) di Master Gudang.</div>
                <?php endif; ?>
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label>Nama Resep / Formulasi</label>
                <input type="text" name="recipe_name" class="form-control" placeholder="Cth: Formulasi Standar Knalpot WR155" required autocomplete="off">
            </div>
        </div>

        <div class="section-title">
            <i class="ph-fill ph-puzzle-piece"></i> Komponen Material Dasar (Kode MAT-)
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
                    <input type="number" name="qty[]" class="form-control" placeholder="Kebutuhan" step="0.01" required style="padding-right: 55px; font-family: 'Space Mono', monospace; font-weight: 900; color: #8b5cf6;">
                    <span style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); font-size: 11px; font-weight: 900; color: var(--text-muted); background: var(--bg-surface); padding: 2px 6px; border-radius: 4px;">Pcs/Kg</span>
                </div>
                <button type="button" class="btn-remove" onclick="this.parentElement.remove()" title="Hapus Baris"><i class="ph-bold ph-x"></i></button>
            </div>
        </div>
        
        <?php if(empty($rawMaterials)): ?>
            <div style="background: rgba(239, 68, 68, 0.1); border: 1px dashed rgba(239, 68, 68, 0.3); color: #ef4444; font-size: 12px; padding: 12px 18px; border-radius: 12px; margin-bottom: 20px; font-weight: 700; display: flex; gap: 8px; align-items: center;">
                <i class="ph-fill ph-warning-circle" style="font-size: 24px;"></i> 
                Sistem belum menemukan Material berawalan "MAT-" di gudang Anda.
            </div>
        <?php endif; ?>

        <button type="button" class="btn-add" onclick="addRmRow()" <?= empty($rawMaterials) ? 'disabled style="opacity:0.5; cursor:not-allowed;"' : '' ?>>
            <i class="ph-bold ph-plus-circle" style="font-size: 18px;"></i> Tambah Komponen Material
        </button>

        <button type="submit" class="btn-save" <?= (empty($finishedGoods) || empty($rawMaterials)) ? 'disabled' : '' ?>>
            <i class="ph-bold ph-floppy-disk" style="font-size: 22px;"></i> Simpan Resep Produksi
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
                <input type="number" name="qty[]" class="form-control" placeholder="Kebutuhan" step="0.01" required style="padding-right: 55px; font-family: 'Space Mono', monospace; font-weight: 900; color: #8b5cf6;">
                <span style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); font-size: 11px; font-weight: 900; color: var(--text-muted); background: var(--bg-surface); padding: 2px 6px; border-radius: 4px;">Pcs/Kg</span>
            </div>
            <button type="button" class="btn-remove" onclick="this.parentElement.remove()" title="Hapus Baris"><i class="ph-bold ph-x"></i></button>
        `;
        
        row.style.opacity = 0;
        row.style.transform = "translateY(15px) scale(0.98)";
        container.appendChild(row);
        
        setTimeout(() => {
            row.style.opacity = 1;
            row.style.transform = "translateY(0) scale(1)";
        }, 10);
    }
</script>

<?= $this->endSection() ?>