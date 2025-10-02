<?php
// id.php — unified personal account and authentication page

require __DIR__.'/helpers.php';

use App\Auth;
use App\Bootstrap;
use App\CSRF;

$title = 'Личный кабинет';
$pdo   = Bootstrap::$pdo;
$u     = Auth::user();
$messages = [];

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF check
    if (!CSRF::verify($_POST['csrf'] ?? null)) {
        $messages[] = ['type' => 'error', 'text' => 'CSRF токен недействителен.'];
    } else {
        // Registration
        if (isset($_POST['register'])) {
            $login     = trim($_POST['reg_login'] ?? '');
            $password  = $_POST['reg_password'] ?? '';
            $password2 = $_POST['reg_password2'] ?? '';
            if ($login === '' || !preg_match('/^[A-Za-z0-9_-]{3,32}$/', $login)) {
                $messages[] = ['type' => 'error', 'text' => 'Логин должен содержать 3–32 символа: латинские буквы, цифры, "_" или "-".'];
            } elseif ($password === '' || strlen($password) < 6) {
                $messages[] = ['type' => 'error', 'text' => 'Пароль должен содержать не менее 6 символов.'];
            } elseif ($password !== $password2) {
                $messages[] = ['type' => 'error', 'text' => 'Пароли не совпадают.'];
            } else {
                // Attempt registration; role defaults to user
                if (Auth::register($login, $password, 'user')) {
                    // Login after successful registration
                    if (Auth::login($login, $password)) {
                        $u = Auth::user();
                        $messages[] = ['type' => 'success', 'text' => 'Регистрация прошла успешно. Вы вошли как ' . h($login) . '.'];
                    } else {
                        $messages[] = ['type' => 'error', 'text' => 'Не удалось выполнить автоматический вход. Попробуйте войти.'];
                    }
                } else {
                    $messages[] = ['type' => 'error', 'text' => 'Не удалось зарегистрировать. Возможно, логин занят.'];
                }
            }
        }
        // Login
        if (isset($_POST['login'])) {
            $login    = trim($_POST['login_login'] ?? '');
            $password = $_POST['login_password'] ?? '';
            if ($login === '' || $password === '') {
                $messages[] = ['type' => 'error', 'text' => 'Введите логин и пароль.'];
            } else {
                if (Auth::login($login, $password)) {
                    $u = Auth::user();
                    $messages[] = ['type' => 'success', 'text' => 'Вы успешно вошли.'];
                } else {
                    $messages[] = ['type' => 'error', 'text' => 'Неверная пара логин/пароль.'];
                }
            }
        }
        // Change login (username)
        if ($u && isset($_POST['change_login'])) {
            $newLogin = trim($_POST['new_login'] ?? '');
            if ($newLogin === '' || !preg_match('/^[A-Za-z0-9_-]{3,32}$/', $newLogin)) {
                $messages[] = ['type' => 'error', 'text' => 'Новый логин должен содержать 3–32 символа: латинские буквы, цифры, "_" или "-".'];
            } else {
                // Check if login is taken by another user
                $stmt = $pdo->prepare('SELECT COUNT(*) FROM users WHERE email = ? AND id <> ?');
                $stmt->execute([$newLogin, $u['id']]);
                if ($stmt->fetchColumn()) {
                    $messages[] = ['type' => 'error', 'text' => 'Этот логин уже занят.'];
                } else {
                    $stmt = $pdo->prepare('UPDATE users SET email = ? WHERE id = ?');
                    $stmt->execute([$newLogin, $u['id']]);
                    $messages[] = ['type' => 'success', 'text' => 'Логин успешно изменён.'];
                    // Update local user array
                    $u['email'] = $newLogin;
                }
            }
        }
        // Change password
        if ($u && isset($_POST['change_password'])) {
            $curPass = $_POST['current_password'] ?? '';
            $newPass = $_POST['new_password'] ?? '';
            $newPass2= $_POST['new_password2'] ?? '';
            if ($curPass === '' || $newPass === '' || $newPass2 === '') {
                $messages[] = ['type' => 'error', 'text' => 'Заполните все поля для смены пароля.'];
            } elseif ($newPass !== $newPass2) {
                $messages[] = ['type' => 'error', 'text' => 'Новый пароль и подтверждение не совпадают.'];
            } elseif (strlen($newPass) < 6) {
                $messages[] = ['type' => 'error', 'text' => 'Новый пароль должен содержать не менее 6 символов.'];
            } else {
                // Verify current password
                $stmt = $pdo->prepare('SELECT pass_hash FROM users WHERE id = ?');
                $stmt->execute([$u['id']]);
                $row = $stmt->fetch();
                if (!$row || !password_verify($curPass, $row['pass_hash'])) {
                    $messages[] = ['type' => 'error', 'text' => 'Текущий пароль неверен.'];
                } else {
                    // Update new password
                    $hash = password_hash($newPass, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare('UPDATE users SET pass_hash = ? WHERE id = ?');
                    $stmt->execute([$hash, $u['id']]);
                    $messages[] = ['type' => 'success', 'text' => 'Пароль успешно изменён.'];
                }
            }
        }
    }
}

