@extends('layouts.app')

@section('title', 'Peta Kegiatan')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
    <div>
        <h1 class="h4 mb-0">Peta Kegiatan Pengadaan</h1>
        <small class="text-secondary">Sebaran pekerjaan pengadaan Kabupaten Banjarnegara TA {{ $tahun }}</small>
    </div>
    <div class="d-flex gap-2 align-items-center">
        <label class="text-secondary small mb-0" for="pilih-tahun">Tahun Anggaran</label>
        <select id="pilih-tahun" class="form-select form-select-sm" style="width: auto;">
            @foreach ($listTahun as $t)
                <option value="{{ $t }}" {{ $t == $tahun ? 'selected' : '' }}>{{ $t }}</option>
            @endforeach
        </select>
    </div>
</div>

{{-- Statistik ringkas --}}
<div class="row g-2 mb-3" id="stat-strip">
    <div class="col-6 col-md-2"><div class="card h-100"><div class="card-body py-2 px-3">
        <div class="text-secondary small">Total Pekerjaan</div><div class="h5 mb-0" id="stat-total">-</div>
    </div></div></div>
    <div class="col-6 col-md-2"><div class="card h-100"><div class="card-body py-2 px-3">
        <div class="text-secondary small">Total Pagu</div><div class="h5 mb-0" id="stat-pagu">-</div>
    </div></div></div>
    <div class="col-6 col-md-2"><div class="card h-100"><div class="card-body py-2 px-3">
        <div class="text-secondary small">Risiko Kritis</div><div class="h5 mb-0 text-danger" id="stat-kritis">-</div>
    </div></div></div>
    <div class="col-6 col-md-2"><div class="card h-100"><div class="card-body py-2 px-3">
        <div class="text-secondary small">Tender Gagal</div><div class="h5 mb-0 text-warning" id="stat-gagal">-</div>
    </div></div></div>
    <div class="col-6 col-md-2"><div class="card h-100"><div class="card-body py-2 px-3">
        <div class="text-secondary small">Selesai</div><div class="h5 mb-0 text-success" id="stat-selesai">-</div>
    </div></div></div>
    <div class="col-6 col-md-2"><div class="card h-100"><div class="card-body py-2 px-3">
        <div class="text-secondary small">Hasil Filter</div><div class="h5 mb-0 text-primary" id="stat-filter">-</div>
    </div></div></div>
</div>

