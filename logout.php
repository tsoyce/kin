<?php
// Logout script for Katindirnet.  Destroys the current session and
// redirects to the homepage.  This file is intentionally minimal to
// minimise attack surface.

declare(strict_types=1);
require_once __DIR__ . '/helpers.php';

use App\Auth;

Auth::logout();
// Redirect to the home page after logout
header('Location: index.php');
exit;