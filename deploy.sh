#!/bin/bash

# Exit on error
set -e

echo "🚀 Starting deployment..."

# Run migrations
echo "📊 Running database migrations..."
php artisan migrate --force

# Clear and cache configurations
echo "⚡ Optimizing application..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Optimize Filament
echo "🎨 Optimizing Filament..."
php artisan filament:optimize

# Create storage link if not exists
echo "🔗 Creating storage link..."
php artisan storage:link || true

# Set permissions
echo "🔐 Setting permissions..."
chmod -R 755 storage bootstrap/cache

# Generate app key if not set
if [ -z "$APP_KEY" ]; then
    echo "🔑 Generating application key..."
    php artisan key:generate --force
fi

echo "✅ Deployment completed successfully!"
