<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;

class LoginPNUser extends Authenticatable
{
    use HasApiTokens;

    protected $connection = 'login_db'; // Use Login database connection
    protected $table = 'pnph_users';
    protected $primaryKey = 'user_id';
    public $incrementing = false;

    protected $fillable = [
        'user_id',
        'user_fname',
        'user_lname',
        'user_mInitial',
        'user_suffix',
        'gender',
        'user_email',
        'user_role',
        'user_password',
        'status',
        'is_temp_password',
        'remember_token',
        'created_at',
        'updated_at'
    ];

    protected $hidden = [
        'user_password',
        'remember_token',
    ];

    protected $casts = [
        'is_temp_password' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the student detail for this user
     */
    public function studentDetail()
    {
        return $this->hasOne(LoginStudentDetail::class, 'user_id', 'user_id');
    }

    /**
     * Get the password attribute name for authentication
     */
    public function getAuthPassword()
    {
        return $this->user_password;
    }

    /**
     * Get the unique identifier for the user
     */
    public function getAuthIdentifierName()
    {
        return 'user_id';
    }

    /**
     * Get the unique identifier for the user
     */
    public function getAuthIdentifier()
    {
        return $this->getAttribute($this->getAuthIdentifierName());
    }

    /**
     * Scope for active users
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for students
     */
    public function scopeStudents($query)
    {
        return $query->where('user_role', 'student');
    }

    /**
     * Scope for admins
     */
    public function scopeAdmins($query)
    {
        return $query->where('user_role', 'admin');
    }

    /**
     * Get full name attribute
     */
    public function getFullNameAttribute()
    {
        return trim(($this->user_fname ?? '') . ' ' . ($this->user_lname ?? ''));
    }
}
