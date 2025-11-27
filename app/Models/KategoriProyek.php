<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KategoriProyek extends Model
{
    protected $guarded = ['id'];
    
    public function proyeks()
    {
        return $this->hasMany(Proyek::class, 'kategori_id');
    }
}