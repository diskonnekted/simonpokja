@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
{{-- ===== Header & Filter Eksekutif ===== --}}
<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 pb-2 border-bottom gap-2" style="border-color: var(--border-panel) !important;">
    <div>
        <h5 class="fw-bold mb-0 text-uppercase" style="font-size: 18px; letter-spacing: 0.5px; color: var(--text-main);">Ringkasan Eksekutif Pengadaan</h5>
        <div class="text-secondary small" style="font-size: 13px;">Sistem Pengawasan & Evaluasi Tender LPSE Kabupaten Banjarnegara</div>
    </div>
    <form method="GET" class="d-flex align-items-center gap-2">
        <label for="filterTahun" class="small text-secondary fw-semibold text-uppercase d-none d-sm-inline" style="font-size: 12px; letter-spacing: 0.5px;">Tahun:</label>
        <select id="filterTahun" name="tahun" class="form-select form-select-sm" style="width: 130px; font-size: 13px;">
            @forelse ($tahuns as $t)
                <option value="{{ $t }}" {{ $t == $tahun ? 'selected' : '' }}>TA {{ $t }}</option>
            @empty
                <option value="{{ $tahun }}">TA {{ $tahun }}</option>
            @endforelse
        </select>
        <button class="btn btn-primary btn-sm d-flex align-items-center gap-1" style="font-size: 13px;">
            <i class="bi bi-funnel"></i> Filter
        </button>
    </form>
</div>

{{-- ===== Unified Executive Metrics Bar (4 Kolom Terpadu) ===== --}}
<div class="metrics-strip">
    <div class="metric-cell">
        <div class="metric-caption">
            <i class="bi bi-box-seam me-1 text-muted"></i>Total Paket Tender
        </div>
        <div class="metric-number">
            {{ number_format($stats['total_paket'], 0, ',', '.') }}
            <span class="metric-unit">Paket</span>
        </div>
        <div class="metric-subtext">Tahun Anggaran {{ $tahun }}</div>
    </div>

    <div class="metric-cell">
        <div class="metric-caption">
            <i class="bi bi-cash-stack me-1 text-muted"></i>Total Pagu Anggaran
        </div>
        <div class="metric-number">
            {{ format_rupiah_singkat($stats['total_pagu']) }}
        </div>
        <div class="metric-subtext">Alokasi APBD Kab. Banjarnegara</div>
    </div>

    <div class="metric-cell">
        <div class="metric-caption">
            <i class="bi bi-file-earmark-check me-1 text-muted"></i>Realisasi Kontrak
        </div>
        <div class="metric-number">
            {{ format_rupiah_singkat($stats['total_kontrak']) }}
        </div>
        <div class="metric-subtext">Penetapan SPK & Perjanjian</div>
    </div>

    <div class="metric-cell">
        <div class="metric-caption">
            <i class="bi bi-graph-down-arrow me-1 text-muted"></i>Efisiensi Anggaran
        </div>
        <div class="metric-number" style="color: #16a34a;">
            {{ $stats['efisiensi'] }}%
        </div>
        <div class="metric-subtext">Penghematan Selisih Nilai Kontrak</div>
    </div>
</div>

{{-- ===== Status Ratio Track (Ledger Pengawasan) ===== --}}
@php
    $tot = $stats['total_paket'] > 0 ? $stats['total_paket'] : 1;
    $pctSelesai = round(($stats['selesai'] / $tot) * 100, 1);
    $pctBerjalan = round(($stats['berjalan'] / $tot) * 100, 1);
    $pctPemilihan = round(($stats['pemilihan'] / $tot) * 100, 1);
