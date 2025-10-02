<?php

declare(strict_types=1);

use Project\Services\AuthService;
use Project\Models\User;
use function Project\render;

require __DIR__ . '/../app/bootstrap.php';

$auth = new AuthService();
$auth->requireRole('admin');

$users = User::all(200, 0);

render('admin/users_list.php', ['users' => $users], 'admin/layout.php');
