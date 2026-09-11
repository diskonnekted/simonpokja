<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Perangkat Daerah (OPD) Kabupaten Banjarnegara
        Schema::create('opds', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 30)->unique();
            $table->string('nama');
            $table->string('singkatan', 50)->nullable();
            $table->string('kepala')->nullable();
            $table->string('nip_kepala', 30)->nullable();
            $table->string('alamat')->nullable();
            $table->string('telepon', 20)->nullable();
            $table->string('email')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Penyedia / Vendor
        Schema::create('penyedias', function (Blueprint $table) {
            $table->id();
            $table->string('npwp', 30)->unique()->nullable();
            $table->string('nib', 50)->nullable();
            $table->string('nama');
            $table->enum('jenis_usaha', ['kecil', 'non_kecil', 'perseorangan', 'koperasi'])->default('kecil');
            $table->enum('kualifikasi', ['kecil', 'menengah', 'besar'])->default('kecil');
            $table->string('alamat')->nullable();
            $table->string('direktur')->nullable();
            $table->string('telepon', 20)->nullable();
            $table->string('email')->nullable();
            $table->boolean('aktif')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        // Paket Pengadaan
        Schema::create('paket_pengadaans', function (Blueprint $table) {
            $table->id();
            $table->string('kode_paket', 50)->unique();
            $table->string('nama_paket');
            $table->foreignId('opd_id')->constrained('opds')->cascadeOnDelete();
            $table->foreignId('penyedia_id')->nullable()->constrained('penyedias')->nullOnDelete();
            $table->enum('jenis', ['barang', 'jasa_konsultansi', 'konstruksi', 'jasa_lainnya']);
            $table->enum('metode', ['tender', 'seleksi', 'epurchasing', 'penunjukan_langsung', 'pengadaan_langsung', 'swakelola']);
            $table->enum('sumber_dana', ['apbd', 'apbn', 'blm', 'dak', 'did', 'lainnya'])->default('apbd');
            $table->bigInteger('pagu')->default(0);
            $table->bigInteger('hps')->default(0);
            $table->bigInteger('nilai_kontrak')->default(0)->nullable();
            $table->year('tahun_anggaran');
            $table->enum('status', ['draft', 'persiapan', 'pemilihan', 'kontrak', 'pelaksanaan', 'selesai', 'batal'])->default('draft');
            $table->integer('progress')->default(0); // 0-100
            $table->date('tanggal_mulai')->nullable();
            $table->date('tanggal_selesai')->nullable();
            $table->string('lokasi')->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tahun_anggaran', 'status']);
            $table->index('opd_id');
        });

        // Tahapan paket (timeline pengadaan)
        Schema::create('tahapans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paket_id')->constrained('paket_pengadaans')->cascadeOnDelete();
            $table->string('nama_tahap');
            $table->integer('urutan')->default(0);
            $table->enum('status', ['belum', 'proses', 'selesai'])->default('belum');
            $table->date('tanggal_rencana')->nullable();
            $table->date('tanggal_aktual')->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });

        // Evaluasi (kualifikasi/teknis/harga)
        Schema::create('evaluasis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paket_id')->constrained('paket_pengadaans')->cascadeOnDelete();
            $table->foreignId('penyedia_id')->constrained('penyedias')->cascadeOnDelete();
            $table->enum('jenis', ['kualifikasi', 'teknis', 'harga']);
            $table->decimal('skor', 8, 2)->default(0);
            $table->enum('hasil', ['lolos', 'gugur', 'menunggu'])->default('menunggu');
            $table->text('catatan')->nullable();
            $table->date('tanggal')->nullable();
            $table->timestamps();
        });

        // Audit log
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('aksi', 50);
            $table->string('tabel', 100);
            $table->unsignedBigInteger('record_id')->nullable();
            $table->json('data_lama')->nullable();
            $table->json('data_baru')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('evaluasis');
        Schema::dropIfExists('tahapans');
        Schema::dropIfExists('paket_pengadaans');
        Schema::dropIfExists('penyedias');
        Schema::dropIfExists('opds');
    }
};
