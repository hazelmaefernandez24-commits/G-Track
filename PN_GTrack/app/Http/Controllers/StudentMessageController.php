<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StudentMessageController extends NotificationScreenController
{
    public function index(Request $request)
    {
        return $this->renderMessagesScreen($request);
    }

    public function reply(Request $request, $id)
    {
        if (!$this->canSendMessages()) {
            return redirect()->back()->with('error', 'Only education staff can send student messages.');
        }

        $validated = $request->validate(['message' => 'required|string']);
        $parent = Message::with('conversation')->findOrFail($id);

        $parent->conversation->messages()->create([
            'sender_type' => 'admin',
            'sender_id' => $this->currentAdminId(),
            'message' => $validated['message'],
            'reply_to_id' => $parent->id,
        ]);

        $parent->update(['read_at' => now()]);

        return redirect()->back()->with('success', 'Reply sent to student!');
    }

    public function read($id)
    {
        Message::whereKey($id)->whereNull('read_at')->update(['read_at' => now()]);

        return redirect()->back()->with('success', 'Message marked as read.');
    }

    public function getMessagesJson($studentId)
    {
        $student = $this->findStudent($studentId);
        if (!$student) {
            return response()->json(['messages' => []]);
        }

        $user = Auth::guard('admin')->user();
        $conversations = Conversation::where('student_id', $student->id)
            ->when($user && in_array($user->role, ['education', 'main'], true), function ($query) use ($user) {
                $query->where('admin_id', $user->getKey());
            })
            ->get();

        if ($conversations->isEmpty()) {
            return response()->json(['messages' => []]);
        }

        Message::whereIn('conversation_id', $conversations->pluck('id'))
            ->where('sender_type', 'student')
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        $messages = Message::with('conversation')
            ->whereIn('conversation_id', $conversations->pluck('id'))
            ->orderBy('created_at')
            ->get()
            ->map(fn (Message $message) => $this->formatMessage($message));

        return response()->json(['messages' => $messages]);
    }

    public function sendMessageAjax(Request $request, $studentId)
    {
        if (!$this->canSendMessages()) {
            return response()->json(['success' => false, 'message' => 'Only education staff can send student messages.'], 403);
        }

        $messageText = trim((string) $request->input('message', ''));
        if ($messageText === '') {
            return response()->json(['success' => false, 'message' => 'Message cannot be empty.'], 422);
        }

        $student = $this->findStudent($studentId);
        if (!$student) {
            return response()->json(['success' => false, 'message' => 'Student not found.'], 404);
        }

        $conversation = Conversation::firstOrCreate([
            'student_id' => $student->id,
            'admin_id' => $this->currentAdminId(),
        ]);
        $latestStudentMessage = $conversation->messages()
            ->where('sender_type', 'student')
            ->latest()
            ->first();

        $message = $conversation->messages()->create([
            'sender_type' => 'admin',
            'sender_id' => $this->currentAdminId(),
            'message' => $messageText,
            'reply_to_id' => $latestStudentMessage?->id,
        ]);

        return response()->json(['success' => true, 'id' => $message->id]);
    }

    public function deleteConversation(Request $request, $studentId)
    {
        if (!$this->canSendMessages()) {
            return response()->json(['success' => false, 'message' => 'Only education staff can delete student messages.'], 403);
        }

        $student = $this->findStudent($studentId);
        if (!$student) {
            return response()->json(['success' => false, 'message' => 'Student not found.'], 404);
        }

        Conversation::where('student_id', $student->id)
            ->where('admin_id', $this->currentAdminId())
            ->delete();

        return response()->json(['success' => true]);
    }

    public function allStudentsJson(Request $request)
    {
        $class = $request->query('class');
        $students = Student::with('studentClass')
            ->when($class && $class !== 'all', function ($query) use ($class) {
                $query->whereHas('studentClass', fn ($classQuery) => $classQuery->where('year', $class));
            })
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get()
            ->map(function (Student $student) {
                $picture = $student->profile_picture;
                if ($picture) {
                    $picture = str_starts_with($picture, 'http')
                        ? asset(ltrim(parse_url($picture, PHP_URL_PATH), '/'))
                        : asset('storage/' . $picture);
                }

                return [
                    'id' => $student->id,
                    'name' => $student->name,
                    'student_id' => $student->student_id,
                    'class' => $student->class,
                    'status' => $student->status,
                    'profile_picture' => $picture,
                ];
            });

        return response()->json(['students' => $students]);
    }

    public function apiSend(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required',
            'target' => 'required|in:student_message',
            'message' => 'required|string',
            'admin_id' => 'nullable|integer|exists:admins,id',
            'recipient_admin_id' => 'nullable|integer|exists:admins,id',
        ]);

        $student = $this->findStudent($validated['student_id']);
        if (!$student) {
            return response()->json(['success' => false, 'message' => 'Invalid Student ID'], 404);
        }

        $adminId = $validated['admin_id'] ?? $validated['recipient_admin_id'] ?? null;
        $conversation = Conversation::firstOrCreate([
            'student_id' => $student->id,
            'admin_id' => $adminId,
        ]);
        $message = $conversation->messages()->create([
            'sender_type' => 'student',
            'sender_id' => $student->id,
            'message' => $validated['message'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Message received by Admin!',
            'notification_id' => $message->id,
        ]);
    }

    public function apiAdminSendMessage(Request $request, $studentId)
    {
        $validated = $request->validate([
            'message' => 'required|string',
            'admin_id' => 'nullable|integer|exists:admins,id',
        ]);
        $student = $this->findStudent($studentId);
        if (!$student) {
            return response()->json(['success' => false, 'message' => 'Student not found'], 404);
        }

        $adminId = $validated['admin_id'] ?? Auth::guard('admin')->id();
        $conversation = Conversation::firstOrCreate([
            'student_id' => $student->id,
            'admin_id' => $adminId,
        ]);
        $parent = $conversation->messages()
            ->where('sender_type', 'student')
            ->latest()
            ->first();
        $message = $conversation->messages()->create([
            'sender_type' => 'admin',
            'sender_id' => $adminId,
            'message' => $validated['message'],
            'reply_to_id' => $parent?->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Message sent successfully',
            'id' => $message->id,
        ]);
    }

    private function findStudent($id): ?Student
    {
        return Student::where('id', $id)->orWhere('student_id', $id)->first();
    }

    private function formatMessage(Message $message): array
    {
        $senderName = match ($message->sender_type) {
            'student' => $message->conversation?->student?->name,
            'admin' => \App\Models\Admin::find($message->sender_id)?->first_name,
            default => 'System',
        };

        return [
            'id' => $message->id,
            'conversation_id' => $message->conversation_id,
            'sender_type' => $message->sender_type,
            'sender_id' => $message->sender_id,
            'sender_name' => $senderName,
            'message' => $message->message,
            'read_at' => $message->read_at,
            'reply_to_id' => $message->reply_to_id,
            'created_at' => $message->created_at,
        ];
    }
}
