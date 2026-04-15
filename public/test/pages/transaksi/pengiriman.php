<?php 
require_once '../../config/database.php';
date_default_timezone_set('Asia/Jakarta');
cek_login(); 

if($_SESSION['role'] != 'kepala_bengkel' && $_SESSION['role'] != 'admin') { 
    echo "<script>window.location='../dashboard.php';</script>";
    exit; 
}

$swal_script = "";

// --- PROSES SIMPAN PENGIRIMAN (TRANSACTION SAFER) ---
if(isset($_POST['simpan_pengiriman'])) {
    $id_order  = $_POST['id_order_kirim'];
    $tgl_kirim = $_POST['tgl_kirim'];
    $item_ids  = isset($_POST['item_id']) ? $_POST['item_id'] : []; 
    $kirim_now = isset($_POST['qty_kirim_sekarang']) ? $_POST['qty_kirim_sekarang'] : []; 
    
    if(empty($id_order)) {
        $swal_script = "Swal.fire({icon: 'error', title: 'Error', text: 'ID Order tidak ditemukan.'});";
    } else {
        // Mulai Transaksi Database
        mysqli_begin_transaction($conn);
        $error = false;
        $count_items = 0;

        try {
            // 1. Buat Header Pengiriman
            $q_head = mysqli_query($conn, "INSERT INTO pengiriman (order_id, tanggal) VALUES ('$id_order', '$tgl_kirim')");
            if(!$q_head) throw new Exception("Gagal buat header pengiriman.");
            
            $id_pengiriman = mysqli_insert_id($conn);

            foreach($item_ids as $index => $iid) {
                $qty_input = (int)$kirim_now[$index];
                
                if($qty_input > 0) {
                    // Validasi: Cek Sisa Stok dulu di DB untuk mencegah minus
                    $cek_stok = mysqli_fetch_assoc(mysqli_query($conn, "SELECT qty, qty_sent FROM order_items WHERE id='$iid' FOR UPDATE"));
                    $sisa_db = $cek_stok['qty'] - $cek_stok['qty_sent'];
                    
                    if($qty_input > $sisa_db) {
                        throw new Exception("Qty input melebihi sisa (ID Item: $iid). Max: $sisa_db");
                    }

                    // Insert Item Pengiriman
                    $q_ins = mysqli_query($conn, "INSERT INTO pengiriman_items (pengiriman_id, order_item_id, qty_kirim) VALUES ('$id_pengiriman', '$iid', '$qty_input')");
                    
                    // Update Order Item (Accumulate)
                    $q_upd = mysqli_query($conn, "UPDATE order_items SET qty_sent = qty_sent + $qty_input WHERE id='$iid'");
                    
                    if(!$q_ins || !$q_upd) throw new Exception("Gagal update item database.");
                    $count_items++;
                }
            }

            if($count_items == 0) {
                throw new Exception("Tidak ada item yang diinput (Qty 0 semua).");
            }

            // 2. Cek Status Order Global (Apakah sudah selesai semua?)
            $cek_final = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(qty) as total_order, SUM(qty_sent) as total_sent FROM order_items WHERE order_id='$id_order'"));
            
            if($cek_final['total_sent'] >= $cek_final['total_order']) {
                mysqli_query($conn, "UPDATE orderan SET status='Selesai' WHERE id='$id_order'");
            } else {
                // Pastikan status tetap 'Proses' jika parsial
                mysqli_query($conn, "UPDATE orderan SET status='Proses' WHERE id='$id_order'");
            }

            // Commit Transaksi
            mysqli_commit($conn);
            
            $swal_script = "Swal.fire({icon: 'success', title: 'Terkirim!', text: 'Surat jalan siap dicetak.', showCancelButton: true, confirmButtonText: 'Cetak Surat Jalan', cancelButtonText: 'Tutup'}).then((result) => { 
                if (result.isConfirmed) { 
                    window.open('cetak_surat_jalan.php?id=$id_pengiriman', '_blank'); 
                    window.location='pengiriman.php'; 
                } else { 
                    window.location='pengiriman.php'; 
                } 
            });";

        } catch (Exception $e) {
            mysqli_rollback($conn); // Batalkan semua jika ada error
            $msg = $e->getMessage();
            $swal_script = "Swal.fire({icon: 'error', title: 'Gagal', text: '$msg'});";
        }
    }
}

