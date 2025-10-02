<?php

declare(strict_types=1);

use Project\Bootstrap;
use Project\Services\AuthService;
use function Project\render;

require __DIR__ . '/../app/bootstrap.php';

$auth = new AuthService();
$auth->requireRole('reader');

render('admin/dashboard.php', [], 'admin/layout.php');
