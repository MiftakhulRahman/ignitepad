<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Kolaborator extends Model
{
    protected $fillable = [
        'proyek_id',
        'user_id',
        'peran_kolaborator',
        'bisa_edit',
        'bisa_hapus',
        'status_undangan',
        'diundang_oleh',
        'direspon_pada',
    ];

    protected $casts = [
        'bisa_edit' => 'boolean',
        'bisa_hapus' => 'boolean',
        'direspon_pada' => 'datetime',
    ];

    // Relationships
    public function proyek(): BelongsTo
    {
        return $this->belongsTo(Proyek::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function diundangOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diundang_oleh');
    }
}
