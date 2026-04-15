<?php
// Pastikan session dimulai
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Gunakan path absolut atau pengecekan file agar tidak error fatal
$db_path = 'config/database.php';
if (file_exists($db_path)) {
    require_once $db_path;
} else {
    die("System Error: Konfigurasi database tidak ditemukan.");
}

// Logika Routing
if (isset($_SESSION['status']) && $_SESSION['status'] === "login") {
    // User sudah login -> Arahkan ke Dashboard
    header("Location: pages/dashboard.php");
    exit(); // Penting: Hentikan eksekusi script setelah redirect
} else {
    // User belum login -> Arahkan ke Login
    header("Location: login.php");
    exit(); // Penting
}
?>