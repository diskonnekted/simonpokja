<?php

namespace App\Http\Controllers;

use App\Models\PaketPengadaan;
use App\Models\PesanPaket;
use App\Models\ProgresPekerjaan;
use App\Models\Pokja;
use App\Models\Sanggahan;
use App\Models\PerubahanJadwal;
use App\Models\Tahapan;
use App\Models\AlertAnomali;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * Dasbor untuk user dengan peran Pokja: mengelola laporan progres
 * pekerjaan milik Pokja-nya sendiri. Admin dapat meninjau semua Pokja
 * (read-only).
 */
class PokjaDashboardController extends Controller
{
    /**
     * Dasbor pokja: ?pokja=x untuk admin melihat Pokja tertentu.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        // Admin boleh pilih Pokja apa pun; user pokja terkunci ke Pokja-nya
        if ($user->isAdmin()) {
            $pokjaId = (int) $request->query('pokja', 0) ?: \App\Models\Pokja::orderBy('id')->value('id');
            $bolehSimpan = false;
        } else {
            $pokjaId = $user->pokja_id;
            $bolehSimpan = true;
        }

        $pokja = \App\Models\Pokja::findOrFail($pokjaId);
        $listPokja = \App\Models\Pokja::orderBy('id')->get(['id', 'nama']);

        // Tahun anggaran yang tersedia di sistem
        $tahuns = PaketPengadaan::select('tahun_anggaran')
            ->distinct()
            ->orderByDesc('tahun_anggaran')
            ->pluck('tahun_anggaran')
            ->toArray();

        if (empty($tahuns)) {
            $tahuns = [(int) date('Y')];
        }

        // Resolusi tahun anggaran: parameter request > cek paket tahun berjalan > fallback tahun terbaru milik Pokja
        if ($request->filled('tahun')) {
            $tahun = (int) $request->query('tahun');
        } else {
            $adaTahunIni = PaketPengadaan::where('pokja_id', $pokja->id)
                ->where('tahun_anggaran', (int) date('Y'))
                ->exists();

            if ($adaTahunIni) {
                $tahun = (int) date('Y');
            } else {
                $tahunTerbaru = PaketPengadaan::where('pokja_id', $pokja->id)
                    ->orderByDesc('tahun_anggaran')
                    ->value('tahun_anggaran');

                $tahun = $tahunTerbaru ? (int) $tahunTerbaru : (int) date('Y');
            }
        }

        $paketsQuery = PaketPengadaan::with(['opd:id,singkatan', 'penyedia:id,nama', 'tahapans', 'progresTerakhir', 'sanggahans'])
            ->where('pokja_id', $pokja->id);

        if ($tahun > 0) {
            $paketsQuery->where('tahun_anggaran', $tahun);
        }

        $pakets = $paketsQuery->get()
            // Urut: lewat deadline -> mendesak -> mendekati -> aman -> tanggal terdekat
            ->sort(function ($a, $b) {
                $urut = ['lewat' => 0, 'mendesak' => 1, 'mendekati' => 2, 'normal' => 3];
                $ka = $urut[$a->status_deadline[0]] <=> $urut[$b->status_deadline[0]];
                if ($ka !== 0) {
                    return $ka;
                }

                return $a->tanggal_selesai?->valueOf() <=> $b->tanggal_selesai?->valueOf();
            })
            ->values();

        $hitung = $pakets->countBy(fn ($p) => $p->status_deadline[0]);

        // Statistik pesan per paket: total & yang belum dibaca user saat ini
        $statPesan = PesanPaket::whereIn('paket_id', $pakets->pluck('id'))
            ->selectRaw('paket_id, COUNT(*) as total, SUM(CASE WHEN dibaca_pada IS NULL AND user_id != ? THEN 1 ELSE 0 END) as baru', [$user->id])
            ->groupBy('paket_id')
            ->get()
            ->keyBy('paket_id');

        // R2: Kalkulasi item yang membutuhkan tindakan segera (action-needed items)
        $perluTindakan = collect();
        foreach ($pakets as $p) {
            $pesanBaru = (int) ($statPesan[$p->id]->baru ?? 0);
            $sanggahanMenunggu = $p->sanggahans->where('status', 'menunggu')->count();
            $statusDl = $p->status_deadline[0];

            if ($statusDl === 'lewat') {
                $perluTindakan->push([
                    'paket' => $p,
                    'tipe' => 'deadline_lewat',
                    'prioritas' => 'critical',
                    'badge_class' => 'danger',
                    'ikon' => 'bi-alarm-fill',
                    'judul' => 'Melampaui Deadline',
                    'pesan' => "Pekerjaan telah melampaui batas waktu penyelesaian (" . abs($p->status_deadline[3]) . " hari).",
                    'aksi' => 'Update Progres',
                    'modal' => 'progres',
                ]);
            } elseif ($statusDl === 'mendesak') {
                $perluTindakan->push([
                    'paket' => $p,
                    'tipe' => 'deadline_mendesak',
                    'prioritas' => 'high',
                    'badge_class' => 'warning',
                    'ikon' => 'bi-hourglass-bottom',
                    'judul' => 'Deadline Mendesak',
                    'pesan' => "Sisa waktu penyelesaian {$p->status_deadline[3]} hari lagi.",
                    'aksi' => 'Update Progres',
                    'modal' => 'progres',
                ]);
            }

            if ($sanggahanMenunggu > 0) {
                $perluTindakan->push([
                    'paket' => $p,
                    'tipe' => 'sanggahan_menunggu',
                    'prioritas' => 'high',
                    'badge_class' => 'danger',
                    'ikon' => 'bi-flag-fill',
                    'judul' => "Sanggahan Aktif ({$sanggahanMenunggu})",
                    'pesan' => "Ada sanggahan penyedia yang menunggu jawaban resmi.",
                    'aksi' => 'Tinjau Sanggahan',
                    'url' => route('pemantauan.show', $p),
                ]);
            }

            if ($pesanBaru > 0) {
                $perluTindakan->push([
                    'paket' => $p,
                    'tipe' => 'pesan_belum_dibaca',
                    'prioritas' => 'medium',
                    'badge_class' => 'info',
                    'ikon' => 'bi-chat-dots-fill',
                    'judul' => "{$pesanBaru} Pesan Masuk",
                    'pesan' => "Koordinasi belum dibaca dari " . ($user->isAdmin() ? 'Pokja' : 'Kepala LPSE') . ".",
                    'aksi' => 'Buka Diskusi',
                    'modal' => 'pesan',
                ]);
            }
        }

        // Rincian Skor Risiko Pokja untuk Modal Interaktif
        $rasioBeban = $pokja->rasio_beban;
        $kapasitasIdeal = max($pokja->kapasitas_ideal, 1);
        $poinBeban = min(35, (int) (($rasioBeban / $kapasitasIdeal) * 25));

        $paketDelayQuery = \App\Models\PerubahanJadwal::select('paket_id')
            ->selectRaw('COUNT(*) as jumlah')
            ->whereIn('paket_id', $pokja->pakets()->pluck('id'))
            ->groupBy('paket_id')
            ->havingRaw('COUNT(*) > 3')
            ->get();
        $paketDelayCount = $paketDelayQuery->count();
        $poinDelay = min(25, $paketDelayCount * 8);

        $alertCriticalCount = $pokja->alerts()->where('status', 'aktif')->where('tingkat', 'critical')->count();
        $poinAlert = min(25, $alertCriticalCount * 12);

        $poinGagal = min(15, (int) ($pokja->tender_gagal_persen / 2));

        $rincianRisiko = [
            'total' => $pokja->skor_risiko,
            'label' => $pokja->label_risiko,
            'warna' => $pokja->warna_risiko,
            'komponen' => [
                'beban' => [
                    'nama' => 'Beban Kerja Berlebih',
                    'poin' => $poinBeban,
                    'max' => 35,
                    'deskripsi' => "Rasio beban ({$rasioBeban} paket) vs kapasitas ideal ({$kapasitasIdeal} paket)",
                ],
                'delay' => [
                    'nama' => 'Keterlambatan/Delay Jadwal',
                    'poin' => $poinDelay,
                    'max' => 25,
                    'deskripsi' => "{$paketDelayCount} paket mengalami perubahan jadwal > 3 kali",
                ],
                'alert' => [
                    'nama' => 'Peringatan/Alert Kritis',
                    'poin' => $poinAlert,
                    'max' => 25,
                    'deskripsi' => "{$alertCriticalCount} peringatan kritis kepatuhan belum ditutup",
                ],
                'gagal' => [
                    'nama' => 'Tingkat Tender Gagal',
                    'poin' => $poinGagal,
                    'max' => 15,
                    'deskripsi' => "Persentase lelang gagal: {$pokja->tender_gagal_persen}%",
                ],
            ],
            'paket_delay' => PaketPengadaan::whereIn('id', $paketDelayQuery->pluck('paket_id'))->get(['id', 'kode_paket', 'nama_paket']),
            'alert_kritis' => $pokja->alerts()->where('status', 'aktif')->where('tingkat', 'critical')->get(['id', 'deskripsi', 'tingkat', 'created_at']),
        ];

        // Hitung paket per tahapan untuk filter chips
        $perluTindakanPaketIds = $perluTindakan->pluck('paket.id')->unique()->values();
        $countPerStatus = [
            'semua' => $pakets->count(),
            'perlu_aksi' => $perluTindakanPaketIds->count(),
            'persiapan' => $pakets->where('status', 'persiapan')->count(),
            'pemilihan' => $pakets->where('status', 'pemilihan')->count(),
            'kontrak' => $pakets->where('status', 'kontrak')->count(),
            'pelaksanaan' => $pakets->where('status', 'pelaksanaan')->count(),
            'selesai' => $pakets->where('status', 'selesai')->count(),
            'draft' => $pakets->where('status', 'draft')->count(),
        ];

        return view('pokja.dasbor', [
            'user' => $user,
            'pokja' => $pokja,
            'listPokja' => $listPokja,
            'pakets' => $pakets,
            'bolehSimpan' => $bolehSimpan,
            'statPesan' => $statPesan,
            'perluTindakan' => $perluTindakan,
            'perluTindakanPaketIds' => $perluTindakanPaketIds,
            'rincianRisiko' => $rincianRisiko,
            'countPerStatus' => $countPerStatus,
            'tahun' => $tahun,
            'tahuns' => $tahuns,
            'nLewat' => $hitung['lewat'] ?? 0,
            'nMendesak' => $hitung['mendesak'] ?? 0,
            'nMendekati' => $hitung['mendekati'] ?? 0,
        ]);
    }

    /**
     * Simpan progres pekerjaan (riwayat + sinkron ke paket).
     */
    public function simpanProgres(Request $request, PaketPengadaan $paket)
    {
        $user = $request->user();

        // Policy: user pokja hanya boleh paket milik pokja-nya; admin hanya tinjau
        if ($user->isAdmin()) {
            abort(403, 'Admin berperan sebagai pengamat; penyimpanan progres dilakukan oleh akun Pokja.');
        }
        if ($user->pokja_id !== $paket->pokja_id) {
            abort(403, 'Paket ini bukan milik Pokja Anda.');
        }

        $data = $request->validate([
            'progress' => ['required', 'integer', 'min:0', 'max:100'],
            'status' => ['required', 'in:draft,persiapan,pemilihan,kontrak,pelaksanaan,selesai,batal'],
            'catatan' => ['nullable', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($data, $user, $paket) {
            // Riwayat
            ProgresPekerjaan::create([
                'paket_id' => $paket->id,
                'user_id' => $user->id,
                'progress' => $data['progress'],
                'status' => $data['status'],
                'catatan' => $data['catatan'] ?? null,
            ]);
            // Sinkron ke paket agar dashboard admin & peta konsisten
            $paket->update([
                'progress' => $data['progress'],
                'status' => $data['status'],
            ]);
        });

        // Notifikasi ke Admin LPSE
        try {
            app(\App\Services\NotificationService::class)->kirimKeAdmin(
                'Laporan Progres Baru',
                "{$paket->kode_paket}: {$user->name} memperbarui progres fisik menjadi {$data['progress']}% ({$data['status']}).",
                route('pemantauan.show', $paket),
                'progres',
                'info'
            );
        } catch (\Throwable $e) {}

        return back()->with('success', 'Progres "' . $paket->nama_paket . '" tersimpan (' . $data['progress'] . '%, ' . $data['status'] . ').');
    }

    /**
     * Riwayat progres satu paket.
     */
    public function riwayat(Request $request, PaketPengadaan $paket)
    {
        $user = $request->user();
        if (! $user->isAdmin() && $user->pokja_id !== $paket->pokja_id) {
            abort(403, 'Paket ini bukan milik Pokja Anda.');
        }

        $riwayat = $paket->progresPekerjaans()->with('user:id,name')->get();

        return response()->json([
            'paket' => [
                'kode' => $paket->kode_paket,
                'nama' => $paket->nama_paket,
                'progress' => $paket->progress,
            ],
            'riwayat' => $riwayat->map(fn ($r) => [
                'waktu' => $r->created_at->translatedFormat('d M Y H:i'),
                'user' => $r->user?->name,
                'progress' => $r->progress,
                'status' => $r->status,
                'catatan' => $r->catatan,
            ]),
        ]);
    }

    /**
     * Resolusi objek Pokja, listPokja, dan tahun terpilih.
     */
    private function resolvePokjaAndTahun(Request $request): array
    {
        $user = $request->user();

        if ($user->isAdmin()) {
            $pokjaId = (int) $request->query('pokja', 0) ?: Pokja::orderBy('id')->value('id');
            $bolehSimpan = false;
        } else {
            $pokjaId = $user->pokja_id;
            $bolehSimpan = true;
        }

        $pokja = Pokja::findOrFail($pokjaId);
        $listPokja = Pokja::orderBy('id')->get(['id', 'nama']);

        $tahuns = PaketPengadaan::select('tahun_anggaran')
            ->distinct()
            ->orderByDesc('tahun_anggaran')
            ->pluck('tahun_anggaran')
            ->toArray();

        if (empty($tahuns)) {
            $tahuns = [(int) date('Y')];
        }

        if ($request->filled('tahun')) {
            $tahun = (int) $request->query('tahun');
        } else {
            $adaTahunIni = PaketPengadaan::where('pokja_id', $pokja->id)
                ->where('tahun_anggaran', (int) date('Y'))
                ->exists();

            if ($adaTahunIni) {
                $tahun = (int) date('Y');
            } else {
                $tahunTerbaru = PaketPengadaan::where('pokja_id', $pokja->id)
                    ->orderByDesc('tahun_anggaran')
                    ->value('tahun_anggaran');

                $tahun = $tahunTerbaru ? (int) $tahunTerbaru : (int) date('Y');
            }
        }

        return [$user, $pokja, $listPokja, $tahun, $tahuns, $bolehSimpan];
    }

    /**
     * Halaman Sanggahan & SLA Pokja.
     */
    public function sanggahan(Request $request)
    {
        [$user, $pokja, $listPokja, $tahun, $tahuns, $bolehSimpan] = $this->resolvePokjaAndTahun($request);

        $paketsQuery = PaketPengadaan::where('pokja_id', $pokja->id);
        if ($tahun > 0) {
            $paketsQuery->where('tahun_anggaran', $tahun);
        }
        $paketIds = $paketsQuery->pluck('id');

        $sanggahans = Sanggahan::with(['paket.opd:id,singkatan', 'penyedia:id,nama,email,telepon'])
            ->whereIn('paket_id', $paketIds)
            ->get()
            ->transform(function ($sg) {
                $tglMasuk = $sg->tanggal_masuk ?? $sg->created_at;
                if ($sg->hasil === 'menunggu') {
                    $hariLewat = (int) now()->diffInDays($tglMasuk);
                    $sisaHari = 3 - $hariLewat;
                    if ($sisaHari < 0) {
                        $sg->sla_status = 'terlewat';
                        $sg->sla_label = 'Lewat SLA (' . abs($sisaHari) . ' hari)';
                        $sg->sla_badge = 'danger';
                    } elseif ($sisaHari === 0) {
                        $sg->sla_status = 'mendesak';
                        $sg->sla_label = 'Hari Terakhir SLA';
                        $sg->sla_badge = 'warning';
                    } else {
                        $sg->sla_status = 'berjalan';
                        $sg->sla_label = 'Sisa ' . $sisaHari . ' hari kerja';
                        $sg->sla_badge = 'info';
                    }
                } else {
                    $sg->sla_status = 'selesai';
                    $sg->sla_label = 'Selesai (' . ucfirst($sg->hasil) . ')';
                    $sg->sla_badge = $sg->hasil === 'diterima' ? 'success' : 'secondary';
                }
                return $sg;
            })
            ->sort(function ($a, $b) {
                $urut = ['terlewat' => 0, 'mendesak' => 1, 'berjalan' => 2, 'selesai' => 3];
                $ka = ($urut[$a->sla_status] ?? 4) <=> ($urut[$b->sla_status] ?? 4);
                if ($ka !== 0) {
                    return $ka;
                }
                return $b->tanggal_masuk?->valueOf() <=> $a->tanggal_masuk?->valueOf();
            })
            ->values();

        $kpi = [
            'total' => $sanggahans->count(),
            'menunggu' => $sanggahans->where('hasil', 'menunggu')->count(),
            'kritis' => $sanggahans->whereIn('sla_status', ['terlewat', 'mendesak'])->count(),
            'diterima' => $sanggahans->where('hasil', 'diterima')->count(),
            'ditolak' => $sanggahans->where('hasil', 'ditolak')->count(),
        ];

        return view('pokja.sanggahan', compact(
            'user', 'pokja', 'listPokja', 'tahun', 'tahuns', 'bolehSimpan', 'sanggahans', 'kpi'
        ));
    }

    /**
     * Beri Tanggapan / Jawaban Sanggahan Resmi.
     */
    public function jawabSanggahan(Request $request, Sanggahan $sanggahan)
    {
        $user = $request->user();
        $paket = $sanggahan->paket;

        if (! $user->isAdmin() && $user->pokja_id !== $paket->pokja_id) {
            abort(403, 'Anda tidak memiliki hak untuk menjawab sanggahan pada paket ini.');
        }

        $validated = $request->validate([
            'hasil' => ['required', 'in:diterima,ditolak'],
            'substantif' => ['nullable', 'boolean'],
            'catatan' => ['required', 'string', 'max:1000'],
        ]);

        $sanggahan->update([
            'hasil' => $validated['hasil'],
            'substantif' => $request->boolean('substantif'),
            'catatan' => $validated['catatan'],
            'tanggal_dijawab' => now(),
        ]);

        AlertAnomali::where('paket_id', $paket->id)
            ->where('jenis', 'sanggah_tidak_tepat_waktu')
            ->where('status', 'aktif')
            ->update(['status' => 'selesai']);

        try {
            app(\App\Services\NotificationService::class)->kirimKeAdmin(
                'Sanggahan Resmi Dijawab',
                "{$paket->kode_paket}: Tanggapan sanggahan telah dicatat ({$validated['hasil']}) oleh Pokja {$user->name}.",
                route('pemantauan.show', $paket),
                'sanggahan',
                'info'
            );
        } catch (\Throwable $e) {}

        return back()->with('success', 'Tanggapan sanggahan untuk paket ' . $paket->kode_paket . ' berhasil dicatat secara resmi.');
    }

    /**
     * Halaman Jadwal & Evaluasi Tahapan.
     */
    public function jadwal(Request $request)
    {
        [$user, $pokja, $listPokja, $tahun, $tahuns, $bolehSimpan] = $this->resolvePokjaAndTahun($request);

        $paketsQuery = PaketPengadaan::with(['opd:id,singkatan', 'tahapans', 'perubahanJadwals'])
            ->where('pokja_id', $pokja->id);
        if ($tahun > 0) {
            $paketsQuery->where('tahun_anggaran', $tahun);
        }
        $pakets = $paketsQuery->get();
        $paketIds = $pakets->pluck('id');

        $perubahanJadwals = PerubahanJadwal::with('paket:id,kode_paket,nama_paket,status')
            ->whereIn('paket_id', $paketIds)
            ->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->get();

        $paketRisikoDelay = $pakets->filter(fn ($p) => $p->perubahanJadwals->count() > 3)->values();

        $kpi = [
            'paketPemilihan' => $pakets->whereIn('status', ['pemilihan', 'persiapan'])->count(),
            'totalPerubahan' => $perubahanJadwals->count(),
            'tanpaBa' => $perubahanJadwals->where('ada_berita_acara', 0)->count(),
            'paketRisikoDelay' => $paketRisikoDelay->count(),
        ];

        return view('pokja.jadwal', compact(
            'user', 'pokja', 'listPokja', 'tahun', 'tahuns', 'bolehSimpan',
            'pakets', 'perubahanJadwals', 'paketRisikoDelay', 'kpi'
        ));
    }

    /**
     * Catat Perubahan Jadwal Baru.
     */
    public function simpanPerubahanJadwal(Request $request, PaketPengadaan $paket)
    {
        $user = $request->user();
        if (! $user->isAdmin() && $user->pokja_id !== $paket->pokja_id) {
            abort(403, 'Anda tidak memiliki hak untuk mengubah jadwal paket ini.');
        }

        $validated = $request->validate([
            'tanggal' => ['required', 'date'],
            'jam' => ['nullable', 'date_format:H:i'],
            'jenis' => ['required', 'string', 'max:50'],
            'tahap_terkait' => ['required', 'string', 'max:100'],
            'alasan' => ['required', 'string', 'max:500'],
            'ada_berita_acara' => ['nullable', 'boolean'],
        ]);

        $adaBa = $request->boolean('ada_berita_acara');

        PerubahanJadwal::create([
            'paket_id' => $paket->id,
            'tanggal' => $validated['tanggal'],
            'jam' => $validated['jam'] ? $validated['jam'] . ':00' : null,
            'jenis' => $validated['jenis'],
            'tahap_terkait' => $validated['tahap_terkait'],
            'alasan' => $validated['alasan'],
            'ada_berita_acara' => $adaBa,
        ]);

        if (! $adaBa) {
            AlertAnomali::firstOrCreate(
                ['paket_id' => $paket->id, 'jenis' => 'perubahan_jadwal_tanpa_ba', 'status' => 'aktif'],
                [
                    'pokja_id' => $paket->pokja_id,
                    'tingkat' => 'warning',
                    'deskripsi' => "{$paket->kode_paket}: Perubahan jadwal dilakukan tanpa Berita Acara resmi.",
                ]
            );
        }

        return back()->with('success', 'Perubahan jadwal untuk ' . $paket->kode_paket . ' berhasil dicatat.');
    }

    /**
     * Lembar Kendali & Dokumen Hasil (Print-ready).
     */
    public function laporan(Request $request)
    {
        [$user, $pokja, $listPokja, $tahun, $tahuns, $bolehSimpan] = $this->resolvePokjaAndTahun($request);

        $paketsQuery = PaketPengadaan::with(['opd:id,nama,singkatan', 'penyedia:id,nama', 'sanggahans', 'perubahanJadwals', 'progresTerakhir'])
            ->where('pokja_id', $pokja->id);
        if ($tahun > 0) {
            $paketsQuery->where('tahun_anggaran', $tahun);
        }
        $pakets = $paketsQuery->orderBy('kode_paket')->get();

        $ringkasan = [
            'totalPaket' => $pakets->count(),
            'totalPagu' => $pakets->sum('pagu'),
            'totalHps' => $pakets->sum('hps'),
            'totalKontrak' => $pakets->sum('nilai_kontrak'),
            'rataProgress' => $pakets->count() > 0 ? round($pakets->avg('progress'), 1) : 0,
            'selesai' => $pakets->where('status', 'selesai')->count(),
            'aktif' => $pakets->whereIn('status', ['persiapan', 'pemilihan', 'kontrak', 'pelaksanaan'])->count(),
            'sanggahanAktif' => $pakets->sum(fn ($p) => $p->sanggahans->where('hasil', 'menunggu')->count()),
            'perubahanJadwal' => $pakets->sum(fn ($p) => $p->perubahanJadwals->count()),
        ];

        return view('pokja.laporan', compact(
            'user', 'pokja', 'listPokja', 'tahun', 'tahuns', 'pakets', 'ringkasan'
        ));
    }
}
