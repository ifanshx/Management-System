<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekap Produksi | <?= esc($company['app_name'] ?? 'Noric ERP') ?></title>
    <?php if (isset($company['logo_path']) && !empty($company['logo_path'])): ?>
        <link rel="icon" type="image/png" href="<?= base_url('uploads/logo/' . esc($company['logo_path'])) ?>">
    <?php endif; ?>
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Space+Mono:wght@400;700&display=swap');
        * { box-sizing: border-box; margin: 0; padding: 0; }
        :root { --ink: #0f172a; --muted: #475569; --line: #cbd5e1; --paper: #ffffff; --bg: #e2e8f0; --warning: #b45309; --accent: #2563eb; --success: #166534; }
        body { font-family: 'Inter', sans-serif; font-size: 11px; color: var(--ink); background: var(--bg); padding: 18px; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
        .container { width: 100%; max-width: 820px; margin: 0 auto; background: var(--paper); padding: 30px; box-shadow: 0 10px 28px rgba(15, 23, 42, 0.10); border-radius: 14px; position: relative; overflow: hidden; }
        .watermark-bg { position: absolute; inset: 0; <?php if (!empty($company['logo_path'])): ?> background-image: url('<?= base_url('uploads/logo/' . esc($company['logo_path'])) ?>'); <?php endif; ?> background-repeat: repeat; background-size: 170px; background-position: center; opacity: 0.025; transform: rotate(-22deg) scale(1.15); z-index: 1; pointer-events: none; }
        .content-wrapper { position: relative; z-index: 5; }
        .action-bar { text-align: center; margin-bottom: 20px; }
        .btn-print { background: linear-gradient(135deg, #f59e0b, #d97706); color: #fff; border: none; padding: 11px 22px; font-size: 13px; font-weight: 800; border-radius: 10px; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; }
        .print-guide { display: inline-block; text-align: left; background: #ffffff; padding: 10px 16px; border-radius: 10px; border: 1px dashed #94a3b8; font-size: 11px; color: #334155; margin-top: 12px; }
        .header { border-bottom: 2px solid #0f172a; padding-bottom: 15px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: flex-end; }
        .company-text h1 { font-size: 22px; font-weight: 900; margin-bottom: 4px; text-transform: uppercase; letter-spacing: -0.5px; }
        .company-text .sub { font-size: 11px; color: var(--muted); font-weight: 700; }
        .doc-title { text-align: right; }
        .doc-title h2 { font-size: 20px; font-weight: 900; color: var(--warning); text-transform: uppercase; margin-bottom: 5px;}
        .doc-title p { font-family: 'Space Mono', monospace; font-size: 14px; font-weight: 800; background: #fef3c7; color: #92400e; padding: 4px 10px; border-radius: 8px; display: inline-block; border: 1px dashed #fcd34d;}
        .info-panel { background: #f8fafc; border: 1px solid var(--line); border-radius: 10px; padding: 15px; margin-bottom: 20px; display: flex; justify-content: space-between; }
        .info-item { display: flex; flex-direction: column; gap: 4px; }
        .info-label { font-size: 10px; font-weight: 800; color: var(--muted); text-transform: uppercase; }
        .info-val { font-size: 14px; font-weight: 900; color: var(--ink); }
        .table-wrap { border: 1.5px solid #0f172a; border-radius: 12px; overflow: hidden; margin-bottom: 20px; }
        .item-table { width: 100%; border-collapse: collapse; }
        .item-table th { background: #0f172a; color: #fff; text-align: left; padding: 12px 10px; font-size: 10.5px; font-weight: 800; text-transform: uppercase; border-right: 1px solid rgba(255,255,255,0.12);}
        .item-table th:last-child { border-right: none; }
        .item-table td { padding: 10px 10px; border-bottom: 1px solid var(--line); font-size: 11px; font-weight: 600; vertical-align: middle; }
        .item-table tr:last-child td { border-bottom: none; }
        .item-table tr:nth-child(even) { background: rgba(0,0,0,0.02); }
        .qty-box { font-family: 'Space Mono', monospace; font-size: 13.5px; font-weight: 900; text-align: center; }
        .sku-code { font-family: 'Space Mono', monospace; color: var(--ink); font-size: 10.5px; font-weight: 900;}
        .note-badge { font-size: 10px; font-weight: 900; color: var(--warning); display: block; margin-top: 5px; }
        .total-row td { background: #eff6ff; font-size: 14px; font-weight: 900; color: var(--accent); padding: 14px 10px; border-top: 1.5px solid #0f172a;}
        .signature-section { display: flex; justify-content: space-between; gap: 15px; margin-top: 40px; text-align: center; }
        .sig-box { flex: 1; border: 1px dashed #94a3b8; border-radius: 10px; padding: 10px; background: #fff; }
        .sig-box p { font-size: 11px; font-weight: 800; margin-bottom: 50px; }
        .sig-line { border-top: 1px solid #0f172a; margin: 0 auto; width: 80%; padding-top: 5px; font-weight: 700; font-size: 10px;}
        @page { size: A4 portrait; margin: 10mm; }
        @media print { body { padding: 0; background: #fff; } .container { box-shadow: none; max-width: 100%; border-radius: 0; padding: 0; border: none;} .no-print { display: none !important; } .watermark-bg { position: fixed; inset: 0; width: 100vw; height: 100vh; } }
    </style>
</head>
<body>
    <div class="action-bar no-print">
        <button onclick="window.print()" class="btn-print"><i class="ph-bold ph-printer"></i> Cetak Rekap A4</button>
        <div class="print-guide">Berikan kertas ini ke Mandor untuk dipajang sebagai papan target.</div>
    </div>
    <div class="container">
        <div class="watermark-bg"></div>
        <div class="content-wrapper">
            <div class="header">
                <div class="company-text">
                    <h1>SURAT REKAPITULASI TARGET</h1>
                    <div class="sub"><?= esc($company['company_name'] ?? 'PT. NORIC EXHAUST') ?> - DIVISI PRODUKSI PUSAT</div>
                </div>
                <div class="doc-title">
                    <h2>SPK GABUNGAN</h2>
                    <p><i class="ph-bold ph-storefront"></i> <?= esc($so['company_name'] ?? 'Internal / Stok Gudang') ?></p>
                </div>
            </div>
            <div class="info-panel">
                <div class="info-item">
                    <span class="info-label">Referensi SO / Order</span>
                    <span class="info-val"><?= esc($so['so_number'] ?? 'SPK INTERNAL') ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Tgl. Terbit SPK</span>
                    <span class="info-val"><?= date('d F Y') ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Deadline Produksi</span>
                    <span class="info-val" style="color: var(--danger);">
                        <?= !empty($so['due_date']) ? date('d F Y', strtotime($so['due_date'])) : 'Menyesuaikan Gudang' ?>
                    </span>
                </div>
            </div>
            <div class="table-wrap">
                <table class="item-table">
                    <thead>
                        <tr>
                            <th style="width:5%; text-align:center;">No</th>
                            <th style="width:20%;">Nomor SPK</th>
                            <th style="width:35%;">Target Produk & Keterangan</th>
                            <th style="width:13%; text-align:center;">Target Qty</th>
                            <th style="width:13%; text-align:center;">Selesai Qty</th>
                            <th style="width:14%; text-align:center;">Ceklist</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; $totalTarget = 0; $totalSelesai = 0; ?>
                        <?php foreach($workOrders as $wo): ?>
                            <?php 
                                $totalTarget += (int)$wo['planned_qty']; 
                                $totalSelesai += (int)$wo['completed_qty'];
                            ?>
                            <tr>
                                <td style="text-align:center;"><?= $no++ ?></td>
                                <td>
                                    <span class="sku-code"><?= esc($wo['spk_number']) ?></span>
                                    <div style="font-size: 9.5px; color: var(--muted); margin-top:3px; font-weight:700;">SKU: [<?= esc($wo['fg_sku']) ?>]</div>
                                </td>
                                <td>
                                    <div style="font-size: 13px; font-weight: 800; text-transform: uppercase;"><?= esc($wo['item_name']) ?></div>
                                    <?php if(!empty($wo['production_notes'])): ?>
                                        <span class="note-badge"><i class="ph-bold ph-warning-circle"></i> CATATAN: <?= esc($wo['production_notes']) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="qty-box" style="color: var(--accent);"><?= $wo['planned_qty'] ?> Pcs</td>
                                <td class="qty-box" style="color: var(--success);"><?= floatval($wo['completed_qty']) ?> Pcs</td>
                                <td></td>
                            </tr>
                        <?php endforeach; ?>
                        <tr class="total-row">
                            <td colspan="3" style="text-align: right; text-transform: uppercase;">Total Keseluruhan :</td>
                            <td class="qty-box" style="font-size: 16.5px;"><?= $totalTarget ?> Pcs</td>
                            <td class="qty-box" style="font-size: 16.5px; color: var(--success);"><?= $totalSelesai ?> Pcs</td>
                            <td></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="signature-section">
                <div class="sig-box"><p>Kepala PPIC</p><div class="sig-line">Admin Produksi</div></div>
                <div class="sig-box"><p>Mengetahui / Pimpinan</p><div class="sig-line">Manager Pabrik</div></div>
                <div class="sig-box"><p>Diterima Oleh</p><div class="sig-line">Mandor Bengkel</div></div>
            </div>
        </div>
    </div>
</body>
</html>