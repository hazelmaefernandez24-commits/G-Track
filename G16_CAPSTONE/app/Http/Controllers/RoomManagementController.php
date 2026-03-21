<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\RoomAssignment;
use App\Models\RoomTask;
use App\Models\PNUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class RoomManagementController extends Controller
{
    /**
     * Display the room management page
     */
    public function index()
    {
        try {
            // Automatically sync with dashboard data to ensure occupant names are current
            $this->syncWithDashboardData();

            // Get all rooms with their current occupancy and assignments
            $rooms = Room::with(['assignments' => function($query) {
                $query->orderBy('assignment_order');
            }])->orderBy('room_number')->get();

            // Debug: Log room assignment counts
            Log::info('Room Management Index - Room Assignment Counts:', [
                'total_rooms' => $rooms->count(),
                'rooms_with_assignments' => $rooms->filter(function($room) {
                    return $room->assignments->count() > 0;
                })->count(),
                'total_assignments' => $rooms->sum(function($room) {
                    return $room->assignments->count();
                })
            ]);

            // Get all students grouped by batch/year for assignment interface
            $students = PNUser::where('user_role', 'student')
                ->where('status', 'active')
                ->with('studentDetail')
                ->select('user_id', 'user_fname', 'user_lname', 'gender')
                ->get()
                ->filter(function($student) {
                    return $student->studentDetail && $student->studentDetail->batch;
                })
                ->groupBy(function($student) {
                    return $student->studentDetail->batch;
                });

            // Get room task areas for task management from the DB TaskTemplate model
            // If no templates are found, fall back to an empty array so UI doesn't
            // rely on hardcoded defaults that may re-create deleted tasks.
            try {
                $taskAreas = \App\Models\TaskTemplate::where('is_active', true)
                    ->orderBy('id')
                    ->get(['area', 'description'])
                    ->pluck('description', 'area')
                    ->toArray();
            } catch (\Throwable $e) {
                // In case the TaskTemplate model or table is unavailable, fall back to empty list
                \Log::warning('Failed to load TaskTemplate list for room management: ' . $e->getMessage());
                $taskAreas = [];
            }

            // Get room statistics
            $statistics = [
                'total_rooms' => $rooms->count(),
                'active_rooms' => $rooms->where('status', 'active')->count(),
                'total_capacity' => $rooms->sum('capacity'),
                'total_occupancy' => $rooms->sum(function($room) {
                    return $room->assignments->count();
                })
            ];

            return view('room-management', compact('rooms', 'students', 'taskAreas', 'statistics'));

        } catch (\Exception $e) {
            Log::error('Error loading room management page: ' . $e->getMessage());
            return redirect()->route('dashboard')->with('error', 'Failed to load room management page');
        }
    }



    /**
     * Store a new room
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'room_number' => 'required|string|max:10|unique:rooms,room_number',
            'capacity' => 'required|integer|min:1|max:20',
            'status' => 'required|in:active,inactive',
            'occupant_type' => 'nullable|in:male,female,both',
            'description' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Auto-generate room name based on room number
            $roomData = $request->all();
            $roomData['name'] = 'Room ' . $request->room_number;
            
            // Only set occupant_type for student rooms (status = 'active')
            if ($request->input('status') === 'inactive') {
                unset($roomData['occupant_type']);
            } else {
                // Default to 'both' if not provided for student rooms
                $roomData['occupant_type'] = $request->input('occupant_type', 'both');
            }

            // Validate gender capacity doesn't exceed total capacity
            $totalGenderCapacity = ($request->male_capacity ?? 0) + ($request->female_capacity ?? 0);
            if ($totalGenderCapacity > $request->capacity) {
                return response()->json([
                    'success' => false,
                    'message' => 'Male + Female capacity cannot exceed total capacity'
                ], 422);
            }

            $room = Room::create($roomData);

            return response()->json([
                'success' => true,
                'message' => 'Room created successfully',
                'room' => $room->load('assignments')
            ]);

        } catch (\Exception $e) {
            Log::error('Error creating room: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create room'
            ], 500);
        }
    }

    /**
     * Show a specific room
     */
    public function show($id)
    {
        try {
            $room = Room::with('assignments')->findOrFail($id);

            return response()->json([
                'success' => true,
                'room' => $room
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting room: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Room not found'
            ], 404);
        }
    }

    /**
     * Update an existing room
     */
    public function update(Request $request, $id)
    {
        $room = Room::findOrFail($id);

        // Determine if this is a minimal update (only status/description) or a full update
        $isMinimal = true;
        $fullFields = ['room_number', 'capacity', 'male_capacity', 'female_capacity', 'assigned_batch'];
        foreach ($fullFields as $f) {
            if ($request->has($f)) {
                $isMinimal = false;
                break;
            }
        }

        if ($isMinimal) {
            $validator = Validator::make($request->all(), [
                'status' => 'sometimes|required|in:active,inactive',
                'occupant_type' => 'nullable|in:male,female,both',
                'description' => 'nullable|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            try {
                $oldStatus = $room->status;

                if ($request->has('status')) $room->status = $request->input('status');
                if ($request->has('description')) $room->description = $request->input('description');
                
                // Update occupant_type only for student rooms (status = 'active')
                if ($room->status === 'active') {
                    if ($request->has('occupant_type')) {
                        $room->occupant_type = $request->input('occupant_type', 'both');
                    }
                } elseif (!$room->occupant_type) {
                    // Ensure column stays populated for non-student rooms to avoid DB null issues
                    $room->occupant_type = 'both';
                }

                $room->save();

                // If status changed, update dashboard floor data accordingly
                if ($request->has('status') && $oldStatus !== $room->status) {
                    if ($room->status === 'inactive') {
                        // When a room is set inactive, remove from dashboard and clear assignments
                        $this->updateDashboardFloorData($room->room_number, 'delete');
                    } elseif ($room->status === 'active') {
                        // Bring room back to dashboard
                        $this->updateDashboardFloorData($room->room_number, 'add');
                    }
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Room updated successfully',
                    'room' => $room->load('assignments')
                ]);

            } catch (\Exception $e) {
                Log::error('Error updating room (minimal): ' . $e->getMessage());
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update room'
                ], 500);
            }
        }

        // Full update fallback: preserve existing validation rules
        $validator = Validator::make($request->all(), [
            'room_number' => 'required|string|max:10|unique:rooms,room_number,' . $id,
            'capacity' => 'required|integer|min:1|max:20',
            'male_capacity' => 'nullable|integer|min:0|max:20',
            'female_capacity' => 'nullable|integer|min:0|max:20',
            'assigned_batch' => 'nullable|string|max:50',
            'status' => 'required|in:active,inactive',
            'description' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Check if capacity is being reduced and if it would exceed current occupancy
            $currentOccupancy = $room->assignments()->count();
            if ($request->capacity < $currentOccupancy) {
                return response()->json([
                    'success' => false,
                    'message' => "Cannot reduce capacity below current occupancy ({$currentOccupancy} students)"
                ], 422);
            }

            // Validate gender capacity doesn't exceed total capacity
            $totalGenderCapacity = ($request->male_capacity ?? 0) + ($request->female_capacity ?? 0);
            if ($totalGenderCapacity > $request->capacity) {
                return response()->json([
                    'success' => false,
                    'message' => 'Male + Female capacity cannot exceed total capacity'
                ], 422);
            }

            // Auto-generate room name based on room number
            $roomData = $request->all();
            $roomData['name'] = 'Room ' . $request->room_number;

            $oldStatus = $room->status;
            $room->update($roomData);

            // If status changed in full update, call dashboard update
            if (isset($roomData['status']) && $oldStatus !== $room->status) {
                if ($room->status === 'inactive') {
                    $this->updateDashboardFloorData($room->room_number, 'delete');
                } elseif ($room->status === 'active') {
                    $this->updateDashboardFloorData($room->room_number, 'add');
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Room updated successfully',
                'room' => $room->load('assignments')
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating room: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update room'
            ], 500);
        }
    }

    /**
     * Delete a room
     */
    public function destroy($id)
    {
        try {
            $room = Room::findOrFail($id);
            
            // Check if room has assignments
            if ($room->assignments()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete room with assigned students. Please reassign students first.'
                ], 422);
            }

            // Delete associated tasks first
            RoomTask::where('room_number', $room->room_number)->delete();
            
            $room->delete();

            return response()->json([
                'success' => true,
                'message' => 'Room deleted successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error deleting room: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete room'
            ], 500);
        }
    }

    /**
     * Delete room with frontend sync flow (returns requires_confirmation if room has assignments)
     */
    public function deleteRoomWithSync(Request $request, $id)
    {
        try {
            $room = Room::findOrFail($id);
            $assignmentsCount = $room->assignments()->count();
            $taskCount = RoomTask::where('room_number', $room->room_number)->count();

            // If not forcing deletion and there are assignments, ask for confirmation with counts
            $force = $request->query('force_delete') || $request->boolean('force_delete');

            if (!$force && $assignmentsCount > 0) {
                return response()->json([
                    'success' => false,
                    'requires_confirmation' => true,
                    'student_count' => $assignmentsCount,
                    'task_count' => $taskCount,
                    'message' => 'Room has assigned students. Confirm deletion to remove assignments and room.'
                ], 200);
            }

            // If forcing deletion, remove assignments as well
            if ($force && $assignmentsCount > 0) {
                RoomAssignment::where('room_number', $room->room_number)->delete();
            }

            // Delete associated tasks first
            RoomTask::where('room_number', $room->room_number)->delete();

            $room->delete();

            return response()->json([
                'success' => true,
                'message' => 'Room deleted successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error deleting room with sync: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete room'
            ], 500);
        }
    }





    /**
     * Assign student to room
     */
    public function assignStudent(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'room_number' => 'required|string|exists:rooms,room_number',
            'student_id' => 'required|string|exists:pnph_users,user_id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $room = Room::where('room_number', $request->room_number)->first();
            $student = PNUser::where('user_id', $request->student_id)->first();

            // Check room capacity
            if ($room->assignments()->count() >= $room->capacity) {
                return response()->json([
                    'success' => false,
                    'message' => 'Room is at full capacity'
                ], 422);
            }

            // Check gender compatibility
            $roomGender = $room->assignments()->first()?->student_gender;
            if ($roomGender && $roomGender !== $student->gender) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gender mismatch. This room is assigned to ' . ($roomGender === 'M' ? 'male' : 'female') . ' students.'
                ], 422);
            }

            // Check if student is already assigned to this room
            if ($room->assignments()->where('student_id', $student->user_id)->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Student is already assigned to this room'
                ], 422);
            }

            // Create assignment
            $assignment = RoomAssignment::create([
                'room_number' => $room->room_number,
                'student_id' => $student->user_id,
                'student_name' => $student->user_fname . ' ' . $student->user_lname,
                'student_gender' => $student->gender,
                'assignment_order' => $room->assignments()->count(),
                'room_capacity' => $room->capacity,
                'assigned_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Student assigned successfully',
                'assignment' => $assignment
            ]);

        } catch (\Exception $e) {
            Log::error('Error assigning student: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to assign student'
            ], 500);
        }
    }

    /**
     * Remove student from room
     */
    public function removeStudent(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'assignment_id' => 'required|integer|exists:room_assignments,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $assignment = RoomAssignment::findOrFail($request->assignment_id);
            $assignment->delete();

            return response()->json([
                'success' => true,
                'message' => 'Student removed successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error removing student: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove student'
            ], 500);
        }
    }

    /**
     * Create room task
     */
    public function createTask(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'room_number' => 'required|string|exists:rooms,room_number',
            'area' => 'required|string|max:255',
            'description' => 'required|string',
            'day' => 'required|string|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
            'apply_to_all' => 'sometimes|boolean',
            'assigned_to' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // If apply_to_all is true, create a template task for every day of
            // the week and persist it with no specific week/month/year so it
            // applies across all dates. Avoid creating duplicates.
            if ($request->boolean('apply_to_all')) {
                $daysOfWeek = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
                $created = [];
                foreach ($daysOfWeek as $d) {
                    // Avoid duplicates for identical template tasks (same room/area/desc/day)
                    $exists = RoomTask::where('room_number', $request->room_number)
                        ->where('area', $request->area)
                        ->where('desc', $request->description)
                        ->where('day', $d)
                        ->whereNull('week')
                        ->whereNull('month')
                        ->whereNull('year')
                        ->exists();

                    if ($exists) continue;

                    $t = RoomTask::create([
                        'name' => $request->assigned_to ?? '', // leave empty so UI shows 'None assigned'
                        'room_number' => $request->room_number,
                        'area' => $request->area,
                        'desc' => $request->description,
                        'day' => $d,
                        'status' => 'not yet',
                        'week' => null,
                        'month' => null,
                        'year' => null
                    ]);
                    $created[] = $t;
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Tasks created for all days successfully',
                    'tasks' => $created
                ]);
            }

            // Default: create a single task tied to current date/week/month/year
            $task = RoomTask::create([
                'name' => $request->assigned_to ?? '',
                'room_number' => $request->room_number,
                'area' => $request->area,
                'desc' => $request->description,
                'day' => $request->day,
                'status' => 'not yet',
                'week' => date('W'),
                'month' => date('n'),
                'year' => date('Y')
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Task created successfully',
                'task' => $task
            ]);

        } catch (\Exception $e) {
            Log::error('Error creating task: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create task'
            ], 500);
        }
    }

    /**
     * Update room task
     */
    public function updateTask(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'area' => 'required|string|max:255',
            'description' => 'required|string',
            'day' => 'required|string|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
            'assigned_to' => 'nullable|string',
            'status' => 'nullable|string|in:not yet,in progress,completed'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $task = RoomTask::findOrFail($id);

            $task->update([
                'name' => $request->assigned_to ?? $task->name,
                'area' => $request->area,
                'desc' => $request->description,
                'day' => $request->day,
                'status' => $request->status ?? $task->status
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Task updated successfully',
                'task' => $task
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating task: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update task'
            ], 500);
        }
    }

    /**
     * Delete room task
     */
    public function deleteTask($id)
    {
        try {
            $task = RoomTask::findOrFail($id);
            $task->delete();

            return response()->json([
                'success' => true,
                'message' => 'Task deleted successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error deleting task: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete task'
            ], 500);
        }
    }

    /**
     * Get room tasks
     */
    public function getRoomTasks($roomNumber)
    {
        try {
            $tasks = RoomTask::where('room_number', $roomNumber)
                ->orderBy('day')
                ->orderBy('area')
                ->get();

            return response()->json([
                'success' => true,
                'tasks' => $tasks
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting room tasks: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to get room tasks'
            ], 500);
        }
    }

    /**
     * Get room statistics
     */
    public function getStatistics()
    {
        try {
            $rooms = Room::with('assignments')->get();

            $statistics = [
                'total_rooms' => $rooms->count(),
                'active_rooms' => $rooms->where('status', 'active')->count(),
                'inactive_rooms' => $rooms->where('status', 'inactive')->count(),
                'total_capacity' => $rooms->sum('capacity'),
                'total_occupancy' => $rooms->sum(function($room) {
                    return $room->assignments->count();
                }),
                'occupancy_rate' => $rooms->sum('capacity') > 0 ?
                    round(($rooms->sum(function($room) { return $room->assignments->count(); }) / $rooms->sum('capacity')) * 100, 1) : 0,
                'rooms_at_capacity' => $rooms->filter(function($room) {
                    return $room->assignments->count() >= $room->capacity;
                })->count(),
                'available_slots' => $rooms->sum(function($room) {
                    return max(0, $room->capacity - $room->assignments->count());
                })
            ];

            return response()->json([
                'success' => true,
                'statistics' => $statistics
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting statistics: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to get statistics'
            ], 500);
        }
    }

    /**
     * Public method to manually sync with dashboard data
     */
    public function syncWithDashboard()
    {
        try {
            $this->syncWithDashboardData();

            return response()->json([
                'success' => true,
                'message' => 'Successfully synced room management with dashboard data'
            ]);

        } catch (\Exception $e) {
            Log::error('Error in manual sync: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to sync with dashboard data'
            ], 500);
        }
    }

    /**
     * Sync room management data with dashboard data
     */
    private function syncWithDashboardData()
    {
        try {
            // Get dashboard room assignments from TaskController logic
            $taskController = new \App\Http\Controllers\TaskController();
            $dashboardRoomStudents = $taskController->getDynamicRoomStudents();

            Log::info('Sync with Dashboard - Dashboard room students:', [
                'room_count' => count($dashboardRoomStudents),
                'rooms_with_students' => array_filter($dashboardRoomStudents, function($students) {
                    return !empty($students);
                }),
                'sample_data' => array_slice($dashboardRoomStudents, 0, 3, true)
            ]);

            // Create rooms that appear in the dashboard data. Avoid any hardcoded room lists.
            $roomNumbersFromDashboard = array_keys($dashboardRoomStudents);
            foreach ($roomNumbersFromDashboard as $roomNumber) {
                // Skip invalid/empty keys
                if (!$roomNumber) continue;

                $room = Room::where('room_number', $roomNumber)->first();

                if (!$room) {
                    // Try to infer floor from the room number (first digit) when possible
                    $floor = null;
                    if (is_string($roomNumber) && strlen($roomNumber) >= 1 && is_numeric(substr($roomNumber, 0, 1))) {
                        $floor = intval(substr($roomNumber, 0, 1));
                    }

                    // Create room if it doesn't exist
                    $room = Room::create([
                        'room_number' => $roomNumber,
                        'name' => "Room {$roomNumber}",
                        'capacity' => 6, // Default capacity
                        'status' => 'active',
                        'description' => $this->getFloorDescription($floor)
                    ]);

                    Log::info("Created room {$roomNumber} from dashboard sync");
                }
            }

            // Sync student assignments from dashboard to room management
            foreach ($dashboardRoomStudents as $roomNumber => $students) {
                $room = Room::where('room_number', $roomNumber)->first();

                if ($room && !empty($students)) {
                    // Get current assignments for comparison
                    $currentAssignments = RoomAssignment::where('room_number', $roomNumber)
                        ->orderBy('assignment_order')
                        ->pluck('student_name')
                        ->toArray();

                    // Check if assignments have changed
                    $dashboardStudents = array_values($students); // Ensure indexed array
                    $assignmentsChanged = $currentAssignments !== $dashboardStudents;

                    if ($assignmentsChanged) {
                        // Clear existing assignments for this room
                        RoomAssignment::where('room_number', $roomNumber)->delete();

                        // Create new assignments based on dashboard data
                        foreach ($students as $index => $studentName) {
                            // Try to find student in PNUser table
                            $student = PNUser::where('user_role', 'student')
                                ->where(function($query) use ($studentName) {
                                    $query->whereRaw("CONCAT(user_fname, ' ', user_lname) = ?", [$studentName])
                                          ->orWhere('user_fname', 'LIKE', "%{$studentName}%")
                                          ->orWhere('user_lname', 'LIKE', "%{$studentName}%");
                                })
                                ->first();

                            RoomAssignment::create([
                                'room_number' => $roomNumber,
                                'student_id' => $student ? $student->user_id : 'unknown_' . $index,
                                'student_name' => $studentName,
                                'student_gender' => $student ? $student->gender : 'M', // Default to M if unknown
                                'assignment_order' => $index,
                                'room_capacity' => $room->capacity,
                                'assigned_at' => now()
                            ]);
                        }

                        Log::info("Synced {$roomNumber} with " . count($students) . " students from dashboard (assignments changed)");
                    }
                }
            }

            Log::info('Successfully synced room management with dashboard data');

        } catch (\Exception $e) {
            Log::error('Error syncing with dashboard data: ' . $e->getMessage());
        }
    }

    /**
     * Get floor description based on floor number
     */
    private function getFloorDescription($floor)
    {
        $descriptions = [
            2 => 'Second floor room for students',
            3 => 'Third floor room for students',
            4 => 'Fourth floor room for students',
            5 => 'Fifth floor room for students',
            6 => 'Sixth floor room for students',
            7 => 'Seventh floor room for students'
        ];

        return $descriptions[$floor] ?? 'Student dormitory room';
    }

    /**
     * Update dashboard floor data when rooms are added/deleted
     */
    private function updateDashboardFloorData($roomNumber, $action)
    {
        try {
            // This method ensures the TaskController's room data stays in sync
            // The dashboard will automatically pick up changes through the existing sync mechanism

            if ($action === 'add') {
                // Trigger a sync to ensure the new room appears in dashboard
                $taskController = new \App\Http\Controllers\TaskController();
                $taskController->getDynamicRoomStudents(); // This will create room assignments if needed

                Log::info("Dashboard floor data updated for added room: {$roomNumber}");
            } elseif ($action === 'delete') {
                // Clean up any remaining room assignment data
                RoomAssignment::where('room_number', $roomNumber)->delete();

                Log::info("Dashboard floor data updated for deleted room: {$roomNumber}");
            }

        } catch (\Exception $e) {
            Log::error("Error updating dashboard floor data for room {$roomNumber}: " . $e->getMessage());
        }
    }


}
