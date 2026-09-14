@extends('layouts.app')

@section('title', 'Audit Log & Sesi Aktif')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
    <div>
        <h4 class="fw-bold mb-1">Audit Log &amp; Monitoring Sesi</h4>
        <p class="text-secondary mb-0 small">Jejak aktivitas perubahan data dan pemantauan sesi pengguna aktif di sistem</p>
    </div>
    @if ($guestSessionCount > 0)
        <form method="POST" action="{{ route('audit.bersihkanSesi') }}" onsubmit="return confirm('Bersihkan {{ $guestSessionCount }} sesi tamu/usang dari basis data?')">
            @csrf
            <button type="submit" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-trash3 me-1"></i>Bersihkan Sesi Usang ({{ $guestSessionCount }})
            </button>
        </form>
    @endif
</div>

{{-- Tab Navigasi --}}
<ul class="nav nav-tabs mb-3" id="auditTab" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active fw-semibold" id="tab-log-btn" data-bs-toggle="tab" data-bs-target="#tab-log" type="button" role="tab" aria-selected="true">
            <i class="bi bi-clock-history me-1"></i>Aktivitas Perubahan Data ({{ $logs->total() }})
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link fw-semibold" id="tab-sesi-btn" data-bs-toggle="tab" data-bs-target="#tab-sesi" type="button" role="tab" aria-selected="false">
            <i class="bi bi-person-badge me-1"></i>Sesi Pengguna Aktif ({{ $activeSessions->count() }})
        </button>
    </li>
</ul>

