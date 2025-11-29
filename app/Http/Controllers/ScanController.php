<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Attendance;
use App\Models\WorkSchedule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ScanController extends Controller
{
    public function index()
    {
        return view('security.scan');
    }

    // FUNGSI 1: Cek QR Code dan Kembalikan Status HARI INI
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

        // Cek Sesi HARI INI
        // Kita hanya mencari sesi check-in HARI INI yang belum checkout.
        $attendanceSession = Attendance::where('user_id', $user->id)
            ->whereNull('check_out_time')
            ->whereDate('check_in_time', today()) // Strict Today
            ->first();

        // Jika tidak ada sesi aktif, ambil history hari ini (jika sudah pulang)
        if (!$attendanceSession) {
            $attendanceSession = Attendance::where('user_id', $user->id)
                ->whereDate('check_in_time', today())
                ->latest('check_in_time')
                ->first();
        }

        $workSchedule = WorkSchedule::getScheduleForUser($user->id);

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
                    : 'https://ui-avatars.com/api/?name=' . urlencode($user->name),
                
                'attendance_status' => $attendanceSession ? [
                    'has_checked_in' => !is_null($attendanceSession->check_in_time),
                    'has_checked_out' => !is_null($attendanceSession->check_out_time),
                    'check_in_time' => $attendanceSession->check_in_time?->format('H:i'),
                    'check_out_time' => $attendanceSession->check_out_time?->format('H:i'),
                    'is_late' => $attendanceSession->is_late_checkin,
                ] : null,

                'work_schedule' => $workSchedule ? [
                    'check_in_start' => $workSchedule->check_in_start->format('H:i'),
                    'check_in_end' => $workSchedule->check_in_end->format('H:i'),
                    'check_out_start' => $workSchedule->check_out_start->format('H:i'),
                    'check_out_end' => $workSchedule->check_out_end->format('H:i'),
                ] : null
            ]
        ]);
    }

    // FUNGSI 2: Simpan Absensi (Auto Close Yesterday & Create Today)
    public function storeAttendance(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'type' => 'required|in:masuk,pulang',
            'image' => 'required|string',
        ]);

        $user = User::find($request->user_id);
        $securityUser = Auth::user();
        $workSchedule = WorkSchedule::getScheduleForUser($user->id);
        $currentTime = now();

        // 1. Proses Upload Foto Base64
        $image = $request->image;
        $image = str_replace('data:image/png;base64,', '', $image);
        $image = str_replace(' ', '+', $image);
        $typeLabel = $request->type;
        $imageName = 'attendance/capture_' . $typeLabel . '_' . time() . '_' . $user->id . '.png';
        Storage::disk('public')->put($imageName, base64_decode($image));

        // ==============================================================
        // LOGIKA ABSEN MASUK (CHECK-IN)
        // ==============================================================
        if ($request->type == 'masuk') {
            
            // --- [FITUR AUTO RESET] ---
            // Cek sesi lama yang lupa di-checkout (sebelum hari ini)
            $hangingSessions = Attendance::where('user_id', $user->id)
                ->whereNull('check_out_time')
                ->whereDate('check_in_time', '<', today())
                ->get();

            foreach ($hangingSessions as $hanging) {
                // Auto Close session kemarin
                $hanging->update([
                    'check_out_time' => Carbon::parse($hanging->check_in_time)->endOfDay(),
                    'notes' => 'Auto-closed by Security Scan (Lupa Pulang)',
                ]);
            }
            // --------------------------

            // Cek double login HARI INI
            $existsToday = Attendance::where('user_id', $user->id)
                ->whereDate('check_in_time', today())
                ->whereNull('check_out_time')
                ->exists();

            if ($existsToday) {
                return response()->json(['status' => 'error', 'message' => 'Karyawan ini sudah absen masuk hari ini!'], 409);
            }

            // Validasi Keterlambatan
            $isLate = false;
            $status = 'present';

            if ($workSchedule) {
                $checkInTime = Carbon::parse($currentTime);
                $scheduleEnd = Carbon::parse($workSchedule->check_in_end);

                if ($checkInTime->gt($scheduleEnd)) {
                    $isLate = true;
                    $status = 'late';
                }
            }

            // Create Absen Baru
            Attendance::create([
                'user_id' => $user->id,
                'branch_id' => $user->branch_id,
                'check_in_time' => $currentTime,
                'status' => $status,
                'photo_path' => $imageName,
                'scanned_by_user_id' => $securityUser->id,
                'work_schedule_id' => $workSchedule?->id,
                'is_late_checkin' => $isLate,
                'attendance_type' => 'scan',
            ]);

            $msg = $isLate ? "Absen MASUK Berhasil (TERLAMBAT)" : "Absen MASUK Berhasil";

        } 
        // ==============================================================
        // LOGIKA ABSEN PULANG (CHECK-OUT)
        // ==============================================================
        elseif ($request->type == 'pulang') {
            
            // Cari Sesi Aktif HARI INI
            $attendance = Attendance::where('user_id', $user->id)
                ->whereNull('check_out_time')
                ->whereDate('check_in_time', today()) // Harus hari ini
                ->first();

            if (!$attendance) {
                return response()->json(['status' => 'error', 'message' => 'Karyawan ini belum absen masuk hari ini!'], 404);
            }

            // Validasi Pulang Cepat
            $isEarlyCheckout = false;
            if ($workSchedule) {
                $checkOutTime = Carbon::parse($currentTime);
                $scheduleStart = Carbon::parse($workSchedule->check_out_start);
                
                // Cek hanya jamnya saja
                $coTime = Carbon::parse($currentTime->format('H:i:s'));
                if ($coTime->lt($scheduleStart)) {
                    $isEarlyCheckout = true;
                }
            }

            // UPDATE record
            $attendance->update([
                'check_out_time' => $currentTime,
                'photo_out_path' => $imageName,
                'is_early_checkout' => $isEarlyCheckout,
            ]);

            $msg = $isEarlyCheckout ? "Absen PULANG Berhasil (PULANG CEPAT)" : "Absen PULANG Berhasil";
        }

        return response()->json([
            'status' => 'success',
            'message' => $msg,
            'data' => [
                'name' => $user->name,
                'photo' => asset('storage/' . $imageName),
                'time' => $currentTime->format('H:i:s'),
                'date' => $currentTime->format('d-m-Y'),
                'is_late' => $isLate ?? false,
                'is_early_checkout' => $isEarlyCheckout ?? false,
                'work_schedule' => $workSchedule ? [
                    'check_in_start' => $workSchedule->check_in_start->format('H:i'),
                    'check_in_end' => $workSchedule->check_in_end->format('H:i'),
                    'check_out_start' => $workSchedule->check_out_start->format('H:i'),
                    'check_out_end' => $workSchedule->check_out_end->format('H:i'),
                ] : null
            ]
        ]);
    }

    // FUNGSI 3: Get Stats (SAMA SEPERTI SEBELUMNYA)
    public function getStats(Request $request)
    {
        $securityUser = Auth::user();
        $today = today();

        $stats = [
            'total_scans_today' => Attendance::where('scanned_by_user_id', $securityUser->id)
                ->whereDate('check_in_time', $today)
                ->count(),
            'check_in_count' => Attendance::where('scanned_by_user_id', $securityUser->id)
                ->whereDate('check_in_time', $today)
                ->whereNotNull('check_in_time')
                ->count(),
            'check_out_count' => Attendance::where('scanned_by_user_id', $securityUser->id)
                ->whereDate('check_in_time', $today)
                ->whereNotNull('check_out_time')
                ->count(),
            'late_count' => Attendance::where('scanned_by_user_id', $securityUser->id)
                ->whereDate('check_in_time', $today)
                ->where('is_late_checkin', true)
                ->count(),
        ];

        return response()->json([
            'status' => 'success',
            'data' => $stats
        ]);
    }
}