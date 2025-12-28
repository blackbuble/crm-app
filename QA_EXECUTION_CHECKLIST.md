# QA Execution Checklist - CRM Application
**Testing Date**: 2025-12-28  
**Tester**: Development Team

---

## 🚀 Quick Start Guide

### Prerequisites
1. ✅ Laragon running
2. ✅ Database configured
3. ✅ Application accessible at `http://crm-app.test` or `http://localhost`
4. ✅ Test users created with different roles

### Test User Accounts
Create these test accounts before starting:

```bash
# Super Admin
Email: admin@test.com
Password: password

# Manager
Email: manager@test.com
Password: password

# Sales Rep
Email: sales@test.com
Password: password

# Limited User
Email: user@test.com
Password: password
```

---

## 📋 PHASE 1: Critical Path Testing (Priority 1)

### ✅ 1.1 Login & Authentication
- [ ] Open browser to application URL
- [ ] Login with Super Admin credentials
- [ ] Verify redirect to dashboard
- [ ] Check dashboard loads without errors
- [ ] Logout and verify redirect to login
- **Status**: ⏳ **Result**: _____

### ✅ 1.2 Exhibition Kiosk - Basic Flow
- [ ] Navigate to `/admin/exhibition-kiosk`
- [ ] Fill form:
  - Visitor Name: "QA Test Lead 1"
  - Phone: "+6281234567890"
  - Email: "qatest1@example.com"
- [ ] Click Save
- [ ] Verify success notification
- [ ] Navigate to Customers
- [ ] Verify customer "QA Test Lead 1" exists
- **Status**: ⏳ **Result**: _____

### ✅ 1.3 Exhibition Kiosk - Lead Scoring
- [ ] Open Exhibition Kiosk
- [ ] Fill basic info (Visitor Name, Phone)
- [ ] Check "Decision Maker" checkbox
- [ ] Observe score increase
- [ ] Check "Has Budget" checkbox
- [ ] Observe score increase again
- [ ] Check "Request Quotation" checkbox
- [ ] Verify score is 60%+ and label shows "Gold" or "Hot"
- **Status**: ⏳ **Result**: _____

### ✅ 1.4 Exhibition Kiosk - Duplicate Prevention
- [ ] Create lead with email: "duplicate@test.com"
- [ ] Save successfully
- [ ] Open kiosk again (or new tab)
- [ ] Create another lead with SAME email: "duplicate@test.com"
- [ ] Use different name
- [ ] Save
- [ ] Navigate to Customers
- [ ] Search for "duplicate@test.com"
- [ ] Verify ONLY ONE customer exists (not two)
- **Status**: ⏳ **Result**: _____

### ✅ 1.5 Customer Management - Create Personal
- [ ] Navigate to Customers > Create
- [ ] Select Type: Personal
- [ ] Fill:
  - First Name: "Budi"
  - Last Name: "Santoso"
  - Email: "budi.santoso@test.com"
  - Phone: "+6281234567891"
- [ ] Save
- [ ] Verify Name field auto-filled as "Budi Santoso"
- **Status**: ⏳ **Result**: _____

### ✅ 1.6 Customer Management - Update Name (Observer)
- [ ] Edit customer "Budi Santoso"
- [ ] Change Last Name to "Widodo"
- [ ] Save
- [ ] Verify Name auto-updates to "Budi Widodo"
- **Status**: ⏳ **Result**: _____

### ✅ 1.7 Customer Assignment & Notification
- [ ] Create new customer
- [ ] Assign to "Sales Rep" user
- [ ] Save
- [ ] Login as Sales Rep
- [ ] Check bell icon for notification
- [ ] Verify notification received
- **Status**: ⏳ **Result**: _____

### ✅ 1.8 Quotation - Basic Creation
- [ ] Navigate to Quotations > Create
- [ ] Select a customer
- [ ] Verify Quotation Number auto-generated (format: Q-YYYY-XXXX)
- [ ] Add item:
  - Description: "Product A"
  - Quantity: 2
  - Unit Price: 100,000
- [ ] Verify Subtotal = 200,000
- [ ] Save
- **Status**: ⏳ **Result**: _____

