<?php
/**
 * Crafted Studio — Booking Page (Public, No Login)
 * Step 1: Pick date, time, package
 */
require_once __DIR__ . '/includes/functions.php';

$pakets = getPaketFoto();
$selectedPaketId = isset($_GET['paket']) ? (int)$_GET['paket'] : 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Sesi Foto — Crafted Studio</title>
    <meta name="description" content="Pilih jadwal dan paket foto Anda. Proses booking mudah tanpa perlu daftar akun.">
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
            <li><a href="/crafted-studio/#paket">Paket</a></li>
            <li><a href="booking.php" style="color:var(--accent)">Booking</a></li>
        </ul>
        <button class="nav-toggle" onclick="document.getElementById('navLinks').classList.toggle('open')">
            <span></span><span></span><span></span>
        </button>
    </div>
</nav>

<main class="booking-page">
    <div class="container">
        <h1 class="fade-up">Pesan Sesi Foto</h1>
        <p class="subtitle fade-up delay-1">Pilih tanggal, waktu, dan paket foto yang sesuai kebutuhanmu</p>

        <!-- Steps Indicator -->
        <div class="booking-steps fade-up delay-1">
            <div class="step-indicator active" id="stepInd1">
                <span class="num">1</span> Jadwal & Paket
            </div>
            <div class="step-indicator" id="stepInd2">
                <span class="num">2</span> Konfirmasi
            </div>
            <div class="step-indicator" id="stepInd3">
                <span class="num">3</span> Selesai
            </div>
        </div>

        <div class="booking-layout fade-up delay-2">
            <!-- Main Form -->
            <div>
                <!-- Date Selection -->
                <div class="glass-card" style="margin-bottom:24px;">
                    <h3 style="margin-bottom:20px;font-size:1rem;">
                        <i class="ph ph-calendar" style="color:var(--accent);margin-right:8px;"></i>
                        Pilih Tanggal
                    </h3>
                    <div class="form-group">
                        <div class="date-input-wrap">
                            <input type="date" class="form-control" id="bookingDate"
                                   min="<?= date('Y-m-d', strtotime('+1 day')) ?>"
                                   max="<?= date('Y-m-d', strtotime('+60 days')) ?>">
                        </div>
                        <small style="color:var(--text-muted);font-size:0.8rem;margin-top:6px;display:block;">
                            Pemesanan minimal H+1 dari hari ini
                        </small>
                    </div>
                </div>

                <!-- Time Selection -->
                <div class="glass-card" style="margin-bottom:24px;">
                    <h3 style="margin-bottom:20px;font-size:1rem;">
                        <i class="ph ph-clock" style="color:var(--accent);margin-right:8px;"></i>
                        Pilih Waktu Mulai
                    </h3>
                    <div class="time-slots" id="timeSlots">
                        <?php for ($h = 9; $h <= 19; $h++): ?>
                        <button type="button" class="time-slot" data-time="<?= sprintf('%02d:00', $h) ?>">
                            <?= sprintf('%02d:00', $h) ?>
                        </button>
                        <?php endfor; ?>
                    </div>
                    <small style="color:var(--text-muted);font-size:0.8rem;margin-top:12px;display:block;" id="timeInfo">
                        Jam operasional: 09:00 — 20:00 WIB
                    </small>
                </div>

                <!-- Package Selection -->
                <div class="glass-card">
                    <h3 style="margin-bottom:20px;font-size:1rem;">
                        <i class="ph ph-package" style="color:var(--accent);margin-right:8px;"></i>
                        Pilih Paket Foto
                    </h3>
                    <div class="paket-options" id="paketOptions">
                        <?php foreach ($pakets as $p): ?>
                        <label class="paket-option <?= $selectedPaketId === $p['id_paket'] ? 'selected' : '' ?>"
                               data-id="<?= $p['id_paket'] ?>"
                               data-harga="<?= $p['harga'] ?>"
                               data-durasi="<?= $p['durasi_jam'] ?>"
                               data-nama="<?= e($p['nama_paket']) ?>">
                            <input type="radio" name="paket" value="<?= $p['id_paket'] ?>"
                                   <?= $selectedPaketId === $p['id_paket'] ? 'checked' : '' ?>>
                            <div class="paket-radio"></div>
                            <div class="paket-info">
                                <h4><?= e($p['nama_paket']) ?></h4>
                                <p><?= $p['durasi_jam'] ?> jam · <?= $p['jumlah_foto'] ?> foto</p>
                            </div>
                            <div class="paket-price"><?= formatRupiah($p['harga']) ?></div>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Summary Sidebar -->
            <div class="glass-card summary-card">
                <h3>
                    <i class="ph ph-receipt" style="color:var(--accent);margin-right:8px;"></i>
                    Ringkasan Pemesanan
                </h3>

                <div class="summary-item">
                    <span class="label">Tanggal</span>
                    <span class="value" id="sumDate">— Belum dipilih</span>
                </div>
                <div class="summary-item">
                    <span class="label">Waktu</span>
                    <span class="value" id="sumTime">— Belum dipilih</span>
                </div>
                <div class="summary-item">
                    <span class="label">Paket</span>
                    <span class="value" id="sumPaket">— Belum dipilih</span>
                </div>
                <div class="summary-item">
                    <span class="label">Durasi</span>
                    <span class="value" id="sumDurasi">—</span>
                </div>

                <div class="summary-total">
                    <span class="label">Total</span>
                    <span class="value" id="sumTotal">Rp 0</span>
                </div>

                <button class="btn btn-primary btn-block btn-lg" style="margin-top:24px;" id="btnNext" disabled>
                    <i class="ph ph-arrow-right"></i>
                    Lanjut ke Konfirmasi
                </button>
            </div>
        </div>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', () => {
    let selectedDate = '';
    let selectedTime = '';
    let selectedPaket = null;

    const dateInput = document.getElementById('bookingDate');
    const timeSlots = document.querySelectorAll('.time-slot');
    const paketOptions = document.querySelectorAll('.paket-option');
    const btnNext = document.getElementById('btnNext');

    // Check if paket was pre-selected via URL
    const preSelected = document.querySelector('.paket-option.selected');
    if (preSelected) {
        selectedPaket = {
            id: preSelected.dataset.id,
            nama: preSelected.dataset.nama,
            harga: parseFloat(preSelected.dataset.harga),
            durasi: parseInt(preSelected.dataset.durasi)
        };
        updateSummary();
    }

    // Date selection
    dateInput.addEventListener('change', (e) => {
        selectedDate = e.target.value;
        updateSummary();
    });

    // Time selection
    timeSlots.forEach(slot => {
        slot.addEventListener('click', () => {
            timeSlots.forEach(s => s.classList.remove('selected'));
            slot.classList.add('selected');
            selectedTime = slot.dataset.time;
            updateSummary();
        });
    });

    // Package selection
    paketOptions.forEach(opt => {
        opt.addEventListener('click', () => {
            paketOptions.forEach(o => o.classList.remove('selected'));
            opt.classList.add('selected');
            opt.querySelector('input').checked = true;
            selectedPaket = {
                id: opt.dataset.id,
                nama: opt.dataset.nama,
                harga: parseFloat(opt.dataset.harga),
                durasi: parseInt(opt.dataset.durasi)
            };
            updateSummary();
        });
    });

    function formatTanggal(dateStr) {
        if (!dateStr) return '— Belum dipilih';
        const d = new Date(dateStr);
        const days = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
        const months = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
        return days[d.getDay()] + ', ' + d.getDate() + ' ' + months[d.getMonth()] + ' ' + d.getFullYear();
    }

    function formatRupiah(n) {
        return 'Rp ' + n.toLocaleString('id-ID');
    }

    function updateSummary() {
        document.getElementById('sumDate').textContent = formatTanggal(selectedDate);
        document.getElementById('sumTime').textContent = selectedTime || '— Belum dipilih';
        document.getElementById('sumPaket').textContent = selectedPaket ? selectedPaket.nama : '— Belum dipilih';
        document.getElementById('sumDurasi').textContent = selectedPaket ? selectedPaket.durasi + ' jam' : '—';
        document.getElementById('sumTotal').textContent = selectedPaket ? formatRupiah(selectedPaket.harga) : 'Rp 0';

        // Calculate end time display
        if (selectedTime && selectedPaket) {
            const [h] = selectedTime.split(':').map(Number);
            const endH = h + selectedPaket.durasi;
            const endTime = String(endH).padStart(2,'0') + ':00';
            document.getElementById('sumTime').textContent = selectedTime + ' — ' + endTime + ' WIB';
            document.getElementById('timeInfo').textContent = 'Sesi berakhir pukul ' + endTime + ' WIB';
        }

        // Enable/disable button
        const ready = selectedDate && selectedTime && selectedPaket;
        btnNext.disabled = !ready;
    }

    // Next button
    btnNext.addEventListener('click', () => {
        if (!selectedDate || !selectedTime || !selectedPaket) return;

        const [h] = selectedTime.split(':').map(Number);
        const endH = h + selectedPaket.durasi;
        const endTime = String(endH).padStart(2,'0') + ':00';

        const params = new URLSearchParams({
            tanggal: selectedDate,
            jam_mulai: selectedTime,
            jam_selesai: endTime,
            paket: selectedPaket.id
        });

        window.location.href = 'konfirmasi.php?' + params.toString();
    });
});
</script>

</body>
</html>
