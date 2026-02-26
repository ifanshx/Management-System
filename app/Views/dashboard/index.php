<?= $this->extend('layout/template') ?>

<?= $this->section('content') ?>

<style>
    .page-header { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 25px; }
    .page-title h1 { font-size: 26px; font-weight: 800; color: var(--text-main); letter-spacing: -1px; margin-bottom: 5px; }
    .page-title p { font-size: 13px; color: var(--text-muted); }

    /* KPI Cards (4 Columns) */
    .kpi-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 25px; }
    .kpi-card { background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: 20px; padding: 20px; box-shadow: var(--shadow-card); transition: 0.3s; position: relative; overflow: hidden; }
    .kpi-card:hover { transform: translateY(-4px); border-color: var(--accent-main); }
    
    .kpi-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; }
    .kpi-title { font-size: 13px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; }
    .kpi-icon { width: 40px; height: 40px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 20px; }
    
    .kpi-value { font-size: 28px; font-weight: 800; color: var(--text-main); line-height: 1; letter-spacing: -1px; margin-bottom: 8px; }
    .kpi-desc { font-size: 12px; font-weight: 600; display: flex; align-items: center; gap: 4px; }
    
    /* Custom Colors */
    .c-blue { background: rgba(56, 189, 248, 0.1); color: #38bdf8; }
    .c-green { background: rgba(16, 185, 129, 0.1); color: #10b981; }
    .c-orange { background: rgba(245, 158, 11, 0.1); color: #f59e0b; }
    .c-purple { background: rgba(168, 85, 247, 0.1); color: #a855f7; }
    .text-success { color: #10b981; } .text-danger { color: #ef4444; }

    /* Middle Section (Chart & Quick Actions) */
    .middle-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 20px; margin-bottom: 25px; }
    .bento-box { background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: 20px; padding: 24px; box-shadow: var(--shadow-card); }
    .box-title { font-size: 16px; font-weight: 800; color: var(--text-main); margin-bottom: 20px; display: flex; align-items: center; gap: 8px; border-bottom: 1px solid var(--border-subtle); padding-bottom: 12px; }
    
    .action-btn { display: flex; align-items: center; justify-content: space-between; background: var(--bg-base); border: 1px solid var(--border-subtle); color: var(--text-main); padding: 14px 16px; border-radius: 12px; font-weight: 600; font-size: 13px; text-decoration: none; transition: 0.3s; margin-bottom: 10px; }
    .action-btn:hover { background: var(--accent-main); color: #fff; border-color: var(--accent-main); }
    .action-btn i.arrow { font-size: 16px; opacity: 0.5; }

    /* Bottom Section (Mini Tables/Lists) */
    .bottom-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
    .status-list { display: flex; flex-direction: column; gap: 12px; }
    .status-item { display: flex; justify-content: space-between; align-items: center; padding: 12px 16px; background: var(--bg-base); border: 1px solid var(--border-subtle); border-radius: 12px; font-size: 13px; }
    .status-item .label { font-weight: 700; color: var(--text-main); display: flex; align-items: center; gap: 8px; }
    .status-item .val { font-family: monospace; font-weight: 700; font-size: 14px; }

    @media (max-width: 1200px) { .kpi-grid { grid-template-columns: 1fr 1fr; } }
    @media (max-width: 992px) { .middle-grid, .bottom-grid { grid-template-columns: 1fr; } }
</style>

<div class="page-header">
    <div class="page-title">
        <h1>Command Center Pabrik</h1>
        <p>Ringkasan performa Produksi, Penjualan, Gudang, dan SDM secara real-time.</p>
    </div>
    <div style="background: var(--bg-surface); border: 1px solid var(--border-subtle); padding: 10px 16px; border-radius: 12px; font-size: 13px; font-weight: 700; color: var(--text-main);">
        <i class="ph ph-calendar-blank" style="color: var(--accent-main); margin-right: 5px;"></i> <?= date('d F Y') ?>
    </div>
</div>

<div class="kpi-grid">
    <div class="kpi-card">
        <div class="kpi-header">
            <div class="kpi-title">Pendapatan Bulan Ini</div>
            <div class="kpi-icon c-green"><i class="ph ph-money"></i></div>
        </div>
        <div class="kpi-value">Rp <?= number_format($sales['revenue_this_month'] / 1000000, 1) ?>M</div>
        <div class="kpi-desc text-success"><i class="ph ph-trend-up"></i> +12% dari bulan lalu</div>
    </div>

    <div class="kpi-card">
        <div class="kpi-header">
            <div class="kpi-title">Output Produksi Hari Ini</div>
            <div class="kpi-icon c-orange"><i class="ph ph-fire"></i></div>
        </div>
        <div class="kpi-value"><?= $production['completed_today'] ?> <span style="font-size: 16px; color: var(--text-muted);">Pcs</span></div>
        <div class="kpi-desc <?= ($production['target_achieved'] >= 80) ? 'text-success' : 'text-danger' ?>">
            <i class="ph ph-target"></i> <?= $production['target_achieved'] ?>% Target Tercapai
        </div>
    </div>

    <div class="kpi-card">
        <div class="kpi-header">
            <div class="kpi-title">Pesanan Tertunda (PO)</div>
            <div class="kpi-icon c-purple"><i class="ph ph-shopping-cart-simple"></i></div>
        </div>
        <div class="kpi-value"><?= $sales['pending_orders'] ?> <span style="font-size: 16px; color: var(--text-muted);">Antrean</span></div>
        <div class="kpi-desc text-danger"><i class="ph ph-warning-circle"></i> Butuh percepatan rilis</div>
    </div>

    <div class="kpi-card">
        <div class="kpi-header">
            <div class="kpi-title">Total Pekerja Aktif</div>
            <div class="kpi-icon c-blue"><i class="ph ph-users-three"></i></div>
        </div>
        <div class="kpi-value"><?= $totalEmployees ?> <span style="font-size: 16px; color: var(--text-muted);">Orang</span></div>
        <div class="kpi-desc" style="color: var(--text-muted);"><i class="ph ph-wallet"></i> Beban: Rp <?= number_format($totalPayrollCost/1000000, 1) ?>M</div>
    </div>
</div>

<div class="middle-grid">
    <div class="bento-box">
        <div class="box-title"><i class="ph ph-chart-line-up"></i> Tren Produksi vs Penjualan (Minggu Ini)</div>
        <div style="height: 300px; width: 100%;">
            <canvas id="factoryChart"></canvas>
        </div>
    </div>

    <div class="bento-box">
        <div class="box-title"><i class="ph ph-lightning"></i> Pintasan Modul ERP</div>
        <a href="<?= base_url('/employee') ?>" class="action-btn">
            <span style="display:flex; align-items:center; gap:10px;"><i class="ph ph-users" style="color:#38bdf8;"></i> Kelola Karyawan (HRD)</span>
            <i class="ph ph-caret-right arrow"></i>
        </a>
        <a href="<?= base_url('/payroll') ?>" class="action-btn">
            <span style="display:flex; align-items:center; gap:10px;"><i class="ph ph-calculator" style="color:#10b981;"></i> Proses Slip Gaji</span>
            <i class="ph ph-caret-right arrow"></i>
        </a>
        <a href="#" class="action-btn">
            <span style="display:flex; align-items:center; gap:10px;"><i class="ph ph-clipboard-text" style="color:#f59e0b;"></i> Buat SPK Produksi</span>
            <i class="ph ph-caret-right arrow"></i>
        </a>
        <a href="#" class="action-btn" style="margin-bottom:0;">
            <span style="display:flex; align-items:center; gap:10px;"><i class="ph ph-package" style="color:#a855f7;"></i> Cek Stok Gudang</span>
            <i class="ph ph-caret-right arrow"></i>
        </a>
    </div>
</div>

<div class="bottom-grid">
    <div class="bento-box">
        <div class="box-title"><i class="ph ph-factory"></i> Status Lantai Produksi (Hari Ini)</div>
        <div class="status-list">
            <div class="status-item">
                <div class="label"><i class="ph ph-engine" style="color:#f59e0b;"></i> SPK Sedang Berjalan</div>
                <div class="val"><?= $production['active_spk'] ?> Dokumen</div>
            </div>
            <div class="status-item">
                <div class="label"><i class="ph ph-warning" style="color:#ef4444;"></i> Tingkat Defect / Cacat</div>
                <div class="val" style="color:#ef4444;"><?= $production['defect_rate'] ?> %</div>
            </div>
            <div class="status-item">
                <div class="label"><i class="ph ph-star" style="color:#10b981;"></i> Produk Terlaris</div>
                <div class="val" style="font-family: inherit; font-size:13px;"><?= $sales['top_product'] ?></div>
            </div>
        </div>
    </div>

    <div class="bento-box">
        <div class="box-title"><i class="ph ph-warehouse"></i> Status Gudang & Logistik</div>
        <div class="status-list">
            <div class="status-item">
                <div class="label"><i class="ph ph-truck" style="color:#38bdf8;"></i> Material Masuk (Inbound)</div>
                <div class="val"><?= $warehouse['inbound_today'] ?> Truk</div>
            </div>
            <div class="status-item">
                <div class="label"><i class="ph ph-package" style="color:#a855f7;"></i> Pengiriman Keluar (Outbound)</div>
                <div class="val"><?= $warehouse['outbound_today'] ?> Truk</div>
            </div>
            <div class="status-item">
                <div class="label"><i class="ph ph-siren" style="color:#ef4444;"></i> Stok Material Menipis</div>
                <div class="val" style="color:#ef4444;"><?= $warehouse['low_stock_items'] ?> Item</div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Konfigurasi Mode Gelap
    const isDark = document.documentElement.classList.contains('dark');
    const chartTextColor = isDark ? '#a1a1aa' : '#71717a';
    const gridColor = isDark ? 'rgba(255,255,255,0.05)' : 'rgba(0,0,0,0.05)';

    // Data dari Controller
    const labels = <?= json_encode($chart['labels']) ?>;
    const dataProduksi = <?= json_encode($chart['produksi']) ?>;
    const dataPenjualan = <?= json_encode($chart['penjualan']) ?>;

    const ctx = document.getElementById('factoryChart').getContext('2d');
    new Chart(ctx, {
        type: 'line', // Grafik garis dengan area (filled)
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Produksi (Pcs)',
                    data: dataProduksi,
                    borderColor: '#f59e0b', // Orange
                    backgroundColor: 'rgba(245, 158, 11, 0.1)',
                    borderWidth: 3,
                    tension: 0.4, // Melengkung halus
                    fill: true
                },
                {
                    label: 'Penjualan (Pcs)',
                    data: dataPenjualan,
                    borderColor: '#10b981', // Green
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    borderWidth: 3,
                    tension: 0.4,
                    fill: true
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { position: 'top', labels: { color: chartTextColor, font: { family: 'Plus Jakarta Sans', weight: '600' } } }
            },
            scales: {
                y: { beginAtZero: true, grid: { color: gridColor }, ticks: { color: chartTextColor } },
                x: { grid: { display: false }, ticks: { color: chartTextColor, font: { family: 'Plus Jakarta Sans', weight: '600' } } }
            }
        }
    });
</script>

<?= $this->endSection() ?>