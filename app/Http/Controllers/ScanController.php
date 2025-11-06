<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ScanController extends Controller
{
    /**
     * Menampilkan halaman dengan kamera scanner.
     */
    public function scanPage()
    {
        return view('security.scan'); // File view dari Langkah 1
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
            'photo' => 'required|image|mimes:jpeg,png,jpg|max:2048', // Validasi foto
        ]);

        // Simpan foto
        $path = $request->file('photo')->store('public/attendance_photos');

        // Buat data absensi
        Attendance::create([
            'user_id' => $request->user_id, // ID user yang di-scan
            'check_in_time' => now(),
            'status' => 'verified', // Langsung verified karena di-scan security
            'photo_path' => $path, // Path foto
            'scanned_by_user_id' => Auth::id(), // ID Security yang sedang login
            'verified_by_user_id' => null, // Tidak perlu verifikasi audit lagi
        ]);

        return redirect()->route('security.scan')
            ->with('success', 'Absensi berhasil dicatat!');
    }
}
