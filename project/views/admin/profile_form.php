<?php
/** @var array|null $profile */
/** @var array|null $cv */
?>
<div class="modal" id="profile-form" data-tabs>
    <h2><?= $profile ? 'Редактирование профиля #' . Project\e((string)$profile['id']) : 'Новый профиль'; ?></h2>
    <div id="profile-form-status"></div>
    <form method="post">
        <?= Project\Security::csrfField(); ?>
        <input type="hidden" name="id" value="<?= Project\e($profile['id'] ?? ''); ?>">
        <div class="tabs">
            <button type="button" class="active" data-tab="general">Основное</button>
            <button type="button" data-tab="cv">CV</button>
            <button type="button" data-tab="flags">Отображение</button>
        </div>
        <div class="tab-panel active" data-tab-panel="general">
            <div class="grid columns-2">
                <label class="form-group">
                    <span>ФИО</span>
                    <input class="input" name="fio" value="<?= Project\e($profile['fio'] ?? ''); ?>" required>
                </label>
                <label class="form-group">
                    <span>Должность</span>
                    <input class="input" name="position" value="<?= Project\e($profile['position'] ?? ''); ?>">
                </label>
            </div>
            <div class="grid columns-2">
                <label class="form-group">
                    <span>Email</span>
                    <input class="input" name="email" value="<?= Project\e($profile['email'] ?? ''); ?>">
                </label>
                <label class="form-group">
                    <span>Телеграм</span>
                    <input class="input" name="telegram" value="<?= Project\e($profile['telegram'] ?? ''); ?>">
                </label>
            </div>
            <label class="form-group">
                <span>Телефон</span>
                <input class="input" name="phone" value="<?= Project\e($profile['phone'] ?? ''); ?>">
            </label>
            <label class="form-group">
                <span>Локация</span>
                <input class="input" name="location" value="<?= Project\e($profile['location'] ?? ''); ?>">
            </label>
            <label class="form-group">
                <span>Теги (через запятую)</span>
                <input class="input" name="tags_csv" value="<?= Project\e($profile['tags_csv'] ?? ''); ?>">
            </label>
        </div>
        <div class="tab-panel" data-tab-panel="cv">
            <label class="form-group">
                <span>Краткое описание (Markdown)</span>
                <textarea class="input" name="summary_md" rows="4"><?= Project\e($cv['summary_md'] ?? ''); ?></textarea>
            </label>
            <label class="form-group">
                <span>Опыт (JSON)</span>
                <textarea class="input" name="experience_json" rows="4"><?= Project\e($cv['experience_json'] ?? '[]'); ?></textarea>
            </label>
            <label class="form-group">
                <span>Образование (JSON)</span>
                <textarea class="input" name="education_json" rows="4"><?= Project\e($cv['education_json'] ?? '[]'); ?></textarea>
            </label>
            <label class="form-group">
                <span>Навыки (через запятую)</span>
                <input class="input" name="skills_csv" value="<?= Project\e($cv['skills_csv'] ?? ''); ?>">
            </label>
            <label class="form-group">
                <span>Соцсети (JSON)</span>
                <textarea class="input" name="socials_json" rows="3"><?= Project\e($cv['socials_json'] ?? '[]'); ?></textarea>
            </label>
        </div>
        <div class="tab-panel" data-tab-panel="flags">
            <label class="form-group">
                <span>Показывать на главной</span>
                <select name="show_on_home">
                    <option value="0" <?= empty($profile['show_on_home']) ? 'selected' : ''; ?>>Нет</option>
                    <option value="1" <?= !empty($profile['show_on_home']) ? 'selected' : ''; ?>>Да</option>
                </select>
            </label>
            <label class="form-group">
                <span>Аватар (путь)</span>
                <input class="input" name="avatar_path" value="<?= Project\e($profile['avatar_path'] ?? ''); ?>">
            </label>
        </div>
        <div class="mt-md flex flex-between">
            <button type="submit" class="button">Сохранить</button>
            <?php if ($profile): ?>
                <button type="submit" class="button-danger" name="delete" value="1">Удалить</button>
            <?php endif; ?>
        </div>
    </form>
</div>
