<?php
require_once __DIR__ . '/../../app/bootstrap.php';
require_method('GET'); $user = require_login();
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) json_response(['success'=>false,'message'=>'Invalid project ID.'], 422);
$item = get_submission(db(), (int)$id, true);
if (!$item) json_response(['success'=>false,'message'=>'Project not found.'], 404);
if ($user['role'] !== 'admin' && (int)$item['user_id'] !== $user['id']) json_response(['success'=>false,'message'=>'You cannot access this project.'], 403);
json_response(['success'=>true,'submission'=>public_submission($item)]);
