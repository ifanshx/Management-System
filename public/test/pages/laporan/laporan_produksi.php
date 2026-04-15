<?php 
require_once '../../config/database.php';
date_default_timezone_set('Asia/Jakarta');

if (!isset($_SESSION['status'])) { header("Location: ../../login.php"); exit; }
if ($_SESSION['role'] !== 'admin') { header("Location: ../dashboard.php"); exit; }

// --- FILTER ---
$default_awal  = date('Y-m-d', strtotime('monday this week'));
$default_akhir = date('Y-m-d', strtotime('sunday this week'));
$tgl_awal  = isset($_GET['tgl_awal']) ? $_GET['tgl_awal'] : $default_awal;
$tgl_akhir = isset($_GET['tgl_akhir']) ? $_GET['tgl_akhir'] : $default_akhir;
$keyword   = isset($_GET['keyword']) ? mysqli_real_escape_string($conn, $_GET['keyword']) : '';

// --- QUERY DATA ---
$sql = "SELECT hp.*, u.fullname 
        FROM hasil_produksi_borongan hp 
        JOIN users u ON hp.user_id = u.id 
        WHERE hp.status = 'Approved' 
        AND hp.tanggal BETWEEN '$tgl_awal' AND '$tgl_akhir'";

if(!empty($keyword)) {
    $sql .= " AND (u.fullname LIKE '%$keyword%' OR hp.jenis_pekerjaan LIKE '%$keyword%')";
}
$sql .= " ORDER BY hp.tanggal DESC, hp.created_at DESC";
$q_prod = mysqli_query($conn, $sql);

// --- KALKULASI ---
$list_data = [];
$stats = ['tot_qty' => 0, 'tot_upah' => 0, 'karyawan_aktif' => [], 'avg_qty' => 0];

while($row = mysqli_fetch_assoc($q_prod)) {
    $stats['tot_qty'] += $row['jumlah'];
    $stats['tot_upah'] += $row['total_upah'];
    if(!in_array($row['user_id'], $stats['karyawan_aktif'])) {
        $stats['karyawan_aktif'][] = $row['user_id'];
    }
    $list_data[] = $row;
}

