#!/bin/sh
set -ex # Добавляем -x для подробного вывода команд

echo "--- Current environment variables ---"
printenv
echo "-------------------------------------"

# Устанавливаем порт для Nginx из переменной окружения PORT (предоставляется Render)
# Если PORT не задан, используем 80 по умолчанию (хотя Render всегда должен задавать PORT)
export NGINX_PORT=${PORT:-80}
echo "NGINX_PORT set to: $NGINX_PORT"

echo "--- Nginx config files before modification ---"
ls -la /etc/nginx/
cat /etc/nginx/nginx.conf || echo "/etc/nginx/nginx.conf not found or cat failed"
cat /etc/nginx/sites-available/default || echo "/etc/nginx/sites-available/default not found or cat failed"
echo "--------------------------------------------"

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
    echo "Modifying Nginx port in /etc/nginx/sites-available/default to $NGINX_PORT"
    sed -i "s/listen 80 default_server;/listen ${NGINX_PORT} default_server;/g" /etc/nginx/sites-available/default
    sed -i "s/listen \[::\]:80 default_server;/listen \[::\]:${NGINX_PORT} default_server;/g" /etc/nginx/sites-available/default
else
    echo "Nginx site config /etc/nginx/sites-available/default not found!"
fi

echo "--- Nginx config files after modification ---"
cat /etc/nginx/sites-available/default || echo "/etc/nginx/sites-available/default not found or cat failed (after sed)"
echo "-------------------------------------------"

# Создаем директорию для логов PHP-FPM и даем права
echo "--- Creating PHP-FPM log directory and file ---"
if [ ! -d /usr/local/var/log ]; then
    mkdir -vp /usr/local/var/log # Добавляем -v для verbose
fi
touch /usr/local/var/log/php-fpm.log
chown -v www-data:www-data /usr/local/var/log /usr/local/var/log/php-fpm.log
chmod -vR 775 /usr/local/var/log
ls -la /usr/local/var/log/
echo "-------------------------------------------------"

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
echo "--- Creating PHP run directory ---"
if [ ! -d /var/run/php ]; then
    mkdir -vp /var/run/php
    chown -v www-data:www-data /var/run/php
    chmod -v 755 /var/run/php
fi
ls -la /var/run/php/
echo "----------------------------------"

# Выполняем команду, переданную в CMD Dockerfile (в нашем случае это supervisord)
echo "Executing CMD: $@"
exec "$@" 