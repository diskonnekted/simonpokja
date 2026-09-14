<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE `sessions` 
            ADD COLUMN IF NOT EXISTS `waktu_aktif` VARCHAR(30) 
            GENERATED ALWAYS AS (DATE_FORMAT(FROM_UNIXTIME(`last_activity`), '%d-%m-%Y %H:%i:%s')) VIRTUAL AFTER `last_activity`;
        ");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE `sessions` DROP COLUMN IF EXISTS `waktu_aktif`;");
    }
};
