@extends('layouts.app')

@section('title', 'Dasbor Pokja — ' . $pokja->nama)

@section('breadcrumb')
<ul class="breadcrumb-links">
    <li><a href="{{ route('dashboard') }}"><i class="bi bi-house-door me-1"></i>Beranda</a></li>
    <li class="sep"><i class="bi bi-chevron-right"></i></li>
    <li><span class="text-secondary">Kelompok Kerja</span></li>
    <li class="sep"><i class="bi bi-chevron-right"></i></li>
    <li class="active">{{ $pokja->nama }}</li>
</ul>
<div class="text-secondary small font-monospace d-none d-sm-block">
    <i class="bi bi-person-badge me-1"></i>{{ $pokja->bidang }} (TA {{ $tahun }})
</div>
@endsection

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
    <div>
        <h1 class="h4 mb-0">Dasbor {{ $pokja->nama }}</h1>
        <small class="text-secondary">{{ $pokja->bidang }} &bull; {{ $user->name }}</small>
    </div>
    <form method="GET" action="{{ route('pokja.dasbor') }}" class="d-flex gap-2 align-items-center flex-wrap">
        @if ($user->isAdmin())
            <label class="small text-secondary mb-0" for="pilih-pokja">Pokja:</label>
            <select id="pilih-pokja" name="pokja" class="form-select form-select-sm" style="width:auto" onchange="this.form.submit()">
                @foreach ($listPokja as $pj)
                    <option value="{{ $pj->id }}" {{ $pj->id == $pokja->id ? 'selected' : '' }}>{{ $pj->nama }}</option>
                @endforeach
            </select>
        @endif
        <label class="small text-secondary mb-0" for="pilih-tahun">Tahun:</label>
        <select id="pilih-tahun" name="tahun" class="form-select form-select-sm" style="width:auto" onchange="this.form.submit()">
            @foreach ($tahuns as $t)
                <option value="{{ $t }}" {{ $t == $tahun ? 'selected' : '' }}>TA {{ $t }}</option>
            @endforeach
        </select>
    </form>
</div>

{{-- Baris Metrik Taktis Pokja --}}
<div class="row g-2 mb-3">
    <div class="col-6 col-lg-3">
        <div class="card h-100 border-start border-4 border-danger js-card-filter shadow-sm" data-filter="perlu_aksi" title="Klik untuk menyaring pekerjaan yang perlu tindakan segera" style="cursor: pointer;">
            <div class="card-body py-2 px-3">
                <div class="text-secondary small d-flex justify-content-between align-items-center mb-1">
                    <span class="fw-semibold">Butuh Tindakan Segera</span>
                    <i class="bi bi-exclamation-triangle-fill text-danger"></i>
                </div>
                <div class="fs-4 fw-bold text-danger mb-0">{{ $countPerStatus['perlu_aksi'] }} <small class="fs-6 fw-normal text-muted">pekerjaan</small></div>
                <div class="text-secondary" style="font-size: 0.7rem;">&bull; Klik untuk tampilkan antrean</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card h-100 border-start border-4 border-primary js-card-filter shadow-sm" data-filter="aktif" title="Klik untuk menyaring pekerjaan yang sedang aktif berjalan" style="cursor: pointer;">
            <div class="card-body py-2 px-3">
                <div class="text-secondary small d-flex justify-content-between align-items-center mb-1">
                    <span class="fw-semibold">Sedang Berjalan</span>
                    <i class="bi bi-play-circle-fill text-primary"></i>
                </div>
                <div class="fs-4 fw-bold text-primary mb-0">{{ $countPerStatus['persiapan'] + $countPerStatus['pemilihan'] + $countPerStatus['kontrak'] + $countPerStatus['pelaksanaan'] }} <small class="fs-6 fw-normal text-muted">paket</small></div>
                <div class="text-secondary" style="font-size: 0.7rem;">&bull; Persiapan, Pemilihan, Kontrak</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card h-100 border-start border-4 border-success js-card-filter shadow-sm" data-filter="selesai" title="Klik untuk menyaring pekerjaan yang sudah 100% selesai" style="cursor: pointer;">
            <div class="card-body py-2 px-3">
                <div class="text-secondary small d-flex justify-content-between align-items-center mb-1">
                    <span class="fw-semibold">Selesai Tuntas</span>
                    <i class="bi bi-check-circle-fill text-success"></i>
                </div>
                <div class="fs-4 fw-bold text-success mb-0">{{ $countPerStatus['selesai'] }} <small class="fs-6 fw-normal text-muted">paket</small></div>
                <div class="text-secondary" style="font-size: 0.7rem;">&bull; Serah terima PPK 100%</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card h-100 border-start border-4 border-{{ $rincianRisiko['warna'] }} shadow-sm" data-bs-toggle="modal" data-bs-target="#modal-rincian-risiko" title="Klik untuk membuka rincian faktor risiko dan mitigasinya" style="cursor: pointer;">
            <div class="card-body py-2 px-3">
                <div class="text-secondary small d-flex justify-content-between align-items-center mb-1">
                    <span class="fw-semibold">Skor Risiko Pokja</span>
                    <i class="bi bi-info-circle-fill text-{{ $rincianRisiko['warna'] }}"></i>
                </div>
                <div class="fs-4 fw-bold text-{{ $rincianRisiko['warna'] }} d-flex align-items-center gap-2 mb-0">
                    {{ $rincianRisiko['total'] }}
                    <span class="badge text-bg-{{ $rincianRisiko['warna'] }}" style="font-size: 0.65rem;">{{ $rincianRisiko['label'] }}</span>
                </div>
                <div class="text-{{ $rincianRisiko['warna'] }}" style="font-size: 0.7rem;"><i class="bi bi-hand-index-thumb me-1"></i>Klik lihat rincian bobot</div>
            </div>
        </div>
    </div>
