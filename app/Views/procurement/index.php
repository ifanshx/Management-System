<?= $this->extend('layout/template') ?>
<?= $this->section('content') ?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    :root {
        --brand: #4f46e5; --brand-dark: #4338ca; --brand-soft: rgba(79, 70, 229, 0.1);
        --success: #10b981; --success-soft: rgba(16, 185, 129, 0.1);
        --warning: #f59e0b; --warning-soft: rgba(245, 158, 11, 0.1);
        --danger: #ef4444; --danger-soft: rgba(239, 68, 68, 0.1);
        --info: #0ea5e9; --info-soft: rgba(14, 165, 233, 0.1);
    }
    .swal2-custom-radius { border-radius: 24px !important; font-family: 'Plus Jakarta Sans', sans-serif; }
    
    .page-header { display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 20px; margin-bottom: 25px; }
    .page-title { display: flex; align-items: center; gap: 16px; }
    .page-icon { width: 56px; height: 56px; border-radius: 18px; background: linear-gradient(135deg, var(--brand), var(--info)); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 28px; box-shadow: 0 10px 25px -5px rgba(79, 70, 229, 0.4); }
    .page-title h1 { font-size: 28px; font-weight: 900; color: var(--text-main); margin: 0; letter-spacing: -0.5px; }
    .page-title p { font-size: 13px; color: var(--text-muted); font-weight: 600; margin: 4px 0 0 0; }

    .kpi-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 30px; }
    @media (max-width: 1024px) { .kpi-grid { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 640px) { .kpi-grid { grid-template-columns: 1fr; } }
    .kpi-card { background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: 20px; padding: 20px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 10px 30px -10px rgba(0,0,0,0.05); transition: 0.3s; }
    .kpi-card:hover { transform: translateY(-3px); border-color: var(--brand); box-shadow: 0 15px 35px -10px rgba(79, 70, 229, 0.15); }
    .kpi-info h4 { margin: 0 0 5px 0; font-size: 12px; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; }
    .kpi-info h2 { margin: 0; font-size: 26px; font-weight: 900; color: var(--text-main); font-family: 'Space Mono', monospace; letter-spacing: -1px; }
    .kpi-icon-box { width: 48px; height: 48px; border-radius: 14px; display: flex; justify-content: center; align-items: center; font-size: 24px; }

    .tab-nav { display: inline-flex; background: var(--bg-surface); padding: 6px; border-radius: 16px; border: 1px solid var(--border-subtle); box-shadow: 0 4px 15px rgba(0,0,0,0.02); margin-bottom: 20px; }
    .tab-btn { padding: 10px 20px; font-size: 13px; font-weight: 800; border-radius: 12px; border: none; background: transparent; color: var(--text-muted); cursor: pointer; transition: 0.3s; display: flex; align-items: center; gap: 8px; }
    .tab-btn.active { background: var(--bg-base); color: var(--brand); box-shadow: 0 2px 8px rgba(0,0,0,0.05); border: 1px solid var(--brand-soft); }
    .tab-content { display: none; animation: fadeUp .4s ease; }
    .tab-content.active { display: block; }
    @keyframes fadeUp { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

    .bento-card { background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: 24px; padding: 25px; box-shadow: 0 15px 35px -15px rgba(0,0,0,0.1); overflow: hidden; }
    .bento-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 15px; }
    
    table { width: 100%; border-collapse: collapse; white-space: nowrap; }
    th { padding: 15px 18px; font-size: 11px; font-weight: 900; text-transform: uppercase; color: var(--text-muted); border-bottom: 2px solid var(--border-subtle); background: var(--bg-base); }
    td { padding: 15px 18px; border-bottom: 1px dashed var(--border-subtle); color: var(--text-main); font-size: 13px; font-weight: 700; vertical-align: middle; }
    tr:last-child td { border-bottom: none; }
    tr:hover td { background: var(--brand-soft); }
    th:first-child, td:first-child { text-align: left; }

    .status-badge { padding: 6px 12px; border-radius: 999px; font-size: 10px; font-weight: 900; text-transform: uppercase; letter-spacing: 0.5px; display: inline-flex; align-items: center; gap: 6px; }
    .btn-action { padding: 8px 14px; border-radius: 10px; font-size: 12px; font-weight: 800; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; transition: 0.3s; border: none; cursor: pointer; }
    .btn-action:hover { transform: translateY(-2px); }
    .btn-primary { background: linear-gradient(135deg, var(--brand), var(--brand-dark)); color: #fff; padding: 12px 20px; border-radius: 14px; font-weight: 900; font-size: 13px; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 8px 20px -6px rgba(79, 70, 229, 0.5); transition: 0.3s; }
    .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 12px 25px -6px rgba(79, 70, 229, 0.6); color: #fff; }

    .modal-overlay { position: fixed; inset: 0; background: rgba(15,23,42,0.6); backdrop-filter: blur(8px); z-index: 9999; display: none; justify-content: center; align-items: center; opacity: 0; transition: 0.3s; padding: 20px; }
    .modal-overlay.active { display: flex; opacity: 1; }
    .modal-box { background: var(--bg-surface); border-radius: 28px; width: 100%; max-width: 480px; padding: 35px; transform: scale(0.95) translateY(20px); transition: 0.4s cubic-bezier(0.16, 1, 0.3, 1); box-shadow: 0 30px 60px -15px rgba(0,0,0,0.4); border: 1px solid var(--border-subtle); }
    .modal-overlay.active .modal-box { transform: scale(1) translateY(0); }
    .input-wrapper { display: flex; align-items: stretch; background: var(--bg-base); border: 1px solid var(--border-subtle); border-radius: 14px; overflow: hidden; transition: .25s ease; margin-bottom: 15px;}
    .input-wrapper:focus-within { border-color: var(--brand); box-shadow: 0 0 0 4px var(--brand-soft); }
    .input-wrapper input, .input-wrapper select, .input-wrapper textarea { flex: 1; background: transparent; border: none; color: var(--text-main); padding: 14px 16px; font-size: 13px; font-weight: 700; outline: none; font-family: inherit; width: 100%; }
</style>

<?php
    $totalPO = count($purchaseOrders);
    $orderedCount = 0; $receivedCount = 0; $totalOutstanding = 0;
    foreach ($purchaseOrders as $po) {
        if ($po['status'] === 'ORDERED') $orderedCount++;
        if ($po['status'] === 'RECEIVED') $receivedCount++;
        if ($po['payment_status'] !== 'PAID') {
            $totalOutstanding += (($po['total_amount'] ?? 0) - ($po['paid_amount'] ?? 0));
        }
    }
?>

<div class="page-header">
    <div class="page-title">
        <div class="page-icon"><i class="ph-fill ph-shopping-cart"></i></div>
        <div>
            <h1>Procurement & Logistik</h1>
            <p>Kelola pemesanan (Purchase Order), vendor, dan pembayaran tagihan.</p>
        </div>
    </div>
</div>

<div class="kpi-grid">
    <div class="kpi-card" style="border-bottom: 4px solid var(--brand);">
        <div class="kpi-info"><h4>Total PO Terbit</h4><h2><?= number_format($totalPO) ?></h2></div>
        <div class="kpi-icon-box" style="background: var(--brand-soft); color: var(--brand);"><i class="ph-fill ph-files"></i></div>
    </div>
    <div class="kpi-card" style="border-bottom: 4px solid var(--warning);">
        <div class="kpi-info"><h4>Menunggu Truk</h4><h2><?= number_format($orderedCount) ?></h2></div>
        <div class="kpi-icon-box" style="background: var(--warning-soft); color: var(--warning);"><i class="ph-fill ph-truck"></i></div>
    </div>
    <div class="kpi-card" style="border-bottom: 4px solid var(--success);">
        <div class="kpi-info"><h4>Selesai Diterima</h4><h2><?= number_format($receivedCount) ?></h2></div>
        <div class="kpi-icon-box" style="background: var(--success-soft); color: var(--success);"><i class="ph-fill ph-package"></i></div>
    </div>
    <div class="kpi-card" style="border-bottom: 4px solid var(--danger);">
        <div class="kpi-info"><h4>Hutang Outstanding</h4><h2><span style="font-size:16px;">Rp</span> <?= number_format($totalOutstanding, 0, ',', '.') ?></h2></div>
        <div class="kpi-icon-box" style="background: var(--danger-soft); color: var(--danger);"><i class="ph-fill ph-wallet"></i></div>
    </div>
</div>

<div class="bento-header">
    <div class="tab-nav">
        <button class="tab-btn active" onclick="switchTab('po', this)"><i class="ph-bold ph-receipt"></i> Dokumen PO</button>
        <button class="tab-btn" onclick="switchTab('supplier', this)"><i class="ph-bold ph-buildings"></i> Vendor / Supplier</button>
    </div>
</div>

<div id="tab-po" class="tab-content active">
    <div class="bento-card">
        <div class="bento-header">
            <h3 style="font-size:16px; font-weight:900; margin:0;"><i class="ph-fill ph-files" style="color:var(--brand);"></i> Daftar Purchase Order</h3>
            <a href="<?= base_url('/procurement/create_po') ?>" class="btn-primary"><i class="ph-bold ph-plus-circle"></i> Terbitkan PO Baru</a>
        </div>
        <div style="overflow-x: auto;">
            <table>
                <thead>
                    <tr>
                        <th>Dokumen PO</th>
                        <th>Vendor Tujuan</th>
                        <th style="text-align: center;">Tanggal PO</th>
                        <th style="text-align: right;">Total Nilai (Rp)</th>
                        <th style="text-align: center;">Logistik</th>
                        <th style="text-align: center;">Finance</th>
                        <th style="text-align: center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($purchaseOrders)): ?>
                        <tr><td colspan="7" style="text-align: center; padding: 40px; color: var(--text-muted);">Belum ada Dokumen PO diterbitkan.</td></tr>
                    <?php else: ?>
                        <?php foreach($purchaseOrders as $po): 
                            $paidAmt = (float)($po['paid_amount'] ?? 0);
                            $totalAmt = (float)($po['total_amount'] ?? 0);
                            $remAmt = max(0, $totalAmt - $paidAmt);
                        ?>
                        <tr>
                            <td><span style="font-family:'Space Mono'; color:var(--brand); font-weight:900; background:var(--brand-soft); padding:4px 8px; border-radius:6px; border:1px dashed rgba(79,70,229,0.3);"><?= esc($po['po_number']) ?></span></td>
                            <td><i class="ph-fill ph-buildings" style="color:var(--text-muted);"></i> <?= esc($po['supplier_name']) ?></td>
                            <td style="text-align: center; font-size:12px; color:var(--text-muted);"><i class="ph-bold ph-calendar-blank"></i> <?= date('d M Y', strtotime($po['po_date'])) ?></td>
                            <td style="text-align: right; font-family:'Space Mono'; font-weight:900; font-size:14px;"><?= number_format($totalAmt, 0, ',', '.') ?></td>
                            
                            <td style="text-align: center;">
                                <?php if($po['status'] == 'ORDERED'): ?>
                                    <span class="status-badge" style="background:var(--warning-soft); color:var(--warning);"><i class="ph-bold ph-truck"></i> Menunggu</span>
                                <?php else: ?>
                                    <span class="status-badge" style="background:var(--success-soft); color:var(--success);"><i class="ph-bold ph-check-circle"></i> Diterima</span>
                                <?php endif; ?>
                            </td>

                            <td style="text-align: center;">
                                <?php if($po['payment_status'] == 'PAID'): ?>
                                    <span class="status-badge" style="background:var(--success-soft); color:var(--success);"><i class="ph-bold ph-check-square-offset"></i> Lunas</span>
                                <?php elseif($po['payment_status'] == 'PARTIAL'): ?>
                                    <span class="status-badge" style="background:var(--info-soft); color:var(--info);" title="Sisa: Rp <?= number_format($remAmt,0,',','.') ?>"><i class="ph-bold ph-chart-pie-slice"></i> Cicilan</span>
                                <?php else: ?>
                                    <span class="status-badge" style="background:var(--danger-soft); color:var(--danger);"><i class="ph-bold ph-warning-circle"></i> Hutang</span>
                                <?php endif; ?>
                            </td>

                            <td style="text-align: center;">
                                <div style="display:flex; justify-content:center; gap:6px;">
                                    <a href="<?= base_url('/procurement/detail/'.$po['id']) ?>" target="_blank" class="btn-action" style="background:var(--bg-main); color:var(--text-main); border:1px solid var(--border-subtle);" title="Cetak PO"><i class="ph-bold ph-printer"></i></a>

                                    <?php if($po['status'] == 'ORDERED'): ?>
                                        <a href="#" class="btn-action" style="background:var(--success-soft); color:var(--success);" onclick="openConfirmModal(event, '<?= base_url('/procurement/receive_goods/'.$po['id']) ?>', 'Terima Barang', 'Stok gudang akan bertambah dan masuk ke Buku Besar sesuai tanggal PO dibuat.')" title="Terima Barang"><i class="ph-bold ph-package"></i> Terima</a>
                                        <a href="#" class="btn-action" style="background:var(--danger-soft); color:var(--danger);" onclick="openConfirmModal(event, '<?= base_url('/procurement/delete_po/'.$po['id']) ?>', 'Batalkan PO', 'PO akan dihapus secara permanen.')" title="Batalkan"><i class="ph-bold ph-trash"></i></a>
                                    <?php else: ?>
                                        <?php if($po['payment_status'] !== 'PAID'): ?>
                                            <button class="btn-action" style="background:var(--warning-soft); color:var(--warning);" onclick="openPaymentModal(<?= $po['id'] ?>, '<?= esc($po['po_number']) ?>', <?= $remAmt ?>)" title="Bayar Tagihan"><i class="ph-bold ph-money"></i> Bayar</button>
                                        <?php endif; ?>
                                    <?php endif; ?>
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

<div id="tab-supplier" class="tab-content">
    <div class="bento-card">
        <div class="bento-header">
            <h3 style="font-size:16px; font-weight:900; margin:0;"><i class="ph-fill ph-buildings" style="color:var(--info);"></i> Direktori Vendor / Supplier</h3>
            <button class="btn-primary" style="background: linear-gradient(135deg, var(--info), #0284c7);" onclick="openSupplierModal()"><i class="ph-bold ph-plus-circle"></i> Tambah Vendor</button>
        </div>
        <div style="overflow-x: auto;">
            <table>
                <thead>
                    <tr>
                        <th>Nama Vendor</th>
                        <th>Kontak PIC</th>
                        <th style="text-align: center;">Telepon</th>
                        <th>Alamat</th>
                        <th style="text-align: center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($suppliers)): ?>
                        <tr><td colspan="5" style="text-align: center; padding: 40px; color: var(--text-muted);">Belum ada data Vendor.</td></tr>
                    <?php else: ?>
                        <?php foreach($suppliers as $sup): ?>
                        <tr>
                            <td style="font-weight:900;"><i class="ph-fill ph-buildings" style="color:var(--text-muted); margin-right:6px;"></i> <?= esc($sup['supplier_name']) ?></td>
                            <td><?= esc($sup['contact_person'] ?: '-') ?></td>
                            <td style="text-align: center; font-family:'Space Mono'; font-weight:800; color:var(--brand); background:var(--brand-soft); border-radius:6px;"><?= esc($sup['phone'] ?: '-') ?></td>
                            <td style="white-space: normal; max-width: 250px; font-size: 12px; line-height: 1.4;"><?= esc($sup['address'] ?: '-') ?></td>
                            <td style="text-align: center;">
                                <div style="display:flex; justify-content:center; gap:6px;">
                                    <button class="btn-action" style="background:var(--warning-soft); color:var(--warning);" onclick='openSupplierModal(<?= json_encode($sup, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'><i class="ph-bold ph-pencil-simple"></i></button>
                                    <a href="#" class="btn-action" style="background:var(--danger-soft); color:var(--danger);" onclick="openConfirmModal(event, '<?= base_url('/procurement/delete_supplier/'.$sup['id']) ?>', 'Hapus Vendor', 'Vendor tidak bisa dihapus jika punya riwayat PO.')"><i class="ph-bold ph-trash"></i></a>
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

<div class="modal-overlay" id="paymentModal">
    <div class="modal-box" style="border-top: 6px solid var(--warning);">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
            <h2 style="margin:0; font-size:20px; font-weight:900; display:flex; align-items:center; gap:10px;"><div style="background:var(--warning-soft); color:var(--warning); padding:8px; border-radius:10px;"><i class="ph-bold ph-wallet"></i></div> Bayar Tagihan PO</h2>
            <button type="button" class="btn-close" onclick="closeModal('paymentModal')"><i class="ph-bold ph-x"></i></button>
        </div>

        <form id="paymentForm" method="post">
            <?= csrf_field() ?>
            <div style="text-align: center; margin-bottom: 20px;">
                <div style="font-size: 10px; font-weight: 900; color: var(--text-muted); text-transform: uppercase;">Nomor Dokumen</div>
                <div id="payPoNumber" style="font-family: 'Space Mono', monospace; font-size: 20px; font-weight: 900; color: var(--text-main);">PO-XXXX</div>
            </div>

            <div style="background: rgba(245, 158, 11, 0.05); border: 1px dashed rgba(245, 158, 11, 0.3); padding: 15px; border-radius: 16px; margin-bottom: 20px;">
                <div style="font-size: 10px; font-weight: 900; color: var(--warning); text-transform: uppercase; margin-bottom: 4px;">Sisa Tagihan Outstanding</div>
                <div id="sisaTagihanDisplay" style="font-family: 'Space Mono', monospace; font-size: 24px; font-weight: 900; color: var(--text-main); letter-spacing: -1px;">Rp 0</div>
            </div>

            <label style="font-size:11px; font-weight:900; color:var(--text-muted); margin-bottom:6px; display:block;">Tanggal Pembayaran Ke Finance</label>
            <div class="input-wrapper" style="border-color: var(--border-subtle);">
                <input type="date" name="payment_date" value="<?= date('Y-m-d') ?>" required style="font-family: 'Space Mono', monospace; font-weight: 900;">
            </div>

            <label style="font-size:11px; font-weight:900; color:var(--text-muted); margin-bottom:6px; display:block;">Nominal Bayar (Bisa Dicicil)</label>
            <div class="input-wrapper" style="border-color: var(--warning);">
                <div style="padding: 14px 16px; background: var(--warning-soft); font-weight: 900; color: var(--warning); border-right: 1px solid rgba(245, 158, 11, 0.2);">Rp</div>
                <input type="text" name="pay_amount" id="payAmountInput" required onkeyup="formatRupiah(this)" style="font-family: 'Space Mono', monospace; font-size: 18px; font-weight: 900; color: var(--warning);" autocomplete="off">
            </div>

            <label style="font-size:11px; font-weight:900; color:var(--text-muted); margin-bottom:6px; margin-top:15px; display:block;">Sumber Dana</label>
            <div class="input-wrapper">
                <select name="payment_method" required>
                    <option value="1-2000">💳 Transfer Saldo Bank [1-2000]</option>
                    <option value="1-1000">💵 Uang Tunai Kas Laci [1-1000]</option>
                </select>
            </div>

            <button type="submit" class="btn-primary" style="width:100%; justify-content:center; padding:16px; font-size:14px; margin-top:20px; background:linear-gradient(135deg, var(--warning), #d97706); box-shadow: 0 10px 25px -5px rgba(245, 158, 11, 0.5);">
                <i class="ph-bold ph-check-circle" style="font-size: 18px;"></i> Eksekusi Pembayaran
            </button>
        </form>
    </div>
</div>

<div class="modal-overlay" id="supplierModal">
    <div class="modal-box" style="border-top: 6px solid var(--info);">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
            <h2 id="supModalTitle" style="margin:0; font-size:20px; font-weight:900; display:flex; align-items:center; gap:10px;"><div style="background:var(--info-soft); color:var(--info); padding:8px; border-radius:10px;"><i class="ph-bold ph-buildings"></i></div> Tambah Vendor</h2>
            <button type="button" class="btn-close" onclick="closeModal('supplierModal')"><i class="ph-bold ph-x"></i></button>
        </div>

        <form id="supplierForm" method="post">
            <?= csrf_field() ?>
            <label style="font-size:10px; font-weight:900; color:var(--text-muted); margin-bottom:6px; display:block; text-transform:uppercase;">Nama Perusahaan / Vendor</label>
            <div class="input-wrapper"><input type="text" name="supplier_name" id="sup_name" required autocomplete="off"></div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:15px; margin-top:15px;">
                <div>
                    <label style="font-size:10px; font-weight:900; color:var(--text-muted); margin-bottom:6px; display:block; text-transform:uppercase;">Kontak (PIC)</label>
                    <div class="input-wrapper"><input type="text" name="contact_person" id="sup_pic" autocomplete="off"></div>
                </div>
                <div>
                    <label style="font-size:10px; font-weight:900; color:var(--text-muted); margin-bottom:6px; display:block; text-transform:uppercase;">No. Telepon</label>
                    <div class="input-wrapper"><input type="text" name="phone" id="sup_phone" autocomplete="off"></div>
                </div>
            </div>

            <label style="font-size:10px; font-weight:900; color:var(--text-muted); margin-bottom:6px; margin-top:15px; display:block; text-transform:uppercase;">Alamat Lengkap</label>
            <div class="input-wrapper"><textarea name="address" id="sup_address"></textarea></div>

            <button type="submit" class="btn-primary" style="width:100%; justify-content:center; padding:16px; font-size:14px; margin-top:20px; background:linear-gradient(135deg, var(--info), #0284c7); box-shadow: 0 10px 25px -5px rgba(14, 165, 233, 0.5);">
                <i class="ph-bold ph-floppy-disk" style="font-size: 18px;"></i> Simpan Data Vendor
            </button>
        </form>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const isDark = document.documentElement.classList.contains('dark');
        const bgColor = isDark ? '#18181b' : '#ffffff';
        const textColor = isDark ? '#f4f4f5' : '#09090b';

        <?php if(session()->getFlashdata('success')): ?>
            Swal.fire({ icon: 'success', title: 'Berhasil!', html: '<?= session()->getFlashdata('success') ?>', confirmButtonColor: '#10b981', background: bgColor, color: textColor, customClass: { popup: 'swal2-custom-radius' } });
        <?php endif; ?>
        <?php if(session()->getFlashdata('error')): ?>
            Swal.fire({ icon: 'error', title: 'Gagal!', html: '<?= session()->getFlashdata('error') ?>', confirmButtonColor: '#ef4444', background: bgColor, color: textColor, customClass: { popup: 'swal2-custom-radius' } });
        <?php endif; ?>
    });

    function switchTab(tabName, el) {
        document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
        el.classList.add('active');
        document.getElementById('tab-' + tabName).classList.add('active');
    }

    function openModal(id) { document.getElementById(id).classList.add('active'); }
    function closeModal(id) { document.getElementById(id).classList.remove('active'); }

    function openSupplierModal(data = null) {
        let form = document.getElementById('supplierForm');
        if(data) {
            document.getElementById('supModalTitle').innerHTML = '<div style="background:var(--info-soft); color:var(--info); padding:8px; border-radius:10px;"><i class="ph-bold ph-pencil-simple"></i></div> Edit Vendor';
            form.action = "<?= base_url('/procurement/update_supplier/') ?>" + data.id;
            document.getElementById('sup_name').value = data.supplier_name;
            document.getElementById('sup_pic').value = data.contact_person;
            document.getElementById('sup_phone').value = data.phone;
            document.getElementById('sup_address').value = data.address;
        } else {
            document.getElementById('supModalTitle').innerHTML = '<div style="background:var(--info-soft); color:var(--info); padding:8px; border-radius:10px;"><i class="ph-bold ph-buildings"></i></div> Tambah Vendor';
            form.action = "<?= base_url('/procurement/store_supplier') ?>";
            form.reset();
        }
        openModal('supplierModal');
    }

    function openPaymentModal(id, no, remAmt) {
        document.getElementById('payPoNumber').innerText = no;
        let formatted = remAmt.toLocaleString('id-ID');
        document.getElementById('sisaTagihanDisplay').innerText = 'Rp ' + formatted;
        document.getElementById('payAmountInput').value = formatted;
        document.getElementById('paymentForm').action = "<?= base_url('/procurement/pay_po/') ?>" + id;
        openModal('paymentModal');
    }

    function openConfirmModal(e, url, title, text) {
        e.preventDefault();
        Swal.fire({
            title: title, text: text, icon: 'warning', showCancelButton: true,
            confirmButtonColor: '#4f46e5', cancelButtonColor: '#94a3b8',
            confirmButtonText: 'Ya, Lanjutkan', cancelButtonText: 'Batal',
            customClass: { popup: 'swal2-custom-radius' }
        }).then((res) => { 
            if (res.isConfirmed) {
                Swal.fire({ title: 'Memproses...', allowOutsideClick: false, showConfirmButton: false, didOpen: () => { Swal.showLoading() }, customClass: { popup: 'swal2-custom-radius' }});
                window.location.href = url;
            }
        });
    }

    function formatRupiah(angka) {
        let numStr = angka.value.replace(/[^,\d]/g, '').toString(),
            split = numStr.split(','), sisa = split[0].length % 3,
            rupiah = split[0].substr(0, sisa), ribuan = split[0].substr(sisa).match(/\d{3}/gi);
        if (ribuan) rupiah += (sisa ? '.' : '') + ribuan.join('.');
        angka.value = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
    }

    function handleAjaxForm(formId, modalId) {
        document.getElementById(formId).addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Clean dots before sending if it's the payment form
            let amountInput = this.querySelector('input[name="pay_amount"]');
            let rawValue = "";
            if (amountInput) {
                rawValue = amountInput.value;
                amountInput.value = rawValue.replace(/\./g, '');
            }

            let btn = this.querySelector('button[type="submit"]');
            let oriHtml = btn.innerHTML;
            btn.disabled = true; btn.innerHTML = '<i class="ph-bold ph-spinner-gap ph-spin"></i> Memproses...';
            
            fetch(this.action, { method: 'POST', body: new FormData(this), headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(res => res.json())
            .then(data => {
                if(amountInput) amountInput.value = rawValue; // restore UI
                if(data.status === 'success') {
                    Swal.fire({ icon: 'success', title: 'Berhasil!', text: data.message, customClass: { popup: 'swal2-custom-radius' }});
                    closeModal(modalId);
                    setTimeout(() => window.location.reload(), 1500);
                } else {
                    Swal.fire({ icon: 'error', title: 'Gagal!', text: data.message, customClass: { popup: 'swal2-custom-radius' }});
                    btn.disabled = false; btn.innerHTML = oriHtml;
                }
            }).catch(() => {
                if(amountInput) amountInput.value = rawValue; // restore UI
                Swal.fire({ icon: 'error', title: 'Koneksi Gagal', text: "Periksa jaringan Anda.", customClass: { popup: 'swal2-custom-radius' }});
                btn.disabled = false; btn.innerHTML = oriHtml;
            });
        });
    }

    handleAjaxForm('supplierForm', 'supplierModal');
    handleAjaxForm('paymentForm', 'paymentModal');
</script>

<?= $this->endSection() ?>