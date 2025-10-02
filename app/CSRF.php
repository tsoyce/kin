<?php
declare(strict_types=1);
namespace App;
use App\Bootstrap;

class CSRF {
    public static function token(): string {
        Bootstrap::start();
        if (empty($_SESSION['csrf_tokens'])) $_SESSION['csrf_tokens'] = [];
        $t = bin2hex(random_bytes(32));
        $_SESSION['csrf_tokens'][$t] = time()+3600;
        return $t;
    }
    public static function verify(?string $t): bool {
        Bootstrap::start();
        if (!$t || empty($_SESSION['csrf_tokens'][$t])) return false;
        $exp = $_SESSION['csrf_tokens'][$t];
        unset($_SESSION['csrf_tokens'][$t]);
        return $exp >= time();
    }
}
