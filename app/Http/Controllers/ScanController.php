<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Support\Facades\Auth;

class ScanController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:security']);
    }

    public function index()
    {
        return view('security.scan');
    }

    public function validateScan(Request $request)
    {
        // 1. Validasi Input
        $request->validate([
            'qr_code' => 'required|string'
        ]);

        $qrValue = $request->qr_code;
        $securityUser = Auth::user();

        // 2. Cari User berdasarkan QR Code
        $userScanned = User::with(['division', 'branch'])
                           ->where('qr_code_value', $qrValue)
                           ->first();

        if (!$userScanned) {
            return response()->json([
                'status' => 'error',
                'message' => 'QR Code tidak ditemukan dalam sistem.'
            ], 404);
        }

        // --- [MODIFIKASI] VALIDASI CABANG DIHAPUS AGAR BEBAS SCAN ---
        // Bagian pengecekan branch_id saya hapus supaya security bisa scan siapa saja.
        
        // 3. Cek Duplikasi Absen (Apakah sudah absen hari ini?)
        $alreadyAttended = Attendance::where('user_id', $userScanned->id)
                                     ->whereDate('check_in_time', today())
                                     ->exists();

        if ($alreadyAttended) {
            return response()->json([
                'status' => 'error',
                'message' => 'Karyawan atas nama ' . $userScanned->name . ' sudah absen hari ini.'
            ], 409); // 409 Conflict
        }

        // 4. Simpan Absensi
        try {
            $attendance = Attendance::create([
                'user_id' => $userScanned->id,
                'branch_id' => $userScanned->branch_id, // Tetap catat cabang asli karyawan
                'check_in_time' => now(),
                'status' => 'present',
                'scanned_by_user_id' => $securityUser->id,
                'verified_by_user_id' => null,
                'photo_path' => $userScanned->profile_photo_path,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Database Error: ' . $e->getMessage()
            ], 500);
        }

        // 5. Response Sukses
        return response()->json([
            'status' => 'success',
            'message' => 'Absensi Berhasil!',
            'data' => [
                'name' => $userScanned->name,
                'role' => $userScanned->role,
                'division' => $userScanned->division->name ?? '-',
                'branch' => $userScanned->branch->name ?? 'Pusat',
                'photo' => $userScanned->profile_photo_path 
                            ? asset('storage/' . $userScanned->profile_photo_path) 
                            : 'https://ui-avatars.com/api/?name=' . urlencode($userScanned->name) . '&background=random',
                'scan_time' => now()->format('H:i:s'),
            ]
        ]);
    }
}