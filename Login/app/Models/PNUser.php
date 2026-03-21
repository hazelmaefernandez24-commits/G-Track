<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;

class PNUser extends Authenticatable
{
    use HasApiTokens;

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
        'token'
    ];

    protected $nullable = [
        'user_mInitial',
        'user_suffix',
    ];

    // Relation to StudentDetail (batch info)
    public function studentDetail()
    {
        return $this->hasOne(\App\Models\StudentDetail::class, 'user_id', 'user_id');
    }

    // Provide a 'name' attribute for backward compatibility
    public function getNameAttribute()
    {
        return trim(($this->user_fname ?? '') . ' ' . ($this->user_lname ?? ''));
    }

    // Provide a 'batch' attribute accessor
    public function getBatchAttribute()
    {
        return $this->studentDetail ? $this->studentDetail->batch : null;
    }
}
