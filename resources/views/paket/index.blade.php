@extends('layouts.app')

@section('title', 'Paket Pengadaan')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <h4 class="fw-bold mb-1">Paket Pengadaan</h4>
        <p class="text-secondary mb-0 small">Kelola dan pantau seluruh paket pengadaan barang/jasa</p>
    </div>
    <a href="{{ route('paket.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i>Tambah Paket
    </a>
</div>

{{-- ===== Filter ===== --}}
<div class="card mb-3">
    <div class="card-body py-3">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-12 col-md-3">
                <label class="form-label small mb-1">Pencarian</label>
                <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="Nama / kode paket...">
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small mb-1">Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">Semua</option>
                    @foreach ($statuses as $val => $label)
                        <option value="{{ $val }}" {{ request('status') == $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label small mb-1">OPD</label>
                <select name="opd_id" class="form-select form-select-sm">
                    <option value="">Semua OPD</option>
                    @foreach ($opds as $o)
                        <option value="{{ $o->id }}" {{ request('opd_id') == $o->id ? 'selected' : '' }}>{{ $o->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small mb-1">Metode</label>
                <select name="metode" class="form-select form-select-sm">
                    <option value="">Semua</option>
                    @foreach ($metodes as $val => $label)
                        <option value="{{ $val }}" {{ request('metode') == $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small mb-1">Tahun</label>
                <div class="d-flex gap-1">
                    <select name="tahun" class="form-select form-select-sm">
                        @forelse ($tahuns as $t)
                            <option value="{{ $t }}" {{ request('tahun', date('Y')) == $t ? 'selected' : '' }}>{{ $t }}</option>
                        @empty
                            <option value="{{ date('Y') }}">{{ date('Y') }}</option>
                        @endforelse
                    </select>
                    <button class="btn btn-primary btn-sm text-nowrap"><i class="bi bi-search"></i></button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- ===== Tabel ===== --}}
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-card mb-0">
                <thead><tr>
                    <th class="ps-3">Kode &amp; Nama Paket</th>
                    <th>OPD</th>
                    <th>Pagu</th>
                    <th>Nilai Kontrak</th>
                    <th>Status</th>
                    <th class="text-center" style="min-width: 120px;">Progress</th>
                    <th class="pe-3 text-end">Aksi</th>
                </tr></thead>
                <tbody>
                    @forelse ($paket as $p)
                        <tr>
                            <td class="ps-3" data-label="Paket">
                                <a href="{{ route('paket.show', $p) }}" class="fw-semibold text-decoration-none d-block" style="max-width: 300px;">{{ $p->nama_paket }}</a>
                                <small class="text-secondary">{{ $p->kode_paket }}</small>
                            </td>
                            <td data-label="OPD"><small>{{ $p->opd->singkatan ?? '-' }}</small></td>
                            <td class="text-nowrap" data-label="Pagu">{{ format_rupiah_singkat($p->pagu) }}</td>
                            <td class="text-nowrap" data-label="Kontrak">{{ $p->nilai_kontrak ? format_rupiah_singkat($p->nilai_kontrak) : '-' }}</td>
                            <td data-label="Status">
                                <span class="badge text-bg-{{ status_badge_class($p->status) }}">{{ $p->status_label }}</span>
                            </td>
                            <td data-label="Progress">
                                <div class="d-flex align-items-center gap-2 justify-content-center">
                                    <div class="progress flex-grow-1" style="height: 6px;">
                                        <div class="progress-bar {{ $p->progress >= 100 ? 'bg-success' : '' }}" style="width: {{ $p->progress }}%"></div>
                                    </div>
                                    <small class="text-secondary" style="width: 34px;">{{ $p->progress }}%</small>
                                </div>
                            </td>
                            <td class="pe-3 text-end text-nowrap" data-label="Aksi">
                                <a href="{{ route('paket.edit', $p) }}" class="btn btn-sm btn-light" title="Edit"><i class="bi bi-pencil"></i></a>
                                <form action="{{ route('paket.destroy', $p) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus paket ini?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-light text-danger" title="Hapus"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-secondary py-5">
                            <i class="bi bi-inbox fs-1 d-block mb-2"></i>Tidak ada paket yang cocok dengan filter
                        </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer bg-white d-flex justify-content-between align-items-center">
        <small class="text-secondary">Menampilkan {{ $paket->count() }} dari {{ $paket->total() }} paket</small>
        {{ $paket->links('pagination::bootstrap-5') }}
    </div>
</div>
@endsection
