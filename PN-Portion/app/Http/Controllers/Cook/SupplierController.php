<?php

namespace App\Http\Controllers\Cook;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Supplier;
use App\Models\PurchaseOrder;

class SupplierController extends Controller
{
    public function index()
    {
        $suppliers = Supplier::all();
        return view('cook.suppliers', compact('suppliers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'contact_number' => 'required|string|max:20',
            'email' => 'required|email|max:255',
            'address' => 'required|string'
        ]);

        Supplier::create($validated);
        return redirect()->route('cook.suppliers')->with('success', 'Supplier added successfully');
    }

    public function update(Request $request, Supplier $supplier)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'contact_number' => 'required|string|max:20',
            'email' => 'required|email|max:255',
            'address' => 'required|string'
        ]);

        $supplier->update($validated);
        return redirect()->route('cook.suppliers')->with('success', 'Supplier updated successfully');
    }

    public function destroy(Supplier $supplier)
    {
        $supplier->delete();
        return redirect()->route('cook.suppliers')->with('success', 'Supplier deleted successfully');
    }

    public function createPurchaseOrder(Request $request)
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'items' => 'required|array',
            'items.*.name' => 'required|string',
            'items.*.quantity' => 'required|numeric',
            'items.*.unit' => 'required|string',
            'total_amount' => 'required|numeric',
            'status' => 'required|in:pending,approved,delivered'
        ]);

        PurchaseOrder::create($validated);
        return redirect()->route('cook.suppliers')->with('success', 'Purchase order created successfully');
    }
}
