<?php
/**
 * Crafted Studio — Fotografer Dashboard
 */
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

requireRole('fotografer');

$today = date('Y-m-d');
$dateFilter = $_GET['date'] ?? '';
$bookings = getBookingsByFotografer($_SESSION['user_id'], $dateFilter ?: null);

// Separate today's and upcoming
$todayBookings = array_filter($bookings, fn($b) => $b['tanggal_booking'] === $today);
$upcomingBookings = array_filter($bookings, fn($b) => $b['tanggal_booking'] > $today);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Fotografer — Crafted Studio</title>
    <link rel="stylesheet" href="../../assets/css/admin.css">
    <link rel="stylesheet" href="https://unpkg.com/@phosphor-icons/web@2.0.3/src/regular/style.css">
</head>
<body>

<div class="admin-layout">
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-brand">
            <h2>Crafted <span>Studio</span></h2>
            <p>Dashboard Fotografer</p>
        </div>

        <nav class="sidebar-nav">
            <div class="nav-section">
                <div class="nav-section-title">Menu</div>
                <a href="dashboard.php" class="nav-item active">
                    <span class="icon"><i class="ph ph-squares-four"></i></span>
                    Dashboard
                </a>
            </div>
        </nav>

        <div class="sidebar-footer">
            <div class="sidebar-user">
                <div class="avatar"><?= strtoupper(substr($_SESSION['nama'], 0, 2)) ?></div>
                <div class="user-info">
                    <h4><?= e($_SESSION['nama']) ?></h4>
                    <p>Fotografer</p>
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
            <h1>Halo, <?= e($_SESSION['nama']) ?> 👋</h1>
            <span style="font-size:0.85rem;color:var(--text-muted);">
                <i class="ph ph-calendar"></i> <?= date('d M Y') ?>
            </span>
        </div>

        <!-- Stats -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon yellow"><i class="ph ph-camera"></i></div>
                <div class="stat-value"><?= count($todayBookings) ?></div>
                <div class="stat-label">Sesi Hari Ini</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon blue"><i class="ph ph-calendar-check"></i></div>
                <div class="stat-value"><?= count($upcomingBookings) ?></div>
                <div class="stat-label">Sesi Mendatang</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon green"><i class="ph ph-star"></i></div>
                <div class="stat-value"><?= count($bookings) ?></div>
                <div class="stat-label">Total Sesi</div>
            </div>
        </div>

        <!-- Today's Sessions -->
        <div class="admin-card" style="margin-bottom:24px;">
            <div class="admin-card-header">
                <h3><i class="ph ph-sun" style="color:var(--warning);margin-right:8px;"></i>Sesi Hari Ini</h3>
            </div>
            <div class="admin-card-body" style="padding:0;">
                <?php if (empty($todayBookings)): ?>
                <div class="empty-state">
                    <div class="icon"><i class="ph ph-coffee"></i></div>
                    <h3>Tidak ada sesi hari ini</h3>
                    <p>Nikmati waktu istirahat Anda!</p>
                </div>
                <?php else: ?>
                <table class="data-table">
                    <thead>
                        <tr><th>Waktu</th><th>Pelanggan</th><th>Paket</th><th>Studio</th><th>Status</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($todayBookings as $b): ?>
                        <tr>
                            <td><strong><?= substr($b['jam_mulai'], 0, 5) ?> — <?= substr($b['jam_selesai'], 0, 5) ?></strong></td>
                            <td><?= e($b['nama_pelanggan']) ?><br><small style="color:var(--text-muted);"><?= e($b['no_hp']) ?></small></td>
                            <td><?= e($b['nama_paket']) ?></td>
                            <td><?= e($b['nama_ruangan'] ?? 'Belum ditentukan') ?></td>
                            <td><?= statusBadge($b['status']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
        </div>

        <!-- Upcoming Sessions -->
        <div class="admin-card">
            <div class="admin-card-header">
                <h3><i class="ph ph-calendar" style="color:var(--info);margin-right:8px;"></i>Jadwal Mendatang</h3>
            </div>
            <div class="admin-card-body" style="padding:0;">
                <?php if (empty($upcomingBookings)): ?>
                <div class="empty-state">
                    <div class="icon"><i class="ph ph-calendar-blank"></i></div>
                    <h3>Belum ada jadwal mendatang</h3>
                </div>
                <?php else: ?>
                <table class="data-table">
                    <thead>
                        <tr><th>Tanggal</th><th>Waktu</th><th>Pelanggan</th><th>Paket</th><th>Studio</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($upcomingBookings as $b): ?>
                        <tr>
                            <td><?= date('d M Y', strtotime($b['tanggal_booking'])) ?></td>
                            <td><?= substr($b['jam_mulai'], 0, 5) ?> — <?= substr($b['jam_selesai'], 0, 5) ?></td>
                            <td><?= e($b['nama_pelanggan']) ?></td>
                            <td><?= e($b['nama_paket']) ?></td>
                            <td><?= e($b['nama_ruangan'] ?? 'TBA') ?></td>
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
