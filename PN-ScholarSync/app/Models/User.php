<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Traits\HasRoles;
use App\Models\StudentDetails;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'pnph_users';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'user_id';

    /**
     * Indicates if the model's ID is auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The data type of the auto-incrementing ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'user_fname',
        'user_lname',
        'user_mInitial',
        'user_suffix',
        'user_email',
        'user_role',
        'user_password',
        'status',
        'is_temp_password',
        'g16_user_id',
        'student_id',
        'batch',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'user_password',
        'token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'user_password' => 'hashed',
            'is_temp_password' => 'boolean',
            'status' => 'string',
            'gender' => 'string',
        ];
    }

    // Temporarily disabled to prevent database errors
    // public function roles()
    // {
    //     return $this->belongsToMany(Role::class, 'user_roles', 'user_id', 'role_id', 'user_id', 'id');
    // }

    /**
     * Get the student details for the user.
     */
    public function studentDetails()
    {
        return $this->hasOne(StudentDetails::class, 'user_id', 'user_id');
    }

    /**
     * Get the violations for the student.
     */
    public function violations()
    {
        return $this->hasManyThrough(
            Violation::class,
            StudentDetails::class,
            'user_id',     // Foreign key on student_details table
            'student_id',  // Foreign key on violations table
            'user_id',     // Local key on users table
            'student_id'   // Local key on student_details table
        );
    }

    /**
     * Override the password attribute name for authentication
     */
    public function getAuthPassword()
    {
        return $this->user_password;
    }

    /**
     * Override the email attribute name for authentication
     */
    public function getEmailForPasswordReset()
    {
        return $this->user_email;
    }

    /**
     * Get the name attribute (combination of first and last name)
     */
    public function getNameAttribute()
    {
        return trim($this->user_fname . ' ' . $this->user_lname);
    }

    /**
     * Get the email attribute
     */
    public function getEmailAttribute()
    {
        return $this->user_email;
    }

    /**
     * Get the password attribute
     */
    public function getPasswordAttribute()
    {
        return $this->user_password;
    }

    /**
     * Get the username for authentication (can be user_id, student_id, or email)
     */
    public function getAuthIdentifierName()
    {
        return 'user_id'; // Primary identifier
    }

    /**
     * Get the unique identifier for the user
     */
    public function getAuthIdentifier()
    {
        return $this->user_id;
    }

    /**
     * Get the remember token name
     */
    public function getRememberTokenName()
    {
        return 'token';
    }


    /**
     * Scope a query to only include users of a given role.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $role
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeRole($query, $role)
    {
        // Temporarily use the user_role column instead of relationships
        return $query->where('user_role', $role);
    }
    
    /**
     * Get the student details for the user.
     */
}
