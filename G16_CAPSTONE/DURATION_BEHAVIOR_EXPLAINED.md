# Assignment Duration Behavior - Complete Guide

## 🎯 Core Concept

**Changing duration DOES NOT trigger auto-shuffle automatically.**
- It only changes **when future shuffles will occur**
- Existing assignments are **preserved**
- Only the **end dates** are updated

## ✅ Correct Behavior

### When You Change Duration:

1. **Existing assignments stay** - Students remain assigned
2. **End dates update** - Based on new duration from start date
3. **No auto-shuffle** - You must manually click "Auto-Shuffle" if you want new assignments

### Duration = 0 (Anytime)

**What it means:**
- Auto-shuffle button is **always available** (never disabled)
- You can **manually click** "Auto-Shuffle" anytime you want
- **No automatic shuffles** - you control when to shuffle

**What it does NOT mean:**
- ❌ Does NOT auto-shuffle automatically every time
- ❌ Does NOT shuffle when you change duration
- ❌ Does NOT shuffle in the background

**How to use:**
1. Set duration to 0
2. Click "Auto-Shuffle" **whenever you want** to create new assignments
3. Students get reassigned each time you click

### Duration = 1 (Daily)

**What it means:**
- Auto-shuffle runs **automatically every day** (via cron job)
- Assignment end dates set to **tomorrow**
- You can also **manually shuffle** anytime

**How to use:**
1. Set duration to 1
2. Set up cron job (see setup guide)
3. System automatically shuffles every day at midnight
4. Or manually click "Auto-Shuffle" anytime

### Duration > 1 (Custom)

**What it means:**
- Auto-shuffle runs **automatically after X days** (via cron job)
- Assignment end dates set to **start_date + X days**
- You can also **manually shuffle** after end date is reached

**How to use:**
1. Set duration to 7 (for weekly)
2. Set up cron job (see setup guide)
3. System automatically shuffles every 7 days
4. Or manually click "Auto-Shuffle" after 7 days pass

## 📋 Step-by-Step Examples

### Example 1: Manual Control (Duration = 0)

**Scenario:** You want full control over when to shuffle

**Steps:**
1. Click **Settings** → Set to **0 Days** → **Save**
   - ✅ Existing assignments preserved
   - ✅ End dates updated to 1 year from now
   - ✅ Student count stays the same (9 students still assigned)

2. When you want new assignments:
   - Click **"Auto-Shuffle"** button
   - ✅ New assignments created
   - ✅ Students reassigned (different students, same count)

3. Next day, want to shuffle again:
   - Click **"Auto-Shuffle"** button again
   - ✅ New assignments created again
   - ✅ Students reassigned again

**Result:** You control exactly when shuffles happen

### Example 2: Daily Automatic (Duration = 1)

**Scenario:** You want automatic daily rotation

**Steps:**
1. Click **Settings** → Set to **1 Day** → **Save**
   - ✅ Existing assignments preserved
   - ✅ End dates updated to tomorrow
   - ✅ Student count stays the same

2. Set up cron job (one-time setup)
   - See "Cron Setup" section below

3. Every day at midnight:
   - ✅ System automatically runs auto-shuffle
   - ✅ New assignments created
   - ✅ Students reassigned automatically

**Result:** Hands-off daily rotation

### Example 3: Weekly Automatic (Duration = 7)

**Scenario:** You want weekly rotation

**Steps:**
1. Click **Settings** → Set to **7 Days** → **Save**
   - ✅ Existing assignments preserved
   - ✅ End dates updated to 7 days from start date
   - ✅ Student count stays the same

2. Set up cron job (one-time setup)

3. Every 7 days:
   - ✅ System automatically runs auto-shuffle
   - ✅ New assignments created
   - ✅ Students reassigned automatically

**Result:** Hands-off weekly rotation

## 🔧 What Happens When You Change Duration

### Scenario: You have 9 students assigned, duration is 7 days

**You change duration to 0:**

**Before:**
- Kitchen operation: 9 students assigned
- End date: November 16, 2025
- Duration: 7 days

**After changing to 0:**
- Kitchen operation: **9 students still assigned** ✅
- End date: November 9, 2026 (1 year from now)
- Duration: 0 days (anytime)

**What changed:**
- ✅ End date updated
- ✅ Duration setting updated

**What did NOT change:**
- ✅ Same 9 students still assigned
- ✅ Same coordinators
- ✅ Same requirements

## ⚠️ Important Notes

### Changing Duration vs. Running Auto-Shuffle

| Action | Effect |
|--------|--------|
| **Change Duration** | Updates end dates only, preserves assignments |
| **Click Auto-Shuffle** | Creates NEW assignments, reassigns students |

### Student Count Preservation

When you change duration:
- ✅ Student count stays the same
- ✅ Same students stay assigned
- ✅ Requirements unchanged

When you click Auto-Shuffle:
- ✅ Student count stays the same (if requirements unchanged)
- ⚠️ Different students may be assigned
- ✅ Requirements still met

## 🐛 Troubleshooting

### Problem: Changed duration to 0, now only 1 student assigned

**Cause:** You clicked "Auto-Shuffle" after changing duration, and something went wrong

**Solution:**
```bash
# Run this command to fix:
php artisan assignments:fix-kitchen
```

Or manually click "Auto-Shuffle" button again to reassign students properly.

### Problem: Duration = 0 but auto-shuffle button is disabled

**Cause:** This shouldn't happen with duration = 0

**Solution:**
1. Refresh the page
2. Check if duration is actually set to 0 in Settings
3. Try setting duration to 0 again and save

### Problem: Changed duration but assignments disappeared

**Cause:** You might have clicked "Auto-Shuffle" which created new assignments

**Solution:**
- Click "Auto-Shuffle" again to reassign students
- Or check "View History" to see previous assignments

## 📊 Quick Reference

| Duration | Manual Shuffle | Auto Shuffle | Cron Needed |
|----------|---------------|--------------|-------------|
| **0** | ✅ Anytime | ❌ Never | ❌ No |
| **1** | ✅ Anytime | ✅ Daily | ✅ Yes |
| **7** | ✅ After 7 days | ✅ Weekly | ✅ Yes |
| **14** | ✅ After 14 days | ✅ Bi-weekly | ✅ Yes |

## 🔄 Cron Setup (For Automatic Shuffles)

Only needed if duration = 1 or higher.

### Windows Task Scheduler:
```
Program: php
Arguments: c:\PN_Systems\G16_CAPSTONE\artisan assignments:daily-shuffle
Trigger: Daily at 12:00 AM
```

### Linux Crontab:
```bash
0 0 * * * cd /path/to/G16_CAPSTONE && php artisan assignments:daily-shuffle
```

## ✅ Summary

1. **Duration = 0**: Manual control, shuffle anytime you want
2. **Duration = 1**: Automatic daily shuffles
3. **Duration > 1**: Automatic shuffles after X days
4. **Changing duration**: Preserves assignments, updates end dates only
5. **Clicking Auto-Shuffle**: Creates new assignments, reassigns students
6. **Student count**: Always preserved unless requirements changed
