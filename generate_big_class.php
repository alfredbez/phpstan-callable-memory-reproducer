<?php

declare(strict_types=1);

$dir = __DIR__ . '/generated';
if (!is_dir($dir)) {
    mkdir($dir, 0777, true);
}

$target = $dir . '/BigContainer.php';
$fp = fopen($target, 'w');
fwrite($fp, "<?php\n\ndeclare(strict_types=1);\n\nclass BigContainer\n{\n");

$numMethods = 25000;
for ($i = 0; $i < $numMethods; $i++) {
    fwrite($fp, "    public function getService_{$i}(): object\n    {\n        return new \\stdClass();\n    }\n\n");
}

fwrite($fp, "}\n");
fclose($fp);

$sizeMb = round(filesize($target) / 1024 / 1024, 2);
echo "Generated BigContainer.php with {$numMethods} methods ({$sizeMb} MB)\n";
