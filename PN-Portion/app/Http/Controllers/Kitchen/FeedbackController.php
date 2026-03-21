<?php

namespace App\Http\Controllers\Kitchen;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use App\Models\Meal;
use Illuminate\Http\Request;
use Carbon\Carbon;

class FeedbackController extends Controller
{
    /**
     * Display feedback for kitchen team
     */
    public function index(Request $request)
    {
        $query = Feedback::with(['student', 'meal'])
            ->orderBy('created_at', 'desc');

        // Filter by date range if provided
        if ($request->has('date_from') && $request->date_from) {
            $query->where('meal_date', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to) {
            $query->where('meal_date', '<=', $request->date_to);
        }

        // Filter by meal type if provided
        if ($request->has('meal_type') && $request->meal_type) {
            $query->where('meal_type', $request->meal_type);
        }

        // Filter by rating if provided
        if ($request->has('rating') && $request->rating) {
            $query->where('rating', $request->rating);
        }

        // Filter by anonymous status
        if ($request->has('anonymous_filter')) {
            if ($request->anonymous_filter === 'anonymous') {
                $query->where('is_anonymous', true);
            } elseif ($request->anonymous_filter === 'identified') {
                $query->where('is_anonymous', false);
            }
        }

        // Search in comments and suggestions
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('comments', 'like', "%{$search}%")
                  ->orWhere('suggestions', 'like', "%{$search}%");
            });
        }

        $feedbacks = $query->paginate(15)->appends($request->query());

        // Get enhanced feedback statistics
        $stats = [
            'total_feedback' => Feedback::count(),
            'average_rating' => round(Feedback::avg('rating'), 1),
            'recent_feedback' => Feedback::where('created_at', '>=', Carbon::now()->subDays(7))->count(),
            'anonymous_feedback' => Feedback::where('is_anonymous', true)->count(),
            'identified_feedback' => Feedback::where('is_anonymous', false)->count(),
            'rating_distribution' => [
                5 => Feedback::where('rating', 5)->count(),
                4 => Feedback::where('rating', 4)->count(),
                3 => Feedback::where('rating', 3)->count(),
                2 => Feedback::where('rating', 2)->count(),
                1 => Feedback::where('rating', 1)->count(),
            ],
            'meal_type_stats' => [
                'breakfast' => [
                    'count' => Feedback::where('meal_type', 'breakfast')->count(),
                    'avg_rating' => round(Feedback::where('meal_type', 'breakfast')->avg('rating'), 1)
                ],
                'lunch' => [
                    'count' => Feedback::where('meal_type', 'lunch')->count(),
                    'avg_rating' => round(Feedback::where('meal_type', 'lunch')->avg('rating'), 1)
                ],
                'dinner' => [
                    'count' => Feedback::where('meal_type', 'dinner')->count(),
                    'avg_rating' => round(Feedback::where('meal_type', 'dinner')->avg('rating'), 1)
                ]
            ]
        ];

        return view('kitchen.feedback.index', compact('feedbacks', 'stats'));
    }
    
    /**
     * Show detailed feedback for a specific meal
     */
    public function show($id)
    {
        $feedback = Feedback::with(['student', 'meal'])->findOrFail($id);
        
        return view('kitchen.feedback.show', compact('feedback'));
    }

    /**
     * Delete a single feedback entry
     */
    public function destroy($id)
    {
        $feedback = Feedback::findOrFail($id);
        $feedback->delete();

        if (request()->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Feedback deleted.']);
        }
        return redirect()->back()->with('success', 'Feedback deleted.');
    }

    /**
     * Delete all feedback entries
     */
    public function destroyAll()
    {
        Feedback::query()->delete();

        if (request()->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'All feedback deleted.']);
        }
        return redirect()->back()->with('success', 'All feedback deleted.');
    }
}
