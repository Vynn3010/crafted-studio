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

// Session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
