FROM php:8.3-cli

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
    libssl-dev \
    libmcrypt-dev \
    libicu-dev \
    libxml2-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libmagickwand-dev \
    netcat-openbsd

# Устанавливаем PHP-расширения
RUN docker-php-ext-install \
    exif \
    zip \
    pdo_mysql \
    bcmath \
    gd \
    intl \
    soap \
    mysqli \
    tokenizer \
    xml \
    json \
    mbstring

# Установка Imagick
RUN apt-get install -y libmagickwand-dev && \
    pecl install imagick && \
    docker-php-ext-enable imagick

# Устанавливаем Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Копируем файлы проекта
COPY . .

# Устанавливаем зависимости PHP
RUN composer install --ignore-platform-reqs --no-dev

EXPOSE 80

CMD ["php", "start_with_port.php"]