<div class="tab-content" id="auditTabContent">
    {{-- TAB 1: LOG AKTIVITAS --}}
    <div class="tab-pane fade show active" id="tab-log" role="tabpanel" aria-labelledby="tab-log-btn">
        <div class="card mb-3">
            <div class="card-body py-2">
                <form method="GET" class="row g-2 align-items-center">
                    <div class="col-12 col-md-5">
                        <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="Cari aksi atau tabel...">
                    </div>
                    <div class="col-6 col-md-4">
                        <select name="tabel" class="form-select form-select-sm">
                            <option value="">Semua Tabel</option>
                            @foreach ($tabels as $t)
                                <option value="{{ $t }}" {{ request('tabel') == $t ? 'selected' : '' }}>{{ $t }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-auto">
                        <button class="btn btn-primary btn-sm"><i class="bi bi-search me-1"></i>Filter</button>
                        @if (request('q') || request('tabel'))
                            <a href="{{ route('audit.index') }}" class="btn btn-outline-secondary btn-sm ms-1">Reset</a>
                        @endif
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light"><tr>
                            <th class="ps-3">Waktu</th>
                            <th>Pengguna</th>
                            <th>Aksi</th>
                            <th>Tabel</th>
                            <th>Record</th>
                            <th class="pe-3">IP Address</th>
                        </tr></thead>
                        <tbody>
                            @forelse ($logs as $log)
                                <tr>
                                    <td class="ps-3 text-nowrap">
                                        <small class="font-monospace">{{ format_tanggal_id($log->created_at, true) }}</small>
                                    </td>
                                    <td>
                                        <div class="fw-semibold small">{{ $log->user->name ?? 'Sistem' }}</div>
                                        @if ($log->user)
                                            <small class="text-secondary" style="font-size: 0.72rem;">{{ $log->user->email }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge {{ str_contains($log->aksi, 'delete') ? 'text-bg-danger' : (str_contains($log->aksi, 'create') ? 'text-bg-success' : 'text-bg-warning text-dark') }}">
                                            {{ strtoupper($log->aksi) }}
                                        </span>
                                    </td>
                                    <td><small class="text-secondary font-monospace">{{ $log->tabel }}</small></td>
                                    <td><small class="font-monospace">#{{ $log->record_id ?? '-' }}</small></td>
                                    <td class="pe-3"><small class="text-secondary font-monospace">{{ $log->ip_address ?? '-' }}</small></td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center text-secondary py-5">
                                    <i class="bi bi-clock-history fs-1 d-block mb-2 text-muted"></i>
                                    Belum ada aktivitas tercatat
                                </td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if ($logs->hasPages())
                <div class="card-footer bg-white py-2">
                    {{ $logs->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </div>
    </div>

    {{-- TAB 2: SESI PENGGUNA AKTIF --}}
    <div class="tab-pane fade" id="tab-sesi" role="tabpanel" aria-labelledby="tab-sesi-btn">
        <div class="card">
            <div class="card-header bg-white py-2">
                <span class="fw-semibold small"><i class="bi bi-people text-success me-1"></i>Daftar Pengguna Online ({{ $activeSessions->count() }})</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light"><tr>
                            <th class="ps-3">Pengguna</th>
                            <th>IP Address</th>
                            <th>Aktivitas Terakhir</th>
                            <th>Perangkat</th>
                            <th class="pe-3 text-end">Aksi</th>
                        </tr></thead>
                        <tbody>
                            @forelse ($activeSessions as $sesi)
                                @php
                                    $isCurrent = ($sesi->session_id === $currentSessionId);
                                    $ua = $sesi->user_agent ?? '';
                                    $browser = 'Browser Web';
                                    if (str_contains($ua, 'Edg/')) $browser = 'MS Edge';
                                    elseif (str_contains($ua, 'Chrome/')) $browser = 'Chrome';
                                    elseif (str_contains($ua, 'Firefox/')) $browser = 'Firefox';
                                    elseif (str_contains($ua, 'Safari/') && !str_contains($ua, 'Chrome/')) $browser = 'Safari';

                                    $os = 'Desktop';
                                    if (str_contains($ua, 'Windows NT 10.0')) $os = 'Windows 10/11';
                                    elseif (str_contains($ua, 'Windows')) $os = 'Windows';
                                    elseif (str_contains($ua, 'Android')) $os = 'Android';
                                    elseif (str_contains($ua, 'iPhone') || str_contains($ua, 'iPad')) $os = 'iOS';
                                    elseif (str_contains($ua, 'Macintosh') || str_contains($ua, 'Mac OS')) $os = 'macOS';
                                    elseif (str_contains($ua, 'Linux')) $os = 'Linux';
                                @endphp
                                <tr class="{{ $isCurrent ? 'table-light' : '' }}">
                                    <td class="ps-3">
                                        <div class="d-flex align-items-center gap-1">
                                            <span class="d-inline-block bg-success rounded-circle" style="width: 7px; height: 7px;" title="Online"></span>
                                            <span class="fw-semibold text-dark">{{ $sesi->user_nama ?? 'Tamu' }}</span>
                                            @if ($isCurrent)
                                                <span class="badge bg-success-subtle text-success border border-success-subtle ms-1" style="font-size: 0.65rem;">Sesi Anda</span>
                                            @endif
                                        </div>
                                        <div class="text-secondary small">{{ $sesi->user_email }}</div>
                                        <div class="mt-1 d-flex gap-1 align-items-center">
                                            <span class="badge text-bg-{{ $sesi->user_role === 'admin' ? 'primary' : 'info' }}" style="font-size: 0.65rem;">
                                                {{ strtoupper($sesi->user_role) }}
                                            </span>
                                            @if ($sesi->user_pokja)
                                                <span class="badge bg-light text-secondary border" style="font-size: 0.65rem;">{{ $sesi->user_pokja }}</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <span class="font-monospace small text-dark">{{ $sesi->ip_address ?? '-' }}</span>
                                    </td>
                                    <td>
                                        <div class="small">{{ $sesi->aktivitas_terakhir }}</div>
                                        <small class="text-secondary">
                                            @if ($sesi->idle_menit == 0)
                                                <span class="text-success">&bull; Baru saja aktif</span>
                                            @else
                                                &bull; {{ $sesi->idle_menit }} menit lalu
                                            @endif
                                        </small>
                                    </td>
                                    <td>
                                        <div class="small text-dark text-truncate" style="max-width: 220px;" title="{{ $sesi->user_agent }}">
                                            <i class="bi bi-laptop me-1 text-secondary"></i>{{ $browser }} <span class="text-muted">({{ $os }})</span>
                                        </div>
                                    </td>
                                    <td class="pe-3 text-end">
                                        @if (!$isCurrent)
                                            <form method="POST" action="{{ route('audit.putusSesi', $sesi->session_id) }}" onsubmit="return confirm('Yakin ingin memutuskan sesi pengguna {{ $sesi->user_nama }}?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-2" style="font-size: 0.75rem;" title="Putus sesi">
                                                    <i class="bi bi-box-arrow-right me-1"></i>Putus Sesi
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-muted small">&mdash;</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-secondary py-4">
                                    <i class="bi bi-shield-check fs-2 d-block mb-2 text-muted"></i>
                                    Tidak ada pengguna lain yang sedang online.
                                </td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
