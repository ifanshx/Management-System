<?php
// ==========================================
// 1. KONEKSI & SESSION
// ==========================================
require_once '../../config/database.php';
if (!isset($_SESSION['status'])) { header("Location: ../../login.php"); exit; }

// --- FILTER LOGIC ---
$kategori_filter = isset($_GET['kategori']) ? $_GET['kategori'] : 'all';
$status_filter   = isset($_GET['status']) ? $_GET['status'] : 'all';

// Base Query
$where_sql = "WHERE 1=1";

if ($kategori_filter != 'all') {
    $where_sql .= " AND jenis_bahan = '$kategori_filter'";
}

if ($status_filter == 'low') {
    $where_sql .= " AND stok <= stok_minimum AND stok > 0";
} elseif ($status_filter == 'empty') {
    $where_sql .= " AND stok = 0";
} elseif ($status_filter == 'safe') {
    $where_sql .= " AND stok > stok_minimum";
}

// --- QUERY DATA SUMMARY (KARTU ATAS) ---
// Total Aset (Global)
$q_asset = $conn->query("SELECT SUM(stok * harga_satuan) as total_aset FROM bahan_baku");
$d_asset = $q_asset->fetch_assoc();

// Hitung Item Global
$total_item = mysqli_num_rows($conn->query("SELECT id FROM bahan_baku"));
$item_low   = mysqli_num_rows($conn->query("SELECT id FROM bahan_baku WHERE stok <= stok_minimum AND stok > 0"));
$item_empty = mysqli_num_rows($conn->query("SELECT id FROM bahan_baku WHERE stok = 0"));

// --- QUERY DATA TABEL (FILTERED) ---
$sql_data = "SELECT * FROM bahan_baku $where_sql ORDER BY nama_bahan ASC";
$q_data   = $conn->query($sql_data);

