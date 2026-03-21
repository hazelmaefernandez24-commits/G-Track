<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\PNUser;
use App\Models\StudentDetail;
use App\Models\StudentGroup16;

class AssignmentMember extends Model
{
    use HasFactory;

    protected $table = 'assignments_members';

    protected $fillable = [
    'assignment_id',
    // application-level attribute 'student_id' maps to DB column 'student_id'
    'student_id',
    'student_code',
    'student_name',
    'is_coordinator',
    'task_type',      // Specific task (cook-breakfast, prep-lunch, etc.)
    'time_slot',      // Specific time slot (monday, tuesday, etc.)
    'comments',
    'comment_created_at',
    ];

    protected $casts = [
        'comment_created_at' => 'datetime',
        'is_coordinator' => 'boolean',
    ];

    public function student_group16()
    {
        // References the student_group16 table directly
        return $this->belongsTo(StudentGroup16::class, 'student_group16_id', 'id');
    }

    public function assignment()
    {
        return $this->belongsTo(Assignment::class);
    }

    // Check if comment is expired (older than 1 day)
    public function isCommentExpired()
    {
        if (!$this->comment_created_at || !$this->comments) {
            return false;
        }

        try {
            // Ensure comment_created_at is a Carbon instance
            $createdAt = $this->comment_created_at instanceof \Carbon\Carbon 
                ? $this->comment_created_at 
                : \Carbon\Carbon::parse($this->comment_created_at);
            
            return $createdAt->diffInDays(now()) >= 1;
        } catch (\Exception $e) {
            // If parsing fails, consider it expired to be safe
            \Log::warning('Failed to parse comment_created_at: ' . $e->getMessage());
            return true;
        }
    }

    // Clean expired comments
    public static function cleanExpiredComments()
    {
        $expiredMembers = self::where('comments', '!=', null)
            ->where('comment_created_at', '<=', now()->subDay())
            ->get();

        foreach ($expiredMembers as $member) {
            $member->update([
                'comments' => null,
                'comment_created_at' => null
            ]);
        }
        return $expiredMembers->count();
    }


    public function student()
    {
        // Connect to PNUser from login database using student_id column
        return $this->belongsTo(PNUser::class, 'student_id', 'user_id');
    }

    // The student_id attribute directly maps to the database column
    // No need for virtual attribute since we're using the actual column

    public function setStudentIdAttribute($value)
    {
        // Direct assignment since student_id column exists in the table
        $this->attributes['student_id'] = $value;
    }

    /**
     * Get student_code attribute.
     * For regular students, this returns the student_code from the database.
     * For custom members (without student_id), this can store batch info like "BATCH_2025".
     */
    public function getStudentCodeAttribute()
    {
        // Return the actual student_code value from the database
        return $this->attributes['student_code'] ?? null;
    }

    public function setStudentCodeAttribute($studentCode)
    {
        // Set the actual student_code value in the database
        $this->attributes['student_code'] = $studentCode;
    }

    /**
     * Model hooks: ensure we have either student_id OR student_name.
     */
    protected static function booted()
    {
        static::creating(function ($member) {
            // Ensure either student_id (for registered students) OR student_name (for custom members) is present
            if (empty($member->student_id) && empty($member->student_name)) {
                \Log::warning('Prevented AssignmentMember insert: both student_id and student_name are NULL', $member->toArray());
                throw new \Exception('AssignmentMember creation prevented: either student_id or student_name is required');
            }
        });
    }

    /**
     * Scope a query to match a student by student_id.
     */
    public function scopeWhereStudentId($query, $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    /**
     * Scope a query to match multiple students by student_ids.
     */
    public function scopeWhereInStudentIds($query, array $studentIds)
    {
        return $query->whereIn('student_id', $studentIds);
    }
}