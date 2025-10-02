<?php

declare(strict_types=1);

use Project\Models\User;
use Project\Security;
use Project\Services\AuthService;
use function Project\render;

require __DIR__ . '/project/app/bootstrap.php';

$auth = new AuthService();
$user = $auth->user();
$messages = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Security::verifyCsrf($_POST['csrf_token'] ?? null)) {
        $messages[] = ['type' => 'error', 'text' => 'CSRF токен недействителен'];
    } else {
        if (isset($_POST['login'])) {
            if ($auth->login(trim($_POST['login_username'] ?? ''), $_POST['login_password'] ?? '')) {
                $user = $auth->user();
                $messages[] = ['type' => 'success', 'text' => 'Вы вошли'];
            } else {
                $messages[] = ['type' => 'error', 'text' => 'Неверный логин или пароль'];
            }
        } elseif (isset($_POST['register'])) {
            $username = trim($_POST['register_username'] ?? '');
            $password = $_POST['register_password'] ?? '';
            $password2 = $_POST['register_password2'] ?? '';
            if ($username === '' || !preg_match('/^[A-Za-z0-9_\-]{3,32}$/', $username)) {
                $messages[] = ['type' => 'error', 'text' => 'Логин должен содержать 3-32 символа'];
            } elseif ($password === '' || strlen($password) < 6) {
                $messages[] = ['type' => 'error', 'text' => 'Пароль слишком короткий'];
            } elseif ($password !== $password2) {
                $messages[] = ['type' => 'error', 'text' => 'Пароли не совпадают'];
            } elseif (User::findByUsername($username)) {
                $messages[] = ['type' => 'error', 'text' => 'Логин уже занят'];
            } else {
                User::create([
                    'username' => $username,
                    'password_hash' => password_hash($password, PASSWORD_DEFAULT),
                    'role' => 'reader',
                ]);
                if ($auth->login($username, $password)) {
                    $user = $auth->user();
                    $messages[] = ['type' => 'success', 'text' => 'Регистрация успешна'];
                }
            }
        } elseif ($user && isset($_POST['change_password'])) {
            $current = $_POST['current_password'] ?? '';
            $new = $_POST['new_password'] ?? '';
            $new2 = $_POST['new_password2'] ?? '';
            if ($current === '' || $new === '' || $new2 === '') {
                $messages[] = ['type' => 'error', 'text' => 'Заполните все поля'];
            } elseif ($new !== $new2) {
                $messages[] = ['type' => 'error', 'text' => 'Пароли не совпадают'];
            } elseif ($new === $current) {
                $messages[] = ['type' => 'error', 'text' => 'Пароль не изменился'];
            } else {
                $fresh = User::find((int)$user['id']);
                if (!$fresh || !password_verify($current, $fresh['password_hash'])) {
                    $messages[] = ['type' => 'error', 'text' => 'Текущий пароль неверен'];
                } else {
                    User::update((int)$user['id'], ['password_hash' => password_hash($new, PASSWORD_DEFAULT)]);
                    $messages[] = ['type' => 'success', 'text' => 'Пароль изменён'];
                }
            }
        }
    }
}

render('id_page.php', [
    'user' => $user,
    'messages' => $messages,
]);
