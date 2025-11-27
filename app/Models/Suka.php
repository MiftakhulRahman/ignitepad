<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Suka extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    // Mendefinisikan bahwa ini adalah polymorphic relation
    public function likeable()
    {
        return $this->morphTo();
    }
}