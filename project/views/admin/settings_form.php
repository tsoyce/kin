<?php
/** @var array $settings */
?>
<section class="card">
    <h1>Настройки</h1>
    <form method="post" action="/admin/settings_save.php">
        <?= Project\Security::csrfField(); ?>
        <div class="form-group">
            <label for="site_title">Заголовок</label>
            <input class="input" name="site_title" id="site_title" value="<?= Project\e($settings['site_title'] ?? ''); ?>">
        </div>
        <div class="form-group">
            <label for="site_subtitle">Подзаголовок</label>
            <input class="input" name="site_subtitle" id="site_subtitle" value="<?= Project\e($settings['site_subtitle'] ?? ''); ?>">
        </div>
        <div class="form-group">
            <label for="theme">Тема</label>
            <input class="input" name="theme" id="theme" value="<?= Project\e($settings['theme'] ?? 'default'); ?>">
        </div>
        <div class="form-group">
            <label for="contacts_json">Контакты (JSON)</label>
            <textarea class="input" name="contacts_json" id="contacts_json" rows="4"><?= Project\e($settings['contacts_json'] ?? '{}'); ?></textarea>
        </div>
        <div class="form-group">
            <label>
                <input type="checkbox" name="analytics_enabled" value="1" <?= !empty($settings['analytics_enabled']) ? 'checked' : ''; ?>> Включить аналитику
            </label>
        </div>
        <button class="button" type="submit">Сохранить</button>
    </form>
</section>
