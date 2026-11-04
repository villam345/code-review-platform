<?php
require_once __DIR__ . '/../../app/bootstrap.php';
require_method('POST'); require_csrf();
$data = request_json();
$email = strtolower(clean_string($data['email'] ?? '', 150));
$password = $data['password'] ?? '';
if (!filter_var($email, FILTER_VALIDATE_EMAIL) || !is_string($password)) json_response(['success'=>false,'message'=>'Enter a valid email and password.'], 422);
$stmt = db()->prepare('SELECT id,name,email,password_hash,role FROM users WHERE email = ? LIMIT 1');
$stmt->execute([$email]);
$user = $stmt->fetch();
if (!$user || !password_verify($password, $user['password_hash'])) json_response(['success'=>false,'message'=>'Email or password is incorrect.'], 401);
session_regenerate_id(true);
$_SESSION['user_id'] = (int)$user['id'];
$_SESSION['role'] = $user['role'];
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
json_response([
    'success' => true,
    'user' => [
        'id' => (int)$user['id'],
        'name' => $user['name'],
        'email' => $user['email'],
        'role' => $user['role'],
    ],
    'csrf_token' => $_SESSION['csrf_token'],
]);
