@extends('layouts.app')

@section('title', 'Jadwal & Evaluasi — ' . $pokja->nama)

@section('breadcrumb')
<ul class="breadcrumb-links">
    <li><a href="{{ route('dashboard') }}"><i class="bi bi-house-door me-1"></i>Beranda</a></li>
    <li class="sep"><i class="bi bi-chevron-right"></i></li>
    <li><a href="{{ route('pokja.dasbor') }}">{{ $pokja->nama }}</a></li>
    <li class="sep"><i class="bi bi-chevron-right"></i></li>
    <li class="active">Jadwal & Evaluasi</li>
</ul>
<div class="text-secondary small font-monospace d-none d-sm-block">
    <i class="bi bi-calendar-week me-1"></i>Timeline & Addendum (TA {{ $tahun }})
</div>
@endsection

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
    <div>
        <h1 class="h4 mb-0">Jadwal & Evaluasi Tahapan</h1>
        <small class="text-secondary">{{ $pokja->nama }} &bull; Pengawasan Linimasa Pemilihan & Audit Addendum Jadwal</small>
    </div>
    <div class="d-flex gap-2 align-items-center flex-wrap">
        @if ($bolehSimpan)
            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modal-tambah-perubahan">
                <i class="bi bi-plus-circle me-1"></i>Catat Perubahan Jadwal
            </button>
        @endif
        <form method="GET" action="{{ route('pokja.jadwal') }}" class="d-flex gap-2 align-items-center flex-wrap">
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
</div>

@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show py-2 px-3 mb-3 small" role="alert">
        <i class="bi bi-check-circle-fill me-1"></i> {{ session('success') }}
        <button type="button" class="btn-close py-2" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if ($kpi['tanpaBa'] > 0 || $kpi['paketRisikoDelay'] > 0)
    <div class="alert alert-warning py-2 px-3 mb-3 small d-flex align-items-center justify-content-between gap-2 border-warning-subtle">
        <div>
            <i class="bi bi-exclamation-triangle-fill me-2 text-warning"></i>
            <strong>Peringatan Kepatuhan:</strong>
            @if ($kpi['tanpaBa'] > 0)
                Terdapat <strong>{{ $kpi['tanpaBa'] }}</strong> perubahan jadwal tanpa Berita Acara (BA) resmi.
            @endif
            @if ($kpi['paketRisikoDelay'] > 0)
                Sebanyak <strong>{{ $kpi['paketRisikoDelay'] }}</strong> paket telah mengalami pergeseran jadwal lebih dari 3 kali.
            @endif
        </div>
        <button type="button" class="btn btn-sm btn-outline-dark py-0 px-2 js-show-risk-packages">
            Tinjau Paket
        </button>
    </div>
@endif

