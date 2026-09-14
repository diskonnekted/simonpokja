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

{{-- Unified Collapsible Split Layout: Integrated Side Drawer + Map Canvas --}}
<div class="card peta-split-container overflow-hidden shadow-sm">
    <div class="d-flex flex-column flex-md-row position-relative peta-split-body">
        
        {{-- Unified Side Panel (Kiri) --}}
        <aside class="peta-side-panel d-flex flex-column" id="petaSidePanel">
            {{-- Tab Header --}}
            <div class="panel-header border-bottom bg-white p-2">
                <ul class="nav nav-pills nav-fill gap-1" id="petaTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active py-1 px-2 small fw-semibold" id="tab-daftar-btn" data-bs-toggle="pill" data-bs-target="#tab-daftar" type="button" role="tab">
                            <i class="bi bi-list-ul me-1"></i>Daftar Pekerjaan
                            <span class="badge text-bg-primary ms-1" id="badge-total-daftar">0</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link py-1 px-2 small fw-semibold" id="tab-filter-btn" data-bs-toggle="pill" data-bs-target="#tab-filter" type="button" role="tab">
                            <i class="bi bi-funnel me-1"></i>Filter & Cari
                            <span class="badge text-bg-warning text-dark ms-1 d-none" id="jml-aktif">0</span>
                        </button>
                    </li>
                </ul>
            </div>

            {{-- Tab Content Container --}}
            <div class="tab-content flex-grow-1 overflow-hidden position-relative" id="petaTabContent">
                
                {{-- TAB 1: DAFTAR PEKERJAAN --}}
                <div class="tab-pane fade show active h-100 flex-column overflow-hidden" id="tab-daftar" role="tabpanel">
                    {{-- Search & Controls Bar --}}
                    <div class="p-2 border-bottom bg-light">
                        <div class="input-group input-group-sm mb-1">
                            <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-secondary"></i></span>
                            <input type="search" id="quick-search" class="form-control border-start-0" placeholder="Cari nama paket/OPD/desa...">
                        </div>
                        <div class="d-flex justify-content-between align-items-center pt-1" style="font-size: 0.76rem;">
                            <div class="form-check form-switch mb-0">
                                <input class="form-check-input" type="checkbox" id="sync-bounds" checked>
                                <label class="form-check-label text-secondary" for="sync-bounds">Hanya di layar peta</label>
                            </div>
                            <div class="dropdown">
                                <button class="btn btn-link btn-sm text-secondary text-decoration-none p-0 dropdown-toggle" style="font-size: 0.76rem;" type="button" data-bs-toggle="dropdown">
                                    <i class="bi bi-sort-down me-1"></i>Urutkan
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow-sm small" id="sort-menu">
                                    <li><a class="dropdown-item active" href="#" data-sort="default"><i class="bi bi-list-nested me-2 text-secondary"></i>Urutan Standar</a></li>
                                    <li><a class="dropdown-item" href="#" data-sort="risiko"><i class="bi bi-exclamation-octagon text-danger me-2"></i>Risiko Tertinggi</a></li>
                                    <li><a class="dropdown-item" href="#" data-sort="pagu"><i class="bi bi-cash-stack text-success me-2"></i>Pagu Terbesar</a></li>
                                    <li><a class="dropdown-item" href="#" data-sort="progres"><i class="bi bi-graph-up-arrow text-primary me-2"></i>Progres Fisik</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    {{-- Scrollable List of Cards --}}
                    <div class="job-list-scroll flex-grow-1 overflow-y-auto p-2" id="daftar-paket"></div>

                    {{-- Footer Counter & Reset Button --}}
                    <div class="p-2 border-top bg-white small text-secondary d-flex justify-content-between align-items-center">
                        <span id="label-count-display">0 pekerjaan</span>
                        <button class="btn btn-outline-secondary btn-sm py-0 px-2" id="btn-reset-view" style="font-size: 0.72rem;">
                            <i class="bi bi-arrows-fullscreen me-1"></i>Reset Tampilan
                        </button>
                    </div>
                </div>

                {{-- TAB 2: FILTER & PENCARIAN --}}
                <div class="tab-pane fade h-100 overflow-y-auto p-3" id="tab-filter" role="tabpanel">
                    <div class="mb-2">
                        <label class="form-label small mb-1 fw-semibold" for="cari">Pencarian Spesifik</label>
                        <input type="search" id="cari" class="form-control form-control-sm" placeholder="Paket, OPD, Pokja, wilayah...">
                        <div class="list-group mt-1" id="hasil-cari" style="max-height: 150px; overflow-y: auto;"></div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small mb-1 fw-semibold" for="f-opd">Perangkat Daerah (OPD)</label>
                        <select id="f-opd" class="form-select form-select-sm">
                            <option value="">Semua OPD</option>
                            @foreach ($listOpd as $o)
                                <option value="{{ $o->id }}">{{ $o->singkatan }} — {{ $o->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small mb-1 fw-semibold" for="f-pokja">Kelompok Kerja (Pokja)</label>
                        <select id="f-pokja" class="form-select form-select-sm">
                            <option value="">Semua Pokja</option>
                            @foreach ($listPokja as $pj)
                                <option value="{{ $pj->id }}">{{ $pj->nama }} ({{ $pj->bidang }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <label class="form-label small mb-1 fw-semibold" for="f-status">Status</label>
                            <select id="f-status" class="form-select form-select-sm">
                                <option value="">Semua</option>
                                @foreach (['draft','persiapan','pemilihan','kontrak','pelaksanaan','selesai','batal'] as $s)
                                    <option value="{{ $s }}">{{ ucfirst($s) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label small mb-1 fw-semibold" for="f-metode">Metode</label>
                            <select id="f-metode" class="form-select form-select-sm">
                                <option value="">Semua</option>
                                @foreach (['tender','seleksi','epurchasing','pengadaan_langsung','penunjukan_langsung','swakelola'] as $m)
                                    <option value="{{ $m }}">{{ \Illuminate\Support\Str::title(str_replace('_',' ',$m)) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <button id="reset-filter" type="button" class="btn btn-outline-secondary btn-sm w-100 mt-2">
                        <i class="bi bi-arrow-counterclockwise me-1"></i>Reset Semua Filter
                    </button>
                </div>

            </div>
        </aside>

        {{-- Collapse / Expand Handle --}}
        <button class="btn-toggle-panel shadow-sm" id="btnTogglePanel" type="button" title="Sembunyikan Panel" aria-label="Buka atau Sembunyikan Daftar Pekerjaan">
            <i class="bi bi-list-ul toggle-icon-list" id="toggleMainIcon"></i>
            <span class="toggle-label" id="toggleLabel">Daftar Pekerjaan</span>
            <span class="badge bg-white text-primary rounded-pill toggle-badge" id="badge-toggle-counter">0</span>
            <i class="bi bi-chevron-left toggle-icon-chevron" id="toggleIcon"></i>
        </button>

        {{-- Map Canvas (Kanan) --}}
        <main class="peta-canvas-wrap flex-grow-1 position-relative">
            <div id="peta" style="width: 100%; height: 100%;"></div>

            {{-- Legenda Risiko Mengambang (Pojok Kanan Bawah) --}}
            <div class="peta-legend-pill shadow-sm" id="petaLegend">
                <button class="btn-legend-toggle text-dark d-flex align-items-center gap-1" id="btnToggleLegend" type="button">
                    <i class="bi bi-shield-shaded text-primary"></i>
                    <span class="small fw-semibold">Legenda Risiko</span>
                    <i class="bi bi-chevron-down small ms-1" id="iconLegendChevron"></i>
                </button>
                <div class="legend-content p-2" id="legendContent">
                    <div class="d-flex flex-column gap-1 small mb-2">
                        <span><i class="bi bi-circle-fill text-success me-1" style="font-size: 0.65rem;"></i> Risiko Rendah</span>
                        <span><i class="bi bi-circle-fill text-warning me-1" style="font-size: 0.65rem;"></i> Risiko Sedang</span>
                        <span><i class="bi bi-circle-fill text-danger me-1" style="font-size: 0.65rem;"></i> Risiko Kritis</span>
                    </div>
                    <hr class="my-1">
                    <div class="d-flex flex-column gap-1 small">
                        <span><i class="bi bi-x-octagon-fill text-danger me-1"></i> Tender Gagal</span>
                        <span><i class="bi bi-geo-alt text-secondary me-1"></i> Lokasi Perkiraan</span>
                    </div>
                </div>
            </div>
        </main>

    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
      integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css"/>
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css"/>
<style>
    /* ===== Unified Split Layout ===== */
    .peta-split-container {
        border: none;
        border-radius: 12px;
        background: #fff;
    }
    .peta-split-body {
        height: calc(100vh - 275px);
        min-height: 560px;
    }

    /* Side Panel */
    .peta-side-panel {
        width: 380px;
        background: #ffffff;
        border-right: 1px solid #e2e8f0;
        transition: margin-left 0.28s cubic-bezier(0.4, 0, 0.2, 1), width 0.28s cubic-bezier(0.4, 0, 0.2, 1);
        z-index: 500;
        flex-shrink: 0;
    }
    .peta-side-panel.collapsed {
        margin-left: -380px;
    }

    /* Tab Display Fix: cegah tab tidak aktif memakan tempat */
    #petaTabContent {
        height: calc(100% - 49px);
    }
    #petaTabContent > .tab-pane {
        display: none !important;
        height: 100%;
    }
    #petaTabContent > .tab-pane.active {
        display: flex !important;
        flex-direction: column;
        height: 100%;
    }
    #petaTabContent > #tab-filter.active {
        display: block !important;
        height: 100%;
        overflow-y: auto;
    }

    /* Toggle Handle */
    .btn-toggle-panel {
        position: absolute;
        top: 50%;
        left: 380px;
        transform: translateY(-50%);
        width: 24px;
        height: 52px;
        background: #ffffff;
        border: 1px solid #cbd5e1;
        border-left: none;
        border-radius: 0 8px 8px 0;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        z-index: 550;
        color: #475569;
        transition: left 0.28s cubic-bezier(0.4, 0, 0.2, 1), background-color 0.15s, box-shadow 0.15s, transform 0.15s;
    }
    .btn-toggle-panel:hover {
        background: #f8fafc;
        color: #2563eb;
    }
    .btn-toggle-panel .toggle-icon-list,
    .btn-toggle-panel .toggle-label,
    .btn-toggle-panel .toggle-badge {
        display: none;
    }
    .btn-toggle-panel .toggle-icon-chevron {
        font-size: 0.85rem;
    }

    /* Floating Capsule saat panel ditutup / dilipat */
    .btn-toggle-panel.collapsed {
        top: 14px;
        left: 14px;
        transform: none;
        width: auto;
        height: 42px;
        padding: 0 16px 0 14px;
        background: #2563eb;
        color: #ffffff;
        border: 1px solid rgba(255, 255, 255, 0.3);
        border-radius: 9999px;
        box-shadow: 0 4px 14px rgba(37, 99, 235, 0.4), 0 2px 6px rgba(0, 0, 0, 0.12);
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    .btn-toggle-panel.collapsed:hover {
        background: #1d4ed8;
        color: #ffffff;
        box-shadow: 0 6px 18px rgba(37, 99, 235, 0.5);
        transform: translateY(-1px);
    }
    .btn-toggle-panel.collapsed .toggle-icon-list {
        display: inline-flex;
        align-items: center;
        font-size: 1.05rem;
    }
    .btn-toggle-panel.collapsed .toggle-label {
        display: inline;
        font-size: 0.86rem;
        font-weight: 600;
        letter-spacing: 0.01em;
        white-space: nowrap;
    }
    .btn-toggle-panel.collapsed .toggle-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background-color: #ffffff !important;
        color: #2563eb !important;
        font-weight: 700;
        font-size: 0.74rem;
        padding: 2px 8px;
        line-height: 1.1;
    }
    .btn-toggle-panel.collapsed .toggle-icon-chevron {
        display: inline-flex;
        align-items: center;
        font-size: 0.75rem;
        opacity: 0.9;
        margin-left: 2px;
    }

    /* Map Canvas */
    .peta-canvas-wrap {
        height: 100%;
        min-width: 0;
    }

    /* Card Job Items */
    .job-item-card {
        border-radius: 8px;
        border: 1px solid #e2e8f0;
        cursor: pointer;
        transition: transform 0.15s ease, background-color 0.15s ease, box-shadow 0.15s ease;
        background: #ffffff;
    }
    .job-item-card:hover {
        background-color: #f8fafc;
        transform: translateX(3px);
        box-shadow: 0 2px 8px rgba(0,0,0,0.08) !important;
    }
    .job-item-card.active-highlight {
        background-color: #eff6ff;
        border-color: #2563eb !important;
        box-shadow: 0 0 0 1px #2563eb, 0 2px 8px rgba(37,99,235,0.15) !important;
    }
    .job-item-card.border-risiko-kritis {
        border-left: 4px solid #dc3545 !important;
    }
    .job-item-card.border-risiko-sedang {
        border-left: 4px solid #f0ad4e !important;
    }
    .job-item-card.border-risiko-rendah {
        border-left: 4px solid #198754 !important;
    }

    /* Legenda Pill (Bottom Right) */
    .peta-legend-pill {
        position: absolute;
        bottom: 16px;
        right: 16px;
        z-index: 800;
        background: rgba(255, 255, 255, 0.96);
        border-radius: 8px;
        border: 1px solid #e2e8f0;
        overflow: hidden;
    }
    .btn-legend-toggle {
        background: transparent;
        border: none;
        padding: 6px 12px;
        font-size: 0.8rem;
        cursor: pointer;
        width: 100%;
    }
    .legend-content {
        display: none;
        border-top: 1px solid #f1f5f9;
        min-width: 160px;
    }
    .legend-content.show {
        display: block;
    }
    .dot-legend {
        display: inline-block;
        width: 10px;
        height: 10px;
        border-radius: 50%;
        vertical-align: -1px;
        margin-right: 4px;
    }

    /* Marker Leaflet */
    .marker-paket {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 30px !important;
        height: 30px !important;
        border-radius: 50% 50% 50% 0;
        transform: rotate(-45deg);
        border: 2px solid #fff;
        box-shadow: 0 1px 4px rgba(0,0,0,.4);
        transition: transform 0.15s ease;
    }
    .marker-paket i {
        transform: rotate(45deg);
        color: #fff;
        font-size: .8rem;
    }
    .marker-paket.risiko-rendah { background: #198754; }
    .marker-paket.risiko-sedang { background: #f0ad4e; }
    .marker-paket.risiko-kritis { background: #dc3545; }
    .marker-paket.tender-gagal::after {
        content: '';
        position: absolute;
        top: -3px;
        right: -3px;
        width: 12px;
        height: 12px;
        background: #dc3545;
        border: 2px solid #fff;
        border-radius: 50%;
    }
    .marker-paket.highlighted {
        transform: rotate(-45deg) scale(1.25);
        box-shadow: 0 0 12px rgba(37,99,235,0.8);
        border-color: #2563eb;
    }

    /* Cluster */
    .cluster-paket { background: transparent; border: 0; }
    .cluster-paket .isi-cluster {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 38px;
        height: 38px;
        border-radius: 50%;
        color: #fff;
        font-weight: 600;
        border: 2px solid #fff;
        box-shadow: 0 1px 5px rgba(0,0,0,.4);
    }
    .cluster-paket.dominan-rendah .isi-cluster { background: rgba(25,135,84,.9); }
    .cluster-paket.dominan-sedang .isi-cluster { background: rgba(240,173,78,.95); }
    .cluster-paket.dominan-kritis .isi-cluster { background: rgba(220,53,69,.92); }

    /* Popup & Leaflet Overrides */
    .leaflet-popup-content { margin: 10px 12px; min-width: 240px; }
    .leaflet-popup-content a.btn,
    .leaflet-container a.btn {
        color: #ffffff !important;
        text-decoration: none;
    }
    .leaflet-popup-content a.btn:hover,
    .leaflet-container a.btn:hover {
        color: #ffffff !important;
        background-color: #1d4ed8 !important;
    }
    .popup-prog { display: flex; height: 8px; border-radius: 4px; overflow: hidden; gap: 2px; background: #f8f9fa; }
    .popup-prog span { display: block; height: 100%; border-radius: 2px; }

    /* Responsive Mobile */
    @media (max-width: 767.98px) {
        .peta-split-body {
            height: auto;
            min-height: unset;
        }
        .peta-side-panel {
            width: 100%;
            height: 340px;
            border-right: none;
            border-bottom: 1px solid #e2e8f0;
            margin-left: 0 !important;
            transition: max-height 0.28s ease-in-out;
        }
        .peta-side-panel.collapsed {
            max-height: 0;
            overflow: hidden;
        }
        .btn-toggle-panel {
            top: auto;
            bottom: 12px;
            left: 12px !important;
            transform: none;
            width: auto;
            height: 32px;
            padding: 0 10px;
            border-radius: 6px;
            border: 1px solid #cbd5e1;
        }
        .peta-canvas-wrap {
            height: 55vh;
            min-height: 380px;
        }
    }
    @media print {
        .peta-split-container, .peta-side-panel, .btn-toggle-panel, .peta-legend-pill { display: none !important; }
    }
</style>
@endpush

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script src="https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    // ====== Inisialisasi peta Leaflet ======
    const PUSAT = [-7.4256, 109.6916]; // Pusat Kabupaten Banjarnegara
    const peta = L.map('peta', { zoomControl: false }).setView(PUSAT, 11);
    L.control.zoom({ position: 'topright' }).addTo(peta);

    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(peta);

    // ====== Layer wilayah (GeoJSON kecamatan) ======
    let layerKec = null;
    let initialBounds = null;

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

            initialBounds = layerKec.getBounds();
            peta.fitBounds(initialBounds, { padding: [20, 20] });
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

    const markersMap = new Map(); // id -> marker

    function buatMarker(p) {
        const ikon = L.divIcon({
            className: '',
            html: `<div class="marker-paket risiko-${p.risiko}${p.tender_gagal ? ' tender-gagal' : ''}" id="marker-elem-${p.id}">
                     <i class="bi bi-geo-alt-fill"></i></div>`,
            iconSize: [30, 30],
            iconAnchor: [15, 30],
            popupAnchor: [0, -28]
        });
        const m = L.marker([p.lat, p.lng], { icon: ikon, title: p.nama, paket: p });
        m.bindPopup(buatPopup(p), { maxWidth: 300 });
        m.bindTooltip(p.nama, { direction: 'top', offset: [0, -28] });

        // Klik marker: highlight card di daftar
        m.on('click', () => {
            highlightCard(p.id);
        });

        markersMap.set(p.id, m);
        return m;
    }

    // ====== Popup pekerjaan ======
    const WARNA_TAHAP = { selesai: '#198754', proses: '#0d6efd', belum: '#adb5bd' };

    function formatRupiah(n) {
        if (!n) return 'Rp 0';
        if (n >= 1e9) return 'Rp ' + (n / 1e9).toFixed(1).replace('.', ',') + ' M';
        if (n >= 1e6) return 'Rp ' + (n / 1e6).toFixed(0) + ' jt';
        return 'Rp ' + Number(n).toLocaleString('id-ID');
    }

    const esc = (s) => String(s || '').replace(/[&<>"']/g, (c) =>
        ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[c]));

    function buatPopup(p) {
        const segmen = p.tahapan.map(t =>
            `<span style="background:${WARNA_TAHAP[t.status]};width:${100 / p.tahapan.length}%"></span>`).join('');
        const presisi = p.presisi === 'tepat' ? 'Lokasi tepat'
            : p.presisi === 'desa' ? 'Lokasi desa' : 'Perkiraan wilayah (sebaran)';
        return `
        <div>
            <div class="fw-semibold" style="font-size:.86rem">${esc(p.nama)}</div>
            <div class="text-secondary" style="font-size:.74rem">${esc(p.kode)} &bull; ${esc(p.opd_singkatan || '-')} &bull; ${esc(p.pokja || '-')}</div>
            <hr class="my-2">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <span class="small">Progres</span><span class="fw-semibold">${p.progres}%</span>
            </div>
            <div class="popup-prog mb-2">${segmen}</div>
            <div class="small mb-1"><span class="text-secondary">Status:</span> ${esc(p.status_label)}
                ${p.tender_gagal ? '<span class="badge text-bg-danger ms-1">Tender gagal</span>' : ''}</div>
            <div class="small mb-1"><span class="text-secondary">Risiko:</span>
                <span class="badge badge-risiko-${p.risiko}">${esc(p.risiko)}</span></div>
            <div class="small mb-1"><span class="text-secondary">Pagu:</span> ${formatRupiah(p.pagu)}</div>
            <div class="small mb-1"><span class="text-secondary">Metode:</span> ${esc((p.metode || '-').replace('_', ' '))}</div>
            <div class="small mb-2"><span class="text-secondary">Lokasi:</span> ${esc(p.desa || p.kecamatan || p.lokasi_teks || '-')}
                <span class="text-secondary" style="font-size:.72rem">(${presisi})</span></div>
            <a href="${esc(p.url_detail)}" class="btn btn-primary btn-sm w-100 text-white">
                <i class="bi bi-box-arrow-up-right me-1"></i>Detail Pekerjaan</a>
        </div>`;
    }

    // ====== State & Variabel ======
    let semuaPaket = [];
    let paketTerfilter = [];
    let currentSort = 'default';

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

    // ====== Filter Elements ======
    const fOpd = document.getElementById('f-opd');
    const fPokja = document.getElementById('f-pokja');
    const fStatus = document.getElementById('f-status');
    const fMetode = document.getElementById('f-metode');
    const cari = document.getElementById('cari');
    const quickSearch = document.getElementById('quick-search');
    const syncBounds = document.getElementById('sync-bounds');

    function paketLolos(p) {
        if (fOpd.value && String(p.opd_id) !== fOpd.value) return false;
        if (fPokja.value && String(p.pokja_id) !== fPokja.value) return false;
        if (fStatus.value && p.status !== fStatus.value) return false;
        if (fMetode.value && p.metode !== fMetode.value) return false;
        
        // Filter input spesifik (Tab 2)
        const q1 = cari.value.trim().toLowerCase();
        if (q1) {
            const gab1 = `${p.nama} ${p.kode} ${p.opd || ''} ${p.opd_singkatan || ''} ${p.pokja || ''} ${p.desa || ''} ${p.kecamatan || ''}`.toLowerCase();
            if (!gab1.includes(q1)) return false;
        }

        // Quick search (Tab 1)
        const q2 = quickSearch.value.trim().toLowerCase();
        if (q2) {
            const gab2 = `${p.nama} ${p.kode} ${p.opd_singkatan || ''} ${p.desa || ''} ${p.kecamatan || ''}`.toLowerCase();
            if (!gab2.includes(q2)) return false;
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

    function urutkanDaftar(list) {
        const sorted = [...list];
        if (currentSort === 'risiko') {
            const weight = { kritis: 3, sedang: 2, rendah: 1 };
            sorted.sort((a, b) => (weight[b.risiko] || 0) - (weight[a.risiko] || 0) || (b.tender_gagal ? 1 : 0) - (a.tender_gagal ? 1 : 0));
        } else if (currentSort === 'pagu') {
            sorted.sort((a, b) => b.pagu - a.pagu);
        } else if (currentSort === 'progres') {
            sorted.sort((a, b) => a.progres - b.progres);
        }
        return sorted;
    }

    function renderFilter() {
        paketTerfilter = semuaPaket.filter(paketLolos);
        document.getElementById('stat-filter').textContent = paketTerfilter.length;
        document.getElementById('badge-total-daftar').textContent = paketTerfilter.length;
        const badgeToggleCounter = document.getElementById('badge-toggle-counter');
        if (badgeToggleCounter) {
            badgeToggleCounter.textContent = paketTerfilter.length;
        }

        // Render markers di cluster
        cluster.clearLayers();
        markersMap.clear();
        paketTerfilter.forEach(p => cluster.addLayer(buatMarker(p)));
        if (peta.hasLayer(cluster)) peta.removeLayer(cluster);
        peta.addLayer(cluster);

        renderDaftar();
        renderCari(paketTerfilter);
        perbaruiBadgeFilter();
    }

    function renderDaftar() {
        const daftarEl = document.getElementById('daftar-paket');
        const bounds = peta.getBounds();
        const hanyaLayar = syncBounds.checked;

        // Saring berdasarkan batas koordinat jika toggle aktif
        let tampilan = paketTerfilter;
        if (hanyaLayar && peta.getZoom() >= 8) {
            tampilan = paketTerfilter.filter(p => bounds.contains([p.lat, p.lng]));
        }

        // Urutkan
        tampilan = urutkanDaftar(tampilan);

        document.getElementById('label-count-display').textContent = `Menampilkan ${tampilan.length} dari ${paketTerfilter.length} pekerjaan`;

        if (!tampilan.length) {
            daftarEl.innerHTML = `
                <div class="text-center text-secondary py-5 small">
                    <i class="bi bi-geo-alt fs-3 d-block text-muted mb-2"></i>
                    Tidak ada pekerjaan pada filter atau area layar ini.
                </div>`;
            return;
        }

        daftarEl.innerHTML = tampilan.map(p => {
            const warnaBadge = 'badge-risiko-' + (p.risiko || 'rendah');
            return `
            <div class="card job-item-card mb-2 border-risiko-${p.risiko} shadow-sm" data-id="${p.id}">
                <div class="card-body p-2">
                    <div class="d-flex justify-content-between align-items-start gap-2 mb-1">
                        <div class="fw-bold text-dark job-title" style="font-size: 0.84rem; line-height: 1.3;">
                            ${esc(p.nama)}
                        </div>
                        <span class="badge ${warnaBadge} flex-shrink-0" style="font-size: 0.72rem;">
                            ${p.progres}%
                        </span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center text-secondary small" style="font-size: 0.72rem;">
                        <span><span class="fw-semibold text-primary">${formatRupiah(p.pagu)}</span> &bull; ${esc(p.opd_singkatan || '-')}</span>
                        <span><i class="bi bi-geo-alt me-0.5"></i>${esc(p.desa || p.kecamatan || '-')}</span>
                    </div>
                    ${p.tender_gagal ? '<div class="mt-1"><span class="badge bg-danger-subtle text-danger" style="font-size: 0.68rem;"><i class="bi bi-x-octagon me-1"></i>Tender Gagal</span></div>' : ''}
                </div>
            </div>`;
        }).join('');

        // Pasang Event Interaksi Dua Arah (Hover & Click)
        daftarEl.querySelectorAll('.job-item-card').forEach(card => {
            const id = Number(card.dataset.id);
            const m = markersMap.get(id);

            // Click -> Zoom & Open Popup
            card.addEventListener('click', () => {
                highlightCard(id);
                if (m) {
                    cluster.zoomToShowLayer(m, () => {
                        m.openPopup();
                    });
                }
            });

            // Hover In -> Pulse Marker & Open Tooltip
            card.addEventListener('mouseenter', () => {
                if (m) {
                    const el = document.getElementById(`marker-elem-${id}`);
                    if (el) el.classList.add('highlighted');
                    m.openTooltip();
                }
            });

            // Hover Out -> Reset
            card.addEventListener('mouseleave', () => {
                if (m) {
                    const el = document.getElementById(`marker-elem-${id}`);
                    if (el) el.classList.remove('highlighted');
                    m.closeTooltip();
                }
            });
        });
    }

    function highlightCard(id) {
        document.querySelectorAll('.job-item-card').forEach(c => {
            c.classList.toggle('active-highlight', Number(c.dataset.id) === id);
            if (Number(c.dataset.id) === id) {
                c.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }
        });
    }

    // ====== Event Listeners Filter ======
    [fOpd, fPokja, fStatus, fMetode].forEach(el => el.addEventListener('change', renderFilter));
    cari.addEventListener('input', renderFilter);
    quickSearch.addEventListener('input', renderDaftar);
    syncBounds.addEventListener('change', renderDaftar);

    // Filter bounds dinamis saat peta digeser / dizoom
    peta.on('moveend zoomend', () => {
        if (syncBounds.checked) {
            renderDaftar();
        }
    });

    // Reset Filter Button
    document.getElementById('reset-filter').addEventListener('click', () => {
        [fOpd, fPokja, fStatus, fMetode].forEach(el => el.value = '');
        cari.value = '';
        quickSearch.value = '';
        renderFilter();
    });

    // Reset View Button
    document.getElementById('btn-reset-view').addEventListener('click', () => {
        if (initialBounds) {
            peta.fitBounds(initialBounds, { padding: [20, 20] });
        } else {
            peta.setView(PUSAT, 11);
        }
    });

    // Sort Dropdown
    document.querySelectorAll('#sort-menu a').forEach(a => {
        a.addEventListener('click', (e) => {
            e.preventDefault();
            document.querySelectorAll('#sort-menu a').forEach(el => el.classList.remove('active'));
            a.classList.add('active');
            currentSort = a.dataset.sort;
            renderDaftar();
        });
    });

    // ====== Collapsible Side Panel Toggle ======
    const sidePanel = document.getElementById('petaSidePanel');
    const btnTogglePanel = document.getElementById('btnTogglePanel');
    const toggleIcon = document.getElementById('toggleIcon');

    btnTogglePanel.addEventListener('click', () => {
        const isCollapsed = sidePanel.classList.toggle('collapsed');
        btnTogglePanel.classList.toggle('collapsed', isCollapsed);
        toggleIcon.className = isCollapsed ? 'bi bi-chevron-right toggle-icon-chevron' : 'bi bi-chevron-left toggle-icon-chevron';
        btnTogglePanel.setAttribute('title', isCollapsed ? 'Buka Daftar Pekerjaan' : 'Sembunyikan Panel');

        // Beritahu Leaflet agar kanvas peta meregang mulus tanpa distorsi
        setTimeout(() => {
            peta.invalidateSize({ animate: true });
        }, 300);
    });

    // ====== Legenda Toggle ======
    const btnToggleLegend = document.getElementById('btnToggleLegend');
    const legendContent = document.getElementById('legendContent');
    const iconLegendChevron = document.getElementById('iconLegendChevron');

    btnToggleLegend.addEventListener('click', () => {
        const isShown = legendContent.classList.toggle('show');
        iconLegendChevron.className = isShown ? 'bi bi-chevron-up small ms-1' : 'bi bi-chevron-down small ms-1';
    });

    // ====== Pencarian Spesifik: saran dropdown ======
    function renderCari(hasil) {
        const box = document.getElementById('hasil-cari');
        const q = cari.value.trim();
        if (!q) { box.innerHTML = ''; return; }
        const saran = hasil.slice(0, 6).map(p => `
            <button type="button" class="list-group-item list-group-item-action py-1 hasil-cari" data-id="${p.id}" style="font-size:.78rem">
                <i class="bi bi-geo-alt me-1"></i>${esc(p.nama)}
            </button>`).join('');
        box.innerHTML = saran;
        box.querySelectorAll('.hasil-cari').forEach(el => {
            el.addEventListener('click', () => {
                const p = semuaPaket.find(x => x.id == el.dataset.id);
                if (!p) return;
                const m = markersMap.get(p.id);
                if (m) {
                    cluster.zoomToShowLayer(m, () => m.openPopup());
                    highlightCard(p.id);
                }
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
                    <div class="fw-semibold">Kecamatan ${esc(namaKec)}</div>
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
        // Buka tab filter jika terlipat
        const tabFilterBtn = document.getElementById('tab-filter-btn');
        if (tabFilterBtn) {
            bootstrap.Tab.getOrCreateInstance(tabFilterBtn).show();
        }
        if (sidePanel.classList.contains('collapsed')) {
            btnTogglePanel.click();
        }
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
