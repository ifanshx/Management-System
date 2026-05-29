<?php
$db = \Config\Database::connect();

// 1. IDENTIFIKASI NAMA PEMESAN / CUSTOMER B2B
$buyerName = 'STOK GUDANG (REGULER)';
$isB2B = false;

if (!empty($spk['so_id'])) {
    $soData = $db->table('b2b_sales_orders')
        ->select('b2b_customers.company_name')
        ->join('b2b_customers', 'b2b_customers.id = b2b_sales_orders.customer_id', 'left')
        ->where('b2b_sales_orders.id', $spk['so_id'])
        ->get()->getRowArray();
        
    if ($soData && !empty($soData['company_name'])) {
        $buyerName = strtoupper($soData['company_name']);
        $isB2B = true;
    }
}

// 2. KALKULASI & PENGELOMPOKAN DATA FORMULA RESEP (BOM)
$plannedQty = (int)($spk['planned_qty'] ?? 1);
$groupedData = [];

if (!empty($items)) {
    foreach ($items as $item) {
        $secName = !empty($item['section_name']) ? strtoupper($item['section_name']) : 'BAGIAN UTAMA';
        if (!isset($groupedData[$secName])) {
            $groupedData[$secName] = ['items' => [], 'operations' => []];
        }
        
        // --- RUMUS CERDAS PERHITUNGAN BAHAN "DISIAPKAN" (GUDANG / CUTTING) ---
        $sizePerItem = floatval($item['size_per_item'] ?? 1);
        $sizeUom     = esc($item['size_uom'] ?? 'PCS');
        $qtyPerItem  = floatval($item['qty_per_item'] ?? 1);
        $qtyUom      = esc($item['qty_uom'] ?? 'PCS');
        
        $kebutuhanGudang = floatval($item['qty_required'] ?? 0) * $plannedQty;
        $unitAkhir       = esc($item['unit'] ?? 'PCS');

        // Total Pcs Aktual yang dipegang tukang potong
        $totalPcs = floatval($qtyPerItem * $plannedQty);
        // Total Panjang/Volume material yang diolah tukang
        $totalUkuran = floatval($sizePerItem * $qtyPerItem * $plannedQty);

        // --- LOGIKA KONVERSI BATANG KHUSUS PIPA (BATANG DI ATAS, CM DI BAWAH) ---
        $estimasiBatangHtml = "";
        $mainTotalHtml = "";
        
        if (strtoupper($sizeUom) == 'CM' && (strpos(strtoupper($item['name'] ?? ''), 'PIPA') !== false || strtoupper($unitAkhir) == 'BATANG' || strtoupper($unitAkhir) == 'BTG')) {
            $btg = round($totalUkuran / 600, 2);
            $mainTotalHtml = "<span style='font-family: \"Space Mono\", monospace; font-size: 15px; font-weight: 900; color: #991b1b;'>{$btg} BATANG</span>";
            $estimasiBatangHtml = "<br><span style='color: #ef4444; font-size: 10.5px; font-weight: 800;'>(&approx; Ambil {$totalUkuran} {$sizeUom})</span>";
            
            if (strtoupper($unitAkhir) == 'BATANG' || strtoupper($unitAkhir) == 'BTG') {
                $kebutuhanGudang = $btg;
            }
        } elseif (strtoupper($sizeUom) == 'MM' && (strpos(strtoupper($item['name'] ?? ''), 'PIPA') !== false || strtoupper($unitAkhir) == 'BATANG' || strtoupper($unitAkhir) == 'BTG')) {
            $btg = round($totalUkuran / 6000, 2);
            $mainTotalHtml = "<span style='font-family: \"Space Mono\", monospace; font-size: 15px; font-weight: 900; color: #991b1b;'>{$btg} BATANG</span>";
            $estimasiBatangHtml = "<br><span style='color: #ef4444; font-size: 10.5px; font-weight: 800;'>(&approx; Ambil {$totalUkuran} {$sizeUom})</span>";
            
            if (strtoupper($unitAkhir) == 'BATANG' || strtoupper($unitAkhir) == 'BTG') {
                $kebutuhanGudang = $btg;
            }
        } else {
            $mainTotalHtml = "<span style='font-family: \"Space Mono\", monospace; font-size: 15px; font-weight: 900; color: #0f172a;'>" . ceil($kebutuhanGudang) . " {$unitAkhir}</span>";
            $estimasiBatangHtml = ""; // Tidak perlu estimasi jika bukan pipa
        }

        // PENGECEKAN: Apakah ini barang potong/ukur? (contoh: CM, MM)
        if ($sizePerItem != 1 || (strtoupper($sizeUom) !== 'PCS' && strtoupper($sizeUom) !== strtoupper($qtyUom))) {
            $item['spec_desc'] = "Dibutuhkan: {$qtyPerItem} {$qtyUom} (@ {$sizePerItem} {$sizeUom}) / Produk";
            
            $item['disiapkan_html'] = "
                <div style='text-align: left;'>
                    <div style='display: flex; align-items: center; gap: 6px; flex-wrap: wrap;'>
                        <span style='font-family: \"Space Mono\", monospace; font-size: 14px; font-weight: 900; color: #2563eb; background: #eff6ff; padding: 2px 8px; border-radius: 6px; border: 1px solid #bfdbfe;'>{$totalPcs} {$qtyUom}</span>
                        <div style='background: #fffbeb; border: 1px dashed #fcd34d; color: #b45309; padding: 4px 8px; border-radius: 6px; font-size: 12px; font-weight: 900; display: inline-block;'>
                            POTONG : {$sizePerItem} {$sizeUom}
                        </div>
                    </div>
                </div>
            ";
            
            // Kebutuhan kotor total ukuran (Batang di atas, CM di bawah)
            $item['total_kotor_html'] = "
                <div style='text-align: center;'>
                    {$mainTotalHtml}
                    {$estimasiBatangHtml}
                </div>
            ";
        } else {
            // Jika barang utuh (misal Monel / Per / Baut) -> Tidak perlu keterangan potong
            $item['spec_desc'] = "";
            $item['disiapkan_html'] = "
                <div style='display: inline-flex; align-items: center; background: #ecfdf5; border: 1px dashed #6ee7b7; padding: 6px 10px; border-radius: 8px; color: #065f46; font-weight: 800; font-size: 11px;'>
                    <i class='ph-bold ph-check-circle' style='font-size: 14px; margin-right:5px;'></i> SIAP RAKIT / UTUH
                </div>
            ";
            
            $item['total_kotor_html'] = "
                <div style='text-align: center;'>
                    {$mainTotalHtml}
                </div>
            ";
        }
        
        $item['ceklis_gudang'] = ceil($kebutuhanGudang) . " {$unitAkhir}";
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

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SPK - <?= esc($spk['spk_number'] ?? '') ?> | <?= esc($company['app_name'] ?? 'Noric ERP') ?></title>
    <?php if (isset($company['logo_path']) && !empty($company['logo_path'])): ?>
        <link rel="icon" type="image/png" href="<?= base_url('uploads/logo/' . esc($company['logo_path'])) ?>">
    <?php endif; ?>
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Space+Mono:wght@400;700;900&display=swap');
        * { box-sizing: border-box; margin: 0; padding: 0; }
        :root { --ink: #0f172a; --muted: #475569; --line: #cbd5e1; --paper: #ffffff; --bg: #f1f5f9; --accent: #2563eb; --warning: #f59e0b; --danger: #ef4444;}
        body { font-family: 'Inter', sans-serif; font-size: 11px; color: var(--ink); background: var(--bg); padding: 18px; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
        
        .container { width: 100%; max-width: 820px; margin: 0 auto; background: var(--paper); padding: 26px 28px; box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08); border-radius: 16px; position: relative; overflow: hidden; }
        .watermark-bg { position: absolute; inset: 0; <?php if (!empty($company['logo_path'])): ?> background-image: url('<?= base_url('uploads/logo/' . esc($company['logo_path'])) ?>'); <?php endif; ?> background-repeat: repeat; background-size: 170px; background-position: center; opacity: 0.025; transform: rotate(-22deg) scale(1.15); z-index: 1; pointer-events: none; }
        .content-wrapper { position: relative; z-index: 5; }
        
        .action-bar { text-align: center; margin-bottom: 20px; }
        .btn-print { background: linear-gradient(135deg, #2563eb, #1d4ed8); color: #fff; border: none; padding: 12px 24px; font-size: 13px; font-weight: 800; border-radius: 12px; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 8px 20px rgba(37, 99, 235, 0.25); transition: 0.2s;}
        .btn-print:hover { transform: translateY(-2px); box-shadow: 0 12px 25px rgba(37, 99, 235, 0.35); }
        
        .top-strip { display: flex; justify-content: space-between; align-items: center; background: linear-gradient(90deg, #1e3a8a, #2563eb); color: #fff; padding: 8px 16px; border-radius: 10px; margin-bottom: 16px; font-size: 10px; font-weight: 800; letter-spacing: 0.5px; }
        
        .header { padding-bottom: 16px; margin-bottom: 16px; display: flex; justify-content: space-between; align-items: center; gap: 18px; }
        .company-info { display: flex; align-items: center; gap: 14px; flex: 1; }
        .company-logo-wrap { width: 50px; height: 50px; border: 1px solid var(--line); border-radius: 12px; display: flex; align-items: center; justify-content: center; background: #fff; flex-shrink: 0; overflow: hidden; }
        .company-logo { width: 40px; height: 40px; object-fit: contain; }
        .company-text h1 { font-size: 20px; font-weight: 900; margin-bottom: 2px; text-transform: uppercase; letter-spacing: -0.5px; line-height: 1.1; }
        .company-text .sub { font-size: 10px; color: var(--muted); font-weight: 700; line-height: 1.4;}
        
        .doc-title { text-align: right; }
        .doc-title .doc-label { font-size: 10px; text-transform: uppercase; font-weight: 900; color: #475569; margin-bottom: 4px; letter-spacing: 1px; }
        .doc-title h2 { font-size: 22px; font-weight: 900; margin-bottom: 4px; color: var(--ink); letter-spacing: -0.5px; }
        .barcode-img { height: 26px; margin-bottom: 6px; }
        .doc-title p { font-family: 'Space Mono', monospace; font-size: 13px; font-weight: 800; background: #f8fafc; border: 1px solid var(--line); padding: 4px 10px; border-radius: 8px; display: inline-block;}

        .info-box { display: flex; justify-content: space-between; align-items: center; background: rgba(37, 99, 235, 0.05); border: 1px solid rgba(37, 99, 235, 0.2); padding: 14px 18px; border-radius: 12px; margin-bottom: 15px;}
        .info-box div h4 { font-size: 10px; text-transform: uppercase; font-weight: 900; color: var(--accent); margin-bottom: 4px;}
        .info-box div p { font-size: 16px; font-weight: 900; color: var(--ink); margin: 0; letter-spacing: -0.3px;}
        .info-box i { font-size: 36px; color: var(--accent); opacity: 0.3;}

        .section-header { font-size: 13px; font-weight: 900; text-transform: uppercase; margin: 24px 0 12px 0; color: var(--ink); display: flex; align-items: center; justify-content: space-between; }
        .section-header .left { display: flex; align-items: center; gap: 8px;}
        .section-header i { font-size: 18px; color: var(--accent); }
        .section-divider { height: 3px; background: var(--accent); border-radius: 3px; margin-bottom: 12px; width: 40px; }
        .section-badge { background: #eff6ff; color: var(--accent); padding: 2px 8px; border-radius: 6px; font-size: 9px; font-weight: 800; border: 1px solid #bfdbfe;}

        .table-wrap { border: 1px solid var(--line); border-radius: 12px; overflow: hidden; margin-bottom: 20px; background: #fff; box-shadow: 0 2px 4px rgba(0,0,0,0.02);}
        .item-table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        .item-table thead th { background: #f8fafc; color: var(--muted); text-align: left; font-weight: 900; text-transform: uppercase; font-size: 9px; padding: 10px 14px; border-bottom: 1px solid var(--line); letter-spacing: 0.5px;}
        .item-table tbody td { border-bottom: 1px solid #f1f5f9; padding: 12px 14px; font-size: 11px; font-weight: 600; vertical-align: middle; }
        .item-table tbody tr:last-child td { border-bottom: none; }
        .item-table tbody tr:nth-child(even) { background: #fafafa; }
        
        .text-center { text-align: center !important; }
        .sku-badge { font-family: 'Space Mono', monospace; font-size: 9.5px; font-weight: 800; background: #e2e8f0; color: #475569; padding: 3px 8px; border-radius: 6px; display: inline-flex; align-items: center; gap: 4px; }
        .desc-text { font-size: 11px; font-weight: 800; text-transform: uppercase; color: var(--ink);}
        
        .box-paraf { width: 100%; height: 30px; border: 1px dashed #94a3b8; border-radius: 6px; margin-top: 4px; }
        .checkbox-cell { width: 20px; height: 20px; border: 2px solid #94a3b8; border-radius: 4px; margin: 0 auto; background: #fff;}

        .notes-section { font-size: 10px; line-height: 1.55; margin-bottom: 16px; border: 1px dashed #94a3b8; padding: 12px 16px; border-radius: 12px; background: rgba(248, 250, 252, 0.92); }
        .notes-section strong { font-weight: 900; text-transform: uppercase; display: inline-block; margin-bottom: 5px; color: var(--danger);}
        .notes-section ol { padding-left: 16px; margin-top: 4px; font-weight: 700; color: var(--muted);}

        .signature-section { display: flex; justify-content: space-between; gap: 16px; margin-top: 30px; text-align: center; page-break-inside: avoid;}
        .sig-box { flex: 1; border: 1px solid var(--line); border-radius: 12px; padding: 12px; background: #fff; }
        .sig-box p { font-size: 10px; font-weight: 800; margin: 0; color: var(--muted); text-transform: uppercase; letter-spacing: 0.5px;}
        .sig-space { height: 50px; }
        .sig-line { border-top: 1.5px solid var(--ink); margin: 0 auto 5px; width: 80%; }
        .sig-name { font-size: 11px; font-weight: 800; color: var(--ink); }
        .footer-note { margin-top: 15px; display: flex; justify-content: space-between; font-size: 9px; color: #64748b; font-weight: 700; }
        
        @page { size: A4 portrait; margin: 7mm; }
        @media print { body { padding: 0; background: #fff; font-size: 10px; } .container { box-shadow: none; border: none; padding: 0; max-width: 100%; } .no-print { display: none !important; } .watermark-bg { position: fixed; inset: 0; width: 100vw; height: 100vh; z-index: 0; } table { page-break-inside: auto; } tr, td, th { page-break-inside: avoid !important; } .table-wrap { border-radius: 0; } }
    </style>
</head>
<body>

    <div class="action-bar no-print">
        <button onclick="window.print()" class="btn-print"><i class="ph-bold ph-printer"></i> Cetak SPK Produksi (A4)</button>
    </div>

    <div class="container">
        <div class="watermark-bg"></div>
        <div class="content-wrapper">

            <div class="top-strip">
                <div class="left"><i class="ph-bold ph-kanban"></i> <span>DOKUMEN MANUFAKTUR & PRODUKSI</span></div>
                <div class="right"><i class="ph-bold ph-clock"></i> <span>Tercetak: <?= date('d/m/Y H:i') ?></span></div>
            </div>

            <div class="header">
                <div class="company-info">
                    <div class="company-logo-wrap">
                        <?php if (!empty($company['logo_path'])): ?>
                            <img src="<?= base_url('uploads/logo/' . esc($company['logo_path'])) ?>" class="company-logo">
                        <?php else: ?>
                            <i class="ph-fill ph-factory" style="font-size: 28px; color: #0f172a;"></i>
                        <?php endif; ?>
                    </div>
                    <div class="company-text">
                        <h1><?= esc($company['company_name'] ?? 'PT. NORIC EXHAUST') ?></h1>
                        <div class="sub">DIVISI PRODUKSI PUSAT<br>Sistem Enterprise Resource Planning (ERP)</div>
                    </div>
                </div>

                <div class="doc-title">
                    <div class="doc-label">Work Order Document</div>
                    <h2>Surat Perintah Kerja</h2>
                    <img src="https://bwipjs-api.metafloor.com/?bcid=code128&text=<?= esc($spk['spk_number'] ?? '') ?>&scale=2&height=8&includetext=false" alt="Barcode" class="barcode-img">
                    <p><?= esc($spk['spk_number'] ?? '') ?></p>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 20px;">
                <div class="info-box" style="margin-bottom: 0;">
                    <div>
                        <h4>Tujuan / Pemesan (B2B)</h4>
                        <p><?= esc($buyerName) ?></p>
                    </div>
                    <i class="ph-fill ph-storefront"></i>
                </div>
                
                <div class="info-box" style="margin-bottom: 0; background: rgba(245, 158, 11, 0.05); border-color: rgba(245, 158, 11, 0.2);">
                    <div>
                        <h4 style="color: var(--warning);">Rincian Data SPK</h4>
                        <p style="font-size: 13px;">Tgl. Rilis: <?= date('d M Y', strtotime($spk['start_date'] ?? date('Y-m-d'))) ?></p>
                        <div style="font-size: 10px; color: var(--muted); margin-top: 4px; font-weight: 700;">ID BOM: <?= esc($bom['recipe_name'] ?? '-') ?></div>
                    </div>
                    <i class="ph-fill ph-calendar-check" style="color: var(--warning);"></i>
                </div>
            </div>

            <div class="section-header">
                <div class="left"><i class="ph-fill ph-flag-checkered"></i> 1. Target Produksi Barang Jadi</div>
            </div>
            <div class="section-divider"></div>
            
            <div class="table-wrap">
                <table class="item-table">
                    <thead>
                        <tr>
                            <th width="20%">SKU Produk Target</th>
                            <th width="50%">Nama Produk / Tipe</th>
                            <th width="30%" class="text-center">Kuantitas Dibuat</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><span class="sku-badge"><i class="ph-bold ph-barcode"></i> <?= esc($targetProduct['sku'] ?? '-') ?></span></td>
                            <td style="font-weight: 900; font-size: 13px; text-transform: uppercase;">
                                <?= esc($targetProduct['item_name'] ?? 'UNKNOWN') ?>
                                <?php if(!empty($spk['production_notes'])): ?>
                                    <br><span style="background: #fef2f2; color: #ef4444; padding: 4px 8px; border-radius: 6px; font-size: 11px; font-weight: 900; border: 1px dashed #f87171; display: inline-block; margin-top: 6px;"><i class="ph-fill ph-warning-circle"></i> CATATAN SPESIAL: <?= esc($spk['production_notes']) ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center" style="font-family: 'Space Mono'; font-size: 18px; font-weight: 900; color: var(--accent);"><?= $plannedQty ?> <span style="font-size:10px; font-family:'Inter'; color:var(--muted);">PCS</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="section-header" style="margin-top: 25px;">
                <div class="left"><i class="ph-fill ph-stack"></i> 2. Rincian Kebutuhan Material & Routing Operasi</div>
            </div>
            <div class="section-divider"></div>

            <?php if(empty($groupedData)): ?>
                <div style="padding: 40px; text-align: center; border: 2px dashed #cbd5e1; border-radius: 12px; color: #64748b; font-weight: 800; margin-top: 20px;">
                    Data resep dan tahapan kosong atau belum diatur.
                </div>
            <?php else: ?>
                <?php foreach($groupedData as $sectionName => $group): ?>
                    <div class="section-header" style="margin: 15px 0 8px 0; border: none; padding: 0;">
                        <span style="font-size: 12px; color: var(--ink);"><i class="ph-bold ph-caret-circle-double-right"></i> Bagian: <?= esc($sectionName) ?></span>
                        <span class="section-badge">SUB-ASSEMBLY</span>
                    </div>

                    <div class="table-wrap">
                        <table class="item-table">
                            <thead>
                                <tr>
                                    <th width="5%">No</th>
                                    <th width="15%">SKU Barang</th>
                                    <th width="40%">Deskripsi Material</th>
                                    <th width="22%">Instruksi (Operator Potong)</th>
                                    <th width="18%">Ambil Gudang (Total)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(empty($group['items'])): ?>
                                    <tr><td colspan="5" class="text-center" style="font-style: italic; color: var(--muted);">Tidak ada kebutuhan fisik/material.</td></tr>
                                <?php else: ?>
                                    <?php $no = 1; foreach($group['items'] as $item): ?>
                                    <tr>
                                        <td class="text-center" style="font-weight: 900; color: var(--muted);"><?= $no++ ?></td>
                                        <td><span class="sku-badge"><?= esc($item['rm_sku']) ?></span></td>
                                        <td>
                                            <span class="desc-text"><?= esc($item['name'] ?? 'UNKNOWN') ?></span>
                                            <?php if(!empty($item['spec_desc'])): ?>
                                                <br><span style="color: #64748b; font-weight: 600; font-size: 9px;"><?= $item['spec_desc'] ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= $item['disiapkan_html'] ?></td>
                                        <td class="text-center">
                                            <div style="background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 6px; padding: 4px;">
                                                <?= $item['total_kotor_html'] ?>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="table-wrap" style="margin-bottom: 30px;">
                        <table class="item-table">
                            <thead>
                                <tr style="background: #f1f5f9;">
                                    <th width="5%">Tahap</th>
                                    <th width="45%">Instruksi Operasi Kerja (Routing)</th>
                                    <th width="25%">Nama Pekerja & Paraf</th>
                                    <th width="25%">Tgl Selesai & QC Mandor</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(empty($group['operations'])): ?>
                                    <tr><td colspan="4" class="text-center" style="font-style: italic; color: var(--muted);">Tahapan routing belum tersedia.</td></tr>
                                <?php else: ?>
                                    <?php foreach($group['operations'] as $op): ?>
                                    <tr>
                                        <td class="text-center" style="font-weight: 900;"><?= $op['step_order'] ?></td>
                                        <td>
                                            <span class="desc-text"><?= esc($op['operation_name']) ?></span>
                                            <?php if($op['is_final_step'] == 1): ?><br><span style="font-size: 9px; color: #166534; font-weight: 800; background: #dcfce7; padding: 2px 6px; border-radius: 4px; display: inline-block; margin-top:3px;"><i class="ph-bold ph-check-circle"></i> TAHAP FINAL (MASUK GUDANG)</span><?php endif; ?>
                                        </td>
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
                <strong><i class="ph-fill ph-warning-octagon"></i> Perhatian K3 & Prosedur Kerja</strong>
                <ol>
                    <li>Gudang wajib memverifikasi material sebelum diserahkan ke area / bengkel produksi.</li>
                    <li>Produksi dikerjakan urut sesuai tabel Routing. <b>Dilarang melompati tahapan proses!</b></li>
                    <li>Setiap selesai 1 tahap, Tukang Wajib Mengisi Nama, Tanda Tangan, dan melapor ke Mandor untuk di-QC.</li>
                    <li>Jaga keselamatan dan kebersihan area kerja. Kesalahan ukuran akan dicatat sebagai Scrap (Rugi Bahan).</li>
                </ol>
            </div>

            <div class="signature-section">
                <div class="sig-box">
                    <p>Diterbitkan Oleh (PPIC)</p>
                    <div class="sig-space"></div>
                    <div class="sig-line"></div>
                    <div class="sig-name">Admin Produksi</div>
                </div>
                <div class="sig-box">
                    <p>Diserahkan Oleh</p>
                    <div class="sig-space"></div>
                    <div class="sig-line"></div>
                    <div class="sig-name">Kepala Gudang</div>
                </div>
                <div class="sig-box">
                    <p>Diterima & Diperiksa</p>
                    <div class="sig-space"></div>
                    <div class="sig-line"></div>
                    <div class="sig-name">Mandor Bengkel</div>
                </div>
            </div>

            <div class="footer-note">
                <span>Form No: MFG-SOP-001 • Rev: 06</span>
                <span>Generated by Noric ERP System - <?= date('d/m/Y H:i') ?></span>
            </div>

        </div>
    </div>

</body>
</html>