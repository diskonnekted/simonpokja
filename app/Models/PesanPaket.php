<?php

namespace App\Models;

use App\Models\Concerns\MencatatAudit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PesanPaket extends Model
{
    use MencatatAudit;

    protected $fillable = [
        'paket_id', 'user_id', 'pesan', 'dibaca_pada',
    ];

    protected $casts = [
        'dibaca_pada' => 'datetime',
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
