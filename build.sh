#!/bin/bash
# Build script для Laravel на Render.com

# Установка зависимостей
composer install --no-interaction --prefer-dist --optimize-autoloader

# Если у вас есть фронтенд (Vue, React и т.д.)
if [ -f "package.json" ]; then
    npm ci
    npm run build
fi

# Миграции базы данных (если нужно)
php artisan migrate --force

# Кэширование конфигурации
php artisan config:cache
php artisan route:cache
php artisan view:cache