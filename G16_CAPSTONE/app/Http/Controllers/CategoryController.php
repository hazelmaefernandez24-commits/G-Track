<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;

class CategoryController extends Controller
{
    public function index() {
        $categories = Category::all();
        if (request()->expectsJson()) {
            return response()->json($categories);
        }
        return view('categories.index', compact('categories'));
    }

    // Store a new category and return it as JSON
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:categories,id',
            'is_main_area' => 'nullable|boolean',
        ]);

        // Ensure uniqueness using the Category model (uses the login connection)
        if (Category::where('name', $request->name)->exists()) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'errors' => ['name' => ['The name has already been taken.']]], 422);
            }
            return redirect()->back()->withErrors(['name' => 'The name has already been taken.'])->withInput();
        }

        // Determine parent_id based on form input
        $parentId = null;
        if ($request->has('parent_id') && $request->parent_id) {
            $parentId = $request->parent_id;
        } elseif ($request->has('is_main_area') && !$request->is_main_area) {
            // If it's not a main area, try to find a suitable parent
            // For now, we'll leave it null and let the user specify
        }

        $category = Category::create([
            'name' => $request->name,
            'description' => $request->description,
            'parent_id' => $parentId,
        ]);
        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'category' => $category]);
        }
        return redirect()->route('categories.index')->with('success', 'Category added successfully.');
    }

    // Update an existing category
    public function update(Request $request, Category $category)
    {
        $request->validate([
            'name' => 'required|string',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:categories,id',
        ]);
        // uniqueness check via model connection
        if (Category::where('name', $request->name)->where('id', '!=', $category->id)->exists()) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'errors' => ['name' => ['The name has already been taken.']]], 422);
            }
            return redirect()->back()->withErrors(['name' => 'The name has already been taken.'])->withInput();
        }

        $category->update([
            'name' => $request->name,
            'description' => $request->description,
            'parent_id' => $request->parent_id,
        ]);
        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'category' => $category]);
        }
        return redirect()->route('categories.index')->with('success', 'Category updated successfully.');
    }

    public function destroy(Category $category)
    {
        $category->delete();
        if (request()->expectsJson()) {
            return response()->json(['success' => true]);
        }
        return redirect()->route('categories.index')->with('success', 'Category deleted successfully.');
    }

    /**
     * Get categories with proper hierarchy for General Task page
     * Main areas (parent_id = null) with their sub-areas
     */
    public function getHierarchy()
    {
        // Get main areas (no parent)
        $mainAreas = Category::whereNull('parent_id')->get();
        
        $hierarchy = [];
        foreach ($mainAreas as $mainArea) {
            // Get sub-areas for this main area
            $subAreas = Category::where('parent_id', $mainArea->id)->get();
            
            $hierarchy[] = [
                'id' => $mainArea->id,
                'name' => $mainArea->name,
                'description' => $mainArea->description,
                'is_main_area' => true,
                'sub_areas' => $subAreas->map(function($subArea) {
                    return [
                        'id' => $subArea->id,
                        'name' => $subArea->name,
                        'description' => $subArea->description,
                        'parent_id' => $subArea->parent_id,
                        'is_main_area' => false,
                    ];
                })
            ];
        }
        
        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'hierarchy' => $hierarchy
            ]);
        }
        
        return $hierarchy;
    }

    /**
     * Get main areas only (for dropdown in forms)
     */
    public function getMainAreas()
    {
        $mainAreas = Category::whereNull('parent_id')->get();
        
        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'main_areas' => $mainAreas
            ]);
        }
        
        return $mainAreas;
    }

    /**
     * Get sub areas with their checklists for template dropdown
     */
    public function getSubAreasWithChecklists()
    {
        $subAreas = Category::with('checklist')
            ->whereNotNull('parent_id')
            ->get();
        
        return response()->json($subAreas);
    }
}