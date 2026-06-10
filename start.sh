#!/bin/bash

echo "🚀 Starting Pricedom..."

# Wait for database
echo "⏳ Waiting for database..."
sleep 5

# Run migrations
echo "🗄️ Running migrations..."
php artisan migrate --force

# Cache config for production
echo "⚡ Caching configuration..."
php artisan config:cache

# Start Apache (Railway version)
echo "🌐 Starting web server..."
exec apache2-foreground