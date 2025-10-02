<?php if (!empty($_SESSION['flash'])): ?>
    <?php foreach ($_SESSION['flash'] as $flash): ?>
        <div class="alert <?= Project\e($flash['type'] === 'success' ? 'alert-success' : 'alert-error'); ?>" data-flash>
            <?= Project\e($flash['message']); ?>
        </div>
    <?php endforeach; ?>
    <?php unset($_SESSION['flash']); ?>
<?php endif; ?>
