<?php
// includes/sidebar.php
// Usage: include with $active = 'dashboard' | 'barang' | 'peminjaman' | 'pengguna'
$user = currentUser();
$isAdmin = isAdmin();
$initial = strtoupper(substr($user['nama'], 0, 1));
$base = $isAdmin ? '../admin/' : '../user/';
?>
<aside class="sidebar">
    <div class="sidebar-brand">
        <div class="logo-mark">
            <div class="logo-icon">📦</div>
            <h1>Inventaris<br><span style="color:var(--accent)">RPL 1</span></h1>
        </div>
        <p>SMKN · Manajemen Aset</p>
    </div>

    <div class="sidebar-user">
        <div class="avatar"><?= $initial ?></div>
        <div class="uinfo">
            <small><?= $isAdmin ? '⭐ Administrator' : '👤 Pengguna' ?></small>
            <strong><?= clean($user['nama']) ?></strong>
        </div>
    </div>

    <nav class="sidebar-nav">
        <?php if ($isAdmin): ?>
        <div class="nav-section-label">Admin Panel</div>
        <a href="../admin/dashboard.php"  class="nav-link <?= ($active??'')==='dashboard' ? 'active':'' ?>"><span class="icon">🏠</span> Dashboard</a>
        <a href="../admin/barang.php"     class="nav-link <?= ($active??'')==='barang' ? 'active':'' ?>"><span class="icon">📦</span> Kelola Barang</a>
        <a href="../admin/peminjaman.php" class="nav-link <?= ($active??'')==='peminjaman' ? 'active':'' ?>"><span class="icon">📋</span> Data Peminjaman</a>
        <a href="../admin/pengguna.php"   class="nav-link <?= ($active??'')==='pengguna' ? 'active':'' ?>"><span class="icon">👥</span> Pengguna</a>
        <a href="../admin/laporan.php"    class="nav-link <?= ($active??'')==='laporan' ? 'active':'' ?>"><span class="icon">📊</span> Laporan</a>
        <?php else: ?>
        <div class="nav-section-label">Menu</div>
        <a href="../user/dashboard.php"   class="nav-link <?= ($active??'')==='dashboard' ? 'active':'' ?>"><span class="icon">🏠</span> Dashboard</a>
        <a href="../user/barang.php"      class="nav-link <?= ($active??'')==='barang' ? 'active':'' ?>"><span class="icon">📦</span> Daftar Barang</a>
        <a href="../user/pinjam.php"      class="nav-link <?= ($active??'')==='pinjam' ? 'active':'' ?>"><span class="icon">🛒</span> Pinjam Barang</a>
        <a href="../user/riwayat.php"     class="nav-link <?= ($active??'')==='riwayat' ? 'active':'' ?>"><span class="icon">🕒</span> Riwayat Saya</a>
        <?php endif; ?>
    </nav>

    <div class="sidebar-footer">
        <a href="../logout.php" class="btn-logout">🚪 Keluar</a>
    </div>
</aside>
<?php
function renderFlash() {
    $f = getFlash();
    if ($f): ?>
    <div class="alert alert-<?= $f['type'] === 'success' ? 'success' : ($f['type'] === 'danger' ? 'danger' : 'warning') ?>">
        <?= $f['type'] === 'success' ? '✅' : ($f['type'] === 'danger' ? '❌' : '⚠️') ?>
        <?= clean($f['msg']) ?>
    </div>
    <?php endif;
}
?>
