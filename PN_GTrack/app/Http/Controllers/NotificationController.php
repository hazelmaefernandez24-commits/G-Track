<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class NotificationController extends Controller
{
    public function index(Request $request, ?string $screen = null)
    {
        $screen ??= match ($request->query('tab')) {
            'sos' => 'alerts',
            'broadcast' => 'broadcasts',
            default => 'messages',
        };

        return match ($screen) {
            'alerts' => app(SosAlertController::class)->index($request),
            'messages' => app(StudentMessageController::class)->index($request),
            'broadcasts' => app(BroadcastController::class)->index($request),
            default => abort(404),
        };
    }

    public function apiSend(Request $request)
    {
        $request->validate(['target' => 'required|in:sos,student_message,broadcast']);

        return match ($request->input('target')) {
            'sos' => app(SosAlertController::class)->apiSend($request),
            'student_message' => app(StudentMessageController::class)->apiSend($request),
            'broadcast' => app(BroadcastController::class)->apiSend($request),
        };
    }

    public function apiGet($studentId)
    {
        $student = Student::where('id', $studentId)
            ->orWhere('student_id', $studentId)
            ->first();

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Student not found',
                'notifications' => [],
            ], 404);
        }

        $user = Auth::guard('admin')->user();
        if ($user && $user->role === 'main') {
            return response()->json([
                'success' => false,
                'message' => 'Access denied',
                'notifications' => [],
            ], 403);
        }

        $notificationsQuery = DB::table('notifications')
            ->where(function ($query) use ($student) {
                $query->where('class', 'all')
                    ->orWhere('class', $student->class)
                    ->orWhere('student_id', $student->id);
            })
            ->whereIn('type', ['broadcast', 'sos', 'student_message', 'admin_reply']);

        if ($user && $user->role === 'education') {
            $notificationsQuery->where('admin_id', $user->getKey());
        }

        $notifications = $notificationsQuery->orderByDesc('created_at')->get();

        return response()->json([
            'success' => true,
            'student' => [
                'name' => $student->name,
                'class' => $student->class,
            ],
            'notifications' => $notifications,
        ]);
    }

    public function getAdmins()
    {
        $admins = Admin::select('id', 'staff_id', 'first_name', 'middle_initial', 'last_name', 'email', 'role')
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get()
            ->map(fn (Admin $admin) => [
                'id' => $admin->id,
                'name' => trim($admin->first_name . ' ' . $admin->last_name),
                'email' => $admin->email,
                'role' => $admin->role,
                'staff_id' => $admin->staff_id,
            ]);

        return response()->json(['success' => true, 'admins' => $admins]);
    }
}
