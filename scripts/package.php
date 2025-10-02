<?php

declare(strict_types=1);

// Заготовка упаковщика проекта. Не запускать автоматически.
// Пример использования:
// php scripts/package.php output-directory

if ($argc < 2) {
    fwrite(STDERR, "Укажите директорию назначения\n");
    exit(1);
}

die('Packaging disabled in this environment');
