<?php
/** @var array|null $user */
/** @var array $messages */
?>
<section class="card">
    <h1>Личный кабинет</h1>
    <?php foreach ($messages as $message): ?>
        <div class="alert <?= Project\e($message['type'] === 'success' ? 'alert-success' : 'alert-error'); ?> mt-sm" data-flash>
            <?= Project\e($message['text']); ?>
        </div>
    <?php endforeach; ?>

    <?php if (!$user): ?>
        <div class="tabs" data-tabs>
            <button type="button" class="active" data-tab="login">Вход</button>
            <button type="button" data-tab="register">Регистрация</button>
        </div>
        <div class="tab-panel active" data-tab-panel="login">
            <form method="post" class="grid" style="gap:16px; max-width:420px;">
                <?= Project\Security::csrfField(); ?>
                <input class="input" name="login_username" placeholder="Логин" required>
                <input class="input" type="password" name="login_password" placeholder="Пароль" required>
                <button class="button" name="login" type="submit">Войти</button>
            </form>
        </div>
        <div class="tab-panel" data-tab-panel="register">
            <form method="post" class="grid" style="gap:16px; max-width:420px;">
                <?= Project\Security::csrfField(); ?>
                <input class="input" name="register_username" placeholder="Логин" required>
                <input class="input" type="password" name="register_password" placeholder="Пароль" required>
                <input class="input" type="password" name="register_password2" placeholder="Повторите пароль" required>
                <button class="button" name="register" type="submit">Создать аккаунт</button>
            </form>
        </div>
    <?php else: ?>
        <p>Вы вошли как <strong><?= Project\e($user['username']); ?></strong>.</p>
        <form method="post" class="card mt-md">
            <h2>Смена пароля</h2>
            <?= Project\Security::csrfField(); ?>
            <div class="form-group">
                <label for="current_password">Текущий пароль</label>
                <input class="input" type="password" name="current_password" id="current_password" required>
            </div>
            <div class="form-group">
                <label for="new_password">Новый пароль</label>
                <input class="input" type="password" name="new_password" id="new_password" required>
            </div>
            <div class="form-group">
                <label for="new_password2">Повторите пароль</label>
                <input class="input" type="password" name="new_password2" id="new_password2" required>
            </div>
            <button class="button" name="change_password" type="submit">Сохранить</button>
        </form>
    <?php endif; ?>
</section>
