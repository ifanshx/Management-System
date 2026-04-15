<?php
// 1. KONEKSI & SESSION
require_once '../../config/database.php';
if (!isset($_SESSION['status'])) { header("Location: ../../login.php"); exit; }

// =================================================================================
// [AJAX] LOGIKA: GET DATA SUPPLIER HISTORY
// =================================================================================
if (isset($_POST['get_supplier_history'])) {
    $id_bahan = intval($_POST['id']);
    
    // Query: Menghitung total supply per supplier
    $sql = "SELECT 
                supplier, 
                SUM(jumlah_masuk) as total_qty, 
                SUM(total_harga) as total_spending,
                MAX(tanggal_masuk) as last_supply
            FROM incoming_bahan_baku 
            WHERE bahan_baku_id = '$id_bahan' 
            GROUP BY supplier
            ORDER BY last_supply DESC"; 
            
    $result = mysqli_query($conn, $sql);
    
    if (mysqli_num_rows($result) > 0) {
        echo '<div class="table-responsive">
                <table class="table-custom" style="margin-top:0;">
                <thead>
                    <tr style="background:#f1f5f9;">
                        <th>Supplier</th>
                        <th style="text-align:right;">Estimasi Harga/Unit</th>
                        <th style="text-align:center;">Total Supply</th>
                        <th style="text-align:right;">Terakhir Restock</th>
                    </tr>
                </thead>
                <tbody>';
        while ($row = mysqli_fetch_assoc($result)) {
            // Hitung harga satuan berdasarkan history belanja di supplier tersebut
            $price_per_unit = 0;
            if($row['total_qty'] > 0){
                $price_per_unit = $row['total_spending'] / $row['total_qty'];
            }

            $rp  = number_format($price_per_unit, 0, ',', '.');
            $qty = number_format($row['total_qty'], 0, ',', '.');
            $tgl = date('d/m/Y', strtotime($row['last_supply']));
            
            echo "<tr>
                    <td style='font-weight:600; color:#334155;'>{$row['supplier']}</td>
                    <td style='text-align:right; color:#2563eb;'>Rp {$rp}</td>
                    <td style='text-align:center;'>{$qty}</td>
                    <td style='text-align:right; font-size:11px; color:#64748b;'>{$tgl}</td>
                  </tr>";
        }
        echo '</tbody></table></div>';
    } else {
        echo '<div style="padding:30px; text-align:center; color:#94a3b8;">
                <i class="fa fa-box-open fa-2x" style="margin-bottom:10px; opacity:0.5;"></i><br>
                <span style="font-size:13px;">Belum ada riwayat pembelian (restock) untuk barang ini.</span>
              </div>';
    }
    exit; // Stop script for AJAX response
}

// =================================================================================
// LOGIKA CRUD MASTER BAHAN
// =================================================================================

// 1. TAMBAH / EDIT MASTER BAHAN
if (isset($_POST['simpan_master'])) {
    $id       = $_POST['id_bahan'];
    $kode     = trim($_POST['kode_bahan']);
    $nama     = trim($_POST['nama_bahan']);
    $jenis    = $_POST['jenis_bahan'];
    $satuan   = $_POST['satuan'];
    $min_stok = floatval($_POST['stok_minimum']);
    $lokasi   = trim($_POST['lokasi_penyimpanan']);
    
    // Auto Generate Kode jika kosong
    if(empty($kode)) {
        $prefix = strtoupper(substr($nama, 0, 3));
        $rand = rand(100, 999);
        $kode = "MAT-" . $prefix . "-" . $rand;
    }

    if(empty($id)) {
        // INSERT MASTER BARU
        $stmt = $conn->prepare("INSERT INTO bahan_baku (kode_bahan, nama_bahan, jenis_bahan, satuan, stok_minimum, lokasi_penyimpanan, stok, harga_satuan) VALUES (?, ?, ?, ?, ?, ?, 0, 0)");
        $stmt->bind_param("ssssss", $kode, $nama, $jenis, $satuan, $min_stok, $lokasi);
        
        if($stmt->execute()) {
            $_SESSION['success'] = "Master bahan baku berhasil dibuat.";
        } else {
            $_SESSION['error'] = "Gagal: " . $conn->error;
        }
    } else {
        // UPDATE DATA MASTER
        $stmt = $conn->prepare("UPDATE bahan_baku SET kode_bahan=?, nama_bahan=?, jenis_bahan=?, satuan=?, stok_minimum=?, lokasi_penyimpanan=? WHERE id=?");
        $stmt->bind_param("ssssssi", $kode, $nama, $jenis, $satuan, $min_stok, $lokasi, $id);
        
        if($stmt->execute()) {
            $_SESSION['success'] = "Data master bahan diperbarui.";
        } else {
            $_SESSION['error'] = "Gagal update: " . $conn->error;
        }
    }
    header("Location: stok_bahan_baku.php"); exit;
}

