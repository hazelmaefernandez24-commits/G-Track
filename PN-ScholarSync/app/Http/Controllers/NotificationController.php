<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class NotificationController extends Controller
{
    /**
     * Get notifications for the authenticated user
     */
    public function index(Request $request)
    {
        try {
            $user = Auth::user();
            
            // Debug logging
            Log::info('NotificationController::index called', [
                'user' => $user ? $user->toArray() : 'null',
                'user_id' => $user ? $user->user_id : 'null'
            ]);
            
            if (!$user) {
                Log::error('No authenticated user found');
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }
            
            $perPage = $request->get('per_page', 10);
            
            $notifications = Notification::where('user_id', $user->user_id)
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);

            Log::info('Notifications fetched successfully', [
                'count' => $notifications->count(),
                'total' => $notifications->total(),
                'user_id' => $user->user_id
            ]);

            // Return success even if no notifications found
            return response()->json([
                'success' => true,
                'notifications' => $notifications,
                'message' => $notifications->count() > 0 ? 'Notifications loaded' : 'No notifications found'
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching notifications: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch notifications: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mark a notification as read
     */
    public function markAsRead($id)
    {
        try {
            $user = Auth::user();
            
            $notification = Notification::where('id', $id)
                ->where('user_id', $user->user_id)
                ->first();

            if (!$notification) {
                return response()->json([
                    'success' => false,
                    'message' => 'Notification not found'
                ], 404);
            }

            $notification->update(['is_read' => true]);

            return response()->json([
                'success' => true,
                'message' => 'Notification marked as read'
            ]);
        } catch (\Exception $e) {
            Log::error('Error marking notification as read: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark notification as read'
            ], 500);
        }
    }

    /**
     * Mark all notifications as read for the authenticated user
     */
    public function markAllAsRead()
    {
        try {
            $user = Auth::user();
            
            Notification::where('user_id', $user->user_id)
                ->where('is_read', false)
                ->update(['is_read' => true]);

            return response()->json([
                'success' => true,
                'message' => 'All notifications marked as read'
            ]);
        } catch (\Exception $e) {
            Log::error('Error marking all notifications as read: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark all notifications as read'
            ], 500);
        }
    }

    /**
     * Get unread notification count for the authenticated user
     */
    public function getUnreadCount()
    {
        try {
            $user = Auth::user();
            
            $count = Notification::where('user_id', $user->user_id)
                ->where('is_read', false)
                ->count();

            return response()->json([
                'success' => true,
                'count' => $count
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting unread notification count: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to get notification count'
            ], 500);
        }
    }

    /**
     * Create a notification for a user
     */
    public static function createNotification($userId, $title, $message, $type = 'info', $relatedId = null)
    {
        try {
            Notification::create([
                'user_id' => $userId,
                'title' => $title,
                'message' => $message,
                'type' => $type,
                'related_id' => $relatedId,
                'is_read' => false
            ]);

            Log::info('Notification created', [
                'user_id' => $userId,
                'title' => $title,
                'type' => $type
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Error creating notification: ' . $e->getMessage(), [
                'user_id' => $userId,
                'title' => $title
            ]);
            return false;
        }
    }

    /**
     * Render a notifications page (HTML) for the authenticated user
     */
    public function page(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $perPage = (int) $request->get('per_page', 15);
        $notifications = Notification::where('user_id', $user->user_id)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        // unread badge for layout
        $unreadCount = Notification::where('user_id', $user->user_id)
            ->where('is_read', false)
            ->count();

        // Render appropriate view based on role
        if (strtolower($user->user_role ?? '') === 'student') {
            return view('student.notifications', compact('notifications', 'unreadCount'));
        }
        return view('educator.notifications', compact('notifications', 'unreadCount'));
    }
}
