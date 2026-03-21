#!/bin/bash

echo "=========================================="
echo "  PN-PORTION: Fix & Test Script"
echo "=========================================="
echo ""

# Clear all caches
echo "✓ Clearing Laravel caches..."
php artisan view:clear
php artisan config:clear
php artisan route:clear
php artisan cache:clear 2>/dev/null || true

echo ""
echo "✓ Caches cleared!"
echo ""

# Check if server is running
echo "✓ Checking server status..."
if lsof -Pi :8001 -sTCP:LISTEN -t >/dev/null ; then
    echo "  Server is running on port 8001"
else
    echo "  Server is NOT running. Starting..."
    php artisan serve &
    sleep 2
fi

echo ""
echo "=========================================="
echo "  ✅ READY TO TEST!"
echo "=========================================="
echo ""
echo "📋 Test URLs:"
echo ""
echo "1. Test Button Functionality:"
echo "   http://127.0.0.1:8001/test-button.html"
echo ""
echo "2. Kitchen Team - Inventory Management:"
echo "   http://127.0.0.1:8001/kitchen/inventory-management"
echo ""
echo "3. Cook - Weekly Menu Dishes:"
echo "   http://127.0.0.1:8001/cook/weekly-menu-dishes"
echo ""
echo "4. Test Access Page (All Links):"
echo "   http://127.0.0.1:8001/test-access.html"
echo ""
echo "=========================================="
echo "  📝 IMPORTANT:"
echo "=========================================="
echo ""
echo "After opening the page in your browser:"
echo "1. Press Ctrl+Shift+R (hard refresh)"
echo "2. Open Console (F12)"
echo "3. Check for errors"
echo "4. Try clicking 'Add Dish' button"
echo ""
echo "If button doesn't work, check:"
echo "- BUTTON_FIX_COMPLETE.md"
echo "- DEBUG_WEEKLY_MENU.md"
echo ""
