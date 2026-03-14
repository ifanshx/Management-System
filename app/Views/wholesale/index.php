<?= $this->extend('layout/template') ?>
<?= $this->section('content') ?>

<style>
    /* =========================================================
       1. PAGE HEADER & TABS
       ========================================================= */
    .page-header { margin-bottom: 25px; display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 15px;}
    .page-title { display: flex; align-items: center; gap: 15px;}
    .title-icon { width: 50px; height: 50px; border-radius: 14px; background: linear-gradient(135deg, rgba(16, 185, 129, 0.15), rgba(5, 150, 105, 0.05)); color: #10b981; display: flex; align-items: center; justify-content: center; font-size: 26px; border: 1px solid rgba(16, 185, 129, 0.2);}
    .page-title h1 { font-size: 26px; font-weight: 900; color: var(--text-main); margin: 0 0 4px 0; letter-spacing: -0.5px;}
    .page-title p { font-size: 13px; color: var(--text-muted); margin: 0; font-weight: 500;}

    .tab-nav { display: inline-flex; background: var(--bg-surface); padding: 6px; border-radius: 16px; border: 1px solid var(--border-subtle); margin-bottom: 25px; box-shadow: 0 2px 10px rgba(0,0,0,0.02);}
    .tab-btn { padding: 10px 20px; font-size: 13px; font-weight: 800; border-radius: 12px; border: none; background: transparent; color: var(--text-muted); cursor: pointer; transition: all 0.3s; display: flex; align-items: center; gap: 8px;}
    .tab-btn:hover { color: var(--text-main); }
    .tab-btn.active { background: var(--bg-base); color: #10b981; box-shadow: 0 2px 8px rgba(0,0,0,0.05); border: 1px solid rgba(16, 185, 129, 0.1); }
    
    .tab-content { display: none; animation: slideFadeUp 0.4s cubic-bezier(0.16, 1, 0.3, 1); }
    .tab-content.active { display: block; }
    @keyframes slideFadeUp { from { opacity: 0; transform: translateY(15px); } to { opacity: 1; transform: translateY(0); } }

    /* =========================================================
       2. LAYOUT GRID & BUTTONS
       ========================================================= */
    .layout-grid { display: grid; grid-template-columns: 380px 1fr; gap: 24px; align-items: start;}
    @media (max-width: 1024px) { .layout-grid { grid-template-columns: 1fr; } }

    .bento-card { background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: 24px; box-shadow: 0 10px 30px -10px rgba(0,0,0,0.05); padding: 25px; transition: all 0.3s;}
    .card-title { font-size: 15px; font-weight: 900; color: var(--text-main); margin-bottom: 20px; display: flex; align-items: center; gap: 10px; border-bottom: 2px dashed var(--border-subtle); padding-bottom: 15px;}
    .card-title i { background: rgba(16, 185, 129, 0.1); color: #10b981; padding: 6px; border-radius: 8px; font-size: 18px;}

    .btn-submit { width: 100%; background: linear-gradient(135deg, #10b981, #059669); color: #fff; border: none; padding: 16px 20px; border-radius: 14px; font-size: 15px; font-weight: 900; cursor: pointer; transition: 0.3s; display: flex; justify-content: center; align-items: center; gap: 8px; box-shadow: 0 8px 20px -5px rgba(16, 185, 129, 0.5);}
    .btn-submit:hover { transform: translateY(-3px); box-shadow: 0 12px 25px -5px rgba(16, 185, 129, 0.6);}
    
    .btn-secondary { background: var(--bg-surface); color: var(--text-main); border: 1px dashed var(--border-subtle); padding: 12px 20px; border-radius: 14px; font-weight: 800; font-size: 13px; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; transition: 0.3s;}
    .btn-secondary:hover { border-color: #10b981; color: #10b981; background: rgba(16, 185, 129, 0.05); transform: translateY(-2px);}

    /* =========================================================
       3. FORM ELEMENTS (SO BUILDER)
       ========================================================= */
    .form-group { margin-bottom: 15px;}
    .form-group label { display: block; font-size: 11px; font-weight: 900; color: var(--text-muted); margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px;}
    .form-control { width: 100%; background: var(--bg-base); border: 1px solid var(--border-subtle); padding: 14px 16px; border-radius: 12px; font-size: 13px; font-weight: 700; outline: none; color: var(--text-main); transition: 0.3s; appearance: none; cursor: pointer;}
    .form-control:focus { border-color: #10b981; background: var(--bg-surface); box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.15);}

    .input-money { display: flex; align-items: center; background: var(--bg-base); border: 1px solid var(--border-subtle); border-radius: 12px; overflow: hidden; transition: 0.3s;}
    .input-money:focus-within { border-color: #10b981; box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.15); background: var(--bg-surface);}
    .input-money span { padding: 14px 16px; background: rgba(0,0,0,0.03); font-size: 12px; font-weight: 900; color: var(--text-muted); border-right: 1px solid var(--border-subtle);}
    .input-money input { border: none; padding: 14px 16px; font-size: 14px; font-weight: 800; width: 100%; outline: none; background: transparent; color: var(--text-main); font-family: 'Space Mono', monospace;}

    .item-row { background: var(--bg-base); border: 1px dashed var(--border-subtle); padding: 15px; border-radius: 16px; margin-bottom: 12px; transition: 0.3s;}
    .item-row:focus-within { border-color: #10b981; border-style: solid; background: var(--bg-surface); box-shadow: 0 4px 15px rgba(16, 185, 129, 0.08);}
    
    .btn-add-row { width: 100%; background: var(--bg-surface); border: 2px dashed #10b981; color: #10b981; padding: 14px; border-radius: 14px; font-weight: 900; font-size: 13px; cursor: pointer; margin-bottom: 20px; transition: 0.3s; display: flex; align-items: center; justify-content: center; gap: 8px;}
    .btn-add-row:hover { background: rgba(16, 185, 129, 0.05); transform: translateY(-2px);}
    .btn-remove-row { background: rgba(239, 68, 68, 0.1); color: #ef4444; border: 1px solid transparent; width: 44px; height: 44px; border-radius: 10px; font-size: 20px; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: 0.3s;}
    .btn-remove-row:hover { background: #ef4444; color: #fff; transform: scale(1.05);}

    .live-total-box { background: linear-gradient(135deg, #0f172a, #1e293b); border: 1px solid #334155; padding: 20px 25px; border-radius: 16px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 10px 20px rgba(0,0,0,0.1);}
    .live-total-val { font-size: 28px; font-weight: 900; color: #10b981; font-family: 'Space Mono', monospace; line-height: 1; letter-spacing: -1px;}

    /* =========================================================
       4. ANALYTICAL TABLE DESIGN
       ========================================================= */
    table { width: 100%; border-collapse: collapse; white-space: nowrap; }
    th { text-align: center; padding: 16px 20px; font-size: 11px; font-weight: 900; text-transform: uppercase; color: var(--text-muted); border-bottom: 2px solid var(--border-subtle); border-right: 1px solid var(--border-subtle); background: var(--bg-base); letter-spacing: 0.5px;}
    td { text-align: center; padding: 16px 20px; border-bottom: 1px dashed var(--border-subtle); border-right: 1px solid var(--border-subtle); color: var(--text-main); font-size: 13px; font-weight: 600; vertical-align: middle; transition: 0.2s;}
    
    th:first-child, td:first-child { text-align: left; }
    th:last-child, td:last-child { border-right: none; }
    tr:last-child td { border-bottom: none; }
    tr:hover td { background: rgba(16, 185, 129, 0.02); }
    html.dark tr:hover td { background: rgba(16, 185, 129, 0.05); }

    /* Action Buttons in Table */
    .action-group { display: flex; justify-content: center; gap: 8px; flex-wrap: wrap; }
    .btn-rincian { background: var(--bg-base); border: 1px solid var(--border-subtle); color: #3b82f6; font-size: 12px; font-weight: 800; padding: 8px 14px; border-radius: 10px; display: inline-flex; align-items: center; gap: 6px; cursor: pointer; transition: 0.3s;}
    .btn-rincian:hover { background: #3b82f6; color: #fff; border-color: #3b82f6; transform: translateY(-2px);}
    .btn-sj { background: var(--bg-base); border: 1px solid var(--border-subtle); color: #f59e0b; font-size: 12px; font-weight: 800; padding: 8px 14px; border-radius: 10px; display: inline-flex; align-items: center; gap: 6px; cursor: pointer; transition: 0.3s; text-decoration: none;}
    .btn-sj:hover { background: #f59e0b; color: #fff; border-color: #f59e0b; transform: translateY(-2px);}
    
    /* Pay Installment Unified Input */
    .pay-form { display: flex; align-items: center; background: var(--bg-base); border: 1px solid var(--border-subtle); border-radius: 10px; overflow: hidden; focus-within: border-color: #10b981; margin: 0 auto; width: fit-content;}
    .pay-form span { font-size: 10px; font-weight: 900; color: var(--text-muted); padding: 8px 10px; background: rgba(0,0,0,0.03); border-right: 1px solid var(--border-subtle);}
    .pay-form input { border: none; background: transparent; padding: 8px 10px; font-size: 12px; font-weight: 800; width: 100px; outline: none; font-family: 'Space Mono', monospace;}
    .pay-btn-small { background: rgba(16, 185, 129, 0.1); border: none; border-left: 1px solid var(--border-subtle); color: #10b981; padding: 8px 14px; font-weight: 900; font-size: 12px; cursor: pointer; transition: 0.3s;}
    .pay-btn-small:hover { background: #10b981; color: #fff;}

    .btn-delete { color: #ef4444; background: rgba(239, 68, 68, 0.05); font-size: 18px; transition: 0.3s; width: 34px; height: 34px; display: inline-flex; align-items: center; justify-content: center; border-radius: 10px; border: 1px solid transparent;}
    .btn-delete:hover { color: #fff; background: #ef4444; box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3); transform: translateY(-2px);}

    /* Progress Bar */
    .progress-wrapper { width: 100%; background: var(--border-subtle); border-radius: 100px; height: 6px; overflow: hidden; margin: 8px 0;}
    .progress-bar { height: 100%; border-radius: 100px; transition: width 0.5s ease-in-out;}
    .status-badge { padding: 5px 10px; border-radius: 8px; font-size: 10px; font-weight: 900; text-transform: uppercase; letter-spacing: 0.5px; display: inline-block;}
    .s-pending { background: rgba(239, 68, 68, 0.1); color: #ef4444;}
    .s-partial { background: rgba(245, 158, 11, 0.1); color: #d97706;}
    .s-paid { background: rgba(16, 185, 129, 0.1); color: #10b981;}

    .empty-state { text-align: center; padding: 60px 20px; color: var(--text-muted); }
    .empty-state i { font-size: 56px; color: var(--border-subtle); margin-bottom: 15px; display: block; }
    .empty-state h3 { font-size: 16px; font-weight: 900; color: var(--text-main); margin-bottom: 5px; }

    /* =========================================================
       5. MODALS & PRINT MEDIA
       ========================================================= */
    .modal-overlay { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.6); backdrop-filter: blur(6px); z-index: 1000; display: none; justify-content: center; align-items: center; opacity: 0; transition: opacity 0.3s;}
    .modal-overlay.active { display: flex; opacity: 1; }
    
    .modal-box { background: var(--bg-surface); border-radius: 28px; width: 100%; max-width: 500px; padding: 40px; transform: scale(0.95) translateY(20px); transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1); box-shadow: 0 30px 60px -15px rgba(0,0,0,0.4); border: 1px solid var(--border-subtle);}
    .modal-overlay.active .modal-box { transform: scale(1) translateY(0); }
    
    .modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
    .btn-close { background: var(--bg-base); border: 1px solid var(--border-subtle); width: 36px; height: 36px; border-radius: 50%; font-size: 16px; color: var(--text-muted); cursor: pointer; display: flex; align-items: center; justify-content: center; transition: 0.2s;}
    .btn-close:hover { background: #ef4444; color: #fff; border-color: #ef4444; transform: rotate(90deg);}

    /* Thermal Receipt Modal Design */
    .receipt-paper { background: #ffffff; color: #000; padding: 30px; border-radius: 8px; font-family: 'Space Mono', monospace; border: 1px solid #ccc; box-shadow: 0 10px 20px rgba(0,0,0,0.1); max-height: 500px; overflow-y: auto;}

    @media print {
        body * { visibility: hidden; }
        #printArea, #printArea * { visibility: visible; color: #000 !important; }
        #printArea { position: absolute; left: 0; top: 0; width: 100%; max-width: 100%; padding: 0; background: #fff !important; border: none; box-shadow: none;}
        .modal-overlay { background: transparent; }
        .modal-box { box-shadow: none; border: none; padding: 0; }
        .no-print { display: none !important; }
    }
</style>

<div class="page-header no-print">
    <div class="page-title">
        <div class="title-icon"><i class="ph-fill ph-handshake"></i></div>
        <div>
            <h1>B2B Wholesale & Piutang</h1>
            <p>Kelola pesanan partai besar (Grosir/Reseller) dan pantau pelunasan piutang.</p>
        </div>
    </div>
</div>

<div class="tab-nav no-print">
    <button class="tab-btn active" onclick="switchTab('so')"><i class="ph-bold ph-receipt"></i> Pesanan Grosir (SO)</button>
    <button class="tab-btn" onclick="switchTab('reseller')"><i class="ph-bold ph-users-three"></i> Master Data Reseller</button>
</div>

<div id="tab-so" class="tab-content active no-print">
    <div class="layout-grid">
        
        <div class="bento-card" style="border-top: 6px solid #10b981; position: sticky; top: 20px;">
            <div class="card-title">
                <i class="ph-fill ph-shopping-cart"></i> Buat Pesanan Baru
            </div>
            
            <form action="<?= base_url('/wholesale/store_so') ?>" method="post">
                <?= csrf_field() ?>
                
                <div class="form-group">
                    <label>Pilih Mitra Reseller</label>
                    <select name="customer_id" class="form-control" required style="border: 2px solid rgba(16, 185, 129, 0.3);">
                        <option value="">-- Pilih Toko / Bengkel --</option>
                        <?php foreach($customers as $c): ?>
                            <option value="<?= $c['id'] ?>"><?= esc($c['company_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div style="margin: 25px 0 15px 0; border-top: 2px dashed var(--border-subtle); padding-top: 20px;">
                    <label style="font-size: 11px; font-weight: 900; color: var(--text-main); margin-bottom: 12px; display: flex; align-items: center; gap: 8px;">
                        <div style="background: rgba(16, 185, 129, 0.1); color: #10b981; padding: 4px; border-radius: 6px;"><i class="ph-fill ph-package" style="font-size: 16px;"></i></div> 
                        Daftar Barang Pesanan
                    </label>
                    
                    <div id="item-container">
                        <div class="item-row">
                            <div class="form-group" style="margin-bottom: 12px;">
                                <select name="fg_sku[]" class="form-control" required>
                                    <option value="">-- Pilih Produk (PRD) --</option>
                                    <?php foreach($products as $p): ?>
                                        <option value="<?= $p['sku'] ?>">[Stok: <?= $p['physical_stock'] ?>] <?= esc($p['item_name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div style="display: grid; grid-template-columns: 1fr 1.8fr auto; gap: 12px; align-items: center;">
                                <input type="number" name="qty[]" class="form-control so-qty" placeholder="Qty" required min="1" oninput="calcSoTotal()" style="text-align: center; font-family: 'Space Mono', monospace; font-size: 15px;">
                                <div class="input-money">
                                    <span>Rp</span>
                                    <input type="number" name="unit_price[]" class="so-price" placeholder="Harga Satuan" required oninput="calcSoTotal()">
                                </div>
                                <button type="button" class="btn-remove-row" onclick="this.parentElement.parentElement.remove(); calcSoTotal();" title="Hapus"><i class="ph-bold ph-trash"></i></button>
                            </div>
                        </div>
                    </div>

                    <button type="button" class="btn-add-row" onclick="addSoRow()">
                        <i class="ph-bold ph-plus-circle" style="font-size: 18px;"></i> Tambah Produk Lainnya
                    </button>
                </div>

                <div class="live-total-box">
                    <div><div style="font-size: 11px; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.5px;">Estimasi Grand Total</div></div>
                    <div class="live-total-val" id="soGrandTotal">Rp 0</div>
                </div>

                <div style="display: grid; grid-template-columns: 1.5fr 1fr; gap: 15px; margin-top: 25px;">
                    <div class="form-group">
                        <label>Uang Muka / DP Dibayar</label>
                        <div class="input-money">
                            <span>Rp</span>
                            <input type="number" name="dp_amount" value="0" required min="0">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Jatuh Tempo</label>
                        <input type="date" name="due_date" class="form-control" required style="font-family: 'Space Mono', monospace;">
                    </div>
                </div>
                
                <button type="submit" class="btn-submit" style="margin-top: 20px;">
                    <i class="ph-bold ph-paper-plane-tilt" style="font-size: 18px;"></i> Terbitkan Dokumen SO
                </button>
            </form>
        </div>

        <div class="bento-card" style="padding: 20px;">
            <div class="card-title" style="color: var(--text-muted); margin-bottom: 15px;">
                <i class="ph-fill ph-list-dashes"></i> Daftar Piutang & Transaksi
            </div>
            
            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>Dokumen SO</th>
                            <th>Reseller</th>
                            <th style="min-width: 180px;">Status Pembayaran</th>
                            <th style="text-align: center;">Aksi Dokumen</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($salesOrders)): ?>
                            <tr>
                                <td colspan="4">
                                    <div class="empty-state">
                                        <i class="ph-fill ph-receipt"></i>
                                        <h3>Belum Ada Riwayat Grosir</h3>
                                        <p>Buat pesanan pertama melalui form di sebelah kiri.</p>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach($salesOrders as $so): 
                                $percent = ($so['total_amount'] > 0) ? ($so['paid_amount'] / $so['total_amount']) * 100 : 0;
                                $barColor = ($percent == 100) ? '#10b981' : (($percent > 0) ? '#f59e0b' : '#ef4444');
                                $isOverdue = (strtotime($so['due_date']) < time() && $so['status'] != 'PAID'); 
                            ?>
                            <tr>
                                <td style="white-space: normal; text-align: left;">
                                    <div style="font-family: 'Space Mono', monospace; font-weight: 900; color: #10b981; font-size: 13px; background: rgba(16, 185, 129, 0.1); padding: 4px 8px; border-radius: 6px; display: inline-block; margin-bottom: 6px; border: 1px dashed rgba(16, 185, 129, 0.3);">
                                        <?= esc($so['so_number']) ?>
                                    </div>
                                    <div style="font-size: 10px; color: <?= $isOverdue ? '#ef4444' : 'var(--text-muted)' ?>; font-weight: 800; display: flex; align-items: center; gap: 4px;">
                                        <i class="<?= $isOverdue ? 'ph-fill ph-warning-circle' : 'ph-bold ph-calendar-blank' ?>"></i> 
                                        Tempo: <?= date('d M Y', strtotime($so['due_date'])) ?>
                                    </div>
                                </td>
                                <td style="text-align: left;">
                                    <div style="font-weight: 900; display:flex; align-items:center; gap:8px; font-size: 13px; color: var(--text-main);">
                                        <div style="background: rgba(0,0,0,0.03); padding: 6px; border-radius: 8px; border: 1px solid var(--border-subtle); display: flex;"><i class="ph-fill ph-storefront" style="color: var(--text-muted);"></i></div>
                                        <?= esc($so['company_name']) ?>
                                    </div>
                                </td>
                                <td>
                                    <div style="display: flex; justify-content: space-between; font-size: 11px; font-weight: 900; font-family: 'Space Mono', monospace;">
                                        <span style="color: var(--text-main);">Rp <?= number_format($so['paid_amount'],0,',','.') ?></span>
                                        <span style="color: var(--text-muted);">Rp <?= number_format($so['total_amount'],0,',','.') ?></span>
                                    </div>
                                    <div class="progress-wrapper">
                                        <div class="progress-bar" style="width: <?= $percent ?>%; background-color: <?= $barColor ?>;"></div>
                                    </div>
                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 4px;">
                                        <div class="status-badge s-<?= strtolower($so['status']) ?>">
                                            <?= $so['status'] ?> (<?= round($percent, 1) ?>%)
                                        </div>
                                        
                                        <?php if($so['status'] != 'PAID'): ?>
                                            <form action="<?= base_url('/wholesale/pay_installment/'.$so['id']) ?>" method="post" class="pay-form">
                                                <?= csrf_field() ?>
                                                <span>Rp</span>
                                                <input type="number" name="amount" placeholder="Nominal" required min="1">
                                                <button type="submit" class="pay-btn-small"><i class="ph-bold ph-wallet"></i></button>
                                            </form>
                                        <?php else: ?>
                                            <span style="color: #10b981; font-weight: 900; font-size: 11px;"><i class="ph-fill ph-check-circle"></i> FULL PAID</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="action-group">
                                        <button type="button" class="btn-rincian" 
                                                data-so="<?= esc($so['so_number']) ?>"
                                                data-cust="<?= esc($so['company_name']) ?>"
                                                data-date="<?= date('d M Y', strtotime($so['order_date'])) ?>"
                                                data-status="<?= $so['status'] ?>"
                                                data-total="<?= $so['total_amount'] ?>"
                                                data-paid="<?= $so['paid_amount'] ?>"
                                                data-items="<?= htmlspecialchars(json_encode($so['items'] ?? []), ENT_QUOTES, 'UTF-8') ?>" 
                                                onclick="openReceiptModal(this)" title="Cetak Struk">
                                            <i class="ph-bold ph-receipt"></i> Struk
                                        </button>

                                        <a href="<?= base_url('/wholesale/surat_jalan/'.$so['id']) ?>" target="_blank" class="btn-sj" title="Cetak Surat Jalan">
                                            <i class="ph-bold ph-truck"></i> Surat Jalan
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
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
        <div style="font-size: 12px; font-weight: 800; color: var(--text-muted); background: var(--bg-surface); padding: 12px 20px; border-radius: 12px; border: 1px solid var(--border-subtle); display: flex; align-items: center; gap: 8px;">
            <i class="ph-fill ph-users-three" style="font-size: 18px; color: #10b981;"></i>
            Total Mitra: <span style="color: var(--text-main); font-size: 16px; font-weight: 900; font-family: 'Space Mono', monospace;"><?= count($customers) ?></span>
        </div>
        <button class="btn-secondary" onclick="openModal('modalReseller')">
            <i class="ph-bold ph-plus-circle" style="font-size: 16px;"></i> Tambah Mitra Baru
        </button>
    </div>

    <div class="bento-card" style="padding: 20px;">
        <div style="overflow-x: auto;">
            <table>
                <thead>
                    <tr>
                        <th>Nama Toko / Perusahaan</th>
                        <th>Kontak (PIC)</th>
                        <th style="text-align: center;">Telepon / WA</th>
                        <th>Alamat Pengiriman</th>
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
                                    <p>Klik tombol "Tambah Mitra Baru" untuk mendaftarkan agen Anda.</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach($customers as $c): ?>
                            <tr>
                                <td style="font-weight: 900; color: var(--text-main); font-size: 14px;">
                                    <div style="display: flex; align-items: center; gap: 10px;">
                                        <div style="width: 34px; height: 34px; border-radius: 10px; background: rgba(16, 185, 129, 0.1); color: #10b981; display: flex; align-items: center; justify-content: center; font-size: 18px;"><i class="ph-fill ph-storefront"></i></div>
                                        <?= esc($c['company_name']) ?>
                                    </div>
                                </td>
                                <td>
                                    <div style="font-weight: 800; display:flex; align-items:center; gap:6px; font-size: 13px; color: var(--text-muted);">
                                        <i class="ph-fill ph-user-circle" style="font-size: 18px;"></i> <?= esc($c['contact_name'] ?? '-') ?>
                                    </div>
                                </td>
                                <td style="text-align: center;">
                                    <div style="font-family: 'Space Mono', monospace; font-size: 13px; font-weight: 800; color: #10b981; background: rgba(16, 185, 129, 0.08); padding: 4px 10px; border-radius: 8px; display: inline-block; border: 1px dashed rgba(16, 185, 129, 0.3);">
                                        <?= esc($c['phone'] ?? '-') ?>
                                    </div>
                                </td>
                                <td style="white-space: normal; line-height: 1.5; font-size: 13px; color: var(--text-muted); max-width: 250px;">
                                    <?= esc($c['address'] ?? '-') ?>
                                </td>
                                <td style="text-align: center;">
                                    <a href="#" onclick="openConfirmModal(event, '<?= base_url('/wholesale/delete_customer/'.$c['id']) ?>')" class="btn-delete" title="Hapus Data">
                                        <i class="ph-bold ph-trash"></i>
                                    </a>
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
    <div class="modal-box" style="border-top: 8px solid #10b981;">
        <div class="modal-header">
            <div class="modal-title">
                <div style="background: rgba(16, 185, 129, 0.1); width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; color: #10b981;">
                    <i class="ph-fill ph-users-three" style="font-size: 24px;"></i>
                </div>
                Registrasi Reseller
            </div>
            <button class="btn-close" onclick="closeModal('modalReseller')" type="button"><i class="ph-bold ph-x"></i></button>
        </div>
        <form action="<?= base_url('/wholesale/store_customer') ?>" method="post">
            <?= csrf_field() ?>
            <div class="form-group">
                <label>Nama Toko / Bengkel</label>
                <input type="text" name="company_name" class="form-control" required autocomplete="off" placeholder="Cth: Bengkel Motor Jaya">
            </div>
            <div class="grid-2">
                <div class="form-group">
                    <label>Nama Pemilik / PIC</label>
                    <input type="text" name="contact_name" class="form-control" autocomplete="off" placeholder="Cth: Bpk. Joko">
                </div>
                <div class="form-group">
                    <label>No. WhatsApp</label>
                    <input type="text" name="phone" class="form-control" autocomplete="off" placeholder="Cth: 0812...">
                </div>
            </div>
            <div class="form-group">
                <label>Alamat Lengkap (Pengiriman)</label>
                <textarea name="address" class="form-control" rows="3" style="resize: none; line-height: 1.5;" placeholder="Alamat bengkel untuk tujuan pengiriman truk..."></textarea>
            </div>
            <button type="submit" class="btn-submit" style="margin-top: 20px;">
                <i class="ph-bold ph-floppy-disk" style="font-size: 20px;"></i> Simpan Data Mitra
            </button>
        </form>
    </div>
</div>

<div class="modal-overlay no-print" id="modalConfirm">
    <div class="modal-box" style="max-width: 400px; text-align: center; padding: 45px 30px;">
        <div style="width: 80px; height: 80px; border-radius: 50%; background: rgba(239, 68, 68, 0.15); color: #ef4444; border: 2px dashed rgba(239, 68, 68, 0.4); display: flex; align-items: center; justify-content: center; font-size: 40px; margin: 0 auto 20px auto;">
            <i class="ph-fill ph-trash"></i>
        </div>
        <h2 style="font-size: 22px; font-weight: 900; color: var(--text-main); margin-bottom: 12px; letter-spacing: -0.5px;">Hapus Mitra?</h2>
        <p style="font-size: 14px; color: var(--text-muted); line-height: 1.5; margin-bottom: 30px; font-weight: 500;">Yakin ingin menghapus data Reseller ini secara permanen?</p>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
            <button onclick="closeModal('modalConfirm')" class="btn-secondary" style="justify-content: center;">Batal</button>
            <a href="#" id="confirmBtnYes" class="btn-submit" style="background: linear-gradient(135deg, #ef4444, #dc2626); box-shadow: 0 8px 20px -5px rgba(239, 68, 68, 0.5); justify-content: center; text-decoration: none;">Ya, Hapus</a>
        </div>
    </div>
</div>

<div class="modal-overlay" id="modalReceipt">
    <div class="modal-box" style="max-width: 420px; padding: 0; background: transparent; box-shadow: none; border: none;">
        
        <div class="no-print" style="margin-bottom: 10px; display: flex; justify-content: flex-end;">
            <button onclick="closeModal('modalReceipt')" class="btn-close" style="background: var(--bg-surface);"><i class="ph-bold ph-x"></i></button>
        </div>

        <div class="receipt-paper" id="printArea">
            <div style="text-align: center; margin-bottom: 25px;">
                <h2 style="margin: 0; font-size: 22px; font-weight: 900; letter-spacing: -1px; border-bottom: 2px dashed #000; padding-bottom: 10px; margin-bottom: 10px;">NORIC EXHAUST</h2>
                <div style="font-size: 12px; font-family: sans-serif; font-weight: 600;">INVOICE GROSIR (B2B)</div>
            </div>

            <div style="font-size: 13px; margin-bottom: 20px; line-height: 1.8;">
                <div style="display: flex; justify-content: space-between;"><span>No. SO:</span><b id="r_so"></b></div>
                <div style="display: flex; justify-content: space-between;"><span>Tanggal:</span><b id="r_date"></b></div>
                <div style="display: flex; justify-content: space-between;"><span>Kepada:</span><b id="r_cust"></b></div>
                <div style="display: flex; justify-content: space-between;"><span>Status:</span><b id="r_status"></b></div>
            </div>

            <div style="border-top: 1px dashed #000; border-bottom: 1px dashed #000; padding: 10px 0; margin-bottom: 20px;">
                <table style="width: 100%; font-size: 13px;">
                    <tbody id="r_items">
                        </tbody>
                </table>
            </div>

            <div style="font-size: 14px; line-height: 1.8;">
                <div style="display: flex; justify-content: space-between;">
                    <span>Grand Total</span>
                    <span id="r_total" style="font-weight: 900;"></span>
                </div>
                <div style="display: flex; justify-content: space-between;">
                    <span>Telah Dibayar</span>
                    <span id="r_paid"></span>
                </div>
                <div style="display: flex; justify-content: space-between; font-weight: 900; margin-top: 10px; border-top: 2px solid #000; padding-top: 10px; font-size: 16px;">
                    <span>Sisa Tagihan</span>
                    <span id="r_sisa"></span>
                </div>
            </div>

            <div style="text-align: center; font-size: 11px; margin-top: 40px; font-family: sans-serif;">
                <b>Terima kasih atas kepercayaan Anda</b><br>
                <span style="color: #666; margin-top: 4px; display: inline-block;">Dicetak otomatis oleh Sistem ERP</span>
            </div>
        </div>

        <div class="no-print" style="margin-top: 15px;">
            <button onclick="window.print()" class="btn-submit" style="background: #10b981; box-shadow: 0 8px 20px rgba(16, 185, 129, 0.4);">
                <i class="ph-bold ph-printer" style="font-size: 20px;"></i> Cetak Invoice
            </button>
        </div>
    </div>
</div>

<script>
    function switchTab(tabName) {
        document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
        event.currentTarget.classList.add('active');
        document.getElementById('tab-' + tabName).classList.add('active');
    }

    function openModal(modalId) { document.getElementById(modalId).classList.add('active'); }
    function closeModal(modalId) { document.getElementById(modalId).classList.remove('active'); }
    
    function openConfirmModal(event, actionUrl) {
        event.preventDefault(); 
        const modal = document.getElementById('modalConfirm');
        document.getElementById('confirmBtnYes').href = actionUrl;
        modal.classList.add('active');
    }

    window.onclick = function(event) {
        if (event.target.classList.contains('modal-overlay')) {
            event.target.classList.remove('active');
        }
    }

    const prodData = <?= json_encode($products) ?>;
    function addSoRow() {
        let container = document.getElementById('item-container');
        let options = '<option value="">-- Pilih Produk (PRD) --</option>';
        prodData.forEach(p => { options += `<option value="${p.sku}">[Stok: ${p.physical_stock}] ${p.item_name}</option>`; });

        let row = document.createElement('div');
        row.className = 'item-row';
        row.innerHTML = `
            <div class="form-group" style="margin-bottom: 12px;">
                <select name="fg_sku[]" class="form-control" required>${options}</select>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1.8fr auto; gap: 12px; align-items: center;">
                <input type="number" name="qty[]" class="form-control so-qty" placeholder="Qty" required min="1" oninput="calcSoTotal()" style="text-align: center; font-family: 'Space Mono', monospace; font-size: 15px;">
                <div class="input-money">
                    <span>Rp</span>
                    <input type="number" name="unit_price[]" class="so-price" placeholder="Harga Satuan" required oninput="calcSoTotal()">
                </div>
                <button type="button" class="btn-remove-row" onclick="this.parentElement.parentElement.remove(); calcSoTotal();" title="Hapus"><i class="ph-bold ph-trash"></i></button>
            </div>
        `;
        row.style.opacity = 0;
        row.style.transform = "translateY(10px)";
        container.appendChild(row);
        setTimeout(() => { row.style.opacity = 1; row.style.transform = "translateY(0)"; }, 10);
    }

    function calcSoTotal() {
        let qtys = document.querySelectorAll('.so-qty');
        let prices = document.querySelectorAll('.so-price');
        let total = 0;
        for(let i = 0; i < qtys.length; i++) {
            let q = parseFloat(qtys[i].value) || 0;
            let p = parseFloat(prices[i].value) || 0;
            total += (q * p);
        }
        document.getElementById('soGrandTotal').innerText = 'Rp ' + total.toLocaleString('id-ID');
    }

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
        document.getElementById('r_status').innerText = (stat === 'PAID') ? 'LUNAS' : ((stat === 'PARTIAL') ? 'CICILAN' : 'BELUM BAYAR');
        
        let tbody = document.getElementById('r_items');
        tbody.innerHTML = '';
        items.forEach(it => {
            let name = it.item_name ? it.item_name : it.fg_sku;
            let qty = it.qty;
            let p = parseFloat(it.unit_price) || 0;
            let sub = qty * p;
            tbody.innerHTML += `
                <tr>
                    <td style="padding: 8px 0;">
                        <div style="font-weight: 700; margin-bottom: 4px;">${name}</div>
                        <div style="color: #444; font-size: 11px;">${qty} x Rp ${p.toLocaleString('id-ID')}</div>
                    </td>
                    <td style="text-align: right; vertical-align: bottom; padding: 8px 0; font-weight: 800;">
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
</script>

<?= $this->endSection() ?>