### ✅ 1.9 Quotation - Tax Calculation
- [ ] Edit quotation
- [ ] Add Tax Rate: 11%
- [ ] Verify Tax Amount calculated correctly
- [ ] Verify Grand Total = Subtotal + Tax
- [ ] Save
- **Status**: ⏳ **Result**: _____

---

## 📋 PHASE 2: Feature Testing (Priority 2)

### ✅ 2.1 Marketing Materials - Upload
- [ ] Navigate to Marketing Materials
- [ ] Click Create
- [ ] Fill:
  - Title: "QA Test Brochure"
  - Type: Brochure
  - Upload a PDF file
- [ ] Save
- [ ] Verify file uploaded successfully
- **Status**: ⏳ **Result**: _____

### ✅ 2.2 Sales Toolkit - View Materials
- [ ] Navigate to Sales Toolkit page
- [ ] Verify materials displayed
- [ ] Click "Brochure" filter
- [ ] Verify only brochures shown
- [ ] Click download button
- [ ] Verify file downloads
- **Status**: ⏳ **Result**: _____

### ✅ 2.3 WhatsApp Integration - Template
- [ ] Navigate to WA Templates
- [ ] Create new template:
  - Name: "greeting"
  - Content: "Hello {{name}}, welcome!"
- [ ] Save
- **Status**: ⏳ **Result**: _____

### ✅ 2.4 WhatsApp - Send from Kiosk
- [ ] Open Exhibition Kiosk
- [ ] Fill visitor info
- [ ] Check "Send Instant WA"
- [ ] Select a Price List from dropdown
- [ ] Save
- [ ] Verify notification appears
- [ ] Click "Open WhatsApp" button
- [ ] Verify wa.me link opens with correct message
- **Status**: ⏳ **Result**: _____

### ✅ 2.5 Pricing Configuration - Create Package
- [ ] Navigate to Pricing Config
- [ ] Create new:
  - Name: "Basic Package"
  - Price: 5,000,000
  - Type: Package
- [ ] Save
- **Status**: ⏳ **Result**: _____

### ✅ 2.6 Pricing Configuration - Create Add-on
- [ ] Create new:
  - Name: "Extra Feature"
  - Price: 500,000
  - Type: Add-on
- [ ] Save
- **Status**: ⏳ **Result**: _____

### ✅ 2.7 Kiosk - Package Selection
- [ ] Open Exhibition Kiosk
- [ ] Scroll to Package section
- [ ] Verify packages loaded from database (not hardcoded)
- [ ] Select "Basic Package"
- [ ] Select "Extra Feature" add-on
- [ ] Verify price calculation updates
- [ ] Verify estimation shown correctly
- **Status**: ⏳ **Result**: _____

### ✅ 2.8 Follow-up - Create Task
- [ ] Navigate to Follow-ups > Create
- [ ] Select a customer
- [ ] Set Due Date: Tomorrow
- [ ] Set Priority: High
- [ ] Add Notes: "QA Test Follow-up"
- [ ] Save
- **Status**: ⏳ **Result**: _____

### ✅ 2.9 Follow-up - Update Status
- [ ] Edit follow-up task
- [ ] Change Status to "Completed"
- [ ] Save
- [ ] Verify status updated
- **Status**: ⏳ **Result**: _____

### ✅ 2.10 Storage Settings - Configure
- [ ] Navigate to Settings > Storage Configuration
- [ ] Verify current driver shown
- [ ] Change driver (Local ↔ S3)
- [ ] Fill required fields
- [ ] Save
- [ ] Refresh page
- [ ] Verify settings persisted
- **Status**: ⏳ **Result**: _____

---

## 📋 PHASE 3: Permission & Security Testing (Priority 3)

### ✅ 3.1 Role-Based Access - Super Admin
- [ ] Login as Super Admin
- [ ] Verify access to ALL menu items
- [ ] Verify can view all customers
- [ ] Verify can edit all resources
- **Status**: ⏳ **Result**: _____

### ✅ 3.2 Role-Based Access - Sales Rep
- [ ] Login as Sales Rep
- [ ] Verify limited menu access
- [ ] Navigate to Customers
- [ ] Verify only sees assigned customers
- [ ] Try to access another user's customer
- [ ] Verify access denied
- **Status**: ⏳ **Result**: _____

