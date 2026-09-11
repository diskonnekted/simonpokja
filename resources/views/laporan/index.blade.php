@extends('layouts.app')

@section('title', 'Laporan')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2 no-print">
    <div>
        <h4 class="fw-bold mb-1">Laporan Pengadaan</h4>
        <p class="text-secondary mb-0 small">Rekapitulasi dan ekspor data paket pengadaan</p>
    </div>
    <a href="{{ route('laporan.export', request()->only(['tahun', 'opd_id', 'status'])) }}" class="btn btn-success">
        <i class="bi bi-download me-1"></i>Export CSV
    </a>
</div>

<div class="card mb-3 no-print">
    <div class="card-body py-3">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-6 col-md-2">
                <label class="form-label small mb-1">Tahun</label>
                <select name="tahun" class="form-select form-select-sm">
                    @forelse ($tahuns as $t)
                        <option value="{{ $t }}" {{ $tahun == $t ? 'selected' : '' }}>{{ $t }}</option>
                    @empty
                        <option value="{{ $tahun }}">{{ $tahun }}</option>
                    @endforelse
                </select>
            </div>
            <div class="col-12 col-md-5">
                <label class="form-label small mb-1">OPD</label>
                <select name="opd_id" class="form-select form-select-sm">
                    <option value="">Semua OPD</option>
                    @foreach ($opds as $o)
                        <option value="{{ $o->id }}" {{ $opdId == $o->id ? 'selected' : '' }}>{{ $o->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label small mb-1">Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">Semua Status</option>
                    @foreach (['draft' => 'Draft', 'persiapan' => 'Persiapan', 'pemilihan' => 'Pemilihan', 'kontrak' => 'Kontrak', 'pelaksanaan' => 'Pelaksanaan', 'selesai' => 'Selesai', 'batal' => 'Batal'] as $val => $label)
                        <option value="{{ $val }}" {{ $status == $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <button class="btn btn-primary btn-sm"><i class="bi bi-funnel me-1"></i>Tampilkan</button>
            </div>
        </form>
    </div>
</div>

{{-- Ringkasan --}}
<div class="row g-3 mb-4">
    <div class="col-4">
        <div class="card h-100"><div class="card-body py-3 text-center">
            <small class="text-secondary d-block">Jumlah Paket</small>
            <div class="fs-4 fw-bold">{{ number_format($paket->count(), 0, ',', '.') }}</div>
        </div></div>
    </div>
    <div class="col-4">
        <div class="card h-100"><div class="card-body py-3 text-center">
            <small class="text-secondary d-block">Total Pagu</small>
            <div class="fs-5 fw-bold">{{ format_rupiah($totalPagu) }}</div>
        </div></div>
    </div>
    <div class="col-4">
        <div class="card h-100"><div class="card-body py-3 text-center">
            <small class="text-secondary d-block">Total Kontrak</small>
            <div class="fs-5 fw-bold text-primary">{{ format_rupiah($totalKontrak) }}</div>
        </div></div>
    </div>
</div>

{{-- Tabel rekap --}}
<div class="card">
    <div class="card-header bg-white pt-3 d-flex justify-content-between align-items-center">
        <h6 class="fw-bold mb-0">Rekapitulasi Paket {{ $opdId ? \App\Models\Opd::find($opdId)?->nama : 'Semua OPD' }} — TA {{ $tahun }}</h6>
        <button onclick="window.print()" class="btn btn-light btn-sm no-print"><i class="bi bi-printer me-1"></i>Cetak</button>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm table-hover table-card mb-0">
                <thead><tr>
                    <th class="ps-3">#</th>
                    <th>Kode &amp; Nama Paket</th>
                    <th>OPD</th>
                    <th>Metode</th>
                    <th>Pagu</th>
                    <th>Kontrak</th>
                    <th>Deviasi</th>
                    <th class="pe-3">Status</th>
                </tr></thead>
                <tbody>
                    @forelse ($paket as $i => $p)
                        <tr>
                            <td class="ps-3 text-secondary" data-label="No">{{ $i + 1 }}</td>
                            <td data-label="Paket">
                                <span class="fw-semibold d-block" style="max-width: 280px;">{{ $p->nama_paket }}</span>
                                <small class="text-secondary">{{ $p->kode_paket }}</small>
                            </td>
                            <td data-label="OPD"><small>{{ $p->opd->singkatan ?? '-' }}</small></td>
                            <td data-label="Metode"><small>{{ $p->metode_label }}</small></td>
                            <td class="text-nowrap" data-label="Pagu">{{ format_rupiah_singkat($p->pagu) }}</td>
                            <td class="text-nowrap" data-label="Kontrak">{{ $p->nilai_kontrak ? format_rupiah_singkat($p->nilai_kontrak) : '-' }}</td>
                            <td data-label="Deviasi">{{ $p->deviasi !== null ? $p->deviasi . '%' : '-' }}</td>
                            <td class="pe-3" data-label="Status"><span class="badge text-bg-{{ status_badge_class($p->status) }}">{{ $p->status_label }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-secondary py-5">Tidak ada data untuk filter yang dipilih</td></tr>
                    @endforelse
                </tbody>
                @if ($paket->isNotEmpty())
                    <tfoot>
                        <tr class="table-light fw-bold">
                            <td colspan="4" class="ps-3">TOTAL</td>
                            <td>{{ format_rupiah_singkat($totalPagu) }}</td>
                            <td>{{ format_rupiah_singkat($totalKontrak) }}</td>
                            <td colspan="2"></td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>
@endsection
