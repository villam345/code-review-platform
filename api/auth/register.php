<?php
require_once __DIR__ . '/../../app/bootstrap.php';
require_method('POST'); require_csrf();
$data = request_json();
$name = clean_string($data['name'] ?? '', 100);
$email = strtolower(clean_string($data['email'] ?? '', 150));
$password = $data['password'] ?? '';
if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || !is_string($password) || strlen($password) < 8 || strlen($password) > 200) {
    json_response(['success'=>false,'message'=>'Enter your name, a valid email, and a password of at least 8 characters.'], 422);
}
try {
    $stmt = db()->prepare('INSERT INTO users (name,email,password_hash,role) VALUES (?,?,?,\'developer\')');
    $stmt->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT)]);
    json_response(['success'=>true,'message'=>'Account created. You can now sign in.']);
} catch (PDOException $e) {
    if ($e->getCode() === '23000') json_response(['success'=>false,'message'=>'An account with that email already exists.'], 409);
    error_log($e->getMessage()); json_response(['success'=>false,'message'=>'Could not create the account.'], 500);
}
