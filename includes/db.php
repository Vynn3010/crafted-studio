<?php
/**
 * Crafted Studio — Database Connection (PDO)
 */

require_once __DIR__ . '/config.php';

function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            // If database does not exist (1049), redirect to auto-importer
            if ($e->getCode() === 1049 || strpos($e->getMessage(), '1049') !== false) {
                header('Location: /crafted-studio/import_db.php');
                exit;
            }
            die('Koneksi database gagal: ' . $e->getMessage());
        }
    }
    return $pdo;
}
