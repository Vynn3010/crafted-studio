<?php
/**
 * Crafted Studio — Admin: Paket Foto Management
 */
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

requireRole('admin');

$db = getDB();
$flash = getFlash();

// Handle CRUD
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'tambah') {
        $db->prepare(
            "INSERT INTO paket_foto (nama_paket, deskripsi, harga, durasi_jam, jumlah_foto) VALUES (?, ?, ?, ?, ?)"
        )->execute([
            trim($_POST['nama_paket']),
            trim($_POST['deskripsi']),
            (float)$_POST['harga'],
            (int)$_POST['durasi_jam'],
            (int)$_POST['jumlah_foto']
        ]);
        setFlash('success', 'Paket baru berhasil ditambahkan.');
    } elseif ($action === 'hapus') {
        $db->prepare("DELETE FROM paket_foto WHERE id_paket = ?")->execute([(int)$_POST['id_paket']]);
        setFlash('success', 'Paket berhasil dihapus.');
    }

    header('Location: paket.php');
    exit;
}

$pakets = getPaketFoto();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Paket — Crafted Studio</title>
    <link rel="stylesheet" href="../../assets/css/admin.css">
    <link rel="stylesheet" href="https://unpkg.com/@phosphor-icons/web@2.0.3/src/regular/style.css">
</head>
<body>

<div class="admin-layout">
    <aside class="sidebar">
        <div class="sidebar-brand">
            <h2>Crafted <span>Studio</span></h2>
            <p>Dashboard Admin</p>
        </div>
        <nav class="sidebar-nav">
            <div class="nav-section">
                <div class="nav-section-title">Menu Utama</div>
                <a href="dashboard.php" class="nav-item">
                    <span class="icon"><i class="ph ph-squares-four"></i></span> Dashboard
                </a>
            </div>
            <div class="nav-section">
                <div class="nav-section-title">Kelola</div>
                <a href="bookings.php" class="nav-item">
                    <span class="icon"><i class="ph ph-calendar-check"></i></span> Semua Booking
                </a>
                <a href="paket.php" class="nav-item active">
                    <span class="icon"><i class="ph ph-package"></i></span> Paket Foto
                </a>
            </div>
        </nav>
        <div class="sidebar-footer">
            <div class="sidebar-user">
                <div class="avatar"><?= strtoupper(substr($_SESSION['nama'], 0, 2)) ?></div>
                <div class="user-info">
                    <h4><?= e($_SESSION['nama']) ?></h4>
                    <p><?= e($_SESSION['role']) ?></p>
                </div>
            </div>
            <a href="../logout.php" class="nav-item" style="margin-top:12px;color:var(--danger);">
                <span class="icon"><i class="ph ph-sign-out"></i></span> Logout
            </a>
        </div>
    </aside>

    <main class="admin-main">
        <div class="admin-topbar">
            <h1>Kelola Paket Foto</h1>
            <button class="btn btn-primary" onclick="document.getElementById('addModal').classList.add('active')">
                <i class="ph ph-plus"></i> Tambah Paket
            </button>
        </div>

        <?php if ($flash): ?>
        <div class="flash flash-<?= $flash['type'] ?>">
            <i class="ph ph-<?= $flash['type']==='success'?'check-circle':'warning-circle' ?>"></i>
            <?= e($flash['message']) ?>
        </div>
        <?php endif; ?>

        <div class="admin-card">
            <div class="admin-card-body" style="padding:0;">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Nama Paket</th>
                            <th>Harga</th>
                            <th>Durasi</th>
                            <th>Jumlah Foto</th>
                            <th>Deskripsi</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pakets as $p): ?>
                        <tr>
                            <td><strong><?= e($p['nama_paket']) ?></strong></td>
                            <td style="color:var(--accent);font-weight:600;"><?= formatRupiah($p['harga']) ?></td>
                            <td><?= $p['durasi_jam'] ?> jam</td>
                            <td><?= $p['jumlah_foto'] ?> foto</td>
                            <td style="max-width:200px;"><small style="color:var(--text-muted);"><?= e($p['deskripsi'] ?? '') ?></small></td>
                            <td>
                                <form method="POST" style="display:inline;" onsubmit="return confirm('Hapus paket ini?')">
                                    <input type="hidden" name="action" value="hapus">
                                    <input type="hidden" name="id_paket" value="<?= $p['id_paket'] ?>">
                                    <button type="submit" class="btn btn-danger btn-sm">
                                        <i class="ph ph-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<!-- Add Modal -->
<div class="modal-overlay" id="addModal">
    <div class="modal">
        <div class="modal-header">
            <h3><i class="ph ph-plus-circle" style="color:var(--accent);margin-right:8px;"></i>Tambah Paket Baru</h3>
            <button class="modal-close" onclick="document.getElementById('addModal').classList.remove('active')">&times;</button>
        </div>
        <form method="POST">
            <div class="modal-body">
                <input type="hidden" name="action" value="tambah">
                <div class="form-group">
                    <label>Nama Paket</label>
                    <input type="text" name="nama_paket" class="form-control" placeholder="Paket Premium" required>
                </div>
                <div class="form-group">
                    <label>Harga (Rp)</label>
                    <input type="number" name="harga" class="form-control" placeholder="500000" min="0" required>
                </div>
                <div class="form-group">
                    <label>Durasi (Jam)</label>
                    <input type="number" name="durasi_jam" class="form-control" value="1" min="1" max="12" required>
                </div>
                <div class="form-group">
                    <label>Jumlah Foto Edit</label>
                    <input type="number" name="jumlah_foto" class="form-control" value="10" min="1" required>
                </div>
                <div class="form-group">
                    <label>Deskripsi</label>
                    <textarea name="deskripsi" class="form-control" rows="3" placeholder="Deskripsi paket..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="document.getElementById('addModal').classList.remove('active')">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="ph ph-plus"></i> Tambah</button>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('addModal').addEventListener('click', function(e) {
    if (e.target === this) this.classList.remove('active');
});
</script>

</body>
</html>
