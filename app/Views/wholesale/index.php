<?= $this->extend('layout/template') ?>
<?= $this->section('content') ?>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    :root {
        --brand-green: #10b981; --brand-green-dark: #059669;
        --brand-blue: #0ea5e9; --brand-orange: #f59e0b; --brand-purple: #8b5cf6;
        --radius-xl: 24px; --radius-lg: 16px; --radius-md: 12px;
        --shadow-card: 0 10px 40px -15px rgba(0,0,0,0.05);
        --shadow-hover: 0 20px 40px -10px rgba(0,0,0,0.08);
        --transition-smooth: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .ambient-glow { position: absolute; top: -150px; left: 50%; transform: translateX(-50%); width: 100%; height: 500px; background: radial-gradient(ellipse at top, rgba(16, 185, 129, 0.08) 0%, transparent 60%); z-index: 0; pointer-events: none;}
    html.dark .ambient-glow { background: radial-gradient(ellipse at top, rgba(16, 185, 129, 0.15) 0%, transparent 60%); }
    .page-header { position: relative; z-index: 1; margin-bottom: 25px; display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 20px; width: 100%;}
    .page-title { display: flex; align-items: center; gap: 18px;}
    .title-icon { width: 56px; height: 56px; border-radius: 18px; background: linear-gradient(135deg, var(--brand-green), var(--brand-green-dark)); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 28px; box-shadow: 0 10px 25px -5px rgba(16, 185, 129, 0.4);}
    .title-text h1 { font-size: 30px; font-weight: 900; color: var(--text-main); margin: 0; letter-spacing: -1px; line-height: 1.1;}
    .title-text p { font-size: 14px; color: var(--text-muted); font-weight: 600; margin: 4px 0 0 0;}
    .tab-nav { position: relative; z-index: 1; display: inline-flex; background: rgba(0,0,0,0.03); padding: 6px; border-radius: 20px; border: 1px solid var(--border-subtle); margin-bottom: 30px; box-shadow: inset 0 2px 4px rgba(0,0,0,0.02);}
    html.dark .tab-nav { background: rgba(255,255,255,0.02); }
    .tab-btn { padding: 12px 24px; font-size: 14px; font-weight: 800; border-radius: 14px; border: none; background: transparent; color: var(--text-muted); cursor: pointer; transition: var(--transition-smooth); display: flex; align-items: center; gap: 8px;}
    .tab-btn:hover { color: var(--text-main); }
    .tab-btn.active { background: var(--bg-surface); color: var(--brand-green); box-shadow: 0 4px 15px rgba(0,0,0,0.05); border: 1px solid var(--border-subtle); }
    .tab-content { display: none; animation: slideFadeUp 0.4s cubic-bezier(0.16, 1, 0.3, 1); position: relative; z-index: 1; width: 100%;}
    .tab-content.active { display: block; width: 100%;}
    @keyframes slideFadeUp { from { opacity: 0; transform: translateY(15px); } to { opacity: 1; transform: translateY(0); } }
    .layout-stacked { display: flex; flex-direction: column; gap: 30px; width: 100%;}
    .bento-card { background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: var(--radius-xl); box-shadow: var(--shadow-card); padding: 30px; transition: var(--transition-smooth); width: 100%;}
    .bento-card:hover { border-color: rgba(16, 185, 129, 0.3); box-shadow: var(--shadow-hover);}
    .card-title { font-size: 16px; font-weight: 900; color: var(--text-main); margin-bottom: 25px; display: flex; align-items: center; gap: 12px; border-bottom: 2px dashed var(--border-subtle); padding-bottom: 18px;}
    .card-title i { background: rgba(16, 185, 129, 0.1); color: var(--brand-green); padding: 8px; border-radius: 10px; font-size: 20px; border: 1px solid rgba(16, 185, 129, 0.2);}
    .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 20px;}
    
    .form-group { margin-bottom: 18px; width: 100%;}
    .form-group label { display: block; font-size: 11px; font-weight: 900; color: var(--text-muted); margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px;}
    
    .form-control, .so-select { width: 100%; background: var(--bg-base); border: 1px solid var(--border-subtle); padding: 14px 18px; border-radius: var(--radius-md); font-size: 14px; font-weight: 700; outline: none; color: var(--text-main); transition: var(--transition-smooth); cursor: pointer; appearance: none;}
    .form-control:focus, .so-select:focus { border-color: var(--brand-green); background: var(--bg-surface); box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.15);}
    .so-select { background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%2364748b' viewBox='0 0 256 256'%3E%3Cpath d='M213.66,101.66l-80,80a8,8,0,0,1-11.32,0l-80-80A8,8,0,0,1,53.66,90.34L128,164.69l74.34-74.35a8,8,0,0,1,11.32,11.32Z'%3E%3C/path%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 16px center; padding-right: 40px; }
    
    .select2-container--default .select2-selection--single { background-color: var(--bg-base); border: 1px solid var(--border-subtle); border-radius: var(--radius-md); height: auto; padding: 11px 18px; outline: none; transition: var(--transition-smooth); }
    .select2-container--open .select2-selection--single { border-color: var(--brand-green); background: var(--bg-surface); box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.15); }
    .select2-container--default .select2-selection--single .select2-selection__rendered { color: var(--text-main); font-size: 14px; font-weight: 700; padding-left: 0; line-height: 1.5; }
    .select2-container--default .select2-selection--single .select2-selection__arrow { height: 100%; right: 15px; }
    .select2-dropdown { background-color: var(--bg-surface); border: 1px solid var(--brand-green); border-radius: var(--radius-md); box-shadow: 0 10px 30px rgba(0,0,0,0.1); z-index: 9999; }
    .select2-search__field { background-color: var(--bg-base); color: var(--text-main); border: 1px solid var(--border-subtle) !important; border-radius: 8px; padding: 10px !important; font-family: 'Plus Jakarta Sans', sans-serif; outline: none;}
    .select2-results__option { color: var(--text-main); font-weight: 600; font-size: 13px; padding: 12px 18px; }
    .select2-container--default .select2-results__option--highlighted[aria-selected] { background-color: rgba(16, 185, 129, 0.1); color: var(--brand-green); font-weight: 800; }
    .select2-container--default .select2-results__option[aria-selected=true] { background-color: rgba(0,0,0,0.03); color: var(--text-main); }
    html.dark .select2-container--default .select2-results__option[aria-selected=true] { background-color: rgba(255,255,255,0.05); }

    .input-money { display: flex; align-items: center; background: var(--bg-base); border: 1px solid var(--border-subtle); border-radius: var(--radius-md); overflow: hidden; transition: var(--transition-smooth); width: 100%;}
    .input-money:focus-within { border-color: var(--brand-green); box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.15); background: var(--bg-surface);}
    .input-money span { padding: 14px 16px; background: rgba(0,0,0,0.03); font-size: 13px; font-weight: 900; color: var(--text-muted); border-right: 1px solid var(--border-subtle);}
    .input-money input { border: none; padding: 14px 16px; font-size: 15px; font-weight: 800; width: 100%; outline: none; background: transparent; color: var(--text-main); font-family: 'Space Mono', monospace;}
    
    .item-row { background: var(--bg-base); border: 1px solid transparent; padding: 16px 20px; border-radius: var(--radius-lg); margin-bottom: 12px; transition: var(--transition-smooth); box-shadow: inset 0 2px 4px rgba(0,0,0,0.02); width: 100%;}
    .item-row:hover, .item-row:focus-within { border-color: var(--brand-green); background: var(--bg-surface); box-shadow: 0 8px 20px rgba(16, 185, 129, 0.08); transform: scale(1.01);}
    
    .item-grid { display: grid; grid-template-columns: 2fr 1fr 1.2fr 1.2fr 1.5fr auto; gap: 15px; align-items: flex-start; width: 100%;}
    
    .btn-add-row { width: 100%; background: var(--bg-surface); border: 2px dashed rgba(16, 185, 129, 0.5); color: var(--brand-green); padding: 16px; border-radius: var(--radius-lg); font-weight: 900; font-size: 14px; cursor: pointer; margin-bottom: 25px; transition: var(--transition-smooth); display: flex; align-items: center; justify-content: center; gap: 8px;}
    .btn-add-row:hover { background: rgba(16, 185, 129, 0.05); border-color: var(--brand-green); transform: translateY(-2px);}
    .btn-remove-row { background: rgba(239, 68, 68, 0.1); color: #ef4444; border: 1px solid transparent; width: 46px; height: 46px; border-radius: 12px; font-size: 22px; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: var(--transition-smooth); margin-top: 25px;}
    .btn-remove-row:hover { background: #ef4444; color: #fff; transform: scale(1.1) rotate(5deg); box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);}
    
    .bonus-chips { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 15px;}
    .bonus-chips span { background: rgba(245, 158, 11, 0.1); color: var(--brand-orange); font-size: 12px; font-weight: 800; padding: 8px 14px; border-radius: 10px; cursor: pointer; transition: 0.2s; border: 1px dashed rgba(245, 158, 11, 0.3); display: inline-flex; align-items: center; gap: 6px;}
    .bonus-chips span:hover { background: var(--brand-orange); color: #fff; border-color: var(--brand-orange); transform: translateY(-2px); box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);}
    .bonus-row { background: rgba(245, 158, 11, 0.02); border: 1px dashed rgba(245, 158, 11, 0.3); padding: 12px 16px; border-radius: var(--radius-md); margin-bottom: 8px; display: flex; gap: 15px; align-items: center; transition: 0.2s;}
    .bonus-row:hover { background: rgba(245, 158, 11, 0.05); border-color: var(--brand-orange); transform: translateX(5px); }
    
    .live-total-box { background: linear-gradient(135deg, #0f172a, #1e293b); border: 1px solid #334155; padding: 24px 30px; border-radius: var(--radius-lg); display: flex; justify-content: space-between; align-items: center; box-shadow: 0 15px 30px -10px rgba(0,0,0,0.3); position: relative; overflow: hidden; width: 100%;}
    .live-total-box::after { content: ''; position: absolute; right: -20px; top: -20px; width: 100px; height: 100px; background: radial-gradient(circle, rgba(16,185,129,0.2) 0%, transparent 70%); }
    .live-total-val { font-size: 36px; font-weight: 900; color: #10b981; font-family: 'Space Mono', monospace; line-height: 1; letter-spacing: -1.5px; text-shadow: 0 0 15px rgba(16, 185, 129, 0.4);}
    
    .btn-submit { width: 100%; background: linear-gradient(135deg, #10b981, #059669); color: #fff; border: none; padding: 18px 24px; border-radius: var(--radius-lg); font-size: 16px; font-weight: 900; cursor: pointer; transition: var(--transition-smooth); display: flex; justify-content: center; align-items: center; gap: 10px; box-shadow: 0 8px 25px -5px rgba(16, 185, 129, 0.5); margin-top: 25px;}
    .btn-submit:hover { transform: translateY(-3px); box-shadow: 0 15px 30px -5px rgba(16, 185, 129, 0.6);}
    .btn-excel { background: linear-gradient(135deg, #217346, #14532d); box-shadow: 0 8px 25px -5px rgba(33, 115, 70, 0.5); }
    
    .table-responsive { width: 100%; overflow-x: auto; }
    table { width: 100%; border-collapse: separate; border-spacing: 0; white-space: nowrap; min-width: 600px;}
    th { text-align: left; padding: 18px 20px; font-size: 11px; font-weight: 900; text-transform: uppercase; color: var(--text-muted); background: var(--bg-base); border-bottom: 2px solid var(--border-subtle); letter-spacing: 0.5px;}
    td { text-align: left; padding: 18px 20px; border-bottom: 1px dashed var(--border-subtle); color: var(--text-main); font-size: 14px; font-weight: 700; vertical-align: middle; transition: var(--transition-smooth);}
    tr:last-child td { border-bottom: none; }
    tr:hover td { background: rgba(16, 185, 129, 0.03); }
    
    .action-group { display: flex; justify-content: center; gap: 8px; flex-wrap: wrap; }
    
    .btn-rincian { background: var(--bg-base); border: 1px solid var(--border-subtle); color: var(--brand-blue); font-size: 12px; font-weight: 800; padding: 8px 14px; border-radius: 10px; display: inline-flex; align-items: center; gap: 6px; cursor: pointer; transition: var(--transition-smooth);}
    .btn-rincian:hover { background: var(--brand-blue); color: #fff; border-color: var(--brand-blue); transform: translateY(-2px); box-shadow: 0 4px 12px rgba(14, 165, 233, 0.3);}
    .btn-sj { background: var(--bg-base); border: 1px solid var(--border-subtle); color: var(--brand-orange); font-size: 12px; font-weight: 800; padding: 8px 14px; border-radius: 10px; display: inline-flex; align-items: center; gap: 6px; cursor: pointer; transition: var(--transition-smooth); text-decoration: none;}
    .btn-sj:hover { background: var(--brand-orange); color: #fff; border-color: var(--brand-orange); transform: translateY(-2px); box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);}
    .btn-add-item { background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.2); color: var(--brand-green); font-size: 12px; font-weight: 800; padding: 8px 14px; border-radius: 10px; display: inline-flex; align-items: center; gap: 6px; cursor: pointer; transition: var(--transition-smooth);}
    .btn-add-item:hover { background: var(--brand-green); color: #fff; transform: translateY(-2px); box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);}
    
    .btn-wa-on { background: rgba(37, 211, 102, 0.1); color: #25D366; border: 1px solid rgba(37, 211, 102, 0.3); font-size: 12px; font-weight: 800; padding: 8px 14px; border-radius: 10px; display: inline-flex; align-items: center; gap: 6px; cursor: pointer; transition: var(--transition-smooth); text-decoration: none;}
    .btn-wa-on:hover { background: #25D366; color: #fff; box-shadow: 0 4px 15px rgba(37, 211, 102, 0.4); transform: translateY(-2px);}
    .btn-wa-off { background: rgba(100, 116, 139, 0.1); color: #64748b; border: 1px solid rgba(100, 116, 139, 0.3); font-size: 12px; font-weight: 800; padding: 8px 14px; border-radius: 10px; display: inline-flex; align-items: center; gap: 6px; cursor: not-allowed;}

    .btn-reward { background: linear-gradient(135deg, #f59e0b, #d97706); color: #fff; border: none; font-size: 12px; font-weight: 800; padding: 8px 14px; border-radius: 10px; display: inline-flex; align-items: center; gap: 6px; cursor: pointer; transition: var(--transition-smooth);}
    .btn-reward:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(245, 158, 11, 0.4);}
    .btn-reward-disabled { background: rgba(100, 116, 139, 0.1); color: #94a3b8; border: 1px solid rgba(100, 116, 139, 0.2); font-size: 12px; font-weight: 800; padding: 8px 14px; border-radius: 10px; display: inline-flex; align-items: center; gap: 6px; cursor: not-allowed;}

    .pay-form { display: flex; align-items: center; background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: 10px; overflow: hidden; transition: 0.3s; margin: 0 auto; width: fit-content; box-shadow: inset 0 2px 4px rgba(0,0,0,0.02);}
    .pay-form:focus-within { border-color: var(--brand-green); box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.15);}
    .pay-form span { font-size: 11px; font-weight: 900; color: var(--text-muted); padding: 8px 12px; background: rgba(0,0,0,0.02); border-right: 1px solid var(--border-subtle);}
    .pay-form input { border: none; background: transparent; padding: 8px 12px; font-size: 13px; font-weight: 800; width: 110px; outline: none; font-family: 'Space Mono', monospace; color: var(--text-main);}
    .pay-btn-small { background: rgba(16, 185, 129, 0.1); border: none; border-left: 1px solid var(--border-subtle); color: var(--brand-green); padding: 8px 16px; font-weight: 900; font-size: 14px; cursor: pointer; transition: var(--transition-smooth);}
    .pay-btn-small:hover { background: var(--brand-green); color: #fff;}
    
    .progress-wrapper { width: 100%; background: var(--border-subtle); border-radius: 100px; height: 8px; overflow: hidden; margin: 10px 0; box-shadow: inset 0 1px 2px rgba(0,0,0,0.1);}
    .progress-bar { height: 100%; border-radius: 100px; transition: width 0.8s cubic-bezier(0.16, 1, 0.3, 1);}
    
    .status-badge { padding: 6px 12px; border-radius: 8px; font-size: 11px; font-weight: 900; text-transform: uppercase; letter-spacing: 0.5px; display: inline-block; border: 1px dashed;}
    .s-pending { background: rgba(239, 68, 68, 0.05); color: #ef4444; border-color: rgba(239, 68, 68, 0.3);}
    .s-partial { background: rgba(245, 158, 11, 0.05); color: #d97706; border-color: rgba(245, 158, 11, 0.3);}
    .s-paid { background: rgba(16, 185, 129, 0.05); color: #10b981; border-color: rgba(16, 185, 129, 0.3);}
    .s-returned { background: rgba(99, 102, 241, 0.08); color: #6366f1; border-color: rgba(99, 102, 241, 0.25); }
    .s-shipped { background: rgba(139, 92, 246, 0.1); color: #8b5cf6; border-color: rgba(139, 92, 246, 0.3); }
    
    .empty-state { text-align: center; padding: 60px 20px; color: var(--text-muted); width: 100%;}
    .empty-state i { font-size: 64px; color: var(--border-subtle); margin-bottom: 20px; display: block; }
    .empty-state h3 { font-size: 18px; font-weight: 900; color: var(--text-main); margin-bottom: 8px; letter-spacing: -0.5px;}
    
    .modal-overlay { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.6); backdrop-filter: blur(8px); z-index: 1000; display: none; justify-content: center; align-items: center; opacity: 0; transition: opacity 0.3s;}
    .modal-overlay.active { display: flex; opacity: 1; }
    .modal-box { background: var(--bg-surface); border-radius: 28px; width: 100%; max-width: 500px; padding: 40px; transform: scale(0.95) translateY(20px); transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1); box-shadow: 0 30px 60px -15px rgba(0,0,0,0.4); border: 1px solid var(--border-subtle); max-height: 90vh; overflow-y: auto;}
    .modal-overlay.active .modal-box { transform: scale(1) translateY(0); }
    .modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
    .btn-close { background: var(--bg-base); border: 1px solid var(--border-subtle); width: 38px; height: 38px; border-radius: 50%; font-size: 18px; color: var(--text-muted); cursor: pointer; display: flex; align-items: center; justify-content: center; transition: 0.2s;}
    .btn-close:hover { background: #ef4444; color: #fff; border-color: #ef4444; transform: rotate(90deg);}
    
    .receipt-paper { background: #ffffff; color: #000; padding: 35px 30px; font-family: 'Space Mono', monospace; box-shadow: 0 15px 35px rgba(0,0,0,0.15); max-height: 600px; overflow-y: auto; position: relative; width: 100%;}
    .receipt-paper::before, .receipt-paper::after { content: ""; position: absolute; left: 0; right: 0; height: 6px; background-size: 12px 100%; z-index: 10;}
    .receipt-paper::before { top: 0; background-image: linear-gradient(135deg, var(--bg-surface) 25%, transparent 25%), linear-gradient(225deg, var(--bg-surface) 25%, transparent 25%); background-position: 0 0; }
    .receipt-paper::after { bottom: 0; background-image: linear-gradient(315deg, var(--bg-surface) 25%, transparent 25%), linear-gradient(45deg, var(--bg-surface) 25%, transparent 25%); background-position: 0 0; }
    
    @media print {
        body * { visibility: hidden; }
        #printArea, #printArea * { visibility: visible; color: #000 !important; }
        #printArea { position: absolute; left: 0; top: 0; width: 100%; max-width: 100%; padding: 0; background: #fff !important; border: none; box-shadow: none;}
        #printArea::before, #printArea::after { display: none; }
        .modal-overlay { background: transparent; }
        .modal-box { box-shadow: none; border: none; padding: 0; }
        .no-print { display: none !important; }
    }

    /* MOBILE ADJUSTMENTS */
    @media (max-width: 768px) { 
        .grid-2 { grid-template-columns: 1fr; gap: 15px; }
        .item-grid { grid-template-columns: 1fr; gap: 10px; }
        .live-total-box { flex-direction: column; align-items: flex-start; gap: 15px; padding: 20px;}
        .live-total-val { font-size: 28px; align-self: flex-start; }
        .bento-card { padding: 20px; border-radius: 20px;}
        .modal-box { padding: 25px; border-radius: 20px; max-height: 95vh; }
        .btn-remove-row { width: 100%; margin-top: 0; } 
        .page-header { flex-direction: column; align-items: flex-start; }
        .bonus-row { flex-direction: column; align-items: stretch; }
    }
</style>

<div class="ambient-glow no-print"></div>

<div class="page-header no-print">
    <div class="page-title">
        <div class="title-icon"><i class="ph-fill ph-handshake"></i></div>
        <div class="title-text">
            <h1>B2B Wholesale & Piutang</h1>
            <p>Kelola penjualan partai besar, tagihan, serta sistem <b style="color:var(--brand-orange);">Poin Mitra</b>.</p>
        </div>
    </div>
</div>

<div class="tab-nav no-print">
    <button class="tab-btn active" onclick="switchTab('so')"><i class="ph-bold ph-receipt"></i> Pesanan Grosir (SO)</button>
    <button class="tab-btn" onclick="switchTab('reseller')"><i class="ph-bold ph-users-three"></i> Master Data Reseller </button>
</div>

<div id="tab-so" class="tab-content active no-print">
    <div class="layout-stacked">
        
        <div class="bento-card" style="border-top: 6px solid var(--brand-green);">
            <div style="display: flex; justify-content: space-between; flex-wrap: wrap; gap: 10px; margin-bottom: 25px; align-items: center; border-bottom: 2px dashed var(--border-subtle); padding-bottom: 18px;">
                <div style="font-size: 16px; font-weight: 900; color: var(--text-main); display: flex; align-items: center; gap: 12px;">
                    <i class="ph-fill ph-shopping-cart" style="background: rgba(16, 185, 129, 0.1); color: var(--brand-green); padding: 8px; border-radius: 10px; font-size: 20px; border: 1px solid rgba(16, 185, 129, 0.2);"></i> 
                    Terbitkan Pesanan Baru
                </div>
                <button class="btn-submit" style="width: auto; padding: 10px 18px; margin: 0; background: linear-gradient(135deg, var(--brand-purple), #6d28d9); box-shadow: 0 4px 15px rgba(139, 92, 246, 0.4);" onclick="openGabunganModal()">
                    <i class="ph-bold ph-truck"></i> Cetak Pengiriman Gabungan (Multi-SO)
                </button>
            </div>
            
            <form action="<?= base_url('/wholesale/store_so') ?>" method="post" style="width: 100%;">
                <?= csrf_field() ?>
                
                <div class="grid-2">
                    <div class="form-group">
                        <label>Pilih Mitra Reseller</label>
                        <div class="input-wrapper" style="border-color: rgba(16, 185, 129, 0.4); display: flex; align-items: stretch; background: var(--bg-base); border: 1px solid var(--border-subtle); border-radius: var(--radius-md); overflow: hidden;">
                            <div style="padding: 14px 16px; color: var(--brand-green); background: rgba(0,0,0,0.03); border-right: 1px solid var(--border-subtle);"><i class="ph-fill ph-storefront" style="font-size: 18px;"></i></div>
                            <div style="flex: 1;">
                                <select name="customer_id" class="select2-search" required style="width: 100%;">
                                    <option value=""></option>
                                    <?php foreach($customers as $c): ?>
                                        <?php $hasPhone = !empty(preg_replace('/[^0-9]/', '', $c['phone'])); ?>
                                        <option value="<?= $c['id'] ?>"><?= esc($c['company_name']) ?> <?= $hasPhone ? '' : '(WA Kosong)' ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Jenis Eksekusi Pesanan</label>
                        <div class="input-wrapper" style="border-color: rgba(14, 165, 233, 0.4); display: flex; align-items: stretch; background: var(--bg-base); border: 1px solid var(--border-subtle); border-radius: var(--radius-md); overflow: hidden;">
                            <div style="padding: 14px 16px; color: var(--brand-blue); background: rgba(0,0,0,0.03); border-right: 1px solid var(--border-subtle);"><i class="ph-fill ph-package" style="font-size: 18px;"></i></div>
                            <select name="order_type" class="so-select" required style="border: none; background: transparent;">
                                <option value="READY">Ready Stock (Kirim Langsung)</option>
                                <option value="PREORDER">Pre-Order / Parsial (Kirim Bertahap)</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div style="margin: 15px 0; border-top: 2px dashed var(--border-subtle); padding-top: 25px;">
                    <label style="font-size: 12px; font-weight: 900; color: var(--text-main); margin-bottom: 15px; display: flex; align-items: center; gap: 8px;">
                        <div style="background: rgba(16, 185, 129, 0.1); color: var(--brand-green); padding: 6px; border-radius: 8px;"><i class="ph-fill ph-list-numbers" style="font-size: 18px;"></i></div> 
                        1. Daftar Barang Pesanan (Utama)
                    </label>
                    
                    <div id="item-container">
                        <div class="item-row">
                            <div class="item-grid">
                                <div>
                                    <label style="font-size: 10px; font-weight: 800; color: var(--text-muted); display: block; margin-bottom: 6px;">PRODUK PABRIK</label>
                                    <select name="fg_sku[]" class="select2-product" required onchange="autoFillPrice(this)">
                                        <option value="" data-price="0"></option>
                                        <?php foreach($products as $p): ?>
                                            <?php $wholesalePrice = !empty($p['wholesale_price']) ? $p['wholesale_price'] : $p['hpp']; ?>
                                            <option value="<?= $p['sku'] ?>" data-price="<?= $wholesalePrice ?>">
                                                [<?= esc($p['motor_category']) ?> - <?= esc($p['item_type']) ?>] <?= esc($p['item_name']) ?> (Sisa: <?= $p['physical_stock'] ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div>
                                    <label style="font-size: 10px; font-weight: 800; color: var(--text-muted); display: block; margin-bottom: 6px;">KUANTITAS</label>
                                    <input type="number" name="qty[]" class="form-control so-qty" placeholder="Qty" required min="1" oninput="calcSoTotal()" style="text-align: center; font-family: 'Space Mono', monospace; font-size: 16px;">
                                </div>
                                
                                <div>
                                    <label style="font-size: 10px; font-weight: 800; color: var(--text-muted); display: block; margin-bottom: 6px;">HARGA SATUAN</label>
                                    <div class="input-money">
                                        <span>Rp</span>
                                        <input type="text" inputmode="numeric" name="unit_price[]" class="so-price" placeholder="Harga" required oninput="formatRupiah(this); calcSoTotal();">
                                    </div>
                                </div>

                                <div>
                                    <label style="font-size: 10px; font-weight: 800; color: var(--brand-green); display: block; margin-bottom: 6px;">BIAYA KUSTOM LAIN</label>
                                    <div class="input-money" style="border-color: rgba(16, 185, 129, 0.4);">
                                        <span style="color: var(--brand-green); background: rgba(16, 185, 129, 0.1); border-color: rgba(16, 185, 129, 0.2);">+ Rp</span>
                                        <input type="text" inputmode="numeric" name="additional_fee[]" class="so-add-fee" placeholder="0" value="0" oninput="formatRupiah(this); calcSoTotal();">
                                    </div>
                                </div>
                                
                                <div>
                                    <label style="font-size: 10px; font-weight: 800; color: var(--text-muted); display: block; margin-bottom: 6px;">CATATAN KHUSUS (PABRIK)</label>
                                    <input type="text" name="additional_note[]" class="form-control" placeholder="Misal: Custom Laser...">
                                </div>
                                
                                <div style="display: flex; align-items: flex-end; padding-bottom: 4px;">
                                    <button type="button" class="btn-remove-row" onclick="this.parentElement.parentElement.parentElement.remove(); calcSoTotal();" title="Hapus"><i class="ph-bold ph-trash"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <button type="button" class="btn-add-row" onclick="addSoRow()">
                        <i class="ph-bold ph-plus-circle" style="font-size: 20px;"></i> Tambah Produk Utama
                    </button>
                </div>

                <div style="margin: 15px 0; border-top: 2px dashed var(--border-subtle); padding-top: 25px;">
                    <label style="font-size: 12px; font-weight: 900; color: var(--brand-orange); margin-bottom: 10px; display: flex; align-items: center; gap: 8px;">
                        <div style="background: rgba(245, 158, 11, 0.1); color: var(--brand-orange); padding: 6px; border-radius: 8px;"><i class="ph-fill ph-gift" style="font-size: 18px;"></i></div> 
                        2. Barang Tambahan / Bonus (Gratis)
                    </label>
                    <p style="font-size: 11px; color: var(--text-muted); font-weight: 600; margin-bottom: 15px;">Pilih bahan baku/material yang akan dikirim sebagai bonus. Stok gudang akan otomatis dipotong, harga dihitung 0 di tagihan pelanggan.</p>
                    
                    <div id="bonus-container"></div>
                    
                    <button type="button" class="btn-add-item" onclick="addBonusRow()" style="color: var(--brand-orange); background: rgba(245, 158, 11, 0.1); border-color: rgba(245, 158, 11, 0.3); padding: 12px 18px; margin-top: 10px;">
                        <i class="ph-bold ph-plus-circle" style="font-size: 18px;"></i> Tambah Barang Bonus
                    </button>
                </div>
                
                <div style="margin: 15px 0; border-top: 2px dashed var(--border-subtle); padding-top: 25px;">
                    <label style="font-size: 12px; font-weight: 900; color: var(--brand-blue); margin-bottom: 10px; display: flex; align-items: center; gap: 8px;">
                        <div style="background: rgba(14, 165, 233, 0.1); color: var(--brand-blue); padding: 6px; border-radius: 8px;"><i class="ph-fill ph-factory" style="font-size: 18px;"></i></div> 
                        3. Titipan Produksi / Buffer Stok (Internal Pabrik)
                    </label>
                    <p style="font-size: 11px; color: var(--text-muted); font-weight: 600; margin-bottom: 15px;">Barang yang ditambahkan di sini <b>TIDAK AKAN</b> masuk ke nota/tagihan pelanggan. Sistem hanya akan membuatkan SPK Pabrik otomatis agar barang ini ikut diproduksi sebagai stok jaga-jaga gudang.</p>
                    
                    <div id="buffer-container"></div>
                    
                    <button type="button" class="btn-add-item" onclick="addBufferRow()" style="color: var(--brand-blue); background: rgba(14, 165, 233, 0.1); border-color: rgba(14, 165, 233, 0.3); padding: 12px 18px; margin-top: 10px;">
                        <i class="ph-bold ph-plus-circle" style="font-size: 18px;"></i> Tambah Titipan Produksi
                    </button>
                </div>

                <div class="grid-2" style="margin-top: 40px; align-items: end; border-top: 2px dashed var(--border-subtle); padding-top: 25px;">
                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px;">
                        <div class="form-group" style="margin-bottom: 0;">
                            <label>Diskon Transaksi (%)</label>
                            <div class="input-money" style="border-color: rgba(239, 68, 68, 0.4);">
                                <span style="color: #ef4444; background: rgba(239, 68, 68, 0.1); border-color: rgba(239, 68, 68, 0.2);"><i class="ph-bold ph-percent"></i></span>
                                <input type="number" name="discount_percent" class="so-discount" value="0" min="0" max="100" required oninput="calcSoTotal()" style="font-family: 'Space Mono', monospace;">
                            </div>
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <label>DP Awal (Rp)</label>
                            <div class="input-money" style="border-color: rgba(245, 158, 11, 0.4);">
                                <span style="color: var(--brand-orange); background: rgba(245, 158, 11, 0.1); border-color: rgba(245, 158, 11, 0.2);"><i class="ph-bold ph-wallet"></i></span>
                                <input type="text" inputmode="numeric" name="dp_amount" value="0" required oninput="formatRupiah(this)">
                            </div>
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <label>Tenggat / Tempo</label>
                            <input type="date" name="due_date" class="form-control" required style="font-family: 'Space Mono', monospace; padding: 13.5px 15px;">
                        </div>
                    </div>
                    
                    <div class="live-total-box">
                        <div>
                            <div style="font-size: 11px; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 4px;">Estimasi Grand Total</div>
                            <div style="font-size: 10px; color: #64748b;"><i class="ph-fill ph-info"></i> Reward: <b style="color:var(--brand-green);">1 Poin</b> per 100 Ribu</div>
                        </div>
                        <div class="live-total-val" id="soGrandTotal">Rp 0</div>
                    </div>
                </div>
                
                <button type="submit" class="btn-submit">
                    <i class="ph-bold ph-paper-plane-tilt" style="font-size: 20px;"></i> Terbitkan Dokumen SO
                </button>
            </form>
        </div>

        <div class="bento-card">
            <div class="card-title">
                <i class="ph-fill ph-list-dashes"></i> Riwayat Tagihan & Transaksi B2B
            </div>
            
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Identitas Pesanan</th>
                            <th>Mitra Reseller</th>
                            <th style="min-width: 220px; text-align: center;">Progres Pelunasan</th>
                            <th style="text-align: center;">Aksi Cepat</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($salesOrders)): ?>
                            <tr>
                                <td colspan="4">
                                    <div class="empty-state">
                                        <i class="ph-fill ph-receipt"></i>
                                        <h3>Belum Ada Riwayat Penjualan Grosir</h3>
                                        <p>Buat pesanan pertama Anda melalui formulir di atas.</p>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach($salesOrders as $so): 
                                $isReturned = ($so['status'] ?? '') === 'RETURNED';
                                $percent = ($so['total_amount'] > 0) ? ($so['paid_amount'] / $so['total_amount']) * 100 : 0;
                                $barColor = ($percent == 100) ? 'var(--brand-green)' : (($percent > 0) ? 'var(--brand-orange)' : '#ef4444');
                                $isOverdue = (strtotime($so['due_date']) < time() && $so['status'] != 'PAID'); 
                                
                                $shipBadgeClass = 's-pending';
                                $shipText = 'BELUM DIKIRIM';
                                if ($so['shipping_status'] == 'SHIPPED') {
                                    $shipBadgeClass = 's-shipped';
                                    $shipText = 'FULL DIKIRIM';
                                } elseif ($so['shipping_status'] == 'PARTIAL-SHIPPED') {
                                    $shipBadgeClass = 's-partial';
                                    $shipText = 'KIRIM SEBAGIAN';
                                }

                                $custPhoneRaw = '';
                                foreach($customers as $c) {
                                    if($c['id'] == $so['customer_id']) {
                                        $custPhoneRaw = $c['phone'];
                                        break;
                                    }
                                }

                                $cleanPhone = preg_replace('/[^0-9]/', '', $custPhoneRaw ?? '');
                                $isPhoneValid = false;
                                $waLink = '#';

                                if (!empty($cleanPhone)) {
                                    if (substr($cleanPhone, 0, 1) === '0') {
                                        $cleanPhone = '62' . substr($cleanPhone, 1);
                                    }
                                    if (strlen($cleanPhone) >= 10) {
                                        $isPhoneValid = true;
                                        
                                        $companyName = esc($company['company_name'] ?? 'PT NORIC EXHAUST');
                                        $grandTotalRp = number_format($so['total_amount'], 0, ',', '.');
                                        
                                        $waText = "Halo *{$so['company_name']}*,\n\n";
                                        $waText .= "Berikut adalah rincian pesanan dan e-Invoice tagihan Anda dari *{$companyName}*:\n\n";
                                        $waText .= "📄 *[ No. Tagihan: {$so['so_number']} ]*\n";
                                        $waText .= "--------------------------------------\n";
                                        
                                        $num = 1;
                                        foreach($so['items'] as $itm) {
                                            if($itm['price'] == 0 && strpos(strtoupper($itm['additional_note']), 'BONUS') !== false) {
                                                $waText .= "🎁 {$itm['qty']} Pcs {$itm['item_name']} (GRATIS)\n";
                                            } else {
                                                $itmName = $itm['item_name'] ?: $itm['fg_sku'];
                                                $noteText = !empty($itm['additional_note']) ? " ({$itm['additional_note']})" : "";
                                                $itmQty = $itm['qty'];
                                                $itmPrice = $itm['price'] + $itm['additional_fee'];
                                                $subTotal = $itmQty * $itmPrice;
                                                
                                                $waText .= "{$num}. {$itmName}{$noteText}\n   {$itmQty} Pcs x Rp ".number_format($itmPrice, 0, ',', '.')." = Rp ".number_format($subTotal, 0, ',', '.')."\n";
                                                $num++;
                                            }
                                        }

                                        $waText .= "--------------------------------------\n";
                                        if ($so['discount'] > 0) {
                                            $waText .= "📉 *Diskon:* Rp " . number_format($so['discount'], 0, ',', '.') . "\n";
                                        }
                                        $waText .= "💰 *Grand Total:* Rp {$grandTotalRp}\n";
                                        $waText .= "💵 *Telah Dibayar:* Rp " . number_format($so['paid_amount'], 0, ',', '.') . "\n";
                                        
                                        $sisaTagihan = $so['total_amount'] - $so['paid_amount'];
                                        if($sisaTagihan > 0) {
                                            $waText .= "💳 *SISA TAGIHAN:* Rp " . number_format($sisaTagihan, 0, ',', '.') . "\n";
                                            $waText .= "⏳ *Jatuh Tempo:* " . date('d M Y', strtotime($so['due_date'])) . "\n\n";
                                        } else {
                                            $waText .= "✅ *STATUS:* LUNAS\n\n";
                                        }
                                        
                                        $poinEarned = floor($so['total_amount'] / 100000);
                                        if ($poinEarned > 0) {
                                            $waText .= "Terima kasih atas orderannya! Anda mendapatkan tambahan *{$poinEarned} Poin Reward* dari transaksi ini.\n\n";
                                        }

                                        $waLink = "https://wa.me/{$cleanPhone}?text=" . urlencode($waText);
                                    }
                                }
                            ?>
                            <tr>
                                <td style="white-space: normal;">
                                    <div style="display:flex; align-items:center; gap:8px; margin-bottom:8px;">
                                        <div style="font-family: 'Space Mono', monospace; font-weight: 900; color: <?= $isReturned ? '#6366f1' : 'var(--brand-green)' ?>; font-size: 13px; background: rgba(16, 185, 129, 0.1); padding: 6px 10px; border-radius: 8px; display: inline-block; border: 1px dashed rgba(16, 185, 129, 0.3);">
                                            <?= esc($so['so_number']) ?>
                                        </div>
                                        <span class="status-badge <?= $shipBadgeClass ?>" style="font-size:9px; padding:4px 8px;"><?= $shipText ?></span>
                                    </div>
                                    <div style="font-size: 11px; color: var(--text-main); font-weight: 800; display: flex; align-items: center; gap: 6px; margin-bottom: 4px;">
                                        <i class="ph-bold ph-calendar-check" style="font-size: 14px; color: var(--brand-blue);"></i> 
                                        Order: <?= date('d M Y', strtotime($so['order_date'])) ?>
                                    </div>
                                    <div style="font-size: 11px; color: <?= $isOverdue ? '#ef4444' : 'var(--text-muted)' ?>; font-weight: 800; display: flex; align-items: center; gap: 6px;">
                                        <i class="<?= $isOverdue ? 'ph-fill ph-warning-circle' : 'ph-bold ph-calendar-blank' ?>" style="font-size: 14px;"></i> 
                                        Tempo: <?= date('d M Y', strtotime($so['due_date'])) ?>
                                    </div>
                                </td>
                                <td>
                                    <div style="font-weight: 900; display:flex; align-items:center; gap:10px; font-size: 14px; color: var(--text-main);">
                                        <div style="background: var(--bg-input); padding: 8px; border-radius: 10px; border: 1px solid var(--border-subtle); display: flex;"><i class="ph-fill ph-storefront" style="color: var(--text-muted);"></i></div>
                                        <?= esc($so['company_name']) ?>
                                    </div>
                                </td>
                                <td>
                                    <div style="display: flex; justify-content: space-between; font-size: 12px; font-weight: 900; font-family: 'Space Mono', monospace;">
                                        <span style="color: var(--text-main);">Rp <?= number_format($so['paid_amount'],0,',','.') ?></span>
                                        <span style="color: var(--text-muted);">Rp <?= number_format($so['total_amount'],0,',','.') ?></span>
                                    </div>
                                    <div class="progress-wrapper">
                                        <div class="progress-bar" style="width: <?= $percent ?>%; background-color: <?= $barColor ?>;"></div>
                                    </div>
                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 8px;">
                                        <?php
                                            $statusClass = 's-pending';
                                            if ($so['status'] === 'PAID') $statusClass = 's-paid';
                                            elseif ($so['status'] === 'PARTIAL') $statusClass = 's-partial';
                                            elseif ($so['status'] === 'RETURNED') $statusClass = 's-returned';
                                        ?>
                                        <span class="status-badge <?= $statusClass ?>">
                                            <?= esc($so['status']) ?> (<?= round($percent, 1) ?>%)
                                        </span>
                                        
                                        <?php if($so['status'] != 'PAID' && $so['status'] != 'RETURNED'): ?>
                                            <form action="<?= base_url('/wholesale/pay_installment/'.$so['id']) ?>" method="post" class="pay-form">
                                                <?= csrf_field() ?>
                                                <span>Rp</span>
                                                <input type="text" inputmode="numeric" name="amount" placeholder="Nominal" required oninput="formatRupiah(this)">
                                                <button type="submit" class="pay-btn-small" title="Bayar Cicilan"><i class="ph-bold ph-wallet"></i></button>
                                            </form>
                                        <?php elseif($so['status'] == 'PAID'): ?>
                                            <span style="color: var(--brand-green); font-weight: 900; font-size: 12px; display: flex; align-items: center; gap: 4px;"><i class="ph-fill ph-check-circle" style="font-size: 16px;"></i> FULL PAID</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="action-group">
                                        <?php if($isPhoneValid): ?>
                                            <a href="<?= $waLink ?>" target="_blank" class="btn-wa-on" title="Kirim e-Invoice via WhatsApp">
                                                <i class="ph-bold ph-whatsapp-logo"></i> WA
                                            </a>
                                        <?php else: ?>
                                            <button type="button" class="btn-wa-off" onclick="Swal.fire({icon: 'warning', title: 'Nomor HP Kosong', text: 'Mitra ini belum memiliki nomor HP yang valid. Silakan lengkapi di tab Master Data Reseller.', customClass: { popup: 'swal2-custom-radius' }})" title="Nomor HP Tidak Tersedia">
                                                <i class="ph-bold ph-whatsapp-logo"></i> WA
                                            </button>
                                        <?php endif; ?>

                                        <?php if(!$isReturned && $so['shipping_status'] !== 'SHIPPED'): ?>
                                            <button type="button" class="btn-rincian" onclick="showEditItems(<?= $so['id'] ?>)">
                                                <i class="ph-bold ph-pencil-simple"></i> Edit Item
                                            </button>
                                            <button type="button" class="btn-rincian" style="color: #8b5cf6; border-color: rgba(139, 92, 246, 0.3); background: rgba(139, 92, 246, 0.05);" onclick="openShipModal(<?= $so['id'] ?>)" title="Kirim Barang">
                                                <i class="ph-bold ph-paper-plane-tilt"></i> Kirim
                                            </button>
                                        <?php endif; ?>

                                        <?php if($so['status'] != 'RETURNED'): ?>
                                            <button class="btn-sj" type="button" onclick="openReturnModal(<?= $so['id'] ?>)">
                                                <i class="ph-bold ph-arrow-u-down-left"></i> Retur
                                            </button>
                                        <?php endif; ?>

                                        <button type="button" class="btn-rincian" 
                                                data-so="<?= esc($so['so_number']) ?>"
                                                data-cust="<?= esc($so['company_name']) ?>"
                                                data-date="<?= date('d M Y', strtotime($so['order_date'])) ?>"
                                                data-status="<?= $so['status'] ?>"
                                                data-total="<?= $so['total_amount'] ?>"
                                                data-discount="<?= $so['discount'] ?? 0 ?>"
                                                data-discpercent="<?= $so['discount_percent'] ?? 0 ?>"
                                                data-paid="<?= $so['paid_amount'] ?>"
                                                data-bonus="<?= esc($so['bonus_notes'] ?? '') ?>"
                                                data-items="<?= htmlspecialchars(json_encode($so['items'] ?? []), ENT_QUOTES, 'UTF-8') ?>" 
                                                data-excel="<?= base_url('wholesale/export_excel/'.$so['id']) ?>"
                                                onclick="openReceiptModal(this)" title="Cetak Struk Transaksi">
                                            <i class="ph-bold ph-receipt"></i> Struk
                                        </button>

                                        <a href="<?= base_url('/wholesale/surat_jalan/'.$so['id']) ?>" target="_blank" class="btn-sj" title="Cetak Surat Jalan Pengiriman">
                                            <i class="ph-bold ph-truck"></i> SJ
                                        </a>

                                        <button type="button" 
                                                onclick="openConfirmModal(event, '<?= base_url('/wholesale/delete_so/'.$so['id']) ?>', 'delete_so')" 
                                                class="btn-detail" 
                                                style="color:#ef4444; background:rgba(239,68,68,0.1); border-color:rgba(239,68,68,0.2); padding: 8px; border-radius: 8px; border: none; cursor: pointer;" 
                                                title="Batalkan & Hapus Transaksi">
                                            <i class="ph-bold ph-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div id="tab-reseller" class="tab-content no-print">
    <?php
        $totalPoinBeredar = 0;
        foreach($customers as $c) { $totalPoinBeredar += (int)($c['reward_points'] ?? 0); }
    ?>
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; flex-wrap: wrap; gap: 15px; width: 100%;">
        <div style="display: flex; gap: 15px; flex-wrap: wrap;">
            <div style="font-size: 13px; font-weight: 800; color: var(--text-muted); background: var(--bg-surface); padding: 14px 24px; border-radius: 16px; border: 1px solid var(--border-subtle); display: flex; align-items: center; gap: 10px; box-shadow: var(--shadow-sm);">
                <div style="background: rgba(16, 185, 129, 0.1); padding: 6px; border-radius: 8px; color: var(--brand-green);"><i class="ph-fill ph-users-three" style="font-size: 20px;"></i></div>
                Total Mitra: <span style="color: var(--text-main); font-size: 18px; font-weight: 900; font-family: 'Space Mono', monospace;"><?= count($customers) ?></span>
            </div>
            <div style="font-size: 13px; font-weight: 800; color: var(--text-muted); background: var(--bg-surface); padding: 14px 24px; border-radius: 16px; border: 1px solid var(--border-subtle); display: flex; align-items: center; gap: 10px; box-shadow: var(--shadow-sm);">
                <div style="background: rgba(245, 158, 11, 0.1); padding: 6px; border-radius: 8px; color: var(--brand-orange);"><i class="ph-fill ph-star" style="font-size: 20px;"></i></div>
                Poin Beredar: <span style="color: var(--text-main); font-size: 18px; font-weight: 900; font-family: 'Space Mono', monospace;"><?= number_format($totalPoinBeredar, 0, ',', '.') ?> Pts</span>
            </div>
        </div>
        <button class="btn-submit" style="width: auto; padding: 14px 24px; margin: 0;" onclick="openCreateModalReseller()">
            <i class="ph-bold ph-plus-circle" style="font-size: 20px;"></i> Tambah Mitra Baru
        </button>
    </div>

    <div class="bento-card">
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Nama Toko / Perusahaan</th>
                        <th>Kontak Utama (PIC)</th>
                        <th style="text-align: center;">Poin Reward</th>
                        <th>Alamat Lengkap</th>
                        <th style="text-align: center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($customers)): ?>
                        <tr><td colspan="5"><div class="empty-state"><i class="ph-fill ph-storefront"></i><h3>Belum Ada Data Mitra Reseller</h3><p>Klik tombol Tambah Mitra Baru untuk mendaftarkan agen Anda.</p></div></td></tr>
                    <?php else: ?>
                        <?php foreach($customers as $c): 
                            $pts = (int)($c['reward_points'] ?? 0);
                        ?>
                            <tr>
                                <td style="font-weight: 900; color: var(--text-main); font-size: 15px;">
                                    <div style="display: flex; align-items: center; gap: 12px;">
                                        <div style="width: 40px; height: 40px; border-radius: 12px; background: rgba(16, 185, 129, 0.1); color: var(--brand-green); display: flex; align-items: center; justify-content: center; font-size: 20px; border: 1px solid rgba(16, 185, 129, 0.2);"><i class="ph-fill ph-storefront"></i></div>
                                        <?= esc($c['company_name']) ?>
                                    </div>
                                </td>
                                <td>
                                    <div style="font-weight: 800; display:flex; align-items:center; gap:8px; font-size: 14px; color: var(--text-main);"><i class="ph-fill ph-user-circle" style="font-size: 18px; color: var(--border-subtle);"></i> <?= esc($c['contact_name'] ?? '-') ?></div>
                                    <div style="font-weight: 800; display:flex; align-items:center; gap:8px; font-size: 12px; color: var(--brand-green); margin-top: 4px; font-family: 'Space Mono', monospace;"><i class="ph-fill ph-whatsapp-logo" style="font-size: 16px;"></i> <?= esc($c['phone'] ?? 'KOSONG') ?></div>
                                </td>
                                <td style="text-align: center;">
                                    <div style="background: linear-gradient(135deg, #f59e0b, #d97706); color: #fff; padding: 6px 14px; border-radius: 10px; font-family: 'Space Mono', monospace; font-size: 15px; font-weight: 900; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 4px 10px rgba(245, 158, 11, 0.3);">
                                        <i class="ph-fill ph-star"></i> <?= number_format($pts, 0, ',', '.') ?>
                                    </div>
                                </td>
                                <td style="white-space: normal; line-height: 1.6; font-size: 12px; color: var(--text-muted); max-width: 300px; font-weight: 600;"><?= esc($c['address'] ?? '-') ?></td>
                                <td style="text-align: center;">
                                    <div class="action-group">
                                        <?php if($pts > 0): ?>
                                            <button type="button" onclick="openRedeemModal(<?= $c['id'] ?>, '<?= esc($c['company_name']) ?>', <?= $pts ?>)" class="btn-reward" title="Tukar Poin">
                                                <i class="ph-bold ph-gift"></i> Tarik Poin
                                            </button>
                                        <?php else: ?>
                                            <button type="button" class="btn-reward-disabled" title="Poin Tidak Cukup">
                                                <i class="ph-bold ph-gift"></i> Poin 0
                                            </button>
                                        <?php endif; ?>
                                        
                                        <button type="button" onclick='openEditModalReseller(<?= json_encode($c, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)' class="btn-edit" title="Edit Data" style="color: #3b82f6; background: rgba(59, 130, 246, 0.1); padding: 8px; border-radius: 8px; border:none; cursor: pointer;"><i class="ph-bold ph-pencil-simple"></i></button>
                                        <button type="button" onclick="openConfirmModal(event, '<?= base_url('/wholesale/delete_customer/'.$c['id']) ?>', 'delete')" class="btn-detail" style="color:#ef4444; background:rgba(239,68,68,0.1); border-color:rgba(239,68,68,0.2); padding: 8px; border-radius: 8px; border: none; cursor: pointer;" title="Hapus Data"><i class="ph-bold ph-trash"></i></button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal-overlay no-print" id="modalReseller">
    <div class="modal-box" style="border-top: 8px solid var(--brand-green);">
        <div class="modal-header">
            <div id="modalResellerTitle" style="font-size: 22px; font-weight: 900; color: var(--text-main); display: flex; align-items: center; gap: 14px; letter-spacing: -0.5px;">
                <div style="background: rgba(16, 185, 129, 0.1); width: 48px; height: 48px; border-radius: 14px; display: flex; align-items: center; justify-content: center; color: var(--brand-green);">
                    <i class="ph-fill ph-users-three" style="font-size: 26px;"></i>
                </div>
                Registrasi Mitra Baru
            </div>
            <button class="btn-close" onclick="closeModal('modalReseller')" type="button"><i class="ph-bold ph-x"></i></button>
        </div>
        <form id="formReseller" action="<?= base_url('/wholesale/store_customer') ?>" method="post" style="width: 100%;">
            <?= csrf_field() ?>
            <div class="form-group">
                <label>Nama Toko / Perusahaan</label>
                <div class="input-money" style="padding: 0; background: var(--bg-base);">
                    <span style="border-right: none; padding-right: 0;"><i class="ph-bold ph-storefront" style="font-size: 18px;"></i></span>
                    <input type="text" name="company_name" required autocomplete="off" placeholder="Cth: Bengkel Motor Jaya">
                </div>
            </div>
            <div class="grid-2">
                <div class="form-group">
                    <label>Nama Pemilik / PIC</label>
                    <input type="text" name="contact_name" class="form-control" autocomplete="off" placeholder="Cth: Bpk. Joko">
                </div>
                <div class="form-group">
                    <label>No. WhatsApp (Aktif)</label>
                    <input type="text" name="phone" class="form-control" autocomplete="off" placeholder="Cth: 0812..." style="font-family: 'Space Mono', monospace;">
                </div>
            </div>
            <div class="form-group">
                <label>Alamat Lengkap (Tujuan Ekspedisi)</label>
                <textarea name="address" class="form-control" rows="3" style="resize: none; line-height: 1.5; padding: 14px 18px;" placeholder="Tuliskan jalan, RT/RW, dan kota..." required></textarea>
            </div>
            <button type="submit" class="btn-submit" style="margin-top: 25px;"><i class="ph-bold ph-floppy-disk" style="font-size: 22px;"></i> Simpan Data Mitra</button>
        </form>
    </div>
</div>

<div class="modal-overlay no-print" id="modalRedeem">
    <div class="modal-box" style="border-top: 8px solid var(--brand-orange); max-width: 400px; padding: 40px 30px;">
        <div style="text-align: center; margin-bottom: 25px;">
            <div style="width: 80px; height: 80px; border-radius: 50%; background: rgba(245, 158, 11, 0.1); color: var(--brand-orange); display: flex; align-items: center; justify-content: center; font-size: 40px; margin: 0 auto 15px auto; box-shadow: 0 0 0 4px rgba(245, 158, 11, 0.2);">
                <i class="ph-fill ph-gift"></i>
            </div>
            <h2 style="font-size: 22px; font-weight: 900; color: var(--text-main); margin-bottom: 6px;">Pencairan Poin</h2>
            <p style="font-size: 13px; color: var(--text-muted); font-weight: 600;" id="redeemCustName">Nama Toko</p>
        </div>

        <form id="formRedeem" method="post" style="width: 100%;">
            <?= csrf_field() ?>
            <div style="background: rgba(245, 158, 11, 0.05); border: 1px dashed rgba(245, 158, 11, 0.4); padding: 15px; border-radius: 14px; text-align: center; margin-bottom: 20px;">
                <div style="font-size: 10px; font-weight: 900; color: var(--brand-orange); text-transform: uppercase;">Total Poin Tersedia</div>
                <div id="redeemMaxPoints" style="font-family: 'Space Mono', monospace; font-size: 28px; font-weight: 900; color: var(--text-main);">0</div>
            </div>

            <div class="form-group">
                <label style="text-align: center; justify-content: center;">Berapa Poin Yang Ingin Ditukar?</label>
                <div class="input-money" style="border-color: var(--brand-orange);">
                    <span style="color: var(--brand-orange);"><i class="ph-bold ph-star"></i></span>
                    <input type="number" name="points" id="redeemPointsInput" required min="1" autocomplete="off" style="font-size: 24px; text-align: center; color: var(--brand-orange);">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-top: 30px;">
                <button type="button" onclick="closeModal('modalRedeem')" class="btn-secondary" style="justify-content: center; padding: 16px; border-radius: 14px; background: rgba(0,0,0,0.05); color: var(--text-main); font-weight: 800; border: none; cursor: pointer;">Batal</button>
                <button type="submit" class="btn-submit" style="background: linear-gradient(135deg, #f59e0b, #d97706); box-shadow: 0 8px 20px -5px rgba(245, 158, 11, 0.5); justify-content: center; padding: 16px; margin: 0;">Potong Poin</button>
            </div>
        </form>
    </div>
</div>

<div class="modal-overlay" id="modalReturn">
    <div class="modal-box" style="max-width: 820px; border-top: 8px solid var(--brand-orange);">
        <div class="modal-header">
            <div>
                <h3 style="margin:0; font-size:24px; font-weight:900; color:var(--text-main); display:flex; align-items:center; gap:10px;"><i class="ph-bold ph-arrow-u-down-left" style="color: var(--brand-orange);"></i> Retur Penjualan</h3>
                <p style="margin:6px 0 0; color:var(--text-muted); font-size:13px;">Pilih barang yang diretur dan alasan retur.</p>
            </div>
            <button type="button" class="btn-close" onclick="closeModal('modalReturn')">&times;</button>
        </div>

        <form id="formReturn" method="post">
            <?= csrf_field() ?>
            <div class="grid-2">
                <div class="form-group">
                    <label>Tanggal Retur</label>
                    <input type="date" name="return_date" class="form-control" required value="<?= date('Y-m-d') ?>" style="font-family: 'Space Mono', monospace;">
                </div>
                <div class="form-group">
                    <label>Tipe Pengembalian / Solusi</label>
                    <select name="refund_type" class="form-control" required>
                        <option value="REDUCE_RECEIVABLE">Potong Piutang (Mengurangi Tagihan Berjalan)</option>
                        <option value="CASH_REFUND">Refund Uang Kas (Transfer Uang Balik)</option>
                        <option value="CUSTOMER_CREDIT">Simpan Saldo (Customer Credit / Tabungan)</option>
                        <option value="REPAIR_REPLACE">Perbaikan / Ganti Barang Baru (RMA)</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label>Alasan Retur</label>
                <input type="text" name="reason" class="form-control" placeholder="Cth: Barang cacat dari ekspedisi, tidak sesuai pesanan..." required>
            </div>
            <div class="form-group">
                <label>Pilih Barang yang Dikembalikan</label>
                <div id="returnItemsContainer" style="display:grid; gap:12px; max-height:300px; overflow-y:auto; padding-right:5px;"></div>
            </div>
            <button type="submit" class="btn-submit" style="background: linear-gradient(135deg, var(--brand-orange), #d97706); box-shadow: 0 8px 25px -5px rgba(245, 158, 11, 0.5);">
                <i class="ph-bold ph-arrow-u-down-left"></i> Proses Retur Sekarang
            </button>
        </form>
    </div>
</div>

<div class="modal-overlay" id="shipModal">
    <div class="modal-box" style="max-width: 820px; border-top: 8px solid #8b5cf6;">
        <div class="modal-header">
            <div>
                <h3 style="margin:0; font-size:24px; font-weight:900; color:var(--text-main); display:flex; align-items:center; gap:10px;"><i class="ph-bold ph-paper-plane-tilt" style="color: #8b5cf6;"></i> Pengiriman Barang</h3>
                <p style="margin:6px 0 0; color:var(--text-muted); font-size:13px;">Anda bisa memproses pengiriman sekaligus (Full) atau bertahap (Parsial).</p>
            </div>
            <button type="button" class="btn-close" onclick="closeModal('shipModal')">&times;</button>
        </div>

        <form id="shipForm" method="post">
            <?= csrf_field() ?>
            <div class="form-group">
                <label>Pilih Barang yang Dikirim Hari Ini</label>
                <div id="shipItemsContainer" style="display:grid; gap:12px; max-height:300px; overflow-y:auto; padding-right:5px;"></div>
            </div>
            <button type="submit" class="btn-submit" style="background: linear-gradient(135deg, #8b5cf6, #6d28d9); box-shadow: 0 8px 25px -5px rgba(139, 92, 246, 0.5);">
                <i class="ph-bold ph-truck"></i> Potong Stok & Kirim Barang
            </button>
        </form>
    </div>
</div>

<div class="modal-overlay" id="modalGabungan">
    <div class="modal-box" style="max-width: 820px; border-top: 8px solid #8b5cf6;">
        <div class="modal-header">
            <div>
                <h3 style="margin:0; font-size:20px; font-weight:900; color:var(--text-main); display:flex; align-items:center; gap:10px;">
                    <i class="ph-bold ph-truck" style="color: #8b5cf6; padding: 8px; background: rgba(139, 92, 246, 0.1); border-radius: 8px;"></i> Pengiriman Gabungan (Multi-SO)
                </h3>
                <p style="margin:6px 0 0; color:var(--text-muted); font-size:12px;">Gabungkan beberapa transaksi dari satu pelanggan ke dalam satu kali pengiriman (Satu Surat Jalan).</p>
            </div>
            <button type="button" class="btn-close" onclick="closeModal('modalGabungan')">&times;</button>
        </div>

        <form action="<?= base_url('/wholesale/process_shipment_gabungan') ?>" method="post">
            <?= csrf_field() ?>
            <div class="form-group" style="margin-bottom: 25px;">
                <label>Pilih Pelanggan / Reseller</label>
                <select name="customer_id" id="gabunganCustomerSelect" class="form-control" style="width: 100%;" onchange="loadGabunganItems(this.value)">
                    <option value="">-- Pilih Toko / Bengkel --</option>
                    <?php foreach($customers as $c): ?>
                        <option value="<?= $c['id'] ?>"><?= esc($c['company_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Daftar Barang Belum Dikirim (Dari Berbagai SO)</label>
                <div id="gabunganItemsContainer" style="display:grid; gap:12px; max-height:350px; overflow-y:auto; padding-right:5px;">
                    <div class="empty-state" style="padding: 20px;">
                        <i class="ph-duotone ph-storefront" style="font-size: 30px;"></i>
                        <p style="font-size: 13px;">Silakan pilih pelanggan terlebih dahulu.</p>
                    </div>
                </div>
            </div>
            <button type="submit" id="btnSubmitGabungan" class="btn-submit" style="background: linear-gradient(135deg, #8b5cf6, #6d28d9); box-shadow: 0 8px 25px -5px rgba(139, 92, 246, 0.5); display: none;">
                <i class="ph-bold ph-printer"></i> Proses & Cetak Surat Jalan Gabungan
            </button>
        </form>
    </div>
</div>

<div class="modal-overlay no-print" id="modalConfirm">
    <div class="modal-box" style="max-width: 420px; text-align: center; padding: 50px 40px;">
        <div id="confirmIconWrap" style="width: 86px; height: 86px; border-radius: 50%; background: rgba(239, 68, 68, 0.15); color: #ef4444; border: 4px solid var(--bg-surface); box-shadow: 0 0 0 2px rgba(239, 68, 68, 0.3); display: flex; align-items: center; justify-content: center; font-size: 44px; margin: 0 auto 25px auto;">
            <i class="ph-fill ph-paper-plane-tilt"></i>
        </div>
        <h2 id="confirmTitle" style="font-size: 24px; font-weight: 900; color: var(--text-main); margin-bottom: 12px; letter-spacing: -0.5px;">Konfirmasi</h2>
        <p id="confirmDesc" style="font-size: 15px; color: var(--text-muted); line-height: 1.6; margin-bottom: 35px; font-weight: 500;">Apakah anda yakin?</p>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
            <button type="button" onclick="closeModal('modalConfirm')" class="btn-secondary" style="justify-content: center; padding: 16px; border-radius: 14px; background: rgba(0,0,0,0.05); color: var(--text-main); font-weight: 800; border: none; cursor: pointer;">Batalkan</button>
            <a href="#" id="confirmBtnYes" class="btn-submit" style="background: linear-gradient(135deg, #8b5cf6, #6d28d9); box-shadow: 0 8px 20px -5px rgba(139, 92, 246, 0.5); justify-content: center; text-decoration: none; padding: 16px; margin: 0;">Ya, Lanjutkan</a>
        </div>
    </div>
</div>

<div class="modal-overlay" id="modalEditItems">
    <div class="modal-box" style="max-width: 900px; border-top: 8px solid var(--brand-orange);">
        <div class="modal-header">
            <div>
                <h3 style="margin:0; font-size:24px; font-weight:900; color:var(--text-main); display:flex; align-items:center; gap:10px;">
                    <i class="ph-bold ph-pencil-simple" style="color: var(--brand-orange);"></i> Rincian & Edit Item <span id="addItemsSoLabel" style="color: var(--brand-orange);"></span>
                </h3>
                <p style="margin:6px 0 0; color:var(--text-muted); font-size:13px;">Ubah Qty, hapus barang, atau tambahkan barang baru ke orderan ini.</p>
            </div>
            <button class="btn-close" onclick="closeModal('modalEditItems')">&times;</button>
        </div>
        <div class="modal-body" style="padding-top: 0;">
            
            <div style="background: rgba(245, 158, 11, 0.05); border: 1px dashed rgba(245, 158, 11, 0.3); padding: 12px 20px; border-radius: 12px; margin-bottom: 20px; font-size: 12px; color: var(--text-muted); font-weight: 600;">
                <i class="ph-fill ph-info" style="color: var(--brand-orange);"></i> Jika mengubah Qty atau menghapus item, SPK produksi dan stok gudang akan disinkronkan otomatis!
            </div>

            <div class="table-responsive" style="max-height: 250px; overflow-y: auto; border: 1px solid var(--border-subtle); border-radius: 12px; margin-bottom: 25px;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead style="position: sticky; top: 0; z-index: 10; background: var(--bg-input);">
                        <tr>
                            <th style="padding: 12px 16px;">Barang Dipesan</th>
                            <th style="padding: 12px 16px; text-align: center;">Ubah Qty</th>
                            <th style="padding: 12px 16px; text-align: right;">Subtotal Saat Ini</th>
                            <th style="padding: 12px 16px; text-align: center;">Hapus</th>
                        </tr>
                    </thead>
                    <tbody id="editItemsList">
                    </tbody>
                </table>
            </div>

            <div style="border-top: 2px dashed var(--border-subtle); padding-top: 20px; margin-top: 10px;">
                <h4 style="margin: 0 0 15px 0; font-size: 16px; font-weight: 900; color: var(--brand-green); display: flex; align-items: center; gap: 8px;">
                    <i class="ph-fill ph-plus-circle"></i> Tambah Barang Baru ke Pesanan Ini
                </h4>
                
                <form action="<?= base_url('wholesale/add_item_to_so') ?>" method="post">
                    <?= csrf_field() ?>
                    <input type="hidden" name="so_id" id="formAddSoId">
                    
                    <div id="newItemsContainer"></div>

                    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 15px;">
                        <div style="display: flex; gap: 10px;">
                            <button type="button" class="btn-add-item" onclick="addNewRowToModal()">
                                <i class="ph-bold ph-plus"></i> Produk Utama
                            </button>
                            <button type="button" class="btn-add-item" style="color:var(--brand-orange); background:rgba(245,158,11,0.1); border-color:rgba(245,158,11,0.3);" onclick="addBonusRowToModal()">
                                <i class="ph-bold ph-gift"></i> Tambah Bonus
                            </button>
                        </div>
                        
                        <div style="text-align: right;">
                            <div style="font-size: 10px; font-weight: 800; color: var(--text-muted); text-transform: uppercase;">Total Penambahan Kotor</div>
                            <div id="addItemsGrandTotal" style="font-size: 20px; font-weight: 900; color: var(--brand-green); font-family: 'Space Mono', monospace;">Rp 0</div>
                        </div>
                    </div>

                    <button type="submit" class="btn-submit" style="margin-top: 20px;">
                        <i class="ph-bold ph-floppy-disk" style="font-size: 20px;"></i> Simpan Tambahan Barang Baru
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal-overlay" id="modalReceipt">
    <div class="modal-box" style="max-width: 450px; padding: 0; background: transparent; box-shadow: none; border: none;">
        <div class="no-print" style="margin-bottom: 15px; display: flex; justify-content: flex-end; gap: 10px;">
            <a href="#" id="btnExportExcel" class="btn-submit btn-excel" style="margin: 0; width: auto; padding: 10px 18px; text-decoration: none;">
                <i class="ph-bold ph-file-xls" style="font-size: 20px;"></i> Export Excel
            </a>
            <button type="button" onclick="closeModal('modalReceipt')" class="btn-close" style="background: var(--bg-surface); width: 44px; height: 44px; font-size: 20px;"><i class="ph-bold ph-x"></i></button>
        </div>

        <div class="receipt-paper" id="printArea">
            <div style="text-align: center; margin-bottom: 30px;">
                <h2 style="margin: 0; font-size: 26px; font-weight: 900; letter-spacing: -1.5px; border-bottom: 2px dashed #000; padding-bottom: 15px; margin-bottom: 15px;">NORIC EXHAUST</h2>
                <div style="font-size: 13px; font-family: sans-serif; font-weight: 800; letter-spacing: 1px;">INVOICE GROSIR (B2B)</div>
            </div>

            <div style="font-size: 14px; margin-bottom: 25px; line-height: 2; font-weight: 600;">
                <div style="display: flex; justify-content: space-between;"><span>No. Tagihan:</span><b id="r_so"></b></div>
                <div style="display: flex; justify-content: space-between;"><span>Tanggal:</span><b id="r_date"></b></div>
                <div style="display: flex; justify-content: space-between;"><span>Kepada:</span><b id="r_cust"></b></div>
                <div style="display: flex; justify-content: space-between;"><span>Status:</span><b id="r_status"></b></div>
            </div>

            <div style="border-top: 2px dashed #000; border-bottom: 2px dashed #000; padding: 15px 0; margin-bottom: 25px;">
                <table style="width: 100%; font-size: 14px; border: none;">
                    <tbody id="r_items"></tbody>
                </table>
            </div>

            <div style="font-size: 15px; line-height: 2; font-weight: 600;">
                <div id="r_total_container"></div>
                
                <div style="display: flex; justify-content: space-between;">
                    <span>Telah Dibayar</span>
                    <span id="r_paid"></span>
                </div>
                <div style="display: flex; justify-content: space-between; font-weight: 900; margin-top: 15px; border-top: 3px solid #000; padding-top: 15px; font-size: 18px;">
                    <span>Sisa Tagihan</span>
                    <span id="r_sisa"></span>
                </div>
            </div>

            <div style="text-align: center; font-size: 12px; margin-top: 50px; font-family: sans-serif; line-height: 1.6;">
                <b>Terima kasih atas kepercayaan Anda</b><br>
                <span style="color: #444; font-weight: 600;">Dicetak otomatis oleh Sistem ERP Noric</span>
            </div>
        </div>

        <div class="no-print" style="margin-top: 20px;">
            <button type="button" onclick="window.print()" class="btn-submit" style="background: linear-gradient(135deg, #10b981, #059669); padding: 20px; font-size: 18px; box-shadow: 0 10px 30px rgba(16, 185, 129, 0.4);">
                <i class="ph-bold ph-printer" style="font-size: 24px;"></i> Cetak Struk Sekarang
            </button>
        </div>
    </div>
</div>

<script>
    const salesOrdersData = <?= json_encode($salesOrders) ?>;
    const prodData = <?= json_encode($products) ?>;
    const rawData = <?= json_encode($rawMaterials) ?>;

    $(document).ready(function() {
        initSelect2();
        
        $('#gabunganCustomerSelect').select2({
            dropdownParent: $('#modalGabungan'),
            placeholder: "-- Pilih Toko / Bengkel --"
        }).on('change', function() {
            loadGabunganItems(this.value);
        });
    });

    function initSelect2() {
        $('.select2-product').select2({ placeholder: "-- Pilih Produk Katalog (PRD) --", allowClear: true, width: '100%' });
        $('.select2-search').select2({ placeholder: "-- Cari Toko / Bengkel --", allowClear: true, width: '100%' });
    }

    function switchTab(tabName) {
        document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
        event.currentTarget.classList.add('active');
        document.getElementById('tab-' + tabName).classList.add('active');
    }

    function openModal(modalId) { document.getElementById(modalId).classList.add('active'); }
    function closeModal(modalId) { document.getElementById(modalId).classList.remove('active'); }
    
    function addBufferRow() {
        let container = document.getElementById('buffer-container');
        let options = '<option value="">-- Pilih Produk Yang Akan Diproduksi --</option>';
        prodData.forEach(p => { 
            options += `<option value="${p.sku}">[${p.motor_category} - ${p.item_type}] ${p.item_name} (Sisa Stok: ${p.physical_stock})</option>`; 
        });

        let row = document.createElement('div');
        row.className = 'bonus-row';
        row.style.borderColor = 'rgba(14, 165, 233, 0.3)';
        row.style.backgroundColor = 'rgba(14, 165, 233, 0.02)';
        
        row.innerHTML = `
            <div style="flex: 2;">
                <label style="font-size: 10px; font-weight: 800; color: var(--brand-blue); display: block; margin-bottom: 6px;">PRODUK PABRIK (BUFFER)</label>
                <select name="buffer_sku[]" class="select2-buffer" required style="width: 100%; border-color: rgba(14,165,233,0.3);">${options}</select>
            </div>
            <div style="flex: 1;">
                <label style="font-size: 10px; font-weight: 800; color: var(--brand-blue); display: block; margin-bottom: 6px;">QTY PRODUKSI</label>
                <input type="number" name="buffer_qty[]" class="form-control" value="1" required min="1" style="border-color: rgba(14,165,233,0.3); color: var(--brand-blue); font-family: monospace; font-weight: 900; text-align: center; height:46px;">
            </div>
            <div style="display: flex; align-items: flex-end;">
                <button type="button" class="btn-remove-row" onclick="this.parentElement.parentElement.remove();" style="margin-top: 0; background: transparent; color: var(--danger);" title="Hapus"><i class="ph-bold ph-x" style="font-size: 20px; color: #ef4444;"></i></button>
            </div>
        `;
        row.style.opacity = 0; row.style.transform = "translateY(15px)";
        container.appendChild(row);
        
        $(row).find('.select2-buffer').select2({ placeholder: "-- Pilih Produk Katalog --", width: '100%' });
        
        setTimeout(() => { row.style.opacity = 1; row.style.transform = "translateY(0)"; }, 10);
    }
    // FUNGSI BONUS BARU (TANPA SWEETALERT)
    function addBonusRow() {
        let container = document.getElementById('bonus-container');
        let options = '<option value="">-- Pilih Material/Aksesoris --</option>';
        rawData.forEach(rm => {
            options += `<option value="${rm.sku}">[Stok: ${rm.physical_stock}] ${rm.item_name}</option>`;
        });

        let row = document.createElement('div');
        row.className = 'bonus-row';
        row.innerHTML = `
            <div style="flex: 2;">
                <label style="font-size: 10px; font-weight: 800; color: var(--brand-orange); display: block; margin-bottom: 6px;">NAMA BARANG GRATIS (Bahan/Aksesoris)</label>
                <select name="bonus_sku[]" class="select2-bonus" required style="width: 100%; border-color: rgba(245,158,11,0.3);">${options}</select>
            </div>
            <div style="flex: 1;">
                <label style="font-size: 10px; font-weight: 800; color: var(--brand-orange); display: block; margin-bottom: 6px;">QTY</label>
                <input type="number" name="bonus_qty[]" class="form-control" value="1" required min="1" style="border-color: rgba(245,158,11,0.3); color: var(--brand-orange); font-family: monospace; font-weight: 900; text-align: center; height:46px;">
            </div>
            <div style="display: flex; align-items: flex-end;">
                <button type="button" class="btn-remove-row" onclick="this.parentElement.parentElement.remove();" style="margin-top: 0; background: transparent; color: var(--danger);" title="Hapus Bonus"><i class="ph-bold ph-x" style="font-size: 20px; color: #ef4444;"></i></button>
            </div>
        `;
        row.style.opacity = 0; row.style.transform = "translateY(15px)";
        container.appendChild(row);
        
        $(row).find('.select2-bonus').select2({ placeholder: "-- Pilih Material/Aksesoris Gudang --", width: '100%' });
        
        setTimeout(() => { row.style.opacity = 1; row.style.transform = "translateY(0)"; }, 10);
    }

    function openCreateModalReseller() {
        document.getElementById('formReseller').reset();
        document.getElementById('modalResellerTitle').innerHTML = '<div style="background: rgba(16, 185, 129, 0.1); width: 48px; height: 48px; border-radius: 14px; display: flex; align-items: center; justify-content: center; color: var(--brand-green);"><i class="ph-fill ph-users-three" style="font-size: 26px;"></i></div> Registrasi Mitra Baru';
        document.getElementById('formReseller').action = "<?= base_url('/wholesale/store_customer') ?>";
        openModal('modalReseller');
    }

    function openEditModalReseller(data) {
        document.getElementById('formReseller').reset();
        document.getElementById('modalResellerTitle').innerHTML = '<div style="background: rgba(59, 130, 246, 0.1); width: 48px; height: 48px; border-radius: 14px; display: flex; align-items: center; justify-content: center; color: #3b82f6;"><i class="ph-fill ph-pencil-simple" style="font-size: 26px;"></i></div> Edit Data Mitra';
        document.getElementById('formReseller').action = "<?= base_url('/wholesale/update_customer/') ?>" + data.id;

        document.querySelector('#modalReseller input[name="company_name"]').value = data.company_name;
        document.querySelector('#modalReseller input[name="contact_name"]').value = data.contact_name;
        document.querySelector('#modalReseller input[name="phone"]').value = data.phone;
        document.querySelector('#modalReseller textarea[name="address"]').value = data.address;
        
        openModal('modalReseller');
    }
    
    function addBonusRowToModal() {
        let container = document.getElementById('newItemsContainer');
        let options = '<option value="">-- Pilih Material/Aksesoris --</option>';
        rawData.forEach(rm => {
            options += `<option value="${rm.sku}">[Stok: ${rm.physical_stock}] ${rm.item_name}</option>`;
        });

        let row = document.createElement('div');
        row.className = 'item-row';
        row.style.padding = '10px';
        row.style.border = '1px dashed var(--brand-orange)';
        row.style.borderRadius = '12px';
        row.style.marginBottom = '10px';
        row.style.backgroundColor = 'rgba(245, 158, 11, 0.02)';
        
        row.innerHTML = `
            <div class="item-grid" style="grid-template-columns: 2fr 1fr 1.5fr 1.5fr 1fr auto; gap:10px;">
                <select name="bonus_sku[]" class="select2-bonus-modal" required>${options}</select>
                <input type="number" name="bonus_qty[]" class="form-control so-qty-modal" placeholder="Qty" required min="1" style="text-align: center; font-family: 'Space Mono', monospace; font-size: 14px; border-color: var(--brand-orange); color: var(--brand-orange);">
                <div class="input-money" style="padding:0; height: 100%; opacity: 0.5; pointer-events: none;">
                    <span style="padding: 0 10px;">Rp</span>
                    <input type="text" value="0" readonly style="padding: 10px;">
                </div>
                <div class="input-money" style="padding:0; height: 100%; opacity: 0.5; pointer-events: none;">
                    <span style="padding: 0 10px;">+ Rp</span>
                    <input type="text" value="0" readonly style="padding: 10px;">
                </div>
                <input type="text" value="BONUS GRATIS" readonly class="form-control" style="height: 100%; opacity:0.7;">
                <button type="button" class="btn-remove-row" onclick="this.parentElement.remove(); calcAddItemsTotal();" style="width: 36px; height: 36px; font-size: 16px; margin:0; background:rgba(239,68,68,0.1); color:#ef4444;" title="Hapus"><i class="ph-bold ph-trash"></i></button>
            </div>
        `;
        
        container.appendChild(row);

        $(row).find('.select2-bonus-modal').select2({
            placeholder: "-- Pilih Material (Bonus) --",
            allowClear: true,
            width: '100%',
            dropdownParent: $('#modalEditItems')
        });
    }

    function openRedeemModal(custId, custName, maxPoints) {
        document.getElementById('formRedeem').reset();
        document.getElementById('formRedeem').action = "<?= base_url('/wholesale/redeem_points/') ?>" + custId;
        document.getElementById('redeemCustName').innerText = custName;
        document.getElementById('redeemMaxPoints').innerText = maxPoints.toLocaleString('id-ID');
        
        const input = document.getElementById('redeemPointsInput');
        input.max = maxPoints;
        input.value = maxPoints; 

        openModal('modalRedeem');
    }

    // FUNGSI RETUR (BARU DITAMBAHKAN)
    function openReturnModal(soId) {
        const form = document.getElementById('formReturn');
        const container = document.getElementById('returnItemsContainer');

        const so = salesOrdersData.find(x => parseInt(x.id) === parseInt(soId));
        if (!so) return;

        form.action = "<?= base_url('/wholesale/return_so/') ?>" + soId;
        container.innerHTML = '';

        let hasReturnable = false;

        (so.items || []).forEach((item) => {
            const returnableQty = parseInt(item.returnable_qty || 0);
            if (returnableQty <= 0) return;

            hasReturnable = true;

            const row = document.createElement('div');
            row.className = 'item-row';
            row.style.background = '#f8fafc';
            row.innerHTML = `
                <div class="item-grid" style="grid-template-columns: 3fr 1.5fr 1.5fr;">
                    <div>
                        <label style="display:block; font-size:10px; font-weight:800; color:var(--text-muted); margin-bottom:6px; text-transform:uppercase;">Identitas Barang</label>
                        <input type="hidden" name="so_item_id[]" value="${item.id}">
                        <div style="font-weight:900; font-size:13px; color:var(--text-main); margin-bottom:4px;">${item.item_name || item.fg_sku}</div>
                        <div style="font-size:11px; color:var(--text-muted); font-family:'Space Mono', monospace;">
                            Beli: ${item.qty} | Sudah Retur Sebelumnya: <b style="color:var(--brand-orange);">${item.returned_qty || 0}</b>
                        </div>
                    </div>
                    <div>
                        <label style="display:block; font-size:10px; font-weight:800; color:var(--text-muted); margin-bottom:6px; text-transform:uppercase;">Maks. Retur</label>
                        <input type="text" class="form-control" value="${returnableQty} Pcs" readonly style="font-family:'Space Mono', monospace; font-weight:900;">
                    </div>
                    <div>
                        <label style="display:block; font-size:10px; font-weight:800; color:var(--text-muted); margin-bottom:6px; text-transform:uppercase;">Jml Yg Diretur</label>
                        <input type="number" name="qty_return[]" class="form-control" style="border-color: var(--brand-orange); color: var(--brand-orange); font-family:'Space Mono', monospace; font-size:16px; font-weight:900; text-align:center;"
                               min="0" max="${returnableQty}" value="0">
                    </div>
                </div>
            `;
            container.appendChild(row);
        });

        if (!hasReturnable) {
            container.innerHTML = `
                <div class="empty-state" style="padding:20px 10px;">
                    <i class="ph-bold ph-check-circle" style="font-size:40px; margin-bottom:10px; color: var(--brand-green);"></i>
                    <h3 style="font-size:15px;">Semua item pesanan ini sudah diretur secara utuh.</h3>
                </div>
            `;
        }

        openModal('modalReturn');
    }
    
    function openGabunganModal() {
        $('#gabunganCustomerSelect').val(null).trigger('change');
        document.getElementById('gabunganItemsContainer').innerHTML = `
            <div class="empty-state" style="padding: 20px;">
                <i class="ph-duotone ph-storefront" style="font-size: 30px;"></i>
                <p style="font-size: 13px;">Silakan pilih pelanggan terlebih dahulu.</p>
            </div>
        `;
        document.getElementById('btnSubmitGabungan').style.display = 'none';
        openModal('modalGabungan');
    }

    function loadGabunganItems(customerId) {
        const container = document.getElementById('gabunganItemsContainer');
        const btnSubmit = document.getElementById('btnSubmitGabungan');
        
        if (!customerId) {
            container.innerHTML = `
                <div class="empty-state" style="padding: 20px;">
                    <i class="ph-duotone ph-storefront" style="font-size: 30px;"></i>
                    <p style="font-size: 13px;">Silakan pilih pelanggan terlebih dahulu.</p>
                </div>
            `;
            btnSubmit.style.display = 'none';
            return;
        }

        container.innerHTML = '<div style="text-align:center; padding: 20px;"><i class="ph-bold ph-spinner-gap ph-spin" style="font-size: 30px; color: var(--brand-purple);"></i><p>Memuat tagihan...</p></div>';

        fetch('<?= base_url("wholesale/get_pending_by_customer") ?>/' + customerId, {
            headers: {'X-Requested-With': 'XMLHttpRequest'}
        })
        .then(response => response.json())
        .then(res => {
            container.innerHTML = '';
            if (res.status === 'success' && res.data.length > 0) {
                res.data.forEach(item => {
                    const unshippedQty = parseInt(item.qty) - parseInt(item.shipped_qty);
                    const row = document.createElement('div');
                    row.className = 'item-row';
                    row.style.background = '#f8fafc';
                    row.innerHTML = `
                        <div class="item-grid" style="grid-template-columns: 3fr 1.5fr 1.5fr; gap:10px;">
                            <div>
                                <label style="display:block; font-size:10px; font-weight:800; color:var(--text-muted); margin-bottom:6px; text-transform:uppercase;">Identitas Produk</label>
                                <input type="hidden" name="so_item_id[]" value="${item.id}">
                                <div style="display:flex; align-items:center; gap:8px; margin-bottom:4px;">
                                    <span style="font-size:9px; background:rgba(139,92,246,0.1); color:#8b5cf6; padding:2px 6px; border-radius:4px; font-family:'Space Mono'; font-weight:900;">${item.so_number}</span>
                                    <span style="font-weight:900; font-size:13px; color:var(--text-main);">${item.item_name || item.fg_sku}</span>
                                </div>
                                <div style="font-size:11px; color:var(--text-muted); font-family:'Space Mono', monospace;">
                                    Target: ${item.qty} | Terkirim: <b style="color:var(--brand-green);">${item.shipped_qty}</b>
                                </div>
                            </div>
                            <div>
                                <label style="display:block; font-size:10px; font-weight:800; color:var(--text-muted); margin-bottom:6px; text-transform:uppercase;">Sisa Gudang</label>
                                <input type="text" class="form-control" value="${unshippedQty} Pcs" readonly style="font-family:'Space Mono', monospace; font-weight:900;">
                            </div>
                            <div>
                                <label style="display:block; font-size:10px; font-weight:800; color:var(--text-muted); margin-bottom:6px; text-transform:uppercase;">Kirim Hari Ini</label>
                                <input type="number" name="ship_qty[]" class="form-control" style="border-color: #8b5cf6; color: #8b5cf6; font-family:'Space Mono', monospace; font-size:16px; font-weight:900; text-align:center;"
                                       min="0" max="${unshippedQty}" value="${unshippedQty}">
                            </div>
                        </div>
                    `;
                    container.appendChild(row);
                });
                btnSubmit.style.display = 'flex';
            } else {
                container.innerHTML = `
                    <div class="empty-state" style="padding: 20px;">
                        <i class="ph-duotone ph-check-circle" style="font-size: 30px; color: var(--brand-green);"></i>
                        <p style="font-size: 13px;">Semua pesanan untuk toko ini sudah terkirim Lunas (Full).</p>
                    </div>
                `;
                btnSubmit.style.display = 'none';
            }
        })
        .catch(err => {
            container.innerHTML = '<div style="color:red; text-align:center; padding:10px;">Gagal memuat data.</div>';
        });
    }

    function openConfirmModal(event, actionUrl, type = 'delete') {
        event.preventDefault(); 
        const modal = document.getElementById('modalConfirm');
        const title = document.getElementById('confirmTitle');
        const desc = document.getElementById('confirmDesc');
        const iconWrap = document.getElementById('confirmIconWrap');
        const btnYes = document.getElementById('confirmBtnYes');

        if(type === 'ship') {
            iconWrap.style.background = 'rgba(139, 92, 246, 0.15)';
            iconWrap.style.color = '#8b5cf6';
            iconWrap.style.boxShadow = '0 0 0 2px rgba(139, 92, 246, 0.3)';
            iconWrap.innerHTML = '<i class="ph-fill ph-paper-plane-tilt"></i>';
            title.innerText = 'Kirim Knalpot Pre-Order?';
            desc.innerText = 'Pastikan tim Manufaktur sudah selesai merakit. Sistem akan otomatis memotong stok Gudang dan menjurnal HPP.';
            btnYes.style.background = 'linear-gradient(135deg, #8b5cf6, #6d28d9)';
            btnYes.style.boxShadow = '0 8px 20px -5px rgba(139, 92, 246, 0.5)';
            btnYes.innerHTML = 'Ya, Kirim Sekarang';
        } else if(type === 'delete_so') {
            iconWrap.style.background = 'rgba(239, 68, 68, 0.15)';
            iconWrap.style.color = '#ef4444';
            iconWrap.style.boxShadow = '0 0 0 2px rgba(239, 68, 68, 0.3)';
            iconWrap.innerHTML = '<i class="ph-fill ph-warning-circle"></i>';
            title.innerText = 'Hapus Transaksi (SO)?';
            desc.innerHTML = '<b style="color:#ef4444;">Peringatan Keras!</b> Menghapus transaksi ini otomatis menarik kembali Stok dari Customer, Menghapus Jurnal, dan <b style="color:#ef4444;">Menarik Kembali Poin Mitra</b>.';
            btnYes.style.background = 'linear-gradient(135deg, #ef4444, #dc2626)';
            btnYes.style.boxShadow = '0 8px 20px -5px rgba(239, 68, 68, 0.5)';
            btnYes.innerHTML = 'Ya, Batalkan Transaksi';
        } else {
            iconWrap.style.background = 'rgba(239, 68, 68, 0.15)';
            iconWrap.style.color = '#ef4444';
            iconWrap.style.boxShadow = '0 0 0 2px rgba(239, 68, 68, 0.3)';
            iconWrap.innerHTML = '<i class="ph-fill ph-trash"></i>';
            title.innerText = 'Hapus Mitra?';
            desc.innerText = 'Tindakan ini tidak bisa dibatalkan. Menghapus mitra berarti menghilangkan datanya dari sistem selamanya.';
            btnYes.style.background = 'linear-gradient(135deg, #ef4444, #dc2626)';
            btnYes.style.boxShadow = '0 8px 20px -5px rgba(239, 68, 68, 0.5)';
            btnYes.innerHTML = 'Ya, Hapus Saja';
        }

        btnYes.href = actionUrl;
        modal.classList.add('active');
    }

    function formatRupiah(inputElement) {
        let value = inputElement.value.replace(/[^,\d]/g, '');
        let split = value.split(',');
        let sisa = split[0].length % 3;
        let rupiah = split[0].substr(0, sisa);
        let ribuan = split[0].substr(sisa).match(/\d{3}/gi);

        if (ribuan) {
            let separator = sisa ? '.' : '';
            rupiah += separator + ribuan.join('.');
        }
        rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
        inputElement.value = rupiah;
    }

    function parseRupiah(formattedString) {
        if(!formattedString) return 0;
        return parseFloat(formattedString.replace(/\./g, '').replace(',', '.')) || 0;
    }
    
    function formatRupiahJS(num) {
        return 'Rp ' + new Intl.NumberFormat('id-ID').format(num || 0);
    }

    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!this.checkValidity()) {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning', title: 'Data Belum Lengkap', text: 'Mohon periksa kembali form Anda.',
                    background: document.documentElement.classList.contains('dark') ? '#18181b' : '#ffffff',
                    color: document.documentElement.classList.contains('dark') ? '#f4f4f5' : '#09090b',
                    confirmButtonColor: '#10b981', customClass: { popup: 'swal2-custom-radius' }
                });
                return;
            }

            let moneyInputs = this.querySelectorAll('.so-price, .so-add-fee, input[name="dp_amount"], input[name="amount"]');
            moneyInputs.forEach(input => {
                if(input.value) input.value = input.value.replace(/\./g, '');
            });

            let btn = this.querySelector('button[type="submit"]');
            if(btn && !btn.classList.contains('pay-btn-small') && !btn.classList.contains('btn-add-item') && !btn.classList.contains('btn-excel')) {
                btn.innerHTML = '<i class="ph-bold ph-spinner-gap ph-spin" style="font-size: 20px;"></i> Memproses...';
                btn.style.opacity = '0.8'; btn.style.pointerEvents = 'none';
            }
        });
    });

    function addSoRow() {
        let container = document.getElementById('item-container');
        let options = '<option value="" data-price="0"></option>';
        prodData.forEach(p => { 
            let wholesalePrice = parseFloat(p.wholesale_price) || parseFloat(p.hpp) || 0;
            options += `<option value="${p.sku}" data-price="${wholesalePrice}">[${p.motor_category} - ${p.item_type}] ${p.item_name} (Sisa: ${p.physical_stock})</option>`; 
        });

        let row = document.createElement('div');
        row.className = 'item-row';
        row.innerHTML = `
            <div class="item-grid">
                <div>
                    <label style="font-size: 10px; font-weight: 800; color: var(--text-muted); display: block; margin-bottom: 6px;">PRODUK PABRIK</label>
                    <select name="fg_sku[]" class="select2-product" required onchange="autoFillPrice(this)">${options}</select>
                </div>
                <div>
                    <label style="font-size: 10px; font-weight: 800; color: var(--text-muted); display: block; margin-bottom: 6px;">KUANTITAS</label>
                    <input type="number" name="qty[]" class="form-control so-qty" placeholder="Qty" required min="1" oninput="calcSoTotal()" style="text-align: center; font-family: 'Space Mono', monospace; font-size: 16px;">
                </div>
                <div>
                    <label style="font-size: 10px; font-weight: 800; color: var(--text-muted); display: block; margin-bottom: 6px;">HARGA SATUAN</label>
                    <div class="input-money">
                        <span>Rp</span>
                        <input type="text" inputmode="numeric" name="unit_price[]" class="so-price" placeholder="Harga" required oninput="formatRupiah(this); calcSoTotal();">
                    </div>
                </div>
                <div>
                    <label style="font-size: 10px; font-weight: 800; color: var(--brand-green); display: block; margin-bottom: 6px;">BIAYA EKSTRA CUSTOM</label>
                    <div class="input-money" style="border-color: rgba(16, 185, 129, 0.4);">
                        <span style="color: var(--brand-green); background: rgba(16, 185, 129, 0.1); border-color: rgba(16, 185, 129, 0.2);">+ Rp</span>
                        <input type="text" inputmode="numeric" name="additional_fee[]" class="so-add-fee" placeholder="0" value="0" oninput="formatRupiah(this); calcSoTotal();">
                    </div>
                </div>
                <div>
                    <label style="font-size: 10px; font-weight: 800; color: var(--text-muted); display: block; margin-bottom: 6px;">CATATAN KHUSUS PABRIK</label>
                    <input type="text" name="additional_note[]" class="form-control" placeholder="Misal: Custom Laser...">
                </div>
                <div style="display: flex; align-items: flex-end; padding-bottom: 4px;">
                    <button type="button" class="btn-remove-row" onclick="this.parentElement.parentElement.parentElement.remove(); calcSoTotal();" title="Hapus"><i class="ph-bold ph-trash"></i></button>
                </div>
            </div>
        `;
        row.style.opacity = 0; row.style.transform = "translateY(15px)";
        container.appendChild(row);

        $(row).find('.select2-product').select2({
            placeholder: "-- Pilih Produk Katalog (PRD) --",
            allowClear: true,
            width: '100%'
        });

        setTimeout(() => { row.style.opacity = 1; row.style.transform = "translateY(0)"; }, 10);
    }

    function addNewRowToModal() {
        let container = document.getElementById('newItemsContainer');
        let options = '<option value="" data-price="0"></option>';
        prodData.forEach(p => { 
            let wholesalePrice = parseFloat(p.wholesale_price) || parseFloat(p.hpp) || 0;
            options += `<option value="${p.sku}" data-price="${wholesalePrice}">[${p.motor_category} - ${p.item_type}] ${p.item_name} (Sisa: ${p.physical_stock})</option>`; 
        });

        let row = document.createElement('div');
        row.className = 'item-row';
        row.style.padding = '10px';
        row.style.border = '1px solid var(--border-subtle)';
        row.style.borderRadius = '12px';
        row.style.marginBottom = '10px';
        
        row.innerHTML = `
            <div class="item-grid" style="grid-template-columns: 2fr 1fr 1.5fr 1.5fr 1fr auto; gap:10px;">
                <select name="fg_sku[]" class="select2-product-modal" required onchange="autoFillPriceModal(this)">${options}</select>
                <input type="number" name="qty[]" class="form-control so-qty-modal" placeholder="Qty" required min="1" oninput="calcAddItemsTotal()" style="text-align: center; font-family: 'Space Mono', monospace; font-size: 14px;">
                <div class="input-money" style="padding:0; height: 100%;">
                    <span style="padding: 0 10px;">Rp</span>
                    <input type="text" inputmode="numeric" name="unit_price[]" class="so-price-modal" placeholder="Harga" required oninput="formatRupiah(this); calcAddItemsTotal();" style="padding: 10px;">
                </div>
                <div class="input-money" style="padding:0; height: 100%; border-color: rgba(16, 185, 129, 0.4);">
                    <span style="padding: 0 10px; color: var(--brand-green);">+ Rp</span>
                    <input type="text" inputmode="numeric" name="additional_fee[]" class="so-add-fee-modal" placeholder="0" value="0" oninput="formatRupiah(this); calcAddItemsTotal();" style="padding: 10px;">
                </div>
                <input type="text" name="additional_note[]" class="form-control" placeholder="Aksesoris/Bonus" style="height: 100%;">
                <button type="button" class="btn-remove-row" onclick="this.parentElement.parentElement.remove(); calcAddItemsTotal();" style="width: 36px; height: 36px; font-size: 16px; margin:0;" title="Hapus"><i class="ph-bold ph-trash"></i></button>
            </div>
        `;
        
        container.appendChild(row);

        $(row).find('.select2-product-modal').select2({
            placeholder: "-- Pilih Produk Katalog --",
            allowClear: true,
            width: '100%',
            dropdownParent: $('#modalEditItems')
        });
    }

    function autoFillPrice(selectElement) {
        let selectedOption = selectElement.options[selectElement.selectedIndex];
        let price = selectedOption.getAttribute('data-price') || 0;
        
        let priceInput = selectElement.closest('.item-grid').querySelector('.so-price');
        if (priceInput) {
            priceInput.value = parseFloat(price).toLocaleString('id-ID'); 
            calcSoTotal(); 
        }
    }

    function autoFillPriceModal(selectElement) {
        let selectedOption = selectElement.options[selectElement.selectedIndex];
        let price = selectedOption.getAttribute('data-price') || 0;
        
        let priceInput = selectElement.closest('.item-grid').querySelector('.so-price-modal');
        if (priceInput) {
            priceInput.value = parseFloat(price).toLocaleString('id-ID'); 
            calcAddItemsTotal(); 
        }
    }

    function calcSoTotal() {
        let qtys = document.querySelectorAll('.so-qty');
        let prices = document.querySelectorAll('.so-price');
        let addFees = document.querySelectorAll('.so-add-fee');
        let discInput = document.querySelector('.so-discount');
        
        let total = 0;
        for(let i = 0; i < qtys.length; i++) {
            let q = parseFloat(qtys[i].value) || 0;
            let p = parseRupiah(prices[i].value); 
            let af = parseRupiah(addFees[i] ? addFees[i].value : '0');
            total += (q * (p + af));
        }
        
        let discPercent = parseFloat(discInput ? discInput.value : '0') || 0;
        if(discPercent < 0) discPercent = 0;
        if(discPercent > 100) discPercent = 100;

        let discNominal = total * (discPercent / 100);
        let grandTotal = total - discNominal;
        if (grandTotal < 0) grandTotal = 0;
        
        document.getElementById('soGrandTotal').innerText = 'Rp ' + grandTotal.toLocaleString('id-ID');
    }

    function calcAddItemsTotal() {
        let qtys = document.querySelectorAll('.so-qty-modal');
        let prices = document.querySelectorAll('.so-price-modal');
        let addFees = document.querySelectorAll('.so-add-fee-modal');
        
        let total = 0;
        for(let i = 0; i < qtys.length; i++) {
            let q = parseFloat(qtys[i].value) || 0;
            let p = parseRupiah(prices[i].value); 
            let af = parseRupiah(addFees[i] ? addFees[i].value : '0');
            total += (q * (p + af));
        }
        
        document.getElementById('addItemsGrandTotal').innerText = 'Rp ' + total.toLocaleString('id-ID');
    }

    // FUNGSI AJAX UPDATE QTY (ANTI-CSRF EXPIRED)
    function updateItemQty(itemId, soId) {
        let newQty = document.getElementById('edit_qty_' + itemId).value;
        if(newQty <= 0) { Swal.fire('Error', 'Kuantitas minimal adalah 1 Pcs', 'error'); return; }

        Swal.fire({ title: 'Menyimpan & Menyesuaikan...', allowOutsideClick: false, didOpen: () => { Swal.showLoading() } });

        let csrfName = '<?= csrf_token() ?>';
        let csrfHash = $('input[name="<?= csrf_token() ?>"]').first().val();

        let formData = new FormData();
        formData.append('new_qty', newQty);
        formData.append(csrfName, csrfHash);

        fetch('<?= base_url("wholesale/update_item_qty") ?>/' + itemId, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(async res => {
            if (!res.ok && res.status === 403) throw new Error("Token Keamanan Kadaluarsa");
            let data = await res.json();
            if (data.message === 'The action you requested is not allowed.') throw new Error("Token Keamanan Kadaluarsa");
            return data;
        })
        .then(res => {
            if(res.csrf_token) {
                $('input[name="<?= csrf_token() ?>"]').val(res.csrf_token);
            }

            if(res.status === 'success') {
                Swal.fire('Berhasil', res.message, 'success').then(() => {
                    window.location.reload(); 
                });
            } else {
                Swal.fire('Gagal', res.message, 'error');
            }
        })
        .catch(err => {
            Swal.fire({
                icon: 'warning',
                title: 'Memperbarui Sesi',
                text: 'Sistem sedang memuat ulang untuk memperbarui token keamanan Anda...',
                showConfirmButton: false,
                timer: 2000,
                customClass: { popup: 'swal2-custom-radius' }
            }).then(() => {
                window.location.reload();
            });
        });
    }
    
    function showEditItems(soId) {
        Swal.fire({ title: 'Memuat data...', allowOutsideClick: false, didOpen: () => { Swal.showLoading() } });

        fetch('<?= base_url("wholesale/get_so") ?>/' + soId, {
            headers: {'X-Requested-With': 'XMLHttpRequest'}
        })
        .then(res => res.json())
        .then(res => {
            Swal.close();
            if(res.status === 'success') {
                const tbody = document.getElementById('editItemsList');
                document.getElementById('formAddSoId').value = soId;
                document.getElementById('addItemsSoLabel').innerText = res.data.so_number;
                document.getElementById('newItemsContainer').innerHTML = ''; 
                document.getElementById('addItemsGrandTotal').innerText = 'Rp 0';
                
                tbody.innerHTML = '';
                
                res.data.items.forEach(item => {
                    let isShipped = res.data.shipping_status === 'SHIPPED';
                    let isReturned = res.data.status === 'RETURNED';

                    let deleteBtnHtml = '';
                    if (isShipped || isReturned) {
                        deleteBtnHtml = `<span style="font-size:10px; color:#94a3b8;"><i class="ph-fill ph-lock-key"></i> Terkunci</span>`;
                    } else {
                        deleteBtnHtml = `
                            <a href="<?= base_url('wholesale/delete_item_from_so') ?>/${item.id}" 
                               class="btn-del" 
                               onclick="return confirm('Hapus item ini? Stok akan dikembalikan dan tagihan berkurang.')"
                               style="color:red; font-size:18px; padding:6px; border-radius:6px; background:rgba(239,68,68,0.1);"><i class="ph-bold ph-trash"></i></a>
                        `;
                    }

                    let qtyHtml = '';
                    if (isShipped || isReturned) {
                        qtyHtml = `${item.qty} Pcs`;
                    } else {
                        qtyHtml = `
                            <div style="display:flex; align-items:center; justify-content:center; gap:5px;">
                                <input type="number" id="edit_qty_${item.id}" value="${item.qty}" class="form-control" style="width: 70px; padding: 6px; text-align: center; font-family: monospace; font-size: 15px; font-weight: bold;">
                                <button type="button" class="btn-action-sm" onclick="updateItemQty(${item.id}, ${soId})" title="Simpan Qty Baru" style="background: var(--brand-green); color: white; border:none; padding: 7px; border-radius: 6px;">
                                    <i class="ph-bold ph-check"></i>
                                </button>
                            </div>
                        `;
                    }

                    tbody.innerHTML += `
                        <tr style="border-bottom: 1px dashed var(--border-subtle);">
                            <td style="padding: 12px 16px;">
                                <div style="font-weight: 900; color: var(--text-main); font-size: 13px;">${item.item_name || item.fg_sku}</div>
                                <div style="font-size: 10px; color: var(--text-muted); font-family: 'Space Mono', monospace;">SKU: ${item.fg_sku}</div>
                            </td>
                            <td style="padding: 12px 16px; text-align: center;">${qtyHtml}</td>
                            <td style="padding: 12px 16px; text-align: right; font-family: 'Space Mono', monospace; font-weight: 900; color: var(--brand-green);">Rp ${parseInt(item.subtotal).toLocaleString('id-ID')}</td>
                            <td style="padding: 12px 16px; text-align: center;">
                                ${deleteBtnHtml}
                            </td>
                        </tr>
                    `;
                });

                if(res.data.shipping_status !== 'SHIPPED') {
                    addNewRowToModal();
                }

                openModal('modalEditItems');
            } else {
                Swal.fire('Error', res.message, 'error');
            }
        })
        .catch(err => {
            Swal.close();
            console.error(err);
            Swal.fire('Error', 'Terjadi kesalahan sistem/jaringan.', 'error');
        });
    }

    function openReceiptModal(btn) {
        const soNum = btn.getAttribute('data-so');
        const cust  = btn.getAttribute('data-cust');
        const date  = btn.getAttribute('data-date');
        const stat  = btn.getAttribute('data-status');
        const total = parseFloat(btn.getAttribute('data-total'));
        const diskonNom = parseFloat(btn.getAttribute('data-discount')) || 0;
        const diskonPct = parseFloat(btn.getAttribute('data-discpercent')) || 0;
        const paid  = parseFloat(btn.getAttribute('data-paid'));
        const bonus = btn.getAttribute('data-bonus');
        const sisa  = total - paid;
        const items = JSON.parse(btn.getAttribute('data-items'));
        const excelUrl = btn.getAttribute('data-excel');

        document.getElementById('r_so').innerText = soNum;
        document.getElementById('r_cust').innerText = cust;
        document.getElementById('r_date').innerText = date;
        document.getElementById('r_status').innerText = (stat === 'PAID') ? 'LUNAS' : ((stat === 'PARTIAL') ? 'CICILAN' : ((stat === 'RETURNED') ? 'DIRETUR' : 'BELUM BAYAR'));
        document.getElementById('btnExportExcel').href = excelUrl;
        
        let tbody = document.getElementById('r_items');
        tbody.innerHTML = '';
        
        let shippedHtml = '';
        let unshippedHtml = '';

        items.forEach(it => {
            let name = it.item_name ? it.item_name : it.fg_sku;
            let qtyTotal = parseInt(it.qty) || 0;
            let qtyShipped = parseInt(it.shipped_qty) || 0;
            let qtyUnshipped = qtyTotal - qtyShipped;

            let p = parseFloat(it.price) || 0; 
            let af = parseFloat(it.additional_fee) || 0;
            let an = it.additional_note ? `(${it.additional_note})` : '';
            let strikeStyle = (stat === 'RETURNED') ? 'text-decoration: line-through;' : '';
            
            let isBonus = (p === 0 && (an.toUpperCase().includes('BONUS') || an === ''));

            // Fungsi render baris untuk Struk
            const renderRow = (qtyRender) => {
                let sub = (p + af) * qtyRender;
                if(isBonus) {
                    return `
                        <tr style="background: rgba(245, 158, 11, 0.05);">
                            <td style="padding: 6px 10px; border: none; ${strikeStyle}">
                                <div style="font-weight: 800; font-size: 12px; color: var(--brand-orange);"><i class="ph-fill ph-gift"></i> ${qtyRender} Pcs ${name}</div>
                            </td>
                            <td style="text-align: right; padding: 6px 10px; font-weight: 900; font-size: 12px; border: none; color: var(--brand-orange); ${strikeStyle}">
                                GRATIS
                            </td>
                        </tr>
                    `;
                } else {
                    let detailText = `${qtyRender} x Rp ${p.toLocaleString('id-ID')}`;
                    if (af > 0) detailText += ` + Kustom: Rp ${af.toLocaleString('id-ID')}`;
                    return `
                        <tr>
                            <td style="padding: 10px 0; border: none; ${strikeStyle}">
                                <div style="font-weight: 800; margin-bottom: 6px;">${name} <span style="font-size:11px; font-weight:normal; color:#666;">${an}</span></div>
                                <div style="color: #555; font-size: 13px;">${detailText}</div>
                            </td>
                            <td style="text-align: right; vertical-align: bottom; padding: 10px 0; font-weight: 900; border: none; ${strikeStyle}">
                                Rp ${sub.toLocaleString('id-ID')}
                            </td>
                        </tr>
                    `;
                }
            };

            // Memasukkan ke grup yang sesuai berdasarkan Qty Terkirim
            if (qtyShipped > 0) {
                shippedHtml += renderRow(qtyShipped);
            }
            if (qtyUnshipped > 0) {
                unshippedHtml += renderRow(qtyUnshipped);
            }
        });

        // Gabungkan HTML dengan Header Pemisah
        let finalHtml = '';
        
        if (shippedHtml !== '') {
            finalHtml += `<tr><td colspan="2" style="background:#ecfdf5; padding:8px 10px; font-weight:900; font-size:11px; border-radius:6px; color:#059669; border: 1px dashed #a7f3d0;">📦 SUDAH TERKIRIM</td></tr>` + shippedHtml;
        }
        
        if (unshippedHtml !== '') {
            let spacing = shippedHtml !== '' ? 'margin-top: 15px;' : '';
            finalHtml += `<tr><td colspan="2" style="background:#fef2f2; padding:8px 10px; font-weight:900; font-size:11px; border-radius:6px; color:#dc2626; border: 1px dashed #fecaca; display:block; width:100%; box-sizing:border-box; ${spacing}">⏳ BELUM TERKIRIM (SISA / BACKORDER)</td></tr>` + unshippedHtml;
        }

        tbody.innerHTML = finalHtml;

        let htmlSubtotal = '';
        if (diskonNom > 0) {
            htmlSubtotal += `<div style="display: flex; justify-content: space-between; font-size: 14px; margin-bottom: 5px;"><span>Diskon Transaksi (${diskonPct}%)</span><span style="color:#ef4444;">- Rp ${diskonNom.toLocaleString('id-ID')}</span></div>`;
        }

        document.getElementById('r_total_container').innerHTML = htmlSubtotal + `<div style="display: flex; justify-content: space-between; margin-top:5px; padding-top:10px; border-top: 1px dashed #ddd;"><span>Grand Total Tagihan</span><span style="font-weight:900;">Rp ${total.toLocaleString('id-ID')}</span></div>`;
        
        document.getElementById('r_paid').innerText = 'Rp ' + paid.toLocaleString('id-ID');
        document.getElementById('r_sisa').innerText = 'Rp ' + sisa.toLocaleString('id-ID');

        openModal('modalReceipt');
    }

    function openShipModal(soId) {
        const form = document.getElementById('shipForm');
        const container = document.getElementById('shipItemsContainer');

        const so = salesOrdersData.find(x => parseInt(x.id) === parseInt(soId));
        if (!so) return;

        form.action = "<?= base_url('/wholesale/process_shipment/') ?>" + soId;
        container.innerHTML = '';

        let hasUnshipped = false;

        (so.items || []).forEach((item) => {
            const unshippedQty = parseInt(item.unshipped_qty || 0);
            if (unshippedQty <= 0) return;

            hasUnshipped = true;

            const row = document.createElement('div');
            row.className = 'item-row';
            row.style.background = '#f8fafc';
            row.innerHTML = `
                <div class="item-grid" style="grid-template-columns: 3fr 1.5fr 1.5fr;">
                    <div>
                        <label style="display:block; font-size:10px; font-weight:800; color:var(--text-muted); margin-bottom:6px; text-transform:uppercase;">Produk / Bonus</label>
                        <input type="hidden" name="so_item_id[]" value="${item.id}">
                        <div style="font-weight:900; font-size:13px; color:var(--text-main); margin-bottom:4px;">${item.item_name || item.fg_sku}</div>
                        <div style="font-size:11px; color:var(--text-muted); font-family:'Space Mono', monospace;">
                            Total Order: ${item.qty} | Sudah Dikirim: <b style="color:var(--brand-green);">${item.shipped_qty}</b>
                        </div>
                    </div>
                    <div>
                        <label style="display:block; font-size:10px; font-weight:800; color:var(--text-muted); margin-bottom:6px; text-transform:uppercase;">Sisa (Belum Kirim)</label>
                        <input type="text" class="form-control" value="${unshippedQty} Pcs" readonly style="font-family:'Space Mono', monospace; font-weight:900;">
                    </div>
                    <div>
                        <label style="display:block; font-size:10px; font-weight:800; color:var(--text-muted); margin-bottom:6px; text-transform:uppercase;">Kirim Hari Ini</label>
                        <input type="number" name="ship_qty[]" class="form-control" style="border-color: #8b5cf6; color: #8b5cf6; font-family:'Space Mono', monospace; font-size:16px; font-weight:900; text-align:center;"
                               min="0" max="${unshippedQty}" value="${unshippedQty}">
                    </div>
                </div>
            `;
            container.appendChild(row);
        });

        if (!hasUnshipped) {
            container.innerHTML = `
                <div class="empty-state" style="padding:20px 10px;">
                    <i class="ph-bold ph-package" style="font-size:40px; margin-bottom:10px;"></i>
                    <h3 style="font-size:15px;">Semua item telah dikirim</h3>
                    <p style="font-size:12px;">Sales Order ini tidak memiliki sisa barang untuk dikirim.</p>
                </div>
            `;
        }

        openModal('shipModal');
    }

    document.addEventListener("DOMContentLoaded", function() {
        const isDark = document.documentElement.classList.contains('dark');
        const swalBg = isDark ? '#18181b' : '#ffffff';
        const swalText = isDark ? '#f4f4f5' : '#09090b';

        <?php if(session()->getFlashdata('success')): ?>
            Swal.fire({ icon: 'success', title: 'Berhasil!', html: <?= json_encode(session()->getFlashdata('success'), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) ?>, confirmButtonColor: '#10b981', background: swalBg, color: swalText, customClass: { popup: 'swal2-custom-radius' } });
        <?php endif; ?>
        <?php if(session()->getFlashdata('error')): ?>
            Swal.fire({ icon: 'error', title: 'Gagal!', html: <?= json_encode(session()->getFlashdata('error'), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) ?>, confirmButtonColor: '#ef4444', background: swalBg, color: swalText, customClass: { popup: 'swal2-custom-radius' } });
        <?php endif; ?>
        
        // AUTO-TRIGGER CETAK SJ GABUNGAN JIKA ADA FLASHDATA
        <?php if(session()->getFlashdata('print_gabungan')): ?>
            Swal.fire({
                icon: 'success',
                title: 'Pengiriman Berhasil!',
                text: 'Stok gudang telah dipotong sesuai jumlah yang Anda masukkan. Apakah Anda ingin mencetak Surat Jalan Gabungan sekarang?',
                background: swalBg, color: swalText,
                showCancelButton: true,
                confirmButtonColor: '#8b5cf6',
                cancelButtonColor: '#64748b',
                confirmButtonText: '<i class="ph-bold ph-printer"></i> Ya, Cetak Surat Jalan',
                cancelButtonText: 'Tutup',
                customClass: { popup: 'swal2-custom-radius' }
            }).then((result) => {
                if (result.isConfirmed) {
                    window.open('<?= base_url('/wholesale/print_sj_gabungan/' . session()->getFlashdata('print_gabungan')) ?>', '_blank');
                }
            });
        <?php endif; ?>
    });
</script>

<?= $this->endSection() ?>