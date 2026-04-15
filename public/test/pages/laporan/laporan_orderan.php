<?php 
require_once '../../config/database.php';
date_default_timezone_set('Asia/Jakarta');

if (!isset($_SESSION['status'])) { header("Location: ../../login.php"); exit; }
if ($_SESSION['role'] !== 'admin') { header("Location: ../dashboard.php"); exit; }

// --- 1. FILTER PERIODE & STATUS ---
$default_awal  = date('Y-m-d', strtotime('monday this week'));
$default_akhir = date('Y-m-d', strtotime('sunday this week'));
$tgl_awal  = isset($_GET['tgl_awal']) ? $_GET['tgl_awal'] : $default_awal;
$tgl_akhir = isset($_GET['tgl_akhir']) ? $_GET['tgl_akhir'] : $default_akhir;
$status    = isset($_GET['status']) ? $_GET['status'] : '';

// --- 2. QUERY DATA ---
$sql = "SELECT * FROM orderan WHERE tanggal BETWEEN '$tgl_awal' AND '$tgl_akhir'";
if(!empty($status)) { $sql .= " AND status = '$status'"; }
$sql .= " ORDER BY tanggal DESC, created_at DESC";
$q_data = mysqli_query($conn, $sql);

// --- 3. PROSES DATA ---
$list_order = [];
$stats = ['total_trx' => 0, 'total_qty_pesan' => 0, 'total_qty_kirim' => 0, 'cnt_pending' => 0, 'cnt_selesai' => 0];

