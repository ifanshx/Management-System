<?php
// ==========================================
// 1. KONEKSI & SESSION
// ==========================================
require_once '../../config/database.php';
if (!isset($_SESSION['status'])) { header("Location: ../../login.php"); exit; }

// --- AJAX HANDLER: GET DETAIL TRANSAKSI ---
if (isset($_POST['action']) && $_POST['action'] == 'get_detail') {
    $id_trx = intval($_POST['id']);
    $query = "SELECT d.*, b.kode_barang 
              FROM penjualan_detail d 
              LEFT JOIN barang b ON d.barang_id = b.id 
              WHERE d.penjualan_id = '$id_trx'";
    $result = mysqli_query($conn, $query);
    $details = [];
    while ($row = mysqli_fetch_assoc($result)) { $details[] = $row; }
    echo json_encode($details);
    exit;
}

// --- LOGIKA FILTER ---
$tgl_awal  = isset($_GET['tgl_awal']) ? $_GET['tgl_awal'] : date('Y-m-01');
$tgl_akhir = isset($_GET['tgl_akhir']) ? $_GET['tgl_akhir'] : date('Y-m-d');
$sales_id  = isset($_GET['sales_id']) ? $_GET['sales_id'] : 'all';

// --- BUILD QUERY WHERE ---
$where_sql = "WHERE DATE(tanggal) BETWEEN '$tgl_awal' AND '$tgl_akhir'";
if($sales_id != 'all') {
    $where_sql .= " AND sales_id = '$sales_id'";
}

// --- QUERY DATA SUMMARY ---
$q_summary = "SELECT 
                COUNT(id) as total_trx, 
                SUM(total_bayar) as total_omset, 
                SUM(total_item) as total_item_terjual 
              FROM penjualan $where_sql";
$d_summary = mysqli_fetch_assoc(mysqli_query($conn, $q_summary));

// --- QUERY DATA UTAMA ---
$q_laporan = "SELECT * FROM penjualan $where_sql ORDER BY tanggal DESC";
$res_laporan = mysqli_query($conn, $q_laporan);

