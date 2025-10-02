<?php

declare(strict_types=1);

use Project\Services\AuthService;
use Project\Services\ProfileService;
use function Project\render;

require __DIR__ . '/../app/bootstrap.php';

$auth = new AuthService();
$auth->requireRole('editor');

$filters = [
    'query' => $_GET['query'] ?? '',
];
if ($_GET['show_on_home'] ?? '' !== '') {
    $filters['show_on_home'] = $_GET['show_on_home'];
}

$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$perPage = 50;

$service = new ProfileService();
[$profiles, $pagination] = $service->paginated($filters, $perPage, $page);

render('admin/profiles_list.php', [
    'profiles' => $profiles,
    'pagination' => $pagination,
    'filters' => $filters,
], 'admin/layout.php');
