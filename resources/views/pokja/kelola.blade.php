@extends('layouts.app')

@section('title', 'Kelola Pokja')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <h4 class="fw-bold mb-1"><i class="bi bi-diagram-3 me-2 text-primary"></i>Kelola Pokja / Panitia Pengadaan</h4>
        <p class="text-secondary mb-0 small">Daftarkan pokja-pokja LPSE Kab. Banjarnegara beserta susunan keanggotaannya</p>
    </div>
    <a href="{{ route('pokja.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Tambah Pokja</a>
</div>

<div class="row g-3">
    @forelse ($pokjas as $p)
        <div class="col-md-6 col-xl-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <h6 class="fw-bold mb-0">{{ $p->nama }}</h6>
                            <small class="text-secondary">{{ $p->bidang }}</small>
                        </div>
                        <span class="badge {{ $p->aktif ? 'text-bg-success' : 'text-bg-secondary' }}">{{ $p->aktif ? 'Aktif' : 'Nonaktif' }}</span>
                    </div>

                    <div class="small mb-2">
                        <div><i class="bi bi-person-badge me-2 text-secondary"></i>{{ $p->ketua ?? 'Ketua belum diisi' }}</div>
                        <div><i class="bi bi-people me-2 text-secondary"></i>{{ count($p->anggota ?? []) }} anggota + ketua</div>
                        <div><i class="bi bi-box-seam me-2 text-secondary"></i>{{ $p->pakets_count }} paket ditangani &bull; kapasitas ideal ≤{{ $p->kapasitas_ideal }}/anggota</div>
                    </div>

                    {{-- Daftar anggota --}}
                    @if ($p->anggota)
                        <div class="d-flex flex-wrap gap-1 mb-3">
                            @foreach ($p->anggota as $anggota)
                                <span class="badge bg-primary-subtle text-secondary fw-normal">{{ $anggota }}</span>
                            @endforeach
                        </div>
                    @endif

                    <div class="d-flex gap-2 mt-auto">
                        <a href="{{ route('pokja.show', $p) }}" class="btn btn-sm btn-outline-primary flex-grow-1">
                            <i class="bi bi-graph-up me-1"></i>Kinerja
                        </a>
                        <a href="{{ route('pokja.edit', $p) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
                        <form action="{{ route('pokja.destroy', $p) }}" method="POST"
                              onsubmit="return confirm('Hapus {{ $p->nama }}?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="card"><div class="card-body text-center text-secondary py-5">
                <i class="bi bi-diagram-3 fs-1 d-block mb-2"></i>
                Belum ada pokja terdaftar. Klik "Tambah Pokja" untuk mendaftarkan.
            </div></div>
        </div>
    @endforelse
</div>
@endsection
