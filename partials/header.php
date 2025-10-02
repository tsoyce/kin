<?php $u = user(); ?>
<!doctype html>
<html lang="ru">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<?php
  // Fetch site-wide settings.  Provide sensible defaults if not configured.
  $siteName  = get_setting('site_name', 'Shorty');
  $siteFavicon = get_setting('site_favicon', '');
  // Build page title by concatenating the current page title (if provided)
  // with the site name.  If no page title is set, just use the site name.
  $pageTitle = isset($title) && $title !== '' ? ($title . ' • ' . $siteName) : $siteName;
?>
<title><?= h($pageTitle) ?></title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="/assets/app.css">
<link rel="icon" href="<?= h($siteFavicon !== '' ? $siteFavicon : 'data:,') ?>">
</head>
<body class="min-h-screen">
<!-- Root wrapper is no longer styled with Tailwind bg classes; global
     background is set via CSS.  We keep a wrapper div for structural
     purposes but remove the old bg-slate-50 class. -->
<div class="app-root min-h-screen">
<!-- Sticky header uses a frosted glass effect instead of a white bar. -->
<header class="sticky top-0 z-10 glass border-b border-white/10 backdrop-blur">
  <div class="max-w-5xl mx-auto px-4 py-3 flex items-center justify-between">
    <!-- Application name; colored white for contrast on dark background -->
    <a href="/" class="font-bold text-lg text-white"><?= h($siteName) ?></a>
    <nav class="flex items-center gap-3">
      <?php if ($u): ?>
        <!-- Show current user email and role with reduced opacity -->
        <span class="text-sm opacity-80"><?= h($u['email']) ?> (<?= h($u['role']) ?>)</span>
        <?php if ($u['role']==='admin'): ?>
          <a class="btn-ghost" href="/admin.php">Админка</a>
        <?php endif; ?>
        <!-- Link to personal settings page and logout -->
        <a class="btn-ghost" href="/id.php">Личный кабинет</a>
        <a class="btn-ghost" href="/logout.php">Выйти</a>
      <?php else: ?>
        <!-- Single entry point for authentication: id.php handles both login and registration -->
        <a class="btn-ghost" href="/id.php">Авторизоваться</a>
      <?php endif; ?>
    </nav>
  </div>
</header>
<main class="max-w-5xl mx-auto px-4 py-8">
