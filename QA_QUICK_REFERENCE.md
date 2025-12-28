# QA Quick Reference Guide
**Quick access guide for testing the CRM application**

---

## 🚀 Quick Start

### 1. Open Application
```
URL: http://crm-app.test (or http://localhost)
```

### 2. Test Accounts
```
Super Admin:
- Email: admin@test.com
- Password: password

Sales Rep:
- Email: sales@test.com  
- Password: password

Manager:
- Email: manager@test.com
- Password: password
```

### 3. Critical Paths to Test

#### Path 1: Exhibition Kiosk (5 min)
1. Login as admin
2. Go to `/admin/exhibition-kiosk`
3. Fill visitor info
4. Check lead scoring checkboxes
5. Save
6. Verify customer created

#### Path 2: Customer Management (3 min)
1. Go to Customers
2. Create new customer
3. Edit customer
4. Verify name auto-updates
5. Delete customer

#### Path 3: Quotation (5 min)
1. Go to Quotations
2. Create new quotation
3. Add items
4. Verify calculations
5. Save

---

## 📋 Test Data

### Sample Customer Data
```
Personal Customer:
- First Name: John
- Last Name: Doe
- Email: john.doe@test.com
- Phone: +6281234567890

Company Customer:
- Company Name: PT Test Indonesia
- Email: info@test.co.id
- Phone: +6281234567891
```

### Sample Quotation Items
```
Item 1:
- Description: Product A
- Quantity: 2
- Price: 100,000

Item 2:
- Description: Service B
- Quantity: 1
- Price: 500,000
```

---

## 🐛 Common Issues & Solutions

### Issue: Login not working
**Solution**: Clear browser cache, check credentials

### Issue: Customer not saving
**Solution**: Check required fields (Name, Email, Phone)

### Issue: Quotation calculation wrong
**Solution**: Verify tax rate and discount values

### Issue: WhatsApp link not working
**Solution**: Check phone number format (+62...)

---

## ✅ Quick Checklist

### Before Testing
- [ ] Laragon running
- [ ] Database migrated
- [ ] Test users created
- [ ] Browser cache cleared

### During Testing
- [ ] Take screenshots of errors
- [ ] Note down steps to reproduce
- [ ] Check browser console for errors
- [ ] Test on different browsers

### After Testing
- [ ] Update test results
- [ ] Create bug reports
- [ ] Document findings
- [ ] Share with team

---

## 📊 Bug Report Template

```markdown
**Bug ID**: BUG-XXX
**Title**: [Short description]
**Severity**: Critical/High/Medium/Low

**Steps to Reproduce**:
1. 
2. 
3. 

**Expected**: 
**Actual**: 

**Screenshot**: [Attach]
**Browser**: Chrome/Firefox/Safari
**User Role**: Admin/Sales/Manager
```

---

## 🔗 Quick Links

### Documentation
- [Full Test Cases](QA_COMPREHENSIVE_REPORT.md)
- [Execution Checklist](QA_EXECUTION_CHECKLIST.md)
- [Code Review](QA_CODE_REVIEW_FINDINGS.md)
- [Summary Report](QA_SUMMARY_REPORT.md)

### Application URLs
- Dashboard: `/admin`
- Customers: `/admin/customers`
- Quotations: `/admin/quotations`
- Exhibition Kiosk: `/admin/exhibition-kiosk`
- Sales Toolkit: `/admin/sales-toolkit`
- Settings: `/admin/settings`

---

## 📞 Emergency Contacts

**Development Team**: [Contact]  
**QA Lead**: [Contact]  
**Project Manager**: [Contact]

---

**Last Updated**: 2025-12-28
