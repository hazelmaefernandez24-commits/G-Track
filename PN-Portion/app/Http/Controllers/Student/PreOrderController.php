<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\PreOrder;
use App\Models\KitchenMenuPoll;
use App\Models\KitchenPollResponse;
use App\Models\User;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PreOrderController extends Controller
{
    /**
     * Display a listing of the student pre-orders.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = Auth::user();
        
        // Get start and end date for the week (today + 7 days)
        $startDate = Carbon::today();
        $endDate = Carbon::today()->addDays(7);
        
        // Get menu items for the next week
        $menuItems = Menu::whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->orderBy('date')
            ->orderBy('meal_type')
            ->get()
            ->groupBy('date');
        
        // Get student's pre-orders (meal attendance responses)
        $studentPreOrders = PreOrder::where('user_id', $user->user_id)
            ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->get()
            ->keyBy(function ($item) {
                return $item->date . '_' . $item->meal_type;
            });
        
        // Define cutoff times for each meal type
        $cutoffTimes = [
            'breakfast' => Carbon::today()->setHour(18)->setMinute(0), // 6 PM the day before
            'lunch' => Carbon::today()->setHour(8)->setMinute(0),     // 8 AM same day
            'dinner' => Carbon::today()->setHour(14)->setMinute(0),   // 2 PM same day
        ];
        
        // Get active meal polls for the upcoming week
        $activeMealPolls = \App\Models\Announcement::where('is_active', true)
            ->where('is_poll', true)
            ->whereDate('expiry_date', '>=', $startDate)
            ->whereDate('expiry_date', '<=', $endDate)
            ->orderBy('created_at', 'desc')
            ->get();
            
        // Get student's responses to meal polls
        $pollResponses = [];
        foreach ($activeMealPolls as $poll) {
            $response = $poll->pollResponses()->where('user_id', $user->user_id)->first();
            if ($response) {
                $pollResponses[$poll->id] = $response->response;
            }
        }
        
        // Get food waste statistics to show impact of meal attendance tracking
        $wasteStats = [
            'weekly_reduction' => rand(15, 25), // Placeholder for actual waste reduction percentage
            'monthly_savings' => rand(500, 1500), // Placeholder for actual cost savings
            'contribution' => $studentPreOrders->where('is_attending', true)->count() * 0.5 // Each attendance response saves ~0.5kg of food waste
        ];
        
        return view('student.pre-order.index', compact(
            'menuItems', 
            'studentPreOrders', 
            'startDate', 
            'endDate', 
            'cutoffTimes',
            'activeMealPolls',
            'pollResponses',
            'wasteStats'
        ));
    }

    /**
     * Store a newly created pre-order in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'menu_id' => 'required|exists:menus,id',
            'date' => 'required|date',
            'meal_type' => 'required|in:breakfast,lunch,dinner',
            'notes' => 'nullable|string|max:255',
        ]);
        
        $user = Auth::user();
        $menu = Menu::findOrFail($request->menu_id);
        
        // Check if cutoff time has passed
        $now = Carbon::now();
        $orderDate = Carbon::parse($request->date);
        
        $cutoffPassed = false;
        
        if ($request->meal_type === 'breakfast') {
            // Cutoff for breakfast is 6 PM the day before
            $cutoffTime = Carbon::parse($request->date)->subDay()->setHour(18)->setMinute(0);
            $cutoffPassed = $now->greaterThan($cutoffTime);
        } elseif ($request->meal_type === 'lunch') {
            // Cutoff for lunch is 8 AM the same day
            $cutoffTime = Carbon::parse($request->date)->setHour(8)->setMinute(0);
            $cutoffPassed = $now->greaterThan($cutoffTime);
        } elseif ($request->meal_type === 'dinner') {
            // Cutoff for dinner is 2 PM the same day
            $cutoffTime = Carbon::parse($request->date)->setHour(14)->setMinute(0);
            $cutoffPassed = $now->greaterThan($cutoffTime);
        }
        
        // Check if user already has a pre-order for this date and meal type
        $existingPreOrder = PreOrder::where('user_id', $user->user_id)
            ->where('date', $request->date)
            ->where('meal_type', $request->meal_type)
            ->first();
            
        if ($existingPreOrder) {
            // Update existing pre-order instead of showing error
            $existingPreOrder->update([
                'menu_id' => $request->menu_id,
                'is_attending' => $request->is_attending,
                'special_requests' => $request->special_requests,
            ]);
            
            $message = $request->is_attending ? 
                'Your meal attendance has been updated. Thank you for helping reduce food waste!' : 
                'Your meal attendance has been updated. Thanks for letting us know you won\'t be attending.';
                
            return redirect()->back()->with('success', $message);
        }
        
        // Create new pre-order
        $preOrder = PreOrder::create([
            'user_id' => Auth::user()->user_id,
            'date' => $request->date,
            'meal_type' => $request->meal_type,
            'menu_id' => $request->menu_id,
            'is_attending' => $request->is_attending,
            'special_requests' => $request->special_requests,
        ]);
        
        // Calculate impact on food waste reduction
        $wasteReduction = 0.5; // Approximate kg of food saved by accurate attendance tracking
        $costSaving = 2.50; // Approximate cost saving per accurate attendance record
        
        $message = $request->is_attending ? 
            "Your meal attendance has been recorded. You've helped save approximately {$wasteReduction}kg of food waste!" : 
            "Thanks for letting us know you won't be attending. This helps us prepare the right amount of food.";
        
        return redirect()->back()->with('success', $message);
    }

    /**
     * Update the specified pre-order in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $preOrder = PreOrder::findOrFail($id);
        
        // Ensure the pre-order belongs to the authenticated user
        if ($preOrder->user_id !== Auth::user()->user_id) {
            return redirect()->route('student.pre-order.index')
                ->with('error', 'You are not authorized to update this pre-order.');
        }
        
        // Check if cutoff time has passed
        $now = Carbon::now();
        $orderDate = Carbon::parse($preOrder->date);
        
        $cutoffPassed = false;
        
        if ($preOrder->meal_type === 'breakfast') {
            // Cutoff for breakfast is 6 PM the day before
            $cutoffTime = Carbon::parse($preOrder->date)->subDay()->setHour(18)->setMinute(0);
            $cutoffPassed = $now->greaterThan($cutoffTime);
        } elseif ($preOrder->meal_type === 'lunch') {
            // Cutoff for lunch is 8 AM the same day
            $cutoffTime = Carbon::parse($preOrder->date)->setHour(8)->setMinute(0);
            $cutoffPassed = $now->greaterThan($cutoffTime);
        } elseif ($preOrder->meal_type === 'dinner') {
            // Cutoff for dinner is 2 PM the same day
            $cutoffTime = Carbon::parse($preOrder->date)->setHour(14)->setMinute(0);
            $cutoffPassed = $now->greaterThan($cutoffTime);
        }
        
        if ($cutoffPassed) {
            return redirect()->route('student.pre-order.index')
                ->with('error', 'The cutoff time for this meal has passed. You cannot update your pre-order.');
        }
        
        // Update pre-order
        $preOrder->update([
            'is_attending' => $request->has('is_attending') ? $request->is_attending : $preOrder->is_attending,
            'notes' => $request->has('notes') ? $request->notes : $preOrder->notes,
        ]);
        
        return redirect()->route('student.pre-order.index')
            ->with('success', 'Your pre-order has been updated successfully!');
    }

    // REMOVED: history() method - Pre-order history functionality deleted
    
    /**
     * Display the student's meal spending dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function dashboard()
    {
        $user = auth()->user();
        
        // Get current month's data
        $currentMonth = Carbon::now()->format('F Y');
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();
        
        // Get previous month's data
        $previousMonth = Carbon::now()->subMonth()->format('F Y');
        $startOfPrevMonth = Carbon::now()->subMonth()->startOfMonth();
        $endOfPrevMonth = Carbon::now()->subMonth()->endOfMonth();
        
        // Calculate meal totals for current month
        $currentBreakfastTotal = PreOrder::where('user_id', $user->user_id)
            ->whereBetween('date', [$startOfMonth->format('Y-m-d'), $endOfMonth->format('Y-m-d')])
            ->where('meal_type', 'breakfast')
            ->where('is_attending', true)
            ->count();

        $currentLunchTotal = PreOrder::where('user_id', $user->user_id)
            ->whereBetween('date', [$startOfMonth->format('Y-m-d'), $endOfMonth->format('Y-m-d')])
            ->where('meal_type', 'lunch')
            ->where('is_attending', true)
            ->count();

        $currentDinnerTotal = PreOrder::where('user_id', $user->user_id)
            ->whereBetween('date', [$startOfMonth->format('Y-m-d'), $endOfMonth->format('Y-m-d')])
            ->where('meal_type', 'dinner')
            ->where('is_attending', true)
            ->count();
        
        // Calculate meal totals for previous month
        $prevBreakfastTotal = PreOrder::where('user_id', $user->user_id)
            ->whereBetween('date', [$startOfPrevMonth->format('Y-m-d'), $endOfPrevMonth->format('Y-m-d')])
            ->where('meal_type', 'breakfast')
            ->where('is_attending', true)
            ->count();

        $prevLunchTotal = PreOrder::where('user_id', $user->user_id)
            ->whereBetween('date', [$startOfPrevMonth->format('Y-m-d'), $endOfPrevMonth->format('Y-m-d')])
            ->where('meal_type', 'lunch')
            ->where('is_attending', true)
            ->count();

        $prevDinnerTotal = PreOrder::where('user_id', $user->user_id)
            ->whereBetween('date', [$startOfPrevMonth->format('Y-m-d'), $endOfPrevMonth->format('Y-m-d')])
            ->where('meal_type', 'dinner')
            ->where('is_attending', true)
            ->count();
        
        // Get spending by day of week
        $spendingByDayOfWeek = [
            'Monday' => 0,
            'Tuesday' => 0,
            'Wednesday' => 0,
            'Thursday' => 0,
            'Friday' => 0,
            'Saturday' => 0,
            'Sunday' => 0
        ];
        
        // Get monthly spending history (last 6 months)
        $monthlySpendingHistory = [];
        
        // Define meal costs
        $mealCosts = [
            'breakfast' => 2.50,
            'lunch' => 5.00,
            'dinner' => 5.00
        ];
        
        return view('student.pre-order.dashboard', compact(
            'currentBreakfastTotal',
            'currentLunchTotal',
            'currentDinnerTotal',
            'prevBreakfastTotal',
            'prevLunchTotal',
            'prevDinnerTotal',
            'spendingByDayOfWeek',
            'monthlySpendingHistory',
            'mealCosts',
            'currentMonth',
            'previousMonth'
        ));
    }

    /**
     * Get active kitchen polls for students
     */
    public function getKitchenPolls()
    {
        try {
            $user = Auth::user();

            \Log::info('🔄 Student requesting kitchen polls (cycle-based)', [
                'user_id' => $user->id,
                'user_name' => $user->name,
                'current_date' => now()->format('Y-m-d'),
                'current_day' => strtolower(date('l')),
                'current_week_cycle' => (now()->weekOfMonth % 2 === 1) ? 1 : 2,
                'route_called' => 'getKitchenPolls',
                'controller' => 'Student\PreOrderController'
            ]);

            // Get active polls that students can respond to
            $activePolls = KitchenMenuPoll::where('is_active', true)
                ->where('poll_date', '>=', now()->format('Y-m-d'))
                ->orderBy('created_at', 'desc')
                ->get();

            \Log::info('📊 Found kitchen polls', [
                'total_polls' => $activePolls->count(),
                'poll_ids' => $activePolls->pluck('id')->toArray()
            ]);

            // Get student's existing responses
            $studentResponses = KitchenPollResponse::where('student_id', $user->user_id)
                ->whereIn('poll_id', $activePolls->pluck('id'))
                ->get()
                ->keyBy('poll_id');

            $formattedPolls = $activePolls->map(function ($poll) use ($studentResponses) {
                $response = $studentResponses->get($poll->id);

                \Log::info('📝 Formatting poll', [
                    'poll_id' => $poll->id,
                    'meal_name' => $poll->meal_name,
                    'poll_date' => $poll->poll_date,
                    'deadline' => $poll->deadline,
                    'has_response' => $response !== null
                ]);

                return [
                    'id' => $poll->id,
                    'meal_name' => $poll->meal_name,
                    'ingredients' => $poll->ingredients,
                    'poll_date' => $poll->poll_date->format('Y-m-d'),
                    'meal_type' => $poll->meal_type,
                    'deadline' => $poll->deadline->format('g:i A'), // 12-hour format
                    'deadline_formatted' => $poll->deadline->format('g:i A'), // 12-hour format
                    'status' => $poll->status,
                    'has_responded' => $response !== null,
                    'response' => $response ? $response->will_eat : null,
                    'response_notes' => $response ? $response->notes : null,
                    'can_respond' => $poll->poll_date >= now()->format('Y-m-d') &&
                                     now()->format('H:i:s') <= $poll->deadline->format('H:i:s')
                ];
            });

            return response()->json([
                'success' => true,
                'polls' => $formattedPolls
            ]);
        } catch (\Exception $e) {
            \Log::error('🚨 DEEP FIX: Failed to get kitchen polls for student', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'controller' => 'Student\PreOrderController',
                'method' => 'getKitchenPolls'
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to load polls: ' . $e->getMessage(),
                'debug' => [
                    'error_type' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]
            ], 500);
        }
    }

    /**
     * Respond to a kitchen poll
     */
    public function respondToKitchenPoll(Request $request, $pollId)
    {
        $request->validate([
            'will_eat' => 'required|boolean',
            'notes' => 'nullable|string|max:500'
        ]);

        try {
            $user = Auth::user();
            $poll = KitchenMenuPoll::findOrFail($pollId);

            // Check if poll is still active and deadline hasn't passed
            if (!in_array($poll->status, ['active', 'sent'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'This poll is no longer active.'
                ], 400);
            }

            // Check if deadline has passed (compare date and time separately)
            $pollDate = $poll->poll_date->format('Y-m-d');
            $currentDate = now()->format('Y-m-d');
            $currentTime = now()->format('H:i:s');

            if ($pollDate < $currentDate || ($pollDate === $currentDate && $poll->deadline->format('H:i:s') < $currentTime)) {
                return response()->json([
                    'success' => false,
                    'message' => 'The deadline for this poll has passed.'
                ], 400);
            }

            // Create or update response
            $response = KitchenPollResponse::updateOrCreate(
                [
                    'poll_id' => $pollId,
                    'student_id' => $user->user_id // Use the actual user_id primary key
                ],
                [
                    'will_eat' => $request->will_eat,
                    'notes' => $request->notes,
                    'responded_at' => now()
                ]
            );

            // Send notification to kitchen team
            $notificationService = new NotificationService();
            $notificationService->pollResponseSubmitted([
                'student_name' => $user->name,
                'meal_name' => $poll->meal_name,
                'poll_date' => $poll->poll_date->format('Y-m-d'),
                'response' => $request->will_eat ? 'yes' : 'no'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Your response has been recorded successfully!'
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to respond to kitchen poll', [
                'error' => $e->getMessage(),
                'poll_id' => $pollId,
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to submit response: ' . $e->getMessage()
            ], 500);
        }
    }
}
