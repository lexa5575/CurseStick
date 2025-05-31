# Основной образ PHP с необходимыми расширениями
FROM php:8.2-fpm as builder

# Устанавливаем необходимые зависимости и расширения
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libzip-dev \
    git \
    zip \
    unzip \
    libicu-dev \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    libonig-dev \
    && docker-php-ext-configure intl \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_pgsql zip intl exif mbstring gd

# Устанавливаем Composer
COPY --from=composer:2.6 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Копируем composer файлы и устанавливаем зависимости
COPY composer.json composer.lock ./
RUN composer install --no-autoloader --no-scripts --no-dev --ignore-platform-reqs

# Копируем весь проект
COPY . .
RUN composer dump-autoload --optimize --no-dev --ignore-platform-reqs

# Финальный образ
FROM php:8.2-fpm

# Устанавливаем необходимые расширения PHP
RUN apt-get update && apt-get install -y \
    libpq-dev \
    zip \
    unzip \
    libzip-dev \
    libicu-dev \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    libonig-dev \
    && docker-php-ext-configure intl \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_pgsql zip intl exif mbstring gd

WORKDIR /var/www/html

# Копируем все файлы из builder этапа
COPY --from=builder /var/www/html /var/www/html

# Создаем файл .env и добавляем права на директории
RUN touch .env && \
    chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Создаем скрипт запуска
COPY --from=composer:2.6 /usr/bin/composer /usr/bin/composer

RUN echo '#!/bin/sh\n\
# Создаем базовый .env файл из переменных окружения\n\
echo "APP_ENV=\${APP_ENV:-production}" > .env\n\
echo "APP_DEBUG=\${APP_DEBUG:-false}" >> .env\n\
echo "APP_URL=\${APP_URL:-http://localhost}" >> .env\n\
echo "APP_KEY=" >> .env\n\
echo "LOG_CHANNEL=\${LOG_CHANNEL:-stderr}" >> .env\n\
echo "DB_CONNECTION=\${DB_CONNECTION:-pgsql}" >> .env\n\
echo "DB_HOST=\${DB_HOST}" >> .env\n\
echo "DB_PORT=\${DB_PORT:-5432}" >> .env\n\
echo "DB_DATABASE=\${DB_DATABASE}" >> .env\n\
echo "DB_USERNAME=\${DB_USERNAME}" >> .env\n\
echo "DB_PASSWORD=\${DB_PASSWORD}" >> .env\n\
echo "CACHE_DRIVER=\${CACHE_DRIVER:-file}" >> .env\n\
echo "SESSION_DRIVER=\${SESSION_DRIVER:-database}" >> .env\n\
echo "QUEUE_CONNECTION=\${QUEUE_CONNECTION:-sync}" >> .env\n\
\n# Генерируем ключ приложения\n\
php artisan key:generate --force\n\
\n# Кэшируем конфигурацию\n\
php artisan config:clear\n\
php artisan cache:clear\n\
php artisan config:cache\n\
php artisan route:cache\n\
php artisan view:cache\n\
\n# Запускаем сервер\n\
php artisan serve --host=0.0.0.0 --port=8000\n' > /var/www/html/start.sh \
    && chmod +x /var/www/html/start.sh

EXPOSE 8000

CMD ["/var/www/html/start.sh"]