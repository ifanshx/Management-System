<?php
require_once '../../config/database.php';

// Pastikan ada ID pengiriman
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("ID Pengiriman tidak ditemukan.");
}

$id_pengiriman = mysqli_real_escape_string($conn, $_GET['id']);

// 1. Ambil Data Header
$query_header = "SELECT p.id as no_sj, p.tanggal as tgl_kirim, 
                 o.nama_pelanggan, o.id as no_order, o.keterangan as ket_order
                 FROM pengiriman p 
                 JOIN orderan o ON p.order_id = o.id 
                 WHERE p.id = '$id_pengiriman'";

$result_header = mysqli_query($conn, $query_header);
$header = mysqli_fetch_assoc($result_header);

if (!$header) { die("Data pengiriman tidak ditemukan."); }

// 2. Ambil Data Items (Qty Order, Qty Kirim INI, & Total Qty Sent DATABASE)
// SAYA MENAMBAHKAN: oi.qty_sent agar bisa menghitung sisa
$query_items = "SELECT pi.qty_kirim, oi.nama_barang, oi.qty as qty_total_order, oi.qty_sent
                FROM pengiriman_items pi
                JOIN order_items oi ON pi.order_item_id = oi.id
                WHERE pi.pengiriman_id = '$id_pengiriman' AND pi.qty_kirim > 0";

$result_items = mysqli_query($conn, $query_items);

// --- LOGIKA PAGINATION ---
$all_items = [];
$total_qty_current_shipment = 0;
while($row = mysqli_fetch_assoc($result_items)) {
    // Hitung Sisa Realtime
    // Sisa = Total Order - Total Yang Sudah Dikirim (Akumulasi Database)
    $sisa_db = $row['qty_total_order'] - $row['qty_sent'];
    
    // Logic Status Keterangan
    if ($sisa_db <= 0) {
        $row['status_ket'] = "LUNAS / SELESAI";
    } else {
        $row['status_ket'] = "SISA ORDER: " . $sisa_db;
    }

    $all_items[] = $row;
    $total_qty_current_shipment += $row['qty_kirim'];
}

// Tentukan maksimal baris per halaman
$max_per_page = 14; 

