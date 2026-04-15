<?php
// TAMPILKAN SEMUA ERROR (UNTUK DEBUGGING)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 1. KONEKSI
// Coba sesuaikan path ini. 
// Jika file ini ada di dalam folder 'cetak', maka naik 3 level ke config (tergantung struktur folder Anda)
// OPSI A: Jika struktur folder: root/pages/inventory/cetak/file.php
$path_config = '../../../config/database.php'; 

// OPSI B: Jika struktur folder: root/pages/inventory/file.php (tanpa folder cetak)
// $path_config = '../../config/database.php';

if (file_exists($path_config)) {
    require_once $path_config;
} else {
    die("<h1>Error Path Database!</h1>File database.php tidak ditemukan di: <b>$path_config</b>.<br>Silakan cek struktur folder Anda.");
}

// 2. CEK SESSION
if (!isset($_SESSION['status'])) { 
    die("<h1>Sesi Habis</h1>Silakan login kembali.");
}

// 3. AMBIL DATA
$kode_url = isset($_GET['kode']) ? $_GET['kode'] : '';

// --- DEBUGGING AREA (HAPUS NANTI JIKA SUDAH FIX) ---
if(empty($kode_url)) {
    die("<h1>Error Parameter!</h1>Kode transaksi tidak terbaca dari URL.<br>Pastikan link di tombol cetak berformat: <code>?kode=OUT-XXXX</code>");
}
// ---------------------------------------------------

$data = [];
$safe_kode = $conn->real_escape_string($kode_url);

// Cek apakah tabel ada isinya?
$q = $conn->query("SELECT * FROM transaksi_bahan_baku WHERE kode_transaksi = '$safe_kode'");

if (!$q) {
    die("<h1>Error Query SQL!</h1>" . $conn->error);
}

while($row = $q->fetch_assoc()) {
    $data[] = $row;
}

// Jika data kosong
if (empty($data)) {
    echo "<h1>Data transaksi tidak ditemukan.</h1>";
    echo "Sistem mencari kode: <b>" . htmlspecialchars($kode_url) . "</b><br>";
    echo "Jumlah data ditemukan: 0<br>";
    echo "<br><b>Solusi:</b><br>";
    echo "1. Cek tabel <code>transaksi_bahan_baku</code> di phpMyAdmin, apakah kode tersebut ada?<br>";
    echo "2. Pastikan tidak ada spasi tambahan di kode transaksi.<br>";
    exit;
}

// Ambil Header info dari baris pertama
$header = $data[0];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Jalan - <?= $header['kode_transaksi'] ?></title>
    <style>
        /* RESET & BASIC */
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Arial, sans-serif; font-size: 12px; color: #000; padding: 20px;}
        .container { width: 100%; max-width: 800px; margin: 0 auto; border: 1px solid #000; padding: 20px; }
        .header { border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: flex-start; }
        .company-info h1 { font-size: 18px; font-weight: bold; margin-bottom: 5px; }
        .company-info p { font-size: 11px; }
        .doc-title { text-align: right; }
        .doc-title h2 { font-size: 16px; text-transform: uppercase; border: 1px solid #000; padding: 5px 10px; display: inline-block; }
        .meta-info { display: flex; justify-content: space-between; margin-bottom: 20px; }
        .meta-table td { padding: 2px 5px; font-size: 12px; }
        .item-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .item-table th, .item-table td { border: 1px solid #000; padding: 8px; text-align: left; }
        .item-table th { background-color: #f0f0f0; text-align: center; font-weight: bold; text-transform: uppercase; }
        .text-center { text-align: center !important; }
        .signature-section { display: flex; justify-content: space-between; margin-top: 40px; text-align: center; }
        .sig-box { width: 30%; }
        .sig-space { height: 70px; }
        .sig-line { border-top: 1px solid #000; margin: 0 auto; width: 80%; }
        @media print { @page { size: auto; margin: 0mm; } body { padding: 10px; } .no-print { display: none; } }
    </style>
</head>
<body onload="window.print()">

    <div class="container">
        <div class="header">
            <div class="company-info">
                <h1>NORIC RACING EXHAUST</h1>
                <p>Jl. Raya Purbalingga - Bobotsari</p>
                <p>Purbalingga, Jawa Tengah</p>
            </div>
            <div class="doc-title">
                <h2>BUKTI PENGELUARAN BARANG</h2>
            </div>
        </div>

        <div class="meta-info">
            <table class="meta-table">
                <tr><td>No. Transaksi</td><td>: <?= $header['kode_transaksi'] ?></td></tr>
                <tr><td>Tanggal</td><td>: <?= date('d F Y', strtotime($header['created_at'])) ?></td></tr>
            </table>
            <table class="meta-table">
                <tr><td>Divisi/Tujuan</td><td>: <?= $header['tujuan_pengambilan'] ?></td></tr>
                <tr><td>PIC Penerima</td><td>: <?= strtoupper($header['karyawan_pengambil']) ?></td></tr>
            </table>
        </div>

        <table class="item-table">
            <thead>
                <tr>
                    <th width="5%">No</th>
                    <th width="40%">Nama Barang</th>
                    <th width="15%">Jenis</th>
                    <th width="15%">Qty</th>
                    <th width="25%">Keterangan</th>
                </tr>
            </thead>
            <tbody>
                <?php $no = 1; foreach($data as $d): ?>
                <tr>
                    <td class="text-center"><?= $no++ ?></td>
                    <td><strong><?= $d['nama_bahan'] ?></strong></td>
                    <td class="text-center"><?= $d['jenis_bahan'] ?></td>
                    <td class="text-center"><strong><?= floatval($d['jumlah_ambil']) ?></strong> <?= $d['satuan'] ?></td>
                    <td><?= $d['keterangan'] ? $d['keterangan'] : '-' ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div style="font-size: 11px; margin-bottom: 20px;">
            <strong>Catatan:</strong><br>
            - Harap periksa barang yang diterima sesuai dengan daftar di atas.<br>
            - Bukti ini sah jika telah ditandatangani oleh kedua belah pihak.
        </div>

        <div class="signature-section">
            <div class="sig-box"><p>Diserahkan Oleh (Gudang)</p><div class="sig-space"></div><div class="sig-line"></div><p>Admin Gudang</p></div>
            <div class="sig-box"><p>Diketahui Oleh</p><div class="sig-space"></div><div class="sig-line"></div><p>Kepala Produksi</p></div>
            <div class="sig-box"><p>Diterima Oleh</p><div class="sig-space"></div><div class="sig-line"></div><p><?= strtoupper($header['karyawan_pengambil']) ?></p></div>
        </div>
    </div>
</body>
</html>