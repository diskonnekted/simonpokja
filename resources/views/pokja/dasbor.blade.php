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
    <div class="card-header py-2 d-flex justify-content-between align-items-center gap-2 flex-wrap">
        <span><i class="bi bi-list-task me-1"></i><strong>Daftar Pekerjaan</strong>
            <span class="text-secondary small">TA {{ date('Y') }}</span></span>
        <div class="d-flex align-items-center gap-2">
            <div class="input-group input-group-sm" style="width: 250px;">
                <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-secondary"></i></span>
                <input type="text" id="cari-paket" class="form-control border-start-0 ps-1" placeholder="Cari pekerjaan… ( / )" autocomplete="off">
            </div>
            <span class="badge text-bg-primary" id="info-jumlah" title="Jumlah pekerjaan yang ditampilkan">{{ $pakets->count() }}</span>
        </div>
    </div>
    <div class="card-body p-0" style="max-height: 640px; overflow-y: auto;">
        <table class="table table-sm table-hover align-middle mb-0" id="tabel-paket">
            <thead><tr>
                <th class="ps-3" data-sort="nama" title="Klik untuk mengurutkan">Pekerjaan</th>
                <th data-sort="progres" title="Klik untuk mengurutkan">Progres</th>
                <th data-sort="deadline" title="Klik untuk mengurutkan">Deadline</th>
                <th class="text-end pe-3">Aksi</th>
            </tr></thead>
            <tbody>
                @forelse ($pakets as $p)
                    @php [$kunci, $labelDl, $warnaDl, $sisa] = $p->status_deadline; @endphp
                    <tr class="js-row"
                        data-nama="{{ strtolower($p->nama_paket) }}"
                        data-progres="{{ (int) $p->progress }}"
                        data-deadline="{{ $p->tanggal_selesai?->format('Y-m-d') ?? '9999-12-31' }}">
                        <td class="ps-3" style="max-width: 280px;">
                            <div class="fw-semibold small text-truncate" title="{{ $p->nama_paket }}">{{ $p->nama_paket }}</div>
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
                        <td class="text-nowrap">
                            <div>{{ $p->tanggal_selesai?->format('d M Y') ?? '-' }}</div>
                            <span class="badge text-bg-{{ $warnaDl }}">{{ $labelDl }}</span>
                        </td>
                        <td class="text-end pe-3 text-nowrap">
                            @if ($bolehSimpan)
                                <div class="dropdown d-inline">
                                    <button class="btn btn-sm btn-primary dropdown-toggle" data-bs-toggle="dropdown" data-bs-strategy="fixed" data-bs-auto-close="outside" aria-expanded="false">
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
                                                <select id="st-{{ $p->id }}" name="status" class="form-select form-select-sm js-status-progres" required>
                                                    @foreach (['draft','persiapan','pemilihan','kontrak','pelaksanaan','selesai','batal'] as $s)
                                                        <option value="{{ $s }}" {{ $p->status === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                                                    @endforeach
                                                </select>
                                                <small class="text-secondary" style="font-size:.7rem">Progres % otomatis menyesuaikan status</small>
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
                            @php $sp = $statPesan[$p->id] ?? null; @endphp
                            <button type="button" class="btn btn-sm btn-outline-primary ms-1 position-relative js-btn-pesan"
                                    data-bs-toggle="modal" data-bs-target="#modal-pesan"
                                    data-paket-id="{{ $p->id }}"
                                    data-nama="{{ $p->nama_paket }}"
                                    title="Diskusi dengan {{ $user->isAdmin() ? 'Pokja' : 'Kepala LPSE' }} ({{ $sp->total ?? 0 }} pesan)">
                                <i class="bi bi-chat-left-text"></i>
                                @if (($sp->baru ?? 0) > 0)
                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger js-badge-pesan">{{ $sp->baru }}</span>
                                @endif
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center text-secondary py-4">Tidak ada pekerjaan TA {{ date('Y') }} untuk Pokja ini</td></tr>
                @endforelse
                <tr id="baris-tak-ada" class="d-none">
                    <td colspan="4" class="text-center text-secondary py-4">
                        <i class="bi bi-search d-block fs-4 mb-1"></i>
                        Tidak ada pekerjaan yang cocok dengan pencarian.
                    </td>
                </tr>
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

{{-- Modal diskusi Kepala LPSE <-> Pokja per pekerjaan --}}
<div class="modal fade" id="modal-pesan" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h5 class="modal-title h6 mb-0" id="pesan-judul">Diskusi Pekerjaan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body p-0 bg-body-tertiary">
                @include('pesan._chat')
            </div>
        </div>
    </div>
</div>
@include('pesan._chat-script')
@endsection

@push('styles')
<style>
    .tahapan-bar { display: flex; height: 10px; border-radius: 5px; overflow: hidden; gap: 2px; background: #f8f9fa; }
    .tahapan-seg { display: block; height: 100%; border-radius: 2px; }
    /* Dropdown form supaya klik di dalam tidak menutup dropdown */
    .dropdown-menu form { padding: 0; margin: 0; }

    /* Header tabel tetap terlihat saat kontainer di-scroll */
    #tabel-paket thead th {
        position: sticky;
        top: 0;
        z-index: 10;
        background: var(--bs-gray-100, #f8f9fa);
        box-shadow: inset 0 -1px 0 var(--bs-border-color);
    }
    #tabel-paket th[data-sort] { cursor: pointer; user-select: none; white-space: nowrap; }
    #tabel-paket th[data-sort]:hover { color: var(--bs-primary); }
    #tabel-paket th[data-sort] .sort-ico { font-size: .7rem; margin-left: .2rem; }

    /* Fokus kolom pencarian */
    #cari-paket:focus { box-shadow: none; border-color: var(--bs-primary); }
    .input-group:focus-within .input-group-text { border-color: var(--bs-primary); }
    .input-group:focus-within { box-shadow: 0 0 0 .2rem rgba(13, 110, 253, .15); border-radius: .375rem; }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    'use strict';
    // ====== Pilih Status -> angka Progres (%) menyesuaikan otomatis ======
    // Pemetaan konsisten dengan logika model PaketPengadaan (tahapan progres):
    // draft=0, persiapan=10, pemilihan=30, kontrak=50, pelaksanaan=70, selesai=100, batal=0
    const PETA_STATUS_PROGRES = {
        draft: 0, persiapan: 10, pemilihan: 30, kontrak: 50,
        pelaksanaan: 70, selesai: 100, batal: 0
    };
    document.querySelectorAll('.js-status-progres').forEach(function (sel) {
        sel.addEventListener('change', function () {
            const form = sel.closest('form');
            const prog = form ? form.querySelector('input[name="progress"]') : null;
            if (prog && sel.value in PETA_STATUS_PROGRES) {
                prog.value = PETA_STATUS_PROGRES[sel.value];
                prog.classList.add('border-primary');           // sorot sebentar agar terlihat berubah
                setTimeout(() => prog.classList.remove('border-primary'), 1200);
            }
        });
    });

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

    // Modal pesan: sambungkan panel chat ke paket yang dipilih
    const TPL_PESAN_INDEX = @json(route('pesan.index', ['paket' => '__ID__']));
    const TPL_PESAN_STORE = @json(route('pesan.store', ['paket' => '__ID__']));
    const modalPesan = document.getElementById('modal-pesan');
    const panelPesan = modalPesan.querySelector('.chat-paket');
    const judulPesan = document.getElementById('pesan-judul');

    modalPesan.addEventListener('show.bs.modal', function (ev) {
        const btn = ev.relatedTarget;
        const id = btn.dataset.paketId;
        judulPesan.textContent = 'Diskusi — ' + btn.dataset.nama;
        panelPesan.dataset.paket = id;
        window.ChatPaket.attach(panelPesan, TPL_PESAN_INDEX.replace('__ID__', id), TPL_PESAN_STORE.replace('__ID__', id));
        // Membuka thread menandai pesan lawan sebagai terbaca di server → bersihkan badge
        const badge = btn.querySelector('.js-badge-pesan');
        if (badge) badge.remove();
    });

    modalPesan.addEventListener('hidden.bs.modal', function () {
        window.ChatPaket.detach(panelPesan);
        judulPesan.textContent = 'Diskusi Pekerjaan';
    });

    // ====== Pencarian cepat + pengurutan tabel ======
    const inputCari = document.getElementById('cari-paket');
    const tabel = document.getElementById('tabel-paket');
    const tbody = tabel.querySelector('tbody');
    const baris = Array.from(tbody.querySelectorAll('tr.js-row'));
    const takAda = document.getElementById('baris-tak-ada');
    const info = document.getElementById('info-jumlah');

    function terapkanCari() {
        const q = inputCari.value.trim().toLowerCase();
        let n = 0;
        baris.forEach(function (tr) {
            const cocok = !q || tr.textContent.toLowerCase().includes(q);
            tr.classList.toggle('d-none', !cocok);
            if (cocok) n++;
        });
        takAda.classList.toggle('d-none', n > 0);
        info.textContent = q ? n + ' / ' + baris.length : baris.length;
        info.title = q ? `Menampilkan ${n} dari ${baris.length} pekerjaan` : 'Jumlah pekerjaan yang ditampilkan';
    }
    inputCari.addEventListener('input', terapkanCari);

    // Tekan "/" untuk langsung fokus ke kolom pencarian
    document.addEventListener('keydown', function (e) {
        if (e.key === '/' && !/^(INPUT|TEXTAREA|SELECT)$/.test(e.target.tagName)) {
            e.preventDefault();
            inputCari.focus();
        }
    });

    // Urutkan kolom: Pekerjaan (teks), Progres (angka), Deadline (tanggal)
    let sortKey = null, sortDir = 1;
    tabel.querySelectorAll('th[data-sort]').forEach(function (th) {
        th.addEventListener('click', function () {
            const key = th.dataset.sort;
            sortDir = (sortKey === key) ? -sortDir : 1;
            sortKey = key;
            baris.slice().sort(function (a, b) {
                let va = a.dataset[key] ?? '', vb = b.dataset[key] ?? '';
                if (key === 'progres') { va = +va; vb = +vb; }
                return (va > vb ? 1 : va < vb ? -1 : 0) * sortDir;
            }).forEach(function (tr) { tbody.appendChild(tr); });
            tbody.appendChild(takAda); // baris "tidak ditemukan" selalu di akhir
            tabel.querySelectorAll('th[data-sort] .sort-ico').forEach(function (el) { el.remove(); });
            th.insertAdjacentHTML('beforeend',
                ' <i class="bi bi-caret-' + (sortDir === 1 ? 'up' : 'down') + '-fill sort-ico"></i>');
        });
    });

    // Urutan awal: deadline paling dekat di atas (pekerjaan tanpa deadline di bawah)
    const thDeadline = tabel.querySelector('th[data-sort="deadline"]');
    if (thDeadline) thDeadline.click();
});
</script>
@endpush
