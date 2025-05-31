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

# Создаем базовый .env файл
RUN touch .env

# Установка зависимостей Laravel
RUN composer install --no-dev --optimize-autoloader

# Генерация ключа и кэширование, выполняется в entrypoint
EXPOSE 8000

# Создаем entrypoint скрипт
RUN echo '#!/bin/sh\n\
# Устанавливаем переменные окружения из Render\n\
echo "\$APP_ENV" > .env\n\
echo "APP_KEY=" >> .env\n\
echo "DB_CONNECTION=\$DB_CONNECTION" >> .env\n\
echo "DB_HOST=\$DB_HOST" >> .env\n\
echo "DB_PORT=\$DB_PORT" >> .env\n\
echo "DB_DATABASE=\$DB_DATABASE" >> .env\n\
echo "DB_USERNAME=\$DB_USERNAME" >> .env\n\
echo "DB_PASSWORD=\$DB_PASSWORD" >> .env\n\
echo "APP_DEBUG=\$APP_DEBUG" >> .env\n\
echo "APP_URL=\$APP_URL" >> .env\n\
# Выполняем команды Laravel\n\
php artisan key:generate --force || true\n\
php artisan config:cache || true\n\
php artisan route:cache || true\n\
php artisan view:cache || true\n\
php artisan serve --host=0.0.0.0 --port=8000\n\
' > /var/www/html/entrypoint.sh \
    && chmod +x /var/www/html/entrypoint.sh

# Старт через entrypoint
CMD ["/var/www/html/entrypoint.sh"]