<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Technology extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'icon',
        'color',
    ];

    /**
     * Bind route key to 'slug' column.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Technology digunakan di banyak Project.
     */
    public function projects()
    {
        return $this->belongsToMany(Project::class, 'project_technology');
    }
}