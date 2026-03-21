<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;

class PNUser extends Authenticatable
{
    use HasFactory;

    // Define the table associated with the model
    protected $table = 'pnph_users';

    // The primary key column
    protected $primaryKey = 'user_id';

    // Disable timestamps if you don't want Eloquent to manage created_at and updated_at
    public $timestamps = true;

    // Specify that the primary key is not auto-incrementing
    public $incrementing = false;

    // Specify the type of the primary key (e.g., string)
    protected $keyType = 'string';

    // Specify which fields are mass assignable
    protected $fillable = [
        'user_id',
        'user_fname',
        'user_lname',
        'user_mInitial',
        'user_suffix',
        'gender',
        'user_email',
        'user_password',
        'user_role',
        'status',
        'is_temp_password'
    ];

    // Add this relationship method to the PNUser class
    public function studentDetail()
    {
        return $this->hasOne(StudentDetail::class, 'user_id', 'user_id');
    }

    /**
     * Check if this student is already enrolled in a class
     *
     * @param int|null $excludeClassId Optional class ID to exclude from the check
     * @return array|null Returns array with class info if enrolled, null if not enrolled
     */
    public function getEnrolledClass($excludeClassId = null)
    {
        if ($this->user_role !== 'student') {
            return null;
        }

        $query = DB::table('class_student')
            ->join('classes', 'class_student.class_id', '=', 'classes.id')
            ->where('class_student.user_id', $this->user_id);

        if ($excludeClassId) {
            $query->where('classes.id', '!=', $excludeClassId);
        }

        $enrollment = $query->select(
            'classes.id',
            'classes.class_id',
            'classes.class_name',
            'classes.school_id'
        )->first();

        return $enrollment ? (array) $enrollment : null;
    }

    /**
     * Check if this student is already enrolled in any class
     *
     * @param int|null $excludeClassId Optional class ID to exclude from the check
     * @return bool
     */
    public function isEnrolledInClass($excludeClassId = null)
    {
        return $this->getEnrolledClass($excludeClassId) !== null;
    }

    // Add the role scope
    public function scopeRole(Builder $query, string $role): Builder
    {
        return $query->where('user_role', $role);
    }

    /**
     * Check if the user has a given role.
     *
     * @param string $role
     * @return bool
     */
    public function hasRole(string $role): bool
    {
        return $this->user_role === $role;
    }
}   
