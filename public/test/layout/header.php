<?php
if (session_status() == PHP_SESSION_NONE) { session_start(); }
// $base_url = "http://localhost/absensi/"; 
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
    <title>NORIC RACING EXHAUST</title>

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <link rel="shortcut icon" href="<?php echo $base_url; ?>assets/image/favicon.ico" type="image/x-icon">

    <style>
        :root { 
            --primary: #2563eb; 
            --bg-body: #f1f5f9; 
            --text-main: #1e293b;
            --text-muted: #64748b;
            --sidebar-w: 260px; 
            --header-h: 70px; 
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        body { 
            font-family: 'Inter', sans-serif; 
            background-color: var(--bg-body); 
            color: var(--text-main); 
            overflow-x: hidden; 
            margin: 0; 
        }

        /* --- NAVBAR --- */
        .top-navbar {
            background: #ffffff; 
            height: var(--header-h);
            position: fixed; 
            top: 0; left: 0; right: 0; 
            z-index: 1030;
            box-shadow: var(--shadow-sm);
            display: flex; 
            align-items: center; 
            padding: 0 20px;
            margin-left: var(--sidebar-w);
            transition: margin-left 0.3s ease-in-out;
        }

        /* TOMBOL TOGGLE */
        #sidebar-toggle {
            background: transparent; 
            border: none; 
            font-size: 20px; 
            color: var(--text-main);
            cursor: pointer; 
            padding: 8px; 
            border-radius: 8px;
            margin-right: 15px;
            transition: 0.2s;
        }
        #sidebar-toggle:hover { background: #f8fafc; }

        /* BRAND LOGO */
        .brand-text { 
            font-weight: 800; 
            font-size: 18px; 
            color: var(--text-main); 
            display: flex; 
            align-items: center; 
            letter-spacing: -0.5px;
        }
        .brand-text i { 
            color: var(--primary); 
            margin-right: 10px; 
            font-size: 22px;
        }

        /* PROFILE SECTION */
        .top-right { 
            margin-left: auto; 
            display: flex; 
            align-items: center; 
            gap: 12px; 
        }
        .profile-info { text-align: right; line-height: 1.3; }
        .profile-name { font-weight: 600; font-size: 13px; color: var(--text-main); }
        .profile-role { font-size: 11px; color: var(--text-muted); text-transform: uppercase; font-weight: 500; }
        .profile-img { width: 40px; height: 40px; border-radius: 10px; border: 1px solid #e2e8f0; object-fit: cover; }

        /* --- CONTENT WRAPPER --- */
        .content-wrapper {
            margin-top: var(--header-h);
            margin-left: var(--sidebar-w);
            padding: 25px;
            min-height: calc(100vh - var(--header-h));
            transition: margin-left 0.3s ease-in-out;
            position: relative;
        }

        /* --- RESPONSIVE LOGIC --- */
        body.sidebar-collapsed .top-navbar { margin-left: 0; }
        body.sidebar-collapsed .content-wrapper { margin-left: 0; }

        @media (max-width: 768px) {
            .top-navbar { margin-left: 0; padding: 0 15px; }
            .content-wrapper { margin-left: 0; padding: 15px; }
            .profile-info { display: none; }
            .brand-text span { font-size: 16px; }
        }

        /* --- PRINT MODE FIX (PERBAIKAN UTAMA) --- */
        @media print {
            /* Sembunyikan elemen Navigasi dan Sidebar */
            .top-navbar, 
            .main-sidebar, 
            #sidebar-toggle,
            .sidebar-menu,
            .main-footer,
            #sidebar-overlay { 
                display: none !important; 
                visibility: hidden !important;
                height: 0 !important;
                width: 0 !important;
                position: absolute !important;
            }

            /* Reset Margin & Padding Konten Utama */
            body, .content-wrapper { 
                margin: 0 !important; 
                padding: 0 !important; 
                width: 100% !important; 
                background: white !important; 
                min-height: auto !important;
                margin-top: 0 !important;
                margin-left: 0 !important;
            }

            /* Paksa latar putih bersih */
            body { 
                background-color: #fff !important; 
                -webkit-print-color-adjust: exact; 
            }
        }
    </style>
</head>
<body>

<nav class="top-navbar">
    <button id="sidebar-toggle" onclick="toggleSidebar()">
        <i class="fa-solid fa-bars-staggered"></i>
    </button>
    
    <div class="top-right">
       <div class="brand-text">
        <i class="fa-brands fa-hive"></i> <span>NORIC RACING EXHAUST</span>
    </div>
    </div>
</nav>

<script>
    function toggleSidebar() {
        document.body.classList.toggle('sidebar-collapsed');
        const isCollapsed = document.body.classList.contains('sidebar-collapsed');
        localStorage.setItem('sidebarState', isCollapsed ? 'collapsed' : 'expanded');
    }

    document.addEventListener("DOMContentLoaded", function() {
        if (window.innerWidth > 768) {
            const savedState = localStorage.getItem('sidebarState');
            if (savedState === 'collapsed') {
                document.body.classList.add('sidebar-collapsed');
            }
        } else {
            document.body.classList.add('sidebar-collapsed'); 
        }
    });
</script>