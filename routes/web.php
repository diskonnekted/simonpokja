<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PaketController;
use App\Http\Controllers\OpdController;
use App\Http\Controllers\PenyediaController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\PokjaController;
use App\Http\Controllers\PemantauanController;
use App\Http\Controllers\PetaController;
use App\Http\Controllers\PokjaDashboardController;
use App\Http\Controllers\PesanPaketController;

// ================= AUTH =================
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// ================= DASBOR POKJA (admin boleh meninjau) =================
Route::middleware('auth')->group(function () {
    Route::get('pokja-dasbor', [PokjaDashboardController::class, 'index'])->name('pokja.dasbor');
    Route::get('pokja-dasbor/paket/{paket}/riwayat', [PokjaDashboardController::class, 'riwayat'])->name('pokja.riwayat');
});

// ================= APLIKASI (hanya admin) =================
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // ===== DASBOR PETA KEGIATAN (menu teratas) =====
    Route::get('peta-kegiatan', [PetaController::class, 'index'])->name('peta.index');
    Route::get('api/peta/paket', [PetaController::class, 'paket'])->name('peta.api.paket');
    Route::get('api/peta/statistik', [PetaController::class, 'statistik'])->name('peta.api.statistik');

    // ===== MONITORING KINERJA POKJA (Fokus Utama) =====
    Route::get('kinerja-pokja', [PokjaController::class, 'index'])->name('pokja.index');
    Route::get('kinerja-pokja/{pokja}', [PokjaController::class, 'show'])->name('pokja.show');
    Route::post('kinerja-pokja/{pokja}/checklist', [PokjaController::class, 'simpanChecklist'])->name('pokja.checklist');
    Route::patch('alert/{alert}', [PokjaController::class, 'updateAlert'])->name('alert.update');

    // ===== KELOLA POKJA (CRUD) =====
    Route::get('kelola-pokja', [PokjaController::class, 'kelola'])->name('pokja.kelola');
    Route::get('kelola-pokja/tambah', [PokjaController::class, 'create'])->name('pokja.create');
    Route::post('kelola-pokja', [PokjaController::class, 'storePokja'])->name('pokja.store');
    Route::get('kelola-pokja/{pokja}/edit', [PokjaController::class, 'edit'])->name('pokja.edit');
    Route::put('kelola-pokja/{pokja}', [PokjaController::class, 'updatePokja'])->name('pokja.update');
    Route::delete('kelola-pokja/{pokja}', [PokjaController::class, 'destroyPokja'])->name('pokja.destroy');

    // ===== PEMANTAUAN PAKET BERMASALAH =====
    Route::get('pemantauan', [PemantauanController::class, 'index'])->name('pemantauan.index');
    Route::get('pemantauan/{paket}', [PemantauanController::class, 'show'])->name('pemantauan.show');
    Route::post('pemantauan/{paket}/perubahan-jadwal', [PemantauanController::class, 'storePerubahan'])->name('pemantauan.perubahan');
    Route::post('pemantauan/{paket}/sanggahan', [PemantauanController::class, 'storeSanggahan'])->name('pemantauan.sanggahan');
    Route::post('sanggahan/{sanggahan}/jawab', [PemantauanController::class, 'jawabSanggahan'])->name('sanggahan.jawab');

    // Paket Pengadaan
    Route::resource('paket', PaketController::class);
    Route::post('paket/{paket}/tahapan', [PaketController::class, 'storeTahapan'])->name('paket.tahapan.store');
    Route::put('tahapan/{tahapan}', [PaketController::class, 'updateTahapan'])->name('tahapan.update');
    Route::post('paket/{paket}/evaluasi', [PaketController::class, 'storeEvaluasi'])->name('paket.evaluasi.store');

    // OPD
    Route::resource('opd', OpdController::class);

    // Penyedia
    Route::resource('penyedia', PenyediaController::class)->parameters(['penyedia' => 'penyedia']);

    // Laporan
    Route::get('laporan', [LaporanController::class, 'index'])->name('laporan.index');
    Route::get('laporan/export', [LaporanController::class, 'export'])->name('laporan.export');

    // Audit Log
    Route::get('audit', [AuditLogController::class, 'index'])->name('audit.index');
});

// ================= SIMPAN PROGRES (khusus user pokja) =================
Route::middleware(['auth', 'role:pokja'])->group(function () {
    Route::post('pokja-dasbor/paket/{paket}/progres', [PokjaDashboardController::class, 'simpanProgres'])->name('pokja.progres');
});

// ================= PERPESANAN PER PEKERJAAN (Kepala LPSE <-> Pokja) =================
Route::middleware(['auth', 'role:admin,pokja'])->group(function () {
    Route::get('paket/{paket}/pesan', [PesanPaketController::class, 'index'])->name('pesan.index');
    Route::post('paket/{paket}/pesan', [PesanPaketController::class, 'store'])->name('pesan.store');
});
