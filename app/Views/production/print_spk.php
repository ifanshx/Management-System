<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SPK - <?= esc($spk['spk_number']) ?> | <?= esc($company['app_name'] ?? 'Noric ERP') ?></title>
    <?php if (isset($company['logo_path']) && !empty($company['logo_path'])): ?>
        <link rel="icon" type="image/png" href="<?= base_url('uploads/logo/' . esc($company['logo_path'])) ?>">
        <link rel="apple-touch-icon" href="<?= base_url('uploads/logo/' . esc($company['logo_path'])) ?>">
    <?php endif; ?>
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Space+Mono:wght@400;700&display=swap');
        * { box-sizing: border-box; margin: 0; padding: 0; }
        :root { --ink: #0f172a; --muted: #475569; --line: #cbd5e1; --paper: #ffffff; --bg: #e2e8f0; --accent: #2563eb; }
        body { font-family: 'Inter', sans-serif; font-size: 11px; color: var(--ink); background: var(--bg); padding: 18px; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
        .container { width: 100%; max-width: 820px; margin: 0 auto; background: var(--paper); padding: 26px 28px; box-shadow: 0 10px 28px rgba(15, 23, 42, 0.10); border-radius: 14px; position: relative; overflow: hidden; }
        .watermark-bg { position: absolute; inset: 0; <?php if (!empty($company['logo_path'])): ?> background-image: url('<?= base_url('uploads/logo/' . esc($company['logo_path'])) ?>'); <?php endif; ?> background-repeat: repeat; background-size: 170px; background-position: center; opacity: 0.025; transform: rotate(-22deg) scale(1.15); z-index: 1; pointer-events: none; }
        .content-wrapper { position: relative; z-index: 5; }
        .action-bar { text-align: center; margin-bottom: 20px; }
        .btn-print { background: linear-gradient(135deg, #2563eb, #1d4ed8); color: #fff; border: none; padding: 11px 22px; font-size: 13px; font-weight: 800; border-radius: 10px; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 8px 20px rgba(37, 99, 235, 0.22); }
        .top-strip { display: flex; justify-content: space-between; align-items: center; background: linear-gradient(90deg, #1e3a8a, #2563eb); color: #fff; padding: 8px 14px; border-radius: 10px; margin-bottom: 14px; font-size: 10px; font-weight: 700; letter-spacing: 0.2px; }
        .header { border-bottom: 2px solid #0f172a; padding-bottom: 14px; margin-bottom: 16px; display: flex; justify-content: space-between; align-items: flex-start; gap: 18px; }
        .company-info { display: flex; align-items: flex-start; gap: 12px; flex: 1; }
        .company-logo-wrap { width: 56px; height: 56px; border: 1px solid var(--line); border-radius: 10px; display: flex; align-items: center; justify-content: center; background: #fff; flex-shrink: 0; overflow: hidden; }
        .company-logo { width: 46px; height: 46px; object-fit: contain; }
        .company-text h1 { font-size: 20px; font-weight: 900; margin-bottom: 4px; text-transform: uppercase; letter-spacing: -0.4px; line-height: 1.1; }
        .company-text .sub { font-size: 10px; color: var(--muted); line-height: 1.5; font-weight: 600; }
        .doc-title { text-align: right; min-width: 200px; display: flex; flex-direction: column; align-items: flex-end; }
        .doc-title .doc-label { font-size: 10px; text-transform: uppercase; font-weight: 800; color: #64748b; margin-bottom: 5px; }
        .doc-title h2 { font-size: 20px; text-transform: uppercase; font-weight: 900; margin-bottom: 4px; }
        .barcode-img { height: 24px; margin-bottom: 4px; }
        .doc-title p { font-family: 'Space Mono', monospace; font-size: 12px; font-weight: 700; border: 1.5px solid #0f172a; padding: 4px 8px; background: #f1f5f9; border-radius: 8px; margin-top: 2px; }
        .meta-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 14px; }
        .meta-box { border: 1px solid var(--line); padding: 11px 13px; border-radius: 10px; background: rgba(255, 255, 255, 0.9); }
        .meta-box.alt { background: rgba(239, 246, 255, 0.6); }
        .meta-box h4 { font-size: 10px; text-transform: uppercase; font-weight: 900; border-bottom: 1px dashed #94a3b8; padding-bottom: 6px; margin-bottom: 8px; display: flex; align-items: center; gap: 7px; }
        .meta-table { width: 100%; border-collapse: collapse; }
        .meta-table td { padding: 3px 0; font-size: 10.5px; vertical-align: top; }
        .meta-label { width: 92px; font-weight: 800; color: var(--muted); }
        .meta-val { font-weight: 700; color: var(--ink); }
        .meta-val.bold { font-weight: 900; font-size: 12px; text-transform: uppercase; }
        
        .section-header { font-size: 12px; font-weight: 900; text-transform: uppercase; margin: 20px 0 10px 0; color: var(--accent); padding-bottom: 6px; border-bottom: 2px solid var(--accent); display: flex; justify-content: space-between; align-items: center; }
        .section-badge { background: #eff6ff; color: var(--accent); padding: 2px 8px; border-radius: 6px; font-size: 9px; font-weight: 800; border: 1px solid #bfdbfe;}

        .table-wrap { border: 1.5px solid #0f172a; border-radius: 12px; overflow: hidden; margin-bottom: 16px; background: rgba(255, 255, 255, 0.95); }
        .item-table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        .item-table thead th { background: #0f172a; color: #fff; text-align: center; font-weight: 800; text-transform: uppercase; font-size: 9.5px; padding: 9px 6px; border-right: 1px solid rgba(255,255,255,0.12); }
        .item-table tbody td { border-top: 1px solid #cbd5e1; padding: 7px 6px; font-size: 10.5px; font-weight: 600; vertical-align: middle; border-right: 1px solid #cbd5e1;}
        .item-table tbody tr:nth-child(even) { background: #f8fafc; }
        .text-center { text-align: center !important; }
        .sku-badge { font-family: 'Space Mono', monospace; font-size: 9.5px; font-weight: 700; background: #f1f5f9; border: 1px solid #cbd5e1; border-radius: 999px; padding: 3px 7px; }
        .desc-text { font-size: 11px; font-weight: 800; text-transform: uppercase;}
        .qty-val { font-family: 'Space Mono', monospace; font-size: 12px; font-weight: 900; }
        
        .box-paraf { width: 100%; height: 30px; border: 1px dashed #94a3b8; border-radius: 4px; margin-top: 4px; }
        
        .notes-section { font-size: 9.8px; line-height: 1.55; margin-bottom: 16px; border: 1px dashed #94a3b8; padding: 10px 12px; border-radius: 10px; background: rgba(248, 250, 252, 0.92); }
        .notes-section strong { font-weight: 900; text-transform: uppercase; display: inline-block; margin-bottom: 5px; }
        .notes-section ol { padding-left: 16px; margin-top: 4px; }
        .signature-section { display: flex; justify-content: space-between; gap: 12px; margin-top: 16px; text-align: center; }
        .sig-box { width: 32%; border: 1px solid var(--line); border-radius: 10px; padding: 10px 8px 8px; background: #fff; }
        .sig-box p { font-size: 10px; font-weight: 800; margin: 0; color: var(--ink); }
        .sig-space { height: 45px; }
        .sig-line { border-top: 1px solid #0f172a; margin: 0 auto 5px; width: 90%; }
        .sig-name { font-size: 9px; font-weight: 700; color: #475569; }
        .footer-note { margin-top: 12px; display: flex; justify-content: space-between; font-size: 9px; color: #64748b; font-weight: 700; }
        
        @page { size: A4 portrait; margin: 7mm; }
        @media print { body { padding: 0; background: #fff; font-size: 10px; } .container { box-shadow: none; border: none; padding: 0; max-width: 100%; } .no-print { display: none !important; } .watermark-bg { position: fixed; inset: 0; width: 100vw; height: 100vh; z-index: 0; } table { page-break-inside: auto; } tr, td, th { page-break-inside: avoid !important; } .table-wrap { border-radius: 0; } }
    </style>
</head>
<body>

    <?php
        // Target Kuantitas SPK (Berapa Pcs Knalpot yang mau dibuat)
        $plannedQty = (int)($spk['planned_qty'] ?? 1);

        // Mengelompokkan item dan operasi berdasarkan section_name
        $groupedData = [];

        if (!empty($items)) {
            foreach ($items as $item) {
                $secName = !empty($item['section_name']) ? strtoupper($item['section_name']) : 'BAGIAN UTAMA';
                if (!isset($groupedData[$secName])) {
                    $groupedData[$secName] = ['items' => [], 'operations' => []];
                }
                
                // -------------------------------------------------------------
                // RUMUS CERDAS PERHITUNGAN BAHAN "DISIAPKAN" (GUDANG / CUTTING)
                // -------------------------------------------------------------
                $sizePerItem = floatval($item['size_per_item'] ?? 1);
                $sizeUom     = esc($item['size_uom'] ?? 'PCS');
                $qtyPerItem  = floatval($item['qty_per_item'] ?? 1);
                $qtyUom      = esc($item['qty_uom'] ?? 'PCS');
                
                $kebutuhanGudang = floatval($item['qty_required']) * $plannedQty;
                $unitAkhir       = esc($item['unit'] ?? 'PCS');

                // Total Pcs Aktual yang dipegang tukang potong
                $totalPcs = floatval($qtyPerItem * $plannedQty);
                // Total Panjang/Volume material yang diolah tukang
                $totalUkuran = floatval($sizePerItem * $qtyPerItem * $plannedQty);

                // --- LOGIKA KONVERSI BATANG ---
                $estimasiBatangHtml = "";
                
                if (strtoupper($sizeUom) == 'CM' && (strpos(strtoupper($item['name']), 'PIPA') !== false || strtoupper($unitAkhir) == 'BATANG' || strtoupper($unitAkhir) == 'BTG')) {
                    // Konversi CM ke BATANG (Asumsi 1 Batang = 600 CM)
                    $btg = round($totalUkuran / 600, 2);
                    $estimasiBatangHtml = "<br><span style='color: #ef4444; font-size: 10.5px;'>(&approx; Ambil {$btg} BATANG)</span>";
                    
                    // Force kebutuhan gudang jadi batang jika UoM akhirnya batang
                    if (strtoupper($unitAkhir) == 'BATANG' || strtoupper($unitAkhir) == 'BTG') {
                        $kebutuhanGudang = $btg;
                    }
                } elseif (strtoupper($sizeUom) == 'MM' && (strpos(strtoupper($item['name']), 'PIPA') !== false || strtoupper($unitAkhir) == 'BATANG' || strtoupper($unitAkhir) == 'BTG')) {
                    // Konversi MM ke BATANG (Asumsi 1 Batang = 6000 MM)
                    $btg = round($totalUkuran / 6000, 2);
                    $estimasiBatangHtml = "<br><span style='color: #ef4444; font-size: 10.5px;'>(&approx; Ambil {$btg} BATANG)</span>";
                    
                    // Force kebutuhan gudang jadi batang jika UoM akhirnya batang
                    if (strtoupper($unitAkhir) == 'BATANG' || strtoupper($unitAkhir) == 'BTG') {
                        $kebutuhanGudang = $btg;
                    }
                } else {
                    // Normal
                    $estimasiBatangHtml = "<br><span style='color: #ef4444; font-size: 10.5px;'>(&approx; Ambil {$kebutuhanGudang} {$unitAkhir})</span>";
                }
                // ------------------------------

                // PENGECEKAN: Apakah ini barang potong/ukur? (contoh: CM, MM)
                if ($sizePerItem != 1 || (strtoupper($sizeUom) !== 'PCS' && strtoupper($sizeUom) !== strtoupper($qtyUom))) {
                    
                    $item['spec_desc'] = "Dibutuhkan: {$qtyPerItem} {$qtyUom} (@ {$sizePerItem} {$sizeUom}) / Produk";
                    
                    // HTML Box untuk Tukang Potong (Menampilkan Estimasi Satuan Gudang)
                    $item['disiapkan_html'] = "
                        <div style='text-align: center;'>
                            <div style='font-family: \"Space Mono\", monospace; font-size: 16px; font-weight: 900; color: var(--accent);'>{$totalPcs} {$qtyUom}</div>
                            <div style='font-size: 9.5px; font-weight: 800; color: var(--muted); margin-top: 3px; text-transform: uppercase;'>Potongan @ {$sizePerItem} {$sizeUom}</div>
                            <div style='background: #fff1f2; border: 1px dashed #fca5a5; color: #991b1b; padding: 6px; border-radius: 6px; font-size: 10px; font-weight: 900; margin-top: 6px; display: inline-block; line-height: 1.4;'>
                                TOTAL: {$totalUkuran} {$sizeUom}
                                {$estimasiBatangHtml}
                            </div>
                        </div>
                    ";
                } else {
                    // Jika barang utuh (misal Monel / Per / Baut) -> Tidak perlu keterangan potong
                    $item['spec_desc'] = "";
                    $item['disiapkan_html'] = "
                        <div style='text-align: center;'>
                            <div style='font-family: \"Space Mono\", monospace; font-size: 16px; font-weight: 900; color: var(--accent);'>{$kebutuhanGudang} {$unitAkhir}</div>
                        </div>
                    ";
                }
                
                // Nilai yang dicentang dan dikeluarkan dari form ceklis gudang
                $item['ceklis_gudang'] = "{$kebutuhanGudang} {$unitAkhir}";
                // -------------------------------------------------------------

                $groupedData[$secName]['items'][] = $item;
            }
        }

        if (!empty($operations)) {
            foreach ($operations as $op) {
                $secName = 'BAGIAN UTAMA';
                $opName = $op['operation_name'];
                
                if (preg_match('/^\[(.*?)\]\s*(.*)$/', $opName, $matches)) {
                    $secName = strtoupper(trim($matches[1]));
                    $opName = trim($matches[2]);
                    $op['operation_name'] = $opName;
                }
                
                if (!isset($groupedData[$secName])) {
                    $groupedData[$secName] = ['items' => [], 'operations' => []];
                }
                $groupedData[$secName]['operations'][] = $op;
            }
        }
    ?>

    <div class="action-bar no-print">
        <button onclick="window.print()" class="btn-print"><i class="ph-bold ph-printer"></i> Cetak SPK (A4)</button>
    </div>

    <div class="container">
        <div class="watermark-bg"></div>
        <div class="content-wrapper">

            <div class="top-strip">
                <div class="left"><i class="ph-bold ph-factory"></i> <span>Dokumen Manufaktur & Produksi</span></div>
                <div class="right"><i class="ph-bold ph-calendar-blank"></i> <span><?= date('d F Y') ?></span></div>
            </div>

            <div class="header">
                <div class="company-info">
                    <div class="company-logo-wrap">
                        <?php if (!empty($company['logo_path'])): ?>
                            <img src="<?= base_url('uploads/logo/' . esc($company['logo_path'])) ?>" class="company-logo">
                        <?php else: ?>
                            <i class="ph-fill ph-factory" style="font-size: 32px; color: #0f172a;"></i>
                        <?php endif; ?>
                    </div>
                    <div class="company-text">
                        <h1><?= esc($company['company_name'] ?? 'PT. NORIC EXHAUST') ?></h1>
                        <div class="sub"><b>DIVISI PRODUKSI PUSAT</b><br>Sistem Enterprise Resource Planning (ERP)</div>
                    </div>
                </div>

                <div class="doc-title">
                    <div class="doc-label">Work Order Document</div>
                    <h2>Surat Perintah Kerja</h2>
                    <img src="https://bwipjs-api.metafloor.com/?bcid=code128&text=<?= esc($spk['spk_number']) ?>&scale=2&height=8&includetext=false" alt="Barcode" class="barcode-img">
                    <p><?= esc($spk['spk_number']) ?></p>
                </div>
            </div>

            <div class="meta-grid">
                <div class="meta-box alt">
                    <h4><i class="ph-bold ph-info"></i> Rincian Perintah</h4>
                    <table class="meta-table">
                        <tr><td class="meta-label">Tgl. Rilis</td><td class="meta-val">: <?= date('d M Y', strtotime($spk['start_date'])) ?></td></tr>
                        <tr><td class="meta-label">ID Resep (BOM)</td><td class="meta-val">: <?= esc($bom['recipe_name']) ?></td></tr>
                        <tr><td class="meta-label">Catatan Order</td><td class="meta-val">: <?= esc($spk['production_notes'] ?? '-') ?></td></tr>
                    </table>
                </div>

                <div class="meta-box">
                    <h4><i class="ph-bold ph-target"></i> Target Produksi</h4>
                    <table class="meta-table">
                        <tr><td class="meta-label">SKU Produk</td><td class="meta-val" style="font-family: monospace;">: <?= esc($targetProduct['sku']) ?></td></tr>
                        <tr><td class="meta-label">Nama Produk</td><td class="meta-val bold">: <?= esc($targetProduct['item_name']) ?></td></tr>
                        <tr><td class="meta-label">Target Qty</td><td class="meta-val bold" style="color: var(--accent);">: <?= $plannedQty ?> Pcs</td></tr>
                    </table>
                </div>
            </div>

            <?php if(empty($groupedData)): ?>
                <div style="padding: 40px; text-align: center; border: 2px dashed #cbd5e1; border-radius: 12px; color: #64748b; font-weight: 800; margin-top: 20px;">
                    Data resep dan tahapan kosong atau belum diatur.
                </div>
            <?php else: ?>

                <?php foreach($groupedData as $sectionName => $group): ?>
                    <div class="section-header">
                        <span><?= esc($sectionName) ?></span>
                        <span class="section-badge">SUB-ASSEMBLY</span>
                    </div>

                    <div class="table-wrap">
                        <table class="item-table">
                            <thead>
                                <tr>
                                    <th width="5%">No</th>
                                    <th width="15%">SKU Barang</th>
                                    <th width="40%">Deskripsi Material</th>
                                    <th width="22%">Instruksi Potong (Operator)</th>
                                    <th width="18%">Pengeluaran Gudang</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(empty($group['items'])): ?>
                                    <tr><td colspan="5" class="text-center" style="font-style: italic; color: var(--muted);">Tidak ada kebutuhan fisik/material.</td></tr>
                                <?php else: ?>
                                    <?php $no = 1; foreach($group['items'] as $item): ?>
                                    <tr>
                                        <td class="text-center"><?= $no++ ?></td>
                                        <td class="text-center"><span class="sku-badge"><?= esc($item['rm_sku']) ?></span></td>
                                        <td>
                                            <span class="desc-text"><?= esc($item['name']) ?></span>
                                            <?php if(!empty($item['spec_desc'])): ?>
                                                <br><span style="color: #64748b; font-weight: 600; font-size: 9px;"><?= $item['spec_desc'] ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?= $item['disiapkan_html'] ?>
                                        </td>
                                        <td class="text-center">
                                            <div style="display:flex; align-items:center; gap:6px; justify-content:center;">
                                                <div style="width:14px; height:14px; border:2px solid #0f172a; border-radius:3px;"></div>
                                                <span style="font-family: 'Space Mono', monospace; font-size: 13px; font-weight: 900; color: #0f172a;"><?= $item['ceklis_gudang'] ?></span>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="table-wrap">
                        <table class="item-table">
                            <thead>
                                <tr>
                                    <th width="6%">No</th>
                                    <th width="34%">Instruksi Operasi Kerja</th>
                                    <th width="10%">Target</th>
                                    <th width="25%">Pekerja & Paraf</th>
                                    <th width="25%">Tgl Selesai & QC Mandor</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(empty($group['operations'])): ?>
                                    <tr><td colspan="5" class="text-center" style="font-style: italic; color: var(--muted);">Tahapan routing belum tersedia.</td></tr>
                                <?php else: ?>
                                    <?php foreach($group['operations'] as $op): ?>
                                    <tr>
                                        <td class="text-center"><?= $op['step_order'] ?></td>
                                        <td>
                                            <span class="desc-text"><?= esc($op['operation_name']) ?></span>
                                            <?php if($op['is_final_step'] == 1): ?><br><span style="font-size: 8px; color: #166534; font-weight: 800;">(Tahap Final - Masuk Gudang)</span><?php endif; ?>
                                        </td>
                                        <td class="text-center qty-val"><?= $plannedQty ?></td>
                                        <td class="text-center"><div class="box-paraf"></div></td>
                                        <td class="text-center"><div class="box-paraf"></div></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endforeach; ?>
                
            <?php endif; ?>

            <div class="notes-section">
                <strong>Instruksi Kerja & K3</strong>
                <ol>
                    <li>Gudang wajib memverifikasi material sebelum diserahkan ke bengkel produksi.</li>
                    <li>Produksi dikerjakan urut sesuai tabel Routing. Jangan melompati proses!</li>
                    <li>Setiap selesai 1 tahap, <b>Tukang Wajib Tanda Tangan</b> dan melapor ke Mandor untuk di QC.</li>
                    <li>Jaga kebersihan area kerja. Kesalahan ukuran akan menjadi tanggung jawab pelaksana (Scrap).</li>
                </ol>
            </div>

            <div class="signature-section">
                <div class="sig-box"><p>Disetujui Oleh (PPIC)</p><div class="sig-space"></div><div class="sig-line"></div><div class="sig-name">Admin Produksi</div></div>
                <div class="sig-box"><p>Diserahkan Oleh</p><div class="sig-space"></div><div class="sig-line"></div><div class="sig-name">Kepala Gudang</div></div>
                <div class="sig-box"><p>Diterima & Diperiksa</p><div class="sig-space"></div><div class="sig-line"></div><div class="sig-name">Mandor Bengkel</div></div>
            </div>

            <div class="footer-note">
                <span>Form No: MFG-SOP-002 • Rev: 05</span>
                <span>Generated by Noric ERP - <?= date('d/m/Y H:i') ?></span>
            </div>

        </div>
    </div>

</body>
</html>