while($d = mysqli_fetch_assoc($q_data)) {
    $oid = $d['id'];
    $q_items = mysqli_query($conn, "SELECT * FROM order_items WHERE order_id='$oid'");
    $items = [];
    $sub_qty_pesan = 0; $sub_qty_kirim = 0;

    while($it = mysqli_fetch_assoc($q_items)) {
        $sub_qty_pesan += $it['qty']; $sub_qty_kirim += $it['qty_sent'];
        $items[] = $it;
    }
    
    $d['items'] = $items; 
    $d['real_qty_pesan'] = $sub_qty_pesan; $d['real_qty_kirim'] = $sub_qty_kirim;
    $d['progress'] = ($sub_qty_pesan > 0) ? round(($sub_qty_kirim / $sub_qty_pesan) * 100) : 0;

    $stats['total_trx']++;
    if($d['status'] != 'Batal') {
        $stats['total_qty_pesan'] += $sub_qty_pesan;
        $stats['total_qty_kirim'] += $sub_qty_kirim;
    }
    if($d['status'] == 'Pending') $stats['cnt_pending']++;
    if($d['status'] == 'Selesai') $stats['cnt_selesai']++;

    $list_order[] = $d;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <?php include '../../layout/header.php'; ?>
    <link href="https://fonts.googleapis.com/css2?family=Roboto+Mono:wght@500&family=Inter:wght@400;500;600;800&display=swap" rel="stylesheet">
    <style>
        :root { --blue: #2563eb; --red: #dc2626; --green: #16a34a; --orange: #ca8a04; --dark: #1e293b; --sidebar-w: 260px; }
        body { background-color: #f8fafc; font-family: 'Inter', sans-serif; color: #334155; }
        
        .main-content { margin-left: var(--sidebar-w); padding: 30px; padding-top: 100px; min-height: 100vh; transition: margin-left 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
        body.sidebar-collapsed .main-content { margin-left: 0; }
        @media (max-width: 992px) { .main-content { margin-left: 0; padding-top: 90px; } }

        .page-header-custom { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 25px; flex-wrap:wrap; gap:15px; }
        .page-title { font-size: 24px; font-weight: 800; color: #0f172a; margin: 0; line-height: 1.2; }
        .page-subtitle { color: #64748b; font-size: 14px; margin-top: 5px; font-weight: 500; }
        
        .filter-container { display: flex; gap: 15px; align-items: flex-end; padding: 20px; background: #fff; border-radius: 12px; border: 1px solid #e2e8f0; margin-bottom: 25px; flex-wrap: wrap; }
        .form-group-filter { flex: 1; min-width: 150px; }
        .form-label { font-size: 11px; font-weight: 600; color: #64748b; margin-bottom: 5px; display: block; text-transform: uppercase; }
        .form-input { width: 100%; padding: 10px 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 13px; color: #334155; outline: none; box-sizing: border-box; }
        .btn-act { border: none; padding: 10px 20px; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; transition: 0.2s; display: inline-flex; align-items: center; gap: 6px; text-decoration: none; height: 42px; }
        .btn-dark { background: var(--dark); color: white; } .btn-dark:hover { background: #0f172a; }
        .btn-green { background: var(--green); color: white; } .btn-green:hover { background: #059669; }

        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 25px; }
        .stat-card { background: #fff; border-radius: 12px; padding: 15px; border: 1px solid #e2e8f0; box-shadow: 0 2px 4px rgba(0,0,0,0.02); display: flex; flex-direction: column; justify-content: center; position: relative; overflow:hidden; }
        .stat-card.blue { border-left: 4px solid var(--blue); } .stat-card.green { border-left: 4px solid var(--green); }
        .stat-card.orange { border-left: 4px solid var(--orange); } .stat-card.red { border-left: 4px solid var(--red); }
        .stat-label { font-size: 11px; font-weight: 700; text-transform: uppercase; color: #64748b; margin-bottom: 5px; }
        .stat-value { font-size: 24px; font-weight: 800; color: #1e293b; font-family: 'Inter', sans-serif; }

        .card-table { background: white; border-radius: 12px; border: 1px solid #e2e8f0; overflow: hidden; margin-bottom: 20px; }
        .table-responsive { width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch; }
        .table-custom { width: 100%; border-collapse: collapse; min-width: 900px; }
        .table-custom th { background: #f8fafc; padding: 15px; font-size: 11px; text-transform: uppercase; color: #475569; border-bottom: 1px solid #e2e8f0; font-weight: 700; text-align: left; }
        .table-custom td { padding: 12px 15px; border-bottom: 1px solid #f3f4f6; font-size: 13px; text-align: left; vertical-align: top; }
        .table-custom tr:hover td { background: #f8fafc; }
        .table-custom tfoot td { background: #f9fafb; font-weight: 800; border-top: 2px solid #e2e8f0; color: #334155; padding: 15px; }

        .font-mono { font-family: 'Roboto Mono', monospace; font-size: 12px; }
        .text-center { text-align: center !important; } .text-right { text-align: right !important; }

        /* Item List */
        .item-list { list-style: none; padding: 0; margin: 0; }
        .item-list li { display: flex; justify-content: space-between; align-items:center; padding: 6px 0; border-bottom: 1px dashed #f1f5f9; font-size: 12px; }
        .item-list li:last-child { border-bottom: none; }
        .item-name { font-weight: 600; color: #4b5563; flex: 1; padding-right: 10px; }
        .stat-pill { padding: 2px 6px; border-radius: 4px; display: inline-block; white-space: nowrap; font-size: 10px; font-weight: 600; }
        .pill-pesan { color: #6b7280; background: #f1f5f9; }
        .pill-kirim { color: #ca8a04; background: #fef9c3; }
        .pill-done { color: #16a34a; background: #dcfce7; }

        /* Progress Bar */
        .progress-w { width: 100%; background: #f1f5f9; height: 6px; border-radius: 10px; margin-top: 6px; overflow: hidden; }
        .progress-f { height: 100%; border-radius: 10px; transition: width 0.5s ease; }
        
        /* Badges */
        .badge { padding: 4px 10px; border-radius: 6px; font-size: 10px; font-weight: 700; text-transform: uppercase; display: inline-block; }
        .bg-Pending { background: #fff7ed; color: #c2410c; border: 1px solid #ffedd5; }
        .bg-Proses { background: #eff6ff; color: #1d4ed8; border: 1px solid #dbeafe; }
        .bg-Selesai { background: #f0fdf4; color: #15803d; border: 1px solid #dcfce7; }
        .bg-Batal { background: #fef2f2; color: #b91c1c; border: 1px solid #fee2e2; }
    </style>
</head>
<body>
    <?php include '../../layout/sidebar.php'; ?>
    
    <div class="main-content">
        
        <div class="page-header-custom">
            <div>
                <h1 class="page-title">Laporan Order & Pengiriman</h1>
                <p class="page-subtitle">Periode: <?= date('d M Y', strtotime($tgl_awal)) ?> s/d <?= date('d M Y', strtotime($tgl_akhir)) ?></p>
            </div>
            
            <a href="../cetak/cetak_laporan_orderan.php?tgl_awal=<?= $tgl_awal ?>&tgl_akhir=<?= $tgl_akhir ?>&status=<?= $status ?>" target="_blank" class="btn-act btn-green">
                <i class="fa fa-print"></i> Cetak / PDF
            </a>
        </div>

        <form method="GET" class="filter-container">
            <div class="form-group-filter">
                <span class="form-label">Tanggal Awal</span>
                <input type="date" name="tgl_awal" value="<?= $tgl_awal ?>" class="form-input">
            </div>
            <div class="form-group-filter">
                <span class="form-label">Tanggal Akhir</span>
                <input type="date" name="tgl_akhir" value="<?= $tgl_akhir ?>" class="form-input">
            </div>
            <div class="form-group-filter">
                <span class="form-label">Status Order</span>
                <select name="status" class="form-input">
                    <option value="">-- Semua Status --</option>
                    <option value="Pending" <?= ($status=='Pending')?'selected':'' ?>>Pending</option>
                    <option value="Proses" <?= ($status=='Proses')?'selected':'' ?>>Proses</option>
                    <option value="Selesai" <?= ($status=='Selesai')?'selected':'' ?>>Selesai</option>
                    <option value="Batal" <?= ($status=='Batal')?'selected':'' ?>>Batal</option>
                </select>
            </div>
            <button type="submit" class="btn-act btn-dark" style="margin-bottom: 2px;">
                <i class="fa fa-filter"></i> Filter
            </button>
        </form>

        <div class="stats-grid">
            <div class="stat-card blue">
                <div class="stat-label">Total Transaksi</div>
                <div class="stat-value" style="color:#2563eb;"><?= number_format($stats['total_trx']) ?></div>
            </div>
            <div class="stat-card orange">
                <div class="stat-label">Item Dipesan</div>
                <div class="stat-value" style="color:#ca8a04;"><?= number_format($stats['total_qty_pesan']) ?></div>
            </div>
            <div class="stat-card green">
                <div class="stat-label">Item Terkirim</div>
                <div class="stat-value" style="color:#16a34a;"><?= number_format($stats['total_qty_kirim']) ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Selesai / Pending</div>
                <div class="stat-value"><?= $stats['cnt_selesai'] ?> / <?= $stats['cnt_pending'] ?></div>
            </div>
        </div>

        <div class="card-table">
            <div class="table-responsive">
                <table class="table-custom">
                    <thead>
                        <tr>
                            <th width="120">Tanggal</th>
                            <th width="200">Pelanggan</th>
                            <th>Rincian Barang & Pengiriman</th>
                            <th width="180" class="text-center">Progress</th>
                            <th width="100" class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($list_order)): ?>
                            <tr><td colspan="5" style="text-align:center; padding:40px; color:#94a3b8;">Tidak ada data order.</td></tr>
                        <?php else: ?>
                            <?php foreach($list_order as $row): 
                                $prog_color = ($row['status'] == 'Selesai') ? '#10b981' : '#f97316';
                            ?>
                            <tr>
                                <td class="font-mono" style="color:#64748b;"><?= date('d/m/Y', strtotime($row['tanggal'])) ?></td>
                                <td>
                                    <div style="font-weight:700; color:#1f2937;"><?= htmlspecialchars($row['nama_pelanggan']) ?></div>
                                    <?php if(!empty($row['keterangan'])): ?>
                                        <div style="font-size:11px; color:#ef4444; margin-top:4px; font-style:italic;">Note: <?= htmlspecialchars($row['keterangan']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <ul class="item-list">
                                        <?php foreach($row['items'] as $item): 
                                            $sisa = $item['qty'] - $item['qty_sent'];
                                            $pill_class = ($sisa <= 0) ? 'pill-done' : 'pill-kirim';
                                        ?>
                                        <li>
                                            <span class="item-name"><?= htmlspecialchars($item['nama_barang']) ?></span>
                                            <span class="item-stats">
                                                <span class="stat-pill pill-pesan">Pesan: <?= $item['qty'] ?></span>
                                                <span class="stat-pill <?= $pill_class ?>">Kirim: <?= $item['qty_sent'] ?></span>
                                            </span>
                                        </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </td>
                                <td class="text-center">
                                    <span style="font-size:12px; font-weight:700; color:<?= $prog_color ?>;">
                                        <?= $row['real_qty_kirim'] ?> / <?= $row['real_qty_pesan'] ?>
                                    </span>
                                    <div class="progress-w">
                                        <div class="progress-f" style="width: <?= $row['progress'] ?>%; background-color: <?= $prog_color ?>;"></div>
                                    </div>
                                    <span style="font-size:10px; color:#6b7280; display:block; margin-top:2px;"><?= $row['progress'] ?>% Terkirim</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-<?= $row['status'] ?>"><?= strtoupper($row['status']) ?></span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" class="text-right" style="padding-right:20px; color:#64748b;">GRAND TOTAL PERIODE INI</td>
                            <td class="text-center" style="font-size:14px; font-weight:800; color:#10b981;">
                                <?= number_format($stats['total_qty_kirim']) ?> / <?= number_format($stats['total_qty_pesan']) ?>
                            </td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

    </div>
    <?php include '../../layout/footer.php'; ?>
</body>
</html>