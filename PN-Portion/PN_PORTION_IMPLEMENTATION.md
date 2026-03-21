# PN-Portion System Implementation

## Overview
This document describes the implementation of the PN-Portion system that allows the Kitchen Team and Cook to manage ingredients, purchase orders, and weekly menus with automatic inventory updates.

## System Architecture

### User Roles

#### 1. Kitchen Team
**Responsibilities:**
- Maintain and update the Inventory
- View, add, edit, and remove items in the inventory
- Track inventory history

**Access:**
- Inventory Management interface at `/kitchen/inventory-management`

#### 2. Cook
**Responsibilities:**
- Create Purchase Orders to restock items
- Create Weekly Menus and plan dishes with ingredients
- View inventory updates

**Access:**
- Purchase Orders interface at `/cook/purchase-orders`
- Weekly Menu Dishes interface at `/cook/weekly-menu-dishes`

---

## Core Features

### 1. Inventory Management (Kitchen Team)

**Location:** `app/Http/Controllers/Kitchen/InventoryManagementController.php`

**Database Table:** `inventory`

**Fields:**
- `name` - Item Name (e.g., Tomatoes, Chicken, Flour)
- `item_type` - Item Type (e.g., Vegetable, Meat, Spice)
- `quantity` - Available quantity
- `unit` - Unit of measurement (e.g., kg, pieces, liters)
- `description` - Optional description
- `reorder_point` - Minimum stock level before reorder alert
- `last_updated_by` - User who last updated the item
- `status` - Current status (available, low_stock, out_of_stock)

**Functions:**
- ✅ View all inventory items with pagination
- ✅ Add new inventory items
- ✅ Edit existing inventory items
- ✅ Remove inventory items (with validation)
- ✅ View inventory history/transaction log
- ✅ Automatic status calculation (available/low stock/out of stock)

**Routes:**
```php
GET  /kitchen/inventory-management           - List all inventory items
POST /kitchen/inventory-management           - Create new item
PUT  /kitchen/inventory-management/{id}      - Update item
DELETE /kitchen/inventory-management/{id}    - Delete item
GET  /kitchen/inventory-management/{id}/history - View item history
```

---

### 2. Purchase Order Management (Cook)

**Location:** `app/Http/Controllers/Cook/PurchaseOrderController.php`

**Database Tables:**
- `purchase_orders` - Main purchase order records
- `purchase_order_items` - Individual items in each order

**Purchase Order Fields:**
- `order_number` - Auto-generated (e.g., PO-2025-0001)
- `created_by` - Cook who created the order
- `ordered_by` - Name of person ordering
- `supplier_name` - Supplier name
- `status` - pending, approved, delivered, cancelled
- `order_date` - Date of order
- `expected_delivery_date` - Expected delivery
- `total_amount` - Total cost (auto-calculated)

**Purchase Order Item Fields:**
- `item_name` - Name of item
- `quantity_ordered` - Quantity to purchase
- `quantity_delivered` - Actual quantity delivered
- `unit` - Unit of measurement
- `unit_price` - Cost per unit
- `total_price` - Quantity × Unit Price (auto-calculated)
- `inventory_id` - Link to inventory item

**Process Flow:**

1. **Cook Creates Purchase Order:**
   - Select items to order
   - Specify quantity and unit price
   - System calculates total cost automatically
   - Status: `pending`

2. **Order Approval:**
   - Cook or Admin approves the order
   - Status changes to `approved`

3. **Delivery Confirmation:**
   - Kitchen Team or Cook confirms delivery
   - Status changes to `delivered`
   - **AUTOMATIC INVENTORY UPDATE:**
     - System adds delivered quantity to inventory
     - Creates inventory history record
     - Updates `last_updated_by` field

**Example:**
```
Before PO: Tomatoes = 100 kg

PO Created:
- Item: Tomatoes
- Quantity: 200 kg
- Unit Price: 10
- Total Cost: 2,000 (auto-calculated)

After Delivery Confirmation:
- Tomatoes = 100 + 200 = 300 kg (AUTOMATIC)
- Inventory History: "Purchase Order PO-2025-0001 delivery"
```

