<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditChecklist extends Model
{
    protected $table = 'audit_checklists';

    protected $fillable = [
        'pokja_id', 'paket_id', 'kategori', 'poin', 'jawaban', 'catatan', 'auditor_id',
    ];

    public function pokja()
    {
        return $this->belongsTo(Pokja::class);
    }

    public function paket()
    {
        return $this->belongsTo(PaketPengadaan::class);
    }

    // 6 kategori checklist audit sesuai dokumen "Pokja II Risk Audit Checklist"
    public static function kategoriList(): array
    {
        return [
            1 => 'Beban Kerja & Distribusi Penugasan',
            2 => 'Kepatuhan Jadwal & Pengunduran Lelang (SLA)',
            3 => 'Spesifikasi Teknis & Dokumen Pemilihan',
            4 => 'Objektivitas Evaluasi & Penilaian Penawaran',
            5 => 'Jejak Digital & Komunikasi Formal',
            6 => 'Penanganan Sanggahan & Tender Gagal',
        ];
    }

    // Pertanyaan default per kategori.
    // PENTING: semua poin berpolaritas TEMUAN — jawaban "ya" selalu berarti
    // ditemukan masalah/penyimpangan (konsisten dengan badge risiko di view).
    public static function poinDefault(int $kategori): array
    {
        return match ($kategori) {
            1 => [
                'Apakah akumulasi paket melebihi ambang batas rasio ideal per anggota Pokja?',
                'Apakah terdapat penumpukan paket pagu besar pada personel tertentu?',
                'Apakah ada keterkaitan anggota dengan PPK/penyedia pada paket bermasalah?',
            ],
            2 => [
                'Apakah pengunduran jadwal tahapan tender terjadi lebih dari 3 kali?',
                'Apakah ada perubahan jadwal tanpa Berita Acara yang sah?',
                'Apakah perubahan jadwal dilakukan di akhir jam kerja / menjelang tutup penawaran?',
            ],
            3 => [
                'Apakah reviu dokumen pemilihan bersama PPK dilewati / tidak dilakukan?',
                'Apakah kriteria kualifikasi diubah setelah pengumuman pemilihan?',
                'Apakah terdapat addendum mendadak yang berpotensi menggugurkan peserta?',
            ],
            4 => [
                'Apakah evaluasi menyimpang dari parameter & bobot yang ditetapkan awal?',
                'Apakah ada pengguguran peserta tanpa bukti teknis yang objektif?',
                'Apakah ada perbedaan perlakuan evaluasi antar peserta sejenis?',
            ],
            5 => [
                'Apakah terdapat aksi penting yang tidak terekam dalam audit trail?',
                'Apakah ada indikasi komunikasi informal di luar sistem?',
                'Apakah akun diakses dari IP/perangkat di luar lingkungan resmi?',
            ],
            6 => [
                'Apakah ada sanggahan yang dijawab melewati SLA 3 hari / tanpa substansi?',
                'Apakah sanggahan mengindikasikan penyimpangan prosedur?',
                'Apakah penetapan tender gagal dipaksakan / tidak sesuai ketentuan?',
            ],
            default => [],
        };
    }
}
