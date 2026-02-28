<?= $this->extend('layout/template') ?>
<?= $this->section('content') ?>

<style>
    .page-header { margin-bottom: 25px; display: flex; justify-content: space-between; align-items: flex-end;}
    .page-title h1 { font-size: 26px; font-weight: 800; color: var(--text-main); margin-bottom: 5px; display: flex; align-items: center; gap: 10px;}
    
    /* --- TAB NAVIGATION (MODERN SAAS STYLE) --- */
    .tab-nav { display: flex; gap: 10px; background: var(--bg-surface); padding: 8px; border-radius: 16px; border: 1px solid var(--border-subtle); width: fit-content; margin-bottom: 20px; box-shadow: var(--shadow-card);}
    .tab-btn { padding: 10px 24px; font-size: 14px; font-weight: 800; border-radius: 10px; border: none; background: transparent; color: var(--text-muted); cursor: pointer; transition: 0.3s; display: flex; align-items: center; gap: 8px;}
    .tab-btn:hover { color: var(--text-main); }
    .tab-btn.active { background: var(--text-main); color: var(--bg-base); box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
    html.dark .tab-btn.active { background: var(--accent-main); color: #fff;}

    /* TAB CONTENT PANELS */
    .tab-content { display: none; animation: fadeIn 0.4s ease; }
    .tab-content.active { display: block; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

    /* BUTTONS */
    .btn-action { background: var(--accent-main); color: #fff; border: none; padding: 10px 20px; border-radius: 10px; font-weight: 800; font-size: 13px; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3); transition: 0.2s;}
    .btn-action:hover { background: #1d4ed8; transform: translateY(-2px); }

    /* TABLES */
    .table-card { background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: 16px; box-shadow: var(--shadow-card); overflow: hidden; margin-top: 15px;}
    table { width: 100%; border-collapse: collapse; text-align: left; }
    th { padding: 15px 20px; font-size: 11px; font-weight: 800; text-transform: uppercase; color: var(--text-muted); border-bottom: 2px solid var(--border-subtle); background: var(--bg-base); letter-spacing: 0.5px;}
    td { padding: 16px 20px; border-bottom: 1px solid var(--border-subtle); font-size: 13px; font-weight: 600; color: var(--text-main); vertical-align: middle;}
    tr:hover td { background: rgba(0,0,0,0.01); }

    /* BADGES */
    .sku-badge { background: rgba(99, 102, 241, 0.1); color: #4f46e5; border: 1px dashed rgba(99, 102, 241, 0.3); padding: 4px 10px; border-radius: 6px; font-family: monospace; font-weight: 800;}
    .stock-box { display: inline-flex; align-items: center; justify-content: center; min-width: 40px; height: 30px; border-radius: 6px; font-weight: 900; font-family: 'Space Mono', monospace; background: rgba(16, 185, 129, 0.1); color: #10b981;}
    .stock-danger { background: rgba(239, 68, 68, 0.1); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.3);}

    /* MODAL PURE CSS */
    .modal-overlay { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.6); backdrop-filter: blur(4px); z-index: 999; display: none; justify-content: center; align-items: center; opacity: 0; transition: opacity 0.3s;}
    .modal-overlay.active { display: flex; opacity: 1; }
    .modal-box { background: var(--bg-surface); border-radius: 20px; width: 100%; max-width: 600px; padding: 30px; transform: translateY(30px); transition: transform 0.3s; box-shadow: 0 20px 40px rgba(0,0,0,0.2);}
    .modal-overlay.active .modal-box { transform: translateY(0); }
    .modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
    .btn-close { background: none; border: none; font-size: 24px; color: var(--text-muted); cursor: pointer; }
    
    .form-group { margin-bottom: 15px; }
    .form-group label { display: block; font-size: 12px; font-weight: 700; color: var(--text-muted); margin-bottom: 6px; }
    .form-control { width: 100%; background: var(--bg-base); border: 1px solid var(--border-subtle); padding: 12px 15px; border-radius: 10px; font-size: 14px; color: var(--text-main); font-weight: 600; outline: none;}
    .form-control:focus { border-color: var(--accent-main); box-shadow: 0 0 0 3px var(--accent-light);}
    .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }

    /* AUTO SKU INFO */
    .auto-sku-info { background: rgba(59, 130, 246, 0.05); border: 1px dashed rgba(59, 130, 246, 0.3); color: #3b82f6; padding: 10px 15px; border-radius: 10px; font-size: 12px; font-weight: 700; display: flex; align-items: center; gap: 8px; margin-bottom: 20px;}
</style>

<div class="page-header">
    <div class="page-title">
        <h1><i class="ph ph-database" style="color: var(--accent-main);"></i> Master Inventaris Pabrik</h1>
        <p>Kelola data induk Barang Jadi (Siap Jual) dan Bahan Baku (Untuk Produksi).</p>
    </div>
</div>

<div class="tab-nav">
    <button class="tab-btn active" onclick="switchTab('fg')"><i class="ph ph-motorcycle"></i> Barang Jadi (Knalpot)</button>
    <button class="tab-btn" onclick="switchTab('rm')"><i class="ph ph-nut"></i> Bahan Baku Produksi</button>
</div>

<div id="tab-fg" class="tab-content active">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <div>
            <div style="font-size: 13px; color: var(--text-muted); font-weight: 700;">Aset Barang Jadi: <span style="color: var(--text-main); font-size: 16px;">Rp <?= number_format($totalValueFG,0,',','.') ?></span></div>
        </div>
        <button class="btn-action" onclick="openModal('modalFG')"><i class="ph ph-plus-circle"></i> Tambah Barang Jadi</button>
    </div>

    <div class="table-card">
        <div style="overflow-x: auto;">
            <table>
                <thead>
                    <tr>
                        <th>SKU Barang</th>
                        <th>Nama Knalpot / Produk</th>
                        <th>HPP (Modal)</th>
                        <th style="text-align: center;">Stok Gudang</th>
                        <th style="text-align: center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($finishedGoods)): ?>
                        <tr><td colspan="5" style="text-align:center; padding:30px; color:var(--text-muted);">Belum ada barang jadi.</td></tr>
                    <?php endif; ?>
                    <?php foreach($finishedGoods as $fg): ?>
                    <tr>
                        <td><span class="sku-badge"><?= esc($fg['sku']) ?></span></td>
                        <td style="font-weight: 800;"><?= esc($fg['item_name']) ?></td>
                        <td style="font-family: monospace;">Rp <?= number_format($fg['hpp'], 0, ',', '.') ?></td>
                        <td style="text-align: center;">
                            <div class="stock-box <?= ($fg['physical_stock'] <= $fg['min_stock']) ? 'stock-danger' : '' ?>">
                                <?= $fg['physical_stock'] ?>
                            </div>
                        </td>
                        <td style="text-align: center;">
                            <a href="<?= base_url('/warehouse/delete_fg/'.$fg['id']) ?>" onclick="return confirm('Hapus barang ini?')" style="color:#ef4444; font-size:18px;"><i class="ph ph-trash"></i></a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div id="tab-rm" class="tab-content">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <div>
            <div style="font-size: 13px; color: var(--text-muted); font-weight: 700;">Aset Bahan Baku: <span style="color: var(--text-main); font-size: 16px;">Rp <?= number_format($totalValueRM,0,',','.') ?></span></div>
        </div>
        <button class="btn-action" style="background: #f59e0b; box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);" onclick="openModal('modalRM')"><i class="ph ph-plus-circle"></i> Tambah Bahan Baku</button>
    </div>

    <div class="table-card">
        <div style="overflow-x: auto;">
            <table>
                <thead>
                    <tr>
                        <th>SKU Bahan</th>
                        <th>Nama Material (Pipa/Plat/Gas)</th>
                        <th>Harga Beli / Satuan</th>
                        <th style="text-align: center;">Stok Saat Ini</th>
                        <th style="text-align: center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($rawMaterials)): ?>
                        <tr><td colspan="5" style="text-align:center; padding:30px; color:var(--text-muted);">Belum ada bahan baku tercatat.</td></tr>
                    <?php endif; ?>
                    <?php foreach($rawMaterials as $rm): ?>
                    <tr>
                        <td><span class="sku-badge" style="color:#f59e0b; background: rgba(245, 158, 11, 0.1); border-color: rgba(245, 158, 11, 0.3);"><?= esc($rm['sku_material']) ?></span></td>
                        <td style="font-weight: 800;"><?= esc($rm['material_name']) ?></td>
                        <td style="font-family: monospace;">Rp <?= number_format($rm['hpp'], 0, ',', '.') ?> <span style="font-size:10px; color:var(--text-muted);">/ <?= esc($rm['unit']) ?></span></td>
                        <td style="text-align: center;">
                            <div class="stock-box <?= ($rm['physical_stock'] <= $rm['min_stock']) ? 'stock-danger' : '' ?>">
                                <?= floatval($rm['physical_stock']) ?> <?= esc($rm['unit']) ?>
                            </div>
                        </td>
                        <td style="text-align: center;">
                            <a href="<?= base_url('/warehouse/delete_rm/'.$rm['id']) ?>" onclick="return confirm('Hapus bahan ini?')" style="color:#ef4444; font-size:18px;"><i class="ph ph-trash"></i></a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>


<div class="modal-overlay" id="modalFG">
    <div class="modal-box">
        <div class="modal-header">
            <h2 style="font-size: 18px; font-weight: 800;"><i class="ph ph-motorcycle"></i> Input Barang Jadi</h2>
            <button class="btn-close" onclick="closeModal('modalFG')"><i class="ph ph-x"></i></button>
        </div>
        <form action="<?= base_url('/warehouse/store_fg') ?>" method="post">
            <?= csrf_field() ?>
<div class="auto-sku-info"><i class="ph ph-magic-wand"></i> SKU akan dibuat otomatis oleh sistem (Format Universal: FG-0001)</div>            <div class="form-group">
                <label>Nama Lengkap Knalpot</label>
                <input type="text" name="item_name" class="form-control" placeholder="Cth: Knalpot Standar WR155 Noric" required>
            </div>
            <div class="form-group">
                <label>Harga Pokok Produksi (HPP) - Est. Modal</label>
                <input type="text" name="hpp" class="form-control" placeholder="Rp 0" required onkeyup="formatRupiah(this)">
            </div>
            <div class="grid-2">
                <div class="form-group">
                    <label>Stok Fisik Awal</label>
                    <input type="number" name="initial_stock" class="form-control" value="0" required min="0">
                </div>
                <div class="form-group">
                    <label>Batas Minimum (Alert)</label>
                    <input type="number" name="min_stock" class="form-control" value="5" required min="1">
                </div>
            </div>
            <button type="submit" class="btn-action" style="width: 100%; justify-content: center; padding: 14px; margin-top:10px;"><i class="ph ph-floppy-disk"></i> Simpan Barang Jadi</button>
        </form>
    </div>
</div>

<div class="modal-overlay" id="modalRM">
    <div class="modal-box" style="border-top: 4px solid #f59e0b;">
        <div class="modal-header">
            <h2 style="font-size: 18px; font-weight: 800;"><i class="ph ph-nut"></i> Input Bahan Baku</h2>
            <button class="btn-close" onclick="closeModal('modalRM')"><i class="ph ph-x"></i></button>
        </div>
        <form action="<?= base_url('/warehouse/store_rm') ?>" method="post">
            <?= csrf_field() ?>
<div class="auto-sku-info" style="color: #f59e0b; background: rgba(245, 158, 11, 0.05); border-color: rgba(245, 158, 11, 0.3);"><i class="ph ph-magic-wand"></i> SKU dibuat otomatis (Format Universal: RM-0001)</div>            <div class="form-group">
                <label>Nama Material (Pipa/Plat/Glaswool)</label>
                <input type="text" name="material_name" class="form-control" placeholder="Cth: Pipa Stainless 2 Inch" required>
            </div>
            <div class="grid-2">
                <div class="form-group">
                    <label>Harga Beli Satuan (HPP)</label>
                    <input type="text" name="hpp" class="form-control" placeholder="Rp 0" required onkeyup="formatRupiah(this)">
                </div>
                <div class="form-group">
                    <label>Satuan (Unit)</label>
                    <select name="unit" class="form-control" required>
                        <option value="Batang">Batang</option>
                        <option value="Lembar">Lembar</option>
                        <option value="Kg">Kilogram (Kg)</option>
                        <option value="Liter">Liter</option>
                        <option value="Roll">Roll</option>
                        <option value="Pcs">Pcs</option>
                    </select>
                </div>
            </div>
            <div class="grid-2">
                <div class="form-group">
                    <label>Stok Fisik Awal</label>
                    <input type="number" step="0.1" name="initial_stock" class="form-control" value="0" required min="0">
                </div>
                <div class="form-group">
                    <label>Batas Minimum (Alert)</label>
                    <input type="number" step="0.1" name="min_stock" class="form-control" value="10" required min="1">
                </div>
            </div>
            <button type="submit" class="btn-action" style="width: 100%; background: #f59e0b; justify-content: center; padding: 14px; margin-top:10px; box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);"><i class="ph ph-floppy-disk"></i> Simpan Bahan Baku</button>
        </form>
    </div>
</div>

<script>
    // Logika Tab Sederhana & Cepat
    function switchTab(tabName) {
        // Hapus class active dari semua tombol dan konten
        document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
        
        // Tambahkan class active ke yang dipilih
        event.currentTarget.classList.add('active');
        document.getElementById('tab-' + tabName).classList.add('active');
    }

    // Modal Logic
    function openModal(modalId) { document.getElementById(modalId).classList.add('active'); }
    function closeModal(modalId) { document.getElementById(modalId).classList.remove('active'); }
    
    // Klik di luar area untuk menutup
    window.onclick = function(event) {
        if (event.target.classList.contains('modal-overlay')) {
            event.target.classList.remove('active');
        }
    }
</script>

<?= $this->endSection() ?>