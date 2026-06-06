<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/auth.php';
requireAdmin();

$active = 'peminjaman';

// Kembalikan barang
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['act'] ?? '') === 'kembali') {
    $id = (int)$_POST['id_pinjam'];
    $result = $conn->query("CALL kembalikan_barang($id)");
    if ($result) {
        setFlash('success', 'Pengembalian barang berhasil dicatat.');
    } else {
        setFlash('danger', 'Gagal memproses pengembalian.');
    }
    redirect('peminjaman.php');
}

// Filter
$filter = $_GET['filter'] ?? 'semua';
$where = '';
if ($filter === 'dipinjam') $where = "WHERE p.status = 'dipinjam'";
elseif ($filter === 'kembali') $where = "WHERE p.status = 'dikembalikan'";

$data = $conn->query("
    SELECT p.*, u.nama AS nama_user, b.nama_barang
    FROM peminjaman p
    JOIN user u ON p.id_user = u.id_user
    JOIN barang b ON p.id_barang = b.id_barang
    $where
    ORDER BY p.id_pinjam DESC
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Data Peminjaman — Inventaris RPL 1</title>
    <link rel="stylesheet" href="../includes/style.css">
</head>
<body>
<div class="wrapper">
    <?php include '../includes/sidebar.php'; ?>
    <div class="main">
        <div class="topbar">
            <div>
                <h2>📋 Data Peminjaman</h2>
                <div class="breadcrumb">Admin → Manajemen Peminjaman</div>
            </div>
        </div>
        <div class="content">
            <?php renderFlash(); ?>

            <!-- Filter -->
            <div style="display:flex;gap:.5rem;margin-bottom:1.5rem;flex-wrap:wrap">
                <a href="?filter=semua"     class="btn <?= $filter==='semua'?'btn-primary':'btn-ghost' ?> btn-sm">📋 Semua</a>
                <a href="?filter=dipinjam"  class="btn <?= $filter==='dipinjam'?'btn-primary':'btn-ghost' ?> btn-sm">🔄 Sedang Dipinjam</a>
                <a href="?filter=kembali"   class="btn <?= $filter==='kembali'?'btn-primary':'btn-ghost' ?> btn-sm">✅ Sudah Kembali</a>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3>Riwayat Peminjaman</h3>
                    <span class="badge badge-muted mono"><?= $data->num_rows ?> record</span>
                </div>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Peminjam</th>
                                <th>Barang</th>
                                <th>Jml</th>
                                <th>Tgl Pinjam</th>
                                <th>Tgl Kembali</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if ($data->num_rows === 0): ?>
                            <tr><td colspan="8">
                                <div class="empty-state">
                                    <div class="empty-icon">📭</div>
                                    <p>Tidak ada data peminjaman</p>
                                </div>
                            </td></tr>
                        <?php else: while ($row = $data->fetch_assoc()): ?>
                            <tr>
                                <td class="mono" style="color:var(--muted)"><?= $row['id_pinjam'] ?></td>
                                <td><strong><?= clean($row['nama_user']) ?></strong></td>
                                <td><?= clean($row['nama_barang']) ?></td>
                                <td><span class="badge badge-blue"><?= $row['jumlah_pinjam'] ?></span></td>
                                <td class="mono" style="font-size:.8rem"><?= $row['tanggal_pinjam'] ?></td>
                                <td class="mono" style="font-size:.8rem"><?= $row['tanggal_kembali'] ?? '<span style="color:var(--muted)">—</span>' ?></td>
                                <td>
                                    <?php if ($row['status'] === 'dipinjam'): ?>
                                        <span class="badge badge-amber">🔄 Dipinjam</span>
                                    <?php else: ?>
                                        <span class="badge badge-green">✅ Dikembalikan</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($row['status'] === 'dipinjam'): ?>
                                    <form method="POST" onsubmit="return confirm('Konfirmasi pengembalian barang?')">
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
