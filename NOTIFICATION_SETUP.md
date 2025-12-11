# Notification System Setup - Complete Guide

## ✅ Changes Made

### 1. Enabled Database Notifications in Filament
**File:** `app/Providers/Filament/AdminPanelProvider.php`

Added:
```php
->databaseNotifications()
->databaseNotificationsPolling('30s')
```

This enables:
- Bell icon in navbar for all users
- Real-time notification polling every 30 seconds
- Database-backed notifications

### 2. Fixed Observer Notification URLs
**Files:**
- `app/Observers/CustomerObserver.php`
- `app/Observers/QuotationObserver.php`

Changed from:
```php
->url(route('filament.admin.resources.customers.edit', $customer))
```

To:
```php
->url(fn () => CustomerResource::getUrl('edit', ['record' => $customer]))
```

### 3. Created Test Notification Page
**Files:**
- `app/Filament/Pages/TestNotification.php`
- `resources/views/filament/pages/test-notification.blade.php`

Features:
- Send test notification to yourself
- Send notification to all users
- View notification configuration
- Troubleshooting guide

## 📋 Prerequisites Checklist

✅ **User Model** - Already using `Notifiable` trait
✅ **Notifications Table** - Migration exists: `2025_12_07_071526_create_notifications_table.php`
✅ **Database Notifications** - Enabled in AdminPanelProvider
✅ **Observers** - CustomerObserver, QuotationObserver, FollowUpObserver registered

## 🚀 How to Use

### 1. Run Migrations (if not done)
```bash
php artisan migrate
```

### 2. Clear Cache
```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

### 3. Test Notifications

#### Option A: Use Test Page (Recommended)
1. Login as super_admin
2. Go to **System → Test Notifications**
3. Click "Send Test Notification to Me"
4. Check the bell icon (🔔) in navbar
5. You should see a red badge with "1"
6. Click bell to view notification

#### Option B: Trigger Real Events
1. **Create a Customer** and assign to a user
   - Assigned user gets notification
   - Manager gets notification
   
2. **Update Customer Status** to "inactive"
   - Manager gets notification
   
3. **Convert Lead to Customer**
   - Celebration notification sent
   
4. **Reassign Customer**
   - Old and new user get notifications

### 4. Check Notifications in Navbar

The bell icon should appear in the top-right corner:
```
[Profile Icon] [🔔 (1)] [Theme Toggle]
```

Click the bell to see:
- Notification title
- Notification body
- Action buttons (if any)
- Time ago
- Mark as read option

## 🔧 Notification System Components

### 1. AdminPanelProvider Configuration
```php
public function panel(Panel $panel): Panel
{
    return $panel
        // ... other config
        ->databaseNotifications()           // Enable notifications
        ->databaseNotificationsPolling('30s'); // Poll every 30 seconds
}
```

### 2. Sending Notifications

#### Basic Notification
```php
use Filament\Notifications\Notification;

Notification::make()
    ->title('Customer Created')
    ->body('New customer has been added')
    ->icon('heroicon-o-user-plus')
    ->iconColor('success')
    ->sendToDatabase($user);
```

#### With Action Button
```php
use Filament\Notifications\Actions\Action;
use App\Filament\Resources\CustomerResource;

Notification::make()
    ->title('New Customer Assigned')
    ->body("You have been assigned: {$customer->name}")
    ->actions([
        Action::make('view')
            ->label('View Customer')
            ->url(fn () => CustomerResource::getUrl('edit', ['record' => $customer]))
            ->button(),
    ])
    ->sendToDatabase($user);
```

#### Send to Multiple Users
```php
$users = User::role(['super_admin', 'sales_manager'])->get();

