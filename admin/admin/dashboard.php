<?php
/**
 * Crafted Studio — Admin Dashboard
 * Manage bookings, validate conflicts, assign resources
 */
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

requireRole('admin', 'staff');

$db = getDB();
$today = date('Y-m-d');
$flash = getFlash();

// Handle booking actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action    = $_POST['action'] ?? '';
    $bookingId = (int)($_POST['booking_id'] ?? 0);

    if ($bookingId && $action) {
        switch ($action) {
            case 'konfirmasi':
                $fotograferId = (int)($_POST['id_fotografer'] ?? 0);
                $studioId     = (int)($_POST['id_studio'] ?? 0);

                if (!$fotograferId || !$studioId) {
                    setFlash('error', 'Pilih fotografer dan studio terlebih dahulu.');
                    break;
                }

                // Get booking details for conflict check
                $stmt = $db->prepare("SELECT tanggal_booking, jam_mulai, jam_selesai FROM booking WHERE id_booking = ?");
                $stmt->execute([$bookingId]);
                $bk = $stmt->fetch();

                // Check for conflicts
                $studioConflict = checkStudioConflict($studioId, $bk['tanggal_booking'], $bk['jam_mulai'], $bk['jam_selesai'], $bookingId);
                $fotoConflict = checkFotograferConflict($fotograferId, $bk['tanggal_booking'], $bk['jam_mulai'], $bk['jam_selesai'], $bookingId);

                if ($studioConflict) {
                    setFlash('error', 'BENTROK! Studio sudah terpakai di jam tersebut.');
                    break;
                }
                if ($fotoConflict) {
                    setFlash('error', 'BENTROK! Fotografer sudah ada jadwal di jam tersebut.');
                    break;
                }

                $db->prepare("UPDATE booking SET status = 'dikonfirmasi', id_fotografer = ?, id_studio = ? WHERE id_booking = ?")
                   ->execute([$fotograferId, $studioId, $bookingId]);
                setFlash('success', 'Booking #CS-' . str_pad($bookingId, 4, '0', STR_PAD_LEFT) . ' berhasil dikonfirmasi.');
                break;

            case 'batalkan':
                $db->prepare("UPDATE booking SET status = 'dibatalkan' WHERE id_booking = ?")
                   ->execute([$bookingId]);
                setFlash('success', 'Booking berhasil dibatalkan.');
                break;

            case 'selesai':
                $db->prepare("UPDATE booking SET status = 'selesai' WHERE id_booking = ?")
                   ->execute([$bookingId]);
                setFlash('success', 'Booking ditandai selesai.');
                break;
        }
        header('Location: dashboard.php');
        exit;
    }
}

// Filters
$statusFilter = $_GET['status'] ?? '';
$dateFilter   = $_GET['date'] ?? '';

// Stats
$totalBooking   = countBookings();
$menunggu       = countBookings('menunggu');
$dikonfirmasi   = countBookings('dikonfirmasi');
$bookingHariIni = countBookings('', $today);

// Get bookings
$bookings   = getAllBookings($statusFilter, $dateFilter);
$fotografer = getFotografer();
$studios    = getStudios();

// Check conflicts for each booking
function detectConflicts(array $bookings): array {
    $conflicts = [];
    $count = count($bookings);
    for ($i = 0; $i < $count; $i++) {
        for ($j = $i + 1; $j < $count; $j++) {
            $a = $bookings[$i];
            $b = $bookings[$j];
            if ($a['status'] === 'dibatalkan' || $b['status'] === 'dibatalkan') continue;
            if ($a['tanggal_booking'] !== $b['tanggal_booking']) continue;

            $timeOverlap = ($a['jam_mulai'] < $b['jam_selesai']) && ($a['jam_selesai'] > $b['jam_mulai']);

            if ($timeOverlap) {
                // Same studio?
                if ($a['id_studio'] && $b['id_studio'] && $a['id_studio'] === $b['id_studio']) {
                    $conflicts[$a['id_booking']][] = 'Studio bentrok dengan #CS-' . str_pad($b['id_booking'], 4, '0', STR_PAD_LEFT);
                    $conflicts[$b['id_booking']][] = 'Studio bentrok dengan #CS-' . str_pad($a['id_booking'], 4, '0', STR_PAD_LEFT);
                }
                // Same fotografer?
                if ($a['id_fotografer'] && $b['id_fotografer'] && $a['id_fotografer'] === $b['id_fotografer']) {
                    $conflicts[$a['id_booking']][] = 'Fotografer bentrok dengan #CS-' . str_pad($b['id_booking'], 4, '0', STR_PAD_LEFT);
                    $conflicts[$b['id_booking']][] = 'Fotografer bentrok dengan #CS-' . str_pad($a['id_booking'], 4, '0', STR_PAD_LEFT);
                }
            }
        }
    }
    return $conflicts;
}
$conflicts = detectConflicts($bookings);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin — Crafted Studio</title>
    <link rel="stylesheet" href="../../assets/css/admin.css">
    <link rel="stylesheet" href="https://unpkg.com/@phosphor-icons/web@2.0.3/src/regular/style.css">
