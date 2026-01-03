#!/bin/bash

# Скрипт для внесения улучшений на production-сервере

SERVER_DIR="/var/www/idrom"

echo "🔄 Обновление системы получения куки и изображений Copart..."

# 1. Создадим резервную копию важных файлов
echo "📦 Создание резервных копий..."
cp "$SERVER_DIR/app/Services/CopartCookieManager.php" "$SERVER_DIR/app/Services/CopartCookieManager.php.backup" 2>/dev/null || echo "⚠️ Резервная копия CopartCookieManager не создана"
cp "$SERVER_DIR/scraper/fetch-copart-lot.cjs" "$SERVER_DIR/scraper/fetch-copart-lot.cjs.backup" 2>/dev/null || echo "⚠️ Резервная копия fetch-copart-lot не создана"
cp "$SERVER_DIR/app/Services/CopartImageService.php" "$SERVER_DIR/app/Services/CopartImageService.php.backup" 2>/dev/null || echo "⚠️ Резервная копия CopartImageService не создана"

# 2. Обновим таймаут в CopartCookieManager до 120 секунд
echo "⏱️ Обновление таймаута в CopartCookieManager..."
sed -i 's/private const FETCH_TIMEOUT = 60;/private const FETCH_TIMEOUT = 120; \/\/ seconds - увеличенный таймаут для более надежной работы/g' "$SERVER_DIR/app/Services/CopartCookieManager.php"

# 3. Обновим логику выбора скрипта для использования быстрых куки
echo "⚡ Обновление логики выбора скрипта для быстрых куки..."
sed -i '/# Используем улучшенную логику выбора скрипта/d' "$SERVER_DIR/app/Services/CopartCookieManager.php"
sed -i '/if (! is_file(\$script)) {/i \
            // Используем улучшенную логику выбора скрипта\
            $useFastScript = config("services.copart.fast_cookies", false);\
            $script = $useFastScript \
                ? base_path("scraper/fetch-copart-cookies-auto.cjs")  // быстрый скрипт\
                : base_path("scraper/fetch-copart-cookies-firefox.cjs"); // оригинальный скрипт' "$SERVER_DIR/app/Services/CopartCookieManager.php"

# 4. Обновим путь к браузеру в CopartCookieManager
sed -i 's|/home/admin/\.cache/ms-playwright|/home/vov/\.cache/ms-playwright|g' "$SERVER_DIR/app/Services/CopartCookieManager.php"

# 5. Улучшим логику проверки куки
echo "🔍 Обновление проверки валидности куки..."
# Заменяем метод isCookieAlive на улучшенную версию
cat > /tmp/isCookieAlive_new.php << 'EOF'
    private function isCookieAlive(?string $cookieHeader): bool
    {
        if (! is_string($cookieHeader) || trim($cookieHeader) === '') {
            return false;
        }

        // Быстрая проверка наличия основных куки-идентификаторов перед HTTP-запросом
        if (!preg_match('/(nlbi|visid_incap|incap_ses|reese84|PHPSESSID|XSRF-TOKEN|laravel_session)/i', $cookieHeader)) {
            return false;
        }

        try {
            $response = Http::timeout(5) // Уменьшенный таймаут для более быстрой проверки
                ->withHeaders([
                    'Cookie' => $cookieHeader,
                    'User-Agent' => config('services.copart.user_agent'),
                    'Accept' => 'application/json, text/plain, */*',
                    'Accept-Encoding' => 'gzip', // Для быстрой передачи
                ])
                ->get(self::CHECK_URL);

            if (! $response->successful()) {
                return false;
            }

            // Быстрая проверка ответа без полной десериализации
            $body = $response->body();
            return strpos($body, '"data"') !== false || strpos($body, '"lotDetails"') !== false;
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::debug('CopartCookieManager: cookie check failed', ['error' => $e->getMessage()]);
            return false;
        }
    }
EOF

# Заменим метод в файле
sed -i '/private function isCookieAlive/,/^    }/{
    /private function isCookieAlive/{
        r /tmp/isCookieAlive_new.php
    }
    /    }/!d
}' "$SERVER_DIR/app/Services/CopartCookieManager.php"

