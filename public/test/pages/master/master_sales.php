<?php
require_once '../../config/database.php';
if (!isset($_SESSION['status'])) { header("Location: ../../login.php"); exit; }

// --- LOGIKA: SIMPAN / UPDATE SALES ---
if (isset($_POST['simpan_sales'])) {
    $nama = trim($_POST['nama_sales']);
    $hp   = trim($_POST['no_hp']);
    $id   = $_POST['id_sales'] ?? '';

    if (empty($id)) {
        $stmt = $conn->prepare("INSERT INTO sales (nama_sales, no_hp) VALUES (?, ?)");
        $stmt->bind_param("ss", $nama, $hp);
    } else {
        $stmt = $conn->prepare("UPDATE sales SET nama_sales=?, no_hp=? WHERE id=?");
        $stmt->bind_param("ssi", $nama, $hp, $id);
    }
    if ($stmt->execute()) echo "<script>window.location='master_sales.php';</script>";
}

// --- LOGIKA: HAPUS SALES ---
if (isset($_GET['hapus'])) {
    $id = intval($_GET['hapus']);
    $conn->query("DELETE FROM sales WHERE id=$id");
    echo "<script>window.location='master_sales.php';</script>";
}

// --- AJAX: SIMPAN HARGA KHUSUS ---
if (isset($_POST['ajax_act']) && $_POST['ajax_act'] == 'update_price') {
    $sid = intval($_POST['sales_id']);
    $bid = intval($_POST['barang_id']);
    $val = floatval($_POST['harga']);

    $cek = $conn->query("SELECT id FROM barang_harga_sales WHERE sales_id=$sid AND barang_id=$bid");
    
    if ($cek->num_rows > 0) {
        if ($val > 0) {
            $conn->query("UPDATE barang_harga_sales SET harga_khusus=$val WHERE sales_id=$sid AND barang_id=$bid");
        } else {
            $conn->query("DELETE FROM barang_harga_sales WHERE sales_id=$sid AND barang_id=$bid");
        }
    } else {
        if ($val > 0) {
            $conn->query("INSERT INTO barang_harga_sales (sales_id, barang_id, harga_khusus) VALUES ($sid, $bid, $val)");
        }
    }
    echo "OK"; exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <?php include '../../layout/header.php'; ?>
    <style>
        /* BASE STYLE (Sama dengan Stok Barang) */
        body { background-color: #f1f5f9; font-family: 'Inter', sans-serif; }
        
        .content-wrapper { padding: 30px !important; }

        /* Card Styles */
        .card-custom { background: #fff; border-radius: 12px; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); margin-bottom: 25px; }
        
        /* Table Styles */
        .table-custom { width: 100%; border-collapse: collapse; }
        .table-custom th { text-align: left; padding: 12px 15px; background: #f8fafc; color: #64748b; font-size: 11px; text-transform: uppercase; border-bottom: 1px solid #e2e8f0; font-weight: 700; }
        .table-custom td { padding: 10px 15px; border-bottom: 1px solid #f1f5f9; font-size: 13px; color: #334155; vertical-align: middle; }
        .table-custom tr:hover td { background: #f8fafc; }
        
        /* Badges */
        .badge { padding: 4px 10px; border-radius: 6px; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; }
        .st-ok { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
        .st-low { background: #fef3c7; color: #92400e; border: 1px solid #fde68a; } 
        
        /* Buttons */
        .btn-act { border: none; padding: 8px 12px; border-radius: 6px; font-size: 13px; font-weight: 600; cursor: pointer; transition: 0.2s; display: inline-flex; align-items: center; gap: 5px; }
        .btn-blue { background: #2563eb; color: white; } .btn-blue:hover { background: #1d4ed8; }
        .btn-green { background: #10b981; color: white; } .btn-green:hover { background: #059669; }
        .btn-icon { width: 26px; height: 26px; border-radius: 6px; display: inline-flex; justify-content: center; align-items: center; cursor: pointer; color: white; border: none; margin-right: 3px; }
        .bi-edit { background: #3b82f6; } .bi-edit:hover { background: #2563eb; }
        .bi-del { background: #ef4444; } .bi-del:hover { background: #dc2626; }
        .bi-tag { background: #10b981; } .bi-tag:hover { background: #059669; }

        /* Modal Styles */
        .modal-overlay { 
            display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; 
            background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center; backdrop-filter: blur(2px);
        }
        .modal-box { 
            background: white; width: 90%; max-width: 500px; padding: 25px; border-radius: 12px; 
            box-shadow: 0 10px 25px rgba(0,0,0,0.3); max-height: 90vh; overflow-y: auto; 
            animation: slideDown 0.3s; position: relative;
        }
        .modal-lg { max-width: 800px; }
        @keyframes slideDown { from {transform: translateY(-30px); opacity: 0;} to {transform: translateY(0); opacity: 1;} }

        /* Forms */
        .form-group { margin-bottom: 12px; }
        .form-label { display: block; font-size: 11px; font-weight: 700; color: #64748b; margin-bottom: 5px; }
        .form-control { width: 100%; padding: 9px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px; box-sizing: border-box; }
        .form-control:focus { border-color: #3b82f6; outline: none; box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1); }
        .inp-price { font-family: monospace; text-align: right; color: #2563eb; font-weight: 700; }

        /* Toast */
        .toast { position: fixed; bottom: 30px; right: 30px; background: #1e293b; color: #fff; padding: 12px 20px; border-radius: 8px; font-size: 13px; font-weight: 600; display: none; align-items: center; gap: 10px; z-index: 100000; animation: fadeUp 0.3s; }
        @keyframes fadeUp { from {transform: translateY(20px); opacity: 0;} to {transform: translateY(0); opacity: 1;} }
    </style>
</head>
<body>
    <?php include '../../layout/sidebar.php'; ?>
    
    <div class="content-wrapper">
        
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
            <div>
                <h2 style="font-weight:800; color:#1e293b; margin:0; font-size: 22px;">Manajemen Sales</h2>
                <p style="color:#64748b; font-size:13px; margin-top:2px;">Kelola tim sales dan konfigurasi harga khusus.</p>
            </div>
            <button onclick="openForm()" class="btn-act btn-blue">
                <i class="fa fa-plus"></i> Tambah Sales
            </button>
        </div>

        <div class="card-custom">
            <div style="overflow-x: auto;">
                <table class="table-custom">
                    <thead>
                        <tr>
                            <th width="50">No</th>
                            <th>Nama Sales</th>
                            <th>Kontak</th>
                            <th>Status Harga</th>
                            <th style="text-align: center;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $no = 1;
                        $q = $conn->query("SELECT * FROM sales ORDER BY nama_sales ASC");
                        while($r = $q->fetch_assoc()):
                            $cx = $conn->query("SELECT COUNT(*) as c FROM barang_harga_sales WHERE sales_id='{$r['id']}'")->fetch_assoc();
                        ?>
                        <tr>
                            <td style="color:#64748b;"><?= $no++ ?></td>
                            <td style="font-weight: 700;"><?= htmlspecialchars($r['nama_sales']) ?></td>
                            <td>
                                <?php if($r['no_hp']): ?>
                                    <span style="font-family: monospace; font-size: 12px; color: #64748b;"><i class="fa fa-phone"></i> <?= htmlspecialchars($r['no_hp']) ?></span>
                                <?php else: ?>
                                    <span style="color:#cbd5e1;">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if($cx['c'] > 0): ?>
                                    <span class="badge st-ok"><i class="fa fa-check"></i> <?= $cx['c'] ?> Custom Prices</span>
                                <?php else: ?>
                                    <span class="badge st-low">Harga Default</span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: center;">
                                <div style="display:inline-flex; gap:5px;">
                                    <button onclick="editSales(<?= htmlspecialchars(json_encode($r)) ?>)" class="btn-icon bi-edit" title="Edit Profil"><i class="fa fa-pen" style="font-size: 10px;"></i></button>
                                    <button onclick="openPriceConfig(<?= $r['id'] ?>, '<?= $r['nama_sales'] ?>')" class="btn-icon bi-tag" title="Atur Harga"><i class="fa fa-tags" style="font-size: 10px;"></i></button>
                                    <a href="?hapus=<?= $r['id'] ?>" onclick="return confirm('Yakin ingin menghapus sales ini?')" class="btn-icon bi-del" title="Hapus"><i class="fa fa-trash" style="font-size: 10px;"></i></a>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div id="modalForm" class="modal-overlay">
        <div class="modal-box">
            <div style="display:flex; justify-content:space-between; margin-bottom:20px; border-bottom:1px solid #e2e8f0; padding-bottom:10px;">
                <h3 style="margin:0; color:#1e293b;">Data Sales</h3>
                <span onclick="closeModal('modalForm')" style="cursor:pointer; font-size:20px; color:#94a3b8;">&times;</span>
            </div>
            <form method="POST">
                <input type="hidden" name="id_sales" id="id_sales">
                <div class="form-group">
                    <label class="form-label">Nama Lengkap</label>
                    <input type="text" name="nama_sales" id="nama_sales" class="form-control" placeholder="Contoh: Andi Saputra" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Nomor WhatsApp / HP</label>
                    <input type="text" name="no_hp" id="no_hp" class="form-control" placeholder="Contoh: 0812xxxx">
                </div>
                <div style="text-align: right; margin-top: 20px; border-top: 1px solid #f1f5f9; padding-top: 15px;">
                    <button type="button" onclick="closeModal('modalForm')" class="btn-act" style="background:#f1f5f9; color:#64748b; margin-right:8px;">Batal</button>
                    <button type="submit" name="simpan_sales" class="btn-act btn-blue"><i class="fa fa-save"></i> Simpan Data</button>
                </div>
            </form>
        </div>
    </div>

    <div id="modalConfig" class="modal-overlay">
        <div class="modal-box modal-lg">
            <div style="display:flex; justify-content:space-between; margin-bottom:15px; border-bottom:1px solid #e2e8f0; padding-bottom:10px; align-items:center;">
                <div>
                    <h3 style="margin:0; color:#1e293b;">Konfigurasi Harga</h3>
                    <div style="font-size:12px; color:#64748b; margin-top:2px;">Sales: <span id="lblSalesName" style="color:#2563eb; font-weight:700;"></span></div>
                </div>
                <span onclick="closeModal('modalConfig')" style="cursor: pointer; font-size: 20px; color:#94a3b8;">&times;</span>
            </div>
            
            <div style="flex: 1; overflow-y: auto; padding: 0;">
                <div id="priceListArea">
                    <div style="text-align:center; padding:50px; color:#94a3b8;">
                        <i class="fa fa-spinner fa-spin fa-2x"></i><br>Sedang memuat data...
                    </div>
                </div>
            </div>

            <div style="padding-top: 15px; border-top: 1px solid #e2e8f0; text-align: right; margin-top: 15px;">
                <small style="float:left; color:#94a3b8; line-height:30px; font-size:11px;">*Kosongkan / isi 0 untuk kembali ke harga normal.</small>
                <button onclick="closeModal('modalConfig')" class="btn-act btn-blue">Selesai Konfigurasi</button>
            </div>
        </div>
    </div>

    <div id="saveToast" class="toast">
        <i class="fa fa-check-circle" style="color:#4ade80;"></i> Harga diperbarui!
    </div>

    <?php include '../../layout/footer.php'; ?>
    
    <script>
        function openForm() {
            document.getElementById('id_sales').value = '';
            document.getElementById('nama_sales').value = '';
            document.getElementById('no_hp').value = '';
            document.getElementById('modalForm').style.display = 'flex';
        }

        function editSales(data) {
            document.getElementById('id_sales').value = data.id;
            document.getElementById('nama_sales').value = data.nama_sales;
            document.getElementById('no_hp').value = data.no_hp;
            document.getElementById('modalForm').style.display = 'flex';
        }

        function closeModal(id) {
            document.getElementById(id).style.display = 'none';
        }

        window.onclick = function(event) {
            if (event.target.classList.contains('modal-overlay')) {
                event.target.style.display = "none";
            }
        }

        function openPriceConfig(sid, sname) {
            document.getElementById('lblSalesName').innerText = sname;
            document.getElementById('modalConfig').style.display = 'flex';
            
            <?php
            $items = [];
            $qi = $conn->query("SELECT id, kode_barang, nama_barang, harga_jual FROM barang ORDER BY nama_barang ASC");
            while($i = $qi->fetch_assoc()) $items[] = $i;

            $prices = [];
            $qp = $conn->query("SELECT sales_id, barang_id, harga_khusus FROM barang_harga_sales");
            while($p = $qp->fetch_assoc()) $prices[$p['sales_id']][$p['barang_id']] = $p['harga_khusus'];
            ?>
            
            const products = <?= json_encode($items) ?>;
            const priceMap = <?= json_encode($prices) ?>;
            const currentPrices = priceMap[sid] || {};

            let html = `
                <table class="table-custom">
                    <thead>
                        <tr>
                            <th style="padding-left:15px;">Barang</th>
                            <th width="140" style="text-align:right;">Harga Normal</th>
                            <th width="140" style="text-align:right; padding-right:15px;">Harga Khusus (Rp)</th>
                        </tr>
                    </thead>
                    <tbody>
            `;

            products.forEach(p => {
                let val = currentPrices[p.id] ? currentPrices[p.id] : '';
                html += `
                    <tr>
                        <td style="padding-left:15px;">
                            <div style="font-weight:700; color:#334155;">${p.nama_barang}</div>
                            <div style="font-size:11px; color:#94a3b8; font-family:monospace;">${p.kode_barang}</div>
                        </td>
                        <td style="text-align:right; color:#94a3b8; font-size:12px;">${new Intl.NumberFormat('id-ID').format(p.harga_jual)}</td>
                        <td style="padding-right:15px;">
                            <input type="number" class="form-control inp-price" placeholder="Default" value="${val}" 
                            onchange="savePrice(${sid}, ${p.id}, this.value)">
                        </td>
                    </tr>
                `;
            });
            html += '</tbody></table>';
            document.getElementById('priceListArea').innerHTML = html;
        }

        function savePrice(sid, bid, val) {
            let fd = new FormData();
            fd.append('ajax_act', 'update_price');
            fd.append('sales_id', sid);
            fd.append('barang_id', bid);
            fd.append('harga', val);
            
            fetch('', { method: 'POST', body: fd })
            .then(r => r.text())
            .then(res => {
                let t = document.getElementById('saveToast');
                t.style.display = 'flex';
                setTimeout(() => { t.style.display = 'none'; }, 2000);
            });
        }
    </script>
</body>
</html>