{{-- 4 Metrik Taktis Jadwal --}}
<div class="row g-2 mb-3">
    <div class="col-6 col-lg-3">
        <div class="card h-100 border-start border-4 border-primary shadow-sm">
            <div class="card-body py-2 px-3">
                <div class="text-secondary small d-flex justify-content-between align-items-center mb-1">
                    <span class="fw-semibold">Paket Tahap Pemilihan</span>
                    <i class="bi bi-diagram-3-fill text-primary"></i>
                </div>
                <div class="fs-4 fw-bold text-primary mb-0">{{ $kpi['paketPemilihan'] }} <small class="fs-6 fw-normal text-muted">paket</small></div>
                <div class="text-secondary" style="font-size: 0.7rem;">&bull; Persiapan & tender aktif</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card h-100 border-start border-4 border-info shadow-sm">
            <div class="card-body py-2 px-3">
                <div class="text-secondary small d-flex justify-content-between align-items-center mb-1">
                    <span class="fw-semibold">Total Perubahan Jadwal</span>
                    <i class="bi bi-calendar-range text-info"></i>
                </div>
                <div class="fs-4 fw-bold text-info mb-0">{{ $kpi['totalPerubahan'] }} <small class="fs-6 fw-normal text-muted">kali</small></div>
                <div class="text-secondary" style="font-size: 0.7rem;">&bull; Log addendum tercatat</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card h-100 border-start border-4 {{ $kpi['tanpaBa'] > 0 ? 'border-danger' : 'border-success' }} shadow-sm">
            <div class="card-body py-2 px-3">
                <div class="text-secondary small d-flex justify-content-between align-items-center mb-1">
                    <span class="fw-semibold">Perubahan Tanpa BA</span>
                    <i class="bi bi-file-earmark-x-fill {{ $kpi['tanpaBa'] > 0 ? 'text-danger' : 'text-success' }}"></i>
                </div>
                <div class="fs-4 fw-bold {{ $kpi['tanpaBa'] > 0 ? 'text-danger' : 'text-success' }} mb-0">{{ $kpi['tanpaBa'] }} <small class="fs-6 fw-normal text-muted">kejadian</small></div>
                <div class="text-secondary" style="font-size: 0.7rem;">&bull; {{ $kpi['tanpaBa'] > 0 ? 'Wajib segera dilengkapi BA' : 'Seluruh perubahan ber-BA' }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card h-100 border-start border-4 {{ $kpi['paketRisikoDelay'] > 0 ? 'border-warning' : 'border-success' }} shadow-sm">
            <div class="card-body py-2 px-3">
                <div class="text-secondary small d-flex justify-content-between align-items-center mb-1">
                    <span class="fw-semibold">Paket Diubah &gt;3 Kali</span>
                    <i class="bi bi-clock-history {{ $kpi['paketRisikoDelay'] > 0 ? 'text-warning' : 'text-success' }}"></i>
                </div>
                <div class="fs-4 fw-bold {{ $kpi['paketRisikoDelay'] > 0 ? 'text-warning' : 'text-success' }} mb-0">{{ $kpi['paketRisikoDelay'] }} <small class="fs-6 fw-normal text-muted">paket</small></div>
                <div class="text-secondary" style="font-size: 0.7rem;">&bull; Indikator risiko keterlambatan</div>
            </div>
        </div>
    </div>
</div>

{{-- Tab Navigasi Taktis --}}
<div class="card shadow-sm border-0 mb-3">
    <div class="card-header bg-white border-bottom p-2">
        <ul class="nav nav-pills card-header-pills" id="tabJadwal" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active py-1 px-3 small fw-semibold" id="tab-log-btn" data-bs-toggle="pill" data-bs-target="#tab-log" type="button" role="tab" aria-selected="true">
                    <i class="bi bi-journal-text me-1"></i>Log Perubahan Jadwal ({{ $perubahanJadwals->count() }})
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link py-1 px-3 small fw-semibold" id="tab-matriks-btn" data-bs-toggle="pill" data-bs-target="#tab-matriks" type="button" role="tab" aria-selected="false">
                    <i class="bi bi-calendar3 me-1"></i>Matriks Paket & Tahapan ({{ $pakets->count() }})
                </button>
            </li>
        </ul>
    </div>

    <div class="card-body p-0">
        <div class="tab-content" id="tabJadwalContent">
            {{-- TAB 1: Log Perubahan Jadwal --}}
            <div class="tab-pane fade show active" id="tab-log" role="tabpanel" aria-labelledby="tab-log-btn">
                <div class="p-2 border-bottom d-flex justify-content-between align-items-center gap-2 bg-light">
                    <div class="small text-secondary">
                        <i class="bi bi-info-circle me-1"></i>Riwayat addendum tanggal dan tahapan pengadaan yang dicatat panitia.
                    </div>
                    <div class="input-group input-group-sm" style="max-width: 260px;">
                        <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" id="cari-perubahan" class="form-control border-start-0" placeholder="Cari paket / tahapan...">
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="tabel-perubahan" style="font-size: 0.85rem;">
                        <thead class="table-light text-secondary text-uppercase font-monospace" style="font-size: 0.75rem;">
                            <tr>
                                <th style="width: 140px;">Waktu Perubahan</th>
                                <th>Paket Pengadaan</th>
                                <th style="width: 180px;">Tahap Terkait</th>
                                <th style="width: 130px;">Jenis</th>
                                <th>Alasan / Justifikasi</th>
                                <th style="width: 130px;" class="text-center">Berita Acara (BA)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($perubahanJadwals as $pj)
                                @php
                                    $tglWaktu = $pj->tanggal ? $pj->tanggal->translatedFormat('d M Y') . ($pj->jam ? ' ' . substr($pj->jam, 0, 5) : '') : $pj->created_at->translatedFormat('d M Y H:i');
                                @endphp
                                <tr class="js-row-perubahan">
                                    <td>
                                        <div class="fw-semibold text-dark">{{ $tglWaktu }}</div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-1 mb-1">
                                            <span class="font-monospace fw-semibold text-primary" style="font-size: 0.8rem;">{{ $pj->paket?->kode_paket ?? 'N/A' }}</span>
                                            <span class="badge bg-light text-secondary border" style="font-size: 0.65rem;">{{ ucfirst($pj->paket?->status ?? '-') }}</span>
                                        </div>
                                        <div class="fw-medium text-dark text-truncate" style="max-width: 320px;" title="{{ $pj->paket?->nama_paket }}">
                                            {{ $pj->paket?->nama_paket ?? 'Paket Terhapus' }}
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle font-sans">
                                            {{ $pj->tahap_terkait ?: 'Tahapan Umum' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border font-monospace">
                                            {{ ucfirst($pj->jenis ?: 'Perubahan') }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="text-dark text-truncate" style="max-width: 350px;" title="{{ $pj->alasan }}">
                                            {{ $pj->alasan ?: 'Penyesuaian jadwal operasional pemilihan.' }}
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        @if ($pj->ada_berita_acara)
                                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                                <i class="bi bi-check-circle-fill me-1"></i>Ada BA
                                            </span>
                                        @else
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1">
                                                <i class="bi bi-exclamation-circle-fill me-1"></i>Tanpa BA
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        <i class="bi bi-calendar-check display-6 d-block mb-2 text-success"></i>
                                        <div class="fw-semibold">Tidak Ada Riwayat Perubahan Jadwal</div>
                                        <small>Seluruh jadwal pengadaan berjalan sesuai linimasa awal.</small>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- TAB 2: Matriks Paket & Tahapan --}}
            <div class="tab-pane fade" id="tab-matriks" role="tabpanel" aria-labelledby="tab-matriks-btn">
                <div class="p-2 border-bottom d-flex justify-content-between align-items-center gap-2 bg-light">
                    <div class="small text-secondary">
                        <i class="bi bi-list-check me-1"></i>Ringkasan tahapan tender dan intensitas perubahan jadwal per paket.
                    </div>
                    <div class="input-group input-group-sm" style="max-width: 260px;">
                        <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" id="cari-matriks" class="form-control border-start-0" placeholder="Cari nama / kode...">
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="tabel-matriks" style="font-size: 0.85rem;">
                        <thead class="table-light text-secondary text-uppercase font-monospace" style="font-size: 0.75rem;">
                            <tr>
                                <th style="width: 140px;">Kode Paket</th>
                                <th>Nama Pekerjaan & OPD</th>
                                <th style="width: 130px;">Status Progres</th>
                                <th style="width: 150px;">Perubahan Jadwal</th>
                                <th style="width: 150px;">Deadline Penyelesaian</th>
                                <th style="width: 110px;" class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($pakets as $p)
                                @php
                                    $nPerubahan = $p->perubahanJadwals->count();
                                    $isHighRisk = $nPerubahan > 3;
                                @endphp
                                <tr class="js-row-matriks {{ $isHighRisk ? 'table-warning-subtle' : '' }}" data-risk="{{ $isHighRisk ? 'high' : 'normal' }}">
                                    <td>
                                        <span class="font-monospace fw-semibold text-primary">{{ $p->kode_paket }}</span>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-dark">{{ $p->nama_paket }}</div>
                                        <small class="text-muted">{{ $p->opd?->singkatan ?? 'OPD' }} &bull; Pagu: Rp {{ number_format($p->pagu, 0, ',', '.') }}</small>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="progress flex-grow-1" style="height: 6px;">
                                                <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $p->progress }}%;" aria-valuenow="{{ $p->progress }}" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                            <span class="fw-bold font-monospace" style="font-size: 0.75rem;">{{ $p->progress }}%</span>
                                        </div>
                                        <div class="text-secondary small" style="font-size: 0.72rem;">Status: {{ ucfirst($p->status) }}</div>
                                    </td>
                                    <td>
                                        @if ($nPerubahan > 0)
                                            <span class="badge {{ $isHighRisk ? 'bg-danger-subtle text-danger border border-danger-subtle' : 'bg-light text-secondary border' }}">
                                                <i class="bi bi-clock-history me-1"></i>{{ $nPerubahan }}x diubah
                                            </span>
                                            @if ($isHighRisk)
                                                <div class="text-danger fw-semibold" style="font-size: 0.68rem;">&bull; Risiko delay tinggi</div>
                                            @endif
                                        @else
                                            <span class="badge bg-success-subtle text-success border border-success-subtle">
                                                <i class="bi bi-check2 me-1"></i>Jadwal Asli
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="fw-medium text-dark">{{ $p->tanggal_selesai?->translatedFormat('d M Y') ?? '-' }}</div>
                                        <span class="badge bg-{{ $p->status_deadline[1] }}-subtle text-{{ $p->status_deadline[1] }}" style="font-size: 0.68rem;">
                                            {{ $p->status_deadline[2] }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        @if ($bolehSimpan)
                                            <button type="button" class="btn btn-sm btn-outline-primary py-1 px-2 js-btn-catat-paket"
                                                data-paket-id="{{ $p->id }}"
                                                data-paket-kode="{{ $p->kode_paket }}"
                                                data-paket-nama="{{ $p->nama_paket }}">
                                                <i class="bi bi-plus me-1"></i>Catat
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        <i class="bi bi-inbox display-6 d-block mb-2"></i>
                                        <div>Belum Ada Paket Terdaftar</div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal Catat Perubahan Jadwal --}}
