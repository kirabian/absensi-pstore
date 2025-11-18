<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Division;
use App\Models\Attendance;
use App\Models\LateNotification; // Pastikan ini di-import
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
        $lateQuery = LateNotification::query(); // Perbaikan nama variabel query

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
            // Hitung berapa kali security ini melakukan scan hari ini
            $data['myScansToday'] = Attendance::where('scanned_by_user_id', $user->id)
                ->whereDate('check_in_time', today())
                ->count();
                
            $data['totalUsers'] = $userQuery->whereIn('role', ['user_biasa', 'leader'])->count();

        } elseif ($user->role == 'user_biasa' || $user->role == 'leader') {
            // --- USER BIASA & LEADER ---
            
            // [PENTING] Ambil Data Absensi Hari Ini (Termasuk Checkout Time)
            // Pakai latest() untuk memastikan mengambil data update terakhir
            $data['myAttendanceToday'] = Attendance::where('user_id', $user->id)
                ->whereDate('check_in_time', today())
                ->latest() 
                ->first(); 

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