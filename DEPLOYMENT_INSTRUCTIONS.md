# Инструкция по развертыванию проекта Idrom.am

## Подготовка сервера

1. Установите Docker и Docker Compose на сервере Kamatera
2. Установите Git
3. Клонируйте репозиторий проекта

## Установка Docker и Docker Compose

```bash
# Обновление системы
sudo apt update && sudo apt upgrade -y

# Установка Docker
sudo apt install ca-certificates curl gnupg lsb-release -y
sudo mkdir -p /etc/apt/keyrings
curl -fsSL https://download.docker.com/linux/ubuntu/gpg | sudo gpg --dearmor -o /etc/apt/keyrings/docker.gpg
sudo echo \
  "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.gpg] https://download.docker.com/linux/ubuntu \
  $(lsb_release -cs) stable" | sudo tee /etc/apt/sources.list.d/docker.list > /dev/null
sudo apt update
sudo apt install docker-ce docker-ce-cli containerd.io docker-compose-plugin

# Добавление пользователя в группу docker
sudo usermod -aG docker $USER
newgrp docker
```

## Клонирование и настройка проекта

```bash
# Клонирование репозитория
git clone https://github.com/ваш_аккаунт/idrom-am.git
cd idrom-am

# Копирование файла окружения
cp .env.example .env

# Установка зависимостей
docker-compose run --rm artisan composer install
npm install

# Создание ключа приложения
docker-compose run --rm artisan php artisan key:generate

# Создание базы данных
docker-compose exec mysql mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS laravel; CREATE USER IF NOT EXISTS 'sail'@'%' IDENTIFIED BY 'password'; GRANT ALL PRIVILEGES ON laravel.* TO 'sail'@'%'; FLUSH PRIVILEGES;"

# Выполнение миграций
docker-compose run --rm artisan php artisan migrate --force

# Запуск приложения
docker-compose up -d
```

## Административная панель

Административная панель доступна по адресу:
```
http://ваш_сервер/admin
```

## Доступы

- Email: admin@idrom.am
- Пароль: admin123

## Обновление проекта

Для обновления проекта выполните:

```bash
./update_production.sh
```