</div>

{{-- R2: Perlu Tindakan Segera --}}
@if ($perluTindakan->isNotEmpty())
    <div class="card border-warning mb-3">
        <div class="card-header bg-warning-subtle py-2 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-exclamation-triangle-fill text-warning"></i>
                <strong class="small text-dark">Perlu Tindakan Segera ({{ $perluTindakan->count() }} Item)</strong>
            </div>
            <small class="text-secondary">Pekerjaan dengan deadline kritis, sanggahan aktif, atau pesan koordinasi belum dijawab</small>
        </div>
        <div class="card-body p-0">
            <ul class="list-group list-group-flush small">
                @foreach ($perluTindakan as $item)
                    <li class="list-group-item px-3 py-2 d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <span class="badge text-bg-{{ $item['badge_class'] }}">
                                <i class="bi {{ $item['ikon'] }} me-1"></i>{{ $item['judul'] }}
                            </span>
                            <span class="fw-semibold text-dark">{{ $item['paket']->nama_paket }}</span>
                            <span class="text-secondary font-monospace">({{ $item['paket']->kode_paket }})</span>
                            <span class="text-secondary">&bull; {{ $item['pesan'] }}</span>
                        </div>
                        <div>
                            @if (isset($item['url']))
                                <a href="{{ $item['url'] }}" class="btn btn-sm btn-outline-primary py-0 px-2" style="font-size: 0.75rem;">
                                    {{ $item['aksi'] }} <i class="bi bi-arrow-right"></i>
                                </a>
                            @elseif (($item['modal'] ?? '') === 'pesan')
                                <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2 js-btn-pesan"
                                        style="font-size: 0.75rem;"
                                        data-bs-toggle="modal" data-bs-target="#modal-pesan"
                                        data-paket-id="{{ $item['paket']->id }}"
                                        data-nama="{{ $item['paket']->nama_paket }}">
                                    <i class="bi bi-chat-left-text me-1"></i>{{ $item['aksi'] }}
                                </button>
                            @elseif (($item['modal'] ?? '') === 'progres' && $bolehSimpan)
                                <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2 js-btn-simpan-progres"
                                        style="font-size: 0.75rem;"
                                        data-bs-toggle="modal" data-bs-target="#modal-simpan-progres"
                                        data-action="{{ route('pokja.progres', $item['paket']) }}"
                                        data-kode="{{ $item['paket']->kode_paket }}"
                                        data-nama="{{ $item['paket']->nama_paket }}"
                                        data-progres="{{ (int) $item['paket']->progress }}"
                                        data-status="{{ $item['paket']->status }}"
                                        title="Simpan atau perbarui progres pekerjaan">
                                    <i class="bi bi-pencil-square me-1"></i>{{ $item['aksi'] }}
                                </button>
                            @else
                                <a href="#tabel-paket" class="btn btn-sm btn-outline-secondary py-0 px-2" style="font-size: 0.75rem;">
                                    {{ $item['aksi'] }} <i class="bi bi-arrow-down"></i>
                                </a>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
