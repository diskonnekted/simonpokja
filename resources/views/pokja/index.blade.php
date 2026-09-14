@extends('layouts.app')

@section('title', 'Kinerja Pokja')

@section('content')
{{-- ===== Header + Filter Tahun ===== --}}
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <h4 class="fw-bold mb-1"><i class="bi bi-person-badge me-2 text-primary"></i>Monitoring Kinerja Pokja / Panitia Pengadaan</h4>
        <p class="text-secondary mb-0 small">Pengawasan beban kerja, kepatuhan SLA, dan risiko panitia pengadaan Kab. Banjarnegara</p>
    </div>
    <form method="GET" class="d-flex gap-2">
        <select name="tahun" class="form-select form-select-sm" style="width: 130px;" onchange="this.form.submit()">
            @forelse ($tahuns as $t)
                <option value="{{ $t }}" {{ $t == $tahun ? 'selected' : '' }}>Tahun {{ $t }}</option>
            @empty
                <option value="{{ $tahun }}">Tahun {{ $tahun }}</option>
            @endforelse
        </select>
    </form>
</div>

{{-- ===== KPI UTAMA (4 metrik dasbor pengawasan) ===== --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-xl-3">
        <div class="card stat-card h-100 border-start border-4 border-primary">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon bg-primary-subtle text-primary"><i class="bi bi-box-seam"></i></div>
                <div>
                    <p class="stat-label">Paket Aktif</p>
                    <h3>{{ $kpi['paket_aktif'] }}</h3>
                    <small class="text-secondary">sedang dikelola pokja</small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card stat-card h-100 border-start border-4 {{ $kpi['tender_gagal_persen'] > 20 ? 'border-danger' : 'border-success' }}">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon {{ $kpi['tender_gagal_persen'] > 20 ? 'bg-danger-subtle text-danger' : 'bg-success-subtle text-success' }}"><i class="bi bi-x-octagon"></i></div>
                <div>
                    <p class="stat-label">Tender Gagal</p>
                    <h3 class="{{ $kpi['tender_gagal_persen'] > 20 ? 'text-danger' : '' }}">{{ $kpi['tender_gagal_persen'] }}%</h3>
                    <small class="text-secondary">{{ $kpi['tender_gagal_jumlah'] }} dari paket tender/seleksi</small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card stat-card h-100 border-start border-4 {{ $kpi['kepatuhan_sla'] < 80 ? 'border-warning' : 'border-success' }}">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon {{ $kpi['kepatuhan_sla'] < 80 ? 'bg-warning-subtle text-warning' : 'bg-success-subtle text-success' }}"><i class="bi bi-stopwatch"></i></div>
                <div>
                    <p class="stat-label">Kepatuhan SLA</p>
                    <h3 class="{{ $kpi['kepatuhan_sla'] < 80 ? 'text-warning' : '' }}">{{ $kpi['kepatuhan_sla'] }}%</h3>
                    <small class="text-secondary">{{ $kpi['delay_berulang'] }} paket delay &gt;3x</small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card stat-card h-100 border-start border-4 {{ $kpi['alert_critical'] > 0 ? 'border-danger' : 'border-success' }}">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon {{ $kpi['alert_critical'] > 0 ? 'bg-danger-subtle text-danger' : 'bg-success-subtle text-success' }}"><i class="bi bi-exclamation-octagon"></i></div>
                <div>
                    <p class="stat-label">Alert Anomali</p>
                    <h3>{{ $kpi['alert_aktif'] }}</h3>
                    <small class="text-danger fw-semibold">{{ $kpi['alert_critical'] }} critical perlu tindakan</small>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ===== KARTU POKJA (Beban kerja & status risiko per pokja) ===== --}}
