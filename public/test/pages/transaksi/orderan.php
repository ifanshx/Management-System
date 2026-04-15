<?php 
require_once '../../config/database.php';
date_default_timezone_set('Asia/Jakarta');
cek_login(); 

// PROTEKSI: Hanya Admin
if($_SESSION['role'] != 'admin') { 
    echo "<script>window.location='../dashboard.php';</script>";
    exit; 
}

$swal_script = "";
$edit_mode = false;
$data_edit = null;
$items_edit = [];
$is_locked = false; // Flag jika order sudah diproses

// --- 1. PROSES SIMPAN / UPDATE ---
if(isset($_POST['simpan_order'])) {
    $tgl   = $_POST['tanggal'];
    $nama  = mysqli_real_escape_string($conn, $_POST['nama_pelanggan']);
    $ket   = mysqli_real_escape_string($conn, $_POST['keterangan']); 
    
    // Validasi Item Input
    $items = isset($_POST['item_nama']) ? $_POST['item_nama'] : []; 
    $qtys  = isset($_POST['item_qty']) ? $_POST['item_qty'] : [];      
    $total_qty = array_sum($qtys);

    if(!empty($_POST['order_id_edit'])) {
        // --- UPDATE MODE ---
        $id_order = $_POST['order_id_edit'];
        
        // Cek status terkini sebelum update
        $cek_status = mysqli_fetch_assoc(mysqli_query($conn, "SELECT status FROM orderan WHERE id='$id_order'"));
        
        if($cek_status['status'] == 'Pending') {
            // Jika Masih Pending: BOLEH Update Full (Header + Item)
            mysqli_query($conn, "UPDATE orderan SET tanggal='$tgl', nama_pelanggan='$nama', keterangan='$ket', total_qty='$total_qty' WHERE id='$id_order'");
            
            // Reset Item (Aman karena belum ada pengiriman)
            mysqli_query($conn, "DELETE FROM order_items WHERE order_id='$id_order'");
            
            if(!empty($items)) {
                foreach($items as $index => $nama_barang) {
                    $qty_barang = (int)$qtys[$index];
                    if(!empty($nama_barang) && $qty_barang > 0) {
                        $nama_fix = mysqli_real_escape_string($conn, $nama_barang);
                        mysqli_query($conn, "INSERT INTO order_items (order_id, nama_barang, qty, qty_sent) VALUES ('$id_order', '$nama_fix', '$qty_barang', 0)");
                    }
                }
            }
            $swal_title = "Update Berhasil";
            $swal_text  = "Data orderan telah diperbarui.";
            
        } else {
            // Jika Sudah Proses/Selesai: HANYA Update Header (Nama/Ket/Tgl)
            // Item TIDAK diupdate agar qty_sent tidak hilang
            mysqli_query($conn, "UPDATE orderan SET tanggal='$tgl', nama_pelanggan='$nama', keterangan='$ket' WHERE id='$id_order'");
            
            $swal_title = "Info Update";
            $swal_text  = "Hanya data pelanggan yang diubah. Item dikunci karena sudah diproses.";
        }
        
    } else {
        // --- INSERT MODE ---
        $stat  = 'Pending'; 
        mysqli_query($conn, "INSERT INTO orderan (tanggal, nama_pelanggan, keterangan, total_qty, status) VALUES ('$tgl', '$nama', '$ket', '$total_qty', '$stat')");
        $id_order = mysqli_insert_id($conn);

        if($id_order && !empty($items)) {
            foreach($items as $index => $nama_barang) {
                $qty_barang = (int)$qtys[$index];
                if(!empty($nama_barang) && $qty_barang > 0) {
                    $nama_fix = mysqli_real_escape_string($conn, $nama_barang);
                    mysqli_query($conn, "INSERT INTO order_items (order_id, nama_barang, qty, qty_sent) VALUES ('$id_order', '$nama_fix', '$qty_barang', 0)");
                }
            }
        }
        $swal_title = "Order Berhasil";
        $swal_text  = "Data tersimpan. Menunggu verifikasi.";
    }
    
    $swal_script = "Swal.fire({icon: 'success', title: '$swal_title', text: '$swal_text', timer: 2000, showConfirmButton: false}).then(() => { window.location='orderan.php'; });";
}

