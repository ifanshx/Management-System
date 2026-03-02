<?= $this->extend('layout/template') ?>
<?= $this->section('content') ?>

<style>
    /* =========================================================
       1. PAGE HEADER
       ========================================================= */
    .page-header { margin-bottom: 25px; display: flex; justify-content: flex-start; align-items: center;}
    
    .btn-back { color: var(--text-muted); text-decoration: none; font-size: 13px; font-weight: 800; display: inline-flex; align-items: center; gap: 8px; padding: 10px 20px; background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: 100px; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); box-shadow: 0 4px 10px rgba(0,0,0,0.02);}
    .btn-back:hover { color: #4f46e5; border-color: #4f46e5; transform: translateX(-5px); box-shadow: 0 4px 15px rgba(79, 70, 229, 0.15);}

    /* =========================================================
       2. PAPER DOCUMENT CARD (INVOICE STYLE)
       ========================================================= */
    .document-card { background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: 24px; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.08); max-width: 950px; margin: 0 auto; overflow: hidden; position: relative;}
    
    /* Aksen Pita Atas */
    .document-card::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 6px; background: linear-gradient(90deg, #4f46e5, #38bdf8); }

    /* =========================================================
       3. DOC HEADER (BRANDING & META)
       ========================================================= */
    .doc-header { padding: 50px 50px 40px 50px; display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 1px solid var(--border-subtle);}
    @media (max-width: 640px) { .doc-header { flex-direction: column; gap: 30px; padding: 30px; } }
    
    .company-brand { display: flex; flex-direction: column; gap: 5px; }
    .company-brand h2 { margin: 0; font-size: 26px; font-weight: 900; color: #4f46e5; display: flex; align-items: center; gap: 10px; letter-spacing: -0.5px;}
    .company-brand p { margin: 0; font-size: 13px; color: var(--text-muted); font-weight: 600;}

    .doc-meta { text-align: right; display: flex; flex-direction: column; align-items: flex-end; gap: 8px;}
    @media (max-width: 640px) { .doc-meta { text-align: left; align-items: flex-start; } }
    .doc-meta h1 { margin: 0; font-size: 36px; font-weight: 900; color: var(--text-main); letter-spacing: -1.5px; line-height: 1; text-transform: uppercase;}
    .doc-meta p { margin: 0; font-size: 15px; color: var(--text-muted); font-family: 'Space Mono', monospace; font-weight: 800; background: var(--bg-base); padding: 6px 12px; border-radius: 8px; border: 1px solid var(--border-subtle);}

    .status-badge { display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; border-radius: 12px; font-size: 12px; font-weight: 900; text-transform: uppercase; margin-top: 5px; letter-spacing: 0.5px;}
    .bg-ordered { background: rgba(245, 158, 11, 0.1); color: #d97706; border: 1px solid rgba(245, 158, 11, 0.2);}
    .bg-received { background: rgba(16, 185, 129, 0.1); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.2);}

    /* =========================================================
       4. DOC INFO (SUPPLIER & DETAILS)
       ========================================================= */
    .doc-info { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; padding: 40px 50px; background: rgba(0,0,0,0.015);}
    html.dark .doc-info { background: rgba(255,255,255,0.01); }
    @media (max-width: 640px) { .doc-info { grid-template-columns: 1fr; padding: 30px; } }
    
    .info-block { background: var(--bg-surface); padding: 25px; border-radius: 16px; border: 1px solid var(--border-subtle); box-shadow: 0 4px 10px rgba(0,0,0,0.02);}
    .info-block h4 { margin: 0 0 15px 0; font-size: 11px; font-weight: 900; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px; display: flex; align-items: center; gap: 6px;}
    
    .info-block p { margin: 0 0 10px 0; font-size: 16px; color: var(--text-main); font-weight: 800;}
    .info-block span { font-size: 13px; color: var(--text-muted); display: flex; line-height: 1.6;}
    .info-block span b { width: 50px; display: inline-block; color: var(--text-main); font-weight: 700;}

    .meta-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-top: 5px;}
    .meta-item { background: var(--bg-base); padding: 12px; border-radius: 12px; border: 1px solid var(--border-subtle);}
    .meta-item .m-label { font-size: 10px; font-weight: 800; color: var(--text-muted); text-transform: uppercase; display: block; margin-bottom: 4px;}
    .meta-item .m-val { font-size: 13px; font-weight: 800; color: var(--text-main); display: flex; align-items: center; gap: 6px;}

    /* =========================================================
       5. DOC ITEMS (TABLE)
       ========================================================= */
    .doc-items { padding: 40px 50px; }
    @media (max-width: 640px) { .doc-items { padding: 20px; } }

    table { width: 100%; border-collapse: collapse; }
    th { text-align: left; padding: 15px 20px; font-size: 11px; font-weight: 900; text-transform: uppercase; color: var(--text-muted); border-bottom: 2px solid var(--border-subtle); background: var(--bg-base); letter-spacing: 0.5px;}
    td { padding: 20px; border-bottom: 1px dashed var(--border-subtle); color: var(--text-main); font-size: 14px; font-weight: 600; vertical-align: top;}
    
    .sku-badge { background: rgba(79, 70, 229, 0.08); color: #4f46e5; padding: 6px 12px; border-radius: 8px; font-family: 'Space Mono', monospace; font-size: 12px; font-weight: 800; display: inline-block; border: 1px dashed rgba(79, 70, 229, 0.3);}

    /* =========================================================
       6. DOC TOTAL & FOOTER
       ========================================================= */
    .doc-total { padding: 40px 50px; background: var(--bg-base); display: flex; justify-content: flex-end; border-top: 1px solid var(--border-subtle);}
    @media (max-width: 640px) { .doc-total { padding: 30px 20px; justify-content: center; } }
    
    .total-box { width: 100%; max-width: 380px; background: var(--bg-surface); padding: 30px; border-radius: 20px; border: 1px solid var(--border-subtle); box-shadow: 0 10px 25px -5px rgba(0,0,0,0.05);}
    .total-line { display: flex; justify-content: space-between; margin-bottom: 15px; font-size: 14px; color: var(--text-muted); font-weight: 700; align-items: center;}
    .total-line span:last-child { color: var(--text-main); font-family: 'Space Mono', monospace;}
    
    .total-grand { display: flex; justify-content: space-between; align-items: center; margin-top: 20px; padding-top: 20px; border-top: 2px dashed var(--border-subtle); font-size: 22px; font-weight: 900; color: #4f46e5;}
    .total-grand span:last-child { font-family: 'Space Mono', monospace; letter-spacing: -1px;}

    /* Watermark / Stamp */
    .watermark { text-align: center; padding: 20px; font-size: 11px; color: var(--text-muted); font-weight: 700; text-transform: uppercase; letter-spacing: 2px; opacity: 0.6;}
</style>

<div style="max-width: 950px; margin: 0 auto; padding-bottom: 50px;">
    
    <div class="page-header">
        <a href="<?= base_url('/procurement') ?>" class="btn-back">
            <i class="ph-bold ph-arrow-left" style="font-size: 16px;"></i> Kembali ke Dasbor Logistik
        </a>
    </div>

    <div class="document-card">
        <div class="doc-header">
            <div class="company-brand">
                <h2><i class="ph-fill ph-factory"></i> Noric Exhaust ERP</h2>
                <p>PT. Noric Manufaktur Nusantara</p>
            </div>
            <div class="doc-meta">
                <h1>Purchase Order</h1>
                <p><?= esc($po['po_number']) ?></p>
                <?php if($po['status'] == 'ORDERED'): ?>
                    <div class="status-badge bg-ordered"><i class="ph-bold ph-truck"></i> Menunggu Kiriman Vendor</div>
                <?php else: ?>
                    <div class="status-badge bg-received"><i class="ph-bold ph-check-circle"></i> Selesai & Diterima Gudang</div>
                <?php endif; ?>
            </div>
        </div>

        <div class="doc-info">
            <div class="info-block">
                <h4><i class="ph-fill ph-buildings" style="font-size: 16px; color: #4f46e5;"></i> Kepada (Vendor Tujuan):</h4>
                <p><?= esc($po['supplier_name']) ?></p>
                <span><b>PIC</b> : <?= esc($po['contact_person'] ?? '-') ?></span>
                <span><b>Telp</b> : <?= esc($po['phone'] ?? '-') ?></span>
                <span style="margin-top: 10px; padding-top: 10px; border-top: 1px solid var(--border-subtle); display: block;">
                    <?= esc($po['address'] ?? 'Alamat operasional tidak dicatat dalam sistem.') ?>
                </span>
            </div>
            
            <div class="info-block">
                <h4><i class="ph-fill ph-info" style="font-size: 16px; color: #4f46e5;"></i> Detail Pemesanan:</h4>
                <div class="meta-grid">
                    <div class="meta-item">
                        <span class="m-label">Tanggal Pesan</span>
                        <span class="m-val"><i class="ph-bold ph-calendar-blank"></i> <?= date('d M Y', strtotime($po['po_date'])) ?></span>
                    </div>
                    <div class="meta-item">
                        <span class="m-label">Metode Pembayaran</span>
                        <span class="m-val"><i class="ph-bold ph-bank"></i> Bank Transfer</span>
                    </div>
                    <div class="meta-item">
                        <span class="m-label">Mata Uang</span>
                        <span class="m-val"><i class="ph-bold ph-coins"></i> IDR (Rupiah)</span>
                    </div>
                    <div class="meta-item">
                        <span class="m-label">Dibuat Oleh</span>
                        <span class="m-val"><i class="ph-bold ph-user"></i> Procurement</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="doc-items">
            <div style="overflow-x: auto; border: 1px solid var(--border-subtle); border-radius: 16px;">
                <table>
                    <thead>
                        <tr>
                            <th style="width: 45%;">Deskripsi Material (Bahan Baku)</th>
                            <th style="text-align: right; width: 15%;">Kuantitas</th>
                            <th style="text-align: right; width: 20%;">Harga Satuan</th>
                            <th style="text-align: right; width: 20%;">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($items as $item): ?>
                        <tr>
                            <td>
                                <div style="display: flex; flex-direction: column; gap: 8px;">
                                    <span style="font-weight: 900; font-size: 14px;"><?= esc($item['material_name'] ?? 'Material Tidak Diketahui') ?></span>
                                    <span class="sku-badge" style="width: fit-content;"><?= esc($item['rm_sku']) ?></span>
                                </div>
                            </td>
                            <td style="text-align: right; font-family: 'Space Mono', monospace; font-weight: 800; font-size: 15px;">
                                <?= floatval($item['qty']) ?> <span style="font-size: 11px; color: var(--text-muted); font-family: 'Plus Jakarta Sans', sans-serif; margin-left: 2px;"><?= esc($item['unit'] ?? 'Unit') ?></span>
                            </td>
                            <td style="text-align: right; font-family: 'Space Mono', monospace;">
                                Rp <?= number_format($item['unit_price'], 0, ',', '.') ?>
                            </td>
                            <td style="text-align: right; font-family: 'Space Mono', monospace; font-weight: 900; color: var(--text-main); font-size: 15px;">
                                Rp <?= number_format($item['subtotal'], 0, ',', '.') ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="doc-total">
            <div class="total-box">
                <div class="total-line">
                    <span>Subtotal Barang:</span>
                    <span>Rp <?= number_format($po['total_amount'], 0, ',', '.') ?></span>
                </div>
                <div class="total-line">
                    <span>Pajak (PPN 0%):</span>
                    <span>Rp 0</span>
                </div>
                <div class="total-line">
                    <span>Biaya Pengiriman:</span>
                    <span>Rp 0</span>
                </div>
                <div class="total-grand">
                    <span>TOTAL PO</span>
                    <span>Rp <?= number_format($po['total_amount'], 0, ',', '.') ?></span>
                </div>
            </div>
        </div>

        <div class="watermark">
            <i class="ph-bold ph-check-circle" style="font-size: 14px; display: block; margin-bottom: 5px;"></i>
            Dokumen Resmi Diterbitkan Oleh Sistem ERP Noric
        </div>
    </div>
</div>

<?= $this->endSection() ?>