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

{{-- Peta full-width dengan overlay --}}
<div class="card peta-wrap">
    <div class="card-body p-2 position-relative">
        <div id="peta" style="height: calc(100vh - 300px); min-height: 520px; border-radius: .375rem; z-index: 1;"></div>

        {{-- Tombol & panel Filter (overlay kiri-atas) --}}
        <button id="btn-filter" type="button" class="btn btn-light btn-sm shadow-sm peta-btn peta-btn-filter">
            <i class="bi bi-funnel me-1"></i>Filter
            <span class="badge text-bg-primary ms-1 d-none" id="jml-aktif">0</span>
        </button>
        <div id="panel-filter" class="panel-overlay">
            <div class="d-flex justify-content-between align-items-center px-3 pt-2">
                <span class="fw-semibold small"><i class="bi bi-funnel me-1"></i>Filter &amp; Pencarian</span>
                <button type="button" class="btn-close btn-sm" id="tutup-filter" aria-label="Tutup"></button>
            </div>
            <div class="px-3 pb-3 pt-2">
                <div class="mb-2">
                    <label class="form-label small mb-1" for="cari">Pencarian</label>
                    <input type="search" id="cari" class="form-control form-control-sm"
                           placeholder="Paket, OPD, Pokja, wilayah...">
                    <div class="list-group mt-1" id="hasil-cari" style="max-height: 150px; overflow-y: auto;"></div>
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

        {{-- Tombol & panel Daftar Pekerjaan (overlay kiri-bawah) --}}
        <button id="btn-daftar" type="button" class="btn btn-light btn-sm shadow-sm peta-btn peta-btn-daftar">
            <i class="bi bi-list-ul me-1"></i>Daftar
            <span class="badge text-bg-primary ms-1" id="jumlah-daftar">0</span>
        </button>
        <div id="panel-daftar" class="panel-overlay panel-daftar">
            <div class="d-flex justify-content-between align-items-center px-3 pt-2">
                <span class="fw-semibold small">
                    <i class="bi bi-list-ul me-1"></i>Daftar Pekerjaan
                    <span class="badge text-bg-secondary ms-1" id="jumlah-daftar-2">0</span>
                </span>
                <button type="button" class="btn-close btn-sm" id="tutup-daftar" aria-label="Tutup"></button>
            </div>
            <div class="list-group list-group-flush px-2 pb-2" id="daftar-paket" style="max-height: 330px; overflow-y: auto;"></div>
        </div>

        {{-- Legend (overlay kanan-bawah) --}}
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
@endsection

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
      integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css"/>
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css"/>
<style>
    /* ===== Overlay di atas peta ===== */
    .peta-wrap { position: relative; }
    /* Tint biru wilayah diterapkan langsung pada fill poligon GeoJSON kecamatan
       (lihat JS) — hanya area dalam batas wilayah yang terwarnai. */
    .peta-btn {
        position: absolute; z-index: 900;
        background: rgba(255,255,255,.95);
    }
    .peta-btn-filter { top: 12px; left: 12px; }
    .peta-btn-daftar { bottom: 12px; left: 12px; }
    .panel-overlay {
        position: absolute; z-index: 880;
        background: rgba(255,255,255,.97);
        border-radius: .5rem;
        box-shadow: 0 4px 16px rgba(0,0,0,.18);
        display: none;
    }
    .panel-overlay.show { display: block; }
    #panel-filter { top: 52px; left: 12px; width: 280px; }
    .panel-daftar { bottom: 48px; left: 12px; width: 330px; }
    .peta-legend {
        position: absolute; bottom: 12px; right: 12px; z-index: 850;
        width: 180px; background: rgba(255,255,255,.94);
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
    /* Mobile: overlay full-lebar */
    @media (max-width: 767.98px) {
        #peta { height: 65vh !important; min-height: 380px; }
        #panel-filter, .panel-daftar { width: calc(100% - 24px); }
        .peta-legend { width: 148px; font-size: .78rem; bottom: 8px; right: 8px; }
    }
    @media print { #peta, .peta-legend, .peta-btn, .panel-overlay { display: none; } }
</style>
@endpush

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script src="https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    // ====== Inisialisasi peta (zoom di kanan-atas agar tak bertabrakan dengan overlay) ======
    const PUSAT = [-7.4256, 109.6916]; // Pusat Kabupaten Banjarnegara
    const peta = L.map('peta', { zoomControl: false }).setView(PUSAT, 11);
    L.control.zoom({ position: 'topright' }).addTo(peta);

    // ====== Basemap multi-penyedia + fallback otomatis ======
    // tile.openstreetmap.org kerap kena blokir (kebijakan tile OSM / jaringan kantor),
    // karena itu disediakan beberapa penyedia; jika satu gagal, peta pindah otomatis.
    // Catatan: CARTO sengaja TIDAK disertakan — kini menampilkan tile ber-watermark
    // "API key required" (HTTP 200), sehingga tak terdeteksi oleh fallback berbasis error.
    const BASEMAP = {
        'Esri Jalan': L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Street_Map/MapServer/tile/{z}/{y}/{x}', {
            maxZoom: 19,
            attribution: 'Tiles &copy; Esri &mdash; sumber: Esri, HERE, Garmin, OpenStreetMap'
        }),
        'Esri Satelit': L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
            maxZoom: 19,
            attribution: 'Tiles &copy; Esri &mdash; sumber: Esri, Maxar, Earthstar Geographics'
        }),
        'OpenStreetMap': L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap contributors'
        }),
        'OSM Jerman': L.tileLayer('https://tile.openstreetmap.de/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap contributors'
        }),
        'OpenTopoMap': L.tileLayer('https://{s}.tile.opentopomap.org/{z}/{x}/{y}.png', {
            maxZoom: 17, subdomains: 'abc',
            attribution: '&copy; OpenStreetMap contributors &copy; OpenTopoMap (CC-BY-SA)'
        }),
    };

    const URUTAN_BASEMAP = Object.keys(BASEMAP);
    const BASEMAP_DEFAULT = 'Esri Jalan'; // paling stabil: tanpa API key, tanpa watermark
    const KUNCI_BASEMAP = 'peta-basemap';
    const gagalBerturut = {};
    let siklusFallback = 0;

    Object.entries(BASEMAP).forEach(([nama, layer]) => {
        layer.on('tileload', () => { gagalBerturut[nama] = 0; siklusFallback = 0; });
        layer.on('tileerror', () => {
            if (!peta.hasLayer(layer)) return;
            gagalBerturut[nama] = (gagalBerturut[nama] || 0) + 1;
            if (gagalBerturut[nama] >= 8) gantiBasemapOtomatis(nama);
        });
    });

    function gantiBasemapOtomatis(dari) {
        if (siklusFallback >= URUTAN_BASEMAP.length) {
            infoBasemap('Semua penyedia peta tidak dapat dijangkau. Periksa koneksi jaringan Anda.');
            return;
        }
        siklusFallback++;
        const berikut = URUTAN_BASEMAP[(URUTAN_BASEMAP.indexOf(dari) + 1) % URUTAN_BASEMAP.length];
        gagalBerturut[berikut] = 0;
        peta.removeLayer(BASEMAP[dari]);
        BASEMAP[berikut].addTo(peta);
        localStorage.setItem(KUNCI_BASEMAP, berikut);
        infoBasemap(`Peta "${dari}" tidak merespons — dialihkan otomatis ke "${berikut}".`);
    }

    function infoBasemap(pesan) {
        document.getElementById('info-basemap')?.remove();
        const el = document.createElement('div');
        el.id = 'info-basemap';
        el.className = 'alert alert-warning py-1 px-2 small shadow-sm mb-0';
        el.style.cssText = 'position:absolute;top:12px;left:50%;transform:translateX(-50%);z-index:950;max-width:92%;';
        el.textContent = pesan;
        document.querySelector('.peta-wrap .card-body').appendChild(el);
        setTimeout(() => el.remove(), 8000);
    }

    // Pulihkan pilihan pengguna terakhir; kalau belum ada, pakai default non-OSM
    const basemapTersimpan = localStorage.getItem(KUNCI_BASEMAP);
    (BASEMAP[basemapTersimpan] ? BASEMAP[basemapTersimpan] : BASEMAP[BASEMAP_DEFAULT]).addTo(peta);

    // Pemilih basemap manual (pojok kanan atas, di bawah tombol zoom)
    L.control.layers(BASEMAP, null, { position: 'topright' }).addTo(peta);
    peta.on('baselayerchange', e => localStorage.setItem(KUNCI_BASEMAP, e.name));

    // ====== Layer wilayah (tint biru tipis HANYA di dalam poligon kecamatan) ======
    let layerKec = null;

    fetch('{{ asset("peta_kecamatan.geojson") }}')
        .then(r => r.json())
        .then(gj => {
            layerKec = L.geoJSON(gj, {
                style: { color: '#3b82f6', weight: 1.4, fillColor: '#2563eb', fillOpacity: 0.14 },
                onEachFeature: (f, layer) => {
                    layer.bindTooltip(f.properties.Kecamatan || '', { sticky: true });
                    layer.on('click', () => ringkasWilayah(f.properties.Kecamatan));
                }
            }).addTo(peta);

            // Paskan frame ke seluruh batas wilayah: pas di tengah, tidak ada yang terpotong
            peta.fitBounds(layerKec.getBounds(), { padding: [20, 20] });
        });

    // ====== Cluster marker ======
    const cluster = L.markerClusterGroup({
        showCoverageOnHover: false,
        maxClusterRadius: 55,
        iconCreateFunction: buatIkonCluster
    });

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

    function perbaruiBadgeFilter() {
        let n = 0;
        [fOpd, fPokja, fStatus, fMetode].forEach(el => { if (el.value) n++; });
        if (cari.value.trim()) n++;
        const b = document.getElementById('jml-aktif');
        b.textContent = n;
        b.classList.toggle('d-none', n === 0);
    }

    function renderFilter() {
        const hasil = semuaPaket.filter(paketLolos);
        document.getElementById('stat-filter').textContent = hasil.length;

        // Render cluster
        cluster.clearLayers();
        hasil.forEach(p => cluster.addLayer(buatMarker(p)));
        if (peta.hasLayer(cluster)) peta.removeLayer(cluster);
        peta.addLayer(cluster);

        // Daftar di panel geser
        const daftar = document.getElementById('daftar-paket');
        document.getElementById('jumlah-daftar').textContent = hasil.length;
        document.getElementById('jumlah-daftar-2').textContent = hasil.length;
        daftar.innerHTML = hasil.map(p => `
            <button type="button" class="list-group-item list-group-item-action item-paket" data-id="${p.id}">
                <div class="d-flex justify-content-between align-items-center gap-2">
                    <div class="small">
                        <div class="fw-semibold text-truncate" style="max-width:210px">${p.nama}</div>
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
        perbaruiBadgeFilter();
    }

    [fOpd, fPokja, fStatus, fMetode].forEach(el => el.addEventListener('change', renderFilter));
    cari.addEventListener('input', renderFilter);

    document.getElementById('reset-filter').addEventListener('click', () => {
        [fOpd, fPokja, fStatus, fMetode].forEach(el => el.value = '');
        cari.value = '';
        renderFilter();
    });

    // ====== Toggle panel overlay ======
    const panelFilter = document.getElementById('panel-filter');
    const panelDaftar = document.getElementById('panel-daftar');

    document.getElementById('btn-filter').addEventListener('click', () => panelFilter.classList.toggle('show'));
    document.getElementById('tutup-filter').addEventListener('click', () => panelFilter.classList.remove('show'));
    document.getElementById('btn-daftar').addEventListener('click', () => panelDaftar.classList.toggle('show'));
    document.getElementById('tutup-daftar').addEventListener('click', () => panelDaftar.classList.remove('show'));

    // Tutup kedua panel dengan tombol ESC
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') {
            panelFilter.classList.remove('show');
            panelDaftar.classList.remove('show');
        }
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
        panelFilter.classList.add('show'); // perlihatkan filter aktif ke pengguna
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
