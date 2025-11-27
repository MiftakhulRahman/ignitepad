<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProgramStudi extends Model
{
    protected $fillable = ['nama', 'kode', 'fakultas'];

    public function users()
    {
        return $this->hasMany(User::class, 'prodi_id');
    }
}
