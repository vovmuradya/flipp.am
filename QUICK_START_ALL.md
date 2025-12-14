# 🚀 Flipp.am - Быстрый старт

## Запуск всего одной командой

```bash
cd /home/vov/flipp-am
./start_all.sh
```

Это запустит:
- ✅ Laravel API сервер (http://localhost:8000)
- ✅ Queue Worker для парсинга Copart
- ✅ Flutter мобильное приложение

## Остановка всего

```bash
cd /home/vov/flipp-am
./stop_all.sh
```

## Отдельные команды

### Laravel API
```bash
# Запуск
php artisan serve

# Остановка
pkill -f "php artisan serve"
```

### Queue Worker (для импорта из Copart)
```bash
# Запуск
./start_queue_worker.sh

# Проверка
ps aux | grep "queue:work"

# Логи
tail -f /tmp/queue_worker.log

# Остановка
pkill -f "queue:work"
```

### Flutter приложение
```bash
# Запуск
cd mobile-app
flutter run -d linux

# Логи
tail -f flutter_output.log
```

## Проверка статуса всех сервисов

```bash
ps aux | grep -E 'artisan|queue|flutter'
```

## Логи

- **Laravel сервер**: `/tmp/laravel_server.log`
- **Queue Worker**: `/tmp/queue_worker.log`
- **Flutter app**: `mobile-app/flutter_output.log`

## Важные заметки

⚠️ **Queue Worker обязателен для импорта из Copart!**  
Без него импорт будет показывать ошибку "превышено время ожидания".

✅ **Автоматический вход**  
После первого входа токен сохраняется. При следующем запуске `flutter run` вы сразу попадёте в приложение.

## Функционал импорта из Copart

При импорте автоматически заполняются:
- ✅ Марка, модель, год
- ✅ Пробег, цвет, двигатель
- ✅ Коробка передач, топливо
- ✅ Цена (Buy Now или текущая ставка)
- ✅ Описание с повреждениями
- ✅ 14+ фотографий

## Структура проекта

```
/home/vov/flipp-am/
├── start_all.sh              # Запустить всё
├── stop_all.sh               # Остановить всё
├── start_queue_worker.sh     # Запустить только queue worker
├── app/                      # Laravel backend
├── routes/api.php            # API роуты
├── mobile-app/               # Flutter приложение
│   ├── lib/
│   │   ├── main.dart
│   │   ├── screens/
│   │   │   ├── import_from_auction_screen.dart
│   │   │   └── create_listing_screen.dart
│   │   └── services/
│   │       └── api_service.dart
│   └── flutter_output.log
└── database/database.sqlite
```

## Быстрые тесты

### Тест импорта из Copart
1. Запустите всё: `./start_all.sh`
2. Откройте приложение (подождите ~30 сек пока скомпилируется)
3. Войдите (или авторизация автоматическая)
4. Нажмите большой оранжевый "+" внизу
5. Выберите "Объявление из аукциона"
6. Вставьте ссылку: `https://www.copart.com/lot/87001015/clean-title-2021-nissan-rogue-s-ma-north-boston`
7. Нажмите "Импортировать с аукциона"
8. Ждите 40-60 секунд
9. ✅ Форма заполнится всеми данными + 14 фото!

### Проверка логов парсинга
```bash
tail -f /tmp/queue_worker.log
```

Вы увидите:
```
Processing jobs from the [default] queue.
App\Jobs\ParseAuctionJob ........... RUNNING
App\Jobs\ParseAuctionJob ........... DONE
```

## Полезные команды

```bash
# Проверить миграции БД
php artisan migrate:status

# Запустить миграции
php artisan migrate

# Очистить кэш
php artisan cache:clear

# Посмотреть роуты API
php artisan route:list --path=api

# Hot reload Flutter
# (в терминале где запущен flutter нажмите 'r')
```

## Troubleshooting

### "could not find a generator for route"
- Проблема: не хватает именованных роутов
- Решение: уже исправлено, используется Navigator.push вместо pushNamed

### "превышено время ожидания парсинга"
- Проблема: queue worker не запущен
- Решение: `./start_queue_worker.sh`

### "Unauthenticated" в API
- Проблема: токен истёк или не сохранён
- Решение: выйдите и войдите снова в приложении

### Приложение не компилируется
```bash
cd mobile-app
flutter clean
flutter pub get
flutter run -d linux
```

## Поддержка

Все скрипты и код настроены для работы из коробки.  
Просто запустите `./start_all.sh` и всё заработает! 🚀
