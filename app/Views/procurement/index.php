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
    .tab-btn.active { background: var(--bg-base); color: var(--brand); border: 1px solid var(--brand-soft); }
    .tab-content { display: none; animation: fadeUp .4s ease; }
    .tab-content.active { display: block; }
    @keyframes fadeUp { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

    .bento-card { background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: 24px; padding: 25px; box-shadow: 0 15px 35px -15px rgba(0,0,0,0.1); overflow: hidden; }
    
    table { width: 100%; border-collapse: collapse; white-space: nowrap; }
    th { padding: 15px 18px; font-size: 11px; font-weight: 900; text-transform: uppercase; color: var(--text-muted); border-bottom: 2px solid var(--border-subtle); background: var(--bg-base); }
    td { padding: 15px 18px; border-bottom: 1px dashed var(--border-subtle); color: var(--text-main); font-size: 13px; font-weight: 700; vertical-align: middle; }
    tr:last-child td { border-bottom: none; }
    tr:hover td { background: var(--brand-soft); }
    th:first-child, td:first-child { text-align: left; }

    .status-badge { padding: 6px 12px; border-radius: 999px; font-size: 10px; font-weight: 900; text-transform: uppercase; letter-spacing: 0.5px; display: inline-flex; align-items: center; gap: 6px; }
    .btn-action { padding: 8px 14px; border-radius: 10px; font-size: 12px; font-weight: 800; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; transition: 0.3s; border: none; cursor: pointer; color: inherit; }
    .btn-action:hover { transform: translateY(-2px); }

    .btn-wa-on { background: rgba(37, 211, 102, 0.1); color: #25D366; border: 1px solid rgba(37, 211, 102, 0.3); }
    .btn-wa-off { background: #f1f5f9; color: #94a3b8; border: 1px solid #e2e8f0; }

    .modal-overlay { position: fixed; inset: 0; background: rgba(15,23,42,0.6); backdrop-filter: blur(8px); z-index: 9999; display: none; justify-content: center; align-items: center; opacity: 0; transition: 0.3s; padding: 20px; }
    .modal-overlay.active { display: flex; opacity: 1; }
    .modal-box { background: var(--bg-surface); border-radius: 28px; width: 100%; max-width: 550px; padding: 35px; box-shadow: 0 30px 60px -15px rgba(0,0,0,0.4); border: 1px solid var(--border-subtle); max-height: 95vh; overflow-y: auto;}
    .modal-overlay.active .modal-box { transform: scale(1) translateY(0); }
    .input-wrapper { display: flex; align-items: stretch; background: var(--bg-base); border: 1px solid var(--border-subtle); border-radius: 14px; overflow: hidden; transition: .25s ease; margin-bottom: 15px;}
    .input-wrapper:focus-within { border-color: var(--brand); box-shadow: 0 0 0 4px var(--brand-soft); }
    .input-wrapper input, .input-wrapper select, .input-wrapper textarea { flex: 1; background: transparent; border: none; color: var(--text-main); padding: 14px 16px; font-size: 13px; font-weight: 700; outline: none; font-family: inherit; width: 100%; }

    /* Checklist Table in Modal */
    .chk-table { width: 100%; border: 1px solid var(--border-subtle); border-radius: 12px; overflow: hidden; margin-bottom: 20px;}
    .chk-table th { background: var(--bg-base); font-size: 10px; padding: 12px 10px; }
    .chk-table td { padding: 12px 10px; font-size: 12px; border-bottom: 1px solid var(--border-subtle); vertical-align: middle; }
    
    .chk-input-wrapper { display: flex; align-items: center; background: var(--bg-surface); border: 1px solid var(--success); border-radius: 8px; overflow: hidden; }
    .chk-input-wrapper input { width: 100px; border: none; padding: 8px; text-align: center; font-family: 'Space Mono', monospace; font-weight: 900; color: var(--success); font-size: 14px; outline: none;}
    .chk-input-wrapper span { background: var(--success-soft); color: var(--success); font-size: 10px; font-weight: 900; padding: 0 10px; display: flex; align-items: center; border-left: 1px dashed var(--success); }
    
    /* Scrollbar untuk modal lebar */
    .modal-wide { max-width: 800px; }
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
        <div class="title-text">
            <h1>Procurement & Logistik</h1>
            <p>Kelola pemesanan (Purchase Order), penerimaan barang parsial, dan pembayaran Supplier.</p>
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

<div class="tab-nav">
    <button class="tab-btn active" onclick="switchTab('po', this)"><i class="ph-bold ph-receipt"></i> Dokumen PO</button>
    <button class="tab-btn" onclick="switchTab('supplier', this)"><i class="ph-bold ph-buildings"></i> Vendor / Supplier</button>
</div>

<div id="tab-po" class="tab-content active">
    <div class="bento-card">
        <div class="bento-header" style="display:flex; justify-content:space-between; margin-bottom:20px;">
            <h3 style="font-size:16px; font-weight:900; margin:0;"><i class="ph-fill ph-files" style="color:var(--brand);"></i> Log Purchase Order</h3>
            <a href="<?= base_url('/procurement/create_po') ?>" style="background:linear-gradient(135deg, var(--brand), var(--brand-dark)); color:#fff; padding:12px 20px; border-radius:14px; font-weight:900; text-decoration:none; display:flex; align-items:center; gap:8px;"><i class="ph-bold ph-plus-circle" style="font-size:16px;"></i> Terbitkan PO Baru</a>
        </div>
        <div style="overflow-x: auto;">
            <table>
                <thead>
                    <tr>
                        <th>Dokumen PO</th>
                        <th>Vendor Tujuan</th>
                        <th>Nilai Tagihan</th>
                        <th style="text-align:center;">Logistik</th>
                        <th style="text-align:center;">Finance</th>
                        <th style="text-align:center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($purchaseOrders as $po): 
                        $paidAmt = (float)($po['paid_amount'] ?? 0);
                        $totalAmt = (float)($po['total_amount'] ?? 0);
                        $remAmt = max(0, $totalAmt - $paidAmt);

                        $rawPhone = $po['supplier_phone'] ?? '';
                        $cleanPhone = preg_replace('/[^0-9]/', '', $rawPhone);
                        if (!empty($cleanPhone)) {
                            if (substr($cleanPhone, 0, 1) === '0') $cleanPhone = '62' . substr($cleanPhone, 1);
                        }
                        $isPhoneValid = (strlen($cleanPhone) >= 10);
                        
                        $compName = esc($company['company_name'] ?? 'Perusahaan Kami');
                        $waText = "Halo *" . esc($po['supplier_name']) . "*,\n\nKami dari *" . $compName . "* memesan barang:\n\n📄 *[ No. PO: " . esc($po['po_number']) . " ]*\n------------------\n";
                        if (isset($groupedItems[$po['id']])) {
                            $num = 1;
                            foreach ($groupedItems[$po['id']] as $itm) {
                                $uomBeli = esc($itm['purchase_uom'] ?? $itm['unit']); 
                                $waText .= "{$num}. {$itm['material_name']} ({$itm['qty']} {$uomBeli})\n";
                                $num++;
                            }
                        }
                        $waText .= "------------------\n💰 *Total:* Rp " . number_format($totalAmt, 0, ',', '.') . "\n💳 *Termin:* " . esc($po['payment_term'] ?? 'Cash') . "\n\nTerima kasih 🙏";
                        $waLink = $isPhoneValid ? "https://wa.me/{$cleanPhone}?text=" . urlencode($waText) : "#";
                        
                        $isPartialReceived = ($po['status'] === 'ORDERED' && ($po['receipt_status'] ?? 'PENDING') === 'PARTIAL');
                    ?>
                    <tr>
                        <td>
                            <span style="font-family:'Space Mono'; font-weight:900; color:var(--brand);"><?= esc($po['po_number']) ?></span>
                            <div style="font-size:10px; color:var(--text-muted); margin-top:4px;"><i class="ph-fill ph-calendar"></i> <?= date('d M Y', strtotime($po['po_date'])) ?></div>
                        </td>
                        <td><i class="ph-fill ph-buildings"></i> <?= esc($po['supplier_name']) ?></td>
                        <td style="font-family:'Space Mono'; font-weight:900;">Rp <?= number_format($po['total_amount'], 0, ',', '.') ?></td>
                        
                        <td style="text-align:center;">
                            <?php if($po['status'] == 'RECEIVED'): ?>
                                <span class="status-badge" style="background:var(--success-soft); color:var(--success);"><i class="ph-fill ph-check-circle"></i> MASUK GUDANG</span>
                            <?php elseif($isPartialReceived): ?>
                                <span class="status-badge" style="background:var(--info-soft); color:var(--info);"><i class="ph-fill ph-package"></i> MASUK SEBAGIAN</span>
                            <?php else: ?>
                                <span class="status-badge" style="background:var(--warning-soft); color:var(--warning);"><i class="ph-fill ph-truck"></i> OTW</span>
                            <?php endif; ?>
                        </td>

                        <td style="text-align:center;">
                            <span class="status-badge" style="background:<?= $po['payment_status'] == 'PAID' ? 'var(--success-soft)' : 'var(--danger-soft)' ?>; color:<?= $po['payment_status'] == 'PAID' ? 'var(--success)' : 'var(--danger)' ?>">
                                <?= $po['payment_status'] == 'PAID' ? 'LUNAS' : (($po['payment_status'] == 'PARTIAL') ? 'DICICIL' : 'HUTANG') ?>
                            </span>
                        </td>

                        <td style="text-align:center;">
                            <div style="display:flex; justify-content:center; gap:8px;">
                                <?php if($po['status'] == 'ORDERED'): ?>
                                    <button class="btn-action" style="background:var(--success-soft); color:var(--success); border:1px solid rgba(16,185,129,0.2);" onclick="openReceiveModal(<?= $po['id'] ?>, '<?= esc($po['po_number']) ?>')" title="Terima & Cek Barang Masuk"><i class="ph-bold ph-list-checks"></i> Cek & Terima</button>

                                    <?php if($isPhoneValid): ?>
                                        <a href="<?= $waLink ?>" target="_blank" class="btn-action btn-wa-on" title="Kirim WA Pesanan"><i class="ph-bold ph-whatsapp-logo"></i> WA</a>
                                    <?php else: ?>
                                        <button class="btn-action btn-wa-off" onclick="Swal.fire('HP Kosong','Lengkapi nomor HP di tab Vendor.','warning')" title="HP Belum Diisi"><i class="ph-bold ph-whatsapp-logo"></i> WA</button>
                                    <?php endif; ?>

                                    <?php if(!$isPartialReceived): ?>
                                        <button class="btn-action" style="background:var(--danger-soft); color:var(--danger); border:1px solid rgba(239,68,68,0.2);" onclick="openConfirm('<?= base_url('/procurement/delete_po/'.$po['id']) ?>', 'Hapus PO Secara Permanen')" title="Hapus PO"><i class="ph-bold ph-trash"></i></button>
                                    <?php endif; ?>
                                <?php endif; ?>

                                <a href="<?= base_url('/procurement/detail/'.$po['id']) ?>" class="btn-action" style="background:#f1f5f9; color:#475569; border:1px solid #cbd5e1;" title="Cetak / Detail PDF"><i class="ph-bold ph-printer"></i></a>

                                <?php if(($po['status'] == 'RECEIVED' || $isPartialReceived) && $po['payment_status'] !== 'PAID'): ?>
                                    <button class="btn-action" style="background:var(--warning-soft); color:var(--warning);" onclick="openPaymentModal(<?= $po['id'] ?>, '<?= esc($po['po_number']) ?>', <?= $remAmt ?>)" title="Bayar Tagihan Hutang"><i class="ph-bold ph-money"></i> Bayar</button>
                                    
                                    <button class="btn-action" style="background:var(--danger-soft); color:var(--danger);" onclick="openConfirm('<?= base_url('/procurement/void_po/'.$po['id']) ?>', 'VOID (Batal Terima)? Seluruh stok penerimaan PO ini akan dikembalikan dan Hutang dibatalkan.')" title="Batal Terima Semua Barang (Void)"><i class="ph-bold ph-arrow-u-up-left"></i> Batal</button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div id="tab-supplier" class="tab-content">
    <div class="bento-card">
        <div class="bento-header" style="display:flex; justify-content:space-between; margin-bottom:20px;">
            <h3 style="font-size:16px; font-weight:900; margin:0;"><i class="ph-fill ph-buildings" style="color:var(--info);"></i> Daftar Vendor</h3>
            <button style="background:linear-gradient(135deg, var(--info), #0284c7); color:#fff; border:none; padding:12px 20px; border-radius:14px; font-weight:900; display:flex; align-items:center; gap:8px; cursor:pointer;" onclick="openSupplierModal()"><i class="ph-bold ph-plus-circle" style="font-size:16px;"></i> Tambah Vendor</button>
        </div>
        <div style="overflow-x: auto;">
            <table>
                <thead>
                    <tr>
                        <th>Vendor</th>
                        <th>No. Telepon (WA)</th>
                        <th>Alamat</th>
                        <th style="text-align:center;">Opsi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($suppliers as $s): ?>
                    <tr>
                        <td><b><?= esc($s['supplier_name']) ?></b><br><small><?= esc($s['contact_person']) ?></small></td>
                        <td><span style="background:var(--brand-soft); color:var(--brand); padding:4px 8px; border-radius:6px; font-family:'Space Mono'; font-weight:800;"><?= esc($s['phone'] ?: 'BELUM DIISI') ?></span></td>
                        <td><?= esc($s['address']) ?></td>
                        <td style="text-align:center;">
                            <button class="btn-action" style="background:var(--warning-soft); color:var(--warning);" onclick='openSupplierModal(<?= json_encode($s, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) ?>)'><i class="ph-bold ph-pencil"></i></button>
                            
                            <button class="btn-action" style="background:var(--danger-soft); color:var(--danger);" onclick="openConfirm('<?= base_url('/procurement/delete_supplier/'.$s['id']) ?>', 'Hapus Vendor')"><i class="ph-bold ph-trash"></i></button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal-overlay" id="supplierModal">
    <div class="modal-box">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
            <h2 id="supModalTitle" style="margin:0; font-size:20px; font-weight:900;">Tambah Vendor</h2>
            <button type="button" class="btn-close" style="width:32px; height:32px; border-radius:50%; border:none; cursor:pointer;" onclick="closeModal('supplierModal')"><i class="ph-bold ph-x"></i></button>
        </div>
        <form id="supplierForm" action="<?= base_url('/procurement/store_supplier') ?>" method="post">
            <?= csrf_field() ?>
            <label style="font-size:10px; font-weight:900; color:var(--text-muted); margin-bottom:6px; display:block;">NAMA VENDOR</label>
            <div class="input-wrapper"><input type="text" name="supplier_name" id="sup_name" placeholder="Nama Vendor" required autocomplete="off"></div>
            
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:15px;">
                <div>
                    <label style="font-size:10px; font-weight:900; color:var(--text-muted); margin-bottom:6px; display:block;">KONTAK PIC</label>
                    <div class="input-wrapper"><input type="text" name="contact_person" id="sup_pic" placeholder="Nama PIC" autocomplete="off"></div>
                </div>
                <div>
                    <label style="font-size:10px; font-weight:900; color:var(--text-muted); margin-bottom:6px; display:block;">NO HP (WA)</label>
                    <div class="input-wrapper"><input type="text" name="phone" id="sup_phone" placeholder="Contoh: 0812..." autocomplete="off"></div>
                </div>
            </div>

            <label style="font-size:10px; font-weight:900; color:var(--text-muted); margin-bottom:6px; display:block;">ALAMAT</label>
            <div class="input-wrapper"><textarea name="address" id="sup_address" placeholder="Alamat"></textarea></div>
            
            <button type="submit" class="btn-primary" style="width:100%; justify-content:center; border:none; padding:15px; border-radius:14px; margin-top:10px;">Simpan Data Vendor</button>
        </form>
    </div>
</div>

<div class="modal-overlay" id="paymentModal">
    <div class="modal-box">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
            <h2 style="margin:0; font-size:20px; font-weight:900;">Bayar Tagihan PO</h2>
            <button type="button" class="btn-close" style="width:32px; height:32px; border-radius:50%; border:none; cursor:pointer;" onclick="closeModal('paymentModal')"><i class="ph-bold ph-x"></i></button>
        </div>
        <form id="paymentForm" method="post">
            <?= csrf_field() ?>
            <div style="text-align: center; margin-bottom: 20px;">
                <div style="font-size: 10px; font-weight: 900; color: var(--text-muted); text-transform: uppercase;">Nomor Dokumen Hutang</div>
                <div id="payPoNumber" style="font-family: 'Space Mono', monospace; font-size: 20px; font-weight: 900; color: var(--text-main);">PO-XXXX</div>
            </div>

            <div style="background: rgba(245, 158, 11, 0.05); border: 1px dashed rgba(245, 158, 11, 0.3); padding: 15px; border-radius: 16px; margin-bottom: 20px;">
                <div style="font-size: 10px; font-weight: 900; color: var(--warning); text-transform: uppercase; margin-bottom: 4px;">Sisa Tagihan Outstanding</div>
                <div id="sisaTagihanDisplay" style="font-family: 'Space Mono', monospace; font-size: 24px; font-weight: 900; color: var(--text-main); letter-spacing: -1px;">Rp 0</div>
            </div>

            <label style="font-size:11px; font-weight:900; color:var(--text-muted); margin-bottom:6px; display:block;">Tanggal Kas Keluar</label>
            <div class="input-wrapper">
                <input type="date" name="payment_date" value="<?= date('Y-m-d') ?>" required style="font-family: 'Space Mono', monospace;">
            </div>

            <label style="font-size:11px; font-weight:900; color:var(--text-muted); margin-bottom:6px; display:block;">Nominal Bayar</label>
            <div class="input-wrapper" style="border-color: var(--warning);">
                <div style="padding: 14px 16px; background: var(--warning-soft); font-weight: 900; color: var(--warning); border-right: 1px solid rgba(245, 158, 11, 0.2);">Rp</div>
                <input type="text" name="pay_amount" id="payAmountInput" required onkeyup="formatRupiah(this)" style="font-family: 'Space Mono', monospace; font-size: 18px; font-weight: 900; color: var(--warning);" autocomplete="off">
            </div>

            <label style="font-size:11px; font-weight:900; color:var(--text-muted); margin-bottom:6px; display:block;">Sumber Dana (Kas Operasional)</label>
            <div class="input-wrapper">
                <select name="payment_method" required>
                    <option value="1-2000">💳 Transfer Saldo Bank [1-2000]</option>
                    <option value="1-1000">💵 Uang Tunai Kas Laci [1-1000]</option>
                </select>
            </div>

            <button type="submit" class="btn-primary" style="width:100%; justify-content:center; padding:16px; margin-top:10px; background:linear-gradient(135deg, var(--warning), #d97706);">
                <i class="ph-bold ph-check-circle" style="font-size: 18px;"></i> Potong Kas & Lunasi Hutang
            </button>
        </form>
    </div>
</div>

<div class="modal-overlay" id="receiveModal">
    <div class="modal-box modal-wide">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
            <h2 style="margin:0; font-size:20px; font-weight:900; display:flex; align-items:center; gap:10px;">
                <div style="background:var(--success-soft); color:var(--success); width:40px; height:40px; border-radius:12px; display:flex; justify-content:center; align-items:center;"><i class="ph-bold ph-list-checks"></i></div>
                Checklist Kedatangan Barang
            </h2>
            <button type="button" class="btn-close" style="width:32px; height:32px; border-radius:50%; border:none; cursor:pointer;" onclick="closeModal('receiveModal')"><i class="ph-bold ph-x"></i></button>
        </div>
        
        <div style="background: rgba(15,23,42,0.03); border: 1px dashed var(--border-subtle); padding: 12px 20px; border-radius: 12px; margin-bottom: 20px; font-size: 12px; color: var(--text-muted);">
            Dokumen PO: <strong id="receiveTitlePo" style="color:var(--brand); font-family:'Space Mono'; font-size:14px;">PO-XXXX</strong><br>
            Sesuaikan kolom <b>"DITERIMA"</b> dengan jumlah barang fisik yang benar-benar datang dari truk pengirim. Barang yang diisi nol (0) akan tetap berstatus OTW.
        </div>
        
        <form id="receiveForm" method="post">
            <?= csrf_field() ?> 
            
            <div style="max-height: 400px; overflow-y: auto; border: 1px solid var(--border-subtle); border-radius: 14px; margin-bottom: 15px;">
                <table class="chk-table" id="chkTable" style="margin: 0; border: none;">
                    <thead>
                        <tr>
                            <th style="width: 5%;">No</th>
                            <th style="width: 35%;">Material / Barang</th>
                            <th style="width: 20%; text-align: center;">Total Dipesan</th>
                            <th style="width: 20%; text-align: center;">Sisa Menunggu</th>
                            <th style="width: 20%; text-align: center;">Diterima Sekarang</th>
                        </tr>
                    </thead>
                    <tbody id="chkBody">
                        <tr><td colspan="5" style="text-align:center; padding: 40px;"><i class="ph-bold ph-spinner-gap ph-spin" style="font-size:24px;"></i><br>Memuat data...</td></tr>
                    </tbody>
                </table>
            </div>

            <div class="form-group" style="margin-bottom: 20px;">
                <label style="font-size:11px; font-weight:800; color:var(--text-muted); margin-bottom:6px; display:block;">Catatan Surat Jalan / Pengiriman (Opsional)</label>
                <div class="input-wrapper" style="margin-bottom: 0;">
                    <textarea name="receive_notes" rows="2" placeholder="Contoh: Surat Jalan No. SJ-1234, dus A penyok tapi isi aman..." style="font-weight:600; font-size:13px; line-height:1.5;"></textarea>
                </div>
            </div>

            <button type="submit" id="btnSubmitReceive" class="btn-primary" style="width:100%; justify-content:center; padding:18px; background:linear-gradient(135deg, var(--success), #059669); font-size: 15px;">
                <i class="ph-bold ph-check-square-offset" style="font-size: 20px;"></i> Verifikasi & Masukkan ke Gudang
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
            Swal.fire({ icon: 'success', title: 'Berhasil!', html: <?= json_encode(session()->getFlashdata('success')) ?>, confirmButtonColor: '#10b981', background: bgColor, color: textColor, customClass: { popup: 'swal2-custom-radius' } });
        <?php endif; ?>
        <?php if(session()->getFlashdata('error')): ?>
            Swal.fire({ icon: 'error', title: 'Gagal!', html: <?= json_encode(session()->getFlashdata('error')) ?>, confirmButtonColor: '#ef4444', background: bgColor, color: textColor, customClass: { popup: 'swal2-custom-radius' } });
        <?php endif; ?>
    });

    function switchTab(tab, el) {
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
        el.classList.add('active');
        document.getElementById('tab-'+tab).classList.add('active');
    }

    function openModal(id) { document.getElementById(id).classList.add('active'); }
    function closeModal(id) { document.getElementById(id).classList.remove('active'); }

    function openConfirm(url, title) {
        Swal.fire({
            title: title + '?',
            text: "Pastikan Anda memahami aksi ini. Tindakan ini akan mengubah saldo & stok.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#94a3b8',
            confirmButtonText: 'Ya, Lanjutkan',
            cancelButtonText: 'Batal',
            customClass: { popup: 'swal2-custom-radius' }
        }).then((r) => { 
            if(r.isConfirmed) {
                Swal.fire({ title: 'Memproses...', allowOutsideClick: false, showConfirmButton: false, didOpen: () => { Swal.showLoading() }, customClass: { popup: 'swal2-custom-radius' }});
                window.location.href = url;
            }
        });
    }

    function formatRupiah(angka) {
        let n = angka.value.replace(/[^,\d]/g, '').toString(),
            split = n.split(','), sisa = split[0].length % 3,
            rupiah = split[0].substr(0, sisa), ribuan = split[0].substr(sisa).match(/\d{3}/gi);
        if (ribuan) rupiah += (sisa ? '.' : '') + ribuan.join('.');
        angka.value = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
    }

    function openSupplierModal(data = null) {
        let form = document.getElementById('supplierForm');
        if(data) {
            document.getElementById('supModalTitle').innerText = 'Edit Vendor';
            form.action = "<?= base_url('/procurement/update_supplier/') ?>" + data.id;
            document.getElementById('sup_name').value = data.supplier_name;
            document.getElementById('sup_pic').value = data.contact_person;
            document.getElementById('sup_phone').value = data.phone;
            document.getElementById('sup_address').value = data.address;
        } else {
            document.getElementById('supModalTitle').innerText = 'Tambah Vendor';
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

    function openReceiveModal(id, no) {
        document.getElementById('receiveForm').action = "<?= base_url('/procurement/receive_goods/') ?>" + id;
        document.getElementById('receiveTitlePo').innerText = no;
        
        let tbody = document.getElementById('chkBody');
        tbody.innerHTML = '<tr><td colspan="5" style="text-align:center; padding:40px;"><i class="ph-bold ph-spinner-gap ph-spin" style="font-size:30px; color:var(--brand);"></i><br><br>Memuat data pesanan...</td></tr>';
        document.getElementById('btnSubmitReceive').style.display = 'none'; 

        openModal('receiveModal');
        
        fetch("<?= base_url('/procurement/get_po_items/') ?>" + id, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(res => res.json())
        .then(data => {
            tbody.innerHTML = '';
            let validItems = 0;

            if(data.length > 0) {
                let n = 1;
                data.forEach(item => {
                    let remQty = parseFloat(item.remaining_qty);
                    
                    if (remQty > 0) {
                        validItems++;
                        tbody.innerHTML += `
                            <tr style="background: #fff; transition:0.3s;">
                                <td style="text-align:center; font-weight:900; color:var(--text-muted);">${n++}</td>
                                <td>
                                    <div style="font-weight:900; color:var(--text-main); font-size: 13px;">${item.material_name}</div>
                                    <div style="font-size:10px; color:var(--brand); font-family:'Space Mono'; margin-top:4px; font-weight:800; background:var(--brand-soft); display:inline-block; padding:2px 6px; border-radius:4px; border:1px solid rgba(79, 70, 229, 0.2);">${item.rm_sku}</div>
                                </td>
                                <td style="text-align:center; font-family:'Space Mono'; font-weight:700; color:var(--text-muted); font-size:13px;">${parseFloat(item.qty)} ${item.purchase_uom}</td>
                                <td style="text-align:center; font-family:'Space Mono'; font-weight:900; color:var(--danger); font-size:15px; background:var(--danger-soft); border-radius:8px;">${remQty} <span style="font-size:10px;">${item.purchase_uom}</span></td>
                                <td>
                                    <div class="chk-input-wrapper">
                                        <input type="number" step="0.01" min="0" max="${remQty}" name="qty_received[${item.id}]" value="${remQty}" 
                                               onfocus="this.parentElement.style.borderColor='var(--brand)'; this.parentElement.style.boxShadow='0 0 0 3px var(--brand-soft)'"
                                               onblur="this.parentElement.style.borderColor='var(--success)'; this.parentElement.style.boxShadow='none'">
                                        <span>${item.purchase_uom}</span>
                                    </div>
                                </td>
                            </tr>
                        `;
                    }
                });

                if (validItems === 0) {
                    tbody.innerHTML = '<tr><td colspan="5" style="text-align:center; color:var(--success); font-weight:800; padding:30px;"><i class="ph-fill ph-check-circle" style="font-size:40px; margin-bottom:10px;"></i><br>Semua item di PO ini sudah diterima sepenuhnya.</td></tr>';
                    document.getElementById('btnSubmitReceive').style.display = 'none';
                } else {
                    document.getElementById('btnSubmitReceive').style.display = 'flex';
                }
            } else {
                tbody.innerHTML = '<tr><td colspan="5" style="text-align:center; color:var(--danger); padding:30px;">Data item tidak ditemukan.</td></tr>';
            }
        });
    }

    function handleAjaxForm(formId, modalId) {
        document.getElementById(formId).addEventListener('submit', function(e) {
            e.preventDefault();
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
                if(amountInput) amountInput.value = rawValue;
                if(data.status === 'success') {
                    Swal.fire({ icon: 'success', title: 'Berhasil!', text: data.message, customClass: { popup: 'swal2-custom-radius' }});
                    closeModal(modalId);
                    setTimeout(() => window.location.reload(), 1500);
                } else {
                    Swal.fire({ icon: 'error', title: 'Gagal!', text: data.message, customClass: { popup: 'swal2-custom-radius' }});
                    btn.disabled = false; btn.innerHTML = oriHtml;
                }
            }).catch(() => {
                if(amountInput) amountInput.value = rawValue;
                Swal.fire({ icon: 'error', title: 'Koneksi Gagal', text: "Periksa jaringan Anda.", customClass: { popup: 'swal2-custom-radius' }});
                btn.disabled = false; btn.innerHTML = oriHtml;
            });
        });
    }

    handleAjaxForm('supplierForm', 'supplierModal');
    
    document.getElementById('paymentForm').addEventListener('submit', function(e) {
        let amountInput = this.querySelector('input[name="pay_amount"]');
        if (amountInput) { amountInput.value = amountInput.value.replace(/\./g, ''); }
        let btn = this.querySelector('button[type="submit"]');
        btn.disabled = true; btn.innerHTML = '<i class="ph-bold ph-spinner-gap ph-spin"></i> Membayar...';
    });

    document.getElementById('receiveForm').addEventListener('submit', function(e) {
        let btn = document.getElementById('btnSubmitReceive');
        btn.disabled = true; btn.innerHTML = '<i class="ph-bold ph-spinner-gap ph-spin" style="font-size:20px;"></i> Menyimpan ke Gudang...';
    });
</script>

<?= $this->endSection() ?>