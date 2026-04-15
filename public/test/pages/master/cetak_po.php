<?php
require_once '../../config/database.php';
date_default_timezone_set('Asia/Jakarta');

if (!isset($_SESSION['status'])) { header("Location: ../../login.php"); exit; }

$kode = $_GET['kode'] ?? '';

// 1. Ambil Item Barang
$q = $conn->query("SELECT * FROM incoming_bahan_baku WHERE kode_transaksi = '$kode'");
$data = [];
$grand_total = 0;
while($r = $q->fetch_assoc()) { 
    $data[] = $r; 
    $grand_total += $r['total_harga'];
}

if(empty($data)) { echo "Data tidak ditemukan"; exit; }
$header = $data[0];

// 2. Ambil Info Pembayaran dari Hutang Dagang (Untuk detail DP/Sisa)
$q_hutang = $conn->query("SELECT total_dibayar, sisa_hutang, jatuh_tempo FROM hutang_dagang WHERE kode_transaksi = '$kode'");
$d_hutang = $q_hutang->fetch_assoc();

// Logika Tampilan Pembayaran
$metode_bayar = $header['metode_pembayaran'];
$total_bayar  = 0;
$sisa_hutang  = 0;
$tgl_tempo    = '-';

if ($metode_bayar == 'Cash') {
    $total_bayar = $grand_total;
    $sisa_hutang = 0;
    $tgl_tempo   = '-';
} else {
    // Jika Tempo atau Partial
    if ($d_hutang) {
        $total_bayar = $d_hutang['total_dibayar'];
        $sisa_hutang = $d_hutang['sisa_hutang'];
        $tgl_tempo   = !empty($d_hutang['jatuh_tempo']) ? date('d/m/Y', strtotime($d_hutang['jatuh_tempo'])) : '-';
    } else {
        // Fallback jika data hutang tidak ada (sangat jarang terjadi)
        $total_bayar = 0;
        $sisa_hutang = $grand_total;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>PO - <?= $kode ?></title>
    <style>
        body { font-family: Arial, sans-serif; padding: 30px; font-size: 13px; color: #333; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #333; padding-bottom: 15px; }
        .header h2 { margin: 0; font-size: 22px; text-transform: uppercase; }
        .header p { margin: 5px 0 0; font-size: 14px; }
        
        .info-table { width: 100%; margin-bottom: 20px; }
        .info-table td { padding: 4px 0; vertical-align: top; }
        
        .data-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .data-table th, .data-table td { border: 1px solid #333; padding: 10px; }
        .data-table th { background: #f5f5f5; text-transform: uppercase; font-size: 11px; }
        
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .bold { font-weight: bold; }
        
        .summary-box { width: 40%; float: right; margin-top: 10px; }
        .summary-table { width: 100%; border-collapse: collapse; }
        .summary-table td { padding: 5px; border: none; }
        .summary-table .border-top { border-top: 1px solid #333; }
        
        .footer { margin-top: 80px; width: 100%; display: flex; justify-content: space-between; text-align: center; clear: both; }
        .sign-box { width: 30%; }
        .sign-line { margin-top: 60px; border-top: 1px solid #333; font-weight: bold; }
        
        @media print { 
            .no-print { display: none; } 
            body { padding: 0; }
        }
    </style>
</head>
<body onload="window.print()">
    
    <button class="no-print" onclick="window.print()" style="padding:10px 20px; margin-bottom:20px; cursor:pointer;">Cetak Dokumen</button>
    
    <div class="header">
        <h2>NORIC RACING EXHAUST</h2>
        <p>BUKTI PENERIMAAN BARANG (PURCHASE ORDER)</p>
    </div>

    <table class="info-table">
        <tr>
            <td width="15%"><b>No. Transaksi</b></td>
            <td width="35%">: <?= $header['kode_transaksi'] ?></td>
            <td width="15%"><b>Tanggal</b></td>
            <td>: <?= date('d F Y', strtotime($header['tanggal_masuk'])) ?></td>
        </tr>
        <tr>
            <td><b>Supplier</b></td>
            <td>: <?= $header['supplier'] ?></td>
            <td><b>Penerima</b></td>
            <td>: <?= $_SESSION['nama_lengkap'] ?? 'Admin Gudang' ?></td>
        </tr>
        <tr>
            <td><b>Metode Bayar</b></td>
            <td>: <?= $metode_bayar ?></td>
            <td><b>Jatuh Tempo</b></td>
            <td>: <?= $tgl_tempo ?></td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th width="5%">No</th>
                <th>Nama Barang / Deskripsi</th>
                <th width="10%">Qty</th>
                <th width="15%">Harga Satuan</th>
                <th width="20%">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            <?php $no=1; foreach($data as $d): ?>
            <tr>
                <td class="text-center"><?= $no++ ?></td>
                <td>
                    <b><?= $d['nama_bahan'] ?></b>
                    <?php if(!empty($d['keterangan'])): ?>
                        <br><small style="font-style:italic;"><?= $d['keterangan'] ?></small>
                    <?php endif; ?>
                </td>
                <td class="text-center"><?= floatval($d['jumlah_masuk']) ?></td>
                <td class="text-right">Rp <?= number_format($d['harga_beli_satuan'],0,',','.') ?></td>
                <td class="text-right">Rp <?= number_format($d['total_harga'],0,',','.') ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="summary-box">
        <table class="summary-table">
            <tr>
                <td class="text-right">Total Tagihan :</td>
                <td class="text-right bold">Rp <?= number_format($grand_total,0,',','.') ?></td>
            </tr>
            <tr>
                <td class="text-right">Dibayar / DP :</td>
                <td class="text-right">Rp <?= number_format($total_bayar,0,',','.') ?></td>
            </tr>
            <tr>
                <td class="text-right border-top bold">Sisa Hutang :</td>
                <td class="text-right border-top bold">Rp <?= number_format($sisa_hutang,0,',','.') ?></td>
            </tr>
        </table>
    </div>

    <div class="footer">
        <div class="sign-box">
            <p>Diserahkan Oleh,</p>
            <div class="sign-line"><?= $header['supplier'] ?></div>
        </div>
        <div class="sign-box">
            <p>Diketahui Oleh,</p>
            <div class="sign-line">Keuangan / Admin</div>
        </div>
        <div class="sign-box">
            <p>Diterima Oleh,</p>
            <div class="sign-line"><?= $_SESSION['nama_lengkap'] ?? 'Gudang' ?></div>
        </div>
    </div>

</body>
</html>