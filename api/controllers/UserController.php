<?php

class UserController
{
    public static function register(array $input): void
    {
        $username = trim($input['username'] ?? '');
        $password = $input['password'] ?? '';

        if ($username === '' || $password === '') {
            jsonResponse(['error' => 'Korisničko ime i lozinka su obavezni.'], 400);
            return;
        }

        if (strlen($password) < 4) {
            jsonResponse(['error' => 'Lozinka mora imati bar 4 karaktera.'], 400);
            return;
        }

        $db = getDB();
        $check = $db->prepare('SELECT id FROM users WHERE username = ?');
        $check->execute([$username]);

        if ($check->fetch()) {
            jsonResponse(['error' => 'Korisničko ime je zauzeto.'], 409);
            return;
        }

        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $insert = $db->prepare('INSERT INTO users (username, password) VALUES (?, ?)');
        $insert->execute([$username, $hashed]);

        $_SESSION['user_id'] = (int) $db->lastInsertId();
        $_SESSION['username'] = $username;

        jsonResponse(['id' => $_SESSION['user_id'], 'username' => $username], 201);
    }

    public static function login(array $input): void
    {
        $username = trim($input['username'] ?? '');
        $password = $input['password'] ?? '';

        $db = getDB();
        $stmt = $db->prepare('SELECT * FROM users WHERE username = ?');
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user || !password_verify($password, $user['password'])) {
            jsonResponse(['error' => 'Pogrešno korisničko ime ili lozinka.'], 401);
            return;
        }

        $_SESSION['user_id'] = (int) $user['id'];
        $_SESSION['username'] = $user['username'];

        jsonResponse(['id' => $user['id'], 'username' => $user['username']]);
    }

    public static function logout(): void
    {
        $_SESSION = [];
        session_destroy();
        jsonResponse(['message' => 'Odjavljeni ste.']);
    }
}
