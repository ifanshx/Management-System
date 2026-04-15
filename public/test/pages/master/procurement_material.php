<?php
// 1. KONEKSI & SESSION
require_once '../../config/database.php';
date_default_timezone_set('Asia/Jakarta');

if (!isset($_SESSION['status'])) { header("Location: ../../login.php"); exit; }

// --- AJAX: GET DETAIL TRANSAKSI UNTUK MODAL ---
if (isset($_POST['act']) && $_POST['act'] == 'get_detail_transaksi') {
    $kode = $conn->real_escape_string($_POST['kode']);
    
    // 1. Ambil Item Barang
    $q = $conn->query("SELECT * FROM incoming_bahan_baku WHERE kode_transaksi = '$kode'");
    $items = [];
    $grand_total = 0;
    $metode = '';
    $jatuh_tempo = '-';
    
    while($r = $q->fetch_assoc()) { 
        $items[] = $r; 
        $grand_total += $r['total_harga'];
        $metode = $r['metode_pembayaran'];
        if(!empty($r['jatuh_tempo'])) $jatuh_tempo = date('d/m/Y', strtotime($r['jatuh_tempo']));
    }

    // 2. Ambil Info Pembayaran (Cek Hutang)
    $dibayar = 0;
    $sisa = 0;

    if ($metode == 'Cash') {
        $dibayar = $grand_total;
        $sisa = 0;
    } else {
        // Jika Tempo/Partial, ambil data real-time dari tabel hutang
        $q_hutang = $conn->query("SELECT total_dibayar, sisa_hutang FROM hutang_dagang WHERE kode_transaksi = '$kode'");
        if ($d_hutang = $q_hutang->fetch_assoc()) {
            $dibayar = floatval($d_hutang['total_dibayar']);
            $sisa = floatval($d_hutang['sisa_hutang']);
        } else {
            // Fallback (jarang terjadi)
            $dibayar = 0;
            $sisa = $grand_total;
        }
    }

    // Return JSON Paket Lengkap
    echo json_encode([
        'items' => $items,
        'info' => [
            'total' => $grand_total,
            'metode' => $metode,
            'dibayar' => $dibayar,
            'sisa' => $sisa,
            'jatuh_tempo' => $jatuh_tempo
        ]
    ]);
    exit;
}

