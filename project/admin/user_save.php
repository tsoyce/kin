<?php

declare(strict_types=1);

use Project\Models\User;
use Project\Security;
use Project\Services\AuthService;
use function Project\flash;
use function Project\redirect;

require __DIR__ . '/../app/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/admin/users_list.php');
}

$auth = new AuthService();
$auth->requireRole('admin');

if (!Security::verifyCsrf($_POST['csrf_token'] ?? null)) {
    flash('error', 'CSRF токен недействителен');
    redirect('/admin/users_list.php');
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : null;
$username = trim($_POST['username'] ?? '');
$role = $_POST['role'] ?? 'reader';
$password = $_POST['password'] ?? '';

if ($username === '') {
    flash('error', 'Укажите логин');
    redirect('/admin/users_list.php');
}

$data = ['username' => $username, 'role' => $role];
if ($password !== '') {
    $data['password_hash'] = password_hash($password, PASSWORD_DEFAULT);
}

if ($id) {
    User::update($id, $data);
    flash('success', 'Пользователь обновлён');
} else {
    if (empty($data['password_hash'])) {
        flash('error', 'Для нового пользователя нужен пароль');
        redirect('/admin/users_list.php');
    }
    User::create($data);
    flash('success', 'Пользователь создан');
}

redirect('/admin/users_list.php');
