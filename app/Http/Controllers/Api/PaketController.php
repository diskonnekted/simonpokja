<?php

namespace App\Http\Controllers\Api;

use App\Models\AlertAnomali;
use App\Models\AuditChecklist;
use App\Models\PaketPengadaan;
use Illuminate\Http\Request;

class PaketController extends ApiController
{
    /**
     * GET /api/v1/paket — daftar paket pengadaan (dengan filter & pagination).
     */
    public function index(Request $request)
    {
        $q = PaketPengadaan::query()
            ->with(['opd', 'pokja', 'penyedia'])
            ->when($request->filled('tahun'), fn ($qq) => $qq->tahun((int) $request->tahun))
            ->when($request->filled('status'), fn ($qq) => $qq->status($request->status))
            ->when($request->filled('jenis'), fn ($qq) => $qq->where('jenis', $request->jenis))
            ->when($request->filled('metode'), fn ($qq) => $qq->where('metode', $request->metode))
            ->when($request->filled('sumber_dana'), fn ($qq) => $qq->where('sumber_dana', $request->sumber_dana))
            ->when($request->filled('risiko'), fn ($qq) => $qq->where('risiko', $request->risiko))
            ->when($request->filled('opd_id'), fn ($qq) => $qq->where('opd_id', $request->opd_id))
            ->when($request->filled('pokja_id'), fn ($qq) => $qq->where('pokja_id', $request->pokja_id))
            ->when($request->filled('penyedia_id'), fn ($qq) => $qq->where('penyedia_id', $request->penyedia_id))
            ->when($request->filled('tender_gagal'), fn ($qq) => $qq->where('tender_gagal', filter_var($request->tender_gagal, FILTER_VALIDATE_BOOLEAN)))
            ->when($request->filled('q'), fn ($qq) => $qq->where(function ($w) use ($request) {
                $w->where('nama_paket', 'like', "%{$request->q}%")
                    ->orWhere('kode_paket', 'like', "%{$request->q}%");
            }))
            ->when($request->filled('dari'), fn ($qq) => $qq->whereDate('tanggal_mulai', '>=', $request->dari))
            ->when($request->filled('sampai'), fn ($qq) => $qq->whereDate('tanggal_mulai', '<=', $request->sampai));

        $sortable = ['kode_paket', 'nama_paket', 'tahun_anggaran', 'pagu', 'hps', 'nilai_kontrak', 'tanggal_mulai', 'tanggal_selesai', 'created_at', 'updated_at'];
        $sort = in_array($request->query('sort'), $sortable, true) ? $request->query('sort') : 'tahun_anggaran';
        $dir = strtolower($request->query('order', 'desc')) === 'asc' ? 'asc' : 'desc';
        $q->orderBy($sort, $dir)->orderBy('id', 'desc');

        return $this->page(
            $q->paginate($this->limit($request))->through(fn (PaketPengadaan $p) => $this->mapPaket($p))
        );
    }

