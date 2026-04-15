<?php
require_once '../../config/database.php';
date_default_timezone_set('Asia/Jakarta');

if (!isset($_SESSION['status'])) { header("Location: ../../login.php"); exit; }

// --- LOGIKA 1: PROSES BAYAR HUTANG ---
if (isset($_POST['bayar_hutang'])) {
    $conn->begin_transaction();
    try {
        $id_hutang    = $_POST['id_hutang'];
        $tgl_bayar    = $_POST['tanggal_bayar'];
        $nominal      = floatval(str_replace('.', '', $_POST['nominal_bayar']));
        $sumber_dana  = $_POST['sumber_dana'];
        $keterangan   = $_POST['keterangan'];
        $user_id      = $_SESSION['user_id'] ?? 1;

        // 1. Ambil Data Hutang Saat Ini (Lock Row)
        $q_cek = $conn->query("SELECT * FROM hutang_dagang WHERE id = '$id_hutang' FOR UPDATE");
        $d_cek = $q_cek->fetch_assoc();

        if ($nominal <= 0) {
            throw new Exception("Nominal pembayaran harus lebih dari 0!");
        }

        if ($nominal > $d_cek['sisa_hutang']) {
            throw new Exception("Nominal pembayaran melebihi sisa hutang! Sisa: Rp " . number_format($d_cek['sisa_hutang'],0,',','.'));
        }

        // 2. Update Tabel Hutang
        $bayar_baru = $d_cek['total_dibayar'] + $nominal;
        $sisa_baru  = $d_cek['sisa_hutang'] - $nominal;
        $status_baru = ($sisa_baru <= 0) ? 'Lunas' : 'Sebagian';

        $stmt_upd = $conn->prepare("UPDATE hutang_dagang SET total_dibayar = ?, sisa_hutang = ?, status = ? WHERE id = ?");
        $stmt_upd->bind_param("ddsi", $bayar_baru, $sisa_baru, $status_baru, $id_hutang);
        $stmt_upd->execute();

        // 3. Catat Riwayat Pembayaran (Cicilan)
        $ket_hist = "Pembayaran Hutang ($sumber_dana)";
        if(!empty($keterangan)) $ket_hist .= " - " . $keterangan;

        $stmt_hist = $conn->prepare("INSERT INTO riwayat_bayar_hutang (hutang_id, kode_transaksi, nominal_bayar, tanggal_bayar, keterangan) VALUES (?, ?, ?, ?, ?)");
        $stmt_hist->bind_param("isdss", $id_hutang, $d_cek['kode_transaksi'], $nominal, $tgl_bayar, $ket_hist);
        $stmt_hist->execute();

        // 4. Catat Pengeluaran Kas (Uang Keluar)
        $ket_kas = "BAYAR HUTANG " . strtoupper($d_cek['nama_supplier']) . " [Ref: " . $d_cek['kode_transaksi'] . "]";
        
        $stmt_kas = $conn->prepare("INSERT INTO transaksi_kas (user_id, tanggal, jenis, keterangan, nominal, metode) VALUES (?, ?, 'Keluar', ?, ?, ?)");
        $stmt_kas->bind_param("issds", $user_id, $tgl_bayar, $ket_kas, $nominal, $sumber_dana);
        $stmt_kas->execute();

        $conn->commit();
        $_SESSION['success'] = "Pembayaran sebesar Rp " . number_format($nominal,0,',','.') . " berhasil disimpan.";
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error'] = "Gagal: " . $e->getMessage();
    }
    header("Location: hutang_dagang.php"); exit;
}

// --- FILTER DATA ---
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'Belum Lunas';
$where = "WHERE 1=1";
if ($status_filter != 'Semua') {
    if ($status_filter == 'Belum Lunas') {
        $where .= " AND status IN ('Belum Lunas', 'Sebagian')";
    } else {
        $where .= " AND status = '$status_filter'";
    }
}

