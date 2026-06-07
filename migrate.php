<?php
/**
 * Crafted Studio — Database Migration
 * Adds: studio table, id_studio column to booking
 */

try {
    $pdo = new PDO('mysql:host=localhost;dbname=crafted_studio;charset=utf8mb4', 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);

    $steps = [];

    // 1. Create studio table if not exists
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `studio` (
            `id_studio` INT AUTO_INCREMENT PRIMARY KEY,
            `nama_ruangan` VARCHAR(100) NOT NULL,
            `background` VARCHAR(100) NOT NULL DEFAULT 'Putih',
            `kapasitas` INT NOT NULL DEFAULT 5,
            `status` ENUM('tersedia', 'nonaktif') NOT NULL DEFAULT 'tersedia',
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    $steps[] = ['ok', 'Tabel <code>studio</code> berhasil dibuat (atau sudah ada).'];

    // 2. Add id_studio column to booking if not exists
    $col = $pdo->query("SHOW COLUMNS FROM `booking` LIKE 'id_studio'")->fetch();
    if (!$col) {
        $pdo->exec("ALTER TABLE `booking` ADD COLUMN `id_studio` INT NULL AFTER `id_fotografer`");
        $pdo->exec("ALTER TABLE `booking` ADD CONSTRAINT `fk_booking_studio` FOREIGN KEY (`id_studio`) REFERENCES `studio` (`id_studio`) ON DELETE SET NULL");
        $steps[] = ['ok', 'Kolom <code>id_studio</code> berhasil ditambahkan ke tabel <code>booking</code>.'];
    } else {
        $steps[] = ['info', 'Kolom <code>id_studio</code> sudah ada di tabel <code>booking</code>.'];
    }

    // 3. Seed studios if empty
    $count = $pdo->query("SELECT COUNT(*) FROM studio")->fetchColumn();
    if ($count == 0) {
        $pdo->exec("INSERT INTO `studio` (`nama_ruangan`, `background`, `kapasitas`) VALUES
            ('Studio A', 'Putih Bersih', 8),
            ('Studio B', 'Abu-abu Gelap', 6),
            ('Studio C', 'Outdoor Natural', 10)");
        $steps[] = ['ok', '3 data studio berhasil ditambahkan.'];
    } else {
        $steps[] = ['info', "Tabel studio sudah berisi $count data, tidak perlu seed."];
    }

} catch (PDOException $e) {
    $steps[] = ['error', 'Error: ' . htmlspecialchars($e->getMessage())];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Migration — Crafted Studio</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { background: #0a0a0f; color: #f3f4f6; font-family: system-ui, sans-serif; display: flex; align-items: center; justify-content: center; min-height: 100vh; padding: 20px; }
        .card { background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.08); border-radius: 20px; padding: 40px; max-width: 520px; width: 100%; }
        h1 { font-size: 1.4rem; margin-bottom: 8px; }
        .subtitle { color: #71717a; font-size: 0.875rem; margin-bottom: 28px; }
        .step { display: flex; align-items: flex-start; gap: 12px; padding: 12px 16px; border-radius: 10px; margin-bottom: 10px; font-size: 0.875rem; }
        .step.ok   { background: rgba(16,185,129,0.08); border: 1px solid rgba(16,185,129,0.2); }
        .step.info { background: rgba(99,179,237,0.08); border: 1px solid rgba(99,179,237,0.2); }
        .step.error{ background: rgba(239,68,68,0.08);  border: 1px solid rgba(239,68,68,0.2); }
        .icon { font-size: 1rem; flex-shrink: 0; margin-top: 1px; }
        .ok   .icon { color: #10b981; }
        .info .icon { color: #63b3ed; }
        .error .icon { color: #ef4444; }
        code { background: rgba(255,255,255,0.08); padding: 1px 5px; border-radius: 4px; font-family: monospace; }
        .actions { margin-top: 28px; display: flex; gap: 10px; }
        a { display: inline-flex; align-items: center; gap: 6px; padding: 10px 20px; border-radius: 10px; text-decoration: none; font-size: 0.875rem; font-weight: 600; }
        .btn-primary { background: #c8a55a; color: #0a0a0f; }
        .btn-outline { border: 1px solid rgba(255,255,255,0.15); color: #f3f4f6; }
        .btn-outline:hover { background: rgba(255,255,255,0.05); }
    </style>
</head>
<body>
<div class="card">
    <h1>🔧 Database Migration</h1>
    <p class="subtitle">Menambahkan tabel studio dan kolom id_studio ke booking</p>

    <?php foreach ($steps as [$type, $msg]): ?>
    <div class="step <?= $type ?>">
        <span class="icon"><?= $type === 'ok' ? '✓' : ($type === 'error' ? '✗' : 'ℹ') ?></span>
        <span><?= $msg ?></span>
    </div>
    <?php endforeach; ?>

    <div class="actions">
        <a href="/crafted-studio/admin/admin/dashboard.php" class="btn-primary">→ Buka Admin Dashboard</a>
        <a href="/crafted-studio/" class="btn-outline">Beranda</a>
    </div>
</div>
</body>
</html>
