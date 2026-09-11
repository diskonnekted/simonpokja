<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'pokja_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // ====== Kewenangan ======

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isPokja(): bool
    {
        return $this->role === 'pokja';
    }

    public function pokja(): BelongsTo
    {
        return $this->belongsTo(Pokja::class);
    }

    public function progresPekerjaans(): HasMany
    {
        return $this->hasMany(ProgresPekerjaan::class);
    }

    /**
     * Arah redirect setelah login sesuai peran.
     */
    public function routeBeranda(): string
    {
        return $this->isAdmin()
            ? route('peta.index')
            : route('pokja.dasbor');
    }
}