<h6 class="fw-bold text-secondary text-uppercase mb-3" style="font-size: .75rem; letter-spacing: 1px;">Beban Kerja &amp; Status Risiko per Pokja</h6>
<div class="row g-3 mb-4">
    @forelse ($pokjas as $p)
        <div class="col-md-6 col-xl-3">
            <a href="{{ route('pokja.show', $p) }}" class="text-decoration-none">
                <div class="card h-100 pokja-card position-relative overflow-hidden">
                    <div class="position-absolute top-0 end-0 mt-2 me-2">
                        <span class="badge badge-risiko-{{ strtolower($p->label_risiko) }}">{{ $p->label_risiko }}</span>
                    </div>
                    <div class="card-body">
                        <h6 class="fw-bold mb-1 text-dark">{{ $p->nama }}</h6>
                        <small class="text-secondary d-block mb-2">{{ $p->bidang }}</small>
                        <div class="d-flex justify-content-between small mb-1">
                            <span class="text-secondary">Ketua</span>
                            <span class="fw-semibold text-dark" style="font-size: .78rem;">{{ \Illuminate\Support\Str::limit($p->ketua, 22) }}</span>
                        </div>
                        <div class="d-flex justify-content-between small mb-2">
                            <span class="text-secondary">Beban</span>
                            <span class="fw-bold {{ $p->overload ? 'text-danger' : 'text-dark' }}">{{ $p->paket_aktif }} paket
                                @if ($p->overload) <i class="bi bi-exclamation-triangle text-danger" title="Overload"></i>@endif
                            </span>
                        </div>
                        {{-- Skor risiko bar --}}
                        <div class="d-flex justify-content-between small mb-1">
                            <span class="text-secondary">Skor Risiko</span>
                            <span class="fw-bold">{{ $p->skor_risiko }}/100</span>
                        </div>
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar bg-{{ $p->warna_risiko }}" style="width: {{ $p->skor_risiko }}%"></div>
                        </div>
                        <div class="d-flex gap-3 mt-2 small text-secondary">
                            <span><i class="bi bi-stopwatch me-1"></i>SLA {{ $p->kepatuhan_sla }}%</span>
                            <span><i class="bi bi-x-octagon me-1"></i>Gagal {{ $p->tender_gagal_persen }}%</span>
                            @if ($p->alert_critical > 0)
                                <span class="text-danger fw-bold"><i class="bi bi-exclamation-octagon me-1"></i>{{ $p->alert_critical }}</span>
                            @endif
                        </div>
                    </div>
                </div>
            </a>
        </div>
    @empty
        <div class="col-12"><div class="card"><div class="card-body text-center text-secondary py-4">Belum ada pokja terdaftar</div></div></div>
    @endforelse
</div>

{{-- ===== Grafik: Distribusi tahapan + tren jadwal ===== --}}
<div class="row g-3 mb-4">
    <div class="col-lg-5">
        <div class="card h-100">
            <div class="card-header bg-white pt-3">
                <h6 class="fw-bold mb-0">Distribusi Tahapan Tender (Paket Aktif)</h6>
            </div>
            <div class="card-body">
                <canvas id="chartTahapan" height="220"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-7">
        <div class="card h-100">
            <div class="card-header bg-white pt-3 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0">Tren Perubahan/Pengunduran Jadwal (6 Bulan)</h6>
                <span class="badge {{ $persenTanpaBa > 20 ? 'text-bg-danger' : 'text-bg-success' }}">{{ $persenTanpaBa }}% tanpa BA</span>
            </div>
            <div class="card-body"><canvas id="chartTren" height="220"></canvas></div>
        </div>
    </div>
</div>

