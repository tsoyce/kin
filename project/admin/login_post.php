<?php

declare(strict_types=1);

use Project\Security;
use Project\Services\AuthService;
use function Project\redirect;

require __DIR__ . '/../app/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/admin/login.php');
}

if (!Security::verifyCsrf($_POST['csrf_token'] ?? null)) {
    $_SESSION['login_error'] = 'CSRF токен недействителен';
    redirect('/admin/login.php');
}

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

$auth = new AuthService();
if ($auth->login($username, $password)) {
    redirect('/admin/');
}

$_SESSION['login_error'] = 'Неверные данные';
redirect('/admin/login.php');
