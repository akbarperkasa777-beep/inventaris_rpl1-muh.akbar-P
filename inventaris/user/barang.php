<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/auth.php';
requireLogin();

$active = 'barang';
$cari = clean($_GET['cari'] ?? '');
$sql = "SELECT *, status_barang(jumlah) AS status_stok FROM barang";
if ($cari) $sql .= " WHERE nama_barang LIKE '%$cari%'";
$sql .= " ORDER BY jumlah DESC";
$barangList = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Daftar Barang — Inventaris RPL 1</title>
    <link rel="stylesheet" href="../includes/style.css">
</head>
<body>
<div class="wrapper">
    <?php include '../includes/sidebar.php'; ?>
    <div class="main">
        <div class="topbar">
            <div><h2>📦 Daftar Barang</h2><div class="breadcrumb">User → Barang Tersedia</div></div>
            <div class="topbar-right">
                <a href="pinjam.php" class="btn btn-primary">🛒 Pinjam Barang</a>
            </div>
        </div>
        <div class="content">
            <form method="GET" style="margin-bottom:1.5rem;display:flex;gap:.75rem">
                <input type="text" name="cari" class="form-control" style="max-width:320px"
                       placeholder="🔍 Cari barang..." value="<?= clean($_GET['cari'] ?? '') ?>">
                <button type="submit" class="btn btn-ghost">Cari</button>
                <?php if ($cari): ?><a href="barang.php" class="btn btn-ghost">× Reset</a><?php endif; ?>
            </form>

            <div class="card">
                <div class="card-header">
                    <h3>Inventaris Barang Sekolah</h3>
                    <span class="badge badge-muted mono"><?= $barangList->num_rows ?> barang</span>
                </div>
                <div class="table-wrap">
                    <table>
                        <thead><tr><th>#</th><th>Nama Barang</th><th>Stok Tersedia</th><th>Status</th><th>Kondisi</th><th>Aksi</th></tr></thead>
                        <tbody>
                        <?php if ($barangList->num_rows === 0): ?>
                            <tr><td colspan="6"><div class="empty-state"><div class="empty-icon">🔍</div><p>Tidak ada barang ditemukan</p></div></td></tr>
                        <?php else: while ($row = $barangList->fetch_assoc()):
                            $tersedia = $row['jumlah'] > 0 && $row['kondisi_barang'] === 'baik';
                            $pct = min(100, ($row['jumlah'] / 20) * 100);
                            $barClass = $row['jumlah'] === 0 ? 'empty' : ($row['jumlah'] <= 5 ? 'low' : '');
                        ?>
                            <tr>
                                <td class="mono" style="color:var(--muted)"><?= $row['id_barang'] ?></td>
                                <td><strong><?= clean($row['nama_barang']) ?></strong></td>
                                <td>
                                    <div class="stok-bar">
                                        <strong><?= $row['jumlah'] ?></strong>
                                        <div class="bar"><div class="bar-fill <?= $barClass ?>" style="width:<?= $pct ?>%"></div></div>
                                    </div>
                                </td>
                                <td>
                                    <?php if ($row['status_stok'] === 'Habis'): ?>
                                        <span class="badge badge-red">⛔ Habis</span>
                                    <?php elseif ($row['status_stok'] === 'Hampir Habis'): ?>
                                        <span class="badge badge-amber">⚠️ Hampir Habis</span>
                                    <?php else: ?>
                                        <span class="badge badge-green">✅ Tersedia</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= $row['kondisi_barang'] === 'baik' ? '<span class="badge badge-green">👍 Baik</span>' : '<span class="badge badge-red">🔧 Rusak</span>' ?></td>
                                <td>
                                    <?php if ($tersedia): ?>
                                        <a href="pinjam.php?id=<?= $row['id_barang'] ?>" class="btn btn-primary btn-sm">🛒 Pinjam</a>
                                    <?php else: ?>
                                        <span class="badge badge-muted">Tidak Tersedia</span>
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
