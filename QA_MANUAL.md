# QA Manual Testing Checklist

## 0. Automated Tests (Auto QA)
Before performing manual tests, run the automated test suite to verify core logic:

```bash
php artisan test
```

This will run:
*   **Customer Logic**: Verifies naming conventions and creation.
*   **Customer Form**: Verifies the Filament/Livewire form works correctly (validation, submission).
*   **Storage Settings**: Verifies settings persistence.

If these fail, do not proceed to manual testing as the core logic is broken.

## 1. Exhibition Kiosk (Quick Lead Entry)
- [ ] **Access**: Navigate to `/admin/exhibition-kiosk`.
- [ ] **Validation**: Try to save without `Visitor Name`. Should show error.
- [ ] **Lead Scoring**:
    - [ ] Check "Decision Maker", "Has Budget", "Request Quotation".
    - [ ] Verify score increases (e.g., > 60%).
    - [ ] Verify "Gold/Hot/Potential" label changes dynamically.
- [ ] **Duplicate Prevention**:
    - [ ] Enter a lead with Email `test@duplicate.com`. Save.
    - [ ] Open a new tab (or refresh), enter the SAME email `test@duplicate.com` with a *different* name.
    - [ ] Save.
    - [ ] **Expected**: Only ONE customer record exists (the original or updated), NOT two. The system should update the existing record or notify.
- [ ] **WhatsApp**:
    - [ ] Check "Send Instant WA".
    - [ ] Select a Price List from the "Attach" dropdown.
    - [ ] Click Save.
    - [ ] **Expected**: A notification appears with "Open WhatsApp" button. Clicking it opens `wa.me` with the correct text + file link.

## 2. Sales Toolkit (Marketing Materials)
- [ ] **Admin**: Go to Sales Toolkit > Materials.
- [ ] **Upload**: Create new Material (Type: Brochure). Upload a PDF. Save.
- [ ] **Viewer**: Go to "Sales Toolkit" page.
- [ ] **Filter**: Click "Brochure". Ensure your file appears.
- [ ] **Access**: 
    - [ ] Login as a user *without* `view_any_marketing_material` permission.
    - [ ] Ensure "Sales Toolkit" menu is hidden.

## 3. Storage Configuration
- [ ] **Settings**: Go to Settings > Storage Configuration.
- [ ] **Switch Driver**: Change "Active Disk" to `S3 / Compatible`.
- [ ] **Defaults**: Ensure fields like "Region" have defaults (e.g., `us-east-1`).
- [ ] **Save**: Enter dummy data (Bucket: `test-bucket`). Save.
- [ ] **Reload**: Refresh page. Ensure "S3" is still selected and data persists.

## 4. Customer Management
- [ ] **Observer Logic**:
    - [ ] Create a "Personal" customer with First Name "Budi", Last Name "Santoso".
    - [ ] Field `Name` should auto-fill to "Budi Santoso".
    - [ ] Update Last Name to "Widodo". 
    - [ ] Field `Name` should update to "Budi Widodo".
- [ ] **Re-assignment**:
    - [ ] Create customer assigned to User A.
    - [ ] Edit customer, change "Assigned To" to User B.
    - [ ] **Expected**: User A and User B (and/or Manager) receive a notification (Bell icon).

## 5. Quotations
- [ ] **Create**: Go to Quotations > Create.
- [ ] **Number**: Verify "Quotation Number" is auto-filled (e.g., `Q-202X-XXXX`).
- [ ] **Items**: Add item, Qty: 2, Price: 100,000.
- [ ] **Calculation**: Verify Subtotal = 200,000.
- [ ] **Tax**: add Tax 11%. Verify Grand Total calculation is correct.
