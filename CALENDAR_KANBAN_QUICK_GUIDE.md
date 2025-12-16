# 🎯 Google Calendar & Kanban - Quick Implementation Guide

## ✅ Status Saat Ini

### Kanban Board:
- ✅ **Sudah Ada** - Kanban board dengan drag & drop
- ✅ **Functional** - Bisa move customer antar status
- ✅ **Styled** - Sudah ada styling yang bagus
- 🔄 **Perlu Enhancement** - Bisa ditingkatkan ke Trello-style

### Google Calendar:
- ❌ **Belum Ada** - Perlu implementasi dari awal
- 📋 **Requires** - Google Cloud setup & OAuth

---

## 🚀 Quick Start - Kanban Enhancement

### Current Kanban Features:
✅ Drag & drop customers between statuses  
✅ Visual columns (Lead, Prospect, Customer, Inactive)  
✅ Card shows: name, email, phone, tags, follow-ups  
✅ Real-time updates with Livewire  
✅ Dark mode support  

### Suggested Enhancements (Optional):

#### 1. **Trello-Style Colors**
Current colors are good, but for more Trello-like:
- Lead: `bg-amber-50` with `border-amber-300`
- Prospect: `bg-sky-50` with `border-sky-300`
- Customer: `bg-emerald-50` with `border-emerald-300`
- Inactive: `bg-slate-50` with `border-slate-300`

#### 2. **Quick Actions on Cards**
Add buttons to cards:
- 👁️ View details
- ✏️ Edit
- 📞 Call/WhatsApp
- 📧 Email

#### 3. **Card Hover Effects**
```css
.kanban-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}
```

#### 4. **Add Card Button**
Bottom of each column:
```html
<button class="w-full py-2 text-sm text-gray-600 hover:bg-gray-100">
    + Add Customer
</button>
```

---

## 📅 Google Calendar Integration - Implementation Steps

### Prerequisites:
1. Google Cloud account
2. Google Calendar API enabled
3. OAuth 2.0 credentials

### Step-by-Step Setup:

#### 1. **Google Cloud Console**
```
1. Go to: https://console.cloud.google.com
2. Create new project: "CRM Calendar"
3. Enable APIs: Google Calendar API
4. Create credentials: OAuth 2.0 Client ID
   - Application type: Web application
   - Authorized redirect URIs: http://localhost/admin/google/callback
5. Download JSON credentials
```

#### 2. **Install Package**
```bash
composer require google/apiclient:"^2.0"
```

#### 3. **Environment Variables**
Add to `.env`:
```env
GOOGLE_CALENDAR_ENABLED=true
GOOGLE_CLIENT_ID=your-client-id-here.apps.googleusercontent.com
GOOGLE_CLIENT_SECRET=your-client-secret-here
GOOGLE_REDIRECT_URI=http://localhost/admin/google/callback
GOOGLE_CALENDAR_ID=primary
```

#### 4. **Database Migration**
```bash
php artisan make:migration add_google_calendar_fields
```

Add to `follow_ups` table:
```php
$table->string('google_event_id')->nullable();
$table->boolean('synced_to_calendar')->default(false);
```

Create `google_tokens` table:
```php
Schema::create('google_tokens', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->text('access_token');
    $table->text('refresh_token')->nullable();
    $table->timestamp('expires_at');
    $table->timestamps();
});
```

#### 5. **Run Migration**
```bash
php artisan migrate
```

---

## 📁 Files to Create for Google Calendar

### 1. **GoogleCalendarService.php**
Location: `app/Services/GoogleCalendarService.php`

Purpose: Main service to interact with Google Calendar API

Key methods:
- `getClient()` - Get authenticated Google client
- `createEvent()` - Create calendar event
- `updateEvent()` - Update calendar event
- `deleteEvent()` - Delete calendar event
- `listEvents()` - Get upcoming events

### 2. **GoogleToken Model**
Location: `app/Models/GoogleToken.php`

Purpose: Store OAuth tokens per user

### 3. **Google Auth Page**
Location: `app/Filament/Pages/GoogleAuthPage.php`

Purpose: Handle OAuth flow

Features:
- Connect Google account button
- Show connection status
- Disconnect option

### 4. **Calendar Widget**
Location: `app/Filament/Widgets/CalendarWidget.php`

Purpose: Show upcoming follow-ups on dashboard

Display:
- Today's follow-ups
- Tomorrow's follow-ups
- This week's follow-ups
- Link to full calendar

### 5. **Calendar Page**
Location: `app/Filament/Pages/CalendarPage.php`

Purpose: Full calendar view

Features:
- Month/Week/Day views
- Click to create follow-up
- Sync with Google Calendar
- Filter by user/customer

### 6. **FollowUp Observer**
Location: `app/Observers/FollowUpCalendarObserver.php`

Purpose: Auto-sync follow-ups to Google Calendar

Events:
- `created` → Create calendar event
- `updated` → Update calendar event
- `deleted` → Delete calendar event

---

## 🎨 Kanban Widget (Dashboard)

### Create Widget:
```bash
php artisan make:filament-widget KanbanWidget
```

### Features:
- Mini kanban view (4 columns)
- Show top 3 customers per status
- Quick stats (total per status)
- Link to full kanban page

