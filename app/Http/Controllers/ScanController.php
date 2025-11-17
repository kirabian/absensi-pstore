<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage; // PASTIKAN INI DIIMPORT
use Illuminate\Support\Facades\Log; // GUNAKAN LOG FACADE

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
        Log::info('ScanController accessed by user: ' . Auth::id());
        return view('security.scan');
    }

    /**
     * Proses Validasi QR Code
     */
    public function validateScan(Request $request)
    {
        Log::info('Validate scan request', ['user' => Auth::id(), 'data' => $request->all()]);

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
            Log::warning('QR code not found: ' . $qrValue);
            return response()->json([
                'status' => 'error',
                'message' => 'QR Code tidak terdaftar.'
            ], 404);
        }

        // Validasi cabang
        if ($securityUser->branch_id && $userScanned->branch_id && 
            $securityUser->branch_id != $userScanned->branch_id) {
            Log::warning('Branch mismatch', [
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
            Log::warning('User already attended today', [
                'user_id' => $userScanned->id,
                'user_name' => $userScanned->name
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Karyawan sudah absen hari ini.'
            ], 409);
        }

        // === SIMPAN DATA ABSENSI ===
        try {
            $attendance = Attendance::create([
                'user_id' => $userScanned->id,
                'branch_id' => $userScanned->branch_id,
                'check_in_time' => now(),
                'status' => 'present', // Status awal
                'scanned_by_user_id' => $securityUser->id,
                'verified_by_user_id' => null,
                'photo_path' => $userScanned->profile_photo_path,
                'latitude' => null,
                'longitude' => null,
            ]);

            Log::info('Attendance recorded successfully', [
                'attendance_id' => $attendance->id,
                'user_scanned' => $userScanned->id,
                'user_name' => $userScanned->name,
                'security_user' => $securityUser->id,
                'security_name' => $securityUser->name,
                'branch' => $userScanned->branch->name ?? 'Pusat'
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to save attendance: ' . $e->getMessage());
            
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menyimpan data absensi: ' . $e->getMessage()
            ], 500);
        }

        // Response sukses
        Log::info('Scan successful', [
            'scanned_user' => $userScanned->id,
            'attendance_id' => $attendance->id
        ]);
        
        return response()->json([
            'status' => 'success',
            'message' => 'Absensi berhasil dicatat! Silakan lanjutkan dengan foto selfie.',
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
                'attendance_id' => $attendance->id,
                'user_id' => $userScanned->id // TAMBAHKAN INI UNTUK COMPLETE ATTENDANCE
            ]
        ]);
    }

    /**
     * Menyelesaikan Absensi dengan Foto Selfie
     */
    public function completeAttendance(Request $request)
    {
        Log::info('Complete attendance request', ['user' => Auth::id()]);

        $request->validate([
            'user_data' => 'required|array',
            'selfie_photo' => 'required|string'
        ]);

        try {
            $userData = $request->user_data;
            
            // Cari user berdasarkan ID (lebih akurat daripada name & role)
            $user = User::find($userData['user_id']);

            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Data user tidak valid'
                ], 404);
            }

            // Process selfie photo
            $selfiePath = null;
            if ($request->selfie_photo) {
                $image = $request->selfie_photo;
                
                // Handle base64 image
                if (preg_match('/^data:image\/(\w+);base64,/', $image, $type)) {
                    $image = substr($image, strpos($image, ',') + 1);
                    $type = strtolower($type[1]); // jpg, png, gif
                    
                    if (!in_array($type, ['jpg', 'jpeg', 'png', 'gif'])) {
                        throw new \Exception('Format gambar tidak didukung');
                    }
                    
                    $image = str_replace(' ', '+', $image);
                    $imageDecoded = base64_decode($image);
                    
                    if ($imageDecoded === false) {
                        throw new \Exception('Gagal decode base64 image');
                    }
                } else {
                    // Jika tanpa data URL prefix, langsung decode
                    $image = str_replace(' ', '+', $image);
                    $imageDecoded = base64_decode($image);
                }
                
                $imageName = 'selfie_' . time() . '_' . Str::slug($user->name) . '.jpg';
                $selfiePath = 'attendance-selfies/' . $imageName;
                
                // Simpan ke storage
                Storage::disk('public')->put($selfiePath, $imageDecoded);
            }

            // Update the latest attendance record for this user
            $attendance = Attendance::where('user_id', $user->id)
                                  ->whereDate('check_in_time', today())
                                  ->latest()
                                  ->first();

            if ($attendance) {
                $attendance->update([
                    'photo_path' => $selfiePath, // Update dengan path selfie
                    'status' => 'completed' // Ubah status menjadi completed
                ]);

                Log::info('Attendance completed with selfie', [
                    'attendance_id' => $attendance->id,
                    'user_id' => $user->id,
                    'selfie_path' => $selfiePath
                ]);

                return response()->json([
                    'status' => 'success',
                    'message' => 'Absensi berhasil diselesaikan dengan foto selfie.'
                ]);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Data absensi tidak ditemukan'
                ], 404);
            }

        } catch (\Exception $e) {
            Log::error('Complete attendance failed: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menyelesaikan absensi: ' . $e->getMessage()
            ], 500);
        }
    }
}