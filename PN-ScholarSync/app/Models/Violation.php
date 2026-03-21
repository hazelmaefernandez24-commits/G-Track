<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\StudentDetails;
use App\Events\ViolationCreated;
use Carbon\Carbon;

class Violation extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'gender',
        'violation_date',
        'violation_type_id',
        'severity',
        'offense',
        'penalty',
        'consequence',
        'consequence_duration_value',
        'consequence_duration_unit',
        'consequence_start_date',
        'consequence_end_date',
        'consequence_status',
        'action_taken',
        'status',
        'incident_datetime',
        'incident_place',
        'incident_details',
        'prepared_by',
        'logify_sync_batch_id',
        'task_submission_id',
        'g16_user_id',
        'recorded_by',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'action_taken' => 'boolean',
        'consequence_start_date' => 'datetime',
        'consequence_end_date' => 'datetime',
    ];

    /**
     * The model's default values for attributes.
     */
    protected $attributes = [
        'action_taken' => true,
    ];

    /**
     * Boot the model and set up event listeners
     */
    protected static function boot()
    {
        parent::boot();

        // When creating or updating a violation
        static::saving(function ($violation) {
            // If action_taken is false, automatically set status to resolved
            if (!$violation->action_taken) {
                $violation->status = 'resolved';
            }

            // Ensure consequence_status matches action_taken, but allow manual override for appeals
            if (!$violation->action_taken) {
                // No action taken - consequence is resolved
                $violation->consequence_status = 'resolved';
            } else {
                // Action taken - consequence is active by default
                // BUT: Don't override if violation is resolved (could be due to approved appeal)
                if ($violation->status !== 'resolved' && $violation->consequence_status !== 'resolved') {
                    $violation->consequence_status = 'active';
                }
            }
        });

        // Fire event when a new violation is created
        static::created(function ($violation) {
            event(new ViolationCreated($violation));
        });
    }

    // Define the relationship with the StudentDetails model
    public function studentDetails()
    {
        return $this->belongsTo(StudentDetails::class, 'student_id', 'student_id');
    }

    // Define the relationship with the User model through StudentDetails
    public function student()
    {
        return $this->hasOneThrough(
            User::class,
            StudentDetails::class,
            'student_id', // Foreign key on student_details table
            'user_id',    // Foreign key on pnph_users table
            'student_id', // Local key on violations table
            'user_id'     // Local key on student_details table
        );
    }

    // Define the relationship with the ViolationType model
    public function violationType()
    {
        return $this->belongsTo(ViolationType::class, 'violation_type_id');
    }

    // Define the relationship with the User who recorded the violation
    public function recordedBy()
    {
        return $this->belongsTo(User::class, 'recorded_by', 'user_id');
    }

    // Define the relationship with the OffenseCategory model through ViolationType
    public function offenseCategory()
    {
        return $this->hasOneThrough(
            OffenseCategory::class,
            ViolationType::class,
            'id', // Foreign key on violation_types table
            'id', // Foreign key on offense_categories table
            'violation_type_id', // Local key on violations table
            'offense_category_id' // Local key on violation_types table
        );
    }

    // Define the relationship with ViolationAppeal
    public function appeals()
    {
        return $this->hasMany(ViolationAppeal::class);
    }

    // Get the latest appeal for this violation
    public function latestAppeal()
    {
        return $this->hasOne(ViolationAppeal::class)->latest();
    }

    // Check if this violation has been appealed
    public function hasBeenAppealed()
    {
        return $this->appeals()->exists();
    }

    // Check if this violation can be appealed
    public function canBeAppealed()
    {
        // Can only appeal active violations that haven't been appealed yet
        return $this->status === 'active' && !$this->hasBeenAppealed();
    }

    // Get appeal status for this violation
    public function getAppealStatus()
    {
        $latestAppeal = $this->latestAppeal;
        return $latestAppeal ? $latestAppeal->status : null;
    }

    // Check if violation is appeal approved (now resolved due to approved appeal)
    public function isAppealApproved()
    {
        // Check if violation is resolved and has an approved appeal
        if ($this->status === 'resolved') {
            $latestAppeal = $this->latestAppeal;
            return $latestAppeal && $latestAppeal->status === 'approved';
        }
        // Keep backward compatibility for existing 'appeal_approved' status
        return $this->status === 'appeal_approved';
    }

    // Check if violation is appeal denied
    public function isAppealDenied()
    {
        return $this->status === 'appeal_denied';
    }

    // Check if violation is currently appealed (pending review)
    public function isCurrentlyAppealed()
    {
        return $this->status === 'appealed';
    }

    // Check if violation was resolved due to an approved appeal
    public function isResolvedByAppeal()
    {
        if ($this->status === 'resolved') {
            $latestAppeal = $this->latestAppeal;
            return $latestAppeal && $latestAppeal->status === 'approved';
        }
        return false;
    }

    // Check if consequence was resolved due to an approved appeal
    public function isConsequenceResolvedByAppeal()
    {
        if ($this->consequence_status === 'resolved') {
            $latestAppeal = $this->latestAppeal;
            return $latestAppeal && $latestAppeal->status === 'approved';
        }
        return false;
    }

    // Check if this violation should count toward penalty calculation
    public function shouldCountForPenalty()
    {
        // Must have action taken
        if (!$this->action_taken) {
            return false;
        }

        // Exclude violations resolved by approved appeals
        if ($this->status === 'resolved' && $this->isResolvedByAppeal()) {
            return false;
        }

        return true;
    }

    // Get human-readable status
    public function getStatusDisplayAttribute()
    {
        return match($this->status) {
            'pending' => 'Pending',
            'active' => 'Active',
            'resolved' => $this->isResolvedByAppeal() ? 'Resolved (Appeal Approved)' : 'Resolved',
            'appealed' => 'Under Appeal',
            'appeal_approved' => 'Resolved (Appeal Approved)', // Backward compatibility
            'appeal_denied' => 'Appeal Denied',
            default => ucfirst($this->status)
        };
    }

    // Get status color for UI
    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'pending' => 'warning',
            'active' => 'danger',
            'resolved' => 'success',
            'appealed' => 'info',
            'appeal_approved' => 'success',
            'appeal_denied' => 'danger',
            default => 'secondary'
        };
    }

    // Get consequence status color for UI
    public function getConsequenceStatusColorAttribute()
    {
        return match($this->consequence_status) {
            'active' => 'danger',
            'resolved' => 'success',
            default => 'secondary'
        };
    }

    // Calculate consequence end date based on duration
    public function calculateConsequenceEndDate($startDate = null)
    {
        if (!$this->consequence_duration_value || !$this->consequence_duration_unit) {
            return null;
        }

        $start = $startDate ? Carbon::parse($startDate) : Carbon::now();

        return match($this->consequence_duration_unit) {
            'minutes' => $start->addMinutes($this->consequence_duration_value),
            'hours' => $start->addHours($this->consequence_duration_value),
            'days' => $start->addDays($this->consequence_duration_value),
            'weeks' => $start->addWeeks($this->consequence_duration_value),
            'months' => $start->addMonths($this->consequence_duration_value),
            default => null
        };
    }

    // Check if consequence has expired
    public function isConsequenceExpired()
    {
        if (!$this->consequence_end_date) {
            return false;
        }

        return Carbon::now()->isAfter($this->consequence_end_date);
    }

    // Start the consequence (set start date and calculate end date)
    public function startConsequence()
    {
        if ($this->consequence_duration_value && $this->consequence_duration_unit) {
            $this->consequence_start_date = Carbon::now();
            $this->consequence_end_date = $this->calculateConsequenceEndDate($this->consequence_start_date);
            $this->consequence_status = 'active';
            $this->save();
        }
    }

    // Resolve the consequence
    public function resolveConsequence()
    {
        $this->consequence_status = 'resolved';
        $this->save();
    }

    // Resolve both violation and consequence (used for appeal approvals)
    public function resolveViolationAndConsequence()
    {
        $this->status = 'resolved';
        $this->consequence_status = 'resolved';
        $this->save();
    }

    // Get remaining time for consequence
    public function getRemainingConsequenceTime()
    {
        if (!$this->consequence_end_date || $this->isConsequenceExpired()) {
            return null;
        }

        return Carbon::now()->diffForHumans($this->consequence_end_date, true);
    }

    // Scope for active consequences
    public function scopeActiveConsequences($query)
    {
        return $query->where('consequence_status', 'active')
                    ->whereNotNull('consequence_end_date');
    }

    // Scope for expired consequences
    public function scopeExpiredConsequences($query)
    {
        return $query->where('consequence_status', 'active')
                    ->whereNotNull('consequence_end_date')
                    ->where('consequence_end_date', '<=', Carbon::now());
    }
}