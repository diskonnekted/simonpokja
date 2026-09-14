<?php

namespace App\Services;

use App\Models\User;
use App\Models\Notifikasi;
use App\Models\PushSubscription;
use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    protected ?WebPush $webPush = null;

    public function __construct()
    {
        $this->ensureOpenSslConfig();
    }

    /**
     * Memastikan openssl.cnf terdeteksi di Windows CLI / embedded PHP
     */
    protected function ensureOpenSslConfig(): void
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' && !getenv('OPENSSL_CONF')) {
            $candidates = [
                base_path('.tools/php/extras/ssl/openssl.cnf'),
                'C:/xampp/php/extras/ssl/openssl.cnf',
            ];
            foreach ($candidates as $cand) {
                if (file_exists($cand)) {
                    putenv("OPENSSL_CONF={$cand}");
                    break;
                }
            }
        }
    }

    /**
     * Inisialisasi WebPush Client dengan VAPID credentials
     */
    protected function getWebPush(): ?WebPush
    {
        if ($this->webPush) {
            return $this->webPush;
        }

        $publicKey = config('webpush.vapid.public_key') ?: env('VAPID_PUBLIC_KEY');
        $privateKey = config('webpush.vapid.private_key') ?: env('VAPID_PRIVATE_KEY');
        $subject = config('webpush.vapid.subject') ?: env('VAPID_SUBJECT', 'mailto:admin@banjarnegara.go.id');

        if (!$publicKey || !$privateKey) {
            return null;
        }

        try {
            $auth = [
                'VAPID' => [
                    'subject' => $subject,
                    'publicKey' => $publicKey,
                    'privateKey' => $privateKey,
                ],
            ];

            $options = [
                'TTL' => config('webpush.options.TTL', 86400),
                'urgency' => config('webpush.options.urgency', 'high'),
            ];

            $this->webPush = new WebPush($auth, $options);
            return $this->webPush;
        } catch (\Throwable $e) {
            Log::warning('Gagal inisialisasi WebPush: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Kirim notifikasi ke User tertentu (Simpan ke DB + Tembak Web Push)
     */
    public function kirimKeUser(
        int $userId,
        string $judul,
        string $pesan,
        string $url = '/',
        string $kategori = 'sistem',
        string $tingkat = 'info'
    ): ?Notifikasi {
        $user = User::find($userId);
        if (!$user) {
            return null;
        }

        // 1. Simpan ke database (In-App notification record)
        $notif = Notifikasi::create([
            'user_id' => $user->id,
            'judul' => $judul,
            'pesan' => $pesan,
            'url_tujuan' => $url,
            'kategori' => $kategori,
            'tingkat' => $tingkat,
        ]);

        // 2. Tembak ke seluruh browser aktif user via Web Push
        $this->kirimWebPushKeUser($user, [
            'id' => $notif->id,
            'title' => $judul,
            'body' => $pesan,
            'url' => $url,
            'kategori' => $kategori,
            'tingkat' => $tingkat,
            'icon' => '/favicon.ico',
            'badge' => '/favicon.ico',
        ]);

        return $notif;
    }

    /**
     * Kirim notifikasi ke seluruh anggota Pokja tertentu
     */
    public function kirimKePokja(
        int $pokjaId,
        string $judul,
        string $pesan,
        string $url = '/',
        string $kategori = 'sistem',
        string $tingkat = 'info'
    ): array {
        $users = User::where('pokja_id', $pokjaId)->get();
        $notifs = [];

        foreach ($users as $u) {
            $notifs[] = $this->kirimKeUser($u->id, $judul, $pesan, $url, $kategori, $tingkat);
        }

        return $notifs;
    }

    /**
     * Kirim notifikasi ke seluruh administrator LPSE
     */
    public function kirimKeAdmin(
        string $judul,
        string $pesan,
        string $url = '/',
        string $kategori = 'sistem',
        string $tingkat = 'info'
    ): array {
        $admins = User::where('role', 'admin')->get();
        $notifs = [];

        foreach ($admins as $adm) {
            $notifs[] = $this->kirimKeUser($adm->id, $judul, $pesan, $url, $kategori, $tingkat);
        }

        return $notifs;
    }

    /**
     * Mengirim Web Push ke seluruh langganan browser milik user
     */
    protected function kirimWebPushKeUser(User $user, array $payload): void
    {
        $subscriptions = $user->pushSubscriptions;
        if ($subscriptions->isEmpty()) {
            return;
        }

        $webPush = $this->getWebPush();
        if (!$webPush) {
            return;
        }

        $payloadJson = json_encode($payload);

        foreach ($subscriptions as $sub) {
            try {
                $subObject = Subscription::create([
                    'endpoint' => $sub->endpoint,
                    'publicKey' => $sub->public_key,
                    'authToken' => $sub->auth_token,
                    'contentEncoding' => $sub->content_encoding ?: 'aesgcm',
                ]);

                $webPush->queueNotification($subObject, $payloadJson);
            } catch (\Throwable $e) {
                Log::debug("Format subscription invalid untuk sub ID {$sub->id}: " . $e->getMessage());
            }
        }

        // Eksekusi pengiriman antrean Web Push
        try {
            foreach ($webPush->flush() as $report) {
                $endpoint = $report->getRequest()->getUri()->__toString();
                if (!$report->isSuccess()) {
                    Log::info("Web push gagal kirim ke {$endpoint}: " . $report->getReason());
                    // Hapus subscription jika endpoint sudah kedaluwarsa / 404 / 410 Gone
                    if ($report->isSubscriptionExpired()) {
                        PushSubscription::where('endpoint', $endpoint)->delete();
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Error saat flush WebPush: ' . $e->getMessage());
        }
    }
}
