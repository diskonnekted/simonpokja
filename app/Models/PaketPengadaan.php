<?php

namespace App\Models;

use App\Models\Concerns\MencatatAudit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaketPengadaan extends Model
{
    use SoftDeletes;
    use MencatatAudit;

    protected $table = 'paket_pengadaans';

    protected $fillable = [
        'kode_paket', 'nama_paket', 'opd_id', 'penyedia_id', 'jenis', 'metode',
        'sumber_dana', 'pagu', 'hps', 'nilai_kontrak', 'tahun_anggaran', 'status',
        'progress', 'tanggal_mulai', 'tanggal_selesai', 'lokasi', 'keterangan',
        'latitude', 'longitude', 'desa', 'kecamatan',
    ];

    protected $casts = [
        'pagu' => 'integer',
        'hps' => 'integer',
        'nilai_kontrak' => 'integer',
        'progress' => 'integer',
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
        'latitude' => 'float',
        'longitude' => 'float',
    ];

    public function opd()
    {
        return $this->belongsTo(Opd::class);
    }

    public function pokja()
    {
        return $this->belongsTo(Pokja::class);
    }

    public function perubahanJadwals()
    {
        return $this->hasMany(PerubahanJadwal::class, 'paket_id');
    }

    public function sanggahans()
    {
        return $this->hasMany(Sanggahan::class, 'paket_id');
    }

    public function penyedia()
    {
        return $this->belongsTo(Penyedia::class);
    }

    public function tahapans()
    {
        return $this->hasMany(Tahapan::class, 'paket_id')->orderBy('urutan');
    }

    public function evaluasis()
    {
        return $this->hasMany(Evaluasi::class, 'paket_id');
    }

    public function progresPekerjaans()
    {
        return $this->hasMany(ProgresPekerjaan::class, 'paket_id')->latest();
    }

    public function progresTerakhir()
    {
        return $this->hasOne(ProgresPekerjaan::class, 'paket_id')->latestOfMany();
    }

    public function pesans()
    {
        return $this->hasMany(PesanPaket::class, 'paket_id')->oldest();
    }

    // ====== Indikator Deadline ======

    /**
     * Status deadline pekerjaan dihitung dari tanggal_selesai.
     * Ambang dari config/monitoring.php: mendesak (<=7 hari), mendekati (<=30 hari).
     * Paket selesai/batal tidak dinilai.
     *
     * return: [kunci, label, kelas_badge, sisa_hari]
     */
    public function getStatusDeadlineAttribute(): array
    {
        if (! $this->tanggal_selesai || in_array($this->status, ['selesai', 'batal'])) {
            return ['normal', 'Aman', 'success', null];
        }

        $sisa = now()->startOfDay()->diffInDays($this->tanggal_selesai->copy()->startOfDay(), false);

        if ($sisa < 0) {
            return ['lewat', 'Melampaui deadline ' . abs($sisa) . ' hari', 'danger', (int) abs($sisa)];
        }

        if ($sisa <= config('monitoring.deadline.mendesak', 7)) {
            return ['mendesak', 'Mendesak — sisa ' . $sisa . ' hari', 'danger', (int) $sisa];
        }

        if ($sisa <= config('monitoring.deadline.mendekati', 30)) {
            return ['mendekati', 'Mendekati deadline — sisa ' . $sisa . ' hari', 'warning', (int) $sisa];
        }

        return ['normal', 'Aman — sisa ' . $sisa . ' hari', 'success', (int) $sisa];
    }

    // Label metode dalam Bahasa Indonesia
    public function getMetodeLabelAttribute(): string
    {
        return match ($this->metode) {
            'tender' => 'Tender',
            'seleksi' => 'Seleksi',
            'epurchasing' => 'e-Purchasing',
            'penunjukan_langsung' => 'Penunjukan Langsung',
            'pengadaan_langsung' => 'Pengadaan Langsung',
            'swakelola' => 'Swakelola',
            default => ucfirst($this->metode),
        };
    }

    public function getJenisLabelAttribute(): string
    {
        return match ($this->jenis) {
            'barang' => 'Barang',
            'jasa_konsultansi' => 'Jasa Konsultansi',
            'konstruksi' => 'Konstruksi',
            'jasa_lainnya' => 'Jasa Lainnya',
            default => ucfirst($this->jenis),
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'Draft',
            'persiapan' => 'Persiapan',
            'pemilihan' => 'Pemilihan',
            'kontrak' => 'Kontrak',
            'pelaksanaan' => 'Pelaksanaan',
            'selesai' => 'Selesai',
            'batal' => 'Batal',
            default => ucfirst($this->status),
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'gray',
            'persiapan' => 'info',
            'pemilihan' => 'warning',
            'kontrak' => 'primary',
            'pelaksanaan' => 'primary',
            'selesai' => 'success',
            'batal' => 'danger',
            default => 'gray',
        };
    }

    // Deviasi harga: (pagu - kontrak) / pagu
    public function getDeviasiAttribute(): ?float
    {
        if (!$this->pagu || !$this->nilai_kontrak) {
            return null;
        }
        return round((($this->pagu - $this->nilai_kontrak) / $this->pagu) * 100, 2);
    }

    /**
     * Progres tahapan proses pengadaan untuk mini bar bersegmen.
     * Sumber utama: relasi tahapans (5 tahap berurutan).
     * Fallback: derivasi dari kolom status paket (jika belum ada baris tahapan).
     */
    public function getTahapanProgresAttribute(): array
    {
        $tahapans = $this->tahapans;

        if ($tahapans->isNotEmpty()) {
            return $tahapans
                ->sortBy('urutan')
                ->map(fn ($t) => ['nama' => $t->nama_tahap, 'status' => $t->status])
                ->values()
                ->toArray();
        }

        // Fallback: turunkan dari status paket
        $tahapanStatus = [
            'draft' => ['belum', 'belum', 'belum', 'belum', 'belum'],
            'persiapan' => ['proses', 'belum', 'belum', 'belum', 'belum'],
            'pemilihan' => ['selesai', 'proses', 'belum', 'belum', 'belum'],
            'kontrak' => ['selesai', 'selesai', 'proses', 'belum', 'belum'],
            'pelaksanaan' => ['selesai', 'selesai', 'selesai', 'proses', 'belum'],
            'selesai' => ['selesai', 'selesai', 'selesai', 'selesai', 'selesai'],
            'batal' => ['belum', 'belum', 'belum', 'belum', 'belum'],
        ];

        $pola = $tahapanStatus[$this->status] ?? $tahapanStatus['draft'];
        $nama = ['Persiapan', 'Pemilihan', 'Kontrak', 'Pelaksanaan', 'Serah Terima'];

        return collect($pola)
            ->map(fn ($s, $i) => ['nama' => $nama[$i], 'status' => $s])
            ->toArray();
    }

    /**
     * Persentase progres tahapan: selesai = 100%, proses = 50%, belum = 0%.
     * Membulat ke kelipatan 5 agar tampilan ringkas.
     */
    public function getProgresPersenAttribute(): int
    {
        $tahapans = $this->tahapan_progres;
        if (empty($tahapans)) {
            return 0;
        }
        $skor = 0;
        foreach ($tahapans as $t) {
            $skor += match ($t['status']) {
                'selesai' => 100,
                'proses' => 50,
                default => 0,
            };
        }
        $persen = (int) round($skor / count($tahapans) / 5) * 5;

        return min(100, $persen);
    }

    /**
     * Warna segmen mini bar sesuai status tahapan.
     * Abu "belum" memakai #868e96 agar lolos WCAG 1.4.11 non-text (3:1) pada latar #f8f9fa.
     */
    public static function warnaTahapan(string $status): string
    {
        return match ($status) {
            'selesai' => '#198754', // hijau bootstrap success (4.53:1 vs putih)
            'proses' => '#0d6efd',  // biru bootstrap primary (4.50:1 vs putih)
            default => '#868e96',   // abu (3.15:1 vs #f8f9fa, lolos non-text 3:1)
        };
    }

    public function scopeTahun($query, $tahun)
    {
        return $query->where('tahun_anggaran', $tahun);
    }

    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }
}
