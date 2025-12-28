# Hotfix: QA High Priority Issues
**Branch**: `hotfix/qa-high-priority-fixes`  
**Created**: 2025-12-28  
**Target Completion**: 2025-12-30

---

## 🎯 Objective

Fix 2 high priority issues identified during QA code review to improve data integrity and validation.

---

## 🐛 Issues to Fix

### ISSUE-H001: Race Condition in Duplicate Prevention
**Priority**: P1 (High)  
**File**: `app/Filament/Pages/ExhibitionKiosk.php`  
**Lines**: 620-623  
**Status**: 🔴 Open → 🟡 In Progress

**Problem**:
```php
// Current code uses orWhere which can match different customers
$customer = Customer::where('email', $data['email'])
    ->orWhere('phone', $data['phone'])
    ->lockForUpdate()
    ->first();
```

**Issue**: If email and phone belong to different customer records, the query may update the wrong customer.

**Solution**:
```php
// Check email and phone separately
$customerByEmail = Customer::where('email', $data['email'])
    ->lockForUpdate()
    ->first();
    
$customerByPhone = Customer::where('phone', $data['phone'])
    ->lockForUpdate()
    ->first();

// Handle conflict
if ($customerByEmail && $customerByPhone && $customerByEmail->id !== $customerByPhone->id) {
    throw new \Exception('Email and phone number belong to different customers. Please verify the information.');
}

$customer = $customerByEmail ?? $customerByPhone;
```

**Impact**: 
- ✅ Prevents updating wrong customer record
- ✅ Improves data integrity
- ✅ Clear error message for users

---

### ISSUE-H002: Missing Validation for Required Fields
**Priority**: P1 (High)  
**File**: `app/Filament/Pages/ExhibitionKiosk.php`  
**Lines**: 526-530  
**Status**: 🔴 Open → 🟡 In Progress

**Problem**:
The `visitor_type` field is marked as required in the form but is in a collapsed section. Users might submit without filling it, causing incomplete lead scoring.

**Solution**:
Add backend validation in the `create()` method:

```php
public function create(): void
{
    $data = $this->form->getState();
    
    // Validate critical fields for lead scoring
    $requiredFields = [
        'visitor_type' => 'Visitor Type',
        'wedding_timeline' => 'Wedding Timeline',
    ];
    
    foreach ($requiredFields as $field => $label) {
        if (empty($data[$field])) {
            Notification::make()
                ->danger()
                ->title('Validation Error')
                ->body("Please select {$label} to continue. This helps us serve you better.")
                ->send();
            return;
        }
    }
    
    // ... rest of code
}
```

**Impact**:
- ✅ Ensures complete data collection
- ✅ Accurate lead scoring
- ✅ Better user guidance

---

## 📋 Implementation Checklist

### Phase 1: Code Changes
- [ ] Fix ISSUE-H001 in ExhibitionKiosk.php
- [ ] Fix ISSUE-H002 in ExhibitionKiosk.php
- [ ] Add code comments for clarity
- [ ] Update error messages

### Phase 2: Testing
- [ ] Test duplicate detection with same email
- [ ] Test duplicate detection with same phone
- [ ] Test duplicate detection with email+phone conflict
- [ ] Test validation for visitor_type
- [ ] Test validation for wedding_timeline
- [ ] Test form submission with all fields

### Phase 3: Documentation
- [ ] Update QA_ISSUES_TRACKER.md
- [ ] Update QA_CODE_REVIEW_FINDINGS.md
- [ ] Add comments in code
- [ ] Update CHANGELOG.md

### Phase 4: Review & Merge
- [ ] Self-review code changes
- [ ] Run automated tests
- [ ] Manual testing
- [ ] Create pull request
- [ ] Code review by team
- [ ] Merge to main

---

## 🧪 Test Cases

### Test Case 1: Duplicate Email Detection
```
Steps:
1. Create customer A: email=test@a.com, phone=081111111111
2. Try to create customer B: email=test@a.com, phone=082222222222
Expected: Update customer A, not create new
```

### Test Case 2: Duplicate Phone Detection
```
Steps:
1. Create customer A: email=test@a.com, phone=081111111111
2. Try to create customer B: email=test@b.com, phone=081111111111
Expected: Update customer A, not create new
```

