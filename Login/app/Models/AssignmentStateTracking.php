<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class AssignmentStateTracking extends Model
{
    use HasFactory;

    protected $table = 'assignment_state_tracking';

    protected $fillable = [
        'assignment_id',
        'category_name',
        'assignment_status',
        'last_shuffle_at',
        'shuffle_requirements',
        'total_members',
        'member_distribution',
        'assignment_start_date',
        'assignment_end_date',
        'is_locked',
        'shuffle_allowed',
        'next_shuffle_allowed_at',
        'created_by_user_id',
        'last_modified_by_user_id',
        'modification_notes'
    ];

    protected $casts = [
        'shuffle_requirements' => 'array',
        'member_distribution' => 'array',
        'last_shuffle_at' => 'datetime',
        'assignment_start_date' => 'date',
        'assignment_end_date' => 'date',
        'next_shuffle_allowed_at' => 'datetime',
        'is_locked' => 'boolean',
        'shuffle_allowed' => 'boolean'
    ];

    /**
     * Check if auto-shuffle is currently allowed for this assignment
     */
    public function isShuffleAllowed(): bool
    {
        if (!$this->shuffle_allowed || $this->is_locked) {
            return false;
        }

        // Check if end date has been reached
        if ($this->assignment_end_date && Carbon::now()->lt($this->assignment_end_date)) {
            return false;
        }

        // Check if next shuffle time has been reached
        if ($this->next_shuffle_allowed_at && Carbon::now()->lt($this->next_shuffle_allowed_at)) {
            return false;
        }

        return true;
    }

    /**
     * Record a new shuffle operation
     */
    public function recordShuffle(array $requirements, int $totalMembers, array $distribution, ?string $userId = null): void
    {
        $this->update([
            'last_shuffle_at' => Carbon::now(),
            'shuffle_requirements' => $requirements,
            'total_members' => $totalMembers,
            'member_distribution' => $distribution,
            'last_modified_by_user_id' => $userId,
            'modification_notes' => 'Auto-shuffle executed with ' . $totalMembers . ' members assigned'
        ]);
    }

    /**
     * Lock assignment to prevent changes
     */
    public function lockAssignment(string $reason = 'Assignment locked', ?string $userId = null): void
    {
        $this->update([
            'is_locked' => true,
            'shuffle_allowed' => false,
            'last_modified_by_user_id' => $userId,
            'modification_notes' => $reason
        ]);
    }

    /**
     * Unlock assignment to allow changes
     */
    public function unlockAssignment(string $reason = 'Assignment unlocked', ?string $userId = null): void
    {
        $this->update([
            'is_locked' => false,
            'shuffle_allowed' => true,
            'last_modified_by_user_id' => $userId,
            'modification_notes' => $reason
        ]);
    }

    /**
     * Set when next shuffle will be allowed
     */
    public function setNextShuffleTime(Carbon $nextAllowedTime, ?string $userId = null): void
    {
        $this->update([
            'next_shuffle_allowed_at' => $nextAllowedTime,
            'last_modified_by_user_id' => $userId,
            'modification_notes' => 'Next shuffle allowed at: ' . $nextAllowedTime->format('Y-m-d H:i:s')
        ]);
    }

    /**
     * Get assignment state summary
     */
    public function getStateSummary(): array
    {
        return [
            'assignment_id' => $this->assignment_id,
            'category_name' => $this->category_name,
            'status' => $this->assignment_status,
            'total_members' => $this->total_members,
            'last_shuffle' => $this->last_shuffle_at?->format('Y-m-d H:i:s'),
            'assignment_period' => $this->assignment_start_date->format('M d') . ' - ' . $this->assignment_end_date->format('M d, Y'),
            'shuffle_allowed' => $this->isShuffleAllowed(),
            'is_locked' => $this->is_locked,
            'member_breakdown' => $this->member_distribution
        ];
    }
}