@if ($bolehSimpan)
<div class="modal fade" id="modal-tambah-perubahan" tabindex="-1" aria-labelledby="modalTambahPerubahanLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form method="POST" id="form-tambah-perubahan" action="">
                @csrf
                <div class="modal-header py-2 px-3" style="background-color: var(--navy-sidebar, #1e293b);">
                    <h6 class="modal-title fs-6 text-white" id="modalTambahPerubahanLabel">
                        <i class="bi bi-calendar-plus me-1"></i>Catat Perubahan Jadwal Pengadaan
                    </h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-3">
                    <div class="mb-3">
                        <label for="pilih_paket_perubahan" class="form-label small fw-semibold text-secondary mb-1">Paket Pengadaan <span class="text-danger">*</span></label>
                        <select id="pilih_paket_perubahan" name="paket_id_select" class="form-select form-select-sm" required>
                            <option value="">-- Pilih Paket Pengadaan --</option>
                            @foreach ($pakets as $p)
                                <option value="{{ $p->id }}" data-action="{{ route('pokja.jadwal.perubahan', $p) }}">
                                    [{{ $p->kode_paket }}] {{ $p->nama_paket }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-7">
                            <label for="tanggal_perubahan" class="form-label small fw-semibold text-secondary mb-1">Tanggal Perubahan <span class="text-danger">*</span></label>
                            <input type="date" class="form-control form-control-sm" id="tanggal_perubahan" name="tanggal" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-5">
                            <label for="jam_perubahan" class="form-label small fw-semibold text-secondary mb-1">Jam</label>
                            <input type="time" class="form-control form-control-sm" id="jam_perubahan" name="jam" value="{{ date('H:i') }}">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="tahap_terkait" class="form-label small fw-semibold text-secondary mb-1">Tahapan yang Diubah <span class="text-danger">*</span></label>
                        <input type="text" list="daftar_tahapan" class="form-control form-control-sm" id="tahap_terkait" name="tahap_terkait" required placeholder="Contoh: Evaluasi Penawaran, Pembuktian Kualifikasi">
                        <datalist id="daftar_tahapan">
                            <option value="Pemberian Penjelasan (Aanwijzing)">
                            <option value="Pemasukan Dokumen Penawaran">
                            <option value="Pembukaan Dokumen Penawaran">
                            <option value="Evaluasi Administrasi & Kualifikasi">
                            <option value="Evaluasi Teknis">
                            <option value="Evaluasi Harga & Biaya">
                            <option value="Pembuktian Kualifikasi">
                            <option value="Penetapan & Pengumuman Pemenang">
                            <option value="Masa Sanggah">
                            <option value="Surat Penunjukan Penyedia (SPPBJ)">
                        </datalist>
                    </div>

                    <div class="mb-3">
                        <label for="jenis_perubahan" class="form-label small fw-semibold text-secondary mb-1">Jenis Perubahan <span class="text-danger">*</span></label>
                        <select id="jenis_perubahan" name="jenis" class="form-select form-select-sm" required>
                            <option value="pengunduran">Pengunduran Jadwal (Mundur)</option>
                            <option value="perpanjangan">Perpanjangan Waktu</option>
                            <option value="penyesuaian">Penyesuaian Tahapan</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="alasan_perubahan" class="form-label small fw-semibold text-secondary mb-1">Alasan / Dasar Perubahan <span class="text-danger">*</span></label>
                        <textarea class="form-control form-control-sm" id="alasan_perubahan" name="alasan" rows="3" required placeholder="Tuliskan alasan teknis pergeseran jadwal..."></textarea>
                    </div>

                    <div class="mb-2">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="ada_berita_acara" value="1" id="ada_ba_check" checked>
                            <label class="form-check-label small fw-semibold text-dark" for="ada_ba_check">
                                Perubahan Disertai Berita Acara (BA) Resmi Pokja
                            </label>
                        </div>
                        <div class="form-text text-muted" style="font-size: 0.72rem;">
                            Catatan: Perubahan jadwal tanpa BA akan memicu peringatan kepatuhan EWS.
                        </div>
                    </div>
                </div>
                <div class="modal-footer py-2 px-3 bg-light border-top">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm btn-primary">
                        <i class="bi bi-save me-1"></i>Simpan Perubahan Jadwal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Pencarian tabel log perubahan
    const cariPerubahan = document.getElementById('cari-perubahan');
    const rowsPerubahan = document.querySelectorAll('#tabel-perubahan tbody tr.js-row-perubahan');
    if (cariPerubahan) {
        cariPerubahan.addEventListener('input', function () {
            const val = this.value.trim().toLowerCase();
            rowsPerubahan.forEach(r => {
                r.style.display = r.textContent.toLowerCase().includes(val) ? '' : 'none';
            });
        });
    }

    // Pencarian tabel matriks paket
    const cariMatriks = document.getElementById('cari-matriks');
    const rowsMatriks = document.querySelectorAll('#tabel-matriks tbody tr.js-row-matriks');
    if (cariMatriks) {
        cariMatriks.addEventListener('input', function () {
            const val = this.value.trim().toLowerCase();
            rowsMatriks.forEach(r => {
                r.style.display = r.textContent.toLowerCase().includes(val) ? '' : 'none';
            });
        });
    }

    // Trigger lihat paket risiko dari alert banner
    const btnShowRisk = document.querySelector('.js-show-risk-packages');
    if (btnShowRisk) {
        btnShowRisk.addEventListener('click', function () {
            const tabMatriksBtn = document.getElementById('tab-matriks-btn');
            if (tabMatriksBtn) {
                const tab = new bootstrap.Tab(tabMatriksBtn);
                tab.show();
                if (cariMatriks) {
                    cariMatriks.value = '';
                }
                rowsMatriks.forEach(r => {
                    r.style.display = r.dataset.risk === 'high' ? '' : 'none';
                });
            }
        });
    }

    // Modal Catat Perubahan
    const selectPaket = document.getElementById('pilih_paket_perubahan');
    const formPerubahan = document.getElementById('form-tambah-perubahan');
    const modalTambahPerubahanEl = document.getElementById('modal-tambah-perubahan');
    const modalTambahPerubahan = modalTambahPerubahanEl ? new bootstrap.Modal(modalTambahPerubahanEl) : null;

    if (selectPaket && formPerubahan) {
        selectPaket.addEventListener('change', function () {
            const selectedOpt = this.options[this.selectedIndex];
            if (selectedOpt && selectedOpt.dataset.action) {
                formPerubahan.action = selectedOpt.dataset.action;
            } else {
                formPerubahan.action = '';
            }
        });

        // Tombol aksi di tabel matriks
        document.querySelectorAll('.js-btn-catat-paket').forEach(btn => {
            btn.addEventListener('click', function () {
                const pId = this.dataset.paketId;
                if (selectPaket) {
                    selectPaket.value = pId;
                    selectPaket.dispatchEvent(new Event('change'));
                }
                if (modalTambahPerubahan) {
                    modalTambahPerubahan.show();
                }
            });
        });
    }
});
</script>
@endpush
