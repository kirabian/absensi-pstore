<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Traits\SendFcmNotification; // <-- 1. IMPORT TRAIT NOTIFIKASI

class ScanController extends Controller
{
    use SendFcmNotification; // <-- 2. GUNAKAN TRAIT NOTIFIKASI

    /**
     * Menampilkan halaman dengan kamera scanner.
     */
    public function scanPage()
    {
        // Diubah ke 'psguard.scan_index' sesuai permintaan Anda sebelumnya
        return view('psguard.scan_index');
    }

    /**
     * [Route API]
     * Menerima QR Code dari JavaScript dan mengembalikan data user.
     */
    public function getUserByQr(Request $request)
    {
        $request->validate(['qr_code' => 'required|string']);

        $user = User::where('qr_code_value', $request->qr_code)->with('division')->first();

        if ($user) {
            // Jika ketemu, kirim data user (dan nama divisinya)
            return response()->json([
                'user' => $user,
                'division_name' => $user->division->name ?? null
            ]);
        } else {
            // Jika tidak ketemu
            return response()->json(['error' => 'User not found'], 404);
        }
    }

    /**
     * [Route Web]
     * Menyimpan data absensi (termasuk foto) ke database.
     */
    public function storeAttendance(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'photo' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        // Simpan foto ke 'storage/app/public/foto_security'
        $path = $request->file('photo')->store('public/foto_security');

        // Ambil data user yang di-scan untuk notifikasi
        $user = User::find($request->user_id);

        Attendance::create([
            'user_id' => $request->user_id,
            'check_in_time' => now(),
            'status' => 'verified', // <-- Sesuai permintaan Anda (langsung masuk)
            'photo_path' => $path, // <-- Foto disimpan
            'scanned_by_user_id' => Auth::id(), // ID Security
        ]);

        // --- 3. KIRIM NOTIFIKASI ---
        $title = "Absensi Masuk (Security)";
        $body = $user->name . " telah diabsen masuk oleh Security.";
        $this->sendNotificationToRoles(['admin', 'audit'], $title, $body);
        // -----------------------------

        return redirect()->route('security.scan')
            ->with('success', 'Absensi berhasil dicatat!');
    }
}
