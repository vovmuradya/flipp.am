#!/bin/bash

# Скрипт обновления проекта Idrom.am на сервере Kamatera
# Запускать с правами пользователя vov

set -e

echo "Начинаем обновление проекта Idrom.am..."

# Переходим в директорию проекта
cd /home/vov/flipp-am

# Останавливаем контейнеры
echo "Остановка контейнеров..."
docker-compose down

# Обновляем код из репозитория
echo "Обновление кода из репозитория..."
git pull origin main

# Устанавливаем зависимости PHP
echo "Установка зависимостей PHP..."
docker-compose run --rm artisan composer install --no-dev

# Обновляем зависимости Node.js
echo "Обновление зависимостей Node.js..."
npm install

# Обновляем сборку фронтенда
echo "Сборка фронтенда..."
npm run build

# Выполняем миграции базы данных
echo "Выполнение миграций..."
docker-compose run --rm artisan php artisan migrate --force

# Очищаем кэш
echo "Очистка кэша..."
docker-compose run --rm artisan php artisan cache:clear
docker-compose run --rm artisan php artisan config:clear
docker-compose run --rm artisan php artisan route:clear
docker-compose run --rm artisan php artisan view:clear

# Пересобираем Docker-образы
echo "Пересборка Docker-образов..."
docker-compose build --no-cache

# Запускаем контейнеры
echo "Запуск контейнеров..."
docker-compose up -d

# Выполняем seeders если необходимо
echo "Выполнение seeders (если необходимо)..."
# docker-compose run --rm artisan php artisan db:seed

echo "Обновление завершено успешно!"