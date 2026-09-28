<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AlertAnomali;
use App\Models\AuditChecklist;
use App\Models\AuditLog;
use App\Models\Evaluasi;
use App\Models\PaketPengadaan;
use App\Models\PerubahanJadwal;
use App\Models\PesanPaket;
use App\Models\Pokja;
use App\Models\ProgresPekerjaan;
use App\Models\Sanggahan;
use App\Models\Tahapan;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

abstract class ApiController extends Controller
{
    /**
     * Respons JSON sukses dengan envelope standar.
     */
    protected function ok(mixed $data = null, string $message = 'OK', int $status = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $status);
    }

    /**
     * Respons JSON untuk hasil paginasi.
     */
    protected function page(LengthAwarePaginator $paginator, string $message = 'OK'): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $paginator->items(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
            ],
            'links' => [
                'first' => $paginator->url(1),
                'last' => $paginator->url($paginator->lastPage()),
                'prev' => $paginator->previousPageUrl(),
                'next' => $paginator->nextPageUrl(),
            ],
        ], 200);
    }

    protected function notFound(string $message = 'Data tidak ditemukan.'): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
        ], 404);
    }

    /**
     * Ambil nilai per_page yang dibatasi (1 - 100).
     */
    protected function limit(Request $request): int
    {
        $max = (int) config('sibijak.per_page_max', 100);

        return min(max((int) $request->query('per_page', 15), 1), $max);
    }

    /**
     * Serialisasi entitas PaketPengadaan menjadi array JSON.
     */
    protected function mapPaket(PaketPengadaan $p): array
    {
        $deadline = $p->status_deadline;

        $data = [
            'id' => $p->id,
            'kode_paket' => $p->kode_paket,
            'nama_paket' => $p->nama_paket,
            'tahun_anggaran' => (int) $p->tahun_anggaran,
            'jenis' => $p->jenis,
            'jenis_label' => $p->jenis_label,
            'metode' => $p->metode,
            'metode_label' => $p->metode_label,
            'sumber_dana' => $p->sumber_dana,
            'status' => $p->status,
            'status_label' => $p->status_label,
            'risiko' => $p->risiko,
            'tender_gagal' => (bool) $p->tender_gagal,
            'tahap_tender' => $p->tahap_tender,
            'pagu' => (int) $p->pagu,
            'hps' => (int) $p->hps,
            'nilai_kontrak' => (int) $p->nilai_kontrak,
            'deviasi_persen' => $p->deviasi,
            'progres_persen' => (int) $p->progres_persen,
            'tanggal_mulai' => $p->tanggal_mulai?->toDateString(),
            'tanggal_selesai' => $p->tanggal_selesai?->toDateString(),
            'lokasi' => $p->lokasi,
            'desa' => $p->desa,
            'kecamatan' => $p->kecamatan,
            'latitude' => $p->latitude,
            'longitude' => $p->longitude,
            'keterangan' => $p->keterangan,
            'deadline' => [
                'status' => $deadline[0] ?? 'normal',
                'sisa_hari' => $deadline[3] ?? null,
            ],
            'opd_id' => $p->opd_id,
            'pokja_id' => $p->pokja_id,
            'penyedia_id' => $p->penyedia_id,
            'created_at' => $p->created_at?->toIso8601String(),
            'updated_at' => $p->updated_at?->toIso8601String(),
        ];

        if ($p->relationLoaded('opd') && $p->opd) {
            $data['opd'] = [
                'id' => $p->opd->id,
                'kode' => $p->opd->kode,
                'nama' => $p->opd->nama,
                'singkatan' => $p->opd->singkatan,
            ];
        }

        if ($p->relationLoaded('pokja') && $p->pokja) {
            $data['pokja'] = [
                'id' => $p->pokja->id,
                'nama' => $p->pokja->nama,
                'bidang' => $p->pokja->bidang,
            ];
        }

        if ($p->relationLoaded('penyedia') && $p->penyedia) {
            $data['penyedia'] = [
                'id' => $p->penyedia->id,
                'npwp' => $p->penyedia->npwp,
                'nama' => $p->penyedia->nama,
            ];
        }

        return $data;
    }

    /**
     * Serialisasi entitas Pokja menjadi array JSON.
     */
    protected function mapPokja(Pokja $p): array
    {
        return [
            'id' => $p->id,
            'nama' => $p->nama,
            'bidang' => $p->bidang,
            'ketua' => $p->ketua,
            'nip_ketua' => $p->nip_ketua,
            'anggota' => $p->anggota ?? [],
            'jumlah_anggota' => is_array($p->anggota) ? count($p->anggota) : 0,
            'kapasitas_ideal' => (int) $p->kapasitas_ideal,
            'aktif' => (bool) $p->aktif,
            'kinerja' => [
                'beban_kerja' => $p->beban_kerja,
                'rasio_beban' => $p->rasio_beban,
                'overload' => $p->overload,
                'kepatuhan_sla' => $p->kepatuhan_sla,
                'tender_gagal_persen' => $p->tender_gagal_persen,
                'alert_critical' => $p->alert_critical,
                'skor_risiko' => $p->skor_risiko,
                'label_risiko' => $p->label_risiko,
                'warna_risiko' => $p->warna_risiko,
            ],
        ];
    }

    // =====================================================
    // Serialisasi entitas turunan paket
    // =====================================================
    protected function mapTahapan(Tahapan $t): array
    {
        return [
            'id' => $t->id,
            'paket_id' => $t->paket_id,
            'nama_tahap' => $t->nama_tahap,
            'urutan' => (int) $t->urutan,
            'status' => $t->status,
            'tanggal_rencana' => $t->tanggal_rencana?->toDateString(),
            'tanggal_aktual' => $t->tanggal_aktual?->toDateString(),
            'keterangan' => $t->keterangan,
            'created_at' => $t->created_at?->toIso8601String(),
            'updated_at' => $t->updated_at?->toIso8601String(),
        ];
    }

    protected function mapEvaluasi(Evaluasi $e): array
    {
        return [
            'id' => $e->id,
            'paket_id' => $e->paket_id,
            'penyedia_id' => $e->penyedia_id,
            'jenis' => $e->jenis,
            'skor' => (float) $e->skor,
            'hasil' => $e->hasil,
            'catatan' => $e->catatan,
            'tanggal' => $e->tanggal?->toDateString(),
            'penyedia' => ($e->relationLoaded('penyedia') && $e->penyedia)
                ? ['id' => $e->penyedia->id, 'nama' => $e->penyedia->nama]
                : null,
            'created_at' => $e->created_at?->toIso8601String(),
            'updated_at' => $e->updated_at?->toIso8601String(),
        ];
    }

    protected function mapSanggahan(Sanggahan $s): array
    {
        return [
            'id' => $s->id,
            'paket_id' => $s->paket_id,
            'penyedia_id' => $s->penyedia_id,
            'tanggal_masuk' => $s->tanggal_masuk?->toDateString(),
            'tanggal_dijawab' => $s->tanggal_dijawab?->toDateString(),
            'hasil' => $s->hasil,
            'substantif' => (bool) $s->substantif,
            'catatan' => $s->catatan,
            'penyedia' => ($s->relationLoaded('penyedia') && $s->penyedia)
                ? ['id' => $s->penyedia->id, 'nama' => $s->penyedia->nama]
                : null,
            'created_at' => $s->created_at?->toIso8601String(),
            'updated_at' => $s->updated_at?->toIso8601String(),
        ];
    }

    protected function mapProgres(ProgresPekerjaan $p): array
    {
        return [
            'id' => $p->id,
            'paket_id' => $p->paket_id,
            'user_id' => $p->user_id,
            'progress' => (int) $p->progress,
            'status' => $p->status,
            'catatan' => $p->catatan,
            'user' => ($p->relationLoaded('user') && $p->user)
                ? ['id' => $p->user->id, 'nama' => $p->user->name]
                : null,
            'created_at' => $p->created_at?->toIso8601String(),
            'updated_at' => $p->updated_at?->toIso8601String(),
        ];
    }

    protected function mapPerubahanJadwal(PerubahanJadwal $p): array
    {
        return [
            'id' => $p->id,
            'paket_id' => $p->paket_id,
            'tanggal' => $p->tanggal?->toDateString(),
            'jam' => $p->jam
                ? (is_scalar($p->jam) ? substr((string) $p->jam, 0, 8) : $p->jam->format('H:i:s'))
                : null,
            'jenis' => $p->jenis,
            'tahap_terkait' => $p->tahap_terkait,
            'alasan' => $p->alasan,
            'ada_berita_acara' => (bool) $p->ada_berita_acara,
            'created_at' => $p->created_at?->toIso8601String(),
            'updated_at' => $p->updated_at?->toIso8601String(),
        ];
    }

    protected function mapPesan(PesanPaket $p): array
    {
        return [
            'id' => $p->id,
            'paket_id' => $p->paket_id,
            'user_id' => $p->user_id,
            'pesan' => $p->pesan,
            'dibaca_pada' => $p->dibaca_pada?->toIso8601String(),
            'user' => ($p->relationLoaded('user') && $p->user)
                ? ['id' => $p->user->id, 'nama' => $p->user->name]
                : null,
            'created_at' => $p->created_at?->toIso8601String(),
            'updated_at' => $p->updated_at?->toIso8601String(),
        ];
    }

    protected function mapAlert(AlertAnomali $a): array
    {
        return [
            'id' => $a->id,
            'paket_id' => $a->paket_id,
            'pokja_id' => $a->pokja_id,
            'jenis' => $a->jenis,
            'tingkat' => $a->tingkat,
            'deskripsi' => $a->deskripsi,
            'status' => $a->status,
            'pokja' => ($a->relationLoaded('pokja') && $a->pokja)
                ? ['id' => $a->pokja->id, 'nama' => $a->pokja->nama]
                : null,
            'created_at' => $a->created_at?->toIso8601String(),
            'updated_at' => $a->updated_at?->toIso8601String(),
        ];
    }

    protected function mapAuditChecklist(AuditChecklist $c): array
    {
        $kategori = (int) $c->kategori;
        $daftarKategori = AuditChecklist::kategoriList();

        return [
            'id' => $c->id,
            'pokja_id' => $c->pokja_id,
            'paket_id' => $c->paket_id,
            'kategori' => $kategori,
            'kategori_label' => $daftarKategori[$kategori] ?? null,
            'poin' => $c->poin,
            'jawaban' => $c->jawaban,
            'catatan' => $c->catatan,
            'auditor_id' => $c->auditor_id,
            'pokja' => ($c->relationLoaded('pokja') && $c->pokja)
                ? ['id' => $c->pokja->id, 'nama' => $c->pokja->nama]
                : null,
            'paket' => ($c->relationLoaded('paket') && $c->paket)
                ? ['id' => $c->paket->id, 'kode_paket' => $c->paket->kode_paket, 'nama_paket' => $c->paket->nama_paket]
                : null,
            'created_at' => $c->created_at?->toIso8601String(),
            'updated_at' => $c->updated_at?->toIso8601String(),
        ];
    }

    protected function mapAuditLog(AuditLog $l): array
    {
        return [
            'id' => $l->id,
            'user_id' => $l->user_id,
            'aksi' => $l->aksi,
            'tabel' => $l->tabel,
            'record_id' => $l->record_id,
            'data_lama' => $l->data_lama,
            'data_baru' => $l->data_baru,
            'ip_address' => $l->ip_address,
            'user_agent' => $l->user_agent,
            'user' => ($l->relationLoaded('user') && $l->user)
                ? ['id' => $l->user->id, 'nama' => $l->user->name]
                : null,
            'created_at' => $l->created_at?->toIso8601String(),
        ];
    }
}