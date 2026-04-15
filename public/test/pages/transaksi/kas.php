<?php 
require_once '../../config/database.php';

// 1. SET TIMEZONE
date_default_timezone_set('Asia/Jakarta');
cek_login(); 

$today = date('Y-m-d');
$tgl_filter = isset($_GET['tgl']) ? $_GET['tgl'] : $today;
$is_locked = false; // Kunci dimatikan sesuai permintaan
$edit_mode = false;
$data_edit = null;

// --- 1. PROSES HAPUS ---
if(isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    // Hapus data (Validasi masa lalu dimatikan)
    $del = mysqli_query($conn, "DELETE FROM transaksi_kas WHERE id='$id'");
    if($del) {
        echo "<script>
            setTimeout(function() {
                Swal.fire({title: 'Terhapus!', text: 'Data berhasil dihapus.', icon: 'success', timer: 1000, showConfirmButton: false})
                .then(() => { window.location='kas.php?tgl=$tgl_filter'; });
            }, 100);
        </script>";
    }
}

// --- 2. PROSES SIMPAN (INPUT BIASA & MUTASI) ---
if(isset($_POST['simpan_transaksi'])) {
    $uid = $_SESSION['user_id'];
    $tgl = $_POST['tanggal'];
    $mode_input = $_POST['mode_input']; // 'transaksi' atau 'mutasi'
    $ket = mysqli_real_escape_string($conn, strtoupper($_POST['keterangan']));
    $nom = str_replace('.', '', $_POST['nominal']);

    if($nom > 0) {
        if ($mode_input == 'transaksi') {
            // --- LOGIKA TRANSAKSI BIASA (MASUK/KELUAR) ---
            $jenis = $_POST['jenis'];
            $metode = $_POST['metode'];
            
            $sql = "INSERT INTO transaksi_kas (user_id, tanggal, jenis, metode, keterangan, nominal) 
                    VALUES ('$uid', '$tgl', '$jenis', '$metode', '$ket', '$nom')";
            mysqli_query($conn, $sql);
            $msg = "Transaksi berhasil disimpan!";

        } elseif ($mode_input == 'mutasi') {
            // --- LOGIKA MUTASI (PINDAH DANA) ---
            $arah_mutasi = $_POST['arah_mutasi']; // 'atm_to_cash' atau 'cash_to_atm'
            
            if ($arah_mutasi == 'atm_to_cash') {
                // 1. ATM KELUAR
                $sql1 = "INSERT INTO transaksi_kas (user_id, tanggal, jenis, metode, keterangan, nominal) 
                         VALUES ('$uid', '$tgl', 'Keluar', 'ATM', 'TARIK TUNAI (Ke Cash)', '$nom')";
                // 2. CASH MASUK
                $sql2 = "INSERT INTO transaksi_kas (user_id, tanggal, jenis, metode, keterangan, nominal) 
                         VALUES ('$uid', '$tgl', 'Masuk', 'Cash', 'TERIMA DARI ATM', '$nom')";
                $ket_msg = "Tarik Tunai Berhasil";
            } else {
                // 1. CASH KELUAR
                $sql1 = "INSERT INTO transaksi_kas (user_id, tanggal, jenis, metode, keterangan, nominal) 
                         VALUES ('$uid', '$tgl', 'Keluar', 'Cash', 'SETOR TUNAI (Ke ATM)', '$nom')";
                // 2. ATM MASUK
                $sql2 = "INSERT INTO transaksi_kas (user_id, tanggal, jenis, metode, keterangan, nominal) 
                         VALUES ('$uid', '$tgl', 'Masuk', 'ATM', 'MASUK DARI CASH', '$nom')";
                $ket_msg = "Setor Tunai Berhasil";
            }
            
            mysqli_query($conn, $sql1);
            mysqli_query($conn, $sql2);
            $msg = "$ket_msg (Saldo dipindahkan)";
        }

        echo "<script>
            setTimeout(function() {
                Swal.fire({title: 'Berhasil!', text: '$msg', icon: 'success', timer: 1500, showConfirmButton: false})
                .then(() => { window.location='kas.php?tgl=$tgl'; });
            }, 100);
        </script>";
    }
}

