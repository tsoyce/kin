<?php
use Project\Models\Settings;
use function Project\seo_meta;
$settings = Settings::get();
$meta = $meta ?? seo_meta(['title' => 'Админка — ' . ($settings['site_title'] ?? 'Катиндирнет')]);
include __DIR__ . '/../partials/head.php';
?>
<header class="site-header">
    <div class="container">
        <nav class="navbar">
            <a href="/admin/" class="brand">Админка</a>
            <a href="/admin/profiles_list.php">Профили</a>
            <a href="/admin/users_list.php">Пользователи</a>
            <a href="/admin/settings.php">Настройки</a>
            <a href="/admin/redirects.php">Редиректы</a>
            <a href="/admin/tools.php">Инструменты</a>
            <span class="spacer"></span>
            <a href="/admin/logout.php">Выход</a>
        </nav>
    </div>
</header>
<main>
    <div class="main-container">
        <?php include __DIR__ . '/../partials/flash.php'; ?>
        <?= $content; ?>
    </div>
</main>
<?php include __DIR__ . '/../partials/footer.php'; ?>
