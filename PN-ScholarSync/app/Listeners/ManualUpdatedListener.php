<?php

namespace App\Listeners;

use App\Events\ManualUpdated;
use App\Http\Controllers\NotificationController;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class ManualUpdatedListener
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
    public function handle(ManualUpdated $event): void
    {
        try {
            // Get all students
            $students = User::where('user_role', 'student')->get();
            
            if ($students->isEmpty()) {
                Log::info('No students found for manual update notification');
                return;
            }

            // Generate notification content based on update type
            $notificationData = $this->generateNotificationContent($event->updateType, $event->updateDetails);
            
            // Create notifications for all students
            $notificationsCreated = 0;
            foreach ($students as $student) {
                $success = NotificationController::createNotification(
                    $student->user_id,
                    $notificationData['title'],
                    $notificationData['message'],
                    $notificationData['type'],
                    null // No specific related_id for manual updates
                );
                
                if ($success) {
                    $notificationsCreated++;
                }
            }

            Log::info('Manual update notifications created', [
                'update_type' => $event->updateType,
                'students_notified' => $notificationsCreated,
                'total_students' => $students->count()
            ]);

        } catch (\Exception $e) {
            Log::error('Error creating manual update notifications: ' . $e->getMessage(), [
                'update_type' => $event->updateType ?? null,
                'update_details' => $event->updateDetails ?? null,
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Generate notification content based on update type
     */
    private function generateNotificationContent($updateType, $updateDetails)
    {
        switch ($updateType) {
            case 'manual_update':
                return [
                    'title' => 'Student Code of Conduct Updated',
                    'message' => 'The Student Code of Conduct has been updated with new rules and guidelines. Please review the updated manual to stay informed about the latest policies and expectations.',
                    'type' => 'info'
                ];
                
            case 'new_violation_type':
                $violationName = $updateDetails['violation_name'] ?? 'new violation';
                return [
                    'title' => 'New Violation Type Added',
                    'message' => "A new violation type '{$violationName}' has been added to the Student Code of Conduct. Please review the updated manual to understand the new rules and their consequences.",
                    'type' => 'warning'
                ];
                
            case 'category_change':
                $categoryName = $updateDetails['category_name'] ?? 'violation category';
                $action = $updateDetails['action'] ?? 'updated';
                return [
                    'title' => 'Violation Category Updated',
                    'message' => "The violation category '{$categoryName}' has been {$action} in the Student Code of Conduct. Please review the updated manual for the latest information.",
                    'type' => 'info'
                ];
                
            case 'severity_config_update':
                return [
                    'title' => 'Penalty Rules Updated',
                    'message' => 'The penalty rules and severity configurations have been updated in the Student Code of Conduct. Please review the updated manual to understand how violations are now penalized.',
                    'type' => 'warning'
                ];
                
            default:
                return [
                    'title' => 'Student Manual Updated',
                    'message' => 'The Student Code of Conduct has been updated. Please review the manual for the latest information and guidelines.',
                    'type' => 'info'
                ];
        }
    }
}
