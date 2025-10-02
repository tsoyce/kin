<footer class="site-footer">
    <div class="container">
        <div class="flex flex-between gap-md">
            <div>
                <strong>Катиндирнет</strong>
                <p class="text-muted">Каталог талантов, резюме и инструментов.</p>
            </div>
            <div class="analytics-placeholder">
                Аналитика: <?= !empty($settings['analytics_enabled']) ? 'включена' : 'отключена'; ?>.
            </div>
        </div>
    </div>
</footer>
</body>
</html>
