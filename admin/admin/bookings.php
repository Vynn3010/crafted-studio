<?php
/**
 * Crafted Studio — Admin: All Bookings View
 */
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

requireRole('admin', 'staff');

$statusFilter = $_GET['status'] ?? '';
$dateFilter   = $_GET['date'] ?? '';
$bookings = getAllBookings($statusFilter, $dateFilter);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Semua Booking — Crafted Studio</title>
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
                    <span class="icon"><i class="ph ph-squares-four"></i></span>
                    Dashboard
                </a>
            </div>
            <div class="nav-section">
                <div class="nav-section-title">Kelola</div>
                <a href="bookings.php" class="nav-item active">
                    <span class="icon"><i class="ph ph-calendar-check"></i></span>
                    Semua Booking
                </a>
                <a href="paket.php" class="nav-item">
                    <span class="icon"><i class="ph ph-package"></i></span>
                    Paket Foto
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
            <h1>Semua Booking</h1>
        </div>

        <div class="filter-bar">
            <form method="GET" class="form-inline">
                <select name="status" class="form-control" onchange="this.form.submit()">
                    <option value="">Semua Status</option>
                    <option value="menunggu" <?= $statusFilter==='menunggu'?'selected':'' ?>>Menunggu</option>
                    <option value="dikonfirmasi" <?= $statusFilter==='dikonfirmasi'?'selected':'' ?>>Dikonfirmasi</option>
                    <option value="selesai" <?= $statusFilter==='selesai'?'selected':'' ?>>Selesai</option>
                    <option value="dibatalkan" <?= $statusFilter==='dibatalkan'?'selected':'' ?>>Dibatalkan</option>
                </select>
                <input type="date" name="date" class="form-control" value="<?= e($dateFilter) ?>" onchange="this.form.submit()">
                <?php if ($statusFilter || $dateFilter): ?>
                <a href="bookings.php" class="btn btn-outline btn-sm">Reset</a>
                <?php endif; ?>
            </form>
        </div>

        <div class="admin-card">
            <div class="admin-card-body" style="padding:0;overflow-x:auto;">
                <?php if (empty($bookings)): ?>
                <div class="empty-state">
                    <div class="icon"><i class="ph ph-calendar-x"></i></div>
                    <h3>Tidak ada booking ditemukan</h3>
                </div>
                <?php else: ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Pelanggan</th>
                            <th>Kontak</th>
                            <th>Tanggal</th>
                            <th>Waktu</th>
                            <th>Paket</th>
                            <th>Fotografer</th>
                            <th>Studio</th>
                            <th>Total</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bookings as $b): ?>
                        <tr>
                            <td><strong style="color:var(--accent);">#CS-<?= str_pad($b['id_booking'], 4, '0', STR_PAD_LEFT) ?></strong></td>
                            <td><strong><?= e($b['nama_pelanggan']) ?></strong></td>
                            <td>
                                <?= e($b['no_hp']) ?><br>
                                <small style="color:var(--text-muted);"><?= e($b['email_pelanggan']) ?></small>
                            </td>
                            <td><?= date('d M Y', strtotime($b['tanggal_booking'])) ?></td>
                            <td><?= substr($b['jam_mulai'], 0, 5) ?> — <?= substr($b['jam_selesai'], 0, 5) ?></td>
                            <td><?= e($b['nama_paket']) ?></td>
                            <td><?= e($b['nama_fotografer'] ?? '—') ?></td>
                            <td><?= e($b['nama_ruangan'] ?? '—') ?></td>
                            <td><strong><?= formatRupiah($b['total_harga']) ?></strong></td>
                            <td><?= statusBadge($b['status']) ?></td>
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
