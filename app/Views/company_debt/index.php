<?= $this->extend('layout/template') ?>
<?= $this->section('content') ?>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    :root {
        --primary-color: #0f172a; --accent-color: #3b82f6; 
        --success-color: #10b981; --warning-color: #f59e0b; --danger-color: #ef4444;
        --surface-color: var(--bg-surface); --border-color: var(--border-subtle);
    }
    
    .page-title-box { margin-bottom: 30px; border-left: 6px solid var(--danger-color); padding-left: 20px;}
    .page-title-box h1 { font-size: 28px; font-weight: 900; margin: 0; color: var(--text-main); letter-spacing: -0.5px;}
    .page-title-box p { font-size: 14px; font-weight: 600; color: var(--text-muted); margin: 5px 0 0 0;}

    /* ENTERPRISE METRICS CARDS */
    .metric-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 30px; }
    .metric-card { background: var(--surface-color); border: 1px solid var(--border-color); border-radius: 16px; padding: 25px; display: flex; align-items: flex-start; justify-content: space-between; box-shadow: 0 4px 20px rgba(0,0,0,0.03); transition: transform 0.3s;}
    .metric-card:hover { transform: translateY(-5px); }
    .metric-info h4 { font-size: 12px; font-weight: 800; color: var(--text-muted); text-transform: uppercase; margin: 0 0 8px 0; letter-spacing: 1px;}
    .metric-info h2 { font-size: 26px; font-weight: 900; font-family: 'Space Mono', monospace; margin: 0; color: var(--text-main);}
    .metric-icon { width: 50px; height: 50px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 24px;}
    
    .overall-progress { background: var(--surface-color); border: 1px solid var(--border-color); border-radius: 16px; padding: 25px; margin-bottom: 30px;}
    .op-header { display: flex; justify-content: space-between; font-size: 14px; font-weight: 800; margin-bottom: 15px;}
    .op-bar-bg { width: 100%; height: 12px; background: var(--border-color); border-radius: 20px; overflow: hidden;}
    .op-bar-fill { height: 100%; background: linear-gradient(90deg, var(--danger-color), var(--success-color)); border-radius: 20px; transition: width 1s ease-in-out;}

    /* ENTERPRISE DATA TABLE */
    .data-card { background: var(--surface-color); border: 1px solid var(--border-color); border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.03); overflow: hidden;}
    .data-header { padding: 20px 25px; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center;}
    .data-header h3 { font-size: 16px; font-weight: 800; margin: 0;}
    
    .table-container { width: 100%; overflow-x: auto; }
    table { width: 100%; border-collapse: collapse; min-width: 900px; }
    th { padding: 15px 25px; font-size: 11px; font-weight: 800; text-transform: uppercase; color: var(--text-muted); background: rgba(0,0,0,0.02); text-align: left; border-bottom: 2px solid var(--border-color);}
    td { padding: 20px 25px; border-bottom: 1px solid var(--border-color); font-size: 14px; font-weight: 600; color: var(--text-main); vertical-align: top;}
    tbody tr:hover { background: rgba(0,0,0,0.01); }

    .doc-number { display: inline-block; font-family: 'Space Mono', monospace; font-size: 12px; font-weight: 800; background: rgba(59,130,246,0.1); color: var(--accent-color); padding: 4px 8px; border-radius: 6px; margin-bottom: 6px;}
    .category-tag { font-size: 11px; font-weight: 700; color: var(--text-muted); text-transform: uppercase;}
    
    .status-badge { display: inline-block; padding: 6px 12px; font-size: 11px; font-weight: 900; border-radius: 20px; text-transform: uppercase; letter-spacing: 0.5px;}
    .st-belum { background: rgba(239, 68, 68, 0.1); color: var(--danger-color); border: 1px solid rgba(239, 68, 68, 0.2);}
    .st-sebagian { background: rgba(245, 158, 11, 0.1); color: var(--warning-color); border: 1px solid rgba(245, 158, 11, 0.2);}
    .st-lunas { background: rgba(16, 185, 129, 0.1); color: var(--success-color); border: 1px solid rgba(16, 185, 129, 0.2);}

    .prog-track { width: 100%; background: var(--border-color); height: 6px; border-radius: 10px; margin-top: 8px;}
    .prog-fill { height: 100%; border-radius: 10px;}

    /* BUTTONS & FORMS */
    .btn-primary { background: var(--primary-color); color: #fff; border: none; padding: 12px 24px; border-radius: 10px; font-size: 13px; font-weight: 800; cursor: pointer; transition: 0.2s; display: inline-flex; align-items: center; gap: 8px;}
    .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0,0,0,0.2);}
    
    .btn-pay { background: var(--success-color); color: #fff; border: none; padding: 8px 16px; border-radius: 8px; font-size: 12px; font-weight: 800; cursor: pointer; transition: 0.2s;}
    .btn-pay:hover { opacity: 0.9; box-shadow: 0 4px 10px rgba(16,185,129,0.3);}

    .action-group { display: flex; gap: 6px; justify-content: center; }
    .btn-action { padding: 8px; border: none; border-radius: 8px; color: #fff; cursor: pointer; transition: 0.2s; display: inline-flex; align-items: center; justify-content: center; }
    .btn-edit { background: var(--accent-color); }
    .btn-edit:hover { background: #2563eb; box-shadow: 0 4px 10px rgba(59,130,246,0.3); }
    .btn-delete { background: var(--danger-color); }
    .btn-delete:hover { background: #dc2626; box-shadow: 0 4px 10px rgba(239,68,68,0.3); }

    /* MODAL */
    .modal-overlay { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.6); backdrop-filter: blur(5px); z-index: 1000; display: none; justify-content: center; align-items: center; opacity: 0; transition: 0.3s;}
    .modal-overlay.active { display: flex; opacity: 1; }
    .modal-box { background: var(--surface-color); border-radius: 20px; width: 100%; max-width: 500px; padding: 35px; transform: scale(0.95); transition: 0.3s; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5); max-height: 90vh; overflow-y: auto;}
    .modal-overlay.active .modal-box { transform: scale(1); }

    .form-group { margin-bottom: 20px;}
    .form-label { display: block; font-size: 12px; font-weight: 800; color: var(--text-muted); margin-bottom: 8px; text-transform: uppercase;}
    .form-control { width: 100%; background: var(--bg-base); border: 1px solid var(--border-color); padding: 14px; border-radius: 10px; font-size: 14px; font-weight: 600; color: var(--text-main); font-family: inherit;}
    .form-control:focus { border-color: var(--accent-color); outline: none; box-shadow: 0 0 0 3px rgba(59,130,246,0.1);}
    
    .money-input { display: flex; align-items: center; background: var(--bg-base); border: 1px solid var(--border-color); border-radius: 10px; overflow: hidden;}
    .money-input:focus-within { border-color: var(--success-color); box-shadow: 0 0 0 3px rgba(16,185,129,0.1);}
    .money-input span { padding: 14px 18px; font-weight: 900; background: rgba(0,0,0,0.03); color: var(--text-muted); border-right: 1px solid var(--border-color);}
    .money-input input { border: none; padding: 14px; width: 100%; font-size: 16px; font-weight: 800; font-family: 'Space Mono', monospace; background: transparent; color: var(--text-main); outline: none;}
</style>

<div class="page-title-box">
    <h1>Account Payable (Non-Operasional)</h1>
    <p>Manajemen pencatatan, pelacakan, dan pelunasan kewajiban eksternal perusahaan.</p>
</div>

<div class="metric-grid">
    <div class="metric-card">
        <div class="metric-info">
            <h4>Total Kewajiban (Historis)</h4>
            <h2>Rp <?= number_format($totalDebt, 0, ',', '.') ?></h2>
        </div>
        <div class="metric-icon" style="background: rgba(239,68,68,0.1); color: var(--danger-color);"><i class="ph-bold ph-receipt-x"></i></div>
    </div>
    <div class="metric-card">
        <div class="metric-info">
            <h4>Total Terbayar (Lunas)</h4>
            <h2>Rp <?= number_format($totalPaid, 0, ',', '.') ?></h2>
        </div>
        <div class="metric-icon" style="background: rgba(16,185,129,0.1); color: var(--success-color);"><i class="ph-bold ph-check-fat"></i></div>
    </div>
    <div class="metric-card" style="border-bottom: 4px solid var(--warning-color);">
        <div class="metric-info">
            <h4>Sisa Harus Dibayar</h4>
            <h2 style="color: var(--warning-color);">Rp <?= number_format($totalSisa, 0, ',', '.') ?></h2>
            <div style="font-size: 11px; font-weight: 700; color: var(--text-muted); margin-top: 5px;"><?= $activeCases ?> Dokumen Aktif</div>
        </div>
        <div class="metric-icon" style="background: rgba(245,158,11,0.1); color: var(--warning-color);"><i class="ph-bold ph-warning-circle"></i></div>
    </div>
</div>

<div class="overall-progress">
    <div class="op-header">
        <span><i class="ph-fill ph-chart-line-up" style="color: var(--accent-color);"></i> Persentase Penyelesaian Total</span>
        <span><?= number_format($overallProgress, 2) ?>%</span>
    </div>
    <div class="op-bar-bg">
        <div class="op-bar-fill" style="width: <?= $overallProgress ?>%;"></div>
    </div>
</div>

<div class="data-card">
    <div class="data-header">
        <h3><i class="ph-bold ph-folders"></i> Register Dokumen Kewajiban</h3>
        <button class="btn-primary" onclick="openModal('modalAdd')">
            <i class="ph-bold ph-plus"></i> Terbitkan Dokumen
        </button>
    </div>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Informasi Dokumen</th>
                    <th>Keterangan / Kasus</th>
                    <th style="min-width: 250px;">Progres Pelunasan</th>
                    <th style="text-align: center;">Status</th>
                    <th style="text-align: center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($debts)): ?>
                    <tr><td colspan="5" style="text-align: center; padding: 50px; color: var(--text-muted); font-weight: 800;">Tidak ada catatan dokumen.</td></tr>
                <?php else: ?>
                    <?php foreach($debts as $d): 
                        $pct = ($d['total_debt'] > 0) ? ($d['paid_amount'] / $d['total_debt']) * 100 : 0;
                        $sisa = $d['total_debt'] - $d['paid_amount'];
                        $clr = ($pct == 100) ? 'var(--success-color)' : (($pct > 0) ? 'var(--warning-color)' : 'var(--danger-color)');
                    ?>
                    <tr>
                        <td>
                            <div class="doc-number"><?= esc($d['debt_number']) ?></div>
                            <div style="font-weight: 900; font-size: 15px; margin-bottom: 4px;"><?= esc($d['creditor_name']) ?></div>
                            <div class="category-tag"><i class="ph-fill ph-tag"></i> <?= esc($d['category']) ?></div>
                        </td>
                        <td style="white-space: normal; max-width: 280px; font-size: 13px; line-height: 1.6;">
                            <?= esc($d['description']) ?>
                            <div style="margin-top: 10px; font-size: 10px; font-weight: 700; color: var(--text-muted);">
                                Diinput oleh: <?= esc($d['created_by']) ?> (<?= date('d/m/Y', strtotime($d['created_at'])) ?>)
                            </div>
                        </td>
                        <td>
                            <div style="display: flex; justify-content: space-between; font-family: 'Space Mono'; font-size: 13px; font-weight: 800; margin-bottom: 5px;">
                                <span style="color: var(--success-color);">Rp <?= number_format($d['paid_amount'],0,',','.') ?></span>
                                <span style="color: var(--text-muted);">Rp <?= number_format($d['total_debt'],0,',','.') ?></span>
                            </div>
                            <div class="prog-track">
                                <div class="prog-fill" style="width: <?= $pct ?>%; background: <?= $clr ?>;"></div>
                            </div>
                            <?php if($sisa > 0): ?>
                                <div style="margin-top: 8px; font-size: 12px; font-weight: 800; color: var(--danger-color);">
                                    Sisa: Rp <?= number_format($sisa,0,',','.') ?>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td style="text-align: center;">
                            <?php
                                $badgeClass = 'st-belum';
                                if($d['status'] === 'LUNAS') $badgeClass = 'st-lunas';
                                elseif($d['status'] === 'SEBAGIAN') $badgeClass = 'st-sebagian';
                            ?>
                            <span class="status-badge <?= $badgeClass ?>"><?= esc($d['status']) ?></span>
                        </td>
                        <td style="text-align: center;">
                            <div class="action-group">
                                <?php if($sisa > 0): ?>
                                    <button class="btn-pay" title="Bayar Cicilan" onclick="openPayModal(<?= $d['id'] ?>, '<?= esc($d['debt_number']) ?>', '<?= esc($d['creditor_name']) ?>', <?= $sisa ?>)">
                                        <i class="ph-bold ph-wallet"></i> Bayar
                                    </button>
                                <?php else: ?>
                                    <span style="background: rgba(16, 185, 129, 0.1); color: var(--success-color); padding: 6px 12px; border-radius: 8px; font-size: 11px; font-weight: 900; display: inline-flex; align-items: center; justify-content: center; height: 32px;"><i class="ph-bold ph-check"></i> LUNAS</span>
                                <?php endif; ?>
                                
                                <button class="btn-action btn-edit" title="Edit Dokumen" 
                                    data-id="<?= $d['id'] ?>"
                                    data-cat="<?= esc($d['category']) ?>"
                                    data-name="<?= esc($d['creditor_name']) ?>"
                                    data-amount="<?= round($d['total_debt'], 0) ?>"
                                    data-desc="<?= esc($d['description']) ?>"
                                    onclick="handleEdit(this)">
                                    <i class="ph-bold ph-pencil-simple"></i>
                                </button>
                                
                                <button class="btn-action btn-delete" title="Hapus Dokumen" onclick="confirmDelete(<?= $d['id'] ?>, '<?= esc($d['debt_number']) ?>')">
                                    <i class="ph-bold ph-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal-overlay" id="modalAdd">
    <div class="modal-box">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
            <h3 style="margin: 0; font-size: 18px; font-weight: 900;"><i class="ph-bold ph-file-plus" style="color: var(--accent-color);"></i> Terbitkan Dokumen</h3>
            <button type="button" style="background: transparent; border: none; font-size: 24px; cursor: pointer; color: var(--text-muted);" onclick="closeModal('modalAdd')">&times;</button>
        </div>
        <form action="<?= base_url('companydebt/store') ?>" method="post">
            <?= csrf_field() ?>
            <div class="form-group">
                <label class="form-label">Klasifikasi Kewajiban (Kategori)</label>
                <select name="category" class="form-control" required>
                    <option value="" disabled selected>-- Pilih Klasifikasi --</option>
                    <option value="Penyelesaian Insiden / Hukum">Penyelesaian Insiden / Hukum (Kasus)</option>
                    <option value="Fasilitas Pendanaan Eksternal">Fasilitas Pendanaan Eksternal (Kreditur/Institusi)</option>
                    <option value="Titipan Dana Pemegang Saham">Titipan Dana Pemegang Saham / Investor</option>
                    <option value="Lain-lain">Lain-lain</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Nama Pihak / Kreditor</label>
                <input type="text" name="creditor_name" class="form-control" required placeholder="Cth: Bapak Budi / PT XYZ">
            </div>
            <div class="form-group">
                <label class="form-label">Total Nilai Kewajiban</label>
                <div class="money-input">
                    <span>Rp</span>
                    <input type="text" inputmode="numeric" name="total_debt" required placeholder="0" oninput="formatRupiah(this)">
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Kronologi / Keterangan</label>
                <textarea name="description" class="form-control" rows="3" required placeholder="Jelaskan asal muasal kewajiban secara ringkas..."></textarea>
            </div>
            <button type="submit" class="btn-primary" style="width: 100%; justify-content: center; padding: 16px; font-size: 15px;"><i class="ph-bold ph-floppy-disk"></i> Simpan Dokumen</button>
        </form>
    </div>
</div>

<div class="modal-overlay" id="modalEdit">
    <div class="modal-box" style="border-top: 6px solid var(--accent-color);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
            <h3 style="margin: 0; font-size: 18px; font-weight: 900;"><i class="ph-bold ph-pencil-simple" style="color: var(--accent-color);"></i> Edit Dokumen</h3>
            <button type="button" style="background: transparent; border: none; font-size: 24px; cursor: pointer; color: var(--text-muted);" onclick="closeModal('modalEdit')">&times;</button>
        </div>
        <form id="formEdit" method="post">
            <?= csrf_field() ?>
            <div class="form-group">
                <label class="form-label">Klasifikasi Kewajiban (Kategori)</label>
                <select name="category" id="editCategory" class="form-control" required>
                    <option value="Penyelesaian Insiden / Hukum">Penyelesaian Insiden / Hukum (Kasus)</option>
                    <option value="Fasilitas Pendanaan Eksternal">Fasilitas Pendanaan Eksternal (Kreditur/Institusi)</option>
                    <option value="Titipan Dana Pemegang Saham">Titipan Dana Pemegang Saham / Investor</option>
                    <option value="Lain-lain">Lain-lain</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Nama Pihak / Kreditor</label>
                <input type="text" name="creditor_name" id="editName" class="form-control" required>
            </div>
            <div class="form-group">
                <label class="form-label">Total Nilai Kewajiban</label>
                <div class="money-input">
                    <span>Rp</span>
                    <input type="text" inputmode="numeric" name="total_debt" id="editAmount" required oninput="formatRupiah(this)">
                </div>
                <small style="color: var(--warning-color); font-weight: 700; font-size: 11px;">*Nilai kewajiban tidak boleh kurang dari uang yang sudah dibayarkan.</small>
            </div>
            <div class="form-group">
                <label class="form-label">Kronologi / Keterangan</label>
                <textarea name="description" id="editDesc" class="form-control" rows="3" required></textarea>
            </div>
            <button type="submit" class="btn-primary" style="width: 100%; justify-content: center; padding: 16px; font-size: 15px;"><i class="ph-bold ph-floppy-disk"></i> Simpan Perubahan</button>
        </form>
    </div>
</div>

<div class="modal-overlay" id="modalPay">
    <div class="modal-box" style="border-top: 6px solid var(--success-color);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 style="margin: 0; font-size: 18px; font-weight: 900;"><i class="ph-bold ph-wallet" style="color: var(--success-color);"></i> Alokasi Pembayaran</h3>
            <button type="button" style="background: transparent; border: none; font-size: 24px; cursor: pointer; color: var(--text-muted);" onclick="closeModal('modalPay')">&times;</button>
        </div>
        
        <div style="background: rgba(0,0,0,0.03); border: 1px solid var(--border-color); padding: 15px; border-radius: 12px; margin-bottom: 25px;">
            <div style="font-size: 11px; font-weight: 800; color: var(--text-muted); text-transform: uppercase;">Pembayaran Untuk:</div>
            <div style="font-weight: 900; font-size: 15px; margin: 4px 0;"><span id="payDoc" style="color: var(--accent-color);"></span> - <span id="payName"></span></div>
            <div style="font-size: 11px; font-weight: 800; color: var(--text-muted); text-transform: uppercase; margin-top: 10px;">Maksimal / Sisa Tagihan:</div>
            <div id="paySisaText" style="font-size: 22px; font-weight: 900; font-family: 'Space Mono'; color: var(--danger-color);"></div>
        </div>

        <form id="formPay" method="post">
            <?= csrf_field() ?>
            <div class="form-group">
                <label class="form-label">Nominal Yang Dibayarkan</label>
                <div class="money-input" style="border-color: var(--success-color);">
                    <span style="color: var(--success-color); background: rgba(16,185,129,0.1);">Rp</span>
                    <input type="text" inputmode="numeric" name="amount" id="payAmountInput" required placeholder="0" oninput="formatRupiah(this)">
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Sumber Dana</label>
                <select name="payment_method" class="form-control" required>
                    <option value="BANK">Kas Bank (Transfer / Cek)</option>
                    <option value="CASH">Kas Toko (Uang Tunai)</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Berita Acara / Catatan</label>
                <input type="text" name="notes" class="form-control" placeholder="Cth: Transfer via Mandiri, Bukti #123...">
            </div>
            <button type="submit" class="btn-primary" style="width: 100%; justify-content: center; padding: 16px; font-size: 15px; background: var(--success-color);"><i class="ph-bold ph-check-circle"></i> Proses Pembayaran</button>
        </form>
    </div>
</div>

<script>
    function openModal(id) { document.getElementById(id).classList.add('active'); }
    function closeModal(id) { document.getElementById(id).classList.remove('active'); }
    
    // Buka Modal Pembayaran
    function openPayModal(id, docNum, name, maxAmount) {
        document.getElementById('payDoc').innerText = docNum;
        document.getElementById('payName').innerText = name;
        document.getElementById('paySisaText').innerText = 'Rp ' + maxAmount.toLocaleString('id-ID');
        document.getElementById('formPay').action = "<?= base_url('companydebt/pay/') ?>" + id;
        document.getElementById('payAmountInput').value = '';
        openModal('modalPay');
    }

    // Handle Buka Modal Edit Bebas Error
    function handleEdit(btn) {
        const id = btn.getAttribute('data-id');
        const cat = btn.getAttribute('data-cat');
        const name = btn.getAttribute('data-name');
        const amount = btn.getAttribute('data-amount');
        const desc = btn.getAttribute('data-desc');

        document.getElementById('formEdit').action = "<?= base_url('companydebt/update/') ?>" + id;
        document.getElementById('editCategory').value = cat;
        document.getElementById('editName').value = name;
        document.getElementById('editDesc').value = desc;
        
        let amountInput = document.getElementById('editAmount');
        amountInput.value = amount.toString();
        formatRupiah(amountInput); // Formating input
        
        openModal('modalEdit');
    }

    // Format Rupiah
    function formatRupiah(inputElement) {
        let value = inputElement.value.replace(/[^,\d]/g, '');
        let split = value.split(',');
        let sisa = split[0].length % 3;
        let rupiah = split[0].substr(0, sisa);
        let ribuan = split[0].substr(sisa).match(/\d{3}/gi);
        if (ribuan) {
            let separator = sisa ? '.' : '';
            rupiah += separator + ribuan.join('.');
        }
        rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
        inputElement.value = rupiah;
    }

    // Konfirmasi Hapus SweetAlert2
    function confirmDelete(id, docNum) {
        const isDark = document.documentElement.classList.contains('dark');
        const swalBg = isDark ? '#18181b' : '#ffffff';
        const swalText = isDark ? '#f4f4f5' : '#09090b';

        Swal.fire({
            title: 'Hapus Dokumen?',
            html: "Anda akan menghapus dokumen <b>" + docNum + "</b> beserta <b>seluruh riwayat pembayarannya</b>. Tindakan ini tidak bisa dibatalkan!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#3f3f46',
            confirmButtonText: '<i class="ph-bold ph-trash"></i> Ya, Hapus!',
            cancelButtonText: 'Batal',
            background: swalBg,
            color: swalText,
            customClass: { popup: 'swal2-custom-radius' }
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = "<?= base_url('companydebt/delete/') ?>" + id;
            }
        });
    }

    // Handle submit & bersihkan format titik
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function(e) {
            let moneyInputs = this.querySelectorAll('input[inputmode="numeric"]');
            moneyInputs.forEach(input => {
                if(input.value) input.value = input.value.replace(/\./g, '');
            });
            let btn = this.querySelector('button[type="submit"]');
            if(btn) {
                btn.innerHTML = '<i class="ph-bold ph-spinner-gap ph-spin" style="font-size: 20px;"></i> Memproses...';
                btn.style.opacity = '0.8'; btn.style.pointerEvents = 'none';
            }
        });
    });

    // Alert Handling
    document.addEventListener("DOMContentLoaded", function() {
        const isDark = document.documentElement.classList.contains('dark');
        const swalBg = isDark ? '#18181b' : '#ffffff';
        const swalText = isDark ? '#f4f4f5' : '#09090b';

        <?php if(session()->getFlashdata('success')): ?>
            Swal.fire({ icon: 'success', title: 'Berhasil!', html: '<?= session()->getFlashdata('success') ?>', confirmButtonColor: '#10b981', background: swalBg, color: swalText, customClass: { popup: 'swal2-custom-radius' } });
        <?php endif; ?>
        <?php if(session()->getFlashdata('error')): ?>
            Swal.fire({ icon: 'error', title: 'Gagal!', html: '<?= session()->getFlashdata('error') ?>', confirmButtonColor: '#ef4444', background: swalBg, color: swalText, customClass: { popup: 'swal2-custom-radius' } });
        <?php endif; ?>
    });
</script>

<?= $this->endSection() ?>