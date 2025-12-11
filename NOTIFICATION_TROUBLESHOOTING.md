# Notification Troubleshooting Guide

## ✅ Status Update

**Test Notification:** ✅ BERHASIL
**Observer Notifications:** ⚠️ Sedang di-debug

## 🔍 Debugging Steps

### 1. Check Logs
Sekarang semua observer events di-log. Check file log:

```bash
# Windows (Laragon)
tail -f storage/logs/laravel.log

# Or view in editor
notepad storage/logs/laravel.log
```

### 2. Create Customer Test

**Scenario A: Customer dengan Assignment**
1. Buka **Customers → Create Customer**
2. Isi data:
   - Type: Personal
   - First Name: Test
   - Last Name: User
   - Email: test@example.com
   - **Assigned To: [Pilih user]** ← PENTING!
3. Save

**Expected Log:**
```
CustomerObserver::created triggered
- customer_id: X
- name: Test User
- assigned_to: Y

Sending notification to assigned user
- user_id: Y
- user_name: [User Name]
```

**Expected Notification:**
- Assigned user dapat: "New Customer Assigned"
- Manager dapat: "Customer Assigned to Team Member"

---

**Scenario B: Customer tanpa Assignment**
1. Create customer
2. **JANGAN pilih Assigned To**
3. Save

**Expected Log:**
```
CustomerObserver::created triggered
- customer_id: X
- name: Test User
- assigned_to: null

Customer created without assignment
- customer_id: X
```

**Expected Notification:**
- All managers/super_admin dapat: "New Unassigned Customer"

### 3. Update Customer Test

**Scenario A: Change Status to Inactive**
1. Edit existing customer
2. Change **Status** to "Inactive"
3. Save

**Expected Log:**
```
CustomerObserver::updated triggered
- dirty_fields: ['status']
- status: inactive

Customer status changed to inactive
```

**Expected Notification:**
- Managers dapat: "Customer Inactive"

---

**Scenario B: Convert Lead to Customer**
1. Edit customer dengan status "Lead" atau "Prospect"
2. Change **Status** to "Customer"
3. Save

**Expected Log:**
```
CustomerObserver::updated triggered
- dirty_fields: ['status']
- old_status: lead
- status: customer

Customer converted from lead/prospect
```

**Expected Notification:**
- Assigned user dapat: "🎉 Customer Conversion!"
- Managers dapat: "Customer Conversion"

---

**Scenario C: Reassign Customer**
1. Edit customer
2. Change **Assigned To** ke user lain
3. Save

**Expected Log:**
```
CustomerObserver::updated triggered
- dirty_fields: ['assigned_to']
- old_user: 1
- new_user: 2

Customer reassigned
```

**Expected Notification:**
- Old user dapat: "Customer Reassigned Away"
- New user dapat: "New Customer Assigned"
- Managers dapat: "Customer Reassigned"

## 🐛 Common Issues & Solutions

### Issue 1: Observer Tidak Ter-trigger

**Symptoms:**
- Tidak ada log "CustomerObserver::created"
- Tidak ada log "CustomerObserver::updated"

**Solutions:**
1. **Clear cache:**
```bash
php artisan config:clear
php artisan cache:clear
php artisan optimize:clear
```

2. **Check observer registration:**
```php
// app/Providers/AppServiceProvider.php
Customer::observe(CustomerObserver::class);
```

3. **Restart server:**
```bash
# Stop dan start ulang php artisan serve
# Atau restart Laragon
```

### Issue 2: Notification Tidak Muncul (tapi log ada)

**Symptoms:**
- Log menunjukkan "Sending notification to..."
- Tapi tidak ada di bell icon

**Solutions:**
1. **Check database:**
```sql
SELECT * FROM notifications 
WHERE notifiable_id = [YOUR_USER_ID]
ORDER BY created_at DESC;
```

2. **Check User model:**
```php
// Pastikan ada trait Notifiable
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;
}
```

3. **Hard refresh browser:**
- Press `Ctrl + Shift + R`
- Or clear browser cache

4. **Wait for polling:**
- Polling interval: 30 seconds
- Wait 30 detik atau refresh page

### Issue 3: Notification Muncul tapi Action Button Error

