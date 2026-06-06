<?php
session_start();
if (isset($_SESSION['user'])) {
    $role = $_SESSION['user']['role'];
    header("Location: " . ($role === 'admin' ? 'admin/dashboard.php' : 'user/dashboard.php'));
} else {
    header("Location: login.php");
}
exit();
?>
