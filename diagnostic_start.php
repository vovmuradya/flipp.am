#!/usr/bin/env php
<?php

// diagnostic_start.php - диагностический скрипт запуска

$port = getenv('PORT') ?: '8000';
echo "Starting diagnostic server on port {$port}\n";

// Запускаем встроенный сервер PHP с диагностическим скриптом
$publicPath = __DIR__ . '/public';
$diagnosticScript = __DIR__ . '/diagnostic.php';

// Создаем временный index.php для вывода диагностики
$tempIndex = $publicPath . '/index.php';
file_put_contents($tempIndex, "<?php include_once '../diagnostic.php';");

echo "Created temporary diagnostic index.php\n";

// Запускаем сервер
$command = "php -S 0.0.0.0:{$port} -t {$publicPath}";
echo "Executing: {$command}\n";

system($command);

// Удаляем временный файл после завершения
if (file_exists($tempIndex)) {
    unlink($tempIndex);
}