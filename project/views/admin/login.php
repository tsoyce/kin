<section class="card" style="max-width:420px;margin:40px auto;">
    <h1>Вход в админку</h1>
    <?php if (!empty($error)): ?>
        <div class="alert alert-error mt-sm" data-flash><?= Project\e($error); ?></div>
    <?php endif; ?>
    <form method="post" action="/admin/login_post.php" class="grid" style="gap:16px;">
        <?= Project\Security::csrfField(); ?>
        <input class="input" name="username" placeholder="Логин" required autofocus>
        <input class="input" type="password" name="password" placeholder="Пароль" required>
        <button class="button" type="submit">Войти</button>
    </form>
</section>
