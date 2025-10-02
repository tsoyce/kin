<?php

declare(strict_types=1);

namespace Project\Models;

use DateTimeImmutable;
use Project\Bootstrap;

final class Settings
{
    public static function get(): array
    {
        $stmt = Bootstrap::$pdo->query('SELECT * FROM settings WHERE id = 1');
        $settings = $stmt->fetch() ?: [];
        return $settings;
    }

    public static function update(array $data): void
    {
        $now = (new DateTimeImmutable())->format(DATE_ATOM);
        $stmt = Bootstrap::$pdo->prepare('UPDATE settings SET site_title = :site_title, site_subtitle = :site_subtitle, theme = :theme, contacts_json = :contacts_json, analytics_enabled = :analytics_enabled, updated_at = :updated_at WHERE id = 1');
        $stmt->execute([
            'site_title' => $data['site_title'],
            'site_subtitle' => $data['site_subtitle'],
            'theme' => $data['theme'],
            'contacts_json' => $data['contacts_json'],
            'analytics_enabled' => (int)$data['analytics_enabled'],
            'updated_at' => $now,
        ]);
    }
}
