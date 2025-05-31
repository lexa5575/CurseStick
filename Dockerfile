# Используем официальный PHP-образ с нужными расширениями
FROM php:8.2-fpm

# Установка системных зависимостей и PHP-расширений
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    curl \
    libpq-dev \
    libzip-dev \
    zip \
    && docker-php-ext-install pdo pdo_pgsql zip

# Установка Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Установка рабочей директории
WORKDIR /var/www/html

# Копируем все файлы проекта
COPY . .

# Установка зависимостей Laravel
RUN composer install --no-dev --optimize-autoloader && \
    php artisan key:generate && \
    php artisan config:cache && \
    php artisan route:cache && \
    php artisan view:cache

# Открываем порт (опционально, Render сам пробросит)
EXPOSE 8000

# Запуск Laravel сервера
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]