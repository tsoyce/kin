<?php

declare(strict_types=1);

namespace Project\Models;

use DateTimeImmutable;
use PDO;
use Project\Bootstrap;

final class Profile
{
    public static function find(int $id): ?array
    {
        $stmt = Bootstrap::$pdo->prepare('SELECT * FROM profiles WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $profile = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$profile) {
            return null;
        }
        $cv = CV::findByProfile($id);
        $profile['cv'] = $cv;
        return $profile;
    }

    public static function search(array $filters, int $limit, int $offset): array
    {
        $conditions = [];
        $params = [];

        if (!empty($filters['query'])) {
            $conditions[] = '(fio LIKE :query OR email LIKE :query OR telegram LIKE :query OR tags_csv LIKE :query)';
            $params['query'] = '%' . $filters['query'] . '%';
        }

        if (isset($filters['show_on_home'])) {
            $conditions[] = 'show_on_home = :show_on_home';
            $params['show_on_home'] = (int)$filters['show_on_home'];
        }

        $sql = 'SELECT * FROM profiles';
        if ($conditions) {
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }
        $sql .= ' ORDER BY created_at DESC LIMIT :limit OFFSET :offset';

        $stmt = Bootstrap::$pdo->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function count(array $filters = []): int
    {
        $conditions = [];
        $params = [];

        if (!empty($filters['query'])) {
            $conditions[] = '(fio LIKE :query OR email LIKE :query OR telegram LIKE :query OR tags_csv LIKE :query)';
            $params['query'] = '%' . $filters['query'] . '%';
        }
        if (isset($filters['show_on_home'])) {
            $conditions[] = 'show_on_home = :show_on_home';
            $params['show_on_home'] = (int)$filters['show_on_home'];
        }

        $sql = 'SELECT COUNT(*) FROM profiles';
        if ($conditions) {
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }

        $stmt = Bootstrap::$pdo->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $stmt->execute();
        return (int)$stmt->fetchColumn();
    }

    public static function allFeatured(int $limit = 10): array
    {
        $stmt = Bootstrap::$pdo->prepare('SELECT * FROM profiles WHERE show_on_home = 1 ORDER BY updated_at DESC LIMIT :limit');
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        $profiles = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($profiles as &$profile) {
            $profile['cv'] = CV::findByProfile((int)$profile['id']);
        }
        return $profiles;
    }

    public static function save(array $data): int
    {
        $now = (new DateTimeImmutable())->format(DATE_ATOM);
        if (!empty($data['id'])) {
            $stmt = Bootstrap::$pdo->prepare('UPDATE profiles SET fio = :fio, email = :email, phone = :phone, telegram = :telegram, location = :location, avatar_path = :avatar_path, tags_csv = :tags_csv, show_on_home = :show_on_home, updated_at = :updated_at, position = :position, meta_json = :meta_json WHERE id = :id');
            $stmt->execute([
                'fio' => $data['fio'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'telegram' => $data['telegram'],
                'location' => $data['location'],
                'avatar_path' => $data['avatar_path'],
                'tags_csv' => $data['tags_csv'],
                'show_on_home' => (int)$data['show_on_home'],
                'updated_at' => $now,
                'position' => $data['position'],
                'meta_json' => $data['meta_json'],
                'id' => (int)$data['id'],
            ]);
            return (int)$data['id'];
        }

        $stmt = Bootstrap::$pdo->prepare('INSERT INTO profiles(fio, email, phone, telegram, location, avatar_path, tags_csv, show_on_home, created_at, updated_at, position, meta_json) VALUES(:fio, :email, :phone, :telegram, :location, :avatar_path, :tags_csv, :show_on_home, :created_at, :updated_at, :position, :meta_json)');
        $stmt->execute([
            'fio' => $data['fio'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'telegram' => $data['telegram'],
            'location' => $data['location'],
            'avatar_path' => $data['avatar_path'],
            'tags_csv' => $data['tags_csv'],
            'show_on_home' => (int)$data['show_on_home'],
            'created_at' => $now,
            'updated_at' => $now,
            'position' => $data['position'],
            'meta_json' => $data['meta_json'],
        ]);
        return (int)Bootstrap::$pdo->lastInsertId();
    }

    public static function delete(int $id): void
    {
        $stmt = Bootstrap::$pdo->prepare('DELETE FROM profiles WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }
}
