<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/auth.php';
requireAdmin();

$active = 'pengguna';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $act = $_POST['act'] ?? '';

    if ($act === 'tambah') {
        $nama     = clean($_POST['nama']);
        $username = clean($_POST['username']);
        $password = $_POST['password'];
        $role     = in_array($_POST['role'], ['admin','user']) ? $_POST['role'] : 'user';

        $check = $conn->prepare("SELECT id_user FROM user WHERE username=?");
        $check->bind_param("s", $username);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            setFlash('danger', 'Username sudah digunakan.');
        } else {
            $stmt = $conn->prepare("INSERT INTO user (nama, username, password, role) VALUES (?,?,MD5(?),?)");
            $stmt->bind_param("ssss", $nama, $username, $password, $role);
            if ($stmt->execute()) setFlash('success', "Pengguna \"$nama\" berhasil ditambahkan.");
            else setFlash('danger', 'Gagal menambahkan pengguna.');
        }
        redirect('pengguna.php');
    }

    if ($act === 'hapus') {
        $id = (int)$_POST['id_user'];
        if ($id == currentUser()['id_user']) {
            setFlash('danger', 'Tidak dapat menghapus akun yang sedang aktif.');
        } else {
            $stmt = $conn->prepare("DELETE FROM user WHERE id_user=?");
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) setFlash('success', 'Pengguna berhasil dihapus.');
            else setFlash('danger', 'Gagal menghapus. Ada data peminjaman terkait.');
        }
        redirect('pengguna.php');
    }
}

$users = $conn->query("SELECT u.*, (SELECT COUNT(*) FROM peminjaman WHERE id_user=u.id_user AND status='dipinjam') AS aktif_pinjam FROM user u ORDER BY u.role, u.id_user");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pengguna — Inventaris RPL 1</title>
    <link rel="stylesheet" href="../includes/style.css">
    <style>
        .modal-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.6);z-index:200;place-items:center}
        .modal-overlay.show{display:grid}
        .modal-box{background:var(--card);border:1px solid var(--border);border-radius:14px;width:460px;max-width:95vw;overflow:hidden;animation:loginSlideUp .3s ease}
        .modal-header{padding:1.2rem 1.5rem;border-bottom:1px solid var(--border);display:flex;justify-content:space-between;align-items:center}
        .modal-header h3{font-family:var(--font-head);font-weight:700}
        .modal-close{background:none;border:none;color:var(--muted);cursor:pointer;font-size:1.2rem}
        .modal-body{padding:1.5rem}
        .modal-footer{padding:1rem 1.5rem;border-top:1px solid var(--border);display:flex;justify-content:flex-end;gap:.75rem}
    </style>
</head>
<body>
<div class="wrapper">
    <?php include '../includes/sidebar.php'; ?>
    <div class="main">
        <div class="topbar">
            <div><h2>👥 Pengguna</h2><div class="breadcrumb">Admin → Manajemen Pengguna</div></div>
            <div class="topbar-right">
                <button class="btn btn-primary" onclick="document.getElementById('modalTambah').classList.add('show')">➕ Tambah Pengguna</button>
            </div>
        </div>
        <div class="content">
            <?php renderFlash(); ?>
            <div class="card">
                <div class="card-header">
                    <h3>Daftar Pengguna</h3>
                    <span class="badge badge-muted mono"><?= $users->num_rows ?> akun</span>
                </div>
                <div class="table-wrap">
                    <table>
                        <thead><tr><th>#</th><th>Nama</th><th>Username</th><th>Role</th><th>Pinjam Aktif</th><th>Aksi</th></tr></thead>
                        <tbody>
                        <?php while ($row = $users->fetch_assoc()):
                            $isMe = $row['id_user'] == currentUser()['id_user'];
                        ?>
                            <tr>
                                <td class="mono" style="color:var(--muted)"><?= $row['id_user'] ?></td>
                                <td><strong><?= clean($row['nama']) ?></strong> <?= $isMe ? '<span class="badge badge-amber" style="font-size:.6rem">Kamu</span>' : '' ?></td>
                                <td class="mono" style="font-size:.85rem"><?= clean($row['username']) ?></td>
                                <td><?= $row['role'] === 'admin' ? '<span class="badge badge-amber">⭐ Admin</span>' : '<span class="badge badge-blue">👤 User</span>' ?></td>
                                <td><?= $row['aktif_pinjam'] > 0 ? "<span class=\"badge badge-amber\">{$row['aktif_pinjam']} aktif</span>" : '<span style="color:var(--muted)">—</span>' ?></td>
                                <td>
                                    <?php if (!$isMe): ?>
                                    <form method="POST" onsubmit="return confirm('Hapus pengguna ini?')">
                                        <input type="hidden" name="act" value="hapus">
                                        <input type="hidden" name="id_user" value="<?= $row['id_user'] ?>">
                                        <button type="submit" class="btn btn-danger btn-sm">🗑️ Hapus</button>
                                    </form>
                                    <?php else: ?><span style="color:var(--muted);font-size:.8rem">—</span><?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal-overlay" id="modalTambah" onclick="if(event.target===this)this.classList.remove('show')">
    <div class="modal-box">
        <div class="modal-header">
            <h3>➕ Tambah Pengguna</h3>
            <button class="modal-close" onclick="document.getElementById('modalTambah').classList.remove('show')">✕</button>
        </div>
        <form method="POST">
            <input type="hidden" name="act" value="tambah">
            <div class="modal-body">
                <div class="form-group"><label>Nama Lengkap</label><input type="text" name="nama" class="form-control" required placeholder="Nama pengguna"></div>
                <div class="form-row">
                    <div class="form-group"><label>Username</label><input type="text" name="username" class="form-control" required placeholder="username unik"></div>
                    <div class="form-group"><label>Password</label><input type="password" name="password" class="form-control" required placeholder="••••••"></div>
                </div>
                <div class="form-group">
                    <label>Role</label>
                    <select name="role" class="form-control">
                        <option value="user">👤 User</option>
                        <option value="admin">⭐ Admin</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-ghost" onclick="document.getElementById('modalTambah').classList.remove('show')">Batal</button>
                <button type="submit" class="btn btn-primary">Tambah Pengguna</button>
            </div>
        </form>
    </div>
</div>
</body>
</html>
