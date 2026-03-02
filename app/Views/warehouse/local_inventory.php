<?= $this->extend('layout/template') ?>
<?= $this->section('content') ?>

<style>
    /* =========================================================
       PAGE HEADER
       ========================================================= */
    .page-header { margin-bottom: 25px; display: flex; justify-content: space-between; align-items: flex-end;}
    .page-title h1 { font-size: 28px; font-weight: 900; color: var(--text-main); margin: 0 0 5px 0; display: flex; align-items: center; gap: 10px; letter-spacing: -0.5px;}
    .page-title p { font-size: 14px; color: var(--text-muted); margin: 0; font-weight: 500;}
    
    /* =========================================================
       PREMIUM TAB NAVIGATION
       ========================================================= */
    .tab-nav { display: inline-flex; background: var(--bg-surface); padding: 8px; border-radius: 16px; border: 1px solid var(--border-subtle); margin-bottom: 25px; box-shadow: var(--shadow-sm);}
    .tab-btn { padding: 12px 24px; font-size: 14px; font-weight: 800; border-radius: 12px; border: none; background: transparent; color: var(--text-muted); cursor: pointer; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); display: flex; align-items: center; gap: 8px;}
    .tab-btn:hover { color: var(--text-main); }
    .tab-btn.active { background: var(--text-main); color: var(--bg-surface); box-shadow: 0 4px 15px rgba(0,0,0,0.15); }
    html.dark .tab-btn.active { background: var(--accent-main); color: #fff;}

    .tab-content { display: none; animation: slideFadeUp 0.4s cubic-bezier(0.16, 1, 0.3, 1); }
    .tab-content.active { display: block; }
    @keyframes slideFadeUp { from { opacity: 0; transform: translateY(15px); } to { opacity: 1; transform: translateY(0); } }

    /* =========================================================
       BUTTONS & COMPONENTS
       ========================================================= */
    .btn-primary { background: #3b82f6; color: #fff; border: none; padding: 12px 24px; border-radius: 12px; font-weight: 800; font-size: 14px; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3); transition: 0.3s;}
    .btn-primary:hover { background: #2563eb; transform: translateY(-2px); box-shadow: 0 8px 20px rgba(59, 130, 246, 0.4);}
    
    .btn-warning { background: #f59e0b; color: #fff; border: none; padding: 12px 24px; border-radius: 12px; font-weight: 800; font-size: 14px; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 4px 15px rgba(245, 158, 11, 0.3); transition: 0.3s;}
    .btn-warning:hover { background: #d97706; transform: translateY(-2px); box-shadow: 0 8px 20px rgba(245, 158, 11, 0.4);}

    .asset-summary { font-size: 13px; color: var(--text-muted); font-weight: 700; background: var(--bg-surface); padding: 10px 20px; border-radius: 12px; border: 1px solid var(--border-subtle); display: inline-flex; align-items: center; gap: 10px;}
    .asset-val { color: var(--text-main); font-size: 16px; font-weight: 900; font-family: 'Space Mono', monospace;}

    /* =========================================================
       TABLE DESIGN
       ========================================================= */
    .table-card { background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: 20px; box-shadow: var(--shadow-card); overflow: hidden; margin-top: 20px;}
    table { width: 100%; border-collapse: collapse; text-align: left; }
    th { padding: 18px 25px; font-size: 11px; font-weight: 800; text-transform: uppercase; color: var(--text-muted); border-bottom: 1px solid var(--border-subtle); background: var(--bg-base); letter-spacing: 0.5px;}
    td { padding: 16px 25px; border-bottom: 1px dashed var(--border-subtle); font-size: 14px; font-weight: 600; color: var(--text-main); vertical-align: middle;}
    tr:last-child td { border-bottom: none; }
    tr:hover td { background: rgba(0,0,0,0.015); }
    html.dark tr:hover td { background: rgba(255,255,255,0.02); }

    /* BADGES (PRD & MAT) */
    .sku-badge { padding: 6px 12px; border-radius: 8px; font-family: 'Space Mono', monospace; font-weight: 900; font-size: 12px; display: inline-block; border: 1px solid transparent; letter-spacing: -0.5px;}
    .sku-prd { background: rgba(59, 130, 246, 0.1); color: #3b82f6; border-color: rgba(59, 130, 246, 0.3);}
    .sku-mat { background: rgba(245, 158, 11, 0.1); color: #f59e0b; border-color: rgba(245, 158, 11, 0.3);}

    .stock-box { display: inline-flex; align-items: center; justify-content: center; padding: 6px 14px; border-radius: 8px; font-weight: 900; font-family: 'Space Mono', monospace; background: rgba(16, 185, 129, 0.1); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.2);}
    .stock-danger { background: rgba(239, 68, 68, 0.1); color: #ef4444; border-color: rgba(239, 68, 68, 0.3);}

    .btn-delete { color: var(--text-muted); font-size: 20px; transition: 0.2s; padding: 6px; display: inline-flex; align-items: center; justify-content: center; border-radius: 8px;}
    .btn-delete:hover { color: #ef4444; background: rgba(239, 68, 68, 0.1); transform: scale(1.1);}

    /* =========================================================
       MODAL PURE CSS
       ========================================================= */
    .modal-overlay { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); backdrop-filter: blur(6px); z-index: 1000; display: none; justify-content: center; align-items: center; opacity: 0; transition: opacity 0.3s;}
    .modal-overlay.active { display: flex; opacity: 1; }
    .modal-box { background: var(--bg-surface); border-radius: 24px; width: 100%; max-width: 550px; padding: 35px; transform: scale(0.95); transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1); box-shadow: 0 25px 50px -12px rgba(0,0,0,0.3); border: 1px solid var(--border-subtle);}
    .modal-overlay.active .modal-box { transform: scale(1); }
    
    .modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
    .modal-title { font-size: 20px; font-weight: 900; color: var(--text-main); display: flex; align-items: center; gap: 10px;}
    .btn-close { background: var(--bg-base); border: 1px solid var(--border-subtle); width: 36px; height: 36px; border-radius: 50%; font-size: 16px; color: var(--text-muted); cursor: pointer; display: flex; align-items: center; justify-content: center; transition: 0.2s;}
    .btn-close:hover { background: #ef4444; color: #fff; border-color: #ef4444; transform: rotate(90deg);}
    
    /* Form Elements */
    .form-group { margin-bottom: 20px; }
    .form-group label { display: block; font-size: 12px; font-weight: 800; color: var(--text-muted); margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px;}
    .form-control { width: 100%; background: var(--bg-base); border: 1px solid var(--border-subtle); padding: 14px 18px; border-radius: 12px; font-size: 14px; color: var(--text-main); font-weight: 600; outline: none; transition: 0.3s;}
    
    /* Focus Colors depending on Context */
    .focus-blue:focus { border-color: #3b82f6; background: var(--bg-surface); box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);}
    .focus-orange:focus { border-color: #f59e0b; background: var(--bg-surface); box-shadow: 0 0 0 4px rgba(245, 158, 11, 0.1);}
    
    .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }

    .input-money { display: flex; align-items: center; background: var(--bg-base); border: 1px solid var(--border-subtle); border-radius: 12px; overflow: hidden; transition: 0.3s;}
    .input-money.im-blue:focus-within { border-color: #3b82f6; box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1); background: var(--bg-surface);}
    .input-money.im-orange:focus-within { border-color: #f59e0b; box-shadow: 0 0 0 4px rgba(245, 158, 11, 0.1); background: var(--bg-surface);}
    .input-money span { padding: 14px 16px; background: rgba(0,0,0,0.03); font-size: 13px; font-weight: 800; color: var(--text-muted); border-right: 1px solid var(--border-subtle);}
    .input-money input { border: none; padding: 14px 16px; font-size: 14px; font-weight: 700; width: 100%; outline: none; background: transparent; color: var(--text-main); font-family: 'Space Mono', monospace;}

    /* Information Boxes */
    .auto-sku-info { padding: 14px 18px; border-radius: 12px; font-size: 13px; font-weight: 700; display: flex; align-items: center; gap: 10px; margin-bottom: 25px; line-height: 1.4;}
    .info-blue { background: rgba(59, 130, 246, 0.05); border: 1px dashed rgba(59, 130, 246, 0.3); color: #3b82f6;}
    .info-orange { background: rgba(245, 158, 11, 0.05); border: 1px dashed rgba(245, 158, 11, 0.3); color: #f59e0b;}
</style>

<div class="page-header">
    <div class="page-title">
        <h1><i class="ph ph-database" style="color: var(--accent-main);"></i> Master Inventaris Pabrik</h1>
        <p>Kelola data induk Produk Jadi (Siap Jual) dan Material Dasar (Bahan Baku Produksi).</p>
    </div>
</div>

<div class="tab-nav">
    <button class="tab-btn active" onclick="switchTab('fg')"><i class="ph ph-motorcycle"></i> Produk Jadi (PRD)</button>
    <button class="tab-btn" onclick="switchTab('rm')"><i class="ph ph-nut"></i> Material Mentah (MAT)</button>
</div>

<div id="tab-fg" class="tab-content active">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
        <div class="asset-summary">
            <i class="ph ph-vault" style="font-size: 18px; color: #3b82f6;"></i>
            Aset Produk (PRD): <span class="asset-val">Rp <?= number_format($totalValueFG, 0, ',', '.') ?></span>
        </div>
        <button class="btn-primary" onclick="openModal('modalFG')">
            <i class="ph ph-plus-circle"></i> Registrasi Produk Jadi
        </button>
    </div>

    <div class="table-card">
        <div style="overflow-x: auto;">
            <table>
                <thead>
                    <tr>
                        <th>Kode SKU Produk</th>
                        <th>Nama Knalpot / Produk</th>
                        <th>HPP (Modal Produksi)</th>
                        <th style="text-align: center;">Stok Tersedia</th>
                        <th style="text-align: center;">Tindakan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($finishedGoods)): ?>
                        <tr><td colspan="5" style="text-align:center; padding:50px; color:var(--text-muted);"><i class="ph ph-package" style="font-size: 48px; opacity: 0.5; margin-bottom: 10px; display: block;"></i>Belum ada data Produk Jadi.</td></tr>
                    <?php endif; ?>
                    <?php foreach($finishedGoods as $fg): ?>
                    <tr>
                        <td><span class="sku-badge sku-prd"><?= esc($fg['sku']) ?></span></td>
                        <td style="font-weight: 800;"><?= esc($fg['item_name']) ?></td>
                        <td style="font-family: 'Space Mono', monospace;">Rp <?= number_format($fg['hpp'], 0, ',', '.') ?></td>
                        <td style="text-align: center;">
                            <div class="stock-box <?= ($fg['physical_stock'] <= $fg['min_stock']) ? 'stock-danger' : '' ?>" title="Minimum Stok: <?= $fg['min_stock'] ?>">
                                <?= $fg['physical_stock'] ?>
                            </div>
                        </td>
                        <td style="text-align: center;">
                            <a href="<?= base_url('/warehouse/delete_fg/'.$fg['id']) ?>" onclick="return confirm('Hapus produk ini secara permanen?')" class="btn-delete" title="Hapus"><i class="ph ph-trash"></i></a>
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
            <i class="ph ph-vault" style="font-size: 18px; color: #f59e0b;"></i>
            Aset Material Mentah: <span class="asset-val">Rp <?= number_format($totalValueRM, 0, ',', '.') ?></span>
        </div>
        <button class="btn-warning" onclick="openModal('modalRM')">
            <i class="ph ph-plus-circle"></i> Registrasi Material Baru
        </button>
    </div>

    <div class="table-card">
        <div style="overflow-x: auto;">
            <table>
                <thead>
                    <tr>
                        <th>Kode SKU Material</th>
                        <th>Nama Material (Pipa/Plat/Gas)</th>
                        <th>Harga Beli Satuan</th>
                        <th style="text-align: center;">Stok Tersedia</th>
                        <th style="text-align: center;">Tindakan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($rawMaterials)): ?>
                        <tr><td colspan="5" style="text-align:center; padding:50px; color:var(--text-muted);"><i class="ph ph-nut" style="font-size: 48px; opacity: 0.5; margin-bottom: 10px; display: block;"></i>Belum ada data Material Dasar.</td></tr>
                    <?php endif; ?>
                    <?php foreach($rawMaterials as $rm): ?>
                    <tr>
                        <td><span class="sku-badge sku-mat"><?= esc($rm['sku_material']) ?></span></td>
                        <td style="font-weight: 800;"><?= esc($rm['material_name']) ?></td>
                        <td style="font-family: 'Space Mono', monospace;">Rp <?= number_format($rm['hpp'], 0, ',', '.') ?> <span style="font-size:11px; color:var(--text-muted); font-weight: 700;">/ <?= esc($rm['unit']) ?></span></td>
                        <td style="text-align: center;">
                            <div class="stock-box <?= ($rm['physical_stock'] <= $rm['min_stock']) ? 'stock-danger' : '' ?>" title="Minimum Stok: <?= $rm['min_stock'] ?>">
                                <?= floatval($rm['physical_stock']) ?> <?= esc($rm['unit']) ?>
                            </div>
                        </td>
                        <td style="text-align: center;">
                            <a href="<?= base_url('/warehouse/delete_rm/'.$rm['id']) ?>" onclick="return confirm('Hapus material ini secara permanen?')" class="btn-delete" title="Hapus"><i class="ph ph-trash"></i></a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal-overlay" id="modalFG">
    <div class="modal-box" style="border-top: 6px solid #3b82f6;">
        <div class="modal-header">
            <div class="modal-title"><i class="ph ph-motorcycle" style="color: #3b82f6;"></i> Input Produk Jadi</div>
            <button class="btn-close" onclick="closeModal('modalFG')"><i class="ph ph-x"></i></button>
        </div>
        <form action="<?= base_url('/warehouse/store_fg') ?>" method="post">
            <?= csrf_field() ?>
            <div class="auto-sku-info info-blue">
                <i class="ph ph-magic-wand" style="font-size: 24px;"></i> 
                Sistem akan membuat SKU otomatis dengan format Enterprise:<br><b>PRD-0001, PRD-0002, dst.</b>
            </div>            
            
            <div class="form-group">
                <label>Nama Lengkap Knalpot / Produk</label>
                <input type="text" name="item_name" class="form-control focus-blue" placeholder="Cth: Knalpot Standar WR155 Noric" required autocomplete="off">
            </div>
            
            <div class="form-group">
                <label>Modal Dasar Produksi (HPP)</label>
                <div class="input-money im-blue">
                    <span>Rp</span>
                    <input type="text" name="hpp" placeholder="0" required onkeyup="formatRupiah(this)" autocomplete="off">
                </div>
            </div>
            
            <div class="grid-2">
                <div class="form-group">
                    <label>Stok Fisik Saat Ini</label>
                    <input type="number" name="initial_stock" class="form-control focus-blue" value="0" required min="0" style="font-family: monospace; font-size: 16px;">
                </div>
                <div class="form-group">
                    <label>Peringatan Stok Minimum</label>
                    <input type="number" name="min_stock" class="form-control focus-blue" value="5" required min="1" style="font-family: monospace; font-size: 16px;">
                </div>
            </div>
            
            <button type="submit" class="btn-primary" style="width: 100%; justify-content: center; padding: 18px; margin-top: 10px;">
                <i class="ph ph-floppy-disk" style="font-size: 18px;"></i> Simpan Produk Jadi (PRD)
            </button>
        </form>
    </div>
</div>

<div class="modal-overlay" id="modalRM">
    <div class="modal-box" style="border-top: 6px solid #f59e0b;">
        <div class="modal-header">
            <div class="modal-title"><i class="ph ph-nut" style="color: #f59e0b;"></i> Input Material Mentah</div>
            <button class="btn-close" onclick="closeModal('modalRM')"><i class="ph ph-x"></i></button>
        </div>
        <form action="<?= base_url('/warehouse/store_rm') ?>" method="post">
            <?= csrf_field() ?>
            <div class="auto-sku-info info-orange">
                <i class="ph ph-magic-wand" style="font-size: 24px;"></i> 
                Sistem akan membuat SKU otomatis dengan format Enterprise:<br><b>MAT-0001, MAT-0002, dst.</b>
            </div>            
            
            <div class="form-group">
                <label>Nama Material (Pipa/Plat/Glaswool)</label>
                <input type="text" name="material_name" class="form-control focus-orange" placeholder="Cth: Pipa Stainless 2 Inch SS304" required autocomplete="off">
            </div>
            
            <div class="grid-2">
                <div class="form-group">
                    <label>Harga Beli (Modal / HPP)</label>
                    <div class="input-money im-orange">
                        <span>Rp</span>
                        <input type="text" name="hpp" placeholder="0" required onkeyup="formatRupiah(this)" autocomplete="off">
                    </div>
                </div>
                <div class="form-group">
                    <label>Satuan (Unit)</label>
                    <select name="unit" class="form-control focus-orange" required>
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
                    <label>Stok Fisik Saat Ini</label>
                    <input type="number" step="0.1" name="initial_stock" class="form-control focus-orange" value="0" required min="0" style="font-family: monospace; font-size: 16px;">
                </div>
                <div class="form-group">
                    <label>Peringatan Stok Minimum</label>
                    <input type="number" step="0.1" name="min_stock" class="form-control focus-orange" value="10" required min="1" style="font-family: monospace; font-size: 16px;">
                </div>
            </div>
            
            <button type="submit" class="btn-warning" style="width: 100%; justify-content: center; padding: 18px; margin-top: 10px;">
                <i class="ph ph-floppy-disk" style="font-size: 18px;"></i> Simpan Material (MAT)
            </button>
        </form>
    </div>
</div>

<script>
    // --- Logika Tab Segmented Premium ---
    function switchTab(tabName) {
        document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
        
        event.currentTarget.classList.add('active');
        document.getElementById('tab-' + tabName).classList.add('active');
    }

    // --- Modal Logic ---
    function openModal(modalId) { 
        document.getElementById(modalId).classList.add('active'); 
    }
    
    function closeModal(modalId) { 
        document.getElementById(modalId).classList.remove('active'); 
    }
    
    // Tutup modal jika klik di area gelap
    window.onclick = function(event) {
        if (event.target.classList.contains('modal-overlay')) {
            event.target.classList.remove('active');
        }
    }

    // --- Helper Format Rupiah ---
    function formatRupiah(angka) {
        if (!angka) return;
        let number_string = angka.value.replace(/[^,\d]/g, '').toString(),
            split   = number_string.split(','),
            sisa    = split[0].length % 3,
            rupiah  = split[0].substr(0, sisa),
            ribuan  = split[0].substr(sisa).match(/\d{3}/gi);
        if (ribuan) {
            let separator = sisa ? '.' : '';
            rupiah += separator + ribuan.join('.');
        }
        rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
        angka.value = rupiah;
    }
</script>

<?= $this->endSection() ?>