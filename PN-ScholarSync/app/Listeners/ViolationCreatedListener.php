<?php

namespace App\Listeners;

use App\Events\ViolationCreated;
use App\Http\Controllers\NotificationController;
use App\Models\User;
use App\Models\StudentDetails;
use App\Models\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class ViolationCreatedListener
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(ViolationCreated $event): void
    {
        try {
            $violation = $event->violation;
            
            // Get the student's user information
            $studentDetails = StudentDetails::where('student_id', $violation->student_id)->first();
            
            if (!$studentDetails) {
                Log::warning('Student details not found for violation notification', [
                    'violation_id' => $violation->id,
                    'student_id' => $violation->student_id
                ]);
                return;
            }

            $user = User::where('user_id', $studentDetails->user_id)->first();
            
            if (!$user) {
                Log::warning('User not found for violation notification', [
                    'violation_id' => $violation->id,
                    'student_id' => $violation->student_id,
                    'user_id' => $studentDetails->user_id
                ]);
                return;
            }

            // Check if notification already exists for this violation
            $existingNotification = Notification::where('user_id', $user->user_id)
                ->where('related_id', $violation->id)
                ->where('type', 'warning')
                ->first();

            if ($existingNotification) {
                Log::info('Notification already exists for this violation', [
                    'violation_id' => $violation->id,
                    'user_id' => $user->user_id,
                    'existing_notification_id' => $existingNotification->id
                ]);
                return;
            }

            // Load violation type for better notification message
            $violation->load('violationType');

            // Create notification title and message
            $title = 'New Violation Recorded';
            $violationName = $violation->violationType->violation_name ?? 'Unknown Violation';
            $violationDate = $violation->violation_date ? date('M j, Y', strtotime($violation->violation_date)) : 'Today';

            $message = "A new violation has been recorded against you: {$violationName} on {$violationDate}. Please review your violation history and contact your educator if you have any questions.";

            // Create the notification
            NotificationController::createNotification(
                $user->user_id,
                $title,
                $message,
                'warning', // Use warning type for violations
                $violation->id
            );

            Log::info('Violation notification created successfully', [
                'violation_id' => $violation->id,
                'student_id' => $violation->student_id,
                'user_id' => $user->user_id
            ]);

        } catch (\Exception $e) {
            Log::error('Error creating violation notification: ' . $e->getMessage(), [
                'violation_id' => $event->violation->id ?? null,
                'student_id' => $event->violation->student_id ?? null,
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}
