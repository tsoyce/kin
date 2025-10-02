<?php
declare(strict_types=1);

// CV save endpoint for Katindirnet.
//
// Accepts JSON payload via POST with fields:
//   - data: an associative array containing all resume fields
//   - pin: a 4‑digit string used to authorise edits
//   - id (optional): existing resume ID to update
//
// If id is provided, the CV will be updated only if the supplied PIN
// matches the stored PIN.  If id is not provided, a new record will be
// created.  In both cases an IIN (individual identification number) is
// generated once and reused on subsequent saves.  On success, returns
// JSON { ok: true, id: <id>, iin: <iin> }.  On error, returns { ok: false }.

require_once __DIR__ . '/helpers.php';

use App\Bootstrap;

header('Content-Type: application/json; charset=utf-8');

// Initialise database and session via Bootstrap.  This ensures that
// $_SESSION is available for reading the current user and the PDO
// connection is ready.
Bootstrap::start();
$pdo = Bootstrap::$pdo;

// Read and decode JSON input.  Do not trust client input blindly.
$input = json_decode(file_get_contents('php://input'), true);
if (!$input || !isset($input['data']) || !isset($input['pin']) || !preg_match('/^\d{4}$/', (string)$input['pin'])) {
    echo json_encode(['ok' => false]);
    exit;
}

$data = $input['data'];
$pin  = (string)$input['pin'];
$id   = isset($input['id']) && preg_match('/^[a-zA-Z0-9]+$/', $input['id']) ? $input['id'] : '';

// Helper to generate a pseudo‑unique textual ID.  We reuse uniqid()
// semantics to keep backwards compatibility with the previous
// filesystem implementation.  The ID consists of a base36 encoded
// random integer and the current timestamp.  It is short yet
// sufficiently unique for our use case.
function generate_id(): string {
    $rand = random_int(0, 0xFFFFFF);
    $time = microtime(true);
    return dechex($rand) . dechex((int)($time * 1000000));
}

// Generate an IIN (Individual Identification Number) based on the
// provided resume data and current date.  The IIN format is:
//   birth (6 digits: YYMMDD) + gender digit (1/2/0) + now (YYMMDD) + seq (two digits)
// Sequence number increments for each CV generated on the same day.
function generate_iin(array $data, PDO $pdo): string {
    // Birth part: last two digits of year + month + day, fallback to '000000'.
    $birthPart = '000000';
    if (!empty($data['birth']) && preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $data['birth'], $m)) {
        $birthPart = substr($m[1], 2, 2) . $m[2] . $m[3];
    }
    // Gender digit: 1 for male, 2 for female, 0 otherwise.
    $genderMap = ['Мужской' => '1', 'Женский' => '2'];
    $genderDigit = $genderMap[$data['gender'] ?? ''] ?? '0';
    // Current date in YYMMDD format.
    $current = date('ymd');
    // Determine sequence number: count existing CVs for today.
    // We look at the substring of IIN starting after the gender digit
    // (offset 7 if 1‑indexed) of length 6, which corresponds to the
    // current date for that record.  We then count how many have
    // the same current date and increment.  The two‑digit sequence wraps
    // at 99.
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM cvs WHERE substr(iin, 8, 6) = ?");
    $stmt->execute([$current]);
    $count = (int)$stmt->fetchColumn();
    $seq = str_pad((string)($count + 1), 2, '0', STR_PAD_LEFT);
    return $birthPart . $genderDigit . $current . $seq;
}

try {
    // Determine the user ID.  If a user is logged in, assign the CV to
    // their account.  Otherwise fall back to the guest user.  The
    // Auth::user() helper returns null if not authenticated.
    $user = App\Auth::user();
    $userId = $user['id'] ?? guest_id();

    // Fetch existing record if id provided.
    $existing = null;
    if ($id) {
        $stmt = $pdo->prepare('SELECT id, pin, iin, user_id FROM cvs WHERE id = ?');
        $stmt->execute([$id]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$existing) {
            // Provided ID does not exist.
            echo json_encode(['ok' => false]);
            exit;
        }
        // Only allow updating CV if PIN matches.
        if ((string)$existing['pin'] !== $pin) {
            echo json_encode(['ok' => false]);
            exit;
        }
    }

    // Generate IIN if not already present.  If this is an update and the
    // record has an existing IIN, reuse it to maintain continuity.
    $iin = $existing['iin'] ?? null;
    if (!$iin) {
        $iin = generate_iin($data, $pdo);
    }
    $data['iin'] = $iin;

    // Encode the resume data as JSON.  Use pretty print for readability
    // and ensure Unicode characters are not escaped.  The encoded
    // structure is stored verbatim in the database.
    $jsonData = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    if ($jsonData === false) {
        echo json_encode(['ok' => false]);
        exit;
    }

    if ($existing) {
        // Update existing CV.  We update the data and the updated_at
        // timestamp.  The PIN is not changed.
        $stmt = $pdo->prepare('UPDATE cvs SET data = ?, iin = ?, updated_at = datetime("now") WHERE id = ?');
        $stmt->execute([$jsonData, $iin, $id]);
    } else {
        // Generate a new ID.  Ensure uniqueness by looping until an
        // unused ID is found.  Because the ID space is large, the
        // probability of collision is extremely small.
        do {
            $newId = generate_id();
            $check = $pdo->prepare('SELECT 1 FROM cvs WHERE id = ?');
            $check->execute([$newId]);
        } while ($check->fetchColumn());
        $id = $newId;
        // Insert new CV.
        $stmt = $pdo->prepare('INSERT INTO cvs(id, user_id, pin, data, iin, created_at, updated_at) VALUES(?,?,?,?,?, datetime("now"), datetime("now"))');
        $stmt->execute([$id, $userId, $pin, $jsonData, $iin]);
    }
    echo json_encode(['ok' => true, 'id' => $id, 'iin' => $iin]);
} catch (\Throwable $e) {
    // Log the error in production.  For now simply return failure.
    echo json_encode(['ok' => false]);
}