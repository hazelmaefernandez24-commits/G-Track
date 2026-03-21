<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;

class StudentDetail extends Model
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
        'student_id',
        'batch',
        'group',
        'student_number',
        'training_code',
    ];

    public static function get_student($student_id)
    {
        return self::where('student_id', $student_id)->first();
    }
    
    public function user()
    {
        return $this->belongsTo(PNUser::class, 'user_id', 'user_id');
    }

    public function visitor()
    {
        return $this->hasOne(Visitor::class, 'student_id', 'student_id');
    }

    public function academic()
    {
        return $this->hasOne(Academic::class, 'student_id', 'student_id');
    }

    public function goingOut()
    {
        return $this->hasOne(Going_out::class, 'student_id', 'student_id');
    }
}