// List Kategori untuk Dropdown
$q_kat = $conn->query("SELECT DISTINCT jenis_bahan FROM bahan_baku ORDER BY jenis_bahan ASC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <?php include '../../layout/header.php'; ?>
    
    <style>
        /* --- STYLE GLOBAL (SAMA DENGAN LAPORAN PENJUALAN) --- */
        body { background-color: #f1f5f9; font-family: 'Inter', sans-serif; }
        .content-wrapper { padding: 30px !important; }
        
        /* Card Container */
        .card-custom { 
            background: #fff; border-radius: 12px; border: 1px solid #e2e8f0; 
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); margin-bottom: 25px; 
        }
        .card-header-c { 
            padding: 20px; background: #fff; border-bottom: 1px solid #f1f5f9; 
            font-weight: 700; color: #334155; display:flex; justify-content:space-between; align-items:center;
            border-radius: 12px 12px 0 0;
        }
        
        /* Stats Grid */
        .stats-grid { 
            display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); 
            gap: 20px; margin-bottom: 25px; 
        }
        .stat-card { 
            background: #fff; border-radius: 12px; padding: 25px; 
            border: 1px solid #e2e8f0; box-shadow: 0 2px 4px rgba(0,0,0,0.02); 
            display: flex; flex-direction: column;
        }
        .stat-label { 
            font-size: 11px; color: #64748b; font-weight: 700; 
            text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px;
        }
        .stat-value { font-size: 24px; font-weight: 800; color: #1e293b; }
        
        /* Filter Area */
        .filter-area { display: flex; gap: 10px; align-items: flex-end; flex-wrap: wrap; }
        .form-control-sm { 
            padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 8px; 
            font-size: 13px; color: #334155; outline: none; background: #fff; min-width: 180px;
        }
        .form-control-sm:focus { border-color: #2563eb; }
        .filter-label { font-size: 11px; font-weight: 600; color: #64748b; margin-bottom: 4px; display: block; }

        /* Table Styles */
        .table-custom { width: 100%; border-collapse: collapse; }
        .table-custom th { 
            text-align: left; padding: 15px; background: #f8fafc; color: #64748b; 
            font-size: 11px; text-transform: uppercase; border-bottom: 1px solid #e2e8f0; font-weight: 700; 
        }
        .table-custom td { 
            padding: 15px; border-bottom: 1px solid #f1f5f9; font-size: 13px; 
            color: #334155; vertical-align: middle; 
        }
        .table-custom tr:hover td { background: #f8fafc; }
        
        /* Badges */
        .badge { padding: 5px 10px; border-radius: 6px; font-size: 11px; font-weight: 700; display: inline-flex; align-items: center; gap: 5px; }
        .badge-safe { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
        .badge-low { background: #fef9c3; color: #854d0e; border: 1px solid #fde047; }
        .badge-empty { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
        .badge-code { background: #eff6ff; color: #2563eb; border: 1px solid #dbeafe; font-family: monospace; }

        /* Buttons */
        .btn-act { 
            border: none; padding: 10px 16px; border-radius: 8px; font-size: 13px; 
            font-weight: 600; cursor: pointer; transition: 0.2s; display: inline-flex; 
            align-items: center; gap: 6px; text-decoration: none; 
        }
        .btn-blue { background: #2563eb; color: white; } .btn-blue:hover { background: #1d4ed8; color:white; }
        .btn-green { background: #10b981; color: white; } .btn-green:hover { background: #059669; color:white; }
    </style>
</head>
<body>
    <?php include '../../layout/sidebar.php'; ?>
    
    <div class="content-wrapper">
        
        <div style="display:flex; justify-content:space-between; align-items:end; margin-bottom:30px; flex-wrap:wrap; gap:20px;">
            <div>
                <h2 style="font-weight:800; color:#1e293b; margin:0; font-size: 24px;">Laporan Stok Bahan</h2>
                <p style="color:#64748b; font-size:14px; margin-top:5px;">Monitoring ketersediaan material dan valuasi aset gudang.</p>
            </div>
            
            <form method="GET" class="filter-area">
                <div>
                    <span class="filter-label">Filter Kategori</span>
                    <select name="kategori" class="form-control-sm">
                        <option value="all">-- Semua Kategori --</option>
                        <?php while($k = $q_kat->fetch_assoc()): ?>
                            <option value="<?= $k['jenis_bahan'] ?>" <?= $kategori_filter == $k['jenis_bahan'] ? 'selected' : '' ?>>
                                <?= $k['jenis_bahan'] ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div>
                    <span class="filter-label">Status Stok</span>
                    <select name="status" class="form-control-sm">
                        <option value="all">-- Semua Status --</option>
                        <option value="safe" <?= $status_filter == 'safe' ? 'selected' : '' ?>>Aman</option>
                        <option value="low" <?= $status_filter == 'low' ? 'selected' : '' ?>>Menipis (Alert)</option>
                        <option value="empty" <?= $status_filter == 'empty' ? 'selected' : '' ?>>Habis (0)</option>
                    </select>
                </div>
                <button type="submit" class="btn-act btn-blue">
                    <i class="fa fa-filter"></i> Filter
                </button>
                
                <a href="../cetak/cetak_laporan_stok.php?kategori=<?= $kategori_filter ?>&status=<?= $status_filter ?>" target="_blank" class="btn-act btn-green">
                    <i class="fa fa-print"></i> Cetak Laporan
                </a>
            </form>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-label">TOTAL NILAI ASET (GUDANG)</div>
                <div class="stat-value" style="color: #2563eb;">Rp <?= number_format($d_asset['total_aset'] ?? 0, 0, ',', '.') ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">TOTAL SKU / ITEM</div>
                <div class="stat-value"><?= number_format($total_item) ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">STOK MENIPIS (ALERT)</div>
                <div class="stat-value" style="color: #eab308;"><?= number_format($item_low) ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">STOK KOSONG</div>
                <div class="stat-value" style="color: #ef4444;"><?= number_format($item_empty) ?></div>
            </div>
        </div>

        <div class="card-custom">
            <div class="card-header-c">
                <span style="font-size: 16px;">Rincian Stok</span>
                <input type="text" id="tableSearch" placeholder="Cari Nama / Kode..." onkeyup="searchTable()"
                       style="padding: 10px 15px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 13px; width: 250px; outline:none;">
            </div>
            
            <div style="overflow-x: auto;">
                <table class="table-custom" id="mainTable">
                    <thead>
                        <tr>
                            <th width="50">No</th>
                            <th width="150">Kode Bahan</th>
                            <th>Nama Bahan</th>
                            <th>Kategori</th>
                            <th style="text-align: right;">Harga Rata-rata</th>
                            <th style="text-align: center;">Stok Fisik</th>
                            <th style="text-align: right;">Nilai Aset</th>
                            <th style="text-align: center;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $no = 1; 
                        $total_filtered_asset = 0;
                        if($q_data->num_rows > 0):
                            while($d = $q_data->fetch_assoc()):
                                $stok = floatval($d['stok']);
                                $min  = floatval($d['stok_minimum']);
                                $harga = floatval($d['harga_satuan']);
                                $aset  = $stok * $harga;
                                $total_filtered_asset += $aset;

                                // Badge Logic
                                if($stok == 0) {
                                    $badge = '<span class="badge badge-empty"><i class="fa fa-times-circle"></i> Habis</span>';
                                } elseif($stok <= $min) {
                                    $badge = '<span class="badge badge-low"><i class="fa fa-exclamation-triangle"></i> Menipis</span>';
                                } else {
                                    $badge = '<span class="badge badge-safe"><i class="fa fa-check-circle"></i> Aman</span>';
                                }
                        ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><span class="badge badge-code"><?= $d['kode_bahan'] ?></span></td>
                            <td style="font-weight: 600; color: #1e293b;"><?= $d['nama_bahan'] ?></td>
                            <td><?= $d['jenis_bahan'] ?></td>
                            <td style="text-align: right; font-family: monospace; color: #64748b;">
                                Rp <?= number_format($harga, 0, ',', '.') ?>
                            </td>
                            <td style="text-align: center;">
                                <strong><?= $stok ?></strong> <span style="font-size:11px; color:#94a3b8;"><?= $d['satuan'] ?></span>
                            </td>
                            <td style="text-align: right; font-weight: 700; color: #2563eb;">
                                Rp <?= number_format($aset, 0, ',', '.') ?>
                            </td>
                            <td style="text-align: center;"><?= $badge ?></td>
                        </tr>
                        <?php endwhile; else: ?>
                        <tr>
                            <td colspan="8" style="text-align:center; padding:40px; color:#94a3b8;">
                                <i class="fa fa-folder-open" style="font-size: 24px; margin-bottom: 10px;"></i><br>
                                Tidak ada data bahan baku sesuai filter.
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                    <tfoot style="background: #f8fafc; font-weight: 700;">
                        <tr>
                            <td colspan="6" style="text-align: right; padding: 15px; color: #64748b;">TOTAL ASET (FILTERED):</td>
                            <td style="text-align: right; padding: 15px; color: #10b981;">Rp <?= number_format($total_filtered_asset, 0, ',', '.') ?></td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <?php include '../../layout/footer.php'; ?>
    <script>
        function searchTable() {
            let input = document.getElementById('tableSearch');
            let filter = input.value.toUpperCase();
            let table = document.getElementById('mainTable');
            let tr = table.getElementsByTagName('tr');

            for (let i = 1; i < tr.length; i++) { // Skip header
                let tdName = tr[i].getElementsByTagName('td')[2]; // Kolom Nama
                let tdCode = tr[i].getElementsByTagName('td')[1]; // Kolom Kode
                
                if (tdName || tdCode) {
                    let txtName = tdName.textContent || tdName.innerText;
                    let txtCode = tdCode.textContent || tdCode.innerText;
                    
                    if (txtName.toUpperCase().indexOf(filter) > -1 || txtCode.toUpperCase().indexOf(filter) > -1) {
                        tr[i].style.display = "";
                    } else {
                        tr[i].style.display = "none";
                    }
                }        
            }
        }
    </script>
</body>
</html>