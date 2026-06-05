<?php
/**
 * Crafted Studio — Konfirmasi Pemesanan (Public, No Login)
 * Step 2: Input customer details and confirm
 */
require_once __DIR__ . '/includes/functions.php';

// Get booking data from URL params
$tanggal    = $_GET['tanggal'] ?? '';
$jamMulai   = $_GET['jam_mulai'] ?? '';
$jamSelesai = $_GET['jam_selesai'] ?? '';
$paketId    = (int)($_GET['paket'] ?? 0);

// Validate we have required data
if (!$tanggal || !$jamMulai || !$jamSelesai || !$paketId) {
    header('Location: booking.php');
    exit;
}

$paket = getPaketById($paketId);
if (!$paket) {
    header('Location: booking.php');
    exit;
}

// Handle form submission
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama  = trim($_POST['nama'] ?? '');
    $noHp  = trim($_POST['no_hp'] ?? '');
    $email = trim($_POST['email'] ?? '');

    // Validate
    if (empty($nama)) $errors[] = 'Nama lengkap wajib diisi';
    if (empty($noHp)) $errors[] = 'Nomor telepon wajib diisi';
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email tidak valid';

    if (empty($errors)) {
        $db = getDB();

        try {
            $db->beginTransaction();

            // Insert or find pelanggan
            $stmt = $db->prepare("SELECT id_pelanggan FROM pelanggan WHERE email = ? LIMIT 1");
            $stmt->execute([$email]);
            $existing = $stmt->fetch();

            if ($existing) {
                $pelangganId = $existing['id_pelanggan'];
                // Update name/phone if changed
                $db->prepare("UPDATE pelanggan SET nama = ?, no_hp = ? WHERE id_pelanggan = ?")
                   ->execute([$nama, $noHp, $pelangganId]);
            } else {
                $stmt = $db->prepare("INSERT INTO pelanggan (nama, no_hp, email) VALUES (?, ?, ?)");
                $stmt->execute([$nama, $noHp, $email]);
                $pelangganId = $db->lastInsertId();
            }

            // Insert booking (fotografer & studio assigned by admin later)
            $stmt = $db->prepare(
                "INSERT INTO booking (id_pelanggan, id_paket, tanggal_booking, jam_mulai, jam_selesai, total_harga, status)
                 VALUES (?, ?, ?, ?, ?, ?, 'menunggu')"
            );
            $stmt->execute([
                $pelangganId,
                $paketId,
                $tanggal,
                $jamMulai . ':00',
                $jamSelesai . ':00',
                $paket['harga']
            ]);
            $bookingId = $db->lastInsertId();

            $db->commit();

            // Redirect to success page
            header('Location: sukses.php?id=' . $bookingId);
            exit;

        } catch (Exception $e) {
            $db->rollBack();
            $errors[] = 'Terjadi kesalahan. Silakan coba lagi.';
        }
    }
}

// Format date for display
$dateObj = new DateTime($tanggal);
$days = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
$months = ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
$formattedDate = $days[$dateObj->format('w')] . ', ' . $dateObj->format('j') . ' ' . $months[(int)$dateObj->format('n')] . ' ' . $dateObj->format('Y');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Konfirmasi Pemesanan — Crafted Studio</title>
    <link rel="stylesheet" href="assets/css/public.css">
    <link rel="stylesheet" href="https://unpkg.com/@phosphor-icons/web@2.0.3/src/regular/style.css">
</head>
<body>

<div class="bg-grid"></div>
<div class="bg-orb bg-orb-1"></div>

<!-- Navbar -->
<nav class="navbar scrolled">
    <div class="container">
        <a href="/crafted-studio/" class="nav-brand">Crafted <span>Studio</span></a>
        <ul class="nav-links" id="navLinks">
            <li><a href="/crafted-studio/">Beranda</a></li>
            <li><a href="booking.php">Booking</a></li>
        </ul>
        <button class="nav-toggle" onclick="document.getElementById('navLinks').classList.toggle('open')">
            <span></span><span></span><span></span>
        </button>
    </div>
</nav>

