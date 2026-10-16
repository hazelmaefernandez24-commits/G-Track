<?php

namespace App\Http\Controllers;

use App\Models\BatchClass;
use App\Models\Notification;
use App\Models\BroadcastNotification;
use App\Models\SosAlert;
use App\Models\Student;
use App\Models\StudentMessage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

abstract class NotificationScreenController extends Controller
{
    protected function renderScreen(Request $request, string $screen, Builder $query, bool $groupByStudent = false)
    {
        $class = $request->query('class', 'all');
        $dbClass = str_starts_with($class, 'Class ')
            ? str_replace('Class ', '', $class)
            : $class;

        if ($class !== 'all') {
            $query->where(function ($classQuery) use ($dbClass, $screen) {
                $classQuery->where('class', $dbClass)
                    ->orWhereHas('student', fn ($studentQuery) => $studentQuery->where('class', $dbClass));

                if ($screen !== 'messages') {
                    $classQuery->orWhere('class', 'all');
                }
            });
        }

        $allNotifications = $query->with(['replies', 'student'])
            ->orderByDesc('created_at')
            ->get();
        $notifications = $groupByStudent
            ? $allNotifications->whereNotNull('student_id')->groupBy('student_id')
            : $allNotifications->values();

        $students = Student::all();
        $latestUpdate = Student::max('updated_at');
        $user = Auth::guard('admin')->user();
        $threadStudentIds = $allNotifications->whereNotNull('student_id')
            ->pluck('student_id')->filter()->unique()->values();

        $unreadCountsQuery = StudentMessage::selectRaw('student_id, count(*) as unread_count')
            ->where('sender_type', 'student')
            ->where('read', false)
            ->whereNotNull('student_id');

        if ($user && in_array($user->role, ['education', 'main'], true)) {
            $unreadCountsQuery->where('admin_id', $user->getKey());
        }

        return view($screen, [
            'notifications' => $notifications,
            'sidebarStudents' => $threadStudentIds->isNotEmpty()
                ? Student::whereIn('id', $threadStudentIds)->orderBy('name')->get()
                : collect(),
            'stats' => [
                'unread' => Notification::where('read', false)->count(),
                'sos' => SosAlert::where('status', '!=', 'resolved')->where('read', false)->count(),
                'broadcast' => BroadcastNotification::count(),
                'onlineCount' => $students->where('status', true)->count(),
                'offlineCount' => $students->where('status', false)->count(),
                'latestTime' => $latestUpdate ? \Carbon\Carbon::parse($latestUpdate)->format('h:i A') : null,
                'latestDate' => $latestUpdate ? \Carbon\Carbon::parse($latestUpdate)->format('M d, Y') : null,
            ],
            'screen' => $screen,
            'class' => $class,
            'dbClass' => $dbClass,
            'classes' => BatchClass::orderBy('name')->get(),
            'canMessage' => $this->canSendMessages(),
            'unreadCounts' => $unreadCountsQuery->groupBy('student_id')->pluck('unread_count', 'student_id'),
            'currentAdminId' => $user ? $user->getKey() : null,
            'currentAdminRole' => $user ? $user->role : null,
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
            $user->middle_initial,
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
