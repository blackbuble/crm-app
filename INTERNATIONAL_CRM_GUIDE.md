# International CRM Implementation Guide

## Overview
Implementasi fitur internasional untuk CRM system dengan dukungan multi-negara, kode negara dinamis untuk WhatsApp, dan struktur tim sales yang lebih kompleks.

## 🌍 Fitur Baru

### 1. **Dynamic WhatsApp Country Code**
- WhatsApp sekarang mendukung kode negara dinamis
- Tidak lagi hardcoded ke Indonesia (+62)
- Otomatis menggunakan country code dari customer atau default +62

### 2. **Country & Area Management**
- **Country**: Negara untuk customer dan user
- **Country Code**: Kode negara untuk WhatsApp (e.g., +62, +65, +1)
- **Area**: Wilayah/region untuk sales (e.g., Jakarta, Surabaya, Singapore Central)

### 3. **New Role: Country Manager**
- Role baru di antara Super Admin dan Sales Manager
- Mengelola sales team di satu negara
- Memiliki akses penuh kecuali shield dan role management

## 📊 Struktur Hierarki Tim

```
Super Admin (Global)
    ├── Country Manager (Indonesia)
    │   ├── Sales Manager (Jakarta Area)
    │   │   ├── Sales Rep (Jakarta Pusat)
    │   │   └── Sales Rep (Jakarta Selatan)
    │   └── Sales Manager (Surabaya Area)
    │       ├── Sales Rep (Surabaya Utara)
    │       └── Sales Rep (Surabaya Selatan)
    │
    └── Country Manager (Singapore)
        └── Sales Manager (Singapore)
            ├── Sales Rep (Central)
            └── Sales Rep (East)
```

## 🗄️ Database Changes

### Migration Files Created:
1. **2025_12_12_000001_add_country_fields.php** (Already exists)
   - Adds `country` and `country_code` to `customers` table
   - Adds `country` and `country_code` to `users` table

2. **2025_12_12_070200_add_area_to_users_table.php** (New)
   - Adds `area` field to `users` table

### Run Migrations:
```bash
php artisan migrate
```

## 👥 User Management

### New Fields in Users Table:
- `country` (string, 100) - Country name
- `country_code` (string, 5) - Phone country code (e.g., +62)
- `area` (string, 100) - Sales area/region

### Creating Users with Country & Area:

**Via UserResource:**
1. Navigate to **Settings > Users & Teams**
2. Click **New User**
3. Fill in user information
4. Select **Country** (auto-fills country code)
5. Enter **Area/Region**
6. Select **Role**:
   - Super Admin
   - Country Manager (new!)
   - Sales Manager
   - Sales Rep
7. Select **Reports To** (manager)

## 🎯 Role Permissions

### Super Admin
- Full access to everything
- Can manage roles and permissions
- Can access Shield

### Country Manager (NEW)
- Manages all sales activities in their country
- Can view/edit all customers in their country
- Can manage Sales Managers and Sales Reps
- **Cannot** manage roles/permissions
- **Cannot** access Shield

### Sales Manager
- Manages sales team in their area
- Can view/edit customers assigned to their team
- Reports to Country Manager

### Sales Rep
- Manages their own customers
- Reports to Sales Manager

## 📱 WhatsApp Integration

### Helper Functions Created:

**File:** `app/Helpers/WhatsAppHelper.php`

#### 1. `format_whatsapp_number($phone, $countryCode = null)`
Formats phone number for WhatsApp with dynamic country code.

```php
// Example usage:
$formatted = format_whatsapp_number('8123456789', '+62');
// Returns: 628123456789

$formatted = format_whatsapp_number('021123456', '+62');
// Returns: 6221123456
```

#### 2. `get_whatsapp_url($phone, $countryCode = null, $message = null)`
Generates complete WhatsApp URL.

```php
// Example usage:
$url = get_whatsapp_url('8123456789', '+62', 'Hello from CRM!');
// Returns: https://wa.me/628123456789?text=Hello%20from%20CRM%21
```

#### 3. `get_country_codes()`
Returns array of country codes with country names.

```php
$codes = get_country_codes();
// Returns: ['+1' => 'United States / Canada (+1)', '+62' => 'Indonesia (+62)', ...]
```

#### 4. `get_countries()`
Returns array of countries.

```php
$countries = get_countries();
// Returns: ['Indonesia' => 'Indonesia', 'Singapore' => 'Singapore', ...]
```

### Usage in CustomerResource:

**Before:**
```php
Tables\Actions\Action::make('whatsapp')
    ->url(function (Customer $record) {
        $phone = preg_replace('/[^0-9]/', '', $record->phone);
        if (substr($phone, 0, 1) === '0') {
            $phone = '62' . substr($phone, 1); // Hardcoded!
        }
        return "https://wa.me/{$phone}";
    })
```

**After:**
```php
Tables\Actions\Action::make('whatsapp')
    ->url(function (Customer $record) {
        $message = "Hi {$record->name}, this is a follow-up.";
        return get_whatsapp_url($record->phone, $record->country_code, $message);
    })
```

