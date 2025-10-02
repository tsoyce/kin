<?php

declare(strict_types=1);

namespace Project\Models;

use DateTimeImmutable;
use PDO;
use Project\Bootstrap;

final class User
{
    public static function findByUsername(string $username): ?array
    {
        $stmt = Bootstrap::$pdo->prepare('SELECT * FROM users WHERE username = :username LIMIT 1');
        $stmt->execute(['username' => $username]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public static function find(int $id): ?array
    {
        $stmt = Bootstrap::$pdo->prepare('SELECT * FROM users WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public static function all(int $limit = 100, int $offset = 0): array
    {
        $stmt = Bootstrap::$pdo->prepare('SELECT * FROM users ORDER BY created_at DESC LIMIT :limit OFFSET :offset');
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function count(): int
    {
        $stmt = Bootstrap::$pdo->query('SELECT COUNT(*) FROM users');
        return (int)$stmt->fetchColumn();
    }

    public static function create(array $data): int
    {
        $stmt = Bootstrap::$pdo->prepare('INSERT INTO users(username, password_hash, role, created_at) VALUES(:username, :password_hash, :role, :created_at)');
        $stmt->execute([
            'username' => $data['username'],
            'password_hash' => $data['password_hash'],
            'role' => $data['role'],
            'created_at' => (new DateTimeImmutable())->format(DATE_ATOM),
        ]);
        return (int)Bootstrap::$pdo->lastInsertId();
    }

    public static function update(int $id, array $data): void
    {
        $fields = [];
        $params = ['id' => $id];
        foreach (['username', 'password_hash', 'role'] as $column) {
            if (isset($data[$column])) {
                $fields[] = $column . ' = :' . $column;
                $params[$column] = $data[$column];
            }
        }
        if (!$fields) {
            return;
        }
        $sql = 'UPDATE users SET ' . implode(', ', $fields) . ' WHERE id = :id';
        $stmt = Bootstrap::$pdo->prepare($sql);
        $stmt->execute($params);
    }

    public static function delete(int $id): void
    {
        $stmt = Bootstrap::$pdo->prepare('DELETE FROM users WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }
}
