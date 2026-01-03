# Инструкция по применению улучшений на production-сервере

## Важно перед началом:
1. Убедитесь, что у вас есть резервная копия сервера
2. В идеале, сначала протестируйте изменения на тестовом окружении
3. Убедитесь, что сервер входит в период низкой активности

## Шаг 1: Скопируйте скрипт на сервер

Вы можете скопировать содержимое скрипта update_production.sh на сервер любым удобным способом:

```bash
# На сервере выполните:
nano /tmp/update_production.sh
# Вставьте содержимое скрипта и сохраните (Ctrl+X, затем Y)

# Сделайте скрипт исполняемым
chmod +x /tmp/update_production.sh

# Выполните скрипт
sudo bash /tmp/update_production.sh
```

## Шаг 2: Альтернативный способ - выполнение вручную

Если вы предпочитаете вносить изменения вручную, вот пошаговые инструкции:

### 2.1 Обновление таймаута в CopartCookieManager.php:
```bash
# Перейдите в директорию проекта
cd /var/www/idrom

# Создайте резервную копию
cp app/Services/CopartCookieManager.php app/Services/CopartCookieManager.php.backup

# Обновите константу таймаута
sed -i 's/private const FETCH_TIMEOUT = 60;/private const FETCH_TIMEOUT = 120; \/\/ seconds - увеличенный таймаут для более надежной работы/g' app/Services/CopartCookieManager.php
```

### 2.2 Обновление логики выбора скрипта:
```bash
# В файле app/Services/CopartCookieManager.php замените старый код выбора скрипта на:
# Используем улучшенную логику выбора скрипта
$useFastScript = config('services.copart.fast_cookies', false);
$script = $useFastScript
    ? base_path('scraper/fetch-copart-cookies-auto.cjs')  // быстрый скрипт
    : base_path('scraper/fetch-copart-cookies-firefox.cjs'); // оригинальный скрипт
```

### 2.3 Улучшение логики проверки куки:
Замените метод `isCookieAlive` в файле `app/Services/CopartCookieManager.php` на улучшенную версию:

```php
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
```

### 2.4 Обновление CopartImageService для передачи куки:
В файле `app/Services/CopartImageService.php` замените команду вызова скрипта в методе `fetchViaHeadless`:

```php
// Получаем куки и передаем их в скрипт как третий параметр
$cookieHeader = $this->cookieManager->getCookieHeader();
$escapedCookies = $cookieHeader ? escapeshellarg($cookieHeader) : "''";

$command = sprintf(
    'node %s %s %s 2>&1',
    escapeshellarg($scriptPath),
    escapeshellarg($lotId),
    $escapedCookies
);
```

### 2.5 Обновление изображений в интерфейсе:
В файле `resources/views/listings/create.blade.php` обновите стили изображений:

Замените:
`style="border-radius: 10px; object-fit: cover; cursor: pointer; border: 2px solid #e5e7eb;"`

На:
`style="border-radius: 12px; object-fit: cover; cursor: pointer; border: 2px solid #e5e7eb; aspect-ratio: 1; box-shadow: 0 2px 4px rgba(0,0,0,0.1); transition: all 0.2s ease;"`

И добавьте:
`onmouseover="this.style.transform='scale(1.05)'; this.style.borderColor='#93c5fd';"`
`onmouseout="this.style.transform='scale(1)'; this.style.borderColor='#e5e7eb';"`

### 2.6 Обновление конфигурации:
В файле `config/services.php` измените значение по умолчанию:
`'fast_cookies' => env('COPART_FAST_COOKIES', true), // использовать быстрый скрипт для получения куки`

## Шаг 3: Обновление настроек в .env файле:
```bash
# Добавьте в .env файл:
COPART_FAST_COOKIES=true
```

## Шаг 4: Очистка кэша:
```bash
cd /var/www/idrom
php artisan cache:clear
php artisan config:clear
```

## Шаг 5: Установка браузеров Playwright (если нужно):
```bash
cd /var/www/idrom
npx playwright install firefox chromium
```

## Результат:
После выполнения всех изменений:
1. Таймауты при получении куки будут увеличены до 120 секунд
2. Система будет использовать более быстрые скрипты для получения куки
3. Изображения в интерфейсе будут квадратными с эффектами при наведении
4. Улучшена стабильность получения изображений с сайта Copart
5. Лучше обработка ошибок и защита от ботов

## Откат изменений:
Если потребуется откат, восстановите файлы из резервных копий:
- `app/Services/CopartCookieManager.php.backup`
- `scraper/fetch-copart-lot.cjs.backup`
- `app/Services/CopartImageService.php.backup`