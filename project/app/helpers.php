<?php

declare(strict_types=1);

namespace Project;

use Project\Services\SeoService;

function base_path(string $path = ''): string
{
    $base = dirname(__DIR__);
    return $path ? $base . '/' . ltrim($path, '/') : $base;
}

function public_path(string $path = ''): string
{
    $base = base_path('public');
    return $path ? $base . '/' . ltrim($path, '/') : $base;
}

function view_path(string $path = ''): string
{
    $base = base_path('views');
    return $path ? $base . '/' . ltrim($path, '/') : $base;
}

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function asset(string $path): string
{
    $path = '/' . ltrim($path, '/');
    return $path . '?v=' . substr(sha1($path), 0, 6);
}

function render(string $view, array $params = [], ?string $layout = 'layout.php'): void
{
    extract($params, EXTR_SKIP);
    $viewFile = view_path($view);
    if (!is_file($viewFile)) {
        throw new \RuntimeException("View '{$view}' not found");
    }

    ob_start();
    include $viewFile;
    $content = ob_get_clean();

    if ($layout) {
        $layoutFile = view_path($layout);
        if (!is_file($layoutFile)) {
            throw new \RuntimeException("Layout '{$layout}' not found");
        }
        include $layoutFile;
    } else {
        echo $content;
    }
}

function seo_meta(array $overrides = []): array
{
    $service = new SeoService();
    return $service->compose($overrides);
}

function redirect(string $location): void
{
    header('Location: ' . $location);
    exit;
}

function is_ajax(): bool
{
    return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

function paginate(int $total, int $perPage, int $page): array
{
    $pages = max(1, (int)ceil($total / $perPage));
    $page = max(1, min($page, $pages));
    return [
        'current' => $page,
        'pages' => $pages,
        'total' => $total,
        'per_page' => $perPage,
        'offset' => ($page - 1) * $perPage,
    ];
}

function flash(string $type, string $message): void
{
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}
