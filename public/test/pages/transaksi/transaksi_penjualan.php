<?php
// 1. KONEKSI & SESSION
require_once '../../config/database.php';
date_default_timezone_set('Asia/Jakarta');

if (!isset($_SESSION['status'])) { header("Location: ../../login.php"); exit; }

// --- AJAX 1: HANDLE CHECKOUT (POS + AUTO KAS) ---
if (isset($_POST['act']) && $_POST['act'] === 'checkout') {
    $data = json_decode($_POST['data'], true);
    
    $trx  = 'TRX-'.date('ymd').'-'.rand(1000,9999);
    $uid  = $_SESSION['user_id'] ?? 1;
    $uname= $_SESSION['nama_lengkap'] ?? 'Admin';
    
    $sid   = !empty($data['sales_id']) ? intval($data['sales_id']) : NULL;
    $sname = !empty($data['sales_nama']) ? $data['sales_nama'] : NULL;
    $metode_bayar = $data['payment_method']; 

    mysqli_begin_transaction($conn);
    try {
        // 1. Simpan PENJUALAN
        $stmt = $conn->prepare("INSERT INTO penjualan (kode_transaksi, customer_name, sales_id, sales_nama, total_item, total_bayar, jumlah_bayar, kembalian, metode_pembayaran, kasir_id, kasir_nama) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssissidddis", $trx, $data['customer'], $sid, $sname, $data['total_items'], $data['grand_total'], $data['payment'], $data['change'], $metode_bayar, $uid, $uname);
        $stmt->execute();
        $pid = $conn->insert_id;

        // 2. Simpan DETAIL & Update STOK (Kurang)
        foreach ($data['cart'] as $item) {
            $conn->query("UPDATE barang SET stok = stok - {$item['qty']} WHERE id='{$item['id']}'");
            $sub = $item['qty'] * $item['harga_jual'];
            $conn->query("INSERT INTO penjualan_detail (penjualan_id, barang_id, nama_barang_snapshot, qty, harga_satuan, subtotal) VALUES ($pid, {$item['id']}, '{$item['nama_barang']}', {$item['qty']}, {$item['harga_jual']}, $sub)");
        }

        // 3. Masuk BUKU KAS
        $tgl_kas = date('Y-m-d');
        $ket_kas = "PENJUALAN POS - $trx (" . ($data['customer'] ?: 'Umum') . ")";
        $nominal_kas = $data['grand_total']; 

        $stmt_kas = $conn->prepare("INSERT INTO transaksi_kas (user_id, tanggal, jenis, keterangan, nominal, metode) VALUES (?, ?, 'Masuk', ?, ?, ?)");
        $stmt_kas->bind_param("issds", $uid, $tgl_kas, $ket_kas, $nominal_kas, $metode_bayar);
        $stmt_kas->execute();

        mysqli_commit($conn);
        echo json_encode(['status'=>'success', 'kode'=>$trx]);
    } catch (Exception $e) {
        mysqli_rollback($conn);
        echo json_encode(['status'=>'error', 'msg'=>$e->getMessage()]);
    }
    exit;
}

// --- AJAX 2: BATALKAN TRANSAKSI (HAPUS) ---
if (isset($_POST['act']) && $_POST['act'] === 'batal_transaksi') {
    $kode = $conn->real_escape_string($_POST['kode']);
    
    mysqli_begin_transaction($conn);
    try {
        // 1. Ambil ID Penjualan & Data Detail
        $q_head = $conn->query("SELECT id FROM penjualan WHERE kode_transaksi = '$kode'");
        $d_head = $q_head->fetch_assoc();
        
        if($d_head) {
            $pid = $d_head['id'];
            $q_det = $conn->query("SELECT barang_id, qty FROM penjualan_detail WHERE penjualan_id = '$pid'");
            
            // 2. Kembalikan Stok Barang
            while($item = $q_det->fetch_assoc()) {
                $conn->query("UPDATE barang SET stok = stok + {$item['qty']} WHERE id = '{$item['barang_id']}'");
            }

            // 3. Hapus Data Penjualan
            $conn->query("DELETE FROM penjualan WHERE id = '$pid'");

            // 4. Hapus dari Transaksi Kas 
            $conn->query("DELETE FROM transaksi_kas WHERE keterangan LIKE '%$kode%' AND jenis='Masuk'");
            
            mysqli_commit($conn);
            echo json_encode(['status'=>'success']);
        } else {
            throw new Exception("Transaksi tidak ditemukan");
        }
    } catch (Exception $e) {
        mysqli_rollback($conn);
        echo json_encode(['status'=>'error', 'msg'=>$e->getMessage()]);
    }
    exit;
}

