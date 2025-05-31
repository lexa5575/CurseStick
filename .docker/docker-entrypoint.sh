#!/bin/sh
set -e

# Запускаем оптимизацию Laravel, если это продакшн
# (Render обычно устанавливает APP_ENV=production)
if [ "$APP_ENV" = "production" ]; then
    echo "Running production optimizations..."
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    # php artisan event:cache # Раскомментируй, если используешь события и хочешь их кешировать
fi

# Убедимся, что папка для php-fpm сокета существует и у нее правильные права
# (хотя обычно это делается при установке php-fpm)
if [ ! -d /var/run/php ]; then
    mkdir -p /var/run/php
    chown www-data:www-data /var/run/php
    chmod 755 /var/run/php
fi

# Выполняем команду, переданную в CMD Dockerfile (в нашем случае это supervisord)
exec "$@" 