### ✅ 3.3 Shield Permissions - Widget Visibility
- [ ] Login as Super Admin
- [ ] Navigate to Shield > Roles
- [ ] Edit "Sales Rep" role
- [ ] Uncheck a widget permission
- [ ] Save
- [ ] Login as Sales Rep
- [ ] Verify widget is hidden on dashboard
- **Status**: ⏳ **Result**: _____

### ✅ 3.4 Shield Permissions - Resource Access
- [ ] As Super Admin, edit role permissions
- [ ] Remove "view_any_marketing_material" from Sales Rep
- [ ] Save
- [ ] Login as Sales Rep
- [ ] Verify "Sales Toolkit" menu hidden
- **Status**: ⏳ **Result**: _____

### ✅ 3.5 Shield Permissions - Action Buttons
- [ ] Remove "delete_customer" permission from role
- [ ] Login as that role
- [ ] Navigate to Customers
- [ ] Verify delete button hidden/disabled
- **Status**: ⏳ **Result**: _____

---

## 📋 PHASE 4: Integration Testing (Priority 4)

### ✅ 4.1 Customer Import - Valid Data
- [ ] Prepare Excel file with valid customer data
- [ ] Navigate to Customers
- [ ] Click Import
- [ ] Upload file
- [ ] Map columns
- [ ] Import
- [ ] Verify all customers imported
- [ ] Check for duplicates
- **Status**: ⏳ **Result**: _____

### ✅ 4.2 Customer Import - Invalid Data
- [ ] Prepare Excel with invalid email format
- [ ] Try to import
- [ ] Verify validation errors shown
- [ ] Verify no invalid data imported
- **Status**: ⏳ **Result**: _____

### ✅ 4.3 Customer Export
- [ ] Navigate to Customers
- [ ] Click Export
- [ ] Verify Excel file downloads
- [ ] Open file
- [ ] Verify data correct
- **Status**: ⏳ **Result**: _____

### ✅ 4.4 Notification System - Assignment
- [ ] Create customer assigned to User A
- [ ] Login as User A
- [ ] Check notifications
- [ ] Verify notification received
- [ ] Click notification
- [ ] Verify redirects to customer
- **Status**: ⏳ **Result**: _____

### ✅ 4.5 Notification System - Re-assignment
- [ ] Edit customer
- [ ] Change assignment from User A to User B
- [ ] Save
- [ ] Login as User A
- [ ] Verify notification about re-assignment
- [ ] Login as User B
- [ ] Verify notification about new assignment
- **Status**: ⏳ **Result**: _____

### ✅ 4.6 Notification Persistence
- [ ] Receive a notification
- [ ] Logout
- [ ] Login again
- [ ] Verify notification still visible (if unread)
- **Status**: ⏳ **Result**: _____

### ✅ 4.7 Calendar View - Follow-ups
- [ ] Navigate to Calendar
- [ ] Verify follow-ups displayed on correct dates
- [ ] Click on a date
- [ ] Verify can create follow-up from calendar
- **Status**: ⏳ **Result**: _____

### ✅ 4.8 Kanban Board - View
- [ ] Navigate to Customer Kanban
- [ ] Verify Trello-style board displayed
- [ ] Verify customers in correct stages
- **Status**: ⏳ **Result**: _____

### ✅ 4.9 Kanban Board - Drag & Drop
- [ ] Drag a customer card to different stage
- [ ] Verify card moves
- [ ] Refresh page
- [ ] Verify stage persisted in database
- **Status**: ⏳ **Result**: _____

---

## 📋 PHASE 5: UI/UX & Edge Cases (Priority 5)

### ✅ 5.1 Responsive Design - Mobile
- [ ] Open application on mobile device or resize browser
- [ ] Navigate through main pages
- [ ] Verify layout responsive
- [ ] Verify no horizontal scroll
- [ ] Verify buttons accessible
- **Status**: ⏳ **Result**: _____

### ✅ 5.2 Form Validation - Empty Required Fields
- [ ] Try to create customer without required fields
- [ ] Verify validation errors shown
- [ ] Verify error messages clear
- [ ] Verify no data saved
- **Status**: ⏳ **Result**: _____

### ✅ 5.3 Form Validation - Invalid Email
- [ ] Enter invalid email format
- [ ] Try to save
- [ ] Verify validation error
- **Status**: ⏳ **Result**: _____

