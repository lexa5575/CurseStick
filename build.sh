#!/usr/bin/env bash

# Exit on error
set -e

echo "Installing PHP dependencies..."
composer install --no-dev --optimize-autoloader

echo "Installing Node.js dependencies..."
npm install

echo "Building frontend assets..."
npm run build

echo "Running Laravel post-deployment tasks..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan storage:link

# Миграции базы данных
# Раскомментируйте следующие строки, если нужно запустить миграции при деплое
# echo "Running database migrations..."
# php artisan migrate --force

echo "Setting permissions..."
chmod -R 775 storage bootstrap/cache

echo "Build completed successfully!"