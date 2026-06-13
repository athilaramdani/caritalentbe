<?php

namespace App\Observers;

use App\Models\Notification;
use App\Models\User;
use App\Services\FcmService;
use Illuminate\Support\Facades\Log;

class NotificationObserver
{
    protected FcmService $fcm;

    public function __construct(FcmService $fcm)
    {
        $this->fcm = $fcm;
    }

    /**
     * Dipanggil otomatis setiap kali Notification::create() dipanggil di manapun.
     * Akan langsung mengirim push notification ke HP user yang bersangkutan.
     */
    public function created(Notification $notification): void
    {
        // Ambil user penerima dan cek apakah punya FCM token
        $user = User::find($notification->user_id);

        if (!$user || empty($user->fcm_token)) {
            Log::info('[FCM Observer] User tidak punya FCM token, skip push.', [
                'user_id' => $notification->user_id,
            ]);
            return;
        }

        // Kirim push notification ke HP user
        $success = $this->fcm->sendToToken(
            fcmToken: $user->fcm_token,
            title:    $notification->title,
            body:     $notification->body,
            data:     [
                'notification_id'  => (string) $notification->id,
                'type'             => $notification->type ?? '',
                'action'           => $notification->action ?? '',
                'reference_type'   => $notification->reference_type ?? '',
                'reference_id'     => (string) ($notification->reference_id ?? ''),
            ],
        );

        if ($success) {
            Log::info('[FCM Observer] Push notification terkirim.', [
                'user_id' => $notification->user_id,
                'title'   => $notification->title,
            ]);
        }
    }
}
