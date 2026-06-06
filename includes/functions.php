<?php
/**
 * Crafted Studio — Helper Functions
 */

require_once __DIR__ . '/db.php';

/**
 * Format Rupiah
 */
function formatRupiah(float $amount): string {
    return 'Rp ' . number_format($amount, 0, ',', '.');
}

/**
 * Get all active paket foto
 */
function getPaketFoto(): array {
    $db = getDB();
    $stmt = $db->query("SELECT * FROM paket_foto ORDER BY harga ASC");
    return $stmt->fetchAll();
}

/**
 * Get single paket by ID
 */
function getPaketById(int $id): array|false {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM paket_foto WHERE id_paket = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

/**
 * Get available studios
 */
function getStudios(): array {
    $db = getDB();
    $stmt = $db->query("SELECT * FROM studio WHERE status = 'tersedia' ORDER BY nama_ruangan ASC");
    return $stmt->fetchAll();
}

/**
 * Get active fotografer
 */
function getFotografer(): array {
    $db = getDB();
    $stmt = $db->query("SELECT * FROM fotografer WHERE status = 'aktif' ORDER BY nama ASC");
    return $stmt->fetchAll();
}

/**
 * Check schedule conflict for a studio on a given date/time
 */
function checkStudioConflict(int $studioId, string $tanggal, string $jamMulai, string $jamSelesai, ?int $excludeBookingId = null): bool {
    $db = getDB();
    $sql = "SELECT COUNT(*) FROM booking 
            WHERE id_studio = ? 
            AND tanggal_booking = ? 
            AND status NOT IN ('dibatalkan')
            AND jam_mulai < ? 
            AND jam_selesai > ?";
    $params = [$studioId, $tanggal, $jamSelesai, $jamMulai];
    
    if ($excludeBookingId) {
        $sql .= " AND id_booking != ?";
        $params[] = $excludeBookingId;
    }
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchColumn() > 0;
}

/**
 * Check schedule conflict for a fotografer on a given date/time
 */
function checkFotograferConflict(int $fotograferId, string $tanggal, string $jamMulai, string $jamSelesai, ?int $excludeBookingId = null): bool {
    $db = getDB();
    $sql = "SELECT COUNT(*) FROM booking 
            WHERE id_fotografer = ? 
            AND tanggal_booking = ? 
            AND status NOT IN ('dibatalkan')
            AND jam_mulai < ? 
            AND jam_selesai > ?";
    $params = [$fotograferId, $tanggal, $jamSelesai, $jamMulai];
    
    if ($excludeBookingId) {
        $sql .= " AND id_booking != ?";
        $params[] = $excludeBookingId;
    }
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchColumn() > 0;
}

/**
 * Get all bookings with related data
 */
function getAllBookings(string $statusFilter = '', string $dateFilter = ''): array {
    $db = getDB();
    $sql = "SELECT b.*, p.nama AS nama_pelanggan, p.no_hp, p.email AS email_pelanggan,
                   pf.nama_paket, pf.harga,
                   f.nama AS nama_fotografer,
                   s.nama_ruangan
            FROM booking b
            JOIN pelanggan p ON b.id_pelanggan = p.id_pelanggan
            JOIN paket_foto pf ON b.id_paket = pf.id_paket
            LEFT JOIN fotografer f ON b.id_fotografer = f.id_fotografer
            LEFT JOIN studio s ON b.id_studio = s.id_studio
            WHERE 1=1";
    $params = [];

    if ($statusFilter) {
        $sql .= " AND b.status = ?";
        $params[] = $statusFilter;
    }
    if ($dateFilter) {
        $sql .= " AND b.tanggal_booking = ?";
        $params[] = $dateFilter;
    }

    $sql .= " ORDER BY b.created_at DESC";
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

/**
 * Get bookings for a specific fotografer
 */
function getBookingsByFotografer(int $fotograferId, string $dateFilter = ''): array {
    $db = getDB();
    $sql = "SELECT b.*, p.nama AS nama_pelanggan, p.no_hp,
                   pf.nama_paket, s.nama_ruangan
            FROM booking b
            JOIN pelanggan p ON b.id_pelanggan = p.id_pelanggan
            JOIN paket_foto pf ON b.id_paket = pf.id_paket
            LEFT JOIN studio s ON b.id_studio = s.id_studio
            WHERE b.id_fotografer = ? AND b.status NOT IN ('dibatalkan')";
    $params = [$fotograferId];

    if ($dateFilter) {
        $sql .= " AND b.tanggal_booking = ?";
        $params[] = $dateFilter;
    }

    $sql .= " ORDER BY b.tanggal_booking ASC, b.jam_mulai ASC";
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

/**
 * Get editing tasks for an editor
 */
function getEditingByEditor(int $editorId): array {
    $db = getDB();
    $stmt = $db->prepare(
        "SELECT ef.*, b.tanggal_booking, p.nama AS nama_pelanggan, pf.nama_paket
         FROM editing_foto ef
         JOIN booking b ON ef.id_booking = b.id_booking
         JOIN pelanggan p ON b.id_pelanggan = p.id_pelanggan
         JOIN paket_foto pf ON b.id_paket = pf.id_paket
         WHERE ef.id_editor = ?
         ORDER BY ef.status ASC, b.tanggal_booking DESC"
    );
    $stmt->execute([$editorId]);
    return $stmt->fetchAll();
}

/**
 * Count bookings by status
 */
function countBookings(string $status = '', string $date = ''): int {
    $db = getDB();
    $sql = "SELECT COUNT(*) FROM booking WHERE 1=1";
    $params = [];
    if ($status) {
        $sql .= " AND status = ?";
        $params[] = $status;
    }
    if ($date) {
        $sql .= " AND tanggal_booking = ?";
        $params[] = $date;
    }
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    return (int) $stmt->fetchColumn();
}

/**
 * Get available time slots for a given date
 * Studio operates 09:00 - 20:00
 */
function getAvailableSlots(string $tanggal): array {
    $db = getDB();
    $allSlots = [];
    for ($h = 9; $h <= 19; $h++) {
        $allSlots[] = sprintf('%02d:00', $h);
    }

    // Get booked slots
    $stmt = $db->prepare(
        "SELECT jam_mulai, jam_selesai FROM booking 
         WHERE tanggal_booking = ? AND status NOT IN ('dibatalkan')
         ORDER BY jam_mulai ASC"
    );
    $stmt->execute([$tanggal]);
    $booked = $stmt->fetchAll();

    return $allSlots; // Return all slots, frontend will handle availability display
}

/**
 * Escape HTML output
 */
function e(string $str): string {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

/**
 * Flash message
 */
function setFlash(string $type, string $message): void {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash(): ?array {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Status badge HTML
 */
function statusBadge(string $status): string {
    $colors = [
        'menunggu'      => 'badge-warning',
        'dikonfirmasi'  => 'badge-info',
        'diproses'      => 'badge-primary',
        'selesai'       => 'badge-success',
        'dibatalkan'    => 'badge-danger',
        'editing'       => 'badge-primary',
        'revisi'        => 'badge-warning',
    ];
    $class = $colors[$status] ?? 'badge-secondary';
    return '<span class="badge ' . $class . '">' . ucfirst(e($status)) . '</span>';
}

/**
 * Auto-assign editor with fewest active tasks
 */
function getAutoAssignEditor(): int|false {
    $db = getDB();
    $stmt = $db->query(
        "SELECT e.id_editor
         FROM editor e
         LEFT JOIN editing_foto ef ON e.id_editor = ef.id_editor AND ef.status IN ('menunggu','editing')
         WHERE e.status = 'aktif'
         GROUP BY e.id_editor
         ORDER BY COUNT(ef.id_editing) ASC
         LIMIT 1"
    );
    $row = $stmt->fetch();
    return $row ? (int)$row['id_editor'] : false;
}

/**
 * Create editing task for a booking
 */
function createEditingTask(int $bookingId, int $editorId): void {
    $db = getDB();
    $db->prepare("INSERT INTO editing_foto (id_booking, id_editor, status) VALUES (?, ?, 'menunggu')")
       ->execute([$bookingId, $editorId]);
}

/**
 * Check if editing is complete for a booking
 */
function isEditingComplete(int $bookingId): bool {
    $db = getDB();
    $stmt = $db->prepare("SELECT status FROM editing_foto WHERE id_booking = ? ORDER BY id_editing DESC LIMIT 1");
    $stmt->execute([$bookingId]);
    $row = $stmt->fetch();
    return $row && $row['status'] === 'selesai';
}

/**
 * Count uploaded photos for a booking
 */
function getPhotoCountByBooking(int $bookingId): int {
    $db = getDB();
    $stmt = $db->prepare("SELECT COUNT(*) FROM galeri_foto WHERE id_booking = ?");
    $stmt->execute([$bookingId]);
    return (int)$stmt->fetchColumn();
}

