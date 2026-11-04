<?php
require_once __DIR__ . '/../../app/bootstrap.php';
require_method('GET'); $user = require_login();
$pdo = db();
if ($user['role'] === 'admin') {
    $stmt = $pdo->query('SELECT id FROM submissions ORDER BY created_at DESC');
} else {
    $stmt = $pdo->prepare('SELECT id FROM submissions WHERE user_id = ? ORDER BY created_at DESC');
    $stmt->execute([$user['id']]);
}
$items = [];
foreach ($stmt->fetchAll() as $row) {
    $item = get_submission($pdo, (int)$row['id'], false);
    if ($item) $items[] = public_submission($item);
}
json_response(['success'=>true,'submissions'=>$items]);
