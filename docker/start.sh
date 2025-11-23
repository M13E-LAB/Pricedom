#!/bin/bash

# Set default port if not provided
export PORT=${PORT:-8080}

# Replace PORT in nginx config
envsubst '${PORT}' < /etc/nginx/sites-available/default > /tmp/nginx.conf
mv /tmp/nginx.conf /etc/nginx/sites-available/default

# Generate application key if not exists
php artisan key:generate --force

# Create database file if not exists
touch /var/www/database/database.sqlite

# Run migrations
php artisan migrate --force

# Cache configuration for better performance
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Start PHP-FPM in background
php-fpm -D

# Start nginx in foreground
nginx -g "daemon off;" 