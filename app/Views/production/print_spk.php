<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title) ?></title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800;900&family=Space+Mono:wght@700&display=swap');

        :root {
            --black: #000000;
            --gray-dark: #475569;
            --gray-light: #cbd5e1;
            --border-heavy: 2px solid var(--black);
            --border-light: 1px solid var(--black);
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: #e2e8f0;
            color: var(--black);
            margin: 0;
            padding: 20px;
            font-size: 12px;
        }

        .a4-paper {
            background: #ffffff;
            width: 210mm;
            min-height: 297mm;
            height: auto; 
            margin: 0 auto;
            padding: 12mm 15mm;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            box-sizing: border-box;
            position: relative;
        }

        /* --- KOP SURAT --- */
        .doc-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: var(--border-heavy);
            padding-bottom: 12px;
            margin-bottom: 20px;
        }
        
        .company-info { display: flex; align-items: center; gap: 15px; }
        .company-logo { width: 50px; height: 50px; object-fit: contain; }
        
        .company-info .logo {
            margin: 0;
            font-size: 24px;
            font-weight: 900;
            letter-spacing: -1px;
            text-transform: uppercase;
        }
        .company-info .sub-text {
            margin: 4px 0 0 0;
            font-size: 10px;
            color: var(--gray-dark);
            font-weight: 600;
            line-height: 1.4;
        }
        
        .doc-title-area {
            text-align: right;
            display: flex;
            flex-direction: column;
            align-items: flex-end;
        }
        .doc-title-area h2 {
            margin: 0 0 8px 0;
            font-size: 20px;
            font-weight: 900;
            letter-spacing: -0.5px;
            text-transform: uppercase;
            background: var(--black);
            color: #fff;
            padding: 6px 12px;
        }
        .barcode-container img {
            height: 35px;
            display: block;
            margin-bottom: 4px;
        }
        .spk-num {
            font-family: 'Space Mono', monospace;
            font-size: 14px;
            font-weight: 900;
            letter-spacing: 1px;
        }

        /* --- KOTAK INFO --- */
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            border: var(--border-heavy);
            margin-bottom: 25px;
            page-break-inside: avoid;
        }
        .info-col { padding: 0; }
        .info-col:first-child { border-right: var(--border-heavy); }
        .info-row { display: flex; border-bottom: var(--border-light); align-items: stretch; }
        .info-row:last-child { border-bottom: none; }
        .info-label {
            width: 130px;
            background: #f8fafc;
            padding: 8px 12px;
            font-size: 10px;
            font-weight: 800;
            color: var(--gray-dark);
            text-transform: uppercase;
            border-right: var(--border-light);
            display: flex;
            align-items: center;
        }
        .info-val { padding: 8px 12px; font-weight: 900; font-size: 12px; flex: 1; display: flex; align-items: center; }
        .val-target { font-family: 'Space Mono', monospace; font-size: 16px; }

        /* --- TABEL --- */
        .section-title {
            font-size: 14px;
            font-weight: 900;
            text-transform: uppercase;
            margin-top: 25px;
            margin-bottom: 10px;
            letter-spacing: 0.5px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .section-title::before {
            content: '';
            display: inline-block;
            width: 12px;
            height: 12px;
            background: var(--black);
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            page-break-inside: auto;
        }
        thead { display: table-header-group; }
        tr { page-break-inside: avoid; page-break-after: auto; }

        th, td { border: var(--border-light); padding: 8px 10px; vertical-align: middle; }
        th { background: #f1f5f9; text-transform: uppercase; font-size: 10px; font-weight: 900; letter-spacing: 0.5px; text-align: center; border-top: var(--border-heavy); border-bottom: var(--border-heavy); }
        
        td.sku { font-family: 'Space Mono', monospace; font-weight: 900; font-size: 11px; text-align: center;}
        td.qty { font-family: 'Space Mono', monospace; font-weight: 900; text-align: center; font-size: 13px; }
        
        .box-check { width: 16px; height: 16px; border: 2px solid var(--black); margin: 0 auto; background: #fff; }

        /* --- CATATAN --- */
        .warning-box {
            border: var(--border-heavy);
            padding: 10px 15px;
            font-size: 10px;
            font-weight: 600;
            line-height: 1.5;
            margin-top: 25px;
            margin-bottom: 30px;
            background: #fff;
            page-break-inside: avoid;
        }
        .warning-box strong { font-size: 11px; text-transform: uppercase; }

        /* --- TANDA TANGAN --- */
        .signature-area { display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px; text-align: center; margin-top: 30px; page-break-inside: avoid; }
        .sig-box { display: flex; flex-direction: column; align-items: center; }
        .sig-title { font-size: 10px; font-weight: 800; text-transform: uppercase; margin-bottom: 60px; color: var(--gray-dark); }
        .sig-line { width: 90%; border-bottom: var(--border-light); margin-bottom: 4px; }
        .sig-name { font-weight: 800; font-size: 11px; text-transform: uppercase; }

        /* --- FOOTER --- */
        .doc-control { margin-top: 20px; padding-top: 10px; border-top: 1px solid var(--gray-light); display: flex; justify-content: space-between; font-size: 9px; color: var(--gray-dark); font-weight: 800; text-transform: uppercase; }

        .btn-print { position: fixed; top: 20px; right: 30px; z-index: 1000; display: flex; justify-content: center; align-items: center; gap: 8px; background: #2563eb; color: #fff; padding: 12px 24px; border-radius: 100px; font-weight: 900; font-size: 14px; cursor: pointer; border: none; box-shadow: 0 10px 25px rgba(37, 99, 235, 0.4); transition: 0.3s; }
        .btn-print:hover { transform: translateY(-3px); box-shadow: 0 15px 30px rgba(37, 99, 235, 0.5); }

        @media print {
            body { background: #fff; padding: 0; }
            .a4-paper { margin: 0; box-shadow: none; width: 100%; border: none; padding: 10mm; }
            .btn-print { display: none !important; }
            @page { margin: 0; size: A4 portrait; }
        }
    </style>
</head>
<body>

    <button onclick="window.print()" class="btn-print">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 256 256"><path d="M224,96V200a8,8,0,0,1-8,8H40a8,8,0,0,1-8-8V96a8,8,0,0,1,8-8H72V40a8,8,0,0,1,8-8H176a8,8,0,0,1,8,8V88h32A8,8,0,0,1,224,96ZM88,48V88h80V48ZM208,104H48V192H208Zm-32,48H80a8,8,0,0,0,0,16h96a8,8,0,0,0,0-16Z"></path></svg> Cetak SPK
    </button>

    <div class="a4-paper">
        
        <div class="doc-header">
            <div class="company-info">
                <?php if(!empty($company['logo_path']) && $company['logo_path'] !== 'default-logo.png'): ?>
                    <img src="<?= base_url('uploads/logo/' . esc($company['logo_path'])) ?>" alt="Logo" class="company-logo">
                <?php endif; ?>
                <div>
                    <h1 class="logo"><?= esc($company['company_name'] ?? 'NORIC EXHAUST') ?></h1>
                    <p class="sub-text">
                        <b>DIVISI PRODUKSI & MANUFAKTUR</b><br>
                        Fasilitas Produksi Pusat<br>
                        Sistem Enterprise Resource Planning (ERP)
                    </p>
                </div>
            </div>
            <div class="doc-title-area">
                <h2>Surat Perintah Kerja</h2>
                <div class="barcode-container">
                    <img src="https://bwipjs-api.metafloor.com/?bcid=code128&text=<?= esc($spk['spk_number']) ?>&scale=2&height=8&includetext=false" alt="Barcode SPK">
                </div>
                <div class="spk-num"><?= esc($spk['spk_number']) ?></div>
            </div>
        </div>

        <div class="info-grid">
            <div class="info-col">
                <div class="info-row">
                    <div class="info-label">Tanggal Rilis</div>
                    <div class="info-val"><?= date('d F Y', strtotime($spk['start_date'])) ?></div>
                </div>
                <div class="info-row">
                    <div class="info-label">ID Resep (BOM)</div>
                    <div class="info-val"><?= esc($bom['recipe_name']) ?></div>
                </div>
                <div class="info-row">
                    <div class="info-label">Status SPK</div>
                    <div class="info-val"><?= ($spk['status'] === 'COMPLETED') ? 'SELESAI / CLOSED' : 'PROSES PENGERJAAN' ?></div>
                </div>
            </div>
            <div class="info-col">
                <div class="info-row">
                    <div class="info-label">Target Produk</div>
                    <div class="info-val" style="color: #000; font-size: 11px;">[<?= esc($targetProduct['sku']) ?>] <?= esc($targetProduct['item_name']) ?></div>
                </div>
                <div class="info-row">
                    <div class="info-label">Kuantitas Target</div>
                    <div class="info-val val-target"><?= $spk['planned_qty'] ?> Pcs</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Tgl. Penyelesaian</div>
                    <div class="info-val"><?= ($spk['status'] === 'COMPLETED') ? date('d/m/Y H:i', strtotime($spk['completed_at'])) : '- (Menunggu Setoran)' ?></div>
                </div>
            </div>
        </div>

        <div class="section-title">Kebutuhan Material (BOM)</div>
        <table>
            <thead>
                <tr>
                    <th style="width: 5%;">No</th>
                    <th style="width: 15%;">SKU Barang</th>
                    <th style="width: 35%; text-align: left;">Deskripsi Material / Barang Mentah</th>
                    <th style="width: 15%;">Rasio / Pcs</th>
                    <th style="width: 18%; background: #e2e8f0; color: #000;">Total Disiapkan</th>
                    <th style="width: 12%;">Gudang (Cek)</th>
                </tr>
            </thead>
            <tbody>
                <?php $no = 1; foreach($items as $item): ?>
                <tr>
                    <td style="text-align: center; font-weight: 900;"><?= $no++ ?></td>
                    <td class="sku"><?= esc($item['rm_sku']) ?></td>
                    <td style="font-weight: 800; font-size: 11px; text-transform: uppercase;"><?= esc($item['name']) ?></td>
                    <td class="qty" style="color: var(--gray-dark); font-weight: 600; font-size: 11px;"><?= floatval($item['qty_required']) ?> <?= esc($item['unit']) ?></td>
                    <td class="qty" style="background: #f8fafc; font-size: 14px;"><?= floatval($item['total_needed']) ?> <?= esc($item['unit']) ?></td>
                    <td><div class="box-check"></div></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="section-title">Jalur Perakitan / Tahapan Borongan (Routing)</div>
        <table>
            <thead>
                <tr>
                    <th style="width: 5%;">Tahap</th>
                    <th style="width: 35%; text-align: left;">Instruksi Kerja (Operasi)</th>
                    <th style="width: 15%;">Tipe Pekerja</th>
                    <th style="width: 10%;">Target Qty</th>
                    <th style="width: 35%;">Nama Pelaksana / Paraf</th>
                </tr>
            </thead>
            <tbody>
                <?php if(isset($operations) && !empty($operations)): ?>
                    <?php foreach($operations as $op): ?>
                    <tr>
                        <td style="text-align: center; font-weight: 900; font-size: 14px;"><?= $op['step_order'] ?></td>
                        <td style="font-weight: 800; font-size: 12px; text-transform: uppercase;">
                            <?= esc($op['operation_name']) ?>
                            <?php if($op['is_final_step'] == 1): ?>
                                <br><span style="font-size:9px; color:var(--gray-dark); font-weight: 600;">*Tahap Final (Selesai -> Masuk Gudang)</span>
                            <?php endif; ?>
                        </td>
                        <td style="text-align: center; font-weight: 800; font-size: 10px;">
                            <?php if((float)$op['wage_per_piece'] > 0): ?>
                                <span style="background: #fef3c7; color: #b45309; padding: 2px 6px; border-radius: 4px; border: 1px solid #fde68a;">BORONGAN</span>
                            <?php else: ?>
                                <span style="background: #dbeafe; color: #1d4ed8; padding: 2px 6px; border-radius: 4px; border: 1px solid #bfdbfe;">TETAP</span>
                            <?php endif; ?>
                        </td>
                        <td class="qty" style="font-size: 14px;"><?= $spk['planned_qty'] ?> Pcs</td>
                        <td></td> 
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" style="text-align: center; font-style: italic; color: #94a3b8;">Tahapan operasi tidak ditemukan.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <div class="warning-box">
            <strong>INSTRUKSI KERJA & K3:</strong><br>
            1. Pihak Gudang wajib memverifikasi kuantitas material sebelum diserahkan ke area bengkel / pabrikasi.<br>
            2. Tim Produksi (Borongan & Tetap) wajib mengerjakan sesuai urutan tahapan operasi di atas.<br>
            3. <b>PERHATIAN:</b> Tahapan berstatus "TETAP" diselesaikan oleh Karyawan Tetap (Gaji Harian/Bulanan). Tahapan berstatus "BORONGAN" diselesaikan oleh Karyawan Borongan dengan upah per Pcs.<br>
            4. Setiap tahapan yang selesai wajib diparaf oleh pelaksana untuk proses klaim upah ke Mandor.<br>
            5. Laporkan ke Mandor jika terdapat material cacat (Scrap) untuk penyesuaian stok di sistem ERP.
        </div>

        <div class="signature-area">
            <div class="sig-box">
                <div class="sig-title">Disetujui Oleh (PPIC)</div>
                <div class="sig-line"></div>
                <div class="sig-name">Admin Produksi</div>
            </div>
            <div class="sig-box">
                <div class="sig-title">Diserahkan Oleh</div>
                <div class="sig-line"></div>
                <div class="sig-name">Kepala Gudang</div>
            </div>
            <div class="sig-box">
                <div class="sig-title">Dilaksanakan Oleh</div>
                <div class="sig-line"></div>
                <div class="sig-name">Tim Produksi</div>
            </div>
            <div class="sig-box">
                <div class="sig-title">Diperiksa (QC & Mandor)</div>
                <div class="sig-line"></div>
                <div class="sig-name">Mandor Bengkel</div>
            </div>
        </div>

        <div class="doc-control">
            <div>Form No: MFG-SOP-002</div>
            <div>Generated by Noric ERP System - <?= date('d M Y, H:i') ?></div>
            <div>Rev: 03 (Routing & Labor Type Enabled)</div>
        </div>

    </div>

</body>
</html>