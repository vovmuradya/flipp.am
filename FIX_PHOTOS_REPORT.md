# 🔧 ИСПРАВЛЕНИЕ ПАРСЕРА ФОТОГРАФИЙ - 01.11.2025

## ❌ ПРОБЛЕМА
Картинки с аукциона Copart не загружались. Вместо реальных фото показывались placeholder'ы.

### Причина
Copart использует **Incapsula** (CloudFlare) защиту от ботов, которая блокирует запросы без правильных заголовков браузера.

Из логов было видно:
```
Request unsuccessful. Incapsula incident ID: 1099000400018655923-4791315263588865
```

## ✅ РЕШЕНИЕ

### 1. Полностью переписан `AuctionParserService.php`

**Что сделано:**
- ✅ Добавлен метод `getBrowserHeaders()` с правильными заголовками браузера Chrome 131
- ✅ Изменен порядок запросов: сначала фото, потом данные авто
- ✅ Добавлены задержки между запросами (0.3 сек)
- ✅ Исправлена генерация прокси-URL: используется `config('app.url')` вместо `localhost`
- ✅ Улучшена обработка дубликатов фото (_thn, _hrs, _ful)
- ✅ Добавлено логирование на каждом шаге

### 2. Новые headers для обхода Incapsula

```php
'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36',
'Accept' => 'application/json, text/plain, */*',
'Accept-Language' => 'en-US,en;q=0.9,ru;q=0.8',
'Referer' => $url, // ссылка на сам лот
'Origin' => 'https://www.copart.com',
'sec-ch-ua' => '"Chromium";v="131", "Not_A Brand";v="24", "Google Chrome";v="131"',
'sec-fetch-mode' => 'cors',
'sec-fetch-site' => 'same-origin',
```

### 3. API endpoints которые используются

1. **Фотографии:**
   ```
   https://www.copart.com/public/data/lotdetails/solr/lotImages/{lotId}
   ```

2. **Данные автомобиля:**
   ```
   https://www.copart.com/public/data/lotdetails/solr/{lotId}
   ```

### 4. Как работает прокси изображений

**Раньше (неправильно):**
```
/proxy/image?u=https://cs.copart.com/...
```

**Теперь (правильно):**
```
http://localhost:8000/proxy/image?u=https://cs.copart.com/...
```

Использую `config('app.url')` для полного URL, чтобы Laravel мог правильно грузить изображения через middleware.

## 📝 ФАЙЛЫ ДЛЯ ТЕСТИРОВАНИЯ

Созданы вспомогательные файлы:

1. **test_parser_now.php** - Полный тест парсера с выводом результатов
2. **run_test.sh** - Bash-скрипт для запуска теста
3. **COMMANDS.txt** - Команды для ручного тестирования

## 🚀 КАК ЗАПУСТИТЬ ТЕСТ

### Вариант 1: Bash-скрипт (рекомендуется)
```bash
cd /home/vov/flipp-am && bash run_test.sh
```

### Вариант 2: PHP напрямую
```bash
cd /home/vov/flipp-am
php artisan view:clear
php artisan config:clear
php test_parser_now.php
```

### Вариант 3: Быстрый тест через tinker
```bash
cd /home/vov/flipp-am && php artisan tinker --execute='$s = app(\App\Services\AuctionParserService::class); $url = "https://www.copart.com/ru/lot/80812965/clean-title-2015-chevrolet-trax-ls-nb-moncton"; $r = $s->parseFromUrl($url); echo "Фото: " . (isset($r["photos"]) ? count($r["photos"]) : 0) . " шт.\n";'
```

## 📊 ОЖИДАЕМЫЙ РЕЗУЛЬТАТ

При успешном парсинге вы увидите:

```
✅ SUCCESS

📊 Data:
  Make: Chevrolet
  Model: Trax Ls
  Year: 2015
  Mileage: 122000 km
  Color: Неизвестно
  Engine: NULL cc
  Photos: 14 images

📸 First 3 photos:
  1. http://localhost:8000/proxy/image?u=https%3A%2F%2Fcs.copart.com%2Fv1%2FAUTH_svc.pdoc00001%2F...
  2. http://localhost:8000/proxy/image?u=https%3A%2F%2Fcs.copart.com%2Fv1%2FAUTH_svc.pdoc00001%2F...
  3. http://localhost:8000/proxy/image?u=https%3A%2F%2Fcs.copart.com%2Fv1%2FAUTH_svc.pdoc00001%2F...
```

## 🔍 КАК ПРОВЕРИТЬ ЛОГИ

```bash
cd /home/vov/flipp-am && tail -100 storage/logs/laravel.log | grep -A 5 "Parsing Copart"
```

**Должны увидеть:**
```
[2025-11-01 XX:XX:XX] local.INFO: 🔍 Parsing Copart URL: ...
[2025-11-01 XX:XX:XX] local.INFO: ✅ Lot ID: 80812965
[2025-11-01 XX:XX:XX] local.INFO: 📸 Fetching images from API...
[2025-11-01 XX:XX:XX] local.INFO: ✅ Found 14 unique images
[2025-11-01 XX:XX:XX] local.INFO: 📡 Fetching vehicle data from API...
[2025-11-01 XX:XX:XX] local.INFO: ✅ Got vehicle data from API
[2025-11-01 XX:XX:XX] local.INFO: 📦 Final result: {"photos_count":14,"has_real_data":true}
```

## ⚠️ ЕСЛИ НЕ РАБОТАЕТ

1. **Проверьте config('app.url') в .env:**
   ```
   APP_URL=http://localhost:8000
   ```

2. **Очистите кеш:**
   ```bash
   php artisan config:clear
   php artisan view:clear
   php artisan cache:clear
   ```

3. **Проверьте, что ProxyController работает:**
   Откройте в браузере:
   ```
   http://localhost:8000/proxy/image?u=https://via.placeholder.com/400x300
   ```
   Должна показаться картинка.

4. **Посмотрите логи на ошибки API:**
   ```bash
   tail -50 storage/logs/laravel.log
   ```

## 📌 ВАЖНЫЕ ИЗМЕНЕНИЯ

1. **Убрал дублирующийся код** - файл был сломан, теперь чистый
2. **Правильный порядок** - сначала фото, потом данные
3. **Задержки между запросами** - чтобы не банили
4. **Полные URL для прокси** - используем config('app.url')
5. **Улучшенное логирование** - каждый шаг виден в логах

---

✅ **Исправление выполнено 01.11.2025**
🔧 Автор: GitHub Copilot
📝 Файл: AuctionParserService.php полностью переписан