// --- LOGIK PERHITUNGAN SALDO ---
// 1. Saldo Awal Global
$q_awal = mysqli_query($conn, "SELECT 
    SUM(CASE WHEN jenis='Masuk' THEN nominal ELSE 0 END) as tot_masuk,
    SUM(CASE WHEN jenis='Keluar' THEN nominal ELSE 0 END) as tot_keluar
    FROM transaksi_kas WHERE tanggal < '$tgl_filter'");
$d_awal = mysqli_fetch_assoc($q_awal);
$saldo_awal = $d_awal['tot_masuk'] - $d_awal['tot_keluar'];

// 2. Transaksi Hari Ini Global
$q_today = mysqli_query($conn, "SELECT 
    SUM(CASE WHEN jenis='Masuk' THEN nominal ELSE 0 END) as tot_masuk,
    SUM(CASE WHEN jenis='Keluar' THEN nominal ELSE 0 END) as tot_keluar
    FROM transaksi_kas WHERE tanggal = '$tgl_filter'");
$d_today = mysqli_fetch_assoc($q_today);
$masuk_hari_ini = $d_today['tot_masuk'];
$keluar_hari_ini = $d_today['tot_keluar'];

// 3. Saldo Akhir Global
$saldo_akhir = $saldo_awal + $masuk_hari_ini - $keluar_hari_ini;

// 4. Rincian Saldo (Cash vs ATM) s/d Hari Ini
$q_rincian = mysqli_query($conn, "SELECT 
    (SUM(CASE WHEN jenis='Masuk' AND metode='Cash' THEN nominal ELSE 0 END) - 
     SUM(CASE WHEN jenis='Keluar' AND metode='Cash' THEN nominal ELSE 0 END)) as sisa_cash,
    (SUM(CASE WHEN jenis='Masuk' AND metode='ATM' THEN nominal ELSE 0 END) - 
     SUM(CASE WHEN jenis='Keluar' AND metode='ATM' THEN nominal ELSE 0 END)) as sisa_atm
    FROM transaksi_kas WHERE tanggal <= '$tgl_filter'");
$d_rincian = mysqli_fetch_assoc($q_rincian);
$sisa_cash = $d_rincian['sisa_cash'] ?? 0;
$sisa_atm = $d_rincian['sisa_atm'] ?? 0;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <?php include '../../layout/header.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://fonts.googleapis.com/css2?family=Courier+Prime:wght@400;700&family=Inter:wght@400;500;600;800&display=swap" rel="stylesheet">

    <style>
        :root { 
            --accent-green: #10b981; --accent-red: #ef4444; 
            --accent-blue: #3b82f6; --accent-orange: #f97316; 
            --accent-purple: #8b5cf6; --accent-dark: #1e293b;
        }
        body { background-color: #f3f4f6; font-family: 'Inter', sans-serif; color:#1f2937; }
        .content-wrapper { padding: 30px; }

        /* HEADER */
        .header-wrapper { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 30px; }
        .page-title h3 { margin: 0; font-weight: 800; color: #111827; font-size: 24px; }
        .page-title p { margin: 5px 0 0; color: #6b7280; font-size: 14px; }
        
        .live-clock-widget { text-align: right; }
        .live-clock-time { font-size: 28px; font-weight: 800; color: var(--accent-blue); letter-spacing: -1px; line-height: 1; font-family: 'Courier Prime', monospace; }
        .live-clock-date { font-size: 12px; color: #64748b; font-weight: 600; margin-top: 5px; text-transform: uppercase; letter-spacing: 1px; }
        .date-filter-box { background: #fff; padding: 5px 15px; border-radius: 8px; box-shadow: 0 1px 2px rgba(0,0,0,0.05); display: inline-flex; align-items: center; border: 1px solid #e5e7eb; margin-top: 10px; }
        .date-filter-box input { border: none; background: transparent; font-weight: 700; color: var(--accent-blue); font-size: 14px; outline: none; cursor: pointer; }

        /* STATS GRID */
        .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 25px; }
        .stat-card { background: #fff; border-radius: 16px; padding: 20px; position: relative; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); border: 1px solid #f3f4f6; }
        .stat-card.dark { background: linear-gradient(145deg, #1e293b, #0f172a); color: white; }
        .stat-card.green { border-left: 4px solid var(--accent-green); }
        .stat-card.red { border-left: 4px solid var(--accent-red); }
        .stat-card.blue { background: linear-gradient(145deg, #3b82f6, #2563eb); color: white; }
        .stat-label { font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600; opacity: 0.9; margin-bottom: 5px; }
        .stat-value { font-size: 22px; font-weight: 700; font-family: 'Courier Prime', monospace; margin: 0; }
        .stat-icon { position: absolute; right: 15px; top: 50%; transform: translateY(-50%); font-size: 32px; opacity: 0.15; }

        /* ASSET GRID */
        .asset-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 25px; }
        .asset-card { padding: 15px 20px; border-radius: 12px; display: flex; justify-content: space-between; align-items: center; color: white; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .asset-card.cash { background: linear-gradient(135deg, #f97316, #ea580c); }
        .asset-card.atm { background: linear-gradient(135deg, #8b5cf6, #7c3aed); }
        .asset-info h5 { margin: 0; font-size: 11px; text-transform: uppercase; font-weight: 700; opacity: 0.9; }
        .asset-info h3 { margin: 5px 0 0; font-size: 20px; font-family: 'Courier Prime', monospace; }

        /* CARD & FORM */
        .modern-card { background: #fff; border-radius: 16px; border: 1px solid #e5e7eb; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); overflow: hidden; margin-bottom: 20px; }
        .card-header { padding: 15px 20px; border-bottom: 1px solid #f3f4f6; font-weight: 700; color: #374151; display: flex; align-items: center; justify-content: space-between; background: #f9fafb; }
        .card-body { padding: 20px; }

        .form-group label { font-size: 12px; font-weight: 700; color: #6b7280; text-transform: uppercase; display: block; margin-bottom: 5px; }
        .form-input { width: 100%; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px; font-weight: 500; outline: none; transition: 0.2s; }
        .form-input:focus { border-color: var(--accent-blue); box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1); }
        .form-input-money { font-family: 'Courier Prime', monospace; font-weight: 700; font-size: 16px; color: #111827; }

        /* TABS UNTUK INPUT */
        .input-tabs { display: flex; gap: 10px; margin-bottom: 15px; border-bottom: 1px solid #e5e7eb; padding-bottom: 10px; }
        .tab-btn { padding: 8px 15px; font-size: 13px; font-weight: 600; border-radius: 6px; cursor: pointer; border: 1px solid transparent; background: transparent; color: #6b7280; }
        .tab-btn.active { background: #eff6ff; color: var(--accent-blue); border-color: #bfdbfe; }
        .tab-content { display: none; }
        .tab-content.active { display: block; }

        /* RADIO BUTTONS */
        .radio-wrapper { display: flex; gap: 10px; }
        .radio-label { flex: 1; position: relative; cursor: pointer; }
        .radio-label input { position: absolute; opacity: 0; }
        .radio-box { display: flex; align-items: center; justify-content: center; padding: 10px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 13px; font-weight: 600; color: #6b7280; transition: 0.2s; gap: 5px; }
        .radio-label input:checked + .radio-box.in { background: #ecfdf5; border-color: var(--accent-green); color: var(--accent-green); }
        .radio-label input:checked + .radio-box.out { background: #fef2f2; border-color: var(--accent-red); color: var(--accent-red); }
        .radio-label input:checked + .radio-box.cash { background: #fff7ed; border-color: var(--accent-orange); color: var(--accent-orange); }
        .radio-label input:checked + .radio-box.atm { background: #f5f3ff; border-color: var(--accent-purple); color: var(--accent-purple); }
        /* Radio Mutasi */
        .radio-label input:checked + .radio-box.wd { background: #eff6ff; border-color: var(--accent-blue); color: var(--accent-blue); } /* Tarik Tunai */
        .radio-label input:checked + .radio-box.dp { background: #f0fdf4; border-color: var(--accent-green); color: var(--accent-green); } /* Setor Tunai */

        /* TABLE */
        .table-custom { width: 100%; border-collapse: collapse; }
        .table-custom th { background: #f9fafb; text-align: left; padding: 15px; font-size: 11px; text-transform: uppercase; color: #6b7280; border-bottom: 1px solid #e5e7eb; }
        .table-custom td { padding: 15px; border-bottom: 1px solid #f3f4f6; font-size: 13px; color: #374151; vertical-align: middle; }
        .text-right { text-align: right !important; }
        .text-center { text-align: center !important; }
        
        .badge { padding: 3px 8px; border-radius: 4px; font-size: 10px; font-weight: 700; text-transform: uppercase; border: 1px solid transparent; }
        .badge.cash { background: #fff7ed; color: #9a3412; border-color: #ffedd5; }
        .badge.atm { background: #f5f3ff; color: #5b21b6; border-color: #ede9fe; }
        .font-mono { font-family: 'Courier Prime', monospace; font-weight: 600; }
        .text-green { color: var(--accent-green); }
        .text-red { color: var(--accent-red); }

        @media (max-width: 992px) { .stats-grid { grid-template-columns: 1fr 1fr; } }
        @media (max-width: 768px) { .stats-grid, .asset-grid { grid-template-columns: 1fr; } .header-wrapper { flex-direction: column; align-items: flex-start; } .live-clock-widget { text-align: left; } }
    </style>
</head>
<body>
    <?php include '../../layout/sidebar.php'; ?>
    
    <div class="content-wrapper">
        
        <div class="header-wrapper">
            <div class="page-title">
                <h3>Buku Kas Harian</h3>
                <p><span class="badge" style="background:#dcfce7; color:#166534;"><i class="fa fa-check-circle"></i> AKTIF</span> &nbsp; Kelola arus kas masuk, keluar, dan mutasi.</p>
            </div>
            <div class="live-clock-widget">
                <div id="live-clock" class="live-clock-time"><?php echo date('H:i:s'); ?></div>
                <div id="live-date" class="live-clock-date"><?php echo date('l, d F Y'); ?></div>
                <form method="GET" style="display:inline-block;">
                    <div class="date-filter-box">
                        <span style="font-size:11px; color:#9ca3af; margin-right:5px; font-weight:700;">TANGGAL:</span>
                        <input type="date" name="tgl" value="<?php echo $tgl_filter; ?>" onchange="this.form.submit()">
                    </div>
                </form>
            </div>
        </div>

        <div class="stats-grid">
            <div class="stat-card dark">
                <div class="stat-label">Saldo Awal Hari Ini</div>
                <div class="stat-value">Rp <?php echo number_format($saldo_awal); ?></div>
                <i class="fa fa-history stat-icon"></i>
            </div>
            <div class="stat-card green">
                <div class="stat-label" style="color:var(--accent-green)">Pemasukan Global</div>
                <div class="stat-value" style="color:var(--accent-green)">+ <?php echo number_format($masuk_hari_ini); ?></div>
                <i class="fa fa-arrow-down stat-icon"></i>
            </div>
            <div class="stat-card red">
                <div class="stat-label" style="color:var(--accent-red)">Pengeluaran Global</div>
                <div class="stat-value" style="color:var(--accent-red)">- <?php echo number_format($keluar_hari_ini); ?></div>
                <i class="fa fa-arrow-up stat-icon"></i>
            </div>
            <div class="stat-card blue">
                <div class="stat-label" style="color: white;">Total Saldo Akhir</div>
                <div class="stat-value" style="color: white;">Rp <?php echo number_format($saldo_akhir); ?></div>
                <i class="fa fa-wallet stat-icon" style="color:white; opacity:0.2;"></i>
            </div>
        </div>

        <div class="asset-grid">
            <div class="asset-card cash">
                <div class="asset-info">
                    <h5>DOMPET TUNAI (CASH)</h5>
                    <h3>Rp <?php echo number_format($sisa_cash); ?></h3>
                </div>
                <i class="fa fa-money" style="font-size:35px; opacity:0.3;"></i>
            </div>
            <div class="asset-card atm">
                <div class="asset-info">
                    <h5>REKENING BANK (ATM)</h5>
                    <h3>Rp <?php echo number_format($sisa_atm); ?></h3>
                </div>
                <i class="fa fa-credit-card" style="font-size:35px; opacity:0.3;"></i>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <div class="modern-card">
                    <div class="card-header">
                        <span><i class="fa fa-plus-circle"></i> &nbsp; Input Transaksi</span>
                    </div>
                    <div class="card-body">
                        <div class="input-tabs">
                            <button type="button" class="tab-btn active" onclick="switchTab('biasa')">Transaksi Biasa</button>
                            <button type="button" class="tab-btn" onclick="switchTab('mutasi')">Pindah Dana</button>
                        </div>

                        <form method="POST">
                            <input type="hidden" name="id_transaksi" value="">
                            
                            <div class="form-group" style="margin-bottom:15px;">
                                <label>Tanggal</label>
                                <input type="date" name="tanggal" class="form-input" style="background:#f9fafb;" value="<?php echo $tgl_filter; ?>" readonly>
                            </div>

                            <div id="tab-biasa" class="tab-content active">
                                <input type="hidden" name="mode_input" id="mode_input_biasa" value="transaksi">
                                
                                <div class="form-group" style="margin-bottom:15px;">
                                    <label>Arus Dana</label>
                                    <div class="radio-wrapper">
                                        <label class="radio-label">
                                            <input type="radio" name="jenis" value="Masuk" checked>
                                            <div class="radio-box in"><i class="fa fa-arrow-down"></i> Masuk</div>
                                        </label>
                                        <label class="radio-label">
                                            <input type="radio" name="jenis" value="Keluar">
                                            <div class="radio-box out"><i class="fa fa-arrow-up"></i> Keluar</div>
                                        </label>
                                    </div>
                                </div>

                                <div class="form-group" style="margin-bottom:15px;">
                                    <label>Sumber Dana</label>
                                    <div class="radio-wrapper">
                                        <label class="radio-label">
                                            <input type="radio" name="metode" value="Cash" checked>
                                            <div class="radio-box cash"><i class="fa fa-money"></i> Cash</div>
                                        </label>
                                        <label class="radio-label">
                                            <input type="radio" name="metode" value="ATM">
                                            <div class="radio-box atm"><i class="fa fa-credit-card"></i> ATM</div>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div id="tab-mutasi" class="tab-content">
                                <input type="hidden" name="mode_input" id="mode_input_mutasi" value="mutasi" disabled>
                                
                                <div class="form-group" style="margin-bottom:15px;">
                                    <label>Jenis Perpindahan</label>
                                    <div class="radio-wrapper" style="flex-direction:column; gap:8px;">
                                        <label class="radio-label">
                                            <input type="radio" name="arah_mutasi" value="atm_to_cash">
                                            <div class="radio-box wd" style="justify-content:space-between; padding:12px;">
                                                <span><i class="fa fa-credit-card"></i> ATM</span>
                                                <i class="fa fa-arrow-right text-muted"></i>
                                                <span><i class="fa fa-money"></i> CASH</span>
                                                <span style="font-size:10px; background:#eff6ff; padding:2px 6px; border-radius:4px; border:1px solid #bfdbfe; color:#2563eb;">TARIK TUNAI</span>
                                            </div>
                                        </label>
                                        <label class="radio-label">
                                            <input type="radio" name="arah_mutasi" value="cash_to_atm">
                                            <div class="radio-box dp" style="justify-content:space-between; padding:12px;">
                                                <span><i class="fa fa-money"></i> CASH</span>
                                                <i class="fa fa-arrow-right text-muted"></i>
                                                <span><i class="fa fa-credit-card"></i> ATM</span>
                                                <span style="font-size:10px; background:#f0fdf4; padding:2px 6px; border-radius:4px; border:1px solid #bbf7d0; color:#16a34a;">SETOR TUNAI</span>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group" style="margin-bottom:15px;">
                                <label>Nominal (Rp)</label>
                                <input type="text" name="nominal" id="nominal_input" class="form-input form-input-money" placeholder="0" required autocomplete="off">
                            </div>

                            <div class="form-group" style="margin-bottom:25px;">
                                <label>Keterangan</label>
                                <textarea name="keterangan" class="form-input" rows="3" placeholder="Keterangan transaksi..." required></textarea>
                            </div>

                            <button type="submit" name="simpan_transaksi" class="btn btn-primary" style="width:100%; padding:12px; border-radius:8px; font-weight:700; border:none; background:var(--accent-blue); color:white;">
                                <i class="fa fa-save"></i> SIMPAN TRANSAKSI
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="modern-card">
                    <div class="card-header">
                        <span><i class="fa fa-list-alt text-muted"></i> Mutasi: <?php echo date('d F Y', strtotime($tgl_filter)); ?></span>
                    </div>
                    <div class="card-body p-0" style="padding:0;">
                        <div class="table-responsive">
                            <table class="table-custom">
                                <thead>
                                    <tr>
                                        <th>Keterangan</th>
                                        <th class="text-center" width="10%">Metode</th>
                                        <th class="text-right" width="20%">Nominal</th>
                                        <th class="text-right" width="20%">Saldo Global</th>
                                        <th class="text-center" width="10%">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr style="background:#fdfdfe;">
                                        <td style="border-left: 3px solid #1e293b;"><b><i class="fa fa-circle" style="font-size:8px; vertical-align:middle;"></i> SALDO AWAL</b></td>
                                        <td class="text-center"><span class="badge sys" style="background:#f3f4f6; color:#6b7280;">SYSTEM</span></td>
                                        <td class="text-right text-muted font-mono">-</td>
                                        <td class="text-right font-mono"><b><?php echo number_format($saldo_awal); ?></b></td>
                                        <td></td>
                                    </tr>

                                    <?php
                                    $q_kas = mysqli_query($conn, "SELECT * FROM transaksi_kas WHERE tanggal = '$tgl_filter' ORDER BY created_at ASC");
                                    $running_balance = $saldo_awal;
                                    
                                    if(mysqli_num_rows($q_kas) == 0) {
                                        echo "<tr><td colspan='5' class='text-center text-muted' style='padding:30px;'>Belum ada transaksi hari ini.</td></tr>";
                                    }

                                    while($d = mysqli_fetch_assoc($q_kas)) {
                                        $badge_metode = ($d['metode'] == 'ATM') ? 'badge atm' : 'badge cash';
                                        
                                        if($d['jenis'] == 'Masuk') {
                                            $nom_disp = "+ ".number_format($d['nominal']);
                                            $cls_nom = "text-green";
                                            $running_balance += $d['nominal'];
                                        } else {
                                            $nom_disp = "- ".number_format($d['nominal']);
                                            $cls_nom = "text-red";
                                            $running_balance -= $d['nominal'];
                                        }
                                    ?>
                                    <tr>
                                        <td>
                                            <span style="font-weight:600; display:block;"><?php echo $d['keterangan']; ?></span>
                                        </td>
                                        <td class="text-center">
                                            <span class="<?php echo $badge_metode; ?>"><?php echo $d['metode']; ?></span>
                                        </td>
                                        <td class="text-right font-mono <?php echo $cls_nom; ?>">
                                            <?php echo $nom_disp; ?>
                                        </td>
                                        <td class="text-right font-mono" style="color:var(--accent-blue);">
                                            <?php echo number_format($running_balance); ?>
                                        </td>
                                        <td class="text-center">
                                            <button onclick="confirmDelete(<?php echo $d['id']; ?>, '<?php echo $tgl_filter; ?>')" class="btn btn-xs" style="background:none; border:none; color:#ef4444; cursor:pointer;" title="Hapus"><i class="fa fa-trash"></i></button>
                                        </td>
                                    </tr>
                                    <?php } ?>
                                </tbody>
                                <tfoot style="background:#f9fafb; border-top:2px solid #e5e7eb;">
                                    <tr>
                                        <td colspan="3" class="text-right" style="font-weight:700; color:#64748b;">TOTAL SALDO (CASH + ATM)</td>
                                        <td class="text-right font-mono" style="font-size:16px; font-weight:800; color:#1e293b;">
                                            Rp <?php echo number_format($saldo_akhir); ?>
                                        </td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php include '../../layout/footer.php'; ?>
    
    <script>
    // 1. Script Jam Realtime
    function updateClock() {
        const now = new Date();
        document.getElementById('live-clock').textContent = now.toLocaleTimeString('id-ID', { hour12: false });
        document.getElementById('live-date').textContent = now.toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
    }
    setInterval(updateClock, 1000);
    updateClock();

    // 2. Format Rupiah Input
    const nominalInput = document.getElementById('nominal_input');
    if(nominalInput){
        nominalInput.addEventListener('keyup', function(e) {
            let value = this.value.replace(/[^,\d]/g, '').toString();
            let split = value.split(',');
            let sisa = split[0].length % 3;
            let rupiah = split[0].substr(0, sisa);
            let ribuan = split[0].substr(sisa).match(/\d{3}/gi);
            if (ribuan) {
                let separator = sisa ? '.' : '';
                rupiah += separator + ribuan.join('.');
            }
            this.value = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
        });
    }

    // 3. Tab Switcher
    function switchTab(tabName) {
        document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
        document.querySelectorAll('.tab-btn').forEach(el => el.classList.remove('active'));
        
        document.getElementById('tab-' + tabName).classList.add('active');
        // Update styling tombol
        const btns = document.querySelectorAll('.tab-btn');
        if(tabName === 'biasa') {
            btns[0].classList.add('active');
            document.getElementById('mode_input_biasa').disabled = false;
            document.getElementById('mode_input_mutasi').disabled = true;
        } else {
            btns[1].classList.add('active');
            document.getElementById('mode_input_biasa').disabled = true;
            document.getElementById('mode_input_mutasi').disabled = false;
        }
    }

    // 4. Konfirmasi Hapus
    function confirmDelete(id, tgl) {
        Swal.fire({
            title: 'Hapus data?',
            text: "Saldo akan dikalkulasi ulang.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#cbd5e1',
            confirmButtonText: 'Ya, Hapus'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = `kas.php?hapus=${id}&tgl=${tgl}`;
            }
        })
    }
    </script>
</body>
</html>