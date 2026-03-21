<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Specify the correct table name.
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
     * Indicates if the ID is auto-incrementing.
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
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'user_email',
        'user_password',
        'user_role',
        'user_fname',
        'user_lname',
        'user_mInitial',
        'user_suffix',
        'gender',
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
        'token',
    ];

    /**
     * Use user_id for authentication.
     *
     * @return string
     */
    public function getAuthIdentifierName()
    {
        return 'user_id';
    }

    /**
     * Use user_password for authentication.
     *
     * @return string
     */
    public function getAuthPassword()
    {
        return $this->user_password;
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Add helper for full name.
     *
     * @return string
     */
    public function getFullNameAttribute()
    {
        return trim($this->user_fname . ' ' . $this->user_lname);
    }
}
/*
    {
        if ($this->role === 'admin' && $this->admin) {
            return $this->admin->first_name . ' ' . $this->admin->last_name;
     }

        if ($this->role === 'educator' && $this->educator) {
            return $this->educator->first_name . ' ' . $this->educator->last_name;
        }

        if ($this->role === 'student' && $this->student) {
            return $this->student->first_name . ' ' . $this->student->last_name;
        }

        if ($this->role === 'inspector' && $this->inspector) {
            return $this->inspector->first_name . ' ' . $this->inspector->last_name;
        }
        return 'Unknown User'; // Default if no related record is found
    }
*/