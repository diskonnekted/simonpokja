<?php

namespace App\Models;

use App\Models\Concerns\MencatatAudit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Penyedia extends Model
{
    use SoftDeletes;
    use MencatatAudit;

    protected $table = 'penyedias';

    protected $fillable = [
        'npwp', 'nib', 'nama', 'jenis_usaha', 'kualifikasi',
        'alamat', 'direktur', 'telepon', 'email', 'aktif',
    ];

    protected $casts = [
        'aktif' => 'boolean',
    ];

    public function pakets()
    {
        return $this->hasMany(PaketPengadaan::class);
    }
}
