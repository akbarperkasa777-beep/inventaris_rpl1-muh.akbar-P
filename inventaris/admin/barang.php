<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/auth.php';
requireAdmin();

$active = 'barang';
$aksi = $_GET['aksi'] ?? '';
$edit_data = null;

// ── PROSES POST ─────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $act = $_POST['act'] ?? '';

    if ($act === 'tambah') {
        $nama    = clean($_POST['nama_barang']);
        $jumlah  = (int)$_POST['jumlah'];
        $kondisi = in_array($_POST['kondisi'], ['baik','rusak']) ? $_POST['kondisi'] : 'baik';

        if ($nama && $jumlah >= 0) {
            $stmt = $conn->prepare("INSERT INTO barang (nama_barang, jumlah, kondisi_barang) VALUES (?,?,?)");
            $stmt->bind_param("sis", $nama, $jumlah, $kondisi);
            if ($stmt->execute()) {
                setFlash('success', "Barang \"$nama\" berhasil ditambahkan.");
            } else {
                setFlash('danger', 'Gagal menambahkan barang.');
            }
        } else {
            setFlash('danger', 'Data tidak valid.');
        }
        redirect('barang.php');
    }

    if ($act === 'edit') {
        $id      = (int)$_POST['id_barang'];
        $nama    = clean($_POST['nama_barang']);
        $jumlah  = (int)$_POST['jumlah'];
        $kondisi = in_array($_POST['kondisi'], ['baik','rusak']) ? $_POST['kondisi'] : 'baik';

        $stmt = $conn->prepare("UPDATE barang SET nama_barang=?, jumlah=?, kondisi_barang=? WHERE id_barang=?");
        $stmt->bind_param("sisi", $nama, $jumlah, $kondisi, $id);
        if ($stmt->execute()) {
            setFlash('success', 'Data barang berhasil diperbarui.');
        } else {
            setFlash('danger', 'Gagal memperbarui barang.');
        }
        redirect('barang.php');
    }

    if ($act === 'hapus') {
        $id = (int)$_POST['id_barang'];
        $stmt = $conn->prepare("DELETE FROM barang WHERE id_barang=?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            setFlash('success', 'Barang berhasil dihapus.');
        } else {
            setFlash('danger', 'Gagal menghapus. Mungkin ada data peminjaman terkait.');
        }
        redirect('barang.php');
    }
}

// ── GET EDIT DATA ────────────────────────────────────────────
if ($aksi === 'edit' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $r = $conn->query("SELECT * FROM barang WHERE id_barang=$id");
    $edit_data = $r->fetch_assoc();
}

// ── FETCH BARANG LIST ────────────────────────────────────────
$cari = clean($_GET['cari'] ?? '');
$sql = "SELECT *, status_barang(jumlah) AS status_stok FROM barang";
if ($cari) $sql .= " WHERE nama_barang LIKE '%$cari%'";
$sql .= " ORDER BY id_barang DESC";
$barangList = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kelola Barang — Inventaris RPL 1</title>
    <link rel="stylesheet" href="../includes/style.css">
    <style>
        .modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,.6); z-index:200; place-items:center; }
        .modal-overlay.show { display:grid; }
        .modal-box { background:var(--card); border:1px solid var(--border); border-radius:14px; width:460px; max-width:95vw; overflow:hidden; animation:loginSlideUp .3s ease; }
        .modal-header { padding:1.2rem 1.5rem; border-bottom:1px solid var(--border); display:flex; justify-content:space-between; align-items:center; }
        .modal-header h3 { font-family:var(--font-head); font-weight:700; }
        .modal-close { background:none; border:none; color:var(--muted); cursor:pointer; font-size:1.2rem; }
        .modal-body { padding:1.5rem; }
        .modal-footer { padding:1rem 1.5rem; border-top:1px solid var(--border); display:flex; justify-content:flex-end; gap:.75rem; }
    </style>
