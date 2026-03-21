<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

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
        'gender',
        'user_email',
        'user_role',
        'user_password',
        'status',
        'is_temp_password',
        'token',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'user_password',
        'remember_token',
    ];

    /**
     * Specify the correct table name.
     *
     * @var string
     */
    protected $table = 'pnph_users';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'user_password' => 'hashed',
            'is_temp_password' => 'boolean',
        ];
    }

    /**
     * Specify user_id as the primary key.
     *
     * @var string
     */
    protected $primaryKey = 'user_id';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The "type" of the auto-incrementing ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * Get the name of the unique identifier for the user.
     *
     * @return string
     */
    public function getAuthIdentifierName()
    {
        return 'user_id';
    }

    /**
     * Get the unique identifier for the user.
     *
     * @return mixed
     */
    public function getAuthIdentifier()
    {
        return $this->user_id;
    }

    /**
     * Get the password for the user.
     *
     * @return string
     */
    public function getAuthPassword()
    {
        return $this->user_password;
    }

    /**
     * Get the email address for the user.
     *
     * @return string
     */
    public function getEmailForPasswordReset()
    {
        return $this->user_email;
    }

    /**
     * Specify user_id as the username field.
     *
     * @return string
     */
    protected function username(): string
    {
        return 'user_id';
    }

    public function finance()
    {
        return $this->hasOne(Finance::class, 'user_id', 'user_id');
    }

    public function studentDetails()
    {
        return $this->hasOne(StudentDetails::class, 'user_id', 'user_id');
    }

    /**
     * Get the profile name based on the user's role.
     *
     * @return string
     */
    public function getProfileNameAttribute()
    {
        if ($this->user_role === 'finance' && $this->finance) {
            return $this->finance->first_name . ' ' . $this->finance->last_name;
        }

        if ($this->user_role === 'student') {
            return $this->user_fname . ' ' . $this->user_lname;
        }

        return 'Unknown User'; // Provide a default value if no related record is found
    }

    public function getFullNameAttribute()
    {
        if ($this->user_role === 'finance' && $this->finance) {
            return $this->finance->first_name . ' ' . $this->finance->last_name;
        }

        if ($this->user_role === 'student') {
            return $this->user_fname . ' ' . $this->user_lname;
        }

        return 'Unknown User'; // Default if no related record is found
    }

    // In Batch.php
protected $casts = [
    'batch_year' => 'string',
];
    
}
