<?php
declare(strict_types=1);

function json_response(array $data, int $status = 200): never {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
}

function request_json(): array {
    $raw = file_get_contents('php://input');
    $data = json_decode($raw ?: '{}', true);
    return is_array($data) ? $data : [];
}

function require_method(string ...$methods): void {
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    if (!in_array($method, $methods, true)) {
        header('Allow: ' . implode(', ', $methods));
        json_response(['success' => false, 'message' => 'Method not allowed.'], 405);
    }
}

function require_login(): array {
    if (empty($_SESSION['user_id'])) {
        json_response(['success' => false, 'message' => 'Please sign in first.'], 401);
    }
    return ['id' => (int)$_SESSION['user_id'], 'role' => (string)$_SESSION['role']];
}

function require_admin(): array {
    $user = require_login();
    if ($user['role'] !== 'admin') {
        json_response(['success' => false, 'message' => 'Administrator access required.'], 403);
    }
    return $user;
}

function require_csrf(): void {
    $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
        json_response(['success' => false, 'message' => 'Security token expired. Refresh the page and try again.'], 419);
    }
}

function clean_string(mixed $value, int $max = 5000): string {
    if (!is_string($value)) return '';
    $value = trim($value);
    return mb_substr($value, 0, $max);
}

function public_submission(array $row): array {
    return [
        'id' => (int)$row['id'],
        'user_id' => (int)$row['user_id'],
        'developer_name' => $row['developer_name'] ?? '',
        'title' => $row['title'],
        'description' => $row['description'],
        'status' => $row['status'],
        'languages' => $row['languages'] ?? [],
        'files' => $row['files'] ?? [],
        'review' => $row['review'] ?? null,
        'created_at' => $row['created_at'],
        'updated_at' => $row['updated_at'],
    ];
}

function get_submission(PDO $pdo, int $id, bool $withContents = false): ?array {
    $stmt = $pdo->prepare(
        "SELECT s.*, u.name AS developer_name
         FROM submissions s JOIN users u ON u.id = s.user_id
         WHERE s.id = ?"
    );
    $stmt->execute([$id]);
    $submission = $stmt->fetch();
    if (!$submission) return null;

    $lang = $pdo->prepare('SELECT language FROM submission_languages WHERE submission_id = ? ORDER BY language');
    $lang->execute([$id]);
    $submission['languages'] = array_column($lang->fetchAll(), 'language');

    $fileStmt = $pdo->prepare('SELECT id, file_name, file_size, uploaded_at, storage_name FROM submission_files WHERE submission_id = ? ORDER BY id');
    $fileStmt->execute([$id]);
    $submission['files'] = [];
    foreach ($fileStmt->fetchAll() as $file) {
        $entry = [
            'id' => (int)$file['id'],
            'file_name' => $file['file_name'],
            'file_size' => (int)$file['file_size'],
            'uploaded_at' => $file['uploaded_at'],
        ];
        if ($withContents) {
            $path = UPLOAD_DIR . '/' . $id . '/' . $file['storage_name'];
            $entry['content'] = is_file($path) ? (string)file_get_contents($path) : '[File unavailable]';
        }
        $submission['files'][] = $entry;
    }

    $reviewStmt = $pdo->prepare(
        "SELECT r.id, r.feedback, r.status, r.created_at, u.name AS admin_name
         FROM reviews r JOIN users u ON u.id = r.admin_id
         WHERE r.submission_id = ? ORDER BY r.id DESC LIMIT 1"
    );
    $reviewStmt->execute([$id]);
    $submission['review'] = $reviewStmt->fetch() ?: null;
    return $submission;
}

function delete_submission_files(int $id): void {
    $dir = UPLOAD_DIR . '/' . $id;
    if (!is_dir($dir)) return;
    foreach (glob($dir . '/*') ?: [] as $file) {
        if (is_file($file)) @unlink($file);
    }
    @rmdir($dir);
}
