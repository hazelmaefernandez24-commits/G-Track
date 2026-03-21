<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'meal_type',
        'report_date',
        'meal_items',
        'feedback',
        'rating'
    ];

    protected $casts = [
        'report_date' => 'datetime',
        'meal_items' => 'array'
    ];

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }
} 