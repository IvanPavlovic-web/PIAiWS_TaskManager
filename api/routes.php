<?php

require_once __DIR__ . '/controllers/UserController.php';
require_once __DIR__ . '/controllers/CategoryController.php';
require_once __DIR__ . '/controllers/TaskController.php';

function dispatch(string $resource, ?int $id, string $method, array $input, array $query): void
{
    switch ($resource) {
        case 'register':
            if ($method === 'POST') {
                UserController::register($input);
                return;
            }
            break;

        case 'login':
            if ($method === 'POST') {
                UserController::login($input);
                return;
            }
            break;

        case 'logout':
            if ($method === 'GET') {
                UserController::logout();
                return;
            }
            break;

        case 'categories':
            $userId = requireAuth();

            if ($method === 'GET') {
                CategoryController::getAll($userId);
                return;
            }
            if ($method === 'POST') {
                CategoryController::create($userId, $input);
                return;
            }
            if ($method === 'PUT' && $id !== null) {
                CategoryController::update($id, $userId, $input);
                return;
            }
            if ($method === 'DELETE' && $id !== null) {
                CategoryController::delete($id, $userId);
                return;
            }
            break;

        case 'tasks':
            $userId = requireAuth();

            if ($method === 'GET') {
                $filters = [
                    'category' => $query['category'] ?? null,
                    'status' => $query['status'] ?? null,
                    'search' => $query['search'] ?? null,
                ];
                TaskController::getAll($userId, $filters);
                return;
            }
            if ($method === 'POST') {
                TaskController::create($userId, $input);
                return;
            }
            if ($method === 'PUT' && $id !== null) {
                TaskController::update($id, $userId, $input);
                return;
            }
            if ($method === 'DELETE' && $id !== null) {
                TaskController::delete($id, $userId);
                return;
            }
            break;
    }

    jsonResponse(['error' => 'Ruta nije pronađena.'], 404);
}
