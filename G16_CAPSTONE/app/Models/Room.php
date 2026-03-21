<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    use HasFactory;
    protected $fillable = [
        'room_number',
        'name',
        'capacity',
        'status',
        'description',
        'male_capacity',
        'female_capacity',
        'male_capacity_2025',
        'female_capacity_2025',
        'male_capacity_2026',
        'female_capacity_2026',
        'assigned_batch',
        'occupant_type'
    ];

    protected $casts = [
        'capacity' => 'integer',
        'male_capacity' => 'integer',
        'female_capacity' => 'integer',
        'male_capacity_2025' => 'integer',
        'female_capacity_2025' => 'integer',
        'male_capacity_2026' => 'integer',
        'female_capacity_2026' => 'integer'
    ];

    /**
     * Get the room assignments for this room
     */
    public function assignments()
    {
        return $this->hasMany(RoomAssignment::class, 'room_number', 'room_number');
    }

    /**
     * Get the room tasks for this room
     */
    public function tasks()
    {
        return $this->hasMany(RoomTask::class, 'room_number', 'room_number');
    }

    /**
     * Get current occupancy count
     */
    public function getCurrentOccupancyAttribute()
    {
        return $this->assignments()->count();
    }

    /**
     * Get available slots
     */
    public function getAvailableSlotsAttribute()
    {
        return $this->capacity - $this->current_occupancy;
    }

    /**
     * Get occupancy percentage
     */
    public function getOccupancyPercentageAttribute()
    {
        if ($this->capacity == 0) {
            return 0;
        }
        return round(($this->current_occupancy / $this->capacity) * 100, 1);
    }

    /**
     * Check if room is full
     */
    public function getIsFullAttribute()
    {
        return $this->current_occupancy >= $this->capacity;
    }

    /**
     * Get room gender (based on assigned students)
     */
    public function getRoomGenderAttribute()
    {
        $assignment = $this->assignments()->first();
        return $assignment ? $assignment->student_gender : null;
    }

    /**
     * Check if room can accommodate students of given gender
     */
    public function canAccommodateGender($gender)
    {
        $roomGender = $this->room_gender;
        return $roomGender === null || $roomGender === $gender;
    }

    /**
     * Get rooms by status
     */
    public static function getByStatus($status)
    {
        return self::where('status', $status)->orderBy('room_number')->get();
    }

    /**
     * Get active rooms
     */
    public static function getActive()
    {
        return self::getByStatus('active');
    }

    /**
     * Get inactive rooms
     */
    public static function getInactive()
    {
        return self::getByStatus('inactive');
    }

    /**
     * Get rooms with available capacity
     */
    public static function getAvailable()
    {
        return self::where('status', 'active')
            ->whereRaw('capacity > (SELECT COUNT(*) FROM room_assignments WHERE room_assignments.room_number = rooms.room_number)')
            ->orderBy('room_number')
            ->get();
    }

    /**
     * Get rooms at full capacity
     */
    public static function getAtCapacity()
    {
        return self::where('status', 'active')
            ->whereRaw('capacity <= (SELECT COUNT(*) FROM room_assignments WHERE room_assignments.room_number = rooms.room_number)')
            ->orderBy('room_number')
            ->get();
    }
}
