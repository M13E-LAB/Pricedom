#!/bin/bash
set -e

echo "🚀 Starting Pricedom deployment..."

# Create SQLite database if not exists
touch /tmp/database.sqlite
chmod 664 /tmp/database.sqlite

# Clear caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Run migrations
php artisan migrate --force

# Generate app key if needed
if [ -z "$APP_KEY" ]; then
    php artisan key:generate --force
fi

# Link storage (if needed)
php artisan storage:link || true

echo "✅ Pricedom setup complete!"

# Start the server
exec php artisan serve --host=0.0.0.0 --port=$PORT