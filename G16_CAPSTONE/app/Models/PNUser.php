<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;

class PNUser extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'pnph_users';
    protected $primaryKey = 'user_id';
    public $incrementing = false;

    // Use the Login DB connection for user records that originate from the Login app
    protected $connection = 'login';
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
    ];

    protected $hidden = [
        'user_password',
    ];

    public function studentDetail()
    {
        return $this->hasOne(StudentDetail::class, 'user_id', 'user_id');
    }

    public function getFullNameAttribute()
    {
        return trim($this->user_fname . ' ' . $this->user_lname);
    }

    /**
     * Backwards-compatible accessor for legacy code that expects ->name
     * Returns the full name composed from first and last name.
     */
    public function getNameAttribute()
    {
        // If another accessor or attribute defines full_name, prefer it.
        $full = $this->getAttribute('full_name');
        if (!empty($full)) {
            return $full;
        }

        return $this->getFullNameAttribute();
    }

    /**
     * Convenience accessor to expose batch directly as ->batch
     * for legacy code that expects batch on the user object.
     */
    public function getBatchAttribute()
    {
        return optional($this->studentDetail)->batch;
    }
}
