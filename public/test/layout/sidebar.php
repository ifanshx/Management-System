<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    /* --- SIDEBAR STYLE MODERN (OPTIMIZED) --- */
    :root {
        --sidebar-w: 260px;
        --primary-gradient: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        --sidebar-bg: #1e293b;
        --text-color: #cbd5e1;
        --hover-bg: rgba(255, 255, 255, 0.08);
        --primary-color: #3b82f6; 
    }

    .main-sidebar {
        width: var(--sidebar-w);
        background: var(--sidebar-bg);
        position: fixed;
        top: 0; bottom: 0; left: 0;
        z-index: 1040;
        overflow-y: auto; 
        transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        border-right: 1px solid rgba(255,255,255,0.05);
        display: flex;
        flex-direction: column;
    }
    
    /* Scrollbar Cantik */
    .main-sidebar::-webkit-scrollbar { width: 5px; }
    .main-sidebar::-webkit-scrollbar-track { background: transparent; }
    .main-sidebar::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.15); border-radius: 10px; }
    .main-sidebar::-webkit-scrollbar-thumb:hover { background: rgba(255,255,255,0.25); }

    /* LOGO AREA */
    .sb-logo-area {
        height: 80px; 
        min-height: 80px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(to bottom, rgba(255,255,255,0.02), rgba(0,0,0,0.1));
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        position: relative;
        overflow: hidden;
    }

    .sb-logo-img {
        max-height: 50px; 
        width: auto;
        transition: transform 0.4s cubic-bezier(0.34, 1.56, 0.64, 1); 
        filter: drop-shadow(0 0 8px rgba(255, 255, 255, 0.4));
        z-index: 2;
        position: relative;
    }

    .sb-logo-area:hover .sb-logo-img {
        transform: scale(1.1); 
        filter: drop-shadow(0 0 15px var(--primary-color));
    }

    /* Efek Kilau pada Logo */
    .sb-logo-area::after {
        content: '';
        position: absolute;
        top: -50%; left: -50%;
        width: 200%; height: 200%;
        background: linear-gradient(to right, rgba(255,255,255,0) 20%, rgba(255,255,255,0.1) 50%, rgba(255,255,255,0) 80%);
        transform: rotate(30deg);
        opacity: 0;
        pointer-events: none;
    }

    .sb-logo-area:hover::after {
        opacity: 1;
        transform: rotate(30deg) translate(100%, 0);
        transition: all 0.7s ease-in-out;
    }

    /* MENU LIST */
    .sidebar-menu { 
        padding: 15px 12px; 
        list-style: none; 
        margin: 0;
        flex-grow: 1; 
    }
    
    .sidebar-menu .header {
        color: #94a3b8; 
        font-size: 11px; 
        font-weight: 800;
        text-transform: uppercase; 
        letter-spacing: 1.2px;
        margin: 25px 0 10px 10px; 
        opacity: 0.7;
    }

    .sidebar-menu li a {
        display: flex; 
        align-items: center;
        padding: 11px 15px;
        color: var(--text-color); 
        text-decoration: none;
        font-size: 14px; 
        font-weight: 500;
        border-radius: 8px; 
        margin-bottom: 4px;
        transition: all 0.2s ease;
        border: 1px solid transparent;
    }
    
    .sidebar-menu li a i { 
        width: 24px; 
        text-align: center; 
        margin-right: 12px; 
        font-size: 15px; 
        transition: 0.2s; 
        color: #94a3b8; 
    }

    /* Hover State */
    .sidebar-menu li a:hover { 
        background: var(--hover-bg); 
        color: #fff; 
        transform: translateX(3px);
        border-color: rgba(255,255,255,0.05);
    }
    .sidebar-menu li a:hover i { color: #60a5fa; }

    /* Active State */
    .sidebar-menu li a.active {
        background: var(--primary-gradient);
        color: #fff; 
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.4); 
        font-weight: 600;
        border: none;
    }
    .sidebar-menu li a.active i { color: #fff; }

    /* LOGOUT BUTTON */
    .menu-logout {
        margin-top: 30px; 
        background: rgba(239, 68, 68, 0.1) !important;
        color: #ef4444 !important;
        border: 1px solid rgba(239, 68, 68, 0.3) !important;
        justify-content: center;
        font-weight: 700 !important;
        transition: all 0.3s ease !important;
    }
    .menu-logout i { color: #ef4444 !important; margin-right: 8px !important; }
    
    .menu-logout:hover {
        background: #ef4444 !important;
        color: #fff !important;
        border-color: #ef4444 !important;
        transform: translateY(-2px) !important;
        box-shadow: 0 4px 15px rgba(239, 68, 68, 0.4);
    }
    .menu-logout:hover i { color: #fff !important; }

    #sidebar-overlay {
        display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0;
        background: rgba(0,0,0,0.5); z-index: 1035; backdrop-filter: blur(2px);
    }
    
    @media (max-width: 768px) { 
        .main-sidebar { transform: translateX(-100%); } 
        body.sidebar-open .main-sidebar { transform: translateX(0); }
        body.sidebar-open #sidebar-overlay { display: block; }
    }
    
    @media (min-width: 769px) {
        body.sidebar-collapsed .main-sidebar { transform: translateX(-100%); }
    }
</style>

<div id="sidebar-overlay" onclick="toggleSidebar()"></div>

<aside class="main-sidebar">
    <div class="sb-logo-area">
        <img src="<?php echo $base_url; ?>assets/image/logo-noric.png" alt="NORIC" class="sb-logo-img">
    </div>

    <ul class="sidebar-menu">
        <li>
            <a href="<?php echo $base_url; ?>pages/dashboard.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
                <i class="fa-solid fa-chart-pie"></i> <span>Dashboard</span>
            </a>
        </li>

        <?php if($_SESSION['role'] == 'user'): ?>
            <li class="header">MENU PEGAWAI</li>
            
            <?php 
            // Cek Status Borongan
            $my_id = $_SESSION['user_id'];
            $q_cek = mysqli_query($conn, "SELECT status_karyawan FROM users WHERE id='$my_id'");
            $cek = mysqli_fetch_assoc($q_cek);
            
            if($cek && $cek['status_karyawan'] == 'Borongan'): 
            ?>
            <li>
                <a href="<?php echo $base_url; ?>pages/produksi/input_produksi.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'input_produksi.php' ? 'active' : ''; ?>">
                    <i class="fa-solid fa-gears"></i> <span>Input Produksi</span>
                </a>
            </li>
            <?php endif; ?>

            <li>
                <a href="<?php echo $base_url; ?>pages/users/data_absen.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'data_absen.php' ? 'active' : ''; ?>">
                    <i class="fa-solid fa-fingerprint"></i> <span>Riwayat Absen</span>
                </a>
            </li>
            <li>
                <a href="<?php echo $base_url; ?>pages/users/slip_gaji.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'slip_gaji.php' ? 'active' : ''; ?>">
                    <i class="fa-solid fa-file-invoice-dollar"></i> <span>Slip Gaji</span>
                </a>
            </li>
            <li>
                <a href="<?php echo $base_url; ?>pages/transaksi/bon.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'bon.php' ? 'active' : ''; ?>">
                    <i class="fa-solid fa-hand-holding-dollar"></i> <span>Uang Makan & Kasbon</span>
                </a>
            </li>
        <?php endif; ?>

        <?php if($_SESSION['role'] == 'admin'): ?>
            <li class="header">MASTER DATA</li>
            <li>
                <a href="<?php echo $base_url; ?>pages/master/karyawan.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'karyawan.php' ? 'active' : ''; ?>">
                    <i class="fa-solid fa-users-gear"></i> <span>Data Karyawan</span>
                </a>
            </li>
            <li>
                <a href="<?php echo $base_url; ?>pages/master/master_sales.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'master_sales.php' ? 'active' : ''; ?>">
                    <i class="fa-solid fa-user-tie"></i> <span>Data Sales</span>
                </a>
            </li>
            <li>
                <a href="<?php echo $base_url; ?>pages/master/izin_absensi.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'izin_absensi.php' ? 'active' : ''; ?>">
                   <i class="fa-solid fa-person-walking-arrow-right"></i> <span>Izin Karyawan</span>
                </a>
            </li>
            
            
            <li class="header">STOK BRG & BHN</li>
             <li>
                <a href="<?php echo $base_url; ?>pages/master/stok_barang.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'stok_barang.php' ? 'active' : ''; ?>">
                    <i class="fa-solid fa-boxes-stacked"></i> <span>Stok Barang Jadi</span>
                </a>
            </li>
            <li>
                <a href="<?php echo $base_url; ?>pages/master/stok_bahan_baku.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'stok_bahan_baku.php' ? 'active' : ''; ?>">
                    <i class="fa-solid fa-cubes-stacked"></i> <span>Stok Bahan Baku</span>
                </a>
            </li>
            <li>
                <a href="<?php echo $base_url; ?>pages/master/procurement_material.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'procurement_material.php' ? 'active' : ''; ?>">
                    <i class="fa-solid fa-cart-flatbed"></i> <span>Pre-Order Bahan</span>
                </a>
            </li>
           <li>
                <a href="<?php echo $base_url; ?>pages/master/hutang_dagang.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'hutang_dagang.php' ? 'active' : ''; ?>">
                    <i class="fa-solid fa-file-contract"></i> <span>Piutang Bahan</span>
                </a>
            </li>
             <li>
                <a href="<?php echo $base_url; ?>pages/transaksi/outgoing_material.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'outgoing_material.php' ? 'active' : ''; ?>">
                    <i class="fa-solid fa-dolly"></i> <span>Ambil Bahan Baku</span>
                </a>
            </li>
            
            <li class="header">TRANSAKSI</li>
            <li>
                <a href="<?php echo $base_url; ?>pages/transaksi/transaksi_penjualan.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'transaksi_penjualan.php' ? 'active' : ''; ?>">
                    <i class="fa-solid fa-shop"></i> <span>Penjualan Barang</span>
                </a>
            </li>
            <li>
                <a href="<?php echo $base_url; ?>pages/transaksi/orderan.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'orderan.php' ? 'active' : ''; ?>">
                    <i class="fa-solid fa-clipboard-list"></i> <span>Orderan Masuk</span>
                </a>
            </li>
             
            
             <li>
                <a href="<?php echo $base_url; ?>pages/transaksi/pengiriman.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'pengiriman.php' ? 'active' : ''; ?>">
                    <i class="fa-solid fa-truck-fast"></i> <span>Jadwal Pengiriman</span>
                </a>
            </li>
            
            
            
            <li class="header">KEUANGAN</li>
            <li>
                <a href="<?php echo $base_url; ?>pages/transaksi/utang_piutang.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'utang_piutang.php' ? 'active' : ''; ?>">
                    <i class="fa-solid fa-scale-balanced"></i> <span>Utang Piutang</span>
                </a>
            </li>
            <li>
                <a href="<?php echo $base_url; ?>pages/transaksi/kas.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'kas.php' ? 'active' : ''; ?>">
                    <i class="fa-solid fa-cash-register"></i> <span>Kas Operasional</span>
                </a>
            </li>
            <li>
                <a href="<?php echo $base_url; ?>pages/transaksi/uang_lainlain.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'uang_lainlain.php' ? 'active' : ''; ?>">
                    <i class="fa-solid fa-sack-dollar"></i> <span>Uang Lain-lain</span>
                </a>
            </li>
            <li>
                <a href="<?php echo $base_url; ?>pages/transaksi/uang_lembur_tambahan.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'uang_lembur_tambahan.php' ? 'active' : ''; ?>">
                    <i class="fa-solid fa-stopwatch"></i> <span>Uang Lembur MLM</span>
                </a>
            </li>
            <li class="header">KONFIRMASI</li>

            <li>
                <a href="<?php echo $base_url; ?>pages/produksi/konfirmasi_produksi.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'konfirmasi_produksi.php' ? 'active' : ''; ?>">
                    <i class="fa-solid fa-list-check"></i> <span>Verif. Produksi</span>
                </a>
            </li>
             <li>
                <a href="<?php echo $base_url; ?>pages/transaksi/konfirmasi_bon.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'konfirmasi_bon.php' ? 'active' : ''; ?>">
                    <i class="fa-solid fa-money-check-dollar"></i> <span>Verif. Kasbon & UM</span>
                </a>
            </li>
            
            
            <li class="header">LAPORAN</li>
            <li>
                <a href="<?php echo $base_url; ?>pages/laporan/laporan_absensi.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'laporan_absensi.php' ? 'active' : ''; ?>">
                    <i class="fa-solid fa-address-book"></i> <span>Lap. Absensi</span>
                </a>
            </li>
            <li>
                <a href="<?php echo $base_url; ?>pages/laporan/laporan_gaji.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'laporan_gaji.php' ? 'active' : ''; ?>">
                    <i class="fa-solid fa-file-contract"></i> <span>Lap. Gaji</span>
                </a>
            </li>
            <li>
                <a href="<?php echo $base_url; ?>pages/laporan/laporan_kasbon.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'laporan_kasbon.php' ? 'active' : ''; ?>">
                    <i class="fa-solid fa-receipt"></i> <span>Lap. Kasbon</span>
                </a>
            </li>
            <li>
                <a href="<?php echo $base_url; ?>pages/laporan/laporan_penjualan_barang.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'laporan_penjualan_barang.php' ? 'active' : ''; ?>">
                    <i class="fa-solid fa-chart-line"></i> <span>Lap. Penjualan</span>
                </a>
            </li>
            <li>
                <a href="<?php echo $base_url; ?>pages/laporan/laporan_stok_bahan.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'laporan_stok_bahan.php' ? 'active' : ''; ?>">
                    <i class="fa-solid fa-warehouse"></i> <span>Lap. Stok Bahan</span>
                </a>
            </li>
            <li>
                <a href="<?php echo $base_url; ?>pages/laporan/laporan_produksi.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'laporan_produksi.php' ? 'active' : ''; ?>">
                    <i class="fa-solid fa-chart-simple"></i> <span>Lap. Produksi</span>
                </a>
            </li>
            <li>
                <a href="<?php echo $base_url; ?>pages/laporan/laporan_kas.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'laporan_kas.php' ? 'active' : ''; ?>">
                    <i class="fa-solid fa-coins"></i> <span>Lap. Arus Kas</span>
                </a>
            </li>
            <li>
                <a href="<?php echo $base_url; ?>pages/laporan/laporan_orderan.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'laporan_orderan.php' ? 'active' : ''; ?>">
                    <i class="fa-solid fa-file-circle-check"></i> <span>Lap. Orderan</span>
                </a>
            </li>
            


            <li class="header">PENGATURAN</li>
            
            <li>
                <a href="<?php echo $base_url; ?>pages/settings/setting_gaji_tetap.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'setting_gaji_tetap.php' ? 'active' : ''; ?>">
                    <i class="fa-solid fa-money-bill-wave"></i> <span>Gaji Karyawan Tetap</span>
                </a>
            </li>
            <li>
                <a href="<?php echo $base_url; ?>pages/settings/setting_tarif_borongan.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'setting_tarif_borongan.php' ? 'active' : ''; ?>">
                    <i class="fa-solid fa-tag"></i> <span>Tarif Borongan</span>
                </a>
            </li>
            <li>
                <a href="<?php echo $base_url; ?>pages/settings/setting_jam_kerja.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'setting_jam_kerja.php' ? 'active' : ''; ?>">
                    <i class="fa-solid fa-clock"></i> <span>Jam & Sanksi</span>
                </a>
            </li>
            <li>
                <a href="<?php echo $base_url; ?>pages/master/admin_system.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'admin_system.php' ? 'active' : ''; ?>">
                    <i class="fa-solid fa-user-shield"></i> <span>Admin System</span>
                </a>
            </li>
            <li>
                <a href="<?php echo $base_url; ?>pages/master/tools_mesin.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'tools_mesin.php' ? 'active' : ''; ?>">
                    <i class="fa-solid fa-screwdriver-wrench"></i> <span>Tools Mesin</span>
                </a>
            </li>
        <?php endif; ?>

        <li>
            <a href="#" id="btnLogoutKeren" class="menu-logout">
                <i class="fa-solid fa-power-off"></i> <span>LOGOUT</span>
            </a>
        </li>
        
        <li style="height: 50px;"></li>
    </ul>
</aside>

<script>
    document.getElementById('btnLogoutKeren').addEventListener('click', function(e) {
        e.preventDefault(); 
        
        Swal.fire({
            title: 'Ingin Keluar?',
            text: "Sesi anda akan diakhiri sekarang.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444', 
            cancelButtonColor: '#f1f5f9', 
            confirmButtonText: 'Ya, Keluar!',
            cancelButtonText: 'Batal',
            customClass: {
                popup: 'rounded-xl',
                cancelButton: 'text-dark' 
            },
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                let timerInterval;
                Swal.fire({
                    title: 'Sampai Jumpa!',
                    html: 'Sedang mengeluarkan akun...',
                    timer: 1000,
                    timerProgressBar: true,
                    didOpen: () => {
                        Swal.showLoading();
                    },
                    willClose: () => {
                        window.location.href = "<?php echo $base_url; ?>logout.php";
                    }
                });
            }
        })
    });
</script>