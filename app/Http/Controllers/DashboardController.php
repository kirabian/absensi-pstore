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
use PDF;

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
        // 2. ISI DATA BERDASARKAN ROLE - SEMUA ROLE DAPAT ID CARD & STATUS ABSENSI
        // ======================================================

        // DATA UNTUK SEMUA ROLE: ID Card & Status Absensi
        $data = $this->getCommonDataForAllRoles($user, $data);

        if ($user->role == 'admin') {
            // --- ADMIN ---
            $data['totalUsers'] = $userQuery->count();
            $data['totalDivisions'] = $divisionQuery->count();
            $data['attendancesToday'] = $attendanceQuery->whereDate('check_in_time', today())->count();
            $data['pendingVerifications'] = $attendanceQuery->where('status', 'pending_verification')->count();
            
            // Statistik untuk Admin
            $data['attendanceStats'] = $this->getAdminAttendanceStats($branch_id);
            
        } elseif ($user->role == 'audit') {
            // --- AUDIT ---
            $data['myTeamMembers'] = $userQuery->whereIn('role', ['user_biasa', 'leader'])->count();
            $data['pendingVerifications'] = $attendanceQuery->where('status', 'pending_verification')->count();
            $data['attendancesToday'] = $attendanceQuery->whereDate('check_in_time', today())->count();
            
            // Statistik untuk Audit
            $data['attendanceStats'] = $this->getAuditAttendanceStats($branch_id);
            
        } elseif ($user->role == 'security') {
            // --- SECURITY ---
            $data['myScansToday'] = Attendance::where('scanned_by_user_id', $user->id)
                ->whereDate('check_in_time', today())
                ->count();

            $data['totalUsers'] = $userQuery->whereIn('role', ['user_biasa', 'leader'])->count();
            
            // Statistik untuk Security
            $data['attendanceStats'] = $this->getSecurityAttendanceStats($user->id, $branch_id);
            
        } elseif ($user->role == 'user_biasa' || $user->role == 'leader') {
            // --- USER BIASA & LEADER ---
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
                
            // Statistik untuk User Biasa & Leader
            $data['attendanceStats'] = $this->getUserAttendanceStats($user->id, $branch_id);
        }

        return view('dashboard', $data);
    }

    /**
     * Get common data for ALL roles (ID Card & Attendance Status)
     */
   /**
     * Get common data for ALL roles (ID Card & Attendance Status)
     */
    private function getCommonDataForAllRoles($user, $data)
    {
        // ------------------------------------------------------------
        // 1. LOGIKA ABSENSI (EXISTING)
        // ------------------------------------------------------------
        $todayAttendances = Attendance::where('user_id', $user->id)
            ->whereDate('check_in_time', today())
            ->orderBy('check_in_time', 'desc')
            ->get();

        // Cari yang SUDAH PULANG (check_out_time NOT NULL)
        $attendanceWithCheckout = $todayAttendances->first(function ($attendance) {
            return !is_null($attendance->check_out_time);
        });

        if ($attendanceWithCheckout) {
            $data['myAttendanceToday'] = $attendanceWithCheckout;
        } else {
            // Cari yang punya photo_out_path (data rusak tapi sudah pulang)
            $attendanceWithPhotoOut = $todayAttendances->first(function ($attendance) {
                return !is_null($attendance->photo_out_path);
            });

            if ($attendanceWithPhotoOut) {
                $data['myAttendanceToday'] = $attendanceWithPhotoOut;
            } else {
                // Ambil yang terakhir (masih masuk)
                $data['myAttendanceToday'] = $todayAttendances->first();
            }
        }

        // ------------------------------------------------------------
        // 2. LOGIKA IZIN / SAKIT / TELAT (BARU DITAMBAHKAN)
        // ------------------------------------------------------------
        $today = Carbon::today();
        
        $myLeaveToday = LeaveRequest::where('user_id', $user->id)
            ->where('status', '!=', 'rejected') // Abaikan yang sudah ditolak
            ->where(function ($query) use ($today) {
                $query->where(function ($q) use ($today) {
                    // Kasus A: Sakit/Izin (Cek Rentang Tanggal)
                    // Start date <= hari ini DAN End date >= hari ini
                    $q->whereIn('type', ['sakit', 'izin'])
                      ->whereDate('start_date', '<=', $today)
                      ->whereDate('end_date', '>=', $today);
                })->orWhere(function ($q) use ($today) {
                    // Kasus B: Telat (Cek Tanggal Spesifik Hari Ini)
                    $q->where('type', 'telat')
                      ->whereDate('start_date', $today);
                });
            })
            ->latest() // Ambil yang paling baru diajukan jika ada duplikat
            ->first();

        // Masukkan ke array data agar bisa dipanggil di Blade
        $data['myLeaveToday'] = $myLeaveToday; 

        return $data;
    }

    /**
     * Get attendance statistics for Admin
     */
    private function getAdminAttendanceStats($branch_id = null)
    {
        $query = Attendance::query();
        
        if ($branch_id) {
            $query->where('branch_id', $branch_id);
        }

        $totalAttendances = $query->whereDate('check_in_time', today())->count();
        $present = $query->clone()->where('status', 'present')->count();
        $late = $query->clone()->where('status', 'late')->count();
        $pending = $query->clone()->where('status', 'pending_verification')->count();
        $absent = $query->clone()->where('status', 'absent')->count();

        return [
            'total' => $totalAttendances,
            'present' => $present,
            'late' => $late,
            'pending' => $pending,
            'absent' => $absent,
            'present_percentage' => $totalAttendances > 0 ? round(($present / $totalAttendances) * 100, 2) : 0,
            'late_percentage' => $totalAttendances > 0 ? round(($late / $totalAttendances) * 100, 2) : 0,
            'pending_percentage' => $totalAttendances > 0 ? round(($pending / $totalAttendances) * 100, 2) : 0,
            'absent_percentage' => $totalAttendances > 0 ? round(($absent / $totalAttendances) * 100, 2) : 0,
        ];
    }

    /**
     * Get attendance statistics for Audit
     */
    private function getAuditAttendanceStats($branch_id = null)
    {
        $query = Attendance::query();
        
        if ($branch_id) {
            $query->where('branch_id', $branch_id);
        }

        $totalAttendances = $query->whereDate('check_in_time', today())->count();
        $verified = $query->clone()->whereNotNull('verified_by_user_id')->count();
        $pending = $query->clone()->whereNull('verified_by_user_id')->count();
        $late = $query->clone()->where('is_late_checkin', true)->count();

        return [
            'total' => $totalAttendances,
            'verified' => $verified,
            'pending' => $pending,
            'late' => $late,
            'verified_percentage' => $totalAttendances > 0 ? round(($verified / $totalAttendances) * 100, 2) : 0,
            'pending_percentage' => $totalAttendances > 0 ? round(($pending / $totalAttendances) * 100, 2) : 0,
            'late_percentage' => $totalAttendances > 0 ? round(($late / $totalAttendances) * 100, 2) : 0,
        ];
    }

    /**
     * Get attendance statistics for Security
     */
    private function getSecurityAttendanceStats($security_id, $branch_id = null)
    {
        $query = Attendance::where('scanned_by_user_id', $security_id);
        
        if ($branch_id) {
            $query->where('branch_id', $branch_id);
        }

        $todayScans = $query->whereDate('check_in_time', today())->count();
        $checkInScans = $query->clone()->whereNotNull('check_in_time')->whereNull('check_out_time')->count();
        $checkOutScans = $query->clone()->whereNotNull('check_out_time')->count();

        return [
            'total_scans' => $todayScans,
            'check_in_scans' => $checkInScans,
            'check_out_scans' => $checkOutScans,
            'check_in_percentage' => $todayScans > 0 ? round(($checkInScans / $todayScans) * 100, 2) : 0,
            'check_out_percentage' => $todayScans > 0 ? round(($checkOutScans / $todayScans) * 100, 2) : 0,
        ];
    }

    /**
     * Get attendance statistics for User Biasa & Leader
     */
    private function getUserAttendanceStats($user_id, $branch_id = null)
    {
        $query = Attendance::where('user_id', $user_id)
            ->whereDate('check_in_time', '>=', now()->subDays(30));
        
        if ($branch_id) {
            $query->where('branch_id', $branch_id);
        }

        $totalAttendances = $query->count();
        $present = $query->clone()->where('status', 'present')->count();
        $late = $query->clone()->where('status', 'late')->count();
        $pending = $query->clone()->where('status', 'pending_verification')->count();
        $onTime = $query->clone()->where('status', 'present')->where('is_late_checkin', false)->count();

        return [
            'total' => $totalAttendances,
            'present' => $present,
            'late' => $late,
            'pending' => $pending,
            'on_time' => $onTime,
            'present_percentage' => $totalAttendances > 0 ? round(($present / $totalAttendances) * 100, 2) : 0,
            'late_percentage' => $totalAttendances > 0 ? round(($late / $totalAttendances) * 100, 2) : 0,
            'pending_percentage' => $totalAttendances > 0 ? round(($pending / $totalAttendances) * 100, 2) : 0,
            'on_time_percentage' => $totalAttendances > 0 ? round(($onTime / $totalAttendances) * 100, 2) : 0,
        ];
    }

    /**
     * Export PDF untuk semua role
     */
    public function exportAttendancePDF(Request $request)
    {
        $user = Auth::user();
        $type = $request->get('type', 'today');
        $date = $request->get('date', today()->format('Y-m-d'));
        
        $data = [];
        
        switch ($user->role) {
            case 'admin':
                $data = $this->getAdminAttendanceStats($user->branch_id);
                $data['title'] = 'Laporan Absensi Admin';
                $data['role'] = 'Admin';
                break;
                
            case 'audit':
                $data = $this->getAuditAttendanceStats($user->branch_id);
                $data['title'] = 'Laporan Verifikasi Absensi';
                $data['role'] = 'Audit';
                break;
                
            case 'security':
                $data = $this->getSecurityAttendanceStats($user->id, $user->branch_id);
                $data['title'] = 'Laporan Pindaian Security';
                $data['role'] = 'Security';
                break;
                
            case 'user_biasa':
            case 'leader':
                $data = $this->getUserAttendanceStats($user->id, $user->branch_id);
                $data['title'] = 'Laporan Absensi Personal';
                $data['role'] = ucfirst(str_replace('_', ' ', $user->role));
                break;
        }
        
        $data['user'] = $user;
        $data['export_date'] = now()->format('d-m-Y H:i:s');
        $data['period'] = $date;

        $pdf = PDF::loadView('pdf.attendance-report', $data);
        
        return $pdf->download('laporan-absensi-' . $user->role . '-' . now()->format('Y-m-d') . '.pdf');
    }
}