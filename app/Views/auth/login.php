<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Noric Management System</title>
    <link rel="shortcut icon" type="image/png" href="<?= base_url('favicon.ico'); ?>">

    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        :root {
            --primary-dark: #111827;
            --primary-accent: #ef4444; /* Merah khas racing */
            --accent-hover: #dc2626;
            --text-gray: #6b7280;
            --bg-light: #f3f4f6;
            --border-color: #e5e7eb;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Montserrat', sans-serif;
        }

        body {
            /* Latar belakang dengan pola dot halus agar tidak terlalu polos */
            background-color: var(--bg-light);
            background-image: radial-gradient(#d1d5db 1px, transparent 1px);
            background-size: 20px 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }

        .login-wrapper {
            display: flex;
            background: #ffffff;
            border-radius: 16px;
            /* Shadow bertingkat yang elegan */
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 20px 40px -5px rgba(0, 0, 0, 0.15);
            overflow: hidden;
            width: 1000px;
            max-width: 100%;
            min-height: 550px;
        }

        /* --- Panel Kiri (Branding) --- */
        .login-left {
            flex: 1.2;
            background: linear-gradient(145deg, var(--primary-dark) 0%, #1f2937 100%);
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

        /* Aksen dekoratif di panel kiri */
        .login-left::before {
            content: '';
            position: absolute;
            top: -50px;
            left: -50px;
            width: 200px;
            height: 200px;
            background: rgba(255, 255, 255, 0.03);
            border-radius: 50%;
        }

        .login-left::after {
            content: '';
            position: absolute;
            bottom: -80px;
            right: -50px;
            width: 300px;
            height: 300px;
            background: rgba(239, 68, 68, 0.05); /* Bias merah halus */
            border-radius: 50%;
        }

        .login-left img {
            width: 130px;
            margin-bottom: 30px;
            filter: drop-shadow(0px 8px 12px rgba(0,0,0,0.5));
            z-index: 1;
        }

        .brand-title {
            letter-spacing: 4px;
            font-size: 36px;
            font-weight: 800;
            margin-bottom: 5px;
            background-image: linear-gradient(to right, #ffffff, #d1d5db);
            color: transparent;
            -webkit-background-clip: text;
            background-clip: text;
            z-index: 1;
        }

        .company-name {
            font-size: 15px;
            font-weight: 500;
            letter-spacing: 2.5px;
            color: #9ca3af;
            text-transform: uppercase;
            z-index: 1;
        }

        .copyright {
            position: absolute;
            bottom: 25px;
            font-size: 12px;
            color: #6b7280;
            z-index: 1;
            font-weight: 500;
        }

        /* --- Panel Kanan (Form) --- */
        .login-right {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 60px 70px;
            background: #ffffff;
        }

        .header-form {
            margin-bottom: 35px;
        }

        .header-form h1 {
            color: var(--primary-dark);
            font-weight: 800;
            font-size: 32px;
            margin-bottom: 8px;
        }

        .header-form p {
            color: var(--text-gray);
            font-size: 15px;
            font-weight: 400;
        }

        /* --- Floating Input Style --- */
        .input-group {
            position: relative;
            margin-bottom: 25px;
        }

        .input-group input {
            width: 100%;
            background-color: transparent;
            border: 2px solid var(--border-color);
            padding: 18px 15px 10px;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 500;
            color: var(--primary-dark);
            transition: all 0.3s ease;
        }

        .input-group input:focus {
            outline: none;
            border-color: var(--primary-dark);
            background-color: #f9fafb;
        }

        /* Label yang melayang (Floating Label) */
        .input-group label {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
            font-size: 15px;
            font-weight: 500;
            pointer-events: none;
            transition: all 0.2s ease-out;
            background-color: #ffffff; /* Nutupin border belakangnya */
            padding: 0 5px;
        }

        /* Animasi label naik saat fokus atau ada isinya */
        .input-group input:focus ~ label,
        .input-group input:not(:placeholder-shown) ~ label {
            top: 0;
            font-size: 12px;
            color: var(--primary-dark);
            font-weight: 600;
        }

        /* --- Tombol Mata Password --- */
        .toggle-password {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #9ca3af;
            transition: color 0.3s;
            display: flex;
            align-items: center;
        }
        
        .toggle-password:hover {
            color: var(--primary-dark);
        }

        .toggle-password svg {
            width: 20px;
            height: 20px;
        }

        /* --- Tombol Login --- */
        button.btn-login {
            width: 100%;
            border-radius: 10px;
            border: none;
            background-color: var(--primary-accent);
            color: #FFFFFF;
            font-size: 15px;
            font-weight: 700;
            padding: 16px;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            margin-top: 10px;
            box-shadow: 0 4px 14px rgba(239, 68, 68, 0.3);
        }

        button.btn-login:hover {
            background-color: var(--accent-hover);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(239, 68, 68, 0.4);
        }

        button.btn-login:active {
            transform: translateY(0);
            box-shadow: 0 2px 10px rgba(239, 68, 68, 0.3);
        }

        /* --- Responsivitas Mobile (Elegan) --- */
        @media (max-width: 850px) {
            .login-wrapper {
                flex-direction: column;
                width: 100%;
                max-width: 450px;
                min-height: auto;
            }
            .login-left {
                padding: 40px 20px 30px;
                flex: none; /* Jangan penuhi layar */
            }
            .login-left img {
                width: 90px;
                margin-bottom: 15px;
            }
            .brand-title {
                font-size: 26px;
            }
            .company-name {
                font-size: 13px;
            }
            .copyright {
                position: static;
                margin-top: 20px;
            }
            .login-right {
                padding: 40px 35px;
            }
            .header-form h1 {
                font-size: 28px;
            }
        }

        @media (max-width: 480px) {
            .login-right {
                padding: 30px 25px;
            }
        }
    </style>
</head>
<body>

    <?php if(session()->getFlashdata('error')): ?>
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Autentikasi Gagal',
                text: '<?= session()->getFlashdata('error') ?>',
                confirmButtonColor: '#ef4444',
                customClass: {
                    popup: 'swal-custom-popup'
                }
            });
        </script>
    <?php endif; ?>

    <div class="login-wrapper">
        <div class="login-left">
            <img src="<?= base_url('assets/img/logo/logo-noric.png'); ?>" alt="Noric Logo">
            <h2 class="brand-title">NORIC</h2>
            <h3 class="company-name">Management System</h3>
            <div class="copyright">
                &copy; <script>document.write(new Date().getFullYear())</script> Noric Racing Exhaust.
            </div>
        </div>

        <div class="login-right">
            <div class="header-form">
                <h1>Masuk</h1>
                <p>Silakan otorisasi akses Anda ke sistem.</p>
            </div>

            <form action="<?= base_url('login/process'); ?>" method="post">
                <?= csrf_field() ?>
                <div class="input-group">
                    <input type="text" name="username" id="username" placeholder=" " required autofocus autocomplete="off" />
                    <label for="username">Username</label>
                </div>
                
                <div class="input-group">
                    <input type="password" name="password" id="password" placeholder=" " required autocomplete="off" />
                    <label for="password">Password</label>
                    
                    <span class="toggle-password" onclick="togglePassword()">
                        <svg id="eye-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                    </span>
                </div>

                <button type="submit" class="btn-login">Masuk Dashboard</button>
            </form>
        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eye-icon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                // Ganti icon ke eye-off (mata disilang)
                eyeIcon.innerHTML = `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />`;
            } else {
                passwordInput.type = 'password';
                // Ganti kembali ke icon eye biasa
                eyeIcon.innerHTML = `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />`;
            }
        }
    </script>
</body>
</html>