**Symptoms:**
- Notification muncul
- Click "View Customer" → Error 404

**Solutions:**
1. **Check URL generation:**
```php
// Harus pakai Resource::getUrl()
->url(fn () => CustomerResource::getUrl('edit', ['record' => $customer]))

// BUKAN route()
->url(route('filament.admin.resources.customers.edit', $customer))
```

2. **Check record exists:**
- Pastikan customer belum di-delete

### Issue 4: Manager Tidak Dapat Notification

**Symptoms:**
- Assigned user dapat notification
- Manager tidak dapat

**Solutions:**
1. **Check manager relationship:**
```sql
SELECT id, name, manager_id FROM users WHERE id = [ASSIGNED_USER_ID];
```

2. **Check manager exists:**
```sql
SELECT * FROM users WHERE id = [MANAGER_ID];
```

3. **Check roles:**
```sql
SELECT u.name, r.name as role
FROM users u
JOIN model_has_roles mhr ON u.id = mhr.model_id
JOIN roles r ON mhr.role_id = r.id
WHERE u.id = [MANAGER_ID];
```

## 📊 Monitoring Notifications

### Check Notification Count
```sql
-- Total notifications
SELECT COUNT(*) FROM notifications;

-- Unread notifications per user
SELECT 
    u.name,
    COUNT(*) as unread_count
FROM notifications n
JOIN users u ON n.notifiable_id = u.id
WHERE n.read_at IS NULL
GROUP BY u.id, u.name;

-- Recent notifications
SELECT 
    u.name as user,
    n.data->>'$.title' as title,
    n.created_at
FROM notifications n
JOIN users u ON n.notifiable_id = u.id
ORDER BY n.created_at DESC
LIMIT 10;
```

### Clear All Notifications (for testing)
```sql
-- Mark all as read
UPDATE notifications SET read_at = NOW() WHERE read_at IS NULL;

-- Delete all
DELETE FROM notifications;
```

## 🧪 Quick Test Commands

### Send Test Notification via Tinker
```bash
php artisan tinker
```

```php
// Get a user
$user = App\Models\User::first();

// Send notification
Filament\Notifications\Notification::make()
    ->title('Test from Tinker')
    ->body('This is a test notification')
    ->icon('heroicon-o-bell')
    ->sendToDatabase($user);

// Check notifications
$user->notifications;
$user->unreadNotifications;
```

### Trigger Observer Manually
```php
php artisan tinker
```

```php
// Create customer with assignment
$customer = App\Models\Customer::create([
    'type' => 'personal',
    'first_name' => 'Test',
    'last_name' => 'User',
    'email' => 'test@example.com',
    'phone' => '081234567890',
    'status' => 'lead',
    'assigned_to' => 1, // User ID
]);

// Check logs
tail -f storage/logs/laravel.log
```

## 📝 Next Steps

1. **Create a customer** dengan assignment
2. **Check logs** di `storage/logs/laravel.log`
3. **Check database** table `notifications`
4. **Check bell icon** di navbar
5. **Report findings:**
   - Apakah log muncul?
   - Apakah data masuk ke database?
   - Apakah bell icon menunjukkan badge?

## 🎯 Expected Behavior

### When Everything Works:

1. **Create Customer:**
   - ✅ Log: "CustomerObserver::created triggered"
   - ✅ Log: "Sending notification to assigned user"
   - ✅ Database: New row in `notifications` table
   - ✅ UI: Bell icon shows badge (1)
   - ✅ UI: Click bell → See notification
   - ✅ UI: Click "View Customer" → Navigate to edit page

2. **Update Customer:**
   - ✅ Log: "CustomerObserver::updated triggered"
   - ✅ Log: Specific event (inactive/conversion/reassignment)
   - ✅ Database: New row in `notifications` table
   - ✅ UI: Bell icon badge increases
   - ✅ UI: Notification appears in dropdown

## 📞 Support

Jika masih ada masalah, provide:
1. **Logs** dari `storage/logs/laravel.log`
2. **Database query** result dari `SELECT * FROM notifications`
3. **Screenshot** dari bell icon
4. **Steps** yang dilakukan untuk trigger notification
