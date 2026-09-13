<?php

namespace App\Models;

use App\Models\Concerns\MencatatAudit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Opd extends Model
{
    use SoftDeletes;
    use MencatatAudit;

    protected $table = 'opds';

    protected $fillable = [
        'kode', 'nama', 'singkatan', 'kepala', 'nip_kepala',
        'alamat', 'telepon', 'email',
    ];

    public function pakets()
    {
        return $this->hasMany(PaketPengadaan::class);
    }
}
