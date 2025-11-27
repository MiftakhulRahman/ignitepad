<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NilaiKriteria extends Model
{
    protected $fillable = [
        'submisi_id',
        'kriteria_penilaian_id',
        'nilai',
        'catatan',
    ];

    public function submisi()
    {
        return $this->belongsTo(SubmisiChallenge::class);
    }

    public function kriteria()
    {
        return $this->belongsTo(KriteriaPenilaian::class, 'kriteria_penilaian_id');
    }
}
