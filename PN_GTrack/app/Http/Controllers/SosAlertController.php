<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Location;
use App\Models\SosAlert;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SosAlertController extends NotificationScreenController
{
    public function index(Request $request)
    {
        return $this->renderAlertsScreen($request);
    }

    public function activeForAdmin()
    {
        $alerts = SosAlert::with(['student:id,student_id,first_name,middle_initial,last_name', 'media'])
            ->where('status', '!=', 'resolved')
            ->orderByDesc('updated_at')
            ->get()
            ->map(fn (SosAlert $alert) => [
                'id' => $alert->id,
                'notification_key' => (string) $alert->id,
                'student_name' => $alert->student?->name ?? 'Unknown student',
                'student_id' => $alert->student?->student_id,
                'description' => $alert->description,
                'created_at' => $alert->created_at?->toISOString(),
                'video_url' => $alert->video_url,
            ]);

        return response()->json(['alerts' => $alerts]);
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
            $alert->update([
                'acknowledged_by_admin_id' => $admin->getKey(),
                'acknowledged_at' => $now,
                'read_at' => $now,
            ]);

            $conversation = Conversation::firstOrCreate([
                'student_id' => $alert->student_id,
                'admin_id' => $admin->getKey(),
            ]);
            $conversation->messages()->create([
                'sender_type' => 'admin',
                'sender_id' => $admin->getKey(),
                'message' => 'Your SOS alert has been acknowledged.',
            ]);

            return 'acknowledged';
        });

        if ($result === 'unavailable') {
            return redirect()->back()->with('error', 'Alert could not be found or has already been resolved.');
        }
        if ($result === 'already_acknowledged') {
            return redirect()->back()->with('info', 'This alert has already been acknowledged.');
        }

        return redirect()->back()->with('success', 'Alert acknowledged and message sent to the student.');
    }

    public function resolve($id)
    {
        $admin = Auth::guard('admin')->user();
        $alert = SosAlert::whereKey($id)->where('status', '!=', 'resolved')->first();
        if (!$alert) {
            return redirect()->back()->with('error', 'Alert could not be found or has already been resolved.');
        }

        DB::transaction(function () use ($alert, $admin): void {
            $now = now();
            $alert->update([
                'status' => 'resolved',
                'read_at' => $now,
                'acknowledged_by_admin_id' => $alert->acknowledged_by_admin_id ?? $admin->getKey(),
                'acknowledged_at' => $alert->acknowledged_at ?? $now,
                'resolved_by_admin_id' => $admin->getKey(),
                'resolved_at' => $now,
            ]);
            $alert->student?->recordStatus(null, 'safe');
        });

        return redirect()->back()->with('success', 'Alert marked as Resolved (Safe).');
    }

    public function deleteArchives()
    {
        SosAlert::where('status', 'resolved')->delete();

        return redirect()->back()->with('success', 'All SOS archives deleted successfully.');
    }

    public function apiSend(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required',
            'target' => 'required|in:sos',
            'message' => 'required|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'battery_level' => 'nullable|integer|between:0,100',
            'battery' => 'nullable|integer|between:0,100',
            'signal' => 'nullable|string',
            'location' => 'nullable|string',
            'media' => 'nullable|file|mimes:mp4,mov,avi,jpg,png,jpeg|max:1048576',
            'video' => 'nullable|file|mimes:mp4,mov,avi|max:1048576',
        ]);

        $student = Student::where('student_id', $validated['student_id'])
            ->orWhere('id', $validated['student_id'])
            ->first();
        if (!$student) {
            return response()->json(['success' => false, 'message' => 'Invalid Student ID'], 404);
        }

        $uploadedMedia = [];
        $uploadFailed = false;
        foreach (['media', 'video'] as $field) {
            if (!$request->hasFile($field)) {
                continue;
            }

            try {
                $file = $request->file($field);
                $mimeType = $file->getMimeType() ?: 'application/octet-stream';
                $isVideo = $field === 'video' || str_starts_with($mimeType, 'video/');
                if ($isVideo) {
                    $classYear = $student->studentClass?->year ?? $student->getRawOriginal('class');
                    $classYear = preg_replace('/^Class\s+/i', '', trim((string) $classYear));
                    $classYear = preg_replace('/[^A-Za-z0-9_-]+/', '_', $classYear);
                    $classFolder = $classYear === '' ? 'Unassigned' : 'Class ' . $classYear;
                    $studentId = preg_replace('/[^A-Za-z0-9_-]+/', '_', $student->student_id);
                    $dateSent = now()->format('Y-m-d_H-i-s-u');
                    $extension = preg_replace('/[^A-Za-z0-9]+/', '', $file->extension()) ?: 'bin';
                    $filePath = $file->storeAs(
                        'recordings/videos/' . $classFolder,
                        $studentId . '_' . $dateSent . '_' . Str::random(6) . '.' . $extension,
                        'public'
                    );
                } else {
                    $filePath = $file->store('recordings/media', 'public');
                }

                if ($filePath === false) {
                    throw new \RuntimeException('The uploaded file could not be stored.');
                }

                $uploadedMedia[] = [
                    'file_path' => $filePath,
                    'mime_type' => $mimeType,
                ];
            } catch (\Exception $exception) {
                Log::error('Failed to upload SOS media.', ['field' => $field, 'error' => $exception->getMessage()]);
                $uploadFailed = true;
            }
        }

        try {
            $alert = DB::transaction(function () use ($student, $validated, $uploadedMedia) {
                $location = null;
                if (isset($validated['latitude'], $validated['longitude'])) {
                    $location = Location::create([
                        'student_id' => $student->id,
                        'latitude' => $validated['latitude'],
                        'longitude' => $validated['longitude'],
                        'recorded_at' => now(),
                    ]);
                }

                $alert = SosAlert::where('student_id', $student->id)
                    ->where('status', '!=', 'resolved')
                    ->latest()
                    ->first();

                if ($alert) {
                    $alert->update([
                        'description' => $validated['message'],
                        'location_id' => $location?->id ?? $alert->location_id,
                    ]);
                } else {
                    $alert = SosAlert::create([
                        'student_id' => $student->id,
                        'location_id' => $location?->id,
                        'description' => $validated['message'],
                        'status' => 'pending',
                    ]);
                }

                foreach ($uploadedMedia as $media) {
                    $alert->media()->create($media);
                }

                $student->recordStatus(null, 'help');

                return $alert;
            });
        } catch (\Exception $exception) {
            Log::error('Failed to create or update emergency alert.', ['error' => $exception->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create emergency alert.',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => $uploadFailed
                ? 'Emergency alert received, but some media could not be uploaded.'
                : 'Emergency alert received by Admin!',
            'video_uploaded' => !$uploadFailed && collect($uploadedMedia)->contains(
                fn (array $media) => str_starts_with($media['mime_type'], 'video/')
            ),
            'notification_id' => $alert->id,
        ]);
    }

    public function apiResolve($id)
    {
        $alert = SosAlert::find($id);
        if (!$alert) {
            return response()->json(['success' => false, 'message' => 'Alert not found'], 404);
        }

        DB::transaction(function () use ($alert): void {
            $now = now();
            $adminId = Auth::guard('admin')->id();
            $alert->update([
                'status' => 'resolved',
                'read_at' => $now,
                'acknowledged_by_admin_id' => $alert->acknowledged_by_admin_id ?? $adminId,
                'acknowledged_at' => $alert->acknowledged_at ?? $now,
                'resolved_by_admin_id' => $adminId,
                'resolved_at' => $now,
            ]);
            $alert->student?->recordStatus(null, 'safe');
        });

        return response()->json(['success' => true, 'message' => 'Alert marked as Resolved (Safe).']);
    }

    public function apiGetVideo($id)
    {
        $alert = SosAlert::find($id);
        if (!$alert) {
            return response()->json(['success' => false, 'message' => 'Alert not found'], 404);
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

    private function extractVideoUrl(SosAlert $alert): ?string
    {
        $video = $alert->media->last(fn ($media) => str_starts_with($media->mime_type, 'video/'));

        if ($video) {
            return $video->url;
        }

        $media = $alert->media->last(fn ($item) => !Str::endsWith($item->file_path, ['.mp3', '.wav']));

        return $media?->url;
    }
}
