<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/auth.php';
requireLogin();

$active = 'dashboard';
$uid = currentUser()['id_user'];

$totalPinjamku  = $conn->query("SELECT COUNT(*) FROM peminjaman WHERE id_user=$uid AND status='dipinjam'")->fetch_row()[0];
$totalRiwayat   = $conn->query("SELECT COUNT(*) FROM peminjaman WHERE id_user=$uid")->fetch_row()[0];
$totalBarang    = $conn->query("SELECT COUNT(*) FROM barang WHERE jumlah > 0")->fetch_row()[0];

$pinjamAktif = $conn->query("
    SELECT p.*, b.nama_barang, b.kondisi_barang
    FROM peminjaman p
    JOIN barang b ON p.id_barang = b.id_barang
    WHERE p.id_user = $uid AND p.status = 'dipinjam'
    ORDER BY p.id_pinjam DESC
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard — Inventaris RPL 1</title>
    <link rel="stylesheet" href="../includes/style.css">
</head>
<body>
<div class="wrapper">
    <?php include '../includes/sidebar.php'; ?>
    <div class="main">
        <div class="topbar">
            <div>
                <h2>Halo, <?= clean(currentUser()['nama']) ?> 👋</h2>
                <div class="breadcrumb">Dashboard Pengguna</div>
            </div>
            <div class="topbar-right">
                <span class="badge badge-blue">👤 User</span>
                <span class="mono" style="font-size:.7rem;color:var(--muted)"><?= date('d M Y') ?></span>
            </div>
        </div>
        <div class="content">
            <?php renderFlash(); ?>

            <div class="stats-grid">
                <div class="stat-card amber">
                    <div class="label">Sedang Dipinjam</div>
                    <div class="value"><?= $totalPinjamku ?></div>
                    <div class="sub">Barang aktif</div>
                    <div class="stat-icon">🔄</div>
                </div>
                <div class="stat-card blue">
                    <div class="label">Total Riwayat</div>
                    <div class="value"><?= $totalRiwayat ?></div>
                    <div class="sub">Peminjaman saya</div>
                    <div class="stat-icon">📋</div>
                </div>
                <div class="stat-card green">
                    <div class="label">Barang Tersedia</div>
                    <div class="value"><?= $totalBarang ?></div>
                    <div class="sub">Bisa dipinjam</div>
                    <div class="stat-icon">📦</div>
                </div>
            </div>

            <?php if ($totalPinjamku > 0): ?>
            <div class="card">
                <div class="card-header">
                    <h3>🔄 Barang yang Sedang Saya Pinjam</h3>
                    <a href="riwayat.php" class="btn btn-ghost btn-sm">Lihat Semua →</a>
                </div>
                <div class="table-wrap">
                    <table>
                        <thead><tr><th>#</th><th>Nama Barang</th><th>Jumlah</th><th>Tgl Pinjam</th><th>Kondisi</th></tr></thead>
                        <tbody>
                        <?php while ($row = $pinjamAktif->fetch_assoc()): ?>
                            <tr>
                                <td class="mono" style="color:var(--muted)"><?= $row['id_pinjam'] ?></td>
                                <td><strong><?= clean($row['nama_barang']) ?></strong></td>
                                <td><span class="badge badge-blue"><?= $row['jumlah_pinjam'] ?> unit</span></td>
                                <td class="mono" style="font-size:.8rem"><?= $row['tanggal_pinjam'] ?></td>
                                <td><?= $row['kondisi_barang'] === 'baik' ? '<span class="badge badge-green">👍 Baik</span>' : '<span class="badge badge-red">🔧 Rusak</span>' ?></td>
                            </tr>
                        <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-body" style="display:flex;gap:1rem;flex-wrap:wrap;align-items:center">
                    <div>
                        <h3 style="font-family:var(--font-head);margin-bottom:.3rem">Ingin meminjam barang?</h3>
                        <p style="color:var(--muted);font-size:.875rem">Lihat daftar barang yang tersedia dan ajukan peminjaman.</p>
                    </div>
                    <div style="margin-left:auto;display:flex;gap:.75rem;flex-shrink:0">
                        <a href="barang.php" class="btn btn-ghost">📦 Lihat Barang</a>
                        <a href="pinjam.php" class="btn btn-primary">🛒 Pinjam Sekarang</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
