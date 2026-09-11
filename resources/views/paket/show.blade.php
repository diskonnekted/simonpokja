@extends('layouts.app')

@section('title', 'Detail Paket')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-start mb-4 gap-2">
    <div class="d-flex gap-2">
        <a href="{{ route('paket.index') }}" class="btn btn-light btn-sm mt-1"><i class="bi bi-arrow-left"></i></a>
        <div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <h4 class="fw-bold mb-0">{{ $paket->nama_paket }}</h4>
                <span class="badge text-bg-{{ status_badge_class($paket->status) }}">{{ $paket->status_label }}</span>
            </div>
            <p class="text-secondary mb-0 small">
                {{ $paket->kode_paket }} &bull; {{ $paket->opd->nama ?? '-' }} &bull; TA {{ $paket->tahun_anggaran }}
            </p>
        </div>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('paket.edit', $paket) }}" class="btn btn-warning btn-sm"><i class="bi bi-pencil me-1"></i>Edit</a>
        <form action="{{ route('paket.destroy', $paket) }}" method="POST" onsubmit="return confirm('Hapus paket ini?')">
            @csrf @method('DELETE')
            <button class="btn btn-outline-danger btn-sm"><i class="bi bi-trash"></i></button>
        </form>
    </div>
</div>

{{-- ===== Ringkasan Anggaran ===== --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="card h-100"><div class="card-body py-3">
            <small class="text-secondary">Pagu Anggaran</small>
            <div class="fw-bold fs-5">{{ format_rupiah($paket->pagu) }}</div>
        </div></div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card h-100"><div class="card-body py-3">
            <small class="text-secondary">HPS</small>
            <div class="fw-bold fs-5">{{ $paket->hps ? format_rupiah($paket->hps) : '-' }}</div>
        </div></div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card h-100"><div class="card-body py-3">
            <small class="text-secondary">Nilai Kontrak</small>
            <div class="fw-bold fs-5 text-primary">{{ $paket->nilai_kontrak ? format_rupiah($paket->nilai_kontrak) : '-' }}</div>
        </div></div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card h-100"><div class="card-body py-3">
            <small class="text-secondary">Deviasi</small>
            <div class="fw-bold fs-5 {{ ($paket->deviasi ?? 0) < 0 ? 'text-danger' : 'text-success' }}">
                {{ $paket->deviasi !== null ? $paket->deviasi . '%' : '-' }}
            </div>
        </div></div>
    </div>
</div>

{{-- ===== Progress Bar ===== --}}
<div class="card mb-4">
    <div class="card-body">
        <div class="d-flex justify-content-between mb-2">
            <span class="fw-semibold">Progres Pelaksanaan</span>
            <span class="fw-bold">{{ $paket->progress }}%</span>
        </div>
        <div class="progress" style="height: 10px;">
            <div class="progress-bar {{ $paket->progress >= 100 ? 'bg-success' : 'bg-primary' }}" style="width: {{ $paket->progress }}%"></div>
        </div>
        <div class="row mt-3 small text-secondary">
            <div class="col-md-3"><i class="bi bi-calendar-event me-1"></i>Mulai: {{ format_tanggal_id($paket->tanggal_mulai) }}</div>
            <div class="col-md-3"><i class="bi bi-calendar-check me-1"></i>Target: {{ format_tanggal_id($paket->tanggal_selesai) }}</div>
            <div class="col-md-3"><i class="bi bi-geo-alt me-1"></i>Lokasi: {{ $paket->lokasi ?? '-' }}</div>
            <div class="col-md-3"><i class="bi bi-person-badge me-1"></i>Penyedia: {{ $paket->penyedia->nama ?? 'Belum ditentukan' }}</div>
        </div>
    </div>
</div>

<div class="row g-3">
    {{-- ===== Timeline Tahapan ===== --}}
    <div class="col-lg-7">
        <div class="card h-100">
            <div class="card-header bg-white pt-3">
                <h6 class="fw-bold mb-0"><i class="bi bi-list-check me-2"></i>Tahapan Pengadaan</h6>
            </div>
            <div class="card-body">
                <div class="timeline">
                    @forelse ($paket->tahapans as $tahap)
                        <div class="d-flex gap-3 pb-3 position-relative">
                            <div class="flex-shrink-0 d-flex flex-column align-items-center">
                                <span class="rounded-circle d-flex align-items-center justify-content-center
                                    {{ $tahap->status == 'selesai' ? 'bg-success' : ($tahap->status == 'proses' ? 'bg-primary' : 'bg-secondary-subtle text-secondary') }}"
                                    style="width: 34px; height: 34px;">
                                    <i class="bi {{ $tahap->status == 'selesai' ? 'bi-check-lg text-white' : ($tahap->status == 'proses' ? 'bi-arrow-repeat text-white' : 'bi-circle') }}"></i>
                                </span>
                                @if (!$loop->last)
                                    <div class="flex-grow-1 my-1" style="width: 2px; background: #e2e8f0;"></div>
                                @endif
                            </div>
                            <div class="flex-grow-1 pb-2">
                                <div class="d-flex justify-content-between flex-wrap gap-1">
                                    <span class="fw-semibold">{{ $loop->iteration }}. {{ $tahap->nama_tahap }}</span>
                                    <form action="{{ route('tahapan.update', $tahap) }}" method="POST" class="d-flex gap-1 align-items-center">
                                        @csrf
                                        <select name="status" class="form-select form-select-sm" style="width: auto;" onchange="this.form.submit()">
                                            @foreach (['belum' => 'Belum', 'proses' => 'Proses', 'selesai' => 'Selesai'] as $val => $label)
                                                <option value="{{ $val }}" {{ $tahap->status == $val ? 'selected' : '' }}>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </form>
                                </div>
                                <small class="text-secondary">
                                    Rencana: {{ format_tanggal_id($tahap->tanggal_rencana) }}
                                    @if ($tahap->tanggal_aktual)
                                        &bull; Aktual: {{ format_tanggal_id($tahap->tanggal_aktual) }}
                                    @endif
                                </small>
                            </div>
                        </div>
                    @empty
                        <p class="text-secondary text-center py-4 mb-0">Belum ada tahapan</p>
                    @endforelse
                </div>

                <hr>
                <form action="{{ route('paket.tahapan.store', $paket) }}" method="POST" class="row g-2">
                    @csrf
                    <div class="col-7">
                        <input type="text" name="nama_tahap" class="form-control form-control-sm" placeholder="Nama tahap baru..." required>
                    </div>
                    <div class="col-3">
                        <input type="date" name="tanggal_rencana" class="form-control form-control-sm">
                    </div>
                    <div class="col-2">
                        <button class="btn btn-primary btn-sm w-100"><i class="bi bi-plus-lg"></i></button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ===== Evaluasi ===== --}}
    <div class="col-lg-5">
        <div class="card h-100">
            <div class="card-header bg-white pt-3">
                <h6 class="fw-bold mb-0"><i class="bi bi-clipboard-check me-2"></i>Hasil Evaluasi</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead><tr>
                            <th class="ps-3">Penyedia</th><th>Jenis</th><th>Skor</th><th class="pe-3">Hasil</th>
                        </tr></thead>
                        <tbody>
                            @forelse ($paket->evaluasis as $ev)
                                <tr>
                                    <td class="ps-3"><small>{{ $ev->penyedia->nama ?? '-' }}</small></td>
                                    <td><small class="text-capitalize">{{ $ev->jenis }}</small></td>
                                    <td><span class="badge bg-primary-subtle text-primary">{{ $ev->skor }}</span></td>
                                    <td class="pe-3">
                                        <span class="badge {{ $ev->hasil == 'lolos' ? 'text-bg-success' : ($ev->hasil == 'gugur' ? 'text-bg-danger' : 'text-bg-secondary') }}">
                                            {{ ucfirst($ev->hasil) }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-secondary py-4">Belum ada evaluasi</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-white">
                <form action="{{ route('paket.evaluasi.store', $paket) }}" method="POST" class="row g-2">
                    @csrf
                    <div class="col-12">
                        <select name="penyedia_id" class="form-select form-select-sm" required>
                            <option value="">-- Pilih penyedia --</option>
                            @foreach (\App\Models\Penyedia::where('aktif', true)->orderBy('nama')->get() as $pen)
                                <option value="{{ $pen->id }}">{{ $pen->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-4">
                        <select name="jenis" class="form-select form-select-sm">
                            @foreach (['kualifikasi', 'teknis', 'harga'] as $j)
                                <option value="{{ $j }}" {{ $j == 'kualifikasi' ? 'selected' : '' }}>{{ ucfirst($j) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-4">
                        <input type="number" name="skor" class="form-control form-control-sm" placeholder="Skor" min="0" max="100" step="0.01" required>
                    </div>
                    <div class="col-4">
                        <select name="hasil" class="form-select form-select-sm">
                            @foreach (['lolos', 'gugur', 'menunggu'] as $h)
                                <option value="{{ $h }}">{{ ucfirst($h) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12">
                        <button class="btn btn-primary btn-sm w-100"><i class="bi bi-plus-lg me-1"></i>Tambah Evaluasi</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
