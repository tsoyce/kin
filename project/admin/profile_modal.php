<?php

declare(strict_types=1);

use Project\Services\AuthService;
use Project\Services\ProfileService;
use function Project\render;

require __DIR__ . '/../app/bootstrap.php';

$auth = new AuthService();
$auth->requireRole('editor');

$service = new ProfileService();
$id = $_GET['id'] ?? 'new';
$profile = null;
$cv = null;
if ($id !== 'new') {
    $profile = $service->get((int)$id);
    $cv = $profile['cv'] ?? null;
}

render('admin/profile_form.php', [
    'profile' => $profile,
    'cv' => $cv,
], layout: null);
