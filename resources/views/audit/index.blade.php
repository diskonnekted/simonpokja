@extends('layouts.app')

@section('title', 'Audit Log')

@section('content')
<div class="mb-4">
    <h4 class="fw-bold mb-1">Audit Log</h4>
    <p class="text-secondary mb-0 small">Jejak aktivitas dan perubahan data pada sistem</p>
</div>

<div class="card mb-3">
    <div class="card-body py-3">
        <form method="GET" class="row g-2">
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
                <button class="btn btn-primary btn-sm"><i class="bi bi-search"></i></button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead><tr>
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
                                <small>{{ format_tanggal_id($log->created_at, true) }}</small>
                            </td>
                            <td><small>{{ $log->user->name ?? 'Sistem' }}</small></td>
                            <td>
                                <span class="badge {{ str_contains($log->aksi, 'delete') ? 'text-bg-danger' : (str_contains($log->aksi, 'create') ? 'text-bg-success' : 'text-bg-warning text-dark') }}">
                                    {{ strtoupper($log->aksi) }}
                                </span>
                            </td>
                            <td><small class="text-secondary">{{ $log->tabel }}</small></td>
                            <td><small>#{{ $log->record_id ?? '-' }}</small></td>
                            <td class="pe-3"><small class="text-secondary">{{ $log->ip_address ?? '-' }}</small></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-secondary py-5">
                            <i class="bi bi-clock-history fs-1 d-block mb-2"></i>
                            Belum ada aktivitas tercatat
                        </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer bg-white">
        {{ $logs->links('pagination::bootstrap-5') }}
    </div>
</div>
@endsection
