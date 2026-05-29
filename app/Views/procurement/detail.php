<?php
    $totalAmt = $po['total_amount'] ?? 0;
    $paidAmt  = $po['paid_amount'] ?? 0;
    $balance  = $totalAmt - $paidAmt;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PO - <?= esc($po['po_number']) ?> | <?= esc($company['app_name'] ?? 'Noric ERP') ?></title>

    <?php if (isset($company['logo_path']) && !empty($company['logo_path'])): ?>
        <link rel="icon" type="image/png" href="<?= base_url('uploads/logo/' . esc($company['logo_path'])) ?>">
        <link rel="apple-touch-icon" href="<?= base_url('uploads/logo/' . esc($company['logo_path'])) ?>">
    <?php endif; ?>

    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Space+Mono:wght@400;700&display=swap');

        * { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --ink: #0f172a; --muted: #475569; --soft: #64748b;
            --line: #cbd5e1; --line-dark: #94a3b8; --paper: #ffffff;
            --bg: #e2e8f0; --head: #f8fafc; --accent: #4f46e5;
            --warning: #b45309; --danger: #b91c1c; --success: #166534;
        }

        body {
            font-family: 'Inter', Arial, sans-serif;
            font-size: 11px; color: var(--ink); background: var(--bg);
            padding: 18px; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important;
        }

        .swal2-custom-radius { border-radius: 20px !important; font-family: 'Inter', sans-serif; }

        .container {
            width: 100%; max-width: 820px; margin: 0 auto; background: var(--paper);
            padding: 24px 26px; box-shadow: 0 10px 28px rgba(15, 23, 42, 0.10);
            border-radius: 14px; position: relative; overflow: hidden;
        }

        .watermark-bg {
            position: absolute; inset: 0;
            <?php if (isset($company['logo_path']) && !empty($company['logo_path'])): ?>
            background-image: url('<?= base_url('uploads/logo/' . esc($company['logo_path'])) ?>');
            <?php endif; ?>
            background-repeat: repeat; background-size: 170px; background-position: center;
            opacity: 0.025; transform: rotate(-22deg) scale(1.15); z-index: 1; pointer-events: none;
        }

        .content-wrapper { position: relative; z-index: 5; }

        /* ACTION BAR */
        .action-bar { text-align: center; margin-bottom: 20px; }
        .btn-wrap { display: inline-flex; gap: 10px; flex-wrap: wrap; justify-content: center; }

        .btn-action {
            border: none; padding: 11px 20px; font-size: 13px; font-weight: 800;
            border-radius: 10px; cursor: pointer; display: inline-flex; align-items: center;
            gap: 8px; text-decoration: none; transition: all .2s ease;
        }

        .btn-print { background: linear-gradient(135deg, #4f46e5, #4338ca); color: #fff; box-shadow: 0 8px 20px rgba(79, 70, 229, 0.22); }
        .btn-wa { background: linear-gradient(135deg, #10b981, #059669); color: #fff; box-shadow: 0 8px 20px rgba(16, 185, 129, 0.22); }
        .btn-wa-disabled { background: #e2e8f0; color: #94a3b8; border: 1px solid #cbd5e1; cursor: not-allowed; }
        .btn-back { background: #fff; color: var(--ink); border: 1px solid var(--line); }
        .btn-action:hover:not(.btn-wa-disabled) { transform: translateY(-2px); filter: brightness(1.1); }

        .print-guide {
            display: inline-block; text-align: left; background: #ffffff; padding: 10px 16px;
            border-radius: 10px; border: 1px dashed var(--line-dark); font-size: 11px;
            color: #334155; margin-top: 12px; line-height: 1.6;
        }

        /* TOP STRIP */
        .top-strip {
            display: flex; justify-content: space-between; align-items: center;
            background: linear-gradient(90deg, #312e81, #4f46e5); color: #fff;
            padding: 8px 14px; border-radius: 10px; margin-bottom: 14px;
            font-size: 10px; font-weight: 700; letter-spacing: 0.2px;
        }
        .top-strip .left, .top-strip .right { display: flex; align-items: center; gap: 6px; }

        /* HEADER */
        .header { border-bottom: 2px solid #0f172a; padding-bottom: 14px; margin-bottom: 16px; display: flex; justify-content: space-between; align-items: flex-start; gap: 18px; }
        .company-info { display: flex; align-items: flex-start; gap: 12px; flex: 1; }
        .company-logo-wrap { width: 56px; height: 56px; border: 1px solid var(--line); border-radius: 10px; display: flex; align-items: center; justify-content: center; background: #fff; flex-shrink: 0; overflow: hidden; }
        .company-logo { width: 46px; height: 46px; object-fit: contain; }
        .company-text h1 { font-size: 20px; font-weight: 900; margin-bottom: 4px; text-transform: uppercase; letter-spacing: -0.4px; line-height: 1.1; }
        .company-text .sub { font-size: 10px; color: var(--muted); line-height: 1.5; font-weight: 600; }
        .doc-title { text-align: right; min-width: 220px; display: flex; flex-direction: column; align-items: flex-end; }
        .doc-title .doc-label { font-size: 10px; text-transform: uppercase; font-weight: 800; color: var(--soft); letter-spacing: 1.2px; margin-bottom: 5px; }
        .doc-title h2 { font-size: 20px; text-transform: uppercase; font-weight: 900; letter-spacing: 0.5px; margin-bottom: 4px; line-height: 1; }
        .barcode-img { height: 24px; margin-bottom: 4px; }
        .doc-title p { font-family: 'Space Mono', monospace; font-size: 12px; font-weight: 700; border: 1.5px solid #0f172a; padding: 4px 8px; display: inline-block; background: rgba(241, 245, 249, 0.95); border-radius: 8px; margin-top: 2px; }

        /* WATERMARK STATUS */
        .status-watermark { position: absolute; top: 46%; left: 50%; transform: translate(-50%, -50%) rotate(-18deg); font-size: 68px; font-weight: 900; letter-spacing: 6px; opacity: 0.08; border: 6px solid; padding: 12px 28px; border-radius: 16px; pointer-events: none; z-index: 2; white-space: nowrap; }
        .wm-paid { color: #16a34a; border-color: #16a34a; }
        .wm-partial { color: #d97706; border-color: #d97706; }
        .wm-unpaid { color: #dc2626; border-color: #dc2626; }

        /* META */
        .meta-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 14px; }
        .meta-box { border: 1px solid var(--line); padding: 11px 13px; border-radius: 10px; background: rgba(255, 255, 255, 0.92); }
        .meta-box.alt { background: rgba(238, 242, 255, 0.72); }
        .meta-box h4 { font-size: 10px; text-transform: uppercase; font-weight: 900; border-bottom: 1px dashed var(--line-dark); padding-bottom: 6px; margin-bottom: 8px; display: flex; align-items: center; gap: 7px; letter-spacing: 0.35px; }
        .meta-table { width: 100%; border-collapse: collapse; }
        .meta-table td { padding: 3px 0; font-size: 10.5px; vertical-align: top; }
        .meta-label { width: 100px; font-weight: 800; color: var(--muted); white-space: nowrap; }
        .meta-val { font-weight: 700; color: var(--ink); }
        .meta-val.bold { font-weight: 900; font-size: 12px; }

        /* ADDRESS */
        .address-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 14px; }
        .address-box { border: 1px solid var(--line); padding: 11px 13px; border-radius: 10px; background: rgba(255, 255, 255, 0.92); }
        .address-box h4 { font-size: 10px; text-transform: uppercase; font-weight: 900; border-bottom: 1px dashed var(--line-dark); padding-bottom: 6px; margin-bottom: 8px; display: flex; align-items: center; gap: 7px; letter-spacing: 0.35px; }
        .address-box h3 { font-size: 14px; font-weight: 900; margin-bottom: 6px; line-height: 1.25; }
        .address-box p { font-size: 10.5px; color: var(--muted); line-height: 1.55; margin-bottom: 8px; white-space: pre-line; }
        .addr-row { font-size: 10.5px; font-weight: 700; margin-bottom: 3px; }
        .addr-row span { display: inline-block; min-width: 48px; color: var(--muted); font-weight: 800; }

        /* SECTION TITLE */
        .section-title-sm { font-size: 11px; font-weight: 900; text-transform: uppercase; margin-bottom: 8px; margin-top: 14px; display: flex; align-items: center; gap: 6px; letter-spacing: 0.5px; }

        /* TABLE */
        .table-wrap { border: 1.5px solid #0f172a; border-radius: 12px; overflow: hidden; margin-bottom: 12px; background: rgba(255, 255, 255, 0.95); }
        .item-table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        .item-table thead th { background: #0f172a; color: #fff; text-align: center; font-weight: 800; text-transform: uppercase; font-size: 9.5px; letter-spacing: 0.3px; padding: 9px 6px; border-right: 1px solid rgba(255,255,255,0.12); }
        .item-table thead th:last-child { border-right: none; }
        .item-table tbody td { border-top: 1px solid #cbd5e1; padding: 7px 6px; font-size: 10.5px; font-weight: 600; vertical-align: middle; line-height: 1.35; }
        .item-table tbody tr:nth-child(even) { background: #f8fafc; }
        .text-center { text-align: center !important; }
        .text-right { text-align: right !important; }
        .no-cell { font-weight: 900; font-size: 11px; }
        .sku-badge { display: inline-block; font-family: 'Space Mono', monospace; font-size: 9.5px; font-weight: 700; color: #0f172a; background: #f1f5f9; border: 1px solid #cbd5e1; border-radius: 999px; padding: 3px 7px; white-space: nowrap; }
        .product-inline { white-space: normal; word-break: break-word; line-height: 1.35; }
        .desc-text { font-size: 11px; font-weight: 800; display: inline; color: var(--ink); text-transform: uppercase; }
        .qty-val { font-family: 'Space Mono', monospace; font-size: 11px; font-weight: 700; white-space: nowrap; }

        /* BOTTOM GRID */
        .bottom-grid { display: grid; grid-template-columns: 1fr 320px; gap: 12px; align-items: start; margin-top: 10px; }
        .notes-section { font-size: 9.8px; line-height: 1.55; border: 1px dashed var(--line-dark); padding: 10px 12px; border-radius: 10px; background: rgba(248, 250, 252, 0.92); }
        .notes-section strong { font-weight: 900; text-transform: uppercase; display: inline-block; margin-bottom: 5px; }
        .notes-section ol { padding-left: 16px; margin-top: 4px; }
        .notes-section li { margin-bottom: 3px; }
        .summary-card { border: 1px solid var(--line); border-radius: 12px; overflow: hidden; background: #fff; }
        .sum-line { display: flex; justify-content: space-between; gap: 12px; padding: 9px 12px; border-bottom: 1px solid #e2e8f0; font-size: 10.8px; font-weight: 700; }
        .sum-line span:last-child { font-family: 'Space Mono', monospace; text-align: right; }
        .sum-line.total { font-weight: 900; font-size: 11.5px; background: #f8fafc; }
        .sum-line.paid { color: #16a34a; font-weight: 900; }
        .grand-total { padding: 12px; color: #fff; font-weight: 900; display: flex; justify-content: space-between; align-items: center; }
        .grand-total .label { font-size: 10px; text-transform: uppercase; letter-spacing: 0.6px; }
        .grand-total .value { font-family: 'Space Mono', monospace; font-size: 16px; }
        .grand-unpaid { background: #dc2626; }
        .grand-partial { background: #d97706; }
        .grand-paid { background: #16a34a; }

        /* SIGNATURE */
        .signature-section { display: flex; justify-content: space-between; gap: 12px; margin-top: 16px; text-align: center; }
        .sig-box { width: 49%; border: 1px solid var(--line); border-radius: 10px; padding: 10px 8px 8px; background: rgba(255, 255, 255, 0.95); }
        .sig-box p { font-size: 10px; font-weight: 800; margin: 0; color: var(--ink); }
        .sig-space { height: 55px; }
        .sig-line { border-top: 1px solid #0f172a; margin: 0 auto 5px; width: 90%; }
        .sig-name { font-size: 9px; font-weight: 700; color: #475569; }
        .footer-note { margin-top: 12px; display: flex; justify-content: space-between; font-size: 9px; color: #64748b; font-weight: 700; letter-spacing: 0.2px; }

        @page { size: A4 portrait; margin: 7mm 7mm 9mm 7mm; }
        @media print {
            body { padding: 0; background: #fff; font-size: 10px; }
            .container { box-shadow: none; border: none; border-radius: 0; padding: 0; max-width: 100%; width: 100%; margin: 0; overflow: visible; }
            .no-print { display: none !important; }
            .watermark-bg { position: fixed; inset: 0; width: 100vw; height: 100vh; z-index: 0; }
            table { page-break-inside: auto; border-collapse: collapse; }
            tr { page-break-inside: avoid; page-break-after: auto; }
            td, th { page-break-inside: avoid; }
            thead { display: table-header-group; }
            tfoot { display: table-footer-group; }
            .meta-grid, .address-grid, .notes-section, .summary-card, .signature-section, .header, .top-strip, .section-title-sm, .bottom-grid { page-break-inside: avoid; }
            .table-wrap { border-radius: 0; }
        }
    </style>
</head>
<body>

    <?php
        // LOGIKA LINK WHATSAPP DENGAN DAFTAR BARANG YANG SUPER RAPI & PROFESIONAL
        $cleanPhone = preg_replace('/[^0-9]/', '', $po['supplier_phone'] ?? '');
        if (!empty($cleanPhone) && substr($cleanPhone, 0, 1) === '0') {
            $cleanPhone = '62' . substr($cleanPhone, 1);
        }
        $isPhoneValid = (strlen($cleanPhone) >= 10);
        
        $compName = esc($company['company_name'] ?? 'Perusahaan Kami');
        $waText = "Halo *" . esc($po['supplier_name']) . "*,\n\n";
        $waText .= "Kami dari *" . $compName . "* ingin melakukan pemesanan barang dengan rincian sebagai berikut:\n\n";
        $waText .= "📄 *[ No. PO: " . esc($po['po_number']) . " ]*\n";
        $waText .= "--------------------------------------\n";
        
        $num = 1;
        foreach($items as $itm) {
            $qty = floatval($itm['qty']);
            $unit = esc($itm['unit'] ?: 'Unit');
            $matName = esc($itm['material_name'] ?: '-');
            
            $waText .= "{$num}. {$matName} ({$qty} {$unit})\n";
            $num++;
        }
        
        $waText .= "--------------------------------------\n";
        $waText .= "💰 *Total Estimasi:* Rp " . number_format($totalAmt, 0, ',', '.') . "\n";
        $waText .= "💳 *Sistem Bayar:* " . esc($po['payment_term'] ?? 'Cash') . "\n\n";
        $waText .= "Mohon bantuannya untuk segera diproses dan diinformasikan ketersediaannya.\nTerima kasih 🙏";
        
        $waLink = $isPhoneValid ? "https://wa.me/{$cleanPhone}?text=" . urlencode($waText) : "#";
    ?>

    <div class="action-bar no-print">
        <div class="btn-wrap">
            <a href="<?= base_url('/procurement') ?>" class="btn-action btn-back">
                <i class="ph-bold ph-arrow-left"></i> Kembali
            </a>
            
            <?php if($po['status'] == 'ORDERED'): ?>
                <?php if($isPhoneValid): ?>
                    <a href="<?= $waLink ?>" target="_blank" class="btn-action btn-wa">
                        <i class="ph-bold ph-whatsapp-logo"></i> Kirim Rincian Pesanan via WA
                    </a>
                <?php else: ?>
                    <button type="button" class="btn-action btn-wa-disabled" onclick="Swal.fire({icon: 'warning', title: 'Nomor HP Kosong', text: 'Vendor ini belum memiliki nomor HP yang valid. Silakan lengkapi di tab Vendor/Supplier.', customClass: { popup: 'swal2-custom-radius' }})">
                        <i class="ph-bold ph-whatsapp-logo"></i> Kirim Rincian Pesanan via WA
                    </button>
                <?php endif; ?>
            <?php endif; ?>

            <button onclick="window.print()" class="btn-action btn-print">
                <i class="ph-bold ph-printer"></i> Cetak PO (A4)
            </button>
        </div>
        <br>
        <div class="print-guide">
            <strong>Panduan Cetak:</strong><br>
            1. Centang <b>"Background graphics"</b> agar watermark/logo ikut tercetak.<br>
            2. Hilangkan centang <b>"Headers and footers"</b> agar URL browser tidak ikut muncul.
        </div>
    </div>

    <div class="container">
        <div class="watermark-bg"></div>

        <?php if($po['payment_status'] === 'PAID'): ?>
            <div class="status-watermark wm-paid">LUNAS</div>
        <?php elseif($po['payment_status'] === 'PARTIAL'): ?>
            <div class="status-watermark wm-partial">DICICIL</div>
        <?php else: ?>
            <div class="status-watermark wm-unpaid">BELUM LUNAS</div>
        <?php endif; ?>

        <div class="content-wrapper">

            <div class="top-strip">
                <div class="left">
                    <i class="ph-bold ph-shopping-cart"></i>
                    <span>Dokumen Procurement & Purchasing</span>
                </div>
                <div class="right">
                    <i class="ph-bold ph-calendar-blank"></i>
                    <span><?= date('d F Y') ?></span>
                </div>
            </div>

            <div class="header">
                <div class="company-info">
                    <div class="company-logo-wrap">
                        <?php if (isset($company['logo_path']) && !empty($company['logo_path']) && $company['logo_path'] !== 'default-logo.png'): ?>
                            <img src="<?= base_url('uploads/logo/' . esc($company['logo_path'])) ?>" alt="Logo" class="company-logo">
                        <?php else: ?>
                            <i class="ph-fill ph-factory" style="font-size: 32px; color: #0f172a;"></i>
                        <?php endif; ?>
                    </div>

                    <div class="company-text">
                        <h1><?= esc($company['company_name'] ?? 'PT. NORIC EXHAUST') ?></h1>
                        <div class="sub">
                            <b>DIVISI PROCUREMENT & PURCHASING</b><br>
                            <?= nl2br(esc($company['address'] ?? 'Alamat Belum Diatur')) ?><br>
                            Telp: <b><?= esc($company['phone'] ?? '-') ?></b>
                        </div>
                    </div>
                </div>

                <div class="doc-title">
                    <div class="doc-label">Vendor Purchase Document</div>
                    <h2>Purchase Order</h2>
                    <img src="https://bwipjs-api.metafloor.com/?bcid=code128&text=<?= esc($po['po_number']) ?>&scale=2&height=8&includetext=false" alt="Barcode" class="barcode-img">
                    <p><?= esc($po['po_number']) ?></p>
                </div>
            </div>

            <div class="meta-grid">
                <div class="meta-box alt">
                    <h4><i class="ph-bold ph-info"></i> Informasi Dokumen</h4>
                    <table class="meta-table">
                        <tr>
                            <td class="meta-label">Tgl. Terbit</td>
                            <td class="meta-val">: <?= date('d M Y', strtotime($po['po_date'])) ?></td>
                        </tr>
                        <tr>
                            <td class="meta-label">Termin Bayar</td>
                            <td class="meta-val">: <?= esc($po['payment_term'] ?? 'Cash') ?></td>
                        </tr>
                        <tr>
                            <td class="meta-label">Status Bayar</td>
                            <td class="meta-val">:
                                <?php if($po['payment_status'] === 'PAID'): ?>
                                    <span style="font-weight:900;color:#16a34a;">LUNAS</span>
                                <?php elseif($po['payment_status'] === 'PARTIAL'): ?>
                                    <span style="font-weight:900;color:#d97706;">DICICIL</span>
                                <?php else: ?>
                                    <span style="font-weight:900;color:#dc2626;">BELUM LUNAS</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="meta-label">Status Barang</td>
                            <td class="meta-val">:
                                <?php if(($po['receipt_status'] ?? 'PENDING') === 'FULLY_RECEIVED'): ?>
                                    <span style="font-weight:900;color:#16a34a;">DITERIMA PENUH</span>
                                <?php elseif(($po['receipt_status'] ?? 'PENDING') === 'PARTIAL'): ?>
                                    <span style="font-weight:900;color:#d97706;">DITERIMA SEBAGIAN</span>
                                <?php else: ?>
                                    <span style="font-weight:900;color:#dc2626;">BELUM DITERIMA</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    </table>
                </div>

                <div class="meta-box">
                    <h4><i class="ph-bold ph-currency-circle-dollar"></i> Ringkasan Pembayaran</h4>
                    <table class="meta-table">
                        <tr>
                            <td class="meta-label">Total PO</td>
                            <td class="meta-val bold">: Rp <?= number_format($totalAmt, 0, ',', '.') ?></td>
                        </tr>
                        <tr>
                            <td class="meta-label">Sudah Dibayar</td>
                            <td class="meta-val">: Rp <?= number_format($paidAmt, 0, ',', '.') ?></td>
                        </tr>
                        <tr>
                            <td class="meta-label">Sisa Tagihan</td>
                            <td class="meta-val" style="font-weight:900;">: Rp <?= number_format(abs($balance), 0, ',', '.') ?></td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="address-grid">
                <div class="address-box">
                    <h4><i class="ph-bold ph-truck"></i> Vendor Tujuan (To)</h4>
                    <h3><?= esc($po['supplier_name']) ?></h3>
                    <p><?= nl2br(esc(!empty($po['supplier_address']) ? $po['supplier_address'] : '-')) ?></p>
                    <div class="addr-row"><span>ATTN</span>: <?= esc(!empty($po['contact_person']) ? $po['contact_person'] : '-') ?></div>
                    <div class="addr-row"><span>TELP</span>: <?= esc(!empty($po['supplier_phone']) ? $po['supplier_phone'] : '-') ?></div>
                </div>

                <div class="address-box">
                    <h4><i class="ph-bold ph-map-pin-line"></i> Kirim Barang Ke (Ship To)</h4>
                    <h3><?= esc($company['company_name'] ?? 'PT NORIC EXHAUST') ?></h3>
                    <p><?= nl2br(esc($company['address'] ?? 'Alamat Belum Diatur')) ?></p>
                    <div class="addr-row"><span>ATTN</span>: Dept. Gudang & Logistik</div>
                    <div class="addr-row"><span>TELP</span>: <?= esc($company['phone'] ?? '-') ?></div>
                </div>
            </div>

            <div class="section-title-sm">
                <i class="ph-fill ph-package" style="color:var(--accent);"></i> Detail Material / Barang
            </div>

            <div class="table-wrap">
                <table class="item-table">
                    <thead>
                        <tr>
                            <th width="5%">No</th>
                            <th width="35%">Material / Barang</th>
                            <th width="13%">Dipesan</th>
                            <th width="13%">Diterima</th>
                            <th width="14%">Harga Sat.</th>
                            <th width="20%">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(!empty($items)): ?>
                            <?php $no=1; foreach($items as $item): 
                                $unitBeli = esc($item['purchase_uom'] ?? $item['unit'] ?? 'PCS');
                            ?>
                            <tr>
                                <td class="text-center no-cell"><?= $no++ ?></td>
                                <td>
                                    <div class="product-inline">
                                        <span class="desc-text"><?= esc($item['material_name'] ?: '-') ?></span><br>
                                        <span class="sku-badge"><?= esc($item['rm_sku']) ?></span>
                                    </div>
                                </td>
                                <td class="text-center qty-val">
                                    <?= floatval($item['qty']) ?> <?= $unitBeli ?>
                                </td>
                                <td class="text-center qty-val">
                                    <?php 
                                        $rcvPurchase = floatval($item['qty_received']) / floatval($item['conversion_factor']);
                                        $isComplete = ($rcvPurchase >= floatval($item['qty']));
                                    ?>
                                    <span style="color: <?= $isComplete ? '#16a34a' : ($rcvPurchase > 0 ? '#d97706' : '#dc2626') ?>;">
                                        <?= floatval($rcvPurchase) ?> <?= $unitBeli ?>
                                    </span>
                                </td>
                                <td class="text-right qty-val">
                                    Rp <?= number_format($item['unit_price'], 0, ',', '.') ?>
                                </td>
                                <td class="text-right qty-val" style="font-weight:900;">
                                    Rp <?= number_format($item['subtotal'], 0, ',', '.') ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center" style="font-style: italic; color: var(--muted);">
                                    Tidak ada item pembelian.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="bottom-grid">
                <div class="notes-section">
                    <strong>Syarat & Ketentuan</strong>
                    <ol>
                        <li>Vendor wajib mencantumkan Nomor PO pada Invoice dan Surat Jalan.</li>
                        <li>Barang yang dikirim harus sesuai spesifikasi, kuantitas, dan standar kualitas yang disepakati.</li>
                        <li>Barang cacat, rusak, atau tidak sesuai dapat diretur dan menjadi tanggung jawab Vendor.</li>
                        <li>Pembayaran dilakukan sesuai termin yang tercantum setelah dokumen penerimaan diverifikasi.</li>
                    </ol>
                </div>

                <div class="summary-card">
                    <div class="sum-line">
                        <span>Subtotal Barang</span>
                        <span>Rp <?= number_format($po['subtotal'], 0, ',', '.') ?></span>
                    </div>
                    <div class="sum-line">
                        <span>Pajak (PPN)</span>
                        <span>Rp <?= number_format($po['tax_amount'], 0, ',', '.') ?></span>
                    </div>
                    <div class="sum-line">
                        <span>Biaya Ongkir</span>
                        <span>Rp <?= number_format($po['shipping_cost'], 0, ',', '.') ?></span>
                    </div>
                    <div class="sum-line total">
                        <span>Total Tagihan PO</span>
                        <span>Rp <?= number_format($totalAmt, 0, ',', '.') ?></span>
                    </div>
                    <div class="sum-line paid">
                        <span>Sudah Dibayar</span>
                        <span>- Rp <?= number_format($paidAmt, 0, ',', '.') ?></span>
                    </div>

                    <?php 
                        $cls = 'grand-unpaid';
                        if($balance <= 0) $cls = 'grand-paid';
                        elseif($paidAmt > 0) $cls = 'grand-partial';
                    ?>
                    <div class="grand-total <?= $cls ?>">
                        <span class="label"><?= $balance <= 0 ? 'Status: Lunas' : 'Sisa Tagihan' ?></span>
                        <span class="value">Rp <?= number_format(abs($balance), 0, ',', '.') ?></span>
                    </div>
                </div>
            </div>

            <div class="signature-section">
                <div class="sig-box">
                    <p>Disetujui Oleh (Pembeli)</p>
                    <div class="sig-space"></div>
                    <div class="sig-line"></div>
                    <div class="sig-name">Procurement & Finance</div>
                </div>
                <div class="sig-box">
                    <p>Diterima Oleh (Vendor)</p>
                    <div class="sig-space"></div>
                    <div class="sig-line"></div>
                    <div class="sig-name"><?= esc($po['supplier_name']) ?></div>
                </div>
            </div>

            <div class="footer-note">
                <span>Form No: PROC-PO-001 • Rev: 05</span>
                <span>Generated by <?= esc($company['app_name'] ?? 'Noric ERP') ?> - <?= date('d/m/Y H:i') ?></span>
            </div>

        </div>
    </div>

</body>
</html>