@endphp
<div class="ratio-track-container">
    <div class="d-flex justify-content-between align-items-center">
        <span class="fw-bold text-uppercase" style="font-size: 12px; letter-spacing: 0.5px; color: var(--text-secondary);">
            Distribusi Status Pelaksanaan Tender
        </span>
        <span class="text-secondary" style="font-size: 12.5px;">
            <i class="bi bi-people me-1"></i><strong>{{ $stats['penyedia_aktif'] }}</strong> Penyedia Aktif Terdaftar
        </span>
    </div>

    <div class="stacked-progress-bar">
        <div class="track-seg-success" style="width: {{ $pctSelesai }}%" title="Selesai: {{ $stats['selesai'] }} ({{ $pctSelesai }}%)"></div>
        <div class="track-seg-primary" style="width: {{ $pctBerjalan }}%" title="Berjalan: {{ $stats['berjalan'] }} ({{ $pctBerjalan }}%)"></div>
        <div class="track-seg-warning" style="width: {{ $pctPemilihan }}%" title="Pemilihan: {{ $stats['pemilihan'] }} ({{ $pctPemilihan }}%)"></div>
    </div>

    <div class="ratio-legend-row">
        <div><span class="status-dot track-seg-success"></span> Selesai: <strong>{{ $stats['selesai'] }}</strong> ({{ $pctSelesai }}%)</div>
        <div><span class="status-dot track-seg-primary"></span> Pelaksanaan Berjalan: <strong>{{ $stats['berjalan'] }}</strong> ({{ $pctBerjalan }}%)</div>
        <div><span class="status-dot track-seg-warning"></span> Tahap Pemilihan: <strong>{{ $stats['pemilihan'] }}</strong> ({{ $pctPemilihan }}%)</div>
        <div class="ms-auto"><span class="status-dot" style="background: #64748b;"></span> Rerata Progres Fisik: <strong>{{ $stats['progress_rata'] }}%</strong></div>
    </div>
</div>

{{-- ===== Charts Row ===== --}}
<div class="row g-3 mb-3">
    <div class="col-lg-7">
        <div class="gov-panel h-100 mb-0">
            <div class="gov-panel-header">
                <h6 class="gov-panel-title">Nilai Pagu per OPD (Top 8 Alokasi)</h6>
                <span class="text-muted" style="font-size: 12px;"><i class="bi bi-bar-chart me-1"></i>Satuan Pagu</span>
            </div>
            <div class="gov-panel-body">
                <canvas id="chartOpd" height="230"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="gov-panel h-100 mb-0">
            <div class="gov-panel-header">
                <h6 class="gov-panel-title">Komposisi Jenis Pengadaan</h6>
                <span class="text-muted" style="font-size: 12px;"><i class="bi bi-pie-chart me-1"></i>Distribusi</span>
            </div>
            <div class="gov-panel-body">
                <canvas id="chartJenis" height="230"></canvas>
            </div>
        </div>
    </div>
</div>

