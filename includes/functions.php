<?php
session_start();

date_default_timezone_set('Asia/Kolkata');

if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
}

function loadEnvFile(): array {
    static $loaded = false;
    static $values = [];

    if ($loaded) {
        return $values;
    }

    $paths = [
        dirname(__DIR__) . '/.env',
        dirname(__DIR__, 2) . '/.env'
    ];

    foreach ($paths as $path) {
        if (!is_file($path)) {
            continue;
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false) {
            continue;
        }

        foreach ($lines as $line) {
            $trimmed = trim($line);
            if ($trimmed === '' || str_starts_with($trimmed, '#')) {
                continue;
            }

            [$name, $rawValue] = array_pad(explode('=', $trimmed, 2), 2, '');
            $name = trim($name);
            $value = trim($rawValue);

            if ((str_starts_with($value, '"') && str_ends_with($value, '"')) || (str_starts_with($value, "'") && str_ends_with($value, "'"))) {
                $value = substr($value, 1, -1);
            }

            if ($name !== '') {
                $values[$name] = $value;
                $_ENV[$name] = $value;
            }
        }
    }

    $loaded = true;
    return $values;
}

function getEnvValue(string $key, mixed $default = null): string {
    loadEnvFile();

    if (isset($_ENV[$key])) {
        return (string) $_ENV[$key];
    }

    if (isset($_SERVER[$key])) {
        return (string) $_SERVER[$key];
    }

    $value = getenv($key);
    return $value === false ? (string) $default : (string) $value;
}

/**
 * Decrypts a v1 AES-256-GCM value stored in the environment. The encryption
 * key is kept separately in CREDENTIAL_ENCRYPTION_KEY.
 */
function getEncryptedEnvValue(string $key): string {
    $encodedPayload = getEnvValue($key, '');
    if (!str_starts_with($encodedPayload, 'v1:')) {
        throw new RuntimeException($key . ' must be an AES-256-GCM encrypted v1 value.');
    }

    $encryptionKey = base64_decode(getEnvValue('CREDENTIAL_ENCRYPTION_KEY', ''), true);
    $payload = base64_decode(substr($encodedPayload, 3), true);
    if ($encryptionKey === false || strlen($encryptionKey) !== 32 || $payload === false || strlen($payload) < 29) {
        throw new RuntimeException('Invalid encrypted credential configuration.');
    }

    $iv = substr($payload, 0, 12);
    $tag = substr($payload, 12, 16);
    $ciphertext = substr($payload, 28);
    $value = openssl_decrypt($ciphertext, 'aes-256-gcm', $encryptionKey, OPENSSL_RAW_DATA, $iv, $tag);
    if ($value === false) {
        throw new RuntimeException('Could not decrypt ' . $key . '.');
    }

    return $value;
}

function getDataFilePath(): string {
    return dirname(__DIR__) . '/data/bookings.json';
}

function getAdminSettingsFilePath(): string {
    return dirname(__DIR__) . '/data/admin-settings.json';
}

function parseEmailList(string $value): array {
    return array_values(array_unique(array_filter(array_map(static function ($entry) {
        return trim((string) $entry);
    }, preg_split('/[,\n;]/', $value) ?: []), static function ($entry) {
        return $entry !== '' && filter_var($entry, FILTER_VALIDATE_EMAIL) !== false;
    })));
}

