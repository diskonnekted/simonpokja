@extends('layouts.app')

@section('title', $pokja->exists ? 'Edit Pokja' : 'Tambah Pokja')

@section('content')
<div class="d-flex align-items-center gap-2 mb-4">
    <a href="{{ route('pokja.kelola') }}" class="btn btn-light btn-sm"><i class="bi bi-arrow-left"></i></a>
    <div>
        <h4 class="fw-bold mb-0">{{ $pokja->exists ? 'Edit Pokja' : 'Tambah Pokja Baru' }}</h4>
        <p class="text-secondary mb-0 small">{{ $pokja->exists ? $pokja->nama : 'Daftarkan panitia pengadaan baru' }}</p>
    </div>
</div>

<form action="{{ $pokja->exists ? route('pokja.update', $pokja) : route('pokja.store') }}"
      method="POST" class="needs-validation" novalidate>
    @csrf
    @if ($pokja->exists) @method('PUT') @endif

    <div class="row g-3">
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header bg-white pt-3">
                    <h6 class="fw-bold mb-0">Identitas Pokja</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-5">
                            <label class="form-label">Nama Pokja <span class="text-danger">*</span></label>
                            <input type="text" name="nama" value="{{ old('nama', $pokja->nama) }}"
                                   class="form-control @error('nama') is-invalid @enderror"
                                   placeholder="cth: Pokja I" required>
                            @error('nama')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-7">
                            <label class="form-label">Bidang / Lingkup Tugas</label>
                            <input type="text" name="bidang" value="{{ old('bidang', $pokja->bidang) }}"
                                   class="form-control" placeholder="cth: Barang & e-Purchasing">
                        </div>
                        <div class="col-md-7">
                            <label class="form-label">Ketua</label>
                            <input type="text" name="ketua" value="{{ old('ketua', $pokja->ketua) }}"
                                   class="form-control" placeholder="Nama lengkap & gelar">
                        </div>
                        <div class="col-md-5">
                            <label class="form-label">NIP Ketua</label>
                            <input type="text" name="nip_ketua" value="{{ old('nip_ketua', $pokja->nip_ketua) }}"
                                   class="form-control" placeholder="18 digit">
                        </div>
                        <div class="col-md-5">
                            <label class="form-label">Kapasitas Ideal <span class="text-danger">*</span></label>
                            <input type="number" name="kapasitas_ideal" value="{{ old('kapasitas_ideal', $pokja->kapasitas_ideal ?? 5) }}"
                                   class="form-control" min="1" max="20" required>
                            <div class="form-text">Maks. paket aktif per anggota sebelum dianggap overload</div>
                        </div>
                        <div class="col-md-7 d-flex align-items-end">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="aktif" value="1" id="aktif"
                                       {{ old('aktif', $pokja->aktif ?? true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="aktif">Pokja aktif menangani paket</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card">
                <div class="card-header bg-white pt-3 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0">Anggota Panitia</h6>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="btnTambahAnggota">
                        <i class="bi bi-plus-lg"></i> Tambah
                    </button>
                </div>
                <div class="card-body">
                    <div id="daftarAnggota">
                        @forelse (old('anggota', $pokja->anggota ?? []) as $anggota)
                            <div class="input-group input-group-sm mb-2 baris-anggota">
                                <span class="input-group-text"><i class="bi bi-person"></i></span>
                                <input type="text" name="anggota[]" value="{{ $anggota }}" class="form-control" placeholder="Nama anggota...">
                                <button type="button" class="btn btn-outline-danger hapus-anggota"><i class="bi bi-x"></i></button>
                            </div>
                        @empty
                            <div class="input-group input-group-sm mb-2 baris-anggota">
                                <span class="input-group-text"><i class="bi bi-person"></i></span>
                                <input type="text" name="anggota[]" class="form-control" placeholder="Nama anggota...">
                                <button type="button" class="btn btn-outline-danger hapus-anggota"><i class="bi bi-x"></i></button>
                            </div>
                        @endforelse
                    </div>
                    <small class="text-secondary">Biarkan kosong jika belum ada anggota. Minimal 1 baris akan diabaikan bila kosong.</small>
                </div>
            </div>

            <div class="d-grid gap-2 mt-3">
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Simpan Pokja</button>
                <a href="{{ route('pokja.kelola') }}" class="btn btn-light">Batal</a>
            </div>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
    // Tambah baris anggota
    document.getElementById('btnTambahAnggota')?.addEventListener('click', () => {
        const div = document.createElement('div');
        div.className = 'input-group input-group-sm mb-2 baris-anggota';
        div.innerHTML = `
            <span class="input-group-text"><i class="bi bi-person"></i></span>
            <input type="text" name="anggota[]" class="form-control" placeholder="Nama anggota...">
            <button type="button" class="btn btn-outline-danger hapus-anggota"><i class="bi bi-x"></i></button>`;
        document.getElementById('daftarAnggota').appendChild(div);
    });

    // Hapus baris anggota (event delegation)
    document.addEventListener('click', e => {
        if (e.target.closest('.hapus-anggota')) {
            const baris = document.querySelectorAll('.baris-anggota');
            if (baris.length > 1) e.target.closest('.baris-anggota').remove();
        }
    });

    // Validasi bootstrap
    document.querySelectorAll('.needs-validation').forEach(form => {
        form.addEventListener('submit', e => {
            if (!form.checkValidity()) { e.preventDefault(); e.stopPropagation(); }
            form.classList.add('was-validated');
        });
    });
</script>
@endpush