    /**
     * GET /api/v1/paket/{id} — detail lengkap paket beserta seluruh data turunan.
     */
    public function show($id)
    {
        $p = PaketPengadaan::with(['opd', 'pokja', 'penyedia'])->find($id);

        if (! $p) {
            return $this->notFound('Paket pengadaan tidak ditemukan.');
        }

        $tahapans = $p->tahapans()->orderBy('urutan')->get();
        $evaluasis = $p->evaluasis()->with('penyedia')->orderByDesc('tanggal')->get();
        $sanggahans = $p->sanggahans()->with('penyedia')->orderByDesc('tanggal_masuk')->get();
        $progres = $p->progresPekerjaans()->with('user')->orderByDesc('created_at')->get();
        $jadwal = $p->perubahanJadwals()->orderByDesc('tanggal')->get();
        $pesan = $p->pesans()->with('user')->orderByDesc('created_at')->get();
        $alerts = AlertAnomali::with('pokja')->where('paket_id', $p->id)->orderByDesc('created_at')->get();
        $checklists = AuditChecklist::with('pokja')->where('paket_id', $p->id)->orderBy('kategori')->orderBy('id')->get();

        return $this->ok([
            'paket' => $this->mapPaket($p),
            'tahapan' => $tahapans->map(fn ($t) => $this->mapTahapan($t)),
            'evaluasi' => $evaluasis->map(fn ($e) => $this->mapEvaluasi($e)),
            'sanggahan' => $sanggahans->map(fn ($s) => $this->mapSanggahan($s)),
            'progres_pekerjaan' => $progres->map(fn ($pr) => $this->mapProgres($pr)),
            'perubahan_jadwal' => $jadwal->map(fn ($j) => $this->mapPerubahanJadwal($j)),
            'pesan' => $pesan->map(fn ($msg) => $this->mapPesan($msg)),
            'alert_anomali' => $alerts->map(fn ($a) => $this->mapAlert($a)),
            'audit_checklist' => $checklists->map(fn ($c) => $this->mapAuditChecklist($c)),
            'ringkasan' => [
                'tahapan' => $tahapans->count(),
                'evaluasi' => $evaluasis->count(),
                'sanggahan' => $sanggahans->count(),
                'progres_pekerjaan' => $progres->count(),
                'perubahan_jadwal' => $jadwal->count(),
                'pesan' => $pesan->count(),
                'alert_anomali' => $alerts->count(),
                'audit_checklist' => $checklists->count(),
            ],
        ]);
    }

    // =====================================================
    // Endpoint turunan per paket (kemudahan konsumen)
    // =====================================================
    public function tahapan($id)
    {
        $p = PaketPengadaan::find($id);
        if (! $p) {
            return $this->notFound('Paket pengadaan tidak ditemukan.');
        }

        return $this->ok($p->tahapans()->orderBy('urutan')->get()->map(fn ($t) => $this->mapTahapan($t)));
    }

    public function evaluasi($id)
    {
        $p = PaketPengadaan::find($id);
        if (! $p) {
            return $this->notFound('Paket pengadaan tidak ditemukan.');
        }

        return $this->ok($p->evaluasis()->with('penyedia')->orderByDesc('tanggal')->get()->map(fn ($e) => $this->mapEvaluasi($e)));
    }

    public function sanggahan($id)
    {
        $p = PaketPengadaan::find($id);
        if (! $p) {
            return $this->notFound('Paket pengadaan tidak ditemukan.');
        }

        return $this->ok($p->sanggahans()->with('penyedia')->orderByDesc('tanggal_masuk')->get()->map(fn ($s) => $this->mapSanggahan($s)));
    }

    public function progres($id)
    {
        $p = PaketPengadaan::find($id);
        if (! $p) {
            return $this->notFound('Paket pengadaan tidak ditemukan.');
        }

        return $this->ok($p->progresPekerjaans()->with('user')->orderByDesc('created_at')->get()->map(fn ($pr) => $this->mapProgres($pr)));
    }

    public function perubahanJadwal($id)
    {
        $p = PaketPengadaan::find($id);
        if (! $p) {
            return $this->notFound('Paket pengadaan tidak ditemukan.');
        }

        return $this->ok($p->perubahanJadwals()->orderByDesc('tanggal')->get()->map(fn ($j) => $this->mapPerubahanJadwal($j)));
    }

    public function pesan($id)
    {
        $p = PaketPengadaan::find($id);
        if (! $p) {
            return $this->notFound('Paket pengadaan tidak ditemukan.');
        }

        return $this->ok($p->pesans()->with('user')->orderByDesc('created_at')->get()->map(fn ($msg) => $this->mapPesan($msg)));
    }

    public function alert($id)
    {
        $p = PaketPengadaan::find($id);
        if (! $p) {
            return $this->notFound('Paket pengadaan tidak ditemukan.');
        }

        return $this->ok(AlertAnomali::with('pokja')->where('paket_id', $p->id)->orderByDesc('created_at')->get()->map(fn ($a) => $this->mapAlert($a)));
    }
}