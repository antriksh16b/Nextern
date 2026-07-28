<?php
require_once __DIR__ . '/includes/functions.php';

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$path = trim(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH), '/');

if ($method === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$route = trim((string) ($_GET['route'] ?? ''));
if ($route === '' && $path !== '') {
    $segments = array_values(array_filter(explode('/', $path), static function ($segment) {
        return $segment !== '';
    }));

    if (!empty($segments) && $segments[0] === 'api') {
        $route = implode(array_slice($segments, 1), '/');

        if (preg_match('#^admin/bookings/([^/]+)$#', $route, $matches)) {
            $_GET['id'] = $matches[1];
            $route = 'admin/bookings';
        }
    }
}

switch ($route) {
    case 'availability':
    case 'api/availability':
        $dateText = trim((string) ($_GET['date'] ?? ''));
        $requestedDate = DateTimeImmutable::createFromFormat('!Y-m-d', $dateText, getBusinessTimezone());
        $dateErrors = DateTimeImmutable::getLastErrors();
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateText) || $requestedDate === false || ($dateErrors !== false && ($dateErrors['warning_count'] > 0 || $dateErrors['error_count'] > 0))) {
            jsonResponse(['message' => 'date must be in YYYY-MM-DD format.'], 400);
        }

        jsonResponse(['date' => $dateText, 'occupiedSlots' => getOccupiedSlotsForDate($dateText)]);
        break;

    case 'bookings':
    case 'api/bookings':
        if ($method !== 'POST') {
            jsonResponse(['message' => 'Method not allowed.'], 405);
        }

        $payload = readJsonBody();
        if (empty($payload) && !empty($_POST)) {
            $payload = $_POST;
        } elseif (!empty($_POST)) {
            $payload = array_merge($_POST, $payload);
        }

        $fullName = trim((string) ($payload['fullName'] ?? $payload['name'] ?? ''));
        $email = trim(strtolower((string) ($payload['email'] ?? '')));
        $meetingAt = trim((string) ($payload['meetingAt'] ?? $payload['meeting_at'] ?? ''));

        if ($fullName === '' || $email === '' || $meetingAt === '') {
            jsonResponse(['message' => 'Full name, email, and meeting date/time are required.'], 400);
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            jsonResponse(['message' => 'Please provide a valid email address.'], 400);
        }

        $meetingDate = parseBusinessMeetingTime($meetingAt);
        if ($meetingDate === null) {
            jsonResponse(['message' => 'Please choose a valid meeting date and time.'], 400);
        }

        if ($meetingDate <= new DateTimeImmutable('now', getBusinessTimezone())) {
            jsonResponse(['message' => 'Please choose a future meeting time.'], 400);
        }

        if (!in_array($meetingDate->format('H:i'), getBusinessSlots(), true)) {
            jsonResponse(['message' => 'Please choose one of the available time slots.'], 400);
        }

        $normalizedSlot = $meetingDate->format(DateTimeInterface::ATOM);

        if (bookingExistsAt($normalizedSlot)) {
            jsonResponse(['message' => 'This slot is already booked. Please choose another time.'], 409);
        }

        $booking = [
            'id' => generateBookingId(),
            'fullName' => $fullName,
            'email' => $email,
            'phone' => trim((string) ($payload['phone'] ?? '')),
            'businessName' => trim((string) ($payload['businessName'] ?? '')),
            'meetingAt' => $normalizedSlot,
            'meetingType' => trim((string) ($payload['meetingType'] ?? 'Strategy Call')),
            'notes' => trim((string) ($payload['notes'] ?? '')),
            'createdAt' => gmdate('c')
        ];

        createBooking($booking);
        $mailResult = sendBookingNotifications($booking);
        $response = ['message' => 'Meeting scheduled successfully. Our team will contact you shortly.', 'bookingId' => $booking['id'], 'emailSent' => $mailResult['ok'] ?? false];
        if (!($mailResult['ok'] ?? false)) {
            $response['message'] = 'Meeting scheduled successfully, but the confirmation email could not be sent.';
        }
        jsonResponse($response, 201);
        break;

    case 'admin/login':
    case 'api/admin/login':
        if ($method !== 'POST') {
            jsonResponse(['message' => 'Method not allowed.'], 405);
        }

        $payload = readJsonBody();
        $username = trim((string) ($payload['username'] ?? ''));
        $password = (string) ($payload['password'] ?? '');

        if ($username === getOwnerUsername() && $password === getOwnerPassword()) {
            $_SESSION['isOwner'] = true;
            $_SESSION['username'] = getOwnerUsername();
            jsonResponse(['message' => 'Login successful.']);
        }

        jsonResponse(['message' => 'Invalid owner credentials.'], 401);
        break;

    case 'admin/logout':
    case 'api/admin/logout':
        if ($method !== 'POST') {
            jsonResponse(['message' => 'Method not allowed.'], 405);
        }

        requireOwner();
        session_destroy();
        jsonResponse(['message' => 'Logged out.']);
        break;

    case 'admin/session':
    case 'api/admin/session':
        if ($method !== 'GET') {
            jsonResponse(['message' => 'Method not allowed.'], 405);
        }

        if (isOwnerAuthenticated()) {
            jsonResponse(['authenticated' => true, 'username' => $_SESSION['username'] ?? getOwnerUsername()]);
        }

        jsonResponse(['authenticated' => false]);
        break;

    case 'admin/bookings':
    case 'api/admin/bookings':
        if ($method === 'GET') {
            requireOwner();
            $bookings = loadBookings();
            usort($bookings, function ($a, $b) {
                return strtotime($a['meetingAt'] ?? '0') <=> strtotime($b['meetingAt'] ?? '0');
            });
            jsonResponse(['total' => count($bookings), 'bookings' => $bookings]);
        }

        if ($method === 'DELETE') {
            requireOwner();
            $id = trim((string) ($_GET['id'] ?? ''));
            $deleted = deleteBookingById($id);

            if ($deleted === null) {
                jsonResponse(['message' => 'Booking not found.'], 404);
            }

            jsonResponse(['message' => 'Booking cancelled successfully.', 'deleted' => $deleted]);
        }

        if ($method === 'PUT') {
            requireOwner();
            $id = trim((string) ($_GET['id'] ?? ''));
            $payload = readJsonBody();
            $updated = updateBookingById($id, $payload);

            if ($updated === null) {
                jsonResponse(['message' => 'Booking not found.'], 404);
            }

            jsonResponse(['message' => 'Booking updated successfully.', 'booking' => $updated]);
        }

        jsonResponse(['message' => 'Method not allowed.'], 405);
        break;

    case 'admin/settings':
    case 'api/admin/settings':
        requireOwner();
        if ($method === 'GET') {
            jsonResponse(['adminEmails' => readAdminSettings()['adminEmails'] ?? []]);
        }

        if ($method === 'POST') {
            $payload = readJsonBody();
            $adminEmails = is_array($payload['adminEmails'] ?? null) ? $payload['adminEmails'] : parseEmailList((string) ($payload['adminEmails'] ?? ''));
            $saved = writeAdminSettings(['adminEmails' => array_values(array_filter(array_map('trim', $adminEmails)))]);
            if ($saved === false) {
                jsonResponse(['message' => 'Could not save admin settings.'], 500);
            }
            jsonResponse(['message' => 'Admin notification settings updated.', 'adminEmails' => $saved ? array_values(array_filter(array_map('trim', $adminEmails))) : []]);
        }

        jsonResponse(['message' => 'Method not allowed.'], 405);
        break;

    case 'admin/export.csv':
    case 'api/admin/export.csv':
        requireOwner();
        $bookings = loadBookings();
        $header = ['ID', 'Full Name', 'Email', 'Phone', 'Business Name', 'Meeting Date (ISO)', 'Meeting Type', 'Notes', 'Booked At (ISO)'];
        $rows = [];
        foreach ($bookings as $booking) {
            $rows[] = [$booking['id'] ?? '', $booking['fullName'] ?? '', $booking['email'] ?? '', $booking['phone'] ?? '', $booking['businessName'] ?? '', $booking['meetingAt'] ?? '', $booking['meetingType'] ?? '', $booking['notes'] ?? '', $booking['createdAt'] ?? ''];
        }

        $output = [];
        $output[] = implode(array_map('sanitizeCsvValue', $header), ',');
        foreach ($rows as $row) {
            $output[] = implode(array_map('sanitizeCsvValue', $row), ',');
        }

        header('Content-Type', 'text/csv; charset=utf-8');
        header('Content-Disposition', 'attachment; filename="bookings-export.csv"');
        echo implode($output, PHP_EOL);
        exit;
        break;

    default:
        http_response_code(404);
        echo json_encode(['message' => 'Route not found.']);
        exit;
}
