<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

// Nanti kita akan buat model ini
// Buat file app/Models/UserDeviceToken.php
use App\Models\UserDeviceToken;

class NotificationController extends Controller
{
    /**
     * Menyimpan token FCM dari user yang sedang login.
     */
    public function saveToken(Request $request)
    {
        $request->validate(['token' => 'required']);

        // Simpan atau update token
        UserDeviceToken::updateOrCreate(
            [
                'user_id' => Auth::id(),
                'token' => $request->token,
            ],
            [
                'user_id' => Auth::id(),
                'token' => $request->token,
            ]
        );

        return response()->json(['message' => 'Token saved successfully.']);
    }
}
