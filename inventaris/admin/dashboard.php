<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/auth.php';
requireAdmin();

$active = 'dashboard';

// Stats
$totalBarang   = $conn->query("SELECT COUNT(*) FROM barang")->fetch_row()[0];
$totalStok     = $conn->query("SELECT COALESCE(SUM(jumlah),0) FROM barang")->fetch_row()[0];
$totalPinjam   = $conn->query("SELECT COUNT(*) FROM peminjaman WHERE status='dipinjam'")->fetch_row()[0];
$totalUser     = $conn->query("SELECT COUNT(*) FROM user WHERE role='user'")->fetch_row()[0];
$stokHabis     = $conn->query("SELECT COUNT(*) FROM barang WHERE jumlah=0")->fetch_row()[0];
$barangRusak   = $conn->query("SELECT COUNT(*) FROM barang WHERE kondisi_barang='rusak'")->fetch_row()[0];

// Recent peminjaman
$recentPinjam = $conn->query("
    SELECT p.*, u.nama AS nama_user, b.nama_barang
    FROM peminjaman p
    JOIN user u ON p.id_user = u.id_user
    JOIN barang b ON p.id_barang = b.id_barang
    ORDER BY p.id_pinjam DESC
    LIMIT 8
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard Admin — Inventaris RPL 1</title>
    <link rel="stylesheet" href="../includes/style.css">
</head>
<body>
<div class="wrapper">
    <?php include '../includes/sidebar.php'; ?>

    <div class="main">
        <div class="topbar">
            <div>
                <h2>Dashboard Admin</h2>
                <div class="breadcrumb">Selamat datang, <?= clean(currentUser()['nama']) ?> 👋</div>
            </div>
            <div class="topbar-right">
                <span class="badge badge-amber">⭐ Admin</span>
                <span class="mono" style="font-size:.7rem;color:var(--muted)"><?= date('d M Y') ?></span>
            </div>
        </div>

        <div class="content">
            <?php renderFlash(); ?>

            <!-- Stats -->
            <div class="stats-grid">
                <div class="stat-card amber">
                    <div class="label">Total Barang</div>
                    <div class="value"><?= $totalBarang ?></div>
                    <div class="sub">Jenis barang</div>
                    <div class="stat-icon">📦</div>
                </div>
                <div class="stat-card green">
                    <div class="label">Total Stok</div>
                    <div class="value"><?= $totalStok ?></div>
                    <div class="sub">Unit tersedia</div>
                    <div class="stat-icon">📊</div>
                </div>
                <div class="stat-card blue">
                    <div class="label">Sedang Dipinjam</div>
                    <div class="value"><?= $totalPinjam ?></div>
                    <div class="sub">Transaksi aktif</div>
                    <div class="stat-icon">📋</div>
                </div>
                <div class="stat-card red">
                    <div class="label">Stok Habis</div>
                    <div class="value"><?= $stokHabis ?></div>
                    <div class="sub">Perlu restok</div>
                    <div class="stat-icon">⚠️</div>
                </div>
                <div class="stat-card amber">
                    <div class="label">Barang Rusak</div>
                    <div class="value"><?= $barangRusak ?></div>
                    <div class="sub">Perlu perbaikan</div>
                    <div class="stat-icon">🔧</div>
                </div>
                <div class="stat-card green">
                    <div class="label">Pengguna</div>
                    <div class="value"><?= $totalUser ?></div>
                    <div class="sub">Akun aktif</div>
                    <div class="stat-icon">👥</div>
                </div>
            </div>

            <!-- Recent Peminjaman -->
            <div class="card">
                <div class="card-header">
                    <h3>📋 Peminjaman Terbaru</h3>
                    <a href="peminjaman.php" class="btn btn-ghost btn-sm">Lihat Semua →</a>
                </div>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Peminjam</th>
                                <th>Barang</th>
                                <th>Jumlah</th>
                                <th>Tgl Pinjam</th>
                                <th>Tgl Kembali</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if ($recentPinjam->num_rows === 0): ?>
                            <tr><td colspan="7" class="empty-state"><div class="empty-icon">📭</div><p>Belum ada data peminjaman</p></td></tr>
                        <?php else: while ($row = $recentPinjam->fetch_assoc()): ?>
                            <tr>
                                <td class="mono" style="color:var(--muted)"><?= $row['id_pinjam'] ?></td>
                                <td><strong><?= clean($row['nama_user']) ?></strong></td>
                                <td><?= clean($row['nama_barang']) ?></td>
                                <td><span class="badge badge-blue"><?= $row['jumlah_pinjam'] ?> unit</span></td>
                                <td class="mono" style="font-size:.8rem"><?= $row['tanggal_pinjam'] ?></td>
                                <td class="mono" style="font-size:.8rem"><?= $row['tanggal_kembali'] ?? '<span style="color:var(--muted)">—</span>' ?></td>
                                <td>
                                    <?php if ($row['status'] === 'dipinjam'): ?>
                                        <span class="badge badge-amber">🔄 Dipinjam</span>
                                    <?php else: ?>
                                        <span class="badge badge-green">✅ Dikembalikan</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card">
                <div class="card-header"><h3>⚡ Aksi Cepat</h3></div>
                <div class="card-body" style="display:flex;gap:1rem;flex-wrap:wrap">
                    <a href="barang.php?aksi=tambah" class="btn btn-primary">➕ Tambah Barang</a>
                    <a href="peminjaman.php" class="btn btn-ghost">📋 Kelola Peminjaman</a>
                    <a href="laporan.php" class="btn btn-ghost">📊 Lihat Laporan</a>
                    <a href="pengguna.php" class="btn btn-ghost">👥 Kelola Pengguna</a>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