</head>
<body>

<div class="admin-layout">
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-brand">
            <h2>Crafted <span>Studio</span></h2>
            <p>Dashboard Admin</p>
        </div>

        <nav class="sidebar-nav">
            <div class="nav-section">
                <div class="nav-section-title">Menu Utama</div>
                <a href="dashboard.php" class="nav-item active">
                    <span class="icon"><i class="ph ph-squares-four"></i></span>
                    Dashboard
                    <?php if ($menunggu > 0): ?>
                    <span class="badge-nav"><?= $menunggu ?></span>
                    <?php endif; ?>
                </a>
            </div>
            <div class="nav-section">
                <div class="nav-section-title">Kelola</div>
                <a href="bookings.php" class="nav-item">
                    <span class="icon"><i class="ph ph-calendar-check"></i></span>
                    Semua Booking
                </a>
                <a href="paket.php" class="nav-item">
                    <span class="icon"><i class="ph ph-package"></i></span>
                    Paket Foto
                </a>
                <a href="#" class="nav-item">
                    <span class="icon"><i class="ph ph-buildings"></i></span>
                    Studio
                </a>
                <a href="#" class="nav-item">
                    <span class="icon"><i class="ph ph-users"></i></span>
                    Staf
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
            <a href="../logout.php?role=admin" class="nav-item" style="margin-top:12px;color:var(--danger);">
                <span class="icon"><i class="ph ph-sign-out"></i></span>
                Logout
            </a>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="admin-main">
        <div class="admin-topbar">
            <h1>Dashboard</h1>
            <div class="topbar-actions">
                <span style="font-size:0.85rem;color:var(--text-muted);">
                    <i class="ph ph-calendar"></i>
                    <?= date('d M Y') ?>
                </span>
            </div>
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
                <div class="stat-icon yellow"><i class="ph ph-calendar"></i></div>
                <div class="stat-value"><?= $bookingHariIni ?></div>
                <div class="stat-label">Booking Hari Ini</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon red"><i class="ph ph-clock"></i></div>
                <div class="stat-value"><?= $menunggu ?></div>
                <div class="stat-label">Menunggu Konfirmasi</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon blue"><i class="ph ph-check-circle"></i></div>
                <div class="stat-value"><?= $dikonfirmasi ?></div>
                <div class="stat-label">Dikonfirmasi</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon green"><i class="ph ph-chart-line-up"></i></div>
                <div class="stat-value"><?= $totalBooking ?></div>
                <div class="stat-label">Total Booking</div>
            </div>
        </div>

        <!-- Booking Table -->
        <div class="admin-card">
            <div class="admin-card-header">
                <h3>
                    <i class="ph ph-list-checks" style="color:var(--accent);margin-right:8px;"></i>
                    Daftar Booking
                </h3>
                <div class="filter-bar" style="margin-bottom:0;">
                    <form method="GET" class="form-inline">
                        <select name="status" class="form-control" onchange="this.form.submit()">
                            <option value="">Semua Status</option>
                            <option value="menunggu" <?= $statusFilter === 'menunggu' ? 'selected' : '' ?>>Menunggu</option>
                            <option value="dikonfirmasi" <?= $statusFilter === 'dikonfirmasi' ? 'selected' : '' ?>>Dikonfirmasi</option>
                            <option value="diproses" <?= $statusFilter === 'diproses' ? 'selected' : '' ?>>Diproses</option>
                            <option value="selesai" <?= $statusFilter === 'selesai' ? 'selected' : '' ?>>Selesai</option>
                            <option value="dibatalkan" <?= $statusFilter === 'dibatalkan' ? 'selected' : '' ?>>Dibatalkan</option>
                        </select>
                        <input type="date" name="date" class="form-control" value="<?= e($dateFilter) ?>" onchange="this.form.submit()">
                    </form>
                </div>
            </div>
            <div class="admin-card-body" style="padding:0;overflow-x:auto;">
                <?php if (empty($bookings)): ?>
                <div class="empty-state">
                    <div class="icon"><i class="ph ph-calendar-x"></i></div>
                    <h3>Belum ada booking</h3>
                    <p>Booking baru dari pelanggan akan muncul di sini</p>
                </div>
                <?php else: ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Pelanggan</th>
                            <th>Tanggal</th>
                            <th>Waktu</th>
                            <th>Paket</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bookings as $b): ?>
                        <tr>
                            <td>
                                <strong style="color:var(--accent);">#CS-<?= str_pad($b['id_booking'], 4, '0', STR_PAD_LEFT) ?></strong>
                                <?php if (isset($conflicts[$b['id_booking']])): ?>
                                <br>
                                <?php foreach ($conflicts[$b['id_booking']] as $c): ?>
                                <span class="badge badge-conflict" title="<?= e($c) ?>">
                                    <i class="ph ph-warning"></i> BENTROK
                                </span>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong><?= e($b['nama_pelanggan']) ?></strong><br>
                                <small style="color:var(--text-muted);"><?= e($b['no_hp']) ?></small>
                            </td>
                            <td><?= date('d M Y', strtotime($b['tanggal_booking'])) ?></td>
                            <td><?= substr($b['jam_mulai'], 0, 5) ?> — <?= substr($b['jam_selesai'], 0, 5) ?></td>
                            <td><?= e($b['nama_paket']) ?></td>
                            <td><strong><?= formatRupiah($b['total_harga']) ?></strong></td>
                            <td><?= statusBadge($b['status']) ?></td>
                            <td>
                                <?php if ($b['status'] === 'menunggu'): ?>
                                <button class="btn btn-success btn-sm" onclick="openConfirmModal(<?= $b['id_booking'] ?>)">
                                    <i class="ph ph-check"></i> Konfirmasi
                                </button>
                                <form method="POST" style="display:inline;" onsubmit="return confirm('Batalkan booking ini?')">
                                    <input type="hidden" name="action" value="batalkan">
                                    <input type="hidden" name="booking_id" value="<?= $b['id_booking'] ?>">
                                    <button type="submit" class="btn btn-danger btn-sm">
                                        <i class="ph ph-x"></i>
                                    </button>
                                </form>
                                <?php elseif ($b['status'] === 'dikonfirmasi'): ?>
                                <span class="badge badge-info"><i class="ph ph-camera"></i> Menunggu Fotografer</span>
                                <?php elseif ($b['status'] === 'diproses'): ?>
                                    <?php if (isEditingComplete($b['id_booking'])): ?>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="action" value="selesai">
                                        <input type="hidden" name="booking_id" value="<?= $b['id_booking'] ?>">
                                        <button type="submit" class="btn btn-primary btn-sm">
                                            <i class="ph ph-check-circle"></i> Validasi Selesai
                                        </button>
                                    </form>
                                    <?php else: ?>
                                    <span class="badge badge-primary"><i class="ph ph-paint-brush"></i> Menunggu Editor</span>
                                    <?php endif; ?>
                                <?php elseif ($b['status'] === 'dibatalkan'): ?>
                                <span style="color:var(--text-muted);font-size:0.8rem;">—</span>
                                <?php elseif ($b['status'] === 'selesai'): ?>
                                <span class="badge badge-success">✓ Selesai</span>
                                <?php else: ?>
                                <span class="badge badge-success">✓</span>
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

