<?php
// Admin panel for Katindirnet
//
// This page allows the administrator to manage site‑wide settings such as
// contact information and profile cards, as well as view and manage all
// resumes (CVs) stored in the database.  It requires an authenticated user
// with an admin role.  The layout and styling mirror the Katindirnet
// aesthetic by reusing the same CSS files as the main site.

declare(strict_types=1);
require_once __DIR__ . '/helpers.php';

use App\Auth;
use App\CSRF;
use App\Bootstrap;

// Restrict access to administrators only.  If the current user does not
// have the admin role they will receive a 403 response.
Auth::requireRole('admin');

// Pull the current user for display in the header
$u = user();

// Initialise arrays for displaying messages back to the user.  These
// messages will be rendered at the top of the page after form handling.
$messages = [];

// Handle POST requests for updating settings, profiles and CVs.  The
// presence of certain POST fields indicates which action should be
// performed.  CSRF tokens are validated for every action.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf'] ?? '';
    if (!CSRF::check($token)) {
        $messages[] = ['type' => 'error', 'text' => 'Неверный CSRF токен. Попробуйте ещё раз.'];
    } else {
        $pdo = Bootstrap::$pdo;
        // Update contact details
        if (isset($_POST['update_contacts'])) {
            set_setting('contact_email',   trim((string)$_POST['contact_email']));
            set_setting('contact_phone',   trim((string)$_POST['contact_phone']));
            set_setting('contact_address', trim((string)$_POST['contact_address']));
            set_setting('contact_telegram',trim((string)$_POST['contact_telegram']));
            $messages[] = ['type' => 'success', 'text' => 'Контактные данные обновлены.'];
        }
        // Update general site settings
        if (isset($_POST['update_site'])) {
            set_setting('site_name', trim((string)$_POST['site_name']));
            set_setting('site_favicon', trim((string)$_POST['site_favicon']));
            set_setting('site_copyright', trim((string)$_POST['site_copyright']));
            $messages[] = ['type' => 'success', 'text' => 'Настройки сайта сохранены.'];
        }
        // Update an existing profile
        if (isset($_POST['save_profile'])) {
            $pid   = (int)($_POST['profile_id'] ?? 0);
            $name  = trim((string)$_POST['name']);
            $about = trim((string)$_POST['about']);
            $role  = trim((string)$_POST['role']);
            $medal = trim((string)$_POST['medal']);
            $skills= trim((string)$_POST['skills']);
            $joke  = trim((string)$_POST['joke']);
            $image = trim((string)$_POST['image']);
            if ($pid > 0) {
                $stmt = $pdo->prepare('UPDATE profiles SET name=?, about=?, role=?, medal=?, skills=?, joke=?, image=? WHERE id=?');
                $stmt->execute([$name,$about,$role,$medal,$skills,$joke,$image,$pid]);
                $messages[] = ['type' => 'success', 'text' => 'Профиль обновлён.'];
            }
        }
        // Delete a profile
        if (isset($_POST['delete_profile'])) {
            $pid = (int)($_POST['profile_id'] ?? 0);
            if ($pid > 0) {
                $pdo->prepare('DELETE FROM profiles WHERE id=?')->execute([$pid]);
                $messages[] = ['type' => 'success', 'text' => 'Профиль удалён.'];
            }
        }
        // Add a new profile
        if (isset($_POST['add_profile'])) {
            $name  = trim((string)$_POST['name']);
            $about = trim((string)$_POST['about']);
            $role  = trim((string)$_POST['role']);
            $medal = trim((string)$_POST['medal']);
            $skills= trim((string)$_POST['skills']);
            $joke  = trim((string)$_POST['joke']);
            $image = trim((string)$_POST['image']);
            if ($name !== '') {
                $stmt = $pdo->prepare('INSERT INTO profiles(name, about, role, medal, skills, joke, image, created_at) VALUES(?,?,?,?,?,?,?,?)');
                $stmt->execute([$name,$about,$role,$medal,$skills,$joke,$image,date('Y-m-d H:i:s')]);
                $messages[] = ['type' => 'success', 'text' => 'Новый профиль добавлен.'];
            }
        }
        // Reset a CV pin
        if (isset($_POST['reset_pin'])) {
            $cvId = (string)($_POST['cv_id'] ?? '');
            if ($cvId !== '') {
                $newPin = str_pad((string)random_int(0, 9999), 4, '0', STR_PAD_LEFT);
                $stmt = $pdo->prepare('UPDATE cvs SET pin=?, updated_at=CURRENT_TIMESTAMP WHERE id=?');
                $stmt->execute([$newPin, $cvId]);
                $messages[] = ['type' => 'success', 'text' => 'ПИН обновлён до ' . h($newPin) . '.'];
            }
        }
        // Delete a CV
        if (isset($_POST['delete_cv'])) {
            $cvId = (string)($_POST['cv_id'] ?? '');
            if ($cvId !== '') {
                $pdo->prepare('DELETE FROM cvs WHERE id=?')->execute([$cvId]);
                $messages[] = ['type' => 'success', 'text' => 'Резюме удалено.'];
            }
        }
    }
}

