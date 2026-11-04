<?php
require_once __DIR__ . '/../../app/bootstrap.php';
require_method('GET');
if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
if (empty($_SESSION['user_id'])) json_response(['success'=>true,'authenticated'=>false,'csrf_token'=>$_SESSION['csrf_token']]);
$stmt = db()->prepare('SELECT id,name,email,role FROM users WHERE id = ?');
$stmt->execute([(int)$_SESSION['user_id']]);
$user = $stmt->fetch();
if (!$user) { session_destroy(); json_response(['success'=>true,'authenticated'=>false]); }
$user['id'] = (int)$user['id'];
json_response(['success'=>true,'authenticated'=>true,'user'=>$user,'csrf_token'=>$_SESSION['csrf_token']]);
