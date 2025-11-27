<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $guarded = ['id'];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'registrasi_selesai' => 'boolean',
        'status_aktif' => 'boolean',
    ];

    // --- RELASI (RELATIONSHIPS) ---

    // Relasi ke Peran (Many to Many)
    public function perans(): BelongsToMany
    {
        return $this->belongsToMany(Peran::class, 'pengguna_peran', 'user_id', 'peran_id');
    }

    // Relasi ke Program Studi (Belongs To)
    public function prodi(): BelongsTo
    {
        return $this->belongsTo(ProgramStudi::class, 'prodi_id');
    }

    // Cek apakah user punya peran tertentu (Helper function)
    public function hasRole($roleSlug)
    {
        return $this->perans->contains('slug', $roleSlug);
    }
}