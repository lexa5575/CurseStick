# Этап 1: Сборка фронтенд-ассетов (остается на Alpine для скорости и размера этого этапа)
FROM node:22-alpine as frontend_builder

WORKDIR /app

# Копируем файлы для установки зависимостей Node.js
COPY package.json package-lock.json ./

# Устанавливаем зависимости Node.js
RUN npm install

# Копируем остальные файлы проекта, необходимые для сборки фронтенда
COPY . .

# Собираем фронтенд-ассеты
# Если у тебя другая команда для сборки, измени ее здесь
RUN npm run build

# Этап 2: Основной образ PHP + Nginx на Debian
FROM php:8.3-fpm

WORKDIR /var/www/html

# Устанавливаем системные зависимости
RUN apt-get update && apt-get install -y --no-install-recommends \
    nginx \
    supervisor \
    curl \
    libzip-dev \
    zlib1g-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libwebp-dev \
    libpq-dev && \
    # Очищаем кэш apt
    rm -rf /var/lib/apt/lists/*

# Устанавливаем расширения PHP
# Для Debian может потребоваться предварительно установить зависимости для некоторых расширений
# Например, libmagickwand-dev для imagick, но мы его пока не ставим
RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j$(nproc) gd pdo pdo_pgsql zip bcmath pcntl exif opcache

# Устанавливаем Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Копируем конфигурацию Nginx для сайта
COPY .docker/nginx.conf /etc/nginx/sites-available/default
# Активируем наш сайт и удаляем стандартный сайт Nginx, если он есть
RUN ln -sf /etc/nginx/sites-available/default /etc/nginx/sites-enabled/default \
    && if [ -f /etc/nginx/sites-enabled/default ]; then rm -f /etc/nginx/sites-enabled/default; fi \
    && ln -sf /etc/nginx/sites-available/default /etc/nginx/sites-enabled/default
    # Эта двойная проверка и создание ссылки нужны, чтобы избежать ошибки, если дефолтный сайт уже удален
    # или если наша ссылка уже существует.

# Копируем конфигурацию Supervisor
COPY .docker/supervisor.conf /etc/supervisor/conf.d/app.conf

# Копируем скрипт точки входа
COPY .docker/docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Копируем код приложения (без vendor и node_modules, они будут установлены/скопированы ниже)
COPY --chown=www-data:www-data . /var/www/html

# Копируем собранные фронтенд-ассеты из первого этапа
COPY --from=frontend_builder /app/public/build /var/www/html/public/build

# Устанавливаем зависимости Composer
RUN composer install --no-interaction --no-dev --prefer-dist --optimize-autoloader

# Устанавливаем права на папки
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Открываем порт, который будет слушать Nginx
EXPOSE 80

# Запускаем скрипт точки входа
ENTRYPOINT ["docker-entrypoint.sh"]

# Команда по умолчанию для Supervisor
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/supervisord.conf"]

# Force new build by adding a comment 