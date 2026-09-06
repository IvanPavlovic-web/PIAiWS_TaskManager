<?php

function startSession(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

function currentUserId(): ?int
{
    return $_SESSION['user_id'] ?? null;
}

function requireAuth(): int
{
    $userId = currentUserId();

    if ($userId === null) {
        jsonResponse(['error' => 'Niste prijavljeni.'], 401);
        exit;
    }

    return $userId;
}

function jsonResponse(array $data, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data);
}
