<?php

class CategoryController
{
    public static function getAll(int $userId): void
    {
        $db = getDB();
        $stmt = $db->prepare('SELECT * FROM categories WHERE user_id = ? ORDER BY name ASC');
        $stmt->execute([$userId]);
        jsonResponse(['data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
    }

    public static function create(int $userId, array $input): void
    {
        $name = trim($input['name'] ?? '');

        if ($name === '') {
            jsonResponse(['error' => 'Naziv kategorije je obavezan.'], 400);
            return;
        }

        $db = getDB();
        $stmt = $db->prepare('INSERT INTO categories (name, user_id) VALUES (?, ?)');
        $stmt->execute([$name, $userId]);

        jsonResponse(['id' => (int) $db->lastInsertId(), 'name' => $name, 'user_id' => $userId], 201);
    }

    public static function update(int $id, int $userId, array $input): void
    {
        $name = trim($input['name'] ?? '');

        if ($name === '') {
            jsonResponse(['error' => 'Naziv kategorije je obavezan.'], 400);
            return;
        }

        $db = getDB();
        $owned = self::fetchOwned($db, $id, $userId);

        if (!$owned) {
            jsonResponse(['error' => 'Kategorija nije pronađena.'], 404);
            return;
        }

        $stmt = $db->prepare('UPDATE categories SET name = ? WHERE id = ? AND user_id = ?');
        $stmt->execute([$name, $id, $userId]);

        jsonResponse(['id' => $id, 'name' => $name, 'user_id' => $userId]);
    }

    public static function delete(int $id, int $userId): void
    {
        $db = getDB();
        $owned = self::fetchOwned($db, $id, $userId);

        if (!$owned) {
            jsonResponse(['error' => 'Kategorija nije pronađena.'], 404);
            return;
        }

        $stmt = $db->prepare('DELETE FROM categories WHERE id = ? AND user_id = ?');
        $stmt->execute([$id, $userId]);

        jsonResponse(['message' => 'Kategorija obrisana.']);
    }

    private static function fetchOwned(PDO $db, int $id, int $userId): ?array
    {
        $stmt = $db->prepare('SELECT * FROM categories WHERE id = ? AND user_id = ?');
        $stmt->execute([$id, $userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }
}
