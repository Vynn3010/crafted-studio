<?php
/**
 * Crafted Studio — Editor Dashboard
 */
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

requireRole('editor');

$db = getDB();
$flash = getFlash();

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $editingId = (int)($_POST['editing_id'] ?? 0);
    $newStatus = $_POST['status'] ?? '';
    $catatan   = trim($_POST['catatan'] ?? '');

    if ($editingId && in_array($newStatus, ['editing', 'selesai'])) {
        $db->prepare("UPDATE editing_foto SET status = ?, catatan = ?, tanggal_edit = NOW() WHERE id_editing = ? AND id_editor = ?")
           ->execute([$newStatus, $catatan, $editingId, $_SESSION['user_id']]);
        setFlash('success', 'Status editing berhasil diupdate.');
        header('Location: dashboard.php');
        exit;
    }
}

$editingTasks = getEditingByEditor($_SESSION['user_id']);
$menunggu = array_filter($editingTasks, fn($t) => $t['status'] === 'menunggu');
$editing  = array_filter($editingTasks, fn($t) => $t['status'] === 'editing');
$selesai  = array_filter($editingTasks, fn($t) => $t['status'] === 'selesai');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Editor — Crafted Studio</title>
    <link rel="stylesheet" href="../../assets/css/admin.css">
    <link rel="stylesheet" href="https://unpkg.com/@phosphor-icons/web@2.0.3/src/regular/style.css">
</head>
<body>

<div class="admin-layout">
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-brand">
            <h2>Crafted <span>Studio</span></h2>
            <p>Dashboard Editor</p>
        </div>

        <nav class="sidebar-nav">
            <div class="nav-section">
                <div class="nav-section-title">Menu</div>
                <a href="dashboard.php" class="nav-item active">
                    <span class="icon"><i class="ph ph-squares-four"></i></span>
                    Dashboard
                    <?php if (count($menunggu) > 0): ?>
                    <span class="badge-nav"><?= count($menunggu) ?></span>
                    <?php endif; ?>
                </a>
            </div>
        </nav>

        <div class="sidebar-footer">
            <div class="sidebar-user">
                <div class="avatar"><?= strtoupper(substr($_SESSION['nama'], 0, 2)) ?></div>
                <div class="user-info">
                    <h4><?= e($_SESSION['nama']) ?></h4>
                    <p>Editor</p>
                </div>
            </div>
            <a href="../logout.php" class="nav-item" style="margin-top:12px;color:var(--danger);">
                <span class="icon"><i class="ph ph-sign-out"></i></span>
                Logout
            </a>
        </div>
    </aside>

    <!-- Main -->
    <main class="admin-main">
        <div class="admin-topbar">
            <h1>Halo, <?= e($_SESSION['nama']) ?> 🎨</h1>
            <span style="font-size:0.85rem;color:var(--text-muted);">
                <i class="ph ph-calendar"></i> <?= date('d M Y') ?>
            </span>
        </div>

        <?php if ($flash): ?>
        <div class="flash flash-<?= $flash['type'] ?>">
            <i class="ph ph-<?= $flash['type'] === 'success' ? 'check-circle' : 'warning-circle' ?>"></i>
            <?= e($flash['message']) ?>
        </div>
        <?php endif; ?>

        <!-- Stats -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon red"><i class="ph ph-clock"></i></div>
                <div class="stat-value"><?= count($menunggu) ?></div>
                <div class="stat-label">Menunggu</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon blue"><i class="ph ph-paint-brush"></i></div>
                <div class="stat-value"><?= count($editing) ?></div>
                <div class="stat-label">Sedang Editing</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon green"><i class="ph ph-check-circle"></i></div>
                <div class="stat-value"><?= count($selesai) ?></div>
                <div class="stat-label">Selesai</div>
            </div>
        </div>

        <!-- Task List -->
        <div class="admin-card">
            <div class="admin-card-header">
                <h3><i class="ph ph-paint-brush" style="color:var(--accent);margin-right:8px;"></i>Tugas Editing</h3>
            </div>
            <div class="admin-card-body" style="padding:0;">
                <?php if (empty($editingTasks)): ?>
                <div class="empty-state">
                    <div class="icon"><i class="ph ph-image"></i></div>
                    <h3>Belum ada tugas editing</h3>
                    <p>Admin akan menugaskan tugas editing kepada Anda</p>
                </div>
                <?php else: ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Booking</th>
                            <th>Pelanggan</th>
                            <th>Paket</th>
                            <th>Tanggal Sesi</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($editingTasks as $t): ?>
                        <tr>
                            <td><strong style="color:var(--accent);">#CS-<?= str_pad($t['id_booking'], 4, '0', STR_PAD_LEFT) ?></strong></td>
                            <td><?= e($t['nama_pelanggan']) ?></td>
                            <td><?= e($t['nama_paket']) ?></td>
                            <td><?= date('d M Y', strtotime($t['tanggal_booking'])) ?></td>
                            <td><?= statusBadge($t['status']) ?></td>
                            <td>
                                <?php if ($t['status'] === 'menunggu'): ?>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="editing_id" value="<?= $t['id_editing'] ?>">
                                    <input type="hidden" name="status" value="editing">
                                    <input type="hidden" name="catatan" value="">
                                    <button type="submit" class="btn btn-primary btn-sm">
                                        <i class="ph ph-play"></i> Mulai
                                    </button>
                                </form>
                                <?php elseif ($t['status'] === 'editing'): ?>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="editing_id" value="<?= $t['id_editing'] ?>">
                                    <input type="hidden" name="status" value="selesai">
                                    <input type="hidden" name="catatan" value="">
                                    <button type="submit" class="btn btn-success btn-sm">
                                        <i class="ph ph-check"></i> Selesai
                                    </button>
                                </form>
                                <?php else: ?>
                                <span class="badge badge-success">✓ Selesai</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>

</body>
</html>