// --- LOGIKA 1: TAMBAH SUPPLIER ---
if (isset($_POST['tambah_supplier'])) {
    $nama = trim($_POST['nama_supplier']);
    $kontak = trim($_POST['kontak']);
    $alamat = trim($_POST['alamat']);
    if(!empty($nama)) {
        $stmt = $conn->prepare("INSERT INTO suppliers (nama_supplier, kontak, alamat) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $nama, $kontak, $alamat);
        if($stmt->execute()) $_SESSION['success'] = "Supplier berhasil ditambahkan!";
    }
    header("Location: procurement_material.php"); exit;
}

// --- LOGIKA 2: SIMPAN PENGADAAN (CORE LOGIC) ---
if (isset($_POST['simpan_procurement'])) {
    $conn->begin_transaction();
    try {
        $trx        = "PO-" . date('ymd') . rand(100, 999);
        $id_supp    = $_POST['supplier_id'];
        $tgl        = $_POST['tanggal'];
        $ket_user   = $_POST['keterangan']; 
        $user_id    = $_SESSION['user_id'] ?? 1;
        
        // Data Pembayaran
        $metode_bayar = $_POST['metode_pembayaran']; // Cash, Tempo, Partial
        $sumber_dana  = $_POST['sumber_dana'];       // Cash atau ATM
        $jatuh_tempo  = (!empty($_POST['jatuh_tempo'])) ? $_POST['jatuh_tempo'] : NULL;
        
        // Handling Nominal DP (Hapus titik ribuan)
        $nominal_dp   = 0;
        if ($metode_bayar == 'Partial') {
            $nominal_dp = floatval(str_replace('.', '', $_POST['nominal_dp']));
        } elseif ($metode_bayar == 'Cash') {
            $nominal_dp = 0; 
        }

        // Ambil Nama Supplier
        $d_supp = $conn->query("SELECT nama_supplier FROM suppliers WHERE id=$id_supp")->fetch_assoc();
        $nama_supplier = $d_supp['nama_supplier'];

        $bahan_ids = $_POST['bahan_id']; 
        $qtys      = $_POST['qty'];       
        $hargas    = $_POST['harga'];     

        $grand_total = 0; 

        // A. LOOPING ITEM BARANG (Masuk Stok & Update Harga Rata2)
        for ($i = 0; $i < count($bahan_ids); $i++) {
            $id_bahan   = $bahan_ids[$i];
            $qty        = floatval($qtys[$i]);
            $harga_beli = floatval(str_replace('.', '', $hargas[$i]));
            $total_rp   = $qty * $harga_beli;

            if($qty > 0 && $id_bahan != "") {
                // Lock Data Lama
                $q_old = $conn->query("SELECT * FROM bahan_baku WHERE id = $id_bahan FOR UPDATE");
                $d_old = $q_old->fetch_assoc();
                
                // Hitung Average Cost Baru
                $stok_lama  = floatval($d_old['stok']);
                $harga_lama = floatval($d_old['harga_satuan']);
                $nilai_lama = $stok_lama * $harga_lama;
                $stok_baru  = $stok_lama + $qty;
                $avg_price  = ($stok_baru > 0) ? ($nilai_lama + $total_rp) / $stok_baru : $harga_beli;

                // Insert Log Incoming
                $stmt_in = $conn->prepare("INSERT INTO incoming_bahan_baku (kode_transaksi, bahan_baku_id, nama_bahan, supplier, jumlah_masuk, harga_beli_satuan, total_harga, tanggal_masuk, keterangan, created_by, metode_pembayaran, jatuh_tempo) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt_in->bind_param("sisssssssiss", $trx, $id_bahan, $d_old['nama_bahan'], $nama_supplier, $qty, $harga_beli, $total_rp, $tgl, $ket_user, $user_id, $metode_bayar, $jatuh_tempo);
                $stmt_in->execute();

                // Update Master Bahan
                $stmt_upd = $conn->prepare("UPDATE bahan_baku SET stok = ?, harga_satuan = ?, supplier = ? WHERE id = ?");
                $stmt_upd->bind_param("ddsi", $stok_baru, $avg_price, $nama_supplier, $id_bahan);
                $stmt_upd->execute();

                $grand_total += $total_rp; 
            }
        }

        // B. HANDLING KEUANGAN (KAS & HUTANG)
        if ($grand_total > 0) {
            
            if ($metode_bayar == 'Cash') {
                // --- FULL CASH ---
                // 1. Potong Kas (Sesuai Sumber Dana)
                $ket_kas = "BELANJA BAHAN (LUNAS) - " . strtoupper($nama_supplier) . " [Kode: $trx]";
                $stmt_kas = $conn->prepare("INSERT INTO transaksi_kas (user_id, tanggal, jenis, keterangan, nominal, metode) VALUES (?, ?, 'Keluar', ?, ?, ?)");
                $stmt_kas->bind_param("issds", $user_id, $tgl, $ket_kas, $grand_total, $sumber_dana);
                $stmt_kas->execute();

            } elseif ($metode_bayar == 'Partial') {
                // --- DP / TERMIN ---
                
                // 1. Catat Pengeluaran DP di Kas (Jika ada DP)
                if ($nominal_dp > 0) {
                    $ket_kas = "DP PEMBELIAN BAHAN - " . strtoupper($nama_supplier) . " [Kode: $trx]";
                    $stmt_kas = $conn->prepare("INSERT INTO transaksi_kas (user_id, tanggal, jenis, keterangan, nominal, metode) VALUES (?, ?, 'Keluar', ?, ?, ?)");
                    $stmt_kas->bind_param("issds", $user_id, $tgl, $ket_kas, $nominal_dp, $sumber_dana);
                    $stmt_kas->execute();
                }

                // 2. Catat Sisa Hutang
                $sisa_hutang = $grand_total - $nominal_dp;
                if ($sisa_hutang > 0) {
                    if(empty($jatuh_tempo)) $jatuh_tempo = date('Y-m-d', strtotime($tgl . ' +30 days'));
                    
                    $stmt_hutang = $conn->prepare("INSERT INTO hutang_dagang (kode_transaksi, supplier_id, nama_supplier, total_tagihan, total_dibayar, sisa_hutang, status, tanggal_transaksi, jatuh_tempo) VALUES (?, ?, ?, ?, ?, ?, 'Sebagian', ?, ?)");
                    $stmt_hutang->bind_param("sissddss", $trx, $id_supp, $nama_supplier, $grand_total, $nominal_dp, $sisa_hutang, $tgl, $jatuh_tempo);
                    $stmt_hutang->execute();
                    
                    // Catat DP sebagai history pembayaran hutang pertama
                    if ($nominal_dp > 0) {
                        $hutang_id = $conn->insert_id;
                        $ket_bayar = "Pembayaran DP Awal ($sumber_dana)";
                        $stmt_hist = $conn->prepare("INSERT INTO riwayat_bayar_hutang (hutang_id, kode_transaksi, nominal_bayar, tanggal_bayar, keterangan) VALUES (?, ?, ?, ?, ?)");
                        $stmt_hist->bind_param("isdss", $hutang_id, $trx, $nominal_dp, $tgl, $ket_bayar);
                        $stmt_hist->execute();
                    }
                }

            } else {
                // --- FULL TEMPO ---
                // Tidak ada uang keluar dari kas
                if(empty($jatuh_tempo)) $jatuh_tempo = date('Y-m-d', strtotime($tgl . ' +30 days'));

                $stmt_hutang = $conn->prepare("INSERT INTO hutang_dagang (kode_transaksi, supplier_id, nama_supplier, total_tagihan, sisa_hutang, status, tanggal_transaksi, jatuh_tempo) VALUES (?, ?, ?, ?, ?, 'Belum Lunas', ?, ?)");
                $stmt_hutang->bind_param("sissdss", $trx, $id_supp, $nama_supplier, $grand_total, $grand_total, $tgl, $jatuh_tempo);
                $stmt_hutang->execute();
            }
        }

        $conn->commit();
        $_SESSION['success'] = "Transaksi <b>$trx</b> berhasil disimpan ($metode_bayar).";
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error'] = "Gagal: " . $e->getMessage();
    }
    header("Location: procurement_material.php"); exit;
}

// --- LOGIKA 3: BATALKAN TRANSAKSI ---
if (isset($_GET['hapus_kode'])) {
    $kode_trx = $conn->real_escape_string($_GET['hapus_kode']);
    
    $conn->begin_transaction();
    try {
        // 1. Cek Hutang (Validasi Pembatalan)
        $cek_hutang = $conn->query("SELECT id, total_tagihan, sisa_hutang FROM hutang_dagang WHERE kode_transaksi = '$kode_trx'");
        
        if ($cek_hutang->num_rows > 0) {
            $dh = $cek_hutang->fetch_assoc();
            $id_htg = $dh['id'];
            
            // Cek apakah sudah ada pembayaran cicilan SELAIN DP AWAL?
            // Jika ada pembayaran lain, user tidak boleh membatalkan dari sini.
            // Harus batalkan pembayaran cicilannya dulu di menu Hutang Dagang.
            
            $cek_cicilan = $conn->query("SELECT COUNT(*) as jml FROM riwayat_bayar_hutang WHERE hutang_id = '$id_htg' AND keterangan NOT LIKE 'Pembayaran DP Awal%'");
            $d_ccl = $cek_cicilan->fetch_assoc();

            if ($d_ccl['jml'] > 0) {
                throw new Exception("Gagal membatalkan: Transaksi ini sudah memiliki riwayat cicilan. Harap hapus pembayaran cicilan terlebih dahulu di menu Hutang Dagang.");
            }
            
            // Jika aman (hanya DP atau belum bayar), hapus data hutang & history DP
            $conn->query("DELETE FROM riwayat_bayar_hutang WHERE hutang_id = '$id_htg'");
            $conn->query("DELETE FROM hutang_dagang WHERE id = '$id_htg'");
        }

        // 2. Kembalikan Stok Barang (Kurangi)
        $q_items = $conn->query("SELECT bahan_baku_id, jumlah_masuk FROM incoming_bahan_baku WHERE kode_transaksi = '$kode_trx'");
        while($item = $q_items->fetch_assoc()) {
            $id_bahan = $item['bahan_baku_id'];
            $qty_batal = floatval($item['jumlah_masuk']);
            $conn->query("UPDATE bahan_baku SET stok = stok - $qty_batal WHERE id = '$id_bahan'");
        }

        // 3. Hapus Log Barang Masuk
        $conn->query("DELETE FROM incoming_bahan_baku WHERE kode_transaksi = '$kode_trx'");

        // 4. Hapus Transaksi Kas (Baik itu Lunas atau DP)
        $conn->query("DELETE FROM transaksi_kas WHERE keterangan LIKE '%[Kode: $kode_trx]%'");

        $conn->commit();
        $_SESSION['success'] = "Transaksi <b>$kode_trx</b> berhasil dibatalkan. Stok & Kas dikembalikan.";
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error'] = $e->getMessage();
    }
    header("Location: procurement_material.php"); exit;
}

// --- LOAD DATA ---
$q_bahan_list = $conn->query("SELECT * FROM bahan_baku ORDER BY nama_bahan ASC");
$bahan_array = [];
while($b = $q_bahan_list->fetch_assoc()) { $bahan_array[] = $b; }

$q_supp = $conn->query("SELECT * FROM suppliers ORDER BY nama_supplier ASC");

// --- FILTER HISTORY ---
$filter_date = isset($_GET['date']) ? $_GET['date'] : '';
$where_hist = "WHERE 1=1";
if(!empty($filter_date)) {
    $where_hist .= " AND tanggal_masuk = '$filter_date'";
}

$q_hist_group = $conn->query("SELECT kode_transaksi, tanggal_masuk, supplier, metode_pembayaran, SUM(total_harga) as grand_total, COUNT(id) as total_item 
                              FROM incoming_bahan_baku 
                              $where_hist 
                              GROUP BY kode_transaksi 
                              ORDER BY created_at DESC LIMIT 15");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <?php include '../../layout/header.php'; ?>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <style>
        /* GLOBAL STYLES */
        * { box-sizing: border-box; }
        body { background-color: #f8fafc; font-family: 'Inter', sans-serif; color: #334155; }
        .content-wrapper { padding: 25px 30px !important; }
        
        /* GRID LAYOUT */
        .procurement-grid { display: grid; grid-template-columns: 1.8fr 1.2fr; gap: 25px; align-items: start; }

        /* CARDS */
        .card-pro { background: #fff; border-radius: 12px; border: 1px solid #e2e8f0; overflow: hidden; margin-bottom: 25px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02); }
        .card-header-pro { padding: 15px 20px; background: #fff; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center; }
        .card-header-pro h3 { margin: 0; font-size: 15px; font-weight: 700; color: #1e293b; text-transform: uppercase; letter-spacing: 0.5px; }
        .card-body-pro { padding: 20px; }

        /* FORMS */
        .form-row-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 20px; }
        .form-label { display: block; font-size: 11px; font-weight: 700; color: #64748b; margin-bottom: 6px; text-transform: uppercase; }
        .form-control { width: 100%; padding: 10px 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 13px; outline: none; transition: 0.2s; }
        .form-control:focus { border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37,99,235,0.1); }
        
        /* SELECT2 FIXES */
        .select2-container { width: 100% !important; } 
        .select2-selection--single { height: 40px !important; border: 1px solid #cbd5e1 !important; border-radius: 8px !important; display: flex !important; align-items: center !important; }
        .select2-selection__arrow { top: 7px !important; }
        .select2-selection__rendered { font-size: 13px; color: #334155; padding-left: 12px; white-space: normal !important; word-wrap: break-word !important; }

        /* INPUT TABLE */
        .table-input { width: 100%; border-collapse: separate; border-spacing: 0; margin-bottom: 15px; }
        .table-input th { text-align: left; padding: 10px; background: #f1f5f9; color: #64748b; font-size: 11px; font-weight: 700; text-transform: uppercase; border-radius: 6px; }
        .table-input td { padding: 10px 5px; vertical-align: top; }
        .qty-input, .harga-input, .nominal-input { text-align: right; font-family: monospace; font-weight: 600; }

        /* BUTTONS */
        .btn-add-row { background: #f0fdf4; color: #166534; border: 1px dashed #86efac; width: 100%; padding: 10px; border-radius: 8px; font-size: 12px; font-weight: 600; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 6px; transition: 0.2s; }
        .btn-add-row:hover { background: #dcfce7; }
        .btn-submit { width: 100%; padding: 14px; background: #2563eb; color: #fff; border: none; border-radius: 8px; font-weight: 700; cursor: pointer; margin-top: 20px; font-size: 14px; box-shadow: 0 4px 6px -1px rgba(37,99,235,0.2); transition: 0.2s; }
        .btn-submit:hover { background: #1d4ed8; }

        /* HISTORY LIST STYLE (DESKTOP TABLE) */
        .table-history { width: 100%; border-collapse: collapse; }
        .table-history th { text-align: left; padding: 12px 15px; background: #f8fafc; color: #64748b; font-size: 11px; font-weight: 700; text-transform: uppercase; border-bottom: 1px solid #e2e8f0; }
        .table-history td { padding: 12px 15px; border-bottom: 1px solid #f1f5f9; font-size: 13px; vertical-align: middle; }
        .table-history tr:hover { background-color: #f8fafc; }
        
        .code-text { font-family: monospace; font-weight: 700; color: #2563eb; font-size: 12px; display: block; }
        .date-text { font-size: 11px; color: #94a3b8; }
        .amount-text { font-weight: 700; font-family: monospace; color: #059669; }
        
        .badge-status { padding: 4px 8px; border-radius: 6px; font-size: 10px; font-weight: 700; display: inline-block; margin-top: 4px; text-transform: uppercase; letter-spacing: 0.5px; }
        .bs-cash { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
        .bs-tempo { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
        .bs-partial { background: #dbeafe; color: #1e40af; border: 1px solid #bfdbfe; }

        .btn-icon { width: 28px; height: 28px; display: inline-flex; align-items: center; justify-content: center; border-radius: 6px; border: 1px solid transparent; transition: 0.2s; cursor: pointer; text-decoration: none; }
        .btn-icon-view { background: #f0f9ff; color: #0284c7; border-color: #e0f2fe; }
        .btn-icon-view:hover { background: #0284c7; color: #fff; }
        .btn-icon-del { background: #fef2f2; color: #ef4444; border-color: #fee2e2; }
        .btn-icon-del:hover { background: #ef4444; color: #fff; }

        /* MOBILE CARD HISTORY */
        .hist-card { border: 1px solid #e2e8f0; border-radius: 10px; padding: 15px; margin-bottom: 15px; background: #fff; position: relative; box-shadow: 0 1px 3px rgba(0,0,0,0.02); }
        .hist-card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; padding-bottom: 10px; border-bottom: 1px dashed #f1f5f9; }
        .hist-row { display: flex; justify-content: space-between; font-size: 12px; margin-bottom: 6px; }
        .hist-lbl { color: #64748b; }
        .hist-val { font-weight: 600; color: #334155; }

        /* MODAL */
        .modal-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.6); z-index: 9999; align-items: center; justify-content: center; backdrop-filter: blur(4px); padding: 20px; }
        .modal-box { background: #fff; width: 100%; max-width: 650px; border-radius: 16px; display:flex; flex-direction:column; max-height: 90vh; overflow: hidden; animation: zoomIn 0.2s; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1); }
        @keyframes zoomIn { from {transform: scale(0.95); opacity:0;} to {transform: scale(1); opacity:1;} }

        /* RESPONSIVE BREAKPOINTS */
        @media (max-width: 992px) {
            .procurement-grid { grid-template-columns: 1fr; gap: 30px; }
            .content-wrapper { padding: 15px !important; }
            
            /* Hide Desktop Table, Show Mobile Cards */
            .d-md-block { display: none !important; }
            .d-md-none { display: block !important; }

            /* Adjust Table Input for Mobile */
            #itemTable thead { display: none; }
            #itemTable, #itemTable tbody, #itemTable tr, #itemTable td { display: block; width: 100%; }
            #itemTable tr { background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 15px; margin-bottom: 15px; position: relative; box-shadow: 0 2px 4px rgba(0,0,0,0.02); }
            #itemTable td { padding: 5px 0; border: none; }
            #itemTable td:nth-child(2), #itemTable td:nth-child(3) { display: inline-block; width: 48%; }
            #itemTable td:nth-child(3) { float: right; }
            #itemTable td:nth-child(4) { margin-top: 10px; padding-top: 10px; border-top: 1px dashed #e2e8f0; display: flex; justify-content: space-between; width: 100%; }
            #itemTable td:nth-child(4)::before { content: "Subtotal:"; font-size: 11px; color: #64748b; font-weight: 600; }
            #itemTable td:nth-child(5) { position: absolute; top: 10px; right: 10px; width: auto; padding: 0; }
            .btn-del-row { background: #fee2e2; color: #ef4444; width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; border: none; cursor: pointer; }
        }
        @media (min-width: 993px) {
            .d-md-block { display: table !important; }
            .d-md-none { display: none !important; }
        }
    </style>
</head>
<body>
    <?php include '../../layout/sidebar.php'; ?>

    <div class="content-wrapper">
        <div style="margin-bottom: 25px;">
            <h2 style="font-weight:800; color:#1e293b; margin:0; font-size: 22px;">Pengadaan Bahan</h2>
            <p style="color:#64748b; margin-top:4px; font-size:13px;">Kelola pembelian stok masuk, pembayaran Cash/Termin/Hutang.</p>
        </div>

        <div class="procurement-grid">
            
            <div>
                <form method="POST" id="formProcure" onsubmit="return validateForm()">
                    <div class="card-pro">
                        <div class="card-header-pro">
                            <h3><i class="fa fa-shopping-cart" style="margin-right:8px; color:#2563eb;"></i> Transaksi Baru</h3>
                            <button type="button" onclick="openModalSupplier()" style="background:#f0f9ff; border:1px solid #bae6fd; color:#0284c7; padding:6px 12px; border-radius:6px; cursor:pointer; font-size:11px; font-weight:700; display:flex; align-items:center; gap:5px;">
                                <i class="fa fa-plus"></i> Supplier Baru
                            </button>
                        </div>
                        <div class="card-body-pro">
                            <div class="form-row-2">
                                <div>
                                    <label class="form-label">Tanggal Transaksi</label>
                                    <input type="date" name="tanggal" class="form-control" value="<?= date('Y-m-d') ?>" required>
                                </div>
                                <div>
                                    <label class="form-label">Supplier</label>
                                    <select name="supplier_id" class="form-control select2" required>
                                        <option value="">-- Pilih Supplier --</option>
                                        <?php mysqli_data_seek($q_supp, 0); while($s = $q_supp->fetch_assoc()): ?>
                                            <option value="<?= $s['id'] ?>"><?= $s['nama_supplier'] ?></option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                            </div>

                            <div style="background: #fffbeb; border: 1px solid #fcd34d; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                                <div class="form-row-2" style="margin-bottom: 0;">
                                    <div>
                                        <label class="form-label" style="color:#b45309;">Metode Pembayaran</label>
                                        <select name="metode_pembayaran" id="metodeBayar" class="form-control select2-simple" style="width:100%;">
                                            <option value="Cash">Bayar Lunas</option>
                                            <option value="Partial">Bayar Sebagian (DP/Termin)</option>
                                            <option value="Tempo">Full Hutang (Tempo)</option>
                                        </select>
                                    </div>
                                    <div id="divJatuhTempo" style="display: none;">
                                        <label class="form-label" style="color:#b45309;">Jatuh Tempo (Sisa)</label>
                                        <input type="date" name="jatuh_tempo" class="form-control" style="border-color:#fcd34d;">
                                    </div>
                                </div>
                                
                                <div id="divSumberDana" style="margin-top: 15px;">
                                    <label class="form-label" style="color:#b45309;">Sumber Dana (Bayar Pakai)</label>
                                    <select name="sumber_dana" id="sumberDana" class="form-control select2-simple" style="width:100%;">
                                        <option value="Cash">Kas Tunai (Cash)</option>
                                        <option value="ATM">Rekening Bank (ATM)</option>
                                    </select>
                                </div>

                                <div id="divNominalDP" style="margin-top: 15px; display: none;">
                                    <label class="form-label" style="color:#b45309;">Nominal DP / Bayar Awal (Rp)</label>
                                    <input type="text" name="nominal_dp" id="inputDP" class="form-control nominal-input" placeholder="0" onkeyup="formatRupiah(this); calcSisaHutang()" style="border-color:#fcd34d; font-size:15px; font-weight:700; color:#b45309;">
                                    <div style="margin-top: 5px; font-size: 11px; color: #b45309; display:flex; justify-content:space-between; font-weight:600;">
                                        <span>Tagihan: <span id="lblTotalTagihan">Rp 0</span></span>
                                        <span>Sisa Hutang: <span id="lblSisaHutang">Rp 0</span></span>
                                    </div>
                                </div>
                            </div>
                            
                            <div style="background:#f8fafc; border-radius:8px; padding:10px; border:1px solid #e2e8f0;">
                                <table class="table-input" id="itemTable">
                                    <thead>
                                        <tr>
                                            <th width="40%">Item Bahan Baku</th>
                                            <th width="20%">Qty</th>
                                            <th width="25%">Harga Satuan (Rp)</th>
                                            <th width="15%" style="text-align:right;">Subtotal</th>
                                            <th width="5%"></th>
                                        </tr>
                                    </thead>
                                    <tbody id="tableBody"></tbody>
                                </table>
                                
                                <button type="button" class="btn-add-row" onclick="addRow()">
                                    <i class="fa fa-plus-circle"></i> Tambah Item Lain
                                </button>
                            </div>

                            <div style="margin-top: 20px;">
                                <label class="form-label">Catatan (Opsional)</label>
                                <input type="text" name="keterangan" class="form-control" placeholder="Contoh: No. Nota Supplier...">
                            </div>

                            <div style="background: #f0fdf4; border:1px solid #bbf7d0; padding:15px; border-radius:8px; margin-top:20px; display:flex; justify-content:space-between; align-items:center;">
                                <span style="font-size: 12px; font-weight: 700; color: #166534; text-transform:uppercase;">Grand Total</span>
                                <span style="font-size: 18px; font-weight: 800; color: #166534;" id="grandTotal">Rp 0</span>
                            </div>

                            <button type="submit" name="simpan_procurement" class="btn-submit">
                                <i class="fa fa-save"></i> SIMPAN TRANSAKSI
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <div>
                <div class="card-pro">
                    <div class="card-header-pro">
                        <h3><i class="fa fa-history" style="margin-right:5px; color:#64748b;"></i> Riwayat</h3>
                        <form method="GET" style="display:flex; gap:5px; max-width:160px;">
                            <input type="date" name="date" class="form-control" style="padding:5px 8px; height:32px; font-size:11px;" value="<?= $filter_date ?>">
                            <button type="submit" style="background:#2563eb; color:#fff; border:none; border-radius:6px; width:32px; height:32px; cursor:pointer; display:flex; align-items:center; justify-content:center;"><i class="fa fa-search"></i></button>
                            <?php if(!empty($filter_date)): ?>
                                <a href="procurement_material.php" style="background:#fee2e2; color:#ef4444; border:1px solid #fecaca; border-radius:6px; width:32px; height:32px; display:flex; align-items:center; justify-content:center; text-decoration:none;"><i class="fa fa-times"></i></a>
                            <?php endif; ?>
                        </form>
                    </div>
                    
                    <div style="max-height: 800px; overflow-y: auto;">
                        <?php if($q_hist_group->num_rows > 0): ?>
                            
                            <table class="table-history d-md-block">
                                <thead>
                                    <tr>
                                        <th>Kode / Tgl</th>
                                        <th>Supplier</th>
                                        <th>Total / Status</th>
                                        <th style="text-align:right;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($h = $q_hist_group->fetch_assoc()): ?>
                                    <?php 
                                        $cls = 'bs-cash'; 
                                        if($h['metode_pembayaran'] == 'Tempo') $cls = 'bs-tempo';
                                        if($h['metode_pembayaran'] == 'Partial') $cls = 'bs-partial';
                                    ?>
                                    <tr>
                                        <td>
                                            <span class="code-text"><?= $h['kode_transaksi'] ?></span>
                                            <span class="date-text"><?= date('d/m/Y', strtotime($h['tanggal_masuk'])) ?></span>
                                        </td>
                                        <td>
                                            <span style="font-weight:600; font-size:13px; color:#334155;"><?= $h['supplier'] ?></span>
                                            <div style="font-size:11px; color:#94a3b8;"><?= $h['total_item'] ?> Items</div>
                                        </td>
                                        <td>
                                            <div class="amount-text">Rp <?= number_format($h['grand_total'],0,',','.') ?></div>
                                            <span class="badge-status <?= $cls ?>"><?= $h['metode_pembayaran'] ?></span>
                                        </td>
                                        <td style="text-align:right; white-space:nowrap;">
                                            <button class="btn-icon btn-icon-view" title="Detail" onclick="viewDetail('<?= $h['kode_transaksi'] ?>', '<?= $h['supplier'] ?>', '<?= $h['tanggal_masuk'] ?>')"><i class="fa fa-eye"></i></button>
                                            <button class="btn-icon btn-icon-del" title="Batal" onclick="batalTransaksi('<?= $h['kode_transaksi'] ?>')"><i class="fa fa-trash"></i></button>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>

                            <div class="d-md-none" style="padding:15px;">
                                <?php mysqli_data_seek($q_hist_group, 0); ?>
                                <?php while($h = $q_hist_group->fetch_assoc()): ?>
                                    <?php 
                                        $cls = 'bs-cash'; 
                                        if($h['metode_pembayaran'] == 'Tempo') $cls = 'bs-tempo';
                                        if($h['metode_pembayaran'] == 'Partial') $cls = 'bs-partial';
                                    ?>
                                    <div class="hist-card">
                                        <div class="hist-card-header">
                                            <span class="code-text" style="font-size:13px;"><?= $h['kode_transaksi'] ?></span>
                                            <span class="badge-status <?= $cls ?>"><?= $h['metode_pembayaran'] ?></span>
                                        </div>
                                        <div class="hist-row">
                                            <span class="hist-lbl">Tanggal</span>
                                            <span class="hist-val"><?= date('d/m/Y', strtotime($h['tanggal_masuk'])) ?></span>
                                        </div>
                                        <div class="hist-row">
                                            <span class="hist-lbl">Supplier</span>
                                            <span class="hist-val"><?= $h['supplier'] ?></span>
                                        </div>
                                        <div class="hist-row">
                                            <span class="hist-lbl">Total</span>
                                            <span class="amount-text">Rp <?= number_format($h['grand_total'],0,',','.') ?></span>
                                        </div>
                                        <div style="margin-top:10px; padding-top:10px; border-top:1px dashed #f1f5f9; display:flex; gap:10px;">
                                            <button class="btn-icon btn-icon-view" style="flex:1; width:auto; font-size:12px; font-weight:600;" onclick="viewDetail('<?= $h['kode_transaksi'] ?>', '<?= $h['supplier'] ?>', '<?= $h['tanggal_masuk'] ?>')"><i class="fa fa-eye" style="margin-right:5px;"></i> Detail</button>
                                            <button class="btn-icon btn-icon-del" style="flex:1; width:auto; font-size:12px; font-weight:600;" onclick="batalTransaksi('<?= $h['kode_transaksi'] ?>')"><i class="fa fa-trash" style="margin-right:5px;"></i> Batal</button>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            </div>

                        <?php else: ?>
                            <div style="padding:50px 20px; text-align:center; color:#94a3b8;">
                                <i class="fa fa-clipboard-list" style="font-size:32px; margin-bottom:10px; opacity:0.5;"></i>
                                <div style="font-size:13px;">Belum ada riwayat transaksi.</div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <div id="modalDetail" class="modal-overlay">
        <div class="modal-box">
            <div style="padding: 15px 20px; border-bottom: 1px solid #e2e8f0; display:flex; justify-content:space-between; align-items:center; background:#f8fafc;">
                <div>
                    <h3 style="margin:0; font-size:16px; color:#1e293b;">Detail Transaksi</h3>
                    <div style="font-size:12px; color:#64748b; margin-top:2px;" id="detInfo">Loading...</div>
                </div>
                <span onclick="closeModalDetail()" style="cursor:pointer; font-size:24px; color:#94a3b8; width:30px; height:30px; text-align:center; line-height:30px;">&times;</span>
            </div>
            <div class="modal-body" style="padding:0; overflow-y:auto; flex:1;">
                <table class="table-input" style="margin:0;">
                    <thead style="background:#fff; border-bottom:2px solid #f1f5f9; position:sticky; top:0;">
                        <tr>
                            <th style="padding-left:20px;">Item</th>
                            <th style="text-align:center;">Qty</th>
                            <th style="text-align:right;">@Harga</th>
                            <th style="text-align:right; padding-right:20px;">Total</th>
                        </tr>
                    </thead>
                    <tbody id="detContent"></tbody>
                </table>
            </div>
            <div style="padding:15px 20px; border-top:1px solid #e2e8f0; text-align:right; background:#fff;">
                <a href="#" id="btnCetak" target="_blank" style="display:block; width:100%; padding:12px; background:#10b981; color:#fff; border-radius:8px; text-decoration:none; font-size:14px; font-weight:700; text-align:center; box-shadow:0 4px 6px -1px rgba(16,185,129,0.3);">
                    <i class="fa fa-print" style="margin-right:5px;"></i> CETAK BUKTI MASUK
                </a>
            </div>
        </div>
    </div>

    <div id="modalSupplier" class="modal-overlay">
        <div class="modal-box" style="width:90%; max-width:400px; height:auto;">
            <div style="padding:20px; border-bottom:1px solid #e2e8f0;">
                <h3 style="margin:0; font-size:16px;">Tambah Supplier</h3>
            </div>
            <form method="POST" style="padding:20px;">
                <div style="margin-bottom:15px;">
                    <label class="form-label">Nama Supplier</label>
                    <input type="text" name="nama_supplier" class="form-control" required placeholder="Nama PT / Toko">
                </div>
                <div style="margin-bottom:15px;">
                    <label class="form-label">No. Kontak / WA</label>
                    <input type="text" name="kontak" class="form-control" placeholder="08...">
                </div>
                <div style="margin-bottom:20px;">
                    <label class="form-label">Alamat</label>
                    <input type="text" name="alamat" class="form-control" placeholder="Kota / Alamat Lengkap">
                </div>
                <div style="display:flex; gap:10px;">
                    <button type="button" onclick="closeModalSupplier()" style="flex:1; padding:10px; background:#fff; border:1px solid #cbd5e1; border-radius:8px; cursor:pointer; color:#64748b; font-weight:600;">Batal</button>
                    <button type="submit" name="tambah_supplier" style="flex:1; padding:10px; background:#2563eb; border:none; border-radius:8px; cursor:pointer; color:#fff; font-weight:600;">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <?php include '../../layout/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    
    <script>
        const bahanData = <?= json_encode($bahan_array) ?>;
        let currentGrandTotal = 0;

        $(document).ready(function() {
            $('.select2').select2();
            $('.select2-simple').select2({ minimumResultsForSearch: Infinity });

            $('#metodeBayar').on('change', function() { togglePaymentOptions(); });
            addRow(); 
        });

        function togglePaymentOptions() {
            var metode = $('#metodeBayar').val();
            var divTempo = document.getElementById("divJatuhTempo");
            var divDP = document.getElementById("divNominalDP");
            var divSumber = document.getElementById("divSumberDana");
            var inputDP = document.getElementById("inputDP");

            if (metode === "Cash") {
                divTempo.style.display = "none";
                divDP.style.display = "none";
                divSumber.style.display = "block"; 
                inputDP.value = "0";
            } else if (metode === "Tempo") {
                divTempo.style.display = "block";
                divDP.style.display = "none";
                divSumber.style.display = "none"; 
                inputDP.value = "0";
            } else if (metode === "Partial") {
                divTempo.style.display = "block";
                divDP.style.display = "block";
                divSumber.style.display = "block"; 
                inputDP.value = ""; 
            }
            calcSisaHutang();
        }

        function calcSisaHutang() {
            let dpStr = document.getElementById("inputDP").value.replace(/\./g, '') || "0";
            let dp = parseFloat(dpStr);
            let sisa = currentGrandTotal - dp;
            if(sisa < 0) sisa = 0;

            document.getElementById("lblTotalTagihan").innerText = 'Rp ' + new Intl.NumberFormat('id-ID').format(currentGrandTotal);
            document.getElementById("lblSisaHutang").innerText = 'Rp ' + new Intl.NumberFormat('id-ID').format(sisa);
        }

        function validateForm() {
            var metode = $('#metodeBayar').val();
            if (metode === "Partial") {
                let dpStr = document.getElementById("inputDP").value.replace(/\./g, '') || "0";
                let dp = parseFloat(dpStr);
                if (dp <= 0) {
                    Swal.fire('Error', 'Untuk metode DP, nominal DP harus diisi lebih dari 0', 'error');
                    return false;
                }
                if (dp >= currentGrandTotal) {
                    Swal.fire('Error', 'Jika DP sama atau lebih besar dari Total, silakan pilih metode Cash (Lunas)', 'warning');
                    return false;
                }
            }
            return true;
        }

        function addRow() {
            let options = '<option value="">-- Pilih Bahan --</option>';
            bahanData.forEach(b => { options += `<option value="${b.id}"> ${b.nama_bahan} (${b.satuan})</option>`; });
            let btnDelete = `<button type="button" class="btn-del-row" onclick="removeRow(this)"><i class="fa fa-trash"></i></button>`;
            
            let row = `
                <tr>
                    <td><select name="bahan_id[]" class="form-control select2-item" required style="width:100%;">${options}</select></td>
                    <td><input type="number" name="qty[]" class="form-control qty-input" step="0.01" placeholder="0" oninput="calcRow(this)" required></td>
                    <td><input type="text" name="harga[]" class="form-control harga-input" placeholder="0" onkeyup="formatRupiah(this); calcRow(this)" required></td>
                    <td class="subtotal" style="text-align:right; font-weight:700; color:#2563eb; font-size:13px; padding-top:14px;">Rp 0</td>
                    <td>${btnDelete}</td>
                </tr>`;
            $('#tableBody').append(row);
            $('.select2-item').select2();
        }

        function removeRow(btn) { 
            let rowCount = $('#tableBody tr').length;
            if(rowCount > 1) { $(btn).closest('tr').remove(); calcGrandTotal(); } 
            else { Swal.fire({icon:'warning', title:'Perhatian', text:'Minimal harus ada 1 item transaksi.'}); }
        }

        function calcRow(el) {
            let row = $(el).closest('tr');
            let qty = parseFloat(row.find('.qty-input').val()) || 0;
            let harga = parseFloat(row.find('.harga-input').val().replace(/\./g, '')) || 0;
            let sub = qty * harga;
            row.find('.subtotal').text('Rp ' + new Intl.NumberFormat('id-ID').format(sub));
            row.attr('data-sub', sub);
            calcGrandTotal();
        }

        function calcGrandTotal() {
            let total = 0;
            $('#tableBody tr').each(function() { total += parseFloat($(this).attr('data-sub')) || 0; });
            currentGrandTotal = total;
            $('#grandTotal').text('Rp ' + new Intl.NumberFormat('id-ID').format(total));
            calcSisaHutang();
        }

        function formatRupiah(el) {
            let val = el.value.replace(/[^0-9]/g, '');
            el.value = new Intl.NumberFormat('id-ID').format(val);
        }

        function openModalSupplier() { document.getElementById('modalSupplier').style.display = 'flex'; }
        function closeModalSupplier() { document.getElementById('modalSupplier').style.display = 'none'; }
        function closeModalDetail() { document.getElementById('modalDetail').style.display = 'none'; }

        function batalTransaksi(kode) {
            Swal.fire({
                title: 'Batalkan Transaksi?',
                html: `Anda akan menghapus transaksi <b>${kode}</b>.<br>Stok, Hutang, & Kas akan dikembalikan!`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Batalkan!'
            }).then((result) => {
                if (result.isConfirmed) { window.location.href = `procurement_material.php?hapus_kode=${kode}`; }
            })
        }

        // REVISI VIEW DETAIL (INCLUDE PAYMENT INFO)
        function viewDetail(kode, supp, tgl) {
            document.getElementById('detInfo').innerText = kode + " • " + supp + " • " + tgl;
            document.getElementById('btnCetak').href = "cetak_po.php?kode=" + kode;
            document.getElementById('modalDetail').style.display = 'flex';
            document.getElementById('detContent').innerHTML = '<tr><td colspan="4" style="text-align:center; padding:30px; color:#64748b;">Loading data...</td></tr>';

            $.post('', { act: 'get_detail_transaksi', kode: kode }, function(res) {
                let data = JSON.parse(res);
                let items = data.items;
                let info = data.info;
                
                let html = '';
                items.forEach(d => {
                    let sub = parseFloat(d.total_harga);
                    html += `
                        <tr>
                            <td style="padding-left:20px;">
                                <div style="font-weight:700; color:#334155;">${d.nama_bahan}</div>
                                <div style="font-size:10px; color:#94a3b8;">${d.keterangan || '-'}</div>
                            </td>
                            <td style="text-align:center;">${parseFloat(d.jumlah_masuk)}</td>
                            <td style="text-align:right;">${new Intl.NumberFormat('id-ID').format(d.harga_beli_satuan)}</td>
                            <td style="text-align:right; padding-right:20px; font-weight:700;">${new Intl.NumberFormat('id-ID').format(sub)}</td>
                        </tr>
                    `;
                });

                html += `
                    <tr style="border-top: 2px solid #e2e8f0; background:#f8fafc;">
                        <td colspan="3" style="text-align:right; padding-top:15px; color:#64748b; font-size:12px;">TOTAL TAGIHAN</td>
                        <td style="text-align:right; padding-right:20px; padding-top:15px; font-weight:800; color:#1e293b; font-size:14px;">Rp ${new Intl.NumberFormat('id-ID').format(info.total)}</td>
                    </tr>
                    <tr style="background:#f8fafc;">
                        <td colspan="3" style="text-align:right; color:#64748b; font-size:12px;">DIBAYAR / DP (${info.metode})</td>
                        <td style="text-align:right; padding-right:20px; font-weight:700; color:#059669; font-size:13px;">Rp ${new Intl.NumberFormat('id-ID').format(info.dibayar)}</td>
                    </tr>
                    <tr style="background:#fffbeb;">
                        <td colspan="3" style="text-align:right; color:#b45309; font-weight:600; padding-bottom:15px; font-size:12px;">SISA HUTANG (Tempo: ${info.jatuh_tempo})</td>
                        <td style="text-align:right; padding-right:20px; font-weight:800; color:#ef4444; padding-bottom:15px; font-size:14px;">Rp ${new Intl.NumberFormat('id-ID').format(info.sisa)}</td>
                    </tr>
                `;

                document.getElementById('detContent').innerHTML = html;
            });
        }

        <?php if(isset($_SESSION['success'])): ?>
            Swal.fire({icon:'success', title:'Berhasil', html:'<?= $_SESSION['success'] ?>', timer:2000, showConfirmButton:false});
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>
        <?php if(isset($_SESSION['error'])): ?>
            Swal.fire({icon:'error', title:'Gagal', html:'<?= $_SESSION['error'] ?>'});
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
    </script>
</body>
</html>