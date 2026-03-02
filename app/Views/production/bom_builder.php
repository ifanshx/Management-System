<?= $this->extend('layout/template') ?>
<?= $this->section('content') ?>

<style>
    .page-header { margin-bottom: 25px; }
    .page-title h1 { font-size: 26px; font-weight: 800; color: var(--text-main); margin-bottom: 5px;}
    
    .builder-card { background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: 16px; padding: 25px; box-shadow: var(--shadow-card); max-width: 800px; margin: 0 auto;}
    
    .form-group { margin-bottom: 20px; }
    .form-group label { display: block; font-size: 12px; font-weight: 800; color: var(--text-muted); margin-bottom: 8px; text-transform: uppercase;}
    .form-control { width: 100%; background: var(--bg-base); border: 1px solid var(--border-subtle); padding: 14px 15px; border-radius: 10px; font-size: 14px; font-weight: 600; outline: none; color: var(--text-main);}
    .form-control:focus { border-color: #8b5cf6; box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.1);}

    /* Dynamic Row untuk Bahan Baku */
    .rm-row { display: flex; gap: 15px; align-items: center; margin-bottom: 10px; background: rgba(0,0,0,0.01); padding: 10px; border-radius: 12px; border: 1px dashed var(--border-subtle);}
    html.dark .rm-row { background: rgba(255,255,255,0.02); }
    .rm-row select { flex: 2; }
    .rm-row input { flex: 1; }
    
    .btn-remove { background: rgba(239, 68, 68, 0.1); color: #ef4444; border: none; width: 40px; height: 40px; border-radius: 8px; font-size: 18px; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: 0.2s;}
    .btn-remove:hover { background: #ef4444; color: #fff;}

    .btn-add { background: var(--bg-base); color: #8b5cf6; border: 2px dashed #8b5cf6; width: 100%; padding: 12px; border-radius: 10px; font-weight: 800; cursor: pointer; margin-bottom: 20px; transition: 0.2s;}
    .btn-add:hover { background: rgba(139, 92, 246, 0.1); }

    .btn-save { width: 100%; background: #8b5cf6; color: #fff; border: none; padding: 18px; border-radius: 12px; font-size: 16px; font-weight: 900; cursor: pointer; box-shadow: 0 4px 15px rgba(139, 92, 246, 0.3); transition: 0.2s;}
    .btn-save:hover { background: #7c3aed; transform: translateY(-2px);}
</style>

<div class="page-header" style="max-width: 800px; margin: 0 auto 25px auto;">
    <a href="<?= base_url('/production') ?>" style="color: var(--text-muted); text-decoration: none; font-size: 13px; font-weight: 700; display: inline-flex; align-items: center; gap: 5px; margin-bottom: 10px;"><i class="ph ph-arrow-left"></i> Kembali</a>
    <div class="page-title">
        <h1><i class="ph ph-flask" style="color: #8b5cf6;"></i> Bill of Materials (BoM) Builder</h1>
        <p>Rakit resep standar untuk satu unit Knalpot.</p>
    </div>
</div>

<div class="builder-card">
    <form action="<?= base_url('/production/store_bom') ?>" method="post">
        <?= csrf_field() ?>

        <div style="background: rgba(139, 92, 246, 0.05); border: 1px solid rgba(139, 92, 246, 0.2); padding: 20px; border-radius: 12px; margin-bottom: 25px;">
            <div class="form-group" style="margin-bottom: 10px;">
                <label style="color: #8b5cf6;">Target Barang Jadi (Knalpot)</label>
                <select name="fg_sku" class="form-control" required style="border-color: rgba(139, 92, 246, 0.5);">
                    <option value="">-- Pilih Barang Jadi dari Gudang --</option>
                    <?php foreach($finishedGoods as $fg): ?>
                        <option value="<?= esc($fg['sku']) ?>">[<?= esc($fg['sku']) ?>] <?= esc($fg['item_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <input type="text" name="recipe_name" class="form-control" placeholder="Nama Resep (Cth: Resep Standar WR155)" required>
            </div>
        </div>

        <div style="font-size: 14px; font-weight: 900; color: var(--text-main); margin-bottom: 15px; border-bottom: 2px dashed var(--border-subtle); padding-bottom: 10px;">
            <i class="ph ph-puzzle-piece"></i> Komposisi Bahan Baku (Untuk 1 Pcs Knalpot)
        </div>

        <div id="rm-container">
            <div class="rm-row">
                <select name="rm_sku[]" class="form-control" required>
                    <option value="">-- Pilih Bahan Baku --</option>
                    <?php foreach($rawMaterials as $rm): ?>
                        <option value="<?= esc($rm['sku']) ?>">[<?= esc($rm['sku']) ?>] <?= esc($rm['item_name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <input type="number" name="qty[]" class="form-control" placeholder="Qty (Cth: 1.5)" step="0.01" required>
                <button type="button" class="btn-remove" onclick="this.parentElement.remove()"><i class="ph ph-trash"></i></button>
            </div>
        </div>

        <button type="button" class="btn-add" onclick="addRmRow()"><i class="ph ph-plus-circle"></i> Tambah Bahan Baku</button>

        <button type="submit" class="btn-save"><i class="ph ph-floppy-disk"></i> Simpan Resep Produksi</button>
    </form>
</div>

<script>
    // Data bahan baku di-parse ke JS agar bisa digenerate dinamis
    const rmData = <?= json_encode($rawMaterials) ?>;

    function addRmRow() {
        let container = document.getElementById('rm-container');
        
        let options = '<option value="">-- Pilih Bahan Baku --</option>';
        rmData.forEach(rm => {
            options += `<option value="${rm.sku}">[${rm.sku}] ${rm.item_name}</option>`;
        });

        let row = document.createElement('div');
        row.className = 'rm-row';
        row.innerHTML = `
            <select name="rm_sku[]" class="form-control" required>${options}</select>
            <input type="number" name="qty[]" class="form-control" placeholder="Qty (Cth: 0.5)" step="0.01" required>
            <button type="button" class="btn-remove" onclick="this.parentElement.remove()"><i class="ph ph-trash"></i></button>
        `;
        container.appendChild(row);
    }
</script>

<?= $this->endSection() ?>