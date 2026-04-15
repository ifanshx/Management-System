<?php
// 1. KONEKSI & SESSION
require_once '../../config/database.php';
if (!isset($_SESSION['status'])) { header("Location: ../../login.php"); exit; }

// --- LOGIKA PHP: KATEGORI BARANG ---
if (isset($_POST['tambah_kategori'])) {
    $kat_baru = trim($_POST['nama_kategori_baru']);
    if(!empty($kat_baru)) {
        $stmt = $conn->prepare("SELECT id FROM kategori_barang WHERE nama_kategori = ?");
        $stmt->bind_param("s", $kat_baru);
        $stmt->execute();
        if($stmt->get_result()->num_rows == 0){
            $stmt_ins = $conn->prepare("INSERT INTO kategori_barang (nama_kategori) VALUES (?)");
            $stmt_ins->bind_param("s", $kat_baru);
            $stmt_ins->execute();
        }
    }
    echo "<script>window.location.href='stok_barang.php';</script>";
}

if (isset($_GET['hapus_kategori'])) {
    $id_kat = intval($_GET['hapus_kategori']);
    
    // Ambil nama kategori
    $d_kat = $conn->query("SELECT nama_kategori FROM kategori_barang WHERE id=$id_kat")->fetch_assoc();
    $nama_kat = $d_kat['nama_kategori'];

    // Cek ketergantungan
    $cek = $conn->query("SELECT id FROM barang WHERE kategori = '$nama_kat'");
    
    if($cek->num_rows == 0) {
        $conn->query("DELETE FROM kategori_barang WHERE id = $id_kat");
    }
    echo "<script>window.location.href='stok_barang.php';</script>";
}

// --- LOGIKA PHP: BARANG (CRUD) ---
if (isset($_POST['simpan_barang'])) {
    $kode   = trim($_POST['kode_barang']);
    $nama   = trim($_POST['nama_barang']);
    $kat    = $_POST['kategori'];
    $stok   = intval($_POST['stok']);
    $lokasi = trim($_POST['lokasi_rak']);
    $modal  = floatval(str_replace('.', '', $_POST['harga_modal']));
    $jual   = floatval(str_replace('.', '', $_POST['harga_jual']));
    $id     = $_POST['id_barang'];

    // Auto-Generate Kode
    if(empty($kode)){
        $prefix = strtoupper(substr($nama, 0, 3));
        $rand = rand(100, 999);
        $kode = "BRG-" . $prefix . "-" . $rand;
    }

    // Upload Gambar
    $gambar_val = "";
    $sql_img_upd = "";
    
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === 0) {
        $target_dir = "../../assets/uploads/products/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
        
        $ext = pathinfo($_FILES["gambar"]["name"], PATHINFO_EXTENSION);
        $new_name = time() . '_' . rand(100, 999) . '.' . $ext;
        
        if (move_uploaded_file($_FILES["gambar"]["tmp_name"], $target_dir . $new_name)) {
            $gambar_val = $new_name;
            $sql_img_upd = ", gambar='$new_name'";
        }
    }

    if(empty($id)) {
        // INSERT
        $cek = $conn->query("SELECT id FROM barang WHERE kode_barang = '$kode'");
        if($cek->num_rows > 0){
            $err_msg = "Kode Barang sudah digunakan!";
        } else {
            $img_name = $gambar_val ?: 'default.png';
            $stmt = $conn->prepare("INSERT INTO barang (kode_barang, nama_barang, kategori, stok, harga_modal, harga_jual, lokasi_rak, gambar) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssidsss", $kode, $nama, $kat, $stok, $modal, $jual, $lokasi, $img_name);
            
            if($stmt->execute()) $success_msg = "Produk berhasil ditambahkan";
            else $err_msg = "Gagal simpan database";
        }
    } else {
        // UPDATE
        $sql = "UPDATE barang SET kode_barang=?, nama_barang=?, kategori=?, stok=?, harga_modal=?, harga_jual=?, lokasi_rak=? $sql_img_upd WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssidssi", $kode, $nama, $kat, $stok, $modal, $jual, $lokasi, $id);
        
        if($stmt->execute()) $success_msg = "Produk berhasil diperbarui";
        else $err_msg = "Gagal update database";
    }
}

// Hapus Barang
if (isset($_GET['hapus_barang'])) {
    $id = intval($_GET['hapus_barang']);
    $d_g = $conn->query("SELECT gambar FROM barang WHERE id='$id'")->fetch_assoc();
    
    if($conn->query("DELETE FROM barang WHERE id=$id")) {
        if($d_g['gambar']!='default.png' && file_exists("../../assets/uploads/products/".$d_g['gambar'])){
            unlink("../../assets/uploads/products/".$d_g['gambar']);
        }
        $success_msg = "Produk berhasil dihapus";
    }
}