// --- AJAX 3: LOAD PRODUK ---
if (isset($_GET['act']) && $_GET['act'] == 'get_products') {
    $sales_id = intval($_GET['sales_id']); 
    $sql = "SELECT b.id, b.kode_barang, b.nama_barang, b.kategori, b.stok, b.gambar,
            COALESCE(s.harga_khusus, b.harga_jual) as harga_final
            FROM barang b
            LEFT JOIN barang_harga_sales s ON b.id = s.barang_id AND s.sales_id = '$sales_id'
            WHERE b.stok > 0 ORDER BY b.nama_barang ASC";
    $q = $conn->query($sql);
    $res = [];
    while($r = $q->fetch_assoc()) {
        $r['img'] = (!empty($r['gambar']) && $r['gambar']!='default.png') ? "../../assets/uploads/products/".$r['gambar'] : null;
        $r['harga_jual'] = floatval($r['harga_final']);
        $res[] = $r;
    }
    echo json_encode($res); exit;
}

// --- LOAD RIWAYAT ---
$tgl_hist = isset($_GET['tgl_hist']) ? $_GET['tgl_hist'] : date('Y-m-d');
$q_hist = $conn->query("SELECT * FROM penjualan WHERE DATE(tanggal) = '$tgl_hist' ORDER BY tanggal DESC");
$history_data = [];
while($h = $q_hist->fetch_assoc()) $history_data[] = $h;

