#!/bin/bash

echo "Starting deployment..."

# Pull latest changes
git pull origin main

# Install composer dependencies
composer install --no-dev --optimize-autoloader

# Install npm dependencies
npm install

# Build assets for production
npm run build

# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan route:clear

# Optimize Laravel
php artisan optimize

# Set proper permissions
chmod -R 755 storage bootstrap/cache public/build
chown -R www-data:www-data storage bootstrap/cache public/build

echo "Deployment completed!" 