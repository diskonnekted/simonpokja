<?php

use App\Http\Controllers\Api\MasterController;
use App\Http\Controllers\Api\MonitoringController;
use App\Http\Controllers\Api\PaketController;
use App\Http\Controllers\Api\StatistikController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Integrasi — konsumen: SIBIJAK
|--------------------------------------------------------------------------
| Prefiks URL : /api/v1
| Autentikasi : header "Authorization: Bearer <token>" (atau ?token=)
| Token       : diatur lewat env SIBIJAK_API_TOKEN
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->middleware('api.token')->group(function () {
    // Health / uji koneksi
    Route::get('ping', [StatistikController::class, 'ping']);

    // ============ Master data ============
    Route::get('opd', [MasterController::class, 'opds']);
    Route::get('opd/{id}', [MasterController::class, 'opdDetail']);
    Route::get('pokja', [MasterController::class, 'pokjas']);
    Route::get('pokja/{id}', [MasterController::class, 'pokjaDetail']);
    Route::get('penyedia', [MasterController::class, 'penyedias']);
    Route::get('penyedia/{id}', [MasterController::class, 'penyediaDetail']);

    // ============ Paket pengadaan ============
    Route::get('paket', [PaketController::class, 'index']);
    Route::get('paket/{id}', [PaketController::class, 'show']);
    Route::get('paket/{id}/tahapan', [PaketController::class, 'tahapan']);
    Route::get('paket/{id}/evaluasi', [PaketController::class, 'evaluasi']);
    Route::get('paket/{id}/sanggahan', [PaketController::class, 'sanggahan']);
    Route::get('paket/{id}/progres', [PaketController::class, 'progres']);
    Route::get('paket/{id}/perubahan-jadwal', [PaketController::class, 'perubahanJadwal']);
    Route::get('paket/{id}/pesan', [PaketController::class, 'pesan']);
    Route::get('paket/{id}/alert', [PaketController::class, 'alert']);

    // ============ Data monitoring (global) ============
    Route::get('tahapan', [MonitoringController::class, 'tahapan']);
    Route::get('evaluasi', [MonitoringController::class, 'evaluasi']);
    Route::get('sanggahan', [MonitoringController::class, 'sanggahan']);
    Route::get('progres', [MonitoringController::class, 'progres']);
    Route::get('alert', [MonitoringController::class, 'alert']);
    Route::get('perubahan-jadwal', [MonitoringController::class, 'perubahanJadwal']);
    Route::get('pesan', [MonitoringController::class, 'pesan']);
    Route::get('audit-checklist', [MonitoringController::class, 'auditChecklist']);
    Route::get('audit-log', [MonitoringController::class, 'auditLog']);

    // ============ Agregat ============
    Route::get('statistik', [StatistikController::class, 'statistik']);
    Route::get('kinerja-pokja', [StatistikController::class, 'kinerjaPokja']);
});