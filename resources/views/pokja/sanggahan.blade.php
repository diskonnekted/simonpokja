@extends('layouts.app')

@section('title', 'Sanggahan & SLA — ' . $pokja->nama)

@section('breadcrumb')
<ul class="breadcrumb-links">
    <li><a href="{{ route('dashboard') }}"><i class="bi bi-house-door me-1"></i>Beranda</a></li>
    <li class="sep"><i class="bi bi-chevron-right"></i></li>
    <li><a href="{{ route('pokja.dasbor') }}">{{ $pokja->nama }}</a></li>
    <li class="sep"><i class="bi bi-chevron-right"></i></li>
    <li class="active">Sanggahan & SLA</li>
</ul>
<div class="text-secondary small font-monospace d-none d-sm-block">
    <i class="bi bi-flag me-1"></i>SLA 3 Hari Kerja (TA {{ $tahun }})
</div>
@endsection

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
    <div>
        <h1 class="h4 mb-0">Sanggahan Rekanan & SLA</h1>
        <small class="text-secondary">{{ $pokja->nama }} &bull; Pengawasan Tenggat Waktu Tanggapan Resmi Peserta Pemilihan</small>
    </div>
    <form method="GET" action="{{ route('pokja.sanggahan') }}" class="d-flex gap-2 align-items-center flex-wrap">
        @if ($user->isAdmin())
            <label class="small text-secondary mb-0" for="pilih-pokja">Pokja:</label>
            <select id="pilih-pokja" name="pokja" class="form-select form-select-sm" style="width:auto" onchange="this.form.submit()">
                @foreach ($listPokja as $pj)
                    <option value="{{ $pj->id }}" {{ $pj->id == $pokja->id ? 'selected' : '' }}>{{ $pj->nama }}</option>
                @endforeach
            </select>
        @endif
        <label class="small text-secondary mb-0" for="pilih-tahun">Tahun:</label>
        <select id="pilih-tahun" name="tahun" class="form-select form-select-sm" style="width:auto" onchange="this.form.submit()">
            @foreach ($tahuns as $t)
                <option value="{{ $t }}" {{ $t == $tahun ? 'selected' : '' }}>TA {{ $t }}</option>
            @endforeach
        </select>
    </form>
</div>

@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show py-2 px-3 mb-3 small" role="alert">
        <i class="bi bi-check-circle-fill me-1"></i> {{ session('success') }}
        <button type="button" class="btn-close py-2" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if ($kpi['kritis'] > 0)
    <div class="alert alert-danger py-2 px-3 mb-3 small d-flex align-items-center justify-content-between gap-2 border-danger-subtle">
        <div>
            <i class="bi bi-exclamation-octagon-fill me-2 text-danger"></i>
            <strong>Perhatian SLA:</strong> Terdapat <strong>{{ $kpi['kritis'] }}</strong> sanggahan yang telah melampaui atau berada pada hari terakhir batas toleransi jawaban (3 hari kerja).
        </div>
        <button type="button" class="btn btn-sm btn-outline-danger py-0 px-2 js-chip-trigger" data-filter="kritis">
            Lihat Yang Kritis
        </button>
    </div>
@endif

