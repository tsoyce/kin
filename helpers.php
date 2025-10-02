<?php
require_once __DIR__ . '/project/app/bootstrap.php';

use Project\Services\AuthService;
use function Project\render;
use function Project\e;
use function Project\flash;
use function Project\seo_meta;

function user(): ?array
{
    $auth = new AuthService();
    return $auth->user();
}

function h(?string $value): string
{
    return Project\e($value);
}

function csrf_field(): string
{
    return Project\Security::csrfField();
}