**Routes:**
```php
GET  /cook/purchase-orders                    - List orders
GET  /cook/purchase-orders/create             - Create form
POST /cook/purchase-orders                    - Store order
GET  /cook/purchase-orders/{id}               - View order
PUT  /cook/purchase-orders/{id}               - Update order
DELETE /cook/purchase-orders/{id}             - Delete order
POST /cook/purchase-orders/{id}/approve       - Approve order
```

---

### 3. Weekly Menu Management (Cook)

**Location:** `app/Http/Controllers/Cook/WeeklyMenuDishController.php`

**Database Tables:**
- `weekly_menu_dishes` - Dish records
- `weekly_menu_dish_ingredients` - Ingredients used in each dish

**Weekly Menu Dish Fields:**
- `dish_name` - Name of the dish
- `description` - Optional description
- `day_of_week` - monday, tuesday, etc.
- `meal_type` - breakfast, lunch, dinner
- `week_cycle` - 1 or 2 (for alternating weeks)
- `created_by` - Cook who created it

**Dish Ingredient Fields (Pivot Table):**
- `weekly_menu_dish_id` - Link to dish
- `inventory_id` - Link to inventory item
- `quantity_used` - Amount used for this dish
- `unit` - Unit of measurement

**Process Flow:**

1. **Cook Creates Weekly Menu Dish:**
   - Select day, meal type, and week cycle
   - Enter dish name and description
   - Add ingredients from inventory
   - Specify quantity used for each ingredient
   - System checks if ingredients are available

2. **Ingredient Availability Check:**
   - System validates sufficient inventory
   - Shows available vs required quantities
   - Prevents creation if insufficient stock

3. **Dish Creation:**
   - **AUTOMATIC INVENTORY DEDUCTION:**
     - System deducts used quantities from inventory
     - Creates inventory history for each ingredient
     - Updates `last_updated_by` field

4. **Dish Update:**
   - System restores old ingredients to inventory
   - Deducts new ingredients from inventory
   - Maintains accurate inventory levels

5. **Dish Deletion:**
   - System restores all ingredients back to inventory
   - Creates restoration history records

**Example:**
```
Inventory Before Menu:
- Tomatoes = 300 kg
- Chicken = 150 kg

Weekly Menu Created:
- Dish: Tomato Soup (Monday Lunch)
- Ingredients:
  * Tomatoes: 10 kg
  * Chicken: 20 kg

Inventory After Menu (AUTOMATIC):
- Tomatoes = 300 - 10 = 290 kg
- Chicken = 150 - 20 = 130 kg
- History: "Used for weekly menu dish: Tomato Soup (monday lunch)"
```

**Routes:**
```php
GET  /cook/weekly-menu-dishes                 - List all dishes
POST /cook/weekly-menu-dishes                 - Create dish
GET  /cook/weekly-menu-dishes/{id}            - View dish
PUT  /cook/weekly-menu-dishes/{id}            - Update dish
DELETE /cook/weekly-menu-dishes/{id}          - Delete dish
GET  /api/available-inventory                 - Get inventory (AJAX)
POST /api/check-ingredient-availability       - Check availability (AJAX)
```

---

## Database Schema

### New Tables Created

#### 1. `inventory` table (enhanced)
```sql
- id
- name
- item_type (NEW FIELD)
- category
- quantity
- unit
- description
- reorder_point
- supplier
- location
- unit_price
- last_updated_by
- status
- timestamps
```

#### 2. `weekly_menu_dishes` table (NEW)
```sql
- id
- dish_name
- description
- day_of_week
- meal_type
- week_cycle
- created_by
- timestamps
```

#### 3. `weekly_menu_dish_ingredients` table (NEW)
```sql
- id
- weekly_menu_dish_id
- inventory_id
- quantity_used
- unit
- timestamps
```

#### 4. `inventory_history` table (existing)
```sql
- id
- inventory_item_id
- user_id
- action_type
- quantity_change
- previous_quantity
- new_quantity
- notes
- timestamps
```

---

## Models

### 1. Inventory Model
**File:** `app/Models/Inventory.php`