// Fetch settings and data for display
$settings = [
    'site_name'      => get_setting('site_name','Катиндирнет'),
    'site_favicon'   => get_setting('site_favicon',''),
    'site_copyright' => get_setting('site_copyright',''),
    'contact_email'  => get_setting('contact_email',''),
    'contact_phone'  => get_setting('contact_phone',''),
    'contact_address'=> get_setting('contact_address',''),
    'contact_telegram'=> get_setting('contact_telegram',''),
];

// Fetch all profiles for editing
$profiles = Bootstrap::$pdo->query('SELECT * FROM profiles ORDER BY id')->fetchAll();

// Fetch all CVs along with user email.  Use LEFT JOIN so that guest CVs are shown with NULL user email.
$cvStmt = Bootstrap::$pdo->prepare('SELECT cvs.*, users.email AS user_email FROM cvs LEFT JOIN users ON cvs.user_id = users.id ORDER BY created_at DESC');
$cvStmt->execute();
$cvs = $cvStmt->fetchAll();

?>
<!doctype html>
<html lang="ru">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover" />
  <title>Админка — Катиндирнет</title>
  <!-- Include the same fonts and styles as the main site to preserve the look and feel -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/index.css">
  <link rel="stylesheet" href="css/tokens.css">
  <link rel="stylesheet" href="css/theme-classic-plus.css">
  <link rel="stylesheet" href="css/theme-neon-plus.css">
  <link rel="stylesheet" href="css/theme-light.css">
  <script src="js/theme.js"></script>
