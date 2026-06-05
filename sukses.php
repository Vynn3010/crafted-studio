<?php
/**
 * Crafted Studio — Sukses Booking (Public)
 * Step 3: Show booking confirmation
 */
require_once __DIR__ . '/includes/functions.php';

$bookingId = (int)($_GET['id'] ?? 0);
if (!$bookingId) {
    header('Location: /crafted-studio/');
    exit;
}

$db = getDB();
$stmt = $db->prepare(
    "SELECT b.*, p.nama AS nama_pelanggan, p.email AS email_pelanggan, p.no_hp,
            pf.nama_paket, pf.jumlah_foto, pf.durasi_jam
     FROM booking b
     JOIN pelanggan p ON b.id_pelanggan = p.id_pelanggan
     JOIN paket_foto pf ON b.id_paket = pf.id_paket
     WHERE b.id_booking = ?"
);
$stmt->execute([$bookingId]);
$booking = $stmt->fetch();

if (!$booking) {
    header('Location: /crafted-studio/');
    exit;
}

// Format date
$dateObj = new DateTime($booking['tanggal_booking']);
$days = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
$months = ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
$formattedDate = $days[$dateObj->format('w')] . ', ' . $dateObj->format('j') . ' ' . $months[(int)$dateObj->format('n')] . ' ' . $dateObj->format('Y');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Berhasil — Crafted Studio</title>
    <link rel="stylesheet" href="assets/css/public.css">
    <link rel="stylesheet" href="https://unpkg.com/@phosphor-icons/web@2.0.3/src/regular/style.css">
</head>
<body>

<div class="bg-grid"></div>
<div class="bg-orb bg-orb-1"></div>
<div class="bg-orb bg-orb-2"></div>

<main class="success-page">
    <div class="success-box fade-up">
        <div class="success-icon">
            <i class="ph ph-check"></i>
        </div>

        <h1>Pemesanan Berhasil!</h1>
        <p>Terima kasih, <?= e($booking['nama_pelanggan']) ?>. Pemesanan Anda telah kami terima dan sedang menunggu konfirmasi admin.</p>

        <div class="booking-ref">
            #CS-<?= str_pad($bookingId, 4, '0', STR_PAD_LEFT) ?>
        </div>

        <div class="success-details">
            <div class="row">
                <span class="l">Nama</span>
                <span class="r"><?= e($booking['nama_pelanggan']) ?></span>
            </div>
            <div class="row">
                <span class="l">Email</span>
                <span class="r"><?= e($booking['email_pelanggan']) ?></span>
            </div>
            <div class="row">
                <span class="l">Telepon</span>
                <span class="r"><?= e($booking['no_hp']) ?></span>
            </div>
            <div class="row">
                <span class="l">Tanggal</span>
                <span class="r"><?= e($formattedDate) ?></span>
            </div>
            <div class="row">
                <span class="l">Waktu</span>
                <span class="r"><?= substr($booking['jam_mulai'], 0, 5) ?> — <?= substr($booking['jam_selesai'], 0, 5) ?> WIB</span>
            </div>
            <div class="row">
                <span class="l">Paket</span>
                <span class="r"><?= e($booking['nama_paket']) ?></span>
            </div>
            <div class="row">
                <span class="l">Total Harga</span>
                <span class="r" style="color:var(--accent);font-size:1.1rem;"><?= formatRupiah($booking['total_harga']) ?></span>
            </div>
            <div class="row">
                <span class="l">Status</span>
                <span class="r">
                    <span class="badge badge-warning" style="font-size:0.8rem;">Menunggu Konfirmasi</span>
                </span>
            </div>
        </div>

        <p style="color:var(--text-muted);font-size:0.85rem;margin-bottom:32px;line-height:1.7;">
            <i class="ph ph-info" style="margin-right:4px;"></i>
            Simpan nomor booking Anda. Admin akan mengkonfirmasi jadwal dan menghubungi Anda melalui email atau telepon.
        </p>

        <a href="/crafted-studio/" class="btn btn-primary btn-lg">
            <i class="ph ph-house"></i>
            Kembali ke Beranda
        </a>
    </div>
</main>

</body>
</html>
