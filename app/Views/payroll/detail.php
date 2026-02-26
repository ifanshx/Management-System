<?= $this->extend('layout/template') ?>
<?= $this->section('content') ?>

<style>
    .header-box { background: var(--bg-surface); padding: 25px; border-radius: 20px; border: 1px solid var(--border-subtle); display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;}
    table { width: 100%; border-collapse: collapse; white-space: nowrap; background: var(--bg-surface); border-radius: 16px; overflow: hidden;}
    th { padding: 15px; background: var(--bg-base); font-size: 11px; text-transform: uppercase; color: var(--text-muted); border-bottom: 1px solid var(--border-subtle);}
    td { padding: 15px; border-bottom: 1px solid var(--border-subtle); font-size: 13px; font-weight: 600; color: var(--text-main);}
</style>

<div class="header-box">
    <div>
        <h1 style="margin:0; font-size: 24px; color: var(--text-main);"><?= esc($payroll['payroll_code']) ?></h1>
        <p style="margin: 5px 0 0 0; color: var(--text-muted); font-size: 13px;">Periode: <?= date('d M', strtotime($payroll['period_start'])) ?> s/d <?= date('d M Y', strtotime($payroll['period_end'])) ?></p>
    </div>
    <div style="text-align: right;">
        <div style="font-size: 12px; color: var(--text-muted); font-weight: 700; text-transform: uppercase;">Total Pencairan</div>
        <div style="font-size: 28px; font-weight: 800; color: #10b981;">Rp <?= number_format($payroll['total_amount'], 0, ',', '.') ?></div>
    </div>
</div>

<div style="overflow-x: auto; border-radius: 16px; border: 1px solid var(--border-subtle);">
    <table>
        <thead>
            <tr>
                <th>Karyawan</th>
                <th>Kehadiran</th>
                <th>Denda Telat</th>
                <th>Uang Lembur</th>
                <th>Gaji Bersih (THP)</th>
                <th>Rekening Tujuan</th>
                <th>Slip PDF</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($details as $row): ?>
            <tr>
                <td>
                    <div style="font-weight: 800;"><?= esc($row['name']) ?></div>
                    <div style="font-size: 11px; color: var(--text-muted); font-family: monospace;"><?= esc($row['employee_id']) ?></div>
                </td>
                <td><span style="background: rgba(16, 185, 129, 0.1); color: #10b981; padding: 4px 8px; border-radius: 6px;"><?= $row['total_present'] ?> Hari</span></td>
                <td><span style="color: #ef4444;">- Rp <?= number_format($row['late_penalty'], 0, ',', '.') ?></span></td>
                <td><span style="color: #a855f7;">+ Rp <?= number_format($row['overtime_pay'], 0, ',', '.') ?></span></td>
                <td style="font-size: 16px; font-weight: 800; color: var(--accent-main);">Rp <?= number_format($row['net_salary'], 0, ',', '.') ?></td>
                <td>
                    <div style="font-size: 12px;"><?= esc($row['bank_name']) ?></div>
                    <div style="font-family: monospace; font-weight: 700;"><?= esc($row['bank_account']) ?></div>
                </td>
                <td>
                    <a href="<?= base_url('/payroll/print_slip/' . $row['id']) ?>" target="_blank" style="background: var(--bg-base); color: var(--text-main); border: 1px solid var(--border-subtle); padding: 8px 12px; border-radius: 8px; text-decoration: none; font-size: 12px; display: inline-flex; align-items: center; gap: 5px;">
                        <i class="ph ph-printer"></i> Cetak
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?= $this->endSection() ?>