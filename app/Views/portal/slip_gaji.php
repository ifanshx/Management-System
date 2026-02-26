<?= $this->extend('layout/template') ?>
<?= $this->section('content') ?>

<style>
    .page-header { margin-bottom: 25px; }
    .page-title h1 { font-size: 24px; font-weight: 800; color: var(--text-main); letter-spacing: -0.5px; margin-bottom: 4px; }
    .page-title p { font-size: 13px; color: var(--text-muted); }

    .slip-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; }
    
    .slip-card { background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: 16px; padding: 20px; box-shadow: var(--shadow-card); transition: 0.3s; position: relative; overflow: hidden; }
    .slip-card:hover { border-color: var(--accent-main); transform: translateY(-3px); }
    .slip-card::before { content: ''; position: absolute; top: 0; left: 0; width: 4px; height: 100%; background: var(--accent-main); }
    
    .period-title { font-size: 16px; font-weight: 800; color: var(--text-main); margin-bottom: 5px; }
    .doc-code { font-size: 11px; font-family: monospace; color: var(--text-muted); background: var(--bg-base); padding: 4px 8px; border-radius: 6px; display: inline-block; margin-bottom: 15px; }
    
    .amount { font-size: 22px; font-weight: 800; color: #10b981; margin-bottom: 20px; }
    
    .btn-download { width: 100%; background: var(--bg-base); color: var(--text-main); border: 1px solid var(--border-subtle); padding: 10px; border-radius: 10px; font-weight: 700; cursor: pointer; transition: 0.2s; display: flex; justify-content: center; align-items: center; gap: 8px; text-decoration: none; font-size: 13px; }
    .btn-download:hover { background: var(--accent-main); color: #fff; border-color: var(--accent-main); }
</style>

<div class="page-header">
    <div class="page-title">
        <h1>Slip Gaji Saya</h1>
        <p>Dokumen rahasia penggajian (Payslip) yang diterbitkan oleh HRD.</p>
    </div>
</div>

<?php if(session()->getFlashdata('error')): ?>
    <div style="background: rgba(239, 68, 68, 0.1); color: #ef4444; padding: 15px; border-radius: 10px; margin-bottom: 20px; font-size: 13px; font-weight: 600; border: 1px solid rgba(239, 68, 68, 0.2);">
        <i class="ph ph-warning-circle"></i> <?= session()->getFlashdata('error') ?>
    </div>
<?php endif; ?>

<div class="slip-grid">
    <?php if(empty($mySlips)): ?>
        <div style="grid-column: 1 / -1; padding: 40px; text-align: center; background: var(--bg-surface); border: 1px dashed var(--border-subtle); border-radius: 16px; color: var(--text-muted);">
            <i class="ph ph-receipt" style="font-size: 48px; margin-bottom: 10px;"></i>
            <div>Belum ada dokumen slip gaji yang diterbitkan untuk Anda.</div>
        </div>
    <?php else: ?>
        <?php foreach($mySlips as $slip): ?>
        <div class="slip-card">
            <div class="period-title"><?= date('d M Y', strtotime($slip['period_start'])) ?> - <?= date('d M Y', strtotime($slip['period_end'])) ?></div>
            <div class="doc-code"><i class="ph ph-file-text"></i> <?= esc($slip['payroll_code']) ?></div>
            
            <div style="font-size: 11px; color: var(--text-muted); font-weight: 700; text-transform: uppercase;">Take Home Pay</div>
            <div class="amount">Rp <?= number_format($slip['net_salary'], 0, ',', '.') ?></div>
            
            <a href="<?= base_url('/portal/print_slip/' . $slip['id']) ?>" target="_blank" class="btn-download">
                <i class="ph ph-download-simple"></i> Unduh / Cetak PDF
            </a>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?= $this->endSection() ?>