// --- DATA SUMMARY ---
$total_item  = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM barang"));
$row_asset   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(stok * harga_modal) as val FROM barang"));
$total_asset = $row_asset['val'] ?? 0;
$stok_nipis  = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM barang WHERE stok <= 5 AND stok > 0"));
$stok_habis  = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM barang WHERE stok = 0"));

// Ambil Data Barang
$q_barang = mysqli_query($conn, "SELECT * FROM barang ORDER BY CASE WHEN stok = 0 THEN 0 WHEN stok <= 5 THEN 1 ELSE 2 END ASC, nama_barang ASC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    
    <?php include '../../layout/header.php'; ?>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    
    <style>
        /* BASE STYLE */
        * { box-sizing: border-box; }
        body { background-color: #f1f5f9; font-family: 'Inter', sans-serif; -webkit-font-smoothing: antialiased; }
        
        /* CONTENT WRAPPER */
        .content-wrapper { padding: 20px 30px !important; transition: all 0.3s; }

        /* HEADER SECTION RESPONSIVE */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap; /* Agar bisa turun ke bawah */
            gap: 15px;
        }
        .page-title h2 { font-weight: 800; color: #1e293b; margin: 0; font-size: 22px; }
        .page-title p { color: #64748b; font-size: 13px; margin-top: 4px; margin-bottom: 0; }
        .header-actions { display: flex; gap: 8px; }

        /* Card Styles */
        .card-custom { background: #fff; border-radius: 12px; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); margin-bottom: 25px; overflow: hidden; }
        
        .card-header-c { 
            padding: 15px 20px; 
            background: #f8fafc; 
            border-bottom: 1px solid #e2e8f0; 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            gap: 10px;
        }
        .card-title-text { font-weight: 700; color: #334155; white-space: nowrap; }

        /* INPUT SEARCH */
        #searchInput {
            padding: 8px 12px; 
            border: 1px solid #cbd5e1; 
            border-radius: 6px; 
            font-size: 13px; 
            width: 250px;
            transition: width 0.3s;
        }

        /* Stats Grid (KOTAK KECIL) */
        .stats-grid { 
            display: grid; 
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); 
            gap: 15px; 
            margin-bottom: 25px; 
        }
        .stat-card { 
            background: #fff; border-radius: 10px; padding: 15px;
            border: 1px solid #e2e8f0; box-shadow: 0 2px 4px rgba(0,0,0,0.05); 
        }
        .stat-label { font-size:10px; color:#64748b; font-weight:700; text-transform:uppercase; margin-bottom: 5px; }
        .stat-val { font-size:20px; font-weight:800; color:#1e293b; word-break: break-all; }

        /* Table Styles */
        .table-responsive { width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch; }
        .table-custom { width: 100%; border-collapse: collapse; white-space: nowrap; /* Mencegah teks turun baris */ }
        .table-custom th { text-align: left; padding: 12px 15px; background: #f8fafc; color: #64748b; font-size: 11px; text-transform: uppercase; border-bottom: 1px solid #e2e8f0; font-weight: 700; }
        .table-custom td { padding: 10px 15px; border-bottom: 1px solid #f1f5f9; font-size: 13px; color: #334155; vertical-align: middle; }
        .table-custom tr:hover td { background: #f8fafc; }
        
        /* Badges */
        .badge { padding: 4px 10px; border-radius: 6px; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; }
        .badge-kode { background: #eff6ff; color: #2563eb; border: 1px solid #dbeafe; font-family: monospace; }
        
        .status-dot { display: inline-block; width: 6px; height: 6px; border-radius: 50%; margin-right: 5px; }
        .st-ok { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; } .dot-ok { background: #16a34a; }
        .st-low { background: #fef3c7; color: #92400e; border: 1px solid #fde68a; } .dot-low { background: #d97706; }
        .st-zero { background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; } .dot-zero { background: #dc2626; }

        /* Buttons */
        .btn-act { border: none; padding: 8px 12px; border-radius: 6px; font-size: 13px; font-weight: 600; cursor: pointer; transition: 0.2s; display: inline-flex; align-items: center; justify-content: center; gap: 5px; text-decoration: none; }
        .btn-blue { background: #2563eb; color: white; } .btn-blue:hover { background: #1d4ed8; }
        .btn-yellow { background: #f59e0b; color: white; } .btn-yellow:hover { background: #d97706; }
        .btn-icon { width: 28px; height: 28px; border-radius: 6px; display: inline-flex; justify-content: center; align-items: center; cursor: pointer; color: white; border: none; margin-right: 3px; }
        .bi-edit { background: #3b82f6; } .bi-edit:hover { background: #2563eb; }
        .bi-del { background: #ef4444; } .bi-del:hover { background: #dc2626; }

        /* Modal Styles */
        .modal-overlay { 
            display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; 
            background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center; backdrop-filter: blur(2px);
            padding: 20px;
        }
        .modal-box { 
            background: white; width: 100%; max-width: 600px; padding: 25px; border-radius: 12px; 
            box-shadow: 0 10px 25px rgba(0,0,0,0.3); max-height: 90vh; overflow-y: auto; 
            animation: slideDown 0.3s; position: relative;
        }
        @keyframes slideDown { from {transform: translateY(-30px); opacity: 0;} to {transform: translateY(0); opacity: 1;} }

        /* Forms */
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
        .form-group { margin-bottom: 12px; }
        .form-label { display: block; font-size: 11px; font-weight: 700; color: #64748b; margin-bottom: 5px; }
        .form-control { width: 100%; padding: 10px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px; box-sizing: border-box; }
        .form-control:focus { border-color: #3b82f6; outline: none; box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1); }
        .full-width { grid-column: span 2; }
        .img-preview { width: 40px; height: 40px; object-fit: cover; border-radius: 6px; border: 1px solid #e2e8f0; }

        /* --- MEDIA QUERIES (RESPONSIVE RULES) --- */
        @media (max-width: 768px) {
            .content-wrapper { padding: 15px !important; }
            
            /* Header menjadi Stack */
            .page-header { flex-direction: column; align-items: flex-start; gap: 15px; }
            .header-actions { width: 100%; display: grid; grid-template-columns: 1fr 1fr; }
            .btn-act { width: 100%; justify-content: center; }

            /* PERUBAHAN DISINI: Memaksa Grid menjadi 2 Kolom */
            .stats-grid { 
                grid-template-columns: repeat(2, 1fr) !important; 
                gap: 10px; 
            }
            /* Font size disesuaikan agar muat di 2 kolom sempit */
            .stat-val { font-size: 16px; }

            /* Card Toolbar Search */
            .card-header-c { flex-direction: column; align-items: flex-start; gap: 10px; }
            #searchInput { width: 100%; }

            /* Form Modal menjadi 1 kolom */
            .form-grid { grid-template-columns: 1fr; gap: 0; }
            .modal-box { padding: 20px; width: 100%; }
        }

        /* Hapus override 1 kolom pada layar kecil */
        @media (max-width: 480px) {
            /* Tidak ada perubahan kolom, tetap mengikuti aturan 768px (2 kolom) */
        }
    </style>
</head>
<body>
    <?php include '../../layout/sidebar.php'; ?>
    
    <div class="content-wrapper">
        
        <div class="page-header">
            <div class="page-title">
                <h2>Stok Barang Jadi</h2>
                <p>Inventaris produk siap jual (Knalpot & Aksesoris)</p>
            </div>
            <div class="header-actions">
                <button onclick="openModalKategori()" class="btn-act btn-yellow"><i class="fa fa-tags"></i> Kategori</button>
                <button onclick="openModalBarang()" class="btn-act btn-blue"><i class="fa fa-plus"></i> Tambah</button>
            </div>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-label">Total SKU</div>
                <div class="stat-val"><?= number_format($total_item) ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Menipis</div>
                <div class="stat-val" style="color:#f59e0b;"><?= $stok_nipis ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Habis</div>
                <div class="stat-val" style="color:#ef4444;"><?= $stok_habis ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Aset (HPP)</div>
                <div class="stat-val" style="color:#10b981;">Rp <?= number_format($total_asset, 0, ',', '.') ?></div>
            </div>
        </div>

        <div class="card-custom">
            <div class="card-header-c">
                <span class="card-title-text">Daftar Produk</span>
                <input type="text" id="searchInput" placeholder="Cari Kode, Nama, Kategori...">
            </div>
            
            <div class="table-responsive">
                <table class="table-custom">
                    <thead>
                        <tr>
                            <th width="100">Kode</th>
                            <th>Nama Produk</th>
                            <th>Kategori / Rak</th>
                            <th>Harga Jual</th>
                            <th width="80">Stok</th>
                            <th width="100">Status</th>
                            <th style="text-align:center;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody">
                        <?php if(mysqli_num_rows($q_barang) > 0): ?>
                            <?php while($d = mysqli_fetch_assoc($q_barang)): ?>
                            <?php 
                                $stok = intval($d['stok']);
                                $img_src = (!empty($d['gambar']) && $d['gambar']!='default.png') ? "../../assets/uploads/products/".$d['gambar'] : "../../assets/image/no-image.png";

                                if($stok == 0) { $cls = 'st-zero'; $dot = 'dot-zero'; $txt = 'Habis'; }
                                elseif($stok <= 5) { $cls = 'st-low'; $dot = 'dot-low'; $txt = 'Menipis'; }
                                else { $cls = 'st-ok'; $dot = 'dot-ok'; $txt = 'Aman'; }
                                
                                $search_txt = strtolower($d['nama_barang'] . ' ' . $d['kode_barang'] . ' ' . $d['kategori']);
                            ?>
                            <tr class="search-item" data-search="<?= htmlspecialchars($search_txt) ?>">
                                <td><span class="badge badge-kode"><?= $d['kode_barang'] ?></span></td>
                                <td>
                                    <div style="display:flex; align-items:center; gap:10px;">
                                        <img src="<?= $img_src ?>" class="img-preview">
                                        <span style="font-weight:600;"><?= $d['nama_barang'] ?></span>
                                    </div>
                                </td>
                                <td>
                                    <?= $d['kategori'] ?>
                                    <div style="font-size:10px; color:#64748b;">
                                        <i class="fa fa-map-marker-alt"></i> <?= $d['lokasi_rak'] ?: '-' ?>
                                    </div>
                                </td>
                                <td style="font-family:monospace; color:#2563eb; font-weight:700;">
                                    Rp <?= number_format($d['harga_jual'], 0, ',', '.') ?>
                                </td>
                                <td><div style="font-weight:700;"><?= $stok ?></div></td>
                                <td>
                                    <span class="badge <?= $cls ?>">
                                        <span class="status-dot <?= $dot ?>"></span><?= $txt ?>
                                    </span>
                                </td>
                                <td style="text-align:center;">
                                    <button class="btn-icon bi-edit" title="Edit" onclick='editBarang(<?= json_encode($d) ?>)'><i class="fa fa-pen" style="font-size:12px;"></i></button>
                                    <button class="btn-icon bi-del" title="Hapus" onclick="hapusBarang(<?= $d['id'] ?>, '<?= addslashes($d['nama_barang']) ?>')"><i class="fa fa-trash" style="font-size:12px;"></i></button>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="7" style="text-align:center; padding:40px; color:#94a3b8;">Belum ada data barang</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div id="modalBarang" class="modal-overlay">
        <div class="modal-box">
            <div style="display:flex; justify-content:space-between; margin-bottom:20px; border-bottom:1px solid #e2e8f0; padding-bottom:10px;">
                <h3 id="modalTitle" style="margin:0;">Tambah Produk</h3>
                <span onclick="closeModal('modalBarang')" style="cursor:pointer; font-size:24px; color:#94a3b8;">&times;</span>
            </div>
            
            <form method="POST" id="formBarang" enctype="multipart/form-data">
                <input type="hidden" name="id_barang" id="id_barang">
                
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Kode SKU (Auto jika kosong)</label>
                        <input type="text" name="kode_barang" id="kode_barang" class="form-control" placeholder="Contoh: BRG-001">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Nama Barang *</label>
                        <input type="text" name="nama_barang" id="nama_barang" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Kategori *</label>
                        <select name="kategori" id="kategori" class="form-control" required>
                            <option value="">-- Pilih --</option>
                            <?php 
                            $q_kat = mysqli_query($conn, "SELECT * FROM kategori_barang ORDER BY nama_kategori ASC");
                            while($k = mysqli_fetch_assoc($q_kat)): 
                            ?>
                                <option value="<?= $k['nama_kategori'] ?>"><?= $k['nama_kategori'] ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Lokasi Rak</label>
                        <input type="text" name="lokasi_rak" id="lokasi_rak" class="form-control" placeholder="A-01">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Harga Modal (Rp)</label>
                        <input type="text" name="harga_modal" id="harga_modal" class="form-control" placeholder="0" onkeyup="formatRupiah(this)">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Harga Jual (Rp)</label>
                        <input type="text" name="harga_jual" id="harga_jual" class="form-control" placeholder="0" onkeyup="formatRupiah(this)">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Stok Awal</label>
                        <input type="number" name="stok" id="stok" class="form-control" value="0">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Gambar Produk</label>
                        <input type="file" name="gambar" class="form-control" accept="image/*" style="padding:7px;">
                    </div>
                </div>

                <div style="text-align:right; margin-top:20px; border-top:1px solid #f1f5f9; padding-top:15px;">
                    <button type="button" onclick="closeModal('modalBarang')" class="btn-act" style="border:1px solid #cbd5e1; background:white; color:#64748b; margin-right:10px;">Batal</button>
                    <button type="submit" name="simpan_barang" class="btn-act btn-blue"><i class="fa fa-save"></i> Simpan Data</button>
                </div>
            </form>
        </div>
    </div>

    <div id="modalKategori" class="modal-overlay">
        <div class="modal-box" style="max-width:400px;">
            <div style="display:flex; justify-content:space-between; margin-bottom:15px;">
                <h3 style="margin:0;">Kelola Kategori</h3>
                <span onclick="closeModal('modalKategori')" style="cursor:pointer; color:#94a3b8; font-size: 24px;">&times;</span>
            </div>
            
            <form method="POST" style="display:flex; gap:10px; margin-bottom:20px;">
                <input type="text" name="nama_kategori_baru" class="form-control" placeholder="Nama Kategori Baru" required>
                <button type="submit" name="tambah_kategori" class="btn-act btn-blue">Tambah</button>
            </form>

            <div style="max-height:250px; overflow-y:auto; border:1px solid #f1f5f9; border-radius:8px;">
                <?php 
                $list_kat = mysqli_query($conn, "SELECT * FROM kategori_barang ORDER BY nama_kategori ASC");
                if(mysqli_num_rows($list_kat) > 0):
                    while($lk = mysqli_fetch_assoc($list_kat)):
                ?>
                <div style="padding:10px; border-bottom:1px solid #f1f5f9; display:flex; justify-content:space-between; align-items:center;">
                    <span style="font-size:13px;"><?= $lk['nama_kategori'] ?></span>
                    <a href="?hapus_kategori=<?= $lk['id'] ?>" onclick="return confirm('Hapus kategori ini?')" style="color:#ef4444; font-size:12px; text-decoration:none;">[Hapus]</a>
                </div>
                <?php endwhile; else: ?>
                <div style="padding:15px; text-align:center; color:#94a3b8; font-size:12px;">Belum ada kategori</div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php include '../../layout/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // ALERT HANDLER
        <?php if(isset($success_msg)): ?>
            Swal.fire({ icon: 'success', title: 'Berhasil', text: '<?= $success_msg ?>', timer: 1500, showConfirmButton: false });
        <?php endif; ?>
        <?php if(isset($err_msg)): ?>
            Swal.fire({ icon: 'error', title: 'Gagal', text: '<?= $err_msg ?>' });
        <?php endif; ?>

        // MODAL LOGIC
        function openModalBarang() {
            document.getElementById('formBarang').reset();
            document.getElementById('id_barang').value = '';
            document.getElementById('modalTitle').innerText = 'Tambah Produk';
            document.getElementById('modalBarang').style.display = 'flex';
        }

        function openModalKategori() {
            document.getElementById('modalKategori').style.display = 'flex';
        }

        function closeModal(id) {
            document.getElementById(id).style.display = 'none';
        }

        function editBarang(data) {
            document.getElementById('id_barang').value = data.id;
            document.getElementById('kode_barang').value = data.kode_barang;
            document.getElementById('nama_barang').value = data.nama_barang;
            document.getElementById('kategori').value = data.kategori;
            document.getElementById('lokasi_rak').value = data.lokasi_rak;
            document.getElementById('stok').value = data.stok;
            
            document.getElementById('harga_modal').value = new Intl.NumberFormat('id-ID').format(data.harga_modal);
            document.getElementById('harga_jual').value = new Intl.NumberFormat('id-ID').format(data.harga_jual);

            document.getElementById('modalTitle').innerText = 'Edit Produk';
            document.getElementById('modalBarang').style.display = 'flex';
        }

        function hapusBarang(id, nama) {
            Swal.fire({
                title: 'Hapus Produk?',
                text: nama,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                confirmButtonText: 'Ya, Hapus'
            }).then((r) => {
                if(r.isConfirmed) window.location.href = '?hapus_barang=' + id;
            });
        }

        function formatRupiah(el) {
            let val = el.value.replace(/[^0-9]/g, '');
            el.value = new Intl.NumberFormat('id-ID').format(val);
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
    </script>
</body>
</html>