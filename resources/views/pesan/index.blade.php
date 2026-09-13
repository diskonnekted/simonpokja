@extends('layouts.app')

@section('title', 'Diskusi Paket')

@push('styles')
<style>
    /* Panel diskusi versi halaman penuh — lebih lega daripada versi modal */
    .chat-halaman .chat-isi { max-height: 58vh; min-height: 46vh; }
</style>
@endpush

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-start mb-4 gap-2">
    <div class="d-flex gap-2">
        <a href="{{ route('paket.show', $paket) }}" class="btn btn-light btn-sm mt-1"><i class="bi bi-arrow-left"></i></a>
        <div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <h4 class="fw-bold mb-0">Diskusi Paket</h4>
                <span class="badge text-bg-{{ status_badge_class($paket->status) }}">{{ $paket->status_label }}</span>
            </div>
            <p class="text-secondary mb-0 small">
                {{ $paket->nama_paket }} &bull; {{ $paket->kode_paket }} &bull; {{ $paket->opd->nama ?? '-' }}
            </p>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm chat-halaman">
    <div class="card-header bg-white py-2 d-flex align-items-center gap-2">
        <i class="bi bi-chat-square-text text-primary"></i>
        <span class="fw-semibold small">
            Diskusi dengan {{ auth()->user()->isAdmin() ? ($paket->pokja->nama ?? 'Pokja') : 'Kepala LPSE' }}
        </span>
        <span class="text-muted small ms-auto"><i class="bi bi-arrow-repeat me-1"></i>terbarui otomatis</span>
    </div>
    @include('pesan._chat', ['paket' => $paket])
</div>
@endsection

@include('pesan._chat-script')
