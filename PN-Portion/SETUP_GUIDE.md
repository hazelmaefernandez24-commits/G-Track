# PN-Portion Setup Guide

## Quick Start

### Step 1: Run Database Migrations

Make sure your database is running, then execute:

```bash
cd /home/oem/PN_Systems/PN-Portion
php artisan migrate
```

This will create the necessary tables:
- Add `item_type` column to `inventory` table
- Create `weekly_menu_dishes` table
- Create `weekly_menu_dish_ingredients` table

### Step 2: Access the System

#### For Kitchen Team Users:
1. Login with a Kitchen Team account
2. Navigate to: **Inventory Management**
   - URL: `http://your-domain/kitchen/inventory-management`
3. Start adding inventory items with:
   - Item Name (e.g., Tomatoes)
   - Item Type (e.g., Vegetable)
   - Quantity (e.g., 100)
   - Unit (e.g., kg)

#### For Cook Users:
1. Login with a Cook account
2. Navigate to: **Purchase Orders**
   - URL: `http://your-domain/cook/purchase-orders`
   - Create orders to restock items
   - Confirm delivery to automatically update inventory

3. Navigate to: **Weekly Menu Dishes**
   - URL: `http://your-domain/cook/weekly-menu-dishes`
   - Create dishes with ingredients
   - System automatically deducts from inventory

---

## System Workflow Example

### Example 1: Complete Purchase Order Flow

**Step 1: Kitchen Team adds inventory item**
```
Item: Tomatoes
Type: Vegetable
Quantity: 100 kg
Reorder Point: 20 kg
```

**Step 2: Cook creates Purchase Order**
```
Order Date: Today
Supplier: Fresh Farms
Items:
  - Tomatoes: 200 kg @ ₱10/kg = ₱2,000
Total: ₱2,000
Status: Pending
```

**Step 3: Cook approves order**
```
Status: Pending → Approved
```

**Step 4: Delivery confirmed**
```
Status: Approved → Delivered
AUTOMATIC: Tomatoes inventory: 100 + 200 = 300 kg
History: "Purchase Order PO-2025-0001 delivery"
```

---

### Example 2: Weekly Menu Creation Flow

**Step 1: Check current inventory**
```
Tomatoes: 300 kg
Chicken: 150 kg
Onions: 50 kg
```

**Step 2: Cook creates weekly menu dish**
```
Dish: Chicken Adobo
Day: Monday
Meal Type: Lunch
Week Cycle: 1

Ingredients:
  - Chicken: 20 kg
  - Onions: 5 kg
  - Soy Sauce: 2 liters
```

**Step 3: System checks availability**
```
✓ Chicken: Available (150 kg) > Required (20 kg)
✓ Onions: Available (50 kg) > Required (5 kg)
✓ Soy Sauce: Available (10 liters) > Required (2 liters)
```

**Step 4: Dish created, inventory updated**
```
AUTOMATIC DEDUCTION:
- Chicken: 150 - 20 = 130 kg
- Onions: 50 - 5 = 45 kg
- Soy Sauce: 10 - 2 = 8 liters

History records created for each ingredient
```

---

## Navigation Menu Updates

Add these menu items to your navigation:

### Kitchen Team Menu:
```
- Dashboard
- Inventory Management (NEW) ← /kitchen/inventory-management
- Inventory Check (Reports)
- Purchase Orders (View only)
- Pre-Orders
- Post Assessment
```

### Cook Menu:
```
- Dashboard
- Weekly Menu
- Weekly Menu Dishes (NEW) ← /cook/weekly-menu-dishes
- Purchase Orders
- Stock Management
- Inventory Reports
- Student Feedback
```

---

## Key Features Summary

### ✅ Inventory Management (Kitchen Team)
- **Add Items**: Name, Type, Quantity, Unit
- **Edit Items**: Update any field
- **Remove Items**: Delete with validation
- **View History**: Complete transaction log
- **Status Alerts**: Low stock, out of stock indicators

