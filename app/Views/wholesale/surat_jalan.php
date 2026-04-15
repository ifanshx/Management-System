<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surat Jalan - <?= esc($so['so_number']) ?> | <?= esc($company['app_name'] ?? 'Noric ERP') ?></title>

    <?php if (isset($company['logo_path']) && !empty($company['logo_path'])): ?>
        <link rel="icon" type="image/png" href="<?= base_url('uploads/logo/' . esc($company['logo_path'])) ?>">
        <link rel="apple-touch-icon" href="<?= base_url('uploads/logo/' . esc($company['logo_path'])) ?>">
    <?php endif; ?>

    <script src="https://unpkg.com/@phosphor-icons/web"></script>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Space+Mono:wght@400;700&display=swap');

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        :root {
            --ink: #0f172a;
            --muted: #475569;
            --soft: #64748b;
            --line: #cbd5e1;
            --line-dark: #94a3b8;
            --paper: #ffffff;
            --bg: #e2e8f0;
            --head: #f8fafc;
            --accent: #0f172a;
            --warning: #b45309;
            --danger: #b91c1c;
            --success: #166534;
        }

        body {
            font-family: 'Inter', Arial, sans-serif;
            font-size: 11px;
            color: var(--ink);
            background: var(--bg);
            padding: 18px;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        .container {
            width: 100%;
            max-width: 820px;
            margin: 0 auto;
            background: var(--paper);
            padding: 26px 28px;
            box-shadow: 0 10px 28px rgba(15, 23, 42, 0.10);
            border-radius: 14px;
            position: relative;
            overflow: hidden;
        }

        .watermark-bg {
            position: absolute;
            inset: 0;
            <?php if (isset($company['logo_path']) && !empty($company['logo_path'])): ?>
            background-image: url('<?= base_url('uploads/logo/' . esc($company['logo_path'])) ?>');
            <?php endif; ?>
            background-repeat: repeat;
            background-size: 170px;
            background-position: center;
            opacity: 0.025;
            transform: rotate(-22deg) scale(1.15);
            z-index: 1;
            pointer-events: none;
        }

        .content-wrapper {
            position: relative;
            z-index: 5;
        }

        /* ACTION BAR */
        .action-bar {
            text-align: center;
            margin-bottom: 20px;
        }

        .btn-print {
            background: linear-gradient(135deg, #0f172a, #1e293b);
            color: #fff;
            border: none;
            padding: 11px 22px;
            font-size: 13px;
            font-weight: 800;
            border-radius: 10px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 8px 20px rgba(15, 23, 42, 0.22);
            transition: all .2s ease;
        }

        .btn-print:hover {
            transform: translateY(-1px);
        }

        .print-guide {
            display: inline-block;
            text-align: left;
            background: #ffffff;
            padding: 10px 16px;
            border-radius: 10px;
            border: 1px dashed var(--line-dark);
            font-size: 11px;
            color: #334155;
            margin-top: 12px;
            line-height: 1.6;
        }

        /* TOP STRIP */
        .top-strip {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: linear-gradient(90deg, #0f172a, #1e293b);
            color: #fff;
            padding: 8px 14px;
            border-radius: 10px;
            margin-bottom: 14px;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.2px;
        }

        .top-strip .left,
        .top-strip .right {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        /* HEADER */
        .header {
            border-bottom: 2px solid #0f172a;
            padding-bottom: 14px;
            margin-bottom: 16px;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 18px;
        }

        .company-info {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            flex: 1;
        }

        .company-logo-wrap {
            width: 56px;
            height: 56px;
            border: 1px solid var(--line);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #fff;
            flex-shrink: 0;
            overflow: hidden;
        }

        .company-logo {
            width: 46px;
            height: 46px;
            object-fit: contain;
        }

        .company-text h1 {
            font-size: 20px;
            font-weight: 900;
            margin-bottom: 4px;
            text-transform: uppercase;
            letter-spacing: -0.4px;
            line-height: 1.1;
        }

        .company-text .sub {
            font-size: 10px;
            color: var(--muted);
            line-height: 1.5;
            font-weight: 600;
        }

        .doc-title {
            text-align: right;
            min-width: 200px;
        }

        .doc-title .doc-label {
            font-size: 10px;
            text-transform: uppercase;
            font-weight: 800;
            color: var(--soft);
            letter-spacing: 1.2px;
            margin-bottom: 5px;
        }

        .doc-title h2 {
            font-size: 24px;
            text-transform: uppercase;
            font-weight: 900;
            letter-spacing: 0.8px;
            margin-bottom: 7px;
            line-height: 1;
        }

        .doc-title p {
            font-family: 'Space Mono', monospace;
            font-size: 12px;
            font-weight: 700;
            border: 1.5px solid #0f172a;
            padding: 5px 10px;
            display: inline-block;
            background: rgba(241, 245, 249, 0.95);
            border-radius: 8px;
        }

        /* META */
        .meta-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-bottom: 14px;
        }

        .meta-box {
            border: 1px solid var(--line);
            padding: 11px 13px;
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.9);
        }

        .meta-box.alt {
            background: rgba(248, 250, 252, 0.95);
        }

        .meta-box h4 {
            font-size: 10px;
            text-transform: uppercase;
            font-weight: 900;
            border-bottom: 1px dashed var(--line-dark);
            padding-bottom: 6px;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 7px;
            letter-spacing: 0.35px;
        }

        .meta-table {
            width: 100%;
            border-collapse: collapse;
        }

        .meta-table td {
            padding: 3px 0;
            font-size: 10.5px;
            vertical-align: top;
        }

        .meta-label {
            width: 92px;
            font-weight: 800;
            color: var(--muted);
            white-space: nowrap;
        }

        .meta-val {
            font-weight: 700;
            color: var(--ink);
        }

        .meta-val.bold {
            font-weight: 900;
            font-size: 12px;
        }

        .status-chip {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 9px;
            border-radius: 999px;
            font-size: 10px;
            font-weight: 900;
            letter-spacing: 0.2px;
            border: 1px solid transparent;
        }

        .status-full {
            background: #dcfce7;
            color: #166534;
            border-color: #86efac;
        }

        .status-partial {
            background: #fef3c7;
            color: #92400e;
            border-color: #fcd34d;
        }

        .status-wait {
            background: #e2e8f0;
            color: #334155;
            border-color: #cbd5e1;
        }

        .line-input {
            display: inline-block;
            border-bottom: 1px dotted #0f172a;
            width: 100%;
            height: 13px;
            margin-bottom: -2px;
        }

        /* TABLE */
        .table-wrap {
            border: 1.5px solid #0f172a;
            border-radius: 12px;
            overflow: hidden;
            margin-bottom: 12px;
            background: rgba(255, 255, 255, 0.95);
        }

        .item-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .item-table thead th {
            background: #0f172a;
            color: #fff;
            text-align: center;
            font-weight: 800;
            text-transform: uppercase;
            font-size: 9.5px;
            letter-spacing: 0.3px;
            padding: 9px 6px;
            border-right: 1px solid rgba(255,255,255,0.12);
        }

        .item-table thead th:last-child {
            border-right: none;
        }

        .item-table tbody td {
            border-top: 1px solid #cbd5e1;
            padding: 7px 6px;
            font-size: 10.5px;
            font-weight: 600;
            vertical-align: middle;
            line-height: 1.35;
        }

        .item-table tbody tr:nth-child(even) {
            background: #f8fafc;
        }

        .text-center {
            text-align: center !important;
        }

        .no-cell {
            font-weight: 900;
            font-size: 11px;
        }

        .sku-badge {
            display: inline-block;
            font-family: 'Space Mono', monospace;
            font-size: 9.5px;
            font-weight: 700;
            color: #0f172a;
            background: #f1f5f9;
            border: 1px solid #cbd5e1;
            border-radius: 999px;
            padding: 3px 7px;
            white-space: nowrap;
        }

        /* ===== INI BAGIAN PENTING YANG ANDA MAU ===== */
        .product-inline {
            white-space: normal;
            word-break: break-word;
            line-height: 1.35;
        }

        .desc-text {
            font-size: 11px;
            font-weight: 800;
            display: inline;
            color: var(--ink);
        }

        .note-inline {
            font-size: 10px;
            color: #64748b;
            font-style: italic;
            font-weight: 700;
            display: inline;
            margin-left: 4px;
        }
        /* =========================================== */

        .qty-val {
            font-family: 'Space Mono', monospace;
            font-size: 11px;
            font-weight: 700;
            white-space: nowrap;
        }

        .qty-shipped {
            font-family: 'Space Mono', monospace;
            font-size: 11px;
            font-weight: 900;
            background: #ecfeff;
            color: #0f172a;
            display: inline-block;
            padding: 4px 8px;
            border: 1px solid #0f172a;
            border-radius: 8px;
            min-width: 64px;
            white-space: nowrap;
        }

        .qty-remaining-zero {
            color: #64748b;
            font-weight: 800;
        }

        .qty-remaining-pending {
            color: var(--warning);
            font-weight: 900;
        }

        .check-box {
            width: 15px;
            height: 15px;
            border: 1.5px solid #0f172a;
            border-radius: 3px;
            margin: 0 auto;
            background: #fff;
        }

        /* SUMMARY */
        .summary-bar {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 12px;
        }

        .summary-box {
            min-width: 230px;
            border: 1px solid var(--line);
            border-radius: 10px;
            overflow: hidden;
            background: #fff;
        }

        .summary-box .head {
            background: #0f172a;
            color: #fff;
            padding: 8px 12px;
            font-size: 10px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.35px;
        }

        .summary-box .body {
            padding: 10px 12px;
            display: grid;
            gap: 6px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            font-size: 10.5px;
        }

        .summary-row .label {
            color: var(--muted);
            font-weight: 800;
        }

        .summary-row .value {
            font-family: 'Space Mono', monospace;
            font-weight: 900;
            color: var(--ink);
            white-space: nowrap;
        }

        /* NOTES */
        .notes-section {
            font-size: 9.8px;
            line-height: 1.55;
            margin-bottom: 16px;
            border: 1px dashed var(--line-dark);
            padding: 10px 12px;
            border-radius: 10px;
            background: rgba(248, 250, 252, 0.92);
        }

        .notes-section strong {
            font-weight: 900;
            text-transform: uppercase;
            display: inline-block;
            margin-bottom: 5px;
        }

        .notes-section ol {
            padding-left: 16px;
            margin-top: 4px;
        }

        .notes-section li {
            margin-bottom: 3px;
        }

        .warning-note {
            margin-top: 6px;
            display: block;
            font-weight: 900;
            color: var(--danger);
        }

        /* SIGNATURE */
        .signature-section {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            margin-top: 16px;
            text-align: center;
        }

        .sig-box {
            width: 33.33%;
            border: 1px solid var(--line);
            border-radius: 10px;
            padding: 10px 10px 8px;
            background: rgba(255, 255, 255, 0.95);
        }

        .sig-box p {
            font-size: 10.5px;
            font-weight: 800;
            margin: 0;
            color: var(--ink);
        }

        .sig-space {
            height: 58px;
        }

        .sig-line {
            border-top: 1px solid #0f172a;
            margin: 0 auto 5px;
            width: 88%;
        }

        .sig-name {
            font-size: 9.5px;
            font-weight: 700;
            color: #475569;
        }

        .footer-note {
            margin-top: 12px;
            text-align: center;
            font-size: 9px;
            color: #64748b;
            font-weight: 700;
            letter-spacing: 0.2px;
        }

        @page {
            size: A4 portrait;
            margin: 7mm 7mm 9mm 7mm;
        }

        @media print {
            body {
                padding: 0;
                background: #fff;
                font-size: 10px;
            }

            .container {
                box-shadow: none;
                border: none;
                border-radius: 0;
                padding: 0;
                max-width: 100%;
                width: 100%;
                margin: 0;
                overflow: visible;
            }

            .no-print {
                display: none !important;
            }

            .watermark-bg {
                position: fixed;
                inset: 0;
                width: 100vw;
                height: 100vh;
                z-index: 0;
            }

            table {
                page-break-inside: auto;
                border-collapse: collapse;
            }

            tr {
                page-break-inside: avoid;
                page-break-after: auto;
            }

            td, th {
                page-break-inside: avoid;
            }

            thead {
                display: table-header-group;
            }

            tfoot {
                display: table-footer-group;
            }

            .meta-grid,
            .notes-section,
            .signature-section,
            .summary-bar,
            .header,
            .top-strip {
                page-break-inside: avoid;
            }

            .table-wrap {
                border-radius: 0;
            }
        }
    </style>
</head>
<body>

    <div class="action-bar no-print">
        <button onclick="window.print()" class="btn-print">
            <i class="ph-bold ph-printer"></i> Cetak Surat Jalan (A4)
        </button>
        <br>
        <div class="print-guide">
            <strong>Panduan Cetak:</strong><br>
            1. Centang <b>"Background graphics"</b> agar watermark/logo ikut tercetak.<br>
            2. Hilangkan centang <b>"Headers and footers"</b> agar URL browser tidak ikut muncul.
        </div>
    </div>

    <div class="container">
        <div class="watermark-bg"></div>

        <div class="content-wrapper">

            <div class="top-strip">
                <div class="left">
                    <i class="ph-bold ph-package"></i>
                    <span>Dokumen Distribusi Barang</span>
                </div>
                <div class="right">
                    <i class="ph-bold ph-calendar-blank"></i>
                    <span><?= date('d F Y') ?></span>
                </div>
            </div>

            <div class="header">
                <div class="company-info">
                    <div class="company-logo-wrap">
                        <?php if (isset($company['logo_path']) && !empty($company['logo_path'])): ?>
                            <img src="<?= base_url('uploads/logo/' . esc($company['logo_path'])) ?>" alt="Logo" class="company-logo">
                        <?php else: ?>
                            <i class="ph-fill ph-factory" style="font-size: 32px; color: #0f172a;"></i>
                        <?php endif; ?>
                    </div>

                    <div class="company-text">
                        <h1><?= esc($company['company_name'] ?? 'PT. NORIC EXHAUST') ?></h1>
                        <div class="sub">
                            <?= esc($company['address'] ?? 'Kawasan Industri Pabrik Knalpot Purbalingga, Jawa Tengah') ?><br>
                            Telp/WA: <?= esc($company['phone'] ?? '(0281) 8899-7766') ?>
                        </div>
                    </div>
                </div>

                <div class="doc-title">
                    <div class="doc-label">Delivery Document</div>
                    <h2>Surat Jalan</h2>
                    <p><?= esc($so['so_number']) ?></p>
                </div>
            </div>

            <div class="meta-grid">
                <div class="meta-box alt">
                    <h4><i class="ph-bold ph-truck"></i> Rincian Pengiriman</h4>
                    <table class="meta-table">
                        <tr>
                            <td class="meta-label">Tgl. Cetak</td>
                            <td class="meta-val">: <?= date('d F Y') ?></td>
                        </tr>
                        <tr>
                            <td class="meta-label">Status Kirim</td>
                            <td class="meta-val">
                                :
                                <?php if ($so['shipping_status'] == 'SHIPPED'): ?>
                                    <span class="status-chip status-full">LENGKAP (FULL)</span>
                                <?php elseif ($so['shipping_status'] == 'PARTIAL-SHIPPED'): ?>
                                    <span class="status-chip status-partial">SEBAGIAN (PARSIAL)</span>
                                <?php else: ?>
                                    <span class="status-chip status-wait">MENUNGGU KIRIMAN</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="meta-label" style="padding-top:5px;">Plat Nomor</td>
                            <td>: <span class="line-input"></span></td>
                        </tr>
                        <tr>
                            <td class="meta-label" style="padding-top:5px;">Nama Supir</td>
                            <td>: <span class="line-input"></span></td>
                        </tr>
                    </table>
                </div>

                <div class="meta-box">
                    <h4><i class="ph-bold ph-buildings"></i> Tujuan Pengiriman</h4>
                    <table class="meta-table">
                        <tr>
                            <td class="meta-label">Pelanggan</td>
                            <td class="meta-val bold">: <?= esc($so['company_name']) ?></td>
                        </tr>
                        <tr>
                            <td class="meta-label">UP / Kontak</td>
                            <td class="meta-val">: <?= esc($so['contact_name'] ?? '-') ?> (<?= esc($so['phone'] ?? '-') ?>)</td>
                        </tr>
                        <tr>
                            <td class="meta-label">Alamat</td>
                            <td class="meta-val" style="line-height: 1.45;">: <?= esc($so['address'] ?? '-') ?></td>
                        </tr>
                    </table>
                </div>
            </div>

            <?php
                $totalOrderAll = 0;
                $totalKirimAll = 0;
                $totalSisaAll  = 0;
            ?>

            <div class="table-wrap">
                <table class="item-table">
                    <thead>
                        <tr>
                            <th width="5%">No</th>
                            <th width="15%">Kode</th>
                            <th width="40%">Deskripsi Produk</th>
                            <th width="12%">Order</th>
                            <th width="12%">Dikirim</th>
                            <th width="10%">Sisa</th>
                            <th width="6%">Cek</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; foreach ($items as $item): ?>
                            <?php
                                $qtyOrder   = (int) $item['qty'];
                                $qtyShipped = (int) $item['shipped_qty'];

                                // LOGIC TETAP - TIDAK DIUBAH
                                if ($so['shipping_status'] == 'SHIPPED') {
                                    $dikirimHariIni = $qtyOrder;
                                    $sisa = 0;
                                } else {
                                    $dikirimHariIni = $qtyShipped;
                                    $sisa = max(0, $qtyOrder - $qtyShipped);
                                }

                                $totalOrderAll += $qtyOrder;
                                $totalKirimAll += $dikirimHariIni;
                                $totalSisaAll  += $sisa;
                            ?>
                            <tr>
                                <td class="text-center no-cell"><?= $no++ ?></td>
                                <td class="text-center">
                                    <span class="sku-badge"><?= esc($item['fg_sku']) ?></span>
                                </td>
                                <td>
                                    <div class="product-inline">
                                        <span class="desc-text"><?= esc($item['item_name'] ?? 'Produk Knalpot') ?></span>
                                        <?php if (!empty($item['additional_note'])): ?>
                                            <span class="note-inline">— Note: <?= esc($item['additional_note']) ?></span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="text-center qty-val"><?= number_format($qtyOrder, 0, ',', '.') ?> Pcs</td>
                                <td class="text-center">
                                    <span class="qty-shipped"><?= number_format($dikirimHariIni, 0, ',', '.') ?> Pcs</span>
                                </td>
                                <td class="text-center qty-val <?= $sisa > 0 ? 'qty-remaining-pending' : 'qty-remaining-zero' ?>">
                                    <?= number_format($sisa, 0, ',', '.') ?> Pcs
                                </td>
                                <td class="text-center">
                                    <div class="check-box"></div>
                                </td>
                            </tr>
                        <?php endforeach; ?>

                        <?php
                            $rowCount = count($items);
                            if ($rowCount < 3):
                                for ($i = $rowCount; $i < 3; $i++):
                        ?>
                            <tr>
                                <td style="color: transparent;">-</td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                            </tr>
                        <?php
                                endfor;
                            endif;
                        ?>
                    </tbody>
                </table>
            </div>

            <div class="summary-bar">
                <div class="summary-box">
                    <div class="head">Ringkasan</div>
                    <div class="body">
                        <div class="summary-row">
                            <span class="label">Baris Item</span>
                            <span class="value"><?= count($items) ?></span>
                        </div>
                        <div class="summary-row">
                            <span class="label">Qty Order</span>
                            <span class="value"><?= number_format($totalOrderAll, 0, ',', '.') ?> Pcs</span>
                        </div>
                        <div class="summary-row">
                            <span class="label">Qty Dikirim</span>
                            <span class="value"><?= number_format($totalKirimAll, 0, ',', '.') ?> Pcs</span>
                        </div>
                        <div class="summary-row">
                            <span class="label">Qty Sisa</span>
                            <span class="value"><?= number_format($totalSisaAll, 0, ',', '.') ?> Pcs</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="notes-section">
                <strong>Catatan & Syarat Penerimaan</strong>
                <ol>
                    <li>Dokumen ini merupakan bukti serah terima fisik barang.</li>
                    <li>Penerima wajib memeriksa jumlah, jenis, dan kondisi barang sebelum menandatangani.</li>
                    <li>Selisih / kerusakan wajib dicatat saat serah terima berlangsung.</li>
                    <li>Dokumen yang ditandatangani tanpa catatan dianggap telah diterima sesuai.</li>
                </ol>

                <?php if ($so['shipping_status'] == 'PARTIAL-SHIPPED'): ?>
                    <span class="warning-note">
                        PERHATIAN: Pengiriman berstatus PARSIAL. Barang pada kolom "Sisa" akan dikirim menyusul.
                    </span>
                <?php endif; ?>
            </div>

            <div class="signature-section">
                <div class="sig-box">
                    <p>Penerima</p>
                    <div class="sig-space"></div>
                    <div class="sig-line"></div>
                    <div class="sig-name">( Nama, TTD & Cap )</div>
                </div>

                <div class="sig-box">
                    <p>Supir / Pengirim</p>
                    <div class="sig-space"></div>
                    <div class="sig-line"></div>
                    <div class="sig-name">( Nama Jelas & TTD )</div>
                </div>

                <div class="sig-box">
                    <p>Gudang / Pabrik</p>
                    <div class="sig-space"></div>
                    <div class="sig-line"></div>
                    <div class="sig-name">( <?= esc($company['company_name'] ?? 'PT. Noric Manufaktur') ?> )</div>
                </div>
            </div>

            <div class="footer-note">
                Dicetak melalui sistem ERP • Dokumen operasional distribusi barang
            </div>

        </div>
    </div>

</body>
</html>