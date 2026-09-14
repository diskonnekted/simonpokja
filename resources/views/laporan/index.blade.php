@extends('layouts.app')

@section('title', 'Laporan Pengadaan')

@section('breadcrumb')
<ul class="breadcrumb-links">
    <li><a href="{{ route('dashboard') }}"><i class="bi bi-house-door me-1"></i>Beranda</a></li>
    <li class="sep"><i class="bi bi-chevron-right"></i></li>
    <li class="active">Laporan Pengadaan</li>
    <li class="sep"><i class="bi bi-chevron-right"></i></li>
    <li class="active">TA {{ $tahun }}</li>
</ul>
<div class="text-secondary small font-monospace d-none d-sm-block">
    <i class="bi bi-file-earmark-bar-graph me-1"></i>Rekapitulasi {{ $paket->count() }} Paket
</div>
@endsection

@section('content')
{{-- ===== Kop Surat Cetak Resmi (Khusus Mode Cetak) ===== --}}
<div class="d-none d-print-block text-center mb-4 pb-3 border-bottom border-2 border-dark">
    <h5 class="fw-bold mb-0 text-uppercase" style="letter-spacing: 1px;">Pemerintah Kabupaten Banjarnegara</h5>
    <h6 class="fw-bold mb-1 text-uppercase" style="letter-spacing: 0.5px;">Sekretariat Daerah — Bagian Pengadaan Barang dan Jasa</h6>
    <p class="small mb-0 text-secondary">Jl. Diponegoro No. 1 Banjarnegara, Jawa Tengah 53412 &bull; Telp. (0286) 591218 &bull; lpse.banjarnegarakab.go.id</p>
    <div class="mt-3 fw-bold text-decoration-underline" style="font-size: 1.05rem;">LAPORAN REKAPITULASI PELAKSANAAN PENGADAAN BARANG DAN JASA</div>
    <div class="small mt-1">Tahun Anggaran {{ $tahun }} &bull; Perangkat Daerah: {{ $opdId ? \App\Models\Opd::find($opdId)?->nama : 'Semua Perangkat Daerah' }}</div>
</div>

{{-- ===== Header Halaman (Layar) ===== --}}
<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2 no-print">
    <div>
        <h4 class="fw-bold mb-1">Laporan Pengadaan</h4>
        <p class="text-secondary mb-0 small">Rekapitulasi agregat per OPD dan rincian data paket pengadaan</p>
    </div>
    <div class="d-flex gap-2">
        <button onclick="window.print()" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-printer me-1"></i>Cetak Laporan
        </button>
        <a href="{{ route('laporan.export', request()->only(['tahun', 'opd_id', 'status'])) }}" class="btn btn-success btn-sm">
            <i class="bi bi-download me-1"></i>Export CSV
        </a>
    </div>
</div>

