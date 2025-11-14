#!/bin/bash
cd /home/vov/flipp-am
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
echo "✅ Все кеши очищены!"

