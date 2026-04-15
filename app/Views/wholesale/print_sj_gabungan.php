<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Jalan Gabungan - <?= esc($customer['company_name']) ?></title>
    <link href="https://unpkg.com/@phosphor-icons/web" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&family=Space+Mono:wght@700&display=swap');
        body { font-family: 'Inter', sans-serif; font-size: 11px; color: #0f172a; padding: 20px; background: #e2e8f0; }
        .container { max-width: 800px; margin: 0 auto; background: #fff; padding: 30px; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); position: relative; }
        .header { display: flex; justify-content: space-between; border-bottom: 2px solid #0f172a; padding-bottom: 15px; margin-bottom: 20px; }
        .title h1 { font-size: 24px; font-weight: 900; margin-bottom: 5px; text-transform: uppercase; }
        .title-badge { font-family: 'Space Mono', monospace; font-size: 11px; background: #0f172a; color: #fff; padding: 5px 10px; border-radius: 6px; }
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px; }
        .info-box { border: 1px solid #cbd5e1; padding: 12px; border-radius: 8px; background: #f8fafc; }
        .info-box h4 { font-size: 10px; text-transform: uppercase; border-bottom: 1px dashed #cbd5e1; padding-bottom: 5px; margin-bottom: 8px; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #0f172a; color: #fff; font-size: 10px; padding: 8px; text-transform: uppercase; }
        td { border-bottom: 1px dashed #cbd5e1; padding: 8px; vertical-align: middle; }
        .qty-box { font-family: 'Space Mono'; font-weight: 900; background: #f1f5f9; padding: 4px 8px; border-radius: 6px; border: 1px solid #cbd5e1; }
        .so-badge { font-size: 9px; color: #8b5cf6; font-weight: 900; background: rgba(139,92,246,0.1); padding: 2px 6px; border-radius: 4px; }
        .ttd-grid { display: flex; justify-content: space-between; margin-top: 40px; text-align: center; }
        .ttd-box { width: 30%; border: 1px solid #cbd5e1; border-radius: 8px; padding: 10px; }
        .ttd-space { height: 60px; }
        @media print { body { padding: 0; background: #fff; } .container { box-shadow: none; padding: 0; } .no-print { display: none; } }
    </style>
</head>
<body>
    <div class="no-print" style="text-align: center; margin-bottom: 20px;">
        <button onclick="window.print()" style="background: #10b981; color: #fff; padding: 12px 24px; border: none; border-radius: 8px; font-weight: 900; cursor: pointer; font-size: 14px;"><i class="ph-bold ph-printer"></i> Cetak Surat Jalan Gabungan</button>
    </div>

    <div class="container">
        <div class="header">
            <div>
                <h1><?= esc($company['company_name'] ?? 'PT. NORIC EXHAUST') ?></h1>
                <p style="color: #64748b; font-weight: 600; font-size: 10px; max-width: 250px;"><?= esc($company['address'] ?? 'Purbalingga') ?></p>
            </div>
            <div style="text-align: right;">
                <div style="font-size: 10px; font-weight: 900; color: #64748b; margin-bottom: 5px;">DOKUMEN PENGIRIMAN</div>
                <div class="title-badge">SURAT JALAN GABUNGAN</div>
            </div>
        </div>

        <div class="info-grid">
            <div class="info-box">
                <h4><i class="ph-bold ph-storefront"></i> Tujuan Pengiriman (Pelanggan)</h4>
                <div style="font-weight: 900; font-size: 14px; margin-bottom: 4px;"><?= esc($customer['company_name']) ?></div>
                <div style="font-weight: 600; color: #475569; margin-bottom: 2px;">UP: <?= esc($customer['contact_name']) ?> (<?= esc($customer['phone']) ?>)</div>
                <div style="color: #64748b; line-height: 1.4;"><?= esc($customer['address']) ?></div>
            </div>
            <div class="info-box">
                <h4><i class="ph-bold ph-truck"></i> Referensi Pengiriman</h4>
                <table style="width: 100%; font-size: 10px; font-weight: 600;">
                    <tr><td style="padding: 2px 0; border: none; width: 80px;">Tanggal Kirim</td><td style="padding: 2px 0; border: none;">: <b><?= date('d M Y') ?></b></td></tr>
                    <tr><td style="padding: 2px 0; border: none;">Plat Nomor</td><td style="padding: 2px 0; border: none;">: ........................</td></tr>
                    <tr><td style="padding: 2px 0; border: none;">Nama Supir</td><td style="padding: 2px 0; border: none;">: ........................</td></tr>
                </table>
            </div>
        </div>

        <div style="border: 1px solid #0f172a; border-radius: 8px; overflow: hidden; margin-bottom: 20px;">
            <table>
                <thead>
                    <tr>
                        <th style="width: 5%;">No</th>
                        <th style="width: 50%;">Deskripsi Barang & Catatan</th>
                        <th style="width: 25%; text-align: center;">Ref. SO</th>
                        <th style="width: 20%; text-align: center;">Qty Dikirim</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1; $totalKirim = 0; foreach($items as $itm): 
                        // Hanya tampilkan jika ada yang dikirim
                        if($itm['shipped_qty'] <= 0) continue; 
                        $totalKirim += $itm['shipped_qty'];
                    ?>
                        <tr>
                            <td style="text-align: center; font-weight: 900;"><?= $no++ ?></td>
                            <td>
                                <div style="font-weight: 800; font-size: 12px;"><?= esc($itm['item_name'] ?: $itm['fg_sku']) ?></div>
                                <?php if($itm['additional_note']): ?>
                                    <div style="font-size: 9px; color: #64748b; font-style: italic;">Note: <?= esc($itm['additional_note']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: center;"><span class="so-badge"><?= esc($itm['so_number']) ?></span></td>
                            <td style="text-align: center;"><span class="qty-box"><?= $itm['shipped_qty'] ?> Pcs</span></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <div style="background: #0f172a; color: #fff; padding: 10px 15px; display: flex; justify-content: space-between; font-weight: 900; text-transform: uppercase;">
                <span>Total Barang Dikirim Dalam Truk/Paket:</span>
                <span style="font-family: 'Space Mono'; font-size: 14px;"><?= $totalKirim ?> Pcs</span>
            </div>
        </div>

        <div class="ttd-grid">
            <div class="ttd-box"><p>Penerima Barang</p><div class="ttd-space"></div><div style="border-top:1px solid #0f172a;"></div><p style="font-size:8px; margin-top:4px; color:#64748b;">Nama & Tanda Tangan</p></div>
            <div class="ttd-box"><p>Supir / Ekspedisi</p><div class="ttd-space"></div><div style="border-top:1px solid #0f172a;"></div><p style="font-size:8px; margin-top:4px; color:#64748b;">Nama & Tanda Tangan</p></div>
            <div class="ttd-box"><p>Hormat Kami (Gudang)</p><div class="ttd-space"></div><div style="border-top:1px solid #0f172a;"></div><p style="font-size:8px; margin-top:4px; color:#64748b;"><?= esc($company['company_name'] ?? 'Pabrik Knalpot') ?></p></div>
        </div>
    </div>
</body>
</html>