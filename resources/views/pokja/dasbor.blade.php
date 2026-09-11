@extends('layouts.app')

@section('title', 'Dasbor Pokja — ' . $pokja->nama)

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
    <div>
        <h1 class="h4 mb-0">Dasbor {{ $pokja->nama }}</h1>
        <small class="text-secondary">{{ $pokja->bidang }} &bull; {{ auth()->user()->name }}</small>
    </div>
    @if ($user->isAdmin())
        <form method="GET" action="{{ route('pokja.dasbor') }}" class="d-flex gap-2 align-items-center">
            <label class="small text-secondary mb-0" for="pilih-pokja">Mode tinjauan:</label>
            <select id="pilih-pokja" name="pokja" class="form-select form-select-sm" style="width:auto" onchange="this.form.submit()">
                @foreach ($listPokja as $pj)
                    <option value="{{ $pj->id }}" {{ $pj->id == $pokja->id ? 'selected' : '' }}>{{ $pj->nama }}</option>
                @endforeach
            </select>
        </form>
    @endif
</div>

{{-- Ringkasan --}}
<div class="row g-2 mb-3">
    <div class="col-6 col-lg-2"><div class="card h-100"><div class="card-body py-2 px-3">
        <div class="text-secondary small">Skor Risiko</div>
        <div class="h5 mb-0">{{ $pokja->skor_risiko }}
            <span class="badge text-bg-{{ $pokja->warna_risiko }} fs-6">{{ $pokja->label_risiko }}</span>
        </div>
    </div></div></div>
    <div class="col-6 col-lg-2"><div class="card h-100"><div class="card-body py-2 px-3">
        <div class="text-secondary small">Beban Kerja</div>
        <div class="h5 mb-0">{{ $pakets->count() }} <small class="text-secondary">paket</small></div>
    </div></div></div>
    <div class="col-6 col-lg-2"><div class="card h-100 border-danger-subtle"><div class="card-body py-2 px-3">
        <div class="text-secondary small">Lampaui Deadline</div>
        <div class="h5 mb-0 text-danger">{{ $nLewat }}</div>
    </div></div></div>
    <div class="col-6 col-lg-2"><div class="card h-100 border-warning-subtle"><div class="card-body py-2 px-3">
        <div class="text-secondary small">Mendesak (&le;7 hari)</div>
        <div class="h5 mb-0 text-warning">{{ $nMendesak }}</div>
    </div></div></div>
    <div class="col-6 col-lg-2"><div class="card h-100"><div class="card-body py-2 px-3">
        <div class="text-secondary small">Mendekati (&le;30 hari)</div>
        <div class="h5 mb-0 text-secondary">{{ $nMendekati }}</div>
    </div></div></div>
    <div class="col-6 col-lg-2"><div class="card h-100"><div class="card-body py-2 px-3">
        <div class="text-secondary small">Kepatuhan SLA</div>
        <div class="h5 mb-0">{{ (int) $pokja->kepatuhan_sla }}%</div>
    </div></div></div>
</div>

