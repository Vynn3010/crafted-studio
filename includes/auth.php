<?php
/**
 * Crafted Studio — Authentication
 */

require_once __DIR__ . '/db.php';

/**
 * Attempt login across all role tables.
 * Returns ['role' => ..., 'user' => ...] or false.
 */
function attemptLogin(string $email, string $password): array|false {
    $db = getDB();

    // 1. Check staf table (admin/staff)
    $stmt = $db->prepare("SELECT * FROM staf WHERE email = ? AND status = 'aktif' LIMIT 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    if ($user && password_verify($password, $user['password'])) {
        return [
            'role'    => $user['role'], // 'admin' or 'staff'
            'user_id' => $user['id_staf'],
            'nama'    => $user['nama'],
            'email'   => $user['email'],
        ];
    }

    // 2. Check fotografer table
    $stmt = $db->prepare("SELECT * FROM fotografer WHERE email = ? AND status = 'aktif' LIMIT 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    if ($user && password_verify($password, $user['password'])) {
        return [
            'role'    => 'fotografer',
            'user_id' => $user['id_fotografer'],
            'nama'    => $user['nama'],
            'email'   => $user['email'],
        ];
    }

    // 3. Check editor table
    $stmt = $db->prepare("SELECT * FROM editor WHERE email = ? AND status = 'aktif' LIMIT 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    if ($user && password_verify($password, $user['password'])) {
        return [
            'role'    => 'editor',
            'user_id' => $user['id_editor'],
            'nama'    => $user['nama'],
            'email'   => $user['email'],
        ];
    }

    return false;
}

/**
 * Set session after successful login
 */
function loginUser(array $userData): void {
    $_SESSION['logged_in'] = true;
    $_SESSION['role']      = $userData['role'];
    $_SESSION['user_id']   = $userData['user_id'];
    $_SESSION['nama']      = $userData['nama'];
    $_SESSION['email']     = $userData['email'];
}

/**
 * Check if user is logged in
 */
function isLoggedIn(): bool {
    return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
}

/**
 * Get current user role
 */
function getUserRole(): string {
    return $_SESSION['role'] ?? '';
}

/**
 * Require login — redirect to login page if not authenticated
 */
function requireLogin(): void {
    if (!isLoggedIn()) {
        header('Location: /crafted-studio/admin/login.php');
        exit;
    }
}

/**
 * Require specific role
 */
function requireRole(string ...$roles): void {
    requireLogin();
    if (!in_array(getUserRole(), $roles)) {
        header('HTTP/1.1 403 Forbidden');
        echo '<h1>Akses Ditolak</h1><p>Anda tidak memiliki izin untuk halaman ini.</p>';
        exit;
    }
}

/**
 * Logout
 */
function logout(): void {
    session_destroy();
    header('Location: /crafted-studio/admin/login.php');
    exit;
}
