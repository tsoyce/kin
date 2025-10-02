<?php
use function Project\asset;
use function Project\seo_meta;
$meta = $meta ?? seo_meta($meta ?? []);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= Project\e($meta['title'] ?? 'Катиндирнет'); ?></title>
    <meta name="description" content="<?= Project\e($meta['description'] ?? ''); ?>">
    <link rel="canonical" href="<?= Project\e($meta['canonical'] ?? ''); ?>">
    <meta name="robots" content="index, follow">
    <meta property="og:type" content="website">
    <meta property="og:title" content="<?= Project\e($meta['title'] ?? 'Катиндирнет'); ?>">
    <meta property="og:description" content="<?= Project\e($meta['description'] ?? ''); ?>">
    <meta property="og:url" content="<?= Project\e($meta['canonical'] ?? ''); ?>">
    <meta property="og:image" content="/project/public/assets/img/logo.svg">
    <meta name="twitter:card" content="summary_large_image">
    <link rel="stylesheet" href="<?= asset('project/public/assets/css/style.css'); ?>">
    <script defer src="<?= asset('project/public/assets/js/app.js'); ?>"></script>
</head>
<body>
