<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PaketPengadaan;
use App\Models\PerubahanJadwal;
use App\Models\Sanggahan;

class PemantauanController extends Controller
{
    // ===== DAFTAR PAKET BERMASALAH / PERLU PERHATIAN =====
    public function index(Request $request)
    {
        $q = $request->get('q');
        $filter = $request->get('filter', 'semua');

        $query = PaketPengadaan::with(['pokja', 'opd', 'penyedia'])
            ->whereNotIn('status', ['draft']);

        // Hanya paket tahun berjalan
        $query->where('tahun_anggaran', $request->get('tahun', date('Y')));

        if ($q) {
            $query->where(function ($sub) use ($q) {
                $sub->where('nama_paket', 'like', "%{$q}%")
                    ->orWhere('kode_paket', 'like', "%{$q}%");
            });
        }

        $paket = $query->get()->map(function ($p) {
            // Hitung indikator masalah per paket
            $p->jumlah_perubahan = $p->perubahanJadwals()->count();
            $p->tanpa_ba = $p->perubahanJadwals()->where('ada_berita_acara', false)->count();
            $p->akhir_jam_kerja = $p->perubahanJadwals()
                ->whereTime('jam', '>=', '16:30')->count();
            $p->sanggah_menunggu = $p->sanggahans()->where('hasil', 'menunggu')->count();

            // Skor masalah: kombinasi indikator
            $p->skor_masalah = ($p->jumlah_perubahan >= 4 ? 3 : ($p->jumlah_perubahan >= 2 ? 1 : 0))
                + ($p->tanpa_ba > 0 ? 2 : 0)
                + ($p->akhir_jam_kerja > 0 ? 2 : 0)
                + ($p->sanggah_menunggu > 0 ? 2 : 0)
                + ($p->tender_gagal ? 2 : 0)
                + ($p->risiko == 'kritis' ? 1 : 0);

            return $p;
        })
        ->filter(function ($p) use ($filter) {
            return match ($filter) {
                'delay' => $p->jumlah_perubahan >= 3,
                'tanpa_ba' => $p->tanpa_ba > 0,
                'sanggah' => $p->sanggah_menunggu > 0,
                'gagal' => $p->tender_gagal,
                'kritis' => $p->risiko == 'kritis',
                default => $p->skor_masalah > 0,
            };
        })
        ->sortByDesc('skor_masalah')
        ->values();

        return view('pemantauan.index', compact('paket', 'filter'));
    }

