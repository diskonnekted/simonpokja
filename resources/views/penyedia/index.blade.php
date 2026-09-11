@extends('layouts.app')

@section('title', 'Penyedia')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <h4 class="fw-bold mb-1">Penyedia Barang/Jasa</h4>
        <p class="text-secondary mb-0 small">Database penyedia yang berpartisipasi dalam pengadaan</p>
    </div>
    <a href="{{ route('penyedia.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Tambah Penyedia</a>
</div>

<div class="card mb-3">
    <div class="card-body py-3">
        <form method="GET" class="row g-2">
            <div class="col-12 col-md-5">
                <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="Cari nama atau NPWP...">
            </div>
            <div class="col-6 col-md-3">
                <select name="kualifikasi" class="form-select form-select-sm">
                    <option value="">Semua Kualifikasi</option>
                    @foreach (['kecil' => 'Kecil', 'menengah' => 'Menengah', 'besar' => 'Besar'] as $val => $label)
                        <option value="{{ $val }}" {{ request('kualifikasi') == $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
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
                    <th class="ps-3">Nama Penyedia</th>
                    <th>NPWP</th>
                    <th>Kualifikasi</th>
                    <th>Kontak</th>
                    <th class="text-center">Paket</th>
                    <th class="text-center">Status</th>
                    <th class="pe-3 text-end">Aksi</th>
                </tr></thead>
                <tbody>
                    @forelse ($penyedias as $pen)
                        <tr>
                            <td class="ps-3">
                                <a href="{{ route('penyedia.show', $pen) }}" class="fw-semibold text-decoration-none">{{ $pen->nama }}</a>
                                <small class="text-secondary d-block">{{ $pen->direktur ?? '' }}</small>
                            </td>
                            <td><small>{{ $pen->npwp ?? '-' }}</small></td>
                            <td>
                                <span class="badge {{ $pen->kualifikasi == 'besar' ? 'text-bg-dark' : ($pen->kualifikasi == 'menengah' ? 'text-bg-info' : 'text-bg-light text-dark') }}">
                                    {{ ucfirst($pen->kualifikasi) }}
                                </span>
                            </td>
                            <td>
                                <small class="d-block">{{ $pen->telepon ?? '-' }}</small>
                                <small class="text-secondary">{{ $pen->email ?? '' }}</small>
                            </td>
                            <td class="text-center"><span class="badge bg-primary-subtle text-primary">{{ $pen->pakets_count }}</span></td>
                            <td class="text-center">
                                <span class="badge {{ $pen->aktif ? 'text-bg-success' : 'text-bg-secondary' }}">{{ $pen->aktif ? 'Aktif' : 'Nonaktif' }}</span>
                            </td>
                            <td class="pe-3 text-end text-nowrap">
                                <a href="{{ route('penyedia.edit', $pen) }}" class="btn btn-sm btn-light"><i class="bi bi-pencil"></i></a>
                                <form action="{{ route('penyedia.destroy', $pen) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus penyedia ini?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-light text-danger"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-secondary py-5">
                            <i class="bi bi-inbox fs-1 d-block mb-2"></i>Tidak ada penyedia ditemukan
                        </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer bg-white">
        {{ $penyedias->links('pagination::bootstrap-5') }}
    </div>
</div>
@endsection
