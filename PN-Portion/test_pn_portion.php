<?php

/**
 * PN-Portion System Test Script
 * Run this to verify the system is working correctly
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "\n";
echo "========================================\n";
echo "   PN-PORTION SYSTEM VERIFICATION\n";
echo "========================================\n\n";

// Test 1: Check Database Connection
echo "✓ Test 1: Database Connection\n";
try {
    DB::connection()->getPdo();
    echo "  Status: CONNECTED\n";
    echo "  Database: " . DB::connection()->getDatabaseName() . "\n\n";
} catch (Exception $e) {
    echo "  Status: FAILED - " . $e->getMessage() . "\n\n";
    exit(1);
}

// Test 2: Check Inventory Table
echo "✓ Test 2: Inventory Table Structure\n";
try {
    $columns = DB::select('SHOW COLUMNS FROM inventory');
    $hasItemType = false;
    foreach ($columns as $col) {
        if ($col->Field == 'item_type') {
            $hasItemType = true;
            break;
        }
    }
    if ($hasItemType) {
        echo "  Status: item_type column EXISTS ✓\n\n";
    } else {
        echo "  Status: item_type column MISSING ✗\n\n";
    }
} catch (Exception $e) {
    echo "  Status: FAILED - " . $e->getMessage() . "\n\n";
}

// Test 3: Check Weekly Menu Dishes Table
echo "✓ Test 3: Weekly Menu Dishes Table\n";
try {
    $exists = DB::select("SELECT 1 FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'weekly_menu_dishes'");
    if ($exists) {
        echo "  Status: weekly_menu_dishes table EXISTS ✓\n";
        $count = DB::table('weekly_menu_dishes')->count();
        echo "  Records: $count dishes\n\n";
    } else {
        echo "  Status: Table MISSING ✗\n\n";
    }
} catch (Exception $e) {
    echo "  Status: FAILED - " . $e->getMessage() . "\n\n";
}

// Test 4: Check Weekly Menu Dish Ingredients Table
echo "✓ Test 4: Weekly Menu Dish Ingredients Table\n";
try {
    $exists = DB::select("SELECT 1 FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'weekly_menu_dish_ingredients'");
    if ($exists) {
        echo "  Status: weekly_menu_dish_ingredients table EXISTS ✓\n\n";
    } else {
        echo "  Status: Table MISSING ✗\n\n";
    }
} catch (Exception $e) {
    echo "  Status: FAILED - " . $e->getMessage() . "\n\n";
}

// Test 5: Check Models
echo "✓ Test 5: Model Classes\n";
$models = [
    'App\Models\Inventory',
    'App\Models\PurchaseOrder',
    'App\Models\PurchaseOrderItem',
    'App\Models\WeeklyMenuDish',
    'App\Models\InventoryHistory'
];

foreach ($models as $model) {
    if (class_exists($model)) {
        echo "  ✓ " . basename(str_replace('\\', '/', $model)) . " - EXISTS\n";
    } else {
        echo "  ✗ " . basename(str_replace('\\', '/', $model)) . " - MISSING\n";
    }
}
echo "\n";

// Test 6: Check Controllers
echo "✓ Test 6: Controller Classes\n";
$controllers = [
    'App\Http\Controllers\Cook\PurchaseOrderController',
    'App\Http\Controllers\Cook\WeeklyMenuDishController',
    'App\Http\Controllers\Kitchen\InventoryManagementController'
];

foreach ($controllers as $controller) {
    if (class_exists($controller)) {
        echo "  ✓ " . basename(str_replace('\\', '/', $controller)) . " - EXISTS\n";
    } else {
        echo "  ✗ " . basename(str_replace('\\', '/', $controller)) . " - MISSING\n";
    }
}
echo "\n";

// Test 7: Check Routes
echo "✓ Test 7: Routes Registration\n";
$routes = [
    'kitchen.inventory-management.index',
    'cook.purchase-orders.index',
    'cook.weekly-menu-dishes.index'
];

foreach ($routes as $routeName) {
    try {
        $url = route($routeName);
        echo "  ✓ $routeName - REGISTERED\n";
    } catch (Exception $e) {
        echo "  ✗ $routeName - MISSING\n";
    }
}
echo "\n";

// Test 8: Check Inventory Count
echo "✓ Test 8: Current Inventory Status\n";
try {
    $totalItems = DB::table('inventory')->count();
    $lowStock = DB::table('inventory')->whereRaw('quantity <= reorder_point')->count();
    $outOfStock = DB::table('inventory')->where('quantity', '<=', 0)->count();
    
    echo "  Total Items: $totalItems\n";
    echo "  Low Stock: $lowStock\n";
    echo "  Out of Stock: $outOfStock\n\n";
} catch (Exception $e) {
    echo "  Status: FAILED - " . $e->getMessage() . "\n\n";
}

// Test 9: Check Purchase Orders
echo "✓ Test 9: Purchase Orders Status\n";
try {
    $totalPOs = DB::table('purchase_orders')->count();
    $pending = DB::table('purchase_orders')->where('status', 'pending')->count();
    $delivered = DB::table('purchase_orders')->where('status', 'delivered')->count();
    
    echo "  Total Orders: $totalPOs\n";
    echo "  Pending: $pending\n";
    echo "  Delivered: $delivered\n\n";
} catch (Exception $e) {
    echo "  Status: FAILED - " . $e->getMessage() . "\n\n";
}

// Test 10: Check Weekly Menu Dishes
echo "✓ Test 10: Weekly Menu Dishes Status\n";
try {
    $totalDishes = DB::table('weekly_menu_dishes')->count();
    $week1 = DB::table('weekly_menu_dishes')->where('week_cycle', 1)->count();
    $week2 = DB::table('weekly_menu_dishes')->where('week_cycle', 2)->count();
    
    echo "  Total Dishes: $totalDishes\n";
    echo "  Week 1 Dishes: $week1\n";
    echo "  Week 2 Dishes: $week2\n\n";
} catch (Exception $e) {
    echo "  Status: FAILED - " . $e->getMessage() . "\n\n";
}

echo "========================================\n";
echo "   VERIFICATION COMPLETE!\n";
echo "========================================\n\n";

echo "Next Steps:\n";
echo "1. Access Kitchen Team: http://localhost:8000/kitchen/inventory-management\n";
echo "2. Access Cook PO: http://localhost:8000/cook/purchase-orders\n";
echo "3. Access Cook Menu: http://localhost:8000/cook/weekly-menu-dishes\n\n";

echo "For detailed documentation, see:\n";
echo "- PN_PORTION_IMPLEMENTATION.md\n";
echo "- SETUP_GUIDE.md\n\n";
