<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'user_id',
        'rating',
        'comment',
        'status',
    ];

    /**
     * Review dimiliki oleh satu Project.
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Review dimiliki oleh satu User (reviewer).
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}