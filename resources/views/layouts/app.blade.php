<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - Pokja LPSE Banjarnegara</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- Google Fonts: Inter & JetBrains Mono -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@500;600&display=swap" rel="stylesheet">

    <style>
        :root {
            --sidebar-width: 250px;
            --topbar-height: 56px;
            --navy-dark: #0f172a;
            --navy-sidebar: #1e293b;
            --blue-primary: #1d4ed8;
            --blue-hover: #1e40af;

            --bg-canvas: #f8fafc;
            --bg-panel: #ffffff;
            --text-main: #0f172a;
            --text-secondary: #475569;
            --text-muted: #64748b;

            --border-panel: #cbd5e1;
            --border-divider: #e2e8f0;

            --radius-krisp: 4px;
            --radius-input: 4px;
            --radius-md: 4px;

            /* Vibes Coding Anti-Slop Core Tokens */
            --vibe-background: #f8fafc;
            --vibe-surface: #ffffff;
            --vibe-text-main: #0f172a;
            --vibe-text-sub: #475569;
            --vibe-accent-1: #1d4ed8;
            --vibe-accent-2: #0f172a;
            --vibe-error: #dc2626;
            --vibe-success: #16a34a;
            --vibe-warning: #d97706;
            --vibe-font-main: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            --vibe-font-head: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            --vibe-transition: all 0.15s ease-in-out;
        }

        body {
            background-color: var(--bg-canvas);
            color: var(--text-main);
            font-family: var(--vibe-font-main);
            font-size: 14px;
            line-height: 1.5;
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
        }

        /* ===== Typography Normalization (Consistent & Legible Scale) ===== */
        h1, .h1 { font-size: 1.75rem; font-weight: 700; }
        h2, .h2 { font-size: 1.5rem; font-weight: 700; }
        h3, .h3 { font-size: 1.35rem; font-weight: 700; }
        h4, .h4 { font-size: 1.2rem; font-weight: 700; }
        h5, .h5 { font-size: 1.05rem; font-weight: 600; }
        h6, .h6 { font-size: 0.95rem; font-weight: 600; }
        small, .small { font-size: 12.5px; line-height: 1.4; }
        .text-muted, .text-secondary { color: #5a6268 !important; }

        /* ===== Sidebar (Swiss Government Data-Dense) ===== */
        .sidebar {
            position: fixed;
            top: 0; left: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: var(--navy-dark);
            border-right: 1px solid #1e293b;
            color: #94a3b8;
            z-index: 1040;
            transition: all 0.2s ease;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
        }
        .sidebar-brand {
            height: var(--topbar-height);
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 0 18px;
            border-bottom: 1px solid #1e293b;
            background: #090d16;
            flex-shrink: 0;
        }
        .sidebar-brand .brand-icon {
            width: 30px; height: 30px;
            background: var(--blue-primary);
            border-radius: var(--radius-krisp);
            display: flex; align-items: center; justify-content: center;
            font-size: 15px; color: #fff;
            flex-shrink: 0;
        }
        .sidebar-brand h6 {
            margin: 0; color: #fff;
            font-weight: 700; font-size: 14px;
            letter-spacing: 0.3px; line-height: 1.2;
        }
        .sidebar-brand small {
            color: #94a3b8; font-size: 11.5px;
        }
        .sidebar-nav {
            padding: 8px 0;
            flex-grow: 1;
        }
        .sidebar-nav .nav-label {
            font-size: 11px; text-transform: uppercase; letter-spacing: 0.8px;
            color: #64748b; padding: 14px 18px 6px; font-weight: 700;
        }
        .sidebar-nav a {
            display: flex; align-items: center; gap: 12px;
            padding: 8px 18px; margin: 0;
            color: #cbd5e1; text-decoration: none;
            border-radius: 0; font-size: 13.5px; font-weight: 500;
            border-left: 3px solid transparent;
            transition: background 0.1s, color 0.1s;
        }
        .sidebar-nav a:hover {
            background: #1e293b; color: #fff;
        }
        .sidebar-nav a.active {
            background: #1e293b; color: #fff;
            font-weight: 600;
            border-left-color: #3b82f6;
        }
        .sidebar-nav a i {
            width: 18px; text-align: center;
            font-size: 16px; color: #94a3b8;
        }
        .sidebar-nav a.active i {
            color: #60a5fa;
        }

        /* ===== Topbar (Clean Executive) ===== */
        .topbar {
            position: fixed;
            top: 0; left: var(--sidebar-width);
            right: 0; height: var(--topbar-height);
            background: #fff;
            border-bottom: 1px solid var(--border-panel);
            display: flex; align-items: center;
            padding: 0 24px;
            z-index: 1030;
            transition: left 0.2s ease;
        }
        .main-content {
            margin-left: var(--sidebar-width);
            margin-top: var(--topbar-height);
            padding: 20px 24px;
            min-height: calc(100vh - var(--topbar-height));
            transition: margin 0.2s ease;
        }

        /* ===== Global Container & Card Rigidity (Anti-AI-Slop) ===== */
        .card {
            border: 1px solid var(--border-panel);
            border-radius: var(--radius-krisp);
            box-shadow: none;
            background: #ffffff;
        }
        .card-header {
            background: #f8fafc;
            border-bottom: 1px solid var(--border-panel);
            padding: 10px 16px;
        }

        /* Form & Button Crisp */
        .form-control, .form-select {
            border-radius: var(--radius-input);
            font-size: 13.5px;
            padding: 6px 12px;
            border: 1px solid var(--border-panel);
            color: var(--text-main);
        }
        .form-control:focus, .form-select:focus {
            border-color: var(--blue-primary);
            box-shadow: none;
        }
        .form-select-sm, .form-control-sm {
            font-size: 12.5px;
            padding: 4px 10px;
        }
        .btn {
            border-radius: var(--radius-input);
            font-size: 13px;
            font-weight: 500;
            padding: 6px 14px;
        }
        .btn-sm {
            font-size: 12.5px;
            padding: 4px 10px;
        }
        .btn-primary {
            background-color: var(--blue-primary);
            border-color: var(--blue-primary);
        }
        .btn-primary:hover {
            background-color: var(--blue-hover);
            border-color: var(--blue-hover);
        }

        /* Stat Card Fallback */
        .stat-card { position: relative; overflow: hidden; }
        .stat-card .stat-icon {
            width: 42px; height: 42px;
            border-radius: var(--radius-krisp);
            display: flex; align-items: center; justify-content: center;
            font-size: 1.35rem;
        }
        .stat-card h3 {
            font-size: 1.5rem; font-weight: 700;
            margin: 2px 0 0; letter-spacing: -0.5px;
        }
        .stat-card .stat-label {
            font-size: 0.8rem; color: var(--text-secondary);
            margin: 0; font-weight: 600; text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* ===== Executive Panel & Metrics Strip ===== */
        .gov-panel {
            background: var(--bg-panel);
            border: 1px solid var(--border-panel);
            border-radius: var(--radius-krisp);
            margin-bottom: 16px;
        }
        .gov-panel-header {
            padding: 10px 16px;
            background: #f8fafc;
            border-bottom: 1px solid var(--border-panel);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .gov-panel-title {
            font-size: 13px;
            font-weight: 700;
            color: var(--text-main);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 0;
        }
        .gov-panel-body {
            padding: 16px;
        }

        .metrics-strip {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            border: 1px solid var(--border-panel);
            background: #ffffff;
            border-radius: var(--radius-krisp);
            margin-bottom: 16px;
        }
        .metric-cell {
            padding: 14px 18px;
            border-right: 1px solid var(--border-panel);
        }
        .metric-cell:last-child {
            border-right: none;
        }
        .metric-caption {
            font-size: 12px;
            font-weight: 600;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 3px;
        }
        .metric-number {
            font-size: 24px;
            font-weight: 700;
            color: var(--text-main);
            letter-spacing: -0.5px;
            line-height: 1.15;
        }
        .metric-unit {
            font-size: 13.5px;
            font-weight: 500;
            color: var(--text-muted);
        }
        .metric-subtext {
            margin-top: 5px;
            font-size: 12px;
            color: var(--text-muted);
        }

        /* Status Ratio Track */
        .ratio-track-container {
            background: #ffffff;
            border: 1px solid var(--border-panel);
            border-radius: var(--radius-krisp);
            padding: 12px 16px;
            margin-bottom: 16px;
        }
        .stacked-progress-bar {
            height: 7px;
            background: #e2e8f0;
            border-radius: 1px;
            display: flex;
            overflow: hidden;
            margin: 8px 0;
        }
        .track-seg-success { background: #16a34a; }
        .track-seg-primary { background: #2563eb; }
        .track-seg-warning { background: #d97706; }
        .track-seg-info { background: #0284c7; }
        .track-seg-danger { background: #dc2626; }

        .ratio-legend-row {
            display: flex;
            flex-wrap: wrap;
            gap: 18px;
            font-size: 12px;
            color: var(--text-secondary);
        }

        /* Indicator Dots */
        .status-dot {
            display: inline-block;
            width: 7px;
            height: 7px;
            border-radius: 50%;
            margin-right: 6px;
            vertical-align: middle;
        }

        /* Standard WCAG Table & Data Dense */
        .table, .gov-table { font-size: 13.5px; width: 100%; border-collapse: collapse; }
        .table th, .gov-table th {
            font-size: 11.5px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #334155;
            font-weight: 600;
            background: #f8fafc;
            padding: 9px 14px;
            border-top: 1px solid var(--border-panel);
            border-bottom: 1px solid var(--border-panel);
        }
        .table td, .gov-table td {
            vertical-align: middle;
            font-size: 13.5px;
            padding: 10px 14px;
            border-bottom: 1px solid var(--border-divider);
            color: var(--text-main);
        }
        .table tr:hover td, .gov-table tr:hover td {
            background-color: #f8fafc;
        }
        .progress { border-radius: 2px; height: 6px; background-color: #e2e8f0; }

        /* Kontras WCAG AA */
        .text-secondary, .text-muted { color: #5a6268 !important; }

        /* ===== High-Contrast WCAG AA Badge System (Anti-Invisible-Text) ===== */
        .badge {
            font-size: 11.5px;
            font-weight: 600;
            letter-spacing: 0.3px;
            padding: 4px 8px;
            border-radius: var(--radius-krisp);
            display: inline-flex;
            align-items: center;
            gap: 4px;
            line-height: 1.25;
            border: 1px solid transparent;
            text-decoration: none;
            white-space: nowrap;
        }

        /* 1. Status Badges — Kontras Tinggi Terjamin */
        .badge.status-draft,
        .badge.text-bg-secondary,
        .badge.bg-secondary,
        .badge.bg-secondary-subtle {
            background-color: #f1f5f9 !important;
            border-color: #cbd5e1 !important;
            color: #334155 !important;
        }

        .badge.status-persiapan,
        .badge.text-bg-info,
        .badge.bg-info,
        .badge.bg-info-subtle {
            background-color: #e0f2fe !important;
            border-color: #7dd3fc !important;
            color: #0369a1 !important;
        }

        .badge.status-pemilihan,
        .badge.text-bg-warning,
        .badge.bg-warning,
        .badge.bg-warning-subtle {
            background-color: #fef3c7 !important;
            border-color: #fcd34d !important;
            color: #92400e !important;
        }

        .badge.status-kontrak,
        .badge.text-bg-primary,
        .badge.bg-primary,
        .badge.bg-primary-subtle {
            background-color: #dbeafe !important;
            border-color: #93c5fd !important;
            color: #1d4ed8 !important;
        }

        .badge.status-pelaksanaan,
        .badge.text-bg-purple,
        .badge.bg-purple,
        .badge.purple {
            background-color: #f3e8ff !important;
            border-color: #d8b4fe !important;
            color: #6b21a8 !important;
        }

        .badge.status-selesai,
        .badge.text-bg-success,
        .badge.bg-success,
        .badge.bg-success-subtle {
            background-color: #dcfce7 !important;
            border-color: #86efac !important;
            color: #15803d !important;
        }

        .badge.status-batal,
        .badge.text-bg-danger,
        .badge.bg-danger,
        .badge.bg-danger-subtle {
            background-color: #fee2e2 !important;
            border-color: #fca5a5 !important;
            color: #b91c1c !important;
        }

        .badge.text-bg-dark,
        .badge.bg-dark {
            background-color: #0f172a !important;
            border-color: #0f172a !important;
            color: #ffffff !important;
        }

        .badge.text-bg-light,
        .badge.bg-light {
            background-color: #f8fafc !important;
            border-color: #cbd5e1 !important;
            color: #334155 !important;
        }

        /* 2. Risiko Badges — Standar Kontras Tegas */
        .badge-risiko-rendah {
            background-color: #dcfce7 !important;
            border-color: #86efac !important;
            color: #15803d !important;
        }

        .badge-risiko-sedang {
            background-color: #fef3c7 !important;
            border-color: #fcd34d !important;
            color: #92400e !important;
        }

        .badge-risiko-tinggi {
            background-color: #ffedd5 !important;
            border-color: #fed7aa !important;
            color: #c2410c !important;
        }

        .badge-risiko-kritis {
            background-color: #fee2e2 !important;
            border-color: #fca5a5 !important;
            color: #b91c1c !important;
        }

        /* 3. Kualifikasi Badges — Terbaca Jelas */
        .badge-kual-besar {
            background-color: #0f172a !important;
            border-color: #0f172a !important;
            color: #ffffff !important;
        }

        .badge-kual-menengah {
            background-color: #dbeafe !important;
            border-color: #93c5fd !important;
            color: #1d4ed8 !important;
        }

        .badge-kual-kecil {
            background-color: #f1f5f9 !important;
            border-color: #cbd5e1 !important;
            color: #334155 !important;
        }

        /* Mobile responsive */
        .sidebar-toggle { display: none; }
        @media (max-width: 992px) {
            .sidebar { left: calc(-1 * var(--sidebar-width)); }
            .sidebar.show { left: 0; box-shadow: 0 0 40px rgba(0,0,0,0.3); }
            .topbar { left: 0; }
            .main-content { margin-left: 0; }
            .sidebar-toggle { display: inline-flex; }
            .metrics-strip { grid-template-columns: repeat(2, 1fr); }
            .metric-cell:nth-child(2) { border-right: none; }
            .metric-cell:nth-child(1), .metric-cell:nth-child(2) { border-bottom: 1px solid var(--border-panel); }
        }
        .sidebar-backdrop {
            position: fixed; inset: 0;
            background: rgba(0,0,0,0.45);
            z-index: 1039; display: none;
        }
        .sidebar-backdrop.show { display: block; }

        /* Mobile: tabel lebar menjadi kartu */
        @media (max-width: 767.98px) {
            .table-card thead { display: none; }
            .table-card, .table-card tbody, .table-card tr, .table-card td { display: block; width: 100%; }
            .table-card tr { margin-bottom: 12px; border: 1px solid var(--border-panel); border-radius: var(--radius-krisp); background: #fff; overflow: hidden; }
            .table-card td { display: flex; justify-content: space-between; align-items: center; gap: 12px; border: none !important; border-bottom: 1px solid var(--border-divider) !important; padding: 8px 12px; text-align: right; white-space: normal !important; }
            .table-card td::before { content: attr(data-label); font-weight: 600; color: #5a6268; text-align: left; }
            .table-card td[colspan]::before { content: none; }
            .table-card td[colspan] { justify-content: center; text-align: center; }
            .table-card td:last-child { border-bottom: none !important; }
        }
        /* ===== Swiss Government Crisp Toast (Anti-AI-Slop) ===== */
        .toast-gov {
            background-color: var(--vibe-surface);
            border: 1px solid var(--border-panel);
            border-left: 4px solid var(--blue-primary);
            border-radius: var(--radius-krisp);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            font-size: 13px;
            color: var(--vibe-text-main);
            min-width: 320px;
            max-width: 380px;
            margin-bottom: 8px;
            animation: toastSlideIn 0.2s cubic-bezier(0.16, 1, 0.3, 1);
            overflow: hidden;
            pointer-events: auto;
        }
        .toast-gov.toast-critical,
        .toast-gov.toast-danger {
            border-left-color: var(--vibe-error);
        }
        .toast-gov.toast-warning {
            border-left-color: var(--vibe-warning);
        }
        .toast-gov.toast-success {
            border-left-color: var(--vibe-success);
        }
        .toast-gov.toast-info {
            border-left-color: var(--vibe-accent-1);
        }
        .toast-gov-header {
            padding: 8px 12px;
            border-bottom: 1px solid var(--border-divider);
            background-color: #f8fafc;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .toast-gov-category {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--vibe-text-sub);
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .toast-gov-body {
            padding: 10px 12px;
            display: flex;
            flex-direction: column;
            gap: 2px;
            cursor: pointer;
            text-decoration: none;
            color: inherit;
        }
        .toast-gov-body:hover {
            background-color: #f8fafc;
        }
        .toast-gov-title {
            font-size: 13px;
            font-weight: 600;
            color: var(--vibe-text-main);
            line-height: 1.35;
        }
        .toast-gov-message {
            font-size: 12px;
            color: var(--vibe-text-sub);
            line-height: 1.4;
            margin: 0;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .toast-gov-close {
            background: none;
            border: none;
            padding: 0 4px;
            font-size: 16px;
            line-height: 1;
            color: #64748b;
            cursor: pointer;
            border-radius: 2px;
        }
        .toast-gov-close:hover {
            color: #0f172a;
            background-color: #e2e8f0;
        }
        @keyframes toastSlideIn {
            from { transform: translateX(110%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        @keyframes toastSlideOut {
            from { transform: translateX(0); opacity: 1; }
            to { transform: translateX(110%); opacity: 0; }
        }

        /* ===== R1: Workflow Breadcrumb & Context Bar ===== */
        .workflow-breadcrumb {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 8px;
            background: var(--bg-panel);
            border: 1px solid var(--border-panel);
            border-radius: var(--radius-krisp);
            padding: 8px 14px;
            font-size: 0.8125rem;
        }
        .workflow-breadcrumb .breadcrumb-links {
            display: flex;
            align-items: center;
            gap: 6px;
            color: var(--text-muted);
            margin: 0;
            padding: 0;
            list-style: none;
        }
        .workflow-breadcrumb .breadcrumb-links a {
            color: var(--blue-primary);
            text-decoration: none;
            font-weight: 500;
        }
        .workflow-breadcrumb .breadcrumb-links a:hover {
            text-decoration: underline;
        }
        .workflow-breadcrumb .breadcrumb-links .sep {
            color: var(--border-panel);
            font-size: 0.75rem;
        }
        .workflow-breadcrumb .breadcrumb-links .active {
            color: var(--text-main);
            font-weight: 600;
        }
        .workflow-phase-steps {
            display: inline-flex;
            align-items: center;
            gap: 2px;
            background: var(--bg-canvas);
            padding: 2px 6px;
            border-radius: var(--radius-krisp);
            border: 1px solid var(--border-divider);
        }
        .workflow-phase-step {
            font-size: 0.7rem;
            padding: 2px 6px;
            border-radius: 2px;
            color: var(--text-muted);
            white-space: nowrap;
        }
        .workflow-phase-step.active {
            background: var(--blue-primary);
            color: #ffffff;
            font-weight: 600;
        }
        .workflow-phase-step.completed {
            background: #dcfce7;
            color: #166534;
            font-weight: 500;
        }
        .workflow-phase-step .sep-dot {
            margin-left: 4px;
            color: var(--border-panel);
        }

        /* ===== R3: Vertical Timeline ===== */
        .timeline-vertical {
            position: relative;
            padding-left: 24px;
            margin-left: 8px;
        }
        .timeline-vertical::before {
            content: '';
            position: absolute;
            top: 8px;
            bottom: 8px;
            left: 5px;
            width: 2px;
            background: var(--border-divider);
        }
        .timeline-item {
            position: relative;
            margin-bottom: 14px;
        }
        .timeline-item:last-child {
            margin-bottom: 0;
        }
        .timeline-marker {
            position: absolute;
            left: -24px;
            top: 5px;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: #ffffff;
            border: 2px solid var(--border-panel);
            box-sizing: border-box;
            z-index: 2;
        }
        .timeline-marker.marker-danger {
            border-color: var(--vibe-error);
            background: var(--vibe-error);
        }
        .timeline-marker.marker-warning {
            border-color: var(--vibe-warning);
            background: var(--vibe-warning);
        }
        .timeline-marker.marker-info {
            border-color: var(--blue-primary);
            background: var(--blue-primary);
        }
        .timeline-marker.marker-success {
            border-color: var(--vibe-success);
            background: var(--vibe-success);
        }
        .timeline-content {
            background: var(--bg-panel);
            border: 1px solid var(--border-panel);
            border-radius: var(--radius-krisp);
            padding: 8px 12px;
            font-size: 0.8125rem;
        }

        /* ===== R7: Modal Konfirmasi Destruktif ===== */
        .modal-konfirmasi .modal-content {
            border-radius: var(--radius-krisp);
            border: 1px solid var(--border-panel);
            box-shadow: 0 4px 12px rgba(15, 23, 42, 0.08);
        }
        .modal-konfirmasi .modal-header {
            border-bottom: 1px solid var(--border-divider);
            padding: 10px 16px;
        }
        .modal-konfirmasi .modal-body {
            padding: 16px;
            font-size: 0.875rem;
        }
        .modal-konfirmasi .modal-footer {
            border-top: 1px solid var(--border-divider);
            padding: 8px 16px;
        }

        /* Identitas cetak */
        .print-footer { display: none; }
        @media print {
            .sidebar, .topbar, .no-print, .toast-container { display: none !important; }
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
                <h6>SIMONPOKJA</h6>
                <small>LPSE Kab. Banjarnegara</small>
            </div>
        </div>
        <nav class="sidebar-nav">
            @if (auth()->user()?->isPokja())
                <div class="nav-label">Panitia Pengadaan</div>
                <a href="{{ route('pokja.dasbor') }}" class="{{ request()->routeIs('pokja.dasbor', 'pokja.riwayat') ? 'active' : '' }}">
                    <i class="bi bi-speedometer2"></i> Dasbor Pokja
                </a>
                <a href="{{ route('pokja.sanggahan') }}" class="{{ request()->routeIs('pokja.sanggahan*') ? 'active' : '' }}">
                    <i class="bi bi-flag"></i> Sanggahan & SLA
                </a>
                <a href="{{ route('pokja.jadwal') }}" class="{{ request()->routeIs('pokja.jadwal*') ? 'active' : '' }}">
                    <i class="bi bi-calendar-week"></i> Jadwal & Evaluasi
                </a>
                <a href="{{ route('pokja.laporan') }}" class="{{ request()->routeIs('pokja.laporan*') ? 'active' : '' }}">
                    <i class="bi bi-file-earmark-check"></i> Lembar Kendali
                </a>
            @elseif (auth()->user()?->isAdmin())
                <a href="{{ route('peta.index') }}" class="{{ request()->routeIs('peta.*') ? 'active' : '' }}">
                    <i class="bi bi-map"></i> Peta Kegiatan
                </a>
                <a href="{{ route('pokja.dasbor') }}" class="{{ request()->routeIs('pokja.dasbor', 'pokja.riwayat') ? 'active' : '' }}">
                    <i class="bi bi-speedometer2"></i> Dasbor Pokja
                </a>
                <div class="nav-label">Monitoring Pokja</div>
                <a href="{{ route('pokja.index') }}" class="{{ request()->routeIs('pokja.index', 'pokja.show') ? 'active' : '' }}">
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
                    <i class="bi bi-graph-up"></i> Dashboard
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
            @endif
        </nav>
    </aside>
    <div class="sidebar-backdrop" id="sidebarBackdrop"></div>

    <!-- ===== Topbar ===== -->
    <header class="topbar">
        <button class="btn btn-outline-secondary btn-sm sidebar-toggle me-2" id="sidebarToggle" style="border-radius: var(--radius-input);">
            <i class="bi bi-list"></i>
        </button>
        <div class="d-none d-md-block">
            <span class="fw-semibold text-secondary" style="font-size: 13.5px; text-transform: uppercase; letter-spacing: 0.5px;">@yield('title', 'Dashboard')</span>
        </div>
        <div class="ms-auto d-flex align-items-center gap-2">
            <span class="text-secondary small fw-semibold d-none d-md-inline me-1" style="font-size: 13px;">
                <i class="bi bi-calendar3 me-1 text-muted"></i> TA {{ request('tahun', date('Y')) }}
            </span>
            @auth
                @include('layouts._notifications')
            @endauth
            <div class="dropdown">
                <button class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-2 bg-white text-dark" data-bs-toggle="dropdown" style="border-radius: var(--radius-input); border-color: var(--border-panel); font-size: 13.5px;">
                    <i class="bi bi-person-circle fs-6 text-secondary"></i>
                    <span class="d-none d-md-inline fw-semibold">{{ auth()->user()->name ?? 'Guest' }}</span>
                    <i class="bi bi-chevron-down small text-muted"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end" style="border-radius: var(--radius-krisp); border-color: var(--border-panel); box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); font-size: 13px;">
                    <li><h6 class="dropdown-header text-muted" style="font-size: 12px;">{{ auth()->user()->email ?? '' }}</h6></li>
                    <li>
                        <span class="dropdown-item-text small">
                            @if (auth()->user()?->isAdmin())
                                <span class="badge" style="background: #0f172a; color: #fff; border-radius: 2px; font-weight: 500; font-size: 12px;">Administrator</span>
                            @elseif (auth()->user()?->isPokja())
                                <span class="badge" style="background: #1d4ed8; color: #fff; border-radius: 2px; font-weight: 500; font-size: 12px;">Pokja {{ auth()->user()->pokja?->nama_pokja ?? auth()->user()->pokja_id }}</span>
                            @endif
                        </span>
                    </li>
                    <li><hr class="dropdown-divider" style="border-color: var(--border-divider);"></li>
                    <li>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="dropdown-item text-danger d-flex align-items-center gap-2" style="font-size: 13px;">
                                <i class="bi bi-box-arrow-right"></i>Keluar
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </header>

    <!-- ===== Content ===== -->
    <main class="main-content">
        @hasSection('breadcrumb')
            <div class="workflow-breadcrumb mb-3">
                @yield('breadcrumb')
            </div>
        @endif

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

    <!-- ===== Modal Konfirmasi Destruktif (Swiss Government Crisp) ===== -->
    <div class="modal fade modal-konfirmasi" id="modal-konfirmasi" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered modal-sm" style="max-width: 400px;">
            <div class="modal-content">
                <div class="modal-header py-2 bg-light">
                    <h6 class="modal-title mb-0 d-flex align-items-center gap-2 fw-semibold" id="konfirmasi-judul">
                        <i class="bi bi-exclamation-triangle-fill text-warning" id="konfirmasi-ikon"></i>
                        <span id="konfirmasi-judul-teks">Konfirmasi</span>
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Batal"></button>
                </div>
                <div class="modal-body py-3">
                    <p class="mb-0 text-secondary" id="konfirmasi-pesan">Apakah Anda yakin ingin melanjutkan tindakan ini?</p>
                </div>
                <div class="modal-footer py-2 bg-light d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-sm btn-danger" id="konfirmasi-btn-submit">Lanjutkan</button>
                </div>
            </div>
        </div>
    </div>

    <!-- ===== Toast Container (Swiss Government Crisp) ===== -->
    <div class="toast-container position-fixed bottom-0 end-0 p-3" id="simonToastContainer" style="z-index: 1090;"></div>

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

        // Global Confirmation Modal Handler
        let activeConfirmForm = null;
        const modalKonfirmasiEl = document.getElementById('modal-konfirmasi');
        const modalKonfirmasi = modalKonfirmasiEl ? new bootstrap.Modal(modalKonfirmasiEl) : null;
        const confirmBtnSubmit = document.getElementById('konfirmasi-btn-submit');
        const confirmTitleText = document.getElementById('konfirmasi-judul-teks');
        const confirmIcon = document.getElementById('konfirmasi-ikon');
        const confirmMsg = document.getElementById('konfirmasi-pesan');

        document.addEventListener('click', function (e) {
            const trigger = e.target.closest('[data-confirm-modal]');
            if (!trigger) return;
            e.preventDefault();

            const title = trigger.getAttribute('data-confirm-title') || 'Konfirmasi Tindakan';
            const message = trigger.getAttribute('data-confirm-message') || 'Apakah Anda yakin ingin melanjutkan tindakan ini?';
            const btnText = trigger.getAttribute('data-confirm-btn-text') || 'Lanjutkan';
            const btnClass = trigger.getAttribute('data-confirm-btn-class') || 'btn-danger';
            const iconClass = trigger.getAttribute('data-confirm-icon') || 'bi-exclamation-triangle-fill text-warning';

            if (confirmTitleText) confirmTitleText.textContent = title;
            if (confirmMsg) confirmMsg.textContent = message;
            if (confirmBtnSubmit) {
                confirmBtnSubmit.textContent = btnText;
                confirmBtnSubmit.className = 'btn btn-sm ' + btnClass;
            }
            if (confirmIcon) confirmIcon.className = 'bi ' + iconClass;

            const formId = trigger.getAttribute('data-confirm-form');
            if (formId) {
                activeConfirmForm = document.getElementById(formId);
            } else if (trigger.closest('form')) {
                activeConfirmForm = trigger.closest('form');
            } else {
                activeConfirmForm = null;
            }

            modalKonfirmasi?.show();
        });

        confirmBtnSubmit?.addEventListener('click', function () {
            if (activeConfirmForm) {
                modalKonfirmasi?.hide();
                activeConfirmForm.submit();
            }
        });
    </script>
    @stack('scripts')
</body>
</html>
