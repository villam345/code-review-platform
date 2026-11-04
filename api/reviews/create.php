<?php
require_once __DIR__ . '/../../app/bootstrap.php';
require_method('POST'); $admin = require_admin(); require_csrf();
$data = request_json();
$id = (int)($data['submission_id'] ?? 0);
$status = $data['status'] ?? '';
$feedback = clean_string($data['feedback'] ?? '', 10000);
if ($id < 1 || !in_array($status, ['reviewed','revision_requested','rejected'], true) || $feedback === '') json_response(['success'=>false,'message'=>'Choose an outcome and write feedback.'], 422);
$pdo = db();
$stmt = $pdo->prepare('SELECT user_id FROM submissions WHERE id = ?');
$stmt->execute([$id]); $submission = $stmt->fetch();
if (!$submission) json_response(['success'=>false,'message'=>'Submission not found.'], 404);
$pdo->beginTransaction();
try {
    $pdo->prepare('INSERT INTO reviews (submission_id,admin_id,feedback,status) VALUES (?,?,?,?)')->execute([$id,$admin['id'],$feedback,$status]);
    $pdo->prepare('UPDATE submissions SET status = ? WHERE id = ?')->execute([$status,$id]);
    $message = 'Your project review is available. Status: ' . str_replace('_',' ',$status) . '.';
    $pdo->prepare('INSERT INTO notifications (user_id,message) VALUES (?,?)')->execute([(int)$submission['user_id'],$message]);
    $pdo->commit();
    json_response(['success'=>true,'message'=>'Review saved and developer notified.']);
} catch (Throwable $e) {
    $pdo->rollBack(); error_log($e->getMessage());
    json_response(['success'=>false,'message'=>'Could not save the review.'],500);
}