### ✅ Purchase Orders (Cook)
- **Create Orders**: Multiple items per order
- **Auto Calculate**: Total cost = Qty × Unit Price
- **Approve Orders**: Workflow management
- **Confirm Delivery**: Automatic inventory increase
- **View History**: All order records

### ✅ Weekly Menu Dishes (Cook)
- **Create Dishes**: Day, meal type, ingredients
- **Check Availability**: Real-time inventory check
- **Auto Deduct**: Inventory decreases automatically
- **Update Dishes**: Restore old, deduct new ingredients
- **Delete Dishes**: Restore ingredients to inventory

### ✅ Automatic Updates
- Purchase delivery → Inventory increases ⬆️
- Menu creation → Inventory decreases ⬇️
- Menu update → Inventory adjusts ↔️
- Menu deletion → Inventory restores ⬆️

---

## Testing Steps

### Test 1: Inventory Management
1. Login as Kitchen Team
2. Go to Inventory Management
3. Click "Add New Item"
4. Fill in: Tomatoes, Vegetable, 100, kg
5. Verify item appears in list
6. Click Edit, change quantity to 150
7. View History to see the change

### Test 2: Purchase Order
1. Login as Cook
2. Go to Purchase Orders
3. Click "Create Purchase Order"
4. Add item: Tomatoes, 200 kg, ₱10/kg
5. Verify total shows ₱2,000
6. Submit order
7. Approve order
8. Confirm delivery
9. Check inventory - should show 100 + 200 = 300 kg

### Test 3: Weekly Menu
1. Login as Cook
2. Go to Weekly Menu Dishes
3. Click "Add Dish" on Monday Lunch
4. Enter dish name: "Tomato Soup"
5. Add ingredient: Tomatoes, 10 kg
6. Click "Check Ingredient Availability"
7. Verify shows available
8. Save dish
9. Check inventory - should show 300 - 10 = 290 kg
10. View inventory history - should show deduction

---

## Troubleshooting

### Issue: Migration fails
**Solution:** Ensure database is running and credentials in `.env` are correct

### Issue: Cannot see new menu items
**Solution:** Clear cache: `php artisan cache:clear` and `php artisan config:clear`

### Issue: Inventory not updating
**Solution:** Check `inventory_history` table for transaction logs

### Issue: Permission denied
**Solution:** Verify user role is set to 'kitchen' or 'cook' in database

---

## Database Schema Reference

### inventory table
```sql
id, name, item_type, category, quantity, unit, 
description, reorder_point, supplier, location, 
unit_price, last_updated_by, status, timestamps
```

### weekly_menu_dishes table
```sql
id, dish_name, description, day_of_week, meal_type, 
week_cycle, created_by, timestamps
```

### weekly_menu_dish_ingredients table
```sql
id, weekly_menu_dish_id, inventory_id, 
quantity_used, unit, timestamps
```

### inventory_history table
```sql
id, inventory_item_id, user_id, action_type, 
quantity_change, previous_quantity, new_quantity, 
notes, timestamps
```

---

## Important URLs

### Kitchen Team:
- Inventory Management: `/kitchen/inventory-management`
- Inventory History: `/kitchen/inventory-management/{id}/history`

### Cook:
- Purchase Orders: `/cook/purchase-orders`
- Create PO: `/cook/purchase-orders/create`
- Weekly Menu Dishes: `/cook/weekly-menu-dishes`
- Stock Management: `/cook/stock-management`

---

## Next Steps

1. ✅ Run migrations
2. ✅ Test Kitchen Team inventory management
3. ✅ Test Cook purchase order flow
4. ✅ Test Cook weekly menu creation
5. ✅ Verify automatic inventory updates
6. ✅ Check inventory history logs
7. ✅ Train users on new features

---

## Support

For detailed information, see: `PN_PORTION_IMPLEMENTATION.md`

**System Status:** ✅ Ready for Testing
**Last Updated:** November 11, 2025
