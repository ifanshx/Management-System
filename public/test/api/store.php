<?php
// ==============================================
// FILE: store.php
// DESKRIPSI: API endpoint untuk menerima data dari FingerSpot (Optimized)
// VERSION: 2.1 (Final Fix)
// ==============================================

// =================== CONFIGURATION ===================
date_default_timezone_set('Asia/Jakarta');
header("Content-Type: text/plain");

// Matikan error reporting di production agar tidak mengganggu output "OK"
// Jika ingin debugging, ubah ke 1
ini_set('display_errors', 0); 
error_reporting(E_ALL);

// =================== FUNCTIONS ===================
function debug_log($message, $type = 'INFO') {
    $logFile = 'fingerspot_log_' . date('Y-m-d') . '.txt';
    $timestamp = date('Y-m-d H:i:s');
    $formattedMsg = "[$timestamp][$type] $message" . PHP_EOL;
    file_put_contents($logFile, $formattedMsg, FILE_APPEND);
}

function log_raw_request($method, $body) {
    $logFile = 'fingerspot_raw_' . date('Y-m-d') . '.txt';
    $timestamp = date('Y-m-d H:i:s');
    $formattedMsg = "[" . $timestamp . "][Method: " . $method . "]" . PHP_EOL;
    $formattedMsg .= "Body: " . $body . PHP_EOL;
    $formattedMsg .= str_repeat("-", 80) . PHP_EOL;
    file_put_contents($logFile, $formattedMsg, FILE_APPEND);
}

// =================== MAIN PROCESS ===================
try {
    debug_log("=== API CALLED ===");
    
    $method = $_SERVER['REQUEST_METHOD'];
    $body = file_get_contents('php://input');
    
    log_raw_request($method, $body);
    
    if (empty($body)) {
        debug_log("WARNING: Empty request body", "WARNING");
        die("OK");
    }
    
    $json_data = json_decode($body, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        debug_log("JSON Error: " . json_last_error_msg(), "ERROR");
        die("OK");
    }
    
    require_once '../config/database.php';
    if (!$conn) {
        debug_log("Database connection failed", "ERROR");
        die("OK");
    }

    $type = $json_data['type'] ?? 'unknown';
    $cloud_id = $json_data['cloud_id'] ?? 'unknown';
    $created_at = date('Y-m-d H:i:s');
    $encoded_data = json_encode($json_data);

    // 1. Simpan ke t_log (Prepared Statement)
    $stmt_log = mysqli_prepare($conn, "INSERT INTO t_log (cloud_id, type, created_at, original_data) VALUES (?, ?, ?, ?)");
    if ($stmt_log) {
        mysqli_stmt_bind_param($stmt_log, "ssss", $cloud_id, $type, $created_at, $encoded_data);
        mysqli_stmt_execute($stmt_log);
        mysqli_stmt_close($stmt_log);
    }

    // 2. Proses Absensi jika tipe adalah 'attlog'
    if ($type === 'attlog' && isset($json_data['data'])) {
        $data = $json_data['data'];
        $pin = isset($data['pin']) ? trim($data['pin']) : '';
        $scan = isset($data['scan']) ? $data['scan'] : '';
        $verify = (int)($data['verify'] ?? 0);
        $status_scan = (int)($data['status_scan'] ?? 0);
        $photo_url = (!empty($data['photo_url']) && $data['photo_url'] !== '-') ? trim($data['photo_url']) : null;
        
        if (!empty($pin) && !empty($scan)) {
            $scan_date = date('Y-m-d H:i:s', strtotime($scan));

            // Cek User
            $stmt_user = mysqli_prepare($conn, "SELECT id FROM users WHERE pin = ? LIMIT 1");
            mysqli_stmt_bind_param($stmt_user, "s", $pin);
            mysqli_stmt_execute($stmt_user);
            $res_user = mysqli_stmt_get_result($stmt_user);

            if ($row_user = mysqli_fetch_assoc($res_user)) {
                // Cek Duplikat
                $stmt_dup = mysqli_prepare($conn, "SELECT id FROM absensi WHERE pin = ? AND scan_date = ?");
                mysqli_stmt_bind_param($stmt_dup, "ss", $pin, $scan_date);
                mysqli_stmt_execute($stmt_dup);
                mysqli_stmt_store_result($stmt_dup);

                if (mysqli_stmt_num_rows($stmt_dup) == 0) {
                    // Cek ketersediaan kolom photo_url secara dinamis (Cached-like)
                    $res_col = mysqli_query($conn, "SHOW COLUMNS FROM absensi LIKE 'photo_url'");
                    $has_photo_col = (mysqli_num_rows($res_col) > 0);

                    if ($has_photo_col) {
                        $stmt_absensi = mysqli_prepare($conn, "INSERT INTO absensi (pin, scan_date, status_scan, verify_mode, photo_url) VALUES (?, ?, ?, ?, ?)");
                        mysqli_stmt_bind_param($stmt_absensi, "ssiis", $pin, $scan_date, $status_scan, $verify, $photo_url);
                    } else {
                        $stmt_absensi = mysqli_prepare($conn, "INSERT INTO absensi (pin, scan_date, status_scan, verify_mode) VALUES (?, ?, ?, ?)");
                        mysqli_stmt_bind_param($stmt_absensi, "ssii", $pin, $scan_date, $status_scan, $verify);
                    }

                    if (mysqli_stmt_execute($stmt_absensi)) {
                        debug_log("SUCCESS: Attendance saved for PIN: $pin", "SUCCESS");
                    }
                    mysqli_stmt_close($stmt_absensi);
                } else {
                    debug_log("Duplicate ignored for PIN: $pin", "WARNING");
                }
                mysqli_stmt_close($stmt_dup);
            } else {
                // Simpan PIN tidak dikenal
                debug_log("User not found: $pin", "WARNING");
                $stmt_unk = mysqli_prepare($conn, "INSERT INTO unknown_pins (pin, scan_date, cloud_id, created_at) VALUES (?, ?, ?, NOW())");
                mysqli_stmt_bind_param($stmt_unk, "sss", $pin, $scan_date, $cloud_id);
                mysqli_stmt_execute($stmt_unk);
                mysqli_stmt_close($stmt_unk);
            }
            mysqli_stmt_close($stmt_user);
        }
    }

    mysqli_close($conn);

} catch (Exception $e) {
    debug_log("EXCEPTION: " . $e->getMessage(), "ERROR");
}

// WAJIB: Selalu beri respon OK agar mesin tidak kirim ulang (Retrying)
echo "OK";
debug_log("=== API PROCESS COMPLETED ===" . PHP_EOL);
?>