<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tahapan extends Model
{
    protected $table = 'tahapans';

    protected $fillable = [
        'paket_id', 'nama_tahap', 'urutan', 'status',
        'tanggal_rencana', 'tanggal_aktual', 'keterangan',
    ];

    protected $casts = [
        'tanggal_rencana' => 'date',
        'tanggal_aktual' => 'date',
    ];

    public function paket()
    {
        return $this->belongsTo(PaketPengadaan::class, 'paket_id');
    }
}