@endif

{{-- Daftar pekerjaan --}}
<div class="card mb-3 shadow-sm">
    <div class="card-header bg-white py-2">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
            <div class="d-flex align-items-center gap-2">
                <span class="fw-bold small text-dark"><i class="bi bi-list-task text-primary me-1"></i>Daftar Pekerjaan TA {{ $tahun }}</span>
                <span class="badge bg-light text-secondary border" id="info-jumlah">{{ $pakets->count() }} paket</span>
            </div>
            <div class="input-group input-group-sm" style="width: 250px;">
                <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-secondary"></i></span>
                <input type="text" id="cari-paket" class="form-control border-start-0 ps-1" placeholder="Cari nama/OPD... ( / )" autocomplete="off">
            </div>
        </div>
        {{-- Quick Filter Chips --}}
        <div class="d-flex flex-wrap gap-1 align-items-center pt-2 border-top" id="filter-chips">
            <span class="small text-secondary me-1" style="font-size: 0.72rem;"><i class="bi bi-funnel me-1"></i>Filter Tahap:</span>
            <button type="button" class="btn btn-sm btn-dark py-0 px-2 chip-btn active" data-chip="semua" style="font-size: 0.72rem; border-radius: 4px;">
                Semua ({{ $countPerStatus['semua'] }})
            </button>
            @if ($countPerStatus['perlu_aksi'] > 0)
                <button type="button" class="btn btn-sm btn-outline-danger py-0 px-2 chip-btn" data-chip="perlu_aksi" style="font-size: 0.72rem; border-radius: 4px;">
                    <i class="bi bi-exclamation-triangle-fill me-1"></i>Perlu Aksi ({{ $countPerStatus['perlu_aksi'] }})
                </button>
            @endif
            @if ($countPerStatus['persiapan'] > 0)
                <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2 chip-btn" data-chip="persiapan" style="font-size: 0.72rem; border-radius: 4px;">
                    Persiapan ({{ $countPerStatus['persiapan'] }})
                </button>
            @endif
            @if ($countPerStatus['pemilihan'] > 0)
                <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2 chip-btn" data-chip="pemilihan" style="font-size: 0.72rem; border-radius: 4px;">
                    Pemilihan ({{ $countPerStatus['pemilihan'] }})
                </button>
            @endif
            @if ($countPerStatus['kontrak'] > 0)
                <button type="button" class="btn btn-sm btn-outline-info py-0 px-2 chip-btn" data-chip="kontrak" style="font-size: 0.72rem; border-radius: 4px;">
                    Kontrak ({{ $countPerStatus['kontrak'] }})
                </button>
            @endif
            @if ($countPerStatus['pelaksanaan'] > 0)
                <button type="button" class="btn btn-sm btn-outline-warning py-0 px-2 chip-btn" data-chip="pelaksanaan" style="font-size: 0.72rem; border-radius: 4px;">
                    Pelaksanaan ({{ $countPerStatus['pelaksanaan'] }})
                </button>
            @endif
            @if ($countPerStatus['selesai'] > 0)
                <button type="button" class="btn btn-sm btn-outline-success py-0 px-2 chip-btn" data-chip="selesai" style="font-size: 0.72rem; border-radius: 4px;">
                    Selesai ({{ $countPerStatus['selesai'] }})
                </button>
            @endif
        </div>
    </div>
    <div class="card-body p-0" style="max-height: 640px; overflow-y: auto;">
        <table class="table table-sm table-hover align-middle mb-0" id="tabel-paket">
            <thead><tr>
                <th class="ps-3" data-sort="nama" title="Klik untuk mengurutkan">Pekerjaan</th>
                <th data-sort="progres" title="Klik untuk mengurutkan">Progres</th>
                <th data-sort="deadline" title="Klik untuk mengurutkan">Deadline</th>
                <th class="text-end pe-3">Aksi</th>
            </tr></thead>
            <tbody>
                @forelse ($pakets as $p)
                    @php
                        [$kunci, $labelDl, $warnaDl, $sisa] = $p->status_deadline;
                        $urgensiRank = match($kunci) {
                            'lewat' => 0,
                            'mendesak' => 1,
                            'mendekati' => 2,
                            default => 3,
                        };
                        $isPerluAksi = $perluTindakanPaketIds->contains($p->id) ? '1' : '0';
                    @endphp
                    <tr class="js-row"
                        data-nama="{{ strtolower($p->nama_paket . ' ' . $p->kode_paket . ' ' . ($p->opd?->singkatan ?? '')) }}"
                        data-progres="{{ (int) $p->progress }}"
                        data-status="{{ $p->status }}"
                        data-perlu-aksi="{{ $isPerluAksi }}"
                        data-urgensi="{{ $urgensiRank }}"
                        data-deadline="{{ $p->tanggal_selesai?->format('Y-m-d') ?? '9999-12-31' }}">
                        <td class="ps-3" style="max-width: 280px;">
                            <div class="fw-semibold small text-truncate" title="{{ $p->nama_paket }}">{{ $p->nama_paket }}</div>
                            <small class="text-secondary">{{ $p->kode_paket }} &bull; {{ $p->opd?->singkatan ?? '-' }} &bull; {{ \Illuminate\Support\Str::title(str_replace('_',' ',$p->metode)) }}</small>
                            <div class="mt-1">
                                <span class="badge text-bg-{{ status_badge_class($p->status) }}" style="font-size: 0.68rem;">{{ $p->status_label }}</span>
                                <small class="text-secondary ms-1" style="font-size: 0.72rem;"><i class="bi bi-arrow-right-short text-primary"></i>{{ $p->next_action }}</small>
                            </div>
                        </td>
                        <td style="min-width: 120px;">
                            <div class="tahapan-bar" role="img" aria-label="Progres {{ $p->progress }}%">
                                @foreach ($p->tahapan_progres as $t)
                                    <span class="tahapan-seg" style="background: {{ \App\Models\PaketPengadaan::warnaTahapan($t['status']) }}; width: {{ 100 / count($p->tahapan_progres) }}%;"></span>
                                @endforeach
                            </div>
                            <small class="text-secondary">{{ $p->progress }}%</small>
                        </td>
                        <td class="text-nowrap">
                            <div>{{ $p->tanggal_selesai?->format('d M Y') ?? '-' }}</div>
                            <span class="badge text-bg-{{ $warnaDl }}">{{ $labelDl }}</span>
                        </td>
                        <td class="text-end pe-3 text-nowrap">
                            @if ($bolehSimpan)
                                <button type="button" class="btn btn-sm btn-primary js-btn-simpan-progres"
                                        data-bs-toggle="modal" data-bs-target="#modal-simpan-progres"
                                        data-action="{{ route('pokja.progres', $p) }}"
                                        data-kode="{{ $p->kode_paket }}"
                                        data-nama="{{ $p->nama_paket }}"
                                        data-progres="{{ (int) $p->progress }}"
                                        data-status="{{ $p->status }}"
                                        title="Simpan atau perbarui progres pekerjaan">
                                    <i class="bi bi-pencil-square me-1"></i>Simpan Progres
                                </button>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary">Tinjauan</span>
                            @endif
                            <button type="button" class="btn btn-sm btn-outline-secondary ms-1" data-bs-toggle="modal"
                                    data-bs-target="#modal-riwayat" data-url="{{ route('pokja.riwayat', $p) }}"
                                    title="Riwayat progres">
                                <i class="bi bi-clock-history"></i>
                            </button>
                            @php $sp = $statPesan[$p->id] ?? null; @endphp
                            <button type="button" class="btn btn-sm btn-outline-primary ms-1 position-relative js-btn-pesan"
                                    data-bs-toggle="modal" data-bs-target="#modal-pesan"
                                    data-paket-id="{{ $p->id }}"
                                    data-nama="{{ $p->nama_paket }}"
                                    title="Diskusi dengan {{ $user->isAdmin() ? 'Pokja' : 'Kepala LPSE' }} ({{ $sp->total ?? 0 }} pesan)">
                                <i class="bi bi-chat-left-text"></i>
                                @if (($sp->baru ?? 0) > 0)
                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger js-badge-pesan">{{ $sp->baru }}</span>
                                @endif
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center text-secondary py-5">
                        <i class="bi bi-clipboard2-check fs-1 d-block mb-2 text-muted"></i>
                        <div class="fw-semibold text-dark mb-1">Belum Ada Pekerjaan Ditugaskan</div>
                        <small class="text-secondary">Belum ada paket pengadaan TA {{ $tahun }} yang ditugaskan ke kelompok kerja ini. Hubungi Admin LPSE untuk penugasan paket baru.</small>
                    </td></tr>
                @endforelse
                <tr id="baris-tak-ada" class="d-none">
                    <td colspan="4" class="text-center text-secondary py-4">
                        <i class="bi bi-search d-block fs-4 mb-1"></i>
                        Tidak ada pekerjaan yang cocok dengan pencarian.
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