**Key Methods:**
- `scopeLowStock()` - Get items below reorder point
- `scopeOutOfStock()` - Get items with zero quantity
- `isLowStock()` - Check if item needs reordering
- `weeklyMenuDishes()` - Relationship to dishes using this item

### 2. PurchaseOrder Model
**File:** `app/Models/PurchaseOrder.php`

**Key Methods:**
- `markAsDelivered()` - Mark order as delivered and update inventory
- `updateInventoryFromDelivery()` - Automatic inventory update
- `calculateTotal()` - Calculate total order cost

### 3. WeeklyMenuDish Model (NEW)
**File:** `app/Models/WeeklyMenuDish.php`

**Key Methods:**
- `deductIngredientsFromInventory()` - Deduct ingredients when dish is created
- `canBePrepared()` - Check if sufficient inventory exists
- `getMissingIngredients()` - Get list of insufficient ingredients
- `ingredients()` - Relationship to inventory items

---

## Automatic Inventory Updates

### Trigger 1: Purchase Order Delivery
**When:** Cook or Kitchen Team confirms delivery
**Action:** 
```php
foreach ($purchaseOrder->items as $item) {
    $inventoryItem->quantity += $item->quantity_delivered;
    $inventoryItem->save();
    
    // Log history
    InventoryHistory::create([
        'action_type' => 'purchase_delivery',
        'quantity_change' => +$item->quantity_delivered,
        'notes' => "Purchase Order {$orderNumber} delivery"
    ]);
}
```

### Trigger 2: Weekly Menu Creation
**When:** Cook creates a new weekly menu dish
**Action:**
```php
foreach ($dish->ingredients as $ingredient) {
    $inventoryItem->quantity -= $ingredient->pivot->quantity_used;
    $inventoryItem->save();
    
    // Log history
    InventoryHistory::create([
        'action_type' => 'weekly_menu_creation',
        'quantity_change' => -$ingredient->pivot->quantity_used,
        'notes' => "Used for weekly menu dish: {$dishName}"
    ]);
}
```

### Trigger 3: Weekly Menu Update
**When:** Cook updates an existing dish
**Action:**
```php
// Step 1: Restore old ingredients
foreach ($oldIngredients as $ingredient) {
    $inventoryItem->quantity += $ingredient->quantity_used;
}

// Step 2: Deduct new ingredients
foreach ($newIngredients as $ingredient) {
    $inventoryItem->quantity -= $ingredient->quantity_used;
}
```

### Trigger 4: Weekly Menu Deletion
**When:** Cook deletes a dish
**Action:**
```php
foreach ($dish->ingredients as $ingredient) {
    $inventoryItem->quantity += $ingredient->pivot->quantity_used;
    $inventoryItem->save();
    
    // Log history
    InventoryHistory::create([
        'action_type' => 'weekly_menu_deletion',
        'quantity_change' => +$ingredient->pivot->quantity_used,
        'notes' => "Restored from deleting dish: {$dishName}"
    ]);
}
```

---

## System Flow Summary

### Complete Workflow

```
Step 1: Kitchen Team Sets Up Inventory
├─ Add items: Tomatoes, Chicken, Flour, etc.
├─ Set quantities and reorder points
└─ System tracks all changes

Step 2: Cook Creates Purchase Order
├─ Select items to restock
├─ Enter quantities and prices
├─ System calculates total cost
└─ Status: Pending → Approved

Step 3: Delivery Confirmation
├─ Kitchen Team or Cook confirms delivery
├─ System AUTOMATICALLY adds to inventory
├─ Creates history record
└─ Status: Delivered

Step 4: Cook Creates Weekly Menu
├─ Select day, meal type, week cycle
├─ Add dish with ingredients
├─ System checks availability
├─ System AUTOMATICALLY deducts from inventory
└─ Creates history record

Step 5: Kitchen Team Views Updated Inventory
├─ See real-time quantities
├─ View transaction history
└─ Identify low stock items
```

---

## Installation & Setup

### 1. Run Migrations
```bash
cd /home/oem/PN_Systems/PN-Portion
php artisan migrate
```

This will create:
- Add `item_type` column to `inventory` table
- Create `weekly_menu_dishes` table
- Create `weekly_menu_dish_ingredients` table

