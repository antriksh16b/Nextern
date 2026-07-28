<?php
require_once __DIR__ . '/includes/functions.php';
$payload = readJsonBody();
header('Content-Type: application/json');
echo json_encode(['payload' => $payload, 'post' => $_POST, 'contentType' => $_SERVER['CONTENT_TYPE'] ?? null]);
