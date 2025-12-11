# Shield Widget Permissions - Important Note

## ⚠️ CRITICAL ISSUE

### Problem
Widgets dengan custom `canView()` method akan **OVERRIDE** Shield permissions.

### Current Situation

**Widgets WITH canView():**
- KpiWidget → `return auth()->user()->hasAnyRole([...])` 
- SalesRepStatsWidget → `return auth()->user()->hasRole('sales_rep')`
- TeamPerformanceWidget → `return auth()->user()->hasAnyRole([...])`

**Result:** Shield permissions di database **DIABAIKAN** ❌

**Widgets WITHOUT canView():**
- CustomerStatsWidget → No canView()
- CustomerChartWidget → No canView()
- CustomerStatusWidget → No canView()
- MyCustomersWidget → No canView()
- RecentCustomersWidget → No canView()

**Result:** Shield permissions **BEKERJA** ✅

---

## 🔍 Why This Happens

### Filament Widget Visibility Flow:
```
1. Check canView() method
   ↓
2. If canView() returns false → HIDE widget
   ↓
3. If canView() returns true → SHOW widget
   ↓
4. Shield permissions NOT checked automatically
```

### The Problem:
```php
public static function canView(): bool
{
    return auth()->user()->hasRole('sales_rep');
}
```

This **ALWAYS returns true** for sales_rep, regardless of Shield permissions!

---

## ✅ Solution Options

### Option 1: Remove ALL canView() Methods (Recommended)
**Let Shield handle everything**

```php
class CustomerStatsWidget extends BaseWidget
{
    // NO canView() method
    // Shield will control visibility
}
```

**Pros:**
- ✅ Shield permissions work perfectly
- ✅ Simple, no custom code
- ✅ Easy to manage

**Cons:**
- ❌ Cannot have role-specific widgets
- ❌ All widgets available to all roles (unless unchecked in Shield)

---

### Option 2: Explicitly Check Shield Permissions
**Manually check Shield in canView()**

```php
public static function canView(): bool
{
    $user = auth()->user();
    
    // Check role first
    if (!$user->hasRole('sales_rep')) {
        return false;
    }
    
    // Check Shield permission
    $widgetClass = static::class;
    return $user->can('view_' . $widgetClass);
}
```

**Pros:**
- ✅ Role restrictions work
- ✅ Shield permissions work

**Cons:**
- ❌ Complex
- ❌ Need to know Shield permission naming
- ❌ More code to maintain

---

### Option 3: Use Shield's Built-in Method
**Use Shield's hasPermissionTo()**

```php
public static function canView(): bool
{
    $user = auth()->user();
    
    // Check role
    if (!$user->hasRole('sales_rep')) {
        return false;
    }
    
    // Check Shield permission
    return $user->can('view_widget::' . static::class);
}
```

**Note:** Need to verify exact permission name format Shield uses.

---

## 🎯 Recommended Approach

### For Most Widgets:
**Remove canView() completely**

```php
class CustomerStatsWidget extends BaseWidget
{
    protected ?string $heading = 'Customer Statistics';
    
    // NO canView() - let Shield handle it
    
    protected function getStats(): array
    {
        // ...
    }
}
```

### For Role-Specific Widgets:
**Keep role check ONLY if widget should NEVER be visible to certain roles**

Example: SalesRepStatsWidget should NEVER be visible to managers:

```php
class SalesRepStatsWidget extends BaseWidget
{
    public static function canView(): bool
    {
        // Hard requirement: must be sales_rep
        // This cannot be overridden by Shield
        return auth()->user()->hasRole('sales_rep');
    }
}
```

**Trade-off:** Shield cannot control this widget for sales_rep (always visible).

---

## 📊 Current Widget Status

| Widget | Has canView() | Shield Works? | Recommendation |
|--------|---------------|---------------|----------------|
| CustomerStatsWidget | ❌ No | ✅ Yes | Keep as is |
| CustomerChartWidget | ❌ No | ✅ Yes | Keep as is |
| CustomerStatusWidget | ❌ No | ✅ Yes | Keep as is |
| MyCustomersWidget | ❌ No | ✅ Yes | Keep as is |
| RecentCustomersWidget | ❌ No | ✅ Yes | Keep as is |
| **KpiWidget** | ✅ Yes | ❌ No | **Remove canView()** |
| **SalesRepStatsWidget** | ✅ Yes | ❌ No | **Keep (role-specific)** |
| **TeamPerformanceWidget** | ✅ Yes | ❌ No | **Remove canView()** |

---

## 🔧 Proposed Changes

### 1. Remove canView() from KpiWidget
```php
class KpiWidget extends BaseWidget
{
    protected static ?int $sort = 0;
    protected ?string $heading = 'Key Performance Indicators';
    
    // REMOVE canView() - let Shield handle it
    
    protected function getStats(): array
    {
        // ...
    }
}
```

**Result:** 
- All roles can see it by default
- Shield can control visibility per role
- Uncheck in Shield = hidden

---

### 2. Remove canView() from TeamPerformanceWidget
```php
class TeamPerformanceWidget extends BaseWidget
{
    protected static ?int $sort = 1;
    protected ?string $heading = 'Team Performance';
    
    // REMOVE canView() - let Shield handle it
    
    public function table(Table $table): Table
    {
        // ...
    }
}
```

**Result:**
- All roles can see it by default
- Shield can control visibility per role

---

### 3. Keep canView() in SalesRepStatsWidget
```php
class SalesRepStatsWidget extends BaseWidget
{
    protected static ?int $sort = 0;
    protected ?string $heading = 'My Performance';
    
    // KEEP - This is role-specific
    public static function canView(): bool
    {
        return auth()->user()->hasRole('sales_rep');
    }
}
```

**Result:**
- Only sales_rep can see
- Shield cannot override (always visible for sales_rep)
- This is acceptable for personal stats widget

---

## ✅ Action Items

1. **Remove canView() from KpiWidget** ✅
2. **Remove canView() from TeamPerformanceWidget** ✅
3. **Keep canView() in SalesRepStatsWidget** (role-specific)
4. **Test Shield permissions** after changes

---

## 🧪 Testing After Changes

### Test 1: Shield Control Works
```
1. Login as super_admin
2. Go to Shield → Roles → super_admin
3. Uncheck "KPI Widget"
4. Save
5. Go to Dashboard
6. KPI Widget should be HIDDEN ✅
```

### Test 2: Role-Specific Still Works
```
1. Login as sales_manager
2. Go to Dashboard
3. "My Performance" widget should NOT appear ✅
   (because it's only for sales_rep)
```

### Test 3: All Widgets Controllable
```
1. Login as any role
2. Go to Shield → Roles → [role]
3. Uncheck any widget
4. Widget should disappear from dashboard ✅
```

---

## 📝 Summary

**Problem:** canView() overrides Shield permissions

**Solution:** Remove canView() from most widgets

**Exception:** Keep canView() for truly role-specific widgets

**Result:** Shield permissions will work correctly

---

**Next Step:** Remove canView() from KpiWidget and TeamPerformanceWidget?
