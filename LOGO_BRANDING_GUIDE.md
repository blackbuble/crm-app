# Company Logo & Branding Setup Guide

## Overview
Sistem CRM sekarang mendukung **logo perusahaan dinamis** yang dapat diatur melalui Settings dan akan otomatis muncul di:
- 🎨 **Navbar** (semua halaman)
- 🔐 **Halaman Login**
- 🌐 **Favicon** (tab browser)
- 📄 **PDF Quotations** (jika diimplementasikan)

## 🎯 Fitur

### 1. **Dynamic Logo**
- Upload logo melalui Settings page
- Otomatis muncul di navbar dan login page
- Support format: JPG, PNG, JPEG
- Auto-resize ke 300x300px
- Max size: 2MB

### 2. **Dynamic Brand Name**
- Nama perusahaan dari Settings
- Muncul di navbar
- Fallback ke `config('app.name')`

### 3. **Favicon**
- Logo juga digunakan sebagai favicon
- Muncul di tab browser

## 📋 Setup Instructions

### Step 1: Upload Logo

1. Login sebagai **Super Admin**
2. Buka menu **Settings** (di grup CRM)
3. Di section **Company Information**, upload logo:
   - Klik area upload atau drag & drop
   - Pilih file logo (JPG/PNG, max 2MB)
   - Logo akan auto-resize ke 300x300px
4. Isi **Company Name** (akan muncul di navbar)
5. Klik **Save Settings**

### Step 2: Verify Logo Appears

Logo akan otomatis muncul di:
- ✅ **Navbar** (kiri atas, semua halaman)
- ✅ **Login Page** (tengah atas)
- ✅ **Favicon** (tab browser)

### Step 3: Update Logo (Optional)

Untuk mengganti logo:
1. Buka **Settings**
2. Klik **X** pada logo lama untuk hapus
3. Upload logo baru
4. **Save Settings**

## 🔧 Technical Implementation

### Helper Functions Created

**File:** `app/Helpers/SettingsHelper.php`

#### 1. `get_setting($name, $default, $group)`
Get any setting value from database.

```php
$value = get_setting('company_name', 'Default Name');
```

#### 2. `get_company_logo()`
Get company logo URL.

```php
$logoUrl = get_company_logo();
// Returns: '/storage/settings/company/company-logo.png'
// Or null if not set
```

#### 3. `get_company_name()`
Get company name from settings.

```php
$name = get_company_name();
// Returns: 'PT. Your Company' or config('app.name')
```

#### 4. `get_company_info()`
Get all company information at once.

```php
$info = get_company_info();
// Returns: [
//     'name' => 'PT. Your Company',
//     'email' => 'hello@company.com',
//     'phone' => '+62123456789',
//     'address' => 'Jl. Example No. 123',
//     'tax_id' => '01.234.567.8-901.000',
//     'logo' => '/storage/settings/company/company-logo.png'
// ]
```

### Filament Panel Configuration

**File:** `app/Providers/Filament/AdminPanelProvider.php`

```php
public function panel(Panel $panel): Panel
{
    return $panel
        ->brandName(fn () => get_company_name())
        ->brandLogo(fn () => get_company_logo())
        ->brandLogoHeight('2.5rem')
        ->favicon(fn () => get_company_logo())
        // ... other configurations
}
```

**Features:**
- `brandName()` - Company name di navbar
- `brandLogo()` - Logo di navbar & login
- `brandLogoHeight()` - Tinggi logo (2.5rem = ~40px)
- `favicon()` - Icon di tab browser

## 📁 File Structure

### Logo Storage Location:
```
storage/
  app/
    public/
      settings/
        company/
          company-logo.png  ← Logo tersimpan di sini
```

### Public Access:
Logo accessible via:
```
http://your-domain.com/storage/settings/company/company-logo.png
```

**Note:** Pastikan symbolic link sudah dibuat:
```bash
php artisan storage:link
```

## 🎨 Logo Specifications

### Recommended:
- **Format:** PNG (untuk transparency)
- **Size:** 300x300px (square)
- **Aspect Ratio:** 1:1
- **Background:** Transparent atau putih
- **File Size:** < 500KB (max 2MB)

### Auto-Processing:
Sistem akan otomatis:
- ✅ Resize ke 300x300px
- ✅ Maintain aspect ratio
- ✅ Optimize file size
- ✅ Rename to `company-logo.ext`

## 💡 Usage Examples