## 📋 Customer Form Updates

### New Fields in Contact Information Section:
1. **Country** (Select, searchable)
   - Auto-fills country code when selected
   - Options from `get_countries()`

2. **Country Code** (Select, searchable)
   - Phone country code for WhatsApp
   - Options from `get_country_codes()`

3. **Phone** (Text Input)
   - Helper text: "Enter phone number without country code"
   - Store without country code (e.g., 8123456789)

### Auto Country Code Mapping:
```php
'Indonesia' => '+62',
'Singapore' => '+65',
'Malaysia' => '+60',
'Thailand' => '+66',
'Philippines' => '+63',
'Vietnam' => '+84',
'United States' => '+1',
'United Kingdom' => '+44',
'Australia' => '+61',
// ... and more
```

## 📊 Excel Template Updates

### New Columns Added:
- **Column E**: `country` (Indonesia, Singapore, etc.)
- **Column F**: `country_code` (+62, +65, +1, etc.)

### Updated Template Structure:
```
A: type
B: name
C: email
D: phone (without country code)
E: country (NEW)
F: country_code (NEW)
G: address
H: company_name
I: tax_id
J: first_name
K: last_name
L: notes
M: status
```

### Example Data:

**Company Example:**
```
type: company
name: PT Contoh Indonesia
email: contact@contoh.co.id
phone: 21123456
country: Indonesia
country_code: +62
address: Jl. Sudirman No. 123, Jakarta
company_name: PT Contoh Indonesia
tax_id: 01.234.567.8-901.000
status: lead
```

**Personal Example:**
```
type: personal
email: john.doe@email.com
phone: 8123456789
country: Indonesia
country_code: +62
address: Jl. Gatot Subroto No. 45, Bandung
first_name: John
last_name: Doe
status: prospect
```

## 🚀 Setup Instructions

### 1. Run Migrations
```bash
php artisan migrate
```

### 2. Update Composer Autoload
```bash
composer dump-autoload
```

### 3. Seed Country Manager Role
```bash
php artisan db:seed --class=CountryManagerRoleSeeder
```

### 4. Create Country Managers
1. Go to **Settings > Users & Teams**
2. Create new users with **Country Manager** role
3. Set their **Country** and **Area**

### 5. Assign Sales Managers to Country Managers
1. Edit existing Sales Managers
2. Set **Reports To** to their Country Manager
3. Set their **Country** and **Area**

## 📝 Best Practices

### 1. Phone Number Format
- Store phone numbers **without** country code
- Store country code separately in `country_code` field
- Use helper functions for WhatsApp URLs

### 2. Country & Area Assignment
- Always set country for users who manage customers
- Set area for Sales Managers and Sales Reps
- Country Managers can have area = "National" or country name

### 3. Hierarchical Structure
- Super Admin → Country Manager → Sales Manager → Sales Rep
- Each level reports to the level above
- Use `manager_id` field to maintain hierarchy

### 4. Excel Import
- Always include country and country_code in imports
- Phone numbers should be without country code
- Country code should include the '+' sign

## 🔧 Troubleshooting

### WhatsApp Link Not Working
1. Check if `country_code` is set for the customer
2. Verify phone number format (no spaces, no special characters)
3. Ensure helper functions are loaded (`composer dump-autoload`)

### Country Code Not Auto-Filling
1. Check if country is in the `$countryCodeMap` array
2. Verify the country name matches exactly
3. Add custom mappings if needed

### Role Permissions Not Working
1. Run `php artisan shield:generate --all`
2. Clear cache: `php artisan cache:clear`
3. Re-seed roles if needed

## 📚 Files Modified/Created

### New Files:
1. `app/Helpers/WhatsAppHelper.php` - WhatsApp helper functions
2. `app/Filament/Resources/UserResource.php` - User management
3. `app/Filament/Resources/UserResource/Pages/ListUsers.php`
4. `app/Filament/Resources/UserResource/Pages/CreateUser.php`
5. `app/Filament/Resources/UserResource/Pages/EditUser.php`
6. `database/migrations/2025_12_12_070200_add_area_to_users_table.php`
7. `database/seeders/CountryManagerRoleSeeder.php`

### Modified Files:
1. `app/Models/User.php` - Added country, country_code, area
2. `app/Models/Customer.php` - Added country, country_code
3. `app/Filament/Resources/CustomerResource.php` - Updated WhatsApp action, added country fields
4. `app/Exports/CustomersTemplateExport.php` - Added country columns
5. `app/Imports/CustomersImport.php` - Handle country fields
6. `composer.json` - Autoload WhatsAppHelper

## 🎉 Summary

Sistem CRM sekarang mendukung:
- ✅ Multi-country operations
- ✅ Dynamic WhatsApp country codes
- ✅ Hierarchical team structure with Country Manager
- ✅ Area/region management for sales teams
- ✅ International customer management
- ✅ Flexible phone number handling

---

**Version:** 2.0
**Last Updated:** 2025-12-12
**Author:** CRM Development Team
