<?php

namespace App\Http\Controllers;

use App\Models\BatchClass;
use App\Models\BroadcastNotification;
use App\Models\Student;
use Illuminate\Http\Request;

class BroadcastController extends NotificationScreenController
{
    public function index(Request $request)
    {
        return $this->renderScreen($request, 'broadcasts', BroadcastNotification::query());
    }

    public function send(Request $request)
    {
        $request->validate([
            'target' => 'required',
            'subject' => 'required|max:255',
            'message' => 'required',
        ]);

        $target = $request->target;
        $studentClass = 'all';
        $studentId = null;
        $knownClasses = BatchClass::pluck('name')->toArray();

        if ($target !== 'all' && !in_array($target, $knownClasses, true)) {
            $studentId = $target;
            $student = Student::where('student_id', $target)->orWhere('id', $target)->first();
            if ($student) {
                $studentClass = $student->class;
            }
        } elseif ($target !== 'all') {
            $studentClass = $target;
        }

        BroadcastNotification::create([
            'student_id' => $studentId,
            'class' => $studentClass,
            'sender_type' => 'admin',
            'sender_name' => $this->currentSenderLabel(),
            'subject' => $request->subject,
            'message' => $request->message,
            'read' => true,
            'status' => 'pending',
        ]);

        return redirect()->back()->with('success', 'Broadcast notification sent successfully!');
    }

    public function apiSend(Request $request)
    {
        $request->validate([
            'student_id' => 'required',
            'target' => 'required|in:broadcast',
            'message' => 'required',
            'subject' => 'nullable|string|max:255',
        ]);

        $student = Student::where('student_id', $request->student_id)
            ->orWhere('id', $request->student_id)
            ->first();
        if (!$student) {
            return response()->json(['success' => false, 'message' => 'Invalid Student ID'], 404);
        }

        $broadcast = BroadcastNotification::create([
            'student_id' => $student->id,
            'class' => $student->class,
            'sender_type' => 'student',
            'subject' => $this->normalizeSubject('broadcast', $request->message, $request->subject),
            'message' => $request->message,
            'read' => false,
            'status' => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Notification received by Admin!',
            'notification_id' => $broadcast->id,
        ]);
    }

    public function apiAdminBroadcast(Request $request)
    {
        $request->validate([
            'target' => 'required',
            'subject' => 'required|max:255',
            'message' => 'required',
        ]);

        $target = $request->target;
        $studentClass = 'all';
        $studentId = null;
        $knownClasses = BatchClass::pluck('name')->toArray();

        if ($target !== 'all' && !in_array($target, $knownClasses, true)) {
            $studentId = $target;
            $student = Student::where('student_id', $target)->orWhere('id', $target)->first();
            if ($student) {
                $studentClass = $student->class;
            }
        } elseif ($target !== 'all') {
            $studentClass = $target;
        }

        BroadcastNotification::create([
            'student_id' => $studentId,
            'class' => $studentClass,
            'sender_type' => 'admin',
            'sender_name' => $request->input('admin_name', 'Admin'),
            'subject' => $request->subject,
            'message' => $request->message,
            'read' => true,
            'status' => 'pending',
        ]);

        return response()->json(['success' => true, 'message' => 'Broadcast sent successfully']);
    }
}
