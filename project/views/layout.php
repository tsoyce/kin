<?php
use Project\Models\Settings;
use function Project\seo_meta;
$settings = Settings::get();
$meta = $meta ?? seo_meta($meta ?? []);
include __DIR__ . '/partials/head.php';
include __DIR__ . '/partials/header.php';
?>
<main>
    <div class="main-container">
        <?php include __DIR__ . '/partials/flash.php'; ?>
        <?= $content; ?>
    </div>
</main>
<?php include __DIR__ . '/partials/footer.php'; ?>
