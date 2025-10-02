<?php
/** @var array $profiles */
?>
<section class="home-hero card fade-in">
    <div>
        <h1>Катиндирнет</h1>
        <p>Каталог специалистов с резюме, инструментами и прозрачной админкой.</p>
        <div class="mt-md">
            <a class="button" href="/admin/">Перейти в админку</a>
            <a class="button-secondary" href="/id.php">Личный кабинет</a>
        </div>
    </div>
    <div>
        <p>Покажите команду миру: добавляйте профили, синхронизируйте CV и управляемые редиректы.</p>
    </div>
</section>

<section class="mt-lg">
    <h2>Избранные профили</h2>
    <div class="grid columns-2 mt-md">
        <?php foreach ($profiles as $profile): ?>
            <article class="card profile-card">
                <img src="<?= Project\e($profile['avatar_path'] ?: '/project/public/assets/img/logo.svg'); ?>" alt="<?= Project\e($profile['fio']); ?>">
                <div>
                    <h3><?= Project\e($profile['fio']); ?></h3>
                    <p class="text-muted"><?= Project\e($profile['position'] ?? ''); ?></p>
                    <div class="tags mt-sm">
                        <?php foreach (array_filter(array_map('trim', explode(',', (string)$profile['tags_csv']))) as $tag): ?>
                            <span class="tag-pill">#<?= Project\e($tag); ?></span>
                        <?php endforeach; ?>
                    </div>
                    <?php if (!empty($profile['cv']['summary_md'])): ?>
                        <p class="mt-sm"><?= nl2br(Project\e($profile['cv']['summary_md'])); ?></p>
                    <?php endif; ?>
                    <div class="mt-sm">
                        <?php if (!empty($profile['email'])): ?>
                            <a href="mailto:<?= Project\e($profile['email']); ?>" class="badge badge-primary">Почта</a>
                        <?php endif; ?>
                        <?php if (!empty($profile['telegram'])): ?>
                            <a href="https://t.me/<?= ltrim(Project\e($profile['telegram']), '@'); ?>" class="badge">Telegram</a>
                        <?php endif; ?>
                    </div>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</section>