{{-- ===== Filter Form (Layar) ===== --}}
<div class="card mb-3 no-print">
    <div class="card-body py-3">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-6 col-md-2">
                <label class="form-label small mb-1">Tahun Anggaran</label>
                <select name="tahun" class="form-select form-select-sm">
                    @forelse ($tahuns as $t)
                        <option value="{{ $t }}" {{ $tahun == $t ? 'selected' : '' }}>{{ $t }}</option>
                    @empty
                        <option value="{{ $tahun }}">{{ $tahun }}</option>
                    @endforelse
                </select>
            </div>
            <div class="col-12 col-md-5">
                <label class="form-label small mb-1">Perangkat Daerah (OPD)</label>
                <select name="opd_id" class="form-select form-select-sm">
                    <option value="">Semua OPD</option>
                    @foreach ($opds as $o)
                        <option value="{{ $o->id }}" {{ $opdId == $o->id ? 'selected' : '' }}>{{ $o->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label small mb-1">Status Paket</label>
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

@php
    $totalHemat = ($totalPagu > 0 && $totalKontrak > 0) ? max(0, $totalPagu - $totalKontrak) : 0;
    $persenHemat = $totalPagu > 0 && $totalHemat > 0 ? round(($totalHemat / $totalPagu) * 100, 1) : 0;
@endphp

{{-- ===== Ringkasan Eksekutif ===== --}}
<div class="row g-2 mb-3">
    <div class="col-6 col-md-3">
        <div class="card h-100"><div class="card-body py-2 px-3">
            <small class="text-secondary d-block">Jumlah Paket</small>
            <div class="fs-5 fw-bold mb-0">{{ number_format($paket->count(), 0, ',', '.') }} <small class="text-secondary fw-normal fs-6">paket</small></div>
        </div></div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card h-100"><div class="card-body py-2 px-3">
            <small class="text-secondary d-block">Total Pagu</small>
            <div class="fs-5 fw-bold mb-0">{{ format_rupiah_singkat($totalPagu) }}</div>
        </div></div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card h-100"><div class="card-body py-2 px-3">
            <small class="text-secondary d-block">Total Nilai Kontrak</small>
            <div class="fs-5 fw-bold text-primary mb-0">{{ format_rupiah_singkat($totalKontrak) }}</div>
        </div></div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card h-100 border-success-subtle"><div class="card-body py-2 px-3">
            <small class="text-secondary d-block">Efisiensi (Penghematan)</small>
            <div class="fs-5 fw-bold text-success mb-0">
                {{ format_rupiah_singkat($totalHemat) }}
                @if ($persenHemat > 0)<small class="badge text-bg-success ms-1" style="font-size: 0.7rem;">{{ $persenHemat }}%</small>@endif
            </div>
        </div></div>
    </div>
</div>

{{-- ===== R5: Ringkasan per Perangkat Daerah (OPD) ===== --}}
@if ($ringkasanPerOpd->isNotEmpty())
    <div class="card mb-3">
        <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center">
            <h6 class="fw-bold mb-0 small text-uppercase" style="letter-spacing: 0.5px;">
                <i class="bi bi-building me-1 text-primary"></i>Ringkasan per Perangkat Daerah (OPD)
            </h6>
            <small class="text-secondary">{{ $ringkasanPerOpd->count() }} OPD terdata</small>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle mb-0">
                    <thead class="table-light"><tr>
                        <th class="ps-3">Perangkat Daerah</th>
                        <th class="text-center">Paket</th>
                        <th>Total Pagu</th>
                        <th>Total Kontrak</th>
                        <th>Efisiensi</th>
                        <th class="text-center">Distribusi Status</th>
                        <th class="pe-3 text-end">Rata Progres</th>
                    </tr></thead>
                    <tbody>
                        @foreach ($ringkasanPerOpd as $r)
                            <tr>
                                <td class="ps-3">
                                    <div class="fw-semibold small text-truncate" style="max-width: 250px;" title="{{ $r['nama'] }}">{{ $r['nama'] }}</div>
                                    <small class="text-secondary">{{ $r['singkatan'] }}</small>
                                </td>
                                <td class="text-center font-monospace">{{ $r['jumlah_paket'] }}</td>
                                <td class="text-nowrap">{{ format_rupiah_singkat($r['total_pagu']) }}</td>
                                <td class="text-nowrap">{{ $r['total_kontrak'] > 0 ? format_rupiah_singkat($r['total_kontrak']) : '-' }}</td>
                                <td class="text-nowrap">
                                    @if ($r['efisiensi'] > 0)
                                        <span class="text-success fw-semibold">{{ format_rupiah_singkat($r['efisiensi']) }}</span>
                                        <small class="text-success" style="font-size: 0.72rem;">({{ $r['persen_efisiensi'] }}%)</small>
                                    @else
                                        <span class="text-secondary">-</span>
                                    @endif
                                </td>
                                <td class="text-center" style="min-width: 140px;">
                                    <div class="d-flex justify-content-center gap-1">
                                        @if ($r['selesai'] > 0)
                                            <span class="badge text-bg-success" style="font-size: 0.68rem;" title="{{ $r['selesai'] }} Selesai">{{ $r['selesai'] }} Selesai</span>
                                        @endif
                                        @if ($r['berjalan'] > 0)
                                            <span class="badge text-bg-primary" style="font-size: 0.68rem;" title="{{ $r['berjalan'] }} Berjalan">{{ $r['berjalan'] }} Aktif</span>
                                        @endif
                                        @if ($r['batal'] > 0)
                                            <span class="badge text-bg-danger" style="font-size: 0.68rem;" title="{{ $r['batal'] }} Batal">{{ $r['batal'] }} Batal</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="pe-3 text-end font-monospace">
                                    <span class="fw-semibold {{ $r['rata_progres'] >= 80 ? 'text-success' : 'text-primary' }}">{{ $r['rata_progres'] }}%</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endif

{{-- ===== Tabel Rincian Rekapitulasi Paket ===== --}}
<div class="card mb-4">
    <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center">
        <h6 class="fw-bold mb-0 small text-uppercase" style="letter-spacing: 0.5px;">
            <i class="bi bi-table me-1 text-primary"></i>Rincian Paket Pengadaan {{ $opdId ? '— ' . (\App\Models\Opd::find($opdId)?->nama ?? '') : '' }}
        </h6>
        <small class="text-secondary font-monospace">{{ $paket->count() }} Data</small>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm table-hover table-card mb-0">
                <thead class="table-light"><tr>
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
                                <span class="fw-semibold d-block text-truncate" style="max-width: 280px;" title="{{ $p->nama_paket }}">{{ $p->nama_paket }}</span>
                                <small class="text-secondary font-monospace">{{ $p->kode_paket }}</small>
                            </td>
                            <td data-label="OPD"><small>{{ $p->opd->singkatan ?? '-' }}</small></td>
                            <td data-label="Metode"><small>{{ $p->metode_label }}</small></td>
                            <td class="text-nowrap" data-label="Pagu">{{ format_rupiah_singkat($p->pagu) }}</td>
                            <td class="text-nowrap" data-label="Kontrak">{{ $p->nilai_kontrak ? format_rupiah_singkat($p->nilai_kontrak) : '-' }}</td>
                            <td data-label="Deviasi">
                                @if ($p->deviasi !== null)
                                    <span class="{{ $p->deviasi < 0 ? 'text-danger fw-semibold' : 'text-success' }}">{{ $p->deviasi }}%</span>
                                @else
                                    <span class="text-secondary">-</span>
                                @endif
                            </td>
                            <td class="pe-3" data-label="Status">
                                <span class="badge text-bg-{{ status_badge_class($p->status) }}">{{ $p->status_label }}</span>
                                <small class="text-secondary d-block mt-0 font-monospace" style="font-size: 0.7rem;"><i class="bi bi-arrow-right-short text-primary"></i>{{ $p->next_action }}</small>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-secondary py-5">
                            <i class="bi bi-file-earmark-x fs-1 d-block mb-2 text-muted"></i>
                            <div class="fw-semibold text-dark mb-1">Tidak Ada Data Paket</div>
                            <small class="text-secondary">Tidak ada data paket pengadaan untuk filter tahun {{ $tahun }} dan OPD yang dipilih. Silakan ubah filter pencarian di atas.</small>
                        </td></tr>
                    @endforelse
                </tbody>
                @if ($paket->isNotEmpty())
                    <tfoot>
                        <tr class="table-light fw-bold">
                            <td colspan="4" class="ps-3 text-uppercase">TOTAL</td>
                            <td>{{ format_rupiah_singkat($totalPagu) }}</td>
                            <td>{{ format_rupiah_singkat($totalKontrak) }}</td>
                            <td colspan="2" class="text-success text-end pe-3">
                                @if ($totalHemat > 0)
                                    Hemat {{ format_rupiah_singkat($totalHemat) }} ({{ $persenHemat }}%)
                                @endif
                            </td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>

{{-- ===== Lembar Pengesahan / Tanda Tangan (Khusus Mode Cetak) ===== --}}
<div class="d-none d-print-block mt-5 pt-4">
    <div class="row">
        <div class="col-6 text-center">
            <p class="mb-5 small">Mengetahui,<br><strong>Kepala Bagian Pengadaan Barang dan Jasa<br>Setda Kabupaten Banjarnegara</strong></p>
            <p class="fw-bold text-decoration-underline mb-0">___________________________</p>
            <p class="small text-secondary">NIP. ..............................................</p>
        </div>
        <div class="col-6 text-center">
            <p class="mb-5 small">Banjarnegara, {{ now()->translatedFormat('d F Y') }}<br><strong>Pejabat Pembuat Laporan / Operator</strong></p>
            <p class="fw-bold text-decoration-underline mb-0">{{ auth()->user()->name }}</p>
            <p class="small text-secondary">NIP. ..............................................</p>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
@media print {
    body { font-size: 11px !important; color: #000 !important; background: #fff !important; }
    .card { border: none !important; box-shadow: none !important; }
    .table { font-size: 10px !important; width: 100% !important; }
    .table th, .table td { padding: 4px 6px !important; border: 1px solid #dee2e6 !important; }
    .page-break { page-break-before: always; }
}
</style>
@endpush