### Display:
```
┌─────────────────────────────────────────┐
│  📊 Customer Pipeline                   │
├─────────┬─────────┬─────────┬───────────┤
│  LEAD   │ PROSPECT│ CUSTOMER│ INACTIVE  │
│   (12)  │   (8)   │   (25)  │   (3)     │
├─────────┼─────────┼─────────┼───────────┤
│ Card 1  │ Card 1  │ Card 1  │ Card 1    │
│ Card 2  │ Card 2  │ Card 2  │           │
│ Card 3  │ Card 3  │ Card 3  │           │
│         │         │         │           │
│ +9 more │ +5 more │ +22 more│ +2 more   │
└─────────┴─────────┴─────────┴───────────┘
│         [View Full Kanban →]            │
└─────────────────────────────────────────┘
```

---

## 🔔 Calendar Widget (Dashboard)

### Features:
- Show today's follow-ups
- Show upcoming follow-ups (7 days)
- Color-coded by type
- Click to view details
- Sync status indicator

### Display:
```
┌─────────────────────────────────────────┐
│  📅 Upcoming Follow-ups                 │
│  🔄 Synced with Google Calendar         │
├─────────────────────────────────────────┤
│  TODAY (3)                              │
│  ⏰ 10:00 - Call John Doe              │
│  ⏰ 14:00 - Meeting PT ABC             │
│  ⏰ 16:00 - Follow-up Jane             │
│                                         │
│  TOMORROW (2)                           │
│  ⏰ 09:00 - Demo XYZ Corp              │
│  ⏰ 15:00 - Check-in Client            │
│                                         │
│  THIS WEEK (8)                          │
│  📊 View all →                          │
│                                         │
│  [View Full Calendar →]                 │
└─────────────────────────────────────────┘
```

---

## 🎯 Priority Implementation

### Phase 1: Kanban Enhancements (Easy - 1-2 hours)
1. ✅ Add quick action buttons to cards
2. ✅ Improve hover effects
3. ✅ Add "Add Customer" button
4. ✅ Create Kanban Widget for dashboard

### Phase 2: Calendar Widget (Medium - 2-3 hours)
1. ✅ Create CalendarWidget
2. ✅ Show upcoming follow-ups
3. ✅ Add to dashboard
4. ✅ Link to follow-up details

### Phase 3: Google Calendar (Complex - 4-6 hours)
1. ⏳ Setup Google Cloud (user action required)
2. ⏳ Install Google API client
3. ⏳ Create GoogleCalendarService
4. ⏳ Create OAuth flow
5. ⏳ Create Calendar Page
6. ⏳ Implement auto-sync

---

## 📝 Implementation Checklist

### Kanban:
- [x] Basic kanban exists
- [ ] Add quick actions to cards
- [ ] Improve Trello-style colors
- [ ] Add hover animations
- [ ] Create Kanban Widget
- [ ] Add to dashboard

### Calendar:
- [ ] Google Cloud setup
- [ ] Install google/apiclient
- [ ] Add .env variables
- [ ] Run migrations
- [ ] Create GoogleCalendarService
- [ ] Create GoogleToken model
- [ ] Create GoogleAuthPage
- [ ] Create CalendarWidget
- [ ] Create CalendarPage
- [ ] Create FollowUpObserver
- [ ] Test OAuth flow
- [ ] Test event sync

---

## 🚨 Important Notes

### Google Calendar:
- **Requires user action** - Each user must authorize Google access
- **Rate limits** - 10,000 requests/day per project
- **OAuth tokens** - Expire after 1 hour (refresh token needed)
- **Timezone** - Must handle timezone conversions
- **Testing** - Use Google Calendar test account first

### Kanban:
- **Already functional** - Current implementation works well
- **Enhancements optional** - Current design is good
- **Performance** - Consider pagination for many customers
- **Real-time** - Uses Livewire, no polling needed

---

## 💡 Quick Wins (Can Implement Now)

### 1. **Kanban Widget** (30 minutes)
Create mini kanban for dashboard showing pipeline overview.

### 2. **Calendar Widget** (1 hour)
Show upcoming follow-ups without Google Calendar integration.

### 3. **Card Quick Actions** (30 minutes)
Add View/Edit/Call buttons to kanban cards.

### 4. **Improved Styling** (30 minutes)
Enhance kanban with better colors and animations.

---

## 🎓 Learning Resources

### Google Calendar API:
- [Official Docs](https://developers.google.com/calendar/api/guides/overview)
- [PHP Quickstart](https://developers.google.com/calendar/api/quickstart/php)
- [OAuth 2.0](https://developers.google.com/identity/protocols/oauth2)

### Filament:
- [Widgets](https://filamentphp.com/docs/3.x/panels/dashboard)
- [Custom Pages](https://filamentphp.com/docs/3.x/panels/pages)
- [Livewire](https://livewire.laravel.com/docs)

---

**Recommendation:** Start with Kanban enhancements and Calendar Widget (without Google sync) first. These provide immediate value without external dependencies. Add Google Calendar integration later when ready.

**Current Status:** Kanban is functional and good. Focus on widgets for dashboard visibility!
