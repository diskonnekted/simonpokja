@extends('layouts.app')

@section('title', $penyedia->exists ? 'Edit Penyedia' : 'Tambah Penyedia')

@section('content')
<div class="d-flex align-items-center gap-2 mb-4">
    <a href="{{ route('penyedia.index') }}" class="btn btn-light btn-sm"><i class="bi bi-arrow-left"></i></a>
    <div>
        <h4 class="fw-bold mb-0">{{ $penyedia->exists ? 'Edit Penyedia' : 'Tambah Penyedia' }}</h4>
        <p class="text-secondary mb-0 small">{{ $penyedia->exists ? $penyedia->nama : 'Data penyedia barang/jasa baru' }}</p>
    </div>
</div>

<form action="{{ $penyedia->exists ? route('penyedia.update', $penyedia) : route('penyedia.store') }}" method="POST" class="needs-validation" novalidate>
    @csrf
    @if ($penyedia->exists) @method('PUT') @endif

    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header bg-white pt-3">
                    <h6 class="fw-bold mb-0">Identitas Usaha</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label">Nama Penyedia <span class="text-danger">*</span></label>
                            <input type="text" name="nama" value="{{ old('nama', $penyedia->nama) }}" class="form-control @error('nama') is-invalid @enderror" required>
                            @error('nama')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">NPWP</label>
                            <input type="text" name="npwp" value="{{ old('npwp', $penyedia->npwp) }}" class="form-control @error('npwp') is-invalid @enderror">
                            @error('npwp')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">NIB</label>
                            <input type="text" name="nib" value="{{ old('nib', $penyedia->nib) }}" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Jenis Usaha <span class="text-danger">*</span></label>
                            <select name="jenis_usaha" class="form-select" required>
                                @foreach (['kecil' => 'Kecil (UKM)', 'non_kecil' => 'Non Kecil (PT/KV)', 'perseorangan' => 'Perseorangan', 'koperasi' => 'Koperasi'] as $val => $label)
                                    <option value="{{ $val }}" {{ old('jenis_usaha', $penyedia->jenis_usaha ?? 'kecil') == $val ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Kualifikasi <span class="text-danger">*</span></label>
                            <select name="kualifikasi" class="form-select" required>
                                @foreach (['kecil' => 'Kecil', 'menengah' => 'Menengah', 'besar' => 'Besar'] as $val => $label)
                                    <option value="{{ $val }}" {{ old('kualifikasi', $penyedia->kualifikasi ?? 'kecil') == $val ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Direktur / Pemilik</label>
                            <input type="text" name="direktur" value="{{ old('direktur', $penyedia->direktur) }}" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Alamat</label>
                            <input type="text" name="alamat" value="{{ old('alamat', $penyedia->alamat) }}" class="form-control">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header bg-white pt-3">
                    <h6 class="fw-bold mb-0">Kontak &amp; Status</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Telepon</label>
                        <input type="text" name="telepon" value="{{ old('telepon', $penyedia->telepon) }}" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" value="{{ old('email', $penyedia->email) }}" class="form-control">
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="aktif" value="1" id="aktif" {{ old('aktif', $penyedia->aktif ?? true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="aktif">Penyedia aktif</label>
                    </div>
                </div>
            </div>
            <div class="d-grid gap-2 mt-3">
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Simpan</button>
                <a href="{{ route('penyedia.index') }}" class="btn btn-light">Batal</a>
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
