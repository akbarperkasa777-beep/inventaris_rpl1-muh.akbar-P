<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/auth.php';
requireLogin();

$active = 'riwayat';
$uid = currentUser()['id_user'];

// Proses pengembalian barang
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['act'] ?? '') === 'kembali') {
    $id = (int)$_POST['id_pinjam'];
    
    // Verifikasi bahwa pinjaman milik user saat ini
    $check = $conn->query("SELECT id_user FROM peminjaman WHERE id_pinjam = $id");
    if ($check && ($row = $check->fetch_assoc()) && $row['id_user'] == $uid) {
        $result = $conn->query("CALL kembalikan_barang($id)");
        if ($result) {
            setFlash('success', '✅ Barang berhasil dikembalikan.');
        } else {
            setFlash('danger', 'Gagal memproses pengembalian.');
        }
    } else {
        setFlash('danger', 'Pengembalian gagal: Data tidak ditemukan.');
    }
    redirect('riwayat.php');
}

$filter = $_GET['filter'] ?? 'semua';
$where = "WHERE p.id_user = $uid";
if ($filter === 'dipinjam') $where .= " AND p.status = 'dipinjam'";
elseif ($filter === 'kembali') $where .= " AND p.status = 'dikembalikan'";

$data = $conn->query("
    SELECT p.*, b.nama_barang, b.kondisi_barang
    FROM peminjaman p
    JOIN barang b ON p.id_barang = b.id_barang
    $where
    ORDER BY p.id_pinjam DESC
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Riwayat Peminjaman — Inventaris RPL 1</title>
    <link rel="stylesheet" href="../includes/style.css">
</head>
<body>
<div class="wrapper">
    <?php include '../includes/sidebar.php'; ?>
    <div class="main">
        <div class="topbar">
            <div><h2>🕒 Riwayat Peminjaman</h2><div class="breadcrumb">User → Riwayat Saya</div></div>
            <div class="topbar-right">
                <a href="pinjam.php" class="btn btn-primary">🛒 Pinjam Lagi</a>
            </div>
        </div>
        <div class="content">
            <?php renderFlash(); ?>

            <div style="display:flex;gap:.5rem;margin-bottom:1.5rem;flex-wrap:wrap">
                <a href="?filter=semua"    class="btn <?= $filter==='semua'?'btn-primary':'btn-ghost' ?> btn-sm">📋 Semua</a>
                <a href="?filter=dipinjam" class="btn <?= $filter==='dipinjam'?'btn-primary':'btn-ghost' ?> btn-sm">🔄 Aktif</a>
                <a href="?filter=kembali"  class="btn <?= $filter==='kembali'?'btn-primary':'btn-ghost' ?> btn-sm">✅ Selesai</a>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3>Riwayat Saya</h3>
                    <span class="badge badge-muted mono"><?= $data->num_rows ?> transaksi</span>
                </div>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr><th>#</th><th>Nama Barang</th><th>Jumlah</th><th>Tgl Pinjam</th><th>Tgl Kembali</th><th>Kondisi</th><th>Status</th><th>Aksi</th></tr>
                        </thead>
                        <tbody>
                        <?php if ($data->num_rows === 0): ?>
                            <tr><td colspan="8">
                                <div class="empty-state">
                                    <div class="empty-icon">📭</div>
                                    <p>Belum ada riwayat peminjaman.</p>
                                    <a href="pinjam.php" class="btn btn-primary" style="margin-top:1rem">Pinjam Barang Sekarang</a>
                                </div>
                            </td></tr>
                        <?php else: while ($row = $data->fetch_assoc()): ?>
                            <tr>
                                <td class="mono" style="color:var(--muted)"><?= $row['id_pinjam'] ?></td>
                                <td><strong><?= clean($row['nama_barang']) ?></strong></td>
                                <td><span class="badge badge-blue"><?= $row['jumlah_pinjam'] ?> unit</span></td>
                                <td class="mono" style="font-size:.8rem"><?= $row['tanggal_pinjam'] ?></td>
                                <td class="mono" style="font-size:.8rem"><?= $row['tanggal_kembali'] ?? '<span style="color:var(--muted)">Belum dikembalikan</span>' ?></td>
                                <td><?= $row['kondisi_barang'] === 'baik' ? '<span class="badge badge-green">👍 Baik</span>' : '<span class="badge badge-red">🔧 Rusak</span>' ?></td>
                                <td>
                                    <?php if ($row['status'] === 'dipinjam'): ?>
                                        <span class="badge badge-amber">🔄 Dipinjam</span>
                                    <?php else: ?>
                                        <span class="badge badge-green">✅ Dikembalikan</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($row['status'] === 'dipinjam'): ?>
                                    <form method="POST" style="display:inline" onsubmit="return confirm('Konfirmasi pengembalian barang?')">
                                        <input type="hidden" name="act" value="kembali">
                                        <input type="hidden" name="id_pinjam" value="<?= $row['id_pinjam'] ?>">
                                        <button type="submit" class="btn btn-success btn-sm">↩️ Kembalikan</button>
                                    </form>
                                    <?php else: ?>
                                        <span style="color:var(--muted);font-size:.8rem">—</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
