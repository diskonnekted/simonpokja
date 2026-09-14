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
use App\Http\Controllers\NotifikasiController;

// ================= AUTH =================
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::match(['get', 'post'], '/logout', [AuthController::class, 'logout'])->name('logout');

// ================= BERANDA & DASBOR POKJA =================
Route::middleware('auth')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('pokja-dasbor', [PokjaDashboardController::class, 'index'])->name('pokja.dasbor');
    Route::get('pokja-dasbor/paket/{paket}/riwayat', [PokjaDashboardController::class, 'riwayat'])->name('pokja.riwayat');
    Route::get('pokja-sanggahan', [PokjaDashboardController::class, 'sanggahan'])->name('pokja.sanggahan');
    Route::post('pokja-sanggahan/{sanggahan}/jawab', [PokjaDashboardController::class, 'jawabSanggahan'])->name('pokja.sanggahan.jawab');
    Route::get('pokja-jadwal', [PokjaDashboardController::class, 'jadwal'])->name('pokja.jadwal');
    Route::post('pokja-jadwal/{paket}/perubahan', [PokjaDashboardController::class, 'simpanPerubahanJadwal'])->name('pokja.jadwal.perubahan');
    Route::get('pokja-laporan', [PokjaDashboardController::class, 'laporan'])->name('pokja.laporan');
});

// ================= APLIKASI (hanya admin) =================
Route::middleware(['auth', 'role:admin'])->group(function () {

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

    // Audit Log & Monitoring Sesi
    Route::get('audit', [AuditLogController::class, 'index'])->name('audit.index');
    Route::post('audit/bersihkan-sesi', [AuditLogController::class, 'bersihkanSesi'])->name('audit.bersihkanSesi');
    Route::delete('audit/putus-sesi/{sessionId}', [AuditLogController::class, 'putusSesi'])->name('audit.putusSesi');
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

// ================= NOTIFIKASI & WEB PUSH (semua user login) =================
Route::middleware('auth')->group(function () {
    Route::get('notifikasi', [NotifikasiController::class, 'index'])->name('notifikasi.index');
    Route::post('notifikasi/{notifikasi}/baca', [NotifikasiController::class, 'baca'])->name('notifikasi.baca');
    Route::post('notifikasi/baca-semua', [NotifikasiController::class, 'bacaSemua'])->name('notifikasi.bacaSemua');
    Route::get('api/push/vapid-key', [NotifikasiController::class, 'vapidPublicKey'])->name('push.vapidKey');
    Route::post('api/push/subscribe', [NotifikasiController::class, 'subscribe'])->name('push.subscribe');
    Route::post('api/push/unsubscribe', [NotifikasiController::class, 'unsubscribe'])->name('push.unsubscribe');
});