// Data Sales Dropdown
$sales_list = [];
$qs = $conn->query("SELECT id, nama_sales FROM sales WHERE status='aktif'");
while($s = $qs->fetch_assoc()) $sales_list[] = $s;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <?php include '../../layout/header.php'; ?>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    
    <style>
        /* Scoped Variables untuk POS */
        .pos-wrapper {
            --primary: #2563eb; --primary-dark: #1e40af; --bg-light: #f8fafc;
            --white: #ffffff; --border: #e2e8f0; --text-main: #0f172a; --text-sub: #64748b;
            --accent-green: #10b981; --accent-red: #ef4444; --radius: 12px;
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1);
            font-family: 'Inter', sans-serif;
            padding: 15px; 
            box-sizing: border-box;
        }

        .pos-layout {
            display: grid; 
            grid-template-columns: 1fr 400px; 
            gap: 20px;
            height: calc(100vh - 120px); /* Kunci agar layout fix di layar dan bisa scroll di dalam */
            min-height: 500px;
            width: 100%;
        }

        /* STRUKTUR UTAMA AGAR BISA SCROLL */
        .catalog-section, .cart-section {
            background: var(--white); 
            border-radius: 16px; 
            border: 1px solid var(--border);
            display: flex; 
            flex-direction: column; 
            height: 100%; 
            overflow: hidden;
            box-shadow: var(--shadow-md);
            min-height: 0; /* WAJIB ADA AGAR FLEXBOX ANAKNYA BISA SCROLL */
        }
        
        .filter-bar { background: var(--white); padding: 15px; border-bottom: 1px solid var(--border); display: flex; gap: 12px; align-items: center; flex-wrap: wrap; z-index: 10; flex-shrink: 0; }
        .search-wrap { flex: 1; position: relative; min-width: 200px; }
        .search-wrap i { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--text-sub); }
        .search-input { width: 100%; padding: 10px 10px 10px 35px; border: 1px solid var(--border); border-radius: 8px; font-size: 14px; outline: none; background: #f8fafc; transition: 0.2s; box-sizing: border-box; }
        .search-input:focus { border-color: var(--primary); background: #fff; }
        .sales-select { padding: 10px; border: 1px solid var(--border); border-radius: 8px; background: #fff; font-size: 14px; color: var(--text-main); font-weight: 500; cursor: pointer; outline: none; min-width: 150px; }
        
        .btn-history { background: #fff7ed; color: #ea580c; border: 1px solid #ffedd5; padding: 10px 14px; border-radius: 8px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 6px; font-size: 13px; transition: 0.2s; }
        .btn-history:hover { background: #ffedd5; }

        /* PRODUCT GRID DENGAN SCROLL & FULL FIX */
        .product-list { 
            flex: 1; 
            overflow-y: auto; /* Memunculkan scroll vertikal */
            padding: 15px; 
            display: grid; 
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); 
            grid-auto-rows: min-content; /* KUNCI: Paksa grid agar setinggi isi kontennya */
            gap: 15px; 
            min-height: 0; 
            padding-bottom: 30px;
        }

        /* KARTU PRODUK (DIJAMIN TEKS MUNCUL) */
        .prod-card { 
            background: var(--white); 
            border-radius: var(--radius); 
            border: 1px solid var(--border); 
            cursor: pointer; 
            display: block; /* Ganti dari flex ke block agar natural mengikuti panjang teks */
            overflow: hidden; 
            box-shadow: var(--shadow-sm); 
            transition: 0.2s;
        }
        .prod-card:hover { transform: translateY(-3px); border-color: #bfdbfe; box-shadow: var(--shadow-md); }
        .prod-card:active { transform: scale(0.98); }
        
        .prod-img { 
            width: 100%;
            height: 130px; /* Tinggi gambar fix */
            background: #f1f5f9; 
            position: relative; 
        }
        .prod-img img { width: 100%; height: 100%; object-fit: cover; }
        .stock-badge { position: absolute; top: 8px; right: 8px; background: rgba(15,23,42,0.8); color: #fff; font-size: 11px; padding: 4px 8px; border-radius: 6px; font-weight: 700; }
        
        .prod-info { 
            padding: 12px; 
            /* Teks di bawah akan otomatis mendorong tinggi kartu */
        }
        .prod-title { font-size: 13px; font-weight: 600; color: var(--text-main); margin-bottom: 4px; line-height: 1.4; word-wrap: break-word; }
        .prod-sku { font-size: 11px; color: var(--text-sub); margin-bottom: 8px; display: block;}
        .prod-price { font-size: 15px; font-weight: 800; color: var(--primary); display: block; }

        /* KERANJANG DENGAN SCROLL */
        .cart-header { padding: 18px 20px; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; background: #f8fafc; flex-shrink: 0; }
        .cart-title { font-weight: 700; font-size: 16px; display: flex; align-items: center; gap: 8px; margin: 0; }
        .cart-customer-box { padding: 15px 20px 0; background: #fff; flex-shrink: 0; }
        .cust-input { width: 100%; padding: 12px; border: 1px solid var(--border); border-radius: 8px; outline: none; font-size: 14px; box-sizing: border-box; background: #f8fafc;}
        .cust-input:focus { border-color: var(--primary); background: #fff; }
        
        .cart-items { 
            flex: 1; 
            overflow-y: auto; /* Memunculkan Scrollbar */
            padding: 15px 20px; 
            display: flex; 
            flex-direction: column; 
            gap: 12px; 
            min-height: 0; /* WAJIB UNTUK SCROLL */
        }
        
        .c-item { display: flex; align-items: center; justify-content: space-between; padding: 12px; border: 1px solid var(--border); border-radius: 10px; background: #fff; }
        .c-info { flex: 1; padding-right: 10px; }
        .c-name { font-size: 14px; font-weight: 600; color: var(--text-main); margin-bottom: 4px; }
        .c-price { font-size: 12px; color: var(--text-sub); }
        .c-actions { display: flex; flex-direction: column; align-items: flex-end; gap: 8px; }
        .c-subtotal { font-weight: 700; font-size: 14px; color: var(--primary); }
        .qty-control { display: flex; align-items: center; background: #f1f5f9; border-radius: 6px; border: 1px solid var(--border); }
        .qty-btn { width: 28px; height: 28px; border: none; background: transparent; cursor: pointer; font-weight: bold; color: var(--text-main); display: flex; align-items: center; justify-content: center; }
        .qty-btn:hover { background: #e2e8f0; }
        .qty-val { width: 30px; text-align: center; font-size: 13px; font-weight: 600; background: #fff; border-left: 1px solid var(--border); border-right: 1px solid var(--border); display: flex; align-items: center; justify-content: center; height: 28px; }
        
        .cart-footer { padding: 20px; border-top: 1px solid var(--border); background: #f8fafc; flex-shrink: 0; }
        .c-row { display: flex; justify-content: space-between; font-size: 14px; color: var(--text-sub); margin-bottom: 8px; }
        .c-total { display: flex; justify-content: space-between; font-size: 18px; font-weight: 800; color: var(--text-main); margin-top: 12px; padding-top: 12px; border-top: 1px dashed #cbd5e1; }
        .btn-checkout { width: 100%; padding: 14px; background: var(--primary); color: #fff; border: none; border-radius: 10px; font-weight: 700; font-size: 15px; margin-top: 15px; cursor: pointer; display: flex; justify-content: center; align-items: center; gap: 8px; transition: 0.2s; box-shadow: 0 4px 6px rgba(37,99,235,0.2); }
        .btn-checkout:hover:not(:disabled) { background: #1d4ed8; transform: translateY(-1px); }
        .btn-checkout:disabled { background: #cbd5e1; cursor: not-allowed; box-shadow: none; }

        /* MOBILE TAB BAR */
        .mobile-tab-bar { display: none; position: fixed; bottom: 0; left: 0; right: 0; background: #fff; border-top: 1px solid var(--border); padding: 10px; z-index: 999; justify-content: space-around; box-shadow: 0 -4px 15px rgba(0,0,0,0.05); }
        .tab-item { flex: 1; text-align: center; padding: 8px; border-radius: 8px; font-size: 12px; font-weight: 600; color: var(--text-sub); cursor: pointer; display: flex; flex-direction: column; align-items: center; gap: 4px; }
        .tab-item.active { color: var(--primary); background: #eff6ff; }
        .tab-icon { font-size: 18px; position: relative; }
        .badge-count { position: absolute; top: -5px; right: -8px; background: var(--accent-red); color: white; font-size: 10px; padding: 2px 5px; border-radius: 10px; border: 1px solid #fff; }

        /* MODAL COMMON STYLE */
        .modal-overlay { 
            display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; 
            background: rgba(15, 23, 42, 0.7); z-index: 2000; 
            align-items: center; justify-content: center; backdrop-filter: blur(4px); padding: 20px; box-sizing: border-box; 
        }
        .modal-box { 
            background: #fff; width: 100%; max-width: 420px; border-radius: 16px; 
            overflow: hidden; animation: popIn 0.2s ease-out; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25); 
            display: flex; flex-direction: column; max-height: 90vh; 
        }
        .modal-head { padding: 18px 20px; background: #f8fafc; font-weight: 700; border-bottom: 1px solid var(--border); color: var(--text-main); font-size: 15px; display: flex; justify-content: space-between; align-items: center; flex-shrink: 0; }
        .modal-body { padding: 20px; overflow-y: auto; flex: 1; min-height: 0; }
        
        /* PAYMENT MODAL */
        .big-total { font-size: 36px; font-weight: 800; color: var(--primary); margin: 5px 0 25px; text-align: center; letter-spacing: -1px; }
        .pay-input { width: 100%; padding: 16px; font-size: 24px; text-align: center; font-weight: 700; border: 2px solid var(--border); border-radius: 12px; outline: none; box-sizing: border-box; margin-bottom: 15px; color: var(--text-main); }
        .pay-input:focus { border-color: var(--primary); }
        .pay-method-wrap { display: flex; gap: 12px; margin-bottom: 20px; }
        .pm-label { flex: 1; cursor: pointer; position: relative; }
        .pm-label input { position: absolute; opacity: 0; }
        .pm-box { border: 2px solid var(--border); border-radius: 12px; padding: 14px; font-size: 13px; font-weight: 600; color: var(--text-sub); transition: 0.2s; display: flex; align-items: center; justify-content: center; gap: 8px; }
        .pm-label input:checked + .pm-box.cash { background: #fff7ed; color: #ea580c; border-color: #ea580c; }
        .pm-label input:checked + .pm-box.atm { background: #eff6ff; color: #2563eb; border-color: #2563eb; }
        .change-display { margin-top: 10px; font-size: 14px; font-weight: 600; color: var(--text-sub); text-align: center; }
        .change-amount { display: block; font-size: 20px; font-weight: 800; margin-top: 4px; }
        .text-ok { color: var(--accent-green); } .text-no { color: var(--accent-red); }
        .modal-foot { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-top: 25px; }
        .btn-back { padding: 14px; background: #fff; border: 1px solid var(--border); border-radius: 10px; font-weight: 600; cursor: pointer; color: var(--text-sub); transition: 0.2s;}
        .btn-back:hover { background: #f1f5f9; }
        .btn-process { padding: 14px; background: var(--accent-green); border: none; border-radius: 10px; font-weight: 700; color: #fff; cursor: pointer; transition: 0.2s; box-shadow: 0 4px 10px rgba(16,185,129,0.2); }
        .btn-process:hover:not(:disabled) { background: #059669; transform: translateY(-1px); }
        .btn-process:disabled { background: #cbd5e1; box-shadow: none; cursor: not-allowed;}

        /* HISTORY MODAL DENGAN SCROLL */
        .modal-history { max-width: 700px; height: 80vh; }
        .hist-filter { display: flex; gap: 10px; padding: 15px 20px; border-bottom: 1px solid var(--border); background: #fff; flex-shrink: 0; }
        .hist-list { overflow-y: auto; flex: 1; padding: 0; min-height: 0; }
        .hist-item { display: flex; justify-content: space-between; align-items: center; padding: 16px 20px; border-bottom: 1px solid #f1f5f9; transition: 0.2s; }
        .hist-item:hover { background: #f8fafc; }
        .h-info { display: flex; flex-direction: column; }
        .h-code { font-weight: 700; font-size: 14px; color: var(--text-main); }
        .h-meta { font-size: 12px; color: var(--text-sub); margin-top: 4px; }
        .h-total { font-weight: 800; font-size: 14px; color: var(--primary); text-align: right; margin-right: 15px;}
        .btn-cancel { padding: 6px 12px; background: #fef2f2; color: #ef4444; border: 1px solid #fecaca; border-radius: 6px; font-size: 11px; font-weight: 700; cursor: pointer; white-space: nowrap; transition: 0.2s; }
        .btn-cancel:hover { background: #ef4444; color: white; }

        @media (max-width: 1024px) {
            .pos-wrapper { padding: 10px; padding-bottom: 80px; }
            .pos-layout { 
                grid-template-columns: 1fr; 
                height: calc(100vh - 120px); 
            }

            .catalog-section.hidden-mobile { display: none; }
            .cart-section.hidden-mobile { display: none; }
            
            .catalog-section, .cart-section { border-radius: 12px; }
            .mobile-tab-bar { display: flex; }
            .filter-bar { padding: 12px; position: sticky; top: 0; z-index: 40; border-radius: 12px 12px 0 0; }
            .search-wrap { width: 100%; order: 1; }
            .sales-select { width: calc(100% - 110px); order: 2; flex:1; }
            .btn-history { order: 3; padding: 10px; }
            
            .modal-box { border-radius: 16px; }
            .modal-history { height: 85vh; }
        }
        
        @keyframes popIn { from { transform: scale(0.95) translateY(10px); opacity: 0; } to { transform: scale(1) translateY(0); opacity: 1; } }
        .close-btn { background: none; border: none; font-size: 24px; color: var(--text-sub); cursor: pointer; line-height: 1; padding: 0; transition: 0.2s; }
        .close-btn:hover { color: var(--accent-red); }

        .swal2-container { z-index: 99999 !important; }
    </style>
</head>
<body>
    <?php include '../../layout/sidebar.php'; ?>
    
    <div class="content-wrapper">
        <div class="pos-wrapper" x-data="posApp()" x-init="initData()">
            <div class="pos-layout">

                <div class="catalog-section" :class="{'hidden-mobile': mobileTab === 'cart'}">
                    <div class="filter-bar">
                        <div class="search-wrap">
                            <i class="fa fa-search"></i>
                            <input x-model="search" type="text" class="search-input" placeholder="Cari barang (Kode/Nama)...">
                        </div>
                        <select x-model="salesId" @change="changeSales()" class="sales-select">
                            <option value="0">Pelanggan Umum</option>
                            <?php foreach($sales_list as $sl): ?>
                                <option value="<?= $sl['id'] ?>"><?= $sl['nama_sales'] ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button class="btn-history" @click="openHistory()">
                            <i class="fa fa-history"></i>
                        </button>
                    </div>

                    <div class="product-list">
                        <template x-for="p in filteredProducts" :key="p.id">
                            <div class="prod-card" @click="addToCart(p)">
                                <div class="prod-img">
                                    <img :src="p.img || '../../assets/image/no-image.png'" alt="Img" loading="lazy">
                                    <span class="stock-badge" x-text="p.stok"></span>
                                </div>
                                <div class="prod-info">
                                    <div class="prod-title" x-text="p.nama_barang"></div>
                                    <div class="prod-sku" x-text="p.kode_barang"></div>
                                    <div class="prod-price" x-text="fmt(p.harga_jual)"></div>
                                </div>
                            </div>
                        </template>
                        <div x-show="filteredProducts.length === 0" style="grid-column: 1/-1; text-align: center; padding: 60px 20px; color: #94a3b8;">
                            <i class="fa fa-box-open" style="font-size: 40px; margin-bottom: 15px; opacity: 0.5;"></i><br>
                            <span style="font-weight: 500;">Produk tidak ditemukan</span>
                        </div>
                    </div>
                </div>

                <div class="cart-section" :class="{'hidden-mobile': mobileTab === 'catalog'}">
                    <div class="cart-header">
                        <h3 class="cart-title"><i class="fa fa-shopping-bag" style="color:var(--primary);"></i> Keranjang</h3>
                        <button @click="resetCart()" x-show="cart.length > 0" style="font-size:12px; color:var(--accent-red); background:#fef2f2; padding:6px 12px; border-radius:6px; border:none; cursor:pointer; font-weight:700;">KOSONGKAN</button>
                    </div>
                    <div class="cart-customer-box">
                        <input x-model="customer" type="text" class="cust-input" placeholder="Nama Pelanggan (Opsional)">
                    </div>
                    <div class="cart-items">
                        <template x-for="(item, idx) in cart" :key="item.id">
                            <div class="c-item">
                                <div class="c-info">
                                    <div class="c-name" x-text="item.nama_barang"></div>
                                    <div class="c-price" x-text="fmt(item.harga_jual) + ' x ' + item.qty"></div>
                                </div>
                                <div class="c-actions">
                                    <div class="c-subtotal" x-text="fmt(item.harga_jual * item.qty)"></div>
                                    <div class="qty-control">
                                        <button class="qty-btn" @click="updQty(idx, -1)">-</button>
                                        <div class="qty-val" x-text="item.qty"></div>
                                        <button class="qty-btn" @click="updQty(idx, 1)">+</button>
                                    </div>
                                </div>
                            </div>
                        </template>
                        <div x-show="cart.length === 0" style="text-align: center; margin: auto; color: #cbd5e1;">
                            <i class="fa fa-shopping-cart" style="font-size: 50px; margin-bottom: 15px;"></i><br>
                            <span style="font-weight: 600;">Keranjang Kosong</span>
                        </div>
                    </div>
                    <div class="cart-footer">
                        <div class="c-row">
                            <span>Sales</span><span x-text="getSalesName()" style="font-weight:600; color:var(--text-main);"></span>
                        </div>
                        <div class="c-row">
                            <span>Items</span><span style="font-weight:600; color:var(--text-main);" x-text="totalQty + ' Pcs'"></span>
                        </div>
                        <div class="c-total">
                            <span>Total</span><span style="color:var(--primary);" x-text="fmt(grandTotal)"></span>
                        </div>
                        <button class="btn-checkout" @click="openPayment()" :disabled="cart.length===0"><i class="fa fa-credit-card"></i> PROSES BAYAR</button>
                    </div>
                </div>

                <div class="mobile-tab-bar">
                    <div class="tab-item" :class="{'active': mobileTab === 'catalog'}" @click="mobileTab = 'catalog'">
                        <div class="tab-icon"><i class="fa fa-store"></i></div><span>Katalog</span>
                    </div>
                    <div class="tab-item" :class="{'active': mobileTab === 'cart'}" @click="mobileTab = 'cart'">
                        <div class="tab-icon">
                            <i class="fa fa-shopping-bag"></i><span class="badge-count" x-show="totalQty > 0" x-text="totalQty"></span>
                        </div><span>Keranjang</span>
                    </div>
                </div>

                <div class="modal-overlay" :style="showPayModal ? 'display:flex' : 'display:none'">
                    <div class="modal-box modal-payment">
                        <div class="modal-head">
                            <span>Konfirmasi Pembayaran</span>
                            <button class="close-btn" @click="showPayModal=false">&times;</button>
                        </div>
                        <div class="modal-body">
                            <div style="font-size:13px; font-weight:600; color:var(--text-sub); text-align:center;">TOTAL TAGIHAN</div>
                            <div class="big-total" x-text="fmt(grandTotal)"></div>
                            <div class="pay-method-wrap">
                                <label class="pm-label"><input type="radio" name="payment_method" value="Cash" x-model="paymentMethod" @change="payAmount = ''"><div class="pm-box cash"><i class="fa fa-money"></i> TUNAI</div></label>
                                <label class="pm-label"><input type="radio" name="payment_method" value="ATM" x-model="paymentMethod" @change="payAmount = grandTotal"><div class="pm-box atm"><i class="fa fa-credit-card"></i> TRANSFER / ATM</div></label>
                            </div>
                            <input type="number" x-model.number="payAmount" class="pay-input" placeholder="Input Nominal" inputmode="numeric">
                            <div class="change-display">Kembalian <span class="change-amount" :class="(payAmount - grandTotal) < 0 ? 'text-no' : 'text-ok'" x-text="fmt(Math.max(0, payAmount - grandTotal))"></span></div>
                            <div class="modal-foot">
                                <button @click="showPayModal=false" class="btn-back">Batal</button>
                                <button @click="processCheckout()" class="btn-process" :disabled="payAmount < grandTotal">BAYAR SEKARANG</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-overlay" :style="showHistoryModal ? 'display:flex' : 'display:none'">
                    <div class="modal-box modal-history">
                        <div class="modal-head">
                            <span>Riwayat Penjualan Hari Ini</span>
                            <button class="close-btn" @click="showHistoryModal=false">&times;</button>
                        </div>
                        <div class="hist-filter">
                            <form method="GET" style="display:flex; gap:10px; width:100%;">
                                <input type="date" name="tgl_hist" value="<?= $tgl_hist ?>" class="cust-input" style="padding:10px;">
                                <button type="submit" class="btn-back" style="padding:10px 20px; color:var(--text-main);">Cari</button>
                            </form>
                        </div>
                        <div class="hist-list">
                            <?php if(empty($history_data)): ?>
                                <div style="text-align:center; padding:40px; color:#cbd5e1;">Belum ada transaksi.</div>
                            <?php else: ?>
                                <?php foreach($history_data as $h): ?>
                                <div class="hist-item">
                                    <div class="h-info">
                                        <span class="h-code"><?= $h['kode_transaksi'] ?></span>
                                        <span class="h-meta"><?= date('H:i', strtotime($h['tanggal'])) ?> • <?= $h['customer_name'] ?> • <?= $h['metode_pembayaran'] ?></span>
                                    </div>
                                    <div style="display:flex; align-items:center;">
                                        <div class="h-total">Rp <?= number_format($h['total_bayar'], 0, ',', '.') ?></div>
                                        <button class="btn-cancel" onclick="cancelSale('<?= $h['kode_transaksi'] ?>')">Batal</button>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <?php include '../../layout/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
        function posApp() {
            return {
                salesId: 0,
                search: '',
                customer: '',
                products: [],
                cart: [],
                mobileTab: 'catalog',
                showPayModal: false,
                showHistoryModal: false,
                payAmount: '',
                paymentMethod: 'Cash',

                initData() { this.fetchProducts(); },
                fetchProducts() {
                    fetch(`?act=get_products&sales_id=${this.salesId}`).then(r => r.json()).then(d => {
                        this.products = d; this.recalcCartPrices(d);
                    });
                },
                changeSales() { this.fetchProducts(); },
                recalcCartPrices(newProds) {
                    this.cart = this.cart.map(c => {
                        let updated = newProds.find(p => p.id == c.id);
                        if(updated) c.harga_jual = updated.harga_jual;
                        return c;
                    });
                },
                get filteredProducts() {
                    if (this.search === '') return this.products;
                    const q = this.search.toLowerCase();
                    return this.products.filter(p => p.nama_barang.toLowerCase().includes(q) || p.kode_barang.toLowerCase().includes(q));
                },
                addToCart(p) {
                    if (p.stok <= 0) return Swal.fire({icon: 'error', title: 'Stok Habis', toast: true, position: 'top', showConfirmButton: false, timer: 1500});
                    let exist = this.cart.find(c => c.id == p.id);
                    if (exist) {
                        if (exist.qty < p.stok) { exist.qty++; Swal.mixin({toast: true, position: 'bottom', showConfirmButton: false, timer: 1000}).fire({icon: 'success', title: 'Jumlah ditambah'}); }
                        else { Swal.fire({icon: 'warning', title: 'Mencapai batas stok', toast: true, position: 'top', showConfirmButton: false, timer: 1500}); }
                    } else { this.cart.push({...p, qty: 1}); }
                },
                updQty(idx, val) {
                    let c = this.cart[idx];
                    let n = c.qty + val;
                    if (n > c.stok) return;
                    if (n <= 0) this.cart.splice(idx, 1); else c.qty = n;
                },
                resetCart() { this.cart = []; this.customer = ''; },
                get grandTotal() { return this.cart.reduce((a, b) => a + (b.harga_jual * b.qty), 0); },
                get totalQty() { return this.cart.reduce((a, b) => a + b.qty, 0); },
                getSalesName() { let el = document.querySelector('.sales-select'); return el.options[el.selectedIndex].text; },
                fmt(n) { return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(n); },
                openPayment() { this.payAmount = ''; this.paymentMethod = 'Cash'; this.showPayModal = true; },
                openHistory() { this.showHistoryModal = true; },
                processCheckout() {
                    this.showPayModal = false;
                    Swal.fire({title: 'Memproses...', didOpen: () => Swal.showLoading()});
                    let fd = new FormData();
                    fd.append('act', 'checkout');
                    fd.append('data', JSON.stringify({
                        cart: this.cart, customer: this.customer || 'Umum', sales_id: this.salesId, sales_nama: this.getSalesName(),
                        payment: this.payAmount, payment_method: this.paymentMethod, grand_total: this.grandTotal,
                        change: this.payAmount - this.grandTotal, total_items: this.totalQty
                    }));
                    fetch('', { method: 'POST', body: fd }).then(r => r.json()).then(res => {
                        if (res.status == 'success') {
                            Swal.fire({icon: 'success', title: 'Transaksi Berhasil!', text: 'Kode: ' + res.kode, confirmButtonText: 'OK', allowOutsideClick: false}).then(() => location.reload());
                        } else { Swal.fire('Gagal', res.msg, 'error'); }
                    });
                }
            }
        }

        // FUNGSI BATAL TRANSAKSI (GLOBAL)
        function cancelSale(kode) {
            Swal.fire({
                title: 'Batalkan Transaksi?',
                text: "Stok akan dikembalikan dan Kas akan dihapus!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#cbd5e1',
                confirmButtonText: 'Ya, Batalkan!',
                target: 'body', 
                customClass: { container: 'swal-z-index' } 
            }).then((result) => {
                if (result.isConfirmed) {
                    let fd = new FormData();
                    fd.append('act', 'batal_transaksi');
                    fd.append('kode', kode);
                    fetch('', { method: 'POST', body: fd }).then(r => r.json()).then(res => {
                        if (res.status == 'success') {
                            Swal.fire('Berhasil!', 'Transaksi dibatalkan.', 'success').then(() => location.reload());
                        } else { Swal.fire('Gagal', res.msg, 'error'); }
                    });
                }
            })
        }
    </script>
</body>
</html>