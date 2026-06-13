<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class FcmService
{
    private string $projectId;
    private string $credentialsPath;

    public function __construct()
    {
        $this->credentialsPath = storage_path('app/firebase-credentials.json');

        // Ambil project_id langsung dari file credentials
        if (file_exists($this->credentialsPath)) {
            $credentials = json_decode(file_get_contents($this->credentialsPath), true);
            $this->projectId = $credentials['project_id'] ?? 'caritalent';
        } else {
            $this->projectId = 'caritalent';
        }
    }

    /**
     * Kirim push notification ke satu device berdasarkan FCM token.
     */
    public function sendToToken(string $fcmToken, string $title, string $body, array $data = []): bool
    {
        try {
            $accessToken = $this->getAccessToken();
            if (!$accessToken) {
                Log::warning('[FCM] Tidak bisa mendapatkan access token.');
                return false;
            }

            $response = Http::withToken($accessToken)
                ->post("https://fcm.googleapis.com/v1/projects/{$this->projectId}/messages:send", [
                    'message' => [
                        'token' => $fcmToken,
                        'notification' => [
                            'title' => $title,
                            'body'  => $body,
                        ],
                        'android' => [
                            'priority' => 'high',
                            'notification' => [
                                'sound'        => 'default',
                                'channel_id'   => 'caritalent_high_importance',
                                'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                            ],
                        ],
                        'data' => array_map('strval', $data),
                    ],
                ]);

            if ($response->successful()) {
                return true;
            }

            Log::warning('[FCM] Gagal kirim notifikasi', [
                'status'   => $response->status(),
                'response' => $response->body(),
            ]);
            return false;

        } catch (\Exception $e) {
            Log::error('[FCM] Exception: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Dapatkan OAuth2 access token dari service account credentials.
     * Token di-cache selama 55 menit agar tidak request berkali-kali.
     */
    private function getAccessToken(): ?string
    {
        return Cache::remember('fcm_access_token', 3300, function () {
            if (!file_exists($this->credentialsPath)) {
                Log::error('[FCM] File firebase-credentials.json tidak ditemukan di: ' . $this->credentialsPath);
                return null;
            }

            $credentials = json_decode(file_get_contents($this->credentialsPath), true);

            $now = time();
            $payload = [
                'iss'   => $credentials['client_email'],
                'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
                'aud'   => 'https://oauth2.googleapis.com/token',
                'iat'   => $now,
                'exp'   => $now + 3600,
            ];

            // Buat JWT secara manual (tanpa library eksternal)
            $header = base64_encode(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
            $claims = base64_encode(json_encode($payload));
            $base = $header . '.' . $claims;

            $privateKey = $credentials['private_key'];
            openssl_sign($base, $signature, $privateKey, OPENSSL_ALGO_SHA256);
            $jwt = $base . '.' . base64_encode($signature);

            // Tukar JWT dengan access token Google
            $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion'  => $jwt,
            ]);

            if ($response->successful()) {
                return $response->json('access_token');
            }

            Log::error('[FCM] Gagal mendapatkan access token', ['response' => $response->body()]);
            return null;
        });
    }
}
