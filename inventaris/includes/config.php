<?php
// ============================================================
// KONFIGURASI DATABASE
// inventaris_rpl1 | XI RPL 1
// ============================================================

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'inventaris_rpl1');

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    die('<div style="font-family:sans-serif;padding:2rem;color:#e74c3c;">
        <h2>⚠️ Koneksi Database Gagal</h2>
        <p>' . $conn->connect_error . '</p>
        <p>Pastikan XAMPP/Laragon aktif dan database <b>inventaris_rpl1</b> sudah dibuat.</p>
    </div>');
}

$conn->set_charset("utf8mb4");

// Helper: redirect
function redirect($url) {
    header("Location: $url");
    exit();
}

// Helper: flash message
function setFlash($type, $msg) {
    $_SESSION['flash'] = ['type' => $type, 'msg' => $msg];
}

function getFlash() {
    if (isset($_SESSION['flash'])) {
        $f = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $f;
    }
    return null;
}

// Helper: sanitize
function clean($str) {
    return htmlspecialchars(strip_tags(trim($str)));
}
?>
