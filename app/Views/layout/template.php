<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Dashboard') ?> | <?= esc($company['app_name'] ?? 'Noric ERP') ?></title>
    
    <link rel="icon" type="image/png" href="<?= base_url('uploads/logo/' . esc($company['logo_path'] ?? 'default-logo.png')) ?>">
    <link rel="apple-touch-icon" href="<?= base_url('uploads/logo/' . esc($company['logo_path'] ?? 'default-logo.png')) ?>">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
    
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        /* ==========================================================================
           1. CSS VARIABLES (DESIGN SYSTEM)
           ========================================================================== */
        :root {
            --bg-base: #f4f4f5; 
            --bg-surface: #ffffff; 
            --border-subtle: #e4e4e7;
            --text-main: #09090b; 
            --text-muted: #71717a;
            --accent-main: #2563eb; 
            --accent-light: rgba(37, 99, 235, 0.1); 
            --sidebar-width: 280px;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow-card: 0 4px 20px -2px rgba(0, 0, 0, 0.05);
            --shadow-dropdown: 0 20px 40px -10px rgba(0, 0, 0, 0.1);
            --transition-smooth: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        }

        html.dark {
            --bg-base: #09090b; 
            --bg-surface: #18181b; 
            --border-subtle: rgba(255, 255, 255, 0.08);
            --text-main: #f4f4f5; 
            --text-muted: #a1a1aa;
            --accent-main: #38bdf8; 
            --accent-light: rgba(56, 189, 248, 0.15);
            --shadow-card: 0 10px 30px -10px rgba(0, 0, 0, 0.5);
            --shadow-dropdown: 0 30px 60px -15px rgba(0, 0, 0, 0.8);
        }

        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Plus Jakarta Sans', sans-serif; -webkit-font-smoothing: antialiased; }
        body { background-color: var(--bg-base); color: var(--text-main); display: flex; min-height: 100vh; overflow-x: hidden; transition: background-color 0.3s ease; }

        /* ==========================================================================
           2. SIDEBAR NAVIGATION
           ========================================================================== */
        .sidebar {
            width: var(--sidebar-width); height: calc(100vh - 40px); margin: 20px 0 20px 20px;
            background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: 24px;
            box-shadow: var(--shadow-card); display: flex; flex-direction: column; position: fixed; z-index: 100;
            transition: var(--transition-smooth);
        }

        .brand { padding: 30px 25px 20px; display: flex; align-items: center; gap: 12px; }
        .brand img { width: 42px; height: 42px; border-radius: 12px; object-fit: cover; box-shadow: var(--shadow-sm); }
        .brand-text { font-weight: 900; font-size: 18px; letter-spacing: -0.5px; color: var(--text-main); line-height: 1.2;}

        .nav-menu { padding: 0 15px 20px 15px; margin-top: 10px; flex-grow: 1; overflow-y: auto; overflow-x: hidden; scroll-behavior: smooth; }
        .nav-menu::-webkit-scrollbar { width: 4px; }
        .nav-menu::-webkit-scrollbar-track { background: transparent; }
        .nav-menu::-webkit-scrollbar-thumb { background: var(--border-subtle); border-radius: 100px; }
        .nav-menu::-webkit-scrollbar-thumb:hover { background: var(--text-muted); }

        .nav-label { font-size: 10px; font-weight: 900; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px; margin: 24px 0 10px 15px; }
        
        .nav-link { display: flex; align-items: center; gap: 12px; padding: 12px 15px; color: var(--text-muted); text-decoration: none; font-weight: 700; font-size: 13px; border-radius: 14px; transition: var(--transition-smooth); margin-bottom: 4px; border: 1px solid transparent; position: relative;}
        .nav-link i { font-size: 20px; transition: transform 0.2s; }
        
        .nav-link:hover { color: var(--text-main); background: var(--bg-base); }
        .nav-link:hover i { transform: scale(1.15); }
        
        .nav-link.active { color: var(--accent-main); background: linear-gradient(90deg, var(--accent-light), transparent); font-weight: 800; border-color: rgba(37, 99, 235, 0.05); }
        html.dark .nav-link.active { border-color: rgba(56, 189, 248, 0.05); }

        /* ==========================================================================
           3. WORKSPACE & HEADER
           ========================================================================== */
        .workspace { margin-left: calc(var(--sidebar-width) + 20px); padding: 20px 40px; flex-grow: 1; display: flex; flex-direction: column; width: calc(100% - var(--sidebar-width) - 20px); min-height: 100vh;}
        
        .header { display: flex; justify-content: space-between; align-items: center; padding: 10px 0 35px; }
        .mobile-toggle { display: none; background: none; border: none; color: var(--text-main); font-size: 28px; cursor: pointer; }

        .header-left { display: flex; align-items: center; gap: 20px;}
        .header-search input { background: var(--bg-surface); border: 1px solid var(--border-subtle); padding: 14px 20px 14px 44px; border-radius: 100px; color: var(--text-main); width: 300px; font-size: 13px; font-weight: 600; outline: none; transition: var(--transition-smooth); box-shadow: var(--shadow-sm); background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='18' height='18' fill='%2371717a' viewBox='0 0 256 256'%3E%3Cpath d='M229.66,218.34l-50.07-50.06a88.11,88.11,0,1,0-11.31,11.31l50.06,50.07a8,8,0,0,0,11.32-11.32ZM40,112a72,72,0,1,1,72,72A72.08,72.08,0,0,1,40,112Z'%3E%3C/path%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: 16px center;}
        .header-search input:focus { border-color: var(--accent-main); box-shadow: 0 0 0 3px var(--accent-light); width: 340px;}

        .header-actions { display: flex; align-items: center; gap: 15px; }
        
        .theme-toggle { background: var(--bg-surface); border: 1px solid var(--border-subtle); color: var(--text-main); width: 46px; height: 46px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 22px; cursor: pointer; transition: var(--transition-smooth); box-shadow: var(--shadow-sm); }
        .theme-toggle:hover { color: var(--accent-main); border-color: var(--accent-main); transform: rotate(15deg);}

        /* ==========================================================================
           4. PROFILE DROPDOWN (SAAS STYLE)
           ========================================================================== */
        .profile-dropdown-wrapper { position: relative; }
        .user-pill { display: flex; align-items: center; gap: 10px; background: var(--bg-surface); border: 1px solid var(--border-subtle); padding: 6px 16px 6px 6px; border-radius: 100px; cursor: pointer; transition: var(--transition-smooth); box-shadow: var(--shadow-sm); user-select: none; }
        .user-pill:hover, .user-pill.active { border-color: var(--accent-main); background: var(--bg-base); }
        
        .avatar { width: 34px; height: 34px; border-radius: 50%; background: linear-gradient(135deg, var(--accent-main), #8b5cf6); color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 900; font-size: 15px; }
        .user-info { display: flex; flex-direction: column; margin-left: 2px; }
        .user-name { font-size: 13px; font-weight: 800; color: var(--text-main); line-height: 1.2; }
        .user-role { font-size: 11px; font-weight: 600; color: var(--text-muted); line-height: 1.2; }

        .profile-dropdown-menu { position: absolute; top: calc(100% + 15px); right: 0; width: 280px; background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: 20px; box-shadow: var(--shadow-dropdown); padding: 8px; opacity: 0; visibility: hidden; transform: translateY(-15px) scale(0.95); transition: var(--transition-smooth); z-index: 1000; backdrop-filter: blur(10px); }
        .profile-dropdown-menu.show { opacity: 1; visibility: visible; transform: translateY(0) scale(1); }
        
        .dropdown-header { padding: 15px; margin-bottom: 4px; background: var(--bg-base); border-radius: 14px;}
        .dropdown-item { display: flex; align-items: center; gap: 12px; padding: 12px 15px; color: var(--text-main); text-decoration: none; font-size: 13px; font-weight: 700; border-radius: 12px; transition: var(--transition-smooth); }
        .dropdown-item i { font-size: 20px; color: var(--text-muted); transition: color 0.2s; }
        .dropdown-item:hover { background: var(--bg-base); color: var(--accent-main); padding-left: 20px;}
        .dropdown-item:hover i { color: var(--accent-main); }
        
        .dropdown-item.text-danger:hover { background: rgba(239, 68, 68, 0.1); color: #ef4444; padding-left: 20px;}
        .dropdown-item.text-danger:hover i { color: #ef4444; }
        .dropdown-divider { height: 1px; background: var(--border-subtle); margin: 8px 0; }

        /* ==========================================================================
           5. GLOBAL AJAX TOAST NOTIFICATION
           ========================================================================== */
        #globalAjaxToast {
            position: fixed; top: 30px; right: -450px;
            background: #10b981; color: #fff; padding: 16px 24px; border-radius: 16px; font-size: 14px; font-weight: 800;
            box-shadow: 0 10px 30px rgba(16, 185, 129, 0.4); display: flex; align-items: center; gap: 12px; z-index: 9999;
            transition: right 0.5s cubic-bezier(0.16, 1, 0.3, 1);
        }
        #globalAjaxToast.show { right: 30px; }
        #globalAjaxToast.error { background: #ef4444; box-shadow: 0 10px 30px rgba(239, 68, 68, 0.4); }

        .saas-footer { margin-top: auto; padding-top: 40px; padding-bottom: 20px; text-align: center; font-size: 12px; color: var(--text-muted); font-weight: 600; }

        /* ==========================================================================
           6. RESPONSIVE MOBILE
           ========================================================================== */
        @media (max-width: 1024px) {
            .sidebar { transform: translateX(-120%); margin: 0; height: 100vh; border-radius: 0 24px 24px 0; }
            .sidebar.active { transform: translateX(0); }
            .workspace { margin-left: 0; padding: 20px; width: 100%; }
            .mobile-toggle { display: block; } 
            .header-search { display: none; }
            .overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); backdrop-filter: blur(4px); z-index: 90; display: none; opacity: 0; transition: opacity 0.3s; }
            .overlay.active { display: block; opacity: 1; }
        }
    </style>
</head>
<body>

    <div class="overlay" id="overlay"></div>

    <div id="globalAjaxToast">
        <i class="ph-bold ph-check-circle" style="font-size: 24px;"></i> 
        <span id="globalToastMsg">Berhasil!</span>
    </div>

    <aside class="sidebar" id="sidebar">
        <div class="brand">
            <img src="<?= base_url('uploads/logo/' . esc($company['logo_path'] ?? 'default-logo.png')) ?>" alt="Logo">
            <div class="brand-text"><?= esc($company['app_name'] ?? 'ERP System') ?></div>
        </div>

       <div class="nav-menu">
            <?php 
                $role = session()->get('role');
                $dept = session()->get('department');
                $isAdmin = ($role === 'admin'); 
            ?>

           <?php if($isAdmin): ?>
                <div class="nav-label">Command Center</div>
                <a href="<?= base_url('/dashboard') ?>" class="nav-link <?= (url_is('dashboard')) ? 'active' : '' ?>">
                    <i class="ph-bold ph-presentation-chart"></i> Dasbor Eksekutif
                </a>
                
                <a href="<?= base_url('/setting/company') ?>" class="nav-link <?= (url_is('setting/company')) ? 'active' : '' ?>">
                    <i class="ph-bold ph-buildings"></i> Identitas Perusahaan
                </a>

                <a href="<?= base_url('/setting/workshift_create') ?>" class="nav-link <?= (url_is('setting/workshift*')) ? 'active' : '' ?>">
                    <i class="ph-bold ph-clock"></i> Parameter Shift Kerja
                </a>
            <?php else: ?>
                <div class="nav-label">Employee Portal</div>
                <a href="<?= base_url('/portal') ?>" class="nav-link <?= (url_is('portal')) ? 'active' : '' ?>">
                    <i class="ph-bold ph-identification-card"></i> Portal Saya
                </a>
                <a href="<?= base_url('/portal/slip_gaji') ?>" class="nav-link <?= (url_is('portal/slip_gaji')) ? 'active' : '' ?>">
                    <i class="ph-bold ph-receipt"></i> Slip Gaji
                </a>
                <a href="<?= base_url('/leave') ?>" class="nav-link <?= (url_is('leave')) ? 'active' : '' ?>">
                    <i class="ph-bold ph-calendar-plus"></i> Form Cuti / Izin
                </a>
                <div class="nav-label" style="color: var(--accent-main); margin-top: 25px; border-top: 1px dashed var(--border-subtle); padding-top: 15px;">
                    Area Kerja: <?= esc($dept ?? 'Umum') ?>
                </div>
            <?php endif; ?>

            <?php if($isAdmin || $dept === 'Produksi & Manufaktur'): ?>
                <div class="nav-label" style="margin-top:20px;">Pabrik & Manufaktur</div>
                <a href="<?= base_url('/production') ?>" class="nav-link <?= (url_is('production*')) ? 'active' : '' ?>">
                    <i class="ph-bold ph-factory"></i> Produksi & BoM
                </a>
                <a href="<?= base_url('/asset') ?>" class="nav-link <?= (url_is('asset*')) ? 'active' : '' ?>">
                    <i class="ph-bold ph-wrench"></i> Manajemen Aset Mesin
                </a>
            <?php endif; ?>

            <?php if($isAdmin || $dept === 'Admin Sales' || $dept === 'Logistik & Gudang'): ?>
                <div class="nav-label" style="margin-top:20px;">Supply Chain & Gudang</div>
                
                <a href="<?= base_url('/procurement') ?>" class="nav-link <?= (url_is('procurement*')) ? 'active' : '' ?>">
                    <i class="ph-bold ph-truck"></i> Procurement (PO)
                </a>
                <a href="<?= base_url('/warehouse/local-inventory') ?>" class="nav-link <?= (url_is('warehouse/local-inventory')) ? 'active' : '' ?>">
                    <i class="ph-bold ph-database"></i> Master Gudang Lokal
                </a>
                <a href="<?= base_url('/warehouse/mass-fulfillment') ?>" class="nav-link <?= (url_is('warehouse/mass-fulfillment')) ? 'active' : '' ?>">
                    <i class="ph-bold ph-stack"></i> Pusat Cetak Massal
                </a>
                <a href="<?= base_url('/warehouse/orders') ?>" class="nav-link <?= (url_is('warehouse/orders')) ? 'active' : '' ?>">
                    <i class="ph-bold ph-package"></i> Antrean Packing
                </a>
                <a href="<?= base_url('/warehouse/cancellation-hub') ?>" class="nav-link <?= (url_is('warehouse/cancellation-hub')) ? 'active' : '' ?>">
                    <i class="ph-bold ph-warning-circle"></i> Resolusi Pembatalan
                </a>

                <div class="nav-label" style="margin-top:20px;">E-Commerce B2C</div>
                <a href="<?= base_url('/shopee') ?>" class="nav-link <?= (url_is('shopee*') || url_is('marketing*')) ? 'active' : '' ?>">
                    <i class="ph-bold ph-storefront"></i> Shopee Omnichannel
                </a>
            <?php endif; ?>

            <?php if($isAdmin || $dept === 'Manajemen & HRD'): ?>
                <div class="nav-label" style="margin-top:20px;">Keuangan & Penjualan</div>
                
                <a href="<?= base_url('/accounting') ?>" class="nav-link <?= (url_is('accounting*')) ? 'active' : '' ?>">
                    <i class="ph-bold ph-books"></i> Buku Besar (GL)
                </a>
                <a href="<?= base_url('/wholesale') ?>" class="nav-link <?= (url_is('wholesale*')) ? 'active' : '' ?>">
                    <i class="ph-bold ph-handshake"></i> B2B Grosir & Piutang
                </a>
                <a href="<?= base_url('/sales/offline') ?>" class="nav-link <?= (url_is('sales/offline')) ? 'active' : '' ?>">
                    <i class="ph-bold ph-monitor"></i> POS Kasir Pabrik
                </a>
                <a href="<?= base_url('/finance/cash_index') ?>" class="nav-link <?= (url_is('finance*')) ? 'active' : '' ?>">
                    <i class="ph-bold ph-wallet"></i> Kas Operasional
                </a>

                <div class="nav-label" style="margin-top:20px;">HRD & IoT Absensi</div>
                <a href="<?= base_url('/employee') ?>" class="nav-link <?= (url_is('employee*')) ? 'active' : '' ?>">
                    <i class="ph-bold ph-users-three"></i> Database Karyawan
                </a>
                <a href="<?= base_url('/attendance') ?>" class="nav-link <?= (url_is('attendance*')) ? 'active' : '' ?>">
                    <i class="ph-bold ph-fingerprint"></i> Data Absensi Mesin
                </a>
                <a href="<?= base_url('/payroll') ?>" class="nav-link <?= (url_is('payroll*')) ? 'active' : '' ?>">
                    <i class="ph-bold ph-money"></i> Kelola Penggajian
                </a>
                <a href="<?= base_url('/leave/approval') ?>" class="nav-link <?= (url_is('leave/approval')) ? 'active' : '' ?>">
                    <i class="ph-bold ph-files"></i> Approval Cuti Karyawan
                </a>
                <?php if($isAdmin): ?>
                    <a href="<?= base_url('/device') ?>" class="nav-link <?= (url_is('device*')) ? 'active' : '' ?>">
                        <i class="ph-bold ph-cpu"></i> Control Panel IoT
                    </a>
                <?php endif; ?>
            <?php endif; ?>

            <a href="<?= base_url('/logout') ?>" class="nav-link" style="color: #ef4444; margin-top: 30px; border-top: 1px dashed var(--border-subtle); border-radius: 0; padding-top: 20px;">
                <i class="ph-bold ph-sign-out"></i> Keluar Sistem
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
                    <input type="text" placeholder="Cari menu... (Ctrl+K)" aria-label="Search">
                </div>
            </div>

            <div class="header-actions">
                <button class="theme-toggle" id="themeToggle" aria-label="Toggle Dark Mode">
                    <i class="ph-bold ph-moon" id="themeIcon"></i>
                </button>

                <div class="profile-dropdown-wrapper">
                    <div class="user-pill" id="profileDropdownBtn">
                        <div class="avatar"><?= strtoupper(substr(session()->get('name') ?? 'A', 0, 1)) ?></div>
                        <div class="user-info">
                            <span class="user-name"><?= esc(session()->get('name') ?? 'Administrator') ?></span>
                            <span class="user-role">
                                <?php 
                                    if(session()->get('role') === 'admin') echo 'System Admin';
                                    else echo esc(session()->get('position') ?? 'Karyawan');
                                ?>
                            </span>
                        </div>
                        <i class="ph-bold ph-caret-down" style="color: var(--text-muted); font-size: 14px; margin-left: 8px;"></i>
                    </div>

                    <div class="profile-dropdown-menu" id="profileDropdownMenu">
                        <div class="dropdown-header">
                            <div style="font-weight: 900; font-size: 16px; color: var(--text-main); margin-bottom: 2px;">
                                <?= esc(session()->get('name') ?? 'Administrator') ?>
                            </div>
                            <div style="font-size: 11px; color: var(--text-muted); font-family: 'Space Mono', monospace; font-weight: 700;">
                                ID: <?= esc(session()->get('employee_id') ?? 'SYSTEM-ROOT') ?>
                            </div>
                        </div>
                        <div class="dropdown-divider"></div>
                        <a href="<?= base_url('/profile') ?>" class="dropdown-item"><i class="ph-bold ph-user"></i> Profil Saya</a>
                        <a href="<?= base_url('/setting/company') ?>" class="dropdown-item"><i class="ph-bold ph-gear-six"></i> Pengaturan Sistem</a>
                        <div class="dropdown-divider"></div>
                        <a href="<?= base_url('/logout') ?>" class="dropdown-item text-danger"><i class="ph-bold ph-sign-out"></i> Keluar Sistem</a>
                    </div>
                </div>
            </div>
        </header>

        <?= $this->renderSection('content') ?>

        <footer class="saas-footer">
            &copy; <?= date('Y') ?> <b><?= esc($company['company_name'] ?? 'Noric Exhaust') ?></b>. Crafted specifically for Enterprise Manufacturing.
        </footer>
    </main>

    <script>
        // 1. Sidebar Toggle Logic
        const sidebar = document.getElementById('sidebar');
        const toggleBtn = document.getElementById('mobileToggle');
        const overlay = document.getElementById('overlay');

        function toggleMenu() {
            sidebar.classList.toggle('active');
            overlay.classList.toggle('active');
        }
        toggleBtn.addEventListener('click', toggleMenu);
        overlay.addEventListener('click', toggleMenu);

        // 2. Dark Mode Engine
        const themeToggleBtn = document.getElementById('themeToggle');
        const themeIcon = document.getElementById('themeIcon');
        const htmlElement = document.documentElement;

        if (localStorage.getItem('theme') === 'dark') {
            htmlElement.classList.add('dark');
            themeIcon.classList.replace('ph-moon', 'ph-sun');
        }

        themeToggleBtn.addEventListener('click', () => {
            htmlElement.classList.toggle('dark');
            if (htmlElement.classList.contains('dark')) {
                localStorage.setItem('theme', 'dark');
                themeIcon.classList.replace('ph-moon', 'ph-sun');
            } else {
                localStorage.setItem('theme', 'light');
                themeIcon.classList.replace('ph-sun', 'ph-moon');
            }
        });

        // 3. Profile Dropdown Interaction
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

        // 4. GLOBAL AJAX TOAST FUNCTION (Tersedia untuk semua halaman)
        window.showGlobalToast = function(msg, isError = false) {
            const toast = document.getElementById('globalAjaxToast');
            const msgEl = document.getElementById('globalToastMsg');
            
            msgEl.innerText = msg;
            
            if(isError) {
                toast.classList.add('error');
                toast.innerHTML = `<i class="ph-bold ph-warning-circle" style="font-size: 24px;"></i> <span id="globalToastMsg" style="line-height:1.4;">${msg}</span>`;
            } else {
                toast.classList.remove('error');
                toast.innerHTML = `<i class="ph-bold ph-check-circle" style="font-size: 24px;"></i> <span id="globalToastMsg" style="line-height:1.4;">${msg}</span>`;
            }
            
            toast.classList.add('show');
            setTimeout(() => { toast.classList.remove('show'); }, 3500);
        }

        // 5. AUTO-CATCH FLASHDATA CI4 INTO SWEETALERT TOAST
        // Menangkap redirect tradisional dari backend dan mengubahnya menjadi pop-up modern
        document.addEventListener('DOMContentLoaded', function() {
            <?php if (session()->getFlashdata('success')): ?>
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: '<?= session()->getFlashdata('success') ?>',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 4000,
                    timerProgressBar: true,
                    background: document.documentElement.classList.contains('dark') ? '#18181b' : '#ffffff',
                    color: document.documentElement.classList.contains('dark') ? '#f4f4f5' : '#09090b',
                    iconColor: '#10b981'
                });
            <?php endif; ?>

            <?php if (session()->getFlashdata('error')): ?>
                Swal.fire({
                    icon: 'error',
                    title: 'Akses Ditolak / Gagal',
                    text: '<?= session()->getFlashdata('error') ?>',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 5000,
                    timerProgressBar: true,
                    background: document.documentElement.classList.contains('dark') ? '#18181b' : '#ffffff',
                    color: document.documentElement.classList.contains('dark') ? '#f4f4f5' : '#09090b',
                    iconColor: '#ef4444'
                });
            <?php endif; ?>
        });
    </script>
</body>
</html>