<?php
// 1. KONEKSI
require_once '../../config/database.php';

// 2. VALIDASI AKSES
if (!isset($_SESSION['status'])) { 
    echo "<script>window.close();</script>"; 
    exit; 
}

// 3. AMBIL DATA
$kode = isset($_GET['kode']) ? $_GET['kode'] : '';
$data = [];

if(!empty($kode)) {
    $q = $conn->query("SELECT * FROM transaksi_bahan_baku WHERE kode_transaksi = '$conn->real_escape_string($kode)'");
    while($row = $q->fetch_assoc()) {
        $data[] = $row;
    }
}

// Jika data kosong
if (empty($data)) {
    echo "Data transaksi tidak ditemukan.";
    exit;
}

// Ambil Header info dari baris pertama
$header = $data[0];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Jalan - <?= $kode ?></title>
    <style>
        /* RESET & BASIC */
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { 
            font-family: Arial, sans-serif; 
            font-size: 12px; 
            color: #000; 
            padding: 20px;
        }

        /* CONTAINER A5 Landscape / A4 Portrait friendly */
        .container {
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
            border: 1px solid #000;
            padding: 20px;
        }

        /* HEADER */
        .header {
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }
        .company-info h1 { font-size: 18px; font-weight: bold; margin-bottom: 5px; }
        .company-info p { font-size: 11px; }
        
        .doc-title { text-align: right; }
        .doc-title h2 { font-size: 16px; text-transform: uppercase; border: 1px solid #000; padding: 5px 10px; display: inline-block; }

        /* META INFO */
        .meta-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        .meta-table td { padding: 2px 5px; font-size: 12px; }
        .meta-table tr td:first-child { font-weight: bold; width: 100px; }

        /* TABLE ITEMS */
        .item-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .item-table th, .item-table td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }
        .item-table th {
            background-color: #f0f0f0;
            text-align: center;
            font-weight: bold;
            text-transform: uppercase;
        }
        .text-center { text-align: center !important; }
        .text-right { text-align: right !important; }

        /* SIGNATURES */
        .signature-section {
            display: flex;
            justify-content: space-between;
            margin-top: 40px;
            text-align: center;
        }
        .sig-box { width: 30%; }
        .sig-space { height: 70px; }
        .sig-line { border-top: 1px solid #000; margin: 0 auto; width: 80%; }

        /* PRINT SETTINGS */
        @media print {
            @page { size: auto; margin: 0mm; }
            body { padding: 10px; }
            .no-print { display: none; }
        }
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
                <tr>
                    <td>No. Transaksi</td>
                    <td>: <?= $header['kode_transaksi'] ?></td>
                </tr>
                <tr>
                    <td>Tanggal</td>
                    <td>: <?= date('d F Y', strtotime($header['created_at'])) ?></td>
                </tr>
            </table>

            <table class="meta-table">
                <tr>
                    <td>Divisi/Tujuan</td>
                    <td>: <?= $header['tujuan_pengambilan'] ?></td>
                </tr>
                <tr>
                    <td>PIC Penerima</td>
                    <td>: <?= strtoupper($header['karyawan_pengambil']) ?></td>
                </tr>
            </table>
        </div>

        <table class="item-table">
            <thead>
                <tr>
                    <th width="5%">No</th>
                    <th width="40%">Nama Barang</th>
                    <th width="15%">Jenis/Kategori</th>
                    <th width="15%">Qty</th>
                    <th width="25%">Keterangan</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $no = 1;
                foreach($data as $d): 
                ?>
                <tr>
                    <td class="text-center"><?= $no++ ?></td>
                    <td>
                        <strong><?= $d['nama_bahan'] ?></strong>
                    </td>
                    <td class="text-center"><?= $d['jenis_bahan'] ?></td>
                    <td class="text-center">
                        <strong><?= floatval($d['jumlah_ambil']) ?></strong> <?= $d['satuan'] ?>
                    </td>
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
            <div class="sig-box">
                <p>Diserahkan Oleh (Gudang)</p>
                <div class="sig-space"></div>
                <div class="sig-line"></div>
                <p>Admin Gudang</p>
            </div>
            
            <div class="sig-box">
                <p>Diketahui Oleh</p>
                <div class="sig-space"></div>
                <div class="sig-line"></div>
                <p>Kepala Produksi</p>
            </div>

            <div class="sig-box">
                <p>Diterima Oleh</p>
                <div class="sig-space"></div>
                <div class="sig-line"></div>
                <p><?= strtoupper($header['karyawan_pengambil']) ?></p>
            </div>
        </div>
    </div>

</body>
</html>