    // ===== DETAIL PEMANTAUAN PAKET: JADWAL & SANGGAHAN =====
    public function show(PaketPengadaan $paket)
    {
        $paket->load(['pokja', 'opd', 'penyedia', 'perubahanJadwals', 'sanggahans.penyedia', 'progresPekerjaans.user']);

        $perubahans = $paket->perubahanJadwals()->orderByDesc('tanggal')->get();
        $sanggahans = $paket->sanggahans()->orderByDesc('tanggal_masuk')->get();
        $progresPekerjaans = $paket->progresPekerjaans()->orderByDesc('created_at')->get();

        // Bangun kronologi terpadu (unified timeline)
        $timeline = collect();

        // 1. Event Pembuatan Paket
        if ($paket->created_at) {
            $timeline->push([
                'tipe' => 'paket_dibuat',
                'tipe_label' => 'Paket Terdaftar',
                'ikon' => 'bi-file-earmark-plus',
                'marker' => 'marker-info',
                'tanggal' => $paket->created_at,
                'judul' => 'Paket Pengadaan Didaftarkan',
                'deskripsi' => "Paket didaftarkan oleh " . ($paket->opd?->nama ?? 'Perangkat Daerah') . " dengan pagu " . format_rupiah($paket->pagu),
                'badge' => 'primary',
            ]);
        }

        // 2. Event Perubahan Jadwal
        foreach ($perubahans as $pj) {
            $isDanger = !$pj->ada_berita_acara || ($pj->jam && substr($pj->jam, 0, 5) >= '16:00');
            $tgl = $pj->tanggal ? \Carbon\Carbon::parse($pj->tanggal->format('Y-m-d') . ' ' . ($pj->jam ?? '00:00:00')) : $pj->created_at;
            $timeline->push([
                'tipe' => 'perubahan_jadwal',
                'tipe_label' => 'Perubahan Jadwal',
                'ikon' => 'bi-calendar-event',
                'marker' => $isDanger ? 'marker-danger' : 'marker-warning',
                'tanggal' => $tgl,
                'judul' => ucfirst($pj->jenis) . ($pj->tahap_terkait ? " ({$pj->tahap_terkait})" : ''),
                'deskripsi' => ($pj->alasan ?: 'Penyesuaian jadwal pengadaan') . ($pj->ada_berita_acara ? ' &bull; Dilengkapi BA' : ' &bull; <strong class="text-danger">Tanpa Berita Acara</strong>'),
                'badge' => $isDanger ? 'danger' : 'warning',
            ]);
        }

        // 3. Event Sanggahan
        foreach ($sanggahans as $sg) {
            $isKritis = $sg->status === 'menunggu';
            $timeline->push([
                'tipe' => 'sanggahan',
                'tipe_label' => 'Sanggahan',
                'ikon' => 'bi-flag-fill',
                'marker' => $isKritis ? 'marker-danger' : 'marker-info',
                'tanggal' => $sg->tanggal_masuk ?? $sg->created_at,
                'judul' => "Sanggahan: " . ($sg->penyedia?->nama ?: 'Penyedia'),
                'deskripsi' => ($sg->materi_singkat ?: 'Sanggahan pengadaan') . " &bull; Status: " . ucfirst($sg->status),
                'badge' => $isKritis ? 'danger' : 'success',
            ]);
        }

        // 4. Event Progres Pekerjaan
        foreach ($progresPekerjaans as $prog) {
            $isSelesai = $prog->status === 'selesai' || $prog->progress >= 100;
            $timeline->push([
                'tipe' => 'progres',
                'tipe_label' => 'Update Progres',
                'ikon' => $isSelesai ? 'bi-check-circle-fill' : 'bi-arrow-repeat',
                'marker' => $isSelesai ? 'marker-success' : 'marker-info',
                'tanggal' => $prog->created_at,
                'judul' => "Progres {$prog->progress}% — Status: " . ucfirst($prog->status),
                'deskripsi' => ($prog->catatan ?: 'Pembaruan progres berkala') . ($prog->user ? " (oleh: {$prog->user->name})" : ''),
                'badge' => $isSelesai ? 'success' : 'primary',
            ]);
        }

        $timeline = $timeline->sortByDesc('tanggal')->values();

        return view('pemantauan.show', compact('paket', 'perubahans', 'sanggahans', 'timeline'));
    }

