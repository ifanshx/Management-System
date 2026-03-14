<?= $this->extend('layout/template') ?>
<?= $this->section('content') ?>

<?php
    $aset = 0; $kewajiban = 0; $ekuitas = 0; $pendapatan = 0; $beban = 0;

    foreach($summary as $row) {
        if($row['account_type'] == 'ASET') $aset = $row['total_balance'];
        if($row['account_type'] == 'LIABILITI') $kewajiban = $row['total_balance'];
        if($row['account_type'] == 'EKUITI') $ekuitas = $row['total_balance'];
        if($row['account_type'] == 'PENDAPATAN') $pendapatan = $row['total_balance'];
        if($row['account_type'] == 'PERBELANJAAN') $beban = $row['total_balance'];
    }

    $labaBersih = $pendapatan - $beban;
?>

<style>
    .page-header { margin-bottom: 30px; display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 15px;}
    
    .page-title { display: flex; align-items: center; gap: 15px;}
    .title-icon { width: 52px; height: 52px; border-radius: 16px; background: linear-gradient(135deg, rgba(14, 165, 233, 0.15), rgba(2, 132, 199, 0.05)); color: #0ea5e9; display: flex; align-items: center; justify-content: center; font-size: 26px; border: 1px solid rgba(14, 165, 233, 0.2);}
    .page-title h1 { font-size: 28px; font-weight: 900; color: var(--text-main); margin: 0; letter-spacing: -0.5px;}
    .page-title p { font-size: 14px; color: var(--text-muted); font-weight: 500; margin: 4px 0 0 0;}
    
    .btn-primary { background: linear-gradient(135deg, #0ea5e9, #0284c7); color: #fff; border: none; padding: 14px 24px; border-radius: 14px; font-size: 14px; font-weight: 900; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; text-decoration: none; box-shadow: 0 8px 20px -6px rgba(14, 165, 233, 0.5); transition: all 0.3s;}
    .btn-primary:hover { transform: translateY(-3px); box-shadow: 0 12px 25px -6px rgba(14, 165, 233, 0.6);}

    /* SUMMARY CARDS (ENTERPRISE STYLE) */
    .kpi-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 25px;}
    @media (max-width: 1024px) { .kpi-grid { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 640px) { .kpi-grid { grid-template-columns: 1fr; } }

    .kpi-card { background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: 20px; padding: 24px; box-shadow: 0 10px 30px -10px rgba(0,0,0,0.05); position: relative; overflow: hidden; transition: 0.3s;}
    .kpi-card:hover { transform: translateY(-3px); box-shadow: 0 15px 35px -10px rgba(0,0,0,0.08);}
    
    .kpi-icon { width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 22px; margin-bottom: 15px;}
    
    .c-aset .kpi-icon { background: rgba(59, 130, 246, 0.1); color: #3b82f6; border: 1px solid rgba(59, 130, 246, 0.2);}
    .c-rev .kpi-icon { background: rgba(16, 185, 129, 0.1); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.2);}
    .c-exp .kpi-icon { background: rgba(239, 68, 68, 0.1); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.2);}
    .c-profit .kpi-icon { background: rgba(139, 92, 246, 0.1); color: #8b5cf6; border: 1px solid rgba(139, 92, 246, 0.2);}

    .kpi-title { font-size: 12px; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px;}
    .kpi-val { font-size: 20px; font-weight: 900; color: var(--text-main); font-family: 'Space Mono', monospace; margin-top: 5px; letter-spacing: -1px;}
    .kpi-profit { color: <?= ($labaBersih >= 0) ? '#10b981' : '#ef4444' ?>; }

    /* LAYOUT BOTTOM */
    .bottom-grid { display: grid; grid-template-columns: 2fr 1.2fr; gap: 24px; }
    @media (max-width: 1024px) { .bottom-grid { grid-template-columns: 1fr; } }

    .bento-card { background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: 24px; padding: 30px; box-shadow: 0 10px 30px -10px rgba(0,0,0,0.05); }
    .card-title { font-size: 16px; font-weight: 900; color: var(--text-main); margin-bottom: 25px; display: flex; align-items: center; gap: 10px; border-bottom: 2px dashed var(--border-subtle); padding-bottom: 15px;}
    .card-title i { background: rgba(14, 165, 233, 0.1); color: #0ea5e9; padding: 6px; border-radius: 8px; font-size: 20px;}

    /* TABLE STYLES */
    table { width: 100%; border-collapse: collapse; }
    td { padding: 16px 10px; border-bottom: 1px dashed var(--border-subtle); color: var(--text-main); vertical-align: top;}
    tr:last-child td { border-bottom: none; }
</style>

<div class="page-header">
    <div class="page-title">
        <div class="title-icon"><i class="ph-fill ph-chart-pie-slice"></i></div>
        <div>
            <h1>Dashboard Finansial</h1>
            <p>Ringkasan kesehatan finansial pabrik, nilai aset, dan laba rugi (General Ledger).</p>
        </div>
    </div>
    
    <a href="<?= base_url('/accounting/journal') ?>" class="btn-primary">
        <i class="ph-bold ph-plus-circle" style="font-size: 20px;"></i> Catat Jurnal Baru
    </a>
</div>

<div class="kpi-grid">
    <div class="kpi-card c-aset">
        <div class="kpi-icon"><i class="ph-fill ph-bank"></i></div>
        <div class="kpi-title">Total Aset (Kas & Barang)</div>
        <div class="kpi-val">Rp <?= number_format($aset, 0, ',', '.') ?></div>
    </div>
    <div class="kpi-card c-rev">
        <div class="kpi-icon"><i class="ph-fill ph-trend-up"></i></div>
        <div class="kpi-title">Pendapatan Kotor</div>
        <div class="kpi-val">Rp <?= number_format($pendapatan, 0, ',', '.') ?></div>
    </div>
    <div class="kpi-card c-exp">
        <div class="kpi-icon"><i class="ph-fill ph-trend-down"></i></div>
        <div class="kpi-title">Total Beban (HPP & Ops)</div>
        <div class="kpi-val">Rp <?= number_format($beban, 0, ',', '.') ?></div>
    </div>
    <div class="kpi-card c-profit">
        <div class="kpi-icon"><i class="ph-fill ph-scales"></i></div>
        <div class="kpi-title">Laba Bersih (Net Profit)</div>
        <div class="kpi-val kpi-profit">Rp <?= number_format($labaBersih, 0, ',', '.') ?></div>
    </div>
</div>

<div class="bottom-grid">
    <div class="bento-card">
        <div class="card-title"><i class="ph-fill ph-chart-bar"></i> Visualisasi Arus Kas & Laba</div>
        <div style="position: relative; height: 320px; width: 100%;">
            <canvas id="financeChart"></canvas>
        </div>
    </div>

    <div class="bento-card" style="padding: 25px;">
        <div class="card-title"><i class="ph-fill ph-clock-counter-clockwise"></i> Log Jurnal Terakhir</div>
        <div style="overflow-x: auto;">
            <table>
                <tbody>
                    <?php if(empty($recent_journals)): ?>
                        <tr><td style="text-align: center; color: var(--text-muted); padding: 40px 10px;">Belum ada riwayat transaksi jurnal.</td></tr>
                    <?php else: ?>
                        <?php foreach($recent_journals as $jrn): ?>
                            <tr>
                                <td>
                                    <div style="font-weight: 900; font-size: 13px; color: #0ea5e9; font-family: 'Space Mono', monospace; background: rgba(14, 165, 233, 0.1); padding: 4px 8px; border-radius: 6px; display: inline-block; margin-bottom: 4px;"><?= esc($jrn['journal_number']) ?></div>
                                    <div style="font-size: 11px; color: var(--text-muted); font-weight: 700;"><i class="ph-bold ph-calendar-blank"></i> <?= date('d M Y', strtotime($jrn['transaction_date'])) ?></div>
                                </td>
                                <td>
                                    <div style="font-size: 13px; white-space: normal; line-height: 1.4; font-weight: 800; color: var(--text-main);"><?= esc($jrn['description']) ?></div>
                                    <div style="font-size: 11px; color: var(--text-muted); margin-top: 4px;"><i class="ph-fill ph-user-circle"></i> <?= esc($jrn['created_by']) ?></div>
                                </td>
                                <td style="text-align: right; font-weight: 900; font-family: 'Space Mono', monospace; font-size: 14px;">
                                    Rp <?= number_format($jrn['total_amount'], 0, ',', '.') ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <a href="<?= base_url('/accounting/journal') ?>" style="display: flex; justify-content: center; align-items: center; gap: 8px; margin-top: 20px; font-size: 12px; font-weight: 800; color: #0ea5e9; text-decoration: none; background: rgba(14, 165, 233, 0.05); padding: 12px; border-radius: 12px; transition: 0.3s;" onmouseover="this.style.background='rgba(14, 165, 233, 0.1)'" onmouseout="this.style.background='rgba(14, 165, 233, 0.05)'">
            Lihat Semua Jurnal Akuntansi <i class="ph-bold ph-arrow-right"></i>
        </a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('financeChart').getContext('2d');
        const isDark = document.documentElement.classList.contains('dark');
        const textColor = isDark ? '#a1a1aa' : '#71717a';

        // Create Gradients for Premium Look
        let gradGreen = ctx.createLinearGradient(0, 0, 0, 300);
        gradGreen.addColorStop(0, 'rgba(16, 185, 129, 0.8)'); gradGreen.addColorStop(1, 'rgba(16, 185, 129, 0.2)');
        
        let gradRed = ctx.createLinearGradient(0, 0, 0, 300);
        gradRed.addColorStop(0, 'rgba(239, 68, 68, 0.8)'); gradRed.addColorStop(1, 'rgba(239, 68, 68, 0.2)');
        
        let gradPurple = ctx.createLinearGradient(0, 0, 0, 300);
        gradPurple.addColorStop(0, 'rgba(139, 92, 246, 0.8)'); gradPurple.addColorStop(1, 'rgba(139, 92, 246, 0.2)');

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Pendapatan Kotor', 'Total Beban Pokok', 'Laba Bersih (Profit)'],
                datasets: [{
                    label: 'Nominal (Rp)',
                    data: [<?= $pendapatan ?>, <?= $beban ?>, <?= $labaBersih ?>],
                    backgroundColor: [gradGreen, gradRed, gradPurple],
                    borderColor: ['#10b981', '#ef4444', '#8b5cf6'],
                    borderWidth: 1,
                    borderRadius: 12,
                    barPercentage: 0.6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: isDark ? 'rgba(24, 24, 27, 0.9)' : 'rgba(255, 255, 255, 0.9)',
                        titleColor: isDark ? '#fff' : '#000',
                        bodyColor: isDark ? '#d4d4d8' : '#3f3f46',
                        borderColor: isDark ? 'rgba(255,255,255,0.1)' : 'rgba(0,0,0,0.1)',
                        borderWidth: 1, padding: 12,
                        titleFont: { family: 'Plus Jakarta Sans', size: 13, weight: 'bold' },
                        bodyFont: { family: 'Space Mono', size: 14, weight: 'bold' },
                        callbacks: {
                            label: function(context) { return 'Rp ' + context.raw.toLocaleString('id-ID'); }
                        }
                    }
                },
                scales: {
                    y: { beginAtZero: true, grid: { color: isDark ? 'rgba(255,255,255,0.05)' : 'rgba(0,0,0,0.04)', drawBorder: false }, ticks: { color: textColor, font: {family: 'Space Mono'} } },
                    x: { grid: { display: false, drawBorder: false }, ticks: { color: textColor, font: { weight: 'bold', family: 'Plus Jakarta Sans' } } }
                }
            }
        });
    });
</script>

<?= $this->endSection() ?>