### 2. Access URLs

**Kitchen Team:**
- Inventory Management: `http://your-domain/kitchen/inventory-management`
- Inventory Check (Reports): `http://your-domain/kitchen/inventory`

**Cook:**
- Purchase Orders: `http://your-domain/cook/purchase-orders`
- Weekly Menu Dishes: `http://your-domain/cook/weekly-menu-dishes`
- Stock Management: `http://your-domain/cook/stock-management`

---

## Key Features Implemented

✅ **Inventory Management (Kitchen Team)**
- Add, edit, remove inventory items
- Track item name, item type, quantity
- View inventory history
- Automatic status indicators

✅ **Purchase Order System (Cook)**
- Create purchase orders with items
- Automatic cost calculation (Quantity × Unit Price)
- Automatic inventory update on delivery
- Order approval workflow

✅ **Weekly Menu System (Cook)**
- Create dishes with ingredient tracking
- Automatic inventory deduction
- Ingredient availability checking
- Restore inventory on dish deletion/update

✅ **Automatic Inventory Updates**
- Purchase order delivery → Inventory increases
- Weekly menu creation → Inventory decreases
- Complete audit trail via inventory history

✅ **Real-time Updates**
- Both roles see updated inventory immediately
- Transaction history for full transparency
- Low stock and out-of-stock alerts

---

## Important Notes

1. **Only Purchase Orders include cost calculations** - Inventory tracks only name, type, and quantity
2. **Both Purchase Orders and Weekly Menus automatically update inventory**
3. **System prevents insufficient inventory** - Cannot create menu if ingredients unavailable
4. **Complete audit trail** - All changes logged in inventory_history table
5. **Role-based access** - Kitchen Team manages inventory, Cook manages orders and menus
6. **No changes to other subsystems** - All modifications contained within PN-Portion folder

---

## Files Created/Modified

### New Files Created:
1. `database/migrations/2025_11_11_000000_add_item_type_to_inventory_table.php`
2. `database/migrations/2025_11_11_000001_create_weekly_menu_dishes_table.php`
3. `app/Models/WeeklyMenuDish.php`
4. `app/Http/Controllers/Cook/WeeklyMenuDishController.php`
5. `app/Http/Controllers/Kitchen/InventoryManagementController.php`
6. `resources/views/cook/weekly-menu-dishes/index.blade.php`
7. `resources/views/cook/weekly-menu-dishes/week-table.blade.php`
8. `resources/views/cook/weekly-menu-dishes/show.blade.php`
9. `resources/views/kitchen/inventory-management/index.blade.php`
10. `resources/views/kitchen/inventory-management/history.blade.php`

### Modified Files:
1. `app/Models/Inventory.php` - Added item_type field and weeklyMenuDishes relationship
2. `routes/web.php` - Added routes for weekly menu dishes and inventory management

---

## Testing Checklist

### Kitchen Team Tests:
- [ ] Add new inventory item with item_type
- [ ] Edit existing inventory item
- [ ] View inventory history
- [ ] Delete unused inventory item
- [ ] Verify low stock alerts

### Cook Tests:
- [ ] Create purchase order
- [ ] Approve purchase order
- [ ] Confirm delivery (verify inventory increases)
- [ ] Create weekly menu dish (verify inventory decreases)
- [ ] Check ingredient availability
- [ ] Update weekly menu dish (verify inventory adjusts)
- [ ] Delete weekly menu dish (verify inventory restores)

### Integration Tests:
- [ ] Complete workflow: Inventory → PO → Delivery → Menu → Inventory Update
- [ ] Verify inventory history shows all transactions
- [ ] Test insufficient inventory prevention
- [ ] Verify cost calculations in purchase orders

---

## Support & Maintenance

For issues or questions:
1. Check inventory_history table for transaction logs
2. Verify database migrations completed successfully
3. Ensure user roles are correctly assigned (kitchen/cook)
4. Check Laravel logs at `storage/logs/laravel.log`

---

**Implementation Date:** November 11, 2025
**System Version:** Laravel 10.x
**Database:** MySQL