{{-- 4 Metrik Taktis Sanggahan --}}
<div class="row g-2 mb-3">
    <div class="col-6 col-lg-3">
        <div class="card h-100 border-start border-4 border-danger js-card-filter shadow-sm" data-filter="menunggu" style="cursor: pointer;" title="Klik untuk menyaring sanggahan yang belum dijawab">
            <div class="card-body py-2 px-3">
                <div class="text-secondary small d-flex justify-content-between align-items-center mb-1">
                    <span class="fw-semibold">Menunggu Tanggapan</span>
                    <i class="bi bi-hourglass-split text-danger"></i>
                </div>
                <div class="fs-4 fw-bold text-danger mb-0">{{ $kpi['menunggu'] }} <small class="fs-6 fw-normal text-muted">berkas</small></div>
                <div class="text-secondary" style="font-size: 0.7rem;">&bull; Memerlukan tanggapan Pokja</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card h-100 border-start border-4 border-warning js-card-filter shadow-sm" data-filter="kritis" style="cursor: pointer;" title="Klik untuk menyaring sanggahan yang kritis SLA">
            <div class="card-body py-2 px-3">
                <div class="text-secondary small d-flex justify-content-between align-items-center mb-1">
                    <span class="fw-semibold">SLA Kritis / Terlewat</span>
                    <i class="bi bi-alarm-fill text-warning"></i>
                </div>
                <div class="fs-4 fw-bold text-warning mb-0">{{ $kpi['kritis'] }} <small class="fs-6 fw-normal text-muted">berkas</small></div>
                <div class="text-secondary" style="font-size: 0.7rem;">&bull; Hari terakhir atau &gt;3 hari</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card h-100 border-start border-4 border-secondary js-card-filter shadow-sm" data-filter="ditolak" style="cursor: pointer;" title="Klik untuk menyaring sanggahan yang ditolak">
            <div class="card-body py-2 px-3">
                <div class="text-secondary small d-flex justify-content-between align-items-center mb-1">
                    <span class="fw-semibold">Sanggahan Ditolak</span>
                    <i class="bi bi-x-circle-fill text-secondary"></i>
                </div>
                <div class="fs-4 fw-bold text-secondary mb-0">{{ $kpi['ditolak'] }} <small class="fs-6 fw-normal text-muted">berkas</small></div>
                <div class="text-secondary" style="font-size: 0.7rem;">&bull; Selesai, tender berlanjut</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card h-100 border-start border-4 border-success js-card-filter shadow-sm" data-filter="diterima" style="cursor: pointer;" title="Klik untuk menyaring sanggahan yang diterima">
            <div class="card-body py-2 px-3">
                <div class="text-secondary small d-flex justify-content-between align-items-center mb-1">
                    <span class="fw-semibold">Sanggahan Diterima</span>
                    <i class="bi bi-check-circle-fill text-success"></i>
                </div>
                <div class="fs-4 fw-bold text-success mb-0">{{ $kpi['diterima'] }} <small class="fs-6 fw-normal text-muted">berkas</small></div>
                <div class="text-secondary" style="font-size: 0.7rem;">&bull; Evaluasi ulang / klarifikasi</div>
            </div>
        </div>
    </div>
</div>

{{-- Bar Alat & Filter Cepat --}}
<div class="card mb-3 shadow-sm border-0">
    <div class="card-body p-2 d-flex flex-wrap align-items-center justify-content-between gap-2">
        <div class="d-flex flex-wrap gap-1 align-items-center" id="filter-chips">
            <button type="button" class="btn btn-sm btn-dark js-chip active" data-filter="semua">
                Semua ({{ $kpi['total'] }})
            </button>
            <button type="button" class="btn btn-sm btn-outline-danger js-chip" data-filter="menunggu">
                Menunggu Jawaban ({{ $kpi['menunggu'] }})
            </button>
            <button type="button" class="btn btn-sm btn-outline-warning js-chip" data-filter="kritis">
                SLA Kritis ({{ $kpi['kritis'] }})
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary js-chip" data-filter="ditolak">
                Ditolak ({{ $kpi['ditolak'] }})
            </button>
            <button type="button" class="btn btn-sm btn-outline-success js-chip" data-filter="diterima">
                Diterima ({{ $kpi['diterima'] }})
            </button>
        </div>
        <div class="input-group input-group-sm" style="max-width: 280px;">
            <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
            <input type="text" id="cari-sanggahan" class="form-control border-start-0" placeholder="Cari paket / rekanan..." aria-label="Cari sanggahan">
        </div>
    </div>
</div>

