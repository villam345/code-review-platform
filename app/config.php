<?php
declare(strict_types=1);

/*
 * LOCAL XAMPP DEFAULTS.
 * Replace these values with the database details from your hosting provider
 * before deployment. Never put database credentials in frontend JavaScript.
 */
const DB_HOST = 'localhost';
const DB_NAME = 'code_review_platform';
const DB_USER = 'root';
const DB_PASS = '';

const MAX_FILE_SIZE = 5 * 1024 * 1024; // 5 MB per file
const MAX_FILES = 10;
const MAX_TOTAL_UPLOAD = 20 * 1024 * 1024; // 20 MB per submission
const UPLOAD_DIR = __DIR__ . '/../storage/uploads';
