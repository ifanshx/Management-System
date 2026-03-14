<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Autentikasi | <?= esc($company['app_name'] ?? 'Enterprise ERP') ?></title>
    
    <link rel="shortcut icon" type="image/png" href="<?= base_url('uploads/logo/' . esc($company['logo_path'] ?? 'default-logo.png')) ?>">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&family=Space+Mono:ital,wght@0,400;0,700;1,400;1,700&display=swap" rel="stylesheet">
    
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        :root {
            --brand-primary: #2563eb;
            --brand-dark: #1e3a8a;
            --brand-light: #60a5fa;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --bg-page: #f8fafc;
            --bg-card: #ffffff;
            --border-line: #e2e8f0;
        }

        * { 
            box-sizing: border-box; 
            margin: 0; 
            padding: 0; 
            font-family: 'Plus Jakarta Sans', sans-serif; 
        }

        body {
            background-color: var(--bg-page);
            background-image: radial-gradient(var(--border-line) 1px, transparent 1px);
            background-size: 24px 24px;
            display: flex; 
            justify-content: center; 
            align-items: center;
            min-height: 100vh; 
            padding: 20px;
            color: var(--text-main);
        }

        /* --- WRAPPER UTAMA (BENTO SPLIT) --- */
        .login-wrapper {
            display: flex; 
            background: var(--bg-card); 
            border-radius: 28px;
            box-shadow: 0 20px 40px -15px rgba(0, 0, 0, 0.1), 0 0 0 1px rgba(0,0,0,0.02);
            overflow: hidden; 
            width: 1000px; 
            max-width: 100%; 
            min-height: 600px;
            position: relative;
        }

        /* --- PANEL KIRI (BRANDING & VISUAL) --- */
        .login-left {
            flex: 1.2;
            background: linear-gradient(135deg, var(--brand-dark) 0%, var(--brand-primary) 100%);
            color: #ffffff; 
            display: flex; 
            flex-direction: column;
            justify-content: center; 
            align-items: center;
            padding: 50px 40px; 
            text-align: center; 
            position: relative; 
            overflow: hidden;
        }

        /* Efek Cahaya / Glow pada Panel Kiri */
        .login-left::before {
            content: ''; position: absolute; top: -100px; left: -100px;
            width: 400px; height: 400px; background: radial-gradient(circle, rgba(255, 255, 255, 0.15) 0%, transparent 70%); border-radius: 50%;
        }
        .login-left::after {
            content: ''; position: absolute; bottom: -150px; right: -150px;
            width: 500px; height: 500px; background: radial-gradient(circle, rgba(96, 165, 250, 0.2) 0%, transparent 70%); border-radius: 50%;
        }

        .logo-box {
            background: rgba(255,255,255,0.1);
            backdrop-filter: blur(10px);
            padding: 20px;
            border-radius: 24px;
            border: 1px solid rgba(255,255,255,0.2);
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
            margin-bottom: 35px;
            z-index: 10;
        }

        .login-left img {
            width: 110px; 
            height: auto; 
            border-radius: 12px;
            display: block;
        }

        .brand-title {
            font-size: 36px;
            font-weight: 900; 
            margin-bottom: 8px;
            letter-spacing: -1px;
            text-shadow: 0 4px 15px rgba(0,0,0,0.2);
            z-index: 10;
            line-height: 1.1;
        }

        .company-name {
            font-size: 14px; 
            font-weight: 600; 
            letter-spacing: 2px;
            color: rgba(255,255,255,0.8); 
            text-transform: uppercase; 
            z-index: 10;
        }

        .copyright {
            position: absolute; 
            bottom: 30px; 
            font-size: 11px; 
            color: rgba(255,255,255,0.5); 
            z-index: 10; 
            font-weight: 500;
            font-family: 'Space Mono', monospace;
        }

        /* --- PANEL KANAN (FORMULIR INTERAKTIF) --- */
        .login-right {
            flex: 1; 
            display: flex; 
            flex-direction: column; 
            justify-content: center;
            padding: 60px 80px; 
            background: #ffffff;
        }

        .header-form { margin-bottom: 40px; }
        .header-form h1 { 
            color: var(--text-main); 
            font-weight: 900; 
            font-size: 32px; 
            margin-bottom: 10px; 
            letter-spacing: -1px;
        }
        .header-form p { 
            color: var(--text-muted); 
            font-size: 14px; 
            font-weight: 500; 
            line-height: 1.5; 
        }
        .header-form b { color: var(--brand-primary); }

        /* Floating Input Fields */
        .input-group { 
            position: relative; 
            margin-bottom: 25px; 
        }
        .input-group input {
            width: 100%; 
            background-color: transparent; 
            border: 2px solid var(--border-line);
            padding: 20px 20px 12px; 
            border-radius: 16px; 
            font-size: 15px; 
            font-weight: 700;
            color: var(--text-main); 
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            outline: none;
        }
        
        .input-group input:hover { border-color: #cbd5e1; }
        .input-group input:focus { 
            border-color: var(--brand-primary); 
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1); 
            background-color: rgba(37, 99, 235, 0.02);
        }
        
        .input-group label {
            position: absolute; 
            left: 20px; 
            top: 50%; 
            transform: translateY(-50%);
            color: var(--text-muted); 
            font-size: 14px; 
            font-weight: 600; 
            pointer-events: none;
            transition: all 0.2s ease-out; 
            background-color: transparent; 
            padding: 0 4px;
        }
        
        /* Efek teks melayang naik saat diketik / fokus */
        .input-group input:focus ~ label, 
        .input-group input:not(:placeholder-shown) ~ label {
            top: 0; 
            font-size: 11px; 
            color: var(--brand-primary); 
            font-weight: 800;
            background-color: #fff;
        }

        .toggle-password {
            position: absolute; 
            right: 20px; 
            top: 50%; 
            transform: translateY(-50%);
            cursor: pointer; 
            color: var(--text-muted); 
            transition: color 0.3s; 
            display: flex; 
            align-items: center;
            font-size: 20px;
        }
        .toggle-password:hover { color: var(--brand-primary); }

        /* Tombol Login Super Premium */
        button.btn-login {
            width: 100%; 
            border-radius: 16px; 
            border: none; 
            background: linear-gradient(135deg, var(--brand-primary), #1d4ed8);
            color: #FFFFFF; 
            font-size: 16px; 
            font-weight: 900; 
            padding: 18px; 
            cursor: pointer; 
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            margin-top: 15px; 
            box-shadow: 0 10px 25px -5px rgba(37, 99, 235, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        button.btn-login:hover { 
            transform: translateY(-3px); 
            box-shadow: 0 15px 30px -5px rgba(37, 99, 235, 0.6); 
            filter: brightness(1.1);
        }
        button.btn-login:active { 
            transform: translateY(0); 
            box-shadow: 0 5px 15px rgba(37, 99, 235, 0.4); 
        }

        /* --- RESPONSIVITAS --- */
        @media (max-width: 900px) {
            .login-wrapper { flex-direction: column; width: 100%; max-width: 480px; min-height: auto; border-radius: 24px;}
            .login-left { padding: 50px 30px 40px; flex: none; }
            .logo-box { padding: 15px; margin-bottom: 25px; }
            .login-left img { width: 80px; }
            .copyright { position: static; margin-top: 30px; }
            .login-right { padding: 40px 30px; }
            .header-form h1 { font-size: 28px; }
        }
    </style>
</head>
<body>

    <?php if(session()->getFlashdata('error')): ?>
        <script>
            Swal.fire({
                icon: 'error', 
                title: 'Akses Ditolak', 
                text: '<?= session()->getFlashdata('error') ?>', 
                confirmButtonColor: '#ef4444',
                background: '#ffffff',
                customClass: {
                    popup: 'swal2-custom-radius'
                }
            });
        </script>
        <style>.swal2-custom-radius { border-radius: 20px !important; font-family: 'Plus Jakarta Sans', sans-serif; }</style>
    <?php endif; ?>

    <div class="login-wrapper">
        <div class="login-left">
            <div class="logo-box">
                <img src="<?= base_url('uploads/logo/' . esc($company['logo_path'] ?? 'default-logo.png')); ?>" alt="Logo Instansi">
            </div>
            <h2 class="brand-title"><?= esc($company['app_name'] ?? 'ERP System') ?></h2>
            <h3 class="company-name"><?= esc($company['company_name'] ?? 'Sistem Manajemen Terpadu') ?></h3>
            <div class="copyright">
                <i class="ph-bold ph-copyright"></i> <script>document.write(new Date().getFullYear())</script> Hak Cipta Dilindungi.
            </div>
        </div>

        <div class="login-right">
            <div class="header-form">
                <h1>Otorisasi Masuk</h1>
                <p>Silakan masukkan kredensial Anda untuk mengakses <b>Portal Manajemen Area</b>.</p>
            </div>

            <form action="<?= base_url('login/process'); ?>" method="post" id="loginForm">
                <?= csrf_field() ?>
                
                <div class="input-group">
                    <input type="text" name="username" id="username" placeholder=" " required autofocus autocomplete="off" />
                    <label for="username">Username / ID Pengguna</label>
                </div>
                
                <div class="input-group">
                    <input type="password" name="password" id="password" placeholder=" " required autocomplete="off" />
                    <label for="password">Kata Sandi Rahasia</label>
                    <span class="toggle-password" onclick="togglePassword()" title="Tampilkan Sandi">
                        <i id="eye-icon" class="ph-bold ph-eye"></i>
                    </span>
                </div>

                <button type="submit" class="btn-login" onclick="this.innerHTML='<i class=\'ph-bold ph-spinner-gap ph-spin\' style=\'font-size:24px;\'></i> Memverifikasi...';">
                    Masuk ke Sistem <i class="ph-bold ph-arrow-right"></i>
                </button>
            </form>
        </div>
    </div>

    <script>
        // Animasi Tampil/Sembunyi Password dengan icon Phosphor
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eye-icon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.classList.remove('ph-eye');
                eyeIcon.classList.add('ph-eye-slash');
                eyeIcon.style.color = 'var(--brand-primary)';
            } else {
                passwordInput.type = 'password';
                eyeIcon.classList.remove('ph-eye-slash');
                eyeIcon.classList.add('ph-eye');
                eyeIcon.style.color = 'var(--text-muted)';
            }
        }
    </script>
</body>
</html>