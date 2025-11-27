<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KriteriaPenilaian extends Model
{
    protected $fillable = [
        'challenge_id',
        'nama_kriteria',
        'bobot_persen',
        'deskripsi',
    ];

    public function challenge()
    {
        return $this->belongsTo(Challenge::class);
    }
}
