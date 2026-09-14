@extends('layouts.app')

@section('title', $pokja->nama)

@section('breadcrumb')
<ul class="breadcrumb-links">
    <li><a href="{{ route('dashboard') }}"><i class="bi bi-house-door me-1"></i>Beranda</a></li>
    <li class="sep"><i class="bi bi-chevron-right"></i></li>
    <li><a href="{{ route('pokja.index') }}">Kinerja Pokja</a></li>
    <li class="sep"><i class="bi bi-chevron-right"></i></li>
    <li class="active">{{ $pokja->nama }}</li>
</ul>
<div class="text-secondary small font-monospace d-none d-sm-block">
    <i class="bi bi-person-badge me-1"></i>Ketua: {{ $pokja->ketua }} &bull; TA {{ $tahun }}
</div>
@endsection

@section('content')
{{-- ===== Header ===== --}}
<div class="d-flex flex-wrap justify-content-between align-items-start mb-4 gap-2">
    <div class="d-flex gap-2">
        <a href="{{ route('pokja.index') }}" class="btn btn-light btn-sm mt-1"><i class="bi bi-arrow-left"></i></a>
        <div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <h4 class="fw-bold mb-0">{{ $pokja->nama }}</h4>
                <span class="badge badge-risiko-{{ strtolower($pokja->label_risiko) }}">Risiko {{ $pokja->label_risiko }} ({{ $pokja->skor_risiko }}/100)</span>
            </div>
            <p class="text-secondary mb-0 small">{{ $pokja->bidang }} &bull; Ketua: {{ $pokja->ketua }} &bull; {{ count($pokja->anggota ?? []) }} anggota</p>
        </div>
    </div>
    <form method="GET">
        <select name="tahun" class="form-select form-select-sm" onchange="this.form.submit()">
            @foreach ($tahuns as $t)
                <option value="{{ $t }}" {{ $t == $tahun ? 'selected' : '' }}>TA {{ $t }}</option>
            @endforeach
        </select>
    </form>
</div>

