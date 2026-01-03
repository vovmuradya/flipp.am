#!/bin/bash

# Упрощенный скрипт для быстрого исправления основной проблемы с таймаутом

SERVER_DIR="/var/www/idrom"
TIMESTAMP=$(date +%s)

echo "🔧 Быстрое исправление проблемы с таймаутом Copart (время: $TIMESTAMP)"
echo "📦 Создание резервной копии..."

# Создание резервной копии файла CopartCookieManager.php
cp "$SERVER_DIR/app/Services/CopartCookieManager.php" "$SERVER_DIR/app/Services/CopartCookieManager.php.backup.$TIMESTAMP"

echo "⏱️ Увеличение таймаута до 120 секунд..."

# Заменяем старый таймаут на новый
sed -i 's/private const FETCH_TIMEOUT = 60;/private const FETCH_TIMEOUT = 120; \/\/ seconds - увеличенный таймаут для более надежной работы/g' "$SERVER_DIR/app/Services/CopartCookieManager.php"

# Также обновим путь к браузерам, если нужно
sed -i 's|/home/admin/\.cache/ms-playwright|/home/vov/\.cache/ms-playwright|g' "$SERVER_DIR/app/Services/CopartCookieManager.php"

echo "✅ Основная проблема с таймаутом устранена!"
echo "📋 Была создана резервная копия: CopartCookieManager.php.backup.$TIMESTAMP"

# Проверим, что изменение внесено
if grep -q "FETCH_TIMEOUT = 120" "$SERVER_DIR/app/Services/CopartCookieManager.php"; then
    echo "✅ Проверка: таймаут успешно увеличен до 120 секунд"
else
    echo "❌ Ошибка: не удалось изменить таймаут"
fi

# Добавим переменную в .env если она не существует
if ! grep -q "COPART_FAST_COOKIES" "$SERVER_DIR/.env"; then
    echo "⚙️ Добавление COPART_FAST_COOKIES=true в .env файл..."
    echo "COPART_FAST_COOKIES=true" >> "$SERVER_DIR/.env"
fi

echo ""
echo "💡 Рекомендуется очистить кэш Laravel:"
echo "   cd $SERVER_DIR && php artisan cache:clear"
echo "   cd $SERVER_DIR && php artisan config:clear"
echo ""
echo "🚀 Основное улучшение применено!"