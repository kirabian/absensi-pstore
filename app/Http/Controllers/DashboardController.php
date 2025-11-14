<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Division;
use App\Models\Attendance;
use App\Models\AuditTeam;
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
        $branch_id = $user->branch_id; // Ambil branch_id user

        // ======================================================
        // LOGIKA BARU: Tentukan query dasar berdasarkan cabang
        // ======================================================
        $attendanceQuery = Attendance::query();
        $userQuery = User::query();
        $divisionQuery = Division::query();
        $lateQuery = LateNotification::query();

        if ($user->role != 'admin' || $branch_id != null) {
            // Jika BUKAN Super Admin (yaitu Admin Cabang, Audit, Security, Leader, User)
            // Filter semua query berdasarkan cabang user
            $attendanceQuery->where('branch_id', $branch_id);
            $userQuery->where('branch_id', $branch_id);
            $divisionQuery->where('branch_id', $branch_id);
            $lateQuery->where('branch_id', $branch_id);
        }
        // Jika Super Admin (role 'admin' dan branch_id null), dia dapat semua data (tanpa filter).
        // ======================================================


        if ($user->role == 'admin') {
            // --- Data untuk ADMIN (Super ATAU Cabang) ---
            $data['totalUsers'] = $userQuery->count();
            $data['totalDivisions'] = $divisionQuery->count();
            $data['attendancesToday'] = $attendanceQuery->whereDate('check_in_time', today())->count();
            $data['pendingVerifications'] = $attendanceQuery->where('status', 'pending_verification')->count();
        } elseif ($user->role == 'audit') {
            // --- Data untuk AUDIT (Logika Baru) ---

            // 1. "Anggota Tim" = Semua user_biasa & leader di cabang ini
            $data['myTeamMembers'] = $userQuery->whereIn('role', ['user_biasa', 'leader'])->count(); // KEMBALI ke user_biasa

            // 2. "Perlu Verifikasi" = Semua pending di cabang ini
            $data['pendingVerifications'] = $attendanceQuery->where('status', 'pending_verification')->count();

            // 3. "Absen Hari Ini" = Semua absen di cabang ini hari ini
            $data['attendancesToday'] = $attendanceQuery->whereDate('check_in_time', today())->count();
        } elseif ($user->role == 'security') {
            // --- Data untuk SECURITY (Logika Baru) ---
            $data['myScansToday'] = Attendance::where('scanned_by_user_id', $user->id)
                ->whereDate('check_in_time', today())
                ->count();
            // Total user yg bisa di-scan DI CABANG INI
            $data['totalUsers'] = $userQuery->whereIn('role', ['user_biasa', 'leader'])->count(); // KEMBALI ke user_biasa
        } elseif ($user->role == 'user_biasa' || $user->role == 'leader') { // KEMBALI ke user_biasa
            // --- Data untuk USER BIASA & LEADER ---
            $data['myAttendanceToday'] = Attendance::where('user_id', $user->id)
                ->whereDate('check_in_time', today())
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