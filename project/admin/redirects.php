<?php

declare(strict_types=1);

use Project\Models\Redirect;
use Project\Services\AuthService;
use function Project\render;

require __DIR__ . '/../app/bootstrap.php';

$auth = new AuthService();
$auth->requireRole('editor');

$redirects = Redirect::all();

render('admin/redirects_list.php', ['redirects' => $redirects], 'admin/layout.php');
