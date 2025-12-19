#!/bin/bash
set -e

echo "🚀 Starting deployment sequence..."

# Fast cache clear
php artisan config:clear
php artisan cache:clear || true

# Storage link
php artisan storage:link || true

# Run migrations (Force)
echo "📊 Running database migrations..."
if php artisan migrate --force; then
    echo "✅ Migrations successful"
else
    echo "⚠️  Migration failed or DB not reachable yet, continuing to start app..."
fi

# Optimizations
echo "⚡ Optimizing application..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan filament:optimize || true

# Set permissions
chmod -R 755 storage bootstrap/cache

echo "✅ Deployment sequence finished!"