# 6. Обновим логику в refreshCookies для лучшей обработки ошибок
echo "🐛 Обновление обработки ошибок в refreshCookies..."
# Используем более точное обновление
sed -i '/\$process = new Process(\[\'node\', \$script\], base_path(), \$env, null, self::FETCH_TIMEOUT);/a \
                try {\
                    $process->run();\
                } catch (\\Throwable $e) {\
                    \\Illuminate\\Support\\Facades\\Log::warning(\'CopartCookieManager: process execution failed\', [\
                        \'error\' => $e->getMessage(),\
                        \'attempt\' => $i\
                    ]);\
                    $lastError = $e->getMessage();\
                    continue;\
                }' "$SERVER_DIR/app/Services/CopartCookieManager.php"

# 7. Обновим CopartImageService для передачи куки в headless-скрипт
echo "📦 Обновление CopartImageService для передачи куки..."
sed -i '/\$command = sprintf(/,/exec(\$command, \$output, \$returnCode);/{
    /exec(\$command, \$output, \$returnCode);/i \
        // Получаем куки и передаем их в скрипт как третий параметр\
        $cookieHeader = $this->cookieManager->getCookieHeader();\
        $escapedCookies = $cookieHeader ? escapeshellarg($cookieHeader) : "\'\'";\
        \
        $command = sprintf(\
            \'node %s %s %s 2>&1\',\
            escapeshellarg($scriptPath),\
            escapeshellarg($lotId),\
            $escapedCookies\
        );
    /exec(\$command, \$output, \$returnCode);/b
}' "$SERVER_DIR/app/Services/CopartImageService.php"

# 8. Обновим контроллер для увеличенного таймаута
echo "⏱️ Обновление таймаута в контроллере..."
sed -i 's/set_time_limit(45);/set_time_limit(120);  \/\/ Увеличен лимит времени для улучшенного парсинга/g' "$SERVER_DIR/app/Http/Controllers/ListingController.php"

# 9. Обновим конфиг для использования быстрых куки по умолчанию
echo "⚙️ Обновление конфигурации..."
sed -i 's/\'fast_cookies\' => env(\'COPART_FAST_COOKIES\', false),/\'fast_cookies\' => env(\'COPART_FAST_COOKIES\', true), \/\/ использовать быстрый скрипт для получения куки/g' "$SERVER_DIR/config/services.php"

# 10. Обновим CSS для квадратных изображений
echo "🖼️ Обновление стилей изображений..."
sed -i 's/style="border-radius: 10px; object-fit: cover; cursor: pointer; border: 2px solid #e5e7eb;"/style="border-radius: 12px; object-fit: cover; cursor: pointer; border: 2px solid #e5e7eb; aspect-ratio: 1; box-shadow: 0 2px 4px rgba(0,0,0,0.1); transition: all 0.2s ease;"/g' "$SERVER_DIR/resources/views/listings/create.blade.php"

sed -i '/style="border-radius: 10px; object-fit: cover; cursor: pointer; border: 2px solid #e5e7eb;"/a \
                                             onmouseover="this.style.transform=\'scale(1.05)\'; this.style.borderColor=\'#93c5fd\';" \
                                             onmouseout="this.style.transform=\'scale(1)\'; this.style.borderColor=\'#e5e7eb\';"' "$SERVER_DIR/resources/views/listings/create.blade.php"

# 11. Установим браузеры для Playwright если нужно
echo "🌐 Установка браузеров Playwright..."
cd "$SERVER_DIR" && npx playwright install firefox chromium 2>/dev/null || echo "⚠️ Установка браузеров Playwright может потребовать ручного выполнения"

# 12. Обновим .env файл
echo "🔑 Обновление .env файла..."
if ! grep -q "COPART_FAST_COOKIES" "$SERVER_DIR/.env"; then
    echo "COPART_FAST_COOKIES=true" >> "$SERVER_DIR/.env"
fi

# 13. Обновим пути в других файлах если нужно
echo "🔧 Обновление путей к браузерам в других местах..."
find "$SERVER_DIR" -name "*.php" -exec sed -i 's|/home/admin/\.cache/ms-playwright|/home/vov/\.cache/ms-playwright|g' {} \;

echo "✅ Улучшения успешно внесены в систему!"
echo ""
echo "📋 Что было улучшено:"
echo "   - Увеличен таймаут до 120 секунд"
echo "   - Добавлена поддержка быстрых скриптов (Playwright)"
echo "   - Улучшена логика проверки куки"
echo "   - Улучшена обработка ошибок"
echo "   - Изображения теперь квадратные с эффектами"
echo "   - Улучшено получение изображений с передачей куки"
echo ""
echo "💡 Рекомендуется очистить кэш Laravel после обновления:"
echo "   cd $SERVER_DIR && php artisan cache:clear"
echo "   cd $SERVER_DIR && php artisan config:clear"