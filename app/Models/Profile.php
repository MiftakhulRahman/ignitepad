<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'bio',
        'skills',
        'linkedin_url',
        'github_url',
        'portfolio_url',
        'cv_file',
        'achievements',
    ];

    /**
     * Cast kolom JSON ke array
     */
    protected function casts(): array
    {
        return [
            'skills' => 'array',
            'achievements' => 'array',
        ];
    }

    /**
     * Profile dimiliki oleh satu User.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}