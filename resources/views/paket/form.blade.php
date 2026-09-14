@extends('layouts.app')

@section('title', $paket->exists ? 'Edit Paket' : 'Tambah Paket')

@section('content')
<div class="d-flex align-items-center gap-2 mb-4">
    <a href="{{ $paket->exists ? route('paket.show', $paket) : route('paket.index') }}" class="btn btn-light btn-sm">
        <i class="bi bi-arrow-left"></i>
    </a>
    <div>
        <h4 class="fw-bold mb-0">{{ $paket->exists ? 'Edit Paket' : 'Tambah Paket Pengadaan' }}</h4>
        <p class="text-secondary mb-0 small">{{ $paket->exists ? $paket->kode_paket : 'Daftarkan paket pengadaan baru' }}</p>
    </div>
</div>

<form action="{{ $paket->exists ? route('paket.update', $paket) : route('paket.store') }}"
      method="POST"
      @class(['needs-validation' => true])
      novalidate>
    @csrf
    @if ($paket->exists) @method('PUT') @endif

    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card mb-3">
                <div class="card-header bg-white pt-3">
                    <h6 class="fw-bold mb-0">Informasi Paket</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Kode Paket <span class="text-danger">*</span></label>
                            <input type="text" name="kode_paket" value="{{ old('kode_paket', $paket->kode_paket) }}" class="form-control @error('kode_paket') is-invalid @enderror" required>
                            @error('kode_paket')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Nama Paket <span class="text-danger">*</span></label>
                            <input type="text" name="nama_paket" value="{{ old('nama_paket', $paket->nama_paket) }}" class="form-control @error('nama_paket') is-invalid @enderror" required>
                            @error('nama_paket')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Perangkat Daerah <span class="text-danger">*</span></label>
                            <select name="opd_id" class="form-select @error('opd_id') is-invalid @enderror" required>
                                <option value="">-- Pilih OPD --</option>
                                @foreach ($opds as $o)
                                    <option value="{{ $o->id }}" {{ old('opd_id', $paket->opd_id) == $o->id ? 'selected' : '' }}>{{ $o->nama }}</option>
                                @endforeach
                            </select>
                            @error('opd_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Penyedia</label>
                            <select name="penyedia_id" class="form-select">
                                <option value="">-- Belum ditentukan --</option>
                                @foreach ($penyedias as $pen)
                                    <option value="{{ $pen->id }}" {{ old('penyedia_id', $paket->penyedia_id) == $pen->id ? 'selected' : '' }}>{{ $pen->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Jenis <span class="text-danger">*</span></label>
                            <select name="jenis" class="form-select" required>
                                @foreach (['barang' => 'Barang', 'jasa_konsultansi' => 'Jasa Konsultansi', 'konstruksi' => 'Konstruksi', 'jasa_lainnya' => 'Jasa Lainnya'] as $val => $label)
                                    <option value="{{ $val }}" {{ old('jenis', $paket->jenis) == $val ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Metode <span class="text-danger">*</span></label>
                            <select name="metode" class="form-select" required>
                                @foreach (['tender' => 'Tender', 'seleksi' => 'Seleksi', 'epurchasing' => 'e-Purchasing', 'penunjukan_langsung' => 'Penunjukan Langsung', 'pengadaan_langsung' => 'Pengadaan Langsung', 'swakelola' => 'Swakelola'] as $val => $label)
                                    <option value="{{ $val }}" {{ old('metode', $paket->metode) == $val ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Sumber Dana <span class="text-danger">*</span></label>
                            <select name="sumber_dana" class="form-select" required>
                                @foreach (['apbd' => 'APBD', 'apbn' => 'APBN', 'blm' => 'BLM', 'dak' => 'DAK', 'did' => 'DID', 'lainnya' => 'Lainnya'] as $val => $label)
                                    <option value="{{ $val }}" {{ old('sumber_dana', $paket->sumber_dana ?? 'apbd') == $val ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Lokasi</label>
                            <input type="text" name="lokasi" value="{{ old('lokasi', $paket->lokasi) }}" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Keterangan</label>
                            <input type="text" name="keterangan" value="{{ old('keterangan', $paket->keterangan) }}" class="form-control">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card mb-3">
                <div class="card-header bg-white pt-3">
                    <h6 class="fw-bold mb-0">Anggaran</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Pagu (Rp) <span class="text-danger">*</span></label>
                        <input type="number" name="pagu" value="{{ old('pagu', $paket->pagu) }}" class="form-control @error('pagu') is-invalid @enderror" min="0" required>
                        @error('pagu')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">HPS (Rp)</label>
                        <input type="number" name="hps" value="{{ old('hps', $paket->hps) }}" class="form-control" min="0">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nilai Kontrak (Rp)</label>
                        <input type="number" name="nilai_kontrak" value="{{ old('nilai_kontrak', $paket->nilai_kontrak) }}" class="form-control" min="0">
                    </div>
                    <div>
                        <label class="form-label">Tahun Anggaran <span class="text-danger">*</span></label>
                        <input type="number" name="tahun_anggaran" value="{{ old('tahun_anggaran', $paket->tahun_anggaran ?? date('Y')) }}" class="form-control" min="2020" max="2035" required>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header bg-white pt-3">
                    <h6 class="fw-bold mb-0">Status &amp; Jadwal</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label" for="selectStatus">Status <span class="text-danger">*</span></label>
                        <select name="status" id="selectStatus" class="form-select" required>
                            @foreach (['draft' => 'Draft', 'persiapan' => 'Persiapan', 'pemilihan' => 'Pemilihan', 'kontrak' => 'Kontrak', 'pelaksanaan' => 'Pelaksanaan', 'selesai' => 'Selesai', 'batal' => 'Batal'] as $val => $label)
                                <option value="{{ $val }}" {{ old('status', $paket->status ?? 'draft') == $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label mb-0" for="progressNumber">Progress Fisik (%) <span class="text-danger">*</span></label>
                            <div class="input-group input-group-sm" style="width: 105px;">
                                <input type="number" id="progressNumber" class="form-control text-end fw-bold text-primary font-monospace"
                                       min="0" max="100" value="{{ old('progress', $paket->progress ?? 0) }}">
                                <span class="input-group-text">%</span>
                            </div>
                        </div>
                        <input type="range" name="progress" min="0" max="100" step="1"
                               value="{{ old('progress', $paket->progress ?? 0) }}" class="form-range" id="progressRange">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tanggal Mulai</label>
                        <input type="date" name="tanggal_mulai" value="{{ old('tanggal_mulai', $paket->tanggal_mulai?->format('Y-m-d')) }}" class="form-control">
                    </div>
                    <div>
                        <label class="form-label">Tanggal Selesai</label>
                        <input type="date" name="tanggal_selesai" value="{{ old('tanggal_selesai', $paket->tanggal_selesai?->format('Y-m-d')) }}" class="form-control">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex gap-2 mt-4">
        <button type="submit" class="btn btn-primary px-4">
            <i class="bi bi-check-lg me-1"></i>{{ $paket->exists ? 'Simpan Perubahan' : 'Simpan Paket' }}
        </button>
        <a href="{{ route('paket.index') }}" class="btn btn-light">Batal</a>
    </div>
</form>
@endsection

@push('scripts')
<script>
    // Pemetaan standar status tahapan ke angka progres fisik (%)
    const PETA_STATUS_PROGRES = {
        draft: 0,
        persiapan: 10,
        pemilihan: 30,
        kontrak: 50,
        pelaksanaan: 70,
        selesai: 100,
        batal: 0
    };

    const selStatus = document.getElementById('selectStatus');
    const range = document.getElementById('progressRange');
    const num = document.getElementById('progressNumber');

    function setProgressVal(v) {
        v = Math.min(100, Math.max(0, parseInt(v, 10) || 0));
        if (range) range.value = v;
        if (num) num.value = v;
    }

    range?.addEventListener('input', () => {
        if (num) num.value = range.value;
    });

    num?.addEventListener('input', () => {
        if (range) range.value = num.value;
    });

    // Otomatis sinkronisasi nilai progress saat status dipilih
    selStatus?.addEventListener('change', function () {
        const s = selStatus.value;
        if (s in PETA_STATUS_PROGRES) {
            setProgressVal(PETA_STATUS_PROGRES[s]);
            if (num) {
                num.classList.add('border-primary');
                setTimeout(() => num.classList.remove('border-primary'), 1200);
            }
        }
    });

    // Bootstrap validation
    document.querySelectorAll('.needs-validation').forEach(form => {
        form.addEventListener('submit', e => {
            if (!form.checkValidity()) { e.preventDefault(); e.stopPropagation(); }
            form.classList.add('was-validated');
        });
    });
</script>
@endpush
