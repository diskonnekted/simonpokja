<?php

namespace App\Models;

use App\Models\Concerns\MencatatAudit;
use Illuminate\Database\Eloquent\Model;

class AlertAnomali extends Model
{
    use MencatatAudit;

    protected $table = 'alert_anomalis';

    protected $fillable = [
        'paket_id', 'pokja_id', 'jenis', 'tingkat', 'deskripsi', 'status',
    ];

    public function paket()
    {
        return $this->belongsTo(PaketPengadaan::class);
    }

    public function pokja()
    {
        return $this->belongsTo(Pokja::class);
    }
}
