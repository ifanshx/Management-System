<?php
// 1. KONEKSI & SESSION
require_once '../../config/database.php';
if (!isset($_SESSION['status'])) { header("Location: ../../login.php"); exit; }

// --- AJAX: GET STOK SAAT INI ---
if (isset($_POST['act']) && $_POST['act'] == 'get_stok') {
    $id = intval($_POST['id']);
    $q = $conn->query("SELECT stok, satuan FROM bahan_baku WHERE id = $id");
    $d = $q->fetch_assoc();
    echo json_encode($d);
    exit;
}

// --- AJAX: DETAIL TRANSAKSI (MODAL) ---
if (isset($_POST['act']) && $_POST['act'] == 'get_detail_transaksi') {
    $kode = $conn->real_escape_string($_POST['kode']);
    $q = $conn->query("SELECT * FROM transaksi_bahan_baku WHERE kode_transaksi = '$kode'");
    $data = [];
    while($r = $q->fetch_assoc()) { $data[] = $r; }
    echo json_encode($data);
    exit;
}

// --- LOGIKA 1: SIMPAN PENGAMBILAN (CORE LOGIC) ---
if (isset($_POST['simpan_outgoing'])) {
    $conn->begin_transaction();
    try {
        $trx        = "OUT-" . date('ymd') . rand(100, 999);
        $tgl        = $_POST['tanggal'];
        $tujuan     = $_POST['tujuan'];      
        $pic_id     = $_POST['karyawan'];   // ID Karyawan dari dropdown
        $user_id    = $_SESSION['user_id'] ?? 1;

        // 1. Ambil Nama Karyawan dari Database Users
        $q_karyawan = $conn->query("SELECT fullname FROM users WHERE id = '$pic_id'");
        if ($q_karyawan->num_rows > 0) {
            $d_karyawan = $q_karyawan->fetch_assoc();
            $nama_pic   = $d_karyawan['fullname'];
        } else {
            $nama_pic   = "Unknown User";
        }

        $bahan_ids = $_POST['bahan_id']; 
        $qtys      = $_POST['qty'];       
        $kets      = $_POST['ket_item'];    

        for ($i = 0; $i < count($bahan_ids); $i++) {
            $id_bahan = intval($bahan_ids[$i]); // Pastikan integer
            $qty_ambil = floatval($qtys[$i]);
            $ket_item  = $conn->real_escape_string($kets[$i]);

            if($qty_ambil > 0 && $id_bahan != 0) {
                // 2. Cek Stok & Lock Row
                $q_old = $conn->query("SELECT * FROM bahan_baku WHERE id = $id_bahan FOR UPDATE");
                $d_old = $q_old->fetch_assoc();
                
                $stok_sebelum = floatval($d_old['stok']);
                
                // Validasi Stok
                if($stok_sebelum < $qty_ambil) {
                    throw new Exception("Stok {$d_old['nama_bahan']} kurang! Sisa: $stok_sebelum");
                }

                $stok_sesudah = $stok_sebelum - $qty_ambil;

                // 3. Insert Transaksi
                $stmt_out = $conn->prepare("INSERT INTO transaksi_bahan_baku (kode_transaksi, bahan_baku_id, nama_bahan, jenis_bahan, jumlah_ambil, satuan, stok_sebelum, stok_sesudah, tujuan_pengambilan, karyawan_pengambil, keterangan, status, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Completed', ?)");
                
                $stmt_out->bind_param("sissdssdsssi", 
                    $trx, 
                    $id_bahan, 
                    $d_old['nama_bahan'], 
                    $d_old['jenis_bahan'], 
                    $qty_ambil, 
                    $d_old['satuan'], 
                    $stok_sebelum, 
                    $stok_sesudah, 
                    $tujuan, 
                    $nama_pic, 
                    $ket_item, 
                    $user_id
                );
                
                if (!$stmt_out->execute()) {
                    throw new Exception("Gagal insert: " . $stmt_out->error);
                }

                // 4. Update Master Stok (INI YANG MENGURANGI STOK DI DB UTAMA)
                // Kita update tabel bahan_baku dengan stok_sesudah
                $update_master = $conn->query("UPDATE bahan_baku SET stok = $stok_sesudah WHERE id = $id_bahan");
                
                if (!$update_master) {
                     throw new Exception("Gagal update master stok: " . $conn->error);
                }
            }
        }

        $conn->commit();
        $_SESSION['success'] = "Pengambilan berhasil! Stok telah dikurangi. Kode: <b>$trx</b>";

    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error'] = "Gagal: " . $e->getMessage();
    }
    header("Location: outgoing_material.php"); exit;
}

