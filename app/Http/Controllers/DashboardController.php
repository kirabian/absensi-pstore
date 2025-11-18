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
        } elseif ($user->role == 'user_biasa' || $user->role == 'leader') {
            // --- Data untuk USER BIASA & LEADER ---

            // PERBAIKAN: CARI ABSENSI HARI INI YANG SUDAH PULANG DULU
            // Priority: 1. Yang sudah pulang, 2. Yang masih masuk, 3. Tidak ada
            
            // Cari yang SUDAH PULANG (ada check_out_time)
            $attendanceWithCheckout = Attendance::where('user_id', $user->id)
                ->whereDate('check_in_time', today())
                ->whereNotNull('check_out_time') // ← INI YANG PENTING!
                ->orderBy('check_in_time', 'desc')
                ->first();

            if ($attendanceWithCheckout) {
                // JIKA ADA YANG SUDAH PULANG
                $data['myAttendanceToday'] = $attendanceWithCheckout;
            } else {
                // JIKA BELUM PULANG, CARI YANG MASIH MASUK
                $data['myAttendanceToday'] = Attendance::where('user_id', $user->id)
                    ->whereDate('check_in_time', today())
                    ->whereNull('check_out_time') // Yang belum pulang
                    ->orderBy('check_in_time', 'desc')
                    ->first();
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