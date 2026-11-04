<?php
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => $secure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/helpers.php';

header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: strict-origin-when-cross-origin');

function db(): PDO {
    static $pdo = null;
    if ($pdo instanceof PDO) return $pdo;
    global $DB_HOST, $DB_NAME, $DB_USER, $DB_PASS, $DB_PORT;
    $dsn = 'mysql:host=' . $DB_HOST . ';port=' . $DB_PORT . ';dbname=' . $DB_NAME . ';charset=utf8mb4';
    try {
        $pdo = new PDO($dsn, $DB_USER, $DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
        ]);
    } catch (PDOException $e) {
        error_log('Database connection failed: ' . $e->getMessage());
        json_response([
            'success' => false,
            'message' => 'Database is unavailable. Start MySQL and import database/schema.sql, then refresh.',
        ], 503);
    }
    return $pdo;
}
