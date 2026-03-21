# Task Management Enhancement - Deployment Guide

## Quick Start

### Step 1: Apply Database Migration
```bash
cd G16_CAPSTONE
php artisan migrate
```

This will add the `color_code` column to the `categories` table.

### Step 2: Clear Cache (Optional but Recommended)
```bash
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

### Step 3: Test the Features

#### Feature 1: Color Picker for Sub-Areas
1. Navigate to the General Task Assignment page
2. Click "+ Add New Task Area" button
3. Select "Sub Area" radio button
4. Verify color picker section appears
5. Select a color or use custom color picker
6. Fill in other fields and create the sub-area
7. Verify color is saved in database

#### Feature 2: Balanced Schedule Generation
1. Open a task category (click "Manage Tasks")
2. Scroll to "Generate Schedule" section
3. Set start date, end date, and rotation frequency
4. Click "Apply Schedule"
5. Verify schedule is generated with balanced distribution
6. Check that each student has approximately equal task assignments

#### Feature 3: Add Task Functionality
1. In the "Manage Tasks" modal, click "+ Add Task"
2. Enter task title and description
3. Click "Save Task"
4. Verify task appears in the task list

---

## Files Modified

### Frontend
- `resources/views/generalTask.blade.php`
  - Added color picker UI (lines 816-836)
  - Added color picker JavaScript (lines 17640-17707)
  - Enhanced schedule generation (lines 17133-17201)
  - Updated save handler (lines 5795, 5819, 5837)

### Backend
- `app/Models/Category.php`
  - Added `color_code` to fillable array (line 13)

- `app/Http/Controllers/GeneralTaskController.php`
  - Added color_code validation (line 360)
  - Added color_code to category creation (line 380)

### Database
- `database/migrations/2025_11_25_000001_add_color_code_to_categories_table.php`
  - New migration file

---

## Verification Checklist

- [ ] Migration runs without errors
- [ ] No database errors in application logs
- [ ] Color picker appears when selecting "Sub Area"
- [ ] Color picker buttons are clickable
- [ ] Custom color input works
- [ ] Color is saved to database
- [ ] Schedule generation completes successfully
- [ ] Schedule shows balanced distribution
- [ ] Add Task button works in manage modal
- [ ] Tasks appear in task list

---

## Rollback Instructions

If you need to rollback the changes:

```bash
# Rollback the migration
php artisan migrate:rollback

# Or rollback to specific migration
php artisan migrate:rollback --step=1
```

This will remove the `color_code` column from the database.

---

## Troubleshooting

### Issue: Migration fails with "Column already exists"
**Solution:** The column may already exist. Check your database:
```sql
DESCRIBE categories;
```
If `color_code` exists, you can safely ignore the error.

### Issue: Color picker doesn't appear
**Solution:** 
1. Clear browser cache
2. Check browser console for JavaScript errors
3. Verify `subAreaType` radio button is properly selected

### Issue: Schedule generation shows no data
**Solution:**
1. Ensure tasks are added to the category
2. Ensure members are assigned to the category
3. Check browser console for API errors
4. Verify dates are in correct format (YYYY-MM-DD)

### Issue: Color not saving
**Solution:**
1. Check that color format is valid hex (#RRGGBB or #RGB)
2. Verify `color_code` column exists in database
3. Check server logs for validation errors

---

## Performance Notes

- Color picker: Minimal performance impact (simple DOM manipulation)
- Schedule generation: O(n²) complexity per task (acceptable for typical use)
- Database: Single column addition, no indexing required
- UI: No additional API calls required

---

## Browser Compatibility

- Color picker: Works in all modern browsers
- HTML5 color input: Fallback for older browsers
- JavaScript: ES6+ (requires modern browser)

---

## Support

For issues or questions:
1. Check the IMPLEMENTATION_SUMMARY.md for detailed information
2. Review browser console for JavaScript errors
3. Check Laravel logs: `storage/logs/laravel.log`
4. Verify database migration status: `php artisan migrate:status`

---

**Last Updated:** November 25, 2025
**Version:** 1.0
