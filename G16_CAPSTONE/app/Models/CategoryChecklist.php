<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CategoryChecklist extends Model
{
    protected $table = 'category_checklists';
    
    protected $fillable = [
        'category_id',
        'checklist_items'
    ];

    protected $casts = [
        'checklist_items' => 'array'
    ];

    /**
     * Get the category that owns the checklist
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
