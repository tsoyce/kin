<?php

declare(strict_types=1);

namespace Project\Models;

use PDO;
use Project\Bootstrap;

final class Redirect
{
    public static function all(): array
    {
        $stmt = Bootstrap::$pdo->query('SELECT * FROM redirects ORDER BY from_path ASC');
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function findByPath(string $fromPath): ?array
    {
        $stmt = Bootstrap::$pdo->prepare('SELECT * FROM redirects WHERE from_path = :from_path LIMIT 1');
        $stmt->execute(['from_path' => $fromPath]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public static function save(array $data): void
    {
        if (!empty($data['id'])) {
            $stmt = Bootstrap::$pdo->prepare('UPDATE redirects SET from_path = :from_path, to_url = :to_url, code = :code WHERE id = :id');
            $stmt->execute([
                'from_path' => $data['from_path'],
                'to_url' => $data['to_url'],
                'code' => (int)$data['code'],
                'id' => (int)$data['id'],
            ]);
            return;
        }

        $stmt = Bootstrap::$pdo->prepare('INSERT INTO redirects(from_path, to_url, code) VALUES(:from_path, :to_url, :code)');
        $stmt->execute([
            'from_path' => $data['from_path'],
            'to_url' => $data['to_url'],
            'code' => (int)$data['code'],
        ]);
    }

    public static function delete(int $id): void
    {
        $stmt = Bootstrap::$pdo->prepare('DELETE FROM redirects WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }
}
