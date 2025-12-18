# System Analysis & Roadmap
**Date:** December 18, 2025
**Version:** 1.0

## 1. System Overview (Current State)

The system is a specialized **Sales & Marketing CRM** built on **Laravel 11** and **Filament PHP**. It is designed to bridge the gap between Lead Generation (Marketing) and Closing (Sales), with a strong focus on team hierarchy and performance tracking.

### A. Core Modules

#### 1. Sales Operations (CRM)
Focuses on the daily activities of the sales team.
*   **Customers:** Central database with segmentation (Lead, Prospect, Customer). Features "Smart Assignment" logic (Sales Rep <-> Manager).
*   **Follow-ups:** Task management for interactions (Calls, WA, Emails).
*   **Quotations:** Generating and tracking price quotes. Includes currency handling and tax/discount logic.
*   **Exhibition Kiosk:** A dedicated "POS-like" interface for fast lead capture at events, featuring automated lead scoring (BANT/Wedding logic).
*   **Price Calculator:** A utility for sales reps to quickly estimate package prices + add-ons during consultations.
*   **Sales Toolkit:** A digital asset library (brochures, scripts) for sales enablement.

#### 2. Marketing Operations
Focuses on acquisition and campaign performance.
*   **Ad Spend:** Tracking advertising costs across platforms (Meta, Google, TikTok).
*   **Exhibitions:** Management of offline events, tracking booth costs vs. revenue (ROI).
*   **UTM Builder:** Tool to standardize campaign tracking links.
*   **Integration Settings:** Centralized management of API credentials for ad platforms.

#### 3. Performance & Management
*   **KPI Targets:** Setting granular targets (Revenue, Activities, Conversion Rates) per user/period.
*   **User Management:** Role-based access (Super Admin, Country Manager, Sales Manager, Sales Rep).
*   **Offboarding:** Automated workflow to transfer assets (Leads, Tasks) from resigning employees.
*   **Security:** 2FA (TOTP) and detailed profile management.

---

## 2. Feature Roadmap

This roadmap focuses on **enhancing existing features** to maximize their value before building new modules.

### Phase 1: Polish & User Experience (Immediate)
*Goal: Remove friction for daily users.*

*   **[CRM] Customer Interaction Timeline:**
    *   *Current:* Notes are separate from Follow-ups.
    *   *Upgrade:* Create a unified timeline view on the Customer page showing notes, completed follow-ups, and status changes in chronological order.
*   **[Quotes] PDF Generation & Design:**
    *   *Current:* Basic structure exists.
    *   *Upgrade:* Implement a professional, branded PDF template for Quotations that can be downloaded or emailed directly.
*   **[Kiosk] Offline Mode:**
    *   *Current:* Web-based.
    *   *Upgrade:* Ensure the Kiosk form works well on tablets/mobile and handles flaky internet (PWA capabilities).

### Phase 2: Automation & Intelligence (Short-term)
*Goal: Reduce manual data entry.*

*   **[Marketing] Ad Spend API Sync:**
    *   *Current:* `IntegrationSettings` exists, but data entry seems manual.
    *   *Upgrade:* Create scheduled Jobs to fetch actual spend/clicks data from Meta/Google APIs using the stored credentials.
*   **[Sales] WA Blast / Automation:**
    *   *Current:* "Open WhatsApp" links.
    *   *Upgrade:* Integrate a WhatsApp Gateway (like Wablas/Fonnte) to send automated "Thank You" messages after Kiosk submission or Follow-up reminders.
*   **[Offboarding] Audit Logs:**
    *   *Current:* Transfers data.
    *   *Upgrade:* Keep a strict log of *who* transferred *what* and *when* for compliance.

### Phase 3: Analytics & Deep Reporting (Mid-term)
*Goal: Better decision making for Managers.*

*   **[KPI] Dashboard Widgets:**
    *   *Current:* Table view of targets.
    *   *Upgrade:* Visual gauges on the Dashboard showing "% to Goal" for each Sales Rep.
*   **[Marketing] True ROI Attribution:**
    *   *Current:* Basic cost vs revenue.
    *   *Upgrade:* Connect `Customer` (UTM Source) -> `Quotation` (Revenue) -> `AdSpend` (Cost) to show exactly which ad campaign is profitable (ROAS).

### Phase 4: Expansion (Long-term)
*   **ERP - Simple Invoicing:** Convert Accepted Quotations into Invoices.
*   **ERP - Payment Tracking:** Record payments against Invoices to track Outstanding Balance.

---

## 3. Technical Debt / Maintenance
*   **Testing:** Increase test coverage for critical flows (Offboarding, Quotation Calculation).
*   **Validation:** Ensure phone number formatting is strictly enforced E.164 for WhatsApp integration reliability.
