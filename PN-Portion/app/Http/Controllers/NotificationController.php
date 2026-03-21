<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Get notifications for the authenticated user
     */
    public function index(Request $request)
    {
        $query = Notification::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc');

        // Filter by read status
        if ($request->has('unread') && $request->unread == 'true') {
            $query->whereNull('read_at');
        }

        // Filter by type
        if ($request->has('type') && $request->type) {
            $query->where('type', $request->type);
        }

        $notifications = $query->paginate(20);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'notifications' => $notifications->items(),
                'unread_count' => $this->getUnreadCount(),
                'pagination' => [
                    'current_page' => $notifications->currentPage(),
                    'last_page' => $notifications->lastPage(),
                    'total' => $notifications->total()
                ]
            ]);
        }

        return view('notifications.index', compact('notifications'));
    }

    /**
     * Get unread notifications count
     */
    public function getUnreadCount()
    {
        return Notification::where('user_id', Auth::id())
            ->whereNull('read_at')
            ->count();
    }

    /**
     * Get recent notifications for dropdown
     */
    public function getRecent()
    {
        $notifications = Notification::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $unreadCount = $this->getUnreadCount();

        return response()->json([
            'success' => true,
            'notifications' => $notifications,
            'unread_count' => $unreadCount
        ]);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead($id)
    {
        $notification = Notification::where('user_id', Auth::id())
            ->where('id', $id)
            ->first();

        if ($notification) {
            $notification->markAsRead();
            
            return response()->json([
                'success' => true,
                'message' => 'Notification marked as read'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Notification not found'
        ], 404);
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead()
    {
        Notification::where('user_id', Auth::id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => 'All notifications marked as read'
        ]);
    }

    /**
     * Delete notification
     */
    public function destroy($id)
    {
        $notification = Notification::where('user_id', Auth::id())
            ->where('id', $id)
            ->first();

        if ($notification) {
            $notification->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Notification deleted'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Notification not found'
        ], 404);
    }

    /**
     * Get notification statistics
     */
    public function getStats()
    {
        $userId = Auth::id();
        
        $stats = [
            'total' => Notification::where('user_id', $userId)->count(),
            'unread' => Notification::where('user_id', $userId)->whereNull('read_at')->count(),
            'today' => Notification::where('user_id', $userId)->whereDate('created_at', today())->count(),
            'this_week' => Notification::where('user_id', $userId)->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
        ];

        // Get notifications by type
        $byType = Notification::where('user_id', $userId)
            ->selectRaw('type, COUNT(*) as count')
            ->groupBy('type')
            ->pluck('count', 'type')
            ->toArray();

        return response()->json([
            'success' => true,
            'stats' => $stats,
            'by_type' => $byType
        ]);
    }

    /**
     * Get feature-based notification status
     */
    public function getFeatureStatus()
    {
        $userId = Auth::id();
        $userRole = Auth::user()->role;

        // Feature mapping for different user roles
        $featureMap = [
            'cook' => [
                'cook.inventory' => ['inventory_report', 'low_stock'],
                'cook.feedback' => ['feedback_submitted'],
                'cook.post-assessment' => ['post_meal_report'],
                'cook.pre-orders' => ['poll_response']
            ],
            'kitchen' => [
                'kitchen.daily-menu' => ['menu_update'],
                'kitchen.inventory' => ['inventory_approved', 'low_stock'],
                'kitchen.feedback' => ['feedback_submitted'],
                'kitchen.pre-orders' => ['poll_response']
            ],
            'student' => [
                'student.menu' => ['menu_update'],
                'student.pre-order' => ['poll_created'],
                'student.feedback' => ['system_update']
            ]
        ];

        $features = [];
        $newNotifications = [];

        if (isset($featureMap[$userRole])) {
            foreach ($featureMap[$userRole] as $route => $types) {
                $count = Notification::where('user_id', $userId)
                    ->whereIn('type', $types)
                    ->whereNull('read_at')
                    ->count();

                $features[$route] = $count;

                // Get new notifications (created in last 2 minutes for better detection)
                $recent = Notification::where('user_id', $userId)
                    ->whereIn('type', $types)
                    ->whereNull('read_at')
                    ->where('created_at', '>=', now()->subMinutes(2))
                    ->orderBy('created_at', 'desc')
                    ->get();

                $newNotifications = array_merge($newNotifications, $recent->toArray());
            }
        }

        return response()->json([
            'success' => true,
            'features' => $features,
            'new_notifications' => $newNotifications
        ]);
    }

    /**
     * Mark feature notifications as read
     */
    public function markFeatureAsRead(Request $request)
    {
        $userId = Auth::id();
        $userRole = Auth::user()->role;
        $route = $request->input('route');

        // Feature mapping
        $featureMap = [
            'cook' => [
                'cook.inventory' => ['inventory_report', 'low_stock'],
                'cook.feedback' => ['feedback_submitted'],
                'cook.post-assessment' => ['post_meal_report'],
                'cook.pre-orders' => ['poll_response']
            ],
            'kitchen' => [
                'kitchen.daily-menu' => ['menu_update'],
                'kitchen.inventory' => ['inventory_approved', 'low_stock'],
                'kitchen.feedback' => ['feedback_submitted'],
                'kitchen.pre-orders' => ['poll_response']
            ],
            'student' => [
                'student.menu' => ['menu_update'],
                'student.pre-order' => ['poll_created'],
                'student.feedback' => ['system_update']
            ]
        ];

        if (isset($featureMap[$userRole][$route])) {
            $types = $featureMap[$userRole][$route];

            Notification::where('user_id', $userId)
                ->whereIn('type', $types)
                ->whereNull('read_at')
                ->update(['read_at' => now()]);

            return response()->json([
                'success' => true,
                'message' => 'Feature notifications marked as read'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Invalid feature route'
        ], 400);
    }

    /**
     * Test notification (for development)
     */
    public function test(Request $request)
    {
        if (app()->environment('local')) {
            $type = $request->input('type', 'test');
            $title = $request->input('title', 'Test Notification');
            $message = $request->input('message', 'This is a test notification to verify the system is working.');

            Notification::create([
                'user_id' => Auth::id(),
                'title' => $title,
                'message' => $message,
                'type' => $type,
                'data' => [
                    'test' => true,
                    'created_at' => now()->toISOString()
                ],
                'read_at' => null
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Test notification created',
                'notification' => [
                    'type' => $type,
                    'title' => $title,
                    'message' => $message
                ]
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Test notifications only available in development'
        ], 403);
    }
}
