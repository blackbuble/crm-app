# Logo Upload - Public Storage Update

## 🔄 Changes Made

### Previous Setup:
- ❌ Logo stored in: `storage/app/public/settings/company/`
- ❌ Required symlink: `php artisan storage:link`
- ❌ Complex path structure

### New Setup:
- ✅ Logo stored in: `storage/app/public/logo/`
- ✅ Still uses public disk (symlink required)
- ✅ Simpler path structure
- ✅ **Logo preview displayed in Settings page**

## 📁 File Structure

### Storage Location:
```
storage/
  app/
    public/
      logo/
        company-logo.png  ← Logo tersimpan di sini
```

### Public Access (via symlink):
```
public/
  storage/  ← Symlink ke storage/app/public/
    logo/
      company-logo.png  ← Accessible via web
```

### URL Access:
```
http://your-domain.com/storage/logo/company-logo.png
```

## 🔧 Setup Instructions

### Step 1: Create Storage Link (REQUIRED)

**Via Artisan:**
```bash
php artisan storage:link
```

This creates a symbolic link:
```
public/storage → storage/app/public
```

**Verify Link Created:**
```bash
# Windows
dir public\storage

# Linux/Mac
ls -la public/storage
```

### Step 2: Upload Logo

1. Login as **Super Admin**
2. Go to **Settings** page
3. In **Company Information** section:
   - Click upload area or drag & drop logo
   - Logo will be uploaded to `storage/app/public/logo/`
   - **Logo preview will appear immediately**
4. Fill **Company Name**
5. Click **Save Settings**

### Step 3: Verify Logo

Logo should appear in:
- ✅ **Settings page** (preview after upload)
- ✅ **Navbar** (all pages)
- ✅ **Login page**
- ✅ **Favicon** (browser tab)

## 🎨 Features

### 1. **Live Preview in Settings**
- Logo preview shows immediately after upload
- Preview height: 150px
- Aspect ratio: 1:1 (square)
- Downloadable from Settings page

### 2. **Auto-Resize**
- Target size: 300x300px
- Maintains aspect ratio
- Optimizes file size

### 3. **File Naming**
- Auto-renamed to: `company-logo.ext`
- Extension preserved (png/jpg/jpeg)
- Overwrites previous logo

## 💡 Code Changes

### Settings.php
```php
FileUpload::make('company_logo')
    ->disk('public')                    // Use public disk
    ->directory('logo')                 // Simpler directory
    ->imagePreviewHeight('150')         // Show preview
    ->downloadable()                    // Allow download
```

### SettingsHelper.php
```php
function get_company_logo()
{
    $logo = get_setting('company_logo');
    
    if ($logo) {
        // Generate URL: /storage/logo/company-logo.png
        return Storage::disk('public')->url($logo);
    }
    
    return null;
}
```

## 🔍 Troubleshooting

### Logo Not Showing?

**1. Check Storage Link:**
```bash
php artisan storage:link
```

**2. Verify Link Exists:**
```bash
# Windows
dir public\storage

# Should show: <SYMLINK> or <JUNCTION>
```

**3. Check File Exists:**
```bash
# Check if logo file exists
dir storage\app\public\logo\company-logo.*
```

**4. Check Permissions:**
```bash
# Windows (run as Administrator)
icacls storage\app\public\logo /grant Users:F /T

# Linux/Mac
chmod -R 755 storage/app/public/logo
```

**5. Clear Cache:**
```bash
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

### Logo Preview Not Showing in Settings?

**Check:**
1. ✅ Storage link created
2. ✅ File uploaded successfully
3. ✅ Database has correct path
4. ✅ Browser cache cleared

**Test URL directly:**
```
http://localhost/storage/logo/company-logo.png
```

### Database Check:
```sql
SELECT * FROM settings WHERE name = 'company_logo';

-- Should return:
-- payload: {"value":"logo/company-logo.png"}
```

## 📊 Path Comparison

### Old Path:
```
Storage: storage/app/public/settings/company/company-logo.png
URL:     /storage/settings/company/company-logo.png
DB:      settings/company/company-logo.png
```

### New Path:
```
Storage: storage/app/public/logo/company-logo.png
URL:     /storage/logo/company-logo.png
DB:      logo/company-logo.png
```

## 🚀 Migration Guide

### If You Already Have Logo Uploaded:

**Option 1: Re-upload (Recommended)**
1. Go to Settings
2. Delete old logo
3. Upload new logo
4. Save

**Option 2: Manual Migration**
```bash
# Create new directory
mkdir storage\app\public\logo

# Copy logo (if exists)
copy storage\app\public\settings\company\company-logo.* storage\app\public\logo\

# Update database
# Run in MySQL/phpMyAdmin:
UPDATE settings 
SET payload = JSON_SET(payload, '$.value', 'logo/company-logo.png')
WHERE name = 'company_logo';
```

## ✨ Benefits of New Structure

1. **Simpler Path**
   - Old: `settings/company/company-logo.png`
   - New: `logo/company-logo.png`

2. **Better Organization**
   - Dedicated `logo` folder
   - Easy to find
   - Clear purpose

3. **Preview in Settings**
   - See logo immediately
   - Download option
   - Better UX

4. **Consistent Naming**
   - Always `company-logo.ext`
   - No confusion
   - Easy to reference

## 📝 Files Modified

1. ✅ `app/Filament/Pages/Settings.php`
   - Changed `directory('settings/company')` → `directory('logo')`
   - Added `disk('public')`
   - Added `imagePreviewHeight('150')`
   - Added `downloadable()`

2. ✅ `app/Helpers/SettingsHelper.php`
   - Updated `get_company_logo()` to use `Storage::disk('public')->url()`
   - Better comments

## 🎯 Summary

✅ **Simpler path**: `logo/` instead of `settings/company/`  
✅ **Preview enabled**: See logo in Settings page  
✅ **Downloadable**: Can download from Settings  
✅ **Public disk**: Uses Laravel's public disk  
✅ **Symlink required**: `php artisan storage:link`  
✅ **Auto-resize**: 300x300px optimal  

---

**Important:** Don't forget to run `php artisan storage:link` !

**Last Updated:** 2025-12-12  
**Version:** 1.1
