<?= $this->extend('layout/template') ?>
<?= $this->section('content') ?>

<style>
    .page-header { margin-bottom: 30px; display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 20px;}
    .page-title { display: flex; align-items: center; gap: 18px;}
    .title-icon { width: 56px; height: 56px; border-radius: 16px; background: linear-gradient(135deg, #a855f7, #7e22ce); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 28px; box-shadow: 0 10px 25px -5px rgba(168, 85, 247, 0.4);}
    .page-title h1 { font-size: 28px; font-weight: 900; color: var(--text-main); margin: 0 0 4px 0; letter-spacing: -0.5px;}
    .page-title p { font-size: 13px; color: var(--text-muted); font-weight: 600; margin: 0;}

    .btn-back { background: var(--bg-surface); color: var(--text-main); border: 1px solid var(--border-subtle); padding: 12px 20px; border-radius: 14px; font-weight: 800; text-decoration: none; font-size: 13px; display: inline-flex; align-items: center; gap: 8px; transition: all 0.3s; box-shadow: 0 4px 10px rgba(0,0,0,0.02);}
    .btn-back:hover { color: #a855f7; border-color: #a855f7; transform: translateX(-4px); box-shadow: 0 8px 15px rgba(168, 85, 247, 0.15);}

    .bento-card { background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: 28px; padding: 35px; box-shadow: 0 15px 40px -15px rgba(0,0,0,0.05); border-top: 6px solid #a855f7;}
    
    .slip-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 20px; }
    .slip-box { border: 1px solid var(--border-subtle); background: var(--bg-base); border-radius: 20px; padding: 25px; transition: 0.3s; position: relative; overflow: hidden; display: flex; flex-direction: column; gap: 15px;}
    .slip-box:hover { border-color: #a855f7; transform: translateY(-4px); box-shadow: 0 12px 25px rgba(168, 85, 247, 0.15);}
    .slip-box::before { content: ''; position: absolute; right: -20px; top: -20px; width: 100px; height: 100px; background: radial-gradient(circle, rgba(168, 85, 247, 0.1) 0%, transparent 70%); border-radius: 50%; pointer-events: none;}

    .s-head { display: flex; justify-content: space-between; align-items: flex-start;}
    .s-code { font-family: 'Space Mono', monospace; font-size: 12px; font-weight: 900; color: #a855f7; background: rgba(168, 85, 247, 0.1); padding: 4px 10px; border-radius: 8px; border: 1px dashed rgba(168, 85, 247, 0.3); margin-bottom: 8px; display: inline-block;}
    .s-period { font-size: 16px; font-weight: 900; color: var(--text-main); margin-bottom: 4px;}
    .s-date { font-size: 12px; font-weight: 700; color: var(--text-muted); display: flex; align-items: center; gap: 6px;}

    .s-amount { font-family: 'Space Mono', monospace; font-size: 26px; font-weight: 900; color: #10b981; letter-spacing: -1px; padding: 15px 0; border-top: 1px dashed var(--border-subtle); border-bottom: 1px dashed var(--border-subtle); margin: 5px 0;}
    
    .s-action { text-align: center; margin-top: auto;}
    .btn-download { display: flex; align-items: center; justify-content: center; gap: 8px; width: 100%; background: #0f172a; color: #fff; text-decoration: none; padding: 14px; border-radius: 12px; font-weight: 900; font-size: 14px; transition: 0.3s;}
    .btn-download:hover { background: #a855f7; transform: translateY(-2px); box-shadow: 0 8px 20px rgba(168, 85, 247, 0.4);}
    html.dark .btn-download { background: #f8fafc; color: #0f172a; }
    html.dark .btn-download:hover { background: #c084fc; color: #fff;}

    .empty-state { text-align: center; padding: 60px 20px; color: var(--text-muted); grid-column: 1 / -1;}
    .empty-state i { font-size: 64px; color: var(--border-subtle); margin-bottom: 15px; display: block; }
</style>

<div class="page-wrapper">
    <div class="page-header">
        <div class="page-title">
            <div class="title-icon"><i class="ph-fill ph-receipt"></i></div>
            <div>
                <h1>Riwayat Slip Gaji</h1>
                <p>Arsip dokumen penggajian Anda yang sah dan siap diunduh.</p>
            </div>
        </div>
        <a href="<?= base_url('/portal') ?>" class="btn-back">
            <i class="ph-bold ph-arrow-left"></i> Kembali ke Dasbor
        </a>
    </div>

    <?php if(session()->getFlashdata('error')): ?>
        <div style="background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.3); padding: 15px 20px; border-radius: 14px; color: #ef4444; font-size: 13px; font-weight: 700; margin-bottom: 25px;">
            <i class="ph-fill ph-warning-circle" style="font-size: 18px; vertical-align: middle; margin-right: 8px;"></i>
            <?= session()->getFlashdata('error') ?>
        </div>
    <?php endif; ?>

    <div class="bento-card">
        <div class="slip-grid">
            <?php if(empty($mySlips)): ?>
                <div class="empty-state">
                    <i class="ph-fill ph-folder-open"></i>
                    <h2 style="font-size: 20px; font-weight: 900; color: var(--text-main); margin: 0 0 8px 0;">Belum Ada Arsip Gaji</h2>
                    <p style="font-size: 14px; margin: 0;">Sistem belum menerbitkan slip gaji untuk Anda, atau dokumen masih berstatus draf.</p>
                </div>
            <?php else: ?>
                <?php foreach($mySlips as $slip): ?>
                <div class="slip-box">
                    <div class="s-head">
                        <div>
                            <div class="s-code"><?= esc($slip['payroll_code']) ?></div>
                            <div class="s-period"><?= date('F Y', strtotime($slip['period_end'])) ?></div>
                            <div class="s-date"><i class="ph-bold ph-calendar-blank"></i> Periode: <?= date('d/m', strtotime($slip['period_start'])) ?> - <?= date('d/m/Y', strtotime($slip['period_end'])) ?></div>
                        </div>
                        <i class="ph-fill ph-check-circle" style="color: #10b981; font-size: 24px;" title="Telah Disalurkan (Paid)"></i>
                    </div>
                    
                    <div class="s-amount">
                        <span style="font-size: 14px; color: var(--text-muted); font-family: 'Plus Jakarta Sans', sans-serif;">THP:</span> 
                        Rp <?= number_format($slip['net_salary'], 0, ',', '.') ?>
                    </div>
                    
                    <div class="s-action">
                        <a href="<?= base_url('/portal/print_my_slip/' . $slip['id']) ?>" target="_blank" class="btn-download">
                            <i class="ph-bold ph-download-simple" style="font-size: 18px;"></i> Unduh / Cetak Dokumen
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
<?= $this->endSection() ?>