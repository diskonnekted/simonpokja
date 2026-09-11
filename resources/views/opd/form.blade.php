@extends('layouts.app')

@section('title', $opd->exists ? 'Edit OPD' : 'Tambah OPD')

@section('content')
<div class="d-flex align-items-center gap-2 mb-4">
    <a href="{{ route('opd.index') }}" class="btn btn-light btn-sm"><i class="bi bi-arrow-left"></i></a>
    <div>
        <h4 class="fw-bold mb-0">{{ $opd->exists ? 'Edit Perangkat Daerah' : 'Tambah Perangkat Daerah' }}</h4>
        <p class="text-secondary mb-0 small">{{ $opd->exists ? $opd->nama : 'OPD baru Kabupaten Banjarnegara' }}</p>
    </div>
</div>

<form action="{{ $opd->exists ? route('opd.update', $opd) : route('opd.store') }}" method="POST" class="needs-validation" novalidate>
    @csrf
    @if ($opd->exists) @method('PUT') @endif

    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header bg-white pt-3">
                    <h6 class="fw-bold mb-0">Identitas OPD</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Kode <span class="text-danger">*</span></label>
                            <input type="text" name="kode" value="{{ old('kode', $opd->kode) }}" class="form-control @error('kode') is-invalid @enderror" required>
                            @error('kode')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Nama OPD <span class="text-danger">*</span></label>
                            <input type="text" name="nama" value="{{ old('nama', $opd->nama) }}" class="form-control @error('nama') is-invalid @enderror" required>
                            @error('nama')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Singkatan</label>
                            <input type="text" name="singkatan" value="{{ old('singkatan', $opd->singkatan) }}" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Kepala</label>
                            <input type="text" name="kepala" value="{{ old('kepala', $opd->kepala) }}" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">NIP Kepala</label>
                            <input type="text" name="nip_kepala" value="{{ old('nip_kepala', $opd->nip_kepala) }}" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Alamat</label>
                            <input type="text" name="alamat" value="{{ old('alamat', $opd->alamat) }}" class="form-control">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header bg-white pt-3">
                    <h6 class="fw-bold mb-0">Kontak</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Telepon</label>
                        <input type="text" name="telepon" value="{{ old('telepon', $opd->telepon) }}" class="form-control">
                    </div>
                    <div>
                        <label class="form-label">Email</label>
                        <input type="email" name="email" value="{{ old('email', $opd->email) }}" class="form-control">
                    </div>
                </div>
            </div>
            <div class="d-grid gap-2 mt-3">
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Simpan</button>
                <a href="{{ route('opd.index') }}" class="btn btn-light">Batal</a>
            </div>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
    document.querySelectorAll('.needs-validation').forEach(form => {
        form.addEventListener('submit', e => {
            if (!form.checkValidity()) { e.preventDefault(); e.stopPropagation(); }
            form.classList.add('was-validated');
        });
    });
</script>
@endpush
