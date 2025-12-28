# QA Issues Tracker
**Track all issues found during QA testing**

---

## 📊 Issue Statistics

**Total Issues**: 15  
**Critical**: 0  
**High**: 2  
**Medium**: 5  
**Low**: 6  

**Status**:
- 🔴 Open: 11
- 🟡 In Progress: 0
- ✅ Fixed: 4
- ⏸️ Deferred: 0

**Latest Update**: 2025-12-28 20:00 - Fixed all P1, P2, and P3 issues in ExhibitionKiosk

---

## 🔴 CRITICAL ISSUES (P0)

*None found*

---

## 🟠 HIGH PRIORITY ISSUES (P1)

### ISSUE-H001: Race Condition in Duplicate Prevention
**Status**: ✅ Fixed (2025-12-28)  
**Reported**: 2025-12-28  
**Reporter**: QA Team  
**Assigned**: Development Team  
**Fixed By**: Hotfix Branch `hotfix/qa-high-priority-fixes`  
**Commit**: b69ec89

**Description**:
The `orWhere` clause in duplicate detection can match different customers if email and phone belong to different records.

**Location**: `app/Filament/Pages/ExhibitionKiosk.php:620-623`

**Impact**: High - Data integrity issues

**Steps to Reproduce**:
1. Create customer A with email: test@a.com, phone: 081111111111
2. Create customer B with email: test@b.com, phone: 082222222222
3. Try to create customer C with email: test@a.com, phone: 082222222222
4. System may update wrong customer

**Implemented Fix**:
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
    throw new \Exception(
        'Data conflict detected: Email belongs to "' . $customerByEmail->name . 
        '" but phone belongs to "' . $customerByPhone->name . 
        '". Please verify the information.'
    );
}

