<?php
$raw = file_get_contents('php://input');
echo json_encode(['raw' => $raw, 'post' => $_POST, 'contentType' => $_SERVER['CONTENT_TYPE'] ?? null]);
