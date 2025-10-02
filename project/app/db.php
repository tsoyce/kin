<?php

declare(strict_types=1);

namespace Project;

use PDO;
use PDOException;

final class DB
{
    private static ?PDO $instance = null;

    public static function connection(): PDO
    {
        if (self::$instance instanceof PDO) {
            return self::$instance;
        }

        $databasePath = getenv('KIN_SQLITE_PATH') ?: __DIR__ . '/../storage/kin.sqlite';
        $dir = dirname($databasePath);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        $dsn = 'sqlite:' . $databasePath;

        try {
            $pdo = new PDO($dsn, null, null, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $e) {
            throw new PDOException('Не удалось подключиться к базе данных: ' . $e->getMessage(), (int)$e->getCode(), $e);
        }

        self::$instance = $pdo;
        return self::$instance;
    }
}
