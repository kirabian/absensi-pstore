<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Support\Facades\Auth;

class ScanController extends Controller
{
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->middleware(['auth', 'role:security']);
    }

    /**
     * Menampilkan View Scanner
     */
    public function index()
    {
        \Illuminate\Support\Facades\Log::info('ScanController accessed by user: ' . Auth::id());
        return view('security.scan');
    }

    /**
     * Proses Validasi QR Code
     */
    public function validateScan(Request $request)
    {
        \Illuminate\Support\Facades\Log::info('Validate scan request', ['user' => Auth::id(), 'data' => $request->all()]);

        // Validasi input
        $request->validate([
            'qr_code' => 'required|string'
        ]);

        $qrValue = $request->qr_code;
        $securityUser = Auth::user();

        // Cari user berdasarkan QR code
        $userScanned = User::with(['division', 'branch'])
                           ->where('qr_code_value', $qrValue)
                           ->first();

        if (!$userScanned) {
            \Illuminate\Support\Facades\Log::warning('QR code not found: ' . $qrValue);
            return response()->json([
                'status' => 'error',
                'message' => 'QR Code tidak terdaftar.'
            ], 404);
        }

        // Validasi cabang
        if ($securityUser->branch_id && $userScanned->branch_id && 
            $securityUser->branch_id != $userScanned->branch_id) {
            \Illuminate\Support\Facades\Log::warning('Branch mismatch', [
                'security_branch' => $securityUser->branch_id,
                'user_branch' => $userScanned->branch_id
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'GAGAL: Karyawan dari cabang berbeda (' . ($userScanned->branch->name ?? 'Pusat') . ').'
            ], 403);
        }

        // === CEK APAKAH SUDAH ABSEN HARI INI ===
        $alreadyAttended = Attendance::where('user_id', $userScanned->id)
                                    ->whereDate('check_in_time', today())
                                    ->exists();

        if ($alreadyAttended) {
            \Illuminate\Support\Facades\Log::warning('User already attended today', [
                'user_id' => $userScanned->id,
                'user_name' => $userScanned->name
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Karyawan sudah absen hari ini.'
            ], 409); // 409 Conflict
        }

        // === SIMPAN DATA ABSENSI ===
        try {
            $attendance = Attendance::create([
                'user_id' => $userScanned->id,
                'branch_id' => $userScanned->branch_id,
                'check_in_time' => now(),
                'status' => 'present',
                'scanned_by_user_id' => $securityUser->id,
                'verified_by_user_id' => null, // Belum diverifikasi audit
                'photo_path' => $userScanned->profile_photo_path, // Simpan foto profil user
                'latitude' => null, // Bisa ditambahkan jika ada GPS
                'longitude' => null, // Bisa ditambahkan jika ada GPS
            ]);

            \Illuminate\Support\Facades\Log::info('Attendance recorded successfully', [
                'attendance_id' => $attendance->id,
                'user_scanned' => $userScanned->id,
                'user_name' => $userScanned->name,
                'security_user' => $securityUser->id,
                'security_name' => $securityUser->name,
                'branch' => $userScanned->branch->name ?? 'Pusat'
            ]);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to save attendance: ' . $e->getMessage());
            
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menyimpan data absensi: ' . $e->getMessage()
            ], 500);
        }

        // Response sukses
        \Illuminate\Support\Facades\Log::info('Scan successful', [
            'scanned_user' => $userScanned->id,
            'attendance_id' => $attendance->id
        ]);
        
        return response()->json([
            'status' => 'success',
            'message' => 'Absensi berhasil dicatat!',
            'data' => [
                'name' => $userScanned->name,
                'role' => $userScanned->role,
                'division' => $userScanned->division->name ?? '-',
                'branch' => $userScanned->branch->name ?? 'Pusat',
                'photo' => $userScanned->profile_photo_path 
                            ? asset('storage/' . $userScanned->profile_photo_path) 
                            : 'https://ui-avatars.com/api/?name=' . urlencode($userScanned->name) . '&background=random',
                'scan_time' => now()->format('H:i:s'),
                'scan_date' => now()->format('d-m-Y'),
                'attendance_id' => $attendance->id
            ]
        ]);
    }
}