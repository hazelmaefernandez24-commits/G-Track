<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ClassModel extends Model
{
    protected $table = 'classes'; // Specify the table name since it differs from model name

    protected $fillable = [
        'class_id',
        'class_name',
        'school_id',
        'batch'
    ];

    protected $primaryKey = 'id';

    protected $attributes = [
        'class_name' => null
    ];

    protected static function booted()
    {
        static::creating(function ($class) {
            if (isset($class->attributes['name'])) {
                $class->attributes['class_name'] = $class->attributes['name'];
                unset($class->attributes['name']);
            }
        });
    }

    public function school()
    {
        return $this->belongsTo(School::class, 'school_id', 'school_id');
    }

    public function students()
    {
        return $this->belongsToMany(PNUser::class, 'class_student', 'class_id', 'user_id')
            ->withTimestamps();
    }

    public function subjects()
{
    return $this->belongsToMany(Subject::class, 'class_subject', 'class_id', 'subject_id');
}





    public static function test()
    {
        return "Model is working";
    }
} 