{{-- ===== Alert Anomali + Paket Kritis ===== --}}
<div class="row g-3">
    <div class="col-lg-7">
        <div class="card h-100">
            <div class="card-header bg-white pt-3 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0"><i class="bi bi-bell me-2 text-danger"></i>Alert Anomali Aktif</h6>
                <a href="{{ route('pemantauan.index') }}" class="small text-decoration-none">Semua paket bermasalah <i class="bi bi-arrow-right"></i></a>
            </div>
            <div class="card-body p-0" style="max-height: 420px; overflow-y: auto;">
                <ul class="list-group list-group-flush">
                    @forelse ($alerts as $a)
                        <li class="list-group-item d-flex gap-3 align-items-start px-3 py-3">
                            <span class="flex-shrink-0 rounded-circle d-flex align-items-center justify-content-center
                                {{ $a->tingkat == 'critical' ? 'bg-danger-subtle text-danger' : ($a->tingkat == 'warning' ? 'bg-warning-subtle text-warning' : 'bg-info-subtle text-info') }}"
                                style="width: 36px; height: 36px;">
                                <i class="bi {{ $a->tingkat == 'critical' ? 'bi-exclamation-octagon-fill' : 'bi-exclamation-triangle' }}"></i>
                            </span>
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between gap-2">
                                    <span class="fw-semibold small">{{ $a->deskripsi }}</span>
                                </div>
                                <small class="text-secondary">
                                    {{ $a->pokja->nama ?? '-' }}
                                    @if ($a->paket) &bull; <a href="{{ route('pemantauan.show', $a->paket) }}" class="text-decoration-none">{{ $a->paket->kode_paket }}</a> @endif
                                    &bull; {{ $a->created_at->diffForHumans() }}
                                </small>
                            </div>
                            <form action="{{ route('alert.update', $a) }}" method="POST" class="flex-shrink-0">
                                @csrf @method('PATCH')
                                <select name="status" class="form-select form-select-sm" style="width: auto;" onchange="this.form.submit()">
                                    @foreach (['aktif' => 'Aktif', 'ditinjau' => 'Ditinjau', 'selesai' => 'Selesai'] as $val => $label)
                                        <option value="{{ $val }}" {{ $a->status == $val ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </form>
                        </li>
                    @empty
                        <li class="list-group-item text-center text-secondary py-4">Tidak ada alert aktif</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card h-100">
            <div class="card-header bg-white pt-3 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0">Segmentasi Risiko Paket</h6>
                <div class="d-flex gap-2 small">
                    @foreach (['kritis' => 'danger', 'sedang' => 'warning', 'rendah' => 'success'] as $r => $c)
                        <span class="badge badge-risiko-{{ $r }}">{{ strtoupper($r) }}: {{ $risikoDistribusi[$r] ?? 0 }}</span>
                    @endforeach
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead><tr>
                            <th class="ps-3">Paket Kritis</th>
                            <th>Pokja</th>
                            <th class="pe-3 text-end">Pagu</th>
                        </tr></thead>
                        <tbody>
                            @forelse ($paketKritis as $p)
                                <tr>
                                    <td class="ps-3">
                                        <a href="{{ route('pemantauan.show', $p) }}" class="fw-semibold text-decoration-none small d-block text-truncate" style="max-width: 220px;">
                                            {{ \Illuminate\Support\Str::limit($p->nama_paket, 38) }}
                                        </a>
                                        <small class="text-secondary">{{ $p->kode_paket }}</small>
                                    </td>
                                    <td><small>{{ $p->pokja->nama ?? '-' }}</small></td>
                                    <td class="pe-3 text-end text-nowrap">{{ format_rupiah_singkat($p->pagu) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-center text-secondary py-4">Tidak ada paket kritis</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .pokja-card { transition: all .15s ease; }
    .pokja-card:hover { transform: translateY(-3px); box-shadow: 0 8px 24px rgba(0,0,0,.12) !important; }
</style>
@endpush

@push('scripts')
<script>
    const tahapLabels = { persiapan: 'Persiapan', pengumuman: 'Pengumuman', evaluasi: 'Evaluasi', sanggah: 'Sanggah', penetapan: 'Penetapan' };
    const tahapData = {!! json_encode($tahapanDistribusi) !!};

    // Chart: Distribusi Tahapan Tender
    new Chart(document.getElementById('chartTahapan'), {
        type: 'bar',
        data: {
            labels: Object.keys(tahapData).map(k => tahapLabels[k] || k),
            datasets: [{
                data: Object.values(tahapData),
                backgroundColor: ['#64748b', '#0ea5e9', '#f59e0b', '#7c3aed', '#10b981'],
                borderRadius: 6, maxBarThickness: 42,
            }]
        },
        options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
    });

    // Chart: Tren Perubahan Jadwal
    new Chart(document.getElementById('chartTren'), {
        type: 'line',
        data: {
            labels: {!! json_encode($trenJadwal->pluck('bulan')) !!},
            datasets: [{
                label: 'Perubahan jadwal',
                data: {!! json_encode($trenJadwal->pluck('jumlah')) !!},
                borderColor: '#dc3545', backgroundColor: 'rgba(220,53,69,.08)',
                fill: true, tension: .35, pointRadius: 4,
            }]
        },
        options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
    });
</script>
@endpush