// --- 2. VERIFIKASI ---
if(isset($_GET['verifikasi_id'])) {
    $oid = $_GET['verifikasi_id'];
    mysqli_query($conn, "UPDATE orderan SET status='Proses' WHERE id='$oid'");
    echo "<script>window.location='orderan.php';</script>";
}

// --- 3. HAPUS ---
if(isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    // Validasi hapus hanya jika belum ada pengiriman
    $cek_kirim = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(qty_sent) as total_sent FROM order_items WHERE order_id='$id'"));
    
    if($cek_kirim['total_sent'] > 0){
        $swal_script = "Swal.fire({icon: 'error', title: 'Gagal Hapus', text: 'Order ini sudah memiliki riwayat pengiriman!'});";
    } else {
        mysqli_query($conn, "DELETE FROM order_items WHERE order_id='$id'"); 
        mysqli_query($conn, "DELETE FROM orderan WHERE id='$id'"); 
        echo "<script>window.location='orderan.php';</script>";
    }
}

// --- 4. AMBIL DATA EDIT ---
if(isset($_GET['edit_id'])) {
    $id_edit = $_GET['edit_id'];
    $q_edit = mysqli_query($conn, "SELECT * FROM orderan WHERE id='$id_edit'");
    if(mysqli_num_rows($q_edit) > 0) {
        $edit_mode = true;
        $data_edit = mysqli_fetch_assoc($q_edit);
        
        // Cek apakah item terkunci (Status bukan pending)
        if($data_edit['status'] != 'Pending') {
            $is_locked = true;
        }

        $q_items = mysqli_query($conn, "SELECT * FROM order_items WHERE order_id='$id_edit'");
        while($row = mysqli_fetch_assoc($q_items)) {
            $items_edit[] = $row;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <?php include '../../layout/header.php'; ?>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <style>
        :root { --accent: #4f46e5; --bg-body: #f8fafc; --text-main: #1e293b; --card-border: #e2e8f0; }
        body { background-color: var(--bg-body); font-family: 'Inter', sans-serif; color: var(--text-main); }
        .content-wrapper { padding: 30px; }
        .page-header { margin-bottom: 30px; }
        .page-title h3 { margin: 0; font-weight: 800; color: #1e293b; font-size: 24px; }
        .modern-card { background: #fff; border-radius: 16px; border: 1px solid var(--card-border); box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02); overflow: hidden; margin-bottom: 30px; }
        .card-header-gradient { background: linear-gradient(135deg, #4f46e5 0%, #4338ca 100%); padding: 20px 25px; color: #fff; display: flex; align-items: center; justify-content: space-between; }
        .card-header-edit { background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); padding: 20px 25px; color: #fff; display: flex; align-items: center; justify-content: space-between; }
        .card-body { padding: 25px; }
        .form-label { font-size: 11px; font-weight: 700; color: #6b7280; text-transform: uppercase; margin-bottom: 6px; display: block; }
        .form-control-lg { height: 45px; border-radius: 10px; border: 1px solid #cbd5e1; width: 100%; padding: 0 15px; font-size: 14px; }
        .item-row { display: flex; gap: 10px; margin-bottom: 10px; align-items: center; }
        .btn-add-item { background: #eff6ff; color: var(--accent); border: 1px dashed var(--accent); width: 100%; padding: 12px; border-radius: 10px; font-weight: 600; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; }
        .remove-icon { color: #ef4444; font-size: 18px; cursor: pointer; }
        .table-custom { width: 100%; border-collapse: collapse; }
        .table-custom th { background: #f8fafc; font-size: 11px; text-transform: uppercase; padding: 15px 20px; color: #64748b; text-align: left; border-bottom: 2px solid #e2e8f0; }
        .table-custom td { padding: 15px 20px; border-bottom: 1px solid #f1f5f9; font-size: 13px; }
        .badge-status { padding: 4px 10px; border-radius: 20px; font-size: 10px; font-weight: 700; text-transform: uppercase; }
        .bg-Pending { background: #fff7ed; color: #c2410c; }
        .bg-Proses { background: #eff6ff; color: #1d4ed8; }
        .bg-Selesai { background: #f0fdf4; color: #15803d; }
        .btn-action { padding: 8px 12px; border-radius: 8px; border: none; cursor: pointer; text-decoration: none; display: inline-flex; gap: 5px; align-items: center; font-size: 11px; font-weight: bold; }
        .btn-verif { background: #10b981; color: white; }
        .btn-edit { background: #f59e0b; color: white; }
        .btn-del { background: #fee2e2; color: #ef4444; }
        .btn-print { background: #0ea5e9; color: white; }
        .btn-history { background: #fff; border: 1px solid #cbd5e1; color: #475569; }
        
        /* --- PERBAIKAN CSS MODAL --- */
        .modal { 
            display: none; 
            position: fixed; 
            z-index: 9999; /* Z-Index Tinggi */
            left: 0; 
            top: 0; 
            width: 100%; 
            height: 100%; 
            background-color: rgba(15, 23, 42, 0.75); 
            backdrop-filter: blur(4px); 
            
            /* Center Flexbox */
            align-items: center;
            justify-content: center;
            padding: 20px;
            box-sizing: border-box;
        }
        
        .modal-content { 
            background-color: #fff; 
            margin: 0; 
            padding: 0; 
            border-radius: 16px; 
            width: 600px; 
            max-width: 100%; 
            max-height: 90vh; /* Maksimal tinggi 90% layar */
            display: flex; 
            flex-direction: column; /* Susunan vertikal */
            
            animation: slideUp 0.3s ease-out; 
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1); 
        }

        @keyframes slideUp { 
            from { transform: translateY(20px); opacity: 0; } 
            to { transform: translateY(0); opacity: 1; } 
        }
    </style>
</head>
<body>
    <?php include '../../layout/sidebar.php'; ?>
    
    <div class="content-wrapper">
        <div class="page-header">
            <div class="page-title">
                <h3>Kelola Orderan</h3>
                <p>Input pesanan baru, edit order, dan verifikasi status produksi.</p>
            </div>
        </div>

        <div class="modern-card">
            <div class="<?php echo $edit_mode ? 'card-header-edit' : 'card-header-gradient'; ?>">
                <div>
                    <h4 style="margin:0; font-weight:800; font-size:16px;">
                        <?php echo $edit_mode ? 'Edit Order #'.$data_edit['id'] : 'Order Baru'; ?>
                    </h4>
                    <p style="margin:2px 0 0; opacity:0.8; font-size:12px;">
                        <?php echo $edit_mode ? 'Status: <b>'.$data_edit['status'].'</b>' : 'Input pesanan baru.'; ?>
                    </p>
                </div>
            </div>
            <div class="card-body">
                <form method="POST">
                    <?php if($edit_mode): ?>
                        <input type="hidden" name="order_id_edit" value="<?php echo $data_edit['id']; ?>">
                    <?php endif; ?>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group" style="margin-bottom:15px;">
                                <label class="form-label">Tanggal</label>
                                <input type="date" name="tanggal" class="form-control-lg" value="<?php echo $edit_mode ? $data_edit['tanggal'] : date('Y-m-d'); ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group" style="margin-bottom:15px;">
                                <label class="form-label">Pelanggan</label>
                                <input type="text" name="nama_pelanggan" class="form-control-lg" placeholder="Contoh: TOKO BERKAH" value="<?php echo $edit_mode ? $data_edit['nama_pelanggan'] : ''; ?>" required>
                            </div>
                        </div>
                    </div>
                    <div class="form-group" style="margin-bottom:20px;">
                        <label class="form-label">Catatan Order</label>
                        <input type="text" name="keterangan" class="form-control-lg" placeholder="Opsional (Packing kayu, dll)..." value="<?php echo $edit_mode ? $data_edit['keterangan'] : ''; ?>">
                    </div>

                    <div style="background:#f9fafb; padding:20px; border-radius:12px; border:1px solid #e5e7eb; margin-bottom:20px;">
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
                            <label class="form-label" style="color:var(--accent); margin:0; font-size:13px;">RINCIAN BARANG</label>
                            <?php if($is_locked): ?>
                                <span style="font-size:11px; background:#fee2e2; color:#b91c1c; padding:3px 8px; border-radius:4px; font-weight:600;">
                                    <i class="fa fa-lock"></i> Item terkunci (Sedang/Sudah Diproses)
                                </span>
                            <?php endif; ?>
                        </div>
                        
                        <div id="items_container">
                            <?php if($edit_mode && !empty($items_edit)): ?>
                                <?php foreach($items_edit as $item): ?>
                                    <div class="item-row">
                                        <input type="text" name="item_nama[]" class="form-control-lg" value="<?php echo htmlspecialchars($item['nama_barang']); ?>" placeholder="Nama Barang" style="flex:3; <?php echo $is_locked ? 'background:#f1f5f9;' : ''; ?>" <?php echo $is_locked ? 'readonly' : 'required'; ?>>
                                        <input type="number" name="item_qty[]" class="form-control-lg" value="<?php echo $item['qty']; ?>" placeholder="Qty" style="flex:1; text-align:center; <?php echo $is_locked ? 'background:#f1f5f9;' : ''; ?>" <?php echo $is_locked ? 'readonly' : 'required'; ?>>
                                        
                                        <?php if(!$is_locked): ?>
                                            <i class="fa fa-times-circle remove-icon" onclick="this.parentNode.remove()"></i>
                                        <?php else: ?>
                                            <div style="width:20px;"></div>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="item-row">
                                    <input type="text" name="item_nama[]" class="form-control-lg" placeholder="Nama Barang" style="flex:3;" required>
                                    <input type="number" name="item_qty[]" class="form-control-lg" placeholder="Qty" style="flex:1; text-align:center;" required>
                                    <i class="fa fa-times-circle remove-icon" onclick="this.parentNode.remove()"></i>
                                </div>
                            <?php endif; ?>
                        </div>

                        <?php if(!$is_locked): ?>
                            <button type="button" class="btn-add-item" onclick="addItem()" style="margin-top:15px;">
                                <i class="fa fa-plus"></i> Tambah Item
                            </button>
                        <?php endif; ?>
                    </div>

                    <div style="text-align:right;">
                        <?php if($edit_mode): ?>
                            <a href="orderan.php" class="btn btn-secondary" style="padding:12px 25px; border-radius:10px; text-decoration:none; margin-right:10px; font-weight:600; color:#64748b;">Batal</a>
                        <?php endif; ?>
                        <button type="submit" name="simpan_order" class="btn btn-primary" style="padding:14px 40px; font-weight:bold; border-radius:10px; background: <?php echo $edit_mode ? '#f59e0b' : 'var(--accent)'; ?>; border:none; color:#fff; cursor:pointer;">
                            <i class="fa <?php echo $edit_mode ? 'fa-save' : 'fa-paper-plane'; ?>"></i> 
                            <?php echo $edit_mode ? 'SIMPAN PERUBAHAN' : 'BUAT ORDERAN'; ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="modern-card">
            <div style="padding:20px 25px; border-bottom:1px solid #f1f5f9; background:#fff; display:flex; align-items:center; gap:10px;">
                <i class="fa fa-list-alt" style="color:var(--accent);"></i>
                <h5 style="margin:0; font-weight:800; color:#1f2937; font-size:16px;">Daftar Orderan</h5>
            </div>
            <div class="table-responsive">
                <table class="table table-custom">
                    <thead>
                        <tr>
                            <th style="padding-left:25px;">Pelanggan</th>
                            <th class="text-center">Total Qty</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $q = mysqli_query($conn, "SELECT * FROM orderan ORDER BY CASE WHEN status='Pending' THEN 1 WHEN status='Proses' THEN 2 ELSE 3 END, tanggal DESC LIMIT 20");
                        if(mysqli_num_rows($q) > 0):
                            while($d = mysqli_fetch_assoc($q)):
                        ?>
                        <tr>
                            <td style="padding-left:25px;">
                                <div style="font-weight:700; color:#1e293b;"><?php echo htmlspecialchars($d['nama_pelanggan']); ?></div>
                                <div style="font-size:11px; color:#64748b; margin-top:3px;">
                                    <?php echo date('d M Y', strtotime($d['tanggal'])); ?> 
                                    <?php if($d['keterangan']) echo " • <span style='color:#d97706'>{$d['keterangan']}</span>"; ?>
                                </div>
                            </td>
                            <td class="text-center">
                                <span style="background:#f1f5f9; padding:5px 10px; border-radius:6px; font-weight:bold; color:#1e293b;"><?php echo $d['total_qty']; ?></span>
                            </td>
                            <td class="text-center">
                                <?php 
                                    $cls = $d['status'] == 'Pending' ? 'bg-Pending' : ($d['status'] == 'Proses' ? 'bg-Proses' : 'bg-Selesai');
                                    echo "<span class='badge-status $cls'>{$d['status']}</span>";
                                ?>
                            </td>
                            <td class="text-center">
                                <div style="display:flex; justify-content:center; gap:5px;">
                                    <?php if($d['status'] == 'Pending'): ?>
                                        <button onclick="verif(<?php echo $d['id']; ?>)" class="btn-action btn-verif" title="Verifikasi"><i class="fa fa-check"></i></button>
                                        <a href="orderan.php?edit_id=<?php echo $d['id']; ?>" class="btn-action btn-edit" title="Edit"><i class="fa fa-pencil-alt"></i></a>
                                        <button onclick="hapus(<?php echo $d['id']; ?>)" class="btn-action btn-del" title="Hapus"><i class="fa fa-trash-alt"></i></button>
                                    <?php else: ?>
                                        <button onclick="printSPK(<?php echo $d['id']; ?>)" class="btn-action btn-print" title="Cetak SPK"><i class="fa fa-print"></i></button>
                                        <a href="orderan.php?edit_id=<?php echo $d['id']; ?>" class="btn-action btn-edit" title="Lihat Detail/Edit Info"><i class="fa fa-eye"></i></a>
                                        <button onclick="lihatHistory(<?php echo $d['id']; ?>)" class="btn-action btn-history" title="History"><i class="fa fa-history"></i></button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; else: ?>
                        <tr><td colspan="4" class="text-center" style="padding:40px;">Belum ada data.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div id="modalHistory" class="modal">
        <div class="modal-content">
            <div style="padding:20px; background:#f8fafc; border-bottom:1px solid #e2e8f0; display:flex; justify-content:space-between; flex-shrink: 0;">
                <h4 style="margin:0;">Riwayat Pengiriman</h4>
                <span onclick="document.getElementById('modalHistory').style.display='none'" style="cursor:pointer; font-size:20px;">&times;</span>
            </div>
            <div id="konten_history" style="padding:20px; overflow-y:auto; flex-grow: 1;"></div>
        </div>
    </div>

    <?php include '../../layout/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        <?php if(!empty($swal_script)) echo $swal_script; ?>

        function addItem() {
            var div = document.createElement("div");
            div.className = "item-row";
            div.innerHTML = `<input type="text" name="item_nama[]" class="form-control-lg" placeholder="Nama Barang" style="flex:3;" required>
                             <input type="number" name="item_qty[]" class="form-control-lg" placeholder="Qty" style="flex:1; text-align:center;" required>
                             <i class="fa fa-times-circle remove-icon" onclick="this.parentNode.remove()"></i>`;
            document.getElementById("items_container").appendChild(div);
        }

        function verif(id) {
            Swal.fire({title: 'Verifikasi?', text: "Status akan berubah jadi PROSES.", icon: 'question', showCancelButton: true, confirmButtonText: 'Ya, Proses', confirmButtonColor: '#10b981'}).then((result) => {
                if (result.isConfirmed) window.location.href = `orderan.php?verifikasi_id=${id}`;
            })
        }
        function hapus(id) {
            Swal.fire({title: 'Hapus?', text: "Hanya bisa jika belum ada pengiriman.", icon: 'warning', showCancelButton: true, confirmButtonText: 'Hapus', confirmButtonColor: '#ef4444'}).then((result) => {
                if (result.isConfirmed) window.location.href = `orderan.php?hapus=${id}`;
            })
        }
        function printSPK(id) { window.open(`cetak_spk.php?id=${id}`, '_blank'); }
        
        function lihatHistory(id) {
            // Gunakan 'flex' agar centering CSS bekerja
            document.getElementById('modalHistory').style.display = 'flex';
            document.getElementById('konten_history').innerHTML = 'Loading...';
            fetch(`get_shipping_history.php?id=${id}`).then(r => r.text()).then(html => { document.getElementById('konten_history').innerHTML = html; });
        }
        
        window.onclick = function(e) { if(e.target == document.getElementById('modalHistory')) document.getElementById('modalHistory').style.display = 'none'; }
    </script>
</body>
</html>