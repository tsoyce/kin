<?php

declare(strict_types=1);

namespace Project\Services;

use Project\Models\User;
use function Project\redirect;

final class AuthService
{
    private const SESSION_KEY = 'auth_user_id';

    public function user(): ?array
    {
        $id = $_SESSION[self::SESSION_KEY] ?? null;
        if (!$id) {
            return null;
        }
        return User::find((int)$id);
    }

    public function login(string $username, string $password): bool
    {
        $limiter = new RateLimiter('login:' . ($_SERVER['REMOTE_ADDR'] ?? 'cli'));
        if (!$limiter->check()) {
            return false;
        }

        $user = User::findByUsername($username);
        if (!$user || !password_verify($password, $user['password_hash'])) {
            $limiter->hit();
            return false;
        }

        if (password_needs_rehash($user['password_hash'], PASSWORD_DEFAULT)) {
            User::update((int)$user['id'], ['password_hash' => password_hash($password, PASSWORD_DEFAULT)]);
        }

        $_SESSION[self::SESSION_KEY] = (int)$user['id'];
        $limiter->reset();
        return true;
    }

    public function logout(): void
    {
        unset($_SESSION[self::SESSION_KEY]);
    }

    public function requireRole(string $role): void
    {
        $user = $this->user();
        if (!$user || !$this->checkRole($user['role'], $role)) {
            redirect('/admin/login.php');
        }
    }

    public function checkRole(string $actual, string $required): bool
    {
        $order = ['reader' => 0, 'editor' => 1, 'admin' => 2];
        return ($order[$actual] ?? -1) >= ($order[$required] ?? 999);
    }
}
