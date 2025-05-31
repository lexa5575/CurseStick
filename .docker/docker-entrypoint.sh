#!/bin/sh
set -e

# Устанавливаем порт для Nginx из переменной окружения PORT (предоставляется Render)
# Если PORT не задан, используем 80 по умолчанию (хотя Render всегда должен задавать PORT)
export NGINX_PORT=${PORT:-80}

# Подставляем значение порта в конфигурацию Nginx
# Создаем временный файл конфигурации, чтобы не изменять исходный в образе
# envsubst < /etc/nginx/sites-available/default > /etc/nginx/sites-available/default.conf
# mv /etc/nginx/sites-available/default.conf /etc/nginx/sites-available/default
# Более простой способ, если envsubst доступен и работает с заменой на месте (не всегда)
# Или используем sed, если envsubst нет или сложен в настройке прав

# Используем sed для замены порта в конфиге Nginx
# Ищем строку "listen 80 default_server;" и заменяем 80 на $NGINX_PORT
# Также для IPv6 "listen [::]:80 default_server;"
if [ -f /etc/nginx/sites-available/default ]; then
    sed -i "s/listen 80 default_server;/listen ${NGINX_PORT} default_server;/g" /etc/nginx/sites-available/default
    sed -i "s/listen \[::\]:80 default_server;/listen \[::\]:${NGINX_PORT} default_server;/g" /etc/nginx/sites-available/default
fi

# Создаем директорию для логов PHP-FPM и даем права
if [ ! -d /usr/local/var/log ]; then
    mkdir -p /usr/local/var/log
fi
# Создаем сам лог-файл, если его нет, и даем права
touch /usr/local/var/log/php-fpm.log
chown www-data:www-data /usr/local/var/log /usr/local/var/log/php-fpm.log
chmod -R 775 /usr/local/var/log

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