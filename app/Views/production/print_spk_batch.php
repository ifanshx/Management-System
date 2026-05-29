<?php
// =========================================================================
// KONSOLIDASI MATERIAL (GABUNG QTY BERDASARKAN SKU UNTUK GUDANG)
// =========================================================================
$consolidatedMaterials = [];

if (!empty($materials)) {
    foreach ($materials as $m) {
        $sku = $m['sku'];
        $name = strtoupper($m['name']);
        $size = (float)($m['size_per_item'] ?? 1);
        $sizeUom = strtoupper($m['size_uom'] ?? 'PCS');
        $qtyUom = strtoupper($m['qty_uom'] ?? 'PCS');
        $unitAkhir = strtoupper($m['unit_akhir'] ?? 'PCS');
        $totalPcs = (float)($m['total_pcs'] ?? 0);
        $kebutuhanGudang = (float)($m['total_kebutuhan_gudang'] ?? 0);
        
        // Deteksi apakah ini material potongan
        $isCutting = ($size != 1 || ($sizeUom !== 'PCS' && $sizeUom !== $qtyUom));
        $totalUkuran = $isCutting ? ($size * $totalPcs) : $kebutuhanGudang;
        
        $rawTotal = $totalUkuran;
        $rawUom = $isCutting ? $sizeUom : $unitAkhir;

        $finalQty = $kebutuhanGudang;
        $finalUom = $unitAkhir;

        // DETEKSI PIPA & PAKSA KONVERSI KE BATANG (1 Btg = 6 Meter / 600 CM / 6000 MM)
        $isPipa = (strpos($name, 'PIPA') !== false || $unitAkhir == 'BATANG' || $unitAkhir == 'BTG');
        if ($isPipa) {
            if ($rawUom == 'CM') {
                $finalQty = $rawTotal / 600;
                $finalUom = 'BATANG';
            } elseif ($rawUom == 'MM') {
                $finalQty = $rawTotal / 6000;
                $finalUom = 'BATANG';
            }
        }

        // GABUNGKAN DATA JIKA SKU SUDAH ADA DI ARRAY KONSOLIDASI
        if (!isset($consolidatedMaterials[$sku])) {
            $consolidatedMaterials[$sku] = [
                'sku'       => $sku,
                'name'      => $name,
                'qty'       => 0,
                'uom'       => $finalUom,
                'raw_total' => 0,
                'raw_uom'   => $rawUom,
                'is_pipa'   => $isPipa
            ];
        }
        
        $consolidatedMaterials[$sku]['qty'] += $finalQty;
        $consolidatedMaterials[$sku]['raw_total'] += $rawTotal;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Work Ticket SO - <?= esc($so['so_number'] ?? 'BATCH') ?> | <?= esc($company['app_name'] ?? 'Noric ERP') ?></title>
    <?php if (isset($company['logo_path']) && !empty($company['logo_path'])): ?>
        <link rel="icon" type="image/png" href="<?= base_url('uploads/logo/' . esc($company['logo_path'])) ?>">
    <?php endif; ?>
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Space+Mono:wght@400;700;900&display=swap');
        * { box-sizing: border-box; margin: 0; padding: 0; }
        :root { --ink: #0f172a; --muted: #475569; --line: #cbd5e1; --paper: #ffffff; --bg: #f1f5f9; --accent: #8b5cf6; --warning: #f59e0b; --danger: #ef4444;}
        body { font-family: 'Inter', sans-serif; font-size: 11px; color: var(--ink); background: var(--bg); padding: 18px; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
        
        .container { width: 100%; max-width: 820px; margin: 0 auto; background: var(--paper); padding: 26px 28px; box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08); border-radius: 16px; position: relative; overflow: hidden; }
        
        .action-bar { text-align: center; margin-bottom: 20px; }
        .btn-print { background: linear-gradient(135deg, #8b5cf6, #6d28d9); color: #fff; border: none; padding: 12px 24px; font-size: 13px; font-weight: 800; border-radius: 12px; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 8px 20px rgba(139, 92, 246, 0.25); transition: 0.2s;}
        .btn-print:hover { transform: translateY(-2px); box-shadow: 0 12px 25px rgba(139, 92, 246, 0.35); }
        
        .top-strip { display: flex; justify-content: space-between; align-items: center; background: linear-gradient(90deg, #0f172a, #334155); color: #fff; padding: 8px 16px; border-radius: 10px; margin-bottom: 16px; font-size: 10px; font-weight: 800; letter-spacing: 0.5px; }
        
        .header { border-bottom: 2px dashed var(--line); padding-bottom: 16px; margin-bottom: 16px; display: flex; justify-content: space-between; align-items: center; gap: 18px; }
        .company-info { display: flex; align-items: center; gap: 14px; flex: 1; }
        .company-logo-wrap { width: 50px; height: 50px; border: 1px solid var(--line); border-radius: 12px; display: flex; align-items: center; justify-content: center; background: #fff; flex-shrink: 0; overflow: hidden; }
        .company-logo { width: 40px; height: 40px; object-fit: contain; }
        .company-text h1 { font-size: 20px; font-weight: 900; margin-bottom: 2px; text-transform: uppercase; letter-spacing: -0.5px; line-height: 1.1; }
        .company-text .sub { font-size: 10px; color: var(--muted); font-weight: 700; }
        
        .doc-title { text-align: right; }
        .doc-title .doc-label { font-size: 10px; text-transform: uppercase; font-weight: 900; color: var(--accent); margin-bottom: 4px; letter-spacing: 1px; }
        .doc-title h2 { font-size: 22px; font-weight: 900; margin-bottom: 4px; color: var(--ink); letter-spacing: -0.5px; }
        .doc-title p { font-family: 'Space Mono', monospace; font-size: 13px; font-weight: 800; background: #f8fafc; border: 1px solid var(--line); padding: 4px 10px; border-radius: 8px; display: inline-block;}

        .info-box { display: flex; justify-content: space-between; align-items: center; background: rgba(139, 92, 246, 0.05); border: 1px solid rgba(139, 92, 246, 0.2); padding: 14px 18px; border-radius: 12px; margin-bottom: 24px;}
        .info-box div h4 { font-size: 10px; text-transform: uppercase; font-weight: 900; color: var(--accent); margin-bottom: 4px;}
        .info-box div p { font-size: 16px; font-weight: 900; color: var(--ink); margin: 0; letter-spacing: -0.3px;}
        .info-box i { font-size: 36px; color: var(--accent); opacity: 0.3;}

        .section-header { font-size: 13px; font-weight: 900; text-transform: uppercase; margin: 24px 0 12px 0; color: var(--ink); display: flex; align-items: center; gap: 8px; }
        .section-header i { font-size: 18px; color: var(--accent); }
        .section-divider { height: 3px; background: var(--ink); border-radius: 3px; margin-bottom: 12px; width: 40px; }

        .table-wrap { border: 1px solid var(--line); border-radius: 12px; overflow: hidden; margin-bottom: 20px; background: #fff; box-shadow: 0 2px 4px rgba(0,0,0,0.02);}
        .item-table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        .item-table thead th { background: #f8fafc; color: var(--muted); text-align: left; font-weight: 900; text-transform: uppercase; font-size: 9px; padding: 10px 14px; border-bottom: 1px solid var(--line); letter-spacing: 0.5px;}
        .item-table tbody td { border-bottom: 1px solid #f1f5f9; padding: 12px 14px; font-size: 11px; font-weight: 600; vertical-align: middle; }
        .item-table tbody tr:last-child td { border-bottom: none; }
        .item-table tbody tr:nth-child(even) { background: #fafafa; }
        
        .text-center { text-align: center !important; }
        
        .sku-badge { font-family: 'Space Mono', monospace; font-size: 9.5px; font-weight: 800; background: #e2e8f0; color: #475569; padding: 3px 8px; border-radius: 6px; display: inline-flex; align-items: center; gap: 4px; }
        
        /* WAREHOUSE STYLE (KOTAK GUDANG) */
        .wh-box { display: inline-flex; flex-direction: column; align-items: center; background: #f8fafc; border: 1px solid #cbd5e1; padding: 8px 16px; border-radius: 10px; min-width: 90px; box-shadow: inset 0 2px 4px rgba(0,0,0,0.02);}
        .wh-qty { font-family: 'Space Mono', monospace; font-size: 22px; font-weight: 900; color: var(--ink); line-height: 1;}
        .wh-uom { font-size: 10px; font-weight: 900; color: var(--muted); margin-top: 4px; letter-spacing: 0.5px; text-transform: uppercase;}
        .checkbox-cell { width: 24px; height: 24px; border: 2px solid #94a3b8; border-radius: 6px; margin: 0 auto; background: #fff;}

        /* CUTTING STYLE */
        .cut-instruction { display: inline-flex; align-items: center; background: #fffbeb; border: 1px dashed #fcd34d; padding: 6px 10px; border-radius: 8px; gap: 10px; }
        .cut-size { font-family: 'Space Mono', monospace; font-size: 14px; font-weight: 900; color: #b45309; }
        .cut-times { font-size: 12px; color: #d97706; font-weight: 900; }
        .cut-qty { font-size: 12px; font-weight: 900; color: #92400e; background: #fde68a; padding: 2px 8px; border-radius: 6px; }
        
        .utuh-instruction { display: inline-flex; align-items: center; background: #ecfdf5; border: 1px dashed #6ee7b7; padding: 6px 10px; border-radius: 8px; gap: 8px; color: #065f46; font-weight: 800; font-size: 11px;}

        .signature-section { display: flex; justify-content: space-between; gap: 16px; margin-top: 30px; text-align: center; page-break-inside: avoid;}
        .sig-box { flex: 1; border: 1px solid var(--line); border-radius: 12px; padding: 12px; background: #fff; }
        .sig-box p { font-size: 10px; font-weight: 800; margin: 0; color: var(--muted); text-transform: uppercase; letter-spacing: 0.5px;}
        .sig-space { height: 50px; }
        .sig-line { border-top: 1.5px solid var(--ink); margin: 0 auto 5px; width: 80%; }
        .sig-name { font-size: 11px; font-weight: 800; color: var(--ink); }
        
        @page { size: A4 portrait; margin: 7mm; }
        @media print { body { padding: 0; background: #fff; } .container { box-shadow: none; border: none; padding: 0; } .no-print { display: none !important; } table { page-break-inside: auto; } tr, td, th { page-break-inside: avoid !important; } }
    </style>
</head>
<body>

    <div class="action-bar no-print">
        <button onclick="window.print()" class="btn-print"><i class="ph-bold ph-printer"></i> Cetak Work Ticket BATCH (A4)</button>
    </div>

    <div class="container">
        <div class="top-strip">
            <div class="left"><i class="ph-bold ph-kanban"></i> <span>WORK TICKET / REKAP PRODUKSI</span></div>
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
                    <div class="sub">DIVISI PRODUKSI & LOGISTIK</div>
                </div>
            </div>

            <div class="doc-title">
                <div class="doc-label">Kode Produksi Batch</div>
                <h2>Rekap Kebutuhan</h2>
                <p><?= esc($so['so_number'] ?? 'BATCH-MANUAL') ?></p>
            </div>
        </div>

        <div class="info-box">
            <div>
                <h4>Tujuan / Pelanggan</h4>
                <p><?= esc($so['company_name'] ?? 'Gudang Internal (Stok)') ?></p>
            </div>
            <i class="ph-fill ph-truck"></i>
        </div>

        <div class="section-header">
            <i class="ph-fill ph-flag-checkered"></i> 1. Target Output Barang Jadi
        </div>
        <div class="section-divider"></div>
        
        <div class="table-wrap">
            <table class="item-table">
                <thead>
                    <tr>
                        <th width="8%" class="text-center">No</th>
                        <th width="25%">SKU Produk</th>
                        <th width="47%">Nama Produk / Tipe</th>
                        <th width="20%" class="text-center">Target Qty</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($targetProducts)): ?>
                        <tr><td colspan="4" class="text-center">Tidak ada produk terdeteksi.</td></tr>
                    <?php else: ?>
                        <?php $no = 1; foreach($targetProducts as $prod): ?>
                        <tr>
                            <td class="text-center" style="font-weight: 900; font-size:12px; color: var(--muted);"><?= $no++ ?></td>
                            <td><span class="sku-badge"><i class="ph-bold ph-barcode"></i> <?= esc($prod['sku']) ?></span></td>
                            <td style="font-weight: 800; font-size: 12px; text-transform: uppercase;">
                                <?= esc($prod['name']) ?>
                                <?php if(!empty($prod['notes'])): ?>
                                    <br><span style="background: #fef2f2; color: #ef4444; padding: 3px 8px; border-radius: 6px; font-size: 10.5px; font-weight: 900; border: 1px dashed #f87171; display: inline-block; margin-top: 5px;"><i class="ph-fill ph-warning-circle"></i> CATATAN SPESIAL: <?= esc($prod['notes']) ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center" style="font-family: 'Space Mono'; font-size: 15px; font-weight: 900; color: var(--ink);"><?= $prod['qty'] ?> <span style="font-size:10px; font-family:'Inter'; color:var(--muted);">PCS</span></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="section-header">
            <i class="ph-fill ph-archive-box"></i> 2. Daftar Ambil Gudang (Total Konsolidasi)
        </div>
        <div class="section-divider"></div>

        <div class="table-wrap" style="border-color: var(--accent);">
            <table class="item-table">
                <thead>
                    <tr style="background: rgba(139, 92, 246, 0.05);">
                        <th width="8%" class="text-center" style="color: var(--accent);">No</th>
                        <th width="62%" style="color: var(--accent);">Deskripsi Material & Spesifikasi</th>
                        <th width="20%" class="text-center" style="color: var(--accent);">Total Ambil</th>
                        <th width="10%" class="text-center" style="color: var(--accent);">Cek</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($consolidatedMaterials)): ?>
                        <tr><td colspan="4" class="text-center">Tidak ada material terdeteksi.</td></tr>
                    <?php else: ?>
                        <?php $no = 1; foreach($consolidatedMaterials as $cm): ?>
                        <tr>
                            <td class="text-center" style="font-weight: 900; font-size: 12px; color: var(--muted);"><?= $no++ ?></td>
                            <td>
                                <div style="font-weight: 900; font-size: 13px; margin-bottom: 6px; color: var(--ink); text-transform: uppercase;">
                                    <?= esc($cm['name']) ?>
                                </div>
                                <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                                    <span class="sku-badge"><i class="ph-bold ph-barcode"></i> <?= esc($cm['sku']) ?></span>
                                    
                                    <?php if($cm['is_pipa'] && ($cm['raw_uom'] == 'CM' || $cm['raw_uom'] == 'MM')): ?>
                                        <span style="font-family: 'Space Mono', monospace; font-size: 13px; font-weight: 900; color: #991b1b; background: #fef2f2; border: 1px dashed #fca5a5; padding: 4px 10px; border-radius: 6px;">
                                            Total: <?= number_format($cm['qty'], 2, '.', '') ?> BATANG
                                        </span>
                                        <span style="font-size: 10px; font-weight: 800; color: var(--accent); background: rgba(139, 92, 246, 0.1); padding: 4px 10px; border-radius: 6px;">
                                            (&approx; <?= number_format($cm['raw_total'], 2, ',', '.') ?> <?= $cm['raw_uom'] ?>)
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="text-center">
                                <div class="wh-box">
                                    <span class="wh-qty"><?= ceil($cm['qty']) ?></span> <span class="wh-uom"><?= esc($cm['uom']) ?></span>
                                </div>
                            </td>
                            <td class="text-center">
                                <div class="checkbox-cell"></div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div style="font-size: 10.5px; color: var(--muted); line-height: 1.6; padding: 12px 16px; border: 1px dashed var(--line); border-radius: 10px; background: #fff; margin-bottom: 30px;">
            <strong><i class="ph-fill ph-warning-circle" style="color: var(--warning);"></i> PENTING UNTUK GUDANG:</strong> Angka pada tabel konsolidasi di atas sudah menggunakan metode <b>pembulatan ke atas (Ceil)</b> untuk memudahkan pengambilan fisik. Serahkan material batangan secara utuh ke divisi Cutting. Sisa potongan (scrap) wajib dikembalikan dan dicatat dalam penyesuaian stok.
        </div>

        <div class="section-header">
            <i class="ph-fill ph-scissors"></i> 3. Rincian Instruksi Kerja (Divisi Cutting & Produksi)
        </div>
        <div class="section-divider"></div>

        <div class="table-wrap">
            <table class="item-table">
                <thead>
                    <tr>
                        <th width="8%" class="text-center">No</th>
                        <th width="42%">Material Dasar</th>
                        <th width="50%">Instruksi (Potong / Rakit)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($materials)): ?>
                        <tr><td colspan="3" class="text-center">Tidak ada instruksi kerja.</td></tr>
                    <?php else: ?>
                        <?php $no = 1; foreach($materials as $m): ?>
                        <?php 
                            $size = (float)($m['size_per_item'] ?? 1);
                            $sizeUom = strtoupper($m['size_uom'] ?? 'PCS');
                            $qtyUom = strtoupper($m['qty_uom'] ?? 'PCS');
                            $totalPcs = (float)($m['total_pcs'] ?? 0);
                            $totalUkuran = $size * $totalPcs;
                            
                            // Deteksi apakah ini instruksi pemotongan
                            $isCutting = ($size != 1 || ($sizeUom !== 'PCS' && $sizeUom !== $qtyUom));
                        ?>
                        <tr>
                            <td class="text-center" style="font-weight: 900; font-size: 12px; color: var(--muted);"><?= $no++ ?></td>
                            <td>
                                <div style="font-weight: 800; font-size: 11.5px; margin-bottom: 4px; color: var(--ink); text-transform: uppercase;"><?= esc($m['name']) ?></div>
                                <div style="font-family: 'Space Mono', monospace; font-size: 9px; color: var(--muted);"><?= esc($m['sku']) ?></div>
                            </td>
                            <td>
                                <?php if($isCutting): ?>
                                    <div style='text-align: left;'>
                                        <div style='display: flex; align-items: center; gap: 8px; flex-wrap: wrap;'>
                                            <span style='font-family: "Space Mono", monospace; font-size: 15px; font-weight: 900; color: var(--accent); background: #f1f5f9; padding: 2px 8px; border-radius: 6px; border: 1px solid #cbd5e1;'><?= $totalPcs ?> <?= $qtyUom ?></span>
                                       
                                        <div style='background: #fffbeb; border: 1px dashed #fcd34d; color: #b45309; padding: 5px 10px; border-radius: 6px; font-size: 15px; font-weight: 900; display: inline-block;'>
                                          POTONGAN : <?= $size ?> <?= $sizeUom ?>
                                        </div>
                                    </div>
                                    </div>
                                <?php else: ?>
                                    <div class="utuh-instruction">
                                        <i class="ph-bold ph-check-circle" style="font-size: 16px;"></i>
                                        SIAP RAKIT / UTUH: <?= $totalPcs ?> <?= $qtyUom ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="signature-section">
            <div class="sig-box">
                <p>Kepala Gudang Logistik</p>
                <div class="sig-space"></div>
                <div class="sig-line"></div>
                <div class="sig-name">_________________</div>
            </div>
            <div class="sig-box">
                <p>Mandor Cutting / Produksi</p>
                <div class="sig-space"></div>
                <div class="sig-line"></div>
                <div class="sig-name">_________________</div>
            </div>
        </div>

    </div>
</body>
</html>