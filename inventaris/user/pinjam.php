<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/auth.php';
requireLogin();

$active = 'pinjam';
$uid = currentUser()['id_user'];

// Proses peminjaman
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_barang = (int)$_POST['id_barang'];
    $jumlah    = (int)$_POST['jumlah'];

    if ($id_barang && $jumlah > 0) {
        $result = $conn->query("CALL pinjam_barang($uid, $id_barang, $jumlah)");
        if ($result) {
            $row = $result->fetch_assoc();
            if ($row['hasil'] === 'SUKSES') {
                setFlash('success', '✅ ' . $row['pesan']);
            } else {
                setFlash('danger', '❌ ' . $row['pesan']);
            }
        } else {
            setFlash('danger', 'Terjadi kesalahan saat memproses peminjaman.');
        }
        redirect('pinjam.php');
    }
}

// Preselect barang dari GET
$preselect = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Ambil barang yang tersedia
$barangList = $conn->query("SELECT * FROM barang WHERE jumlah > 0 AND kondisi_barang = 'baik' ORDER BY nama_barang");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pinjam Barang — Inventaris RPL 1</title>
    <link rel="stylesheet" href="../includes/style.css">
</head>
<body>
<div class="wrapper">
    <?php include '../includes/sidebar.php'; ?>
    <div class="main">
        <div class="topbar">
            <div><h2>🛒 Pinjam Barang</h2><div class="breadcrumb">User → Formulir Peminjaman</div></div>
        </div>
        <div class="content">
            <?php renderFlash(); ?>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;align-items:start">
                <!-- Form Pinjam -->
                <div class="card">
                    <div class="card-header"><h3>📝 Formulir Peminjaman</h3></div>
                    <div class="card-body">
                        <?php if ($barangList->num_rows === 0): ?>
                            <div class="empty-state">
                                <div class="empty-icon">😕</div>
                                <p>Tidak ada barang yang tersedia untuk dipinjam saat ini.</p>
                            </div>
                        <?php else: ?>
                        <form method="POST" id="formPinjam">
                            <div class="form-group">
                                <label>Pilih Barang</label>
                                <select name="id_barang" id="pilihBarang" class="form-control" required onchange="updateStok()">
                                    <option value="">— Pilih barang —</option>
                                    <?php while ($b = $barangList->fetch_assoc()): ?>
                                    <option value="<?= $b['id_barang'] ?>"
                                            data-stok="<?= $b['jumlah'] ?>"
                                            <?= $preselect == $b['id_barang'] ? 'selected' : '' ?>>
                                        <?= clean($b['nama_barang']) ?> (Stok: <?= $b['jumlah'] ?>)
                                    </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Jumlah Pinjam</label>
                                <input type="number" name="jumlah" id="jumlahPinjam" class="form-control"
                                       min="1" max="1" value="1" required placeholder="Masukkan jumlah">
                                <div class="form-hint" id="stokInfo">Pilih barang terlebih dahulu</div>
                            </div>
                            <div class="form-group">
                                <label>Peminjam</label>
                                <input type="text" class="form-control" value="<?= clean(currentUser()['nama']) ?>" disabled>
                            </div>
                            <div class="form-group">
                                <label>Tanggal Pinjam</label>
                                <input type="text" class="form-control" value="<?= date('d F Y') ?>" disabled>
                            </div>
                            <button type="submit" class="btn btn-primary btn-lg" style="width:100%;justify-content:center"
                                    onclick="return confirm('Konfirmasi peminjaman barang?')">
                                ✅ Konfirmasi Pinjam
                            </button>
                        </form>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Info Card -->
                <div>
                    <div class="card" style="margin-bottom:1rem">
                        <div class="card-header"><h3>📌 Peraturan Peminjaman</h3></div>
                        <div class="card-body">
                            <ul style="padding-left:1.2rem;color:var(--muted);font-size:.875rem;line-height:2">
                                <li>Barang dipinjam untuk keperluan sekolah</li>
                                <li>Kembalikan barang tepat waktu</li>
                                <li>Jaga kondisi barang selama dipinjam</li>
                                <li>Laporkan jika barang rusak saat dikembalikan</li>
                                <li>Maksimum pinjam sesuai stok tersedia</li>
                            </ul>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header"><h3>🔄 Pinjaman Aktif Saya</h3></div>
                        <div class="card-body">
                            <?php
                            $aktif = $conn->query("
                                SELECT p.*, b.nama_barang FROM peminjaman p
                                JOIN barang b ON p.id_barang = b.id_barang
                                WHERE p.id_user = $uid AND p.status = 'dipinjam'
                                ORDER BY p.id_pinjam DESC LIMIT 5
                            ");
                            if ($aktif->num_rows === 0): ?>
                                <p style="color:var(--muted);font-size:.875rem">Tidak ada pinjaman aktif.</p>
                            <?php else: while ($r = $aktif->fetch_assoc()): ?>
                                <div style="display:flex;justify-content:space-between;align-items:center;padding:.6rem 0;border-bottom:1px solid var(--border)">
                                    <div>
                                        <div style="font-size:.875rem;font-weight:500"><?= clean($r['nama_barang']) ?></div>
                                        <div class="mono" style="font-size:.7rem;color:var(--muted)"><?= $r['tanggal_pinjam'] ?></div>
                                    </div>
                                    <span class="badge badge-amber"><?= $r['jumlah_pinjam'] ?> unit</span>
                                </div>
                            <?php endwhile; endif; ?>
                            <a href="riwayat.php" class="btn btn-ghost btn-sm" style="margin-top:1rem;width:100%;justify-content:center">Lihat Semua Riwayat</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function updateStok() {
    const sel = document.getElementById('pilihBarang');
    const opt = sel.options[sel.selectedIndex];
    const stok = opt.dataset.stok;
    const inp = document.getElementById('jumlahPinjam');
    const info = document.getElementById('stokInfo');

    if (stok) {
        inp.max = stok;
        inp.value = 1;
        info.textContent = `Stok tersedia: ${stok} unit (maks ${stok})`;
        info.style.color = stok <= 5 ? 'var(--accent)' : 'var(--muted)';
    }
}
// Auto update if preselected
window.addEventListener('DOMContentLoaded', updateStok);
</script>
</body>
</html>
