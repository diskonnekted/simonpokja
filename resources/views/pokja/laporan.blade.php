@extends('layouts.app')

@section('title', 'Lembar Kendali Kepatuhan — ' . $pokja->nama)

@section('breadcrumb')
<ul class="breadcrumb-links">
    <li><a href="{{ route('dashboard') }}"><i class="bi bi-house-door me-1"></i>Beranda</a></li>
    <li class="sep"><i class="bi bi-chevron-right"></i></li>
    <li><a href="{{ route('pokja.dasbor') }}">{{ $pokja->nama }}</a></li>
    <li class="sep"><i class="bi bi-chevron-right"></i></li>
    <li class="active">Lembar Kendali</li>
</ul>
<div class="text-secondary small font-monospace d-none d-sm-block">
    <i class="bi bi-file-earmark-check me-1"></i>Dokumen Kendali (TA {{ $tahun }})
</div>
@endsection

@section('content')
<style>
    /* Styling Cetak Khusus Lembar Kendali Pemerintah */
    @media print {
        body {
            background-color: #ffffff !important;
            color: #000000 !important;
            font-size: 10pt !important;
        }
        .sidebar, .topbar, .breadcrumb-bar, .no-print, .btn, form {
            display: none !important;
        }
        .main-content {
            margin: 0 !important;
            padding: 0 !important;
            width: 100% !important;
        }
        .lembar-cetak {
            border: none !important;
            box-shadow: none !important;
            padding: 0 !important;
            margin: 0 !important;
            width: 100% !important;
        }
        .kop-surat {
            display: block !important;
        }
        table {
            border-collapse: collapse !important;
            width: 100% !important;
            page-break-inside: auto;
        }
        tr {
            page-break-inside: avoid;
            page-break-after: auto;
        }
        thead {
            display: table-header-group;
        }
        th, td {
            border: 1px solid #333333 !important;
            padding: 4px 6px !important;
            font-size: 8.5pt !important;
        }
        .badge {
            border: 1px solid #555555 !important;
            color: #000000 !important;
            background: transparent !important;
        }
        .ttd-section {
            page-break-inside: avoid;
            margin-top: 30px !important;
        }
    }

    .kop-garis-ganda {
        border-top: 2px solid #0f172a;
        border-bottom: 1px solid #0f172a;
        height: 4px;
        margin: 8px 0 16px 0;
    }
</style>

{{-- Bar Aksi di Layar (Disembunyikan saat cetak) --}}
<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2 no-print">
    <div>
        <h1 class="h4 mb-0">Lembar Kendali Kepatuhan Pokja</h1>
        <small class="text-secondary">{{ $pokja->nama }} &bull; Format Resmi Penyerahan Dokumen Pengawasan & Evaluasi Progres</small>
    </div>
    <div class="d-flex gap-2 align-items-center flex-wrap">
        <button type="button" class="btn btn-sm btn-dark" onclick="window.print()">
            <i class="bi bi-printer-fill me-1"></i>Cetak / Simpan PDF
        </button>
        <form method="GET" action="{{ route('pokja.laporan') }}" class="d-flex gap-2 align-items-center flex-wrap">
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