function readAdminSettings(): array {
    try {
        ensureDatabase();
        $statement = getDbConnection()->prepare('SELECT setting_value FROM app_settings WHERE setting_key = ? LIMIT 1');
        $statement->execute(['admin_emails']);
        $storedValue = $statement->fetchColumn();
        if ($storedValue !== false) {
            $adminEmails = json_decode((string) $storedValue, true);
            if (is_array($adminEmails)) {
                return ['adminEmails' => parseEmailList(implode(',', $adminEmails))];
            }
        }
    } catch (PDOException $exception) {
        error_log('Database admin settings read failed: ' . $exception->getMessage());
    }

    $file = getAdminSettingsFilePath();
    if (!file_exists($file)) {
        file_put_contents($file, json_encode(['adminEmails' => []], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        return ['adminEmails' => []];
    }

    $json = file_get_contents($file);
    $data = json_decode($json, true);
    if (!is_array($data)) {
        return ['adminEmails' => []];
    }

    $adminEmails = $data['adminEmails'] ?? [];
    if (!is_array($adminEmails)) {
        $adminEmails = parseEmailList((string) $adminEmails);
    }

    return ['adminEmails' => parseEmailList(implode(',', $adminEmails))];
}

function writeAdminSettings(array $settings): bool {
    $adminEmails = parseEmailList(implode(',', (array) ($settings['adminEmails'] ?? [])));
    try {
        ensureDatabase();
        $statement = getDbConnection()->prepare(
            'INSERT INTO app_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)'
        );
        $statement->execute(['admin_emails', json_encode($adminEmails, JSON_UNESCAPED_SLASHES)]);
        return true;
    } catch (PDOException $exception) {
        error_log('Database admin settings write failed: ' . $exception->getMessage());
    }

    $file = getAdminSettingsFilePath();
    return (bool) file_put_contents($file, json_encode(['adminEmails' => $adminEmails], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
}

function getAdminEmails(): array {
    $settings = readAdminSettings();
    $configured = $settings['adminEmails'] ?? [];
    $envEmails = parseEmailList(getEnvValue('ADMIN_EMAILS', ''));
    $merged = $configured !== [] ? $configured : $envEmails;
    return array_values(array_unique(array_filter(array_map('trim', $merged))));
}

function getMailConfig(): array {
    $encryption = strtolower(trim(getEnvValue('SMTP_ENCRYPTION', '')));
    if ($encryption === '') {
        $encryption = getEnvValue('SMTP_SECURE', 'false') === 'true' ? 'tls' : 'none';
    }

    return [
        'host' => getEnvValue('SMTP_HOST', ''),
        'port' => (int) getEnvValue('SMTP_PORT', '587'),
        'encryption' => in_array($encryption, ['tls', 'ssl', 'none'], true) ? $encryption : 'none',
        'user' => getEnvValue('SMTP_USER', ''),
        'pass' => getEncryptedEnvValue('SMTP_PASS_ENCRYPTED'),
        'from' => getEnvValue('SMTP_FROM', getEnvValue('SMTP_USER', 'noreply@nexternconsultancy.com')),
        'fromName' => getEnvValue('SMTP_FROM_NAME', 'Nextern')
    ];
}

function sendBookingNotifications(array $booking): array {
    $customerEmail = trim((string) ($booking['email'] ?? ''));
    $adminEmails = getAdminEmails();
    $recipients = array_values(array_unique(array_filter([$customerEmail, ...$adminEmails])));
    if ($customerEmail === '' || filter_var($customerEmail, FILTER_VALIDATE_EMAIL) === false) {
        return ['ok' => false, 'reason' => 'no_recipients'];
    }

    $config = getMailConfig();
    if ($config['host'] === '' || $config['user'] === '' || $config['pass'] === '') {
        return ['ok' => false, 'reason' => 'smtp_not_configured'];
    }

    if (!class_exists('PHPMailer\\PHPMailer\\PHPMailer')) {
        return ['ok' => false, 'reason' => 'mailer_unavailable'];
    }

    try {
        $createMailer = static function () use ($config): PHPMailer\PHPMailer\PHPMailer {
            $mail = new PHPMailer\PHPMailer\PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = $config['host'];
            $mail->Port = $config['port'];
            $mail->SMTPAuth = true;
            $mail->Username = $config['user'];
            $mail->Password = $config['pass'];
            $mail->CharSet = 'UTF-8';
            $mail->SMTPAutoTLS = $config['encryption'] !== 'none';
            if ($config['encryption'] === 'tls') {
                $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            } elseif ($config['encryption'] === 'ssl') {
                $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
            }
            $mail->setFrom($config['from'], $config['fromName']);
            return $mail;
        };

        $customerMail = $createMailer();
        $customerMail->addAddress($customerEmail, $booking['fullName'] ?? '');
        $customerMail->Subject = 'We received your Nextern meeting request';
        $customerMail->Body = buildBookingEmailText($booking, true);
        $customerMail->AltBody = $customerMail->Body;
        $customerMail->send();

        if ($adminEmails !== []) {
            $adminMail = $createMailer();
            foreach ($adminEmails as $adminEmail) {
                $adminMail->addAddress($adminEmail);
            }
            $adminMail->Subject = 'New meeting request from ' . ($booking['fullName'] ?? 'a guest');
            $adminMail->Body = buildBookingEmailText($booking, false);
            $adminMail->AltBody = $adminMail->Body;
            $adminMail->send();
        }

        return ['ok' => true, 'recipients' => $recipients];
    } catch (Throwable $e) {
        error_log('Booking notification failed: ' . $e->getMessage());
        return ['ok' => false, 'reason' => 'send_failed'];
    }
}

function getDbConfig(): array {
    return [
        'host' => getEnvValue('DB_HOST', '127.0.0.1'),
        'port' => getEnvValue('DB_PORT', '3306'),
        'name' => getEnvValue('DB_NAME', 'nextern_php'),
        'user' => getEnvValue('DB_USER', 'root'),
        'pass' => getEncryptedEnvValue('DB_PASS_ENCRYPTED'),
        'charset' => getEnvValue('DB_CHARSET', 'utf8mb4')
    ];
}

function getDbConnection(): PDO {
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $config = getDbConfig();
    $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=%s', $config['host'], $config['port'], $config['name'], $config['charset']);

    $pdo = new PDO($dsn, $config['user'], $config['pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);

    return $pdo;
}

function ensureDatabase(): void {
    try {
        $pdo = getDbConnection();
        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS bookings (
                id VARCHAR(100) PRIMARY KEY,
                full_name VARCHAR(255) NOT NULL,
                email VARCHAR(255) NOT NULL,
                phone VARCHAR(50) DEFAULT '',
                business_name VARCHAR(255) DEFAULT '',
                meeting_at VARCHAR(50) NOT NULL,
                meeting_type VARCHAR(100) DEFAULT 'Strategy Call',
                notes TEXT,
                created_at VARCHAR(50) NOT NULL,
                UNIQUE KEY uq_bookings_meeting_at (meeting_at),
                KEY idx_bookings_meeting_at (meeting_at),
                KEY idx_bookings_created_at (created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
        );
        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS app_settings (
                setting_key VARCHAR(100) PRIMARY KEY,
                setting_value TEXT NOT NULL,
                updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
        );
    } catch (PDOException $e) {
        error_log('Database initialization failed: ' . $e->getMessage());
    }
}

function readJsonBookings(): array {
    $file = getDataFilePath();
    if (!file_exists($file)) {
        file_put_contents($file, '[]');
    }

    $json = file_get_contents($file);
    $data = json_decode($json, true);
    return is_array($data) ? $data : [];
}

function loadBookings(): array {
    try {
        ensureDatabase();
        $stmt = getDbConnection()->prepare(
            'SELECT id, full_name AS fullName, email, phone, business_name AS businessName, meeting_at AS meetingAt, meeting_type AS meetingType, notes, created_at AS createdAt FROM bookings ORDER BY meeting_at ASC'
        );
        $stmt->execute();
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log('Database read failed: ' . $e->getMessage());
        return readJsonBookings();
    }
}

function saveBookings(array $bookings): void {
    $file = getDataFilePath();
    file_put_contents($file, json_encode($bookings, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
}

function createBooking(array $booking): array {
    try {
        ensureDatabase();
        $stmt = getDbConnection()->prepare(
            'INSERT INTO bookings (id, full_name, email, phone, business_name, meeting_at, meeting_type, notes, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $booking['id'],
            $booking['fullName'] ?? '',
            $booking['email'] ?? '',
            $booking['phone'] ?? '',
            $booking['businessName'] ?? '',
            $booking['meetingAt'] ?? '',
            $booking['meetingType'] ?? 'Strategy Call',
            $booking['notes'] ?? '',
            $booking['createdAt'] ?? ''
        ]);
        return $booking;
    } catch (PDOException $e) {
        error_log('Database insert failed: ' . $e->getMessage());
        $bookings = readJsonBookings();
        $bookings[] = $booking;
        saveBookings($bookings);
        return $booking;
    }
}

function bookingExistsAt(string $meetingAt): bool {
    try {
        ensureDatabase();
        $stmt = getDbConnection()->prepare('SELECT 1 FROM bookings WHERE meeting_at = ? LIMIT 1');
        $stmt->execute([$meetingAt]);
        return (bool) $stmt->fetchColumn();
    } catch (PDOException $e) {
        error_log('Database conflict check failed: ' . $e->getMessage());
        $bookings = readJsonBookings();
        foreach ($bookings as $booking) {
            if (($booking['meetingAt'] ?? '') === $meetingAt) {
                return true;
            }
        }
        return false;
    }
}

function getBookingById(string $id): ?array {
    try {
        ensureDatabase();
        $stmt = getDbConnection()->prepare(
            'SELECT id, full_name AS fullName, email, phone, business_name AS businessName, meeting_at AS meetingAt, meeting_type AS meetingType, notes, created_at AS createdAt FROM bookings WHERE id = ? LIMIT 1'
        );
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    } catch (PDOException $e) {
        error_log('Database booking lookup failed: ' . $e->getMessage());
        $bookings = readJsonBookings();
        foreach ($bookings as $booking) {
            if (($booking['id'] ?? '') === $id) {
                return $booking;
            }
        }
        return null;
    }
}

function updateBookingById(string $id, array $payload): ?array {
    try {
        ensureDatabase();
        $fields = [];
        $values = [];

        foreach ([
            'fullName' => 'full_name',
            'email' => 'email',
            'phone' => 'phone',
            'businessName' => 'business_name',
            'meetingAt' => 'meeting_at',
            'meetingType' => 'meeting_type',
            'notes' => 'notes'
        ] as $source => $target) {
            if (array_key_exists($source, $payload)) {
                $fields[] = $target . ' = ?';
                $values[] = $payload[$source];
            }
        }

        if ($fields === []) {
            return getBookingById($id);
        }

        $values[] = $id;
        $stmt = getDbConnection()->prepare('UPDATE bookings SET ' . implode(', ', $fields) . ' WHERE id = ?');
        $stmt->execute($values);
        return getBookingById($id);
    } catch (PDOException $e) {
        error_log('Database update failed: ' . $e->getMessage());
        $bookings = readJsonBookings();
        foreach ($bookings as &$booking) {
            if (($booking['id'] ?? '') === $id) {
                foreach ($payload as $key => $value) {
                    $booking[$key] = $value;
                }
                saveBookings($bookings);
                return $booking;
            }
        }
        return null;
    }
}

function deleteBookingById(string $id): ?array {
    try {
        ensureDatabase();
        $booking = getBookingById($id);
        if (!$booking) {
            return null;
        }
        $stmt = getDbConnection()->prepare('DELETE FROM bookings WHERE id = ?');
        $stmt->execute([$id]);
        return $booking;
    } catch (PDOException $e) {
        error_log('Database delete failed: ' . $e->getMessage());
        $bookings = readJsonBookings();
        $remaining = [];
        foreach ($bookings as $booking) {
            if (($booking['id'] ?? '') !== $id) {
                $remaining[] = $booking;
            }
        }
        saveBookings($remaining);
        foreach ($bookings as $booking) {
            if (($booking['id'] ?? '') === $id) {
                return $booking;
            }
        }
        return null;
    }
}

function getOwnerUsername(): string {
    return (string) (getenv('OWNER_USERNAME') ?: 'owner');
}

function getOwnerPassword(): string {
    return (string) (getenv('OWNER_PASSWORD') ?: 'change-me-now');
}

function isOwnerAuthenticated(): bool {
    return !empty($_SESSION['isOwner']);
}

function requireOwner(): void {
    if (!isOwnerAuthenticated()) {
        http_response_code(401);
        echo json_encode(['message' => 'Unauthorized. Owner login required.']);
        exit;
    }
}

function readJsonBody(): array {
    $raw = file_get_contents('php://input');
    if ($raw === '') {
        return [];
    }

    $trimmed = trim($raw);
    if ($trimmed === '') {
        return [];
    }

    $data = json_decode($trimmed, true);
    if (is_array($data)) {
        return $data;
    }

    $pairs = explode(',', str_replace(['{', '}', '"'], '', $trimmed));
    $parsed = [];
    foreach ($pairs as $pair) {
        if (str_contains($pair, ':')) {
            [$key, $value] = explode(':', $pair, 2);
            $parsed[trim($key)] = trim($value);
        }
    }

    return $parsed;
}

function jsonResponse(array $payload, int $status = 200): void {
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($payload);
    exit;
}

function sanitizeCsvValue(mixed $value): string {
    $text = (string) ($value ?? '');
    return '"' . str_replace('"', '""', $text) . '"';
}

function getBusinessTimezone(): DateTimeZone {
    return new DateTimeZone('Asia/Kolkata');
}

function formatMeetingTimeForEmail(string $meetingAt): string {
    try {
        return (new DateTimeImmutable($meetingAt))->setTimezone(getBusinessTimezone())->format('l, j F Y, g:i A T');
    } catch (Throwable $exception) {
        return $meetingAt;
    }
}

function buildBookingEmailText(array $booking, bool $isCustomer): string {
    $name = $booking['fullName'] ?? 'there';
    $details = sprintf(
        "Meeting time: %s\nEmail: %s\nPhone: %s\nBusiness: %s\nMeeting type: %s\nNotes: %s",
        formatMeetingTimeForEmail((string) ($booking['meetingAt'] ?? '')),
        $booking['email'] ?? 'N/A',
        ($booking['phone'] ?? '') ?: 'N/A',
        ($booking['businessName'] ?? '') ?: 'N/A',
        ($booking['meetingType'] ?? '') ?: 'Strategy Call',
        ($booking['notes'] ?? '') ?: 'N/A'
    );

    return $isCustomer
        ? "Hi {$name},\n\nThank you for requesting a meeting with Nextern. We have received your request and our team will contact you shortly.\n\n{$details}\n"
        : "New meeting request from {$name}.\n\n{$details}\n";
}

function getBusinessSlots(): array {
    return [
        '09:00', '09:30', '10:00', '10:30', '11:00', '11:30',
        '12:00', '12:30', '13:00', '13:30', '14:00', '14:30',
        '15:00', '15:30', '16:00', '16:30', '17:00', '17:30'
    ];
}

function getOccupiedSlotsForDate(string $dateText): array {
    $bookings = loadBookings();
    $occupied = [];

    foreach ($bookings as $booking) {
        try {
            $bookingDate = new DateTimeImmutable($booking['meetingAt'] ?? '');
        } catch (Exception $exception) {
            continue;
        }

        $businessDate = $bookingDate->setTimezone(getBusinessTimezone());
        if ($businessDate->format('Y-m-d') === $dateText) {
            $occupied[] = $businessDate->format('H:i');
        }
    }

    sort($occupied);
    return array_values(array_unique($occupied));
}

function parseBusinessMeetingTime(string $meetingAt): ?DateTimeImmutable {
    $timezone = getBusinessTimezone();
    $date = DateTimeImmutable::createFromFormat('!Y-m-d\\TH:i', $meetingAt, $timezone);
    $errors = DateTimeImmutable::getLastErrors();

    if ($date === false || ($errors !== false && ($errors['warning_count'] > 0 || $errors['error_count'] > 0))) {
        return null;
    }

    return $date;
}

function generateBookingId(): string {
    return 'bk_' . time() . '_' . random_int(1000, 999999);
}
