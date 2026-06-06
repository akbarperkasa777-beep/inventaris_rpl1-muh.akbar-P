<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/auth.php';
requireAdmin();

$active = 'laporan';

// Summary stats for report
$totalPinjam   = $conn->query("SELECT COUNT(*) FROM peminjaman")->fetch_row()[0];
$totalKembali  = $conn->query("SELECT COUNT(*) FROM peminjaman WHERE status='dikembalikan'")->fetch_row()[0];
$totalAktif    = $conn->query("SELECT COUNT(*) FROM peminjaman WHERE status='dipinjam'")->fetch_row()[0];

// Barang paling sering dipinjam
$topBarang = $conn->query("
    SELECT b.nama_barang, COUNT(p.id_pinjam) AS total, SUM(p.jumlah_pinjam) AS total_unit
    FROM peminjaman p JOIN barang b ON p.id_barang = b.id_barang
    GROUP BY p.id_barang ORDER BY total DESC LIMIT 5
");

// User paling aktif
$topUser = $conn->query("
    SELECT u.nama, COUNT(p.id_pinjam) AS total
    FROM peminjaman p JOIN user u ON p.id_user = u.id_user
    WHERE u.role = 'user'
    GROUP BY p.id_user ORDER BY total DESC LIMIT 5
");

// Semua data peminjaman untuk tabel laporan
$allData = $conn->query("
    SELECT p.*, u.nama AS nama_user, b.nama_barang
    FROM peminjaman p
    JOIN user u ON p.id_user = u.id_user
    JOIN barang b ON p.id_barang = b.id_barang
    ORDER BY p.id_pinjam DESC
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Laporan — Inventaris RPL 1</title>
    <link rel="stylesheet" href="../includes/style.css">
    <style>
        @media print {
            .sidebar, .topbar, .no-print { display: none !important; }
            .main { margin-left: 0 !important; }
            .card { border: 1px solid #ccc; }
            body { background: #fff; color: #000; }
        }
    </style>
</head>
<body>
<div class="wrapper">
    <?php include '../includes/sidebar.php'; ?>
    <div class="main">
        <div class="topbar">
            <div><h2>📊 Laporan Peminjaman</h2><div class="breadcrumb">Admin → Laporan</div></div>
            <div class="topbar-right no-print">
                <button onclick="window.print()" class="btn btn-ghost">🖨️ Cetak Laporan</button>
            </div>
        </div>
        <div class="content">

            <!-- Laporan Header -->
            <div class="card" style="margin-bottom:1.5rem">
                <div class="card-body" style="text-align:center;padding:2rem">
                    <div style="font-family:var(--font-mono);font-size:.7rem;color:var(--muted);text-transform:uppercase;letter-spacing:.1em;margin-bottom:.5rem">
                        SMKN — XI RPL 1
                    </div>
                    <h2 style="font-family:var(--font-head);font-size:1.5rem;font-weight:800;margin-bottom:.3rem">
                        LAPORAN PEMINJAMAN BARANG INVENTARIS
                    </h2>
                    <p style="color:var(--muted);font-size:.85rem">
                        Dicetak pada: <?= date('d F Y, H:i') ?> WIB &nbsp;·&nbsp; Muh Akbar Perkasa
                    </p>
                </div>
            </div>

            <!-- Ringkasan -->
            <div class="stats-grid" style="margin-bottom:1.5rem">
                <div class="stat-card blue">
                    <div class="label">Total Transaksi</div>
                    <div class="value"><?= $totalPinjam ?></div>
                    <div class="sub">Semua peminjaman</div>
                    <div class="stat-icon">📋</div>
                </div>
                <div class="stat-card amber">
                    <div class="label">Sedang Dipinjam</div>
                    <div class="value"><?= $totalAktif ?></div>
                    <div class="sub">Belum dikembalikan</div>
                    <div class="stat-icon">🔄</div>
                </div>
                <div class="stat-card green">
                    <div class="label">Sudah Kembali</div>
                    <div class="value"><?= $totalKembali ?></div>
                    <div class="sub">Selesai</div>
                    <div class="stat-icon">✅</div>
                </div>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1.5rem">
                <!-- Top Barang -->
                <div class="card">
                    <div class="card-header"><h3>🏆 Barang Paling Sering Dipinjam</h3></div>
                    <div class="table-wrap">
                        <table>
                            <thead><tr><th>Barang</th><th>Frekuensi</th><th>Total Unit</th></tr></thead>
                            <tbody>
                            <?php if ($topBarang->num_rows === 0): ?>
                                <tr><td colspan="3" style="text-align:center;color:var(--muted);padding:1.5rem">Belum ada data</td></tr>
                            <?php else: while ($r = $topBarang->fetch_assoc()): ?>
                                <tr>
                                    <td><?= clean($r['nama_barang']) ?></td>
                                    <td><span class="badge badge-amber"><?= $r['total'] ?>×</span></td>
                                    <td class="mono"><?= $r['total_unit'] ?> unit</td>
                                </tr>
                            <?php endwhile; endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Top User -->
                <div class="card">
                    <div class="card-header"><h3>👥 Pengguna Paling Aktif</h3></div>
                    <div class="table-wrap">
                        <table>
                            <thead><tr><th>Nama</th><th>Total Pinjam</th></tr></thead>
                            <tbody>
                            <?php if ($topUser->num_rows === 0): ?>
                                <tr><td colspan="2" style="text-align:center;color:var(--muted);padding:1.5rem">Belum ada data</td></tr>
                            <?php else: while ($r = $topUser->fetch_assoc()): ?>
                                <tr>
                                    <td><?= clean($r['nama']) ?></td>
                                    <td><span class="badge badge-blue"><?= $r['total'] ?>×</span></td>
                                </tr>
                            <?php endwhile; endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Tabel Lengkap -->
            <div class="card">
                <div class="card-header"><h3>📋 Detail Semua Peminjaman</h3></div>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr><th>#</th><th>Peminjam</th><th>Barang</th><th>Jml</th><th>Tgl Pinjam</th><th>Tgl Kembali</th><th>Status</th></tr>
                        </thead>
                        <tbody>
                        <?php while ($row = $allData->fetch_assoc()): ?>
                            <tr>
                                <td class="mono" style="color:var(--muted)"><?= $row['id_pinjam'] ?></td>
                                <td><?= clean($row['nama_user']) ?></td>
                                <td><?= clean($row['nama_barang']) ?></td>
                                <td><?= $row['jumlah_pinjam'] ?></td>
                                <td class="mono" style="font-size:.8rem"><?= $row['tanggal_pinjam'] ?></td>
                                <td class="mono" style="font-size:.8rem"><?= $row['tanggal_kembali'] ?? '—' ?></td>
                                <td><?= $row['status'] === 'dipinjam' ? '<span class="badge badge-amber">Dipinjam</span>' : '<span class="badge badge-green">Dikembalikan</span>' ?></td>
                            </tr>
                        <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
