<?php

namespace App\Models;

use App\Models\Concerns\MencatatAudit;
use Illuminate\Database\Eloquent\Model;

class Evaluasi extends Model
{
    use MencatatAudit;

    protected $table = 'evaluasis';

    protected $fillable = [
        'paket_id', 'penyedia_id', 'jenis', 'skor', 'hasil', 'catatan', 'tanggal',
    ];

    protected $casts = [
        'skor' => 'decimal:2',
        'tanggal' => 'date',
    ];

    public function paket()
    {
        return $this->belongsTo(PaketPengadaan::class, 'paket_id');
    }

    public function penyedia()
    {
        return $this->belongsTo(Penyedia::class);
    }
}
