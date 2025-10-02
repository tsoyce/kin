<?php
declare(strict_types=1);

// List all CVs stored in the database.  Returns an array of
// objects with id, name and optional photo data.  This is used by
// the CV search dialogue on the client side.  The list is not
// filtered by user; all public CVs are returned.  If no CVs are
// stored, returns an empty array.

require_once __DIR__ . '/helpers.php';
use App\Bootstrap;

header('Content-Type: application/json; charset=utf-8');

Bootstrap::start();
$pdo = Bootstrap::$pdo;

try {
    $res = $pdo->query('SELECT id, data FROM cvs ORDER BY created_at DESC');
    $rows = [];
    foreach ($res as $row) {
        $data = json_decode($row['data'], true);
        // Extract FIO and photo (if available) from the stored data.
        $name  = $data['fio'] ?? '';
        $photo = $data['photo'] ?? null;
        $rows[] = ['id' => $row['id'], 'name' => $name, 'photo' => $photo];
    }
    echo json_encode($rows, JSON_UNESCAPED_UNICODE);
} catch (\Throwable $e) {
    echo json_encode([]);
}