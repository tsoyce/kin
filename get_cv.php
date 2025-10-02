<?php
declare(strict_types=1);

// Retrieve a CV by ID and PIN.  Returns JSON { ok: true }
// when the PIN matches, otherwise { ok: false }.  The actual
// resume content is not returned by this endpoint; use view_cv.php
// to render the resume.  This endpoint is used to validate
// access to a CV before redirecting to the view.

require_once __DIR__ . '/helpers.php';
use App\Bootstrap;

header('Content-Type: application/json; charset=utf-8');

Bootstrap::start();
$pdo = Bootstrap::$pdo;

$id = $_GET['id'] ?? '';
$pin = $_GET['pin'] ?? '';

if (!preg_match('/^[a-zA-Z0-9]+$/', $id) || !preg_match('/^\d{4}$/', (string)$pin)) {
    echo json_encode(['ok' => false]);
    exit;
}
try {
    $stmt = $pdo->prepare('SELECT pin FROM cvs WHERE id = ?');
    $stmt->execute([$id]);
    $storedPin = $stmt->fetchColumn();
    if (!$storedPin) {
        echo json_encode(['ok' => false]);
        exit;
    }
    echo json_encode(['ok' => $storedPin === $pin]);
} catch (\Throwable $e) {
    echo json_encode(['ok' => false]);
}