### ✅ 5.4 Form Validation - Invalid Phone
- [ ] Enter invalid phone format
- [ ] Try to save
- [ ] Verify validation error
- **Status**: ⏳ **Result**: _____

### ✅ 5.5 Error Handling - 404 Page
- [ ] Navigate to non-existent URL
- [ ] Verify 404 page shown
- [ ] Verify can navigate back
- **Status**: ⏳ **Result**: _____

### ✅ 5.6 Error Handling - 403 Forbidden
- [ ] As limited user, try to access forbidden resource
- [ ] Verify 403 error or redirect
- [ ] Verify appropriate message shown
- **Status**: ⏳ **Result**: _____

### ✅ 5.7 Performance - Large Dataset
- [ ] Import 100+ customers
- [ ] Navigate to customer list
- [ ] Verify page loads in reasonable time (< 3s)
- [ ] Test pagination
- [ ] Test search
- **Status**: ⏳ **Result**: _____

### ✅ 5.8 Search Functionality
- [ ] Navigate to Customers
- [ ] Use search box
- [ ] Search by name
- [ ] Verify results correct
- [ ] Search by email
- [ ] Verify results correct
- **Status**: ⏳ **Result**: _____

### ✅ 5.9 Filters
- [ ] Apply filter (e.g., Status = Lead)
- [ ] Verify only leads shown
- [ ] Combine multiple filters
- [ ] Verify results correct
- **Status**: ⏳ **Result**: _____

### ✅ 5.10 Sorting
- [ ] Click column header to sort
- [ ] Verify ascending sort
- [ ] Click again
- [ ] Verify descending sort
- **Status**: ⏳ **Result**: _____

---

## 📋 PHASE 6: Database & Technical Checks

### ✅ 6.1 Database Migrations
```bash
# Check migration status
php artisan migrate:status
```
- [ ] Verify all migrations run
- [ ] Verify no pending migrations
- **Status**: ⏳ **Result**: _____

### ✅ 6.2 Database - WA Templates Table
- [ ] Check database
- [ ] Verify `wa_templates` table exists
- [ ] Verify columns correct
- **Status**: ⏳ **Result**: _____

### ✅ 6.3 Database - Pricing Config Table
- [ ] Verify `pricing_configs` table exists
- [ ] Verify columns correct
- [ ] Verify sample data exists
- **Status**: ⏳ **Result**: _____

### ✅ 6.4 Storage - Local Files
- [ ] Configure Local storage
- [ ] Upload a file
- [ ] Check `storage/app/public` directory
- [ ] Verify file exists
- **Status**: ⏳ **Result**: _____

### ✅ 6.5 Storage - S3/R2 Files (if configured)
- [ ] Configure S3/R2 storage
- [ ] Upload a file
- [ ] Check S3/R2 bucket
- [ ] Verify file uploaded
- **Status**: ⏳ **Result**: _____

### ✅ 6.6 Logs - Error Checking
- [ ] Check `storage/logs/laravel.log`
- [ ] Verify no critical errors
- [ ] Check for warnings
- **Status**: ⏳ **Result**: _____

---

## 📊 Test Results Summary

### Statistics
- **Total Tests**: 75+
- **Passed**: ___
- **Failed**: ___
- **Blocked**: ___
- **Skipped**: ___

### Critical Issues Found
1. _____
2. _____
3. _____

### High Priority Issues
1. _____
2. _____
3. _____

### Medium Priority Issues
1. _____
2. _____

### Low Priority Issues
1. _____

---

## 🐛 Bug Report Template

When you find a bug, document it like this:

```
**Bug ID**: BUG-001
**Title**: [Short description]
**Severity**: Critical / High / Medium / Low
**Priority**: P1 / P2 / P3 / P4

**Steps to Reproduce**:
1. 
2. 
3. 

**Expected Result**:


**Actual Result**:


**Screenshots**: [Attach if applicable]

**Environment**:
- Browser: 
- OS: 
- User Role: 

**Additional Notes**:

```

---

## ✅ Sign-off

### QA Tester
- **Name**: _____
- **Date**: _____
- **Signature**: _____

### Project Manager
- **Name**: _____
- **Date**: _____
- **Signature**: _____

---

**Document Version**: 1.0  
**Last Updated**: 2025-12-28
