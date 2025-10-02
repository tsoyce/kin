<?php

declare(strict_types=1);

namespace Project\Models;

use DateTimeImmutable;
use PDO;
use Project\Bootstrap;

final class CV
{
    public static function findByProfile(int $profileId): ?array
    {
        $stmt = Bootstrap::$pdo->prepare('SELECT * FROM cvs WHERE profile_id = :profile_id');
        $stmt->execute(['profile_id' => $profileId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public static function save(int $profileId, array $data): void
    {
        $exists = self::findByProfile($profileId);
        $payload = [
            'summary_md' => $data['summary_md'],
            'experience_json' => $data['experience_json'],
            'education_json' => $data['education_json'],
            'skills_csv' => $data['skills_csv'],
            'socials_json' => $data['socials_json'],
            'visibility' => $data['visibility'] ?? 'public',
            'updated_at' => (new DateTimeImmutable())->format(DATE_ATOM),
        ];
        if ($exists) {
            $stmt = Bootstrap::$pdo->prepare('UPDATE cvs SET summary_md = :summary_md, experience_json = :experience_json, education_json = :education_json, skills_csv = :skills_csv, socials_json = :socials_json, visibility = :visibility, updated_at = :updated_at WHERE profile_id = :profile_id');
            $stmt->execute($payload + ['profile_id' => $profileId]);
        } else {
            $stmt = Bootstrap::$pdo->prepare('INSERT INTO cvs(profile_id, summary_md, experience_json, education_json, skills_csv, socials_json, visibility, updated_at) VALUES(:profile_id, :summary_md, :experience_json, :education_json, :skills_csv, :socials_json, :visibility, :updated_at)');
            $stmt->execute($payload + ['profile_id' => $profileId]);
        }
    }
}
