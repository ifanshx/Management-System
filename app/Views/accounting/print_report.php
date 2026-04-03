<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title><?= esc($title) ?></title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: "Segoe UI", Arial, sans-serif;
            color: #0f172a;
            margin: 0;
            padding: 30px;
            background: #f8fafc;
        }

        .report-container {
            max-width: 1100px;
            margin: 0 auto;
            background: #fff;
            border-radius: 24px;
            padding: 36px;
            box-shadow: 0 15px 40px -20px rgba(0,0,0,.18);
        }

        .report-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 20px;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 24px;
            margin-bottom: 28px;
        }

        .brand h1 {
            margin: 0 0 6px 0;
            font-size: 30px;
            font-weight: 900;
            color: #0f172a;
            letter-spacing: -.8px;
        }

        .brand p {
            margin: 0;
            color: #64748b;
            font-size: 14px;
            font-weight: 600;
            line-height: 1.6;
        }

        .report-meta {
            text-align: right;
        }

        .report-meta .label {
            font-size: 11px;
            text-transform: uppercase;
            font-weight: 900;
            color: #64748b;
            margin-bottom: 6px;
            letter-spacing: .5px;
        }

        .report-meta .value {
            font-size: 15px;
            font-weight: 800;
            color: #0f172a;
        }

        .hero-box {
            background: linear-gradient(135deg, #0f172a, #1e293b, #1d4ed8);
            color: #fff;
            border-radius: 24px;
            padding: 28px;
            margin-bottom: 26px;
        }

        .hero-box h2 {
            margin: 0 0 8px 0;
            font-size: 28px;
            font-weight: 900;
            letter-spacing: -.8px;
        }

        .hero-box p {
            margin: 0;
            color: rgba(255,255,255,.82);
            font-size: 14px;
            font-weight: 600;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(4,1fr);
            gap: 18px;
            margin-bottom: 28px;
        }

        .summary-card {
            border: 1px solid #e2e8f0;
            border-radius: 20px;
            padding: 22px;
            background: #fff;
        }

        .summary-label {
            font-size: 11px;
            text-transform: uppercase;
            font-weight: 900;
            color: #64748b;
            margin-bottom: 8px;
            letter-spacing: .5px;
        }

        .summary-value {
            font-size: 26px;
            font-weight: 900;
            color: #0f172a;
            line-height: 1;
        }

        .summary-note {
            margin-top: 10px;
            font-size: 12px;
            color: #64748b;
            font-weight: 600;
        }

        .section-card {
            border: 1px solid #e2e8f0;
            border-radius: 22px;
            padding: 24px;
            margin-bottom: 24px;
            background: #fff;
        }

        .section-title {
            font-size: 18px;
            font-weight: 900;
            color: #0f172a;
            margin-bottom: 18px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            text-align: left;
            padding: 14px 16px;
            font-size: 11px;
            font-weight: 900;
            text-transform: uppercase;
            color: #64748b;
            border-bottom: 1px solid #e2e8f0;
            background: #f8fafc;
            letter-spacing: .5px;
        }

        td {
            padding: 16px;
            border-bottom: 1px dashed #e2e8f0;
            font-size: 14px;
            font-weight: 700;
            color: #0f172a;
        }

        .amount {
            font-family: monospace;
            font-weight: 900;
        }

        .positive { color: #10b981; }
        .negative { color: #ef4444; }
        .blue { color: #2563eb; }
        .purple { color: #8b5cf6; }

        .footer-note {
            margin-top: 30px;
            text-align: center;
            font-size: 12px;
            color: #94a3b8;
            font-weight: 600;
        }

        @media print {
            body {
                background: #fff;
                padding: 0;
            }
            .report-container {
                box-shadow: none;
                border-radius: 0;
                max-width: 100%;
                padding: 0;
            }
        }
    </style>
</head>
<body onload="window.print()">

<?php
$aset = 0; $liabiliti = 0; $ekuiti = 0; $pendapatanTotal = 0; $bebanTotal = 0;

foreach($summary as $row) {
    if($row['account_type'] == 'ASET') $aset = $row['total_balance'];
    if($row['account_type'] == 'LIABILITI') $liabiliti = $row['total_balance'];
    if($row['account_type'] == 'EKUITI') $ekuiti = $row['total_balance'];
    if($row['account_type'] == 'PENDAPATAN') $pendapatanTotal = $row['total_balance'];
    if($row['account_type'] == 'PERBELANJAAN') $bebanTotal = $row['total_balance'];
}

$labaKotor = $pendapatan - $hpp;
$labaBersih = $labaKotor - $beban_ops;
?>

<div class="report-container">
    <div class="report-header">
        <div class="brand">
            <h1><?= esc($company['company_name'] ?? 'Perusahaan') ?></h1>
            <p>
                <?= esc($company['address'] ?? '-') ?><br>
                Telp: <?= esc($company['phone'] ?? '-') ?>
            </p>
        </div>

        <div class="report-meta">
            <div class="label">Tanggal Cetak</div>
            <div class="value"><?= date('d F Y H:i') ?></div>
        </div>
    </div>

    <div class="hero-box">
        <h2>Laporan Keuangan Ringkas</h2>
        <p>Ringkasan kondisi keuangan perusahaan berdasarkan jurnal akuntansi yang telah diposting.</p>
    </div>

    <div class="summary-grid">
        <div class="summary-card">
            <div class="summary-label">Aset</div>
            <div class="summary-value blue">Rp <?= number_format($aset, 0, ',', '.') ?></div>
            <div class="summary-note">Total aset aktif perusahaan</div>
        </div>

        <div class="summary-card">
            <div class="summary-label">Liabilitas</div>
            <div class="summary-value">Rp <?= number_format($liabiliti, 0, ',', '.') ?></div>
            <div class="summary-note">Total kewajiban perusahaan</div>
        </div>

        <div class="summary-card">
            <div class="summary-label">Ekuitas</div>
            <div class="summary-value positive">Rp <?= number_format($ekuiti, 0, ',', '.') ?></div>
            <div class="summary-note">Kepemilikan bersih usaha</div>
        </div>

        <div class="summary-card">
            <div class="summary-label">Laba Bersih</div>
            <div class="summary-value <?= $labaBersih >= 0 ? 'positive' : 'negative' ?>">
                Rp <?= number_format($labaBersih, 0, ',', '.') ?>
            </div>
            <div class="summary-note">Profit setelah beban operasional</div>
        </div>
    </div>

    <div class="section-card">
        <div class="section-title">Laporan Laba Rugi</div>
        <table>
            <tbody>
                <tr>
                    <td>Pendapatan</td>
                    <td class="amount positive">Rp <?= number_format($pendapatan, 0, ',', '.') ?></td>
                </tr>
                <tr>
                    <td>Harga Pokok Penjualan (HPP)</td>
                    <td class="amount negative">Rp <?= number_format($hpp, 0, ',', '.') ?></td>
                </tr>
                <tr>
                    <td><strong>Laba Kotor</strong></td>
                    <td class="amount purple"><strong>Rp <?= number_format($labaKotor, 0, ',', '.') ?></strong></td>
                </tr>
                <tr>
                    <td>Beban Operasional</td>
                    <td class="amount negative">Rp <?= number_format($beban_ops, 0, ',', '.') ?></td>
                </tr>
                <tr>
                    <td><strong>Laba Bersih</strong></td>
                    <td class="amount <?= $labaBersih >= 0 ? 'positive' : 'negative' ?>">
                        <strong>Rp <?= number_format($labaBersih, 0, ',', '.') ?></strong>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="section-card">
        <div class="section-title">Ringkasan Neraca</div>
        <table>
            <thead>
                <tr>
                    <th>Kategori</th>
                    <th>Nilai</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Total Aset</td>
                    <td class="amount blue">Rp <?= number_format($aset, 0, ',', '.') ?></td>
                </tr>
                <tr>
                    <td>Total Liabilitas</td>
                    <td class="amount">Rp <?= number_format($liabiliti, 0, ',', '.') ?></td>
                </tr>
                <tr>
                    <td>Total Ekuitas</td>
                    <td class="amount positive">Rp <?= number_format($ekuiti, 0, ',', '.') ?></td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="footer-note">
        Dokumen ini dibuat otomatis oleh sistem akuntansi internal perusahaan.
    </div>
</div>

</body>
</html>