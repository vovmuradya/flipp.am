#!/bin/bash

# Скрипт для подготовки репозитория Idrom.am к публикации

echo "Подготовка репозитория Idrom.am к публикации..."

# Инициализация git репозитория, если он не существует
if [ ! -d ".git" ]; then
    git init
fi

# Добавление удаленного репозитория (замените YOUR_USERNAME на ваше имя пользователя GitHub)
read -p "Введите ваше имя пользователя GitHub: " github_username
read -p "Введите название репозитория (по умолчанию idrom-am): " repo_name

repo_name=${repo_name:-idrom-am}

git remote add origin https://github.com/$github_username/$repo_name.git

# Убедимся, что в .gitignore нет важных файлов, которые должны быть в репозитории
echo "Проверка .gitignore..."
cat << 'EOF' > .gitignore
# Исключаем файлы конфигурации и чувствительные данные
.env
.env.local
.env.*.local
!.env.example

# Исключаем директории кэша и временных файлов
/storage/logs/*
!/storage/logs/.gitkeep
/bootstrap/cache/*
!/bootstrap/cache/.gitkeep
/storage/framework/cache/*
/storage/framework/sessions/*
/storage/framework/views/*
/public/storage
/storage/app/public/*

# Исключаем зависимости и сборки
/node_modules
/vendor

# Исключаем системные файлы
.DS_Store
Thumbs.db
*.log

# Исключаем файлы IDE
.idea/
.vscode/
*.swp
*.swo

# Исключаем архивы и временные файлы
*.zip
*.tar
*.gz
*.rar
*.7z

# Исключаем файлы браузера
npm-debug.log*
yarn-debug.log*
yarn-error.log*

# Исключаем файлы Docker
.docker/
!docker/
!docker/**/*

# Исключаем чувствительные файлы
config/sentry.php
database/seeds/users_seeder.php

# Исключаем файлы тестирования
tests/fixtures/*

# Исключаем файлы CI/CD
.github/workflows/
EOF

echo "Файл .gitignore обновлен."

# Добавляем все файлы
git add .

# Делаем первый коммит
git commit -m "Инициализация проекта Idrom.am с полной административной панелью"

# Создаем ветку main
git branch -M main

echo "Репозиторий подготовлен к публикации."
echo "Для публикации выполните:"
echo "  git push -u origin main"