#!/bin/bash

cd /home/vov/flipp-am

echo "Очистка кеша Laravel..."

# Очистка всех кешей
php artisan view:clear
php artisan cache:clear
php artisan config:clear
php artisan route:clear

# Очистка скомпилированных файлов Blade вручную
rm -rf storage/framework/views/*.php 2>/dev/null || true

# Очистка автозагрузки Composer
composer dump-autoload -o

echo "✅ Кеш полностью очищен!"
echo "Теперь попробуйте открыть http://localhost/listings/46"

