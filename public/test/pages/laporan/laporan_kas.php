<?php 
require_once '../../config/database.php';
date_default_timezone_set('Asia/Jakarta');

if (!isset($_SESSION['status'])) { header("Location: ../../login.php"); exit; }
if ($_SESSION['role'] !== 'admin') { header("Location: ../dashboard.php"); exit; }

// --- 1. FILTER DATA ---
$default_awal  = date('Y-m-d', strtotime('monday this week'));
$default_akhir = date('Y-m-d', strtotime('sunday this week'));
$tgl_awal  = isset($_GET['tgl_awal']) ? $_GET['tgl_awal'] : $default_awal;
$tgl_akhir = isset($_GET['tgl_akhir']) ? $_GET['tgl_akhir'] : $default_akhir;

// --- 2. HITUNG SALDO AWAL ---
$q_awal = mysqli_query($conn, "SELECT 
    SUM(CASE WHEN jenis='Masuk' THEN nominal ELSE 0 END) as tot_masuk,
    SUM(CASE WHEN jenis='Keluar' THEN nominal ELSE 0 END) as tot_keluar,
    (SUM(CASE WHEN jenis='Masuk' AND metode='Cash' THEN nominal ELSE 0 END) - 
     SUM(CASE WHEN jenis='Keluar' AND metode='Cash' THEN nominal ELSE 0 END)) as awal_cash,
    (SUM(CASE WHEN jenis='Masuk' AND metode='ATM' THEN nominal ELSE 0 END) - 
     SUM(CASE WHEN jenis='Keluar' AND metode='ATM' THEN nominal ELSE 0 END)) as awal_atm
    FROM transaksi_kas WHERE tanggal < '$tgl_awal'");

$d_awal = mysqli_fetch_assoc($q_awal);
$saldo_awal_global = $d_awal['tot_masuk'] - $d_awal['tot_keluar'];
$saldo_awal_cash   = $d_awal['awal_cash'] ?? 0;
$saldo_awal_atm    = $d_awal['awal_atm'] ?? 0;

// --- 3. AMBIL DATA TRANSAKSI ---
$sql = "SELECT * FROM transaksi_kas 
        WHERE tanggal BETWEEN '$tgl_awal' AND '$tgl_akhir' 
        ORDER BY tanggal ASC, created_at ASC";
$q_kas = mysqli_query($conn, $sql);

// --- 4. CALCULATE LOOP ---
$total_masuk = 0; $total_keluar = 0;
$mutasi_cash_masuk = 0; $mutasi_cash_keluar = 0;
$mutasi_atm_masuk = 0; $mutasi_atm_keluar = 0;

$list_data = [];
$running_saldo = $saldo_awal_global;

while($d = mysqli_fetch_assoc($q_kas)) {
    if($d['jenis'] == 'Masuk') {
        $total_masuk += $d['nominal'];
        $running_saldo += $d['nominal'];
        if($d['metode'] == 'Cash') $mutasi_cash_masuk += $d['nominal']; else $mutasi_atm_masuk += $d['nominal'];
    } else {
        $total_keluar += $d['nominal'];
        $running_saldo -= $d['nominal'];
        if($d['metode'] == 'Cash') $mutasi_cash_keluar += $d['nominal']; else $mutasi_atm_keluar += $d['nominal'];
    }
    $d['saldo_row'] = $running_saldo;
    $list_data[] = $d;
}

$saldo_akhir_global = $saldo_awal_global + $total_masuk - $total_keluar;
$saldo_akhir_cash   = $saldo_awal_cash + $mutasi_cash_masuk - $mutasi_cash_keluar;
$saldo_akhir_atm    = $saldo_awal_atm + $mutasi_atm_masuk - $mutasi_atm_keluar;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <?php include '../../layout/header.php'; ?>
    <link href="https://fonts.googleapis.com/css2?family=Roboto+Mono:wght@500&family=Inter:wght@400;500;600;800&display=swap" rel="stylesheet">
    <style>
        :root { --blue: #2563eb; --red: #dc2626; --green: #16a34a; --orange: #ca8a04; --purple: #8b5cf6; --dark: #1e293b; --sidebar-w: 260px; }
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
        .stat-card { background: #fff; border-radius: 12px; padding: 20px; border: 1px solid #e2e8f0; box-shadow: 0 2px 4px rgba(0,0,0,0.02); display: flex; flex-direction: column; justify-content: center; position: relative; overflow:hidden; }
        .stat-card.primary { background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%); color: #fff; border:none; }
        .stat-card.primary .stat-label { color: rgba(255,255,255,0.8); }
        .stat-card.primary .stat-value { color: #fff; }
        
        .stat-label { font-size: 11px; font-weight: 700; text-transform: uppercase; color: #64748b; margin-bottom: 5px; }
        .stat-value { font-size: 24px; font-weight: 800; color: #1e293b; font-family: 'Inter', sans-serif; }
        .text-green { color: var(--green); } .text-red { color: var(--red); }

        .card-table { background: white; border-radius: 12px; border: 1px solid #e2e8f0; overflow: hidden; margin-bottom: 20px; }
        .table-responsive { width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch; }
        .table-custom { width: 100%; border-collapse: collapse; min-width: 900px; }
        .table-custom th { background: #f8fafc; padding: 15px; font-size: 11px; text-transform: uppercase; color: #475569; border-bottom: 1px solid #e2e8f0; font-weight: 700; text-align: left; }
        .table-custom td { padding: 12px 15px; border-bottom: 1px solid #f3f4f6; font-size: 13px; text-align: left; vertical-align: middle; white-space: nowrap; }
        .table-custom tr:hover td { background: #f8fafc; }
        .table-custom tfoot td { background: #fff; border-top: 2px solid #e2e8f0; padding: 20px; font-weight: 700; }

        .font-mono { font-family: 'Roboto Mono', monospace; font-size: 12px; }
        .text-right { text-align: right !important; } .text-center { text-align: center !important; }
        
        .badge { padding: 4px 10px; border-radius: 6px; font-size: 10px; font-weight: 700; text-transform: uppercase; display: inline-block; }
        .badge-in { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
        .badge-out { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
        .badge-cash { background: #ffedd5; color: #9a3412; border: 1px solid #fed7aa; }
        .badge-atm { background: #ede9fe; color: #5b21b6; border: 1px solid #ddd6fe; }

        .saldo-breakdown { display: flex; gap: 15px; margin-bottom: 25px; }
        .break-card { flex: 1; background: #fff; padding: 15px; border-radius: 12px; border: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; }
        .break-label { font-size: 12px; font-weight: 600; color: #64748b; }
        .break-val { font-size: 18px; font-weight: 800; color: #334155; }
        
        @media (max-width: 768px) { .saldo-breakdown { flex-direction: column; } }
    </style>
</head>
<body>
    <?php include '../../layout/sidebar.php'; ?>
    
    <div class="main-content">
        
        <div class="page-header-custom">
            <div>
                <h1 class="page-title">Laporan Arus Kas</h1>
                <p class="page-subtitle">Periode: <?= date('d M Y', strtotime($tgl_awal)) ?> s/d <?= date('d M Y', strtotime($tgl_akhir)) ?></p>
            </div>
            
            <a href="../cetak/cetak_laporan_kas.php?tgl_awal=<?= $tgl_awal ?>&tgl_akhir=<?= $tgl_akhir ?>" target="_blank" class="btn-act btn-green">
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
            <button type="submit" class="btn-act btn-dark" style="margin-bottom: 2px;">
                <i class="fa fa-filter"></i> Filter
            </button>
        </form>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-label">Saldo Awal</div>
                <div class="stat-value" style="color: #64748b;">Rp <?= number_format($saldo_awal_global) ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Total Pemasukan</div>
                <div class="stat-value text-green">+ <?= number_format($total_masuk) ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Total Pengeluaran</div>
                <div class="stat-value text-red">- <?= number_format($total_keluar) ?></div>
            </div>
            <div class="stat-card primary">
                <div class="stat-label">Saldo Akhir</div>
                <div class="stat-value">Rp <?= number_format($saldo_akhir_global) ?></div>
            </div>
        </div>

        <div class="saldo-breakdown">
            <div class="break-card">
                <div>
                    <div class="break-label">DOMPET (CASH)</div>
                    <div class="break-val">Rp <?= number_format($saldo_akhir_cash) ?></div>
                </div>
                <i class="fa-solid fa-wallet" style="font-size: 24px; color: #f97316; opacity: 0.2;"></i>
            </div>
            <div class="break-card">
                <div>
                    <div class="break-label">REKENING (ATM)</div>
                    <div class="break-val">Rp <?= number_format($saldo_akhir_atm) ?></div>
                </div>
                <i class="fa-solid fa-building-columns" style="font-size: 24px; color: #8b5cf6; opacity: 0.2;"></i>
            </div>
        </div>

        <div class="card-table">
            <div class="table-responsive">
                <table class="table-custom">
                    <thead>
                        <tr>
                            <th width="120">Tanggal</th>
                            <th width="100" class="text-center">Metode</th>
                            <th>Keterangan</th>
                            <th width="100" class="text-center">Tipe</th>
                            <th width="150" class="text-right">Masuk</th>
                            <th width="150" class="text-right">Keluar</th>
                            <th width="150" class="text-right">Saldo</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr style="background:#f8fafc;">
                            <td colspan="6" class="text-right" style="font-weight:600; color:#64748b;">SALDO AWAL</td>
                            <td class="text-right font-mono" style="font-weight:700;">Rp <?= number_format($saldo_awal_global) ?></td>
                        </tr>
                        <?php if(empty($list_data)): ?>
                            <tr><td colspan="7" style="text-align:center; padding:40px; color:#94a3b8;">Tidak ada transaksi.</td></tr>
                        <?php else: ?>
                            <?php foreach($list_data as $d): $is_in = ($d['jenis'] == 'Masuk'); ?>
                            <tr>
                                <td style="color:#64748b; font-weight:500;"><?= date('d M Y', strtotime($d['tanggal'])) ?></td>
                                <td class="text-center">
                                    <span class="badge <?= ($d['metode']=='ATM')?'badge-atm':'badge-cash' ?>"><?= $d['metode'] ?></span>
                                </td>
                                <td style="font-weight:500; color:#334155;"><?= htmlspecialchars($d['keterangan']) ?></td>
                                <td class="text-center">
                                    <span class="badge <?= $is_in?'badge-in':'badge-out' ?>"><?= strtoupper($d['jenis']) ?></span>
                                </td>
                                <td class="text-right font-mono text-green"><?= $is_in ? number_format($d['nominal']) : '-' ?></td>
                                <td class="text-right font-mono text-red"><?= !$is_in ? number_format($d['nominal']) : '-' ?></td>
                                <td class="text-right font-mono text-thp"><?= number_format($d['saldo_row']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="4" class="text-right" style="color:#64748b;">TOTAL MUTASI</td>
                            <td class="text-right text-green">+ <?= number_format($total_masuk) ?></td>
                            <td class="text-right text-red">- <?= number_format($total_keluar) ?></td>
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