// 2. HAPUS BAHAN
if (isset($_GET['hapus'])) {
    $id = intval($_GET['hapus']);
    $cek_in  = $conn->query("SELECT id FROM incoming_bahan_baku WHERE bahan_baku_id = $id LIMIT 1");

    if($cek_in->num_rows > 0) {
        $_SESSION['error'] = "Gagal hapus! Bahan ini memiliki riwayat transaksi masuk.";
    } else {
        $conn->query("DELETE FROM bahan_baku WHERE id = $id");
        $_SESSION['success'] = "Data bahan baku dihapus.";
    }
    header("Location: stok_bahan_baku.php"); exit;
}

// 3. KELOLA JENIS/KATEGORI
if (isset($_POST['tambah_jenis'])) {
    $nama_jenis = trim($_POST['nama_jenis_baru']);
    if(!empty($nama_jenis)) {
        $conn->query("INSERT INTO jenis_bahan_baku (nama_jenis) VALUES ('$nama_jenis')");
        $_SESSION['success'] = "Kategori bahan ditambahkan.";
    }
    header("Location: stok_bahan_baku.php"); exit;
}

if (isset($_GET['hapus_jenis'])) {
    $id_j = intval($_GET['hapus_jenis']);
    $cek = $conn->query("SELECT id FROM bahan_baku WHERE jenis_bahan = (SELECT nama_jenis FROM jenis_bahan_baku WHERE id=$id_j) LIMIT 1");
    
    if($cek->num_rows == 0) {
        $conn->query("DELETE FROM jenis_bahan_baku WHERE id=$id_j");
        $_SESSION['success'] = "Kategori dihapus.";
    } else {
        $_SESSION['error'] = "Kategori sedang digunakan oleh bahan baku.";
    }
    header("Location: stok_bahan_baku.php"); exit;
}

// DATA SUMMARY DASHBOARD
$total_item  = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM bahan_baku"));

// Hitung Total Nilai Aset (Stok Saat Ini * Harga Satuan di Master)
$row_asset   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(stok * harga_satuan) as val FROM bahan_baku"));
$total_asset = $row_asset['val'] ?? 0;

$stok_nipis  = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM bahan_baku WHERE stok <= stok_minimum AND stok > 0"));
$stok_habis  = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM bahan_baku WHERE stok = 0"));