</head>
<body>
  <div class="wrap">
    <header>
      <div class="brand">
        <div class="logo">КА</div>
        <div>
          <h1>Катиндирнет</h1>
          <div class="subtitle">Административная панель</div>
        </div>
      </div>
      <!-- Admin navigation -->
      <div class="controls">
        <a class="btn" href="index.php">Главная</a>
        <a class="btn" href="id.php">Личный кабинет</a>
        <form method="post" action="<?php echo h($_SERVER['PHP_SELF']); ?>" style="display:inline;">
          <!-- Logout button simply links to logout.php to destroy the session -->
        </form>
        <a class="btn" href="logout.php">Выйти</a>
      </div>
    </header>

    <!-- Display any feedback messages -->
    <?php foreach ($messages as $msg): ?>
      <div class="alert <?php echo $msg['type'] === 'success' ? 'alert-green' : 'alert-red'; ?>">
        <?php echo h($msg['text']); ?>
      </div>
    <?php endforeach; ?>

    <!-- Site settings section -->
    <section class="hero" style="margin-bottom:20px;">
      <h2 class="title">Настройки сайта</h2>
      <form method="post" class="controls" style="flex-direction:column; gap:8px;" action="admin.php">
        <?php echo csrf_field(); ?>
        <div class="modal-row">
          <label>Название сайта</label>
          <input class="btn" style="flex:1;" type="text" name="site_name" value="<?php echo h($settings['site_name']); ?>" />
        </div>
        <div class="modal-row">
          <label>URL иконки (favicon)</label>
          <input class="btn" style="flex:1;" type="text" name="site_favicon" value="<?php echo h($settings['site_favicon']); ?>" placeholder="/path/to/favicon.ico" />
        </div>
        <div class="modal-row">
          <label>Копирайт</label>
          <input class="btn" style="flex:1;" type="text" name="site_copyright" value="<?php echo h($settings['site_copyright']); ?>" placeholder="Катиндирнет • <?php echo date('Y'); ?>" />
        </div>
        <button class="btn primary" type="submit" name="update_site">Сохранить настройки</button>
      </form>
    </section>

    <!-- Contact details section -->
    <section class="hero" style="margin-bottom:20px;">
      <h2 class="title">Контакты</h2>
      <form method="post" class="controls" style="flex-direction:column; gap:8px;" action="admin.php">
        <?php echo csrf_field(); ?>
        <div class="modal-row">
          <label>E‑mail</label>
          <input class="btn" style="flex:1;" type="text" name="contact_email" value="<?php echo h($settings['contact_email']); ?>" />
        </div>
        <div class="modal-row">
          <label>Телефон</label>
          <input class="btn" style="flex:1;" type="text" name="contact_phone" value="<?php echo h($settings['contact_phone']); ?>" />
        </div>
        <div class="modal-row">
          <label>Адрес</label>
          <input class="btn" style="flex:1;" type="text" name="contact_address" value="<?php echo h($settings['contact_address']); ?>" />
        </div>
        <div class="modal-row">
          <label>Telegram</label>
          <input class="btn" style="flex:1;" type="text" name="contact_telegram" value="<?php echo h($settings['contact_telegram']); ?>" />
        </div>
        <button class="btn primary" type="submit" name="update_contacts">Сохранить контакты</button>
      </form>
    </section>

    <!-- Profiles management section -->
    <section class="hero" style="margin-bottom:20px;">
      <h2 class="title">Профили</h2>
      <!-- If a profile is selected for editing (via ?edit_profile=ID), show the edit form -->
      <?php if ($editProfile): ?>
        <form method="post" class="card" style="flex-direction:column; gap:6px; margin-bottom:10px;" action="admin.php?edit_profile=<?php echo (int)$editProfile['id']; ?>">
          <?php echo csrf_field(); ?>
          <input type="hidden" name="profile_id" value="<?php echo (int)$editProfile['id']; ?>">
          <input type="hidden" name="user_id" value="<?php echo (int)($editProfile['user_id'] ?? 0); ?>">
          <div class="modal-row">
            <label>Имя</label>
            <input class="btn" type="text" name="name" value="<?php echo h($editProfile['name']); ?>" required>
          </div>
          <div class="modal-row">
            <label>Обо мне</label>
            <input class="btn" type="text" name="about" value="<?php echo h($editProfile['about']); ?>">
          </div>
          <div class="modal-row">
            <label>Роль (отображение)</label>
            <input class="btn" type="text" name="role" value="<?php echo h($editProfile['role']); ?>">
          </div>
          <div class="modal-row">
            <label>Медаль</label>
            <input class="btn" type="text" name="medal" value="<?php echo h($editProfile['medal']); ?>">
          </div>
          <div class="modal-row">
            <label>Навыки (через запятую)</label>
            <input class="btn" type="text" name="skills" value="<?php echo h($editProfile['skills']); ?>">
          </div>
          <div class="modal-row">
            <label>Шутка</label>
            <input class="btn" type="text" name="joke" value="<?php echo h($editProfile['joke']); ?>">
          </div>
          <div class="modal-row">
            <label>Изображение</label>
            <input class="btn" type="text" name="image" value="<?php echo h($editProfile['image']); ?>" placeholder="images/1.jpg">
          </div>
          <div class="modal-row">
            <label>Показывать на главной</label>
            <input type="checkbox" name="show_home" <?php echo ($editProfile['show_home'] ?? 0) ? 'checked' : ''; ?>>
          </div>
          <?php if ($editProfile['user_id']): ?>
            <div class="modal-row">
              <label>Логин (e‑mail/ник)</label>
              <input class="btn" type="text" name="user_email" value="<?php echo h($editProfile['user_email']); ?>">
            </div>
            <div class="modal-row">
              <label>Роль пользователя</label>
              <select class="btn" name="user_role">
                <?php $roles = ['admin' => 'Администратор', 'editor' => 'Редактор', 'user' => 'Читатель']; ?>
                <?php foreach ($roles as $val => $label): ?>
                  <option value="<?php echo $val; ?>" <?php echo ($editProfile['user_role'] === $val) ? 'selected' : ''; ?>>
                    <?php echo $label; ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="modal-row">
              <label>Новый пароль</label>
              <input class="btn" type="password" name="new_password" placeholder="Оставьте пустым, чтобы не менять">
            </div>
            <div class="modal-row">
              <label>Подтверждение пароля</label>
              <input class="btn" type="password" name="new_password2" placeholder="Повторите пароль">
            </div>
          <?php else: ?>
            <p class="alert">У этого профиля нет связанного пользователя.</p>
          <?php endif; ?>
          <div class="controls" style="gap:8px;">
            <button class="btn primary" type="submit" name="edit_profile_save">Сохранить</button>
            <button class="btn danger" type="submit" name="delete_profile" onclick="return confirm('Удалить профиль?');">Удалить</button>
          </div>
        </form>
      <?php endif; ?>

      <!-- Table of profiles -->
      <div style="overflow-x:auto;">
        <table class="table" style="width:100%; border-collapse:collapse;">
          <thead>
            <tr style="border-bottom:1px solid rgba(255,255,255,.15)">
              <th style="padding:6px; text-align:left;">ID</th>
              <th style="padding:6px; text-align:left;">Имя</th>
              <th style="padding:6px; text-align:left;">Логин</th>
              <th style="padding:6px; text-align:left;">Роль</th>
              <th style="padding:6px; text-align:left;">На&nbsp;главной</th>
              <th style="padding:6px; text-align:left;">Действия</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($profiles as $p): ?>
            <tr style="border-bottom:1px solid rgba(255,255,255,.1)">
              <td style="padding:6px;"><?php echo (int)$p['id']; ?></td>
              <td style="padding:6px;">
                <a href="admin.php?edit_profile=<?php echo (int)$p['id']; ?>" class="btn"><?php echo h($p['name']); ?></a>
              </td>
              <td style="padding:6px; "><?php echo $p['user_email'] ? h($p['user_email']) : '-'; ?></td>
              <td style="padding:6px; "><?php echo $p['user_role'] ? h($p['user_role']) : '-'; ?></td>
              <td style="padding:6px; "><?php echo ($p['show_home'] ?? 0) ? 'Да' : 'Нет'; ?></td>
              <td style="padding:6px; ">
                <form method="post" action="admin.php" style="display:inline-block;" onsubmit="return confirm('Удалить профиль?');">
                  <?php echo csrf_field(); ?>
                  <input type="hidden" name="profile_id" value="<?php echo (int)$p['id']; ?>">
                  <input type="hidden" name="user_id" value="<?php echo (int)($p['user_id'] ?? 0); ?>">
                  <button class="btn danger" type="submit" name="delete_profile">🗑️</button>
                </form>
              </td>
            </tr>
            <?php endforeach; ?>
            <?php if (!$profiles): ?>
            <tr><td colspan="6" style="padding:6px;">Профили отсутствуют.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>

      <!-- Form to add a new profile -->
      <form method="post" class="card" style="flex-direction:column; gap:6px; margin-top:15px;" action="admin.php">
        <?php echo csrf_field(); ?>
        <h3 style="margin:0">Добавить новый профиль</h3>
        <div class="modal-row">
          <label>Имя</label>
          <input class="btn" type="text" name="name" required>
        </div>
        <div class="modal-row">
          <label>Обо мне</label>
          <input class="btn" type="text" name="about">
        </div>
        <div class="modal-row">
          <label>Роль (отображение)</label>
          <input class="btn" type="text" name="role">
        </div>
        <div class="modal-row">
          <label>Медаль</label>
          <input class="btn" type="text" name="medal">
        </div>
        <div class="modal-row">
          <label>Навыки (через запятую)</label>
          <input class="btn" type="text" name="skills">
        </div>
        <div class="modal-row">
          <label>Шутка</label>
          <input class="btn" type="text" name="joke">
        </div>
        <div class="modal-row">
          <label>Изображение</label>
          <input class="btn" type="text" name="image" placeholder="images/new.jpg">
        </div>
        <div class="modal-row">
          <label>Показывать на главной</label>
          <input type="checkbox" name="show_home" checked>
        </div>
        <h4 style="margin-top:10px; margin-bottom:4px;">Данные пользователя</h4>
        <div class="modal-row">
          <label>Логин (e‑mail/ник)</label>
          <input class="btn" type="text" name="new_user_login" required>
        </div>
        <div class="modal-row">
          <label>Пароль</label>
          <input class="btn" type="password" name="new_user_password" required>
        </div>
        <div class="modal-row">
          <label>Роль пользователя</label>
          <select class="btn" name="new_user_role">
            <option value="user">Читатель</option>
            <option value="editor">Редактор</option>
            <option value="admin">Администратор</option>
          </select>
        </div>
        <button class="btn primary" type="submit" name="add_profile_new">Добавить</button>
      </form>
    </section>

    <!-- CV management section -->
    <section class="hero" style="margin-bottom:20px;">
      <h2 class="title">Резюме (CV)</h2>
      <div style="overflow-x:auto;">
        <table class="table" style="width:100%; border-collapse:collapse;">
          <thead>
            <tr style="border-bottom:1px solid rgba(255,255,255,.15)">
              <th style="text-align:left; padding:8px;">ID</th>
              <th style="text-align:left; padding:8px;">Пользователь</th>
              <th style="text-align:left; padding:8px;">Создано</th>
              <th style="text-align:left; padding:8px;">Обновлено</th>
              <th style="text-align:left; padding:8px;">Действия</th>
            </tr>
          </thead>
          <tbody>
          <?php foreach ($cvs as $cv): ?>
            <tr style="border-bottom:1px solid rgba(255,255,255,.1)">
              <td style="padding:8px; font-family:monospace;"><?php echo h($cv['id']); ?></td>
              <td style="padding:8px;"><?php echo $cv['user_email'] ? h($cv['user_email']) : 'Гость'; ?></td>
              <td style="padding:8px;"><?php echo h($cv['created_at']); ?></td>
              <td style="padding:8px;"><?php echo h($cv['updated_at']); ?></td>
              <td style="padding:8px;">
                <form method="post" action="admin.php" style="display:inline-block; margin-right:4px;">
                  <?php echo csrf_field(); ?>
                  <input type="hidden" name="cv_id" value="<?php echo h($cv['id']); ?>">
                  <button class="btn" type="submit" name="reset_pin" title="Сбросить ПИН">🔄</button>
                </form>
                <a class="btn" href="view_cv.php?id=<?php echo h($cv['id']); ?>&pin=<?php echo h($cv['pin']); ?>" target="_blank" title="Просмотр">👁️</a>
                <form method="post" action="admin.php" style="display:inline-block;" onsubmit="return confirm('Удалить резюме?');">
                  <?php echo csrf_field(); ?>
                  <input type="hidden" name="cv_id" value="<?php echo h($cv['id']); ?>">
                  <button class="btn danger" type="submit" name="delete_cv" title="Удалить">🗑️</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
          <?php if (!$cvs): ?>
            <tr><td colspan="5" style="padding:8px;">Резюме не найдены.</td></tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </section>

    <footer>
      <?php echo h(get_setting('site_copyright','Катиндирнет ' . date('Y'))); ?>
    </footer>
  </div>
</body>
</html>