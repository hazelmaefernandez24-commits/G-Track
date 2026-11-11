<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\BroadcastRecipient;
use App\Models\Conversation;
use App\Models\SosAlert;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

        $notifications = collect();

        $broadcasts = BroadcastRecipient::with('broadcast.admin')
            ->where('student_id', $student->id)
            ->get()
            ->map(function (BroadcastRecipient $recipient) use ($student) {
                $broadcast = $recipient->broadcast;

                return (object) [
                    'id' => $broadcast->id,
                    'student_id' => $student->id,
                    'admin_id' => $broadcast->admin_id,
                    'type' => 'broadcast',
                    'subject' => $broadcast->subject,
                    'message' => $broadcast->message,
                    'sender_type' => 'admin',
                    'sender_name' => $broadcast->sender_name,
                    'read' => $recipient->read_at !== null,
                    'status' => 'sent',
                    'created_at' => $broadcast->created_at,
                ];
            });
        $notifications = $notifications->concat($broadcasts);

        $alerts = SosAlert::where('student_id', $student->id)
            ->get()
            ->map(fn (SosAlert $alert) => (object) [
                'id' => $alert->id,
                'student_id' => $student->id,
                'type' => 'sos',
                'subject' => 'SOS Alert',
                'message' => $alert->description,
                'sender_type' => 'student',
                'sender_name' => $student->name,
                'read' => $alert->read_at !== null,
                'status' => $alert->status,
                'created_at' => $alert->created_at,
            ]);
        $notifications = $notifications->concat($alerts);

        $conversations = Conversation::with(['messages', 'admin'])
            ->where('student_id', $student->id)
            ->when($user && $user->role === 'education', fn ($query) => $query->where('admin_id', $user->getKey()))
            ->get();
        $messages = $conversations->flatMap(function (Conversation $conversation) use ($student) {
            return $conversation->messages->map(function ($message) use ($conversation, $student) {
                $isStudentMessage = $message->sender_type === 'student';

                return (object) [
                    'id' => $message->id,
                    'student_id' => $student->id,
                    'admin_id' => $conversation->admin_id,
                    'type' => $isStudentMessage ? 'student_message' : 'admin_reply',
                    'subject' => $conversation->subject,
                    'message' => $message->message,
                    'sender_type' => $message->sender_type,
                    'sender_name' => $isStudentMessage
                        ? $student->name
                        : ($conversation->admin?->first_name ?? 'Admin'),
                    'read' => $message->read_at !== null,
                    'status' => 'sent',
                    'created_at' => $message->created_at,
                ];
            });
        });
        $notifications = $notifications->concat($messages)->sortByDesc('created_at')->values();

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
                'name' => trim(implode(' ', array_filter([
                    $admin->first_name,
                    $admin->middle_initial ? $admin->middle_initial . '.' : null,
                    $admin->last_name,
                ]))),
                'email' => $admin->email,
                'role' => $admin->role,
                'staff_id' => $admin->staff_id,
            ]);

        return response()->json(['success' => true, 'admins' => $admins]);
    }
}
