<?php
session_start();
require_once 'includes/config.php';

// Redirect if already logged in
if (isset($_SESSION['user'])) {
    $role = $_SESSION['user']['role'];
    redirect($role === 'admin' ? 'admin/dashboard.php' : 'user/dashboard.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = clean($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username && $password) {
        $stmt = $conn->prepare("SELECT * FROM user WHERE username = ? AND password = MD5(?)");
        $stmt->bind_param("ss", $username, $password);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            $_SESSION['user'] = $row;
            redirect($row['role'] === 'admin' ? 'admin/dashboard.php' : 'user/dashboard.php');
        } else {
            $error = 'Username atau password salah.';
        }
    } else {
        $error = 'Mohon isi semua kolom.';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login — Inventaris RPL 1</title>
    <link rel="stylesheet" href="includes/style.css">
</head>
<body class="login-page">
    <div class="login-box">
        <div class="login-header">
            <div class="logo-big">📦</div>
            <h1>INVENTARIS RPL 1</h1>
            <p>Sistem Pengelolaan Barang<br>
               <span style="font-family:var(--font-mono);font-size:.7rem;color:var(--muted)">
               Asesmen Sumatif Genap 2025/2026
               </span>
            </p>
        </div>

        <div class="login-body">
            

            <?php if ($error): ?>
            <div class="alert alert-danger">❌ <?= clean($error) ?></div>
            <?php endif; ?>

            <form method="POST" autocomplete="off">
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" class="form-control"
                           placeholder="Masukkan username"
                           value="<?= clean($_POST['username'] ?? '') ?>"
                           autofocus required>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" class="form-control"
                           placeholder="Masukkan password" required>
                </div>
                <button type="submit" class="btn btn-primary btn-lg" style="width:100%;justify-content:center;margin-top:.5rem">
                    🔐 Masuk
                </button>
            </form>
        </div>

        <div class="login-footer">
            XI RPL 1 &nbsp;·&nbsp; Muh Akbar Perkasa &nbsp;·&nbsp; 2025–2026
        </div>
    </div>
</body>
</html>
