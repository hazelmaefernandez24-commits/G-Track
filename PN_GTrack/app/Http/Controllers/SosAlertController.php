<?php

namespace App\Http\Controllers;

use App\Models\SosAlert;
use App\Models\StudentMessage;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SosAlertController extends NotificationScreenController
{
    public function index(Request $request)
    {
        return $this->renderScreen($request, 'alerts', SosAlert::query());
    }

    public function acknowledge($id)
    {
        $admin = Auth::guard('admin')->user();
        $result = DB::transaction(function () use ($id, $admin) {
            $alert = SosAlert::whereKey($id)->lockForUpdate()->first();

            if (!$alert || $alert->status === 'resolved') {
                return 'unavailable';
            }

            if ($alert->acknowledged_at !== null) {
                return 'already_acknowledged';
            }

            $now = now();
            $updated = SosAlert::whereKey($alert->id)
                ->where('status', '!=', 'resolved')
                ->whereNull('acknowledged_at')
                ->update([
                    'acknowledged_by_admin_id' => $admin->getKey(),
                    'acknowledged_by_name' => $this->currentAdminName(),
                    'acknowledged_at' => $now,
                    'read' => true,
                    'updated_at' => $now,
                ]);

            if ($updated !== 1) {
                return 'already_acknowledged';
            }

            if (!$alert->student_id) {
                return 'missing_student';
            }

            StudentMessage::create([
                'student_id' => $alert->student_id,
                'admin_id' => $admin->getKey(),
                'class' => $alert->class,
                'type' => 'admin_reply',
                'sender_type' => 'admin',
                'sender_name' => $this->currentAdminName(),
                'subject' => $this->normalizeSubject('admin_reply', 'Your SOS alert has been acknowledged.'),
                'reply_to_id' => $alert->id,
                'message' => 'Your SOS alert has been acknowledged.',
                'read' => false,
                'status' => 'replied',
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            return 'acknowledged';
        });

        if ($result === 'unavailable') {
            return redirect()->back()->with('error', 'Alert could not be found or has already been resolved.');
        }
        if ($result === 'missing_student') {
            return redirect()->back()->with('error', 'Alert acknowledged, but no student is linked to receive a message.');
        }
        if ($result === 'already_acknowledged') {
            return redirect()->back()->with('info', 'This alert has already been acknowledged; no additional message was sent.');
        }

        return redirect()->back()->with('success', 'Alert acknowledged and message sent to the student.');
    }

    public function resolve($id)
    {
        $admin = Auth::guard('admin')->user();
        $now = now();
        $resolved = SosAlert::whereKey($id)
            ->where('status', '!=', 'resolved')
            ->whereNull('resolved_at')
            ->update([
                'status' => 'resolved',
                'read' => true,
                'resolved_by_admin_id' => $admin->getKey(),
                'resolved_by_name' => $this->currentAdminName(),
                'resolved_at' => $now,
                'updated_at' => $now,
            ]);

        if ($resolved) {
            $alert = SosAlert::find($id);
            if ($alert && $alert->student_id) {
                $student = Student::where('student_id', $alert->student_id)
                    ->orWhere('id', $alert->student_id)
                    ->first();
                if ($student) {
                    $student->sos_status = 'safe';
                    $student->save();
                }
            }
        }

        return redirect()->back()->with('success', 'Alert marked as Resolved (Safe).');
    }

    public function deleteArchives()
    {
        SosAlert::where('status', 'resolved')->delete();

        return redirect()->back()->with('success', 'All SOS archives deleted successfully.');
    }

    public function apiSend(Request $request)
    {
        $request->validate([
            'student_id' => 'required',
            'target' => 'required|in:sos',
            'message' => 'required',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'battery_level' => 'nullable|integer|between:0,100',
            'battery' => 'nullable|integer|between:0,100',
            'signal' => 'nullable|string',
            'location' => 'nullable|string',
            'media' => 'nullable|file|mimes:mp4,mov,avi,jpg,png,jpeg|max:25600',
            'video' => 'nullable|file|mimes:mp4,mov,avi|max:25600',
        ]);

        $student = Student::where('student_id', $request->student_id)
            ->orWhere('id', $request->student_id)
            ->first();
        if (!$student) {
            return response()->json(['success' => false, 'message' => 'Invalid Student ID'], 404);
        }

        $videoUploadFailed = false;
        try {
            $mediaUrl = $request->hasFile('media')
                ? asset('storage/' . $request->file('media')->store('recordings/media', 'public'))
                : null;
            $videoUrl = $request->hasFile('video')
                ? asset('storage/' . $request->file('video')->store('recordings/videos', 'public'))
                : null;
        } catch (\Exception $exception) {
            \Log::error('Failed to upload media/video files: ' . $exception->getMessage());
            $mediaUrl = null;
            $videoUrl = null;
            $videoUploadFailed = true;
        }

        $recipientAdminId = $request->input(
            'admin_id',
            $request->input('recipient_admin_id', $request->input('target_admin_id', $request->input('to_admin_id')))
        );

        try {
            $existingAlert = SosAlert::where('student_id', $student->id)
                ->where('status', '!=', 'resolved')
                ->first();

            if ($existingAlert) {
                $now = now();
                $existingAlert->update([
                    'video_url' => $videoUrl ?? $existingAlert->video_url,
                    'media_url' => $mediaUrl ?? $existingAlert->media_url,
                    'subject' => $this->normalizeSubject('sos', $request->message ?: $existingAlert->message, $existingAlert->subject),
                    'message' => $request->message ?: $existingAlert->message,
                    'latitude' => $request->latitude ?? $existingAlert->latitude,
                    'longitude' => $request->longitude ?? $existingAlert->longitude,
                    'battery_level' => $request->input('battery_level', $request->input('battery')) ?? $existingAlert->battery_level,
                    'signal_status' => $request->signal ?? $existingAlert->signal_status,
                    'location' => $request->location ?? $existingAlert->location,
                    'updated_at' => $now,
                ]);
                $id = $existingAlert->id;
            } else {
                $alert = SosAlert::create([
                    'student_id' => $student->id,
                    'admin_id' => $recipientAdminId,
                    'class' => $student->class,
                    'sender_type' => 'student',
                    'subject' => $this->normalizeSubject('sos', $request->message),
                    'message' => $request->message,
                    'latitude' => $request->latitude,
                    'longitude' => $request->longitude,
                    'battery_level' => $request->input('battery_level', $request->input('battery')),
                    'signal_status' => $request->signal,
                    'location' => $request->location,
                    'media_url' => $mediaUrl,
                    'video_url' => $videoUrl,
                    'audio_url' => null,
                    'read' => false,
                    'status' => 'pending',
                ]);
                $id = $alert->id;
            }
        } catch (\Exception $exception) {
            \Log::error('Failed to create/update notification: ' . $exception->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to create/update notification: ' . $exception->getMessage(),
            ], 500);
        }

        try {
            if ($request->filled('latitude')) {
                $student->latitude = $request->latitude;
            }
            if ($request->filled('longitude')) {
                $student->longitude = $request->longitude;
            }
            $battery = $request->input('battery_level', $request->input('battery'));
            if (isset($battery)) {
                $student->battery_level = $battery;
            }
            if ($request->filled('signal')) {
                $student->signal_status = $request->signal;
            }
            $student->sos_status = 'help';
            $student->last_update = now()->format('M d, Y h:i A');
            $student->save();
        } catch (\Exception $exception) {
            \Log::error('Failed to update student status: ' . $exception->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Alert received but failed to update student status: ' . $exception->getMessage(),
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => $videoUploadFailed
                ? 'Emergency alert received by Admin, but the video could not be uploaded.'
                : 'Emergency alert received by Admin!',
            'video_uploaded' => !$videoUploadFailed && !empty($videoUrl),
            'notification_id' => $id,
        ]);
    }

    public function apiResolve($id)
    {
        $alert = SosAlert::find($id);
        if (!$alert) {
            return response()->json(['success' => false, 'message' => 'Notification not found'], 404);
        }

        $alert->update(['status' => 'resolved', 'read' => true]);
        if ($alert->student_id) {
            $student = Student::where('student_id', $alert->student_id)
                ->orWhere('id', $alert->student_id)
                ->first();
            if ($student) {
                $student->sos_status = 'safe';
                $student->save();
            }
        }

        return response()->json(['success' => true, 'message' => 'Alert marked as Resolved (Safe).']);
    }

    public function apiGetVideo($id)
    {
        $alert = SosAlert::find($id);
        if (!$alert) {
            return response()->json(['success' => false, 'message' => 'Notification not found'], 404);
        }

        $videoUrl = $this->extractVideoUrl($alert);

        return $videoUrl
            ? response()->json(['success' => true, 'video_url' => $videoUrl])
            : response()->json(['success' => false, 'message' => 'No video feed available for this alert.']);
    }

    public function apiGetLatestVideo($studentId)
    {
        $student = Student::where('id', $studentId)->orWhere('student_id', $studentId)->first();
        if (!$student) {
            return response()->json(['success' => false, 'message' => 'Student not found'], 404);
        }

        $alert = SosAlert::where('student_id', $student->id)->latest('created_at')->first();
        if (!$alert) {
            return response()->json(['success' => false, 'message' => 'No alerts found for this student.'], 404);
        }

        $videoUrl = $this->extractVideoUrl($alert);

        return $videoUrl
            ? response()->json(['success' => true, 'video_url' => $videoUrl, 'notification_id' => $alert->id])
            : response()->json(['success' => false, 'message' => 'No video feed available in the latest alert.']);
    }

    private function extractVideoUrl(SosAlert $notification): ?string
    {
        if (!empty($notification->video_url)) {
            return $notification->video_url;
        }

        if (!empty($notification->media_url) && !Str::endsWith($notification->media_url, ['.mp3', '.wav'])) {
            return $notification->media_url;
        }

        return null;
    }
}
