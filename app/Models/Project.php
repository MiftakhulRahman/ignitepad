<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str; // Kita akan butuh ini nanti

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'slug',
        'description',
        'thumbnail',
        'demo_url',
        'repository_url',
        'status',
        'visibility',
        'semester',
        'academic_year',
        'supervisor_id',
        'featured',
    ];

    /**
     * Bind route key to 'slug' column.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Otomatis buat slug saat menyimpan title
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($project) {
            if (empty($project->slug)) {
                $project->slug = Str::slug($project->title) . '-' . uniqid();
            }
        });
    }

    // --- RELASI ---

    /**
     * Project dimiliki oleh satu User (creator).
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Project dibimbing oleh satu User (supervisor).
     */
    public function supervisor()
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    /**
     * Project memiliki banyak Category.
     */
    public function categories()
    {
        return $this->belongsToMany(Category::class, 'project_category');
    }

    /**
     * Project menggunakan banyak Technology.
     */
    public function technologies()
    {
        return $this->belongsToMany(Technology::class, 'project_technology');
    }

    /**
     * Project memiliki banyak screenshot (ProjectImage).
     */
    public function images()
    {
        return $this->hasMany(ProjectImage::class);
    }

    /**
     * Project memiliki banyak User (collaborators).
     */
    public function collaborators()
    {
        return $this->belongsToMany(User::class, 'project_collaborators')
                    ->withPivot('role')
                    ->withTimestamps();
    }

    /**
     * Project memiliki banyak Review.
     */
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Project memiliki banyak Like.
     */
    public function likes()
    {
        return $this->hasMany(Like::class);
    }

    /**
     * Project dimiliki banyak Bookmark.
     */
    public function bookmarks()
    {
        return $this->hasMany(Bookmark::class);
    }
}