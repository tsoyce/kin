<?php
declare(strict_types=1);

// Render a CV as a standalone HTML document.  The resume data is
// retrieved from the cvs table instead of a JSON file.  If the
// specified ID does not exist, a 404 response is returned.

require_once __DIR__ . '/helpers.php';
use App\Bootstrap;

header('Content-Type: text/html; charset=utf-8');
Bootstrap::start();
$pdo = Bootstrap::$pdo;

$id = $_GET['id'] ?? '';
if (!preg_match('/^[a-zA-Z0-9]+$/', (string)$id)) {
    http_response_code(404);
    echo 'Not found';
    exit;
}
// Fetch CV data from the database
$stmt = $pdo->prepare('SELECT data, pin FROM cvs WHERE id = ?');
$stmt->execute([$id]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$row) {
    http_response_code(404);
    echo 'Not found';
    exit;
}
$data = json_decode($row['data'], true) ?: [];
$data['pin'] = $row['pin'];
?>
<!doctype html>
<html lang="ru">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <title>CV — <?=htmlspecialchars($data['fio'] ?? '')?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/tokens.css">
  <link rel="stylesheet" href="css/theme-classic-plus.css">
  <link rel="stylesheet" href="css/theme-neon-plus.css">
  <link rel="stylesheet" href="css/theme-light.css">
  <link rel="stylesheet" href="css/print.css" media="print">
  <script src="js/theme.js"></script>
</head>
<body>
<script>const resumeData = <?=json_encode($data, JSON_UNESCAPED_UNICODE|JSON_HEX_TAG);?>; const resumeId = '<?=htmlspecialchars($id, ENT_QUOTES)?>';</script>
<script src="js/resume.js"></script>
</body>
</html>