{{-- Tabel Data Sanggahan --}}
<div class="card shadow-sm border-0">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" id="tabel-sanggahan" style="font-size: 0.85rem;">
            <thead class="table-light text-secondary text-uppercase font-monospace" style="font-size: 0.75rem;">
                <tr>
                    <th style="width: 140px;">Tgl Masuk & SLA</th>
                    <th>Paket Pengadaan</th>
                    <th style="width: 200px;">Penyedia Penyanggah</th>
                    <th>Materi & Sifat</th>
                    <th style="width: 120px;" class="text-center">Status</th>
                    <th style="width: 130px;" class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($sanggahans as $sg)
                    @php
                        $tglMasukFormatted = ($sg->tanggal_masuk ?? $sg->created_at)->translatedFormat('d M Y');
                        $tglDijawabFormatted = $sg->tanggal_dijawab ? $sg->tanggal_dijawab->translatedFormat('d M Y') : '-';
                        $filterCat = $sg->hasil;
                        if ($sg->hasil === 'menunggu' && in_array($sg->sla_status, ['terlewat', 'mendesak'])) {
                            $filterCat .= ' kritis';
                        }
                    @endphp
                    <tr class="js-row" data-filter="{{ $filterCat }}" data-sla="{{ $sg->sla_status }}" data-hasil="{{ $sg->hasil }}">
                        <td>
                            <div class="fw-semibold text-dark">{{ $tglMasukFormatted }}</div>
                            @if ($sg->hasil === 'menunggu')
                                <span class="badge bg-{{ $sg->sla_badge }}-subtle text-{{ $sg->sla_badge }} border border-{{ $sg->sla_badge }}-subtle" style="font-size: 0.7rem;">
                                    <i class="bi bi-clock me-1"></i>{{ $sg->sla_label }}
                                </span>
                            @else
                                <span class="badge bg-light text-secondary border" style="font-size: 0.7rem;">
                                    <i class="bi bi-check2-all me-1"></i>Dijawab: {{ $tglDijawabFormatted }}
                                </span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-1 mb-1">
                                <span class="font-monospace fw-semibold text-primary" style="font-size: 0.8rem;">{{ $sg->paket?->kode_paket ?? 'N/A' }}</span>
                                @if ($sg->paket?->opd)
                                    <span class="badge bg-light text-secondary border" style="font-size: 0.65rem;">{{ $sg->paket->opd->singkatan }}</span>
                                @endif
                            </div>
                            <div class="fw-medium text-dark text-truncate" style="max-width: 320px;" title="{{ $sg->paket?->nama_paket }}">
                                {{ $sg->paket?->nama_paket ?? 'Paket Terhapus' }}
                            </div>
                        </td>
                        <td>
                            <div class="fw-semibold text-dark text-truncate" style="max-width: 190px;" title="{{ $sg->penyedia?->nama }}">
                                <i class="bi bi-building me-1 text-muted"></i>{{ $sg->penyedia?->nama ?? 'Penyedia Tidak Ditemukan' }}
                            </div>
                            @if ($sg->penyedia?->telepon || $sg->penyedia?->email)
                                <div class="text-muted small text-truncate" style="font-size: 0.75rem;">
                                    {{ $sg->penyedia->telepon ?: $sg->penyedia->email }}
                                </div>
                            @endif
                        </td>
                        <td>
                            <div class="text-secondary text-truncate mb-1" style="max-width: 300px;" title="{{ $sg->catatan ?: 'Materi sanggahan' }}">
                                {{ $sg->catatan ?: 'Tanpa keterangan materi tertulis.' }}
                            </div>
                            @if ($sg->substantif)
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle" style="font-size: 0.68rem;">
                                    <i class="bi bi-file-earmark-text me-1"></i>Substantif
                                </span>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle" style="font-size: 0.68rem;">
                                    Non-Substantif
                                </span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if ($sg->hasil === 'menunggu')
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1">
                                    <i class="bi bi-hourglass-split me-1"></i>Menunggu
                                </span>
                            @elseif ($sg->hasil === 'diterima')
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                    <i class="bi bi-check-circle me-1"></i>Diterima
                                </span>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1">
                                    <i class="bi bi-x-circle me-1"></i>Ditolak
                                </span>
                            @endif
                        </td>
                        <td class="text-end">
                            @if ($sg->hasil === 'menunggu')
                                <button type="button" class="btn btn-sm btn-primary py-1 px-2 js-btn-jawab"
                                    data-id="{{ $sg->id }}"
                                    data-action="{{ route('pokja.sanggahan.jawab', $sg) }}"
                                    data-kode="{{ $sg->paket?->kode_paket }}"
                                    data-paket="{{ $sg->paket?->nama_paket }}"
                                    data-penyedia="{{ $sg->penyedia?->nama }}"
                                    data-tgl="{{ $tglMasukFormatted }}"
                                    data-catatan="{{ $sg->catatan }}">
                                    <i class="bi bi-reply-fill me-1"></i>Tanggapi
                                </button>
                            @else
                                <button type="button" class="btn btn-sm btn-outline-secondary py-1 px-2 js-btn-detail"
                                    data-kode="{{ $sg->paket?->kode_paket }}"
                                    data-paket="{{ $sg->paket?->nama_paket }}"
                                    data-penyedia="{{ $sg->penyedia?->nama }}"
                                    data-tgl-masuk="{{ $tglMasukFormatted }}"
                                    data-tgl-jawab="{{ $tglDijawabFormatted }}"
                                    data-hasil="{{ ucfirst($sg->hasil) }}"
                                    data-hasil-badge="{{ $sg->hasil === 'diterima' ? 'success' : 'secondary' }}"
                                    data-substantif="{{ $sg->substantif ? 'Ya, Berkas Substantif' : 'Non-Substantif' }}"
                                    data-catatan="{{ $sg->catatan }}">
                                    <i class="bi bi-eye me-1"></i>Rincian
                                </button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="bi bi-shield-check display-6 d-block mb-2 text-success"></i>
                            <div class="fw-semibold">Tidak Ada Sanggahan Aktif</div>
                            <small>Seluruh pekerjaan tender Pokja berjalan tertib tanpa sanggahan rekanan pada TA {{ $tahun }}.</small>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Modal Beri Tanggapan Resmi Sanggahan --}}
