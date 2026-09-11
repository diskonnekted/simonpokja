@extends('layouts.app')

@section('title', 'Detail Penyedia')

@section('content')
<div class="d-flex align-items-center gap-2 mb-4">
    <a href="{{ route('penyedia.index') }}" class="btn btn-light btn-sm mt-1"><i class="bi bi-arrow-left"></i></a>
    <div>
        <div class="d-flex align-items-center gap-2">
            <h4 class="fw-bold mb-0">{{ $penyedia->nama }}</h4>
            <span class="badge {{ $penyedia->aktif ? 'text-bg-success' : 'text-bg-secondary' }}">{{ $penyedia->aktif ? 'Aktif' : 'Nonaktif' }}</span>
        </div>
        <p class="text-secondary mb-0 small">
            NPWP: {{ $penyedia->npwp ?? '-' }} &bull; Kualifikasi {{ ucfirst($penyedia->kualifikasi) }}
            @if ($penyedia->direktur) &bull; {{ $penyedia->direktur }} @endif
        </p>
    </div>
    <a href="{{ route('penyedia.edit', $penyedia) }}" class="btn btn-warning btn-sm ms-auto"><i class="bi bi-pencil me-1"></i>Edit</a>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card h-100"><div class="card-body">
            <h6 class="fw-bold text-secondary small text-uppercase">Kontak</h6>
            <p class="mb-1"><i class="bi bi-telephone me-2 text-secondary"></i>{{ $penyedia->telepon ?? '-' }}</p>
            <p class="mb-1"><i class="bi bi-envelope me-2 text-secondary"></i>{{ $penyedia->email ?? '-' }}</p>
            <p class="mb-0"><i class="bi bi-geo-alt me-2 text-secondary"></i>{{ $penyedia->alamat ?? '-' }}</p>
        </div></div>
    </div>
    <div class="col-md-4">
        <div class="card h-100"><div class="card-body">
            <h6 class="fw-bold text-secondary small text-uppercase">Legalitas</h6>
            <p class="mb-1"><i class="bi bi-receipt me-2 text-secondary"></i>NPWP: {{ $penyedia->npwp ?? '-' }}</p>
            <p class="mb-1"><i class="bi bi-file-earmark-text me-2 text-secondary"></i>NIB: {{ $penyedia->nib ?? '-' }}</p>
            <p class="mb-0"><i class="bi bi-briefcase me-2 text-secondary"></i>Jenis: {{ ucfirst(str_replace('_', ' ', $penyedia->jenis_usaha)) }}</p>
        </div></div>
    </div>
    <div class="col-md-4">
        <div class="card h-100"><div class="card-body">
            <h6 class="fw-bold text-secondary small text-uppercase">Ringkasan</h6>
            <p class="mb-1"><i class="bi bi-box-seam me-2 text-secondary"></i>Total paket: <strong>{{ $pakets->total() }}</strong></p>
            <p class="mb-0"><i class="bi bi-cash-stack me-2 text-secondary"></i>Nilai kontrak:
                <strong>{{ format_rupiah_singkat($pakets->getCollection()->sum('nilai_kontrak')) }}</strong></p>
        </div></div>
    </div>
</div>

<div class="card">
    <div class="card-header bg-white pt-3">
        <h6 class="fw-bold mb-0">Riwayat Paket Ikutsertaan</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead><tr>
                    <th class="ps-3">Paket</th>
                    <th>OPD</th>
                    <th>Tahun</th>
                    <th>Nilai Kontrak</th>
                    <th class="pe-3">Status</th>
                </tr></thead>
                <tbody>
                    @forelse ($pakets as $p)
                        <tr>
                            <td class="ps-3">
                                <a href="{{ route('paket.show', $p) }}" class="fw-semibold text-decoration-none">{{ $p->nama_paket }}</a>
                            </td>
                            <td><small>{{ $p->opd->singkatan ?? '-' }}</small></td>
                            <td>{{ $p->tahun_anggaran }}</td>
                            <td class="text-nowrap">{{ $p->nilai_kontrak ? format_rupiah_singkat($p->nilai_kontrak) : '-' }}</td>
                            <td class="pe-3"><span class="badge text-bg-{{ status_badge_class($p->status) }}">{{ $p->status_label }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-secondary py-5">Penyedia ini belum mengikuti paket apa pun</td></tr>
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
