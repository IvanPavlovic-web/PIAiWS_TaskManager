<?php

class TaskController
{
    public static function getAll(int $userId, array $filters): void
    {
        $db = getDB();

        $sql = 'SELECT * FROM tasks WHERE user_id = ?';
        $params = [$userId];

        if (!empty($filters['category'])) {
            $sql .= ' AND category_id = ?';
            $params[] = (int) $filters['category'];
        }

        if (!empty($filters['status'])) {
            $sql .= ' AND status = ?';
            $params[] = $filters['status'];
        }

        if (!empty($filters['search'])) {
            $sql .= ' AND title LIKE ?';
            $params[] = '%' . $filters['search'] . '%';
        }

        $sql .= ' ORDER BY due_date IS NULL, due_date ASC, priority DESC';

        $stmt = $db->prepare($sql);
        $stmt->execute($params);

        jsonResponse(['data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
    }

    public static function create(int $userId, array $input): void
    {
        $title = trim($input['title'] ?? '');

        if ($title === '') {
            jsonResponse(['error' => 'Naslov zadatka je obavezan.'], 400);
            return;
        }

        $description = $input['description'] ?? '';
        $dueDate = $input['due_date'] ?? null;
        $priority = isset($input['priority']) ? (int) $input['priority'] : 3;
        $status = $input['status'] ?? 'pending';
        $categoryId = isset($input['category_id']) && $input['category_id'] !== ''
            ? (int) $input['category_id']
            : null;

        if (!self::validStatus($status)) {
            jsonResponse(['error' => 'Nepoznat status.'], 400);
            return;
        }

        if (!self::validPriority($priority)) {
            jsonResponse(['error' => 'Prioritet mora biti između 1 i 5.'], 400);
            return;
        }

        $db = getDB();
        if ($categoryId !== null && !self::isOwnedCategory($db, $userId, $categoryId)) {
            jsonResponse(['error' => 'Kategorija ne pripada ovom korisniku.'], 400);
            return;
        }

        $stmt = $db->prepare('
            INSERT INTO tasks (title, description, due_date, priority, status, category_id, user_id, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ');
        $stmt->execute([
            $title,
            $description,
            $dueDate,
            $priority,
            $status,
            $categoryId,
            $userId,
            date('Y-m-d H:i:s'),
        ]);

        jsonResponse(['id' => (int) $db->lastInsertId()], 201);
    }

    public static function update(int $id, int $userId, array $input): void
    {
        $db = getDB();
        $task = self::fetchOwned($db, $id, $userId);

        if (!$task) {
            jsonResponse(['error' => 'Zadatak nije pronađen.'], 404);
            return;
        }

        $title = trim($input['title'] ?? $task['title']);
        $description = $input['description'] ?? $task['description'];
        $dueDate = array_key_exists('due_date', $input) ? $input['due_date'] : $task['due_date'];
        $priority = isset($input['priority']) ? (int) $input['priority'] : $task['priority'];
        $status = $input['status'] ?? $task['status'];
        $categoryId = array_key_exists('category_id', $input)
            ? ($input['category_id'] !== '' ? (int) $input['category_id'] : null)
            : $task['category_id'];

        if (!self::validStatus($status)) {
            jsonResponse(['error' => 'Nepoznat status.'], 400);
            return;
        }

        if (!self::validPriority($priority)) {
            jsonResponse(['error' => 'Prioritet mora biti između 1 i 5.'], 400);
            return;
        }

        if ($categoryId !== null && !self::isOwnedCategory($db, $userId, $categoryId)) {
            jsonResponse(['error' => 'Kategorija ne pripada ovom korisniku.'], 400);
            return;
        }

        $stmt = $db->prepare('
            UPDATE tasks
            SET title = ?, description = ?, due_date = ?, priority = ?, status = ?, category_id = ?
            WHERE id = ? AND user_id = ?
        ');
        $stmt->execute([$title, $description, $dueDate, $priority, $status, $categoryId, $id, $userId]);

        jsonResponse(['message' => 'Zadatak ažuriran.']);
    }

    public static function delete(int $id, int $userId): void
    {
        $db = getDB();
        $task = self::fetchOwned($db, $id, $userId);

        if (!$task) {
            jsonResponse(['error' => 'Zadatak nije pronađen.'], 404);
            return;
        }

        $stmt = $db->prepare('DELETE FROM tasks WHERE id = ? AND user_id = ?');
        $stmt->execute([$id, $userId]);

        jsonResponse(['message' => 'Zadatak obrisan.']);
    }

    private static function fetchOwned(PDO $db, int $id, int $userId): ?array
    {
        $stmt = $db->prepare('SELECT * FROM tasks WHERE id = ? AND user_id = ?');
        $stmt->execute([$id, $userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    private static function validStatus(string $status): bool
    {
        return in_array($status, ['pending', 'in_progress', 'completed'], true);
    }

    private static function validPriority(int $priority): bool
    {
        return $priority >= 1 && $priority <= 5;
    }

    private static function isOwnedCategory(PDO $db, int $userId, int $categoryId): bool
    {
        $stmt = $db->prepare('SELECT 1 FROM categories WHERE id = ? AND user_id = ? LIMIT 1');
        $stmt->execute([$categoryId, $userId]);
        return (bool) $stmt->fetchColumn();
    }
}
