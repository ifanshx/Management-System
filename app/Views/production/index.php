<?= $this->extend('layout/template') ?>
<?= $this->section('content') ?>

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<?php
// Kalkulasi Statistik Cepat untuk Dashboard
$spkAktif = 0; $spkSelesai = 0; $totalKnalpotHariIni = 0;
foreach($workOrders as $wo) {
    if($wo['status'] == 'IN_PROGRESS') $spkAktif++;
    if($wo['status'] == 'COMPLETED') $spkSelesai++;
}
foreach($logs as $log) {
    if(date('Y-m-d', strtotime($log['production_date'])) == date('Y-m-d') && $log['is_final_step'] == 1) {
        $totalKnalpotHariIni += $log['qty_produced'];
    }
}
?>

<style>
    :root { --transition-smooth: all 0.3s cubic-bezier(0.16, 1, 0.3, 1); }
    .page-header { margin-bottom: 25px; display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 20px;}
    .page-title { display: flex; align-items: center; gap: 18px;}
    .title-icon { width: 56px; height: 56px; border-radius: 18px; background: linear-gradient(135deg, #3b82f6, #2563eb); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 28px; box-shadow: 0 10px 25px -5px rgba(59, 130, 246, 0.4);}
    .title-text h1 { font-size: 28px; font-weight: 900; color: var(--text-main); margin: 0; letter-spacing: -1px; }
    .title-text p { font-size: 13px; color: var(--text-muted); font-weight: 600; margin: 4px 0 0 0;}

    /* STAT WIDGETS */
    .stats-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 30px;}
    .stat-card { background: var(--bg-surface); border: 1px solid var(--border-subtle); padding: 25px; border-radius: 20px; display: flex; align-items: center; gap: 20px; box-shadow: 0 10px 30px -10px rgba(0,0,0,0.05);}
    .stat-icon { width: 60px; height: 60px; border-radius: 16px; display: flex; align-items: center; justify-content: center; font-size: 28px;}
    .stat-info h4 { margin: 0 0 5px 0; font-size: 12px; color: var(--text-muted); text-transform: uppercase; font-weight: 800;}
    .stat-info h2 { margin: 0; font-size: 26px; color: var(--text-main); font-weight: 900; font-family: 'Space Mono', monospace;}

    .header-actions { display: flex; gap: 12px; }
    .btn-create-bom { background: #fff; color: #8b5cf6; border: 2px solid #e2e8f0; padding: 12px 20px; border-radius: 14px; font-size: 13px; font-weight: 900; text-decoration: none; transition: var(--transition-smooth); display: flex; align-items: center; gap: 8px;}
    .btn-create-bom:hover { border-color: #8b5cf6; background: rgba(139, 92, 246, 0.05); transform: translateY(-2px);}
    .btn-open-spk { background: #2563eb; color: #fff; border: none; padding: 12px 20px; border-radius: 14px; font-size: 13px; font-weight: 900; cursor: pointer; transition: var(--transition-smooth); display: flex; align-items: center; gap: 8px; box-shadow: 0 8px 20px -5px rgba(59, 130, 246, 0.5);}
    .btn-open-spk:hover { background: #1d4ed8; transform: translateY(-2px); }

    .bento-card { background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: 24px; padding: 30px; margin-bottom: 30px; box-shadow: 0 10px 40px -15px rgba(0,0,0,0.05);}
    .card-title { font-size: 16px; font-weight: 900; color: var(--text-main); margin-bottom: 20px; display: flex; align-items: center; gap: 10px; border-bottom: 2px dashed var(--border-subtle); padding-bottom: 15px;}
    .card-title i { background: rgba(59, 130, 246, 0.1); color: #3b82f6; padding: 8px; border-radius: 10px; font-size: 18px;}

    table { width: 100%; border-collapse: collapse; white-space: nowrap; }
    th { text-align: left; padding: 15px 20px; font-size: 11px; font-weight: 900; text-transform: uppercase; color: var(--text-muted); background: #f8fafc; border-bottom: 2px solid var(--border-subtle);}
    td { padding: 15px 20px; border-bottom: 1px dashed var(--border-subtle); color: var(--text-main); font-size: 13px; font-weight: 700; vertical-align: middle;}
    
    .spk-badge { font-family: 'Space Mono', monospace; font-size: 12px; color: #3b82f6; background: rgba(59, 130, 246, 0.1); padding: 4px 10px; border-radius: 8px; font-weight: 900;}
    .status-badge { padding: 6px 12px; border-radius: 8px; font-size: 10px; font-weight: 900; text-transform: uppercase;}
    .s-progress { background: rgba(245, 158, 11, 0.1); color: #d97706; }
    .s-completed { background: rgba(16, 185, 129, 0.1); color: #10b981; }

    .btn-action-sm { padding: 8px 14px; border-radius: 10px; font-size: 12px; font-weight: 900; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; text-decoration: none; border: 1px solid transparent; transition: var(--transition-smooth);}
    .btn-action-sm:hover { transform: translateY(-2px);}
    .btn-complete { background: rgba(245, 158, 11, 0.1); color: #d97706; border-color: rgba(245, 158, 11, 0.3); }
    .btn-complete:hover { background: #d97706; color: #fff;}
    .btn-print { background: rgba(139, 92, 246, 0.1); color: #8b5cf6; border-color: rgba(139, 92, 246, 0.3); }
    .btn-print:hover { background: #8b5cf6; color: #fff;}

    /* MODAL STYLING */
    .modal-overlay { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.6); backdrop-filter: blur(5px); z-index: 1000; display: none; justify-content: center; align-items: center; opacity: 0; transition: opacity 0.3s;}
    .modal-overlay.active { display: flex; opacity: 1; }
    .modal-box { background: #fff; border-radius: 24px; width: 100%; max-width: 480px; padding: 35px; transform: scale(0.95); transition: 0.3s; max-height: 90vh; overflow-y: auto; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5);}
    .modal-overlay.active .modal-box { transform: scale(1); }
    .btn-close { background: #f1f5f9; border: none; width: 36px; height: 36px; border-radius: 50%; font-size: 16px; cursor: pointer; display: flex; align-items: center; justify-content: center;}
    
    .form-group { margin-bottom: 20px; }
    .form-group label { display: block; font-size: 11px; font-weight: 900; color: var(--text-muted); margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px;}
    .form-control { width: 100%; background: #f8fafc; border: 1px solid #e2e8f0; padding: 14px 16px; border-radius: 12px; font-size: 14px; font-weight: 700; outline: none; transition: 0.3s; box-sizing: border-box;}
    .form-control:focus { border-color: #3b82f6; background: #fff; box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);}
    
    .select2-container--default .select2-selection--single { background: #f8fafc; border: 1px solid #e2e8f0; height: 48px; border-radius: 12px; display: flex; align-items: center;}
    .select2-container--default .select2-selection--single .select2-selection__rendered { font-weight: 800; font-size: 14px; }
    
    .input-group { display: flex; align-items: stretch; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; transition: 0.3s;}
    .input-group input { flex: 1; border: none; background: transparent; padding: 14px 16px; font-size: 15px; font-weight: 900; outline: none; width: 100%;}
    .input-group span { padding: 0 16px; background: rgba(0,0,0,0.03); font-size: 12px; font-weight: 800; color: var(--text-muted); display: flex; align-items: center; border-left: 1px solid #e2e8f0;}

    .btn-submit-log { width: 100%; background: linear-gradient(135deg, #f59e0b, #d97706); color: white; border: none; padding: 18px; border-radius: 16px; font-weight: 900; font-size: 15px; display: flex; align-items: center; justify-content: center; gap: 8px; cursor: pointer; transition: 0.3s; margin-top: 25px; box-shadow: 0 8px 20px -5px rgba(245, 158, 11, 0.4);}
    .btn-submit-log:hover { transform: translateY(-3px); box-shadow: 0 12px 25px -5px rgba(245, 158, 11, 0.5);}
</style>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon" style="background: rgba(59, 130, 246, 0.1); color: #3b82f6;"><i class="ph-fill ph-clipboard-text"></i></div>
        <div class="stat-info"><h4>SPK Sedang Berjalan</h4><h2><?= $spkAktif ?> SPK</h2></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background: rgba(16, 185, 129, 0.1); color: #10b981;"><i class="ph-fill ph-check-circle"></i></div>
        <div class="stat-info"><h4>SPK Selesai (All Time)</h4><h2><?= $spkSelesai ?> SPK</h2></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background: rgba(245, 158, 11, 0.1); color: #f59e0b;"><i class="ph-fill ph-package"></i></div>
        <div class="stat-info"><h4>Knalpot Jadi (Hari Ini)</h4><h2><?= $totalKnalpotHariIni ?> Pcs</h2></div>
    </div>
</div>

<div class="page-header">
    <div class="page-title">
        <div class="title-text">
            <h1>Pusat Eksekusi Manufaktur</h1>
            <p>Terbitkan SPK, atur jalur Borongan & Karyawan Tetap, lalu potong material otomatis ke Gudang.</p>
        </div>
    </div>
    <div class="header-actions">
        <a href="<?= base_url('/production/bom_builder') ?>" class="btn-create-bom"><i class="ph-bold ph-flask"></i> Formulasi BoM Baru</a>
        <button onclick="openModal('modalSPK')" class="btn-open-spk"><i class="ph-bold ph-plus-circle"></i> Terbitkan SPK Pabrik</button>
    </div>
</div>

<div class="bento-card">
    <div class="card-title"><i class="ph-fill ph-kanban"></i> Papan Pemantauan SPK Berjalan</div>
    <div class="table-responsive">
        <table id="tableSPK">
            <thead>
                <tr>
                    <th>Dokumen SPK</th>
                    <th>Metode & Target Produk</th>
                    <th style="text-align: center;">Target Pcs</th>
                    <th style="text-align: center;">Status</th>
                    <th style="text-align: center;">Aksi Mandor</th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($workOrders)): ?>
                    <tr><td colspan="5" style="text-align:center; padding:50px; color:#94a3b8; font-size:14px;"><i class="ph-duotone ph-clipboard-text" style="font-size: 48px; display: block; margin-bottom:10px; opacity:0.5;"></i>Belum ada SPK aktif hari ini.</td></tr>
                <?php else: ?>
                    <?php foreach($workOrders as $wo): ?>
                        <tr>
                            <td>
                                <div class="spk-badge"><?= esc($wo['spk_number']) ?></div>
                                <div style="font-size: 10px; color: var(--text-muted); margin-top: 6px;"><i class="ph-bold ph-calendar"></i> <?= date('d M Y', strtotime($wo['start_date'])) ?></div>
                            </td>
                            <td>
                                <div style="font-size: 14px; font-weight: 900; margin-bottom: 4px;"><?= esc($wo['recipe_name']) ?></div>
                                <span style="font-size: 10px; background: #f1f5f9; padding: 4px 8px; border-radius: 6px; border: 1px solid #cbd5e1; font-family: monospace; font-weight: 800; color: #64748b;">Target: <?= esc($wo['fg_sku']) ?></span>
                            </td>
                            <td style="text-align: center; font-family: 'Space Mono', monospace; font-size: 20px; font-weight: 900; color: #2563eb;"><?= $wo['planned_qty'] ?></td>
                            <td style="text-align: center;">
                                <?= ($wo['status'] == 'IN_PROGRESS') ? '<span class="status-badge s-progress"><i class="ph-bold ph-spinner-gap"></i> Dikerjakan</span>' : '<span class="status-badge s-completed"><i class="ph-bold ph-check"></i> Selesai</span>' ?>
                            </td>
                            <td style="text-align: center;">
                                <?php if($wo['status'] == 'IN_PROGRESS'): ?>
                                    <a href="#" onclick="openDailyLogModal(event, <?= $wo['id'] ?>, '<?= esc($wo['spk_number']) ?>')" class="btn-action-sm btn-complete"><i class="ph-bold ph-hammer"></i> Setor Hasil</a>
                                <?php endif; ?>
                                <a href="<?= base_url('production/print_spk/'.$wo['id']) ?>" target="_blank" class="btn-action-sm btn-print" title="Cetak Surat Jalan SPK"><i class="ph-bold ph-printer"></i></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="bento-card">
    <div class="card-title"><i class="ph-fill ph-clock-counter-clockwise" style="color: #f59e0b; background: rgba(245, 158, 11, 0.1);"></i> Log Setoran (Tahapan Kerja)</div>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Waktu & SPK</th>
                    <th>Pekerja / Welder</th>
                    <th>Tahapan Kerja (Operasi)</th>
                    <th style="text-align: center;">Kuantitas</th>
                    <th style="text-align: right;">Upah Tercatat</th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($logs)): ?>
                    <tr><td colspan="5" style="text-align:center; padding:40px; color:#94a3b8;">Belum ada riwayat setoran pekerjaan.</td></tr>
                <?php else: ?>
                    <?php foreach($logs as $log): ?>
                        <tr>
                            <td>
                                <div style="font-size: 12px; font-weight: 800; color: var(--text-main); margin-bottom: 2px;"><?= date('d M, H:i', strtotime($log['production_date'])) ?></div>
                                <div style="font-size: 10px; color: var(--text-muted); font-family: 'Space Mono'; border: 1px solid var(--border-subtle); display: inline-block; padding: 2px 6px; border-radius: 4px;"><?= esc($log['spk_number']) ?></div>
                            </td>
                            <td>
                                <div style="font-weight: 900; font-size: 13px;"><?= esc($log['employee_name']) ?></div>
                                <?php if(stripos($log['emp_status'], 'Borong') !== false): ?>
                                    <div style="font-size: 10px; color: #f59e0b; font-weight: 900; margin-top: 4px; background: rgba(245,158,11,0.1); display: inline-block; padding: 2px 6px; border-radius: 4px;">BORONGAN</div>
                                <?php else: ?>
                                    <div style="font-size: 10px; color: #3b82f6; font-weight: 900; margin-top: 4px; background: rgba(59,130,246,0.1); display: inline-block; padding: 2px 6px; border-radius: 4px;">TETAP (Rp 0)</div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div style="font-size: 13px; font-weight: 800; color: #0f172a;"><?= esc($log['operation_name']) ?></div>
                                <?php if($log['is_final_step'] == 1): ?>
                                    <div style="font-size: 10px; font-weight: 900; background: rgba(16,185,129,0.1); color: #10b981; padding: 3px 8px; border-radius: 6px; display: inline-block; margin-top: 6px;"><i class="ph-bold ph-package"></i> Masuk Gudang</div>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: center; font-family: 'Space Mono', monospace; font-weight: 900; color: #10b981; font-size: 16px;">+<?= $log['qty_produced'] ?></td>
                            <td style="text-align: right; font-family: 'Space Mono', monospace; font-weight: 900; font-size: 14px; <?= $log['total_wage'] == 0 ? 'color: var(--text-muted);' : 'color: #ef4444;' ?>">
                                Rp <?= number_format($log['total_wage'], 0, ',', '.') ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal-overlay" id="modalSPK">
    <div class="modal-box" style="border-top: 8px solid #2563eb;">
        <div class="modal-header">
            <h3 style="margin: 0; font-weight: 900; font-size: 20px; display: flex; align-items: center; gap: 10px;"><div style="background: rgba(59,130,246,0.1); color: #2563eb; padding: 8px; border-radius: 12px;"><i class="ph-bold ph-clipboard-text"></i></div> Terbitkan SPK Produksi</h3>
            <button class="btn-close" onclick="closeModal('modalSPK')" type="button"><i class="ph-bold ph-x"></i></button>
        </div>
        <form id="formSPK" action="<?= base_url('production/create_spk') ?>" method="post">
            <?= csrf_field() ?>
            <div class="form-group">
                <label>Pilih Resep Produksi (BOM & Tahapan)</label>
                <select name="bom_id" class="form-control" required style="padding: 16px; border-radius: 14px;">
                    <option value="">-- Silakan Pilih Resep / Metode BoM --</option>
                    <?php foreach($boms as $b): ?>
                        <option value="<?= $b['id'] ?>"><?= esc($b['recipe_name']) ?> [<?= esc($b['fg_sku']) ?>]</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Target Akhir Produk (Pcs)</label>
                <div class="input-group" style="border-color: #2563eb;">
                    <input type="number" name="planned_qty" placeholder="0" required min="1" autocomplete="off" style="color: #2563eb; font-size: 24px; text-align: center;">
                    <span style="border-left-color: rgba(59,130,246,0.2); background: rgba(59,130,246,0.05); color: #2563eb;">Pcs</span>
                </div>
            </div>
            <button type="submit" class="btn-submit-log" style="background: linear-gradient(135deg, #3b82f6, #2563eb); margin-top: 30px;"><i class="ph-bold ph-paper-plane-tilt" style="font-size: 20px;"></i> Buat SPK Sekarang</button>
        </form>
    </div>
</div>

<div class="modal-overlay" id="modalDailyLog">
    <div class="modal-box" style="border-top: 8px solid #f59e0b;">
        <div class="modal-header">
            <h3 style="margin: 0; font-weight: 900; font-size: 20px; display: flex; align-items: center; gap: 10px;"><div style="background: rgba(245,158,11,0.1); color: #f59e0b; padding: 8px; border-radius: 12px;"><i class="ph-bold ph-hammer"></i></div> Setoran Pekerjaan</h3>
            <button class="btn-close" onclick="closeModal('modalDailyLog')" type="button"><i class="ph-bold ph-x"></i></button>
        </div>
        
        <div style="background: #fffbeb; padding: 15px; border-radius: 14px; margin-bottom: 25px; text-align: center; border: 1px dashed #fcd34d;">
            <span style="font-size: 11px; font-weight: 900; color: #d97706; text-transform: uppercase;">Mengerjakan SPK:</span><br>
            <strong id="displaySpkNumber" style="font-family: 'Space Mono', monospace; font-size: 20px; color: #b45309; margin-top: 4px; display: inline-block;"></strong>
        </div>

        <form id="formDailyLog" action="<?= base_url('production/add_production_log') ?>" method="post">
            <?= csrf_field() ?>
            <input type="hidden" name="spk_id" id="logSpkId">
            
            <div class="form-group">
                <label>Pilih Tahapan (Cth: Roll Monel, Las Cacing, Amril)</label>
                <select name="operation_id" id="operationSelect" class="form-control" required style="border-color: #3b82f6; font-weight: 900; color: #2563eb; padding: 14px;">
                    <option value="">-- Sedang Memuat Data... --</option>
                </select>
                <div style="font-size: 11px; color: var(--text-muted); margin-top: 6px; font-weight: 600;"><i class="ph-fill ph-info"></i> Tahap final akan otomatis memotong material gudang.</div>
            </div>

            <div class="form-group">
                <label>Nama Tukang / Pekerja</label>
                <select name="employee_id" class="form-control select2-employee" required>
                    <option value=""></option> 
                    <?php if(!empty($workers)): ?>
                        <optgroup label="Tukang Borongan (Dihitung per Pcs)">
                            <?php foreach($workers as $w): 
                                $isBorong = (stripos($w['status'] ?? '', 'Borong') !== false);
                                if($isBorong): 
                            ?>
                                <option value="<?= esc($w['employee_id']) ?>">🛠️ <?= esc($w['name']) ?> - [<?= esc($w['position']) ?>]</option>
                            <?php endif; endforeach; ?>
                        </optgroup>
                        <optgroup label="Karyawan Tetap (Upah Produksi = Rp 0)">
                            <?php foreach($workers as $w): 
                                $isBorong = (stripos($w['status'] ?? '', 'Borong') !== false);
                                if(!$isBorong): 
                            ?>
                                <option value="<?= esc($w['employee_id']) ?>">✅ <?= esc($w['name']) ?> (Gaji Tetap)</option>
                            <?php endif; endforeach; ?>
                        </optgroup>
                    <?php endif; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Kuantitas Setoran (Diselesaikan)</label>
                <div class="input-group" style="border-color: #f59e0b;">
                    <input type="number" name="qty_produced" placeholder="0" required min="1" autocomplete="off" style="color: #f59e0b; font-size: 24px; text-align: center;">
                    <span style="border-left-color: rgba(245, 158, 11, 0.2); background: rgba(245, 158, 11, 0.05); color: #f59e0b;">Pcs</span>
                </div>
            </div>

            <div style="margin-top: 25px; border-top: 1px dashed var(--border-subtle); padding-top: 20px;">
                <div style="font-size: 12px; font-weight: 900; color: var(--text-muted); cursor: pointer; display: flex; align-items: center; justify-content: space-between;" onclick="$('#customWageDiv').slideToggle();">
                    <span style="display:flex; align-items:center; gap:6px;"><i class="ph-bold ph-caret-down"></i> [OPSIONAL] Harga Nego Khusus / Overhead</span>
                </div>
                
                <div id="customWageDiv" style="display: none; background: #f8fafc; padding: 18px; border-radius: 16px; border: 1px solid var(--border-subtle); margin-top: 15px;">
                    <div class="form-group" style="margin-bottom: 15px;">
                        <label>Harga Nego per Pcs (Bila Beda dgn BoM)</label>
                        <div class="input-group"><span style="color:#ef4444;">Rp</span><input type="text" name="custom_wage" placeholder="Kosongkan jika standar" onkeyup="formatRupiah(this)" style="color: #ef4444; font-family: monospace;"></div>
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label>Biaya Overhead Habis Pakai (Cth: Gas Argon)</label>
                        <div class="input-group"><span style="color:#8b5cf6;">Rp</span><input type="text" name="overhead_cost" placeholder="0" onkeyup="formatRupiah(this)" style="color: #8b5cf6; font-family: monospace;"></div>
                    </div>
                </div>
            </div>

            <button type="submit" id="btnSubmitLog" class="btn-submit-log"><i class="ph-bold ph-floppy-disk" style="font-size: 22px;"></i> Rekam Setoran Pekerjaan</button>
        </form>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('.select2-employee').select2({ width: '100%', placeholder: "Pilih / Ketik Nama Pekerja", dropdownParent: $('#modalDailyLog') });
    });

    function openModal(modalId) { document.getElementById(modalId).classList.add('active'); }
    function closeModal(modalId) { 
        document.getElementById(modalId).classList.remove('active'); 
        if(modalId === 'modalDailyLog') { 
            $('.select2-employee').val(null).trigger('change'); 
            $('#customWageDiv').hide(); 
        }
    }
    
    function formatRupiah(angka) {
        if (!angka) return;
        let number_string = angka.value.replace(/[^,\d]/g, '').toString(), split = number_string.split(','), sisa = split[0].length % 3, rupiah = split[0].substr(0, sisa), ribuan = split[0].substr(sisa).match(/\d{3}/gi);
        if (ribuan) { let separator = sisa ? '.' : ''; rupiah += separator + ribuan.join('.'); }
        rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah; angka.value = rupiah;
    }

    function openDailyLogModal(event, spkId, spkNumber) {
        event.preventDefault();
        document.getElementById('logSpkId').value = spkId;
        document.getElementById('displaySpkNumber').innerText = spkNumber;
        document.getElementById('formDailyLog').reset();
        $('.select2-employee').val(null).trigger('change'); 
        $('#customWageDiv').hide();
        
        let opSelect = document.getElementById('operationSelect');
        opSelect.innerHTML = '<option value="">Sedang memuat data tahapan...</option>';
        
        let ajaxUrl = '<?= base_url('production/get_operations') ?>' + '/' + spkId;
        
        fetch(ajaxUrl)
            .then(response => {
                if(!response.ok) throw new Error("Koneksi ke Controller Gagal (Error " + response.status + ")");
                return response.json();
            })
            .then(res => {
                if(res.status === 'success') {
                    opSelect.innerHTML = '<option value="">-- Pilih Tahapan (Roll, Las Cacing, dll) --</option>';
                    res.data.forEach(op => {
                        let finalLabel = (op.is_final_step == 1) ? ' 🌟 (Tahap Final - Masuk Gudang)' : '';
                        opSelect.innerHTML += `<option value="${op.id}">${op.operation_name} - Rp ${parseInt(op.wage_per_piece).toLocaleString('id-ID')}/pcs${finalLabel}</option>`;
                    });
                } else {
                    opSelect.innerHTML = `<option value="">❌ ${res.message}</option>`;
                }
            })
            .catch(error => {
                console.error("AJAX Error:", error);
                opSelect.innerHTML = '<option value="">🚨 Error AJAX. Lihat Console (F12).</option>';
            });

        openModal('modalDailyLog');
    }
    
    document.getElementById('formDailyLog').addEventListener('submit', function() {
        const btn = document.getElementById('btnSubmitLog');
        btn.style.opacity = '0.8'; btn.style.pointerEvents = 'none';
        btn.innerHTML = '<i class="ph-bold ph-spinner-gap ph-spin" style="font-size: 22px;"></i> Memproses Jurnal...';
    });

    document.addEventListener("DOMContentLoaded", function() {
        const isDark = document.documentElement.classList.contains('dark');
        <?php if(session()->getFlashdata('success')): ?>
            Swal.fire({ icon: 'success', title: 'Tercatat!', html: '<?= session()->getFlashdata('success') ?>', confirmButtonColor: '#10b981', background: isDark ? '#18181b' : '#ffffff', color: isDark ? '#f4f4f5' : '#09090b', customClass: { popup: 'swal2-custom-radius' } });
        <?php endif; ?>
        <?php if(session()->getFlashdata('error')): ?>
            Swal.fire({ icon: 'error', title: 'Ditolak!', html: '<?= session()->getFlashdata('error') ?>', confirmButtonColor: '#ef4444', background: isDark ? '#18181b' : '#ffffff', color: isDark ? '#f4f4f5' : '#09090b', customClass: { popup: 'swal2-custom-radius' } });
        <?php endif; ?>
    });
</script>

<?= $this->endSection() ?>