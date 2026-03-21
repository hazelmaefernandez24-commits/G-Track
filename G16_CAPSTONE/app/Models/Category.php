<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    // Persist categories into the Login application's database connection.
    // Configure connection details in `config/database.php` using the
    // DB_LOGIN_* environment variables (see added connection named "login").
    protected $connection = 'login';
    // allow description and parent_id to be mass assigned as well
    protected $fillable = ['name', 'description', 'parent_id', 'batch_requirements', 'color_code'];
    
    // Cast batch_requirements as array for automatic JSON handling
    protected $casts = [
        'batch_requirements' => 'array'
    ];

    public function assignments()
    {
        return $this->hasMany(Assignment::class);
    }

    /**
     * Get the parent category (main area)
     */
    public function parentCategory()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    /**
     * Get the sub-categories (sub-areas) under this main area
     */
    public function subCategories()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    /**
     * Check if this is a main area (no parent)
     */
    public function isMainArea()
    {
        return is_null($this->parent_id);
    }

    /**
     * Check if this is a sub-area (has parent)
     */
    public function isSubArea()
    {
        return !is_null($this->parent_id);
    }

    /**
     * Get the checklist for this category
     */
    public function checklist()
    {
        return $this->hasOne(CategoryChecklist::class);
    }
}