{{-- Daftar pekerjaan --}}
<div class="card">
    <div class="card-header py-2 d-flex justify-content-between align-items-center">
        <span><i class="bi bi-list-task me-1"></i><strong>Daftar Pekerjaan</strong>
            <span class="text-secondary small">TA {{ date('Y') }}</span></span>
        <span class="badge text-bg-primary">{{ $pakets->count() }}</span>
    </div>
    <div class="card-body p-0" style="max-height: 640px; overflow-y: auto;">
        <table class="table table-sm table-hover align-middle mb-0">
            <thead><tr>
                <th class="ps-3">Pekerjaan</th>
                <th>Progres</th>
                <th>Deadline</th>
                <th class="text-end pe-3">Aksi</th>
            </tr></thead>
            <tbody>
                @forelse ($pakets as $p)
                    @php [$kunci, $labelDl, $warnaDl, $sisa] = $p->status_deadline; @endphp
                    <tr>
                        <td class="ps-3" style="max-width: 280px;">
                            <div class="fw-semibold small text-truncate">{{ $p->nama_paket }}</div>
                            <small class="text-secondary">{{ $p->kode_paket }} &bull; {{ $p->opd?->singkatan ?? '-' }} &bull; {{ \Illuminate\Support\Str::title(str_replace('_',' ',$p->metode)) }}</small>
                        </td>
                        <td style="min-width: 120px;">
                            <div class="tahapan-bar" role="img" aria-label="Progres {{ $p->progress }}%">
                                @foreach ($p->tahapan_progres as $t)
                                    <span class="tahapan-seg" style="background: {{ \App\Models\PaketPengadaan::warnaTahapan($t['status']) }}; width: {{ 100 / count($p->tahapan_progres) }}%;"></span>
                                @endforeach
                            </div>
                            <small class="text-secondary">{{ $p->progress }}%</small>
                        </td>
                        <td>
                            <div>{{ $p->tanggal_selesai?->format('d M Y') ?? '-' }}</div>
                            <span class="badge text-bg-{{ $warnaDl }}">{{ $labelDl }}</span>
                        </td>
                        <td class="text-end pe-3">
                            @if ($bolehSimpan)
                                <div class="dropdown d-inline">
                                    <button class="btn btn-sm btn-primary dropdown-toggle" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
                                        Simpan Progres
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-end p-3" style="min-width: 300px;">
                                        <form method="POST" action="{{ route('pokja.progres', $p) }}">
                                            @csrf
                                            <h6 class="dropdown-header px-0">Simpan Progres Pekerjaan</h6>
                                            <div class="mb-2">
                                                <label class="form-label small mb-1" for="prog-{{ $p->id }}">Progres (%)</label>
                                                <input type="number" id="prog-{{ $p->id }}" name="progress" class="form-control form-control-sm"
                                                       min="0" max="100" value="{{ $p->progress }}" required>
                                            </div>
                                            <div class="mb-2">
                                                <label class="form-label small mb-1" for="st-{{ $p->id }}">Status</label>
                                                <select id="st-{{ $p->id }}" name="status" class="form-select form-select-sm" required>
                                                    @foreach (['draft','persiapan','pemilihan','kontrak','pelaksanaan','selesai','batal'] as $s)
                                                        <option value="{{ $s }}" {{ $p->status === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label small mb-1" for="cat-{{ $p->id }}">Catatan</label>
                                                <textarea id="cat-{{ $p->id }}" name="catatan" class="form-control form-control-sm" rows="2"
                                                          placeholder="Kemajuan / kendala hari ini...">{{ old('catatan') }}</textarea>
                                            </div>
                                            <button type="submit" class="btn btn-sm btn-primary w-100">
                                                <i class="bi bi-save me-1"></i>Simpan
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary">Tinjauan</span>
                            @endif
                            <button type="button" class="btn btn-sm btn-outline-secondary ms-1" data-bs-toggle="modal"
                                    data-bs-target="#modal-riwayat" data-url="{{ route('pokja.riwayat', $p) }}"
                                    title="Riwayat progres">
                                <i class="bi bi-clock-history"></i>
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center text-secondary py-4">Tidak ada pekerjaan TA {{ date('Y') }} untuk Pokja ini</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Modal riwayat --}}
<div class="modal fade" id="modal-riwayat" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h5 class="modal-title h6" id="riwayat-judul">Riwayat Progres</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body" id="riwayat-isi"><div class="text-secondary text-center py-4">Memuat...</div></div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .tahapan-bar { display: flex; height: 10px; border-radius: 5px; overflow: hidden; gap: 2px; background: #f8f9fa; }
    .tahapan-seg { display: block; height: 100%; border-radius: 2px; }
    /* Dropdown form supaya klik di dalam tidak menutup dropdown */
    .dropdown-menu form { padding: 0; margin: 0; }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    'use strict';
    // Modal riwayat: ambil data via API
    const modal = document.getElementById('modal-riwayat');
    modal.addEventListener('show.bs.modal', function (ev) {
        const url = ev.relatedTarget.dataset.url;
        const isi = document.getElementById('riwayat-isi');
        isi.innerHTML = '<div class="text-secondary text-center py-4">Memuat...</div>';
        fetch(url)
            .then(r => r.json())
            .then(d => {
                document.getElementById('riwayat-judul').textContent =
                    'Riwayat Progres — ' + d.paket.kode;
                isi.innerHTML = d.riwayat.length === 0
                    ? '<div class="text-secondary text-center py-4">Belum ada riwayat progres.</div>'
                    : '<table class="table table-sm table-striped mb-0"><thead><tr><th>Waktu</th><th>Oleh</th><th>Progres</th><th>Status</th><th>Catatan</th></tr></thead><tbody>' +
                      d.riwayat.map(r =>
                        `<tr><td class="text-nowrap">${r.waktu}</td><td>${r.user || '-'}</td>` +
                        `<td><strong>${r.progress}%</strong></td><td>${r.status}</td><td class="small">${r.catatan || '-'}</td></tr>`
                      ).join('') + '</tbody></table>';
            })
            .catch(() => { isi.innerHTML = '<div class="text-danger text-center py-4">Gagal memuat riwayat.</div>'; });
    });
});
</script>
@endpush
