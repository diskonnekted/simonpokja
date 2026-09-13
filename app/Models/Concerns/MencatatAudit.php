<?php

namespace App\Models\Concerns;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

/**
 * Mencatat setiap perubahan model (create/update/delete) ke tabel audit_logs.
 *
 * Hanya aktif untuk request web — aksi console (seeder, migrasi, tinker)
 * diabaikan agar log tidak dibanjiri data dummy/maintenance.
 *
 * JANGAN pasang trait ini pada model AuditLog sendiri (akan rekursi).
 */
trait MencatatAudit
{
    public static function bootMencatatAudit(): void
    {
        if (app()->runningInConsole()) {
            return;
        }

        static::created(fn ($model) => static::tulisAudit($model, 'create', null, $model->getAttributes()));
        static::updated(fn ($model) => static::tulisAudit($model, 'update', $model->getOriginal(), $model->getChanges()));
        static::deleted(fn ($model) => static::tulisAudit($model, 'delete', $model->getOriginal(), null));
    }

    protected static function tulisAudit($model, string $aksi, ?array $dataLama, ?array $dataBaru): void
    {
        try {
            // Atribut tersembunyi (password, remember_token, dsb) tidak boleh tercatat
            $tersembunyi = $model->getHidden();

            AuditLog::create([
                'user_id'    => Auth::id(),
                'aksi'       => $aksi,
                'tabel'      => $model->getTable(),
                'record_id'  => $model->getKey(),
                'data_lama'  => $dataLama ? collect($dataLama)->except($tersembunyi)->all() : null,
                'data_baru'  => $dataBaru ? collect($dataBaru)->except($tersembunyi)->all() : null,
                'ip_address' => Request::ip(),
                'user_agent' => substr((string) Request::userAgent(), 0, 255),
            ]);
        } catch (\Throwable $e) {
            // Kegagalan pencatatan tidak boleh mengganggu aksi utama pengguna
            report($e);
        }
    }
}
