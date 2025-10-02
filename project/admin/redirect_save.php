<?php

declare(strict_types=1);

use Project\Models\Redirect;
use Project\Security;
use Project\Services\AuthService;
use function Project\flash;
use function Project\redirect;

require __DIR__ . '/../app/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/admin/redirects.php');
}

$auth = new AuthService();
$auth->requireRole('editor');

if (!Security::verifyCsrf($_POST['csrf_token'] ?? null)) {
    flash('error', 'CSRF токен недействителен');
    redirect('/admin/redirects.php');
}

$from = trim($_POST['from_path'] ?? '');
$to = trim($_POST['to_url'] ?? '');
$code = (int)($_POST['code'] ?? 302);

if ($from === '' || $to === '') {
    flash('error', 'Укажите путь и адрес');
    redirect('/admin/redirects.php');
}

$exists = Redirect::findByPath($from);
if ($exists) {
    flash('error', 'Редирект для этого пути уже существует');
    redirect('/admin/redirects.php');
}

Redirect::save([
    'from_path' => $from,
    'to_url' => $to,
    'code' => $code,
]);

flash('success', 'Редирект сохранён');
redirect('/admin/redirects.php');
