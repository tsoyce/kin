<?php
declare(strict_types=1);
namespace App;
use PDO;

class Bootstrap {
    public static ?PDO $pdo = null;

    public static function start(): void {
        self::session();
        self::db();
        self::migrate();
        self::seed();
    }

    private static function session(): void {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_name('SHORTENERSESSID');
            session_set_cookie_params([
                'httponly' => true,
                'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
                'samesite' => 'Lax',
                'path' => '/',
            ]);
            session_start();
        }
    }

    public static function db(): PDO {
        if (self::$pdo) return self::$pdo;
        $baseDir = __DIR__ . '/../';
        $dbPath  = $baseDir . 'storage/database.sqlite';
        $dir = dirname($dbPath);
        if (!is_dir($dir)) @mkdir($dir, 0775, true);
        self::$pdo = new PDO('sqlite:'.$dbPath, null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        self::$pdo->exec('PRAGMA journal_mode=WAL;');
        self::$pdo->exec('PRAGMA foreign_keys=ON;');
        return self::$pdo;
    }

    private static function migrate(): void {
        $pdo = self::db();
        $pdo->exec('PRAGMA foreign_keys=ON;');

        $pdo->exec('CREATE TABLE IF NOT EXISTS users (
          id INTEGER PRIMARY KEY AUTOINCREMENT,
          email TEXT NOT NULL UNIQUE,
          pass_hash TEXT NOT NULL,
          role TEXT NOT NULL DEFAULT "user",
          created_at TEXT NOT NULL,
          register_ip TEXT
        );');

        $pdo->exec('CREATE TABLE IF NOT EXISTS domains (
          id INTEGER PRIMARY KEY AUTOINCREMENT,
          host TEXT NOT NULL UNIQUE,
          is_active INTEGER NOT NULL DEFAULT 1,
          updated_at TEXT NOT NULL
        );');

        // Core links table. Additional columns for statistics and auditing
        // such as creator_ip, hits and last_hit are added via ALTER TABLE below.
        $pdo->exec('CREATE TABLE IF NOT EXISTS links (
          id INTEGER PRIMARY KEY AUTOINCREMENT,
          user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
          domain_id INTEGER NOT NULL REFERENCES domains(id) ON DELETE RESTRICT,
          slug TEXT NOT NULL UNIQUE,
          target TEXT NOT NULL,
          created_at TEXT NOT NULL
        );');

        // Clicks table. Later migrations may add columns for ip and user agent.
        $pdo->exec('CREATE TABLE IF NOT EXISTS clicks (
          id INTEGER PRIMARY KEY AUTOINCREMENT,
          link_id INTEGER NOT NULL REFERENCES links(id) ON DELETE CASCADE,
          ts TEXT NOT NULL,
          ip_hash TEXT,
          ua TEXT,
          ref TEXT
        );');

        // Add optional statistics and auditing columns to links if they are missing.
        // SQLite lacks IF NOT EXISTS for adding columns, so we check first.
        $columns = $pdo->query("PRAGMA table_info('links')")->fetchAll();
        $colNames = array_column($columns, 'name');
        if (!in_array('creator_ip', $colNames, true)) {
            $pdo->exec("ALTER TABLE links ADD COLUMN creator_ip TEXT");
        }
        if (!in_array('hits', $colNames, true)) {
            $pdo->exec("ALTER TABLE links ADD COLUMN hits INTEGER NOT NULL DEFAULT 0");
        }
        if (!in_array('last_hit', $colNames, true)) {
            $pdo->exec("ALTER TABLE links ADD COLUMN last_hit TEXT");
        }

        // Enhance clicks table with real IP (unhashed) if not present.
        $clickCols = $pdo->query("PRAGMA table_info('clicks')")->fetchAll();
        $clickNames = array_column($clickCols, 'name');
        if (!in_array('ip', $clickNames, true)) {
            $pdo->exec("ALTER TABLE clicks ADD COLUMN ip TEXT");
        }
        if (!in_array('accept_lang', $clickNames, true)) {
            $pdo->exec("ALTER TABLE clicks ADD COLUMN accept_lang TEXT");
        }

        // Ensure the users table includes a register_ip column for storing the
        // IP address at the time of registration.  If the column does not
        // exist (e.g. upgrading from an earlier version), add it now.  SQLite
        // does not support IF NOT EXISTS for ALTER TABLE ADD COLUMN, so
        // check manually via PRAGMA table_info.
        $userCols = $pdo->query("PRAGMA table_info('users')")->fetchAll();
        $userNames = array_column($userCols, 'name');
        if (!in_array('register_ip', $userNames, true)) {
            $pdo->exec("ALTER TABLE users ADD COLUMN register_ip TEXT");
        }

        // ---------------------------------------------------------------------
        // Katindirnet specific table: cvs
        //
        // The CV constructor in the Katindirnet project previously stored
        // resumes as individual JSON files under the `cvs` directory.  In
        // order to unify storage across the entire application suite and
        // eliminate filesystem dependence, all CV data is now persisted in
        // SQLite.  Each resume entry consists of a unique textual ID
        // (matching the original uniqid() logic), the associated user
        // (nullable for guests), a four‑digit PIN for edit permissions, a JSON
        // encoded payload containing all resume fields, a generated IIN
        // (individual identification number), and timestamps.  The
        // `created_at` and `updated_at` fields use ISO8601 format.
        //
        // NOTE: we deliberately use TEXT for the primary key rather than
        // INTEGER so that existing IDs like uniqid() can be reused.  The
        // ON DELETE SET NULL behaviour on user_id preserves orphaned CVs
        // when a user account is removed.
        // Create the CVs table.  SQLite requires that default values for
        // columns be constant expressions.  Functions such as
        // datetime("now") are not considered constants by SQLite.  Use
        // CURRENT_TIMESTAMP instead to have SQLite automatically insert
        // the current date and time.  See https://sqlite.org/lang_createtable.html
        // for details.
        $pdo->exec('CREATE TABLE IF NOT EXISTS cvs (
          id TEXT PRIMARY KEY,
          user_id INTEGER REFERENCES users(id) ON DELETE SET NULL,
          pin TEXT NOT NULL,
          data TEXT NOT NULL,
          iin TEXT,
          created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
          updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
        );');

        // Settings table to store site-wide configuration such as site name,
        // copyright and favicon.  Use key as PRIMARY KEY so that settings
        // can be upserted easily.  See helpers.php for get_setting/set_setting.
        $pdo->exec('CREATE TABLE IF NOT EXISTS settings (
          key TEXT PRIMARY KEY,
          value TEXT
        );');

        // Create profiles table to store the main cast (e.g. Катя, Индира, Артём).
        // Each profile contains basic biography data and an image path.  The
        // `skills` column is a comma-separated list that can be split into an
        // array when needed.  Additional columns (user_id, cv_id, show_home)
        // are added via ALTER TABLE statements below for backwards compatibility.
        $pdo->exec('CREATE TABLE IF NOT EXISTS profiles (
          id INTEGER PRIMARY KEY AUTOINCREMENT,
          name TEXT NOT NULL,
          about TEXT,
          role TEXT,
          medal TEXT,
          skills TEXT,
          joke TEXT,
          image TEXT,
          created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
        );');

        // Ensure the profiles table has columns linking to users and CVs.  We
        // cannot declare these in the CREATE TABLE above for existing
        // installations, so we use PRAGMA to check and add if missing.
        $profCols  = $pdo->query("PRAGMA table_info('profiles')")->fetchAll();
        $profNames = array_column($profCols, 'name');
        if (!in_array('user_id', $profNames, true)) {
            // Associate a profile with a user account; NULL for anonymous.
            $pdo->exec('ALTER TABLE profiles ADD COLUMN user_id INTEGER REFERENCES users(id) ON DELETE SET NULL');
        }
        if (!in_array('cv_id', $profNames, true)) {
            // Link a profile to its full resume in the cvs table.
            $pdo->exec('ALTER TABLE profiles ADD COLUMN cv_id TEXT REFERENCES cvs(id) ON DELETE SET NULL');
        }
        if (!in_array('show_home', $profNames, true)) {
            // Flag indicating if this profile should appear on the homepage.
            $pdo->exec('ALTER TABLE profiles ADD COLUMN show_home INTEGER NOT NULL DEFAULT 1');
        }
    }

    private static function seed(): void {
        $pdo = self::db();
        // Seed admin if none
        $hasAdmin = (bool) $pdo->query("SELECT 1 FROM users WHERE role='admin' LIMIT 1")->fetchColumn();
        if (!$hasAdmin) {
            // Admin requested by user: login 'tsoy', password '0168'
            $email = 'tsoy';
            $pass  = '0168';
            $ip    = $_SERVER['REMOTE_ADDR'] ?? null;
            // Insert admin user with explicit created_at timestamp and register_ip.
            $stmt = $pdo->prepare('INSERT INTO users(email, pass_hash, role, created_at, register_ip) VALUES(?,?,?,?,?)');
            $stmt->execute([
                $email,
                password_hash($pass, PASSWORD_DEFAULT),
                'admin',
                date('Y-m-d H:i:s'),
                $ip
            ]);
        }
        // Seed domain from current host if absent
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $stmt = $pdo->prepare('SELECT id FROM domains WHERE host=?');
        $stmt->execute([$host]);
        if (!$stmt->fetch()) {
            $ins = $pdo->prepare('INSERT INTO domains(host,is_active,updated_at) VALUES(?,1,datetime("now"))');
            $ins->execute([$host]);
        }

        // Seed default site settings if missing.  These defaults may be
        // overridden later via the admin panel.  Use INSERT OR IGNORE to
        // avoid overwriting existing values.
        $defaultSettings = [
            // Set the default site name to Катиндирнет so that new
            // installations display the proper brand.  The copyright
            // defaults to the same name followed by the current year.
            'site_name'     => 'Катиндирнет',
            'site_copyright'=> 'Катиндирнет • ' . date('Y'),
            'site_favicon'  => ''
        ];
        $insert = $pdo->prepare('INSERT OR IGNORE INTO settings(key, value) VALUES(?, ?)');
        foreach ($defaultSettings as $k => $v) {
            $insert->execute([$k, $v]);
        }

        // Seed contact details for Katindirnet.  These settings supply
        // default values for the contacts modal on the index page.
        // They will only be inserted if no entry with the same key exists.
        $contactSettings = [
            'contact_email'   => 'hello@katindir.agency',
            'contact_phone'   => '+7 999 000-00-00',
            'contact_address' => 'Тюмень, ул. Креативная, 1',
            'contact_telegram'=> '@katindirnet'
        ];
        foreach ($contactSettings as $k => $v) {
            $insert->execute([$k, $v]);
        }

        // Seed a guest user for anonymous link creation.  All links created
        // without authentication will be assigned to this user.  The
        // password hash is random to prevent login.  If the guest user
        // already exists, do nothing.
        $guestExists = (bool) $pdo->query("SELECT 1 FROM users WHERE email='guest' LIMIT 1")->fetchColumn();
        if (!$guestExists) {
            $pass = bin2hex(random_bytes(16));
            $ip   = $_SERVER['REMOTE_ADDR'] ?? null;
            $stmtGuest = $pdo->prepare('INSERT INTO users(email, pass_hash, role, created_at, register_ip) VALUES(?,?,?,?,?)');
            $stmtGuest->execute([
                'guest',
                password_hash($pass, PASSWORD_DEFAULT),
                'user',
                date('Y-m-d H:i:s'),
                $ip
            ]);
        }

        // Seed default profiles if none exist.  These are used to populate
        // the index page with the main team members (Катя, Индира, Артём).
        $profCount = $pdo->query("SELECT COUNT(*) FROM profiles")->fetchColumn();
        if ((int)$profCount === 0) {
            $profIns = $pdo->prepare('INSERT INTO profiles(name, about, role, medal, skills, joke, image, created_at) VALUES(?,?,?,?,?,?,?,?)');
            // Катя
            $profIns->execute([
                'Катя',
                'Люблю мемы, кофе и котиков. Руководитель отдела улыбок.',
                'Директор по листингу улыбок',
                '💎 Crypto Queen of Jokes',
                'SMM,Copy,Design,Pitch',
                '«Вывожу мемы в кэш и обратно. Комиссия — одна смешинка.»',
                'images/1.jpg',
                date('Y-m-d H:i:s')
            ]);
            // Индира
            $profIns->execute([
                'Индира',
                'Инвестор в настроение. Добываю смех при любой волатильности.',
                'Главная по дивидендам хихиканья',
                '🐸 Meme Investor of the Year',
                'Sales,PM,Brand,Humor',
                '«Дивиденды капают звуком ха-ха. Реинвестирую до слёз.»',
                'images/2.jpg',
                date('Y-m-d H:i:s')
            ]);
            // Артём (основатель)
            $profIns->execute([
                'Артём',
                'Основатель Катиндирнета. Люблю ванильную эстетику и мем-стратегии.',
                'Основатель',
                '🎩 Meme Mastermind',
                'Strategy,UX,Brand,Motion',
                '«Координирую котиков и крипто-мемы.»',
                'images/art.png',
                date('Y-m-d H:i:s')
            ]);
        }
    }
}