{{-- Modal simpan progres pekerjaan --}}
<div class="modal fade" id="modal-simpan-progres" tabindex="-1" aria-labelledby="modal-progres-title" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 480px;">
        <div class="modal-content border-0 shadow">
            <form method="POST" id="form-simpan-progres" action="">
                @csrf
                <div class="modal-header py-2 px-3 bg-light border-bottom">
                    <div>
                        <h5 class="modal-title h6 mb-0 text-dark" id="modal-progres-title">
                            <i class="bi bi-pencil-square text-primary me-1"></i>Simpan Progres Pekerjaan
                        </h5>
                        <small class="text-secondary d-block mt-1 font-monospace text-truncate" id="modal-progres-subjudul" style="font-size: 0.75rem; max-width: 380px;">-</small>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body p-3">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold mb-1" for="modal-input-progres">Progres Fisik (%) <span class="text-danger">*</span></label>
                        <div class="input-group input-group-sm">
                            <input type="number" id="modal-input-progres" name="progress" class="form-control"
                                   min="0" max="100" required>
                            <span class="input-group-text">%</span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold mb-1" for="modal-select-status">Status Tahapan <span class="text-danger">*</span></label>
                        <select id="modal-select-status" name="status" class="form-select form-select-sm js-modal-status-progres" required>
                            @foreach (['draft','persiapan','pemilihan','kontrak','pelaksanaan','selesai','batal'] as $s)
                                <option value="{{ $s }}">{{ ucfirst($s) }}</option>
                            @endforeach
                        </select>
                        <div class="form-text text-muted" style="font-size: 0.75rem;">
                            <i class="bi bi-info-circle me-1"></i>Persentase progres akan menyesuaikan otomatis dengan tahapan yang dipilih.
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small fw-semibold mb-1" for="modal-textarea-catatan">Catatan Kemajuan / Kendala</label>
                        <textarea id="modal-textarea-catatan" name="catatan" class="form-control form-control-sm" rows="3"
                                  placeholder="Tuliskan kemajuan pekerjaan atau kendala lapangan hari ini..."></textarea>
                    </div>
                </div>
                <div class="modal-footer py-2 px-3 bg-light border-top d-flex justify-content-between">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x me-1"></i>Batal
                    </button>
                    <button type="submit" class="btn btn-sm btn-primary">
                        <i class="bi bi-check2-circle me-1"></i>Simpan Progres
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal riwayat --}}
<div class="modal fade" id="modal-riwayat" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h5 class="modal-title h6" id="riwayat-judul">Riwayat Progres</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body" id="riwayat-isi"><div class="text-secondary text-center py-4">Memuat...</div></div>
        </div>
    </div>