include __DIR__.'/partials/header.php';
?>
<section class="max-w-md mx-auto space-y-6">
  <h1 class="text-2xl font-bold mb-4">Личный кабинет</h1>
  <?php foreach ($messages as $msg): ?>
    <div class="<?php echo ($msg['type'] === 'error') ? 'alert-red' : 'alert-green'; ?> alert mb-4">
      <?php echo h($msg['text']); ?>
    </div>
  <?php endforeach; ?>

<?php if (!$u): ?>
    <!-- Unified authentication pane with toggle for login/registration -->
    <div class="glass rounded-xl p-6">
      <div class="flex gap-2 mb-4 justify-center">
        <button id="tab-login" type="button" class="btn btn-ghost">Вход</button>
        <button id="tab-register" type="button" class="btn btn-ghost">Регистрация</button>
      </div>
      <!-- Login form (shown by default) -->
      <div id="pane-login" class="space-y-3">
        <form method="post" class="space-y-3">
          <?php echo csrf_field(); ?>
          <input class="input w-full" name="login_login" placeholder="Логин" required>
          <input class="input w-full" type="password" name="login_password" placeholder="Пароль" required>
          <button class="btn-primary w-full" name="login" type="submit">Войти</button>
        </form>
      </div>
      <!-- Registration form (hidden by default) -->
      <div id="pane-register" class="space-y-3 hidden">
        <form method="post" class="space-y-3">
          <?php echo csrf_field(); ?>
          <input class="input w-full" name="reg_login" placeholder="Логин" required>
          <input class="input w-full" type="password" name="reg_password" placeholder="Пароль (мин. 6)" required>
          <input class="input w-full" type="password" name="reg_password2" placeholder="Повторите пароль" required>
          <button class="btn-primary w-full" name="register" type="submit">Создать аккаунт</button>
        </form>
      </div>
    </div>
    <script>
      // Toggle between login and registration forms
      (function() {
        const btnLogin = document.getElementById('tab-login');
        const btnRegister = document.getElementById('tab-register');
        const paneLogin = document.getElementById('pane-login');
        const paneRegister = document.getElementById('pane-register');
        function activate(tab) {
          if (tab === 'login') {
            paneLogin.classList.remove('hidden');
            paneRegister.classList.add('hidden');
            btnLogin.classList.remove('btn-ghost');
            btnLogin.classList.add('btn');
            btnRegister.classList.remove('btn');
            btnRegister.classList.add('btn-ghost');
          } else {
            paneLogin.classList.add('hidden');
            paneRegister.classList.remove('hidden');
            btnRegister.classList.remove('btn-ghost');
            btnRegister.classList.add('btn');
            btnLogin.classList.remove('btn');
            btnLogin.classList.add('btn-ghost');
          }
        }
        btnLogin.addEventListener('click', () => activate('login'));
        btnRegister.addEventListener('click', () => activate('register'));
        // Initialise default view
        activate('login');
      })();
    </script>
  <?php else: ?>
    <!-- Logged-in user: show profile and settings -->
    <div class="glass rounded-xl p-6 space-y-6">
      <p class="text-lg">Вы вошли как <strong><?php echo h($u['email']); ?></strong> (роль: <?php echo h($u['role']); ?>)</p>
      <form method="post" class="space-y-3">
        <?php echo csrf_field(); ?>
        <h3 class="text-lg font-semibold">Сменить логин</h3>
        <input class="input w-full" name="new_login" value="<?php echo h($u['email']); ?>" required>
        <button class="btn-ghost" name="change_login" type="submit">Изменить логин</button>
      </form>
      <hr class="border-white/10">
      <form method="post" class="space-y-3">
        <?php echo csrf_field(); ?>
        <h3 class="text-lg font-semibold">Сменить пароль</h3>
        <input class="input w-full" type="password" name="current_password" placeholder="Текущий пароль" required>
        <input class="input w-full" type="password" name="new_password" placeholder="Новый пароль (мин. 6)" required>
        <input class="input w-full" type="password" name="new_password2" placeholder="Повторите новый пароль" required>
        <button class="btn-ghost" name="change_password" type="submit">Изменить пароль</button>
      </form>
      <div class="mt-4 flex flex-col gap-3">
        <?php if ($u['role'] === 'admin'): ?>
          <a class="btn" href="/admin.php">Перейти в админку</a>
        <?php endif; ?>
        <a class="btn-danger" href="/logout.php">Выйти</a>
      </div>
    </div>
  <?php endif; ?>
</section>
<?php include __DIR__.'/partials/footer.php'; ?>