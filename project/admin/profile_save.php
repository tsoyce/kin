<?php

declare(strict_types=1);

use Project\Security;
use Project\Services\AuthService;
use Project\Services\ProfileService;

require __DIR__ . '/../app/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Неверный метод']);
    exit;
}

if (!Security::verifyCsrf($_POST['csrf_token'] ?? null)) {
    echo json_encode(['success' => false, 'message' => 'CSRF токен недействителен']);
    exit;
}

$auth = new AuthService();
$auth->requireRole('editor');

if (!empty($_POST['delete']) && !empty($_POST['id'])) {
    Project\Models\Profile::delete((int)$_POST['id']);
    echo json_encode(['success' => true, 'message' => 'Профиль удалён']);
    exit;
}

$profileData = [
    'id' => $_POST['id'] ?? null,
    'fio' => trim($_POST['fio'] ?? ''),
    'position' => trim($_POST['position'] ?? ''),
    'email' => trim($_POST['email'] ?? ''),
    'telegram' => trim($_POST['telegram'] ?? ''),
    'phone' => trim($_POST['phone'] ?? ''),
    'location' => trim($_POST['location'] ?? ''),
    'tags_csv' => trim($_POST['tags_csv'] ?? ''),
    'show_on_home' => (int)($_POST['show_on_home'] ?? 0),
    'avatar_path' => trim($_POST['avatar_path'] ?? ''),
    'meta_json' => '{}',
];

if ($profileData['fio'] === '') {
    echo json_encode(['success' => false, 'message' => 'Укажите ФИО']);
    exit;
}

$cvData = [
    'summary_md' => trim($_POST['summary_md'] ?? ''),
    'experience_json' => $_POST['experience_json'] ?: '[]',
    'education_json' => $_POST['education_json'] ?: '[]',
    'skills_csv' => trim($_POST['skills_csv'] ?? ''),
    'socials_json' => $_POST['socials_json'] ?: '[]',
    'visibility' => 'public',
];

$service = new ProfileService();
$service->save($profileData, $cvData);

echo json_encode(['success' => true, 'message' => 'Профиль сохранён']);
