<?php

namespace App\Models;

use App\Models\PNUser;

/**
 * Compatibility shim: legacy code references App\Models\Student.
 * This class extends PNUser so existing code expecting a Student model works
 * while keeping the canonical user model in PNUser (from the Login schema).
 */
class Student extends PNUser
{
    // Intentionally empty - inherits table, primary key, and accessors from PNUser
}

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    protected $fillable = [
        'name',
        'gender',
        'batch',
        // ...add other columns as needed...
    ];

    // If your table name is not 'students', uncomment and set it:
    // protected $table = 'students';

    public function batch()
    {
        return $this->belongsTo(Batch::class, 'batch', 'year');
    }

    // ...add other relationships as needed...
}
