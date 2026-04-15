<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="<?= csrf_hash() ?>">
    
    <title><?= esc($title ?? 'Dashboard') ?> | <?= esc($company['app_name'] ?? 'Noric ERP') ?></title>

    <link rel="icon" type="image/png" href="<?= base_url('uploads/logo/' . esc($company['logo_path'] ?? 'default-logo.png')) ?>">
    <link rel="apple-touch-icon" href="<?= base_url('uploads/logo/' . esc($company['logo_path'] ?? 'default-logo.png')) ?>">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://unpkg.com/@phosphor-icons/web@2.1.1/src/fill/style.css">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>

    <style>
        :root {
            --bg-base: #f5f7fb; --bg-base-alt: #eef2f7; --bg-surface: rgba(255, 255, 255, 0.88); --bg-surface-strong: #ffffff; --bg-soft: rgba(15, 23, 42, 0.03);
            --border-subtle: rgba(148, 163, 184, 0.18); --border-strong: rgba(148, 163, 184, 0.28);
            --text-main: #0f172a; --text-muted: #64748b; --text-soft: #94a3b8;
            --accent-main: #0ea5e9; --accent-main-dark: #0284c7; --accent-purple: #8b5cf6; --accent-green: #10b981; --accent-red: #ef4444; --accent-orange: #f59e0b;
            --sidebar-width: 292px;
            --radius-xs: 10px; --radius-sm: 14px; --radius-md: 18px; --radius-lg: 24px; --radius-xl: 28px;
            --shadow-sm: 0 2px 8px rgba(15, 23, 42, 0.04); --shadow-md: 0 10px 30px -12px rgba(15, 23, 42, 0.08); --shadow-lg: 0 20px 45px -15px rgba(15, 23, 42, 0.12); --shadow-xl: 0 28px 60px -20px rgba(15, 23, 42, 0.18);
            --transition-smooth: all .35s cubic-bezier(.16,1,.3,1); --transition-fast: all .2s ease;
        }

        html.dark {
            --bg-base: #09090b; --bg-base-alt: #111216; --bg-surface: rgba(18, 18, 20, 0.82); --bg-surface-strong: #121214; --bg-soft: rgba(255,255,255,0.03);
            --border-subtle: rgba(255,255,255,0.06); --border-strong: rgba(255,255,255,0.12);
            --text-main: #f8fafc; --text-muted: #94a3b8; --text-soft: #64748b;
            --accent-main: #38bdf8; --accent-main-dark: #0ea5e9; --accent-purple: #a78bfa; --accent-green: #34d399; --accent-red: #f87171; --accent-orange: #fbbf24;
            --shadow-sm: 0 2px 10px rgba(0,0,0,.2); --shadow-md: 0 12px 30px -12px rgba(0,0,0,.35); --shadow-lg: 0 20px 45px -15px rgba(0,0,0,.45); --shadow-xl: 0 28px 60px -20px rgba(0,0,0,.55);
        }

        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Plus Jakarta Sans', sans-serif; -webkit-font-smoothing: antialiased; text-rendering: optimizeLegibility; }
        
        /* Pencegah Overflow Horizontal di HP */
        html, body { overflow-x: hidden; width: 100vw; max-width: 100%; }
        
        html { scroll-behavior: smooth; }
        body { background: radial-gradient(circle at top left, rgba(14,165,233,.07), transparent 24%), radial-gradient(circle at top right, rgba(139,92,246,.06), transparent 22%), linear-gradient(180deg, var(--bg-base), var(--bg-base-alt)); color: var(--text-main); display: flex; min-height: 100vh; transition: background-color .3s ease, color .3s ease; }
        html.dark body { background: radial-gradient(circle at top left, rgba(14,165,233,.10), transparent 24%), radial-gradient(circle at top right, rgba(139,92,246,.10), transparent 22%), linear-gradient(180deg, var(--bg-base), var(--bg-base-alt)); }
        a { color: inherit; text-decoration: none;} img { display: block; max-width: 100%; } button, input, select, textarea { font: inherit; }

        .glass-card { background: var(--bg-surface); border: 1px solid var(--border-subtle); box-shadow: var(--shadow-md); backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px); border-radius: var(--radius-lg); }
        .surface-card { background: var(--bg-surface-strong); border: 1px solid var(--border-subtle); box-shadow: var(--shadow-sm); border-radius: var(--radius-lg); }
        .soft-divider { height: 1px; width: 100%; background: linear-gradient(90deg, transparent, var(--border-subtle), transparent); }
        .mono { font-family: 'Space Mono', monospace !important; }

        .btn-ui { display: inline-flex; align-items: center; justify-content: center; gap: 8px; padding: 12px 18px; border-radius: var(--radius-sm); border: 1px solid transparent; font-size: 13px; font-weight: 900; text-decoration: none; cursor: pointer; transition: var(--transition-smooth); line-height: 1; }
        .btn-ui-primary { background: linear-gradient(135deg, var(--accent-main), var(--accent-main-dark)); color: #fff; box-shadow: 0 12px 25px -10px rgba(14,165,233,.45); }
        .btn-ui-primary:hover { transform: translateY(-3px); box-shadow: 0 16px 30px -12px rgba(14,165,233,.5); }
        .btn-ui-soft { background: var(--bg-surface); border-color: var(--border-subtle); color: var(--text-main); }
        .btn-ui-soft:hover { transform: translateY(-2px); border-color: var(--accent-main); color: var(--accent-main); }

        .sidebar { width: var(--sidebar-width); height: calc(100vh - 24px); margin: 12px 0 12px 12px; background: linear-gradient(180deg, var(--bg-surface), rgba(255,255,255,.72)); border-radius: 28px; box-shadow: 0 0 0 1px var(--border-subtle), var(--shadow-lg); display: flex; flex-direction: column; position: fixed; top: 0; left: 0; z-index: 100; transition: var(--transition-smooth); overflow: hidden; backdrop-filter: blur(18px); -webkit-backdrop-filter: blur(18px); }
        html.dark .sidebar { background: linear-gradient(180deg, rgba(18,18,20,.90), rgba(18,18,20,.76)); }
        .sidebar::before { content: ''; position: absolute; top: -120px; left: -40px; width: 280px; height: 280px; background: radial-gradient(circle, rgba(14,165,233,.14), transparent 70%); pointer-events: none; }
        .sidebar::after { content: ''; position: absolute; bottom: -140px; right: -50px; width: 260px; height: 260px; background: radial-gradient(circle, rgba(139,92,246,.12), transparent 70%); pointer-events: none; }

        .brand { padding: 24px 20px 18px; display: flex; align-items: center; gap: 14px; position: relative; z-index: 2; }
        .brand::after { content: ''; position: absolute; bottom: 0; left: 20px; right: 20px; height: 1px; background: linear-gradient(90deg, transparent, var(--border-subtle), transparent); }
        .brand-logo-wrap { position: relative; width: 48px; height: 48px; border-radius: 16px; overflow: hidden; box-shadow: 0 12px 25px -10px rgba(14,165,233,.35); flex-shrink: 0; }
        .brand-logo-wrap::after { content: ''; position: absolute; inset: 0; border: 1px solid rgba(255,255,255,.22); border-radius: inherit; pointer-events: none; }
        .brand img { width: 100%; height: 100%; object-fit: cover; }
        .brand-text { font-weight: 900; font-size: 19px; letter-spacing: -.5px; color: var(--text-main); line-height: 1.05; }
        .brand-sub { font-size: 10px; font-weight: 900; color: var(--accent-main); text-transform: uppercase; letter-spacing: 1.5px; margin-top: 5px; }

        .nav-menu { padding: 14px 12px 20px 12px; flex-grow: 1; overflow-y: auto; overflow-x: hidden; scroll-behavior: smooth; position: relative; z-index: 2; }
        .nav-menu::-webkit-scrollbar { width: 5px; }
        .nav-menu::-webkit-scrollbar-track { background: transparent; }
        .nav-menu::-webkit-scrollbar-thumb { background: var(--border-strong); border-radius: 999px; }

        .nav-label { font-size: 10px; font-weight: 900; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1.4px; margin: 24px 10px 8px 10px; display: flex; align-items: center; opacity: .9; }
        .nav-label::after { content: ''; flex-grow: 1; height: 1px; background: var(--border-subtle); margin-left: 12px; opacity: .6; }

        .nav-link { display: flex; align-items: center; gap: 12px; padding: 12px 16px; color: var(--text-muted); text-decoration: none; font-weight: 800; font-size: 13px; border-radius: 16px; transition: var(--transition-smooth); margin-bottom: 5px; position: relative; overflow: hidden; }
        .nav-link::before { content: ''; position: absolute; inset: 0; background: linear-gradient(90deg, rgba(14,165,233,.08), transparent); opacity: 0; transition: .25s ease; }
        .nav-link i { font-size: 20px; transition: var(--transition-smooth); color: var(--text-muted); opacity: .9; flex-shrink: 0; }
        .nav-link:hover:not(.active) { color: var(--text-main); background: rgba(14,165,233,.05); transform: translateX(4px); }
        .nav-link:hover:not(.active)::before { opacity: 1; }
        .nav-link:hover:not(.active) i { color: var(--accent-main); transform: scale(1.08); opacity: 1; }

        .nav-link.active { background: linear-gradient(135deg, var(--accent-main), var(--accent-main-dark)); color: #fff; font-weight: 900; box-shadow: 0 10px 22px -10px rgba(14,165,233,.45); }
        .nav-link.active i { color: #fff; opacity: 1; filter: drop-shadow(0 2px 4px rgba(0,0,0,.2)); }
        .nav-link.active::after { content: ''; position: absolute; right: 14px; width: 8px; height: 8px; border-radius: 999px; background: rgba(255,255,255,.92); box-shadow: 0 0 0 4px rgba(255,255,255,.15); }

        .nav-logout { color: var(--accent-red); background: rgba(239,68,68,.05); margin-top: 22px; border: 1px dashed rgba(239,68,68,.18); }
        .nav-logout:hover:not(.active) { background: var(--accent-red); color: #fff; border-color: var(--accent-red); }
        .nav-logout i { color: inherit; }
        .nav-logout:hover:not(.active) i { color: #fff; }

        .workspace { margin-left: calc(var(--sidebar-width) + 12px); padding: 12px 28px 34px 28px; flex-grow: 1; display: flex; flex-direction: column; width: calc(100% - var(--sidebar-width) - 12px); min-height: 100vh; position: relative; overflow-x: hidden; }
        .workspace::before { content: ''; position: absolute; top: -100px; right: 10%; width: 320px; height: 320px; background: radial-gradient(circle, rgba(14,165,233,.08), transparent 70%); pointer-events: none; z-index: -1; }
        .workspace-content { flex-grow: 1; position: relative; width: 100%; overflow-x: hidden; }

        .header { display: flex; justify-content: space-between; align-items: center; padding: 8px 0 28px; gap: 20px; position: relative; z-index: 40; }
        .mobile-toggle { display: none; background: var(--bg-surface); border: 1px solid var(--border-subtle); color: var(--text-main); width: 46px; height: 46px; border-radius: 14px; font-size: 24px; cursor: pointer; box-shadow: var(--shadow-sm); transition: var(--transition-fast); flex-shrink: 0;}
        .mobile-toggle:hover { transform: translateY(-2px); color: var(--accent-main); border-color: var(--accent-main); }

        .header-left { display: flex; align-items: center; gap: 18px; flex: 1; min-width: 0;}
        .header-search { position: relative; max-width: 100%;}
        .header-search input { background: var(--bg-surface); border: 1px solid var(--border-subtle); padding: 13px 46px 13px 44px; border-radius: 16px; color: var(--text-main); width: 300px; font-size: 13px; font-weight: 700; outline: none; transition: var(--transition-smooth); box-shadow: var(--shadow-sm); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); }
        .header-search input::placeholder { color: var(--text-soft); }
        .header-search::before { content: '\f4a5'; font-family: 'Phosphor'; position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: var(--text-muted); font-size: 18px; pointer-events: none; }
        .header-search::after { content: 'Ctrl K'; position: absolute; right: 14px; top: 50%; transform: translateY(-50%); font-size: 10px; font-weight: 900; color: var(--text-muted); background: var(--bg-soft); border: 1px solid var(--border-subtle); padding: 4px 8px; border-radius: 999px; letter-spacing: .5px; }
        .header-search input:focus { border-color: var(--accent-main); box-shadow: 0 0 0 4px rgba(14,165,233,.12); width: 360px; }

        .header-actions { display: flex; align-items: center; gap: 14px; flex-shrink: 0; }
        .theme-toggle { background: var(--bg-surface); border: 1px solid var(--border-subtle); color: var(--text-muted); width: 46px; height: 46px; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 22px; cursor: pointer; transition: var(--transition-smooth); box-shadow: var(--shadow-sm); flex-shrink: 0; }
        .theme-toggle:hover { color: var(--accent-main); border-color: var(--accent-main); transform: translateY(-2px); }

        .profile-dropdown-wrapper { position: relative; } 
        .user-pill { display: flex; align-items: center; gap: 10px; background: var(--bg-surface); border: 1px solid var(--border-subtle); padding: 5px 14px 5px 5px; border-radius: 16px; cursor: pointer; transition: var(--transition-smooth); box-shadow: var(--shadow-sm); user-select: none; backdrop-filter: blur(14px); -webkit-backdrop-filter: blur(14px); }
        .user-pill:hover, .user-pill.active { border-color: var(--accent-main); box-shadow: 0 10px 22px -12px rgba(14,165,233,.22); transform: translateY(-2px); }
        .avatar { width: 38px; height: 38px; border-radius: 12px; background: linear-gradient(135deg, var(--accent-main), var(--accent-purple)); color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 900; font-size: 15px; box-shadow: inset 0 -2px 6px rgba(0,0,0,.18); flex-shrink: 0; }
        .user-info { display: flex; flex-direction: column; margin-left: 2px; min-width: 0; }
        .user-name { font-size: 13px; font-weight: 900; color: var(--text-main); line-height: 1.2; max-width: 150px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .user-role { font-size: 11px; font-weight: 800; color: var(--accent-main); line-height: 1.2; margin-top: 2px; }

        .profile-dropdown-menu { position: absolute; top: calc(100% + 14px); right: 0; width: 290px; background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: 20px; box-shadow: var(--shadow-xl); padding: 10px; opacity: 0; visibility: hidden; transform: translateY(-10px) scale(.98); transition: var(--transition-smooth); z-index: 1000; backdrop-filter: blur(18px); -webkit-backdrop-filter: blur(18px); }
        .profile-dropdown-menu.show { opacity: 1; visibility: visible; transform: translateY(0) scale(1); }
        .dropdown-header { padding: 16px; margin-bottom: 8px; background: var(--bg-soft); border-radius: 14px; border: 1px dashed var(--border-subtle); }
        .dropdown-item { display: flex; align-items: center; gap: 12px; padding: 11px 14px; color: var(--text-main); text-decoration: none; font-size: 13px; font-weight: 800; border-radius: 14px; transition: var(--transition-smooth); }
        .dropdown-item i { font-size: 18px; color: var(--text-muted); transition: color .2s; }
        .dropdown-item:hover { background: rgba(14,165,233,.06); color: var(--accent-main); transform: translateX(4px); }
        .dropdown-item:hover i { color: var(--accent-main); }
        .dropdown-item.text-danger:hover { background: rgba(239,68,68,.06); color: var(--accent-red); }
        .dropdown-item.text-danger:hover i { color: var(--accent-red); }
        .dropdown-divider { height: 1px; background: var(--border-subtle); margin: 8px 0; }

        /* =========================================================
           RESPONSIVE (MOBILE & TABLET FIX)
           ========================================================= */
        @media (max-width: 1200px) { 
            .header-search input { width: 240px; } 
            .header-search input:focus { width: 290px; } 
        }
        
        @media (max-width: 1024px) {
            .sidebar { transform: translateX(-120%); margin: 0; height: 100vh; border-radius: 0 28px 28px 0; box-shadow: 20px 0 60px rgba(0,0,0,.18); }
            .sidebar.active { transform: translateX(0); }
            .workspace { margin-left: 0; padding: 20px 18px 28px; width: 100vw; z-index: 5; overflow-x: hidden;}
            .mobile-toggle { display: flex; align-items: center; justify-content: center; }
            .header-search { display: none; }
            .overlay { position: fixed; inset: 0; background: rgba(0,0,0,.42); backdrop-filter: blur(4px); z-index: 90; display: none; opacity: 0; transition: opacity .3s; }
            .overlay.active { display: block; opacity: 1; }
        }
        
        @media (max-width: 640px) { 
            .header { gap: 14px; padding: 10px 0 20px 0;} 
            .user-info { display: none; } 
            .user-pill { padding-right: 10px; } 
            .profile-dropdown-menu { width: 260px; right: -10px; } 
        }
        
        .saas-footer { margin-top: auto; padding-top: 36px; padding-bottom: 10px; text-align: center; font-size: 12px; color: var(--text-muted); font-weight: 600; line-height: 1.7; width: 100%; clear: both; }
    </style>
</head>
<body>

    <div class="overlay" id="overlay"></div>

    <?php
        $session = session();
        $role    = $session->get('role');
        $isAdmin = ($role === 'admin');
        
        $deptName = '';
        $posName  = '';

        if (!$isAdmin && $session->get('employee_id')) {
            $db = \Config\Database::connect();
            $empRecord = $db->table('employees')
                            ->select('departments.name as dept_name, positions.name as pos_name')
                            ->join('departments', 'departments.id = employees.department_id', 'left')
                            ->join('positions', 'positions.id = employees.position_id', 'left')
                            ->where('employees.employee_id', $session->get('employee_id'))
                            ->get()->getRowArray();

            if ($empRecord) {
                $deptName = $empRecord['dept_name'] ?? 'Belum Diatur';
                $posName  = $empRecord['pos_name'] ?? 'Belum Diatur';
            }
        }

        $isManager = ($posName === 'Manager');
        
        $deptLower  = strtolower($deptName);
        $isHRD      = str_contains($deptLower, 'hrd') || str_contains($deptLower, 'manajemen');
        $isGudang   = str_contains($deptLower, 'gudang') || str_contains($deptLower, 'logistik');
        $isProduksi = str_contains($deptLower, 'produksi') || str_contains($deptLower, 'manufaktur');

        $showExecutive = $isAdmin || ($isManager && $isHRD);
        $showHRIS      = $isAdmin || $isHRD;
        $showProduction = $isAdmin || $isProduksi || $isHRD;
        $showAsset = $isAdmin || $isHRD;
        $showSupplyChain = $isAdmin || $isGudang || $isHRD;
        $showSales   = $isAdmin || $isGudang || $isHRD;
        $showFinance = $isAdmin || $isHRD;
        $showConfig  = $isAdmin;
    ?>

    <aside class="sidebar" id="sidebar">
        <div class="brand">
            <div class="brand-logo-wrap">
                <img src="<?= base_url('uploads/logo/' . esc($company['logo_path'] ?? 'default-logo.png')) ?>" alt="Logo">
            </div>
            <div>
                <div class="brand-text"><?= esc($company['app_name'] ?? 'ERP System') ?></div>
                <div class="brand-sub">Enterprise Edition</div>
            </div>
        </div>

        <div class="nav-menu">

            <?php if($showExecutive): ?>
                <div class="nav-label">Main Dashboard</div>
                <a href="<?= base_url('/dashboard') ?>" class="nav-link <?= (url_is('dashboard')) ? 'active' : '' ?>">
                    <i class="ph-bold ph-presentation-chart"></i> Dasbor Eksekutif
                </a>
            <?php endif; ?>

            <?php if(!$isAdmin): ?>
                <div class="nav-label">Employee Portal</div>
                <a href="<?= base_url('/portal') ?>" class="nav-link <?= (url_is('portal')) ? 'active' : '' ?>">
                    <i class="ph-bold ph-identification-card"></i> Beranda Saya
                </a>
                <a href="<?= base_url('/portal/slip_gaji') ?>" class="nav-link <?= (url_is('portal/slip_gaji')) ? 'active' : '' ?>">
                    <i class="ph-bold ph-receipt"></i> Slip Gaji Karyawan
                </a>
                <a href="<?= base_url('/leave') ?>" class="nav-link <?= (url_is('leave')) ? 'active' : '' ?>">
                    <i class="ph-bold ph-calendar-plus"></i> Pengajuan Cuti & Izin
                </a>
            <?php endif; ?>

            <?php if($showHRIS): ?>
                <div class="nav-label">HRIS & Payroll</div>
                <a href="<?= base_url('/employee') ?>" class="nav-link <?= (url_is('employee*')) ? 'active' : '' ?>">
                    <i class="ph-bold ph-users-three"></i> Data Karyawan
                </a>
                <a href="<?= base_url('/attendance') ?>" class="nav-link <?= (url_is('attendance*')) ? 'active' : '' ?>">
                    <i class="ph-bold ph-fingerprint"></i> Log Kehadiran
                </a>
                <a href="<?= base_url('/payroll') ?>" class="nav-link <?= (url_is('payroll*')) ? 'active' : '' ?>">
                    <i class="ph-bold ph-money"></i> Sistem Penggajian
                </a>
                <a href="<?= base_url('/cash_advance') ?>" class="nav-link <?= (url_is('cash_advance*')) ? 'active' : '' ?>">
                    <i class="ph-bold ph-hand-coins"></i> Pinjaman / Kasbon
                </a>
                <a href="<?= base_url('/leave/approval') ?>" class="nav-link <?= (url_is('leave/approval')) ? 'active' : '' ?>">
                    <i class="ph-bold ph-files"></i> Approval Cuti
                </a>
            <?php endif; ?>

            <?php if($showProduction || $showAsset): ?>
                <div class="nav-label">Manufaktur & Aset</div>
                
                <?php if($showProduction): ?>
                    <a href="<?= base_url('/production') ?>" class="nav-link <?= (url_is('production*')) ? 'active' : '' ?>">
                        <i class="ph-bold ph-factory"></i> Lantai Produksi & SPK
                    </a>
                <?php endif; ?>
                
                <?php if($showAsset): ?>
                    <a href="<?= base_url('/asset') ?>" class="nav-link <?= (url_is('asset*')) ? 'active' : '' ?>">
                        <i class="ph-bold ph-wrench"></i> Inventaris Mesin
                    </a>
                <?php endif; ?>
            <?php endif; ?>

            <?php if($showSupplyChain): ?>
                <div class="nav-label">Supply Chain</div>
                <a href="<?= base_url('/procurement') ?>" class="nav-link <?= (url_is('procurement*')) ? 'active' : '' ?>">
                    <i class="ph-bold ph-truck"></i> Pengadaan (PO)
                </a>
                <a href="<?= base_url('/warehouse/local-inventory') ?>" class="nav-link <?= (url_is('warehouse/local-inventory')) ? 'active' : '' ?>">
                    <i class="ph-bold ph-database"></i> Database Gudang
                </a>
            <?php endif; ?>

            <?php if($showSales): ?>
                <div class="nav-label">Sales & Commerce</div>
                <a href="<?= base_url('/sales/offline') ?>" class="nav-link <?= (url_is('sales/offline')) ? 'active' : '' ?>">
                    <i class="ph-bold ph-monitor"></i> Kasir Offline (POS)
                </a>
                <a href="<?= base_url('/wholesale') ?>" class="nav-link <?= (url_is('wholesale*')) ? 'active' : '' ?>">
                    <i class="ph-bold ph-handshake"></i> Penjualan B2B
                </a>
            <?php endif; ?>

            <?php if($showFinance): ?>
                <div class="nav-label">Finance & Ledger</div>
                <a href="<?= base_url('/accounting') ?>" class="nav-link <?= (url_is('accounting') || url_is('accounting/index')) ? 'active' : '' ?>">
                    <i class="ph-bold ph-chart-pie-slice"></i> Dasbor Finansial
                </a>
                <a href="<?= base_url('/finance/cash_index') ?>" class="nav-link <?= (url_is('finance*')) ? 'active' : '' ?>">
                    <i class="ph-bold ph-wallet"></i> Kas Operasional
                </a>
                <a href="<?= base_url('/investor') ?>" class="nav-link <?= (url_is('investor*')) ? 'active' : '' ?>">
                <i class="ph-bold ph-vault"></i> Pendanaan & Investor
                </a>
                <a href="<?= base_url('/companydebt') ?>" class="nav-link <?= (url_is('companydebt*')) ? 'active' : '' ?>">
                <i class="ph-bold ph-vault"></i> Hutang Perusahaan
                </a>
                <a href="<?= base_url('/accounting/journal') ?>" class="nav-link <?= (url_is('accounting/journal')) ? 'active' : '' ?>">
                    <i class="ph-bold ph-notebook"></i> Jurnal Manual
                </a>
                <a href="<?= base_url('/accounting/ledger') ?>" class="nav-link <?= (url_is('accounting/ledger')) ? 'active' : '' ?>">
                    <i class="ph-bold ph-book-open-text"></i> Penjelajah Buku Besar
                </a>
                <a href="<?= base_url('/accounting/coa') ?>" class="nav-link <?= (url_is('accounting/coa*')) ? 'active' : '' ?>">
                    <i class="ph-bold ph-tree-structure"></i> Bagan Akun (COA)
                </a>
            <?php endif; ?>

            <?php if($showConfig): ?>
                <div class="nav-label">System Config</div>
                <a href="<?= base_url('/setting/company') ?>" class="nav-link <?= (url_is('setting/company')) ? 'active' : '' ?>">
                    <i class="ph-bold ph-buildings"></i> Profil Perusahaan
                </a>
                <a href="<?= base_url('/setting/workshift_index') ?>" class="nav-link <?= (url_is('setting/workshift*')) ? 'active' : '' ?>">
                    <i class="ph-bold ph-clock"></i> Manajemen Shift
                </a>
                <a href="<?= base_url('/device') ?>" class="nav-link <?= (url_is('device*')) ? 'active' : '' ?>">
                    <i class="ph-bold ph-cpu"></i> Hardware IoT
                </a>
            <?php endif; ?>

            <a href="<?= base_url('/logout') ?>" class="nav-link nav-logout">
                <i class="ph-bold ph-sign-out"></i> Keluar Akses
            </a>
        </div>
    </aside>

    <main class="workspace">
        <header class="header">
            <button class="mobile-toggle" id="mobileToggle" aria-label="Toggle Menu">
                <i class="ph-bold ph-list"></i>
            </button>

            <div class="header-left">
                <div class="header-search">
                    <input type="text" placeholder="Pencarian cepat global..." aria-label="Search" id="globalSearchInput">
                </div>
            </div>

            <div class="header-actions">
                <button class="theme-toggle" id="themeToggle" aria-label="Toggle Dark Mode" title="Ganti Tema Visual">
                    <i class="ph-bold ph-moon" id="themeIcon"></i>
                </button>

                <div class="profile-dropdown-wrapper">
                    <div class="user-pill" id="profileDropdownBtn">
                        <div class="avatar"><?= strtoupper(substr($session->get('name') ?? 'A', 0, 1)) ?></div>
                        <div class="user-info">
                            <span class="user-name"><?= esc($session->get('name') ?? 'Administrator') ?></span>
                            <span class="user-role">
                                <?= $isAdmin ? 'Super Admin' : esc($posName) ?>
                            </span>
                        </div>
                        <i class="ph-bold ph-caret-down" style="color: var(--text-muted); font-size: 14px; margin-left: 4px;"></i>
                    </div>

                    <div class="profile-dropdown-menu" id="profileDropdownMenu">
                        <div class="dropdown-header">
                            <div style="font-weight: 900; font-size: 15px; color: var(--text-main); margin-bottom: 2px;">
                                <?= esc($session->get('name') ?? 'Administrator') ?>
                            </div>
                            <div style="font-size: 11px; color: var(--text-muted); font-family: 'Space Mono', monospace; font-weight: 700; margin-bottom: <?= !$isAdmin ? '6px' : '0' ?>;">
                                UID: <?= esc($session->get('employee_id') ?? 'SYSTEM-ROOT') ?>
                            </div>
                            <?php if(!$isAdmin && $deptName): ?>
                                <div style="font-size: 10px; font-weight: 800; color: var(--accent-main); background: rgba(14,165,233,.1); padding: 4px 8px; border-radius: 6px; display: inline-block;">
                                    <?= esc($deptName) ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <?php if(!$isAdmin): ?>
                            <a href="<?= base_url('/portal') ?>" class="dropdown-item">
                                <i class="ph-bold ph-user-circle"></i> Beranda Saya
                            </a>
                        <?php endif; ?>

                        <?php if($isAdmin): ?>
                            <a href="<?= base_url('/setting/company') ?>" class="dropdown-item">
                                <i class="ph-bold ph-sliders"></i> Preferensi Sistem
                            </a>
                        <?php endif; ?>

                        <div class="dropdown-divider"></div>

                        <a href="<?= base_url('/logout') ?>" class="dropdown-item text-danger">
                            <i class="ph-bold ph-sign-out"></i> Akhiri Sesi
                        </a>
                    </div>
                </div>
            </div>
        </header>

        <div class="workspace-content">
            <?= $this->renderSection('content') ?>
        </div>

        <footer class="saas-footer">
            <span style="font-weight: 900; color: var(--text-main);">
                &copy; <?= date('Y') ?> <?= esc($company['company_name'] ?? 'Noric Exhaust') ?>
            </span>
            — Hak Cipta Dilindungi.<br>
            Sistem ERP Manufaktur Terintegrasi · Premium Workspace Edition
        </footer>
    </main>

    <script>
        const sidebar = document.getElementById('sidebar');
        const toggleBtn = document.getElementById('mobileToggle');
        const overlay = document.getElementById('overlay');

        function toggleMenu() { sidebar.classList.toggle('active'); overlay.classList.toggle('active'); }
        function closeSidebar() { sidebar.classList.remove('active'); overlay.classList.remove('active'); }

        if (toggleBtn) toggleBtn.addEventListener('click', toggleMenu);
        if (overlay) overlay.addEventListener('click', closeSidebar);

        const themeToggleBtn = document.getElementById('themeToggle');
        const themeIcon = document.getElementById('themeIcon');
        const htmlElement = document.documentElement;

        function updateIcon() {
            if (htmlElement.classList.contains('dark')) {
                themeIcon.classList.remove('ph-moon'); themeIcon.classList.add('ph-sun');
            } else {
                themeIcon.classList.remove('ph-sun'); themeIcon.classList.add('ph-moon');
            }
        }
        
        updateIcon();

        themeToggleBtn.addEventListener('click', () => {
            const newTheme = htmlElement.classList.contains('dark') ? 'light' : 'dark';
            localStorage.setItem('theme', newTheme);
            if (newTheme === 'dark') htmlElement.classList.add('dark');
            else htmlElement.classList.remove('dark');
            updateIcon();
        });

        const profileBtn = document.getElementById('profileDropdownBtn');
        const profileMenu = document.getElementById('profileDropdownMenu');

        profileBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            profileMenu.classList.toggle('show');
            profileBtn.classList.toggle('active');
        });

        document.addEventListener('click', function(e) {
            if (!profileBtn.contains(e.target) && !profileMenu.contains(e.target)) {
                profileMenu.classList.remove('show');
                profileBtn.classList.remove('active');
            }
        });

        document.addEventListener('DOMContentLoaded', function() {
            <?php if (session()->getFlashdata('success')): ?>
                Swal.fire({
                    icon: 'success', title: 'Berhasil!',
                    html: <?= json_encode(session()->getFlashdata('success')) ?>,
                    toast: true, position: 'top-end', showConfirmButton: false, timer: 4500, timerProgressBar: true,
                    background: document.documentElement.classList.contains('dark') ? '#18181b' : '#ffffff',
                    color: document.documentElement.classList.contains('dark') ? '#f4f4f5' : '#09090b', iconColor: '#10b981'
                });
            <?php endif; ?>

            <?php if (session()->getFlashdata('error')): ?>
                Swal.fire({
                    icon: 'error', title: 'Peringatan Sistem',
                    html: <?= json_encode(session()->getFlashdata('error')) ?>,
                    toast: true, position: 'top-end', showConfirmButton: false, timer: 5500, timerProgressBar: true,
                    background: document.documentElement.classList.contains('dark') ? '#18181b' : '#ffffff',
                    color: document.documentElement.classList.contains('dark') ? '#f4f4f5' : '#09090b', iconColor: '#ef4444'
                });
            <?php endif; ?>
        });

        window.formatRupiah = function(angka) {
            let number_string = angka.value.replace(/[^,\d]/g, '').toString(), split = number_string.split(','), sisa = split[0].length % 3, rupiah = split[0].substr(0, sisa), ribuan = split[0].substr(sisa).match(/\d{3}/gi);
            if (ribuan) { let separator = sisa ? '.' : ''; rupiah += separator + ribuan.join('.'); }
            rupiah = split[1] !== undefined ? rupiah + ',' + split[1] : rupiah;
            angka.value = rupiah;
        }

        const globalSearchInput = document.getElementById('globalSearchInput');
        if(globalSearchInput) {
            globalSearchInput.addEventListener('keyup', function() {
                let input = this.value.toLowerCase();
                let tableRows = document.querySelectorAll(".emp-row, tbody tr"); 
                tableRows.forEach(row => {
                    if(row.innerText) {
                        let text = row.innerText.toLowerCase();
                        row.style.display = text.includes(input) ? "" : "none";
                    }
                });
            });
        }

        document.addEventListener('keydown', function(e) {
            if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
                e.preventDefault();
                const localSearch = document.getElementById('searchInput');
                if (localSearch) { localSearch.focus(); localSearch.select(); } 
                else if (globalSearchInput) { globalSearchInput.focus(); globalSearchInput.select(); }
            }
            if (e.key === 'Escape') { profileMenu.classList.remove('show'); profileBtn.classList.remove('active'); closeSidebar(); }
        });
    </script>
</body>
</html>