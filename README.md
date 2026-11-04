# CodeReview Platform

A PHP + MySQL code review portal with developer accounts, admin review tools, multiple source-file uploads, review feedback, and a responsive dashboard.

## Requirements

- PHP 8.1+ (8.2+ recommended)
- MySQL 5.7+ / MariaDB 10.4+
- PHP extensions: PDO, pdo_mysql, fileinfo, mbstring
- XAMPP for local development

## 1. Local setup with XAMPP

1. Copy this folder into `C:\xampp\htdocs\code-review-platform`.
2. Start Apache and MySQL in the XAMPP Control Panel.
3. Open `http://localhost/phpmyadmin`.
4. Import `database/schema.sql` using the Import tab.
5. Check `app/config.php`. The defaults are for a standard XAMPP installation (`root` with a blank password).
6. Ensure `storage/uploads` exists and is writable by PHP.
7. Open `http://localhost/code-review-platform/`.

## 1a. Local setup with PHP's built-in server

From the project directory, run:

```powershell
php -S 127.0.0.1:8080
```

Then open:

- `http://127.0.0.1:8080/index.php`
- `http://localhost:8080/index.php`

## 2. Create the first administrator

Open Command Prompt and run:

```bash
cd C:\xampp\htdocs\code-review-platform
C:\xampp\php\php.exe database\create_admin.php
```

Enter the administrator name, email, and a strong password of at least 12 characters.

Public registration only creates developer accounts. Never add an admin role selector to the public registration form.

## 3. Main features

- Developer registration and login
- Session-based authentication and CSRF tokens
- Role-protected admin functions
- Developer project submission
- Multiple source files per submission (up to 10 files, 5 MB each)
- Admin project list and source-code viewer
- Admin review outcome and written feedback
- Developer review history and status tracking
- Delete reviewed projects owned by the signed-in developer
- Prepared SQL statements and password hashing

## 4. Deploying to free PHP hosting

Choose a host that explicitly supports PHP 8.1+ and MySQL/MariaDB, custom database creation, HTTPS, and writable file storage.

1. Create a MySQL database in the hosting control panel.
2. Import `database/schema.sql` into that database.
3. Update `app/config.php` with the host-provided database host, database name, username, and password.
4. Upload the project files. Ideally configure the web document root to this project's root only if the host supports it. Otherwise upload public files to `public_html` and keep `app/`, `database/`, and `storage/` outside the public web root; adjust include paths if you change the layout.
5. Ensure `storage/uploads` is writable by PHP and is not publicly browsable.
6. Create the first admin using a host-provided terminal/SSH/CLI if available. If the free host does not provide CLI access, create the admin manually with a locally generated `password_hash()` and a SQL insert; do not expose an admin-creation PHP page publicly.
7. Enable HTTPS and test registration, login, uploads, admin review, and developer feedback.
8. Remove test files and keep database backups.

**Free-host limitations:** Many free providers limit upload size, execution time, storage, database connections, or CLI access. Check the provider's current terms and technical limits before choosing one. This project cannot be deployed to static-only hosting such as GitHub Pages because it requires PHP and MySQL.

## 5. Upload constraints

The backend allows text/source-code extensions only. Current limits:
- 10 files per project
- 5 MB per file
- 20 MB total per project

Also configure PHP's `upload_max_filesize`, `post_max_size`, and `max_file_uploads` to values that are at least as large as the application limits.

## 6. Important production checklist

- Replace local database credentials with hosting credentials.
- Keep `app/`, `database/`, and `storage/` inaccessible from the public web.
- Use HTTPS.
- Set strong admin passwords.
- Back up the database and uploads.
- Review host-specific file permissions and PHP limits.
- Add rate limiting and email verification before opening registration to the public.
- Consider malware scanning and stricter content inspection if you later accept more than source-code text files.

## API endpoints

- `GET api/auth/me.php` — current session and CSRF token
- `POST api/auth/register.php` — developer registration
- `POST api/auth/login.php` — login
- `POST api/auth/logout.php` — logout
- `GET api/submissions/list.php` — current user's submissions or all submissions for admin
- `POST api/submissions/create.php` — create submission with multipart files
- `GET api/submissions/detail.php?id=ID` — view project and source files
- `POST api/submissions/delete.php` — delete own reviewed submission
- `GET api/reviews/list.php` — reviews for developer or all reviews for admin
- `POST api/reviews/create.php` — admin saves review
- `GET api/admin/stats.php` — admin dashboard counts