### Example 1: Display Logo in Blade Template
```blade
@if(get_company_logo())
    <img src="{{ get_company_logo() }}" alt="{{ get_company_name() }}" class="h-10">
@else
    <span class="text-xl font-bold">{{ get_company_name() }}</span>
@endif
```

### Example 2: Use in PDF
```php
use Barryvdh\DomPDF\Facade\Pdf;

$pdf = Pdf::loadView('pdf.quotation', [
    'company' => get_company_info(),
    'logo' => get_company_logo(),
]);
```

### Example 3: Email Template
```blade
<table>
    <tr>
        <td>
            @if(get_company_logo())
                <img src="{{ get_company_logo() }}" alt="{{ get_company_name() }}" height="60">
            @endif
        </td>
    </tr>
    <tr>
        <td>
            <h2>{{ get_company_name() }}</h2>
        </td>
    </tr>
</table>
```

## 🔍 Troubleshooting

### Logo Not Showing?

**1. Check Storage Link:**
```bash
php artisan storage:link
```

**2. Check File Permissions:**
```bash
chmod -R 755 storage/app/public
```

**3. Check Settings Table:**
```sql
SELECT * FROM settings WHERE name = 'company_logo';
```

**4. Clear Cache:**
```bash
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

### Logo Too Large/Small?

**Adjust in AdminPanelProvider:**
```php
->brandLogoHeight('3rem')  // Larger (48px)
->brandLogoHeight('2rem')  // Smaller (32px)
```

### Logo Not Centered?

Logo akan otomatis centered di:
- ✅ Login page
- ✅ Navbar (left aligned by design)

Untuk custom positioning, bisa override CSS.

## 📝 Database Schema

### Settings Table:
```sql
CREATE TABLE settings (
    id BIGINT PRIMARY KEY,
    group VARCHAR(255),      -- 'general'
    name VARCHAR(255),       -- 'company_logo'
    locked BOOLEAN,          -- false
    payload JSON,            -- {"value": "settings/company/company-logo.png"}
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    UNIQUE(group, name)
);
```

### Example Data:
```json
{
    "group": "general",
    "name": "company_logo",
    "payload": {
        "value": "settings/company/company-logo.png"
    }
}
```

## 🎯 Best Practices

### 1. Logo Design
- Use simple, recognizable design
- Ensure readability at small sizes
- Use high contrast for visibility
- Consider dark/light mode compatibility

### 2. File Management
- Keep original high-res version
- Use PNG for transparency
- Optimize before upload
- Test on different screens

### 3. Branding Consistency
- Use same logo across all platforms
- Match colors with Filament theme
- Update favicon when changing logo
- Test on mobile devices

## 🚀 Advanced Features

### Custom Login Page (Optional)

Create custom login page with logo:

**File:** `app/Filament/Pages/Auth/Login.php`

```php
<?php

namespace App\Filament\Pages\Auth;

use Filament\Pages\Auth\Login as BaseLogin;

class Login extends BaseLogin
{
    public function mount(): void
    {
        parent::mount();
        
        // Custom login page logic
    }
}
```

Then register in `AdminPanelProvider`:
```php
->login(Login::class)
```

### Multiple Logos

For different contexts (navbar, login, email):

```php
// In Settings.php, add more logo fields:
FileUpload::make('navbar_logo'),
FileUpload::make('login_logo'),
FileUpload::make('email_logo'),
```

## 📚 Files Created/Modified

### New Files:
1. ✅ `app/Helpers/SettingsHelper.php` - Helper functions
2. ✅ `LOGO_BRANDING_GUIDE.md` - This documentation

### Modified Files:
1. ✅ `app/Providers/Filament/AdminPanelProvider.php` - Logo configuration
2. ✅ `composer.json` - Autoload SettingsHelper
3. ✅ `bootstrap/app.php` - Load helper manually

### Existing Files Used:
1. ✅ `app/Filament/Pages/Settings.php` - Logo upload
2. ✅ `database/migrations/2025_12_08_075125_create_settings_table.php` - Settings table

## ✨ Summary

✅ **Logo Upload** - Via Settings page  
✅ **Dynamic Display** - Navbar, Login, Favicon  
✅ **Auto-Resize** - 300x300px optimal  
✅ **Helper Functions** - Easy access anywhere  
✅ **Fallback Support** - Default if no logo  
✅ **Storage Management** - Public storage  

**Your CRM now has professional branding!** 🎨

---

**Last Updated:** 2025-12-12  
**Version:** 1.0  
**Author:** CRM Development Team
