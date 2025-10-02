<?php

declare(strict_types=1);

namespace Project;

use DateTimeImmutable;
use PDO;

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/security.php';
require_once __DIR__ . '/cache.php';

spl_autoload_register(static function (string $class): void {
    if (str_starts_with($class, __NAMESPACE__ . '\\')) {
        $relative = substr($class, strlen(__NAMESPACE__) + 1);
        $path = __DIR__ . '/' . str_replace('\\', '/', $relative) . '.php';
        if (is_file($path)) {
            require_once $path;
        }
    }
});

final class Bootstrap
{
    public static PDO $pdo;
    private static bool $started = false;

    public static function init(): void
    {
        if (self::$started) {
            return;
        }
        self::$started = true;

        Security::boot();
        self::$pdo = DB::connection();
        self::applyPragmas(self::$pdo);
        self::migrate(self::$pdo);
        self::seed(self::$pdo);
    }

    private static function applyPragmas(PDO $pdo): void
    {
        $pdo->exec('PRAGMA foreign_keys = ON;');
        $pdo->exec("PRAGMA journal_mode = WAL;");
        $pdo->exec('PRAGMA synchronous = NORMAL;');
    }

    private static function migrate(PDO $pdo): void
    {
        $pdo->exec('CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT UNIQUE NOT NULL,
            password_hash TEXT NOT NULL,
            role TEXT NOT NULL DEFAULT "reader",
            created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
        )');

        $pdo->exec('CREATE TABLE IF NOT EXISTS profiles (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            fio TEXT NOT NULL,
            email TEXT,
            phone TEXT,
            telegram TEXT,
            location TEXT,
            avatar_path TEXT,
            tags_csv TEXT,
            show_on_home INTEGER NOT NULL DEFAULT 0,
            created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TEXT
        )');

        $pdo->exec('CREATE TABLE IF NOT EXISTS cvs (
            profile_id INTEGER PRIMARY KEY,
            summary_md TEXT,
            experience_json TEXT,
            education_json TEXT,
            skills_csv TEXT,
            socials_json TEXT,
            updated_at TEXT,
            visibility TEXT DEFAULT "public",
            FOREIGN KEY(profile_id) REFERENCES profiles(id) ON DELETE CASCADE
        )');

        $pdo->exec('CREATE TABLE IF NOT EXISTS settings (
            id INTEGER PRIMARY KEY CHECK (id = 1),
            site_title TEXT,
            site_subtitle TEXT,
            theme TEXT,
            contacts_json TEXT,
            analytics_enabled INTEGER DEFAULT 0,
            created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TEXT
        )');

        $pdo->exec('CREATE TABLE IF NOT EXISTS redirects (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            from_path TEXT UNIQUE NOT NULL,
            to_url TEXT NOT NULL,
            code INTEGER NOT NULL DEFAULT 302
        )');

        $pdo->exec('CREATE INDEX IF NOT EXISTS idx_profiles_fio ON profiles(fio)');
        $pdo->exec('CREATE INDEX IF NOT EXISTS idx_profiles_tags ON profiles(tags_csv)');
        $pdo->exec('CREATE INDEX IF NOT EXISTS idx_redirects_from_path ON redirects(from_path)');

        self::ensureColumn($pdo, 'profiles', 'meta_json', 'ALTER TABLE profiles ADD COLUMN meta_json TEXT');
        self::ensureColumn($pdo, 'profiles', 'position', 'ALTER TABLE profiles ADD COLUMN position TEXT');
    }

