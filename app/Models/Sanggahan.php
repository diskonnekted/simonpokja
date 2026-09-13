<?php

namespace App\Models;

use App\Models\Concerns\MencatatAudit;
use Illuminate\Database\Eloquent\Model;

class Sanggahan extends Model
{
    use MencatatAudit;

    protected $table = 'sanggahans';

    protected $fillable = [
        'paket_id', 'penyedia_id', 'tanggal_masuk', 'tanggal_dijawab', 'hasil', 'substantif', 'catatan',
    ];

    protected $casts = [
        'tanggal_masuk' => 'date',
        'tanggal_dijawab' => 'date',
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
