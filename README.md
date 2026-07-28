# PHP version of Nextern

This folder contains a PHP-based version of the booking and admin system that can run on standard PHP hosting.

## Files
- index.php - public website frontend
- admin.php - owner dashboard frontend
- api.php - PHP endpoints for booking and admin actions
- includes/functions.php - shared booking and auth helpers
- data/bookings.json - fallback storage
- db_setup.sql - SQL script for phpMyAdmin import

## phpMyAdmin setup
1. Open phpMyAdmin and create a new database, for example: nextern_php
2. Import the SQL from [db_setup.sql](db_setup.sql)
3. Create a file named .env inside the php-version folder and add your database details:

```env
DB_HOST=localhost
DB_PORT=3306
DB_NAME=nextern_php
DB_USER=root
DB_PASS_ENCRYPTED=v1:your-aes-256-gcm-encrypted-database-password
DB_CHARSET=utf8mb4
```

The database password and SMTP password belong only in this ignored `.env` file (or your host's secret settings), never in `db_setup.sql` or source code. `DB_PORT` and `SMTP_PORT` are also environment variables.

4. Upload the whole php-version folder to your PHP host or place it in the web root.
5. If your hosting panel already provides environment variables, those will be used automatically.

## Email notifications

Add these values to `.env` to send a separate confirmation to the customer and a notification to every configured admin email. Use `tls` for port 587, `ssl` for port 465, or `none` only when your provider explicitly requires it.

```env
SMTP_HOST=smtp.example.com
SMTP_PORT=587
SMTP_ENCRYPTION=tls
SMTP_USER=notifications@example.com
SMTP_PASS_ENCRYPTED=v1:your-aes-256-gcm-encrypted-smtp-app-password
SMTP_FROM=notifications@example.com
SMTP_FROM_NAME=Nextern
ADMIN_EMAILS=owner@example.com,team@example.com
CREDENTIAL_ENCRYPTION_KEY=your-base64-encoded-32-byte-key
```

## Default owner credentials
- Username: owner
- Password: change-me-now
