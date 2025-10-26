<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon',
    ];

    /**
     * Bind route key to 'slug' column.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Category memiliki banyak Project.
     */
    public function projects()
    {
        return $this->belongsToMany(Project::class, 'project_category');
    }
}