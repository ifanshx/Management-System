<?= $this->extend('layout/template') ?>
<?= $this->section('content') ?>

<style>
    /* =========================================================
       PAGE HEADER
       ========================================================= */
    .page-header { margin-bottom: 25px; display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 15px;}
    .page-title h1 { font-size: 26px; font-weight: 900; color: var(--text-main); margin: 0 0 5px 0; display: flex; align-items: center; gap: 10px; letter-spacing: -0.5px;}
    .page-title p { font-size: 13px; color: var(--text-muted); margin: 0; font-weight: 500;}
    
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
    .btn-primary { background: linear-gradient(135deg, #3b82f6, #2563eb); color: #fff; border: none; padding: 12px 24px; border-radius: 14px; font-weight: 900; font-size: 13px; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 8px 20px -6px rgba(59, 130, 246, 0.5); transition: 0.3s;}
    .btn-primary:hover { transform: translateY(-3px); box-shadow: 0 12px 25px -6px rgba(59, 130, 246, 0.6);}
    
    .btn-warning { background: linear-gradient(135deg, #f59e0b, #d97706); color: #fff; border: none; padding: 12px 24px; border-radius: 14px; font-weight: 900; font-size: 13px; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 8px 20px -6px rgba(245, 158, 11, 0.5); transition: 0.3s;}
    .btn-warning:hover { transform: translateY(-3px); box-shadow: 0 12px 25px -6px rgba(245, 158, 11, 0.6);}

    .asset-summary { font-size: 13px; color: var(--text-muted); font-weight: 800; background: var(--bg-surface); padding: 14px 24px; border-radius: 16px; border: 1px solid var(--border-subtle); display: inline-flex; align-items: center; gap: 10px; box-shadow: 0 4px 10px rgba(0,0,0,0.02);}
    .asset-val { color: var(--text-main); font-size: 18px; font-weight: 900; font-family: 'Space Mono', monospace; letter-spacing: -0.5px;}

    /* =========================================================
       TABLE DESIGN (STRICT BORDERS & COMPACT)
       ========================================================= */
    .table-card { background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: 20px; box-shadow: var(--shadow-card); overflow: hidden; margin-top: 20px;}
    table { width: 100%; border-collapse: collapse; white-space: nowrap; }
    
    th { text-align: center; padding: 16px 20px; font-size: 11px; font-weight: 900; text-transform: uppercase; color: var(--text-muted); border-bottom: 2px solid var(--border-subtle); border-right: 1px solid var(--border-subtle); background: var(--bg-base); letter-spacing: 0.5px;}
    td { text-align: center; padding: 16px 20px; border-bottom: 1px dashed var(--border-subtle); border-right: 1px solid var(--border-subtle); font-size: 13px; font-weight: 600; color: var(--text-main); vertical-align: middle; transition: 0.2s;}
    
    th:first-child, td:first-child { text-align: left; }
    th:nth-child(2), td:nth-child(2) { text-align: left; } /* Nama Produk Rata Kiri */
    th:last-child, td:last-child { border-right: none; }
    tr:last-child td { border-bottom: none; }
    
    tr:hover td { background: rgba(59, 130, 246, 0.02); }
    html.dark tr:hover td { background: rgba(255,255,255,0.02); }

    /* BADGES (PRD & MAT) */
    .sku-badge { padding: 6px 12px; border-radius: 8px; font-family: 'Space Mono', monospace; font-weight: 900; font-size: 12px; display: inline-block; border: 1px dashed transparent; letter-spacing: -0.5px;}
    .sku-prd { background: rgba(59, 130, 246, 0.1); color: #3b82f6; border-color: rgba(59, 130, 246, 0.3);}
    .sku-mat { background: rgba(245, 158, 11, 0.1); color: #f59e0b; border-color: rgba(245, 158, 11, 0.3);}

    .stock-box { display: inline-flex; align-items: center; justify-content: center; padding: 6px 14px; border-radius: 8px; font-weight: 900; font-family: 'Space Mono', monospace; background: rgba(16, 185, 129, 0.1); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.2);}
    .stock-danger { background: rgba(239, 68, 68, 0.1); color: #ef4444; border-color: rgba(239, 68, 68, 0.3); animation: pulseDanger 2s infinite;}
    @keyframes pulseDanger { 0% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.4); } 70% { box-shadow: 0 0 0 6px rgba(239, 68, 68, 0); } 100% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); } }

    .btn-delete { color: #ef4444; background: rgba(239, 68, 68, 0.05); font-size: 18px; transition: 0.3s; width: 34px; height: 34px; display: inline-flex; align-items: center; justify-content: center; border-radius: 10px; border: 1px solid transparent;}
    .btn-delete:hover { color: #fff; background: #ef4444; box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3); transform: translateY(-2px);}

    /* Empty State */
    .empty-state { text-align: center; padding: 60px 20px; color: var(--text-muted); }
    .empty-state i { font-size: 56px; color: var(--border-subtle); margin-bottom: 15px; display: block; }
    .empty-state p { font-size: 14px; font-weight: 500; margin: 0;}

    /* =========================================================
       MODAL PURE CSS (REDESIGNED)
       ========================================================= */
    .modal-overlay { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.6); backdrop-filter: blur(6px); z-index: 1000; display: none; justify-content: center; align-items: center; opacity: 0; transition: opacity 0.3s;}
    .modal-overlay.active { display: flex; opacity: 1; }
    
    .modal-box { background: var(--bg-surface); border-radius: 28px; width: 100%; max-width: 520px; padding: 40px; transform: scale(0.95) translateY(20px); transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1); box-shadow: 0 30px 60px -15px rgba(0,0,0,0.4); border: 1px solid var(--border-subtle);}
    .modal-overlay.active .modal-box { transform: scale(1) translateY(0); }
    
    .modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
    .modal-title { font-size: 20px; font-weight: 900; color: var(--text-main); display: flex; align-items: center; gap: 12px;}
    
    .btn-close { background: var(--bg-base); border: 1px solid var(--border-subtle); width: 36px; height: 36px; border-radius: 50%; font-size: 16px; color: var(--text-muted); cursor: pointer; display: flex; align-items: center; justify-content: center; transition: 0.2s;}
    .btn-close:hover { background: #ef4444; color: #fff; border-color: #ef4444; transform: rotate(90deg);}
    
    /* Form Elements */
    .form-group { margin-bottom: 20px; }
    .form-group label { display: block; font-size: 11px; font-weight: 900; color: var(--text-muted); margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px;}
    .form-control { width: 100%; background: var(--bg-base); border: 1px solid var(--border-subtle); padding: 14px 18px; border-radius: 12px; font-size: 14px; color: var(--text-main); font-weight: 600; outline: none; transition: 0.3s;}
    
    .focus-blue:focus { border-color: #3b82f6; background: var(--bg-surface); box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.15);}
    .focus-orange:focus { border-color: #f59e0b; background: var(--bg-surface); box-shadow: 0 0 0 4px rgba(245, 158, 11, 0.15);}
    
    .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }

    .input-money { display: flex; align-items: center; background: var(--bg-base); border: 1px solid var(--border-subtle); border-radius: 12px; overflow: hidden; transition: 0.3s;}
    .input-money.im-blue:focus-within { border-color: #3b82f6; box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.15); background: var(--bg-surface);}
    .input-money.im-orange:focus-within { border-color: #f59e0b; box-shadow: 0 0 0 4px rgba(245, 158, 11, 0.15); background: var(--bg-surface);}
    .input-money span { padding: 14px 16px; background: rgba(0,0,0,0.03); font-size: 13px; font-weight: 900; color: var(--text-muted); border-right: 1px solid var(--border-subtle);}
    .input-money input { border: none; padding: 14px 16px; font-size: 15px; font-weight: 800; width: 100%; outline: none; background: transparent; color: var(--text-main); font-family: 'Space Mono', monospace;}

    .auto-sku-info { padding: 16px 20px; border-radius: 14px; font-size: 13px; font-weight: 700; display: flex; align-items: center; gap: 14px; margin-bottom: 25px; line-height: 1.5;}
    .info-blue { background: rgba(59, 130, 246, 0.08); border: 1px dashed rgba(59, 130, 246, 0.3); color: #3b82f6;}
    .info-orange { background: rgba(245, 158, 11, 0.08); border: 1px dashed rgba(245, 158, 11, 0.3); color: #f59e0b;}

    .btn-submit-modal { width: 100%; color: #fff; border: none; padding: 18px; border-radius: 16px; font-size: 15px; font-weight: 900; cursor: pointer; transition: all 0.3s ease; display: flex; justify-content: center; align-items: center; gap: 8px; margin-top: 10px; }
    .btn-submit-modal.btn-blue { background: linear-gradient(135deg, #3b82f6, #2563eb); box-shadow: 0 8px 20px -5px rgba(59, 130, 246, 0.5); }
    .btn-submit-modal.btn-orange { background: linear-gradient(135deg, #f59e0b, #d97706); box-shadow: 0 8px 20px -5px rgba(245, 158, 11, 0.5); }
    .btn-submit-modal:hover { transform: translateY(-3px); filter: brightness(1.1); }
</style>

<div class="page-header">
    <div class="page-title">
        <h1>
            <div style="background: rgba(16, 185, 129, 0.1); color: #10b981; padding: 8px; border-radius: 12px; display: flex;">
                <i class="ph-fill ph-database"></i>
            </div>
            Master Inventaris Pabrik
        </h1>
        <p>Kelola data induk Produk Jadi (PRD) dan Material Dasar (MAT) dalam satu dasbor cerdas.</p>
    </div>
</div>

<div class="tab-nav">
    <button class="tab-btn active" onclick="switchTab('fg')"><i class="ph-fill ph-motorcycle"></i> Produk Jadi (PRD)</button>
    <button class="tab-btn" onclick="switchTab('rm')"><i class="ph-fill ph-nut"></i> Material Mentah (MAT)</button>
</div>

<div id="tab-fg" class="tab-content active">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
        <div class="asset-summary">
            <div style="background: rgba(59, 130, 246, 0.1); padding: 6px; border-radius: 8px; display: flex;"><i class="ph-fill ph-vault" style="color: #3b82f6;"></i></div>
            Aset Produk (PRD): <span class="asset-val">Rp <?= number_format($totalValueFG, 0, ',', '.') ?></span>
        </div>
        <button class="btn-primary" onclick="openModal('modalFG')">
            <i class="ph-bold ph-plus-circle" style="font-size: 18px;"></i> Registrasi Produk Baru
        </button>
    </div>

    <div class="table-card">
        <div style="overflow-x: auto;">
            <table>
                <thead>
                    <tr>
                        <th>Kode SKU Produk</th>
                        <th>Nama Knalpot / Produk</th>
                        <th style="text-align: right;">HPP (Modal)</th>
                        <th>Stok Tersedia</th>
                        <th>Tindakan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($finishedGoods)): ?>
                        <tr>
                            <td colspan="5">
                                <div class="empty-state">
                                    <i class="ph-fill ph-package"></i>
                                    <h3 style="margin: 0 0 5px 0; color: var(--text-main); font-weight: 800; font-size: 16px;">Belum ada Produk Jadi</h3>
                                    <p>Klik tombol registrasi di sudut kanan atas.</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                    <?php foreach($finishedGoods as $fg): ?>
                    <tr>
                        <td><span class="sku-badge sku-prd"><?= esc($fg['sku']) ?></span></td>
                        <td style="font-weight: 800;"><?= esc($fg['item_name']) ?></td>
                        <td style="text-align: right; font-family: 'Space Mono', monospace; font-weight: 900; color: #10b981;">
                            Rp <?= number_format($fg['hpp'], 0, ',', '.') ?>
                        </td>
                        <td>
                            <div class="stock-box <?= ($fg['physical_stock'] <= $fg['min_stock']) ? 'stock-danger' : '' ?>" title="Minimum Stok: <?= $fg['min_stock'] ?>">
                                <?= $fg['physical_stock'] ?>
                            </div>
                        </td>
                        <td>
                            <a href="#" onclick="confirmDelete(event, '<?= base_url('/warehouse/delete_fg/'.$fg['id']) ?>')" class="btn-delete" title="Hapus Data">
                                <i class="ph-bold ph-trash"></i>
                            </a>
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
            <div style="background: rgba(245, 158, 11, 0.1); padding: 6px; border-radius: 8px; display: flex;"><i class="ph-fill ph-vault" style="color: #f59e0b;"></i></div>
            Aset Material (MAT): <span class="asset-val">Rp <?= number_format($totalValueRM, 0, ',', '.') ?></span>
        </div>
        <button class="btn-warning" onclick="openModal('modalRM')">
            <i class="ph-bold ph-plus-circle" style="font-size: 18px;"></i> Registrasi Material Baru
        </button>
    </div>

    <div class="table-card">
        <div style="overflow-x: auto;">
            <table>
                <thead>
                    <tr>
                        <th>Kode SKU Material</th>
                        <th>Nama Material Dasar</th>
                        <th style="text-align: right;">Harga Beli Satuan</th>
                        <th>Stok Tersedia</th>
                        <th>Tindakan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($rawMaterials)): ?>
                        <tr>
                            <td colspan="5">
                                <div class="empty-state">
                                    <i class="ph-fill ph-nut"></i>
                                    <h3 style="margin: 0 0 5px 0; color: var(--text-main); font-weight: 800; font-size: 16px;">Belum ada Bahan Baku</h3>
                                    <p>Klik tombol registrasi di sudut kanan atas.</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                    <?php foreach($rawMaterials as $rm): ?>
                    <tr>
                        <td><span class="sku-badge sku-mat"><?= esc($rm['sku_material']) ?></span></td>
                        <td style="font-weight: 800;"><?= esc($rm['material_name']) ?></td>
                        <td style="text-align: right; font-family: 'Space Mono', monospace; font-weight: 900; color: #f59e0b;">
                            Rp <?= number_format($rm['hpp'], 0, ',', '.') ?> 
                            <span style="font-size:10px; color:var(--text-muted); font-family: 'Plus Jakarta Sans', sans-serif;">/ <?= esc($rm['unit']) ?></span>
                        </td>
                        <td>
                            <div class="stock-box <?= ($rm['physical_stock'] <= $rm['min_stock']) ? 'stock-danger' : '' ?>" title="Minimum Stok: <?= $rm['min_stock'] ?>">
                                <?= floatval($rm['physical_stock']) ?> <span style="font-size:10px; margin-left:4px; font-family:'Plus Jakarta Sans', sans-serif;"><?= esc($rm['unit']) ?></span>
                            </div>
                        </td>
                        <td>
                            <a href="#" onclick="confirmDelete(event, '<?= base_url('/warehouse/delete_rm/'.$rm['id']) ?>')" class="btn-delete" title="Hapus Data">
                                <i class="ph-bold ph-trash"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal-overlay" id="modalFG">
    <div class="modal-box" style="border-top: 8px solid #3b82f6;">
        <div class="modal-header">
            <div class="modal-title">
                <div style="background: rgba(59, 130, 246, 0.1); width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; color: #3b82f6;">
                    <i class="ph-fill ph-motorcycle"></i>
                </div>
                Input Produk Jadi Baru
            </div>
            <button class="btn-close" onclick="closeModal('modalFG')" type="button"><i class="ph-bold ph-x"></i></button>
        </div>
        <form id="formFG" action="<?= base_url('/warehouse/store_fg') ?>" method="post">
            <?= csrf_field() ?>
            <div class="auto-sku-info info-blue">
                <i class="ph-fill ph-magic-wand" style="font-size: 28px;"></i> 
                <div>Sistem akan membuat SKU secara otomatis: <br><b style="font-family: monospace; font-size:14px;">PRD-0001, PRD-0002, dst.</b></div>
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
            
            <button type="submit" id="btnSubmitFG" class="btn-submit-modal btn-blue">
                <i class="ph-bold ph-floppy-disk" style="font-size: 20px;"></i> <span>Simpan Produk (PRD)</span>
            </button>
        </form>
    </div>
</div>

<div class="modal-overlay" id="modalRM">
    <div class="modal-box" style="border-top: 8px solid #f59e0b;">
        <div class="modal-header">
            <div class="modal-title">
                <div style="background: rgba(245, 158, 11, 0.1); width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; color: #f59e0b;">
                    <i class="ph-fill ph-nut"></i>
                </div>
                Input Material Mentah
            </div>
            <button class="btn-close" onclick="closeModal('modalRM')" type="button"><i class="ph-bold ph-x"></i></button>
        </div>
        <form id="formRM" action="<?= base_url('/warehouse/store_rm') ?>" method="post">
            <?= csrf_field() ?>
            <div class="auto-sku-info info-orange">
                <i class="ph-fill ph-magic-wand" style="font-size: 28px;"></i> 
                <div>Sistem akan membuat SKU secara otomatis: <br><b style="font-family: monospace; font-size:14px;">MAT-0001, MAT-0002, dst.</b></div>
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
                    <select name="unit" class="form-control focus-orange" required style="cursor: pointer;">
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
            
            <button type="submit" id="btnSubmitRM" class="btn-submit-modal btn-orange">
                <i class="ph-bold ph-floppy-disk" style="font-size: 20px;"></i> <span>Simpan Material (MAT)</span>
            </button>
        </form>
    </div>
</div>

<script>
    // --- Tab Logic ---
    function switchTab(tabName) {
        document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
        
        event.currentTarget.classList.add('active');
        document.getElementById('tab-' + tabName).classList.add('active');
    }

    // Auto-Buka Tab jika ada parameter URL
    document.addEventListener("DOMContentLoaded", function() {
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('tab') === 'rm') {
            document.querySelector('[onclick="switchTab(\'rm\')"]').click();
        }
    });

    // --- Modal Logic ---
    function openModal(modalId) { document.getElementById(modalId).classList.add('active'); }
    function closeModal(modalId) { document.getElementById(modalId).classList.remove('active'); }
    window.onclick = function(e) { 
        if (e.target.classList.contains('modal-overlay')) {
            e.target.classList.remove('active'); 
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

    // --- AJAX FORM FG (PRD) ---
    document.getElementById('formFG').addEventListener('submit', function(e) {
        e.preventDefault(); 
        const btn = document.getElementById('btnSubmitFG');
        const btnText = btn.querySelector('span');
        const btnIcon = btn.querySelector('i');
        
        btn.disabled = true;
        btnText.innerText = "Menyimpan...";
        btnIcon.className = "ph-bold ph-spinner-gap ph-spin";

        fetch(this.action, {
            method: 'POST', body: new FormData(this), headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(data => {
            if(data.status === 'success') {
                if(typeof window.showGlobalToast === 'function') window.showGlobalToast(data.message);
                this.reset();
                closeModal('modalFG');
                setTimeout(() => { window.location.href = window.location.pathname; }, 1200); 
            } else {
                if(typeof window.showGlobalToast === 'function') window.showGlobalToast(data.message, true);
                btn.disabled = false; btnText.innerText = "Simpan Produk (PRD)"; btnIcon.className = "ph-bold ph-floppy-disk";
            }
        })
        .catch(err => {
            if(typeof window.showGlobalToast === 'function') window.showGlobalToast("Koneksi Server Gagal", true);
            btn.disabled = false; btnText.innerText = "Simpan Produk (PRD)"; btnIcon.className = "ph-bold ph-floppy-disk";
        });
    });

    // --- AJAX FORM RM (MAT) ---
    document.getElementById('formRM').addEventListener('submit', function(e) {
        e.preventDefault(); 
        const btn = document.getElementById('btnSubmitRM');
        const btnText = btn.querySelector('span');
        const btnIcon = btn.querySelector('i');
        
        btn.disabled = true;
        btnText.innerText = "Menyimpan...";
        btnIcon.className = "ph-bold ph-spinner-gap ph-spin";

        fetch(this.action, {
            method: 'POST', body: new FormData(this), headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(data => {
            if(data.status === 'success') {
                if(typeof window.showGlobalToast === 'function') window.showGlobalToast(data.message);
                this.reset();
                closeModal('modalRM');
                setTimeout(() => { window.location.replace("<?= base_url('/warehouse/local-inventory') ?>?tab=rm"); }, 1200); 
            } else {
                if(typeof window.showGlobalToast === 'function') window.showGlobalToast(data.message, true);
                btn.disabled = false; btnText.innerText = "Simpan Material (MAT)"; btnIcon.className = "ph-bold ph-floppy-disk";
            }
        })
        .catch(err => {
            if(typeof window.showGlobalToast === 'function') window.showGlobalToast("Koneksi Server Gagal", true);
            btn.disabled = false; btnText.innerText = "Simpan Material (MAT)"; btnIcon.className = "ph-bold ph-floppy-disk";
        });
    });

    // Delete Confirmation
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
</script>

<?= $this->endSection() ?>