<?php

declare(strict_types=1);

use Project\Services\AuthService;
use function Project\redirect;
use function Project\render;

require __DIR__ . '/../app/bootstrap.php';

$auth = new AuthService();
if ($auth->user()) {
    redirect('/admin/');
}

$error = $_SESSION['login_error'] ?? null;
unset($_SESSION['login_error']);

render('admin/login.php', ['error' => $error]);
