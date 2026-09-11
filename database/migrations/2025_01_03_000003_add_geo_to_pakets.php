<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('paket_pengadaans', function (Blueprint $table) {
            $table->decimal('latitude', 10, 7)->nullable()->after('lokasi');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            $table->string('desa', 100)->nullable()->after('longitude');
            $table->string('kecamatan', 100)->nullable()->after('desa');
            $table->index(['kecamatan', 'desa']);
        });
    }

    public function down(): void
    {
        Schema::table('paket_pengadaans', function (Blueprint $table) {
            $table->dropIndex(['kecamatan', 'desa']);
            $table->dropColumn(['latitude', 'longitude', 'desa', 'kecamatan']);
        });
    }
};
