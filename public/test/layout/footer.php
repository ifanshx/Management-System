<style>
    /* --- FOOTER MODERN STYLE --- */
    .main-footer {
        background: #fff;
        padding: 15px 30px;
        color: #64748b;
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 13px;
        border-top: 1px solid #e2e8f0;
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-left: 0; /* Default (Mobile first) */
        transition: margin-left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        z-index: 1000;
    }

    /* Link Style */
    .main-footer a {
        color: #4f46e5;
        text-decoration: none;
        font-weight: 700;
        transition: 0.2s;
    }
    .main-footer a:hover {
        color: #3730a3;
        text-decoration: underline;
    }

    /* Right Section (Version) */
    .footer-right {
        font-weight: 600;
        color: #94a3b8;
    }

    /* Print Media Query */
    @media print {
        .no-print { display: none !important; }
    }

    /* Responsive Logic agar Footer ikut geser sesuai Sidebar */
    @media (min-width: 769px) {
        .main-footer {
            margin-left: 260px; /* Lebar sidebar default */
        }
        body.sidebar-collapsed .main-footer {
            margin-left: 0;
        }
    }

    @media (max-width: 768px) {
        .main-footer {
            flex-direction: column;
            gap: 5px;
            text-align: center;
            margin-left: 0;
            padding-bottom: 80px; /* Ruang extra agar tidak tertutup nav hp jika ada */
        }
    }
</style>

<footer class="main-footer no-print">
    <div class="footer-left">
        <strong>Copyright &copy; <?php echo date('Y'); ?> <a href="#">NORIC RACING EXHAUST</a>.</strong>
        <span class="d-none d-sm-inline">All rights reserved.</span>
    </div>
    <div class="footer-right">
        <b>Version</b> 1.2.0
    </div>
</footer>

<script>
    // --- SIDEBAR TOGGLE LOGIC (SMART RESPONSIVE) ---
    function toggleSidebar() {
        const width = window.innerWidth;
        const body = document.body;

        if (width <= 768) {
            // Mode HP: Toggle Overlay & Sidebar muncul dari kiri
            body.classList.toggle('sidebar-open');
        } else {
            // Mode Desktop: Toggle Margin Content (Expand/Collapse)
            body.classList.toggle('sidebar-collapsed');
        }
    }

    // Auto-adjust saat layar diputar/resize
    window.addEventListener('resize', () => {
        const body = document.body;
        if (window.innerWidth > 768) {
            // Jika pindah ke desktop, hilangkan overlay mobile
            body.classList.remove('sidebar-open');
        } else {
            // Jika pindah ke mobile, hilangkan collapse desktop
            body.classList.remove('sidebar-collapsed');
        }
    });
</script>

</body>
</html>