<div class="row g-3">
    {{-- Panel filter & pencarian --}}
    <div class="col-lg-3">
        <div class="card mb-3">
            <div class="card-header py-2"><i class="bi bi-funnel me-1"></i><strong>Filter &amp; Pencarian</strong></div>
            <div class="card-body">
                <div class="mb-2">
                    <label class="form-label small mb-1" for="cari">Pencarian</label>
                    <input type="search" id="cari" class="form-control form-control-sm"
                           placeholder="Paket, OPD, Pokja, wilayah...">
                    <div class="list-group mt-1" id="hasil-cari" style="max-height: 180px; overflow-y: auto;"></div>
                </div>
                <div class="mb-2">
                    <label class="form-label small mb-1" for="f-opd">OPD</label>
                    <select id="f-opd" class="form-select form-select-sm">
                        <option value="">Semua OPD</option>
                        @foreach ($listOpd as $o)
                            <option value="{{ $o->id }}">{{ $o->singkatan }} — {{ $o->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-2">
                    <label class="form-label small mb-1" for="f-pokja">Pokja</label>
                    <select id="f-pokja" class="form-select form-select-sm">
                        <option value="">Semua Pokja</option>
                        @foreach ($listPokja as $pj)
                            <option value="{{ $pj->id }}">{{ $pj->nama }} ({{ $pj->bidang }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="row g-2">
                    <div class="col-6">
                        <label class="form-label small mb-1" for="f-status">Status</label>
                        <select id="f-status" class="form-select form-select-sm">
                            <option value="">Semua</option>
                            @foreach (['draft','persiapan','pemilihan','kontrak','pelaksanaan','selesai','batal'] as $s)
                                <option value="{{ $s }}">{{ ucfirst($s) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label small mb-1" for="f-metode">Metode</label>
                        <select id="f-metode" class="form-select form-select-sm">
                            <option value="">Semua</option>
                            @foreach (['tender','seleksi','epurchasing','pengadaan_langsung','penunjukan_langsung','swakelola'] as $m)
                                <option value="{{ $m }}">{{ \Illuminate\Support\Str::title(str_replace('_',' ',$m)) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <button id="reset-filter" type="button" class="btn btn-outline-secondary btn-sm w-100 mt-3">
                    <i class="bi bi-arrow-counterclockwise me-1"></i>Reset Filter
                </button>
            </div>
        </div>

        {{-- Daftar hasil --}}
        <div class="card">
            <div class="card-header py-2 d-flex justify-content-between align-items-center">
                <span><i class="bi bi-list-ul me-1"></i><strong>Daftar Pekerjaan</strong></span>
                <span class="badge text-bg-primary" id="jumlah-daftar">0</span>
            </div>
            <div class="list-group list-group-flush" id="daftar-paket" style="max-height: 420px; overflow-y: auto;"></div>
        </div>
    </div>

    {{-- Peta --}}
    <div class="col-lg-9">
        <div class="card">
            <div class="card-body p-2 position-relative">
                <div id="peta" style="height: 640px; border-radius: .375rem; z-index: 1;"></div>

                {{-- Legend mode risiko --}}
                <div class="peta-legend card shadow-sm">
                    <div class="card-body py-2 px-3">
                        <div class="small fw-semibold mb-1">Risiko Pekerjaan</div>
                        <div class="d-flex flex-column gap-1 small">
                            <span><span class="dot-legend" style="background:#198754"></span> Rendah</span>
                            <span><span class="dot-legend" style="background:#f0ad4e"></span> Sedang</span>
                            <span><span class="dot-legend" style="background:#dc3545"></span> Kritis</span>
                        </div>
                        <hr class="my-2">
                        <div class="d-flex flex-column gap-1 small">
                            <span><i class="bi bi-geo-alt-fill text-danger"></i> Tender gagal</span>
                            <span><i class="bi bi-eye-slash text-secondary"></i> Lokasi perkiraan (sebaran)</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
      integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css"/>
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css"/>
<style>
    .peta-legend {
        position: absolute; top: 12px; right: 12px; z-index: 500;
        width: 190px; background: rgba(255,255,255,.94);
    }
    .dot-legend { display: inline-block; width: 12px; height: 12px; border-radius: 50%; vertical-align: -1px; margin-right: 4px; }
    /* Marker indikator risiko */
    .marker-paket { display: flex; align-items: center; justify-content: center;
        width: 30px !important; height: 30px !important; border-radius: 50% 50% 50% 0;
        transform: rotate(-45deg); border: 2px solid #fff; box-shadow: 0 1px 4px rgba(0,0,0,.4); }
    .marker-paket i { transform: rotate(45deg); color: #fff; font-size: .8rem; }
    .marker-paket.risiko-rendah { background: #198754; }
    .marker-paket.risiko-sedang { background: #f0ad4e; }
    .marker-paket.risiko-kritis { background: #dc3545; }
    .marker-paket.tender-gagal::after { content: ''; position: absolute; top: -3px; right: -3px;
        width: 12px; height: 12px; background: #dc3545; border: 2px solid #fff; border-radius: 50%; }
    /* Cluster: lingkaran berwarna sesuai dominasi risiko anggota */
    .cluster-paket { background: transparent; border: 0; }
    .cluster-paket .isi-cluster { display: flex; align-items: center; justify-content: center;
        width: 38px; height: 38px; border-radius: 50%; color: #fff; font-weight: 600;
        border: 2px solid #fff; box-shadow: 0 1px 5px rgba(0,0,0,.4); }
    .cluster-paket.dominan-rendah .isi-cluster { background: rgba(25,135,84,.9); }
    .cluster-paket.dominan-sedang .isi-cluster { background: rgba(240,173,78,.95); }
    .cluster-paket.dominan-kritis .isi-cluster { background: rgba(220,53,69,.92); }
    /* Popup */
    .leaflet-popup-content { margin: 10px 12px; min-width: 240px; }
    .popup-prog { display: flex; height: 8px; border-radius: 4px; overflow: hidden; gap: 2px; background: #f8f9fa; }
    .popup-prog span { display: block; height: 100%; border-radius: 2px; }
    /* Mobile: panel filter off-canvas sederhana */
    @media (max-width: 991.98px) {
        .peta-legend { width: 150px; top: 8px; right: 8px; font-size: .8rem; }
        #peta { height: 480px; }
    }
    @media (max-width: 767.98px) { #peta { height: 400px; } }
    @media print { #peta, .peta-legend { display: none; } }
</style>
@endpush

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script src="https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    // ====== Inisialisasi peta ======
    const PUSAT = [-7.4256, 109.6916]; // Pusat Kabupaten Banjarnegara
    const peta = L.map('peta', { zoomControl: true }).setView(PUSAT, 11);

    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(peta);

    // ====== Layer wilayah ======
    let layerKec = null;
    const warnaKec = ['#4c78a8', '#f58518', '#54a24b', '#e45756', '#72b7b2', '#b279a2'];

    fetch('{{ asset("peta_kecamatan.geojson") }}')
        .then(r => r.json())
        .then(gj => {
            layerKec = L.geoJSON(gj, {
                style: { color: '#6c757d', weight: 1.2, fillOpacity: 0.04 },
                onEachFeature: (f, layer) => {
                    layer.bindTooltip(f.properties.Kecamatan || '', { sticky: true });
                    layer.on('click', () => ringkasWilayah(f.properties.Kecamatan));
                }
            }).addTo(peta);
        });

    // ====== Cluster marker ======
    const cluster = L.markerClusterGroup({
        showCoverageOnHover: false,
        maxClusterRadius: 55,
        iconCreateFunction: buatIkonCluster
    });

    function warnaRisiko(r) {
        return r === 'kritis' ? '#dc3545' : r === 'sedang' ? '#f0ad4e' : '#198754';
    }

    function buatIkonCluster(c) {
        const anak = c.getAllChildMarkers();
        const kritis = anak.filter(m => m.options.paket.risiko === 'kritis').length;
        const sedang = anak.filter(m => m.options.paket.risiko === 'sedang').length;
        const dominan = kritis > 0 && kritis >= sedang ? 'kritis' : (sedang > 0 ? 'sedang' : 'rendah');
        return L.divIcon({
            html: `<div class="isi-cluster">${c.getChildCount()}</div>`,
            className: 'cluster-paket dominan-' + dominan,
            iconSize: [38, 38]
        });
    }

    function buatMarker(p) {
        const ikon = L.divIcon({
            className: '',
            html: `<div class="marker-paket risiko-${p.risiko}${p.tender_gagal ? ' tender-gagal' : ''}">
                     <i class="bi bi-geo-alt-fill"></i></div>`,
            iconSize: [30, 30],
            iconAnchor: [15, 30],
            popupAnchor: [0, -28]
        });
        const m = L.marker([p.lat, p.lng], { icon: ikon, title: p.nama, paket: p });
        m.bindPopup(buatPopup(p), { maxWidth: 300 });
        return m;
    }

    // ====== Popup pekerjaan ======
    const LABEL_TAHAP = { selesai: 'Selesai', proses: 'Proses', belum: 'Belum' };
    const WARNA_TAHAP = { selesai: '#198754', proses: '#0d6efd', belum: '#adb5bd' };

    function formatRupiah(n) {
        if (n >= 1e9) return 'Rp ' + (n / 1e9).toFixed(1).replace('.', ',') + ' M';
        if (n >= 1e6) return 'Rp ' + (n / 1e6).toFixed(0) + ' jt';
        return 'Rp ' + n.toLocaleString('id-ID');
    }

    function buatPopup(p) {
        const segmen = p.tahapan.map(t =>
            `<span style="background:${WARNA_TAHAP[t.status]};width:${100 / p.tahapan.length}%"></span>`).join('');
        const presisi = p.presisi === 'tepat' ? 'Lokasi tepat'
            : p.presisi === 'desa' ? 'Lokasi desa' : 'Perkiraan wilayah (sebaran)';
        return `
        <div>
            <div class="fw-semibold" style="font-size:.86rem">${p.nama}</div>
            <div class="text-secondary" style="font-size:.74rem">${p.kode} &bull; ${p.opd_singkatan || '-'} &bull; ${p.pokja || '-'}</div>
            <hr class="my-2">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <span class="small">Progres</span><span class="fw-semibold">${p.progres}%</span>
            </div>
            <div class="popup-prog mb-2">${segmen}</div>
            <div class="small mb-1"><span class="text-secondary">Status:</span> ${p.status_label}
                ${p.tender_gagal ? '<span class="badge text-bg-danger ms-1">Tender gagal</span>' : ''}</div>
            <div class="small mb-1"><span class="text-secondary">Risiko:</span>
                <span class="badge text-bg-${p.risiko === 'kritis' ? 'danger' : p.risiko === 'sedang' ? 'warning text-dark' : 'success'}">${p.risiko}</span></div>
            <div class="small mb-1"><span class="text-secondary">Pagu:</span> ${formatRupiah(p.pagu)}</div>
            <div class="small mb-1"><span class="text-secondary">Metode:</span> ${(p.metode || '-').replace('_', ' ')}</div>
            <div class="small mb-2"><span class="text-secondary">Lokasi:</span> ${p.desa || p.kecamatan || p.lokasi_teks || '-'}
                <span class="text-secondary" style="font-size:.72rem">(${presisi})</span></div>
            <a href="${p.url_detail}" class="btn btn-primary btn-sm w-100">
                <i class="bi bi-box-arrow-up-right me-1"></i>Detail Pekerjaan</a>
        </div>`;
    }

    // ====== Muat data & render ======
    let semuaPaket = [];
    let layerPaket = L.layerGroup().addTo(peta);

    function muatData(tahun) {
        fetch(`/api/peta/paket?tahun=${tahun}`)
            .then(r => r.json())
            .then(json => {
                semuaPaket = json.data;
                renderFilter();
                muatStatistik(tahun);
            });
    }

    function muatStatistik(tahun) {
        fetch(`/api/peta/statistik?tahun=${tahun}`)
            .then(r => r.json())
            .then(s => {
                document.getElementById('stat-total').textContent = s.total;
                document.getElementById('stat-pagu').textContent = formatRupiah(s.pagu);
                document.getElementById('stat-kritis').textContent = s.kritis;
                document.getElementById('stat-gagal').textContent = s.gagal;
                document.getElementById('stat-selesai').textContent = s.selesai;
            });
    }

    // ====== Filter ======
    const fOpd = document.getElementById('f-opd');
    const fPokja = document.getElementById('f-pokja');
    const fStatus = document.getElementById('f-status');
    const fMetode = document.getElementById('f-metode');
    const cari = document.getElementById('cari');

    function paketLolos(p) {
        if (fOpd.value && String(p.opd_id) !== fOpd.value) return false;
        if (fPokja.value && String(p.pokja_id) !== fPokja.value) return false;
        if (fStatus.value && p.status !== fStatus.value) return false;
        if (fMetode.value && p.metode !== fMetode.value) return false;
        const q = cari.value.trim().toLowerCase();
        if (q) {
            const gab = `${p.nama} ${p.kode} ${p.opd || ''} ${p.opd_singkatan || ''} ${p.pokja || ''} ${p.desa || ''} ${p.kecamatan || ''}`.toLowerCase();
            if (!gab.includes(q)) return false;
        }
        return true;
    }

    function renderFilter() {
        const hasil = semuaPaket.filter(paketLolos);
        document.getElementById('stat-filter').textContent = hasil.length;

        // Render cluster
        cluster.clearLayers();
        hasil.forEach(p => cluster.addLayer(buatMarker(p)));
        if (peta.hasLayer(cluster)) peta.removeLayer(cluster);
        peta.addLayer(cluster);

        // Daftar sisi kiri
        const daftar = document.getElementById('daftar-paket');
        document.getElementById('jumlah-daftar').textContent = hasil.length;
        daftar.innerHTML = hasil.map(p => `
            <button type="button" class="list-group-item list-group-item-action item-paket" data-id="${p.id}">
                <div class="d-flex justify-content-between align-items-center gap-2">
                    <div class="small">
                        <div class="fw-semibold text-truncate" style="max-width:230px">${p.nama}</div>
                        <div class="text-secondary" style="font-size:.72rem">${p.kode} &bull; ${p.opd_singkatan || '-'} &bull; ${p.desa || p.kecamatan || '-'}</div>
                    </div>
                    <span class="badge flex-shrink-0 text-bg-${p.risiko === 'kritis' ? 'danger' : p.risiko === 'sedang' ? 'warning text-dark' : 'success'}">${p.progres}%</span>
                </div>
            </button>`).join('') ||
            '<div class="text-center text-secondary py-4 small">Tidak ada pekerjaan yang cocok</div>';

        // Klik daftar: terbang ke marker
        daftar.querySelectorAll('.item-paket').forEach(el => {
            el.addEventListener('click', () => {
                const p = semuaPaket.find(x => x.id == el.dataset.id);
                if (!p) return;
                const target = cluster.getLayers().find(m => m.options.paket.id === p.id);
                if (target) cluster.zoomToShowLayer(target, () => target.openPopup());
            });
        });

        renderCari(hasil);
    }

    [fOpd, fPokja, fStatus, fMetode].forEach(el => el.addEventListener('change', renderFilter));
    cari.addEventListener('input', renderFilter);

    document.getElementById('reset-filter').addEventListener('click', () => {
        [fOpd, fPokja, fStatus, fMetode].forEach(el => el.value = '');
        cari.value = '';
        renderFilter();
    });

    // ====== Pencarian: saran dropdown ======
    function renderCari(hasil) {
        const box = document.getElementById('hasil-cari');
        const q = cari.value.trim();
        if (!q) { box.innerHTML = ''; return; }
        const saran = hasil.slice(0, 6).map(p => `
            <button type="button" class="list-group-item list-group-item-action py-1 hasil-cari" data-id="${p.id}" style="font-size:.78rem">
                <i class="bi bi-geo-alt me-1"></i>${p.nama}
            </button>`).join('');
        box.innerHTML = saran;
        box.querySelectorAll('.hasil-cari').forEach(el => {
            el.addEventListener('click', () => {
                const p = semuaPaket.find(x => x.id == el.dataset.id);
                if (!p) return;
                const target = cluster.getLayers().find(m => m.options.paket.id === p.id);
                if (target) cluster.zoomToShowLayer(target, () => target.openPopup());
            });
        });
    }

    // ====== Ringkasan wilayah saat poligon kecamatan diklik ======
    function ringkasWilayah(namaKec) {
        const diKec = semuaPaket.filter(p => (p.kecamatan || '').toLowerCase().includes((namaKec || '').toLowerCase()));
        const pagu = diKec.reduce((s, p) => s + p.pagu, 0);
        const kritis = diKec.filter(p => p.risiko === 'kritis').length;
        L.popup().setLatLng(peta.getCenter())
            .setContent(`
                <div style="min-width:220px">
                    <div class="fw-semibold">Kecamatan ${namaKec}</div>
                    <hr class="my-1">
                    <div class="small mb-1">Pekerjaan: <strong>${diKec.length}</strong></div>
                    <div class="small mb-1">Total pagu: <strong>${formatRupiah(pagu)}</strong></div>
                    <div class="small mb-2">Risiko kritis: <strong>${kritis}</strong></div>
                    <button type="button" class="btn btn-sm btn-outline-primary w-100" onclick="window.filterKecamatan('${(namaKec||'').replace(/'/g, "\\'")}');">
                        Filter wilayah ini</button>
                </div>`)
            .openOn(peta);
    }

    window.filterKecamatan = function (nama) {
        peta.closePopup();
        cari.value = nama;
        renderFilter();
    };

    // ====== Ganti tahun ======
    document.getElementById('pilih-tahun').addEventListener('change', function () {
        muatData(this.value);
    });

    // Mulai
    muatData('{{ $tahun }}');
});
</script>
@endpush