// Use email match first (more reliable), fallback to phone match
$customer = $customerByEmail ?? $customerByPhone;
```

**Priority**: P1  
**Target Fix Date**: 2025-12-30  
**Actual Fix Date**: 2025-12-28 ✅

---

### ISSUE-H002: Missing Validation for Required Fields
**Status**: ✅ Fixed (2025-12-28)  
**Reported**: 2025-12-28  
**Reporter**: QA Team  
**Assigned**: Development Team  
**Fixed By**: Hotfix Branch `hotfix/qa-high-priority-fixes`  
**Commit**: b69ec89

**Description**:
The `visitor_type` field is marked as required but is in a collapsed section, potentially allowing form submission without it.

**Location**: `app/Filament/Pages/ExhibitionKiosk.php:228, 526`

**Impact**: High - Incomplete data collection, incorrect lead scoring

**Steps to Reproduce**:
1. Open Exhibition Kiosk
2. Fill only basic fields (name, email, phone)
3. Don't expand Wedding Profile section
4. Try to save
5. Form may submit without visitor_type

**Implemented Fix**:
```php
public function create(): void
{
    $data = $this->form->getState();
    
    // HOTFIX: Validate critical fields for accurate lead scoring (ISSUE-H002)
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
            ->body('Please select "Wedding Timeline" for accurate quotation and lead scoring.')
            ->send();
        // Don't return - allow submission but warn user
    }
    
    // ... rest of code
}
```

**Priority**: P1  
**Target Fix Date**: 2025-12-30  
**Actual Fix Date**: 2025-12-28 ✅

---

## 🟡 MEDIUM PRIORITY ISSUES (P2)

### ISSUE-M001: Hardcoded Default Values
**Status**: ✅ Fixed (2025-12-28)
**Reported**: 2025-12-28  
**Priority**: P2  
**Location**: `app/Filament/Pages/ExhibitionKiosk.php:59, 739`

**Description**: Default WhatsApp message is hardcoded in multiple places

**Proposed Fix**: Move to config file or database setting

**Target Fix Date**: 2025-01-05
**Actual Fix Date**: 2025-12-28 ✅

---

### ISSUE-M002: Missing Error Handling for File Attachment
**Status**: ✅ Fixed (2025-12-28)
**Reported**: 2025-12-28  
**Priority**: P2  
**Location**: `app/Filament/Pages/ExhibitionKiosk.php:680-689`

**Description**: No error handling if marketing material file doesn't exist

**Proposed Fix**: Add file existence check and error logging

**Target Fix Date**: 2025-01-05
**Actual Fix Date**: 2025-12-28 ✅

---

### ISSUE-M003: Potential Memory Issue with Large Datasets
**Status**: ✅ Fixed (2025-12-28)
**Reported**: 2025-12-28  
**Priority**: P2  
**Location**: `app/Filament/Pages/ExhibitionKiosk.php:322-327`

**Description**: Loading all packages and add-ons without pagination

**Proposed Fix**: Implement pagination or caching

**Target Fix Date**: 2025-01-10
**Actual Fix Date**: 2025-12-28 ✅

---

### ISSUE-M004: SQL Injection Risk in Lock Key
**Status**: ✅ Acceptable (Add comment)  
**Reported**: 2025-12-28  
**Priority**: P2  
**Location**: `app/Filament/Pages/ExhibitionKiosk.php:612-613`

**Description**: Direct SQL usage (but safe due to parameter binding)

**Proposed Fix**: Add clarifying comment

**Target Fix Date**: 2025-01-05

---

### ISSUE-M005: Missing Transaction Rollback Logging
**Status**: ✅ Fixed (2025-12-28)
**Reported**: 2025-12-28  
**Priority**: P2  
**Location**: `app/Filament/Pages/ExhibitionKiosk.php:744-753`

**Description**: Generic error logging without context

**Proposed Fix**: Add detailed error logging with context

**Target Fix Date**: 2025-01-05
**Actual Fix Date**: 2025-12-28 ✅

---

## 🟢 LOW PRIORITY ISSUES (P3-P4)

### ISSUE-L001: Magic Numbers in Lead Scoring
**Status**: ✅ Fixed (2025-12-28)
**Priority**: P3
**Status**: ✅ Fixed (2025-12-28)
**Priority**: P3
**Target Fix Date**: 2025-01-15
**Actual Fix Date**: 2025-12-28 ✅
**Actual Fix Date**: 2025-12-28 ✅

### ISSUE-L002: Inconsistent String Formatting
**Status**: ✅ Fixed (2025-12-28)
**Priority**: P3
**Status**: ✅ Fixed (2025-12-28)
**Priority**: P3
**Target Fix Date**: 2025-01-15
**Actual Fix Date**: 2025-12-28 ✅
**Actual Fix Date**: 2025-12-28 ✅

### ISSUE-L003: Missing PHPDoc Comments
**Status**: ✅ Fixed (2025-12-28)
**Priority**: P3
**Status**: ✅ Fixed (2025-12-28)
**Priority**: P3
**Target Fix Date**: 2025-01-20
**Actual Fix Date**: 2025-12-28 ✅
**Actual Fix Date**: 2025-12-28 ✅

### ISSUE-L004: Hardcoded Strings (i18n)
**Status**: 🔴 Open  
**Priority**: P4  
**Target Fix Date**: 2025-02-01

### ISSUE-L005: Duplicate Code in Form Reset
**Status**: ✅ Fixed (2025-12-28)
**Priority**: P3
**Status**: ✅ Fixed (2025-12-28)
**Priority**: P3
**Target Fix Date**: 2025-01-15
**Actual Fix Date**: 2025-12-28 ✅
**Actual Fix Date**: 2025-12-28 ✅

### ISSUE-L006: Long Method - create()
**Status**: ✅ Fixed (2025-12-28)
**Priority**: P3
**Status**: ✅ Fixed (2025-12-28)
**Priority**: P3
**Target Fix Date**: 2025-01-20
**Actual Fix Date**: 2025-12-28 ✅
**Actual Fix Date**: 2025-12-28 ✅

### ISSUE-L007: Missing Type Hints
**Status**: 🔴 Open  
**Priority**: P4  
**Target Fix Date**: 2025-02-01

### ISSUE-L008: Potential N+1 Query
**Status**: ✅ Fixed (2025-12-28)
**Priority**: P3
**Status**: ✅ Fixed (2025-12-28)
**Priority**: P3
**Target Fix Date**: 2025-01-15
**Actual Fix Date**: 2025-12-28 ✅
**Actual Fix Date**: 2025-12-28 ✅

---

## 📋 Issue Workflow

### Status Definitions
- 🔴 **Open**: Issue identified, not yet assigned
- 🟡 **In Progress**: Developer working on fix
- 🟢 **Fixed**: Fix implemented, pending verification
- ✅ **Verified**: Fix tested and confirmed working
- ⏸️ **Deferred**: Postponed to future release
- ❌ **Won't Fix**: Decided not to fix

### Priority Definitions
- **P0 (Critical)**: Blocks release, fix immediately
- **P1 (High)**: Must fix before release
- **P2 (Medium)**: Should fix before release
- **P3 (Low)**: Nice to have, can defer
- **P4 (Future)**: Enhancement, future release

---

## 📝 How to Report New Issues

1. **Identify the issue** during testing
2. **Take screenshots** if applicable
3. **Document steps to reproduce**
4. **Assign severity and priority**
5. **Add to this tracker**
6. **Notify development team**

### Issue Template
```markdown
### ISSUE-XXX: [Title]
**Status**: 🔴 Open  
**Reported**: YYYY-MM-DD  
**Reporter**: [Name]  
**Assigned**: [Developer]  
**Priority**: P1/P2/P3/P4

