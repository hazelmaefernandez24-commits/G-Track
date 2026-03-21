<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Assignment extends Model
{
    protected $fillable = [
        'category_id',
        'start_date',
        'end_date',
        'status',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function assignmentMembers()
    {
        return $this->hasMany(AssignmentMember::class);
    }
}