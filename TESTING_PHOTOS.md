# 🔧 Инструкция по тестированию парсера

## ✅ Что было исправлено:

1. **Исправлена регулярка** для поиска изображений (строка 159)
2. **Множественные методы** извлечения фото:
   - Прямые URL в HTML
   - Агрессивный поиск cs.copart.com
   - Поиск в JS переменных
   - Относительные пути
   - data-атрибуты
   - API запрос
3. **Удаление дубликатов** по нормализованному пути
4. **Проксирование** всех изображений через /proxy/image

## 🧪 Как протестировать:

### Вариант 1: Быстрый тест (в WSL)
```bash
cd /home/vov/flipp-am
bash test_parser_simple.sh
```

### Вариант 2: Ручной тест
```bash
cd /home/vov/flipp-am
php artisan view:clear
php artisan config:clear

php artisan tinker --execute='
$s = app(\App\Services\AuctionParserService::class);
$url = "https://www.copart.com/ru/lot/80812965/clean-title-2015-chevrolet-trax-ls-nb-moncton";
$r = $s->parseFromUrl($url);
echo "Фото: " . count($r["photos"]) . "\n";
foreach (array_slice($r["photos"], 0, 3) as $i => $p) {
    echo ($i+1) . ". " . substr($p, 0, 80) . "...\n";
}
'
```

## 📊 Ожидаемый результат:

- **Марка**: Chevrolet
- **Модель**: Trax Ls
- **Год**: 2015
- **Пробег**: ~120000 км (генерируется)
- **Фото**: 10-14 штук (реальные URL с cs.copart.com)

## 🌐 Тест через браузер:

1. Откройте: http://localhost:8000/listings/create-from-auction
2. Вставьте: https://www.copart.com/ru/lot/80812965/clean-title-2015-chevrolet-trax-ls-nb-moncton
3. Нажмите "Далее"
4. Должны появиться миниатюры фотографий (70x70px) и главное фото (200x130px)

## 📝 Проверка логов:

```bash
tail -50 storage/logs/laravel.log | grep "photos\|Final data"
```

Должны увидеть строки типа:
- ✅ Method A: 14 direct URLs
- ✅ Final photos count: 14

## 🔍 Если фото всё равно не отображаются:

1. **Проверьте прокси**:
   ```bash
   curl -v "http://localhost:8000/proxy/image?u=https%3A%2F%2Fcs.copart.com%2Fv1%2FAUTH_svc.pdoc00001%2Fids-c-prod-lpp%2F0925%2F67c8d340f055482d9b83aec788ee11e1_ful.jpg"
   ```
   Должен вернуть 200 и изображение.

2. **Проверьте логи прокси**:
   ```bash
   tail -20 storage/logs/laravel.log | grep proxy
   ```

3. **Очистите всё**:
   ```bash
   php artisan optimize:clear
   php artisan view:clear
   ```

## 📞 Получить помощь:

Если проблема остаётся, отправьте вывод:
```bash
bash test_parser_simple.sh > test_output.txt 2>&1
cat test_output.txt
```

