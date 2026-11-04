<?php
/*
 * Run this from a terminal, not from a browser:
 * php database/create_admin.php
 */
if (PHP_SAPI !== 'cli') { http_response_code(404); exit('Not found'); }
require_once __DIR__ . '/../app/bootstrap.php';
echo "Create the first CodeReview administrator\n";
$name = trim((string)readline('Admin name: '));
$email = strtolower(trim((string)readline('Admin email: ')));
$password = (string)readline('Admin password (minimum 12 characters): ');
if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 12) {
    fwrite(STDERR, "Invalid input. Password must be at least 12 characters.\n"); exit(1);
}
$stmt = db()->prepare("INSERT INTO users (name,email,password_hash,role) VALUES (?,?,?,'admin')");
try {
    $stmt->execute([$name,$email,password_hash($password,PASSWORD_DEFAULT)]);
    echo "Administrator account created.\n";
} catch (PDOException $e) {
    fwrite(STDERR, "Could not create account. The email may already exist.\n"); exit(1);
}
