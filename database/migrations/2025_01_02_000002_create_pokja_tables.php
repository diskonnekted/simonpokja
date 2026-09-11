<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ===== POKJA / PANITIA PENGADAAN =====
        Schema::create('pokjas', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 100);                    // Pokja I - Barang, Pokja II - Jasa & IT, ...
            $table->string('bidang', 150)->nullable();      // Lingkup paket yang ditangani
            $table->string('ketua')->nullable();
            $table->string('nip_ketua', 30)->nullable();
            $table->json('anggota')->nullable();            // daftar anggota panitia
            $table->integer('kapasitas_ideal')->default(5); // ambang rasio paket per anggota
            $table->boolean('aktif')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        // ===== TAMBAHAN KOLOM PADA PAKET =====
        Schema::table('paket_pengadaans', function (Blueprint $table) {
            $table->foreignId('pokja_id')->nullable()->after('opd_id')->constrained('pokjas')->nullOnDelete();
            $table->enum('risiko', ['rendah', 'sedang', 'kritis'])->default('rendah')->after('status');
            $table->boolean('tender_gagal')->default(false)->after('risiko');
            $table->string('tahap_tender', 30)->default('persiapan')->after('tender_gagal');
            // tahap_tender: persiapan | pengumuman | evaluasi | sanggah | penetapan
        });

        // ===== PERUBAHAN / PENGUNDURAN JADWAL (SLA & Delay Audit) =====
        Schema::create('perubahan_jadwals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paket_id')->constrained('paket_pengadaans')->cascadeOnDelete();
            $table->date('tanggal');
            $table->time('jam')->nullable();                 // pola waktu: akhir jam kerja?
            $table->enum('jenis', ['pengunduran', 'reopening', 'penyesuaian'])->default('pengunduran');
            $table->string('tahap_terkait', 100)->nullable(); // tahapan yang diundurkan
            $table->text('alasan')->nullable();
            $table->boolean('ada_berita_acara')->default(false); // BA perubahan jadwal sah?
            $table->timestamps();
        });

        // ===== SANGGAHAN (Ketepatan Waktu Tanggapan) =====
        Schema::create('sanggahans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paket_id')->constrained('paket_pengadaans')->cascadeOnDelete();
            $table->foreignId('penyedia_id')->nullable()->constrained('penyedias')->nullOnDelete();
            $table->date('tanggal_masuk');
            $table->date('tanggal_dijawab')->nullable();
            $table->enum('hasil', ['menunggu', 'diterima', 'ditolak'])->default('menunggu');
            $table->boolean('substantif')->default(false);   // dijawab substantif & tepat waktu?
            $table->text('catatan')->nullable();
            $table->timestamps();
        });

        // ===== ALERT ANOMALI =====
        Schema::create('alert_anomalis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paket_id')->nullable()->constrained('paket_pengadaans')->cascadeOnDelete();
            $table->foreignId('pokja_id')->nullable()->constrained('pokjas')->nullOnDelete();
            $table->string('jenis', 100);                    // delay_berulang, tanpa_ba, jam_akhir_kerja, beban_overload, dst
            $table->enum('tingkat', ['info', 'warning', 'critical'])->default('warning');
            $table->text('deskripsi');
            $table->enum('status', ['aktif', 'ditinjau', 'selesai'])->default('aktif');
            $table->timestamps();
        });

        // ===== CHECKLIST AUDIT RISIKO POKJA (6 Kategori) =====
        Schema::create('audit_checklists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pokja_id')->constrained('pokjas')->cascadeOnDelete();
            $table->foreignId('paket_id')->nullable()->constrained('paket_pengadaans')->nullOnDelete();
            $table->integer('kategori');                     // 1-6 sesuai checklist audit
            $table->string('poin', 255);                     // pertanyaan audit
            $table->enum('jawaban', ['ya', 'tidak', 'na'])->default('na');
            $table->text('catatan')->nullable();
            $table->foreignId('auditor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_checklists');
        Schema::dropIfExists('alert_anomalis');
        Schema::dropIfExists('sanggahans');
        Schema::dropIfExists('perubahan_jadwals');
        Schema::table('paket_pengadaans', function (Blueprint $table) {
            $table->dropConstrainedForeignId('pokja_id');
            $table->dropColumn(['risiko', 'tender_gagal', 'tahap_tender']);
        });
        Schema::dropIfExists('pokjas');
    }
};
