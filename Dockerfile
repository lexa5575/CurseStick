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

# Копируем .env.example в .env для базовой настройки
RUN cp .env.example .env

# Установка зависимостей Laravel
RUN composer install --no-dev --optimize-autoloader

# Генерация ключа и кэширование, выполняется в entrypoint
EXPOSE 8000

# Создаем entrypoint скрипт
RUN echo '#!/bin/sh\n\
php artisan key:generate --force\n\
php artisan config:cache\n\
php artisan route:cache\n\
php artisan view:cache\n\
php artisan serve --host=0.0.0.0 --port=8000' > /var/www/html/entrypoint.sh \
    && chmod +x /var/www/html/entrypoint.sh

# Старт через entrypoint
CMD ["/var/www/html/entrypoint.sh"]