<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\Student;
use App\Models\StudentMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StudentMessageController extends NotificationScreenController
{
    public function index(Request $request)
    {
        $query = Notification::query()->whereIn('type', ['sos', 'student_message', 'admin_reply']);
        $user = Auth::guard('admin')->user();
        if ($user) {
            if ($user->role === 'main') {
                $query->where(function ($scopedQuery) use ($user) {
                    $scopedQuery->where('admin_id', $user->getKey())
                        ->orWhere('type', 'sos');
                });
            } else {
                $query->where('admin_id', $user->getKey());
            }
        }

        return $this->renderScreen($request, 'messages', $query, true);
    }

    public function reply(Request $request, $id)
    {
        if (!$this->canSendMessages()) {
            return redirect()->back()->with('error', 'Only education staff can send student messages.');
        }

        $request->validate(['message' => 'required']);
        $parent = Notification::whereIn('type', ['student_message', 'admin_reply', 'sos'])->find($id);
        if (!$parent) {
            return redirect()->back()->with('error', 'Message not found.');
        }

        Notification::create([
            'student_id' => $parent->student_id,
            'admin_id' => $this->currentAdminId(),
            'class' => $parent->class,
            'type' => 'admin_reply',
            'sender_type' => 'admin',
            'sender_name' => $this->currentAdminName(),
            'subject' => $this->normalizeSubject('admin_reply', $request->message, $parent->subject),
            'reply_to_id' => $parent->id,
            'message' => $request->message,
            'read' => false,
            'status' => 'replied',
        ]);

        $parent->update(['status' => 'replied', 'read' => true]);

        return redirect()->back()->with('success', 'Reply sent to student!');
    }

    public function read($id)
    {
        Notification::findOrFail($id)->update(['read' => true]);

        return redirect()->back()->with('success', 'Notification marked as read.');
    }

    public function getMessagesJson($studentId)
    {
        $student = $this->findStudent($studentId);
        if (!$student) {
            return response()->json(['messages' => []]);
        }

        $user = Auth::guard('admin')->user();
        $markSeenQuery = StudentMessage::where('student_id', $student->id)
            ->where('sender_type', 'student')
            ->where('read', false);

        if ($user && in_array($user->role, ['education', 'main'], true)) {
            $markSeenQuery->where('admin_id', $user->getKey());
        }
        $markSeenQuery->update(['read' => true]);

        $messagesQuery = Notification::where('student_id', $student->id)
            ->whereIn('type', ['sos', 'student_message', 'admin_reply']);
        if ($user && in_array($user->role, ['education', 'main'], true)) {
            $messagesQuery->where('admin_id', $user->getKey());
        }

        return response()->json([
            'messages' => $messagesQuery->orderBy('created_at')->get(),
        ]);
    }

    public function sendMessageAjax(Request $request, $studentId)
    {
        if (!$this->canSendMessages()) {
            return response()->json(['success' => false, 'message' => 'Only education staff can send student messages.'], 403);
        }

        $message = trim((string) $request->input('message', ''));
        if ($message === '') {
            return response()->json(['success' => false, 'message' => 'Message cannot be empty.'], 422);
        }

        $student = $this->findStudent($studentId);
        if (!$student) {
            return response()->json(['success' => false], 404);
        }

        $latestStudentMessage = StudentMessage::where('student_id', $student->id)
            ->where('sender_type', 'student')
            ->latest('created_at')
            ->first();

        $reply = StudentMessage::create([
            'student_id' => $student->id,
            'admin_id' => $this->currentAdminId(),
            'class' => $student->class,
            'type' => 'admin_reply',
            'sender_type' => 'admin',
            'sender_name' => $this->currentAdminName(),
            'subject' => $this->normalizeSubject('admin_reply', $message),
            'reply_to_id' => $latestStudentMessage?->id,
            'message' => $message,
            'read' => false,
            'status' => 'replied',
        ]);

        $readMarkQuery = StudentMessage::where('student_id', $student->id)
            ->where('sender_type', 'student')
            ->where('read', false);
        if (Auth::guard('admin')->user()->role === 'education') {
            $readMarkQuery->where('admin_id', Auth::guard('admin')->id());
        }
        $readMarkQuery->update(['read' => true]);

        return response()->json(['success' => true, 'id' => $reply->id]);
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

        $query = StudentMessage::where('student_id', $student->id);
        $user = Auth::guard('admin')->user();
        if (in_array($user->role, ['education', 'main'], true)) {
            $query->where('admin_id', $user->getKey());
        }
        $query->delete();

        return response()->json(['success' => true]);
    }

    public function allStudentsJson(Request $request)
    {
        $query = Student::query();
        $class = $request->query('class');
        if ($class && $class !== 'all') {
            $query->where('class', $class);
        }

        $students = $query->orderBy('name')
            ->get(['id', 'name', 'student_id', 'class', 'status', 'profile_picture'])
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
        $request->validate([
            'student_id' => 'required',
            'target' => 'required|in:student_message',
            'message' => 'required',
        ]);

        $student = $this->findStudent($request->student_id);
        if (!$student) {
            return response()->json(['success' => false, 'message' => 'Invalid Student ID'], 404);
        }

        $message = StudentMessage::create([
            'student_id' => $student->id,
            'admin_id' => $request->input('admin_id', $request->input('recipient_admin_id')),
            'class' => $student->class,
            'sender_type' => 'student',
            'sender_name' => $request->input('sender_name'),
            'subject' => $this->normalizeSubject('student_message', $request->message),
            'message' => $request->message,
            'read' => false,
            'status' => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Notification received by Admin!',
            'notification_id' => $message->id,
        ]);
    }

    public function apiAdminSendMessage(Request $request, $studentId)
    {
        $request->validate(['message' => 'required']);
        $student = $this->findStudent($studentId);
        if (!$student) {
            return response()->json(['success' => false, 'message' => 'Student not found'], 404);
        }

        $latestStudentMessage = StudentMessage::where('student_id', $student->id)
            ->where('sender_type', 'student')
            ->latest('created_at')
            ->first();

        $reply = StudentMessage::create([
            'student_id' => $student->id,
            'admin_id' => $request->input('admin_id'),
            'class' => $student->class,
            'type' => 'admin_reply',
            'sender_type' => 'admin',
            'sender_name' => $request->input('admin_name', 'Admin'),
            'subject' => $this->normalizeSubject('admin_reply', $request->message),
            'reply_to_id' => $latestStudentMessage?->id,
            'message' => $request->message,
            'read' => false,
            'status' => 'replied',
        ]);

        return response()->json(['success' => true, 'message' => 'Message sent successfully', 'id' => $reply->id]);
    }

    private function findStudent($id): ?Student
    {
        return Student::where('id', $id)->orWhere('student_id', $id)->first();
    }
}
