<?php
require_once __DIR__ . '/../../app/bootstrap.php';
require_method('POST'); $user = require_login(); require_csrf();
$title = clean_string($_POST['title'] ?? '', 150);
$description = clean_string($_POST['description'] ?? '', 5000);
$languages = $_POST['languages'] ?? [];
if ($title === '' || $description === '' || !is_array($languages) || count($languages) < 1) json_response(['success'=>false,'message'=>'Complete the project name, description, and at least one language.'], 422);
$languages = array_values(array_unique(array_filter(array_map(fn($v)=>clean_string($v,50), $languages))));
if (!$languages) json_response(['success'=>false,'message'=>'Choose at least one programming language.'], 422);

$names = $_FILES['files']['name'] ?? [];
if (!is_array($names) || count($names) < 1) json_response(['success'=>false,'message'=>'Upload at least one source file.'], 422);
if (count($names) > MAX_FILES) json_response(['success'=>false,'message'=>'You can upload up to '.MAX_FILES.' files per submission.'], 422);

$allowed = ['html','htm','css','js','jsx','ts','tsx','php','py','java','cpp','c','h','cs','sql','json','xml','txt','md','vue','tsv','sh'];
$total = 0; $validated = [];
$finfo = new finfo(FILEINFO_MIME_TYPE);
foreach ($names as $i => $original) {
    $error = $_FILES['files']['error'][$i] ?? UPLOAD_ERR_NO_FILE;
    if ($error !== UPLOAD_ERR_OK) json_response(['success'=>false,'message'=>'One of the files could not be uploaded. Check its size and try again.'], 422);
    $size = (int)($_FILES['files']['size'][$i] ?? 0);
    $tmp = $_FILES['files']['tmp_name'][$i] ?? '';
    $ext = strtolower(pathinfo((string)$original, PATHINFO_EXTENSION));
    if ($size < 1 || $size > MAX_FILE_SIZE || !in_array($ext, $allowed, true) || !is_uploaded_file($tmp)) {
        json_response(['success'=>false,'message'=>'Invalid file. Use supported source-code files up to 5 MB each.'], 422);
    }
    $total += $size;
    if ($total > MAX_TOTAL_UPLOAD) json_response(['success'=>false,'message'=>'The total upload size cannot exceed 20 MB.'], 422);
    $validated[] = ['original'=>basename((string)$original),'tmp'=>$tmp,'size'=>$size,'ext'=>$ext];
}

$pdo = db();
$pdo->beginTransaction();
$submissionId = 0;
try {
    $stmt = $pdo->prepare("INSERT INTO submissions (user_id,title,description,status) VALUES (?,?,?,'pending')");
    $stmt->execute([$user['id'],$title,$description]);
    $submissionId = (int)$pdo->lastInsertId();
    $langStmt = $pdo->prepare('INSERT INTO submission_languages (submission_id,language) VALUES (?,?)');
    foreach ($languages as $language) $langStmt->execute([$submissionId,$language]);

    $dir = UPLOAD_DIR . '/' . $submissionId;
    if (!is_dir($dir) && !mkdir($dir, 0750, true) && !is_dir($dir)) throw new RuntimeException('Could not create upload directory.');
    $fileStmt = $pdo->prepare('INSERT INTO submission_files (submission_id,file_name,storage_name,file_size) VALUES (?,?,?,?)');
    foreach ($validated as $file) {
        $stored = bin2hex(random_bytes(16)) . '.' . $file['ext'];
        if (!move_uploaded_file($file['tmp'], $dir . '/' . $stored)) throw new RuntimeException('Could not save uploaded file.');
        $fileStmt->execute([$submissionId,$file['original'],$stored,$file['size']]);
    }
    $pdo->commit();
    json_response(['success'=>true,'message'=>'Project submitted successfully.','submission_id'=>$submissionId]);
} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    if ($submissionId) delete_submission_files($submissionId);
    error_log($e->getMessage());
    json_response(['success'=>false,'message'=>'The project could not be saved. Please try again.'], 500);
}
