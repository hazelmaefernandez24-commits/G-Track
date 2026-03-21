<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SystemSetting;

class SystemSettingsController extends Controller
{
    /**
     * Get assignment duration setting
     */
    public function getAssignmentDuration()
    {
        $duration = SystemSetting::get('assignment_duration_days', 7);
        
        return response()->json([
            'success' => true,
            'duration_days' => $duration
        ]);
    }

    /**
     * Update assignment duration setting
     */
    public function updateAssignmentDuration(Request $request)
    {
        $request->validate([
            'duration_days' => 'required|integer|min:0|max:365'
        ]);

        $oldDuration = SystemSetting::get('assignment_duration_days', 7);
        $newDuration = $request->duration_days;

        SystemSetting::set(
            'assignment_duration_days',
            $newDuration,
            'integer',
            'Default duration in days for task assignments. Set to 0 to allow auto-shuffle anytime.'
        );

        // Update end dates of existing current assignments to reflect new duration
        // This preserves the assignments but updates when they expire
        try {
            $currentAssignments = \App\Models\Assignment::where('status', 'current')->get();
            
            foreach ($currentAssignments as $assignment) {
                $startDate = \Carbon\Carbon::parse($assignment->start_date);
                
                // Calculate new end date based on new duration
                if ($newDuration == 0) {
                    // Duration 0 = anytime shuffle, set end date far in future
                    $newEndDate = $startDate->copy()->addYear();
                } else {
                    // Set end date based on new duration from start date
                    $newEndDate = $startDate->copy()->addDays($newDuration);
                }
                
                $assignment->end_date = $newEndDate->toDateString();
                $assignment->save();
                
                \Log::info("Updated assignment #{$assignment->id} end_date to {$newEndDate->toDateString()} (duration changed from {$oldDuration} to {$newDuration} days)");
            }
            
            $updatedCount = $currentAssignments->count();
            $message = $newDuration == 0 
                ? "✅ Duration set to 0 days - Auto-shuffle can run anytime! Updated {$updatedCount} existing assignments."
                : "✅ Duration updated to {$newDuration} days! Updated {$updatedCount} existing assignments to expire in {$newDuration} days from their start date.";
                
        } catch (\Exception $e) {
            \Log::error("Error updating existing assignment end dates: " . $e->getMessage());
            $message = $newDuration == 0 
                ? "Assignment duration set to 0 days - Auto-shuffle can run anytime!"
                : "Assignment duration updated to {$newDuration} days successfully!";
        }

        return response()->json([
            'success' => true,
            'message' => $message
        ]);
    }
}
