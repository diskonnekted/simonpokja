@extends('layouts.app')

@section('title', 'Pemantauan Paket')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-start mb-4 gap-2">
    <div class="d-flex gap-2">
        <a href="{{ route('pemantauan.index') }}" class="btn btn-light btn-sm mt-1"><i class="bi bi-arrow-left"></i></a>
        <div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <h4 class="fw-bold mb-0">{{ $paket->nama_paket }}</h4>
                <span class="badge text-bg-{{ ['rendah' => 'success', 'sedang' => 'warning text-dark', 'kritis' => 'danger'][$paket->risiko] }}">{{ ucfirst($paket->risiko) }}</span>
                @if ($paket->tender_gagal)
                    <span class="badge text-bg-danger"><i class="bi bi-x-octagon me-1"></i>Tender Gagal</span>
                @endif
            </div>
            <p class="text-secondary mb-0 small">
                {{ $paket->kode_paket }} &bull; {{ $paket->opd->nama ?? '-' }} &bull;
                Pokja: <a href="{{ route('pokja.show', $paket->pokja) }}" class="text-decoration-none">{{ $paket->pokja->nama ?? '-' }}</a> &bull;
                {{ format_rupiah($paket->pagu) }}
            </p>
        </div>
    </div>
    <a href="{{ route('paket.show', $paket) }}" class="btn btn-outline-primary btn-sm"><i class="bi bi-box-seam me-1"></i>Detail Paket</a>
</div>

