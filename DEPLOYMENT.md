# Nixpacks Deployment Configuration for Laravel CRM

This project is configured for deployment using Nixpacks (Railway, Render, etc.)

## 📋 Prerequisites

### Required Environment Variables

```env
# Application
APP_NAME="CRM App"
APP_ENV=production
APP_KEY=base64:YOUR_APP_KEY_HERE
APP_DEBUG=false
APP_URL=https://your-domain.com

# Database
DB_CONNECTION=mysql
DB_HOST=your-db-host
DB_PORT=3306
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password

# Mail (Optional)
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="${APP_NAME}"

# Redis (Optional - for queue/cache)
REDIS_HOST=your-redis-host
REDIS_PASSWORD=null
REDIS_PORT=6379

# Queue
QUEUE_CONNECTION=database

# Session
SESSION_DRIVER=database
SESSION_LIFETIME=120

# Cache
CACHE_DRIVER=file
```

## 🚀 Deployment Steps

### 1. Railway Deployment

```bash
# Install Railway CLI
npm install -g @railway/cli

# Login
railway login

# Link project
railway link

# Set environment variables
railway variables set APP_ENV=production
railway variables set APP_DEBUG=false
# ... set other variables

# Deploy
git push
```

### 2. Render Deployment

1. Connect your GitHub repository
2. Set environment variables in Render dashboard
3. Deploy automatically on push

### 3. Manual Nixpacks Build

```bash
# Install Nixpacks
curl -sSL https://nixpacks.com/install.sh | bash

# Build
nixpacks build . --name crm-app

# Run
docker run -p 8080:8080 crm-app
```

## 📦 Files Included

- `nixpacks.json` - Nixpacks configuration
- `Procfile` - Process definitions (web + queue worker)
- `deploy.sh` - Post-deployment script
- `.nixpacks/setup.sh` - Custom setup script (optional)

## 🔧 Post-Deployment

The `deploy.sh` script automatically:
- ✅ Runs database migrations
- ✅ Caches configurations
- ✅ Optimizes Filament
- ✅ Creates storage symlink
- ✅ Sets proper permissions

## 🐛 Troubleshooting

### Storage Permissions
If you encounter storage permission errors:
```bash
chmod -R 755 storage bootstrap/cache
```

### Missing APP_KEY
Generate a new key:
```bash
php artisan key:generate --show
```
Then set it in your environment variables.

### Database Connection
Ensure your database credentials are correct and the database is accessible from your deployment platform.

### Queue Workers
If using queues, ensure the `queue` process is enabled in your platform's process manager.

## 📊 Performance Optimization

The build process includes:
- Composer autoload optimization
- Laravel config/route/view caching
- Filament asset optimization
- NPM production build

## 🔒 Security Checklist

- [ ] Set `APP_DEBUG=false` in production
- [ ] Use strong `APP_KEY`
- [ ] Enable HTTPS (`APP_URL=https://...`)
- [ ] Secure database credentials
- [ ] Configure CORS if needed
- [ ] Set up proper file permissions

## 📝 Notes

- The application runs on port 8080 by default (configurable via `PORT` env var)
- Queue worker is optional but recommended for background jobs
- Redis is optional but improves performance for cache/sessions