</div>

{{-- Modal diskusi Kepala LPSE <-> Pokja per pekerjaan --}}
<div class="modal fade" id="modal-pesan" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h5 class="modal-title h6 mb-0" id="pesan-judul">Diskusi Pekerjaan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body p-0 bg-body-tertiary">
                @include('pesan._chat')
            </div>
        </div>
    </div>
</div>

{{-- Modal Rincian Skor Risiko --}}
<div class="modal fade" id="modal-rincian-risiko" tabindex="-1" aria-labelledby="modal-risiko-title" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 540px;">
        <div class="modal-content border-0 shadow">
            <div class="modal-header py-2 px-3 bg-light border-bottom">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-shield-exclamation text-{{ $rincianRisiko['warna'] }} fs-5"></i>
                    <div>
                        <h6 class="modal-title fw-bold mb-0 text-dark" id="modal-risiko-title">
                            Rincian Skor Risiko — {{ $pokja->nama }}
                        </h6>
                        <small class="text-secondary" style="font-size: 0.72rem;">Kalkulasi otomatis 4 indikator operasional panitia</small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body p-3">
                <div class="d-flex justify-content-between align-items-center p-3 rounded-3 mb-3 bg-{{ $rincianRisiko['warna'] }}-subtle border border-{{ $rincianRisiko['warna'] }}-subtle">
                    <div>
                        <div class="small fw-semibold text-{{ $rincianRisiko['warna'] }}">Skor Risiko Keseluruhan</div>
                        <div class="display-6 fw-bold text-{{ $rincianRisiko['warna'] }} mb-0">{{ $rincianRisiko['total'] }}<span class="fs-6 fw-normal text-muted"> / 100</span></div>
                    </div>
                    <div class="text-end">
                        <span class="badge text-bg-{{ $rincianRisiko['warna'] }} px-3 py-2 fs-6">{{ $rincianRisiko['label'] }}</span>
                        <div class="text-secondary mt-1" style="font-size: 0.7rem;">Status Kepatuhan Pokja</div>
                    </div>
                </div>

                <div class="small fw-bold text-dark mb-2"><i class="bi bi-bar-chart-steps me-1 text-primary"></i>Penyusun Skor Risiko:</div>
                <div class="list-group list-group-flush mb-3 border rounded">
                    @foreach ($rincianRisiko['komponen'] as $k => $komp)
                        <div class="list-group-item px-3 py-2">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="fw-semibold text-dark small">{{ $komp['nama'] }}</span>
                                <span class="badge {{ $komp['poin'] > 0 ? 'bg-danger-subtle text-danger border border-danger-subtle' : 'bg-success-subtle text-success border border-success-subtle' }}">
                                    +{{ $komp['poin'] }} / {{ $komp['max'] }} poin
                                </span>
                            </div>
                            <div class="progress" style="height: 5px;">
                                <div class="progress-bar bg-{{ $komp['poin'] > 0 ? ($komp['poin'] >= ($komp['max'] * 0.7) ? 'danger' : 'warning') : 'success' }}" style="width: {{ $komp['max'] > 0 ? min(100, ($komp['poin'] / $komp['max']) * 100) : 0 }}%;"></div>
                            </div>
                            <small class="text-secondary d-block mt-1" style="font-size: 0.72rem;">{{ $komp['deskripsi'] }}</small>
                        </div>
                    @endforeach
                </div>

                @if ($rincianRisiko['paket_delay']->isNotEmpty())
                    <div class="mb-3">
                        <div class="small fw-bold text-dark mb-1"><i class="bi bi-clock-history text-warning me-1"></i>Paket dengan Pergeseran Jadwal &gt;3x:</div>
                        <ul class="list-group list-group-flush border rounded small">
                            @foreach ($rincianRisiko['paket_delay'] as $pd)
                                <li class="list-group-item py-1 px-3 d-flex justify-content-between align-items-center text-secondary">
                                    <span class="text-truncate" style="max-width: 320px;">{{ $pd->nama_paket }}</span>
                                    <span class="font-monospace" style="font-size: 0.7rem;">{{ $pd->kode_paket }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="p-2 rounded bg-light border small text-secondary" style="font-size: 0.75rem;">
                    <strong class="text-dark"><i class="bi bi-lightbulb-fill me-1 text-warning"></i>Tips Mitigasi Taktis:</strong>
                    Selesaikan pekerjaan yang melampaui deadline dan tanggapi sanggahan aktif untuk menurunkan skor risiko panitia ke kategori <strong>RENDAH</strong>.
                </div>
            </div>
            <div class="modal-footer py-1 px-3 bg-light">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

@include('pesan._chat-script')
@endsection

@push('styles')
<style>
    .tahapan-bar { display: flex; height: 10px; border-radius: 5px; overflow: hidden; gap: 2px; background: #f8f9fa; }
    .tahapan-seg { display: block; height: 100%; border-radius: 2px; }

    /* Header tabel tetap terlihat saat kontainer di-scroll */
    #tabel-paket thead th {
        position: sticky;
        top: 0;
        z-index: 10;
        background: var(--bs-gray-100, #f8f9fa);
        box-shadow: inset 0 -1px 0 var(--bs-border-color);
    }
    #tabel-paket th[data-sort] { cursor: pointer; user-select: none; white-space: nowrap; }
    #tabel-paket th[data-sort]:hover { color: var(--bs-primary); }
    #tabel-paket th[data-sort] .sort-ico { font-size: .7rem; margin-left: .2rem; }

    /* Fokus kolom pencarian */
    #cari-paket:focus { box-shadow: none; border-color: var(--bs-primary); }
    .input-group:focus-within .input-group-text { border-color: var(--bs-primary); }
    .input-group:focus-within { box-shadow: 0 0 0 .2rem rgba(13, 110, 253, .15); border-radius: .375rem; }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    'use strict';
    // ====== Modal Simpan Progres ======
    // Pemetaan konsisten dengan logika model PaketPengadaan (tahapan progres):
    // draft=0, persiapan=10, pemilihan=30, kontrak=50, pelaksanaan=70, selesai=100, batal=0
    const PETA_STATUS_PROGRES = {
        draft: 0, persiapan: 10, pemilihan: 30, kontrak: 50,
        pelaksanaan: 70, selesai: 100, batal: 0
    };
    const modalProgres = document.getElementById('modal-simpan-progres');
    if (modalProgres) {
        const formProgres = document.getElementById('form-simpan-progres');
        const titleSub = document.getElementById('modal-progres-subjudul');
        const inputProg = document.getElementById('modal-input-progres');
        const selectStatus = document.getElementById('modal-select-status');
        const txtCatatan = document.getElementById('modal-textarea-catatan');

        modalProgres.addEventListener('show.bs.modal', function (ev) {
            const btn = ev.relatedTarget;
            if (!btn) return;

            const action = btn.dataset.action || '';
            const kode = btn.dataset.kode || '';
            const nama = btn.dataset.nama || '';
            const progres = btn.dataset.progres || 0;
            const status = btn.dataset.status || 'draft';

            formProgres.action = action;
            titleSub.textContent = kode + (nama ? ' • ' + nama : '');
            titleSub.title = nama;
            inputProg.value = progres;
            selectStatus.value = status;
            txtCatatan.value = '';
        });

        if (selectStatus && inputProg) {
            selectStatus.addEventListener('change', function () {
                if (selectStatus.value in PETA_STATUS_PROGRES) {
                    inputProg.value = PETA_STATUS_PROGRES[selectStatus.value];
                    inputProg.classList.add('border-primary');
                    setTimeout(() => inputProg.classList.remove('border-primary'), 1200);
                }
            });
        }
    }

    // Modal riwayat: ambil data via API
    const modal = document.getElementById('modal-riwayat');
    modal.addEventListener('show.bs.modal', function (ev) {
        const url = ev.relatedTarget.dataset.url;
        const isi = document.getElementById('riwayat-isi');
        isi.innerHTML = '<div class="text-secondary text-center py-4">Memuat...</div>';
        fetch(url)
            .then(r => r.json())
            .then(d => {
                document.getElementById('riwayat-judul').textContent =
                    'Riwayat Progres — ' + d.paket.kode;
                isi.innerHTML = d.riwayat.length === 0
                    ? '<div class="text-secondary text-center py-4">Belum ada riwayat progres.</div>'
                    : '<table class="table table-sm table-striped mb-0"><thead><tr><th>Waktu</th><th>Oleh</th><th>Progres</th><th>Status</th><th>Catatan</th></tr></thead><tbody>' +
                      d.riwayat.map(r =>
                        `<tr><td class="text-nowrap">${r.waktu}</td><td>${r.user || '-'}</td>` +
                        `<td><strong>${r.progress}%</strong></td><td>${r.status}</td><td class="small">${r.catatan || '-'}</td></tr>`
                      ).join('') + '</tbody></table>';
            })
            .catch(() => { isi.innerHTML = '<div class="text-danger text-center py-4">Gagal memuat riwayat.</div>'; });
    });

    // Modal pesan: sambungkan panel chat ke paket yang dipilih
    const TPL_PESAN_INDEX = @json(route('pesan.index', ['paket' => '__ID__']));
    const TPL_PESAN_STORE = @json(route('pesan.store', ['paket' => '__ID__']));
    const modalPesan = document.getElementById('modal-pesan');
    const panelPesan = modalPesan.querySelector('.chat-paket');
    const judulPesan = document.getElementById('pesan-judul');

    modalPesan.addEventListener('show.bs.modal', function (ev) {
        const btn = ev.relatedTarget;
        const id = btn.dataset.paketId;
        judulPesan.textContent = 'Diskusi — ' + btn.dataset.nama;
        panelPesan.dataset.paket = id;
        window.ChatPaket.attach(panelPesan, TPL_PESAN_INDEX.replace('__ID__', id), TPL_PESAN_STORE.replace('__ID__', id));
        // Membuka thread menandai pesan lawan sebagai terbaca di server → bersihkan badge
        const badge = btn.querySelector('.js-badge-pesan');
        if (badge) badge.remove();
    });

    modalPesan.addEventListener('hidden.bs.modal', function () {
        window.ChatPaket.detach(panelPesan);
        judulPesan.textContent = 'Diskusi Pekerjaan';
    });

    // ====== Pencarian cepat + Filter Chip Tahapan + Pengurutan Tabel ======
    const inputCari = document.getElementById('cari-paket');
    const tabel = document.getElementById('tabel-paket');
    const tbody = tabel.querySelector('tbody');
    const baris = Array.from(tbody.querySelectorAll('tr.js-row'));
    const takAda = document.getElementById('baris-tak-ada');
    const info = document.getElementById('info-jumlah');
    const chipBtns = document.querySelectorAll('.chip-btn');
    const cardFilters = document.querySelectorAll('.js-card-filter');

    let currentChip = 'semua';

    function terapkanFilter() {
        const q = inputCari.value.trim().toLowerCase();
        let n = 0;

        baris.forEach(function (tr) {
            const namaCocok = !q || tr.textContent.toLowerCase().includes(q);
            let chipCocok = true;

            if (currentChip === 'semua') {
                chipCocok = true;
            } else if (currentChip === 'perlu_aksi') {
                chipCocok = (tr.dataset.perluAksi === '1');
            } else if (currentChip === 'aktif') {
                chipCocok = ['persiapan', 'pemilihan', 'kontrak', 'pelaksanaan'].includes(tr.dataset.status);
            } else {
                chipCocok = (tr.dataset.status === currentChip);
            }

            const tampil = namaCocok && chipCocok;
            tr.classList.toggle('d-none', !tampil);
            if (tampil) n++;
        });

        takAda.classList.toggle('d-none', n > 0);
        info.textContent = (q || currentChip !== 'semua') ? n + ' / ' + baris.length + ' paket' : baris.length + ' paket';
        info.title = `Menampilkan ${n} dari ${baris.length} pekerjaan`;
    }

    inputCari.addEventListener('input', terapkanFilter);

    // Filter Chips Event
    chipBtns.forEach(function (btn) {
        btn.addEventListener('click', function () {
            currentChip = this.dataset.chip;
            chipBtns.forEach(b => {
                b.classList.remove('btn-dark', 'active');
                if (!b.className.includes('btn-outline-')) {
                    // reset styling jika bukan outline
                }
            });
            this.classList.add('btn-dark', 'active');
            terapkanFilter();
        });
    });

    // Metric Cards Event (klik kartu langsung memfilter tabel)
    cardFilters.forEach(function (card) {
        card.addEventListener('click', function () {
            const targetFilter = this.dataset.filter;
            const targetChip = Array.from(chipBtns).find(b => b.dataset.chip === targetFilter);
            if (targetChip) {
                targetChip.click();
            } else if (targetFilter === 'aktif') {
                currentChip = 'aktif';
                chipBtns.forEach(b => b.classList.remove('btn-dark', 'active'));
                terapkanFilter();
            }
            // Scroll halus ke tabel
            tabel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        });
    });

    // Tekan "/" untuk langsung fokus ke kolom pencarian
    document.addEventListener('keydown', function (e) {
        if (e.key === '/' && !/^(INPUT|TEXTAREA|SELECT)$/.test(e.target.tagName)) {
            e.preventDefault();
            inputCari.focus();
        }
    });

    // Urutkan kolom manual saat header diklik: Pekerjaan (teks), Progres (angka), Deadline (tanggal)
    let sortKey = null, sortDir = 1;
    tabel.querySelectorAll('th[data-sort]').forEach(function (th) {
        th.addEventListener('click', function () {
            const key = th.dataset.sort;
            sortDir = (sortKey === key) ? -sortDir : 1;
            sortKey = key;
            baris.slice().sort(function (a, b) {
                let va = a.dataset[key] ?? '', vb = b.dataset[key] ?? '';
                if (key === 'progres') { va = +va; vb = +vb; }
                return (va > vb ? 1 : va < vb ? -1 : 0) * sortDir;
            }).forEach(function (tr) { tbody.appendChild(tr); });
            tbody.appendChild(takAda); // baris "tidak ditemukan" selalu di akhir
            tabel.querySelectorAll('th[data-sort] .sort-ico').forEach(function (el) { el.remove(); });
            th.insertAdjacentHTML('beforeend',
                ' <i class="bi bi-caret-' + (sortDir === 1 ? 'up' : 'down') + '-fill sort-ico"></i>');
        });
    });

    // Catatan: Halaman dibuka dengan mempertahankan urutan Triage Bawaan
    // (Melampaui Deadline -> Mendesak -> Mendekati -> Normal) dari server tanpa diacak ulang.
});
</script>
@endpush
