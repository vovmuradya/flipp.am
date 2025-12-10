# Деплой асинхронного парсинга

## Что изменилось

Парсинг аукционов теперь работает **асинхронно** через Laravel Queue, чтобы избежать таймаутов Cloudflare (524 error).

### Изменения:
1. **Job**: `app/Jobs/ParseAuctionJob.php` - выполняет парсинг в фоне
2. **Model**: `app/Models/AuctionParseJob.php` - отслеживает статус задач
3. **Migration**: `database/migrations/*_create_auction_parse_jobs_table.php` - таблица для задач
4. **Controller**: `app/Http/Controllers/Api/AuctionParserController.php` - обновлён для async
5. **Routes**: `routes/api.php` - добавлен endpoint `/check-parse-status`
6. **Frontend**: `resources/views/listings/_partials/create-from-auction.blade.php` - polling статуса

## Инструкция по деплою

### На сервере выполнить:

```bash
cd /home/admin/web/idrom.am/public_html

# 1. Получить изменения
git pull

# 2. Запустить миграцию (создаст таблицу auction_parse_jobs)
php artisan migrate --force

# 3. Очистить кеш
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# 4. Запустить queue worker (если еще не запущен)
# ВАЖНО: Worker должен работать постоянно!
php artisan queue:work --daemon --tries=1 --timeout=300 &

# Или добавить в systemd/supervisor (рекомендуется)
```

### Настройка supervisor (рекомендуется)

Создать файл `/etc/supervisor/conf.d/idrom-queue.conf`:

```ini
[program:idrom-queue]
process_name=%(program_name)s_%(process_num)02d
command=php /home/admin/web/idrom.am/public_html/artisan queue:work --sleep=3 --tries=1 --timeout=300
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=admin
numprocs=2
redirect_stderr=true
stdout_logfile=/home/admin/web/idrom.am/public_html/storage/logs/queue-worker.log
stopwaitsecs=3600
```

Затем:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start idrom-queue:*
```

## Как это работает

1. Пользователь вводит URL → API создаёт задачу и возвращает `job_id`
2. Frontend каждые 2 секунды проверяет статус через `/check-parse-status`
3. Job выполняется в фоне (до 5 минут)
4. Когда готово - пользователь переходит на форму с данными

## Проверка работы

```bash
# Проверить что worker работает
ps aux | grep "queue:work"

# Посмотреть логи queue
tail -f storage/logs/laravel.log | grep -i "async parse"

# Проверить таблицу задач
mysql -u root -p idrom -e "SELECT * FROM auction_parse_jobs ORDER BY id DESC LIMIT 5;"
```

## Откат (если что-то пошло не так)

```bash
git revert HEAD
php artisan migrate:rollback --step=1
php artisan cache:clear
```

Старый синхронный код можно вернуть через git history.
