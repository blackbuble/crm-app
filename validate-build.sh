#!/bin/bash
# Pre-build validation script
# Run this locally before pushing to Railway

set -e

echo "🔍 Validating build configuration..."

# Check required files
echo "📋 Checking required files..."
required_files=("composer.json" "composer.lock" "package.json" "package-lock.json" ".env.example")
for file in "${required_files[@]}"; do
    if [ ! -f "$file" ]; then
        echo "❌ Missing required file: $file"
        exit 1
    else
        echo "✅ Found: $file"
    fi
done

# Check PHP version
echo ""
echo "🐘 Checking PHP version..."
if command -v php &> /dev/null; then
    php_version=$(php -v | head -n 1)
    echo "✅ $php_version"
else
    echo "⚠️  PHP not found locally (will be installed on Railway)"
fi

# Check Composer
echo ""
echo "📦 Checking Composer..."
if command -v composer &> /dev/null; then
    composer_version=$(composer --version)
    echo "✅ $composer_version"
    
    echo "🔍 Validating composer.json..."
    composer validate --no-check-all --no-check-publish
else
    echo "⚠️  Composer not found locally (will be installed on Railway)"
fi

# Check Node.js
echo ""
echo "🟢 Checking Node.js..."
if command -v node &> /dev/null; then
    node_version=$(node -v)
    npm_version=$(npm -v)
    echo "✅ Node.js: $node_version"
    echo "✅ NPM: $npm_version"
    
    echo "🔍 Checking for npm audit issues..."
    npm audit --audit-level=high || echo "⚠️  Found npm vulnerabilities (non-blocking)"
else
    echo "⚠️  Node.js not found locally (will be installed on Railway)"
fi

# Check Laravel
echo ""
echo "🎨 Checking Laravel..."
if [ -f "artisan" ]; then
    echo "✅ Laravel artisan found"
    
    if command -v php &> /dev/null; then
        echo "🔍 Testing artisan commands..."
        php artisan --version || echo "⚠️  Artisan test failed (may need dependencies)"
    fi
else
    echo "❌ artisan file not found!"
    exit 1
fi

# Check environment variables template
echo ""
echo "🔐 Checking environment configuration..."
if [ -f ".env.example" ]; then
    echo "✅ .env.example found"
    
    # Check for required env vars
    required_vars=("APP_NAME" "APP_ENV" "APP_KEY" "DB_CONNECTION" "DB_HOST")
    for var in "${required_vars[@]}"; do
        if grep -q "^$var=" .env.example; then
            echo "  ✅ $var defined"
        else
            echo "  ⚠️  $var not found in .env.example"
        fi
    done
else
    echo "❌ .env.example not found!"
    exit 1
fi

# Check nixpacks configuration
echo ""
echo "📦 Checking Nixpacks configuration..."
if [ -f "nixpacks.json" ]; then
    echo "✅ nixpacks.json found"
    
    # Validate JSON
    if command -v python3 &> /dev/null; then
        python3 -m json.tool nixpacks.json > /dev/null && echo "  ✅ Valid JSON" || echo "  ❌ Invalid JSON!"
    fi
else
    echo "⚠️  nixpacks.json not found (will use auto-detection)"
fi

# Check Railway configuration
echo ""
echo "🚂 Checking Railway configuration..."
if [ -f "railway.toml" ]; then
    echo "✅ railway.toml found"
else
    echo "⚠️  railway.toml not found (optional)"
fi

# Summary
echo ""
echo "================================"
echo "✅ Pre-build validation complete!"
echo "================================"
echo ""
echo "📝 Next steps:"
echo "1. Commit all changes: git add . && git commit -m 'Deploy to Railway'"
echo "2. Push to Railway: git push"
echo "3. Monitor build logs in Railway dashboard"
echo ""
echo "🔗 Useful Railway commands:"
echo "  railway logs        - View application logs"
echo "  railway status      - Check deployment status"
echo "  railway variables   - Manage environment variables"
echo ""
