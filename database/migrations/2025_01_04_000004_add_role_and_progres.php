<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin', 'pokja'])->default('pokja')->after('password');
            $table->foreignId('pokja_id')->nullable()->after('role')->constrained('pokjas')->nullOnDelete();
        });

        Schema::create('progres_pekerjaans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paket_id')->constrained('paket_pengadaans')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedTinyInteger('progress');
            $table->string('status', 20);
            $table->text('catatan')->nullable();
            $table->timestamps();
            $table->index(['paket_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('progres_pekerjaans');
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('pokja_id');
            $table->dropColumn('role');
        });
    }
};
