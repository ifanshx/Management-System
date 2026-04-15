<?php
/**
 * Logout System
 * Menangani proses penghapusan sesi pengguna secara aman.
 */

// 1. Mulai atau lanjutkan sesi jika belum aktif
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Hapus semua variabel sesi di server
$_SESSION = [];

// 3. Hapus Cookie Sesi di Browser (Client-side cleanup)
// Ini langkah krusial untuk keamanan agar ID sesi lama tidak bisa dipakai ulang (Session Fixation)
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(), 
        '', 
        time() - 42000, 
        $params["path"], 
        $params["domain"], 
        $params["secure"], 
        $params["httponly"]
    );
}

// 4. Hancurkan data sesi di penyimpanan server
session_destroy();

// 5. Mencegah Caching Browser (Security Best Practice)
// Memastikan user tidak bisa menekan tombol "Back" dan melihat halaman sebelumnya
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// 6. Redirect ke halaman Login
header("Location: login.php?msg=logout");
exit();
?>