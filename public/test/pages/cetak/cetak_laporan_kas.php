<?php 
require_once '../../config/database.php';
// Tidak perlu session_start() jika hanya untuk cetak public atau bisa ditambahkan validasi
session_start();

if (!isset($_SESSION['status']) || $_SESSION['status'] != "login") {
    echo "<script>window.close();</script>";
    exit;
}

date_default_timezone_set('Asia/Jakarta');

// =========================================================================
// 1. DATA PROCESSING (SAMA DENGAN LAPORAN KAS UTAMA)
// =========================================================================

// --- 1. FILTER DATA ---
$default_awal  = date('Y-m-d', strtotime('monday this week'));
$default_akhir = date('Y-m-d', strtotime('sunday this week'));
$tgl_awal  = isset($_GET['tgl_awal']) ? $_GET['tgl_awal'] : $default_awal;
$tgl_akhir = isset($_GET['tgl_akhir']) ? $_GET['tgl_akhir'] : $default_akhir;

// --- 2. HITUNG SALDO AWAL ---
$q_awal = mysqli_query($conn, "SELECT 
    SUM(CASE WHEN jenis='Masuk' THEN nominal ELSE 0 END) as tot_masuk,
    SUM(CASE WHEN jenis='Keluar' THEN nominal ELSE 0 END) as tot_keluar,
    (SUM(CASE WHEN jenis='Masuk' AND metode='Cash' THEN nominal ELSE 0 END) - 
     SUM(CASE WHEN jenis='Keluar' AND metode='Cash' THEN nominal ELSE 0 END)) as awal_cash,
    (SUM(CASE WHEN jenis='Masuk' AND metode='ATM' THEN nominal ELSE 0 END) - 
     SUM(CASE WHEN jenis='Keluar' AND metode='ATM' THEN nominal ELSE 0 END)) as awal_atm
    FROM transaksi_kas WHERE tanggal < '$tgl_awal'");

$d_awal = mysqli_fetch_assoc($q_awal);
$saldo_awal_global = $d_awal['tot_masuk'] - $d_awal['tot_keluar'];
$saldo_awal_cash   = $d_awal['awal_cash'] ?? 0;
$saldo_awal_atm    = $d_awal['awal_atm'] ?? 0;

// --- 3. AMBIL DATA TRANSAKSI ---
$sql = "SELECT * FROM transaksi_kas 
        WHERE tanggal BETWEEN '$tgl_awal' AND '$tgl_akhir' 
        ORDER BY tanggal ASC, created_at ASC";
$q_kas = mysqli_query($conn, $sql);

// --- 4. CALCULATE LOOP ---
$total_masuk = 0; $total_keluar = 0;
$mutasi_cash_masuk = 0; $mutasi_cash_keluar = 0;
$mutasi_atm_masuk = 0; $mutasi_atm_keluar = 0;

$list_data = [];
$running_saldo = $saldo_awal_global;

while($d = mysqli_fetch_assoc($q_kas)) {
    if($d['jenis'] == 'Masuk') {
        $total_masuk += $d['nominal'];
        $running_saldo += $d['nominal'];
        if($d['metode'] == 'Cash') $mutasi_cash_masuk += $d['nominal']; else $mutasi_atm_masuk += $d['nominal'];
    } else {
        $total_keluar += $d['nominal'];
        $running_saldo -= $d['nominal'];
        if($d['metode'] == 'Cash') $mutasi_cash_keluar += $d['nominal']; else $mutasi_atm_keluar += $d['nominal'];
    }
    $d['saldo_row'] = $running_saldo;
    $list_data[] = $d;
}

$saldo_akhir_global = $saldo_awal_global + $total_masuk - $total_keluar;
$saldo_akhir_cash   = $saldo_awal_cash + $mutasi_cash_masuk - $mutasi_cash_keluar;
$saldo_akhir_atm    = $saldo_awal_atm + $mutasi_atm_masuk - $mutasi_atm_keluar;

function tgl_indo($tanggal){
    $bulan = array (1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember');
    $pecahkan = explode('-', $tanggal);
    return $pecahkan[2] . ' ' . $bulan[ (int)$pecahkan[1] ] . ' ' . $pecahkan[0];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Arus Kas - NORIC RACING</title>
    <link href="https://fonts.googleapis.com/css2?family=Times+New+Roman:wght@400;700&family=Arial:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        /* RESET PRINT SETTINGS */
        @media print {
            @page {
                size: A4 portrait;
                margin: 0; /* Hilangkan Header Browser */
            }
            body { margin: 0; }
            .sheet {
                padding: 1.5cm 2cm; 
                width: 100%; height: 100%;
                position: relative; 
                page-break-after: always;
            }
            .print-btn { display: none; }
        }

        body { font-family: 'Arial', sans-serif; font-size: 10px; color: #000; background: #fff; }
        
        @media screen {
            body { background: #f0f0f0; padding: 20px; } 
            .sheet { 
                background: white; width: 21cm; min-height: 29.7cm; 
                margin: 0 auto; padding: 1.5cm 2cm; 
                box-shadow: 0 0 10px rgba(0,0,0,0.1); 
                position: relative;
            } 
        }

        /* WATERMARK */
        .sheet::before {
            content: "";
            position: absolute;
            top: 50%; left: 50%;
            transform: translate(-50%, -50%);
            width: 400px; height: 400px;
            background-image: url('../../assets/image/logo-noric.png');
            background-repeat: no-repeat;
            background-position: center;
            background-size: contain;
            opacity: 0.08; /* Transparansi */
            z-index: 0;
            pointer-events: none;
        }
        .content-layer { position: relative; z-index: 1; }

        /* KOP SURAT */
        .kop-surat { border-bottom: 3px double #000; padding-bottom: 8px; margin-bottom: 15px; display: flex; align-items: center; justify-content: center; position: relative; }
        .kop-logo { width: 70px; height: auto; position: absolute; left: 0; top: 0; }
        .kop-text { text-align: center; width: 100%; padding: 0 80px; }
        .kop-nama { font-family: 'Times New Roman', serif; font-size: 20px; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 3px; }
        .kop-alamat { font-size: 10px; margin-bottom: 2px; }
        .kop-kontak { font-size: 9px; font-style: italic; font-weight: bold; }

        .report-title { text-align: center; font-weight: bold; font-size: 14px; text-decoration: underline; margin-bottom: 3px; text-transform: uppercase; }
        .report-period { text-align: center; font-size: 11px; margin-bottom: 15px; }

        /* RINGKASAN SALDO */
        .summary-box {
            display: flex;
            justify-content: space-between;
            border: 1px solid #000;
            padding: 10px;
            margin-bottom: 15px;
            background-color: rgba(249, 249, 249, 0.8);
        }
        .sum-item { text-align: center; width: 33%; border-right: 1px solid #ccc; }
        .sum-item:last-child { border-right: none; }
        .sum-val { font-weight: bold; font-size: 12px; display: block; margin-top: 2px; }
        .sum-lbl { font-size: 9px; text-transform: uppercase; color: #555; }

        /* TABEL UTAMA */
        .table-data { width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 9px; background: transparent; }
        .table-data th, .table-data td { border: 1px solid #000; padding: 5px; }
        .table-data th { background-color: #eee; font-weight: 700; text-align: center; text-transform: uppercase; }
        .text-right { text-align: right !important; }
        .text-center { text-align: center !important; }
        .text-bold { font-weight: bold; }
        .bg-saldo-awal { background-color: #f0f0f0; font-style: italic; font-weight: bold; }

        /* FOOTER SIGN */
        .footer-sign { margin-top: 30px; display: flex; justify-content: space-between; page-break-inside: avoid; }
        .sign-box { width: 200px; text-align: center; }
        .sign-name { margin-top: 60px; font-weight: 700; text-decoration: underline; }

        .print-btn { position: fixed; bottom: 30px; right: 30px; background: #1e293b; color: white; padding: 12px 20px; border-radius: 50px; cursor: pointer; border: none; font-weight: bold; box-shadow: 0 4px 10px rgba(0,0,0,0.3); z-index:9999; }
    </style>
</head>
<body>

<div class="sheet">
    <div class="content-layer">
        <div class="kop-surat">
            <img src="../../assets/image/logo-noric.png" alt="Logo" class="kop-logo">
            <div class="kop-text">
                <div class="kop-nama">NORIC RACING EXHAUST</div>
                <div class="kop-alamat">JL. Ketuhu, Wirasana, Kec. Purbalingga, Jawa Tengah 53318</div>
                <div class="kop-kontak">Telp: +62 821-1358-2244 | Email: admin@noric-management.my.id</div>
            </div>
        </div>

        <div class="report-title">LAPORAN ARUS KAS</div>
        <div class="report-period">Periode: <?= tgl_indo($tgl_awal) ?> s/d <?= tgl_indo($tgl_akhir) ?></div>

        <div class="summary-box">
            <div class="sum-item">
                <span class="sum-lbl">Saldo Awal</span>
                <span class="sum-val">Rp <?= number_format($saldo_awal_global) ?></span>
            </div>
            <div class="sum-item">
                <span class="sum-lbl">Total Masuk</span>
                <span class="sum-val" style="color: green;">+ Rp <?= number_format($total_masuk) ?></span>
            </div>
            <div class="sum-item">
                <span class="sum-lbl">Total Keluar</span>
                <span class="sum-val" style="color: red;">- Rp <?= number_format($total_keluar) ?></span>
            </div>
            <div class="sum-item" style="background: #eee;">
                <span class="sum-lbl">Saldo Akhir</span>
                <span class="sum-val">Rp <?= number_format($saldo_akhir_global) ?></span>
            </div>
        </div>

        <table class="table-data">
            <thead>
                <tr>
                    <th width="12%">Tanggal</th>
                    <th width="10%">Metode</th>
                    <th>Keterangan</th>
                    <th width="10%">Tipe</th>
                    <th width="15%">Masuk</th>
                    <th width="15%">Keluar</th>
                    <th width="15%">Saldo</th>
                </tr>
            </thead>
            <tbody>
                <tr class="bg-saldo-awal">
                    <td colspan="6" class="text-right">SALDO AWAL</td>
                    <td class="text-right">Rp <?= number_format($saldo_awal_global) ?></td>
                </tr>

                <?php if(empty($list_data)): ?>
                    <tr><td colspan="7" class="text-center" style="padding:20px;">Tidak ada transaksi pada periode ini.</td></tr>
                <?php else: ?>
                    <?php foreach($list_data as $d): $is_in = ($d['jenis'] == 'Masuk'); ?>
                    <tr>
                        <td class="text-center"><?= date('d/m/Y', strtotime($d['tanggal'])) ?></td>
                        <td class="text-center"><?= $d['metode'] ?></td>
                        <td><?= htmlspecialchars($d['keterangan']) ?></td>
                        <td class="text-center"><?= strtoupper($d['jenis']) ?></td>
                        <td class="text-right"><?= $is_in ? number_format($d['nominal']) : '-' ?></td>
                        <td class="text-right"><?= !$is_in ? number_format($d['nominal']) : '-' ?></td>
                        <td class="text-right text-bold"><?= number_format($d['saldo_row']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="4" class="text-right text-bold">TOTAL MUTASI</td>
                    <td class="text-right text-bold">Rp <?= number_format($total_masuk) ?></td>
                    <td class="text-right text-bold">Rp <?= number_format($total_keluar) ?></td>
                    <td class="text-right text-bold" style="background: #eee;">Rp <?= number_format($saldo_akhir_global) ?></td>
                </tr>
            </tfoot>
        </table>

        <div style="font-size: 9px; margin-top: 10px; border: 1px solid #000; padding: 5px; width: 50%;">
            <b>Rincian Saldo Akhir:</b><br>
            - Cash (Dompet): Rp <?= number_format($saldo_akhir_cash) ?><br>
            - ATM (Rekening): Rp <?= number_format($saldo_akhir_atm) ?>
        </div>

        <div class="footer-sign">
            <div class="sign-box">
                <div>Purbalingga, <?= tgl_indo(date('Y-m-d')) ?></div>
                <div style="margin-top:5px;">Dibuat Oleh,</div>
                <div class="sign-name">Admin Keuangan</div>
            </div>
            <div class="sign-box">
                <div>Diketahui Oleh,</div>
                <div style="margin-top:5px;">Pimpinan</div>
                <div class="sign-name">......................................</div>
            </div>
        </div>
    </div>
</div>

<button class="print-btn" onclick="window.print()">🖨️ Cetak</button>

</body>
</html>