<?php

namespace App\Models;

use App\Models\Concerns\MencatatAudit;
use Illuminate\Database\Eloquent\Model;

class PerubahanJadwal extends Model
{
    use MencatatAudit;

    protected $table = 'perubahan_jadwals';

    protected $fillable = [
        'paket_id', 'tanggal', 'jam', 'jenis', 'tahap_terkait', 'alasan', 'ada_berita_acara',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function paket()
    {
        return $this->belongsTo(PaketPengadaan::class, 'paket_id');
    }
}
