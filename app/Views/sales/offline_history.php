<?= $this->extend('layout/template') ?>
<?= $this->section('content') ?>

<style>
    /* =========================================================
       1. AMBIENT GLOW & PAGE HEADER
       ========================================================= */
    :root {
        --shadow-card: 0 10px 40px -15px rgba(0,0,0,0.05);
        --shadow-hover: 0 20px 40px -10px rgba(0,0,0,0.08);
        --transition-smooth: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    }

    .ambient-glow { position: absolute; top: -150px; left: 50%; transform: translateX(-50%); width: 100%; height: 500px; background: radial-gradient(ellipse at top, rgba(59, 130, 246, 0.08) 0%, transparent 60%); z-index: 0; pointer-events: none;}
    html.dark .ambient-glow { background: radial-gradient(ellipse at top, rgba(59, 130, 246, 0.15) 0%, transparent 60%); }

    .page-header { position: relative; z-index: 1; margin-bottom: 30px; display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 20px; width: 100%;}
    
    .page-title { display: flex; align-items: center; gap: 18px;}
    .title-icon { width: 56px; height: 56px; border-radius: 18px; background: linear-gradient(135deg, #3b82f6, #1d4ed8); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 28px; box-shadow: 0 10px 25px -5px rgba(59, 130, 246, 0.4);}
    .title-text h1 { font-size: 30px; font-weight: 900; color: var(--text-main); margin: 0; letter-spacing: -1px; line-height: 1.1;}
    .title-text p { font-size: 14px; color: var(--text-muted); font-weight: 600; margin: 4px 0 0 0;}

    .btn-back { position: relative; z-index: 1; color: var(--text-muted); text-decoration: none; font-size: 13px; font-weight: 800; display: inline-flex; align-items: center; gap: 8px; padding: 12px 24px; background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: 14px; transition: var(--transition-smooth); box-shadow: 0 4px 10px rgba(0,0,0,0.02); margin-bottom: 25px;}
    .btn-back:hover { color: #3b82f6; border-color: #3b82f6; transform: translateX(-5px); box-shadow: 0 8px 20px -5px rgba(59, 130, 246, 0.2);}

    /* =========================================================
       2. FULL WIDTH TABLE BENTO GRID
       ========================================================= */
    .table-card { position: relative; z-index: 1; background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: 24px; box-shadow: var(--shadow-card); overflow: hidden; margin-bottom: 40px; width: 100%; transition: var(--transition-smooth);}
    .table-card:hover { border-color: rgba(59, 130, 246, 0.3); box-shadow: var(--shadow-hover); }
    
    .table-responsive { width: 100%; overflow-x: auto; }
    table { width: 100%; border-collapse: separate; border-spacing: 0; white-space: nowrap; }
    th { text-align: left; padding: 18px 20px; font-size: 11px; font-weight: 900; text-transform: uppercase; color: var(--text-muted); background: var(--bg-base); border-bottom: 2px solid var(--border-subtle); letter-spacing: 0.5px;}
    td { text-align: left; padding: 18px 20px; border-bottom: 1px dashed var(--border-subtle); color: var(--text-main); font-size: 14px; font-weight: 700; vertical-align: middle; transition: var(--transition-smooth);}
    
    tr:last-child td { border-bottom: none; }
    tr:hover td { background: rgba(59, 130, 246, 0.03); }

    /* Custom Badges */
    .inv-badge { padding: 6px 12px; border-radius: 8px; font-family: 'Space Mono', monospace; font-weight: 900; font-size: 13px; display: inline-block; background: rgba(59, 130, 246, 0.08); color: #3b82f6; border: 1px dashed rgba(59, 130, 246, 0.3);}
    
    .pay-badge { padding: 6px 12px; border-radius: 8px; font-size: 11px; font-weight: 900; display: inline-flex; align-items: center; gap: 6px; text-transform: uppercase; letter-spacing: 0.5px;}
    .pay-cash { background: rgba(16, 185, 129, 0.08); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.3); }
    .pay-bank { background: rgba(139, 92, 246, 0.08); color: #8b5cf6; border: 1px solid rgba(139, 92, 246, 0.3); }

    .btn-detail { background: var(--bg-base); color: #3b82f6; border: 1px solid var(--border-subtle); padding: 8px 16px; border-radius: 10px; font-size: 13px; font-weight: 800; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; gap: 8px; transition: var(--transition-smooth);}
    .btn-detail:hover { background: #3b82f6; color: #fff; border-color: #3b82f6; transform: translateY(-2px); box-shadow: 0 6px 15px rgba(59, 130, 246, 0.3);}

    .empty-state { text-align: center; padding: 60px 20px; color: var(--text-muted); width: 100%;}
    .empty-state i { font-size: 64px; color: var(--border-subtle); margin-bottom: 20px; display: block; }
    .empty-state h3 { font-size: 18px; font-weight: 900; color: var(--text-main); margin-bottom: 8px; letter-spacing: -0.5px;}

    /* =========================================================
       3. RECEIPT MODAL (PREMIUM THERMAL PAPER STYLE)
       ========================================================= */
    .modal-overlay { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.6); backdrop-filter: blur(8px); z-index: 1000; display: none; justify-content: center; align-items: center; opacity: 0; transition: opacity 0.3s;}
    .modal-overlay.active { display: flex; opacity: 1; }
    
    .modal-box { background: transparent; border-radius: 0; width: 100%; max-width: 450px; padding: 0; transform: scale(0.95) translateY(20px); transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1); border: none; box-shadow: none;}
    .modal-overlay.active .modal-box { transform: scale(1) translateY(0); }
    
    .modal-header-actions { display: flex; justify-content: flex-end; margin-bottom: 15px; }
    .btn-close { background: var(--bg-surface); border: 1px solid var(--border-subtle); width: 44px; height: 44px; border-radius: 50%; font-size: 20px; color: var(--text-muted); cursor: pointer; display: flex; align-items: center; justify-content: center; transition: 0.2s; box-shadow: 0 4px 10px rgba(0,0,0,0.1);}
    .btn-close:hover { background: #ef4444; color: #fff; border-color: #ef4444; transform: rotate(90deg);}

    .receipt-paper { 
        background: #ffffff; color: #0f172a; padding: 35px 30px; 
        font-family: 'Space Mono', monospace; 
        box-shadow: 0 15px 35px rgba(0,0,0,0.2); 
        max-height: 600px; overflow-y: auto;
        position: relative; width: 100%;
        border-radius: 8px;
    }
    
    /* Zigzag border effect at top and bottom to mimic thermal tear */
    .receipt-paper::before, .receipt-paper::after {
        content: ""; position: absolute; left: 0; right: 0; height: 6px;
        background-size: 12px 100%; z-index: 10;
    }
    .receipt-paper::before { top: 0; background-image: linear-gradient(135deg, rgba(0,0,0,0.5) 25%, transparent 25%), linear-gradient(225deg, rgba(0,0,0,0.5) 25%, transparent 25%); background-position: 0 0; }
    .receipt-paper::after { bottom: 0; background-image: linear-gradient(315deg, rgba(0,0,0,0.5) 25%, transparent 25%), linear-gradient(45deg, rgba(0,0,0,0.5) 25%, transparent 25%); background-position: 0 0; }

    .receipt-paper::-webkit-scrollbar { width: 5px; }
    .receipt-paper::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }

    .detail-item { display: flex; justify-content: space-between; align-items: flex-start; padding: 15px 0; border-bottom: 1px dashed #cbd5e1; font-size: 14px;}
    .detail-item:last-child { border-bottom: none; }
</style>

<div class="ambient-glow"></div>

<div>
    <a href="<?= base_url('/sales/offline') ?>" class="btn-back">
        <i class="ph-bold ph-arrow-left" style="font-size: 18px;"></i> Kembali ke Mesin Kasir (POS)
    </a>
    
    <div class="page-header">
        <div class="page-title">
            <div class="title-icon"><i class="ph-fill ph-clock-counter-clockwise"></i></div>
            <div class="title-text">
                <h1>Riwayat Transaksi Penjualan</h1>
                <p>Log lengkap dan rekam tagihan dari seluruh transaksi POS Offline pabrik.</p>
            </div>
        </div>
    </div>
</div>

<div class="table-card">
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Waktu Pembayaran</th>
                    <th>Nomor Invoice (Struk)</th>
                    <th>Nama Pelanggan</th>
                    <th style="text-align: right;">Total Nilai (Rp)</th>
                    <th style="text-align: center;">Metode Bayar</th>
                    <th style="text-align: center;">Kasir Bertugas</th>
                    <th style="text-align: center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($sales)): ?>
                    <tr>
                        <td colspan="7">
                            <div class="empty-state">
                                <i class="ph-fill ph-receipt"></i>
                                <h3>Belum Ada Riwayat Transaksi</h3>
                                <p style="font-size: 13px;">Silakan lakukan penjualan pertama Anda di menu POS Kasir Offline.</p>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
                <?php foreach($sales as $s): ?>
                <tr>
                    <td>
                        <div style="font-weight: 900; font-size: 14px; margin-bottom: 6px;">
                            <?= date('d M Y', strtotime($s['created_at'])) ?>
                        </div>
                        <div style="font-size: 12px; color: var(--text-muted); font-family: 'Space Mono', monospace; font-weight: 800; display: flex; align-items: center; gap: 4px;">
                            <i class="ph-bold ph-clock"></i> Pukul <?= date('H:i', strtotime($s['created_at'])) ?>
                        </div>
                    </td>
                    <td><span class="inv-badge"><?= esc($s['invoice_no']) ?></span></td>
                    <td style="font-weight: 800;">
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <i class="ph-fill ph-user-circle" style="color: var(--border-subtle); font-size: 20px;"></i> 
                            <?= esc($s['customer_name']) ?>
                        </div>
                    </td>
                    <td style="text-align: right; font-family: 'Space Mono', monospace; font-weight: 900; color: #10b981; font-size: 15px;">
                        Rp <?= number_format($s['total_amount'], 0, ',', '.') ?>
                    </td>
                    <td style="text-align: center;">
                        <?php $isCash = (strpos(strtolower($s['payment_method']), 'cash') !== false || strpos(strtolower($s['payment_method']), 'tunai') !== false); ?>
                        <span class="pay-badge <?= $isCash ? 'pay-cash' : 'pay-bank' ?>">
                            <i class="ph-fill <?= $isCash ? 'ph-money' : 'ph-credit-card' ?>" style="font-size: 16px;"></i> <?= esc($s['payment_method']) ?>
                        </span>
                    </td>
                    <td style="text-align: center; font-size: 13px; color: var(--text-muted); font-weight: 800;">
                        <?= esc($s['cashier_name']) ?>
                    </td>
                    <td style="text-align: center;">
                        <button onclick="viewDetail('<?= esc($s['invoice_no']) ?>')" class="btn-detail" title="Lihat Salinan Struk Thermal">
                            <i class="ph-bold ph-printer" style="font-size: 18px;"></i> Cetak Ulang
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal-overlay" id="modalDetail">
    <div class="modal-box">
        <div class="modal-header-actions">
            <button class="btn-close" onclick="closeModal('modalDetail')" type="button"><i class="ph-bold ph-x"></i></button>
        </div>
        
        <div class="receipt-paper" id="detailContainer">
            <div style="text-align: center; color: #64748b; padding: 40px 0;">
                <i class="ph-bold ph-spinner-gap ph-spin" style="font-size: 32px; color: #3b82f6;"></i><br><br>
                <span style="font-weight: 700; font-family: 'Plus Jakarta Sans', sans-serif;">Menarik data dari Jurnal...</span>
            </div>
        </div>
    </div>
</div>

<script>
    function openModal(modalId) { document.getElementById(modalId).classList.add('active'); }
    function closeModal(modalId) { document.getElementById(modalId).classList.remove('active'); }
    
    // Tutup jika klik area luar modal
    window.onclick = function(e) { if (e.target.classList.contains('modal-overlay')) { e.target.classList.remove('active'); } }

    function formatRupiahJs(angka) {
        return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(angka);
    }

    function viewDetail(invoiceNo) {
        const container = document.getElementById('detailContainer');
        container.innerHTML = '<div style="text-align: center; color: #64748b; padding: 40px 0;"><i class="ph-bold ph-spinner-gap ph-spin" style="font-size: 32px; color: #3b82f6;"></i><br><br><span style="font-weight: 700; font-family: \'Plus Jakarta Sans\', sans-serif;">Menarik data dari Jurnal...</span></div>';
        
        openModal('modalDetail');

        fetch('<?= base_url('/sales/get_detail/') ?>' + invoiceNo, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(data => {
            if(data.length > 0) {
                let html = `
                    <div style="text-align: center; margin-bottom: 25px;">
                        <h2 style="margin: 0; font-size: 24px; font-weight: 900; letter-spacing: -1.5px; border-bottom: 2px dashed #000; padding-bottom: 12px; margin-bottom: 12px;">NORIC EXHAUST</h2>
                        <div style="font-size: 12px; font-family: sans-serif; font-weight: 800; letter-spacing: 1px;">SALINAN STRUK KASIR</div>
                    </div>
                    
                    <div style="font-size: 13px; font-weight: 700; margin-bottom: 20px; line-height: 2;">
                        <div style="display: flex; justify-content: space-between;"><span>No:</span><b>${invoiceNo}</b></div>
                    </div>
                    
                    <div style="border-top: 2px dashed #000; border-bottom: 2px dashed #000; padding: 10px 0; margin-bottom: 20px;">
                `;
                
                let grandTotal = 0;
                
                data.forEach(item => {
                    html += `
                        <div class="detail-item">
                            <div style="flex: 1; padding-right: 15px;">
                                <div style="font-weight: 900; color: #000; line-height: 1.4; margin-bottom: 6px; font-family: 'Plus Jakarta Sans', sans-serif;">${item.item_name}</div>
                                <div style="font-size: 12px; color: #444;">${item.qty} x ${formatRupiahJs(item.price)}</div>
                            </div>
                            <div style="font-weight: 900; font-size: 14px;">
                                ${formatRupiahJs(item.subtotal)}
                            </div>
                        </div>
                    `;
                    grandTotal += parseFloat(item.subtotal);
                });
                
                html += `
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 15px; font-size: 16px; font-weight: 900; color: #000;">
                        <span>TOTAL</span>
                        <span>${formatRupiahJs(grandTotal)}</span>
                    </div>
                    
                    <div style="text-align: center; margin-top: 40px; font-size: 11px; font-family: sans-serif; line-height: 1.6;">
                        <b>Terima Kasih Telah Berbelanja</b><br>
                        <span style="color: #666; font-weight: 600;">(Dokumen Terekam di Jurnal ERP)</span>
                    </div>
                    
                    <div style="text-align: center; margin-top: 25px;" class="no-print">
                        <button onclick="window.print()" style="background: #10b981; color: white; border: none; padding: 12px 24px; border-radius: 12px; font-weight: 800; cursor: pointer; font-family: 'Plus Jakarta Sans', sans-serif; display: inline-flex; align-items: center; gap: 8px;">
                            <i class="ph-bold ph-printer" style="font-size: 18px;"></i> Print Struk Thermal
                        </button>
                    </div>
                `;
                container.innerHTML = html;
            } else {
                container.innerHTML = '<div style="text-align: center; color: #ef4444; padding: 30px 0; font-family: \'Plus Jakarta Sans\', sans-serif; font-weight: 700;">Data rincian tidak ditemukan di database.</div>';
            }
        })
        .catch(err => {
            container.innerHTML = '<div style="text-align: center; color: #ef4444; padding: 30px 0; font-family: \'Plus Jakarta Sans\', sans-serif;"><i class="ph-bold ph-warning-circle" style="font-size: 40px; margin-bottom:15px;"></i><br><br><b>Koneksi Gagal</b><br>Gagal memuat data dari server.</div>';
        });
    }
</script>

<?= $this->endSection() ?>