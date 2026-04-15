<?php
session_start();
require_once 'config/database.php';

// Redirect jika sudah login
if (isset($_SESSION['status']) && $_SESSION['status'] == "login") {
    header("Location: pages/dashboard.php");
    exit;
}

$error = "";

if (isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // Gunakan prepared statement untuk keamanan
    $stmt = mysqli_prepare($conn, "SELECT id, pin, username, password, fullname, role, status_karyawan FROM users WHERE username = ?");
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);

    if ($user && password_verify($password, $user['password'])) {
        session_regenerate_id(true);
        
        $_SESSION['user_id']  = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['fullname'] = $user['fullname'];
        $_SESSION['role']     = $user['role'];
        $_SESSION['status']   = "login";

        header("Location: pages/dashboard.php");
        exit;
    } else {
        $error = "Username atau Password salah!";
    }
    mysqli_stmt_close($stmt);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login System | NORIC</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <link rel="shortcut icon" href="assets/image/favicon.ico" type="image/x-icon">

    <style>
        :root {
            --primary: #3b82f6;
            --primary-dark: #2563eb;
            --bg-gradient: radial-gradient(at top center, #1e293b, #0f172a);
            --card-bg: #ffffff;
            --text-main: #1e293b;
            --text-muted: #64748b;
            --input-bg: #f8fafc;
            --border-color: #e2e8f0;
        }

        body {
            background: var(--bg-gradient);
            font-family: 'Plus Jakarta Sans', sans-serif;
            height: 100vh;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        /* Container Animasi */
        .login-wrapper {
            width: 100%;
            max-width: 420px;
            padding: 20px;
            animation: slideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .login-card {
            background: var(--card-bg);
            border-radius: 24px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            padding: 40px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        /* Aksen Dekorasi */
        .login-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; width: 100%; height: 6px;
            background: linear-gradient(90deg, #3b82f6, #06b6d4);
        }

        .logo-area {
            margin-bottom: 30px;
        }
        .logo-img {
            height: 50px;
            width: auto;
            margin-bottom: 15px;
            transition: transform 0.3s;
        }
        .logo-img:hover {
            transform: scale(1.05);
        }

        .welcome-text h3 {
            font-size: 22px;
            font-weight: 800;
            color: var(--text-main);
            margin: 0 0 8px 0;
            letter-spacing: -0.5px;
        }
        .welcome-text p {
            font-size: 14px;
            color: var(--text-muted);
            margin: 0;
        }

        /* Form Styling */
        .form-content {
            margin-top: 30px;
            text-align: left;
        }

        .input-group {
            position: relative;
            margin-bottom: 20px;
        }

        .input-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 18px;
            transition: color 0.3s;
        }

        .form-control {
            width: 100%;
            height: 52px;
            padding: 0 15px 0 50px; /* Padding kiri untuk icon */
            font-size: 15px;
            font-weight: 500;
            color: var(--text-main);
            background: var(--input-bg);
            border: 2px solid transparent;
            border-radius: 12px;
            transition: all 0.3s ease;
            box-sizing: border-box; /* Penting agar padding tidak merusak width */
        }

        .form-control:focus {
            outline: none;
            background: #fff;
            border-color: var(--primary);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.1);
        }

        /* Ubah warna icon saat input fokus */
        .form-control:focus + .input-icon, 
        .input-group:focus-within .input-icon {
            color: var(--primary);
        }

        .btn-login {
            width: 100%;
            height: 52px;
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
            font-size: 16px;
            font-weight: 700;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
            margin-top: 10px;
            letter-spacing: 0.5px;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px -5px rgba(37, 99, 235, 0.4);
        }
        
        .btn-login:active {
            transform: translateY(0);
        }

        /* Alert Styling */
        .alert-error {
            background-color: #fee2e2;
            color: #991b1b;
            padding: 12px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: shake 0.4s ease-in-out;
        }

        /* Footer */
        .footer-text {
            margin-top: 25px;
            font-size: 12px;
            color: #94a3b8;
        }

        /* Animation Keyframes */
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }

        /* Responsif untuk layar kecil */
        @media (max-width: 480px) {
            .login-card {
                padding: 30px 20px;
            }
            .logo-img {
                height: 40px;
            }
        }
    </style>
</head>
<body>

    <div class="login-wrapper">
        <div class="login-card">
            
            <div class="logo-area">
                <img src="assets/image/logo-noric.png" alt="NORIC SYSTEM" class="logo-img">
                <div class="welcome-text">
                    <h3>Selamat Datang</h3>
                    <p>Silakan masuk untuk melanjutkan akses.</p>
                </div>
            </div>

            <?php if($error): ?>
                <div class="alert-error">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <span><?php echo $error; ?></span>
                </div>
            <?php endif; ?>

            <form method="POST" class="form-content">
                <div class="input-group">
                    <input type="text" name="username" class="form-control" placeholder="Username" required autocomplete="off">
                    <i class="fa-regular fa-user input-icon"></i>
                </div>

                <div class="input-group">
                    <input type="password" name="password" class="form-control" placeholder="Password" required>
                    <i class="fa-solid fa-lock input-icon"></i>
                </div>

                <button type="submit" name="login" class="btn-login">
                    MASUK <i class="fa-solid fa-arrow-right-to-bracket" style="margin-left:8px;"></i>
                </button>
            </form>

            <div class="footer-text">
                &copy; <?php echo date('Y'); ?> <strong>Noric Racing Exhaust</strong>. <br>All rights reserved.
            </div>
        </div>
    </div>

</body>
</html>