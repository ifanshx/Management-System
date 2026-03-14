<?= $this->extend('layout/template') ?>
<?= $this->section('content') ?>

<style>
    /* --- HEADER --- */
    .page-header { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 25px; flex-wrap: wrap; gap: 20px;}
    .page-title h1 { font-size: 26px; font-weight: 900; color: var(--text-main); margin: 0 0 5px 0; letter-spacing: -0.5px; display: flex; align-items: center; gap: 10px; }
    .page-title p { font-size: 13px; color: var(--text-muted); margin: 0;}
    .title-icon { width: 46px; height: 46px; border-radius: 12px; background: linear-gradient(135deg, rgba(16, 185, 129, 0.15), rgba(5, 150, 105, 0.05)); color: #10b981; display: flex; align-items: center; justify-content: center; font-size: 24px; border: 1px solid rgba(16, 185, 129, 0.2);}

    /* --- STATS BENTO GRID --- */
    .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 20px; }
    .stat-card { background: var(--bg-surface); border: 1px solid var(--border-subtle); padding: 20px; border-radius: 20px; display: flex; align-items: center; gap: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.02); transition: 0.3s; }
    .stat-card:hover { transform: translateY(-3px); box-shadow: 0 10px 20px rgba(0,0,0,0.05); }
    .stat-icon { width: 48px; height: 48px; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 24px; }
    
    .font-mono { font-family: 'Space Mono', monospace; font-weight: 900; font-size: 18px; margin-top: 4px; letter-spacing: -0.5px;}
    .text-green { color: #10b981; }
    .text-red { color: #ef4444; }

    /* --- LAYOUT UTAMA --- */
    .bento-card { background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: 24px; padding: 25px; box-shadow: 0 10px 30px -10px rgba(0,0,0,0.05); overflow: hidden;}
    
    /* --- TABLE CONTROLS --- */
    .table-controls { padding-bottom: 20px; border-bottom: 1px dashed var(--border-subtle); margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;}
    
    .form-control-date { background: var(--bg-base); border: 1px solid var(--border-subtle); color: var(--text-main); padding: 10px 15px; border-radius: 12px; font-family: 'Space Mono', monospace; font-size: 13px; font-weight: 700; outline: none; transition: 0.3s; cursor: pointer;}
    .form-control-date:focus { border-color: #10b981; }
    
    .btn-filter { background: var(--bg-base); color: var(--text-main); border: 1px solid var(--border-subtle); padding: 10px 20px; border-radius: 12px; font-weight: 800; font-size: 13px; cursor: pointer; transition: 0.3s; display: inline-flex; align-items: center; gap: 6px;}
    .btn-filter:hover { background: var(--border-subtle); }
    
    .btn-create { background: linear-gradient(135deg, #10b981, #059669); color: #fff; border: none; padding: 12px 24px; border-radius: 14px; font-weight: 900; font-size: 14px; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 8px 20px -6px rgba(16, 185, 129, 0.5); transition: 0.3s; text-decoration: none;}
    .btn-create:hover { transform: translateY(-3px); box-shadow: 0 12px 25px -6px rgba(16, 185, 129, 0.6);}

    /* --- TABEL ANALITIK --- */
    table { width: 100%; border-collapse: collapse; white-space: nowrap; }
    th { text-align: center; padding: 16px 20px; font-size: 11px; font-weight: 900; text-transform: uppercase; color: var(--text-muted); background: var(--bg-base); border-bottom: 2px solid var(--border-subtle); border-right: 1px solid var(--border-subtle); letter-spacing: 0.5px;}
    td { text-align: center; padding: 16px 20px; border-bottom: 1px dashed var(--border-subtle); border-right: 1px solid var(--border-subtle); color: var(--text-main); font-size: 13px; font-weight: 600; vertical-align: middle; transition: 0.2s;}
    
    th:first-child, td:first-child { text-align: left; }
    th:nth-child(2), td:nth-child(2) { text-align: left; }
    th:last-child, td:last-child { border-right: none; }
    tr:last-child td { border-bottom: none; }
    
    tr:hover td { background: rgba(16, 185, 129, 0.02); }
    html.dark tr:hover td { background: rgba(255,255,255,0.02); }

    .badge-metode { padding: 4px 10px; border-radius: 6px; font-size: 10px; font-weight: 900; display: inline-flex; align-items: center; gap: 4px; text-transform: uppercase; letter-spacing: 0.5px;}
    .badge-metode.cash { background: rgba(249, 115, 22, 0.1); color: #f97316; border: 1px solid rgba(249, 115, 22, 0.2); }
    .badge-metode.atm { background: rgba(139, 92, 246, 0.1); color: #8b5cf6; border: 1px solid rgba(139, 92, 246, 0.2); }

    /* =========================================================
       MODAL STYLES (PREMIUM RE-DESIGN)
       ========================================================= */
    .modal-overlay { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.6); backdrop-filter: blur(6px); z-index: 1000; display: none; justify-content: center; align-items: center; opacity: 0; transition: opacity 0.3s;}
    .modal-overlay.active { display: flex; opacity: 1; }
    
    .modal-box { background: var(--bg-surface); border-radius: 28px; width: 100%; max-width: 520px; padding: 40px; transform: scale(0.95) translateY(20px); transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1); box-shadow: 0 30px 60px -15px rgba(0,0,0,0.4); border: 1px solid var(--border-subtle); max-height: 90vh; overflow-y: auto; border-top: 8px solid #10b981;}
    .modal-overlay.active .modal-box { transform: scale(1) translateY(0); }
    
    .modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
    .modal-title { font-size: 22px; font-weight: 900; color: var(--text-main); display: flex; align-items: center; gap: 12px; margin: 0;}
    .btn-close { background: var(--bg-base); border: 1px solid var(--border-subtle); width: 38px; height: 38px; border-radius: 50%; font-size: 16px; color: var(--text-muted); cursor: pointer; display: flex; align-items: center; justify-content: center; transition: 0.3s;}
    .btn-close:hover { background: #ef4444; color: #fff; border-color: #ef4444; transform: rotate(90deg);}

    /* Segment Control (iOS Pill Style) */
    .segment-control { display: flex; background: var(--bg-base); border: 1px solid var(--border-subtle); border-radius: 16px; padding: 6px; margin-bottom: 30px; box-shadow: inset 0 2px 4px rgba(0,0,0,0.02);}
    .segment-btn { flex: 1; text-align: center; padding: 12px; font-size: 13px; font-weight: 800; color: var(--text-muted); border-radius: 12px; cursor: pointer; transition: all 0.3s; }
    .segment-btn.active { background: var(--bg-surface); color: var(--text-main); box-shadow: 0 4px 12px rgba(0,0,0,0.05); }

    /* Form Elements */
    .form-group { margin-bottom: 20px; }
    .form-group label { display: block; font-size: 11px; font-weight: 900; color: var(--text-muted); margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px;}

    /* Input Text & Textarea */
    .input-wrapper { display: flex; align-items: stretch; background: var(--bg-base); border: 1px solid var(--border-subtle); border-radius: 16px; overflow: hidden; transition: all 0.2s ease; }
    .input-wrapper:focus-within { border-color: #10b981; box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.15); background: var(--bg-surface);}
    .input-wrapper input, .input-wrapper textarea { flex: 1; background: transparent; border: none; color: var(--text-main); padding: 16px 20px; font-size: 14px; font-weight: 600; outline: none; resize: none;}
    
    /* Input Uang (Lebih Menonjol) */
    .money-wrapper { border-color: #10b981; box-shadow: 0 4px 15px rgba(16, 185, 129, 0.1); background: var(--bg-surface);}
    .prefix { background: rgba(16, 185, 129, 0.1); color: #10b981; font-size: 16px; font-weight: 900; padding: 0 20px; display: flex; align-items: center; border-right: 1px solid rgba(16, 185, 129, 0.2); }
    .money-wrapper input { font-size: 24px; font-family: 'Space Mono', monospace; font-weight: 900; color: #10b981; padding: 16px 20px;}

    /* Custom File Upload */
    .file-upload-wrapper { background: var(--bg-base); border: 2px dashed var(--border-subtle); border-radius: 16px; padding: 12px; display: flex; align-items: center; transition: 0.3s; cursor: pointer;}
    .file-upload-wrapper:hover { border-color: #10b981; background: var(--bg-surface); }
    .file-upload-wrapper input[type="file"] { width: 100%; font-size: 13px; font-weight: 600; color: var(--text-muted); cursor: pointer; outline: none;}
    .file-upload-wrapper input[type="file"]::file-selector-button { background: rgba(16, 185, 129, 0.1); color: #10b981; border: none; padding: 10px 16px; border-radius: 10px; font-weight: 800; cursor: pointer; transition: 0.3s; margin-right: 15px;}
    .file-upload-wrapper input[type="file"]::file-selector-button:hover { background: #10b981; color: #fff; }

    /* Radio Custom Selection */
    .radio-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
    .radio-label { position: relative; cursor: pointer; display: block;}
    .radio-label input { position: absolute; opacity: 0; }
    
    .radio-box { display: flex; align-items: center; justify-content: center; gap: 10px; padding: 16px; background: var(--bg-base); border: 2px solid var(--border-subtle); border-radius: 16px; font-size: 14px; font-weight: 800; color: var(--text-muted); transition: all 0.3s; }
    .radio-box i { font-size: 20px; }
    
    .radio-label input:checked + .radio-box.in { background: rgba(16, 185, 129, 0.08); border-color: #10b981; color: #10b981; box-shadow: 0 4px 15px rgba(16, 185, 129, 0.15);}
    .radio-label input:checked + .radio-box.out { background: rgba(239, 68, 68, 0.08); border-color: #ef4444; color: #ef4444; box-shadow: 0 4px 15px rgba(239, 68, 68, 0.15);}
    .radio-label input:checked + .radio-box.cash { background: rgba(249, 115, 22, 0.08); border-color: #f97316; color: #f97316; box-shadow: 0 4px 15px rgba(249, 115, 22, 0.15);}
    .radio-label input:checked + .radio-box.atm { background: rgba(139, 92, 246, 0.08); border-color: #8b5cf6; color: #8b5cf6; box-shadow: 0 4px 15px rgba(139, 92, 246, 0.15);}
    
    /* Mutasi Khusus Style */
    .mutasi-box { padding: 20px 25px; justify-content: space-between; }
    .radio-label input:checked + .radio-box.mutasi-box { background: var(--bg-surface); border-color: #3b82f6; box-shadow: 0 8px 25px rgba(59, 130, 246, 0.15); }

    .tab-content { display: none; }
    .tab-content.active { display: block; animation: slideFadeUp 0.3s; }

    .btn-submit-modal { width: 100%; background: linear-gradient(135deg, #10b981, #059669); color: #fff; padding: 20px; border: none; border-radius: 16px; font-weight: 900; font-size: 16px; cursor: pointer; transition: 0.3s; display: flex; justify-content: center; align-items: center; gap: 10px; box-shadow: 0 8px 25px -5px rgba(16, 185, 129, 0.5); margin-top: 15px;}
    .btn-submit-modal:hover { transform: translateY(-3px); box-shadow: 0 15px 30px -5px rgba(16, 185, 129, 0.6);}
</style>

<div class="page-header">
    <div class="page-title">
        <div class="title-icon"><i class="ph-fill ph-wallet"></i></div>
        <div>
            <h1>Kas Operasional Harian</h1>
            <p>Catat pengeluaran tunai dan sinkronisasi otomatis dengan Jurnal Akuntansi.</p>
        </div>
    </div>
    
    <div style="text-align: right; background: var(--bg-surface); padding: 12px 24px; border-radius: 16px; border: 1px solid var(--border-subtle); box-shadow: 0 4px 15px rgba(0,0,0,0.02);">
        <div style="font-size: 10px; color: var(--text-muted); font-weight: 900; text-transform: uppercase; letter-spacing: 0.5px;">Jam Operasional</div>
        <div id="live-clock" style="font-size: 24px; font-weight: 900; font-family: 'Space Mono', monospace; color: #10b981; letter-spacing: -1px; margin-top: 2px;"><?= date('H:i:s') ?></div>
    </div>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon" style="background: rgba(100, 116, 139, 0.1); color: #64748b;"><i class="ph-fill ph-clock-counter-clockwise"></i></div>
        <div>
            <div style="font-size: 11px; color: var(--text-muted); font-weight: 900; letter-spacing: 0.5px;">SALDO AWAL (HARI INI)</div>
            <div class="font-mono" style="color: var(--text-main);">Rp <?= number_format($saldo_awal, 0, ',', '.') ?></div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background: rgba(16, 185, 129, 0.1); color: #10b981;"><i class="ph-bold ph-trend-up"></i></div>
        <div>
            <div style="font-size: 11px; color: var(--text-muted); font-weight: 900; letter-spacing: 0.5px;">PEMASUKAN GLOBAL</div>
            <div class="font-mono text-green">+ Rp <?= number_format($masuk_hari_ini, 0, ',', '.') ?></div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background: rgba(239, 68, 68, 0.1); color: #ef4444;"><i class="ph-bold ph-trend-down"></i></div>
        <div>
            <div style="font-size: 11px; color: var(--text-muted); font-weight: 900; letter-spacing: 0.5px;">PENGELUARAN GLOBAL</div>
            <div class="font-mono text-red">- Rp <?= number_format($keluar_hari_ini, 0, ',', '.') ?></div>
        </div>
    </div>
    <div class="stat-card" style="background: linear-gradient(135deg, rgba(59, 130, 246, 0.1), rgba(37, 99, 235, 0.05)); border-color: rgba(59, 130, 246, 0.3);">
        <div class="stat-icon" style="background: #3b82f6; color: #fff; box-shadow: 0 4px 10px rgba(59, 130, 246, 0.4);"><i class="ph-fill ph-vault"></i></div>
        <div>
            <div style="font-size: 11px; color: #3b82f6; font-weight: 900; letter-spacing: 0.5px;">TOTAL SALDO AKHIR</div>
            <div class="font-mono" style="font-size: 24px; color: #2563eb;">Rp <?= number_format($saldo_akhir, 0, ',', '.') ?></div>
        </div>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 25px;">
    <div style="background: var(--bg-surface); padding: 20px 30px; border-radius: 20px; border: 1px solid rgba(249, 115, 22, 0.2); border-left: 6px solid #f97316; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 4px 15px rgba(0,0,0,0.02);">
        <div>
            <div style="font-size: 13px; font-weight: 900; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px;">Isi Dompet (Cash Fisik)</div>
            <div class="font-mono" style="font-size: 26px; color: var(--text-main);">Rp <?= number_format($sisa_cash, 0, ',', '.') ?></div>
        </div>
        <i class="ph-fill ph-money" style="font-size: 50px; color: #f97316; opacity: 0.15;"></i>
    </div>
    <div style="background: var(--bg-surface); padding: 20px 30px; border-radius: 20px; border: 1px solid rgba(139, 92, 246, 0.2); border-left: 6px solid #8b5cf6; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 4px 15px rgba(0,0,0,0.02);">
        <div>
            <div style="font-size: 13px; font-weight: 900; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px;">Rekening Bank (ATM)</div>
            <div class="font-mono" style="font-size: 26px; color: var(--text-main);">Rp <?= number_format($sisa_atm, 0, ',', '.') ?></div>
        </div>
        <i class="ph-fill ph-credit-card" style="font-size: 50px; color: #8b5cf6; opacity: 0.15;"></i>
    </div>
</div>

<div class="bento-card">
    <div class="table-controls">
        <form action="" method="get" style="display: flex; gap: 10px; align-items: center;">
            <div style="font-weight: 900; font-size: 12px; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px;">Pilih Tanggal:</div>
            <input type="date" name="tgl" class="form-control-date" value="<?= esc($tgl_filter) ?>" onchange="this.form.submit()">
            <button type="submit" class="btn-filter"><i class="ph-bold ph-magnifying-glass"></i> Filter</button>
        </form>
        
        <button onclick="openModal('modalKas')" class="btn-create">
            <i class="ph-bold ph-plus-circle" style="font-size: 18px;"></i> Catat Transaksi Baru
        </button>
    </div>

    <div style="overflow-x: auto;">
        <table>
            <thead>
                <tr>
                    <th>Waktu & ID</th>
                    <th>Keterangan Transaksi</th>
                    <th style="text-align: center;">Metode</th>
                    <th style="text-align: right;">Nominal Mutasi</th>
                    <th style="text-align: right;">Saldo Berjalan</th>
                    <th style="text-align: center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <tr style="background: rgba(0,0,0,0.02);">
                    <td style="border-left: 4px solid var(--text-muted); font-weight: 900; font-size: 12px;">SALDO AWAL</td>
                    <td>
                        <span style="background: var(--border-subtle); color: var(--text-main); padding: 4px 10px; border-radius: 6px; font-size: 10px; font-weight: 900; letter-spacing: 0.5px;">
                            SISTEM KALKULASI
                        </span>
                    </td>
                    <td style="text-align: center; color: var(--text-muted);">-</td>
                    <td style="text-align: right; color: var(--text-muted);">-</td>
                    <td style="text-align: right; font-family: 'Space Mono', monospace; font-size: 16px; font-weight: 900;">
                        Rp <?= number_format($saldo_awal, 0, ',', '.') ?>
                    </td>
                    <td></td>
                </tr>
                
                <?php if(empty($transactions)): ?>
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 60px 20px;">
                            <i class="ph-fill ph-wallet" style="font-size: 48px; color: var(--border-subtle); display: block; margin-bottom: 10px;"></i>
                            <div style="color: var(--text-muted); font-weight: 600;">Belum ada catatan pengeluaran/pemasukan di tanggal ini.</div>
                        </td>
                    </tr>
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
                            <div style="font-weight: 900; font-size: 13px; margin-bottom: 4px;">
                                <?= date('H:i', strtotime($trx['created_at'])) ?> WIB
                            </div>
                            <div style="font-size: 11px; color: var(--text-muted); font-family: 'Space Mono', monospace; font-weight: 700;">
                                <?= esc($trx['transaction_code']) ?>
                            </div>
                        </td>
                        <td style="white-space: normal; line-height: 1.4; max-width: 250px;">
                            <div style="font-weight: 800; color: var(--text-main); margin-bottom: 6px;">
                                <?= esc($trx['description']) ?>
                            </div>
                            <div style="font-size: 11px; color: var(--text-muted); font-weight: 700; display: flex; align-items: center; gap: 4px;">
                                <i class="ph-fill ph-user-circle"></i> <?= esc($trx['pic_name']) ?>
                            </div>
                        </td>
                        <td style="text-align: center;">
                            <span class="badge-metode <?= strtolower($trx['metode']) ?>">
                                <?= esc($trx['metode']) ?>
                            </span>
                        </td>
                        <td style="text-align: right;" class="font-mono <?= $color ?>">
                            <?= $sign ?> Rp <?= number_format($trx['amount'], 0, ',', '.') ?>
                        </td>
                        <td style="text-align: right; color: var(--text-main);" class="font-mono">
                            Rp <?= number_format($running, 0, ',', '.') ?>
                        </td>
                        <td style="text-align: center;">
                            <div style="display: flex; justify-content: center; gap: 12px; align-items: center;">
                                <?php if($trx['receipt_file']): ?>
                                    <a href="<?= base_url('uploads/receipts/' . $trx['receipt_file']) ?>" target="_blank" style="color: #3b82f6; font-size: 20px; transition: 0.2s;" title="Lihat Bukti Foto" onmouseover="this.style.transform='scale(1.2)'" onmouseout="this.style.transform='scale(1)'">
                                        <i class="ph-bold ph-image"></i>
                                    </a>
                                <?php else: ?>
                                    <span style="color: var(--border-subtle); font-size: 20px;" title="Tanpa Struk">
                                        <i class="ph-bold ph-prohibit"></i>
                                    </span>
                                <?php endif; ?>
                                <a href="javascript:void(0)" onclick="confirmDelete('<?= base_url('/finance/cash_delete/'.$trx['id']) ?>')" style="color: #ef4444; font-size: 20px; transition: 0.2s;" title="Hapus" onmouseover="this.style.transform='scale(1.2)'" onmouseout="this.style.transform='scale(1)'">
                                    <i class="ph-bold ph-trash"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal-overlay" id="modalKas">
    <div class="modal-box">
        <div class="modal-header">
            <div class="modal-title">
                <div style="background: rgba(16, 185, 129, 0.1); width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; color: #10b981;">
                    <i class="ph-fill ph-receipt"></i>
                </div>
                Catat Transaksi
            </div>
            <button class="btn-close" onclick="closeModal('modalKas')" type="button"><i class="ph-bold ph-x"></i></button>
        </div>
        
        <div class="segment-control">
            <div class="segment-btn active" id="btn-biasa" onclick="switchFormTab('biasa')">Buku Kas Harian</div>
            <div class="segment-btn" id="btn-mutasi" onclick="switchFormTab('mutasi')">Pindah Dana Internal</div>
        </div>

        <form id="formKas" action="<?= base_url('/finance/cash_store') ?>" method="POST" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <input type="hidden" name="mode_input" id="mode_input_val" value="transaksi">
            
            <div id="tab-biasa" class="tab-content active">
                <div class="form-group">
                    <label>Sifat Transaksi</label>
                    <div class="radio-grid">
                        <label class="radio-label">
                            <input type="radio" name="type" value="Cash In">
                            <div class="radio-box in"><i class="ph-bold ph-arrow-down-left"></i> Pemasukan</div>
                        </label>
                        <label class="radio-label">
                            <input type="radio" name="type" value="Cash Out" checked>
                            <div class="radio-box out"><i class="ph-bold ph-arrow-up-right"></i> Pengeluaran</div>
                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label>Lokasi Penyimpanan Dana</label>
                    <div class="radio-grid">
                        <label class="radio-label">
                            <input type="radio" name="metode" value="Cash" checked>
                            <div class="radio-box cash"><i class="ph-fill ph-money"></i> Laci Kasir</div>
                        </label>
                        <label class="radio-label">
                            <input type="radio" name="metode" value="ATM">
                            <div class="radio-box atm"><i class="ph-fill ph-credit-card"></i> Bank (ATM)</div>
                        </label>
                    </div>
                </div>
            </div>

            <div id="tab-mutasi" class="tab-content">
                <div class="form-group">
                    <label>Arah Perpindahan Dana</label>
                    <div style="display: flex; flex-direction: column; gap: 15px;">
                        <label class="radio-label">
                            <input type="radio" name="arah_mutasi" value="atm_to_cash" checked disabled class="mutasi-input">
                            <div class="radio-box mutasi-box">
                                <div style="display: flex; align-items: center; gap: 12px; font-size: 16px;">
                                    <span style="color: #8b5cf6; font-weight: 900;"><i class="ph-fill ph-credit-card"></i> Bank</span>
                                    <i class="ph-bold ph-arrow-right" style="color: var(--text-muted);"></i>
                                    <span style="color: #f97316; font-weight: 900;"><i class="ph-fill ph-money"></i> Laci</span>
                                </div>
                                <span style="font-size:10px; background:var(--bg-base); padding:6px 10px; border-radius:8px; border: 1px solid var(--border-subtle); color: var(--text-main); font-weight: 900;">TARIK TUNAI</span>
                            </div>
                        </label>
                        <label class="radio-label">
                            <input type="radio" name="arah_mutasi" value="cash_to_atm" disabled class="mutasi-input">
                            <div class="radio-box mutasi-box">
                                <div style="display: flex; align-items: center; gap: 12px; font-size: 16px;">
                                    <span style="color: #f97316; font-weight: 900;"><i class="ph-fill ph-money"></i> Laci</span>
                                    <i class="ph-bold ph-arrow-right" style="color: var(--text-muted);"></i>
                                    <span style="color: #8b5cf6; font-weight: 900;"><i class="ph-fill ph-credit-card"></i> Bank</span>
                                </div>
                                <span style="font-size:10px; background:var(--bg-base); padding:6px 10px; border-radius:8px; border: 1px solid var(--border-subtle); color: var(--text-main); font-weight: 900;">SETOR TUNAI</span>
                            </div>
                        </label>
                    </div>
                </div>
            </div>

            <div class="form-group" style="margin-top: 25px;">
                <label style="color: #10b981;">Besaran Nominal Transaksi</label>
                <div class="input-wrapper money-wrapper">
                    <div class="prefix">Rp</div>
                    <input type="text" name="amount" placeholder="0" onkeyup="formatRupiah(this)" required autocomplete="off">
                </div>
            </div>

            <div class="form-group">
                <label>Keterangan / Tujuan Transaksi</label>
                <div class="input-wrapper">
                    <textarea name="description" rows="2" placeholder="Cth: Beli bensin mobil operasional pabrik..." required></textarea>
                </div>
            </div>

            <div class="form-group">
                <label>Unggah Struk / Bukti Bayar (Opsional)</label>
                <div class="file-upload-wrapper">
                    <input type="file" name="receipt_file" accept="image/*">
                </div>
            </div>

            <button type="submit" id="btnSubmitKas" class="btn-submit-modal">
                <i class="ph-bold ph-paper-plane-tilt" style="font-size: 22px;"></i> <span>Simpan & Bukukan Transaksi</span>
            </button>
        </form>
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
        if (ribuan) { let separator = sisa ? '.' : ''; rupiah += separator + ribuan.join('.'); }
        angka.value = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
    }

    function openModal(modalId) { document.getElementById(modalId).classList.add('active'); }
    function closeModal(modalId) { document.getElementById(modalId).classList.remove('active'); }
    window.onclick = function(e) { if (e.target.classList.contains('modal-overlay')) closeModal('modalKas'); }

    function switchFormTab(tabName) {
        document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
        document.querySelectorAll('.segment-btn').forEach(el => el.classList.remove('active'));
        
        document.getElementById('tab-' + tabName).classList.add('active');
        document.getElementById('btn-' + tabName).classList.add('active');
        document.getElementById('mode_input_val').value = tabName;

        if(tabName === 'biasa') {
            document.querySelectorAll('.mutasi-input').forEach(el => el.disabled = true);
            document.querySelectorAll('input[name="type"]').forEach(el => el.disabled = false);
            document.querySelectorAll('input[name="metode"]').forEach(el => el.disabled = false);
        } else {
            document.querySelectorAll('.mutasi-input').forEach(el => el.disabled = false);
            document.querySelectorAll('input[name="type"]').forEach(el => el.disabled = true);
            document.querySelectorAll('input[name="metode"]').forEach(el => el.disabled = true);
        }
    }

    // AJAX Form Submission yang terkoneksi ke window.showGlobalToast (dari template.php)
    document.getElementById('formKas').addEventListener('submit', function(e) {
        e.preventDefault(); 
        
        const form = this;
        const btn = document.getElementById('btnSubmitKas');
        const btnText = btn.querySelector('span');
        const btnIcon = btn.querySelector('i');
        
        btn.disabled = true;
        btnText.innerText = "Mengunci Jurnal...";
        btnIcon.className = "ph-bold ph-spinner-gap ph-spin";

        fetch(form.action, {
            method: 'POST',
            body: new FormData(form),
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(data => {
            if(data.status === 'success') {
                if(typeof window.showGlobalToast === 'function') window.showGlobalToast(data.message);
                form.reset();
                closeModal('modalKas');
                setTimeout(() => { window.location.reload(); }, 1200);
            } else {
                if(typeof window.showGlobalToast === 'function') window.showGlobalToast(data.message, true);
                btn.disabled = false;
                btnText.innerText = "Simpan & Bukukan Transaksi";
                btnIcon.className = "ph-bold ph-paper-plane-tilt";
            }
        })
        .catch(err => {
            if(typeof window.showGlobalToast === 'function') window.showGlobalToast("Koneksi Server Gagal", true);
            btn.disabled = false;
            btnText.innerText = "Simpan & Bukukan Transaksi";
            btnIcon.className = "ph-bold ph-paper-plane-tilt";
        });
    });

    function confirmDelete(url) {
        const isDark = document.documentElement.classList.contains('dark');
        Swal.fire({
            title: 'Hapus Transaksi?', 
            text: "Data yang dihapus tidak bisa dikembalikan. Saldo akan otomatis disesuaikan.",
            icon: 'warning', 
            showCancelButton: true, 
            confirmButtonColor: '#ef4444', 
            cancelButtonColor: isDark ? '#3f3f46' : '#cbd5e1', 
            confirmButtonText: 'Ya, Hapus Data',
            background: isDark ? '#18181b' : '#ffffff', 
            color: isDark ? '#f4f4f5' : '#09090b',
        }).then((result) => {
            if (result.isConfirmed) window.location.href = url;
        })
    }
</script>

<?= $this->endSection() ?>