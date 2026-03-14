<?= $this->extend('layout/template') ?>
<?= $this->section('content') ?>

<?php
// --- FUNGSI PHP CERDAS UNTUK MENYINGKAT ANGKA BESAR ---
if (!function_exists('format_compact')) {
    function format_compact($angka) {
        $angka = (float)$angka;
        if ($angka >= 1000000000) {
            return number_format($angka / 1000000000, 2, ',', '.') . ' M'; 
        } elseif ($angka >= 1000000) {
            return number_format($angka / 1000000, 2, ',', '.') . ' Jt'; 
        } else {
            return number_format($angka, 0, ',', '.'); 
        }
    }
}
?>

<style>
    /* =========================================================
       1. CORE LAYOUT & TYPOGRAPHY
       ========================================================= */
    .dashboard-header { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 30px; flex-wrap: wrap; gap: 15px;}
    .header-greeting { display: flex; align-items: center; gap: 12px; margin-bottom: 8px;}
    .greeting-badge { background: rgba(59, 130, 246, 0.1); color: #3b82f6; padding: 6px 12px; border-radius: 100px; font-size: 11px; font-weight: 900; text-transform: uppercase; letter-spacing: 0.5px; border: 1px solid rgba(59, 130, 246, 0.2);}
    
    .page-title h1 { font-size: 32px; font-weight: 900; color: var(--text-main); letter-spacing: -1px; margin: 0;}
    .page-title p { font-size: 14px; color: var(--text-muted); font-weight: 500; margin: 5px 0 0 0;}

    .date-indicator { background: var(--bg-surface); border: 1px solid var(--border-subtle); padding: 12px 20px; border-radius: 14px; font-size: 13px; font-weight: 800; color: var(--text-main); display: flex; align-items: center; gap: 10px; box-shadow: 0 4px 15px -5px rgba(0,0,0,0.05);}

    /* =========================================================
       2. BENTO GRID SYSTEM
       ========================================================= */
    .bento-row-1 { display: grid; grid-template-columns: repeat(4, 1fr); gap: 24px; margin-bottom: 30px; }
    .bento-row-2 { display: grid; grid-template-columns: 2.5fr 1fr; gap: 24px; margin-bottom: 30px; }
    .bento-row-3 { display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; }

    @media (max-width: 1200px) {
        .bento-row-1 { grid-template-columns: repeat(2, 1fr); }
        .bento-row-2 { grid-template-columns: 1fr; }
    }
    @media (max-width: 768px) {
        .bento-row-1, .bento-row-3 { grid-template-columns: 1fr; }
    }

    .bento-card { background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: 24px; padding: 30px; box-shadow: 0 10px 30px -10px rgba(0,0,0,0.05); transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1); position: relative; overflow: hidden;}
    .bento-card:hover { transform: translateY(-4px); box-shadow: 0 20px 40px -10px rgba(0,0,0,0.08); border-color: rgba(59, 130, 246, 0.3);}
    
    .card-title-main { font-size: 16px; font-weight: 900; color: var(--text-main); margin-bottom: 25px; display: flex; align-items: center; gap: 12px; }

    /* =========================================================
       3. SMART KPI CARDS (PREMIUM FINANCIAL METRICS)
       ========================================================= */
    .kpi-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px;}
    .kpi-icon-wrapper { width: 46px; height: 46px; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 24px; box-shadow: inset 0 2px 5px rgba(255,255,255,0.2), 0 4px 15px rgba(0,0,0,0.1);}
    
    .kpi-label { font-size: 12px; font-weight: 800; color: var(--text-muted); line-height: 1.4; text-transform: uppercase; letter-spacing: 0.5px;}
    
    .kpi-value-compact { font-size: 32px; font-weight: 900; color: var(--text-main); font-family: 'Space Mono', monospace; letter-spacing: -1.5px; line-height: 1.1;}
    .kpi-value-exact { font-size: 11px; font-weight: 800; color: var(--text-muted); font-family: 'Space Mono', monospace; margin-top: 8px; display: inline-flex; align-items: center; gap: 6px; background: var(--bg-base); padding: 4px 10px; border-radius: 6px; border: 1px dashed var(--border-subtle);}

    .kpi-blue .kpi-icon-wrapper { background: linear-gradient(135deg, #3b82f6, #2563eb); color: white; }
    .kpi-green .kpi-icon-wrapper { background: linear-gradient(135deg, #10b981, #059669); color: white; }
    .kpi-orange .kpi-icon-wrapper { background: linear-gradient(135deg, #f59e0b, #d97706); color: white; }
    .kpi-purple .kpi-icon-wrapper { background: linear-gradient(135deg, #8b5cf6, #7c3aed); color: white; }

    /* =========================================================
       4. QUICK ACTIONS
       ========================================================= */
    .action-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
    .action-btn { display: flex; flex-direction: column; justify-content: center; padding: 20px; border-radius: 18px; text-decoration: none; transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1); background: var(--bg-base); border: 1px solid var(--border-subtle); position: relative; overflow: hidden; text-align: center;}
    .action-btn i { font-size: 32px; margin-bottom: 12px; z-index: 2; transition: 0.3s;}
    .action-btn span { font-size: 13px; font-weight: 900; color: var(--text-main); z-index: 2; letter-spacing: -0.5px;}
    
    .action-btn::before { content: ''; position: absolute; inset: 0; opacity: 0; transition: 0.3s; z-index: 1;}
    .action-btn:hover { transform: translateY(-5px); border-color: transparent;}
    .action-btn:hover::before { opacity: 1; }
    .action-btn:hover i { transform: scale(1.1); }

    .a-blue:hover::before { background: linear-gradient(135deg, rgba(59, 130, 246, 0.15), rgba(59, 130, 246, 0.02)); }
    .a-blue i { color: #3b82f6; } .a-blue:hover { border-color: #3b82f6; box-shadow: 0 10px 20px rgba(59, 130, 246, 0.15);}
    
    .a-orange:hover::before { background: linear-gradient(135deg, rgba(245, 158, 11, 0.15), rgba(245, 158, 11, 0.02)); }
    .a-orange i { color: #f59e0b; } .a-orange:hover { border-color: #f59e0b; box-shadow: 0 10px 20px rgba(245, 158, 11, 0.15);}
    
    .a-purple:hover::before { background: linear-gradient(135deg, rgba(139, 92, 246, 0.15), rgba(139, 92, 246, 0.02)); }
    .a-purple i { color: #8b5cf6; } .a-purple:hover { border-color: #8b5cf6; box-shadow: 0 10px 20px rgba(139, 92, 246, 0.15);}
    
    .a-green:hover::before { background: linear-gradient(135deg, rgba(16, 185, 129, 0.15), rgba(16, 185, 129, 0.02)); }
    .a-green i { color: #10b981; } .a-green:hover { border-color: #10b981; box-shadow: 0 10px 20px rgba(16, 185, 129, 0.15);}

    /* =========================================================
       5. STATUS LISTS
       ========================================================= */
    .status-list { display: flex; flex-direction: column; gap: 12px; }
    .status-item { display: flex; justify-content: space-between; align-items: center; padding: 18px; background: var(--bg-base); border-radius: 16px; border: 1px solid transparent; transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);}
    .status-item:hover { border-color: var(--border-subtle); background: var(--bg-surface); transform: translateX(5px); box-shadow: 0 4px 15px rgba(0,0,0,0.03);}
    
    .status-left { display: flex; align-items: center; gap: 15px; }
    .status-icon { width: 36px; height: 36px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 18px; background: var(--bg-surface); border: 1px solid var(--border-subtle); color: var(--text-muted);}
    .status-label { font-size: 13px; font-weight: 900; color: var(--text-main); letter-spacing: -0.5px;}
    .status-sub { font-size: 11px; color: var(--text-muted); font-weight: 600; margin-top: 4px;}
    
    .status-val { font-size: 15px; font-weight: 900; font-family: 'Space Mono', monospace; color: var(--text-main); background: var(--bg-surface); padding: 6px 12px; border-radius: 10px; border: 1px solid var(--border-subtle); box-shadow: 0 2px 5px rgba(0,0,0,0.02);}
    .val-danger { color: #ef4444; background: rgba(239, 68, 68, 0.1); border-color: rgba(239, 68, 68, 0.2);}
</style>

<div class="dashboard-header">
    <div class="page-title">
        <div class="header-greeting">
            <span class="greeting-badge"><i class="ph-fill ph-shield-check"></i> Command Center Aktif</span>
        </div>
        <h1>Executive Dashboard</h1>
        <p>Ringkasan analitik finansial, pergerakan manufaktur, dan statistik operasional harian.</p>
    </div>
    <div class="date-indicator">
        <i class="ph-fill ph-calendar-blank" style="color: #8b5cf6; font-size: 22px;"></i>
        <div>
            <div style="font-size: 10px; color: var(--text-muted); text-transform: uppercase;">Laporan Periode</div>
            <div style="font-weight: 900;"><?= $currentMonthName ?></div>
        </div>
    </div>
</div>

<div class="bento-row-1">
    
    <div class="bento-card kpi-blue">
        <div class="kpi-header">
            <div class="kpi-label">Pendapatan Kotor<br><span style="font-size:10px; color:var(--text-muted); font-weight:600; text-transform:none;">(Gross Revenue)</span></div>
            <div class="kpi-icon-wrapper"><i class="ph-bold ph-trend-up"></i></div>
        </div>
        <div class="kpi-value-compact">Rp <?= format_compact($finance['revenue']) ?></div>
        <div class="kpi-value-exact" title="Nominal Detail">
            <i class="ph-fill ph-info"></i> Rp <?= number_format($finance['revenue'], 0, ',', '.') ?>
        </div>
    </div>

    <div class="bento-card kpi-green">
        <div class="kpi-header">
            <div class="kpi-label">Laba Bersih<br><span style="font-size:10px; color:var(--text-muted); font-weight:600; text-transform:none;">(Net Profit)</span></div>
            <div class="kpi-icon-wrapper"><i class="ph-bold ph-scales"></i></div>
        </div>
        <div class="kpi-value-compact">Rp <?= format_compact($finance['profit']) ?></div>
        <div class="kpi-value-exact" title="Nominal Detail">
            <i class="ph-fill ph-info"></i> Rp <?= number_format($finance['profit'], 0, ',', '.') ?>
        </div>
    </div>

    <div class="bento-card kpi-orange">
        <div class="kpi-header">
            <div class="kpi-label">Valuasi Gudang<br><span style="font-size:10px; color:var(--text-muted); font-weight:600; text-transform:none;">(Total Inventory)</span></div>
            <div class="kpi-icon-wrapper"><i class="ph-bold ph-package"></i></div>
        </div>
        <div class="kpi-value-compact">Rp <?= format_compact($finance['inventory_value']) ?></div>
        <div class="kpi-value-exact" title="Nominal Detail">
            <i class="ph-fill ph-info"></i> Rp <?= number_format($finance['inventory_value'], 0, ',', '.') ?>
        </div>
    </div>

    <div class="bento-card kpi-purple">
        <div class="kpi-header">
            <div class="kpi-label">Total Aset Likuid<br><span style="font-size:10px; color:var(--text-muted); font-weight:600; text-transform:none;">(Kas Tunai & ATM)</span></div>
            <div class="kpi-icon-wrapper"><i class="ph-bold ph-bank"></i></div>
        </div>
        <div class="kpi-value-compact">Rp <?= format_compact($finance['assets']) ?></div>
        <div class="kpi-value-exact" title="Nominal Detail">
            <i class="ph-fill ph-info"></i> Rp <?= number_format($finance['assets'], 0, ',', '.') ?>
        </div>
    </div>

</div>

<div class="bento-row-2">
    <div class="bento-card" style="padding-bottom: 20px;">
        <div class="card-title-main">
            <div style="background: rgba(59, 130, 246, 0.1); color: #3b82f6; padding: 8px; border-radius: 10px;"><i class="ph-bold ph-chart-line-up" style="font-size: 20px;"></i></div>
            <div style="flex: 1;">
                <div style="font-size: 18px; margin-bottom: 2px;">Tren Arus Kas Bulanan</div>
                <div style="font-size: 12px; color: var(--text-muted); font-weight: 600;">Perbandingan Pendapatan vs Beban Pengeluaran</div>
            </div>
            <span style="font-size: 11px; font-weight: 800; color: var(--text-muted); background: var(--bg-base); padding: 6px 12px; border-radius: 8px; border: 1px dashed var(--border-subtle);">Skala Nominal</span>
        </div>
        <div style="height: 340px; width: 100%; margin-top: 10px; position: relative;">
            <canvas id="financeChart"></canvas>
        </div>
    </div>

    <div class="bento-card">
        <div class="card-title-main">
            <div style="background: rgba(245, 158, 11, 0.1); color: #f59e0b; padding: 8px; border-radius: 10px;"><i class="ph-bold ph-lightning" style="font-size: 20px;"></i></div>
            Jalan Pintas Aksi
        </div>
        <div class="action-grid">
            <a href="<?= base_url('/accounting/journal') ?>" class="action-btn a-blue">
                <i class="ph-fill ph-notebook"></i>
                <span>Catat Jurnal</span>
            </a>
            <a href="<?= base_url('/production') ?>" class="action-btn a-orange">
                <i class="ph-fill ph-factory"></i>
                <span>Cetak SPK</span>
            </a>
            <a href="<?= base_url('/procurement') ?>" class="action-btn a-purple">
                <i class="ph-fill ph-truck"></i>
                <span>Pesan Material</span>
            </a>
            <a href="<?= base_url('/sales/offline') ?>" class="action-btn a-green">
                <i class="ph-fill ph-monitor"></i>
                <span>Mesin Kasir</span>
            </a>
        </div>
    </div>
</div>

<div class="bento-row-3">
    <div class="bento-card">
        <div class="card-title-main" style="border-bottom: 2px dashed var(--border-subtle); padding-bottom: 20px;">
            <i class="ph-fill ph-engine" style="color: #f59e0b; font-size: 24px;"></i> Status Manufaktur
        </div>
        <div class="status-list">
            <div class="status-item">
                <div class="status-left">
                    <div class="status-icon" style="color: #3b82f6;"><i class="ph-bold ph-clipboard-text"></i></div>
                    <div>
                        <div class="status-label">SPK Berjalan</div>
                        <div class="status-sub">Sedang diproduksi</div>
                    </div>
                </div>
                <div class="status-val"><?= $production['active_spk'] ?> <span style="font-size: 11px; color: var(--text-muted); font-family: 'Plus Jakarta Sans', sans-serif;">Tiket</span></div>
            </div>
            <div class="status-item">
                <div class="status-left">
                    <div class="status-icon" style="color: #ef4444;"><i class="ph-bold ph-warning-circle"></i></div>
                    <div>
                        <div class="status-label">Stok Kritis</div>
                        <div class="status-sub">Butuh re-stock Gudang</div>
                    </div>
                </div>
                <div class="status-val <?= $production['low_stock'] > 0 ? 'val-danger' : '' ?>"><?= $production['low_stock'] ?> <span style="font-size: 11px; color: inherit; font-family: 'Plus Jakarta Sans', sans-serif;">Item</span></div>
            </div>
        </div>
    </div>

    <div class="bento-card">
        <div class="card-title-main" style="border-bottom: 2px dashed var(--border-subtle); padding-bottom: 20px;">
            <i class="ph-fill ph-handshake" style="color: #10b981; font-size: 24px;"></i> Distribusi B2B
        </div>
        <div class="status-list">
            <div class="status-item">
                <div class="status-left">
                    <div class="status-icon" style="color: #10b981;"><i class="ph-bold ph-users-three"></i></div>
                    <div>
                        <div class="status-label">Nilai Grosir</div>
                        <div class="status-sub">Total transaksi B2B</div>
                    </div>
                </div>
                <div class="status-val" style="font-size: 14px;" title="Rp <?= number_format($sales['b2b_revenue'], 0, ',', '.') ?>">
                    Rp <?= format_compact($sales['b2b_revenue']) ?>
                </div>
            </div>
            <div class="status-item">
                <div class="status-left">
                    <div class="status-icon" style="color: #f59e0b;"><i class="ph-bold ph-hourglass-high"></i></div>
                    <div>
                        <div class="status-label">Piutang Gantung</div>
                        <div class="status-sub">Menunggu pelunasan toko</div>
                    </div>
                </div>
                <div class="status-val" style="color: #f59e0b;"><?= $sales['pending_b2b'] ?> <span style="font-size: 11px; color: inherit; font-family: 'Plus Jakarta Sans', sans-serif;">Nota</span></div>
            </div>
        </div>
    </div>

    <div class="bento-card">
        <div class="card-title-main" style="border-bottom: 2px dashed var(--border-subtle); padding-bottom: 20px;">
            <i class="ph-fill ph-users" style="color: #8b5cf6; font-size: 24px;"></i> HRD & E-Commerce
        </div>
        <div class="status-list">
            <div class="status-item">
                <div class="status-left">
                    <div class="status-icon" style="color: #ef4444;"><i class="ph-bold ph-wallet"></i></div>
                    <div>
                        <div class="status-label">Beban Payroll</div>
                        <div class="status-sub">Total gaji terbayar bulan ini</div>
                    </div>
                </div>
                <div class="status-val" style="font-size: 14px; color: #ef4444;" title="Rp <?= number_format($totalPayrollCost, 0, ',', '.') ?>">
                    Rp <?= format_compact($totalPayrollCost) ?>
                </div>
            </div>
            <div class="status-item">
                <div class="status-left">
                    <div class="status-icon" style="color: #ec4899;"><i class="ph-bold ph-shopping-bag"></i></div>
                    <div>
                        <div class="status-label">Shopee Sync</div>
                        <div class="status-sub">Toko E-Commerce Terhubung</div>
                    </div>
                </div>
                <div class="status-val" style="color: #ec4899;"><?= $sales['active_shops'] ?> <span style="font-size: 11px; color: inherit; font-family: 'Plus Jakarta Sans', sans-serif;">Toko</span></div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    const isDark = document.documentElement.classList.contains('dark');
    const textColor = isDark ? '#a1a1aa' : '#71717a';
    const gridColor = isDark ? 'rgba(255,255,255,0.06)' : 'rgba(0,0,0,0.04)';

    const ctx = document.getElementById('financeChart').getContext('2d');
    
    // Gradient Setup yang Jauh Lebih Halus (Fade to bottom)
    let gradientBlue = ctx.createLinearGradient(0, 0, 0, 350);
    gradientBlue.addColorStop(0, 'rgba(59, 130, 246, 0.6)');
    gradientBlue.addColorStop(0.5, 'rgba(59, 130, 246, 0.2)');
    gradientBlue.addColorStop(1, 'rgba(59, 130, 246, 0.0)');

    let gradientRed = ctx.createLinearGradient(0, 0, 0, 350);
    gradientRed.addColorStop(0, 'rgba(239, 68, 68, 0.6)');
    gradientRed.addColorStop(0.5, 'rgba(239, 68, 68, 0.2)');
    gradientRed.addColorStop(1, 'rgba(239, 68, 68, 0.0)');

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?= json_encode($chart['labels']) ?>,
            datasets: [
                {
                    label: 'Pendapatan Kotor',
                    data: <?= json_encode($chart['pendapatan']) ?>,
                    borderColor: '#3b82f6',
                    backgroundColor: gradientBlue,
                    borderWidth: 3, 
                    tension: 0.4, // Kunci: Garis Melengkung Elegan (Spline)
                    fill: true,
                    pointBackgroundColor: '#ffffff',
                    pointBorderColor: '#3b82f6',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    pointHoverBackgroundColor: '#3b82f6',
                    pointHoverBorderColor: '#ffffff',
                    pointHoverBorderWidth: 3
                },
                {
                    label: 'Beban Operasional',
                    data: <?= json_encode($chart['beban']) ?>,
                    borderColor: '#ef4444',
                    backgroundColor: gradientRed,
                    borderWidth: 3, 
                    tension: 0.4, // Kunci: Garis Melengkung Elegan (Spline)
                    fill: true,
                    pointBackgroundColor: '#ffffff',
                    pointBorderColor: '#ef4444',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    pointHoverBackgroundColor: '#ef4444',
                    pointHoverBorderColor: '#ffffff',
                    pointHoverBorderWidth: 3
                }
            ]
        },
        options: {
            responsive: true, 
            maintainAspectRatio: false,
            layout: {
                padding: { top: 10, right: 10, bottom: 0, left: 0 }
            },
            interaction: { 
                mode: 'index', 
                intersect: false 
            },
            plugins: { 
                legend: { 
                    position: 'top', 
                    align: 'end',
                    labels: { 
                        color: textColor, 
                        usePointStyle: true,
                        pointStyle: 'circle', // Titik legend bulat rapi
                        boxWidth: 8,
                        boxHeight: 8,
                        padding: 20,
                        font: { family: 'Plus Jakarta Sans', weight: 'bold', size: 12 } 
                    } 
                },
                tooltip: {
                    backgroundColor: isDark ? 'rgba(24, 24, 27, 0.95)' : 'rgba(255, 255, 255, 0.95)',
                    titleColor: isDark ? '#fff' : '#000',
                    bodyColor: isDark ? '#d4d4d8' : '#3f3f46',
                    borderColor: isDark ? 'rgba(255,255,255,0.1)' : 'rgba(0,0,0,0.1)',
                    borderWidth: 1,
                    padding: 16,
                    boxPadding: 8,
                    cornerRadius: 12,
                    usePointStyle: true,
                    titleFont: { family: 'Plus Jakarta Sans', size: 14, weight: 'bold' },
                    bodyFont: { family: 'Space Mono', size: 13, weight: 'normal' },
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) label += ' : ';
                            if (context.parsed.y !== null) {
                                // Tooltip menunjukkan nilai rupiah utuh
                                label += 'Rp ' + new Intl.NumberFormat('id-ID').format(context.parsed.y);
                            }
                            return label;
                        }
                    }
                }
            },
            scales: {
                y: { 
                    beginAtZero: true,
                    border: { display: false }, // Hilangkan garis tegak paling pinggir
                    grid: { 
                        color: gridColor, 
                        drawTicks: false, // Hilangkan garis centang kecil
                        tickLength: 0
                    }, 
                    ticks: { 
                        color: textColor, 
                        padding: 10,
                        font: {family: 'Space Mono', size: 11, weight: 'bold'},
                        // Konversi angka Y-Axis agar ringkas (M/Jt/Rb)
                        callback: function(value) {
                            if (value === 0) return '0';
                            if (value >= 1000000000) return (value / 1000000000) + ' M';
                            if (value >= 1000000) return (value / 1000000) + ' Jt';
                            if (value >= 1000) return (value / 1000) + ' Rb';
                            return value;
                        }
                    } 
                },
                x: { 
                    border: { display: false }, // Hilangkan garis alas dasar
                    grid: { display: false, drawTicks: false }, 
                    ticks: { 
                        color: textColor, 
                        padding: 10,
                        font: {family: 'Plus Jakarta Sans', weight: 'bold', size: 12} 
                    } 
                }
            }
        }
    });
</script>

<?= $this->endSection() ?>