<div class="row g-3">
    {{-- ===== Riwayat Perubahan Jadwal ===== --}}
    <div class="col-lg-7">
        <div class="card mb-3">
            <div class="card-header bg-white pt-3 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0"><i class="bi bi-hourglass-split me-2 text-warning"></i>Riwayat Perubahan / Pengunduran Jadwal</h6>
                <span class="badge {{ $perubahans->count() > 3 ? 'text-bg-danger' : 'text-bg-secondary' }}">{{ $perubahans->count() }}x</span>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    @forelse ($perubahans as $pj)
                        <li class="list-group-item px-3 py-2 d-flex gap-3 align-items-start">
                            <div class="flex-shrink-0 text-center" style="width: 70px;">
                                <div class="fw-bold small">{{ $pj->tanggal->format('d M') }}</div>
                                <small class="text-secondary">{{ $pj->tanggal->format('Y') }}</small>
                            </div>
                            <div class="flex-grow-1">
                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                    <span class="badge {{ $pj->jenis == 'reopening' ? 'text-bg-info' : 'text-bg-warning text-dark' }}">{{ ucfirst($pj->jenis) }}</span>
                                    <span class="small fw-semibold">{{ $pj->tahap_terkait }}</span>
                                    <span class="small text-secondary"><i class="bi bi-clock"></i> {{ $pj->jam ?? '-' }}</span>
                                    @if ($pj->jam && $pj->jam >= '16:30')
                                        <i class="bi bi-alarm text-danger small" title="Di akhir jam kerja!"></i>
                                    @endif
                                </div>
                                <small class="text-secondary d-block">{{ $pj->alasan }}</small>
                            </div>
                            <div class="flex-shrink-0">
                                @if ($pj->ada_berita_acara)
                                    <span class="badge text-bg-success"><i class="bi bi-file-earmark-check me-1"></i>BA</span>
                                @else
                                    <span class="badge text-bg-danger"><i class="bi bi-file-earmark-x me-1"></i>Tanpa BA</span>
                                @endif
                            </div>
                        </li>
                    @empty
                        <li class="list-group-item text-center text-secondary py-4">Tidak ada perubahan jadwal ✓</li>
                    @endforelse
                </ul>
            </div>
            <div class="card-footer bg-white">
                <form action="{{ route('pemantauan.perubahan', $paket) }}" method="POST" class="row g-2">
                    @csrf
                    <div class="col-md-3">
                        <input type="date" name="tanggal" class="form-control form-control-sm" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="col-md-2">
                        <input type="time" name="jam" class="form-control form-control-sm" value="09:00">
                    </div>
                    <div class="col-md-2">
                        <select name="jenis" class="form-select form-select-sm">
                            <option value="pengunduran">Pengunduran</option>
                            <option value="reopening">Reopening</option>
                            <option value="penyesuaian">Penyesuaian</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <input type="text" name="tahap_terkait" class="form-control form-control-sm" placeholder="Tahapan yang diundur...">
                    </div>
                    <div class="col-md-2">
                        <div class="form-check form-switch pt-1">
                            <input class="form-check-input" type="checkbox" name="ada_berita_acara" value="1" id="ba">
                            <label class="form-check-label small" for="ba">Ada BA</label>
                        </div>
                    </div>
                    <div class="col-12">
                        <input type="text" name="alasan" class="form-control form-control-sm" placeholder="Alasan perubahan...">
                    </div>
                    <div class="col-12">
                        <button class="btn btn-warning btn-sm"><i class="bi bi-plus-lg me-1"></i>Catat Perubahan Jadwal</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ===== Sanggahan ===== --}}
    <div class="col-lg-5">
        <div class="card">
            <div class="card-header bg-white pt-3">
                <h6 class="fw-bold mb-0"><i class="bi bi-chat-dots me-2 text-info"></i>Sanggahan Penyedia</h6>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    @forelse ($sanggahans as $s)
                        <li class="list-group-item px-3 py-3">
                            <div class="d-flex justify-content-between align-items-start mb-1">
                                <span class="fw-semibold small">{{ $s->penyedia->nama ?? '-' }}</span>
                                <span class="badge {{ $s->hasil == 'menunggu' ? 'text-bg-warning text-dark' : ($s->hasil == 'diterima' ? 'text-bg-success' : 'text-bg-secondary') }}">
                                    {{ ucfirst($s->hasil) }}
                                </span>
                            </div>
                            <small class="text-secondary d-block">
                                Masuk: {{ format_tanggal_id($s->tanggal_masuk) }}
                                @if ($s->tanggal_dijawab)
                                    &bull; Dijawab: {{ format_tanggal_id($s->tanggal_dijawab) }}
                                @else
                                    &bull; <span class="text-danger fw-semibold">Belum dijawab ({{ $s->tanggal_masuk->diffInDays(now()) }} hari)</span>
                                @endif
                            </small>
                            @if ($s->hasil == 'menunggu')
                                <form action="{{ route('sanggahan.jawab', $s) }}" method="POST" class="row g-1 mt-2">
                                    @csrf
                                    <div class="col-5">
                                        <select name="hasil" class="form-select form-select-sm">
                                            <option value="diterima">Diterima</option>
                                            <option value="ditolak">Ditolak</option>
                                        </select>
                                    </div>
                                    <div class="col-5">
                                        <input type="text" name="catatan" class="form-control form-control-sm" placeholder="Catatan...">
                                    </div>
                                    <div class="col-2">
                                        <button class="btn btn-primary btn-sm w-100"><i class="bi bi-check-lg"></i></button>
                                    </div>
                                </form>
                            @endif
                        </li>
                    @empty
                        <li class="list-group-item text-center text-secondary py-4">Tidak ada sanggahan</li>
                    @endforelse
                </ul>
            </div>
            <div class="card-footer bg-white">
                <form action="{{ route('pemantauan.sanggahan', $paket) }}" method="POST" class="row g-2">
                    @csrf
                    <div class="col-7">
                        <select name="penyedia_id" class="form-select form-select-sm" required>
                            <option value="">-- Penyedia penggugat --</option>
                            @foreach (\App\Models\Penyedia::orderBy('nama')->get() as $pen)
                                <option value="{{ $pen->id }}">{{ $pen->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-5">
                        <input type="date" name="tanggal_masuk" class="form-control form-control-sm" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="col-12">
                        <button class="btn btn-info btn-sm text-white w-100"><i class="bi bi-plus-lg me-1"></i>Catat Sanggahan Baru</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
