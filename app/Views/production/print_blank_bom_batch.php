<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Formulir Resep Produksi (Blank)') ?> | <?= esc($company['app_name'] ?? 'Noric ERP') ?></title>

    <?php if (isset($company['logo_path']) && !empty($company['logo_path'])): ?>
        <link rel="icon" type="image/png" href="<?= base_url('uploads/logo/' . esc($company['logo_path'])) ?>">
    <?php endif; ?>

    <script src="https://unpkg.com/@phosphor-icons/web"></script>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Space+Mono:wght@400;700&display=swap');

        * { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --ink: #0f172a; --muted: #475569; --soft: #64748b;
            --line: #cbd5e1; --line-dark: #94a3b8;
            --paper: #ffffff; --bg: #e2e8f0; --head: #f8fafc;
            --accent: #2563eb; --success: #10b981;
        }

        body {
            font-family: 'Inter', Arial, sans-serif;
            font-size: 10px; color: var(--ink); background: var(--bg);
            padding: 14px; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important;
        }

        .container { width: 100%; max-width: 820px; margin: 0 auto; }

        .a4-wrapper {
            background: var(--paper); padding: 26px 28px;
            box-shadow: 0 10px 28px rgba(15, 23, 42, 0.10); border-radius: 14px;
            position: relative; overflow: hidden; margin-bottom: 30px;
        }

        .watermark-bg {
            position: absolute; inset: 0;
            <?php if (isset($company['logo_path']) && !empty($company['logo_path'])): ?>
            background-image: url('<?= base_url('uploads/logo/' . esc($company['logo_path'])) ?>');
            <?php endif; ?>
            background-repeat: repeat; background-size: 150px; background-position: center;
            opacity: 0.02; transform: rotate(-22deg) scale(1.15); z-index: 1; pointer-events: none;
        }

        .content-wrapper { position: relative; z-index: 5; }

        .action-bar { text-align: center; margin-bottom: 20px; }
        .btn-print { background: linear-gradient(135deg, #10b981, #059669); color: #fff; border: none; padding: 11px 22px; font-size: 13px; font-weight: 800; border-radius: 10px; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 8px 20px rgba(16, 185, 129, 0.22); transition: all .2s ease; }
        .btn-print:hover { transform: translateY(-1px); }
        .print-guide { display: inline-block; text-align: left; background: #ffffff; padding: 10px 16px; border-radius: 10px; border: 1px dashed var(--line-dark); font-size: 11px; color: #334155; margin-top: 12px; line-height: 1.6; }

        .top-strip { display: flex; justify-content: space-between; align-items: center; background: linear-gradient(90deg, #14532d, #16a34a); color: #fff; padding: 8px 14px; border-radius: 10px; margin-bottom: 14px; font-size: 10px; font-weight: 700; letter-spacing: 0.2px; }
        .top-strip .left, .top-strip .right { display: flex; align-items: center; gap: 6px; }

        .header { border-bottom: 2px solid #0f172a; padding-bottom: 14px; margin-bottom: 16px; display: flex; justify-content: space-between; align-items: flex-start; gap: 18px; }
        .company-info { display: flex; align-items: flex-start; gap: 12px; flex: 1; }
        .company-logo-wrap { width: 56px; height: 56px; border: 1px solid var(--line); border-radius: 10px; display: flex; align-items: center; justify-content: center; background: #fff; flex-shrink: 0; overflow: hidden; }
        .company-logo { width: 46px; height: 46px; object-fit: contain; }
        .company-text h1 { font-size: 20px; font-weight: 900; margin-bottom: 4px; text-transform: uppercase; letter-spacing: -0.4px; line-height: 1.1; }
        .company-text .sub { font-size: 10px; color: var(--muted); line-height: 1.5; font-weight: 600; }

        .doc-title { text-align: right; min-width: 200px; display: flex; flex-direction: column; align-items: flex-end; }
        .doc-title .doc-label { font-size: 10px; text-transform: uppercase; font-weight: 800; color: var(--soft); letter-spacing: 1.2px; margin-bottom: 5px; }
        .doc-title h2 { font-size: 20px; text-transform: uppercase; font-weight: 900; letter-spacing: 0.5px; margin-bottom: 4px; line-height: 1; }
        .doc-code { font-family: 'Space Mono', monospace; font-size: 12px; font-weight: 700; border: 1.5px solid #0f172a; padding: 4px 8px; display: inline-block; background: rgba(241, 245, 249, 0.95); border-radius: 8px; margin-top: 5px; }

        .meta-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 14px; }
        .meta-box { border: 1px solid var(--line); padding: 11px 13px; border-radius: 10px; background: rgba(255, 255, 255, 0.9); }
        .meta-box.alt { background: rgba(240, 253, 244, 0.8); }
        .meta-box h4 { font-size: 10px; text-transform: uppercase; font-weight: 900; border-bottom: 1px dashed var(--line-dark); padding-bottom: 6px; margin-bottom: 8px; display: flex; align-items: center; gap: 7px; letter-spacing: 0.35px; }
        .meta-table { width: 100%; border-collapse: collapse; }
        .meta-table td { padding: 3px 0; font-size: 10.5px; vertical-align: top; }
        .meta-label { width: 100px; font-weight: 800; color: var(--muted); white-space: nowrap; }
        .meta-val { font-weight: 700; color: var(--ink); }
        .meta-val.bold { font-weight: 900; font-size: 12px; }

        .sku-badge { display: inline-block; font-family: 'Space Mono', monospace; font-size: 9.5px; font-weight: 700; background: #f1f5f9; border: 1px solid #cbd5e1; border-radius: 999px; padding: 3px 7px; }

        .section-title-sm { font-size: 11px; font-weight: 900; text-transform: uppercase; margin-bottom: 8px; margin-top: 15px; display: flex; align-items: center; gap: 6px; letter-spacing: 0.5px; }
        .table-wrap { border: 1.5px solid #0f172a; border-radius: 12px; overflow: hidden; margin-bottom: 12px; background: rgba(255, 255, 255, 0.95); }
        .item-table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        .item-table thead th { background: #0f172a; color: #fff; text-align: center; font-weight: 800; text-transform: uppercase; font-size: 9.5px; padding: 9px 6px; border-right: 1px solid rgba(255,255,255,0.12); }
        .item-table thead th:last-child { border-right: none; }
        .item-table tbody td { border-top: 1px solid #cbd5e1; padding: 7px 6px; font-size: 10.5px; font-weight: 600; vertical-align: middle; min-height: 28px; }
        .item-table tbody tr:nth-child(even) { background: #f8fafc; }
        .text-center { text-align: center !important; }
        .no-cell { font-weight: 900; font-size: 11px; color: #94a3b8; }

        .notes-section { font-size: 9.8px; line-height: 1.55; margin-bottom: 16px; border: 1px dashed var(--line-dark); padding: 10px 12px; border-radius: 10px; background: rgba(248, 250, 252, 0.92); }
        .notes-section strong { font-weight: 900; text-transform: uppercase; display: inline-block; margin-bottom: 5px; }
        .notes-section ol { padding-left: 16px; margin-top: 4px; }
        .notes-section li { margin-bottom: 3px; }

        .signature-section { display: flex; justify-content: space-between; gap: 12px; margin-top: 16px; text-align: center; }
        .sig-box { width: 48%; border: 1px solid var(--line); border-radius: 10px; padding: 10px 8px 8px; background: rgba(255, 255, 255, 0.95); }
        .sig-box p { font-size: 10px; font-weight: 800; margin: 0; color: var(--ink); }
        .sig-space { height: 60px; }
        .sig-line { border-top: 1px solid #0f172a; margin: 0 auto 5px; width: 80%; }
        .sig-name { font-size: 9px; font-weight: 700; color: #475569; }

        .footer-note { margin-top: 12px; display: flex; justify-content: space-between; font-size: 9px; color: #64748b; font-weight: 700; letter-spacing: 0.2px; }

        .empty-box { background: white; border-radius: 16px; padding: 40px; text-align: center; box-shadow: 0 10px 28px rgba(15, 23, 42, 0.08); }

        @page { size: A4 portrait; margin: 7mm 7mm 9mm 7mm; }

        @media print {
            body { padding: 0; background: #fff; font-size: 10px; }
            .container { box-shadow: none; border: none; border-radius: 0; padding: 0; max-width: 100%; width: 100%; margin: 0; overflow: visible; }
            .no-print { display: none !important; }
            .a4-wrapper { box-shadow: none; border: none; border-radius: 0; padding: 0; margin: 0; page-break-after: always; min-height: auto; }
            .a4-wrapper:last-child { page-break-after: auto; }
            .watermark-bg { position: fixed; inset: 0; width: 100vw; height: 100vh; z-index: 0; }
            table { page-break-inside: auto; border-collapse: collapse; }
            tr, td, th { page-break-inside: avoid !important; }
            thead { display: table-header-group; }
            tfoot { display: table-footer-group; }
            .meta-grid, .notes-section, .signature-section, .header, .top-strip, .section-title-sm, .table-wrap, .footer-note { page-break-inside: avoid !important; }
        }
    </style>
</head>
<body>

<?php $products = $products ?? []; ?>

<?php if (empty($products)): ?>
    <div class="container">
        <div class="empty-box">
            <h2 style="margin-bottom:10px;">Data Produk Kosong</h2>
            <p style="font-size:14px; color:#64748b;">Tidak ada produk <b>PRD-</b> yang ditemukan untuk dicetak.</p>
        </div>
    </div>
<?php else: ?>

    <div class="action-bar no-print">
        <button onclick="window.print()" class="btn-print">
            <i class="ph-bold ph-file-dashed"></i> Cetak Semua Form Blank BOM
        </button>
        <br>
        <div class="print-guide">
            <strong>Panduan Cetak Massal:</strong><br>
            1. Sistem akan otomatis mencetak <b>1 Lembar Form per Produk</b>.<br>
            2. Centang <b>"Background graphics"</b> agar watermark/garis tercetak.<br>
            3. Kertas ini ditujukan untuk diisi manual (tulis tangan) oleh Kepala Produksi.
        </div>
    </div>

    <div class="container">
        <?php foreach ($products as $prd): ?>
        <div class="a4-wrapper">
            <div class="watermark-bg"></div>

            <div class="content-wrapper">
                <div class="top-strip">
                    <div class="left"><i class="ph-bold ph-factory"></i><span>Dokumen Blank Master Produksi</span></div>
                    <div class="right"><i class="ph-bold ph-calendar-blank"></i><span><?= date('d F Y') ?></span></div>
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
                            <div class="sub"><b>DIVISI PRODUKSI PUSAT</b><br>Formulir Pengisian Standar Operasional Produksi</div>
                        </div>
                    </div>

                    <div class="doc-title">
                        <div class="doc-label">Blank Production Form</div>
                        <h2>Form Blank BOM</h2>
                        <div class="doc-code"><?= esc($prd['sku'] ?? '-') ?></div>
                    </div>
                </div>

                <div class="meta-grid">
                    <div class="meta-box alt">
                        <h4><i class="ph-bold ph-package"></i> Identitas Produk</h4>
                        <table class="meta-table">
                            <tr>
                                <td class="meta-label">SKU Produk</td>
                                <td class="meta-val">: <span class="sku-badge"><?= esc($prd['sku'] ?? '-') ?></span></td>
                            </tr>
                            <tr>
                                <td class="meta-label">Nama Produk</td>
                                <td class="meta-val bold">: <?= esc($prd['item_name'] ?? '-') ?></td>
                            </tr>
                            <tr>
                                <td class="meta-label">Kategori</td>
                                <td class="meta-val">: <?= esc($prd['item_type'] ?? 'Produk Umum') ?></td>
                            </tr>
                        </table>
                    </div>

                    <div class="meta-box">
                        <h4><i class="ph-bold ph-note-pencil"></i> Identitas Dokumen</h4>
                        <table class="meta-table">
                            <tr>
                                <td class="meta-label">Judul Resep / SOP</td>
                                <td class="meta-val">: _____________________________</td>
                            </tr>
                            <tr>
                                <td class="meta-label">Revisi Ke</td>
                                <td class="meta-val">: _____________________________</td>
                            </tr>
                            <tr>
                                <td class="meta-label">Tanggal Pengisian</td>
                                <td class="meta-val">: _____________________________</td>
                            </tr>
                        </table>
                    </div>
                </div>

                <div class="section-title-sm"><i class="ph-fill ph-cube" style="color:var(--accent);"></i> Kebutuhan Material & Bahan Penolong (Tulis Manual)</div>
                <div class="table-wrap">
                    <table class="item-table">
                        <thead>
                            <tr>
                                <th width="5%">No</th>
                                <th width="20%">SKU / Kode (Bila Ada)</th>
                                <th width="45%">Nama Material / Komponen</th>
                                <th width="15%">Qty / Pcs</th>
                                <th width="15%">Satuan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php for ($i = 1; $i <= 10; $i++): ?>
                            <tr>
                                <td class="text-center no-cell"><?= $i ?></td>
                                <td></td><td></td><td></td><td></td>
                            </tr>
                            <?php endfor; ?>
                        </tbody>
                    </table>
                </div>

                <div class="section-title-sm"><i class="ph-fill ph-kanban" style="color:var(--accent);"></i> Routing / Tahapan Produksi (Tulis Manual)</div>
                <div class="table-wrap">
                    <table class="item-table">
                        <thead>
                            <tr>
                                <th width="6%">Tahap</th>
                                <th width="60%">Instruksi Operasi Kerja (Dari awal perakitan sampai akhir)</th>
                                <th width="34%">Keterangan Tambahan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php for ($j = 1; $j <= 8; $j++): ?>
                            <tr>
                                <td class="text-center no-cell"><?= $j ?></td>
                                <td></td><td></td>
                            </tr>
                            <?php endfor; ?>
                        </tbody>
                    </table>
                </div>

                <div class="notes-section">
                    <strong>Petunjuk Pengisian & SOP</strong>
                    <ol>
                        <li>Isi seluruh kebutuhan material utama, bahan pembantu, dan kemasan secara mendetail per 1 buah (Pcs) knalpot jadi.</li>
                        <li>Tuliskan urutan proses kerja (Routing) secara sistematis. Mulai dari pemotongan, bending, perakitan, hingga tahap las akhir.</li>
                        <li><b>PERHATIAN:</b> Tahapan yang Anda tulis paling bawah akan di-set oleh sistem ERP sebagai "TAHAP FINAL" untuk pemotongan gudang.</li>
                        <li>Setelah formulir ini disetujui, serahkan kepada Admin ERP agar dimasukkan ke dalam Database Master.</li>
                    </ol>
                </div>

                <div class="signature-section">
                    <div class="sig-box">
                        <p>Disusun Oleh (Ka. Produksi)</p>
                        <div class="sig-space"></div>
                        <div class="sig-line"></div>
                        <div class="sig-name">Mas Anjal</div>
                    </div>
                    <div class="sig-box">
                        <p>Diinput Ke Sistem Oleh</p>
                        <div class="sig-space"></div>
                        <div class="sig-line"></div>
                        <div class="sig-name">Admin ERP</div>
                    </div>
                </div>

                <div class="footer-note">
                    <span>Form No: MFG-BLANK-BOM-001</span>
                    <span>Printed via <?= esc($company['app_name'] ?? 'Noric ERP') ?> - <?= date('d/m/Y') ?></span>
                </div>

            </div>
        </div>
        <?php endforeach; ?>
    </div>

<?php endif; ?>

</body>
</html>