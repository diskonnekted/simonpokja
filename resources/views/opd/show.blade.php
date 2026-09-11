@extends('layouts.app')

@section('title', $opd->singkatan ?? 'Detail OPD')

@section('content')
<div class="d-flex align-items-center gap-2 mb-4">
    <a href="{{ route('opd.index') }}" class="btn btn-light btn-sm mt-1"><i class="bi bi-arrow-left"></i></a>
    <div>
        <h4 class="fw-bold mb-0">{{ $opd->nama }}</h4>
        <p class="text-secondary mb-0 small">{{ $opd->kode }} @if($opd->kepala) &bull; Kepala: {{ $opd->kepala }} @endif</p>
    </div>
    <a href="{{ route('opd.edit', $opd) }}" class="btn btn-warning btn-sm ms-auto"><i class="bi bi-pencil me-1"></i>Edit</a>
</div>

{{-- Statistik --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="card h-100"><div class="card-body py-3 text-center">
            <small class="text-secondary d-block">Total Paket</small>
            <div class="fs-4 fw-bold">{{ $statistik['total_paket'] }}</div>
        </div></div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card h-100"><div class="card-body py-3 text-center">
            <small class="text-secondary d-block">Total Pagu</small>
            <div class="fs-5 fw-bold">{{ format_rupiah_singkat($statistik['total_pagu']) }}</div>
        </div></div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card h-100"><div class="card-body py-3 text-center">
            <small class="text-secondary d-block">Nilai Kontrak</small>
            <div class="fs-5 fw-bold text-primary">{{ format_rupiah_singkat($statistik['total_kontrak']) }}</div>
        </div></div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card h-100"><div class="card-body py-3 text-center">
            <small class="text-secondary d-block">Paket Selesai</small>
            <div class="fs-4 fw-bold text-success">{{ $statistik['selesai'] }}</div>
        </div></div>
    </div>
</div>

{{-- Daftar paket OPD --}}
<div class="card">
    <div class="card-header bg-white pt-3">
        <h6 class="fw-bold mb-0">Paket Pengadaan {{ $opd->singkatan ?? $opd->nama }}</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead><tr>
                    <th class="ps-3">Paket</th>
                    <th>Metode</th>
                    <th>Pagu</th>
                    <th>Status</th>
                    <th class="pe-3">Penyedia</th>
                </tr></thead>
                <tbody>
                    @forelse ($pakets as $p)
                        <tr>
                            <td class="ps-3">
                                <a href="{{ route('paket.show', $p) }}" class="fw-semibold text-decoration-none">{{ $p->nama_paket }}</a>
                                <small class="text-secondary d-block">{{ $p->kode_paket }} &bull; TA {{ $p->tahun_anggaran }}</small>
                            </td>
                            <td><small>{{ $p->metode_label }}</small></td>
                            <td class="text-nowrap">{{ format_rupiah_singkat($p->pagu) }}</td>
                            <td><span class="badge text-bg-{{ status_badge_class($p->status) }}">{{ $p->status_label }}</span></td>
                            <td class="pe-3"><small>{{ $p->penyedia->nama ?? '-' }}</small></td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-secondary py-5">Belum ada paket untuk OPD ini</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer bg-white">
        {{ $pakets->links('pagination::bootstrap-5') }}
    </div>
</div>
@endsection
