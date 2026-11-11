<?php

namespace App\Http\Controllers;

use App\Models\BatchClass;
use App\Models\SosAlert;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

abstract class NotificationScreenController extends Controller
{
    protected function renderMessagesScreen(Request $request)
    {
        $class = $request->query('class', 'all');
        $dbClass = str_starts_with($class, 'Class ')
            ? str_replace('Class ', '', $class)
            : $class;
        $user = Auth::guard('admin')->user();

        $query = \App\Models\Conversation::with(['student.studentClass', 'messages'])
            ->when($user && in_array($user->role, ['education', 'main'], true), function ($builder) use ($user) {
                $builder->where('admin_id', $user->getKey());
            })
            ->when($class !== 'all', function ($builder) use ($dbClass) {
                $builder->whereHas('student.studentClass', fn ($studentQuery) => $studentQuery->where('year', $dbClass));
            });

        $conversations = $query->orderByDesc('updated_at')->get();
        $students = $conversations->pluck('student')->filter()->unique('id')->values();
        $studentIds = $students->pluck('id');

        $unreadCounts = DB::table('messages')
            ->join('conversations', 'conversations.id', '=', 'messages.conversation_id')
            ->whereIn('conversations.student_id', $studentIds)
            ->where('messages.sender_type', 'student')
            ->whereNull('messages.read_at')
            ->when($user && in_array($user->role, ['education', 'main'], true), function ($builder) use ($user) {
                $builder->where('conversations.admin_id', $user->getKey());
            })
            ->select('conversations.student_id', DB::raw('count(*) as unread_count'))
            ->groupBy('conversations.student_id')
            ->pluck('unread_count', 'student_id');

        $latestUpdate = DB::table('student_statuses')->max('recorded_at');
        $allStudents = Student::with('latestStatus')->get();
        $activeAlerts = DB::table('emergency_alerts')->where('status', '!=', 'resolved');
        $unreadAlerts = (clone $activeAlerts)->whereNull('read_at')->count();
        $broadcastUnread = DB::table('broadcast_recipients')->whereNull('read_at')->count();
        $unreadMessages = DB::table('messages')
            ->where('sender_type', 'student')
            ->whereNull('read_at')
            ->count();

        return view('messages', [
            'notifications' => $conversations,
            'sidebarStudents' => $students->sortBy('name')->values(),
            'stats' => [
                'unread' => $unreadAlerts + $broadcastUnread + $unreadMessages,
                'sos' => $unreadAlerts,
                'broadcast' => DB::table('broadcasts')->count(),
                'onlineCount' => $allStudents->where('status', true)->count(),
                'offlineCount' => $allStudents->where('status', false)->count(),
                'latestTime' => $latestUpdate ? \Carbon\Carbon::parse($latestUpdate)->format('h:i A') : null,
                'latestDate' => $latestUpdate ? \Carbon\Carbon::parse($latestUpdate)->format('M d, Y') : null,
            ],
            'screen' => 'messages',
            'class' => $class,
            'dbClass' => $dbClass,
            'classes' => BatchClass::orderBy('year')->get(),
            'canMessage' => $this->canSendMessages(),
            'unreadCounts' => $unreadCounts,
            'currentAdminId' => $user?->getKey(),
            'currentAdminRole' => $user?->role,
        ]);
    }

    protected function renderAlertsScreen(Request $request)
    {
        $class = $request->query('class', 'all');
        $dbClass = str_starts_with($class, 'Class ')
            ? str_replace('Class ', '', $class)
            : $class;
        $user = Auth::guard('admin')->user();

        $alerts = SosAlert::with([
            'student.studentClass',
            'alertLocation',
            'media',
            'acknowledgedBy',
            'resolvedBy',
        ])
            ->when($class !== 'all', function ($query) use ($dbClass) {
                $query->whereHas('student.studentClass', fn ($studentQuery) => $studentQuery->where('year', $dbClass));
            })
            ->orderByDesc('created_at')
            ->get();

        $latestUpdate = DB::table('student_statuses')->max('recorded_at');
        $allStudents = Student::with('latestStatus')->get();
        $unreadAlerts = (clone SosAlert::query())->whereNull('read_at')->count();
        $broadcastUnread = DB::table('broadcast_recipients')->whereNull('read_at')->count();
        $unreadMessages = DB::table('messages')
            ->where('sender_type', 'student')
            ->whereNull('read_at')
            ->count();

        return view('alerts', [
            'notifications' => $alerts,
            'sidebarStudents' => collect(),
            'stats' => [
                'unread' => $unreadAlerts + $broadcastUnread + $unreadMessages,
                'sos' => $unreadAlerts,
                'broadcast' => DB::table('broadcasts')->count(),
                'onlineCount' => $allStudents->where('status', true)->count(),
                'offlineCount' => $allStudents->where('status', false)->count(),
                'latestTime' => $latestUpdate ? \Carbon\Carbon::parse($latestUpdate)->format('h:i A') : null,
                'latestDate' => $latestUpdate ? \Carbon\Carbon::parse($latestUpdate)->format('M d, Y') : null,
            ],
            'screen' => 'alerts',
            'class' => $class,
            'dbClass' => $dbClass,
            'classes' => BatchClass::orderBy('year')->get(),
            'canMessage' => $this->canSendMessages(),
            'unreadCounts' => collect(),
            'currentAdminId' => $user?->getKey(),
            'currentAdminRole' => $user?->role,
        ]);
    }

    protected function canSendMessages(): bool
    {
        return Auth::guard('admin')->check()
            && in_array(Auth::guard('admin')->user()->role, ['education', 'main'], true);
    }

    protected function currentAdminId(): ?int
    {
        $user = Auth::guard('admin')->user();

        return $user ? (int) $user->getKey() : null;
    }

    protected function currentAdminName(): string
    {
        $user = Auth::guard('admin')->user();

        if (!$user) {
            return 'Education Staff';
        }

        return trim(implode(' ', array_filter([
            $user->first_name,
            $user->middle_initial ? $user->middle_initial . '.' : null,
            $user->last_name,
        ])));
    }

    protected function currentSenderLabel(): string
    {
        $user = Auth::guard('admin')->user();
        $roleLabel = $user && $user->role === 'education' ? 'Education' : 'Admin';
        $name = $this->currentAdminName();

        return $name ? $roleLabel . ' - ' . $name : $roleLabel;
    }

    protected function normalizeSubject(?string $type, ?string $message, ?string $fallback = null): string
    {
        $candidate = trim((string) ($fallback ?? ''));
        if ($candidate !== '') {
            return substr($candidate, 0, 255);
        }

        $typeLabel = match ($type ?? '') {
            'sos' => 'SOS Alert',
            'broadcast' => 'Broadcast Notice',
            'student_message' => 'Student Message',
            'admin_reply' => 'Admin Reply',
            default => 'Notification',
        };
        $messageText = trim((string) ($message ?? ''));
        if ($messageText === '') {
            return $typeLabel;
        }

        $messageText = preg_replace('/\s+/', ' ', strip_tags($messageText)) ?? $messageText;

        return substr($messageText, 0, 255) ?: $typeLabel;
    }
}
