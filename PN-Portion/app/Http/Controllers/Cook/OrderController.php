<?php

namespace App\Http\Controllers\Cook;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PreOrder;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::with(['items.menu', 'student'])
            ->orderBy('created_at', 'desc')
            ->get();
        return view('cook.orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        $order->load(['items.menu', 'student']);
        return view('cook.orders.show', compact('order'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,preparing,ready,completed,cancelled'
        ]);

        $order->update(['status' => $validated['status']]);

        // If order is completed, update inventory
        if ($validated['status'] === 'completed') {
            foreach ($order->items as $item) {
                foreach ($item->menu->ingredients as $ingredient) {
                    $inventoryItem = $ingredient->inventoryItem;
                    $quantityToDeduct = $ingredient->quantity_required * $item->quantity;
                    
                    if ($inventoryItem->quantity < $quantityToDeduct) {
                        return back()->withErrors(['error' => "Insufficient stock for {$inventoryItem->name}"]);
                    }

                    $inventoryItem->decrement('quantity', $quantityToDeduct);
                }
            }
        }

        return redirect()->route('cook.orders.index')->with('success', 'Order status updated successfully');
    }

    public function dailyOrders()
    {
        // Get daily pre-orders instead of orders
        $orders = PreOrder::with(['menu', 'user'])
            ->whereDate('created_at', today())
            ->orderBy('created_at', 'desc')
            ->get();
        return view('cook.orders.daily', compact('orders'));
    }

    public function pendingOrders()
    {
        // Get pending pre-orders instead of orders
        $orders = PreOrder::with(['menu', 'user'])
            ->where('is_prepared', false)
            ->orderBy('created_at', 'asc')
            ->get();
        return view('cook.orders.pending', compact('orders'));
    }

    public function orderHistory()
    {
        // Get completed pre-orders instead of orders
        $orders = PreOrder::with(['menu', 'user'])
            ->where('is_prepared', true)
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        return view('cook.orders.history', compact('orders'));
    }

    public function analytics()
    {
        // Get analytics from pre-orders instead of orders
        $dailyOrders = PreOrder::whereDate('created_at', today())->count();

        $popularItems = PreOrder::with('menu')
            ->select('menu_id', \DB::raw('COUNT(*) as total_count'))
            ->groupBy('menu_id')
            ->orderByDesc('total_count')
            ->limit(5)
            ->get();

        return view('cook.orders.analytics', compact('dailyOrders', 'popularItems'));
    }
}
