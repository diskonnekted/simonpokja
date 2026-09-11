<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProgresPekerjaan extends Model
{
    protected $fillable = [
        'paket_id', 'user_id', 'progress', 'status', 'catatan',
    ];

    protected $casts = [
        'progress' => 'integer',
    ];

    public function paket(): BelongsTo
    {
        return $this->belongsTo(PaketPengadaan::class, 'paket_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
