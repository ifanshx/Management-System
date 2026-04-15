<?php
require_once '../../config/database.php';

if(isset($_GET['id'])) {
    $oid = mysqli_real_escape_string($conn, $_GET['id']);
    // Ambil data item
    $q = mysqli_query($conn, "SELECT * FROM order_items WHERE order_id='$oid'");
    
    echo '<table style="width:100%; border-collapse:collapse; font-size:13px;">';
    echo '<thead style="background:#f1f5f9; color:#64748b;">
            <tr>
                <th style="padding:10px; text-align:left;">Nama Barang</th>
                <th style="padding:10px; text-align:center;">Total Order</th>
                <th style="padding:10px; text-align:center;">Sudah Dikirim</th>
                <th style="padding:10px; text-align:center;">Sisa</th>
                <th style="padding:10px; text-align:right;">Kirim Sekarang</th>
            </tr>
          </thead>';
    echo '<tbody>';

    $found = false;
    while($d = mysqli_fetch_assoc($q)) {
        $found = true;
        $sisa = $d['qty'] - $d['qty_sent'];
        $bg = ($sisa <= 0) ? '#f0fdf4' : '#fff';
        $color = ($sisa <= 0) ? '#16a34a' : '#334155';
        
        echo "<tr style='background:$bg; border-bottom:1px solid #f1f5f9;'>";
        echo "<td style='padding:10px; font-weight:600; color:$color;'>".htmlspecialchars($d['nama_barang'])."</td>";
        echo "<td style='padding:10px; text-align:center;'>{$d['qty']}</td>";
        echo "<td style='padding:10px; text-align:center;'>{$d['qty_sent']}</td>";
        echo "<td style='padding:10px; text-align:center; font-weight:bold;'>{$sisa}</td>";
        
        echo "<td style='padding:10px; text-align:right;'>";
        echo "<input type='hidden' name='item_id[]' value='{$d['id']}'>";
        if($sisa > 0) {
            // Input maksimal sisa
            echo "<input type='number' name='qty_kirim_sekarang[]' class='form-control' style='width:80px; text-align:center; border:1px solid #cbd5e1; padding:5px; border-radius:5px;' min='0' max='$sisa' placeholder='0'>";
        } else {
            echo "<span style='font-size:11px; font-weight:bold; color:#16a34a;'>Lunas</span>";
            echo "<input type='hidden' name='qty_kirim_sekarang[]' value='0'>";
        }
        echo "</td>";
        echo "</tr>";
    }
    
    if(!$found) {
        echo "<tr><td colspan='5' style='text-align:center; padding:20px;'>Item tidak ditemukan.</td></tr>";
    }
    
    echo '</tbody></table>';
}
?>