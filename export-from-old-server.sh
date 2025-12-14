#!/bin/bash

##############################################
# IDROM.AM - Экспорт со старого сервера
# Запускать на СТАРОМ сервере
##############################################

set -e  # Остановить при ошибке

echo "🔄 Начинаем экспорт данных idrom.am..."

# Цвета для вывода
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Параметры (НАСТРОИТЬ ПОД ВАШ ПРОЕКТ!)
PROJECT_PATH="/home/vov/flipp-am"  # Путь к проекту на старом сервере
DB_NAME="idrom_db"                  # Имя базы данных
DB_USER="root"                      # MySQL пользователь
BACKUP_DIR="/tmp/idrom_migration_$(date +%Y%m%d_%H%M%S)"
NEW_SERVER_IP=""                    # IP нового сервера (заполнить!)
NEW_SERVER_USER=""                  # SSH юзер нового сервера (заполнить!)

# Проверка параметров
if [ -z "$NEW_SERVER_IP" ] || [ -z "$NEW_SERVER_USER" ]; then
    echo -e "${RED}❌ Ошибка: Настройте NEW_SERVER_IP и NEW_SERVER_USER в скрипте!${NC}"
    exit 1
fi

echo -e "${YELLOW}📁 Создаём директорию для бэкапа: $BACKUP_DIR${NC}"
mkdir -p "$BACKUP_DIR"

# 1. Экспорт базы данных
echo -e "${YELLOW}💾 Экспортируем базу данных...${NC}"
mysqldump -u "$DB_USER" -p "$DB_NAME" > "$BACKUP_DIR/database.sql"
if [ $? -eq 0 ]; then
    echo -e "${GREEN}✅ База данных экспортирована${NC}"
else
    echo -e "${RED}❌ Ошибка экспорта БД${NC}"
    exit 1
fi

# 2. Архивация медиа файлов
echo -e "${YELLOW}📦 Архивируем медиа файлы...${NC}"
cd "$PROJECT_PATH"
if [ -d "storage/app/public" ]; then
    tar -czf "$BACKUP_DIR/media.tar.gz" storage/app/public/
    echo -e "${GREEN}✅ Медиа файлы заархивированы${NC}"
else
    echo -e "${YELLOW}⚠️  storage/app/public не найден, пропускаем${NC}"
fi

# 3. Копируем .env файл
echo -e "${YELLOW}🔐 Копируем .env файл...${NC}"
if [ -f "$PROJECT_PATH/.env" ]; then
    cp "$PROJECT_PATH/.env" "$BACKUP_DIR/env_backup"
    echo -e "${GREEN}✅ .env скопирован${NC}"
else
    echo -e "${RED}❌ .env не найден!${NC}"
    exit 1
fi

# 4. Сохраняем версию composer.lock
echo -e "${YELLOW}📋 Копируем composer.lock...${NC}"
if [ -f "$PROJECT_PATH/composer.lock" ]; then
    cp "$PROJECT_PATH/composer.lock" "$BACKUP_DIR/composer.lock"
fi

# 5. Создаём манифест
echo -e "${YELLOW}📝 Создаём манифест миграции...${NC}"
cat > "$BACKUP_DIR/MANIFEST.txt" << EOF
IDROM.AM Migration Backup
Дата: $(date '+%Y-%m-%d %H:%M:%S')
Сервер: $(hostname)
Проект: $PROJECT_PATH
База: $DB_NAME

Содержимое:
- database.sql (MySQL dump)
- media.tar.gz (storage/app/public/)
- env_backup (копия .env)
- composer.lock

Размер бэкапа: $(du -sh "$BACKUP_DIR" | cut -f1)
EOF

cat "$BACKUP_DIR/MANIFEST.txt"

# 6. Упаковываем всё в один архив
echo -e "${YELLOW}📦 Создаём финальный архив...${NC}"
FINAL_ARCHIVE="/tmp/idrom_migration_$(date +%Y%m%d_%H%M%S).tar.gz"
tar -czf "$FINAL_ARCHIVE" -C "$(dirname $BACKUP_DIR)" "$(basename $BACKUP_DIR)"
echo -e "${GREEN}✅ Архив создан: $FINAL_ARCHIVE${NC}"

# 7. Копируем на новый сервер
echo -e "${YELLOW}🚀 Копируем на новый сервер $NEW_SERVER_IP...${NC}"
echo -e "${YELLOW}   (Введите пароль SSH если требуется)${NC}"

scp "$FINAL_ARCHIVE" "$NEW_SERVER_USER@$NEW_SERVER_IP:/tmp/"

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✅ Файлы успешно скопированы на новый сервер!${NC}"
    echo ""
    echo -e "${GREEN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
    echo -e "${GREEN}✅ ЭКСПОРТ ЗАВЕРШЁН${NC}"
    echo -e "${GREEN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
    echo ""
    echo "📋 Следующие шаги:"
    echo "1. Подключитесь к новому серверу: ssh $NEW_SERVER_USER@$NEW_SERVER_IP"
    echo "2. Запустите скрипт импорта: bash /tmp/import-to-new-server.sh"
    echo ""
    echo "📦 Архив на новом сервере: /tmp/$(basename $FINAL_ARCHIVE)"
    echo "🗂️  Локальный бэкап сохранён: $FINAL_ARCHIVE"
else
    echo -e "${RED}❌ Ошибка копирования на новый сервер${NC}"
    echo "Архив сохранён локально: $FINAL_ARCHIVE"
    echo "Скопируйте вручную: scp $FINAL_ARCHIVE $NEW_SERVER_USER@$NEW_SERVER_IP:/tmp/"
    exit 1
fi
