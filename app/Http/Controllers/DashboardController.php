<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Division;
use App\Models\Attendance;
use App\Models\LateNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Menampilkan dashboard berdasarkan role user.
     */
    public function index()
    {
        $user = Auth::user();
        $data = [];
        $branch_id = $user->branch_id;

        // ======================================================
        // 1. QUERY DASAR (Filter Cabang)
        // ======================================================
        $attendanceQuery = Attendance::query();
        $userQuery = User::query();
        $divisionQuery = Division::query();
        $lateQuery = LateNotification::query();

        if ($user->role != 'admin' || $branch_id != null) {
            // Filter per cabang untuk User/Leader/Security/Admin Cabang
            $attendanceQuery->where('branch_id', $branch_id);
            $userQuery->where('branch_id', $branch_id);
            $divisionQuery->where('branch_id', $branch_id);
            $lateQuery->where('branch_id', $branch_id);
        }

        // ======================================================
        // 2. ISI DATA BERDASARKAN ROLE
        // ======================================================

        if ($user->role == 'admin') {
            // --- ADMIN ---
            $data['totalUsers'] = $userQuery->count();
            $data['totalDivisions'] = $divisionQuery->count();
            $data['attendancesToday'] = $attendanceQuery->whereDate('check_in_time', today())->count();
            $data['pendingVerifications'] = $attendanceQuery->where('status', 'pending_verification')->count();
        } elseif ($user->role == 'audit') {
            // --- AUDIT ---
            $data['myTeamMembers'] = $userQuery->whereIn('role', ['user_biasa', 'leader'])->count();
            $data['pendingVerifications'] = $attendanceQuery->where('status', 'pending_verification')->count();
            $data['attendancesToday'] = $attendanceQuery->whereDate('check_in_time', today())->count();
        } elseif ($user->role == 'security') {
            // --- SECURITY ---
            $data['myScansToday'] = Attendance::where('scanned_by_user_id', $user->id)
                ->whereDate('check_in_time', today())
                ->count();

            $data['totalUsers'] = $userQuery->whereIn('role', ['user_biasa', 'leader'])->count();
            // HANYA UNTUK USER BIASA & LEADER
        } elseif ($user->role == 'user_biasa' || $user->role == 'leader') {

            // AMBIL SEMUA absensi hari ini
            $todayAttendances = Attendance::where('user_id', $user->id)
                ->whereDate('check_in_time', today())
                ->orderBy('check_in_time', 'desc')
                ->get();

            // LOGIC PRIORITY:
            // 1. Cari yang SUDAH PULANG (check_out_time NOT NULL)
            $attendanceWithCheckout = $todayAttendances->first(function ($attendance) {
                return !is_null($attendance->check_out_time);
            });

            if ($attendanceWithCheckout) {
                // JIKA ADA YANG SUDAH PULANG
                $data['myAttendanceToday'] = $attendanceWithCheckout;
            } else {
                // 2. Cari yang punya photo_out_path (data rusak tapi sudah pulang)
                $attendanceWithPhotoOut = $todayAttendances->first(function ($attendance) {
                    return !is_null($attendance->photo_out_path);
                });

                if ($attendanceWithPhotoOut) {
                    // JIKA ADA YANG SUDAH PULANG (tapi check_out_time NULL)
                    $data['myAttendanceToday'] = $attendanceWithPhotoOut;
                } else {
                    // 3. Ambil yang terakhir (masih masuk)
                    $data['myAttendanceToday'] = $todayAttendances->first();
                }
            }

            $data['myPendingCount'] = Attendance::where('user_id', $user->id)
                ->where('status', 'pending_verification')
                ->count();

            $data['myTeamCount'] = User::where('division_id', $user->division_id)
                ->where('id', '!=', $user->id)
                ->count();

            $data['activeLateStatus'] = LateNotification::where('user_id', $user->id)
                ->where('is_active', true)
                ->whereDate('created_at', today())
                ->first();
        }

        return view('dashboard', $data);
    }
}