<!-- Confirm Modal -->
<div class="modal-overlay" id="confirmModal">
    <div class="modal">
        <div class="modal-header">
            <h3><i class="ph ph-check-circle" style="color:var(--success);margin-right:8px;"></i>Konfirmasi Booking</h3>
            <button class="modal-close" onclick="closeModal()">&times;</button>
        </div>
        <form method="POST">
            <div class="modal-body">
                <input type="hidden" name="action" value="konfirmasi">
                <input type="hidden" name="booking_id" id="modalBookingId">

                <p style="color:var(--text-secondary);font-size:0.88rem;margin-bottom:20px;">
                    Pilih fotografer dan studio untuk booking ini:
                </p>

                <div class="form-group">
                    <label>Fotografer</label>
                    <select name="id_fotografer" class="form-control" required>
                        <option value="">— Pilih Fotografer —</option>
                        <?php foreach ($fotografer as $f): ?>
                        <option value="<?= $f['id_fotografer'] ?>">
                            <?= e($f['nama']) ?> (<?= e($f['spesialisasi']) ?>)
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Studio / Ruangan</label>
                    <select name="id_studio" class="form-control" required>
                        <option value="">— Pilih Studio —</option>
                        <?php foreach ($studios as $s): ?>
                        <option value="<?= $s['id_studio'] ?>">
                            <?= e($s['nama_ruangan']) ?> (<?= e($s['background']) ?>)
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal()">Batal</button>
                <button type="submit" class="btn btn-primary">
                    <i class="ph ph-check"></i> Konfirmasi
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openConfirmModal(bookingId) {
    document.getElementById('modalBookingId').value = bookingId;
    document.getElementById('confirmModal').classList.add('active');
}
function closeModal() {
    document.getElementById('confirmModal').classList.remove('active');
}
// Close on overlay click
document.getElementById('confirmModal').addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});
</script>

</body>
</html>
