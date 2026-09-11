<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - Pokja LPSE Banjarnegara</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        :root {
            --sidebar-width: 260px;
            --topbar-height: 60px;
            --primary-color: #0d6efd;
            --sidebar-bg: #1e293b;
            --sidebar-active: #2563eb;
        }
        body {
            background-color: #f1f5f9;
            font-family: 'Segoe UI', system-ui, sans-serif;
            overflow-x: hidden;
        }
        /* ===== Sidebar ===== */
        .sidebar {
            position: fixed;
            top: 0; left: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: var(--sidebar-bg);
            color: #cbd5e1;
            z-index: 1040;
            transition: all 0.3s ease;
            overflow-y: auto;
        }
        .sidebar-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 18px 20px;
            border-bottom: 1px solid rgba(255,255,255,0.08);
        }
        .sidebar-brand .brand-icon {
            width: 42px; height: 42px;
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.3rem; color: #fff;
            flex-shrink: 0;
        }
        .sidebar-brand h6 { margin: 0; color: #fff; font-weight: 700; font-size: 0.95rem; }
        .sidebar-brand small { color: #94a3b8; font-size: 0.72rem; }
        .sidebar-nav { padding: 14px 12px; }
        .sidebar-nav .nav-label {
            font-size: 0.68rem; text-transform: uppercase; letter-spacing: 1px;
            color: #8fa3bd; padding: 12px 12px 6px; font-weight: 700;
        }
        .sidebar-nav a {
            display: flex; align-items: center; gap: 12px;
            padding: 10px 14px; margin-bottom: 2px;
            color: #cbd5e1; text-decoration: none;
            border-radius: 8px; font-size: 0.9rem;
            transition: all 0.15s ease;
        }
        .sidebar-nav a:hover { background: rgba(255,255,255,0.07); color: #fff; }
        .sidebar-nav a.active { background: var(--sidebar-active); color: #fff; font-weight: 600; }
        .sidebar-nav a i { width: 20px; text-align: center; font-size: 1.05rem; }
        /* ===== Topbar ===== */
        .topbar {
            position: fixed;
            top: 0; left: var(--sidebar-width);
            right: 0; height: var(--topbar-height);
            background: #fff;
            border-bottom: 1px solid #e2e8f0;
            display: flex; align-items: center;
            padding: 0 24px;
            z-index: 1030;
            transition: left 0.3s ease;
        }
        .main-content {
            margin-left: var(--sidebar-width);
            margin-top: var(--topbar-height);
            padding: 24px;
            min-height: calc(100vh - var(--topbar-height));
            transition: margin 0.3s ease;
        }
        /* ===== Cards ===== */
        .card { border: none; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.06); }
        .stat-card { position: relative; overflow: hidden; }
        .stat-card .stat-icon {
            width: 48px; height: 48px;
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.4rem;
        }
        .stat-card h3 { font-size: clamp(1.1rem, 4vw, 1.5rem); font-weight: 700; margin: 4px 0 0; overflow-wrap: anywhere; }
        .stat-card .stat-label { font-size: 0.8rem; color: #64748b; margin: 0; font-weight: 500; }
        .badge.purple { background-color: #7c3aed; color: #fff; }
        /* Kontras WCAG AA — hasil audit antislop (#1) */
        .text-secondary, .text-muted { color: #5a6268 !important; }
        .progress { border-radius: 999px; }
        /* ===== Table ===== */
        .table th { font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px; color: #64748b; font-weight: 600; }
        .table td { vertical-align: middle; font-size: 0.875rem; }
        /* ===== Responsive ===== */
        .sidebar-toggle { display: none; }
        @media (max-width: 992px) {
            .sidebar { left: calc(-1 * var(--sidebar-width)); }
            .sidebar.show { left: 0; box-shadow: 0 0 40px rgba(0,0,0,0.3); }
            .topbar { left: 0; }
            .main-content { margin-left: 0; }
            .sidebar-toggle { display: inline-flex; }
        }
        /* Backdrop mobile */
        .sidebar-backdrop {
            position: fixed; inset: 0;
            background: rgba(0,0,0,0.45);
            z-index: 1039; display: none;
        }
        .sidebar-backdrop.show { display: block; }
        /* Mobile: tabel lebar menjadi kartu (#3) */
        @media (max-width: 767.98px) {
            .table-card thead { display: none; }
            .table-card, .table-card tbody, .table-card tr, .table-card td { display: block; width: 100%; }
            .table-card tr { margin-bottom: 12px; border: 1px solid #e2e8f0; border-radius: 10px; background: #fff; overflow: hidden; }
            .table-card td { display: flex; justify-content: space-between; align-items: center; gap: 12px; border: none !important; border-bottom: 1px solid #f1f5f9 !important; padding: 8px 12px; text-align: right; white-space: normal !important; }
            .table-card td::before { content: attr(data-label); font-weight: 600; color: #5a6268; text-align: left; }
            .table-card td[colspan]::before { content: none; }
            .table-card td[colspan] { justify-content: center; text-align: center; }
            .table-card td:last-child { border-bottom: none !important; }
        }
        /* Identitas cetak (#6) */
        .print-footer { display: none; }
        @media print {
            .sidebar, .topbar, .no-print { display: none !important; }
            .main-content { margin: 0; padding: 0; }
            .print-footer { display: block !important; margin-top: 20px; padding-top: 8px; border-top: 1px solid #adb5bd; font-size: 11px; color: #495057; }
        }
    </style>
    @stack('styles')
</head>
<body>
    <!-- ===== Sidebar ===== -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <div class="brand-icon"><i class="bi bi-shield-check"></i></div>
            <div>
                <h6>Pokja LPSE</h6>
                <small>Kab. Banjarnegara</small>
            </div>
        </div>
        <nav class="sidebar-nav">
            <div class="nav-label">Monitoring Pokja</div>
            <a href="{{ route('pokja.index') }}" class="{{ request()->routeIs('pokja.*') ? 'active' : '' }}">
                <i class="bi bi-person-badge"></i> Kinerja Pokja
            </a>
            <a href="{{ route('pemantauan.index') }}" class="{{ request()->routeIs('pemantauan.*') ? 'active' : '' }}">
                <i class="bi bi-search-heart"></i> Pemantauan
            </a>
            <a href="{{ route('pokja.kelola') }}" class="{{ request()->routeIs('pokja.kelola', 'pokja.create', 'pokja.edit') ? 'active' : '' }}">
                <i class="bi bi-diagram-3"></i> Kelola Pokja
            </a>
            <div class="nav-label">Data Pengadaan</div>
            <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>
            <a href="{{ route('paket.index') }}" class="{{ request()->routeIs('paket.*') ? 'active' : '' }}">
                <i class="bi bi-box-seam"></i> Paket Pengadaan
            </a>
            <a href="{{ route('opd.index') }}" class="{{ request()->routeIs('opd.*') ? 'active' : '' }}">
                <i class="bi bi-building"></i> Perangkat Daerah
            </a>
            <a href="{{ route('penyedia.index') }}" class="{{ request()->routeIs('penyedia.*') ? 'active' : '' }}">
                <i class="bi bi-people"></i> Penyedia
            </a>
            <div class="nav-label">Laporan</div>
            <a href="{{ route('laporan.index') }}" class="{{ request()->routeIs('laporan.*') ? 'active' : '' }}">
                <i class="bi bi-file-earmark-bar-graph"></i> Laporan
            </a>
            <a href="{{ route('audit.index') }}" class="{{ request()->routeIs('audit.*') ? 'active' : '' }}">
                <i class="bi bi-clock-history"></i> Audit Log
            </a>
        </nav>
    </aside>
    <div class="sidebar-backdrop" id="sidebarBackdrop"></div>

    <!-- ===== Topbar ===== -->
    <header class="topbar">
        <button class="btn btn-light btn-sm sidebar-toggle" id="sidebarToggle">
            <i class="bi bi-list"></i>
        </button>
        <div class="ms-2 d-none d-md-block">
            <span class="fw-semibold text-secondary">@yield('title', 'Dashboard')</span>
        </div>
        <div class="ms-auto d-flex align-items-center gap-3">
            <span class="badge bg-primary-subtle text-primary d-none d-md-inline">
                <i class="bi bi-calendar3"></i> TA {{ request('tahun', date('Y')) }}
            </span>
            <div class="dropdown">
                <button class="btn btn-light btn-sm d-flex align-items-center gap-2" data-bs-toggle="dropdown">
                    <i class="bi bi-person-circle fs-5 text-primary"></i>
                    <span class="d-none d-md-inline fw-semibold">{{ auth()->user()->name ?? 'Guest' }}</span>
                    <i class="bi bi-chevron-down small"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow">
                    <li><h6 class="dropdown-header">{{ auth()->user()->email ?? '' }}</h6></li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="dropdown-item text-danger">
                                <i class="bi bi-box-arrow-right me-2"></i>Keluar
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </header>

    <!-- ===== Content ===== -->
    <main class="main-content">
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @yield('content')

        <footer class="print-footer">
            Dicetak: {{ now()->translatedFormat('d F Y H:i') }} WIB &bull; Sistem Monitoring Pokja LPSE Kab. Banjarnegara &bull; Halaman: {{ request()->path() == '/' ? 'dashboard' : request()->path() }}
        </footer>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
    <script>
        // Sidebar toggle (mobile)
        const sidebar = document.getElementById('sidebar');
        const backdrop = document.getElementById('sidebarBackdrop');
        document.getElementById('sidebarToggle')?.addEventListener('click', () => {
            sidebar.classList.add('show');
            backdrop.classList.add('show');
        });
        backdrop.addEventListener('click', () => {
            sidebar.classList.remove('show');
            backdrop.classList.remove('show');
        });
        // Auto-dismiss alerts
        setTimeout(() => {
            document.querySelectorAll('.alert-dismissible').forEach(el => {
                bootstrap.Alert.getOrCreateInstance(el)?.close();
            });
        }, 5000);
        // Tooltip global (dipakai mini bar progres tahapan dsb.)
        document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
            new bootstrap.Tooltip(el);
        });
    </script>
    @stack('scripts')
</body>
</html>
