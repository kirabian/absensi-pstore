<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Traits\SendFcmNotification;
use Illuminate\Support\Facades\Log;

class ScanController extends Controller
{
    use SendFcmNotification;

    public function scanPage()
    {
        return view('security.scan');
    }

    public function getUserByQr(Request $request)
{
    try {
        $request->validate(['qr_code' => 'required|string']);
        $security = Auth::user();

        // Cek user berdasarkan QR dan Cabang Security
        $user = User::where('qr_code_value', $request->qr_code)
            ->where('branch_id', $security->branch_id)
            ->with('division')
            ->first();

        if ($user) {
            // Gunakan method dari model yang sudah diperbaiki
            $alreadyAbsen = Attendance::hasUserAttendedToday($user->id);

            return response()->json([
                'status' => 'success',
                'user' => $user,
                'division_name' => $user->division->name ?? '-',
                'already_absen' => $alreadyAbsen
            ]);
        }

        return response()->json([
            'status' => 'error', 
            'message' => 'Karyawan tidak ditemukan atau beda cabang.'
        ], 404);

    } catch (\Exception $e) {
        Log::error("Error Scan QR: " . $e->getMessage());
        return response()->json([
            'status' => 'error', 
            'message' => 'Terjadi kesalahan server.'
        ], 500);
    }
}

    public function storeAttendance(Request $request)
{
    $request->validate([
        'user_id' => 'required|exists:users,id',
        'photo' => 'required|image|mimes:jpeg,png,jpg|max:4048',
    ]);

    try {
        $user = User::findOrFail($request->user_id);
        $security = Auth::user();
        
        // Validasi: pastikan user yang di-scan berada di cabang yang sama dengan security
        if ($user->branch_id !== $security->branch_id) {
            return redirect()->route('security.scan')
                ->with('error', 'Tidak bisa absen: karyawan beda cabang.');
        }

        // Validasi: cek apakah sudah absen hari ini
        $alreadyAbsen = Attendance::where('user_id', $user->id)
            ->whereDate('check_in_time', today())
            ->exists();

        if ($alreadyAbsen) {
            return redirect()->route('security.scan')
                ->with('error', $user->name . ' sudah absen hari ini.');
        }

        // Simpan Foto ke storage public
        $path = $request->file('photo')->store('foto_security', 'public');

        // Buat record absensi - PASTIKAN branch_id DISERTAKAN
        Attendance::create([
            'user_id' => $user->id,
            'branch_id' => $user->branch_id, // INI YANG PENTING!
            'check_in_time' => now(),
            'status' => 'verified',
            'photo_path' => $path,
            'scanned_by_user_id' => $security->id,
        ]);

        // Kirim Notifikasi ke Admin & Audit di cabang yang sama + Super Admin
        $title = "📋 Absensi Masuk (Security)";
        $body = $user->name . " telah diabsen masuk oleh Security " . $security->name;
        
        try {
            $this->sendNotificationToBranchRoles(
                ['admin', 'audit'], 
                $user->branch_id, 
                $title, 
                $body
            );
        } catch (\Exception $ex) {
            Log::error('FCM Notification Failed: ' . $ex->getMessage());
        }

        return redirect()->route('security.scan')
            ->with('success', 'Berhasil! Absensi ' . $user->name . ' tercatat.');

    } catch (\Exception $e) {
        Log::error("Store Attendance Error: " . $e->getMessage());
        return redirect()->route('security.scan')
            ->with('error', 'Gagal menyimpan absen: ' . $e->getMessage());
    }
}
}