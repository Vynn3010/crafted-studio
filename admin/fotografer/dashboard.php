<?php
/**
 * Crafted Studio — Fotografer Dashboard
 */
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

requireRole('fotografer');

$db = getDB();
$flash = getFlash();

// Handle POST: fotografer marks photo session as done
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action    = $_POST['action'] ?? '';
    $bookingId = (int)($_POST['booking_id'] ?? 0);

    if ($action === 'selesai_foto' && $bookingId) {
        // Verify booking belongs to this fotografer and is dikonfirmasi
        $stmt = $db->prepare("SELECT id_booking FROM booking WHERE id_booking = ? AND id_fotografer = ? AND status = 'dikonfirmasi'");
        $stmt->execute([$bookingId, $_SESSION['user_id']]);
        if ($stmt->fetch()) {
            $editorId = getAutoAssignEditor();
            if ($editorId) {
                $db->prepare("UPDATE booking SET status = 'diproses' WHERE id_booking = ?")
                   ->execute([$bookingId]);
                createEditingTask($bookingId, $editorId);
                setFlash('success', 'Sesi foto #CS-' . str_pad($bookingId, 4, '0', STR_PAD_LEFT) . ' ditandai selesai. Tugas editing diteruskan ke editor.');
            } else {
                setFlash('error', 'Tidak ada editor aktif saat ini. Hubungi admin.');
            }
        } else {
            setFlash('error', 'Booking tidak valid atau bukan milik Anda.');
        }
        header('Location: dashboard.php');
        exit;
    }
}

$today = date('Y-m-d');
$bookings = getBookingsByFotografer($_SESSION['user_id'], '');

$todayBookings    = array_filter($bookings, fn($b) => $b['tanggal_booking'] === $today);
$upcomingBookings = array_filter($bookings, fn($b) => $b['tanggal_booking'] > $today);
$totalDikonfirmasi = count(array_filter($bookings, fn($b) => $b['status'] === 'dikonfirmasi'));
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
                    <?php if ($totalDikonfirmasi > 0): ?>
                    <span class="badge-nav"><?= $totalDikonfirmasi ?></span>
                    <?php endif; ?>
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
            <a href="../logout.php?role=fotografer" class="nav-item" style="margin-top:12px;color:var(--danger);">
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

        <?php if ($flash): ?>
        <div class="flash flash-<?= $flash['type'] ?>">
            <i class="ph ph-<?= $flash['type'] === 'success' ? 'check-circle' : 'warning-circle' ?>"></i>
            <?= e($flash['message']) ?>
        </div>
        <?php endif; ?>

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
                        <tr><th>Waktu</th><th>Pelanggan</th><th>Paket</th><th>Studio</th><th>Status</th><th>Aksi</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($todayBookings as $b): ?>
                        <tr>
                            <td><strong><?= substr($b['jam_mulai'], 0, 5) ?> — <?= substr($b['jam_selesai'], 0, 5) ?></strong></td>
                            <td><?= e($b['nama_pelanggan']) ?><br><small style="color:var(--text-muted);"><?= e($b['no_hp']) ?></small></td>
                            <td><?= e($b['nama_paket']) ?></td>
                            <td><?= e($b['nama_ruangan'] ?? 'Belum ditentukan') ?></td>
                            <td><?= statusBadge($b['status']) ?></td>
                            <td>
                                <?php if ($b['status'] === 'dikonfirmasi'): ?>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="action" value="selesai_foto">
                                    <input type="hidden" name="booking_id" value="<?= $b['id_booking'] ?>">
                                    <button type="submit" class="btn btn-success btn-sm">
                                        <i class="ph ph-check-circle"></i> Selesai Foto
                                    </button>
                                </form>
                                <?php elseif ($b['status'] === 'diproses'): ?>
                                <span class="badge badge-primary"><i class="ph ph-paint-brush"></i> Di-edit</span>
                                <?php elseif ($b['status'] === 'selesai'): ?>
                                <span class="badge badge-success">✓ Selesai</span>
                                <?php else: ?>
                                <span class="badge badge-warning">Menunggu</span>
                                <?php endif; ?>
                            </td>
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
                        <tr><th>Tanggal</th><th>Waktu</th><th>Pelanggan</th><th>Paket</th><th>Studio</th><th>Status</th><th>Aksi</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($upcomingBookings as $b): ?>
                        <tr>
                            <td><?= date('d M Y', strtotime($b['tanggal_booking'])) ?></td>
                            <td><?= substr($b['jam_mulai'], 0, 5) ?> — <?= substr($b['jam_selesai'], 0, 5) ?></td>
                            <td><?= e($b['nama_pelanggan']) ?></td>
                            <td><?= e($b['nama_paket']) ?></td>
                            <td><?= e($b['nama_ruangan'] ?? 'TBA') ?></td>
                            <td><?= statusBadge($b['status']) ?></td>
                            <td>
                                <?php if ($b['status'] === 'dikonfirmasi'): ?>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="action" value="selesai_foto">
                                    <input type="hidden" name="booking_id" value="<?= $b['id_booking'] ?>">
                                    <button type="submit" class="btn btn-success btn-sm">
                                        <i class="ph ph-check-circle"></i> Selesai Foto
                                    </button>
                                </form>
                                <?php elseif ($b['status'] === 'diproses'): ?>
                                <span class="badge badge-primary"><i class="ph ph-paint-brush"></i> Di-edit</span>
                                <?php elseif ($b['status'] === 'selesai'): ?>
                                <span class="badge badge-success">✓ Selesai</span>
                                <?php else: ?>
                                <span class="badge badge-warning">Menunggu</span>
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
