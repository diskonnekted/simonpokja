<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifikasis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('judul', 150);
            $table->text('pesan');
            $table->string('url_tujuan', 255)->default('/');
            $table->enum('kategori', ['pesan', 'anomali', 'sanggahan', 'progres', 'deadline', 'sistem'])->default('sistem');
            $table->enum('tingkat', ['info', 'warning', 'critical'])->default('info');
            $table->timestamp('dibaca_pada')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'dibaca_pada']);
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifikasis');
    }
};
