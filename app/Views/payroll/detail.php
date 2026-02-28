<?= $this->extend('layout/template') ?>
<?= $this->section('content') ?>

<style>
    /* --- HEADER & TYPOGRAPHY --- */
    .page-header { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 25px; flex-wrap: wrap; gap: 20px;}
    .page-title h1 { font-size: 26px; font-weight: 800; color: var(--text-main); margin-bottom: 5px; letter-spacing: -1px; }
    .page-title p { color: var(--text-muted); font-size: 13px; }
    
    .btn-secondary { background: var(--bg-surface); color: var(--text-main); border: 1px solid var(--border-subtle); padding: 10px 20px; border-radius: 10px; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; transition: 0.3s; text-decoration: none; font-size: 13px;}
    .btn-secondary:hover { background: var(--border-subtle); }

    /* --- BENTO CARDS --- */
    .bento-card { background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: 20px; box-shadow: var(--shadow-card); padding: 25px; margin-bottom: 25px; position: relative; overflow: hidden;}
    
    /* --- SUMMARY PANEL --- */
    .summary-grid { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 20px; }
    .doc-info-label { font-size: 11px; font-weight: 800; text-transform: uppercase; color: var(--text-muted); letter-spacing: 0.5px; margin-bottom: 5px;}
    .doc-code { font-size: 28px; font-weight: 800; color: var(--text-main); font-family: monospace; letter-spacing: -1px; margin-bottom: 5px;}
    .doc-meta { display: flex; gap: 15px; font-size: 13px; font-weight: 600; color: var(--text-muted); }
    .doc-meta span { display: flex; align-items: center; gap: 5px; background: var(--bg-base); padding: 4px 10px; border-radius: 6px; border: 1px solid var(--border-subtle);}
    
    .total-box { text-align: right; }
    .total-amount { font-size: 36px; font-weight: 800; color: #10b981; font-family: monospace; letter-spacing: -1.5px; line-height: 1;}
    
    /* --- STATUS BADGES --- */
    .status-banner { padding: 12px 20px; border-radius: 12px; font-weight: 800; font-size: 14px; display: flex; align-items: center; justify-content: center; gap: 10px; text-transform: uppercase; letter-spacing: 1px;}
    .banner-draft { background: rgba(245, 158, 11, 0.1); color: #d97706; border: 1px dashed rgba(245, 158, 11, 0.3); }
    .banner-paid { background: rgba(16, 185, 129, 0.1); color: #10b981; border: 1px dashed rgba(16, 185, 129, 0.3); }

    /* --- TABLE STYLES --- */
    .table-responsive { width: 100%; overflow-x: auto; margin-top: 10px; }
    table { width: 100%; border-collapse: collapse; white-space: nowrap; }
    th { padding: 12px 15px; font-size: 11px; font-weight: 800; text-transform: uppercase; color: var(--text-muted); border-bottom: 1px solid var(--border-subtle); border-right: 1px solid var(--border-subtle);}
    td { padding: 15px; border-bottom: 1px solid var(--border-subtle); border-right: 1px solid var(--border-subtle); font-size: 13px; font-weight: 500; color: var(--text-main); vertical-align: middle;}
    th:last-child, td:last-child { border-right: none; }
    tr:hover td { background: rgba(0,0,0,0.01); }
    html.dark tr:hover td { background: rgba(255,255,255,0.02); }

    .th-green { background: rgba(16, 185, 129, 0.05); color: #059669; }
    .th-red { background: rgba(239, 68, 68, 0.05); color: #dc2626; }
    .th-main { background: var(--bg-base); }
    .th-accent { background: var(--accent-light); color: var(--accent-main); }
    
    .font-mono { font-family: 'Space Mono', monospace; font-weight: 700; font-size: 13px; }
    .text-plus { color: #10b981; }
    .text-min { color: #ef4444; }
    .text-thp { color: var(--accent-main); font-weight: 800; font-size: 15px;}

    .btn-print { background: var(--bg-base); border: 1px solid var(--border-subtle); color: var(--text-main); padding: 6px 12px; border-radius: 8px; cursor: pointer; transition: 0.2s; display: inline-flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 700; text-decoration: none;}
    .btn-print:hover { background: var(--accent-main); color: #fff; border-color: var(--accent-main);}

    /* --- DISBURSEMENT PANEL --- */
    .action-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 20px;}
    .action-box { padding: 25px; border-radius: 16px; display: flex; flex-direction: column; justify-content: space-between; position: relative; overflow: hidden; border: 1px solid transparent; transition: 0.3s;}
    .action-box:hover { transform: translateY(-3px); box-shadow: 0 10px 25px rgba(0,0,0,0.1);}
    
    .box-atm { background: linear-gradient(135deg, rgba(139, 92, 246, 0.1) 0%, rgba(109, 40, 217, 0.05) 100%); border-color: rgba(139, 92, 246, 0.2); }
    .box-cash { background: linear-gradient(135deg, rgba(16, 185, 129, 0.1) 0%, rgba(5, 150, 105, 0.05) 100%); border-color: rgba(16, 185, 129, 0.2); }
    
    .action-title { font-size: 16px; font-weight: 800; color: var(--text-main); margin-bottom: 5px; display: flex; align-items: center; gap: 8px;}
    .action-desc { font-size: 12px; color: var(--text-muted); font-weight: 500; margin-bottom: 20px; line-height: 1.4;}
    
    .btn-action { width: 100%; border: none; padding: 14px 0; border-radius: 10px; font-size: 14px; font-weight: 800; cursor: pointer; transition: 0.3s; display: flex; align-items: center; justify-content: center; gap: 8px; color: #fff;}
    .btn-atm { background: #8b5cf6; box-shadow: 0 4px 12px rgba(139, 92, 246, 0.3); }
    .btn-atm:hover { background: #7c3aed; }
    .btn-cash { background: #10b981; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3); }
    .btn-cash:hover { background: #059669; }
</style>

<div class="page-header">
    <div class="page-title">
        <h1>Rincian Ledger Penggajian</h1>
        <p>Detail kalkulasi Take Home Pay (THP) dan panel otorisasi pencairan dana.</p>
    </div>
    <a href="<?= base_url('/payroll') ?>" class="btn-secondary">
        <i class="ph ph-arrow-left"></i> Kembali ke Riwayat
    </a>
</div>

<div class="bento-card">
    <div class="summary-grid">
        <div>
            <div class="doc-info-label">Nomor Referensi Dokumen</div>
            <div class="doc-code"><?= esc($payroll['payroll_code']) ?></div>
            <div class="doc-meta">
                <span><i class="ph ph-calendar"></i> <?= date('d M Y', strtotime($payroll['period_start'])) ?> - <?= date('d M Y', strtotime($payroll['period_end'])) ?></span>
                <span><i class="ph ph-users"></i> <?= $payroll['total_employees'] ?> Karyawan</span>
                <span><i class="ph ph-briefcase"></i> Tipe: <?= esc($payroll['salary_type']) ?></span>
            </div>
        </div>
        
        <div class="total-box">
            <div class="doc-info-label">Total Dana Dibutuhkan</div>
            <div class="total-amount">Rp <?= number_format($payroll['total_amount'], 0, ',', '.') ?></div>
            
            <div style="margin-top: 15px;">
                <?php if($payroll['status'] == 'Draft'): ?>
                    <div class="status-banner banner-draft"><i class="ph ph-warning-circle" style="font-size: 20px;"></i> DRAFT (Menunggu Pencairan)</div>
                <?php else: ?>
                    <div class="status-banner banner-paid"><i class="ph ph-check-circle" style="font-size: 20px;"></i> DANA SUDAH DICAIRKAN</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="bento-card" style="padding: 0;">
    <div style="padding: 20px 25px; border-bottom: 1px solid var(--border-subtle); display: flex; align-items: center; gap: 10px; font-weight: 800; font-size: 15px; color: var(--text-main);">
        <i class="ph ph-list-numbers" style="color: var(--accent-main); font-size: 20px;"></i> Rincian Kalkulasi per Karyawan
    </div>
    
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th rowspan="2" class="th-main">Data Karyawan</th>
                    <th rowspan="2" class="th-main" style="text-align: center;">Kehadiran</th>
                    <th colspan="3" class="th-green" style="text-align: center;">Pendapatan Tambahan (+)</th>
                    <th colspan="2" class="th-red" style="text-align: center;">Potongan (-)</th>
                    <th rowspan="2" class="th-accent" style="text-align: right;">Gaji Bersih (THP)</th>
                    <th rowspan="2" class="th-main" style="text-align: center;">Tindakan</th>
                </tr>
                <tr>
                    <th class="th-green" style="text-align: right;">Tunj. Jabatan</th>
                    <th class="th-green" style="text-align: right;">Makan & Trnspt</th>
                    <th class="th-green" style="text-align: right;">U. Lembur</th>
                    <th class="th-red" style="text-align: right;">Denda Telat</th>
                    <th class="th-red" style="text-align: right;">BPJS</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($details as $row): ?>
                <tr>
                    <td>
                        <div style="font-weight: 800; font-size: 14px; margin-bottom: 4px; color: var(--text-main);"><?= esc($row['name']) ?></div>
                        <div style="font-size: 11px; color: var(--text-muted); font-family: monospace; margin-bottom: 6px;">
                            <i class="ph ph-identification-card"></i> <?= esc($row['employee_id']) ?>
                        </div>
                        <div style="display: inline-flex; align-items: center; gap: 5px; background: rgba(37, 99, 235, 0.05); border: 1px dashed rgba(37, 99, 235, 0.2); color: #2563eb; padding: 4px 8px; border-radius: 6px; font-size: 11px; font-weight: 700; font-family: 'Space Mono', monospace;">
                            Gapok: Rp <?= number_format($row['basic_salary'], 0, ',', '.') ?>
                        </div>
                    </td>
                    
                    <td style="text-align: center;">
                        <span style="background: var(--bg-base); border: 1px solid var(--border-subtle); padding: 4px 10px; border-radius: 6px; font-weight: 800; font-size: 12px;"><?= $row['total_present'] ?> HK</span>
                    </td>
                    
                    <td class="font-mono text-plus" style="text-align: right;">+<?= number_format($row['position_allowance'], 0, ',', '.') ?></td>
                    <td class="font-mono text-plus" style="text-align: right;">+<?= number_format($row['meal_allowance'] + $row['transport_allowance'], 0, ',', '.') ?></td>
                    <td class="font-mono text-plus" style="text-align: right;">+<?= number_format($row['overtime_pay'], 0, ',', '.') ?></td>
                    
                    <td class="font-mono text-min" style="text-align: right;">-<?= number_format($row['late_penalty'], 0, ',', '.') ?></td>
                    <td class="font-mono text-min" style="text-align: right;">-<?= number_format($row['bpjs_deduction'], 0, ',', '.') ?></td>
                    
                    <td class="font-mono text-thp" style="text-align: right; background: rgba(0,0,0,0.01);">
                        Rp <?= number_format($row['net_salary'], 0, ',', '.') ?>
                    </td>
                    
                    <td style="text-align: center;">
                        <a href="<?= base_url('/payroll/print_slip/' . $row['id']) ?>" target="_blank" class="btn-print" title="Cetak / Download Slip">
                            <i class="ph ph-printer" style="font-size: 16px;"></i> Slip
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php if($payroll['status'] == 'Draft'): ?>
    <div class="doc-info-label" style="margin-top: 35px; font-size: 13px;">Pilih Metode Pencairan Dana</div>
    <div class="action-grid">
        
        <div class="action-box box-atm">
            <div>
                <div class="action-title" style="color: #8b5cf6;"><i class="ph ph-bank" style="font-size: 24px;"></i> Transfer Bank (ATM)</div>
                <div class="action-desc">
                    Dana sebesar <b>Rp <?= number_format($payroll['total_amount'], 0, ',', '.') ?></b> akan dipotong otomatis dari Saldo Rekening Bank di Buku Kas Operasional.
                </div>
            </div>
            
            <form method="POST" action="<?= base_url('/payroll/push_to_finance') ?>" id="formATM">
                <?= csrf_field() ?>
                <input type="hidden" name="payroll_id" value="<?= $payroll['id'] ?>">
                <input type="hidden" name="metode" value="ATM">
                <input type="hidden" name="total_amount" value="<?= $payroll['total_amount'] ?>">
                <input type="hidden" name="payroll_code" value="<?= $payroll['payroll_code'] ?>">
                
                <button type="button" onclick="confirmDisburse('formATM', 'Transfer Bank (ATM)', '#8b5cf6')" class="btn-action btn-atm">
                    <i class="ph ph-paper-plane-right"></i> Proses Potong Saldo Bank
                </button>
            </form>
            <i class="ph ph-credit-card" style="position: absolute; right: -20px; top: 10px; font-size: 140px; opacity: 0.05; transform: rotate(-15deg); pointer-events: none;"></i>
        </div>

        <div class="action-box box-cash">
            <div>
                <div class="action-title" style="color: #10b981;"><i class="ph ph-money" style="font-size: 24px;"></i> Uang Tunai Fisik (Cash)</div>
                <div class="action-desc">
                    Dana sebesar <b>Rp <?= number_format($payroll['total_amount'], 0, ',', '.') ?></b> akan dipotong otomatis dari Dompet Tunai / Brankas di Buku Kas Operasional.
                </div>
            </div>
            
            <form method="POST" action="<?= base_url('/payroll/push_to_finance') ?>" id="formCash">
                <?= csrf_field() ?>
                <input type="hidden" name="payroll_id" value="<?= $payroll['id'] ?>">
                <input type="hidden" name="metode" value="Cash">
                <input type="hidden" name="total_amount" value="<?= $payroll['total_amount'] ?>">
                <input type="hidden" name="payroll_code" value="<?= $payroll['payroll_code'] ?>">
                
                <button type="button" onclick="confirmDisburse('formCash', 'Uang Tunai (Cash)', '#10b981')" class="btn-action btn-cash">
                    <i class="ph ph-paper-plane-right"></i> Proses Potong Brankas Tunai
                </button>
            </form>
            <i class="ph ph-wallet" style="position: absolute; right: -20px; top: 10px; font-size: 140px; opacity: 0.05; transform: rotate(-15deg); pointer-events: none;"></i>
        </div>

    </div>
<?php else: ?>
    <div style="background: rgba(16, 185, 129, 0.05); border: 1px dashed rgba(16, 185, 129, 0.3); padding: 30px; border-radius: 16px; text-align: center; margin-top: 35px;">
        <i class="ph ph-lock-key" style="font-size: 42px; color: #10b981; margin-bottom: 10px;"></i>
        <h3 style="margin: 0 0 5px 0; color: #059669; font-weight: 800; font-size: 18px;">Dokumen Penggajian Terkunci</h3>
        <p style="margin: 0; color: var(--text-muted); font-size: 13px; font-weight: 500;">Dana telah berhasil dicairkan dan tercatat secara permanen di Modul Buku Kas (Finance).</p>
    </div>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    <?php if(session()->getFlashdata('success')): ?>
        const isDark = document.documentElement.classList.contains('dark');
        Swal.fire({ icon: 'success', title: 'Pencairan Sukses!', text: '<?= session()->getFlashdata('success') ?>', confirmButtonColor: '#38bdf8', background: isDark ? '#18181b' : '#ffffff', color: isDark ? '#f4f4f5' : '#09090b' });
    <?php endif; ?>
    
    <?php if(session()->getFlashdata('error')): ?>
        Swal.fire({ icon: 'error', title: 'Perhatian Sistem', text: '<?= session()->getFlashdata('error') ?>', confirmButtonColor: '#ef4444' });
    <?php endif; ?>

    function confirmDisburse(formId, methodText, colorCode) {
        let total = "<?= number_format($payroll['total_amount'], 0, ',', '.') ?>";
        Swal.fire({
            title: `Cairkan via ${methodText}?`,
            html: `Sistem akan memotong kas sebesar <br><b style="font-size:18px; color:${colorCode};">Rp ${total}</b><br>di Modul Keuangan (Finance).<br><br><span style="color:#ef4444; font-size: 12px; font-weight:600;"><i class="ph ph-warning"></i> Dokumen ini akan dikunci permanen!</span>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: colorCode,
            cancelButtonColor: '#71717a',
            confirmButtonText: 'Ya, Cairkan Sekarang!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById(formId).submit();
            }
        });
    }
</script>
<?= $this->endSection() ?>