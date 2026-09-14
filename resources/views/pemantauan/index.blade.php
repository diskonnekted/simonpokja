@extends('layouts.app')

@section('title', 'Pemantauan')

@section('content')
<div class="mb-4">
    <h4 class="fw-bold mb-1"><i class="bi bi-search-heart me-2 text-danger"></i>Pemantauan Paket Bermasalah</h4>
    <p class="text-secondary mb-0 small">Deteksi dini: delay berulang, tanpa Berita Acara, sanggahan tertunda, tender gagal</p>
</div>

{{-- Filter --}}
<div class="card mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-center">
            <div class="col-12 col-md-4">
                <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="Cari kode/nama paket...">
            </div>
            <div class="col-auto">
                <select name="filter" class="form-select form-select-sm" onchange="this.form.submit()">
                    @foreach ([
                        'semua' => 'Semua Bermasalah',
                        'delay' => 'Delay ≥3x',
                        'tanpa_ba' => 'Tanpa Berita Acara',
                        'sanggah' => 'Sanggahan Tertunda',
                        'gagal' => 'Tender Gagal',
                        'kritis' => 'Risiko Kritis',
                    ] as $val => $label)
                        <option value="{{ $val }}" {{ request('filter', 'semua') == $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <button class="btn btn-primary btn-sm"><i class="bi bi-search"></i></button>
            </div>
        </form>
    </div>
</div>

{{-- Tabel paket bermasalah --}}
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-card mb-0">
                <thead><tr>
                    <th class="ps-3">Paket</th>
                    <th>Pokja</th>
                    <th class="text-center">Perubahan</th>
                    <th class="text-center">Tanpa BA</th>
                    <th class="text-center">Sanggah</th>
                    <th>Risiko</th>
                    <th class="pe-3 text-center">Skor Masalah</th>
                </tr></thead>
                <tbody>
                    @forelse ($paket as $p)
                        <tr>
                            <td class="ps-3" data-label="Paket">
                                <a href="{{ route('pemantauan.show', $p) }}" class="fw-semibold text-decoration-none d-block" style="max-width: 300px;">
                                    {{ $p->nama_paket }}
                                </a>
                                <small class="text-secondary">{{ $p->kode_paket }} &bull; {{ $p->opd->singkatan ?? '-' }} &bull; {{ format_rupiah_singkat($p->pagu) }}</small>
                                <div class="mt-1">
                                    <span class="badge text-bg-{{ status_badge_class($p->status) }}" style="font-size: 0.68rem;">{{ $p->status_label }}</span>
                                    <small class="text-secondary ms-1" style="font-size: 0.72rem;"><i class="bi bi-arrow-right-short text-primary"></i>{{ $p->next_action }}</small>
                                </div>
                            </td>
                            <td data-label="Pokja"><small>{{ $p->pokja->nama ?? '-' }}</small></td>
                            <td class="text-center" data-label="Perubahan">
                                @if ($p->jumlah_perubahan >= 4)
                                    <span class="badge text-bg-danger">{{ $p->jumlah_perubahan }}x</span>
                                @elseif ($p->jumlah_perubahan >= 2)
                                    <span class="badge text-bg-warning text-dark">{{ $p->jumlah_perubahan }}x</span>
                                @else
                                    <span class="text-secondary">{{ $p->jumlah_perubahan }}x</span>
                                @endif
                                @if ($p->akhir_jam_kerja > 0)
                                    <i class="bi bi-alarm text-danger ms-1" title="{{ $p->akhir_jam_kerja }}x perubahan di akhir jam kerja"></i>
                                @endif
                            </td>
                            <td class="text-center" data-label="Tanpa BA">
                                @if ($p->tanpa_ba > 0)
                                    <span class="badge text-bg-danger">{{ $p->tanpa_ba }}</span>
                                @else
                                    <i class="bi bi-check-lg text-success"></i>
                                @endif
                            </td>
                            <td class="text-center" data-label="Sanggah">
                                @if ($p->sanggah_menunggu > 0)
                                    <span class="badge text-bg-warning text-dark">{{ $p->sanggah_menunggu }}</span>
                                @else
                                    <span class="text-secondary">0</span>
                                @endif
                            </td>
                            <td data-label="Risiko">
                                <span class="badge badge-risiko-{{ $p->risiko }}">{{ ucfirst($p->risiko) }}</span>
                                @if ($p->tender_gagal) <i class="bi bi-x-octagon text-danger ms-1" title="Tender gagal"></i>@endif
                            </td>
                            <td class="pe-3 text-center" data-label="Skor">
                                <span class="badge {{ $p->skor_masalah >= 5 ? 'text-bg-danger' : ($p->skor_masalah >= 3 ? 'text-bg-warning' : 'text-bg-secondary') }}">
                                    {{ $p->skor_masalah }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-secondary py-5">
                            <i class="bi bi-shield-check fs-1 d-block mb-2 text-success"></i>
                            <div class="fw-semibold text-dark mb-1">Tidak Ada Paket Bermasalah Terdeteksi</div>
                            <small class="text-secondary">Seluruh paket pengadaan berjalan sesuai jadwal, tanpa anomali jam kerja, tanpa BA tertunda, dan tidak ada sanggahan kritis.</small>
                        </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
