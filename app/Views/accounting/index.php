<?= $this->extend('layout/template') ?>
<?= $this->section('content') ?>

<?php
    $aset = 0; $kewajiban = 0; $ekuitas = 0; $pendapatanTotal = 0; $beban = 0;

    foreach($summary as $row) {
        if($row['account_type'] == 'ASET') $aset = $row['total_balance'];
        if($row['account_type'] == 'LIABILITI') $kewajiban = $row['total_balance'];
        if($row['account_type'] == 'EKUITI') $ekuitas = $row['total_balance'];
        if($row['account_type'] == 'PENDAPATAN') $pendapatanTotal = $row['total_balance'];
        if($row['account_type'] == 'PERBELANJAAN') $beban = $row['total_balance'];
    }

    $profitMargin = ($pendapatan > 0) ? ($laba_bersih / $pendapatan) * 100 : 0;

    if (!function_exists('format_compact')) {
        function format_compact($angka) {
            $isNegative = $angka < 0;
            $val = abs((float)$angka);
            
            if ($val >= 1000000000) {
                $res = number_format($val / 1000000000, 2, ',', '.') . ' M'; 
            } elseif ($val >= 1000000) {
                $res = number_format($val / 1000000, 2, ',', '.') . ' Jt'; 
            } else {
                $res = number_format($val, 0, ',', '.'); 
            }
            
            return $isNegative ? '-' . $res : $res;
        }
    }
