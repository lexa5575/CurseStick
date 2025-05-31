# Используем официальный образ PHP с FPM
FROM php:8.2-fpm

# Устанавливаем системные зависимости и PHP-расширения
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    curl \
    libpq-dev \
    libzip-dev \
    zip \
    libicu-dev \
    && docker-php-ext-install pdo pdo_pgsql zip intl bcmath exif

# Установка Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Установка рабочей директории
WORKDIR /var/www/html

# Копируем проект в контейнер
COPY . .

# Установка зависимостей Laravel
RUN composer install --no-dev --optimize-autoloader && \
    php artisan key:generate && \
    php artisan config:cache && \
    php artisan route:cache && \
    php artisan view:cache

EXPOSE 8000

# Старт Laravel-сервера
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]