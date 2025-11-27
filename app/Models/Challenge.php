<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Challenge extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'persyaratan' => 'array',
        'kategori_diizinkan' => 'array',
        'tanggal_mulai' => 'datetime',
        'batas_waktu' => 'datetime',
        'tanggal_pengumuman' => 'datetime',
        'pemenang_diumumkan_pada' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($challenge) {
            $challenge->slug = Str::slug($challenge->judul) . '-' . Str::random(5);
        });
    }

    // --- RELASI ---

    public function pembuat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pembuat_id');
    }

    public function kriteriaPenilaian(): HasMany
    {
        return $this->hasMany(KriteriaPenilaian::class)->orderBy('urutan');
    }

    public function submisi(): HasMany
    {
        return $this->hasMany(SubmisiChallenge::class);
    }

    // Pemenang
    public function juara1(): BelongsTo { return $this->belongsTo(User::class, 'pemenang_1_id'); }
    public function juara2(): BelongsTo { return $this->belongsTo(User::class, 'pemenang_2_id'); }
    public function juara3(): BelongsTo { return $this->belongsTo(User::class, 'pemenang_3_id'); }
}