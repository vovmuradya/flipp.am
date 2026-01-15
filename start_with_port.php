#!/usr/bin/env php
<?php

// Получаем порт из переменной окружения
$port = getenv('PORT') ?: '8000';

// Устанавливаем настройки для работы с FrankenPHP
putenv('APP_RUNNING_IN_CONSOLE=false');

// Запускаем встроенный сервер PHP на нужном порту
$publicPath = __DIR__ . '/public';
echo "Starting server on port {$port}\n";
echo "Public path: {$publicPath}\n";

// Запускаем сервер
$command = "php -S 0.0.0.0:{$port} -t {$publicPath}";
echo "Executing: {$command}\n";

system($command);