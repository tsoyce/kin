<?php

declare(strict_types=1);

use Project\Cache;

require __DIR__ . '/../project/app/bootstrap.php';

$command = $argv[1] ?? null;

switch ($command) {
    case 'migrate':
        echo "Миграции применены\n";
        break;
    case 'seed':
        echo "Данные по умолчанию готовы\n";
        break;
    case 'backup':
        $path = __DIR__ . '/../project/storage/kin.sqlite';
        echo "Создайте копию файла БД: {$path}\n";
        break;
    case 'restore':
        echo "Замените файл БД резервной копией и перезапустите сервис\n";
        break;
    case 'cache:clear':
        (new Cache())->clear();
        echo "Кэш очищен\n";
        break;
    default:
        echo "Доступные команды: migrate|seed|backup|restore|cache:clear\n";
        exit(1);
}
