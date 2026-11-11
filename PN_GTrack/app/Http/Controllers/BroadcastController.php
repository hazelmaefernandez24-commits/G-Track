<?php

namespace App\Http\Controllers;

use App\Models\BatchClass;
use App\Models\Broadcast;
use App\Models\Conversation;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BroadcastController extends NotificationScreenController
{
    public function index(Request $request)
    {
        $class = $request->query('class', 'all');
        $dbClass = str_starts_with($class, 'Class ')
            ? str_replace('Class ', '', $class)
            : $class;
        $user = Auth::guard('admin')->user();

        $broadcasts = Broadcast::with([
            'admin',
            'recipients.student.studentClass',
        ])
            ->when($class !== 'all', function ($query) use ($dbClass) {
                $query->whereHas('recipients.student.studentClass', fn ($studentQuery) => $studentQuery->where('year', $dbClass));
            })
            ->orderByDesc('created_at')
            ->get();

        $broadcasts->each(function (Broadcast $broadcast): void {
            $recipientStudents = $broadcast->recipients->pluck('student')->filter();
            $years = $recipientStudents->map(fn (Student $student) => $student->studentClass?->year)
                ->filter()->unique()->values();
            $label = $recipientStudents->count() === Student::count()
                ? 'All Students'
                : ($years->count() === 1
                    ? 'Class ' . $years->first()
                    : $recipientStudents->count() . ' Students');
            $broadcast->setAttribute('audience_label', $label);
        });

        $latestUpdate = DB::table('student_statuses')->max('recorded_at');
        $allStudents = Student::with('latestStatus')->get();
        $unreadAlerts = DB::table('emergency_alerts')
            ->where('status', '!=', 'resolved')
            ->whereNull('read_at')
            ->count();
        $unreadBroadcasts = DB::table('broadcast_recipients')->whereNull('read_at')->count();
        $unreadMessages = DB::table('messages')->where('sender_type', 'student')->whereNull('read_at')->count();

        return view('broadcasts', [
            'notifications' => $broadcasts,
            'sidebarStudents' => collect(),
            'stats' => [
                'unread' => $unreadAlerts + $unreadBroadcasts + $unreadMessages,
                'sos' => $unreadAlerts,
                'broadcast' => Broadcast::count(),
                'onlineCount' => $allStudents->where('status', true)->count(),
                'offlineCount' => $allStudents->where('status', false)->count(),
                'latestTime' => $latestUpdate ? \Carbon\Carbon::parse($latestUpdate)->format('h:i A') : null,
                'latestDate' => $latestUpdate ? \Carbon\Carbon::parse($latestUpdate)->format('M d, Y') : null,
            ],
            'screen' => 'broadcasts',
            'class' => $class,
            'dbClass' => $dbClass,
            'classes' => BatchClass::orderBy('year')->get(),
            'canMessage' => $this->canSendMessages(),
            'unreadCounts' => collect(),
            'currentAdminId' => $user?->getKey(),
            'currentAdminRole' => $user?->role,
        ]);
    }

    public function send(Request $request)
    {
        $validated = $request->validate([
            'target' => 'required|string',
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        $students = $this->studentsForTarget($validated['target']);
        if ($students->isEmpty()) {
            return redirect()->back()->withInput()->withErrors(['target' => 'The selected audience has no students.']);
        }

        $broadcast = DB::transaction(function () use ($validated, $students) {
            $broadcast = Broadcast::create([
                'admin_id' => Auth::guard('admin')->id(),
                'subject' => $validated['subject'],
                'message' => $validated['message'],
            ]);
            $broadcast->recipients()->createMany(
                $students->map(fn (Student $student) => ['student_id' => $student->id])->all()
            );

            return $broadcast;
        });

        return redirect()->back()->with('success', 'Broadcast sent to ' . $broadcast->recipients()->count() . ' student(s).');
    }

    public function apiSend(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required',
            'target' => 'required|in:broadcast',
            'message' => 'required|string',
            'subject' => 'nullable|string|max:255',
        ]);

        $student = $this->findStudent($validated['student_id']);
        if (!$student) {
            return response()->json(['success' => false, 'message' => 'Invalid Student ID'], 404);
        }

        $adminId = $request->input('admin_id');
        $conversation = Conversation::firstOrCreate([
            'student_id' => $student->id,
            'admin_id' => $adminId,
        ]);
        $message = $conversation->messages()->create([
            'sender_type' => 'student',
            'sender_id' => $student->id,
            'message' => $validated['message'],
        ]);
        if (!empty($validated['subject'])) {
            $conversation->update(['subject' => $validated['subject']]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Message received by Admin!',
            'notification_id' => $message->id,
        ]);
    }

    public function apiAdminBroadcast(Request $request)
    {
        $validated = $request->validate([
            'target' => 'required|string',
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
            'admin_id' => 'nullable|integer|exists:admins,id',
        ]);

        $adminId = $validated['admin_id'] ?? Auth::guard('admin')->id();
        if (!$adminId) {
            return response()->json(['success' => false, 'message' => 'A valid admin_id is required.'], 422);
        }

        $students = $this->studentsForTarget($validated['target']);
        if ($students->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'The selected audience has no students.'], 422);
        }

        $broadcast = DB::transaction(function () use ($validated, $adminId, $students) {
            $broadcast = Broadcast::create([
                'admin_id' => $adminId,
                'subject' => $validated['subject'],
                'message' => $validated['message'],
            ]);
            $broadcast->recipients()->createMany(
                $students->map(fn (Student $student) => ['student_id' => $student->id])->all()
            );

            return $broadcast;
        });

        return response()->json([
            'success' => true,
            'message' => 'Broadcast sent successfully',
            'broadcast_id' => $broadcast->id,
            'recipient_count' => $students->count(),
        ]);
    }

    private function studentsForTarget(string $target)
    {
        if ($target === 'all') {
            return Student::all();
        }

        $classYear = preg_replace('/^Class\\s+/', '', $target);
        $classExists = BatchClass::where('year', $classYear)->exists();
        if ($classExists) {
            return Student::whereHas('studentClass', fn ($query) => $query->where('year', $classYear))->get();
        }

        $student = $this->findStudent($target);

        return $student ? collect([$student]) : collect();
    }

    private function findStudent($id): ?Student
    {
        return Student::where('id', $id)->orWhere('student_id', $id)->first();
    }
}
