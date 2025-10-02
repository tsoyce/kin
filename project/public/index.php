<?php

declare(strict_types=1);

use Project\Services\ProfileService;
use function Project\render;

$service = new ProfileService();
$profiles = $service->featured(6);

render('home.php', ['profiles' => $profiles]);
