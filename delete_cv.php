<?php
declare(strict_types=1);

// Delete a CV by ID and PIN.  Accepts JSON via POST with
// { id: <id>, pin: <pin> }.  Returns { ok: true } on success.
// If the PIN is incorrect or the ID does not exist, returns
// { ok: false }.  The row is removed from the database, and
// associated data is permanently deleted.

require_once __DIR__ . '/helpers.php';
use App\Bootstrap;

header('Content-Type: application/json; charset=utf-8');

Bootstrap::start();
$pdo = Bootstrap::$pdo;

$input = json_decode(file_get_contents('php://input'), true);
$id  = $input['id'] ?? '';
$pin = $input['pin'] ?? '';
if (!preg_match('/^[a-zA-Z0-9]+$/', (string)$id) || !preg_match('/^\d{4}$/', (string)$pin)) {
    echo json_encode(['ok' => false]);
    exit;
}
try {
    $stmt = $pdo->prepare('SELECT pin FROM cvs WHERE id = ?');
    $stmt->execute([$id]);
    $storedPin = $stmt->fetchColumn();
    if (!$storedPin || $storedPin !== (string)$pin) {
        echo json_encode(['ok' => false]);
        exit;
    }
    // Delete the record
    $del = $pdo->prepare('DELETE FROM cvs WHERE id = ?');
    $del->execute([$id]);
    echo json_encode(['ok' => true]);
} catch (\Throwable $e) {
    echo json_encode(['ok' => false]);
}