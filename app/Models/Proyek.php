<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Str;

class Proyek extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'galeri_gambar' => 'array',
        'tag' => 'array',
        'terbit_pada' => 'datetime',
        'boleh_komentar' => 'boolean',
        'unggulan' => 'boolean',
    ];

    // Otomatis buat slug saat judul diisi
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($proyek) {
            $proyek->slug = Str::slug($proyek->judul) . '-' . Str::random(5);
        });
    }

    // --- RELASI ---

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function kategori(): BelongsTo
    {
        return $this->belongsTo(KategoriProyek::class, 'kategori_id');
    }

    public function teknologi(): BelongsToMany
    {
        return $this->belongsToMany(Teknologi::class, 'proyek_teknologi');
    }

    public function kolaborator(): HasMany
    {
        return $this->hasMany(Kolaborator::class);
    }

    // Alias plural untuk konsistensi
    public function kolaborators(): HasMany
    {
        return $this->hasMany(Kolaborator::class);
    }

    public function komentar(): HasMany
    {
        return $this->hasMany(Komentar::class);
    }

    // Polymorphic Relation untuk Like
    public function likes(): MorphMany
    {
        return $this->morphMany(Suka::class, 'likeable');
    }
    
    // Cek apakah user tertentu sudah like
    public function isLikedBy(User $user)
    {
        return $this->likes()->where('user_id', $user->id)->exists();
    }
}