<?php

namespace App\Http\Controllers;

use App\Models\Notifikasi;
use App\Models\PushSubscription;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class NotifikasiController extends Controller
{
    /**
     * Mengambil daftar notifikasi terbaru & jumlah belum dibaca (JSON untuk navbar)
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user() ?: auth()->user();
        if (!$user) {
            return response()->json(['unread_count' => 0, 'notifikasis' => []], 401);
        }

        $notifikasis = $user->notifikasis()
            ->limit(10)
            ->get()
            ->map(fn ($n) => [
                'id' => $n->id,
                'judul' => $n->judul,
                'pesan' => $n->pesan,
                'url' => $n->url_tujuan,
                'kategori' => $n->kategori,
                'tingkat' => $n->tingkat,
                'icon' => $n->icon,
                'color' => $n->color_class,
                'dibaca' => !is_null($n->dibaca_pada),
                'waktu' => $n->created_at->diffForHumans(),
            ]);

        $unreadCount = $user->unreadNotifikasiCount();

        return response()->json([
            'unread_count' => $unreadCount,
            'notifikasis' => $notifikasis,
        ]);
    }

    /**
     * Tandai satu notifikasi telah dibaca
     */
    public function baca(Request $request, Notifikasi $notifikasi): JsonResponse
    {
        $user = $request->user() ?: auth()->user();
        if (!$user || $notifikasi->user_id !== $user->id) {
            abort(403);
        }

        $notifikasi->markAsRead();

        return response()->json([
            'success' => true,
            'unread_count' => $user->unreadNotifikasiCount(),
        ]);
    }

    /**
     * Tandai semua notifikasi milik user sebagai sudah dibaca
     */
    public function bacaSemua(Request $request): JsonResponse
    {
        $user = $request->user() ?: auth()->user();
        if (!$user) {
            return response()->json(['success' => false], 401);
        }

        $user->notifikasis()->unread()->update(['dibaca_pada' => now()]);

        return response()->json([
            'success' => true,
            'unread_count' => 0,
        ]);
    }

    /**
     * Ambil VAPID Public Key untuk frontend subscription
     */
    public function vapidPublicKey(): JsonResponse
    {
        $key = config('webpush.vapid.public_key') ?: env('VAPID_PUBLIC_KEY');
        return response()->json([
            'publicKey' => $key,
        ]);
    }

    /**
     * Mendaftarkan Push Subscription browser milik user
     */
    public function subscribe(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'endpoint' => 'required|string',
            'keys.p256dh' => 'required|string',
            'keys.auth' => 'required|string',
            'content_encoding' => 'nullable|string',
        ]);

        $user = $request->user() ?: auth()->user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        PushSubscription::updateOrCreate(
            [
                'endpoint' => $validated['endpoint'],
            ],
            [
                'user_id' => $user->id,
                'public_key' => $validated['keys']['p256dh'],
                'auth_token' => $validated['keys']['auth'],
                'content_encoding' => $validated['content_encoding'] ?? 'aesgcm',
                'device_name' => substr($request->userAgent() ?? 'Browser', 0, 140),
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Langganan push notifikasi berhasil diaktifkan.',
        ]);
    }

    /**
     * Menghapus Push Subscription jika user menonaktifkan notifikasi
     */
    public function unsubscribe(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'endpoint' => 'required|string',
        ]);

        PushSubscription::where('endpoint', $validated['endpoint'])->delete();

        return response()->json([
            'success' => true,
            'message' => 'Langganan push notifikasi dinonaktifkan.',
        ]);
    }
}
