<?php
/**
 * Crafted Studio — Admin Login Page
 */
require_once __DIR__ . '/../includes/auth.php';

// Already logged in? Redirect to dashboard
if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Email dan password harus diisi.';
    } else {
        $result = attemptLogin($email, $password);
        if ($result) {
            loginUser($result);
            header('Location: index.php');
            exit;
        } else {
            $error = 'Email atau password salah.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Crafted Studio Dashboard</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://unpkg.com/@phosphor-icons/web@2.0.3/src/regular/style.css">
    <style>
        .login-page::before {
            content: '';
            position: fixed;
            top: -200px; right: -200px;
            width: 600px; height: 600px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(200,165,90,0.08), transparent 70%);
            filter: blur(80px);
            pointer-events: none;
        }
        .login-logo {
            width: 60px; height: 60px;
            border-radius: 16px;
            background: linear-gradient(135deg, var(--accent), var(--accent-dark));
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 1.5rem;
            color: #0a0a0f;
        }
        .demo-info {
            margin-top: 24px;
            padding: 16px;
            background: rgba(255,255,255,0.03);
            border: 1px solid rgba(255,255,255,0.06);
            border-radius: 8px;
            font-size: 0.75rem;
            color: var(--text-muted);
            line-height: 1.8;
        }
        .demo-info strong { color: var(--text-secondary); }
    </style>
</head>
<body>
<div class="login-page">
    <div class="login-box">
        <div class="login-card">
            <div class="login-header">
                <div class="login-logo">
                    <i class="ph ph-camera"></i>
                </div>
                <h1>Crafted <span>Studio</span></h1>
                <p>Masuk ke dashboard</p>
            </div>

            <?php if ($error): ?>
            <div class="flash flash-error">
                <i class="ph ph-warning-circle"></i>
                <?= htmlspecialchars($error) ?>
            </div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" class="form-control" id="email" name="email"
                           placeholder="email@crafted.studio"
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required autofocus>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" class="form-control" id="password" name="password"
                           placeholder="••••••••" required>
                </div>

                <button type="submit" class="btn btn-primary btn-block" style="padding:12px;margin-top:8px;">
                    <i class="ph ph-sign-in"></i>
                    Masuk
                </button>
            </form>

            <div class="demo-info">
                <strong>Demo Login:</strong><br>
                Admin: admin@crafted.studio<br>
                Fotografer: budi@crafted.studio<br>
                Editor: dian@crafted.studio<br>
                Password (semua): <strong>password</strong>
            </div>
        </div>
        <div class="login-footer">
            <a href="/crafted-studio/" style="color:var(--text-muted);font-size:0.8rem;">
                ← Kembali ke website
            </a>
        </div>
    </div>
</div>
</body>
</html>
