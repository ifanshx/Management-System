<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Massal Resep (BOM) | <?= esc($company['app_name'] ?? 'Noric ERP') ?></title>
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;900&family=Space+Mono:wght@400;700&display=swap');

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Inter', sans-serif;
            background: #e2e8f0;
            padding: 20px;
            font-size: 10px;
            color: #0f172a;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        .action-bar {
            text-align: center;
            margin-bottom: 20px;
        }

        .btn-print {
            background: #2563eb;
            color: #fff;
            border: none;
            padding: 10px 20px;
            font-size: 13px;
            font-weight: 800;
            border-radius: 8px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 4px 10px rgba(37,99,235,0.2);
        }

        .print-guide {
            background: #fff;
            padding: 8px 14px;
            border-radius: 8px;
            font-size: 11px;
            margin-top: 10px;
            display: inline-block;
            border: 1px dashed #94a3b8;
            line-height: 1.5;
        }

        /* Simulasi A4 Landscape di Web */
        .a4-page {
            width: 297mm;
            height: 200mm;
            margin: 0 auto 20px auto;
            background: #fff;
            padding: 10mm;
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            display: flex;
            flex-direction: column;
            page-break-after: always;
            page-break-inside: avoid;
        }

        .header {
            display: flex;
            justify-content: space-between;
            border-bottom: 2px solid #0f172a;
            padding-bottom: 6px;
            margin-bottom: 10px;
            flex-shrink: 0;
        }

        .header h1 {
            font-size: 18px;
            font-weight: 900;
            text-transform: uppercase;
            margin-bottom: 2px;
        }

        .header p {
            font-size: 10px;
            color: #475569;
            font-weight: 600;
        }

        .header .right {
            text-align: right;
        }

        /* GRID 3 Kolom x 2 Baris Pasti */
        .bom-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            grid-template-rows: repeat(2, 1fr);
            gap: 6px;
            flex-grow: 1;
            height: 100%;
            overflow: hidden;
        }

        .bom-card {
            border: 1px solid #0f172a;
            border-radius: 6px;
            overflow: hidden;
            background: #fff;
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .bom-card-header {
            background: #0f172a;
            color: #fff;
            padding: 4px 6px;
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .bom-card-header .sku {
            font-family: 'Space Mono', monospace;
            font-size: 8px;
            font-weight: 700;
            color: #93c5fd;
        }

        .bom-card-header .name {
            font-size: 10px;
            font-weight: 900;
            text-transform: uppercase;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .bom-card-body {
            flex-grow: 1;
            overflow: hidden;
        }

        .section-badge {
            background: #eff6ff;
            color: #1d4ed8;
            padding: 2px 6px;
            font-size: 7.5px;
            font-weight: 800;
            text-transform: uppercase;
            border-bottom: 1px solid #bfdbfe;
            border-top: 1px solid #bfdbfe;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .mini-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .mini-table th {
            font-size: 7px;
            text-transform: uppercase;
            background: #f8fafc;
            color: #475569;
            padding: 3px;
            text-align: left;
            border-bottom: 1px solid #cbd5e1;
            border-right: 1px solid #e2e8f0;
        }

        .mini-table th:last-child { border-right: none; }

        .mini-table td {
            font-size: 7.5px;
            padding: 3px;
            border-bottom: 1px dashed #e2e8f0;
            border-right: 1px dashed #e2e8f0;
            font-weight: 600;
            vertical-align: middle;
            line-height: 1.1;
            word-wrap: break-word;
        }

        .mini-table td:last-child { border-right: none; }
        .mini-table tr:last-child td { border-bottom: none; }

        .t-center { text-align: center; }
        .t-val {
            font-family: 'Space Mono', monospace;
            font-weight: 700;
            text-align: center;
        }

        .badge {
            background: #f1f5f9;
            border: 1px solid #cbd5e1;
            color: #0f172a;
            padding: 1px 3px;
            border-radius: 4px;
            font-size: 7px;
            font-weight: 900;
            display: inline-block;
            white-space: nowrap;
        }

        .empty-state {
            padding: 15px;
            text-align: center;
            font-style: italic;
            font-size: 8px;
            color: #94a3b8;
        }

        /* Optimasi Cetak */
        @media print {
            body { background: #fff; padding: 0; }
            .no-print { display: none !important; }
            
            @page {
                size: A4 landscape;
                margin: 5mm; 
            }

            .a4-page {
                width: 100%;
                height: 100vh;
                padding: 0;
                margin: 0;
                box-shadow: none;
                border: none;
                border-radius: 0;
                page-break-after: always;
            }

            .a4-page:last-of-type {
                page-break-after: auto;
            }

            .bom-card {
                border-width: 1px;
            }
        }
    </style>
</head>
<body>

    <div class="action-bar no-print">
        <button onclick="window.print()" class="btn-print">
            <i class="ph-bold ph-printer"></i> Cetak Katalog Resep (Super Hemat Kertas)
        </button>
        <br>
        <div class="print-guide">
            <strong>Panduan Cetak:</strong><br>
            1. Ubah <b>Layout</b> printer menjadi <b>Landscape</b>.<br>
            2. Atur <b>Margins</b> menjadi <b>Minimum / None</b>.<br>
            3. Centang <b>"Background graphics"</b>.
        </div>
    </div>

    <?php 
        // LAKUKAN SORTING BERDASARKAN NAMA PRODUK SEBELUM DI-CHUNK (DIBAGI 6)
        usort($bomDataBatch, function($a, $b) {
            $nameA = strtoupper((string) ($a['bom']['item_name'] ?? $a['bom']['recipe_name'] ?? ''));
            $nameB = strtoupper((string) ($b['bom']['item_name'] ?? $b['bom']['recipe_name'] ?? ''));
            return $nameA <=> $nameB;
        });

        // Setelah terurut (A-Z), baru pecah menjadi array berisi maksimal 6 item per halaman
        $chunks = array_chunk($bomDataBatch, 6); 
    ?>

    <?php foreach ($chunks as $pageIndex => $chunk): ?>
        <div class="a4-page">
            <div class="header">
                <div>
                    <h1>BUKU KATALOG MATERIAL (BOM)</h1>
                    <p><?= esc($company['company_name'] ?? 'PT. NORIC EXHAUST') ?> - DIVISI PRODUKSI PUSAT</p>
                </div>
                <div class="right">
                    <h2>Halaman <?= $pageIndex + 1 ?> / <?= count($chunks) ?></h2>
                    <p>Dicetak: <?= date('d/m/Y H:i') ?></p>
                </div>
            </div>

            <div class="bom-grid">
                <?php foreach ($chunk as $data): ?>
                    <?php
                        $bom = $data['bom'];
                        $items = $data['items'];
                        $groupedItems = [];
                        
                        // Opsional: Urutkan material dalam resep secara alfabet juga
                        if (!empty($items)) {
                            usort($items, function($a, $b) {
                                $matA = strtoupper((string) ($a['material_name'] ?? $a['rm_name'] ?? $a['item_name'] ?? ''));
                                $matB = strtoupper((string) ($b['material_name'] ?? $b['rm_name'] ?? $b['item_name'] ?? ''));
                                return $matA <=> $matB;
                            });

                            foreach ($items as $item) {
                                $secName = !empty($item['section_name']) ? strtoupper($item['section_name']) : 'BAGIAN UTAMA';
                                $groupedItems[$secName][] = $item;
                            }
                        }
                    ?>
                    <div class="bom-card">
                        <div class="bom-card-header">
                            <div class="sku"><?= esc($bom['fg_sku']) ?></div>
                            <div class="name"><?= esc($bom['item_name'] ?? $bom['recipe_name'] ?? 'Produk Umum') ?></div>
                        </div>

                        <div class="bom-card-body">
                            <?php if (empty($groupedItems)): ?>
                                <div class="empty-state">Tidak ada material</div>
                            <?php else: ?>
                                <?php foreach ($groupedItems as $sectionName => $sectionItems): ?>
                                    <div class="section-badge">
                                        <i class="ph-bold ph-cube"></i> <?= esc($sectionName) ?>
                                    </div>
                                    <table class="mini-table">
                                        <thead>
                                            <tr>
                                                <th width="7%" class="t-center">No</th>
                                                <th width="39%">Material</th>
                                                <th width="18%" class="t-center">Ukuran</th>
                                                <th width="18%" class="t-center">Isi</th>
                                                <th width="18%" class="t-center">Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $n = 1; foreach ($sectionItems as $i): ?>
                                                <?php
                                                    $sizePerItem = floatval($i['size_per_item'] ?? 0);
                                                    $sizeUom     = trim($i['size_uom'] ?? '');
                                                    $qtyPerItem  = floatval($i['qty_per_item'] ?? 0);
                                                    $qtyUom      = trim($i['qty_uom'] ?? '');
                                                    $totalQty    = floatval($i['qty_required'] ?? 0);
                                                    $totalUom    = trim($i['unit'] ?? $qtyUom ?? 'PCS');

                                                    $ukuran = ($sizePerItem > 0) ? rtrim(rtrim(number_format($sizePerItem, 2, '.', ''), '0'), '.') . ' ' . $sizeUom : '-';
                                                    $jmlItem = ($qtyPerItem > 0) ? rtrim(rtrim(number_format($qtyPerItem, 2, '.', ''), '0'), '.') . ' ' . $qtyUom : '-';
                                                    $materialName = $i['material_name'] ?? $i['rm_name'] ?? $i['item_name'] ?? $i['fg_name'] ?? $i['rm_sku'] ?? '-';
                                                    $formattedTotalQty = rtrim(rtrim(number_format($totalQty, 2, '.', ''), '0'), '.');
                                                ?>
                                                <tr>
                                                    <td class="t-center"><?= $n++ ?></td>
                                                    <td><?= esc($materialName) ?></td>
                                                    <td class="t-val"><?= esc($ukuran) ?></td>
                                                    <td class="t-val"><?= esc($jmlItem) ?></td>
                                                    <td class="t-val"><span class="badge"><?= esc($formattedTotalQty) ?> <?= esc($totalUom) ?></span></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endforeach; ?>
</body>
</html>