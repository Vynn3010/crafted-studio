<?php
/**
 * Crafted Studio — Admin Index (Role-based redirect)
 */
require_once __DIR__ . '/../includes/auth.php';

requireLogin();

$role = getUserRole();

switch ($role) {
    case 'admin':
    case 'staff':
        header('Location: admin/dashboard.php');
        break;
    case 'fotografer':
        header('Location: fotografer/dashboard.php');
        break;
    case 'editor':
        header('Location: editor/dashboard.php');
        break;
    default:
        header('Location: login.php');
        break;
}
exit;
