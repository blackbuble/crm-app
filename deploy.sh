#!/bin/bash
set -e

echo "🚀 Starting deployment sequence..."

# Clear any existing cache that might point to non-existent tables
echo "🧹 Clearing previous caches..."
php artisan config:clear
php artisan cache:clear || true

# Create storage link
php artisan storage:link || true

# Run migrations
echo "📊 Running database migrations..."
php artisan migrate --force

# Now that tables exist, we can safely optimize
echo "⚡ Optimizing application..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan filament:optimize

# Set permissions
chmod -R 755 storage bootstrap/cache

echo "✅ Deployment sequence completed!"