### Test Case 3: Email+Phone Conflict
```
Steps:
1. Create customer A: email=test@a.com, phone=081111111111
2. Create customer B: email=test@b.com, phone=082222222222
3. Try to create customer C: email=test@a.com, phone=082222222222
Expected: Error message about conflict
```

### Test Case 4: Missing Visitor Type
```
Steps:
1. Open Exhibition Kiosk
2. Fill name, email, phone only
3. Don't expand Wedding Profile section
4. Try to save
Expected: Validation error asking for Visitor Type
```

### Test Case 5: Complete Form Submission
```
Steps:
1. Fill all required fields including visitor_type
2. Save
Expected: Success, customer created with correct lead score
```

---

## 📝 Code Changes Summary

### File: `app/Filament/Pages/ExhibitionKiosk.php`

#### Change 1: Improved Duplicate Detection (Lines ~620-625)
**Before**:
```php
$customer = Customer::where('email', $data['email'])
    ->orWhere('phone', $data['phone'])
    ->lockForUpdate()
    ->first();
```

**After**:
```php
// Separate checks for email and phone to prevent wrong customer updates
$customerByEmail = Customer::where('email', $data['email'])
    ->lockForUpdate()
    ->first();
    
$customerByPhone = Customer::where('phone', $data['phone'])
    ->lockForUpdate()
    ->first();

// Detect conflict: same email and phone exist but on different records
if ($customerByEmail && $customerByPhone && $customerByEmail->id !== $customerByPhone->id) {
    throw new \Exception('Email and phone number belong to different customers. Please verify the information.');
}

// Use email match first, fallback to phone match
$customer = $customerByEmail ?? $customerByPhone;
```

#### Change 2: Backend Validation (Lines ~528-540)
**Before**:
```php
public function create(): void
{
    $data = $this->form->getState();
    
    // Calculate Weighted Score
    $score = 0;
    // ...
```

**After**:
```php
public function create(): void
{
    $data = $this->form->getState();
    
    // Validate critical fields for accurate lead scoring
    if (empty($data['visitor_type'])) {
        Notification::make()
            ->danger()
            ->title('Validation Error')
            ->body('Please select "Who Visited" to continue. This helps us provide better service.')
            ->persistent()
            ->send();
        return;
    }
    
    if (empty($data['wedding_timeline'])) {
        Notification::make()
            ->warning()
            ->title('Missing Information')
            ->body('Please select "Wedding Timeline" for accurate quotation.')
            ->send();
        // Don't return - allow submission but warn
    }
    
    // Calculate Weighted Score
    $score = 0;
    // ...
```

---

## 🔄 Rollback Plan

If issues arise after deployment:

### Quick Rollback
```bash
# Revert to previous version
git checkout main
git pull origin main

# Or revert specific commit
git revert <commit-hash>
```

### Database Rollback
No database changes in this hotfix, so no migration rollback needed.

---

## 📊 Success Criteria

- [x] Branch created: `hotfix/qa-high-priority-fixes`
- [ ] Both high priority issues fixed
- [ ] All test cases passing
- [ ] Code reviewed and approved
- [ ] No new bugs introduced
- [ ] Documentation updated
- [ ] Merged to main

---

## 🚀 Deployment Steps

### 1. After Merge to Main
```bash
# Pull latest
git checkout main
git pull origin main

# Deploy to staging
php artisan config:clear
php artisan cache:clear
php artisan migrate --force

# Run tests
php artisan test
```

### 2. Production Deployment
```bash
# Same as staging
# Monitor logs for errors
tail -f storage/logs/laravel.log
```

---

## 📞 Contacts

**Developer**: [Assign Developer]  
**QA Tester**: [Assign QA]  
**Reviewer**: [Assign Reviewer]  

---

## 📅 Timeline

| Date | Activity | Status |
|------|----------|--------|
| 2025-12-28 | Branch created | ✅ Done |
| 2025-12-28 | Code changes | 🟡 In Progress |
| 2025-12-29 | Testing | ⏳ Pending |
| 2025-12-29 | Code review | ⏳ Pending |
| 2025-12-30 | Merge & Deploy | ⏳ Pending |

---

**Status**: 🟡 IN PROGRESS  
**Last Updated**: 2025-12-28 16:42 WIB  
**Next Action**: Implement code fixes
