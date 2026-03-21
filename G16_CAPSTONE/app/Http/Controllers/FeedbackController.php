<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class FeedbackController extends Controller
{
    private function getWeekNumberInMonth($date)
    {
        $date = Carbon::parse($date);
        // Find the first day of the month
        $firstOfMonth = $date->copy()->startOfMonth();
        // Find the first Monday before or on the 1st
        $firstMonday = $firstOfMonth->copy()->startOfWeek(Carbon::MONDAY);
        // Calculate the difference in days from the first Monday to the current date
        $diffDays = $date->diffInDays($firstMonday);
        // Week number is 1-based
        $weekNumber = intval(floor($diffDays / 7)) + 1;
        // Ensure week number is between 1 and 6 (max 6 weeks in a month)
        return min(max($weekNumber, 1), 6);
    }

    public function submitFeedback(Request $request)
    {
        try {
            // Validate the request
            try {
                $request->validate([
                    'feedback' => 'required|string',
                    'room_number' => 'required|string',
                    'day' => 'required|string',
                    'week' => 'required|string',
                    'month' => 'required|integer|min:1|max:12',
                    'year' => 'required|integer|min:2000',
                    'feedback_files' => 'nullable|array|max:3',
                    'feedback_files.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                ]);
            } catch (ValidationException $ve) {
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => $ve->validator->errors()->first()
                    ], 422);
                }
                throw $ve;
            }

            // Get the values from request
            $day = $request->input('day');
            $week = $request->input('week');
            $month = $request->input('month');
            $year = $request->input('year');

            // Store the files and collect their paths
            $photoPaths = [];
            if ($request->hasFile('feedback_files')) {
                foreach ($request->file('feedback_files') as $file) {
                    try {
                        // Generate a unique filename
                        $originalName = $file->getClientOriginalName();
                        $extension = $file->getClientOriginalExtension();
                        $fileName = pathinfo($originalName, PATHINFO_FILENAME);
                        $uniqueFileName = $fileName . '_' . Str::random(8) . '.' . $extension;
                        
                        // Store the file in the feedback_photos directory
                        $path = $file->storeAs('feedback_photos', $uniqueFileName, 'public');
                        
                        if ($path) {
                            $photoPaths[] = $path;
                        }
                    } catch (\Exception $e) {
                        \Log::error('File upload error: ' . $e->getMessage());
                        continue; // Continue with next file if one fails
                    }
                }
            }

            // Begin database transaction
            DB::beginTransaction();

            try {
                // Prepare data for insertion
                $feedbackData = [
                    'room_number' => $request->room_number,
                    'feedback' => $request->feedback,
                    'photo_paths' => !empty($photoPaths) ? json_encode($photoPaths) : null,
                    'day' => (string)$day,
                    'week' => (string)$week,
                    'month' => (string)$month,
                    'year' => (string)$year,
                    'created_at' => now(),
                    'updated_at' => now()
                ];

                // If feedback_id is present, update the existing feedback, else insert new
                if ($request->filled('feedback_id')) {
                    $feedbackId = $request->input('feedback_id');
                    $existing = DB::table('feedback_room')->where('id', $feedbackId)->first();
                    if ($existing) {
                        // If new files uploaded, merge with old ones
                        $oldPhotos = [];
                        if ($existing->photo_paths) {
                            $oldPhotos = json_decode($existing->photo_paths, true) ?: [];
                        }
                        $allPhotos = array_merge($oldPhotos, $photoPaths);
                        $feedbackData['photo_paths'] = !empty($allPhotos) ? json_encode($allPhotos) : null;
                        unset($feedbackData['created_at']);
                        DB::table('feedback_room')->where('id', $feedbackId)->update($feedbackData);
                    }
                } else {
                    // Insert the feedback record (always new row)
                    $feedbackId = DB::table('feedback_room')->insertGetId($feedbackData);
                    if (!$feedbackId) {
                        throw new \Exception('Failed to insert feedback record');
                    }
                }

                DB::commit();

                // Return JSON if AJAX, else redirect
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json(['success' => true]);
                }
                return redirect()->back()->with('success', 'Feedback submitted successfully!');

            } catch (\Exception $e) {
                DB::rollBack();
                
                // Delete any uploaded files if database transaction fails
                foreach ($photoPaths as $path) {
                    if (Storage::disk('public')->exists($path)) {
                        Storage::disk('public')->delete($path);
                    }
                }

                throw $e;
            }
        } catch (\Exception $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 400);
            }
            return redirect()->back()
                ->with('error', 'Failed to submit feedback: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function editFeedback(Request $request)
    {
        $request->validate([
            'id' => 'required|integer|exists:feedback_room,id',
            'feedback' => 'required|string',
            'remove_photos' => 'array',
            'remove_photos.*' => 'string',
            'feedback_files.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $feedback = \App\Models\FeedbackRoom::findOrFail($request->id);

        // Remove selected photos
        $photoPaths = $feedback->photo_paths ? json_decode($feedback->photo_paths, true) : [];
        $removePhotos = $request->input('remove_photos', []);
        $photoPaths = array_values(array_diff($photoPaths, $removePhotos));
        foreach ($removePhotos as $removePath) {
            if (Storage::disk('public')->exists($removePath)) {
                Storage::disk('public')->delete($removePath);
            }
        }

        // Add new photos
        if ($request->hasFile('feedback_files')) {
            foreach ($request->file('feedback_files') as $file) {
                $originalName = $file->getClientOriginalName();
                $extension = $file->getClientOriginalExtension();
                $fileName = pathinfo($originalName, PATHINFO_FILENAME);
                $uniqueFileName = $fileName . '_' . Str::random(8) . '.' . $extension;
                $path = $file->storeAs('feedback_photos', $uniqueFileName, 'public');
                if ($path) {
                    $photoPaths[] = $path;
                }
            }
        }

        $feedback->feedback = $request->feedback;
        $feedback->photo_paths = !empty($photoPaths) ? json_encode($photoPaths) : null;
        $feedback->save();

        return response()->json(['success' => true]);
    }

    public function deleteFeedback(Request $request)
    {
         if (!auth()->check()) {
             return redirect()->route('login');
        }

        $request->validate([
            'id' => 'required|integer|exists:feedback_room,id',
        ]);
        $feedback = \App\Models\FeedbackRoom::findOrFail($request->id);
        // Delete photos
        $photoPaths = $feedback->photo_paths ? json_decode($feedback->photo_paths, true) : [];
        foreach ($photoPaths as $path) {
            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
        }
        $feedback->delete();
        return response()->json(['success' => true]);
    }

    public function getRoomFeedbacks(Request $request)
    {
        $room = $request->input('room_number');
        $day = $request->input('day');
        $week = $request->input('week');
        $month = $request->input('month');
        $year = $request->input('year');
        $isStudentView = $request->input('student_view') == '1';

        $feedbacks = \App\Models\FeedbackRoom::where('room_number', $room)
            ->where('day', $day)
            ->when($week, function($q) use ($week) { return $q->where('week', $week); })
            ->when($month, function($q) use ($month) { return $q->where('month', $month); })
            ->when($year, function($q) use ($year) { return $q->where('year', $year); })
            ->orderByDesc('id')
            ->get();

        $html = '';
        if ($feedbacks->count()) {
            foreach ($feedbacks as $fb) {
                $createdAt = $fb->created_at
                    ? \Carbon\Carbon::parse($fb->created_at)->timezone('Asia/Manila')->format('F j, Y · h:i A')
                    : '';
                $photoPaths = $fb->photo_paths ? json_decode($fb->photo_paths, true) : [];
                $html .= '<div class="feedback-card" data-feedback-id="' . $fb->id . '" data-photo-paths=\'' . json_encode($photoPaths) . '\' style="background:#f8fbff;border-radius:12px;padding:18px 18px 12px 18px;margin-bottom:22px;box-shadow:0 2px 8px #e5e9f2;position:relative;">';

                // Only show admin controls if not student view
                if (!$isStudentView) {
                    $html .= '<div style="position:absolute;top:14px;right:18px;z-index:2;">';
                    $html .= '<button class="btn btn-md btn-primary edit-feedback-btn" data-id="' . $fb->id . '" style="margin-right:6px;">Edit Feedback</button>';
                    $html .= '<button class="btn btn-md btn-danger delete-feedback-btn" data-id="' . $fb->id . '">Delete Feedback</button>';
                    $html .= '</div>';
                }

                $html .= '<div style="margin-bottom:10px;"><strong>Comment:</strong><div style="background:whitesmoke;border-radius:8px;padding:10px 14px;margin-top:4px;font-size:15px;">'
                    . nl2br(e($fb->feedback)) . '</div></div>';
                $html .= '<div style="font-size:13px;color:#666;margin-bottom:10px;">'
                    . 'Posted'
                    . ($createdAt ? ' • ' . $createdAt : '')
                    . '</div>';
                if ($photoPaths) {
                    $html .= '<div style="display:flex;gap:14px;margin-bottom:10px;">';
                    foreach ($photoPaths as $img) {
                        $html .= '<a href="/storage/' . e($img) . '" target="_blank" style="display:inline-block;">'
                            . '<img src="/storage/' . e($img) . '" style="width:120px;height:90px;object-fit:cover;border-radius:8px;border:1px solid #dbe4f3;" />'
                            . '</a>';
                    }
                    $html .= '</div>';
                    $html .= '<div style="font-size:13px;color:#888;margin-bottom:2px;">'
                        . count($photoPaths) . ' Photo' . (count($photoPaths) > 1 ? 's' : '') . '</div>';
                }
                $html .= '</div>';
            }
        } else {
            $html .= '<div style="color:#888;font-size:15px;margin-top:12px;">No feedback submitted for this day.</div>';
        }
        // Always allow feedback form to be shown after submission/edit
        return $html;
    }
}