**Description**: [What is the issue]

**Location**: [File:Line]

**Impact**: [How it affects users]

**Steps to Reproduce**:
1. 
2. 
3. 

**Expected Behavior**: [What should happen]

**Actual Behavior**: [What actually happens]

**Proposed Fix**: [Suggested solution]

**Target Fix Date**: YYYY-MM-DD  
**Actual Fix Date**: -
```

---

## 📊 Weekly Progress Report

### Week 1 (2025-12-28 to 2026-01-03)
- Issues Found: 15
- Issues Fixed: 0
- Issues Verified: 0
- Open Issues: 11

### Week 2 (Target)
- Target Fixes: 7 (P1 + P2)
- Target Verification: 7
- Target Remaining: 8

---

## 🎯 Fix Priority Order

### Sprint 1 (This Week)
1. ISSUE-H001 - Race condition
2. ISSUE-H002 - Missing validation
3. ISSUE-M001 - Hardcoded values
4. ISSUE-M002 - File error handling
5. ISSUE-M005 - Transaction logging

### Sprint 2 (Next Week)
6. ISSUE-M003 - Memory optimization
7. ISSUE-L001 - Magic numbers
8. ISSUE-L005 - Duplicate code
9. ISSUE-L006 - Long method refactoring (Fixed)

### Sprint 3 (Week 3)
10. ISSUE-L003 - PHPDoc comments (Fixed)
11. ISSUE-L008 - N+1 query (Fixed)
12. ISSUE-L002 - String formatting (Fixed)

### Future Sprints
13. ISSUE-L004 - Internationalization
14. ISSUE-L007 - Type hints
15. Other enhancements

---

## 📞 Escalation Process

### Level 1: Developer
- Assign issue to developer
- Developer investigates and fixes
- Developer marks as "Fixed"

### Level 2: QA Lead
- If issue not fixed in 3 days
- QA Lead reviews and escalates
- Reassign or reprioritize

### Level 3: Project Manager
- If issue blocks release
- PM decides on timeline
- May defer to future release

---

**Last Updated**: 2025-12-28  
**Next Review**: 2025-12-30  
**Maintained By**: QA Team