<main class="booking-page">
    <div class="container">
        <h1 class="fade-up">Konfirmasi Pemesanan</h1>
        <p class="subtitle fade-up delay-1">Lengkapi data diri dan periksa detail pemesanan Anda</p>

        <!-- Steps -->
        <div class="booking-steps fade-up delay-1">
            <div class="step-indicator done">
                <span class="num">✓</span> Jadwal & Paket
            </div>
            <div class="step-indicator active">
                <span class="num">2</span> Konfirmasi
            </div>
            <div class="step-indicator">
                <span class="num">3</span> Selesai
            </div>
        </div>

        <?php if (!empty($errors)): ?>
        <div class="flash flash-error fade-up">
            <i class="ph ph-warning-circle"></i>
            <?= implode('<br>', array_map('e', $errors)) ?>
        </div>
        <?php endif; ?>

        <form method="POST" class="confirm-grid fade-up delay-2">
            <!-- Left: Customer Form -->
            <div class="glass-card">
                <h3 style="margin-bottom:24px;font-size:1rem;">
                    <i class="ph ph-user" style="color:var(--accent);margin-right:8px;"></i>
                    Data Pemesan
                </h3>

                <div class="form-group">
                    <label for="nama">Nama Lengkap *</label>
                    <input type="text" class="form-control" id="nama" name="nama"
                           placeholder="Masukkan nama lengkap"
                           value="<?= e($_POST['nama'] ?? '') ?>" required>
                </div>

                <div class="form-group">
                    <label for="no_hp">Nomor Telepon *</label>
                    <input type="tel" class="form-control" id="no_hp" name="no_hp"
                           placeholder="08xxxxxxxxxx"
                           value="<?= e($_POST['no_hp'] ?? '') ?>" required>
                </div>

                <div class="form-group">
                    <label for="email">Alamat Email *</label>
                    <input type="email" class="form-control" id="email" name="email"
                           placeholder="email@contoh.com"
                           value="<?= e($_POST['email'] ?? '') ?>" required>
                </div>
            </div>

            <!-- Right: Summary + Confirm -->
            <div class="glass-card">
                <h3 style="margin-bottom:24px;font-size:1rem;">
                    <i class="ph ph-receipt" style="color:var(--accent);margin-right:8px;"></i>
                    Detail Pemesanan
                </h3>

                <div class="summary-item">
                    <span class="label">Tanggal</span>
                    <span class="value"><?= e($formattedDate) ?></span>
                </div>
                <div class="summary-item">
                    <span class="label">Waktu</span>
                    <span class="value"><?= e($jamMulai) ?> — <?= e($jamSelesai) ?> WIB</span>
                </div>
                <div class="summary-item">
                    <span class="label">Paket</span>
                    <span class="value"><?= e($paket['nama_paket']) ?></span>
                </div>
                <div class="summary-item">
                    <span class="label">Durasi</span>
                    <span class="value"><?= $paket['durasi_jam'] ?> jam</span>
                </div>
                <div class="summary-item">
                    <span class="label">Jumlah Foto</span>
                    <span class="value"><?= $paket['jumlah_foto'] ?> foto</span>
                </div>

                <div class="summary-total">
                    <span class="label">Total Harga</span>
                    <span class="value"><?= formatRupiah($paket['harga']) ?></span>
                </div>

                <p style="color:var(--text-muted);font-size:0.8rem;margin-top:16px;line-height:1.6;">
                    <i class="ph ph-info" style="margin-right:4px;"></i>
                    Pembayaran dapat dilakukan di studio pada hari sesi foto. Admin akan mengkonfirmasi jadwal Anda.
                </p>

                <button type="submit" class="btn btn-primary btn-block btn-lg" style="margin-top:24px;">
                    <i class="ph ph-check-circle"></i>
                    Konfirmasi Pemesanan
                </button>

                <a href="booking.php?paket=<?= $paketId ?>" style="display:block;text-align:center;margin-top:12px;font-size:0.85rem;color:var(--text-muted);">
                    ← Kembali ubah jadwal
                </a>
            </div>
        </form>
    </div>
</main>

</body>
</html>
