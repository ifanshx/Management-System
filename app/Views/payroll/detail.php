<?= $this->extend('layout/template') ?>
<?= $this->section('content') ?>

<?php
$totalCash = 0;
$totalTransfer = 0;

// Kalkulasi untuk TAMPILAN VIEW. Semua karyawan (termasuk anak buah) dijumlahkan
// Karena di Controller mereka juga dijumlahkan agar seimbang.
foreach($details as $d) {
    $pm = $d['payment_method'] ?? 'Cash';
    if (($payroll['employee_status'] ?? '') === 'Borongan') {
        $pm = 'Transfer';
    }
    
    if ($pm === 'Transfer') {
        $totalTransfer += (float) ($d['net_salary'] ?? 0);
    } else {
        $totalCash += (float) ($d['net_salary'] ?? 0);
    }
}
?>

<style>
    .page-header { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 20px; flex-wrap: wrap; gap: 15px;}
    .page-title h1 { font-size: 24px; font-weight: 900; color: var(--text-main); margin-bottom: 4px; letter-spacing: -0.5px; }
    .page-title p { color: var(--text-muted); font-size: 13px; margin: 0; }
    
    .btn-secondary { background: var(--bg-surface); color: var(--text-main); border: 1px solid var(--border-subtle); padding: 10px 18px; border-radius: 12px; font-weight: 800; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; transition: 0.3s; text-decoration: none; font-size: 13px; box-shadow: 0 2px 5px rgba(0,0,0,0.02);}
    .btn-secondary:hover { background: var(--border-subtle); transform: translateY(-2px); }

    .bento-card { background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: 20px; box-shadow: 0 10px 30px -10px rgba(0,0,0,0.05); padding: 25px; margin-bottom: 25px; position: relative; overflow: hidden;}
    
    .summary-grid { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 20px; }
    .doc-info-label { font-size: 11px; font-weight: 900; text-transform: uppercase; color: var(--text-muted); letter-spacing: 0.8px; margin-bottom: 6px;}
    .doc-code { font-size: 26px; font-weight: 900; color: var(--text-main); font-family: monospace; letter-spacing: -1px; margin-bottom: 8px;}
    .doc-meta { display: flex; gap: 12px; font-size: 12px; font-weight: 700; color: var(--text-muted); }
    .doc-meta span { display: flex; align-items: center; gap: 6px; background: var(--bg-base); padding: 6px 12px; border-radius: 8px; border: 1px solid var(--border-subtle);}
    
    .total-box { text-align: right; }
    .total-amount { font-size: 34px; font-weight: 900; color: #10b981; font-family: monospace; letter-spacing: -1px; line-height: 1;}
    
    .status-banner { padding: 10px 20px; border-radius: 12px; font-weight: 900; font-size: 13px; display: inline-flex; align-items: center; justify-content: center; gap: 8px; text-transform: uppercase; letter-spacing: 0.5px;}
    .banner-draft { background: rgba(245, 158, 11, 0.1); color: #d97706; border: 1px dashed rgba(245, 158, 11, 0.3); }
    .banner-paid { background: rgba(16, 185, 129, 0.1); color: #10b981; border: 1px dashed rgba(16, 185, 129, 0.3); }

    .table-responsive { width: 100%; overflow-x: auto; margin-top: 10px; }
    table { width: 100%; border-collapse: collapse; white-space: nowrap; }
    th { padding: 14px 16px; font-size: 11px; font-weight: 900; text-transform: uppercase; color: var(--text-muted); border-bottom: 2px solid var(--border-subtle); border-right: 1px solid var(--border-subtle);}
    td { padding: 16px 18px; border-bottom: 1px dashed var(--border-subtle); border-right: 1px solid var(--border-subtle); font-size: 13px; font-weight: 600; color: var(--text-main); vertical-align: middle;}
    th:last-child, td:last-child { border-right: none; }
    tr:hover td { background: rgba(0,0,0,0.01); }

    .th-green { background: rgba(16, 185, 129, 0.05); color: #059669; }
    .th-red { background: rgba(239, 68, 68, 0.05); color: #dc2626; }
    .th-main { background: var(--bg-base); }
    .th-accent { background: var(--accent-light); color: var(--accent-main); }
    
    .font-mono { font-family: 'Space Mono', monospace; font-weight: 800; font-size: 13px; }
    .text-plus { color: #10b981; }
    .text-min { color: #ef4444; }
    .text-thp { color: var(--accent-main); font-weight: 900; font-size: 16px;}

    .btn-print { background: var(--bg-base); border: 1px solid var(--border-subtle); color: var(--text-main); padding: 8px 16px; border-radius: 10px; cursor: pointer; transition: 0.2s; display: inline-flex; align-items: center; gap: 8px; font-size: 13px; font-weight: 900; text-decoration: none;}
    .btn-print:hover { background: var(--accent-main); color: #fff; border-color: var(--accent-main); box-shadow: 0 4px 15px rgba(37, 99, 235, 0.3);}

    .split-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-top: 15px;}
    @media (max-width: 768px) { .split-grid { grid-template-columns: 1fr; } }
    .split-box { padding: 25px; border-radius: 20px; border: 1px solid var(--border-subtle); background: var(--bg-base); display: flex; align-items: center; gap: 18px; transition: 0.3s;}
    .split-box:hover { transform: translateY(-3px); box-shadow: 0 10px 25px rgba(0,0,0,0.05); border-color: #2563eb;}
    .split-icon { width: 56px; height: 56px; border-radius: 16px; display: flex; align-items: center; justify-content: center; font-size: 28px;}
    .split-title { font-size: 13px; font-weight: 900; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px;}
    .split-val { font-size: 28px; font-weight: 900; font-family: 'Space Mono', monospace; color: var(--text-main); letter-spacing: -1px;}
    
    .btn-disburse { width: 100%; border: none; padding: 20px; border-radius: 16px; font-size: 16px; font-weight: 900; cursor: pointer; transition: 0.3s; display: flex; align-items: center; justify-content: center; gap: 10px; color: #fff; background: linear-gradient(135deg, #10b981, #059669); box-shadow: 0 10px 25px -5px rgba(16, 185, 129, 0.4); margin-top: 30px;}
    .btn-disburse:hover { transform: translateY(-4px); box-shadow: 0 15px 35px -5px rgba(16, 185, 129, 0.5); }
    
    .detail-breakdown { font-size: 10px; color: var(--text-muted); margin-top: 6px; font-family: 'Plus Jakarta Sans', sans-serif; font-weight: 600; line-height: 1.5;}
    .team-badge { display: inline-block; font-size: 9px; background: var(--accent-light); color: var(--accent-main); padding: 3px 6px; border-radius: 4px; font-weight: 800; border: 1px solid rgba(37,99,235,0.2); margin-top: 6px;}
</style>

<div class="page-header">
    <div class="page-title">
        <h1>Rincian Ledger Penggajian</h1>
        <p>Detail kalkulasi Take Home Pay (THP) dan panel otorisasi pencairan dana ke Accounting.</p>
    </div>
    <a href="<?= base_url('/payroll') ?>" class="btn-secondary">
        <i class="ph-bold ph-arrow-left"></i> Kembali ke Riwayat
    </a>
</div>

<div class="bento-card">
    <div class="summary-grid">
        <div>
            <div class="doc-info-label">Nomor Referensi Dokumen</div>
            <div class="doc-code"><?= esc($payroll['payroll_code'] ?? '-') ?></div>
            <div class="doc-meta">
                <span><i class="ph-bold ph-calendar"></i> <?= date('d M Y', strtotime($payroll['period_start'] ?? 'now')) ?> - <?= date('d M Y', strtotime($payroll['period_end'] ?? 'now')) ?></span>
                <span><i class="ph-bold ph-users"></i> <?= esc($payroll['total_employees'] ?? 0) ?> Orang Terproses</span>
                <span><i class="ph-bold ph-briefcase"></i> Siklus: <?= esc($payroll['employee_status'] ?? '-') ?> (<?= esc($payroll['salary_type'] ?? '-') ?>)</span>
            </div>
        </div>
        
        <div class="total-box">
            <div class="doc-info-label">Total Dana Dibutuhkan</div>
            <div class="total-amount">Rp <?= number_format((float)($payroll['total_amount'] ?? 0), 0, ',', '.') ?></div>
            
            <div style="margin-top: 15px;">
                <?php if(($payroll['status'] ?? '') == 'Draft'): ?>
                    <div class="status-banner banner-draft"><i class="ph-bold ph-warning-circle" style="font-size: 18px;"></i> DRAFT (Menunggu Pencairan)</div>
                <?php else: ?>
                    <div class="status-banner banner-paid"><i class="ph-bold ph-check-circle" style="font-size: 18px;"></i> DANA SUDAH DICAIRKAN</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="bento-card" style="padding: 0;">
    <div style="padding: 20px 25px; border-bottom: 1px dashed var(--border-subtle); display: flex; align-items: center; gap: 10px; font-weight: 900; font-size: 15px; color: var(--text-main); background: rgba(0,0,0,0.01);">
        <i class="ph-fill ph-list-numbers" style="color: var(--accent-main); font-size: 20px;"></i> Rincian Kalkulasi per Karyawan / Mandor
    </div>
    
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th rowspan="2" class="th-main" style="padding-left:25px;">Data Karyawan / Mandor</th>
                    <th rowspan="2" class="th-main" style="text-align: center;">Via</th>
                    <th rowspan="2" class="th-main" style="text-align: center;">Hadir</th>
                    <th colspan="3" class="th-green" style="text-align: center;">Pendapatan Utama & Tambahan (+)</th>
                    <th colspan="3" class="th-red" style="text-align: center;">Potongan (-)</th>
                    <th rowspan="2" class="th-accent" style="text-align: right;">Gaji Bersih (THP)</th>
                    <th rowspan="2" class="th-main" style="text-align: center;">Tindakan</th>
                </tr>
                <tr>
                    <th class="th-green" style="text-align: right;">Upah Pokok/Prod</th>
                    <th class="th-green" style="text-align: right;">Tunjangan (All)</th>
                    <th class="th-green" style="text-align: right;">U. Lembur</th>
                    <th class="th-red" style="text-align: right;">Kasbon (Diproses)</th>
                    <th class="th-red" style="text-align: right;">Denda Telat</th>
                    <th class="th-red" style="text-align: right;">BPJS</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($details as $row): ?>
                <?php
                    $netMealSaved = (float)($row['meal_allowance'] ?? 0);
                    $totalTunjanganLengkap = (float)($row['position_allowance'] ?? 0) + $netMealSaved + (float)($row['transport_allowance'] ?? 0);
                    $totalAllKasbon = (float)($row['cash_advance'] ?? 0);
                    
                    $isMandor = false;
                    foreach($details as $sub) {
                        if(($sub['leader_id'] ?? '') === ($row['employee_id'] ?? '')) {
                            $isMandor = true; break;
                        }
                    }

                    $pm = $row['payment_method'] ?? 'Cash';
                    if (($payroll['employee_status'] ?? '') === 'Borongan') $pm = 'Transfer';
                    
                    $isAnakBuah = !empty($row['leader_id']);
                ?>
                <tr <?= $isAnakBuah ? 'style="background:rgba(0,0,0,0.02); opacity:0.6;" title="Diakumulasi ke Mandor"' : '' ?>>
                    <td style="padding-left:25px;">
                        <div style="font-weight: 900; font-size: 14px; margin-bottom: 4px; color: var(--text-main);">
                            <?= esc($row['name'] ?? 'Unknown') ?>
                            <?php if($isMandor): ?>
                                <i class="ph-fill ph-users-three" style="color: #2563eb;" title="Ketua Regu / Mandor"></i>
                            <?php endif; ?>
                            <?php if($isAnakBuah): ?>
                                <i class="ph-bold ph-arrow-elbow-down-right" style="color: var(--text-muted);"></i>
                            <?php endif; ?>
                        </div>
                        <div style="font-size: 11px; color: var(--text-muted); font-family: 'Space Mono', monospace;">
                            <i class="ph-bold ph-identification-card"></i> <?= esc($row['employee_id'] ?? '-') ?>
                        </div>
                        <?php if($isMandor && ($payroll['employee_status'] ?? '') === 'Borongan'): ?>
                            <div class="team-badge">Slip Gaji Borongan Tim</div>
                        <?php elseif($isAnakBuah): ?>
                            <div class="team-badge" style="background:#f1f5f9; color:#64748b; border-color:#cbd5e1;">Anggota Tim</div>
                        <?php endif; ?>
                    </td>
                    
                    <td style="text-align: center;">
                        <?php if($pm === 'Transfer'): ?>
                            <span style="font-size: 10px; font-weight: 900; background: rgba(139, 92, 246, 0.1); color: #8b5cf6; padding: 4px 8px; border-radius: 6px; border: 1px solid rgba(139, 92, 246, 0.2);"><i class="ph-bold ph-bank"></i> ATM</span>
                        <?php else: ?>
                            <span style="font-size: 10px; font-weight: 900; background: rgba(16, 185, 129, 0.1); color: #10b981; padding: 4px 8px; border-radius: 6px; border: 1px solid rgba(16, 185, 129, 0.2);"><i class="ph-bold ph-money"></i> CASH</span>
                        <?php endif; ?>
                    </td>

                    <td style="text-align: center;">
                        <span style="background: var(--bg-base); border: 1px solid var(--border-subtle); padding: 6px 10px; border-radius: 8px; font-weight: 900; font-size: 12px;"><?= esc($row['total_present'] ?? 0) ?> HK</span>
                    </td>
                    
                    <td class="font-mono text-plus" style="text-align: right;">+<?= number_format((float)($row['basic_salary'] ?? 0) + (float)($row['borongan_pay'] ?? 0), 0, ',', '.') ?></td>
                    
                    <td style="text-align: right;">
                        <span class="font-mono text-plus">+<?= number_format($totalTunjanganLengkap, 0, ',', '.') ?></span>
                        <div class="detail-breakdown">
                            <?php if(($row['position_allowance'] ?? 0) > 0) echo "<div>Jab: ".number_format((float)$row['position_allowance'], 0, ',', '.')."</div>"; ?>
                            <?php if($netMealSaved > 0) echo "<div>Mkn(Sisa Bersih): ".number_format($netMealSaved, 0, ',', '.')."</div>"; ?>
                            <?php if(isset($row['overtime_meal_allowance']) && $row['overtime_meal_allowance'] > 0) echo "<div style='color:#64748b;'>Mkn Lmbr (Telah Cair): ".number_format((float)$row['overtime_meal_allowance'], 0, ',', '.')."</div>"; ?>
                            <?php if(($row['transport_allowance'] ?? 0) > 0) echo "<div>Trnspt: ".number_format((float)$row['transport_allowance'], 0, ',', '.')."</div>"; ?>
                        </div>
                    </td>

                    <td class="font-mono text-plus" style="text-align: right;">+<?= number_format((float)($row['overtime_pay'] ?? 0), 0, ',', '.') ?></td>
                    
                    <td style="text-align: right; <?= $totalAllKasbon > 0 ? 'background: rgba(239, 68, 68, 0.05);' : '' ?>">
                        <span class="font-mono text-min" style="<?= $totalAllKasbon > 0 ? 'font-weight: 900;' : '' ?>">-<?= number_format($totalAllKasbon, 0, ',', '.') ?></span>
                        <div class="detail-breakdown" style="color: #ef4444;">
                            <?php if(($row['cash_advance'] ?? 0) > 0) echo "<div>Kalkulasi Otomatis</div>"; ?>
                        </div>
                    </td>

                    <td class="font-mono text-min" style="text-align: right;">-<?= number_format((float)($row['late_penalty'] ?? 0), 0, ',', '.') ?></td>
                    <td class="font-mono text-min" style="text-align: right;">-<?= number_format((float)($row['bpjs_deduction'] ?? 0), 0, ',', '.') ?></td>
                    
                    <td class="font-mono text-thp" style="text-align: right; background: rgba(0,0,0,0.01);">
                        Rp <?= number_format((float)($row['net_salary'] ?? 0), 0, ',', '.') ?>
                    </td>
                    
                    <td style="text-align: center;">
                        <a href="<?= base_url('/payroll/print_slip/' . ($row['id'] ?? '')) ?>" target="_blank" class="btn-print" title="Cetak / Download Slip">
                            <i class="ph-bold ph-printer" style="font-size: 18px;"></i> Slip
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php if(($payroll['status'] ?? '') == 'Draft'): ?>
    <div class="doc-info-label" style="margin-top: 30px; font-size: 13px;">Otorisasi Pencairan Dana (Otomatis Dipecah & Masuk Buku Besar)</div>
    <div style="font-size: 13px; color: var(--text-muted); margin-bottom: 15px; font-weight: 600;">Sistem akan memotong kas laci/bank dan otomatis memilah beban gaji dengan kasbon secara akurat (Balance).</div>
    
    <div class="split-grid">
        <div class="split-box">
            <div class="split-icon" style="background: rgba(16, 185, 129, 0.1); color: #10b981;"><i class="ph-fill ph-money"></i></div>
            <div>
                <div class="split-title">Dipotong dari Brankas Tunai (Cash)</div>
                <div class="split-val">Rp <?= number_format($totalCash, 0, ',', '.') ?></div>
            </div>
        </div>
        <div class="split-box">
            <div class="split-icon" style="background: rgba(139, 92, 246, 0.1); color: #8b5cf6;"><i class="ph-fill ph-bank"></i></div>
            <div>
                <div class="split-title">Dipotong dari Rekening Bank (Transfer)</div>
                <div class="split-val">Rp <?= number_format($totalTransfer, 0, ',', '.') ?></div>
            </div>
        </div>
    </div>

    <form method="POST" action="<?= base_url('/payroll/push_to_finance') ?>" id="formDisburse">
        <?= csrf_field() ?>
        <!-- Data murni dikirim sebagai ID. Nominal murni dihitung backend! -->
        <input type="hidden" name="payroll_id" value="<?= esc($payroll['id'] ?? '') ?>">
        <input type="hidden" name="payroll_code" value="<?= esc($payroll['payroll_code'] ?? '') ?>">
        
        <button type="button" onclick="confirmSmartDisburse()" class="btn-disburse">
            <i class="ph-bold ph-lock-key" style="font-size: 22px;"></i> Kunci Dokumen & Eksekusi Pembayaran Gaji
        </button>
    </form>

<?php else: ?>
    <div style="background: rgba(16, 185, 129, 0.05); border: 1px dashed rgba(16, 185, 129, 0.3); padding: 30px; border-radius: 20px; text-align: center; margin-top: 30px;">
        <i class="ph-fill ph-lock-key" style="font-size: 48px; color: #10b981; margin-bottom: 12px;"></i>
        <h3 style="margin: 0 0 6px 0; color: #059669; font-weight: 900; font-size: 18px;">Dokumen Penggajian Terkunci</h3>
        <p style="margin: 0; color: var(--text-muted); font-size: 13px; font-weight: 600;">
            Dana telah dicairkan & Jurnal terbentuk: <b>Rp <?= number_format($totalCash, 0, ',', '.') ?> (Tunai Kas)</b> & <b>Rp <?= number_format($totalTransfer, 0, ',', '.') ?> (Transfer Bank)</b>.
        </p>
    </div>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    <?php if(session()->getFlashdata('success')): ?>
        const isDark = document.documentElement.classList.contains('dark');
        Swal.fire({ icon: 'success', title: 'Pencairan Sukses!', html: <?= json_encode(session()->getFlashdata('success')) ?>, confirmButtonColor: '#38bdf8', background: isDark ? '#18181b' : '#ffffff', color: isDark ? '#f4f4f5' : '#09090b', customClass: { popup: 'swal2-custom-radius' } });
    <?php endif; ?>
    <?php if(session()->getFlashdata('error')): ?>
        Swal.fire({ icon: 'error', title: 'Pencairan Ditolak!', html: <?= json_encode(session()->getFlashdata('error')) ?>, confirmButtonColor: '#ef4444', customClass: { popup: 'swal2-custom-radius' } });
    <?php endif; ?>

    function confirmSmartDisburse() {
        let cash = "<?= number_format($totalCash, 0, ',', '.') ?>";
        let transfer = "<?= number_format($totalTransfer, 0, ',', '.') ?>";
        
        const isDark = document.documentElement.classList.contains('dark');
        Swal.fire({
            title: `Otorisasi Pencairan Gaji?`,
            html: `Sistem akan memotong kas operasional Anda sejumlah:<br>
                   <b style="color: #10b981; font-size: 16px;">Rp ${cash} (Tunai Laci)</b> dan <b style="color: #8b5cf6; font-size: 16px;">Rp ${transfer} (Transfer ATM)</b>.<br><br>
                   <span style="color:#ef4444; font-size: 12px; font-weight:700;"><i class="ph-bold ph-warning"></i> Dokumen akan terkunci dan otomatis melunasi Piutang Karyawan di Buku Besar. Lanjutkan?</span>`,
            icon: 'warning', showCancelButton: true, confirmButtonColor: '#10b981', cancelButtonColor: isDark ? '#3f3f46' : '#cbd5e1',
            confirmButtonText: 'Ya, Cairkan Sekarang!', cancelButtonText: 'Batal',
            background: isDark ? '#18181b' : '#ffffff', color: isDark ? '#f4f4f5' : '#09090b', customClass: { popup: 'swal2-custom-radius' }
        }).then((result) => {
            if (result.isConfirmed) document.getElementById('formDisburse').submit();
        });
    }
</script>
<?= $this->endSection() ?>