?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
    :root {
        --brand-blue: #0ea5e9;
        --brand-blue-dark: #0284c7;
        --brand-green: #10b981;
        --brand-red: #ef4444;
        --brand-purple: #8b5cf6;
        --brand-orange: #f59e0b;
        --shadow-card: 0 10px 40px -15px rgba(0,0,0,0.06);
        --shadow-hover: 0 20px 45px -12px rgba(0,0,0,0.10);
        --transition-smooth: all .35s cubic-bezier(.16,1,.3,1);
    }

    .ambient-glow {
        position: absolute;
        top: -180px;
        left: 50%;
        transform: translateX(-50%);
        width: 1000px;
        height: 500px;
        background: radial-gradient(circle, rgba(14, 165, 233, 0.10) 0%, rgba(139, 92, 246, 0.08) 30%, transparent 70%);
        z-index: 0;
        pointer-events: none;
    }
    html.dark .ambient-glow {
        background: radial-gradient(circle, rgba(14, 165, 233, 0.18) 0%, rgba(139, 92, 246, 0.14) 30%, transparent 70%);
    }

    .page-wrapper {
        position: relative;
        z-index: 1;
        animation: fadeSlideUp .6s cubic-bezier(.16,1,.3,1);
    }
    @keyframes fadeSlideUp {
        from { opacity: 0; transform: translateY(20px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    /* =========================
       INFO ALERT (ACCRUAL BASIS)
    ========================= */
    .accrual-alert {
        display: flex;
        align-items: flex-start;
        gap: 15px;
        background: rgba(14, 165, 233, 0.05);
        border: 1px solid rgba(14, 165, 233, 0.2);
        padding: 16px 22px;
        border-radius: 16px;
        margin-bottom: 30px;
    }
    .accrual-alert i {
        color: var(--brand-blue);
        font-size: 24px;
        margin-top: 2px;
    }
    .accrual-alert h4 {
        margin: 0 0 6px 0;
        font-size: 14px;
        font-weight: 900;
        color: var(--brand-blue);
    }
    .accrual-alert p {
        margin: 0;
        font-size: 13px;
        color: var(--text-muted);
        line-height: 1.5;
        font-weight: 600;
    }

    /* =========================
       HEADER
    ========================= */
    .page-header {
        margin-bottom: 24px;
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        flex-wrap: wrap;
        gap: 20px;
    }

    .page-title {
        display: flex;
        align-items: center;
        gap: 18px;
    }

    .title-icon {
        width: 62px;
        height: 62px;
        border-radius: 20px;
        background: linear-gradient(135deg, #0ea5e9, #2563eb, #8b5cf6);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 30px;
        box-shadow: 0 14px 30px -8px rgba(14,165,233,.45);
    }

    .page-title h1 {
        font-size: 34px;
        font-weight: 900;
        color: var(--text-main);
        margin: 0;
        letter-spacing: -1px;
        line-height: 1.1;
    }

    .page-title p {
        font-size: 14px;
        color: var(--text-muted);
        font-weight: 600;
        margin: 7px 0 0 0;
        letter-spacing: -.2px;
    }

    .header-toolbar {
        display: flex;
        align-items: center;
        background: var(--bg-surface);
        padding: 8px;
        border-radius: 18px;
        border: 1px solid var(--border-subtle);
        box-shadow: var(--shadow-card);
        gap: 6px;
        backdrop-filter: blur(12px);
    }

    .btn-tb {
        padding: 11px 17px;
        border-radius: 12px;
        font-size: 13px;
        font-weight: 800;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        text-decoration: none;
        transition: var(--transition-smooth);
        border: none;
        background: transparent;
        font-family: inherit;
    }

    .btn-tb-primary {
        background: linear-gradient(135deg, #0ea5e9, #2563eb);
        color: #fff;
        box-shadow: 0 8px 20px -8px rgba(14,165,233,.45);
    }
    .btn-tb-primary:hover {
        transform: translateY(-3px);
        box-shadow: 0 14px 25px -10px rgba(14,165,233,.5);
    }

    .btn-tb-outline {
        color: var(--text-main);
    }
    .btn-tb-outline:hover {
        background: rgba(14,165,233,.08);
        color: var(--brand-blue);
    }

    .tb-divider {
        width: 1px;
        height: 22px;
        background: var(--border-subtle);
        margin: 0 4px;
    }

    /* =========================
       KPI CARDS
    ========================= */
    .kpi-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 22px;
        margin-bottom: 30px;
    }
    @media (max-width: 1100px) { .kpi-grid { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 640px)  { .kpi-grid { grid-template-columns: 1fr; } }

    .kpi-card {
        background: var(--bg-surface);
        border: 1px solid var(--border-subtle);
        border-radius: 26px;
        padding: 26px;
        box-shadow: var(--shadow-card);
        position: relative;
        overflow: hidden;
        transition: var(--transition-smooth);
    }

    .kpi-card::before {
        content: '';
        position: absolute;
        inset: 0;
        background: radial-gradient(circle at top right, rgba(255,255,255,.12), transparent 45%);
        pointer-events: none;
    }

    .kpi-card::after {
        content: '';
        position: absolute;
        top: 0; left: 0;
        width: 100%;
        height: 5px;
        transition: .3s;
    }

    .kpi-card:hover {
        transform: translateY(-6px);
        box-shadow: var(--shadow-hover);
    }
    .kpi-card:hover::after { height: 8px; }

    .c-aset::after   { background: linear-gradient(90deg, #3b82f6, #60a5fa); }
    .c-rev::after    { background: linear-gradient(90deg, #10b981, #34d399); }
    .c-exp::after    { background: linear-gradient(90deg, #ef4444, #f87171); }
    .c-profit::after { background: linear-gradient(90deg, #8b5cf6, #a78bfa); }

    .kpi-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 12px;
    }

    .kpi-title {
        font-size: 12px;
        font-weight: 900;
        color: var(--text-muted);
        line-height: 1.5;
        text-transform: uppercase;
        letter-spacing: .5px;
    }

    .kpi-title strong {
        display: block;
        color: var(--text-main);
        font-size: 14px;
        font-weight: 900;
        text-transform: none;
        letter-spacing: -.2px;
    }

    .kpi-icon {
        width: 52px;
        height: 52px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        transition: .3s;
    }

    .kpi-card:hover .kpi-icon {
        transform: scale(1.08) rotate(5deg);
    }

    .c-aset .kpi-icon   { background: rgba(59,130,246,.1); color: #3b82f6; }
    .c-rev .kpi-icon    { background: rgba(16,185,129,.1); color: #10b981; }
    .c-exp .kpi-icon    { background: rgba(239,68,68,.1); color: #ef4444; }
    .c-profit .kpi-icon { background: rgba(139,92,246,.1); color: #8b5cf6; }

    .kpi-val {
        font-size: 34px;
        font-weight: 900;
        color: var(--text-main);
        font-family: 'Space Mono', monospace;
        margin-top: 12px;
        letter-spacing: -2px;
        line-height: 1;
    }

    .kpi-exact {
        font-size: 11px;
        font-weight: 800;
        color: var(--text-muted);
        margin-top: 14px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: var(--bg-base);
        padding: 7px 12px;
        border-radius: 10px;
        border: 1px solid transparent;
        transition: .3s;
    }

    .kpi-card:hover .kpi-exact {
        border-color: var(--border-subtle);
        background: var(--bg-surface);
    }

    /* =========================
       BENTO LAYOUT
    ========================= */
    .bento-grid {
        display: grid;
        grid-template-columns: 1.6fr 1fr;
        gap: 24px;
        margin-bottom: 30px;
    }
    @media (max-width: 1100px) {
        .bento-grid { grid-template-columns: 1fr; }
    }

    .bento-card {
        background: var(--bg-surface);
        border: 1px solid var(--border-subtle);
        border-radius: 28px;
        padding: 30px;
        box-shadow: var(--shadow-card);
        transition: var(--transition-smooth);
        overflow: hidden;
        position: relative;
    }

    .bento-card:hover {
        transform: translateY(-4px);
        box-shadow: var(--shadow-hover);
    }

    .card-title {
        font-size: 18px;
        font-weight: 900;
        color: var(--text-main);
        margin-bottom: 22px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .card-title i {
        color: var(--brand-blue);
        font-size: 20px;
    }

    /* =========================
       CHART AREA
    ========================= */
    .chart-wrap {
        position: relative;
        height: 340px;
    }

    .mini-stats {
        display: grid;
        grid-template-columns: 1fr;
        gap: 14px;
    }

    .mini-stat {
        padding: 18px 20px;
        border-radius: 18px;
        background: var(--bg-base);
        border: 1px solid var(--border-subtle);
        transition: var(--transition-smooth);
    }

    .mini-stat:hover {
        transform: translateY(-3px);
        border-color: rgba(14,165,233,.25);
    }

    .mini-stat .label {
        font-size: 11px;
        font-weight: 900;
        color: var(--text-muted);
        text-transform: uppercase;
        margin-bottom: 8px;
        letter-spacing: .5px;
    }

    .mini-stat .value {
        font-size: 28px;
        font-weight: 900;
        color: var(--text-main);
        font-family: 'Space Mono', monospace;
        letter-spacing: -1px;
        line-height: 1;
    }

    .mini-stat .desc {
        font-size: 12px;
        color: var(--text-muted);
        margin-top: 8px;
        font-weight: 700;
    }

    /* =========================
       RECENT JOURNALS
    ========================= */
    .journal-table-wrap {
        overflow-x: auto;
    }

    .journal-table {
        width: 100%;
        border-collapse: collapse;
        white-space: nowrap;
    }

    .journal-table th {
        text-align: left;
        padding: 16px 18px;
        font-size: 11px;
        font-weight: 900;
        text-transform: uppercase;
        color: var(--text-muted);
        border-bottom: 1px solid var(--border-subtle);
        background: var(--bg-base);
        letter-spacing: .5px;
    }

    .journal-table td {
        text-align: left;
        padding: 18px;
        border-bottom: 1px dashed var(--border-subtle);
        color: var(--text-main);
        font-size: 14px;
        font-weight: 700;
        vertical-align: middle;
    }

    .journal-table tr:hover td {
        background: rgba(14,165,233,.03);
    }

    .mono {
        font-family: 'Space Mono', monospace;
    }

    .status-badge {
        font-size: 11px;
        font-weight: 900;
        padding: 6px 10px;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        text-transform: uppercase;
        letter-spacing: .4px;
    }

    .status-posted {
        background: rgba(16,185,129,.1);
        color: #10b981;
        border: 1px solid rgba(16,185,129,.18);
    }

    .status-void {
        background: rgba(239,68,68,.1);
        color: #ef4444;
        border: 1px solid rgba(239,68,68,.18);
    }

    @media (max-width: 768px) {
        .header-toolbar { flex-wrap: wrap; width: 100%; justify-content: center; }
        .btn-tb { width: 100%; justify-content: center; }
        .tb-divider { display: none; }
        .page-title h1 { font-size: 28px; }
        .kpi-val { font-size: 28px; }
    }
</style>

<div class="ambient-glow"></div>

<div class="page-wrapper">
    <div class="page-header">
        <div class="page-title">
            <div class="title-icon"><i class="ph-fill ph-chart-line-up"></i></div>
            <div>
                <h1>Dasbor Finansial Eksekutif</h1>
                <p>Ringkasan performa keuangan real-time berbasis jurnal akuntansi (Accrual Basis).</p>
            </div>
        </div>

        <div class="header-toolbar">
            <a href="<?= base_url('/accounting/journal') ?>" class="btn-tb btn-tb-primary">
                <i class="ph-fill ph-plus-circle"></i> Buat Jurnal
            </a>
            <div class="tb-divider"></div>
            <a href="<?= base_url('/accounting/coa') ?>" class="btn-tb btn-tb-outline">
                <i class="ph-fill ph-list-bullets"></i> Chart of Accounts
            </a>
            <a href="<?= base_url('/accounting/ledger') ?>" class="btn-tb btn-tb-outline">
                <i class="ph-fill ph-book-open-text"></i> Buku Besar
            </a>
            <a href="<?= base_url('/accounting/print-report') ?>" class="btn-tb btn-tb-outline" target="_blank">
                <i class="ph-fill ph-printer"></i> Cetak Laporan
            </a>
        </div>
    </div>

    <div class="accrual-alert">
        <i class="ph-fill ph-info"></i>
        <div>
            <h4>Informasi Standar Akuntansi</h4>
            <p>Sistem ERP ini menggunakan standar <b>Akuntansi Berbasis Akrual (Accrual Basis)</b>. Artinya, <b>Pendapatan</b> langsung diakui pada saat pesanan (Sales Order) diterbitkan meskipun uang belum lunas (menjadi Piutang). Demikian pula <b>Beban/HPP</b> diakui pada saat fisik barang benar-benar dikirim keluar dari gudang.</p>
        </div>
    </div>

    <div class="kpi-grid">
        <div class="kpi-card c-aset">
            <div class="kpi-header">
                <div class="kpi-title">
                    Aset & Kas
                    <strong>Total Kekayaan Aktif</strong>
                </div>
                <div class="kpi-icon"><i class="ph-fill ph-wallet"></i></div>
            </div>
            <div class="kpi-val">Rp <?= format_compact($aset) ?></div>
            <div class="kpi-exact">
                <i class="ph-fill ph-sparkle"></i> Termasuk Piutang & Barang Gudang
            </div>
        </div>

        <div class="kpi-card c-rev">
            <div class="kpi-header">
                <div class="kpi-title">
                    Pendapatan (Revenue)
                    <strong>Omzet Terbukukan</strong>
                </div>
                <div class="kpi-icon"><i class="ph-fill ph-trend-up"></i></div>
            </div>
            <div class="kpi-val">Rp <?= format_compact($pendapatan) ?></div>
            <div class="kpi-exact">
                <i class="ph-fill ph-chart-bar"></i> Laba Kotor: Rp <?= format_compact($laba_kotor) ?>
            </div>
        </div>

        <div class="kpi-card c-exp">
            <div class="kpi-header">
                <div class="kpi-title">
                    Beban Operasional
                    <strong>Total Pengeluaran</strong>
                </div>
                <div class="kpi-icon"><i class="ph-fill ph-receipt-x"></i></div>
            </div>
            <div class="kpi-val">Rp <?= format_compact($beban_ops) ?></div>
            <div class="kpi-exact">
                <i class="ph-fill ph-arrow-circle-down"></i> HPP Keluar: Rp <?= format_compact($hpp) ?>
            </div>
        </div>

        <div class="kpi-card c-profit">
            <div class="kpi-header">
                <div class="kpi-title">
                    Laba Bersih (Net Profit)
                    <strong>Profit Riil Berjalan</strong>
                </div>
                <div class="kpi-icon"><i class="ph-fill ph-currency-circle-dollar"></i></div>
            </div>
            <div class="kpi-val" style="color: <?= ($laba_bersih >= 0) ? '#10b981' : '#ef4444' ?>;">
                Rp <?= format_compact($laba_bersih) ?>
            </div>
            <div class="kpi-exact">
                <i class="ph-fill ph-bank"></i> Profit Margin: <?= number_format($profitMargin, 1, ',', '.') ?>%
            </div>
        </div>
    </div>

    <div class="bento-grid">
        <div class="bento-card">
            <div class="card-title">
                <i class="ph-fill ph-chart-pie-slice"></i>
                Komposisi Finansial Berjalan
            </div>
            <div class="chart-wrap">
                <canvas id="financeChart"></canvas>
            </div>
        </div>

        <div class="bento-card">
            <div class="card-title">
                <i class="ph-fill ph-lightning"></i>
                Ringkasan Neraca Saldo
            </div>

            <div class="mini-stats">
                <div class="mini-stat">
                    <div class="label">Liabilitas (Kewajiban)</div>
                    <div class="value">Rp <?= format_compact($kewajiban) ?></div>
                    <div class="desc">Total hutang/kewajiban perusahaan saat ini.</div>
                </div>

                <div class="mini-stat">
                    <div class="label">Ekuitas (Modal Saham)</div>
                    <div class="value">Rp <?= format_compact($ekuitas) ?></div>
                    <div class="desc">Kepemilikan bersih pemilik usaha.</div>
                </div>

                <div class="mini-stat">
                    <div class="label">Status Kesehatan Akun</div>
                    <div class="value" style="color: <?= $laba_bersih >= 0 ? 'var(--brand-green)' : 'var(--brand-red)' ?>;">
                        <?= $laba_bersih >= 0 ? 'SEHAT (PROFIT)' : 'AWAS (RUGI)' ?>
                    </div>
                    <div class="desc">Berdasarkan hasil akumulasi laba bersih berjalan.</div>
                </div>
            </div>
        </div>
    </div>

    <div class="bento-card">
        <div class="card-title">
            <i class="ph-fill ph-notebook"></i>
            Aktivitas Jurnal Terbaru
        </div>

        <div class="journal-table-wrap">
            <table class="journal-table">
                <thead>
                    <tr>
                        <th>No. Jurnal</th>
                        <th>Tanggal</th>
                        <th>Deskripsi Referensi</th>
                        <th>Total Mutasi</th>
                        <th>Status</th>
                        <th>Dicatat Oleh</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($recent_journals)): ?>
                        <tr>
                            <td colspan="6" style="text-align:center; color:var(--text-muted); padding:30px;">
                                Belum ada data jurnal tercatat.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach($recent_journals as $j): ?>
                            <tr>
                                <td class="mono"><?= esc($j['journal_number']) ?></td>
                                <td><?= date('d M Y', strtotime($j['transaction_date'])) ?></td>
                                <td><?= esc($j['description']) ?></td>
                                <td class="mono">Rp <?= number_format($j['total_amount'], 0, ',', '.') ?></td>
                                <td>
                                    <span class="status-badge <?= $j['status'] === 'VOID' ? 'status-void' : 'status-posted' ?>">
                                        <i class="ph-fill <?= $j['status'] === 'VOID' ? 'ph-x-circle' : 'ph-check-circle' ?>"></i>
                                        <?= esc($j['status']) ?>
                                    </span>
                                </td>
                                <td><?= esc($j['created_by'] ?? '-') ?></td>
                            </tr>
                        <?php endforeach ?>
                    <?php endif ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
const ctx = document.getElementById('financeChart');

new Chart(ctx, {
    type: 'doughnut',
    data: {
        labels: ['Aset', 'Liabilitas', 'Ekuitas', 'Pendapatan', 'Beban Ops', 'HPP'],
        datasets: [{
            data: [
                <?= (float)$aset ?>,
                <?= (float)$kewajiban ?>,
                <?= (float)$ekuitas ?>,
                <?= (float)$pendapatan ?>,
                <?= (float)$beban_ops ?>,
                <?= (float)$hpp ?>
            ],
            backgroundColor: [
                '#3b82f6', // Aset (Blue)
                '#f59e0b', // Liabilitas (Orange)
                '#10b981', // Ekuitas (Green)
                '#8b5cf6', // Pendapatan (Purple)
                '#ef4444', // Beban (Red)
                '#f43f5e'  // HPP (Pinkish Red)
            ],
            borderWidth: 0,
            hoverOffset: 12,
            borderRadius: 8
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '72%',
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    padding: 15,
                    usePointStyle: true,
                    boxWidth: 8,
                    font: {
                        family: 'Plus Jakarta Sans',
                        weight: '700'
                    }
                }
            },
            tooltip: {
                backgroundColor: '#0f172a',
                titleFont: { family: 'Plus Jakarta Sans', weight: '800' },
                bodyFont: { family: 'Plus Jakarta Sans', weight: '700' },
                padding: 12,
                cornerRadius: 12,
                callbacks: {
                    label: function(context) {
                        return `${context.label}: Rp ${Number(context.raw).toLocaleString('id-ID')}`;
                    }
                }
            }
        }
    }
});
</script>

<?= $this->endSection() ?>