{{-- ===== Tables Row: Paket Berjalan & Paket Pagu Terbesar ===== --}}
<div class="row g-3">
    <div class="col-lg-7">
        <div class="gov-panel h-100 mb-0">
            <div class="gov-panel-header">
                <h6 class="gov-panel-title">Paket Sedang Berjalan</h6>
                <a href="{{ route('paket.index') }}" class="text-decoration-none fw-semibold" style="font-size: 13px; color: var(--blue-primary);">
                    Lihat Semua <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>
            <div class="p-0 table-responsive">
                <table class="gov-table">
                    <thead>
                        <tr>
                            <th class="ps-3" style="width: 50%;">Paket Pekerjaan</th>
                            <th style="width: 25%;">Progres Fisik</th>
                            <th class="pe-3 text-end" style="width: 25%;">Nilai Kontrak / Pagu</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($paketBerjalan as $p)
                            <tr>
                                <td class="ps-3">
                                    <a href="{{ route('paket.show', $p) }}" class="text-decoration-none fw-semibold d-block text-truncate text-dark" style="max-width: 330px; font-size: 14px;">
                                        {{ \Illuminate\Support\Str::limit($p->nama_paket, 45) }}
                                    </a>
                                    <div class="text-secondary small mt-1" style="font-size: 12px;">
                                        <span class="fw-semibold text-dark">{{ $p->opd->singkatan ?? '-' }}</span> &bull; {{ $p->metode_label ?? 'Tender' }}
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="progress flex-grow-1" style="height: 6px;">
                                            <div class="progress-bar {{ $p->progress >= 100 ? 'bg-success' : 'bg-primary' }}" style="width: {{ $p->progress }}%"></div>
                                        </div>
                                        <span class="text-dark fw-semibold" style="font-size: 13px; min-width: 38px;">{{ $p->progress }}%</span>
                                    </div>
                                </td>
                                <td class="pe-3 text-end fw-semibold text-nowrap" style="font-size: 13.5px; font-family: 'JetBrains Mono', monospace;">
                                    {{ format_rupiah_singkat($p->nilai_kontrak ?: $p->pagu) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center text-secondary py-4" style="font-size: 13px;">Tidak ada paket yang sedang berjalan</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="gov-panel h-100 mb-0">
            <div class="gov-panel-header">
                <h6 class="gov-panel-title">Paket dengan Pagu Terbesar</h6>
                <span class="text-muted" style="font-size: 12px;"><i class="bi bi-sort-numeric-down me-1"></i>Pagu</span>
            </div>
            <div class="p-0 table-responsive">
                <table class="gov-table">
                    <thead>
                        <tr>
                            <th class="ps-3" style="width: 65%;">Nama Paket & OPD</th>
                            <th class="pe-3 text-end" style="width: 35%;">Alokasi Pagu</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($paketTerbesar as $p)
                            <tr>
                                <td class="ps-3">
                                    <a href="{{ route('paket.show', $p) }}" class="text-decoration-none fw-semibold d-block text-truncate text-dark" style="max-width: 250px; font-size: 14px;">
                                        {{ \Illuminate\Support\Str::limit($p->nama_paket, 38) }}
                                    </a>
                                    <div class="text-secondary small mt-1" style="font-size: 12px;">
                                        <span class="fw-semibold text-dark">{{ $p->opd->singkatan ?? '-' }}</span> &bull; {{ $p->metode_label }}
                                    </div>
                                </td>
                                <td class="pe-3 text-end fw-semibold text-nowrap" style="font-size: 13.5px; font-family: 'JetBrains Mono', monospace; color: var(--blue-primary);">
                                    {{ format_rupiah_singkat($p->pagu) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="text-center text-secondary py-4" style="font-size: 13px;">Belum ada data paket</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Konfigurasi Standar Chart.js Swiss Data-Dense dengan Tipografi Nyaman
    Chart.defaults.font.family = "'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif";
    Chart.defaults.font.size = 12.5;
    Chart.defaults.color = '#475569';

    // Chart: Pagu per OPD (Top 8)
    new Chart(document.getElementById('chartOpd'), {
        type: 'bar',
        data: {
            labels: {!! json_encode($perOpd->pluck('singkatan')) !!},
            datasets: [{
                label: 'Total Pagu (Rp)',
                data: {!! json_encode($perOpd->pluck('total_pagu')) !!},
                backgroundColor: '#1d4ed8',
                hoverBackgroundColor: '#1e40af',
                borderRadius: 2,
                maxBarThickness: 34,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#0f172a',
                    padding: 10,
                    cornerRadius: 3,
                    titleFont: { size: 13, weight: 'bold' },
                    bodyFont: { size: 12 }
                }
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { font: { size: 12 } }
                },
                y: {
                    border: { dash: [3, 3] },
                    grid: { color: '#e2e8f0' },
                    ticks: {
                        font: { size: 12 },
                        callback: function(value) {
                            if (value >= 1e9) return (value / 1e9).toFixed(1) + ' M';
                            if (value >= 1e6) return (value / 1e6).toFixed(0) + ' jt';
                            return value;
                        }
                    }
                }
            }
        }
    });

    // Chart: Komposisi Jenis Pengadaan
    new Chart(document.getElementById('chartJenis'), {
        type: 'doughnut',
        data: {
            labels: {!! json_encode($perJenis->keys()->map(fn($j) => ['barang' => 'Barang', 'jasa_konsultansi' => 'Jasa Konsultansi', 'konstruksi' => 'Konstruksi', 'jasa_lainnya' => 'Jasa Lainnya'][$j] ?? $j)) !!},
            datasets: [{
                data: {!! json_encode($perJenis->values()) !!},
                backgroundColor: ['#1d4ed8', '#0f172a', '#16a34a', '#d97706'],
                borderWidth: 2,
                borderColor: '#ffffff',
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '66%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        boxWidth: 12,
                        padding: 14,
                        font: { size: 12 }
                    }
                },
                tooltip: {
                    backgroundColor: '#0f172a',
                    padding: 10,
                    cornerRadius: 3,
                    titleFont: { size: 13 },
                    bodyFont: { size: 12 }
                }
            }
        }
    });
</script>
@endpush
