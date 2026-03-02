<?= $this->extend('layout/template') ?>
<?= $this->section('content') ?>

<?php
    // Inisialisasi variabel untuk menghitung ringkasan
    $aset = 0; $kewajiban = 0; $ekuitas = 0; $pendapatan = 0; $beban = 0;

    foreach($summary as $row) {
        if($row['account_type'] == 'ASET') $aset = $row['total_balance'];
        if($row['account_type'] == 'LIABILITI') $kewajiban = $row['total_balance'];
        if($row['account_type'] == 'EKUITI') $ekuitas = $row['total_balance'];
        if($row['account_type'] == 'PENDAPATAN') $pendapatan = $row['total_balance'];
        if($row['account_type'] == 'PERBELANJAAN') $beban = $row['total_balance'];
    }

    // Menghitung Laba Bersih (Net Profit)
    $labaBersih = $pendapatan - $beban;
?>

<style>
    .page-header { margin-bottom: 25px; display: flex; justify-content: space-between; align-items: flex-end;}
    .page-title h1 { font-size: 26px; font-weight: 800; color: var(--text-main); margin-bottom: 5px;}
    
    .btn-primary { background: #0ea5e9; color: #fff; border: none; padding: 12px 20px; border-radius: 12px; font-weight: 800; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; text-decoration: none; box-shadow: 0 4px 15px rgba(14, 165, 233, 0.3); transition: 0.2s;}
    .btn-primary:hover { background: #0284c7; transform: translateY(-2px);}

    /* SUMMARY CARDS */
    .kpi-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 25px;}
    @media (max-width: 1024px) { .kpi-grid { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 640px) { .kpi-grid { grid-template-columns: 1fr; } }

    .kpi-card { background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: 16px; padding: 20px; box-shadow: var(--shadow-card); position: relative; overflow: hidden;}
    .kpi-card::before { content: ''; position: absolute; left: 0; top: 0; height: 100%; width: 4px;}
    .kpi-card.c-aset::before { background: #3b82f6; }
    .kpi-card.c-rev::before { background: #10b981; }
    .kpi-card.c-exp::before { background: #ef4444; }
    .kpi-card.c-profit::before { background: #8b5cf6; }

    .kpi-title { font-size: 12px; font-weight: 800; color: var(--text-muted); text-transform: uppercase; margin-bottom: 10px; display: flex; align-items: center; gap: 8px;}
    .kpi-val { font-size: 24px; font-weight: 900; color: var(--text-main); font-family: 'Space Mono', monospace;}
    .kpi-profit { color: <?= ($labaBersih >= 0) ? '#10b981' : '#ef4444' ?>; }

    /* LAYOUT BOTTOM */
    .bottom-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 20px; }
    @media (max-width: 1024px) { .bottom-grid { grid-template-columns: 1fr; } }

    .bento-card { background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: 16px; padding: 25px; box-shadow: var(--shadow-card); }
    .card-title { font-size: 15px; font-weight: 900; color: var(--text-main); margin-bottom: 20px; display: flex; align-items: center; gap: 8px; border-bottom: 1px dashed var(--border-subtle); padding-bottom: 15px;}

    /* TABLE STYLES */
    table { width: 100%; border-collapse: collapse; white-space: nowrap; }
    th { text-align: left; padding: 12px 15px; font-size: 11px; font-weight: 800; text-transform: uppercase; color: var(--text-muted); border-bottom: 1px solid var(--border-subtle); background: var(--bg-base);}
    td { padding: 12px 15px; border-bottom: 1px solid var(--border-subtle); color: var(--text-main); font-size: 13px; font-weight: 600;}
</style>

<div class="page-header">
    <div class="page-title">
        <h1><i class="ph ph-chart-pie-slice" style="color: #0ea5e9;"></i> Executive Financial Dashboard</h1>
        <p>Ringkasan kesehatan finansial pabrik, aset, dan laba rugi secara real-time.</p>
    </div>
    
    <a href="<?= base_url('/accounting/journal') ?>" class="btn-primary">
        <i class="ph ph-plus-circle"></i> Catat Jurnal Baru
    </a>
</div>

<div class="kpi-grid">
    <div class="kpi-card c-aset">
        <div class="kpi-title"><i class="ph ph-bank" style="font-size:18px; color:#3b82f6;"></i> Total Aset</div>
        <div class="kpi-val">Rp <?= number_format($aset, 0, ',', '.') ?></div>
    </div>
    <div class="kpi-card c-rev">
        <div class="kpi-title"><i class="ph ph-trend-up" style="font-size:18px; color:#10b981;"></i> Pendapatan Kotor</div>
        <div class="kpi-val">Rp <?= number_format($pendapatan, 0, ',', '.') ?></div>
    </div>
    <div class="kpi-card c-exp">
        <div class="kpi-title"><i class="ph ph-trend-down" style="font-size:18px; color:#ef4444;"></i> Total Beban (HPP + Operasional)</div>
        <div class="kpi-val">Rp <?= number_format($beban, 0, ',', '.') ?></div>
    </div>
    <div class="kpi-card c-profit">
        <div class="kpi-title"><i class="ph ph-scales" style="font-size:18px; color:#8b5cf6;"></i> Laba Bersih (Net Profit)</div>
        <div class="kpi-val kpi-profit">Rp <?= number_format($labaBersih, 0, ',', '.') ?></div>
    </div>
</div>

<div class="bottom-grid">
    <div class="bento-card">
        <div class="card-title"><i class="ph ph-chart-bar"></i> Visualisasi Arus Kas & Laba</div>
        <div style="position: relative; height: 300px; width: 100%;">
            <canvas id="financeChart"></canvas>
        </div>
    </div>

    <div class="bento-card">
        <div class="card-title"><i class="ph ph-clock-counter-clockwise"></i> Histori Jurnal Terakhir</div>
        <div style="overflow-x: auto;">
            <table>
                <tbody>
                    <?php if(empty($recent_journals)): ?>
                        <tr><td style="text-align: center; color: var(--text-muted); padding: 30px;">Belum ada transaksi jurnal.</td></tr>
                    <?php else: ?>
                        <?php foreach($recent_journals as $jrn): ?>
                            <tr>
                                <td>
                                    <div style="font-weight: 800; font-size: 13px; color: #0ea5e9; font-family: monospace;"><?= esc($jrn['journal_number']) ?></div>
                                    <div style="font-size: 11px; color: var(--text-muted);"><?= date('d M Y', strtotime($jrn['transaction_date'])) ?></div>
                                </td>
                                <td>
                                    <div style="font-size: 12px; white-space: normal; line-height: 1.3; font-weight: 700;"><?= esc($jrn['description']) ?></div>
                                </td>
                                <td style="text-align: right; font-weight: 900; font-family: monospace;">
                                    Rp <?= number_format($jrn['total_amount'], 0, ',', '.') ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <a href="<?= base_url('/accounting/journal') ?>" style="display: block; text-align: center; margin-top: 15px; font-size: 12px; font-weight: 800; color: #0ea5e9; text-decoration: none;">Lihat Semua Jurnal &rarr;</a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('financeChart').getContext('2d');
        const isDark = document.documentElement.classList.contains('dark');
        const textColor = isDark ? '#a1a1aa' : '#71717a';

        // Data dinamis dari PHP
        const chartData = {
            labels: ['Pendapatan', 'Beban & HPP', 'Laba Bersih'],
            datasets: [{
                label: 'Nominal (Rp)',
                data: [<?= $pendapatan ?>, <?= $beban ?>, <?= $labaBersih ?>],
                backgroundColor: [
                    'rgba(16, 185, 129, 0.7)', // Hijau utk Pendapatan
                    'rgba(239, 68, 68, 0.7)',  // Merah utk Beban
                    'rgba(139, 92, 246, 0.7)'  // Ungu utk Laba Bersih
                ],
                borderColor: [
                    'rgb(16, 185, 129)',
                    'rgb(239, 68, 68)',
                    'rgb(139, 92, 246)'
                ],
                borderWidth: 2,
                borderRadius: 8
            }]
        };

        new Chart(ctx, {
            type: 'bar',
            data: chartData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let value = context.raw || 0;
                                return 'Rp ' + value.toLocaleString('id-ID');
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: isDark ? 'rgba(255,255,255,0.05)' : 'rgba(0,0,0,0.05)' },
                        ticks: { color: textColor }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { color: textColor, font: { weight: 'bold' } }
                    }
                }
            }
        });
    });
</script>

<?= $this->endSection() ?>