// --- AMBIL LIST SALES UNTUK FILTER ---
$q_sales = mysqli_query($conn, "SELECT id, nama_sales FROM sales ORDER BY nama_sales ASC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <?php include '../../layout/header.php'; ?>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    
    <style>
        body { background-color: #f1f5f9; font-family: 'Inter', sans-serif; }
        
        .content-wrapper { padding: 30px !important; }

        .card-custom { 
            background: #fff; border-radius: 12px; border: 1px solid #e2e8f0; 
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); margin-bottom: 25px; 
        }
        .card-header-c { 
            padding: 15px 20px; background: #fff; border-bottom: 1px solid #f1f5f9; 
            font-weight: 700; color: #334155; display:flex; justify-content:space-between; align-items:center;
            border-radius: 12px 12px 0 0;
        }
        
        /* --- STATS GRID (COMPACT) --- */
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 20px; }
        .stat-card { 
            background: #fff; border-radius: 10px; padding: 15px; 
            border: 1px solid #e2e8f0; box-shadow: 0 2px 4px rgba(0,0,0,0.02); 
            display: flex; flex-direction: column;
        }
        .stat-label { font-size: 10px; color: #64748b; font-weight: 700; text-transform: uppercase; margin-bottom: 5px; }
        .stat-value { font-size: 20px; font-weight: 800; color: #1e293b; }
        
        .filter-area { display: flex; gap: 10px; align-items: flex-end; flex-wrap: wrap; }
        .form-control-sm { 
            padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 8px; 
            font-size: 13px; color: #334155; outline: none; background: #fff; min-width: 150px;
        }
        .form-control-sm:focus { border-color: #2563eb; }
        .filter-label { font-size: 11px; font-weight: 600; color: #64748b; margin-bottom: 4px; display: block; }

        .table-custom { width: 100%; border-collapse: collapse; }
        .table-custom th { 
            text-align: left; padding: 12px 15px; background: #f8fafc; color: #64748b; 
            font-size: 11px; text-transform: uppercase; border-bottom: 1px solid #e2e8f0; font-weight: 700; 
        }
        .table-custom td { padding: 10px 15px; border-bottom: 1px solid #f1f5f9; font-size: 13px; color: #334155; vertical-align: middle; }
        .table-custom tr:hover td { background: #f8fafc; }
        
        .badge { padding: 4px 10px; border-radius: 6px; font-size: 10px; font-weight: 700; display: inline-flex; align-items: center; gap: 5px; }
        .badge-code { background: #eff6ff; color: #2563eb; border: 1px solid #dbeafe; font-family: monospace; }
        .badge-user { background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; }
        .badge-sales { background: #fdf2f8; color: #db2777; border: 1px solid #fce7f3; }

        .btn-act { 
            border: none; padding: 9px 15px; border-radius: 8px; font-size: 12px; 
            font-weight: 600; cursor: pointer; transition: 0.2s; display: inline-flex; 
            align-items: center; gap: 6px; text-decoration: none; 
        }
        .btn-blue { background: #2563eb; color: white; } .btn-blue:hover { background: #1d4ed8; color:white; }
        .btn-green { background: #10b981; color: white; } .btn-green:hover { background: #059669; color:white; }
        .btn-outline { background: white; border: 1px solid #cbd5e1; color: #334155; } .btn-outline:hover { background: #f1f5f9; }

        .modal-overlay { 
            display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; 
            background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center; backdrop-filter: blur(2px); 
        }
        .modal-box { 
            background: white; width: 90%; max-width: 600px; padding: 0; border-radius: 12px; 
            box-shadow: 0 10px 25px rgba(0,0,0,0.2); animation: slideDown 0.3s; 
            overflow: hidden; display: flex; flex-direction: column; max-height: 85vh; 
        }
        .modal-header { 
            padding: 15px 20px; border-bottom: 1px solid #f1f5f9; display: flex; 
            justify-content: space-between; align-items: center; background: #fff; 
        }
        .modal-body { padding: 20px; overflow-y: auto; background: #fff; }
        .modal-footer { padding: 15px 20px; border-top: 1px solid #f1f5f9; text-align: right; background: #fff; }
        @keyframes slideDown { from {transform: translateY(-20px); opacity: 0;} to {transform: translateY(0); opacity: 1;} }
    </style>
</head>
<body>
    <?php include '../../layout/sidebar.php'; ?>
    
    <div class="content-wrapper">
        
        <div style="display:flex; justify-content:space-between; align-items:end; margin-bottom:25px; flex-wrap:wrap; gap:20px;">
            <div>
                <h2 style="font-weight:800; color:#1e293b; margin:0; font-size: 22px;">Laporan Penjualan</h2>
                <p style="color:#64748b; font-size:13px; margin-top:2px;">Rekapitulasi data transaksi penjualan barang</p>
            </div>
            
            <form method="GET" class="filter-area">
                <div>
                    <span class="filter-label">Filter Sales</span>
                    <select name="sales_id" class="form-control-sm">
                        <option value="all">-- Semua Sales --</option>
                        <?php while($s = mysqli_fetch_assoc($q_sales)): ?>
                            <option value="<?= $s['id'] ?>" <?= $sales_id == $s['id'] ? 'selected' : '' ?>><?= $s['nama_sales'] ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div>
                    <span class="filter-label">Dari Tanggal</span>
                    <input type="date" name="tgl_awal" value="<?= $tgl_awal ?>" class="form-control-sm">
                </div>
                <div>
                    <span class="filter-label">Sampai Tanggal</span>
                    <input type="date" name="tgl_akhir" value="<?= $tgl_akhir ?>" class="form-control-sm">
                </div>
                <button type="submit" class="btn-act btn-blue">
                    <i class="fa fa-filter"></i> Filter
                </button>
                
                <a href="../cetak/cetak_laporan_penjualan.php?tgl_awal=<?= $tgl_awal ?>&tgl_akhir=<?= $tgl_akhir ?>&sales_id=<?= $sales_id ?>" target="_blank" class="btn-act btn-green">
                    <i class="fa fa-print"></i> Cetak
                </a>
            </form>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-label">TOTAL OMSET</div>
                <div class="stat-value" style="color: #2563eb;">Rp <?= number_format($d_summary['total_omset'] ?? 0, 0, ',', '.') ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">TOTAL TRANSAKSI</div>
                <div class="stat-value"><?= number_format($d_summary['total_trx'] ?? 0) ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">ITEM TERJUAL</div>
                <div class="stat-value" style="color: #10b981;">
                    <?= number_format($d_summary['total_item_terjual'] ?? 0) ?> <span style="font-size:12px; color:#94a3b8; font-weight:500;">Pcs</span>
                </div>
            </div>
        </div>

        <div class="card-custom">
            <div class="card-header-c">
                <span style="font-size: 15px;">Rincian Transaksi</span>
                <input type="text" id="tableSearch" placeholder="Cari Kode Transaksi..." onkeyup="searchTable()"
                       style="padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px; width: 250px; outline:none;">
            </div>
            
            <div style="overflow-x: auto;">
                <table class="table-custom" id="mainTable">
                    <thead>
                        <tr>
                            <th width="50">No</th>
                            <th width="150">Kode TRX</th>
                            <th>Waktu</th>
                            <th>Pelanggan</th>
                            <th>Sales</th>
                            <th style="text-align: right;">Total Bayar</th>
                            <th width="100" style="text-align: center;">Opsi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $no = 1;
                        if(mysqli_num_rows($res_laporan) > 0):
                            while($row = mysqli_fetch_assoc($res_laporan)): 
                        ?>
                        <tr>
                            <td style="color:#64748b;"><?= $no++ ?></td>
                            <td><span class="badge badge-code"><?= $row['kode_transaksi'] ?></span></td>
                            <td>
                                <?= date('d/m/y', strtotime($row['tanggal'])) ?>
                                <span style="font-size:11px; color:#94a3b8;"><?= date('H:i', strtotime($row['tanggal'])) ?></span>
                            </td>
                            <td style="font-weight:600;"><?= $row['customer_name'] ?></td>
                            <td>
                                <?php if($row['sales_nama']): ?>
                                    <span class="badge badge-sales"><i class="fa fa-user-tag"></i> <?= $row['sales_nama'] ?></span>
                                <?php else: ?>
                                    <span style="color:#cbd5e1;">-</span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: right; font-weight:700; color:#1e293b;">
                                Rp <?= number_format($row['total_bayar'], 0, ',', '.') ?>
                            </td>
                            <td style="text-align: center;">
                                <button onclick="showDetail(<?= $row['id'] ?>, '<?= $row['kode_transaksi'] ?>')" class="btn-act btn-outline" style="padding: 5px 10px; font-size:11px;">
                                    <i class="fa fa-eye"></i> Detail
                                </button>
                            </td>
                        </tr>
                        <?php endwhile; else: ?>
                        <tr>
                            <td colspan="7" style="text-align:center; padding:40px; color:#94a3b8;">
                                <i class="fa fa-folder-open" style="font-size: 24px; margin-bottom: 10px;"></i><br>
                                Tidak ada data penjualan pada periode ini
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div id="modalDetail" class="modal-overlay">
        <div class="modal-box">
            <div class="modal-header">
                <h3 style="margin:0; font-size:15px; font-weight:800; color:#1e293b;">Detail: <span id="modalKodeTrx" style="color:#2563eb;"></span></h3>
                <span onclick="closeModal()" style="cursor:pointer; font-size:20px; color:#94a3b8;">&times;</span>
            </div>
            <div class="modal-body">
                <table class="table-custom" style="width:100%;">
                    <thead>
                        <tr>
                            <th style="background:#fff;">Produk</th>
                            <th style="background:#fff; text-align:right;">Harga</th>
                            <th style="background:#fff; text-align:center;">Qty</th>
                            <th style="background:#fff; text-align:right;">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody id="modalContent"></tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="closeModal()" class="btn-act btn-outline">Tutup</button>
            </div>
        </div>
    </div>

    <?php include '../../layout/footer.php'; ?>
    <script>
        function showDetail(id, kode) {
            document.getElementById('modalKodeTrx').innerText = kode;
            document.getElementById('modalContent').innerHTML = '<tr><td colspan="4" style="text-align:center; padding:20px;">Memuat data...</td></tr>';
            document.getElementById('modalDetail').style.display = 'flex';

            const formData = new FormData();
            formData.append('action', 'get_detail');
            formData.append('id', id);

            fetch('', { method: 'POST', body: formData })
            .then(response => response.json())
            .then(data => {
                let html = '';
                if(data.length > 0) {
                    data.forEach(item => {
                        html += `
                            <tr>
                                <td>
                                    <div style="font-weight:600; font-size:13px;">${item.nama_barang_snapshot}</div>
                                    <div style="font-size:11px; color:#94a3b8; font-family:monospace;">${item.kode_barang || '-'}</div>
                                </td>
                                <td style="text-align:right;">${new Intl.NumberFormat('id-ID').format(item.harga_satuan)}</td>
                                <td style="text-align:center;">${item.qty}</td>
                                <td style="text-align:right; font-weight:700;">${new Intl.NumberFormat('id-ID').format(item.subtotal)}</td>
                            </tr>
                        `;
                    });
                } else {
                    html = '<tr><td colspan="4" style="text-align:center; padding:20px;">Detail tidak ditemukan</td></tr>';
                }
                document.getElementById('modalContent').innerHTML = html;
            })
            .catch(err => {
                document.getElementById('modalContent').innerHTML = '<tr><td colspan="4" style="text-align:center; color:red; padding:20px;">Gagal memuat data</td></tr>';
            });
        }

        function closeModal() { document.getElementById('modalDetail').style.display = 'none'; }
        window.onclick = function(event) { if (event.target == document.getElementById('modalDetail')) closeModal(); }

        function searchTable() {
            let input = document.getElementById('tableSearch');
            let filter = input.value.toUpperCase();
            let table = document.getElementById('mainTable');
            let tr = table.getElementsByTagName('tr');
            for (let i = 1; i < tr.length; i++) {
                let td = tr[i].getElementsByTagName('td')[1];
                if (td) {
                    let txtValue = td.textContent || td.innerText;
                    tr[i].style.display = (txtValue.toUpperCase().indexOf(filter) > -1) ? "" : "none";
                }       
            }
        }
    </script>
</body>
</html>