// --- LOGIKA 2: HAPUS RIWAYAT (ROLLBACK STOK) ---
if (isset($_GET['hapus_kode'])) {
    $kode = $conn->real_escape_string($_GET['hapus_kode']);
    $conn->begin_transaction();
    try {
        // Ambil item yang akan dihapus
        $q_items = $conn->query("SELECT bahan_baku_id, jumlah_ambil FROM transaksi_bahan_baku WHERE kode_transaksi = '$kode'");
        
        while($item = $q_items->fetch_assoc()) {
            // Kembalikan Stok ke Master (Stok + Jumlah Ambil)
            $conn->query("UPDATE bahan_baku SET stok = stok + {$item['jumlah_ambil']} WHERE id = {$item['bahan_baku_id']}");
        }

        // Hapus Data Transaksi
        $conn->query("DELETE FROM transaksi_bahan_baku WHERE kode_transaksi = '$kode'");
        
        $conn->commit();
        $_SESSION['success'] = "Transaksi <b>$kode</b> dihapus. Stok dikembalikan ke gudang.";
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error'] = "Gagal menghapus: " . $e->getMessage();
    }
    header("Location: outgoing_material.php"); exit;
}

// --- LOAD DATA ---
$q_bahan_list = $conn->query("SELECT * FROM bahan_baku WHERE stok > 0 ORDER BY nama_bahan ASC");
$bahan_array = [];
while($b = $q_bahan_list->fetch_assoc()) { $bahan_array[] = $b; }

// Ambil Karyawan Aktif dari Tabel Users
$q_users = $conn->query("SELECT id, fullname, pin FROM users ORDER BY fullname ASC");

