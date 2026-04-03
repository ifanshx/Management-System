<?= $this->extend('layout/template') ?>
<?= $this->section('content') ?>

<style>
    /* =========================================================
       1. VARIABLES & AMBIENT GLOW
       ========================================================= */
    :root {
        --brand-green: #10b981; --brand-green-dark: #059669;
        --brand-blue: #0ea5e9; --brand-orange: #f59e0b;
        --radius-xl: 24px; --radius-lg: 16px; --radius-md: 12px;
        --shadow-card: 0 10px 40px -15px rgba(0,0,0,0.05);
        --shadow-hover: 0 20px 40px -10px rgba(0,0,0,0.08);
        --transition-smooth: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    }

    .ambient-glow { position: absolute; top: -150px; left: 50%; transform: translateX(-50%); width: 100%; height: 500px; background: radial-gradient(ellipse at top, rgba(16, 185, 129, 0.08) 0%, transparent 60%); z-index: 0; pointer-events: none;}
    html.dark .ambient-glow { background: radial-gradient(ellipse at top, rgba(16, 185, 129, 0.15) 0%, transparent 60%); }

    /* =========================================================
       2. PAGE HEADER & PREMIUM TABS
       ========================================================= */
    .page-header { position: relative; z-index: 1; margin-bottom: 25px; display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 20px; width: 100%;}
    .page-title { display: flex; align-items: center; gap: 18px;}
    .title-icon { width: 56px; height: 56px; border-radius: 18px; background: linear-gradient(135deg, var(--brand-green), var(--brand-green-dark)); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 28px; box-shadow: 0 10px 25px -5px rgba(16, 185, 129, 0.4);}
    .title-text h1 { font-size: 30px; font-weight: 900; color: var(--text-main); margin: 0; letter-spacing: -1px; line-height: 1.1;}
    .title-text p { font-size: 14px; color: var(--text-muted); font-weight: 600; margin: 4px 0 0 0;}

    .tab-nav { position: relative; z-index: 1; display: inline-flex; background: rgba(0,0,0,0.03); padding: 6px; border-radius: 20px; border: 1px solid var(--border-subtle); margin-bottom: 30px; box-shadow: inset 0 2px 4px rgba(0,0,0,0.02);}
    html.dark .tab-nav { background: rgba(255,255,255,0.02); }
    
    .tab-btn { padding: 12px 24px; font-size: 14px; font-weight: 800; border-radius: 14px; border: none; background: transparent; color: var(--text-muted); cursor: pointer; transition: var(--transition-smooth); display: flex; align-items: center; gap: 8px;}
    .tab-btn:hover { color: var(--text-main); }
    .tab-btn.active { background: var(--bg-surface); color: var(--brand-green); box-shadow: 0 4px 15px rgba(0,0,0,0.05); border: 1px solid var(--border-subtle); }
    
    .tab-content { display: none; animation: slideFadeUp 0.4s cubic-bezier(0.16, 1, 0.3, 1); position: relative; z-index: 1; width: 100%;}
    .tab-content.active { display: block; width: 100%;}
    @keyframes slideFadeUp { from { opacity: 0; transform: translateY(15px); } to { opacity: 1; transform: translateY(0); } }

    /* =========================================================
       3. BENTO CARDS & STACKED GRID
       ========================================================= */
    .layout-stacked { display: flex; flex-direction: column; gap: 30px; width: 100%;}

    .bento-card { background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: var(--radius-xl); box-shadow: var(--shadow-card); padding: 30px; transition: var(--transition-smooth); width: 100%;}
    .bento-card:hover { border-color: rgba(16, 185, 129, 0.3); box-shadow: var(--shadow-hover);}
    
    .card-title { font-size: 16px; font-weight: 900; color: var(--text-main); margin-bottom: 25px; display: flex; align-items: center; gap: 12px; border-bottom: 2px dashed var(--border-subtle); padding-bottom: 18px;}
    .card-title i { background: rgba(16, 185, 129, 0.1); color: var(--brand-green); padding: 8px; border-radius: 10px; font-size: 20px; border: 1px solid rgba(16, 185, 129, 0.2);}

    /* =========================================================
       4. MODERN FORMS (SO BUILDER)
       ========================================================= */
    .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 20px;}
    @media (max-width: 768px) { .grid-2 { grid-template-columns: 1fr; } }

    .form-group { margin-bottom: 18px; width: 100%;}
    .form-group label { display: block; font-size: 11px; font-weight: 900; color: var(--text-muted); margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px;}
    
    .form-control, .so-select { width: 100%; background: var(--bg-base); border: 1px solid var(--border-subtle); padding: 14px 18px; border-radius: var(--radius-md); font-size: 14px; font-weight: 700; outline: none; color: var(--text-main); transition: var(--transition-smooth); cursor: pointer; appearance: none;}
    .form-control:focus, .so-select:focus { border-color: var(--brand-green); background: var(--bg-surface); box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.15);}
    .so-select { background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%2364748b' viewBox='0 0 256 256'%3E%3Cpath d='M213.66,101.66l-80,80a8,8,0,0,1-11.32,0l-80-80A8,8,0,0,1,53.66,90.34L128,164.69l74.34-74.35a8,8,0,0,1,11.32,11.32Z'%3E%3C/path%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 16px center; padding-right: 40px; }

    .input-money { display: flex; align-items: center; background: var(--bg-base); border: 1px solid var(--border-subtle); border-radius: var(--radius-md); overflow: hidden; transition: var(--transition-smooth); width: 100%;}
    .input-money:focus-within { border-color: var(--brand-green); box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.15); background: var(--bg-surface);}
    .input-money span { padding: 14px 16px; background: rgba(0,0,0,0.03); font-size: 13px; font-weight: 900; color: var(--text-muted); border-right: 1px solid var(--border-subtle);}
    .input-money input { border: none; padding: 14px 16px; font-size: 15px; font-weight: 800; width: 100%; outline: none; background: transparent; color: var(--text-main); font-family: 'Space Mono', monospace;}

    .item-row { background: var(--bg-base); border: 1px solid transparent; padding: 16px 20px; border-radius: var(--radius-lg); margin-bottom: 12px; transition: var(--transition-smooth); box-shadow: inset 0 2px 4px rgba(0,0,0,0.02); width: 100%;}
    .item-row:hover, .item-row:focus-within { border-color: var(--brand-green); background: var(--bg-surface); box-shadow: 0 8px 20px rgba(16, 185, 129, 0.08); transform: scale(1.01);}
    
    .item-grid { display: grid; grid-template-columns: 2.5fr 1fr 1.5fr auto; gap: 15px; align-items: center; width: 100%;}
    @media (max-width: 768px) { .item-grid { grid-template-columns: 1fr; } }
    
    .btn-add-row { width: 100%; background: var(--bg-surface); border: 2px dashed rgba(16, 185, 129, 0.5); color: var(--brand-green); padding: 16px; border-radius: var(--radius-lg); font-weight: 900; font-size: 14px; cursor: pointer; margin-bottom: 25px; transition: var(--transition-smooth); display: flex; align-items: center; justify-content: center; gap: 8px;}
    .btn-add-row:hover { background: rgba(16, 185, 129, 0.05); border-color: var(--brand-green); transform: translateY(-2px);}
    
    .btn-remove-row { background: rgba(239, 68, 68, 0.1); color: #ef4444; border: 1px solid transparent; width: 46px; height: 46px; border-radius: 12px; font-size: 22px; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: var(--transition-smooth);}
    .btn-remove-row:hover { background: #ef4444; color: #fff; transform: scale(1.1) rotate(5deg); box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);}

    .live-total-box { background: linear-gradient(135deg, #0f172a, #1e293b); border: 1px solid #334155; padding: 24px 30px; border-radius: var(--radius-lg); display: flex; justify-content: space-between; align-items: center; box-shadow: 0 15px 30px -10px rgba(0,0,0,0.3); position: relative; overflow: hidden; width: 100%;}
    .live-total-box::after { content: ''; position: absolute; right: -20px; top: -20px; width: 100px; height: 100px; background: radial-gradient(circle, rgba(16,185,129,0.2) 0%, transparent 70%); }
    .live-total-val { font-size: 36px; font-weight: 900; color: #10b981; font-family: 'Space Mono', monospace; line-height: 1; letter-spacing: -1.5px; text-shadow: 0 0 15px rgba(16, 185, 129, 0.4);}

    .btn-submit { width: 100%; background: linear-gradient(135deg, #10b981, #059669); color: #fff; border: none; padding: 18px 24px; border-radius: var(--radius-lg); font-size: 16px; font-weight: 900; cursor: pointer; transition: var(--transition-smooth); display: flex; justify-content: center; align-items: center; gap: 10px; box-shadow: 0 8px 25px -5px rgba(16, 185, 129, 0.5); margin-top: 25px;}
    .btn-submit:hover { transform: translateY(-3px); box-shadow: 0 15px 30px -5px rgba(16, 185, 129, 0.6);}

    /* =========================================================
       5. FULL WIDTH ANALYTICAL TABLE DESIGN
       ========================================================= */
    .table-responsive { width: 100%; overflow-x: auto; }
    table { width: 100%; border-collapse: separate; border-spacing: 0; white-space: nowrap; }
    th { text-align: left; padding: 18px 20px; font-size: 11px; font-weight: 900; text-transform: uppercase; color: var(--text-muted); background: var(--bg-base); border-bottom: 2px solid var(--border-subtle); letter-spacing: 0.5px;}
    td { text-align: left; padding: 18px 20px; border-bottom: 1px dashed var(--border-subtle); color: var(--text-main); font-size: 14px; font-weight: 700; vertical-align: middle; transition: var(--transition-smooth);}
    
    tr:last-child td { border-bottom: none; }
    tr:hover td { background: rgba(16, 185, 129, 0.03); }

    .action-group { display: flex; justify-content: center; gap: 8px; flex-wrap: wrap; }
    .btn-rincian { background: var(--bg-base); border: 1px solid var(--border-subtle); color: var(--brand-blue); font-size: 12px; font-weight: 800; padding: 8px 14px; border-radius: 10px; display: inline-flex; align-items: center; gap: 6px; cursor: pointer; transition: var(--transition-smooth);}
    .btn-rincian:hover { background: var(--brand-blue); color: #fff; border-color: var(--brand-blue); transform: translateY(-2px); box-shadow: 0 4px 12px rgba(14, 165, 233, 0.3);}
    
    .btn-sj { background: var(--bg-base); border: 1px solid var(--border-subtle); color: var(--brand-orange); font-size: 12px; font-weight: 800; padding: 8px 14px; border-radius: 10px; display: inline-flex; align-items: center; gap: 6px; cursor: pointer; transition: var(--transition-smooth); text-decoration: none;}
    .btn-sj:hover { background: var(--brand-orange); color: #fff; border-color: var(--brand-orange); transform: translateY(-2px); box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);}
    
    .pay-form { display: flex; align-items: center; background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: 10px; overflow: hidden; transition: 0.3s; margin: 0 auto; width: fit-content; box-shadow: inset 0 2px 4px rgba(0,0,0,0.02);}
    .pay-form:focus-within { border-color: var(--brand-green); box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.15);}
    .pay-form span { font-size: 11px; font-weight: 900; color: var(--text-muted); padding: 8px 12px; background: rgba(0,0,0,0.02); border-right: 1px solid var(--border-subtle);}
    .pay-form input { border: none; background: transparent; padding: 8px 12px; font-size: 13px; font-weight: 800; width: 110px; outline: none; font-family: 'Space Mono', monospace; color: var(--text-main);}
    .pay-btn-small { background: rgba(16, 185, 129, 0.1); border: none; border-left: 1px solid var(--border-subtle); color: var(--brand-green); padding: 8px 16px; font-weight: 900; font-size: 14px; cursor: pointer; transition: var(--transition-smooth);}
    .pay-btn-small:hover { background: var(--brand-green); color: #fff;}

    .progress-wrapper { width: 100%; background: var(--border-subtle); border-radius: 100px; height: 8px; overflow: hidden; margin: 10px 0; box-shadow: inset 0 1px 2px rgba(0,0,0,0.1);}
    .progress-bar { height: 100%; border-radius: 100px; transition: width 0.8s cubic-bezier(0.16, 1, 0.3, 1);}
    
    .status-badge { padding: 6px 12px; border-radius: 8px; font-size: 11px; font-weight: 900; text-transform: uppercase; letter-spacing: 0.5px; display: inline-block; border: 1px dashed;}
    .s-pending { background: rgba(239, 68, 68, 0.05); color: #ef4444; border-color: rgba(239, 68, 68, 0.3);}
    .s-partial { background: rgba(245, 158, 11, 0.05); color: #d97706; border-color: rgba(245, 158, 11, 0.3);}
    .s-paid { background: rgba(16, 185, 129, 0.05); color: #10b981; border-color: rgba(16, 185, 129, 0.3);}
    .s-returned { background: rgba(99, 102, 241, 0.08); color: #6366f1; border-color: rgba(99, 102, 241, 0.25); }

    .empty-state { text-align: center; padding: 60px 20px; color: var(--text-muted); width: 100%;}
    .empty-state i { font-size: 64px; color: var(--border-subtle); margin-bottom: 20px; display: block; }
    .empty-state h3 { font-size: 18px; font-weight: 900; color: var(--text-main); margin-bottom: 8px; letter-spacing: -0.5px;}

    /* =========================================================
       6. MODALS & PRINT MEDIA
       ========================================================= */
    .modal-overlay { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.6); backdrop-filter: blur(8px); z-index: 1000; display: none; justify-content: center; align-items: center; opacity: 0; transition: opacity 0.3s;}
    .modal-overlay.active { display: flex; opacity: 1; }
    
    .modal-box { background: var(--bg-surface); border-radius: 28px; width: 100%; max-width: 500px; padding: 40px; transform: scale(0.95) translateY(20px); transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1); box-shadow: 0 30px 60px -15px rgba(0,0,0,0.4); border: 1px solid var(--border-subtle);}
    .modal-overlay.active .modal-box { transform: scale(1) translateY(0); }
    
    .modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
    .btn-close { background: var(--bg-base); border: 1px solid var(--border-subtle); width: 38px; height: 38px; border-radius: 50%; font-size: 18px; color: var(--text-muted); cursor: pointer; display: flex; align-items: center; justify-content: center; transition: 0.2s;}
    .btn-close:hover { background: #ef4444; color: #fff; border-color: #ef4444; transform: rotate(90deg);}

    .receipt-paper { background: #ffffff; color: #000; padding: 35px 30px; font-family: 'Space Mono', monospace; box-shadow: 0 15px 35px rgba(0,0,0,0.15); max-height: 600px; overflow-y: auto; position: relative; width: 100%;}
    .receipt-paper::before, .receipt-paper::after { content: ""; position: absolute; left: 0; right: 0; height: 6px; background-size: 12px 100%; z-index: 10;}
    .receipt-paper::before { top: 0; background-image: linear-gradient(135deg, var(--bg-surface) 25%, transparent 25%), linear-gradient(225deg, var(--bg-surface) 25%, transparent 25%); background-position: 0 0; }
    .receipt-paper::after { bottom: 0; background-image: linear-gradient(315deg, var(--bg-surface) 25%, transparent 25%), linear-gradient(45deg, var(--bg-surface) 25%, transparent 25%); background-position: 0 0; }

    @media print {
        body * { visibility: hidden; }
        #printArea, #printArea * { visibility: visible; color: #000 !important; }
        #printArea { position: absolute; left: 0; top: 0; width: 100%; max-width: 100%; padding: 0; background: #fff !important; border: none; box-shadow: none;}
        #printArea::before, #printArea::after { display: none; }
        .modal-overlay { background: transparent; }
        .modal-box { box-shadow: none; border: none; padding: 0; }
        .no-print { display: none !important; }
    }
</style>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="ambient-glow no-print"></div>

<div class="page-header no-print">
    <div class="page-title">
        <div class="title-icon"><i class="ph-fill ph-handshake"></i></div>
        <div class="title-text">
            <h1>B2B Wholesale & Piutang</h1>
            <p>Kelola pesanan partai besar (Grosir/Reseller) dan pantau pelunasan tagihan.</p>
        </div>
    </div>
</div>

<div class="tab-nav no-print">
    <button class="tab-btn active" onclick="switchTab('so')"><i class="ph-bold ph-receipt"></i> Pesanan Grosir (SO)</button>
    <button class="tab-btn" onclick="switchTab('reseller')"><i class="ph-bold ph-users-three"></i> Master Data Reseller</button>
</div>

<div id="tab-so" class="tab-content active no-print">
    <div class="layout-stacked">
        
        <div class="bento-card" style="border-top: 6px solid var(--brand-green);">
            <div class="card-title">
                <i class="ph-fill ph-shopping-cart"></i> Terbitkan Pesanan Baru
            </div>
            
            <form action="<?= base_url('/wholesale/store_so') ?>" method="post" style="width: 100%;">
                <?= csrf_field() ?>
                
                <div class="grid-2">
                    <div class="form-group">
                        <label>Pilih Mitra Reseller</label>
                        <div class="input-wrapper" style="border-color: rgba(16, 185, 129, 0.4);">
                            <div style="padding: 0 16px; color: var(--brand-green);"><i class="ph-fill ph-storefront" style="font-size: 20px;"></i></div>
                            <select name="customer_id" class="so-select" required style="border: none; background: transparent; padding-left: 0;">
                                <option value="">-- Cari Toko / Bengkel --</option>
                                <?php foreach($customers as $c): ?>
                                    <option value="<?= $c['id'] ?>"><?= esc($c['company_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Jenis Eksekusi Pesanan</label>
                        <div class="input-wrapper" style="border-color: rgba(14, 165, 233, 0.4);">
                            <div style="padding: 0 16px; color: var(--brand-blue);"><i class="ph-fill ph-package" style="font-size: 20px;"></i></div>
                            <select name="order_type" class="so-select" required style="border: none; background: transparent; padding-left: 0;">
                                <option value="READY">Ready Stock (Ambil dari Gudang)</option>
                                <option value="PREORDER">Pre-Order / Custom (Masuk Antrean Pabrik)</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div style="margin: 15px 0; border-top: 2px dashed var(--border-subtle); padding-top: 25px;">
                    <label style="font-size: 12px; font-weight: 900; color: var(--text-main); margin-bottom: 15px; display: flex; align-items: center; gap: 8px;">
                        <div style="background: rgba(16, 185, 129, 0.1); color: var(--brand-green); padding: 6px; border-radius: 8px;"><i class="ph-fill ph-list-numbers" style="font-size: 18px;"></i></div> 
                        Daftar Barang Pesanan
                    </label>
                    
                    <div id="item-container">
                        <div class="item-row">
                            <div class="item-grid">
                                <select name="fg_sku[]" class="form-control so-select" required onchange="autoFillPrice(this)">
                                    <option value="" data-price="0">-- Pilih Produk Katalog (PRD) --</option>
                                    <?php foreach($products as $p): ?>
                                        <?php $wholesalePrice = !empty($p['wholesale_price']) ? $p['wholesale_price'] : $p['hpp']; ?>
                                        <option value="<?= $p['sku'] ?>" data-price="<?= $wholesalePrice ?>">
                                            [Sisa Stok: <?= $p['physical_stock'] ?>] <?= esc($p['item_name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                
                                <input type="number" name="qty[]" class="form-control so-qty" placeholder="Kuantitas" required min="1" oninput="calcSoTotal()" style="text-align: center; font-family: 'Space Mono', monospace; font-size: 16px;">
                                
                                <div class="input-money">
                                    <span>Rp</span>
                                    <input type="text" inputmode="numeric" name="unit_price[]" class="so-price" placeholder="Harga Satuan" required oninput="formatRupiah(this); calcSoTotal();">
                                </div>
                                
                                <button type="button" class="btn-remove-row" onclick="this.parentElement.parentElement.remove(); calcSoTotal();" title="Hapus"><i class="ph-bold ph-trash"></i></button>
                            </div>
                        </div>
                    </div>

                    <button type="button" class="btn-add-row" onclick="addSoRow()">
                        <i class="ph-bold ph-plus-circle" style="font-size: 20px;"></i> Tambah Baris Produk
                    </button>
                </div>

                <div class="grid-2" style="margin-top: 30px; align-items: end;">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <div class="form-group" style="margin-bottom: 0;">
                            <label>Uang Muka / DP Awal (Rp)</label>
                            <div class="input-money" style="border-color: rgba(245, 158, 11, 0.4);">
                                <span style="color: var(--brand-orange);"><i class="ph-bold ph-wallet"></i></span>
                                <input type="text" inputmode="numeric" name="dp_amount" value="0" required oninput="formatRupiah(this)">
                            </div>
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <label>Tenggat Waktu / Tempo</label>
                            <input type="date" name="due_date" class="form-control" required style="font-family: 'Space Mono', monospace;">
                        </div>
                    </div>
                    
                    <div class="live-total-box">
                        <div>
                            <div style="font-size: 11px; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 4px;">Estimasi Grand Total</div>
                            <div style="font-size: 10px; color: #64748b;"><i class="ph-fill ph-info"></i> Kalkulasi real-time</div>
                        </div>
                        <div class="live-total-val" id="soGrandTotal">Rp 0</div>
                    </div>
                </div>
                
                <button type="submit" class="btn-submit">
                    <i class="ph-bold ph-paper-plane-tilt" style="font-size: 20px;"></i> Terbitkan Dokumen SO
                </button>
            </form>
        </div>

        <div class="bento-card">
            <div class="card-title">
                <i class="ph-fill ph-list-dashes"></i> Riwayat Tagihan & Transaksi B2B
            </div>
            
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Identitas Pesanan</th>
                            <th>Mitra Reseller</th>
                            <th style="min-width: 220px; text-align: center;">Progres Pelunasan</th>
                            <th style="text-align: center;">Aksi Cepat</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($salesOrders)): ?>
                            <tr>
                                <td colspan="4">
                                    <div class="empty-state">
                                        <i class="ph-fill ph-receipt"></i>
                                        <h3>Belum Ada Riwayat Penjualan Grosir</h3>
                                        <p>Buat pesanan pertama Anda melalui formulir di atas.</p>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach($salesOrders as $so): 
                                $isReturned = ($so['status'] ?? '') === 'RETURNED';
                                $percent = ($so['total_amount'] > 0) ? ($so['paid_amount'] / $so['total_amount']) * 100 : 0;
                                $barColor = ($percent == 100) ? 'var(--brand-green)' : (($percent > 0) ? 'var(--brand-orange)' : '#ef4444');
                                $isOverdue = (strtotime($so['due_date']) < time() && $so['status'] != 'PAID'); 
                            ?>
                            <tr>
                                <td style="white-space: normal;">
                                    <div style="font-family: 'Space Mono', monospace; font-weight: 900; color: <?= $isReturned ? '#6366f1' : 'var(--brand-green)' ?>; font-size: 13px; background: rgba(16, 185, 129, 0.1); padding: 6px 10px; border-radius: 8px; display: inline-block; margin-bottom: 8px; border: 1px dashed rgba(16, 185, 129, 0.3);">
                                        <?= esc($so['so_number']) ?>
                                    </div>
                                    <div style="font-size: 11px; color: <?= $isOverdue ? '#ef4444' : 'var(--text-muted)' ?>; font-weight: 800; display: flex; align-items: center; gap: 6px;">
                                        <i class="<?= $isOverdue ? 'ph-fill ph-warning-circle' : 'ph-bold ph-calendar-blank' ?>" style="font-size: 14px;"></i> 
                                        Tempo: <?= date('d M Y', strtotime($so['due_date'])) ?>
                                    </div>

                                    <?php if (!empty($so['returns'])): ?>
                                        <div style="margin-top:8px; display:flex; flex-direction:column; gap:4px;">
                                            <?php foreach ($so['returns'] as $ret): ?>
                                                <span class="status-badge s-returned" style="font-size: 9px;">
                                                    <i class="ph-bold ph-arrow-u-down-left"></i> Retur: <?= esc($ret['return_number']) ?> (-Rp <?= number_format($ret['total_return'], 0, ',', '.') ?>)
                                                </span>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div style="font-weight: 900; display:flex; align-items:center; gap:10px; font-size: 14px; color: var(--text-main);">
                                        <div style="background: var(--bg-input); padding: 8px; border-radius: 10px; border: 1px solid var(--border-subtle); display: flex;"><i class="ph-fill ph-storefront" style="color: var(--text-muted);"></i></div>
                                        <?= esc($so['company_name']) ?>
                                    </div>
                                </td>
                                <td>
                                    <div style="display: flex; justify-content: space-between; font-size: 12px; font-weight: 900; font-family: 'Space Mono', monospace;">
                                        <span style="color: var(--text-main);">Rp <?= number_format($so['paid_amount'],0,',','.') ?></span>
                                        <span style="color: var(--text-muted);">Rp <?= number_format($so['total_amount'],0,',','.') ?></span>
                                    </div>
                                    <div class="progress-wrapper">
                                        <div class="progress-bar" style="width: <?= $percent ?>%; background-color: <?= $barColor ?>;"></div>
                                    </div>
                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 8px;">
                                        <?php
                                            $statusClass = 's-pending';
                                            if ($so['status'] === 'PAID') $statusClass = 's-paid';
                                            elseif ($so['status'] === 'PARTIAL') $statusClass = 's-partial';
                                            elseif ($so['status'] === 'RETURNED') $statusClass = 's-returned';
                                        ?>
                                        <span class="status-badge <?= $statusClass ?>">
                                            <?= esc($so['status']) ?> (<?= round($percent, 1) ?>%)
                                        </span>
                                        
                                        <?php if($so['status'] != 'PAID' && $so['status'] != 'RETURNED'): ?>
                                            <form action="<?= base_url('/wholesale/pay_installment/'.$so['id']) ?>" method="post" class="pay-form">
                                                <?= csrf_field() ?>
                                                <span>Rp</span>
                                                <input type="text" inputmode="numeric" name="amount" placeholder="Nominal" required oninput="formatRupiah(this)">
                                                <button type="submit" class="pay-btn-small" title="Bayar Cicilan"><i class="ph-bold ph-wallet"></i></button>
                                            </form>
                                        <?php elseif($so['status'] == 'PAID'): ?>
                                            <span style="color: var(--brand-green); font-weight: 900; font-size: 12px; display: flex; align-items: center; gap: 4px;"><i class="ph-fill ph-check-circle" style="font-size: 16px;"></i> FULL PAID</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="action-group">
                                        <?php if(!$isReturned && isset($so['shipping_status']) && $so['shipping_status'] == 'PRE-ORDER'): ?>
                                            <button type="button" class="btn-rincian" style="color: #8b5cf6; border-color: rgba(139, 92, 246, 0.3); background: rgba(139, 92, 246, 0.05);" onclick="openConfirmModal(event, '<?= base_url('/wholesale/ship_preorder/'.$so['id']) ?>', 'ship')" title="Kirim Barang Pre-Order">
                                                <i class="ph-bold ph-paper-plane-tilt"></i> Kirim
                                            </button>
                                        <?php endif; ?>

                                        <?php if($so['status'] != 'RETURNED'): ?>
                                            <button class="btn-sj" type="button" onclick="openReturnModal(<?= $so['id'] ?>)">
                                                <i class="ph-bold ph-arrow-u-down-left"></i> Retur
                                            </button>
                                        <?php endif; ?>

                                        <button type="button" class="btn-rincian" 
                                                data-so="<?= esc($so['so_number']) ?>"
                                                data-cust="<?= esc($so['company_name']) ?>"
                                                data-date="<?= date('d M Y', strtotime($so['order_date'])) ?>"
                                                data-status="<?= $so['status'] ?>"
                                                data-total="<?= $so['total_amount'] ?>"
                                                data-paid="<?= $so['paid_amount'] ?>"
                                                data-items="<?= htmlspecialchars(json_encode($so['items'] ?? []), ENT_QUOTES, 'UTF-8') ?>" 
                                                onclick="openReceiptModal(this)" title="Cetak Struk Transaksi">
                                            <i class="ph-bold ph-receipt"></i> Struk
                                        </button>

                                        <a href="<?= base_url('/wholesale/surat_jalan/'.$so['id']) ?>" target="_blank" class="btn-sj" title="Cetak Surat Jalan Pengiriman">
                                            <i class="ph-bold ph-truck"></i> SJ
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div id="tab-reseller" class="tab-content no-print">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; flex-wrap: wrap; gap: 15px; width: 100%;">
        <div style="font-size: 13px; font-weight: 800; color: var(--text-muted); background: var(--bg-surface); padding: 14px 24px; border-radius: 16px; border: 1px solid var(--border-subtle); display: flex; align-items: center; gap: 10px; box-shadow: var(--shadow-sm);">
            <div style="background: rgba(16, 185, 129, 0.1); padding: 6px; border-radius: 8px; color: var(--brand-green);"><i class="ph-fill ph-users-three" style="font-size: 20px;"></i></div>
            Total Mitra Terdaftar: <span style="color: var(--text-main); font-size: 18px; font-weight: 900; font-family: 'Space Mono', monospace;"><?= count($customers) ?></span>
        </div>
        <button class="btn-submit" style="width: auto; padding: 14px 24px; margin: 0;" onclick="openModal('modalReseller')">
            <i class="ph-bold ph-plus-circle" style="font-size: 20px;"></i> Tambah Mitra Baru
        </button>
    </div>

    <div class="bento-card">
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Nama Toko / Perusahaan</th>
                        <th>Kontak Utama (PIC)</th>
                        <th style="text-align: center;">Telepon / WA</th>
                        <th>Alamat Lengkap Pengiriman</th>
                        <th style="text-align: center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($customers)): ?>
                        <tr>
                            <td colspan="5">
                                <div class="empty-state">
                                    <i class="ph-fill ph-storefront"></i>
                                    <h3>Belum Ada Data Mitra Reseller</h3>
                                    <p>Klik tombol "Tambah Mitra Baru" di kanan atas untuk mendaftarkan agen Anda.</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach($customers as $c): ?>
                            <tr>
                                <td style="font-weight: 900; color: var(--text-main); font-size: 15px;">
                                    <div style="display: flex; align-items: center; gap: 12px;">
                                        <div style="width: 40px; height: 40px; border-radius: 12px; background: rgba(16, 185, 129, 0.1); color: var(--brand-green); display: flex; align-items: center; justify-content: center; font-size: 20px; border: 1px solid rgba(16, 185, 129, 0.2);"><i class="ph-fill ph-storefront"></i></div>
                                        <?= esc($c['company_name']) ?>
                                    </div>
                                </td>
                                <td>
                                    <div style="font-weight: 800; display:flex; align-items:center; gap:8px; font-size: 14px; color: var(--text-muted);">
                                        <i class="ph-fill ph-user-circle" style="font-size: 20px; color: var(--border-subtle);"></i> <?= esc($c['contact_name'] ?? '-') ?>
                                    </div>
                                </td>
                                <td style="text-align: center;">
                                    <div style="font-family: 'Space Mono', monospace; font-size: 14px; font-weight: 800; color: var(--brand-green); background: rgba(16, 185, 129, 0.05); padding: 6px 12px; border-radius: 10px; display: inline-block; border: 1px dashed rgba(16, 185, 129, 0.4);">
                                        <?= esc($c['phone'] ?? '-') ?>
                                    </div>
                                </td>
                                <td style="white-space: normal; line-height: 1.6; font-size: 13px; color: var(--text-muted); max-width: 300px; font-weight: 600;">
                                    <?= esc($c['address'] ?? '-') ?>
                                </td>
                                <td style="text-align: center;">
                                    <button type="button" onclick="openConfirmModal(event, '<?= base_url('/wholesale/delete_customer/'.$c['id']) ?>', 'delete')" class="btn-detail" style="color:#ef4444; background:rgba(239,68,68,0.1); border-color:rgba(239,68,68,0.2);" title="Hapus Data">
                                        <i class="ph-bold ph-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal-overlay no-print" id="modalReseller">
    <div class="modal-box" style="border-top: 8px solid var(--brand-green);">
        <div class="modal-header">
            <div style="font-size: 22px; font-weight: 900; color: var(--text-main); display: flex; align-items: center; gap: 14px; letter-spacing: -0.5px;">
                <div style="background: rgba(16, 185, 129, 0.1); width: 48px; height: 48px; border-radius: 14px; display: flex; align-items: center; justify-content: center; color: var(--brand-green);">
                    <i class="ph-fill ph-users-three" style="font-size: 26px;"></i>
                </div>
                Registrasi Mitra Baru
            </div>
            <button class="btn-close" onclick="closeModal('modalReseller')" type="button"><i class="ph-bold ph-x"></i></button>
        </div>
        <form action="<?= base_url('/wholesale/store_customer') ?>" method="post" style="width: 100%;">
            <?= csrf_field() ?>
            <div class="form-group">
                <label>Nama Toko / Perusahaan</label>
                <div class="input-money" style="padding: 0; background: var(--bg-base);">
                    <span style="border-right: none; padding-right: 0;"><i class="ph-bold ph-storefront" style="font-size: 18px;"></i></span>
                    <input type="text" name="company_name" required autocomplete="off" placeholder="Cth: Bengkel Motor Jaya" style="font-family: 'Plus Jakarta Sans', sans-serif;">
                </div>
            </div>
            <div class="grid-2">
                <div class="form-group">
                    <label>Nama Pemilik / PIC</label>
                    <input type="text" name="contact_name" class="form-control" autocomplete="off" placeholder="Cth: Bpk. Joko">
                </div>
                <div class="form-group">
                    <label>No. WhatsApp (Aktif)</label>
                    <input type="text" name="phone" class="form-control" autocomplete="off" placeholder="Cth: 0812..." style="font-family: 'Space Mono', monospace;">
                </div>
            </div>
            <div class="form-group">
                <label>Alamat Lengkap (Tujuan Ekspedisi)</label>
                <textarea name="address" class="form-control" rows="3" style="resize: none; line-height: 1.5; padding: 14px 18px;" placeholder="Tuliskan jalan, RT/RW, dan kota..."></textarea>
            </div>
            <button type="submit" class="btn-submit" style="margin-top: 25px;">
                <i class="ph-bold ph-floppy-disk" style="font-size: 22px;"></i> Simpan Data Mitra
            </button>
        </form>
    </div>
</div>

<div class="modal-overlay" id="returnModal">
    <div class="modal-box" style="max-width: 820px; border-top: 8px solid #6366f1;">
        <div class="modal-header">
            <div>
                <h3 style="margin:0; font-size:24px; font-weight:900; color:var(--text-main); display:flex; align-items:center; gap:10px;"><i class="ph-bold ph-arrow-u-down-left" style="color: #6366f1;"></i> Retur Penjualan</h3>
                <p style="margin:6px 0 0; color:var(--text-muted); font-size:13px;">Proses retur parsial item dari pelanggan secara akurat.</p>
            </div>
            <button type="button" class="btn-close" onclick="closeReturnModal()">&times;</button>
        </div>

        <form id="returnForm" method="post">
            <?= csrf_field() ?>

            <div class="grid-2">
                <div class="form-group">
                    <label>Tanggal Retur</label>
                    <input type="date" name="return_date" class="form-control" value="<?= date('Y-m-d') ?>" required style="font-family: 'Space Mono', monospace;">
                </div>
                <div class="form-group">
                    <label>Jenis Pengembalian Dana</label>
                    <select name="refund_type" class="so-select" required>
                        <option value="REDUCE_RECEIVABLE">Kurangi Tagihan Piutang</option>
                        <option value="CASH_REFUND">Refund Uang Tunai/Transfer</option>
                        <option value="CUSTOMER_CREDIT">Simpan Jadi Saldo Pelanggan</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label>Alasan Retur</label>
                <textarea name="reason" class="form-control" rows="2" placeholder="Contoh: Barang cacat, salah kirim, ukuran tidak sesuai." required></textarea>
            </div>

            <div class="form-group">
                <label>Pilih Item & Kuantitas Retur</label>
                <div id="returnItemsContainer" style="display:grid; gap:12px; max-height:300px; overflow-y:auto; padding-right:5px;"></div>
            </div>

            <div class="live-total-box" style="margin-top:20px; background: linear-gradient(135deg, #312e81, #4338ca); border-color: #3730a3;">
                <div>
                    <div style="font-size:11px; color:#c7d2fe; font-weight:800; text-transform:uppercase; letter-spacing:1px;">Total Nilai Retur</div>
                    <div style="color:#a5b4fc; font-size:10px; font-weight:700; margin-top:4px;"><i class="ph-fill ph-info"></i> Kalkulasi otomatis</div>
                </div>
                <div class="live-total-val" id="returnGrandTotal" style="color: #fff;">Rp 0</div>
            </div>

            <button type="submit" class="btn-submit" style="background: linear-gradient(135deg, #6366f1, #4f46e5); box-shadow: 0 8px 25px -5px rgba(99, 102, 241, 0.5);">
                <i class="ph-bold ph-check-circle"></i> Proses & Jurnal Retur Penjualan
            </button>
        </form>
    </div>
</div>

<div class="modal-overlay no-print" id="modalConfirm">
    <div class="modal-box" style="max-width: 420px; text-align: center; padding: 50px 40px;">
        <div id="confirmIconWrap" style="width: 86px; height: 86px; border-radius: 50%; background: rgba(239, 68, 68, 0.15); color: #ef4444; border: 4px solid var(--bg-surface); box-shadow: 0 0 0 2px rgba(239, 68, 68, 0.3); display: flex; align-items: center; justify-content: center; font-size: 44px; margin: 0 auto 25px auto;">
            <i class="ph-fill ph-paper-plane-tilt"></i>
        </div>
        <h2 id="confirmTitle" style="font-size: 24px; font-weight: 900; color: var(--text-main); margin-bottom: 12px; letter-spacing: -0.5px;">Kirim Pre-Order?</h2>
        <p id="confirmDesc" style="font-size: 15px; color: var(--text-muted); line-height: 1.6; margin-bottom: 35px; font-weight: 500;">Pastikan tim Manufaktur sudah selesai merakit. Sistem akan otomatis memotong stok Gudang.</p>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
            <button type="button" onclick="closeModal('modalConfirm')" class="btn-secondary" style="justify-content: center; padding: 16px;">Batalkan</button>
            <a href="#" id="confirmBtnYes" class="btn-submit" style="background: linear-gradient(135deg, #8b5cf6, #6d28d9); box-shadow: 0 8px 20px -5px rgba(139, 92, 246, 0.5); justify-content: center; text-decoration: none; padding: 16px; margin: 0;">Ya, Kirim</a>
        </div>
    </div>
</div>

<div class="modal-overlay" id="modalReceipt">
    <div class="modal-box" style="max-width: 420px; padding: 0; background: transparent; box-shadow: none; border: none;">
        <div class="no-print" style="margin-bottom: 15px; display: flex; justify-content: flex-end;">
            <button type="button" onclick="closeModal('modalReceipt')" class="btn-close" style="background: var(--bg-surface); width: 44px; height: 44px; font-size: 20px;"><i class="ph-bold ph-x"></i></button>
        </div>

        <div class="receipt-paper" id="printArea">
            <div style="text-align: center; margin-bottom: 30px;">
                <h2 style="margin: 0; font-size: 26px; font-weight: 900; letter-spacing: -1.5px; border-bottom: 2px dashed #000; padding-bottom: 15px; margin-bottom: 15px;">NORIC EXHAUST</h2>
                <div style="font-size: 13px; font-family: sans-serif; font-weight: 800; letter-spacing: 1px;">INVOICE GROSIR (B2B)</div>
            </div>

            <div style="font-size: 14px; margin-bottom: 25px; line-height: 2; font-weight: 600;">
                <div style="display: flex; justify-content: space-between;"><span>No. Tagihan:</span><b id="r_so"></b></div>
                <div style="display: flex; justify-content: space-between;"><span>Tanggal:</span><b id="r_date"></b></div>
                <div style="display: flex; justify-content: space-between;"><span>Kepada:</span><b id="r_cust"></b></div>
                <div style="display: flex; justify-content: space-between;"><span>Status:</span><b id="r_status"></b></div>
            </div>

            <div style="border-top: 2px dashed #000; border-bottom: 2px dashed #000; padding: 15px 0; margin-bottom: 25px;">
                <table style="width: 100%; font-size: 14px; border: none;">
                    <tbody id="r_items"></tbody>
                </table>
            </div>

            <div style="font-size: 15px; line-height: 2; font-weight: 600;">
                <div style="display: flex; justify-content: space-between;">
                    <span>Grand Total</span>
                    <span id="r_total" style="font-weight: 900;"></span>
                </div>
                <div style="display: flex; justify-content: space-between;">
                    <span>Telah Dibayar</span>
                    <span id="r_paid"></span>
                </div>
                <div style="display: flex; justify-content: space-between; font-weight: 900; margin-top: 15px; border-top: 3px solid #000; padding-top: 15px; font-size: 18px;">
                    <span>Sisa Tagihan</span>
                    <span id="r_sisa"></span>
                </div>
            </div>

            <div style="text-align: center; font-size: 12px; margin-top: 50px; font-family: sans-serif; line-height: 1.6;">
                <b>Terima kasih atas kepercayaan Anda</b><br>
                <span style="color: #444; font-weight: 600;">Dicetak otomatis oleh Sistem ERP Noric</span>
            </div>
        </div>

        <div class="no-print" style="margin-top: 20px;">
            <button type="button" onclick="window.print()" class="btn-submit" style="background: linear-gradient(135deg, #10b981, #059669); padding: 20px; font-size: 18px; box-shadow: 0 10px 30px rgba(16, 185, 129, 0.4);">
                <i class="ph-bold ph-printer" style="font-size: 24px;"></i> Cetak Struk Sekarang
            </button>
        </div>
    </div>
</div>

<script>
    // Data Global dari PHP (Untuk Modals)
    const salesOrdersData = <?= json_encode($salesOrders) ?>;
    const prodData = <?= json_encode($products) ?>;

    // Tab Navigation
    function switchTab(tabName) {
        document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
        event.currentTarget.classList.add('active');
        document.getElementById('tab-' + tabName).classList.add('active');
    }

    // Modal Display Logic
    function openModal(modalId) { document.getElementById(modalId).classList.add('active'); }
    function closeModal(modalId) { document.getElementById(modalId).classList.remove('active'); }
    
    // Dynamic Confirm Modal for Ship/Delete
    function openConfirmModal(event, actionUrl, type = 'delete') {
        event.preventDefault(); 
        
        const modal = document.getElementById('modalConfirm');
        const title = document.getElementById('confirmTitle');
        const desc = document.getElementById('confirmDesc');
        const iconWrap = document.getElementById('confirmIconWrap');
        const btnYes = document.getElementById('confirmBtnYes');

        if(type === 'ship') {
            iconWrap.style.background = 'rgba(139, 92, 246, 0.15)';
            iconWrap.style.color = '#8b5cf6';
            iconWrap.style.boxShadow = '0 0 0 2px rgba(139, 92, 246, 0.3)';
            iconWrap.innerHTML = '<i class="ph-fill ph-paper-plane-tilt"></i>';
            title.innerText = 'Kirim Knalpot Pre-Order?';
            desc.innerText = 'Pastikan tim Manufaktur sudah selesai merakit. Sistem akan otomatis memotong stok Gudang dan menjurnal HPP.';
            btnYes.style.background = 'linear-gradient(135deg, #8b5cf6, #6d28d9)';
            btnYes.style.boxShadow = '0 8px 20px -5px rgba(139, 92, 246, 0.5)';
            btnYes.innerHTML = 'Ya, Kirim Sekarang';
        }

        btnYes.href = actionUrl;
        modal.classList.add('active');
    }

    window.onclick = function(e) {
        if (e.target.classList.contains('modal-overlay')) {
            e.target.classList.remove('active');
        }
    }

    // --- FORMAT RUPIAH ---
    function formatRupiah(inputElement) {
        let value = inputElement.value.replace(/[^,\d]/g, '');
        let split = value.split(',');
        let sisa = split[0].length % 3;
        let rupiah = split[0].substr(0, sisa);
        let ribuan = split[0].substr(sisa).match(/\d{3}/gi);

        if (ribuan) {
            let separator = sisa ? '.' : '';
            rupiah += separator + ribuan.join('.');
        }

        rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
        inputElement.value = rupiah;
    }

    function parseRupiah(formattedString) {
        if(!formattedString) return 0;
        return parseFloat(formattedString.replace(/\./g, '').replace(',', '.')) || 0;
    }
    
    function formatRupiahJS(num) {
        return 'Rp ' + new Intl.NumberFormat('id-ID').format(num || 0);
    }

    // INTERCEPTOR SUBMIT: Bersihkan titik & Loading sebelum dikirim ke backend!
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!this.checkValidity()) {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning', title: 'Data Belum Lengkap', text: 'Mohon periksa kembali form Anda.',
                    background: document.documentElement.classList.contains('dark') ? '#18181b' : '#ffffff',
                    color: document.documentElement.classList.contains('dark') ? '#f4f4f5' : '#09090b',
                    confirmButtonColor: '#10b981', customClass: { popup: 'swal2-custom-radius' }
                });
                return;
            }

            // CLEANUP: Menghapus titik sebelum dikirim ke Backend Controller
            let moneyInputs = this.querySelectorAll('.so-price, input[name="dp_amount"], input[name="amount"]');
            moneyInputs.forEach(input => {
                if(input.value) input.value = input.value.replace(/\./g, '');
            });

            let btn = this.querySelector('button[type="submit"]');
            if(btn && !btn.classList.contains('pay-btn-small')) {
                btn.innerHTML = '<i class="ph-bold ph-spinner-gap ph-spin" style="font-size: 20px;"></i> Mengirim...';
                btn.style.opacity = '0.8'; btn.style.pointerEvents = 'none';
            }
        });
    });

    // --- SO BUILDER SCRIPT ---
    function addSoRow() {
        let container = document.getElementById('item-container');
        let options = '<option value="" data-price="0">-- Pilih Produk Katalog (PRD) --</option>';
        prodData.forEach(p => { 
            let wholesalePrice = parseFloat(p.wholesale_price) || parseFloat(p.hpp) || 0;
            options += `<option value="${p.sku}" data-price="${wholesalePrice}">[Sisa Stok: ${p.physical_stock}] ${p.item_name}</option>`; 
        });

        let row = document.createElement('div');
        row.className = 'item-row';
        
        row.innerHTML = `
            <div class="item-grid">
                <select name="fg_sku[]" class="form-control so-select" required onchange="autoFillPrice(this)">${options}</select>
                <input type="number" name="qty[]" class="form-control so-qty" placeholder="Kuantitas" required min="1" oninput="calcSoTotal()" style="text-align: center; font-family: 'Space Mono', monospace; font-size: 16px;">
                <div class="input-money">
                    <span>Rp</span>
                    <input type="text" inputmode="numeric" name="unit_price[]" class="so-price" placeholder="Harga Satuan" required oninput="formatRupiah(this); calcSoTotal();">
                </div>
                <button type="button" class="btn-remove-row" onclick="this.parentElement.parentElement.remove(); calcSoTotal();" title="Hapus"><i class="ph-bold ph-trash"></i></button>
            </div>
        `;
        row.style.opacity = 0; row.style.transform = "translateY(15px)";
        container.appendChild(row);
        setTimeout(() => { row.style.opacity = 1; row.style.transform = "translateY(0)"; }, 10);
    }

    function autoFillPrice(selectElement) {
        let selectedOption = selectElement.options[selectElement.selectedIndex];
        let price = selectedOption.getAttribute('data-price') || 0;
        
        let priceInput = selectElement.closest('.item-grid').querySelector('.so-price');
        if (priceInput) {
            priceInput.value = parseFloat(price).toLocaleString('id-ID'); 
            calcSoTotal(); 
        }
    }

    function calcSoTotal() {
        let qtys = document.querySelectorAll('.so-qty');
        let prices = document.querySelectorAll('.so-price');
        let total = 0;
        for(let i = 0; i < qtys.length; i++) {
            let q = parseFloat(qtys[i].value) || 0;
            let p = parseRupiah(prices[i].value); 
            total += (q * p);
        }
        document.getElementById('soGrandTotal').innerText = 'Rp ' + total.toLocaleString('id-ID');
    }

    // --- RECEIPT & RETURN SCRIPT ---
    function openReceiptModal(btn) {
        const soNum = btn.getAttribute('data-so');
        const cust  = btn.getAttribute('data-cust');
        const date  = btn.getAttribute('data-date');
        const stat  = btn.getAttribute('data-status');
        const total = parseFloat(btn.getAttribute('data-total'));
        const paid  = parseFloat(btn.getAttribute('data-paid'));
        const sisa  = total - paid;
        const items = JSON.parse(btn.getAttribute('data-items'));

        document.getElementById('r_so').innerText = soNum;
        document.getElementById('r_cust').innerText = cust;
        document.getElementById('r_date').innerText = date;
        document.getElementById('r_status').innerText = (stat === 'PAID') ? 'LUNAS' : ((stat === 'PARTIAL') ? 'CICILAN' : ((stat === 'RETURNED') ? 'DIRETUR' : 'BELUM BAYAR'));
        
        let tbody = document.getElementById('r_items');
        tbody.innerHTML = '';
        items.forEach(it => {
            let name = it.item_name ? it.item_name : it.fg_sku;
            let qty = it.qty;
            let p = parseFloat(it.price) || 0; 
            let sub = qty * p;
            let strikeStyle = (stat === 'RETURNED') ? 'text-decoration: line-through;' : '';

            tbody.innerHTML += `
                <tr>
                    <td style="padding: 10px 0; border: none; ${strikeStyle}">
                        <div style="font-weight: 800; margin-bottom: 6px;">${name}</div>
                        <div style="color: #555; font-size: 13px;">${qty} x Rp ${p.toLocaleString('id-ID')}</div>
                    </td>
                    <td style="text-align: right; vertical-align: bottom; padding: 10px 0; font-weight: 900; border: none; ${strikeStyle}">
                        Rp ${sub.toLocaleString('id-ID')}
                    </td>
                </tr>
            `;
        });

        document.getElementById('r_total').innerText = 'Rp ' + total.toLocaleString('id-ID');
        document.getElementById('r_paid').innerText = 'Rp ' + paid.toLocaleString('id-ID');
        document.getElementById('r_sisa').innerText = 'Rp ' + sisa.toLocaleString('id-ID');

        openModal('modalReceipt');
    }

    // --- MODAL RETUR PENJUALAN ---
    function openReturnModal(soId) {
        const modal = document.getElementById('returnModal');
        const form = document.getElementById('returnForm');
        const container = document.getElementById('returnItemsContainer');
        const totalEl = document.getElementById('returnGrandTotal');

        const so = salesOrdersData.find(x => parseInt(x.id) === parseInt(soId));
        if (!so) return;

        form.action = "<?= base_url('/wholesale/return_so/') ?>" + soId;
        container.innerHTML = '';
        totalEl.innerText = 'Rp 0';

        let hasReturnable = false;

        (so.items || []).forEach((item, index) => {
            const returnableQty = parseInt(item.returnable_qty || 0);
            if (returnableQty <= 0) return;

            hasReturnable = true;

            const row = document.createElement('div');
            row.className = 'item-row';
            row.style.background = '#f8fafc';
            row.innerHTML = `
                <div class="item-grid" style="grid-template-columns: 2fr 1.5fr 1fr 1.5fr;">
                    <div>
                        <label style="display:block; font-size:10px; font-weight:800; color:var(--text-muted); margin-bottom:6px; text-transform:uppercase;">Produk</label>
                        <input type="hidden" name="so_item_id[]" value="${item.id}">
                        <div style="font-weight:900; font-size:13px; color:var(--text-main); margin-bottom:4px;">${item.item_name || item.fg_sku}</div>
                        <div style="font-size:11px; color:var(--text-muted); font-family:'Space Mono', monospace;">
                            Terjual: ${item.qty} | Sisa Retur: <b style="color:var(--brand-green);">${returnableQty}</b>
                        </div>
                    </div>

                    <div>
                        <label style="display:block; font-size:10px; font-weight:800; color:var(--text-muted); margin-bottom:6px; text-transform:uppercase;">Harga Beli</label>
                        <input type="text" class="form-control" value="${formatRupiahJS(parseFloat(item.price || 0))}" readonly style="font-family:'Space Mono', monospace;">
                    </div>

                    <div>
                        <label style="display:block; font-size:10px; font-weight:800; color:var(--text-muted); margin-bottom:6px; text-transform:uppercase;">Qty Retur</label>
                        <input type="number" name="qty_return[]" class="form-control return-qty" style="border-color: #ef4444; color: #ef4444; font-family:'Space Mono', monospace; font-size:16px; font-weight:900; text-align:center;"
                               min="0" max="${returnableQty}" value="0"
                               data-price="${parseFloat(item.price || 0)}"
                               oninput="calcReturnTotal()">
                    </div>

                    <div>
                        <label style="display:block; font-size:10px; font-weight:800; color:var(--text-muted); margin-bottom:6px; text-transform:uppercase;">Subtotal Retur</label>
                        <input type="text" class="form-control return-subtotal" value="Rp 0" readonly style="color:var(--text-main); font-weight:900; font-family:'Space Mono', monospace;">
                    </div>
                </div>
            `;
            container.appendChild(row);
        });

        if (!hasReturnable) {
            container.innerHTML = `
                <div class="empty-state" style="padding:20px 10px;">
                    <i class="ph-bold ph-package" style="font-size:40px; margin-bottom:10px;"></i>
                    <h3 style="font-size:15px;">Semua item sudah habis diretur</h3>
                    <p style="font-size:12px;">Sales Order ini sudah tidak punya item yang bisa diretur lagi.</p>
                </div>
            `;
        }

        modal.classList.add('active');
        calcReturnTotal();
    }

    function closeReturnModal() {
        document.getElementById('returnModal').classList.remove('active');
    }

    function calcReturnTotal() {
        let grandTotal = 0;

        document.querySelectorAll('#returnItemsContainer .item-row').forEach(row => {
            const qtyInput = row.querySelector('.return-qty');
            const subtotalInput = row.querySelector('.return-subtotal');

            const qty = parseInt(qtyInput.value || 0);
            const price = parseFloat(qtyInput.dataset.price || 0);
            const subtotal = qty * price;

            subtotalInput.value = formatRupiahJS(subtotal);
            grandTotal += subtotal;
        });

        document.getElementById('returnGrandTotal').innerText = formatRupiahJS(grandTotal);
    }

    // SWEETALERT ALERTS
    document.addEventListener("DOMContentLoaded", function() {
        const isDark = document.documentElement.classList.contains('dark');
        const swalBg = isDark ? '#18181b' : '#ffffff';
        const swalText = isDark ? '#f4f4f5' : '#09090b';

        <?php if(session()->getFlashdata('success')): ?>
            Swal.fire({ icon: 'success', title: 'Berhasil!', html: '<?= session()->getFlashdata('success') ?>', confirmButtonColor: '#10b981', background: swalBg, color: swalText, customClass: { popup: 'swal2-custom-radius' } });
        <?php endif; ?>
        <?php if(session()->getFlashdata('error')): ?>
            Swal.fire({ icon: 'error', title: 'Gagal!', html: '<?= session()->getFlashdata('error') ?>', confirmButtonColor: '#ef4444', background: swalBg, color: swalText, customClass: { popup: 'swal2-custom-radius' } });
        <?php endif; ?>
    });
</script>

<?= $this->endSection() ?>