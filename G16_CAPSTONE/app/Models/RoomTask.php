<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoomTask extends Model
{
    use HasFactory;

    protected $table = 'roomtask';

    protected $fillable = [
        'name',
        'room_number',
        'area',
        'desc',
        'day',
        'status',
        'week',
        'month',
        'year',
    ];

    /**
     * Get the room associated with this task
     */
    public function room()
    {
        return $this->belongsTo(Room::class, 'room_number', 'room_number');
    }

    /**
     * Get tasks by room number
     */
    public static function getByRoomNumber($roomNumber)
    {
        return self::where('room_number', $roomNumber)
            ->orderBy('day')
            ->orderBy('area')
            ->get();
    }

    /**
     * Get tasks by day
     */
    public static function getByDay($day)
    {
        return self::where('day', $day)
            ->orderBy('room_number')
            ->orderBy('area')
            ->get();
    }

    /**
     * Get tasks by status
     */
    public static function getByStatus($status)
    {
        return self::where('status', $status)
            ->orderBy('room_number')
            ->orderBy('day')
            ->get();
    }

    /**
     * Get task areas
     */
    public static function getTaskAreas()
    {
        // Deprecated: do not use hardcoded task areas. Templates should come from the
        // `task_templates` table (via the TaskTemplate model). Returning an empty
        // array prevents accidental regeneration of deleted/managed tasks.
        return [];
    }

    /**
     * Create tasks for room
     */
    public static function createTasksForRoom($roomNumber, $students, $areas, $days)
    {
        $tasks = [];
        $timestamp = now();

        foreach ($days as $day) {
            foreach ($areas as $area => $description) {
                // Assign to specific student or everyone
                $assignedTo = count($students) > 0 ? $students[array_rand($students)] : 'Everyone';

                $tasks[] = [
                    'name' => $assignedTo,
                    'room_number' => $roomNumber,
                    'area' => $area,
                    'desc' => $description,
                    'day' => $day,
                    'status' => 'not yet',
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp
                ];
            }
        }

        if (!empty($tasks)) {
            self::insert($tasks);
        }

        return count($tasks);
    }

    /**
     * Update task status
     */
    public function updateStatus($status)
    {
        $this->status = $status;
        return $this->save();
    }
}