    private static function ensureColumn(PDO $pdo, string $table, string $column, string $alterSql): void
    {
        $stmt = $pdo->prepare('PRAGMA table_info(' . $table . ')');
        $stmt->execute();
        $columns = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'name');
        if (!in_array($column, $columns, true)) {
            $pdo->exec($alterSql);
        }
    }

    private static function seed(PDO $pdo): void
    {
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM users WHERE username = :username');
        $stmt->execute(['username' => 'tsoy']);
        if ((int)$stmt->fetchColumn() === 0) {
            $hash = password_hash('0168', PASSWORD_DEFAULT);
            $ins = $pdo->prepare('INSERT INTO users(username, password_hash, role) VALUES(:username, :hash, :role)');
            $ins->execute([
                'username' => 'tsoy',
                'hash' => $hash,
                'role' => 'admin',
            ]);
        }

        $stmt = $pdo->query('SELECT COUNT(*) FROM settings');
        if ((int)$stmt->fetchColumn() === 0) {
            $ins = $pdo->prepare('INSERT INTO settings(id, site_title, site_subtitle, theme, contacts_json, created_at, updated_at)
                VALUES (1, :title, :subtitle, :theme, :contacts, :created, :updated)');
            $now = (new DateTimeImmutable())->format(DATE_ATOM);
            $ins->execute([
                'title' => 'Катиндирнет',
                'subtitle' => 'Каталог талантов и резюме',
                'theme' => 'default',
                'contacts' => json_encode([
                    'email' => 'hello@example.com',
                    'telegram' => '@katindirnet',
                ], JSON_THROW_ON_ERROR),
                'created' => $now,
                'updated' => $now,
            ]);
        }

        $stmt = $pdo->query('SELECT COUNT(*) FROM profiles');
        if ((int)$stmt->fetchColumn() === 0) {
            $profile = $pdo->prepare('INSERT INTO profiles(fio, email, phone, telegram, location, avatar_path, tags_csv, show_on_home, created_at, updated_at, position)
                VALUES(:fio, :email, :phone, :tg, :location, :avatar, :tags, :show, :created, :updated, :position)');
            $now = (new DateTimeImmutable())->format(DATE_ATOM);
            $profile->execute([
                'fio' => 'Иван Катин',
                'email' => 'ivan@example.com',
                'phone' => '+7 999 123-45-67',
                'tg' => '@ivan',
                'location' => 'Москва',
                'avatar' => null,
                'tags' => 'fullstack,php,lead',
                'show' => 1,
                'created' => $now,
                'updated' => $now,
                'position' => 'Senior PHP Engineer',
            ]);
            $profileId = (int)$pdo->lastInsertId();
            $cv = $pdo->prepare('INSERT INTO cvs(profile_id, summary_md, experience_json, education_json, skills_csv, socials_json, visibility, updated_at)
                VALUES(:profile_id, :summary, :experience, :education, :skills, :socials, :visibility, :updated)');
            $cv->execute([
                'profile_id' => $profileId,
                'summary' => "Senior PHP разработчик с опытом в высоконагруженных проектах.",
                'experience' => json_encode([
                    ['company' => 'Катиндирнет', 'role' => 'Ведущий инженер', 'period' => '2021—н.в.'],
                ], JSON_THROW_ON_ERROR),
                'education' => json_encode([
                    ['title' => 'МГТУ им. Баумана', 'period' => '2012—2018'],
                ], JSON_THROW_ON_ERROR),
                'skills' => 'PHP,SQLite,Leadership',
                'socials' => json_encode([
                    ['title' => 'GitHub', 'url' => 'https://github.com/example'],
                ], JSON_THROW_ON_ERROR),
                'visibility' => 'public',
                'updated' => $now,
            ]);

            $profile->execute([
                'fio' => 'Мария Дирнет',
                'email' => 'maria@example.com',
                'phone' => '+7 903 987-65-43',
                'tg' => '@maria',
                'location' => 'Санкт-Петербург',
                'avatar' => null,
                'tags' => 'designer,ux,mentor',
                'show' => 1,
                'created' => $now,
                'updated' => $now,
                'position' => 'Lead Product Designer',
            ]);
            $profileId = (int)$pdo->lastInsertId();
            $cv->execute([
                'profile_id' => $profileId,
                'summary' => "Продуктовый дизайнер, создающий системы для роста бизнеса.",
                'experience' => json_encode([
                    ['company' => 'Катиндирнет', 'role' => 'Руководитель дизайна', 'period' => '2019—н.в.'],
                ], JSON_THROW_ON_ERROR),
                'education' => json_encode([
                    ['title' => 'СПбГУ', 'period' => '2010—2015'],
                ], JSON_THROW_ON_ERROR),
                'skills' => 'UX,UI,Leadership',
                'socials' => json_encode([
                    ['title' => 'Behance', 'url' => 'https://behance.net/example'],
                ], JSON_THROW_ON_ERROR),
                'visibility' => 'public',
                'updated' => $now,
            ]);
        }
    }
}

Bootstrap::init();
