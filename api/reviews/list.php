<?php
require_once __DIR__ . '/../../app/bootstrap.php';
require_method('GET'); $user = require_login();
if ($user['role'] === 'admin') {
    $stmt = db()->query("SELECT r.id,r.submission_id,r.feedback,r.status,r.created_at,s.title AS project_title,u.name AS developer_name
        FROM reviews r JOIN submissions s ON s.id=r.submission_id JOIN users u ON u.id=s.user_id ORDER BY r.created_at DESC");
} else {
    $stmt = db()->prepare("SELECT r.id,r.submission_id,r.feedback,r.status,r.created_at,s.title AS project_title,u.name AS admin_name
        FROM reviews r JOIN submissions s ON s.id=r.submission_id JOIN users u ON u.id=r.admin_id
        WHERE s.user_id = ? ORDER BY r.created_at DESC");
    $stmt->execute([$user['id']]);
}
json_response(['success'=>true,'reviews'=>$stmt->fetchAll()]);
