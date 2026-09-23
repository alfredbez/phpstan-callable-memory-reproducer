<?php

declare(strict_types=1);

if (!file_exists(__DIR__ . '/generated/BigContainer.php')) {
    echo "Generating BigContainer.php...\n";
    require_once __DIR__ . '/generate_big_class.php';
}

$files = [
    'src/FooClean.php (1st item not a class name)' => 'src/FooClean.php',
    'src/FooLeaking.php (1st item matches BigContainer)' => 'src/FooLeaking.php',
];

echo "\nRunning PHPStan analysis comparison:\n";
echo str_repeat('-', 75) . "\n";
printf("%-48s | %-12s | %-8s\n", "Scenario", "Consumed RAM", "Duration");
echo str_repeat('-', 75) . "\n";

foreach ($files as $label => $file) {
    $out = [];
    $cmd = sprintf(
        'php -d memory_limit=2G ./vendor/bin/phpstan analyse %s --debug -vvv --no-progress 2>&1',
        escapeshellarg($file)
    );
    exec($cmd, $out, $ret);

    $consumed = 'N/A';
    $duration = 'N/A';
    foreach ($out as $line) {
        if (str_contains($line, 'consumed')) {
            // Format: "--- consumed 305.97 MB, total 343.98 MB, took 0.82 s, 7.321 LoC/s"
            if (preg_match('/consumed\s+([0-9.]+\s+[MG]B).*took\s+([0-9.]+\s+s)/', $line, $matches)) {
                $consumed = $matches[1];
                $duration = $matches[2];
            } else {
                $consumed = trim($line);
            }
            break;
        }
    }

    printf("%-48s | %-12s | %-8s\n", $label, $consumed, $duration);
}

echo str_repeat('-', 75) . "\n";
echo "Notice how FooLeaking causes PHPStan to reflect BigContainer into memory,\n";
echo "causing significant RAM consumption and slowdown for a simple 2-element array constant.\n\n";
