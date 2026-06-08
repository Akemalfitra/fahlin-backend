<?php

namespace App\Services;

use Google\Client;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FcmService
{
    /**
     * Send push notification using FCM v1 API.
     */
    public static function send($token, $title, $body, $data = [])
    {
        if (empty($token)) {
            return false;
        }

        try {
            $credentialsPath = storage_path('app/firebase-service-account.json');
            
            if (!file_exists($credentialsPath)) {
                Log::warning("FCM Service Account JSON not found at: $credentialsPath");
                return false;
            }

            $client = new Client();
            $client->setAuthConfig($credentialsPath);
            $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
            
            $accessToken = $client->fetchAccessTokenWithAssertion()['access_token'];
            $projectId = json_decode(file_get_contents($credentialsPath), true)['project_id'];

            $response = Http::withToken($accessToken)->post("https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send", [
                'message' => [
                    'token' => $token,
                    'notification' => [
                        'title' => $title,
                        'body' => $body,
                    ],
                    'data' => array_map('strval', $data),
                    'android' => [
                        'notification' => [
                            'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                        ],
                    ],
                ],
            ]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error("FCM Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send push notification to multiple users.
     */
    public static function sendToMultiple($tokens, $title, $body, $data = [])
    {
        foreach ($tokens as $token) {
            self::send($token, $title, $body, $data);
        }
    }
}
