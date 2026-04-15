<?php 
require_once '../../config/database.php';
// session_start() ditaruh paling atas
session_start();

if (!isset($_SESSION['status']) || $_SESSION['status'] != "login") {
    echo "<script>window.close();</script>";
    exit;
}

date_default_timezone_set('Asia/Jakarta');

// =========================================================================
// 1. DATA PROCESSING (SAMA DENGAN HALAMAN LAPORAN STOK)
// =========================================================================

// --- FILTER LOGIC ---
$kategori_filter = isset($_GET['kategori']) ? $_GET['kategori'] : 'all';
$status_filter   = isset($_GET['status']) ? $_GET['status'] : 'all';

// Base Query
$where_sql = "WHERE 1=1";

if ($kategori_filter != 'all') {
    $where_sql .= " AND jenis_bahan = '$kategori_filter'";
}

if ($status_filter == 'low') {
    $where_sql .= " AND stok <= stok_minimum AND stok > 0";
} elseif ($status_filter == 'empty') {
    $where_sql .= " AND stok = 0";
} elseif ($status_filter == 'safe') {
    $where_sql .= " AND stok > stok_minimum";
}

// --- QUERY DATA TABEL ---
$sql_data = "SELECT * FROM bahan_baku $where_sql ORDER BY jenis_bahan ASC, nama_bahan ASC";
$q_data   = $conn->query($sql_data);

// --- HITUNG SUMMARY UTK HEADER ---
$total_item_tampil = 0;
$total_aset_tampil = 0;

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
    <title>Laporan Stok Bahan Baku - NORIC RACING</title>
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

        /* INFO FILTER */
        .filter-info { 
            margin-bottom: 10px; 
            font-size: 10px; 
            border: 1px dashed #999; 
            padding: 5px 10px; 
            display: inline-block;
        }

        /* TABEL UTAMA */
        .table-data { width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 9px; background: transparent; }
        .table-data th, .table-data td { border: 1px solid #000; padding: 5px; }
        .table-data th { background-color: #eee; font-weight: 700; text-align: center; text-transform: uppercase; }
        .text-right { text-align: right !important; }
        .text-center { text-align: center !important; }
        .text-bold { font-weight: bold; }
        .text-red { color: red; font-weight: bold; }
        
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

        <div class="report-title">LAPORAN STOK BAHAN BAKU</div>
        <div class="report-period">Dicetak Pada: <?= tgl_indo(date('Y-m-d')) ?></div>

        <div class="filter-info">
            Filter: <b><?= ($kategori_filter == 'all') ? 'Semua Kategori' : $kategori_filter ?></b> | 
            Status: <b><?= ($status_filter == 'all') ? 'Semua Status' : strtoupper($status_filter) ?></b>
        </div>

        <table class="table-data">
            <thead>
                <tr>
                    <th width="5%">No</th>
                    <th width="15%">Kode</th>
                    <th width="25%">Nama Bahan</th>
                    <th width="15%">Kategori</th>
                    <th width="10%">Satuan</th>
                    <th width="10%">Stok</th>
                    <th width="20%">Nilai Aset (Rp)</th>
                </tr>
            </thead>
            <tbody>
                <?php if($q_data->num_rows == 0): ?>
                    <tr><td colspan="7" class="text-center" style="padding:20px;">Tidak ada data bahan baku sesuai filter.</td></tr>
                <?php else: 
                    $no = 1;
                    while($d = $q_data->fetch_assoc()): 
                        $nilai_aset = $d['stok'] * $d['harga_satuan'];
                        $total_asset_tampil += $nilai_aset;
                        $total_item_tampil++;
                        
                        // Cek Warning Stok
                        $warn_class = ($d['stok'] <= $d['stok_minimum']) ? 'text-red' : '';
                ?>
                    <tr>
                        <td class="text-center"><?= $no++ ?></td>
                        <td class="text-center"><?= $d['kode_bahan'] ?></td>
                        <td><?= $d['nama_bahan'] ?></td>
                        <td class="text-center"><?= $d['jenis_bahan'] ?></td>
                        <td class="text-center"><?= $d['satuan'] ?></td>
                        <td class="text-center <?= $warn_class ?>"><?= $d['stok'] ?></td>
                        <td class="text-right"><?= number_format($nilai_aset, 0, ',', '.') ?></td>
                    </tr>
                <?php endwhile; endif; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="5" class="text-right text-bold">TOTAL NILAI ASET</td>
                    <td class="text-center text-bold"><?= number_format($total_item_tampil) ?> Item</td>
                    <td class="text-right text-bold">Rp <?= number_format($total_asset_tampil, 0, ',', '.') ?></td>
                </tr>
            </tfoot>
        </table>

        <div class="footer-sign">
            <div class="sign-box">
                <div>Purbalingga, <?= tgl_indo(date('Y-m-d')) ?></div>
                <div style="margin-top:5px;">Dibuat Oleh,</div>
                <div class="sign-name">Admin Gudang</div>
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