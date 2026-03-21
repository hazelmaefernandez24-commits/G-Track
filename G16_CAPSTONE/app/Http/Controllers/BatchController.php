<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Batch;
use App\Models\StudentDetail;
use App\Models\PNUser;

class BatchController extends Controller
{
    // Display all batches
    public function index()
    {
        // Prefer canonical batches from Login student_details for display
        $batches = \App\Models\StudentDetail::select('batch')
            ->distinct()
            ->orderBy('batch')
            ->get()
            ->map(function($r){ return (object)['year' => $r->batch, 'display_name' => (string)$r->batch]; });

        return view('batches.index', compact('batches'));
    }

    // Store new batch
    public function store(Request $request)
    {
        $request->validate([
            'year' => 'required|integer|min:2020|max:2050|unique:batches,year',
            'name' => 'nullable|string|max:255',
        ]);

        $batch = Batch::create([
            'year' => $request->year,
            'name' => $request->name,
            'is_active' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => "Batch {$batch->year} added successfully",
            'batch' => $batch
        ]);
    }

    // Update batch
    public function update(Request $request, Batch $batch)
    {
        $request->validate([
            'name' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        $batch->update($request->only(['name', 'is_active']));

        return response()->json([
            'success' => true,
            'message' => "Batch {$batch->year} updated successfully",
            'batch' => $batch
        ]);
    }

    // Delete batch (only if no students)
    public function destroy(Batch $batch)
    {
    // Count students from Login's StudentDetail linked to PNUser
    $studentCount = StudentDetail::where('batch', $batch->year)->count();
        
        if ($studentCount > 0) {
            return response()->json([
                'success' => false,
                'message' => "Cannot delete batch {$batch->year}. It has {$studentCount} students assigned."
            ], 400);
        }

        $year = $batch->year;
        $batch->delete();

        return response()->json([
            'success' => true,
            'message' => "Batch {$year} deleted successfully"
        ]);
    }

    // Get active batches for API
    public function getActiveBatches()
    {
        $batches = \App\Models\StudentDetail::select('batch')
            ->distinct()
            ->orderBy('batch')
            ->get()
            ->map(function($r){ return (object)['year' => $r->batch, 'display_name' => (string)$r->batch]; });

        return response()->json($batches);
    }
}