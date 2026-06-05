<?php
/**
 * Crafted Studio — Landing Page (Public)
 */
require_once __DIR__ . '/includes/functions.php';
$pakets = getPaketFoto();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crafted Studio — Abadikan Momen Terbaikmu</title>
    <meta name="description" content="Studio foto profesional dengan fotografer berpengalaman. Pesan sesi foto Anda sekarang tanpa perlu daftar akun.">
    <link rel="stylesheet" href="assets/css/public.css">
    <link rel="stylesheet" href="https://unpkg.com/@phosphor-icons/web@2.0.3/src/regular/style.css">
</head>
<body>

<!-- Background Decorations -->
<div class="bg-grid"></div>
<div class="bg-orb bg-orb-1"></div>
<div class="bg-orb bg-orb-2"></div>

<!-- Navbar -->
<nav class="navbar" id="navbar">
    <div class="container">
        <a href="/crafted-studio/" class="nav-brand">Crafted <span>Studio</span></a>
        <ul class="nav-links" id="navLinks">
            <li><a href="#beranda">Beranda</a></li>
            <li><a href="#paket">Paket Foto</a></li>
            <li><a href="#tentang">Tentang</a></li>
            <li><a href="booking.php" class="btn btn-primary btn-sm">Pesan Sekarang</a></li>
        </ul>
        <button class="nav-toggle" id="navToggle" onclick="document.getElementById('navLinks').classList.toggle('open')">
            <span></span><span></span><span></span>
        </button>
    </div>
</nav>

<!-- Hero Section -->
<section class="hero" id="beranda">
    <div class="container">
        <div class="hero-content fade-up">
            <div class="hero-badge">
                <span class="dot"></span>
                Booking Terbuka
            </div>
            <h1>
                Abadikan Momen<br>
                <span class="highlight">Terbaikmu</span>
            </h1>
            <p>
                Studio foto profesional dengan peralatan terkini dan fotografer berpengalaman.
                Pesan sesi foto Anda dalam hitungan menit — tanpa perlu daftar akun.
            </p>
            <div class="hero-actions">
                <a href="booking.php" class="btn btn-primary btn-lg">
                    <i class="ph ph-camera"></i>
                    Pesan Sesi Foto
                </a>
                <a href="#paket" class="btn btn-outline btn-lg">
                    <i class="ph ph-eye"></i>
                    Lihat Paket
                </a>
            </div>
            <div class="hero-stats fade-up delay-2">
                <div class="hero-stat">
                    <h3>500+</h3>
                    <p>Sesi Foto</p>
                </div>
                <div class="hero-stat">
                    <h3>3</h3>
                    <p>Studio Premium</p>
                </div>
                <div class="hero-stat">
                    <h3>98%</h3>
                    <p>Klien Puas</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Packages Section -->
<section class="section" id="paket">
    <div class="container">
        <div class="section-header fade-up">
            <span class="label">Pilihan Paket</span>
            <h2>Temukan Paket yang Sempurna</h2>
            <p>Setiap paket dirancang untuk menghadirkan pengalaman foto terbaik sesuai kebutuhanmu</p>
        </div>

        <div class="packages-grid">
            <?php foreach ($pakets as $i => $paket): ?>
            <div class="package-card fade-up delay-<?= min($i + 1, 4) ?> <?= $i === 2 ? 'featured' : '' ?>">
                <?php if ($i === 2): ?>
                    <span class="package-badge">Populer</span>
                <?php endif; ?>
                <h3><?= e($paket['nama_paket']) ?></h3>
                <div class="price">
                    <?= formatRupiah($paket['harga']) ?>
                    <small>/ sesi</small>
                </div>
                <ul class="features">
                    <li>
                        <span class="icon"><i class="ph ph-clock"></i></span>
                        Durasi <?= $paket['durasi_jam'] ?> jam
                    </li>
                    <li>
                        <span class="icon"><i class="ph ph-image"></i></span>
                        <?= $paket['jumlah_foto'] ?> foto edit
                    </li>
                    <li>
                        <span class="icon"><i class="ph ph-user"></i></span>
                        Fotografer profesional
                    </li>
                    <li>
                        <span class="icon"><i class="ph ph-sparkle"></i></span>
                        Studio premium
                    </li>
                </ul>
                <a href="booking.php?paket=<?= $paket['id_paket'] ?>" class="btn <?= $i === 2 ? 'btn-primary' : 'btn-outline' ?> btn-block">
                    Pilih Paket
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- About Section -->
<section class="section" id="tentang">
    <div class="container">
        <div class="section-header fade-up">
            <span class="label">Mengapa Kami</span>
            <h2>Pengalaman Foto Premium</h2>
            <p>Kami menghadirkan kombinasi sempurna antara teknologi modern dan sentuhan artistik</p>
        </div>
        <div class="packages-grid">
            <div class="package-card fade-up delay-1">
                <h3><i class="ph ph-lightning" style="color:var(--accent);margin-right:8px;"></i>Proses Mudah</h3>
                <p style="color:var(--text-secondary);margin-top:12px;font-size:0.9rem;">
                    Pesan sesi foto tanpa perlu membuat akun. Cukup pilih jadwal, isi data, dan konfirmasi — selesai!
                </p>
            </div>
            <div class="package-card fade-up delay-2">
                <h3><i class="ph ph-paint-brush" style="color:var(--accent);margin-right:8px;"></i>Editing Profesional</h3>
                <p style="color:var(--text-secondary);margin-top:12px;font-size:0.9rem;">
                    Tim editor berpengalaman kami akan menyempurnakan setiap foto dengan sentuhan profesional.
                </p>
            </div>
            <div class="package-card fade-up delay-3">
                <h3><i class="ph ph-shield-check" style="color:var(--accent);margin-right:8px;"></i>Hasil Terjamin</h3>
                <p style="color:var(--text-secondary);margin-top:12px;font-size:0.9rem;">
                    Kepuasan pelanggan adalah prioritas utama kami. Revisi gratis hingga Anda benar-benar puas.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Footer -->
<footer class="footer">
    <div class="container">
        <div class="footer-grid">
            <div class="footer-brand">
                <h3>Crafted <span>Studio</span></h3>
                <p>Studio foto profesional yang menghadirkan pengalaman fotografi premium. Abadikan setiap momen berharga bersama kami.</p>
            </div>
            <div class="footer-col">
                <h4>Navigasi</h4>
                <ul>
                    <li><a href="#beranda">Beranda</a></li>
                    <li><a href="#paket">Paket Foto</a></li>
                    <li><a href="booking.php">Booking</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h4>Kontak</h4>
                <ul>
                    <li><a href="#">hello@crafted.studio</a></li>
                    <li><a href="#">+62 812 3456 7890</a></li>
                    <li><a href="#">Jl. Fotografi No. 1</a></li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            &copy; <?= date('Y') ?> Crafted Studio. All rights reserved.
        </div>
    </div>
</footer>

<script>
// Navbar scroll effect
const navbar = document.getElementById('navbar');
window.addEventListener('scroll', () => {
    navbar.classList.toggle('scrolled', window.scrollY > 50);
});

// Fade-in on scroll
const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.style.animationPlayState = 'running';
        }
    });
}, { threshold: 0.1 });

document.querySelectorAll('.fade-up').forEach(el => {
    el.style.animationPlayState = 'paused';
    observer.observe(el);
});
</script>

</body>
</html>
