FROM php:8.2-fpm

# Установим нужные зависимости
RUN apt-get update && apt-get install -y \
    git curl zip unzip libpq-dev libzip-dev libonig-dev libxml2-dev \
    && docker-php-ext-install pdo pdo_pgsql zip mbstring

# Установка Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Установка рабочей директории
WORKDIR /var/www/html

# Копируем файлы проекта внутрь контейнера
COPY . .

# Установка зависимостей Laravel + запуск команд
RUN composer install --no-dev --optimize-autoloader && \
    php artisan key:generate && \
    php artisan config:cache && \
    php artisan migrate --force

# Указываем порт
EXPOSE 8000

# Команда для запуска Laravel
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]