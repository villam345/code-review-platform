<?php
require_once __DIR__ . '/../../app/bootstrap.php';
require_method('POST'); $user = require_login(); require_csrf();
$data = request_json(); $id = (int)($data['id'] ?? 0);
$stmt = db()->prepare('SELECT id,user_id,status FROM submissions WHERE id = ?');
$stmt->execute([$id]); $row = $stmt->fetch();
if (!$row || (int)$row['user_id'] !== $user['id']) json_response(['success'=>false,'message'=>'Project not found.'], 404);
if (!in_array($row['status'], ['reviewed','revision_requested','rejected'], true)) json_response(['success'=>false,'message'=>'Only completed or reviewed submissions can be deleted.'], 409);
db()->prepare('DELETE FROM submissions WHERE id = ?')->execute([$id]);
delete_submission_files($id);
json_response(['success'=>true,'message'=>'Submission deleted.']);
