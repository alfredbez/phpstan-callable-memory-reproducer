<?php

declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

$containerFile = __DIR__ . '/generated/BigContainer.php';
if (!file_exists($containerFile)) {
    echo "BigContainer.php not found. Run: php generate_big_class.php\n";
    exit(1);
}

require_once $containerFile;
