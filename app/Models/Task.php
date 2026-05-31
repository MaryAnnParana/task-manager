<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    // Allow mass assignment for these database columns
    protected $fillable = [
        'title',
        'description',
        'category_id',
        'due_date',
        'status',
    ];

    /**
     * Get the category that owns the task (Inverse of One-to-Many).
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}