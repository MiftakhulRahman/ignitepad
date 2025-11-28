<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dislike extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    // Mendefinisikan bahwa ini adalah polymorphic relation
    public function dislikeable()
    {
        return $this->morphTo();
    }

    // Relationship ke user
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
