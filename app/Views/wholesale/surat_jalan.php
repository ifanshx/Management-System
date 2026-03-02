<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?></title>
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800;900&family=Space+Mono:wght@400;700&display=swap');

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            margin: 0;
            padding: 0;
            background: #f4f4f5;
            color: #18181b;
        }

        .a4-container {
            background: #ffffff;
            width: 210mm;
            min-height: 297mm;
            margin: 40px auto;
            padding: 20mm;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            position: relative;
        }

        /* HEADER DOKUMEN */
        .doc-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 3px solid #18181b;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }

        .brand-info h1 { margin: 0; font-size: 28px; font-weight: 900; letter-spacing: -1px; display: flex; align-items: center; gap: 10px;}
        .brand-info p { margin: 5px 0 0 0; font-size: 13px; color: #555; line-height: 1.5;}

        .doc-type { text-align: right; }
        .doc-type h2 { margin: 0; font-size: 32px; font-weight: 900; color: #18181b; text-transform: uppercase; letter-spacing: 2px;}
        .doc-type p { margin: 5px 0 0 0; font-size: 14px; font-family: 'Space Mono', monospace; font-weight: 700; background: #f4f4f5; padding: 6px 12px; border-radius: 6px; display: inline-block;}

        /* INFO PENGIRIMAN */
        .shipping-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            margin-bottom: 40px;
        }

        .info-box { border: 1px solid #e4e4e7; border-radius: 12px; padding: 20px;}
        .info-box h4 { margin: 0 0 15px 0; font-size: 12px; font-weight: 800; color: #71717a; text-transform: uppercase; letter-spacing: 1px;}
        
        .customer-name { font-size: 18px; font-weight: 800; margin-bottom: 8px;}
        .customer-detail { font-size: 13px; color: #3f3f46; line-height: 1.6;}
        .customer-detail b { color: #18181b;}

        /* TABEL BARANG */
        table { width: 100%; border-collapse: collapse; margin-bottom: 40px; }
        th { text-align: left; padding: 15px; background: #f4f4f5; font-size: 12px; font-weight: 800; text-transform: uppercase; border-top: 2px solid #18181b; border-bottom: 2px solid #18181b;}
        td { padding: 15px; font-size: 14px; font-weight: 600; border-bottom: 1px dashed #d4d4d8; vertical-align: middle;}
        
        .sku-code { font-family: 'Space Mono', monospace; font-size: 11px; color: #71717a; background: #f4f4f5; padding: 4px 8px; border-radius: 4px;}

        /* TANDA TANGAN */
        .signature-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 20px;
            margin-top: 60px;
            text-align: center;
        }
        .sig-box { display: flex; flex-direction: column; align-items: center;}
        .sig-title { font-size: 13px; font-weight: 800; color: #555; margin-bottom: 80px;}
        .sig-line { width: 80%; border-bottom: 1px solid #18181b; margin-bottom: 8px;}
        .sig-name { font-size: 12px; color: #71717a;}

        /* TOMBOL CETAK & MEDIA PRINT */
        .action-bar { text-align: center; margin: 30px 0; }
        .btn-print { background: #3b82f6; color: white; border: none; padding: 15px 30px; font-size: 16px; font-weight: 800; border-radius: 12px; cursor: pointer; display: inline-flex; align-items: center; gap: 10px; box-shadow: 0 10px 20px rgba(59,130,246,0.3); transition: 0.3s;}
        .btn-print:hover { background: #2563eb; transform: translateY(-3px);}

        @media print {
            body { background: #ffffff; }
            .a4-container { margin: 0; padding: 0; box-shadow: none; width: 100%; min-height: auto;}
            .action-bar { display: none !important; }
            @page { margin: 15mm; }
        }
    </style>
</head>
<body>

    <div class="action-bar">
        <button onclick="window.print()" class="btn-print">
            <i class="ph-bold ph-printer" style="font-size: 24px;"></i> Cetak Surat Jalan (A4)
        </button>
        <p style="font-size: 12px; color: #71717a; margin-top: 10px;">Tekan tombol di atas atau Ctrl+P. Dokumen ini disesuaikan untuk kertas A4.</p>
    </div>

    <div class="a4-container">
        
        <div class="doc-header">
            <div class="brand-info">
                <h1><i class="ph-fill ph-factory"></i> PT. NORIC MANUFAKTUR</h1>
                <p>Jl. Kawasan Industri No. 88, Jawa Barat<br>Telp: (021) 8899-7766 | Email: logistics@noric.com</p>
            </div>
            <div class="doc-type">
                <h2>SURAT JALAN</h2>
                <p><?= esc($so['so_number']) ?></p>
            </div>
        </div>

        <div class="shipping-info">
            <div class="info-box" style="background: #fafafa;">
                <h4><i class="ph-fill ph-truck" style="font-size: 16px; position:relative; top:2px;"></i> Informasi Pengiriman</h4>
                <div class="customer-detail">
                    <b>Tanggal Kirim:</b> <?= date('d F Y', strtotime($so['order_date'])) ?><br>
                    <b>Plat Nomor Truk:</b> ____________________<br>
                    <b>Nama Supir:</b> ____________________
                </div>
            </div>
            <div class="info-box">
                <h4><i class="ph-fill ph-buildings" style="font-size: 16px; position:relative; top:2px;"></i> Alamat Tujuan (Penerima)</h4>
                <div class="customer-name"><?= esc($so['company_name']) ?></div>
                <div class="customer-detail">
                    <b>UP:</b> <?= esc($so['contact_name'] ?? '-') ?> <br>
                    <b>Telp/WA:</b> <?= esc($so['phone'] ?? '-') ?> <br>
                    <span style="display: block; margin-top: 8px; border-top: 1px dashed #d4d4d8; padding-top: 8px;">
                        <?= esc($so['address'] ?? 'Alamat tidak terdata di sistem') ?>
                    </span>
                </div>
            </div>
        </div>

        <p style="font-size: 13px; font-weight: 700; color: #18181b;">Harap diterima dengan baik produk (PRD) di bawah ini:</p>
        <table>
            <thead>
                <tr>
                    <th style="width: 5%; text-align: center;">No.</th>
                    <th style="width: 25%;">Kode SKU</th>
                    <th style="width: 40%;">Deskripsi Produk</th>
                    <th style="width: 15%; text-align: center;">Kuantitas</th>
                    <th style="width: 15%;">Ceklis Fisik</th>
                </tr>
            </thead>
            <tbody>
                <?php $no = 1; foreach($items as $item): ?>
                <tr>
                    <td style="text-align: center; color: #71717a;"><?= $no++ ?>.</td>
                    <td><span class="sku-code"><?= esc($item['fg_sku']) ?></span></td>
                    <td style="font-weight: 800;"><?= esc($item['item_name'] ?? 'Produk Jadi') ?></td>
                    <td style="text-align: center; font-family: 'Space Mono', monospace; font-size: 16px; font-weight: 900; color: #10b981;">
                        <?= $item['qty'] ?> <span style="font-size: 11px; color: #71717a; font-family: 'Plus Jakarta Sans', sans-serif;">Pcs</span>
                    </td>
                    <td><div style="width: 24px; height: 24px; border: 2px solid #d4d4d8; border-radius: 4px;"></div></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div style="font-size: 12px; color: #71717a; margin-bottom: 20px; line-height: 1.5;">
            <b>Catatan Gudang:</b> Surat jalan ini adalah bukti sah serah terima barang fisik. 
            Mohon pihak penerima mengecek kesesuaian fisik produk dengan dokumen ini sebelum menandatangani.
        </div>

        <div class="signature-grid">
            <div class="sig-box">
                <div class="sig-title">Penerima Barang,</div>
                <div class="sig-line"></div>
                <div class="sig-name">(Tanda Tangan & Cap Toko)</div>
            </div>
            <div class="sig-box">
                <div class="sig-title">Supir / Pengirim,</div>
                <div class="sig-line"></div>
                <div class="sig-name">(Nama Jelas & TTD)</div>
            </div>
            <div class="sig-box">
                <div class="sig-title">Hormat Kami, (Gudang)</div>
                <div class="sig-line"></div>
                <div class="sig-name">PT. Noric Manufaktur</div>
            </div>
        </div>

    </div>

</body>
</html>