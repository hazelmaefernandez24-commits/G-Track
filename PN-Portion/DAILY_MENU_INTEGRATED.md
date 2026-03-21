# ✅ Daily & Weekly Menu Integrated into Weekly Menu Dishes

## 🎯 What Changed

**Removed:**
- ❌ "Daily & Weekly Menu" menu item from sidebar
- ❌ Separate page for viewing daily/weekly menu

**Added to Weekly Menu Dishes:**
- ✅ **"Today's Menu" section** at the top showing current day's meals
- ✅ **Current date/time** display in header
- ✅ **Highlight today's row** in the weekly table
- ✅ **"TODAY" badge** on current day
- ✅ **Week cycle indicator** (Week 1 or Week 2)
- ✅ **Automatic week detection** based on week number

---

## 🎨 New Features

### 1. Today's Menu Section (Top of Page)
Shows the current day's meals with:
- **Day name badge** (e.g., "Monday")
- **Week cycle badge** (e.g., "Week 1")
- **Three meal cards:**
  - 🌅 Breakfast (Yellow header)
  - ☀️ Lunch (Green header)
  - 🌙 Dinner (Red header)
- **Dish details:**
  - Dish name
  - Description
  - Ingredients with quantities

### 2. Current Date/Time Display
- Shows in header (top right)
- Format: "Monday, November 11, 2024, 09:52 AM"
- Updates automatically every minute

### 3. Highlighted Today's Row
- Current day's row has **blue background** (table-primary)
- **"TODAY" badge** next to day name
- Only highlights if viewing the current week cycle

### 4. Automatic Week Cycle Detection
- Week 1: Odd week numbers (1, 3, 5, etc.)
- Week 2: Even week numbers (2, 4, 6, etc.)
- Automatically shows correct week's menu

---

## 📋 How It Works

### Week Cycle Logic:
```php
$weekNumber = now()->weekOfYear; // e.g., 45
$currentWeek = ($weekNumber % 2 == 0) ? 2 : 1;
```

**Examples:**
- Week 45 (odd) → Shows Week 1 menu
- Week 46 (even) → Shows Week 2 menu
- Week 47 (odd) → Shows Week 1 menu

### Today Detection:
```php
$today = strtolower(now()->format('l')); // e.g., 'monday'
$isToday = ($today === $day && $currentWeek == $weekCycle);
```

---

## 🎯 Visual Layout

### Page Structure:
```
┌─────────────────────────────────────────────────┐
│ Weekly Menu Management          [Current Time]  │
├─────────────────────────────────────────────────┤
│                                                 │
│ 📅 Today's Menu [Monday] [Week 1]              │
│ ┌─────────┐ ┌─────────┐ ┌─────────┐           │
│ │🌅Breakfast│ │☀️Lunch  │ │🌙Dinner │           │
│ │ Dish Name│ │Dish Name│ │Dish Name│           │
│ │ Details  │ │Details  │ │Details  │           │
│ └─────────┘ └─────────┘ └─────────┘           │
│                                                 │
├─────────────────────────────────────────────────┤
│ [Week 1 & 3] [Week 2 & 4]                      │
├─────────────────────────────────────────────────┤
│ Day      │ Breakfast │ Lunch │ Dinner          │
│ Monday   │ ...       │ ...   │ ...             │
│ [TODAY]  │ (highlighted in blue)               │
│ Tuesday  │ ...       │ ...   │ ...             │
│ ...                                             │
└─────────────────────────────────────────────────┘
```

---

## ✅ Files Modified

### 1. Controller
**File:** `/app/Http/Controllers/Cook/WeeklyMenuDishController.php`

**Changes:**
- Added `getCurrentWeekCycle()` method
- Pass `$today`, `$currentWeek`, `$todaysDishes` to view
- Automatic week detection logic

### 2. Main View
**File:** `/resources/views/cook/weekly-menu-dishes/index.blade.php`

**Changes:**
- Added "Today's Menu" section
- Added current date/time display
- Added JavaScript for time updates
- Enhanced header with time

