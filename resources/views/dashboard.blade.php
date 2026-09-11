@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
{{-- ===== Filter Tahun ===== --}}
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <h4 class="fw-bold mb-1">Dashboard Pengadaan</h4>
        <p class="text-secondary mb-0 small">Rekapitulasi paket pengadaan barang/jasa Kabupaten Banjarnegara</p>
    </div>
    <form method="GET" class="d-flex gap-2">
        <select name="tahun" class="form-select form-select-sm" style="width: 130px;">
            @forelse ($tahuns as $t)
                <option value="{{ $t }}" {{ $t == $tahun ? 'selected' : '' }}>Tahun {{ $t }}</option>
            @empty
                <option value="{{ $tahun }}">Tahun {{ $tahun }}</option>
            @endforelse
        </select>
        <button class="btn btn-primary btn-sm"><i class="bi bi-funnel me-1"></i>Filter</button>
    </form>
</div>

{{-- ===== Stat Cards ===== --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-xl-3">
        <div class="card stat-card h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon bg-primary-subtle text-primary"><i class="bi bi-box-seam"></i></div>
                <div>
                    <p class="stat-label">Total Paket</p>
                    <h3>{{ number_format($stats['total_paket'], 0, ',', '.') }}</h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card stat-card h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon bg-success-subtle text-success"><i class="bi bi-cash-stack"></i></div>
                <div>
                    <p class="stat-label">Total Pagu</p>
                    <h3>{{ format_rupiah_singkat($stats['total_pagu']) }}</h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card stat-card h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon bg-info-subtle text-info"><i class="bi bi-file-earmark-check"></i></div>
                <div>
                    <p class="stat-label">Nilai Kontrak</p>
                    <h3>{{ format_rupiah_singkat($stats['total_kontrak']) }}</h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card stat-card h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon bg-warning-subtle text-warning"><i class="bi bi-graph-down-arrow"></i></div>
                <div>
                    <p class="stat-label">Efisiensi</p>
                    <h3>{{ $stats['efisiensi'] }}%</h3>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ===== Status Strip ===== --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3 col-xl">
        <div class="card h-100"><div class="card-body py-3 text-center">
            <div class="small text-secondary">Selesai</div>
            <div class="fs-4 fw-bold text-success">{{ $stats['selesai'] }}</div>
        </div></div>
    </div>
    <div class="col-6 col-md-3 col-xl">
        <div class="card h-100"><div class="card-body py-3 text-center">
            <div class="small text-secondary">Berjalan</div>
            <div class="fs-4 fw-bold text-primary">{{ $stats['berjalan'] }}</div>
        </div></div>
    </div>
    <div class="col-6 col-md-3 col-xl">
        <div class="card h-100"><div class="card-body py-3 text-center">
            <div class="small text-secondary">Tahap Pemilihan</div>
            <div class="fs-4 fw-bold text-warning">{{ $stats['pemilihan'] }}</div>
        </div></div>
    </div>
    <div class="col-6 col-md-3 col-xl">
        <div class="card h-100"><div class="card-body py-3 text-center">
            <div class="small text-secondary">Penyedia Aktif</div>
            <div class="fs-4 fw-bold text-info">{{ $stats['penyedia_aktif'] }}</div>
        </div></div>
    </div>
    <div class="col-6 col-md-3 col-xl">
        <div class="card h-100"><div class="card-body py-3 text-center">
            <div class="small text-secondary">Rata-rata Progress</div>
            <div class="fs-4 fw-bold">{{ $stats['progress_rata'] }}%</div>
            <div class="progress mt-1" style="height: 4px;">
                <div class="progress-bar" style="width: {{ $stats['progress_rata'] }}%"></div>
            </div>
        </div></div>
    </div>
</div>

{{-- ===== Charts ===== --}}
<div class="row g-3 mb-4">
    <div class="col-lg-7">
        <div class="card h-100">
            <div class="card-header bg-white border-0 pt-3 pb-0">
                <h6 class="fw-bold mb-0">Nilai Pagu per OPD (Top 8)</h6>
            </div>
            <div class="card-body"><canvas id="chartOpd" height="230"></canvas></div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card h-100">
            <div class="card-header bg-white border-0 pt-3 pb-0">
                <h6 class="fw-bold mb-0">Komposisi Jenis Pengadaan</h6>
            </div>
            <div class="card-body"><canvas id="chartJenis" height="230"></canvas></div>
        </div>
    </div>
</div>

{{-- ===== Tabel Paket Berjalan + Terbesar ===== --}}
<div class="row g-3">
    <div class="col-lg-7">
        <div class="card h-100">
            <div class="card-header bg-white border-0 pt-3 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0">Paket Sedang Berjalan</h6>
                <a href="{{ route('paket.index') }}" class="small text-decoration-none">Lihat semua <i class="bi bi-arrow-right"></i></a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead><tr>
                            <th class="ps-3">Paket</th><th>Progress</th><th class="pe-3">Nilai</th>
                        </tr></thead>
                        <tbody>
                            @forelse ($paketBerjalan as $p)
                                <tr>
                                    <td class="ps-3">
                                        <a href="{{ route('paket.show', $p) }}" class="text-decoration-none fw-semibold d-block text-truncate" style="max-width: 320px;">
                                            {{ \Illuminate\Support\Str::limit($p->nama_paket, 45) }}
                                        </a>
                                        <small class="text-secondary">{{ $p->opd->singkatan ?? '-' }}</small>
                                    </td>
                                    <td style="min-width: 110px;">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="progress flex-grow-1" style="height: 6px;">
                                                <div class="progress-bar {{ $p->progress >= 100 ? 'bg-success' : '' }}" style="width: {{ $p->progress }}%"></div>
                                            </div>
                                            <small class="text-secondary">{{ $p->progress }}%</small>
                                        </div>
                                    </td>
                                    <td class="pe-3 text-nowrap">{{ format_rupiah_singkat($p->nilai_kontrak ?: $p->pagu) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-center text-secondary py-4">Belum ada paket berjalan</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card h-100">
            <div class="card-header bg-white border-0 pt-3">
                <h6 class="fw-bold mb-0">Paket dengan Pagu Terbesar</h6>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    @forelse ($paketTerbesar as $p)
                        <li class="list-group-item d-flex justify-content-between align-items-center px-3">
                            <div class="me-2">
                                <a href="{{ route('paket.show', $p) }}" class="text-decoration-none fw-semibold small d-block text-truncate" style="max-width: 260px;">
                                    {{ \Illuminate\Support\Str::limit($p->nama_paket, 40) }}
                                </a>
                                <small class="text-secondary">{{ $p->opd->singkatan ?? '-' }} &bull; {{ $p->metode_label }}</small>
                            </div>
                            <span class="badge bg-primary-subtle text-primary">{{ format_rupiah_singkat($p->pagu) }}</span>
                        </li>
                    @empty
                        <li class="list-group-item text-center text-secondary py-4">Belum ada data</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Chart: Pagu per OPD
    new Chart(document.getElementById('chartOpd'), {
        type: 'bar',
        data: {
            labels: {!! json_encode($perOpd->pluck('singkatan')) !!},
            datasets: [{
                label: 'Total Pagu (Rp)',
                data: {!! json_encode($perOpd->pluck('total_pagu')) !!},
                backgroundColor: '#2563eb',
                borderRadius: 6,
                maxBarThickness: 38,
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                y: {
                    ticks: {
                        callback: function(value) {
                            if (value >= 1e9) return (value / 1e9).toFixed(0) + ' M';
                            if (value >= 1e6) return (value / 1e6).toFixed(0) + ' jt';
                            return value;
                        }
                    }
                }
            }
        }
    });

    // Chart: Jenis (doughnut)
    new Chart(document.getElementById('chartJenis'), {
        type: 'doughnut',
        data: {
            labels: {!! json_encode($perJenis->keys()->map(fn($j) => ['barang' => 'Barang', 'jasa_konsultansi' => 'Jasa Konsultansi', 'konstruksi' => 'Konstruksi', 'jasa_lainnya' => 'Jasa Lainnya'][$j] ?? $j)) !!},
            datasets: [{
                data: {!! json_encode($perJenis->values()) !!},
                backgroundColor: ['#2563eb', '#7c3aed', '#10b981', '#f59e0b'],
                borderWidth: 2,
                borderColor: '#fff',
            }]
        },
        options: {
            responsive: true,
            cutout: '62%',
            plugins: { legend: { position: 'bottom' } }
        }
    });
</script>
@endpush