// --- HISTORY QUERY (GROUP CONCAT untuk Preview Barang) ---
$q_hist_group = $conn->query("
    SELECT 
        kode_transaksi, 
        created_at, 
        tujuan_pengambilan, 
        karyawan_pengambil, 
        COUNT(id) as total_item,
        GROUP_CONCAT(CONCAT(nama_bahan, ' (', TRIM(jumlah_ambil)+0, ')') SEPARATOR ', ') as list_barang
    FROM transaksi_bahan_baku 
    GROUP BY kode_transaksi 
    ORDER BY created_at DESC 
    LIMIT 20
");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <?php include '../../layout/header.php'; ?>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <style>
        /* BASE RESET */
        * { box-sizing: border-box; }
        body { background-color: #f1f5f9; font-family: 'Inter', sans-serif; -webkit-font-smoothing: antialiased; }
        .content-wrapper { padding: 20px 30px !important; transition: all 0.3s ease; }
        
        /* GRID LAYOUT */
        .procurement-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 25px; align-items: start; }

        /* CARD STYLES */
        .card-pro { 
            background: #fff; border-radius: 12px; border: 1px solid #e2e8f0; 
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02); overflow: hidden; margin-bottom: 25px; 
        }
        .card-header-pro { 
            padding: 15px 20px; background: #fff; border-bottom: 1px solid #f1f5f9; 
            display: flex; justify-content: space-between; align-items: center; 
        }
        .card-header-pro h3 { margin: 0; font-size: 16px; font-weight: 700; color: #1e293b; display:flex; align-items:center; gap:8px; }
        .card-body-pro { padding: 25px; }

        /* FORM ELEMENTS */
        .form-row-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 20px; }
        .form-label { display: block; font-size: 11px; font-weight: 700; color: #64748b; margin-bottom: 6px; text-transform: uppercase; }
        .form-control { 
            width: 100%; padding: 10px 12px; border: 1px solid #cbd5e1; 
            border-radius: 8px; font-size: 13px; outline: none; transition: 0.2s;
        }
        .form-control:focus { border-color: #f97316; box-shadow: 0 0 0 3px rgba(249, 115, 22, 0.1); } 
        
        /* Select2 Custom */
        .select2-container .select2-selection--single { height: 42px; border: 1px solid #cbd5e1; border-radius: 8px; display: flex; align-items: center; }
        .select2-container--default .select2-selection--single .select2-selection__arrow { top: 8px; }
        
        /* TABLE INPUT */
        .table-input { width: 100%; border-collapse: separate; border-spacing: 0; margin-bottom: 15px; }
        .table-input th { text-align: left; padding: 12px; background: #fff7ed; color: #c2410c; font-size: 12px; border-bottom: 2px solid #ffedd5; font-weight: 700; text-transform: uppercase; }
        .table-input td { padding: 12px; border-bottom: 1px solid #f1f5f9; vertical-align: top; }
        
        /* BUTTONS */
        .btn-add-row { 
            background: #fff7ed; color: #c2410c; border: 1px dashed #fdba74; 
            width: 100%; padding: 12px; border-radius: 8px; font-size: 13px; 
            font-weight: 600; cursor: pointer; transition: 0.2s; display: flex; align-items: center; justify-content: center; gap: 8px;
        }
        .btn-add-row:hover { background: #ffedd5; border-color: #fb923c; }

        .btn-del-row { 
            background: #fee2e2; color: #ef4444; border: none; width: 32px; height: 32px; 
            border-radius: 6px; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: 0.2s;
        }
        .btn-del-row:hover { background: #fecaca; }

        .btn-submit { 
            width: 100%; padding: 14px; background: #f97316; color: #fff; 
            border: none; border-radius: 8px; font-weight: 700; cursor: pointer; 
            font-size: 14px; margin-top: 20px; transition: 0.2s; box-shadow: 0 4px 6px -1px rgba(249, 115, 22, 0.2);
        }
        .btn-submit:active { transform: translateY(1px); }

        /* STOCK DISPLAY */
        .stock-display { 
            font-size: 11px; font-weight: 700; color: #059669; margin-top: 4px; 
            display: inline-block; background: #ecfdf5; padding: 2px 6px; border-radius: 4px; border: 1px solid #a7f3d0;
        }
        
        /* HISTORY LIST */
        .hist-item { padding: 15px; border-bottom: 1px solid #f1f5f9; display: flex; gap: 15px; align-items: flex-start; cursor: pointer; transition: 0.2s; position: relative; }
        .hist-item:last-child { border-bottom: none; }
        .hist-item:hover { background: #fff7ed; }
        .hist-icon { 
            width: 40px; height: 40px; background: #ffedd5; color: #c2410c; 
            border-radius: 10px; display: flex; align-items: center; justify-content: center; 
            font-size: 18px; flex-shrink: 0; margin-top: 2px;
        }
        .hist-info { flex: 1; min-width: 0; }
        .hist-code { font-weight: 700; font-size: 13px; color: #f97316; font-family: monospace; display: block; margin-bottom: 2px; }
        .hist-meta { font-size: 11px; color: #94a3b8; display: flex; justify-content: space-between; margin-bottom: 6px; }
        
        .hist-pic { font-size: 13px; color: #334155; font-weight: 600; display: flex; align-items: center; gap: 5px; margin-bottom: 6px; }
        
        .hist-summary { 
            font-size: 11px; color: #475569; line-height: 1.4; 
            background: #fafaf9; padding: 8px; border-radius: 6px; 
            border: 1px solid #e7e5e4; display: block; font-style: italic;
        }

        /* MODAL */
        .modal-overlay { 
            display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; 
            background: rgba(15, 23, 42, 0.7); z-index: 9999; align-items: center; justify-content: center; backdrop-filter: blur(4px); 
            padding: 20px;
        }
        .modal-box { 
            background: #fff; width: 100%; max-width: 600px; border-radius: 16px; 
            box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25); animation: zoomIn 0.2s; 
            display:flex; flex-direction:column; max-height: 90vh; overflow: hidden;
        }
        @keyframes zoomIn { from {transform: scale(0.95); opacity:0;} to {transform: scale(1); opacity:1;} }

        /* --- MOBILE RESPONSIVE CSS --- */
        @media (max-width: 900px) {
            .content-wrapper { padding: 15px !important; }
            .procurement-grid { grid-template-columns: 1fr; gap: 20px; }
            .form-row-2 { grid-template-columns: 1fr; gap: 15px; }
            
            /* TRANSFORM TABLE TO CARDS */
            #itemTable thead { display: none; }
            #itemTable, #itemTable tbody, #itemTable tr, #itemTable td { display: block; width: 100%; }
            
            #itemTable tr {
                background: #fff;
                border: 1px solid #e2e8f0;
                border-radius: 12px;
                padding: 15px;
                margin-bottom: 15px;
                position: relative;
                box-shadow: 0 2px 4px rgba(0,0,0,0.02);
            }

            #itemTable td { padding: 5px 0; border: none; }
            
            /* Row Layouting */
            #itemTable td:nth-child(1) { margin-bottom: 10px; padding-right: 30px; } /* Select Bahan */
            
            /* Qty & Ket Side-by-Side */
            #itemTable td:nth-child(2),
            #itemTable td:nth-child(3) { display: inline-block; vertical-align: top; }
            
            #itemTable td:nth-child(2) { width: 35%; margin-right: 4%; } /* Qty */
            #itemTable td:nth-child(3) { width: 59%; } /* Ket */
            
            /* Delete Button Absolute Top Right */
            #itemTable td:nth-child(4) { 
                position: absolute; top: 10px; right: 10px; width: auto; padding: 0; 
            }
        }
    </style>
</head>
<body>
    <?php include '../../layout/sidebar.php'; ?>

    <div class="content-wrapper">
        <div style="margin-bottom: 25px;">
            <h2 style="font-weight:800; color:#1e293b; margin:0; font-size: 22px;">Pengambilan Bahan</h2>
            <p style="color:#64748b; margin-top:4px; font-size:13px;">Input pengeluaran material untuk produksi.</p>
        </div>

        <div class="procurement-grid">
            
            <div>
                <form method="POST" id="formOut">
                    <div class="card-pro">
                        <div class="card-header-pro">
                            <h3><i class="fa fa-dolly" style="color:#f97316;"></i> Form Keluar Barang</h3>
                        </div>
                        <div class="card-body-pro">
                            
                            <div class="form-row-2">
                                <div>
                                    <label class="form-label">Tanggal Ambil</label>
                                    <input type="date" name="tanggal" class="form-control" value="<?= date('Y-m-d') ?>" required>
                                </div>
                                <div>
                                    <label class="form-label">Nama Pengambil (PIC)</label>
                                    <select name="karyawan" class="form-control select2" required style="width: 100%;">
                                        <option value="">-- Cari Karyawan --</option>
                                        <?php mysqli_data_seek($q_users, 0); while($k = $q_users->fetch_assoc()): ?>
                                            <option value="<?= $k['id'] ?>"><?= strtoupper($k['fullname']) ?> (PIN: <?= $k['pin'] ?>)</option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                            </div>

                            <div style="margin-bottom: 25px;">
                                <label class="form-label">Tujuan / Divisi</label>
                                <select name="tujuan" class="form-control select2" required style="width: 100%;">
                                    <option value="Produksi Knalpot">Produksi Knalpot</option>
                                    <option value="Perbaikan (Repair)">Perbaikan (Repair)</option>
                                    <option value="R & D (Research)">R & D (Research)</option>
                                    <option value="Maintenance Mesin">Maintenance Mesin</option>
                                    <option value="Lainnya">Lainnya</option>
                                </select>
                            </div>
                            
                            <div style="background:#f8fafc; border:1px solid #f1f5f9; border-radius:12px; padding:10px;">
                                <table class="table-input" id="itemTable">
                                    <thead>
                                        <tr>
                                            <th width="45%">Bahan Baku</th>
                                            <th width="20%">Qty Ambil</th>
                                            <th width="30%">Keterangan</th>
                                            <th width="5%"></th>
                                        </tr>
                                    </thead>
                                    <tbody id="tableBody"></tbody>
                                </table>
                                
                                <button type="button" class="btn-add-row" onclick="addRow()">
                                    <i class="fa fa-plus-circle"></i> Tambah Item Lain
                                </button>
                            </div>

                            <button type="submit" name="simpan_outgoing" class="btn-submit">
                                <i class="fa fa-paper-plane"></i> PROSES PENGELUARAN
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <div>
                <div class="card-pro" style="max-height: 800px; display: flex; flex-direction: column;">
                    <div class="card-header-pro">
                        <h3><i class="fa fa-history" style="color:#64748b;"></i> Riwayat Keluar</h3>
                    </div>
                    <div style="overflow-y: auto; flex: 1;">
                        <?php if($q_hist_group->num_rows > 0): ?>
                            <?php while($h = $q_hist_group->fetch_assoc()): ?>
                            <div class="hist-item" onclick="viewDetail('<?= $h['kode_transaksi'] ?>', '<?= $h['tujuan_pengambilan'] ?>', '<?= $h['karyawan_pengambil'] ?>')">
                                <div class="hist-icon"><i class="fa fa-box-open"></i></div>
                                <div class="hist-info">
                                    <div class="hist-meta">
                                        <span class="hist-code"><?= $h['kode_transaksi'] ?></span>
                                        <span><?= date('d/m/y', strtotime($h['created_at'])) ?></span>
                                    </div>
                                    
                                    <div class="hist-pic">
                                        <i class="fa fa-user-circle" style="color:#94a3b8;"></i> <?= $h['karyawan_pengambil'] ?>
                                    </div>

                                    <div class="hist-summary">
                                        <?= $h['list_barang'] ?>
                                    </div>
                                </div>
                                <a href="?hapus_kode=<?= $h['kode_transaksi'] ?>" onclick="event.stopPropagation(); return confirm('Hapus transaksi <?= $h['kode_transaksi'] ?>? Stok akan dikembalikan!');" style="position:absolute; top:15px; right:15px; color:#ef4444; background:#fee2e2; width:24px; height:24px; display:flex; align-items:center; justify-content:center; border-radius:4px; font-size:10px;"><i class="fa fa-trash"></i></a>
                            </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div style="padding:40px 20px; text-align:center; color:#94a3b8;">
                                <i class="fa fa-clipboard-list" style="font-size:30px; margin-bottom:10px; opacity:0.5;"></i><br>
                                Belum ada riwayat.
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
                    <h3 style="margin:0; font-size:16px; color:#1e293b;">Bukti Pengeluaran</h3>
                    <div style="font-size:12px; color:#64748b; margin-top:2px;" id="detInfo">OUT-XXXX</div>
                </div>
                <span onclick="closeModalDetail()" style="cursor:pointer; font-size:24px; color:#94a3b8; width:30px; height:30px; text-align:center; line-height:30px;">&times;</span>
            </div>
            <div class="modal-body" style="padding:0; overflow-y:auto; flex:1;">
                <table class="table-input" style="margin:0;">
                    <thead style="background:#fff; border-bottom:2px solid #f1f5f9;">
                        <tr>
                            <th style="padding-left:20px;">Bahan Baku</th>
                            <th style="text-align:center;">Qty</th>
                            <th style="padding-right:20px;">Ket</th>
                        </tr>
                    </thead>
                    <tbody id="detContent"></tbody>
                </table>
            </div>
            <div style="padding:15px 20px; border-top:1px solid #e2e8f0; text-align:right; background:#fff;">
                <a href="#" id="btnCetak" target="_blank" style="display:block; width:100%; padding:12px; background:#f97316; color:#fff; border-radius:8px; text-decoration:none; font-size:14px; font-weight:700; text-align:center;">
                    <i class="fa fa-print"></i> CETAK SURAT BARANG OUT
                </a>
            </div>
        </div>
    </div>

    <?php include '../../layout/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    
    <script>
        const bahanData = <?= json_encode($bahan_array) ?>;

        $(document).ready(function() {
            $('.select2').select2();
            addRow(); 
        });

        // --- ROW FUNCTION (Disesuaikan untuk Mobile Card View) ---
        function addRow() {
            let options = '<option value="">-- Pilih Bahan --</option>';
            bahanData.forEach(b => { 
                options += `<option value="${b.id}" data-stok="${b.stok}" data-satuan="${b.satuan}">${b.kode_bahan} - ${b.nama_bahan}</option>`; 
            });

            // Struktur TR disesuaikan agar bisa di-style menjadi Card di Mobile
            let row = `
                <tr>
                    <td>
                        <select name="bahan_id[]" class="form-control select2-item" required style="width:100%;" onchange="checkStok(this)">
                            ${options}
                        </select>
                        <span class="stock-display">Stok: -</span>
                    </td>
                    <td>
                        <input type="number" name="qty[]" class="form-control qty-input" step="0.01" placeholder="Qty" required oninput="validateQty(this)">
                    </td>
                    <td>
                        <input type="text" name="ket_item[]" class="form-control" placeholder="Keterangan...">
                    </td>
                    <td>
                        <button type="button" class="btn-del-row" onclick="removeRow(this)"><i class="fa fa-trash"></i></button>
                    </td>
                </tr>
            `;
            $('#tableBody').append(row);
            $('.select2-item').select2();
        }

        function removeRow(btn) { 
            let rowCount = $('#tableBody tr').length;
            if(rowCount > 1) $(btn).closest('tr').remove();
            else Swal.fire({icon:'warning', title:'Perhatian', text:'Minimal harus ada 1 item transaksi.'});
        }

        function checkStok(select) {
            let row = $(select).closest('tr');
            let option = $(select).find(':selected');
            let stok = parseFloat(option.data('stok')) || 0;
            let satuan = option.data('satuan') || '';
            
            let display = row.find('.stock-display');
            display.text(`Stok: ${stok} ${satuan}`);
            
            // Set max attribute untuk validasi
            row.find('.qty-input').attr('data-limit', stok);
        }

        function validateQty(input) {
            let max = parseFloat($(input).attr('data-limit')) || 0;
            let val = parseFloat($(input).val()) || 0;
            if(max > 0 && val > max) {
                Swal.fire({icon:'warning', title:'Stok Tidak Cukup!', text:'Maksimal ambil: ' + max, timer:1500});
                $(input).val(max);
            }
        }

        function viewDetail(kode, tujuan, pic) {
            document.getElementById('detInfo').innerText = `${kode} • ${tujuan} • ${pic}`;
            document.getElementById('btnCetak').href = "cetak/cetak_bukti_keluar.php?kode=" + kode;
            document.getElementById('modalDetail').style.display = 'flex';
            document.getElementById('detContent').innerHTML = '<tr><td colspan="3" style="text-align:center; padding:30px; color:#64748b;">Loading data...</td></tr>';

            $.post('', { act: 'get_detail_transaksi', kode: kode }, function(res) {
                let data = JSON.parse(res);
                let html = '';
                data.forEach(d => {
                    html += `
                        <tr>
                            <td style="padding-left:20px;">
                                <div style="font-weight:700; color:#334155;">${d.nama_bahan}</div>
                                <div style="font-size:10px; color:#94a3b8;">${d.jenis_bahan}</div>
                            </td>
                            <td style="text-align:center; font-weight:700; color:#c2410c;">${parseFloat(d.jumlah_ambil)} ${d.satuan}</td>
                            <td style="font-size:12px; color:#64748b; padding-right:20px;">${d.keterangan || '-'}</td>
                        </tr>
                    `;
                });
                document.getElementById('detContent').innerHTML = html;
            });
        }

        function closeModalDetail() { document.getElementById('modalDetail').style.display = 'none'; }
        window.onclick = function(e) { if(e.target == document.getElementById('modalDetail')) closeModalDetail(); }

        <?php if(isset($_SESSION['success'])): ?>
            Swal.fire({icon:'success', title:'Berhasil', html:'<?= $_SESSION['success'] ?>', timer:2000, showConfirmButton:false});
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>
        <?php if(isset($_SESSION['error'])): ?>
            Swal.fire({icon:'error', title:'Gagal', text:'<?= $_SESSION['error'] ?>'});
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
    </script>
</body>
</html>