</head>
<body>
<div class="wrapper">
    <?php include '../includes/sidebar.php'; ?>
    <div class="main">
        <div class="topbar">
            <div>
                <h2>📦 Kelola Barang</h2>
                <div class="breadcrumb">Admin → Barang Inventaris</div>
            </div>
            <div class="topbar-right">
                <button onclick="document.getElementById('modalTambah').classList.add('show')" class="btn btn-primary">
                    ➕ Tambah Barang
                </button>
            </div>
        </div>

        <div class="content">
            <?php renderFlash(); ?>

            <!-- Search -->
            <form method="GET" style="margin-bottom:1.5rem;display:flex;gap:.75rem">
                <input type="text" name="cari" class="form-control" style="max-width:320px"
                       placeholder="🔍 Cari nama barang..." value="<?= clean($_GET['cari'] ?? '') ?>">
                <button type="submit" class="btn btn-ghost">Cari</button>
                <?php if ($cari): ?><a href="barang.php" class="btn btn-ghost">× Reset</a><?php endif; ?>
            </form>

            <div class="card">
                <div class="card-header">
                    <h3>Daftar Barang Inventaris</h3>
                    <span class="badge badge-muted mono"><?= $barangList->num_rows ?> barang</span>
                </div>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>#ID</th>
                                <th>Nama Barang</th>
                                <th>Jumlah Stok</th>
                                <th>Status Stok</th>
                                <th>Kondisi</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if ($barangList->num_rows === 0): ?>
                            <tr><td colspan="6">
                                <div class="empty-state">
                                    <div class="empty-icon">📦</div>
                                    <p>Belum ada barang. Tambahkan barang pertama!</p>
                                </div>
                            </td></tr>
                        <?php else: while ($row = $barangList->fetch_assoc()):
                            $maxStok = 20;
                            $pct = min(100, ($row['jumlah'] / $maxStok) * 100);
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
                                <td>
                                    <?= $row['kondisi_barang'] === 'baik'
                                        ? '<span class="badge badge-green">👍 Baik</span>'
                                        : '<span class="badge badge-red">🔧 Rusak</span>' ?>
                                </td>
                                <td style="display:flex;gap:.5rem;flex-wrap:wrap">
                                    <button class="btn btn-ghost btn-sm"
                                        onclick="openEdit(<?= $row['id_barang'] ?>, '<?= addslashes(clean($row['nama_barang'])) ?>', <?= $row['jumlah'] ?>, '<?= $row['kondisi_barang'] ?>')">
                                        ✏️ Edit
                                    </button>
                                    <form method="POST" onsubmit="return confirm('Hapus barang ini?')">
                                        <input type="hidden" name="act" value="hapus">
                                        <input type="hidden" name="id_barang" value="<?= $row['id_barang'] ?>">
                                        <button type="submit" class="btn btn-danger btn-sm">🗑️ Hapus</button>
                                    </form>
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

<!-- Modal Tambah Barang -->
<div class="modal-overlay" id="modalTambah" onclick="if(event.target===this)this.classList.remove('show')">
    <div class="modal-box">
        <div class="modal-header">
            <h3>➕ Tambah Barang</h3>
            <button class="modal-close" onclick="document.getElementById('modalTambah').classList.remove('show')">✕</button>
        </div>
        <form method="POST">
            <input type="hidden" name="act" value="tambah">
            <div class="modal-body">
                <div class="form-group">
                    <label>Nama Barang</label>
                    <input type="text" name="nama_barang" class="form-control" placeholder="Contoh: Laptop Asus" required>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Jumlah Stok</label>
                        <input type="number" name="jumlah" class="form-control" min="0" placeholder="0" required>
                    </div>
                    <div class="form-group">
                        <label>Kondisi Barang</label>
                        <select name="kondisi" class="form-control">
                            <option value="baik">👍 Baik</option>
                            <option value="rusak">🔧 Rusak</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-ghost" onclick="document.getElementById('modalTambah').classList.remove('show')">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Barang</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Barang -->
<div class="modal-overlay" id="modalEdit" onclick="if(event.target===this)this.classList.remove('show')">
    <div class="modal-box">
        <div class="modal-header">
            <h3>✏️ Edit Barang</h3>
            <button class="modal-close" onclick="document.getElementById('modalEdit').classList.remove('show')">✕</button>
        </div>
        <form method="POST">
            <input type="hidden" name="act" value="edit">
            <input type="hidden" name="id_barang" id="edit_id">
            <div class="modal-body">
                <div class="form-group">
                    <label>Nama Barang</label>
                    <input type="text" name="nama_barang" id="edit_nama" class="form-control" required>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Jumlah Stok</label>
                        <input type="number" name="jumlah" id="edit_jumlah" class="form-control" min="0" required>
                    </div>
                    <div class="form-group">
                        <label>Kondisi</label>
                        <select name="kondisi" id="edit_kondisi" class="form-control">
                            <option value="baik">👍 Baik</option>
                            <option value="rusak">🔧 Rusak</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-ghost" onclick="document.getElementById('modalEdit').classList.remove('show')">Batal</button>
                <button type="submit" class="btn btn-primary">Update Barang</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEdit(id, nama, jumlah, kondisi) {
    document.getElementById('edit_id').value = id;
    document.getElementById('edit_nama').value = nama;
    document.getElementById('edit_jumlah').value = jumlah;
    document.getElementById('edit_kondisi').value = kondisi;
    document.getElementById('modalEdit').classList.add('show');
}
<?php if ($aksi === 'tambah'): ?>
document.getElementById('modalTambah').classList.add('show');
<?php endif; ?>
</script>
</body>
</html>
