<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Setoran Kerja Harian | <?= esc($company['app_name'] ?? 'Noric ERP') ?></title>

    <?php if (isset($company['logo_path']) && !empty($company['logo_path'])): ?>
        <link rel="icon" type="image/png" href="<?= base_url('uploads/logo/' . esc($company['logo_path'])) ?>">
    <?php endif; ?>

    <script src="https://unpkg.com/@phosphor-icons/web"></script>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700;900&display=swap');

        * { box-sizing: border-box; margin: 0; padding: 0; }
        
        body {
            font-family: 'Inter', Arial, sans-serif;
            font-size: 11px; color: #0f172a; background: #e2e8f0;
            padding: 20px; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important;
        }

        .container {
            width: 100%; max-width: 820px; margin: 0 auto; background: #fff;
            padding: 25px 30px; box-shadow: 0 10px 28px rgba(15, 23, 42, 0.10); border-radius: 14px;
        }

        .action-bar { text-align: center; margin-bottom: 20px; }
        .btn-print { background: #10b981; color: #fff; border: none; padding: 12px 24px; font-size: 14px; font-weight: 800; border-radius: 10px; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 8px 20px rgba(16, 185, 129, 0.3); }
        .print-guide { display: inline-block; background: #fff; padding: 10px 16px; border-radius: 10px; border: 1px dashed #94a3b8; font-size: 11px; color: #334155; margin-top: 12px; }

        .header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #0f172a; padding-bottom: 12px; margin-bottom: 15px; }
        .header-left h1 { font-size: 20px; font-weight: 900; text-transform: uppercase; margin-bottom: 4px; letter-spacing: -0.5px;}
        .header-left p { font-size: 10px; font-weight: 700; color: #475569; }
        .header-right { text-align: right; }
        .doc-code { font-family: monospace; font-size: 13px; font-weight: 900; border: 1px solid #0f172a; padding: 4px 10px; border-radius: 6px; display: inline-block; background: #f8fafc;}

        .identitas-box { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px; }
        .id-row { display: flex; align-items: flex-end; font-size: 11px; font-weight: 700; margin-bottom: 8px; }
        .id-label { width: 120px; color: #334155; }
        .id-line { flex: 1; border-bottom: 1px dashed #0f172a; height: 16px; margin-left: 5px; }

        .table-wrap { border: 1.5px solid #0f172a; margin-bottom: 15px; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #0f172a; color: #fff; padding: 8px 6px; font-size: 9.5px; font-weight: 800; text-transform: uppercase; text-align: center; border-right: 1px solid rgba(255,255,255,0.2); }
        th:last-child { border-right: none; }
        
        /* Tinggi baris di-set 23px agar 35 baris tetap muat di kertas A4 */
        td { padding: 4px 8px; border-bottom: 1px solid #cbd5e1; border-right: 1px solid #cbd5e1; height: 23px; } 
        td:last-child { border-right: none; }
        
        .td-center { text-align: center; }
        .hari-text { font-weight: 900; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; color: #0f172a; }

        /* Garis pembatas tebal antar hari */
        .tr-hari td { border-top: 2px solid #0f172a; }
        .tr-sub td { border-bottom: 2px solid #0f172a; }

        .rules-box { font-size: 9px; color: #334155; line-height: 1.5; border: 1px dashed #94a3b8; padding: 10px; border-radius: 8px; margin-bottom: 15px; background: #f8fafc;}
        .rules-box strong { color: #0f172a; font-size: 10px;}

        .signature-section { display: flex; justify-content: space-between; text-align: center; margin-top: 10px;}
        .sig-box { width: 28%; }
        .sig-box p { font-size: 10px; font-weight: 800; margin-bottom: 45px; color: #0f172a;}
        .sig-line { border-bottom: 1px solid #0f172a; margin: 0 auto 5px; width: 90%; }
        .sig-name { font-size: 9px; font-weight: 700; color: #475569; }

        @page { size: A4 portrait; margin: 8mm; }
        @media print {
            body { padding: 0; background: #fff; }
            .container { box-shadow: none; border-radius: 0; padding: 0; max-width: 100%; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>

    <div class="action-bar no-print">
        <button onclick="window.print()" class="btn-print"><i class="ph-bold ph-printer"></i> Cetak Blangko Mingguan A4</button>
        <br>
        <div class="print-guide">Bagikan kertas ini ke Karyawan / Tukang setiap hari Senin pagi.</div>
    </div>

    <div class="container">
        <div class="header">
            <div class="header-left">
                <h1>LEMBAR SETORAN KERJA HARIAN</h1>
                <p><?= esc($company['company_name'] ?? 'PT. NORIC EXHAUST') ?> - DIVISI PRODUKSI</p>
            </div>
            <div class="header-right">
                <div class="doc-code">FORM-MFG-003</div>
            </div>
        </div>

        <div class="identitas-box">
            <div>
                <div class="id-row"><span class="id-label">NAMA KARYAWAN</span><span class="id-line"></span></div>
                <div class="id-row"><span class="id-label">BAGIAN / POSISI</span><span class="id-line"></span></div>
            </div>
            <div>
                <div class="id-row"><span class="id-label">PERIODE MINGGU KE</span><span class="id-line"></span></div>
                <div class="id-row"><span class="id-label">BULAN / TAHUN</span><span class="id-line"></span></div>
            </div>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th width="4%">No</th>
                        <th width="12%">Hari</th>
                        <th width="36%">Tipe Barang & Tahapan Operasi</th>
                        <th width="20%">Lokasi / Tujuan Barang</th>
                        <th width="10%">Qty (Pcs)</th>
                        <th width="18%">Paraf Mandor</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $hari = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
                    $no = 1;
                    foreach($hari as $h): 
                    ?>
                    <tr class="tr-hari">
                        <td rowspan="5" class="td-center" style="font-weight:900; font-size:12px; border-bottom: 2px solid #0f172a; border-top: 2px solid #0f172a;"><?= $no++ ?></td>
                        <td rowspan="5" class="td-center hari-text" style="border-bottom: 2px solid #0f172a; border-top: 2px solid #0f172a;"><?= $h ?></td>
                        <td style="border-top: 2px solid #0f172a;"></td>
                        <td style="border-top: 2px solid #0f172a;"></td>
                        <td style="border-top: 2px solid #0f172a;"></td>
                        <td style="border-top: 2px solid #0f172a;"></td>
                    </tr>
                    
                    <?php for($i=0; $i<3; $i++): ?>
                    <tr>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                    <?php endfor; ?>

                    <tr class="tr-sub">
                        <td style="border-bottom: 2px solid #0f172a;"></td>
                        <td style="border-bottom: 2px solid #0f172a;"></td>
                        <td style="border-bottom: 2px solid #0f172a;"></td>
                        <td style="border-bottom: 2px solid #0f172a;"></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="rules-box">
            <strong>PERATURAN PRODUKSI PABRIK:</strong>
            <ol style="margin-left: 15px; margin-top: 5px;">
                <li>Tulislah hasil kerja dengan <b>Jujur, Jelas, dan Rapi</b> agar tidak terjadi kesalahan perhitungan gaji.</li>
                <li>Tuliskan dengan jelas lokasi tujuan barang (Misal: <i>"Gudang Tengah"</i>, <i>"Lanjut Las Cacing"</i>, <i>"Meja Pak Joko"</i>).</li>
                <li>Setiap barang yang disetor <b>wajib diperiksa dan diparaf oleh Mandor / QC</b> pada hari yang sama. Setoran tanpa paraf tidak akan dihitung.</li>
                <li>Jika terjadi produk cacat (reject) karena kelalaian, harap segera laporkan ke Mandor.</li>
                <li>Serahkan lembaran ini ke Admin Produksi paling lambat akhir pekan untuk direkapitulasi dan dibayarkan gajinya.</li>
            </ol>
        </div>

        <div class="signature-section">
            <div class="sig-box">
                <p>Tukang / Karyawan</p>
                <div class="sig-line"></div>
                <div class="sig-name">( Nama Jelas & Tanda Tangan )</div>
            </div>
            <div class="sig-box">
                <p>Divalidasi Oleh (Mandor/QC)</p>
                <div class="sig-line"></div>
                <div class="sig-name">( Nama Jelas & Tanda Tangan )</div>
            </div>
            <div class="sig-box">
                <p>Penerima / Admin Produksi</p>
                <div class="sig-line"></div>
                <div class="sig-name">( Direkap Untuk Penggajian )</div>
            </div>
        </div>

    </div>

</body>
</html>