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

    // FUNGSI 2: Simpan Absensi dengan Foto Real-time & Tipe Absen (FIXED)
    public function storeAttendance(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'type' => 'required|in:masuk,pulang,malam',
            'image' => 'required|string',
        ]);

        $user = User::find($request->user_id);
        $securityUser = Auth::user();

        // 1. Proses Upload Foto Base64
        $image = $request->image;
        $image = str_replace('data:image/png;base64,', '', $image);
        $image = str_replace(' ', '+', $image);

        // Nama file unik (bedakan masuk/pulang/malam)
        $typeLabel = $request->type;
        $imageName = 'attendance/capture_' . $typeLabel . '_' . time() . '_' . $user->id . '.png';

        Storage::disk('public')->put($imageName, base64_decode($image));

        // 2. Logika Absen
        if ($request->type == 'masuk') {
            // Cek double login - HANYA yang belum pulang
            $exists = Attendance::where('user_id', $user->id)
                ->whereDate('check_in_time', today())
                ->whereNull('check_out_time') // ← Hanya cek yang belum pulang
                ->exists();

            if ($exists) {
                return response()->json(['status' => 'error', 'message' => 'Karyawan ini sudah absen masuk hari ini!'], 409);
            }

            $attendance = Attendance::create([
                'user_id' => $user->id,
                'branch_id' => $user->branch_id,
                'check_in_time' => now(),
                'status' => 'present',
                'photo_path' => $imageName, // Foto MASUK
                'scanned_by_user_id' => $securityUser->id,
            ]);

            $msg = "Absen MASUK Berhasil";
        } elseif ($request->type == 'pulang') {
            // CARI record MASUK yang BELUM PULANG
            $attendance = Attendance::where('user_id', $user->id)
                ->whereDate('check_in_time', today())
                ->whereNull('check_out_time') // ← PASTIKAN cari yang belum pulang
                ->first();

            if (!$attendance) {
                return response()->json(['status' => 'error', 'message' => 'Karyawan ini belum absen masuk hari ini atau sudah pulang!'], 404);
            }

            // UPDATE record yang sama - JANGAN buat record baru
            $attendance->update([
                'check_out_time' => now(), // ← INI YANG PENTING!
                'photo_out_path' => $imageName, // Foto PULANG
            ]);

            $msg = "Absen PULANG Berhasil";
        } elseif ($request->type == 'malam') {
            // Logika lembur - buat record baru
            $attendance = Attendance::create([
                'user_id' => $user->id,
                'branch_id' => $user->branch_id,
                'check_in_time' => now(),
                'status' => 'overtime',
                'photo_path' => $imageName,
                'scanned_by_user_id' => $securityUser->id,
            ]);

            $msg = "Absen LEMBUR Berhasil";
        }

        return response()->json([
            'status' => 'success',
            'message' => $msg,
            'data' => [
                'name' => $user->name,
                'photo' => asset('storage/' . $imageName)
            ]
        ]);
    }
}
