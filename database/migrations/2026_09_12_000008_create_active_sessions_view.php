<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            CREATE OR REPLACE VIEW v_active_sessions AS
            SELECT 
                s.id AS session_id,
                CASE WHEN s.user_id IS NOT NULL THEN 'Pengguna Terdaftar' ELSE 'Tamu / Anonim' END AS tipe_sesi,
                s.user_id,
                u.name AS user_nama,
                u.email AS user_email,
                u.role AS user_role,
                p.nama AS user_pokja,
                s.ip_address,
                s.user_agent,
                FROM_UNIXTIME(s.last_activity) AS aktivitas_terakhir,
                TIMESTAMPDIFF(MINUTE, FROM_UNIXTIME(s.last_activity), NOW()) AS idle_menit,
                CASE 
                    WHEN s.last_activity >= UNIX_TIMESTAMP(NOW() - INTERVAL 120 MINUTE) THEN 'Aktif'
                    ELSE 'Kedaluwarsa'
                END AS status_sesi
            FROM sessions s
            LEFT JOIN users u ON s.user_id = u.id
            LEFT JOIN pokjas p ON u.pokja_id = p.id
            ORDER BY s.last_activity DESC;
        ");
    }

    public function down(): void
    {
        DB::statement("DROP VIEW IF EXISTS v_active_sessions;");
    }
};
