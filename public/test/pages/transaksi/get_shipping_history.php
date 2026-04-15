<?php
require_once '../../config/database.php';
if(!isset($_GET['id'])) die("");
$oid = mysqli_real_escape_string($conn, $_GET['id']);

$q = mysqli_query($conn, "SELECT p.*, (SELECT SUM(qty_kirim) FROM pengiriman_items WHERE pengiriman_id=p.id) as total_pcs 
                          FROM pengiriman p WHERE order_id='$oid' ORDER BY tanggal DESC");

if(mysqli_num_rows($q) > 0) {
    while($d = mysqli_fetch_assoc($q)) {
        echo '<div style="border:1px solid #e2e8f0; padding:15px; border-radius:8px; margin-bottom:10px; display:flex; justify-content:space-between; align-items:center;">
                <div>
                    <div style="font-weight:700; color:#1e293b;">Surat Jalan #'.$d['id'].'</div>
                    <div style="font-size:12px; color:#64748b;">Tgl: '.date('d M Y', strtotime($d['tanggal'])).' | Total: <b>'.$d['total_pcs'].' Pcs</b></div>
                </div>
                <a href="cetak_surat_jalan.php?id='.$d['id'].'" target="_blank" style="background:#fff; border:1px solid #cbd5e1; color:#334155; padding:6px 12px; border-radius:6px; text-decoration:none; font-size:12px; font-weight:600;">
                    <i class="fa fa-print"></i> Cetak
                </a>
              </div>';
    }
} else {
    echo '<div style="text-align:center; color:#94a3b8; padding:20px;">Belum ada riwayat pengiriman.</div>';
}
?>