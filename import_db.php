<?php
/**
 * Crafted Studio — Database Auto-Importer
 */
$error = '';
$success = false;

if (isset($_GET['run'])) {
    try {
        // Connect to MySQL server (without specifying DB name yet, because it might not exist)
        $host = 'localhost';
        $user = 'root';
        $pass = '';
        
        $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
        
        // Read database.sql
        $sqlPath = __DIR__ . '/database.sql';
        if (!file_exists($sqlPath)) {
            throw new Exception("File database.sql tidak ditemukan di: " . $sqlPath);
        }
        
        $sql = file_get_contents($sqlPath);
        
        // Execute the entire SQL script (multi-query)
        $pdo->exec($sql);
        $success = true;
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Importer — Crafted Studio</title>
    <link rel="stylesheet" href="assets/css/public.css">
    <link rel="stylesheet" href="https://unpkg.com/@phosphor-icons/web@2.0.3/src/regular/style.css">
    <style>
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
            font-family: 'Inter', sans-serif;
            background-color: #0a0a0f;
            color: #f3f4f6;
        }
        .importer-card {
            width: 100%;
            max-width: 480px;
            padding: 40px;
            border-radius: 24px;
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.5);
            text-align: center;
        }
        .icon-wrap {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
            font-size: 2.5rem;
        }
        .icon-wrap.neutral {
            background: rgba(200, 165, 90, 0.1);
            color: #c8a55a;
        }
        .icon-wrap.success {
            background: rgba(16, 185, 129, 0.1);
            color: #10b981;
        }
        .icon-wrap.error {
            background: rgba(239, 68, 68, 0.1);
            color: #ef4444;
        }
        h1 {
            font-size: 1.75rem;
            font-weight: 700;
            margin-bottom: 12px;
            background: linear-gradient(135deg, #fff, #a1a1aa);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        p {
            color: #a1a1aa;
            font-size: 0.95rem;
            line-height: 1.6;
            margin-bottom: 30px;
        }
        .error-box {
            padding: 16px;
            background: rgba(239, 68, 68, 0.05);
            border: 1px solid rgba(239, 68, 68, 0.15);
            border-radius: 12px;
            color: #fca5a5;
            font-family: monospace;
            font-size: 0.82rem;
            text-align: left;
            margin-bottom: 24px;
            overflow-x: auto;
            white-space: pre-wrap;
            word-break: break-all;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            padding: 14px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 0.95rem;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
        }
        .btn-primary {
            background: #c8a55a;
            color: #0a0a0f;
            border: none;
        }
        .btn-primary:hover {
            background: #b69248;
            transform: translateY(-2px);
        }
        .btn-outline {
            background: transparent;
            border: 1px solid rgba(255, 255, 255, 0.15);
            color: #f3f4f6;
        }
        .btn-outline:hover {
            background: rgba(255, 255, 255, 0.05);
            border-color: rgba(255, 255, 255, 0.3);
        }
    </style>
</head>
<body>

<div class="importer-card">
    <?php if (!$success && !$error): ?>
        <div class="icon-wrap neutral">
            <i class="ph ph-database"></i>
        </div>
        <h1>Setup Database</h1>
        <p>Aplikasi membutuhkan database <strong>crafted_studio</strong>. Klik tombol di bawah untuk mengimpor schema database secara otomatis.</p>
        <a href="?run=1" class="btn btn-primary">
            <i class="ph ph-play-circle"></i>
            Mulai Impor Sekarang
        </a>
    <?php elseif ($success): ?>
        <div class="icon-wrap success">
            <i class="ph ph-check-circle"></i>
        </div>
        <h1>Impor Database Sukses!</h1>
        <p>Database <strong>crafted_studio</strong> beserta tabel dan data contoh berhasil dibuat dan diimpor.</p>
        <a href="/crafted-studio/" class="btn btn-primary">
            <i class="ph ph-house"></i>
            Buka Website Utama
        </a>
    <?php else: ?>
        <div class="icon-wrap error">
            <i class="ph ph-warning-circle"></i>
        </div>
        <h1>Gagal Mengimpor Database</h1>
        <p>Terjadi kesalahan saat mencoba menyambung ke MySQL atau mengeksekusi script SQL:</p>
        <div class="error-box"><?= htmlspecialchars($error) ?></div>
        <a href="?run=1" class="btn btn-primary" style="margin-bottom:12px;">
            <i class="ph ph-arrow-counter-clockwise"></i>
            Coba Lagi
        </a>
        <a href="/crafted-studio/" class="btn btn-outline">
            Kembali ke Beranda
        </a>
    <?php endif; ?>
</div>

</body>
</html>
