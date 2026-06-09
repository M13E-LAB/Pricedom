#!/bin/bash
set -e

echo "🚀 Starting Pricedom deployment..."

# Wait for PostgreSQL to be ready
echo "⏳ Waiting for PostgreSQL connection..."
timeout 30 bash -c 'until php artisan db:show 2>/dev/null; do sleep 1; done' || echo "⚠️ Database connection timeout, continuing..."

# Clear all caches
echo "🧹 Clearing caches..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Run migrations
echo "🗄️ Running database migrations..."
php artisan migrate --force

# Link storage (if needed)
echo "🔗 Linking storage..."
php artisan storage:link || true

# Optimize for production
echo "⚡ Optimizing for production..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "✅ Pricedom setup complete!"

# Start the server
exec php artisan serve --host=0.0.0.0 --port=$PORT