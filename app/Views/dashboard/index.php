<?= $this->extend('layout/template') ?>
<?= $this->section('content') ?>

<style>
    .page-header { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 25px; }
    .page-title h1 { font-size: 26px; font-weight: 900; color: var(--text-main); letter-spacing: -0.5px; margin-bottom: 5px; display: flex; align-items: center; gap: 10px;}
    .page-title p { font-size: 13px; color: var(--text-muted); font-weight: 500;}

    /* KPI Cards - Top Level */
    .kpi-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 25px; }
    .kpi-card { background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: 20px; padding: 20px; box-shadow: var(--shadow-card); position: relative; overflow: hidden; display: flex; flex-direction: column; justify-content: space-between; min-height: 140px;}
    .kpi-card::before { content: ''; position: absolute; left: 0; top: 0; height: 100%; width: 4px; border-radius: 4px 0 0 4px;}
    
    .kpi-card.c-profit::before { background: #10b981; }
    .kpi-card.c-rev::before { background: #3b82f6; }
    .kpi-card.c-cost::before { background: #ef4444; }
    .kpi-card.c-asset::before { background: #8b5cf6; }

    .kpi-header { display: flex; justify-content: space-between; align-items: flex-start; }
    .kpi-title { font-size: 12px; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px;}
    .kpi-icon { width: 36px; height: 36px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 18px; }
    
    .kpi-value { font-size: 26px; font-weight: 900; color: var(--text-main); font-family: 'Space Mono', monospace; margin-top: 15px;}
    
    /* Middle Section */
    .middle-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 20px; margin-bottom: 25px; }
    .bento-box { background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: 20px; padding: 24px; box-shadow: var(--shadow-card); }
    .box-title { font-size: 15px; font-weight: 900; color: var(--text-main); margin-bottom: 20px; display: flex; align-items: center; gap: 8px; border-bottom: 1px dashed var(--border-subtle); padding-bottom: 15px; }
    
    /* Quick Actions */
    .action-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
    .action-btn { display: flex; flex-direction: column; align-items: flex-start; gap: 10px; background: var(--bg-base); border: 1px solid var(--border-subtle); padding: 15px; border-radius: 14px; text-decoration: none; transition: 0.2s; color: var(--text-main); font-weight: 700; font-size: 12px;}
    .action-btn i { font-size: 24px; }
    .action-btn:hover { transform: translateY(-3px); box-shadow: var(--shadow-card); }
    .action-btn.a-blue:hover { border-color: #3b82f6; color: #3b82f6; }
    .action-btn.a-green:hover { border-color: #10b981; color: #10b981; }
    .action-btn.a-orange:hover { border-color: #f59e0b; color: #f59e0b; }
    .action-btn.a-purple:hover { border-color: #a855f7; color: #a855f7; }

    /* Mini Status Lists */
    .status-list { display: flex; flex-direction: column; gap: 10px; }
    .status-item { display: flex; justify-content: space-between; align-items: center; padding: 12px 15px; background: var(--bg-base); border-radius: 12px; font-size: 13px; border: 1px solid var(--border-subtle);}
    .status-label { font-weight: 700; color: var(--text-muted); display: flex; align-items: center; gap: 8px; }
    .status-val { font-weight: 900; color: var(--text-main); }

    @media (max-width: 1024px) { .kpi-grid { grid-template-columns: 1fr 1fr; } .middle-grid { grid-template-columns: 1fr; } }
    @media (max-width: 640px) { .kpi-grid, .action-grid { grid-template-columns: 1fr; } }
</style>

<div class="page-header">
    <div class="page-title">
        <h1><i class="ph ph-presentation-chart" style="color: var(--accent-main);"></i> Executive Command Center</h1>
        <p>Ringkasan performa finansial, manufaktur, dan operasional pabrik terpusat.</p>
    </div>
</div>

<div class="kpi-grid">
    <div class="kpi-card c-profit">
        <div class="kpi-header">
            <div class="kpi-title">Laba Bersih (Net Profit)</div>
            <div class="kpi-icon" style="background: rgba(16, 185, 129, 0.1); color: #10b981;"><i class="ph ph-scales"></i></div>
        </div>
        <div class="kpi-value">Rp <?= number_format($finance['profit'], 0, ',', '.') ?></div>
    </div>

    <div class="kpi-card c-rev">
        <div class="kpi-header">
            <div class="kpi-title">Pendapatan Kotor (Rev)</div>
            <div class="kpi-icon" style="background: rgba(59, 130, 246, 0.1); color: #3b82f6;"><i class="ph ph-trend-up"></i></div>
        </div>
        <div class="kpi-value">Rp <?= number_format($finance['revenue'], 0, ',', '.') ?></div>
    </div>

    <div class="kpi-card c-cost">
        <div class="kpi-header">
            <div class="kpi-title">Beban Gaji Bulan Ini</div>
            <div class="kpi-icon" style="background: rgba(239, 68, 68, 0.1); color: #ef4444;"><i class="ph ph-wallet"></i></div>
        </div>
        <div class="kpi-value">Rp <?= number_format($totalPayrollCost, 0, ',', '.') ?></div>
    </div>

    <div class="kpi-card c-asset">
        <div class="kpi-header">
            <div class="kpi-title">Total Aset (Kas & Stok)</div>
            <div class="kpi-icon" style="background: rgba(139, 92, 246, 0.1); color: #8b5cf6;"><i class="ph ph-bank"></i></div>
        </div>
        <div class="kpi-value">Rp <?= number_format($finance['assets'], 0, ',', '.') ?></div>
    </div>
</div>

<div class="middle-grid">
    <div class="bento-box">
        <div class="box-title"><i class="ph ph-chart-bar"></i> Tren Pendapatan vs Beban (Juta Rp)</div>
        <div style="height: 280px; width: 100%;">
            <canvas id="financeChart"></canvas>
        </div>
    </div>

    <div class="bento-box">
        <div class="box-title"><i class="ph ph-lightning"></i> Jalan Pintas Operasional</div>
        <div class="action-grid">
            <a href="<?= base_url('/accounting/journal') ?>" class="action-btn a-blue">
                <i class="ph ph-books" style="color: #3b82f6;"></i> Catat Jurnal
            </a>
            <a href="<?= base_url('/production') ?>" class="action-btn a-orange">
                <i class="ph ph-factory" style="color: #f59e0b;"></i> Buat SPK Pabrik
            </a>
            <a href="<?= base_url('/procurement') ?>" class="action-btn a-purple">
                <i class="ph ph-truck" style="color: #a855f7;"></i> Beli Bahan (PO)
            </a>
            <a href="<?= base_url('/payroll') ?>" class="action-btn a-green">
                <i class="ph ph-money" style="color: #10b981;"></i> Bayar Gaji
            </a>
        </div>
    </div>
</div>

<div class="kpi-grid" style="grid-template-columns: repeat(3, 1fr);">
    <div class="bento-box" style="padding: 20px;">
        <div class="box-title" style="font-size: 13px;"><i class="ph ph-engine" style="color: #f59e0b;"></i> Status Manufaktur</div>
        <div class="status-list">
            <div class="status-item">
                <span class="status-label"><i class="ph ph-clipboard-text"></i> SPK Berjalan</span>
                <span class="status-val"><?= $production['active_spk'] ?> Tiket</span>
            </div>
            <div class="status-item">
                <span class="status-label"><i class="ph ph-warning-circle"></i> Peringatan Stok Tipis</span>
                <span class="status-val" style="color: #ef4444;"><?= $production['low_stock'] ?> Item</span>
            </div>
        </div>
    </div>

    <div class="bento-box" style="padding: 20px;">
        <div class="box-title" style="font-size: 13px;"><i class="ph ph-handshake" style="color: #10b981;"></i> Status Grosir B2B</div>
        <div class="status-list">
            <div class="status-item">
                <span class="status-label"><i class="ph ph-users-three"></i> Pendapatan Grosir</span>
                <span class="status-val">Rp <?= number_format($sales['b2b_revenue'], 0, ',', '.') ?></span>
            </div>
            <div class="status-item">
                <span class="status-label"><i class="ph ph-hourglass-high"></i> Piutang Belum Lunas</span>
                <span class="status-val" style="color: #f59e0b;"><?= $sales['pending_b2b'] ?> Nota</span>
            </div>
        </div>
    </div>

    <div class="bento-box" style="padding: 20px;">
        <div class="box-title" style="font-size: 13px;"><i class="ph ph-storefront" style="color: #ec4899;"></i> Status Shopee & HRD</div>
        <div class="status-list">
            <div class="status-item">
                <span class="status-label"><i class="ph ph-plugs-connected"></i> Toko Terhubung</span>
                <span class="status-val"><?= $sales['active_shops'] ?> Toko Aktif</span>
            </div>
            <div class="status-item">
                <span class="status-label"><i class="ph ph-identification-badge"></i> Karyawan Aktif</span>
                <span class="status-val"><?= $totalEmployees ?> Orang</span>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const isDark = document.documentElement.classList.contains('dark');
    const textColor = isDark ? '#a1a1aa' : '#71717a';
    const gridColor = isDark ? 'rgba(255,255,255,0.05)' : 'rgba(0,0,0,0.05)';

    const ctx = document.getElementById('financeChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?= json_encode($chart['labels']) ?>,
            datasets: [
                {
                    label: 'Pendapatan (Juta)',
                    data: <?= json_encode($chart['pendapatan']) ?>,
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    borderWidth: 3, tension: 0.4, fill: true
                },
                {
                    label: 'Beban Pokok (Juta)',
                    data: <?= json_encode($chart['beban']) ?>,
                    borderColor: '#ef4444',
                    backgroundColor: 'rgba(239, 68, 68, 0.05)',
                    borderWidth: 3, tension: 0.4, fill: true
                }
            ]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: { legend: { position: 'top', labels: { color: textColor, font: {family: 'Plus Jakarta Sans', weight: 'bold'} } } },
            scales: {
                y: { beginAtZero: true, grid: { color: gridColor }, ticks: { color: textColor } },
                x: { grid: { display: false }, ticks: { color: textColor, font: {weight: 'bold'} } }
            }
        }
    });
</script>

<?= $this->endSection() ?>