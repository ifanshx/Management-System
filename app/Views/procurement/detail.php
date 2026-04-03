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
    <title>PO - <?= esc($po['po_number']) ?></title>
    
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&family=Space+Mono:wght@700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>

    <style>
        :root {
            --text-main: #0f172a;
            --text-muted: #64748b;
            --border-color: #cbd5e1;
            --brand: #4f46e5;
            --bg-gray: #f8fafc;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: #94a3b8;
            color: var(--text-main);
            margin: 0;
            padding: 40px 20px;
            font-size: 12px;
        }

        .no-print { position: fixed; top: 20px; right: 30px; display: flex; gap: 12px; z-index: 1000; }
        .btn-action { background: #fff; color: var(--text-main); border: 1px solid var(--border-color); padding: 12px 24px; border-radius: 999px; font-weight: 800; font-size: 13px; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; text-decoration: none; box-shadow: 0 4px 15px rgba(0,0,0,0.05); transition: 0.3s; }
        .btn-print { background: var(--brand); color: #fff; border: none; }
        .btn-print:hover { transform: translateY(-2px); box-shadow: 0 10px 25px rgba(79,70,229,0.3); }
        .btn-back:hover { background: var(--bg-gray); transform: translateY(-2px); }

        .a4 {
            background: #fff;
            width: 210mm;
            min-height: 297mm;
            margin: 0 auto;
            padding: 20mm;
            box-shadow: 0 20px 50px rgba(0,0,0,0.15);
            position: relative;
            box-sizing: border-box;
            border-top: 14px solid var(--brand);
            border-radius: 4px;
        }

        .watermark { position: absolute; top: 40%; left: 50%; transform: translate(-50%, -50%) rotate(-20deg); font-size: 85px; font-weight: 900; letter-spacing: 12px; opacity: 0.07; border: 8px solid; padding: 15px 40px; border-radius: 20px; pointer-events: none; z-index: 0; }
        .wm-paid { color: #10b981; border-color: #10b981; }
        .wm-partial { color: #f59e0b; border-color: #f59e0b; }
        .wm-unpaid { color: #ef4444; border-color: #ef4444; }

        .header { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 2px solid var(--border-color); padding-bottom: 25px; margin-bottom: 30px; position: relative; z-index: 1;}
        .company { display: flex; align-items: center; gap: 15px; }
        .logo { width: 65px; height: 65px; object-fit: contain; }
        .logo-box { width: 55px; height: 55px; background: rgba(79,70,229,0.1); color: var(--brand); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 28px;}
        .com-text h1 { margin: 0 0 5px 0; font-size: 22px; font-weight: 900; color: var(--brand); letter-spacing: -0.5px;}
        .com-text p { margin: 0; font-size: 11px; color: var(--text-muted); line-height: 1.5; font-weight: 600;}

        .meta { text-align: right; }
        .meta h2 { margin: 0 0 10px 0; font-size: 26px; font-weight: 900; letter-spacing: -1px;}
        .meta-box { display: grid; grid-template-columns: auto auto; gap: 6px 20px; background: var(--bg-gray); padding: 12px 16px; border-radius: 10px; border: 1px solid var(--border-color); text-align: left;}
        .lbl { font-size: 10px; font-weight: 800; color: var(--text-muted); text-transform: uppercase;}
        .val { font-size: 12px; font-weight: 900; font-family: 'Space Mono', monospace; text-align: right;}

        .address-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 40px; margin-bottom: 40px; position: relative; z-index: 1;}
        .addr-title { font-size: 10px; font-weight: 900; color: var(--text-muted); text-transform: uppercase; border-bottom: 2px solid var(--brand); padding-bottom: 5px; margin-bottom: 10px; display: inline-block;}
        .addr h3 { margin: 0 0 5px 0; font-size: 16px; font-weight: 900; }
        .addr p { margin: 0 0 8px 0; line-height: 1.5; color: var(--text-muted); font-weight: 500;}
        .addr div { font-size: 12px; font-weight: 700; margin-top: 4px; }
        .addr div b { color: var(--text-muted); width: 45px; display: inline-block;}

        table { width: 100%; border-collapse: collapse; margin-bottom: 30px; position: relative; z-index: 1;}
        th { background: var(--bg-gray); color: var(--text-muted); padding: 12px; font-size: 10px; font-weight: 800; text-transform: uppercase; border-top: 1px solid var(--border-color); border-bottom: 2px solid var(--border-color); text-align: right;}
        td { padding: 14px 12px; border-bottom: 1px dashed var(--border-color); vertical-align: top; text-align: right; font-size: 12px; font-weight: 600;}
        th.l, td.l { text-align: left; }
        
        .t-name { font-weight: 800; font-size: 13px; color: var(--text-main); margin-bottom: 4px;}
        .t-sku { font-family: 'Space Mono', monospace; font-size: 10px; color: var(--text-muted); background: var(--bg-gray); padding: 3px 6px; border-radius: 4px; border: 1px solid var(--border-color);}
        .t-mono { font-family: 'Space Mono', monospace; font-weight: 700; color: var(--text-main);}

        .sum-flex { display: flex; justify-content: space-between; align-items: flex-start; gap: 30px; position: relative; z-index: 1;}
        .notes { width: 45%; background: var(--bg-gray); padding: 15px; border-radius: 12px; border: 1px dashed var(--border-color);}
        .notes h4 { margin: 0 0 6px 0; font-size: 10px; font-weight: 800; text-transform: uppercase; color: var(--text-muted);}
        .notes ul { margin: 0; padding-left: 15px; font-size: 10px; color: var(--text-muted); line-height: 1.6;}

        .calc { width: 50%; }
        .c-line { display: flex; justify-content: space-between; padding: 8px 10px; font-size: 12px; font-weight: 600; color: var(--text-muted);}
        .c-line.b { font-weight: 800; color: var(--text-main); font-size: 13px; border-top: 1px solid var(--border-color); margin-top: 5px; padding-top: 10px;}
        .c-line.paid { color: #10b981; font-weight: 800; }
        .c-grand { display: flex; justify-content: space-between; align-items: center; padding: 15px 12px; border-radius: 12px; margin-top: 10px; color: #fff; font-weight: 900; background: var(--brand);}
        .c-grand.c-unpaid { background: #ef4444; }
        .c-grand.c-partial { background: #f59e0b; }
        .c-grand.c-paid { background: #10b981; }
        .c-grand .t { font-size: 11px; letter-spacing: 1px; text-transform: uppercase; }
        .c-grand .v { font-family: 'Space Mono', monospace; font-size: 18px; letter-spacing: -0.5px;}

        .signs { display: grid; grid-template-columns: 1fr 1fr; gap: 40px; margin-top: 60px; text-align: center; position: relative; z-index: 1;}
        .s-title { font-size: 10px; font-weight: 800; color: var(--text-muted); text-transform: uppercase; margin-bottom: 70px;}
        .s-line { width: 70%; border-bottom: 1px solid var(--text-main); margin: 0 auto 8px auto;}
        .s-name { font-weight: 800; font-size: 12px; }

        @media print {
            body { background: #fff; padding: 0; }
            .a4 { margin: 0; box-shadow: none; width: 100%; border-top: 12px solid var(--brand); }
            .no-print { display: none !important; }
            @page { margin: 0; size: A4 portrait; }
            * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
        }
    </style>
</head>
<body>

    <div class="no-print">
        <a href="<?= base_url('/procurement') ?>" class="btn-action btn-back"><i class="ph-bold ph-arrow-left"></i> Kembali</a>
        <button onclick="window.print()" class="btn-action btn-print"><i class="ph-bold ph-printer"></i> Cetak / PDF</button>
    </div>

    <div class="a4">
        
        <?php if($po['payment_status'] === 'PAID'): ?>
            <div class="watermark wm-paid">LUNAS</div>
        <?php elseif($po['payment_status'] === 'PARTIAL'): ?>
            <div class="watermark wm-partial">DICICIL</div>
        <?php else: ?>
            <div class="watermark wm-unpaid">BELUM LUNAS</div>
        <?php endif; ?>

        <div class="header">
            <div class="company">
                <?php if(!empty($company['logo_path']) && $company['logo_path'] !== 'default-logo.png'): ?>
                    <img src="<?= base_url('uploads/logo/' . esc($company['logo_path'])) ?>" class="logo">
                <?php else: ?>
                    <div class="logo-box"><i class="ph-fill ph-factory"></i></div>
                <?php endif; ?>
                <div class="com-text">
                    <h1><?= esc($company['company_name'] ?? 'PT NORIC EXHAUST') ?></h1>
                    <p><?= nl2br(esc($company['address'] ?? 'Alamat Belum Diatur')) ?><br>Telp: <b><?= esc($company['phone'] ?? '-') ?></b></p>
                </div>
            </div>
            
            <div class="meta">
                <h2>PURCHASE ORDER</h2>
                <div class="meta-box">
                    <span class="lbl">No Dokumen</span><span class="val"><?= esc($po['po_number']) ?></span>
                    <span class="lbl">Tgl Terbit</span><span class="val"><?= date('d M Y', strtotime($po['po_date'])) ?></span>
                    <span class="lbl">Termin Bayar</span><span class="val"><?= esc($po['payment_term'] ?? 'Cash') ?></span>
                </div>
            </div>
        </div>

        <div class="address-grid">
            <div class="addr">
                <div class="addr-title">Vendor Tujuan (TO)</div>
                <h3><?= esc($po['supplier_name']) ?></h3>
                <p><?= nl2br(esc($po['address'] ?? '-')) ?></p>
                <div><b>ATTN</b>: <?= esc($po['contact_person'] ?: '-') ?></div>
                <div><b>TELP</b>: <?= esc($po['phone'] ?: '-') ?></div>
            </div>
            <div class="addr">
                <div class="addr-title">Kirim Barang Ke (SHIP TO)</div>
                <h3><?= esc($company['company_name'] ?? 'PT NORIC EXHAUST') ?></h3>
                <p><?= nl2br(esc($company['address'] ?? 'Alamat Belum Diatur')) ?></p>
                <div><b>ATTN</b>: Dept. Gudang & Logistik</div>
                <div><b>TELP</b>: <?= esc($company['phone'] ?? '-') ?></div>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th class="l" style="width: 5%;">No</th>
                    <th class="l" style="width: 45%;">Material / Barang</th>
                    <th style="width: 15%;">Qty</th>
                    <th style="width: 15%;">Harga Sat.</th>
                    <th style="width: 20%;">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php $no=1; foreach($items as $item): ?>
                <tr>
                    <td class="l t-mono"><?= $no++ ?></td>
                    <td class="l">
                        <div class="t-name"><?= esc($item['material_name'] ?: '-') ?></div>
                        <span class="t-sku"><i class="ph-bold ph-barcode"></i> <?= esc($item['rm_sku']) ?></span>
                    </td>
                    <td class="t-mono"><?= floatval($item['qty']) ?> <span style="font-size:9px; color:var(--text-muted);"><?= esc($item['unit'] ?: 'Unit') ?></span></td>
                    <td class="t-mono"><?= number_format($item['unit_price'], 0, ',', '.') ?></td>
                    <td class="t-mono" style="color:var(--text-main); font-weight:900;"><?= number_format($item['subtotal'], 0, ',', '.') ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="sum-flex">
            <div class="notes">
                <h4>Syarat & Ketentuan</h4>
                <ul>
                    <li>Cantumkan Nomor PO pada Invoice dan Surat Jalan.</li>
                    <li>Barang yang dikirim harus sesuai dengan spesifikasi dan standar kualitas yang diminta.</li>
                    <li>Barang cacat/rusak akan diretur atas biaya Vendor.</li>
                </ul>
            </div>

            <div class="calc">
                <div class="c-line"><span>Subtotal Barang</span><span class="t-mono">Rp <?= number_format($po['subtotal'], 0, ',', '.') ?></span></div>
                <div class="c-line"><span>Pajak (PPN)</span><span class="t-mono">Rp <?= number_format($po['tax_amount'], 0, ',', '.') ?></span></div>
                <div class="c-line"><span>Biaya Ongkir</span><span class="t-mono">Rp <?= number_format($po['shipping_cost'], 0, ',', '.') ?></span></div>
                
                <div class="c-line b"><span>Total Tagihan PO</span><span class="t-mono">Rp <?= number_format($totalAmt, 0, ',', '.') ?></span></div>
                
                <div class="c-line paid"><span>Sudah Dibayar</span><span class="t-mono">- Rp <?= number_format($paidAmt, 0, ',', '.') ?></span></div>

                <?php 
                    $cls = 'c-unpaid';
                    if($balance <= 0) $cls = 'c-paid';
                    elseif($paidAmt > 0) $cls = 'c-partial';
                ?>
                <div class="c-grand <?= $cls ?>">
                    <span class="t"><?= $balance <= 0 ? 'Status: Lunas' : 'Sisa Tagihan' ?></span>
                    <span class="v">Rp <?= number_format(abs($balance), 0, ',', '.') ?></span>
                </div>
            </div>
        </div>

        <div class="signs">
            <div>
                <div class="s-title">Disetujui Oleh (Pembeli)</div>
                <div class="s-line"></div>
                <div class="s-name">Procurement & Finance</div>
            </div>
            <div>
                <div class="s-title">Diterima Oleh (Vendor)</div>
                <div class="s-line"></div>
                <div class="s-name"><?= esc($po['supplier_name']) ?></div>
            </div>
        </div>

    </div>
</body>
</html>