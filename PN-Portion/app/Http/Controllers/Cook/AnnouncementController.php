<?php

namespace App\Http\Controllers\Cook;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Announcement;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class AnnouncementController extends Controller
{
    /**
     * Display a listing of announcements.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Get active meal polls
        $activeMealPolls = Announcement::where('is_active', true)
            ->where('is_poll', true)
            ->orderBy('created_at', 'desc')
            ->get();
            
        // Get active general announcements
        $activeAnnouncements = Announcement::where('is_active', true)
            ->orderBy('created_at', 'desc')
            ->get();
            
        // Get inactive announcements
        $inactiveAnnouncements = Announcement::where('is_active', false)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
            
        // Get poll response statistics for active polls
        $pollStats = [];
        foreach ($activeMealPolls as $poll) {
            $responses = $poll->pollResponses;
            $stats = [
                'total' => $responses->count(),
                'by_option' => []
            ];
            
            if ($poll->poll_options) {
                $options = json_decode($poll->poll_options, true);
                foreach ($options as $option) {
                    $stats['by_option'][$option] = $responses->where('response', $option)->count();
                }
            }
            
            $pollStats[$poll->id] = $stats;
        }
            
        return view('cook.announcements', compact(
            'activeMealPolls',
            'activeAnnouncements',
            'inactiveAnnouncements',
            'pollStats'
        ));
    }
    
    /**
     * Store a newly created announcement in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'expiry_date' => 'required|date|after:today',
            'is_poll' => 'boolean',
            'poll_options' => 'required_if:is_poll,1|array|min:2',
            'poll_options.*' => 'required_if:is_poll,1|string|max:255',
            'meal_date' => 'required_if:is_poll,1|date|after_or_equal:today',
            'meal_type' => 'required_if:is_poll,1|in:breakfast,lunch,dinner',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Create default poll options if it's a meal poll
        $pollOptions = null;
        $title = $request->title;
        $content = $request->content;
        
        if ($request->has('is_poll')) {
            // If no custom options provided, use default attendance options
            if (empty($request->poll_options)) {
                $pollOptions = json_encode(['Will Attend', 'Will Not Attend', 'Undecided']);
            } else {
                $pollOptions = json_encode($request->poll_options);
            }
            
            // If it's a meal poll, format the title and content appropriately
            if ($request->has('meal_date') && $request->has('meal_type')) {
                $mealDate = date('l, F j, Y', strtotime($request->meal_date));
                $mealType = ucfirst($request->meal_type);
                
                // If no custom title provided, generate one
                if (empty($request->title) || $request->title == 'Meal Attendance Poll') {
                    $title = "$mealType Attendance Poll for $mealDate";
                }
                
                // If no custom content provided, generate one
                if (empty($request->content) || $request->content == 'Please indicate if you will be attending this meal.') {
                    $content = "Please indicate if you will be attending $mealType on $mealDate. This helps us prepare the right amount of food and reduce waste.";
                }
            }
        }

        $announcement = Announcement::create([
            'title' => $title,
            'content' => $content,
            'user_id' => Auth::id(),
            'expiry_date' => $request->expiry_date,
            'is_active' => true,
            'is_poll' => $request->has('is_poll'),
            'poll_options' => $pollOptions,
            'meta_data' => $request->has('is_poll') && $request->has('meal_date') && $request->has('meal_type') ? 
                json_encode(['meal_date' => $request->meal_date, 'meal_type' => $request->meal_type]) : null,
        ]);

        $successMessage = $request->has('is_poll') ? 
            'Meal attendance poll created successfully.' : 
            'Announcement created successfully.';

        return redirect()->route('cook.announcements')
            ->with('success', $successMessage);
    }
    
    /**
     * Update the specified announcement in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'expiry_date' => 'required|date',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $announcement = Announcement::findOrFail($id);
        $announcement->update([
            'title' => $request->title,
            'content' => $request->content,
            'expiry_date' => $request->expiry_date,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('cook.announcements')
            ->with('success', 'Announcement updated successfully.');
    }
}
