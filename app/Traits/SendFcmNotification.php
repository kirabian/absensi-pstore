<?php

namespace App\Traits;

use App\Models\UserDeviceToken;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use App\Models\User;

// --- TAMBAHKAN 2 BARIS INI UNTUK SUARA ---
use Kreait\Firebase\Messaging\AndroidConfig;
use Kreait\Firebase\Messaging\ApnsConfig;
// ---------------------------------------

trait SendFcmNotification
{
    /**
     * Mengirim notifikasi ke SATU user.
     */
    protected function sendNotificationToUser(User $user, $title, $body)
    {
        $tokens = UserDeviceToken::where('user_id', $user->id)->pluck('token')->toArray();

        if (empty($tokens)) {
            return;
        }

        $messaging = app('firebase.messaging');

        // --- PERBAIKAN DI BLOK INI ---
        $notification = Notification::create($title, $body);

        // Atur suara untuk Android
        $androidConfig = AndroidConfig::new()
            ->withSound('default'); // 'default' atau 'suara-notif.mp3'

        // Atur suara untuk Apple (iOS)
        $apnsConfig = ApnsConfig::new()
            ->withSound('default'); // 'default' atau 'suara-notif.mp3'

        $message = CloudMessage::new()
            ->withNotification($notification)
            ->withAndroidConfig($androidConfig) // Tambahkan config Android
            ->withApnsConfig($apnsConfig);      // Tambahkan config Apple
        // --- BATAS PERBAIKAN ---

        try {
            $report = $messaging->sendMulticast($message, $tokens);

            if ($report->hasFailures()) {
                foreach ($report->failures()->tokens() as $token) {
                    UserDeviceToken::where('token', $token)->delete();
                }
            }
        } catch (\Exception $e) {
            // Log::error('FCM Send Error: '. $e->getMessage());
        }
    }

    /**
     * Mengirim notifikasi ke BANYAK user (misal, semua admin).
     */
    protected function sendNotificationToRoles(array $roles, $title, $body)
    {
        $tokens = UserDeviceToken::whereHas('user', function ($query) use ($roles) {
            $query->whereIn('role', $roles);
        })->pluck('token')->toArray();

        if (empty($tokens)) {
            return;
        }

        $messaging = app('firebase.messaging');

        // --- PERBAIKAN DI BLOK INI JUGA ---
        $notification = Notification::create($title, $body);

        // Atur suara untuk Android
        $androidConfig = AndroidConfig::new()
            ->withSound('default'); // 'default' atau 'suara-notif.mp3'

        // Atur suara untuk Apple (iOS)
        $apnsConfig = ApnsConfig::new()
            ->withSound('default'); // 'default' atau 'suara-notif.mp3'

        $message = CloudMessage::new()
            ->withNotification($notification)
            ->withAndroidConfig($androidConfig) // Tambahkan config Android
            ->withApnsConfig($apnsConfig);      // Tambahkan config Apple
        // --- BATAS PERBAIKAN ---

        try {
            $report = $messaging->sendMulticast($message, $tokens);

            if ($report->hasFailures()) {
                foreach ($report->failures()->tokens() as $token) {
                    UserDeviceToken::where('token', $token)->delete();
                }
            }
        } catch (\Exception $e) {
            // Log::error('FCM Send Error: '. $e->getMessage());
        }
    }
}
