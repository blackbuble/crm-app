# Nixpacks Build Troubleshooting Guide

## Common Build Failures & Solutions

### 1. ❌ Node.js Package Conflict
**Error:** `undefined variable 'nodejs-20_x'` or `node provider conflict`

**Solution:**
- ✅ Use `nodejs_20` (underscore, not dash)
- ✅ Don't define `providers` array - let auto-detect
- ✅ Remove from nixPkgs if package.json exists

**Current Config:**
```json
"nixPkgs": ["nodejs_20"]  // ✅ Correct
```

---

### 2. ❌ Composer Not Found
**Error:** `composer: command not found`

**Solution:**
- ✅ Add `php83Packages.composer` to nixPkgs
- ✅ Or use fallback in build.sh

**Current Config:**
```json
"nixPkgs": ["php83Packages.composer"]  // ✅ Included
```

---

### 3. ❌ Missing PHP Extensions
**Error:** `Class 'DOMDocument' not found` or similar

**Required Extensions:**
- ✅ mbstring (string manipulation)
- ✅ pdo, pdo_mysql (database)
- ✅ gd (image processing)
- ✅ zip (file compression)
- ✅ bcmath (arbitrary precision math)
- ✅ intl (internationalization)
- ✅ redis (caching)
- ✅ dom (XML/HTML parsing)
- ✅ xml (XML processing)
- ✅ curl (HTTP requests)
- ✅ fileinfo (file type detection)
- ✅ tokenizer (PHP tokenizer)

**All included in current config ✅**

---

### 4. ❌ NPM Install Fails
**Error:** `npm ERR! code ERESOLVE` or dependency conflicts

**Solutions:**
- Use `npm ci` instead of `npm install` ✅
- Add `--legacy-peer-deps` if needed
- Check package-lock.json is committed

**Current Config:**
```bash
npm ci --include=dev  // ✅ Using ci
```

---

### 5. ❌ Vite Build Fails
**Error:** `vite build` fails or out of memory

**Solutions:**
- Increase Node.js memory: `NODE_OPTIONS=--max_old_space_size=4096`
- Check vite.config.js is correct
- Ensure all dependencies are installed

**Add to Railway env vars:**
```env
NODE_OPTIONS=--max_old_space_size=4096
```

---

### 6. ❌ Laravel Optimization Fails
**Error:** `artisan config:cache` fails

**Common Causes:**
- Missing APP_KEY
- Invalid .env configuration
- Missing required env vars

**Required Env Vars:**
```env
APP_KEY=base64:...
APP_ENV=production
APP_DEBUG=false
DB_CONNECTION=mysql
DB_HOST=...
DB_DATABASE=...
DB_USERNAME=...
DB_PASSWORD=...
```

---

### 7. ❌ Storage/Cache Permission Issues
**Error:** `Permission denied` for storage or cache

**Solution:**
```bash
chmod -R 755 storage bootstrap/cache
```

**Included in deploy.sh ✅**

---

### 8. ❌ Composer Memory Limit
**Error:** `Allowed memory size exhausted`

**Solution:**
Add to Railway env vars:
```env
COMPOSER_MEMORY_LIMIT=-1
```

---

### 9. ❌ Database Connection During Build
**Error:** `SQLSTATE[HY000] [2002] Connection refused`

**Cause:** Trying to connect to DB during build phase

**Solution:**
- Don't run migrations in build phase
- Run migrations in deploy.sh (after container starts)
- Use `--no-scripts` flag for composer install ✅

**Current Config:**
```bash
composer install --no-scripts  // ✅ Correct
```

---

### 10. ❌ Filament Optimize Fails
**Error:** `filament:optimize` command not found

**Cause:** Filament not installed or wrong version

**Solution:**
- Ensure filament/filament is in composer.json
- Run after composer install completes
- Check Filament version compatibility

---

## Recommended Build Order

1. ✅ Install PHP + Composer + Node.js (setup phase)
2. ✅ Install Composer dependencies (--no-scripts)
3. ✅ Install NPM dependencies
4. ✅ Build assets (npm run build)
5. ✅ Run Composer post-scripts
6. ✅ Cache Laravel configs
7. ✅ Optimize Filament

**Current build.sh follows this order ✅**

---

## Environment Variables Checklist

### Required:
- [ ] APP_KEY
- [ ] APP_ENV=production
- [ ] APP_DEBUG=false
- [ ] APP_URL
- [ ] DB_CONNECTION
- [ ] DB_HOST
- [ ] DB_DATABASE
- [ ] DB_USERNAME
- [ ] DB_PASSWORD

### Recommended:
- [ ] COMPOSER_MEMORY_LIMIT=-1
- [ ] NODE_OPTIONS=--max_old_space_size=4096
- [ ] QUEUE_CONNECTION=database
- [ ] SESSION_DRIVER=database
- [ ] CACHE_DRIVER=file

---

## Debug Commands

### Check PHP version:
```bash
php -v
```

### Check Composer:
```bash
composer --version
```

### Check Node/NPM:
```bash
node -v
npm -v
```

### Check PHP extensions:
```bash
php -m
```

### Test Laravel:
```bash
php artisan --version
php artisan config:clear
```

---

## If Build Still Fails

1. **Check Railway build logs** for exact error
2. **Verify all env vars** are set correctly
3. **Try simplified nixpacks.json** (remove custom commands)
4. **Use Railway's default detection** (delete nixpacks.json temporarily)
5. **Check composer.lock** is committed and up-to-date
6. **Verify package-lock.json** exists and is committed

---

## Alternative: Simplified Config

If complex config fails, try this minimal version:

```json
{
  "phases": {
    "setup": {
      "nixPkgs": ["php83", "php83Packages.composer"]
    }
  }
}
```

Let Nixpacks auto-detect everything else.
