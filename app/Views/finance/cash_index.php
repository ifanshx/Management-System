<?= $this->extend('layout/template') ?>
<?= $this->section('content') ?>

<style>
    /* --- HEADER --- */
    .page-header { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 25px; flex-wrap: wrap; gap: 20px;}
    .page-title h1 { font-size: 26px; font-weight: 800; color: var(--text-main); margin-bottom: 5px; letter-spacing: -1px; }
    .page-title p { font-size: 13px; color: var(--text-muted); }

    /* --- STATS BENTO GRID --- */
    .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 25px; }
    .stat-card { background: var(--bg-surface); border: 1px solid var(--border-subtle); padding: 20px; border-radius: 16px; display: flex; align-items: center; gap: 15px; box-shadow: var(--shadow-card); transition: 0.3s; }
    .stat-card:hover { border-color: var(--accent-main); transform: translateY(-2px); }
    .stat-icon { width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 24px; }

    /* --- LAYOUT UTAMA --- */
    .finance-grid { display: grid; grid-template-columns: 380px 1fr; gap: 20px; align-items: start; }
    @media (max-width: 1024px) { .finance-grid { grid-template-columns: 1fr; } }

    /* --- KARTU BENTO (FORM & INFO) --- */
    .bento-card { background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: 20px; padding: 25px; box-shadow: var(--shadow-card); }
    .card-header { display: flex; align-items: center; gap: 10px; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 1px dashed var(--border-subtle); }
    .icon-wrapper { width: 34px; height: 34px; border-radius: 8px; background: var(--accent-light); color: var(--accent-main); display: flex; align-items: center; justify-content: center; font-size: 18px; }
    .card-header h3 { font-size: 15px; font-weight: 800; color: var(--text-main); margin: 0; }

    /* --- FORM ELEMENTS --- */
    .form-group { margin-bottom: 16px; }
    .form-label { display: block; font-size: 12px; font-weight: 700; color: var(--text-main); margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.5px; }
    
    .input-wrapper { display: flex; align-items: stretch; background: var(--bg-base); border: 1px solid var(--border-subtle); border-radius: 10px; overflow: hidden; transition: all 0.2s ease; }
    .input-wrapper:focus-within { border-color: var(--accent-main); box-shadow: 0 0 0 3px var(--accent-light); }
    .input-wrapper input, .input-wrapper textarea { flex: 1; background: transparent; border: none; color: var(--text-main); padding: 12px 14px; font-size: 13px; font-weight: 600; outline: none; font-family: inherit; resize: none; width: 100%;}
    
    .prefix { background: rgba(0,0,0,0.02); color: var(--text-muted); font-size: 12px; font-weight: 700; padding: 0 14px; display: flex; align-items: center; border-right: 1px solid var(--border-subtle); }
    html.dark .prefix { background: rgba(255,255,255,0.02); }

    /* --- CUSTOM RADIO BOX (BIASA & MUTASI) --- */
    .radio-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
    .radio-label { position: relative; cursor: pointer; display: block;}
    .radio-label input { position: absolute; opacity: 0; }
    .radio-box { display: flex; align-items: center; justify-content: center; gap: 6px; padding: 10px; background: var(--bg-base); border: 1px solid var(--border-subtle); border-radius: 10px; font-size: 12px; font-weight: 700; color: var(--text-muted); transition: 0.2s; }
    
    /* Active States (Transaksi Biasa) */
    .radio-label input:checked + .radio-box.in { background: rgba(16, 185, 129, 0.1); border-color: #10b981; color: #10b981; }
    .radio-label input:checked + .radio-box.out { background: rgba(239, 68, 68, 0.1); border-color: #ef4444; color: #ef4444; }
    .radio-label input:checked + .radio-box.cash { background: rgba(249, 115, 22, 0.1); border-color: #f97316; color: #f97316; }
    .radio-label input:checked + .radio-box.atm { background: rgba(139, 92, 246, 0.1); border-color: #8b5cf6; color: #8b5cf6; }

    /* Active States (Mutasi Khusus) */
    .radio-box.mutasi-box { padding: 15px; justify-content: space-between; border: 2px solid transparent; background: var(--bg-base); }
    .radio-label input:checked + .radio-box.mutasi-box { background: var(--accent-light); border-color: var(--accent-main); box-shadow: 0 4px 12px rgba(37,99,235,0.1); }

    /* Segmented Control (Tabs) */
    .segment-control { display: flex; background: var(--bg-base); border: 1px solid var(--border-subtle); border-radius: 10px; padding: 4px; margin-bottom: 20px; }
    .segment-btn { flex: 1; text-align: center; padding: 8px; font-size: 12px; font-weight: 700; color: var(--text-muted); border-radius: 6px; cursor: pointer; transition: 0.2s; }
    .segment-btn.active { background: var(--bg-surface); color: var(--accent-main); box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
    
    .tab-content { display: none; }
    .tab-content.active { display: block; animation: fadeIn 0.3s; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(5px); } to { opacity: 1; transform: translateY(0); } }

    .btn-submit { width: 100%; background: var(--accent-main); color: #fff; padding: 14px; border: none; border-radius: 10px; font-weight: 800; font-size: 13px; cursor: pointer; transition: 0.3s; display: flex; justify-content: center; align-items: center; gap: 8px; box-shadow: 0 4px 12px var(--accent-light); margin-top: 10px;}
    .btn-submit:hover { transform: translateY(-3px); filter: brightness(1.1); }

    /* --- TABLE STYLES --- */
    .table-card { background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: 20px; box-shadow: var(--shadow-card); overflow: hidden; }
    .table-controls { padding: 20px 24px; border-bottom: 1px solid var(--border-subtle); display: flex; justify-content: space-between; align-items: center; gap: 15px; flex-wrap: wrap; background: rgba(0,0,0,0.01); }
    html.dark .table-controls { background: rgba(255,255,255,0.02); }
    
    .form-control { background: var(--bg-base); border: 1px solid var(--border-subtle); color: var(--text-main); padding: 10px 15px; border-radius: 10px; font-family: inherit; font-size: 13px; outline: none; }
    .btn-filter { background: var(--bg-base); color: var(--text-main); border: 1px solid var(--border-subtle); padding: 10px 20px; border-radius: 10px; font-weight: 700; cursor: pointer; transition: 0.3s; display: flex; align-items: center; gap: 8px;}
    .btn-filter:hover { background: var(--border-subtle); }

    table { width: 100%; border-collapse: collapse; white-space: nowrap; }
    th { text-align: left; padding: 15px 24px; font-size: 11px; font-weight: 700; text-transform: uppercase; color: var(--text-muted); background: var(--bg-base); border-bottom: 1px solid var(--border-subtle); }
    td { padding: 16px 24px; border-bottom: 1px solid var(--border-subtle); color: var(--text-main); font-size: 13px; font-weight: 500; }
    tr:hover td { background: rgba(0,0,0,0.01); }
    html.dark tr:hover td { background: rgba(255,255,255,0.02); }

    .badge-metode { padding: 4px 10px; border-radius: 6px; font-size: 11px; font-weight: 800; display: inline-flex; align-items: center; gap: 4px; }
    .badge-metode.cash { background: rgba(249, 115, 22, 0.1); color: #f97316; border: 1px solid rgba(249, 115, 22, 0.2); }
    .badge-metode.atm { background: rgba(139, 92, 246, 0.1); color: #8b5cf6; border: 1px solid rgba(139, 92, 246, 0.2); }

    .font-mono { font-family: monospace; font-weight: 800; font-size: 14px; }
    .text-green { color: #10b981; }
    .text-red { color: #ef4444; }
</style>

<div class="page-header">
    <div class="page-title">
        <h1>Kas Operasional (Petty Cash)</h1>
        <p>Catat dan pantau aliran dana harian pabrik secara terpusat.</p>
    </div>
    
    <div style="text-align: right; background: var(--bg-surface); padding: 10px 20px; border-radius: 12px; border: 1px solid var(--border-subtle); box-shadow: var(--shadow-card);">
        <div style="font-size: 11px; color: var(--text-muted); font-weight: 700; text-transform: uppercase;">Waktu Real-Time</div>
        <div id="live-clock" style="font-size: 20px; font-weight: 800; font-family: monospace; color: var(--accent-main);"><?= date('H:i:s') ?></div>
    </div>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon" style="background: rgba(100, 116, 139, 0.1); color: #64748b;"><i class="ph ph-clock-counter-clockwise"></i></div>
        <div>
            <div style="font-size: 11px; color: var(--text-muted); font-weight: 700;">SALDO AWAL (HARI INI)</div>
            <div class="font-mono" style="font-size: 18px; color: var(--text-main);">Rp <?= number_format($saldo_awal, 0, ',', '.') ?></div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background: rgba(16, 185, 129, 0.1); color: #10b981;"><i class="ph ph-trend-up"></i></div>
        <div>
            <div style="font-size: 11px; color: var(--text-muted); font-weight: 700;">PEMASUKAN GLOBAL</div>
            <div class="font-mono" style="font-size: 18px; color: #10b981;">+ Rp <?= number_format($masuk_hari_ini, 0, ',', '.') ?></div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background: rgba(239, 68, 68, 0.1); color: #ef4444;"><i class="ph ph-trend-down"></i></div>
        <div>
            <div style="font-size: 11px; color: var(--text-muted); font-weight: 700;">PENGELUARAN GLOBAL</div>
            <div class="font-mono" style="font-size: 18px; color: #ef4444;">- Rp <?= number_format($keluar_hari_ini, 0, ',', '.') ?></div>
        </div>
    </div>
    <div class="stat-card" style="border-color: var(--accent-main); background: var(--accent-light);">
        <div class="stat-icon" style="background: var(--accent-main); color: #fff;"><i class="ph ph-wallet"></i></div>
        <div>
            <div style="font-size: 11px; color: var(--accent-main); font-weight: 800;">TOTAL SALDO AKHIR</div>
            <div class="font-mono" style="font-size: 20px; color: var(--accent-main);">Rp <?= number_format($saldo_akhir, 0, ',', '.') ?></div>
        </div>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 25px;">
    <div style="background: var(--bg-surface); padding: 15px 20px; border-radius: 16px; border: 1px solid rgba(249, 115, 22, 0.3); border-left: 4px solid #f97316; display: flex; justify-content: space-between; align-items: center;">
        <div>
            <div style="font-size: 11px; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">Dompet Tunai (Brankas Cash)</div>
            <div class="font-mono" style="font-size: 18px; color: var(--text-main);">Rp <?= number_format($sisa_cash, 0, ',', '.') ?></div>
        </div>
        <i class="ph ph-money" style="font-size: 32px; color: #f97316; opacity: 0.5;"></i>
    </div>
    <div style="background: var(--bg-surface); padding: 15px 20px; border-radius: 16px; border: 1px solid rgba(139, 92, 246, 0.3); border-left: 4px solid #8b5cf6; display: flex; justify-content: space-between; align-items: center;">
        <div>
            <div style="font-size: 11px; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">Rekening Bank (Saldo ATM)</div>
            <div class="font-mono" style="font-size: 18px; color: var(--text-main);">Rp <?= number_format($sisa_atm, 0, ',', '.') ?></div>
        </div>
        <i class="ph ph-credit-card" style="font-size: 32px; color: #8b5cf6; opacity: 0.5;"></i>
    </div>
</div>


<div class="finance-grid">
    
    <div class="bento-card" style="position: sticky; top: 20px;">
        <div class="card-header">
            <div class="icon-wrapper"><i class="ph ph-receipt"></i></div>
            <h3>Catat Transaksi</h3>
        </div>
        
        <div class="segment-control">
            <div class="segment-btn active" id="btn-biasa" onclick="switchTab('biasa')">Transaksi Biasa</div>
            <div class="segment-btn" id="btn-mutasi" onclick="switchTab('mutasi')">Pindah Dana</div>
        </div>

        <form action="<?= base_url('/finance/cash_store') ?>" method="POST" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <input type="hidden" name="transaction_date" value="<?= esc($tgl_filter) ?>">
            
            <div id="tab-biasa" class="tab-content active">
                <input type="hidden" name="mode_input" id="mode_input_biasa" value="transaksi">
                
                <div class="form-group">
                    <label class="form-label">Arus Kas</label>
                    <div class="radio-grid">
                        <label class="radio-label">
                            <input type="radio" name="type" value="Cash In">
                            <div class="radio-box in"><i class="ph ph-arrow-down-left"></i> Masuk</div>
                        </label>
                        <label class="radio-label">
                            <input type="radio" name="type" value="Cash Out" checked>
                            <div class="radio-box out"><i class="ph ph-arrow-up-right"></i> Keluar</div>
                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Sumber Dana</label>
                    <div class="radio-grid">
                        <label class="radio-label">
                            <input type="radio" name="metode" value="Cash" checked>
                            <div class="radio-box cash"><i class="ph ph-money"></i> Cash</div>
                        </label>
                        <label class="radio-label">
                            <input type="radio" name="metode" value="ATM">
                            <div class="radio-box atm"><i class="ph ph-credit-card"></i> ATM</div>
                        </label>
                    </div>
                </div>
            </div>

            <div id="tab-mutasi" class="tab-content">
                <input type="hidden" name="mode_input" id="mode_input_mutasi" value="mutasi" disabled>
                <div class="form-group">
                    <label class="form-label">Jenis Pemindahan Aset</label>
                    <div style="display: flex; flex-direction: column; gap: 12px;">
                        
                        <label class="radio-label">
                            <input type="radio" name="arah_mutasi" value="atm_to_cash" checked>
                            <div class="radio-box mutasi-box">
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <span style="color: #8b5cf6;"><i class="ph ph-credit-card"></i> ATM</span>
                                    <i class="ph ph-arrow-right" style="color: var(--text-muted);"></i>
                                    <span style="color: #f97316;"><i class="ph ph-money"></i> CASH</span>
                                </div>
                                <span style="font-size:10px; background:var(--bg-surface); padding:4px 8px; border-radius:6px; border: 1px solid var(--border-subtle); color: var(--text-main);">TARIK TUNAI</span>
                            </div>
                        </label>

                        <label class="radio-label">
                            <input type="radio" name="arah_mutasi" value="cash_to_atm">
                            <div class="radio-box mutasi-box">
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <span style="color: #f97316;"><i class="ph ph-money"></i> CASH</span>
                                    <i class="ph ph-arrow-right" style="color: var(--text-muted);"></i>
                                    <span style="color: #8b5cf6;"><i class="ph ph-credit-card"></i> ATM</span>
                                </div>
                                <span style="font-size:10px; background:var(--bg-surface); padding:4px 8px; border-radius:6px; border: 1px solid var(--border-subtle); color: var(--text-main);">SETOR TUNAI</span>
                            </div>
                        </label>

                    </div>
                </div>
            </div>

            <div class="form-group" style="margin-top: 20px;">
                <label class="form-label" style="color: var(--accent-main);">Nominal (Rp)</label>
                <div class="input-wrapper" style="border-color: var(--accent-main); box-shadow: 0 4px 15px var(--accent-light);">
                    <div class="prefix" style="color: var(--accent-main); font-weight: 800; background: var(--accent-light);">Rp</div>
                    <input type="text" name="amount" placeholder="0" onkeyup="formatRupiah(this)" style="font-size: 20px; font-family: monospace; font-weight: 800; color: var(--text-main); padding: 15px;" required autocomplete="off">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Keterangan Lengkap</label>
                <div class="input-wrapper">
                    <textarea name="description" rows="2" placeholder="Contoh: Beli bensin mobil box..." required></textarea>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Bukti Nota / Struk (Opsional)</label>
                <div class="input-wrapper" style="padding: 5px;">
                    <input type="file" name="receipt_file" accept="image/*" style="font-size: 12px; padding: 6px;">
                </div>
            </div>

            <button type="submit" class="btn-submit">
                <i class="ph ph-floppy-disk"></i> Bukukan Transaksi
            </button>
        </form>
    </div>

    <div class="table-card">
        
        <div class="table-controls">
            <form action="" method="get" style="display: flex; gap: 15px; align-items: center;">
                <div style="font-weight: 700; font-size: 13px; color: var(--text-main);">Tanggal Buku:</div>
                <input type="date" name="tgl" class="form-control" value="<?= esc($tgl_filter) ?>" onchange="this.form.submit()">
                <button type="submit" class="btn-filter"><i class="ph ph-magnifying-glass"></i> Tampilkan</button>
            </form>
        </div>

        <div style="overflow-x: auto; max-height: 700px; overflow-y: auto;">
            <table>
                <thead style="position: sticky; top: 0; z-index: 10;">
                    <tr>
                        <th>Waktu & ID</th>
                        <th>Keterangan Transaksi</th>
                        <th style="text-align: center;">Metode</th>
                        <th style="text-align: right;">Nominal</th>
                        <th style="text-align: right;">Saldo Berjalan</th>
                        <th style="text-align: center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr style="background: rgba(0,0,0,0.02);">
                        <td style="border-left: 3px solid var(--text-main);"><b>SALDO AWAL</b></td>
                        <td colspan="2"><span style="background: var(--border-subtle); padding: 4px 8px; border-radius: 6px; font-size: 10px; font-weight: 800;">SISTEM KALKULASI</span></td>
                        <td style="text-align: right; color: var(--text-muted);">-</td>
                        <td style="text-align: right; font-family: monospace; font-size: 15px; font-weight: 800;">Rp <?= number_format($saldo_awal, 0, ',', '.') ?></td>
                        <td></td>
                    </tr>
                    
                    <?php if(empty($transactions)): ?>
                        <tr><td colspan="6" style="text-align: center; padding: 60px 20px; color: var(--text-muted);">Belum ada transaksi di tanggal ini.</td></tr>
                    <?php else: ?>
                        <?php $running = $saldo_awal; ?>
                        <?php foreach($transactions as $trx): ?>
                        <?php 
                            if($trx['type'] == 'Cash In') {
                                $running += $trx['amount'];
                                $color = 'text-green'; $sign = '+';
                            } else {
                                $running -= $trx['amount'];
                                $color = 'text-red'; $sign = '-';
                            }
                        ?>
                        <tr>
                            <td>
                                <div style="font-weight: 700; font-size: 13px;"><?= date('H:i', strtotime($trx['created_at'])) ?> WIB</div>
                                <div style="font-size: 11px; color: var(--text-muted); font-family: monospace; margin-top: 4px;"><?= esc($trx['transaction_code']) ?></div>
                            </td>
                            <td>
                                <div style="max-width: 250px; white-space: normal; line-height: 1.4; font-weight: 600; margin-bottom: 4px;"><?= esc($trx['description']) ?></div>
                                <div style="font-size: 11px; color: var(--text-muted);"><i class="ph ph-user"></i> <?= esc($trx['pic_name']) ?></div>
                            </td>
                            <td style="text-align: center;">
                                <span class="badge-metode <?= strtolower($trx['metode']) ?>"><?= esc($trx['metode']) ?></span>
                            </td>
                            <td style="text-align: right;" class="font-mono <?= $color ?>">
                                <?= $sign ?> Rp <?= number_format($trx['amount'], 0, ',', '.') ?>
                            </td>
                            <td style="text-align: right; color: var(--accent-main);" class="font-mono">
                                Rp <?= number_format($running, 0, ',', '.') ?>
                            </td>
                            <td style="text-align: center; display: flex; justify-content: center; gap: 8px;">
                                <?php if($trx['receipt_file']): ?>
                                    <a href="<?= base_url('uploads/receipts/' . $trx['receipt_file']) ?>" target="_blank" style="color: var(--accent-main); font-size: 18px;" title="Lihat Struk"><i class="ph ph-image"></i></a>
                                <?php else: ?>
                                    <span style="color: var(--border-subtle); font-size: 18px;" title="Tanpa Struk"><i class="ph ph-prohibit"></i></span>
                                <?php endif; ?>
                                <a href="javascript:void(0)" onclick="confirmDelete('<?= base_url('/finance/cash_delete/'.$trx['id']) ?>')" style="color: #ef4444; font-size: 18px;" title="Hapus Data"><i class="ph ph-trash"></i></a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function updateClock() {
        const now = new Date();
        document.getElementById('live-clock').textContent = now.toLocaleTimeString('id-ID', { hour12: false });
    }
    setInterval(updateClock, 1000); updateClock();

    function formatRupiah(angka) {
        var number_string = angka.value.replace(/[^,\d]/g, '').toString(),
            split   = number_string.split(','), sisa = split[0].length % 3,
            rupiah  = split[0].substr(0, sisa), ribuan = split[0].substr(sisa).match(/\d{3}/gi);
        if (ribuan) { separator = sisa ? '.' : ''; rupiah += separator + ribuan.join('.'); }
        angka.value = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
    }

    // Tab Logic
    function switchTab(tabName) {
        document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
        document.querySelectorAll('.segment-btn').forEach(el => el.classList.remove('active'));
        
        document.getElementById('tab-' + tabName).classList.add('active');
        document.getElementById('btn-' + tabName).classList.add('active');
        
        if(tabName === 'biasa') {
            document.getElementById('mode_input_biasa').disabled = false;
            document.getElementById('mode_input_mutasi').disabled = true;
        } else {
            document.getElementById('mode_input_biasa').disabled = true;
            document.getElementById('mode_input_mutasi').disabled = false;
        }
    }

    <?php if(session()->getFlashdata('success')): ?>
        const isDark = document.documentElement.classList.contains('dark');
        Swal.fire({
            icon: 'success', title: 'Berhasil!', text: '<?= session()->getFlashdata('success') ?>',
            confirmButtonColor: '#38bdf8', background: isDark ? '#18181b' : '#ffffff', color: isDark ? '#f4f4f5' : '#09090b',
        });
    <?php endif; ?>

    function confirmDelete(url) {
        Swal.fire({
            title: 'Hapus Transaksi?', text: "Saldo harian akan dikalkulasi ulang secara otomatis.",
            icon: 'warning', showCancelButton: true, confirmButtonColor: '#ef4444', cancelButtonColor: '#cbd5e1', confirmButtonText: 'Ya, Hapus'
        }).then((result) => {
            if (result.isConfirmed) window.location.href = url;
        })
    }
</script>
<?= $this->endSection() ?>