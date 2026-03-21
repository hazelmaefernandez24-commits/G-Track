<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;

class StudentDetail extends Model
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'user_id',
        'student_id',
        'batch',
        'group',
        'student_number',
        'training_code',
    ];
    
        // This model should query the Login DB for canonical student details
        protected $connection = 'login';

    public function user()
    {
        return $this->belongsTo(PNUser::class, 'user_id', 'user_id');
    }
}