### 3. Week Table Partial
**File:** `/resources/views/cook/weekly-menu-dishes/week-table.blade.php`

**Changes:**
- Added today detection logic
- Highlight today's row with `table-primary` class
- Added "TODAY" badge

### 4. Sidebar
**File:** `/resources/views/Component/cook-sidebar.blade.php`

**Changes:**
- Removed "Daily & Weekly Menu" menu item
- "Weekly Menu Dishes" now uses calendar-week icon

---

## 🧪 How to Test

### Step 1: Clear Cache & Refresh
```bash
php artisan view:clear
php artisan route:clear
```

Then hard refresh browser: **Ctrl + Shift + R**

### Step 2: Access Weekly Menu Dishes
Go to: `http://127.0.0.1:8001/cook/weekly-menu-dishes`

### Step 3: Verify Features

**Check Today's Menu Section:**
- [ ] Shows at top of page
- [ ] Displays current day name
- [ ] Shows correct week cycle (1 or 2)
- [ ] Shows today's breakfast/lunch/dinner
- [ ] If no meal planned, shows "No meal planned"

**Check Date/Time:**
- [ ] Shows in header (top right)
- [ ] Format is readable
- [ ] Updates every minute

**Check Weekly Table:**
- [ ] Today's row has blue background
- [ ] "TODAY" badge appears next to day name
- [ ] Only highlights in correct week tab
- [ ] Other days are normal

**Check Sidebar:**
- [ ] "Daily & Weekly Menu" is removed
- [ ] "Weekly Menu Dishes" is the first item under MEAL PLANNING
- [ ] Icon is calendar-week

---

## 💡 Benefits

### 1. Single Page for Everything
- No need to switch between pages
- All menu information in one place
- Better user experience

### 2. Context Awareness
- Always know what day it is
- See today's menu immediately
- Know which week cycle is active

### 3. Visual Clarity
- Today's menu highlighted at top
- Current day highlighted in table
- Clear badges for day and week

### 4. Automatic Updates
- Week cycle changes automatically
- Today's menu updates daily
- Time updates every minute

---

## 🎨 Customization

### Change Highlight Color:
In `week-table.blade.php`, change:
```php
$rowClass = $isToday ? 'table-primary' : '';
```

Options:
- `table-primary` - Blue (current)
- `table-success` - Green
- `table-warning` - Yellow
- `table-info` - Light blue
- `table-danger` - Red

### Change Week Cycle Logic:
If you want different week cycle logic, modify in controller:
```php
// Current: Odd/Even week numbers
$currentWeek = ($weekNumber % 2 == 0) ? 2 : 1;

// Alternative: First/Second half of month
$dayOfMonth = now()->day;
$currentWeek = ($dayOfMonth <= 15) ? 1 : 2;

// Alternative: Manual selection
$currentWeek = 1; // Always show week 1
```

---

## 📊 Data Flow

```
Controller (WeeklyMenuDishController)
    ↓
Get current day (e.g., 'monday')
Get current week cycle (1 or 2)
Get today's dishes from correct week
    ↓
Pass to View:
- $today = 'monday'
- $currentWeek = 1
- $todaysDishes = ['breakfast' => ..., 'lunch' => ..., 'dinner' => ...]
    ↓
View (index.blade.php)
    ↓
Display "Today's Menu" section
Show current date/time
    ↓
Week Table (week-table.blade.php)
    ↓
Highlight today's row if:
- $today === $day
- $currentWeek === $weekCycle
```

---

## 🎉 Result

**Before:**
- Separate "Daily & Weekly Menu" page
- Need to navigate to see today's menu
- No indication of current day in weekly view

**After:**
- ✅ Today's menu at top of page
- ✅ Current day highlighted in table
- ✅ Week cycle automatically detected
- ✅ Date/time always visible
- ✅ One unified page for all menu management

---

**Status:** ✅ INTEGRATED SUCCESSFULLY  
**Last Updated:** November 11, 2025 09:52 AM  
**Result:** Daily & Weekly Menu functionality now in Weekly Menu Dishes!

**Hard refresh (Ctrl+Shift+R) to see the changes!** 🎉
