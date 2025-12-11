# Navbar Notification Fix

## Problem
Filament's `Notification::make()->sendToDatabase()` tidak menyimpan ke database dengan benar.

## Solution
Gunakan **database notification trigger** yang berbeda.

## Test Command
```bash
php artisan tinker
```

```php
// Check Filament notifications
DB::table('notifications')->where('type', 'like', '%Filament%')->count();

// Check Laravel notifications  
DB::table('notifications')->where('type', 'like', '%App%')->count();

// View all
DB::table('notifications')->select('type')->get();
```

## Expected
- Laravel notifications: Should have count > 0
- Filament notifications: Might be 0 (this is the problem)

## Next Steps
Need to investigate why Filament notifications not saving.
