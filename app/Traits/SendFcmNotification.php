<?php

namespace App\Traits;

use App\Models\UserDeviceToken;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use App\Models\User;
use Kreait\Firebase\Messaging\AndroidConfig;
use Kreait\Firebase\Messaging\ApnsConfig;

trait SendFcmNotification
{
    /**
     * Mengirim notifikasi ke SATU user spesifik.
     * (Fungsi ini sudah benar dan tidak berubah)
     */
    protected function sendNotificationToUser(User $user, $title, $body)
    {
        $tokens = UserDeviceToken::where('user_id', $user->id)->pluck('token')->toArray();
        if (empty($tokens)) return;

        $messaging = app('firebase.messaging');
        $notification = Notification::create($title, $body);
        $androidConfig = AndroidConfig::new()->withSound('default');
        $apnsConfig = ApnsConfig::new()->withSound('default');
        $message = CloudMessage::new()
            ->withNotification($notification)
            ->withAndroidConfig($androidConfig)
            ->withApnsConfig($apnsConfig);

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
     * FUNGSI BARU: Mengirim notifikasi ke role di CABANG SPESIFIK + SUPER ADMIN
     *
     * @param array $roles - Cth: ['admin', 'audit']
     * @param int $branch_id - ID Cabang tempat kejadian
     * @param string $title - Judul notifikasi
     * @param string $body - Isi pesan
     */
    protected function sendNotificationToBranchRoles(array $roles, $branch_id, $title, $body)
    {
        // 1. Ambil token untuk Admin/Audit di CABANG SPESIFIK itu
        $branchTokens = UserDeviceToken::whereHas('user', function ($query) use ($roles, $branch_id) {
            $query->whereIn('role', $roles)
                ->where('branch_id', $branch_id);
        })->pluck('token')->toArray();

        // 2. Ambil token untuk SEMUA Super Admin (admin yg branch_id-nya null)
        $superAdminTokens = UserDeviceToken::whereHas('user', function ($query) {
            $query->where('role', 'admin')
                ->whereNull('branch_id');
        })->pluck('token')->toArray();

        // 3. Gabungkan semua token (hapus duplikat jika ada)
        $tokens = array_unique(array_merge($branchTokens, $superAdminTokens));

        if (empty($tokens)) {
            return; // Tidak ada device untuk dikirimi
        }

        $messaging = app('firebase.messaging');
        $notification = Notification::create($title, $body);
        $androidConfig = AndroidConfig::new()->withSound('default');
        $apnsConfig = ApnsConfig::new()->withSound('default');
        $message = CloudMessage::new()
            ->withNotification($notification)
            ->withAndroidConfig($androidConfig)
            ->withApnsConfig($apnsConfig);

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
