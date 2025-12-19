# 🚀 Railway Deployment - Ready to Deploy!

## ✅ Issues Fixed

### 1. ❌ Missing package-lock.json → ✅ FIXED
- Generated `package-lock.json` with `npm install --package-lock-only`
- Updated build commands to use `npm install` as fallback
- Smart detection: uses `npm ci` if lockfile exists, otherwise `npm install`

### 2. ❌ Node.js package conflict → ✅ FIXED
- Changed from `nodejs-20_x` to `nodejs_20`
- Removed `providers` array to avoid auto-detection conflict

### 3. ❌ Composer not found → ✅ FIXED
- Added `php83Packages.composer` to nixPkgs
- Fallback installation in build.sh if needed

### 4. ❌ Missing PHP extensions → ✅ FIXED
- Added all required Laravel extensions (dom, xml, curl, fileinfo, tokenizer)

---

## 📦 Current Configuration

### nixpacks.json
```json
{
  "phases": {
    "setup": {
      "nixPkgs": [
        "php83",
        "php83Packages.composer",
        "nodejs_20",
        "php83Extensions.mbstring",
        "php83Extensions.pdo",
        "php83Extensions.pdo_mysql",
        "php83Extensions.gd",
        "php83Extensions.zip",
        "php83Extensions.bcmath",
        "php83Extensions.intl",
        "php83Extensions.redis",
        "php83Extensions.dom",
        "php83Extensions.xml",
        "php83Extensions.curl",
        "php83Extensions.fileinfo",
        "php83Extensions.tokenizer"
      ]
    },
    "install": {
      "cmds": [
        "composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist --no-scripts",
        "npm install --include=dev"
      ]
    },
    "build": {
      "cmds": [
        "composer run-script post-autoload-dump",
        "npm run build",
        "php artisan config:cache",
        "php artisan route:cache",
        "php artisan view:cache",
        "php artisan filament:optimize"
      ]
    }
  },
  "start": {
    "cmd": "php artisan serve --host=0.0.0.0 --port=${PORT:-8080}"
  }
}
```

---

## 🔐 Required Environment Variables

Set these in Railway dashboard before deploying:

### Critical (Must Set):
```env
APP_KEY=                    # Generate with: php artisan key:generate --show
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-app.railway.app

DB_CONNECTION=mysql
DB_HOST=${{ MYSQLHOST }}
DB_PORT=${{ MYSQLPORT }}
DB_DATABASE=${{ MYSQLDATABASE }}
DB_USERNAME=${{ MYSQLUSER }}
DB_PASSWORD=${{ MYSQLPASSWORD }}
```

### Recommended:
```env
COMPOSER_MEMORY_LIMIT=-1
NODE_OPTIONS=--max_old_space_size=4096
QUEUE_CONNECTION=database
SESSION_DRIVER=database
CACHE_DRIVER=file
```

---

## 🚀 Deployment Steps

### 1. Commit Changes
```bash
git add .
git commit -m "Fix Railway deployment configuration"
git push
```

### 2. Set Environment Variables in Railway
1. Go to Railway dashboard
2. Select your project
3. Go to "Variables" tab
4. Add all required environment variables above
5. **Important:** Generate APP_KEY locally first:
   ```bash
   php artisan key:generate --show
   ```
   Copy the output and paste to Railway

### 3. Deploy
Railway will auto-deploy after you push to the connected branch.

### 4. Run Migrations (After First Deploy)
In Railway dashboard:
```bash
php artisan migrate --force
```

---

## 📊 Expected Build Output

```
✓ Detected PHP (composer.json)
✓ Detected Node.js (package.json)
✓ Installing PHP 8.3
✓ Installing Composer
✓ Installing Node.js 20
✓ Installing PHP extensions
✓ Running composer install
✓ Installing NPM dependencies
✓ Building assets with Vite
✓ Running post-autoload scripts
✓ Caching Laravel configs
✓ Optimizing Filament
✓ Build complete!
```

---

## 🐛 If Build Fails

### Check Build Logs
Look for specific error messages in Railway logs.

### Common Issues:

**1. Vite Build Out of Memory**
```env
NODE_OPTIONS=--max_old_space_size=4096
```

**2. Composer Memory Limit**
```env
COMPOSER_MEMORY_LIMIT=-1
```

**3. Missing APP_KEY**
Generate locally and set in Railway:
```bash
php artisan key:generate --show
```

**4. Database Connection During Build**
This is normal - migrations run AFTER deployment, not during build.

### Try Simplified Config
If all else fails, use the minimal config:
```bash
mv nixpacks.json nixpacks.backup.json
mv nixpacks.simple.json nixpacks.json
git commit -am "Use simplified config" && git push
```

---

## 📝 Post-Deployment

### 1. Run Migrations
```bash
php artisan migrate --force
```

### 2. Create Admin User
```bash
php artisan make:filament-user
```

### 3. Seed Data (Optional)
```bash
php artisan db:seed --class=PricingConfigSeeder
```

### 4. Test Application
Visit your Railway URL and verify:
- ✅ Homepage loads
- ✅ Can login to admin panel
- ✅ Assets load correctly
- ✅ Database connection works

---

## 🔗 Useful Railway Commands

```bash
# View logs
railway logs

# Check status
railway status

# Run artisan commands
railway run php artisan migrate

# Open shell
railway shell

# Manage variables
railway variables
```

---

## ✅ Deployment Checklist

- [ ] `package-lock.json` exists and committed
- [ ] `composer.lock` exists and committed
- [ ] All environment variables set in Railway
- [ ] APP_KEY generated and set
- [ ] Database service created in Railway
- [ ] Code pushed to repository
- [ ] Build logs checked for errors
- [ ] Migrations run successfully
- [ ] Admin user created
- [ ] Application accessible via Railway URL

---

## 🎉 You're Ready to Deploy!

Everything is configured and ready. Just:
1. Set environment variables in Railway
2. Push your code
3. Monitor the build logs
4. Run migrations after deployment

Good luck! 🚀
