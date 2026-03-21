<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CategoryLimit extends Model
{
    protected $fillable = [
        'category_name',
        'max_total',
        'max_boys',
        'max_girls',
    ];
}