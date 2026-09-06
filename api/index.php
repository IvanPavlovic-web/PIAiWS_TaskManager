<?php

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/routes.php';

startSession();

$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// izdvoji dio putanje poslije /api/
$path = preg_replace('#^.*?/api/?#', '', $path);
$path = trim($path, '/');
$segments = $path === '' ? [] : explode('/', $path);

$resource = $segments[0] ?? '';
$id = isset($segments[1]) && is_numeric($segments[1]) ? (int) $segments[1] : null;

$input = [];
if (in_array($method, ['POST', 'PUT'], true)) {
    $raw = file_get_contents('php://input');
    $input = json_decode($raw, true) ?: [];
}

dispatch($resource, $id, $method, $input, $_GET);
