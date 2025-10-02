<?php
declare(strict_types=1);
namespace App;
use App\Bootstrap;

class Auth {
    public static function user(): ?array {
        Bootstrap::start();
        if (!empty($_SESSION['uid'])) {
            $stmt = Bootstrap::$pdo->prepare('SELECT id,email,role FROM users WHERE id=?');
            $stmt->execute([$_SESSION['uid']]);
            $u = $stmt->fetch();
            return $u ?: null;
        }
        return null;
    }
    public static function login(string $email, string $password): bool {
        Bootstrap::start();
        $stmt = Bootstrap::$pdo->prepare('SELECT id,pass_hash FROM users WHERE email=?');
        $stmt->execute([$email]);
        $row = $stmt->fetch();
        if (!$row) return false;
        if (!password_verify($password, $row['pass_hash'])) return false;
        $_SESSION['uid'] = (int)$row['id'];
        return true;
    }
    public static function register(string $email, string $password, string $role='user'): bool {
        Bootstrap::start();
        $hash = password_hash($password, PASSWORD_DEFAULT);
        // Record the registering user's IP address.  This helps with audits.
        $ip = $_SERVER['REMOTE_ADDR'] ?? null;
        $stmt = Bootstrap::$pdo->prepare('INSERT INTO users(email, pass_hash, role, created_at, register_ip) VALUES(?,?,?,?,?)');
        try {
            return $stmt->execute([
                $email,
                $hash,
                $role,
                date('Y-m-d H:i:s'),
                $ip
            ]);
        } catch (\PDOException $e) {
            return false;
        }
    }
    public static function logout(): void {
        Bootstrap::start();
        session_destroy();
    }
    public static function requireRole(string $role): void {
        $u = self::user();
        if (!$u || ($u['role'] !== $role && $u['role'] !== 'admin')) {
            http_response_code(403); echo 'Forbidden'; exit;
        }
    }
}