<div class="modal fade" id="modal-jawab-sanggahan" tabindex="-1" aria-labelledby="modalJawabLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form method="POST" id="form-jawab-sanggahan" action="">
                @csrf
                <div class="modal-header py-2 px-3" style="background-color: var(--navy-sidebar, #1e293b);">
                    <h6 class="modal-title fs-6 text-white" id="modalJawabLabel">
                        <i class="bi bi-reply-fill me-1"></i>Tanggapan Resmi Sanggahan
                    </h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-3">
                    <div class="bg-light border p-2 mb-3 rounded-1" style="font-size: 0.8rem;">
                        <div class="d-flex justify-content-between text-muted mb-1">
                            <span id="modal-jawab-kode" class="font-monospace fw-bold text-primary"></span>
                            <span id="modal-jawab-tgl"></span>
                        </div>
                        <div class="fw-semibold text-dark text-truncate mb-1" id="modal-jawab-paket"></div>
                        <div class="text-secondary"><i class="bi bi-building me-1"></i>Penyedia: <span id="modal-jawab-penyedia" class="fw-medium text-dark"></span></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary mb-1">Keputusan Akhir Pokja <span class="text-danger">*</span></label>
                        <div class="d-flex gap-3">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="hasil" id="hasil_ditolak" value="ditolak" checked>
                                <label class="form-check-label text-danger fw-semibold" for="hasil_ditolak">
                                    <i class="bi bi-x-circle me-1"></i>Sanggahan Ditolak
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="hasil" id="hasil_diterima" value="diterima">
                                <label class="form-check-label text-success fw-semibold" for="hasil_diterima">
                                    <i class="bi bi-check-circle me-1"></i>Sanggahan Diterima
                                </label>
                            </div>
                        </div>
                        <div class="form-text text-muted" style="font-size: 0.72rem;">
                            Jika ditolak, proses tender dilanjutkan. Jika diterima, Pokja melakukan evaluasi ulang atau revisi hasil.
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="substantif" value="1" id="substantif_check" checked>
                            <label class="form-check-label small fw-semibold text-dark" for="substantif_check">
                                Materi Sanggahan Bersifat Substantif (Teknis / Harga / Legalitas)
                            </label>
                        </div>
                    </div>

                    <div class="mb-2">
                        <label for="catatan_jawaban" class="form-label small fw-semibold text-secondary mb-1">Uraian / Jawaban Resmi Pokja <span class="text-danger">*</span></label>
                        <textarea class="form-control form-control-sm font-sans" id="catatan_jawaban" name="catatan" rows="4" required placeholder="Tuliskan dasar pertimbangan dan jawaban resmi sanggahan berdasarkan Berita Acara Hasil Pemilihan (BAHP)..."></textarea>
                    </div>
                </div>
                <div class="modal-footer py-2 px-3 bg-light border-top">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm btn-primary">
                        <i class="bi bi-send-check me-1"></i>Simpan & Terbitkan Jawaban
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal Rincian Sanggahan --}}
<div class="modal fade" id="modal-detail-sanggahan" tabindex="-1" aria-labelledby="modalDetailLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header py-2 px-3" style="background-color: var(--navy-sidebar, #1e293b);">
                <h6 class="modal-title fs-6 text-white" id="modalDetailLabel">
                    <i class="bi bi-file-earmark-check me-1"></i>Rincian Berkas Sanggahan
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3" style="font-size: 0.85rem;">
                <div class="mb-2">
                    <span id="detail-kode" class="font-monospace fw-bold text-primary"></span>
                    <h6 class="fw-bold text-dark mt-1 mb-0" id="detail-paket"></h6>
                </div>
                <hr class="my-2">
                <div class="row g-2 mb-2">
                    <div class="col-6">
                        <div class="text-secondary small">Penyedia:</div>
                        <div class="fw-semibold text-dark" id="detail-penyedia"></div>
                    </div>
                    <div class="col-6">
                        <div class="text-secondary small">Klasifikasi Sifat:</div>
                        <div class="fw-semibold text-dark" id="detail-substantif"></div>
                    </div>
                    <div class="col-6">
                        <div class="text-secondary small">Tanggal Masuk:</div>
                        <div class="text-dark" id="detail-tgl-masuk"></div>
                    </div>
                    <div class="col-6">
                        <div class="text-secondary small">Tanggal Dijawab:</div>
                        <div class="text-dark" id="detail-tgl-jawab"></div>
                    </div>
                </div>
                <div class="mb-2">
                    <div class="text-secondary small mb-1">Status Keputusan Pokja:</div>
                    <span id="detail-hasil-badge" class="badge px-2 py-1"></span>
                </div>
                <div class="mb-0">
                    <div class="text-secondary small mb-1">Catatan / Jawaban Resmi:</div>
                    <div class="p-2 bg-light border rounded text-dark" id="detail-catatan" style="white-space: pre-wrap;"></div>
                </div>
            </div>
            <div class="modal-footer py-2 px-3 bg-light border-top">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const chips = document.querySelectorAll('.js-chip');
    const cardFilters = document.querySelectorAll('.js-card-filter');
    const chipTriggers = document.querySelectorAll('.js-chip-trigger');
    const searchInput = document.getElementById('cari-sanggahan');
    const rows = document.querySelectorAll('#tabel-sanggahan tbody tr.js-row');

    let currentFilter = 'semua';
    let currentSearch = '';

    function applyFilters() {
        rows.forEach(row => {
            const filterCat = (row.dataset.filter || '').toLowerCase();
            const textContent = row.textContent.toLowerCase();

            let matchesFilter = true;
            if (currentFilter === 'semua') {
                matchesFilter = true;
            } else if (currentFilter === 'kritis') {
                matchesFilter = filterCat.includes('kritis');
            } else {
                matchesFilter = filterCat.includes(currentFilter);
            }

            const matchesSearch = currentSearch === '' || textContent.includes(currentSearch);

            if (matchesFilter && matchesSearch) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    function setActiveFilter(filter) {
        currentFilter = filter;
        chips.forEach(c => {
            if (c.dataset.filter === filter) {
                c.classList.remove('btn-outline-danger', 'btn-outline-warning', 'btn-outline-secondary', 'btn-outline-success');
                c.classList.add('btn-dark', 'active');
            } else {
                c.classList.remove('btn-dark', 'active');
                if (c.dataset.filter === 'menunggu') c.classList.add('btn-outline-danger');
                else if (c.dataset.filter === 'kritis') c.classList.add('btn-outline-warning');
                else if (c.dataset.filter === 'ditolak') c.classList.add('btn-outline-secondary');
                else if (c.dataset.filter === 'diterima') c.classList.add('btn-outline-success');
                else c.classList.add('btn-outline-dark');
            }
        });
        applyFilters();
    }

    chips.forEach(chip => {
        chip.addEventListener('click', function () {
            setActiveFilter(this.dataset.filter);
        });
    });

    cardFilters.forEach(card => {
        card.addEventListener('click', function () {
            setActiveFilter(this.dataset.filter);
        });
    });

    chipTriggers.forEach(btn => {
        btn.addEventListener('click', function () {
            setActiveFilter(this.dataset.filter);
        });
    });

    if (searchInput) {
        searchInput.addEventListener('input', function () {
            currentSearch = this.value.trim().toLowerCase();
            applyFilters();
        });
    }

    // Modal Jawab Sanggahan
    const modalJawabEl = document.getElementById('modal-jawab-sanggahan');
    const modalJawab = modalJawabEl ? new bootstrap.Modal(modalJawabEl) : null;
    const formJawab = document.getElementById('form-jawab-sanggahan');

    document.querySelectorAll('.js-btn-jawab').forEach(btn => {
        btn.addEventListener('click', function () {
            if (!formJawab || !modalJawab) return;
            formJawab.action = this.dataset.action;
            document.getElementById('modal-jawab-kode').textContent = this.dataset.kode;
            document.getElementById('modal-jawab-paket').textContent = this.dataset.paket;
            document.getElementById('modal-jawab-penyedia').textContent = this.dataset.penyedia;
            document.getElementById('modal-jawab-tgl').textContent = 'Masuk: ' + this.dataset.tgl;
            document.getElementById('catatan_jawaban').value = '';
            modalJawab.show();
        });
    });

    // Modal Detail Sanggahan
    const modalDetailEl = document.getElementById('modal-detail-sanggahan');
    const modalDetail = modalDetailEl ? new bootstrap.Modal(modalDetailEl) : null;
    document.querySelectorAll('.js-btn-detail').forEach(btn => {
        btn.addEventListener('click', function () {
            if (!modalDetail) return;
            document.getElementById('detail-kode').textContent = this.dataset.kode;
            document.getElementById('detail-paket').textContent = this.dataset.paket;
            document.getElementById('detail-penyedia').textContent = this.dataset.penyedia;
            document.getElementById('detail-substantif').textContent = this.dataset.substantif;
            document.getElementById('detail-tgl-masuk').textContent = this.dataset.tglMasuk;
            document.getElementById('detail-tgl-jawab').textContent = this.dataset.tglJawab;
            document.getElementById('detail-catatan').textContent = this.dataset.catatan || 'Tidak ada catatan tertulis.';

            const badge = document.getElementById('detail-hasil-badge');
            badge.textContent = this.dataset.hasil;
            badge.className = 'badge px-2 py-1 bg-' + this.dataset.hasilBadge + '-subtle text-' + this.dataset.hasilBadge + ' border border-' + this.dataset.hasilBadge + '-subtle';

            modalDetail.show();
        });
    });
});
</script>
@endpush