    // ===== CATAT PERUBAHAN JADWAL =====
    public function storePerubahan(Request $request, PaketPengadaan $paket)
    {
        $validated = $request->validate([
            'tanggal' => 'required|date',
            'jam' => 'nullable|date_format:H:i',
            'jenis' => 'required|in:pengunduran,reopening,penyesuaian',
            'tahap_terkait' => 'nullable|max:100',
            'alasan' => 'nullable|max:500',
            'ada_berita_acara' => 'boolean',
        ]);

        $validated['paket_id'] = $paket->id;
        $validated['ada_berita_acara'] = $request->boolean('ada_berita_acara');

        PerubahanJadwal::create($validated);

        // Auto-alert jika pola bermasalah: >3x atau tanpa BA
        $jumlah = $paket->perubahanJadwals()->count();
        if ($jumlah >= 4) {
            AlertAnomali::firstOrCreate(
                ['paket_id' => $paket->id, 'jenis' => 'delay_berulang', 'status' => 'aktif'],
                [
                    'pokja_id' => $paket->pokja_id,
                    'tingkat' => 'critical',
                    'deskripsi' => "{$paket->kode_paket}: Pengunduran/perubahan jadwal {$jumlah}x — melebihi ambang SLA.",
                ]
            );

            // Notifikasi kritis EWS
            try {
                $ns = app(\App\Services\NotificationService::class);
                $msg = "{$paket->kode_paket}: Pengunduran jadwal {$jumlah}x — melebihi ambang SLA.";
                if ($paket->pokja_id) {
                    $ns->kirimKePokja($paket->pokja_id, 'Peringatan Kritis EWS', $msg, route('pemantauan.show', $paket), 'anomali', 'critical');
                }
                $ns->kirimKeAdmin('Peringatan Kritis EWS', $msg, route('pemantauan.show', $paket), 'anomali', 'critical');
            } catch (\Throwable $e) {}
        } elseif (!$validated['ada_berita_acara']) {
            AlertAnomali::firstOrCreate(
                ['paket_id' => $paket->id, 'jenis' => 'tanpa_ba', 'status' => 'aktif'],
                [
                    'pokja_id' => $paket->pokja_id,
                    'tingkat' => 'warning',
                    'deskripsi' => "{$paket->kode_paket}: Perubahan jadwal tanpa Berita Acara resmi.",
                ]
            );

            try {
                $ns = app(\App\Services\NotificationService::class);
                $msg = "{$paket->kode_paket}: Perubahan jadwal dilakukan tanpa Berita Acara resmi.";
                if ($paket->pokja_id) {
                    $ns->kirimKePokja($paket->pokja_id, 'Peringatan EWS (Tanpa BA)', $msg, route('pemantauan.show', $paket), 'anomali', 'warning');
                }
            } catch (\Throwable $e) {}
        }

        return back()->with('success', 'Perubahan jadwal tercatat.');
    }

    // ===== CATAT SANGGAHAN =====
    public function storeSanggahan(Request $request, PaketPengadaan $paket)
    {
        $validated = $request->validate([
            'penyedia_id' => 'required|exists:penyedias,id',
            'tanggal_masuk' => 'required|date',
            'hasil' => 'required|in:menunggu,diterima,ditolak',
            'catatan' => 'nullable|max:500',
        ]);

        $validated['paket_id'] = $paket->id;
        $validated['substantif'] = $request->hasil != 'menunggu';

        Sanggahan::create($validated);

        // Notifikasi ke Pokja penangan tentang sanggahan rekanan
        try {
            if ($paket->pokja_id) {
                app(\App\Services\NotificationService::class)->kirimKePokja(
                    $paket->pokja_id,
                    'Sanggahan Rekanan Masuk',
                    "{$paket->kode_paket}: Sanggahan baru diterima. Batas waktu SLA tanggapan 3 hari kerja.",
                    route('pemantauan.show', $paket),
                    'sanggahan',
                    'warning'
                );
            }
        } catch (\Throwable $e) {}

        // Auto-alert jika tidak dijawab tepat waktu (cek saat hasil masih menunggu >3 hari)
        if ($validated['hasil'] == 'menunggu' && now()->diffInDays($validated['tanggal_masuk']) > 3) {
            AlertAnomali::firstOrCreate(
                ['paket_id' => $paket->id, 'jenis' => 'sanggah_tidak_tepat_waktu', 'status' => 'aktif'],
                [
                    'pokja_id' => $paket->pokja_id,
                    'tingkat' => 'warning',
                    'deskripsi' => "{$paket->kode_paket}: Sanggahan belum dijawab lebih dari 3 hari kerja.",
                ]
            );
        }

        return back()->with('success', 'Sanggahan tercatat.');
    }

    // ===== JAWAB SANGGAHAN =====
    public function jawabSanggahan(Request $request, Sanggahan $sanggahan)
    {
        $validated = $request->validate([
            'hasil' => 'required|in:diterima,ditolak',
            'substantif' => 'boolean',
            'catatan' => 'nullable|max:500',
        ]);

        $validated['tanggal_dijawab'] = now();
        $validated['substantif'] = $request->boolean('substantif');

        $sanggahan->update($validated);

        // Resolve alert terkait jika sudah dijawab
        AlertAnomali::where('paket_id', $sanggahan->paket_id)
            ->where('jenis', 'sanggah_tidak_tepat_waktu')
            ->where('status', 'aktif')
            ->update(['status' => 'selesai']);

        return back()->with('success', 'Tanggapan sanggahan tersimpan.');
    }
}