{{-- ===== Statistik Pokja ===== --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-lg">
        <div class="card h-100"><div class="card-body py-3 text-center">
            <small class="text-secondary d-block">Total Paket</small>
            <div class="fs-4 fw-bold">{{ $stat['total_paket'] }}</div>
        </div></div>
    </div>
    <div class="col-6 col-lg">
        <div class="card h-100"><div class="card-body py-3 text-center">
            <small class="text-secondary d-block">Paket Aktif</small>
            <div class="fs-4 fw-bold text-primary">{{ $stat['paket_aktif'] }}</div>
            <small class="{{ $pokja->overload ? 'text-danger fw-semibold' : 'text-secondary' }}">
                rasio {{ $pokja->rasio_beban }}/anggota (ideal ≤{{ $pokja->kapasitas_ideal }})
            </small>
        </div></div>
    </div>
    <div class="col-6 col-lg">
        <div class="card h-100"><div class="card-body py-3 text-center">
            <small class="text-secondary d-block">Total Pagu</small>
            <div class="fs-5 fw-bold">{{ format_rupiah_singkat($stat['total_pagu']) }}</div>
        </div></div>
    </div>
    <div class="col-6 col-lg">
        <div class="card h-100"><div class="card-body py-3 text-center">
            <small class="text-secondary d-block">Tender Gagal</small>
            <div class="fs-4 fw-bold {{ $stat['tender_gagal'] > 0 ? 'text-danger' : '' }}">{{ $stat['tender_gagal'] }}</div>
        </div></div>
    </div>
    <div class="col-6 col-lg">
        <div class="card h-100"><div class="card-body py-3 text-center">
            <small class="text-secondary d-block">SLA</small>
            <div class="fs-4 fw-bold {{ $pokja->kepatuhan_sla < 80 ? 'text-warning' : 'text-success' }}">{{ $pokja->kepatuhan_sla }}%</div>
        </div></div>
    </div>
</div>

{{-- ===== Alert Pokja Ini ===== --}}
@if ($alerts->isNotEmpty())
    <div class="alert alert-warning d-flex align-items-start gap-2 mb-4">
        <i class="bi bi-exclamation-octagon-fill mt-1"></i>
        <div class="flex-grow-1">
            <strong>Alert aktif pada pokja ini:</strong>
            <ul class="mb-0 mt-1 small">
                @foreach ($alerts as $a)
                    <li>{{ $a->deskripsi }} <em class="text-secondary">({{ $a->created_at->diffForHumans() }})</em></li>
                @endforeach
            </ul>
        </div>
    </div>
@endif

<div class="row g-3">
    {{-- ===== Kolom Kiri: Paket + Delay + Sanggahan ===== --}}
    <div class="col-lg-7">
        <div class="card mb-3">
            <div class="card-header bg-white pt-3">
                <h6 class="fw-bold mb-0">Paket yang Ditangani</h6>
            </div>
            <div class="card-body p-0" style="max-height: 380px; overflow-y: auto;">
                <table class="table table-sm table-hover mb-0">
                    <thead><tr>
                        <th class="ps-3">Paket</th>
                        <th title="Progres fisik pekerjaan &amp; tahapan pengadaan">Progres</th>
                        <th>Risiko</th>
                        <th>Pagu</th>
                        <th class="pe-3">Status</th>
                    </tr></thead>
                    <tbody>
                        @forelse ($pakets as $p)
                            <tr>
                                <td class="ps-3">
                                    <a href="{{ route('pemantauan.show', $p) }}" class="fw-semibold text-decoration-none small d-block text-truncate" style="max-width: 260px;">
                                        {{ \Illuminate\Support\Str::limit($p->nama_paket, 40) }}
                                    </a>
                                    <small class="text-secondary">{{ $p->kode_paket }} &bull; {{ $p->opd->singkatan ?? '-' }}</small>
                                </td>
                                <td style="min-width: 130px;">
                                    {{-- Mini bar progres tahapan: hijau=selesai, biru=proses, abu=belum --}}
                                    <div class="tahapan-bar" role="img"
                                         aria-label="Progres {{ $p->progres_persen }}%: {{ collect($p->tahapan_progres)->map(fn($t) => $t['nama'].' '.($t['status'] == 'selesai' ? 'selesai' : ($t['status'] == 'proses' ? 'sedang proses' : 'belum mulai')))->implode(', ') }}"
                                         title="{{ collect($p->tahapan_progres)->map(fn($t) => $t['nama'].': '.($t['status'] == 'selesai' ? 'selesai' : ($t['status'] == 'proses' ? 'sedang proses' : 'belum mulai')))->implode(' | ') }}">
                                        @foreach ($p->tahapan_progres as $t)
                                            <span class="tahapan-seg" style="background: {{ \App\Models\PaketPengadaan::warnaTahapan($t['status']) }}; width: {{ 100 / count($p->tahapan_progres) }}%;"
                                                  data-bs-toggle="tooltip" data-bs-title="{{ $t['nama'] }} — {{ $t['status'] == 'selesai' ? 'selesai' : ($t['status'] == 'proses' ? 'sedang proses' : 'belum mulai') }}"></span>
                                        @endforeach
                                    </div>
                                    <small class="text-secondary tahapan-persen">{{ $p->progress }}%</small>
                                </td>
                                <td>
                                    <span class="badge badge-risiko-{{ $p->risiko }}">
                                        {{ ucfirst($p->risiko) }}
                                    </span>
                                    @if ($p->tender_gagal) <i class="bi bi-x-octagon text-danger" title="Tender gagal"></i>@endif
                                </td>
                                <td class="text-nowrap">{{ format_rupiah_singkat($p->pagu) }}</td>
                                <td class="pe-3"><span class="badge text-bg-{{ status_badge_class($p->status) }}">{{ $p->status_label }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-secondary py-4">Tidak ada paket TA {{ $tahun }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Delay per paket --}}
        <div class="card mb-3">
            <div class="card-header bg-white pt-3">
                <h6 class="fw-bold mb-0"><i class="bi bi-hourglass-split me-2 text-warning"></i>Pengunduran Jadwal per Paket</h6>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm table-hover mb-0">
                    <thead><tr>
                        <th class="ps-3">Paket</th>
                        <th class="text-center">Total</th>
                        <th class="text-center">Tanpa BA</th>
                        <th class="pe-3">Status SLA</th>
                    </tr></thead>
                    <tbody>
                        @forelse ($delayPerPaket as $d)
                            <tr>
                                <td class="ps-3">
                                    <span class="small fw-semibold">{{ \Illuminate\Support\Str::limit($d->paket->nama_paket ?? '-', 35) }}</span>
                                    <small class="text-secondary d-block">{{ $d->paket->kode_paket ?? '' }}</small>
                                </td>
                                <td class="text-center">
                                    <span class="badge {{ $d->jumlah > 3 ? 'text-bg-danger' : ($d->jumlah >= 2 ? 'text-bg-warning text-dark' : 'text-bg-secondary') }}">{{ $d->jumlah }}x</span>
                                </td>
                                <td class="text-center">
                                    @if ($d->tanpa_ba > 0)
                                        <span class="badge text-bg-danger">{{ $d->tanpa_ba }}</span>
                                    @else
                                        <i class="bi bi-check-lg text-success"></i>
                                    @endif
                                </td>
                                <td class="pe-3">
                                    @if ($d->jumlah > 3)
                                        <span class="badge text-bg-danger">Melanggar SLA</span>
                                    @else
                                        <span class="badge text-bg-success">Patuh</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-secondary py-4"><i class="bi bi-check-circle text-success me-1"></i>Tidak ada perubahan jadwal</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Sanggahan --}}
        <div class="card">
            <div class="card-header bg-white pt-3">
                <h6 class="fw-bold mb-0"><i class="bi bi-chat-dots me-2 text-info"></i>Sanggahan pada Paket Pokja Ini</h6>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm table-hover mb-0">
                    <thead><tr>
                        <th class="ps-3">Paket</th>
                        <th>Penyedia</th>
                        <th>Masuk <i class="bi bi-arrow-right small"></i> Dijawab</th>
                        <th class="pe-3">Hasil</th>
                    </tr></thead>
                    <tbody>
                        @forelse ($sanggahans as $s)
                            <tr>
                                <td class="ps-3"><small>{{ $s->paket->kode_paket ?? '-' }}</small></td>
                                <td><small>{{ $s->penyedia->nama ?? '-' }}</small></td>
                                <td><small>{{ format_tanggal_id($s->tanggal_masuk) }}<br><i class="bi bi-arrow-return-right text-secondary me-1"></i>{{ $s->tanggal_dijawab ? format_tanggal_id($s->tanggal_dijawab) : 'BELUM' }}</small></td>
                                <td class="pe-3">
                                    <span class="badge {{ $s->hasil == 'menunggu' ? 'text-bg-warning text-dark' : ($s->hasil == 'diterima' ? 'text-bg-success' : 'text-bg-secondary') }}">
                                        {{ ucfirst($s->hasil) }}
                                    </span>
                                    @if ($s->hasil == 'menunggu' && $s->tanggal_masuk->diffInDays(now()) > 3)
                                        <i class="bi bi-exclamation-triangle text-danger" title="Melewati SLA 3 hari"></i>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-secondary py-4">Tidak ada sanggahan</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ===== Kolom Kanan: Checklist Audit 6 Kategori ===== --}}
    <div class="col-lg-5">
        <div class="card">
            <div class="card-header bg-white pt-3">
                <h6 class="fw-bold mb-0"><i class="bi bi-clipboard2-check me-2 text-primary"></i>Checklist Audit Risiko</h6>
                <small class="text-secondary">6 kategori sesuai instrumen audit pokja</small>
            </div>
            <div class="card-body" style="max-height: 760px; overflow-y: auto;">
                @foreach ($kategoriList as $kat => $katNama)
                    @php
                        $items = $checklists->get($kat, collect());
                        $ya = $items->where('jawaban', 'ya')->count();
                        $dinilai = $items->whereIn('jawaban', ['ya', 'tidak'])->count();
                        $persenYa = $dinilai > 0 ? round(($ya / $dinilai) * 100) : null;
                    @endphp
                    <div class="mb-3 pb-3 border-bottom">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="fw-semibold small">{{ $kat }}. {{ $katNama }}</span>
                            @if ($persenYa !== null)
                                <span class="badge {{ $persenYa >= 50 ? 'text-bg-danger' : 'text-bg-success' }}">{{ $persenYa }}% "ya"</span>
                            @else
                                <span class="badge text-bg-light text-secondary">Belum diaudit</span>
                            @endif
                        </div>
                        {{-- Pertanyaan default yang belum tersimpan --}}
                        @foreach (collect(\App\Models\AuditChecklist::poinDefault($kat)) as $poin)
                            @php $tersimpan = $items->firstWhere('poin', $poin); @endphp
                            <form action="{{ route('pokja.checklist', $pokja) }}" method="POST"
                                  class="d-flex align-items-center gap-2 py-1 {{ $tersimpan ? 'opacity-75' : '' }}">
                                @csrf
                                <input type="hidden" name="kategori" value="{{ $kat }}">
                                <input type="hidden" name="poin" value="{{ $poin }}">
                                <select name="jawaban" class="form-select form-select-sm" style="width: 92px;" onchange="this.form.submit()">
                                    @foreach (['na' => 'N/A', 'ya' => 'Ya', 'tidak' => 'Tidak'] as $val => $label)
                                        <option value="{{ $val }}" {{ ($tersimpan->jawaban ?? 'na') == $val ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                                <span class="small {{ $tersimpan ? 'text-secondary' : '' }}">{{ $poin }}</span>
                            </form>
                        @endforeach
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    /* Mini bar progres tahapan — 5 segmen berurutan */
    .tahapan-bar {
        display: flex;
        height: 10px;
        border-radius: 5px;
        overflow: hidden;
        gap: 2px;
        background: #f8f9fa;
    }
    .tahapan-seg {
        display: block;
        height: 100%;
        border-radius: 2px;
        transition: opacity .15s ease;
    }
    .tahapan-seg:hover { opacity: .75; }
    .tahapan-persen { font-size: .72rem; display: inline-block; margin-top: 2px; }
    /* Mobile: bar tetap terbaca dalam tampilan kartu */
    @media (max-width: 767.98px) {
        .tahapan-bar { min-width: 110px; }
        .tahapan-persen { margin-left: 6px; }
    }
    /* Cetak: bar tampil polos agar jelas di printer hitam-putih */
    @media print {
        .tahapan-seg { outline: 1px solid #adb5bd; }
        .tahapan-persen { font-size: .7rem; }
    }
</style>
@endpush
@endsection
