<?= $this->extend('layout/template') ?>
<?= $this->section('content') ?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    /* =========================================================
       1. PAGE HEADER
       ========================================================= */
    .page-header { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 25px; flex-wrap: wrap; gap: 20px;}
    .page-title h1 { font-size: 26px; font-weight: 800; color: var(--text-main); margin-bottom: 5px; letter-spacing: -1px; display: flex; align-items: center; gap: 10px;}
    .page-title p { font-size: 13px; color: var(--text-muted); margin: 0;}
    
    .btn-back { color: var(--text-muted); text-decoration: none; font-size: 13px; font-weight: 800; display: inline-flex; align-items: center; gap: 6px; padding: 10px 20px; background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: 100px; transition: all 0.3s ease; box-shadow: 0 4px 10px rgba(0,0,0,0.02); margin-bottom: 15px;}
    .btn-back:hover { color: #3b82f6; border-color: #3b82f6; transform: translateX(-4px); box-shadow: 0 4px 15px rgba(59, 130, 246, 0.15);}

    /* =========================================================
       2. TABEL BENTO GRID (GAYA BARU YANG SANGAT RAPI)
       ========================================================= */
    .table-card { background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: 20px; box-shadow: var(--shadow-card); overflow: hidden; margin-bottom: 30px;}
    
    table { width: 100%; border-collapse: collapse; white-space: nowrap; }
    th { text-align: center; padding: 12px 15px; font-size: 11px; font-weight: 800; text-transform: uppercase; color: var(--text-muted); background: var(--bg-base); border-bottom: 1px solid var(--border-subtle); border-right: 1px solid var(--border-subtle); letter-spacing: 0.5px;}
    td { text-align: center; padding: 12px 15px; border-bottom: 1px solid var(--border-subtle); border-right: 1px solid var(--border-subtle); color: var(--text-main); font-size: 13px; font-weight: 600; vertical-align: middle; transition: background 0.2s;}
    
    th:first-child, td:first-child { text-align: left; }
    th:last-child, td:last-child { border-right: none; }
    
    tr:hover td { background: rgba(59, 130, 246, 0.02); }
    html.dark tr:hover td { background: rgba(59, 130, 246, 0.05); }

    /* Custom Badges (Disesuaikan agar lebih compact) */
    .inv-badge { padding: 4px 10px; border-radius: 6px; font-family: 'Space Mono', monospace; font-weight: 900; font-size: 12px; display: inline-block; background: rgba(59, 130, 246, 0.08); color: #3b82f6; border: 1px dashed rgba(59, 130, 246, 0.3);}
    
    .pay-badge { padding: 4px 10px; border-radius: 6px; font-size: 11px; font-weight: 800; display: inline-flex; align-items: center; gap: 4px; text-transform: uppercase;}
    .pay-cash { background: rgba(16, 185, 129, 0.1); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.2); }
    .pay-bank { background: rgba(139, 92, 246, 0.1); color: #8b5cf6; border: 1px solid rgba(139, 92, 246, 0.2); }

    .btn-detail { background: var(--bg-base); color: #3b82f6; border: 1px solid var(--border-subtle); padding: 6px 12px; border-radius: 8px; font-size: 12px; font-weight: 800; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; transition: 0.3s;}
    .btn-detail:hover { background: #3b82f6; color: #fff; border-color: #3b82f6; box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3); transform: translateY(-2px);}

    .empty-state { text-align: center; padding: 60px 20px; color: var(--text-muted); }
    .empty-state i { font-size: 48px; color: var(--border-subtle); margin-bottom: 10px; display: block; }

    /* =========================================================
       3. RECEIPT MODAL (THERMAL PAPER STYLE)
       ========================================================= */
    .modal-overlay { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.6); backdrop-filter: blur(6px); z-index: 1000; display: none; justify-content: center; align-items: center; opacity: 0; transition: opacity 0.3s;}
    .modal-overlay.active { display: flex; opacity: 1; }
    
    .modal-box { background: var(--bg-surface); border-radius: 24px; width: 100%; max-width: 480px; padding: 35px 40px; transform: scale(0.95) translateY(20px); transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1); box-shadow: 0 30px 60px -15px rgba(0,0,0,0.4); border: 1px solid var(--border-subtle);}
    .modal-overlay.active .modal-box { transform: scale(1) translateY(0); }
    
    .modal-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px; border-bottom: 2px dashed var(--border-subtle); padding-bottom: 15px;}
    .btn-close { background: var(--bg-base); border: 1px solid var(--border-subtle); width: 34px; height: 34px; border-radius: 50%; font-size: 16px; color: var(--text-muted); cursor: pointer; display: flex; align-items: center; justify-content: center; transition: 0.2s;}
    .btn-close:hover { background: #ef4444; color: #fff; border-color: #ef4444; transform: rotate(90deg);}

    .receipt-paper { background: var(--bg-base); padding: 20px; border-radius: 12px; border: 1px solid var(--border-subtle); box-shadow: inset 0 0 10px rgba(0,0,0,0.02); max-height: 450px; overflow-y: auto;}
    .receipt-paper::-webkit-scrollbar { width: 4px; }
    .receipt-paper::-webkit-scrollbar-thumb { background: var(--border-subtle); border-radius: 10px; }

    .detail-item { display: flex; justify-content: space-between; align-items: flex-start; padding: 12px 0; border-bottom: 1px dashed var(--border-subtle); font-size: 13px;}
    .detail-item:last-child { border-bottom: none; }
</style>

<div>
    <a href="<?= base_url('/sales/offline') ?>" class="btn-back">
        <i class="ph-bold ph-arrow-left"></i> Kembali ke Mesin Kasir
    </a>
    
    <div class="page-header">
        <div class="page-title">
            <h1><i class="ph-fill ph-clock-counter-clockwise" style="color: #3b82f6;"></i> Riwayat Transaksi Penjualan</h1>
            <p>Log lengkap dan detail tagihan dari seluruh transaksi mesin kasir pabrik.</p>
        </div>
    </div>
</div>

<div class="table-card">
    <div style="overflow-x: auto;">
        <table>
            <thead>
                <tr>
                    <th>Waktu Pembayaran</th>
                    <th>Nomor Invoice (Struk)</th>
                    <th>Nama Pelanggan</th>
                    <th style="text-align: right;">Total Nilai (Rp)</th>
                    <th>Metode Bayar</th>
                    <th>Kasir Bertugas</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($sales)): ?>
                    <tr>
                        <td colspan="7">
                            <div class="empty-state">
                                <i class="ph-fill ph-receipt"></i>
                                <div style="color: var(--text-main); font-weight: 800; margin-bottom: 5px;">Belum Ada Riwayat</div>
                                <div style="font-size: 12px;">Silakan lakukan transaksi pertama Anda di menu POS Kasir.</div>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
                <?php foreach($sales as $s): ?>
                <tr>
                    <td>
                        <div style="font-weight: 800; font-size: 13px;">
                            <?= date('d M Y', strtotime($s['created_at'])) ?>
                        </div>
                        <div style="font-size: 11px; color: var(--text-muted); font-family: 'Space Mono', monospace; font-weight: 700; margin-top: 2px;">
                            <i class="ph-bold ph-clock"></i> <?= date('H:i:s', strtotime($s['created_at'])) ?>
                        </div>
                    </td>
                    <td><span class="inv-badge"><?= esc($s['invoice_no']) ?></span></td>
                    <td style="font-weight: 700;"><?= esc($s['customer_name']) ?></td>
                    <td style="text-align: right; font-family: 'Space Mono', monospace; font-weight: 900; color: #10b981; font-size: 14px;">
                        Rp <?= number_format($s['total_amount'], 0, ',', '.') ?>
                    </td>
                    <td>
                        <?php $isCash = (strpos(strtolower($s['payment_method']), 'cash') !== false || strpos(strtolower($s['payment_method']), 'tunai') !== false); ?>
                        <span class="pay-badge <?= $isCash ? 'pay-cash' : 'pay-bank' ?>">
                            <i class="ph-fill <?= $isCash ? 'ph-money' : 'ph-credit-card' ?>" style="font-size: 14px;"></i> <?= esc($s['payment_method']) ?>
                        </span>
                    </td>
                    <td style="font-size: 12px; color: var(--text-muted); font-weight: 700;">
                        <i class="ph-fill ph-user-circle" style="font-size: 14px; vertical-align: middle;"></i> <?= esc($s['cashier_name']) ?>
                    </td>
                    <td>
                        <button onclick="viewDetail('<?= esc($s['invoice_no']) ?>')" class="btn-detail" title="Lihat Salinan Struk">
                            <i class="ph-bold ph-printer"></i> Struk
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal-overlay" id="modalDetail">
    <div class="modal-box" style="border-top: 6px solid #3b82f6;">
        <div class="modal-header">
            <div>
                <div style="font-size: 11px; font-weight: 900; color: var(--text-muted); text-transform: uppercase; margin-bottom: 4px; letter-spacing: 0.5px;">Salinan Struk Belanja</div>
                <div style="font-family: 'Space Mono', monospace; font-weight: 900; font-size: 18px; color: #3b82f6;" id="detailInvNo">INV-XXX</div>
            </div>
            <button class="btn-close" onclick="closeModal('modalDetail')" type="button"><i class="ph-bold ph-x"></i></button>
        </div>
        
        <div class="receipt-paper" id="detailContainer">
            <div style="text-align: center; color: var(--text-muted); padding: 30px 0;">
                <i class="ph-bold ph-spinner-gap ph-spin" style="font-size: 28px;"></i><br><br>Memuat rincian transaksi...
            </div>
        </div>
    </div>
</div>

<script>
    function openModal(modalId) { document.getElementById(modalId).classList.add('active'); }
    function closeModal(modalId) { document.getElementById(modalId).classList.remove('active'); }
    window.onclick = function(e) { if (e.target.classList.contains('modal-overlay')) { e.target.classList.remove('active'); } }

    function formatRupiahJs(angka) {
        return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(angka);
    }

    function viewDetail(invoiceNo) {
        document.getElementById('detailInvNo').innerText = invoiceNo;
        const container = document.getElementById('detailContainer');
        container.innerHTML = '<div style="text-align: center; color: var(--text-muted); padding: 30px 0;"><i class="ph-bold ph-spinner-gap ph-spin" style="font-size: 28px;"></i><br><br>Memuat rincian transaksi...</div>';
        
        openModal('modalDetail');

        fetch('<?= base_url('/sales/get_detail/') ?>' + invoiceNo, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(data => {
            if(data.length > 0) {
                let html = '';
                let grandTotal = 0;
                
                data.forEach(item => {
                    html += `
                        <div class="detail-item">
                            <div style="flex: 1; padding-right: 15px;">
                                <div style="font-weight: 800; color: var(--text-main); line-height: 1.4; margin-bottom: 4px;">${item.item_name}</div>
                                <div style="font-size: 11px; color: var(--text-muted); font-family: 'Space Mono', monospace; font-weight: 700;">${item.qty} x ${formatRupiahJs(item.price)}</div>
                            </div>
                            <div style="font-weight: 900; font-family: 'Space Mono', monospace; color: #10b981; font-size: 13px;">
                                ${formatRupiahJs(item.subtotal)}
                            </div>
                        </div>
                    `;
                    grandTotal += parseFloat(item.subtotal);
                });
                
                html += `
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 15px; padding-top: 15px; border-top: 2px dashed var(--border-subtle); font-size: 14px; font-weight: 900; color: var(--text-main);">
                        <span>TOTAL TAGIHAN</span>
                        <span style="font-family: 'Space Mono', monospace; color: #3b82f6; font-size: 16px;">${formatRupiahJs(grandTotal)}</span>
                    </div>
                    <div style="text-align: center; margin-top: 25px; font-size: 10px; color: var(--text-muted); font-weight: 800; text-transform: uppercase; letter-spacing: 1px; background: rgba(0,0,0,0.03); padding: 8px; border-radius: 8px;">
                        Lunas & Tercatat di Jurnal
                    </div>
                `;
                container.innerHTML = html;
            } else {
                container.innerHTML = '<div style="text-align: center; color: var(--text-muted); padding: 30px 0;">Data rincian tidak ditemukan.</div>';
            }
        })
        .catch(err => {
            container.innerHTML = '<div style="text-align: center; color: #ef4444; padding: 30px 0;"><i class="ph-bold ph-warning" style="font-size: 28px; margin-bottom:10px;"></i><br>Gagal memuat data dari server.</div>';
        });
    }
</script>

<?= $this->endSection() ?>