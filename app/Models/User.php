<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'nim',
        'role',
        'photo',
        'phone',
        'batch_year',
        'status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Bind route key to 'nim' column for route model binding.
     */
    public function getRouteKeyName(): string
    {
        return 'username';
    }

    // --- RELASI ---

    /**
     * User memiliki satu Profile.
     */
    public function profile()
    {
        return $this->hasOne(Profile::class);
    }

    /**
     * User (student) memiliki banyak Project (sebagai creator).
     */
    public function projects()
    {
        return $this->hasMany(Project::class);
    }

    /**
     * User (lecturer) membimbing banyak Project (sebagai supervisor).
     */
    public function supervisedProjects()
    {
        return $this->hasMany(Project::class, 'supervisor_id');
    }

    /**
     * User (student) bisa berkolaborasi di banyak Project.
     */
    public function collaborations()
    {
        return $this->belongsToMany(Project::class, 'project_collaborators')
                    ->withPivot('role')
                    ->withTimestamps();
    }

    /**
     * User memiliki banyak Review.
     */
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    /**
     * User memiliki banyak Like.
     */
    public function likes()
    {
        return $this->hasMany(Like::class);
    }

    /**
     * User memiliki banyak Bookmark.
     */
    public function bookmarks()
    {
        return $this->hasMany(Bookmark::class);
    }

    // --- HELPER UNTUK CEK ROLE ---
    public function isAdmin()
    {
        return $this->role === 'admin';
    }
    
    public function isLecturer()
    {
        return $this->role === 'lecturer';
    }

    public function isStudent()
    {
        return $this->role === 'student';
    }
}