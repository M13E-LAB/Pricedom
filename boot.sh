#!/bin/bash

echo "🚀 Pricedom boot sequence..."

# Ensure proper permissions
chmod -R 755 storage bootstrap/cache

# Clear caches
php artisan config:clear
php artisan cache:clear

# Run migrations
php artisan migrate --force

echo "✅ Boot complete!"