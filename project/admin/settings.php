<?php

declare(strict_types=1);

use Project\Models\Settings;
use Project\Services\AuthService;
use function Project\render;

require __DIR__ . '/../app/bootstrap.php';

$auth = new AuthService();
$auth->requireRole('admin');

$settings = Settings::get();

render('admin/settings_form.php', ['settings' => $settings], 'admin/layout.php');
