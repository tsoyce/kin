<?php
/** @var array $profiles */
/** @var array $pagination */
/** @var array $filters */
?>
<section class="card">
    <div class="flex flex-between">
        <h1>Профили</h1>
        <button class="button" data-profile-id="new">Добавить</button>
    </div>
    <form method="get" class="toolbar mt-md">
        <input class="input search" type="search" name="query" placeholder="Поиск (ФИО, email, tags)" value="<?= Project\e($filters['query'] ?? ''); ?>">
        <div class="filters">
            <label>
                <span class="badge">На главной</span>
                <select name="show_on_home">
                    <option value="">Все</option>
                    <option value="1" <?= isset($filters['show_on_home']) && $filters['show_on_home'] === '1' ? 'selected' : ''; ?>>Да</option>
                    <option value="0" <?= isset($filters['show_on_home']) && $filters['show_on_home'] === '0' ? 'selected' : ''; ?>>Нет</option>
                </select>
            </label>
        </div>
        <button class="button-secondary" type="submit">Фильтровать</button>
    </form>

    <div class="table-responsive mt-md">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>ФИО</th>
                    <th>Контакты</th>
                    <th>Теги</th>
                    <th>Флаги</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($profiles as $profile): ?>
                    <tr>
                        <td>#<?= Project\e((string)$profile['id']); ?></td>
                        <td>
                            <strong><?= Project\e($profile['fio']); ?></strong><br>
                            <span class="text-muted"><?= Project\e($profile['position'] ?? ''); ?></span>
                        </td>
                        <td>
                            <?php if (!empty($profile['email'])): ?>
                                <div><?= Project\e($profile['email']); ?></div>
                            <?php endif; ?>
                            <?php if (!empty($profile['telegram'])): ?>
                                <div><?= Project\e($profile['telegram']); ?></div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php foreach (array_filter(array_map('trim', explode(',', (string)$profile['tags_csv']))) as $tag): ?>
                                <span class="badge">#<?= Project\e($tag); ?></span>
                            <?php endforeach; ?>
                        </td>
                        <td>
                            <?php if (!empty($profile['show_on_home'])): ?>
                                <span class="badge badge-flag">Главная</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="table-actions">
                                <button type="button" data-profile-id="<?= Project\e((string)$profile['id']); ?>">Редактировать</button>
                                <a href="/view_cv.php?id=<?= Project\e((string)$profile['id']); ?>" target="_blank">Открыть CV</a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php if ($pagination['pages'] > 1): ?>
        <div class="pagination">
            <?php for ($i = 1; $i <= $pagination['pages']; $i++): ?>
                <a href="#" data-page="<?= $i; ?>" class="<?= $i === $pagination['current'] ? 'active' : ''; ?>"><?= $i; ?></a>
            <?php endfor; ?>
        </div>
    <?php endif; ?>
</section>

<div id="profile-modal-backdrop" class="modal-backdrop" role="dialog" aria-modal="true">
    <div id="profile-modal-content"></div>
    <button type="button" class="button-secondary" data-modal-close style="position:absolute;top:24px;right:24px;">×</button>
</div>
