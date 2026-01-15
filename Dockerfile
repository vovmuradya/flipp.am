FROM dunglas/frankenphp:php8.3-bookworm

# Устанавливаем системные зависимости
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev \
    netcat-openbsd

# Устанавливаем PHP-расширения
RUN install-php-extensions \
    exif \
    zip \
    pdo_mysql \
    redis \
    bcmath \
    gd \
    intl \
    soap \
    imagick

# Устанавливаем Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Копируем файлы проекта
COPY . .

# Устанавливаем зависимости PHP
RUN composer install --ignore-platform-reqs --no-dev

# Копируем Caddyfile
COPY Caddyfile /etc/caddy/Caddyfile

EXPOSE 80

CMD ["/usr/local/bin/frankenphp", "server", "--config", "/etc/caddy/Caddyfile"]