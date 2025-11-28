<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Komentar extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    // Append virtual attributes
    protected $appends = ['jumlah_balasan'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function proyek()
    {
        return $this->belongsTo(Proyek::class);
    }
    
    // Self-relation untuk balasan komentar (reply)
    public function balasan()
    {
        return $this->hasMany(Komentar::class, 'induk_id');
    }

    public function likes()
    {
        return $this->morphMany(Suka::class, 'likeable');
    }

    public function dislikes()
    {
        return $this->morphMany(Dislike::class, 'dislikeable');
    }

    // Virtual attribute untuk jumlah balasan
    public function getJumlahBalasanAttribute()
    {
        return $this->balasan()->count();
    }
}