// Stats Dashboard
$q_stat = mysqli_query($conn, "SELECT COUNT(*) as cnt, status FROM orderan WHERE status IN ('Proses', 'Selesai') GROUP BY status");
$stat_data = ['Proses'=>0, 'Selesai'=>0];
while($r=mysqli_fetch_assoc($q_stat)) $stat_data[$r['status']] = $r['cnt'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <?php include '../../layout/header.php'; ?>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Courier+Prime:wght@400;700&family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    <style>
        :root { --primary: #4f46e5; --accent: #0ea5e9; --bg-body: #f8fafc; --text-main: #1e293b; --card-border: #e2e8f0; }
        body { background-color: var(--bg-body); font-family: 'Inter', sans-serif; color: var(--text-main); }
        .content-wrapper { padding: 30px; }
        .page-header { margin-bottom: 30px; }
        .stats-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: #fff; padding: 20px; border-radius: 16px; border: 1px solid var(--card-border); position: relative; overflow: hidden; }
        .stat-card.blue { border-left: 4px solid #3b82f6; }
        .stat-card.green { border-left: 4px solid #10b981; }
        .stat-value { font-size: 28px; font-weight: 800; color: #0f172a; margin: 0; }
        .modern-card { background: #fff; border-radius: 16px; border: 1px solid var(--card-border); overflow: hidden; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.03); }
        .table-custom { width: 100%; border-collapse: collapse; min-width: 900px; }
        .table-custom th { background: #f8fafc; font-size: 11px; text-transform: uppercase; padding: 18px 20px; color: #64748b; text-align: left; border-bottom: 2px solid #e2e8f0; }
        .table-custom td { padding: 20px; border-bottom: 1px solid #f1f5f9; font-size: 13px; vertical-align: top; }
        .progress-container { width: 100%; background: #f1f5f9; height: 8px; border-radius: 10px; margin-top: 8px; overflow: hidden; }
        .progress-bar-fill { height: 100%; border-radius: 10px; transition: width 0.6s ease; }
        .btn-action { padding: 8px 16px; border-radius: 8px; font-weight: 600; font-size: 12px; cursor: pointer; border:none; display: inline-flex; align-items: center; gap: 8px; transition: all 0.2s; text-decoration: none; }
        .btn-kirim { background: var(--primary); color: white; }
        .btn-kirim:hover { background: #4338ca; }

        /* --- PERBAIKAN MODAL CSS --- */
        .modal { 
            display: none; 
            position: fixed; 
            z-index: 9999; /* Z-Index sangat tinggi agar paling depan */
            left: 0; 
            top: 0; 
            width: 100%; 
            height: 100%; 
            background-color: rgba(15, 23, 42, 0.75); 
            backdrop-filter: blur(4px); 
            
            /* Flexbox untuk menengahkan modal secara sempurna */
            align-items: center;
            justify-content: center;
            padding: 20px; /* Jarak aman dari pinggir layar HP */
            box-sizing: border-box;
        }

        .modal-content { 
            background-color: #fff; 
            margin: 0; /* Margin 0 karena sudah dihandle flexbox */
            padding: 0; 
            border-radius: 16px; 
            width: 600px; 
            max-width: 100%; 
            
            /* Agar modal tidak melebihi tinggi layar dan bisa discroll dalamnya saja */
            max-height: 90vh; 
            display: flex;
            flex-direction: column;
            
            animation: slideUp 0.3s ease-out; 
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }

        /* Animasi */
        @keyframes slideUp { 
            from { transform: translateY(20px); opacity: 0; } 
            to { transform: translateY(0); opacity: 1; } 
        }

        /* Form di dalam modal agar mengikuti flex */
        .modal-content form {
            display: flex;
            flex-direction: column;
            height: 100%;
            overflow: hidden; /* Mencegah scroll double */
        }

        /* Bagian Header Modal (Tetap di atas) */
        .modal-header {
            padding: 20px 25px; 
            border-bottom: 1px solid #e2e8f0; 
            background: #f8fafc; 
            display: flex; 
            justify-content: space-between; 
            align-items: center;
            flex-shrink: 0; /* Jangan mengecil */
        }

        /* Bagian Tengah Modal (Bisa di-scroll) */
        .modal-body {
            padding: 25px;
            overflow-y: auto; /* Scroll vertikal aktif disini */
            flex-grow: 1; /* Mengisi sisa ruang */
        }

        /* Bagian Footer/Tombol (Tetap di bawah) */
        .modal-footer {
            padding: 15px 25px; 
            background: #f8fafc; 
            text-align: right; 
            border-top: 1px solid #e2e8f0;
            flex-shrink: 0; /* Jangan mengecil */
        }
    </style>
</head>
<body>
    <?php include '../../layout/sidebar.php'; ?>
    
    <div class="content-wrapper">
        <div class="page-header">
            <div class="page-title">
                <h3>Jadwal Pengiriman</h3>
                <p>Kelola pengiriman barang parsial dan cetak surat jalan.</p>
            </div>
        </div>

        <div class="stats-grid">
            <div class="stat-card blue">
                <div style="font-size:11px; font-weight:700; color:#64748b; text-transform:uppercase;">Siap Kirim (Partial)</div>
                <div class="stat-value"><?php echo $stat_data['Proses']; ?></div>
            </div>
            <div class="stat-card green">
                <div style="font-size:11px; font-weight:700; color:#64748b; text-transform:uppercase;">Selesai (Lengkap)</div>
                <div class="stat-value"><?php echo $stat_data['Selesai']; ?></div>
            </div>
        </div>

        <div class="modern-card">
            <div style="overflow-x:auto;">
                <table class="table-custom">
                    <thead>
                        <tr>
                            <th width="25%">Pelanggan</th>
                            <th width="40%">Progress Pengiriman</th>
                            <th class="text-center" width="15%">Status</th>
                            <th class="text-center" width="20%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Ambil Orderan Proses & Selesai
                        $q = mysqli_query($conn, "SELECT * FROM orderan WHERE status IN ('Proses', 'Selesai') ORDER BY CASE WHEN status='Proses' THEN 1 ELSE 2 END, tanggal ASC");
                        
                        if(mysqli_num_rows($q) == 0) {
                            echo "<tr><td colspan='4' style='text-align:center; padding:40px; color:#94a3b8;'>Tidak ada data pengiriman aktif.</td></tr>";
                        }

                        while($d = mysqli_fetch_assoc($q)):
                            $oid = $d['id'];
                            // Kalkulasi Persentase
                            $q_sum = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(qty) as tp, SUM(qty_sent) as tk FROM order_items WHERE order_id='$oid'"));
                            $tp = $q_sum['tp'] > 0 ? $q_sum['tp'] : 1;
                            $tk = $q_sum['tk'];
                            $persen = round(($tk / $tp) * 100);
                            $color = ($persen >= 100) ? '#10b981' : '#f59e0b';
                        ?>
                        <tr>
                            <td>
                                <strong style="font-size:15px; color:#1e293b;"><?php echo htmlspecialchars($d['nama_pelanggan']); ?></strong>
                                <div style="font-size:12px; color:#64748b; margin-top:4px;"><i class="fa fa-calendar"></i> <?php echo date('d M Y', strtotime($d['tanggal'])); ?></div>
                            </td>
                            <td>
                                <div style="display:flex; justify-content:space-between; font-size:11px; font-weight:600; color:#64748b;">
                                    <span>Terkirim: <?php echo $tk; ?> / <?php echo $tp; ?> unit</span>
                                    <span style="color:<?php echo $color; ?>"><?php echo $persen; ?>%</span>
                                </div>
                                <div class="progress-container">
                                    <div class="progress-bar-fill" style="width: <?php echo $persen; ?>%; background-color: <?php echo $color; ?>;"></div>
                                </div>
                            </td>
                            <td class="text-center">
                                <?php if($d['status'] == 'Proses'): ?>
                                    <span style="background:#eff6ff; color:#4f46e5; padding:4px 10px; border-radius:20px; font-size:10px; font-weight:700; border:1px solid #c7d2fe;">SIAP KIRIM</span>
                                <?php else: ?>
                                    <span style="background:#f0fdf4; color:#16a34a; padding:4px 10px; border-radius:20px; font-size:10px; font-weight:700; border:1px solid #bbf7d0;">LENGKAP</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <?php if($d['status'] == 'Proses'): ?>
                                    <button onclick="bukaModalKirim(<?php echo $d['id']; ?>, '<?php echo htmlspecialchars($d['nama_pelanggan']); ?>')" class="btn-action btn-kirim">
                                        <i class="fa fa-truck"></i> Input Pengiriman
                                    </button>
                                <?php else: ?>
                                    <button class="btn-action" style="background:#e2e8f0; color:#94a3b8; cursor:default;"><i class="fa fa-check"></i> Selesai</button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div id="modalKirim" class="modal">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h4 style="margin:0; color:#1e293b; font-weight:700;">Buat Pengiriman Baru</h4>
                    <span onclick="document.getElementById('modalKirim').style.display='none'" style="cursor:pointer; font-size:24px;">&times;</span>
                </div>

                <div class="modal-body">
                    <input type="hidden" name="id_order_kirim" id="id_order_kirim">
                    
                    <div style="margin-bottom:20px; background:#eff6ff; padding:15px; border-radius:8px; border:1px solid #bfdbfe;">
                        <div style="font-size:11px; color:#4f46e5; font-weight:700;">PELANGGAN</div>
                        <div id="nama_plg_modal" style="font-size:16px; font-weight:800; color:#1e293b;">-</div>
                    </div>
                    
                    <div style="margin-bottom:20px;">
                        <label style="font-size:12px; font-weight:700; color:#64748b;">Tanggal Surat Jalan</label>
                        <input type="date" name="tgl_kirim" value="<?php echo date('Y-m-d'); ?>" style="width:100%; padding:10px; border:1px solid #cbd5e1; border-radius:8px; margin-top:5px;" required>
                    </div>

                    <div style="font-size:12px; font-weight:700; color:#1e293b; margin-bottom:10px; border-bottom:1px solid #e2e8f0; padding-bottom:5px;">
                        ITEM YANG AKAN DIKIRIM (Isi Qty)
                    </div>
                    <div id="list_item_kirim" style="border:1px solid #e2e8f0; border-radius:8px;"></div>
                </div>

                <div class="modal-footer">
                    <button type="button" onclick="document.getElementById('modalKirim').style.display='none'" class="btn-action" style="background:#fff; border:1px solid #cbd5e1; color:#334155; margin-right:10px;">Batal</button>
                    <button type="submit" name="simpan_pengiriman" class="btn-action btn-kirim">Simpan & Cetak SJ</button>
                </div>
            </form>
        </div>
    </div>

    <?php include '../../layout/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        <?php if(!empty($swal_script)) echo $swal_script; ?>
        
        function bukaModalKirim(id, nama) {
            document.getElementById('id_order_kirim').value = id;
            document.getElementById('nama_plg_modal').innerText = nama;
            
            // Tampilkan modal dengan Flexbox agar center
            var modal = document.getElementById('modalKirim');
            modal.style.display = 'flex'; 
            
            document.getElementById('list_item_kirim').innerHTML = '<div style="padding:20px; text-align:center;">Loading...</div>';
            
            fetch(`get_items_for_shipping.php?id=${id}`)
                .then(response => response.text())
                .then(html => {
                    document.getElementById('list_item_kirim').innerHTML = html;
                });
        }
        
        window.onclick = function(event) {
            if (event.target == document.getElementById('modalKirim')) {
                document.getElementById('modalKirim').style.display = "none";
            }
        }
    </script>
</body>
</html>