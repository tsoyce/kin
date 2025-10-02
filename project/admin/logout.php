<?php

declare(strict_types=1);

use Project\Services\AuthService;
use function Project\flash;
use function Project\redirect;

require __DIR__ . '/../app/bootstrap.php';

$auth = new AuthService();
$auth->logout();
flash('success', 'Вы вышли из админки');
redirect('/admin/login.php');
