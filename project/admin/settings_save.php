<?php

declare(strict_types=1);

use Project\Models\Settings;
use Project\Security;
use Project\Services\AuthService;
use function Project\flash;
use function Project\redirect;

require __DIR__ . '/../app/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/admin/settings.php');
}

$auth = new AuthService();
$auth->requireRole('admin');

if (!Security::verifyCsrf($_POST['csrf_token'] ?? null)) {
    flash('error', 'CSRF токен недействителен');
    redirect('/admin/settings.php');
}

$contacts = json_decode($_POST['contacts_json'] ?? '{}', true);
if (!is_array($contacts)) {
    flash('error', 'Контакты должны быть валидным JSON');
    redirect('/admin/settings.php');
}

Settings::update([
    'site_title' => trim($_POST['site_title'] ?? ''),
    'site_subtitle' => trim($_POST['site_subtitle'] ?? ''),
    'theme' => trim($_POST['theme'] ?? ''),
    'contacts_json' => json_encode($contacts, JSON_THROW_ON_ERROR),
    'analytics_enabled' => isset($_POST['analytics_enabled']) ? 1 : 0,
]);

flash('success', 'Настройки сохранены');
redirect('/admin/settings.php');
