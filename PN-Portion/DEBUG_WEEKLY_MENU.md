# Debug Guide: Weekly Menu Dishes "Add Dish" Button

## ✅ Fix Applied

Changed from `@section('scripts')` to `@push('scripts')` to match the layout file.

---

## 🔍 How to Test if It's Working Now

### Step 1: Clear Browser Cache
Press **Ctrl + Shift + R** (hard refresh) or clear your browser cache completely.

### Step 2: Open Developer Console
1. Press **F12** in your browser
2. Go to the **Console** tab
3. Look for any JavaScript errors (they will be in red)

### Step 3: Access the Page
Go to: `http://127.0.0.1:8001/cook/weekly-menu-dishes`

### Step 4: Test the Button
1. Click any "Add Dish" button
2. A modal should pop up with the title "Create Weekly Menu Dish"

---

## 🐛 If Button Still Doesn't Work

### Check 1: Verify JavaScript is Loaded

Open browser console (F12) and type:
```javascript
typeof openCreateDishModal
```

**Expected result:** `"function"`  
**If you see:** `"undefined"` → JavaScript not loaded

### Check 2: Verify Bootstrap is Loaded

In browser console, type:
```javascript
typeof bootstrap
```

**Expected result:** `"object"`  
**If you see:** `"undefined"` → Bootstrap not loaded

### Check 3: Check for Console Errors

Look in the Console tab for errors like:
- `bootstrap is not defined`
- `openCreateDishModal is not defined`
- `$ is not defined` (jQuery)

### Check 4: Verify Button HTML

Right-click the "Add Dish" button → Inspect Element

Should look like:
```html
<button type="button" class="btn btn-outline-primary btn-sm w-100" 
        onclick="openCreateDishModal(1, 'monday', 'breakfast')">
    <i class="bi bi-plus-circle"></i> Add Dish
</button>
```

---

## 🔧 Manual Test

If the button still doesn't work, try this in the browser console:

```javascript
// Test if function exists
console.log(typeof openCreateDishModal);

// Try calling it manually
openCreateDishModal(1, 'monday', 'breakfast');
```

If the modal opens, the function works but the button click isn't firing.

---

## 🚨 Common Issues & Solutions

### Issue 1: "openCreateDishModal is not defined"

**Cause:** JavaScript not loaded  
**Solution:**
1. Clear view cache: `php artisan view:clear`
2. Hard refresh browser: Ctrl + Shift + R
3. Check if `@push('scripts')` is in the view
4. Check if `@stack('scripts')` is in the layout

### Issue 2: "bootstrap is not defined"

**Cause:** Bootstrap JS not loaded  
**Solution:** Check if layout includes Bootstrap:
```html
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
```

### Issue 3: Button Clicks But Nothing Happens

**Cause:** Modal HTML might be missing  
**Solution:** Check if modal div exists in the page:
```javascript
console.log(document.getElementById('dishModal'));
```
Should return an element, not `null`.

### Issue 4: "Cannot read property 'value' of null"

**Cause:** Form fields missing  
**Solution:** Check if all form fields exist:
```javascript
console.log(document.getElementById('weekCycle'));
console.log(document.getElementById('dayOfWeek'));
console.log(document.getElementById('mealType'));
```

---

## ✅ Quick Fix Commands

Run these commands to ensure everything is fresh:

```bash
cd /home/oem/PN_Systems/PN-Portion

# Clear all caches
php artisan view:clear
php artisan config:clear
php artisan route:clear

# Restart server
# Press Ctrl+C to stop, then:
php artisan serve
```

Then in browser:
1. Close all tabs with the site
2. Open new tab
3. Hard refresh: Ctrl + Shift + R
4. Try clicking "Add Dish"

---

## 📋 Checklist

- [ ] Cleared Laravel view cache
- [ ] Cleared browser cache (hard refresh)
- [ ] Opened browser console (F12)
- [ ] No red errors in console
- [ ] `typeof openCreateDishModal` returns "function"
- [ ] `typeof bootstrap` returns "object"
- [ ] Clicked "Add Dish" button
- [ ] Modal appears with form

---

## 🎯 Expected Behavior

When you click "Add Dish":

1. ✅ Modal pops up
2. ✅ Title shows "Create Weekly Menu Dish"
3. ✅ Form has fields: Dish Name, Description
4. ✅ One ingredient row is added automatically
5. ✅ "Add Ingredient" button is visible
6. ✅ "Check Ingredient Availability" button is visible
7. ✅ "Save Dish" button is visible

---

## 📞 Still Not Working?

If after all these steps it still doesn't work, please check:

1. **Browser Console Errors:** Copy any red error messages
2. **Network Tab:** Check if JavaScript files are loading (Status 200)
3. **Elements Tab:** Verify the button HTML has the onclick attribute

Then we can debug further based on the specific error.

---

**Last Updated:** November 11, 2025 09:01 AM  
**Status:** ✅ JavaScript fixed to use @push instead of @section