$pages = array_chunk($all_items, $max_per_page);
$total_pages = count($pages);
if ($total_pages == 0) { $pages = [[]]; $total_pages = 1; }

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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surat Jalan #<?php echo $header['no_sj']; ?></title>
    <style>
        /* --- RESET & BASE STYLES --- */
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 13px; color: #333; margin: 0; padding: 20px; background: #525659; }
        .page-container { background: #fff; width: 210mm; min-height: 297mm; margin: 0 auto 20px auto; padding: 15mm 20mm; box-shadow: 0 4px 10px rgba(0,0,0,0.5); position: relative; box-sizing: border-box; overflow: hidden; display: flex; flex-direction: column; }
        .watermark-logo { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 80%; height: auto; opacity: 0.15; z-index: 0; pointer-events: none; }
        .content-overlay { position: relative; z-index: 2; flex: 1; display: flex; flex-direction: column; }
        
        /* Header */
        .company-header { border-bottom: 2px solid #000; padding-bottom: 15px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; }
        .company-info h1 { margin: 0 0 5px 0; font-size: 24px; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; line-height: 1; }
        .company-info p { margin: 2px 0 0; font-size: 12px; color: #555; }
        .document-title { text-align: right; }
        .document-title h2 { margin: 0; font-size: 20px; border: 2px solid #333; padding: 5px 15px; display: inline-block; text-transform: uppercase; }
        .page-number { font-size: 10px; text-align: right; margin-top: 5px; color: #666; }

        /* Info Customer */
        .info-section { display: flex; justify-content: space-between; margin-bottom: 20px; }
        .customer-box { border: 1px solid #ddd; padding: 15px; width: 45%; border-radius: 4px; background: rgba(255,255,255,0.85); }
        .customer-box h4 { margin: 0 0 5px; font-size: 11px; text-transform: uppercase; color: #777; }
        .meta-table td { padding: 2px 0; font-size: 13px; }
        .meta-table td:first-child { font-weight: bold; width: 100px; }

        /* Tabel Items */
        .items-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; background: rgba(129, 122, 122, 0.1); }
        .items-table th { background: rgba(129, 122, 122, 0.36); border-top: 2px solid #333; border-bottom: 2px solid #333; padding: 8px 10px; text-align: left; font-weight: bold; text-transform: uppercase; font-size: 11px; }
        .items-table td { border-bottom: 1px solid #eee; padding: 8px 10px; vertical-align: top; font-size: 13px; }

        /* Footer */
        .footer-wrapper { margin-top: auto; }
        .note-box { font-size: 11px; margin-bottom: 20px; margin-top: 10px; }
        .note-box ul { margin: 5px 0 0 0; padding-left: 20px; }
        .signature-section { display: flex; justify-content: space-between; margin-top: 20px; page-break-inside: avoid; }
        .sign-box { text-align: center; width: 30%; }
        .sign-box p { margin: 0 0 60px; font-weight: bold; font-size: 12px; }
        .sign-line { border-top: 1px solid #000; margin: 0 auto; width: 80%; padding-top: 5px; font-size: 12px; }

        /* Print Settings */
        .no-print { position: fixed; top: 20px; right: 20px; display: flex; gap: 10px; z-index: 999; }
        .btn { padding: 10px 20px; color: white; border: none; cursor: pointer; border-radius: 5px; font-weight: bold; text-decoration: none; font-family: sans-serif; box-shadow: 0 2px 5px rgba(0,0,0,0.2); }
        .btn-print { background: #2563eb; }
        .btn-back { background: #64748b; }

        @media print {
            @page { size: A4; margin: 0; }
            body { margin: 0; padding: 0; background: none; }
            .page-container { width: 210mm; height: 296mm; margin: 0; padding: 15mm 20mm; border: none; box-shadow: none; page-break-after: always; margin-bottom: 0; }
            .page-container:last-child { page-break-after: auto; }
            .no-print { display: none; }
            .customer-box, .items-table, .items-table th { background: transparent !important; }
        }
    </style>
</head>
<body>

    <div class="no-print">
        <a href="pengiriman.php" class="btn btn-back">Kembali</a>
        <button onclick="window.print()" class="btn btn-print">Cetak PDF</button>
    </div>

    <?php 
    $global_no = 1;
    foreach($pages as $index => $items_in_page): 
        $current_page = $index + 1;
        $is_last_page = ($current_page == $total_pages);
    ?>

    <div class="page-container">
        
        <img src="../../assets/image/logo-noric.png" alt="Watermark" class="watermark-logo" onerror="this.style.display='none'">

        <div class="content-overlay">

            <div class="company-header">
                <div class="company-info">
                    <h1>NORIC RACING EXHAUST</h1>
                    <p>JL. Ketuhu, Wirasana, Kec. Purbalingga, Jawa Tengah 53318</p>
                    <p>Telp: +62 821-1358-2244</p>
                </div>
                <div class="document-title">
                    <h2>SURAT JALAN</h2>
                    <div class="page-number">Hal: <?php echo $current_page . " / " . $total_pages; ?></div>
                </div>
            </div>

            <div class="info-section">
                <div class="customer-box">
                    <h4>Kepada Yth:</h4>
                    <div style="font-size: 16px; font-weight: bold; margin-bottom: 5px;">
                        <?php echo htmlspecialchars($header['nama_pelanggan']); ?>
                    </div>
                    <div style="font-size: 12px; color: #555;">
                        Pelanggan Setia Noric Racing Exhaust
                    </div>
                    <?php if(!empty($header['ket_order'])): ?>
                    <div style="font-size: 11px; margin-top:5px; font-style:italic; color:#444;">
                        Catatan: <?php echo htmlspecialchars($header['ket_order']); ?>
                    </div>
                    <?php endif; ?>
                </div>
                
                <div style="width: 45%;">
                    <table class="meta-table">
                        <tr>
                            <td>No. SJ</td>
                            <td>: <strong>SJ/<?php echo date('Y', strtotime($header['tgl_kirim'])); ?>/<?php echo str_pad($header['no_sj'], 4, '0', STR_PAD_LEFT); ?></strong></td>
                        </tr>
                        <tr>
                            <td>Tanggal</td>
                            <td>: <?php echo tgl_indo($header['tgl_kirim']); ?></td>
                        </tr>
                        <tr>
                            <td>No. Order</td>
                            <td>: ORD-<?php echo str_pad($header['no_order'], 4, '0', STR_PAD_LEFT); ?></td>
                        </tr>
                    </table>
                </div>
            </div>

            <table class="items-table">
                <thead>
                    <tr>
                        <th style="width: 5%; text-align:center;">No</th>
                        <th style="width: 40%;">Nama Barang</th>
                        <th style="width: 12%; text-align:center;">Qty Order</th>
                        <th style="width: 12%; text-align:center;">Dikirim</th>
                        <th style="width: 31%;">Keterangan / Status Barang</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($items_in_page as $item): ?>
                    <tr>
                        <td style="text-align:center;"><?php echo $global_no++; ?></td>
                        <td>
                            <strong style="font-size:13px;"><?php echo htmlspecialchars($item['nama_barang']); ?></strong>
                        </td>
                        <td style="text-align:center; color:#555;">
                            <?php echo number_format($item['qty_total_order']); ?>
                        </td>
                        <td style="text-align:center; font-weight:bold; font-size:14px; ">
                            <?php echo number_format($item['qty_kirim']); ?>
                        </td>
                        <td style="font-size:11px; font-weight:600; color: #444;">
                            <?php echo $item['status_ket']; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    
                    <?php 
                    $rows_needed = $max_per_page - count($items_in_page);
                    for($i=0; $i<$rows_needed; $i++): 
                    ?>
                    <tr>
                        <td style="height: 25px;"></td><td></td><td></td><td></td><td></td>
                    </tr>
                    <?php endfor; ?>
                </tbody>
                
                <?php if($is_last_page): ?>
                <tfoot>
                    <tr>
                        <td colspan="3" style="text-align: right; padding-right: 20px; font-weight: bold; border-top: 2px solid #333;">TOTAL BARANG DIKIRIM (HARI INI)</td>
                        <td style="text-align: center; font-weight: bold; border-top: 2px solid #333; font-size:14px;"><?php echo number_format($total_qty_current_shipment); ?></td>
                        <td style="border-top: 2px solid #333;"></td>
                    </tr>
                </tfoot>
                <?php endif; ?>
            </table>

            <div class="footer-wrapper">
                <?php if(!$is_last_page): ?>
                    <div style="text-align:right; font-style:italic; font-size:11px; margin-top:10px;">
                        (Bersambung ke halaman berikutnya...)
                    </div>
                <?php endif; ?>

                <?php if($is_last_page): ?>
                    <div class="note-box">
                        <strong>Perhatian:</strong>
                        <ul>
                            <li>Barang telah diperiksa dan diterima dalam kondisi baik.</li>
                            <li>Surat jalan ini merupakan bukti penerimaan barang yang sah.</li>
                        </ul>
                    </div>

                    <div class="signature-section">
                        <div class="sign-box">
                            <p>Penerima</p>
                            <div class="sign-line">( ................................... )</div>
                        </div>
                        <div class="sign-box">
                            <p>Supir / Ekspedisi</p>
                            <div class="sign-line">( ................................... )</div>
                        </div>
                        <div class="sign-box">
                            <p>Hormat Kami,</p>
                            <div class="sign-line">( ................................... )</div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div> 
    </div> 
    <?php endforeach; ?> 
</body>
</html>