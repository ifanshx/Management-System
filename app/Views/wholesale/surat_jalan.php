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
            background: #f1f5f9;
            color: #0f172a;
        }

        .a4-container {
            background: #ffffff;
            width: 210mm;
            min-height: 297mm;
            margin: 40px auto;
            padding: 20mm;
            box-shadow: 0 20px 50px rgba(0,0,0,0.1);
            position: relative;
            box-sizing: border-box;
        }

        /* HEADER DOKUMEN */
        .doc-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 4px solid #0f172a;
            padding-bottom: 25px;
            margin-bottom: 35px;
        }

        .brand-info h1 { margin: 0; font-size: 32px; font-weight: 900; letter-spacing: -1.5px; display: flex; align-items: center; gap: 12px; color: #0f172a;}
        .brand-info p { margin: 8px 0 0 0; font-size: 14px; color: #475569; line-height: 1.6; font-weight: 600;}

        .doc-type { text-align: right; }
        .doc-type h2 { margin: 0 0 8px 0; font-size: 34px; font-weight: 900; color: #10b981; text-transform: uppercase; letter-spacing: 2px;}
        .doc-type p { margin: 0; font-size: 15px; font-family: 'Space Mono', monospace; font-weight: 700; background: #f1f5f9; padding: 8px 16px; border-radius: 8px; display: inline-block; border: 1px dashed #cbd5e1;}

        /* INFO PENGIRIMAN */
        .shipping-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 40px;
        }

        .info-box { border: 2px solid #e2e8f0; border-radius: 16px; padding: 25px;}
        .info-box.highlight { background: #f8fafc; border-color: #cbd5e1;}
        .info-box h4 { margin: 0 0 20px 0; font-size: 13px; font-weight: 900; color: #64748b; text-transform: uppercase; letter-spacing: 1px; display: flex; align-items: center; gap: 8px;}
        .info-box h4 i { color: #10b981; font-size: 18px; }
        
        .customer-name { font-size: 20px; font-weight: 900; margin-bottom: 12px; color: #0f172a;}
        .customer-detail { font-size: 14px; color: #475569; line-height: 1.8; font-weight: 500;}
        .customer-detail b { color: #0f172a; font-weight: 800;}

        /* TABEL BARANG */
        table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        th { text-align: left; padding: 18px; background: #f1f5f9; font-size: 12px; font-weight: 900; text-transform: uppercase; color: #475569; border-top: 3px solid #0f172a; border-bottom: 3px solid #0f172a; letter-spacing: 0.5px;}
        td { padding: 18px; font-size: 15px; font-weight: 700; border-bottom: 1px dashed #cbd5e1; vertical-align: middle;}
        
        .sku-code { font-family: 'Space Mono', monospace; font-size: 12px; font-weight: 800; color: #64748b; background: #f1f5f9; padding: 6px 10px; border-radius: 6px; border: 1px solid #e2e8f0;}

        .check-box { width: 28px; height: 28px; border: 2px solid #94a3b8; border-radius: 6px; margin: 0 auto; }

        /* TANDA TANGAN */
        .signature-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 30px;
            margin-top: 60px;
            text-align: center;
        }
        .sig-box { display: flex; flex-direction: column; align-items: center;}
        .sig-title { font-size: 14px; font-weight: 800; color: #475569; margin-bottom: 90px;}
        .sig-line { width: 85%; border-bottom: 2px solid #0f172a; margin-bottom: 10px;}
        .sig-name { font-size: 13px; font-weight: 700; color: #64748b;}

        /* TOMBOL CETAK & MEDIA PRINT */
        .action-bar { text-align: center; margin: 40px 0; }
        .btn-print { background: linear-gradient(135deg, #10b981, #059669); color: white; border: none; padding: 18px 35px; font-size: 18px; font-weight: 900; border-radius: 16px; cursor: pointer; display: inline-flex; align-items: center; gap: 12px; box-shadow: 0 10px 25px rgba(16, 185, 129, 0.4); transition: 0.3s;}
        .btn-print:hover { transform: translateY(-4px); box-shadow: 0 15px 35px rgba(16, 185, 129, 0.5);}

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
            <i class="ph-bold ph-printer"></i> Cetak Surat Jalan (A4)
        </button>
        <p style="font-size: 14px; font-weight: 600; color: #64748b; margin-top: 15px;">Tekan tombol di atas atau Ctrl+P. Format sudah dikalibrasi untuk kertas A4.</p>
    </div>

    <div class="a4-container">
        
        <div class="doc-header">
            <div class="brand-info">
                <h1><i class="ph-fill ph-factory" style="color: #10b981;"></i> NORIC EXHAUST</h1>
                <p>Kawasan Industri Pabrik Knalpot Purbalingga<br>Telp: (0281) 8899-7766 | Email: logistik@noric.com</p>
            </div>
            <div class="doc-type">
                <h2>SURAT JALAN</h2>
                <p><?= esc($so['so_number']) ?></p>
            </div>
        </div>

        <div class="shipping-info">
            <div class="info-box highlight">
                <h4><i class="ph-bold ph-truck"></i> Informasi Pengiriman</h4>
                <div class="customer-detail">
                    <b>Tanggal Kirim:</b> <?= date('d F Y', strtotime($so['order_date'])) ?><br>
                    <div style="margin-top: 12px;"><b>Plat Nomor Armada:</b> <span style="display:inline-block; border-bottom: 1px dashed #94a3b8; width: 150px; margin-bottom: -4px;"></span></div>
                    <div style="margin-top: 12px;"><b>Nama Supir:</b> <span style="display:inline-block; border-bottom: 1px dashed #94a3b8; width: 200px; margin-bottom: -4px;"></span></div>
                </div>
            </div>
            <div class="info-box">
                <h4><i class="ph-bold ph-buildings"></i> Tujuan Pengiriman (Penerima)</h4>
                <div class="customer-name"><?= esc($so['company_name']) ?></div>
                <div class="customer-detail">
                    <b>UP (Kontak):</b> <?= esc($so['contact_name'] ?? '-') ?> <br>
                    <b>Telp/WA:</b> <?= esc($so['phone'] ?? '-') ?> <br>
                    <span style="display: block; margin-top: 12px; border-top: 1px dashed #cbd5e1; padding-top: 12px; line-height: 1.5;">
                        <?= esc($so['address'] ?? 'Alamat tidak terdata di sistem') ?>
                    </span>
                </div>
            </div>
        </div>

        <div style="font-size: 14px; font-weight: 800; color: #0f172a; margin-bottom: 15px; display: flex; align-items: center; gap: 8px;">
            <i class="ph-bold ph-package" style="color: #10b981; font-size: 18px;"></i> Daftar Barang Diserahkan:
        </div>

        <table>
            <thead>
                <tr>
                    <th style="width: 5%; text-align: center;">No.</th>
                    <th style="width: 25%;">Kode SKU</th>
                    <th style="width: 40%;">Deskripsi Produk</th>
                    <th style="width: 15%; text-align: center;">Kuantitas</th>
                    <th style="width: 15%; text-align: center;">Cek Fisik</th>
                </tr>
            </thead>
            <tbody>
                <?php $no = 1; foreach($items as $item): ?>
                <tr>
                    <td style="text-align: center; color: #64748b; font-weight: 800;"><?= $no++ ?>.</td>
                    <td><span class="sku-code"><?= esc($item['fg_sku']) ?></span></td>
                    <td style="font-weight: 900; color: #0f172a;"><?= esc($item['item_name'] ?? 'Produk Jadi (Knalpot)') ?></td>
                    <td style="text-align: center; font-family: 'Space Mono', monospace; font-size: 18px; font-weight: 900; color: #10b981;">
                        <?= $item['qty'] ?> <span style="font-size: 12px; color: #64748b; font-family: 'Plus Jakarta Sans', sans-serif;">Pcs</span>
                    </td>
                    <td><div class="check-box"></div></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 15px 20px; font-size: 13px; color: #475569; margin-bottom: 30px; line-height: 1.6; display: flex; gap: 15px; align-items: flex-start;">
            <i class="ph-fill ph-info" style="color: #0ea5e9; font-size: 24px;"></i>
            <div>
                <b>Catatan Dokumen:</b> Surat jalan ini adalah bukti sah serah terima barang fisik dari Pabrik ke pihak Pembeli/Ekspedisi. 
                Mohon pihak penerima mengecek kesesuaian fisik produk (jumlah dan kondisi) dengan dokumen ini sebelum menandatangani.
            </div>
        </div>

        <div class="signature-grid">
            <div class="sig-box">
                <div class="sig-title">Penerima Barang,</div>
                <div class="sig-line"></div>
                <div class="sig-name">(Tanda Tangan & Cap Toko)</div>
            </div>
            <div class="sig-box">
                <div class="sig-title">Supir / Ekspedisi,</div>
                <div class="sig-line"></div>
                <div class="sig-name">(Nama Jelas & TTD)</div>
            </div>
            <div class="sig-box">
                <div class="sig-title">Hormat Kami (Gudang),</div>
                <div class="sig-line"></div>
                <div class="sig-name">PT. Noric Manufaktur</div>
            </div>
        </div>

    </div>

</body>
</html>