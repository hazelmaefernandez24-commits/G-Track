<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoomAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'room_number',
        'student_id',
        'student_name',
        'student_gender',
        'batch_year',
        'assignment_order',
        'room_capacity',
        'assigned_at'
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'assignment_order' => 'integer',
        'room_capacity' => 'integer'
    ];

    /**
     * Get all room assignments grouped by room number
     */
    public static function getAllRoomAssignments()
    {
        return self::orderBy('room_number')
            ->orderBy('assignment_order')
            ->get()
            ->groupBy('room_number')
            ->map(function ($assignments) {
                return $assignments->pluck('student_name')->toArray();
            })
            ->toArray();
    }

    /**
     * Clear all room assignments
     */
    public static function clearAllAssignments()
    {
        return self::truncate();
    }

    /**
     * Get the student associated with this assignment
     */
    public function student()
    {
        return $this->belongsTo(PNUser::class, 'student_id', 'user_id');
    }

    /**
     * Get the room associated with this assignment
     */
    public function room()
    {
        return $this->belongsTo(Room::class, 'room_number', 'room_number');
    }
}
