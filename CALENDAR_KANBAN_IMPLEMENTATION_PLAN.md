# Google Calendar & Trello-Style Kanban Implementation Plan

## 📋 Overview

Implementasi 2 fitur besar:
1. **Google Calendar Integration** - Sync follow-up reminders
2. **Trello-Style Kanban Board** - Visual customer pipeline

---

## 🗓️ Part 1: Google Calendar Integration

### Features:
- ✅ Sync follow-ups to Google Calendar
- ✅ Auto-create calendar events
- ✅ Reminder notifications
- ✅ Two-way sync (optional)
- ✅ Calendar widget on dashboard
- ✅ Dedicated calendar page

### Implementation Steps:

#### Step 1: Install Google Calendar Package
```bash
composer require google/apiclient
```

#### Step 2: Google Cloud Setup
1. Go to [Google Cloud Console](https://console.cloud.google.com)
2. Create new project: "CRM Calendar Integration"
3. Enable Google Calendar API
4. Create OAuth 2.0 credentials
5. Download credentials JSON
6. Add to `.env`:
```env
GOOGLE_CALENDAR_ENABLED=true
GOOGLE_CLIENT_ID=your-client-id
GOOGLE_CLIENT_SECRET=your-client-secret
GOOGLE_REDIRECT_URI=http://localhost/admin/google/callback
GOOGLE_CALENDAR_ID=primary
```

#### Step 3: Database Migration
```php
// Add google_event_id to follow_ups table
Schema::table('follow_ups', function (Blueprint $table) {
    $table->string('google_event_id')->nullable();
    $table->boolean('synced_to_calendar')->default(false);
});

// Create google_tokens table
Schema::create('google_tokens', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->text('access_token');
    $table->text('refresh_token')->nullable();
    $table->timestamp('expires_at');
    $table->timestamps();
});
```

#### Step 4: Create Services
- `GoogleCalendarService.php` - Main calendar service
- `CalendarSyncService.php` - Sync follow-ups
- `CalendarEventBuilder.php` - Build event data

#### Step 5: Create Pages & Widgets
- `CalendarPage.php` - Full calendar view
- `CalendarWidget.php` - Dashboard widget
- `GoogleAuthPage.php` - OAuth connection

#### Step 6: Auto-Sync
- Observer: When follow-up created → create calendar event
- Observer: When follow-up updated → update calendar event
- Observer: When follow-up deleted → delete calendar event

---

## 🎨 Part 2: Trello-Style Kanban Board

### Features:
- ✅ Drag & drop cards
- ✅ Trello-like styling
- ✅ Card details modal
- ✅ Quick actions
- ✅ Real-time updates
- ✅ Filters & search
- ✅ Kanban widget

### Design Specifications:

#### Colors:
```css
Lead:     #FEF3C7 (Yellow-100) with #F59E0B border
Prospect: #DBEAFE (Blue-100) with #3B82F6 border
Customer: #D1FAE5 (Green-100) with #10B981 border
Inactive: #F3F4F6 (Gray-100) with #6B7280 border
```

#### Card Design:
```
┌─────────────────────────────────┐
│ 👤 Customer Name                │
│ ─────────────────────────────── │
│ 📧 email@example.com            │
│ 📱 +62 812 3456 789             │
│ ─────────────────────────────── │
│ 🏷️ Tag1  Tag2  Tag3             │
│ ─────────────────────────────── │
│ 📅 Next: Dec 15, 2025           │
│ 💬 3 follow-ups                 │
│ ─────────────────────────────── │
│ [👁️ View] [✏️ Edit] [📞 Call]    │
└─────────────────────────────────┘
```

#### Implementation:

**Technologies:**
- Alpine.js (already in Filament)
- Sortable.js for drag & drop
- Tailwind CSS for styling
- Livewire for backend

**Files to Create:**
1. `resources/views/filament/resources/customer-resource/pages/customer-kanban.blade.php`
2. `public/js/kanban.js`
3. `public/css/kanban.css`

---

## 📁 File Structure

```
app/
├── Services/
│   ├── GoogleCalendarService.php
│   ├── CalendarSyncService.php
│   └── CalendarEventBuilder.php
├── Filament/
│   ├── Pages/
│   │   ├── CalendarPage.php
│   │   └── GoogleAuthPage.php
│   └── Widgets/
│       ├── CalendarWidget.php
│       └── KanbanWidget.php
├── Observers/
│   └── FollowUpCalendarObserver.php
└── Models/
    └── GoogleToken.php

database/
└── migrations/
    ├── xxxx_add_google_fields_to_follow_ups.php
    └── xxxx_create_google_tokens_table.php

resources/
└── views/
    └── filament/
        ├── pages/
        │   └── calendar.blade.php
        └── widgets/
            ├── calendar-widget.blade.php
            └── kanban-widget.blade.php

public/
├── js/
│   ├── kanban.js
│   └── calendar.js
└── css/
    └── kanban.css
```

---

## 🚀 Implementation Priority

### Phase 1: Trello-Style Kanban (Easier, No External Dependencies)
1. ✅ Update Kanban blade view with Trello styling
2. ✅ Add Sortable.js for drag & drop
3. ✅ Enhance card design
4. ✅ Add quick actions
5. ✅ Create Kanban widget

### Phase 2: Google Calendar Integration (Complex, External API)
1. ✅ Install Google API client
2. ✅ Setup OAuth flow
3. ✅ Create calendar services
4. ✅ Add database migrations
5. ✅ Create calendar page & widget
6. ✅ Implement auto-sync

---

## 📝 Detailed Implementation

### I'll create the following files in order:

1. **Kanban Styling & Enhancement** (Immediate)
   - Updated blade view
   - CSS for Trello-style
   - JavaScript for drag & drop

2. **Kanban Widget** (Immediate)
   - Dashboard widget
   - Mini kanban view

3. **Google Calendar Setup** (Requires user action)
   - Migration files
   - Service classes
   - OAuth pages

4. **Calendar Widget** (After Google setup)
   - Dashboard calendar
   - Upcoming follow-ups

---

## ⚠️ Important Notes

### Google Calendar:
- Requires Google Cloud account
- OAuth setup needed
- User must authorize access
- Rate limits apply (10,000 requests/day)

### Kanban:
- Works immediately
- No external dependencies (except Sortable.js CDN)
- Real-time with Livewire

---

## 🎯 Expected Outcome

### Kanban Board:
```
┌─────────────┬─────────────┬─────────────┬─────────────┐
│    LEAD     │  PROSPECT   │  CUSTOMER   │  INACTIVE   │
│   (Yellow)  │   (Blue)    │   (Green)   │   (Gray)    │
├─────────────┼─────────────┼─────────────┼─────────────┤
│ [Card 1]    │ [Card 3]    │ [Card 5]    │ [Card 7]    │
│ [Card 2]    │ [Card 4]    │ [Card 6]    │ [Card 8]    │
│             │             │             │             │
│ + Add       │ + Add       │ + Add       │ + Add       │
└─────────────┴─────────────┴─────────────┴─────────────┘
```

### Calendar Widget:
```
┌─────────────────────────────────────────┐
│  📅 Upcoming Follow-ups                 │
├─────────────────────────────────────────┤
│  Today (3)                              │
│  • 10:00 - Call John Doe               │
│  • 14:00 - Meeting PT ABC              │
│  • 16:00 - Follow-up Jane Smith        │
│                                         │
│  Tomorrow (2)                           │
│  • 09:00 - Demo for XYZ Corp           │
│  • 15:00 - Check-in with Client        │
│                                         │
│  [View Full Calendar →]                 │
└─────────────────────────────────────────┘
```

---

**Ready to implement? Let's start with Kanban (easier) first!**
