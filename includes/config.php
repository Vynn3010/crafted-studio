<?php
/**
 * Crafted Studio — Configuration
 */

// Database
define('DB_HOST', 'localhost');
define('DB_NAME', 'crafted_studio');
define('DB_USER', 'root');
define('DB_PASS', '');

// App
define('APP_NAME', 'Crafted Studio');
define('APP_TAGLINE', 'Abadikan Momen Terbaikmu');
define('APP_URL', 'http://crafted-studio.test');

// Paths
define('BASE_PATH', dirname(__DIR__));
define('INCLUDES_PATH', BASE_PATH . '/includes');
define('ASSETS_URL', APP_URL . '/assets');

// Timezone
date_default_timezone_set('Asia/Jakarta');

/**
 * Determine session name based on current URL path.
 * Each role gets its own isolated PHP session cookie,
 * so admin, fotografer, and editor can all be logged
 * in simultaneously in different browser tabs.
 */
function resolveSessionName(): string {
    $uri = $_SERVER['REQUEST_URI'] ?? '';

    if (strpos($uri, '/admin/fotografer/') !== false) {
        return 'cs_fotografer';
    }
    if (strpos($uri, '/admin/editor/') !== false) {
        return 'cs_editor';
    }
    if (strpos($uri, '/admin/admin/') !== false) {
        return 'cs_admin';
    }
    // Login & logout pages: check ?role= query param
    if (strpos($uri, '/admin/login.php') !== false || strpos($uri, '/admin/logout.php') !== false) {
        $role = $_GET['role'] ?? '';
        if ($role === 'fotografer') return 'cs_fotografer';
        if ($role === 'editor')     return 'cs_editor';
        return 'cs_admin'; // default for admin/staff login page
    }
    // index.php dispatcher
    return 'cs_admin';
}

// Session — must be set before session_start()
if (session_status() === PHP_SESSION_NONE) {
    session_name(resolveSessionName());
    session_start();
}
