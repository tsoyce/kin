<?php

declare(strict_types=1);

namespace Project\Services;

use Project\Models\Settings;

final class SeoService
{
    public function compose(array $overrides = []): array
    {
        $settings = Settings::get();
        $scheme = $_SERVER['REQUEST_SCHEME'] ?? ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http');
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $defaults = [
            'title' => $settings['site_title'] ?? 'Катиндирнет',
            'description' => $settings['site_subtitle'] ?? 'Каталог талантов и резюме',
            'canonical' => $scheme . '://' . $host . $uri,
        ];
        return array_merge($defaults, $overrides);
    }
}
