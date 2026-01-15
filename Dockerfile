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
    libicu-dev \
    libxml2-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libmagickwand-dev \
    netcat-openbsd \
    && docker-php-ext-install -j$(nproc) iconv

# Устанавливаем PHP-расширения по одному для лучшей отладки
RUN docker-php-ext-install exif
RUN docker-php-ext-install zip
RUN docker-php-ext-install pdo_mysql
RUN docker-php-ext-install bcmath
RUN docker-php-ext-install tokenizer
RUN docker-php-ext-install xml
RUN docker-php-ext-install json
RUN docker-php-ext-install mbstring

# Установка GD
RUN docker-php-ext-install gd

# Установка Intl
RUN docker-php-ext-install intl

# Установка SOAP
RUN docker-php-ext-install soap

# Установка MySQLi
RUN docker-php-ext-install mysqli

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