{{-- Lembar Kerja / Dokumen Hasil --}}
<div class="card shadow-sm border-0 lembar-cetak p-3 p-md-4 bg-white">
    {{-- Kop Dokumen Resmi --}}
    <div class="kop-surat text-center mb-2">
        <div class="d-flex align-items-center justify-content-center gap-3">
            <div class="text-center">
                <h6 class="fw-bold mb-0 text-uppercase" style="letter-spacing: 0.5px; font-size: 0.95rem;">Pemerintah Kabupaten Banjarnegara</h6>
                <h5 class="fw-bold mb-0 text-uppercase" style="letter-spacing: 0.5px; font-size: 1.05rem;">Sekretariat Daerah</h5>
                <div class="fw-semibold text-uppercase" style="font-size: 0.85rem;">Bagian Pengadaan Barang dan Jasa (UKPBJ / LPSE)</div>
                <small class="text-muted" style="font-size: 0.75rem;">Jl. Dipayuda No. 01 Banjarnegara, Jawa Tengah 53412 &bull; Sistem Monitoring Pokja (SIMONPOKJA)</small>
            </div>
        </div>
        <div class="kop-garis-ganda"></div>
        <h5 class="fw-bold text-uppercase mt-2 mb-1" style="font-size: 1rem; text-decoration: underline;">
            Lembar Kendali Kepatuhan & Progres Pemilihan
        </h5>
        <div class="text-secondary small font-monospace">
            Unit Kerja: {{ $pokja->nama }} (Bidang {{ $pokja->bidang }}) &bull; Tahun Anggaran {{ $tahun }}
        </div>
    </div>

    {{-- Ringkasan Parameter Dokumen --}}
    <div class="row g-2 mb-3 mt-1" style="font-size: 0.85rem;">
        <div class="col-md-6">
            <table class="table table-sm table-borderless mb-0">
                <tr>
                    <td class="text-secondary ps-0 py-0" style="width: 140px;">Nama Panitia/Pokja</td>
                    <td class="fw-semibold py-0">: {{ $pokja->nama }}</td>
                </tr>
                <tr>
                    <td class="text-secondary ps-0 py-0">Bidang Pengadaan</td>
                    <td class="fw-medium py-0">: {{ $pokja->bidang }}</td>
                </tr>
                <tr>
                    <td class="text-secondary ps-0 py-0">Personel Bertugas</td>
                    <td class="fw-medium py-0">: {{ $user->name }}</td>
                </tr>
            </table>
        </div>
        <div class="col-md-6">
            <table class="table table-sm table-borderless mb-0">
                <tr>
                    <td class="text-secondary ps-0 py-0" style="width: 140px;">Tanggal Dokumen</td>
                    <td class="fw-semibold py-0">: {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</td>
                </tr>
                <tr>
                    <td class="text-secondary ps-0 py-0">Total Paket Kelolaan</td>
                    <td class="fw-medium py-0">: {{ $ringkasan['totalPaket'] }} Paket Pekerjaan</td>
                </tr>
                <tr>
                    <td class="text-secondary ps-0 py-0">Total Nilai Pagu</td>
                    <td class="fw-medium py-0">: Rp {{ number_format($ringkasan['totalPagu'], 0, ',', '.') }}</td>
                </tr>
            </table>
        </div>
    </div>

    {{-- 4 Kotak Ringkasan Eksekutif --}}
    <div class="row g-2 mb-3 no-print">
        <div class="col-6 col-lg-3">
            <div class="p-2 border rounded bg-light">
                <div class="text-secondary small">Total Paket & Pagu</div>
                <div class="fw-bold fs-6 text-dark">{{ $ringkasan['totalPaket'] }} Paket</div>
                <div class="text-muted small" style="font-size: 0.7rem;">Pagu: Rp {{ number_format($ringkasan['totalPagu'], 0, ',', '.') }}</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="p-2 border rounded bg-light">
                <div class="text-secondary small">Rata-Rata Progres Fisik</div>
                <div class="fw-bold fs-6 text-primary">{{ $ringkasan['rataProgress'] }}%</div>
                <div class="text-muted small" style="font-size: 0.7rem;">Tuntas: {{ $ringkasan['selesai'] }} &bull; Aktif: {{ $ringkasan['aktif'] }}</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="p-2 border rounded bg-light">
                <div class="text-secondary small">Sanggahan Rekanan</div>
                <div class="fw-bold fs-6 {{ $ringkasan['sanggahanAktif'] > 0 ? 'text-danger' : 'text-success' }}">
                    {{ $ringkasan['sanggahanAktif'] }} Aktif
                </div>
                <div class="text-muted small" style="font-size: 0.7rem;">{{ $ringkasan['sanggahanAktif'] > 0 ? 'Perlu tindakan tanggapan' : 'Tertib tanpa sanggahan' }}</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="p-2 border rounded bg-light">
                <div class="text-secondary small">Addendum / Ubah Jadwal</div>
                <div class="fw-bold fs-6 text-dark">{{ $ringkasan['perubahanJadwal'] }} Kali</div>
                <div class="text-muted small" style="font-size: 0.7rem;">Riwayat pergeseran linimasa</div>
            </div>
        </div>
    </div>

    {{-- Tabel Utama Kendali Kepatuhan --}}
    <div class="table-responsive mb-3">
        <table class="table table-bordered align-middle mb-0" style="font-size: 0.8rem;">
            <thead class="table-light text-secondary text-uppercase text-center font-monospace" style="font-size: 0.72rem;">
                <tr>
                    <th style="width: 35px;">No</th>
                    <th style="width: 130px;">Kode Paket</th>
                    <th>Nama Pekerjaan & OPD</th>
                    <th style="width: 130px;">Pagu / Kontrak</th>
                    <th style="width: 95px;">Tahapan</th>
                    <th style="width: 80px;">Fisik</th>
                    <th style="width: 80px;">Sanggah</th>
                    <th style="width: 80px;">Jadwal</th>
                    <th style="width: 100px;">Kepatuhan</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($pakets as $idx => $p)
                    @php
                        $sanggahAktif = $p->sanggahans->where('hasil', 'menunggu')->count();
                        $jadwalCount = $p->perubahanJadwals->count();
                        $tanpaBaCount = $p->perubahanJadwals->where('ada_berita_acara', 0)->count();
                        $isTerlambat = $p->status_deadline[0] === 'lewat';

                        $isPatuh = ($sanggahAktif === 0) && ($tanpaBaCount === 0) && (!$isTerlambat);
                    @endphp
                    <tr>
                        <td class="text-center">{{ $idx + 1 }}</td>
                        <td class="font-monospace fw-semibold text-primary">{{ $p->kode_paket }}</td>
                        <td>
                            <div class="fw-semibold text-dark">{{ $p->nama_paket }}</div>
                            <small class="text-muted">{{ $p->opd?->nama ?? 'Perangkat Daerah' }}</small>
                            @if ($p->penyedia)
                                <div class="text-secondary" style="font-size: 0.72rem;">
                                    <i class="bi bi-building me-1"></i>{{ $p->penyedia->nama }}
                                </div>
                            @endif
                        </td>
                        <td class="text-end font-monospace">
                            <div class="fw-medium text-dark">Rp {{ number_format($p->pagu, 0, ',', '.') }}</div>
                            @if ($p->nilai_kontrak)
                                <small class="text-muted" style="font-size: 0.7rem;">Kontrak: Rp {{ number_format($p->nilai_kontrak, 0, ',', '.') }}</small>
                            @endif
                        </td>
                        <td class="text-center">
                            <span class="badge bg-light text-secondary border font-sans" style="font-size: 0.7rem;">
                                {{ ucfirst($p->status) }}
                            </span>
                        </td>
                        <td class="text-center">
                            <span class="fw-bold font-monospace {{ $p->progress >= 100 ? 'text-success' : 'text-primary' }}">
                                {{ $p->progress }}%
                            </span>
                        </td>
                        <td class="text-center">
                            @if ($sanggahAktif > 0)
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle" style="font-size: 0.68rem;">
                                    {{ $sanggahAktif }} Aktif
                                </span>
                            @else
                                <span class="text-muted" style="font-size: 0.75rem;">Nihil</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if ($jadwalCount > 0)
                                <span class="badge {{ $tanpaBaCount > 0 ? 'bg-danger-subtle text-danger' : 'bg-light text-secondary border' }}" style="font-size: 0.68rem;">
                                    {{ $jadwalCount }}x
                                </span>
                            @else
                                <span class="text-muted" style="font-size: 0.75rem;">0</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if ($isPatuh)
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1" style="font-size: 0.7rem;">
                                    <i class="bi bi-check-circle me-1"></i>Patuh
                                </span>
                            @else
                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-1" style="font-size: 0.7rem;">
                                    <i class="bi bi-exclamation-triangle me-1"></i>Perhatian
                                </span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center py-4 text-muted">
                            Belum ada paket pengadaan terdaftar pada Tahun Anggaran {{ $tahun }}.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Catatan Pengawasan --}}
    <div class="mb-4" style="font-size: 0.8rem;">
        <div class="fw-bold text-secondary text-uppercase mb-1" style="font-size: 0.72rem;">Catatan Verifikasi Panitia:</div>
        <div class="p-2 border rounded bg-light text-secondary">
            Lembar kendali ini diverifikasi secara otomatis oleh sistem SIMONPOKJA berdasarkan data Berita Acara, riwayat addendum jadwal, dan penyelesaian tanggapan sanggahan rekanan per tanggal {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}.
        </div>
    </div>

    {{-- Kolom Pengesahan Tanda Tangan Fisik --}}
    <div class="ttd-section">
        <div class="row text-center" style="font-size: 0.85rem;">
            <div class="col-6">
                <div>Mengetahui,</div>
                <div class="fw-semibold">Kepala Bagian Pengadaan Barang dan Jasa / Koordinator LPSE</div>
                <div style="height: 65px;"></div>
                <div class="fw-bold text-decoration-underline">( ........................................................... )</div>
                <div class="text-muted small">NIP. .....................................................</div>
            </div>
            <div class="col-6">
                <div>Banjarnegara, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</div>
                <div class="fw-semibold">Ketua / Anggota {{ $pokja->nama }}</div>
                <div style="height: 65px;"></div>
                <div class="fw-bold text-decoration-underline">{{ $user->name }}</div>
                <div class="text-muted small">NIP. {{ $user->nip ?? '.....................................................' }}</div>
            </div>
        </div>
    </div>
</div>
@endsection
