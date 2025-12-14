#!/bin/bash

##############################################
# IDROM.AM - Импорт на новый сервер
# Запускать на НОВОМ сервере
##############################################

set -e  # Остановить при ошибке

echo "🚀 Начинаем установку idrom.am на новый сервер..."

# Цвета для вывода
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

# Параметры (НАСТРОИТЬ!)
PROJECT_PATH="/var/www/idrom"           # Куда установить проект
DB_NAME="idrom_db"                      # Имя новой базы данных
DB_USER="idrom_user"                    # MySQL пользователь
DB_PASSWORD=""                          # MySQL пароль (заполнить!)
DOMAIN="idrom.am"                       # Домен
GIT_REPO="https://github.com/vovmuradya/flipp.am.git"

# Проверка ROOT прав
if [ "$EUID" -ne 0 ]; then 
    echo -e "${RED}❌ Запустите с правами root: sudo bash $0${NC}"
    exit 1
fi

# Проверка параметров
if [ -z "$DB_PASSWORD" ]; then
    echo -e "${RED}❌ Ошибка: Настройте DB_PASSWORD в скрипте!${NC}"
    exit 1
fi

# Поиск архива
ARCHIVE=$(ls -t /tmp/idrom_migration_*.tar.gz 2>/dev/null | head -1)
if [ -z "$ARCHIVE" ]; then
    echo -e "${RED}❌ Архив миграции не найден в /tmp/${NC}"
    echo "Скопируйте архив со старого сервера в /tmp/"
    exit 1
fi

echo -e "${GREEN}✅ Найден архив: $ARCHIVE${NC}"

# Распаковка архива
echo -e "${YELLOW}📦 Распаковываем архив...${NC}"
TEMP_DIR="/tmp/idrom_import_$$"
mkdir -p "$TEMP_DIR"
tar -xzf "$ARCHIVE" -C "$TEMP_DIR" --strip-components=1

# Проверка содержимого
if [ ! -f "$TEMP_DIR/database.sql" ]; then
    echo -e "${RED}❌ database.sql не найден в архиве${NC}"
    exit 1
fi

echo -e "${GREEN}✅ Архив распакован${NC}"

# 1. Установка зависимостей
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${BLUE}📦 Установка зависимостей системы${NC}"
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"

apt-get update
apt-get install -y \
    apache2 \
    mysql-server \
    php8.3 \
    php8.3-cli \
    php8.3-fpm \
    php8.3-mysql \
    php8.3-mbstring \
    php8.3-xml \
    php8.3-curl \
    php8.3-zip \
    php8.3-gd \
    php8.3-redis \
    git \
    curl \
    unzip \
    certbot \
    python3-certbot-apache \
    nodejs \
    npm

# Composer
if ! command -v composer &> /dev/null; then
    echo -e "${YELLOW}📥 Установка Composer...${NC}"
    curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
fi

echo -e "${GREEN}✅ Зависимости установлены${NC}"

# 2. Клонирование проекта
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${BLUE}📥 Клонирование проекта${NC}"
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"

if [ -d "$PROJECT_PATH" ]; then
    echo -e "${YELLOW}⚠️  Директория $PROJECT_PATH уже существует${NC}"
    read -p "Удалить и создать заново? (y/n): " -n 1 -r
    echo
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        rm -rf "$PROJECT_PATH"
    else
        echo -e "${RED}❌ Отменено${NC}"
        exit 1
    fi
fi

git clone "$GIT_REPO" "$PROJECT_PATH"
cd "$PROJECT_PATH"

echo -e "${GREEN}✅ Проект клонирован${NC}"

# 3. Установка зависимостей проекта
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${BLUE}📦 Установка зависимостей проекта${NC}"
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"

# Composer
composer install --no-dev --optimize-autoloader --no-interaction

# NPM (основной проект)
npm install
npm run build

# NPM (scraper)
cd scraper
npm install
npx playwright install --with-deps chromium
cd ..

echo -e "${GREEN}✅ Зависимости проекта установлены${NC}"

# 4. Создание базы данных
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${BLUE}💾 Настройка базы данных${NC}"
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"

# Создание БД и пользователя
mysql -u root << EOF
DROP DATABASE IF EXISTS $DB_NAME;
CREATE DATABASE $DB_NAME CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
DROP USER IF EXISTS '$DB_USER'@'localhost';
CREATE USER '$DB_USER'@'localhost' IDENTIFIED BY '$DB_PASSWORD';
GRANT ALL PRIVILEGES ON $DB_NAME.* TO '$DB_USER'@'localhost';
FLUSH PRIVILEGES;
EOF

echo -e "${GREEN}✅ База данных создана${NC}"

# Импорт данных
echo -e "${YELLOW}📥 Импорт данных из старого сервера...${NC}"
mysql -u root "$DB_NAME" < "$TEMP_DIR/database.sql"
echo -e "${GREEN}✅ Данные импортированы${NC}"

# 5. Настройка .env
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${BLUE}⚙️  Настройка .env${NC}"
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"

