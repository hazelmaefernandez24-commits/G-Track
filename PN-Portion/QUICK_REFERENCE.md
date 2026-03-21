# PN-Portion Quick Reference Card

## 🔑 Access URLs

| Role | Feature | URL |
|------|---------|-----|
| **Kitchen Team** | Inventory Management | `/kitchen/inventory-management` |
| **Kitchen Team** | Inventory Reports | `/kitchen/inventory` |
| **Cook** | Purchase Orders | `/cook/purchase-orders` |
| **Cook** | Weekly Menu Dishes | `/cook/weekly-menu-dishes` |
| **Cook** | Stock Management | `/cook/stock-management` |

---

## 📦 Inventory Management (Kitchen Team)

### Add New Item
```
1. Click "Add New Item"
2. Fill: Name, Type, Quantity, Unit
3. Save
```

### Edit Item
```
1. Click pencil icon
2. Update fields
3. Save
```

### View History
```
Click clock icon → See all transactions
```

---

## 🛒 Purchase Orders (Cook)

### Create Order
```
1. Click "Create Purchase Order"
2. Enter: Date, Supplier, Ordered By
3. Add items: Name, Qty, Unit, Price
4. Total auto-calculates
5. Submit
```

### Approve & Deliver
```
1. Click "Approve" → Status: Approved
2. Click "Mark as Delivered"
3. ✅ Inventory auto-increases
```

**Example:**
```
Before: Tomatoes = 100 kg
PO: +200 kg
After: Tomatoes = 300 kg (AUTOMATIC)
```

---

## 🍽️ Weekly Menu Dishes (Cook)

### Create Dish
```
1. Select day/meal slot
2. Click "Add Dish"
3. Enter dish name
4. Add ingredients:
   - Select item
   - Enter quantity
   - Unit auto-fills
5. Click "Check Availability"
6. Save
7. ✅ Inventory auto-decreases
```

**Example:**
```
Before: Tomatoes = 300 kg
Menu: Tomato Soup uses 10 kg
After: Tomatoes = 290 kg (AUTOMATIC)
```

### Update Dish
```
1. Click "View" on existing dish
2. Edit ingredients
3. Save
4. ✅ Old restored, new deducted
```

### Delete Dish
```
1. Click "Delete"
2. Confirm
3. ✅ Ingredients restored to inventory
```

---

## 🔄 Automatic Updates

| Action | Effect | Logged As |
|--------|--------|-----------|
| PO Delivered | Inventory ⬆️ Increases | `purchase_delivery` |
| Menu Created | Inventory ⬇️ Decreases | `weekly_menu_creation` |
| Menu Updated | Inventory ↔️ Adjusts | `weekly_menu_update` |
| Menu Deleted | Inventory ⬆️ Restores | `weekly_menu_deletion` |

---

## 📊 Status Indicators

| Badge | Meaning | Condition |
|-------|---------|-----------|
| 🟢 Available | Good stock | Qty > Reorder Point |
| 🟡 Low Stock | Need reorder | Qty ≤ Reorder Point |
| 🔴 Out of Stock | No stock | Qty = 0 |

---

## 🧪 Quick Test

### Complete Workflow (5 minutes)
```
1. Kitchen: Add "Tomatoes" 100 kg
2. Cook: Create PO for 200 kg @ ₱10
3. Cook: Approve & Deliver PO
4. Verify: Tomatoes = 300 kg ✓
5. Cook: Create "Tomato Soup" using 10 kg
6. Verify: Tomatoes = 290 kg ✓
7. View History: See both transactions ✓
```

---

## 🛠️ Troubleshooting

| Issue | Solution |
|-------|----------|
| Can't access page | Check user role (kitchen/cook) |
| Inventory not updating | Check inventory_history table |
| Can't create menu | Check ingredient availability |
| Can't delete item | Item used in menus/orders |

---

## 💡 Pro Tips

1. **Always check availability** before creating menus
2. **View history** to track all changes
3. **Set reorder points** for low stock alerts
4. **Use descriptive names** for dishes
5. **Verify totals** before submitting POs

---

## 📱 Commands

```bash
# Verify system
php test_pn_portion.php

# Clear cache
php artisan optimize:clear

# Start server
php artisan serve

# Check database
php artisan db:show
```

---

## 📞 Support

**Documentation:**
- Full Guide: `PN_PORTION_IMPLEMENTATION.md`
- Setup: `SETUP_GUIDE.md`
- Status: `SYSTEM_STATUS.md`

**Logs:**
- Laravel: `storage/logs/laravel.log`
- Inventory: `inventory_history` table

---

**System Status:** ✅ OPERATIONAL  
**Last Updated:** November 11, 2025
