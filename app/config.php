<?php
declare(strict_types=1);

/*
 * Supports both local XAMPP defaults and Railway's managed MySQL variables.
 * Never expose these credentials in frontend JavaScript.
 */
$DB_HOST = getenv('MYSQLHOST') ?: getenv('DB_HOST') ?: 'localhost';
$DB_NAME = getenv('MYSQLDATABASE') ?: getenv('DB_NAME') ?: 'code_review_platform';
$DB_USER = getenv('MYSQLUSER') ?: getenv('DB_USER') ?: 'root';
$DB_PASS = getenv('MYSQLPASSWORD') ?: getenv('DB_PASS') ?: '';

const MAX_FILE_SIZE = 5 * 1024 * 1024; // 5 MB per file
const MAX_FILES = 10;
const MAX_TOTAL_UPLOAD = 20 * 1024 * 1024; // 20 MB per submission
const UPLOAD_DIR = __DIR__ . '/../storage/uploads';