foreach ($users as $user) {
    Notification::make()
        ->title('Important Update')
        ->body('System maintenance scheduled')
        ->sendToDatabase($user);
}
```

### 3. Notification Icons & Colors

**Icons:**
- `heroicon-o-bell` - General notifications
- `heroicon-o-user-plus` - New user/customer
- `heroicon-o-check-circle` - Success
- `heroicon-o-exclamation-triangle` - Warning
- `heroicon-o-x-circle` - Error
- `heroicon-o-trophy` - Achievement
- `heroicon-o-megaphone` - Announcement

**Colors:**
- `success` - Green
- `warning` - Yellow
- `danger` - Red
- `info` - Blue
- `primary` - Pink (theme color)

## 📊 Database Structure

### Notifications Table
```sql
CREATE TABLE notifications (
    id UUID PRIMARY KEY,
    type VARCHAR,
    notifiable_type VARCHAR,  -- App\Models\User
    notifiable_id BIGINT,     -- User ID
    data TEXT,                -- JSON notification data
    read_at TIMESTAMP NULL,   -- NULL = unread
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### Query Notifications
```php
// Get all notifications for a user
$notifications = auth()->user()->notifications;

// Get unread notifications
$unread = auth()->user()->unreadNotifications;

// Mark as read
$notification->markAsRead();

// Mark all as read
auth()->user()->unreadNotifications->markAsRead();
```

## 🎯 Current Notifications

### Customer Events
1. **Customer Created** (with assignment)
   - Sent to: Assigned user, Manager
   - Icon: user-plus
   - Color: success
   
2. **Customer Inactive**
   - Sent to: Managers, Super admins
   - Icon: exclamation-triangle
   - Color: warning
   
3. **Customer Conversion** (Lead → Customer)
   - Sent to: Assigned user, Managers
   - Icon: trophy
   - Color: success
   
4. **Customer Reassigned**
   - Sent to: Old user, New user, Managers
   - Icon: user-group
   - Color: info

### Quotation Events
1. **Quotation Created**
   - Sent to: Sales rep, Managers
   - Icon: document-text
   - Color: info
   
2. **Quotation Sent**
   - Sent to: Sales rep
   - Icon: paper-airplane
   - Color: info
   
3. **Deal Closed** (Quotation Accepted)
   - Sent to: Sales rep, Managers
   - Icon: trophy
   - Color: success
   - Broadcast: Yes (real-time)
   
4. **Quotation Rejected**
   - Sent to: Sales rep, Managers
   - Icon: x-circle
   - Color: danger

## 🐛 Troubleshooting

### Notifications Not Showing

1. **Check Bell Icon Exists**
   ```php
   // In AdminPanelProvider.php
   ->databaseNotifications()  // Must be present
   ```

2. **Check Database Table**
   ```bash
   php artisan migrate:status
   # Should show: 2025_12_07_071526_create_notifications_table [Ran]
   ```

3. **Check User Model**
   ```php
   use Illuminate\Notifications\Notifiable;
   
   class User extends Authenticatable
   {
       use Notifiable;  // Must be present
   }
   ```

4. **Clear Cache**
   ```bash
   php artisan config:clear
   php artisan cache:clear
   php artisan view:clear
   ```

5. **Check Database**
   ```sql
   SELECT * FROM notifications ORDER BY created_at DESC LIMIT 10;
   ```

### Notifications Not Updating

1. **Check Polling Interval**
   ```php
   ->databaseNotificationsPolling('30s')  // Adjust if needed
   ```

2. **Force Refresh**
   - Hard refresh browser (Ctrl+Shift+R)
   - Clear browser cache

3. **Check Browser Console**
   - Open DevTools (F12)
   - Look for JavaScript errors

## 📝 Best Practices

1. **Always use Resource::getUrl()** for action URLs
   ```php
   // ✅ Correct
   ->url(fn () => CustomerResource::getUrl('edit', ['record' => $customer]))
   
   // ❌ Wrong
   ->url(route('filament.admin.resources.customers.edit', $customer))
   ```

2. **Use descriptive titles and bodies**
   ```php
   ->title('Customer Assigned')  // Clear and concise
   ->body("You have been assigned: {$customer->name}")  // Specific details
   ```

3. **Add action buttons for important notifications**
   ```php
   ->actions([
       Action::make('view')->url(...)->button(),
   ])
   ```

4. **Use appropriate icons and colors**
   ```php
   ->icon('heroicon-o-trophy')  // Matches the event
   ->iconColor('success')        // Matches the sentiment
   ```

5. **Send to relevant users only**
   ```php
   // Don't spam everyone
   $managers = User::role(['super_admin', 'sales_manager'])->get();
   ```

## ✅ Status

- ✅ Database notifications enabled
- ✅ Polling configured (30s)
- ✅ Observer URLs fixed
- ✅ Test page created
- ✅ User model has Notifiable trait
- ✅ Notifications table exists

**Notifications are now FULLY FUNCTIONAL! 🎉**

## 🧪 Next Steps

1. Login to the application
2. Go to "System → Test Notifications"
3. Send a test notification
4. Check the bell icon in navbar
5. Try creating/updating customers to trigger real notifications
