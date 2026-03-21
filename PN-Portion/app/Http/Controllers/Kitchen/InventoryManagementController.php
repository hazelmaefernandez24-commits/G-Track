<?php

namespace App\Http\Controllers\Kitchen;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use App\Models\InventoryHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class InventoryManagementController extends Controller
{
    /**
     * Display inventory management page for Kitchen Team
     */
    public function index()
    {
        $inventoryItems = Inventory::orderBy('name')->paginate(20);
        
        $stats = [
            'total_items' => Inventory::count(),
            'low_stock_items' => Inventory::lowStock()->count(),
            'out_of_stock_items' => Inventory::outOfStock()->count(),
        ];

        return view('kitchen.inventory-management.index', compact('inventoryItems', 'stats'));
    }

    /**
     * Store a new inventory item
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'quantity' => 'required|numeric|min:0',
            'unit' => 'required|string|max:50',
            'description' => 'nullable|string',
            'reorder_point' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $item = Inventory::create([
                'name' => $request->name,
                'item_type' => 'general', // Default type
                'category' => 'general', // Default category
                'quantity' => $request->quantity,
                'unit' => $request->unit,
                'description' => $request->description,
                'reorder_point' => $request->reorder_point ?? 10,
                'last_updated_by' => Auth::user()->user_id,
                'status' => 'available'
            ]);

            // Log the creation
            InventoryHistory::create([
                'inventory_item_id' => $item->id,
                'user_id' => Auth::user()->user_id,
                'action_type' => 'created',
                'quantity_change' => $request->quantity,
                'previous_quantity' => 0,
                'new_quantity' => $request->quantity,
                'notes' => 'Initial inventory item creation'
            ]);

            return redirect()->route('kitchen.inventory-management.index')
                ->with('success', 'Inventory item added successfully!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to add inventory item: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Update an inventory item
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'quantity' => 'required|numeric|min:0',
            'unit' => 'required|string|max:50',
            'description' => 'nullable|string',
            'reorder_point' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $item = Inventory::findOrFail($id);
            $previousQuantity = $item->quantity;

            $item->update([
                'name' => $request->name,
                'quantity' => $request->quantity,
                'unit' => $request->unit,
                'description' => $request->description,
                'reorder_point' => $request->reorder_point ?? $item->reorder_point,
                'last_updated_by' => Auth::user()->user_id
            ]);

            // Log the update if quantity changed
            if ($previousQuantity != $request->quantity) {
                InventoryHistory::create([
                    'inventory_item_id' => $item->id,
                    'user_id' => Auth::user()->user_id,
                    'action_type' => 'manual_adjustment',
                    'quantity_change' => $request->quantity - $previousQuantity,
                    'previous_quantity' => $previousQuantity,
                    'new_quantity' => $request->quantity,
                    'notes' => 'Manual inventory update by kitchen team'
                ]);
            }

            return redirect()->route('kitchen.inventory-management.index')
                ->with('success', 'Inventory item updated successfully!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to update inventory item: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Delete an inventory item
     */
    public function destroy($id)
    {
        try {
            $item = Inventory::findOrFail($id);
            
            // Detach from weekly menu dishes (remove relationships)
            if ($item->weeklyMenuDishes()->count() > 0) {
                $item->weeklyMenuDishes()->detach();
            }

            // Delete associated history
            InventoryHistory::where('inventory_item_id', $id)->delete();

            // Delete the item
            $item->delete();

            return redirect()->route('kitchen.inventory-management.index')
                ->with('success', 'Inventory item deleted successfully!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to delete inventory item: ' . $e->getMessage());
        }
    }

    /**
     * View inventory history
     */
    public function history($id)
    {
        $item = Inventory::findOrFail($id);
        $history = InventoryHistory::where('inventory_item_id', $id)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('kitchen.inventory-management.history', compact('item', 'history'));
    }
}
