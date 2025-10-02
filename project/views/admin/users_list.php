<?php
/** @var array $users */
?>
<section class="card">
    <h1>Пользователи</h1>
    <form method="post" action="/admin/user_save.php" class="grid" style="gap:16px;">
        <?= Project\Security::csrfField(); ?>
        <div class="grid columns-2">
            <input class="input" name="username" placeholder="Логин" required>
            <select name="role">
                <option value="reader">reader</option>
                <option value="editor">editor</option>
                <option value="admin">admin</option>
            </select>
        </div>
        <input class="input" type="password" name="password" placeholder="Пароль">
        <button class="button" type="submit">Создать пользователя</button>
    </form>
    <div class="table-responsive mt-md">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Логин</th>
                    <th>Роль</th>
                    <th>Создан</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td>#<?= Project\e((string)$user['id']); ?></td>
                        <td><?= Project\e($user['username']); ?></td>
                        <td><span class="badge badge-role-<?= Project\e($user['role']); ?>"><?= Project\e($user['role']); ?></span></td>
                        <td><?= Project\e($user['created_at']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
