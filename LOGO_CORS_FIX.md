# Logo CORS & Storage Link Fix

## 🐛 Error Analysis

### Error Message:
```
Access to fetch at 'https://crm-app.test/storage/logo/company-logo.png' 
from origin 'http://crm-app.test' has been blocked by CORS policy
```

### Root Causes:

#### 1. **Mixed Protocol (HTTPS vs HTTP)**
- URL trying to load: `https://crm-app.test/storage/...`
- Origin: `http://crm-app.test`
- **Solution:** Use same protocol

#### 2. **Storage Link Not Created**
- Symbolic link `public/storage` → `storage/app/public` missing
- **Solution:** Run `php artisan storage:link`

#### 3. **File Doesn't Exist**
- Logo file not uploaded yet
- **Solution:** Upload logo via Settings page

---

## ✅ Quick Fix

### Step 1: Create Storage Link

Run this command in your project directory:

```bash
php artisan storage:link
```

**What it does:**
- Creates symbolic link: `public/storage` → `storage/app/public`
- Allows web access to files in `storage/app/public`

**Expected output:**
```
The [public/storage] link has been connected to [storage/app/public].
The links have been created.
```

---

### Step 2: Fix APP_URL in .env

Check your `.env` file and ensure consistent protocol:

**Option A: Use HTTP (Recommended for local)**
```env
APP_URL=http://crm-app.test
```

**Option B: Use HTTPS (If you have SSL)**
```env
APP_URL=https://crm-app.test
```

**Important:** Match the protocol you're actually using in browser!

---

### Step 3: Clear Config Cache

After changing `.env`:

```bash
php artisan config:clear
php artisan cache:clear
```

---

### Step 4: Verify File Exists

Check if logo file exists:

```bash
# Windows
dir storage\app\public\logo\company-logo.*

# Should show the logo file if uploaded
```

If file doesn't exist:
1. Go to Settings page
2. Upload logo
3. Save

---

## 🔧 Alternative Fix: Update Helper Function

If you want to force HTTP/HTTPS, update `SettingsHelper.php`:

```php
function get_company_logo()
{
    $logo = get_setting('company_logo');
    
    if ($logo) {
        if (filter_var($logo, FILTER_VALIDATE_URL)) {
            return $logo;
        }
        
        // Generate URL with explicit protocol
        $url = Storage::disk('public')->url($logo);
        
        // Force same protocol as APP_URL
        $appUrl = config('app.url');
        if (str_starts_with($appUrl, 'http://')) {
            $url = str_replace('https://', 'http://', $url);
        } elseif (str_starts_with($appUrl, 'https://')) {
            $url = str_replace('http://', 'https://', $url);
        }
        
        return $url;
    }
    
    return null;
}
```

---

## 📋 Checklist

Run through this checklist:

- [ ] **Storage link created** - `php artisan storage:link`
- [ ] **APP_URL correct** - Check `.env` file
- [ ] **Protocol consistent** - http or https, not mixed
- [ ] **Logo uploaded** - Via Settings page
- [ ] **File exists** - Check `storage/app/public/logo/`
- [ ] **Cache cleared** - `php artisan config:clear`
- [ ] **Browser refreshed** - Hard refresh (Ctrl+F5)

---

## 🔍 Verification Steps

### 1. Check Storage Link:

**Windows:**
```bash
dir public\storage
```

Should show: `<SYMLINK>` or `<JUNCTION>`

If not, run:
```bash
php artisan storage:link
```

### 2. Check File Exists:

```bash
dir storage\app\public\logo\
```

Should show: `company-logo.png` (or .jpg/.jpeg)

### 3. Test URL Directly:

Open in browser:
```
http://crm-app.test/storage/logo/company-logo.png
```

Should show the logo image.

### 4. Check APP_URL:

In your browser, check what protocol you're using:
- If URL bar shows `http://crm-app.test` → Use `APP_URL=http://crm-app.test`
- If URL bar shows `https://crm-app.test` → Use `APP_URL=https://crm-app.test`

---

## 🚨 Common Issues

### Issue 1: "The link already exists"

**Error:**
```
The [public/storage] link already exists.
```

**Solution:**
```bash
# Remove old link
rmdir public\storage

# Create new link
php artisan storage:link
```

### Issue 2: Logo shows in Settings but not in Navbar

**Cause:** Cache issue

**Solution:**
```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

Then hard refresh browser (Ctrl+F5)

### Issue 3: CORS Error Persists

**Cause:** Mixed protocol (http/https)

**Solution:**
1. Check browser URL bar - is it http or https?
2. Update `.env` APP_URL to match
3. Run `php artisan config:clear`
4. Hard refresh browser

### Issue 4: File Not Found (404)

**Cause:** Logo not uploaded or storage link missing

**Solution:**
1. Run `php artisan storage:link`
2. Go to Settings
3. Upload logo again
4. Save

---

## 💡 Quick Commands Summary

```bash
# 1. Create storage link
php artisan storage:link

# 2. Clear caches
php artisan config:clear
php artisan cache:clear
php artisan view:clear

# 3. Check if file exists
dir storage\app\public\logo\company-logo.*

# 4. Check if link exists
dir public\storage

# 5. Test URL
# Open in browser: http://crm-app.test/storage/logo/company-logo.png
```

---

## 🎯 Most Likely Solution

Based on the error, the most likely fix is:

### **Run this command:**
```bash
php artisan storage:link
```

### **Then refresh browser:**
Press `Ctrl + F5` (hard refresh)

### **If still not working:**

1. Check `.env` file:
   ```env
   APP_URL=http://crm-app.test
   ```
   (Use http, not https, unless you have SSL)

2. Clear config:
   ```bash
   php artisan config:clear
   ```

3. Hard refresh browser again

---

## 📝 Prevention

To prevent this in the future:

1. **Always run** `php artisan storage:link` after fresh install
2. **Keep APP_URL consistent** with actual protocol used
3. **Don't mix http and https** in same session
4. **Upload logo via Settings** page, not manually

---

**TL;DR:** Run `php artisan storage:link` and refresh browser!
