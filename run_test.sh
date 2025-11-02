#!/bin/bash

echo "🧹 Очистка кешей..."
php artisan view:clear > /dev/null 2>&1
php artisan config:clear > /dev/null 2>&1
php artisan cache:clear > /dev/null 2>&1

echo "✅ Кеш очищен"
echo ""
echo "🚀 Запуск теста парсера..."
echo ""

php test_parser_now.php

