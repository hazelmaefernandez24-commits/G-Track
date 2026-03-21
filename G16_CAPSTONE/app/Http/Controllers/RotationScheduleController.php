<?php

namespace App\Http\Controllers;

use App\Models\RotationSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class RotationScheduleController extends Controller
{
    /**
     * List rotation schedules with optional room filter and pagination.
     */
    public function index(Request $request)
    {
        $perPage = (int) $request->query('per_page', 15);
        $query = RotationSchedule::query();

        if ($room = $request->query('room')) {
            $query->where('room', $room);
        }

        return response()->json($query->paginate($perPage));
    }

    /**
     * Show a single rotation schedule.
     */
    public function show($id)
    {
        $record = RotationSchedule::find($id);
        if (! $record) {
            return response()->json(['message' => 'RotationSchedule not found.'], 404);
        }

        return response()->json($record);
    }

    /**
     * Create a rotation schedule.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'room' => 'required|string|max:255',
            'schedule_map' => 'required',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'mode' => 'nullable|string|max:50',
            'frequency' => 'nullable|string|max:50',
        ]);

        $map = $validated['schedule_map'];
        if (is_string($map)) {
            $decoded = json_decode($map, true);
            if ($decoded !== null) $map = $decoded;
        }

        $schedule = RotationSchedule::create([
            'room' => $validated['room'],
            'schedule_map' => $map,
            'start_date' => $validated['start_date'] ?? null,
            'end_date' => $validated['end_date'] ?? null,
            'mode' => $validated['mode'] ?? null,
            'frequency' => $validated['frequency'] ?? null,
            'created_by' => Auth::check() ? Auth::id() : null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Rotation schedule saved',
            'schedule' => $schedule,
        ], 201);
    }

    /**
     * Update a rotation schedule.
     */
    public function update(Request $request, $id)
    {
        $rotation = RotationSchedule::find($id);
        if (! $rotation) {
            return response()->json(['message' => 'RotationSchedule not found.'], 404);
        }

        // Accept schedule_map as array or JSON string; validate conservatively.
        $data = $request->validate([
            'room' => 'sometimes|required|string|max:255',
            'schedule_map' => 'nullable',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'mode' => 'nullable|string|max:255',
            'frequency' => 'nullable|integer',
            'created_by' => 'nullable|integer',
        ]);

        // Normalize schedule_map if provided as JSON string
        if (array_key_exists('schedule_map', $data) && is_string($data['schedule_map'])) {
            $decoded = json_decode($data['schedule_map'], true);
            if ($decoded !== null) {
                $data['schedule_map'] = $decoded;
            }
        }

        $rotation->update($data);

        return response()->json($rotation);
    }

    /**
     * Soft-delete a rotation schedule.
     */
    public function destroy($id)
    {
        $rotation = RotationSchedule::find($id);
        if (! $rotation) {
            return response()->json(['message' => 'RotationSchedule not found.'], 404);
        }

        $rotation->delete();

        return response()->json(null, 204);
    }

    // Return the latest active schedule for a room (or latest by created_at).
    // Used by the blade to hydrate persisted schedules on page load.
    public function latest($room)
    {
        $today = Carbon::today()->toDateString();

        $schedule = RotationSchedule::where('room', $room)
            ->where(function ($q) use ($today) {
                $q->whereNull('end_date')->orWhere('end_date', '>=', $today);
            })
            ->orderBy('created_at', 'desc')
            ->first();

        // fallback to latest one if none active
        if (!$schedule) {
            $schedule = RotationSchedule::where('room', $room)->orderBy('created_at', 'desc')->first();
        }

        if (!$schedule) {
            return response()->json(['success' => false, 'message' => 'No schedule found for room'], 404);
        }

        // Defensive: ensure schedule_map is array
        $map = $schedule->schedule_map;
        if (is_string($map)) {
            $decoded = json_decode($map, true);
            if ($decoded !== null) $map = $decoded;
        }

        // Normalize response shape expected by the blade
        return response()->json([
            'success' => true,
            'schedule' => [
                'id' => $schedule->id,
                'room' => $schedule->room,
                'start_date' => $schedule->start_date ? $schedule->start_date->format('Y-m-d') : null,
                'end_date' => $schedule->end_date ? $schedule->end_date->format('Y-m-d') : null,
                'mode' => $schedule->mode,
                'frequency' => $schedule->frequency,
                'created_by' => $schedule->created_by,
                'schedule_map' => $map,
                'created_at' => $schedule->created_at?->toDateTimeString(),
            ],
        ], 200);
    }
}