// AMBIL DATA TABEL UTAMA
$q_bahan = mysqli_query($conn, "SELECT * FROM bahan_baku ORDER BY nama_bahan ASC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    
    <?php include '../../layout/header.php'; ?>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    
    <style>
        /* RESET & BASE */
        * { box-sizing: border-box; }
        body { background-color: #f8fafc; font-family: 'Inter', sans-serif; -webkit-font-smoothing: antialiased; }
        .content-wrapper { padding: 20px 30px !important; transition: all 0.3s; }

        /* HEADER SECTION */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            gap: 15px;
            flex-wrap: wrap;
        }
        .header-title h2 { font-weight: 800; color: #1e293b; margin: 0; font-size: 24px; }
        .header-title p { color: #64748b; font-size: 13px; margin-top: 4px; margin-bottom: 0; }
        .header-actions { display: flex; gap: 10px; }

        /* --- STATS GRID --- */
        .stats-grid { 
            display: grid; 
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); 
            gap: 15px; 
            margin-bottom: 25px; 
        }
        .stat-card { 
            background: #fff; border-radius: 10px; padding: 15px 20px;
            border: 1px solid #e2e8f0; box-shadow: 0 2px 4px rgba(0,0,0,0.02); 
            display: flex; flex-direction: column; justify-content: center;
        }
        .stat-label { font-size: 11px; color: #64748b; font-weight: 700; text-transform: uppercase; margin-bottom: 5px; letter-spacing: 0.5px; }
        .stat-val { font-size: 22px; font-weight: 800; color: #1e293b; }

        /* --- TABLE & CARD --- */
        .card-custom { background: #fff; border-radius: 12px; border: 1px solid #e2e8f0; margin-bottom: 25px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02); }
        .card-header-c { padding: 15px 20px; background: #fff; border-bottom: 1px solid #e2e8f0; display:flex; justify-content:space-between; align-items:center; gap: 10px; }
        .card-title-text { font-size:15px; font-weight:700; color:#334155; margin: 0; white-space: nowrap; }
        
        .table-responsive { width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch; }
        .table-custom { width: 100%; border-collapse: collapse; white-space: nowrap; }
        .table-custom th { text-align: left; padding: 12px 15px; background: #f8fafc; color: #64748b; font-size: 11px; text-transform: uppercase; border-bottom: 1px solid #e2e8f0; font-weight: 700; }
        .table-custom td { padding: 12px 15px; border-bottom: 1px solid #f1f5f9; font-size: 13px; color: #334155; vertical-align: middle; }
        .table-custom tr:hover td { background: #f8fafc; }
        
        .badge { padding: 4px 10px; border-radius: 6px; font-size: 11px; font-weight: 700; display: inline-flex; align-items: center; gap: 5px; }
        .st-ok { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
        .st-low { background: #fef9c3; color: #854d0e; border: 1px solid #fde047; }
        .st-zero { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
        .bg-code { background: #eff6ff; color: #2563eb; border: 1px solid #dbeafe; font-family: monospace; }

        .btn-act { padding: 9px 14px; border-radius: 8px; font-size: 12px; font-weight: 600; cursor: pointer; border: none; color: white; display: inline-flex; justify-content: center; align-items: center; gap: 6px; text-decoration: none; transition: 0.2s; white-space: nowrap; }
        .btn-act:hover { transform: translateY(-1px); opacity: 0.9; }
        .btn-blue { background: #2563eb; } .btn-green { background: #10b981; } .btn-yellow { background: #f59e0b; } 
        
        .btn-icon { width: 30px; height: 30px; border-radius: 6px; display: inline-flex; justify-content: center; align-items: center; cursor: pointer; color: white; border: none; margin-right: 4px; transition: 0.2s; }
        .bi-edit { background: #fff; border: 1px solid #cbd5e1; color: #334155; } .bi-edit:hover { background: #f1f5f9; color: #2563eb; }
        .bi-del { background: #fff; border: 1px solid #cbd5e1; color: #ef4444; } .bi-del:hover { background: #fef2f2; }
        .bi-view { background: #fff; border: 1px solid #cbd5e1; color: #8b5cf6; } .bi-view:hover { background: #f5f3ff; color: #7c3aed; }

        /* --- PERBAIKAN MODAL AGAR TIDAK TERPOTONG --- */
        .modal-overlay { 
            display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; 
            background: rgba(15, 23, 42, 0.6); z-index: 9999; align-items: center; justify-content: center; backdrop-filter: blur(2px);
            padding: 20px; /* Padding agar tidak mentok layar HP */
            box-sizing: border-box;
        }
        .modal-box { 
            background: white; 
            width: 100%; 
            max-width: 600px; /* Lebar ditambah agar kolom grid lebih lega */
            padding: 0; 
            border-radius: 12px; 
            box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1); 
            animation: slideDown 0.3s; 
            position: relative; 
            overflow: hidden;
            display: flex; flex-direction: column; 
            max-height: 90vh; /* Agar bisa scroll jika layar pendek */
        }
        .modal-header { padding: 20px 25px; border-bottom: 1px solid #e2e8f0; display:flex; justify-content:space-between; align-items:center; background:#fff; flex-shrink: 0; }
        
        /* Body dengan Scrollbar jika konten panjang */
        .modal-body { 
            padding: 25px; 
            overflow-y: auto; 
            flex: 1; 
        }
        
        .modal-footer { padding: 15px 25px; border-top: 1px solid #e2e8f0; background:#f8fafc; text-align:right; flex-shrink: 0; }
        
        @keyframes slideDown { from {transform: translateY(-20px); opacity: 0;} to {transform: translateY(0); opacity: 1;} }

        /* --- FORM STYLING --- */
        .search-box { position: relative; width: auto; }
        .search-box input { padding: 9px 12px 9px 35px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 13px; width: 220px; outline: none; transition: width 0.3s; }
        .search-box input:focus { border-color: #2563eb; width: 250px; }
        .search-box i { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #94a3b8; }

        /* Grid Layout */
        .form-grid { 
            display: grid; 
            grid-template-columns: 1fr 1fr; /* Dua kolom */
            gap: 20px; /* Jarak antar kolom diperlebar */
            margin-bottom: 15px; 
        }
        
        /* Label agar tidak terpotong */
        .form-label { 
            display: flex; 
            align-items: center; 
            flex-wrap: wrap; /* Izinkan text turun jika terlalu sempit */
            font-size: 12px; 
            font-weight: 600; 
            color: #334155; 
            margin-bottom: 8px; 
            line-height: 1.4; /* Jarak baris agar text tidak mepet */
        }
        
        .form-control { width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 13px; box-sizing: border-box; transition: 0.2s; }
        .form-control:focus { border-color: #2563eb; outline: none; box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1); }

        /* --- MEDIA QUERIES --- */
        @media (max-width: 768px) { 
            .content-wrapper { padding: 15px !important; }
            .page-header { flex-direction: column; align-items: flex-start; gap: 15px; }
            .header-actions { width: 100%; display: grid; grid-template-columns: 1fr 1fr; }
            .header-actions .btn-act { width: 100%; }
            .header-actions a.btn-green { grid-column: span 2; } 
            .stats-grid { grid-template-columns: 1fr 1fr; gap: 10px; }
            .stat-val { font-size: 18px; }
            .card-header-c { flex-direction: column; align-items: flex-start; gap: 12px; }
            .search-box { width: 100%; }
            .search-box input { width: 100%; }
            .search-box input:focus { width: 100%; }
            
            /* Pada layar HP, form jadi 1 kolom saja supaya text panjang muat */
            .form-grid { grid-template-columns: 1fr; gap: 15px; } 
            .modal-box { max-height: 95vh; }
            .modal-body { padding: 20px; }
        }
    </style>
</head>
<body>
    <?php include '../../layout/sidebar.php'; ?>
    
    <div class="content-wrapper">
        
        <div class="page-header">
            <div class="header-title">
                <h2>Stok Bahan Baku</h2>
                <p>Master data material & monitoring nilai aset.</p>
            </div>
            <div class="header-actions">
                <a href="procurement_material.php" class="btn-act btn-green">
                    <i class="fa fa-cart-arrow-down"></i> Restock
                </a>
                <button onclick="openModalJenis()" class="btn-act btn-yellow"><i class="fa fa-layer-group"></i> Kategori</button>
                <button onclick="openModalMaster()" class="btn-act btn-blue"><i class="fa fa-plus"></i> Master Baru</button>
            </div>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-label">Total Item</div>
                <div class="stat-val"><?= number_format($total_item) ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Stok Menipis</div>
                <div class="stat-val" style="color:#eab308;"><?= $stok_nipis ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Stok Habis</div>
                <div class="stat-val" style="color:#ef4444;"><?= $stok_habis ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Total Nilai Aset</div>
                <div class="stat-val" style="color:#10b981;">Rp <?= number_format($total_asset, 0, ',', '.') ?></div>
            </div>
        </div>

        <div class="card-custom">
            <div class="card-header-c">
                <h3 class="card-title-text">Daftar Inventory</h3>
                <div class="search-box">
                    <i class="fa fa-search"></i>
                    <input type="text" id="searchInput" placeholder="Cari Kode atau Nama...">
                </div>
            </div>
            
            <div class="table-responsive">
                <table class="table-custom">
                    <thead>
                        <tr>
                            <th>Bahan Baku</th>
                            <th>Kategori / Rak</th>
                            <th>Stok</th>
                            <th>Status</th>
                            <th style="text-align:center;">Opsi</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody">
                        <?php while($d = mysqli_fetch_assoc($q_bahan)): ?>
                        <?php 
                            $stok = floatval($d['stok']);
                            $min  = floatval($d['stok_minimum']);
                            if($stok == 0) { $cls = 'st-zero'; $icon='fa-times-circle'; $txt = 'Habis'; } 
                            elseif($stok <= $min) { $cls = 'st-low'; $icon='fa-exclamation-circle'; $txt = 'Menipis'; } 
                            else { $cls = 'st-ok'; $icon='fa-check-circle'; $txt = 'Aman'; }
                            $search_txt = strtolower($d['nama_bahan'] . ' ' . $d['kode_bahan']);
                        ?>
                        <tr class="search-item" data-search="<?= $search_txt ?>">
                            <td>
                                <div style="font-weight:700; color:#334155; font-size:14px;"><?= $d['nama_bahan'] ?></div>
                                <span class="badge bg-code"><?= $d['kode_bahan'] ?></span>
                            </td>
                            <td>
                                <div style="font-weight:600; font-size:13px;"><?= $d['jenis_bahan'] ?></div>
                                <div style="font-size:11px; color:#94a3b8;"><i class="fa fa-map-marker-alt"></i> <?= $d['lokasi_penyimpanan'] ?: '-' ?></div>
                            </td>
                            <td>
                                <div style="font-weight:700; font-size:14px;"><?= $stok ?> <span style="font-weight:400; font-size:11px; color:#64748b;"><?= $d['satuan'] ?></span></div>
                            </td>
                            <td>
                                <span class="badge <?= $cls ?>"><i class="fa <?= $icon ?>"></i> <?= $txt ?></span>
                            </td>
                            <td style="text-align:center;">
                                <button onclick="lihatSupplier(<?= $d['id'] ?>, '<?= addslashes($d['nama_bahan']) ?>')" class="btn-icon bi-view" title="Lihat Supplier"><i class="fa fa-eye"></i></button>
                                
                                <button onclick='editMaster(<?= json_encode($d) ?>)' class="btn-icon bi-edit" title="Edit Master"><i class="fa fa-pen"></i></button>
                                <button onclick="hapusMaster(<?= $d['id'] ?>, '<?= addslashes($d['nama_bahan']) ?>')" class="btn-icon bi-del" title="Hapus"><i class="fa fa-trash"></i></button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div id="modalMaster" class="modal-overlay">
        <div class="modal-box">
            <div class="modal-header">
                <h3 id="modalTitle" style="margin:0; color:#1e293b; font-size:16px;">Tambah Master Bahan</h3>
                <span onclick="closeModal('modalMaster')" style="cursor:pointer; color:#94a3b8; font-size:24px;">&times;</span>
            </div>
            
            <form method="POST" style="display:flex; flex-direction:column; flex:1;">
                <div class="modal-body">
                    <input type="hidden" name="id_bahan" id="id_bahan">
                    
                    <div class="form-grid">
                        <div>
                            <label class="form-label">Kode Bahan (Auto jika kosong)</label>
                            <input type="text" name="kode_bahan" id="kode_bahan" class="form-control" placeholder="Contoh: MAT-001">
                        </div>
                        <div>
                            <label class="form-label">Nama Bahan <span style="color:red; margin-left:3px;">*</span></label>
                            <input type="text" name="nama_bahan" id="nama_bahan" class="form-control" required placeholder="Nama Material">
                        </div>
                    </div>

                    <div class="form-grid">
                        <div>
                            <label class="form-label">Kategori <span style="color:red; margin-left:3px;">*</span></label>
                            <select name="jenis_bahan" id="jenis_bahan" class="form-control" required>
                                <option value="">-- Pilih --</option>
                                <?php $qj = $conn->query("SELECT * FROM jenis_bahan_baku ORDER BY nama_jenis ASC"); while($j=$qj->fetch_assoc()): ?>
                                    <option value="<?= $j['nama_jenis'] ?>"><?= $j['nama_jenis'] ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Satuan <span style="color:red; margin-left:3px;">*</span></label>
                            <select name="satuan" id="satuan" class="form-control" required>
                                <option value="Pcs">Pcs</option><option value="Meter">Meter</option>
                                <option value="Kg">Kg</option><option value="Lembar">Lembar</option>
                                <option value="Batang">Batang</option><option value="Roll">Roll</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-grid">
                        <div>
                            <label class="form-label">Stok Minimum (Alert)</label>
                            <input type="number" name="stok_minimum" id="stok_minimum" class="form-control" value="5">
                        </div>
                        <div>
                            <label class="form-label">Lokasi Rak</label>
                            <input type="text" name="lokasi_penyimpanan" id="lokasi_penyimpanan" class="form-control" placeholder="A-01">
                        </div>
                    </div>

                    <div style="margin-top:10px; background:#eff6ff; padding:12px; border-radius:8px; font-size:12px; color:#1e40af; border:1px solid #dbeafe; display:flex; gap:10px;">
                        <i class="fa fa-info-circle" style="margin-top:2px;"></i> 
                        <div>
                            <strong>Catatan:</strong><br>
                            Stok awal dan Harga Beli diinput melalui menu <b>Pengadaan</b> agar tercatat riwayat supplier dan perhitungan harga valid.
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" onclick="closeModal('modalMaster')" class="btn-act" style="background:#fff; border:1px solid #cbd5e1; color:#64748b; margin-right:8px;">Batal</button>
                    <button type="submit" name="simpan_master" class="btn-act btn-blue">Simpan Data</button>
                </div>
            </form>
        </div>
    </div>

    <div id="modalJenis" class="modal-overlay">
        <div class="modal-box" style="max-width:450px;">
            <div class="modal-header">
                <h3 style="margin:0; font-size:16px; color:#1e293b;">Kelola Kategori Bahan</h3>
                <span onclick="closeModal('modalJenis')" style="cursor:pointer; font-size:24px; color:#94a3b8;">&times;</span>
            </div>
            <div class="modal-body">
                <form method="POST" style="display:flex; gap:10px; margin-bottom:20px;">
                    <input type="text" name="nama_jenis_baru" class="form-control" placeholder="Nama Kategori Baru" required>
                    <button type="submit" name="tambah_jenis" class="btn-act btn-blue" style="white-space:nowrap;">+ Tambah</button>
                </form>

                <div style="max-height:250px; overflow-y:auto; border:1px solid #e2e8f0; border-radius:8px;">
                    <?php 
                    $list_jenis = mysqli_query($conn, "SELECT * FROM jenis_bahan_baku ORDER BY nama_jenis ASC");
                    if(mysqli_num_rows($list_jenis) > 0):
                        while($lj = mysqli_fetch_assoc($list_jenis)):
                    ?>
                    <div style="padding:10px 15px; border-bottom:1px solid #f1f5f9; display:flex; justify-content:space-between; align-items:center;">
                        <span style="font-size:13px; font-weight:600; color:#334155;"><?= $lj['nama_jenis'] ?></span>
                        <a href="?hapus_jenis=<?= $lj['id'] ?>" onclick="return confirm('Hapus kategori ini?')" style="color:#ef4444; font-size:12px; text-decoration:none;"><i class="fa fa-trash"></i></a>
                    </div>
                    <?php endwhile; else: ?>
                    <div style="padding:15px; text-align:center; color:#94a3b8; font-size:12px;">Belum ada data kategori</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div id="modalSupplier" class="modal-overlay">
        <div class="modal-box" style="max-width:600px;">
            <div class="modal-header">
                <div>
                    <h3 style="margin:0; font-size:16px; color:#1e293b;">Riwayat Supplier</h3>
                    <p id="supplierItemName" style="margin:2px 0 0; font-size:12px; color:#64748b;">Loading...</p>
                </div>
                <span onclick="closeModal('modalSupplier')" style="cursor:pointer; font-size:24px; color:#94a3b8;">&times;</span>
            </div>
            <div class="modal-body" id="supplierContent" style="padding:0; min-height:150px; position:relative;">
                <div style="position:absolute; top:50%; left:50%; transform:translate(-50%, -50%); color:#cbd5e1;">
                    <i class="fa fa-spinner fa-spin fa-2x"></i>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="closeModal('modalSupplier')" class="btn-act" style="background:#fff; border:1px solid #cbd5e1; color:#64748b;">Tutup</button>
            </div>
        </div>
    </div>

    <?php include '../../layout/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // ALERT HANDLER
        <?php if(isset($_SESSION['success'])): ?>
            Swal.fire({icon:'success', title:'Berhasil', html:'<?= $_SESSION['success'] ?>', timer:2000, showConfirmButton:false});
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>
        
        <?php if(isset($_SESSION['error'])): ?>
            Swal.fire({icon:'error', title:'Gagal', text:'<?= $_SESSION['error'] ?>'});
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        // MODAL LOGIC
        function openModalMaster() {
            document.getElementById('id_bahan').value = '';
            document.getElementById('kode_bahan').value = '';
            document.getElementById('nama_bahan').value = '';
            document.getElementById('stok_minimum').value = '5';
            document.getElementById('lokasi_penyimpanan').value = '';
            document.getElementById('modalTitle').innerText = 'Tambah Master Bahan';
            document.getElementById('modalMaster').style.display = 'flex';
        }

        function openModalJenis() {
            document.getElementById('modalJenis').style.display = 'flex';
        }

        function closeModal(id) {
            document.getElementById(id).style.display = 'none';
        }

        function editMaster(d) {
            document.getElementById('id_bahan').value = d.id;
            document.getElementById('kode_bahan').value = d.kode_bahan;
            document.getElementById('nama_bahan').value = d.nama_bahan;
            document.getElementById('jenis_bahan').value = d.jenis_bahan;
            document.getElementById('satuan').value = d.satuan;
            document.getElementById('stok_minimum').value = d.stok_minimum;
            document.getElementById('lokasi_penyimpanan').value = d.lokasi_penyimpanan;
            document.getElementById('modalTitle').innerText = 'Edit Data Master';
            document.getElementById('modalMaster').style.display = 'flex';
        }

        function hapusMaster(id, nama) {
            Swal.fire({
                title: 'Hapus Bahan?',
                text: "Menghapus master data: " + nama,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#cbd5e1',
                confirmButtonText: 'Ya, Hapus',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = '?hapus=' + id;
                }
            })
        }

        // FUNGSI LIHAT SUPPLIER (AJAX)
        function lihatSupplier(id, nama) {
            $('#supplierItemName').text(nama);
            $('#modalSupplier').css('display', 'flex');
            $('#supplierContent').html('<div style="padding:40px; text-align:center; color:#cbd5e1;"><i class="fa fa-spinner fa-spin fa-2x"></i><br>Mengambil data...</div>');

            $.ajax({
                url: '', // Post ke file ini sendiri
                type: 'POST',
                data: { get_supplier_history: true, id: id },
                success: function(response) {
                    $('#supplierContent').html(response);
                },
                error: function() {
                    $('#supplierContent').html('<div style="padding:20px; text-align:center; color:red;">Gagal mengambil data.</div>');
                }
            });
        }

        // Live Search
        document.getElementById('searchInput').addEventListener('keyup', function() {
            let filter = this.value.toLowerCase();
            let rows = document.querySelectorAll('.search-item');
            rows.forEach(row => {
                let txt = row.getAttribute('data-search');
                row.style.display = txt.includes(filter) ? '' : 'none';
            });
        });

        // Close Modal on Outside Click
        window.onclick = function(event) {
            if (event.target.classList.contains('modal-overlay')) {
                event.target.style.display = "none";
            }
        }
    </script>
</body>
</html>