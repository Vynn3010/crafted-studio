<?php
/**
 * Crafted Studio — Logout
 * Accepts ?role= param so the correct role session is destroyed.
 * config.php already resolves session_name based on ?role= for this page.
 */
require_once __DIR__ . '/../includes/auth.php';
logout();
