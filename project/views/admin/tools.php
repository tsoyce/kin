<section class="card">
    <h1>Инструменты</h1>
    <p>Быстрые ссылки и интеграции.</p>
    <div class="grid columns-2 mt-md">
        <div class="card">
            <h2>Shorty</h2>
            <p>Сервис коротких ссылок.</p>
            <a class="button" href="https://l.tsoy.in" target="_blank" rel="noopener">Открыть Shorty</a>
        </div>
        <div class="card">
            <h2>Аналитика</h2>
            <p>Сейчас: <?= !empty($settings['analytics_enabled']) ? 'включена' : 'отключена'; ?>. Управляется в настройках сайта.</p>
            <a class="button-secondary" href="/admin/settings.php">Открыть настройки</a>
        </div>
    </div>
</section>