$diff = strtotime($tgl_akhir) - strtotime($tgl_awal);
$days = max(1, round($diff / (60 * 60 * 24)) + 1); 
$stats['avg_qty'] = ($days > 0) ? round($stats['tot_qty'] / $days) : 0;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <?php include '../../layout/header.php'; ?>
    <link href="https://fonts.googleapis.com/css2?family=Roboto+Mono:wght@500&family=Inter:wght@400;500;600;800&display=swap" rel="stylesheet">
    <style>
        :root { --blue: #2563eb; --red: #dc2626; --green: #16a34a; --orange: #ca8a04; --dark: #1e293b; --purple: #8b5cf6; --sidebar-w: 260px; }
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
        .stat-card { background: #fff; border-radius: 12px; padding: 15px; border: 1px solid #e2e8f0; box-shadow: 0 2px 4px rgba(0,0,0,0.02); display: flex; flex-direction: column; justify-content: center; border-left: 4px solid transparent; }
        .stat-label { font-size: 11px; font-weight: 700; text-transform: uppercase; color: #64748b; }
        .stat-value { font-size: 24px; font-weight: 800; color: #1e293b; margin-top: 5px; }
        .sc-blue { border-left-color: var(--blue); } .sc-blue .stat-value { color: var(--blue); }
        .sc-green { border-left-color: var(--green); } .sc-green .stat-value { color: var(--green); }
        .sc-purple { border-left-color: var(--purple); } .sc-purple .stat-value { color: var(--purple); }
        .sc-orange { border-left-color: var(--orange); } .sc-orange .stat-value { color: var(--orange); }

        .card-table { background: white; border-radius: 12px; border: 1px solid #e2e8f0; overflow: hidden; margin-bottom: 20px; }
        .table-responsive { width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch; }
        .table-custom { width: 100%; border-collapse: collapse; min-width: 900px; }
        .table-custom th { background: #f8fafc; padding: 15px; font-size: 11px; text-transform: uppercase; color: #475569; border-bottom: 1px solid #e2e8f0; font-weight: 700; text-align: left; }
        .table-custom td { padding: 12px 15px; border-bottom: 1px solid #f3f4f6; font-size: 13px; text-align: left; vertical-align: middle; white-space: nowrap; }
        .table-custom tr:hover td { background: #f8fafc; }
        .table-custom tfoot td { background: #f1f5f9; font-weight: 800; border-top: 2px solid #e2e8f0; color: #334155; padding: 15px; }

        .font-mono { font-family: 'Roboto Mono', monospace; font-size: 12px; }
        .text-money { color: var(--green); font-weight: 700; }
        .badge-motor { background: #eff6ff; color: var(--blue); padding: 3px 8px; border-radius: 4px; font-size: 10px; font-weight: 700; margin-left: 5px; border: 1px solid #dbeafe; }
        .text-right { text-align: right !important; } .text-center { text-align: center !important; }
    </style>
</head>
<body>
    <?php include '../../layout/sidebar.php'; ?>
    
    <div class="main-content">
        
        <div class="page-header-custom">
            <div>
                <h1 class="page-title">Laporan Produksi</h1>
                <p class="page-subtitle">Periode: <?= date('d M Y', strtotime($tgl_awal)) ?> s/d <?= date('d M Y', strtotime($tgl_akhir)) ?></p>
            </div>
            
            <a href="../cetak/cetak_laporan_produksi.php?tgl_awal=<?= $tgl_awal ?>&tgl_akhir=<?= $tgl_akhir ?>&keyword=<?= urlencode($keyword) ?>" target="_blank" class="btn-act btn-green">
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
            <div class="form-group-filter" style="flex: 2;">
                <span class="form-label">Cari Karyawan / Pekerjaan</span>
                <input type="text" name="keyword" value="<?= htmlspecialchars($keyword) ?>" class="form-input" placeholder="Ketik nama...">
            </div>
            <button type="submit" class="btn-act btn-dark" style="margin-bottom: 2px;">
                <i class="fa fa-filter"></i> Filter
            </button>
        </form>

        <div class="stats-grid">
            <div class="stat-card sc-blue">
                <div class="stat-label">TOTAL PRODUKSI</div>
                <div class="stat-value"><?= number_format($stats['tot_qty']) ?> <span style="font-size:14px; font-weight:500; color:#64748b;">Pcs</span></div>
            </div>
            <div class="stat-card sc-green">
                <div class="stat-label">TOTAL UPAH</div>
                <div class="stat-value text-money">Rp <?= number_format($stats['tot_upah']) ?></div>
            </div>
            <div class="stat-card sc-purple">
                <div class="stat-label">KARYAWAN AKTIF</div>
                <div class="stat-value"><?= count($stats['karyawan_aktif']) ?> <span style="font-size:14px; font-weight:500; color:#64748b;">Org</span></div>
            </div>
            <div class="stat-card sc-orange">
                <div class="stat-label">RATA-RATA HARIAN</div>
                <div class="stat-value">~<?= number_format($stats['avg_qty']) ?> <span style="font-size:14px; font-weight:500; color:#64748b;">Pcs</span></div>
            </div>
        </div>

        <div class="card-table">
            <div class="table-responsive">
                <table class="table-custom">
                    <thead>
                        <tr>
                            <th width="120" class="text-center">Tanggal</th>
                            <th>Nama Karyawan</th>
                            <th>Jenis Pekerjaan</th>
                            <th width="100" class="text-center">Jumlah</th>
                            <th width="150" class="text-right">Total Upah</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($list_data)): ?>
                            <tr><td colspan="5" style="text-align:center; padding:40px; color:#94a3b8;">Tidak ada data produksi.</td></tr>
                        <?php else: ?>
                            <?php foreach($list_data as $d): 
                                $parts = explode(' - ', $d['jenis_pekerjaan']);
                                $job = $parts[0];
                                $motor = isset($parts[1]) ? $parts[1] : '';
                            ?>
                            <tr>
                                <td class="text-center font-mono" style="color:#64748b;">
                                    <?= date('d/m/Y', strtotime($d['tanggal'])) ?>
                                </td>
                                <td style="font-weight:600; color:#334155;">
                                    <?= htmlspecialchars($d['fullname']) ?>
                                </td>
                                <td>
                                    <?= htmlspecialchars($job) ?>
                                    <?php if($motor): ?><span class="badge-motor"><?= $motor ?></span><?php endif; ?>
                                </td>
                                <td class="text-center" style="font-weight:700;">
                                    <?= number_format($d['jumlah']) ?>
                                </td>
                                <td class="text-right font-mono text-money">
                                    Rp <?= number_format($d['total_upah']) ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" class="text-right" style="color:#64748b; padding-right:20px;">TOTAL PERIODE INI</td>
                            <td class="text-center" style="color:var(--blue);"><?= number_format($stats['tot_qty']) ?></td>
                            <td class="text-right text-money">Rp <?= number_format($stats['tot_upah']) ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

    </div>
    <?php include '../../layout/footer.php'; ?>
</body>
</html>