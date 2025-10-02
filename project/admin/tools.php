<?php

declare(strict_types=1);

use Project\Models\Settings;
use Project\Services\AuthService;
use function Project\render;

require __DIR__ . '/../app/bootstrap.php';

$auth = new AuthService();
$auth->requireRole('reader');

$settings = Settings::get();

render('admin/tools.php', ['settings' => $settings], 'admin/layout.php');
