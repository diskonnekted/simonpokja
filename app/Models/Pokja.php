<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pokja extends Model
{
    use SoftDeletes;

    protected $table = 'pokjas';

    protected $fillable = [
        'nama', 'bidang', 'ketua', 'nip_ketua', 'anggota', 'kapasitas_ideal', 'aktif',
    ];

    protected $casts = [
        'anggota' => 'array',
        'aktif' => 'boolean',
    ];

    public function pakets()
    {
        return $this->hasMany(PaketPengadaan::class);
    }

    public function alerts()
    {
        return $this->hasMany(AlertAnomali::class);
    }

    public function checklists()
    {
        return $this->hasMany(AuditChecklist::class);
    }

    // ===== INDIKATOR KINERJA =====

    // Beban kerja = jumlah paket aktif (belum selesai/batal)
    public function getBebanKerjaAttribute(): int
    {
        return $this->pakets()
            ->whereNotIn('status', ['selesai', 'batal'])
            ->count();
    }

    // Rasio paket per anggota vs kapasitas ideal
    public function getRasioBebanAttribute(): float
    {
        $jumlahAnggota = max(count($this->anggota ?? []) ?: 1, 1);
        return round($this->beban_kerja / $jumlahAnggota, 1);
    }

    public function getOverloadAttribute(): bool
    {
        return $this->rasio_beban > $this->kapasitas_ideal;
    }

    // Kepatuhan SLA: % paket tanpa pengunduran jadwal berulang (>3x)
    public function getKepatuhanSlaAttribute(): float
    {
        $paketIds = $this->pakets()->pluck('id');
        if ($paketIds->isEmpty()) {
            return 100.0;
        }

        $patuh = 0;
        $total = 0;
        foreach (\App\Models\PerubahanJadwal::select('paket_id')
            ->selectRaw('COUNT(*) as jumlah')
            ->whereIn('paket_id', $paketIds)
            ->groupBy('paket_id')
            ->get() as $row) {
            $total++;
            if ($row->jumlah <= 3) {
                $patuh++;
            }
        }

        $tanpaPerubahan = $paketIds->count() - $total;
        $totalPatuh = $patuh + $tanpaPerubahan;

        return $paketIds->count() > 0
            ? round(($totalPatuh / $paketIds->count()) * 100, 1)
            : 100.0;
    }

    // Persentase tender gagal dari paket yang ditangani
    public function getTenderGagalPersenAttribute(): float
    {
        $total = $this->pakets()->count();
        if ($total === 0) {
            return 0.0;
        }
        $gagal = $this->pakets()->where('tender_gagal', true)->count();
        return round(($gagal / $total) * 100, 1);
    }

    // Jumlah alert aktif tingkat critical
    public function getAlertCriticalAttribute(): int
    {
        return $this->alerts()->where('status', 'aktif')->where('tingkat', 'critical')->count();
    }

    // Skor risiko keseluruhan pokja (0-100): beban + delay + alert + tender gagal
    public function getSkorRisikoAttribute(): int
    {
        $skor = 0;
        // Beban (0-35)
        $skor += min(35, (int) (($this->rasio_beban / max($this->kapasitas_ideal, 1)) * 25));
        // Delay berulang (0-25): paket dengan >3 pengunduran
        $paketDelay = \App\Models\PerubahanJadwal::select('paket_id')
            ->selectRaw('COUNT(*) as jumlah')
            ->whereIn('paket_id', $this->pakets()->pluck('id'))
            ->groupBy('paket_id')
            ->havingRaw('COUNT(*) > 3')
            ->get()->count();
        $skor += min(25, $paketDelay * 8);
        // Alert critical aktif (0-25)
        $skor += min(25, $this->alert_critical * 12);
        // Tender gagal (0-15)
        $skor += min(15, (int) ($this->tender_gagal_persen / 2));

        return min(100, $skor);
    }

    public function getLabelRisikoAttribute(): string
    {
        return match (true) {
            $this->skor_risiko >= 70 => 'KRITIS',
            $this->skor_risiko >= 40 => 'TINGGI',
            $this->skor_risiko >= 20 => 'SEDANG',
            default => 'RENDAH',
        };
    }

    public function getWarnaRisikoAttribute(): string
    {
        return match (true) {
            $this->skor_risiko >= 70 => 'danger',
            $this->skor_risiko >= 40 => 'warning',
            $this->skor_risiko >= 20 => 'info',
            default => 'success',
        };
    }
}
