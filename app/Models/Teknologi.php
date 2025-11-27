<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Teknologi extends Model
{
    protected $guarded = ['id'];

    public function proyeks()
    {
        return $this->belongsToMany(Proyek::class, 'proyek_teknologi');
    }
}