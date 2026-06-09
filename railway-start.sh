#!/bin/bash

echo "🚀 Starting Pricedom deployment..."

# Set error handling but don't exit on first error
set +e

# Check if PORT is set
if [ -z "$PORT" ]; then
    export PORT=8080
    echo "⚠️ PORT not set, defaulting to 8080"
fi

echo "🌐 Using port: $PORT"

# Basic Laravel setup
echo "🧹 Clearing caches..."
php artisan config:clear || echo "Config clear failed, continuing..."
php artisan cache:clear || echo "Cache clear failed, continuing..."
php artisan route:clear || echo "Route clear failed, continuing..."
php artisan view:clear || echo "View clear failed, continuing..."

# Test database connection
echo "🗄️ Testing database connection..."
if php artisan db:show 2>/dev/null; then
    echo "✅ Database connected successfully"
    # Run migrations only if DB is connected
    php artisan migrate --force || echo "⚠️ Migration failed, continuing..."
else
    echo "❌ Database connection failed, skipping migrations"
fi

# Link storage (if needed)
echo "🔗 Linking storage..."
php artisan storage:link 2>/dev/null || echo "Storage link failed or already exists"

# Simple production optimization
echo "⚡ Basic optimization..."
php artisan config:cache 2>/dev/null || echo "Config cache failed"

echo "✅ Pricedom setup complete!"

# Start the server with verbose output
echo "🚀 Starting server on 0.0.0.0:$PORT"
exec php artisan serve --host=0.0.0.0 --port=$PORT --verbose