# Копируем старый .env
if [ -f "$TEMP_DIR/env_backup" ]; then
    cp "$TEMP_DIR/env_backup" .env
    echo -e "${GREEN}✅ .env восстановлен из бэкапа${NC}"
    
    # Обновляем параметры БД
    sed -i "s|^DB_HOST=.*|DB_HOST=127.0.0.1|" .env
    sed -i "s|^DB_DATABASE=.*|DB_DATABASE=$DB_NAME|" .env
    sed -i "s|^DB_USERNAME=.*|DB_USERNAME=$DB_USER|" .env
    sed -i "s|^DB_PASSWORD=.*|DB_PASSWORD=$DB_PASSWORD|" .env
    sed -i "s|^APP_ENV=.*|APP_ENV=production|" .env
    sed -i "s|^APP_DEBUG=.*|APP_DEBUG=false|" .env
    sed -i "s|^APP_URL=.*|APP_URL=https://$DOMAIN|" .env
else
    cp .env.example .env
    php artisan key:generate --force
    echo -e "${YELLOW}⚠️  .env создан заново, настройте вручную!${NC}"
fi

# 6. Восстановление медиа файлов
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${BLUE}🖼️  Восстановление медиа файлов${NC}"
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"

if [ -f "$TEMP_DIR/media.tar.gz" ]; then
    tar -xzf "$TEMP_DIR/media.tar.gz"
    echo -e "${GREEN}✅ Медиа файлы восстановлены${NC}"
else
    echo -e "${YELLOW}⚠️  media.tar.gz не найден${NC}"
fi

# Создать symlink для storage
php artisan storage:link

# 7. Права доступа
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${BLUE}🔐 Настройка прав доступа${NC}"
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"

chown -R www-data:www-data "$PROJECT_PATH"
chmod -R 775 storage bootstrap/cache

echo -e "${GREEN}✅ Права настроены${NC}"

# 8. Laravel оптимизация
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${BLUE}⚡ Оптимизация Laravel${NC}"
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"

php artisan config:cache
php artisan route:cache
php artisan view:cache

echo -e "${GREEN}✅ Кэш создан${NC}"

# 9. Настройка Apache
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${BLUE}🌐 Настройка Apache${NC}"
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"

cat > /etc/apache2/sites-available/$DOMAIN.conf << EOF
<VirtualHost *:80>
    ServerName $DOMAIN
    ServerAlias www.$DOMAIN
    
    DocumentRoot $PROJECT_PATH/public
    
    <Directory $PROJECT_PATH/public>
        AllowOverride All
        Require all granted
        Options -Indexes +FollowSymLinks
    </Directory>
    
    ErrorLog \${APACHE_LOG_DIR}/$DOMAIN-error.log
    CustomLog \${APACHE_LOG_DIR}/$DOMAIN-access.log combined
</VirtualHost>
EOF

a2ensite $DOMAIN.conf
a2enmod rewrite
a2dissite 000-default.conf
systemctl restart apache2

echo -e "${GREEN}✅ Apache настроен${NC}"

# 10. SSL сертификат
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${BLUE}🔒 Установка SSL сертификата${NC}"
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"

read -p "Установить SSL сертификат сейчас? (y/n): " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    certbot --apache -d $DOMAIN -d www.$DOMAIN --non-interactive --agree-tos --email admin@$DOMAIN
    echo -e "${GREEN}✅ SSL сертификат установлен${NC}"
else
    echo -e "${YELLOW}⚠️  SSL пропущен, установите позже: certbot --apache -d $DOMAIN -d www.$DOMAIN${NC}"
fi

# 11. Cron для Laravel Scheduler
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${BLUE}⏰ Настройка Cron${NC}"
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"

(crontab -l 2>/dev/null | grep -v "schedule:run"; echo "* * * * * cd $PROJECT_PATH && php artisan schedule:run >> /dev/null 2>&1") | crontab -

echo -e "${GREEN}✅ Cron настроен${NC}"

# 12. Очистка
echo -e "${YELLOW}🧹 Очистка временных файлов...${NC}"
rm -rf "$TEMP_DIR"

# Финальная информация
echo ""
echo -e "${GREEN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${GREEN}🎉 УСТАНОВКА ЗАВЕРШЕНА!${NC}"
echo -e "${GREEN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo ""
echo -e "${BLUE}📋 Информация о проекте:${NC}"
echo "   Проект:     $PROJECT_PATH"
echo "   Домен:      https://$DOMAIN"
echo "   База:       $DB_NAME"
echo "   Пользователь БД: $DB_USER"
echo ""
echo -e "${BLUE}📝 Следующие шаги:${NC}"
echo "1. Проверьте .env файл: nano $PROJECT_PATH/.env"
echo "2. Добавьте A-запись DNS: $DOMAIN → IP этого сервера"
echo "3. Протестируйте сайт (можно через /etc/hosts)"
echo "4. После проверки переключите DNS на продакшн"
echo ""
echo -e "${BLUE}🔍 Проверка логов:${NC}"
echo "   Laravel: tail -f $PROJECT_PATH/storage/logs/laravel.log"
echo "   Apache:  tail -f /var/log/apache2/$DOMAIN-error.log"
echo ""
echo -e "${YELLOW}⚠️  Не забудьте настроить firewall (ufw) если нужно!${NC}"
echo ""
