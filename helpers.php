<?php
declare(strict_types=1);
require_once __DIR__.'/app/Bootstrap.php';
require_once __DIR__.'/app/Auth.php';
require_once __DIR__.'/app/CSRF.php';

use App\Bootstrap;
use App\Auth;
use App\CSRF;

Bootstrap::start();

function h(?string $v): string { return htmlspecialchars($v ?? '', ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8'); }
function user(): ?array { return Auth::user(); }
function csrf_field(): string { return '<input type="hidden" name="csrf" value="'.h(CSRF::token()).'">'; }
function random_slug(int $len=6): string {
  $alphabet = '23456789abcdefghjkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ-_';
  $s=''; for($i=0;$i<$len;$i++){ $s.=$alphabet[random_int(0,strlen($alphabet)-1)]; }
  return $s;
}

/**
 * Fetch a configuration value from the settings table.  If the key is not
 * present, the provided default will be returned.  Values are cached per
 * request to avoid repeated database queries.
 *
 * @param string $key The settings key to retrieve.
 * @param string $default A default value if the key is missing.
 * @return string The stored value or the default.
 */
function get_setting(string $key, string $default = ''): string {
    static $cache = [];
    if (array_key_exists($key, $cache)) {
        return $cache[$key];
    }
    $pdo = App\Bootstrap::$pdo;
    $stmt = $pdo->prepare('SELECT value FROM settings WHERE key = ?');
    $stmt->execute([$key]);
    $val = $stmt->fetchColumn();
    if ($val === false) {
        $cache[$key] = $default;
        return $default;
    }
    $cache[$key] = (string)$val;
    return (string)$val;
}

/**
 * Persist a configuration value into the settings table.  If the key
 * already exists it will be updated, otherwise it will be inserted.
 *
 * @param string $key The settings key to set.
 * @param string $value The value to store.
 * @return void
 */
function set_setting(string $key, string $value): void {
    $pdo = App\Bootstrap::$pdo;
    // Use SQLite UPSERT syntax via ON CONFLICT
    $stmt = $pdo->prepare('INSERT INTO settings(key, value) VALUES(?, ?) ON CONFLICT(key) DO UPDATE SET value = excluded.value');
    $stmt->execute([$key, $value]);
}

/**
 * Retrieve the user ID of the guest account used for anonymous link creation.
 * The value is cached for the duration of the request to avoid repeated
 * database queries.
 *
 * @return int|null The guest user ID, or null if not found (should not happen).
 */
function guest_id(): ?int {
    static $gid = null;
    if ($gid !== null) {
        return $gid;
    }
    $pdo = App\Bootstrap::$pdo;
    $stmt = $pdo->query("SELECT id FROM users WHERE email='guest' LIMIT 1");
    $gid = $stmt->fetchColumn() ?: null;
    return $gid;
}
