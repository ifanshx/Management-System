<?= $this->extend('layout/template') ?>
<?= $this->section('content') ?>

<?php
// --- FUNGSI PHP CERDAS UNTUK MENYINGKAT ANGKA ---
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

<style>
    .page-header { margin-bottom: 35px; display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 20px;}
    
    .page-title { display: flex; align-items: center; gap: 20px; flex-wrap: wrap;}
    .title-icon { width: 64px; height: 64px; border-radius: 20px; background: linear-gradient(135deg, #0ea5e9, #0369a1); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 32px; box-shadow: 0 12px 30px -8px rgba(14, 165, 233, 0.6); border: 1px solid rgba(255,255,255,0.15); flex-shrink: 0;}
    .title-text { display: flex; flex-direction: column; gap: 8px;}
    .title-text h1 { font-size: 34px; font-weight: 900; margin: 0; letter-spacing: -1.2px; line-height: 1; background: linear-gradient(90deg, var(--text-main), #94a3b8); -webkit-background-clip: text; -webkit-text-fill-color: transparent;}
    .title-text p { display: flex; align-items: center; gap: 10px; font-size: 14px; color: var(--text-muted); font-weight: 600; margin: 0; letter-spacing: -0.2px; flex-wrap: wrap;}
    
    .live-badge { display: inline-flex; align-items: center; gap: 6px; background: rgba(16, 185, 129, 0.1); color: #10b981; padding: 4px 10px; border-radius: 100px; font-size: 11px; font-weight: 900; text-transform: uppercase; border: 1px solid rgba(16, 185, 129, 0.2); letter-spacing: 0.5px;}
    .live-dot { width: 8px; height: 8px; background: #10b981; border-radius: 50%; box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); animation: pulseLive 2s infinite;}
    @keyframes pulseLive { 0% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); } 70% { box-shadow: 0 0 0 6px rgba(16, 185, 129, 0); } 100% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); } }

    .dash-header-toolbar { display: flex; align-items: center; background: var(--bg-surface); padding: 12px 20px; border-radius: 16px; border: 1px solid var(--border-subtle); box-shadow: 0 10px 30px -10px rgba(0,0,0,0.05); gap: 12px; width: 100%; justify-content: space-between;}
    .dash-header-toolbar i { color: #0ea5e9; font-size: 22px; }
    .ht-text { display: flex; flex-direction: column; }
    .ht-label { font-size: 10px; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px;}
    .ht-val { font-size: 14px; font-weight: 900; color: var(--text-main); }

    /* =========================================================
       2. KPI CARDS
       ========================================================= */
    .bento-row-1 { display: grid; grid-template-columns: repeat(4, 1fr); gap: 24px; margin-bottom: 30px; }
    .bento-row-2 { display: grid; grid-template-columns: 2.5fr 1fr; gap: 24px; margin-bottom: 30px; }
    .bento-row-3 { display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; margin-bottom: 30px;}
    .bento-row-4 { display: grid; grid-template-columns: 1fr; gap: 24px; margin-bottom: 30px;}

    .kpi-card { background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: 24px; padding: 25px; box-shadow: 0 4px 15px -5px rgba(0,0,0,0.03); position: relative; overflow: hidden; transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1); z-index: 1;}
    .kpi-card::before { content: ''; position: absolute; top: 0; left: 0; width: 100%; height: 5px; z-index: 2; transition: 0.4s;}
    
    .c-blue::before { background: linear-gradient(90deg, #3b82f6, #60a5fa); }
    .c-green::before { background: linear-gradient(90deg, #10b981, #34d399); }
    .c-orange::before { background: linear-gradient(90deg, #f59e0b, #fbbf24); }
    .c-purple::before { background: linear-gradient(90deg, #8b5cf6, #a78bfa); }

    .kpi-card:hover { transform: translateY(-5px); box-shadow: 0 20px 40px -10px rgba(0,0,0,0.08); border-color: var(--border-subtle);}
    .kpi-card:hover::before { height: 8px; }
    
    .kpi-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;}
    .kpi-icon { width: 48px; height: 48px; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 24px;}
    
    .c-blue .kpi-icon { background: rgba(59, 130, 246, 0.1); color: #3b82f6;}
    .c-green .kpi-icon { background: rgba(16, 185, 129, 0.1); color: #10b981;}
    .c-orange .kpi-icon { background: rgba(245, 158, 11, 0.1); color: #f59e0b;}
    .c-purple .kpi-icon { background: rgba(139, 92, 246, 0.1); color: #8b5cf6;}

    .kpi-title { font-size: 13px; font-weight: 800; color: var(--text-muted); line-height: 1.4; letter-spacing: -0.2px;}
    .kpi-title strong { display: block; color: var(--text-main); font-size: 14px; font-weight: 900;}
    
    .kpi-val { font-size: 30px; font-weight: 900; color: var(--text-main); font-family: 'Space Mono', monospace; margin-top: 10px; letter-spacing: -1.5px; line-height: 1; word-wrap: break-word;}
    
    .kpi-exact { font-size: 12px; font-weight: 800; color: var(--text-muted); font-family: 'Space Mono', monospace; margin-top: 12px; display: inline-flex; align-items: center; gap: 6px; background: var(--bg-base); padding: 6px 12px; border-radius: 8px; border: 1px solid transparent; transition: 0.3s; word-wrap: break-word;}
    .kpi-card:hover .kpi-exact { border-color: var(--border-subtle); background: var(--bg-surface);}

    .kpi-profit-val { color: <?= ($finance['profit'] >= 0) ? '#10b981' : '#ef4444' ?> !important; }

    /* =========================================================
       3. BENTO CARDS & COMPONENTS
       ========================================================= */
    .bento-card { background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: 28px; padding: 30px; box-shadow: 0 10px 40px -15px rgba(0,0,0,0.05); overflow: hidden;}
    .card-title-main { font-size: 18px; font-weight: 900; color: var(--text-main); margin-bottom: 20px; display: flex; align-items: center; gap: 12px; border-bottom: 2px dashed var(--border-subtle); padding-bottom: 18px; flex-wrap: wrap;}
    .card-title-main i { background: var(--bg-base); color: var(--text-main); padding: 10px; border-radius: 12px; font-size: 22px; border: 1px solid var(--border-subtle);}

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
       5. STATUS ITEMS (ULTRA CLEAN)
       ========================================================= */
    .status-list { display: flex; flex-direction: column; gap: 14px; }
    .status-item { display: flex; justify-content: space-between; align-items: center; padding: 20px; background: var(--bg-base); border-radius: 20px; border: 1px solid transparent; transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1); gap: 10px; flex-wrap: wrap;}
    .status-item:hover { background: var(--bg-surface); border-color: var(--border-subtle); transform: translateX(4px); box-shadow: 0 10px 20px -5px rgba(0,0,0,0.05);}
    
    .status-left { display: flex; align-items: center; gap: 15px;}
    .status-icon { width: 42px; height: 42px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 20px; background: var(--bg-surface); border: 1px solid var(--border-subtle); color: var(--text-muted); box-shadow: 0 2px 5px rgba(0,0,0,0.02); flex-shrink: 0;}
    .status-label { font-size: 14px; font-weight: 900; color: var(--text-main); letter-spacing: -0.2px;}
    .status-sub { font-size: 12px; color: var(--text-muted); font-weight: 600; margin-top: 2px;}
    
    .status-val { font-size: 15px; font-weight: 900; font-family: 'Space Mono', monospace; color: var(--text-main); background: var(--bg-surface); padding: 8px 12px; border-radius: 12px; border: 1px solid var(--border-subtle); word-wrap: break-word; text-align: right;}
    .val-danger { color: #ef4444; background: rgba(239, 68, 68, 0.1); border-color: rgba(239, 68, 68, 0.2); animation: pulseDanger 2s infinite;}
    @keyframes pulseDanger { 0% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.4); } 70% { box-shadow: 0 0 0 8px rgba(239, 68, 68, 0); } 100% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); } }

    /* =========================================================
       6. ATTENDANCE FEED
       ========================================================= */
    .att-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 16px; margin-top: 10px;}
    .att-card { display: flex; justify-content: space-between; align-items: center; background: var(--bg-base); padding: 16px; border-radius: 16px; border: 1px solid var(--border-subtle); transition: var(--transition-smooth); gap: 12px;}
    .att-card:hover { border-color: #10b981; background: var(--bg-surface); transform: translateY(-3px); box-shadow: 0 10px 20px -5px rgba(16, 185, 129, 0.15);}
    
    .att-user { display: flex; align-items: center; gap: 12px;}
    .att-avatar { width: 42px; height: 42px; border-radius: 12px; background: linear-gradient(135deg, #10b981, #059669); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 16px; font-weight: 900; box-shadow: 0 4px 10px rgba(16, 185, 129, 0.3); flex-shrink: 0;}
    .att-name { font-size: 14px; font-weight: 900; color: var(--text-main); margin-bottom: 2px;}
    .att-pos { font-size: 11px; font-weight: 700; color: var(--text-muted);}

    .att-badges { display: flex; flex-direction: column; gap: 6px; align-items: flex-end; flex-shrink: 0;}
    .badge-time { font-family: 'Space Mono', monospace; font-size: 11px; font-weight: 800; padding: 4px 10px; border-radius: 8px; display: inline-flex; align-items: center; gap: 6px; border: 1px dashed; white-space: nowrap;}
    .b-in { background: rgba(14, 165, 233, 0.1); color: #0ea5e9; border-color: rgba(14, 165, 233, 0.3);}
    .b-out { background: rgba(245, 158, 11, 0.1); color: #f59e0b; border-color: rgba(245, 158, 11, 0.3);}

    /* =========================================================
       7. MOBILE RESPONSIVENESS
       ========================================================= */
    @media (max-width: 1200px) { 
        .bento-row-1 { grid-template-columns: repeat(2, 1fr); } 
        .bento-row-2 { grid-template-columns: 1fr; } 
    }
    
    @media (max-width: 992px) {
        .bento-row-3 { grid-template-columns: repeat(2, 1fr); }
    }
    
    @media (max-width: 768px) { 
        .page-header { flex-direction: column; align-items: flex-start; gap: 15px; margin-bottom: 20px;}
        .dash-header-actions { width: 100%; display: flex; }
        
        .page-title { gap: 12px; }
        .title-icon { width: 48px; height: 48px; font-size: 22px; border-radius: 14px; }
        .title-text h1 { font-size: 22px; }
        .title-text p { flex-direction: column; align-items: flex-start; gap: 4px; line-height: 1.4; font-size: 12px;}
        
        /* Mengubah semua menjadi 1 kolom di HP */
        .bento-row-1, .bento-row-2, .bento-row-3, .bento-row-4 { grid-template-columns: 1fr; gap: 15px; margin-bottom: 15px; } 
        
        .bento-card, .kpi-card { padding: 18px; border-radius: 16px; }
        .kpi-val { font-size: 24px; }
        .kpi-icon { width: 38px; height: 38px; font-size: 18px;}
        
        .card-title-main { font-size: 16px; padding-bottom: 12px; margin-bottom: 15px;}
        .card-title-main i { font-size: 18px; padding: 8px;}
    }
    
    @media (max-width: 480px) {
        .action-grid { grid-template-columns: 1fr; } /* Tombol aksi berjejer ke bawah */
        
        .status-item { padding: 12px; flex-direction: column; align-items: flex-start; gap: 10px; }
        .status-val { align-self: flex-start; width: 100%; text-align: left; }
        .status-left { gap: 12px; }
        
        .att-card { flex-direction: column; align-items: flex-start; gap: 12px; }
        .att-badges { width: 100%; flex-direction: row; justify-content: flex-start; flex-wrap: wrap; }
        .badge-time { flex: 1; justify-content: center; }
    }
</style>

    <div class="page-header">
        <div class="page-title">
            <div class="title-icon"><i class="ph-fill ph-presentation-chart"></i></div>
            <div class="title-text">
                <h1>Executive Dashboard</h1>
                <p>
                    <span class="live-badge"><span class="live-dot"></span> Live</span>
                    Analitik <i style="color: var(--text-main); font-weight: 800; margin: 0 4px;">real-time</i> operasional pabrik.
                </p>
            </div>
        </div>
        
        <div class="dash-header-actions">
            <div class="dash-header-toolbar">
                <i class="ph-fill ph-calendar-blank"></i>
                <div class="ht-text">
                    <span class="ht-label">Periode Laporan</span>
                    <span class="ht-val"><?= $currentMonthName ?></span>
                </div>
            </div>
        </div>
    </div>

    <div class="bento-row-1">
        <div class="kpi-card c-blue">
            <div class="kpi-header">
                <div class="kpi-title"><strong>Gross Revenue</strong>Pendapatan Kotor</div>
                <div class="kpi-icon"><i class="ph-fill ph-coins"></i></div>
            </div>
            <div class="kpi-val"><?= format_compact($finance['revenue']) ?></div>
            <div class="kpi-exact" title="Nilai Asli"><i class="ph-bold ph-hash"></i> Rp <?= number_format($finance['revenue'], 0, ',', '.') ?></div>
        </div>
        
        <div class="kpi-card c-green">
            <div class="kpi-header">
                <div class="kpi-title"><strong>Net Profit</strong>Laba Bersih</div>
                <div class="kpi-icon"><i class="ph-fill ph-crown"></i></div>
            </div>
            <div class="kpi-val kpi-profit-val"><?= format_compact($finance['profit']) ?></div>
            <div class="kpi-exact" title="Nilai Asli"><i class="ph-bold ph-hash"></i> Rp <?= number_format($finance['profit'], 0, ',', '.') ?></div>
        </div>
        
        <div class="kpi-card c-orange">
            <div class="kpi-header">
                <div class="kpi-title"><strong>Total Inventory</strong>Valuasi Gudang</div>
                <div class="kpi-icon"><i class="ph-fill ph-package"></i></div>
            </div>
            <div class="kpi-val"><?= format_compact($finance['inventory_value']) ?></div>
            <div class="kpi-exact" title="Nilai Asli"><i class="ph-bold ph-hash"></i> Rp <?= number_format($finance['inventory_value'], 0, ',', '.') ?></div>
        </div>
        
        <div class="kpi-card c-purple">
            <div class="kpi-header">
                <div class="kpi-title"><strong>Total Wealth</strong>Valuasi Pabrik</div>
                <div class="kpi-icon"><i class="ph-fill ph-buildings"></i></div>
            </div>
            <div class="kpi-val"><?= format_compact($finance['total_wealth']) ?></div>
            <div class="kpi-exact" title="Nilai Asli"><i class="ph-bold ph-hash"></i> Rp <?= number_format($finance['total_wealth'], 0, ',', '.') ?></div>
        </div>
    </div>

    <div class="bento-row-2">
        <div class="bento-card" style="padding-bottom: 20px;">
            <div class="card-title-main" style="border-bottom: none; padding-bottom: 0;">
                <i class="ph-fill ph-chart-line-up" style="color: #3b82f6;"></i>
                <div style="flex: 1;">
                    <div style="font-size: 16px; margin-bottom: 2px; font-weight: 900;">Tren Arus Kas 6 Bulan Terakhir</div>
                    <div style="font-size: 11px; color: var(--text-muted); font-weight: 600;">Data diekstraksi dari rekam jejak Jurnal Umum</div>
                </div>
            </div>
            <div style="height: 300px; width: 100%; margin-top: 15px; position: relative;">
                <canvas id="financeChart"></canvas>
            </div>
        </div>

        <div class="bento-card">
            <div class="card-title-main" style="border-bottom: none; padding-bottom: 0; margin-bottom: 15px;">
                <i class="ph-fill ph-lightning" style="color: #f59e0b;"></i>
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
            <div class="card-title-main" style="border-bottom: 1px dashed var(--border-subtle); padding-bottom: 12px;">
                <i class="ph-fill ph-engine" style="color: #f59e0b; background: transparent; padding: 0; border: none;"></i> Manufaktur
            </div>
            <div class="status-list">
                <div class="status-item">
                    <div class="status-left">
                        <div class="status-icon" style="color: #3b82f6;"><i class="ph-bold ph-clipboard-text"></i></div>
                        <div>
                            <div class="status-label">SPK Berjalan</div>
                            <div class="status-sub">Tiket produksi aktif</div>
                        </div>
                    </div>
                    <div class="status-val"><?= $production['active_spk'] ?></div>
                </div>
                <div class="status-item">
                    <div class="status-left">
                        <div class="status-icon" style="color: #ef4444;"><i class="ph-bold ph-warning-circle"></i></div>
                        <div>
                            <div class="status-label">Stok Kritis</div>
                            <div class="status-sub">Butuh pengadaan</div>
                        </div>
                    </div>
                    <div class="status-val <?= $production['low_stock'] > 0 ? 'val-danger' : '' ?>"><?= $production['low_stock'] ?></div>
                </div>
            </div>
        </div>

        <div class="bento-card">
            <div class="card-title-main" style="border-bottom: 1px dashed var(--border-subtle); padding-bottom: 12px;">
                <i class="ph-fill ph-handshake" style="color: #10b981; background: transparent; padding: 0; border: none;"></i> Penjualan B2B
            </div>
            <div class="status-list">
                <div class="status-item">
                    <div class="status-left">
                        <div class="status-icon" style="color: #10b981;"><i class="ph-bold ph-users-three"></i></div>
                        <div>
                            <div class="status-label">Nilai Grosir</div>
                            <div class="status-sub">Bulan berjalan</div>
                        </div>
                    </div>
                    <div class="status-val">Rp <?= format_compact($sales['b2b_revenue']) ?></div>
                </div>
                <div class="status-item">
                    <div class="status-left">
                        <div class="status-icon" style="color: #f59e0b;"><i class="ph-bold ph-hourglass-high"></i></div>
                        <div>
                            <div class="status-label">Piutang</div>
                            <div class="status-sub">Nota belum lunas</div>
                        </div>
                    </div>
                    <div class="status-val" style="color: #f59e0b;"><?= $sales['pending_b2b'] ?></div>
                </div>
            </div>
        </div>

        <div class="bento-card">
            <div class="card-title-main" style="border-bottom: 1px dashed var(--border-subtle); padding-bottom: 12px;">
                <i class="ph-fill ph-users" style="color: #ec4899; background: transparent; padding: 0; border: none;"></i> HRD & E-Commerce
            </div>
            <div class="status-list">
                <div class="status-item">
                    <div class="status-left">
                        <div class="status-icon" style="color: #ef4444;"><i class="ph-bold ph-wallet"></i></div>
                        <div>
                            <div class="status-label">Biaya Payroll</div>
                            <div class="status-sub">Gaji bulan berjalan</div>
                        </div>
                    </div>
                    <div class="status-val" style="color: #ef4444;">Rp <?= format_compact($totalPayrollCost) ?></div>
                </div>
                <div class="status-item">
                    <div class="status-left">
                        <div class="status-icon" style="color: #ec4899;"><i class="ph-bold ph-shopping-bag"></i></div>
                        <div>
                            <div class="status-label">Shopee Sync</div>
                            <div class="status-sub">Toko terintegrasi</div>
                        </div>
                    </div>
                    <div class="status-val" style="color: #ec4899;"><?= $sales['active_shops'] ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="bento-row-4">
        <div class="bento-card" style="padding-top: 20px;">
            <div class="card-title-main" style="justify-content: space-between; border-bottom: 1px dashed var(--border-subtle); padding-bottom: 15px; margin-bottom: 10px; flex-wrap: wrap;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <i class="ph-fill ph-fingerprint" style="color: #10b981; background: rgba(16, 185, 129, 0.1); padding: 8px; border-radius: 10px; font-size: 20px; border: 1px solid rgba(16, 185, 129, 0.2);"></i>
                    <div style="display: flex; flex-direction: column;">
                        <span style="font-size: 15px; font-weight: 900;">Aktivitas Karyawan Terbaru</span>
                        <span style="font-size: 11px; color: var(--text-muted); font-weight: 600;">Log absensi pada hari ini</span>
                    </div>
                </div>
                <div style="font-size: 10px; background: rgba(16, 185, 129, 0.1); color: #10b981; padding: 4px 12px; border-radius: 100px; font-weight: 800; border: 1px solid rgba(16, 185, 129, 0.3);">
                    <?= date('d M Y') ?>
                </div>
            </div>
            
            <div class="att-grid">
                <?php if(empty($recentAttendance)): ?>
                    <div style="grid-column: 1 / -1; text-align: center; padding: 30px; color: var(--text-muted); background: var(--bg-base); border-radius: 16px; border: 1px dashed var(--border-subtle);">
                        <i class="ph-duotone ph-fingerprint" style="font-size: 40px; opacity: 0.5; margin-bottom: 10px;"></i>
                        <div style="font-weight: 800; font-size: 13px;">Belum ada karyawan yang absen hari ini.</div>
                    </div>
                <?php else: ?>
                    <?php foreach($recentAttendance as $att): ?>
                        <?php $initial = strtoupper(substr($att['name'] ?? 'U', 0, 1)); ?>
                        <div class="att-card">
                           <div class="att-user">
                                
                                <div style="position: relative; width: 38px; height: 38px; flex-shrink: 0;">
                                    <?php if(!empty($att['photo_url']) && $att['photo_url'] !== 'null'): ?>
                                        <img src="<?= esc($att['photo_url']) ?>" alt="Foto" 
                                             style="width: 100%; height: 100%; border-radius: 10px; object-fit: cover; border: 1px solid rgba(16, 185, 129, 0.3); box-shadow: 0 2px 5px rgba(16, 185, 129, 0.3);"
                                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                        
                                        <div class="att-avatar" style="display: none; position: absolute; inset: 0;"><?= $initial ?></div>
                                    <?php else: ?>
                                        <div class="att-avatar" style="position: absolute; inset: 0;"><?= $initial ?></div>
                                    <?php endif; ?>
                                </div>
                                
                                <div style="display: flex; flex-direction: column;">
                                    <div class="att-name"><?= esc($att['name'] ?? 'Unknown') ?></div>
                                    <div class="att-pos" style="display: flex; align-items: center; gap: 6px;">
                                        <?= esc($att['position'] ?? 'Staff') ?>
                                        
                                        <?php 
                                            $v = (string)($att['verify_method'] ?? '');
                                            if($v==='1') echo '<i class="ph-bold ph-fingerprint" title="Fingerprint" style="color:#3b82f6;"></i>';
                                            elseif($v==='2') echo '<i class="ph-bold ph-key" title="Password" style="color:#f59e0b;"></i>';
                                            elseif($v==='3') echo '<i class="ph-bold ph-identification-card" title="Card" style="color:#10b981;"></i>';
                                            elseif($v==='4') echo '<i class="ph-bold ph-user-focus" title="Face Scan" style="color:#8b5cf6;"></i>';
                                            elseif($v==='6') echo '<i class="ph-bold ph-hand-palm" title="Vein" style="color:#ec4899;"></i>';
                                            elseif($v==='7') echo '<i class="ph-bold ph-qr-code" title="QR Code" style="color:#0ea5e9;"></i>';
                                        ?>
                                    </div>
                                </div>
                            </div>
                            <div class="att-badges">
                                <?php if(!empty($att['time_in'])): ?>
                                    <div class="badge-time b-in">
                                        <i class="ph-bold ph-sign-in"></i> Masuk: <?= date('H:i', strtotime($att['time_in'])) ?>
                                    </div>
                                <?php endif; ?>
                                <?php if(!empty($att['time_out']) && $att['time_out'] !== '00:00:00'): ?>
                                <div class="badge-time b-out">
                                    <i class="ph-bold ph-sign-out"></i> Pulang: <?= date('H:i', strtotime($att['time_out'])) ?>
                                </div>
                            <?php else: ?>
                                <div class="badge-time" style="background: rgba(148, 163, 184, 0.1); color: #94a3b8; border-color: rgba(148, 163, 184, 0.2);">
                                    <i class="ph-bold ph-spinner-gap ph-spin"></i> Belum Pulang
                                </div>
                            <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div style="text-align: center; margin-top: 15px;">
                <a href="<?= base_url('/attendance') ?>" style="font-size: 12px; font-weight: 800; color: #0ea5e9; text-decoration: none; padding: 8px 16px; background: rgba(14, 165, 233, 0.05); border-radius: 100px; transition: 0.3s; display: inline-block;">Lihat Semua Log Absensi &rarr;</a>
            </div>
        </div>
    </div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    const isDark = document.documentElement.classList.contains('dark');
    const textColor = isDark ? '#94a3b8' : '#64748b';
    const gridColor = isDark ? 'rgba(255,255,255,0.05)' : 'rgba(0,0,0,0.03)';

    const ctx = document.getElementById('financeChart').getContext('2d');
    
    let gradientBlue = ctx.createLinearGradient(0, 0, 0, 400);
    gradientBlue.addColorStop(0, 'rgba(14, 165, 233, 0.4)');
    gradientBlue.addColorStop(1, 'rgba(14, 165, 233, 0)');

    let gradientRed = ctx.createLinearGradient(0, 0, 0, 400);
    gradientRed.addColorStop(0, 'rgba(239, 68, 68, 0.4)');
    gradientRed.addColorStop(1, 'rgba(239, 68, 68, 0)');

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?= json_encode($chart['labels']) ?>,
            datasets: [
                {
                    label: 'Revenue',
                    data: <?= json_encode($chart['pendapatan']) ?>,
                    borderColor: '#0ea5e9',
                    backgroundColor: gradientBlue,
                    borderWidth: 3, 
                    tension: 0.4,
                    fill: true,
                    pointRadius: 0,
                    pointHoverRadius: 6,
                    pointHoverBackgroundColor: '#0ea5e9',
                },
                {
                    label: 'Expense',
                    data: <?= json_encode($chart['beban']) ?>,
                    borderColor: '#ef4444',
                    backgroundColor: gradientRed,
                    borderWidth: 3, 
                    tension: 0.4,
                    fill: true,
                    pointRadius: 0,
                    pointHoverRadius: 6,
                    pointHoverBackgroundColor: '#ef4444',
                }
            ]
        },
        options: {
            responsive: true, 
            maintainAspectRatio: false,
            plugins: { 
                legend: { 
                    position: 'top', 
                    align: 'end',
                    labels: { 
                        color: textColor, 
                        usePointStyle: true,
                        pointStyle: 'circle',
                        padding: 15,
                        font: { family: 'Plus Jakarta Sans', weight: '700', size: 11 } 
                    } 
                },
                tooltip: {
                    padding: 12,
                    backgroundColor: isDark ? '#1e293b' : '#ffffff',
                    titleColor: isDark ? '#f8fafc' : '#1e293b',
                    bodyColor: isDark ? '#94a3b8' : '#64748b',
                    borderColor: isDark ? 'rgba(255,255,255,0.1)' : 'rgba(0,0,0,0.1)',
                    borderWidth: 1,
                    displayColors: true,
                    cornerRadius: 8,
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': Rp ' + context.parsed.y.toLocaleString('id-ID') + ' Jt';
                        }
                    }
                }
            },
            scales: {
                y: { 
                    beginAtZero: true,
                    grid: { color: gridColor, drawBorder: false }, 
                    ticks: { 
                        color: textColor, 
                        font: { family: 'Space Mono', size: 10 },
                        callback: function(value) { return value + ' Jt'; }
                    } 
                },
                x: { 
                    grid: { display: false }, 
                    ticks: { 
                        color: textColor, 
                        font: { family: 'Plus Jakarta Sans', weight: '700', size: 10 } 
                    } 
                }
            }
        }
    });
</script>

<?= $this->endSection() ?>