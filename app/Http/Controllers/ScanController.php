<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Attendance;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ScanController extends Controller
{
    /**
     * Menampilkan halaman scan QR code untuk security
     */
    public function scanPage()
    {
        // Cek apakah user adalah security
        if (Auth::user()->role !== 'security') {
            abort(403, 'Akses ditolak. Hanya security yang dapat mengakses halaman ini.');
        }

        return view('security.scan');
    }

    /**
     * Mendapatkan data user berdasarkan QR code
     */
    public function getUserByQr(Request $request)
    {
        try {
            $request->validate([
                'qr_code' => 'required|string'
            ]);

            $security = Auth::user();

            // Cek user berdasarkan QR code dan cabang security
            $user = User::where('qr_code_value', $request->qr_code)
                ->where('branch_id', $security->branch_id)
                ->with(['division', 'branch'])
                ->first();

            if ($user) {
                // Cek apakah user sudah absen hari ini
                $alreadyAbsen = Attendance::where('user_id', $user->id)
                    ->whereDate('check_in_time', today())
                    ->exists();

                return response()->json([
                    'status' => 'success',
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'profile_photo_path' => $user->profile_photo_path,
                        'division_id' => $user->division_id,
                        'branch_id' => $user->branch_id,
                    ],
                    'division_name' => $user->division->name ?? '-',
                    'branch_name' => $user->branch->name ?? '-',
                    'already_absen' => $alreadyAbsen
                ]);
            }

            return response()->json([
                'status' => 'error', 
                'message' => 'Karyawan tidak ditemukan atau berada di cabang yang berbeda.'
            ], 404);

        } catch (\Exception $e) {
            Log::error("Error Scan QR: " . $e->getMessage());
            return response()->json([
                'status' => 'error', 
                'message' => 'Terjadi kesalahan server. Silakan coba lagi.'
            ], 500);
        }
    }

    /**
     * Menyimpan data absensi dari scan QR code
     */
    public function storeAttendance(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'photo' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120', // 5MB max
        ]);

        try {
            $user = User::findOrFail($request->user_id);
            $security = Auth::user();

            // Validasi: pastikan user yang di-scan berada di cabang yang sama dengan security
            if ($user->branch_id !== $security->branch_id) {
                return redirect()->route('security.scan')
                    ->with('error', 'Tidak dapat melakukan absen: karyawan berada di cabang berbeda.');
            }

            // Validasi: cek apakah sudah absen hari ini
            $alreadyAbsen = Attendance::where('user_id', $user->id)
                ->whereDate('check_in_time', today())
                ->exists();

            if ($alreadyAbsen) {
                return redirect()->route('security.scan')
                    ->with('error', $user->name . ' sudah melakukan absen hari ini.');
            }

            // Simpan foto bukti absensi
            $photoPath = $request->file('photo')->store('attendance_photos', 'public');

            // Buat record absensi
            Attendance::create([
                'user_id' => $user->id,
                'branch_id' => $user->branch_id,
                'check_in_time' => now(),
                'status' => 'verified', // Langsung verified karena diverifikasi security
                'photo_path' => $photoPath,
                'scanned_by_user_id' => $security->id,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
            ]);

            // Log activity
            Log::info("Absensi berhasil: {$user->name} diabsen oleh security {$security->name}");

            return redirect()->route('security.scan')
                ->with('success', 'Absensi berhasil! ' . $user->name . ' telah tercatat.');

        } catch (\Exception $e) {
            Log::error("Store Attendance Error: " . $e->getMessage());
            return redirect()->route('security.scan')
                ->with('error', 'Gagal menyimpan absensi: ' . $e->getMessage());
        }
    }

    /**
     * Mendapatkan riwayat absensi hari ini (untuk security)
     */
    public function getTodayAttendance()
    {
        try {
            $security = Auth::user();
            
            $todayAttendance = Attendance::with(['user', 'user.division'])
                ->where('branch_id', $security->branch_id)
                ->whereDate('check_in_time', today())
                ->orderBy('check_in_time', 'desc')
                ->get()
                ->map(function ($attendance) {
                    return [
                        'id' => $attendance->id,
                        'user_name' => $attendance->user->name,
                        'division_name' => $attendance->user->division->name ?? '-',
                        'check_in_time' => $attendance->check_in_time->format('H:i:s'),
                        'photo_url' => $attendance->photo_path ? Storage::url($attendance->photo_path) : null,
                    ];
                });

            return response()->json([
                'status' => 'success',
                'data' => $todayAttendance
            ]);

        } catch (\Exception $e) {
            Log::error("Get Today Attendance Error: " . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil data absensi'
            ], 500);
        }
    }

    /**
     * Mendapatkan statistik absensi hari ini
     */
    public function getAttendanceStats()
    {
        try {
            $security = Auth::user();
            
            $totalUsers = User::where('branch_id', $security->branch_id)
                ->where('role', '!=', 'security')
                ->count();

            $attendedToday = Attendance::where('branch_id', $security->branch_id)
                ->whereDate('check_in_time', today())
                ->count();

            $notAttended = $totalUsers - $attendedToday;

            return response()->json([
                'status' => 'success',
                'data' => [
                    'total_users' => $totalUsers,
                    'attended_today' => $attendedToday,
                    'not_attended' => $notAttended > 0 ? $notAttended : 0,
                    'attendance_percentage' => $totalUsers > 0 ? round(($attendedToday / $totalUsers) * 100, 2) : 0
                ]
            ]);

        } catch (\Exception $e) {
            Log::error("Get Attendance Stats Error: " . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil statistik absensi'
            ], 500);
        }
    }
}