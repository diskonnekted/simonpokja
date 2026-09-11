@extends('layouts.app')

@section('title', 'Perangkat Daerah')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <h4 class="fw-bold mb-1">Perangkat Daerah (OPD)</h4>
        <p class="text-secondary mb-0 small">Daftar Organisasi Perangkat Daerah Kabupaten Banjarnegara</p>
    </div>
    <a href="{{ route('opd.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Tambah OPD</a>
</div>

<div class="card mb-3">
    <div class="card-body py-3">
        <form method="GET" class="row g-2">
            <div class="col-12 col-md-6">
                <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="Cari nama, singkatan, atau kode OPD...">
            </div>
            <div class="col-auto">
                <button class="btn btn-primary btn-sm"><i class="bi bi-search"></i></button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead><tr>
                    <th class="ps-3">Kode &amp; Nama OPD</th>
                    <th>Kepala</th>
                    <th>Kontak</th>
                    <th class="text-center">Jumlah Paket</th>
                    <th class="pe-3 text-end">Aksi</th>
                </tr></thead>
                <tbody>
                    @forelse ($opds as $opd)
                        <tr>
                            <td class="ps-3">
                                <a href="{{ route('opd.show', $opd) }}" class="fw-semibold text-decoration-none d-block">{{ $opd->nama }}</a>
                                <small class="text-secondary">{{ $opd->kode }} @if($opd->singkatan) ({{ $opd->singkatan }}) @endif</small>
                            </td>
                            <td><small>{{ $opd->kepala ?? '-' }}</small></td>
                            <td>
                                <small class="d-block">{{ $opd->telepon ?? '-' }}</small>
                                <small class="text-secondary">{{ $opd->email ?? '' }}</small>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-primary-subtle text-primary">{{ $opd->pakets_count }}</span>
                            </td>
                            <td class="pe-3 text-end text-nowrap">
                                <a href="{{ route('opd.edit', $opd) }}" class="btn btn-sm btn-light"><i class="bi bi-pencil"></i></a>
                                <form action="{{ route('opd.destroy', $opd) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus OPD ini? Semua paket terkait ikut terhapus.')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-light text-danger"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-secondary py-5">
                            <i class="bi bi-inbox fs-1 d-block mb-2"></i>Tidak ada OPD ditemukan
                        </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer bg-white">
        {{ $opds->links('pagination::bootstrap-5') }}
    </div>
</div>
@endsection
