<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Division;
use App\Models\Attendance;
use App\Models\LateNotification;
use App\Models\LeaveRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use PDF; // Pastikan package barryvdh/laravel-dompdf terinstall

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

        // Admin melihat semua jika tidak ada branch_id, role lain terkunci di branch
        if ($user->role != 'admin' || $branch_id != null) {
            $attendanceQuery->where('branch_id', $branch_id);
            $userQuery->where('branch_id', $branch_id);
            $divisionQuery->where('branch_id', $branch_id);
            $lateQuery->where('branch_id', $branch_id);
        }

        // ======================================================
        // 2. DATA IZIN HARI INI (Untuk Notifikasi User)
        // ======================================================
        $data['myLeaveToday'] = $this->getTodayLeaveRequest($user->id);

        // ======================================================
        // 3. ISI DATA BERDASARKAN ROLE & ID CARD
        // ======================================================

        // DATA UNTUK SEMUA ROLE: ID Card & Status Absensi (Logika Cross-day diterapkan di sini)
        $data = $this->getCommonDataForAllRoles($user, $data);

        if ($user->role == 'admin') {
            // --- ADMIN ---
            
            // [UPDATED] Total Users: Exclude Admin accounts
            // Agar jumlah admin tidak ketahuan user lain / statistik murni karyawan
            $data['totalUsers'] = $userQuery->where('role', '!=', 'admin')->count();

            $data['totalDivisions'] = $divisionQuery->count();
            // Total record hari ini
            $data['attendancesToday'] = $attendanceQuery->whereDate('check_in_time', today())->count();
            // Pending Verification Global
            $data['pendingVerifications'] = $attendanceQuery->where('status', 'pending_verification')->count();
            
            // Statistik Lengkap untuk Admin (Chart)
            $data['stats'] = $this->getAdminAttendanceStats($branch_id);
            
        } elseif ($user->role == 'audit') {
            // --- AUDIT ---
            // Anggota tim = user biasa & leader di cabang yang sama
            $data['myTeamMembers'] = $userQuery->whereIn('role', ['user_biasa', 'leader'])->count();
            $data['pendingVerifications'] = $attendanceQuery->where('status', 'pending_verification')->count();
            $data['attendancesToday'] = $attendanceQuery->whereDate('check_in_time', today())->count();
            
            // Statistik untuk Audit (Chart)
            $data['stats'] = $this->getAuditAttendanceStats($branch_id);
            
        } elseif ($user->role == 'security') {
            // --- SECURITY ---
            // Hitung scan yang dilakukan security ini hari ini
            $data['myScansToday'] = Attendance::where('scanned_by_user_id', $user->id)
                ->whereDate('check_in_time', today())
                ->count();

            // Total user yang "bisa" discan
            $data['totalUsers'] = $userQuery->whereIn('role', ['user_biasa', 'leader'])->count();
            
            // Statistik untuk Security (Chart)
            $data['stats'] = $this->getSecurityAttendanceStats($user->id, $branch_id);
            
        } elseif ($user->role == 'user_biasa' || $user->role == 'leader') {
            // --- USER BIASA & LEADER ---
            $data['myPendingCount'] = Attendance::where('user_id', $user->id)
                ->where('status', 'pending_verification')
                ->count();

            // Rekan satu divisi
            $data['myTeamCount'] = User::where('division_id', $user->division_id)
                ->where('id', '!=', $user->id)
                ->count();
                
            // Statistik Personal (Chart)
            $data['stats'] = $this->getUserAttendanceStats($user->id, $branch_id);
        }

        // Kirim variable $attendanceStats juga untuk kompatibilitas view lama jika ada
        if (isset($data['stats'])) {
            $data['attendanceStats'] = $data['stats'];
        }

        return view('dashboard', $data);
    }

    /**
     * Helper: Cek Izin Hari Ini (Sakit, Cuti, Telat)
     */
    private function getTodayLeaveRequest($user_id)
    {
        return LeaveRequest::where('user_id', $user_id)
            ->where('is_active', true)
            ->where('status', 'approved')
            ->where(function($query) {
                $query->where(function($q) {
                    // Izin jangka panjang (Sakit/Cuti)
                    $q->whereIn('type', ['sakit', 'izin'])
                      ->whereDate('start_date', '<=', today())
                      ->whereDate('end_date', '>=', today());
                })->orWhere(function($q) {
                    // Izin harian (Telat)
                    $q->where('type', 'telat')
                      ->whereDate('start_date', today());
                });
            })
            ->first();
    }

    /**
     * Helper: Data ID Card & Status Absensi (Support Lembur Lintas Hari)
     */
    private function getCommonDataForAllRoles($user, $data)
    {
        // 1. Cari Sesi Aktif (Check In ada, Check Out kosong) - Lookback 24 jam
        // Ini menangani kasus lembur melewati jam 00:00
        $activeSession = Attendance::where('user_id', $user->id)
            ->whereNull('check_out_time')
            ->where('check_in_time', '>=', Carbon::now()->subHours(24))
            ->latest('check_in_time')
            ->first();

        if ($activeSession) {
            $data['myAttendanceToday'] = $activeSession;
        } else {
            // 2. Jika tidak ada sesi aktif, cari sesi yang SUDAH SELESAI hari ini
            $finishedSession = Attendance::where('user_id', $user->id)
                ->whereDate('check_in_time', today())
                ->whereNotNull('check_out_time')
                ->latest('check_in_time')
                ->first();
            
            $data['myAttendanceToday'] = $finishedSession;
        }

        return $data;
    }

    /**
     * Statistik Admin: Global Hari Ini
     */
    private function getAdminAttendanceStats($branch_id = null)
    {
        $query = Attendance::whereDate('check_in_time', today());
        
        if ($branch_id) {
            $query->where('branch_id', $branch_id);
        }

        // [UPDATED] Hitung total user real (exclude admin)
        // Ini agar persentase kehadiran akurat terhadap jumlah karyawan
        $totalUsers = User::when($branch_id, function($q) use ($branch_id) {
            return $q->where('branch_id', $branch_id);
        })
        ->where('role', '!=', 'admin') // Exclude Admin
        ->count();

        // Clone query untuk efisiensi
        $presentCount = (clone $query)->count();
        $lateCount = (clone $query)->where('is_late_checkin', true)->count();
        $earlyCount = (clone $query)->where('is_early_checkout', true)->count();
        $pendingCount = (clone $query)->where('status', 'pending_verification')->count();
        
        // On Time = Hadir - Terlambat
        $onTimeCount = max($presentCount - $lateCount, 0);
        // Absent = Total User - Hadir
        $absentCount = max($totalUsers - $presentCount, 0);

        return [
            'total' => $presentCount,
            'present' => $presentCount,
            'late' => $lateCount,
            'early' => $earlyCount,
            'pending' => $pendingCount,
            'on_time' => $onTimeCount,
            'absent' => $absentCount,
            
            // Persentase
            'present_percentage' => $totalUsers > 0 ? round(($presentCount / $totalUsers) * 100) : 0,
            'late_percentage' => $presentCount > 0 ? round(($lateCount / $presentCount) * 100) : 0,
            'pending_percentage' => $presentCount > 0 ? round(($pendingCount / $presentCount) * 100) : 0,
            'absent_percentage' => $totalUsers > 0 ? round(($absentCount / $totalUsers) * 100) : 0,
        ];
    }

    /**
     * Statistik Audit: Fokus Verifikasi Hari Ini
     */
    private function getAuditAttendanceStats($branch_id = null)
    {
        $query = Attendance::whereDate('check_in_time', today());
        
        if ($branch_id) {
            $query->where('branch_id', $branch_id);
        }

        $totalToday = (clone $query)->count();
        $verified = (clone $query)->whereNotNull('verified_by_user_id')->count();
        $pending = (clone $query)->where('status', 'pending_verification')->count();
        $late = (clone $query)->where('is_late_checkin', true)->count();

        return [
            'total' => $totalToday,
            'verified' => $verified,
            'pending' => $pending,
            'late' => $late,
            
            // Persentase
            'verified_percentage' => $totalToday > 0 ? round(($verified / $totalToday) * 100) : 0,
            'pending_percentage' => $totalToday > 0 ? round(($pending / $totalToday) * 100) : 0,
            'late_percentage' => $totalToday > 0 ? round(($late / $totalToday) * 100) : 0,
        ];
    }

    /**
     * Statistik Security: Aktivitas Scan Hari Ini
     */
    private function getSecurityAttendanceStats($security_id, $branch_id = null)
    {
        // Hitung semua scan hari ini
        $query = Attendance::whereDate('check_in_time', today());
        
        if ($branch_id) {
            $query->where('branch_id', $branch_id);
        }
        
        // Filter khusus tipe SCAN QR
        $scanQuery = (clone $query)->where('attendance_type', 'scan');

        $totalScans = (clone $scanQuery)->count(); 
        $checkInScans = (clone $scanQuery)->count(); 
        $checkOutScans = (clone $scanQuery)->whereNotNull('check_out_time')->count();

        // Total aktivitas 'beep' = Masuk + Pulang
        $totalActivity = $checkInScans + $checkOutScans;

        return [
            'total_scans' => $totalActivity,
            'check_in_scans' => $checkInScans,
            'check_out_scans' => $checkOutScans,
            
            // Persentase (Placeholder)
            'check_in_percentage' => 100, 
            'check_out_percentage' => 100, 
        ];
    }

    /**
     * Statistik User Biasa / Leader: Performa Bulan Ini
     */
    private function getUserAttendanceStats($user_id, $branch_id = null)
    {
        // Statistik User menampilkan BULAN INI agar lebih relevan
        $query = Attendance::where('user_id', $user_id)
            ->whereMonth('check_in_time', Carbon::now()->month)
            ->whereYear('check_in_time', Carbon::now()->year);
        
        if ($branch_id) {
            $query->where('branch_id', $branch_id);
        }

        $totalAttendances = (clone $query)->count();
        $present = $totalAttendances; // Asumsi record ada berarti hadir
        $late = (clone $query)->where('is_late_checkin', true)->count();
        $early = (clone $query)->where('is_early_checkout', true)->count();
        $pending = (clone $query)->where('status', 'pending_verification')->count();
        
        // Tepat Waktu = Total - Terlambat
        $onTime = max($totalAttendances - $late, 0);

        return [
            'total' => $totalAttendances,
            'present' => $present,
            'late' => $late,
            'early' => $early,
            'pending' => $pending,
            'on_time' => $onTime,
            
            // Persentase
            'present_percentage' => 100,
            'late_percentage' => $totalAttendances > 0 ? round(($late / $totalAttendances) * 100) : 0,
            'on_time_percentage' => $totalAttendances > 0 ? round(($onTime / $totalAttendances) * 100) : 0,
            'pending_percentage' => $totalAttendances > 0 ? round(($pending / $totalAttendances) * 100) : 0,
        ];
    }

    /**
     * Export PDF (Memanfaatkan data stats yang sudah ada)
     */
    public function exportAttendancePDF(Request $request)
    {
        $user = Auth::user();
        $branch_id = $user->branch_id;
        $date = $request->get('date', today()->format('Y-m-d'));
        
        $data = [];
        $data['user'] = $user;
        $data['export_date'] = now()->format('d-m-Y H:i:s');
        $data['period'] = $date;
        
        switch ($user->role) {
            case 'admin':
                $data['stats'] = $this->getAdminAttendanceStats($branch_id);
                $data['title'] = 'Laporan Statistik Harian (Admin)';
                $data['role'] = 'Admin';
                break;
                
            case 'audit':
                $data['stats'] = $this->getAuditAttendanceStats($branch_id);
                $data['title'] = 'Laporan Verifikasi Absensi';
                $data['role'] = 'Audit';
                break;
                
            case 'security':
                $data['stats'] = $this->getSecurityAttendanceStats($user->id, $branch_id);
                $data['title'] = 'Laporan Aktivitas Security';
                $data['role'] = 'Security';
                break;
                
            case 'user_biasa':
            case 'leader':
                $data['stats'] = $this->getUserAttendanceStats($user->id, $branch_id);
                $data['title'] = 'Laporan Absensi Personal (Bulan Ini)';
                $data['role'] = 'Karyawan';
                break;
        }

        $pdf = PDF::loadView('pdf.attendance-report', $data);
        return $pdf->download('laporan-absensi-' . $user->role . '-' . now()->format('Y-m-d') . '.pdf');
    }
}