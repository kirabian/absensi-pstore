<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Division;
use App\Models\Attendance;
use App\Models\AuditTeam;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // Penting untuk mengambil user

class DashboardController extends Controller
{
    /**
     * Menampilkan dashboard berdasarkan role user.
     */
    public function index()
    {
        $user = Auth::user(); // Ambil user yang sedang login
        $data = []; // Ini adalah data yang akan kita kirim ke view

        if ($user->role == 'admin') {
            // --- Data untuk ADMIN ---
            $data['totalUsers'] = User::count();
            $data['totalDivisions'] = Division::count();
            $data['attendancesToday'] = Attendance::whereDate('check_in_time', today())->count();
            $data['pendingVerifications'] = Attendance::where('status', 'pending_verification')->count();
        } elseif ($user->role == 'audit') {
            // --- Data untuk AUDIT ---

            // 1. Ambil ID divisi yang dia audit
            $myDivisionIds = AuditTeam::where('user_id', $user->id)->pluck('division_id');
            // 2. Ambil ID user yang ada di divisi tersebut
            $myUserIds = User::whereIn('division_id', $myDivisionIds)->pluck('id');

            $data['myTeamMembers'] = $myUserIds->count();
            $data['pendingVerifications'] = Attendance::whereIn('user_id', $myUserIds)
                ->where('status', 'pending_verification')
                ->count();
            $data['attendancesToday'] = Attendance::whereIn('user_id', $myUserIds)
                ->whereDate('check_in_time', today())
                ->count();
        } elseif ($user->role == 'security') {
            // --- Data untuk SECURITY ---
            $data['myScansToday'] = Attendance::where('scanned_by_user_id', $user->id)
                ->whereDate('check_in_time', today())
                ->count();
            $data['totalUsers'] = User::where('role', 'user_biasa')->count(); // Total user yg bisa di-scan

        } elseif ($user->role == 'user_biasa') {
            // --- Data untuk USER BIASA (INI YANG MEMPERBAIKI ERROR ANDA) ---
            $data['myAttendanceToday'] = Attendance::where('user_id', $user->id)
                ->whereDate('check_in_time', today())
                ->first();
            $data['myPendingCount'] = Attendance::where('user_id', $user->id)
                ->where('status', 'pending_verification')
                ->count();
            $data['myTeamCount'] = User::where('division_id', $user->division_id)
                ->where('id', '!=', $user->id) // Jumlah teman satu tim
                ->count();
        }

        // Kirim semua data ($data) yang sesuai ke view 'dashboard'
        return view('dashboard', $data);
    }
}
