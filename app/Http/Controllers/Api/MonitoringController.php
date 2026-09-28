<?php

namespace App\Http\Controllers\Api;

use App\Models\AlertAnomali;
use App\Models\AuditChecklist;
use App\Models\AuditLog;
use App\Models\Evaluasi;
use App\Models\PerubahanJadwal;
use App\Models\PesanPaket;
use App\Models\ProgresPekerjaan;
use App\Models\Sanggahan;
use App\Models\Tahapan;
use Illuminate\Http\Request;

class MonitoringController extends ApiController
{
    // =====================================================
    // Tahapan
    // =====================================================
    public function tahapan(Request $request)
    {
        $data = Tahapan::query()
            ->when($request->filled('paket_id'), fn ($q) => $q->where('paket_id', $request->paket_id))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->orderBy('id', 'desc')
            ->paginate($this->limit($request))
            ->through(fn (Tahapan $t) => $this->mapTahapan($t));

        return $this->page($data);
    }

    // =====================================================
    // Evaluasi
    // =====================================================
    public function evaluasi(Request $request)
    {
        $data = Evaluasi::query()
            ->with('penyedia')
            ->when($request->filled('paket_id'), fn ($q) => $q->where('paket_id', $request->paket_id))
            ->when($request->filled('penyedia_id'), fn ($q) => $q->where('penyedia_id', $request->penyedia_id))
            ->when($request->filled('jenis'), fn ($q) => $q->where('jenis', $request->jenis))
            ->when($request->filled('hasil'), fn ($q) => $q->where('hasil', $request->hasil))
            ->orderByDesc('tanggal')->orderByDesc('id')
            ->paginate($this->limit($request))
            ->through(fn (Evaluasi $e) => $this->mapEvaluasi($e));

        return $this->page($data);
    }

    // =====================================================
    // Sanggahan
    // =====================================================
    public function sanggahan(Request $request)
    {
        $data = Sanggahan::query()
            ->with('penyedia')
            ->when($request->filled('paket_id'), fn ($q) => $q->where('paket_id', $request->paket_id))
            ->when($request->filled('penyedia_id'), fn ($q) => $q->where('penyedia_id', $request->penyedia_id))
            ->when($request->filled('hasil'), fn ($q) => $q->where('hasil', $request->hasil))
            ->when($request->has('substantif'), fn ($q) => $q->where('substantif', filter_var($request->substantif, FILTER_VALIDATE_BOOLEAN)))
            ->orderByDesc('tanggal_masuk')->orderByDesc('id')
            ->paginate($this->limit($request))
            ->through(fn (Sanggahan $s) => $this->mapSanggahan($s));

        return $this->page($data);
    }

    // =====================================================
    // Progres pekerjaan
    // =====================================================
    public function progres(Request $request)
    {
        $data = ProgresPekerjaan::query()
            ->with('user')
            ->when($request->filled('paket_id'), fn ($q) => $q->where('paket_id', $request->paket_id))
            ->when($request->filled('user_id'), fn ($q) => $q->where('user_id', $request->user_id))
            ->orderByDesc('created_at')->orderByDesc('id')
            ->paginate($this->limit($request))
            ->through(fn (ProgresPekerjaan $p) => $this->mapProgres($p));

        return $this->page($data);
    }

    // =====================================================
    // Perubahan jadwal
    // =====================================================
    public function perubahanJadwal(Request $request)
    {
        $data = PerubahanJadwal::query()
            ->when($request->filled('paket_id'), fn ($q) => $q->where('paket_id', $request->paket_id))
            ->when($request->filled('jenis'), fn ($q) => $q->where('jenis', $request->jenis))
            ->orderByDesc('tanggal')->orderByDesc('id')
            ->paginate($this->limit($request))
            ->through(fn (PerubahanJadwal $p) => $this->mapPerubahanJadwal($p));

        return $this->page($data);
    }

    // =====================================================
    // Pesan paket
    // =====================================================
    public function pesan(Request $request)
    {
        $data = PesanPaket::query()
            ->with('user')
            ->when($request->filled('paket_id'), fn ($q) => $q->where('paket_id', $request->paket_id))
            ->when($request->filled('user_id'), fn ($q) => $q->where('user_id', $request->user_id))
            ->orderByDesc('created_at')->orderByDesc('id')
            ->paginate($this->limit($request))
            ->through(fn (PesanPaket $p) => $this->mapPesan($p));

        return $this->page($data);
    }

    // =====================================================
    // Alert anomali
    // =====================================================
    public function alert(Request $request)
    {
        $data = AlertAnomali::query()
            ->with('pokja')
            ->when($request->filled('paket_id'), fn ($q) => $q->where('paket_id', $request->paket_id))
            ->when($request->filled('pokja_id'), fn ($q) => $q->where('pokja_id', $request->pokja_id))
            ->when($request->filled('tingkat'), fn ($q) => $q->where('tingkat', $request->tingkat))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->orderByDesc('created_at')->orderByDesc('id')
            ->paginate($this->limit($request))
            ->through(fn (AlertAnomali $a) => $this->mapAlert($a));

        return $this->page($data);
    }

    // =====================================================
    // Audit checklist
    // =====================================================
    public function auditChecklist(Request $request)
    {
        $data = AuditChecklist::query()
            ->with(['pokja', 'paket'])
            ->when($request->filled('paket_id'), fn ($q) => $q->where('paket_id', $request->paket_id))
            ->when($request->filled('pokja_id'), fn ($q) => $q->where('pokja_id', $request->pokja_id))
            ->when($request->filled('kategori'), fn ($q) => $q->where('kategori', $request->kategori))
            ->when($request->filled('jawaban'), fn ($q) => $q->where('jawaban', $request->jawaban))
            ->orderByDesc('created_at')->orderByDesc('id')
            ->paginate($this->limit($request))
            ->through(fn (AuditChecklist $c) => $this->mapAuditChecklist($c));

        return $this->page($data);
    }

    // =====================================================
    // Audit log (jejak audit)
    // =====================================================
    public function auditLog(Request $request)
    {
        $data = AuditLog::query()
            ->with('user')
            ->when($request->filled('tabel'), fn ($q) => $q->where('tabel', $request->tabel))
            ->when($request->filled('aksi'), fn ($q) => $q->where('aksi', $request->aksi))
            ->when($request->filled('user_id'), fn ($q) => $q->where('user_id', $request->user_id))
            ->when($request->filled('dari'), fn ($q) => $q->whereDate('created_at', '>=', $request->dari))
            ->when($request->filled('sampai'), fn ($q) => $q->whereDate('created_at', '<=', $request->sampai))
            ->orderByDesc('created_at')->orderByDesc('id')
            ->paginate($this->limit($request))
            ->through(fn (AuditLog $l) => $this->mapAuditLog($l));

        return $this->page($data);
    }
}