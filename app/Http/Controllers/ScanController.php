<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ScanController extends Controller
{
    public function index()
    {
        return view('security.scan');
    }

    // FUNGSI 1: Cek QR Code dan Kembalikan Data User (Tanpa Absen Dulu)
    public function checkUser(Request $request)
    {
        $request->validate(['qr_code' => 'required|string']);

        $user = User::with(['division', 'branch'])
                    ->where('qr_code_value', $request->qr_code)
                    ->first();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'QR Code tidak ditemukan.'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'role' => $user->role,
                'division' => $user->division->name ?? '-',
                'branch' => $user->branch->name ?? 'Pusat',
                'photo_url' => $user->profile_photo_path 
                                ? asset('storage/' . $user->profile_photo_path) 
                                : 'https://ui-avatars.com/api/?name=' . urlencode($user->name)
            ]
        ]);
    }

    // FUNGSI 2: Simpan Absensi dengan Foto Real-time & Tipe Absen
    public function storeAttendance(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'type' => 'required|in:masuk,pulang,malam', // Validasi tipe
            'image' => 'required|string', // Base64 Image
        ]);

        $user = User::find($request->user_id);
        $securityUser = Auth::user();
        
        // Proses Upload Foto Base64
        $image = $request->image;  // data:image/png;base64,......
        $image = str_replace('data:image/png;base64,', '', $image);
        $image = str_replace(' ', '+', $image);
        $imageName = 'attendance/capture_' . time() . '_' . $user->id . '.png';
        Storage::disk('public')->put($imageName, base64_decode($image));

        // Logika Berdasarkan Tipe Absen
        if ($request->type == 'masuk') {
            // Cek double login
            $exists = Attendance::where('user_id', $user->id)->whereDate('check_in_time', today())->exists();
            if ($exists) {
                return response()->json(['status' => 'error', 'message' => 'Sudah absen masuk hari ini!'], 409);
            }

            Attendance::create([
                'user_id' => $user->id,
                'branch_id' => $user->branch_id,
                'check_in_time' => now(),
                'status' => 'present',
                'photo_path' => $imageName, // Simpan foto capture security
                'scanned_by_user_id' => $securityUser->id,
            ]);

            $msg = "Absen MASUK Berhasil";
        
        } elseif ($request->type == 'pulang') {
            // Cari absen masuk hari ini
            $attendance = Attendance::where('user_id', $user->id)
                                    ->whereDate('check_in_time', today())
                                    ->first();
            
            if (!$attendance) {
                return response()->json(['status' => 'error', 'message' => 'Belum ada absen masuk hari ini!'], 404);
            }

            // Update check_out_time (Pastikan kolom ini ada di database!)
            // Jika belum ada kolom check_out_time, logic ini harus disesuaikan
            $attendance->update([
                'check_out_time' => now(), // Asumsi ada kolom ini
                // 'photo_out_path' => $imageName, // Opsional jika mau simpan foto pulang
            ]);

            $msg = "Absen PULANG Berhasil";

        } elseif ($request->type == 'malam') {
            // Absen malam / Lembur (Bisa dianggap check-in baru atau status khusus)
            Attendance::create([
                'user_id' => $user->id,
                'branch_id' => $user->branch_id,
                'check_in_time' => now(),
                'status' => 'overtime', // Status khusus
                'photo_path' => $imageName,
                'scanned_by_user_id' => $securityUser->id,
            ]);

            $msg = "Absen MALAM/LEMBUR Berhasil";
        }

        return response()->json([
            'status' => 'success',
            'message' => $msg,
            'data' => [
                'name' => $user->name,
                'photo' => asset('storage/' . $imageName) // Tampilkan foto yang baru dicapture
            ]
        ]);
    }
}