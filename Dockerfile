# Используем образ с PHP и Composer уже предустановленными
FROM composer:2.6 as build

WORKDIR /app

# Копируем только composer.json и composer.lock сначала
COPY composer.json composer.lock ./

# Устанавливаем зависимости
RUN composer install --no-autoloader --no-scripts --no-dev

# Копируем весь проект
COPY . .

# Добавляем autoloader
RUN composer dump-autoload --optimize --no-dev

# Второй этап: создание финального образа
FROM php:8.2-fpm

# Устанавливаем необходимые расширения PHP
RUN apt-get update && apt-get install -y \
    libpq-dev \
    zip \
    unzip \
    libzip-dev \
    && docker-php-ext-install pdo pdo_pgsql zip

WORKDIR /var/www/html

# Копируем все файлы из build этапа
COPY --from=build /app /var/www/html

# Создаем файл .env и добавляем права на директории
RUN touch .env && \
    chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Создаем скрипт запуска
COPY --from=build /usr/bin/composer /usr/bin/composer

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