<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubmisiChallenge extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'dikirim_pada' => 'datetime',
        'dinilai_pada' => 'datetime',
    ];

    // --- RELASI ---

    public function challenge(): BelongsTo
    {
        return $this->belongsTo(Challenge::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function proyek(): BelongsTo
    {
        return $this->belongsTo(Proyek::class);
    }

    public function nilaiKriteria(): HasMany
    {
        return $this->hasMany(NilaiKriteria::class, 'submisi_id');
    }
}