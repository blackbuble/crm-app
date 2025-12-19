#!/bin/bash
# Railway Build Script
# This runs during the build phase

set -e

echo "🔧 Starting build process..."

# Ensure composer is available
if ! command -v composer &> /dev/null; then
    echo "❌ Composer not found! Installing..."
    curl -sS https://getcomposer.org/installer | php
    mv composer.phar /usr/local/bin/composer
    chmod +x /usr/local/bin/composer
fi

echo "📦 Installing Composer dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist --no-scripts

# Check if npm is available
if command -v npm &> /dev/null; then
    echo "📦 Installing NPM dependencies..."
    
    # Use npm install if package-lock.json doesn't exist, otherwise use npm ci
    if [ -f "package-lock.json" ]; then
        echo "  Using npm ci (lockfile found)..."
        npm ci --include=dev
    else
        echo "  Using npm install (no lockfile)..."
        npm install --include=dev
    fi
    
    echo "🏗️ Building assets..."
    npm run build
else
    echo "⚠️  NPM not found, skipping asset build..."
fi

echo "⚡ Running post-install scripts..."
composer run-script post-autoload-dump

echo "🚀 Optimizing Laravel..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "🎨 Optimizing Filament..."
php artisan filament:optimize

echo "✅ Build completed successfully!"
