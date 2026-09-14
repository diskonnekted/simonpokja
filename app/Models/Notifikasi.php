<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class Notifikasi extends Model
{
    protected $table = 'notifikasis';

    protected $fillable = [
        'user_id',
        'judul',
        'pesan',
        'url_tujuan',
        'kategori',
        'tingkat',
        'dibaca_pada',
    ];

    protected $casts = [
        'dibaca_pada' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeUnread(Builder $query): Builder
    {
        return $query->whereNull('dibaca_pada');
    }

    public function markAsRead(): bool
    {
        if (is_null($this->dibaca_pada)) {
            return $this->update(['dibaca_pada' => now()]);
        }
        return true;
    }

    public function getIconAttribute(): string
    {
        return match ($this->kategori) {
            'pesan' => 'bi-chat-dots-fill',
            'anomali' => 'bi-exclamation-triangle-fill',
            'sanggahan' => 'bi-shield-exclamation',
            'progres' => 'bi-graph-up-arrow',
            'deadline' => 'bi-alarm-fill',
            default => 'bi-bell-fill',
        };
    }

    public function getColorClassAttribute(): string
    {
        return match ($this->tingkat) {
            'critical' => 'danger',
            'warning' => 'warning',
            default => 'primary',
        };
    }
}
