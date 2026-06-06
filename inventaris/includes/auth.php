<?php
function requireLogin() {
    if (!isset($_SESSION['user'])) {
        redirect('../login.php');
    }
}

function requireAdmin() {
    requireLogin();
    if ($_SESSION['user']['role'] !== 'admin') {
        redirect('../user/dashboard.php');
    }
}

function isAdmin() {
    return isset($_SESSION['user']) && $_SESSION['user']['role'] === 'admin';
}

function currentUser() {
    return $_SESSION['user'] ?? null;
}
?>
