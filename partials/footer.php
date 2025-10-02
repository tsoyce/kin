</main>
<?php
  // Retrieve copyright text from settings.  If not set, default to site name
  // followed by the current year.
  $copyright = get_setting('site_copyright', get_setting('site_name', 'Сервис коротких ссылок') . ' • ' . date('Y'));
  $copyrightUrl = get_setting('site_copyright_url', '');
?>
<footer class="py-8 text-center text-sm text-slate-500">
  <?php if ($copyrightUrl): ?>
    <a href="<?= h($copyrightUrl) ?>" class="underline" target="_blank" rel="noopener noreferrer">
      <?= h($copyright) ?>
    </a>
  <?php else: ?>
    <?= h($copyright) ?>
  <?php endif; ?>
</footer>
</div>
</body>
</html>
