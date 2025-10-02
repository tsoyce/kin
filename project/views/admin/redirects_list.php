<?php
/** @var array $redirects */
?>
<section class="card">
    <h1>Редиректы</h1>
    <form method="post" action="/admin/redirect_save.php" class="grid" style="gap:16px;">
        <?= Project\Security::csrfField(); ?>
        <div class="grid columns-2">
            <label class="form-group">
                <span>From</span>
                <input class="input" name="from_path" placeholder="/old" required>
            </label>
            <label class="form-group">
                <span>To URL</span>
                <input class="input" name="to_url" placeholder="https://example.com/new" required>
            </label>
        </div>
        <label class="form-group">
            <span>Код</span>
            <select name="code">
                <option value="301">301</option>
                <option value="302" selected>302</option>
            </select>
        </label>
        <button class="button" type="submit">Добавить</button>
    </form>

    <div class="table-responsive mt-md">
        <table>
            <thead>
                <tr>
                    <th>From</th>
                    <th>To</th>
                    <th>Код</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($redirects as $redirect): ?>
                    <tr>
                        <td><?= Project\e($redirect['from_path']); ?></td>
                        <td><?= Project\e($redirect['to_url']); ?></td>
                        <td><?= Project\e((string)$redirect['code']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
