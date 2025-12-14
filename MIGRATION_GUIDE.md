# 🚀 Инструкция по миграции idrom.am на новый сервер

## Автоматическая миграция в 2 шага

### 📋 Подготовка

**1. На старом сервере:**
Отредактируйте `export-from-old-server.sh` и заполните:
```bash
NEW_SERVER_IP="123.456.789.10"      # IP нового сервера
NEW_SERVER_USER="root"              # SSH пользователь нового сервера
```

**2. На новом сервере:**
Отредактируйте `import-to-new-server.sh` и заполните:
```bash
DB_PASSWORD="your_secure_password"  # Пароль для MySQL
```

---

## 🎬 Запуск миграции

### Шаг 1: Экспорт со старого сервера

```bash
# На СТАРОМ сервере (текущий продакшн):
cd /home/vov/flipp-am
bash export-from-old-server.sh
```

**Что делает скрипт:**
- ✅ Экспортирует MySQL базу данных
- ✅ Архивирует медиа файлы (фото, загрузки)
- ✅ Копирует .env с секретами
- ✅ Автоматически отправляет всё на новый сервер через SSH

**Время выполнения:** ~5-10 минут (зависит от размера БД)

---

### Шаг 2: Импорт на новый сервер

```bash
# На НОВОМ сервере:
# Скопируйте скрипт на новый сервер
scp import-to-new-server.sh root@новый-сервер:/tmp/

# Подключитесь к новому серверу
ssh root@новый-сервер

# Запустите установку
cd /tmp
sudo bash import-to-new-server.sh
```

**Что делает скрипт:**
- ✅ Устанавливает все зависимости (Apache, MySQL, PHP 8.3, Node.js)
- ✅ Клонирует проект с GitHub
- ✅ Устанавливает Composer и NPM зависимости
- ✅ Создаёт и настраивает базу данных
- ✅ Импортирует данные со старого сервера
- ✅ Восстанавливает медиа файлы
- ✅ Настраивает Apache и SSL сертификат
- ✅ Создаёт Cron задачи для Laravel Scheduler

**Время выполнения:** ~15-30 минут

---

## 🧪 Тестирование перед переключением DNS

### Вариант 1: Через файл hosts (рекомендуется)

**На вашем компьютере (Windows):**
```
1. Откройте блокнот как Администратор
2. Откройте файл: C:\Windows\System32\drivers\etc\hosts
3. Добавьте строку:
   123.456.789.10  idrom.am www.idrom.am
4. Сохраните
5. Откройте браузер: https://idrom.am
```

**На Linux/Mac:**
```bash
sudo nano /etc/hosts
# Добавьте:
123.456.789.10  idrom.am www.idrom.am
```

### Вариант 2: Через поддомен

```
1. Создайте DNS запись:
   test.idrom.am  A  новый-сервер-IP

2. Добавьте в Apache конфиг (/etc/apache2/sites-available/idrom.am.conf):
   ServerAlias test.idrom.am

3. Перезапустите Apache:
   sudo systemctl restart apache2

4. Откройте: https://test.idrom.am
```

---

## ✅ Чеклист проверки

Перед переключением DNS проверьте:

- [ ] Главная страница открывается
- [ ] Авторизация работает
- [ ] Объявления отображаются
- [ ] Фото загружаются
- [ ] Можно создать новое объявление
- [ ] Парсер list.am работает
- [ ] Отправка email работает (если используется)
- [ ] SSL сертификат установлен
- [ ] Нет ошибок в логах

**Проверка логов:**
```bash
# Laravel
tail -f /var/www/idrom/storage/logs/laravel.log

# Apache
tail -f /var/log/apache2/idrom.am-error.log
```

---

## 🌐 Переключение DNS (финальный шаг)

Когда убедитесь что всё работает:

**1. Уменьшите TTL (за 24 часа до переключения):**
```
idrom.am        A    старый-IP  (TTL: 300)
www.idrom.am    A    старый-IP  (TTL: 300)
```

**2. Переключите на новый IP:**
```
idrom.am        A    новый-IP   (TTL: 300)
www.idrom.am    A    новый-IP   (TTL: 300)
```

**3. Проверьте распространение DNS:**
```bash
# На вашем компьютере:
nslookup idrom.am
# Должен показать новый IP
```

**4. Подождите 24-48 часов** перед отключением старого сервера

---

## 🔄 Rollback (если что-то пошло не так)

Если на новом сервере проблемы:

```
1. Верните DNS на старый IP:
   idrom.am  A  старый-IP

2. Старый сервер продолжит работать
```

---

## 📊 Мониторинг после миграции

**Первые 24 часа:**
```bash
# Следите за логами
tail -f /var/www/idrom/storage/logs/laravel.log

# Проверяйте нагрузку
htop

# Проверяйте MySQL
mysqladmin -u root -p processlist

# Проверяйте трафик Apache
tail -f /var/log/apache2/idrom.am-access.log
```

---

## 🗑️ Отключение старого сервера (через 48 часов)

Когда DNS полностью обновился:

```bash
# На СТАРОМ сервере:

# 1. Финальный бэкап
mysqldump -u root -p idrom_db > /backup/final_$(date +%Y%m%d).sql
tar -czf /backup/final_files_$(date +%Y%m%d).tar.gz /home/vov/flipp-am

# 2. Остановка сервисов
sudo systemctl stop apache2
sudo systemctl stop mysql

# 3. (Опционально) Удаление данных после проверки бэкапов
```

---

## 🆘 Поддержка

**Если возникли проблемы:**

1. Проверьте логи: `/var/www/idrom/storage/logs/laravel.log`
2. Проверьте Apache: `/var/log/apache2/idrom.am-error.log`
3. Проверьте .env файл: `nano /var/www/idrom/.env`
4. Проверьте права: `ls -la /var/www/idrom/storage`

**Частые проблемы:**

| Проблема | Решение |
|----------|---------|
| 500 Internal Server Error | `chmod -R 775 storage bootstrap/cache` |
| База не подключается | Проверьте .env: DB_HOST, DB_DATABASE, DB_USERNAME, DB_PASSWORD |
| Фото не отображаются | `php artisan storage:link` |
| Кэш проблемы | `php artisan cache:clear && php artisan config:clear` |

---

## 📞 Контакты

После успешной миграции не забудьте:
- ✅ Удалить запись из /etc/hosts (если добавляли)
- ✅ Обновить закладки на новый IP
- ✅ Сделать финальный бэкап старого сервера