$q_hutang = $conn->query("SELECT * FROM hutang_dagang $where ORDER BY jatuh_tempo ASC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include '../../layout/header.php'; ?>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    
    <style>
        body { background-color: #f8fafc; font-family: 'Inter', sans-serif; }
        .content-wrapper { padding: 20px 30px; }
        
        .card { background: #fff; border-radius: 12px; border: 1px solid #e2e8f0; box-shadow: 0 2px 4px rgba(0,0,0,0.02); overflow: hidden; margin-bottom: 20px; }
        .card-header { padding: 15px 20px; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center; background: #fff; }
        .card-body { padding: 0; }

        /* TABLE STYLE */
        .table-custom { width: 100%; border-collapse: collapse; }
        .table-custom th { text-align: left; padding: 12px 20px; background: #f8fafc; color: #64748b; font-size: 12px; font-weight: 700; text-transform: uppercase; border-bottom: 1px solid #e2e8f0; }
        .table-custom td { padding: 12px 20px; border-bottom: 1px solid #f1f5f9; color: #334155; font-size: 13px; vertical-align: middle; }
        .table-custom tr:hover { background: #f8fafc; }

        /* BADGES */
        .badge { padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; }
        .badge-lunas { background: #dcfce7; color: #166534; }
        .badge-sebagian { background: #dbeafe; color: #1e40af; }
        .badge-belum { background: #fee2e2; color: #991b1b; }

        /* BUTTONS */
        .btn-pay { background: #2563eb; color: #fff; padding: 6px 12px; border-radius: 6px; text-decoration: none; font-size: 12px; font-weight: 600; border: none; cursor: pointer; display: inline-flex; align-items: center; gap: 5px; }
        .btn-pay:hover { background: #1d4ed8; }
        
        .progress-bar { width: 100%; height: 6px; background: #e2e8f0; border-radius: 3px; overflow: hidden; margin-top: 5px; }
        .progress-fill { height: 100%; background: #10b981; }

        /* MODAL */
        .modal-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.5); z-index: 9999; align-items: center; justify-content: center; backdrop-filter: blur(2px); }
        .modal-box { background: #fff; width: 90%; max-width: 450px; border-radius: 12px; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.1); animation: slideUp 0.3s cubic-bezier(0.16, 1, 0.3, 1); }
        @keyframes slideUp { from { transform: translateY(20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }

        .form-group { margin-bottom: 15px; }
        .form-label { display: block; font-size: 12px; font-weight: 600; color: #64748b; margin-bottom: 5px; }
        .form-input { width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 14px; box-sizing: border-box; outline: none; }
        .form-input:focus { border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37,99,235,0.1); }
    </style>
</head>
<body>
    <?php include '../../layout/sidebar.php'; ?>

    <div class="content-wrapper">
        <div style="margin-bottom: 20px; display:flex; justify-content:space-between; align-items:center;">
            <div>
                <h2 style="margin:0; font-weight:800; color:#1e293b;">Hutang Dagang</h2>
                <p style="margin:5px 0 0; font-size:13px; color:#64748b;">Kelola pembayaran hutang ke supplier.</p>
            </div>
            
            <div style="background:#fff; border:1px solid #cbd5e1; border-radius:8px; overflow:hidden; display:flex;">
                <a href="?status=Belum Lunas" style="padding:8px 15px; font-size:13px; text-decoration:none; font-weight:600; <?= ($status_filter == 'Belum Lunas') ? 'background:#eff6ff; color:#2563eb;' : 'color:#64748b;' ?>">Belum Lunas</a>
                <a href="?status=Semua" style="padding:8px 15px; font-size:13px; text-decoration:none; font-weight:600; border-left:1px solid #cbd5e1; <?= ($status_filter == 'Semua') ? 'background:#eff6ff; color:#2563eb;' : 'color:#64748b;' ?>">Semua Data</a>
            </div>
        </div>

        <div class="card">
            <div style="overflow-x:auto;">
                <table class="table-custom">
                    <thead>
                        <tr>
                            <th>Transaksi</th>
                            <th>Jatuh Tempo</th>
                            <th style="text-align:right;">Total Tagihan</th>
                            <th style="text-align:right;">Sisa Hutang</th>
                            <th style="text-align:center;">Status</th>
                            <th style="text-align:center;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if($q_hutang->num_rows > 0): ?>
                            <?php while($r = $q_hutang->fetch_assoc()): ?>
                                <?php 
                                    $persen = ($r['total_tagihan'] > 0) ? ($r['total_dibayar'] / $r['total_tagihan']) * 100 : 0;
                                    $cls = 'badge-belum';
                                    if($r['status'] == 'Lunas') $cls = 'badge-lunas';
                                    elseif($r['status'] == 'Sebagian') $cls = 'badge-sebagian';
                                    
                                    // Cek Jatuh Tempo
                                    $tgl_tempo = new DateTime($r['jatuh_tempo']);
                                    $tgl_now = new DateTime();
                                    $interval = $tgl_now->diff($tgl_tempo);
                                    $alert_tempo = "";
                                    if($r['status'] != 'Lunas' && $tgl_now > $tgl_tempo) {
                                        $alert_tempo = "<span style='color:#ef4444; font-size:10px; font-weight:700; display:block;'>⚠ Lewat " . $interval->days . " hari</span>";
                                    } elseif ($r['status'] != 'Lunas' && $interval->days <= 3 && $interval->invert == 0) {
                                        $alert_tempo = "<span style='color:#f59e0b; font-size:10px; font-weight:700; display:block;'>⚠ " . $interval->days . " hari lagi</span>";
                                    }
                                ?>
                                <tr>
                                    <td>
                                        <div style="font-weight:700; color:#1e293b;"><?= $r['nama_supplier'] ?></div>
                                        <div style="font-size:11px; color:#64748b; font-family:monospace;"><?= $r['kode_transaksi'] ?> • <?= date('d/m/y', strtotime($r['tanggal_transaksi'])) ?></div>
                                    </td>
                                    <td>
                                        <div style="font-weight:600; color:#334155;"><?= date('d M Y', strtotime($r['jatuh_tempo'])) ?></div>
                                        <?= $alert_tempo ?>
                                    </td>
                                    <td style="text-align:right; font-family:monospace; font-size:14px;">Rp <?= number_format($r['total_tagihan'],0,',','.') ?></td>
                                    <td style="text-align:right;">
                                        <div style="font-family:monospace; font-size:14px; font-weight:700; color:#ef4444;">Rp <?= number_format($r['sisa_hutang'],0,',','.') ?></div>
                                        <div class="progress-bar"><div class="progress-fill" style="width: <?= $persen ?>%"></div></div>
                                        <div style="font-size:10px; color:#64748b; margin-top:2px;">Dibayar: <?= number_format($persen,0) ?>%</div>
                                    </td>
                                    <td style="text-align:center;"><span class="badge <?= $cls ?>"><?= $r['status'] ?></span></td>
                                    <td style="text-align:center;">
                                        <?php if($r['sisa_hutang'] > 0): ?>
                                            <button onclick="openModalBayar('<?= $r['id'] ?>', '<?= $r['kode_transaksi'] ?>', '<?= $r['nama_supplier'] ?>', <?= $r['sisa_hutang'] ?>)" class="btn-pay">
                                                <i class="fa fa-wallet"></i> Bayar
                                            </button>
                                        <?php else: ?>
                                            <span style="color:#10b981; font-size:12px; font-weight:700;"><i class="fa fa-check-circle"></i> Selesai</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="6" style="text-align:center; padding:30px; color:#94a3b8;">Tidak ada data hutang.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div id="modalBayar" class="modal-overlay">
        <div class="modal-box">
            <div style="padding:15px 20px; border-bottom:1px solid #e2e8f0; display:flex; justify-content:space-between; align-items:center;">
                <h3 style="margin:0; font-size:16px; color:#1e293b;">Pembayaran Hutang</h3>
                <span onclick="closeModal()" style="cursor:pointer; font-size:20px; color:#94a3b8;">&times;</span>
            </div>
            <form method="POST" style="padding:20px;">
                <input type="hidden" name="id_hutang" id="idHutang">
                
                <div style="background:#f0f9ff; border:1px solid #bae6fd; padding:10px; border-radius:8px; margin-bottom:15px;">
                    <div style="font-size:11px; color:#64748b;">Supplier / Kode</div>
                    <div style="font-weight:700; color:#0284c7; font-size:13px;" id="infoSupp"></div>
                    <div style="font-size:11px; color:#64748b; margin-top:5px;">Sisa Tagihan</div>
                    <div style="font-weight:800; color:#0284c7; font-size:16px;" id="infoSisa"></div>
                </div>

                <div class="form-group">
                    <label class="form-label">Tanggal Bayar</label>
                    <input type="date" name="tanggal_bayar" class="form-input" value="<?= date('Y-m-d') ?>" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Nominal Bayar (Rp)</label>
                    <input type="text" name="nominal_bayar" id="nominalBayar" class="form-input" placeholder="0" onkeyup="formatRupiah(this)" style="font-weight:700; color:#1e293b;" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Sumber Dana</label>
                    <select name="sumber_dana" class="form-input">
                        <option value="Cash">Kas Tunai (Cash)</option>
                        <option value="ATM">Rekening Bank (ATM)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Keterangan (Opsional)</label>
                    <input type="text" name="keterangan" class="form-input" placeholder="Cth: Cicilan ke-1, Pelunasan, dll">
                </div>

                <button type="submit" name="bayar_hutang" class="btn-pay" style="width:100%; justify-content:center; padding:12px; font-size:14px;">
                    SIMPAN PEMBAYARAN
                </button>
            </form>
        </div>
    </div>

    <?php include '../../layout/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function openModalBayar(id, kode, supplier, sisa) {
            document.getElementById('idHutang').value = id;
            document.getElementById('infoSupp').innerText = supplier + ' (' + kode + ')';
            document.getElementById('infoSisa').innerText = 'Rp ' + new Intl.NumberFormat('id-ID').format(sisa);
            document.getElementById('nominalBayar').value = ''; // Reset input
            document.getElementById('modalBayar').style.display = 'flex';
        }

        function closeModal() {
            document.getElementById('modalBayar').style.display = 'none';
        }

        function formatRupiah(el) {
            let val = el.value.replace(/[^0-9]/g, '');
            el.value = new Intl.NumberFormat('id-ID').format(val);
        }

        <?php if(isset($_SESSION['success'])): ?>
            Swal.fire({icon:'success', title:'Berhasil', text:'<?= $_SESSION['success'] ?>', timer:2000, showConfirmButton:false});
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>
        <?php if(isset($_SESSION['error'])): ?>
            Swal.fire({icon:'error', title:'Gagal', text:'<?= $_SESSION['error'] ?>'});
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
    </script>
</body>
</html>